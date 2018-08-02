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

class PagePermission{
  var $PageID;
  var $UserID;
  var $Insert;
  var $Modify;
  var $Delete;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function PagePermission( ){
    global $PROHITSDB;
    $this->link = $PROHITSDB->link;
  }//function end

  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="") {
    if($ID){
      $this->ID = $ID;
      $SQL = "SELECT 
          PageID, 
          UserID, 
          Insert, 
          Modify, 
          Delete
          FROM PagePermission where  ID='$this->ID'";
       list(
          $this->PageID,
          $this->UserID,
          $this->Insert,
          $this->Modify,
          $this->Delete) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall() {
    $SQL = "SELECT 
         PageID, 
         UserID, 
         Insert, 
         Modify, 
         Delete
         FROM PagePermission";
    $SQL .= " ORDER BY PageID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->PageID[$i], 
         $this->UserID[$i], 
         $this->Insert[$i], 
         $this->Modify[$i], 
         $this->Delete[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_PageID=0, 
       $frm_UserID=0, 
       $frm_Insert=0, 
       $frm_Modify=0, 
       $frm_Delete=0){
    if(
      !$frm_PageID or 
      !$frm_UserID or 
      !$frm_Insert or 
      !$frm_Modify or 
      !$frm_Delete
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO PagePermission SET 
          PageID='$frm_PageID', 
          UserID='$frm_UserID', 
          Insert='$frm_Insert', 
          Modify='$frm_Modify', 
          Delete='$frm_Delete'";
      mysqli_query($this->link, $SQL);

    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM PagePermission WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_PageID=0, 
         $frm_UserID=0, 
         $frm_Insert=0, 
         $frm_Modify=0, 
         $frm_Delete=0){
      $SQL ="UPDATE PagePermission SET 
         PageID='$frm_PageID', 
         UserID='$frm_UserID', 
         Insert='$frm_Insert', 
         Modify='$frm_Modify', 
         Delete='$frm_Delete'
         WHERE ID =$frm_ID ";
      mysqli_query($this->link, $SQL);
   }//end of function update
}//end of class
?>

