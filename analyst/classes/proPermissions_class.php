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

class ProPermission{
  var $ProjectID;
  var $UserID;
  var $perm_insert;
  var $perm_modify;
  var $perm_delete;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function ProPermission( ){
    global $HITSDB;
    $this->link = $HITSDB->link;
  }//function end

  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="") {
    if($ID){
      $this->ID = $ID;
      $SQL = "SELECT 
          ProjectID, 
          UserID, 
          perm_insert, 
          perm_modify, 
          perm_delete
          FROM ProPermission where  ID='$this->ID'";
       list(
          $this->ProjectID,
          $this->UserID,
          $this->perm_insert,
          $this->perm_modify,
          $this->perm_delete) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall() {
    $SQL = "SELECT 
         ProjectID, 
         UserID, 
         perm_insert, 
         perm_modify, 
         perm_delete
         FROM ProPermission";
    $SQL .= " ORDER BY ProjectID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ProjectID[$i], 
         $this->UserID[$i], 
         $this->perm_insert[$i], 
         $this->perm_modify[$i], 
         $this->perm_delete[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_ProjectID=0, 
       $frm_UserID=0, 
       $frm_perm_insert='', 
       $frm_perm_modify='', 
       $frm_perm_delete=''){
    if(
      !$frm_ProjectID or 
      !$frm_UserID or 
      !$frm_perm_insert or 
      !$frm_perm_modify or 
      !$frm_perm_delete
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO ProPermission SET 
          ProjectID='$frm_ProjectID', 
          UserID='$frm_UserID', 
          perm_insert='$frm_perm_insert', 
          perm_modify='$frm_perm_modify', 
          perm_delete='$frm_perm_delete'";
      mysqli_query($this->link, $SQL);

    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM ProPermission WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_ProjectID=0, 
         $frm_UserID=0, 
         $frm_perm_insert='', 
         $frm_perm_modify='', 
         $frm_perm_delete=''){
      $SQL ="UPDATE ProPermission SET 
         ProjectID='$frm_ProjectID', 
         UserID='$frm_UserID', 
         perm_insert='$frm_perm_insert', 
         perm_modify='$frm_perm_modify', 
         perm_delete='$frm_perm_delete'
         WHERE ID =$frm_ID ";
      mysqli_query($this->link, $SQL);
   }//end of function update
}//end of class
?>

