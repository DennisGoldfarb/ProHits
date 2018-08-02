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
$frm_Type = '';
$frm_Name = '';
$frm_Detail = '';
$frm_date = '';
$refresh = '';
$protocolUser = '';
$protocolUserID = '';
$ProtocolArr = array();
$message = '';
$old_name = '';

require("../common/site_permission.inc.php");

$Log = new Log();

$frm_ProjectID = $AccessProjectID;
if($theaction == 'insert' || $theaction == 'update'){
  $SQL = "SELECT `ID`,`Name` FROM `Protocol` WHERE 
          `Name`='".mysqli_real_escape_string($mainDB->link, $frm_Name)."' 
          AND `Type`='$frm_Type'";
  $tmp_arr = $mainDB->fetch($SQL);       
  if(($theaction == 'insert' && $tmp_arr) || ($theaction == 'update' && isset($tmp_arr['Name']) && $tmp_arr['Name'] != $old_name)){
    $message = "<font color=red size=3>The Protocol Name '$frm_Name' has been used. Please give another name.</font>";
    if($theaction == 'insert') $theaction = 'add';
    if($theaction == 'update') $theaction = 'modify';
  }else{
    if($theaction == 'insert'){
      $SQL ="INSERT INTO Protocol SET 
              Name='".mysqli_real_escape_string($mainDB->link, $frm_Name)."', 
              Type='$frm_Type', 
              ProjectID='$frm_ProjectID',
              Detail='".mysqli_real_escape_string($mainDB->link, $frm_Detail)."',
              UserID='".$USER->ID."',
              Date='".@date("Y-m-d")."'";            
      $Protocol_ID = $mainDB->insert($SQL);
      $theaction = '';
      if($Protocol_ID){
        $Desc = "Name=$frm_Name,Type=$frm_Type,Date=".@date("Y-m-d");
        $refresh = 1;
      }else{
        $Desc = "insert failed";
      }        
      $Log->insert($AccessUserID,'Protocol',$Protocol_ID,'insert',$Desc,$AccessProjectID);
    }else if($theaction == 'update'){
       $SQL ="UPDATE Protocol SET
             Name='".mysqli_real_escape_string($mainDB->link, $frm_Name)."',  
             Type='$frm_Type', 
             ProjectID='$frm_ProjectID', 
             Detail='".mysqli_real_escape_string($mainDB->link, $frm_Detail)."'
             WHERE ID='$Protocol_ID'";
      $updateFlag = $mainDB->update($SQL);
      $theaction = '';
      
      if($updateFlag){
        $Desc = "Name=$frm_Name,Type=$frm_Type,Date=".@date("Y-m-d");
        $refresh = 1;
      }else{
        $Desc = "updat failed";
      }
      $Log->insert($AccessUserID,'Protocol',$Protocol_ID,'modify',$Desc,$AccessProjectID);
    }
  }  
}  

if($theaction == 'add'){
  //$frm_Type = '';
  $frm_Name = '';
  if(!$message){
    $frm_Detail = '';
  }
}else{
  $SQL = "SELECT ID, Name, Type, Detail, UserID, Date  FROM Protocol WHERE ID='$Protocol_ID'";
  $ProtocolArr = $mainDB->fetch($SQL);
  if($ProtocolArr){
    $frm_ID = $ProtocolArr['ID'];
    $frm_Type = $ProtocolArr['Type'];
    $old_name = $ProtocolArr['Name'];
    if(!$message){
      $frm_Name = $ProtocolArr['Name'];
    }else{
       $frm_Name = '';
    }  
    $frm_Detail = $ProtocolArr['Detail'];
    $SQL = "SELECT Fname, Lname from User where ID='".$ProtocolArr['UserID']."'";
    $theUser = $PROHITSDB->fetch($SQL);
    if($theUser){
      $protocolUser = $theUser['Fname']." ".$theUser['Lname'];
    }
    $frm_date = $ProtocolArr['Date'];
  }
}
?>
<html>
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
</head>
<body>
<script language="javascript">
function modify_protocol(theForm){
  theForm.theaction.value = 'modify';
  theForm.submit();
}
function add_protocol(theForm){
  theForm.theaction.value = 'add';
  theForm.Protocol_ID.value= '';
  theForm.submit();
}
function submit_add(theForm){
  if(theForm.frm_Type.value == ''){
    alert("Please select a protocol type");
    return false;
  }
  if(theForm.frm_Name.value == ''){
    alert("Please enter a protocol name");
    return false;
  }
  theForm.theaction.value = 'insert';
  theForm.Protocol_ID.value= '';
  theForm.submit();
}
function submit_modify(theForm){
  if(theForm.frm_Type.value == ''){
    alert("Please select a protocol type");
    return false;
  }
  if(theForm.frm_Name.value == ''){
    alert("Please enter a protocol name");
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
<form name="p_form" method=post action="<?php echo $PHP_SELF;?>">  
<input type=hidden name=theaction value=modify>
<input type=hidden name=Protocol_ID value="<?php echo $Protocol_ID;?>">
<input type=hidden name=frm_Type value="<?php echo $frm_Type;?>">
<input type=hidden name=old_name value="<?php echo $old_name;?>">
<table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%><tr><td valign=top align=center bgcolor="white" width=100%>
<table border="0" cellpadding="0" cellspacing="0" width="95%">
  <tr>
    <?php 
    if(!$theaction or $theaction == 'modify'){
      echo "<td bgcolor=white><br><sapn class=pop_header_text>Protocol Detail</span></td>";
    }
    echo "<td align=right bgcolor=white valign=bottom><a href='javascript: closePop();' class=button>[Close the Window]</font></a></td>";
    ?>
 </tr>
 <tr>
   <td nowrap align=center height='1' colspan=2><hr size=1></td>
</tr> 
 <tr>  
  <td bgcolor=white colspan=2>
  <table border="0" cellpadding="3" cellspacing="1" width="100%">
  <?php 
    if($theaction != 'add' ){
  ?>  
  <tr>
    <td width=120 align=right bgcolor=#e1e1e1>
    <div class=maintext_bold>Protocol ID:</div>
    </td>
    <td valign=top bgcolor=#e1e1e1> 
    <div class="maintext"><?php echo $frm_ID?></div>
    </td>
  </tr> 
  <?php }?> 
  <tr>
    <td width=120 align=right bgcolor=#e1e1e1>
    <div class=maintext_bold>Protocol Type:</div>
    </td>
    <td bgcolor=#e1e1e1><div class=maintext> 
      <?php 
      if($frm_Type=='GrowProtocol'){
        echo "Biological Materia";
      }else if($frm_Type=='DigestProtocol'){
        echo "Affinity Purification";
      }else if($frm_Type=='IpProtocol'){
        echo "Peptide Preparation";
      }else if($frm_Type=='PeptideFrag'){
        echo "LC-MS";
      }
      ?></div>
    </td>
  </tr>
  <tr>
    <td width=120 align=right bgcolor=#e1e1e1>
    <div class=maintext_bold>Protocol Name:</div>
    </td>
    <td valign=top bgcolor=#e1e1e1><div class=maintext> 
    <?php 
    if($theaction == 'add' or $theaction == 'modify'){
    ?>
    <input type="text" name="frm_Name" size="30" maxlength=50 value="<?php echo $frm_Name;?>">
    <?php if($message){
        echo "<br>".$message;
      }?>
    </td>
    <?php }else{?>
    <div align="maintext"><?php echo $frm_Name?>
    <?php }?>
    </div>
     </td>
  </tr>
  <tr>
    <td width=120 align=right bgcolor=#e1e1e1 valign=top>
    <div class=maintext_bold>Protocol Detail:</div>
    </td>
    <td bgcolor=#e1e1e1> <div class=maintext>
    <?php 
    if($theaction == 'add' or $theaction == 'modify'){
    ?>
    <textarea name=frm_Detail cols=50 rows=10><?php echo  $frm_Detail;?></textarea>
    <?php 
    }else{
      echo nl2br(htmlspecialchars($frm_Detail));
    }
    ?>
    </div>
    </td>
   </tr>
   <?php 
   if($protocolUser){?>
   <tr>
    <td width=120 align=right bgcolor=#e1e1e1 valign=top>
    <div class=maintext_bold>Submitted by:</div>
    </td> 
    <td bgcolor=#e1e1e1><div class=maintext>
    <?php echo $protocolUser;?></div>
    </td>
   </tr>
   <?php }?>
   <?php 
   if($frm_date){?>
   <tr>
    <td width=120 align=right bgcolor=#e1e1e1 valign=top>
    <div class=maintext_bold>Date Modified:</div>
    </td> 
    <td bgcolor=#e1e1e1><div class=maintext>
    <?php echo $frm_date;?></div>
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
</td></tr></table>
</BODY>
</html>
