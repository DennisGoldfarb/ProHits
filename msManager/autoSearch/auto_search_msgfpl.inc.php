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
function searchMSGFPL($raw_file_path, $WellID, $parameter_arr, $theTaskID, $selected_data_format){
  global $raw_file_pattern;
  global $tableName, $resultTable, $searchEngine_arr;
  global $msManager_link;
	global $frm_theURL;
  global $prohits_root;
  global $is_SWATH_file;
  global $gpm;
  
  $msgfpl_in_prohits = is_in_local_server('MSGFPL');
   
  echo "MSGFPL search for $raw_file_path\n";
  
  if(!preg_match("/\.mzXML$|\.mzXML\.gz$|\.mzML$|\.mzML\.gz$/i", $raw_file_path, $tmp)){
    $msg = "Warning: the file($raw_file_path) cannot be searched, since file extention is no one of raw file in config/conf.inc.php file";
    echo $msg;
    writeLog($msg);
    return;
  }
  if(!$msgfpl_in_prohits){
    $msg = "ERROR: MSGFPL not set in Prohits. Please check Prohits conf file.";
    echo $msg;
    writeLog($msg);
    return;
  }
  
  
  echo "run MSGFPL locally\n";
  $MSGFPL_threads = ''; #empty = all available cores
  $use_memery_size = '5G';
  if(defined("JAR_MAX_MEMORY")){
    if(intval(JAR_MAX_MEMORY) < intval($use_memery_size)){
      $use_memery_size = JAR_MAX_MEMORY;
    }
  }
  //***************************************
  $msgfpl_command = 'java -Xmx'.$use_memery_size.' -jar '. preg_replace("/\/$/", "", MSGFPL_BIN_PATH) . '/MSGFPlus.jar';
  //***************************************
  //in ../common_function.inc.php
  if(defined("PROTEOWIZARD_BIN_PATH") and PROTEOWIZARD_BIN_PATH and _is_dir(PROTEOWIZARD_BIN_PATH)){
    $PWIZ_bin_PATH = preg_replace("/\/$/", "", PROTEOWIZARD_BIN_PATH);
  }else if(_is_dir($prohits_root."EXT/pwiz-bin")){
    $PWIZ_bin_PATH = $prohits_root."EXT/pwiz-bin";
  }else{
    fatalError("Please follow the instruction to set PROTEOWIZARD_BIN_PATH.");
  }
  $task_infor = prepare_run_search_on_local($tableName, $WellID, $theTaskID, $raw_file_path, 'MSGFPL');
  
  //print_r($task_infor); exit;   
   
  
  $msgfpl_para_str = createMSGFPLModFileAndParamStr($task_infor['paramFilePath'], $parameter_arr);
  if($MSGFPL_threads){
    $msgfpl_para_str .= " -thread ".$MSGFPL_threads;
  }
  
  $i = 1;
  $resultFilePath_arr = array();
  $msgfpl_added = '';
  do{
    if($is_SWATH_file){
      $task_infor['linked_raw_file_path'] = $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".mzXML";
      $task_infor['resultFilePath']       = $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".pep.xml";
      $tmp_results_FilePath =  $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".mzid";
    }else{
      $msgfpl_added = '_msgfpl';
      $tmp_results_FilePath =  preg_replace("/pep[.]xml+$/","" , $task_infor['resultFilePath']) ."mzid";
    }
    
    $search_command_tmp = $msgfpl_command . " -s " . escapeshellarg($task_infor['linked_raw_file_path']) . $msgfpl_para_str . " -o " . escapeshellarg($tmp_results_FilePath);
    $resultFilePath_arr[] = $task_infor['resultFilePath'];
    $tmp_resultFilePath_arr[] = $tmp_results_FilePath;
    $search_command[] = $search_command_tmp;
  
    $i++;
  }while($i<=$task_infor['run_times']);
  
  //-----------------------------------------------
  run_search_on_local($search_command, $task_infor);
  //-----------------------------------------------
  
  
  
  $all_resultFilePath = '';
  for($i=0; $i<count($resultFilePath_arr); $i++){
    $command = array();
    //make pep.xml
    $command[] =  $PWIZ_bin_PATH."/idconvert ". $tmp_resultFilePath_arr[$i] . " --pepXML -e ".$msgfpl_added.".pep.xml -o " . $task_infor['taskDir'] ;
    //modify pepXML
    $command[] =  "sed -i 's|protein_descr=\"[^\"]*\"||g' ". escapeshellarg($resultFilePath_arr[$i]);
    $command[] =  "sed -i 's|base_name=\"|base_name=\"".$task_infor['taskDir']."/|g' ".escapeshellarg($resultFilePath_arr[$i]);
    $Qi = $i+1;
    $cmd_file = $task_infor['taskDir']."/"."command_msgfpl_idconvert_Q".$Qi;
    
    $OK = make_command_file($command, $task_infor['taskDir'], $cmd_file);
     
    run_search_on_local($cmd_file, $task_infor);
    
    if(_is_file($resultFilePath_arr[$i])){
      if($all_resultFilePath) $all_resultFilePath .= ";";
      $all_resultFilePath .= $resultFilePath_arr[$i];
    }
  } 
  if($all_resultFilePath){
    $tmp_DataFile = mysqli_escape_string($msManager_link, $all_resultFilePath);
    $SQL = "update $resultTable set DataFiles='".$tmp_DataFile."', Date=now() where WellID='$WellID' and TaskID='$theTaskID' and SearchEngines='MSGFPL'";
    writeLog($SQL);
    mysqli_query($msManager_link, $SQL);
  }else{
    writeLog("MSGFPL was run in the local server: $search_command,  but not result file. Please check log file for detail: ". $task_infor['tasklog']);
  }

  return true;
}
function createMSGFPLModFileAndParamStr($paramFilePath, $parameter_arr) {
   
  $paramStr = '';
  $to_file = "# Max Number of Modifications per peptide\nNumMods=4\n\n";
  $to_file .= "# Mass or CompositionStr, Residues, ModType, Position, Name (all the five fields are required).\n\n";
  foreach($parameter_arr as $name=>$value){
    if($name == 'database_name'){
      $gpmDbFile = get_gpm_db_file_path($value);
      $paramStr .= " -d " . escapeshellarg($gpmDbFile); 
    }else if($name == 'other_param'){
      $paramStr .= $value;
    }else if($name == 'mods_str'){
      $tmp_arr = explode(';;', $value);
      foreach($tmp_arr as $line){
        $to_file .= $line ."\n";
      }
    }
  }
  $handle = fopen($paramFilePath, 'w');
  fwrite($handle, $to_file);
  fclose($handle);
  print "Input parameter file created: ".$paramFilePath."\n";
  return $paramStr . " -mod ". escapeshellarg($paramFilePath);
}
?>
