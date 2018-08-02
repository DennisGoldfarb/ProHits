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

 
$searchLogfile = '../logs/search.log';
$backupLogFile = '../logs/raw_back.log';
$autorunLogFile = '../logs/auto_run.log';

$backup_script_name = 'raw_backup_shell.php';
$backup_script = './autoBackup/' . $backup_script_name;
$search_script_name = 'auto_search_table_shell.php';
$search_script = './autoSearch/' . $search_script_name;
$start_time = @date("Y-m-j G:i:s");

$taskTableNames_arr = array();
$phpProcess_arr = array(); //$phpProcess_arr['PID']['time']['script']['arg1']
//$theTask_arr = array();
//$searchEngine_arr = array();
//$searchEngines_parameter_arr = array();
//$theEngine = '';
//$theTaskID = 0;
//$raw_file_source_dir = '';

//change cwd to this directory
chdir(dirname(__FILE__));

include('../config/conf.inc.php');
include("./is_dir_file.inc.php");
include("./common_functions.inc.php");
include("./autoBackup/shell_functions.inc.php");
require ('../common/PHPMailer-master/PHPMailerAutoload.php');
require ('../common/common_fun.inc.php');
$phpProcess_arr = getPhpProcess_arr();

//******************************************************************************
//display php processes. required from web.
if(array_key_exists('REQUEST_METHOD', $_SERVER)){
  $tableName = '';
  $taskID = '';
  
  $output = 'No';
  if( $_SERVER['REQUEST_METHOD'] == "POST"){
    $request_arr = $_POST;
  }else{
    $request_arr = $_GET;
  }
  foreach ($request_arr as $key => $value) {
    //$queryString = "tableName=$tableName"; "&taskID=$taskID"; "&isTPP=yes"
    $$key=$value;
  }
  //check if $tablename has running task.
  if($tableName){
    foreach($phpProcess_arr as $tmp_ps_arr){
      if(!isset($tmp_ps_arr['Machine']))continue;
      if($tmp_ps_arr['Machine'] == $tableName){
        if(isset($tmp_ps_arr['script'])){
          if(isset($isTPP)){
            if(strpos($tmp_ps_arr['script'], 'tpp_task_shell.php')){
              if($taskID){
                if($taskID == $tmp_ps_arr['TaskID']){
                  $output = '>>Yes<<';
                  break;
                }
              }else{  
                $output = '>>Yes<<';
                break;
              }
            }
          }else if(strpos($tmp_ps_arr['script'], 'auto_search_table_shell.php')){
            if($taskID){
              if($taskID == $tmp_ps_arr['TaskID']){
                $output = '>>Yes<<';
                break;
              }
            }else{  
              $output = '>>Yes<<';
              break;
            }
          }
        }
      }
    }
    echo $output;
  }else{
    echo "<HTML>
    <HEAD><SCRIPT LANGUAGE=\"JavaScript\">
    <!-- Begin hiding Javascript from old browsers.
    var theWait = 30000;
     function reloadMe(){
    window.location.reload(true);
    }
    //-- End hiding Javascript from old browsers. -->
    </SCRIPT><TITLE>Process status</TITLE></HEAD>
    <BODY onLoad=\"timerID=setTimeout('reloadMe()', theWait)\">
    ";
    echo "<h2>Prohits background process status</h2>";
    
    foreach($phpProcess_arr as $key => $value){
      echo "<br><b>Process ID: $key</b><br>";
      foreach($value as $tmp_key => $tmp_value){
        if($tmp_key == 'StartEveryHours') $tmp_value = $tmp_value / 3600;
        echo "$tmp_key: <font color='#008080'>$tmp_value</font> ";
      }
    }
    if(!$phpProcess_arr){
      echo "<b><font color=red>No search task is running.</font></b>";
    }
    echo "</body></html>";
  }
  exit;
}


//**********************************************************
if(count($_SERVER['argv']) > 1){
  if($_SERVER['argv'][1] == 'qstat' and $_SERVER['argv'][2]){
    $cmd = "echo '/home/slave/TEST_qsub/testCluster.bash' | sudo -u slave '/opt/gridengine/bin/linux-x64/qsub' -wd '/home/slave/TEST_qsub' -o 'localhost:/home/slave/TEST_qsub' -j y 2>&1";
    exec($cmd, $output);
    print_r($output);
    exit;
  }else if($_SERVER['argv'][1] != 'connect'){
    echo "Usage: php ".__FILE__." connect\n";exit;
  }
}
mount_msComputers();
//*********************************************************
if(count($_SERVER['argv']) > 1){
  if($_SERVER['argv'][1] == 'connect'){
    exit;
  }
}
sleep(20);

//print_r($phpProcess_arr);
//run backup *******************************************************************
if(is_file($backupLogFile)){
  $backup_log_contents = file($backupLogFile);
  $linenumber = sizeof($backup_log_contents)-1;
  $backup_PSID = 0;
  $backup_is_running = false;
  while($linenumber > 0){
    if(preg_match('/^PSID:([0-9]+)/', $backup_log_contents[$linenumber], $matchs) ){
      $backup_PSID = $matchs[1];
      break;
    }
    $linenumber--;
  }
  if($backup_PSID and isset($phpProcess_arr[$backup_PSID])){
    if(strstr($phpProcess_arr[$backup_PSID]['script'], $backup_script_name) ){
      $backup_is_running = true;
      $logfile = $autorunLogFile; 
      writeLog("running: " . $phpProcess_arr[$backup_PSID]['script']. "\r\n" . @date("Y-m-j G:i:s"));
    }
  }
  if(!$backup_is_running){
    $logfile = $autorunLogFile;
    $cmd = PHP_PATH . " " . $backup_script." > /dev/null & echo \$!";
    writeLog($cmd . "\r\n" . @date("Y-m-j G:i:s"));
    $tmp_PID =  system($cmd);
    $logfile = $backupLogFile;
    writeLog("PSID:$tmp_PID\r\n" . @date("Y-m-j G:i:s"));
  }
  unset($backup_log_contents,$linenumber);
}else{
  $logfile = $autorunLogFile;
  $msg = "Warning : $backupLogFile doesn't exist.";
  writeLog($msg);
}
exit;


//----------------------------------------------
function fatalError($msg='', $line=0){
//----------------------------------------------
  global $start_time;
  $msg  = "Fatal Error--$msg;";
  $msg .=  " Script Name: " . $_SERVER['PHP_SELF']. ";";
  $msg .= " Start time: ". $start_time . ";";
  if($line){
    $msg .= " Line number: $line;";
  }
  writeLog($msg);
  exit;
}
//---------------------------------------------
function writeLog($msg){
//---------------------------------------------
  global $logfile; 
  $log = fopen($logfile, 'a+');
  fwrite($log, "\r\n" . $msg);
  fclose($log);
  //echo "write to $logfile: '".$msg ."'";
}

//--------------------------------------------
function mount_msComputers(){
/*
#! /bin/bash
#1.  This shell script is used to check mounted mass spectrometer computers
#    from mass spec lab.
#2.  Please make sure Prohits admin email address 'ADMIN_EMAIL' is correct in conf file.
#3.  Run auto_run_shell.php from root user. It will read conf.inic.php file and 
     automatically mount mss spec computers. Fix errors if there are any.
     
 	    >sudo /usr/bin/php /var/www/html/Prohits/msManager/auto_run_shell.php connect
      
#4.	Login to the ProHits computer using sudo user and add a cron job for root 
    using the following line

	    >sudo crontab -eu root
    (if you login as root)
	    >crontab -e
    Add the following lines into crontab. This will set backup procedures to 
    run automatically every day at 23:20 server time.
 
########### start root cron ###########################################
#check php command path and Apache document root then modify following line.
#Usage:   http://en.wikipedia.org/wiki/Crontab
20 23 * * * /usr/bin/php /var/www/html/Prohits/msManager/auto_run_shell.php > /dev/null 2>&1 
############ end ######### #############################################


array(
'SOURCE'=>'/mnt/LTQ1/', 
'DEFAULT_PROJECT_ID'=>'17',
'SOURCE_COMPUTER'=>array(
    'ADDRESS'=>'LTQ10269.ad.mshri.on.ca',
    'RAW_DATA_FOLDER'=>'MSdata',
    'SHARED_TO_USER'=>'msusers',
    'SHARED_TO_USER_PASSWD'=>'msuser',
    'WINDOWS_ACTIVE_DIRECTORY'=>'slri_lan1'
));
*/
  global $BACKUP_SOURCE_FOLDERS;
  global $DOWNLOAD_SHARED_FOLDER;
  if($DOWNLOAD_SHARED_FOLDER){
    $BACKUP_SOURCE_FOLDERS['DOWNLOAD_SHARED_FOLDER'] = $DOWNLOAD_SHARED_FOLDER;
  }
  $error="";
  foreach($BACKUP_SOURCE_FOLDERS as $key => $value){
    if($value['SOURCE'] and isset($value['SOURCE_COMPUTER']['ADDRESS']) and $value['SOURCE_COMPUTER']['ADDRESS']){
      print "\nConnecting $key:\n";
      $MS_arr = $value['SOURCE_COMPUTER'];
      $source_dir = $value['SOURCE'];
      $MSname = $key;
      $cmd = "umount -l $source_dir";
      system($cmd);
      if(!_is_dir($source_dir)){
         @mkdir("$source_dir", 0755);
      }
      if(!ping($MS_arr['ADDRESS'])){
        $the_msg = "\nThe computer $MSname (".$MS_arr['ADDRESS'].") cannot be pinged. Make sure that the computer is on.\n";
        $error .= $the_msg;
        echo $the_msg;
        continue;
      }
      $cmd = "mount -o noserverino -t cifs //";
      $cmd .= $MS_arr['ADDRESS'];
      $cmd .= "/".$MS_arr['RAW_DATA_FOLDER'] . " $source_dir";
      $cmd .= " -o user=". $MS_arr['SHARED_TO_USER']. ",password='".$MS_arr['SHARED_TO_USER_PASSWD']."'";
      if(isset($MS_arr['WINDOWS_ACTIVE_DIRECTORY']) and $MS_arr['WINDOWS_ACTIVE_DIRECTORY']){
        $cmd .= ",domain=".$MS_arr['WINDOWS_ACTIVE_DIRECTORY'];
      }
      print $cmd."\n";
      system($cmd);
      if(is_empty_dir("/mnt/$MSname")){
        $cmd = "umount -l $source_dir";
        system($cmd);
        $the_msg = "\nStaorge conn't connect $MSname .\n$cmd\n";
        echo "$the_msg\nPlease check the above command line. If the parameters are not correct you should change parameters from Prohtis conf file.\n";
         
        $error .= $the_msg;
      }else{
        echo "$MSname is connected now\n";
      }
    }
  }
  if($error){
    $msg="Hi Prohits admin,
     \nIt is ". @date("Y-m-j G:i:s") ."
     \nThis is an auto message from ProHits data management. Do not reply to this address.
     \nFollowing computer connections have problem and ought to be fixed as soon as possible.
One possibility is that the computer(s) were turned off.
Turn on all relevant computers if they are not on. 
They will be auto-connected tomorrow. You also can login as root and run script:
--------------------
php ".__FILE__." connect
-------------------- 
to connect them now.
If problem sustained, it could be due to networking problem.
\n$error
\n\nthanks";
    if(defined("PROHITS_GMAIL_USER") and PROHITS_GMAIL_USER){
      $err = prohits_gmail(ADMIN_EMAIL, '', "Prohits ms computer connection error", $msg);
      echo $err;
    }else{
      mail(ADMIN_EMAIL, "Prohits ms computer connection error", "$msg", "From: Prohits\r\n"."Reply-To: ProhitsServer\r\n");
    }
  }
}
//---------------------------------------
function is_empty_dir($dir_path){
//---------------------------------------
 $is_empty = true;   
 $files = @scandir($dir_path);
 if(count($files) > 2 ){  
  $is_empty = false;  
 }
 return $is_empty;
}
function ping($IP){
  $cmd = "ping -c 2 $IP 2>&1";
  exec($cmd, $output, $retval);
  if ($retval != 0) { 
    return false;
  }else{ 
    return true;
  }
}
?>
