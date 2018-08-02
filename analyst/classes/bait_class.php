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


class Bait{
  var $ID;
  var $GeneID;
  var $LocusTag;  
  var $GeneName;
  var $BaitAcc;
  var $AccType;
  var $TaxID;
  var $BaitMW;
  var $Family;
  var $Tag;
  var $Mutation;
  var $Clone;
  var $Vector;
  var $Description;
  var $OwnerID;
  var $ProjectID;
  var $GelFree;
  var $DateTime;
  var $bait_str;
  var $link;

  var $count;
  var $AccessProjectID;  
  
  function Bait($ID=0, $link=0){
    if($link){
     $this->link = $link;
    }else{
      global $HITSDB;
      $this->link = $HITSDB->link;
    }
    global $AccessProjectID;
    $this->AccessProjectID = $AccessProjectID;
    if($ID) $this->fetch($ID);
  }
  //----------------------------------------------
  //      fetchORF -- used by UplodMDS.php
  //----------------------------------------------
  function fetchORF($ORF){
    $SQL = "SELECT 
          ID, 
          GeneID,
          LocusTag,
          GeneName,
          BaitAcc,
          AccType,
          TaxID, 
          BaitMW, 
          Family, 
          Tag,
          Mutation,
          Clone, 
          Vector, 
          Description, 
          OwnerID, 
          ProjectID,
          GelFree, 
          DateTime
          FROM Bait where  LocusTag='$ORF'";
     //echo $SQL;
        
       $results = mysqli_query($this->link, $SQL);
      
       list(
          $this->ID,
          $this->GeneID,
          $this->LocusTag,
          $this->GeneName,
          $this->BaitAcc,
          $this->AccType,
          $this->TaxID,
          $this->BaitMW,
          $this->Family,
          $this->Tag,
          $this->Mutation,
          $this->Clone,
          $this->Vector,
          $this->Description,
          $this->OwnerID,
          $this->ProjectID,
          $this->GelFree,
          $this->DateTime) = mysqli_fetch_array($results);
       $this->count = 1;
  }
  //----------------------------------------------
  //      fetch function
  //----------------------------------------------
  function fetch($ID="") {
    if($ID){
      $this->ID = $ID;
      $SQL = "SELECT 
          ID, 
          GeneID,
          LocusTag,
          GeneName,
          BaitAcc,
          AccType, 
          TaxID, 
          BaitMW, 
          Family, 
          Tag,
          Mutation, 
          Clone, 
          Vector, 
          Description, 
          OwnerID, 
          ProjectID,
          GelFree, 
          DateTime
          FROM Bait where  ID='$this->ID'";
     //echo $SQL;
       $results = mysqli_query($this->link, $SQL);
       list(
          $this->ID,
          $this->GeneID,
          $this->LocusTag,
          $this->GeneName,
          $this->BaitAcc,
          $this->AccType,
          $this->TaxID,
          $this->BaitMW,
          $this->Family,
          $this->Tag,
          $this->Mutation,
          $this->Clone,
          $this->Vector,
          $this->Description,
          $this->OwnerID,
          $this->ProjectID,
          $this->GelFree,
          $this->DateTime) = mysqli_fetch_array($results);
       $this->count = 1;
     }
  } //end of function fetch

  //----------------------------------------------
  //      fetchall function
  //----------------------------------------------
  
  
  function fetchall($order_by='', $start_point=0, $RESULTS_PER_PAGE=0, $isDummy=0, $isGelFree=0){
    global $frm_user_id,$all_group_recodes_str,$frm_group_id_list;    
    $SQL = "SELECT 
         ID, 
         GeneID,
         LocusTag, 
         GeneName,
         BaitAcc,
         AccType, 
         TaxID, 
         BaitMW, 
         Family, 
         Tag,
         Mutation, 
         Clone, 
         Vector, 
         OwnerID, 
         ProjectID,
         GelFree, 
         DateTime
         FROM Bait";  
  $Where = " WHERE  ProjectID=$this->AccessProjectID";  
  $SQL .= $Where;
  if($isDummy){
    $SQL .= " AND Clone='dummy'";
  }
  if($isGelFree == '1'){
    $SQL .= " AND GelFree='1'";
  }else if($isGelFree == '2'){
    $SQL .= " AND (GelFree='0' OR GelFree IS NULL)";
  }
  if(isset($frm_user_id) && $frm_user_id){
    $SQL .= " AND OwnerID='$frm_user_id'";
  }
  if(isset($all_group_recodes_str) && $all_group_recodes_str){
    $SQL .= " AND ID IN($all_group_recodes_str)";
  }elseif(isset($all_group_recodes_str) && !$all_group_recodes_str && isset($frm_group_id_list) && $frm_group_id_list){
    $SQL .= " AND 0 ";
  }
  if($order_by) $SQL .= " ORDER BY $order_by "; 
  if($RESULTS_PER_PAGE) $SQL .= " LIMIT $start_point, $RESULTS_PER_PAGE";
    $i = 0;
     
    $sqlResult = mysqli_query($this->link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while (list(
         $this->ID[$i],
         $this->GeneID[$i],          
         $this->LocusTag[$i],
         $this->GeneName[$i],
         $this->BaitAcc[$i],
         $this->AccType[$i], 
         $this->TaxID[$i], 
         $this->BaitMW[$i], 
         $this->Family[$i], 
         $this->Tag[$i],
         $this->Mutation[$i], 
         $this->Clone[$i], 
         $this->Vector[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i],
         $this->GelFree[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
         if($this->bait_str) $this->bait_str .= ",";
         $this->bait_str .= $this->ID[$i];
       $i++;
    }
    
  }//end of function fetchall
  
  //-----------------------------------------------
  //only return those bait in the id str '2,5,7'    --in use---
  //----------------------------------------------
  function fetchall_ids($order_by='', $id_str='', $in_id_str='in') {
    $SQL = "SELECT 
         ID, 
         GeneID,
         LocusTag, 
         GeneName,
         BaitAcc,
         AccType, 
         TaxID, 
         BaitMW, 
         Family, 
         Tag,
         Mutation, 
         Clone, 
         Vector, 
         OwnerID, 
         ProjectID,
         GelFree, 
         DateTime
         FROM Bait";  
    //if current user is supervisor or labTeck who can acess more then one user's data
    $Where = " WHERE  ProjectID=$this->AccessProjectID"; 
    
    if($id_str){
      $Where .= " and ID $in_id_str ($id_str) ";
    }
    $SQL .= $Where;
    //echo $SQL;
    if($order_by) $SQL .= " ORDER BY $order_by "; 
    //if($RESULTS_PER_PAGE) $SQL .= " LIMIT $start_point, $RESULTS_PER_PAGE";
      $i = 0;
      //echo $SQL;
      $sqlResult = mysqli_query($this->link, $SQL);
      $this->count = mysqli_num_rows($sqlResult);
      while (list(
         $this->ID[$i],
         $this->GeneID[$i],          
         $this->LocusTag[$i],
         $this->GeneName[$i],
         $this->BaitAcc[$i],
         $this->AccType[$i], 
         $this->TaxID[$i], 
         $this->BaitMW[$i], 
         $this->Family[$i], 
         $this->Tag[$i],
         $this->Mutation[$i], 
         $this->Clone[$i], 
         $this->Vector[$i], 
         $this->OwnerID[$i], 
         $this->ProjectID[$i],
         $this->GelFree[$i], 
         $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
         if($this->bait_str) $this->bait_str .= ",";
         $this->bait_str .= $this->ID[$i];
       $i++;
    }
  }//end of function fetchall_ids

  //----------------------------------------------
  //      insert function
  //----------------------------------------------
  function insert(
       $frm_GeneID,
       $frm_LocusTag='',
       $frm_GeneName='',
       $frm_BaitAcc='',
       $frm_AccType='', 
       $frm_TaxID='', 
       $frm_BaitMW=0, 
       $frm_Family='', 
       $frm_Tag='',
       $frm_Mutation='', 
       $frm_Clone='', 
       $frm_Vector='', 
       $frm_Description='', 
       $frm_OwnerID, 
       $frm_ProjectID='',
       $frm_GelFree=0,
       $frm_Date=''){
    /*if(
      (!$frm_LocusTag and !$frm_GeneID and !$frm_GeneName ) or
      !$frm_TaxID or 
      !$frm_BaitMW or 
      !$frm_OwnerID
     ){
      echo "missing info: ... insert into Bait aborted.";
     exit;
    }else{*/
      if(!$frm_Date) $frm_Date=@date("Y-m-d H:i:s");
      $SQL ="INSERT INTO Bait SET ";
          if($frm_GeneID){
            $SQL .= " GeneID = $frm_GeneID,";
          }
          $SQL .= " LocusTag='".mysqli_real_escape_string($this->link, $frm_LocusTag)."', 
          GeneName='".mysqli_real_escape_string($this->link, $frm_GeneName)."',
          BaitAcc='".mysqli_real_escape_string($this->link, $frm_BaitAcc)."',
          AccType='$frm_AccType', 
          TaxID='$frm_TaxID',";
          if($frm_BaitMW){ 
          $SQL .= " BaitMW='$frm_BaitMW',";
          } 
          $SQL .= " Family='".mysqli_real_escape_string($this->link, $frm_Family)."', 
          Tag='".mysqli_real_escape_string($this->link, $frm_Tag)."', 
          Mutation='".mysqli_real_escape_string($this->link, $frm_Mutation)."',
          Clone='".mysqli_real_escape_string($this->link, $frm_Clone)."', 
          Vector='".mysqli_real_escape_string($this->link, $frm_Vector)."', 
          Description='".mysqli_real_escape_string($this->link, $frm_Description)."', 
          OwnerID='$frm_OwnerID', 
          ProjectID='$frm_ProjectID',
          GelFree='$frm_GelFree', 
          DateTime='$frm_Date'";
      //echo $SQL;//exit;
      mysqli_query($this->link, $SQL);
      $this->ID = mysqli_insert_id($this->link);
    //}
  }//end insert

  //----------------------------------------------
  //      delete function       --in use-----*****
  //----------------------------------------------
  function delete($ID) {
    if($ID) {
      //check if bait is used
      if($row=mysqli_fetch_row(mysqli_query($this->link, "select ID from Experiment where BaitID = $ID"))){
        return "You can not delete this bait, since it links to experiment $row[0] !";
      }
      $SQL = "DELETE FROM Bait WHERE ID = '$ID'";
      mysqli_query($this->link, $SQL);
      return '';
    }else{
      echo "Need id to delete!!!";
    }  
  }//end of delete function

  //----------------------------------------------
  //      update function
  //----------------------------------------------
  function update(
         $frm_Bait_ID,
         $frm_GeneID=0,
         $frm_LocusTag='',
         $frm_GeneName='', 
         $frm_BaitAcc='',
         $frm_AccType='', 
         $frm_TaxID='', 
         $frm_BaitMW=0, 
         $frm_Family='', 
         $frm_Tag='',
         $frm_Mutation='', 
         $frm_Clone='', 
         $frm_Vector='', 
         $frm_ProjectID='',
         $frm_Description){
      if(!$frm_Bait_ID){
        echo "missing ID to updata this Bait.";
        exit;
      }
      $SQL = "UPDATE Bait SET";
        if(!$frm_GeneID){
          $SQL .= " GeneID=NULL,";
        }else{
          $SQL .= " GeneID=$frm_GeneID,";
        } 
        $SQL .= " LocusTag='".mysqli_escape_string($this->link, $frm_LocusTag)."', 
        GeneName='".mysqli_real_escape_string($this->link, $frm_GeneName)."',
        BaitAcc='".mysqli_real_escape_string($this->link, $frm_BaitAcc)."',
        AccType='$frm_AccType',  
        TaxID='$frm_TaxID',
        BaitMW='$frm_BaitMW',
        Family='".mysqli_real_escape_string($this->link, $frm_Family)."', 
        Tag='".mysqli_real_escape_string($this->link, $frm_Tag)."', 
        Mutation='".mysqli_real_escape_string($this->link, $frm_Mutation)."', 
        Clone='".mysqli_real_escape_string($this->link, $frm_Clone)."', 
        Vector='".mysqli_real_escape_string($this->link, $frm_Vector)."', 
        Description='".mysqli_real_escape_string($this->link, $frm_Description)."', 
        ProjectID='$frm_ProjectID'
        WHERE ID =$frm_Bait_ID ";
      //echo $SQL;//exit;  
      mysqli_query($this->link,$SQL);
   }//end of function update
   //------------------------------------------------
   //     check is user owne the record        --in use--
   //-----------------------------------------------
   
  function isOwner($Bait_ID=0, $Owner_ID=0){
    $SQL = "select ID from Bait where ID = $Bait_ID and OwnerID = $Owner_ID";
    $sqlResult = mysqli_query($this->link,$SQL);
    if(mysqli_num_rows($sqlResult)){
      return 1;
    }else{
      return 0;
    }
  }
   //--------------------------------------------------
   //    get a number of total records ---in use---
   //--------------------------------------------------
   
  function get_total($isDummy=0, $isGelFree=0){
    global $frm_user_id,$all_group_recodes_str,$frm_group_id_list,$first_show;
    $SQL = "SELECT COUNT(ID) FROM Bait";      
    $Where = " WHERE  ProjectID=$this->AccessProjectID";      
    $SQL .= $Where;
    if($isDummy){
      $SQL .= " AND Clone='dummy'";
    }
    if($isGelFree == '1'){
      $SQL .= " AND GelFree='1'";
    }else if($isGelFree == '2'){
      $SQL .= " AND (GelFree='0' OR GelFree IS NULL)";
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
    if(isset($all_group_recodes_str) && $all_group_recodes_str){
      $SQL .= " AND ID IN($all_group_recodes_str)";
    }elseif(isset($all_group_recodes_str) && !$all_group_recodes_str && isset($frm_group_id_list) && $frm_group_id_list){
      $SQL = '';
    }
    if($SQL){	    
      $row = mysqli_fetch_row(mysqli_query($this->link,$SQL));
    }else{
      $row[0] = 0;
    }
    return $row[0];
  }
   //---------------------------------------------------
   //   move bait - return a current bait
   //---------------------------------------------------
   function move_bait($whichBait, $Bait_ID=0){
     $re = $Bait_ID;
     $SQL = "SELECT ID FROM Bait";     
     $Where = " WHERE  ProjectID=$this->AccessProjectID";     
     $SQL .= $Where;
     // and ID > 7 limit 1
     if($whichBait == 'last'){
       $SQL .= " order by ID desc limit 1";
     }elseif($whichBait == 'first'){
       $SQL .= " order by ID limit 1";
     }elseif($whichBait == 'next' and $Bait_ID){
       $SQL .= " and  ID > $Bait_ID  order by ID limit 1";
     }elseif($whichBait == 'previous' and $Bait_ID){
       $SQL .= " and  ID < $Bait_ID  order by ID desc limit 1";
     }
     //echo $SQL;
     $row = mysqli_fetch_array(mysqli_query($this->link,$SQL));
     if($row[0]) $re = $row[0];
     return $re;
   }
   //----------------------------------------------------
   // check if the bait has hits to report
   //----------------------------------------------------
   function has_hits($Bait_ID){
    if(!$Bait_ID) return 0;
    $SQL = "SELECT ID FROM Hits where BaitID = '$Bait_ID' limit 1";
    $rt = mysqli_num_rows(mysqli_query($this->link,$SQL));
    //echo $SQL; exit;
    return $rt;
   }
   //----------------------------------------------
   //--------in use----*************
   //------------------------------------------------
   function has_exp($Bait_ID){
   if(!$Bait_ID) return 0;
    $SQL = "SELECT ID FROM Experiment where BaitID = '$Bait_ID'";
    //echo $SQL;
    $rt = mysqli_num_rows(mysqli_query($this->link,$SQL));
    return $rt;
   }
   //------------------------------------------------------
   // for page header search in search_bait.inc.php
   //------------------------------------------------------
   function search($searchThis='', $sub, $coipTargetORF=''){
     $SQL = "SELECT ID FROM Bait"; 
    $Where = " WHERE  ProjectID=$this->AccessProjectID";    
    
    if($coipTargetORF){
      $Where .= " and (LocusTag='$coipTargetORF' or LocusTag='$searchThis')";
    }else{
      $GeneIDitem = "";
      if(is_numeric($searchThis)){
        $GeneIDitem = "GeneID=$searchThis or"; 
      }
      $searchThis = mysqli_escape_string($this->link, $searchThis);
      $Where .= " and ($GeneIDitem LocusTag like '%$searchThis%' or GeneName like '%$searchThis%' or Clone like '%$searchThis%')";
    }
    if($sub == 1){
      $Where .= " and GelFree!=1";
    }elseif($sub == 3){
      $Where .= " and GelFree=1";
    }
    $SQL .= $Where;
    $i = 0;
    $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while ($row= mysqli_fetch_row($sqlResult) ) {
       list($this->ID[$i]) = $row;
       $i++;
     }
  }//end of function fetchall
  //----------------------------------------------------------
  // check if the hit and with the bait has done co-ip. then return
  // the co-ip result color 
  //----------------------------------------------------------
  function get_coip_color($baitID,$baitORF,$targetORF,$hasHit = false){
      $color_and_ID = array('color' => '','ID' => '');
      //check if the bait has this hit first.
  	if(!$hasHit){
    	  $SQL = "select ID from Hits where BaitID='$baitID' and LocusTag='$targetORF'";
  	  //if has the hit then get co-ip
  	  if(mysqli_num_rows(mysqli_query($this->link,$SQL))){
  	    $hasHit = true;
  	  }
  	}
  	if($hasHit){
  	  //look for the bait target as pair in coip
  	  $SQL = "select ID,Interaction from Coip where (BaitORF='$baitORF' and TargetORF='$targetORF') or (BaitORF='$targetORF' and TargetORF='$baitORF')";
  	  $results = mysqli_query($this->link,$SQL);
  	  
  	  if($row = mysqli_fetch_row($results)){
  	    switch ($row[1]){
  	    case "Yes":
  	        $color_and_ID['color'] = "green";
  		  break;
  	    case "No":
  	       $color_and_ID['color'] = "red";
  		 break;
  	    case "Possible":
  	       $color_and_ID['color'] = "yellow";
  		 break;
  	    case "In Progress":
  	       $color_and_ID['color'] = "blue";
  		 break;
  	    }
  	  }
  	  $color_and_ID['ID'] = $row[0];
  	}
  	//print_r($color_and_ID);
  	return $color_and_ID;
  }
  //----------------------------------------------------------
  // Used by search_hit.inc.php. Added by JP
  // May 09,2005
  //----------------------------------------------------------
  function hit_search($hit_LocusTag){
    $SQL = "SELECT 
       B.ID, 
       B.GeneID,
       B.LocusTag, 
       B.GeneName,
       B.BaitAcc,
       B.AccType, 
       B.TaxID, 
       B.BaitMW, 
       B.Family, 
       B.Tag,
       B.Mutation, 
       B.Clone, 
       B.Vector, 
       B.OwnerID, 
       B.ProjectID,
       B.GelFree,
       B.DateTime 
       FROM Bait B, Hits H
       WHERE  B.ProjectID='$this->AccessProjectID'
       and H.BaitID=B.ID and H.LocusTag='$hit_LocusTag' group by H.BaitID"; 
	   
     //echo $SQL;exit; 
       
	  $sqlResult = mysqli_query($this->link,$SQL);
    $this->count = mysqli_num_rows($sqlResult);
	
	  $i = 0;
    while (list(
      $this->ID[$i],
      $this->GeneID[$i], 
      $this->LocusTag[$i],
      $this->GeneName[$i],
      $this->BaitAcc[$i],
      $this->AccType[$i], 
      $this->TaxID[$i], 
      $this->BaitMW[$i], 
      $this->Family[$i], 
      $this->Tag[$i], 
      $this->Mutation[$i],
      $this->Clone[$i], 
      $this->Vector[$i], 
      $this->OwnerID[$i], 
      $this->ProjectID[$i],
      $this->GelFree[$i], 
      $this->DateTime[$i])= mysqli_fetch_row($sqlResult) ) {
      $i++;
    }
  }
	function getValue($fieldName, $i){
		print_r($this->$fieldName);exit;
	}
}//end of class
?>