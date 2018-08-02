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

class HitNote{
  var $HitID;
  var $HitNoteTypeID;
  var $Note;
  var $UserID;
  var $DateTime;
	
	var $HitNoteTypeName; //fetchall_one_hit()
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function HitNote($Hit_ID=0, $HitNoteTypeID=0){
    global $HITSDB;
    $this->link = $HITSDB->link;
		if($Hit_ID and $HitNoteTypeID) $this->fetch($Hit_ID, $HitNoteTypeID);
  }//function end

  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($Hit_ID, $HitNoteTypeID) {
    if($Hit_ID and $HitNoteTypeID){
      $SQL = "SELECT 
          HitID, 
          HitNoteTypeID, 
          Note, 
          UserID,
					DateTime 
          FROM HitNote where  HitID='$Hit_ID' and HitNoteTypeID='$HitNoteTypeID'";
			 $results = mysqli_query($this->link, $SQL);
       list(
          $this->HitID,
          $this->HitNoteTypeID,
          $this->Note,
          $this->UserID,
					$this->DateTime) = mysqli_fetch_array($results);
       $this->count = mysqli_num_rows($results);
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall_one_hit($Hit_ID=0) {
   $this->HitNoteTypeID = array();
   $this->HitNoteTypeName = array();
	 if($Hit_ID){
			$SQL = "SELECT H.HitID, H.HitNoteTypeID, T.Name , H.UserID, H.Note, H.DateTime from HitNote H, HitNoteType T where H.HitNoteTypeID=T.ID and H.HitID='$Hit_ID'";
		
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->HitID[$i], 
         $this->HitNoteTypeID[$i], 
         $this->HitNoteTypeName[$i],
         $this->UserID[$i],
         $this->Note[$i],
         $this->DateTime[$i] )= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
	 }else{
	 	echo "Error: no Hit ID passed";exit;
	 }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert($frm_HitID=0, $frm_HitNoteTypeID=0, $frm_Note='', $frm_UserID=0){
    if(!$frm_HitID or !$frm_HitNoteTypeID ){
      echo "missing info: ... insert aborted from HitNote.";
      exit;
    }else{
      $SQL ="INSERT INTO HitNote SET 
          HitID='$frm_HitID', 
          HitNoteTypeID='$frm_HitNoteTypeID', 
          Note='$frm_Note', 
          UserID='$frm_UserID',
					DateTime=now()";
      //echo $SQL;
      @mysqli_query($this->link, $SQL);
    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($Hit_ID, $HitNoteTypeID, $User_id) {
	  $SQL = "select * from HitNote WHERE HitID='$Hit_ID' and HitNoteTypeID='$HitNoteTypeID' and UserID='$User_id'";
		//echo $SQL;
    if(mysqli_num_rows(mysqli_query($this->link, $SQL))) {
      $SQL = "DELETE FROM HitNote WHERE HitID = '$Hit_ID' and HitNoteTypeID='$HitNoteTypeID'";
      mysqli_query($this->link, $SQL);
      return 1;
    }else{
      return 0;
    }  
  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update($Hit_ID=0, $HitNoteTypeID=0, $oldHitNoteTypeID=0, $HitNote='', $User_id ){
	  if($this->delete($Hit_ID, $oldHitNoteTypeID, $User_id) ){
			$this->insert($Hit_ID, $HitNoteTypeID, $HitNote, $User_id);
		}
  }//end of function update
}//end of class
?>