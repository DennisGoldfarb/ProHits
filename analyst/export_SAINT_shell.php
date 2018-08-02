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
date: 2011-07-08
description:
  1. shell > php process_saint_shell.php ID otherOptions
  2. check shell process 
     shell > ps -o lstart PID
*************************************************************************/
set_time_limit(0);
ini_set("memory_limit","-1");

/**********************************************************
if SAINT installed in Prohits server, please make sure that 
the following three commands are correct.
***********************************************************/
$saint_reformat = "saint-reformat";
$saint_ctrl = "saint-spc-ctrl";
$saint_noctrl = "saint-spc-noctrl";


//change cwd to this directory
chdir(dirname(__FILE__));
include('../config/conf.inc.php');
require("../common/common_fun.inc.php");
include("common_functions.inc.php");

@require_once("../common/HTTP/Request_Prohits.php");
require_once("../msManager/is_dir_file.inc.php");
require_once("../msManager/common_functions.inc.php");


//echo "\$Is_geneLevel=$Is_geneLevel<br>";

umask(0);
if(!function_exists("writeLog")){
  //------------------------------------ 
  function writeLog($msg, $log_file=''){
  //----------------------------------- 
    global $logfile; 
    global $debug;
    if(!$log_file and $logfile){
      $log_file = $logfile;
    }
     
    $log = fopen($log_file, 'a+');
    if(!$log){
      echo "can not open the log file to write: $log_file"; exit;
    }
    fwrite($log, "\r\n" . $msg);
    fclose($log);
  }
}



$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$link  = mysqli_connect("$host", $user, $pswd, PROHITS_DB ) or fatalError("Unable to connect to mysql..." . mysqli_error($link));
 


$saint_ID = '';
$zippedFileName = '';
$saint_type = 'express';
$nControl = '4';
$nCompressBaits = '2';
$nburn = 2000;
$niter = 5000;
$lowMode=0;
$minFold=1; 
$fthres = '0';
$fgroup = '0';
$var = '0';
$normalize = '1';

$isZipped = 1;
$has_iRefIndex_file = 0;

if(isset($_SERVER['argv']) and count($_SERVER['argv']) == 3){
  $saint_ID = $_SERVER['argv'][1];
  $other_option = $_SERVER['argv'][2];
  $op_arr = explode(",", $other_option);
  foreach($op_arr as $set){
    $set = preg_replace("/\s/", "", $set);
    $set_arr = explode(":", $set);
    if(count($set_arr) == 2){
      $$set_arr[0] = $set_arr[1];
    }
  }
}else if(array_key_exists('REQUEST_METHOD', $_SERVER)){
    echo "you have no permission to run the shell script.";
    exit;
}else{
  $msg = "Usage: php ".__FILE__." saintID saintOptionString\n";
  echo $msg;
  exit;
}
//******************************************************************
///// shell script start from here /////////////////////////////////
$SQL = "SELECT ID, `Name`,`UserID`, `Date` , `Description`, `Status` , `ProjectID`, `ParentSaintID`, `UserOptions` FROM SAINT_log where ID='$saint_ID'";
$results = mysqli_query($link, $SQL);
if(!$saint_record = mysqli_fetch_array($results)){
  writeLog( "error: no record found for saint_ID=$saint_ID");
  exit;
}
 

$start_time = @date("Y-m-j G:i:s");
$prohits_root = str_replace("analyst","",dirname(__FILE__));
$saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_$saint_ID/";

$logfile = $prohits_root . "logs/saint.log";


$to_log = "SAINT $saint_ID: Start time  " . @date("Y-m-d G:i:s");
$to_log .= "\nFolder:".$saint_folder;
$to_log .= "\nSaintName:".$saint_record['Name'];
$to_log .= "\nProjectID:".$saint_record['ProjectID'];
$to_log .= "\nUserID:".$saint_record['UserID'];

writeLog($to_log);
if(!_is_dir($saint_folder)){
  writeLog( "error: input dir '$saint_folder' is not dir");
  exit;
}


$dir = opendir($saint_folder);
while(false !== ( $file = readdir($dir)) ) {
  if ( _is_dir($saint_folder . $file) and  $file != '.' && $file != '..' ) {
    $cmd = "rm -rf $saint_folder" . $file;
    exec($cmd);
  }
}



$saint_in_prohits = is_in_local_server('SAINT');
 
 
if($saint_in_prohits){
 //SAINT in local server
  $mysql_status = '';
  $error_msg = '';
  $SAINT_EXE_DIR = SAINT_SERVER_PATH;
  $SAINT_EXPRESS_EXE_DIR = SAINT_SERVER_EXPRESS_PATH;
  
  $task_infor['tasklog'] = $logfile;
  $task_infor['taskDir'] = $saint_folder;
  $task_infor['taskComFile'] = $saint_folder."/task.command.log";
  
  if(!preg_match("/\/$/", $SAINT_EXE_DIR, $matches)){
      $SAINT_EXE_DIR .= "/";
  }
  if(!preg_match("/\/$/", $SAINT_EXPRESS_EXE_DIR, $matches)){
      $SAINT_EXPRESS_EXE_DIR .= "/";
  }
  $currDir = getcwd();
  $msg = "run saint in Prohits server\nWorking Dir: $saint_folder\n";
  echo "$msg";
  writeLog($msg);
  chdir($saint_folder);
  if($saint_type != 'express'){
    echo "SAINT type: $saint_type\n";
    $cmd = $SAINT_EXE_DIR."$saint_reformat inter_2.dat prey.dat bait.dat";
    if($nControl and $saint_type == 'saint_control'){
      $cmd .= " $nControl";
    }
    echo "Command:\n$cmd\n";
   
    system("rm ".escapeshellarg($saint_folder."interaction.new"));
    run_search_on_local($cmd, $task_infor, $saint_folder, $saint_folder);
    $other_options = ''; 
    if(_is_file($saint_folder. "/interaction.new")){
      if($saint_type == 'saint_control'){
        $cmd = $SAINT_EXE_DIR."$saint_ctrl";
        $other_options = " $lowMode $minFold $normalize";;
      }else{
        $cmd = $SAINT_EXE_DIR."$saint_noctrl";
        $other_options = " $fthres $fgroup $var $normalize";
      } 
      $cmd = "GSL_RNG_SEED=123 ". $cmd . " interaction.new prey.new bait.new ". $nburn . " ". $niter . $other_options;
       
      echo "Command:\n$cmd\n";
      run_search_on_local($cmd, $task_infor, $saint_folder, $saint_folder);
     
      if(_is_file($saint_folder. "/RESULT/unique_interactions")){
        $mysql_status = 'Finished'; 
        //$error_msg = '';
      }else{
         $error_msg = "no unique_interactions created.\n"; 
         echo $error_msg;
         echo $saint_folder. "/RESULT/unique_interactions\n";
      }
    }
  }else{
    //run SAINT express version
    echo "SAINT type: $saint_type\n";
    $cmd = $SAINT_EXPRESS_EXE_DIR."SAINTexpress-spc";
    if($nControl){
      $cmd .= " -L$nControl";
    }
	  if($nCompressBaits){
      $cmd .= " -R$nCompressBaits";
    }
    $cmd .= " inter.dat prey.dat bait.dat";
    if(_is_file("iRefIndex.dat")){
      $cmd .= " iRefIndex.dat";
    }
    echo "$cmd\n";
  
    run_search_on_local($cmd, $task_infor, $saint_folder, $saint_folder);
    if(_is_file($saint_folder."/list.txt")){
      mkdir("$saint_folder/RESULT", 0775, true); 
	    $cmd = "mv list.txt RESULT";
	    exec("$cmd  2>&1", $output);
      $mysql_status = 'Finished';
    }else{
      $error_msg = 'no list.txt file created.';
    }
  }
  if($error_msg) $mysql_status = 'Error';
  $SQL = "update SAINT_log set Status='$mysql_status' where ID='$saint_ID'";
  echo "\n$SQL";
  mysqli_query($link, $SQL);
  
  
  
  
  
  
  
  chdir($currDir);

}else{
  if(defined("SAINT_SERVER_WEB_PATH") and strpos(SAINT_SERVER_WEB_PATH, 'http://') === 0){
    if($zippedFileName){
      $zip_file_path = $saint_folder . "$zippedFileName";
    }else{
      $err_msg = create_SAINT_input_zipped_file($saint_folder, 'SAINT_input_files.zip');
      if($err_msg){
        writeLog("zip error: $err_msg");
        exit;
      }else{
        $zip_file_path = $saint_folder . 'SAINT_input_files.zip';
      }
    }
    writeLog( "send zipped input files to ". SAINT_SERVER_WEB_PATH);
    $formaction = SAINT_SERVER_WEB_PATH;
    $req = new HTTP_Request($formaction,array('timeout' => 180000,'readTimeout' => array(180000,0)));
    $req->setMethod(HTTP_REQUEST_METHOD_POST);
    $req->addHeader('Content-Type', 'multipart/form-data');
    
    $req->addPostData('theaction', "run_saint");
    $req->addPostData('from_prohits', "1");
    $req->addPostData('nburn', $nburn);
    $req->addPostData('niter', $niter);
    $req->addPostData('lowMode', $lowMode);
    $req->addPostData('minFold', $minFold);
    $req->addPostData('normalize', $normalize); 
    
    $req->addPostData('nControl', $nControl);
    $req->addPostData('fthres', $fthres);
    $req->addPostData('fgroup', $fgroup);
    $req->addPostData('var', $var);
    $req->addPostData('saint_type', $saint_type);
    $req->addPostData('nCompressBaits', $nCompressBaits);
   
    $req->addPostData('isZipped', "1"); 
    $result = $req->addFile("zip_file", $zip_file_path);
    
    $result = $req->sendRequest();
    if (!PEAR::isError($result)) {
      $response1 = $req->getResponseBody();
       
      $to_log =  "\n--------return from $formaction for task $saint_ID------\n";
    	$to_log .=  $response1;
    	$to_log .= "\n---------------end response---------------\n";
      if(defined("DEBUG_SAINT") and DEBUG_SAINT) {
        print $to_log;
      }
      writeLog($to_log);
      $zipped_saint_file = $saint_folder . "RESULT.zip";
      if(preg_match('/>>>(.+)<<</', $response1, $matchs)){
        $downlad_dir = $matchs[1];
        $download_to_log = $saint_folder."wget.log";
        $postData = "theaction=download&workDir=".$downlad_dir;
        $sysCall = 'wget';
        $sysCall .= " --post-data=\"$postData\"";
        $sysCall .= " --directory-prefix=\"$saint_folder\"";
        $sysCall .= " --output-document=\"$zipped_saint_file\"";
        $sysCall .= " ". $formaction;
        $sysCall .= ">> $download_to_log 2>&1";
        echo $sysCall."\n";
        system($sysCall);
        if(!_is_file($zipped_saint_file)){
          writeLog("wget error: $formaction:$postData");
          exit;
        }else{
          $cmd = "unzip -d $saint_folder "."$zipped_saint_file 2>&1";
          exec ($cmd, $output);
        }
        if(_is_file($saint_folder . "RESULT/list.txt") or _is_file($saint_folder . "RESULT/unique_interactions")){
          $SQL = "update SAINT_log set Status='Finished' where ID='$saint_ID'";
        }else{
          $SQL = "update SAINT_log set Status='Error' where ID='$saint_ID'";
        }
        mysqli_query($link, $SQL);
        writeLog($SQL);
      }else{
        $SQL = "update SAINT_log set Status='Error' where ID='$saint_ID'";
        mysqli_query($link, $SQL);
      }
    } else {
      $SQL = "update SAINT_log set Status='Error' where ID='$saint_ID'";
      mysqli_query($link, $SQL);
     	writeLog($result->getMessage() . " in file".__FILE__, __LINE__);
      exit;
    }
  }else{
    $SQL = "update SAINT_log set Status='Error' where ID='$saint_ID'";
    echo $SQL;
    mysqli_query($link, $SQL);
    $msg = "Error: SAINT cannot be run. SAINT set to run remotely. Please set correct bin path for 'SAINT_SERVER_WEB_PATH' in conf file";
    echo $msg;
    writeLog($msg);
  }
}


function create_SAINT_input_zipped_file($outDir, $zip_file_name){
  $err_msg = '';
  if(!_is_file($outDir . $zip_file_name)){
    $myshellcmd = "cd $outDir; zip $zip_file_name bait.dat inter.dat prey.dat";
    if(_is_file($outDir . "log.dat")){
      $myshellcmd .= " log.dat";
    }
    $result = @exec($myshellcmd);
    if(!$result){
      $err_msg = "Can not create a zip file now. '$myshellcmd'.";
    }
  }
  return $err_msg;
}
?>