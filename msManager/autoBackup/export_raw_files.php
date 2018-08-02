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

set_time_limit ( 2400 ) ;
ini_set("memory_limit","-1");
define("MAX_DOWNLOAD_SZIE", 5000); //MB
require("../../common/site_permission.inc.php"); 

//set_include_path(get_include_path() . PATH_SEPARATOR . '../../common/phpseclib0.2.2');

include ( "msManager/autoBackup/shell_functions.inc.php");
include ( "msManager/is_dir_file.inc.php");
require(  "msManager/classes/xmlParser_class.php"); 
require_once("msManager/common_functions.inc.php");

include('Net/SFTP.php');

define("STORAGE_TMP_ZIP_FOLDER", "../../TMP/");

$PHP_SELF = $_SERVER['PHP_SELF'];

$theAction = '';
$userID = $USER->ID;
$filePath = '';
$SID = '';
$ID = '';
$taskID = '';
$tableName = '';
$query_str = ''; 
$file_added_in_export_log = false;
$added_files_arr = array();
$machine_arr = array();
$export_file_log = '';
$todayList = 'fileList_'. @date("Y-m-d").".txt";

$ftp_msg = '';
$ftp_error_msg = '';
$frm_remote_ip = '';
$frm_remote_type = '';
$frm_remote_username = '';
$frm_remote_password = '';
$frm_remote_folder = '';
$frm_remote_folder_new = '';
$shared_msg_error = '';
$shared_msg = '';
$user_shared_folder = '';

$frm_Projects = '';
$folder_id = '';
$folder_name = '';
$block_none = 'none';
$new_old = 'new';
$machine_name = '';
$machine_name_new = '';
$message = '';
$current_page = 1;
$switch_moment_log_name = '';
$export_type = $before_type = 'local_interf';
$sftp = '';

if(isset($URLS["MASSIVE_IP"])){
  $ftp_massive = $URLS["MASSIVE_IP"];
}else{
  $ftp_massive = 'massive.ucsd.edu';
}
$raw_file_tpp_taskID_arr = array();
$raw_file_tpp_taskID_saved_arr = array();
$raw_file_searchResults_taskID_arr = array();
$raw_file_searchResults_saved_taskID_arr = array();

$tppID_searchTaskID_arr = array(); //[TppID] = SearchTaskID

$gpm_taxonomy_file = '';
$tpp_in_prohits = 0;

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
  if(strlen($query_str) > 0){
    $query_str .= "&";
  }
  $query_str .= "$key=$value";
}
/*
echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//check query string variables
if(!$frm_remote_ip and $export_type == 'ftp_massive'){
  $frm_remote_ip = $ftp_massive;
}

$prohits_link = $PROHITSDB->link;
$mangerDB = new mysqlDB(MANAGER_DB);
$msManager_link = $mangerDB->link;
 
$ProjectNameIDarr = get_user_permited_project_id_name($prohits_link, $USER->ID);
$user_raw_export_dir = check_user_raw_export_folder($USER->Username);//get or create user folder for any users.

$selectd_folder_arr = array();
if($user_raw_export_dir){
  $fd = '';
  if($switch_moment_log_name){
    $export_file_log = $switch_moment_log_name;
  }elseif(!$export_file_log and _is_file($user_raw_export_dir . "/".$todayList)){
    $export_file_log = $todayList;
  }
  $log_path = $user_raw_export_dir . "/". $export_file_log; 
  $log_array = getFileListFromDir($user_raw_export_dir, 'txt');
  rsort ($log_array);
   
  //------------------------------------------------------------------------------
  $tmp_dir = dirname($user_raw_export_dir);
  $user_raw_copy_log_dir = $tmp_dir.'/raw_copy';
  if(!_is_dir($user_raw_copy_log_dir )){
    if(!mkdir($user_raw_copy_log_dir, 0777, true)){
      echo "Apache user cannot create tmp folder ". $user_raw_copy_log_dir . ". Please contact Prohits admin.";exit;
    }
  }
  //------------------------------------------------------------------------------
  if(_is_file($log_path)){
    $lines = @file($log_path);
    if(count($lines)) $lines[count($lines)-1] = $lines[count($lines)-1] . "\r\n";
    $lines = array_unique($lines);
/*echo "<pre>";
print_r($lines);
echo "</pre>";*/     
    if($theAction == "delete"){
      $fd = fopen($log_path, 'w');
    }
    $line_num = 0;
    foreach($lines as $theLine){
      
      $the_line = trim($theLine);
      $tmp_arr = explode("\t", $the_line);
      if(count($tmp_arr)<3 or (!$ID and $theAction == 'delete')) continue;
      
      if($fd){
       if($taskID){
        if(count($tmp_arr)>3){
          if($FileType == $tmp_arr[0] and $tableName == $tmp_arr[1] and $ID == $tmp_arr[2] and $taskID == $tmp_arr[3]) continue;
        }
       }else if(count($tmp_arr) == 3){
          if($tableName == $tmp_arr[1] and $ID == $tmp_arr[2]) continue;
       }
       fwrite($fd, $theLine);
      }
      
      $SQL = "select FileName, FolderID, Date, Size from ". $tmp_arr[1] . " where ID='". $tmp_arr[2] ."'";
      $result = mysqli_query($msManager_link, $SQL);
      if($row = mysqli_fetch_assoc($result) ){
        $the_rd_arr = array('FileType'=>$tmp_arr[0],'MS'=>$tmp_arr[1],'ID'=>$tmp_arr[2], 'taskID'=>'', 'index'=>$line_num );
        if(count($tmp_arr)>3){
          $the_rd_arr['taskID'] = $tmp_arr[3];
        } 
        $row = array_merge($row, $the_rd_arr); 
        if(!isset($added_files_arr[$tmp_arr[1]])){
          $added_files_arr[$tmp_arr[1]] = array();
          array_push($machine_arr, $tmp_arr[1]);
        }
        array_push($added_files_arr[$tmp_arr[1]], $row);
        if(!in_array($row['FolderID'], $selectd_folder_arr)){
          array_push($selectd_folder_arr, $row['FolderID']);
        }  
        $line_num++;
      }
    }
    
    
    if($fd) fclose($fd);
    if($theAction == "delete"){
      if($before_type == 'others'){   
        if($added_files_arr){
          $theAction = "others";
        }else{
          $before_type = "local_interf";
        }
      }elseif($before_type == 'ftp'){
        if($added_files_arr){
          $theAction = "ftp";
        }else{
          $before_type = "local_interf";
        }  
      }else{
        $before_type = "local_interf";
      }
    }
  }
}
/*
echo "<pre>";
print_r($added_files_arr);
echo "</pre>";*/

$download_package_info = check_download_package_folder();
if($theAction == 'runFTP'){
  $running_log = getRunningFtpLog($USER->Username);
   
  if($running_log){
    $ftp_error_msg = "The task '$running_log' is running!";
  }else{
    $fd = fopen($log_path, 'a+');
    $taskID_out_put = '';
    if($export_type == 'ftp_massive'){
      
      $row_num = 0;
      $tmpName = "frm_taskID_$row_num";
      while(isset($$tmpName)){
        if($taskID_out_put) $taskID_out_put .= "\r\n";
        $taskID_out_put .= "#*$row_num"."_".$$tmpName;
        $row_num++;
        $tmpName = "frm_taskID_$row_num";
      }
      fwrite($fd, "\r\n#export_type:$export_type");
      fwrite($fd, "\r\n#fasta_file:$frm_fasta_file");
      fwrite($fd, "\r\n#fileIndex: taskID_tppID_seachEngine");
      fwrite($fd, "\r\n$taskID_out_put");
      fwrite($fd, "\r\n");
    }
    
    
    
    fwrite($fd, "\r\n#IP:$frm_remote_ip\r\n#UserName:$frm_remote_username\r\n#password:$frm_remote_password\r\n#type:$frm_remote_type");
    fwrite($fd, "\r\n#Folder:$frm_remote_folder\r\n#NewFolder:$frm_remote_folder_new");
    fwrite($fd, "\r\n>>".@date("Y-m-j G:i:s"));
    fwrite($fd, "\r\n#------------");
    fclose($fd);
    
    
    $cmd = PHP_PATH . " shell_ftp_upload.php $SID ".$USER->Username." $export_file_log ".$_SERVER['SERVER_NAME'];
    
    if(defined('DEBUG_FTP_EXPORT') and DEBUG_FTP_EXPORT){
      $cwd =  getcwd();
      $cmd = "cd $cwd; ". $cmd;
      
      $ftp_msg = "<b>Log path: $log_path.\n<br>This page is stopped on debug mode. <br>If you are Prohits administrator, copy the command line to shell</b><br>\n $cmd<br><br>\n";
    }else{
      $tmp_PID =  system($cmd."> /dev/null & echo \$!");
      if($frm_remote_folder_new) $frm_remote_folder = $frm_remote_folder_new;
      $ftp_msg = "The following command is running in the background.<br>$cmd<br>
      Click the [Log] to see the status. <br>
      Click the [Contents of Remote Folder] to see uploaded files.<br>";
    }
  }
  $theAction = 'testFTP';
}

if($added_files_arr and ($theAction =='local' or $theAction =='ftp' or $theAction =='shared')){
  if($theAction =='shared'){
    if(isset($DOWNLOAD_SHARED_FOLDER['SOURCE']) and $DOWNLOAD_SHARED_FOLDER['SOURCE']){
      $user_shared_folder = $DOWNLOAD_SHARED_FOLDER['SOURCE']."".$USER->Username;
      if(!_is_dir($user_shared_folder)){
        if(!mkdir($user_shared_folder, 0777, true)){
          echo "can not make folder: $user_shared_folder";exit;
        }
        sleep(3);
      }
    }else{
      echo '$DOWNLOAD_SHARED_FOLDER is not defined in conf file.';exit;
    }
  }
  $raw_file_folder = $user_raw_export_dir."/files";
  if(!_is_dir($raw_file_folder)){
    if(!mkdir($raw_file_folder, 0777, true)){
      echo "can not make folder: $raw_file_folder";exit;
    }
  }
  if(_is_file($raw_file_folder."/wget.log")){
    unlink($raw_file_folder."/wget.log");
  }
  $file_needed_arr = array();
  
  //print_r($added_files_arr);exit;
  foreach($added_files_arr as $tableName=>$table_arr){
    foreach($table_arr as $row){
      if($row['FileType'] == 'RAW'){
        $theFilePath = getFilePath($tableName, $row['ID'], $type='full', $msManager_link);
        array_push($file_needed_arr, $row['FileName']);
        if($theAction == 'local' and !_is_file($raw_file_folder."/".$row['FileName'])){
          if(!copy($theFilePath, $raw_file_folder."/".$row['FileName'])){
            echo "error: cannot copy file to $raw_file_folder.<br>";
          }
        }else if($theAction == 'shared'){
          if(!_is_file($user_shared_folder."/".$row['FileName'])){
            if(!copy($theFilePath, $user_shared_folder."/".$row['FileName'])){
              echo "error: cannot copy file to $user_shared_folder.<br>";
            }
          }
        }
      }else{
        //function will check if _is_file
        $tmp_fileName = download_search_result($row['FileType'], $row['MS'], $row['taskID'], $row['ID'], $raw_file_folder);
        if($theAction == 'shared' and _is_file($raw_file_folder."/".$tmp_fileName)){
          $cmd = "mv \"".$raw_file_folder."/".$tmp_fileName."\" $user_shared_folder";
          system($cmd);
        }
        if($tmp_fileName){
          array_push($file_needed_arr, $tmp_fileName);
        }
      }
    }
  }
  if($theAction != 'shared'){
    //remove extra files in raw_file_folder (tmp folder)
    $dir = "$raw_file_folder"; 
    $dp = opendir("$dir");
    while (false !==($file = readdir($dp))) {
      if (!in_array($file, $file_needed_arr) and $file != 'wget.log' and $file!="." and $file != ".."){
        unlink($dir."/".$file);
      }
    }
    closedir($dp);
  }
}elseif($theAction =='copy_files_to_other_folder' && $AccessUserType == 'Admin'){
  $fileFamily = array();
  $RAW_ID_arr = array();
  $exist_files_arr = array();
  $copied_files_array = array();
  $failed_to_copy_arr = array();
   
  foreach($added_files_arr as $tableName=>$table_arr){
    foreach($table_arr as $fileInfo){
      $tmpArr = getFileFamily($tableName, $fileInfo, 'full', $msManager_link);
      foreach($tmpArr as $key => $val){
        if(!array_key_exists($key, $fileFamily)){
          $fileFamily[$key] = $val;
          if($val['RAW_ID']){
            if(!array_key_exists($val['RAW_ID'], $RAW_ID_arr)){
              $RAW_ID_arr[$val['RAW_ID']] = array();
            }
            array_push($RAW_ID_arr[$val['RAW_ID']], $val['ID']);  
          }elseif($val['ID']){
            if(!array_key_exists($val['ID'], $RAW_ID_arr)){
              $RAW_ID_arr[$val['ID']] = array();
            }
          }
        }
      }
    }
  }  
  
  $dest_DB_files_arr = array();
  $folder_exist_flag = 0;
  if($new_old == 'old' && $folder_id){
    $path_to = getFilePath($machine_name_new, $folder_id, 'full', $msManager_link);
     
    $SQL = "SELECT `ID`,`FileName`,`FileType`,`FolderID`,`ProjectID`,`RAW_ID` FROM $machine_name_new WHERE ID='$folder_id'";
    $results = mysqli_query($msManager_link, $SQL);
    if($row = mysqli_fetch_assoc($results)){
      $folder_name = $row['FileName'];
      $frm_Projects = $row['ProjectID'];
    }
    
    $SQL = "SELECT `ID`,`FileName`,`FileType`,`FolderID`,`Size`,`RAW_ID` FROM $machine_name_new WHERE FolderID='$folder_id'";
    $results = mysqli_query($msManager_link, $SQL);
    while($row = mysqli_fetch_assoc($results)){
      $dest_DB_files_arr[$row['FileName']] = $row['ID'];
    } 
  }else{
    $folder_id = 0;
    $dir_header = end_with_slash(STORAGE_FOLDER);
    $path_to = $dir_header . $machine_name_new .'/'. $folder_name;
    
    $SQL = "SELECT `FileName` FROM $machine_name_new WHERE `FolderID`=0 AND `FileName`='$folder_name'";
    $results = mysqli_query($msManager_link, $SQL);
    if($row = mysqli_fetch_assoc($results)){
      $folder_exist_flag = 1;
    }    
    if(!$folder_exist_flag){
      $SQL = "INSERT INTO $machine_name_new SET
              `FileName`='".$folder_name."',
              `FileType`='dir',
              `FolderID`='0',
              `User`='".$userID."',
              `ProjectID`='".$frm_Projects."',
              `Date`=now(),
              `Size`=''";
      if(mysqli_query($msManager_link, $SQL)){
        $folder_id = mysqli_insert_id($msManager_link);
      }else{
        exit;
      }
    }  
  }   
  
  if(!$folder_exist_flag){
    if(!_is_dir($path_to)){
      if(!mkdir($path_to, 0777, true)){
        echo "can not make folder: $path_to";exit;
      }
    }
    echo "<div style='display:block' id='process'>
            Please be patient. copying is processing\n<br><img src='../../analyst/images/process.gif' border=0>
          </div>\n";
    ob_flush();
    flush();   
    foreach($RAW_ID_arr as $old_raw_id => $sub_id_arr){
      if($new_raw_id = copy_insert($old_raw_id)){
        foreach($sub_id_arr as $sub_id){
          copy_insert($sub_id,$new_raw_id);
        }
      }
    } 
    $tmp_files = scandir($path_to);
    $total_size = 0;
    foreach($tmp_files as $tmp_file){
      $tmp_file_full_name = $path_to."/".$tmp_file;
      if(is_file($tmp_file_full_name)){
        $file_size = filesize($tmp_file_full_name);
        $total_size += $file_size;
      }
    }
    $total_size = round($total_size/1024);
    $SQL = "UPDATE $machine_name SET
            `Size`='".$total_size."'
            WHERE `ID`='".$folder_id."'";
    mysqli_query($msManager_link, $SQL);
    
    echo "\n<script language='javascript'>
              document.getElementById('process').style.display = 'none';
          </script>\n";
    
  }else{
    $theAction = "others";
    $message = "The folder's name $folder_name is exist. Please change to another name";
  }  
}  
if($theAction == 'local'){
    $cwd = getcwd();
    chdir($user_raw_export_dir);
    if(_is_file("$user_raw_export_dir/files.zip")) unlink("$user_raw_export_dir/files.zip");
    $size = _filesize("$user_raw_export_dir/files");
     
    $max = (!is_64bit())? 2000: MAX_DOWNLOAD_SZIE;
    if($size/(1024) > $max){
      echo "The total file size is more then ".$max."MB. Larger files can be transferred via FTP.";
      exit;
    }
    echo "\n<span id=\"processing\"><center><font face=\"Arial\" color=\"#000080\"><b>Creating Zip File. Please be patient</font></b>.<br><br>
        <img src='./zip.gif'></center></span>\n";
     
    flush();
    $myshellcmd = "zip -0r files.zip files;";
     
    $result = @exec($myshellcmd);
    chdir($cwd);
    echo "\n<script>processing.style.display='none'</script>\n";
    echo "<h2>Files have been zipped in a folder 'files'. Click the icon to download the zipped file.<br>
    <div align=center>
    <a href='download_raw_file.php?clicked=clicked&SID=rawDataConverter&tableName=$tableName&filePath=". $user_raw_export_dir . "/files.zip'>
    <img src=../images/icon_zip.png border=0 valign=top>
    </a>
    </div>";
    exit;
}else if($theAction == 'ftp' or $theAction == 'ftp_massive'){
  $display_ftp_form = 1;
}else if($theAction == 'testFTP'){
  $remote_dir_arr = array();
  if($frm_remote_ip and $frm_remote_type and $frm_remote_username and $frm_remote_password){
    if(check_connection($frm_remote_username, $frm_remote_password, $frm_remote_ip, $frm_remote_type)){
      $ftp_msg .=  "Remote connection is ok.";
      $ftp_connection_ok = 1;
      if($frm_remote_type == 'ftp'){
      
        if($export_type == 'ftp_massive'){
          //get file task ids
          $ftp_msg .= ". Yellow highlighted is parsed results.";
          
           
          foreach($added_files_arr as $tableName=>$table_arr){
            $tpp_ID_array = array();
            $tppID_searchTaskID_arr[$tableName] = array();
            
            foreach($table_arr as $fileInfo){
               
              //get each file search tasks
             
              $SQL1 = "SELECT `TppTaskID`, `SearchEngine`, `SavedBy` FROM ".$tableName."tppResults WHERE `WellID`='".$fileInfo['ID']."'";
              $result = mysqli_query($msManager_link, $SQL1);
              while($row = mysqli_fetch_assoc($result) ){
                if(!in_array($row['TppTaskID'], $tpp_ID_array)){
                  $tpp_ID_array[] = $row['TppTaskID'];
                }
                if($row['SavedBy']){
                  if($row['SearchEngine'] == 'iProphet' and isset($raw_file_tpp_taskID_saved_arr[$tableName][$fileInfo['ID']])){
                    array_unshift($raw_file_tpp_taskID_saved_arr[$tableName][$fileInfo['ID']],$row);
                  }else{
                    $raw_file_tpp_taskID_saved_arr[$tableName][$fileInfo['ID']][] = $row;
                  }
                }else{
                  $raw_file_tpp_taskID_arr[$tableName][$fileInfo['ID']][] = $row;
                }
              }
              $SQL2 = "SELECT `TaskID`, `SearchEngines`, `SavedBy` FROM ".$tableName."SearchResults WHERE `WellID`='".$fileInfo['ID']."'";
               
              $result = mysqli_query($msManager_link, $SQL2);
              while($row = mysqli_fetch_assoc($result) ){
                if($row['SavedBy']){
                  $raw_file_searchResults_taskID_saved_arr[$tableName][$fileInfo['ID']][] = $row;
                }else{
                  $raw_file_searchResults_taskID_arr[$tableName][$fileInfo['ID']][] = $row;
                }
              }
            }
            if($tpp_ID_array){
              $tppTaskID_str = implode(",", $tpp_ID_array);
              $SQL = "SELECT `ID`, `SearchTaskID` FROM ".$tableName."tppTasks WHERE `ID` in ($tppTaskID_str)";
              //echo $SQL;
              $result = mysqli_query($msManager_link, $SQL);
              while($row = mysqli_fetch_assoc($result) ){
                //$tppID_searchTaskID_arr[$row['ID']] = $row['SearchTaskID'];
                $tppID_searchTaskID_arr[$tableName][$row['ID']] = $row['SearchTaskID'];
              }
            }
          }
          
          //get gpm tax xml file.
          $fasta_dir = "../../TMP/fastas";
          if(!_is_dir($fasta_dir)){
            _mkdir_path($fasta_dir);
          }
          $tpp_in_prohits = is_in_local_server('TPP');
          if($tpp_in_prohits){
            
            if(defined('GPM_CGI_PATH')){
              $gpm_taxonomy_file = dirname(GPM_CGI_PATH) . "/tandem/taxonomy.xml";
            }else{
              $gpm_taxonomy_file = "../../EXT/thegpm/tandem/taxonomy.xml";
            }
          }else{
            $gpm_taxonomy_file =  $fasta_dir."/taxonomy.xml";
            $http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
            $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
            $postData = "tpp_myaction=downloadTppXML&fileName=" . "../tandem/taxonomy.xml";
            if(!copy($tpp_formaction."?".$postData, $gpm_taxonomy_file)){
              $msg = "failed to copy to gpm taxonomy.xml\n";
            }
          }
          if(!_is_file($gpm_taxonomy_file)){
            $gpm_taxonomy_file = '';
          }
           
        }
        
        $systype = @ftp_systype($ftp_conn_id);
        $contents = ftp_rawlist ($ftp_conn_id, ".");
        if(count($contents)){
          foreach($contents as $line){
            if($systype == 'Windows_NT'){
              $fields = preg_split("/[\s]+/",$line, 4);
              if($fields[2] == '<DIR>'){
                array_push($remote_dir_arr, $fields[3]);
              }
            }else{ //linux
              $fields = preg_split("/[\s]+/",$line, 9);
              if(strpos($fields[0], "d") === 0){
                 array_push($remote_dir_arr, $fields[8]);
               }
             }
          }
        }
      }else{ //sftp
        //print_r($sftp);exit;
        $files_arr = $sftp->rawlist();
        foreach($files_arr as $tmp_name=>$file_sta_arr){
          if($file_sta_arr['type']==2 or $file_sta_arr['size'] == 4096){
            if(strpos($tmp_name, '.')!==0)
            array_push($remote_dir_arr, $tmp_name);
          }
        }
      }
    }else{ 
      $ftp_error_msg .= "Couldn't connect the remote site";
    }
  }else{
    $ftp_error_msg .= "Please input ftp account information.";
  }
  $display_ftp_form = 1;
}else if($theAction == 'shared'){
  echo "<p><font face='Arial'>";
  echo "<font size='+2' color='#660000'><b>Copy Files to Prohits Shared Folder</b></font><hr size=1 noshade>";
  echo "Files have been copied to Prohits shared folder. It is temporary folder. Files are 3 month old will be automatically removed. You have to use institute computer to access the folder.<br>";
  echo "<br><b>Computer</b>: ".$DOWNLOAD_SHARED_FOLDER['SOURCE_COMPUTER']['ADDRESS'];
  echo "<br><b>Folder</b>:      ". $DOWNLOAD_SHARED_FOLDER['SOURCE_COMPUTER']['RAW_DATA_FOLDER']. "\\".$USER->Username;
  echo "<br><br>copy following line in Windows Explorer to access your files. If there is permission problem please contact Prohits administrator.";
  echo "<br><br><b><font size=+1 color=#006666>\\\\";
  echo $DOWNLOAD_SHARED_FOLDER['SOURCE_COMPUTER']['ADDRESS'] ."\\". $DOWNLOAD_SHARED_FOLDER['SOURCE_COMPUTER']['RAW_DATA_FOLDER'] . "\\". $USER->Username;
  echo "<br><br><img src='../images/windows_explorer.png'>";
  echo "</font></font>";
  exit;
}elseif($theAction =='copy_files_to_other_folder'){
  $log_path = $user_raw_copy_log_dir . "/". $export_file_log;
  if(!is_file($log_path)){
    $fd = fopen($log_path, 'w');
    fwrite($fd, "\r\nSource Path\tTarget Path\tStatus");
  }else{
    $fd = fopen($log_path, 'a+');
  }
?>
  <center>
  <table width='760'>
    <tr>
      <td>
      <div style="display: block;border: #708090 solid 1px; margin:0px 0px 0px 1px; padding: 10px 10px 10px 10px; width:100%;background-color:#ececec">
<?php 
  $tmp_dir_arr = explode($machine_name, $path_to, 2);
  $folder_path = $tmp_dir_arr[1];
  if($copied_files_array){
    write_to_copy_log($fd,$machine_name,$copied_files_array);
    echo "Following files have been copied to folder $machine_name$folder_path:<br>";
    foreach($copied_files_array as $files_info){
      echo $files_info['FileName']."<br>";
    }
  }
  if($exist_files_arr){
    write_to_copy_log($fd,$machine_name,$exist_files_arr,'failed[file is existing]');
    if($copied_files_array) echo "<br>";
    echo "Following files are existing in folder $machine_name$folder_path and failed to copy.<br>";
    foreach($exist_files_arr as $files_info){
      echo $files_info['FileName']."<br>";
    }
  }
  if($failed_to_copy_arr){
    write_to_copy_log($fd,$machine_name,$failed_to_copy_arr,'broken[system problem]');
    if($copied_files_array || $exist_files_arr) echo "<br>";
    echo "Following files are failed to copy to $machine_name$folder_path because system problem.<br>";
    foreach($failed_to_copy_arr as $files_info){
      echo $files_info['FileName']."<br>";
    }
  }
  fclose($fd);
?>    
      </div>
      </td>
    </tr>
  </table>
  </center>
<?php 
exit;
}
if(count($machine_arr) == 1){
  $machine_name = $machine_arr[0];
}else{
  $machine_name = '';
}
?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 <html>
  <head>
  <style>
    td, th{
    font-family:arial,sans-serif;
    font-size:10pt;
    }
    
   .c { background-color:yellow; }
 
  </style>
  <script language='javascript'>
  function onlyAlphaNumerics(checkString){
    var regExp = /^[\-_A-Za-z0-9]$/;
    for(var i = 0; i < checkString.length; i++){
      if(!checkString.charAt(i).match(regExp)){
        return false;
      }
    }
    return true;
  }
  
  function remove_all(){
    if(confirm("Are you sure that you want to empty the list?")){
      submitForm('delete');
    }
  }
  
  function switch_type(dothis){
    theForm = document.listform;
    var export_type = theForm.export_type;
    var export_file_log = theForm.export_file_log;
    var before_type = theForm.before_type.value;
    for(var i=0; i<export_type.length; i++){
      if(export_type[i].checked && export_type[i].value == before_type){
        return;
      }
    }     
    for(var i=0; i<export_type.length; i++){
      //if(before_type == 'others')
      if(export_type[i].checked && export_type[i].value == 'others'){
        if(!theForm.machine_name.value){
          alert("The  source files for copying should come from the same machine");
          return;
        }
        for(var j=0; j<export_file_log.length; j++){
          if(export_file_log[j].selected){
            theForm.switch_moment_log_name.value = export_file_log[j].value;
            break;
          }  
        }
      }else{
        if(before_type != 'others'){
          theForm.switch_moment_log_name.value = '';
        }
      }
    }
    for(var i=0; i<export_type.length; i++){
      if(export_type[i].checked) theForm.before_type.value = export_type[i].value;
    } 
    theForm.theAction.value = dothis;
    theForm.target = '_self';
    theForm.action = '<?php echo $PHP_SELF;?>';
    theForm.submit();
  }
  
  function submitForm(dothis){
    var theForm = document.listform;
    var export_type_value = '';
    theForm.theAction.value = dothis;
    if(dothis == 'logView'){
      sel = theForm.export_file_log;
      if(sel.options[sel.selectedIndex].value == ''){
        alert('Please select a log file!');
        return;
      }
    }
    if(dothis == 'ftpRemoteList' || dothis == 'logView'){
      popWin=window.open('', 'listWin', 'width=670,height=400,status=yes,resizable=yes,scrollbars=yes');
      popWin.focus();
      theForm.target = 'listWin';
      theForm.action = 'export_raw_files_log.php';    
    }else if(dothis == 'change'){
      theForm.switch_moment_log_name.value = '';
    }else if(dothis == 'shared_local'){
      var export_type = theForm.export_type;
      for(var i=0; i<export_type.length; i++){
        export_type_value = export_type[i].value;
        if(export_type[i].checked && export_type_value == 'shared_interf'){
          theForm.theAction.value = 'shared';
          break;
        }else if(export_type[i].checked && export_type_value == 'local_interf'){
          theForm.theAction.value = 'local';
          break;
        }  
      }
    }else if(dothis == 'runFTP'){
      var sel = theForm.frm_remote_folder;
      var new_d = theForm.frm_remote_folder_new.value;
      if(!isEmptyStr(new_d)){
        for(var i = 0; i < new_d.length; i++){
          if(!new_d.charAt(i).match(/^[_\-A-Za-z0-9]$/)){
            alert("Only characters A-Z, a-z, 0-9, '_' and '-' are valid.");
            return; 
          }
        }
      }else if(isEmptyStr(sel.options[sel.selectedIndex].value)){
        alert('Please select a folder.');
        return;
      }
      var export_type = theForm.export_type;
      for(var i=0; i<export_type.length; i++){
        export_type_value = export_type[i].value;
        if(export_type[i].checked && export_type_value == 'ftp_massive'){
          var sel = theForm.frm_fasta_file;
          if(sel.options[sel.selectedIndex].value == ''){
            if(!confirm("You haven't selected any fasta file. Are you sure you want to continue?")){
              return;
            } 
          }
        }
      }
    }
     
     
    
    theForm.submit();
    theForm.target = '_self';
    theForm.action = '<?php echo $PHP_SELF;?>'; 
  }
  function isEmptyStr(str){
    var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
    if(temstr == 0 || temstr == ''){
       return true;
    } else {
      return false;
    }
  }
  function new_or_old(){
    var obj_id = document.getElementById('folder_id_div');
    var obj_name = document.getElementById('folder_name_div');
    var obj_project= document.getElementById('project_id_div');
    theForm = document.listform;
    if(theForm.new_old[1].checked){
      obj_id.style.display = "block";
      obj_name.style.display = "none";
      obj_project.style.display = "none";
    }else{
      obj_id.style.display = "none";
      obj_name.style.display = "block";
      obj_project.style.display = "block";
    }
  }
  
  function validate_folder_name(){
    theForm = document.listform;
    if(!theForm.machine_name.value){
      return;
    }    
    if(theForm.new_old[0].checked){
      var projectID = theForm.frm_Projects.value;
      if(!projectID){
        alert("Please select a project.")
        return;
      }     
      if(theForm.folder_name.value == null || theForm.folder_name.value == ""){
        alert('Please type folder name.');
        return;
      }else if(!onlyAlphaNumerics(theForm.folder_name.value)){
        alert('-,_,A-Z,a-z,0-9 only');
        return;
      }
      var patt = /(.+)_P\d+$/i;
      var result = theForm.folder_name.value.match(patt);
      if(result){
        theForm.folder_name.value = result[1];
      }
      theForm.folder_name.value = theForm.folder_name.value + "_P" + projectID;
    }else{
      var x = document.getElementById("folder_id").selectedIndex;
      var y = document.getElementById("folder_id").options;
      if(!y[x].value){
        alert("Please select a folder");
        return;
      }
    }
    var index_m = document.getElementById("machine_name_new_id").selectedIndex;
    var options_m = document.getElementById("machine_name_new_id").options;
    if(options_m[index_m].value != theForm.machine_name.value){
      if(!confirm("Do you want to copy files to machine "+options_m[index_m].value+"?")){
        return;
      }
    }
    
    theForm.theAction.value = "copy_files_to_other_folder";
    theForm.action = '<?php echo $PHP_SELF;?>';
    theForm.submit(); 
  }
  
  function remove_file(tableName,ID,taskID,FileType){
    theForm = document.listform;
    theForm.tableName.value = tableName;
    theForm.ID.value = ID;
    theForm.taskID.value = taskID;
    theForm.FileType.value = FileType;
    theForm.theAction.value = 'delete';
    theForm.action = '<?php echo $PHP_SELF;?>';
    theForm.submit(); 
  }
  function change_page(page){
    theForm = document.listform;
    theForm.current_page.value = page;
    theForm.theAction.value = 'others';
    theForm.submit(); 
  }
  </script>
  </head>
  <body style="font-family: arial,sans-serif; font-size: 9pt; background-color: #ccc;\">
   <form action='<?php echo $PHP_SELF;?>' name=listform method=post>
    <input type=hidden name='theAction' value=''>
    <input type=hidden Name=SID value='<?php echo $SID;?>'>
    <input type=hidden Name=machine_name value='<?php echo $machine_name;?>'>
    <input type=hidden Name=block_none value='<?php echo $block_none;?>'>
    <input type=hidden Name=folder_name_readOnly value=''>
    <input type=hidden Name=tableName value=''>
    <input type=hidden Name=ID value=''>
    <input type=hidden Name=taskID value=''>
    <input type=hidden Name=FileType value=''>
    <input type=hidden Name=current_page value='<?php echo $current_page?>'>
    <input type=hidden Name=switch_moment_log_name value='<?php echo $switch_moment_log_name?>'>
    <input type=hidden Name=before_type value='<?php echo $before_type?>'>    
  <div style="padding: 5px 20px; display:block; margin: 0px auto; width:95%; background-color:#FFF; border:#708090 1px solid;">
    <p style="font-family : Lucida Calligraphy; color: #4a4a4a; FONT-SIZE: 18pt;">Export Raw Files
    <hr size=1 noshade>
    <?php     
    if($added_files_arr){
    ?>
	  <div style="padding: 5px 20px; background-color:#6699ff; font: bold; font-weight : bold;">
      <input type='radio' name='export_type' value='ftp' <?php echo (($export_type=='ftp')?'checked':'')?> onclick="switch_type('ftp');">FTP Server
      <?php 
      $download_package_info = check_download_package_folder();
      if($download_package_info['activated']){?>
      <input type='radio' name='export_type' value='shared_interf' <?php echo (($export_type=='shared_interf')?'checked':'')?> onclick="switch_type('shared_interf');">Copy to Shared Folder
    <?php }
      if($USER->Type == 'MSTech' || $USER->Type == 'labTech' || $USER->Type == 'Admin'){
    ?>
      <input type='radio' name='export_type' value='local_interf' <?php echo (($export_type=='local_interf')?'checked':'')?> onclick="switch_type('local_interf');">Download to Local Computer
      <?php if($USER->Type == 'Admin'){?>
      <input type='radio' name='export_type' value='others' <?php echo (($export_type=='others')?'checked':'')?> onclick="switch_type('others');">Copy files to other folder      
       <?php }?>
    <?php }?>
      <input type='radio' name='export_type' value='ftp_massive' <?php echo (($export_type=='ftp_massive')?'checked':'')?> onclick="switch_type('ftp_massive');">Export to MassIVE (FTP)
     </div>  
  <?php }?> 
  <?php if($theAction == 'others' && $added_files_arr){
      $log_array = getFileListFromDir($user_raw_copy_log_dir, 'txt');
      rsort ($log_array);
  ?>
     <p>
      You can select files into a temp package, then copy these files to the folder which you created or selected
     <ul>
     <li>Select raw files from different folder in Storage.</li>
     <li>Select search result files from different search tasks.</li>
     <li>File logs
     <select name="export_file_log"> 
  <?php 
    }else{
      if($theAction == 'others' && !$added_files_arr){
        $theAction = 'local_interf';
      }
  ?>    
    <p>
    You can select files into a temp package, then download the package to your local computer
    <?php if($download_package_info['activated']) echo " or the public shared folder";?>.
      <ul>
      <li>Select raw files from different folder in Storage.</li>
      <li>Select search result files from different search tasks.</li>
      <li>Exported raw file logs
      <select name="export_file_log" onChange="submitForm('change')";>
    <?php }?> 
        <option value=''>
        <?php 
          foreach($log_array as $log){
            $selected = ($log == $export_file_log)? ' selected': '';
            
            echo "<option value='$log'$selected>$log\n";
          }
        ?>
      </select>
      &nbsp; [<a href="javascript: submitForm('logView');"> L O G </a>]
      </ul>
   <?php  
    if($added_files_arr){
   ?>
      <p><b>Files in the exporting list </b>
      <?php 
      if($todayList == $export_file_log){
        echo "<a href=\"javascript: remove_all();\" >[remove all]</a>\n";
      }
      ?>
    <table width=100% cellpadding="2" cellspacing="1">
      <thead bgcolor="#87bdda">
        <tr>
          <th>ID</th>
          <th>TaskID</th>
          <th>Mass Spec</th>
          <th>File Type</th>
          <th>Raw File Name</th>
          <th>Size(MB)</th>
          <th></th>
        </tr>
      </thead>
      <tbody bgcolor="#e1e1e1">
      <?php 
      $row_num = 0;
      foreach($added_files_arr as $tableName=>$table_arr){
        foreach($table_arr as $row){ 
          $tmp_size = round($row['Size']/1024/1024, 2);
          if($tmp_size < 1){
            $tmp_size = round($row['Size']/1024, 2) . "KB";
          }
          $del_qstr = "SID=$SID&theAction=delete&export_file_log=$export_file_log&tableName=$tableName&ID=". $row['ID']."&taskID=".$row['taskID']."&FileType=".$row['FileType'];
          echo "
          <tr>
            <td>".$row['ID']."</td>\n<td>";
         //print_r($tppID_searchTaskID_arr);
        //print_r($raw_file_tpp_taskID_arr);
        //print_r($raw_file_searchResults_taskID_arr);
          
         if($theAction == 'testFTP' and $export_type == 'ftp_massive'){
            $selected = '';
            //echo "<select name='frm_taskID_$row_num'>\n";
            echo "<select name='frm_taskID_".$row['index']."'>\n";
            $option_added = 0;
            if(isset($raw_file_tpp_taskID_saved_arr[$tableName][$row['ID']])){
              foreach($raw_file_tpp_taskID_saved_arr[$tableName][$row['ID']] as $tmpFile){
                $tmp_selected = (!$selected)?" selected":"";
                echo "<option class=c$tmp_selected>task". $tppID_searchTaskID_arr[$tableName][$tmpFile['TppTaskID']]."_tpp". $tmpFile['TppTaskID']."_" . $tmpFile['SearchEngine']."\n";
                $selected = 1;
                 
              }
              $option_added = 1;
            }
            if(isset($raw_file_searchResults_taskID_saved_arr[$tableName][$row['ID']])){
              foreach($raw_file_searchResults_taskID_saved_arr[$tableName][$row['ID']] as $tmpFile){
                $tmp_selected = (!$selected)?" selected":"";
                echo "<option class=c$tmp_selected>task". $tmpFile['TaskID']."_" . $tmpFile['SearchEngines']."\n";
                $selected = 1;
                 
              }
              $option_added = 1;
            }
            if(isset($raw_file_tpp_taskID_arr[$tableName][$row['ID']])){
              foreach($raw_file_tpp_taskID_arr[$tableName][$row['ID']] as $tmpFile){
                $tmp_selected = '';
                if(!$selected and $tmpFile['SearchEngine'] == 'iProphet'){
                  $tmp_selected = " selected";
                  $selected = 1;
                }
                echo "<option$tmp_selected>task". $tppID_searchTaskID_arr[$tableName][$tmpFile['TppTaskID']]."_tpp". $tmpFile['TppTaskID']."_" . $tmpFile['SearchEngine']."\n";
              }
              $option_added = 1;
            }
            if(isset($raw_file_searchResults_taskID_arr[$tableName][$row['ID']])){
              foreach($raw_file_searchResults_taskID_arr[$tableName][$row['ID']] as $tmpFile){
                echo "<option>task". $tmpFile['TaskID']."_" . $tmpFile['SearchEngines']."\n";
              }
              $option_added = 1;
            }
            if(!$option_added){
              echo "<option>\n";
            }
            echo "</select>\n";
         }else{
           echo "&nbsp;";
         }
         
         echo "</td>\n<td>".$tableName."</td>
            <td>".$row['FileType']."</td>
            
            <td>".$row['FileName']."</td>
            <td>".$tmp_size."</td>
            <td><a href=\"javascript: remove_file('".$tableName."','".$row['ID']."','".$row['taskID']."','".$row['FileType']."')\" alt='remove from the list'><img src=../images/icon_delete.gif border=0></a></td>
          </tr>";
          //<td><a href=\"$PHP_SELF?$del_qstr\" alt='remove from the list'><img src=../images/icon_delete.gif border=0></a></td>
         $row_num++;
        }
        
      }?>
      </tbody>
      </table>
      <?php if($gpm_taxonomy_file){?>
        <br>
        <b>Select fasta file</b>: 
        <select name="frm_fasta_file">
          <option value=''></option>\n";
        <?php 
        $xml_P = new xmlParser(); 
        $xml_P->parse($gpm_taxonomy_file);
        foreach($xml_P->output[0]['child'] as $theTAX){
          if($theTAX['name'] == 'TAXON'){
            $theLabel = $theTAX['attrs']['LABEL'];
            $theValue = $theTAX['child'][0]['attrs']['URL'];
            echo "<option value='$theValue|$theLabel'>$theLabel</option>\n";
          }
        }
        ?>
        </select> 
      <?php }?>
      <hr size=1 noshade>
     <?php  
     }
     ?>    
 <?php if(isset($display_ftp_form)){?>
    <?php  echo "<br><font color='#008000'>$ftp_msg</font><font color='#FF0000'>$ftp_error_msg</font>";?>
   <table width=100% cellpadding="2" cellspacing="1">
    <tr>
    	<td colspan=4  bgcolor=#99ccff><b>Remote FTP Address</b> (IP or domain name):
       
    	<input type=text name=frm_remote_ip value='<?php echo $frm_remote_ip;?>' size=30>
      </td>
    </tr>
    <tr>
    	<td bgcolor=#99ccff><b>User Name</b>:</td>
    	<td bgcolor=#99ccff><input type=text name=frm_remote_username value=<?php echo $frm_remote_username;?>></td>
    	<td bgcolor=#99ccff><b>Password</b>:</td>
      <td bgcolor=#99ccff><input type=password name=frm_remote_password value=<?php echo $frm_remote_password;?>></td>
    </tr>
    <tr>
      <td bgcolor=#99ccff><b>Connection Protocol</b></td>
      <td bgcolor=#99ccff>
      <select name=frm_remote_type>
        <option value=''>
        <option value=ftp<?php echo ($frm_remote_type=='ftp')?' selected':'';?>>ftp
        <?php
        if($export_type != 'ftp_massive'){?>
        <option value=sftp<?php echo ($frm_remote_type=='sftp')?' selected':'';?>>sftp
        <?php
        }?>
      </select>
      </td>
      <td bgcolor=#99ccff><b>Upload to Folder</b></td>
      <td bgcolor=#99ccff>
      <select name=frm_remote_folder>
        <option value=''>
        <?php 
        if(isset($remote_dir_arr)){
          foreach($remote_dir_arr as $tmp_dir){
            $selected = ($frm_remote_folder==$tmp_dir)?' selected':'';
            echo "<option value='$tmp_dir'".$selected.">$tmp_dir\n";
          }
        }
        ?>
         
      </select>
      <br><input type=text name=frm_remote_folder_new size=25 value='<?php echo $frm_remote_folder_new;?>'> new folder
      </td>
    </tr>
    <tr>
    	<td colspan=4  bgcolor=#99ccff  align="center">
      <input type=button value='Test Connection' onClick="submitForm('testFTP');">
      <?php  if(isset($ftp_connection_ok)){?>
      <input type=button value='Contents of Remote Folder' onClick="submitForm('ftpRemoteList');">
      <input type=button value='Upload Files' onClick="submitForm('runFTP');">
      <?php }?>
      </td>
    </tr>
  </table> 
<?php }elseif($theAction == "others" && $added_files_arr){
    $project_arr = get_project_list($userID);
    $project_list = implode(",", $project_arr);
    $folder_arr = array();
    $theMachine = $machine_name;
    if($machine_name_new){
      $theMachine = $machine_name_new;
    }
    get_dir_tree($msManager_link, $theMachine, $project_list);
    //--------------------------------------------------------------------------------------
    $total_folders = count($folder_arr);
    $num_p_page = 200;
    $total_pages = ceil($total_folders/$num_p_page); 
    //--------------------------------------------------------------------------------------

?>
  <div id="copy_to_other" style="display: block;float:left;border: #708090 solid 1px;margin:10px 0px 10px 1px; padding: 0px 0px 0px 0px; width:99.5%">
    <table width=100% cellpadding="2" cellspacing="1">
      <tr bgcolor="#87bdda">
        <td width='53%'>
         
        <select id="machine_name_new_id" name="machine_name_new" onChange="submitForm('others')";>
        <?php foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
            $selected = '';
            if((!$machine_name_new and $baseTable==$machine_name) or $baseTable==$machine_name_new){
              $selected = ' selected';
            }
         
            echo "<option  value='$baseTable'$selected>$baseTable\n";
        }?>
        </select>
        
         
          <div style="display: block;float:right;border: #708090 solid 0px;margin:0px 20px 0px 0px; padding: 0px 0px 0px 0px;">
          <input type='radio' name='new_old' value='new' <?php echo (($new_old=='new')?'checked':'')?> onClick="new_or_old()">create a new folder&nbsp;
          <input type='radio' name='new_old' value='old' <?php echo (($new_old=='old')?'checked':'')?> onClick="new_or_old()">existing folder
          </div>
        </td>
        <td><b>Project</b></td>
      </tr>
      <tr bgcolor='#e1e1e1'>
        <td nowrap>
          <div id="folder_id_div" style="display: <?php echo (($new_old=='old')?'block':'none')?>;float:left;border: #708090 solid 1px;margin:10px 0px 10px 1px; padding: 0px 0px 0px 0px; width:99.5%">    
            <select id="folder_id" name="folder_id" id="folder_id">
              <option value=''>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;               
          <?php 
            $dots = '';
            $folder_counter = -1; 
            $start = ($current_page - 1) * $num_p_page; 
            $end = $start + $num_p_page - 1;
            foreach($folder_arr as $key => $val){
              if(in_array($val['ID'], $selectd_folder_arr)){
                continue;
              }  
              $folder_counter++;
              if($folder_counter < $start) continue;
              if($folder_counter > $end) break;
              $folder_level = $val['level'] - 1;
              
              $dots = str_repeat(". . ", $folder_level);
          ?>
              <option  value='<?php echo $val['ID']?>' <?php echo ($val['ID']==$folder_id)?" selected":""?>><?php echo $dots?>(<?php echo $val['ID']?>) <?php echo $val['FileName']?>
          <?php }?>
            </select>&nbsp;
          <?php 
            for($i=1;$i<=$total_pages;$i++){
              if($current_page == $i){
                $page_lable = "<font color=red>$i</font>";
              }else{
                $page_lable = $i;
              }
          ?>
             <a href="javascript: change_page('<?php echo $i?>')"><?php echo $page_lable?></a>
          <?php }?>&nbsp;
          </div>
          <div id="folder_name_div" style="display: <?php echo (($new_old=='new')?'block':'none')?>;float:left;border: #708090 solid 1px;margin:10px 0px 10px 1px; padding: 0px 0px 0px 0px; width:99.5%">    
            <?php echo $message?>
            <input type='text' name="folder_name" id="folder_name" size='50' value="<?php echo $folder_name?>">
          </div>                 
        </td>
        <td>
          <div id="project_id_div" style="display: <?php echo (($new_old=='new')?'block':'none')?>;float:left;border: #708090 solid 1px;margin:10px 0px 10px 1px; padding: 0px 0px 0px 0px; width:99.5%">        
              <select name="frm_Projects" id="frm_Projects">
                <option value=''>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;               
          <?php 
                foreach($ProjectNameIDarr as $key => $val){
                  echo  "<option  value='".$key."'";
                  echo ($key==$frm_Projects)?" selected":"";   
                  echo ">"."(".$key.") ".$val."\n";
                } 
          ?>
              </select>
           </div>
        </td>
      </tr>
      <tr bgcolor='#e1e1e1'  align='center'>
        <td colspan=2><input type=button value=' Paste ' onclick="validate_folder_name()"></td>
      </tr>
    </table>
  </div>      
<?php }?>
   <center>
  <?php if(($export_type == 'shared_interf' || $export_type == 'local_interf') && $added_files_arr){?>
      <input type=button value='Submit' onclick="submitForm('shared_local');">
  <?php }else{?>  
    &nbsp;<hr size=1 noshade>
  <?php }?> 
      <input type=button value='Close Window' onclick='window.close();'>
    </center> 
  </div>
  </form>
</body>
</html>
<?php 

function get_user_permited_project_id_name($prohits_link, $AccessUserID, $intert_only=0){
  $SQL = "SELECT P.ID, P.Name 
          FROM Projects P, ProPermission M
          WHERE M.UserID = $AccessUserID 
          AND M.ProjectID=P.ID";
  if($intert_only){
    $SQL .= " AND M.Insert=1";
  }
  $SQL .= " ORDER BY P.Name";
   
  $result = mysqli_query($prohits_link, $SQL);
  $ProjectNameIDarr=array();
  while($row = mysqli_fetch_row($result)){
    $ProjectNameIDarr[$row[0]] = $row[1];
  }
  ksort($ProjectNameIDarr);
  return $ProjectNameIDarr;
}

function copy_insert($file_id,$new_raw_id=''){
  global $fileFamily;
  global $path_to;
  global $frm_Projects;
  global $userID;
  global $machine_name;
  global $machine_name_new;
  global $folder_id;
  global $msManager_link;
  global $dest_DB_files_arr;
  global $exist_files_arr;
  global $copied_files_array;
  global $failed_to_copy_arr;
  
   
  $fileProperty = $fileFamily[$file_id];
  $sourceFile = $fileProperty['fileFullName'];
  $destFile = $path_to.'/'.$fileProperty['FileName'];
  
  if(array_key_exists($fileProperty['FileName'], $dest_DB_files_arr)){
    $fileProperty['Dest_FolderID'] = $folder_id;
    $fileProperty['Dest_ProjectID'] = $frm_Projects;
    $fileProperty['Dest_Path'] = $path_to;
    array_push($exist_files_arr, $fileProperty);
    $file_id = $dest_DB_files_arr[$fileProperty['FileName']];
    return $file_id;
  }else{
    if(!copy($sourceFile, $destFile)){
      $fileProperty['Dest_Path'] = $path_to;
      array_push($failed_to_copy_arr, $fileProperty);
      echo "failed to copy $sourceFile...\n";
    }else{
      echo(" . ");
	    ob_flush(); flush();
      $destFile_file_size = filesize($destFile);
      $SQL = "INSERT INTO $machine_name_new SET
              `FileName`='".$fileProperty['FileName']."',
              `FileType`='".$fileProperty['FileType']."',
              `FolderID`='".$folder_id."',
              `User`='".$userID."',
              `ProjectID`='".$frm_Projects."',
              `Size`='".$destFile_file_size."',
              `ConvertParameter`='".$fileProperty['ConvertParameter']."',
              `Date`=now(),
              `RAW_ID`='".$new_raw_id."'";
      if(mysqli_query($msManager_link, $SQL)){
        //array_push($copied_files_array, $fileProperty['FileName']);
        $ret_id = mysqli_insert_id($msManager_link);
        $fileProperty['Dest_FolderID'] = $folder_id;
        $fileProperty['Dest_ProjectID'] = $frm_Projects;
        $fileProperty['Dest_Path'] = $path_to;
        $fileProperty['Dest_ID'] = $ret_id;
        $copied_files_array[$ret_id] = $fileProperty;
        return $ret_id;
      }else{
        return false;
      }
    }
  }
}

function get_dir_tree_($msManager_link, $table, $project_list){
  $dirIDsArr = array();
  if(!$project_list) return $dirIDsArr;
  $parents = array('ID'=>'0');
  $queue = array();
  array_push($queue, $parents);
  while(count($queue)){
    $tmp_folder_arr = array_pop($queue);
    $firstItem = $tmp_folder_arr['ID'];
    if(count($tmp_folder_arr) > 1){
      array_push($dirIDsArr, $tmp_folder_arr);
    }
    $SQL = "SELECT `ID`,`FileName`,`FileType`,`FolderID`,`ProjectID` FROM $table WHERE `FolderID`='$firstItem' AND `FileType`='dir' AND `ProjectID` IN($project_list) ORDER BY `ID` ASC";
    
    $result = mysqli_query($msManager_link, $SQL);
    while($row = mysqli_fetch_assoc($result)){
      array_push($queue, $row);
    }
  }
  return $dirIDsArr;
}
function get_dir_tree($msManager_link, $table, $project_list, $FolderID = 0, $level= 0){
  global $folder_arr;
  $SQL = "SELECT `ID`,`FileName`,`FileType`,`FolderID`,`ProjectID` FROM $table WHERE `FolderID`='$FolderID' AND `FileType`='dir' AND `ProjectID` IN($project_list) ORDER BY `ID` DESC";
  $level++;
  $result = mysqli_query($msManager_link, $SQL);
  while($row = mysqli_fetch_assoc($result)){
    array_push($folder_arr, array('level'=>$level, 'ID'=>$row['ID'],'FileName'=>$row['FileName']));
    get_dir_tree($msManager_link, $table, $project_list, $row['ID'], $level);
  }
}

function write_to_copy_log($fd,$machine_name,$copied_files_array,$Status='Success'){
  foreach($copied_files_array as $value){
    $tmp_arr = explode($machine_name, $value['fileFullName']);
    $Source_Path = $machine_name.$tmp_arr[1];
    $tmp_arr = explode($machine_name, $value['Dest_Path']);
    $Target_Path = $machine_name.$tmp_arr[1].'/'.$value['FileName'];
    fwrite($fd, "\r\n".$Source_Path."\t".$Target_Path."\t$Status");
  }
}
//----------------------------------------------------------------
function getFileFamily($tableName, $fileInfo, $type='full', $msdb_link=0){
//----------------------------------------------------------------
  global $msManager_link;  
  $ID = $fileInfo['ID'];
  if(!$msdb_link)  $msdb_link = $msManager_link;
  $files_Info_arr = array();
  $path = getFilePath($tableName, $ID, $type, $msdb_link);
  $path = dirname($path);
  
  $SQL = "SELECT `ID`,
                `FileName`,
                `FileType`,
                `FolderID`,
                `Date`,
                `User`,
                `ProhitsID`,
                `ProjectID`,
                `Size`,
                `ConvertParameter`,
                `RAW_ID`
        FROM $tableName 
        WHERE (`ID`='$ID' OR `RAW_ID`='$ID') AND `FileType`!='dir'";
  $results = mysqli_query($msdb_link, $SQL);
  while($row = mysqli_fetch_assoc($results)){
    if($row['ID'] == $ID && $row['RAW_ID']){
      $raw_id = $row['RAW_ID'];
      $SQL = "SELECT `ID`,
                    `FileName`,
                    `FileType`,
                    `FolderID`,
                    `Date`,
                    `User`,
                    `ProhitsID`,
                    `ProjectID`,
                    `Size`,
                    `ConvertParameter`,
                    `RAW_ID`
            FROM $tableName 
            WHERE `ID`='$raw_id' 
            OR `RAW_ID`='$raw_id'";
      $results = mysqli_query($msdb_link, $SQL);
      while($row_2 = mysqli_fetch_assoc($results)){  
        $fileFullName = $path . "/" .$row_2['FileName'];
        $row_2['fileFullName'] = $fileFullName;
        if(!array_key_exists($row_2['ID'], $files_Info_arr)){
          $files_Info_arr[$row_2['ID']] = $row_2;
        }  
      }
    }else{
      $fileFullName = $path . "/" .$row['FileName'];
      $row['fileFullName'] = $fileFullName;
      if(!array_key_exists($row['ID'], $files_Info_arr)){
        $files_Info_arr[$row['ID']] = $row;
      }  
    }
  }
  return $files_Info_arr;
}
function get_project_list($userID){
  global $prohits_link;
  $SQL = "SELECT `ProjectID` FROM `ProPermission` WHERE `UserID`='$userID'";
  $result = mysqli_query($prohits_link, $SQL);
  $project_list = array();
  while($row = mysqli_fetch_row($result)){
    array_push($project_list, $row[0]);
  }
  return $project_list;  
}
?>