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
set_time_limit(2400);
$ret_div_id = 1;
$Selected_option_str = '';
$ParentID = '';
$td_b_color = "#f8f8fc";
$new_w_td_bcolor = '#d9d9b3';
$new_w_tr_bcolor = '#e7e7cf'; 
$selected_td_bcolor = '#eeeeee';
$message = '';
$send_from = '';
$browser_v = '';
$for_search = false;
$edit_only = 0;

require("../common/site_permission.inc.php");
include("analyst/common_functions.inc.php");

$remove_action = 0;

if($theaction == 'delete_option'){
  if($ParentID == '0'){
    $SQL = "DELETE FROM ExpDetailProject 
          WHERE SelectionID = '$OptionID'
          AND ProjectID='$AccessProjectID'";
    $PROHITSDB->execute($SQL);
    
    $SQL = "SELECT 
            `SelectionID` 
            FROM `ExpDetailProject` 
            WHERE `SelectionID`='$OptionID'";
    $tmp_arr = $PROHITSDB->fetchALL($SQL);
    if(!count($tmp_arr)){
      $SQL = "DELETE FROM ExpDetailName WHERE ParentID = '$OptionID'";
      $PROHITSDB->execute($SQL);
      $SQL = "DELETE FROM ExpDetailName WHERE ID = '$OptionID'";
      $PROHITSDB->execute($SQL);
    }
  }else{
    $SQL = "DELETE FROM ExpDetailName WHERE ID = '$OptionID'";
    $PROHITSDB->execute($SQL);
  }
}elseif($theaction == 'save_sele_other_obj'){  
  $SQL = "INSERT INTO ExpDetailProject SET
          SelectionID = '$checked_s_id',
          ProjectID = '$AccessProjectID',
          UserID = '$AccessUserID',
          DT='".@date('Y-m-j')."'";
  $new_selection_id = $PROHITSDB->insert($SQL);
}elseif($theaction == 'save_new_option'){
  $SQL = "SELECT `Name` FROM `ExpDetailName` WHERE `ParentID`='$ParentID'";
  $tmp_name_arr = $PROHITSDB->fetchAll($SQL);
  $name_used = 0;
  
  foreach($tmp_name_arr as $tmp_name_val){
    if(strtoupper($tmp_name_val['Name']) == strtoupper($frm_new_option_name)){
      $name_used = 1;
      break;
    }
  }
  if(!$name_used){
    $SQL = "INSERT INTO ExpDetailName SET
            ParentID='$ParentID',
            Name='$frm_new_option_name',
            UserID='$AccessUserID',
            DT='".@date('Y-m-j')."'";
    $new_selection_id = $PROHITSDB->insert($SQL);
    if($ParentID == '0' && $new_selection_id){
      $SQL = "INSERT INTO ExpDetailProject SET
              SelectionID ='$new_selection_id',
              ProjectID ='$AccessProjectID',
              UserID='$AccessUserID'";
      $PROHITSDB->insert($SQL);
    }  
    //$ParentID = '0';
  }else{
    $message = "<font size=2 color=red>The item '$frm_new_option_name' has been used. Please enter other items</font>";
  }  
}elseif($theaction == 'send_option'){
  $added_str = $s_o_id.",,".$s_o_name;
  $tmp_s_o_id_arr = explode("_",$s_o_id);
  $addec_s_id = $tmp_s_o_id_arr[0];
  
  if(!$Selected_option_str){
    $Selected_option_str = $added_str;
  }else{
    $tmpArr = explode("@@",$Selected_option_str);   
    $Selected_option_str = '';
    $update_flag = 0;
    foreach($tmpArr as $tmpVal){
      $tmpArr_2 = explode("_",$tmpVal);
      if($tmpArr_2[0] == $addec_s_id){
        $update_flag = 1;
        if(count($tmp_s_o_id_arr) == 1){
          continue;
        }else{
          if($Selected_option_str) $Selected_option_str .= "@@";
          $Selected_option_str .= $added_str;
          continue;
        }  
      }
      if($Selected_option_str) $Selected_option_str .= "@@";
      $Selected_option_str .= $tmpVal;
    }
    if(!$update_flag){
      $Selected_option_str .= "@@".$added_str;
    }  
  } 
}elseif($theaction == 'move_up'){
  $added_str = $s_o_item;
  $tmpArr = explode("@@",$Selected_option_str);
  $Selected_option_str = '';
  $tmp_index = -1;
  for($i=count($tmpArr)-1; $i>=0; $i--){
    if($tmpArr[$i] == $added_str){
      $tmp_index = $i;
      continue;
    }
    if($tmp_index-2 == $i){
      if($Selected_option_str) $Selected_option_str .= "@@";
      $Selected_option_str .= $tmpArr[$tmp_index];
    }    
    if($Selected_option_str) $Selected_option_str .= "@@";
    $Selected_option_str .= $tmpArr[$i];
  }
  if($tmp_index == 1){
    if($Selected_option_str) $Selected_option_str .= "@@";
    $Selected_option_str .= $tmpArr[$tmp_index];
  }elseif($tmp_index === 0){
    if($Selected_option_str){
      $Selected_option_str = $tmpArr[$tmp_index]."@@".$Selected_option_str;
    }else{
      $Selected_option_str = $tmpArr[$tmp_index];
    }
  }
  $tmpArr2 = explode("@@",$Selected_option_str);
  $tmpArr2 = array_reverse($tmpArr2);
  $Selected_option_str = implode("@@", $tmpArr2);
  
}elseif($theaction == 'move_down'){
  $added_str = $s_o_item;
  $tmpArr = explode("@@",$Selected_option_str);
  $Selected_option_str = '';
  $tmp_index = -1000;
  for($i=0; $i<count($tmpArr); $i++){
    if($tmpArr[$i] == $added_str){
      $tmp_index = $i;
      continue;
    }
    if($tmp_index+2 == $i){
      if($Selected_option_str) $Selected_option_str .= "@@";
      $Selected_option_str .= $tmpArr[$tmp_index];
    }    
    if($Selected_option_str) $Selected_option_str .= "@@";
    $Selected_option_str .= $tmpArr[$i];
  }
  if($tmp_index == count($tmpArr)- 2){
    if($Selected_option_str) $Selected_option_str .= "@@";
    $Selected_option_str .= $tmpArr[$tmp_index];
  }elseif($tmp_index == count($tmpArr)- 1){
    if($Selected_option_str){
      $Selected_option_str = $tmpArr[$tmp_index]."@@".$Selected_option_str;
    }else{
      $Selected_option_str = $tmpArr[$tmp_index];
    }
  }
}elseif($theaction == 'remove'){
  $added_str = $s_o_item;
  $tmpArr = explode("@@",$Selected_option_str);
  $Selected_option_str = '';
  for($i=0; $i<count($tmpArr); $i++){
    if($tmpArr[$i] == $added_str){
      continue;
    }
    if($Selected_option_str) $Selected_option_str .= "@@";
    $Selected_option_str .= $tmpArr[$i];
  }
  $remove_action = 1;
}
//---------------------------------------------------------------------
$Selected_option_arr = array();
$selection_d_arr = array();
if($Selected_option_str){
  $tmpArr_1 = explode("@@",$Selected_option_str);
  foreach($tmpArr_1 as $tmpKey_1 => $tmpVal_1){
    $tmpArr_2 = explode(",,",$tmpVal_1);
    $Selected_option_arr[$tmpArr_2[0]] = $tmpArr_2[1];
    $tmpArr3 = explode("_",$tmpArr_2[0]);
    $selection_d_arr[$tmpArr3[0]] = array();
  }
}

$SQL = "SELECT `SelectionID` FROM `ExpDetailProject` WHERE `ProjectID`='$AccessProjectID'";
$pro_selections_arr = array();
if($tmp_sql_arr = $PROHITSDB->fetchAll($SQL)){
  foreach($tmp_sql_arr as $tmp_sql_val){
    array_push($pro_selections_arr, $tmp_sql_val['SelectionID']);
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
.sss { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt; white-space: nowrap; text-align: left}
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
A {TEXT-DECORATION: none;}
.st1 {
  display: block;
  border: blue solid 1px;
  /*width: 200px;*/
  -moz-opacity: 1.0;
  color: black;
  background-color: white;
}
.st2 {
  display: block;
  border: #808080 solid 1px;
  /*width: 200px;*/
  -moz-opacity: 1.0;
  color: black;
  background-color: white
}
.st3 {
  display: block;
  border: #808080 solid 1px;
  /*width: 200px;*/
  -moz-opacity: 1.0;
  color: black;
  background-color: <?php echo $selected_td_bcolor?>
}
</STYLE>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script language="JavaScript" type="text/javascript">
<!--
function add_new_selection(theaction,s_edit_sign){
  var theForm = document.getElementById("exp_edit_frm");
  if(s_edit_sign == "+"){
    theForm.theaction.value = theaction;
    theForm.ParentID.value = 0;
  }else{
    theForm.ParentID.value = '';
  }  
  theForm.submit();
}
function add_new_option(theaction,s_id,s_name,edit_sign){
  var theForm = document.getElementById("exp_edit_frm");
//alert(theForm.Selected_option_str.value);
  if(edit_sign == "+"){
    theForm.ParentID.value = s_id;
    theForm.ParentName.value = s_name;
  }else{
    theForm.ParentID.value = '';
  }
  theForm.theaction.value = theaction;  
  theForm.submit();
}
function save_new_option(theaction,ParentID){
  var theForm = document.getElementById("exp_edit_frm");
  if(trim(theForm.frm_new_option_name.value) == ''){
    alert("Please enter a item.");
    return;
  }else if(!onlyAlphaNumerics(theForm.frm_new_option_name.value,7)){
    alert("Only characters \"%+-_A-Za-z0-9\(\)\.:\" and spaces are allowed.");
    return;
  }
  theForm.ParentID.value = ParentID;
  theForm.theaction.value = theaction;
  theForm.submit();
}
function save_sele_other_obj(obj){
  var theForm = document.getElementById("exp_edit_frm");
  if(obj.checked == true){
    theForm.checked_s_id.value = obj.value;
    theForm.ParentID.value = 0;
    theForm.theaction.value = 'save_sele_other_obj';
    theForm.submit();
  }
}
function delete_option(d_type,selection_id,option_id){
  if(is_selected_item(d_type,selection_id,option_id)){
    var theForm = document.getElementById("exp_edit_frm");
    theForm.theaction.value = 'delete_option';
    if(d_type == 'o_type'){
      theForm.OptionID.value = option_id;
      theForm.ParentID.value = selection_id;
    }else{
      theForm.OptionID.value = selection_id;
      theForm.ParentID.value = "0";
    }
    theForm.submit();
  }else{
    alert("Please remove item in the Selected Options list first");
  }  
}
function is_selected_item(d_type,selection_id,option_id){

  var theForm = document.getElementById("exp_edit_frm");
  var selected_str = theForm.Selected_option_str.value;
  var tmp_arr_1 = selected_str.split("@@");
  var ids_arr = new Array();
  for(var i=0; i<tmp_arr_1.length; i++){
    var tmp_arr_2 = tmp_arr_1[i].split(",,");
    ids_arr[i] = tmp_arr_2[0];
  }
  var s_o_id = selection_id+"_"+option_id;
  for(var i=0; i<ids_arr.length; i++){
    if(d_type == 'o_type'){
      if(ids_arr[i] == s_o_id){
        return false;
      }
    }else{
      var tmp_arr_3 = ids_arr[i].split('_');
      if(tmp_arr_3[0] == selection_id){
        return false;
      }
    }
  }
  return true;
}

function send_option(this_item,selection_name){
  var theForm = document.getElementById("exp_edit_frm");
  if(this_item.value != ''){
    theForm.s_o_id.value = this_item.value;
    var s_obj = document.getElementById(this_item.id);
    var option_name = s_obj.options[s_obj.selectedIndex].text;
    theForm.s_o_name.value = selection_name+";;"+option_name;
    theForm.theaction.value = 'send_option';
    theForm.submit();
  }  
}
function move_item(theaction,this_item){
  var theForm = document.getElementById("exp_edit_frm");
  theForm.theaction.value = theaction;
  theForm.s_o_item.value = this_item;
  theForm.submit();
}
function close_add_section(){
  var theForm = document.getElementById("exp_edit_frm");
  theForm.ParentID.value = '';
  theForm.submit();
}
function pass_data(){
  var theForm = document.getElementById("exp_edit_frm");
  var pass_str = "<table cellspacing='0' cellpadding='1' border='0' align=center width='100%'><tbody>";
  var pass_str_advanced_search = '';
  var tmp_str = theForm.Selected_option_str.value;
  if(tmp_str != ''){ 
    var tmp_arr = tmp_str.split("@@");
    for(var i=0; i<tmp_arr.length; i++){
      var tmp_arr_2 = tmp_arr[i].split(",,");
      var tmp_arr_3 = tmp_arr_2[1].split(";;");
      pass_str += "<tr bgcolor='#d3d3d3'><td align='right' width='29%'><div class=maintext>"+tmp_arr_3[0]+":&nbsp;</div></td><td vlign=top bgcolor='#e3e3e3'><div class=maintext>&nbsp;&nbsp;"+tmp_arr_3[1]+"</div></td></tr>";
      if(pass_str_advanced_search){
        pass_str_advanced_search += "\n";
      }
      pass_str_advanced_search += tmp_arr_3[0] + " (" + tmp_arr_3[1] + ")";
    }
  }  
  pass_str += "</tbody></table>";
  <?php if($for_search){?>
    var sel = theForm.frm_and_or;
    var and_or = sel.options[sel.selectedIndex].value;
   
    pass_str_advanced_search = pass_str_advanced_search.replace(/\n/g, ' ' + and_or + "\n");
    opener.document.getElementById('frm_expDetail_str').value = theForm.Selected_option_str.value + "====" + and_or;
    opener.document.getElementById('frm_expDetail_dis').value = pass_str_advanced_search;
  <?php 
  }else{
  ?>
      opener.document.add_modify_form.Selected_option_str.value = theForm.Selected_option_str.value;
      opener.document.getElementById('condition_data').innerHTML = pass_str;
  <?php }?>
  window.close();
}
//-->
</script>
</head>
<!--body onload="initial_interface();"-->
<body>
<FORM NAME='exp_edit_frm' ID='exp_edit_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
  <input TYPE="hidden" NAME="Selected_option_str" VALUE="<?php echo $Selected_option_str?>">
  <input TYPE="hidden" NAME="theaction" VALUE="">
  <input TYPE="hidden" NAME="Exp_ID" VALUE="<?php echo $Exp_ID?>">
  <input TYPE="hidden" NAME="ParentID" VALUE="">
  <input TYPE="hidden" NAME="ParentName" VALUE="">
  <input TYPE="hidden" NAME="s_o_id" VALUE="">
  <input TYPE="hidden" NAME="s_o_name" VALUE="">
  <input TYPE="hidden" NAME="s_o_item" VALUE="">
  <input TYPE="hidden" NAME="OptionID" VALUE="">
  <input TYPE="hidden" NAME="browser_v" VALUE="<?php echo $browser_v?>">
  <input TYPE="hidden" NAME="checked_s_id" VALUE="">
  <input TYPE="hidden" NAME="for_search" VALUE="<?php echo $for_search;?>">
  <input TYPE="hidden" NAME="edit_only" VALUE="<?php echo $edit_only;?>">
  <table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%>    
    <tr>
      <td valign=top align=center bgcolor="#f8f8fc" width=60%>
        <table border=0 width=95% cellspacing="0" cellpadding=0>
          <tr>
            <td id = 'columns_tital' nowrap><br><span class=pop_header_text>Controlled Vocabularies</span> (Experimental Details)</td>
            <td><a href="javascript: popwin('../doc/Analyst_help.php#faq39', 800, 600, 'help');">
                <b><font color="#000000"><img src='./images/icon_HELP.gif' border=0 ></font></b></a>  </td>
          </tr>
          <tr>
            <td colspan='2' nowrap align=center height='1'><hr size=1></td>
          </tr> 
      <?php 
      if($for_search){
        echo "<tr height=20><td colspan='2' nowrap align=left>&nbsp;</td></tr>\n";
      }else{
        if($USER->Type == 'Admin'){
          $s_edit_sign = '+';
          $s_title_str = 'Open editing window';
          if($ParentID == '0'){
            $s_edit_sign = '-';
            $s_title_str = 'Close editing window';
          }          
      ?>  <tr height=20><td colspan='2' nowrap align=left>Click "+" to add new selection or option</td></tr>
          <tr height=20>
            <td id = 'columns_tital' colspan='2' nowrap valign=top align=right>
          <b>Edit selection</b>&nbsp;<a title='<?php echo $s_title_str?>' href="javascript: add_new_selection('add_new_selection','<?php echo $s_edit_sign?>')" >[<?php echo $s_edit_sign?>]</a>&nbsp;
            </td>
          </tr>  
      <?php }else{?>
          <tr height=20>
          <td colspan='2' nowrap valign=top align=center height='25'>
          Please contact Prohits admin if you want to add new selection or option.
          </td>
          </tr>  
      <?php }
      }
      ?> 
          
      <?php if($ParentID == '0'){?>
          <tr>
            <td align=center >
            <DIV id="add_selection" class="st1">       
            <table border=0 width=100% cellspacing="0" cellpadding=0>
          <?php 
            $exist_selection_arr = array();
            $exist_option_arr = array();
            $SQL = "SELECT 
                    P.SelectionID AS ID,
                    N.Name
                    FROM ExpDetailProject P
                    LEFT JOIN ExpDetailName N ON P.SelectionID=N.ID
                    WHERE ProjectID='$AccessProjectID'";
            $option_d_arr = $PROHITSDB->fetchAll($SQL);
            
            foreach($option_d_arr as $option_d_val){
              $SQL = "SELECT `ID` FROM `Experiment` WHERE `ProjectID`='$AccessProjectID'";
              $tmp_arr = $HITSDB->fetchAll($SQL);
              $tmp_exp_id_arr = array();
              foreach($tmp_arr as $tmp_val){
                array_push($tmp_exp_id_arr, $tmp_val['ID']);
              }
              $tmp_exp_id_str = implode(",", $tmp_exp_id_arr);
              if($tmp_exp_id_str){
                $SQL = "SELECT `SelectionID` FROM `ExpDetail` WHERE `ExpID` IN ($tmp_exp_id_str) GROUP BY `SelectionID`";
                $tmp_arr_2 = $HITSDB->fetchAll($SQL);
                foreach($tmp_arr_2 as $tmp_val_2){
                  array_push($exist_option_arr, $tmp_val_2['SelectionID']);
                }
              }
            }
                        
            ?> 
              <tr bgcolor='<?php echo $selected_td_bcolor?>' height='20'>
                <td width=20% align=right valign=top bgcolor='<?php echo $selected_td_bcolor?>' nowrap>
                  <div class=maintext>&nbsp;&nbsp;<b>Add new selection&nbsp;&nbsp;</b></div>
                </td>
                <td nowrap width=90% colspan=2 >
                  <table border=0 width=100% cellspacing="1" cellpadding=1 >
                <?php foreach($option_d_arr as $option_d_val){
                    array_push($exist_selection_arr, $option_d_val['ID']);
                ?>
                    <tr bgcolor='<?php echo $selected_td_bcolor?>' height='20'>
                      <td align='left' nowrap><div class=maintext>&nbsp;<?php echo $option_d_val['Name']?></div></td>
                  <?php if($analyst_this_page_permission_arr['Delete'] && !in_array($option_d_val['ID'], $exist_option_arr)){?>
                      <td width=10 nowrap>
                        <div class=maintext>
                        <a href="javascript: delete_option('s_type','<?php echo $option_d_val['ID']?>','')" title=delete>       
                        <img border="0" src="images/icon_delete_option.gif" alt="Delete">
                        </a>
                        </div>
                      </td>
                  <?php }else{?>
                      <td nowrap><div class=maintext>&nbsp;</div></td>
                  <?php }?>     
                    </tr>   
                <?php }
                  $s_text_len = 36;
                  if($browser_v == 'Nav')  $s_text_len = 37;
                ?>
                
                <?php if($analyst_this_page_permission_arr['Insert']){?>
                    <tr BGCOLOR='<?php echo $selected_td_bcolor?>'>
                      <td nowrap><div class=maintext>&nbsp;<input type="text" name="frm_new_option_name" size="<?php echo $s_text_len?>" maxlength=50 value=""></div></td>
                      <td nowrap><input type="button" value=" Add " onClick="javascript: save_new_option('save_new_option','0')"?></td>        
                    </tr>
                <?php }?>                     
                <?php if($message){
                    echo "<tr><td colspan=2 BGCOLOR='$selected_td_bcolor'>$message</td></tr>";
                  }                
                ?>      
                  </table>
                </td>
              </tr> 
            </table>
            </DIV><br>
            </td>
          </tr>
          <tr><td align=left><div class=maintext>&nbsp;<b>Import selections from other objects</b></div></td></tr>
          <tr>
            <td align=center >
            <DIV id="other_obj_selection" class="st1">       
            <table border=0 width=100% cellspacing="1" cellpadding=1>
          <?php 
            $project_id_name_arr = get_project_id_name_arr();
            $user_id_name_arr = get_users_ID_Name($PROHITSDB);
            $WHERE = '';
            if($exist_selection_arr){
              $exist_selection_str = implode(",", $exist_selection_arr);
              $WHERE = " WHERE P.SelectionID NOT IN ($exist_selection_str) ";
            }
            $SQL = "SELECT 
                    P.SelectionID,
                    P.ProjectID,
                    P.UserID,
                    N.Name
                    FROM ExpDetailProject P
                    LEFT JOIN ExpDetailName N
                    ON P.SelectionID=N.ID
                    $WHERE
                    ORDER BY P.DT";
            $other_selections_arr = $PROHITSDB->fetchAll($SQL);
          ?> 
              <tr bgcolor='<?php echo $selected_td_bcolor?>'>
                <td width='' align=center nowrap>
                  <div class=maintext><b>Selection</b></div>
                </td>
                <td width='' align=center nowrap>
                  <div class=maintext><b>Project</b></div>
                </td>
                <td width='' align=center nowrap>
                  <div class=maintext><b>User</b></div>
                </td>
                <td width='' align=center nowrap>
                  <div class=maintext>&nbsp;</div>
                </td>
              </tr>
          <?php 
            $unique_selections_arr = array();
            foreach($other_selections_arr as $other_selections_val){
              if(in_array($other_selections_val['SelectionID'], $unique_selections_arr)) continue;
              array_push($unique_selections_arr, $other_selections_val['SelectionID']);
              $project_name = '';
              $user_name = '';
              if(array_key_exists($other_selections_val['ProjectID'], $project_id_name_arr)) $project_name = $project_id_name_arr[$other_selections_val['ProjectID']];
              if(array_key_exists($other_selections_val['UserID'], $user_id_name_arr)) $user_name = $user_id_name_arr[$other_selections_val['UserID']];
              $check_b_name = 'frm_check_'.$other_selections_val['SelectionID'];
              $check_b_value = $other_selections_val['SelectionID'];
              $check_b_checked = '';
              if(isset($$check_b_name)) $check_b_checked = 'checked';
          ?>  
              <tr bgcolor='<?php echo $selected_td_bcolor?>'>
                <td width='' nowrap align=left>
                  <div class=maintext>&nbsp;<?php echo $other_selections_val['Name']?></div>
                </td>
                <td width='' nowrap align=left>
                  <div class=maintext>&nbsp;<?php echo $project_name?></div>
                </td>
                <td width='' nowrap align=left>
                  <div class=maintext>&nbsp;<?php echo $user_name?></div>
                </td>
                <td width=2% nowrap align=left>
                  <div class=maintext>
                  &nbsp;<input type="checkbox" name="<?php echo $check_b_name?>" value="<?php echo $check_b_value?>" onclick="javascript: save_sele_other_obj(this)" <?php echo $check_b_checked?>>
                  </div>
                </td>
              </tr>
          <?php }?>
               
            </table>
            </DIV><br>
            </td>
          </tr>
      <?php }else{?>  
          <tr>
          <td width="" align=center>
            <DIV id="selections" class="st2">
            <table border=0 width=100% cellspacing="3" cellpadding=3>
          <?php           
            $SQL = "SELECT `ID`,`Name` FROM `ExpDetailName` WHERE `ParentID`=0";
            if($selection_d_arr_tmp = $PROHITSDB->fetchAll($SQL)){
              foreach($selection_d_arr_tmp as $s_val){
                if(!in_array($s_val['ID'], $pro_selections_arr)) continue;
                $selection_d_arr[$s_val['ID']] = $s_val;
              }
              foreach($selection_d_arr as $selection_d_val){
                $SQL = "SELECT `ID`,`Name` FROM `ExpDetailName` WHERE `ParentID`='".$selection_d_val['ID']."'";
                $option_d_arr = $PROHITSDB->fetchAll($SQL);
                $num_options = count($option_d_arr);
                if($selection_d_val['ID'] == $ParentID) echo "<tr><td>&nbsp;</td><td></td></tr>";
                $s_len = 56;
                if($browser_v == 'Nav')  $s_len = 75;    
          ?> 
                <tr>
                  <td align=left valign=top><div class=maintext>&nbsp;
                     <b><?php echo $selection_d_val['Name']?></b></div>
                  </td>
                  <td nowrap width='200'>
                    <div class=maintext>
               <?php if(!$edit_only){?>   
                    <select id="s_<?php echo $selection_d_val['ID']?>" name="s_<?php echo $selection_d_val['ID']?>" onChange="send_option(this,'<?php echo $selection_d_val['Name']?>');">
                      <option value="<?php echo $selection_d_val['ID']?>"><?php echo str_repeat("&nbsp;",$s_len)?>
                  <?php 
                    foreach($option_d_arr as $option_d_val){
                      $tmp_key = $selection_d_val['ID']."_".$option_d_val['ID'];
                      $op_selected = '';
                      if(array_key_exists($tmp_key, $Selected_option_arr)) $op_selected = "selected";
                      echo "<option value='$tmp_key' $op_selected>".$option_d_val['Name']."\n";
                    }
                  ?>    
                 	  </select>
               <?php }?>     
                    </div>
                  </td>
                <?php if($USER->Type == 'Admin' and !$for_search){
                    $edit_sign = '+';
                    $title_str = 'Open editing window';
                    if($selection_d_val['ID'] == $ParentID){
                      $edit_sign = '-';
                      $title_str = 'Close editing window';
                    }
                ?> 
                  <td align=right width='' nowrap>                   
                    <a href="javascript: add_new_option('add_new_option','<?php echo $selection_d_val['ID']?>','<?php echo $selection_d_val['Name']?>','<?php echo $edit_sign?>')"  title='<?php echo $title_str?>'>
                    [<?php echo $edit_sign?>]
                    </a>
                  </td>  
                <?php }else{?>
                  <td>
                    &nbsp;&nbsp;
                  </td>  
                <?php }?> 
                </tr>
            <?php  
                if($selection_d_val['ID'] == $ParentID){
                  $SQL = "SELECT `OptionID` FROM `ExpDetail` WHERE `SelectionID`='".$selection_d_val['ID']."'";
                  $exist_option_arr = array();
                  foreach($HITS_DB as $DBname){
                    $tmp_DB = new mysqlDB($DBname);
                    $exist_option_arr_d = $tmp_DB->fetchAll($SQL);
                    foreach($exist_option_arr_d as $exist_option_val_d){
                      array_push($exist_option_arr, $exist_option_val_d['OptionID']);
                    }
                  }
          ?>    <tr>                
                  <td valign=top align=left nowrap>
                    <div class=maintext>&nbsp;&nbsp;</div><?php echo str_repeat("<br>", $num_options)?>
                  </td>
                  <td nowrap width=50%>
                  <DIV id="add_selection" class="st1">
                    <table border=0 width=100% cellspacing="1" cellpadding=1>
                  <?php foreach($option_d_arr as $option_d_val){?>
                      <tr bgcolor='<?php echo $selected_td_bcolor?>' height='20'>
                        <td align='left' valign=top nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $option_d_val['Name']?>&nbsp;&nbsp;
                        <?php if($analyst_this_page_permission_arr['Delete'] && !in_array($option_d_val['ID'], $exist_option_arr)){?>
                          <a href="javascript: delete_option('o_type','<?php echo $ParentID?>','<?php echo $option_d_val['ID']?>')" title=delete>       
                          <img border="0" src="images/icon_delete_option.gif" alt="Delete">
                          </a>
                        <?php }?>
                        </div>
                        </td> 
                      </tr>   
                  <?php }
                    $o_text_len = 28;
                    if($browser_v == 'Nav')  $o_text_len = 29;   
                  ?>
                  <?php if($analyst_this_page_permission_arr['Insert']){?>
                      <tr bgcolor='<?php echo $selected_td_bcolor?>'>
                        <td nowrap><div class=maintext>&nbsp;<input type="text" name="frm_new_option_name" size="<?php echo $o_text_len?>" maxlength=50 value="">&nbsp;
                        <input type="button" value=" Add " onClick="javascript: save_new_option('save_new_option',<?php echo $selection_d_val['ID']?>);"></td>        
                      </tr>
                  <?php }?> 
                  <?php if($message){
                      echo "<tr><td colspan=2 BGCOLOR='$selected_td_bcolor'>$message</td></tr>";
                    }                
                  ?>   
                    </table>
                  </DIV><br>
                  </td>
                  <td>&nbsp;</td>
                </tr>                
              <?php }
              }
            }  ?>            
            </table>       
            </DIV><br><br>        
          </td>
          </tr>
        <?php }?>  
        </table>    
      </td>
    <?php if(!$edit_only){?>
      <td align=center valign=top bgcolor="#f8f8fc">
        <table align="center" cellspacing="0" cellpadding="0" border="0" width=90%>
          <!--tr><td nowrap align=center><br><b>Selected Options</b><br><hr size=1 width=100%><br><br></td></tr-->
          <tr>
            <td colspan='2' nowrap >&nbsp;&nbsp;</td>
          </tr>
          <tr>
            <td id = 'columns_tital' colspan='2' nowrap align=center BGCOLOR='#a0a7c5' height='25'><font color=white><b>Selected Options</b></font></td>
          </tr>
          <tr>
            <td colspan='2' nowrap valign=top align=right height='40'>
            &nbsp;&nbsp;<br>&nbsp;
            </td>
          </tr>
          <tr>
            <td align=center>
            <DIV id="selections" class="st2">
              <table width="100%" border="0" cellspacing="1" cellpadding="0">
              <?php foreach($Selected_option_arr as $Selected_option_key => $Selected_option_val){
                  $tmp_option_arr = explode(";;", $Selected_option_val);
                  $tmp_item = $Selected_option_key.",,".$Selected_option_val;
              ?>
                  <tr bgcolor='<?php echo $selected_td_bcolor?>'>
                    <td align='left' nowrap width=40%><div class=maintext><b>&nbsp;&nbsp;<?php echo $tmp_option_arr[0]?></b>&nbsp;</div></td>
                    <td align='left' nowrap><div class=maintext>&nbsp;<?php echo $tmp_option_arr[1]?></div></td>
                    <td align='left' width=20% nowrap>
                    <a href="javascript:move_item('move_up','<?php echo $tmp_item?>');" title='up' class=button><img border="0" src="images/icon_up.gif"></a><a href="javascript:move_item('move_down','<?php echo $tmp_item?>');" title='down' class=button><img border="0" src="images/icon_down.gif"></a><a href="javascript:move_item('remove','<?php echo $tmp_item?>');" title='remove' class=button><img border="0" src="images/icon_remove.gif"></a>&nbsp;
                    </td>
                  </tr>
              <?php }?>	
        			</table>
            </DIV>  
            </td>
          </tr>
          <tr>
              <td align=center>&nbsp;<br>
              
              <input type="button" value=" Pass Data " onClick="javascript: pass_data();">
              <?php 
              if($for_search){
                echo "with
                      <select name='frm_and_or'>
                      <option value='AND' selected>AND
                      <option value='OR'>OR
                      </select>";
              }
              ?>&nbsp;&nbsp;
              <input type="button" value=" Close " onClick="javascript: window.close();"><br>&nbsp;</td>        
          </tr>
       </table>
      </td>
    <?php }?>  
      
      
    </tr> 
  </table>
</FORM>
</body>
</html>