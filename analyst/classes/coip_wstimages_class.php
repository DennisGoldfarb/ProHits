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


class Coip_WSTimages{
  var $ID;
  var $Image;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Coip_WSTimages( $ID="") {
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
          Image
          FROM Coip_WSTimages where  ID='$this->ID'";
       list(
          $this->ID,
          $this->Image) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall() {
    $SQL = "SELECT 
         ID, 
         Image
         FROM Coip_WSTimages";
    $SQL .= " ORDER BY ID desc";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->Image[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert( $frm_Image=''){
    if(
      !$frm_Image
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO Coip_WSTimages SET 
          Image='$frm_Image'";
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
    return $this->ID;
  }//end insert
  //---------------------------------------------
  //update
  //---------------------------------------------
  function update($ID,$image){
     $SQL = "update Coip_WSTimages SET Image='$image' where ID='$ID'";
     //echo $SQL;
     mysqli_query($this->link, $SQL);
     return mysqli_affected_rows($this->link);
  }
}//end of class
?>
