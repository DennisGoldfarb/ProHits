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
error_reporting(E_ERROR | E_WARNING | E_PARSE);

set_time_limit(0);
ini_set("memory_limit","-1");
ini_set("default_socket_timeout", "10000");
$logfileDir = '../../logs/';
$logfile = '../../logs/raw_back.log';


$file_size_too_large_flag = 0;
$folderMess = '';
$theaction = '';
$frm_Project_ID = '';
$projectID = '';
$msg = '';
$whichFolder = '';
$tableName = '';
$frm_new_folder_name = '';
$frm_old_folder_ID = '';
$frm_old_file_ID = '';

$UserID = '';
$Username = '';
$SID = '';
$submitFolder = '';
$folderName = '';
$parFolderName = '';
$submitFolder = '';
$parFolderName = '';
$attachedFile = '';
$file_str = '';
$folderLevel = '';
$save_to_dir_path = '';
$folderID = '';
$frm_fileType ='';

session_start();

$POST_MAX_SIZE = ini_get('post_max_size');
$UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');
if(!$UPLOAD_MAX_FILESIZE) $UPLOAD_MAX_FILESIZE = $POST_MAX_SIZE;
$FILE_UPLOADS = ini_get('file_uploads');

if(!$FILE_UPLOADS){
  $message = "The Apache setting '<b>file_uploads</b>' is off. Please contact Prohits administrator to change the setting.";
  Upload_Notice($message);
}

$stepColor = "#637eef";
$tmpFilesDirDir = "../../TMP";

if(isset($_SERVER['OS'])){
	$slash = "\\";
}else{
	$slash = "/";
}

$configFile = "..".$slash."..".$slash."config".$slash."conf.inc.php";
 

require($configFile);
include("./shell_functions.inc.php");
include ( "../is_dir_file.inc.php");

if(_is_dir(!$logfileDir)){
  if(!mkdir($logfileDir)){ 
    echo "Cannot create logs dir!";
    exit;
  }
}
$logfile = "..".$slash."..".$slash."logs".$slash."raw_upload.log";

//echo "--$FILE_UPLOADS--";exit;
if(isset($_SERVER['CONTENT_LENGTH'])){
  //$POST_MAX_SIZE = ini_get('post_max_size');
  //$UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');
  //$FILE_UPLOADS = ini_get('file_uploads');
  $mul_P = substr($POST_MAX_SIZE, -1);
  $mul_P= ($mul_P == 'M' ? 1048576 : ($mul_P == 'K' ? 1024 : ($mul_P == 'G' ? 1073741824 : 1)));
  $mul_F = substr($UPLOAD_MAX_FILESIZE, -1);
  $mul_F= ($mul_F == 'M' ? 1048576 : ($mul_F == 'K' ? 1024 : ($mul_F == 'G' ? 1073741824 : 1)));
  if (($_SERVER['CONTENT_LENGTH'] > $mul_P*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) or
     ($_SERVER['CONTENT_LENGTH'] > $mul_F*(int)$UPLOAD_MAX_FILESIZE && $UPLOAD_MAX_FILESIZE))
  {
    $msg = "The file you uploaded has exceeded the server limit post_max_size: 
       $POST_MAX_SIZE or upload_max_filesize $UPLOAD_MAX_FILESIZE. 
       Please contact Prohits administrator to change the setting.";
    $_SESSION['tmp_request_arr']['attachedFile'] = 'Y'; 
    $_SESSION['tmp_request_arr']['theaction'] = 'add_attachment'; 
    $request_arr = $_SESSION['tmp_request_arr'];
    $file_size_too_large_flag = 1;
  }else{
    unset($_SESSION['tmp_request_arr']);
  }
}
if(!$file_size_too_large_flag){
  if($_SERVER['REQUEST_METHOD'] == "POST"){
    $request_arr = $_POST;
  }else{
    $request_arr = $_GET;
  }
}  
foreach ($request_arr as $key => $value) {
  $$key=$value;
}



$PHP_SELF = $_SERVER['PHP_SELF'];
//--------------------------------------------------------------------------------------
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;

$managerDBname = MANAGER_DB;
//$managerDBname = "Backup_test";
$msManager_link  = mysqli_connect($host, $user, $pswd,$managerDBname) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));

$prohits_link  = mysqli_connect($host, $user, $pswd, PROHITS_DB) or die("Unable to connect to mysql..." . mysqli_error($prohits_link));

$HitDB_links = array();
foreach($HITS_DB as $key => $DBname){
  $HitDB_links[$key] = mysqli_connect($host, $user, $pswd, $DBname) or die("Unable to connect to mysql..." . mysqli_error($HitDB_links[$key]));
}

$managerDB_base_tableNames_Arr = get_managerDB_base_tableNames();
if(!$managerDB_base_tableNames_Arr){
  exit;
}


if(isset($parFolderID) and isset($parFolderName)){
  $frm_Project_ID = $parProjectID;
  $frm_old_folder_ID = $parFolderID .",". $parFolderName;
  $whichFolder = "exist";
  
}

$projectID_DBname_Arr = get_projectDB();
$projectID_Name_Arr = get_project_id_name_pair();

//---permission check-----------------------------
//echo $USER->ID;exit;
if($SID){  
  $SQL = "SELECT UserID FROM Session WHERE SID='$SID'";
  $result = mysqli_query($prohits_link, $SQL);
  $SIDArr = mysqli_fetch_assoc($result);
  if($SIDArr){
    $SQL = "SELECT Username FROM User WHERE ID='".$UserID."'";
    $result = mysqli_query($prohits_link, $SQL);
    if($UserArr = mysqli_fetch_assoc($result)){
        $Username = $UserArr['Username'];
    }else{
      fatalError("couldn't find the user $UserID", $logfile);
    }
    if($UserType == 'MSTech' or $UserType == 'Admin'){
      $SQL = "SELECT P.ID, P.Name FROM Projects P order by P.Name"; 
    }else{
      $SQL = "SELECT P.ID, P.Name FROM Projects P, ProPermission M where P.ID=M.ProjectID and M.UserID=$UserID and M.Insert=1 order by P.Name"; 
    }        
    //echo $SQL;        
    $result = mysqli_query($prohits_link, $SQL);
    $num_rows = mysqli_num_rows($result);
    if($num_rows){
      $projectIDarr = array();
      while($row = mysqli_fetch_assoc($result)){
        $projectIDarr[$row['ID']] = $row['Name'];  
      }
    }else{
      $message = "You don't have permission to upload files, since you don't have any project inserting permission.";
      fatalError($message, $logfile);
    } 
  }else{
    $message = "Log in again!";
    fatalError($message, 0, $logfile);
  }
}else{
  $message = "Log in first!";
  fatalError($message, 0, $logfile);
}

//---tmp location for uploaded files---------------------------
$tmpFilesDir = $tmpFilesDirDir . "/" . $Username;
if(preg_match('/\/$/', STORAGE_FOLDER)){
  //---destination location for uploaded files-------------------
  $dirPrefix = STORAGE_FOLDER . $tableName;
}else{
  $dirPrefix = STORAGE_FOLDER . $slash . $tableName;
}

if(!_mkdir_path($tmpFilesDir)){
  $message = "Cannot make directory $tmpFilesDir. pleaea check the folder permission.";
  fatalError($message, 0, $logfile);
}
if(!_mkdir_path($dirPrefix)){
    $message = "Cannot make directory $dirPrefix. pleaea check the folder permission.";
    fatalError($message, 0, $logfile);
}



if($frm_old_folder_ID){
  $tmpPlateArr = explode(",", $frm_old_folder_ID);
  $folderID = $tmpPlateArr[0];
  $folderName = $tmpPlateArr[1];
  $save_to_dir_path = getFilePath($tableName, $folderID);
}else{
  $save_to_dir_path = $dirPrefix;
}


if($theaction == "add_attachment"){
    
  if($attachedFile != 'Y'){
    unset($_SESSION['tmpFilesProptyArr']);
    empty_dir($tmpFilesDir);
  }
  if($file_size_too_large_flag){
    $msg = "The file you uploaded has exceeded the server limit upload_max_filesize $UPLOAD_MAX_FILESIZE. 
       Please contact Prohits administrator to change the setting.";
    writeLog($msg, $logfile); 
  }else{
    $uploaded_file_name = $_FILES['frm_File']['name'];
    $uploaded_file_type = $_FILES['frm_File']['type'];
    $uploaded_file_size = $_FILES['frm_File']['size'];
	$uploaded_file_size = $uploaded_file_size >= 0 ? $uploaded_file_size : 4*1024*1024*1024 + $uploaded_file_size;
    $message = "uploaded file size: $uploaded_file_size";
    writeLog($message, $logfile); 
    $uploaded_file_name = preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
    $is_folder_exist = 0;
    if($whichFolder != "exist"){
      $save_to_dir_path = $dirPrefix.$slash.$frm_new_folder_name;
      if(_is_dir($save_to_dir_path)){
        $is_folder_exist = 1;
      } 
    }
    if($is_folder_exist){
      $msg = "Directory '$save_to_dir_path' exists already. Please type another name.";
    }else{
      $fileKey = $uploaded_file_name;
      $save_to_file_path = $save_to_dir_path."/".$fileKey;
      if(!_is_file($save_to_file_path)){
        if(!_is_dir($tmpFilesDir) && !_mkdir_path($tmpFilesDir)){
          $message = "Cannot make directory $tmpFilesDir";
          writeLog($message, $logfile);
        }else{
          $tmpFullDir = $tmpFilesDir;
          if(!_is_dir($tmpFullDir) && !_mkdir_path($tmpFullDir)){
            $message = "Cannot make directory $tmpFullDir";
            writeLog($message, $logfile);
          }else{
            $tmpFileFullName = $tmpFullDir."/".$uploaded_file_name;
             
            if(move_uploaded_file($_FILES['frm_File']['tmp_name'], $tmpFileFullName)){
              $fileProptys = array();
              $fileProptys['FileName'] = $uploaded_file_name;
              if(preg_match('/\.(\w+(\.gz)?)$/', $uploaded_file_name, $matches)){
                $fileProptys['FileType'] = $matches[1];
              }else{
                $fileProptys['FileType'] = '';
              }
              $fileProptys['FolderID'] = $folderID;
              $fileProptys['ProjectID'] = $frm_Project_ID;
              $fileProptys['fileSize'] = $uploaded_file_size;
              if(isset($_SESSION['tmpFilesProptyArr'][$fileKey])){
                $msg = "File '$tmpFileFullName' is exist in list. Please change the name '$uploaded_file_name'.";
              }else{
                $attachedFile = 'Y';
                $_SESSION['tmpFilesProptyArr'][$fileKey] = $fileProptys;
              }  
            }else{
              $message = "Cannot move file $uploaded_file_name";
              writeLog($message, $logfile);
            }
          }  
        }  
        
      }else{
        $msg = "File error: '$save_to_file_path' exists already. Please change the name '$uploaded_file_name'.";
        writeLog($msg, $logfile);
      }
    }  
  }
}else if($theaction == "delete_marked_files"){
  $tmpFilesProptyArr = &$_SESSION['tmpFilesProptyArr'];
  $j=0;
  foreach($tmpFilesProptyArr as $key => $value){
    if($deletedFiles == $key){
      array_splice($tmpFilesProptyArr, $j, 1);
      unlink($tmpFilesDir.$slash.$deletedFiles);
      break;
    }
    $j++;
  }
}else if($theaction == "upload_files"){
  $tmpStamp = @date("Y-m-d");
  $tmpFilesProptyArr = &$_SESSION['tmpFilesProptyArr'];
  $newFolder_name_iD_Arr = array();
  if($frm_new_folder_name){
    $save_to_dir_path = $save_to_dir_path."/".$frm_new_folder_name;
  }
  foreach($tmpFilesProptyArr as $key => $value){
    $basefileName = $key;
    $midDir = basename($save_to_dir_path);
    if(isset($newFolder_name_iD_Arr[$midDir])){
      $value['FolderID'] = $newFolder_name_iD_Arr[$midDir];
    }
    if(!_is_dir($save_to_dir_path)){
      if(_mkdir_path($save_to_dir_path)){
        if(!$value['FolderID']){
          $SQL = "INSERT INTO $tableName SET 
                  `FileName`='$midDir',
                  `FileType`='dir',
                  `FolderID`=0,
                  `Date`='$tmpStamp',
                  `ProjectID`='".$value['ProjectID']."'";
          //echo $SQL;       
          mysqli_query($msManager_link, $SQL);
          $insertedFolderID = mysqli_insert_id($msManager_link);
          $newFolder_name_iD_Arr[$midDir] = $insertedFolderID;
          $value['FolderID'] = $insertedFolderID;
        }   
      }else{
        $message = "Cannot make directory $desFullDir";
        writeLog($message, $logfile);
        continue;
      } 
    }
    
    $tmpFullFileName = $tmpFilesDir . "/" . $key;
    
    $desFullFileName = $save_to_dir_path . "/" . $key;
    if(rename($tmpFullFileName, $desFullFileName)){
      $SQL = "INSERT INTO $tableName SET              
              `FileName`='".$value['FileName']."',
              `FileType`='".$value['FileType']."',
              `FolderID`='".$value['FolderID']."',
              `Size`='".$value['fileSize']."',
              `Date`='$tmpStamp',
              `ProjectID`='".$value['ProjectID']."'";
      mysqli_query($msManager_link, $SQL);
    }
  }
  unset($_SESSION["tmpFilesProptyArr"]);
  $_SESSION["tmpFilesProptyArr"] = array();
  empty_dir($tmpFilesDir);
  echo "<center>";
  echo "<font face='Arial' size='2' color='red'><B>File(s):</B></font><br><br>";
  foreach($tmpFilesProptyArr as $key => $value){
    echo "<font face='Arial' size='2' color='#008000'>".$key."</font><br>";
  }
  echo "<br><font face='Arial' size='2' color='red'><B>have been uploaded. You may need to manually link them to Prohits Analyst samples.</B></font><br><br>";
  echo "<input type=button value=' Close ' onclick='javascript: window.close();' class=black_but>";
  echo "<center>";
  exit;
}else if($theaction == "cleanup"){
  unset($_SESSION["tmpFilesProptyArr"]);
  $_SESSION["tmpFilesProptyArr"] = array();
  empty_dir($tmpFilesDir);
  echo "<script language='javascript'>window.close();</script>";
}

?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
  <title>Upload Files</title>
</head>
<script language="javascript">
var folerNameArr = new Array();
function project_selected(){
  theForm = document.raw_file_upload;  
  theForm.theaction.value = "project_selected";
  if(typeof theForm.frm_old_folder_ID != "undefined"){
    theForm.frm_old_folder_ID.value = "";
  }
  if(typeof theForm.frm_old_file_ID != "undefined"){ 
    theForm.frm_old_file_ID.value = "";
  }
  if(theForm.frm_fileType.value == ""){
    alert("Select a file type(step1) please.");
    theForm.frm_Project_ID.value = "";
    return false;
  }  
  theForm.folderMess.value = '';
  theForm.folderID.value = '';
  theForm.folderName.value = '';  
  theForm.attachedFile.value = '';
  theForm.submit(); 
} 
function newOrExisting(){
  theForm = document.raw_file_upload;
  theForm.attachedFile.value = '';
  theForm.submit();
}
function change_folder(){
  theForm = document.raw_file_upload;
  if(theForm.frm_fileType.value == ""){
    alert("Select a file type(step1) please.");
    return false;
  }  
  if(theForm.frm_Project_ID.value == ""){
    alert("Select a project first please.");
    return false;
  }
  theForm.theaction.value = "plate_selected";
  if(typeof theForm.frm_old_file_ID != "undefined"){
    theForm.frm_old_file_ID.value = "";
  } 
  theForm.attachedFile.value = '';
  theForm.submit();   
}
function add_attachment(){
  theForm = document.raw_file_upload;
  if(theForm.frm_fileType.value == ""){
    alert("Select a file type(step1) please.");
    return false;
  }else{
    
    if(theForm.frm_Project_ID.type !='hidden' && theForm.frm_Project_ID.value == ""){
      alert("Please select a project.");
      return false;
    }
    if(typeof theForm.frm_old_folder_ID != "undefined"){
      if(theForm.frm_old_folder_ID.value == ""){
        alert("Please select a folder.");
        return false;
      }
    }else if(typeof theForm.frm_new_folder_name != "undefined"){ 
      if(isEmptyStr(theForm.frm_new_folder_name.value)){
        alert("Please enter a folder name.");
        return false;
      }else{
        for(var i=0; i<folerNameArr.length; i++){
          if(folerNameArr[i] == theForm.frm_new_folder_name.value){
            alert("The new folder name you entered had been existed! Please type another name.");
            return false;
          }
        }
      }
    }
    theForm.folderID.value = "";
  }
   
  var upload_full_name = theForm.frm_File.value;
  if(isEmptyStr(upload_full_name)){
    alert("Select a file please.")
    return false;
  }
  var tmpArr = upload_full_name.split((/[\/\\]/g));
  var file_name = tmpArr[tmpArr.length-1];

  if(exist_file(file_name)){
    alert("The file name exists in the folder! Please select other file or rename the file then upload it");
    return false;
  }
  var tmpArr2 = file_name.split('.');
  var fileType = tmpArr2[tmpArr2.length-1];
  if(fileType == 'gz' && tmpArr2.length > 2){
    fileType = tmpArr2[tmpArr2.length-2]+ "."+ tmpArr2[tmpArr2.length-1]; 
  }
  fileType = fileType.toUpperCase();
  var selectedFileType = theForm.frm_fileType.value.toUpperCase();
  if(fileType != selectedFileType){
    if(!confirm("The file tpye you selected is different with the file you attached. Continue?")){
       return false;     
    }
  }
  theForm.theaction.value = "add_attachment";
  theForm.submit();
}
function isEmptyStr(str){
  var str = this != window? this : str;
  var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
  if(temstr == 0 || temstr == ''){
     return true;
  } else {
    return false;
  }
}
function delete_marked_files(file_name){
  theForm = document.raw_file_upload;
  theForm.deletedFiles.value = file_name;
  theForm.theaction.value = "delete_marked_files";
  theForm.submit();
}
function upload_files(){
  theForm = document.raw_file_upload;
  if(theForm.frm_fileType.value == "-1"){
    alert("Select a file type(step1) please.");
    return false;
  }  
  theForm.theaction.value = "upload_files";
  theForm.submit();
}
function cleanup(){
  theForm = document.raw_file_upload;
  theForm.theaction.value = "cleanup";
  theForm.submit();
}
</script>
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">
<?php 

$folderArr = array();
?>
<form name="raw_file_upload" method=post action=<?php echo $PHP_SELF;?> enctype="multipart/form-data">
<input type=hidden name="theaction" value="">
<input type=hidden name="tableName" value="<?php echo $tableName?>">
<input type=hidden name="SID" value="<?php echo $SID?>">
<input type=hidden name="UserID" value="<?php echo $UserID?>">
<input type=hidden name="UserType" value="<?php echo $UserType?>">
<input type=hidden name="folderMess" value="<?php echo $folderMess?>">
<input type=hidden name="folderID" value="<?php echo $folderID?>">
<input type=hidden name="folderName" value="<?php echo $folderName?>">
<input type=hidden name="deletedFiles" value="">
<input type=hidden name="submitFolder" value="<?php echo $submitFolder?>">
<input type=hidden name="parFolderName" value="<?php echo $parFolderName?>">
<input type=hidden name="attachedFile" value="<?php echo $attachedFile;?>">
<input type=hidden name="folderLevel" value="<?php echo $folderLevel;?>">
 
  <center>
  <table border=0 width=99% cellspacing="1">
    <tr>
      <td align=center colspan=2>
        <font face="Arial" size="+2" color="#660000"><b><?php echo $tableName?> Upload Files</b></font><br>
      <hr width="100%" size="1" noshade>
      </td>
    </tr>
    <tr>
      <td width=30% valign=top bgcolor="#8998DB" align=right>
        <font face="Arial" size="2" color="#ffffff"><b>Step 1.&nbsp;&nbsp;Select file type</b></font>
      </td>
      <td>
        <select name="frm_fileType">
				 	  <?php file_type_option($frm_fileType);?>
				</select>
      </td>
    </tr>
    <tr>
<?php if($folderLevel == '1'){?>    
  
      <td width=30% valign=top bgcolor="#8998DB">
        <font face="Arial" size="2" color="#ffffff"><b>Step 2.&nbsp;&nbsp;Select Project</b></font>
      </td>
      <td>
        <select name="frm_Project_ID" onChange="project_selected();">
                           
  		 	  <?php project_option($frm_Project_ID, $projectIDarr);?>
  		  </select>
      </td>  
<?php }else{?>

      <td width=30% valign=top bgcolor="#8998DB">
        <font face="Arial" size="2" color="#ffffff"><b>Project Name</b></font>
      </td>
      <td><input type=hidden name="frm_Project_ID" value="<?php echo $frm_Project_ID;?>">
<?php 
    if(isset($projectID_Name_Arr[$frm_Project_ID])){
      echo $projectID_Name_Arr[$frm_Project_ID];
    }else{
      echo '';
    }  
  }?>       
      </td>
    </tr>
  <?php 
  if($frm_Project_ID || $folderLevel == '2'){
      $plateIdNameArr = folder_option($frm_Project_ID, $tableName);
      /*echo "<pre>";
      print_r($plateIdNameArr);
      echo "</pre>";*/
  ?>
    <tr>
  <?php if($folderLevel == '1'){?>    
      <td width=30% valign=top bgcolor="#8998DB">
        <font face="Arial" size="2" color="#ffffff"><b>Step 3.&nbsp;&nbsp;Select folder or create a new folder.</b></font>
      </td>      
      <td>
      <?php 
        if(!$plateIdNameArr){
          $whichFolder = "new";
      ?>      
        No folder for the project.<br>
        <input type='text' name='frm_new_folder_name' value='<?php echo $frm_new_folder_name;?>' size=25> Type new folder name
      <?php 
          $frm_old_folder_ID = '';
        }else{?>
        <font face="Arial" size="2" >Old folder</font>
         <input type="radio" name="whichFolder" value="exist" <?php echo ($whichFolder=="exist")?" checked":"";?> onclick="newOrExisting();" >         
         &nbsp;&nbsp;&nbsp;<font face="Arial" size="2" >New folder</font> 
         <input type="radio" name="whichFolder" value="new" <?php echo ($whichFolder=="new")?" checked":"";?> onclick="newOrExisting();" ><br>
    
          <?php 
          if($whichFolder == "new"){
          ?>      
          <input type="text" name="frm_new_folder_name" value="<?php echo $frm_new_folder_name;?>" size=25> Type new folder name 
          <?php   
            $frm_old_folder_ID = '';
          }else if($whichFolder == "exist"){
          ?>
          <select name="frm_old_folder_ID" onChange="change_folder();">
            <option  value=''>-----select a folder-----<br>
            <?php foreach($plateIdNameArr as $key => $value){?>
                <option  value="<?php echo $key.",".$value?>" <?php echo  (($key.",".$value)==$frm_old_folder_ID)?" selected":"";?>><?php echo $value?><br>
            <?php }?>             
    		  </select>
        <?php }?>
      <?php }?>  
      </td>
  <?php }else{?>
      <td width=30% valign=top bgcolor="#8998DB">
        <font face="Arial" size="2" color="#ffffff"><b>Folder Name</b></font>
      </td>      
      <td>
      <input type=hidden name="frm_old_folder_ID" value="<?php echo $frm_old_folder_ID;?>">
      <input type=hidden name="whichFolder" value="exist">
  <?php 
  //echo "\$frm_old_folder_ID=$frm_old_folder_ID";
      $folderID = explode(',',$frm_old_folder_ID);
      if($folderID[1]){
        echo $folderID[1];
      }else{
        echo '';
      }
      echo "<td>";
    }
    echo "</tr>";
    $tmp_fold_arr = explode(',',$frm_old_folder_ID);
    $fileNameArr = file_option($tmp_fold_arr[0]);
    if($fileNameArr){
  ?>
    <tr>
      <td width=30% valign=top bgcolor="#8998DB">
        <font face="Arial" size="2" color="#ffffff">Files belong to selected folder</font>
      </td>
      <td>
        <select name="frm_old_file_ID" size=10 disabled>              
          <?php foreach($fileNameArr as $key => $value){?>             
              <option  value='<?php echo $key.",".$value?>' <?php echo  ($key.",".$value == $frm_old_file_ID)?" selected":"";?>><?php echo $value?><br>
          <?php }?>
  		  </select>
      </td>
    </tr>
  <?php }
  //echo "\$frm_Project_ID=$frm_Project_ID<br>";
  
  ?>
    <script language='javascript'> 
    function exist_file(file_name){
      <?php if($file_str){?>
      return false;
      <?php }?>
      fileArr = new Array(<?php echo $file_str?>);
      var i = fileArr.length;
        if (i > 0) {
      	do {
      		if (fileArr[i] === file_name) {
      		   return true;
      		}
      	} while (i--);
      }
      return false;
    }
    </script>
    <tr>
  	  <td colspan=2 align='left' >
      <?php 
      $disabled = "disabled";
      if($frm_Project_ID && ($whichFolder=="new" or $frm_old_folder_ID) || $folderLevel == '2' && $frm_old_folder_ID){
        $disabled = '';
      }
      ?>
      </td>
		</tr>
		<tr>
			<td width=30% valign=top bgcolor="#8998DB">
        <font face="Arial" size="2" color="#ffffff"><b>Step <?php echo ($folderLevel == '1')?'4':'2'?>: Add files</b></font>
      </td>
      <td>
        <font face='Arial' size='2'><b>Upload max file size:</b></font>&nbsp;
        <font face='Arial' size='2' color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;
        <font face='Arial' size='2'><b>Post max size:</b></font>&nbsp;
        <font face='Arial' size='2' color='red'><?php echo $POST_MAX_SIZE?>
      </td>
    </tr>
    <tr><td colspan=4><font face='Arial' size='2' color='red'><?php echo $msg?></font</td></tr>
    <tr>
  	  <td colspan=2 align='center'>
				<INPUT TYPE="FILE" NAME="frm_File" SIZE=50 <?php echo $disabled?>> 
        <INPUT TYPE=submit VALUE='Add File to List' onclick='return add_attachment()' <?php echo $disabled?>>
			</td>
		</tr>	
    <tr>	
          <td align='center' colspan=2><br>
        	  <table border=0 cellpadding=0 cellspacing=1 width=98% bgcolor="#ffffff">          						
        		  <tr bgcolor='#8998DB'>
  				   		<td align='center' ><nobr><Font size=2 face="helvetica,arial,futura" color='#ffffff'><B>File Name</B></font></td>
                <td align='center' ><nobr><Font size=2 face="helvetica,arial,futura" color='#ffffff'><B>File Size(KB)</B></font></td>
                <td align='center' width=15%><nobr><Font face="helvetica,arial,futura" size=2 color='#ffffff'><B>Option</B></font></td>
  				   	</tr>  
              <?php 
                $tmpFilesProptyArr = &$_SESSION['tmpFilesProptyArr'];
                if(is_array($tmpFilesProptyArr))
                foreach($tmpFilesProptyArr as $key => $value){
              ?> 
              <tr bgcolor="#ececff">                         
              	<td align='left' width="70%"><font face='Arial' size='2' color='#008000'>
                <?php echo $key;?></font>
              	</td>
                <td align='left' width="70%"><font face='Arial' size='2' color='#008000'>
                <?php echo  number_format(ceil($value['fileSize']/1024));?></font>
              	</td>
                <td align=center width="10%">
              		<a href='javascript:delete_marked_files("<?php echo $key;?>");'><font face='Arial' size='2' color='red'>remove</font></a>
              	</td>
              </tr>  
          <?php  
            }
          ?>
  					</table>&nbsp;
          </td>          			
      </tr>
 <?php }?>       	
      <tr>
        <td colspan="3" align="center" height=25>
  <?php if(($frm_Project_ID || $folderLevel == '2') && $tmpFilesProptyArr){?>        
        <input type='button' name='frm_submit' value=' Upload File(s) ' onClick="upload_files()">
  <?php }?> 
        <input type="button" value=" Cancel " onclick="javascript: cleanup();"> 
        </td>
      </tr>
    </table> 
    </center>       
  </form>
</body>
<?php 
if($whichFolder == "new"){
  $tmpFolderNameArr = folder_option($frm_Project_ID, $tableName);
  //print_r($tmpFolderNameArr);exit;
  echo "<script language='javascript'>\n";
  if($tmpFolderNameArr){
    $i=0;
    foreach($tmpFolderNameArr as $value){
      echo "folerNameArr[$i]='$value';\n";
      $i++;
    }
  }  
  echo "</script>\n";
}
$_SESSION['tmp_request_arr'] = $request_arr;


?>
</html>
<?php 
function get_managerDB_base_tableNames(){  //--return a array contain all base table names in DB backup.
  global $msManager_link, $managerDBname;
  $tableNameArr = array();
  $sql = "SHOW TABLES FROM $managerDBname";
  $result = mysqli_query($msManager_link, $sql);
  if($result){
    while($row = mysqli_fetch_row($result)){
      if(!strstr($row[0], 'SearchResults') && !strstr($row[0], 'SearchTasks') && !strstr($row[0], 'Plate_Conf')){
        array_push($tableNameArr, $row[0]);
      }  
    }
    mysqli_free_result($result);
  }
  if($tableNameArr){
    return $tableNameArr;
  }else{
    return 0;
  }  
}
function get_project_id_name_pair(){
  global $HitDB_links;
  $SQL = "SELECT `ID` , `Name` FROM `Projects` ORDER BY `ID`";
  $result = mysqli_query($HitDB_links['prohits'], $SQL);
  while($row = mysqli_fetch_assoc($result)){
    $idNamePairArr[$row['ID']] = $row['Name'];
  }
  return $idNamePairArr;
} 

function file_type_option($frm_fileType){
  global $RAW_FILES;
  $fileTypeArr = explode(",", $RAW_FILES);
  ?>
    <option  value=''>-----select a file type---<br>
  <?php 
  foreach($fileTypeArr as $value){
    $fileType = trim($value);
  ?>
    <option  value='<?php echo $fileType?>' <?php echo  ($frm_fileType==$fileType)?" selected":"";?>><?php echo $fileType?>&nbsp;&nbsp;<br>
  <?php   
  }  
}
function project_option($frm_Project_ID, &$projectIDarr){
  ?>
  <option  value='' >-----select a project-----<br>  
  <?php 
  foreach($projectIDarr as $key => $value){
  ?>
    <option  value='<?php echo $key?>' <?php echo  ($frm_Project_ID==$key)?" selected":"";?>><?php echo $value?><br>
  <?php   
  }         
}
function folder_option($projectID=0, $tableName=''){
  global $msManager_link;
  $folderIdNameArr = array();
  if($projectID && $tableName){
    $SQL = "SELECT 
             ID, 
             FileName
             FROM $tableName 
             WHERE ProjectID=$projectID
             AND FolderID=0
             ORDER BY ID DESC";
    //echo $SQL;exit;
    $result = mysqli_query($msManager_link, $SQL);
    while($row = mysqli_fetch_assoc($result)){
      $folderIdNameArr[$row['ID']] = $row['FileName'];
    }
  }
  if($folderIdNameArr){
    return $folderIdNameArr;
  }else{
    return 0;
  }           
}
function file_option($folderID=0){
  global $msManager_link, $tableName, $file_str;
  $fileNameArr = array();
  if($folderID){
    $SQL = "SELECT 
             ID, 
             FileName
             FROM $tableName 
             WHERE FolderID='$folderID' AND FileType != 'dir'
             ORDER BY FileName";
    //echo $SQL;exit;          
    $result = mysqli_query($msManager_link, $SQL);
    while($row = mysqli_fetch_assoc($result)){
      $fileNameArr[$row['ID']] = $row['FileName'];
      $file_str .= ($file_str)?",":"";
      $file_str .= '"'.$row['FileName'].'"';
    }
  }
  //if($fileNameArr){
    return $fileNameArr;
  //}else{
    //return 0;
  //}
}
function empty_dir($path, $level=0){
  if(!_is_dir($path)){
    return false;
  }
  if(substr($path, -1, 1) != "/"){
    $path .= "/";
  }
  foreach (glob($path . "*") as $file){
    if(_is_file($file) === TRUE){ 
      unlink($file); 
    }else if(_is_dir($file) === TRUE){ 
      $new_level = $level + 1;
      empty_dir($file, $new_level);
    }
  }
  if(_is_dir($path) === TRUE and $level) {
    echo "=$path=<br>";
    rmdir($path); 
  }
}

function Upload_Notice($message){
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Upload Notice:</title>
</head>
<body>
<center>
  <table border=0 width=99% cellspacing="1">
    <tr>
      <td align=center>
        <font face="Arial" size="+2" color="#660000"><b>Upload Notice</b></font><br>
      <hr width="100%" size="1" noshade>
      </td>
    </tr>
    <tr>
      <td width=30% valign=top bgcolor=white>
        <font face="Arial" size="2" color="red"><?php echo $message?></font>
      </td>
    </tr>
  </table> 
</center>   
</body>
</html>
<?php 
  exit;
}
?>