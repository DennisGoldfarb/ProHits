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


class Condition{
  var $ID;
  var $Condition;  
  var $count;
  var $link;
 
 function Condition($link = ''){
   if($link){
    $this->link = $link;
   }else{
    global $HITSDB;
    $this->link = $HITSDB->link;
   }
   
 }
  //----------------------------------------------
  //	 get all records in Condition table
  //----------------------------------------------
  function get_all_conditions() {
    $SQL = "SELECT ID, `Condition` FROM `Condition`";
    $SQL .= " ORDER BY ID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL, $this->link);
    $this->count = mysqli_num_rows($sqlResult);
    //$this->count = count($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->Condition[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }
  //---------------------------------------------
  // used by UploadMDS.php
  //---------------------------------------------
  function Get_condition_name($name){
    $SQL = "select ID, `Condition` from `Condition` where `Condition` like '%$name%'";
    list($this->ID,$this->Condition)= mysqli_fetch_row(mysqli_query($this->link, $SQL));
  }
  //----------------------------------------------
  //  get condidtions link to specific Experiment
  //----------------------------------------------
  function fetchall($Exp_ID='') {
    if($Exp_ID){
	    $SQL = "SELECT C.ID, C.`Condition`  FROM `Condition` C , ExpCondition E WHERE C.ID = E.ConditionID and E.ExpID = $Exp_ID ";
	    $SQL .= " ORDER BY C.ID";
	    $i = 0;
      
      $sqlResult = mysqli_query($this->link, $SQL,$this->link);
	    $this->count = mysqli_num_rows($sqlResult);
	    while (list(
	         $this->ID[$i], 
	         $this->Condition[$i])= mysqli_fetch_row($sqlResult) ) {
	       $i++;
        }
	}else{
		// do nothing
	}
  }//end of function fetchall
  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($Exp_ID) {
     if($Exp_ID) {
        $SQL = "DELETE FROM ExpCondition WHERE ExpID = $Exp_ID";
        mysqli_query($this->link, $SQL, $this->link);
     }else{
        echo "Error: Need id to delete from Expcondition table!!!";
     }  
  }//end of delete function
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert($Exp_ID=0, $frm_ConditionID=''){
    if( !$frm_ConditionID or !$Exp_ID ){
      echo "Error: missing info: ... insert into Expcondtion aborted.";
      exit;
    }else{
     $SQL ="INSERT INTO ExpCondition SET 
	      ExpID = '$Exp_ID',
          ConditionID='$frm_ConditionID'";
      mysqli_query($this->link, $SQL, $this->link);
    }
  }//end insert

 }//end of class
?>
