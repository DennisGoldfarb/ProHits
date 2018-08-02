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

$menu_color = '#669999';
$msg = '';
$error = '';
$frm_url = '';
$raw_file_ID = '';
$SearchEngine = '';
$taskID = '';
$tableName = '';

$tableSearchResults = '';
$tableSearchTasks = '';
$tmp_folder_pro_name = '';
$DataFile = '';

include("./ms_permission.inc.php");
require("./classes/Storage_class.php");

if($USER->Type == 'MSTech' or $USER->Type == 'Admin'){
  $where_project = 1;
}else{
  $where_project = "T.ProjectID in($pro_access_ID_str)";
}
if(isset($taskID) and isset($tableName) ){
    $tableSearchResults = $tableName . "SearchResults";
    $tableSearchTasks = $tableName . "SearchTasks";
    
    $where = "ID='$taskID'  and ". $where_project;
    $SQL = "SELECT 
          ID, 
          PlateID, 
          DataFileFormat, 
          SearchEngines, 
          Parameters, 
          TaskName, 
          LCQfilter, 
          Schedule, 
          StartTime, 
          AutoAddFile, 
          Status, 
          UserID,
          ProjectID 
          FROM $tableSearchTasks T where  $where";
  $task_record = $managerDB->fetch($SQL);
  if(!$task_record){
     echo "no enough information to process your request.";exit;
  }
}
//get original info
$fileObj =  new Storage($managerDB->link,$tableName);
$folderObj =  new Storage($managerDB->link,$tableName);
$fileObj->fetch($raw_file_ID);

if($fileObj->FolderID){
  $folderObj->fetch($fileObj->FolderID);
}else{
  echo "could not find (id: $raw_file_ID) this file in any folder";exit;
}
$SQL = "select ID, Name, DBname from Projects where ID='".$fileObj->ProjectID."'";
$pro_rd = $prohitsDB->fetch($SQL);
if($pro_rd){
  $tmp_folder_pro_name = $pro_rd['Name'];
}
if($perm_modify and $theaction == 'save'){
  if($SearchEngine == 'Mascot'){
    //$frm_url = 'http://tyers-frankibm/mascot/cgi/master_results.pl?file=F:/Mascot_Data/20070522/F001477.dat';
    preg_match('@file=(.+$)@i',  $frm_url, $matches);
    if($matches){
      $DataFile = $matches[1];
      if(!preg_match('@http://'.MASCOT_IP.MASCOT_CGI_DIR.'/master_results.pl@i', $frm_url)){
        $error = "Error: The Mascot search results should be from http://".MASCOT_IP.MASCOT_CGI_DIR;
      }
    }else{
      $error = 'Error: Please put a current Mascot search result URL';
    }
    if(!$error){
      if ((strpos($frm_url, "http")) === false) $frm_url = "http://" . $frm_url;
      if (!is_array(get_headers($frm_url))) $error = "Error: the url doesn't exist";
    }
  }
  if(!$error){
    $SQL = "UPDATE $tableSearchResults set
         `DataFiles` = '$DataFile',
         `Date` = now()
          Where `WellID` = '$raw_file_ID' and `TaskID` = '$taskID' and `SearchEngines` = '$SearchEngine'";
    
    $ret = $managerDB->update($SQL);
    if($ret == '1'){
      $log = "file=$DataFile, WellID=$raw_file_ID, TaskID=$taskID, SearchEngines=$SearchEngine";
      $SQL ="INSERT INTO Log SET 
          UserID='".$USER->ID."', 
          MyTable='$tableSearchResults', 
          RecordID='$taskID', 
          MyAction='addSearchLink', 
          Description='$log',
          ProjectID='".$fileObj->ProjectID."'";
      $prohitsDB->insert($SQL);
      $msg = "The link has been added. Please close this window and reload the search result page.";
    }
  }
}
?>
<html>
<body>
<script language="javascript"> 
function checkForm(theForm){
  if(theForm.frm_url.value){
    theForm.theaction.value = 'save';
    theForm.submit();
  }else{
    alert('Please paste the Mascot search result URL in the text box');
  }
}
</script>

<form name=editform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=raw_file_ID value='<?php echo $raw_file_ID;?>'>
<input type=hidden name=SearchEngine value='<?php echo $SearchEngine;?>'>
<input type=hidden name=taskID value='<?php echo $taskID;?>'>
<input type=hidden name=tableName value='<?php echo $tableName;?>'>
<input type=hidden name=theaction value=''>

<table border=0 width=99% cellspacing="5">

<tr>
  <td align=center colspan=2>
  <font face="Arial" size="+2" color="#660000"><b>Add <?php echo $SearchEngine;?> Result Link</b></font><br>
  <font color=red><?php echo $error;?></font>
  <font color=green><?php echo $msg;?></font>
  <hr width="100%" size="1" noshade>
  </td>
</tr>
<tr>
  <td width=30% valign=top bgcolor="<?php echo $menu_color;?>"><font face="Arial" size="2" color="#ffffff"><b>Raw File Information</b></font>
  </td>
  <td>
  <font face="Arial" size="2" color="#008000">
  Machine Name: <font color=black><?php echo $tableName;?></font><br>
  Raw file Storage ID: <font color=#000000><?php echo $fileObj->ID;?></font><br>
  Raw file path: <font color=black><b><?php echo "$folderObj->FileName/$fileObj->FileName";?></b></font><br>
  Folder Project Name: <font color=#ff0000><?php echo $tmp_folder_pro_name;?></font><br>
  <hr>
  Search Task ID: <font color=black><b><?php echo $taskID;?></b></font><br>
  Search Engines:  <font color=black><b><?php echo $task_record['SearchEngines'];?></b></font><br>
  Search Task Name:  <font color=black><b><?php echo $task_record['TaskName'];?></b></font><br>
   
  </font>
  </font>
</td></tr>
<tr>
  <td width=30% valign=top bgcolor="<?php echo $menu_color;?>"><font face="Arial" size="2" color="#ffffff"><b>Link <?php echo $SearchEngine;?> Search Results</b>
  </td>
  <td>
    <table>
      <tr>
      <td>
      <b><font face="Arial"><?php echo $SearchEngine;?> Search Result URL:</font></b>
      </td>
      </tr>
      <tr>
      <td>
      <input type=text size=60 name=frm_url value='<?php echo $frm_url;?>'>
      </td>
      </tr>
    </table>
  </td>
</tr>
<tr>
 <td colspan=2 align=center>
  <input type=button value='Submit' onClick='checkForm(this.form)'>
  <input type="button" value='Close' onClick="window.close()";>
 </td>
</tr>
</table>
</form>
</body>
</html>