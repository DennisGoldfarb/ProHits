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

class Lane{
  var $ID;
  var $GelID;
  var $ExpID;
  var $LaneNum;
  var $LaneCode;
  var $Notes;
  var $OwnerID;
  var $ProjectID;
  var $DateTime;
  
  var $BaitORFName;    //using for get_gel_lanes()
  var $BaitID;        //using for get_gel_lanes()
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Lane( $ID="") {
    global $HITSDB;
    $this->link = $HITSDB->link;
    if($ID) {
      $this->fetch($ID);
    }
  }//function end

  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="") {
    if($ID){
      $this->ID = $ID;
      $SQL = "SELECT 
          ID, 
          GelID, 
          ExpID, 
          LaneNum, 
          LaneCode, 
          Notes, 
          OwnerID, 
          ProjectID, 
          DateTime
          FROM Lane where  ID='$this->ID'";
     //if($Exp_ID) $SQL .= " and ExpID='$Exp_ID'";
     //if($Gel_ID) $SQL .= " and GelID='$Gel_ID'";
     //echo $SQL;
     $sqlResult = mysqli_query($this->link,$SQL);
       $this->count = mysqli_num_rows($sqlResult);
     if(!$this->count){
         echo "<font color=red>Error information to fetch Lane!!</font>";
      exit;
     }
       list(
          $this->ID,
          $this->GelID,
          $this->ExpID,
          $this->LaneNum,
          $this->LaneCode,
          $this->Notes,
          $this->OwnerID,
          $this->ProjectID,
          $this->DateTime) = mysqli_fetch_array($sqlResult);
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall($frm_ExpID=0,$frm_GelID) {
    $SQL = "SELECT 
         ID, 
         GelID, 
         ExpID, 
         LaneNum, 
         LaneCode, 
         Notes, 
         OwnerID, 
         ProjectID, 
         DateTime
         FROM Lane";
  if($frm_ExpID) $SQL .= " WHERE ExpID = '$frm_ExpID'";
  if($frm_GelID) $SQL .= " WHERE GelID = '$frm_GelID'";
    $SQL .= " ORDER BY ID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->GelID[$i], 
         $this->ExpID[$i], 
         $this->LaneNum[$i], 
         $this->LaneCode[$i], 
         $this->Notes[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
    $frm_GelID=0, 
    $frm_ExpID=0, 
    $frm_LaneNum=0, 
    $frm_LaneCode='', 
    $frm_Notes='', 
    $frm_OwnerID=0,
    $frm_ProjectID=0,
    $frm_Date=''){
    if(
      !$frm_GelID or 
      !$frm_ExpID or 
      !$frm_LaneNum or 
      !$frm_LaneCode or 
      !$frm_OwnerID ){
      echo "Error : missing information to insert into Lane table.";
      exit;
    }else if($this->exsist($frm_GelID,$frm_LaneNum) and $frm_GelID){
      $msg =  "<font color=red>Error : you can't resubmit this lane(GelID=$frm_GelID,LaneNum=$frm_LaneNum). Please check the lane number.</font>";
      return $msg;
    }else{
      if(!$frm_Date) $frm_Date=@date("Y-m-d H:i:s");
      $SQL ="INSERT INTO Lane SET 
          GelID='$frm_GelID', 
          ExpID='$frm_ExpID', 
          LaneNum='$frm_LaneNum', 
          LaneCode='". mysqli_escape_string($this->link, $frm_LaneCode)."', 
          Notes='$frm_Notes', 
          OwnerID='$frm_OwnerID', 
          ProjectID='$frm_ProjectID', 
          DateTime='$frm_Date'";
      
      mysqli_query($this->link,$SQL);
      $this->ID = mysqli_insert_id($this->link);
      return "";
    }
  }//end insert
  //----------------------------------------------
  //      check if submitted
  //----------------------------------------------
  function exsist($frm_GelID,$frm_LaneNum){
      $SQL = "SELECT ID FROM Lane WHERE GelID = '$frm_GelID' and LaneNum = '$frm_LaneNum' ";
    if( mysqli_num_rows(mysqli_query($this->link,$SQL)) ){
      return true;
    }else{
      return false;
    }
  }
  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM Lane WHERE ID = '$ID'";
      mysqli_query($this->link,$SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update( $frm_ID, $frm_GelID, $frm_LaneNum, $frm_LaneCode='', $frm_Notes=''){
    //check if the lane number is used before change it
    $msg = "";
    $SQL = '';
    $tmpLane = new Lane($frm_ID);
    if($tmpLane->LaneNum != $frm_LaneNum){
         if($this->exsist($frm_GelID,$frm_LaneNum)){
          $msg = "<font color=red>Error: The lane number ". $frm_LaneNum. " of this gel has been used by another record.</font>";
       }
    }
    if(!$msg){
      $SQL ="UPDATE Lane SET 
         LaneNum='$frm_LaneNum', 
         LaneCode='". mysqli_escape_string($this->link, $frm_LaneCode)."', 
         Notes='$frm_Notes' 
         WHERE ID =$frm_ID ";
       mysqli_query($this->link,$SQL);
     $this->fetch($frm_ID);
     
   }
   //echo $SQL;
   return $msg;
   }//end of function update 
  //-----------------------------------------------
  //  return all lanes in the plate will bait info
  //  It will be used in checksplillover.php
/*+----+-------+-------+---------+-------------------------+---------+--------+
  | ID | GelID | ExpID | LaneNum | LaneCode                | ORFName | BaitID |
  +----+-------+-------+---------+-------------------------+---------+--------+
  |  2 |     9 |    13 |       1 | test Lane               | YER025W |      4 |
  |  3 |     9 |    13 |       2 | test Lane               | YER025W |      4 |
  |  4 |     9 |    13 |       3 | test Lane               | YER025W |      4 |
  |  5 |     9 |    13 |       4 | test Lane               | YER025W |      4 |
  | 28 |     9 |    51 |       5 | theLaneCode             | YLR079W |     68 |
  |  6 |     9 |    16 |       6 | sdf                     | YER025W |      4 |
  | 11 |     9 |    16 |       7 | new lane for exp16 gel9 | YER025W |      4 |
  |  9 |     9 |    16 |       8 | ybr16w                  | YER025W |      4 |
  |  8 |     9 |    16 |       9 | modified code           | YER025W |      4 |
  |  7 |     9 |    16 |      10 | sdfas                   | YER025W |      4 |
  +----+-------+-------+---------+-------------------------+---------+--------+
 */
  function get_gel_lanes($Gel_ID){
    if(!$Gel_ID){
      echo "Error: need gel id in lane_class.php";
      exit;
    }
    $SQL = "select L.ID,L.GelID,L.ExpID,L.LaneNum,L.LaneCode,B.GeneID,B.LocusTag,B.ID 
          from Lane L, Gel G, Experiment E, Bait B where B.ID=E.BaitID and E.ID=L.ExpID and L.GelID=G.ID
          and G.ID='$Gel_ID' ORDER BY L.LaneNum";
    //echo $SQL;     
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    $i=0;
    while (list(
         $this->ID[$i], 
         $this->GelID[$i], 
         $this->ExpID[$i], 
         $this->LaneNum[$i], 
         $this->LaneCode[$i],
         $this->BaitGeneID[$i], 
         $this->BaitLocusTag[$i],
         $this->BaitID[$i] )= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }//end while
  }//end funcation
  //-----------------------------------------------
  // used by band_lane_view.inc.php
  //-----------------------------------------------
  function lane_in_plates($Lane_ID = 0){
    $rt = array();
    if(!$Lane_ID) return $rt;
    $SQL = "select distinct W.PlateID from PlateWell W, Band B, Lane L where L.ID=B.LaneID and B.ID=W.BandID and L.ID='$Lane_ID'";
    //echo $SQL;
    $results = mysqli_query($this->link,$SQL);
    while($row = mysqli_fetch_row($results) ){
      array_push($rt, $row[0]);
    }
    return $rt;
  }
}//end of class
?>
