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


class Coip{
  var $ID;
  var $Clone;
  var $BaitORF;
  var $TargetORF;
  var $Description;
  var $Interaction;
  var $BaitExpression;
  var $TargetExpression;
  var $TargetNegControl;
  var $WSTimgID;
  var $ProjectID;
  var $DateTime;
  var $link;
  var $WImage;
  var $AccessProjectID;  
  var $count;
  //----------------------------------------------
  //      default function
  //----------------------------------------------
  function Coip( $ID="") {
  
    global $HITSDB;
    $this->link = $HITSDB->link;
    global $AccessProjectID;
    $this->AccessProjectID = $AccessProjectID;
    if($ID) {
      $this->fetch($ID);
    }
  }//function end
  //----------------------------------------------
  //    total records
  //----------------------------------------------
  function get_total(){
      $SQL = "SELECT COUNT(ID) FROM Coip WHERE ProjectID=$this->AccessProjectID";
      $row = mysqli_fetch_row(mysqli_query($this->link, $SQL));
      return $row[0];
  }
  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="") {
    if($ID){
      $this->ID = $ID;
      $SQL = "SELECT 
        C.ID, 
        C.Clone, 
        C.BaitORF, 
        C.TargetORF, 
        C.Description, 
        C.Interaction, 
        C.BaitExpression, 
        C.TargetExpression,
        C.TargetNegControl,
        C.WSTimgID, 
        C.ProjectID,
        C.DateTime,
        
        W.Image
        FROM Coip C LEFT JOIN Coip_WSTimages W ON (C.WSTimgID=W.ID) WHERE C.ID='$this->ID' AND C.ProjectID=$this->AccessProjectID";
        //echo $SQL; 
      list(
        $this->ID,
        $this->Clone,
        $this->BaitORF,
        $this->TargetORF,
        $this->Description,
        $this->Interaction,
        $this->BaitExpression,
        $this->TargetExpression,
        $this->TargetNegControl,
        $this->WSTimgID,
        $this->ProjectID,
        $this->DateTime,
        $this->WImage) = mysqli_fetch_array(mysqli_query($this->link, $SQL));
        $this->count = 1;
     }else{
       echo "missing ID: in fetch";
	     exit;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------  
  function fetchall($order_by='', $start_point=0,$RESULTS_PER_PAGE) {
    $SQL = "SELECT 
      C.ID, 
      C.Clone, 
      C.BaitORF, 
      C.TargetORF, 
      C.Description, 
      C.Interaction, 
      C.BaitExpression, 
      C.TargetExpression, 
      C.TargetNegControl,
      C.WSTimgID,
      C.ProjectID,
      C.DateTime,
      W.Image
      FROM Coip C LEFT JOIN Coip_WSTimages W ON (C.WSTimgID=W.ID)";
    $Where = " WHERE  C.ProjectID=$this->AccessProjectID";  
    $SQL .= $Where;
    if($order_by){
      $SQL .= " ORDER BY C.$order_by";
    }else{
      $SQL .= " ORDER BY C.ID DESC";
    }
    if($RESULTS_PER_PAGE) $SQL .= " LIMIT $start_point, $RESULTS_PER_PAGE";
    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
      $this->ID[$i], 
      $this->Clone[$i], 
      $this->BaitORF[$i], 
      $this->TargetORF[$i], 
      $this->Description[$i], 
      $this->Interaction[$i], 
      $this->BaitExpression[$i], 
      $this->TargetExpression[$i], 
      $this->TargetNegControl[$i],
      $this->WSTimgID[$i],
      $this->ProjectID[$i], 
      $this->DateTime[$i],
      $this->WImage[$i])= mysqli_fetch_row($sqlResult)){
      $i++;
    }
  }//end of function fetchall
  //----------------------------------------------
  // search 
  //----------------------------------------------
  function search($searchThis) {
    //if the input is a gene name
    $SQL = "SELECT Y.ORFName 
                FROM YeastDB Y, YeastORF2Gene G 
		    where Y.ORFName=G.ORFName and G.Gene='$searchThis' ";
    //echo $SQL;
    $results = mysqli_query($this->link, $SQL);
    if($row = mysqli_fetch_row($results)){
    	$searchThis = $row[0];
    }
    $SQL = "SELECT 
      C.ID, 
      C.Clone, 
      C.BaitORF, 
      C.TargetORF, 
      C.Description, 
      C.Interaction, 
      C.BaitExpression, 
      C.TargetExpression, 
      C.TargetNegControl,
      C.WSTimgID,
      C.ProjectID
      C.DateTime,
      W.Image
      FROM Coip C LEFT JOIN Coip_WSTimages W ON C.WSTimgID=W.ID 
      where (C.Clone='$searchThis' or C.BaitORF='$searchThis' or C.TargetORF='$searchThis') AND C.ProjectID=$AccessProjectID";

    $i = 0;
    //echo $SQL;
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
      $this->ID[$i], 
      $this->Clone[$i], 
      $this->BaitORF[$i], 
      $this->TargetORF[$i], 
      $this->Description[$i], 
      $this->Interaction[$i], 
      $this->BaitExpression[$i], 
      $this->TargetExpression[$i], 
      $this->TargetNegControl[$i],
      $this->WSTimgID[$i],
      $this->ProjectID[$i], 
      $this->DateTime[$i],
      $this->WImage[$i])= mysqli_fetch_row($sqlResult) ) {
        $i++;
    }
  }//end of function fetchall
  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
    $frm_Clone='', 
    $frm_BaitORF='', 
    $frm_TargetORF='', 
    $frm_Description='', 
    $frm_Interaction=0, 
    $frm_BaitExpression='', 
    $frm_TargetExpression='',
    $frm_TargetNegControl='',
    $frm_WSTimgID=0,
    $frm_ProjectID=''
    ){
    if(
      !$frm_Clone or 
      !$frm_BaitORF or 
      !$frm_TargetORF or 
      !$frm_Interaction
    ){
      echo "missing info: ... insert aborted.";
      exit;
    }else{
      $SQL ="INSERT INTO Coip SET 
        Clone='$frm_Clone', 
        BaitORF='$frm_BaitORF', 
        TargetORF='$frm_TargetORF', 
        Description='$frm_Description', 
        Interaction='$frm_Interaction', 
        BaitExpression='$frm_BaitExpression', 
        TargetExpression='$frm_TargetExpression', 
        TargetNegControl='$frm_TargetNegControl',
        WSTimgID='$frm_WSTimgID', 
        ProjectID='$frm_ProjectID',
        DateTime=now()";
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    }
  }//end insert
   
  //----------------------------------------------
  //      delete function
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      $SQL = "DELETE FROM Coip WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
   }else{
      echo "Need id to delete!!!";
    }  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
    $frm_ID=0, 
    $frm_Clone='', 
    $frm_BaitORF='', 
    $frm_TargetORF='', 
    $frm_Description='', 
    $frm_Interaction=0, 
    $frm_BaitExpression='', 
    $frm_TargetExpression='', 
    $frm_TargetNegControl='',
    $frm_WSTimgID="",
    $frm_ProjectID=0){
      $SQL ="UPDATE Coip SET 
        Clone='$frm_Clone', 
        BaitORF='$frm_BaitORF', 
        TargetORF='$frm_TargetORF', 
        Description='$frm_Description', 
        Interaction='$frm_Interaction', 
        BaitExpression='$frm_BaitExpression', 
        TargetExpression='$frm_TargetExpression', 
        TargetNegControl='$frm_TargetNegControl',
        ProjectID='$frm_ProjectID',
        DateTime=now()";
	if($frm_WSTimgID != "-1"){
	   $SQL .= ", WSTimgID='$frm_WSTimgID'"; 
	}
      $SQL .= "  WHERE ID =$frm_ID ";
	//echo $SQL;
      mysqli_query($this->link, $SQL);
   }//end of function update
}//end of class
?>
