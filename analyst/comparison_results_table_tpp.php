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
$orderby = "Pep_num";
$sort_by_item_id = '';
$asc_desc = 'DESC';
$maxScore = 0;

$subPopWinSize = 25;
if($subPopWinSize%2) $subPopWinSize = $subPopWinSize + 1;
$itemsNumLimit = 30;
$overallWidth =800;
$overallHeight =700;

$power = 1;
$maxScore = 0;
$colorSet = '';
$typeStr = '';

$powerArr['Expect'] = 1/2;
$powerArr['Expect2'] = 1/2;
$powerArr['Pep_num'] = 1/2;
$powerArr['Pep_num_uniqe'] = 1;
$powerArr['Coverage'] = 1;     
$powerArr['Fequency'] = 1;

$itemlableMaxL = 0;
$itemLableArr = array();
$source = '';
$totalHits = '';

$hasGeneID = 0;
$hasProteinID = 0;
$hasLocusTag = 0;
$itemNoName = 0;
$contrlColor = 'C_FFFF00';
$ungroupedItemColor = 'C_FFFFFF';

$frm_filter_Expect = 0;
$frm_filter_Coverage = 0;
$frm_filter_Peptide = '';
$frm_filter_Peptide_value = 0;
$frm_filter_Fequency = '';
$frm_filter_Fequency_value = 0;

$filterStyleDisplay = 'none';
$subfilterStyleDisplay = 'none';
$applyFilters = 0;

$red = '#ff8080';
$blue = '#00bfff';
$green = '#92ef8f';
//$green = '#5bff5b';

$frm_red = ''; 
$frm_green = ''; 
$frm_blue = '';
$filtrColorIniFlag = 0;
$passedTypeStr = '';
 
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$comparisonDir = "../TMP/comparison/";
if(!_is_dir($comparisonDir)) _mkdir_path($comparisonDir);
$subDir = "../TMP/comparison/P_$AccessProjectID/";
if(!is_dir($subDir)) mkdir($subDir);

$argumentsFileName = $subDir.$AccessUserID."_arguments.txt";
$selectedListStrFileName = $subDir.$AccessUserID."_selected_list_.txt";
$hitsIndexFileName = $subDir.$AccessUserID."_hits_index.txt";
$hitsNameFileName = $subDir.$AccessUserID."_hits_name.txt";
$reportFileName = $subDir.$AccessUserID."_report.txt";
$reportFile_handle = fopen($reportFileName, "w");

if(!$reportFile_handle){
  echo "Cannot open file $reportFileName";
}
if($currentType == 'Bait'){
  $typeLable = 'Bait';
  $singleTypeLable = 'Bait';
}else{
  $typeLable = 'Sample';
  $singleTypeLable = 'Sample';
}
if($SearchEngine == 'GPM'){
  $Expect = 'Expect2';
  if(!$orderby || $orderby == 'Expect'){
    $orderby = 'Expect2';
  }
  if($source == 'comparison'){
    $asc_desc = 'ASC';
  }
}else{
  $Expect = 'Expect';
  if(!$orderby){
    $orderby = 'Expect';
  }
}
if($orderby == 'Expect2'){
  $DESC = 'ASC';
  $MAX = 'MIN';
}else{
  $DESC = 'DESC';
  $MAX = 'MAX';
}
if($filtrColorIniFlag){
  $frm_red = 'y'; 
  $frm_green = 'y'; 
  $frm_blue = 'y';
}

if(!isset($gif_y) && isset($tmpgif_y) && $tmpgif_y != ''){
  $gif_x = $tmpgif_x;
  $gif_y = $tmpgif_y;
}
$itemID = $currentType.'ID';

$PROTEINDB = new mysqlDB(PROHITS_PROTEINS_DB);
$itemIdIndexArr = array();
$hitsGeneIdIndexArr = array();

$groupArr = array();
$frm_selected_item_str = '';
$frm_selected_group_str = '';
$no_groupped_str = '';
//echo  @date("H:i:s")."<br>";  
if($frm_selected_list_str){
  create_groupArr_otherStrs($groupArr,$frm_selected_item_str,$frm_selected_group_str,$no_groupped_str);
  $itemIdIndexArr = explode(',', $frm_selected_group_str);
  $tmpRealItemArr = explode(',',$frm_selected_item_str);
  $tmpRealItemCounter = count($tmpRealItemArr);  
}else{
  echo "no input elements";
  exit;
}

if(!$sort_by_item_id) $sort_by_item_id = $itemIdIndexArr[0];
$totalitems = count($itemIdIndexArr);


if($totalitems <= $itemsNumLimit){
	$theaction = "showNormal";
}else{
	if(isset($gif_y)){
		if($subPopWinSize > $totalitems) $subPopWinSize = $totalitems;
		$theaction = "popWindow";
	}else{
		$theaction = "showImage";
	}
}
if($theaction == "showImage" && $source == 'comparison'){
  $selectedListStr_handle = fopen($selectedListStrFileName, "w");
  fwrite($selectedListStr_handle, $frm_selected_list_str);
  fclose($selectedListStr_handle);
}
//echo  @date("H:i:s")."<br>";
//*******create $passedTypeStr, $passedTypeArr[5]='HS', $typeInitIdArr['HS']=5 for subFequency***********

$typeInitIdArr = array();
$passedTypeArr = array();
create_NoteType_info($passedTypeStr,$typeInitIdArr,$passedTypeArr);
//=================================================================================================
//create $itemLableArr for "showNormal" and create
if($theaction != "popWindow"){
  create_item_lable_arr($itemLableArr,$itemlableMaxL);
  reportFile_title_info($groupArr,$itemLableArr,$itemlableMaxL,$totalitems);
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

//==================================================================================================
//Fequency and subFequency
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
//==================================================================================================== 

//**get maxscore*******************
if($orderby == 'Fequency'){
	$maxScore = $fequMaxScore;
}elseif(in_array($orderby, $passedTypeArr)){
	$maxScore = $subFequMaxScore;
}else{
  $maxScore = get_max_value($orderby);
}
$biggestPowedSore = pow($maxScore,$power);
if($orderby != 'Expect2' && $biggestPowedSore <= 0) $biggestPowedSore = 1;

//===============================================================================================
//**caculate totalHits format image size get
if($theaction == "showImage"){    
  $totalHits = get_total_hits();
  $cellH = 0;
  $cellW = 0;
  $fontSize = '';
  $labalH = '';
  $fontH = '';
  $noLableHeight = '';
  format_image();//-get real $overallWidth, $overallHeight
  $argumentsStr = "currentType=$currentType@@typeLable=$typeLable@@MAX=$MAX@@DESC=$DESC@@SearchEngine=$SearchEngine@@Expect=$Expect";
  $argumentsStr .= "@@frm_color_mode=$frm_color_mode@@orderby=$orderby@@sort_by_item_id=$sort_by_item_id";
  $argumentsStr .= "@@asc_desc=$asc_desc@@frm_filter_Coverage=$frm_filter_Coverage@@frm_filter_Peptide=$frm_filter_Peptide";
  $argumentsStr .= "@@frm_filter_Peptide_value=$frm_filter_Peptide_value@@frm_filter_Fequency=$frm_filter_Fequency";
  $argumentsStr .= "@@frm_filter_Fequency_value=$frm_filter_Fequency_value@@frm_red=$frm_red@@frm_green=$frm_green@@frm_blue=$frm_blue";
  $argumentsStr .= "@@overallWidth=$overallWidth@@overallHeight=$overallHeight@@cellH=$cellH@@cellW=$cellW@@labalH=$labalH";
  $argumentsStr .= "@@biggestPowedSore=$biggestPowedSore@@power=$power@@itemlableMaxL=$itemlableMaxL@@itemID=$itemID";
  $argumentsStr .= "@@colorSet=$colorSet@@source=$source@@ungroupedItemColor=$ungroupedItemColor@@contrlColor=$contrlColor";
  $argumentsStr .= "@@fontSize=$fontSize@@fontH=$fontH@@sqlOrderby=$sqlOrderby@@typeNum=$typeNum";
  $argumentsStr .= "@@frm_filter_Expect=$frm_filter_Expect@@powerColorIndex=$powerColorIndex";
  $argumentsStr .= "@@red=$red@@blue=$blue@@green=$green@@applyFilters=$applyFilters@@noLableHeight=$noLableHeight";
  
  $argumentsFile_handle = fopen($argumentsFileName, "w");
  fwrite($argumentsFile_handle, $argumentsStr);
  $_SESSION["passedTypeArr"] = $passedTypeArr;
  $_SESSION["typeInitIdArr"] = $typeInitIdArr;
}else{
	$hitsNameArr = array();
	$itemNameArr = array();

	if($theaction == "popWindow"){
		//-get data from files create $hitsGeneIdIndexArr
    $hitsIndexArr_handle = fopen($hitsIndexFileName, "r");
    if($hitsIndexArr_handle){
      $buffer = fgets($hitsIndexArr_handle);
      $buffer = trim($buffer);
      $hitsGeneIdIndexArr = explode(',',$buffer);
    }else{
      echo "Cannot write to file $hitsIndexFileName";
      exit;
    }
    $hitsNameArr_handle = fopen($hitsNameFileName, "r");
    if($hitsNameArr_handle){
      $tmpHitsNameArr = file($hitsNameFileName);
      foreach($tmpHitsNameArr as $tmpHitsNameValue){
        $tmpHitsNameValue = trim($tmpHitsNameValue);
        $tmpArr1 = explode('@@',$tmpHitsNameValue);
        $tmpArr2['name'] = $tmpArr1[1];
        $tmpArr2['counter'] = $tmpArr1[2];
        $tmpArr2['ctr'] = $tmpArr1[3];
        $hitsNameArr[$tmpArr1[0]] = $tmpArr2;
  	  }
    }else{
      echo "Cannot write to file $hitsIndexFileName";
      exit;
    }	  
    $totalHits = count($hitsGeneIdIndexArr);    
		//--get image lable height for caculating popWindow display area
	  if(isset($_SESSION["labalH"])){
	    $labalH = $_SESSION["labalH"];
	  }else{
	    $labalH =0;
	  }
		//--locating popWind---
	  $totalHits = count($hitsGeneIdIndexArr);
	  $I_index = round((($gif_y-$labalH)/$cellH) - 0.5);
	  $J_index = round(($gif_x / $cellW) - 0.5);
	  if($totalHits <= $subPopWinSize && $totalitems <= $subPopWinSize){
	    $start_I_index = 0;      
	    $start_J_index = 0;
	  }elseif($totalHits > $subPopWinSize && $totalitems <= $subPopWinSize){
	    $start_I_index = start_index($I_index, $subPopWinSize, $totalHits);
	    $start_J_index = 0;
	  }elseif($totalHits <= $subPopWinSize && $totalitems > $subPopWinSize){
	    $start_I_index = 0;
	    $start_J_index = start_index($J_index, $subPopWinSize, $totalitems);
	  }else{
	    $start_I_index = start_index($I_index, $subPopWinSize, $totalHits);
	    $start_J_index = start_index($J_index, $subPopWinSize, $totalitems);
	  }
	  $end_I_index = $start_I_index + $subPopWinSize;
	  $end_J_index = $start_J_index + $subPopWinSize;
	}else{
	  $start_J_index = 0;
	  $end_J_index = count($itemIdIndexArr);
	}
  
	$firstHitsArr = array();
  $contrlArr = array();
	$hitsGeneIdIndexArr2 = array();
	for($j=$start_J_index; $j<$end_J_index; $j++){    
		//---this block for itemlabal--"popWindow"--------------------------------
		if($theaction == "popWindow"){
      push_in_itemLableArr_for_popWindow($j,$itemLableArr,$itemlableMaxL);
      $tempEnptyArr1 = array();
      $tempEnptyArr2 = array();
      $tempEnptyArr3 = array();
      create_itemTree_hitsIndex_hitsPropty_Arrs($j,$firstHitsArr,$contrlArr,$tempEnptyArr1,$tempEnptyArr2,$tempEnptyArr3,$itemNameArr);
    }else{ 
		  create_itemTree_hitsIndex_hitsPropty_Arrs($j,$firstHitsArr,$contrlArr,$hitsGeneIdIndexArr,$hitsGeneIdIndexArr2,$hitsNameArr,$itemNameArr);
	  }
  }
	if((($orderby == 'Expect2' && $asc_desc == 'DESC') || ($orderby != 'Expect2' && $asc_desc == 'ASC')) && $theaction == "showNormal"){
    $firstHitsArr = array_reverse($firstHitsArr);
    $hitsGeneIdIndexArr = $hitsGeneIdIndexArr2;
    unset($hitsGeneIdIndexArr2);
  }
  
	if($theaction == "showNormal"){
    if(count($contrlArr)){
      foreach($contrlArr as $contrlValue){
        $hitsNameArr[$contrlValue]['ctr'] = 1;
      }
    }
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
/*echo "<pre>";
print_r($hitsNameArr);
echo "</pre>";*/
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<title>ProHits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<link rel="stylesheet" type="text/css" href="./tool_tip_style.css">
<link rel="stylesheet" type="text/css" href="./colorPicker_style.css">
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
  theForm.action = 'comparison_results_table.php';
  file = 'loading.html';
  newWin2 = window.open(file,"detailWind",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=900,height=800');
  newWin2.focus();
  theForm.target = 'detailWind';
  theForm.submit();
}
function popTest(){
  file = "./comparison_results_image.php";
  nWin = window.open(file,"image",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=800');
  nWin.moveTo(4,0);
}

function sort_page(){
  theForm = document.form_comparison;
  theForm.filtrColorIniFlag.value = '0';
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  if(theForm.sort_by_item_id.value == '' && theForm.orderby.value == ''){
    alert("Please select a item to sort");
  }else{
    submit_form();
  }
}
function change_color_code(objColor){
  theForm = document.form_comparison;
  if(theForm.color_mode.value == objColor.value) return;
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  theForm.filtrColorIniFlag.value = '0';
  if(objColor.value == 'shared'){
     theForm.filtrColorIniFlag.value = '1';
  }
  submit_form();
}
function submit_form(){ 
  theForm = document.form_comparison; 
  <?php if($theaction != "showNormal"){?>
      theForm.tmpgif_x.value = '';
      theForm.tmpgif_y.value = '';
  <?php }?>
  var obj = document.getElementById('filter_area');
  theForm.filterStyleDisplay.value = obj.style.display;
  var tmp_flag = 0;
  for(var i=0; i<theForm.frm_color_mode.length; i++){
    if(theForm.frm_color_mode[i].checked == true && theForm.frm_color_mode[i].value == 'shared'){
      tmp_flag = 1;
      break;
    }
  }
  if(obj.style.display == 'none' && tmp_flag == 1){
    theForm.subfilterStyleDisplay.value = 'block';
  }else{
    theForm.subfilterStyleDisplay.value = 'none';
  }
  theForm.submit();
}  
function updateFrequency(){
  if(!confirm("update frequency?")){
    return false;
  }
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
function showhide(DivID){
  var theForm = document.getElementById('form_comparison');
  if(typeof(newWin2) == 'object'){
      newWin2.close();
      theForm.target='_parent';
  }
  var obj = document.getElementById(DivID);
  var obj_a = document.getElementById(DivID + "_a");
  var sub_filter_area = document.getElementById('sub_filter_area');
  tmp_flag = 0;
  for(var i=0; i<theForm.frm_color_mode.length; i++){
    if(theForm.frm_color_mode[i].checked == true && theForm.frm_color_mode[i].value == 'shared'){
      tmp_flag = 1;
      break;
    }
  }  
  if(obj.style.display == "none"){
    sub_filter_area.style.display = "none";
    obj.style.display = "block";
    obj_a.innerHTML = "<font size='2' face='Arial'>[&nbsp;Click to remove filters&nbsp;]</font>";
    theForm.applyFilters.value = '1';
  }else{
    if(tmp_flag == 1){
      sub_filter_area.style.display = "block";
    }else{ 
      sub_filter_area.style.display = "none";
    } 
    obj.style.display = "none";
    obj_a.innerHTML = "<font size='2' face='Arial'>[&nbsp;Click to apply filters&nbsp;]</font>";
    theForm.applyFilters.value = '0';
  }
  submit_form();
}
</script>
<META content="MSHTML 6.00.2900.3199" name=GENERATOR></head>
<basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 
topMargin=5 rightMargin=5 marginheight="5" marginwidth="5" onload="Tooltip.init();">
<center>



<FORM ID="form_comparison" ACTION="<?php echo $PHP_SELF;?>" NAME="form_comparison" METHOD="POST">
  <INPUT TYPE="hidden" NAME="frm_selected_list_str" VALUE="<?php echo $frm_selected_list_str?>">
  <INPUT TYPE="hidden" NAME="totalHits" VALUE='<?php echo $totalHits;?>'>
  <INPUT TYPE="hidden" NAME="totalitems" VALUE='<?php echo $totalitems;?>'>
  <INPUT TYPE="hidden" NAME="passedTypeStr" VALUE='<?php echo $passedTypeStr;?>'>
  <INPUT TYPE="hidden" NAME="biggestPowedSore" VALUE="<?php echo $biggestPowedSore;?>">
  <INPUT TYPE="hidden" NAME="currentType" VALUE="<?php echo $currentType;?>">
  <INPUT TYPE="hidden" NAME="currentProperty" VALUE="<?php echo $orderby;?>">
  <INPUT TYPE="hidden" NAME="SearchEngine" VALUE="<?php echo $SearchEngine;?>">
  <INPUT TYPE="hidden" NAME="filterStyleDisplay" VALUE="<?php echo $filterStyleDisplay;?>">
  <INPUT TYPE="hidden" NAME="subfilterStyleDisplay" VALUE="<?php echo $subfilterStyleDisplay;?>">
  <INPUT TYPE="hidden" NAME="filtrColorIniFlag" VALUE="<?php echo $filtrColorIniFlag;?>">
  <INPUT TYPE="hidden" NAME="color_mode" VALUE="<?php echo $frm_color_mode;?>">
  <INPUT TYPE="hidden" NAME="applyFilters" VALUE="<?php echo $applyFilters;?>">
<?php 
$aa = '';
get_colorArrSets($powerColorIndex,$colorArrSet,$aa);
$item = $currentType.'ID';	

if($theaction != "popWindow"){
?>
<b><font face="Arial" size="+3"><?php echo $typeLable?> Comparison</font></b>
<table align="center" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=700>
  <tr>
		<td colspan='2' nowrap>
    <table align="center" bgcolor='' cellspacing="1" cellpadding="3" border="0" width=700>
      <tr bgcolor="#b7c1c8" height=28>
        <td width="15%" align="right" nowrap>
          <font size="2" face="Arial"><b>Color code</b></font>&nbsp;&nbsp;
        </td>
        <td >&nbsp;&nbsp;
          <font size="2">
          Hit property color code <input type=radio name='frm_color_mode' value='property' <?php echo ($frm_color_mode == 'property')?'checked':''?> onClick="change_color_code(this);">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          Shared hits color code <input type=radio name='frm_color_mode' value='shared' <?php echo ($frm_color_mode == 'shared')?'checked':''?> onClick="change_color_code(this);">
          </font>
        </td>
      </tr> 
      <tr bgcolor="#b7c1c8">
        <td nowrap align="right">
    			<font size="2" face="Arial"><b>Sort by</b></font>&nbsp;&nbsp;
           </td>
           <td nowrap >&nbsp;&nbsp;
           <select name="orderby" size=1>
            <option value=''>&nbsp; &nbsp; &nbsp;
            <option value='<?php echo $Expect?>' <?php echo  ($orderby==$Expect)?'selected':''?>><?php echo ($SearchEngine=='Mascot')?'Mascot Score':'GPM Expect'?><br>
            <option value='Pep_num' <?php echo ($orderby=='Pep_num')?'selected':''?>>Total Peptide Number<br>
            <option value='Pep_num_uniqe' <?php echo ($orderby=='Pep_num_uniqe')?'selected':''?>>Unique Peptide Number<br>
            <option value='Coverage' <?php echo ($orderby=='Coverage')?'selected':''?>>Coverage<br>
            <option value='Fequency' <?php echo ($orderby=='Fequency')?'selected':''?>>Project Frequency<br>
            <?php foreach($passedTypeArr as $passedTypeValue){?>
                  <option value='<?php echo $passedTypeValue;?>' <?php echo  ($orderby==$passedTypeValue)?'selected':''?>><?php echo $passedTypeValue;?> Frequency<br>
            <?php }?>      
          </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <font size="2" face="Arial"><?php echo $typeLable?> ID&nbsp;
          <select name="sort_by_item_id" size=1>
              <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
            <?php foreach($itemLableArr as $itemID => $lable){?>
              <option value='<?php echo $itemID?>' <?php echo  ($sort_by_item_id==$itemID)?'selected':''?> <?php echo (strstr($itemID, 'C_'))?"class=$itemID":""?>><?php echo (strstr($itemID, 'C_'))?$lable:$itemID."&nbsp;&nbsp;".$lable?><br>
            <?php }?>
          </select>&nbsp;&nbsp;&nbsp;&nbsp;
          Descending<input type=radio name=asc_desc value='DESC' <?php echo (isset($asc_desc) && $asc_desc=='DESC')?'checked':''?>>&nbsp;&nbsp;&nbsp;
          Ascending<input type=radio name=asc_desc value='ASC' <?php echo (isset($asc_desc) && $asc_desc=='ASC')?'checked':''?>>&nbsp;
        </td>
      </tr>
    </table> 
    </td>
	</tr>
  <tr>
		<td colspan='2' nowrap>
      <a id=filter_area_a onclick="showhide('filter_area')"><font size="2" face="Arial">[&nbsp;<?php echo ($filterStyleDisplay=='none')?'Click to apply filters':'Click to remove filters'?>&nbsp;]</font></a>    </td>
	</tr>
  <tr bgcolor="#b7c1c8">
    <td colspan='2'>
    <DIV ID="sub_filter_area" style="display:<?php echo $subfilterStyleDisplay;?>">
    <table align="center" bgcolor='' cellspacing="3" cellpadding="3" border="0" width=100%>
    <tr bgcolor="#b7c1c8">
    <font size="2" face="Arial">
    <td width=25% align="right">&nbsp;&nbsp;<font size="2" face="Arial"><b>Hit found in</b> all <?php echo $typeLable;?>s</font>&nbsp;&nbsp;&nbsp;</td>
    <td width=8% align=center bgcolor='<?php echo $red;?>'>&nbsp;&nbsp;</td>
  <?php if(!in_array($contrlColor, $itemIdIndexArr)){?>
    <td width=25% align="right">&nbsp; &nbsp;<font size="2" face="Arial"> more than one <?php echo $typeLable;?>s</font>&nbsp;&nbsp;&nbsp;</td>
    <td width=8% align=center bgcolor='<?php echo $green;?>'>&nbsp;&nbsp;</td>
  <?php }?>      
    <td width=15% align=right nowrap>&nbsp; &nbsp;<font size="2" face="Arial"> one <?php echo $typeLable;?></font>&nbsp;&nbsp;</td>
    <td width=8% align=center bgcolor='<?php echo $blue;?>'></td>
    <td >&nbsp;&nbsp;</td>
    </tr>
    </table>
    </DIV>
    </td>
  </tr> 
  <tr>
    <td colspan='2'>
      <DIV ID="filter_area" style="display:<?php echo $filterStyleDisplay;?>">
        <table align="center" bgcolor='' cellspacing="1" cellpadding="3" border="0" width=100%>
          <tr bgcolor="#b7c1c8">
            <td align="" width=50%>&nbsp;&nbsp;&nbsp;<font size="2" face="Arial"><?php echo ($SearchEngine=='Mascot')?'Mascot Score <':'GPM Expect >'?></font>
              <?php 
              create_filter_list($Expect,'frm_filter_Expect',10);
              ?>
						</td>
						 <td align="">&nbsp;<font size="2" face="Arial">Coverage <</font>
              <?php 
              create_filter_list('Coverage','frm_filter_Coverage',1);
              ?>
              <font size="2" face="Arial">%</font> 
            </td>
          </tr>
          <tr bgcolor="#b7c1c8">
						<td align="">&nbsp;&nbsp;<font size="2" face="Arial">&nbsp;Peptide&nbsp;</font>
              <select name="frm_filter_Peptide" size=1>
                <option value=''>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <option value='Pep_num' <?php echo ($frm_filter_Peptide=='Pep_num')?'selected':''?>>Total Peptide
                <option value='Pep_num_uniqe' <?php echo ($frm_filter_Peptide=='Pep_num_uniqe')?'selected':''?>>Unique Peptide              
              </select>
              <font size="2" face="Arial">&nbsp;<&nbsp;</font>
              <?php 
              create_filter_list('Pep_num','frm_filter_Peptide_value',1);
              ?>            
						</td>
            <td align="">&nbsp;<font size="2" face="Arial">Fequency&nbsp;&nbsp;</font>
              <select name="frm_filter_Fequency" size=1>
                <option value=''>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <option value='Fequency' <?php echo ($frm_filter_Fequency=='Fequency')?'selected':''?>>Project Fequency
                <?php 
                  foreach($passedTypeArr as $key => $value){
                    echo "<option value=\"$key\" ".(($frm_filter_Fequency==$key)?'selected':'').">$value Fequency";
                  }
                ?>           
              </select>
              <font size="2" face="Arial">&nbsp;>&nbsp;</font>
               <?php 
               create_filter_list('Fequency','frm_filter_Fequency_value',1);
               ?>  
            <font size="2" face="Arial">%</font>
            </td>
          </tr>
       <?php if($frm_color_mode == 'shared'){?>  
          <tr bgcolor="#b7c1c8">
            <td colspan='2'>
            <table align="center" bgcolor='' cellspacing="1" cellpadding="1" border="0" width=100%>
            <tr bgcolor="#b7c1c8">
            <font size="2" face="Arial">
            <td width=25% align="right">&nbsp;&nbsp;<font size="2" face="Arial"><b>Hit found in</b> all <?php echo $typeLable;?>s</font>&nbsp;&nbsp;&nbsp;</td>
            <td width=8% align=center bgcolor='<?php echo $red;?>'><input type="checkbox" name="frm_red" value="y" <?php echo ($frm_red)?'checked':'';?>></td>
       <?php if(!in_array($contrlColor, $itemIdIndexArr)){?>
            <td width=25% align="right">&nbsp; &nbsp;<font size="2" face="Arial"> more than one <?php echo $typeLable;?>s</font>&nbsp;&nbsp;&nbsp;</td>
            <td width=8% align=center bgcolor='<?php echo $green;?>'><input type="checkbox" name="frm_green" value="y" <?php echo ($frm_green)?'checked':'';?>></td>
      <?php }?>      
            <td width=15% align=right nowrap>&nbsp; &nbsp;<font size="2" face="Arial"> one <?php echo $singleTypeLable;?></font>&nbsp;&nbsp;</td>
            <td width=8% align=center bgcolor='<?php echo $blue;?>'><input type="checkbox" name="frm_blue" value="y" <?php echo ($frm_blue)?'checked':'';?>></td>
            <td >&nbsp;&nbsp;</td>
            </tr></table></td>
          </tr> 
       <?php }?>            
        </table>
      </DIV>
		</td>&nbsp;<br>&nbsp;
  </tr>
  <tr>
		<td colspan=3>
		<table align="" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=100%>
		<tr>
  <?php if($frm_color_mode != 'shared'){
		  print_color_bar($colorArrSet);
    }else{
      print_shared_color_bar();
    }
  ?>  
	  <td align="right"><input type=button value='Update Frequency' onClick='javascript: updateFrequency();'>&nbsp;&nbsp;&nbsp;</td>	
	  <td align="right"><input type=button name=sort_submit value="     GO     " onclick="sort_page();">&nbsp;&nbsp;&nbsp;</td>		
  </tr>
	</table>
	</td>
	</tr>
</table>	
<?php 
}
if($theaction == "showImage"){
  $zoomW = $cellW * $subPopWinSize;
  $zoomH = $cellH * $subPopWinSize;
  if($zoomW >= $overallWidth) $zoomW = $overallWidth;
  if($zoomH >= $noLableHeight) $zoomH = $noLableHeight;
  $tipW = -1 * $zoomW/2;
  $tipH = -1 * $zoomH/2;
?>
  <INPUT ID="gif" TYPE="image" NAME="gif" WIDTH=<?php echo $overallWidth;?> ALT="" 
  SRC="./comparison_results_image.php" onClick="generateSubReport();" onmouseup="showZoom(event,'zoomDiv',<?php echo $tipW;?>,<?php echo $tipH;?>);">
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
  <table><tr><td>
  <a href='javascript: popTest();'>[Test Image]</a>
  </td></tr>
  </table>
</BODY>
</HTML> 
<?php 
exit;
}else{
  if($theaction == "showNormal"){
  ?>
<table align="center" bgcolor="" cellspacing="0" cellpadding="3" border="0" width=750>
  <tr>
    <td align=right><font size=2>
      [<a href="./comparison_results_export.php?infileName=<?php echo $reportFileName;?>">Export Report</a>] &nbsp;
    </font></td>
  </tr>
</table>
  <?php }?>
<table align="center" bgcolor="" cellspacing="0" cellpadding="0" border="0" width=750>
  <tr>
  <?php 
  for($j=$start_J_index; $j<$end_J_index; $j++){
    if(strstr($itemIdIndexArr[$j], 'C_')){
      $lableBgc = str_replace("C_", "", $itemIdIndexArr[$j]);
      $itemLable = $itemLableArr[$itemIdIndexArr[$j]];
      $lableDetail = $groupArr[$itemIdIndexArr[$j]]['itemInfo'];
  ?>
    <td colspan="" class=s20 align=center bgcolor=<?php echo $lableBgc?> rowspan="2" onmouseover="doTooltipmsg(event, '<?php echo $lableDetail;?>')"  onmouseout=hideTip()>
  <?php }else{
      $itemLable = $itemIdIndexArr[$j].' '.$itemLableArr[$itemIdIndexArr[$j]];
      $lableBgc = '000000';
  ?>
    <td colspan="" class=s20 align=center bgcolor=<?php echo $lableBgc?> rowspan="2">
  <?php }?>
      <img src='./comparison_results_create_image.php?strMaxL=<?php echo $itemlableMaxL;?>&displayedStr=<?php echo $itemLable;?>&lableBgc=<?php echo $lableBgc;?>&fontSize=2' border=0></font>
    </td>
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
  //fwrite($reportFile_handle, "contents_start:\r\n");
  $sharedColorSet = array();
  if($frm_color_mode == 'shared'){
    create_colorArr_set($sharedColorSet,'green');
  }
  
  $TB_CELL_COLOR = '#ff7575';
  if(count($hitsGeneIdIndexArr) < $end_I_index) $end_I_index = count($hitsGeneIdIndexArr);  
  for($i=$start_I_index; $i<$end_I_index; $i++){
    if(!array_key_exists($hitsGeneIdIndexArr[$i], $hitsNameArr)){
      continue;
    }else{
      $sharedHitNmb = $hitsNameArr[$hitsGeneIdIndexArr[$i]]['counter'];
    }  
    $freqStr = '';
    $fequencySore = '&nbsp;';
    $subFequencySore = '&nbsp;';
    if(isset($FeqValueArr[$hitsGeneIdIndexArr[$i]])){
      $fequencySore = $FeqValueArr[$hitsGeneIdIndexArr[$i]];
      $freqStr = "<br>Project Frequency: ".$fequencySore."%";
    }
    $subFreqStr = '';
    if(isset($subFeqsValueArr) && count($subFeqsValueArr)){
      $subFreqStr = '';
      foreach($subFeqsValueArr as $fKey => $fValue){
        if(array_key_exists($hitsGeneIdIndexArr[$i], $fValue)){
          if($orderby == $passedTypeArr[$fKey]){
            if($fValue[$hitsGeneIdIndexArr[$i]]){
              $subFequencySore = $fValue[$hitsGeneIdIndexArr[$i]];
            }  
          }  
          $subFreqStr .= "<br>SubFrequency[".$passedTypeArr[$fKey]."]: ".$fValue[$hitsGeneIdIndexArr[$i]]."%";
        }  
      }
    }
    $haredeFreqStr = "<br>Shared Hits Frequency: $sharedHitNmb/$totalitems=" . round($sharedHitNmb*100/$totalitems,1)."%";
    
    if($frm_color_mode == 'shared'){
      $cellBgcolor = classify_filter($i);
      if(!$cellBgcolor) continue;
    }  
  ?>
  <tr bgcolor="#ececec" onmousedown="highlightTR(this, 'click', '#CCFFCC', '#ececec')";>
  <?php 
    $lineInfo = '';
    $counter = 0;
    
    for($j=$start_J_index; $j<$end_J_index; $j++){
    
      $temHitsArr = $itemNameArr[$itemIdIndexArr[$j]];
      if(array_key_exists($hitsGeneIdIndexArr[$i], $temHitsArr)){
        $upStr = '';
        if(strstr($itemIdIndexArr[$j], 'C_')){
          $upStr .= $groupArr[$itemIdIndexArr[$j]]['itemInfo'].'<br>--------------<br>'; 
        }else{
          $upStr .= '<font color=green><b>'.$singleTypeLable.'ID&nbsp;&nbsp;GeneName</b></font><br>'.$itemIdIndexArr[$j].'&nbsp;&nbsp;'.$itemLableArr[$itemIdIndexArr[$j]].'<br>--------------<br>';
        }
        $tmpHitInfoArr = explode('##',$temHitsArr[$hitsGeneIdIndexArr[$i]]);
        $hitSore = 0;
        $subFequencyValue = '';
        if($orderby == 'HitGI'){
          $hitSore = $tmpHitInfoArr[0];
        }elseif($orderby == $Expect){
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
        
        $upStr .= "Protein ID: ".$tmpHitInfoArr[0]."<br>Score:&nbsp;".$tmpHitInfoArr[1]."<br>Total Peptide: ".$tmpHitInfoArr[2]."<br>Unique Peptide: ".$tmpHitInfoArr[3]."<br>Protein Coverage: ".$tmpHitInfoArr[4].'%'.$freqStr.$subFreqStr.$haredeFreqStr;
        if($frm_color_mode != 'shared'){
          if($orderby == "Expect2"){
            $hitSoreForColor = -1 * $hitSore;
          }else{
            $hitSoreForColor = $hitSore;
          }
          $cellBgcolor = color_num($hitSoreForColor, $colorIndex);
          ($colorIndex >= 7)?$numOfClass='s13':$numOfClass='s14';
        }else{  
          $numOfClass = 's14';
        }  
        if($counter) $lineInfo .= ',';
        $lineInfo .= $tmpHitInfoArr[0].':'.$tmpHitInfoArr[1].'('.$tmpHitInfoArr[2].'-'.$tmpHitInfoArr[3].'-'.$tmpHitInfoArr[4].')';
        $counter++;
     ?>
        <td class=<?php echo $numOfClass;?> align=center bgcolor='<?php echo $cellBgcolor;?>' onmouseover="doTooltipmsg(event, '<?php echo $upStr;?>')"  onmouseout=hideTip()><?php echo trim($hitSore);?></td>
     <?php 
      }else{
        if($counter) $lineInfo .= ',';
        $counter++;
      ?>
        <td align=center class=s15>&nbsp;</td>
      <?php 
      }
    }
    list($tmpGeneName,$tempProteinID,$tmpLocusTag) = explode(',',$hitsNameArr[$hitsGeneIdIndexArr[$i]]['name']);
    $tempHitGeneID = $hitsGeneIdIndexArr[$i];
    if(preg_match("/_GI$/", $hitsGeneIdIndexArr[$i])){
      $tempHitGeneID = '';
    }
    if(!($GeneIdURL = get_URL_str('',$tempHitGeneID,'',$tmpGeneName,'comparison'))) $GeneIdURL = "&nbsp;";
    if(!($ProteinIdURL = get_URL_str($tempProteinID,'','','','comparison'))) $ProteinIdURL = "&nbsp;";
    if(!($orfURL = get_URL_str('','',$tmpLocusTag,'','comparison'))) $orfURL = "&nbsp;";
  ?>
  <?php if($hasGeneID){?>
      <td class=s16 align=center align=center><?php echo $GeneIdURL;?></td>
  <?php }
    if($hasProteinID){?>
      <td align=center class=s17 bgcolor=<?php echo ($frm_color_mode == 'shared')?$cellBgcolor:'#d6d6d6'?> align=center><?php echo $ProteinIdURL;?></td>
  <?php }
    if($hasLocusTag){?> 
      <td class=s22 bgcolor=#d6d6d6 align=center nowrap><?php echo $orfURL;?></td>
  <?php }?> 
  </tr>
  <?php 
    if(!$tempHitGeneID) $tempHitGeneID = $tempProteinID;
    $lineLable = $tempHitGeneID.','.$tmpGeneName.','.$tmpLocusTag;
    if($frm_color_mode == 'shared'){
      $lineInfo = $lineLable.','.$lineInfo.'@'.$cellBgcolor."\r\n";
    }else{
      $lineInfo = $lineLable.','.$lineInfo."\r\n";
    }
    fwrite($reportFile_handle, $lineInfo);
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
function print_shared_color_bar(){
	global $powerColorIndex,$theaction,$orderby;
  $powerColorIndex = 'Fequency';
  $colorArrSet = array();
  $aa = '';
  get_colorArrSets(1, $colorArrSet,$aa,'shared');
  
	$colorBarTotalW = 250;
	$colorCellW = 23;
	$colorCellH = 40;
  $maxScoreLable = 100;
?>			
    <td width=<?php echo $colorBarTotalW;?> colspan=2>
      <table align="" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=98%>
        <tr height=40>
    <?php 
      $Key = 0;
      foreach($colorArrSet as $colorCell){
				if($theaction == "showNormal"){
		?>        
          <td valign=top width=<?php echo $colorCellW;?> class=s21 bgcolor='<?php echo $colorCell;?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <?php }else{?>
					<td valign=top width=<?php echo $colorCellW;?> class=s21 ><img src='./comparison_results_create_image.php?imageW=<?php echo $colorCellW;?>&imageH=<?php echo $colorCellH;?>&colorkey=<?php echo $Key;?>&powerColorIndex=<?php echo $powerColorIndex;?>' border=0></td>	
			<?php 
          $Key++;
				}
			}
		?> 
          <td valign=top width=<?php echo $colorCellW;?> class=s21>&nbsp;</td>         
        </tr>
        <tr>
          <?php 
          $i = 0;
          foreach($colorArrSet as $olorKey => $colorCell){              
          ?>     
          <td valign=top width=<?php echo $colorCellW;?> Valign="top"><?php echo 10*$i++?></td>
          <?php }?>
          <td valign=topwidth=<?php echo $colorCellW;?> Valign="top" nowrap><?php echo round($maxScoreLable)?><?print_colorbar_lable($orderby)?></td>          
        </tr>        
      </table>
    </td>
<?php 
}
function print_color_bar(&$colorArrSet){
	global $biggestPowedSore,$power,$powerColorIndex,$maxScore,$theaction,$orderby;
	$colorBarTotalW = 250;
	$colorCellW = 23;
	$colorCellH = 40;
  $maxScoreLable = $maxScore;
  if($orderby == "Expect2") $maxScoreLable = -1 * $maxScore;
	
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
	//get_colorArrSets($powerColorIndex, $colorArrSet,$aa);
	
?>			
    <td width=<?php echo $colorBarTotalW;?> colspan=2>
      <table align="" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=<?php echo $colorBarTotalRealW;?>>
        <tr height=40>
    <?php 
      $Key = 0;
      foreach($colorArrSet as $colorCell){
				if($theaction == "showNormal"){
		?>        
          <td valign=top width=<?php echo $colorCellW;?> class=s21 bgcolor='<?php echo $colorCell;?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <?php }else{?>
					<td valign=top width=<?php echo $colorCellW;?> class=s21 ><img src='./comparison_results_create_image.php?imageW=<?php echo $colorCellW;?>&imageH=<?php echo $colorCellH;?>&colorkey=<?php echo $Key;?>&powerColorIndex=<?php echo $powerColorIndex;?>' border=0></td>	
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
              if($orderby == "Expect2") $colorNumber = -1 * $colorNumber
          ?>     
          <td valign=top width='<?php echo $colorCellW;?>'><?php echo $colorNumber?></td>
          <?php }?>
          <td valign=top width='200' nowrap><?php echo round($maxScoreLable)?><?print_colorbar_lable($orderby)?></td>          
        </tr>        
      </table>
    </td>
<?php 
}

function get_total_hits(){
	global $HITSDB, $frm_selected_item_str,$currentType, $SearchEngine;
	$itemID = $currentType.'ID';
  if($SearchEngine == 'GPM'){
    $subWHERE = "WHERE SearchEngine='GPM' ";
  }else{
    $subWHERE = "WHERE SearchEngine!='GPM' AND SearchEngine!='Sonar' ";
  }
  //echo subWhere();
  if($subFilteStr = subWhere()){
    $subWHERE .= $subFilteStr; 
  }         
	$SQL = "SELECT GeneID 
          FROM Hits ";
  $WHERE = $subWHERE . " AND (GeneID!=0 AND GeneID IS NOT NULL AND GeneID !='') 
          AND $itemID IN($frm_selected_item_str) 
          GROUP BY GeneID";
	$SQL .= $WHERE;
  $hitsArrTmp = $HITSDB->fetchAll($SQL);

	$subTotalHits = count($hitsArrTmp);
	$SQL = "SELECT HitGI 
          FROM Hits ";
  $WHERE = $subWHERE . " AND (GeneID=0 OR GeneID IS NULL OR GeneID ='') 
          AND $itemID IN($frm_selected_item_str) 
          GROUP BY HitGI";
	$SQL .= $WHERE;
  $hitsArrTmp2 = $HITSDB->fetchAll($SQL);
	$subTotalHits2 = count($hitsArrTmp2);
	$totalHits = $subTotalHits + $subTotalHits2;
	return $totalHits;
}
function get_max_value($valueName){
  global $HITSDB,$currentType,$frm_selected_item_str,$MAX;
	$itemID = $currentType.'ID';
	$SQL = "SELECT $MAX(".$valueName.") as biggestNum FROM Hits WHERE $itemID IN($frm_selected_item_str)";
	$hitsArrTmp2 = $HITSDB->fetch($SQL);
	$maxScore = $hitsArrTmp2['biggestNum'];
  if($maxScore < 0) $maxScore = -1 * $maxScore;
  return $maxScore;
}
function create_filter_list($listName,$frmName,$numLen){
  global $$frmName,$orderby;
  if($listName == 'Fequency' || $listName == 'Coverage'){
    $biggestNum = 100;
  }else{  
    $biggestNum = get_max_value($listName);
  }
  $sign = '';
  if($listName == 'Expect2'){
    $sign = '-';
  } 
  $numbers = '1';
  $kk = 1;
  if($orderby == "Expect") $numLen = 1;
  echo "<select name=\"$frmName\" size=1>\r\n";
  if($listName == 'Fequency'){
    echo "<option value='101' selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  }else{
    echo "<option value='0' selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  } 
  while($numbers < $biggestNum){
    $numbers = $numLen * $kk;
    echo "<option value=\"$sign$numbers\" ".(($$frmName==$sign.$numbers)?'selected':'').">$sign$numbers\r\n";
    $kk++; 
  } 
  echo '</select>';
}
function classify_filter($i){
  global $contrlColor,$hitsGeneIdIndexArr,$itemIdIndexArr,$hitsNameArr,$red,$blue,$sharedColorSet,$totalitems;
  global $frm_red,$frm_green,$frm_blue,$applyFilters;
  if(!$applyFilters){
    $frm_red = 'y';
    $frm_green = 'y';
    $frm_blue = 'y';
  }
  if(in_array($contrlColor, $itemIdIndexArr)){
    if($hitsNameArr[$hitsGeneIdIndexArr[$i]]['ctr'] && $hitsNameArr[$hitsGeneIdIndexArr[$i]]['counter'] > 1){
      if(!$frm_red) return '';
      $cellBgcolor = $red;
    }else{
      if(!$frm_blue) return '';
      $cellBgcolor = $blue;
    }
  }else{
    if($hitsNameArr[$hitsGeneIdIndexArr[$i]]['counter'] == $totalitems){
      if(!$frm_red) return '';
      $cellBgcolor = $red;
    }elseif($hitsNameArr[$hitsGeneIdIndexArr[$i]]['counter'] == 1){
      if(!$frm_blue) return '';
      $cellBgcolor = $blue;
    }else{
      if(!$frm_green) return '';
      $colorIndex = floor($hitsNameArr[$hitsGeneIdIndexArr[$i]]['counter']*10/$totalitems);
      $cellBgcolor = $sharedColorSet[$colorIndex];
    }
  }
  return $cellBgcolor;
}

function push_in_itemLableArr_for_popWindow($j,&$itemLableArr,&$itemlableMaxL){
  global $itemIdIndexArr,$groupArr;
  $lableLen = 0;
  if(strstr($itemIdIndexArr[$j], 'C_')){
    $itemLable = $groupArr[$itemIdIndexArr[$j]]['lable'];
    $lableLen = strlen($itemLable);  
  }else{
    get_elements_property($itemArr,$itemIdIndexArr[$j]);								
    if($itemArr['GeneName'] && $itemArr['GeneName'] != "-"){
      $itemLable = $itemArr['GeneName'];
    }elseif($itemArr['LocusTag'] && $itemArr['LocusTag'] != "-"){
      $itemLable = $itemArr['LocusTag'];
    }else{
      $itemNoName++;
      $itemLable = "noName-".$itemNoName;
    }
    $joinedLable = $itemIdIndexArr[$j].'@@'.$itemLable;
    $lableLen = strlen($joinedLable);
  }   
  $itemLableArr[$itemIdIndexArr[$j]] = $itemLable;
  if($lableLen > $itemlableMaxL) $itemlableMaxL = $lableLen;
}

function create_NoteType_info(&$passedTypeStr,&$typeInitIdArr,&$passedTypeArr){
  global $HITSDB,$AccessProjectID,$typeStr,$tmpRealItemCounter;
  $typeCounArr = array();
  $SQL = "SELECT `ID`,`Initial` FROM `NoteType` WHERE `ProjectID`=$AccessProjectID AND type='Bait'";
  $noteTypeArr = $HITSDB->fetchAll($SQL);
  foreach($noteTypeArr as $noteTypeValue){
    $typeCounArr[$noteTypeValue['Initial']] = 0;
    $typeInitIdArr[$noteTypeValue['Initial']] = $noteTypeValue['ID'];
  }
  if($typeStr){
    $tmpTypeArr = explode(',,', $typeStr);
    foreach($tmpTypeArr as $tmpTypeValue){
      $tmpTypeValue = str_replace("[", '', $tmpTypeValue);
      $tmpSubArr = explode(']', $tmpTypeValue);
      array_pop($tmpSubArr);
      foreach($tmpSubArr as $tmpSubValue){
        preg_match('/([A-Z]+)/', $tmpSubValue, $matches);
        if(array_key_exists($matches[1], $typeCounArr)){
          $typeCounArr[$matches[1]]++;
        }  
      }
    }
    foreach($typeCounArr as $typeKey => $typeValue){
      if($typeValue == $tmpRealItemCounter) $passedTypeArr[$typeInitIdArr[$typeKey]] = $typeKey; //array_push($passedTypeArr, $typeKey);
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
}
function print_colorbar_lable($orderby){
  global $passedTypeArr,$SearchEngine,$frm_color_mode ;
  $color = 'black';
  if($frm_color_mode == "shared"){
    echo "% &nbsp;&nbsp;<font color=$color><b>Shared Hits</b>";
  }elseif($orderby == 'Expect'){
    echo " &nbsp;&nbsp;<font color=$color><b>Mascot Score</b>";
  }elseif($orderby == 'Expect2'){
    echo " &nbsp;&nbsp;<font color=$color><b>GPM Expect</b>";
  }elseif($orderby == 'Pep_num'){
    echo " &nbsp;&nbsp;<font color=$color><b>Total Peptide Number</b>";
  }elseif($orderby == 'Pep_num_uniqe'){
    echo " &nbsp;&nbsp;<font color=$color><b>Uniqe Peptide Number</b>";
  }elseif($orderby == 'Coverage'){
    echo "% &nbsp;&nbsp;<font color=$color><b>Coverage</b>";
  }elseif($orderby == 'Fequency'){
    echo "% &nbsp;&nbsp;<font color=$color><b>Project Frequency</b>";
  }elseif(in_array($orderby, $passedTypeArr)){
    echo "% &nbsp;&nbsp;<font color=$color><b>$orderby Frequency</b>";
  }
}            
?>