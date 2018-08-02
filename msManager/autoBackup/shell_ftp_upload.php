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

set_time_limit ( 0 ) ;
ini_set("memory_limit","-1");

include ( "../../config/conf.inc.php");
$gpm_ip = $PROHITS_IP;
$tpp_ip = $PROHITS_IP;

include ( "./shell_functions.inc.php");
include ( "../is_dir_file.inc.php");
require_once("../common_functions.inc.php");
set_include_path(get_include_path() . PATH_SEPARATOR . '../../common/phpseclib0.2.2');
include('Net/SFTP.php');


define("STORAGE_TMP_ZIP_FOLDER", "../../TMP/");

$PHP_SELF = $_SERVER['PHP_SELF'];

$theAction = '';
$username = '';
$filePath = '';
$SID = '';
$ID = '';
$taskID = '';
$tableName = '';
$query_str = ''; 
$file_added_in_export_log = false;
$added_files_arr = array();
$export_file_log = '';
$todayList = 'fileList_'. @date("Y-m-d").".txt";

$ftp_msg = '';
$ftp_error_msg = '';
$frm_remote_ip = '';
$frm_remote_type = '';
$frm_remote_username = '';
$frm_remote_password = '';
$frm_remote_folder = '';

$server_name = '';

$tpp_in_prohits = 0;
$fasta_path = '';

if(array_key_exists('REQUEST_METHOD', $_SERVER)){
  echo "this script cannot be run on web browser!"; exit;
}
if(count($_SERVER['argv']) < 4 ){
  echo "Usage: php shell_ftp_upload.php SID export_file_log\n";exit;
}else{
  $SID = $_SERVER['argv'][1];
  $arg_username = $_SERVER['argv'][2];
  $export_file_log = $_SERVER['argv'][3];
  $server_name = $_SERVER['argv'][4];
}
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$prohits_link  = mysqli_connect("$host", $user, $pswd, PROHITS_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
$msManager_link  = mysqli_connect("$host", $user, $pswd, MANAGER_DB) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));

if($gpm_ip=='localhost') $gpm_ip = $server_name;
 
if(!check_download_permission($SID)) {
  echo "please login prohits.";
  exit;
}

//---------------------------------------
function check_download_permission($SID){
//---------------------------------------
  global $prohits_link;
  global $msManager_link;
  global $username;
  $pro_access_ID_str = '';
  $rt = false;
  if($SID == 'rawDataConverter'){
    return true;
  }
  $SQL = "SELECT U.ID, U.Username, U.Type FROM Session S, User U WHERE U.ID=S.UserID and S.SID = '$SID'";
  $result = mysqli_query($prohits_link, $SQL);
  if($row = mysqli_fetch_row($result) ){
    $username = $row[1];
    $rt = true;
  }else{
    $msg = "session id is not in session table $SID. or the user has no permission to run the script";
  }
  return $rt;
}
//----------------------------------------

$user_raw_export_dir = check_user_raw_export_folder($username);
$tpp_in_prohits = is_in_local_server('TPP');

print "Export DIR=$user_raw_export_dir\n";

if($user_raw_export_dir){
  $fd = '';
   
  $log_path = $user_raw_export_dir . "/". $export_file_log;
  $raw_file_folder = $user_raw_export_dir."/files";
  if(!_is_dir($raw_file_folder)){
    if(!mkdir($raw_file_folder, 0777, true)){
      echo "Apache user cannot create tmp folder ". $raw_file_folder . ". Please contact Prohits admin.";exit;
    }
  }
  if(_is_file($log_path)){
    $lines = @file($log_path);
    $lines = array_unique($lines);
    echo "Log file=$log_path\n";
    
    $raw_file_index = array();
     
    foreach($lines as $theLine){
         
        $the_line = trim($theLine);
        if(strpos($the_line, '#*') ===0){
          $the_line = substr($the_line,2);
          $line_arr = explode("_", $the_line);
          $tmp_index = $line_arr[0];
          $tmp_taskID = '';
          $tmp_tppID = '';
          $tmp_engine = '';
          for($i = 0; $i< count($line_arr); $i++){
            
            if(strpos($line_arr[$i], 'task') ===0){
              $tmp_taskID = substr($line_arr[$i] , 4);
            }else if(strpos($line_arr[$i], 'tpp') ===0){
              $tmp_tppID = substr($line_arr[$i] , 3);
            }else if($i>1){
              $tmp_engine = $line_arr[$i];
            }
          }
          $the_engine = $tmp_engine;
          $the_taskID = $tmp_tppID;  //tppID or taskID
          if($tmp_engine == 'iProohet'){
            $the_engine = 'TPPpep_iProphet';
          }else if($tmp_tppID){
            $the_engine = 'TPPpep_'.$tmp_engine;
          }else{
            $the_taskID = $tmp_taskID;
          }
          $raw_file_index[$tmp_index] = array('TaskID'=>$the_taskID, 'FileType'=>$the_engine);
          
        }else if(strpos($the_line, '#') ===0){
          $the_line = substr($the_line,1);
          $ftp_arr = explode(":", $the_line);
          if(count($ftp_arr) == 2){ 
            $$ftp_arr[0] = $ftp_arr[1];
          }
          continue;
        }else if(strpos($the_line, '>>') ===0){
          continue;
        }
        $tmp_arr = explode("\t", $the_line);
        if(count($tmp_arr)<3) continue;
         
        $SQL = "select FileName, FolderID, Date, Size from ". $tmp_arr[1] . " where ID='". $tmp_arr[2] ."'";
        $result = mysqli_query($msManager_link, $SQL);
        if($row = mysqli_fetch_assoc($result) ){
          $the_rd_arr = array('FileType'=>$tmp_arr[0],'MS'=>$tmp_arr[1],'ID'=>$tmp_arr[2], 'taskID'=>'' );
          if(count($tmp_arr)>3){
            $the_rd_arr['taskID'] = $tmp_arr[3];
          } 
          $row = array_merge($row, $the_rd_arr); 
          $added_files_arr[] = $row;
        }
       
    }
    if($fd) fclose($fd);
  }else{
    echo "Error: file '$log_path' doesn't exist";exit;
  }
}else{
  echo "Error: cannot create tmp folder $user_raw_export_dir";exit;
}

$is_massive = 0;

if(isset($export_type) and $export_type == 'ftp_massive' and $type == 'ftp'){
  $is_massive = 1;
}

if($is_massive){
  echo "--get fasta file---\n";
  $fasta_dir = "../../TMP/fastas";
  if($fasta_file){
    list($fasta_path, $fasta_name) = explode("|", $fasta_file);
    if(!$tpp_in_prohits){
      $fasta_file_baseName = basename($fasta_path);
      $fasta_path =  $fasta_dir."/".$fasta_file_baseName;
      if(!_is_file($fasta_path)){
        $http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
        $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
        $postData = "tpp_myaction=downloadDB&fileName=" .$fasta_name;
        echo $tpp_formaction."?".$postData."\n";
        if(!copy($tpp_formaction."?".$postData, $fasta_path)){
          echo "cannot downlaod ".$tpp_formaction."?".$postData."\n";
        }
      }
    }
  }
    
  echo "--get result files from XTandem server---\n";
 
  $idConvert =  PROTEOWIZARD_BIN_PATH."/idconvert";
  if(!_is_file($idConvert)){
    $idConvert =  preg_replace("/msManager\/autoBackup$/", "", dirname(__FILE__))."EXT/pwiz-bin/idconvert";
  }
  
  //print_r($added_files_arr);
  foreach ($added_files_arr as $theIndex=>$raw_arr){
    if($raw_file_index[$theIndex]['TaskID']){
     
      $theType = $raw_file_index[$theIndex]['FileType'];
      $theTaskID = $raw_file_index[$theIndex]['TaskID'];  //tppID or search taskID
      $tmp_fileName = download_search_result($theType, $raw_arr['MS'], $theTaskID, $raw_arr['ID'], $raw_file_folder, $server_name);
      $tmp_XML_path = $raw_file_folder ."/". $tmp_fileName;
      if(_is_file($tmp_XML_path)){
        $added_files_arr[$theIndex]['pepXML'] = $tmp_XML_path;
      }else{
        writeLog( ">>no pepXML for: ".  $raw_arr['FileName'] , $log_path);
      }
    }
  }
}
//exit;
//print_r($raw_file_index); 
//print_r($added_files_arr); exit;
 


if(!check_connection($UserName, $password, $IP, $type)){
  //global $sftp;
  //$ftp_conn_id is global in check_connection function;
  $fd = fopen($log_path, 'a+');
  echo "Couldn't connect remote site: userName='$UserName' passwd='$password' IP='$IP' Type='$type'";
  fwrite($fd, "\r\n>>Couldn't connect the remote site");
  fclose($fd);
  exit;
}

if($type == 'sftp'){
  if($NewFolder){
    $sftp->mkdir($NewFolder);
    $Folder = $NewFolder;
  }
  if($Folder)  $sftp->chdir($Folder);
}else if($type == 'ftp'){
  
  if($NewFolder){
    $Folder = $NewFolder;
    if(!ftp_directory_exists($ftp_conn_id, $Folder)){
      if(!ftp_mkdir($ftp_conn_id, $Folder)) {
        echo "error: cannot create  $Folder";exit;
      }
    }
  }
  if(!ftp_chdir($ftp_conn_id, $Folder)){
    echo "folder $Folder doesn't exist."; exit;
  }
  if($is_massive){
    $exist_dir_arr = array('RAW','PEAK', 'RESULTS', 'OTHER');
    foreach($exist_dir_arr as $tmpDir){
      if(!ftp_directory_exists($ftp_conn_id, $tmpDir)) {
        ftp_mkdir($ftp_conn_id, $tmpDir);
      }
    }
  }
  echo "FTP folder=$Folder\n";
}

foreach($added_files_arr as $theIndex=>$row){
     
  if($row['FileType'] == 'RAW'){
    $tableName = $row['MS'];
    $theFilePath = getFilePath($tableName, $row['ID'], 'full', $msManager_link);
    //echo $theFilePath;
    $local_file_size = filesize($theFilePath);
    if($type == 'sftp'){
      $remote_file_size = $sftp->size($row['FileName']);
      if( $local_file_size!=$remote_file_size ){
        $sftp->put($row['FileName'], $theFilePath, NET_SFTP_LOCAL_FILE);
        $fd = fopen($log_path, 'a+');
        fwrite($fd, "\r\n>>".$theFilePath);
        fclose($fd);
      }
      //break;
    //////////////////////////
    }else if($type == 'ftp'){
    //////////////////////////
      $remote_folder = '';
      $renamed_file_name = '';
      $ext = '';
      $peak_ext = '';
      if(preg_match("/([^.]+)$/", $row['FileName'], $matches)){
        $ext = $matches[1];// RAW/WIFF/SCAN
        if(strtoupper($ext) == 'SCAN' and $is_massive) continue;
      }else{
        writeLog( ">>file error ".$row['FileName'], $log_path);
        continue;
      }
      if($is_massive){
        //get mzML file and file basename
        $peak_file_base_path = preg_replace("/[^.]+$/", "", $theFilePath);
        $peak_file_path = '';
        if(_is_file($peak_file_base_path."mzML") ){
          $peak_ext = 'mzML';
          $peak_file_path = $peak_file_base_path."mzML";
        }else if(_is_file($peak_file_base_path."mzML.gz") ){
          unzip_gz($peak_file_base_path."mzML.gz");
          $peak_ext = 'mzML';
          $peak_file_path = $peak_file_base_path."mzML";
        }else if(_is_file($peak_file_base_path."mzXML")){
          $peak_ext = 'mzXML';
          $peak_file_path = $peak_file_base_path."mzXML";
        }else if(_is_file($peak_file_base_path."mzXML.gz")){
          unzip_gz($peak_file_base_path."mzXML.gz");
          $peak_ext = 'mzXML';
          $peak_file_path = $peak_file_base_path."mzXML";
        }else if(_is_file($peak_file_base_path."mgf")){
          $peak_ext = 'mgf';
          $peak_file_path = $peak_file_base_path."mgf";
        }else{
          echo "no peak file found.\n";
        }
        //default mzid file name.
        $mzidBasename = '';
        $mzid_file = '';
        $fileBaseName = preg_replace('/\.[^.]+$/','',$row['FileName']);
        $tmp_baseName = $row['ID']."_".$fileBaseName;
        $mzid_file = $raw_file_folder."/".$tmp_baseName.".mzid";
        $theType = $raw_file_index[$theIndex]['FileType'];
        
        $mzid_file = '';
        if(!_is_file($mzid_file) and isset($row['pepXML'])){
          $tmp_XML_path = $row['pepXML'];
          $mzid_file = $raw_file_folder."/".$fileBaseName.".mzid";
          if(!_is_file($mzid_file)){
            if(strpos($theType, "TPPpep") === 0 and _is_file($tmp_XML_path)){
              //convert to mzID
              $cmd = "sed -i 's| base_name=\"[^\"]*\"| base_name=\"". $tmp_baseName.".$peak_ext\"|g' '".$tmp_XML_path."'";
              shell_exec($cmd);
              $cmd = "'$idConvert' --outdir '$raw_file_folder' '$tmp_XML_path'";
              writeLog( ">>". $cmd, $log_path);
              
              $output = shell_exec($cmd);
              sleep(1);
               
              
              if(preg_match("/writing output file: (.+\.mzid)/", $output, $matches)){
                $mzid_file = $matches[1];
              }
            }
          }
        }
        $mzidBasename = preg_replace('/\.[^.]+$/', "", basename($mzid_file));
        
        

        //$added_files_arr[$theIndex]['mzidBasename'] = $mzidBasename;
        //$added_files_arr[$theIndex]['mzidFile'] = $mzid_file;
      
         
        //1. RAW/WIFF/SCAN
        $remote_folder = 'RAW'; 
        $remote_file_path = "$remote_folder/". $row['FileName'];
        if($mzidBasename){
          $remote_file_path = "$remote_folder/". $mzidBasename.".$ext";
        }
         
        ftp_upload_file($ftp_conn_id, $theFilePath, $local_file_size, $remote_file_path);
        if(strtoupper($ext) == 'WIFF'){
          $scan_file = '';
          if(_is_file($theFilePath.'.scan')){
            $scan_file = $theFilePath.'.scan';
            $remote_file_path .= ".scan";
          }else if(_is_file($theFilePath.'.SCAN')){
            $scan_file = $theFilePath.'.SCAN';
            $remote_file_path .= ".SCAN";
          }
          if($scan_file){
            ftp_upload_file($ftp_conn_id, $scan_file, '', $remote_file_path);
          }
        }
        
        if($peak_file_path){
          //2. PEAK
          $remote_folder = 'PEAK'; 
          if($mzidBasename){
            $remote_file_path = "$remote_folder/".$mzidBasename.".$peak_ext";
          }else{
            $remote_file_path = "$remote_folder/".basename($peak_file_path);
          }
          ftp_upload_file($ftp_conn_id, $peak_file_path, '', $remote_file_path);
         
          //3. Results
          $renamed_file_name = '';
          $remote_folder = 'RESULTS';
          if($mzid_file){
            $remote_file_path = "$remote_folder/".basename($mzid_file);
            ftp_upload_file($ftp_conn_id, $mzid_file, '', $remote_file_path);
          }
        }
      }else{
        ftp_upload_file($ftp_conn_id, $theFilePath, $local_file_size, $row['FileName']);
      }
    }
    //if($theAction == 'local'){
    //  if(!copy($theFilePath, $raw_file_folder."/".$row['FileName'])){
     //   echo "error: cannot copy file to $raw_file_folder.<br>";
    // // }
    //}
  }
}


if($is_massive){
  //additional files
  $remote_folder = 'OTHER';
  if($fasta_path and _is_file($fasta_path)){
    $remote_file_path = "$remote_folder/".basename($fasta_path);
    ftp_upload_file($ftp_conn_id, $fasta_path, '', $remote_file_path);
  }
  
}else if(!$is_massive and _is_dir($raw_file_folder)){
  //search result files have been put in the folder from export_raw_files.php and Massive above.
  $dp = opendir("$raw_file_folder");
  while (false !==($file = readdir($dp))) {
    $local_file_size = filesize($raw_file_folder."/".$file);
    if ($file != 'wget.log' and _is_file($raw_file_folder."/".$file)){
      if($type == 'sftp'){
        $remote_file_size = $sftp->size($file);
        if( $local_file_size!=$remote_file_size ){
          $sftp->put($file, $raw_file_folder."/".$file, NET_SFTP_LOCAL_FILE);
          writeLog( ">>". $raw_file_folder."/".$file, $log_path);
        }
      }else if($type == 'ftp'){
        ftp_upload_file($ftp_conn_id, $raw_file_folder."/".$file, $local_file_size, $file);
        
        $remote_file_size = ftp_size($ftp_conn_id, $file);
        if( $local_file_size!=$remote_file_size ){
          if(ftp_put($ftp_conn_id, $file, $raw_file_folder."/".$file, FTP_BINARY)) {
            writeLog( ">>". $raw_file_folder."/".$file, $log_path);
          }
        }
      }
    }
  }
  closedir($dp);
}

writeLog( ">>END>>".@date("Y-m-j G:i:s"), $log_path);
exit;

//-----------------------------------------
function ftp_directory_exists($ftp, $dir) {
//---------------------------------------- 
  $origin = ftp_pwd($ftp); 
  if (@ftp_chdir($ftp, $dir)) { 
    ftp_chdir($ftp, $origin);
    return true; 
  }
  return false; 
}
//----------------------
function ftp_upload_file($ftp_conn_id, $local_file_Path, $local_file_size='', $remote_file_path){
//----------------------
  $origin_Folder = '';
  $need_rename = 0;
  $need_upload = 0;
  global $log_path;
  if(!$local_file_size){
   $local_file_size = filesize($local_file_Path);
  } 
  $remote_file_size = ftp_size($ftp_conn_id, $remote_file_path);
  if( $local_file_size !=$remote_file_size ){
    $need_upload = 1;
    if($remote_file_size != -1){
       ftp_delete($ftp_conn_id, $remote_file_path);
    }
    if(ftp_put($ftp_conn_id, $remote_file_path, $local_file_Path, FTP_BINARY)) {
      writeLog( ">>".$local_file_Path, $log_path);
    }
    echo "uploaded: $local_file_Path\n";
  } 
}
//end of the file