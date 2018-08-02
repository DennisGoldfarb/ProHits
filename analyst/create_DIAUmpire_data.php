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

ini_set("memory_limit","2000M");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//--------------------------------------------------------------------------------------------------------------
$DIAUmpire_file_dir = "../TMP/TEST/";
$fileInput = $DIAUmpire_file_dir."FragSummary_201501151404.xls";
$sampleOrder_str = "40411|0min,40501|0min,40427|10min,40483|10min,40423|1hr,40509|1hr,40437|4hr,40491|4hr";
$input_dir = dirname($fileInput);
$fileOutput = $input_dir."/test.txt";
//---------------------------------------------------------------------------------------------------------------

$sampleOrder_arr = explode(',',$sampleOrder_str);
$static_index_arr = array('Protein'=>'','Peptide'=>'','Fragment'=>'');

foreach($sampleOrder_arr as $sampleOrder_val){
  $tmp_arr = explode('|',$sampleOrder_val);
  $index_arr[$tmp_arr[0]] = '';
}

$title_str = "";
foreach($static_index_arr as $key => $val){
  $title_str .= $key."\t";
}
$title_tmp = str_replace("|", "_", $sampleOrder_str);
$title_tmp = str_replace(",", "\t", $title_tmp);
$title_str .= $title_tmp."\tRT";


$input_handle = @fopen($fileInput, "r");
if(!$input_handle){
  echo "Cannot open file $fileInput";
  exit;
}
$output_handle = @fopen($fileOutput, "w");
if(!$output_handle){
  echo "Cannot open file $fileOutput";
  exit;
}
$title_flag = 1;
$RT_index_arr = array();
fwrite($output_handle, $title_str."\r\n");

$line_count = 0;
$line_num = 0;
while(!feof($input_handle)){
  $line_num++; 
  if($line_num%900 === 0){
    echo '.';
    if($line_num%4000 === 0)  echo "$line_num\n";
    flush();
    ob_flush();
  }
  $buffer = fgets($input_handle);
  if(!$buffer) continue;
  $tmp_arr = explode("\t", $buffer);
  trim(end($tmp_arr));
  if($title_flag){
    $title_flag = 0;
    foreach($tmp_arr as $tmp_key => $tmp_val){
      if(array_key_exists($tmp_val, $static_index_arr)){
        $static_index_arr[$tmp_val] = $tmp_key;
      }
      if(preg_match("/^(\d+)_.+?_Intensity$/i",$tmp_val,$matches)){
        if(array_key_exists($matches[1], $index_arr)){
          $index_arr[$matches[1]] = $tmp_key;
        }
      }
      if(preg_match("/^(\d+)_.+?_RT$/",$tmp_val,$matches)){
        if(array_key_exists($matches[1], $index_arr)){
          $RT_index_arr[] = $tmp_key;
        }
      }  
    }
  }else{
    $line_count++;
    $line = '';
    foreach($index_arr as $index_val){
      $line .= "\t".$tmp_arr[$index_val];
    }
    
    $not_empty = trim($line);
    if(!$not_empty) continue;
    $static_line = '';
    foreach($static_index_arr as $static_index_val){
      if($static_line) $static_line .= "\t";
      $static_line .= $tmp_arr[$static_index_val];
    }
    $line = $static_line.$line;
    
    $RT_counter = 0;
    $RT_total = 0;
    foreach($RT_index_arr as $RT_index_val){
      if($tmp_arr[$RT_index_val]){
        $RT_counter++;
        $RT_total += $tmp_arr[$RT_index_val];
      }
    }
//-----------------------------------------------------------
    $line .= "\t".round($RT_total/$RT_counter,6)."\r\n";
    fwrite($output_handle, $line);
//-----------------------------------------------------------    
  }
  //if($line_count > 100) break;  
}
fclose($input_handle);
fclose($output_handle);
echo "Success";
?>
