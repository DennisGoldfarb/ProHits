<?php 
/***********************************************************************
 Copyright 2010 Gingras and Tyers labs, 
 Samuel Lunenfeld Research Institute, Mount Sinai Hospital.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*************************************************************************/
function searchGPM($raw_file_path, $WellID, $parameter_arr, $LCQfilter_str, $theTaskID, $selected_data_format){
  global $raw_file_pattern;
  global $tableName, $resultTable, $searchEngine_arr;
  global $msManager_link;
	global $debug;
	global $frm_theURL;
	global $is_SWATH_file;
  global $gpm;
  
   
  $modified_parameter_arr = array();
  for($i = 0; $i < count($parameter_arr); $i++){
    $tmp_arr = explode("=", $parameter_arr[$i]);
    if(count($tmp_arr) == 2){
      $tmp_arr[1] = str_replace('http://'.$gpm, '', $tmp_arr[1]);
      $tmp_arr[1] = preg_replace('/^:|:$/', '', $tmp_arr[1]);
      //if(!$tmp_arr[1]) continue;
      //echo $tmp_arr[1]."\n";
      $tmp_arr[0] = str_replace('__', ', ', $tmp_arr[0]);
      $tmp_arr[0] = str_replace('_', ' ', $tmp_arr[0]);
      $tmp_arr[0] = str_replace('99', '+', $tmp_arr[0]);
      $tmp_arr[0] = str_replace('88', '-', $tmp_arr[0]);
      $modified_parameter_arr[$tmp_arr[0]] = $tmp_arr[1];
    }
  }
  
  
  echo "GPM search for $raw_file_path\n";
	$gpm_in_prohits = false;   
  $gpm_in_prohits = is_in_local_server('GPM');
  if(!$gpm_in_prohits){
    $msg = "ERROR: GPM not set in Prohits. Please check Prohits conf file.";
    echo $msg;
    writeLog($msg);
    return;
  }
  echo "run XTandem locally\n";
  //************************************
  $xtandem_cmd = preg_replace("/\/$/", "", TPP_BIN_PATH)."/tandem";
  //xtandem in the local host
  //************************************
  echo "Run Xtandem locally\n";
  //in ../common_function.inc.php
  $task_infor = prepare_run_search_on_local($tableName, $WellID, $theTaskID, $raw_file_path, 'GPM');
  
  //print_r($task_infor);exit;
  $i = 1;
  $resultFilePath_arr = array();
  $search_command = array(); 
  do{
    if($is_SWATH_file){
       
      $task_infor['paramFilePath']        = $task_infor['taskDir']."/tandemparam_".$i.".xml";
      $task_infor['linked_raw_file_path'] = $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".mzXML";
      $task_infor['resultFilePath']       = $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".xml";
    }
    createTandemParamFile($task_infor['paramFilePath'], $task_infor['linked_raw_file_path'], $task_infor['resultFilePath'], $modified_parameter_arr);
    
    print "XTandem parameter file: ".$task_infor['paramFilePath']."\n";
    $search_command[] = $xtandem_cmd . " ". $task_infor['paramFilePath']; 
    $resultFilePath_arr[] = $task_infor['resultFilePath'];
     
     
    ##end of the tandem search
    $i++;
  }while($i<=$task_infor['run_times']);
   
  
  run_search_on_local($search_command, $task_infor);
  
  $all_resultFilePath = '';
  foreach( $resultFilePath_arr as $resultFilePath){
    if(_is_file($resultFilePath)){
      //$resultFilePath = str_replace(dirname(GPM_CGI_PATH), '', $resultFilePath);
      if($all_resultFilePath) $all_resultFilePath .= ";";
      $all_resultFilePath .= $resultFilePath;
    }
  } 
  if($all_resultFilePath){
    $tmp_DataFile = mysqli_escape_string($msManager_link, $all_resultFilePath);
    $SQL = "update $resultTable set DataFiles='".$tmp_DataFile."', Date=now() where WellID='$WellID' and TaskID='$theTaskID' and SearchEngines='GPM'";
    writeLog($SQL);
    mysqli_query($msManager_link, $SQL);
  }else{
    writeLog("XTandem was run in the local server: $search_command,  but not result file. Please check log file for detail: ". $task_infor['tasklog']);
  }
  return true;
}

//for the local search
function createTandemParamFile($paramFilePath, $rawFilePath, $resultsFilePath, $modified_parameter_arr) {
   //for the local search only.
   $core_num = 12;
   if(defined("NUM_THREAD")){
     $core_num = NUM_THREAD;
   }
   $entry='';
   $value='';
   $search_db_str = '';
  $OUTPUT = fopen($paramFilePath, 'w'); 
  
  fwrite($OUTPUT, "<?php xml version=\"1.0\" encoding=\"utf-8\" ?>\n<bioml>\n");
  fwrite($OUTPUT, "<note type=\"input\" label=\"spectrum, threads\">".$core_num."</note>\n");
  foreach( $modified_parameter_arr as $entry=>$value){
    $value = trim($value);
    if((strpos($entry, "modification mass") or strpos($entry, "cleavage site")) and !$value) continue;
    if(strstr($value, ':')) $value = str_replace(":", ",", $value);
    $entry = str_replace(" select", '', $entry);
    
     
    if(preg_match("/protein, taxon\d*/", $entry, $matches) and $value)  {
      if($search_db_str) $search_db_str .=",";
      $search_db_str .= $value;
    }else if($entry == "ProhitsUsekscore"){
      fwrite($OUTPUT, "<note type=\"input\" label=\"scoring, algorithm\">k-score</note>\n");
      fwrite($OUTPUT, "<note type=\"input\" label=\"spectrum, use conditioning\">no</note>\n");
      fwrite($OUTPUT, "<note type=\"input\" label=\"scoring, minimum ion count\">1</note>\n");
    }else if($entry != "output, results" and  $entry != "submit" && $entry != "lpdp" && !(strpos($entry,"tpp_")===0) && !(strpos($entry,"Prohits")===0))  {
       
      fwrite($OUTPUT, "<note type=\"input\" label=\"$entry\">");
      fwrite($OUTPUT, $value);
      fwrite($OUTPUT, "</note>\n");
    }
  }
  
  fwrite($OUTPUT,  "<note type=\"input\" label=\"output, title\">Models from '");
  fwrite($OUTPUT,  $rawFilePath);
  fwrite($OUTPUT,  "'</note>\n");
  fwrite($OUTPUT, "<note type=\"input\" label=\"protein, taxon\">".$search_db_str."</note>\n");
  fwrite($OUTPUT, "<note type=\"input\" label=\"output, spectra\">yes</note>\n");
  fwrite($OUTPUT, "<note type=\"input\" label=\"output, sort results by\">spectrum</note>\n");
  fwrite($OUTPUT, "<note type=\"input\" label=\"output, results\">all</note>\n");
  fwrite($OUTPUT, "<note type=\"input\" label=\"spectrum, path\">".$rawFilePath."</note>\n");
  fwrite($OUTPUT, "<note type=\"input\" label=\"output, path\">".$resultsFilePath."</note>\n");
  fwrite($OUTPUT, "</bioml>");
  echo  "Input parameter file created: ".$paramFilePath."\n";
  fclose($OUTPUT);
}



?>
