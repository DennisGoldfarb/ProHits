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
function searchMSFragger($raw_file_path, $WellID, $parameter_arr, $theTaskID, $selected_data_format){
  global $raw_file_pattern;
  global $tableName, $resultTable, $searchEngine_arr;
  global $msManager_link;
  global $frm_theURL;
  global $is_SWATH_file;
  
  $msfragger_in_prohits = is_in_local_server('MSFragger');
  
  echo "MSFragger search for $raw_file_path. In local=$msfragger_in_prohits\n";
  //print_r($parameter_arr);
  if(!preg_match("/\.mzXML$|\.mzXML\.gz$|\.mzML$|\.mzML\.gz$/i", $raw_file_path, $tmp)){
    $msg = "Warning: the file($raw_file_path) cannot be searched, since file extention is no one of raw file in config/conf.inc.php file";
    echo $msg;
    writeLog($msg);
    return;
  }
  if(!$msfragger_in_prohits){
    $msg = "ERROR: MSFragger not set in Prohits. Please check Prohits conf file.";
    echo $msg;
    writeLog($msg);
    return;
  }
  
  
  echo "run MSFragger\n"; 
  $use_memery_size = '30G';
  if(defined("JAR_MAX_MEMORY")){
    if(intval(JAR_MAX_MEMORY) < intval($use_memery_size)){
      $use_memery_size = JAR_MAX_MEMORY;
    }
  }
  //***************************************
  $msfragger_command = 'java -Xmx'.$use_memery_size.' -jar '. preg_replace("/\/$/", "", MSFRAGGER_BIN_PATH) . '/MSFragger.jar';
  //***************************************
  
  $task_infor = prepare_run_search_on_local($tableName, $WellID, $theTaskID, $raw_file_path, 'MSFragger');
  
  //print_r($task_infor);exit;
   
  
  if($is_SWATH_file){
    $task_infor['paramFilePath'] = $task_infor['taskDir']."/msfragger.params";
  }
  createMSFraggerParamFile($task_infor['paramFilePath'], $parameter_arr, $is_SWATH_file);
  
  $i = 1;
  $resultFilePath_arr = array();
  $search_command = array();
  do{
    if($is_SWATH_file){
      $task_infor['linked_raw_file_path'] = $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".mzXML";
      $task_infor['resultFilePath']       = $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".pep.xml";
    }
    $search_command[] = $msfragger_command . " " . escapeshellarg($task_infor['paramFilePath']) . " ". escapeshellarg($task_infor['linked_raw_file_path']);
    $resultFilePath_arr[] = $task_infor['resultFilePath'];
    $i++;
  }while($i<=$task_infor['run_times']);
   
  run_search_on_local($search_command, $task_infor);
  
  $all_resultFilePath = '';
  foreach( $resultFilePath_arr as $resultFilePath){
    
    if(_is_file($resultFilePath)){
      $tmpPepDirPath = dirname($resultFilePath);
      $com = "sed -i 's|base_name=\"|base_name=\"".$tmpPepDirPath."/|' ".escapeshellarg($resultFilePath);
      writeLog($com, $task_infor['taskComFile']);
      system($com);
      
      if($all_resultFilePath) $all_resultFilePath .= ";";
      $all_resultFilePath .= $resultFilePath;
    }else{
      if(!$is_SWATH_file){
        writeLog("No MSFragger results file created for '$raw_file_path'");
        return false;
      }
    }
  } 
  if($all_resultFilePath){
    $tmp_DataFile = mysqli_escape_string($msManager_link, $all_resultFilePath);
    $SQL = "update $resultTable set DataFiles='".$tmp_DataFile."', Date=now() where WellID='$WellID' and TaskID='$theTaskID' and SearchEngines='MSFragger'";
    writeLog($SQL);
    mysqli_query($msManager_link, $SQL);
  }else{
    writeLog("MSFragger was run in the local server: $search_command,  but not result file. Please check log file for detail: ". $task_infor['tasklog']);
  }

  return true;
}
function createMSFraggerParamFile($paramFilePath, $parameter_arr, $is_SWATH_file) {
  
//for the local search only.
  $default_param_arr = array();
  $to_file = '';
  $enzyme_info='';
  $file_header = '';
  //A value of 0 will cause MSFragger to use the auto-detected number of processors.
  //To set an explicit thread count, enter any value between 1 and 64. 
  $num_threads = 0; 
  if(defined("NUM_THREAD")){
    $num_threads = NUM_THREAD;
  }
  foreach($parameter_arr as $name=>$value){
    if($name == 'HEADER'){
      continue;
    }else if($name == 'database_name'){
      $gpmDbFile = get_gpm_db_file_path($value);
      $to_file .= "database_name = " . $gpmDbFile ."\n"; 
    }else if($name == 'num_threads'){
      $to_file .= $name . " = " . $num_threads."\n";
    }else if(strpos($name, 'variable_mod_') ===0 and !$value){
      $to_file .= "#".$name . " = " . $value ."\n";
    }else if($name == 'output_file_extension'){
      if($is_SWATH_file){
        $to_file .= $name . " = pep.xml\n";
      }else{
        $to_file .= $name . " = msfragger.pep.xml\n";
      }
    }else{
      $to_file .= $name . " = " . $value ."\n";
    }
  }
  $to_file = $file_header . $to_file;
  $handle = fopen($paramFilePath, 'w');
  fwrite($handle, $to_file);
  fclose($handle);
  print "Input parameter file created: ".$paramFilePath."\n";
  
}
?>
