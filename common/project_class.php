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

class Projects{
  var $ID;
  var $Name;
  var $TaxID;
  var $FilterSetID;
  var $DBname;
  var $Frequency;
  var $Description;
  var $LabID;
  var $Date;
  var $db_link;
  var $count;
  var $AccessProjectID;
  
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Projects(  $ID="", $db_link='') {
    if(is_object($ID)){
      $db_link = $ID;
      $ID = '';
    }
    if($db_link){
      $this->db_link = $db_link;
    }else{
      global $PROHITSDB;
      $this->db_link = $PROHITSDB->link;
    }
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
          TaxID,
          FilterSetID,
          DBname,
          Frequency, 
          Description, 
          LabID, 
          Date
          FROM Projects where ID=$this->ID";
       //echo $SQL;   
       list(
          $this->ID,
          $this->Name,
          $this->TaxID,
          $this->FilterSetID,
          $this->DBname,
          $this->Frequency, 
          $this->Description,
          $this->LabID,
          $this->Date) = mysqli_fetch_array(mysqli_query($this->db_link, $SQL));
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
         TaxID, 
         FilterSetID,
         DBname,
         Frequency,
         Description, 
         LabID, 
         Date
         FROM Projects";
    $SQL .= " ORDER BY ID";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->db_link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while ($row = mysqli_fetch_row($sqlResult) ) {
      list(
         $this->ID[$i], 
         $this->Name[$i],
         $this->TaxID[$i],
         $this->FilterSetID[$i],
         $this->DBname[$i],
         $this->Frequency[$i],
         $this->Description[$i], 
         $this->LabID[$i], 
         $this->Date[$i]
           )= $row;
      $i++;
    }
    
    
  }//end of function fetchall
  //----------------------------------------------
  //  get all project the user has permission to 
  //  access
  //----------------------------------------------
  
  function getAccessProjects($UserID=0){
 
    if(!$UserID) {echo "missing info in get access project function"; exit;}
    $this->AccessProjectID = '';
    $SQL = "SELECT P.ID, P.Name, P.TaxID, P.FilterSetID, P.DBname, P.Frequency, P.Description, P.LabID, P.Date FROM Projects P, 
            ProPermission M where P.ID=M.ProjectID and M.UserID=$UserID order by P.ID"; 
    $sqlResult = mysqli_query($this->db_link, $SQL);
    $this->count = mysqli_num_rows($sqlResult); 
    $i = 0;
    while ($row = mysqli_fetch_row($sqlResult) ) {
       list(
            $this->ID[$i], 
            $this->Name[$i],
            $this->TaxID[$i],
            $this->FilterSetID[$i], 
            $this->DBname[$i],
            $this->Frequency[$i],
            $this->Description[$i], 
            $this->LabID[$i], 
            $this->Date[$i]) =  $row;
      $i++;
    }
     
  }//end functoin
  //----------------------------------------------
  //  only get user selected projects
  //----------------------------------------------
  function getSelectedProjects($SelectedProjects=''){
    global $AccessAllData;
    if($AccessAllData == 'Yes'){
      global $USER;
      $this->getAccessProjects($USER->id);
      return '';
    }
    if(!$SelectedProjects) { return '';}
    $SQL = "SELECT 
         ID, 
         Name,
         TaxID,
         FilterSetID,
         DBname,
         Frequency,
         Description, 
         LabID, 
         Date
         FROM Projects";
    $SQL .= " where ID in($SelectedProjects)";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->db_link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while ($row = mysqli_fetch_row($sqlResult) ) {
       list(
         $this->ID[$i], 
         $this->Name[$i],
         $this->TaxID[$i],
         $this->FilterSetID[$i],
         $this->DBname[$i],
         $this->Frequency[$i],
         $this->Description[$i], 
         $this->LabID[$i], 
         $this->Date[$i])= $row;
       $i++;
    }
     
    
  }//end functoin
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_Name='',
       $frm_TaxID='',
       $frm_FilterSetID='',
       $frm_DBname='',
       $frm_Frequency='',
       $frm_Description='',
       $frm_LabID=0, 
       $frm_Date=''){
    if(
      !$frm_Name or
      !$frm_TaxID or
      !$frm_FilterSetID or
      !$frm_DBname or
      !$frm_LabID     
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO Projects SET 
          Name='".mysqli_escape_string($this->db_link, $frm_Name)."',
          TaxID='$frm_TaxID',
          FilterSetID='$frm_FilterSetID',
          DBname='$frm_DBname',
          Frequency='$frm_Frequency',           
          Description='".mysqli_escape_string($this->db_link, $frm_Description)."', 
          LabID='$frm_LabID', 
          Date='$frm_Date'";
      $sqlResult = mysqli_query($this->db_link, $SQL);
      $this->ID = mysqli_insert_id($this->db_link);
    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID and !$this->isUsed($ID)) {
      $SQL = "DELETE FROM Projects WHERE ID = '$ID'";
      mysqli_query($this->db_link, $SQL);
    }else{
      echo "Need id to delete!!!";
    }  
  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
     $frm_ID=0, 
     $frm_Name='',
     $frm_TaxID='',
     $frm_FilterSetID='',
     $frm_DBname='',
     $frm_Frequency='',          
     $frm_Description='', 
     $frm_LabID=0, 
     $frm_Date=''){
     $SQL ="UPDATE Projects SET 
         Name='".mysqli_escape_string($this->db_link,$frm_Name)."',
         TaxID='$frm_TaxID',
         FilterSetID='$frm_FilterSetID',
         DBname='$frm_DBname',
         Frequency='$frm_Frequency',         
         Description='".mysqli_escape_string($this->db_link,$frm_Description)."', 
         LabID='$frm_LabID', 
         Date='$frm_Date'
         WHERE ID =$frm_ID ";
      //echo $SQL;exit;   
      mysqli_query($this->db_link, $SQL);
   }//end of function update
   //----------------------------------------
   // used by project editor
   //----------------------------------------
   function isUsed($ProID = 0){
     $rt = false;
     if(!$ProID) return $rt;
     return $rt;
   }
}//end of class
?>
