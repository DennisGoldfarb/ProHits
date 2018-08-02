<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

$frm_selected_bait_str = '';
$frm_order_by = '';
$withFilter = '';
$mulFlag = 0;
$asc_desc = 'DESC';
$orderby = 'Expect';

require("../common/site_permission.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/experiment_class.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");

$BaitIDarr_have_hits = array();
$SelectedBaits = array();
$tmpBaitArr = array();
if($frm_order_by != 'ID' and $frm_order_by != 'GeneName' and $frm_order_by != 'BaitAcc'){
  $frm_order_by_1 = $frm_order_by;
}else{
  $frm_order_by_1 = "ID DESC";
}

if($frm_selected_bait_str){
  $SQL = "SELECT `ID`,`GeneID`,`GeneName`, `BaitAcc`,`TaxID`,`Clone` FROM `Bait` 
          WHERE `ProjectID`='$AccessProjectID' AND ID IN($frm_selected_bait_str) 
          ORDER BY $frm_order_by_1";
  $SelectedBaits = $HITSDB->fetchAll($SQL);
  foreach($SelectedBaits as $value) array_push($tmpBaitArr,$value['ID']);
}

$bait_group_icon_arr = get_project_noteType_arr($HITSDB);

$SQL = "SELECT `BaitID` FROM `Hits` GROUP BY `BaitID` ORDER BY `BaitID` DESC";
$results = $HITSDB->fetchAll($SQL);
foreach($results as $theID){
  array_push($BaitIDarr_have_hits, $theID['BaitID']);
}

$Baits = array();
if($theaction != 'step2' or $theaction != 'addExp'){
  $SQL = "SELECT `BaitID` FROM `Hits` GROUP BY `BaitID` ORDER BY `BaitID` DESC";
  $BaitIDarr2 = $HITSDB->fetchAll($SQL);
  $SQL = "SELECT `BaitID` FROM `BaitDiscussion` WHERE `NoteType`='1' GROUP BY `BaitID`";
  $BaitDiscussion2 = $HITSDB->fetchAll($SQL);
  $BaitsFailedArr = array();
  foreach($BaitDiscussion2 as $failedBaitValue){
    array_push($BaitsFailedArr, $failedBaitValue['BaitID']);
  }
  $BaitIDstr = '';
  foreach($BaitIDarr2 as $BaitIDvalue){
    if(!in_array($BaitIDvalue['BaitID'], $tmpBaitArr) && !in_array($BaitIDvalue['BaitID'], $BaitsFailedArr)){
      if($BaitIDstr) $BaitIDstr .= "','";
      $BaitIDstr .= $BaitIDvalue['BaitID'];
    }  
  }
  if(!$frm_order_by) $frm_order_by = 'ID DESC';
  $SQL = "SELECT `ID`,`GeneName`, `BaitAcc`,`Clone` FROM `Bait` 
          WHERE `ProjectID`='$AccessProjectID' ";
  if($frm_selected_bait_str){
    $SQL .= "AND ID NOT IN($frm_selected_bait_str) ";
  } 
  if($frm_order_by == 'ID' or $frm_order_by == 'GeneName' or $frm_order_by == 'BaitAcc'){
    $SQL .= "ORDER BY $frm_order_by";
  }else{
    $SQL .= "ORDER BY ID DESC";
  }
  $Baits = $HITSDB->fetchAll($SQL);
  /*if($BaitIDstr){
    $BaitIDstr = "'" . $BaitIDstr . "'";
    if(!$frm_order_by) $frm_order_by = 'ID DESC';
    $SQL = "SELECT `ID`,`GeneName`, `BaitAcc`,`Clone` FROM `Bait` 
            WHERE `ProjectID`='$AccessProjectID' AND ID IN($BaitIDstr) 
            ORDER BY $frm_order_by";
    $Baits = $HITSDB->fetchAll($SQL);
  }*/
}

$selected_exp_str = '';
$selected_exp_arr = array();
$expNohitBaitIdArr = array();
for($i=0; $i<count($SelectedBaits); $i++){
  $SQL = "SELECT 
     ID 
     FROM Experiment 
     WHERE BaitID = '". $SelectedBaits[$i]['ID']."'
     AND ProjectID='$AccessProjectID'";
  $Exps = $HITSDB->fetchAll($SQL);
  $tmpCounter = 0;
  foreach($Exps as $ExpValue){
    $SQL = "SELECT 
            H.ID 
            FROM Band B, Hits H
            WHERE B.ID = H.BandID
            AND B.ExpID='".$ExpValue['ID']."' Limit 1";
    $temHits = $HITSDB->fetchAll($SQL);
    if(count($temHits)){
      array_push($selected_exp_arr, $ExpValue['ID']);
      $tmpCounter++;
      if($tmpCounter > 1 && !$mulFlag){
        $mulFlag = 1;
      } 
    }
  }
//-----------------------------------------	
	if(!$tmpCounter){
		array_push($expNohitBaitIdArr, $SelectedBaits[$i]['ID']);
	}
//----------------------------------	  
}
//echo "\$mulFlag: ".$mulFlag."<br>";
//echo "\$withFilter: ".$withFilter."<br>";
if($selected_exp_arr){
  $selected_exp_str = implode(",", $selected_exp_arr);
}

$SQL = "SELECT `BaitID`, `NoteType` FROM `BaitDiscussion` WHERE `NoteType`<>'0' order by BaitID, NoteType";
$BaitDiscussion2 = $HITSDB->fetchAll($SQL);
$BaitNotesArr = array(); 
 
foreach($BaitDiscussion2 as $theNotes){
  if(!isset($BaitNotesArr[$theNotes['BaitID']])){
    $BaitNotesArr[$theNotes['BaitID']] = array();
  }
  array_push($BaitNotesArr[$theNotes['BaitID']], $theNotes['NoteType']);
}

require("site_header.php");
$bg_tb_header = '#7eb48e';
$bg_tb = '#cee3da';

?>
<script language="javascript">
var newWin;
function changeOrderBy(){
  theForm = document.form_comparison;
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm.theaction.value = 'short';
  theForm.withFilter.value = '';
  if(is_checked() == 1){
    theForm.withFilter.value = 'Y';
  }
  theForm.action="<?php echo $PHP_SELF;?>"
  theForm.submit();
}
function nextStep(){
  theForm = document.form_comparison;
  if(theForm.frm_selected_bait.length <= 1){
    alert("Please select at lease one bait.");
    return;
  }
  selObj = theForm.frm_selected_bait;
	var selected_id_str = '';
  for(var i=1; i<selObj.length; i++){
    if(selected_id_str != "") selected_id_str += ',';
		selected_id_str += selObj[i].value;
  } 
  theForm.frm_selected_bait_str.value = selected_id_str;
  if(is_checked() == '1'){
    theForm.action = 'comparison.php';
    theForm.theaction.value = 'step2';
  }else{
    theForm.action = 'comparison_report_nofilter_pop.php';
    //theForm.theaction.value = 'first_time';
    theForm.source.value = 'comparison';
    theForm.frm_selected_exp_str.value = '';
    file = 'loading.html';
    newWin = window.open(file,"subWin",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=1100,height=800');
    newWin.focus();
    theForm.target = 'subWin';
  } 
  theForm.submit(); 
}

function is_checked(){
  theForm = document.form_comparison;
  if(theForm.withFilter.checked == true){
    return 1;
  }else{
    return 0;
  }
}

function previous_step(theaction){
  theForm = document.form_comparison;
  theForm.action = '<?php echo $PHP_SELF;?>';
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm.theaction.value = theaction;
  theForm.submit();
}

function addBait(){
  theForm = document.form_comparison;
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm = document.form_comparison;
  theForm.action = '<?php echo $PHP_SELF;?>';
  selObj = theForm.frm_baitList;
  var tmpSel_str = '';
  for(var i=1; i<selObj.length; i++){
    if(selObj[i].selected == true){
      if(tmpSel_str != '') tmpSel_str += ',';
      tmpSel_str += selObj[i].value;
    }
  }
  if(tmpSel_str == ''){
    alert('Please select a bait to add from bait list box!');
    return 0;
  }else{
    if(theForm.frm_selected_bait_str.value != '') theForm.frm_selected_bait_str.value += ','
    theForm.frm_selected_bait_str.value += tmpSel_str;
  }
  theForm.theaction.value = 'addBait';
  theForm.withFilter.value = '';
  if(is_checked() == 1){
    theForm.withFilter.value = 'Y';
  }
  theForm.submit();
}

/*function removeBait(){
  theForm = document.form_comparison;
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm.action = '<?php echo $PHP_SELF;?>';
  selObj = theForm.frm_selected_bait;
  var tmpSel_arr = theForm.frm_selected_bait_str.value.split(",");
  var atLeaseOne = 0;
  for(var i=1; i<selObj.length; i++){
    if(selObj[i].selected == true){
      atLeaseOne = 1;
      for(var j=0; j<tmpSel_arr.length; j++){
        if(selObj[i].value == tmpSel_arr[j]){
          tmpSel_arr.splice(j, 1);
          break;
        }
      }
    }
  }
  if(atLeaseOne == 1){
    theForm.frm_selected_bait_str.value = tmpSel_arr.join(",");
  }else{
    alert('Please select a bait to remove from the selected bait box!');
    return 0;
  }
  theForm.theaction.value = 'removeBait';
  theForm.withFilter.value = '';
  if(is_checked() == 1){
    theForm.withFilter.value = 'Y';
  }
  theForm.submit();
}*/

function removeBait(){
  theForm = document.form_comparison;
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm.action = '<?php echo $PHP_SELF;?>';
  selObj = theForm.frm_selected_bait;
  var atLeaseOne = 0;
	var selected_id_str = '';
  for(var i=1; i<selObj.length; i++){
    if(selObj[i].selected == true){
      atLeaseOne = 1;
		}else{
			if(selected_id_str != "") selected_id_str += ',';
			selected_id_str += selObj[i].value;
    }
  }
  if(atLeaseOne == 0){
    alert('Please select a bait to remove from the selected bait box!');
    return 0;
  }
	theForm.frm_selected_bait_str.value = selected_id_str;
  theForm.theaction.value = 'removeBait';
  theForm.withFilter.value = '';
  if(is_checked() == 1){
    theForm.withFilter.value = 'Y';
  }
  theForm.submit();
}

function createSelectedBaitStr(theForm){
  //don't include the selected one
  var str = '';
  var selObj;
  selObj = theForm.frm_selected_bait;
  for (i=1; i < selObj.options.length; i++) {
    if(selObj.selectedIndex != i){
      if(str.length > 0){
        str = str + ',';
      }
      str = str + selObj.options[i].value;
    }
  }
  theForm.frm_selected_bait_str.value = str;
}

<?php if($theaction == 'step2'){?>
function createSelectedExpStr(theForm){
  var str = '';
  var selObj
  var notselected;
  <?php for($i=0; $i<count($SelectedBaits); $i++){
  echo "\n    selObj = theForm.frm_expList$i;\n";
  ?>
    notselected = true;
    for (i=0; i < selObj.options.length; i++) {
      if(selObj.options[i].selected){
        if(str.length > 0){
          str = str + ',';
        }
        str = str + selObj.options[i].value;
        notselected = false;
      }
    }
  if(notselected){
    alert('Please select experiment from bait <?php echo $SelectedBaits[$i]['ID'];?>');
    return notselected;
  }
  <?php }?>
  theForm.frm_selected_exp_str.value = str;
  return notselected;
}
<?php }else{?>
function createSelectedExpStr(theForm){
  var selected_obj = theForm.frm_selected_bait;
  var has_item = 0;
  selObj = theForm.frm_selected_bait;
	var selected_id_str = '';
  for(var i=1; i<selObj.length; i++){
    if(selected_id_str != "") selected_id_str += ',';
		selected_id_str += selObj[i].value;
  } 
  theForm.frm_selected_bait_str.value = selected_id_str;
  for(i=0;i<selected_obj.length;i++){
    if(selected_obj.options[i].value != ''){
      return false;
    }
  }
  alert("Please pass a bait.");
  return true;
}
<?php }?>
function generateReport(){
  theForm = document.form_comparison;
  if(createSelectedExpStr(theForm)){
    return 0;
  }
  theForm.theaction.value = 'report';
  if(theForm.withFilter.type == 'hidden'){
    theForm.action = 'comparison_report_pop.php';
  }else{
    if(is_checked() == 1){
      theForm.action = 'comparison_report_pop.php';
    }else{
      selObj = theForm.frm_selected_bait;
      var typeStr = '';
      for(var i=1; i<selObj.length; i++){
        if(selObj[i].id == ''){
          typeStr = '';
          break;
        }else{
          if(typeStr != '') typeStr
          typeStr += selObj[i].id;
        }
      }
      if(typeStr != '') theForm.typeStr.value = typeStr;
      theForm.action = 'comparison_report_nofilter_pop.php';
      theForm.frm_selected_exp_str.value = '';
      //theForm.theaction.value = 'first_time';
      theForm.source.value = 'comparison';
    }
  }
  file = 'loading.html';
  newWin = window.open(file,"subWin",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=1100,height=800');
  newWin.focus();
  theForm.target = 'subWin';
  theForm.submit();
}
function is_filter(){
  theForm = document.form_comparison;
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }
  theForm = document.form_comparison;
  theForm.action = '<?php echo $PHP_SELF;?>'; 
  theForm.submit();
}
</script>
<FORM ACTION="<?php echo $PHP_SELF;?>" NAME="form_comparison" METHOD="POST">
<INPUT TYPE="hidden" NAME="theaction" VALUE="">
<INPUT TYPE="hidden" NAME="source" VALUE="">
<INPUT TYPE="hidden" NAME="frm_selected_bait_str" VALUE="<?php echo $frm_selected_bait_str?>">
<INPUT TYPE="hidden" NAME="typeStr" VALUE="">
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td colspan=2>&nbsp;
    </td>
  </tr>
  <tr>
    <td align="left" colspan=2>
    &nbsp; <font color="navy" face="helvetica,arial,futura" size="4"><b>Report By Comparison</b></font>
<?php     
    if($AccessProjectName){
        echo "  <font color='red' face='helvetica,arial,futura' size='3'><b>(Project: $AccessProjectName)</b></font>";
    }
?>     
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align=center colspan=2><br>
    
<?php if($theaction != 'step2'){
//this is setp 1
?>

    <font color=#0000cd><b>Step 1: Select Bait</b></font> >> Step 2: Select Experiment >> Step 3: Generate Report<br><br>
    <table border="1" width="800" height="50" cellspacing="1" cellpadding=3 >
    <tr>
      <td width="33%" BGCOLOR="<?php echo $bg_tb;?>" align=center>
      <font size="2" face="Arial"><b>Bait List</b><br>BaitID GeneName ProteinID<br>
       <select name="frm_baitList" size=20 multiple>
         <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
        <?php 
        $option_str1 = "";
         $option_str2 = "";
         for($i=0; $i<count($Baits); $i++){
           $tmpBaitID = $Baits[$i]['ID'];
           if(!in_array($tmpBaitID, $BaitIDarr_have_hits)) continue;
           $tmp2top = false;
           $initial_str = '';
           if(isset($BaitNotesArr[$tmpBaitID])){
             foreach($BaitNotesArr[$tmpBaitID] as $tmpTypeID){  
              $initial_str .= "[".$bait_group_icon_arr[$tmpTypeID]['Initial']."]";
             }
           }
           if(intval($frm_order_by)>0 and $initial_str){
             if(in_array($frm_order_by, $BaitNotesArr[$tmpBaitID])){
              $tmp2top = true;
               
             }
           }
           if($tmp2top){
              
              $option_str1 .= "<option value='".$Baits[$i]['ID']."'>".$Baits[$i]['ID']."&nbsp; &nbsp;".$Baits[$i]['GeneName']."&nbsp; &nbsp;".$Baits[$i]['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
           }else{
               
              $option_str2 .= "<option value='".$Baits[$i]['ID']."'>".$Baits[$i]['ID']."&nbsp; &nbsp;".$Baits[$i]['GeneName']."&nbsp; &nbsp;".$Baits[$i]['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
           }
         }
         echo $option_str1 . $option_str2;
        
         /*for($i=0; $i<count($Baits); $i++){
           echo "<option value='".$Baits[$i]['ID']."'>".$Baits[$i]['ID']."&nbsp; &nbsp;".$Baits[$i]['GeneName']."&nbsp; &nbsp;".$Baits[$i]['BaitAcc']."\n";
         }*/
        ?>
       </select><br><br>
       <b>Sort by:</b>
       <select name="frm_order_by" onChange="changeOrderBy()">
        <option value="ID DESC" <?php echo ($frm_order_by=='ID DESC')?'selected':'';?>>ID</option>
        <option value="GeneName" <?php echo ($frm_order_by=='GeneName')?'selected':'';?>>Gene Name</option>
        <option value="BaitAcc" <?php echo ($frm_order_by=='BaitAcc')?'selected':'';?>>Protein ID</option>
        <?php 
        foreach($bait_group_icon_arr as $key =>$rd){
          $selected = ($frm_order_by == $key)?" selected":"";
          echo "<option value='".$key."'$selected>".$rd['Name']." (".$rd['Initial'].")</option>\n";
        }
        ?> 
      </select><br><br>
      </td>
      <td width="18%"  BGCOLOR="<?php echo $bg_tb;?>" valign=center>
      <font size="2" face="Arial">
      <center>
      
      <input type=button value='&nbsp;&nbsp;   > >  &nbsp;&nbsp;' onClick="addBait()">
      <br><br>
      <input type=button value='&nbsp;&nbsp;   < <  &nbsp;&nbsp;' onClick="removeBait()">
      </center>
      </font> 
      </td>
      <td width="33%" BGCOLOR="<?php echo $bg_tb;?>" align=center valign=top>
      <font size="2" face="Arial"><b>Selected baits</b><br>BaitID GeneName ProteinID<br>
      <select name="frm_selected_bait" size=20 multiple>
         <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
        <?php 
          $typeArr = array(); 
             for($i=0; $i<count($SelectedBaits); $i++){
               $tmpBaitID = $SelectedBaits[$i]['ID'];
               if(in_array($tmpBaitID, $expNohitBaitIdArr)) continue;
               $initial_str = '';
               if(isset($BaitNotesArr[$tmpBaitID])){
                 foreach($BaitNotesArr[$tmpBaitID] as $tmpTypeID){  
                  $initial_str .= "[".$bait_group_icon_arr[$tmpTypeID]['Initial']."]";
                 }
               }
               echo "<option value='".$SelectedBaits[$i]['ID']."' id='".$initial_str."'>".$SelectedBaits[$i]['ID']."&nbsp; &nbsp;".$SelectedBaits[$i]['GeneName']."&nbsp; &nbsp;".$SelectedBaits[$i]['BaitAcc']."&nbsp;&nbsp;$initial_str\n";
               array_push($typeArr, $SelectedBaits[$i]['ID'].";;".$initial_str);
             }
        ?>
       </select><br><br>
       <input type=checkbox name=withFilter value='Y' <?php echo ($withFilter=='Y')?'checked':''?> onclick="is_filter();">&nbsp;<font size="2" face="Arial"><b>With filter</b></font>
      </td>
      <?php 
      if(!$withFilter){
      ?>
      <td width="33%" BGCOLOR="<?php echo $bg_tb;?>" align=center valign=top >
      <font size="2" face="Arial"><b>Sort by:</b><br>&nbsp;&nbsp;<br>
      <select name="sort_by_bait_id" size=1>
         <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
        <?php 
          $typeArr = array(); 
             for($i=0; $i<count($SelectedBaits); $i++){
               $tmpBaitID = $SelectedBaits[$i]['ID'];
               $initial_str = '';
               if(isset($BaitNotesArr[$tmpBaitID])){
                 foreach($BaitNotesArr[$tmpBaitID] as $tmpTypeID){  
                  $initial_str .= "[".$bait_group_icon_arr[$tmpTypeID]['Initial']."]";
                 }
               }
               echo "<option value='".$SelectedBaits[$i]['ID']."'>".$SelectedBaits[$i]['ID']."&nbsp; &nbsp;".$SelectedBaits[$i]['GeneName']."&nbsp; &nbsp;".$SelectedBaits[$i]['BaitAcc']."&nbsp;&nbsp;$initial_str\n";
               array_push($typeArr, $SelectedBaits[$i]['ID'].";;".$initial_str);
             }
        ?>
       </select><br><br>
       <table border="0" width="150" height="50" cellspacing="0" cellpadding=0 >
       <tr>
       <td align=left>
       <input type=radio name=orderby value='Expect' <?php echo (isset($orderby) && $orderby=='Expect')?'checked':''?>>&nbsp;<font size="2" face="Arial"><b>Score</b></font><br>
       <input type=radio name=orderby value='Pep_num' <?php echo (isset($orderby) && $orderby=='Pep_num')?'checked':''?>>&nbsp;<font size="2" face="Arial"><b>Pep_num</b></font><br>
       <input type=radio name=orderby value='Pep_num_uniqe' <?php echo (isset($orderby) && $orderby=='Pep_num_uniqe')?'checked':''?>>&nbsp;<font size="2" face="Arial"><b>Pep_num_uniqe</b></font><br>
       <input type=radio name=orderby value='Coverage' <?php echo (isset($orderby) && $orderby=='Coverage')?'checked':''?>>&nbsp;<font size="2" face="Arial"><b>Coverage</b></font><br>
       <input type=radio name=orderby value='Fequency' <?php echo (isset($orderby) && $orderby=='Fequency')?'checked':''?>>&nbsp;<font size="2" face="Arial"><b>Frequency</b></font><br><br> 
       <input type=radio name=asc_desc value='DESC' <?php echo (isset($asc_desc) && $asc_desc=='DESC')?'checked':''?>>&nbsp;<font size="2" face="Arial"><b>DESC</b></font><br>
       <input type=radio name=asc_desc value='ASC' <?php echo (isset($asc_desc) && $asc_desc=='ASC')?'checked':''?>>&nbsp;<font size="2" face="Arial"><b>ASC</b></font><br>
       </td>
       <?php }?>
       </tr>
       </table>
      </td>
    </tr>
    </table><br>
    <INPUT TYPE="hidden" NAME="frm_selected_exp_str" VALUE="<?php echo $selected_exp_str?>">
  <?php if($mulFlag){?>
    <input type=button value=' Step 2 ' onClick="nextStep()" class=green_but>
  <?php }else{?>
    <input type=button value=' Step 2 ' onClick="generateReport()" class=green_but>
  <?php }?>  
<?php }else{
//this is step 2
?>
    <font size=2 face=Arial>1: Select Bait >><font color=#0000cd><b> Step 2: Select Experiment</b></font> >> Step 3: Generate Report<br><br></font></div>
    <table border="0" width="600" height="50" cellspacing="1" cellpadding=3 >
<?php   
    $userFullNameArr = get_users_ID_Name($HITSDB);
    //print_r($userFullNameArr);
    for($i=0; $i<count($SelectedBaits); $i++){
      $SQL = "SELECT 
         ID,
         Name,
         OwnerID,
         DateTime 
         FROM Experiment
         WHERE BaitID = '". $SelectedBaits[$i]['ID']."' 
         AND ProjectID='$AccessProjectID' ORDER BY ID";
       $Exps = $HITSDB->fetchAll($SQL);
       /*echo "<pre>";
       print_r($Exps);
       echo "</pre>";
       echo "<pre>";
       print_r($selected_exp_arr);
       echo "</pre>";//exit;*/
?>
     <tr>
      <td width="40%" BGCOLOR="<?php echo $bg_tb;?>" valign=top><font size=2 face=Courier>
<?php 
        echo "<b>Bait &nbsp;&nbsp;ID</b>: ". $SelectedBaits[$i]['ID']."";
        echo "\n<br><b>Gene name: </b>". $SelectedBaits[$i]['GeneName']."";
        $Species = get_TaxID_name($HITSDB, $SelectedBaits[$i]['TaxID']);
        echo "\n<br><b>Species  &nbsp;: </b>".$Species."";
        $GIarr = getGI($HITSDB, $SelectedBaits[$i]['GeneID']);
        echo "\n<br><b>GI number: </b>" . ((count($GIarr))?$GIarr[0]['GI']:'');
        echo "\n<br><b>Clone &nbsp; &nbsp;: </b>". $SelectedBaits[$i]['Clone']."";
?>     
      </font></td>
      <td width="60%" BGCOLOR="<?php echo $bg_tb;?>" align=>Experiment: <br>
        <select name="frm_expList<?php echo $i;?>" size=5 multiple>
        <?php 
        for($k=0; $k<count($Exps); $k++){
          if(in_array($Exps[$k]['ID'], $selected_exp_arr)){
             $theUser = isset($userFullNameArr[$Exps[$k]['OwnerID']])?$userFullNameArr[$Exps[$k]['OwnerID']]:'';
             echo "<option value='".$Exps[$k]['ID']."'>".$Exps[$k]['Name']."&nbsp; &nbsp;".$Exps[$k]['DateTime']."&nbsp; &nbsp;$theUser\n";
          }
        }
        ?>
       </select>
      </td>
     </tr>
<?php    } //end for?>
    </table><br>
    <INPUT TYPE="hidden" NAME="withFilter" VALUE="Y">
    <INPUT TYPE="hidden" NAME="frm_selected_exp_str" VALUE="">
    <input type=hidden name=frm_order_by value='<?php echo $frm_order_by?>'>
    <input type=reset value=' Previous ' onClick="previous_step('addBait')" class=green_but> &nbsp; &nbsp;
    <input type=reset value=' Reset ' class=green_but> &nbsp; &nbsp;
    <input type=button value=' Step 3 ' onClick="generateReport()" class=green_but>
<?php } //end if?>
    </td>
  </tr>
</table>
</form>
<?php 
require("site_footer.php");
?>

