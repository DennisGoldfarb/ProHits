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
//----------------------------------------------------------------
$prohits_managerDB = new mysqlDB(MANAGER_DB, HOSTNAME, USERNAME, DBPASSWORD);
$reportDir = "../TMP/experiment/";
if(!_is_dir($reportDir)) _mkdir_path($reportDir);

$filename_out = $reportDir.$_SESSION['USER']->Username."_experiment_progress.csv";
$usersIdNamePair = get_users_ID_Name($mainDB);
$ProtocolIdNamePair = get_Protocol_id_name_pair($AccessProjectID);
$fromTime = $from_Year . "-" . $from_Month . "-" . $from_Day;
$toTime = $to_Year . "-" . $to_Month . "-" . $to_Day;

$filedNameStr = "Bait ID,Bait GeneName,Bait LocusTag,Experiment Name,Growing Conditions / Protocol,Ip Conditions / Protocol,Digest Conditions / Protocol,Peptide Fragmentation,Sample,Has Raw File,Hits Parsed,# of Hits,Experiment created by,In Plate\n";
//echo $filedNameStr;exit;
$title = '';
if($display_option == "all"){
  $title = "All experiments";
}elseif($display_option == "hits"){
  $title = "Experiments which hits have been pressed";
}elseif($display_option == "digest"){
  $title = "Experiment which sample has been digested but has no raw file linked";
}elseif($display_option == "timeRange"){
  $title = "Baits are created from $fromTime to $toTime";
}
$handle_write = fopen($filename_out, "w");
fwrite($handle_write, $title."\r\n");

fwrite($handle_write, $filedNameStr);

$SQL = "SELECT ID, 
        BaitID, 
        Name,
        OwnerID, 
        GrowProtocol,
        IpProtocol,
        DigestProtocol,
        PeptideFrag,
        Notes,
        WesternGel,
        DateTime FROM `Experiment` 
        WHERE `ProjectID`='$AccessProjectID'";
if($display_option == "timeRange"){
  $SQL .= " AND DateTime >= '$toTime' AND DateTime <= '$fromTime'";
}
$SQL .= " ORDER BY BaitID DESC";
$ExperimentArr = $mainDB->fetchAll($SQL);
$writeFlag = 0;
foreach($ExperimentArr as $experimentValue){

  if($display_option == "digest"){
    if($experimentValue['DigestProtocol']){
      $SQL = "SELECT  ID, Location, RawFile, InPlate
              FROM `Band` 
              WHERE ExpID = '".$experimentValue['ID']."'
              AND (`RawFile` = '' OR `RawFile` IS NULL)";
      $BandArr = $mainDB->fetchAll($SQL);
      if(!count($BandArr)) continue;
    }else{
      continue;
    }  
  }else{
    $SQL = "SELECT ID, Location, RawFile, InPlate
            FROM `Band` 
            WHERE ExpID = '".$experimentValue['ID']."'";
    $BandArr = $mainDB->fetchAll($SQL);
  }
  
  $BaitID = $experimentValue['BaitID'];
  $Name = str_replace("\"", "'", $experimentValue['Name']);
  $SQL = "SELECT `GeneName`, LocusTag, GelFree FROM `Bait` WHERE ID='".$experimentValue['BaitID']."'";
  $BaitArr = $mainDB->fetch($SQL);
  $inPlate = "";
  $BaitGeneName = '';
  $BaitLocusTag = '';
  if(isset($BaitArr['GeneName'])){
    $BaitGeneName = $BaitArr['GeneName'];
    $BaitLocusTag = $BaitArr['LocusTag'];
  }
  $GrowProtocol = '';
  $IpProtocol = '';
  $DigestProtocol = '';
  $PeptideFrag = '';
  $GrowProtocolArr = explode(",",$experimentValue['GrowProtocol']);
  if(count($GrowProtocolArr) == 2){
    if(isset($ProtocolIdNamePair[$GrowProtocolArr[0]])){
      $GrowProtocol = $ProtocolIdNamePair[$GrowProtocolArr[0]] . " (" . $GrowProtocolArr[1] . ")";
      $GrowProtocol = str_replace(",", ";", $GrowProtocol);
      $GrowProtocol = str_replace("\"", "'", $GrowProtocol);
    }
  }
  $IpProtocolArr = explode(",",$experimentValue['IpProtocol']);
  if(count($IpProtocolArr) == 2){
    if(isset($ProtocolIdNamePair[$IpProtocolArr[0]])){
      $IpProtocol = $ProtocolIdNamePair[$IpProtocolArr[0]] . " (" . $IpProtocolArr[1] . ")";
      $IpProtocol = str_replace(",", ";", $IpProtocol);
      $IpProtocol = str_replace("\"", "'", $IpProtocol);
    }
  }
  $DigestProtocolArr = explode(",",$experimentValue['DigestProtocol']);
  if(count($DigestProtocolArr) == 2){
    if(isset($ProtocolIdNamePair[$DigestProtocolArr[0]])){
      $DigestProtocol = $ProtocolIdNamePair[$DigestProtocolArr[0]] . " (" . $DigestProtocolArr[1] . ")";
      $DigestProtocol = str_replace(",", ";", $DigestProtocol);
      $DigestProtocol = str_replace("\"", "'", $DigestProtocol);
    }
  }
  $PeptideFragArr = explode(",",$experimentValue['PeptideFrag']);
  if(count($PeptideFragArr) == 2){
    if(isset($ProtocolIdNamePair[$PeptideFragArr[0]])){
      $PeptideFrag = $ProtocolIdNamePair[$PeptideFragArr[0]] . " (" . $PeptideFragArr[1] . ")";
      $PeptideFrag = str_replace(",", ";", $PeptideFrag);
      $PeptideFrag = str_replace("\"", "'", $PeptideFrag);
    }
  }
  $Notes = str_replace(",", ";", $experimentValue['Notes']);
  
  $experimentValue['WesternGel'] = str_replace(",", ";", $experimentValue['WesternGel']);
  $Image = $experimentValue['WesternGel'];
  $Owner = '';
  if(isset($usersIdNamePair[$experimentValue['OwnerID']])){
    $Owner = $usersIdNamePair[$experimentValue['OwnerID']];
  }  
  $DateTime = $experimentValue['DateTime'];
  if(!$BandArr){
    if($display_option == "all" || $display_option == "timeRange" || $display_option == "digest"){
      //fwrite($handle_write, "\n");
      fwrite($handle_write, $BaitID.",".$BaitGeneName.",".$BaitLocusTag.",".$Name.",".$GrowProtocol.",".$IpProtocol.",".$DigestProtocol.",".$PeptideFrag.",,,,,".$Owner.",\r\n");
    }
    continue;
  }
  //if($writeFlag) fwrite($handle_write, "\n");
  $writeFlag = 0;
  foreach($BandArr as $BandValue){
    $sampleName = $BandValue['Location'];
    $inPlate = '';
    if($BandValue['InPlate']){
      $inPlate = $BandValue['InPlate'];
    }
    $rawFile = '';
    if($BandValue['RawFile']) $rawFile = "Y";
    $SQL = "SELECT `ID` FROM `Hits` WHERE `BandID`='".$BandValue['ID']."'";
    $Hits = $mainDB->get_total($SQL);
    $HitsParsed = '';
    if($Hits){
      $HitsParsed = 'Y';
    }else{
      $Hits = '';
      if($rawFile == "Y"){
        $rawfilesInaBandArr = explode(";", $BandValue['RawFile']);
        foreach($rawfilesInaBandArr as $rawfilesInaBandValue){
          $tableName_fileIDArr = explode(":", $rawfilesInaBandValue);
          if(count($tableName_fileIDArr) == 2){
            $resultTableName = $tableName_fileIDArr[0] . "SearchResults";
            $fileID = $tableName_fileIDArr[1];
            $SQL = "SELECT `SavedBy` FROM $resultTableName WHERE `WellID`='$fileID'";
            if($tmpResultArr = $prohits_managerDB->fetch($SQL) && $tmpResultArr['SavedBy']){
              $Hits = 0;
              $HitsParsed = 'Y';
              break;
            }
          }  
        }
      }  
    }  
    if($display_option == "hits" && $Hits == '') continue;
    fwrite($handle_write, $BaitID.",".$BaitGeneName.",".$BaitLocusTag.",".$Name.",".$GrowProtocol.",".$IpProtocol.",".$DigestProtocol.",".$PeptideFrag.",".$sampleName.",".$rawFile.",".$HitsParsed.",".$Hits.",".$Owner.",".$inPlate."\r\n");
    $writeFlag = 1;
  }  
}
fclose($handle_write);

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

function get_Protocol_id_name_pair($projectID){
  global $mainDB;
  $SQL = "SELECT `ID`, `Name` FROM `Protocol` WHERE `ProjectID`='$projectID'";
  $ProtocolArr = $mainDB->fetchAll($SQL);
  $Protocol_id_name_pair = array();
  foreach($ProtocolArr as $value){
    $Protocol_id_name_pair[$value['ID']] = $value['Name'];
  }
  return $Protocol_id_name_pair;
} 
?>