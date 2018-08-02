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

class Plate{
  var $ID;
  var $Name;
  var $PlateNotes;
  var $OwnerID;
  var $DateTime;
  var $ComplitDate;
  var $DigestedBy;
  var $DigestStarted;
  var $DigestCompleted;
  var $Buffer;
  var $MSDate;
  var $ProjectID;
  
  var $fname;
  var $lname;
  var $AccessProjectID; 
  var $count;
  var $link;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  
  function Plate( $ID="") {
    global $HITSDB;
    $this->link = $HITSDB->link;
    global $AccessProjectID;
    $this->AccessProjectID = $AccessProjectID;
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
          PlateNotes, 
          OwnerID, 
          DateTime,
          ComplitDate,
          DigestedBy,
          DigestStarted,
          DigestCompleted,
          Buffer, 
          ProjectID,
          MSDate 
          FROM Plate where  ID='$this->ID'";
       list(
          $this->ID,
          $this->Name,
          $this->PlateNotes,
          $this->OwnerID,
          $this->DateTime,
          $this->ComplitDate,
          $this->DigestedBy,
          $this->DigestStarted,
          $this->DigestCompleted,
          $this->Buffer,
          $this->ProjectID,
          $this->MSDate) = mysqli_fetch_array(mysqli_query($this->link,$SQL));
       $this->count = 1;
     }
  } //end of function fetch
  //---------------------------------------------
  //      available plate
  //--------------------------------------------- 
  function get_available($whichPlate,$Plate_ID = 0){
     $re = $Plate_ID;
     if($whichPlate == 'last'){
       $SQL = "select PlateID from PlateWell group by PlateID having count(PlateID)<96 order by PlateID desc limit 1";
     }elseif($whichPlate == 'first'){
       $SQL = "select PlateID from PlateWell group by PlateID having count(PlateID)<96 order by PlateID limit 1";
     }elseif($whichPlate == 'next' and $Plate_ID){
       $SQL = "select PlateID from PlateWell WHERE PlateID > $Plate_ID group by PlateID having count(PlateID)<96 order by PlateID limit 1";
     }elseif($whichPlate == 'previous' and $Plate_ID){
       $SQL = "select PlateID from PlateWell WHERE PlateID < $Plate_ID group by PlateID having count(PlateID)<96 order by PlateID desc limit 1";
       echo $SQL;
     }
     $row = mysqli_fetch_array(mysqli_query($this->link,$SQL));
     if($row[0]) $re = $row[0];
     return $re;
  }
  //---------------------------------------------
  //     fetch a plate id 
  //--------------------------------------------- 
  function get_one($whichPlate,$Plate_ID = 0){
     $re = $Plate_ID;
     if($whichPlate == 'last'){
       $SQL = "select PlateID from PlateWell WHERE ProjectID=$this->AccessProjectID group by PlateID order by PlateID desc limit 1";
     }elseif($whichPlate == 'first'){
       $SQL = "select PlateID from PlateWell WHERE ProjectID=$this->AccessProjectID group by PlateID order by PlateID limit 1";
     }elseif($whichPlate == 'next' and $Plate_ID){
       $SQL = "select PlateID from PlateWell WHERE ProjectID=$this->AccessProjectID AND PlateID > $Plate_ID group by PlateID order by PlateID limit 1";
       //echo $SQL; exit;
     }elseif($whichPlate == 'previous' and $Plate_ID){
       $SQL = "select PlateID from PlateWell WHERE ProjectID=$this->AccessProjectID AND PlateID < $Plate_ID group by PlateID order by PlateID desc limit 1";
     }
     $row = mysqli_fetch_array(mysqli_query($this->link,$SQL));
     if($row[0]) $re = $row[0];
     return $re;
  }
  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall_list($order_by,$start_point='0',$result_per_page=0){
    global $frm_user_id;
    $SQL = "SELECT 
         P.ID, 
         P.Name, 
         P.PlateNotes, 
         P.OwnerID, 
         P.DateTime,
         P.ComplitDate,
         P.DigestedBy,
         P.DigestStarted,
         P.DigestCompleted,
         P.Buffer,
         P.ProjectID,
         P.MSDate
         FROM Plate P Where P.ProjectID=$this->AccessProjectID";
         
    if(isset($frm_user_id) && $frm_user_id){
      $SQL .= " AND OwnerID='$frm_user_id'";
    }
    if($order_by =="P.MSDate"){
      $SQL .= " ORDER BY $order_by desc";
    }else{
      $SQL .= " ORDER BY $order_by";
    }
    if($result_per_page){
      $SQL .= " LIMIT $start_point,$result_per_page";
    }
    $i = 0;
//echo "$SQL<br>";
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->Name[$i], 
         $this->PlateNotes[$i], 
         $this->OwnerID[$i], 
         $this->DateTime[$i],
         $this->CompletDate[$i],
         $this->DigestedBy[$i],
         $this->DigestStarted[$i],
         $this->DigestCompleted[$i],
         $this->Buffer[$i],
         $this->ProjectID[$i],
         $this->MSDate[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert($frm_Name='',
                  $frm_PlateNotes='',
                  $frm_OwnerID=0, 
                  $frm_DigestedBy='',
                  $frm_DigestStarted='',
                  $frm_DigestCompleted='',
                  $frm_Buffer=''){
    if(
      !$frm_Name or 
      !$frm_OwnerID 
     ){
      echo "missing info: ... insert aborted.";
      exit;
    }else if($this->exsist($frm_Name)){
      return "The plate name has been used, Please change the plate name!";
    }else{
       
      $SQL ="INSERT INTO Plate SET 
          Name='$frm_Name', 
          PlateNotes='$frm_PlateNotes', 
          OwnerID='$frm_OwnerID', 
          DateTime=now(),
          DigestedBy='$frm_DigestedBy',";
      if($frm_DigestStarted) $SQL .= "  DigestStarted='$frm_DigestStarted',";
      if($frm_DigestCompleted) $SQL .= "  DigestCompleted='$frm_DigestCompleted',";
      $SQL .= "
          Buffer='$frm_Buffer',
          ProjectID=$this->AccessProjectID, 
          MSDate=null";
      //echo $SQL;exit;
      mysqli_query($this->link,$SQL);
      $this->ID = mysqli_insert_id($this->link);
      $this->fetch($this->ID);
    }
  }//end insert
  //----------------------------------------------
  //
  //----------------------------------------------
  function exsist($frm_Name){
    return mysqli_num_rows(mysqli_query($this->link,"select ID from Plate where Name='$frm_Name'")); 
  }
  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM Plate WHERE ID = '$ID'";
      mysqli_query($this->link,$SQL);
   }else{
      echo "Need id to delete!!!";
   }  
 }//end of delete function
 function is_empty_plate($Plate_ID){
   //if the plate is empty (no record in PlateWell) it should be deleted
    $SQL = "select ID from PlateWell where PlateID='$Plate_ID'";
    if(!mysqli_num_rows(mysqli_query($this->link,$SQL))){
       $this->delete($Plate_ID);
       return 1;
    }else{
      return 0;
    }
 }
 function removeEnpty($User_ID){
   //if the plate is empty the user created it should be deleted
    $SQL = "select P.ID from Plate P left join PlateWell W on P.ID=W.PlateID where W.PlateID is null and P.OwnerID='$User_ID'";     
    $result = mysqli_query($this->link,$SQL);
    while($row = mysqli_fetch_row($result) ){
       $ret = $this->delete($row[0]);
    }
 }
  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_ID,
         $frm_Name='', 
         $frm_PlateNotes='',
         $frm_DigestedBy='', 
         $frm_DigestStarted='', 
         $frm_DigestCompleted='', 
         $frm_Buffer='',        
         $frm_MSDate=''){
    if(!$frm_ID or !$frm_Name) {
      echo "Error: missing info to update.";
      exit;
    } 
      $SQL ="UPDATE Plate SET 
         Name='$frm_Name', 
         PlateNotes='$frm_PlateNotes', 
         DigestedBy='$frm_DigestedBy',";
      if(!$frm_DigestStarted){
         $SQL .= " DigestStarted=null,";
      }else{
         $SQL .= " DigestStarted='$frm_DigestStarted',";
      }
      if(!$frm_DigestCompleted){
         $SQL .= " DigestCompleted=null,";
      }else{
         $SQL .= " DigestCompleted='$frm_DigestCompleted',";
      }
      if(!$frm_MSDate){
         $SQL .= " MSDate=null,";
      }else{
         $SQL .= " MSDate='$frm_MSDate',";
      }
      $SQL .= " Buffer='$frm_Buffer'  WHERE ID =$frm_ID ";
      //echo $SQL;
      mysqli_query($this->link,$SQL);
   }//end of function update

  function get_total(){
    global $frm_user_id,$first_show;
    $SQL = "select count(ID) from Plate WHERE ProjectID=$this->AccessProjectID";
    if(isset($frm_user_id) && $frm_user_id){
      $SQL2 = $SQL . " AND OwnerID = $frm_user_id ";
      $row = mysqli_fetch_row(mysqli_query($this->link,$SQL2));
      if($row[0]){
        $SQL = $SQL2;
      }else{
        if(isset($first_show)){
          $frm_user_id = '';
        }else{
          $SQL = $SQL2;
        }  
      }
    }
    $row = mysqli_fetch_row(mysqli_query($this->link,$SQL));
    return $row[0];
  }
   function get_plates_in_lane($Lane_ID){
     $SQL = "SELECT 
             P.ID,
             P.Name 
             FROM Band B, PlateWell W,Plate P 
             WHERE B.ID = W.BandID and W.PlateID = P.ID and B.LaneID = $Lane_ID Group by P.ID";
     $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list($this->ID[$i],$this->Name[$i] )= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
   }
   //----------------------------
   // for checkCarryOver.php
   //----------------------------
   function setMSDate(){
     if(!$this->MSDate) mysqli_query($this->link,"update Plate set MSDate=now() where ID='$this->ID'");
   }
   //----------------------------
   // used for UploadMDS.php
   //----------------------------
   function fetch_plate_name($new_Plate){
     $results = mysqli_query($this->link,"select ID from Plate where Name='$new_Plate'");
     $row = mysqli_fetch_row($results);
     $this->ID = $row[0];
   }
   //----------------------------
   // search for search_plate.inc.php
   //----------------------------
   function search($searchThis) {
    $SQL = "SELECT 
         ID, 
         Name, 
         PlateNotes, 
         OwnerID, 
         DateTime,
         ComplitDate,
         DigestedBy,
         DigestStarted,
         DigestCompleted,
         Buffer,
         ProjectID,
         MSDate
         FROM Plate   
         WHERE  Name LIKE '%$searchThis%'";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->Name[$i], 
         $this->PlateNotes[$i], 
         $this->OwnerID[$i], 
         $this->DateTime[$i],
         $this->CompletDate[$i],
         $this->DigestedBy[$i],
         $this->DigestStarted[$i],
         $this->DigestCompleted[$i],
         $this->Buffer[$i],
         $this->ProjectID[$i],
         $this->MSDate[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function search
  //--------------------------------------
  //  used by band.php
  //--------------------------------------
  function get_plate_names($plate_ID_arr){
    $rt = array();
    if(is_array($plate_ID_arr) ){
      $SQL = "select ID, Name from Plate where ID in(";
      for($i = 0; $i < count($plate_ID_arr); $i++){
         if($i>0)  $SQL .= ",";
         $SQL .= $plate_ID_arr[$i];
      }
      $SQL .= ")";
      //echo $SQL;
      $sqlResult = mysqli_query($this->link,$SQL); 
      while ($row = mysqli_fetch_row($sqlResult) ) {
        array_push($rt, $row);
      }
    }
    return $rt;
  }
}//end of class
?>