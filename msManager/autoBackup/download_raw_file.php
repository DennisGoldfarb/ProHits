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

set_time_limit (0) ;
ini_set("memory_limit","4000M");
define("MAX_DOWNLOAD_SZIE", 5000); //MB


include ( "../../config/conf.inc.php");
include ( "./shell_functions.inc.php");
include ( "../is_dir_file.inc.php");
define("STORAGE_TMP_ZIP_FOLDER", "../../TMP/");



$file_is_dir = false;
$username = '';
$filePath = '';
$SID = '';
$ID = '';
$tableName = '';
$query_str = '';
$taskID = '';
$searchType = '';
$download_dir = '';
$file_added_in_export_log = false;
$export_list_file = 'fileList_'. @date("Y-m-d").".txt";
$url_wiffscan = '';

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}

/*echo "<pre>"; 
print_r($request_arr);
echo "</pre>";*/

foreach($request_arr as $key => $value) {
  $$key=$value;
  if(strlen($query_str) > 0){
    $query_str .= "&";
  }
  $query_str .= "$key=$value";
} 

 
//check query string variables 
if(!$SID  or !$tableName or (!$ID and !$filePath)){
  echo "not enough information passed"; exit;
}
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$prohits_link  = mysqli_connect("$host", $user, $pswd, PROHITS_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
 

$msManager_link  = mysqli_connect("$host", $user, $pswd, MANAGER_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));

$theFile_arr['FileType'] = '';
if($SID != 'rawDataConverter' and !$filePath){
	$SQL = "select * from $tableName where ID='$ID'";
	$result = mysqli_query($msManager_link, $SQL);
	$theFile_arr = mysqli_fetch_assoc($result);
	if(!$theFile_arr){
	  echo "no file found in database";exit;
	}
}

if(!check_download_permission($SID)) {
  echo "please login prohits.";
  exit;
} 
  
if(!$searchType){
  if(!$filePath or !_is_file($filePath) ){
    $filePath = getFilePath($tableName, $ID);
  }
  if($theFile_arr['FileType'] == 'dir'){
    if(!_is_dir($filePath)){
      echo "The Folder does't exist '$filePath'";exit;
    }
  }else{
    if(!_is_file($filePath)){
      echo "The file doesn't exist '$filePath'";exit;
    }
  }
}

if($filePath and _is_dir($filePath)){ 
  $download_dir = 1;
  if(!_is_dir(STORAGE_TMP_ZIP_FOLDER)){
    if(!mkdir ( STORAGE_TMP_ZIP_FOLDER, 0700)){
      echo "Apache user cannot create tmp folder ". STORAGE_TMP_ZIP_FOLDER . ". Please contact Prohits admin.";exit;
    }
  }
  //get the folder name
  $user_ip = getenv('REMOTE_ADDR');
  $pos = strrpos($filePath, "/");
  $dir = substr($filePath, 0, $pos );
  $fileName = substr($filePath, $pos + 1);
  $folder_size = get_size($filePath);
   
  if($folder_size/(1024*1024) > MAX_DOWNLOAD_SZIE){
    echo "The folder '$filePath' size is too big to download (more than ".MAX_DOWNLOAD_SZIE." MB). You can open the folder to download raw file one by one.";
    exit;
  }
   
  //delete previous zip file the user created
  if(_is_dir(STORAGE_TMP_ZIP_FOLDER . $user_ip)){
    exec("rm -rf ". STORAGE_TMP_ZIP_FOLDER . $user_ip ."/*");
  }else{
    if(!mkdir ( STORAGE_TMP_ZIP_FOLDER. $user_ip, 0700)){
      echo "Apache user cannot create tmp folder ". STORAGE_TMP_ZIP_FOLDER . $user_ip . ". Please contact Prohits admin.";exit;
    }
  }
  $myshellcmd = "zip -0r ".STORAGE_TMP_ZIP_FOLDER."$user_ip/$fileName.zip '$filePath';";
  getoutput_header();
	$str_empty = str_repeat("&nbsp; ", 1000);

  echo "\n<span id=\"processing\"><center><font face=\"Arial\" color=\"#000080\"><b>Creating Zip File. Please be patient</font></b>.<br><br>
        <img src='./zip.gif'></center>$str_empty</span>\n";
   
  flush();
  $result = @exec($myshellcmd);
  if($result){
     $url = STORAGE_TMP_ZIP_FOLDER."$user_ip/$fileName.zip";
     $size = _filesize(STORAGE_TMP_ZIP_FOLDER."$user_ip/$fileName.zip");
     //echo $size;
     $max = (!is_64bit())? 2000: MAX_DOWNLOAD_SZIE;
     if($size/(1024*1024) > $max){
       echo "The size of this zipped file is more then ".$max."MB. You can open the folder to download raw file one by one.";
       echo "\n<script>processing.style.display='none'</script>\n";
       exit;
     }
  }else{
    $err_msg = "ProHits can not create a zip file now. Please try it later.";
    $url = $_SERVER['PHP_SELF'] . "?" . $query_str;
  }
  echo "\n<script>processing.style.display='none'</script>\n";
  getoutput_footer($url);
  exit;
}

//it is a file
if(isset($clicked)){ 
  header("Content-Type: application/octet-stream");  //download-to-disk dialog
  header("Content-Disposition: attachment; filename=\"".basename($filePath)."\"");
  header("Content-Transfer-Encoding: binary");
  if($searchType != 'MSPLIT'){
    header("Content-Length: "._filesize($filePath));
  }
  //readfile("$filePath");
  _output($filePath);
  exit();
}else{
  $rul = array();
  if(isset($addPackage) and $addPackage == 'yes'){
    $user_raw_export_dir = check_user_raw_export_folder($username);
    if($user_raw_export_dir){
      $export_file_log = $user_raw_export_dir. "/$export_list_file";
      $fd = fopen($export_file_log, 'a+');
      if(!$fd){
        echo "can not open the log file to write: $export_list_file"; exit;
      }
      if($searchType){
        fwrite($fd, "\r\n" . "$searchType\t$tableName\t$ID\t$taskID");
      }else{
        fwrite($fd, "\r\n" . "RAW\t$tableName\t$ID");
      }
      fclose($fd);
      $file_added_in_export_log = true;
    }
  }
  if($searchType){
    if(strpos($searchType, "TPP") === 0){
      list($tppXML, $searchEngline) = explode("_", $searchType);
      $SQL = "SELECT  pepXML, protXML FROM $tableName"."tppResults where WellID=".$ID." and TppTaskID='". $taskID."' and SearchEngine='".$searchEngline."'";
       
      $record= mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL));
      
      if($tppXML == 'TPPprot'){
        $mxl_file = $record['protXML'];
      }else{
        $mxl_file = $record['pepXML'];
      }
      if(!is_file($mxl_file)){
          echo "<font color=red>error</font>: file missing,$mxl_file";exit;
      }
      $url[] = $_SERVER['PHP_SELF'] . "?" . $query_str . "&clicked=yes&filePath=".$mxl_file;
    }else{
      $SQL = "SELECT  FileName, DataFiles FROM $tableName T, $tableName"."SearchResults S where S.WellID=T.ID and WellID=$ID and TaskID='". $taskID."' and SearchEngines='".$searchType."'";
      $record= mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL));
      
      $DataFile_arr = explode(";",$record['DataFiles']);
      if($searchType=='Mascot'){
        $mascot_IP = MASCOT_IP;
        if(defined('MASCOT_IP_OLD') and preg_match("/^\w/", $record['DataFiles'], $matches)){
          $mascot_IP = MASCOT_IP_OLD;
        }
        for($i = 1; $i <= count($DataFile_arr); $i++){ 
          $tmp_data_file = $DataFile_arr[$i-1];
		      if(!$tmp_data_file)continue;
          $dat_file_name = substr($record['FileName'], 0, strrpos($record['FileName'], '.'));
          $url[] = "http://".$mascot_IP.MASCOT_CGI_DIR."/export_dat_2.pl?do_export=1&export_format=MascotDAT&file=$tmp_data_file"; 
          //$url[] = "http://".$mascot_IP.MASCOT_CGI_DIR."/export_dat_2.pl?do_export=1&export_format=pepXML&file=$tmp_data_file"; 
          //$url[] = "http://".$mascot_IP.MASCOT_CGI_DIR."/ProhitsMascotParser.pl?theaction=download&file=".$tmp_data_file."&newName=".$dat_file_name.".dat";
        }
      }else{
        for($i = 1; $i <= count($DataFile_arr); $i++){ 
          $tmp_data_file = $DataFile_arr[$i-1];  
          if(!$tmp_data_file)continue;
          if(!is_file($tmp_data_file)){
           echo "<font color=red>error</font>: file missing, $tmp_data_file";exit;
          }
          $url[] = $_SERVER['PHP_SELF'] . "?" . $query_str . "&clicked=yes&filePath=".$tmp_data_file;
        }
      }
    }
  }else{
    $url[0] = $_SERVER['PHP_SELF'] . "?" . $query_str . "&clicked=yes";
    if(_is_file($filePath) and preg_match("/(^.+)\.(wiff|scan)$/i", $filePath, $matches)){
      $SQL = "select ID, FileName, FolderID from $tableName where ID='$ID'";
      $row = mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL));
      if(count($matches)==3){
        $ext = $matches[2];
        if($ext == 'wiff'){
          
          $new_file_path = $filePath.".scan";
          $new_file_name = $row['FileName'].".scan";
        }else if($ext == 'scan'){
          $url_wiffscan = $url[0];
          $new_file_path = $matches[1];
          $new_file_name = preg_replace("/\.scan$/i", '', $row['FileName']);
        }
        if(_is_file($new_file_path)){
          $SQL = "select ID, FileName from $tableName where FileName='".$new_file_name."' and FolderID='".$row['FolderID']."'";
          if($row = mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL))){
            if($ext == 'scan'){
              $url[0] = $_SERVER['PHP_SELF'] . "?SID=$SID&tableName=$tableName&ID=" . $row['ID'];
            }else{
              $url_wiffscan = $_SERVER['PHP_SELF'] . "?SID=$SID&tableName=$tableName&ID=" . $row['ID'];
            }
            if(isset($addPackage) and $addPackage == 'yes'){
              $fd = fopen($export_file_log, 'a+');
              if($fd){
                fwrite($fd, "\r\n" . "RAW\t$tableName\t".$row['ID']);
              }
              fclose($fd);
            }
          }
        }
      }
    }
    
  }
  $url_add_package = $_SERVER['PHP_SELF'] . "?" . $query_str . "&addPackage=yes";
  getoutput_header();
  getoutput_footer($url, $url_add_package, $url_wiffscan);
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

///////////////////////////////////
function getoutput_header(){
  global $download_dir;
  echo "
 <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
 <html>
  <body style=\"font-family: arial,sans-serif; font-size: 10pt; background-color: #ccc;\">
  ";
}
function getoutput_footer($url, $url_add_package='', $url_wiffscan=''){
  global  $download_dir;
  global $file_added_in_export_log;
  echo "
  <div style=\"padding: 10px 20px; display:block; margin: 0px auto; width:400px; height:230px; background-color:#FFF; border:#708090 1px solid;\">";
   $is_wiff = '';
   if($url_wiffscan){
      $is_wiff = 'WIFF';
      echo "<p><a href='".$url_wiffscan."&clicked=yes'><img style=\"vertical-align: middle; border: none\" src=../images/icon_download.png border=0 valign=top></a> Click the icon to download SCAN file to local computer";
   }
   foreach($url as $the_url){
    echo "<p><a href='$the_url'><img style=\"vertical-align: middle; border: none\" src=../images/icon_download.png border=0 valign=top></a> Click the icon to download the $is_wiff file to local computer";
   }
  if(!$download_dir){
    if($file_added_in_export_log){
      echo "
      <p><font color=\"#008000\">The file has been added in exporting list</font>.";
    }else{
      echo "
    <p><a href='$url_add_package'>
    <img style=\"vertical-align: middle; border: none\" src=../images/icon_add.png border=0></a>
     Click the icon to add the file in exporting list <br>(using ftp or sfp to transfer a large file or group of files).";
    }
  }
  echo "
    <hr size=1 noshade>
    <form>
      <center><input type=button value='Close Window' onclick='javascript: window.close();'>
    </form> 
  </div>
  
 
</body>
</html>"; 
}

function get_size($path){
 if(!_is_dir($path)) return _filesize($path);
 if ($handle = opendir($path)) {
     $size = 0;
     while (false !== ($file = readdir($handle))) {
         if($file!='.' && $file!='..'){
             //$size += _filesize($path.'/'.$file);
             $size += get_size($path.'/'.$file);
              
         }
     }
     closedir($handle);
     return $size;
 }
}
function _output($filePath){
  $filesize = _filesize($filePath);
  if($filesize > 1800000000){   
      _readfileChunked($filePath);
  }else{
      readfile("$filePath");
  }
}
function _readfileChunked($filename, $retbytes=true) {
    $chunksize = 32*(1024*1024);
    $srcStream = fopen($filename, 'rb');
    $dstStream = fopen('php://output', 'wb');

    $offset = 0;
    while(!feof($srcStream)) {
        $offset += stream_copy_to_stream($srcStream, $dstStream, $chunksize, $offset);
    }
    fclose($dstStream);
    fclose($srcStream);
    /*
    $buffer = '';
    $cnt =0;
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
        return false;
    }
    while (!feof($handle)) {
        $buffer = fread($handle, $chunksize);
        echo $buffer;
        ob_flush();
        flush();
        if ($retbytes) {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
    */
}


?>