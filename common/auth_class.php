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

class Auth {
  
  var $ID;
  var $Access;
  var $Insert;
  var $Modify;
  var $Delete;
  var $ProjPageID;
  var $ProjPageName;
  var $db_link;
  
  function Auth($UserID = 0, $Type='', $ProjPageID="", $ProjPageName=""){
    global $PROHITSDB;
    $this->db_link = $PROHITSDB->link;
    if($UserID and $Type and ($ProjPageID or $ProjPageName)){
      $this->fetch($UserID, $Type, $ProjPageID, $ProjPageName);
    }
  }

  function fetch($UserID = 0, $Type = '', $ProjPageID = "", $ProjPageName = ""){
    $this->ID = $UserID;
    $this->ProjPageID = $ProjPageID;
    $this->ProjPageName = $ProjPageName;
    $this->Access = false;
    $this->Insert = false;
    $this->Modify = false;
    $this->Delete = false;

    if(!$ProjPageID and $ProjPageName) {
      $this->get_page_id($Type);
    }
    
    if ($this->ID != "") {
      $SQL = "";      
      if($Type == "project"){
        $SQL = "select * from ProPermission ".
             " where UserID = ".$this->ID.
             " and ProjectID  = ".$this->ProjPageID;
      }else{
        $SQL = "select * from PagePermission ".
             " where UserID = ".$this->ID.
             " and PageID  = ".$this->ProjPageID;
      }
      
      //echo $SQL;
      $sqlResult = mysqli_query($this->db_link, $SQL);
      // first check if there's any row were send back
    
      if ( (mysqli_num_rows($sqlResult)) > 0 )  {
          $this->Access = true;    
          // then check if the user has rights
          $line = mysqli_fetch_array($sqlResult);        
      if ($line['Insert'] == '1') 
        $this->Insert = true;
      if ($line['Modify'] == '1') 
        $this->Modify = true;
      if ($line['Delete'] == '1') 
        $this->Delete = true; 
	  }
    }
  } //end of function Auth

  function get_page_id($Type = '') {
    $SQL = "";
    if($Type == "page"){
      $SQL = "SELECT ID FROM Page WHERE ScriptName like '$this->ProjPageName'";
//echo $SQL;
    }else{
      $SQL = "SELECT ID FROM Projects WHERE Name like '$this->ProjPageName'";
    } 
    list($this->ProjPageID) = mysqli_fetch_array(mysqli_query($this->db_link, $SQL));
  } //end of function get_page_id
  
  function isOwner($table,$recordID,$userID){
    global $HITSDB;
    $SQL = "select ID from $table where ID = '$recordID' and OwnerID = '$userID'";
    $sqlResult = mysqli_query($HITSDB->link, $SQL);
    if(mysqli_num_rows($sqlResult)){
			return 1;
		}else{
			return 0;
		}
  }
}//end of class
?>
