<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

$logfile = '../../logs/raw_back.log';
$backup_script_name = 'raw_backup_shell.php';


$start_time = @date("Y-m-j G:i:s");
$phpProcess_arr = array(); //$phpProcess_arr['PID']['time']['script']['arg1']
$table_backup_is_running = false;
//change cwd to this directory
chdir(dirname(__FILE__));

include('../../config/conf.inc.php');
include('./shell_functions.inc.php');
include_once('../is_dir_file.inc.php');
 
$php_command_location = PHP_PATH;
$phpProcess_arr = getPhpProcess_arr();

if(array_key_exists('REQUEST_METHOD', $_SERVER)){
  if( $_SERVER['REQUEST_METHOD'] == "POST"){
    $request_arr = $_POST;
  }else{
    $request_arr = $_GET;
  }
  foreach ($request_arr as $key => $value) {
    $$key=$value;
  }
  if(isset($SID) and isset($tableName)){ 
    if(!check_permission($SID,$tableName)) {

      echo "You have not Auto Search permission";
      exit;
    }
  }else{
    echo "You have to login Prohits before using the function";
    exit;
  }
}else{
  exit;
}
//print_r($phpProcess_arr);

$process_log_line='';
if(!is_file($logfile)){
 $log = fopen($logfile, 'a+');
 if(!$log){
  echo "<font color=red>Error: cannot write log file $logfile.</font>";exit;
 }else{
  fclose($log);
 }
}

$backup_log_contents = file($logfile);
$linenumber = sizeof($backup_log_contents)-1;
$backup_PSID = 0;
$backup_is_running = false;
while($linenumber > 0){
  if(preg_match('/^PSID:([0-9]+)/', $backup_log_contents[$linenumber], $matchs) ){
    $backup_PSID = $matchs[1];
    $process_log_line = $backup_log_contents[$linenumber];
     
    break;
  }
  $linenumber--;
}
unset($backup_log_contents,$linenumber);

$msg = '';
if($backup_PSID and isset($phpProcess_arr[$backup_PSID]) and isset($tableName)){
  if(strstr($phpProcess_arr[$backup_PSID]['script'], $backup_script_name) ){
    if(trim(str_replace("PSID:$backup_PSID", '', $process_log_line)) == $tableName){
      $table_backup_is_running = true;
      $msg = "bakup program has been started by other user in the background";
    }
  }
}
if(!$table_backup_is_running){
  //if($debug){
  if(defined('DEBUG_BACKUP') and DEBUG_BACKUP){
		echo "this script is set to debug. only admin can do this: login shell then run following command<br>";
		echo  "$php_command_location $backup_script_name 0 ".$tableName;
		exit;
	}
  echo "Shell process ID:";
  $cmd = "$php_command_location $backup_script_name 0 ".$tableName." > /dev/null & echo \$!";
	
  $tmp_PID =  system($cmd);
  $logfile = $logfile;
  checkLogSize($logfile, 1000);
  writeLog("PSID:$tmp_PID $tableName", $logfile);
  writeLog("Backup $tableName start at $start_time", $logfile);
  $msg = "Backup $tableName started at $start_time";
}
echo "<pre>
  <center>
  <form>
  <font color=red>$msg</font><br>
  Please click the log file button to see the progress status.<br>
  <input type=button value='Log File' onClick=\"document.location = '../../logs/log_view.php?log_file=raw_back.log'\">
</form></pre>"
?>