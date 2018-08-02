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
$total = 0;

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;  
}

header("Content-type: image/jpeg");

$currentSetArr = $_SESSION["currentSetArr"];
unset($_SESSION["currentSetArr"]);

$graph_width=400;                     //Width of entire graph
$graph_height=165;                    //Height of entire graph
$left_title="Total Hits: " . $total;  //Y-axis title
$highest = 0;

foreach($currentSetArr as $Value){
  if($Value['Counter'] > $highest){
    $highest = $Value['Counter'];
  } 
}

function PrintGraph($bar_data) {
 
  global $graph_width,$graph_height,$graph_padding,$left_title;
  // Create initial image 
  $graph=ImageCreate($graph_width, $graph_height);  
  $white = ImageColorAllocate($graph, 255, 255, 255);  
  $black = ImageColorAllocate($graph, 0, 0, 0); 
  $con = count($bar_data);
    
 
  $box_top_start = 40;
  $box_top_end = $graph_height - 25;
  $current_width = 25 * $con;
  
  $box_left_start = $graph_width - $current_width;
  $graph_padding = $box_left_start -16;
  if($current_width > $graph_width - 25){
    $box_left_start = 25;
    $graph_padding = 9;
  }
  $box_left_end=$graph_width;
  ImageLine($graph, $box_left_start, $box_top_end, $box_left_end, $box_top_end, $black); 
   
  BottomValues($graph,$bar_data,$box_left_start,$box_top_start,$box_left_end,$box_top_end,$black);
  GraphBars($graph,$bar_data,$box_left_start,$box_top_start,$box_left_end,$box_top_end,$black);   
  ImageStringUp($graph, 3, $graph_padding, $graph_height/2+strlen($left_title)*3, $left_title, $black); 
  ImagePNG($graph);
  ImageDestroy($graph);
}

function GraphBars($image,$bardata,$box_left_start,$box_top_start,$box_left_end,$box_top_end,$black){
  global $highest;
  $count=count($bardata);
  $left = 0;
  foreach($bardata as $value){    
    if(!$left){ 
      $left=$box_left_start; 
    }else{
      $left=round((($box_left_end-$box_left_start)/$count),2)+$left;
    }
    $bar_height= 0;
    if($highest){
      $bar_height=(($box_top_end-$box_top_start)*$value['Counter'])/$highest;
    }
    
    $mywidth=(.6*($box_left_end-$box_left_start)/$count)/2;
     
    $colorString = '';
    if(substr($value['Color'], 0, 1) == '#'){
      $colorString = substr($value['Color'], 1, 6); 
    }else{
      $colorString = $value['Color'];
    }
    $r = substr($colorString, 0, 2);
    $g = substr($colorString, 2, 2);
    $b = substr($colorString, 4, 2); 
    
    $r = hexdec("0x{$r}");
    $g = hexdec("0x{$g}");
    $b = hexdec("0x{$b}"); 

    $color = ImageColorAllocate($image, $r, $g, $b);
    ImageFilledRectangle($image, $left+((($box_left_end-$box_left_start)/$count)/2)-$mywidth, $box_top_end-$bar_height, $left+((($box_left_end-$box_left_start)/$count)/2)+$mywidth, $box_top_end, $color);
    $start1=$left+((($box_left_end-$box_left_start)/$count)/2)-$mywidth;
    $start2=$box_top_end-$bar_height-13;
    ImageString($image, 2, $start1, $start2, $value['Counter'], $black);
  }
}

function BottomValues($image,$bardata,$box_left_start,$box_top_start,$box_left_end,$box_top_end,$color) {
  $count=count($bardata);
  $left = 0;
  $AliasLen = 0;
  foreach($bardata as $value){
    if(strlen($value['Alias']) > $AliasLen){
      $AliasLen = strlen($value['Alias']);
    }
  }
  foreach($bardata as $value){
    $Alias = $value['Alias'];    
    if(!$left){ 
      $left=$box_left_start; 
    }else{
      $left=round((($box_left_end-$box_left_start)/$count),2)+$left;
    }
    ImageString($image, 2, $left+((($box_left_end-$box_left_start)/$count)/2)-5, $box_top_end+$AliasLen*3, $Alias, $color);
  }
}

printgraph($currentSetArr);
?>