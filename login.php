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

require("./config/conf.inc.php");
require("./common/common_fun.inc.php");
require("./common/mysqlDB_class.php");
$mainDB = new mysqlDB(DEFAULT_DB);
require("./common/user_class.php");


$theaction = '';
$frm_Username = '';
$frm_Password = '';
//$RTPAGE = '';
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;  
}
foreach ($request_arr as $key => $value) {
  $$key=$value;  
} 

/* set the cache expire to 30 minutes */
//session_cache_limiter('private');
//session_cache_expire(30);  
  
session_start();
if($frm_Username and $frm_Password) {  
  if (!isset($_SESSION['logintime'])){
    //cookies have been disabled in client browser
    header ("Location: cookiesOff.php");
    exit;
  }
  $User = new User($frm_Username, '', $mainDB->link);
  //if($User->Password == $frm_Password) {
  if($User->Password == encrypt_pwd($frm_Password, $User->Password) or $User->Password == $frm_Password){
    //add to Session table. when logout SID where be emptied
     
    //remove seeeion id is older than  hours
    $SQL = "delete from Session where Date <'". @date("Y-m-d H:i:s", @time()-48*3600)."'";
    $mainDB->execute($SQL);
    if(!$mainDB->exist("select SID from Session where SID='".session_id()."'")){
      $SQL = "insert into Session set UserID='". $User->ID."', SID='".session_id()."', IP='". $_SERVER["REMOTE_ADDR"]. "', Date=now()";
    }else{
      $SQL = "update Session set UserID='". $User->ID."', IP='". $_SERVER["REMOTE_ADDR"]. "', Date=now() where SID='".session_id()."'";
    }
    $mainDB->execute($SQL);
		$User->log($User->ID);
		$_SESSION['USER'] = $User;
    unset($_SESSION["workingProjectID"]); 
    unset($_SESSION["workingProjectName"]);
    unset($_SESSION["workingProjectTaxID"]);
    unset($_SESSION["workingFilterSetID"]);
    unset($_SESSION["workingDBname"]);
    unset($_SESSION["workingProjectFrequency"]);
    unset($_SESSION["superUsers"]);
    
    if(isset($RTPAGE)) {
      $url = $RTPAGE;
      header ("Location: $url");      
      exit;
    }else{    
      header ("Location: ../");
      exit;
    }
  } 
}else{
  //$_SESSION = array();
	$logintime = @time(); 
  $_SESSION["logintime"] = $logintime;
}


if(!isset($RTPAGE)){
    $RTPAGE = "./analyst/";
}
if(strstr($RTPAGE, "analyst") ){
    $login_to = "Analyst";
}else if(strstr($RTPAGE, "Manager") ){
    $login_to = "MS Management";
}else if(strstr($RTPAGE, "admin") ){
    $login_to = "Admin Office";
}
 
include("./main_menu_header.php");
?>
<script language="javascript">
function change_password(){
  var file = './admin_office/pop_change_password.php';
  newNote = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=250');
  newNote.moveTo(1,1);
}
function forget_password(){
  var file = './admin_office/pop_forget_password.php';
  newNote = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=250');
  newNote.moveTo(1,1);
}
</script>

<table border=0 cellspacing=1 cellpadding="5" width=300>
<form method=post action=<?php echo $_SERVER["PHP_SELF"];?>>
<input type=hidden name=theaction value=sumitted>
<input type=hidden name=RTPAGE value=<?php echo $RTPAGE;?>>
  <tr>
    <td colspan=2 align=center >
      <font color=#3F569B face="helvetica,arial,futura" size=3>
	<b>Login <?php echo $login_to;?></b>
      </font>
    </td>
  <tr>
  <tr>
    <td align=right >
      <font face="helvetica,arial,futura" size=2>
	<b>User Name:</b>
      </font>
    </td>
    <td>
    <font face="helvetica,arial,futura" size=2>
      &nbsp;&nbsp;<input type=text size=15 maxlength=15 name=frm_Username value="<?php echo $frm_Username;?>">
      </font>
    </td>
  <tr>
  <tr>
    <td align=right>
      <font face="helvetica,arial,futura" size=2>
	<b>Password:</b>
      </font>
    </td>
    <td>
    <font face="helvetica,arial,futura" size=2>
      &nbsp;&nbsp;<input type=password size=15 name=frm_Password value="">
    </font>
    </td>
  <tr>
  <tr>
    <td colspan=2 align=center><br>
      <input type=submit value="LogIn" class=green_but>
      <br><br>
      <!--a href="./admin_office/pop_change_password.php" class=button>Change your ProHits account password</a-->
      <a href="javascript: change_password();" class=button>Change your ProHits account password</a>
      <br>
      <a href="javascript: forget_password();" class=button>forget password?</a>
    </td>
  <tr>
</form>
</table> 
<?php include("./main_menu_footer.php");?>