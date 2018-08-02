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

$whichBait= '';
$msg = '';
$order_by = '';
$frm_Name = '';
$sub = '';
$theaction = '';
$Exp_ID = '';
$Bait_ID = '';
$Gel_ID = '';
$frm_Date = '';
$frm_TaxID = '';
$frm_OwnerID = '';
$frm_PreySource = '';
$frm_GrowProtocol = '';
$frm_IpProtocol = '';
$frm_DigestProtocol = '';
$frm_PeptideFrag = '';
$frm_Notes = '';
$img_msg = '';
$GrowProtocolFlag = 0;
$IpProtocolFlag = 0;
$DigestProtocolFlag = 0;
$PeptideFragFlag = 0;
$change_protocol = '';
$Selected_option_str = '';

$frm_disID = '';
$note_action = '';
require("../common/site_permission.inc.php");
require("analyst/classes/bait_class.php");
//require("analyst/classes/condition_class.php");
require("analyst/classes/dateSelector_class.php");
require("classes/experiment_class.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("analyst/status_fun_inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

if($theaction == 'modify_note'){
  modify_note_block($frm_disID,'Experiment');
  exit;
}

require("site_header.php");
$imageLocation = "./western_images/";
$Bait = new Bait();
$Log = new Log();
//processing move bait

if($whichBait == 'last'){
  $Bait_ID = $Bait->move_bait('last');
}else if($whichBait == 'first'){
  $Bait_ID = $Bait->move_bait('first');
}else if($whichBait == 'next' and $Bait_ID){
  $Bait_ID = $Bait->move_bait('next',$Bait_ID);
}else if($whichBait == 'previous' and $Bait_ID){
  $Bait_ID = $Bait->move_bait('previous', $Bait_ID);
}

if($Bait_ID){
  $Bait->fetch($Bait_ID);  
}
$query="SELECT * FROM Experiment limit 1";
$result=mysqli_query($mainDB->link, $query);
$numfields = mysqli_field_count($mainDB->link);
$PreySource_ON = 0;

$ExpsObj = new Experiment($HITSDB->link);

  
if($theaction == "delete" AND $Exp_ID AND $AUTH->Delete ){
  if($ExpsObj->isOwner($Exp_ID,$AccessUserID)){
    $msg = $ExpsObj->delete($Exp_ID);
    $theaction = "viewall";
    if(!$msg){
      $Desc = "";
      $Log->insert($AccessUserID,'Experiment',$Exp_ID,'delete',$Desc,$AccessProjectID);
      $SQL = "DELETE FROM ExpDetail WHERE ExpID = $Exp_ID";
      $HITSDB->execute($SQL);
    }
  }
}

$ProtocolIdNameArr = get_Protocol_Name_Pair($mainDB);
$ExpDetail_id_name_arr = get_expDetail_id_name_arr();
?>
<script language="javascript">
function confirm_delete_note(note_ID){
  if(confirm("Are you sure that you want to delete the note?")){
    document.add_modify_form.frm_disID.value = note_ID;
    document.add_modify_form.note_action.value = "delete";
    document.add_modify_form.submit();
  }
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
function confirm_delete(Exp_ID){
    if(confirm("Are you sure that you want to delete the experiment?")){
    document.del_form.Exp_ID.value = Exp_ID;
    document.del_form.submit();
  }
}
function allSelect(){
  var List = document.add_modify_form.conditions;
  var ChangeTo = document.add_modify_form.select_all.checked;
  if (List.length && List.options[0].value == 'temp') return;
  for (i=0;i<List.length;i++){
     List.options[i].selected = ChangeTo;
  }
}
function checkImage(){
  var theForm = document.add_modify_form;
  if(theForm.frm_Image.value ==''){
    alert("please add an image");
  }else{
    submitit();
  }
}
function submitit(){
  var theForm = document.add_modify_form;
  if(!onlyAlphaNumerics(theForm.frm_Name.value, '6')){
    alert("Only [()_A-Za-z0-9] allowed");
    return false;
  }
  
  if(document.getElementById('note_header').innerHTML == 'Modify Experiment Note'){
    theForm.note_action.value = 'update';
  }else{
    theForm.note_action.value = 'insert';
  }
  
  var current_name = theForm.current_Name.value;
  
  for(var i=0; i<exp_name_arr.length; i++){
    if(exp_name_arr[i] == current_name) continue;
    if(exp_name_arr[i] == theForm.frm_Name.value){
      alert("The experiment name had been used");
      return false;
    }
  }
  if(theForm.frm_TaxID.value == ''){
    alert("Bold field names are requiered to make the insert.");
    return false;
  }
  theForm.submit();
}
function select_exp(){
  var theForm = document.add_modify_form;
  theForm.theaction.value = "addnew";
  if(theForm.Gel_ID.value == ''){
    theForm.action = "plate_free.php";
  }else{
    theForm.action = "band.php";
  }  
  theForm.submit();
}
function move_bait(whichBait){
  var theForm =  document.move_bait_form
  if(theForm.theaction.value == 'insert'){
    theForm.theaction.value = 'addnew';
  }
  if(whichBait == 'last') { 
    theForm.whichBait.value = 'last';
  } else if(whichBait == 'first') {
    theForm.whichBait.value = 'first';
  } else if(whichBait == 'next') {
    theForm.whichBait.value = 'next';
  } else if(whichBait == 'previous') {
    theForm.whichBait.value = 'previous';
  }
  //theForm.theaction.value = 'move';
  theForm.submit();
}
function sortList(order_by){
  var theForm = document.del_form;
  theForm.order_by.value = order_by;
  theForm.theaction.value = 'viewall';
  theForm.submit();
}
function modifyIt(Exp_ID){
  var theForm = document.del_form;
  theForm.Exp_ID.value = Exp_ID;
  theForm.theaction.value = 'modify';
  theForm.submit();
}
function goBand(Exp_ID){
  var theForm = document.del_form;
  theForm.Exp_ID.value = Exp_ID;
  theForm.theaction.value = 'addnew';
  if(theForm.Gel_ID.value == ''){
    theForm.action = 'plate_free.php';
  }else{
    theForm.action = 'band.php';
  }  
  theForm.submit();
}

function show_protocol_detail(Protocol_type,is_new){
  var theForm = document.add_modify_form;
  var Protocol_ID = '';
  var frm_Type = '';
  var theaction = '';
  if(Protocol_type == 1){
    Protocol_ID = theForm.frm_GrowProtocol.options[theForm.frm_GrowProtocol.selectedIndex].value;
    frm_Type = 'GrowProtocol';
  }else if(Protocol_type == 2){
    Protocol_ID = theForm.frm_IpProtocol.options[theForm.frm_IpProtocol.selectedIndex].value;
    frm_Type = 'IpProtocol';
  }else if(Protocol_type == 3){
    Protocol_ID = theForm.frm_DigestProtocol.options[theForm.frm_DigestProtocol.selectedIndex].value;
    frm_Type = 'DigestProtocol';
  }else if(Protocol_type == 4){
    Protocol_ID = theForm.frm_PeptideFrag.options[theForm.frm_PeptideFrag.selectedIndex].value;
    frm_Type = 'PeptideFrag';
  }
  if(is_new == 1){
    theaction = "add";
    file = 'show_protocol_detail.php?Protocol_ID=' + Protocol_ID + '&theaction=' + theaction + '&frm_Type=' + frm_Type;
    popwin(file,600,400);
  }else if(Protocol_ID != '' && is_new == 0){
    file = 'show_protocol_detail.php?Protocol_ID=' + Protocol_ID + '&theaction=' + theaction + '&frm_Type=' + frm_Type;
    popwin(file,600,400);
  }
}

function show_protocol_static(Protocol_type,is_new,Protocol_ID){
  if(Protocol_type == 1){
    frm_Type = 'GrowProtocol';
  }else if(Protocol_type == 2){
    frm_Type = 'IpProtocol';
  }else if(Protocol_type == 3){
    frm_Type = 'DigestProtocol';
  }else if(Protocol_type == 4){
    frm_Type = 'PeptideFrag';
  }
  var theaction = '';
  if(is_new == 1 && Protocol_ID == ''){
    theaction = "add";
    file = 'show_protocol_detail.php?Protocol_ID=' + Protocol_ID + '&theaction=' + theaction + '&frm_Type=' + frm_Type;
    popwin(file,600,400);
  }else if(Protocol_ID != '' && is_new == 0){
    file = 'show_protocol_detail.php?Protocol_ID=' + Protocol_ID + '&theaction=' + theaction + '&frm_Type=' + frm_Type;
    popwin(file,600,400);
  }
}
function show_condition(sel, con_add){
  var condition_ID = '';
  if(con_add != 'new'){
    condition_ID = sel.options[sel.selectedIndex].value;
  }
  var file = 'pop_expCondition.php?frm_ID=' + condition_ID;
  popwin(file,600,400);
  
}
function remove_image(theForm){
  theForm.theaction.value = 'removeImage';
  theForm.submit();
}
function view_image(WesternGel,BatchCode) {  
  file = 'western_gel_view.php?WesternGel=' + WesternGel + '&BatchCode=' + BatchCode;
  popwin(file,500,600);
}
function change_protocol(theType){
  var theForm = document.add_modify_form;
  theForm.theaction.value = 'modify';
  theForm.change_protocol.value = theType;
  theForm.submit();
}
<?php if($theaction != 'addnew'){?>
function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 2){
    var div_id = trimString(ret_html_arr[0]);
    document.getElementById(div_id).innerHTML = ret_html_arr[1];
    return;
  }
}
<?php }?>
</script>
<?php if($sub){?>
<table cellspacing="1" cellpadding="0" border="0" align=center>
<tr><td>
<?php if($sub != 3){?>
    <img src="./images/arrow_green_gel.gif" border=0>
<?php }?>    
    <img src="./images/arrow_green_bait.gif" border=0>
    <img src="./images/arrow_red_exp.gif" border=0>
    <img src="./images/arrow_green_band.gif" border=0>
<?php if($sub != 3){?>    
    <img src="./images/arrow_green_well.gif" border=0>
<?php }?>
</td>
</tr>
</table>
<?php }?>
<table border="0" cellpadding="0" cellspacing="0" width="95%">
  <tr>
    <td colspan=2><div class=maintext>
      <img src="images/icon_purge.gif"> Delete 
      <img src="images/icon_tree.gif"> Next Level
      <img src="images/icon_view.gif"> Modify 
      <img src="images/arrow_small.gif"> Next
      </div>
    </td>
  </tr>
  <tr>
    <td align="left">
    &nbsp; <font color="#102343" face="helvetica,arial,futura" size="3"><b>Experiments
<?php 
  if($AccessProjectName){
    echo "  <font color='red' face='helvetica,arial,futura' size='3'>(Project $AccessProjectID: $AccessProjectName)</font>";
  }
  if($sub && !$Gel_ID){
    echo "  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='blue' face='helvetica,arial,futura' size='3'>(Submit Gel Free Sample)</font>";
  }elseif($sub && $Gel_ID){
    echo "  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color='green' face='helvetica,arial,futura' size='3'>(Submit Gel Sample)</font>";
  }
?>
    </b> 
    </font> 
  </td>
  <td align="right">
 <?php if($AUTH->Insert){
      //if($Gel_ID){ 
 ?>
      <a href="<?php echo $PHP_SELF;?>?theaction=addnew&Bait_ID=<?php echo $Bait_ID;?><?php echo  ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?>" class=button>[New Experiment]</a>&nbsp;
  <?php   //}
    }
  ?>
      <a href="<?php echo $PHP_SELF;?>?theaction=viewall&Bait_ID=<?php echo $Bait_ID;?><?php echo  ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?>" class=button>[Experiment List]</a>&nbsp;
 <?php if($Bait_ID){?>
    <a href="bait.php?theaction=modify&Bait_ID=<?php echo $Bait_ID;?><?php echo  ($sub)?"&sub=$sub&Gel_ID=$Gel_ID":"";?>" class=button>[Back to Bait]</a>&nbsp;
  <?php }?> 
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2><br>
<?php 
if($msg){
  echo "<font color=red face=\"Arial\" size=3> $msg </font>";
}

if($Bait_ID){
?>
    <form name=move_bait_form method=post  action=<?php echo $PHP_SELF;?>>    
    <input type=hidden name=theaction value=<?php echo $theaction;?>>
    <input type=hidden name=Gel_ID value='<?php echo $Gel_ID;?>'>
    <input type=hidden name=Bait_ID value='<?php echo $Bait_ID;?>'>
    <input type=hidden name=whichBait value=''>
    <input type=hidden name=sub value=<?php echo $sub;?>>
    <input type=hidden name=ListType value='<?php echo $ListType;?>'>
    <input type=hidden name=searchThis value='<?php echo $searchThis;?>'>
    <table border="0" cellpadding="0" cellspacing="1" width="700">   
    <tr>
      
    </tr>    
    <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
      <td colspan=4 height=20>
      <div class=tableheader>
        &nbsp;Bait Information</div>
      </td>
    </tr>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td width=25%><div class=maintext>&nbsp;<b>Bait ID</b>:</div></td>
      <td width=25%><div class=maintext>&nbsp;<?php echo $Bait->ID;?></div></td>
      <td width=25%><div class=maintext>&nbsp;<b>Clone Number</b>:</div></td>
      <td width=25%><div class=maintext>&nbsp;<?php echo $Bait->Clone;?></div></td>
    </tr>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td><div class=maintext>&nbsp;<b>Gene ID</b>:</div></td>
      <td><div class=maintext>&nbsp;<?php echo $Bait->GeneID;?></div></td>      
      <td><div class=maintext>&nbsp;<b>Genus Species</b>:</div></td>
      <td><div class=maintext>&nbsp;<?php echo get_TaxID_name($mainDB, $Bait->TaxID, $HITS_DB["prohits"]);?></div></td>
    </tr>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td><div class=maintext>&nbsp;<b>Locus Tag</b>:</div></td>
      <td><div class=maintext>&nbsp;<?php echo $Bait->LocusTag;?></div></td>
      <td><div class=maintext>&nbsp;<b>Created</b>:</div></td>
      <td><div class=maintext>&nbsp;<?php echo $Bait->DateTime;?></div></td>
    </tr>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td><div class=maintext>&nbsp;<b>Gene Name</b>:</div></td>
      <td><div class=maintext>&nbsp;<?php echo $Bait->GeneName;?></div></td>
      <td><div class=maintext>&nbsp;<b>Created by</b>:</div></td>
      <?php  $theUser = get_userName($mainDB, $Bait->OwnerID);?>
      <td><div class=maintext>&nbsp;<?php echo $theUser;?></div></td>
    </tr>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td><div class=maintext>&nbsp;<b>Family</b>:</div></td>
      <td><div class=maintext>&nbsp;<?php echo $Bait->Family;?>&nbsp;</div></td>
      <td><div class=maintext>&nbsp;<b>Project</b>:</div></td>
      <td><div class=maintext>&nbsp;<?php echo $AccessProjectName;?></div></td>
    </tr>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td><div class=maintext>&nbsp;<b>Bait MW</b>:</div></td>
      <td><div class=maintext>&nbsp;<?php echo $Bait->BaitMW;?> kDa</div></td>
      <td><div class=maintext>&nbsp;<b>Status</b></div></td>
      <td>
        <DIV id="R_<?php echo $Bait->ID?>">
        <?php $statusArr = get_status($Bait->ID,'Bait');?>
        </DIV>
      </td>
    </tr>
    <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
      <td colspan=45 align=center  valign="bottom"><div class=maintext>
   <?php  if($theaction != "modify" and $theaction != "update" ){ ?> 
      <a href="javascript: move_bait('first');">
      <img src="./images/icon_first.gif" border=0 valign="bottom"></a>&nbsp;
         <a href="javascript: move_bait('previous');">
         <img src="./images/icon_previous.gif" border=0 valign="bottom"></a>&nbsp;
        <a href="javascript: move_bait('next');">
        <img src="./images/icon_next.gif" border=0 valign="bottom"></a>&nbsp;
      <a href="javascript: move_bait('last');">
      <img src="./images/icon_last.gif" border=0 valign="bottom"></a>&nbsp;</div>
  <?php }?></td>
    </tr>
    </table> 
    </form>
<?php 
}//end bait if
if($theaction == "viewall" or !$theaction) {
  if(!$order_by) $order_by = "ID ";
  //====================================
    $SQL = "SELECT * FROM Experiment";
    if(!$Bait_ID) $Bait_ID = 0;
    $SQL .= " WHERE BaitID = '$Bait_ID' and ProjectID=$AccessProjectID";    
    if($order_by){
      $SQL .=" order by $order_by";
    }
    if(!$Bait_ID) $SQL .= ' limit 10';
    $Exps = $HITSDB->fetchAll($SQL);
?>
    <form name=del_form method=post  action=<?php echo $PHP_SELF;?>>    
    <input type=hidden name=theaction value=delete>
    <input type=hidden name=Bait_ID value='<?php echo $Bait_ID;?>'>
    <input type=hidden name=Exp_ID value=>
    <input type=hidden name=sub value=<?php echo $sub;?>>
    <input type=hidden name=Gel_ID value='<?php echo $Gel_ID;?>'>
    <input type=hidden name=order_by value='ID'>
    <input type=hidden name=ListType value='<?php echo $ListType;?>'>
    <input type=hidden name=searchThis value='<?php echo $searchThis;?>'>
  <table border="0" cellpadding="0" cellspacing="1" width="700">
  <tr bgcolor="">
    <td width="" height="20" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center> 
      <div class=tableheader><?php echo ($AccessProjectName != 'Tyers_Yeast_Gel_Free')?"Exp ID":"&nbsp;";?></div>
    </td>
    <td width="" height="20" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center>
      <div class=tableheader>Experiment Name</div>
    </td>
  
    <td width="" height="20" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center>
    <div class=tableheader>Condition</div> 
    </td>
  
  
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center">
      <div class=tableheader>Created</div>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center">
      <div class=tableheader>Status</div>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align="center">
      <div class=tableheader>Options</div>
    </td>
  </tr>
<?php    
  for($i=0; $i < count($Exps); $i++) {
  
    $SQL = "SELECT 
            `SelectionID`,
            `OptionID` 
            FROM `ExpDetail` 
            WHERE `ExpID`='".$Exps[$i]['ID']."'
            ORDER BY `IndexNum`";
    $ExpCDT = $HITSDB->fetchAll($SQL);
     
    $bgcolor = "#d0e4f8";
    $hasLane = $ExpsObj->has_lanes($Exps[$i]['ID']);
    $hasBand = $ExpsObj->has_bands($Exps[$i]['ID']);
?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo ($AccessProjectName != 'Tyers_Yeast_Gel_Free')?$Exps[$i]['ID']:"";?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $Exps[$i]['Name'];?>&nbsp;
      </div>
    </td>
   
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php 
         
          for($k=0; $k<count($ExpCDT); $k++){
            if($k) echo "<br>";
            echo "<b>".$ExpDetail_id_name_arr[$ExpCDT[$k]['SelectionID']]."</b>:".$ExpDetail_id_name_arr[$ExpCDT[$k]['OptionID']];
          }
        ?>
      </div>
    </td>
   
         
    <td width="" align="center"><div class=maintext>&nbsp;
        <?php echo $Exps[$i]['DateTime'];?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>
  <?php  
      $dark = '';
      $statusArr = get_status($Exps[$i]['ID'],'Experiment');
      if($statusArr['has_note']) $dark = "_dark";
  ?>
    </div>   
    </td>
    <td>
      <table border="0" cellpadding="0" cellspacing="0">
      <tr>
      <td width="" align="left"><div class=maintext> &nbsp;
  <?php if($AUTH->Delete and $Exps[$i]['OwnerID'] == $AccessUserID && (($Gel_ID && !$hasLane) || (!$Gel_ID && !$hasBand))){?>
        <a   title='delete experiment' href="javascript:confirm_delete('<?php echo $Exps[$i]['ID'];?>');">
          <img border="0" src="images/icon_purge.gif" alt="Delete"></a>&nbsp;
  <?php }else{
      echo "\n<img border=0 src='images/icon_empty.gif'>&nbsp;";
    }
    if($hasLane){
      if(!$sub){
  ?>
         <a href="lane.php?theaction=viewall&Gell_ID=<?php echo $Gel_ID;?>&Exp_ID=<?php echo $Exps[$i]['ID'];?><?php echo  ($sub)?"&sub=$sub":"";?>">
         <img border="0" src="images/icon_tree.gif" alt="Gel and Bands"></a>&nbsp;
  <?php //}else if($hasBand){?>
  <?php   }else{
        echo "\n<img border=0 src='images/icon_empty.gif'>&nbsp;";
      }  
    }elseif($hasBand){      
      if(!$sub){
        if($Gel_ID){?>
         <a  title='sample' href="band.php?theaction=viewband&Gell_ID=<?php echo $Gel_ID;?>&Exp_ID=<?php echo $Exps[$i]['ID'];?><?php echo  ($sub)?"&sub=$sub":"";?>">
      <?php }else{?>
         <a  title='sample' href="plate_free.php?theaction=viewband&Gell_ID=<?php echo $Gel_ID;?>&Exp_ID=<?php echo $Exps[$i]['ID'];?><?php echo  ($sub)?"&sub=$sub":"";?>"> 
      <?php }?>  
         <img border="0" src="images/icon_tree.gif" alt="Samples/Bands"></a>&nbsp; 
  <?php   }else{
        echo "\n<img src='./images/icon_empty.gif'>&nbsp;";
      }
    }else{
      echo "\n<img border=0 src='images/icon_empty.gif'>&nbsp;";
    } 
    if($AUTH->Access){ 
    ?>
          <a  title='experiment detail' href="javascript: modifyIt('<?php echo $Exps[$i]['ID'];?>');">
          <img border="0" src="images/icon_view.gif"></a>&nbsp;
  <?php }else{
      echo "\n<img src='./images/icon_empty.gif'>&nbsp;";
    }
  ?>  
          <a class=title title='add notes for the experiment' href="javascript: add_notes_dev('<?php echo $Exps[$i]['ID'];?>','Experiment');"><img src="./images/icon_notes<?php echo $dark?>.gif" border=0 alt="Bait Notes"></a>  
  <?php   
    if($Exps[$i]['WesternGel']){
      $WesternGelArr = explode(",",$Exps[$i]['WesternGel']);
      foreach($WesternGelArr as $value){
        echo "<a href=\"javascript: view_image('".$value."','".$Exps[$i]['Name']."');\">";
        echo "<img src='./images/icon_picture.gif' border=0 alt='view image'>";
        echo "</a>&nbsp;";
      }  
    }else{
       echo "\n<img src='./images/icon_empty.gif'>&nbsp;";
    }
    if($sub){
      if(($hasLane && $Gel_ID) || (!$hasLane && !$Gel_ID) || !$hasBand){
  ?>  
        <a href="javascript: goBand('<?php echo $Exps[$i]['ID'];?>');"> 
        <img src="./images/arrow_small.gif" border=0 alt="Submit Sample"></a>&nbsp;
      <?php 
      }else{
        echo "\n<img border=0 src='images/icon_empty.gif'>&nbsp;";
      }
    }
    if(!$hasLane && $hasBand){
      echo "Gel free";
    }
    ?>
        </div>
        </td>
        </tr>
        </table>
      </td>
    </tr>
  <?php 
  } //end for
  ?>
  </table>
  </form>
<?php 
//-----------------------------------------------------------------------------------------
}elseif(($theaction == "addnew" OR $theaction == "insert" ) and $AUTH->Insert ) { 
  $frm_Name = trim($frm_Name);
  if(($theaction == "insert") and ($frm_Name) ){
    if($frm_GrowProtocol){
      $frm_GrowDate = $frm_Grow_Year.'-'.$frm_Grow_Month.'-'.$frm_Grow_Day;
      $frm_GrowProtocol .= ','. $frm_GrowDate;
    }
    if($frm_IpProtocol){
      $frm_IpDate = $frm_Ip_Year.'-'.$frm_Ip_Month.'-'.$frm_Ip_Day;
      $frm_IpProtocol .= ','. $frm_IpDate;
    }
    if($frm_DigestProtocol){
      $frm_DigDate = $frm_Dig_Year.'-'.$frm_Dig_Month.'-'.$frm_Dig_Day;
      $frm_DigestProtocol .= ','. $frm_DigDate;
    }
    if($frm_PeptideFrag){
      $frm_PepDate = $frm_Pep_Year.'-'.$frm_Pep_Month.'-'.$frm_Pep_Day;
      $frm_PeptideFrag .= ','. $frm_PepDate;
    }   
    $SQL ="INSERT INTO Experiment SET 
      BaitID='$Bait_ID', 
      Name='".mysqli_escape_string($mainDB->link, $frm_Name)."', 
      TaxID='$frm_TaxID', 
      OwnerID='$AccessUserID',
      GrowProtocol='$frm_GrowProtocol',
      IpProtocol='$frm_IpProtocol',
      DigestProtocol='$frm_DigestProtocol',
      PeptideFrag='$frm_PeptideFrag',
      ProjectID='$AccessProjectID',
      Notes='".mysqli_escape_string($mainDB->link, $frm_Notes)."',
      DateTime= '$frm_Date',
      CollaboratorID='$frm_Collaborator'";
    $ExpsID = $mainDB->insert($SQL);
    
    $frm_Date =@strtotime($frm_Date);
    $Exp_ID = $ExpsID;
    
    $imageInsertFlag = 0;
    
    $uploaded_file_name = $_FILES['frm_Image']['name'];
    $uploaded_file_type = $_FILES['frm_Image']['type'];
    if((strstr($uploaded_file_type,"jpeg") or strstr($uploaded_file_type,"gif")) && $Exp_ID){
      $uploaded_file_name = preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
      $new_pic_name = "p".$_SESSION["workingProjectID"]."exp".$Exp_ID . "_" . $uploaded_file_name;
      if (move_uploaded_file($_FILES['frm_Image']['tmp_name'], $imageLocation . $new_pic_name)){
        $SQL = "UPDATE Experiment SET
                WesternGel='$new_pic_name'
                WHERE ID='$Exp_ID'";
        $imageInsertFlag = $mainDB->execute($SQL);  
        $img_msg = "image was successfully uploaded";
        $frm_Image = $new_pic_name;
      }else{
        $frm_Image = "";
        $img_msg = "<font color=#FF0000>Possible file upload attack! Please try again</font>";
      }
    }else{
      $frm_Image = "";
      if($uploaded_file_name){
        $img_msg = "<font color=red>uploaded file is not gif or jpeg image, please upload a gif or gpeg file</font>";
      }else{
        $img_msg = "<font color=#FF0000>no image uploaded</font>";
      }
    }
    if($Exp_ID){
      $Desc = "BaitID=$Bait_ID,Name=$frm_Name,TaxID=$frm_TaxID,ConditonID=$conditionString,GrowProtocol=$frm_GrowProtocol,IpProtocol=$frm_IpProtocol,DigestProtocol=$frm_DigestProtocol,DateTime=$frm_Date";
      if($imageInsertFlag){
        $Desc .= ",WesternGel=$new_pic_name";
      }else{
        $Desc .= ",WesternGel=insert failed";
      }      
    }else{
      $Desc = "insert fail";
    }        
    $Log->insert($AccessUserID,'Experiment',$ExpsID,'insert',$Desc,$AccessProjectID);
    
    if($Selected_option_str){
      $tmp_arr = explode("@@",$Selected_option_str);
      $tmp_counter = 1;
      foreach($tmp_arr as $tmp_val){
        $tmp_arr_2 = explode(",,",$tmp_val);
        $tmp_arr_3 = explode("_",$tmp_arr_2[0]);
        $SQL = "INSERT INTO ExpDetail SET
          ExpID ='$Exp_ID',
          SelectionID='".$tmp_arr_3[0]."',
          OptionID='".$tmp_arr_3[1]."', 
          IndexNum='$tmp_counter',
          UserID='$AccessUserID',
          DT='".@date('Y-m-j')."'";
        $new_selection_id = $HITSDB->insert($SQL);
        $tmp_counter++;
      }
      $Selected_option_str = '';
    }
    echo "<center><font color='green' face='helvetica,arial,futura' size=2>";
    echo "New Experiment has been added ($img_msg).";
    echo "</font></center>";
  
    //after insert change the action
    $theaction = "modify";
  }else {
    if($theaction == "insert") {
      echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
      echo "Missing info. <b>Bold</b> field names are requeued to make the insert.";
      echo "</font></center>";
    }
?>
   <table border="0" cellpadding="0" cellspacing="1" width="700">
    <form name=add_modify_form method=post action=<?php echo $PHP_SELF;?> enctype="multipart/form-data">
    <input type=hidden name=theaction value="insert">
    <input type=hidden name=Bait_ID value="<?php echo $Bait_ID;?>">
    <input type=hidden name=conditionString value="">
    <input type=hidden name=sub value=<?php echo $sub;?>>
    <input type=hidden name=Gel_ID value='<?php echo $Gel_ID;?>'>
    <input type=hidden name=ListType value='<?php echo $ListType;?>'>
    <input type=hidden name=searchThis value='<?php echo $searchThis;?>'>
  <tr bgcolor="<?php echo $TB_HD_COLOR;?>">
    <td colspan="2" align="center" height=20>
      <font color="white" face="helvetica,arial,futura" size="2"><b>New Experiment 
        <?php  if($sub and !$Gel_ID) echo ' --Gel Free';
           if(!$Bait_ID) echo ' -- No Bait Sample';
        ?>
        </b></font>
    </td>
  </tr>
  <?php  
  //---------------------
  include("experiment.inc.php"); 
  //---------------------
  ?>
  <input type=hidden name=Selected_option_str value="<?php echo $Selected_option_str?>">
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" align="center">
    <td colspan="2">
      <div class=maintext>
      <input type="button" value="Save" onClick="javascript:submitit();">
      </div>
    </td>
  </tr>
  </form>
  </table>
     
<?php 
  }//end of insert
}

if(($theaction == "modify" OR $theaction == "update" OR $theaction == "removeImage")){

  if($frm_Name AND $Exp_ID AND $theaction == "update"  and $AUTH->Modify){
    if($frm_GrowProtocol){
      $frm_GrowDate = $frm_Grow_Year.'-'.$frm_Grow_Month.'-'.$frm_Grow_Day;
      $frm_GrowProtocol .= ','. $frm_GrowDate;
    }
    if($frm_IpProtocol){
      $frm_IpDate = $frm_Ip_Year.'-'.$frm_Ip_Month.'-'.$frm_Ip_Day;
      $frm_IpProtocol .= ','. $frm_IpDate;
    }
    if($frm_DigestProtocol){
      $frm_DigDate = $frm_Dig_Year.'-'.$frm_Dig_Month.'-'.$frm_Dig_Day;
      $frm_DigestProtocol .= ','. $frm_DigDate;
    }
    if($frm_PeptideFrag){
      $frm_PepDate = $frm_Pep_Year.'-'.$frm_Pep_Month.'-'.$frm_Pep_Day;
      $frm_PeptideFrag .= ','. $frm_PepDate;
    }
    if(isset($_FILES['frm_Image']['name'])){
      $uploaded_file_name = $_FILES['frm_Image']['name'];
      $uploaded_file_type = $_FILES['frm_Image']['type'];
      $frm_Image = '';        
      if(strstr($uploaded_file_type,"jpeg") or strstr($uploaded_file_type,"gif")){
        $uploaded_file_name = preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
        $new_pic_name = "p".$_SESSION["workingProjectID"]."exp".$Exp_ID . "_" . $uploaded_file_name;
        if (move_uploaded_file($_FILES['frm_Image']['tmp_name'], $imageLocation . $new_pic_name)){
          $img_msg = "image was successfully uploaded";
          //$frm_Image = $new_pic_name;
          $SQL = "SELECT WesternGel FROM Experiment WHERE ID='$Exp_ID'";
          if($tmpImageArr = $mainDB->fetch($SQL)){
            if($tmpImageArr && $tmpImageArr['WesternGel']){
              $new_pic_name = $tmpImageArr['WesternGel']. "," . $new_pic_name;
            }
          }
        }else{
          $img_msg = "<font color=#FF0000>Possible file upload attack! Please try again</font>";
        }
      }else{             
        if($uploaded_file_name){
          $img_msg = "<font color=red>uploaded file is not gif nor jpeg image, please upload a gif or jpeg file.</font>";
        }else{
          $img_msg = "<font color=#FF0000>no new image uploaded</font>";
        }
      }
    }else{      
      $new_pic_name = $frm_Image;
    }
  
    $frm_Name = trim($frm_Name);
    $SQL = "UPDATE Experiment SET 
            Name='".mysqli_real_escape_string($mainDB->link, $frm_Name)."', 
            TaxID='$frm_TaxID',";
            if(!$GrowProtocolFlag){
              $SQL .= " GrowProtocol='$frm_GrowProtocol',";
            }
            if(!$IpProtocolFlag){
              $SQL .= " IpProtocol='$frm_IpProtocol',";
            }
            if(!$DigestProtocolFlag){
              $SQL .= " DigestProtocol='$frm_DigestProtocol',";
            }
            if(!$PeptideFragFlag){
              $SQL .= " PeptideFrag='$frm_PeptideFrag',";
            }
            
            if(isset($new_pic_name) && $new_pic_name){
              $SQL .=  "WesternGel='$new_pic_name',";
              $imageDes = "WesternGel=$new_pic_name";
            }else{
              $imageDes = "WesternGel=not changed";
            }              
            $SQL .= " ProjectID='$AccessProjectID',
            Notes='".mysqli_real_escape_string($mainDB->link, $frm_Notes)."',
            CollaboratorID='$frm_Collaborator'
            WHERE ID =$Exp_ID ";
    $updateFlag = $mainDB->update($SQL);
    echo "<center><font color='green' face='helvetica,arial,futura' size=3>";
    echo "Update completed ";
    if($img_msg){
      echo " ($img_msg).";
    }  
    echo "</font></center>";
    //add record into Log table
    if($updateFlag){
      $Desc = "BaitID=$Bait_ID,Name=$frm_Name,TaxID=$frm_TaxID,ConditonID=$conditionString,GrowProtocol=$frm_GrowProtocol,IpProtocol=$frm_IpProtocol,DigestProtocol=$frm_DigestProtocol,DateTime=$frm_Date,$imageDes";
    }else{
      $Desc = "update failed";
    }        
    $Log->insert($AccessUserID,'Experiment',$Exp_ID,'modify',$Desc,$AccessProjectID);
      //end of Log table
    $frm_Date =@strtotime($frm_Date);    
    
    
    $SQL = "DELETE FROM ExpDetail WHERE ExpID = '$Exp_ID'";
    $HITSDB->execute($SQL);
    
    if($Selected_option_str){
      $tmp_arr = explode("@@",$Selected_option_str);
      $tmp_counter = 1;
      foreach($tmp_arr as $tmp_val){
        $tmp_arr_2 = explode(",,",$tmp_val);
        $tmp_arr_3 = explode("_",$tmp_arr_2[0]);
        $SQL = "INSERT INTO ExpDetail SET
          ExpID ='$Exp_ID',
          SelectionID='".$tmp_arr_3[0]."',
          OptionID='".$tmp_arr_3[1]."', 
          IndexNum='$tmp_counter',
          UserID='$AccessUserID',
          DT='".@date('Y-m-j')."'";
        $new_selection_id = $HITSDB->insert($SQL);
        $tmp_counter++;
      }
      $Selected_option_str = '';
    }
    
    $theaction = "modify";
    /*echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
    echo "Update complete.<br>";
    echo "</font></center>";*/
  }else{
    if($theaction == "update") {
      echo "<center><font color='red' face='helvetica,arial,futura' size=2>";
      echo "Missing info. <b>Bold</b> field names are required to make the insert.";
      echo "</font></center><br>";
    }
  }
  if($theaction == "modify" and $Exp_ID){
    $SQL = "SELECT 
            ID, 
            BaitID, 
            Name, 
            TaxID, 
            OwnerID,
            GrowProtocol,
            IpProtocol,
            DigestProtocol,
            PeptideFrag,
            ProjectID, 
            DateTime,
            Notes,
            WesternGel,
            CollaboratorID
            FROM Experiment where  ID='$Exp_ID'";
    //echo $SQL;exit;       
           
    $Exps = $mainDB->fetch($SQL);
    $frm_Name = $Exps['Name'];
    $frm_TaxID = $Exps['TaxID'];
    $frm_OwnerID = $Exps['OwnerID'];
    $frm_GrowProtocol = $Exps['GrowProtocol'];
    $frm_IpProtocol = $Exps['IpProtocol'];
    $frm_DigestProtocol = $Exps['DigestProtocol'];
    $frm_PeptideFrag = $Exps['PeptideFrag'];
     
    $frm_Date =@strtotime($Exps['DateTime']);
    $frm_Notes = $Exps['Notes'];
    $frm_Image = $Exps['WesternGel'];
    $tmpImageNameArr = explode(",",$frm_Image);
  }
?>
    <table border="0" cellpadding="1" cellspacing="1" width="700">
      <form name=add_modify_form method=post action='<?php echo $PHP_SELF;?>' enctype="multipart/form-data">
      <input type=hidden name=theaction value=update>
      <input type=hidden name=Bait_ID value=<?php echo $Bait_ID?>>
      <input type=hidden name=Exp_ID value=<?php echo $Exp_ID;?>>
      <input type=hidden name=conditionString value="">
      <input type=hidden name=sub value=<?php echo $sub;?>>
      <input type=hidden name=Gel_ID value='<?php echo $Gel_ID;?>'>
      <input type=hidden name=change_protocol value=''>
      <tr bgcolor="<?php echo $TB_HD_COLOR;?>" height=20>
      <td colspan="2" align="center">
      <div class=tableheader>Experimental Details</div>
    </td>
  </tr>
  <?php 
  //-----------------------------------
  include("experiment.inc.php");
  //-----------------------------------
//echo $Selected_option_str."******************<br>";
  ?>
  <input type=hidden name=Selected_option_str value="<?php echo $Selected_option_str?>">
   <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" align="center">
    <td colspan="2"><div class=maintext>
<?php 
  if($AUTH->Modify){
    $hasLane = $ExpsObj->has_lanes($Exp_ID); 
    
    $hasBand = $ExpsObj->has_bands($Exp_ID);
?>
  <input type="button" value="Modify" onClick="javascript:submitit();" class="green_but">&nbsp;&nbsp;
<?php   if($hasLane && !$sub){?>      
      <a href="lane.php?theaction=viewall&Gell_ID=<?php echo $Gel_ID;?>&Exp_ID=<?php echo $Exp_ID;?><?php echo  ($sub)?"&sub=$sub":"";?>">
      <img border="0" src="images/icon_tree.gif" alt="Samples/Bands"></a>&nbsp;
  <?php }elseif($hasBand && !$sub){?>
      <a href="plate_free.php?theaction=viewband&Gell_ID=<?php echo $Gel_ID;?>&Exp_ID=<?php echo $Exp_ID;?><?php echo  ($sub)?"&sub=$sub":"";?>"> 
      <img border="0" src="images/icon_tree.gif" alt="Samples/Bands"></a>&nbsp;
<?php   }?>
<?php }?>&nbsp;
<?php if($sub){?> 
    &nbsp; &nbsp;<a href="javascript: select_exp();"><img src='images/arrow_small.gif' border=0></a>
<?php }?>
    </div>
    </td>
  </tr>
      </form>
      </table>
<?php 
} //end if
?>

    </td>
  </tr>
</table><br>
<?php 
require("site_footer.php");

function is_protocol_exist($frm_Protocol){
  global $ProtocolIdNameArr;
  $returnedValArr = array();
  $tmpProtocolArr = explode(",", $frm_Protocol);
  if(count($tmpProtocolArr) == 2){
    $returnedValArr['Name'] = '';
    if(array_key_exists($tmpProtocolArr[0], $ProtocolIdNameArr)){
      $returnedValArr['Name'] = $ProtocolIdNameArr[$tmpProtocolArr[0]];
    }
    $returnedValArr['Date'] = $tmpProtocolArr[1];
    $returnedValArr['ID'] = $tmpProtocolArr[0];
    return $returnedValArr;
  }else{
    return 0;
  }
}
?>

