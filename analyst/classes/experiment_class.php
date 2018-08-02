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

class Experiment{
  var $ID;
  var $BaitID;
  var $Name;
  var $TaxID;
  var $OwnerID;
  var $ProjectID;   //who can access the record.
  var $GrowProtocol;
  var $IpProtocol;
  var $DigestProtocol;
  var $PreySource;
  var $Notes;
  var $WesternGel;
  var $DateTime;

  var $count;  
  var $AccessProjectID;
  var $link;
 
 
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Experiment($link='', $ID="") {
    global $AccessUsers;
    global $AccessProjectID;    
    $this->AccessProjectID = $AccessProjectID;
    if($link){
      $this->link = $link;
    }else{
      global $HITSDB;
      $this->link = $HITSDB->link;
    }
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
          BaitID, 
          Name, 
          TaxID, 
          OwnerID, 
          ProjectID,
          GrowProtocol,
          IpProtocol,
          DigestProtocol,
          PreySource,
          Notes,
          WesternGel, 
          DateTime
          FROM Experiment where  ID='$this->ID'";
         // echo $SQL;
       
      $result = mysqli_query($this->link, $SQL);
       
       list(
          $this->ID,
          $this->BaitID,
          $this->Name,
          $this->TaxID,
          $this->OwnerID,
          $this->ProjectID,
          $this->GrowProtocol,
          $this->IpProtocol,
          $this->DigestProtocol,
          $this->PreySource,
          $this->Notes,
          $this->WesternGel,
          $this->DateTime) = mysqli_fetch_array($result);
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  function fetchall($Bait_ID=0, $order_by='') {
   
    $SQL = "SELECT 
         ID, 
         BaitID, 
         Name, 
         TaxID,
         OwnerID, 
         ProjectID,
         GrowProtocol,
         IpProtocol,
         DigestProtocol,
         PreySource,
         Notes,
         WesternGel, 
         DateTime 
         FROM Experiment";
    if(!$Bait_ID) $Bait_ID = 0;
		$SQL .= " WHERE BaitID = '$Bait_ID' and ProjectID=$this->AccessProjectID";    	
    
    if($order_by){
      $SQL .=" order by $order_by";
    }
    if(!$Bait_ID) $SQL .= ' limit 10';
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i], 
         $this->BaitID[$i], 
         $this->Name[$i], 
         $this->TaxID, 
         $this->OwnerID[$i], 
         $this->ProjectID[$i],
         $this->GrowProtocol[$i],
         $this->IpProtocol[$i],
         $this->DigestProtocol[$i],
         $this->PreySource[$i],
         $this->Notes[$i],
         $this->WesternGel[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
    }
  }//end of function fetchall
  //----------------------------------------------
  //      fetchall exp in the same bait
  //----------------------------------------------
  function fetchall_Bait($Bait_ID=0) {
    if($Bait_ID) {
		 
     $SQL = "SELECT 
         ID, 
         BaitID, 
         Name, 
         TaxID, 
         OwnerID, 
         ProjectID,
         GrowProtocol,
         IpProtocol,
         DigestProtocol,
         PreySource,
         Notes,
         WesternGel, 
         DateTime
         FROM Experiment";
		 $SQL .= " WHERE BaitID = $Bait_ID ORDER BY DateTime";
     $i = 0;
     //echo $SQL;
     $sqlResult = mysqli_query($this->link, $SQL);
     $this->count = mysqli_num_rows($sqlResult);
     while (list(
         $this->ID[$i], 
         $this->BaitID[$i], 
         $this->Name[$i], 
         $this->TaxID,
         $this->OwnerID[$i], 
         $this->ProjectID[$i],
         $this->GrowProtocol[$i],
         $this->IpProtocol[$i],
         $this->DigestProtocol[$i],
         $this->PreySource[$i],
         $this->Notes[$i],
         $this->WesternGel[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
       $i++;
     }
    }//end if
  }//end of function fetchall

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_BaitID=0, 
       $frm_Name='', 
       $frm_TaxID='',
       $frm_OwnerID=0,
       $frm_ProjectID=0,
       $frm_GrowProtocol='',
       $frm_IpProtocol='',
       $frm_DigestProtocol='',
       $frm_PreySource='',
       $frm_Notes='',
       $frm_WesternGel='', 
			 $frm_Date=''){
    
    if(!$frm_Name or !$frm_OwnerID ){
      echo "missing info: ... insert into Experiment table aborted.";
      exit;
    }else{
        $SQL ="INSERT INTO Experiment SET 
          BaitID='$frm_BaitID', 
          Name='".mysqli_escape_string($this->link,$frm_Name)."',
          TaxID='$frm_TaxID', 
          OwnerID='$frm_OwnerID', 
          ProjectID='$frm_ProjectID',
          GrowProtocol='$frm_GrowProtocol',
          IpProtocol='$frm_IpProtocol',
          DigestProtocol='$frm_DigestProtocol',
          PreySource='".mysqli_escape_string($this->link,$frm_PreySource)."',
          Notes='$frm_Notes',
          WesternGel='$frm_WesternGel', 
          DateTime= '$frm_Date'";
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
  }//end insert

  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
     if($row = mysqli_fetch_row(mysqli_query($this->link, "select ID from Lane where ExpID=$ID"))){
        return "You can not delete Experiment $ID , since it links to Lane $row[0].";
     }
     $SQL = "DELETE FROM Experiment WHERE ID = '$ID'";
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
        $frm_TaxID='',
        $frm_ProjectID=0,
        $frm_GrowProtocol='',
        $frm_IpProtocol='',
        $frm_DigestProtocol='',
        $frm_PreySource='',
        $frm_Notes='',
        $frm_WesternGel='', 
        $frm_Date=''){
     if(!($frm_ID and $frm_Name)) {
	 	echo "errror: missing information to update the experiment.";
		exit;
	 }
      $SQL ="UPDATE Experiment SET 
         Name='".mysqli_escape_string($this->link,$frm_Name)."',
         TaxID='$frm_TaxID',
         ProjectID='$frm_ProjectID',
         GrowProtocol='$frm_GrowProtocol',
         IpProtocol='$frm_IpProtocol',
         DigestProtocol='$frm_DigestProtocol',
         PreySource='".mysqli_escape_string($this->link,$frm_PreySource)."',
         Notes='$frm_Notes',
         WesternGel='$frm_WesternGel',
         DateTime='$frm_Date'
         WHERE ID =$frm_ID ";
      mysqli_query($this->link, $SQL);
   }//end of function update
   //------------------------------------------------
   //     check is user owne the record
   //-----------------------------------------------
   function isOwner($Exp_ID=0, $Owner_ID=0){
     	$SQL = "select ID from Experiment where ID = $Exp_ID and OwnerID = $Owner_ID";
        $sqlResult = mysqli_query($this->link, $SQL);
        if(mysqli_num_rows($sqlResult)){
			return 1;
		}else{
			return 0;
		}
   }
   //-------------------------------------------------
   //  check if the experiment has gel lanes link to.
   //  (called from experiment.php)
   //-------------------------------------------------
   function has_lanes($Exp_ID){
     if(!$Exp_ID) return 0;
      $SQL = "SELECT ID FROM Lane where ExpID = '$Exp_ID'";
       
      $rt = mysqli_num_rows(mysqli_query($this->link, $SQL ));
      return $rt;
   }
   //-------------------------------------------------
   //  check if the experiment has gel lanes link to.
   //  (called from experiment.php)
   //-------------------------------------------------
   function has_bands($Exp_ID){
      if(!$Exp_ID) return 0;
      $SQL = "SELECT ID FROM Lane where ExpID = '$Exp_ID'";
      //echo $SQL;
      $rt = mysqli_num_rows(mysqli_query($this->link, $SQL ));
      if(!$rt){
        $SQL = "SELECT ID FROM Band where ExpID = '$Exp_ID' limit 1";
        //echo $SQL;
        $rt = mysqli_num_rows(mysqli_query($this->link, $SQL ));
      }
      return $rt;
   }
   //--------------------------------------------------
   // used by UploadMDS.php
   //--------------------------------------------------
   function fetch_exist($new_Bait_ID,$new_ExpCondition,$new_ExpName){
      $SQL = "select * from Experiment E, ExpCondition EC, `Condition` C 
              where E.ID=EC.ExpID and EC.ConditionID=C.ID 
              and E.BaitID='$new_Bait_ID' and E.Name='$new_ExpName' and C.`Condition` like '%$new_ExpCondition%'";
      list(
          $this->ID,
          $this->BaitID,
          $this->Name,
          $this->OwnerID,
          $this->ProjectID,
          $this->GrowProtocol,
          $this->IpProtocol,
          $this->DigestProtocol,
          $this->PreySource,
          $this->Notes,
          $this->WesternGel,
          $this->DateTime) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
       $this->count = 1;
   }
}//end of class
?>
