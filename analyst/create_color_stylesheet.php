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

$inFilename = "colorPicker.js";
if(!$handle = fopen($inFilename, 'r')){
  echo "Cannot open file ($inFilename)";
  exit;
}
$outFilename = "colorPicker_style.css";
if(!$out_handle = fopen($outFilename, 'w')){
  echo "Cannot open file ($outFilename)";
  exit;
}
$flag = 0;
$colorNumStr = '';
while(!feof($handle)){
  $buffer = fgets($handle, 4096);
  if(preg_match('/var\s*colors\s*=\s*new\s*Array/i', $buffer)){
    $flag = 1;
  }
  if($flag && preg_match('/\)\s*;/', $buffer)){    
    $tmpArr = preg_split('/\"\s*\)/', $buffer);
    $buffer = $tmpArr[0];
    $colorNumStr .= trim($buffer);
    $flag = 0;
  }
  if($flag){
    $colorNumStr .= trim($buffer);
    //echo $buffer;
  }
}
//echo "</br></br>".$colorNumStr."<br>";
fclose($handle);
$colorNumArr = explode('","#',$colorNumStr);
//echo count($colorNumArr)."<br>";
$tmpArr = explode('#', $colorNumArr[0]);
$colorNumArr[0] = $tmpArr[1];
//echo $colorNumArr[0];
foreach($colorNumArr as $value){
  fwrite($out_handle, ".C_$value { BACKGROUND-COLOR: #$value }\r\n");
  //echo ".C_$value { BACKGROUND-COLOR: #$value }<br>";
}

              
?>              
              