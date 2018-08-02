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

class Session {

  var $SID;
  var $name;  //array  variables
  var $value; //array  values
  
  var $new_name;  
  var $new_value;
  var $link;
  function Session($action="", $SID="", $name="", $value="") { 
    global $PROHITSDB;
    $this->link = $PROHITSDB->link;
    $this->SID = $SID;
    $this->new_name = $name;
    $this->new_value = $value;

    switch($action) {
      case "create":
	     $this->generate_sid();
	     $this->insert();
      break;

      case "append":
	     if(!$this->SID OR !$this->new_name) {
	       echo "Need sid, name and value to append!!!";
	       exit;
	     } else {
	       $this->append();
	     }
      break;

      case "purge":
 	     if(!$this->SID) {
	       echo "Need sid to purge!!!";
	       exit;
	     } else {
	       $this->purge();
	     }
      break;
      
      case "update":
 	     if(!$this->SID) {
	      echo "Need sid to update!!!";
	      exit;
	     } else {
	       $this->update();
	     }
      break;
      
	  case "set":
	   if(!$this->SID) {
		   echo "Need sid to set!!!";
		   exit;
	   } else {
		   if($this->value[$this->new_name]) {
			   $this->update();
		   }else{
			   $this->insert();
		   }
	   }
	  break;
	  
      case "fetchall":
	     if(!$this->SID) {
	       echo "Need sid to fetch all!!!";
	       exit;
	     } else {
	       $this->fetchall();
	     }
      break;
	  
	  case "deleteOneDayOld":
	    $this->deleteOneDayOld();
	  break;
      default:
    }
  }

  function fetchall() {
    $SQL = "SELECT sid, name, value FROM session WHERE sid = '$this->SID'";
    $results = mysqli_query($this->link, $SQL);

    while($my_row = mysqli_fetch_object($results)) {
      $this->value[$my_row->name] = $my_row->value;      
    }
  }

  function insert() {

    if($this->SID) {

      if(($this->new_name) AND ($this->new_value)) {
       $SQL = "INSERT INTO session VALUES('$this->SID', '$this->new_name', '$this->new_value')";
        mysqli_query($this->link, $SQL);
		  
      } else {
        global $REMOTE_ADDR;
        $SQL = "INSERT INTO session VALUES('$this->SID','remote ip','$REMOTE_ADDR')";
        mysqli_query($this->link, $SQL);
	      $SQL = "INSERT INTO session VALUES('$this->SID','session','created')";
        mysqli_query($this->link, $SQL);
        $SQL = "insert into session values('$this->SID', 'created on', '".@date("YmdHis")."')";
        mysqli_query($this->link, $SQL);
		 
      }
    } else {
      echo "Need sid to insert!!!";
      exit;
    }
  } //end of functio insert

  function append() {
   $SQL = "INSERT INTO session VALUES('$this->SID', '$this->new_name','$this->new_value')";
    mysqli_query($this->link, $SQL);
  } //end of function append
  
  function update() {
    $SQL = "UPDATE session set value = '$this->new_value' where sid = '$this->SID' and name = '$this->new_name' ";
    mysqli_query($this->link, $SQL);
  }
  function purge() {
    $SQL = "select value FROM session WHERE sid = '$this->SID' and name='remote ip'";
    if($row = mysqli_fetch_array(mysqli_query($this->link, $SQL))){
      $remote_ip = $row['value'];
       
      $SQL = "select sid FROM session WHERE name='remote ip' and value='$remote_ip'";
      $results = mysqli_query($this->link, $SQL);
      while($row = mysqli_fetch_array($results)){
        $SQL = "DELETE FROM session WHERE sid = '".$row['sid']."'";
        mysqli_query($this->link, $SQL);
      }
       
    }
    $SQL = "DELETE FROM session WHERE sid = '$this->SID'";
    mysqli_query($this->link, $SQL);
  } //end of function purge
 function delete_uid() {
    $SQL = "DELETE FROM session WHERE sid = '$this->SID' and name = 'cust_id' ";
    mysqli_query($this->link, $SQL);
  } //end of function purge

  function generate_sid() {
    $SID = @date("Ymdhis") . rand(1000,9999);
    $this->SID = md5($SID);
  } //end of function generate_sid

  function check_SID($SID, $validHour=0) {
    $SQL = "SELECT sid, value FROM session WHERE sid='$SID' and name='created on'";
    if($validHour){
      $expires =@time() - 3600 * $validHour;
      $theTime = @date("YmdHis", $expires);
      $SQL .= " and value > '$theTime'";
    }
    $result = mysqli_query($this->link, $SQL);
    if(mysqli_num_rows($result)) {
      return 1;
    } else {
      return 0;
    }
  } //end of function check_SID
  
  function deleteOneDayOld(){
    //the function will be called from index.php. before user login
	$expires =@time() - 3600 * 24;
	$theTime = @date("YmdHis", $expires);
	$SQL = "SELECT sid FROM session WHERE name='created on'";
    $SQL .= " and value < '$theTime'";
	echo $SQL;
    $result = mysqli_query($this->link, $SQL);
	while($row = mysqli_fetch_array($result)){
	  mysqli_query($this->link, "delete from session WHERE sid='".$row[0]."'");
	}
  }
}//end of file

