<?
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
include("../../common/mysqlDB_class.php");
include("../../common/common_fun.inc.php");
//require_once("../common_functions.inc.php");

define("STORAGE_TMP_ZIP_FOLDER", "../../TMP/");

$PHP_SELF = $_SERVER['PHP_SELF'];

$file_is_dir = false;
$username = '';
$user_ID ='';
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
echo "</pre>"; 
*/
$host = HOSTNAME;

$prohitsDB = new mysqlDB(PROHITS_DB);
$managerDB = new mysqlDB(MANAGER_DB);

if(!check_download_permission($SID)) {
  echo "please login prohits.";
  exit;
}

$export_list_file = 'fileList_'. @date("Y-m-d").".txt";
$user_raw_export_dir = check_user_raw_export_folder($username);
if($user_raw_export_dir){
  $export_file_log = $user_raw_export_dir. "/$export_list_file";
  $fd = fopen($export_file_log, 'a+');
  if(!$fd){
    echo "can not open the log file to write: $export_list_file"; exit;
  }
}  

if(!isset($ProjectID) or !isset($ID_string)) exit;
$insert_only = 1;
$Pro_ID_names = get_user_permited_project_id_name($prohitsDB, $user_ID, $insert_only);

$Pro_ID_dbName = get_projectID_DBname_pair($prohitsDB, $ProjectID);
if(!isset($HITS_DB[$Pro_ID_dbName[$ProjectID]]) or $HITS_DB[$Pro_ID_dbName[$ProjectID]] == PROHITS_DB){
  $hitsDB = $prohitsDB;
   
}else{
  $hitsDB = new mysqlDB($HITS_DB[$Pro_ID_dbName[$ProjectID]]);
}

$all_sample_ID_string = '';
if($item_type == 'Sample'){
  $ID_string = str_replace(" ", '', $ID_string);
  $ID_string = str_replace(";", ',', $ID_string);
  if($all_sample_ID_string) $all_sample_ID_string .= ",";
  $all_sample_ID_string .= $ID_string;
}else{
  $tmp_arr = explode("|", $ID_string);
  foreach($tmp_arr as $theValue){
    if(preg_match("/\((.+)\)/", $theValue, $matches)){
      $matches[1] = str_replace(";", ',', $matches[1]);
      if($all_sample_ID_string) $all_sample_ID_string .= ",";
      $all_sample_ID_string .= $matches[1];
    }
  }
}
 
$SQL = "select RawFile from Band where ID in($all_sample_ID_string)";

$raw_files = $hitsDB->fetchAll($SQL);
//print_r($raw_files);
$tableName2ID_string = '';
foreach($raw_files as $v){
  if($tableName2ID_string) $tableName2ID_string .= ";";
  $tableName2ID_string .= $v['RawFile'];
}
 
$isTPP = '';
$searchEngine = '';

$hitType = preg_replace("/^GeneLevel/", "TPP", $hitType);
if(strpos($hitType, "MSPLIT")){
  $hitType = preg_replace("/^TPP_/", "", $hitType);
}

if(preg_match("/TPP\s+(.*)/", $hitType, $matches) || preg_match("/TPP_(.*)/", $hitType, $matches)){
  $isTPP = 1;
  $searchEngine = $matches[1];
}else{
  if($hitType == 'iProphet'){
    $isTPP = 1;
  }
  $searchEngine = $hitType;
}
$tableName2ID_arr = explode(";", $tableName2ID_string);
foreach($tableName2ID_arr as $tmp_tableName2ID){
  list($tableName, $ID) = explode(":", $tmp_tableName2ID);
  if($isTPP){
    $tppResults_table = $tableName."tppResults";
    $SQL = "select WellID from $tppResults_table where WellID='$ID' and SearchEngine='$searchEngine' and SavedBy > 0";
    $rd = $managerDB->fetch($SQL);
  }else{
    $Results_table = $tableName."SearchResults";
    $SQL = "select WellID from $Results_table where WellID='$ID' and SearchEngines='$searchEngine' and SavedBy > 0";
   
    $rd = $managerDB->fetch($SQL);
    
  }
  if($rd){
    
    write_to_download_list($tableName, $ID, $managerDB->link , $fd);
  }
}
fclose($fd);
?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
 <html>
  <body style="font-family: arial,sans-serif; font-size: 10pt; background-color: #ccc;">
  <div style="padding: 10px 20px; display:block; margin: 0px auto; width:400px; height:230px; background-color:#FFF; border:#708090 1px solid;">
   
    <h2>Raw files have been added in exporting list. Please go to following page then use FTP to transfer files.</h2>
    <br> "Data management" => "Storage" => "Export Raw Files"
    <hr size=1 noshade>
    <form>
      <center><input type=button value='Close Window' onclick='javascript: window.close();'>
    </form> 
  </div>
  
 
</body>
</html> 
<?
//---------------------------------------
function check_download_permission($SID){
//---------------------------------------
  global $prohitsDB;
  global $username;
  global $user_ID;
  $pro_access_ID_str = '';
  $rt = false;
  if($SID == 'rawDataConverter'){
    return true;
  }
  $SQL = "SELECT U.ID, U.Username, U.Type FROM Session S, User U WHERE U.ID=S.UserID and S.SID = '$SID'";
  $result = mysqli_query($prohitsDB->link, $SQL);
  if($row = mysqli_fetch_row($result) ){
    $user_ID = $row[0];
    $username = $row[1];
    $rt = true;
  }else{
    $msg = "session id is not in session table $SID. or the user has no permission to run the script";
  }
  return $rt;
}
function write_to_download_list($tableName, $ID, $msdb_link, $fd){
  while($ID){
    $raw_ID = $ID;
    $SQL = "select ID, FileName, FileType, FolderID, RAW_ID from $tableName where ID='$ID'";
    if($row = mysqli_fetch_assoc(mysqli_query($msdb_link, $SQL))){
      $ID = $row['RAW_ID'];
      if(!$ID and $row['FileType'] == 'wiff'){
        $new_file_name = $row['FileName'].".scan";
        $SQL = "select ID, FileName from $tableName where FileName='".$new_file_name."' and FolderID='".$row['FolderID']."'";
        if($row = mysqli_fetch_assoc(mysqli_query($msdb_link, $SQL))){
          fwrite($fd, "\r\n" . "RAW\t$tableName\t".$row['ID']);
        }
      }
    }
  }
  fwrite($fd, "\r\n" . "RAW\t$tableName\t$raw_ID");
}
?>