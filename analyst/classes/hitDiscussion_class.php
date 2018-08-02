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

class HitDiscussion{
  var $ID;
  var $HitID;
  var $Note;
  var $UserID;
  var $DateTime;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function HitDiscussion($ID="") {
    
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
          Note, 
          UserID, 
          DateTime
          FROM HitDiscussion where  ID='$this->ID'";
       list(
          $this->ID,
          $this->HitID,
          $this->Note,
          $this->UserID,
          $this->DateTime) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall($Hit_ID='') {
    if($Hit_ID){
      $SQL = "SELECT 
           ID, 
           HitID, 
           Note, 
           UserID, 
           DateTime
           FROM HitDiscussion";
      $SQL .= " where HitID='$Hit_ID' ORDER BY ID ";
      $i = 0;
      //echo $SQL;
      $sqlResult = mysqli_query($this->link, $SQL);
      $this->count = mysqli_num_rows($sqlResult);
      while (list(
           $this->ID[$i], 
           $this->HitID[$i], 
           $this->Note[$i], 
           $this->UserID[$i], 
           $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
         $i++;
      }
    }//end if
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert($frm_HitID=0,$frm_Note='', $frm_UserID=0){
    if(
      !$frm_HitID or 
      !$frm_Note or 
      !$frm_UserID
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO HitDiscussion SET 
          HitID='$frm_HitID', 
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
  function delete($ID = '', $USER_id){
    if($ID and $USER_id) {
      $SQL = "DELETE FROM HitDiscussion WHERE ID = '$ID' and UserID='$USER_id'";
      mysqli_query($this->link, $SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update($HitDisID='', $theHitNote='', $USER_id=0){
     $SQL ="UPDATE HitDiscussion SET 
         Note='$theHitNote', 
         UserID='$USER_id', 
         DateTime=now() 
         WHERE ID = '$HitDisID'";
      mysqli_query($this->link, $SQL);
   }//end of function update
}//end of class
?>
