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

require("../config/conf.inc.php");
require("../common/mysqlDB_class.php");
$mainDB = new mysqlDB(DEFAULT_DB);
require("../common/user_class.php");

//$bgcolor = "#e9e1c9";
//$bgcolordark = "#c5b781";
$bgcolordark = $TB_HD_COLOR;
$bgcolor = $TB_CELL_COLOR;

$theaction = '';
$frm_FirstName = '';
$frm_LastName = '';
$message = '';
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;  
}  
session_start();
//----------------------------------------------------------


?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<title>Gel Image</title>
	<link rel="stylesheet" type="text/css" href="./site_style.css"> 
</head>
<BODY>
<center>
<script language="javascript">
function trimString(str){
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}

function checkform(){
  var theForm = document.changePwForm;
  var firstName = trimString(theForm.frm_FirstName.value);
  var lastName = trimString(theForm.frm_LastName.value);
  if(!firstName || !lastName){
    alert("firstName or lastName cannot be empty.");
  }else{  
    theForm.theaction.value = "confirm";
    theForm.submit();
  }   
}

function confirm(){
  var theForm = document.changePwForm; 
  theForm.theaction.value = "sentMail";
  theForm.submit();
}
</script>
<form name=changePwForm method=post action=<?php echo $_SERVER["PHP_SELF"];?> enctype="multipart/form-data">
<input type="hidden" name="theaction" value="">
<table border="0" cellpadding="1" cellspacing="1" width="350">
  <tr bgcolor="<?php echo $bgcolordark;?>">
    <td colspan="2" align="center" height=20>
     <div class=tableheader>Send Prohits account to me</div>
    </td>
  </tr> 
  <?php 
  //echo "<div class=maintext><font color=red size=2><b>$message</b></font></div><br> ";
  if($theaction == "confirm"){
    $userArr = array();
    $confirmed = 0;
    if(isset($_SESSION['VCODE'])){
      if($frm_code == $_SESSION['VCODE']){
       $confirmed = 1;
      }else{
        $message = "<font color=red size=2>Please enter the code shown.</font>";
      }
    }
    if($confirmed){
      $SQL = "SELECT ID, Email, Active FROM User WHERE Fname='$frm_FirstName' AND Lname='$frm_LastName'";  
      $userArr = $mainDB->fetch($SQL);  
      if(!$userArr){
        $message = "<font color=red size=2><b>Please enter your correct FirstName and LastName.</b></font>";
      }else if(!$userArr['Active']){
        $message = "<font color=red size=2><b>Your account is closed. Please contact ProHits Administrator.</b></font>";
      }else if(!strstr($userArr['Email'], '@')){
        $message = "<font color=red size=2><b>ProHits couldn't find your email address. Please contact ProHits Administrator.</b></font>";  
      }else{
        $message = "Please click Confirm button if following information is correct.";
      }
    }
 ?>
 <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="center" colspan=2 bgcolor="<?php echo $bgcolor;?>">
    <table border="0" cellpadding="1" cellspacing="1" width="75%">
      <tr bgcolor="<?php echo $bgcolor;?>">
      <td align="center" colspan=2 bgcolor="<?php echo $bgcolor;?>">
      <table border="0" cellpadding="0" cellspacing="1" width="100%">      
  	    <tr bgcolor="">
  	      <td width="30%" bgcolor="<?php echo $bgcolor;?>"><br>
  	        <div class=maintext><?php echo $message;?></div> 
  	      </td>	      
  	    </tr>
       </table>
      </td>	
      </tr>
	    <tr bgcolor="">
	      <td width="30%" bgcolor="<?php echo $bgcolor;?>" align="right">
	        <div class=maintext><b>First Name:</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>"><div class=maintext>
	      &nbsp;&nbsp;<?php echo $frm_FirstName?></div>
        </td>
	    </tr>
	    <tr bgcolor="">
	      <td width="" bgcolor="<?php echo $bgcolor;?>" align="right">
	        <div class=maintext><b>Last Name:</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>"><div class=maintext>
	      &nbsp;&nbsp;<?php echo $frm_LastName?></div>
        </td>
	    </tr>
      <?php if($userArr and $userArr['Email']){?> 
      <tr bgcolor="">
	      <td width="" bgcolor="<?php echo $bgcolor;?>" align="right">
	        <div class=maintext><b>Email:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>"><div class=maintext>
	      &nbsp;&nbsp;<?php echo (isset($userArr['Email']))?$userArr['Email']:""?></div>
        </td>
	    </tr> 
      <?php }?>           
      <tr bgcolor="<?php echo $bgcolor;?>" align="center">
        <td colspan="2"><br>
      <?php if($userArr && $userArr['ID'] && strstr($userArr['Email'], '@') && $userArr['Active']){?>
          <input type="hidden" name="userID" value=<?php echo$userArr['ID']?>> 
          <input type="button" value="Confirm" class=green_but onClick="javascript: confirm();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <?php }else{?>
          <input type="button" value="Back" class=green_but onClick="javascript: submit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
      <?php }?>    
          <input type="button" value="Close" class=green_but onClick="javascript: window.close();"><br>&nbsp;
        </td>
      </tr>
     </table>
    </td>	
  </tr>
  <?php 
  }else if($theaction == "sentMail" and $userID){
    $SQL = "SELECT  Username, Password, Fname, Lname, Email FROM User WHERE ID=$userID";  
    $userArr = $mainDB->fetch($SQL);
    $backMessage = '';
    $script_path = dirname($_SERVER["PHP_SELF"])."/pop_change_password.php";
    $url_change_pw = "http://".$_SERVER["HTTP_HOST"].$script_path."?en=".$userArr['Password'];
    
     
    
    if($userArr && strstr($userArr['Email'], '@')){
      $message = $userArr['Fname'].",\n\n<br><br>Below is your Prohits account information.\r\n<br>First Name: ".
                $userArr['Fname']."\n<br>Last Name: ".
                $userArr['Lname']."\n<br>Username: ".
                $userArr['Username'];
       
      $message .= "\n<br>Set your Prohits password from following link.<br>$url_change_pw";
                 
      
      $e_to = $userArr['Email'];
      $e_msg = $message;
      
      $e_subject = "Sent prohits account to ".$userArr['Fname'];
      if(defined("ADMIN_EMAIL")){
        $e_from = ADMIN_EMAIL;
        $e_replay = ADMIN_EMAIL;
      }else{
        $e_from = 'ProhitsAdmin';
        $e_replay = 'ProhitsAdmin';
      }
      
      $ret = send_mail($e_to, $e_msg, $e_subject, $e_from, $e_replay);
      if($ret){
        $backMessage = "Your account information have been sent to your email box";
      }else{
        $backMessage = "<font color=red size=2><b>Please contact your Administrator</b></font>";
      }
    }else{
      $backMessage = "<font color=red size=2><b>Please contact your Administrator to correct your email address.</b></font>";
    }  
  ?>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="center" colspan=2 bgcolor="<?php echo $bgcolor;?>">
    <table border="0" cellpadding="1" cellspacing="1" width="75%">
      <tr bgcolor="<?php echo $bgcolor;?>">
      <td align="center" colspan=2 bgcolor="<?php echo $bgcolor;?>">
      <table border="0" cellpadding="0" cellspacing="1" width="100%">      
  	    <tr bgcolor="">
  	      <td width="30%" bgcolor="<?php echo $bgcolor;?>"><br>
  	        <div class=maintext><?php echo $backMessage?></div> 
  	      </td>	      
  	    </tr>
       </table>
      </td>	
      </tr>
	    <tr bgcolor="">	      
      <tr bgcolor="<?php echo $bgcolor;?>" align="center">
        <td colspan="2"><br>
          <input type="button" value="Close" class=green_but onClick="javascript: window.close();"><br>&nbsp;
        </td>
      </tr>
     </table>
    </td>	
  </tr>
 <?php }else{?> 
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="center" colspan=2 bgcolor="<?php echo $bgcolor;?>">
    <table border="0" cellpadding="1" cellspacing="1" width="90%">
      <tr bgcolor="<?php echo $bgcolor;?>">
      <td align="center" colspan=2 bgcolor="<?php echo $bgcolor;?>">
      <table border="0" cellpadding="0" cellspacing="1" width="100%">      
  	    <tr bgcolor="">
  	      <td width="30%" bgcolor="<?php echo $bgcolor;?>"><br>
  	        <div class=maintext>Enter your First Name and Last Name then click submit button. Your Prohits Account will be sent to your email box.</div> 
  	      </td>	      
  	    </tr>
       </table>
      </td>	
      </tr>
	    <tr bgcolor="">
	      <td width="30%" bgcolor="<?php echo $bgcolor;?>" align="right">
	        <div class=maintext><b>First Name:</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>">
	      &nbsp;&nbsp;<input type="text" name="frm_FirstName" size="20" maxlength=15 value="">
        </td>
	    </tr>
	    <tr bgcolor="">
	      <td width="" bgcolor="<?php echo $bgcolor;?>" align="right">
	        <div class=maintext><b>Last Name:</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>">
	      &nbsp;&nbsp;<input type="text" name="frm_LastName" size="20" maxlength=15 value="">
        </td>
	    </tr> 
      <tr bgcolor="">
	      <td width="" align="right" bgcolor="<?php echo $bgcolor;?>" nowrap>
	        <div class=maintext><b>Enter the Code shown:</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>">
	      &nbsp;&nbsp;<input type="text" name="frm_code" size="8" maxlength=8 value="">
        <img src="./confirmCode.php">
        </td>
	    </tr>     
      <tr bgcolor="<?php echo $bgcolor;?>" align="center">
        <td colspan="2"><br>
          <input type="button" value="Submit" class=green_but onClick="javascript: checkform();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <input type="button" value="Close" class=green_but onClick="javascript: window.close();"><br>&nbsp;
        </td>
      </tr>
     </table>
    </td>	
  </tr>
 <?php }?>
</table>
</form>
</center>
</body>
</html>
<?php 
//******************************************************************
function send_mail($to, $msg,  $subject='', $from='', $replayTo=''){
//******************************************************************
  if(!$to or !$msg){
    echo 'need $to or $msg to send a email!';
    exit;
  }
  
  
  return mail($to, $subject, $msg, "From: $from\r\nContent-type: text/html\r\n");
}
//********************************************************************
?>
