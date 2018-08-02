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

 
$frm_project_ID = 0;
$theTaskID = 0;
$menu_color = '#8998DB';
$msg = '';
$table = '';
$theaction = '';

include("./ms_permission.inc.php");
require("./classes/Storage_class.php");
if(!$table or !$theTaskID) exit;
 
$SQL = "SELECT ID, TaskName, UserID, ProjectID FROM $tableSearchTasks where ID='$theTaskID'";
$task_record = $managerDB->fetch($SQL);
if($task_record){
  
  if($USER->Type != 'Admin' and $USER->ID != $task_record['UserID']){
    exit;
  }
}else{
  exit;
}
if($theaction){
  $SQL = "update $tableSearchTasks set ProjectID='$frm_project_ID' where ID='$theTaskID'";
  $managerDB->update($SQL);
  closeWindow();
  exit;
}else{
  $frm_project_ID = $task_record['ProjectID'];
}
?>
<html>
<body>
<script language="javascript"> 
function changeProject(theForm){
  obj = theForm.frm_project_ID;
  if(obj.options[obj.selectedIndex].value == '-1'){
    alert('Please select a Project!');
    return false;
  }
  
  theForm.submit();
}
 
</script>

<form name=editform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=table value='<?php echo $table;?>'>
<input type=hidden name=theTaskID value='<?php echo $theTaskID;?>'>
<input type=hidden name=theaction value='yes'>
<table border=0 width=99% cellspacing="5">

<tr>
  <td align=center colspan=2>
  <font face="Arial" size="+2" color="#660000"><b>Change Task Project</b></font><br>
  <font color=red><?php echo $msg;?></font>
  <hr width="100%" size="1" noshade>
  </td>
</tr>
<tr>
  <td width=30% valign=top bgcolor="<?php echo $menu_color;?>"><font face="Arial" size="2" color="#ffffff"><b>Original Task information</b></font>
  </td>
  <td>
  <font face="Arial" size="2" color="#008000">
  ID: <font color=black><?php echo $task_record['ID'];?></font><br>
  Name: <font color=black><?php echo $task_record['TaskName'];?></font><br>
  Project: <font color=#000000><?php echo $pro_access_ID_Names[$task_record['ProjectID']];?></font><br>
  </font>
</td></tr>
<tr>
  <td width=30% valign=top bgcolor="<?php echo $menu_color;?>"><font face="Arial" size="2" color="#ffffff"><b>Change Project name</b>
  </td>
  <td>
    <table>
      <tr>
      <td>
      <b><font face="Arial">Project Name:</font></b>
      </td>
      <td>
      <select name="frm_project_ID">
      <option value='-1'>-- select project --
      <?php 
       
      foreach($pro_access_ID_Names as $tmp_pro_ID=>$tmp_pro_name){
        $selected = ($tmp_pro_ID == $frm_project_ID)? " selected": "";
        echo "  <option value='$tmp_pro_ID'$selected>($tmp_pro_ID) $tmp_pro_name\n"; 
      }
      ?>
      </select>
      </td>
      </tr>
       
    </table>
  </td>
</tr>
<tr>
 <td colspan=2 align=center>
  <input type=button value='Submit' onClick='changeProject(this.form)'>
  <input type="button" value='Close' onClick="window.close()";>
 </td>
</tr>
</table>
</form>
</body>
</html>
 <?php 
function closeWindow(){
?>
<html>
<body>
<script language='javascript'>
window.opener.location.reload();
window.close();
</script>
</body>
</html>
<?php 
}
?>