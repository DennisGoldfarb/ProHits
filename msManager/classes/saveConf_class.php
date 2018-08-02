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

class SaveConf{
  var $table;
  var $link;
  
  var $ID;
  var $TaskID;
  var $Mascot_SaveScore;
  var $Mascot_SaveValidation;
  var $Status;
  var $SaveBy;
  var $SetDate;
  var $Mascot_SaveWell_str;
  var $GPM_SaveWell_str;
  var $Mascot_Other_Value;
  var $GPM_Value;
  var $SEQUEST_SaveWell_str;
  var $SEQUEST_Value;
  var $TppTaskID;
  var $Tpp_Value;
  var $Tpp_SaveWell_str;
  var $DECOY_prefix;
  
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function SaveConf($tableName, $link) {
    $this->table = $tableName . "SaveConf";
    $this->link = $link;
  }//function end

  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch_task($taskID="") {
    if($taskID){
      $this->TaskID = $taskID;
      $SQL = "SELECT 
          ID, 
          TaskID, 
          Mascot_SaveScore, 
          Mascot_SaveValidation, 
          Status, 
          SaveBy, 
          SetDate, 
          Mascot_SaveWell_str, 
          GPM_SaveWell_str, 
          Mascot_Other_Value, 
          GPM_Value,
          SEQUEST_SaveWell_str,
          SEQUEST_Value,
          TppTaskID,
          Tpp_Value,
          Tpp_SaveWell_str,
          DECOY_prefix 
        FROM $this->table where TaskID='$this->TaskID' order by ID desc limit 1";
       //echo $SQL;
       list(
          $this->ID,
          $this->TaskID,
          $this->Mascot_SaveScore,
          $this->Mascot_SaveValidation,
          $this->Status,
          $this->SaveBy,
          $this->SetDate,
          $this->Mascot_SaveWell_str,
          $this->GPM_SaveWell_str,
          $this->Mascot_Other_Value,
          $this->GPM_Value,
          $this->SEQUEST_SaveWell_str,
          $this->SEQUEST_Value,
          $this->TppTaskID,
          $this->Tpp_Value,
          $this->Tpp_SaveWell_str,
          $this->DECOY_prefix) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
      $this->count = 1;
    }
     //echo $SQL;
  } //end of function fetch
   
  function setStatus($status){
    $SQL = "update $this->table set Status='$status' where ID='".$this->ID."'";
     
    mysqli_query($this->link, $SQL);
  }
}//end of class
?>
