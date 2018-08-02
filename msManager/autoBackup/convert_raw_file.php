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

error_reporting(E_ALL);
set_time_limit(0);
ini_set("memory_limit","-1");
//ini_set("default_socket_timeout", "3600");
chdir(dirname(__FILE__));

$frm_replace_existing = 0; //0-don't replace, 1-replace previouse owner's, 2-create new.

include( "../../config/conf.inc.php");
include('./shell_functions.inc.php');
include ( "../is_dir_file.inc.php");
include ('../../common/mysqlDB_class.php');
@require_once "../../common/HTTP/Request_Prohits.php";


$logfile = '../../logs/raw_back.log';
$SID = '';
$frm_ID_str = '';
$tableName = '';
$query_str = '';
$frm_format = '';
$frm_PROTEOWIZARD_par_str = '--64 --mz64 --inten64 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /doubleprecision';
$frm_theURL = '';
$User_ID = '';
$frm_NOmgf = '';
$no_addtional_mgf = 0;

$php_command_location = PHP_PATH;
$ProhitsDB = new mysqlDB(PROHITS_DB);
$mangerDB = new mysqlDB(MANAGER_DB);
$prohits_link = $ProhitsDB->link;
$msManager_link = $mangerDB->link;

foreach($HITS_DB as $key => $DBname){
  $HitDB[$key] = new mysqlDB(PROHITS_DB);
  $HitDB_links[$key] = $HitDB[$key]->link;
}
/*
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$prohits_link  = mysqli_connect("$host", $user, $pswd, PROHITS_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
mysqli_query($prohits_link, "SET SESSION sql_mode = ''");
$msManager_link  = mysqli_connect("$host", $user, $pswd, MANAGER_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
mysqli_query($msManager_link, "SET SESSION sql_mode = ''");
$HitDB_links = array();
foreach($HITS_DB as $key => $DBname){
  $HitDB_links[$key]  = mysqli_connect("$host", $user, $pswd, $DBname ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
  mysqli_query($HitDB_links[$key], "SET SESSION sql_mode = ''");
}
*/

$proDBarr = get_projectDB();

if(array_key_exists('REQUEST_METHOD', $_SERVER)){
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
  
/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/
  
 
  if(!$SID  or !$tableName or !$frm_ID_str or !$frm_format){
    echo "not enough information passed"; exit;
  }  
  if(!check_login($SID,$tableName)) {
    echo "you didn't login Prohits!";
    exit;
  } 
  
  if($frm_merged_file_name){
    if(preg_match("/^(.+)\.$frm_format$/i", $frm_merged_file_name, $matches)){
      $merged_file_name = $matches[1];
    }else{
      $merged_file_name = $frm_merged_file_name;
    }
    $file_name_ext = $merged_file_name.'.'.$frm_format;
    $SQL = "SELECT ID FROM $tableName WHERE FileName='$file_name_ext'";
    if($row = mysqli_fetch_assoc(mysqli_query($msManager_link,$SQL))){
      $msg = "The file name $file_name_ext has been used. Please enter another name";
      output_html($msg,1);
      exit;
    }
    if(preg_match("/^(.+),$/i", $frm_ID_str, $matches)) $frm_ID_str = $matches[1];
    $merged_file_id_arr = explode(',',$frm_ID_str);
    sort($merged_file_id_arr,SORT_NUMERIC);
    $frm_ID_str = implode(",", $merged_file_id_arr);
    $SQL = "SELECT MergedName FROM MergedFiles WHERE ID_str='$frm_ID_str' AND TableName='$tableName' AND MergedType='$frm_format'";
    if($mergedFileRow = mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL))){
      $msg = "File had been merged. Name: " .$mergedFileRow['MergedName'];
      output_html($msg,1);
      exit;
    }
  }else{
    $merged_file_name = '';
  } 
//***************************************************************************************************
  $org_par_str = $frm_PROTEOWIZARD_par_str;
  $frm_PROTEOWIZARD_par_str = escapeshellarg($frm_PROTEOWIZARD_par_str);
  //$frm_PROTEOWIZARD_par_str = str_replace(" ","*", $frm_PROTEOWIZARD_par_str);
  //$frm_PROTEOWIZARD_par_str = str_replace("\"","#", $frm_PROTEOWIZARD_par_str);
  $theURL = "http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["PHP_SELF"];
  $cmd = "$php_command_location " . __FILE__ ." $tableName $frm_ID_str $frm_format $User_ID $frm_replace_existing $frm_PROTEOWIZARD_par_str ".$theURL." $merged_file_name$frm_NOmgf";
  //if($debug){
  if(defined('DEBUG_CONVERTER') and DEBUG_CONVERTER){
    echo "<b>This page is stopped on debug mode. <br>If you are Prohits administrator, copy the command line to shell</b><br>\n $cmd";
  }else{
    $tmp_PID =  system($cmd."> /dev/null & echo \$!");
    //exec("wine /var/www/html/pwiz/msconvert.exe 2>&1", $output);
    //print_r($output);exit; 
    $msg = "PSID:$tmp_PID $tableName. Convert file(s) to $frm_format. Start at  " . @date("Y-m-j G:i:s");
    $msg .= "\nParameter: $org_par_str";
    writeLog($msg, $logfile);
    echo "<br>";
    output_html("Request has been sent to Prohits and it is running in the background. Please click the 'Log File' button to see the progress.");
  }
  exit;
}else if(count($_SERVER['argv']) > 6){
 
  $tableName = $_SERVER['argv'][1];
  $frm_ID_str = $_SERVER['argv'][2];
  $frm_format = $_SERVER['argv'][3];
  $User_ID = $_SERVER['argv'][4];
  $frm_replace_existing = $_SERVER['argv'][5];
  $frm_PROTEOWIZARD_par_str = $_SERVER['argv'][6];
  $frm_theURL = $_SERVER['argv'][7];
  
  
  $frm_merged_file_name = '';
  $frm_no_mgf = '';
  
  if(isset($_SERVER['argv'][8])){
    if($_SERVER['argv'][8] == 'NOmgf'){
      $no_addtional_mgf = 1;
    }else{
      $frm_merged_file_name = $_SERVER['argv'][8];
    }
  }
  $tmp_arr = explode(":", $frm_format);
  if(count($tmp_arr) == 2){
    $frm_format = $tmp_arr[0];
    if($tmp_arr[1] == 'SWATH'){
      $rawConvert_arr['is_SWATH_file'] = true;
    }
  }
  $rawConvert_arr['Format'] = $frm_format;
  $rawConvert_arr['is_iProphet'] = false;
  //$frm_PROTEOWIZARD_par_str = trim(str_replace("#","\"", $frm_PROTEOWIZARD_par_str));
  $rawConvert_arr['Parameter'] =  $frm_PROTEOWIZARD_par_str;
}else{
  $msg = 'Shell script convert_raw_file.php has not enough arguments passed. converting stopped';
  fatalError($msg,  __LINE__, $logfile);
}
//*********************************************************************************************************

/*
//#####################################
$rawConvert_arr['Format'] = $frm_format;
$rawConvert_arr['Parameter'] = $frm_PROTEOWIZARD_par_str;
$frm_theURL = "http://192.197.250.100:80/Prohits/msManager/autoBackup/convert_raw_file.php";
//echo $rawConvert_arr['Parameter']."<br>";
//####################################
*/
$filePath_arr = array();
$filePath = "";
$ID_arr = explode(",", $frm_ID_str);

for($i = 0; $i< count($ID_arr); $i++){
  if(is_numeric(trim($ID_arr[$i]))){
    $tmp_file_path = getFilePath($tableName, $ID_arr[$i]);
    array_push($filePath_arr, array('ID'=>$ID_arr[$i],'Path'=>$tmp_file_path));
  }
}
$converted_file_arr = array();
$merged_file_size = 0;
$FolderID = '';
$ProjectID = '';
$tmp_user = '';
foreach($filePath_arr as $rawFile_arr){
  if(!_is_file($rawFile_arr['Path']) ){
    $msg = "The file doesn't exist: ".$rawFile_arr['Path'];
    echo "$msg<br>";
    writeLog($msg, $logfile);
  }else{
    $SQL = "select ID, FileName, FileType, FolderID, User, ProhitsID, ProjectID from $tableName where ID='".$rawFile_arr['ID']."'";
    if($rawFileRow = mysqli_fetch_assoc(mysqli_query($msManager_link, $SQL))){
      if(!$FolderID) $FolderID = $rawFileRow['FolderID'];
      if(!$ProjectID) $ProjectID = $rawFileRow['ProjectID'];
      if(!$tmp_user) $tmp_user = $rawFileRow['User'];
      $upperType = strtoupper($rawFileRow['FileType']);
      if($upperType == 'RAW' or $upperType == 'WIFF'){
         
         
        $new_converted_file_arr = get_new_converted_file_array($User_ID, $frm_replace_existing, $rawFile_arr['Path'], $rawFile_arr['ID']); 
        //print_r($rawConvert_arr);
        //print_r($new_converted_file_arr);exit;
         
        //array('path'=>, 'ID'=>,'status'=>, 'FileName'=>,'User_ID'=>)
        if($new_converted_file_arr['status'] == 'existed'){
          writeLog($new_converted_file_arr['path']. " exists", $logfile);
          $merged_file_size += _filesize($new_converted_file_arr['path']);
          array_push($converted_file_arr, $rawFile_arr['ID']);
          continue;
        }else{ 
          $converted_file = array();
          
          $converted_file = convertLargeRawFile($tableName, $rawFile_arr['ID'],$rawFileRow['FileName'], $rawFileRow['FileType'], $rawFile_arr['Path'], $rawConvert_arr, $new_converted_file_arr, 1, $no_addtional_mgf, 'auotConvert');
                     
          if($converted_file){
            $msg = "Created file:" .$converted_file['Name'] ;
            writeLog($msg, $logfile);
            saveConvertedFile2db($converted_file, $rawFileRow);
            if(_is_file($converted_file['Path'])){
              $merged_file_size += _filesize($converted_file['Path']);
              array_push($converted_file_arr, $rawFile_arr['ID']);
            }
          }
        }  
      }
    }
  }
}
//-------- start to merge ---------------------------------------
if($frm_merged_file_name){
  $merged_file_id_arr = explode(',',$frm_ID_str);
  $diff = array_diff($merged_file_id_arr, $converted_file_arr);
  if(count($diff)){
    $failed_ids = implode(",", $diff);
    $msg = "Fail to converted file ids: " .$failed_ids;
    writeLog($msg, $logfile);
  }else{
    $merged_file_size = round($merged_file_size/1024/1024);
    if($merged_file_size > MERGE_SIZE_MAX){
      $msg = "Merged file size > ".MERGE_SIZE_MAX."MG";
      writeLog($msg, $logfile);
    }else{
      $frm_merged_file_name = $frm_merged_file_name.".".$frm_format;
      $merged_file_path = dirname($filePath_arr[0]['Path']);
      $merged_file_neme = $frm_merged_file_name;  
      $merged_file_full_neme = $merged_file_path."/".$frm_merged_file_name;
      $merged_files = '';
      foreach($filePath_arr as $filePath_val){
        $new_path = preg_replace("/\.\w+$/i", "", $filePath_val['Path']).".".$frm_format;
        $sorted_filePath_arr[$filePath_val['ID']] = $new_path;
        $merged_files .= " ".$new_path;
      }
      if($frm_format != "mzXML"){
        $cmd = "cat $merged_files > $merged_file_full_neme";
        $tmp_PID = system($cmd);
        $file_size = _filesize($merged_file_full_neme);
        $SQL = "INSERT INTO $tableName SET
               FileName = '$merged_file_neme', 
               FileType = '$frm_format', 
               FolderID = '$FolderID', 
               User = '$tmp_user', 
               ProjectID = '$ProjectID',
               Date = '".@date("Y-m-d H:i:s")."',
               Size = '$file_size'";
        $ret = mysqli_query($msManager_link, $SQL);
        $tmp_id = mysqli_insert_id($msManager_link);
        $SQL = "INSERT INTO MergedFiles SET
                TableName = '$tableName',
                MergedID = '$tmp_id',
                MergedType = '$frm_format',
                ID_str = '$frm_ID_str',
                MergedName = '$merged_file_neme'";
        $ret = mysqli_query($msManager_link, $SQL);
        $msg = "Created merged file:" .$merged_file_neme . "\nfrom $merged_files";
        writeLog($msg, $logfile);
      }
    }
  }
}

//-------------------------------------------------------------
$msg = "End file converting at  " . @date("Y-m-j G:i:s");
writeLog($msg, $logfile);
exit;

//===============================================
function check_login($SID, $tableName){
//=============================================== 
  global $prohits_link;
  global $msManager_link;
  global $User_ID;
  $rt = false;
  $SQL = "SELECT U.ID, U.Type FROM Session S, User U WHERE U.ID=S.UserID and S.SID = '$SID'";
  $result = mysqli_query($prohits_link, $SQL);
  if($row = mysqli_fetch_row($result) ){
     $User_ID = $row[0];
    //$User_Type = $row[1];
    //if($User_Type == 'MSTech' or $User_Type == 'Admin'){
     
    $rt = true;
    //} 
  }
  return $rt;
}

function output_html($msg,$unlog=0){ 
?>
  <html>
  <body <body bgcolor='#C0C0C0'>
  <table width='400' border='0' cellpadding='1' cellspacing='0' align='center'>
  <tr>
  <td>
     <b><?php echo $msg?></b>
  </td>
  </tr>
  </table>
  </span>
  <br>
  <center>
  <form>
  <input type=button value='Close Window' onclick='javascript: window.close();'>
<?php if(!$unlog){?>  
  <input type=button value='Log File' onclick="javascript: document.location='../../logs/log_view.php?log_file=raw_back.log'">
<?php }?>
  </form> 
</body>
</html> 
<?php 
}
?>