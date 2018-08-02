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

class Storage{
  var $ID;
  var $FileName;
  var $FileType;
  var $FolderID;
  var $Date;
  var $User;
  var $ProhitsID;
  var $ProjectID;
  var $Size;
  var $Link;
  var $TableName;
  var $ConvertParameter;
  var $RAW_ID;

  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Storage( $link = '', $tableName="") {
    if($link){
      $this->Link = $link;
    }else{
      global $managerDB;
      $this->Link = $managerDB->link;
    }
    $this->TableName = $tableName;
    if(!$this->TableName){
      echo "table name is required in class LTQ";exit;
    }
  }//function end
  function _query($SQL){
    return mysqli_query($this->Link, $SQL);
  }
  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="") {
    if($ID){
      $this->ID = $ID;
      $SQL = "SELECT 
          ID, 
          FileName, 
          FileType, 
          FolderID,
          Date, 
          User,
          ProhitsID,
          ProjectID,
          Size,
          ConvertParameter,
          RAW_ID
          FROM $this->TableName where  ID='$this->ID'";
      //echo "=$SQL=";
       
       list(
          $this->ID,
          $this->FileName,
          $this->FileType,
          $this->FolderID,
          $this->Date,
          $this->User,
          $this->ProhitsID,
          $this->ProjectID,
          $this->Size,
          $this->ConvertParameter,
          $this->RAW_ID) = mysqli_fetch_array($this->_query($SQL));
       $this->count = 1;
     }
  } //end of function fetch
  
  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall($order_by='',$start_point=0,$RESULTS_PER_PAGE=200, $where_project='',$year='',$month='',$day='', $folderID=0) {
    global $RAW_FILES;
    
    $SQL = "SELECT 
         ID, 
         FileName, 
         FileType, 
         FolderID, 
         Date, 
         User,
         ProhitsID,
         ProjectID,
         Size,
         ConvertParameter,
         RAW_ID,
         ConvertParameter,
         RAW_ID
         FROM $this->TableName where FolderID=$folderID";
         
     if($RAW_FILES){
      $RAW_FILES_tmp = preg_replace("/\s+/", "", $RAW_FILES);
      $in_type = "'". preg_replace("/,/", "','", $RAW_FILES_tmp) . "','dir'";
       $SQL .= " and FileType in(".$in_type.")";
     }
     if($where_project){
      $SQL .= " and $where_project";
     }
     if($day){
      if(strlen($month) == 1) $month = "0".$month;
      if(strlen($day) == 1) $day = "0".$day;
      $SQL .= " and Date like '".$year."-".$month."-".$day."%'";
     }else if($month){
      if(strlen($month) == 1) $month = "0".$month;
      $SQL .= " and Date like '".$year."-".$month."%'";
     }else if($year){
      $SQL .= " and Date like '".$year."%'";
     }
    if($folderID){
      $SQL .= " and FileType <> 'sld'";
    }
    if($order_by){
       $SQL .= " ORDER BY $order_by ";
    }
    if($start_point or $RESULTS_PER_PAGE){
      $SQL .= " Limit $start_point,$RESULTS_PER_PAGE ";
    }
    $i = 0;
//echo $SQL;
    $sqlResult = $this->_query($SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->FileName[$i], 
         $this->FileType[$i], 
         $this->FolderID[$i], 
         $this->Date[$i], 
         $this->User[$i],
         $this->ProhitsID[$i],
         $this->ProjectID[$i],
         $this->Size[$i],
         $this->ConvertParameter[$i],
         $this->RAW_ID[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
   
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_FileName='', 
       $frm_FileType='', 
       $frm_FolderID=0,
       $frm_Date='', 
       $frm_User='',
       $frm_ProhitsID='',
       $frm_ProjectID,
       $frm_Size=''){
    if(
      !$frm_FileName or 
      !$frm_FileType or 
      !$frm_Date or 
      !$frm_User or
      !$frm_ProhitsID or
      !$frm_ProjectID
     ){
      echo "missing info: ... insert aborted.";
      //exit;
    } 
      $SQL ="INSERT INTO $this->TableName SET 
          FileName='$frm_FileName', 
          FileType='$frm_FileType', 
          FolderID='$frm_FolderID',
          Date='$frm_Date', 
          User='$frm_User',
          ProhitsID='$frm_ProhitsID',
          ProjectID='$frm_ProjectID',
          Size='$frm_Size'";
      //echo $SQL; exit;
      $this->_query($SQL);
      
      $this->ID = mysqli_insert_id($this->Link);
       
     
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM $this->TableName WHERE ID = '$ID'";
      $this->_query($SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_ID=0, 
         $frm_FileName='', 
         $frm_FileType='', 
         $frm_FolderID=0,
         $frm_Date='', 
         $frm_User='',
         $frm_ProhitsID='',
         $frm_ProjectID='',
         $frm_Size=''){
      $SQL ="UPDATE $this->TableName SET 
         FileName='$frm_FileName', 
         FileType='$frm_FileType', 
         FolderID='$frm_FolderID',
         Date='$frm_Date', 
         User='$frm_User',
         ProhitsID='$frm_ProhitsID',
         ProjectID='$frm_ProjectID',
         Size='$frm_Size',
         WHERE ID =$frm_ID ";
      $this->_query($SQL);
  }//end of function update
   //----------------------------------------------
   // for page counter in "../ms_storage_$this->TableName.php"
   //----------------------------------------------
  function get_user_total($where_project, $year='', $month='', $day='', $folderID=0){
     $SQL = "select ID from $this->TableName where FolderID=$folderID and $where_project";
     
     if($day){
      if(strlen($day) == 1) $day = "0".$day;
      $SQL .= " and Date='".$year."-".$month."-".$day."'";
     }else if($month){
      if(strlen($month) == 1) $month = "0".$month;
      $SQL .= " and Date like '".$year."-".$month."%'";
     }else if($year){
      $SQL .= " and Date like '".$year."%'";
     }
     //echo $SQL;
     return mysqli_num_rows($this->_query($SQL));
  }
  //------------------------------------------------
  // for "ms_storage_$this->TableName.php"
  //------------------------------------------------
  function fetch_users(){
    $SQL = "select distinct User from $this->TableName order by User;";
    $results = $this->_query($SQL);
    $this->count = mysqli_num_rows($results);
    $i = 0;
    while( list($this->User[$i]) = mysqli_fetch_row($results) ){
     $i++;
    }
  }
  //----------------------------------------------
  // ms_storage_raw_data.php
  //----------------------------------------------
  function has_kids($folderID){
    $rt = false;
    $SQL = "SELECT ID FROM $this->TableName WHERE FolderID=$folderID"; 
      $sqlResult = $this->_query($SQL);
    if(mysqli_num_rows($sqlResult) ){
      $rt = true;
    }
    return $rt;
  }//end of function
}//end of class
?>
