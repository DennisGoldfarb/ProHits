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
require("./is_dir_file.inc.php");

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}

require("./common_functions.inc.php");
require_once("./autoBackup/shell_functions.inc.php");
$is_running = getPhpProcess_arr($tableName, $taskID, $PID);
 
$log_search_dir = '../logs/searchs/';
$search_logfile = $log_search_dir .$tableName."_". $taskID. ".log";
 
if(_is_file($search_logfile)){
  
  echo file_get_contents($search_logfile);
}
if($is_running){
  echo "\n>>> It is running in Prohits <<<";
}else{
  echo "\n>>><font color=red>ERROR:</font> It is not running in Prohits. Please check log file for detail. <<<";
}
?>