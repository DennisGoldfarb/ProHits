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
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);  // it will execute for 24 hours
$filter_for = 'comparison';
//------------used by orderby---------
$PROBABILITY = '';
$TOTAL_NUMBER_PEPTIDES = '';
$UNIQUE_NUMBER_PEPTIDES = '';
$PERCENT_COVERAGE = '';
//--------------------------------------

$theaction = '';
$sqlOrderby = '';
$orderby = "Pep_num";
$sort_by_item_id = '';
$asc_desc = 'DESC';
$maxScore = 0;

$subPopWinSize = 25;
if($subPopWinSize%2) $subPopWinSize = $subPopWinSize + 1;
$itemsNumLimit = 40;//==========================================================
$overallWidth =800;
$overallHeight =700;

//$subPopWinSize = 2;
//$itemsNumLimit = 2;//==========================================================

$power = 1;
$colorSet = '';
$typeStr = '';

$powerArr['Expect2'] = 1/2;
$powerArr['SpectralCount'] = 1;
$powerArr['SpectralCount'] = 1;
$powerArr['Pep_num'] = 1/2;
$powerArr['Pep_num_uniqe'] = 1;
$powerArr['Unique'] = 1;
$powerArr['Coverage'] = 1;     
$powerArr['Fequency'] = 1;

$itemlableMaxL = 0;
$itemLableArr = array();
$source = '';
$totalHits = '';

$hasGeneID = 0;
$hasProteinID = 1;
$hasLocusTag = 0;
$itemNoName = 0;
$contrlColor = 'C_FFFF00';
$ungroupedItemColor = 'C_FFFFFF';

$frm_filter_Expect = 0;
$frm_filter_Probability = 0;
$frm_filter_Coverage = 0;
$frm_filter_Peptide = '';
$frm_filter_Peptide_value = 0;
$frm_filter_Fequency = '';
$frm_filter_Fequency_value = 0;
$frm_min_XPRESS = '';
$frm_max_XPRESS = '';

$filterStyleDisplay = 'none';
$subfilterStyleDisplay = 'none';
$applyFilters = 0;
$frm_apply_filter = 0;

$red = '#ff8080';
$blue = '#00bfff';
$green = '#92ef8f';
//$green = '#5bff5b';

$frm_red = ''; 
$frm_green = ''; 
$frm_blue = '';
$filtrColorIniFlag = 0;
$passedTypeStr = '';
$php_file_name = "comparison_results_table";
$cellBgcolor = '';

$Is_geneLevel = 0;
$maxScore_original = 0;

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");
//@require_once("common/HTTP/Request_Prohits.php");
ini_set("memory_limit","2000M");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/
//===========================================================================
if(STORAGE_IP == 'localhost'){
   $storage_url = "";
}else{
  $storage_url = "http://".STORAGE_IP;
}
$selected_id_string = '';
$SearchEngine_for_raw = $SearchEngine;
$tmp_item_arr = explode(':',$frm_selected_list_str);
$tmp_item_str = $tmp_item_arr[1];
if($currentType == 'Bait' || $currentType == 'Exp'){
  if($currentType == 'Bait'){
    $itemID = 'BaitID';
  }else{
    $itemID = 'ExpID';
  }
  $SQL = "SELECT `ID` FROM `Band` WHERE $itemID IN ($tmp_item_str)";
  $tmp_sample_arr = $HITSDB->fetchAll($SQL);
  foreach($tmp_sample_arr as $tmp_sample_val){
    if($selected_id_string) $selected_id_string .= ',';
    $selected_id_string .= $tmp_sample_val['ID'];
  }
}else{
  $selected_id_string = $tmp_item_str;
}
//==============================================================================

//echo "$Is_geneLevel=$Is_geneLevel<br>";
if($Is_geneLevel){
  if($orderby == "Pep_num") $orderby = 'Unique';
}
?>
<center>
<div style='display:block' id='process'><img src='./images/process.gif' border=0></div>
</center>
<?php 
ob_flush();
flush();
//-------------------------------------------------------------------------------------------------------------
$SearchEngineConfig_arr = get_project_SearchEngine();
$SearchEngine_lable_arr = get_SearchEngine_lable_arr($SearchEngineConfig_arr);
//-------------------------------------------------------------------------------------------------------------

if($frm_apply_filter){
  $applyFilters = '1';
  $frm_apply_filter = '1';
  $filterStyleDisplay = 'block';
}

if(strstr($SearchEngine, 'TPP_')){
  $powerArr['Expect'] = 1;
}else{
  $powerArr['Expect'] = 1/2;
}  

if(!_is_dir("../TMP/bioGrid/")) _mkdir_path("../TMP/bioGrid/");
$tmp_file = "../TMP/bioGrid/". $USER->ID .".csv";

if(!$matchGred_handle = fopen($tmp_file, 'w')){
  echo "Cannot open file ($tmp_file)";
}
fwrite($matchGred_handle,"edge_info\r\n");

if(!isset($frm_NS_group_id) || !$frm_NS_group_id){
  $frm_NS_group_id = '';
}

$SQL = "SELECT `ID`,`Name` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
$NSarr = $HITSDB->fetchAll($SQL);

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);

if(strstr($SearchEngine, 'TPP_')){
  $hitType = 'TPP';
}elseif(strstr($SearchEngine, 'GeneLevel_')){
  $hitType == 'geneLevel';
}else{
  $hitType = 'normal';
}

$typeBioArr = array();
$typeExpArr_tmp = array();
$typeExpArr = array();
$typeFrequencyArr = array();
create_filter_status_arrs($typeBioArr,$typeExpArr_tmp,$typeFrequencyArr,'comparison');
$filterArgumentsStr = '';
foreach($typeBioArr as $typeBioValue){
  $frmName = 'frm_' . $typeBioValue['Alias'];
  if($theaction == 'generate_report' && !$frm_apply_filter){
    $$frmName = $typeBioValue['Init'];
  }else{
    if(!isset($$frmName)){
      $$frmName = "0";
    }
  }
  $filterArgumentsStr .= '@@'.$frmName.'='.$$frmName;
}
$NStmpArr = array();
foreach($typeExpArr_tmp as $typeExpValue){
  if($typeExpValue['Alias'] == 'OP'){
    continue;
  }elseif($typeExpValue['Alias'] == 'NS'){
    $NStmpArr = $typeExpValue;
  }else{
    array_push($typeExpArr, $typeExpValue);
  }
}
if($NStmpArr) array_unshift($typeExpArr, $NStmpArr);
foreach($typeExpArr as $typeExpValue){
  if($typeExpValue['Alias'] == 'OP') continue;
  $frmName = 'frm_' . $typeExpValue['Alias'];
  if($theaction == 'generate_report' && !$frm_apply_filter){
    $$frmName = $typeExpValue['Init'];
  }else{
    if(!isset($$frmName)){
      $$frmName = "0";
    }
  }
  $filterArgumentsStr .= '@@'.$frmName.'='.$$frmName;
}
$NSfilteIDarr = array();
if(isset($frm_NS) && $frm_NS && $frm_NS_group_id){
  get_NS_geneID($NSfilteIDarr,$frm_NS_group_id);
}

$A = isset($frm_BT) && !$frm_BT;

$subDir = "../TMP/comparison/P_$AccessProjectID/";
if(!_is_dir($subDir)) _mkdir_path($subDir);

$argumentsFileName = $subDir.$AccessUserID."_arguments.txt";
$selectedListStrFileName = $subDir.$AccessUserID."_selected_list_.txt";
$hitsIndexFileName = $subDir.$AccessUserID."_hits_index.txt";
$hitsNameFileName = $subDir.$AccessUserID."_hits_name.txt";
$reportFileName = $subDir.$AccessUserID."_report.txt";
$peptideComparesonFileName = $subDir.$AccessUserID."_peptide_compareson_map.txt";

$html_file_name = $subDir.$AccessUserID."_report.html";
$png_file_name = $subDir.$AccessUserID."_report.png";

if($currentType == 'Bait'){
  $typeLable = 'Bait';
  $singleTypeLable = 'Bait';
}elseif($currentType == 'Exp'){
  $typeLable = 'Experiment';
  $singleTypeLable = 'ExpID&nbsp;&nbsp;ExpName';
}else{
  $typeLable = 'Sample';
  $singleTypeLable = 'SampleID&nbsp;&nbsp;SampleName';
}

if($SearchEngine == 'GPM'){
  $Expect = 'Expect2';
  if(!$orderby || $orderby == 'Expect'){
    $orderby = 'Expect2';
  }
}else{
  $Expect = 'Expect';
  if(!$orderby){
    $orderby = 'Expect';
  }
}
if($orderby == 'Expect2'){
  $asc_desc = $DESC = 'ASC';
  $MAX = 'MAX';
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
$allBaitgeneID_str = '';
 
if($frm_selected_list_str){
  create_groupArr_otherStrs($groupArr,$frm_selected_item_str,$frm_selected_group_str,$no_groupped_str);
  $itemID_geneID_arr = array();  
  if($currentType == 'Bait'){
    $SQL = "SELECT `ID`,`GeneID`,GeneName, BaitAcc FROM `Bait` WHERE `ID` IN($frm_selected_item_str)";
    $group_table_name = "BaitGroup";
  }elseif($currentType == 'Band'){
    $SQL = "SELECT D.ID,B.GeneID,B.GeneName,B.BaitAcc 
            FROM Band D 
            LEFT JOIN Bait B 
            ON D.BaitID=B.ID 
            WHERE D.ID IN($frm_selected_item_str)";
    $group_table_name = "BandGroup";        
  }elseif($currentType == 'Exp'){
    $SQL = "SELECT E.ID,B.GeneID,B.GeneName,B.BaitAcc 
            FROM Experiment E 
            LEFT JOIN Bait B 
            ON E.BaitID=B.ID 
            WHERE E.ID IN($frm_selected_item_str)";
    $group_table_name = "ExperimentGroup";        
  }  
  $tmp_item_arr = $HITSDB->fetchAll($SQL);
  foreach($tmp_item_arr as $tmp_val){
    if(!$tmp_val['GeneID'] || $tmp_val['GeneID'] == '-1'){
      if(!$tmp_val['BaitAcc']){
        $itemID_geneID_arr[$tmp_val['ID']] = $tmp_val['GeneName'];
      }else{
        $itemID_geneID_arr[$tmp_val['ID']] = $tmp_val['BaitAcc'];
      }
    }else{
      $itemID_geneID_arr[$tmp_val['ID']] = $tmp_val['GeneID'];
    }  
  }
   
  $itemIdIndexArr = explode(',', $frm_selected_group_str);
  $tmpRealItemArr = explode(',',$frm_selected_item_str);
  $tmpRealItemCounter = count($tmpRealItemArr);
  $allBaitgeneIDarr = get_all_item_geneID_arr($frm_selected_list_str);
  $allBaitgeneID_str = implode(",", $allBaitgeneIDarr);
}else{
  echo "no input elements";
  exit;
}

if(!$sort_by_item_id) $sort_by_item_id = $itemIdIndexArr[0];
$totalitems = count($itemIdIndexArr);
$spesialWinW  = '';
if($totalitems <= $itemsNumLimit){
	$theaction = "showNormal";
}else{
	if(isset($gif_y)){
		if($subPopWinSize > $totalitems){
      $spesialWinW = $totalitems;
    }
		$theaction = "popWindow";
	}else{
		$theaction = "showImage";
	}
}

//echo "\$theaction=$theaction<br>";

if($theaction != "popWindow"){
  $reportFile_handle = fopen($reportFileName, "w");
  if(!$reportFile_handle){
    echo "Cannot open file $reportFileName";
  }
}

if($theaction == "showImage" && $source == 'comparison'){
  $selectedListStr_handle = fopen($selectedListStrFileName, "w");
  fwrite($selectedListStr_handle, $frm_selected_list_str);
  fclose($selectedListStr_handle);
}

$typeInitIdArr = array();
$passedTypeArr = array();

$selected_item_list_str = '';
$tmp_item_list_arr = explode(";",$frm_selected_list_str);
foreach($tmp_item_list_arr as $tmp_item_list_val){
  $tmp_item_list_arr2 = explode(":",$tmp_item_list_val);
  if($selected_item_list_str) $selected_item_list_str .= ",";
  $selected_item_list_str .= $tmp_item_list_arr2[1];
}

if($selected_item_list_str){
  create_NoteType_info($passedTypeStr,$typeInitIdArr,$passedTypeArr);
}

if($theaction != "popWindow"){
  $filter_export_arr = array();
  $filter_export_arr_2 = array();
  $filter_export_arr_3 = array();
  get_filter_array_for_export($request_arr);
  write_filter_info_map($reportFile_handle);
}

if($theaction != "popWindow"){
  $apply_bioGrid = 0;
  if(isset($frm_biogrid_pHTP) && $frm_biogrid_pHTP){
    $apply_bioGrid = 1;
  }elseif(isset($frm_biogrid_pNONHTP) && $frm_biogrid_pNONHTP){
    $apply_bioGrid = 1;
  }elseif(isset($frm_biogrid_gHTP) && $frm_biogrid_gHTP){
    $apply_bioGrid = 1;
  }elseif(isset($frm_biogrid_gNONHTP) && $frm_biogrid_gNONHTP){
    $apply_bioGrid = 1;
  }
  $lable_GeneName_ID_arr = array();
  $item_geneName_id_arr = array();
  create_item_lable_arr($itemLableArr,$itemlableMaxL);
  reportFile_title_info($groupArr,$itemLableArr,$itemlableMaxL,$totalitems);
}

// determine color set and power*****
if($orderby == 'Fequency' || in_array($orderby, $passedTypeArr) || strstr($orderby,"U:")){
  $sqlOrderby = "ID";
  $powerColorIndex = 'Fequency'; //--subFequency's power and coler Index are same as fequency
}else{
  $sqlOrderby = $orderby;
  $powerColorIndex = $orderby;
}
$power = $powerArr[$powerColorIndex]; //1/2, 1/3, 1......
//Fequency and subFequency
if(strstr($SearchEngine, 'TPP_')){
  $frequencyFileName = 'tpp_frequency.csv';
}else{
  $frequencyFileName = $SearchEngine.'_frequency.csv';
}
//------------------------------------------------------------------------

$FeqIndexArr = array();
$FeqValueArr = array();
$FeqFiltedGeneIdArr = array();
$fequMaxScore = get_fequency($FeqIndexArr, $FeqValueArr,$FeqFiltedGeneIdArr,$frequencyFileName); //-create a fequency sorted index and values arrays.

$subFeqIndexArr = array();
$subFeqsValueArr = array();
$typeNum = '';
$subFequMaxScore =0;
if(count($passedTypeArr)){
	if(array_key_exists($orderby, $typeInitIdArr)){
  	$typeNum = $typeInitIdArr[$orderby];
	}
//-------------------------------------------------------------------
  $subFequMaxScore = get_subFequency($subFeqIndexArr, $subFeqsValueArr,$FeqFiltedGeneIdArr,$passedTypeArr, $typeNum);
}

$optionArr_for_user_d_frequency = get_optionArr_for_user_d_frequency($frm_selected_list_str,$currentType,$hitType);

$userFeqIndexArr = array();
$userFeqsValueArr = array();
$u_file_name = '';
$userFequMaxScore =0;
if(count($optionArr_for_user_d_frequency)){
	if(array_key_exists($orderby, $optionArr_for_user_d_frequency)){
  	$u_file_name = $orderby;
	}
//-----------------------------------------------------------------
  $userFequMaxScore = get_userFequency($userFeqIndexArr,$userFeqsValueArr,$FeqFiltedGeneIdArr,$optionArr_for_user_d_frequency,$u_file_name);
}

//**get maxscore**********************************************
if(strstr($orderby,"U:")){
  if(!$maxScore_original){
    $maxScore_original = $userFequMaxScore;
    $maxScore = round($maxScore_original/2,-1);
  }else{
    $maxScore = $user_maxScore;
  }
}elseif($orderby == 'Fequency'){
  if(!$maxScore_original){
    $maxScore_original = $fequMaxScore;
    $maxScore = round($maxScore_original/2,-1);
  }else{
    $maxScore = $user_maxScore;
  }
}elseif(in_array($orderby, $passedTypeArr)){
  if(!$maxScore_original){
    $maxScore_original = $subFequMaxScore;
    $maxScore = round($maxScore_original/2,-1);
  }else{
    $maxScore = $user_maxScore;
  }
}else{
  if(!$maxScore_original){
    $maxScore_original = $maxScore = get_max_value($orderby);
    //$maxScore = round($maxScore_original/2,-1);
    $maxScore = round($maxScore_original,-1);
  }else{
    $maxScore = $user_maxScore;
  }
}
//***********************************************************

$biggestPowedSore = pow($maxScore,$power);
if($orderby != 'Expect2' && $biggestPowedSore <= 0) $biggestPowedSore = 1;

//**caculate totalHits format image size get
if($theaction == "showImage"){ 
  $totalHits =get_total_hits($frm_selected_item_str);
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
  $argumentsStr .= "@@fontSize=$fontSize@@fontH=$fontH@@sqlOrderby=$sqlOrderby@@typeNum=$typeNum@@frequencyFileName=$frequencyFileName";
  $argumentsStr .= "@@frm_filter_Expect=$frm_filter_Expect@@powerColorIndex=$powerColorIndex@@A=$A";
  $argumentsStr .= "@@red=$red@@blue=$blue@@green=$green@@applyFilters=$applyFilters@@noLableHeight=$noLableHeight@@frm_NS_group_id=$frm_NS_group_id@@Is_geneLevel=$Is_geneLevel";
  $argumentsStr .= $filterArgumentsStr;
  $argumentsStr .= $filterArgumentsStr;
  
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
        $tmpArr2['filter'] = $tmpArr1[2];
        $tmpArr2['counter'] = $tmpArr1[3];
        $tmpArr2['ctr'] = $tmpArr1[4];
        if(isset($tmpArr1[5])){
          $tmpArr2['isBait'] = $tmpArr1[5];
        }  
        $hitsNameArr[$tmpArr1[0]] = $tmpArr2;
  	  }
    }else{
      echo "Cannot write to file $hitsIndexFileName";
      exit;
    }	
/*echo "<pre>";  
print_r($hitsNameArr);  
echo "</pre>";  
exit;*/     
    
    
      
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
    if($spesialWinW == ''){  
	    $end_J_index = $start_J_index + $subPopWinSize;
    }else{
      $end_J_index = $start_J_index + $spesialWinW;
    }  
	}else{
	  $start_J_index = 0;
	  $end_J_index = count($itemIdIndexArr);
	}  
  $all_color_arr = array();  
  //----------------------jp 2016-07-29-----------------
  $all_gi_Acc_Version_arr = array();
  //----------------------------------------------------
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
  
/*echo "<pre>";  
print_r($hitsNameArr);  
echo "</pre>";  
exit;*/ 

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
    if(strstr($orderby,'U:')){
      $hitsGeneIdIndexArr = get_hitsGeneIdIndexArr_for_fequency($hitsGeneIdIndexArr,$userFeqIndexArr);
    }elseif($orderby == 'Fequency'){
	    $hitsGeneIdIndexArr = get_hitsGeneIdIndexArr_for_fequency($hitsGeneIdIndexArr,$FeqIndexArr);
	  }elseif(in_array($orderby, $passedTypeArr)){
	    $hitsGeneIdIndexArr = get_hitsGeneIdIndexArr_for_subFequency($hitsGeneIdIndexArr,$subFeqIndexArr);
	  }    
    if($applyFilters && isset($frm_NS_group_id) && $frm_NS_group_id){
      $NSfilteIDarr = array_diff($NSfilteIDarr, $allBaitgeneIDarr);
      $tmpHitsGeneIdIndexArr = array_diff($hitsGeneIdIndexArr, $NSfilteIDarr);
      $hitsGeneIdIndexArr = array();
      foreach($tmpHitsGeneIdIndexArr as $tmpIndexValue){
        array_push($hitsGeneIdIndexArr, $tmpIndexValue);
      }
    }
    $start_I_index = 0;
  	$end_I_index = count($hitsGeneIdIndexArr);  
	}
}

$item_index_lable_arr = array();
foreach($itemLableArr as $key => $value){
  if(array_key_exists($key,$groupArr)){
    $item_index_lable_arr[$key] = str_replace(",", "|", $groupArr[$key]['simpleInfo']);
  }else{
    $item_index_lable_arr[$key] = $key." ".$value;
  }
}

$html_handle = fopen($html_file_name, "w");
$html_str = "<html>\r\n<head>\r\n<meta http-equiv='content-type' content='text/html;charset=iso-8859-1'>\r\n<title>ProHits</title>\r\n";
fwrite($html_handle, $html_str);
echo "$html_str";

$tool_tip_style_name = "./tool_tip_style.css";
$html_str = file_get_contents($tool_tip_style_name);
fwrite($html_handle, "<style type='text/css'>\r\n");
fwrite($html_handle, $html_str);
fwrite($html_handle, "</style>\r\n");

$site_javascript_name = "../common/javascript/site_javascript.js";
$html_str = file_get_contents($site_javascript_name);
fwrite($html_handle, "<script language='javascript'>\r\n");
fwrite($html_handle, $html_str);
fwrite($html_handle, "</script>\r\n");
//echo "\$selected_id_string=$selected_id_string<br>";
?>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<link rel="stylesheet" type="text/css" href="./tool_tip_style.css">
<link rel="stylesheet" type="text/css" href="./colorPicker_style.css">
<script src="../common/javascript/site_javascript.js" type="text/javascript"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">
 
<script src="../common/javascript/jquery.hoverIntent.js" type="text/javascript"></script>

<script language='javascript'>
var newWin2;
var newWin2_pop = 0;
function generateSubReport(){
  theForm = document.form_comparison;
  if(typeof(nWin) == 'object'){
      nWin.close();
      theForm.target='_parent';
  }
  theForm.theaction.value = 'popWindow';
  theForm.action = '<?php echo $PHP_SELF;?>';
  file = 'loading.html';
  newWin2 = window.open(file,"detailWind",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=900,height=800');
  newWin2.focus();
  theForm.target = 'detailWind';
  newWin2_pop = 1;
  theForm.submit();
  //theForm.reset();
}

function pop_peptede_win(lineNum,GeneID,GeneName,protein_id,protein_OLD_id){
  theForm = document.form_comparison;
  theForm.method='post',
  theForm.action = 'comparison_results_peptide.php';
  theForm.target= 'myNewWin';
  theForm.lineNum.value = lineNum;
  theForm.GeneID.value = GeneID;
  theForm.GeneName.value = escape(GeneName);
  theForm.peptide_report_protein_id.value = protein_id;
  theForm.protein_old_id.value = protein_OLD_id;
  window.open("","myNewWin",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=900,height=800');
  var a = window.setTimeout("document.form_comparison.submit();",500); 
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
    if(theForm.SearchEngine.value == "TPP_Mascot" || theForm.SearchEngine.value == "TPP_COMET" || theForm.SearchEngine.value == "TPP_iProphet" || theForm.SearchEngine.value == "TPP_GPM"  || theForm.SearchEngine.value == "TPP_SEQUEST" || theForm.SearchEngine.value == "TPP_Other"){
      max_num = trim(theForm.frm_max_XPRESS.value);
      min_num = trim(theForm.frm_min_XPRESS.value);      
      if(max_num || min_num){
        if(!(is_numeric(max_num) && is_numeric(min_num))){
          alert("Enter Integer or float number only for max XPRESS Ratio or min XPRESS Ratio.");
          return false; 
        }else{
          if(max_num && min_num && min_num >= max_num){
            alert("max XPRESS Ratio should be greate than min XPRESS Ratio.");
            return false;
          }
        }
      }  
    }
    if(theForm.orderby.value != theForm.per_orderby.value){
      theForm.maxScore_original.value = 0;
    }
    theForm.target=  '_self';
    submit_form();
  }
}

function export_all(){
  theForm = document.form_comparison;
  theForm.method='post',
  theForm.action = "./export_hits.php";
  theForm.theaction.value = "generate_map_file";
  theForm.target= 'exportNewWin';
  theForm.lineNum.value = "";
  theForm.GeneID.value = "";
  theForm.GeneName.value = ""; 
  theForm.peptide_report_protein_id.value = "";  
  window.open("","exportNewWin",'toolbar=1,location=0,directories=0,status=0,menubar=1,scrollbars=1,resizable=1,width=900,height=800');
  theForm.submit();
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
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.submit();
}  

function updateFrequency(){
  if(!confirm("Are you sure that you want to update frequency?")){
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


function pop_sent_win(is_image){
  if(is_image == "Y"){
    theFileName = '<?php echo $png_file_name?>';
  }else{
    theFileName = '<?php echo $html_file_name?>';
  }
  file = "./pop_send_report_to_public.php?html_report_url=" + theFileName;
  nWin = window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=670,height=370');
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
}

function confirmdd_div(){
  var theForm = document.getElementById('form_comparison');
  theForm.action = "./comparison_results_export.php";
  theForm.infileName.value = '<?php echo $reportFileName;?>';
  if(theForm.theaction.value == "popWindow"){
    theForm.theaction.value = "showImage";
  }
  theForm.exportType.value = 'matrix';
  hideTip('matrix_confirm_div');
  theForm.submit();
  theForm.action = '<?php echo $PHP_SELF;?>';
}

function confirm_cyto_div(){
  var theForm = document.getElementById('form_comparison');
  var cyto_qurryStr = theForm.cyto_qurryStr.value;
  var lable = '';
  if(theForm.node_lable[0].checked == true){
    lable = "&node_lable="+ theForm.node_lable[0].value;
  }else if(theForm.node_lable[1].checked == true){
    lable = "&node_lable="+ theForm.node_lable[1].value;
  }else{
    return;
  }
  var allBaitgeneID_str =  "&allBaitgeneID_str="+theForm.allBaitgeneID_str.value; 
  hideTip('cyto_confirm_div');
  var file ="./cytoscape_export.php?"+cyto_qurryStr+lable+allBaitgeneID_str;
	window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=1200,height=820');
}
function image_test(){   
  file = './comparison_results_image.php';
  window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=620');
}
//=============================================================================================
function download_raw(){
  var theWin = '';
  if (!theWin.closed && theWin.location) {
      theWin.close();
  }
  var theForm = document.getElementById('form_comparison');
  theForm.setAttribute("action", "<?php echo $storage_url.dirname(dirname($_SERVER['PHP_SELF']))."/msManager/autoBackup/download_group_raw_file.php";?>");
  //theForm.setAttribute("action", "http://192.197.250.100/testphp/check_post_get.php"); 
  theForm.SID.value = "<?php echo session_id()?>";
  theForm.ID_string.value = "<?php echo $selected_id_string?>";
  theForm.SearchEngine.value = '<?php echo $SearchEngine_for_raw?>';
  theForm.hitType.value = '<?php echo $SearchEngine_for_raw?>';
  theForm.item_type.value = 'Sample';
  
//alert(theForm.ID_string.value);
//alert(theForm.SearchEngine.value);
//alert(theForm.hitType.value);

//return;  
  theWin = window.open("test.html","downloadW",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + 500 + ',height=' + 300);
  theForm.setAttribute("target", "downloadW");
  theWin.focus();
  theForm.submit();
}
//==================================================================================================


</script>
<?php 
$html_str = "<META content='MSHTML 6.00.2900.3199' name=GENERATOR>\r\n</head>\r\n<basefont face='arial'>\r\n";
fwrite($html_handle, $html_str);
echo $html_str;
$html_str = "<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight=5 marginwidth=5>\r\n<center>\r\n";
fwrite($html_handle, $html_str);
echo $html_str;
?>
<FORM id="form_comparison" ACTION="<?php echo $PHP_SELF;?>" NAME="form_comparison" METHOD="POST">
  <INPUT TYPE="hidden" NAME="frm_selected_list_str" VALUE="<?php echo $frm_selected_list_str?>">
  <INPUT TYPE="hidden" NAME="totalHits" VALUE='<?php echo $totalHits;?>'>
  <INPUT TYPE="hidden" NAME="totalitems" VALUE='<?php echo $totalitems;?>'>
  <INPUT TYPE="hidden" NAME="passedTypeStr" VALUE='<?php echo $passedTypeStr;?>'>
  <INPUT TYPE="hidden" NAME="biggestPowedSore" VALUE="<?php echo $biggestPowedSore;?>">
  <INPUT TYPE="hidden" NAME="currentType" VALUE="<?php echo $currentType;?>">
  <INPUT TYPE="hidden" NAME="currentProperty" VALUE="<?php echo $orderby;?>">
  <INPUT TYPE="hidden" NAME="per_orderby" VALUE="<?php echo $orderby;?>">
  <INPUT TYPE="hidden" NAME="SearchEngine" VALUE="<?php echo $SearchEngine;?>">
  <INPUT TYPE="hidden" NAME="filterStyleDisplay" VALUE="<?php echo $filterStyleDisplay;?>">
  <INPUT TYPE="hidden" NAME="subfilterStyleDisplay" VALUE="<?php echo $subfilterStyleDisplay;?>">
  <INPUT TYPE="hidden" NAME="filtrColorIniFlag" VALUE="<?php echo $filtrColorIniFlag;?>">
  <INPUT TYPE="hidden" NAME="color_mode" VALUE="<?php echo $frm_color_mode;?>">
  <INPUT TYPE="hidden" NAME="applyFilters" VALUE="<?php echo $applyFilters;?>">
  <INPUT TYPE="hidden" NAME="typeStr" VALUE="<?php echo $typeStr;?>">
  <INPUT TYPE="hidden" NAME="theaction" VALUE="<?php echo $theaction?>">
  <INPUT TYPE="hidden" NAME="infileName" VALUE="">
  <INPUT TYPE="hidden" NAME="exportType" VALUE="">
  <INPUT TYPE="hidden" NAME="allBaitgeneID_str" VALUE="<?php echo $allBaitgeneID_str?>">
  <INPUT TYPE="hidden" NAME="lineNum" VALUE="">
  <INPUT TYPE="hidden" NAME="GeneID" VALUE="">
  <INPUT TYPE="hidden" NAME="GeneName" VALUE="">
  <input type='hidden' name='peptide_report_protein_id' value=''>
  <input type='hidden' name='protein_old_id' value=''>
  <input type='hidden' name='Is_geneLevel' value='<?php echo $Is_geneLevel?>'>
  <input type='hidden' name='maxScore_original' value='<?php echo $maxScore_original?>'>
  <input type='hidden' name='SID' value=''>
  <input type='hidden' name='ID_string' value=''>
  <input type='hidden' name='hitType' value='<?php echo $hitType?>'>
  <input type='hidden' name='ProjectID' value='<?php echo $AccessProjectID;?>'>
  <input type='hidden' name='item_type' value=''>
<?php 
$aa = '';
get_colorArrSets($powerColorIndex,$colorArrSet,$aa);
$item = $currentType.'ID';


if($theaction != "popWindow"){

$html_str = "<b><font face='Arial' size='+3'>$typeLable Comparison</font></b>\r\n";
fwrite($html_handle, $html_str);
echo $html_str;

$html_str = "<table align='center' bgcolor='' cellspacing='0' cellpadding='0' border='0' width=700>\r\n";
fwrite($html_handle, $html_str);
echo $html_str;

$html_str = "<tr>\r\n<td colspan='2' nowrap>\r\n<table align='center' bgcolor='' cellspacing='1' cellpadding='3' border='0' width=700>
      <tr bgcolor='#b7c1c8' height=28>
        <td width='15%' align='right' nowrap>
          <font size='2' face='Arial'><b>Color code</b></font>&nbsp;&nbsp;
        </td>
        <td >&nbsp;&nbsp;
          <font size='2'>
          Hit property color code <input type=radio name='frm_color_mode' value='property' ".(($frm_color_mode == 'property')?'checked':'')." onClick='change_color_code(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          Shared hits color code <input type=radio name='frm_color_mode' value='shared' ". (($frm_color_mode == 'shared')?'checked':'')." onClick='change_color_code(this);'>
          </font>
        </td>
      </tr>";
echo $html_str;
        
        if(strstr($SearchEngine, 'iProphet')){
          $ExpectLable = 'iProphet Probability';
        }elseif(strstr($SearchEngine, 'TPP_')){
          $ExpectLable = 'TPP Probability';
        }else{
          if($SearchEngine == 'GPM'){
            $Score_lable = ' Expect';
          }else{
            $Score_lable = ' Score';
          }
          $ExpectLable = $SearchEngine_lable_arr[$SearchEngine] . $Score_lable;
        }
        if($Is_geneLevel){
          $lableFormatArr['SpectralCount'] = 'Spectral Count';
          $lableFormatArr['Unique'] = 'Unique Group Peptide Number';
        }else{        
          $lableFormatArr[$Expect] = $ExpectLable;
          $lableFormatArr['Pep_num'] = 'Total Peptide Number';
          $lableFormatArr['Pep_num_uniqe'] = 'Unique Peptide Number';
          $lableFormatArr['Coverage'] = 'Coverage';
        }
        $lableFormatArr['Fequency'] = 'Project Frequency';
        
        $field_lable_key_str = '';
        $field_lable_val_str = '';
        foreach($lableFormatArr as $lab_key => $lab_val){
          if($field_lable_key_str) $field_lable_key_str .= ',';
          if($field_lable_val_str) $field_lable_val_str .= ',';
          $field_lable_key_str .= $lab_key;
          $field_lable_val_str .= $lab_val;
        }   
      ?>
      <INPUT TYPE="hidden" NAME="Expect" VALUE="<?php echo $Expect?>">
      <INPUT TYPE="hidden" NAME="ExpectLable" VALUE="<?php echo $ExpectLable?>">
      <INPUT TYPE="hidden" NAME="field_lable_key_str" VALUE="<?php echo $field_lable_key_str?>">
      <INPUT TYPE="hidden" NAME="field_lable_val_str" VALUE="<?php echo $field_lable_val_str?>">
      
<?php 
$order_by_lable = '';

if(array_key_exists($orderby, $lableFormatArr)) $order_by_lable = $lableFormatArr[$orderby];
if(!$order_by_lable){
  if(array_key_exists($orderby, $optionArr_for_user_d_frequency)) $order_by_lable = $optionArr_for_user_d_frequency[$orderby]." Frequency";
}
if(!$order_by_lable){
  if(in_array($orderby, $passedTypeArr)) $order_by_lable = $orderby." Frequency";
}

$item_list = '';
$select_item = '';
foreach($itemLableArr as $itemID => $lable){
  if($sort_by_item_id==$itemID) $select_item = ((strstr($itemID, 'C_'))?$lable:$itemID."&nbsp;&nbsp;".$lable);
  if($item_list) $item_list .= "<br>";
  $item_list .= ((strstr($itemID, 'C_'))?$lable:$itemID."&nbsp;&nbsp;".$lable);
}

$html_str = "<tr bgcolor='#b7c1c8'>
        <td nowrap align='right'>
    			<font size='2' face='Arial'><b>Sort by</b></font>&nbsp;&nbsp;
        </td>
        <td nowrap >&nbsp;&nbsp;"; 
fwrite($html_handle, $html_str);
echo $html_str;

fwrite($html_handle, $order_by_lable);
                     
$html_str = "<select name='orderby' size=1>
             <option value=''>&nbsp; &nbsp; &nbsp;";
echo $html_str;

if(!$Is_geneLevel){
  $html_str = " <option value='$Expect'" . (($orderby==$Expect)?'selected':'') .">$ExpectLable<br>";
  $html_str .= "<option value='Pep_num'" . (($orderby=='Pep_num')?'selected':'') .">Total Peptide Number<br>";
  $html_str .= "<option value='Pep_num_uniqe'" . (($orderby=='Pep_num_uniqe')?'selected':'') .">Unique Peptide Number<br>";
  $html_str .= "<option value='Coverage'" . (($orderby=='Coverage')?'selected':'') .">Coverage<br>";
  $html_str .= "<option value='Fequency'" . (($orderby=='Fequency')?'selected':'') .">Project Frequency<br>";
}else{
  $html_str = " <option value='SpectralCount'" . (($orderby=='SpectralCount')?'selected':'') .">SpectralCount<br>";
  $html_str .= "<option value='Unique'" . (($orderby=='Unique')?'selected':'') .">Unique Group Peptide Number<br>";
  $html_str .= "<option value='Fequency'" . (($orderby=='Fequency')?'selected':'') .">Project Frequency<br>";
}  


echo $html_str;
            foreach($optionArr_for_user_d_frequency as $option_value => $option_lable){
$html_str = " <option value='$option_value'". (($orderby==$option_value)?'selected':'') .">$option_lable Frequency";
//fwrite($html_handle, $html_str);
echo $html_str;
            }
            foreach($passedTypeArr as $passedTypeValue){
$html_str = " <option value='$passedTypeValue' ".(($orderby==$passedTypeValue)?'selected':'').">(G) $passedTypeValue Frequency<br>";
//fwrite($html_handle, $html_str);
echo $html_str;
            }
   
$html_str = "</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <font size='2' face='Arial'>$typeLable ID&nbsp;
          <select name='sort_by_item_id' size=1>
              <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;";
echo $html_str;
            foreach($itemLableArr as $itemID => $lable){
$html_str = " <option value='$itemID' ". (($sort_by_item_id==$itemID)?'selected':'') ." ". ((strstr($itemID, 'C_'))?'class=$itemID':'') . "> " .((strstr($itemID, 'C_'))?$lable:$itemID."&nbsp;&nbsp;".$lable) ."<br>";

echo $html_str;
            }
$html_str = "</select>&nbsp;&nbsp;&nbsp;&nbsp;";

echo $html_str;

$html_str = "Descending<input type=radio name=asc_desc value='DESC' ".((isset($asc_desc) && $asc_desc=='DESC')?'checked':'') . ">&nbsp;&nbsp;&nbsp;
             Ascending<input type=radio name=asc_desc value='ASC' ".((isset($asc_desc) && $asc_desc=='ASC')?'checked':'') . ">&nbsp;";
echo $html_str;



$tmp_html_str = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;
          <font size='2' face='Arial'><b>$typeLable ID&nbsp;</b></font>&nbsp;&nbsp;";
$item_typeLable = "$typeLable list";
$tmp_html_str .= "<a style='text-decoration:none' href='javascript: href_show_hand()' onmouseover=\"show_hit_detail(event,'item_list_div','$item_typeLable')\" onmouseout=\"hideTip('hit_detail_div')\">
          <DIV ID='item_list_div' STYLE='display: none'>$item_list</DIV>
          $select_item
          </a>";
fwrite($html_handle, $tmp_html_str);



             
$html_str = "</td>
      </tr>
    </table> 
    </td>
	</tr>";
fwrite($html_handle, $html_str);
echo $html_str;
?> 
  <tr>
		<td colspan='2' nowrap>
      <a id="filter_area_a" href="javascript: href_show_hand();" onclick="showhide('filter_area')"><font size="2" face="Arial">[&nbsp;<?php echo ($filterStyleDisplay=='none')?'Click to apply filters':'Click to remove filters'?>&nbsp;]</font></a></td>
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
 <?php  
    include("filter_interface.php");
 ?>  
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
	  <!--td align="right"><a href='./user_defined_frequency.php?theaction=display_frequency' class=button target="_parent">[ Update Frequency ]</a-->&nbsp;&nbsp;&nbsp;</td>	
	  <td align="right"><input type=button name=sort_submit value="     GO     " onclick="sort_page();">&nbsp;&nbsp;&nbsp;</td>		
  </tr>
	</table>
	</td>
	</tr>
<?php 
$html_str = "</table>";
fwrite($html_handle, $html_str);
echo $html_str;

    $qurryStr = "infileName=$reportFileName&exportType=graph&orderby=$orderby&power=$power&biggestPowedSore=$biggestPowedSore&powerColorIndex=$powerColorIndex&hitType=$SearchEngine&bio_checked_str=$bio_checked_str&level1_matched_file=$tmp_file";
?>
<INPUT TYPE="hidden" NAME="cyto_qurryStr" VALUE="<?php echo $qurryStr?>">
<table align="center" bgcolor="" cellspacing="0" cellpadding="3" border="0" width=750>
  <tr>
    <td align=right><font size=2>
  <?php if($theaction == "showNormal"){?>
    <a href="javascript: href_show_hand();" onclick="showTip(event,'cyto_confirm_div')">[<img src=./images/icon_cytoscape.gif border=0>Cytoscape]</a> &nbsp;
  <?php }?> 
    <DIV ID='cyto_confirm_div' STYLE="position: absolute; 
                          display: none;
                          border: black solid 1px;
                          width: 200px";>
      <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
        <tr bgcolor="#c1c184" height=25><td valign="bottem">&nbsp;&nbsp;&nbsp;<font color="white" face="helvetica,arial,futura" size="2"><b>Select node lable:</b></font></td></tr>
        <tr bgcolor="#e6e6cc"><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="node_lable" VALUE="short" checked>&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Gene name</font></td></tr>
        <tr bgcolor="#e6e6cc"><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="node_lable" VALUE="long">&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Gene name and Gene ID</font></td></tr>
        <tr bgcolor="#e6e6cc"><td align="center" height=35><input type=button name='cyto_confirm_div' VALUE=" Confirm " onclick="javascript: confirm_cyto_div();">&nbsp;&nbsp;
        <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: hideTip('cyto_confirm_div');">
        </td>
        </tr>
      </table>   
    </DIV> 
    [<a href="./comparison_results_export.php?infileName=<?php echo $reportFileName;?>&exportType=table&hitType=<?php echo $SearchEngine;?>&currentType=<?php echo $currentType;?>&theaction=<?php echo $theaction?>&Is_geneLevel=<?php echo $Is_geneLevel?>">Export (table)</a>] &nbsp;
    [<a href="javascript: href_show_hand();" onclick="showTip(event,'matrix_confirm_div')">Export (matrix)</a>] &nbsp;
    <DIV ID='matrix_confirm_div' STYLE="position: absolute; 
                          display: none;
                          border: black solid 1px;
                          width: 220px";>
      <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
        <tr bgcolor="#c1c184" height=25><td valign="bottem">&nbsp;&nbsp;&nbsp;<font color="white" face="helvetica,arial,futura" size="2"><b>Select hit property:</b></font></td></tr>
        <tr><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="report_style" VALUE="multiple" checked>&nbsp;<font color="black" face="helvetica,arial,futura" size="2">All hit properties</font></td></tr>
    <?php if(!array_key_exists($orderby, $typeInitIdArr) && array_key_exists($orderby, $lableFormatArr)){?>    
        <tr><td>&nbsp;&nbsp;&nbsp;<input type=radio NAME="report_style" VALUE="<?php echo $orderby?>">&nbsp;<font color="black" face="helvetica,arial,futura" size="2">Only <?php echo $lableFormatArr[$orderby]?></font></td></tr>
    <?php }?>    
        <tr height=35><td align="center"><input type=button name='matrix_confirm' VALUE=" Confirm " onclick="javascript: confirmdd_div();">&nbsp;&nbsp;
        <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: hideTip('matrix_confirm_div');">
        </td></tr>
      </table>   
    </DIV>    
    [<a href="javascript: export_all()";>Export (select)</a>] &nbsp;
    <?php if($theaction != "showImage"){?>
    [<a href="javascript: pop_sent_win('N')";>Send by email</a>] &nbsp;
    <?php }else{?>
    [<a href="javascript: pop_sent_win('Y')";>Send by email</a>] &nbsp;
    <?php }?>
    [<a href="javascript: href_show_hand();" onclick="download_raw()">Export (raw files)</a>] &nbsp;
    </td>
  </tr>
</table>
<?php 
}else{
  include("filter_biogrid.inc.php");
?>
<table align='center' bgcolor='' cellspacing='1' cellpadding='1' border='0' width=750>
  <tr>
  <td align="right">
  <a href="javascript: pop_sent_win('N')";><font face="helvetica,arial,futura" size="2">[Select mail type]</font></a> &nbsp;
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
  <!--a href="javascript: image_test();">[test]</a-->
  
</BODY>
</HTML> 
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>
<?php 
exit;
}else{  
  $peptideComparesonFile_handle = fopen($peptideComparesonFileName, "w");
$html_str = "
<DIV ID='hit_detail_div' STYLE='position: absolute; display: none; border: black solid 1px; width: 200px'>
  <table align='center' cellspacing='0' cellpadding='1' border='0' width='100%' bgcolor='#e6e6cc'>
    <tr bgcolor='#c1c184' height='20'>
      <td valign='bottem'>
        <font color='white' face='helvetica,arial,futura' size='2'><b><div ID='title_div'>Hit details</div></b></font>
      </td>
    </tr>
    <tr><td id='hit_detail_td'></td></tr>
  </table>   
</DIV>";   
fwrite($html_handle, $html_str."<br>&nbsp;&nbsp;");
echo $html_str;

$html_str = "\r\n<table align='center' bgcolor='' cellspacing='0' cellpadding='0' border='0' width=750>
  <tr>";
fwrite($html_handle, $html_str);
echo $html_str;
 
  $itemLableStr = '';
  $itmeLableBgcStr = '';
  $peptide_mapFile_lineCounter = 0;
  $lableDetailStr = '';
  for($j=$start_J_index; $j<$end_J_index; $j++){
    if(strstr($itemIdIndexArr[$j], 'C_')){
      $lableBgc = str_replace("C_", "", $itemIdIndexArr[$j]);
      $itemLable = $itemLableArr[$itemIdIndexArr[$j]];
      $lableDetail = $groupArr[$itemIdIndexArr[$j]]['itemInfo'];
      
      if($lableDetailStr) $lableDetailStr .= ',,';
      $lableDetailStr .= $lableDetail;
      $tmp_arr = explode(":</b><br>",$lableDetail,2);
      $lableDetail = $tmp_arr[1];
      $detail_div_id = "bt_".$j;
$html_str = "<td colspan='' class=s20 align=center bgcolor=$lableBgc rowspan='2'>";
fwrite($html_handle, $html_str);
echo $html_str;

$html_str = "<a style='text-decoration:none' href='javascript: href_show_hand();' onmouseover=\"show_hit_detail(event,'$detail_div_id','Bait details');\" onmouseout=\"hideTip('hit_detail_div');\">";
fwrite($html_handle, $html_str);
echo $html_str;
fwrite($html_handle, $itemLable);
?>
    <img src='./comparison_results_create_image.php?strMaxL=<?php echo $itemlableMaxL;?>&displayedStr=<?php echo $itemLable;?>&lableBgc=<?php echo $lableBgc;?>&fontSize=2' border=0></font>
<?php   
$html_str = "</a>
    <DIV ID='$detail_div_id' STYLE='display: none';>$lableDetail</DIV>";
echo $html_str;
fwrite($html_handle, $html_str);    
    }else{
      $itemLable = $itemIdIndexArr[$j].' '.$itemLableArr[$itemIdIndexArr[$j]];
      $lableBgc = '000000';
      if($lableDetailStr) $lableDetailStr .= ',,';
      $lableDetailStr .= $itemLable;
$html_str = "<td colspan='' class='s20' align=center bgcolor='$lableBgc' rowspan='2'>";
echo $html_str;
fwrite($html_handle, $html_str); 
fwrite($html_handle, "<font color='white'>".$itemLable."</font>");
  ?>
    <img src='./comparison_results_create_image.php?strMaxL=<?php echo $itemlableMaxL;?>&displayedStr=<?php echo $itemLable;?>&lableBgc=<?php echo $lableBgc;?>&fontSize=2' border=0></font>
  <?php }
$html_str = "</td>";
echo $html_str;
fwrite($html_handle, $html_str); 

		if($itemLableStr) $itemLableStr .= ',,';
		$itemLableStr .= $itemLable;
		if($itmeLableBgcStr) $itmeLableBgcStr .= ',,';
		$itmeLableBgcStr .= $lableBgc;
  }
	fwrite($peptideComparesonFile_handle, $itemlableMaxL.",,$currentType\r\n");
	fwrite($peptideComparesonFile_handle, $itemLableStr."\r\n");
	fwrite($peptideComparesonFile_handle, $itmeLableBgcStr."\r\n");
  fwrite($peptideComparesonFile_handle, $lableDetailStr."\r\n");
	$peptide_mapFile_lineCounter += 4;
  $TB_CELL_COLOR = '#ff7575';
  if($hasGeneID || $hasProteinID || $hasLocusTag){
$html_str = "<td bgcolor='#aeaeae' colspan='4' rowspan='1' align='center'><font size='3'><b>Hits</b></font></td>";
echo $html_str;
fwrite($html_handle, $html_str);
 }
 
$html_str = "</tr>
             <tr>";
echo $html_str;
fwrite($html_handle, $html_str);


 if($hasGeneID){
$html_str = "<td class=s19  align=center>Gene Name</td>";
echo $html_str;
fwrite($html_handle, $html_str);
 
 }
  if($hasProteinID){
$html_str = "<td class=s19  align=center>Protein ID</td>";
echo $html_str;
fwrite($html_handle, $html_str);
  }
  if($hasLocusTag){
$html_str = "<td class=s19  align=center>Links</td>";
echo $html_str;
fwrite($html_handle, $html_str);
  }?>
  <td class=s19  align=center>Peptide<br>Comparison</td>
  <?php 
$html_str = "</tr>";
echo $html_str;
fwrite($html_handle, $html_str);

  $sharedColorSet = array();
  if($frm_color_mode == 'shared'){
    create_colorArr_set($sharedColorSet,'green');
  }
$tmpC = 0;
  $TB_CELL_COLOR = '#ff7575';  
  if(count($hitsGeneIdIndexArr) < $end_I_index) $end_I_index = count($hitsGeneIdIndexArr); 

  for($i=$start_I_index; $i<$end_I_index; $i++){
    if(!array_key_exists($hitsGeneIdIndexArr[$i], $hitsNameArr)){
      continue;
    }else{
      $sharedHitNmb = $hitsNameArr[$hitsGeneIdIndexArr[$i]]['counter'];
    }
    if(isset($hitsNameArr[$hitsGeneIdIndexArr[$i]]['filter'])){
      $bioFilterArr = explode(",",$hitsNameArr[$hitsGeneIdIndexArr[$i]]['filter']);
    }else{
      $bioFilterArr = array();;
    }  
    $freqStr = '';
    $fequencySore = '&nbsp;';
    $subFequencySore = '&nbsp;';
    $userFequencySore = '&nbsp;';
    if(isset($FeqValueArr[$hitsGeneIdIndexArr[$i]])){
      $fequencySore = $FeqValueArr[$hitsGeneIdIndexArr[$i]];
      $freqStr = "<br>Project Frequency[$SearchEngine]: ".$fequencySore."%";
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
          $subFreqStr .= "<br>GroupFrequency[".(is_numeric($passedTypeArr[$fKey])?'V'.$passedTypeArr[$fKey]:$passedTypeArr[$fKey])."]: ".$fValue[$hitsGeneIdIndexArr[$i]]."%";
        }  
      }
    }
    
    $userFreqStr = '';  
    if(isset($userFeqsValueArr) && count($userFeqsValueArr)){
      $userFreqStr = '';
      foreach($userFeqsValueArr as $fKey => $fValue){      
        if(array_key_exists($hitsGeneIdIndexArr[$i], $fValue)){
          if($orderby == $fKey){
            if($fValue[$hitsGeneIdIndexArr[$i]]){
              $userFequencySore = $fValue[$hitsGeneIdIndexArr[$i]];
            }  
          }  
          $userFreqStr .= "<br>UserFrequency[".$optionArr_for_user_d_frequency[$fKey]."]: ".$fValue[$hitsGeneIdIndexArr[$i]]."%";
        }  
      }
    }
    
    $haredeFrequency = round($sharedHitNmb*100/$totalitems,1);
    $haredeFreqStr = "<br>Shared Hits Frequency: $sharedHitNmb/$totalitems=" . $haredeFrequency."%";
    
    if($frm_color_mode == 'shared'){
      $cellBgcolor = classify_filter($i);
      if(!$cellBgcolor) continue;
    }
    //---------------------------------------------------------------------------
    if(isset($hitsNameArr[$hitsGeneIdIndexArr[$i]]['isBait'])){ 
		  $B = $hitsNameArr[$hitsGeneIdIndexArr[$i]]['isBait'];
    }else{
      $B = 0;
    }
    //---------------------------------------------------------------------------
    if($theaction == "showNormal" && $applyFilters){    
  		if(!$A || !$B){
        $filter_flag = 0;
        foreach($typeBioArr as $Value) {
       		$frmName = 'frm_' . $Value['Alias'];        
      		if($$frmName and in_array($Value['Alias'] ,$bioFilterArr)){          
      			$filter_flag = 1;
            break;
      		}	
      	}
        if($filter_flag) continue;
      }
    }    
    
    $hitsArr_forLine = array();
    $filterLine = 1;
    for($j=$start_J_index; $j<$end_J_index; $j++){
      $temHitsArr = $itemNameArr[$itemIdIndexArr[$j]];
      if(array_key_exists($hitsGeneIdIndexArr[$i], $temHitsArr)){
        $hitsArr_forCell = array();
        $upStr = '';
        if(strstr($itemIdIndexArr[$j], 'C_')){
          $upStr .= $groupArr[$itemIdIndexArr[$j]]['itemInfo'].'<br>--------------<br>'; 
        }else{
          $tmp_geneName = '';
          if($currentType !== "Bait"){
            $tmpLable_arr = explode('_',$itemLableArr[$itemIdIndexArr[$j]]);
            if(count($tmpLable_arr) == 2){
              $tmp_geneName = $tmpLable_arr[1];
            }
          }
          
          $upStr .= '<font color=green><b>'.$singleTypeLable.'&nbsp;&nbsp;GeneName</b></font><br>'.$itemIdIndexArr[$j].'&nbsp;&nbsp;'.$itemLableArr[$itemIdIndexArr[$j]].'&nbsp;&nbsp;'.$tmp_geneName.'<br>--------------<br>';
        
        }      
        $tmpHitInfoArr_I = explode('##',$temHitsArr[$hitsGeneIdIndexArr[$i]]);
        $tmpHitInfoArr = array();       
        foreach($tmpHitInfoArr_I as $tmpHitInfoArr_I_value){
          $temArr = explode("=", $tmpHitInfoArr_I_value);
          $tmpHitInfoArr[$temArr[0]] = $temArr[1];
        }
        $filter_flag = 0;
        if($theaction == "showNormal" && $applyFilters){
  				$C = $tmpHitInfoArr['isBait'];
  				if(!$A || !$C){
          	if($B){
  	          foreach($typeBioArr as $Value){
  	         		$frmName = 'frm_' . $Value['Alias'];        
  	        		if($$frmName and in_array($Value['Alias'] ,$bioFilterArr)){          
  	        			$filter_flag = 1;
  	              break;
  	        		}	
  	        	}
  					}
  					if(!$filter_flag){
  						$expFilterArr = array();
  						get_exp_filter_arr($expFilterArr,$tmpHitInfoArr);
  						$exp_filter_flag = 0;
  						foreach($typeExpArr as $Value){
  		        	$frmName = 'frm_' . $Value['Alias'];        
  		       		if($$frmName and in_array($Value['Alias'] ,$expFilterArr)){          
  		       			$filter_flag = 1;
  		             break;
  		       		}	
  		       	}
  					}
  				}
        }
        $hitsArr_forCell[0] = $tmpHitInfoArr;
        $hitsArr_forCell[1] = $filter_flag;
        $hitsArr_forCell[2] = $upStr;
        $filterLine = $filterLine && $filter_flag;
        $hitsArr_forLine[$j] = $hitsArr_forCell;
      }else{
        $hitsArr_forLine[$j] = '';
      }
    }
    if($theaction == "showNormal" && $applyFilters){
      if($filterLine) continue;
    }
  $tmpC++;

$html_str = "<tr id='$i' bgcolor='#ececec' onmousedown=\"highlightTR(this, 'click', '#CCFFCC', '#ececec', '$frm_color_mode')\";>";
echo $html_str;
fwrite($html_handle, $html_str);
  
    $lineInfo = '';
    $counter = 0;
   	$tmpHitsStr = '';
    $tmpGiStr = '';
		$hasHitFlag = 0;
    $j_counter = 0;
    $tmpIndex = '';
    for($j=$start_J_index; $j<$end_J_index; $j++){
      $bioGrid_typeStr = ''; 
      if($hitsArr_forLine[$j]){
        $filter_flag = $hitsArr_forLine[$j][1];
		    $tmpHitInfoArr = $hitsArr_forLine[$j][0];
        $upStr = $hitsArr_forLine[$j][2];
        if(!$filter_flag){
          $hitSore = 0;
          $subFequencyValue = '';
          
          if($Is_geneLevel){
            if($orderby == 'SpectralCount'){
              $hitSore = $tmpHitInfoArr['SpectralCount'];
            }elseif($orderby == 'Unique'){
              $hitSore = $tmpHitInfoArr['Unique'];
            }elseif($orderby == 'Pep_num_uniqe'){
              $hitSore = $tmpHitInfoArr['Pep_num_uniqe'];
            }elseif($orderby == 'Subsumed'){
              $hitSore = $tmpHitInfoArr['Subsumed'];
            }
            $scoreLable = 'Spectral Count';
            
          }else{
            if($orderby == 'HitGI'){
              $hitSore = $tmpHitInfoArr['HitGI'];
            }elseif($orderby == $Expect){
              $hitSore = $tmpHitInfoArr['Expect'];
            }elseif($orderby == 'Pep_num'){
              $hitSore = $tmpHitInfoArr['Pep_num'];
            }elseif($orderby == 'Pep_num_uniqe'){
              $hitSore = $tmpHitInfoArr['Pep_num_uniqe'];
            }elseif($orderby == 'Coverage'){
              $hitSore = round($tmpHitInfoArr['Coverage'], 1);
            }
            if($SearchEngine == 'Mascot'){
              $scoreLable = 'Mascot Score';
            }elseif($SearchEngine == 'GPM'){
              $scoreLable = 'GPM Expect';
            }elseif($SearchEngine == 'SEQUEST'){
              $scoreLable = 'SEQUEST Score';
            }elseif(strstr($SearchEngine, 'TPP_')){
              $scoreLable = 'TPP Probability';
            }else{
              $scoreLable = 'Score';
            }
          }          
          if($orderby == 'Fequency'){
            $hitSore = $fequencySore;
          }elseif(strstr($orderby,"U:")){
            $hitSore = $userFequencySore;
          }elseif(in_array($orderby, $passedTypeArr)){
            $hitSore = $subFequencySore;
          }
          
          if(isset($tmpHitInfoArr['RedundantGI']) && $tmpHitInfoArr['RedundantGI']){
            $tmpHitInfoArr['RedundantGI'] = trim($tmpHitInfoArr['RedundantGI']);
            $tmpHitInfoArr['RedundantGI'] = preg_replace('/;+$/', '', $tmpHitInfoArr['RedundantGI']);
            $tmpHitInfoArr['RedundantGI'] = preg_replace('/^gi\|$/i', '', $tmpHitInfoArr['RedundantGI']);
            $tmpHitInfoArr['RedundantGI'] = preg_replace('/;?gi\|/i', '@@', $tmpHitInfoArr['RedundantGI']);
            //$tmpHitInfoArr['RedundantGI'] = preg_replace('/;/', '<br>'.str_repeat("&nbsp;", 18), $tmpHitInfoArr['RedundantGI']);
            $tmpHitInfoArr['RedundantGI'] = preg_replace('/@@/', '<br>'.str_repeat("&nbsp;", 18), $tmpHitInfoArr['RedundantGI']);
            $tmpHitInfoArr['RedundantGI'] = '<br>Redundant:&nbsp;&nbsp;'.$tmpHitInfoArr['RedundantGI'];
          }else{  
            $tmpHitInfoArr['RedundantGI'] = '';
          }
//echo $tmpHitInfoArr['HitGI']."^^^^^^^";
//echo $tmpHitInfoArr['RedundantGI']."*******<br>";
          $TPPextraInfo = '';
          if(strstr($SearchEngine, 'TPP_')){
            $TPPextraInfo = "<br>XPRESSRATIO MEAN: ".$tmpHitInfoArr['XPRESSRATIO_MEAN']."<br>XPRESSRATIO STANDARD DEV: ".$tmpHitInfoArr['XPRESSRATIO_STANDARD_DEV']."<br>XPRESSRATIO NUM PEPTIDES: ".$tmpHitInfoArr['XPRESSRATIO_NUM_PEPTIDES'];
          }
          if($Is_geneLevel){          
            if(isset($tmpHitInfoArr['Dup'])){
              $tmp_tmp_geneID = $tmpHitInfoArr['Dup'];
              //$cellBgcolor = $tmpHitInfoArr['B_color'];
            }else{
              $tmp_tmp_geneID = $tmpHitInfoArr['GeneID'];
            }
            $upStr .= "Gene ID: ".$tmp_tmp_geneID."<br>Spectral Count:&nbsp;".$tmpHitInfoArr['SpectralCount']."<br>Subsumed: ".$tmpHitInfoArr['Subsumed']."<br>Unique Group Peptide: ".$tmpHitInfoArr['Unique'].$TPPextraInfo.$freqStr.$userFreqStr.$subFreqStr.$haredeFreqStr;          
          }else{
            $upStr .= "Protein ID: ".$tmpHitInfoArr['HitGI'].$tmpHitInfoArr['RedundantGI']."<br>$scoreLable:&nbsp;".$tmpHitInfoArr['Expect']."<br>Total Peptide: ".$tmpHitInfoArr['Pep_num']."<br>Unique Peptide: ".$tmpHitInfoArr['Pep_num_uniqe']."<br>Protein Coverage: ".$tmpHitInfoArr['Coverage'].'%'.$TPPextraInfo.$freqStr.$userFreqStr.$subFreqStr.$haredeFreqStr;
          }
          if($frm_color_mode != 'shared'){
            if($orderby == "Expect2"){
              $hitSoreForColor = -1 * $hitSore;
            }else{
              $hitSoreForColor = $hitSore;
            }
            if(isset($tmpHitInfoArr['B_color'])){
              $cellBgcolor = $tmpHitInfoArr['B_color'];
            }else{
              $cellBgcolor = color_num($hitSoreForColor, $colorIndex);
            }
            if(isset($tmpHitInfoArr['RedundantGI']) && $tmpHitInfoArr['RedundantGI']){
              if($orderby==$Expect){
                $numOfClass = 's14_2';
                $font_color = '#eaea00';
              }else{
                $numOfClass = 's14_1';
                $font_color = '#ff9c6c';
              }  
            }else{
              ($colorIndex >= 7)?$numOfClass='s13':$numOfClass='s14';
              $font_color = 'white';
            }  
          }else{  
            $numOfClass = 's14';
            $font_color = 'white';
          }
          if($counter){
  				  $lineInfo .= ',';
  					$tmpHitsStr .= ',,';
            $tmpGiStr .= ',,';
  				}
          $tmpHitsStr .= $tmpHitInfoArr['ID'];
          if(isset($tmpHitInfoArr['HitGI'])){
           $tmpGiStr .= preg_replace("/SP\||\|/",'', strtoupper($tmpHitInfoArr['HitGI']));
          }  
  				if(!$hasHitFlag) $hasHitFlag = 1;
          $detail_div_id = "d_".$tmpHitInfoArr['ID'];
          
$html_str = "<td class='$numOfClass' align='center' bgcolor='$cellBgcolor'>
          <a style='text-decoration:none' href='javascript: href_show_hand()' onmouseover=\"show_hit_detail(event,'$detail_div_id','Hit details')\" onmouseout=\"hideTip('hit_detail_div')\">
          <DIV ID='$detail_div_id' STYLE='display: none'>$upStr</DIV>
          <font color='$font_color'>".(($hitSore=trim($hitSore))?$hitSore:"&nbsp;")."</font>
          </a>";
echo $html_str;
fwrite($html_handle, $html_str);
          
          print_bioGrid_icon($itemIdIndexArr[$j],$hitsGeneIdIndexArr[$i],'1');
		  if($bioGrid_typeStr) $bioGrid_typeStr = "[".$bioGrid_typeStr."]";
          
$html_str = "</td>"; 
echo $html_str;
fwrite($html_handle, $bioGrid_typeStr.$html_str);

          if(!$Is_geneLevel){
            $lineInfo .= str_replace(":", "+++", $tmpHitInfoArr['HitGI']).':'.$tmpHitInfoArr['Expect'].'('.$tmpHitInfoArr['Pep_num'].'-'.$tmpHitInfoArr['Pep_num_uniqe'].'-'.$tmpHitInfoArr['Coverage'].'-'.(($fequencySore=='&nbsp;')?'':$fequencySore).'-'.$haredeFrequency.')'.$bioGrid_typeStr;
          }else{
            $tmpHitInfoArr['Subsumed'] = str_replace("-", "", $tmpHitInfoArr['Subsumed']);
            $tmpHitInfoArr['Subsumed'] = str_replace(",", "|", $tmpHitInfoArr['Subsumed']);
          
            $lineInfo .= ':'.$tmpHitInfoArr['SpectralCount'].'('.$tmpHitInfoArr['Unique'].'-'.$tmpHitInfoArr['Subsumed'].'-'.(($fequencySore=='&nbsp;')?'':$fequencySore).'-'.$haredeFrequency.')'.$bioGrid_typeStr;
          }
          $counter++;
          $tmpIndex = $item_index_lable_arr[$itemIdIndexArr[$j]];
          $j_counter++;
        }else{
          if($counter){
  				  $lineInfo .= ',';
  					$tmpHitsStr .=  ',,';
            $tmpGiStr .=  ',,';
  				}
          $counter++;        
          if($bioGrid_typeStr) $bioGrid_typeStr = "[".$bioGrid_typeStr."]";
          
$html_str = "<td align=center class=s15>&nbsp;"; 
echo $html_str;
fwrite($html_handle, $html_str);          
          
$html_str = "&nbsp;</td>"; 
echo $html_str;
fwrite($html_handle, $bioGrid_typeStr.$html_str);          
          
          $lineInfo .= $bioGrid_typeStr;
        }
      }else{
        if($counter){
				  $lineInfo .= ',';
					$tmpHitsStr .=  ',,';
          $tmpGiStr .=  ',,';
				}
        $counter++;
        
$html_str = "<td align=center class=s15>&nbsp;"; 
echo $html_str;
fwrite($html_handle, $html_str);

           print_bioGrid_icon($itemIdIndexArr[$j],$hitsGeneIdIndexArr[$i],'0');
           if($bioGrid_typeStr) $bioGrid_typeStr = "[".$bioGrid_typeStr."]";

$html_str = "&nbsp;</td>"; 
echo $html_str;
fwrite($html_handle, $bioGrid_typeStr.$html_str);
        
        $lineInfo .= $bioGrid_typeStr;
      }
    }
    $hitsID_GI_str = $tmpHitsStr.':::'.$tmpGiStr;
		fwrite($peptideComparesonFile_handle, $hitsID_GI_str."\r\n");
		$peptide_mapFile_lineCounter++;
//---------------------------------------------------------------------------------------------------------------------------------
    if(isset($hitsNameArr[$hitsGeneIdIndexArr[$i]]['name'])){
      if($Is_geneLevel){
        list($tmpGeneName,$tempProteinID,$tmpLocusTag) = explode(',',$hitsNameArr[$hitsGeneIdIndexArr[$i]]['name']);
        $Protein_OLD_ID = '';
      }else{
        list($tmpGeneName,$tempProteinID,$tmpLocusTag,$Protein_OLD_ID) = explode(',',$hitsNameArr[$hitsGeneIdIndexArr[$i]]['name']);
      }
    }else{
      $tmpGeneName = '';
      $tempProteinID = '';
      $tmpLocusTag = '';
      $Protein_OLD_ID = '';
    }  
//---------------------------------------------------------------------------------------------------------------------------------    
    $tempHitGeneID = $hitsGeneIdIndexArr[$i];
    if(preg_match("/(\w+)_GI$/", $hitsGeneIdIndexArr[$i], $matches)){
      $tmpGeneName_2 = $tempHitGeneID_2 = $matches[1];
      $tempHitGeneID = '';
    }else{
      $tempHitGeneID_2 = $tempHitGeneID = $hitsGeneIdIndexArr[$i];
      $tmpGeneName_2 = $tmpGeneName;
    }
    if(!($GeneIdURL = get_URL_str('',$tempHitGeneID,'',$tmpGeneName,'comparison'))){
      $GeneIdURL = "&nbsp;";
      if($tempHitGeneID) $GeneIdURL = $tempHitGeneID;
    }
    if(!($ProteinIdURL = get_URL_str($tempProteinID,'','','','comparison'))){
      $ProteinIdURL = "&nbsp;";//<img src="images/mascot_cyan_ball_blue.gif" alt="" width="16" height="16" border="0">
      if($tempProteinID) $ProteinIdURL = $tempProteinID;
    }  
    if(!($orfURL = get_URL_str('','',$tmpLocusTag,'','comparison'))) $orfURL = "&nbsp;";
  
    if($hasGeneID && !strstr($tempHitGeneID,'_GI')){
      $tmp_GeneIdURL = str_replace("<br>", "&nbsp;&nbsp;&nbsp;", $GeneIdURL);
      
$html_str = "<td id='gene$i' class=s16 align=center>$tmp_GeneIdURL</td>"; 
echo $html_str;
fwrite($html_handle, $html_str);

    }else{
      if(strstr($tempHitGeneID,'_GI')){
        $tempHitGeneID = '';
        $tmpGeneName = '';
        $tempProteinID = preg_replace("/SP\||\|/",'', strtoupper($tempProteinID)); 
      }
      
$html_str = "<td id='gene$i' class=s16 align=center>&nbsp;&nbsp;</td>"; 
echo $html_str;
fwrite($html_handle, $html_str);
      
    }
    if($hasProteinID || $hasHitFlag){
			if($hasHitFlag){
			  $peptide_href = "<a href=\"javascript: pop_peptede_win('$peptide_mapFile_lineCounter','$tempHitGeneID','$tmpGeneName','$tempProteinID','$Protein_OLD_ID')\"  title='peptide comparison'><img src='images/icon_pep.gif' border='0'></a>";
      }else{
				$peptide_href = '&nbsp;';
			}
      
$html_str = "<td id='protein$i' align='center' class='s17' bgcolor=" . (($frm_color_mode == 'shared')?$cellBgcolor:'#d6d6d6') . " align='center'>
			$ProteinIdURL
			</td>"; 
echo $html_str;
fwrite($html_handle, $html_str); 

    }
    if($hasLocusTag){
    
$html_str = "<td id='link$i'class='s22' bgcolor='#d6d6d6' align='center' nowrap>$orfURL</td>"; 
echo $html_str;
fwrite($html_handle, $html_str);

    }?>
      <td id='shared<?php echo $i?>' align=center class=s17 bgcolor=<?php echo ($frm_color_mode == 'shared')?$cellBgcolor:'#d6d6d6'?> align=center>
			<?php echo $peptide_href;?>
			</td>
    <?php 
$html_str = "</tr>"; 
echo $html_str;
fwrite($html_handle, $html_str); 
 
  
    if(!$tempHitGeneID) $tempHitGeneID = $tempProteinID;
    $old = array(",","\n","\r");
    $new = array(";","","");
    $tempHitGeneID = str_replace($old, $new, $tempHitGeneID);
    $tmpGeneName = str_replace($old, $new, $tmpGeneName);
    $tmpLocusTag = str_replace($old, $new, $tmpLocusTag);
    $lineLable = $tempHitGeneID.','.$tmpGeneName.','.(($tmpLocusTag=='-')?'':$tmpLocusTag);
    if($frm_color_mode == 'shared'){
      $lineInfo = $lineLable.','.$lineInfo.'@'.$cellBgcolor."\r\n";
    }else{
      $lineInfo = $lineLable.','.$lineInfo."\r\n";
    }
    if($theaction != "popWindow") fwrite($reportFile_handle, $lineInfo);
    array_push($hitsGeneIDarr, $hitsGeneIdIndexArr[$i]);
  }  
  
  if($theaction != "popWindow" && $bio_checked_arr && $applyFilters && !$no_grid_data){
    $no_matched_gene_array = array_diff($grid_hits_gene_arr, $hitsGeneIDarr);
    if($no_matched_gene_array){
      $no_matched_gene_IDstr = implode(",", $no_matched_gene_array);    
      $SQL = "SELECT `EntrezGeneID`,
              `LocusTag`,
              `GeneName` 
              FROM `Protein_Class` 
              WHERE `EntrezGeneID` IN ($no_matched_gene_IDstr) 
              ORDER BY `GeneName`";
      $tmp_Protein_Class_arr = $PROTEINDB->fetchAll($SQL);
      $noMatchedHitGeneArr = array();
      if($tmp_Protein_Class_arr){
        $lineInfo = "bioGrid_only:\r\n";
        if($theaction != "popWindow") fwrite($reportFile_handle, $lineInfo);
      }
      foreach($tmp_Protein_Class_arr as $no_matched_gene_info){
        $counter = 0;
        $lineInfo = '';
        $noMatchedHitGeneArr[$no_matched_gene_info['GeneName']] = $no_matched_gene_info['EntrezGeneID'];
        $i++;
$html_str = "<tr id='$i' bgcolor='#ececec' onmousedown=\"highlightTR(this, 'click', '#CCFFCC', '#ececec', '$frm_color_mode')\";>"; 
echo $html_str;
fwrite($html_handle, $html_str);

        $j_counter = 0;
        $tmpIndex2 = '';
        for($j=$start_J_index; $j<$end_J_index; $j++){
          if($counter){
  				  $lineInfo .= ',';
  				}
          $counter++;
          
$html_str = "<td align=center class=s15>&nbsp;"; 
echo $html_str;
fwrite($html_handle, $html_str);

           print_bioGrid_icon_noMatch($itemIdIndexArr[$j],$no_matched_gene_info,$j_counter,$tmpIndex2);
		   if($bioGrid_typeStr) $bioGrid_typeStr = "[".$bioGrid_typeStr."]";

$html_str = "&nbsp;</td>"; 
echo $html_str;
fwrite($html_handle, $bioGrid_typeStr.$html_str);

          $lineInfo .= $bioGrid_typeStr;
        }
        $tempHitGeneID = $no_matched_gene_info['EntrezGeneID'];
        $tmpGeneName = $no_matched_gene_info['GeneName'];
        $tmpLocusTag = $no_matched_gene_info['LocusTag'];
        if(!($GeneIdURL = get_URL_str('',$tempHitGeneID,'',$tmpGeneName,'comparison'))){
          $GeneIdURL = "&nbsp;";
          if($tempHitGeneID) $GeneIdURL = $tempHitGeneID;
        }       
        if(!($orfURL = get_URL_str('','',$tmpLocusTag,'','comparison'))) $orfURL = "&nbsp;";
      
        if($hasGeneID){
$html_str = "<td id='gene$i' class='s16' align='center'>".str_replace("<br>", "&nbsp;&nbsp;&nbsp;", $GeneIdURL)."</td>"; 
echo $html_str;
fwrite($html_handle, $html_str);
        }
        
        if($hasProteinID || $hasHitFlag){
$html_str = "<td id='protein$i' align='center' class='s16' bgcolor='#d6d6d6'>&nbsp;</td>"; 
echo $html_str;
fwrite($html_handle, $html_str);
        }
        
        if($hasLocusTag){
$html_str = "<td id='link$i' class='s22' bgcolor='#d6d6d6' align='center' nowrap>$orfURL></td>"; 
echo $html_str;
fwrite($html_handle, $html_str);          
        }
        ?>
          <td id='shared<?php echo $i?>' align=center class=s17 bgcolor='#d6d6d6' align=center>
    			&nbsp;
    			</td>
        <?php 
$html_str = "</tr>"; 
echo $html_str;
fwrite($html_handle, $html_str);
     
        $lineLable = $tempHitGeneID.','.$tmpGeneName.','.(($tmpLocusTag=='-')?'':$tmpLocusTag);
        if($frm_color_mode == 'shared'){
          $lineInfo = $lineLable.','.$lineInfo.'@'.$cellBgcolor."\r\n";
        }else{
          $lineInfo = $lineLable.','.$lineInfo."\r\n";
        }
        if($theaction != "popWindow") fwrite($reportFile_handle, $lineInfo);
      }
    }
  }
  
  fwrite($matchGred_handle, "bait_info\r\n");
  if(isset($item_geneName_id_arr)){
    foreach($item_geneName_id_arr as $key => $value){
      fwrite($matchGred_handle, $key.",".$value."\r\n");
    }
  }
  
$html_str = "</table>"; 
echo $html_str;
fwrite($html_handle, $html_str);
  ?>
  </FORM>
  <?php 
}
if(!$Is_geneLevel){
$html_str = "<font size='-1' color='black'>
Note that a red number indicates that all peptides assigned to this entry are shared with at least one additional entry in the database.  Mouse over the number for details.
</font>
";
echo $html_str;
fwrite($html_handle, $html_str);
}else{
$html_str = "<font size='-1' color='black'>
Note that the same color indicates that all genes are in the same groug.
</font>
";
echo $html_str;
fwrite($html_handle, $html_str);

}
$html_str = "</BODY>
</HTML>"; 
echo $html_str;

?>
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>
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
      <table align="" bgcolor='' cellspacing="0" cellpadding="0" border="0" width=78%>
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

function get_total_hits($frm_selected_item_str){
	global $HITSDB, $currentType, $SearchEngine;
  global $Is_geneLevel;
  if($currentType == 'Exp'){
    $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($frm_selected_item_str)";
    $tmp_arr = $HITSDB->fetchAll($SQL);
    if(!$tmp_arr) return 0;
    $frm_selected_item_str = '';
    foreach($tmp_arr as $tmp_val){
      if($frm_selected_item_str) $frm_selected_item_str .= ',';
      $frm_selected_item_str .= $tmp_val['ID'];
      $tmp_currentType = 'Band';
    }
  }else{
     $tmp_currentType = $currentType;
  }
  if(strstr($SearchEngine, 'TPP_')){
    $HitsTable = 'TppProtein';
    $ProteinAcc = 'ProteinAcc';
  }elseif($Is_geneLevel){
    $HitsTable = 'Hits_GeneLevel';
    $ProteinAcc = '';
  }else{
    $HitsTable = 'Hits';
    $ProteinAcc = 'HitGI';
  } 
  
	$itemID = $tmp_currentType.'ID';
  
  if(strstr($SearchEngine, 'Mascot')){      
    $subWHERE = " WHERE (SearchEngine='Mascot' OR SearchEngine='MascotUploaded') ";
  }elseif(strstr($SearchEngine, 'COMET')){      
    $subWHERE = " WHERE (SearchEngine='COMET' OR SearchEngine='COMETUploaded') ";  
  }elseif(strstr($SearchEngine, 'iProphet')){      
    $subWHERE = " WHERE (SearchEngine='iProphet' OR SearchEngine='iProphetUploaded') ";
  }elseif(strstr($SearchEngine, 'GPM')){
    $subWHERE = " WHERE (SearchEngine='GPM' OR SearchEngine='GPMUploaded') ";
  }elseif(strstr($SearchEngine, 'SEQUEST')){
    $subWHERE = " WHERE (SearchEngine='SEQUEST' OR SearchEngine='SEQUESTUploaded') ";  
  }else{
    $subWHERE = " WHERE SearchEngine!='Mascot' AND SearchEngine!='MascotUploaded' AND SearchEngine!='COMET' AND SearchEngine!='COMETUploaded' AND SearchEngine!='iProphet' AND SearchEngine!='iProphetUploaded' AND SearchEngine!='GPM' AND SearchEngine!='GPMUploaded' AND SearchEngine!='SEQUEST' AND SearchEngine!='SEQUESTUploaded' ";
  }
  if($subFilteStr = subWhere()){
    $subWHERE .= $subFilteStr; 
  }         
	$SQL = "SELECT GeneID 
          FROM $HitsTable ";
  $WHERE = $subWHERE . " AND (GeneID!=0 AND GeneID IS NOT NULL AND GeneID !='') 
          AND $itemID IN($frm_selected_item_str) 
          GROUP BY GeneID";
	$SQL .= $WHERE;
  
  $hitsArrTmp = $HITSDB->fetchAll($SQL);

	$subTotalHits = count($hitsArrTmp);
  if($HitsTable == 'Hits_GeneLevel'){
    $subTotalHits2 = 0;
  }else{
  	$SQL = "SELECT $ProteinAcc 
            FROM $HitsTable ";
    $WHERE = $subWHERE . " AND (GeneID=0 OR GeneID IS NULL OR GeneID ='') 
            AND $itemID IN($frm_selected_item_str) 
            GROUP BY $ProteinAcc";
  	$SQL .= $WHERE;
    $hitsArrTmp2 = $HITSDB->fetchAll($SQL);
  	$subTotalHits2 = count($hitsArrTmp2);
  }
	$totalHits = $subTotalHits + $subTotalHits2;
	return $totalHits;
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
  global $itemIdIndexArr,$groupArr,$itemNoName;
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
  global $HITSDB,$AccessProjectID,$selected_item_list_str,$currentType,$group_table_name;
  $selected_item_list_arr = explode(",",$selected_item_list_str);
  $itemID_counter = count($selected_item_list_arr);
  
  if($currentType == 'Exp'){
    $tmp_item_type = 'Experiment';
  }else{
    $tmp_item_type = $currentType;
  }
  $SQL = "SELECT `ID`,`Initial` FROM `NoteType` WHERE `ProjectID`=$AccessProjectID AND type='$tmp_item_type'";
  $noteTypeArr = $HITSDB->fetchAll($SQL);  
  foreach($noteTypeArr as $noteTypeValue){
    $typeInitIdArr[$noteTypeValue['Initial']] = $noteTypeValue['ID'];// [HP]=>5
  }
  
  $groupID_itemID_arr = array();  
  if($selected_item_list_str){
    $SQL = "SELECT G.RecordID,N.ID,N.Initial 
            FROM $group_table_name G 
            LEFT JOIN NoteType N
            ON(N.ID=G.NoteTypeID)
            WHERE G.Note NOT LIKE 'SAM_%' AND G.RecordID IN($selected_item_list_str)";        
    if($tmp_sql_arr = $HITSDB->fetchAll($SQL)){
      foreach($tmp_sql_arr as $tmp_sql_val){
        if(!array_key_exists($tmp_sql_val['ID'], $groupID_itemID_arr)){
          $groupID_itemID_arr[$tmp_sql_val['ID']] = array();
        }
        array_push($groupID_itemID_arr[$tmp_sql_val['ID']], $tmp_sql_val);
      }
    }
  }
  foreach($tmp_sql_arr as $key => $val){
    if(!$val['ID']) continue;
    if(!array_key_exists($val['ID'], $passedTypeArr)){
      if($passedTypeStr) $passedTypeStr .= '#';
      $passedTypeStr .= $val['ID'].','.$val['Initial'];
      $passedTypeArr[$val['ID']] = $val['Initial'];
    }
  }
}

function print_bioGrid_icon($itemIdIndex,$hitsGeneIdIndex,$matched){
  global $grid_bait_hits_arr,$bio_checked_arr,$applyFilters,$allBaitgeneIDarr,$matchGred_handle;
  global $item_ID_name_map_arr,$hitsNameArr,$matchedHitGeneIDarr,$j_counter,$bioGrid_typeStr;
  $gridImage = '';
  $bioGrid_typeStr = '';
  if($bio_checked_arr && $applyFilters){
    if(is_numeric($itemIdIndex)){
      $gridIndex = $allBaitgeneIDarr[$itemIdIndex];
    }else{
      $gridIndex = $itemIdIndex;
    }
    $gridHitsArr = array();
    if(array_key_exists($gridIndex, $grid_bait_hits_arr)){
      $gridHitsArr = $grid_bait_hits_arr[$gridIndex];
    }
    if(array_key_exists($hitsGeneIdIndex, $gridHitsArr)){
      $gridImage = get_bioGrid_icon($gridHitsArr[$hitsGeneIdIndex],$bioGrid_typeStr,'s');
      $tmpArr = explode(",",$hitsNameArr[$hitsGeneIdIndex]['name']);
      $geneName = $tmpArr[0];
      $matchedLine = $matched.",".str_replace(",", ";",$item_ID_name_map_arr[$itemIdIndex])."??".str_replace(",", ";",$geneName).",".$bioGrid_typeStr.",".$hitsGeneIdIndex."\r\n";      
      if($matchGred_handle) fwrite($matchGred_handle,$matchedLine);
      if($matched){
        if(!in_array($hitsGeneIdIndex, $matchedHitGeneIDarr)){
          $matchedHitGeneIDarr[$geneName] = $hitsGeneIdIndex;
        }
      } 
      echo $gridImage;
    }
  }
  if(!$matched && $gridImage) $j_counter++;
  if($bioGrid_typeStr) $bioGrid_typeStr = str_replace(":", ";", $bioGrid_typeStr);
}

function print_bioGrid_icon_noMatch($itemIdIndex,$no_matched_gene_info,&$j_counter,&$tmpIndex2){
  global $bio_checked_arr, $allBaitgeneIDarr, $grid_bait_hits_arr,$matchGred_handle,$no_matched_gene_info;
  global $item_ID_name_map_arr,$bioGrid_typeStr;    
  $gridImage = '';
  $bioGrid_typeStr = '';
  if($bio_checked_arr){
    if(is_numeric($itemIdIndex)){
      $gridIndex = $allBaitgeneIDarr[$itemIdIndex];
    }else{
      $gridIndex = $itemIdIndex;
    }
    $gridHitsArr = $grid_bait_hits_arr[$gridIndex];
    if(array_key_exists($no_matched_gene_info['EntrezGeneID'], $gridHitsArr)){
      $gridImage = get_bioGrid_icon($gridHitsArr[$no_matched_gene_info['EntrezGeneID']],$bioGrid_typeStr,'s');
      $geneName = $no_matched_gene_info['GeneName'];
      $matchedLine = "0,".str_replace(",", ";",$item_ID_name_map_arr[$itemIdIndex])."??".str_replace(",", ";",$geneName).",".$bioGrid_typeStr.",".$no_matched_gene_info['EntrezGeneID']."\r\n";      
      if($matchGred_handle) fwrite($matchGred_handle,$matchedLine);
      echo $gridImage;
      $j_counter++;
      $tmpIndex2 = $itemIdIndex;
    }
  }
  if($bioGrid_typeStr) $bioGrid_typeStr = str_replace(":", ";", $bioGrid_typeStr);
}

/*function upload_htmlReport_to_public($fileName){
  $Use_name = $_SESSION['USER']->Username;
  $Project_ID = $_SESSION['AUTH']->ProjPageID;
  $session_id = session_id();
  $random_chars = random_chars($chars = 2);
  $timestamp = time();
  $real_file_name = $Use_name."_".$Project_ID."_".$session_id."_".$random_chars."_".$timestamp.".html";
  $http_public_cgi_dir = "http://prohits-web.lunenfeld.ca";
  $public_url = $http_public_cgi_dir . "/GIPR/upload_prohits_reports.php";
  $uploaded_location = $http_public_cgi_dir . "/prohits_report/$real_file_name";  

  $req = new HTTP_Request($public_url, array('timeout' => 18000,'readTimeout' => array(18000,0)));
  $req->setMethod(HTTP_REQUEST_METHOD_POST);
  $req->addHeader('Content-Type', 'multipart/form-data');
  
  $inputName = "uploaded_file";
  
  $req->addPostData('real_file_name', $real_file_name);
  $req->addFile($inputName, $fileName, $contentType = 'application/octet-stream');
  
  $result = $req->sendRequest();
  if(!PEAR::isError($result)) {
    $response1 = $req->getResponseBody();
    if($response1 !== false) {
      echo "\n======response from $public_url========\n";
      echo $response1 . "#######\n";
      echo "\n======end of url open========\n";
      
      $from = $_SESSION['USER']->Email;
      $to      = 'jp.jianpzhang@gmail.com';
      $subject = 'Comparison report';
      $message = $uploaded_location;
      $headers = "From: $from" . "\r\n" .
          "Reply-To: $from" . "\r\n" .
          "X-Mailer: PHP/" . phpversion();
      
      mail($to, $subject, $message, $headers);   
    }
  }else{ 
   	fatalError($result->getMessage());
  }
  return true;
}

function random_chars($chars = 2) {
  $letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
  return substr(str_shuffle($letters), 0, $chars);
}*/
      
?>