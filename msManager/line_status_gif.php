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

//$barsDataArr = $_SESSION["barsDataArr"];
//--------------------------------------------------------------
$barsDataArr = array();
$total_value = 0;
$biggest_value = 0;
if($content == 'num_files'){
  $size_unit = '';
  $bar_color = '#800000';
}else{
  $bar_color = '#808000';
}
if(!$tableName && $show_single == 'single'){
  for($j=0; $j<count($time_arr);$j++){
  //foreach($time_arr as $date_time){
    $tmpArr = array();
    foreach($tableName_arr as $tableName_key => $tableName_val){    
      $tmpArr_2 = array();
      if(isset($matrix_arr[$tableName_key][$time_arr[$j]])){
        $content_value = $matrix_arr[$tableName_key][$time_arr[$j]][$content];
        if(!$content_value) $content_value = 0;
        if($biggest_value < $content_value) $biggest_value = $content_value;
        $total_value += $content_value;
        $tmpArr_2["color"] = $tableName_val;
        $tmpArr_2[$content] = $content_value;
      }else{
        $tmpArr_2["color"] = $tableName_val;
        $tmpArr_2[$content] = 0;
      }
      $tmpArr[$tableName_key] = $tmpArr_2;
    }
    $barsDataArr[$time_arr[$j]] = $tmpArr;
  }  
  $tmp_tital = "All Machines";
}elseif(!$tableName && $show_all == 'all'){
  for($j=0; $j<count($time_arr);$j++){
    $tmpArr = array();
    $sub_total_val = 0;
    foreach($tableName_arr as $table_key => $table_color){
      if(isset($matrix_arr[$table_key][ $time_arr[$j]])){
        $content_value = $matrix_arr[$table_key][ $time_arr[$j]][$content];
        if(!$content_value) $content_value = 0;
        $sub_total_val += $content_value;
      }
    }
    if($biggest_value < $sub_total_val) $biggest_value = $sub_total_val;
    $total_value += $sub_total_val;
    $tmpArr['All Machines']['color'] = $bar_color;
    $tmpArr['All Machines'][$content] = $sub_total_val;
    $barsDataArr[$time_arr[$j]] = $tmpArr;
  }
  $tmp_tital = "All Machines";
}elseif($tableName){
  for($j=0; $j<count($time_arr);$j++){
  //foreach($time_arr as $time_val){
    $tmpArr = array();
    $tmpArr[$tableName][$content] = 0;
    $tmpArr[$tableName]['color'] = $bar_color;
    if(isset($matrix_arr[$tableName][$time_arr[$j]])){
      $content_value = $matrix_arr[$tableName][$time_arr[$j]][$content];
      if(!$content_value) $content_value = 0;
      $tmpArr[$tableName][$content] = $content_value;
      $total_value += $content_value;
      if($biggest_value < $content_value) $biggest_value = $content_value;
    }
    $barsDataArr[$time_arr[$j]] = $tmpArr;
  }
  $tmp_tital = "$tableName";
}else{
  echo "^^^^^^^^^^^^";exit;
}

/*echo $total_value."<br>";
echo $biggest_value."<br>";   
echo "<pre>";
print_r($barsDataArr);
echo "<pre>";exit;*/

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

$graph_width = $imageWidth;

$left_magin = 20;
$top_magin = 20;
$bottem_magin = 10;
$tital_spacing =20;
$date_lable_len = $lable_font_width * 5;


$grid_height = 20;
$grid_width = $lable_font_width * 7;
$value_str_width = $lable_font_width * 6;
$grid_v_num = 11;
$grid_h_num = count($time_arr) - 1;
$sub_lable_heighth = 0;

if(!$tableName && $show_single == 'single') $sub_lable_heighth = $lable_font_heighth * 3;

$graph_height = $top_magin + $title_font_b_heighth + $tital_spacing + $grid_height * $grid_v_num + $lable_font_heighth + $sub_lable_heighth + $bottem_magin;
$graph_width = $left_magin * 2 + $grid_width * $grid_h_num + $value_str_width;
$graph_width_min = (strlen($size_unit) + strlen($tital)) * $title_font_b_width + $left_magin*4;
if($graph_width < $graph_width_min) $graph_width = $graph_width_min;

$tmp_big_space = 10;
$tmp_small_space = 2;
$tmp_line = 0;
if(!$tableName && $show_single == 'single'){
  $tmp_line = 0;
  $sub_lable_start_x = $left_magin;
  $sub_lable_start_y = $graph_height - $lable_font_heighth*2 - $bottem_magin;
  $sub_lable_heighth = $lable_font_heighth * 3;
  $x = $sub_lable_start_x;
  $y = $sub_lable_start_y;
  foreach($tableName_arr as $tableName_key => $tableName_val){
    $tmp_str = $tableName_key."---";
    $x = $x + strlen($tmp_str)*$lable_font_width + $tmp_big_space;
    if($x > $graph_width - $left_magin*5){
      $x = $sub_lable_start_x;
      $tmp_line++;
    }
  }
  $graph_height += $tmp_line*$lable_font_heighth;
}

/*if($graph_width < $imageWidth){
  $grid_width = round(($imageWidth - $left_magin * 2 - $lable_font_width * 6) / $grid_h_num);
  $graph_width = $left_magin * 2 + $grid_width * $grid_h_num + $lable_font_width * 6;
}*/

$aa = ($grid_height*10)/$biggest_value;

if(!$tableName && $show_single == 'single'){
  foreach($tableName_arr as $tableName_key => $tableName_val){ 
    for($j=0; $j<count($time_arr);$j++){
      $barsDataArr[$time_arr[$j]][$tableName_key]['y_start'] = round($barsDataArr[$time_arr[$j]][$tableName_key][$content] * $aa,3);
      if(isset($time_arr[$j+1])){
        $barsDataArr[$time_arr[$j]][$tableName_key]['y_end'] = round($barsDataArr[$time_arr[$j+1]][$tableName_key][$content] * $aa,3);
      }else{
        $barsDataArr[$time_arr[$j]][$tableName_key]['y_end'] = 'end';
      }
    }
  }  
}elseif(!$tableName && $show_all == 'all'){
  for($j=0; $j<count($time_arr);$j++){
    $barsDataArr[$time_arr[$j]]['All Machines']['y_start'] = round($barsDataArr[$time_arr[$j]]['All Machines'][$content] * $aa,3);
    if(isset($time_arr[$j+1])){
      $barsDataArr[$time_arr[$j]]['All Machines']['y_end'] = round($barsDataArr[$time_arr[$j+1]]['All Machines'][$content] * $aa,3);
    }else{
      $barsDataArr[$time_arr[$j]]['All Machines']['y_end'] = 'end';
    }
  }
}elseif($tableName){
  for($j=0; $j<count($time_arr);$j++){
    $barsDataArr[$time_arr[$j]][$tableName]['y_start'] = round($barsDataArr[$time_arr[$j]][$tableName][$content] * $aa,3);
    if(isset($time_arr[$j+1])){
      $barsDataArr[$time_arr[$j]][$tableName]['y_end'] = round($barsDataArr[$time_arr[$j+1]][$tableName][$content] * $aa,3);
    }else{
      $barsDataArr[$time_arr[$j]][$tableName]['y_end'] = 'end';
    }
  }
}

$graph = ImageCreate($graph_width,$graph_height);

$x_position_arr = array();
$x_value_arr = array();
$h_line_start = $left_magin + $value_str_width;

for($i=0; $i<count($time_arr); $i++){
  $x_position_arr[$i] = $h_line_start + $i*$grid_width;
  $x_position_arr_2[$time_arr[$i]]['x_start'] = $h_line_start + $i*$grid_width;
  if(isset($time_arr[$i+1])){
    $x_position_arr_2[$time_arr[$i]]['x_end'] = $h_line_start + ($i+1)*$grid_width;
  }else{
    $x_position_arr_2[$time_arr[$i]]['x_end'] = 'end';
  }
  if($interval == 'yearly'){
    $x_value_arr[$i] = $time_arr[$i];
  }else{
    $x_value_arr[$i] = substr($time_arr[$i], 2);
  }  
}

$date_lable_len = strlen($x_value_arr[0]);
$date_lable_width = $lable_font_width * $date_lable_len;


if($biggest_value <= 1){
  $left_num = 3;
}elseif($biggest_value <= 10){
  $left_num = 2;
}elseif($biggest_value <= 100){
  $left_num = 1;
}else{
  $left_num = 0;
}
$y_position_arr = array();
$y_value_arr = array();
$v_line_start = $top_magin + $title_font_b_heighth + $tital_spacing;

for($i=0; $i<=$grid_v_num; $i++){
  $y_position_arr[$i] = $v_line_start + $i*$grid_height;
  $y_value_arr[$i] = round(($biggest_value/10)*$i,$left_num);
}

rsort($y_value_arr);
/*echo "<pre>";
echo "\$x_position_arr";
print_r($x_position_arr);
echo "\$x_value_arr";
print_r($x_value_arr);
echo "\$y_position_arr";
print_r($y_position_arr);
echo "\$y_value_arr";
print_r($y_value_arr);
echo "<pre>";exit;*/

$biggest_y_position = end($y_position_arr);

$white = ImageColorAllocate($graph,255,255,255);
$black = ImageColorAllocate($graph,0,0,0);       
$red = ImageColorAllocate($graph,255,85,75);      
$green = ImageColorAllocate($graph,57,255,35);
$blue = ImageColorAllocate($graph,72,141,255);
$light_blue = ImageColorAllocate($graph,202,202,255);

ImageRectangle($graph, 0, 0, $graph_width-1, $graph_height-1,$blue);

$tital_start_x = round(($graph_width - strlen($tital)*$title_font_b_width)/2);
$tital_start_y = $top_magin;
imagestring($graph, $title_font_b, $tital_start_x,$tital_start_y, $tital, $blue); 

$h_start = $x_position_arr[0];
$h_end = end($x_position_arr);

//print_r($y_position_arr);exit;

foreach($y_position_arr as $y_position){
  imageline($graph, $h_start, $y_position, $h_end, $y_position, $light_blue);
}
$v_start = $y_position_arr[0];
$v_end = end($y_position_arr);
foreach($x_position_arr as $x_position){
  imageline($graph, $x_position, $v_start, $x_position, $v_end, $light_blue);
}

$date_lable_y_start = end($y_position_arr) + 2;
for($i=0; $i<count($x_position_arr); $i++){
  $date_lable_x_start = round($x_position_arr[$i] - $date_lable_width/2);
  imagestring($graph, $lable_font, $date_lable_x_start, $date_lable_y_start, $x_value_arr[$i], $blue);
}

$value_lable_x_start = $left_magin;
for($i=0; $i<count($y_position_arr); $i++){
  $value_lable_y_start = round($y_position_arr[$i] - ($lable_font_heighth/2));
  imagestring($graph, $lable_font, $value_lable_x_start, $value_lable_y_start, $y_value_arr[$i], $blue);
}

/*echo "<pre>";
print_r($barsDataArr);
print_r($x_position_arr_2);
echo "<pre>";exit;*/
$m_color_arr = array();
foreach($tableName_arr as $tableName_key => $tableName_val){
  $m_color_arr[$tableName_key] = get_im_color($tableName_val);
}
//print_r($m_color_arr);exit;
if(!$tableName && $show_single == 'single'){
  if(count($barsDataArr) == 1){
    $x_start = round($x_position_arr_2[$time_arr[0]]['x_start']);
    $x_end = $x_start;
    foreach($tableName_arr as $tableName_key => $tableName_val){
      $y_start = round($barsDataArr[$time_arr[0]][$tableName_key]['y_start']);
      $y_end = 0;
      imageline($graph, $x_start, $biggest_y_position-$y_start, $x_end, $biggest_y_position-$y_end, $m_color_arr[$tableName_key]);
    } 
  }else{
    for($i=0; $i<count($time_arr)-1; $i++){
      $x_start = round($x_position_arr_2[$time_arr[$i]]['x_start']);
      $x_end = round($x_position_arr_2[$time_arr[$i]]['x_end']);
      foreach($tableName_arr as $tableName_key => $tableName_val){
        $y_start = round($barsDataArr[$time_arr[$i]][$tableName_key]['y_start']);
        $y_end = round($barsDataArr[$time_arr[$i]][$tableName_key]['y_end']);
        imageline($graph, $x_start, $biggest_y_position-$y_start, $x_end, $biggest_y_position-$y_end, $m_color_arr[$tableName_key]);
      } 
    }
  }   
}elseif(!$tableName && $show_all == 'all'){
  $color_bar = get_im_color($bar_color);
  if(count($barsDataArr) == 1){
    $x_start = round($x_position_arr_2[$time_arr[0]]['x_start']);
    $x_end = $x_start;
    $y_start = round($barsDataArr[$time_arr[0]]['All Machines']['y_start']);
    $y_end = 0;
    imageline($graph, $x_start, $biggest_y_position-$y_start, $x_end, $biggest_y_position-$y_end, $color_bar);
  }else{
    for($i=0; $i<count($time_arr)-1; $i++){
      $x_start = round($x_position_arr_2[$time_arr[$i]]['x_start']);
      $x_end = round($x_position_arr_2[$time_arr[$i]]['x_end']);
      $y_start = round($barsDataArr[$time_arr[$i]]['All Machines']['y_start']);
      $y_end = round($barsDataArr[$time_arr[$i]]['All Machines']['y_end']);
      imageline($graph, $x_start, $biggest_y_position-$y_start, $x_end, $biggest_y_position-$y_end, $color_bar);
    }
  }      
}elseif($tableName){
  $color_bar = get_im_color($bar_color);
  if(count($barsDataArr) == 1){
    $x_start = round($x_position_arr_2[$time_arr[0]]['x_start']);
    $x_end = $x_start;
    $y_start = round($barsDataArr[$time_arr[0]][$tableName]['y_start']);
    $y_end = 0;
    imageline($graph, $x_start, $biggest_y_position-$y_start, $x_end, $biggest_y_position-$y_end, $color_bar);
  }else{
    for($i=0; $i<count($time_arr)-1; $i++){
      $x_start = round($x_position_arr_2[$time_arr[$i]]['x_start']);
      $x_end = round($x_position_arr_2[$time_arr[$i]]['x_end']);
      $y_start = round($barsDataArr[$time_arr[$i]][$tableName]['y_start']);
      $y_end = round($barsDataArr[$time_arr[$i]][$tableName]['y_end']);
      imageline($graph, $x_start, $biggest_y_position-$y_start, $x_end, $biggest_y_position-$y_end, $color_bar);
      //imageBoldLine($graph, $x_start, $biggest_y_position-$y_start, $x_end, $biggest_y_position-$y_end, $color_bar,1);
    }
  }      
}

if(!$tableName && $show_single == 'single'){
  $sub_lable_start_x = $left_magin;
  $sub_lable_start_y = $graph_height - $lable_font_heighth*($tmp_line+2) - $bottem_magin;
  $sub_lable_heighth = $lable_font_heighth * 3;
  $x = $sub_lable_start_x;
  $y = $sub_lable_start_y;
  foreach($tableName_arr as $tableName_key => $tableName_val){
    $tmp_str = $tableName_key."---";
    imagestring($graph, $lable_font, $x, $y, $tmp_str, $m_color_arr[$tableName_key]);
    $x = $x + strlen($tmp_str)*$lable_font_width + $tmp_big_space;
    if($x > $graph_width - $left_magin*5){
      $x = $sub_lable_start_x;
      $y = $sub_lable_start_y + $lable_font_heighth;
    }
  }
}
if($size_unit){
  $b_uni_start_x = $left_magin;
  $b_uni_start_y = $top_magin;
  imagestring($graph, $lable_font, $b_uni_start_x, $b_uni_start_y, $size_unit, $blue);
}

header("Content-type: image/jpeg");
ImagePNG($graph);
ImageDestroy($graph);
exit;

function get_im_color($in_color){
  global $graph;
  $colorString = '';
  if(substr($in_color, 0, 1) == '#'){
    $colorString = substr($in_color, 1, 6); 
  }else{
    $colorString = $in_color;
  }
  $r = substr($colorString, 0, 2);
  $g = substr($colorString, 2, 2);
  $b = substr($colorString, 4, 2); 
  $r = hexdec("0x{$r}");
  $g = hexdec("0x{$g}");
  $b = hexdec("0x{$b}"); 
  $color = ImageColorAllocate($graph, $r, $g, $b);
  return $color;
}
function imageBoldLine($resource, $x1, $y1, $x2, $y2, $Color, $BoldNess=2, $func='imageLine'){
  $x1 -= ($buf=ceil(($BoldNess-1) /2));
  $x2 -= $buf;
  for($i=0;$i < $BoldNess;++$i)
    $func($resource, $x1 +$i, $y1, $x2 +$i, $y2, $Color);
}

?>