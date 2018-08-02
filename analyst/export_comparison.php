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

require("../common/site_permission.inc.php");
require_once("msManager/is_dir_file.inc.php");

if(!$infileName || !$spliter){
  echo "no input file or no delimit.";
  exit;
}
$filename_in = $infileName;
$outDir = "../TMP/comparison/";
if(!_is_dir($outDir)) _mkdir_path($outDir);
$filename_out = $outDir.$_SESSION['USER']->Username."_comparison.csv";

$handle_write = fopen($filename_out, "w");
$handle_read = fopen($filename_in, "r");
$groupNames = '';
$emptyCell = "                ";
if($handle_read){
  while (!feof($handle_read)){
    $buffer = fgets($handle_read, 4096);
    $buffer = trim($buffer);
    if(preg_match ('/^Total samples:(\d+)/', $buffer, $matches)){
      $totalSamples = $matches[1];
      $description = "Comment: m -- Mascot hit. g -- GPM hit.";
      $baitCells = str_repeat($emptyCell, $totalSamples-1);
      $baitlimit = str_repeat(',', $totalSamples);
      fwrite($handle_write, $description . $baitCells . $baitlimit .  "\r\n");
    }elseif(preg_match ('/^Total GIs:(\d+)/', $buffer, $matches)){
      $totalGIs = $matches[1];
    }elseif(preg_match ('/^Sample Names:(.+)/', $buffer, $matches)){
      $sampleNames = str_replace($spliter, ",", $matches[1]);
      $sampleNames .= ',Hit Gene Name,Hit Protein ID';
    }elseif(preg_match ('/^Group Names:(.+)/', $buffer, $matches)){
      $groupNames = str_replace($spliter, ",", $matches[1]);
      $groupNames .= ',';  
    }elseif(preg_match ('/^titals:(.+)/', $buffer, $matches)){
      $baitInfoArr = explode("##", $matches[1]);
      $expInforArr = array();
      foreach($baitInfoArr as $baitInfoValue){
        $tmpArr = explode("%%", $baitInfoValue);
        if(preg_match ('/^(.+\))(\d+)/', $tmpArr[0], $matches)){
          $baitName = $matches[1];
          if(!$matches[2]) continue;
          $totalEmpCells = str_repeat($emptyCell, $matches[2]-1);
          $totaldelimit = str_repeat(',', $matches[2]);
          fwrite($handle_write, $baitName . $totalEmpCells . $totaldelimit);
          $tmpArr2 = explode(";;", $tmpArr[1]);
          foreach($tmpArr2 as $tmpArr2Value){
            array_push($expInforArr, $tmpArr2Value);
          }
        }  
      }
      fwrite($handle_write, "".$emptyCell.",");
      fwrite($handle_write, "\r\n");
      foreach($expInforArr as $expArrValue){
        $tmpArr3 = explode(",,",$expArrValue);
        $expName = "Experiment: " . $tmpArr3[0];
        if(!$tmpArr3[1]) continue;
        $expCells = str_repeat($emptyCell, $tmpArr3[1]-1);
        $expdelimit = str_repeat(',', $tmpArr3[1]);
        fwrite($handle_write, $expName . $expCells . $expdelimit);
      }
      fwrite($handle_write, $emptyCell .',');
      fwrite($handle_write, "\r\n");
      fwrite($handle_write, $sampleNames . "\r\n");
      fwrite($handle_write, $groupNames . "\r\n");
    }elseif(preg_match('/^GI list:/', $buffer) || preg_match('/^Background:/', $buffer)){
      // do nothing.
    }elseif(preg_match('/^\w+,,(.+),\d+$/', $buffer, $matches)){
      $giLine = str_replace("h#", "", $matches[1]);
      $giLine = str_replace("@", " ", $giLine);
      //-------------------------------------------
      /*$giLine = preg_replace("/h#m:[\d|;]+@g:[\d|;]+,/", "mg,", $matches[1]);
      $giLine = preg_replace("/h#m:[\d|;]+,/", "m,", $giLine);
      $giLine = preg_replace("/h#g:[\d|;]+,/", "g,", $giLine);*/
      //-------------------------------------------
      $giLine = str_replace($spliter, ",", $giLine);
      fwrite($handle_write, $giLine."\r\n");
    }
  }
  fclose($handle_read);
  fclose($handle_write);
}

if(_is_file($filename_out)){
  header("Cache-Control: public, must-revalidate");
  //header("Pragma: hack");
  header("Content-Type: application/octet-stream");  //download-to-disk dialog
  header("Content-Disposition: attachment; filename=".basename($filename_out).";" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: "._filesize($filename_out));
  readfile("$filename_out");
  exit();
}
?>