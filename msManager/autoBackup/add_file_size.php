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

$enable_spleep = false;
chdir(dirname(__FILE__));
$configFile = "../../config/conf.inc.php";
$logfile = '../../logs/add_file_size.log';
$only_run_on_shell = false;

require($configFile);
include('./shell_functions.inc.php');

//----------------------------------------------------------
exit;
//----------------------------------------------------------

if(count($_SERVER['argv']) > 1){
  if(isset($_SERVER['argv'][1])){
    $sleep_sec = $_SERVER['argv'][1];
    if(!is_numeric($sleep_sec)){
      echo "Usage: php raw_backup_shell.php spleepSeconds tableName\n";exit;
    }
    if($enable_spleep){
      sleep($sleep_sec);
    }
  }
  if(isset($_SERVER['argv'][2])){
    $tableName = $_SERVER['argv'][2];
  }
}
if(!is_file($logfile)){
  if(!$handle = fopen($logfile, "a")){
    echo "Cannot create log file!";
    exit;
  }
}  

if(array_key_exists('REQUEST_METHOD', $_SERVER) and $only_run_on_shell){
  echo "this script cannot be run on web browser!"; exit;
}
if(STORAGE_IP == PROHITS_SERVER_IP){
  $host = HOSTNAME;
}else{
  $host = PROHITS_SERVER_IP;
}
$user = USERNAME;
$pswd = DBPASSWORD;
$msManager_link  = mysqli_connect($host, $user, $pswd, MANAGER_DB) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
 
 
$allBaseTableNames = array('LCQ', 'LCQtrap', 'LTQ1', 'LTQMT', 'Nanospray', 'QstarOMaldi', 'Qtrap', 'ReflexIV');

//print_r($allBaseTableNames);exit;

foreach($allBaseTableNames as $TableName){
  global $logfile;
  $msg = "\r\n\r\n$TableName--------------------\r\n\r\n";
  writeLog($msg, $logfile);
  $currentTableName = $TableName;
  if(preg_match('/\/$/', STORAGE_FOLDER, $matchs)){
    $desFile = STORAGE_FOLDER . $currentTableName;
  }else{
    $desFile = STORAGE_FOLDER . "/" . $currentTableName;
  }
  $rootFolderID = 0;
  $counter = 0;
  echo "<br>".$desFile."<br>";
  process_dir($desFile, $rootFolderID);
  echo $counter."<br>";
}
exit;

//===================================================================================

function process_dir($desDir, $folderID){
  global $logfile, $currentTableName, $slash, $msManager_link, $counter;
  $SQL = "SELECT ID, FileName, FileType, Size FROM $currentTableName WHERE FolderID='$folderID'";
  $result = mysqli_query($msManager_link, $SQL);
  $recordsArr = array();
	while($row = mysqli_fetch_assoc($result)){
    array_push($recordsArr, $row);
	}
  foreach($recordsArr as $recordsValue){
    $currentFile = $desDir . "/" . $recordsValue['FileName'];
    if(strtolower($recordsValue['FileType']) == 'dir' && is_dir($currentFile)){
      process_dir($currentFile, $recordsValue['ID']);
    }elseif(is_file($currentFile)){
      $fileSize = sprintf("%u", filesize($currentFile));
      if(!$recordsValue['Size']){
        $SQL = "UPDATE $currentTableName SET Size='".$fileSize."' WHERE ID='".$recordsValue['ID']."'";
        $ret = mysqli_query($msManager_link, $SQL);
        echo $currentFile . "**" . $fileSize . "<br>";
        $counter++;
      }
    }else{
      $msg = $currentFile;
      writeLog($msg, $logfile);
    }
  }
}
?>