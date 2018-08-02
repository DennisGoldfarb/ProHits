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

if( $_SERVER['REQUEST_METHOD'] == "POST"){
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
  if($displayedStr == 'Contrl Group'){
    $fontColor = $black;
  }else{
    $fontColor = $white;
  }    
	imagestringup($im, $fontSize, 0, $imageH-4, $displayedStr, $fontColor);
}else{
	get_colorArrSets($powerColorIndex, $colorArrSet, $im);
	imagefilledrectangle($im , 0, 0, $imageW, $imageH, $colorArrSet[$colorkey]);
}	

header("Content-type: image/png");
imagepng($im);
imagedestroy($im);
exit;

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
?>      

