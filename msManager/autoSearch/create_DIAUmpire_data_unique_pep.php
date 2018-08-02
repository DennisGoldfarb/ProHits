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
//Set $uniquePEP=1 will remove shared peptides by different genes. It will not remove shared peptide from only same gene. 
//the gene ID can be parsed from Protein ID string or from Prohits Protein db.

/*
//--------------------------------------------------------------------------------------------------------------
$DIAUmpire_file_dir = "../../TMP/TEST/";
//$fileInput = $DIAUmpire_file_dir."FragSummary_201511301005.xls";
$fileInput = $DIAUmpire_file_dir."frank.txt";
$sampleOrder_str = "21921|pea1_con,22227|pea1_con,22438|pea1_con,21941|pea1_cis,22241|pea1_cis,22442|pea1_cis,21945|pea2_con,22245|pea2_con,22454|pea2_con,21949|pea2_cis,22249|pea2_cis,22458|pea2_cis";
$input_dir = dirname($fileInput);
$fileOutput = $input_dir."/test_new.txt";
//---------------------------------------------------------------------------------------------------------------
*/
$uniquePEP = 0;
if(isset($_SERVER['argv']) and count($_SERVER['argv']) > 1){
  $fileInput       = $_SERVER['argv'][1];
  $sampleOrder_str = $_SERVER['argv'][2];
  $fileOutput      = $_SERVER['argv'][3];
  $uniquePEP       = $_SERVER['argv'][4];
}else{
  echo "usage: php ".__FILE__." 'FragSummary_input_file' 'SAINT_bait_name_str' 'output_file_name' 'uniquePEP'";
  echo "\nWorking dir should be the input file dir\n";
  echo "uniquePEP = 0/1/";
  exit;
}
$unique_peptide_arr = array();
//--------------------------------------------------------------------------
if($uniquePEP){
  $input_handle_tmp = fopen($fileInput, "r");
  if(!$input_handle_tmp){
    echo "Cannot open file $fileInput";
    exit;
  }
  $tmp_flag = 0;
  
  while(!feof($input_handle_tmp)){
    $buffer = fgets($input_handle_tmp);
    if(!$tmp_flag){
      $tmp_flag = 1;
      continue;
    }
    $tmp_arr = explode("\t", $buffer);
    $gene = '';
    preg_match('/gn|.+?:(\d+)|$/', $tmp_arr[1], $matches);
    if(isset($matches[1])){
      $gene = $matches[1];
    }else{
      continue;
      //--get gene from prohits protein db.
    }
    if(!array_key_exists($tmp_arr[2],$unique_peptide_arr)){
      $unique_peptide_arr[$tmp_arr[2]] = $gene;
    }else{
      if($unique_peptide_arr[$tmp_arr[2]] && $unique_peptide_arr[$tmp_arr[2]] != $gene){
        $unique_peptide_arr[$tmp_arr[2]] = '';
      }
    }
  }
  fclose($input_handle_tmp);
}
//---------------------------------------------------------------------------------------  

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


$input_handle = fopen($fileInput, "r");
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
$FILE_ARR = array();

$Peptide_arr = array();

while(!feof($input_handle)){
  $buffer = fgets($input_handle);
  //$buffer = trim($buffer);
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
//-------------------------------------------------------------------  
    if($uniquePEP){
      if(!$unique_peptide_arr[$tmp_arr[2]]) continue;
    }
//------------------------------------------------------------------- 
    $line_count++;
    $line = '';
    foreach($index_arr as $index_val){
      $line .= "\t".$tmp_arr[$index_val];
    }
    $RT_counter = 0;
    $RT_total = 0;
    foreach($RT_index_arr as $RT_index_val){
      if($tmp_arr[$RT_index_val]){
        $RT_counter++;
        $RT_total += $tmp_arr[$RT_index_val];
      }
    }
    if($RT_total){
      $line .= "\t".round($RT_total/$RT_counter,6);
    }else{
      $line .= "\t";
    }
    
    $not_empty = trim($line);
    //if(!$not_empty) continue;
    $static_line = '';
    foreach($static_index_arr as $static_index_val){
      if($static_line) $static_line .= "\t";
      $static_line .= $tmp_arr[$static_index_val];
    }
    $line = $static_line.$line;
    fwrite($output_handle, $line."\r\n");
    /*
    $KEY = $tmp_arr[2];
    if(!array_key_exists($KEY, $FILE_ARR)){
      $FILE_ARR[$KEY] = array();
    }
    $FILE_ARR[$KEY][] = $line;
    */
  }
  //if($line_count > 1000) break;  
}
fclose($input_handle);
fclose($output_handle);
/*
foreach($FILE_ARR as $peptide_arr){
  foreach($peptide_arr as $peptide_val){
    fwrite($output_handle, $peptide_val."\r\n");
  }
}
*/
echo "file $fileOutput was created.\n";
?>
