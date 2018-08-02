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
  8. shell > php auto_search_table.php "arg\" should add slash"
  9. check shell process 
     shell > ps -o lstart PID
*************************************************************************/
set_time_limit(0);
ini_set("memory_limit","-1");
//ini_set("default_socket_timeout", "1000");


$tableName = '';
$schTaskID = 0;
$tppTaskID = 0;
$USER = array();

$condition_file = '';
$tppLogDir = '../../logs/TPP/';
$logfile = $tppLogDir.'tpps.log'; 
 
$raw_file_source_dir = '';
$msg = '';
$frm_theURL = '';


 

//change cwd to this directory
$shell_script_dir = dirname(__FILE__);
chdir($shell_script_dir);

include('../../config/conf.inc.php'); 
include("../../common/mysqlDB_class.php");
include("../../common/user_class.php");
@require_once "../../common/HTTP/Request_Prohits.php";
include('../autoBackup/shell_functions.inc.php');
include("./tpp_task_shell_fun.inc.php");
require("../common_functions.inc.php");
 
include ( "../is_dir_file.inc.php");


$php_command_location = PHP_PATH;
$managerDB = new mysqlDB(MANAGER_DB);

if(isset($_SERVER['argv']) and count($_SERVER['argv']) > 1){
  if($_SERVER['argv'] < 4){
    echo "Usage: thisScritp searchTaskID tppTaskID";exit;
  }
  $tableName = $_SERVER['argv'][1];
  $tppTaskID = $_SERVER['argv'][2];
  $theURL = $_SERVER['argv'][3];
  $frm_theURL = str_replace("tppTask/tpp_task_shell.php","",$theURL) . "autoBackup/download_raw_file.php";
  $tableTppTasks = $tableName . "tppTasks";
  $tableTppResults = $tableName . "tppResults";
  
  $PROHITS_IP = PROHITS_SERVER_IP;
  if(preg_match("/http:\/\/([^:|\/]+)/", $theURL, $matches)){
    $PROHITS_IP = $matches[1];
  }
 
  $gpm_ip = $PROHITS_IP;
  $tpp_ip = $PROHITS_IP;
  $storage_ip = $PROHITS_IP;
    
}else if(array_key_exists('REQUEST_METHOD', $_SERVER)){
  $PROHITS_IP = $_SERVER["SERVER_NAME"];
  $gpm_ip = $PROHITS_IP;
  $tpp_ip = $PROHITS_IP;
  $storage_ip = $PROHITS_IP;
  
  
  session_start();
  if(!isset($_SESSION["USER"])){
    //echo "please login prohits"; exit;
  }else{
    $USER = $_SESSION["USER"]; 
  }
  if( $_SERVER['REQUEST_METHOD'] == "POST"){
    $request_arr = $_POST;
  }else{
    $request_arr = $_GET;
  }
  foreach ($request_arr as $key => $value) {
    $$key=$value;
  }
  $tableTppTasks = $tableName . "tppTasks";
  $tableTppResults = $tableName . "tppResults";
  //echo "$tableName = search task = $schTaskID tpp task = $tppTaskID";
  if(isset($kill)){
     system("kill $kill");
     exit;
  }
  
  $noProcessTask = true;
  /*
  //check if there are any running task.
  $theTppTask = getTppTask($tableTppTasks, 0, 0, $status='Running');
  if($theTppTask and $theTppTask['ProcessID']){
    echo $theTppTask['ProcessID'];
    exec("ps u ".$theTppTask['ProcessID'] ."| grep tpp_task_shell.php", $output);
    if($output){
      $msg = "There is a running  TPP task (task id=".$theTppTask['ID']."). This task will be in the waiting list.";
      $noProcessTask = false;
    }else{
      updateTppTaskStatus($tableTppTasks, $theTppTask['ID'], 'Stopped', $theTppTask['ProcessID']);
    }
  }
  */
  $theTppTask = array();
  if($noProcessTask){
      $theTppTask = getTppTask($tableTppTasks, $tppTaskID);
      if(!$theTppTask){
        echo "Error: no task id ($tppTaskID ) found in  $tableTppTasks";exit;
      }
      $theURL = "http://".$storage_ip.":".$_SERVER["SERVER_PORT"].$_SERVER["PHP_SELF"];
      processTppTask($theTppTask, __FILE__, $theURL);
  }
  exit;
}else{
  $msg = "running on the shell. but no table name passed. the process stopped";
  fatalError($msg,  __LINE__);
}
//////////////// shell processing ////////////////////////////////////////////
checkLogSize($logfile, 2000);
$start_time = @date("Y-m-j G:i:s");

$http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
$http_mascot_cgi_dir = "http://" . MASCOT_IP . MASCOT_CGI_DIR;
$http_sequest_cgi_dir = "http://" . SEQUEST_IP . SEQUEST_CGI_DIR;
$tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";


  
  

if(!mysqli_ping($managerDB->link)){
  $managerDB = new mysqlDB(MANAGER_DB);
}
$msManager_link = $managerDB->link;
$theTppTask = getTppTask($tableTppTasks, $tppTaskID);
$theTppResults = fetchAllTppResult($tableTppResults, $tppTaskID);
if(!$theTppResults){
  writeLog("No tpp task found: in $tableTppTasks for ID $tppTaskID");exit;
}
$schTaskID = $theTppTask['SearchTaskID'];
if(!_is_dir($tppLogDir)) mkdir($tppLogDir, 0755, true);
//$logfile = $tppLogDir.'tpps'.$schTaskID.'.log'; 

writeLog("\r\nTPP task start: $tppTaskID -- $tableName searchTaskID=$schTaskID -- $start_time");
if(!$theTppTask or !$theTppResults){
  fatalError("no tpp task found for id $tppTaskID in table $tableTppTasks",  __LINE__);
  exit;
}
//$param_str = createParameterString($theTppTask['Parameters']);
//preg_match("/ -L(.+\.xml)/", $param_str, $matchs);
//if(count($matchs) == 2) $condition_file = $matchs[1];

$search_task_arr = get_search_task($schTaskID);
$theTask_arr = $search_task_arr;

$tpp_in_prohits = is_in_local_server('TPP');
if(!$tpp_in_prohits){
  fatalError("TPP_BIN_PATH is not correct in conf file.",  __LINE__);
  exit;
}

$sorted_tpp_result_arr = array();
foreach($theTppResults as $row){
  if($row['pepXML'] or $row['protXML']) continue;
  $sorted_tpp_result_arr[$row['WellID']][] = $row['SearchEngine'];
}
ksort($sorted_tpp_result_arr);

 
//print_r($search_task_arr);
//print_r($theTppTask);exit;
//print_r($sorted_tpp_result_arr);exit;

$philosopher_cmd = '';
if(defined("PHILOSOPHER_BIN_PATH")){
  $the_cmd = add_folder_backslash(PHILOSOPHER_BIN_PATH)."philosopher";
  if(is_file($the_cmd)){
    $philosopher_cmd = $the_cmd;
  }
}
 
foreach($sorted_tpp_result_arr as $WellID=>$Engine_arr){
  $run_iProphet = 0;
  foreach($Engine_arr as $SearchEngine){
    if($SearchEngine == 'iProphet'){
      $run_iProphet = 1;
    }else{
      echo "Run TPP serach engine: $SearchEngine\n";
      $param_arr = createParameterString($theTppTask['Parameters'], $SearchEngine);
      //echo $theTppTask['Parameters'];
      //print_r($param_arr);exit;
      
      echo "run TPP:$tableName, $schTaskID, $tppTaskID, $WellID, $SearchEngine for parameter:\n";
      
      runTPP($tableName, $schTaskID, $tppTaskID, $WellID, $SearchEngine, $condition_file);
      
    }
  }
  
  if($run_iProphet){
    //check all has tpp results
    $have_all_tpp_results = 1;
    $WellTppResults = fetchAllTppResult($tableTppResults, $tppTaskID, $WellID);
    $searhEngine_pepXML_str = '';
    $count = 0;
    
    foreach($WellTppResults as $row){
      if((!$row['pepXML'] or $row['pepXML']=='NoPepXML') and $row['SearchEngine'] != 'iProphet'){
        $msg = "Warning: $tableName iProphet may not completed for raw file ID: $WellID. (Search taskID: $schTaskID TPP ID: $tppTaskID) -- " . @date("Y-m-j G:i:s");
        $msg .= "\n".$row['SearchEngine']. " has no TPP results.";
        writeLog($msg);
        $have_all_tpp_results = 0;
        //break;
      }else if($row['SearchEngine'] != 'iProphet'){
        $searhEngine_pepXML_str .= ($searhEngine_pepXML_str)? ";".$row['pepXML']:$row['pepXML'];
        $count++;
      }
    }
    if($count > 1){
      $param_arr = createParameterString($theTppTask['Parameters'], 'iProphet');
      
      echo "$searhEngine_pepXML_str\n";
      echo $theTppTask['Parameters'];
      //print_r($param_arr);exit;
      runTPP($tableName, $schTaskID, $tppTaskID, $WellID, 'iProphet', $condition_file, $searhEngine_pepXML_str);

    }
     
  }
}






updateTppTaskStatus($tableTppTasks, $tppTaskID, 'Finished', $theTppTask['ProcessID']);
writeLog("End TPP task: $tppTaskID -- " . @date("Y-m-j G:i:s"));
//get next waiting task
//$theTppTask = getTppTask($tableTppTasks, 0, 0, 'Waiting');
//if($theTppTask) processTppTask($theTppTask, __FILE__, $theURL);

//echo "\nparameter=$param_str";
//echo "\nconditioni_file = $condition_file";
echo "\n";
?>
