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


class Filter{
  var $FilerNameID;
  var $FilterSetID;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Filter(){
    global $PROHITSDB;
    $this->link = $PROHITSDB->link;
  }//function end
  
  
  function fetchallFilterSets() {
    
    $allSets = Array();    
  
    $SQL = "SELECT 
         ID, 
         Name
         FROM FilerSet";
    $SQL .= " ORDER BY ID";
    $i = 0;
    echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while ($allSets[$i]= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
    return $allSets;
  }//end of function fetchall

  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="") {
    if($ID){
      $this->ID = $ID;
      $SQL = "SELECT 
          FilerNameID, 
          FilterSetID
          FROM Filter where  ID='$this->ID'";
       list(
          $this->FilerNameID,
          $this->FilterSetID) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall() {
    $SQL = "SELECT 
         FilerNameID, 
         FilterSetID
         FROM Filter";
    $SQL .= " ORDER BY FilerNameID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->FilerNameID[$i], 
         $this->FilterSetID[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_FilerNameID=0, 
       $frm_FilterSetID=0){
    if(
      !$frm_FilerNameID or 
      !$frm_FilterSetID
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO Filter SET 
          FilerNameID='$frm_FilerNameID', 
          FilterSetID='$frm_FilterSetID'";
      mysqli_query($this->link, $SQL);

    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM Filter WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_FilerNameID=0, 
         $frm_FilterSetID=0){
      $SQL ="UPDATE Filter SET 
         FilerNameID='$frm_FilerNameID', 
         FilterSetID='$frm_FilterSetID'
         WHERE ID =$frm_ID ";
      mysqli_query($this->link, $SQL);
   }//end of function update
}//end of class
?>

