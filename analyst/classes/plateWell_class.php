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


class PlateWell{
  var $ID;
  var $PlateID;
  var $BandID;
  var $WellCode;
  var $OwnerID;
  var $ProjectID;
  var $DateTime;
  
  var $HitBaitID;   //for get_plate_hits()
  var $HitGeneID;
  var $HitLocusTag; 
  var $HitORFName;  //for get_plate_hits()
  var $HitID;       //for get_plate_hits()
  var $HitBandID;   //for get_plate_hits()
  var $HitMW;   //for get_plate_hits()
  var $link;

  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function PlateWell( $ID="") {
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
          PlateID, 
          BandID, 
          WellCode, 
          OwnerID, 
          ProjectID, 
          DateTime
          FROM PlateWell where  ID='$this->ID'";
 
       list(
          $this->ID,
          $this->PlateID,
          $this->BandID,
          $this->WellCode,
          $this->OwnerID,
          $this->ProjectID,
          $this->DateTime) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch
  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall_this_plate($Plate_ID) {
    if(!$Plate_ID) {
      echo "Error: Missing info to get palte";
      exit;
    }
    $SQL = "SELECT 
         ID, 
         PlateID, 
         BandID, 
         WellCode, 
         OwnerID, 
         ProjectID, 
         DateTime
         FROM PlateWell";
    $SQL .= " WHERE PlateID='$Plate_ID'";
    $SQL .= " ORDER BY WellCode";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->PlateID[$i], 
         $this->BandID[$i], 
         $this->WellCode[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  //------------------------------------------------
  // used by band.php
  //------------------------------------------------
  function fetch_wells_in_lane_or_exp($Lane_ID=0, $Exp_ID=0){
    if(!$Lane_ID and !$Exp_ID) {
      return;
    }
    $SQL = "SELECT 
         W.ID, 
         W.PlateID, 
         W.BandID, 
         W.WellCode, 
         W.OwnerID, 
         W.ProjectID, 
         W.DateTime
         FROM PlateWell W, Band B WHERE W.BandID=B.ID";
    if($Lane_ID){
      $SQL .= " and B.LaneID='$Lane_ID'";
    }else{
      $SQL .= " and B.ExpID='$Exp_ID'";
    }
    $SQL .= " ORDER BY W.PlateID,WellCode";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->PlateID[$i], 
         $this->BandID[$i], 
         $this->WellCode[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert($frm_PlateID, $frm_BandID, $frm_WellCode, $frm_OwnerID=0, $frm_ProjectID=0){
    if(
      !$frm_PlateID or  
      !$frm_WellCode or 
      !$frm_OwnerID  
     ){
      echo "missing info: ... insert aborted.";
      echo "$frm_PlateID, $frm_BandID, $frm_WellCode, $frm_OwnerID=0, $frm_ProjectID=0";
      exit;
    }else{
      //check if the well is used in the plate
      $SQL = "select ID from PlateWell where PlateID = '$frm_PlateID' and WellCode = '$frm_WellCode'";
      if(!mysqli_num_rows(mysqli_query($this->link, $SQL))){
        $SQL ="INSERT INTO PlateWell SET 
          PlateID='$frm_PlateID', 
          BandID='$frm_BandID', 
          WellCode='$frm_WellCode', 
          OwnerID='$frm_OwnerID', 
          ProjectID='$frm_ProjectID', 
          DateTime=now()";
         mysqli_query($this->link, $SQL);
         $this->ID = mysqli_insert_id($this->link);
        //set PlateID into Band
        $SQL = "UPDATE Band SET inPlate=$frm_PlateID WHERE ID = $frm_BandID";
         mysqli_query($this->link, $SQL);
      }
    }
  }//end insert

  //----------------------------------------------
  //     remove  function
  //----------------------------------------------
  function remove($Band_ID) {
      //check if this Band link to Hits. It shouldn't be deleted
      $SQL = "Select ID from Hits where BandID='$Band_ID'";
      if(mysqli_num_rows(mysqli_query($this->link, $SQL))){
        return "The Well can not be deleted since it links to hits.";
      }
      $SQL = "DELETE FROM PlateWell WHERE BandID = '$Band_ID'";
      mysqli_query($this->link, $SQL);
      //set 0 inPlate for Band record
      mysqli_query($this->link, "UPDATE Band SET inPlate=0 WHERE ID = $Band_ID");
      return '';
  }
  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_ID=0, 
         $frm_PlateID=0, 
         $frm_BandID=0, 
         $frm_WellCode='', 
         $frm_OwnerID=0, 
         $frm_ProjectID=0, 
         $frm_DateTime=''){
      $SQL ="UPDATE PlateWell SET 
         PlateID='$frm_PlateID', 
         BandID='$frm_BandID', 
         WellCode='$frm_WellCode', 
         OwnerID='$frm_OwnerID', 
         ProjectID='$frm_ProjectID', 
         DateTime='$frm_DateTime'
         WHERE ID =$frm_ID ";
      mysqli_query($this->link, $SQL);
   }//end of function update
   function get_plate_id($Band_ID){
     $SQL = "select PlateID from PlateWell where BandID = $Band_ID group by PlateID";
     $sqlResult = mysqli_query($this->link, $SQL);
     if($row = mysqli_fetch_row($sqlResult)){
       return $row[0];
     }else{
       return 0;
     }
   }
   //------------------------------------
   //for checkCarryOver.inc.php
   //fetch all hits in the specified plate
   //------------------------------------
   function get_plate_hits($Plate_ID){
    if(!$Plate_ID) exit;
    $SQL = "select W.ID, W.WellCode, H.BaitID, H.GeneID,  H.LocusTag, H.ID, H.BandID, H.MW 
            from PlateWell W, Hits H where W.ID=H.WellID and W.PlateID='$Plate_ID'";
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    $i = 0;
    while (list(
         $this->ID[$i], 
         $this->WellCode[$i], 
         $this->HitBaitID[$i],
         $this->HitGeneID[$i],
         $this->HitLocusTag[$i], 
         $this->HitID[$i],
         $this->HitBandID[$i],
         $this->HitMW[$i] )= mysqli_fetch_row($sqlResult) ) {
       $i++;
    } 
   }//end function
   //---------------------------------
   // used by UploadMDS.php
   //---------------------------------
   function is_exsist($plateName,$wellCode, $Plate_ID = 0){
     if($Plate_ID){
       $SQL = "select ID from PlateWell where PlateID='$Plate_ID' and WellCode='$wellCode'";
     }else{
       $SQL = "select W.ID from PlateWell W, Plate P where W.PlateID=P.ID and P.Name='$plateName' and W.WellCode='$wellCode'";
     }
     if($row = mysqli_fetch_row(mysqli_query($this->link, $SQL))){
       return 1;
     }else{
       return 0;
     }
   }
	 //----------------------------------
	 // used by plate_show.php
	 //----------------------------------
	 function band_in_plate($Band_ID){
	   $Plate_ID_arr = array();
		 if(!$Band_ID)return $Plate_ID_arr;
	   $SQL = "select PlateID from PlateWell where BandID='$Band_ID'";
		 //echo $SQL;
		 $results = mysqli_query($this->link, $SQL);
		 while($row = mysqli_fetch_row($results) ){
		   array_push($Plate_ID_arr,$row[0]);
		 }
		 return array_unique($Plate_ID_arr);
	 }
}//end of class
?>
