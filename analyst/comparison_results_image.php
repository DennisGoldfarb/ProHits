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
error_reporting(E_ALL );
set_time_limit(3600*24);  // it will execute for 24 hours

$theaction = 'showImage';
$overallWidth =800;
$overallHeight =700;

$grid_hits_gene_arr = array();

$powerArr['Expect'] = 1/2;
$powerArr['Pep_num'] = 1/2;
$powerArr['Pep_num_uniqe'] = 1;
$powerArr['Coverage'] = 1;     
$powerArr['Fequency'] = 1;
$itemlableMaxL = 0;
$php_file_name = "comparison_results_image.php";

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");

ini_set("memory_limit","400M");

$theation = "showImage";
$PROTEINDB = new mysqlDB(PROHITS_PROTEINS_DB);
$bio_checked_arr = $_SESSION["bio_checked_arr"];
//-------------------------------------------------------------------------------------------------------------
$SearchEngineConfig_arr = get_project_SearchEngine();
$SearchEngine_lable_arr = get_SearchEngine_lable_arr($SearchEngineConfig_arr);
//-------------------------------------------------------------------------------------------------------------
$typeBioArr = array();
$typeExpArr = array();
$typeExpArr_tmp = array();
$typeFrequencyArr = array();
create_filter_status_arrs($typeBioArr,$typeExpArr_tmp,$typeFrequencyArr,'comparison');
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
$A = isset($frm_BT) && !$frm_BT;

if(!_is_dir("../TMP/comparison/")) _mkdir_path("../TMP/comparison/");
if(!_is_dir("../TMP/comparison/P_$AccessProjectID/")) _mkdir_path("../TMP/comparison/P_$AccessProjectID/");
$subDir = "../TMP/comparison/P_$AccessProjectID/";
$argumentsFileName = $subDir.$AccessUserID."_arguments.txt";
$selectedListStrFileName = $subDir.$AccessUserID."_selected_list_.txt";
$hitsIndexFileName = $subDir.$AccessUserID."_hits_index.txt";
$hitsNameFileName = $subDir.$AccessUserID."_hits_name.txt";
$reportFileName = $subDir.$AccessUserID."_report.txt";

$reportFile_handle = fopen($reportFileName, "a");
if(!$reportFile_handle){
  echo "Cannot open file $reportFileName";
}
//1. get arguments-----------------------------------------------------------------------------------------------------------------
$argumentsFile_handle = @fopen($argumentsFileName, "r");
if($argumentsFile_handle){
  while(!feof($argumentsFile_handle)) {
    $buffer = fgets($argumentsFile_handle);
    $buffer = trim($buffer);
    $tmpArgumentsArr = explode('@@',$buffer);
  }
  fclose($argumentsFile_handle);  
}else{
  echo "cannot open file $argumentsFileName";
  exit;
}
foreach($tmpArgumentsArr as $tmpValu){
  list($key,$value) = explode('=',$tmpValu);
  $$key=$value;
  //echo $key."=".$value."<br>";
}
//2.create $groupArr, $frm_selected_item_str, $frm_selected_group_str and $no_groupped_str. Get ------------------------------------
$selectedListStr_handle = @fopen($selectedListStrFileName, "r");
if($selectedListStr_handle){  
  $buffer = fgets($selectedListStr_handle);
  $frm_selected_list_str = trim($buffer);
  fclose($selectedListStr_handle);
}else{
  echo "cannot open file $argumentsFileName";
  exit;
}

$groupArr = array();
$frm_selected_item_str = '';
$frm_selected_group_str = '';
$no_groupped_str = '';

if($frm_selected_list_str){
  create_groupArr_otherStrs($groupArr,$frm_selected_item_str,$frm_selected_group_str,$no_groupped_str);
  $itemIdIndexArr = explode(',', $frm_selected_group_str);
  $tmpRealItemArr = explode(',',$frm_selected_item_str);
  $tmpRealItemCounter = count($tmpRealItemArr);
  $allBaitgeneIDarr = get_all_item_geneID_arr($frm_selected_list_str);
  $NSfilteIDarr = array();
  if(isset($frm_NS) && $frm_NS && $frm_NS_group_id){
    get_NS_geneID($NSfilteIDarr,$frm_NS_group_id);
  }
}else{
  echo "no input elements";
  exit;
}
$itemLableArr = array();
create_item_lable_arr($itemLableArr,$itemlableMaxL);

//3. get tag type retated arrays------------------------------------------------------------------------------------
$passedTypeArr = $_SESSION["passedTypeArr"];
$typeInitIdArr = $_SESSION["typeInitIdArr"];
//4. create $FeqIndexArr $FeqValueArr-------------------------------------------------------------------------------
$FeqIndexArr = array();
$FeqValueArr = array();$FeqFiltedGeneIdArr = array();
$fequMaxScore = get_fequency($FeqIndexArr, $FeqValueArr,$FeqFiltedGeneIdArr,$frequencyFileName); //-create a fequency sorted index and values arrays.

//5. if tag exist create $subFeqIndexArr and $subFeqValueArr-------------------------------------------------------
$subFeqIndexArr = array();
$subFeqValueArr = array();

if(count($passedTypeArr)){
  get_subFequency($subFeqIndexArr, $subFeqsValueArr,$FeqFiltedGeneIdArr,$passedTypeArr, $typeNum);
}

//6. create $itemNameArr (hits tree) for every item and $hitsGeneIdIndexArr and hits property array $hitsNameArr-------
$firstHitsArr = array();
$contrlArr = array();
$hitsGeneIdIndexArr = array();
$hitsNameArr = array();
$itemNameArr = array();
$hitsGeneIdIndexArr2 = array();

for($j=0; $j<count($itemIdIndexArr); $j++){
	create_itemTree_hitsIndex_hitsPropty_Arrs($j,$firstHitsArr,$contrlArr,$hitsGeneIdIndexArr,$hitsGeneIdIndexArr2,$hitsNameArr,$itemNameArr);	
}

/*echo "<pre>";
print_r($itemNameArr);
echo "</pre>";
exit;*/

$totalitems = count($itemIdIndexArr);
if(($orderby == 'Expect2' && $asc_desc == 'DESC') || ($orderby != 'Expect2' && $asc_desc == 'ASC')){
  $firstHitsArr = array_reverse($firstHitsArr);
  $hitsGeneIdIndexArr = $hitsGeneIdIndexArr2;
  unset($hitsGeneIdIndexArr2);
}
if(count($firstHitsArr)){
  $tmpDiff = array_diff($hitsGeneIdIndexArr, $firstHitsArr);
  $hitsGeneIdIndexArr = array_merge($firstHitsArr, $tmpDiff);
}
if(count($contrlArr)){
  foreach($contrlArr as $contrlValue){
    $hitsNameArr[$contrlValue]['ctr'] = 1;
  }
}

//7. if display order by Fequency or order by subFequency modify $hitsGeneIdIndexArr-------------------------------------
if($orderby == 'Fequency'){
	$hitsGeneIdIndexArr = get_hitsGeneIdIndexArr_for_fequency($hitsGeneIdIndexArr,$FeqIndexArr);
}elseif(in_array($orderby, $passedTypeArr)){
	$hitsGeneIdIndexArr = get_hitsGeneIdIndexArr_for_subFequency($hitsGeneIdIndexArr,$subFeqIndexArr);
}

//--------------------------------------------------------------
if($applyFilters){
  $NSfilteIDarr = array_diff($NSfilteIDarr, $allBaitgeneIDarr);
  $tmpHitsGeneIdIndexArr = array_diff($hitsGeneIdIndexArr, $NSfilteIDarr);
  $hitsGeneIdIndexArr = array();
  foreach($tmpHitsGeneIdIndexArr as $tmpIndexValue){
    array_push($hitsGeneIdIndexArr, $tmpIndexValue);
  }
}      
//----------------------------------------------------------------

$totalHits = count($hitsGeneIdIndexArr);
//8.if $frm_color_mode == 'shared' filter $hitsGeneIdIndexArr amd $hitsNameArr--------------------------------------------

if($frm_color_mode == 'shared'){
//shared_color_filter($hitsGeneIdIndexArr,$hitsNameArr);
//----------------------------------------------------------
  if(!$applyFilters){
    $frm_red = 'y';
    $frm_green = 'y';
    $frm_blue = 'y';
  }
  $tmphitsGeneIdIndexArr = array();
  $tmphitsNameArr = array();
  foreach($hitsGeneIdIndexArr as $hitGeneId){
    $bgColor = '';
    if(in_array($contrlColor, $itemIdIndexArr)){
      if($hitsNameArr[$hitGeneId]['ctr'] && $hitsNameArr[$hitGeneId]['counter'] > 1){
        if(!$frm_red) continue;
        $bgColor = 'red';
      }else{
        if(!$frm_blue) continue;
        $bgColor = 'blue';
      }
    }else{
      if($hitsNameArr[$hitGeneId]['counter'] == $totalitems){
        if(!$frm_red) continue;
        $bgColor = 'red';
      }elseif($hitsNameArr[$hitGeneId]['counter'] == 1){
        if(!$frm_blue) continue;
        $bgColor = 'blue';
      }else{
        if(!$frm_green) continue;
        $bgColor = 'green';
      }
    }
    //-----------------------------------------------------------
		$B = $hitsNameArr[$hitGeneId]['isBait'];
    if($applyFilters){
      //if(!isset($frm_BT) || isset($frm_BT) && $frm_BT || !$hitsNameArr[$hitGeneId]['isBait']){
  		if(!$A || !$B){
        $bioFilterArr = explode(",",$hitsNameArr[$hitGeneId]['filter']);
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
    //-------------------------------------------------------------
    array_push($tmphitsGeneIdIndexArr, $hitGeneId);
    $tmphitsNameArr[$hitGeneId] = $hitsNameArr[$hitGeneId];
    $tmphitsNameArr[$hitGeneId]['color'] = $bgColor;
  }
  $hitsGeneIdIndexArr = $tmphitsGeneIdIndexArr;
  $hitsNameArr = $tmphitsNameArr;
  unset($tmphitsGeneIdIndexArr);
  unset($tmphitsNameArr);
  
}else{
  //bio_filter($hitsGeneIdIndexArr,$hitsNameArr);
//---------------------------------------------------------------
  $tmphitsGeneIdIndexArr = array();
  $tmphitsNameArr = array();
  foreach($hitsGeneIdIndexArr as $hitGeneId){
		$B = $hitsNameArr[$hitGeneId]['isBait'];
    if($applyFilters){
  		if(!$A || !$B){
        $bioFilterArr = explode(",",$hitsNameArr[$hitGeneId]['filter']);
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
    array_push($tmphitsGeneIdIndexArr, $hitGeneId);
    $tmphitsNameArr[$hitGeneId] = $hitsNameArr[$hitGeneId];
  }
  $hitsGeneIdIndexArr = $tmphitsGeneIdIndexArr;
  $hitsNameArr = $tmphitsNameArr;
  unset($tmphitsGeneIdIndexArr);
  unset($tmphitsNameArr);
}

//format_image();
$Height_cut_counter = 0;

if($biggestPowedSore <= 0) $biggestPowedSore = 1;
$totalHits = count($hitsGeneIdIndexArr);

$overallHeight = $cellH * $totalHits + $labalH;
if($overallHeight == 0) $overallHeight = 1;
//======================================================================================================================
//9.***draw image**********

$im = imagecreatetruecolor($overallWidth,$overallHeight) or die("Cannot Initialize new GD image stream");

$white = ImageColorAllocate($im,255,255,255);
$black = ImageColorAllocate($im,0,0,0); 
$bglight = ImageColorAllocate($im,234,234,234);

$bgColorRed = switch_color_format($im,$red);
$bgColorBlue = switch_color_format($im,$blue);
$bgColorGreen = switch_color_format($im,$green);

get_colorArrSets($powerColorIndex, $colorArrSets, $im);
$colorArrLen = count($colorArrSets);

imagefilledrectangle($im, 0, 0, $overallWidth, $overallHeight + $labalH, $bglight);

$x1 = 0;
$y1 = 0;
$c = 0;
if($labalH){
  $displayFont = $white;
  $x1 = 0;
  foreach($itemNameArr as $itemNameKey => $itemNameValue){
  $c++;
    $displayFont = $white;
    $x2 = $x1 + $cellW;
    if(preg_match('/C_(.+)$/', $itemNameKey, $matches)){
      $titlebgColor = switch_color_format($im,$matches[1]);
      if($itemNameKey == $contrlColor) $displayFont = $black;
      $displayedStr = $itemLableArr[$itemNameKey];
    }else{
      $displayedStr = $itemNameKey.' '.$itemLableArr[$itemNameKey];
      $titlebgColor = $black;
    }
    imagefilledrectangle($im, $x1, 0, $x2-2, $labalH, $titlebgColor);  
    imagestringup($im, $fontSize, $x1+round(($cellW - $fontH)/2), $labalH-2, $displayedStr, $displayFont);
    $x1 = $x2;
  }
}
$_SESSION["labalH"] = $labalH;
$y1 = $labalH;


$hitsNameArr_handle = fopen($hitsNameFileName, "w");
if(!$hitsNameArr_handle){
  echo "Cannot write to file $hitsIndexFileName";
  exit;
}
$sharedColorSet = array();
if($frm_color_mode == 'shared'){
  get_colorArrSets('', $sharedColorSet, $im, 'shared');
}
$filtedGeneIdArr = array();

foreach($hitsGeneIdIndexArr as $hitsGeneIdIndex){
  $tmpStr = $hitsGeneIdIndex."@@".implode("@@", $hitsNameArr[$hitsGeneIdIndex]);
  fwrite($hitsNameArr_handle, $tmpStr."\r\n");
  if($frm_color_mode == 'shared'){
    if($hitsNameArr[$hitsGeneIdIndex]['color'] == 'red'){
      $bgColor = $bgColorRed;
    }elseif($hitsNameArr[$hitsGeneIdIndex]['color'] == 'blue'){
      $bgColor = $bgColorBlue;
    }elseif($hitsNameArr[$hitsGeneIdIndex]['color'] == 'green'){
      $sharedColorIndex = floor($hitsNameArr[$hitsGeneIdIndex]['counter']*10/$totalitems);
      $bgColor = $sharedColorSet[$sharedColorIndex];
    }
  }
	$A = isset($frm_BT) && !$frm_BT;
	$B = $hitsNameArr[$hitsGeneIdIndex]['isBait'];
  $y2 = $y1 + $cellH;
  $x1 = 0;
  $filter_flag = 0;
  
  $bioFilterArr = explode(",",$hitsNameArr[$hitsGeneIdIndex]['filter']);
  $hitsArr_forLine = array();
  $filterLine = 1;
  for($j=0; $j<count($itemNameArr); $j++){
    $temHitsArr = $itemNameArr[$itemIdIndexArr[$j]];
    $hitsArr_forCell = array();
    if(array_key_exists($hitsGeneIdIndex, $temHitsArr)){
      $hitSore = 0;
      $tmpPropertyStr = $temHitsArr[$hitsGeneIdIndex];
      $tmp_p_arr = explode("##",$tmpPropertyStr);
      foreach($tmp_p_arr as $tmp_p_val){
        $tmp_p_arr2 = explode("=",$tmp_p_val);
        $tmpPropertyArr[$tmp_p_arr2[0]] = $tmp_p_arr2[1];
      }
			$filter_flag = 0;
      if($applyFilters){
  			$C = $tmpPropertyArr['isBait'];
  			if(!$A || !$C){
  				if($B){
  			    foreach($typeBioArr as $Value) {
  			   		$frmName = 'frm_' . $Value['Alias'];        
    		  		if($$frmName and in_array($Value['Alias'] ,$bioFilterArr)){
    		  			$filter_flag = 1;
    		        break;
    		  		}
  					}	
  				}
  				if(!$filter_flag){
  					$hitsArr['ID'] = $tmpPropertyArr['ID'];
  					$hitsArr['WellID'] = $tmpPropertyArr['WellID'];
  					$hitsArr['MW'] = $tmpPropertyArr['MW'];
  					$expFilterArr = array();
  					get_exp_filter_arr($expFilterArr,$hitsArr);
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
      $hitsArr_forCell[0] = $tmpPropertyArr;
      $hitsArr_forCell[1] = $filter_flag;
      $filterLine = $filterLine && $filter_flag;
    }
    $hitsArr_forLine[$j] = $hitsArr_forCell;
  }
  if($filterLine){
    array_push($filtedGeneIdArr, $hitsGeneIdIndex);
    $Height_cut_counter++;
    continue;
  }
  $lineInfo = '';
  for($j=0; $j<count($itemNameArr); $j++){
    $x2 = $x1 + $cellW;
    if($hitsArr_forLine[$j]){
      $hitSore = 0;
      $filter_flag = $hitsArr_forLine[$j][1];
      if($filter_flag){
        $bgColor = $bglight;
      }else{
        $tmpPropertyArr = $hitsArr_forLine[$j][0];               
        if(isset($FeqValueArr[$hitsGeneIdIndex])){
          $fequencySore = $FeqValueArr[$hitsGeneIdIndex];
        }else{
          $fequencySore = '';
        }
//----------------------------------------------------------------------------        
        if(isset($subFeqsValueArr[$hitsGeneIdIndex])){  
          $haredeFrequency = $subFeqsValueArr[$hitsGeneIdIndex];
        }else{
          $haredeFrequency = '';
        }
        $sharedHitNmb = $hitsNameArr[$hitsGeneIdIndex]['counter'];
        
        $haredeFrequency = round($sharedHitNmb*100/$totalitems,1);
//-----------------------------------------------------------------------------------        
        if($orderby == 'Fequency'){
  				$hitSore = $fequencySore;
  			}elseif(in_array($orderby, $passedTypeArr)){
        
          if(array_key_exists($hitsGeneIdIndex, $subFeqsValueArr[$typeInitIdArr[$orderby]])){
            $hitSore = $subFeqsValueArr[$typeInitIdArr[$orderby]][$hitsGeneIdIndex];
          }
        }else{
          $hitSore = $tmpPropertyArr[$sqlOrderby];
        }
        $colorIndex = color_num_im($hitSore,$colorArrLen);
        if($frm_color_mode != 'shared'){        
          $bgColor = $colorArrSets[$colorIndex];
        }
        if($Is_geneLevel){
          $lineInfo .= str_replace(":", "+++", $tmpPropertyArr['GeneID']).':'.$tmpPropertyArr['SpectralCount'].'('.$tmpPropertyArr['Subsumed'].'-'.$tmpPropertyArr['Unique'].')';
        }else{
          $lineInfo .= str_replace(":", "+++", $tmpPropertyArr['HitGI']).':'.$tmpPropertyArr['Expect'].'('.$tmpPropertyArr['Pep_num'].'-'.$tmpPropertyArr['Pep_num_uniqe'].'-'.$tmpPropertyArr['Coverage'].'-'.(($fequencySore=='&nbsp;')?'':$fequencySore).'-'.(($haredeFrequency=='&nbsp;')?'':$haredeFrequency).')';
        }
      }
      imagefilledrectangle($im, $x1, $y1, $x2, $y2, $bgColor);
    }else{
      //$bgColor = $bglight;
    }  
    $x1 = $x2;
    if($j != count($itemNameArr)-1){
      $lineInfo .= ',';
    }  
  }
  //-----------------------------------------------------------
  list($tmpGeneName,$tempProteinID,$tmpLocusTag) = explode(',',$hitsNameArr[$hitsGeneIdIndex]['name']);
  if(preg_match("/(\w+)_GI$/", $hitsGeneIdIndex, $matches)){
    $tempHitGeneID = $tempProteinID;
  }else{
    $tempHitGeneID = $hitsGeneIdIndex;
  }
  $old = array(",","\n","\r");
  $new = array(";","","");
  $tempHitGeneID = str_replace($old, $new, $tempHitGeneID);
  $tmpGeneName = str_replace($old, $new, $tmpGeneName);
  $tmpLocusTag = str_replace($old, $new, $tmpLocusTag);
  $lineLable = $tempHitGeneID.','.$tmpGeneName.','.(($tmpLocusTag=='-')?'':$tmpLocusTag);
  //echo "$lineInfo<br>";
  if($frm_color_mode == 'shared'){
    $lineInfo = $lineLable.','.$lineInfo.'@'.$bgColor."\r\n";
  }else{
    $lineInfo = $lineLable.','.$lineInfo."\r\n"; 
  }
  fwrite($reportFile_handle, $lineInfo);   
  //echo "$lineInfo";exit;
  //--------------------------------------------------------------
  $y1 = $y2;
}

fclose($hitsNameArr_handle);
$hitsGeneIdIndexArr = array_diff($hitsGeneIdIndexArr, $filtedGeneIdArr);
$hitsIndexArr_handle = fopen($hitsIndexFileName, "w");
if($hitsIndexArr_handle){
  $hitsIndexStr = implode(",", $hitsGeneIdIndexArr);
  fwrite($hitsIndexArr_handle, $hitsIndexStr);
  fclose($hitsIndexArr_handle);
}else{
  echo "Cannot write to file $hitsIndexFileName";
  exit;
}

$subDir = "../TMP/comparison/P_$AccessProjectID/";
$png_file_name = $subDir.$AccessUserID."_report.png";

header("Content-type: image/png");
if($Height_cut_counter){
  $Height_cut = $Height_cut_counter*$cellH;
  $im_out = imagecreatetruecolor($overallWidth,$overallHeight-$Height_cut);
  imagecopyresized($im_out, $im, 0, 0, 0, 0, $overallWidth, $overallHeight-$Height_cut, $overallWidth, $overallHeight-$Height_cut);
  
  imagepng($im_out,$png_file_name);
  imagedestroy($im_out);
$im_out = imagecreatefrompng($png_file_name);
imagepng($im_out);  
  
  //imagepng($im_out);
  imagedestroy($im_out);
}else{
  imagepng($im,$png_file_name);
  imagedestroy($im);
  $im = imagecreatefrompng($png_file_name);
  imagepng($im);
  imagedestroy($im);
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

function color_num_im($score,$colorArrLen){
  global $biggestPowedSore,$power;
  $colorRange = $colorArrLen - 1;
  $powedSore = pow($score,$power);
  $colorIndex = round($colorRange * $powedSore / $biggestPowedSore);
  return $colorIndex;
}
function create_filter_status_arrs(&$typeBioArr,&$typeExpArr,&$typeFrequencyArr,$callBy=''){
  global $PROHITSDB,$workingFilterSetID,$theaction,$submitted,$hitType;
  $SQL = "SELECT FilterNameID FROM Filter WHERE FilterSetID='" . $_SESSION['workingFilterSetID'] . "' ORDER BY FilterNameID";
  $filterIDArr=$PROHITSDB->fetchAll($SQL);
  foreach($filterIDArr as $Value) {
    $SQL = "SELECT ID, Name, Alias, Color, Type, Init FROM FilterName WHERE ID=" . $Value['FilterNameID'];
    $filterAttrArr=$PROHITSDB->fetch($SQL);
    if($filterAttrArr['Type'] == 'Fre'){
      $filterAttrArr['Counter'] = 0;
      $typeFrequencyArr = $filterAttrArr;
    }else{
      $filterAttrArr['Counter'] = 0;  
      if($filterAttrArr['Type'] == 'Bio'){
        array_push($typeBioArr, $filterAttrArr);
      }else if($filterAttrArr['Type'] == 'Exp' && ($hitType == 'normal' || $callBy)){
        array_push($typeExpArr, $filterAttrArr);
      }  
    }  
  }
  if($hitType != 'normal' && !$callBy){
    get_tpp_pep_typeExpArr($typeExpArr);
  }
}

function get_frequency_arr(&$frequencyArr,$FileName=''){
  global $AccessProjectID;
  $biggest = 0;
  if(!$FileName) return false;
  $frequencyDir = STORAGE_FOLDER."Prohits_Data/frequency/";
  $frequencyfileName = $frequencyDir.'P'.$AccessProjectID."_$FileName";
  if(!is_file($frequencyfileName)) return false; 
  $frequencyHandle = fopen($frequencyfileName, "r");
  if($frequencyHandle){
    $buffer = fgets($frequencyHandle, 4096);
    while (!feof($frequencyHandle)) {
      $buffer = fgets($frequencyHandle, 4096);
      $buffer = trim($buffer);
      $tmpArr = explode(',',$buffer);
      if(count($tmpArr) == 2){
        $frequencyArr[$tmpArr[0]] = $tmpArr[1];
        if($tmpArr[1] > $biggest) $biggest = $tmpArr[1];
      }  
    }
    fclose($frequencyHandle);
  }else{
  }
  return $biggest;
}
function hits_table_field_translate_for_tpp($inField){
  $outField[0] = '';
  $outField[1] = '';
  
  if($inField == 'ID'){
    $outField[0] = 'ID';
    $outField[1] = 'ID';
  }elseif($inField == 'Expect' || $inField == 'Expect2'){
    $outField[0] = 'PROBABILITY';
    $outField[1] = 'PROBABILITY as Expect';
  }elseif($inField == 'Coverage'){
    $outField[0] = 'PERCENT_COVERAGE';
    $outField[1] = 'PERCENT_COVERAGE as Coverage';
  }elseif($inField == 'Pep_num'){
    $outField[0] = 'TOTAL_NUMBER_PEPTIDES';
    $outField[1] = 'TOTAL_NUMBER_PEPTIDES as Pep_num';
  }elseif($inField == 'Pep_num_uniqe'){
    $outField[0] = 'UNIQUE_NUMBER_PEPTIDES';
    $outField[1] = 'UNIQUE_NUMBER_PEPTIDES as Pep_num_uniqe';
  }
  return $outField;
}

function get_NS_geneID(&$NSfilteIDarr,$groupID){
  if(!$groupID) return;
  global $HITSDB;
  $NS_Dir = "../TMP/Non_Specific/";
  $NS_data_dir = $NS_Dir."NS_data/";
  $tmpGroupArr = array();
  $SQL = "SELECT `ID`, `FileName` FROM `ExpBackGroundSet` WHERE `ID`='$groupID'";
  
  $NSarr = $HITSDB->fetch($SQL);
  if($NSarr['FileName']){
    $NSfileFullName = $NS_data_dir.$NSarr['FileName'];
    $NSgeneIDstr = @trim(file_get_contents($NSfileFullName));
    $tmpArr = explode(",",$NSgeneIDstr);
    $NSfilteIDarr = $tmpArr;
  }
}
function get_exp_filter_arr(&$expAliasArr,&$hitsArr){
	global $HITSDB,$hitType;
  //------Process CO, ME, RI, SO,  ----------------------------------------
	$SQL = "SELECT FilterAlias FROM HitNote WHERE HitID='".$hitsArr['ID']."'";
	$HitNoteArr = $HITSDB->fetchAll($SQL);
	if($HitNoteArr){
	  for($n=0; $n<count($HitNoteArr); $n++){
	    if($HitNoteArr[$n]['FilterAlias']){
	      array_push($expAliasArr, $HitNoteArr[$n]['FilterAlias']);
	    }
	  }
	}
	if($hitType != "TPP"){
		$SQL = "SELECT B.BandMW FROM Band B, PlateWell P WHERE B.ID=P.BandID AND P.ID='".$hitsArr['WellID']."'";
		$BandMWArr = $HITSDB->fetch($SQL);
		$tmpNum = 0;
		if($BandMWArr && $BandMWArr['BandMW']){
		  if($BandMWArr['BandMW'] > 0){
		  	$tmpNum = abs(($BandMWArr['BandMW'] - $hitsArr['MW'])*100/$BandMWArr['BandMW']);
			}
			if($BandMWArr['BandMW'] < 25 or $BandMWArr['BandMW'] > 100){
				if($tmpNum > 50){
					array_push($expAliasArr, "AW");
				}
			}else{
		    if($tmpNum > 30 ){
					array_push($expAliasArr, "AW");
				}
		  } 
		}
	}
}

function get_project_SearchEngine(){
  global $AccessProjectID,$HITSDB;
  $protein_tableName_arr = array('Hits','TppProtein');
  $SearchEngineConfig_arr = array();
  $first_Engine = '';
  foreach($protein_tableName_arr as $val){
    $SQL = "SELECT H.SearchEngine FROM $val H
            LEFT JOIN Bait B 
            ON (H.BaitID=B.ID) 
            WHERE B.ProjectID='$AccessProjectID'
            GROUP BY `SearchEngine`"; 
    $tmp_Engine_arr = $HITSDB->fetchAll($SQL);
    foreach($tmp_Engine_arr as $tmp_Engine_val){
      $SearchEngine_TMP = str_replace("Uploaded", "", $tmp_Engine_val['SearchEngine']);
      if(!trim($SearchEngine_TMP)) continue;
      if(!in_array($SearchEngine_TMP, $SearchEngineConfig_arr)){
        if($SearchEngine_TMP == "Mascot"){
          $first_Engine = $SearchEngine_TMP;
        }else{
          array_push($SearchEngineConfig_arr, $SearchEngine_TMP);
        }  
      }
    }
  }
  if($first_Engine) array_unshift($SearchEngineConfig_arr, $first_Engine);
  return $SearchEngineConfig_arr;
}

function get_SearchEngine_lable_arr($SearchEngineConfig_arr){
  $SearchEngine_lable_arr = array();
  foreach($SearchEngineConfig_arr as $key){
    if($key == 'GPM'){
      $SearchEngine_lable_arr[$key] = 'XTandem';
    }elseif($key == 'iProphet'){
      $SearchEngine_lable_arr[$key] = $key;
    }else{
      $SearchEngine_lable_arr[$key] = $key;
    }
  }
  foreach($SearchEngineConfig_arr as $key){
    $tpp_key = 'TPP_'.$key;
    $tpp_val = 'TPP '.$key;
    if($key == 'GPM'){
      $SearchEngine_lable_arr[$tpp_key] = 'TPP XTandem';
    }elseif($key == 'iProphet'){
      $SearchEngine_lable_arr[$tpp_key] = $key;
    }else{
      $SearchEngine_lable_arr[$tpp_key] = $tpp_val;
    }
  }
  return $SearchEngine_lable_arr;
}
exit;
?>



