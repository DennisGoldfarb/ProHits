<?php
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

class Log{
  var $ID;
  var $UserID;
  var $MyTable;
  var $RecordID;
  var $MyAction;
  var $Description;
  var $ProjectID;
  var $TS;
  var $link;

  var $count;
  
  function Log($db_link='') {
    if($db_link){
      $this->link = $db_link;
    }else{
      global $PROHITSDB;
      $this->link = $PROHITSDB->link;
    }
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
         ProjectID, 
         TS
         FROM Log";
    $SQL .= " ORDER BY ID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while ($row = mysqli_fetch_row($sqlResult) ) {
      list(
         $this->ID[$i], 
         $this->UserID[$i], 
         $this->MyTable[$i], 
         $this->RecordID[$i], 
         $this->MyAction[$i], 
         $this->Description[$i],
         $this->ProjectID[$i], 
         $this->TS[$i])= $row;
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
       $frm_Description='',
       $frm_ProjectID=0){
    if(
      !$frm_UserID or 
      !$frm_MyTable or
      !$frm_ProjectID 
     ){
      echo "missing info: ... insert into Log aborted.";
    //  exit;
    }else{
      $SQL ="INSERT INTO Log SET 
          UserID='$frm_UserID', 
          MyTable='$frm_MyTable', 
          RecordID='$frm_RecordID', 
          MyAction='$frm_MyAction', 
          Description='$frm_Description',
          ProjectID='$frm_ProjectID' ";
       
        mysqli_query($this->link, $SQL);
        $this->ID = mysqli_insert_id($this->link);
       
    }
  }//end insert
   
}//end of class
?>
