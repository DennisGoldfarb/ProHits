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

include ( "../is_dir_file.inc.php");

chdir(dirname(__FILE__));
$configFile = "../../config/conf.inc.php";
$logfile = '../../logs/raw_back.log';
$only_run_on_shell = false;

require($configFile);
if(count($_SERVER['argv']) > 1){
  if(isset($_SERVER['argv'][1])){
    $sleep_sec = $_SERVER['argv'][1];
    sleep($sleep_sec);
  }
}
if(array_key_exists('REQUEST_METHOD', $_SERVER) and $only_run_on_shell){
  echo "this script cannot be run on web browser!"; exit;
}

$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;

$msManager_link  = mysqli_connect($host, $user, $pswd, MANAGER_DB) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));

if(isset($_SERVER['OS'])){
	$slash = "\\";
}else{
	$slash = "/";
}

$tableNamesArr = array('LCQ');
//$tableNamesArr = array('LCQ', 'LCQtrap', 'LTQ1', 'Nanospray', 'QstarOMaldi', 'Qtrap', 'ReflexIV');
foreach($tableNamesArr as $value){  
  $desFile = STORAGE_FOLDER . "/" . $value;
  if(_is_dir($desFile)){
    $currentTableName = $value;
    $rootFolderID = 0;
    process_dir($desFile, $rootFolderID);
  }  
}
exit;

//===================================================================================

function process_dir($desDir, $folderID){
  global $currentTableName, $slash, $msManager_link;
  $SQL = "SELECT ID, FileName FROM $currentTableName WHERE FolderID='$folderID' AND  FileType='dir'"; 
  $result = mysqli_query($msManager_link, $SQL);
	$recordsArr = array();
	while($row = mysqli_fetch_assoc($result)){
		$recordsArr[$row['FileName']] = $row['ID'];
	} 
  if($recordsArr && _is_dir($desDir) && $handle = opendir($desDir)){    
		while(false !== ($file = readdir($handle))){
			if($file != "." && $file != ".."){				
				$desFile = $desDir . $slash . $file;
				if(_is_dir($desFile) && isset($recordsArr[$file])){
					$dirNewSize = get_lunux_folder_size($desFile);
          echo $dirNewSize."<br>";
	        $SQL = "UPDATE $currentTableName SET
                  Size = '$dirNewSize'
                  WHERE ID='".$recordsArr[$file]."'";
          $ret = mysqli_query($msManager_link, $SQL); 
          if(!$ret){
            $error_message = "fail to update directory size for $desFile";
            writeLog($error_message);
          }
	 				process_dir($desFile, $recordsArr[$file]);
				}
			}
		}
  }  
}

function get_lunux_folder_size($dir){
   $du = popen("/usr/bin/du -sk $dir", "r");   
   $res = fgets($du, 256);
   pclose($du);
   $res = explode("\t", $res);
   //print_r($res);echo "<br>";
   return $res[0];
}

function fatalError($msg='', $line=0){ //--write message to log file then exit.
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

function writeLog($msg){ //--write message to log file
  global $logfile; 
  $log = fopen($logfile, 'a+');
  fwrite($log, "\r\n" . $msg);
  fclose($log);
  echo $msg;
}
?>