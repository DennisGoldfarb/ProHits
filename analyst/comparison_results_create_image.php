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

include("comparison_common_functions.php");

if($_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}
if(isset($strMaxL)){
	$imageH = $strMaxL * imagefontwidth($fontSize)+6;
	$imageW = imagefontheight($fontSize);
}elseif(isset($filter_for)){
  $imageH = 10;
	$imageW = 10;
}
$im = Imagecreate($imageW,$imageH) or die("Cannot Initialize new GD image stream");

if(isset($strMaxL)){
	$white = ImageColorAllocate($im,255,255,255);
	$black = ImageColorAllocate($im,0,0,0);
  if(isset($lableBgc) && $lableBgc){
    $backColor = switch_color_format($im,$lableBgc);
  }else{
    $backColor = $black;
  }
	imagefilledrectangle($im , 0, 0, $imageW, $imageH, $backColor);
  if($displayedStr == 'Control Group'){
    $fontColor = $black;
  }else{
    $fontColor = $white;
  }    
	imagestringup($im, $fontSize, 0, $imageH-4, $displayedStr, $fontColor);
}elseif(isset($filter_for)){  
  $backColor = switch_color_format($im,$lableBgc);
  imagefilledrectangle($im , 0, 0, $imageW, $imageH, $backColor);
}else{
	get_colorArrSets($powerColorIndex, $colorArrSet, $im);
	imagefilledrectangle($im , 0, 0, $imageW, $imageH, $colorArrSet[$colorkey]);
}	

header("Content-type: image/png");
imagepng($im);
imagedestroy($im);
?>
