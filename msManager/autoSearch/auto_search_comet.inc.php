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
function searchCOMET($raw_file_path, $WellID, $parameter_arr, $theTaskID, $selected_data_format){
  global $raw_file_pattern;
  global $tableName, $resultTable, $searchEngine_arr;
  global $msManager_link;
  global $frm_theURL;
  global $is_SWATH_file;
  global $gpm_ip;
  
  $comet_in_prohits = is_in_local_server('COMET');
  
  echo "COMET search for $raw_file_path. In local=$comet_in_prohits\n";
   
   
  
  if(!preg_match("/\.mzXML$|\.mzXML\.gz$|\.mzML$|\.mzML\.gz$/i", $raw_file_path, $tmp)){
    $msg = "Warning: the file($raw_file_path) cannot be searched, since file extention is no one of raw file in config/conf.inc.php file";
    echo $msg;
    writeLog($msg);
    return;
  }
  if(!$comet_in_prohits){
    $msg = "ERROR: COMET not set in Prohits. Please check Prohits conf file.";
    echo $msg;
    writeLog($msg);
    return;
  }else{
    echo "run COMET locally\n";
    //***************************************
    $comet_cmd = preg_replace("/\/$/", "", COMET_BIN_PATH) . '/comet.exe';
    //***************************************
    //in ../common_function.inc.php
    if(!defined("COMET_BIN_PATH") or !is_dir(COMET_BIN_PATH)){
      fatalError("please set COMET in the local server, then define full COMET_BIN_PATH in the conf file.");
    }
    $task_infor = prepare_run_search_on_local($tableName, $WellID, $theTaskID, $raw_file_path, 'COMET'); 
    print_r($task_infor); 
     
    
    if($is_SWATH_file){
      $task_infor['paramFilePath'] = $task_infor['taskDir']."/comet.params";
    }
    createCometParamFile($task_infor['paramFilePath'], $parameter_arr);
    
     
    $i = 1;
    $resultFilePath_arr = array();
    $search_command = array();
    do{
      if($is_SWATH_file){
        $task_infor['linked_raw_file_path'] = $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".mzXML";
        $task_infor['resultFilePath']       = $task_infor['taskDir']."/".$task_infor['swath_file_name_base_in_TPP']."_Q".$i.".pep.xml";
      }
      $search_command[] = $comet_cmd . " -P" . escapeshellarg($task_infor['paramFilePath']) . " ". escapeshellarg($task_infor['linked_raw_file_path']);
      $resultFilePath_arr[] = $task_infor['resultFilePath'];
       
      ##end of the comet search
      $i++;
    }while($i<=$task_infor['run_times']);
     
    run_search_on_local($search_command, $task_infor);
    
    $all_resultFilePath = '';
    foreach( $resultFilePath_arr as $resultFilePath){
      if(!$is_SWATH_file){
        $tmp_results_FilePath =  preg_replace("/".$task_infor['prohits_mzML_file_type']."$/","" , $task_infor['linked_raw_file_path']) ."pep.xml";
        if(_is_file($tmp_results_FilePath)){
          $com =  "mv -f " . escapeshellarg($tmp_results_FilePath). " ". escapeshellarg($resultFilePath);
          //run_search_on_local($com, $task_infor);
          writeLog($com, $task_infor['taskComFile']);
          system($com);
        }else{
          writeLog("No COMET results file created for '$raw_file_path'");
          return false;
        }
      }
      if(_is_file($resultFilePath)){
        if($all_resultFilePath) $all_resultFilePath .= ";";
        $all_resultFilePath .= $resultFilePath;
      }
    } 
    if($all_resultFilePath){
      $tmp_DataFile = mysqli_escape_string($msManager_link, $all_resultFilePath);
      $SQL = "update $resultTable set DataFiles='".$tmp_DataFile."', Date=now() where WellID='$WellID' and TaskID='$theTaskID' and SearchEngines='COMET'";
      writeLog($SQL);
      mysqli_query($msManager_link, $SQL);
    }else{
      writeLog("COMET was run in the local server: $search_command,  but not result file. Please check log file for detail: ". $task_infor['tasklog']);
    }
  }
  return true;
}
function createCometParamFile($paramFilePath, $parameter_arr) {
  
//for the local search only.
  $default_param_arr = array();
  $to_file = '';
  $enzyme_info='';
  $file_header = '';
  //A value of 0 will cause Comet to poll the system and launch the same number of threads as CPU cores.
  //To set an explicit thread count, enter any value between 1 and 64. 
  $num_threads = 0; 
  if(defined("NUM_THREAD")){
    $num_threads = NUM_THREAD;
  }
  foreach($parameter_arr as $name=>$value){
    if($name == 'database_name'){
      $gpmDbFile = get_gpm_db_file_path($value);
      $to_file .= "database_name = " . $gpmDbFile ."\n"; 
    }else if($name == 'COMET_ENZYME_INFO'){
      $enzyme_info = $value;
    }else if($name == 'HEADER'){
      $file_header = $value."\n";
    }else if($name == 'num_threads' and $num_threads){
      $to_file .= $name . " = " . $num_threads."\n";
    }else{
      $to_file .= $name . " = " . $value ."\n";
    }
  }
  $to_file = $file_header . $to_file;
  $to_file .= "[COMET_ENZYME_INFO]\n";
  $to_file .= "$enzyme_info";
  $handle = fopen($paramFilePath, 'w');
  fwrite($handle, $to_file);
  fclose($handle);
  print "Input parameter file created: ".$paramFilePath."\n";
  
}
?>
