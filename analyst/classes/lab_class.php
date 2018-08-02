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

class Lab{
  var $ID;
  var $SupervisorID;
  var $Name;
  var $link;
  var $count;
  function Lab($ID=""){
    global $PROHITSDB;
    $this->link = $PROHITSDB->link;
  	if($ID) $this->fetch($ID);
  }
  //---------------------------------------------
  function fetch($ID="") {
  //----------------------------------------------
    if($ID){
      $this->ID = $ID;
       $SQL = "SELECT 
          ID, 
          SupervisorID, 
          Name 
          FROM Lab where  ID='$this->ID'";
       list(
          $this->ID,
          $this->SupervisorID,
          $this->Name) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  function fetchall() {
  //----------------------------------------------
    $SQL = "SELECT 
         ID, 
         SupervisorID, 
         Name 
         FROM Lab";

    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
	$i = 0;
    while (list(
         $this->ID[$i], 
         $this->SupervisorID[$i], 
         $this->Name[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  function insert(
  //----------------------------------------------
       $frm_SupervisorID=0, 
       $frm_Name=''){
    if(
      !$frm_SupervisorID or 
      !$frm_Name
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO Lab SET 
          SupervisorID='$frm_SupervisorID', 
          Name='$frm_Name'";
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
  }//end insert


}//end of class
?>