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
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
$reportDir = "../TMP/bait-prey/";
if(!_is_dir($reportDir)) _mkdir_path($reportDir);

$SQL = "SELECT `GeneID`,`LocusTag`,`GeneName`,`ID` FROM `Bait` WHERE `ProjectID`=22";
$BaitArr = $HITSDB->fetchAll($SQL);
echo count($BaitArr)."<br>";
$counter = 0;
$file_header = "Bait ID,Bait Gene ID,Bait Gene Name,Bait LocusTag,Hit ID,Hit Gene ID,Hit Gene Name,Hit LocusTag,Hit Protein ID,Hit Score,Peptide Number,Experiment ID\r\n";
foreach($BaitArr as $BaitProperty){
  $open_file_flag = 0;
  $filename_out = $reportDir."bait_prey_".$BaitProperty['ID'].".csv";
  $SQL ="SELECT `ID` FROM `Experiment` WHERE `BaitID`='".$BaitProperty['ID']."'";
  $expArr = $HITSDB->fetchAll($SQL);
  if(!$expArr) continue;
  $BansStrArr = array();
  foreach($expArr as $expID){
    $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID`='".$expID['ID']."' ORDER BY ID";
    $bandsArr = $HITSDB->fetchAll($SQL);
    $BandStr = '';
    foreach($bandsArr as $bandID){
      if($BandStr) $BandStr .= ",";
      $BandStr .= $bandID['ID'];
    }
    if($BandStr) $BansStrArr[$expID['ID']] = $BandStr;
  }
  if(!$BansStrArr) continue;
$hitsCounter = 0;
  foreach($BansStrArr as $expID => $BandsStr){
    $SQL = "SELECT GeneID,
                   LocusTag,
                   ID, 
                   HitGI,
                   Expect
                   FROM Hits
                   WHERE BandID IN (".$BandsStr.")
                   AND Expect !=0
                   ORDER BY LocusTag DESC,Expect DESC";
    $HitArr = $HITSDB->fetchAll($SQL);
    if(!$HitArr) continue;
    
    if(!$open_file_flag){
      if(!$handle = fopen($filename_out, "w")){
        echo "cannot open file $filename_out";
        continue;
      }
      fwrite($handle, $file_header);
      $open_file_flag = 1;
    }  
    $tmpArr2 = array();
  
    foreach($HitArr as $hitProperty){
      if(in_array($hitProperty['LocusTag'], $tmpArr2)) continue;
      array_push($tmpArr2, $hitProperty['LocusTag']);
      $SQL ="SELECT `GeneName` FROM `Protein_Class` WHERE `EntrezGeneID`='".$hitProperty['GeneID']."'";
      $proteinArr = $proteinDB->fetch($SQL);
      $hitGeneName = '';
      if($proteinArr && $proteinArr['GeneName']) $hitGeneName = $proteinArr['GeneName'];
      
      $SQL = "SELECT count(HitID) AS PeptideNum
              FROM Peptide 
              WHERE HitID ='".$hitProperty['ID']."'
              GROUP BY HitID";
      $tmpArr = $HITSDB->fetch($SQL);
      $peptideNum = '';
      if($tmpArr && $tmpArr['PeptideNum']) $peptideNum = $tmpArr['PeptideNum'];
      $hitProperty['LocusTag'] = str_replace(",", ";", $hitProperty['LocusTag']);
      $BaitProperty['LocusTag'] = str_replace(",", ";", $BaitProperty['LocusTag']);
      $hitGeneName = str_replace(",", ";", $hitGeneName);
      $BaitProperty['GeneName'] = str_replace(",", ";", $BaitProperty['GeneName']);
      $line = $BaitProperty['ID'].",".$BaitProperty['GeneID'].",".$BaitProperty['GeneName'].",".$BaitProperty['LocusTag']
              .",".$hitProperty['ID'].",".$hitProperty['GeneID'].",".$hitGeneName.",".$hitProperty['LocusTag'].",".$hitProperty['HitGI']
              .",".$hitProperty['Expect'].",".$peptideNum.",".$expID;
      fwrite($handle, $line."\r\n");
    }
    $hitsCounter += count($tmpArr2);
  }
  if($open_file_flag){
    fclose($handle);
    $counter++;
$SQL = "SELECT `ID` FROM `Hits` WHERE `BaitID`='".$BaitProperty['ID']."'";
$tmpArr3 = $HITSDB->fetchAll($SQL);
echo $BaitProperty['ID']."-----<br>";
echo $hitsCounter."=====".count($tmpArr3)."<br>";
  }
}
echo $counter;
?>
