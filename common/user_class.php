<?php 

/*****************************************************************
 Author: frank
    2002-02-18
    Desc: This calss for table User 
+-----------+----------------------------------------------+------+-----+---------+----------------+
| Field     | Type                                         | Null | Key | Default | Extra          |
+-----------+----------------------------------------------+------+-----+---------+----------------+
| ID        | int(11)                                      |      | PRI | NULL    | auto_increment |
| Username  | varchar(20)                                  | YES  |     | NULL    |                |
| Password  | varchar(20)                                  | YES  |     | NULL    |                |
| Fname     | varchar(25)                                  | YES  |     | NULL    |                |
| Lname     | varchar(35)                                  | YES  |     | NULL    |                |
| Phone     | varchar(15)                                  | YES  |     | NULL    |                |
| Email     | varchar(50)                                  | YES  |     | NULL    |                |
| LastLogin | datetime                                     | YES  |     | NULL    |                |
| Active    | enum('1','0')                                |      |     | 0       |                |
| Type      | enum('supervisor','MSTech','labTech','user') | YES  |     | user    |                |
| LabID     | tinyint(4)                                   | YES  |     | NULL    |                |
+-----------+----------------------------------------------+------+-----+---------+----------------+


*****************************************************************/

class User {

  var $ID;
  var $Username;
  var $Password;
  var $Fname;
  var $Lname;
  var $Phone;
  var $Email;
  var $LastLogin; //to keep track of users latest login times...
  var $Type;
     
  var $LabID;
  var $Active;
  var $AccessUsers_str; //all users can be accessed by the user
  var $AccessPages_arr = array();//
  var $UsersArr = array();
  var $count;
  var $db_link;

  function User($Username = "", $ID="", $db_link='') {
    if(is_object($Username)){
      $this->db_link = $Username;
      $Username = '';
    }
    $this->ID = $ID;
    $this->Username = $Username;
    $this->count = 0;
    if($db_link){
      $this->db_link = $db_link;
    }else{
      global $PROHITSDB;
      $this->db_link = $PROHITSDB->link;
    }
    if($this->ID) {
      $this->fetch();
    } elseif($this->Username) {
      $this->fetch_ID();
      $this->fetch();
    }
    if($this->ID){
      //if the user is supervisor or labTach or within a Group
    // get users string $AccessUsers_str = "1,2,3" the user can access
		  $this->AccessPages_arr = array();
		  $SQL = "select A.ScriptName from Page A, PagePermission B where B.PageID=A.ID and B.UserID='".$this->ID."' ORDER BY A.PageName";
	    $results = mysqli_query($this->db_link, $SQL);
		 
      while($Row = mysqli_fetch_array($results) ){
        array_push($this->AccessPages_arr,$Row[0]);
		  }
      $this->AccessUsers_str = '';
		  $SQL = '';
      if($this->Type == "supervisor" Or $this->Type == "labTech"){
        $SQL = "select ID from User where LabID = $this->LabID";
      }else if($this->Type == "MSTech"){
        $SQL = "select ID from User";
      }else{
        //user himself
        $this->AccessUsers_str = $this->ID;
      }
      if($SQL){
        //echo $SQL;
        $results = mysqli_query($this->db_link, $SQL);   
        $i = 0;
        while ( $Row =  mysqli_fetch_row($results) ) {
          if($i){
            $this->AccessUsers_str .= ",". "$Row[0]";
          }else{
             $this->AccessUsers_str .= "$Row[0]";
          }
          $i++;
        }
      }
      //$this->get_IDNamePair();	
    }
  } //end of function Users
  
  function get_IDNamePair(){
    $SQL = "select ID, Fname, Lname from User";
    $results = mysqli_query($this->db_link, $SQL); 
    while ( $Row =  mysqli_fetch_row($results) ){
      $this->UsersArr[$Row[0]] = $Row[1].' '.$Row[2];
    }
  }
  
   
  function init() {
    $this->ID = "";
    $this->Username = "";
    $this->Password = "";
    $this->Fname = "";
    $this->Lname = "";
    $this->Phone = "";
    $this->Email = "";
    $this->LastLogin = "";
		$this->AccessUsers_str = "";
	  $this->AccessPages_arr = array();
    $this->Type = "";
     
    $this->UsersArr = array();
    $this->LabID = "";
  }

  function fetch_ID($Username="") {
    if($Username) $this->Username = $Username;
    if(!$this->Username) {
      echo "No Eed Username to fetch_ID!!! - error dump from bo_user_class.php";
      exit;
    }
    $SQL = "SELECT ID FROM User WHERE Username = '$this->Username'";
    list($this->ID) = mysqli_fetch_array(mysqli_query($this->db_link, $SQL));
  }
  
   
  function fetch($ID="") {
    if($ID) $this->ID = $ID;
    $SQL = "SELECT ID, Username, Password, Fname, Lname, Phone, Email, LastLogin, Type, LabID, Active ".
      "FROM User ".
      "WHERE ID = '$this->ID' ";
      //echo $SQL;
    list($this->ID,
    $this->Username,
    $this->Password,
    $this->Fname,
    $this->Lname,
    $this->Phone,
    $this->Email,
    $this->LastLogin,
    $this->Type,
     
    $this->LabID,
    $this->Active) = mysqli_fetch_array(mysqli_query($this->db_link, $SQL));
  } //end of function fetch

   
  function fetchall() {
    $SQL = "SELECT ID, Username, Password, Fname, Lname, Phone, Email, LastLogin, Type,  LabID, Active ".
      "FROM User ".
      "ORDER BY Active, LabID, Fname";
      //"ORDER BY Active desc , LabID, Fname";
    $i = 0;   
    $sqlResult = mysqli_query($this->db_link, $SQL);
    $this->count = mysqli_num_rows($sqlResult);
    while ($row = mysqli_fetch_row($sqlResult) ) {
      
    list($this->ID[$i], 
         $this->Username[$i],
         $this->Password[$i],
         $this->Fname[$i],
         $this->Lname[$i],
         $this->Phone[$i],
         $this->Email[$i],
         $this->LastLogin[$i],
         $this->Type[$i],
          
         $this->LabID[$i],
         $this->Active[$i]) = $row;
       $i++;
     }        
      
   } //end of function fetch all
  
  
  function update($ID, $Username, $Password, $Fname, $Lname, $Phone, $Email, $Type, $LabID) {
    $SQL = "UPDATE User ".
       "SET Username= '$Username', Fname = '$Fname', Lname = '$Lname', Phone = '$Phone', Email = '$Email', Type='$Type', LabID='$LabID'";
    if($Password){
      $Password = encrypt_pwd($Password);
      $SQL .= ", Password = '$Password'";
    }
    $SQL .=   " WHERE ID = $ID";
       //echo $SQL;
    mysqli_query($this->db_link, $SQL);
    $this->ID = $ID;
    $this->fetch();
  } //end of function update
  function insert($Username = "", $Password, $Fname, $Lname, $Phone, $Email, $Type, $LabID) {
    
    if((!$Username) OR (!$Password) OR (!$Email) OR (!$Type)) {
      echo "Missing info...insert aborted";
      exit;
    } else {
      $Password = encrypt_pwd($Password);
      $SQL = "INSERT INTO User ".
         "VALUES(0,'$Username', '$Password', '$Fname', '$Lname', '$Phone', '$Email', '', '1','$Type','$LabID')";
      mysqli_query($this->db_link, $SQL);      
      // get new user information
      $SQL = "select * from User where Username='".$Username."'";
      list($this->ID,
           $this->Username,
           $this->Password,
           $this->Fname,
           $this->Lname,
           $this->Phone,
           $this->Email,
           $this->LastLogin,
           $this->Type,
           $this->LabID,) = mysqli_fetch_array(mysqli_query($this->db_link, $SQL));
    }
  } //end of function insert

  function log($ID = "") {
    if($ID) {
      $date_time = @date("YmdHis");
      $SQL = "UPDATE User ".
         "SET LastLogin = '$date_time' ".
         "WHERE ID = '$ID'";       
      mysqli_query($this->db_link, $SQL);
			$this->LastLoin = $date_time;
    } else {
      echo "Missing info...update aborted";
      exit;
    }
  } //end of function log

  function deactivate($uID="") {
    if(!$uID) {
      echo "Need uID to deactivate!! - error dump from User_class.php";
      exit;
    }
    $SQL = "UPDATE User ".
       "SET Active = '0' ".
       "WHERE ID = $uID";
    mysqli_query($this->db_link, $SQL);

  } //end of function deactivate
  function activate($uID="") {
    if(!$uID) {
      echo "Need uID to deactivate!! - error dump from User_class.php";
      exit;
    }
    $SQL = "UPDATE User ".
       "SET Active = '1' ".
       "WHERE ID = $uID";
    mysqli_query($this->db_link, $SQL);

  } //end of function activate
  
  function change_passwd($Username, $old_Password, $new_Password){
    $new_Password = encrypt_pwd($new_Password);
    $SQL = "UPDATE User SET Password = '$new_Password' WHERE Username='$Username'";
    mysqli_query($this->db_link, $SQL);
  }
  
  function getAccessUsers($AccessUsers_str){
    if($AccessUsers_str){
      $SQL = "SELECT ID, Username, Password, Fname, Lname, Phone, Email, LastLogin, Type, LabID ".
      "FROM User ".
      "WHERE Active = '1' and ID in ($AccessUsers_str) ".
      "ORDER BY LabID,Fname";

      $i = 0;
      //echo $SQL;
      $sqlResult = mysqli_query($this->db_link, $SQL);
      $this->count = mysqli_num_rows($sqlResult);
      while ($row = mysqli_fetch_row($sqlResult)){
        list($this->ID[$i], 
           $this->Username[$i],
           $this->Password[$i],
           $this->Fname[$i],
           $this->Lname[$i],
           $this->Phone[$i],
           $this->Email[$i],
           $this->LastLogin[$i],
           $this->Type[$i],
           $this->LabID[$i]) = $row;
         $i++;
       }
    }else{
      echo "error: no AccessUsering string";
    }
  }//end of function getAccessUsers
}

?>
