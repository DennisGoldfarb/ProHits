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


if(!isset($Plate_ID)) $Plate_ID = 0;
if(!isset($hit_db_name)) $hits_db_name = '';

include_once("../common/mysqlDB_class2.php");
require_once("../config/conf.inc.php");

define ("BEFOREWELLS", 12);
if(isset($_SERVER['argv']) and count($_SERVER['argv']) > 2 and !$Plate_ID){
  $Plate_ID = $_SERVER['argv'][1];
  $hits_db_name = $_SERVER['argv'][2];
}

$SQL = "update Plate set MSDate=now() where ID='$Plate_ID'";
if(!isset($mainDB)){ 
  $mainDB = new mysqlDB($hits_db_name);
}
$mainDB->update($SQL);

//genareate an array which contains Bait GeneID of the Well sample
// $BaitGeneIDs[$WellCode]: $BaitGeneIDs['A1'] - $BaitGeneIDs['A12]
$BaitGeneIDs = array();

//get all hits in the plate 
$SQL = "select W.WellCode, H.BaitID, H.GeneID, H.ID
            from PlateWell W, Hits H where W.ID=H.WellID and W.PlateID='$Plate_ID' ORDER BY W.WellCode";
$HitsArr = $mainDB->fetchAll($SQL); 

for($i=0; $i<count($HitsArr); $i++){          
  $SQL = "SELECT GeneID FROM Bait where ID='".$HitsArr[$i]['BaitID']."'";  
  $myBait = $mainDB->fetch($SQL);
  if(!substr($HitsArr[$i]['WellCode'], 1, 1)){
    $HitsArr[$i]['WellCode'] = substr_replace($HitsArr[$i]['WellCode'], '', 1, 1);    
  }      
  if($myBait['GeneID'])  $BaitGeneIDs[$HitsArr[$i]['WellCode']] = $myBait['GeneID'];
}
 

// then let each hit ORF Name walks through the array
$A2H_array = array("A","B","C","D","E","F","G","H");
$wellCode_array = array(); // $wellCode_array[0] = "A1", $wellCode_array[96] = "H12", 
$wellNum_array = array(); //  $wellNum_array["A1"] = 0, $wellNum_array["H12"] = 96,
$well_counter = 0;   // well from 0 -> 95
for($row=0; $row < count($A2H_array); $row++){
  for($col=1; $col <= 12; $col++){
    $wellCode_array[$well_counter] = $A2H_array[$row].$col;
    $wellNum_array[$A2H_array[$row].$col] = $well_counter;
    $well_counter++;
  }
}
//print_r($wellNum_array);exit;

for($i=0; $i<count($HitsArr); $i++){
  $stop = 0;
  $row = 0;  
  if(!$HitsArr[$i]['GeneID']) $stop = 1;
  //start to check carry over
  $current_wellNum = $wellNum_array[$HitsArr[$i]['WellCode']];
  
  $loop = 0;
  while($current_wellNum > 0 and $loop < BEFOREWELLS and !$stop){
    $current_wellNum--;  //go backward 12 times    
    if(isset($BaitGeneIDs[$wellCode_array[$current_wellNum]]) and $BaitGeneIDs[$wellCode_array[$current_wellNum]] == $HitsArr[$i]['GeneID']){
     //this hit is a carry over
     //CO is "Carry Over". Set the record UserID = 0     
      $SQL = "INSERT INTO HitNote SET
               HitID='".$HitsArr[$i]['ID']."', 
               FilterAlias='CO',
               UserID=0, 
               Date=now()";
      $mainDB->insert($SQL);
      break;
    }
    $loop++; //only loop backward 12 times from the start well.
  }//end while
}//end while

?>