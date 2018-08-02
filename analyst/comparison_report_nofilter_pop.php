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

ini_set("memory_limit","-1");
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);  // it will execute for 24 hours

$theaction = '';
$sqlOrderby = '';
$orderby = "Expect";
$sort_by_bait_id = '';
$asc_desc = 'DESC';
$maxScore = 0;

$subPopWinSize = 35;
if($subPopWinSize%2) $subPopWinSize = $subPopWinSize + 1;
$baitsNumLimit = 40;
$overallWidth =800;
$overallHeight =700;


$power = 1;
$maxScore = 0;
$colorSet = '';
$typeStr = '';
$passedTypeStr = '';

$powerArr['Expect'] = 1/2;
$powerArr['Pep_num'] = 1/2;
$powerArr['Pep_num_uniqe'] = 1;
$powerArr['Coverage'] = 1;     
$powerArr['Fequency'] = 1;

$baitlableMaxL = 0;
$baitLableArr = array();
$source = '';
$totalHits = '';

$hasGeneID = 0;
$hasProteinID = 0;
$hasLocusTag = 0;

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

$comparisonDir = "../TMP/comparison/";
if(!_is_dir($comparisonDir)) _mkdir_path($comparisonDir);
$subDir = "../TMP/comparison/P_$AccessProjectID/";
if(!_is_dir($subDir)) _mkdir_path($subDir);
$baitsLableFileName = $subDir.$AccessUserID."_baits_lable.txt";
$hitsIndexFileName = $subDir.$AccessUserID."_hits_index.txt";
$hitsNameFileName = $subDir.$AccessUserID."_hits_name.txt";

$PROTEINDB = new mysqlDB(PROHITS_PROTEINS_DB);
$baitIdIndexArr = array();
$hitsGeneIdIndexArr = array();
$baitIdIndexArr = explode(',', $frm_selected_bait_str);
rsort($baitIdIndexArr);
$totalBaits = count($baitIdIndexArr);
//echo $totalBaits."<br>";
if(!isset($gif_y) && isset($tmpgif_y) && $tmpgif_y != ''){
  $gif_x = $tmpgif_x;
  $gif_y = $tmpgif_y;
}

if($totalBaits <= $baitsNumLimit){
	$theaction = "showNormal";
}else{
	if(isset($gif_y)){
		if($subPopWinSize > $totalBaits) $subPopWinSize = $totalBaits;
		$theaction = "popWindow";
	}else{
		$theaction = "showImage";
	}
}

$typeInitIdArr = array();
$typeCounArr = array();
$SQL = "SELECT `ID`,`Initial` FROM `NoteType` WHERE `ProjectID`=$AccessProjectID";
$noteTypeArr = $HITSDB->fetchAll($SQL);
foreach($noteTypeArr as $noteTypeValue){
  $typeCounArr[$noteTypeValue['Initial']] = 0;
  $typeInitIdArr[$noteTypeValue['Initial']] = $noteTypeValue['ID'];
}
$passedTypeArr = array();

if($typeStr){
  $tmpTypeArr = explode(',,', $typeStr);
  foreach($tmpTypeArr as $tmpTypeValue){
    $tmpSubStr = substr($tmpTypeValue, 1, strlen($tmpTypeValue)-2);
    $tmpSubArr = explode('][', $tmpSubStr);
    foreach($tmpSubArr as $tmpSubValue){
      if(array_key_exists($tmpSubValue, $typeCounArr)) $typeCounArr[$tmpSubValue] += 1;
    }
  }
  foreach($typeCounArr as $typeKey => $typeValue){
    if($typeValue == $totalBaits) $passedTypeArr[$typeInitIdArr[$typeKey]] = $typeKey; //array_push($passedTypeArr, $typeKey);
  }
  if(count($passedTypeArr)){
    foreach($passedTypeArr as $typeKey => $typeValue){
      if($passedTypeStr) $passedTypeStr .= '#';
      $passedTypeStr .= $typeKey.','.$typeValue;
    }
  }
}else{
  if($passedTypeStr){
    $tmppassedTypeArr = explode('#', $passedTypeStr);
    foreach($tmppassedTypeArr as $tmpValue){
      list($tmpKey,$tmpVlue) = explode(',', $tmpValue);
      $passedTypeArr[$tmpKey] = $tmpVlue;
    }
  }  
}

if($theaction != "popWindow"){
	$SQL = "SELECT `ID`,`GeneID`,`GeneName`,`LocusTag` FROM `Bait` WHERE `ID` IN($frm_selected_bait_str) ORDER BY `ID` DESC";
  $baitsArr = $HITSDB->fetchAll($SQL);
	if($theaction == "showImage" && $source == 'comparison'){
		$baitsLableArr_handle = fopen($baitsLableFileName, "w");
	}
	foreach($baitsArr as $baitsValue){
	  if($baitsValue['GeneName'] && $baitsValue['GeneName'] != "-"){
	    $baitLable = $baitsValue['GeneName'];
	  }elseif($baitsValue['LocusTag'] && $baitsValue['LocusTag'] != "-"){
	    $baitLable = $baitsValue['LocusTag'];
	  }else{
	    $baitNoName++;
	    $baitLable = "noName_".$baitNoName;
	  }
		$baitLable = $baitLableArr[$baitsValue['ID']] = $baitsValue['ID'].'_'.$baitLable;
    if($theaction == "showImage" && $source == 'comparison'){
		  fwrite($baitsLableArr_handle, $baitLable."\r\n");
    }
  	if(strlen($baitLable) > $baitlableMaxL) $baitlableMaxL = strlen($baitLable);
	}
}
//=================================================================================================
// determine color set and power*****
if($orderby == 'Fequency' || in_array($orderby, $passedTypeArr)){
  $sqlOrderby = "ID";
  $powerColorIndex = 'Fequency'; //--subFequency's power and coler Index are same as fequency
}else{
  $sqlOrderby = $orderby;
  $powerColorIndex = $orderby;
}
$power = $powerArr[$powerColorIndex]; //1/2, 1/3, 1......
//Fequency and subFequency========================================================================
$FeqIndexArr = array();
$FeqValueArr = array();
$fequMaxScore = get_fequency($FeqIndexArr, $FeqValueArr); //-create a fequency sorted index and values arrays.

$subFeqIndexArr = array();
$subFeqValueArr = array();
$typeNum = '';
$subFequMaxScore =0;
if(count($passedTypeArr)){
	if(array_key_exists($orderby, $typeInitIdArr)){
  	$typeNum = $typeInitIdArr[$orderby];
	}
  $subFequMaxScore = get_subFequency($subFeqIndexArr, $subFeqsValueArr,$passedTypeArr, $typeNum);
} 
//**get maxscore*******************
if($orderby == 'Fequency'){
	$maxScore = $fequMaxScore;
}elseif(in_array($orderby, $passedTypeArr)){
	$typeNumber = $typeInitIdArr[$orderby];
	$maxScore = $subFequMaxScore;
}else{
	$SQL = "SELECT MAX(".$orderby.") as biggestNum FROM Hits WHERE BaitID IN($frm_selected_bait_str)";
	$hitsArrTmp2 = $HITSDB->fetch($SQL);
	$maxScore = $hitsArrTmp2['biggestNum'];
}
$biggestPowedSore = pow($maxScore,$power);
if($biggestPowedSore <= 0) $biggestPowedSore = 1;
//===============================================================================================
//**caculate totalHits format image size get

if($theaction == "showImage"){
	if($source == 'comparison'){
		$SQL = "SELECT GeneID FROM Hits WHERE (GeneID!=0 AND GeneID IS NOT NULL AND GeneID !='') AND BaitID IN($frm_selected_bait_str) GROUP BY GeneID";
		//echo $SQL; 
    $hitsArrTmp = $HITSDB->fetchAll($SQL);
		$subTotalHits = count($hitsArrTmp);
		$SQL = "SELECT HitGI FROM Hits WHERE (GeneID=0 OR GeneID IS NULL OR GeneID ='') AND BaitID IN($frm_selected_bait_str) GROUP BY HitGI";
		//echo $SQL; exit;
    $hitsArrTmp2 = $HITSDB->fetchAll($SQL);
		$subTotalHits2 = count($hitsArrTmp2);
		$totalHits = $subTotalHits + $subTotalHits2;
		
		$cellH = 0;
		$cellW = 0;
		$fontSize = '';
		$labalH = '';
		$fontH = '';
		format_image();//-get real $overallWidth, $overallHeight
	  $argumentsStr = $overallWidth.','.$overallHeight.','.$cellH.','.$cellW.','.$labalH.','.$fontSize.','.$fontH.'\r\n';
		fwrite($baitsLableArr_handle, $argumentsStr."\r\n");
    $_SESSION["passedTypeArr"] = $passedTypeArr;
    $_SESSION["typeInitIdArr"] = $typeInitIdArr;
	}
}else{
	$hitsNameArr = array();
	$baitNameArr = array();
	$baitNoName = 0;

	if($theaction == "popWindow"){
		//-get data from files create $hitsGeneIdIndexArr
	  $tmpHitsIndexArr = file($hitsIndexFileName);
	  $hitsGeneIdIndexArr = array();
	  foreach($tmpHitsIndexArr as $tmpHitsIndexValue){
	    array_push($hitsGeneIdIndexArr, trim($tmpHitsIndexValue));
	  }
    $totalHits = count($hitsGeneIdIndexArr);
    $tmpHitsNameArr = file($hitsNameFileName);
    foreach($tmpHitsNameArr as $tmpHitsNameValue){
      $tmpHitsNameValue = trim($tmpHitsNameValue);
      list($id, $temValue) = explode('::',$tmpHitsNameValue);
	    $hitsNameArr[$id] = $temValue;
	  }
	  if(isset($_SESSION["labalH"])){
	    $labalH = $_SESSION["labalH"];
	  }else{
	    $labalH =0;
	  }
	  $totalHits = count($hitsGeneIdIndexArr);
	  $I_index = round((($gif_y-$labalH)/$cellH) - 0.5);
	  $J_index = round(($gif_x / $cellW) - 0.5);
	  if($totalHits <= $subPopWinSize && $totalBaits <= $subPopWinSize){
	    $start_I_index = 0;      
	    $start_J_index = 0;
	  }elseif($totalHits > $subPopWinSize && $totalBaits <= $subPopWinSize){
	    $start_I_index = start_index($I_index, $subPopWinSize, $totalHits);
	    $start_J_index = 0;
	  }elseif($totalHits <= $subPopWinSize && $totalBaits > $subPopWinSize){
	    $start_I_index = 0;
	    $start_J_index = start_index($J_index, $subPopWinSize, $totalBaits);
	  }else{
	    $start_I_index = start_index($I_index, $subPopWinSize, $totalHits);
	    $start_J_index = start_index($J_index, $subPopWinSize, $totalBaits);
	  }
	  $end_I_index = $start_I_index + $subPopWinSize;
	  $end_J_index = $start_J_index + $subPopWinSize;
	}else{
	  $start_J_index = 0;
	  $end_J_index = count($baitIdIndexArr);
	}
  
	$firstHitsArr = array();
	for($j=$start_J_index; $j<$end_J_index; $j++){
	  $SQL = "SELECT GeneID,
	                 LocusTag,
	                 HitGI,
	                 Pep_num,
	                 Pep_num_uniqe,
	                 Coverage,
	                 Expect,
	                 Expect2,
	                 SearchEngine
	                 FROM Hits
	                 WHERE BaitID='".$baitIdIndexArr[$j]."'";
	  $SQL .= " ORDER BY ".$sqlOrderby." DESC";
	  $hitsArrs = $HITSDB->fetchAll($SQL);
	  if(!count($hitsArrs)) continue;
	  if($asc_desc == 'ASC') $hitsArrs = array_reverse($hitsArrs);

		if($theaction == "popWindow"){
		  $SQL = "SELECT `ID`,`GeneID`,`GeneName`, `LocusTag` FROM `Bait` WHERE `ID`='".$baitIdIndexArr[$j]."'";
		  $baitArr = $HITSDB->fetch($SQL);
		  if($baitArr['GeneName'] && $baitArr['GeneName'] != "-"){
		    $baitLable = $baitArr['GeneName'];
		  }elseif($baitArr['LocusTag'] && $baitArr['LocusTag'] != "-"){
		    $baitLable = $baitArr['LocusTag'];
		  }else{
		    $baitNoName++;
		    $baitLable = "noName_".$baitNoName;
		  }
      $baitLable = $baitArr['ID'].'_'.$baitLable;
		  $baitLableArr[$baitIdIndexArr[$j]] = $baitLable;
		  if(strlen($baitLable) > $baitlableMaxL) $baitlableMaxL = strlen($baitLable);
		}
	
	  $tmpHitsArr = array();
	  foreach($hitsArrs as $hitsArrValue){
			if(!$hasGeneID) $hasGeneID = 1;
			if(!$hasProteinID) $hasProteinID = 1;
      if(!$hasLocusTag && $hitsArrValue['LocusTag'] && $hitsArrValue['LocusTag'] != '-'){
        if(get_protein_ID_type($hitsArrValue['LocusTag']) == 'ORF') $hasLocusTag = 1;
      }  
	  	if($hitsArrValue['GeneID']){
	     	if($sort_by_bait_id == $baitIdIndexArr[$j] && $theaction == "showNormal"){
	       	array_push($firstHitsArr, $hitsArrValue['GeneID']);
	     	}
	     	if(!array_key_exists($hitsArrValue['GeneID'], $tmpHitsArr)){
	       	$tmpHitValue = $hitsArrValue['HitGI']."-".$hitsArrValue['Expect']."-".$hitsArrValue['Pep_num']."-".$hitsArrValue['Pep_num_uniqe']."-".$hitsArrValue['Coverage'];        
	       	$tmpHitsArr[$hitsArrValue['GeneID']] = $tmpHitValue;
	       	if(!in_array($hitsArrValue['GeneID'], $hitsGeneIdIndexArr)){
						if($sort_by_bait_id != $baitIdIndexArr[$j] && $theaction == "showNormal"){
						  array_push($hitsGeneIdIndexArr, $hitsArrValue['GeneID']);
						}
	         	$SQL = "SELECT `GeneName` FROM `Protein_Class` WHERE `EntrezGeneID`='".$hitsArrValue['GeneID']."'";
	         	if($GeneNameArr = $PROTEINDB->fetch($SQL)){
	           	$GeneName = $GeneNameArr['GeneName'];
	         	}else{
	           	$GeneName = '';
	         	}
	         	$hitsNameArr[$hitsArrValue['GeneID']] = $GeneName.','.$hitsArrValue['HitGI'].','.$hitsArrValue['LocusTag'];
	       	}
	     	}
	   	}elseif($hitsArrValue['HitGI']){// && $hitsArrValue['HitGI'] != 'none'){
	      $tempHitGI = $hitsArrValue['HitGI']."_GI";
	      if($sort_by_bait_id == $baitIdIndexArr[$j] && $theaction == "showNormal"){
	        array_push($firstHitsArr, $tempHitGI);
	      }
	      if(!array_key_exists($tempHitGI, $tmpHitsArr)){
	        $tmpHitValue = $hitsArrValue['HitGI']."-".$hitsArrValue['Expect']."-".$hitsArrValue['Pep_num']."-".$hitsArrValue['Pep_num_uniqe']."-".$hitsArrValue['Coverage'];
	        $tmpHitsArr[$tempHitGI] = $tmpHitValue;
	        if(!in_array($tempHitGI, $hitsGeneIdIndexArr)){
	          if($sort_by_bait_id != $baitIdIndexArr[$j] && $theaction == "showNormal"){
	            array_push($hitsGeneIdIndexArr, $tempHitGI);
	          }  
	          $SQL = "SELECT `GeneName` FROM `Protein_Class` WHERE `LocusTag`='".$hitsArrValue['LocusTag']."'";
	          if($GeneNameArr = $PROTEINDB->fetch($SQL) && $GeneNameArr['GeneName'] != "-"){
	            $GeneName = $GeneNameArr['GeneName'];
	          }else{
	            $GeneName = '';
	          }
	          $hitsNameArr[$tempHitGI] = $GeneName.','.$hitsArrValue['HitGI'].','.$hitsArrValue['LocusTag'];
	        }
	      }
	    }
	  }
	  $baitNameArr[$baitIdIndexArr[$j]] = $tmpHitsArr;
	}
	
	if($theaction == "showNormal"){
	  if(count($firstHitsArr)){
	    $tmpDiff = array_diff($hitsGeneIdIndexArr, $firstHitsArr);
	    $hitsGeneIdIndexArr = array_merge($firstHitsArr, $tmpDiff);
	  }
	  if($orderby == 'Fequency'){
	    $hitsGeneIdIndexArr = get_hitsGeneIdIndexArr_for_fequency($hitsGeneIdIndexArr,$FeqIndexArr);
	  }elseif(in_array($orderby, $passedTypeArr)){
	    $hitsGeneIdIndexArr = get_hitsGeneIdIndexArr_for_subFequency($hitsGeneIdIndexArr,$subFeqIndexArr);
	  }
	  $start_I_index = 0;
	  $end_I_index = count($hitsGeneIdIndexArr);
	}
}

?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>ProHits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<link rel="stylesheet" type="text/css" href="./tool_tip_style.css">
<script language="Javascript" src="site_javascript.js"></script>
<SCRIPT src="./tool_tip/dw_event.js" type=text/javascript></SCRIPT>
<SCRIPT src="./tool_tip/dw_viewport.js" type=text/javascript></SCRIPT>
<SCRIPT src="./tool_tip/dw_tooltip.js" type=text/javascript></SCRIPT>
<script language="Javascript" src="tool_tip_javascript.js"></script>
<script language='javascript'>
var newWin2;
function generateSubReport(){
  theForm = document.form_comparison;
  if(typeof(nWin) == 'object'){
      nWin.close();
      theForm.target='_parent';
  }
  theForm.action = 'comparison_report_nofilter_pop.php';
  file = 'loading.html';
  newWin2 = window.open(file,"detailWind",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=900,height=800');
  newWin2.focus();
  theForm.target = 'detailWind';
  theForm.submit();
}

function sort_page(){
  theForm = document.form_comparison;
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  if(theForm.sort_by_bait_id.value == '' && theForm.orderby.value == ''){
    alert("Please select a item to sort");
  }else{
  <?php 
  if($theaction != "showNormal"){?>
    theForm.tmpgif_x.value = '';
    theForm.tmpgif_y.value = '';
  <?php }?>
    theForm.submit();
  }
}
function updateFrequency(){
  theForm = document.form_comparison;
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  file = "./mng_set_frequency.php?theaction=update_only";
  nWin = window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=300');
  nWin.moveTo(4,0);
}
var isNav, isIE;
if(parseInt(navigator.appVersion) >= 4){
  if(navigator.appName == "Netscape"){
    isNav = true;
  }else{
    isIE = true;
  }
}

function showZoom(evt,divID,tipW,tipH){
	var obj_div = document.getElementById(divID);
  var theForm = document.getElementById('form_comparison');
  var obj_image = document.getElementById('gif');
  var image_left = parseInt(obj_image.offsetLeft);
  var image_top = parseInt(obj_image.offsetTop);
  var image_W = parseInt(theForm.overallWidth.value);
  var image_H = parseInt(theForm.overallHeight.value);
  var labalH = parseInt(theForm.labalH.value);
  if(isNav){
    var window_event_x = evt.pageX;
    var window_event_y = evt.pageY;
  }else{
    var window_event_x = window.event.clientX  + document.body.scrollLeft;
    var window_event_y = window.event.clientY + document.body.scrollTop;
  } 
  var dif_x1 = window_event_x - image_left;
  var dif_x2 = image_left + image_W - window_event_x;
  var dif_y1 = window_event_y - (image_top + labalH);
  var dif_y2 = image_top + image_H - window_event_y;
  
  theForm.tmpgif_x.value = window_event_x - image_left;
  theForm.tmpgif_y.value = window_event_y - image_top;
  
  if(dif_x1 < tipW*(-1) && dif_y1 < tipH*(-1)){
    obj_div.style.left = image_left + "px";
    obj_div.style.top = image_top + labalH + "px";
  }else if(dif_x2 < tipW*(-1) && dif_y1 < tipH*(-1)){
    obj_div.style.left = image_left + image_W + 2*tipW + "px";
    obj_div.style.top = image_top + labalH + "px";
  }else if(dif_x1 < tipW*(-1) && dif_y2 < tipH*(-1)){
    obj_div.style.left = image_left + "px";
    obj_div.style.top = image_top + image_H + 2*tipH + "px";
  }else if(dif_x2 < tipW*(-1) && dif_y2 < tipH*(-1)){
    obj_div.style.left = image_left + image_W + 2*tipW + "px";
    obj_div.style.top = image_top + image_H + 2*tipH + "px";
  }else if(dif_x1 < tipW*(-1)){
    obj_div.style.left = image_left + "px";
    obj_div.style.top = window_event_y + tipH + "px";
  }else if(dif_y1 < tipH*(-1)){
    obj_div.style.left = window_event_x + tipW + "px";
    obj_div.style.top = image_top + labalH + "px";
  }else if(dif_x2 < tipW*(-1)){
    obj_div.style.left = image_left + image_W + 2*tipW + "px";
    obj_div.style.top = window_event_y + tipH + "px";
  }else if(dif_y2 < tipH*(-1)){ 
    obj_div.style.left = window_event_x + tipW + "px";
    obj_div.style.top = image_top + image_H + 2*tipH + "px" 
  }else{
    obj_div.style.left = window_event_x + tipW + "px";
    obj_div.style.top = window_event_y + tipH + "px";
  }
  obj_div.style.display="block";
}
function div_change_location(evt,divID,tipW,tipH){
  showZoom(evt,divID,tipW,tipH);
  generateSubReport();
}
</script>
<META content="MSHTML 6.00.2900.3199" name=GENERATOR></head>
<basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 
topMargin=5 rightMargin=5 marginheight="5" marginwidth="5" onload=Tooltip.init()>
<center>


<b><font face="Arial" size="+3">Comparison Report</font></b>
<FORM ID="form_comparison" ACTION="<?php echo $PHP_SELF;?>" NAME="form_comparison" METHOD="POST">
  <INPUT TYPE="hidden" NAME="frm_selected_bait_str" VALUE="<?php echo $frm_selected_bait_str?>">
  <INPUT TYPE="hidden" NAME="totalHits" VALUE='<?php echo $totalHits;?>'>
  <INPUT TYPE="hidden" NAME="totalBaits" VALUE='<?php echo $totalBaits;?>'>
  <INPUT TYPE="hidden" NAME="passedTypeStr" VALUE='<?php echo $passedTypeStr;?>'>
  <INPUT TYPE="hidden" NAME="biggestPowedSore" VALUE="<?php echo $biggestPowedSore;?>">
<?php 
$aa = '';
get_colorArrSets($powerColorIndex,$colorArrSet,$aa);

if($theaction != "popWindow"){
?>
<table align="center" bgcolor='' cellspacing="1" cellpadding="0" border="0" width=650>
  <tr>
<?php 
		print_color_bar($colorArrSet);	
?>			
    <td >
      <table align="center" bgcolor='' cellspacing="0" border="0">
        <tr>  
          <td nowrap><font size="2" face="Arial"><b>Sort by:</b></font>
          </td>
          <td nowrap>
          <select name="orderby" size=1>
            <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
            <option value='Expect' <?php echo ($orderby=='Expect')?'selected':''?>>Score<br>
            <option value='Pep_num' <?php echo ($orderby=='Pep_num')?'selected':''?>>Pep_num<br>
            <option value='Pep_num_uniqe' <?php echo ($orderby=='Pep_num_uniqe')?'selected':''?>>Pep_num_uniqe<br>
            <option value='Coverage' <?php echo ($orderby=='Coverage')?'selected':''?>>Coverage<br>
            <option value='Fequency' <?php echo ($orderby=='Fequency')?'selected':''?>>Frequency<br>
            <?php foreach($passedTypeArr as $passedTypeValue){?>
                  <option value='<?php echo $passedTypeValue;?>' <?php echo  ($orderby==$passedTypeValue)?'selected':''?>><?php echo $passedTypeValue;?><br>
            <?php }?>      
          </select>
            <input type=radio name=asc_desc value='DESC' <?php echo (isset($asc_desc) && $asc_desc=='DESC')?'checked':''?>>Desc.&nbsp;
            <input type=radio name=asc_desc value='ASC' <?php echo (isset($asc_desc) && $asc_desc=='ASC')?'checked':''?>>Asc&nbsp;
            <input type=button value='Update Frequency' onClick='javascript: updateFrequency();'>
          </td>
        </tr>
        <tr>
          <td valign=top nowrap > <font size="2" face="Arial"><b>Bait:</b>&nbsp;
          </td>
          <td nowrap>
          <select name="sort_by_bait_id" size=1>
            <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
          <?php foreach($baitLableArr as $baitID => $lable){
            list($temID,$temLable) = explode('_', $lable, 2);
          ?>
            <option value='<?php echo $baitID?>' <?php echo  ($sort_by_bait_id==$baitID)?'selected':''?>><?php echo $baitID?>&nbsp;&nbsp;<?php echo $temLable?><br>
          <?php 
            flush();
          }?>
          </select>&nbsp;&nbsp;&nbsp;
          <input type=button name=sort_submit value=" GO " onclick="sort_page();">
          </td>
        </tr> 
      </table><br>
    </td>
  </tr>
</table>  

<?php 
}
if($theaction == "showImage"){
  $zoomW = $cellW * $subPopWinSize;
  $zoomH = $cellH * $subPopWinSize;
  $tipW = -1 * $zoomW/2;
  $tipH = -1 * $zoomH/2;
?>
  <INPUT ID="gif" TYPE="image" NAME="gif" HEIGHT=<?php echo $overallHeight;?> WIDTH=<?php echo $overallWidth;?> ALT="" 
  SRC="./comparison_big_gif.php?overallWidth=<?php echo $overallWidth;?>&overallHeight=<?php echo $overallHeight;?>&cellH=<?php echo $cellH;?>&cellW=<?php echo $cellW;?>&orderby=<?php echo $orderby;?>&biggestPowedSore=<?php echo $biggestPowedSore;?>&power=<?php echo $power;?>&baitlableMaxL=<?php echo $baitlableMaxL;?>&colorSet=<?php echo $colorSet;?>&labalH=<?php echo $labalH;?>&fontSize=<?php echo $fontSize;?>&fontH=<?php echo $fontH;?>&asc_desc=<?php echo $asc_desc;?>&sort_by_bait_id=<?php echo $sort_by_bait_id;?>&source=<?php echo $source;?>"  onClick="generateSubReport();" onmouseup="showZoom(event,'zoomDiv',<?php echo $tipW;?>,<?php echo $tipH;?>);">
  <INPUT TYPE="hidden" NAME="cellH" VALUE="<?php echo $cellH;?>">
  <INPUT TYPE="hidden" NAME="cellW" VALUE="<?php echo $cellW;?>">
  <INPUT TYPE="hidden" NAME="overallWidth" VALUE="<?php echo $overallWidth;?>">
  <INPUT TYPE="hidden" NAME="overallHeight" VALUE="<?php echo $overallHeight;?>">
  <INPUT TYPE="hidden" NAME="labalH" VALUE="<?php echo $labalH;?>">
  <INPUT TYPE="hidden" NAME="fontSize" VALUE="<?php echo $fontSize;?>">
  <INPUT TYPE="hidden" NAME="fontH" VALUE="<?php echo $fontH;?>">
  <INPUT TYPE="hidden" NAME="tmpgif_x" VALUE="-1">
  <INPUT TYPE="hidden" NAME="tmpgif_y" VALUE="-1">
  
  <DIV ID='zoomDiv' STYLE="position: absolute; 
                        display: none;
                        border: black solid 2px;
                        width: <?php echo $zoomW;?>px;
                        height: <?php echo $zoomH;?>px;
                        -moz-opacity: 0.3;
                        opacity: 0.3; /* these 2 lines control opacity: they work  in IE, NN, Firefox */
                        filter: alpha(opacity=30); /* make sure the numbers agree, e.g. .7 corresponds to 70% */
                        color: black;
                        background-color: yellow";
                        onclick="div_change_location(event,'zoomDiv',<?php echo $tipW;?>,<?php echo $tipH;?>)";>
  </DIV>
   </FORM>
  <!--table><tr><td>
  <a href='javascript: popTest();'>[Test Image]</a>
  </td></tr>
  </table-->
</BODY>
</HTML> 
<script language='javascript'>

</script>
<?php 
exit;
}else{
?>
<table align="center" bgcolor="" cellspacing="0" cellpadding="0" border="0" width=750>
  <tr>
  <?php  
  for($j=$start_J_index; $j<$end_J_index; $j++){
    $baitLable = $baitLableArr[$baitIdIndexArr[$j]];
  ?>
    <td colspan="" class=s20 align=center rowspan="2"><img src='./bait_image.php?strMaxL=<?php echo $baitlableMaxL;?>&displayedStr=<?php echo $baitLable;?>&fontSize=2' border=0></font></td>
  <?php 
  }
  $TB_CELL_COLOR = '#ff7575';
  if($hasGeneID || $hasProteinID || $hasLocusTag){
  ?>
    <td bgcolor="#aeaeae" colspan=3 rowspan=1 align=center><font size=3><b>Hits</b></font></td>
<?php }?>  
 </tr>
 <tr>
<?php if($hasGeneID){?>
 <td class=s19  align=center>Gene Name</td>
<?php }
  if($hasProteinID){?>
 <td class=s19  align=center>Protein ID</td>
<?php }
  if($hasLocusTag){?> 
 <td class=s19  align=center>Links</td>
<?php }?> 
 </tr>
  <?php 
  $TB_CELL_COLOR = '#ff7575';
  for($i=$start_I_index; $i<$end_I_index; $i++){
    $freqStr = '';
    $fequencySore = '&nbsp;';
    $subFequencySore = '&nbsp;';
    if(isset($FeqValueArr[$hitsGeneIdIndexArr[$i]])){
      $fequencySore = $FeqValueArr[$hitsGeneIdIndexArr[$i]];
      $freqStr = "<br>Frequency: ".$fequencySore."%";
    }
    $subFreqStr = '';
    if(isset($subFeqsValueArr) && count($subFeqsValueArr)){
      $subFreqStr = '';
      foreach($subFeqsValueArr as $fKey => $fValue){
        if(array_key_exists($hitsGeneIdIndexArr[$i], $fValue)){
       //echo $passedTypeArr[$fKey]."<br>";
          if($orderby == $passedTypeArr[$fKey]){
            if($fValue[$hitsGeneIdIndexArr[$i]]){
              $subFequencySore = $fValue[$hitsGeneIdIndexArr[$i]];
            }  
          }  
          $subFreqStr .= "<br>SubFrequency[".$passedTypeArr[$fKey]."]: ".$fValue[$hitsGeneIdIndexArr[$i]]."%";
        }  
      }
    }
  ?>
  <tr bgcolor="#ececec" onmousedown="highlightTR(this, 'click', '#CCFFCC', '#ececec')";>
  <?php 
    for($j=$start_J_index; $j<$end_J_index; $j++){
      $temHitsArr = $baitNameArr[$baitIdIndexArr[$j]];
      if(array_key_exists($hitsGeneIdIndexArr[$i], $temHitsArr)){
        $tmpHitInfoArr = explode('-',$temHitsArr[$hitsGeneIdIndexArr[$i]]);
        $hitSore = 0;
        $subFequencyValue = '';
        if($orderby == 'HitGI'){
          $hitSore = $tmpHitInfoArr[0];
        }elseif($orderby == 'Expect'){
          $hitSore = $tmpHitInfoArr[1];
        }elseif($orderby == 'Pep_num'){
          $hitSore = $tmpHitInfoArr[2];
        }elseif($orderby == 'Pep_num_uniqe'){
          $hitSore = $tmpHitInfoArr[3];
        }elseif($orderby == 'Coverage'){
          $hitSore = round($tmpHitInfoArr[4], 1);
        }elseif($orderby == 'Fequency'){
          $hitSore = $fequencySore;
        }elseif(in_array($orderby, $passedTypeArr)){
          $hitSore = $subFequencySore;
        }
        $upStr = "Protein ID: ".$tmpHitInfoArr[0]."<br>Score:&nbsp;".$tmpHitInfoArr[1]."<br>Total Peptide: ".$tmpHitInfoArr[2]."<br>Unique Peptide: ".$tmpHitInfoArr[3]."<br>Protein Coverage: ".$tmpHitInfoArr[4].$freqStr.$subFreqStr;
        $cellBgcolor = color_num($hitSore, $colorIndex);
        ($colorIndex >= 7)?$numOfClass='s13':$numOfClass='s14';
     ?>
        <td class=<?php echo $numOfClass;?> align=center bgcolor='<?php echo $cellBgcolor;?>' onmouseover="doTooltipmsg(event, '<?php echo $upStr;?>')"  onmouseout=hideTip()><?php echo trim($hitSore);?></td>
     <?php 
      }else{
      ?>
        <td align=center class=s15>&nbsp;</td>
      <?php 
      }
    }
    list($tmpGeneName,$tempProteinID,$tmpLocusTag) = explode(',',$hitsNameArr[$hitsGeneIdIndexArr[$i]]);
    $tempHitGeneID = $hitsGeneIdIndexArr[$i];
    if(preg_match("/_GI$/", $hitsGeneIdIndexArr[$i])){
      $tempHitGeneID = '';
    }
    //get_URL_str($proteinKey='', $geneID='', $locusTag='', $geneName='')
    
    if(!($GeneIdURL = get_URL_str('',$tempHitGeneID,'',$tmpGeneName,'comparison'))) $GeneIdURL = "&nbsp;";
    if(!($ProteinIdURL = get_URL_str($tempProteinID,'','','','comparison'))) $ProteinIdURL = "&nbsp;";
    if(!($orfURL = get_URL_str('','',$tmpLocusTag,'','comparison'))) $orfURL = "&nbsp;";
  ?>
  <?php if($hasGeneID){?>
      <td class=s16 align=center align=center><?php echo $GeneIdURL;?></td>
  <?php }
    if($hasProteinID){?>
      <td class=s17 align=center align=center><?php echo $ProteinIdURL;?></td>
  <?php }
    if($hasLocusTag){?> 
      <td class=s22 bgcolor=#d6d6d6 align=center nowrap><?php echo $orfURL;?></td>
  <?php }?> 
  
  
      <!--td class=s16 align=center bgcolor='#ffffff' width='' align=center><font size=1><?php echo $GeneIdURL;?></font></td>
      <td class=s17 align=center bgcolor='#ffffff' width='' align=center><?php echo $ProteinIdURL;?></td>
      <td class=s16 align=center bgcolor='#ffffff' width='500' align=center nowrap><?php echo $orfURL;?></td-->
  </tr>
  <?php 
  }
  ?>
  </table>
  </FORM>
  <?php 
}
?>
</BODY>
</HTML>

<?php 
function start_index($index, $subPopWinSize, $realRange){
  if($index - (round($subPopWinSize / 2)) <= 0){
    $start_index = 0;
  }elseif($index + 1 + round($subPopWinSize / 2) > $realRange){
    $start_index = $realRange - $subPopWinSize;
  }else{
    $start_index = $index + 1 - round($subPopWinSize / 2);
  }
  return $start_index;
}

function color_num($score,&$colorIndex){
  global $biggestPowedSore,$power,$colorArrSet;
  $colorRange = count($colorArrSet);
  $powedSore = pow($score,$power);
  $colorIndex = round($colorRange * $powedSore / $biggestPowedSore - 0.5);
  $colorIndex = intval($colorIndex);                      
  if($colorIndex >= 10) $colorIndex = 9;
  if($colorIndex <= 0) $colorIndex = 0;
  return $colorArrSet[$colorIndex];
}

function get_subFequency(&$subFeqIndexArr, &$subFeqsValueArr,$passedTypeArr, $typeNum = ''){
  global $AccessProjectID, $asc_desc,$theation;
  $updatedFlag = 0;
  $maxScore = 0;
	if(!$passedTypeArr) return 0;
	
  $subDir = STORAGE_FOLDER."Prohits_Data/subFrequency/";
  foreach($passedTypeArr as $typeKey => $typeValue){
		if($theation == "showImage" && $typeKey != $typeNum) continue;
    $subFileName = $subDir."Pro".$AccessProjectID."_Type".$typeKey.".csv";
    if(!_is_file($subFileName) && !$updatedFlag){
       updata_frequency();
			 $updatedFlag = 1;
    }
    $lines = file($subFileName);
    array_shift($lines);
    foreach($lines as $lineValue){  
      list($GeneID, $Freqency) = explode(',', $lineValue);
      $subFeqValueArr[trim($GeneID)] = trim($Freqency);
    }
    if($typeKey == $typeNum){
      if($asc_desc == 'DESC'){
        arsort($subFeqValueArr);
      }else{
        asort($subFeqValueArr);
      }
      
      foreach($subFeqValueArr as $subFeqKey => $subFeqValue){
        if($maxScore < $subFeqValue){
          $maxScore = $subFeqValue;
        }
        array_push($subFeqIndexArr, $subFeqKey);
      }  
    }
		if($theation == "showImage"){
    	$subFeqsValueArr = $subFeqValueArr;
		}else{
			$subFeqsValueArr[$typeKey] = $subFeqValueArr;
		}	
  }
  return $maxScore;
}

function get_fequency(&$FeqIndexArr, &$FeqValueArr){
  global $HITSDB, $asc_desc, $AccessProjectID;
	$maxScore = 0;
  $SQL = "SELECT `GeneID`, 
                  `Value` 
                  FROM `ExpFilter` 
                  WHERE `ProjectID`='".$AccessProjectID."' AND `FilterAlias`='FQ' 
                  ORDER BY `Value` $asc_desc";
  $temFeq = $HITSDB->fetchAll($SQL);
  $totalFeq = count($temFeq);
  foreach($temFeq as $temFeqValue){
    array_push($FeqIndexArr, $temFeqValue['GeneID']);
		$fequency = round(($temFeqValue['Value']/$totalFeq)*100,2);
    $FeqValueArr[$temFeqValue['GeneID']] = $fequency;
		if($maxScore < $fequency){
      $maxScore = $fequency;
    }
  }
  return $maxScore;
}
function get_hitsGeneIdIndexArr_for_fequency(&$hitsGeneIdIndexArr,&$FeqIndexArr){
  $tmpDiffArr = array_diff($hitsGeneIdIndexArr, $FeqIndexArr);
  $tmpIntersectArr = array_intersect($FeqIndexArr, $hitsGeneIdIndexArr);
  unset($hitsGeneIdIndexArr);
  $hitsGeneIdIndexArr = array();
  foreach($tmpIntersectArr as $value){
    array_push($hitsGeneIdIndexArr, $value);
  }
  foreach($tmpDiffArr as $value){      
    array_push($hitsGeneIdIndexArr, $value);
  }
  return $hitsGeneIdIndexArr;
}
function get_hitsGeneIdIndexArr_for_subFequency(&$hitsGeneIdIndexArr,&$subFeqIndexArr){ 
  $tmpDiffArr = array_diff($hitsGeneIdIndexArr, $subFeqIndexArr);
  $tmpIntersectArr = array_intersect($subFeqIndexArr, $hitsGeneIdIndexArr);
  unset($hitsGeneIdIndexArr);
  $hitsGeneIdIndexArr = array();
  foreach($tmpIntersectArr as $value){
    array_push($hitsGeneIdIndexArr, $value);
  }
  foreach($tmpDiffArr as $value){
    array_push($hitsGeneIdIndexArr, $value);
  }
  return $hitsGeneIdIndexArr;
}

function format_image(){
  global $cellH, $cellW, $overallWidth, $overallHeight,$totalBaits,$totalHits,$fontSize,$labalH,$fontH,$baitlableMaxL;
	$maxCellH = 5;
  $maxCellW = 30;
  $minCellH = 1;
  $minCellW = 1;
  if($overallWidth/$totalBaits > $maxCellW){
    $cellW = $maxCellW;
  }elseif($overallWidth/$totalBaits < $minCellW){
    $cellW = $minCellW;
  }else{
    $cellW =  round(($overallWidth/$totalBaits)-0.5);
  }
  $overallWidth = $cellW * $totalBaits;
  if($overallHeight/$totalHits > $maxCellH){
    $cellH = $maxCellH;
  }elseif($overallHeight/$totalHits < $minCellH){
    $cellH = $minCellH;
  }else{
    $cellH = round(($overallHeight/$totalHits)-0.5);
  }
  
  $font1Heighth = imagefontheight(1);
  $font1Width = imagefontwidth(1);
  $font2Heighth = imagefontheight(2);
  $font2Width = imagefontwidth(2);
  $font4Heighth = imagefontheight(4);
  $font4Width = imagefontwidth(4);
  
  if($cellW > $font4Heighth+2){
    $fontSize = 4;
    $labalH = $font4Width*$baitlableMaxL + 3;
    $fontH = $font4Heighth;
  }elseif($cellW > $font2Heighth+2){
    $fontSize = 2;
    $fontH = $font2Heighth;
    $labalH = $font2Width*$baitlableMaxL + 3;
  }elseif($cellW > $font1Heighth+2){
    $fontSize = 1;
    $fontH = $font1Heighth;
    $labalH = $font1Width*$baitlableMaxL + 3;
  }else{
    $labalH = 0;
  }
  //echo $labalH."#########<br>";
  $overallHeight = $cellH * $totalHits + $labalH;
  
  if($overallHeight > 5000){
    $cellW = 1;
    $overallWidth = $cellW * $totalBaits;
  }
}
//-return a set of color array for different order by -------------------------
function get_colorArrSets($powerColorIndex, &$colorArrSet, &$im){
	$colorArrSet = array();
	if(!$im){
		$colorArrSets['red'] = array("#ffd2d2","#ff9797","#ff6666","#ff3c3c","#fd0000","#d70000","#a80000","#840000","#590000","#2b0000");
		$colorArrSets['blue'] = array("#aaaaff","#7171ff","#5b5bff","#3737ff","#1717ff","#0000f0","#0000ce","#0000b9","#00009b","#000080");
		$colorArrSets['oliver'] = array("#d2d2a6","#c5c58b","#bbbb77","#a7a754","#9a9a4e","#8c8c46","#808040","#6f6f37","#656532","#58582c");
		$colorArrSets['green'] = array("#88ff88","#5bff5b","#00dd00","#00b700","#00a400","#009500","#008000","#006c00","#005f00","#005500");
		$colorArrSets['purple'] = array("#e7ceff","#cd9bff","#c184ff","#a74fff","#9d3cff","#7d00fb","#6700ce","#5400a8","#3c0077","#290053");
		$colorArrSets['sienna'] = array("#ffdece","#ffb591","#ff732f","#ff6a22","#ff5706","#dd4800","#aa3700","#7d2800","#531b00","#2f1700");
	}else{
		$imageColorArrSets['red'] = array('255,164,164','255,151,151','255,102,102','255,60,60','253,0,0','215,0,0','168,0,0','132,0,0','89,0,0','43,0,0');
		$imageColorArrSets['blue'] = array('170,170,255','113,113,255','91,91,255','55,55,255','23,23,255','0,0,240','0,0,206','0,0,185','0,0,155','0,0,128');
		$imageColorArrSets['oliver'] = array('210,210,166','197,197,139','187,187,119','168,168,84','154,154,78','140,140,70','128,128,64','111,111,55','101,101,50','88,88,44');
		$imageColorArrSets['green'] = array('136,255,136','91,255,91','0,221,0','0,183,0','0,164,0','0,149,0','0,128,0','0,108,0','0,95,0','0,85,0');
		$imageColorArrSets['purple'] = array('231,206,255','193,132,255','177,100,255','167,79,255','157,60,255','125,0,251','103,0,206','84,0,168','80,0,119','41,0,83');
		$imageColorArrSets['sienna'] = array('255,222,206','255,181,145','255,115,47','225,106,34','225,87,6','221,72,0','170,55,0','125,40,0','83,27,0','47,23,0');
	}	

	$sortColorArr['Expect'] = 'red';
	$sortColorArr['Pep_num'] = 'blue';
	$sortColorArr['Pep_num_uniqe'] = 'purple';
	$sortColorArr['Coverage'] = 'oliver';     
	$sortColorArr['Fequency'] = 'green';
	
	$colorSet = $sortColorArr[$powerColorIndex]; //red, blue...
	if(!$im){
		$colorArrSet = $colorArrSets[$colorSet];
	}else{
		foreach($imageColorArrSets[$colorSet] as $value){
		  list($tem1,$tem2,$tem3) = explode(',',$value);
		  $tmpColor = ImageColorAllocate($im,$tem1,$tem2,$tem3);
		  array_push($colorArrSet, $tmpColor);
		}
	}
}
function print_color_bar(&$colorArrSet){
	global $biggestPowedSore,$power,$powerColorIndex,$maxScore,$theaction;
	$colorBarTotalW = 250;
	$colorCellW = 23;
	$colorCellH = 40;
	
	$colorRange = count($colorArrSet);
	$colorBarTotalRealW = $colorBarTotalW*0.98;
	if($colorBarTotalRealW/($colorRange+1) > $colorCellW){
		$colorCellW = round($colorBarTotalRealW/($colorRange+1)-0.5);
		$colorBarTotalRealW = $colorCellW * ($colorRange+1);
		$colorBarTotalW = $colorBarTotalRealW + 10;
	}else{
		$colorBarTotalRealW = $colorCellW * ($colorRange+1);
		$colorBarTotalW = $colorBarTotalRealW + 10;
	}
  $aa = '';
	get_colorArrSets($powerColorIndex, $colorArrSet,$aa);
	
?>			
    <td width=<?php echo $colorBarTotalW;?>>
      <table align="" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=<?php echo $colorBarTotalRealW;?>>
        <tr height=40>
    <?php 
      $Key = 0;
      foreach($colorArrSet as $colorCell){
				if($theaction == "showNormal"){
		?>        
          <td valign=top width=<?php echo $colorCellW;?> class=s21 bgcolor='<?php echo $colorCell;?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <?php }else{?>
					<td valign=top width=<?php echo $colorCellW;?> class=s21 ><img src='./bait_image.php?imageW=<?php echo $colorCellW;?>&imageH=<?php echo $colorCellH;?>&colorkey=<?php echo $Key;?>&powerColorIndex=<?php echo $powerColorIndex;?>' border=0></td>	
			<?php 
          $Key++;
				}
			}
		?> 
          <td valign=top width=<?php echo $colorCellW;?> class=s21>&nbsp;</td>         
        </tr>
        <tr>
          <?php foreach($colorArrSet as $olorKey => $colorCell){
              $colorNumber = round(pow(($biggestPowedSore*$olorKey/$colorRange),1/$power));
          ?>     
          <td valign=top width=<?php echo $colorCellW;?> Valign="top"><?php echo $colorNumber?></td>
          <?php }?>
          <td valign=topwidth=<?php echo $colorCellW;?> Valign="top"><?php echo round($maxScore,1)?></td>          
        </tr>        
      </table>
    </td>
<?php 
}				            
?>