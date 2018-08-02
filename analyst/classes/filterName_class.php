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

class FilterName{
  var $ID;
  var $Name;
  var $Color;
  var $Type;
  var $link;
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function FilterName( $ID="") {
    global $PROHITSDB;
    $this->link = $PROHITSDB->link;
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
          Name, 
          Color, 
          Type
          FROM FilterName where  ID='$this->ID'";
       list(
          $this->ID,
          $this->Name,
          $this->Color,
          $this->Type) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall() {
    $SQL = "SELECT 
         ID, 
         Name, 
         Color, 
         Type
         FROM FilterName";
    $SQL .= " ORDER BY ID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->Name[$i], 
         $this->Color[$i], 
         $this->Type[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_Name='', 
       $frm_Color='', 
       $frm_Type=''){
    if(
      !$frm_Name or 
      !$frm_Color or 
      !$frm_Type
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO FilterName SET 
          Name='$frm_Name', 
          Color='$frm_Color', 
          Type='$frm_Type'";
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM FilterName WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_ID=0, 
         $frm_Name='', 
         $frm_Color='', 
         $frm_Type=''){
      $SQL ="UPDATE FilterName SET 
         Name='$frm_Name', 
         Color='$frm_Color', 
         Type='$frm_Type'
         WHERE ID =$frm_ID ";
      mysqli_query($this->link, $SQL);
   }//end of function update
}//end of class
?>

