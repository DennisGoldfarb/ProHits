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

error_reporting(E_STRICT|E_ALL);
set_time_limit(0);
//ini_set("memory_limit","-1");
ini_set("default_socket_timeout", "1000");

$logfile = '../logs/search.log';

$tableName = '';
$theTask_arr = array();
$theTaskID = 0;
$frm_theTaskID = '';
$is_SWATH_file = false;
$SWATH_app = '';
$status = '';
$results_folder_path = '';

include("./ms_permission.inc.php"); 
require("./is_dir_file.inc.php");
require("./common_functions.inc.php");
require("./autoBackup/shell_functions.inc.php");

$GPM_datapath = get_local_gpm_archive_path($tableName, $taskID);
$gpm_machine_dir = $GPM_datapath ."/". $tableName;
$taskDir         = $gpm_machine_dir."/task". $taskID;
$statuslog       = $taskDir."/status.log";
$taskTable = $tableName . "SearchTasks";

$SQL = "SELECT ID, PlateID, DataFileFormat, SearchEngines, Parameters, DIAUmpire_parameters, TaskName, LCQfilter, Schedule, StartTime, 
        AutoAddFile, RunTPP, Status, ProcessID, UserID, ProjectID
        FROM ". $taskTable." where ID='$taskID'";
   
$theTask_arr = $managerDB->fetch($SQL);

echo "<pre>";
if(_is_file($statuslog)){
  echo file_get_contents($statuslog);
}
if(task_is_running($tableName,  $taskID)){
  print "\n<h2>>>>Task is running<<<</h2>";
}else{
  if($theTask_arr['Status'] == 'Finished'){
    print "\n<h2>>>>Finished.<<<</h2>";
  }else{
    print "\n<h2>>>><font color=red>ERROR:</font> Task is not running. Please view search log for detail.<<<</h2>";
  }
}
echo "</pre>";

?>