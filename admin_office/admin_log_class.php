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

/***************************************************************************
    Author: frank
    2002-04-22
    Desc: This calss for table AdminLog which record all user's actions
 +----------------+---------------+------+-----+---------+----------------+
 | Field          | Type          | Null | Key | Default | Extra          |
 +----------------+---------------+------+-----+---------+----------------+
 | ID             |int(11)        |not_null primary_key auto_increment
 | UserID         |int(11)        |
 | MyTable        |string(25)     |
 | RecordID       |int(11)        |
 | MyAction       |string(25)     |
 | Description    |string(255)    |
 | TS             |timestamp(14)  |not_null unsigned zerofill timestamp
 +----------------+---------------+------+-----+---------+----------------+
****************************************************************************/

class AdminLog{
  var $ID;
  var $UserID;
  var $MyTable;
  var $RecordID;
  var $MyAction;
  var $Description;
  var $TS;
  var $link;
  var $count;
  
  function AdminLog(){
    global $PROHITSDB;
     
    $this->link = $PROHITSDB->link;
  }
  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall() {
    
    $SQL = "SELECT 
         ID, 
         UserID, 
         MyTable, 
         RecordID, 
         MyAction, 
         Description, 
         TS
         FROM AdminLog";
    $SQL .= " ORDER BY ID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->UserID[$i], 
         $this->MyTable[$i], 
         $this->RecordID[$i], 
         $this->MyAction[$i], 
         $this->Description[$i], 
         $this->TS[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_UserID=0, 
       $frm_MyTable='', 
       $frm_RecordID=0, 
       $frm_MyAction='', 
       $frm_Description=''){
    if(
      !$frm_UserID or 
      !$frm_MyTable  
     ){
      echo "missing info: ... insert into AdminLog aborted.";
    //  exit;
    }else{
       
      $SQL ="INSERT INTO AdminLog SET 
          UserID='$frm_UserID', 
          MyTable='$frm_MyTable', 
          RecordID='$frm_RecordID', 
          MyAction='$frm_MyAction', 
          Description='$frm_Description' ";
      //echo $SQL;              
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
  }//end insert
  //----------------------------------------------
  // call from mng_set_frequency.php
  //----------------------------------------------
  function get_frequency_update_time(){
     
    $SQL = "SELECT Description FROM AdminLog where MyAction='modify' 
    and MyTable='YeastFrequency' order by ID desc limit 1 ";
    $result = mysqli_query($this->link, $SQL);
    $row = mysqli_fetch_row($result);
    return $row[0];
  }
}//end of class
?>
