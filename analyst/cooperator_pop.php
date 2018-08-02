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

$type_bgcolor = '#808040';
$pro_name_bgcolor = '#d1d0be';
$general_title_bgcol = '#b1b09e';
$bgcolor = "#f1f1ed";
$error_msg = '';
$is_error = 0;
$this_sign = '[+]';
$modal = '';
$selected_type_div_id = '';
$selected_prot_div_id = '';
$old_Initial = '';
$frm_passed_Icon = '';
$toggle_new = '';

$self_pro_arr = array();
$other_pro_arr = array();
$group_type_arr = array();
$selected_str = '';
$prot_type = '';
$icon_folder = "./gel_images";
$frm_Icon = '';
$display_new = 0;
$outsite_script = '';

$selected_id = '';
$frm_Location = '';

$frm_ID = '';
$cooperator_arr = array();

set_time_limit(2400);
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$Log = new Log();
if($this_sign == "[%2B]") $this_sign = '[+]';

if($theaction == 'insert_single_detail' || $theaction == 'update_single_detail'){
  $action = '';
  if($theaction == 'insert_single_detail'){ 
 
    $SQL ="INSERT INTO `Cooperator` SET 
        FirstName='".$frm_FirstName."',
        LastName='".$frm_LastName."',
        Institute='".$frm_Institute."',
        Email='".$frm_Email."',
        UserID='".$AccessUserID."',
        Date=now()";
//echo "$SQL";
    if($frm_ID = $PROHITSDB->insert($SQL)){
      $action = 'insert';
      $theaction = '';
    }
  } 
  if($theaction == 'update_single_detail'){
    $SQL = "UPDATE `Cooperator` SET 
            FirstName='".$frm_FirstName."',
            LastName='".$frm_LastName."',
            Institute='".$frm_Institute."',
            Email='".$frm_Email."',
            UserID='".$AccessUserID."',
            Date=now()
            WHERE ID='".$frm_ID."'";
    if($frm_ID_tmp = $PROHITSDB->update($SQL)){
      $action = 'update';
      $theaction = '';
    }
  }
}

//echo "\$frm_ID=$frm_ID<br>";

if($theaction != 'add_new_single' && $frm_ID){
  $cooperator_arr = array();
  $SQL = "SELECT `ID`, `FirstName`, `LastName`, `Email`, `Institute`, `UserID`, `Date` FROM `Cooperator`
          WHERE ID='$frm_ID'";
  $cooperator_arr = $PROHITSDB->fetch($SQL);
}

$HITS_DB_obj_arr = array();
$used_tag_arr = array();              
foreach($HITS_DB as $DB_key => $DB_name){
  $HITS_DB_obj_arr[$DB_key] = new mysqlDB($DB_name);
  $SQL = "SELECT `Tag` FROM `Bait` GROUP BY `Tag`";
  $tmp_tag_arr = $HITS_DB_obj_arr[$DB_key]->fetchAll($SQL);
  foreach($tmp_tag_arr as $tmp_tag_val){
    if(!$tmp_tag_val) continue;
    if(!in_array($tmp_tag_val['Tag'], $used_tag_arr))  array_push($used_tag_arr, $tmp_tag_val['Tag']); 
  }
}
$general_title = "<span class=pop_header_text>Collaborator</span>";
?>
<html>
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>
<!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
//============================================================================

function add_new(theForm){
  //alert(theForm.name);
  var firstName = trimString(theForm.frm_FirstName.value);
  var lastName = trimString(theForm.frm_LastName.value);
  if(!firstName){
    alert("Please enter your first name.");
    return;
  }
  if(!lastName){
    alert("Please enter your last name.");
    return;
  }
  theForm.submit();
}
function closeWIN(){
  window.opener.location.reload();
  window.close();
}
//==============================================================================

//-->
</script>
</head>
<body>
  <table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%>    
    <tr>
      <td valign=top align=center bgcolor="white" width=100%>
        <table border=0 width=95% cellspacing="0" cellpadding=1>
          <tr>
            <td colspan='2' nowrap >&nbsp;&nbsp;</td>
          </tr>
          <tr>
            <td nowrap align='left' height='25'>
              <?php echo $general_title?>
            </td>
            <td nowrap align='right' height='25'>
              <?php if(!$theaction && $frm_ID){?>
                  <a href="<?php echo $_SERVER['PHP_SELF'];?>?theaction=add_new_single" class=button>[add new]</a>
                  <?php if($cooperator_arr['UserID'] == $AccessUserID || $AccessUserType == 'Admin'){?>
                    <a href="<?php echo $_SERVER['PHP_SELF'];?>?theaction=modify_single_detail&frm_ID=<?php echo $frm_ID?>" class=button>[modify]</a>
                  <?php }?>
              <?php }elseif($theaction && $frm_ID){?>
                  <a href="<?php echo $_SERVER['PHP_SELF'];?>">[add new]</a>
              <?php }?>
            </td>
          </tr>
          
          <tr>
            <td colspan='2'>
            <DIV id="add_new" STYLE="display: block;border: #b3b3d9 solid 1px;padding: 0px 0px 0px 0px;margin: 0px 0px 20px 0px" >
              <?php print_single_detail($cooperator_arr,$theaction);?>
            </DIV>
            </td>
          </tr>
        </table>
      </td> 
    </tr> 
  </table>
</body>
</html>
<?php if(!$theaction && $frm_ID){?>
<!--script language="JavaScript" type="text/javascript">
window.opener.location.reload();
</script-->
<?php 
}
function print_single_detail($row='',$theaction=''){
  global $USER;
 
//echo "\$theaction=$theaction<br>";
  if(!$row) $theaction = 'add_new_single';
  global $bgcolor,$error_msg,$selected_type_div_id,$frm_Location;
  $user_name = '';
?>
<table cellspacing='1' cellpadding='1' border='0' align=center width='99%'>
<?php if($theaction == 'add_new_single'){
    $base_id = '';
?>
  <FORM ID='add_new_frm' NAME='add_new_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
  <input type='hidden' name='theaction' value='insert_single_detail'>
  <input type='hidden' name='frm_ID' value=''>
<?php }elseif($theaction == 'modify_single_detail'){
    $base_id = $row['ID'];
?>
  <FORM NAME='modify_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
  <input type='hidden' name='theaction' value='update_single_detail'>
  <input type='hidden' name='frm_ID' value='<?php echo $row['ID']?>'>
<?php }?>
  <input type='hidden' name='base_id' value='<?php echo $base_id;?>'>
<?php if($theaction != 'add_new_single'){?>
  <tr bgcolor="<?php echo $bgcolor;?>" height='20'>
	  <td align="right" width='20%' nowrap>
	    <div class=maintext><?php echo str_repeat("&nbsp;", 30);?>ID:&nbsp;&nbsp;</div>
	  </td>
	  <td align="left" colspan='2'>    
      <div class=maintext><?php echo $row['ID'];?>&nbsp;&nbsp;</div>
    </td>
	</tr>
<?php }?>
  <tr bgcolor="<?php echo $bgcolor;?>" height='20'>
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;First Name:&nbsp;</div>
	  </td>
	  <td align="left" bgcolor="<?php echo $bgcolor;?>" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><input type="text" name="frm_FirstName" size="40" maxlength=39 value=""></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><input type="text" name="frm_FirstName" size="40" maxlength=39 value="<?php echo $row['FirstName'];?>"></div>
    <?php }else{?>
        <div class=maintext><?php echo $row['FirstName'];?></div>
    <?php }?>
    </td>
	</tr>
  
  <tr bgcolor="<?php echo $bgcolor;?>" height='20'>
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;Last Name:&nbsp;</div>
	  </td>
	  <td align="left" bgcolor="<?php echo $bgcolor;?>" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><input type="text" name="frm_LastName" size="40" maxlength=39 value=""></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><input type="text" name="frm_LastName" size="40" maxlength=39 value="<?php echo $row['LastName'];?>"></div>
    <?php }else{?>
        <div class=maintext><?php echo $row['LastName'];?></div>
    <?php }?>
    </td>
	</tr>
  
  <tr bgcolor="<?php echo $bgcolor;?>" height='20'>
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;Institute:&nbsp;</div>
	  </td>
	  <td align="left" bgcolor="<?php echo $bgcolor;?>" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><input type="text" name="frm_Institute" size="40" maxlength=39 value=""></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><input type="text" name="frm_Institute" size="40" maxlength=39 value="<?php echo $row['Institute'];?>"></div>
    <?php }else{?>
        <div class=maintext><?php echo $row['Institute'];?></div>
    <?php }?>
    </td>
	</tr>
  
  <tr bgcolor="<?php echo $bgcolor;?>" height='20'>
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;Email:&nbsp;</div>
	  </td>
	  <td align="left" bgcolor="<?php echo $bgcolor;?>" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><input type="text" name="frm_Email" size="40" maxlength=39 value=""></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><input type="text" name="frm_Email" size="40" maxlength=39 value="<?php echo $row['Email'];?>"></div>
    <?php }else{?>
        <div class=maintext><?php echo $row['Email'];?></div>
    <?php }?>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>" height='20'>	  
	  <td valign=top colspan=3 align=center>
    <?php if($theaction == 'add_new_single' and $USER->Type== 'Admin'){?>  
        <input type="button" value="Save" onClick="javascript: add_new(this.form);">&nbsp;
        <input type="reset" value="Reset">&nbsp;
        <!--input type="button" value="Close" onClick="javascript: close_add_new('add_new');"-->
    <?php }elseif($theaction == 'modify_single_detail' and $USER->Type== 'Admin'){
    
    ?> 
        <input type="button" value="Save" onClick="javascript: add_new(this.form);">
        <input type="reset" value="Reset">&nbsp;
        <!--input type="button" value="Close" onClick="javascript: close_modify('<?php echo $base_id?>');"-->
    <?php }?>
        <input type="button" value="Close" onClick="javascript: closeWIN();">
	  </td>
	</tr>
<?php if($theaction == 'add_new_single' || $theaction == 'modify_single_detail'){?>
</FORM>
<?php }?>
</table>
<?php 
}
?>           