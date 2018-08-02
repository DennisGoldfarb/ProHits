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

session_start();

$imageWidth = 400;
$orientation = '';
$border = '';
$size_unit = '';
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;  
}

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

if(!isset($tital) || !$tital){
  echo "please set tital argument";
  exit;
}

if(isset($_SESSION["time_arr"]) && isset($_SESSION["tableName_arr"]) && isset($_SESSION["matrix_arr"])){
  $time_arr = $_SESSION["time_arr"];
  $tableName_arr = $_SESSION["tableName_arr"];
  $matrix_arr = $_SESSION["matrix_arr"];
}else{
  echo "No bars infomation passed!";
  exit;
} 

/*echo "<pre>";
print_r($time_arr);
print_r($tableName_arr);
print_r($matrix_arr);
echo "</pre>";exit;*/
$orientation = strtolower($orientation);

$tital = strtoupper($tital);
$title_font_b = 5;
$lable_font = 2;
$lable_font_b = 3;

$lable_font_b_width = imagefontwidth($lable_font_b);
$lable_font_b_heighth = imagefontheight($lable_font_b);
$lable_font_width = imagefontwidth($lable_font);
$lable_font_heighth = imagefontheight($lable_font);
$title_font_b_width = imagefontwidth($title_font_b);
$title_font_b_heighth = imagefontheight($title_font_b);

$graph_width = $imageWidth;
$left_magin = 20;
$top_magin = 20;
$bottem_magin = 10;
$tital_spacing =30;
$bar_height = $lable_font_heighth;
$bar_v_spacing = 2;
$bar_h_spacing = 10;

//$barsDataArr = $_SESSION["barsDataArr"];
//--------------------------------------------------------------
$barsDataArr = array();
$total_value = 0;
if($content == 'num_files'){
  $size_unit = '';
  $bar_color = '#800000';
}else{
  $bar_color = '#808000';
}  
if(!$tableName && $show_single == 'single'){
  foreach($matrix_arr as $matrix_key => $matrix_val){
    $tmpArr = array();
    if(isset($matrix_val[$date_time][$content])){
      $total_value += $matrix_val[$date_time][$content];
      $tmpArr["lable"] = $matrix_key;
      $tmpArr["color"] = $tableName_arr[$matrix_key];
      $tmpArr[$content] = $matrix_val[$date_time][$content];
      $tmpArr["unit"] = $size_unit;
    }else{
      $tmpArr["lable"] = $matrix_key;
      $tmpArr["color"] = "#ffffff";
      $tmpArr[$content] = 0;
      $tmpArr["num_files"] = 0;
      $tmpArr["unit"] = $size_unit;
    }
    array_push($barsDataArr, $tmpArr);  
  }
  $tmp_tital = $date_time;
}elseif(!$tableName && $show_all == 'all'){
  foreach($time_arr as $time_val){
    $tmpArr = array();
    $sub_total_val = 0;
    foreach($tableName_arr as $tmp_table_key => $tmp_table_name){
      if(isset($matrix_arr[$tmp_table_key][ $time_val][$content])){
        $sub_total_val += $matrix_arr[$tmp_table_key][ $time_val][$content];
      }
    }
    $total_value += $sub_total_val;
    $tmpArr['lable'] = $time_val;
    $tmpArr['color'] = $bar_color;
    $tmpArr[$content] = $sub_total_val; 
    $tmpArr["unit"] = $size_unit;
    array_push($barsDataArr, $tmpArr);
  }
  $tmp_tital = "All Machines";
}elseif($tableName){
  foreach($time_arr as $time_val){
    $tmpArr = array();
    $tmpArr['lable'] = $time_val;
    $tmpArr['color'] = $bar_color;
    $tmpArr[$content] = 0;
    if(isset($matrix_arr[$tableName][$time_val][$content])){
      $tmpArr[$content] = $matrix_arr[$tableName][$time_val][$content];
      $total_value += $matrix_arr[$tableName][$time_val][$content];
    }  
    $tmpArr["unit"] = $size_unit;
    array_push($barsDataArr, $tmpArr);
  }
  $tmp_tital = "$tableName";
}else{
  echo "^^^^^^^^^^^^";exit;
}

if($content == 'size'){
  $tital = $tmp_tital." (total size: $total_value $size_unit)";
}else{
  $tital = $tmp_tital." (total files: $total_value)";
} 

//----------------------------------------------------------------
//$_SESSION["barsDataArr"] = '';
//unset($_SESSION["barsDataArr"]);
/*echo "<pre>";
print_r($barsDataArr);
echo "</pre>";exit;*/
//echo "******************";exit;
$num_bars = count($barsDataArr);

$graph_height = $top_magin + $bottem_magin + $title_font_b_heighth + $tital_spacing + ($bar_height + $bar_v_spacing) * $num_bars;

if($orientation == "v"){
  $graph = ImageCreate($graph_height, $graph_width);
}else{
  $graph = ImageCreate($graph_width, $graph_height);
}  

$white = ImageColorAllocate($graph,255,255,255);
$black = ImageColorAllocate($graph,0,0,0);       
$red = ImageColorAllocate($graph,255,85,75);      
$green = ImageColorAllocate($graph,57,255,35);
$blue = ImageColorAllocate($graph,72,141,255);

$lable_max = 0;
$mg_max = 0;
$mg_len_max = 0;
$total_mgb = 0;

if($barsDataArr) $unit = $size_unit;

foreach($barsDataArr as $value){
  if(strlen($value['lable']) > $lable_max) $lable_max = strlen($value['lable']);
  if($value[$content] > $mg_max) $mg_max = $value[$content];
  if(strlen($value[$content]) > $mg_len_max) $mg_len_max = strlen($value[$content]);
  $total_mgb += $value[$content];
}
if(!$tableName && $show_single == 'single'){
  if($content == 'size'){
    $mg_max = $bigest_size;
  }else{  
    $mg_max = $bigest_num_file;
  }
  $mg_len_max = strlen($mg_max); 
}
//$tital = $tital . " (total: $total_mgb $size_unit)";

$total_mgb_str = "Total storage size: ".$total_mgb." ".$size_unit;


if($orientation == "v"){
  if($border){
    ImageRectangle($graph, 0, 0, $graph_height-1, $graph_width-1, $blue);
  }
  $this_x1_s = $graph_width - ($graph_width - strlen($tital) * $title_font_b_width) / 2;
  $this_y1 = $top_magin;
  imagestringup($graph, $title_font_b, $this_y1, $this_x1_s, $tital, $blue);
  $this_x1_s = $graph_width - $left_magin;
  $this_x1_r = $graph_width - ($left_magin + $lable_max*$lable_font_width + $bar_h_spacing);
  $this_x2_s = $left_magin + ($mg_len_max + 3)*$lable_font_width;
  $max_bar_len = ($this_x1_r - $this_x2_s - $bar_h_spacing);
  $this_y1 = $this_y1 + $tital_spacing;
}else{
  if($border){
    ImageRectangle($graph, 0, 0, $graph_width-1, $graph_height-1, $blue);
  }
  $this_x1_s = ($graph_width - strlen($tital) * $title_font_b_width) / 2;
  $this_y1 = $top_magin;
  imagestring($graph, $title_font_b, $this_x1_s, $this_y1, $tital, $blue);
  $this_x1_s = $left_magin;
  $this_x1_r = $this_x1_s + $lable_max*$lable_font_width + $bar_h_spacing;
  $this_x2_s = $graph_width - $left_magin - ($mg_len_max + 3)*$lable_font_width;
  $max_bar_len = $this_x2_s - $this_x1_r - $bar_h_spacing;
  $this_y1 = $this_y1 + $tital_spacing;
}

foreach($barsDataArr as $value){
  $colorString = '';
  if(substr($value['color'], 0, 1) == '#'){
    $colorString = substr($value['color'], 1, 6); 
  }else{
    $colorString = $value['color'];
  }
  $r = substr($colorString, 0, 2);
  $g = substr($colorString, 2, 2);
  $b = substr($colorString, 4, 2); 
  $r = hexdec("0x{$r}");
  $g = hexdec("0x{$g}");
  $b = hexdec("0x{$b}"); 
  $color = ImageColorAllocate($graph, $r, $g, $b);
  
  $this_y2 = $this_y1 + $bar_height;
  if(!$mg_max){
    $tmp_val = 0;
  }else{
    $tmp_val = $value[$content]/$mg_max;
  }  
  if($orientation == "v"){
    $this_x2_r = $this_x1_r - $tmp_val*$max_bar_len;
    imagestringup($graph, $lable_font, $this_y1, $this_x1_s, strtoupper($value['lable']), $black);
    imagefilledrectangle($graph, $this_y1, $this_x1_r, $this_y2, $this_x2_r, $color);
    imagestringup($graph, $lable_font, $this_y1, $this_x2_s, $value[$content].' '.$value['unit'], $black);
  }else{
    $this_x2_r = $this_x1_r + $tmp_val*$max_bar_len;
    imagestring($graph, $lable_font, $this_x1_s, $this_y1, strtoupper($value['lable']), $black);
    imagefilledrectangle($graph, $this_x1_r, $this_y1, $this_x2_r, $this_y2, $color);
    imagestring($graph, $lable_font, $this_x2_s, $this_y1, $value[$content].' '.$value['unit'], $black);
  }  
  $this_y1 = $this_y2 + $bar_v_spacing;
} 

header("Content-type: image/jpeg");
ImagePNG($graph);
ImageDestroy($graph);
//$_SESSION["barsDataArr"] = '';
?>