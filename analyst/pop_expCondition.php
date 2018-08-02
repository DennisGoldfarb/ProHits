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

$theaction = '';
$frm_ID = '';
$frm_Condition = '';
$frm_Description = '';
//$frm_UserID = '';
$ConditionUser = '';
$ConditionArr = array();
$need2refresh = '';
$refresh = '';

require("../common/site_permission.inc.php");
$Log = new Log();

$frm_ProjectID = $AccessProjectID;
if($theaction == 'insert' and $AUTH->Insert){
  $SQL ="INSERT INTO Condition SET 
          Condition='".mysqli_real_escape_string($mainDB->link, $frm_Condition)."',  
          Description='".mysqli_real_escape_string($mainDB->link, $frm_Description)."',
          UserID='".$USER->ID."'";        
  $frm_ID = $mainDB->insert($SQL);
  $theaction = '';
  if($frm_ID){
    $Desc = "Name=$frm_Condition,Description=$frm_Description,Date=".@date("Y-m-d");
    $refresh = 1;
  }else{
    $Desc = "insert failed";
  }        
  $Log->insert($AccessUserID,'Condition',$frm_ID,'insert',$Desc,$AccessProjectID);
}else if($theaction == 'update' and $AUTH->Insert){
   $SQL ="UPDATE Condition SET
         Condition='".mysqli_real_escape_string($mainDB->link, $frm_Condition)."',  
         Description='".mysqli_real_escape_string($mainDB->link, $frm_Description)."'
         WHERE ID='$frm_ID' and UserID='".$USER->ID."'";
  $updateFlag = $mainDB->update($SQL);
  $theaction = '';
  
  if($updateFlag){
    $Desc = "Name=$frm_Condition,Description=$frm_Description,Date=".@date("Y-m-d");
    $refresh = 1;
  }else{
    $Desc = "updat failed";
  }
  $Log->insert($AccessUserID,'Condition',$frm_ID,'modify',$Desc,$AccessProjectID);
}else if(!$frm_ID){
  $theaction = 'add';
}

if($theaction == 'add'){
  $frm_Condition = '';
  $frm_Description = '';
}else{
  $SQL = "SELECT ID, Condition, Description, UserID  FROM Condition WHERE ID='$frm_ID'";
  $ConditionArr = $mainDB->fetch($SQL);
  if($ConditionArr){
    $frm_ID = $ConditionArr['ID'];
    $frm_Condition = $ConditionArr['Condition'];
    $frm_Description = $ConditionArr['Description'];
    $SQL = "SELECT Fname, Lname from User where ID='".$ConditionArr['UserID']."'";
    $theUser = $PROHITSDB->fetch($SQL);
    if($theUser){
      $ConditionUser = $theUser['Fname']." ".$theUser['Lname'];
    }
    
  }
}
?>
<html>
<body>
<script language="javascript">
function modify_condition(theForm){
  theForm.theaction.value = 'modify';
  theForm.submit();
}
function add_condition(theForm){
  theForm.theaction.value = 'add';
  theForm.frm_ID.value= '';
  theForm.submit();
}
function submit_add(theForm){
  if(theForm.frm_Condition.value == ''){
    alert("Please select a condition type");
    return false;
  }
  if(theForm.frm_Condition.value == ''){
    alert("Please enter a condition name");
    return false;
  }
  theForm.theaction.value = 'insert';
  theForm.submit();
}
function submit_modify(theForm){
  if(theForm.frm_Condition.value == ''){
    alert("Please select a condition type");
    return false;
  }
  if(theForm.frm_Condition.value == ''){
    alert("Please enter a condition name");
    return false;
  }
  theForm.theaction.value = 'update';
  theForm.submit();
}
function closePop(){
   var need2refresh = '<?php echo $refresh;?>';
   if(need2refresh){
    window.opener.location.reload( false );

   }
   window.close();
}
  
</script>
<form name="c_form" method=post action="<?php echo $PHP_SELF;?>">  
<input type=hidden name=theaction value=modify>
<input type=hidden name=frm_ID value="<?php echo $frm_ID;?>"> 
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <?php 
    if(!$theaction or $theaction == 'modify'){
      echo "<td bgcolor=white><font size='4' color=#000099 face=\"'MS Sans Serif',Geneva,sans-serif\"><b>Experiment Condition Detail</b></font></td>";
     
    }elseif($AUTH->Insert && $theaction == "add"){
      echo "<td bgcolor=white><font size='4' color=#000099 face=\"'MS Sans Serif',Geneva,sans-serif\"><b>Add New Experiment Condition</b></font></td>";
    }
    echo "<td align=right bgcolor=white>";
    if(isset($ConditionArr['UserID']) and $USER->ID == $ConditionArr['UserID']){
      echo "<a href='javascript: modify_condition(c_form)'>[Modify]</font></a> &nbsp;&nbsp;";
    }
    echo "<a href='javascript: closePop();'>[Close the Window]</font></a></td>";
    ?>
   
 </tr>
 <tr>  
  <td bgcolor=#000000 colspan=2>
  <table border="0" cellpadding="3" cellspacing="1" width="100%">
  <?php 
    if($theaction != 'add'){
  ?>  
  <tr>
    <td width=120 bgcolor=#376c65>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>ID</b>:</font>
    </td>
    <td valign=top bgcolor=white> 
    <div align="maintext"><?php echo $frm_ID?>
    </td>
  </tr> 
  <?php }?> 
  <tr>
    <td width=120 bgcolor=#376c65>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>Name</b>:</font>
    </td>
    <td bgcolor=white> 
    <?php 
    
    if($theaction == 'add' or $theaction == 'modify'){
    ?>
    <input type="text" name="frm_Condition" size="30" maxlength=50 value="<?php echo $frm_Condition;?>"></td>
    <?php }else{?>
    <div align="maintext"><?php echo $frm_Condition?>
    <?php }?>
     </td>
  </tr>
  <tr>
    <td width=120 bgcolor=#376c65 valign=top>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>Description</b>:</font>
    </td>
    <td bgcolor=white> <div class=maintext>
    <?php 
    if($theaction == 'add' or $theaction == 'modify'){
    ?>
    <textarea name=frm_Description cols=50 rows=10><?php echo  $frm_Description;?></textarea>
    <?php 
    }else{
      echo nl2br(htmlspecialchars($frm_Description));
    }
    ?>
    </div>
    </td>
   </tr>
   <?php 
   if($ConditionUser){?>
   <tr>
    <td width=120 bgcolor="#376c65" valign=top>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>Submitted by</b>:</font>
    </td> 
    <td bgcolor=white>
    <?php echo $ConditionUser;?>
    </td>
   </tr>
   <?php }?>
    
  </table>
  </td>
  </tr>
</table>
<br>
<center>
<?php 
if($theaction == 'add'){
?>
  <input type=button value="Submit" onClick="javascript: submit_add(this.form);">
<?php 
}else if($theaction == 'modify'){
?>
  <input type=button value="Submit" onClick="javascript: submit_modify(this.form);">
<?php 
}
if($theaction){
?>
  <input type=reset value="Reset">
<?php }?>
</center>
</form>
</BODY>
</html>
