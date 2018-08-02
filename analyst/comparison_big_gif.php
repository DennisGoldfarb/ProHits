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

ini_set("memory_limit","400M");
error_reporting(E_ALL );
set_time_limit(3600*24);  // it will execute for 24 hours

$theaction = 'showImage';
$overallWidth =800;
$overallHeight =700;

$powerArr['Expect'] = 1/2;
$powerArr['Pep_num'] = 1/2;
$powerArr['Pep_num_uniqe'] = 1;
$powerArr['Coverage'] = 1;     
$powerArr['Fequency'] = 1;
$itemlableMaxL = 0;

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");

$theation = "showImage";
$PROTEINDB = new mysqlDB(PROHITS_PROTEINS_DB);

if(!_is_dir("../TMP/comparison/")) _mkdir_path("../TMP/comparison/");
if(!_is_dir("../TMP/comparison/P_$AccessProjectID/")) _mkdir_path("../TMP/comparison/P_$AccessProjectID/");
$subDir = "../TMP/comparison/P_$AccessProjectID/";
$argumentsFileName = $subDir.$AccessUserID."_arguments.txt";
$selectedListStrFileName = $subDir.$AccessUserID."_selected_list_.txt";
$hitsIndexFileName = $subDir.$AccessUserID."_hits_index.txt";
$hitsNameFileName = $subDir.$AccessUserID."_hits_name.txt";

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
$FeqValueArr = array();
$totalFeq = get_fequency($FeqIndexArr, $FeqValueArr); //-create a fequency sorted index and values arrays.

//5. if tag exist create $subFeqIndexArr and $subFeqValueArr-------------------------------------------------------
$subFeqIndexArr = array();
$subFeqValueArr = array();
if(count($passedTypeArr)){
  $typeNum = '';
  foreach($passedTypeArr as $temKey => $temValue){ 
    if($temValue == $orderby){ 
      $typeNum = $temKey;
    }  
  }
	//For showNormal and popWindow create a index array for sorting usage create a two dimention array for display usage
	//For showImage create a index array for sorting usage create a one dimention array for display usage
  get_subFequency($subFeqIndexArr, $subFeqsValueArr,$passedTypeArr, $typeNum);
}

//6. create $itemNameArr (hits tree) for every item and $hitsGeneIdIndexArr and hits property array $hitsNameArr-------
$firstHitsArr = array();
$contrlArr = array();
$hitsGeneIdIndexArr = array();
$hitsNameArr = array();
$itemNameArr = array();
$hitsGeneIdIndexArr2 = array();

for($j=0; $j<count($itemIdIndexArr); $j++){
	create_itemTree_hitsIndex_hitsPropty_Arrs($j,$firstHitsArr,$contrlArr,$hitsGeneIdIndexArr,$hitsGeneIdIndexArr2,$hitsNameArr,$itemNameArr,'y');	
}
if($asc_desc == 'ASC'){
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
$totalHits = count($hitsGeneIdIndexArr);
//9.if $frm_color_mode == 'shared' filter $hitsGeneIdIndexArr amd $hitsNameArr--------------------------------------------
if($frm_color_mode == 'shared'){
  shared_color_filter($hitsGeneIdIndexArr,$hitsNameArr);
}
$totalitems = count($itemIdIndexArr);
//format_image();

if($biggestPowedSore <= 0) $biggestPowedSore = 1;
$totalHits = count($hitsGeneIdIndexArr);

$overallHeight = $cellH * $totalHits + $labalH;
if($overallHeight == 0) $overallHeight = 1;
//======================================================================================================================
//7.***draw image**********
$im = Imagecreate($overallWidth,$overallHeight) or die("Cannot Initialize new GD image stream");
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
//$start_time = @date("H:i:s");
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

$hitsIndexArr_handle = fopen($hitsIndexFileName, "w");
if($hitsIndexArr_handle){
  $hitsIndexStr = implode(",", $hitsGeneIdIndexArr);
  fwrite($hitsIndexArr_handle, $hitsIndexStr);
  fclose($hitsIndexArr_handle);
}else{
  echo "Cannot write to file $hitsIndexFileName";
  exit;
}
$hitsNameArr_handle = fopen($hitsNameFileName, "w");
if(!$hitsNameArr_handle){
  echo "Cannot write to file $hitsIndexFileName";
  exit;
}

foreach($hitsGeneIdIndexArr as $hitsGeneIdIndex){
  $tmpStr = $hitsGeneIdIndex."@@".implode("@@", $hitsNameArr[$hitsGeneIdIndex]);
  fwrite($hitsNameArr_handle, $tmpStr."\r\n");
  if($frm_color_mode == 'shared'){
    if($hitsNameArr[$hitsGeneIdIndex]['color'] == 'red'){
      $bgColor = $bgColorRed;
    }elseif($hitsNameArr[$hitsGeneIdIndex]['color'] == 'blue'){
      $bgColor = $bgColorBlue;
    }elseif($hitsNameArr[$hitsGeneIdIndex]['color'] == 'green'){
      $bgColor = $bgColorGreen;
    }
  }
  
  $y2 = $y1 + $cellH;
  $x1 = 0;
  foreach($itemNameArr as $itemNameValue){
    $temHitsArr = $itemNameValue;
    $x2 = $x1 + $cellW;
    if(array_key_exists($hitsGeneIdIndex, $temHitsArr)){
      $hitSore = 0;
			if($orderby == 'Fequency'){
				if(isset($FeqValueArr[$hitsGeneIdIndex])){
					$hitSore = $FeqValueArr[$hitsGeneIdIndex];
				}	
			}elseif(array_key_exists($orderby, $typeInitIdArr)){
        if(isset($subFeqsValueArr[$hitsGeneIdIndex])){
          $hitSore = $subFeqsValueArr[$hitsGeneIdIndex];
        }  
      }else{
        $tmpPropertyArr = explode(':',$temHitsArr[$hitsGeneIdIndex]);
        $hitSore = $tmpPropertyArr[1];
      }
      $colorIndex = color_num($hitSore,$colorArrLen);
      if($frm_color_mode != 'shared'){        
        $bgColor = $colorArrSets[$colorIndex];
      }
      imagefilledrectangle($im, $x1, $y1, $x2, $y2, $bgColor);
    }  
    $x1 = $x2;
  }
  $y1 = $y2;
}
fclose($hitsNameArr_handle);

header("Content-type: image/png");
imagepng($im);
imagedestroy($im);

//***functions**********************
function get_fequency(&$FeqIndexArr, &$FeqValueArr){
  global $HITSDB, $asc_desc, $AccessProjectID;
  $SQL = "SELECT `GeneID`, 
                  `Value` 
                  FROM `ExpFilter` 
                  WHERE `ProjectID`='".$AccessProjectID."' AND `FilterAlias`='FQ' 
                  ORDER BY `Value` $asc_desc";
  $temFeq = $HITSDB->fetchAll($SQL);
  $totalFeq = count($temFeq);
  foreach($temFeq as $temFeqValue){
    array_push($FeqIndexArr, $temFeqValue['GeneID']);
    $FeqValueArr[$temFeqValue['GeneID']] = round(($temFeqValue['Value']/$totalFeq)*100,2);
  }
  return $totalFeq;
}
function get_subFequency(&$subFeqIndexArr, &$subFeqsValueArr,$passedTypeArr, $typeNum = ''){
  global $AccessProjectID, $asc_desc,$power, $theation;
  $updatedFlag = 0;
  $maxScore = 0;
  $subDir = STORAGE_FOLDER."Prohits_Data/subFrequency/";
  foreach($passedTypeArr as $typeKey => $typeValue){
		if($theation == "showImage" && $typeKey != $typeNum) continue;
    $subFileName = $subDir."Pro".$AccessProjectID."_Type".$typeKey.".csv";
    if(!_is_file($subFileName) && !$updatedFlag){
       updata_frequency();
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
        array_push($subFeqIndexArr, $subFeqKey);
      }  
    }
		if($theation == "showImage"){
    	$subFeqsValueArr = $subFeqValueArr;
		}else{
			$subFeqsValueArr[$typeKey] = $subFeqValueArr;
		}	
  }
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

function switch_color_format($image,$colorValue){
  $colorString = '';
  if(substr($colorValue, 0, 1) == '#'){
    $colorString = substr($colorValue, 1, 6); 
  }else{
    $colorString = $colorValue;
  }
  $r = substr($colorString, 0, 2);
  $g = substr($colorString, 2, 2);
  $b = substr($colorString, 4, 2); 
  $r = hexdec("0x{$r}");
  $g = hexdec("0x{$g}");
  $b = hexdec("0x{$b}"); 
  return $color = ImageColorAllocate($image, $r, $g, $b);
}

function color_num($score,$colorArrLen){
  global $biggestPowedSore,$power;
  $colorRange = $colorArrLen - 1;
  $powedSore = pow($score,$power);
  $colorIndex = round($colorRange * $powedSore / $biggestPowedSore);
  return $colorIndex;
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
function shared_color_filter(&$hitsGeneIdIndexArr,&$hitsNameArr){
  global $contrlColor,$itemIdIndexArr,$frm_red,$frm_blue,$frm_green,$totalitems,$applyFilters;
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
    array_push($tmphitsGeneIdIndexArr, $hitGeneId);
    $tmphitsNameArr[$hitGeneId] = $hitsNameArr[$hitGeneId];
    $tmphitsNameArr[$hitGeneId]['color'] = $bgColor;
  }
  $hitsGeneIdIndexArr = $tmphitsGeneIdIndexArr;
  $hitsNameArr = $tmphitsNameArr;
  unset($tmphitsGeneIdIndexArr);
  unset($tmphitsNameArr);
}  
exit;
?>



