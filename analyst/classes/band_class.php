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

class Band{
  var $ID;
  var $ExpID;
  var $LaneID;
  var $BaitID;
  var $BaitMW;
  var $BandMW;
  var $Intensity;
  var $Location;
  var $Modification;
  var $BandPI;
  var $Analysis;
  var $ResultsFile;
  var $OwnerID;
  var $ProjectID;
  var $DateTime;
  var $InPlate;
  
  var $LaneNum;
  var $LaneCode;
  var $LaneSpecies;
  var $LaneGelID;
  
  var $GelID;
  var $GelName;
  var $GelImage;
  
  var $WellCode;
  var $WellID;
  var $count = 0;

  var $AccessProjectID;
  var $link;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Band( $ID="") {
    global $HITSDB;
    $this->link = $HITSDB->link;
    global $AccessProjectID;   
    $this->AccessProjectID = $AccessProjectID;
    if($ID) {
      $this->fetch($ID);
    }
  }//function end

  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="") {
	 
    if($ID){ 
      $SQL = "SELECT 
          ID,
          ExpID, 
          LaneID, 
          BaitID, 
          BaitMW, 
          BandMW, 
          Intensity, 
          Location, 
          Modification,
          BandPI, 
          Analysis, 
          ResultsFile, 
          OwnerID, 
          ProjectID, 
          DateTime,
          InPlate 
          FROM Band ";
					$Where = " WHERE ID='$ID'";
          
          $Where .= " and ProjectID=$this->AccessProjectID";
          
        
         $SQL .= $Where;
			 //echo $SQL;
       list(
          $this->ID,
          $this->ExpID,
          $this->LaneID,
          $this->BaitID,
          $this->BaitMW,
          $this->BandMW,
          $this->Intensity,
          $this->Location,
          $this->Modification,
          $this->BandPI,
          $this->Analysis,
          $this->ResultsFile,
          $this->OwnerID,
          $this->ProjectID,
          $this->DateTime,
          $this->InPlate) = mysqli_fetch_array(mysqli_query($this->link,$SQL));
       $this->count = 1;
     }
  } //end of function fetch
  //----------------------------------------------
  //      get  a number of bands for the user
  //----------------------------------------------
  function get_total($newBand=0){
    $SQL = "SELECT COUNT(ID) FROM Band";
    $Where = " WHERE 1";
    $Where .= " and ProjectID=$this->AccessProjectID";
    if($newBand) $Where .= " AND InPlate=0";
    $SQL .= $Where;
    $row = mysqli_fetch_row(mysqli_query($this->link,$SQL));
    return $row[0];
  }
  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall_list($order_by='', $newBand=0,$start_point=0, $RESULTS_PER_PAGE=0){
    if(!$order_by){  $order_by = "B.ID";}
    $SQL = "SELECT 
         B.ID, 
         B.ExpID, 
         B.LaneID, 
         B.BaitID, 
         B.BandMW, 
         B.Intensity, 
         B.Location, 
         B.Modification,
         B.ResultsFile,
         B.OwnerID,
         B.ProjectID,
         B.DateTime,
         B.InPlate,
     
         L.LaneNum,
         L.LaneCode,
         L.GelID
     
         FROM Band B left join Lane L on (L.ID = B.LaneID) ";
    $Where = " WHERE  "; 
    
    $Where .= " B.ProjectID=$this->AccessProjectID";
    
    
    if($newBand) $Where .= " AND B.InPlate=0";
    $SQL .= $Where;
    if($order_by)  $SQL .= " ORDER BY $order_by";
    if($RESULTS_PER_PAGE) $SQL .= " LIMIT $start_point,$RESULTS_PER_PAGE";
    //echo $SQL;
    $i = 0;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->ExpID[$i], 
         $this->LaneID[$i], 
         $this->BaitID[$i], 
         $this->BandMW[$i], 
         $this->Intensity[$i], 
         $this->Location[$i], 
         $this->Modification[$i],
         $this->ResultsFile[$i],
         $this->OwnerID[$i],
         $this->ProjectID[$i],
         $this->DateTime[$i],
         $this->InPlate[$i],
     
         $this->LaneNum[$i],
         $this->LaneCode[$i],
         $this->GelID[$i]
         
        )= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  
  function fetchall_list_one_plate($Plate_ID){
   /* if(!$order_by){
    $order_by = "B.ID";
  }*/
    $SQL = "SELECT 
         B.ID, 
         B.LaneID, 
         B.BaitID, 
         B.BandMW, 
         B.Intensity, 
         B.Location, 
         B.Modification,
         B.ResultsFile,
         B.OwnerID,
         B.ProjectID,
         B.DateTime,
         B.InPlate,
     
         L.LaneNum,
         L.LaneCode,
         L.GelID,
 
         W.WellCode,
         W.ID
         FROM Band B
         INNER JOIN PlateWell W ON B.ID = W.BandID
         LEFT JOIN Lane L ON L.ID = B.LaneID";
  $Where = " WHERE B.ID = W.BandID and W.PlateID = $Plate_ID and B.ProjectID=$this->AccessProjectID order by WellCode"; 
  $SQL .= $Where;
  //echo $SQL;
    $i = 0;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->LaneID[$i], 
         $this->BaitID[$i], 
         $this->BandMW[$i], 
         $this->Intensity[$i], 
         $this->Location[$i], 
         $this->Modification[$i],
         $this->ResultsFile[$i],
         $this->OwnerID[$i],
         $this->ProjectID[$i],
         $this->DateTime[$i],
         $this->InPlate[$i],
     
         $this->LaneNum[$i],
         $this->LaneCode[$i],
         $this->LaneGelID[$i],
         
         $this->WellCode[$i],
         $this->WellID[$i]
        )= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  
  //----------------------------------------------
  //     fetch all bands in same gel and same lane
  // if there is no lane id passed. it will fetch
  // all bands in the same exp.
  //----------------------------------------------
  function fetch_gel_line_or_exp__($Lane_ID=0, $Exp_ID=0){
    if($Lane_ID or $Exp_ID){
      $SQL = "SELECT 
         ID, 
         ExpID, 
         LaneID, 
         BaitID, 
         BaitMW, 
         BandMW, 
         Intensity, 
         Location, 
         Modification,
         Analysis, 
         ResultsFile, 
         OwnerID, 
         ProjectID, 
         DateTime
         FROM Band";
    if($Lane_ID){
      $SQL .= " where LaneID = '$Lane_ID' and ProjectID=$this->AccessProjectID ORDER BY ID";
    }else{
      $SQL .= " where ExpID = '$Exp_ID' and ProjectID=$this->AccessProjectID ORDER BY ID";
    }
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->ExpID[$i],
         $this->LaneID[$i], 
         $this->BaitID[$i], 
         $this->BaitMW[$i], 
         $this->BandMW[$i], 
         $this->Intensity[$i], 
         $this->Location[$i], 
         $this->Modification[$i],
         $this->Analysis[$i], 
         $this->ResultsFile[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }else{
    echo "Error: missing Land id to fetch bands!";
    exit;
  }
  }
  //----------------------------------------------
  //     fetch all bands in same gel and same lane
  // if there is no lane id passed. it will fetch
  // all bands in the same exp.
  //----------------------------------------------
  function fetchAll_id_str($id_str=''){
    if(!$id_str){
      return; 
    }
    $SQL = "SELECT 
         ID, 
         ExpID, 
         LaneID, 
         BaitID, 
         BaitMW, 
         BandMW, 
         Intensity, 
         Location, 
         Modification,
         Analysis, 
         ResultsFile, 
         OwnerID, 
         ProjectID, 
         DateTime
         FROM Band where ID in($id_str) and ProjectID=$this->AccessProjectID order by Location";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->ExpID[$i],
         $this->LaneID[$i], 
         $this->BaitID[$i], 
         $this->BaitMW[$i], 
         $this->BandMW[$i], 
         $this->Intensity[$i], 
         $this->Location[$i], 
         $this->Modification[$i],
         $this->Analysis[$i], 
         $this->ResultsFile[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_ExpID=0,
       $frm_LaneID=0, 
       $frm_BaitID=0, 
       $frm_BaitMW=0, 
       $frm_BandMW=0, 
       $frm_Intensity='', 
       $frm_Location='', 
       $frm_Modification='',
       $frm_ResultsFile='',
       $frm_OwnerID=0,
       $frm_ProjectID=0,
       $frm_InPlate=0,
       $frm_swath=''){
     
      
     //if(!$frm_BaitMW) $frm_BaitMW=0;
     //if(!$frm_BandMW)$frm_BandMW=0;
     //if(!$frm_InPlate)$frm_InPlate = 0;
     $SQL ="INSERT INTO Band SET 
          ExpID='$frm_ExpID',
          LaneID='$frm_LaneID', 
          BaitID='$frm_BaitID', 
          BaitMW='$frm_BaitMW', 
          BandMW='$frm_BandMW', 
          Intensity='$frm_Intensity', 
          Location='".mysqli_real_escape_string($this->link,$frm_Location)."', 
          Modification='$frm_Modification',
          ResultsFile='".mysqli_real_escape_string($this->link,$frm_ResultsFile)."',
          OwnerID='$frm_OwnerID', 
          ProjectID='$frm_ProjectID',
          Analysis='$frm_swath', 
          DateTime=now(),
          InPlate='$frm_InPlate'";
      mysqli_query($this->link,$SQL);
      //echo $SQL;exit;
      $this->ID = mysqli_insert_id($this->link);
      if(!$this->ID) echo mysqli_error($this->link);
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($Band_ID) {
   $msg = '';
   if($Band_ID) {
     $SQL = "SELECT W.ID, W.PlateID FROM PlateWell W, Hits H WHERE H.WellID=W.ID and W.BandID = '$Band_ID'";
     if(mysqli_num_rows(mysqli_query($this->link,$SQL))){
       return "Error: You can't delete the Band since it has hits.";
     }else{
       //if it is the last band in the lane the lane shuld be deleted.
       $tmpBand = new Band($Band_ID);
       $SQL = "DELETE FROM Band WHERE ID = '$Band_ID'";
       $msg = "Band/sample has been deleted.";
       mysqli_query($this->link,$SQL);
       if($tmpBand->LaneID > 0){
         $thisLane_ID = $tmpBand->LaneID; 
         if(mysqli_num_rows(mysqli_query($this->link,"SELECT ID FROM Band WHERE LaneID = '$thisLane_ID'")) == 0){
           mysqli_query($this->link,"DELETE FROM Lane WHERE ID = '$thisLane_ID'");
           $msg .= " Lane has been deleted.";
         }
       }
       //if it is the last band in the plate , delete the plate
       $SQL = "select PlateID from PlateWell where BandID='$Band_ID'";
       if($row = mysqli_fetch_row(mysqli_query($this->link,$SQL) )){
         $thisPlate_ID = $row[0];
         $SQL = "DELETE FROM PlateWell WHERE BandID = '$Band_ID'";
         $msg .= " Well has been deleted.";
         mysqli_query($this->link,$SQL);
         if(mysqli_num_rows(mysqli_query($this->link,"SELECT ID FROM PlateWell WHERE PlateID = '$thisPlate_ID'")) == 0){
           mysqli_query($this->link,"DELETE FROM Plate WHERE ID = '$thisPlate_ID'");
           $msg .= " Plate has been deleted, it is only band in the plate.";
         }
       }
       return $msg;
     }
   }else{
      echo "Need band id to delete!!!";
   }  
  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_ID=0, 
         $frm_BandMW=0, 
         $frm_Intensity='', 
         $frm_Location='',
         $frm_Modification='',
         $frm_ResultsFile,
         $frm_swath=''
         ){
    //if(!$frm_BandMW)$frm_BandMW=0;
    $SQL ="UPDATE Band SET 
         BandMW='$frm_BandMW', 
         Intensity='$frm_Intensity', 
         Location='$frm_Location', 
         Analysis='$frm_swath', 
         Modification='".mysqli_real_escape_string($this->link,$frm_Modification)."', 
         ResultsFile='".mysqli_real_escape_string($this->link,$frm_ResultsFile)."'
         
         WHERE ID =$frm_ID ";
      mysqli_query($this->link,$SQL);
      echo mysqli_error($this->link);
   }//end of function update
   //-----------------------------------------------
   //   has been set to plate
   //-----------------------------------------------
   function is_in_plate($Band_ID){
     if($Band_ID){
       $SQL ="UPDATE Band SET inPlate=1 WHERE ID = $Band_ID";
       mysqli_query($this->link,$SQL);
     }
   }
   //-----------------------------------------------
   //   has been removed from plate
   //-----------------------------------------------
   function not_in_plate($Band_ID){
     if($Band_ID){
       $SQL ="UPDATE Band SET inPlate=0 WHERE ID = $Band_ID";
       mysqli_query($this->link,$SQL);
     }
   }
   //------------------------------------------------
   //     check is user owne the record
   //-----------------------------------------------
   function isOwner($Band_ID=0, $Owner_ID=0){
       $SQL = "select ID from Band where ID = $Band_ID and OwnerID = $Owner_ID";
        $sqlResult = mysqli_query($this->link,$SQL);
        if(mysqli_num_rows($sqlResult)){
      return 1;
    }else{
      return 0;
    }
   }
  //----------------------------------------------
  //    search in file search_band.inc.php
  //----------------------------------------------
  function search( $searchThis){
    $SQL = "SELECT 
         B.ID, 
         B.LaneID, 
         B.BaitID, 
         B.BandMW, 
         B.Intensity, 
         B.Location, 
         B.Modification,
         B.ResultsFile,
         B.OwnerID,
         B.DateTime,
         B.InPlate,
     
         L.LaneNum,
         L.LaneCode,
         
         G.Name,
         G.Image 
     
         FROM Band B, Lane L, Gel G";
    $Where = " WHERE L.ID = B.LaneID and L.GelID = G.ID"; 
    
    $Where .= " and B.ProjectID=$this->AccessProjectID";
    
    
   $Where .= " and (G.Name='$searchThis' or G.Image='$searchThis' or L.LaneCode='$searchThis')";
   $SQL .= $Where;
  
   //echo $SQL;
    $i = 0;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->LaneID[$i], 
         $this->BaitID[$i], 
         $this->BandMW[$i], 
         $this->Intensity[$i], 
         $this->Location[$i], 
         $this->Modification[$i],
         $this->ResultsFile[$i],
         $this->OwnerID[$i],
         $this->DateTime[$i],
         $this->InPlate[$i],
     
         $this->LaneNum[$i],
         $this->LaneCode[$i],
         $this->GelName[$i],
         $this->GelImage[$i]
         
        )= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function search
  function fetch_band_inOneExp($Exp_ID){
    if($Exp_ID){
      $SQL = "SELECT 
           ID, 
           ExpID, 
           LaneID, 
           BaitID, 
           BaitMW, 
           BandMW, 
           Intensity, 
           Location, 
           Modification,
           Analysis, 
           ResultsFile, 
           OwnerID, 
           ProjectID, 
           DateTime
           FROM Band 
           where ExpID = '$Exp_ID' and ProjectID=$this->AccessProjectID ORDER BY ID";
      
      $i = 0;
      //echo $SQL;
      $sqlResult = mysqli_query($this->link,$SQL);
      $this->count = mysqli_num_rows($sqlResult);
      while (list(
           $this->ID[$i], 
           $this->ExpID[$i],
           $this->LaneID[$i], 
           $this->BaitID[$i], 
           $this->BaitMW[$i], 
           $this->BandMW[$i], 
           $this->Intensity[$i], 
           $this->Location[$i], 
           $this->Modification[$i],
           $this->Analysis[$i], 
           $this->ResultsFile[$i], 
           $this->OwnerID[$i], 
           $this->ProjectID[$i], 
           $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
         $i++;
      }
    }else{
      echo "Error: missing Experiment ID to fetch bands!";
      exit;
    }
  }
}//end of class



?>
