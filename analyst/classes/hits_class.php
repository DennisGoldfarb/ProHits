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

class Hits{
  var $ID;
  var $WellID;
  var $BaitID;
  var $BandID;
  var $Instrument;
  var $GeneID;
  var $LocusTag;
  var $KeyType;
  var $HitGI;
  var $HitName;
  var $Expect;
  var $Expect2;
  var $MW;  //kDa
  var $RedundantGI;
  var $HitGene;
  var $ResultFile;
  var $SearchDatabase;
  var $DateTime;
  var $OwnerID;
  var $WellCode;
  var $HitFrequency;
  var $SearchEngine;
  var $BandLocation;
  var $PlateID;
  var $LaneID;
  
  var $ExpID;  //for fetchall_Bait 
  var $link;
  var $count;  
  var $AccessProjectID;
  
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Hits( $ID="") { 
     
    global $HITSDB;
    $this->link = $HITSDB->link;
    
    global $AccessProjectID;
    global $AccessProteinKeyType;    
    $this->AccessProjectID = $AccessProjectID;
    $this->KeyType = $AccessProteinKeyType;    
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
          WellID, 
          BaitID, 
          BandID, 
          Instrument, 
          GeneID,
          LocusTag, 
          HitGI, 
          HitName, 
          Expect,
          Expect2,
          MW, 
          RedundantGI, 
          ResultFile, 
          SearchDatabase, 
          DateTime,
          SearchEngine,
          OwnerID 
          FROM Hits where  ID='$this->ID'";
       //echo $SQL;
       list(
          $this->ID,
          $this->WellID,
          $this->BaitID,
          $this->BandID,
          $this->Instrument,
          $this->GeneID,
          $this->LocusTag,
          $this->HitGI,
          $this->HitName,
          $this->Expect,
          $this->Expect2,
          $this->MW,
          $this->RedundantGI,
          $this->ResultFile,
          $this->SearchDatabase,
          $this->DateTime,
          $this->SearchEngine,
          $this->OwnerID) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch
    //----------------------------------------------
  //      get all hits for a plate 
  //----------------------------------------------
  function fetchall_plate($Plate_ID){
    $SQL = "SELECT 
         H.ID, 
         H.WellID, 
         H.BaitID, 
         H.BandID, 
         H.Instrument, 
         H.GeneID,
         H.LocusTag, 
         H.HitGI, 
         H.HitName, 
         H.Expect,
         H.Expect2,
         H.MW, 
         H.RedundantGI, 
         H.ResultFile, 
         H.SearchDatabase, 
         H.DateTime,
         H.OwnerID, 
         H.SearchEngine,
         W.WellCode, 
         YF.Frequency"; 
         
    $FROM =  " FROM Hits H, PlateWell W  LEFT JOIN  YeastFrequency YF ON H.LocusTag=YF.ORFName ";
   
    $SQL .= $FROM;  
    $SQL .= " WHERE H.WellID=W.ID and PlateID='$Plate_ID' ";    
    $SQL .= " and W.ProjectID=$this->AccessProjectID";    
    $SQL .= ' ORDER By W.WellCode';
    $i = 0;
    echo $SQL;exit;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->WellID[$i], 
         $this->BaitID[$i], 
         $this->BandID[$i], 
         $this->Instrument[$i],
         $this->GeneID[$i],
         $this->LocusTag[$i], 
         $this->HitGI[$i], 
         $this->HitName[$i], 
         $this->Expect[$i],
         $this->Expect2[$i],
         $this->MW[$i], 
         $this->RedundantGI[$i], 
         $this->ResultFile[$i], 
         $this->SearchDatabase[$i], 
         $this->DateTime[$i],
         $this->OwnerID[$i],
         $this->SearchEngine[$i],
         $this->WellCode[$i],
         $this->HitFrequency[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end fetchall for hit report by plate
  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall_list($order_by='',$Plate_ID=0,$start_point='0', $result_per_page=0){
    $SQL = "SELECT 
         H.ID, 
         H.WellID, 
         H.BaitID, 
         H.BandID, 
         H.Instrument,
         H.GeneID, 
         H.LocusTag, 
         H.HitGI, 
         H.HitName, 
         H.Expect,
         H.Expect2,
         H.MW, 
         H.RedundantGI, 
         H.ResultFile, 
         H.SearchDatabase, 
         H.DateTime,
         H.OwnerID, 
         W.WellCode 
         
         FROM Hits H, PlateWell W";
    $SQL .= " WHERE H.WellID=W.ID";
    if($Plate_ID) {
      $SQL .= " and PlateID=$Plate_ID";
    }
    if($oder_by){
      $SQL .= " ORDER BY $order_by";
    }else{
      $SQL .= " ORDER By W.WellCode";
    }
     
    if($result_per_page){
      $SQL .= " LIMIT $start_point,$result_per_page";
    }
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->WellID[$i], 
         $this->BaitID[$i], 
         $this->BandID[$i], 
         $this->Instrument[$i],
         $this->GeneID[$i], 
         $this->LocusTag[$i], 
         $this->HitGI[$i], 
         $this->HitName[$i], 
         $this->Expect[$i],
         $this->Expect2[$i],
         $this->MW[$i], 
         $this->RedundantGI[$i], 
         $this->ResultFile[$i], 
         $this->SearchDatabase[$i], 
         $this->DateTime[$i],
         $this->OwnerID[$i],
         $this->WellCode[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  //----------------------------------------------
  //      fetchall new saved hits function
  //----------------------------------------------
  function fetchall_new_saved($hitsStr){
   if(!$hitsStr){ echo "Error: missing hits String"; exit; }
    $SQL = "SELECT 
         ID, 
         WellID, 
         BaitID, 
         BandID, 
         Instrument, 
         GeneID,
         LocusTag, 
         HitGI, 
         HitName, 
         Expect,
         Expect2,
         MW, 
         RedundantGI, 
         ResultFile, 
         SearchDatabase, 
         DateTime,
         OwnerID
 
         FROM Hits where ID in ($hitsStr)";
 
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->WellID[$i], 
         $this->BaitID[$i], 
         $this->BandID[$i], 
         $this->Instrument[$i], 
         $this->GeneID[$i],
         $this->LocusTag[$i], 
         $this->HitGI[$i], 
         $this->HitName[$i], 
         $this->Expect[$i],
         $this->Expect2[$i],
         $this->MW[$i], 
         $this->RedundantGI[$i], 
         $this->ResultFile[$i], 
         $this->SearchDatabase[$i], 
         $this->DateTime[$i],
         $this->OwnerID[$i] )= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  //--------------------------------------
  // used by plate_show.php
  //--------------------------------------
  function fetchall_one_band($Band_ID=''){
    if(!$Band_ID) return;
    $SQL = "SELECT 
         H.ID, 
         H.WellID, 
         H.BaitID, 
         H.BandID, 
         H.Instrument,
         H.GeneID, 
         H.LocusTag, 
         H.HitGI, 
         H.HitName, 
         H.Expect,
         H.Expect2,
         H.MW, 
         H.RedundantGI, 
         H.ResultFile, 
         H.SearchDatabase, 
         H.DateTime,
         H.OwnerID, 
         H.SearchEngine,
         W.WellCode";
         
    $FROM =  " FROM Hits H, PlateWell W ";
    
    $SQL .= $FROM;
    $SQL .= " WHERE H.WellID=W.ID and W.BandID='$Band_ID'";
    
    
    $i = 0;
    //echo $SQL;exit;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->WellID[$i], 
         $this->BaitID[$i], 
         $this->BandID[$i], 
         $this->Instrument[$i],
         $this->GeneID[$i], 
         $this->LocusTag[$i], 
         $this->HitGI[$i], 
         $this->HitName[$i], 
         $this->Expect[$i],
         $this->Expect2[$i],
         $this->MW[$i], 
         $this->RedundantGI[$i], 
         $this->ResultFile[$i], 
         $this->SearchDatabase[$i], 
         $this->DateTime[$i],
         $this->OwnerID[$i],
         $this->SearchEngine[$i],
         $this->WellCode[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }
  //------------------------------------------
  //this function is used for bait report page
  //used by bait_report.php,
  //------------------------------------------
  function fetchall_Bait($Bait_ID=0){
   if($Bait_ID){
    $SQL = "SELECT 
         H.ID,
         H.WellID, 
         H.BaitID, 
         H.BandID, 
         H.Instrument,
         H.GeneID, 
         H.LocusTag, 
         H.HitGI, 
         H.HitName, 
         H.Expect,
         H.Expect2,
         H.MW,
         H.SearchDatabase, 
         H.DateTime,
         H.OwnerID,
         H.SearchEngine,
         L.ExpID,
         YF.Frequency,
         DB.GeneName";
         
    $FROM =  " FROM Hits H, Band B, Lane L 
         left join YeastDB DB on H.LocusTag=DB.ORFName
         LEFT JOIN  YeastFrequency YF ON H.LocusTag=YF.ORFName ";
    
    $SQL .= $FROM;
         
    $SQL .= " WHERE H.BandID = B.ID and B.LaneID = L.ID  ";
    $SQL .= " and H.BaitID='$Bait_ID' ORDER BY L.ExpID, DB.GeneName";

    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->WellID[$i], 
         $this->BaitID[$i], 
         $this->BandID[$i],          
         $this->Instrument[$i],
         $this->GeneID[$i],
         $this->LocusTag[$i], 
         $this->HitGI[$i], 
         $this->HitName[$i], 
         $this->Expect[$i],
         $this->Expect2[$i],
         $this->MW[$i],
         $this->SearchDatabase[$i], 
         $this->DateTime[$i],
         $this->OwnerID[$i],
         $this->SearchEngine[$i],
         $this->ExpID[$i],
         $this->HitFrequency[$i],
         $this->HitGene[$i])= mysqli_fetch_row($sqlResult) ) {
         $i++;
      }
    }//end if bait id is passed
  }//end of function fetchall
  
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_WellID=0, 
       $frm_BaitID=0, 
       $frm_BandID=0, 
       $frm_Instrument='', 
       $frm_GeneID=0, 
       $frm_LocusTag='', 
       $frm_HitGI='', 
       $frm_HitName='', 
       $frm_Expect=0,
       $frm_MW='',
       $frm_RedundantGI='', 
       $frm_ResultFile='', 
       $frm_SearchDatabase='', 
       $frm_OwnerID='' ){
    if(
      !$frm_WellID or 
      !$frm_BaitID or 
      !$frm_BandID or 
      !$frm_HitGI 
     ){
      echo "missing info: ... insert aborted from Hits.";
      exit;
    }else{
      if(!$this->is_exsist_hit($frm_WellID,$frm_HitGI)){
        if($frm_LocusTag){
           //add this hit into Bait2Hits table
           //get Bait ORF if the record already in Bait2Hits table, it will not be added.
           $SQL = "select LocusTag from Bait where ID=$frm_BaitID";
           $row = mysqli_fetch_row(mysqli_query($this->link, $SQL));
           $baitORF = $row[0];
           $SQL = "INSERT INTO Bait2Hits SET BaitORF='$baitORF', HitORF='$frm_LocusTag'";
           mysqli_query($this->link, $SQL);
        }
         
        $SQL ="INSERT INTO Hits SET 
          WellID='$frm_WellID', 
          BaitID='$frm_BaitID', 
          BandID='$frm_BandID', 
          Instrument='$frm_Instrument',
          GeneID='$frm_GeneID', 
          LocusTag='$frm_LocusTag',
          KeyType = '$this->KeyType', 
          HitGI='$frm_HitGI', 
          HitName='$frm_HitName', 
          Expect='$frm_Expect',
          MW='$frm_MW',
          RedundantGI='$frm_RedundantGI', 
          ResultFile='$frm_ResultFile', 
          SearchDatabase='$frm_SearchDatabase', 
          DateTime=now(),
          OwnerID='$frm_OwnerID'";
        //echo $SQL;
        mysqli_query($this->link, $SQL);
        $this->ID = mysqli_insert_id($this->link);
        $this->fetch($this->ID);
        return $this->ID;
      }else{
        return 0;
      }//end if
    }//end if
  }//end insert

  //----------------------------------------------
  //      delete function
  //      only owner can delete. this function will 
  //     be called from /Sonar/hits_aaved.php
  //----------------------------------------------
  function delete($ID='',$UserID='') {
    //if the user has permissin to delete hit. most users
    // not permission to do this.
    if($ID) {
      $SQL = "DELETE FROM Hits WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
      //delete all peptids link to this hit
      $SQL = "DELETE FROM Peptide WHERE HitID = '$ID'";
      mysqli_query($this->link, $SQL);
   } 
  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_ID=0, 
         $frm_WellID=0, 
         $frm_BaitID=0, 
         $frm_BandID=0, 
         $frm_Instrument='',
         $frm_GeneID=0, 
         $frm_LocusTag='',
         $frm_HitGI='', 
         $frm_HitName='', 
         $frm_MW='',
         $frm_RedundantGI='', 
         $frm_ResultFile='', 
         $frm_SearchDatabase='', 
         $frm_DateTime=''){
      $SQL ="UPDATE Hits SET";
      $comma = '';
      if($frm_WellID) {
          $SQL .=" WellID='$frm_WellID'";
          $comma = ',';
      }
      if($frm_BaitID){
         $SQL .= "$comma BaitID='$frm_BaitID'";
         $comma = ',';
       }
       if($frm_BandID){
         $SQL .= "$comma BandID='$frm_BandID'";
         $comma = ',';
       }
       if($frm_Instrument){
         $SQL .= "$comma Instrument='$frm_Instrument'";
         $comma = ',';
       }
       if($frm_GeneID){
         $SQL .= "$comma GeneID='$frm_GeneID'";
         $comma = ',';
       }
       if($frm_LocusTag){
         $SQL .= "$comma LocusTag='$frm_LocusTag'";
         $comma = ',';
       }       
       if($frm_HitGI){
         $SQL .= "$comma HitGI='$frm_HitGI'";
         $comma = ',';
       }
       if($frm_HitName){
         $SQL .= "$comma HitName='$frm_HitName'";
         $comma = ',';
       }
       if($frm_MW){
         $SQL .= "$comma MW='$frm_MW'";
         $comma = ',';
       }
       if($frm_RedundantGI){
         $SQL .= "$comma RedundantGI='$frm_RedundantGI'";
         $comma = ',';
       }
       if($frm_ResultFile){
         $SQL .= "$comma ResultFile='$frm_ResultFile'";
         $comma = ',';
       }
       if($frm_SearchDatabase){
         $SQL .= "$comma SearchDatabase='$frm_SearchDatabase'";
         $comma =',';
       }
       if($frm_DateTime){
         $SQL .= "$comma DateTime='$frm_DateTime'";
       }
       $SQL .= " WHERE ID =$frm_ID ";
      //echo $SQL;exit;
      mysqli_query($this->link, $SQL);
   }//end of function update
   function get_total($Plate_ID){
    $SQL = "select count(H.ID) from Hits H, PlateWell W Where H.WellID=W.ID and W.PlateID=$Plate_ID";
    $row = mysqli_fetch_row(mysqli_query($this->link, $SQL));
    return $row[0];
   }
   //-------------------------------------------------------------------------------
   // it will be called by checkspillover.php
   // get all spillover hits in the lane. That Hits' orf as same as nearby lane's bait orf
   //--------------------------------------------------------------------------------
   function get_spillover_hits($bait_LocusTag,$laneNum_str){
    $this->count = 0;
     if(!$bait_LocusTag or !$laneNum_str){
      echo "Error: missing info to get spillover hits."; exit;
    }
    $SQL = "select H.ID, H.BaitID,  H.BandID, H.GeneID, H.LocusTag, H.HitGI, W.PlateID,W.WellCode, B.Location, B.LaneID 
            from Hits H, PlateWell W, Band B 
            where H.WellID=W.ID and W.BandID=B.ID 
            and H.LocusTag='$bait_LocusTag' and B.LaneID in($laneNum_str)";
    $i = 0;
    //echo $SQL."<br>";
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->BaitID[$i], 
         $this->BandID[$i],
         $this->GeneID[$i], 
         $this->LocusTag[$i],
         $this->HitGI[$i],
         $this->PlateID[$i],
         $this->WellCode[$i],
         $this->BandLocation[$i],
         $this->LaneID[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    } 
  }//end function
  //--------------------------------------------------------------------------------
  // check if the hit has been inserted before. Well_ID and Hit_GI
  //--------------------------------------------------------------------------------
  function is_exsist_hit($frm_WellID,$frm_HitGI){
    $rt = 0;
    $SQL = "select ID from Hits where WellID='$frm_WellID' and HitGI='$frm_HitGI' ";
    //echo $SQL;
    if( mysqli_num_rows(mysqli_query($this->link, $SQL)) ){
      $rt = 1;
    }
    return $rt;
  }//end function
  //--------------------------------------------------------------------------------
  // if the hit name contains keywords: "ribosome" "ribosomal" 
  //--------------------------------------------------------------------------------
  function ribosome_in_hit_name($Hit_ID){
    $rt = 0;
    $SQL = "select ID from Hits where ID='$Hit_ID'  and HitName like '%ribosom%'";
    //echo $SQL;
    if( mysqli_num_rows(mysqli_query($this->link, $SQL)) ){
      $rt = 1;
    }
    return $rt;
  }
  //--------------------------------------------------------------------------------
  // return a bait id array their are ided.
  // it is used by bait.php
  //--------------------------------------------------------------------------------
  function get_baitlist_that_bait_protein_ided($bait_str = 0){
    $baitIDs = array();
    $SQL = " select B.ID from Bait B, Hits H where ";
    if($bait_str){
      $SQL .= "B.ID in($bait_str) and ";
    }
    $SQL .=" H.BaitID=B.ID and H.GeneID=B.GeneID group by B.ID";
    //echo $SQL;
    $results = mysqli_query($this->link, $SQL);
    $i = 0;
    while (list($baitIDs[$i])= mysqli_fetch_row($results) ) {
       $i++;
    }
    return $baitIDs;
 }
  
}//end of class
?>
