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


class Peptide{
  var $ID;
  var $HitID;
  var $Charge;
  var $MZ;
  var $MASS; //Da
  var $Location;
  var $Expect;
  var $Expect2;
  var $Sequence;
  var $IonFile;
  var $Modifications;
  var $Miss;
  var $Status;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Peptide( $ID="") {
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
          HitID, 
          Charge,
          MZ, 
          MASS, 
          Location, 
          Expect, 
          Expect2, 
          Sequence,
          IonFile,
          Miss,
          Modifications,
          Status
          FROM Peptide where  ID='$this->ID'";
       list(
          $this->ID,
          $this->HitID,
          $this->Charge,
          $this->MZ,
          $this->MASS,
          $this->Location,
          $this->Expect,
          $this->Expect2,
          $this->Sequence,
          $this->IonFile,
          $this->Miss,
          $this->Modifications,
          $this->Status) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall($HitID='') {
    $this->count = 0;
    $SQL = "SELECT 
         ID, 
         HitID, 
         Charge,
         MZ, 
         MASS, 
         Location, 
         Expect, 
         Expect2, 
         Sequence,
         IonFile,
         Miss,
         Modifications,
         Status 
         FROM Peptide";
    if($HitID) {
      $SQL .= " where HitID='$HitID'";
    }
    $SQL .= " ORDER BY Expect";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->HitID[$i], 
         $this->Charge[$i],
         $this->MZ[$i], 
         $this->MASS[$i], 
         $this->Location[$i], 
         $this->Expect[$i], 
         $this->Expect2[$i], 
         $this->Sequence[$i],
         $this->IonFile[$i],
         $this->Miss[$i],
         $this->Modifications[$i],
         $this->Status[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_HitID=0, 
       $frm_Charge=0,
       $frm_MZ=0,
       $frm_MASS=0, 
       $frm_Location='', 
       $frm_Expect=0, 
       $frm_Expect2=0, 
       $frm_Sequence='',
       $frm_IonFile,
       $frm_Miss,
       $frm_Modifications,
       $frm_Status){
    if(
      !$frm_HitID or  
      !$frm_Sequence
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO Peptide SET 
          HitID='$frm_HitID', 
          Charge='$frm_Charge',
          MZ='$frm_MZ'; 
          MASS='$frm_MASS', 
          Location='$frm_Location', 
          Expect='$frm_Expect', 
          Expect='$frm_Expect2', 
          Sequence='$frm_Sequence',
          IonFile='$frm_IonFile',
          Miss='$frm_Miss',
          Modifications='$Modifications',
          Status='$frm_Status'";
      //echo $SQL;
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM Peptide WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update_(
         $frm_ID=0, 
         $frm_HitID=0, 
         $frm_Charge=0,
         $frm_MZ=0, 
         $frm_MASS=0, 
         $frm_Location='', 
         $frm_Expect=0, 
         $frm_Expect2=0, 
         $frm_Sequence='',
         $frm_IonFile,
         $frm_Miss,
         $frm_Modifications,
         $frm_Status){
      $SQL ="UPDATE Peptide SET 
         HitID='$frm_HitID', 
         Charge='$frm_Charge',
         MZ='$frm_MZ', 
         MASS='$frm_MASS', 
         Location='$frm_Location', 
         Expect='$frm_Expect', 
         Expect='$frm_Expect2', 
         Sequence='$frm_Sequence',
         IonFile='$frm_IonFile',
         Miss='$frm_Miss',
         Modifications='$Modifications',
         Status='$frm_Status'
         WHERE ID =$frm_ID ";
      mysqli_query($this->link, $SQL);
   }//end of function update
}//end of class
?>
