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

/************************************************************************
date: 2006-05-08
description:
  1. a mass spec machine table name has to passed to this script
  2. get the running task.
  3. set spleep time, if start everyDay.
  4. get new raw file to searchResults tasble, if is autoAddRawfiles
  5. get all not searched raw files from searchResults table. 
  6. loop all files and send them to search engine if is not searched.
  7. get searched resuts file.
  8. shell > php auto_search_table_shell.php tableName taskID thisScriptURL sleepSec
  9. check shell process 
     shell > ps -o lstart PID
  10.  /usr/bin/php /data/www/Prohits/msManager/autoSearch/auto_search_table_shell.php LCQ 581 http://192.197.250.100:80/Prohits/msManager/autoSearch/auto_search_table_shell.php

*************************************************************************/
//error_reporting(E_STRICT|E_ALL);
set_time_limit(0);
ini_set("memory_limit","-1");
ini_set("default_socket_timeout", "100000");

$tableName = '';
$logfile = '../../logs/search.log';
$log_search_dir = '../../logs/searchs/';
$theTask_arr = array();
$searchEngine_arr = array();
$searchEngines_parameter_arr = array();
$theTaskID = 0;
$raw_file_source_dir = '';
$frm_theTaskID = '';
$frm_theURL = '';
$sleep_time = '';
$prohits_error_msg = '';
$is_SWATH_file = false;
$SWATH_app = '';

//change cwd to this directory
chdir(dirname(__FILE__));

include('../../config/conf.inc.php');
include('./auto_search_gpm.inc.php');
include('./auto_search_mascot.inc.php');
include('./auto_search_sequest.inc.php');
include('./auto_search_comet.inc.php');
include('./auto_search_msfragger.inc.php');
include('./auto_search_msgfpl.inc.php');
include('../autoBackup/shell_functions.inc.php');
include('./auto_search_umpire.inc.php');
include('./auto_search_MSPLIT.inc.php');
require("../common_functions.inc.php");

include('../../common/mysqlDB_class.php');
@require_once("../../common/HTTP/Request_Prohits.php");
require_once('../is_dir_file.inc.php');


$php_command_location = PHP_PATH;
//maybe it is remote connect prohits server---------
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$msManager_link  = mysqli_connect("$host", $user, $pswd, MANAGER_DB ) or fatalError("Unable to connect to mysql..." . mysqli_error($msManager_link));
mysqli_query($msManager_link, "SET SESSION sql_mode = ''");

//if(defined('STORAGE_IP')) 
if(isset($_SERVER['argv']) and count($_SERVER['argv']) > 1){
  $tableName = $_SERVER['argv'][1];
  if(isset($_SERVER['argv'][2])){
    $frm_theTaskID = $_SERVER['argv'][2];
    $theURL = $_SERVER['argv'][3];
    $frm_theURL = str_replace("autoSearch/auto_search_table_shell.php","",$theURL) . "autoBackup/download_raw_file.php";
    
    if(isset($_SERVER['argv'][4])){
      $sleep_sec = $_SERVER['argv'][4];
      if(is_numeric($sleep_sec)){
        $msg =  "It is ".@date("Y-m-d G:i:s").". Next $tableName (task $frm_theTaskID) process will be ". floor($sleep_sec/3600) . " hours later";
        writeLog($msg."\n");
        sleep($sleep_sec);
      }
    }
  }
}else if(array_key_exists('REQUEST_METHOD', $_SERVER)){
  //processing search now sed by web ******************************************
  if( $_SERVER['REQUEST_METHOD'] == "POST"){
    $request_arr = $_POST;
  }else{
    $request_arr = $_GET;
  }
  foreach ($request_arr as $key => $value) {
    $$key=$value;
  }
  if(isset($SID) and isset($tableName) and check_permission($SID,$tableName)) {
    if(isset($kill)){
      system("kill $kill");
      writeLog("Table:$tableName task ID: $frm_theTaskID has been stopped.");
      exit;
    }
    //get running task
    $SQL = "SELECT ID, Schedule, ProcessID FROM ". $tableName ."SearchTasks where ";
    if($frm_theTaskID){
      $SQL .= "ID='$frm_theTaskID'";
    }else{
      $SQL .= "Status='Running' order by ID";
    }
    
    $result = mysqli_query($msManager_link, $SQL);
    if($tmp_row = mysqli_fetch_assoc($result) ){
      $tmp_taskID = $tmp_row['ID'];
      $tmp_schedule = $tmp_row['Schedule'];
      $tmp_ProcessID = $tmp_row['ProcessID'];
      if($tmp_ProcessID) system("kill $tmp_ProcessID");
      
      $theURL = "http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["PHP_SELF"];
      $com = "$php_command_location " . __FILE__ ." ".$tableName. " ".$tmp_taskID." ". $theURL;
     
      if(defined('DEBUG_SEARCH') and DEBUG_SEARCH){
        echo "Prohits was stopped by administrator:<br> if you are Prohits admin copy following line and run it on the server shell for debug.<br>\n";
        echo "<font color=green>$com</font>";
        exit;
      }
      echo "Process ID:";
      $tmp_PID =  system($com." > /dev/null & echo \$!");
      $SQL = "update ". $tableName ."SearchTasks set ProcessID='".$tmp_PID."', Status='Running' where ID='".$tmp_taskID."'";
      mysqli_query($msManager_link, $SQL);
    }
    echo " auto-search is running in the background";
  }else{
    echo "no enough info. passed";
  }
  exit;
  //end web interface **********************************************
}else{
  $msg = "running on the shell. but no table name passed. the process stopped";
  fatalError($msg,  __LINE__);
}

//******************************************************************
///// shell script start from here /////////////////////////////////

checkLogSize($logfile, 2000);
$start_time = @date("Y-m-j G:i:s");
$taskTable = $tableName . "SearchTasks";
$resultTable = $tableName . "SearchResults";
$tableTppTasks = $tableName . "tppTasks";
$tableTppResults = $tableName . "tppResults";
$prohits_root = str_replace("msManager/autoSearch","",dirname(__FILE__));

if(getPhpProcess_arr($tableName, $frm_theTaskID) > 1){
  
  echo "the previous request is running.";
  exit;
}

$raw_file_pattern = str_replace(" ", "", $RAW_FILES);
$raw_file_pattern = str_replace(".", "\.", $raw_file_pattern);
$raw_file_pattern = '/\.' . str_replace(",", '$|\.', $raw_file_pattern) . '$/i';
 
check_manager_db_connection();

$sql = "SHOW TABLES FROM ".MANAGER_DB;
$results = mysqli_query($msManager_link, $sql);
$tmp_num = 0;
while($row = mysqli_fetch_row($results)){
  if($row[0] == $tableName or $row[0] == $taskTable or $row[0] == $resultTable) $tmp_num++;
}
if($tmp_num != 3){
 $e_msg = error_handle('', $msManager_link);
 fatalError("$e_msg \r\n $tableName, $taskTable, $resultTable (3) tables should be in database ". MANAGER_DB,  __LINE__);
}


 
$theTask_arr = get_running_task($frm_theTaskID);

if(!_is_dir($log_search_dir)){
  mkdir ($log_search_dir, 0777, true);
}

$GPM_datapath = get_local_gpm_archive_path($tableName, $frm_theTaskID);
$gpm_machine_dir = $GPM_datapath ."/". $tableName;
$taskDir         = $gpm_machine_dir."/task". $frm_theTaskID;
$statuslog       = $taskDir."/status.log";
$search_logfile  = $statuslog;
if(!_is_dir($taskDir)){
  mkdir($taskDir, 0775, true);
}else{
  system("chmod -R 775 $taskDir >/dev/null 2>&1"); 
}
writeLog("Machine Name:$tableName\nTask ID: $frm_theTaskID\nStart: $start_time", $search_logfile);

if($theTask_arr){
  $frm_theTaskID = $theTask_arr['ID'];
  $tmp_schedule = $theTask_arr['Schedule'];
  if($theTask_arr['AutoAddFile'] == 'Yes'){
    auto_add_files($theTask_arr);
  }
  $tmp_arr = explode(";", $theTask_arr['SearchEngines']); 
  foreach($tmp_arr as $en_str){
    $the_a = explode("=", $en_str); 
    array_push($searchEngine_arr, $the_a[0]);
  }
   
  
  if(in_array('DIAUmpire', $searchEngine_arr)){
    $is_SWATH_file = true;
    $SWATH_app = 'DIAUmpire';
  }else if(in_array('MSPLIT', $searchEngine_arr)){
    $is_SWATH_file = true;
    $SWATH_app = 'MSPLIT';
  }
  echo "is_SWATH_file=$is_SWATH_file;SWATH_app=$SWATH_app\n";
   
  
  
  
  $tmp_par_arr = explode("\n", $theTask_arr['Parameters']);
  
   
  
  foreach($tmp_par_arr as $par_str){
    $engine_par_arr = array();
    $the_p = explode("===", $par_str);
    if(count($the_p)<2) continue;
    if($the_p[0] == 'SEQUEST' or $the_p[0] == 'SearchAll'){
      $searchEngines_parameter_arr[$the_p[0]] = $the_p[1];
    }elseif($the_p[0] == 'COMET'){
      $searchEngines_parameter_arr['COMET'] =  create_comet_parameter_arr($the_p[1], $frm_theTaskID);
    }elseif($the_p[0] == 'MSFragger'){
      $searchEngines_parameter_arr['MSFragger'] =  create_MSFRAGGER_parameter_arr($the_p[1], $frm_theTaskID);
      
    }elseif($the_p[0] == 'MSGFPL'){
      $searchEngines_parameter_arr['MSGFPL'] = create_MSGFPL_parameter_arr($the_p[1], $frm_theTaskID);
    }elseif($the_p[0] == 'MSGFDB'){
      $searchEngines_parameter_arr['MSGFDB'] = create_MSGFPL_parameter_arr($the_p[1]);
    }elseif($the_p[0] == 'MSPLIT'){
      $searchEngines_parameter_arr['MSPLIT'] = str_replace("para_","", $the_p[1]);
    }else{
      $engine_par_arr = explode(";", $the_p[1]);
      $searchEngines_parameter_arr[$the_p[0]] = $engine_par_arr;
    }
  }
  
   //print_r($theTask_arr); exit;
  //print_r($searchEngine_arr);
  //print_r($searchEngines_parameter_arr);exit;
  //$searchEngines_parameter_arr['SearchAll'] ='database_name=HEK293RefV57cRapRev;;search_enzyme_number=1;;multiple_select_str=frm_variable_MODS|Deamidated (NQ):::Oxidation (M):::Phospho (ST):::Phospho (Y)&&frm_fixed_MODS|Carbamyl (N-term)&&frm_refinement_MODS|;;allowed_missed_cleavage=2;;num_enzyme_termini=0;;decoy_search=;;mass_type_parent=1;;mass_type_fragment=1;;peptide_mass_tolerance=50;;peptide_mass_units=2;;fragment_bin_tol=0.6;;use_NL_ions=1;;isotope_error=0;;CHARGE=1+, 2+ and 3+;;INSTRUMENT=Default;;';
  //multiple_select_str=frm_variable_MODS|Deamidated (NQ):::Oxidation (M):::Phospho (ST):::Phospho (Y)&&frm_fixed_MODS|Carbamyl (N-term)&&frm_refinement_MODS|
  //print_r($searchEngines_parameter_arr);exit;
  
 
  
  if($searchEngine_arr[0] == 'iProphet'){
     
    $default_comet_param_arr = get_comet_default_param("./");
    //print_r($default_comet_param_arr);exit;
    $searchs = explode(":", $searchEngine_arr[1]); 

    foreach($searchs as $theSearch){
      if(!trim($theSearch)) continue;
      if($theSearch == 'COMET'){
        $searchEngines_parameter_arr['COMET'] =  create_comet_parameter_arr($searchEngines_parameter_arr['SearchAll'], $frm_theTaskID, $default_comet_param_arr);
        if(!$searchEngines_parameter_arr['COMET']){
          writeLog("\nTable:$tableName; Task ID: ". $theTask_arr['ID']. "$prohits_error_msg" );
        }
      }else if($theSearch == 'GPM'){
        $searchEngines_parameter_arr['GPM'] =    create_gpm_parameter_arr($searchEngines_parameter_arr['SearchAll'], $frm_theTaskID, $default_comet_param_arr);
        if(!$searchEngines_parameter_arr['GPM']){
          writeLog("\nTable:$tableName; Task ID: ". $theTask_arr['ID']. "$prohits_error_msg" );
        }
      }else if($theSearch == 'Mascot'){
        $searchEngines_parameter_arr['Mascot'] = create_mascot_parameter_arr($searchEngines_parameter_arr['SearchAll'], $frm_theTaskID, $default_comet_param_arr);
        
        if(!$searchEngines_parameter_arr['Mascot']){
          writeLog("\nTable:$tableName; Task ID: ". $theTask_arr['ID']. "$prohits_error_msg" );
        }
      }else if($theSearch == 'MSGFPL'){
        $searchEngines_parameter_arr['MSGFPL'] = create_MSGFPL_parameter_arr($searchEngines_parameter_arr['SearchAll'], $frm_theTaskID, $default_comet_param_arr);
        if(!$searchEngines_parameter_arr['MSGFPL']){
          writeLog("\nTable:$tableName; Task ID: ". $theTask_arr['ID']. "$prohits_error_msg" );
        }
      }else if($theSearch == 'MSFRAGGER'){
        $searchEngines_parameter_arr['MSFragger'] = create_MSFRAGGER_parameter_arr($searchEngines_parameter_arr['SearchAll'], $frm_theTaskID, $default_comet_param_arr);
        if(!$searchEngines_parameter_arr['MSFragger']){
          writeLog("\nTable:$tableName; Task ID: ". $theTask_arr['ID']. "$prohits_error_msg" );
        }
      }
    }
    //print_r($searchEngines_parameter_arr);exit;
  }  
}else{
  writeLog("Table:$tableName no Running task. $start_time");
  exit;
} 

//get all raw files they are not searched.
$SQL = "SELECT S.WellID, S.SearchEngines, T.FileName, T.FolderID from ".$tableName." T, ".$resultTable." S 
     where T.ID=S.WellID  and TaskID='". $theTask_arr['ID'] . "' and (S.DataFiles is NULL or S.DataFiles='') order by S.WellID" ;
$results = mysqli_query($msManager_link, $SQL);
//echo $SQL;exit;

writeLog("\nTable:$tableName; Task ID: ". $theTask_arr['ID']. "; Start Time: $start_time; Raw data files: ". mysqli_num_rows($results) );
$SQL = "update $taskTable set StartTime=now() where ID='".$theTask_arr['ID']."'";
mysqli_query($msManager_link, $SQL);

$folder_path_arr = array(); // array('dirID'=>path_str, ...);
//$theTable_path = preg_replace("/\/$/", "", STORAGE_FOLDER) . "/" . $tableName. "/";
$fileCount=mysqli_num_rows($results);


if($is_SWATH_file and $SWATH_app == 'MSPLIT'){
  writeLog("Convert/Check Raw files:", $search_logfile);
}


$i = 0;
$msplit_file_arr = array();
//only run task output files
//if($is_SWATH_file and $SWATH_app == 'MSPLIT' and !$fileCount){
//  runMSPLIT($msplit_file_arr, $theTask_arr, $searchEngines_parameter_arr);
//}
while($row = mysqli_fetch_row($results)){
  $tmp_WellID = $row[0];
  $tmp_Engine = $row['1'];
  $tmp_file = $row['2'];
  $tmp_FolderID = $row[3];
  $tmp_dir_path = getFileDirPath($tableName, $tmp_WellID, $tmp_FolderID);
   
  echo "$tmp_dir_path, $tmp_WellID, $tmp_file, $tmp_Engine";
   
   
  //SWATH DDA file will use 'proteinpilot', other SWATH will use 'centroid' for WIFF files.
  $tmp_converted_file = checkFileFormat($tmp_dir_path, $tmp_WellID, $tmp_file, $tmp_Engine);
  //print_r($tmp_converted_file);exit;
  
  if($tmp_converted_file){
    $tmp_raw_file_path = $tmp_dir_path . $tmp_converted_file['fileName'];
    echo "\nfile format checked: ". $tmp_raw_file_path .": $tmp_Engine \n";
    echo "SWATH_app=$SWATH_app\n";
     
    if($is_SWATH_file){
      if($SWATH_app == 'DIAUmpire'){
        echo "it is SWATH file, run DIAUmpire\n";
         
        //************************************************************************************************************
        $OK = runDIAUmpire($tmp_raw_file_path, $tmp_WellID, $theTask_arr['DIAUmpire_parameters'], $tmp_converted_file, $theTask_arr['ID']);
        //************************************************************************************************************
        
        if(!$OK) continue;
      }else if($SWATH_app == 'MSPLIT'){
        $i++;
        $msplit_file_arr[] = array($tmp_WellID, $tmp_raw_file_path, $tmp_Engine);
        if($i < $fileCount) {
          continue;
        }else{
          
          //**********************************************************************
          runMSPLIT($msplit_file_arr, $theTask_arr, $searchEngines_parameter_arr);
          //**********************************************************************
          break;
        }
      }
    }
     
    
    $send_to_mascot = 1;
    if($tmp_Engine == 'Mascot'){
      if($is_SWATH_file){
        $mgf_file_base = preg_replace("/[.]mzXML([.]gz)?$/","", $tmp_converted_file['fileName']);
        $tmp_dir_path = dirname($linked_raw_file_path);
        $mascot_dat_file_str = '';
        for($i = 1; $i < 4; $i++){
          $tmp_raw_file_path = $tmp_dir_path. "/". $tmp_WellID. "_". $mgf_file_base."_Q".$i.".mgf";
          if(!_is_file($tmp_raw_file_path)){
            writeLog("cannot run mascot serach the mgf file doesn'et exist: $tmp_raw_file_path");
            break;
          }
          searchMascot($tmp_raw_file_path, $tmp_WellID, $searchEngines_parameter_arr['Mascot'], $theTask_arr['LCQfilter'], $theTask_arr['ID'], 'MGF', $i);
        }
      }else{
        searchMascot($tmp_raw_file_path, $tmp_WellID, $searchEngines_parameter_arr['Mascot'], $theTask_arr['LCQfilter'], $theTask_arr['ID'], $tmp_converted_file['type']);
      }
    }else if($tmp_Engine == 'GPM'){
       searchGPM($tmp_raw_file_path, $tmp_WellID, $searchEngines_parameter_arr['GPM'], $theTask_arr['LCQfilter'], $theTask_arr['ID'], $tmp_converted_file['type']);
    }else if($tmp_Engine == 'SEQUEST'){
       searchSEQUEST($tmp_raw_file_path, $tmp_WellID, $searchEngines_parameter_arr['SEQUEST'], $theTask_arr['ID'], $tmp_converted_file['type']);
    }else if($tmp_Engine == 'COMET'){
       searchCOMET($tmp_raw_file_path, $tmp_WellID, $searchEngines_parameter_arr['COMET'], $theTask_arr['ID'], $tmp_converted_file['type']);
    }else if($tmp_Engine == 'MSFragger'){
       searchMSFragger($tmp_raw_file_path, $tmp_WellID, $searchEngines_parameter_arr['MSFragger'], $theTask_arr['ID'], $tmp_converted_file['type']);
       
    }else if($tmp_Engine == 'MSGFPL'){
       searchMSGFPL($tmp_raw_file_path, $tmp_WellID, $searchEngines_parameter_arr['MSGFPL'], $theTask_arr['ID'], $tmp_converted_file['type']);
    }   
  
  }else{
    $fileCount--;
  }
}//end while loop



//cehck if set to run TPP after the search task/////////////////////////////////////////////////
if($theTask_arr['RunTPP']){
  $tpp_theURL = $theURL;
  $tpp_theURL = str_replace("autoSearch/auto_search_table_shell.php","",$tpp_theURL) . "tppTask/tpp_task_shell.php";
  $tpp_theURL .='?tableName='.$tableName.'&tppTaskID='.$theTask_arr['RunTPP'];
  
  $handle = fopen($tpp_theURL, "r");
  $tppmsg = '';
  if($handle){
    while (!feof($handle)) {
      $tppmsg .= fgets($handle, 4096);
    }
    echo "response from: $tpp_theURL\n";
    echo $tppmsg;
    fclose($handle);
  }else{
    $tppmsg = "Cannot open tpp URL $tpp_theURL";
  }
  writeLog($tppmsg);
}

////////////////////////////////run next task ////////////////////////////////////////////////////////////
if(isset($tmp_row['ProcessID'])) system("kill ".$tmp_row['ProcessID'] ." > /dev/null 2>&1 &");
$tmp_row = array();
$end_time = @date("Y-m-d G:i:s");
writeLog("Table:$tableName; Task ID: ". $theTask_arr['ID']. "; End Time: $end_time");
writeLog("End Time: $end_time", $statuslog);
//if($is_SWATH_file and $SWATH_app == 'MSPLIT'){
//  $tmp_row = get_running_task('', $theTask_arr['UserID']);
//}else{
  $SQL = "update $taskTable set Status='Finished' where ID='".$theTask_arr['ID']."'";
  mysqli_query($msManager_link, $SQL);
  $tmp_row = get_running_task('', $theTask_arr['UserID']);
//}
if($tmp_row){
  $tmp_taskID = $tmp_row['ID'];
  echo "Process ID:";
  $com = "$php_command_location " . __FILE__ ." ".$tableName. " ".$tmp_taskID." ". $theURL;
   
  $tmp_PID =  system($com." > /dev/null & echo \$!");
  
  $SQL = "update ". $tableName ."SearchTasks set ProcessID='".$tmp_PID."', Status='Running' where ID='".$tmp_taskID."'";
  echo "the waiting task is running: $SQL\n";
  mysqli_query($msManager_link, $SQL);
  writeLog("Table:$tableName; Task ID: ". $tmp_taskID. " was started after task $frm_theTaskID.");
}
exit;


//************************************************
// return taske record array
//************************************************
function get_running_task($frm_theTaskID ='', $UserID=''){
  global $msManager_link;
  global $taskTable;
  $SQL = "SELECT ID, PlateID, DataFileFormat, SearchEngines, Parameters, DIAUmpire_parameters, TaskName, LCQfilter, Schedule, StartTime, 
        AutoAddFile, RunTPP, Status, ProcessID, UserID, ProjectID
        FROM ". $taskTable." where ";
  if($frm_theTaskID){
    $SQL .= "ID='$frm_theTaskID'";
  }else{
    $SQL .="Status='Waiting' and UserID='$UserID'"." order by ID ";
  }
   
  $result = mysqli_query($msManager_link, $SQL);
  $theTask_arr = mysqli_fetch_array($result);
  return $theTask_arr;
}
//
//************************************************
//
//************************************************
function check_manager_db_connection(){
  global $msManager_link;
  if(!mysqli_ping($msManager_link)){
    mysqli_close($msManager_link);
    $msManager_link = mysqli_connect(PROHITS_SERVER_IP, USERNAME, DBPASSWORD, MANAGER_DB ) or fatalError("Unable to connect to mysql..." . mysqli_error($msManager_link));
  }
}
function error_handle($msg = '', $db_link){
  if(mysqli_errno($db_link) ==  "1062"){
    return '';
  }else{
    $msg .=  "\nerror #" . mysqli_errno($db_link);
    $msg .= "\nmysql error: " . mysqli_error($db_link);
    $msg .= "\nScript Name: " . __FILE__;
  } 
  return $msg;
}
//**********************************************
// it will run the table backup first.
//**********************************************
function auto_add_files($theTask_arr){
  global $msManager_link;
  global $tableName ;
  global $resultTable;
  global $tableTppResults;
  
  writeLog("Get new created files from $tableName machine to Prohits.");
  $com_backup = PHP_PATH. " " .dirname(dirname(__FILE__))."/autoBackup/raw_backup_shell.php"." 0 ". $tableName;
  system($com_backup);
  
  $taskID = $theTask_arr['ID'];
  $fileFormat = $theTask_arr['DataFileFormat'];
  $dirID_str = $theTask_arr['PlateID'];
  $tppID = $theTask_arr['RunTPP'];
  
  $frm_tppID_str = '';
  $search_engine_arr = array();
  $tmp_en_arr = explode(";", $theTask_arr['SearchEngines']);
  foreach($tmp_en_arr as $tmp_en){
    $tmp_set = explode("=", $tmp_en);
    array_push($search_engine_arr, $tmp_set[0]);
  } 
  $SQL = "select ID from ".$tableName." where FolderID in(".$dirID_str.") and FileType='".$fileFormat."'";
  $results = mysqli_query($msManager_link, $SQL);
  while($row = mysqli_fetch_row($results)){
    $SQL ="INSERT INTO ".$resultTable." SET 
            WellID='".$row['0']."', 
            TaskID='".$taskID."', 
            SearchEngines=";
    foreach($search_engine_arr as $tmp_en){
      $tmp_SQL = $SQL . "'$tmp_en'";
      if(mysqli_query($msManager_link, $tmp_SQL)){
        //add records to tpp results table.
        if($tppID){
          $tmp_SQL = "INSERT INTO `$tableTppResults` set 
          `WellID`='".$row['0']."',
          `TppTaskID`='".$tppID."', 
          `SearchEngine`='".$tmp_en."'";
          mysqli_query($msManager_link, $tmp_SQL);
        }
      }
    }
  }
}
?>