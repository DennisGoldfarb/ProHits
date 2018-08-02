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
ini_set("default_socket_timeout", "10000");

$tableName = '';
$logfile = '';
$log_search_dir = '../../logs/DIAUmpire_Quant/';
$theTask_arr = array(); //Umpire Quant task
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
$search_task_arr;
$tpp_formaction = '';

//change cwd to this directory
chdir(dirname(__FILE__));

include('../../config/conf.inc.php');
$gpm_ip = $PROHITS_IP;
$tpp_ip = $PROHITS_IP;
include("../../common/mysqlDB_class.php");
include('../autoBackup/shell_functions.inc.php');
include('./auto_search_umpire.inc.php');
require("../common_functions.inc.php");
@require_once("../../common/HTTP/Request_Prohits.php");
require_once('../is_dir_file.inc.php');
require_once("../tppTask/tpp_task_shell_fun.inc.php");



$php_command_location = PHP_PATH;
//maybe it is remote connect prohits server---------
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$managerDB = new mysqlDB(MANAGER_DB);
$msManager_link  = $managerDB->link;
mysqli_query($msManager_link, "SET SESSION sql_mode = ''");
$PROHITSDB = new mysqlDB(PROHITS_DB);
$prohits_link  = $PROHITSDB->link;
mysqli_query($prohits_link, "SET SESSION sql_mode = ''");



//if(defined('STORAGE_IP')) 
if(isset($_SERVER['argv']) and count($_SERVER['argv']) > 1){
  $umpireQuant_ID = $_SERVER['argv'][1];
  if(isset($_SERVER['argv'][2])){
		$theURL = $_SERVER['argv'][2];
    $frm_theURL = preg_replace("/autoSearch.+$/","",$theURL) . "autoBackup/download_raw_file.php";
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
  if(isset($SID) and isset($umpireQuant_ID) and check_permission($SID,$tableName)) {
    $theURL = "http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["PHP_SELF"];
    $com = "$php_command_location " . __FILE__ ." ".$umpireQuant_ID." ". $theURL;
    if(defined('DEBUG_DIAUmpireQuant') and DEBUG_DIAUmpireQuant){
      echo "Prohits DIAUmpire QUANT was stopped by administrator: if you are Prohits admin, copy following line and run it on the server shell to debug.<br>\n";
      echo "<font color=green>$com</font>";
      exit;
    }
    echo "2. Process ID:";
    $tmp_PID =  system($com." > /dev/null & echo \$!");
    $SQL = "update DIAUmpireQuant_log set Status = 'Running', ProcessID = 'LOCAL_".$tmp_PID."' where ID = '$umpireQuant_ID'";
    mysqli_query($prohits_link, $SQL);
    echo "DIAUmpireQuant is running in the background.";
  }else{
    echo "no enough info. passed";
  }
  exit;
  //end web interface **********************************************
}else{
  $logfile = $log_search_dir.'error.log';
  $msg = "running on the shell. but no enough information passed";
  fatalError($msg,  __LINE__);
}

//******************************************************************
///// shell script start from here /////////////////////////////////

//checkLogSize($logfile, 2000);
$start_time = @date("Y-m-j G:i:s");

$logfile = $log_search_dir.$umpireQuant_ID.'_status.log';

if(!_is_dir( $log_search_dir)){
  umask(0002);
  mkdir("$log_search_dir",  0777, true);
}

$prohits_root = str_replace("msManager/autoSearch","",dirname(__FILE__));
$SQL = "SELECT `ID`, `Name`, `UserID`, `Date`, `Description`, `Machine`, `SearchEngine`, `TaskIDandFileIDs`, `Status`, `ProjectID`, `UserOptions`, `ParentQuantID`, `ProcessID` FROM `DIAUmpireQuant_log`";
$SQL .= " WHERE ID=$umpireQuant_ID";
 
$theTask_arr = $PROHITSDB->fetch($SQL);
$frm_machine = $theTask_arr['Machine'];
$tableName = $frm_machine;
$frm_SearchEngine = $theTask_arr['SearchEngine'];
$frm_selected_list_str = $theTask_arr['TaskIDandFileIDs'];
$ParentQuantID = $theTask_arr['ParentQuantID'];
$allOptions = $theTask_arr['UserOptions'];

$SAINT = '';
$QUANT = '';
$SAINT_control_id_str = '';
$SAINT_bait_name_str = '';
$mapDIA = '';
$SAINT_or_mapDIA = '';
$REMOVE_SHARED_PEPTIDE_GENE = '';

$tmpOption_arr = explode("\n", $allOptions);
foreach($tmpOption_arr as $line){
  $tmp_op_arr = explode("=", $line, 2);
  if(count($tmp_op_arr) == 2){
    $$tmp_op_arr[0] = $tmp_op_arr[1];
  }
}

echo "\numpireQuant_ID=$umpireQuant_ID, \nParentQuantID=$ParentQuantID\n$frm_machine, \n$frm_SearchEngine, \n$frm_selected_list_str, \n$SAINT,\n$QUANT, \n$SAINT_control_id_str, \n$SAINT_bait_name_str, \n$mapDIA, \n$SAINT_or_mapDIA\n$REMOVE_SHARED_PEPTIDE_GENE";
 
$process_info = runDIAUmpire_Quant($umpireQuant_ID, $frm_machine, $frm_SearchEngine, $frm_selected_list_str, $SAINT, $QUANT, $SAINT_control_id_str, $SAINT_bait_name_str, $mapDIA, $SAINT_or_mapDIA, $REMOVE_SHARED_PEPTIDE_GENE, $ParentQuantID);
$SQL = "update DIAUmpireQuant_log set Status = '".$process_info['Status']."', ProcessID = '".$process_info['ProcessID']."' where ID = '$umpireQuant_ID'";
if(!mysqli_ping($PROHITSDB->link)) {
  $PROHITSDB = new mysqlDB(PROHITS_DB);
  $prohits_link  = $PROHITSDB->link;
  mysqli_query($prohits_link, "SET SESSION sql_mode = ''");
}
$PROHITSDB->update($SQL);
exit;
?>