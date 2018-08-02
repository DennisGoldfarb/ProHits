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

set_time_limit(2400);
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");


//echo $URLS["EBI_TAGE"]."<br>";

$Log = new Log();
if($this_sign == "[%2B]") $this_sign = '[+]';

if($theaction == 'insert_single_detail' || $theaction == 'update_single_detail'){
  $action = '';
  if($theaction == 'insert_single_detail'){
    $SQL ="INSERT INTO `EpitopeTag` SET 
        Name='".$frm_Name."',
        Description='".mysqli_real_escape_string($HITSDB->link, $frm_Description)."',
        OLSID='".$frm_OLSID."',
        OLSterm='".$frm_OLSterm."',
        Location='".$frm_Location."'";
    if($frm_ID = $PROHITSDB->insert($SQL)){
      $action = 'insert';
    }  
  }elseif($theaction == 'update_single_detail'){
    $SQL = "UPDATE `EpitopeTag` SET 
            Name='".$frm_Name."',
            Description='".mysqli_real_escape_string($HITSDB->link, $frm_Description)."',
            OLSID='".$frm_OLSID."',
            OLSterm='".$frm_OLSterm."',
            Location='".$frm_Location."'
            WHERE ID='".$frm_ID."'";
    if($frm_ID_tmp = $PROHITSDB->update($SQL)){
      $action = 'update';
    }
  }
  if($frm_ID){
    $Desc = "Name=$frm_Name,Description=$frm_Description,OLSID=$frm_OLSID,OLSterm$frm_OLSterm,Location=$frm_Location";
    $Log->insert($AccessUserID,'EpitopeTag',$frm_ID,$action,$Desc,$AccessProjectID);
    $refresh = 1;
  }
  if(is_numeric($frm_ID)){
    $this_sign = '[+]';
    $display_new = 3;
    $theaction = '';
  }    
  
}elseif($theaction == 'delete_single_detail'){
  $SQL = "DELETE FROM `EpitopeTag`  
          WHERE ID = '$frm_ID'";
  $db_ret = $PROHITSDB->execute($SQL);
}

if($theaction == 'show_single_detail'){
  $SQL = "SELECT `ID`,
          `Name`,
          `Description`,
          `OLSID`,
          `OLSterm`,
          `Location` 
          FROM `EpitopeTag`
          WHERE ID='$base_id'";
  $single_EpitopeTag_arr = $PROHITSDB->fetch($SQL);
  if($single_EpitopeTag_arr){
    echo "@@**@@".$base_id."@@**@@";
    print_single_detail($single_EpitopeTag_arr,$theaction);
  }
  exit;
}

$SQL = "SELECT `ID`,`Name`,`Description`,`OLSID`,`OLSterm`,`Location` FROM `EpitopeTag` ORDER BY ID";
$EpitopeTag_arr = $PROHITSDB->fetchAll($SQL);


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
$general_title = "<span class=pop_header_text>Epitope Tags</span>";

?>

<html>
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
A {TEXT-DECORATION: none;}
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
var div_id_arr = new Array();
var exist_tag_name_arr = new Array();
<?php foreach($EpitopeTag_arr as $tag){?>
    exist_tag_name_arr.push("<?php echo $tag['Name']?>");
    div_id_arr.push("<?php echo $tag['ID']?>"); 
<?php }?>
div_id_arr.push("add_new");
  
Array.prototype.in_array = function(p_val) {
	for(var i = 0, l = this.length; i < l; i++) {
		if(this[i] == p_val) {
			return true;
		}
	}
	return false;
}  
  
  
var peptedeW = '';

function close_other_divs(base_id){
  for(var i=0; i<div_id_arr.length; i++){
    if(div_id_arr[i] != base_id){
      var other_base_id = div_id_arr[i];
      var other_lable_id = other_base_id + '_a';
      var other_lable_obj = document.getElementById(other_lable_id);
      var other_base_obj = document.getElementById(other_base_id);
      var other_sign = '[+]';
      other_sign = other_sign.replace('+', '%2B');
      other_lable_obj.innerHTML = '[+]';
      other_base_obj.style.display = "none";
    }
  }
}

function pass_protocol_data(obj_frm,name_id,detail_id,ini_id,icon_id){
  var name_obj = document.getElementById(name_id);
  var detail_obj = document.getElementById(detail_id);
  var ini_obj = document.getElementById(ini_id);
  var icon_obj = document.getElementById(icon_id);
  if(obj_frm == 'Bait'){
    opener.document.Bait_frm.frm_Name.value = name_obj.innerHTML;
  	opener.document.Bait_frm.frm_Description.value = detail_obj.innerHTML;
    opener.document.Bait_frm.frm_Initial.value = ini_obj.innerHTML;
    opener.document.Bait_frm.frm_passed_icon.value = icon_obj.innerHTML;    
  }else if(obj_frm == 'Experiment'){
    opener.document.Experiment_frm.frm_Name.value = name_obj.innerHTML;
  	opener.document.Experiment_frm.frm_Description.value = detail_obj.innerHTML;
    opener.document.Experiment_frm.frm_Initial.value = ini_obj.innerHTML;
    opener.document.Experiment_frm.frm_passed_icon.value = icon_obj.innerHTML;
  }else if(obj_frm == 'Band'){
    opener.document.Band_frm.frm_Name.value = name_obj.innerHTML;
  	opener.document.Band_frm.frm_Description.value = detail_obj.innerHTML;
    opener.document.Band_frm.frm_Initial.value = ini_obj.innerHTML;
    opener.document.Band_frm.frm_passed_icon.value = icon_obj.innerHTML;
  }else if(obj_frm == 'Export'){
    opener.document.Export_frm.frm_Name.value = name_obj.innerHTML;
  	opener.document.Export_frm.frm_Description.value = detail_obj.innerHTML;
  }  
}

function toggle_detail(base_id){
  var selected_obj = document.getElementById(base_id);
  var selected_a_id = base_id + '_a';
  var selected_a_obj = document.getElementById(selected_a_id);
  if(selected_obj.style.display == "none"){
    var inner_str = trimString(selected_obj.innerHTML);
    queryString = "base_id=" + base_id + "&theaction=show_single_detail";
    ajaxPost("<?php echo $PHP_SELF;?>", queryString);
    selected_obj.style.display = "block";
    selected_a_obj.innerHTML = '[-]';
    selected_a_obj.title = 'close details';
  }else{
    selected_obj.style.display = "none";
    selected_a_obj.innerHTML = '[+]';
  }
  close_pop_win();
}

function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 3){
    var obj_id = trimString(ret_html_arr[1]);
    document.getElementById(obj_id).innerHTML = ret_html_arr[2];
    return;
  }
}

function close_pop_win(){
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
}

function close_add_new(add_new_id){
  var new_obj = document.getElementById(add_new_id);
  var error_msg_obj =  document.getElementById('error_msg');
  if(error_msg_obj != null){
    error_msg_obj.innerHTML='';
  }
  new_obj.style.display = "none";
  if(!peptedeW.closed && peptedeW.location) {
    peptedeW.close();
  }
}

function close_modify(modify_id){
  var modified_obj = document.getElementById(modify_id);
  modify_id_a = modify_id + "_a";
  var modified_a_obj = document.getElementById(modify_id_a);
  modified_obj.style.display = "none";
  modified_a_obj.innerHTML = '[+]';
}

function add_new(theForm){
  theForm.frm_Name.value = trimString(theForm.frm_Name.value);
  var base_id = theForm.base_id.value;
  var p_name = theForm.frm_Name.value;
  if(theForm.frm_OLSID.value != '' && !onlyAlphaNumerics(theForm.frm_OLSID.value, 7)){
    alert("Only characters \"%+-_A-Za-z0-9\(\)\.:\" and spaces are allowed.");
    return;
  }
  if(!onlyAlphaNumerics(p_name, 7)){
    alert("Only characters \"%+-_A-Za-z0-9\(\)\.:\" and spaces are allowed.");
    return;
  }
  if(theForm.theaction.value == "update_single_detail"){
    var lable_id = base_id + "_b";
    var modified_obj = document.getElementById(lable_id);
    var lable = trimString(modified_obj.innerHTML);
    var lable_arr = lable.split(";");
    if(lable_arr.length == 1){
      lable = lable_arr[0];
    }else{
      lable = lable_arr[1];
    }
    if(lable != p_name && exist_tag_name_arr.in_array(p_name)){
      alert("The name " + p_name + " has been used.");
      return;
    }
  }else if(theForm.theaction.value == 'insert_single_detail'){
    if(exist_tag_name_arr.in_array(p_name)){
      alert("The name " + p_name + " has been used.");
      return;
    }
  }   
  theForm.submit();
}

function toggle_add_new(base_id){
  var add_new_id = "add_new";
  var new_obj = document.getElementById(add_new_id);
  new_obj.style.display = "block";
  close_other_divs(base_id);
  close_pop_win();
}

function ebi_tag_link(theaction,id,field){
  var ols_id = '';
  if(theaction == 'add_new_single'){
    var theForm = document.add_new_frm;
    if(field == 'OLSID'){
      ols_id = theForm.frm_OLSID.value;
    }else{
      ols_id = theForm.frm_Location.value;
      tem_arr = ols_id.split(' ');
      ols_id = tem_arr[0];
    }
  }else if(theaction == 'modify_single_detail'){
    var theForm = document.modify_frm;
    if(field == 'OLSID'){
      ols_id = theForm.frm_OLSID.value;
    }else{
      ols_id = theForm.frm_Location.value;
      tem_arr = ols_id.split(' ');
      ols_id = tem_arr[0];
    }
  }else{
    ols_id = id;
  }
  ols_id = ols_id.replace(":", "_");
  var EBI_url = "<?php echo $URLS["EBI_TAGE"]?>" + ols_id;
  popwin(EBI_url,1000,800,'new');
}  
//-->
</script>
</head>
<body>
  <table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%>    
    <tr>
      <td valign=top align=center bgcolor="white" width=100%>
        <table border=0 width=90% cellspacing="0" cellpadding=1>
          <tr>
            <td colspan='2' nowrap >&nbsp;&nbsp;</td>
          </tr>
          <tr>
            <td nowrap align=left" height='25'>
              <?php echo $general_title?>
            </td>
            <td nowrap align=right height='25'>
              <a href="javascript: popwin('../doc/Analyst_help.php#faq43', 800, 600, 'help');"><img src='./images/icon_HELP.gif' border=0 ></a>
              <?php if($analyst_this_page_permission_arr['Insert']){?>
                <a href="javascript: toggle_add_new('add_new')"  title='add new'>[add new]</a>&nbsp;&nbsp;
              <?php }?>
            </td>
          </tr>
          <tr>
            <td colspan='2' nowrap align=center height='1'><hr size=1></td>
          </tr>
          <tr>
            <td colspan='2'>
            <DIV id="add_new" STYLE="display: none;border: black solid 1px">
              <?php 
                $tmp_atr_arr = array('Name' => '', 'Description' => '', 'OLSID' => '', 'OLSterm' => '', 'Location' => '');
                print_single_detail($tmp_atr_arr,'add_new_single');
              ?>
            </DIV>
            </td>
          </tr>
          <tr>
            <td colspan='2' >             
              <?php 
              if($this_sign == "[-]"){
                $style = "display: block";
              }else{
                $style = "display: none";
              }  
              ?>
              <table cellspacing='0' cellpadding='1' border='0' align=center width='100%'>
            <?php foreach($EpitopeTag_arr as $tag_val){
                if($display_new){
                  if($tag_val['ID'] == $frm_ID){
                    $this_sign = "[-]";
                    $style = "display: block";
                  }else{
                    $this_sign = "[+]";
                    $style = "display: none";
                  }  
                }         
                
                if($tag_val['ID'] == $selected_id){
                  $this_sign_tmp = "[-]";
                  $style_tmp = "display: block";
                  if($outsite_script){
                    $theaction_tmp = '';
                  }else{
                    $theaction_tmp = 'modify_single_detail';
                  }  
                }else{
                  $this_sign_tmp = $this_sign;
                  $style_tmp = $style;
                  $theaction_tmp = $theaction;
                }
                
                $base_div_id = $tag_val['ID'];
                $div_id_a = $base_div_id.'_a';
                $div_id_b = $base_div_id.'_b';
            ?>
                <tr BGCOLOR='<?php echo $pro_name_bgcolor?>'>
                  <td height='25' align=left nowrap width='50%'>              
                    <span id="<?php echo $div_id_b?>" class=maintext_bold>
                      &nbsp;<?php echo $tag_val['Name']?>
                    </span>  
                  </td>
                  <td align=right width='' nowrap> 
            <?php   if($analyst_this_page_permission_arr['Modify'] && !in_array($tag_val['Name'], $used_tag_arr)){?>
                    <a href="<?php echo $_SERVER['PHP_SELF'];?>?selected_id=<?php echo $base_div_id;?>"  title='modify detail'>
                    <img border="0" src="images/icon_view.gif" alt="Modify">
                    </a>
            <?php     if($analyst_this_page_permission_arr['Delete']  && !in_array($tag_val['Name'], $used_tag_arr)){?>  
                    <a href="<?php echo $PHP_SELF;?>?frm_ID=<?php echo $tag_val['ID']?>&theaction=delete_single_detail"  title='delete detail'>
                    <img border="0" src="images/icon_purge.gif" alt="Delete">
                    </a>
            <?php     }else{?>
                    <img src="images/icon_empty.gif">&nbsp;
            <?php     }?>              
            <?php   }else{?>
                    <img src="images/icon_empty.gif">&nbsp;
                    <img src="images/icon_empty.gif">&nbsp;
            <?php   }?>    
                    <a id='<?php echo $div_id_a?>' href="javascript: toggle_detail('<?php echo $base_div_id?>')"  title='protocol detail'>
                    <?php echo $this_sign_tmp?>
                    </a>    
                  </td> 
                </tr>
                <tr>
                  <td colspan='2'>
                    <div id="<?php echo $base_div_id;?>" STYLE="<?php echo $style_tmp;?>; border: black solid 1px">
             <?php   if($this_sign_tmp == "[-]"){
                    print_single_detail($tag_val,$theaction_tmp);
                 }
             ?>
                    </div>
                  </td>             
                </tr>   
            <?php }?>
            </table> 
            </td>
        </tr>
      </table>
      </td> 
    </tr> 
  </table>
  <center><b>Note:</b> A used tag cannot be modified.</center>
</body>
</html>
<?php 

function print_single_detail($row='',$theaction=''){
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
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext><?php echo str_repeat("&nbsp;", 30);?>ID:&nbsp;&nbsp;</div>
	  </td>
	  <td align="left" colspan='2'>    
      <div class=maintext><?php echo $row['ID'];?>&nbsp;&nbsp;</div>
    </td>
	</tr>
<?php }?>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;Name:&nbsp;</div>
	  </td>
	  <td align="left" bgcolor="<?php echo $bgcolor;?>" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><input type="text" name="frm_Name" size="40" maxlength=39 value=""></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><input type="text" name="frm_Name" size="40" maxlength=39 value="<?php echo $row['Name'];?>"></div>
    <?php }else{?>
        <div class=maintext><?php echo $row['Name'];?></div>
    <?php }?>
    </td>
	</tr>
  
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' valign=top>
	    <div class=maintext>OLSID:&nbsp;</div>
	  </td>
	  <td valign=top align="left" colspan='2'>
    <div class=maintext>
    <?php if($theaction == 'add_new_single' || $theaction == 'modify_single_detail'){?> 
        <input type=text name=frm_OLSID size="28" maxlength=50 value='<?php echo (isset($row['OLSID']))?$row['OLSID']:''?>'>
    <?php }else{?>
        <?php echo $row['OLSID'];?>
    <?php }?>
      &nbsp;&nbsp;<a href="javascript: ebi_tag_link('<?php echo $theaction?>','<?php echo $row['OLSID'];?>','OLSID')">[OLS Lookup]</a>        
    </div>
	  </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' valign=top>
	    <div class=maintext>Location:&nbsp;</div>
	  </td>
	  <td valign=top align="left" colspan='2'>
    <div class=maintext>
    <?php if($theaction == 'add_new_single' || $theaction == 'modify_single_detail'){?>
      <select name="frm_Location">
        <option value="">
        <option value="MI:0340 n-terminal position" <?php echo (($row['Location']=="MI:0340 n-terminal position")?"selected":"")?>>MI:0340 n-terminal position
        <option value="MI:0334 c-terminal position" <?php echo (($row['Location']=="MI:0334 c-terminal position")?"selected":"")?>>MI:0334 c-terminal position
	    </select>
    <?php }else{?>
        <?php echo $row['Location'];?>
    <?php }
      if(!isset($row['Location'])){
        $Location = '';
      }else{
        if(!$row['Location']){
          $Location = '';
        }else{
          $tmp_arr = explode(" ",$row['Location']);
          $Location = $tmp_arr[0];
        }
      }  
    ?>
    &nbsp;&nbsp;<a href="javascript: ebi_tag_link('<?php echo $theaction?>','<?php echo $Location;?>','Location')">[OLS Lookup]</a> 
    </div>
	  </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' valign=top>
	    <div class=maintext>OLSterm:&nbsp;</div>
	  </td>
	  <td valign=top align="left" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><textarea name=frm_OLSterm cols=39 rows=1></textarea></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><textarea name=frm_OLSterm cols=39 rows=1><?php echo $row['OLSterm']?></textarea></div>
    <?php }else{?>
        <div class=maintext><?php echo $row['OLSterm'];?></div>
    <?php }?>
	  </td>
	</tr>
  
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' valign=top>
	    <div class=maintext>Description:&nbsp;</div>
	  </td>
	  <td valign=top align="left" colspan='2'>
    <?php if($theaction == 'add_new_single'){?>  
        <div class=maintext><textarea name=frm_Description cols=39 rows=3></textarea></div>
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <div class=maintext><textarea name=frm_Description cols=39 rows=3><?php echo $row['Description']?></textarea></div>
    <?php }else{?>
        <div class=maintext><?php echo $row['Description'];?></div>
    <?php }?>
	  </td>
	</tr>
  
  <?php if($theaction){?>
  <tr bgcolor="<?php echo $bgcolor;?>">	  
	  <td valign=top colspan=3 align=center>
    <?php if($theaction == 'add_new_single'){?>  
        <input type="button" value="Save" onClick="javascript: add_new(this.form);">&nbsp;
        <input type="reset" value="Reset">&nbsp;
        <input type="button" value="Close" onClick="javascript: close_add_new('add_new');">
    <?php }elseif($theaction == 'modify_single_detail'){?> 
        <input type="button" value="Save" onClick="javascript: add_new(this.form);">
        <input type="reset" value="Reset">&nbsp;
        <input type="button" value="Close" onClick="javascript: close_modify('<?php echo $base_id?>');">
    <?php }?>
	  </td>
	</tr>
 <?php }?> 
<?php if($theaction == 'add_new_single' || $theaction == 'modify_single_detail'){?>
</FORM>
<?php }?>
</table>
<?php 
}
?>           