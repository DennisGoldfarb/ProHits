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

$open_dir_ID = 0; 
$frm_project_ID = 0;
$frm_plate_ID = 0;
$menu_color = '#8998DB';
$msg = '';

include("./ms_permission.inc.php");
require("./classes/Storage_class.php");
if($USER->Type == 'MSTech' or $USER->Type == 'Admin'){
  //nothing to do
}else{
  echo "you has no permission to access this page";exit;
}
//get original info
$ObjTable =  new Storage($managerDB->link,$tableName);
$ObjTable->fetch($open_dir_ID);
$tmp_pro_name = ($ObjTable->ProjectID)?$pro_access_ID_Names[$ObjTable->ProjectID]:'';
if($theaction == 'savechange' and $frm_project_ID != '-1'){
  $tmp_plate_ID = ($frm_plate_ID == '-1')? 'NULL':$frm_plate_ID;
  if($ObjTable->ProjectID != $frm_project_ID or (($ObjTable->ProhitsID or $frm_plate_ID !='-1') and $ObjTable->ProhitsID != $frm_plate_ID)){
    $SQL = "update $tableName set ProjectID='$frm_project_ID', ProhitsID=$tmp_plate_ID, User='".$USER->ID."' where ID='$open_dir_ID'";
    if($managerDB->update($SQL)){
      //$SQL = "update $tableName set ProjectID='$frm_project_ID' where FolderID='$open_dir_ID' and ( ProhitsID = 0 or ProhitsID is null)";
      //$managerDB->update($SQL);
      $msg = "old ProID=". $ObjTable->ProjectID. " new ProID=$frm_project_ID; old plateID=". $ObjTable->ProhitsID . " new plateID=$tmp_plate_ID";
      $SQL = "insert into Log set UserID='".$USER->ID."', MyTable='$tableName', RecordID='$open_dir_ID', Myaction='modify', Description='$msg', ProjectID='$frm_project_ID'";
      $managerDB->insert($SQL);
       
    }
    closeWindow();
    exit;
  }else{
    $msg = "nothing changed";
  }
}

if(!$frm_project_ID) {
  $frm_project_ID = $ObjTable->ProjectID;
}
if(!$frm_plate_ID){
  $frm_plate_ID = $ObjTable->ProhitsID;
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
  theForm.frm_plate_ID.selectedIndex = '-1';
  theForm.submit();
}
function checkForm(theForm){
  obj = theForm.frm_project_ID;
  if(obj.options[obj.selectedIndex].value == '-1'){
    alert('Please select a Project!');
    return false;
  }else if('<?php echo $ObjTable->ProjectID;?>' != obj.options[obj.selectedIndex].value){
    if(!confirm("The selected project is different from original one.\n Are you sure that you want to save the change?")){
      return false;
    }
  }
  obj = theForm.frm_plate_ID;
  if(obj.options[obj.selectedIndex].value == '-1'){
    if(!confirm("You didn't select a plate. The Prohits Plate/folder ID will be set to '0'. \n Are you sure that you want to save the change?")){
      return false;
    }
  }else if('<?php echo $ObjTable->ProhitsID;?>' != obj.options[obj.selectedIndex].value){
    if(!confirm("The selected plate is different from original one.\n Are you sure that you nat to save the change?")){
      return false;
    }
  }
  theForm.theaction.value = 'savechange';
  theForm.submit();
}
</script>

<form name=editform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=open_dir_ID value='<?php echo $open_dir_ID;?>'>
<input type=hidden name=tableName value='<?php echo $tableName;?>'>
<input type=hidden name=theaction value=''>
<table border=0 width=99% cellspacing="5">

<tr>
  <td align=center colspan=2>
  <font face="Arial" size="+2" color="#660000"><b>Change Prohits Project/Plate ID</b></font><br>
  <font color=red><?php echo $msg;?></font>
  <hr width="100%" size="1" noshade>
  </td>
</tr>
<tr>
  <td width=30% valign=top bgcolor="<?php echo $menu_color;?>"><font face="Arial" size="2" color="#ffffff"><b>Original Folder/plate information</b></font>
  </td>
  <td>
  <font face="Arial" size="2" color="#008000">
  Machine Name: <font color=black><?php echo $tableName;?></font><br>
  Plate/Folder Name: <font color=black>"<?php echo $ObjTable->FileName;?></font><br>
  Folder Storage ID: <font color=#000000><?php echo $ObjTable->ID;?></font><br>
  Prohits Analyst Plate ID: <font color=#ff0000><?php echo $ObjTable->ProhitsID;?></font><br>
  Project Name: <font color=#ff0000><?php echo $tmp_pro_name;?></font><br>
  Created on: <font color=#000000><?php echo $ObjTable->Date;?></font><br>
  <?php 
  if(is_numeric($ObjTable->User)){
    $SQL = "select Fname, Lname from User where ID='".$ObjTable->User."'";
    $record = $prohitsDB->fetch($SQL);
    if(count($record)){
      echo "Link Modified by: <font color=#000000>".$record['Fname'] . " " . $record['Lname']."</font>";
    }
  }
  ?>
  </font>
</td></tr>
<tr>
  <td width=30% valign=top bgcolor="<?php echo $menu_color;?>"><font face="Arial" size="2" color="#ffffff"><b>Change Project name and Prohits ID</b>
  </td>
  <td>
    <table>
      <tr>
      <td>
      <b><font face="Arial">Project Name:</font></b>
      </td>
      <td>
      <select name="frm_project_ID"  onChange="changeProject(this.form);">
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
      <tr>
      <td>
      <b><font face="Arial">Plate/Folder: </font></b>
      </td>
      <td>
      <select name="frm_plate_ID">
      <option value='-1'>--not in Prohits Analyst--
      <?php 
      if($frm_project_ID){
        $SQL = "SELECT DBname FROM Projects where ID='$frm_project_ID'";
        $project_result = $prohitsDB->fetch($SQL);
        $DBname = $project_result['DBname'];
        $hitsDB = new mysqlDB($HITS_DB[$DBname]);
        $SQL = "SELECT ID, Name FROM Plate where ProjectID='$frm_project_ID' ORDER BY ID DESC";
        $records = $hitsDB->fetchAll($SQL);
        for($i = 0; $i < count($records); $i++){
          $selected = ($frm_plate_ID == $records[$i]['ID'])? " selected": "";
          echo "<option value='".$records[$i]['ID']."'$selected>(".$records[$i]['ID'].") ".$records[$i]['Name']. "\n";
        }
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
  <input type=button value='Submit' onClick='checkForm(this.form)'>
  <input type="reset" value="Reset">
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
opener.refreshWin('');
window.close();
</script>
</body>
</html>
<?php 
}
?>