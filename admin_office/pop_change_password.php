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
require("../common/common_fun.inc.php");

//$bgcolor = "#e9e1c9";
//$bgcolordark = "#c5b781";

$bgcolordark = $TB_HD_COLOR;
$bgcolor = $TB_CELL_COLOR;

$theaction = '';
$frm_UserName = '';
$frm_OldPassword = '';
$message = '';
$en = '';

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
  var newPw = trimString(theForm.frm_NewPassword.value);
  var userName = trimString(theForm.frm_UserName.value);
  var oldPw = trimString(theForm.frm_OldPassword.value); 
  var rePw = trimString(theForm.frm_RePassword.value);
  var en = trimString(theForm.en.value);
  if(!userName  || !newPw || !rePw){
    alert("All fields are require to fill in");
  }else if(!en && !oldPw){
    alert("All fields are require to fill in");
  }else if(/\W/.test(newPw)){
    alert("Please enter characters '0-9,A-Z,a-z' only.");
    theForm.frm_NewPassword.value = '';
    theForm.frm_NewPassword.focus();
  }else if(newPw.length > 10 || newPw.length < 4){
    alert("Please enter 4-10 characters for the new password.");
    theForm.frm_NewPassword.value = '';
    theForm.frm_NewPassword.focus();
  }else if(newPw != theForm.frm_RePassword.value)  {
    alert("The passwords that you typed in do not agree with one another. Please try again");
    theForm.frm_RePassword.focus();
  }else{
    theForm.theaction.value = "change";
    theForm.submit();   
  }
}
</script>
<?php 
if($theaction == "change"){
  $confirmed = 0;
  if(isset($_SESSION['VCODE'])){
    if($frm_code == $_SESSION['VCODE']){
     $confirmed = 1;
    }else{
      $message = "Please enter the code shown.";
    }
  }
  if($confirmed){
    $SQL = "SELECT ID, Active, Password FROM User WHERE Username='$frm_UserName'";  
    $userArr = $mainDB->fetch($SQL);  
    if(!$userArr){
      $message = "Please enter correct User Name or Old Password.";
      $frm_UserName = '';
      $frm_OldPassword = '';
    }else{
      if($userArr['Active']){
        if($en){
          if($en != $userArr['Password'] ){
           $message = "Please type correct User name.";
          }
        }else{
          if($userArr['Password'] != encrypt_pwd($frm_OldPassword, $userArr['Password'])){
            $message = "Please enter correct User Name or Old Password.";
          }
        }
         
        if(!$message){
          $frm_NewPassword = encrypt_pwd($frm_NewPassword);
          $SQL = "UPDATE User SET 
                  Password='$frm_NewPassword'
                  WHERE ID='".$userArr['ID']."'";
          $re = $mainDB->execute($SQL);
          if($re){        
            $message = "You new Password has been set up.";
            echo "<div class=maintext><font color=red size=3><b>$message</b></font></div><br> ";
            echo "  <input type='button' value=' Close ' class=green_but onClick='javascript: window.close();'>";
            exit;
          }else{
            $message = "For some reason you password cannot be changed. Please contact ProHits administrator";
          }  
        }
      }else{
        $message = "Your account has been close. Please contact ProHits administrator";
      }    
    }
  }
}

?>

<form name=changePwForm method=post action=<?php echo $_SERVER["PHP_SELF"];?> enctype="multipart/form-data">
<input type="hidden" name="theaction" value="">
<table border="0" cellpadding="1" cellspacing="1" width="450">
  <tr bgcolor="<?php echo $bgcolordark;?>">
    <td colspan="2" align="center" height=20>
     <div class=tableheader>Change Password</div>
    </td>
  </tr> 
  <?php 
    echo "<div class=maintext><font color=red size=2><b>$message</b></font></div><br> ";
  ?>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="center" colspan=2 bgcolor="<?php echo $bgcolor;?>">
    <table border="0" cellpadding="1" cellspacing="1" width="100%">
      <tr bgcolor="">
	      <td width="" bgcolor="<?php echo $bgcolor;?>" colspan=2>
          <table border="0" cellpadding="1" cellspacing="1" width="70%" align="center">
            <tr bgcolor="">
              <td width="" bgcolor="<?php echo $bgcolor;?>">
	              <div class=maintext>For new password enter characters A-Z, a-z and 0-9 only</b></div>
              </td>
            </tr> 
          </table>        
	      </td>	      
	    </tr>
	    <tr bgcolor="">
	      <td width="30%" align="right" bgcolor="<?php echo $bgcolor;?>">
	        <div class=maintext>&nbsp;&nbsp;&nbsp;&nbsp;<b>User Name:</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>">
	      &nbsp;&nbsp;<input type="text" name="frm_UserName" size="23" maxlength=15 value="<?php echo $frm_UserName?>">
        </td>
	    </tr>
      <?php 

      if($en){
        echo "<input type='hidden' name=en value='$en'>\n
              <input type='hidden' name=frm_OldPassword>";
      }else{
      ?> 
        <input type='hidden' name=en value=''>
  	    <tr bgcolor="">
  	      <td width="" align="right" bgcolor="<?php echo $bgcolor;?>">
  	        <div class=maintext>&nbsp;&nbsp;&nbsp;&nbsp;<b>Old Password:</b></div> 
  	      </td>
  	      <td width="" bgcolor="<?php echo $bgcolor;?>">
  	      &nbsp;&nbsp;<input type="password" name="frm_OldPassword" size="25" maxlength=15 value="">
          </td>
  	    </tr>
      <?php }?>
      
      <tr bgcolor="">
	      <td width="" align="right" bgcolor="<?php echo $bgcolor;?>">
	        <div class=maintext>&nbsp;&nbsp;&nbsp;&nbsp;<b>New Password:</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>">
	      &nbsp;&nbsp;<input type="password" name="frm_NewPassword" size="25" maxlength=15 value="">
        </td>
	    </tr>
      <tr bgcolor="">
	      <td width="" align="right" bgcolor="<?php echo $bgcolor;?>">
	        <div class=maintext>&nbsp;&nbsp;&nbsp;&nbsp;<b>New Password:<br>(re-type):</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>">
	      &nbsp;&nbsp;<input type="password" name="frm_RePassword" size="25" maxlength=15 value="">
        </td>
	    </tr>
      <tr bgcolor="">
	      <td width="" align="right" bgcolor="<?php echo $bgcolor;?>">
	        <div class=maintext><b>Enter the Code shown:</b></div> 
	      </td>
	      <td width="" bgcolor="<?php echo $bgcolor;?>">
	      &nbsp;&nbsp;<input type="text" name="frm_code" size="8" maxlength=8 value="">
        <img src="./confirmCode.php">
        </td>
	    </tr>
      <tr bgcolor="<?php echo $bgcolor;?>" align="center">
        <td colspan="2">
          <input type="button" value=" Change " class=green_but onClick="javascript: checkform();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <input type="button" value=" Close " class=green_but onClick="javascript: window.close();"><br>&nbsp;
        </td>
      </tr>
     </table>
    </td>	
  </tr>
</table>
</form>
</center>
</body>
</html>
