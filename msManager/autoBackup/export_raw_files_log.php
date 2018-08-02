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
 
include ( "../../config/conf.inc.php");
include ( "./shell_functions.inc.php");
include ( "../is_dir_file.inc.php");
//require_once("../common_functions.inc.php");
set_include_path(get_include_path() . PATH_SEPARATOR . '../../common/phpseclib0.2.2');
include('Net/SFTP.php');


define("STORAGE_TMP_ZIP_FOLDER", "../../TMP/");

$PHP_SELF = $_SERVER['PHP_SELF'];

$theAction = '';
$SID = '';
$export_file_log = ''; 
$ftp_error_msg = '';
$ftp_msg = '';
$uploaded_file_arr = array();
$results_index_arr = array();
$process_end_time = '';
$process_start_time = '';
$process_is_running = 'NO';
$frm_remote_ip = '';
$frm_remote_username = '';
$frm_remote_folder = '';
$frm_remote_type = '';

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value; 
} 
/*
echo "<pre>";
print_r($request_arr);
echo "</pre>";
*/

if(!$SID){
  echo "not enough information passed"; exit;
}
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$prohits_link  = mysqli_connect("$host", $user, $pswd, PROHITS_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
 

$msManager_link  = mysqli_connect("$host", $user, $pswd, MANAGER_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
  
if(!check_download_permission($SID)) {
  echo "please login Prohits.";
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

if($theAction == 'ftpRemoteList'){
  if($frm_remote_ip and $frm_remote_type and $frm_remote_username and $frm_remote_password){
    if(check_connection($frm_remote_username, $frm_remote_password, $frm_remote_ip, $frm_remote_type)){
      $ftp_connection_ok = 1;
      $remote_files_arr = array();
      if($frm_remote_type == 'sftp'){
        if(!$frm_remote_folder) $frm_remote_folder = '.';
        $files_arr = $sftp->rawlist($frm_remote_folder);
         
        foreach($files_arr as $tmp_name=>$file_sta_arr){
          $fileType = "";
          if(strpos($tmp_name, '.')===0) continue;
          $tmp_type = '';
          if($file_sta_arr['type']==2 or $file_sta_arr['size'] == 4096){
            $fileType = 'folder';
          }
          $mtime = @date("Y-m-d H:i:s", $file_sta_arr['mtime']);
          array_push($remote_files_arr, array('name'=>$tmp_name, 'type'=>$fileType, 'time'=>$mtime,  'size'=>$file_sta_arr['size']));
        }
      }else{
        if(!$frm_remote_folder) $frm_remote_folder = '.';
        $systype = @ftp_systype($ftp_conn_id);
        $remote_files_arr = get_ftp_contents($ftp_conn_id,  $frm_remote_folder);
        //print_r($remote_files_arr);
      }
    }else{
      $ftp_error_msg = "Couldn't connect the remote site";
    }
  }else{
    $ftp_error_msg = "Please input ftp account information.";
  }
}else if($theAction == 'logView'){
  $user_raw_export_dir = check_user_raw_export_folder($username);
  $log_path = $user_raw_export_dir . "/". $export_file_log;
  
  if(isset($export_type) && $export_type == 'others'){
    $log_path = str_ireplace("raw_export", "raw_copy", $log_path);
    $lines = file($log_path);
?>

<table width=100% cellpadding="2" cellspacing="1">
<?php     
    foreach($lines as $line){
      $line = trim($line);
      if(!$line) continue;
      $line_arr = explode("\t", $line);
 ?>
      <tr bgcolor="#eaeaea" >
        <td><?php echo $line_arr[0]?></td>
        <td><?php echo $line_arr[1]?></td>
        <td><?php echo $line_arr[2]?></td>
      </tr>   
<?php 
    }
?>
</table>
<?php  
    exit;
  } 
   
  $running_log = getRunningFtpLog($username);
  if($running_log == $export_file_log){
     $process_is_running  = '<font color=red>YES</font>';
  }
  $lines = @file($log_path);
  $lines = array_unique($lines);
  $line_cnn = 1;
  //print_r($lines);exit;
  foreach($lines as $theLine){
    $the_line = trim($theLine);
    if(strpos($the_line, '#*') ===0){
      array_push($results_index_arr, $the_line);
    }else if(strpos($the_line, '#') ===0){
      $the_line = str_replace('#','', $the_line);
      $ftp_arr = explode(":", $the_line);
      if(count($ftp_arr) == 2){ 
        if($ftp_arr[0] == 'IP'){
         $frm_remote_ip =  $ftp_arr[1];
        }else if($ftp_arr[0] == 'UserName'){
         $frm_remote_username =  $ftp_arr[1];
        }else if($ftp_arr[0] == 'type'){
         $frm_remote_type =  $ftp_arr[1];
        }else if($ftp_arr[0] == 'Folder'){
         $frm_remote_folder =  $ftp_arr[1];
        }else if($ftp_arr[0] == 'NewFolder' and $ftp_arr[1]){
         $frm_remote_folder =  $ftp_arr[1];
        }
      }
      continue;
    }else if(preg_match("/^>>([0-9]{4}-[0-9]{2}-[0-9]+.+)/", $the_line, $matches)){
      $process_start_time = $matches[1];
    }else if(strpos($the_line, '>>END>>') ===0){
      $process_end_time = str_replace(">>END>>", "", $the_line);
    }else if(strpos($the_line, '>>') ===0){
      array_push($uploaded_file_arr, $the_line);
    }
    $line_cnn++;
  }
  //print_r($uploaded_file_arr);exit;
}
function get_ftp_contents($ftp_conn_id,  $frm_remote_folder){
  global $systype;
  $fileType = 'file';
  $remote_files_arr = array();
   
  $contents = ftp_rawlist ($ftp_conn_id,  $frm_remote_folder);
  //print_r($contents);
  if(count($contents)){
    foreach($contents as $line){
      $sub_files_arr = array();
      $tmp_size = '';
      $fileType = 'file';
      if($systype == 'Windows_NT'){
        $fields = preg_split("/[\s]+/",$line, 4);
        if($fields[2] == '<DIR>'){
          $fileType = "folder";
          $sub_files_arr = get_ftp_contents($ftp_conn_id,  $frm_remote_folder."/". $fields[3]);
        }else{
          $tmp_size = $fields[2];
        }
        $mtime = $fields[0]. " " . $fields[1];
        
        array_push($remote_files_arr, array('name'=>$fields[3],'type'=>$fileType, 'time'=>$mtime, 'size'=>$tmp_size, 'subFiles'=>$sub_files_arr));
      }else{ //linux
        $fields = preg_split("/[\s]+/",$line, 9);
         if(strpos($fields[0], "d") === 0){
           $fileType = "folder";
           $sub_files_arr = get_ftp_contents($ftp_conn_id,  $frm_remote_folder."/". $fields[8]);
         }
         $mtime = $fields[5]. "" . $fields[6]." ".$fields[7];
         array_push($remote_files_arr, array('name'=>$fields[8], 'type'=>$fileType, 'time'=>$mtime, 'size'=>$fields[4], 'subFiles'=>$sub_files_arr));
      }
    }
  }
  return $remote_files_arr;
}

function ListFolder($remote_files_arr){
    echo "<ul style='list-style: none;'>\n";
    
    foreach($remote_files_arr as $file_sta_arr){
        $img = "folder_open.gif";
        $tmp_size = '';
        if($file_sta_arr['type'] != 'folder'){
          $img = "file_raw.gif";
          $tmp_size = round($file_sta_arr['size']/1024/1024, 2)."MB";
          if($tmp_size < 1){
            $tmp_size = ceil($file_sta_arr['size']/1024)."KB";
          }
        }
        echo "<li><img src='../images/$img' border=0> <b>".  $file_sta_arr['name']."</b> <font color='green'>".$file_sta_arr['time']." ".$tmp_size."</font></li>";
        if($file_sta_arr['subFiles']){
          ListFolder($file_sta_arr['subFiles']);
        }
      }
    echo "</ul>\n";
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
  </style> 
  <script language='javascript'>
  function remove_all(){
    if(confirm("Are you sure that you want to empty the list?")){
      submitForm('delete');
    }
  }
  function submitForm(dothis){
    theForm = document.listform;
    theForm.theAction.value = dothis;
    theForm.submit(); 
  }
  </script>
  </head>
  <body style="font-family: arial,sans-serif; font-size: 9pt; background-color: #cccccc;\">
   <form action='<?php echo $PHP_SELF;?>' name=listform method=post>
    <input type=hidden name='theAction' value=''>
    <input type=hidden Name=SID value='<?php echo $SID;?>'>
   <div style="padding: 20px 30px 10px 30px; display:block; margin: 5px; width:92%; background-color:#FFFFFF; border:#708090 1px solid;">
    <span style="font-family : Lucida Calligraphy; color: #4a4a4a; FONT-SIZE: 18pt;">Remote FTP site 
    <hr size=1 noshade>
    </span>
   
    <UL style="background-color:#6699ff;">
    <li>Remote site IP: <b><?php echo $frm_remote_ip;?></b>
    <li>Remote site user: <b><?php echo $frm_remote_username;?></b>
    <li>Remote folder: <b><?php echo $frm_remote_folder;?></b> 
    <li>Remote connection: <b><?php echo $frm_remote_type;?></b> 
    </UL> 
    
   <?php 
   echo "<font color='#008000'>$ftp_msg</font><font color='#FF0000'>$ftp_error_msg</font>";
   if($theAction == 'logView'){
      echo "<UL>";
      echo "<li>Is it uploading? $process_is_running";
      echo "<li>Start time: $process_start_time";
      if($process_end_time){
        echo "<li>";
        if($process_is_running != 'NO'){
          echo "Previous Finished time: ";
        }else{
          echo "Finished time: ";
        }
        echo $process_end_time;
      }
      echo "</UL>";
      if($results_index_arr){
        echo "<br><b>Selected results</b>: total ". count($results_index_arr). "
            <hr size=1 noshade>";
        foreach($results_index_arr as $the_file){
          echo $the_file."\n<br>";
        }
        
      }
      echo "<br><b>Uploaded Files</b>: total ". count($uploaded_file_arr). "
            <hr size=1 noshade>";
      foreach($uploaded_file_arr as $the_file){
        echo $the_file."\n<br>";
      }
   }else if($theAction == 'ftpRemoteList'){
   ?>
    <table width=100%>
      <tbody bgcolor="#ffffff">
       <td colspan=4>
      <?php 
      ListFolder($remote_files_arr);
      ?>
      </td>
      </tbody>
    </table>
   <?php  
   }
   ?>
  </form>
</body>
</html>


