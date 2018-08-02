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

class Gel{
  var $ID;
  var $Name;
  var $Image;
  var $Stain;
  var $Notes;
  var $GelType;
  var $OwnerID;
  var $ProjectID;
  var $DateTime;
  var $link;
  var $Plate_ID; //get_plate_ids()
  var $Lane_num; //get_plate_ids()
	var $Lane_ID;  //get_plate_ids()
  var $Band_Location; //for gel_view.php

  var $count;  
  var $AccessProjectID;  
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Gel( $ID="") {
    global $HITSDB;
    $this->link = $HITSDB->link;
    global $AccessProjectID;     
    $this->AccessProjectID = $AccessProjectID;    
    if($ID) $this->fetch($ID);
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
          Image, 
          Stain, 
          Notes,
		  		GelType, 
          OwnerID, 
          ProjectID, 
          DateTime
          FROM Gel where ID='$this->ID' AND ProjectID='$this->AccessProjectID'";
      // echo $SQL;  
       list(
          $this->ID,
          $this->Name,
          $this->Image,
          $this->Stain,
          $this->Notes,
		  		$this->GelType,
          $this->OwnerID,
          $this->ProjectID,
          $this->DateTime) = mysqli_fetch_array(mysqli_query($this->link,$SQL));
       $this->count = 1;
     }
  } //end of function fetch
  //---------------------------------------------
  // used by UploadMDS.php
  //---------------------------------------------
  function Get_from_name($gel_name){
    $SQL = $SQL = "SELECT 
          ID, 
          Name, 
          Image, 
          Stain, 
          Notes,
		  		GelType, 
          OwnerID, 
          ProjectID, 
          DateTime
          FROM Gel where  Name='$gel_name'";
    list(
          $this->ID,
          $this->Name,
          $this->Image,
          $this->Stain,
          $this->Notes,
		  		$this->GelType,
          $this->OwnerID,
          $this->ProjectID,
          $this->DateTime) = mysqli_fetch_array(mysqli_query($this->link,$SQL));
  }
  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall($order_by='', $start_point=0,$RESULTS_PER_PAGE, $isSummy=0){
    global $frm_user_id;
    $SQL = "SELECT 
         ID, 
         Name, 
         Image, 
         Stain, 
         Notes, 
		     GelType,
         OwnerID, 
         ProjectID, 
         DateTime
         FROM Gel 	
         WHERE  ProjectID=$this->AccessProjectID";
    if(isset($frm_user_id) && $frm_user_id){
      $SQL .= " AND OwnerID='$frm_user_id'";
    }    
    if($isSummy){
      $SQL .= " AND GelType='dummy'";
    } 
  	if($order_by){
      $SQL .= " ORDER BY $order_by";
  	}else{
  		$SQL .= " ORDER BY ID DESC";
  	}
    if($RESULTS_PER_PAGE) $SQL .= " LIMIT $start_point, $RESULTS_PER_PAGE";
//echo "$SQL<br>";    
      $i = 0;
      $sqlResult = mysqli_query($this->link,$SQL);
      $this->count = mysqli_num_rows($sqlResult);
      while (list(
           $this->ID[$i], 
           $this->Name[$i], 
           $this->Image[$i], 
           $this->Stain[$i], 
           $this->Notes[$i],
  		     $this->GelType[$i], 
           $this->OwnerID[$i], 
           $this->ProjectID[$i], 
           $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
         $i++;
      }
  }//end of function fetchall
  
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_Name='', 
       $frm_Stain='', 
       $frm_Notes='', 
	     $frm_GelType='',
       $frm_OwnerID=0){
	 
    if( !$frm_Name or  !$frm_OwnerID ){
      echo "missing info: ... insert into gel aborted.";
      exit;
    }else{       
      $SQL ="INSERT INTO Gel SET 
          Name='".mysqli_escape_string($this->link, $frm_Name)."',
          Stain='".mysqli_escape_string($this->link, $frm_Stain)."', 
          Notes='".mysqli_escape_string($this->link, $frm_Notes)."', 
		      GelType='$frm_GelType', 
          OwnerID='$frm_OwnerID', 
          ProjectID='$this->AccessProjectID', 
          DateTime=now()";
      //echo $SQL;    
      mysqli_query($this->link,$SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
  }
	//---------------------------------------------
	//    input image name
	//---------------------------------------------
  function update_image($ID, $new_pic_name){
	   if($ID and  $new_pic_name){
		  $SQL = "UPDATE Gel set Image = '".mysqli_escape_string($this->link, $new_pic_name)."' WHERE ID = $ID ";
		  mysqli_query($this->link,$SQL);
	   }else{
		  echo "Error: missing value to updtate gel";
		  exit;
	   }
  }
  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
	    $SQL = "SELECT ID FROM Lane WHERE GelID = '$ID'";
	    $sqlResult = mysqli_query($this->link,$SQL);
      if(mysqli_num_rows($sqlResult)){
			  return "You can't delete the Gel since some samples share the gel.";
		  }else{
			  $SQL = "DELETE FROM Gel WHERE ID = '$ID' and ID";
        mysqli_query($this->link,$SQL);
			  return "";
		  }
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
         $frm_Image='', 
         $frm_Stain='', 
         $frm_Notes='', 
		     $frm_GelType=''){
	if( !$frm_Name ){
      echo "missing info: ... insert into gel aborted.";
      exit;
	}
    $SQL ="UPDATE Gel SET 
         Name='".mysqli_escape_string($this->link, $frm_Name)."',";
         if($frm_Image){ 
          $SQL .= " Image='".mysqli_escape_string($this->link, $frm_Image)."',";
         } 
         $SQL .=  " Stain='".mysqli_escape_string($this->link, $frm_Stain)."', 
         Notes='".mysqli_escape_string($this->link, $frm_Notes)."', 
		     GelType='$frm_GelType', 
         ProjectID='$this->AccessProjectID' 
         WHERE ID =$frm_ID ";
     //echo $SQL;    
     mysqli_query($this->link,$SQL);
   }//end of function update
   //----------------------------------------------
   //    check ownership
   //----------------------------------------------
   function isOwner($Gel_ID,$Owner_ID) {
   	 $SQL = "select ID from Gel where ID = $Gel_ID and OwnerID = $Owner_ID";
       $sqlResult = mysqli_query($this->link,$SQL);
       if(mysqli_num_rows($sqlResult)){
			 return 1;
		 }else{
			 return 0;
		 }
   }
   //--------------------------------------------------
   //    get a number of total records
   //--------------------------------------------------
   function get_total($isSummy=0){
      global $frm_user_id,$first_show;
      $SQL = "SELECT COUNT(ID) FROM Gel WHERE  ProjectID=$this->AccessProjectID";
      if($isSummy){
        $SQL .= " AND GelType='dummy'";
      }
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
//echo "$SQL<br>";
      $row = mysqli_fetch_row(mysqli_query($this->link,$SQL));
      return $row[0];
   }
   //--------------------------------------------------
   //    get plate ids and lane numbers by passing Gel id
   /*
    | PlateID | LaneNum |
    +---------+---------+
    |      36 |       2 |
    |      36 |       3 |
    |      36 |       7 |
    |      38 |       2 |
    |      40 |       9 |
    +---------+---------+*/
   //--------------------------------------------------
   function get_plate_ids($Gel_ID=0){
    $this->count = 0;
    if(!$Gel_ID){
      echo "Error: should pass Gel_ID to get_plate_ids";
      exit;
    }else{
      $i = 0;
      $SQL = "select PlateID, LaneNum, LaneID from Lane,Band,PlateWell 
              where Lane.ID=Band.LaneID 
              and Band.ID=PlateWell.BandID 
              and GelID='$Gel_ID' group by PlateID, LaneNum";
      $sqlResult = mysqli_query($this->link,$SQL);
      $this->count = mysqli_num_rows($sqlResult);
      while (list($this->Plate_ID[$i],$this->Lane_num[$i],$this->Lane_ID[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
      }//end while
    }//end if
    return $this->count;
  }
  //--------------------------------------
  // cll from gel_view.php
  //--------------------------------------
  function get_gel_id($Band_ID){
    $SQL = "select L.GelID, B.Location from Band B, Lane L where B.LaneID=L.ID and B.ID=$Band_ID";
    $result = mysqli_query($this->link,$SQL);
    $row = mysqli_fetch_row($result);
    $this->ID = $row[0];
    $this->Band_Location = $row[1];
  }

  function move_Gel($whichGel, $Gel_ID=0){
     $re = $Gel_ID;
     $SQL = "SELECT ID FROM Gel WHERE ProjectID=$this->AccessProjectID";
	   
     if($whichGel == 'last'){
       $SQL .= " order by ID desc limit 1";
     }elseif($whichGel == 'first'){
       $SQL .= " order by ID limit 1";
     }elseif($whichGel == 'next' and $Gel_ID){
       $SQL .= " and  ID > $Gel_ID  order by ID limit 1";
     }elseif($whichGel == 'previous' and $Gel_ID){
       $SQL .= " and  ID < $Gel_ID  order by ID desc limit 1";
     }
     //echo $SQL;
     $row = mysqli_fetch_array(mysqli_query($this->link,$SQL));
     if($row[0]) $re = $row[0];
     return $re;
   }
   //----------------------------------------------
  //  search plate for search_gel.inc.php
  //----------------------------------------------
  function search($searchThis) {
    $SQL = "SELECT 
         ID, 
         Name, 
         Image, 
         Stain, 
         Notes, 
		     GelType,
         OwnerID, 
         ProjectID, 
         DateTime
         FROM Gel";
    
	  $Where = " WHERE  ProjectID=$this->AccessProjectID";  	
	  $Where .= " and (Name='$searchThis' or Image='$searchThis') ";
    $SQL .= $Where;
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->Name[$i], 
         $this->Image[$i], 
         $this->Stain[$i], 
         $this->Notes[$i],
		     $this->GelType[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  function fetch_IDs($id_str) {
    $SQL = "SELECT 
         ID, 
         Name, 
         Image, 
         Stain, 
         Notes, 
		     GelType,
         OwnerID, 
         ProjectID, 
         DateTime
         FROM Gel";
    
	  $Where = " WHERE  ProjectID=$this->AccessProjectID";  	
	  $Where .= " and ID in($id_str) ";
    $SQL .= $Where;
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->Name[$i], 
         $this->Image[$i], 
         $this->Stain[$i], 
         $this->Notes[$i],
		     $this->GelType[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall

}//end of class
?>
