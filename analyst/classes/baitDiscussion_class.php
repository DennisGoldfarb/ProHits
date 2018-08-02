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

class BaitDiscussion{
  var $ID;
  var $BaitID;
  var $Note;
  var $NoteType;
  var $UserID;
  var $DateTime;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function BaitDiscussion( $ID="", $UserID="") {
    global $HITSDB;
    $this->link = $HITSDB->link;
    if($ID and $UserID) {
      $this->fetch($ID, $UserID);
    }
  }//function end

  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="", $UserID) {
    if($ID){
      $this->ID = $ID;
      $SQL = "SELECT 
          ID, 
          BaitID, 
          NoteType, 
          Note, 
          UserID, 
          DateTime
          FROM BaitDiscussion where  ID='$this->ID' and UserID='$UserID'";
       list(
          $this->ID,
          $this->BaitID,
          $this->NoteType,
          $this->Note,
          $this->UserID,
          $this->DateTime) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall($BaitID=0) {
    $SQL = "SELECT 
         ID, 
         BaitID, 
         NoteType, 
         Note, 
         UserID, 
         DateTime
         FROM BaitDiscussion where BaitID='$BaitID'";
    $SQL .= " ORDER BY ID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->BaitID[$i], 
         $this->NoteType[$i],
         $this->Note[$i], 
         $this->UserID[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert($frm_BaitID=0,$frm_NoteType=0, $frm_Note='', $frm_UserID=0){
    if(
      !$frm_BaitID or 
      !$frm_Note or 
      !$frm_UserID 
     ){
      echo "missing info: ... insert into BaitDiscussion table aborted.";
      exit;
    }else{
      if($frm_NoteType == 1 and $this->fialedBait($frm_BaitID)){
        return;
      }
      $SQL ="INSERT INTO BaitDiscussion SET 
          BaitID='$frm_BaitID', 
          NoteType='$frm_NoteType',
          Note='$frm_Note', 
          UserID='$frm_UserID', 
          DateTime=now()";
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID, $UserID) {
      $SQL = "DELETE FROM BaitDiscussion WHERE ID='$ID' and UserID='$UserID'";
      mysqli_query($this->link, $SQL);
  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update($frm_disID, $frm_Note, $UserID){
     $SQL ="UPDATE BaitDiscussion SET 
         Note='$frm_Note',
         DateTime=now() 
         WHERE ID=$frm_disID and UserID='$UserID'";
      mysqli_query($this->link, $SQL);
   }//end of function update
   
   function fialedBait($bait_ID=0){
    if($bait_ID){
      $SQL = "select ID from BaitDiscussion where BaitID='$bait_ID' and NoteType='1'";
      return mysqli_num_rows(mysqli_query($this->link, $SQL));
    }
    return false;
   }
    
}//end of class
?>
