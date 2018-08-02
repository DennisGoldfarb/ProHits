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
$mod_HitNote = '';
$mod_HitDis = '';
$HitDisID = '';
$HitNoteID = '';
$message = '';

$Bait_ID = '';
$Exp_ID = '';
$Band_ID = '';
$frm_disID = '';
$pro_interface = '';
$protocol_toggle_lable = 'add sample protocols';

$mod_BaitDiscussion = array();

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require("analyst/status_fun_inc.php");

if($theaction == 'modify_note'){
  modify_note_block($frm_disID,$item_type);
  exit;
}

//=========================================================================================
if($item_type == 'Band'){
  $SQL = "SELECT `ExpID`,`BaitID` FROM `Band` WHERE `ID`='$item_ID'";
  if($tmp_arr = $HITSDB->fetch($SQL)){
    $Bait_ID = $tmp_arr['BaitID'];
    $Exp_ID = $tmp_arr['ExpID'];
    $Band_ID = $item_ID;
    $group_table_name = 'BandGroup';
    $labal = 'Band';
  }
}elseif($item_type == 'Experiment'){
  $SQL = "SELECT `BaitID` FROM `Experiment` WHERE `ID`='$item_ID'";
  if($tmp_arr = $HITSDB->fetch($SQL)){
    $Bait_ID = $tmp_arr['BaitID'];
    $Exp_ID = $item_ID;
    $group_table_name = 'ExperimentGroup';
    $labal = 'Experiment';
  }
}elseif($item_type == 'Bait'){
  $Bait_ID = $item_ID;
  $group_table_name = 'BaitGroup';
  $labal = 'Bait';
}
$userNamesArr = get_users_ID_Name($HITSDB);
$Log = new Log();
//all users can insert note into discussion table

if($item_type == 'Band' && $theaction == 'delete_sample_protocol'){
  if($deleted_user_pro_id){
    $SQL = "DELETE FROM BandGroup 
            WHERE ID='$deleted_user_pro_id'";
    if($HITSDB->execute($SQL)){
      $Desc = "Sample protocol ID='$deleted_user_pro_id'";
      $Log->insert($AccessUserID,$labal.'Group',$deleted_user_pro_id,'delete',$Desc,$AccessProjectID);
    }
  }
  $theaction = '';
}elseif($item_type == 'Band' && $theaction == 'add_change_protocols'){
  for($i=1; $i<=$pro_type_counter; $i++){
    $v_name = 'frm_ProType_'.$i;    
    $tmp_pro_arr = explode("___", $$v_name, 2);
    $protocol_id = $tmp_pro_arr[0];
    $protocol_type = $tmp_pro_arr[1];
    if(!$protocol_id){
      $SQL = "DELETE FROM BandGroup WHERE Note='$protocol_type' AND RecordID='$item_ID'";
      if($HITSDB->execute($SQL)){
        $Desc = "Sample protocol Note='$protocol_type'";
        $Log->insert($AccessUserID,$labal.'Group',$item_ID,'delete',$Desc,$AccessProjectID);
      }
     continue;
    }
    $SQL = "SELECT `ID`,
            `NoteTypeID`,
            `UserID`
            FROM `BandGroup` 
            WHERE `RecordID`='$item_ID'
            AND `Note`='$protocol_type'";
    $tmp_pro_arr = $HITSDB->fetch($SQL);    
    if(!$tmp_pro_arr){
      $SQL = "INSERT INTO BandGroup SET
              `RecordID`='$item_ID',
              `Note`='$protocol_type',
              `NoteTypeID`='$protocol_id',
              `UserID`='$AccessUserID',
              `DateTime`=now()";
      if($ret_id = $HITSDB->insert($SQL)){
        $Desc = "NoteTypeID = $protocol_id(Sample protocol ID)";
        $Log->insert($AccessUserID,$labal.'Group',$ret_id,'insert',$Desc,$AccessProjectID);
      }    
    }elseif($tmp_pro_arr && $tmp_pro_arr['UserID'] == $AccessUserID){
      if(!$protocol_id ){
        $SQL = "DELETE FROM BandGroup WHERE ID='".$tmp_pro_arr['ID']."'";
        if($HITSDB->execute($SQL)){
          $Desc = "Sample protocol";
          $Log->insert($AccessUserID,$labal.'Group',$tmp_pro_arr['ID'],'delete',$Desc,$AccessProjectID);
        }
      }elseif($protocol_id == $tmp_pro_arr['NoteTypeID']){
        continue;
      }else{
        $SQL = "UPDATE BandGroup SET
                `NoteTypeID`='$protocol_id'
                WHERE ID='".$tmp_pro_arr['ID']."'";
        if($HITSDB->execute($SQL)){        
          $Desc = "Sample protocol";
          $Log->insert($AccessUserID,$labal.'Group',$tmp_pro_arr['ID'],'update',$Desc,$AccessProjectID);
        }
      }
    }
  }  
}

$bgcolordark = '#858585';
$bodycolor = '#ffffff';
$bgcolor="#e1e1e1";
//$theUrl = 'http://www.ncbi.nlm.nih.gov/htbin-post/Entrez/query?form=6&db=p&Dopt=g&uid=';
?>
<html>
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
<LINK REL="SHORTCUT ICON" HREF="../images/prohits.ico">
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>
<!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
<script language="javascript">
function confirm_delete_note(disID){
	if(confirm("Are you sure that you want to delete this hit notes?")){ 
    document.action_form.frm_disID.value = disID;
    document.action_form.theaction.value='delete';
    document.action_form.protocol_toggle_lable.value = 'add sample protocols';
    document.action_form.pro_interface.value = '';
		document.action_form.submit();
	}
}

function update_note(){
  var disID=document.action_form.frm_disID.value;
  var theNote=document.action_form.frm_Note.value;  
  if(disID == '' || isEmptyStr(theNote)){
    alert("Note are required.");
    return false;
  }else{
    document.action_form.theaction.value = 'update';
    document.action_form.protocol_toggle_lable.value = 'add sample protocols';
    document.action_form.pro_interface.value = ''; 
    document.action_form.submit();
  } 
}
function add_new_note(theForm){
  if(document.getElementById('add_update').value == 'Update'){
    update_note();
    return;
  }
  var theNote=theForm.frm_Note.value;
  if(theNote == '' || isEmptyStr(theNote)){
    alert("Note Type and Note are required.");
    return false;
  }else {
    theForm.theaction.value = 'insert';
    theForm.protocol_toggle_lable.value = 'add sample protocols';
    theForm.pro_interface.value = ''; 
    theForm.submit();
  } 
}
function refresh(){
  document.action_form.theaction.value = '';
  document.action_form.submit();
}

function isEmptyStr(str){
  var str = this != window? this : str;
  var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
  if(temstr == 0 || temstr == ''){
    return true;
  } else {
    return false;
  }
}

function toggle_protocol(){
  var theForm = document.action_form;
  if(theForm.protocol_toggle_lable.value == 'add sample protocols'){
    theForm.protocol_toggle_lable.value = 'view sample protocols';
    theForm.pro_interface.value = 'add_sample_protocols';
  }else{
    theForm.protocol_toggle_lable.value = 'add sample protocols';
    theForm.pro_interface.value = '';
  }
  theForm.theaction.value = '';
  theForm.submit();
}

function delete_sample_protocol(user_protocol_id,p_id){
  theForm = document.action_form;
  if(!confirm("Delete protocol '"+p_id+"'?"))return;
  theForm.deleted_user_pro_id.value = user_protocol_id;
  theForm.theaction.value = 'delete_sample_protocol';
  theForm.submit();
}
</script>

 </head>
 <body bgcolor=<?php echo $bodycolor;?>>
 <form name=action_form method=post action="<?php echo $PHP_SELF;?>">
 <input type=hidden name=item_ID value="<?php echo $item_ID;?>">
 <input type=hidden name=item_type value='<?php echo $item_type;?>'>
 <input id='theaction' type='hidden' name='theaction' value="">
 <input type=hidden name=pro_interface value="">
 <input type=hidden name=protocol_toggle_lable value="<?php echo $protocol_toggle_lable?>">
 <input type=hidden name=deleted_user_pro_id value="">
 <table border="0" cellpadding="0" cellspacing="0" width="100%">  
   <tr bgcolor="">
  	  <td colspan="4">
      <?php 
      $lable_display = ($labal == 'Band')?'Sample':$labal;
      ?>
  		<span class=pop_header_text>&nbsp;<?php echo $labal?> Notes </span> (ProHits <?php echo $labal?> id:<?php echo $item_ID;?>)<br><hr size=1>
      </td>
   </tr>
 </table>
 
 <table border=0 cellspacing="6" cellpadding="0" width=100%>  
    <tr>
      <td>
        <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
        <?php 
          bait_info($Bait_ID);
        ?>
        </DIV>
      </td>
    </tr>
<?php if($item_type == 'Experiment' || $item_type == 'Band'){?>   
    <tr>
      <td>
        <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
        <?php 
          Exp_info($Exp_ID,'Experiment');
        ?>
        </DIV>
      </td>
    </tr>
<?php }?>
<?php if($item_type == 'Band'){?>    
    <tr>
      <td>
        <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
        <?php 
          band_info($Band_ID,'Band');
        ?>
        </DIV>&nbsp;
      </td>
    </tr>
<?php }
$pro_type_counter = 0;
if(num_protocols_for_this_project($AccessProjectID)){
?>
    <tr>
      <td>
<DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
<?php   
  if($item_type == 'Band'){
    $SQL = "SELECT Type
                   FROM Protocol
                   WHERE Type LIKE 'SAM%'
                   GROUP BY Type";
    $group_pro_arr = $HITSDB->fetchAll($SQL);
  
    $SQL = "SELECT P.ID,
                   P.Name ,
                   P.Type,
                   P.Detail,
                   B.ID AS Protocol_ID,
                   B.UserID,
                   B.DateTime 
                   FROM BandGroup B
                   LEFT JOIN Protocol P
                   ON (P.ID=B.NoteTypeID) 
                   WHERE B.RecordID='$item_ID'
                   AND P.Name IS NOT NULL
                   AND B.Note LIKE 'SAM%'";
    $tmp_pro_arr = $HITSDB->fetchAll($SQL);
  ?>
    <table border="0" cellpadding="1" cellspacing="1" width="100%">
      <tr>
        <td align=left width="30%" colspan="2"><span class='tableheader_black'><b>Sample Protocols</b></span></td>
        <td align=right colspan="2">
   <?php //if(count($group_pro_arr) != count($tmp_pro_arr)){?>
   <?php if(!is_all_sample_protocls_used($item_ID)){?>
          <span class='tableheader_black'>
           <a id='toggle_view' href="javascript:toggle_protocol();">[<?php echo $protocol_toggle_lable?>]</a>
          </span>
   <?php }else{?>
          &nbsp;
   <?php }?>
        </td>
      </tr>
    </table>  
  <?php   
    if(!$pro_interface){
  ?>
    <table border="0" cellpadding="1" cellspacing="1" width="100%">   
      <tr>
        <td nowrap bgcolor="<?php echo $bgcolordark;?>" width="5%"><span class=maintext_bold_white><b>ID</span></td> 
        <td nowrap bgcolor="<?php echo $bgcolordark;?>" width=""><span class=maintext_bold_white>Type</span></td>
        <td nowrap bgcolor="<?php echo $bgcolordark;?>"  width=""><span class=maintext_bold_white>Name</span></td>
        <td nowrap bgcolor="<?php echo $bgcolordark;?>"  width="10%"><span class=maintext_bold_white>Added By</span></td>
        <td  bgcolor="<?php echo $bgcolordark;?>" align=center width="10%"><span class=maintext_bold_white>Options</span></td>
      </tr>    
  <?php    
      foreach($tmp_pro_arr as $type_val){
        $tmp_arr = explode("_", $type_val['Type'], 2);
        $prot_div_id = $AccessProjectID.'_'.$type_val['ID'];
        $tmpUser = get_userName($HITSDB, $type_val['UserID']);
        $file  = "./protocol_detail_pop.php?modal=this_project&outsite_script=1&selected_type_div_id=".$type_val['Type']."&selected_prot_div_id=$prot_div_id";
  ?>
      <tr>
        <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext>SP<?php echo $type_val['ID']?></span></td>
        <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo format_pro_type_name($tmp_arr[1])?></span></td>
        <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $type_val['Name']?></span></td>
        <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $tmpUser?></span></td>
        <td bgcolor="<?php echo $bgcolor;?>" nowrap>
          <span class=maintext>
            <a href="javascript: popwin('<?php echo $file;?>','700','700','new');" class=button>[view]</a>
        <?php if($AUTH->Delete && $type_val['UserID'] == $AccessUserID){?> 
            <a href="javascript:delete_sample_protocol('<?php echo $type_val['Protocol_ID'];?>','SP<?php echo $type_val['ID']?>');">
              <img border="0" src="images/icon_purge.gif" alt="Delete sample protocol">
            </a>
        <?php }else{?> 
              <img src="images/icon_empty.gif" width=17>
        <?php }?>           
          </span>
        </td>
      </tr>
    <?php }?>
    </table>
  <?php }else{
      $pro_type_counter = sample_protocols_select_update_block($item_ID);
      if($pro_type_counter){
  ?>
    <table border="0" cellpadding="1" cellspacing="1" width="100%">
        <tr>
          <td colspan=4 align=center>
            <input type=button value='Add Protocols' onClick='javascript: add_change_protocols(this.form)'; class=black_but>
          </td>
        </tr>    
    </table>
  <?php 
      }  
    }
  }
?>
</DIV>
      </td>
    </tr>
<?php 


}
?>
<script language="javascript">
function add_change_protocols(theForm){
  theForm.theaction.value = 'add_change_protocols';
  var not_empty = false;
<?php for($i=1; $i<=$pro_type_counter; $i++){?>
    Protocol_ID = theForm.frm_ProType_<?php echo $i?>.options[theForm.frm_ProType_<?php echo $i?>.selectedIndex].value;
    if(Protocol_ID != ''){
      var Pretocol_arr = Protocol_ID.split("___");
      if(Pretocol_arr[0] != ''){
        not_empty = true;
      }
    }
<?php }?>
  if(!not_empty){
    alert("Please select a protocol");
    return;
  }  
  theForm.protocol_toggle_lable.value = 'add sample protocols';
  theForm.pro_interface.value = '';
  theForm.submit();
}
</script>
    <tr>
      <td>
<?php 
note_block($theaction,$item_ID,$item_type,$frm_disID);
?>
      </td>
    </tr>
</table>
<center>
<?php 
  //if(count($mod_BaitDiscussion)){?>
	<!--input type=button value=' Update ' onClick='javascript: update_note(this.form)'; class=black_but-->
<?php //}else{?>
	<input id='add_update' type='button' value='Save New Notes' onClick='javascript: add_new_note(this.form)'; class=black_but>
<?php //}?>
<input type=button value=' Refresh ' onClick='javascript: refresh()'; class=black_but>
<input type=button value=' Close ' onClick='javascript: window.close();' class=black_but>
</center>
</form>
</body>
</html>
