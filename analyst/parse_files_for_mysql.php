<?php 
$theaction = '';
require("../common/site_permission.inc.php");
include("analyst/common_functions.inc.php");
ini_set("memory_limit","-1");


$RAW_DATA_PATH = "./";

if(is_dir($RAW_DATA_PATH)){
  $raw_data_arr = scandir($RAW_DATA_PATH);
}else{
  $raw_data_arr = array();
  array_push($raw_data_arr, '');
}
$queue = array();
foreach($raw_data_arr as $raw_data_val){
  if($raw_data_val == '.' || $raw_data_val == '..') continue;
  $item = $RAW_DATA_PATH.$raw_data_val;
  array_push($queue, $item);
}
$fu_names = array();
$count = 0;

while(count($queue)){
  $firstItem = array_pop($queue);
  if(is_dir($firstItem)){ 
    $raw_data_arr = scandir($firstItem);
    foreach($raw_data_arr as $raw_data_val){
      if($raw_data_val == '.' || $raw_data_val == '..') continue;
      $item = $firstItem.'/'.$raw_data_val;
      array_push($queue, $item);
    }
  }elseif(is_file($firstItem)){
    if(!preg_match("/\.php$/i", $firstItem)) continue;
    if(preg_match("/mysqlDB_class\.php$/i", $firstItem)){
      continue;
    } //continue;
    
    $handle = @fopen($firstItem, "r");
    if($handle){

      $flag = 1;
      $tmp_arr = array();
      $class_arr = array();
$line_num = 0;
      while(($buffer = fgets($handle, 4096)) !== false){
$line_num++;
        $buffer = trim($buffer);
        if(preg_match("/((mysql_\w+)\(.+\))/i",$buffer, $matches)){
          if(!in_array($matches[2], $fu_names)){
            array_push($fu_names, $matches[2]);
          }
          echo $line_num."&nbsp;&nbsp;&nbsp;".$matches[1]."<br>";
          if($matches[2] == 'mysql_fetch_object'){
            $buffer = str_replace("mysql_fetch_object", "mysqli_fetch_object", $buffer);
            echo $buffer."<br>";
          }
          
          $count++;
          if($flag){
            $flag = 0;
          }
        }elseif(preg_match("/.+new\s*mysqlDB.+/i",$buffer, $matches)){
          array_push($tmp_arr, $matches[0]."  $line_num");
          echo $line_num.":&nbsp;&nbsp;&nbsp;".$matches[0]."<br>";

        }elseif(preg_match("/.+new\s+\w+\(.+\)/",$buffer, $matches)){
          array_push($class_arr, $matches[0]."  $line_num");
          echo $line_num.":&nbsp;&nbsp;&nbsp;".$matches[0]."<br>";
        }
      }
      if(!feof($handle)){
          echo "Error: unexpected fgets() fail\n";
      }
      fclose($handle);
    }
  }
}
?>
   
