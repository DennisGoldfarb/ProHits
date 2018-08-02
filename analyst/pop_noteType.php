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
$frm_Name = '';
$frm_Type = 'Bait';
$frm_Description = '';
$frm_Icon = '';
$frm_ProjectID = 0;
$frm_UserID = '';
$frm_Initial = '';
$frm_UserID = '';
$version_num = 0;

$icon_folder = "./gel_images";
$error_msg = '';

$NameUser = '';
$NoteTypeArr = array();
$need2refresh = '';
$refresh = '';

require("../common/site_permission.inc.php");
require_once("msManager/is_dir_file.inc.php");

$Log = new Log();

$frm_ProjectID = $AccessProjectID;
if($theaction == 'insert' and $AUTH->Insert and $USER->Type=='Admin'){
  $frm_Initial = strtoupper(trim($frm_Initial));
  $frm_Name = preg_replace("/[^A-Za-z0-9  _-]/",'',$frm_Name);
  if($frm_Initial and check_name_initial($frm_Name, $frm_Initial)){
    $icon_img_name = '';
    if($kind == 'group'){
      $icon_img_name = check_icon($frm_Icon);
    }      
    if(($kind == 'group' && $icon_img_name) || $kind == 'export'){
      $SQL ="INSERT INTO NoteType SET 
          Name='".$frm_Name."',  
          Type='".$frm_Type."',
          Description='".mysqli_real_escape_string($mainDB->link, $frm_Description)."',
          Icon='".$icon_img_name."',
          ProjectID='".$AccessProjectID."',
          UserID='".$USER->ID."',
          Initial='".$frm_Initial."'";
      $frm_ID = $mainDB->insert($SQL);
    }else{
      $error_msg = "Please check the icon image size and image format.";
    }
  }else{
    if($kind == 'group'){
      $error_msg = "The group name or initial has been used";
    }else{
      $error_msg = "The exported version name has been used";
    }  
  }
  $theaction = '';
  if($frm_ID){
    $Desc = "Name=$frm_Name,Description=$frm_Description,Date=".@date("Y-m-d");
    $Log->insert($AccessUserID,'NoteType',$frm_ID,'insert',$Desc,$AccessProjectID);
    $refresh = 1;
  }else{
    $Desc = "insert failed";
  }
  if($error_msg){
    $theaction = 'add';
  }
}else if($theaction == 'update' and $AUTH->Insert and $USER->Type=='Admin'){
   $SQL ="UPDATE NoteType SET
         Name='".mysqli_real_escape_string($mainDB->link, $frm_Name)."',  
         Type='".$frm_Type."',
         Description='".mysqli_real_escape_string($mainDB->link, $frm_Description)."',
         Initial='".$frm_Initial."' 
         WHERE ID='$frm_ID' and UserID='".$USER->ID."'";
  $updateFlag = $mainDB->update($SQL);
  $theaction = '';
  
  if($updateFlag){
    $Desc = "Name=$frm_Name,Description=$frm_Description,Date=".@date("Y-m-d");
    $Log->insert($AccessUserID,'NoteType',$frm_ID,'modify',$Desc,$AccessProjectID);
    $refresh = 1;
  }else{
    $Desc = "updat failed";
  }
}else if(!$frm_ID){
  $theaction = 'add';
}
if($theaction == 'add'){
  if($kind == "export"){
    $SQL = "SELECT Initial FROM NoteType WHERE ProjectID='$AccessProjectID'";
    $NoteInitArr = $mainDB->fetchAll($SQL);
    foreach($NoteInitArr as $value){
      if(!is_numeric($value['Initial'])) continue;
      if($value['Initial'] > $version_num) $version_num = $value['Initial'];
    }
    $frm_Initial = ++$version_num;
  }
}else if($frm_ID){
  $SQL = "SELECT ID, 
          Name, 
          Type, 
          Description, 
          Icon, 
          UserID, 
          ProjectID, 
          Initial  
          FROM NoteType 
          WHERE ID='$frm_ID' 
          AND ProjectID='$AccessProjectID'";
  $NoteTypeArr = $mainDB->fetch($SQL);
  if($NoteTypeArr){
    //$frm_ID = $NoteTypeArr['ID'];
    $frm_Name = $NoteTypeArr['Name'];
    $frm_Description = $NoteTypeArr['Description'];
    $frm_Icon = $NoteTypeArr['Icon'];
    $frm_Initial = $NoteTypeArr['Initial'];
    $frm_UserID = $NoteTypeArr['UserID'];
    $SQL = "SELECT Fname, Lname from User where ID='".$frm_UserID."'";
    $theUser = $PROHITSDB->fetch($SQL);
    if($theUser){
      $NameUser = $theUser['Fname']." ".$theUser['Lname'];
    }
    
  }
}

if($frm_Icon && $theaction == 'changeIcon' and $frm_ID and $frm_UserID == $USER->ID){
echo $frm_Icon."<br>";
  if(!check_icon($frm_Icon)){
    $error_msg = "Please check the icon image size and image format.";
  }else{
    $refresh = 1; 
  }
}
if($kind == "group"){
  if($frm_Type == 'Band'){
    $tmp_tital = 'Sample';
  }else{
    $tmp_tital = $frm_Type;
  }
  $win_title1 = $tmp_tital . " Group Detail";
  $win_title2 = "Add Bait Group";
}else{
  $win_title1 = "Exported Version Detail";
  $win_title2 = "Add Exported Version";
}
?>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="./site_style.css"> 
</head>
<body>
<style type="text/css">
<!--

-->
</style>


<script language="javascript">
function modify_noteType(theForm){
  theForm.theaction.value = 'modify';
  theForm.submit();
}

function submit_add(theForm){
  if(theForm.kind.value == 'group'){
    if(theForm.frm_Name.value == '' || theForm.frm_Initial.value == ''){
      alert("The bait group name and group initial are required!");
      return false;
    }else if(!(/[a-zA-Z]/.test(theForm.frm_Initial.value))){
      alert("The group initial should be two letters!");
      return false;
    }else if(theForm.frm_Initial.value.length > 2){
      alert("The group initial length should be two letters!");
      return false;
    }else if(theForm.frm_Icon.value == ''){
      alert("The bait group icon is required!");
      return false;
    }
  }else{
    if(theForm.frm_Name.value == ''){
      alert("The exporet version name required!");
      return false;
    }  
  }  
  theForm.theaction.value = 'insert';
  theForm.submit();
}
function submit_modify(theForm){
  if(theForm.frm_Name.value == ''){
    alert("Please select a name type.");
    return false;
  }
  if(theForm.frm_Name.value == ''){
    alert("Please enter a name.");
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
function change_icon(theForm){
  theForm.theaction.value = 'changeIcon';
  if(theForm.frm_Icon.value == ''){
    alert("Please add bait group icon");
    return false;
  }
  theForm.submit();
}  
</script>
<form name="T_form" method=post action="<?php echo $PHP_SELF;?>" enctype="multipart/form-data">
<input type=hidden name=theaction value=modify>
<input type=hidden name=frm_ID value="<?php echo $frm_ID;?>">
<input type=hidden name=kind value="<?php echo $kind;?>">
<input type=hidden name=frm_Type value="<?php echo $frm_Type;?>"> 
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <?php 
    if($theaction != 'add'){
      echo "<td bgcolor=white><font size='4' color=#000099 face=\"'MS Sans Serif',Geneva,sans-serif\"><b>$win_title1</b></font></td>";
     
    }elseif($AUTH->Insert && $theaction == "add"){
      echo "<td bgcolor=white><font size='4' color=#000099 face=\"'MS Sans Serif',Geneva,sans-serif\"><b>$win_title2</b></font></td>";
    }
    echo "<td align=right bgcolor=white>";
    if(isset($frm_UserID) and $USER->ID == $frm_UserID){
      echo "<a href='javascript: modify_noteType(T_form)' class=button>[Modify]</font></a> &nbsp;&nbsp;";
    }
    echo "<a href='javascript: closePop();' class=button>[Close the Window]</font></a></td>";
    ?>
   
 </tr>
 <?php if($error_msg){?>
  <tr><td colspan=2 align=center><font color="#FF0000"><?php echo $error_msg;?></font></td></tr>
 <?php }?>
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
    <div align="maintext"><?php echo $frm_ID?></div>
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
    <input type="text" name="frm_Name" size="30" maxlength=50 value="<?php echo $frm_Name;?>">
    <?php }else{?>
    <div align="maintext"><?php echo $frm_Name?></div>
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
    <textarea name=frm_Description cols=50 rows=5><?php echo  $frm_Description;?></textarea>
    <?php 
    }else{
      echo nl2br(htmlspecialchars($frm_Description));
    }
    ?>
    </div>
    </td>
   </tr>
   <tr>
    <td width=120 bgcolor=#376c65>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>Initial</b>:</font>
    </td>
    <td bgcolor=white> <div class=maintext>
    <?php 
    if($kind == 'group'){
      if($theaction == 'add' or $theaction == 'modify'){
    ?>
      <input type="text" name="frm_Initial" size="2" maxlength=2 value="<?php echo $frm_Initial;?>"> Max. 2 letters
    <?php }else{?>
     <?php echo $frm_Initial?>
    <?php }
    }else{
      //if($theaction == 'add' or $theaction == 'modify'){
        echo "VS".$frm_Initial;
        echo "<input type=hidden name=frm_Initial value='$frm_Initial'>";
      //}  
    } 
    ?>
      </div> 
    </td>
  </tr>
  <tr>
    <td width=120 bgcolor=#376c65>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>Icon</b>:</font>
    </td>
    <td bgcolor=white>
    <?php 
    if($kind == 'group'){
      echo "<div class=maintext>\n";
      if($frm_Icon){
        echo "<img src='./gel_images/$frm_Icon'>&nbsp;&nbsp;";
        if((!$theaction or $theaction=='changeIcon') and $USER->ID == $frm_UserID ){
          echo "<input type='file' name='frm_Icon' size='32'>\n";
          echo "&nbsp;&nbsp;<input type=\"button\" value=\"Change Icon\" onClick=\"javascript:change_icon(this.form);\">\n";
          echo "<br>Please only upload GIF formatted and size 17x17 pixels image";
        }
      }
      if($theaction == 'add'){
      ?>
        <input type='file' name='frm_Icon' size='32'>
        <?php 
        if($frm_Icon and $theaction=='modify'){?>
        &nbsp;&nbsp;<input type="button" value="Change Icon" onClick="javascript:checkImage(this.form);">
        <?php 
        }
        echo "<br>Please only upload GIF formatted and size 17x17 pixels image";
      }
      echo "</div>\n";
    }else{
    ?>
      <table border="0" cellpadding="0" cellspacing="0" width="17" height="17">
      <tr><td class="tdback_star_image"><?php echo $frm_Initial?></td></tr>
       <input type='hidden' name='frm_Icon' size='32'>
      </table>
    <?php 
    }
    ?>
    </td>
  </tr>
  
   <?php 
   if($NameUser){?>
   <tr>
    <td width=120 bgcolor="#376c65" valign=top>
    <font face="'MS Sans Serif',Geneva,sans-serif" size="2" color="#ffffff"><b>Submitted by</b>:</font>
    </td> 
    <td bgcolor=white>
    <div align="maintext"><?php echo $NameUser;?></div>
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
<?php 
function check_name_initial($frm_Name, $frm_Initial){
  global $HITSDB, $AccessProjectID;
  $SQL = "select ID from NoteType where Initial='$frm_Initial' and Name='$frm_Name' and ProjectID='$AccessProjectID'";
  if($HITSDB->fetch($SQL)){
    return false;
  }else{
    return true;
  }
}
function check_icon($frm_Icon){
  global $_FILES, $icon_folder, $frm_Name, $frm_Icon;
  if($frm_Icon){
    $uploaded_file_name = $frm_Icon;
  }else{
    $uploaded_file_name = "icon_".preg_replace('/[^A-Za-z0-9]/', '', $frm_Name). ".gif";
  }
  $new_pic_name = $icon_folder ."/" . $uploaded_file_name;
  
  if(!$fileAtrArr = getimagesize($_FILES['frm_Icon']['tmp_name'])){
    return false;
  }elseif($fileAtrArr[2] != 1){
    //not a GIF file
    return false;
  }elseif($fileAtrArr[0] > 17 || $fileAtrArr[1] >17){
    //bigger than 17x17.
    return false;
  }
  if(!move_uploaded_file($_FILES['frm_Icon']['tmp_name'], $new_pic_name)){
    if(_is_file($tmpFileName)) rename($tmpFileName,$previousFileFullName);
    return $img_msg = "<font color=#FF0000>Possible file upload attack! Please try again</font>";
  }else{
    if(is_file($new_pic_name)){
      return $uploaded_file_name;
    }else{
      return false;
    }
  }
} 

?>
