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

$frm_UserIDs = '';
$frm_UserSelect = 'default';
$frm_Subject = '';
$frm_UserStr = '';
$frm_Content = '';
$readonly = '';
$successMessage = '';
$failedMessage = '';
$sendto = '';
$frm_From = '';

require_once("../common/site_permission.inc.php");
require_once("../common/common_fun.inc.php");
require_once ('../common/PHPMailer-master/PHPMailerAutoload.php');
include("admin_log_class.php");
//include("./admin_header.php");
$bgcolordark = "#466cc6";
?>
<script language="javascript">
function isEmptyStr(str){
  var str = this != window? this : str;
  var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
  if(temstr == 0 || temstr == ''){
     return true;
  } else {
    return false;
  }
}
function select_users(){
  var theForm = document.email_form;
  var UserSelected = '';
  for(var i=0; i<theForm.frm_UserSelect.length; i++){
    if(theForm.frm_UserSelect[i].checked == true){
      UserSelected = theForm.frm_UserSelect[i].value;
    }
  }
  if(UserSelected == 'selectedUsers'){
    var UserIDs = theForm.frm_UserIDs.value;
    if(!UserIDs){
      theForm.frm_UserStr.value = '';;
    }
    var file = 'pop_mail_users.php?frm_selected_user_IDs='+UserIDs;
    newwin = window.open(file,"",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=730,height=550');
    newwin.moveTo(1,1);
  }else{
    theForm.submit();
  }   
}
function confirm(){
  var theForm = document.email_form;
  if(isEmptyStr(theForm.frm_Subject.value) || isEmptyStr(theForm.frm_Content.value)){
    alert("need Subject or Content to send a email!")
  }else{
    theForm.theaction.value = "confirm";
    theForm.submit();
  }  
}
function sentform(){  
  var theForm = document.email_form;
  theForm.theaction.value = "sent";
  theForm.submit();
}
function backfill(){
  var theForm = document.email_form;
  theForm.theaction.value = "backfill";
  theForm.submit();
}
function to_fillform(){
  var theForm = document.email_form;
  theForm.theaction.value = "fillform"; 
  theForm.frm_Subject.value = '';   
  theForm.frm_Content.value = ''; 
  theForm.submit();
}
</script>
<?php 


if($theaction == "sent"){
  $AdminLog = new AdminLog();
  
  $addressArr = explode(";", $frm_UserStr);
  $sentStr = '';
  $failedStr = '';
  foreach($addressArr as $value){
    $e_to = trim($value);
    $e_msg = $frm_Content;
    $e_subject = $frm_Subject;
    $e_from = $frm_From;
    $e_replay = $frm_From;
    //$ret = send_mail($e_to, $e_msg, $e_subject, $e_from, $e_replay);
    $err = prohits_gmail($e_to, $e_from, $e_subject, $e_msg);
    sleep(1);
    if(!$err){
      if($sentStr){
        $sentStr .= '; ';
      }
      $sentStr .= $e_to;
    }else{
      if($failedStr){
        $failedStr .= '; ';
      }
      $failedStr .= $err.$e_to;
    }
  }  
  $successMessage = $sentStr;
  $failedMessage =  $failedStr;
  $theaction = "summary";
  $Desc = "FROM=" . $e_from . "\n SEND_TO=" . $sentStr . "\n SUBJECT=" .$e_subject. "\n MESSAGE=" . $e_msg;
  $AdminLog->insert($AccessUserID,'Email','','sentMail', mysqli_real_escape_string($AdminLog->link, $Desc));
}

if($theaction == 'fillform' || $theaction == 'backfill'){
  if($theaction == 'fillform'){
    if($frm_UserSelect == 'default'){
      if(!$sendto){  
        $frm_UserStr = $_SESSION["USER"]->Email;
      }else{
        $frm_UserStr = $sendto;
        if($UserID){
          //print_r($_SERVER);exit;
          $theUser = new User('', $UserID);
          $frm_Content = "Hi " . $theUser->Fname;
          $frm_Content .= ",\nYour Prohits account as follows:
          User name: ".$theUser->Username."
          Password: ". $theUser->Password."
          User Type: ". $theUser->Type."
          Prohits URL: http://". $_SERVER["SERVER_NAME"].dirname(dirname($_SERVER["PHP_SELF"]))."\n\n".$_SESSION["USER"]->Fname;
        }
      }
    }else if($frm_UserSelect == 'allUsers'){
      $frm_UserStr = '';
      $SQL="SELECT Email FROM User where Active='1' ORDER BY LastLogin desc";
      $EmailArr = $mainDB->fetchAll($SQL);
      for($i=0; $i<count($EmailArr); $i++){
        if($frm_UserStr){
          $frm_UserStr  .= '; ';
        }
        if(!strstr($EmailArr[$i]['Email'], "@")){
           $EmailArr[$i]['Email'] .= "@mshri.on.ca";
        }
        $frm_UserStr .= $EmailArr[$i]['Email'];
      }
    }
    //$frm_From = $_SESSION["USER"]->Email;
    $frm_From = ADMIN_EMAIL;
  }else{
    $theaction = 'fillform';
  }
}

$error = check_gmail();
?>
<br>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td align="middle" valign="top" colspan="1">
        <?php echo "<font color=red>Error: $error</font>";?>
        <table border="0" cellpadding="1" cellspacing="1" width="800"> 
          <tr>
            <td colspan="1" height=20>
              <font color="<?php echo $bgcolordark?>" face="helvetica,arial,futura" size="3"><b>Email</b></font>
            </td>
            <td width="" align="right" height=20>&nbsp;&nbsp;</td>            
          </tr>  
          <form name=email_form method=post action=<?php echo $PHP_SELF;?> >
          <input type="hidden" name="frm_UserIDs" value="">
          <input type="hidden" name="theaction" value="<?php echo $theaction?>">
      <?php 
        if($theaction == "confirm" || $theaction == "summary"){
      ?> 
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="10%" align="left" height=20><div class=maintext>
              &nbsp;&nbsp;<b>From:</b>&nbsp; </div>
            </td>
            <td width="" align="left" height=20><div class=maintext>
              <?php echo $frm_From?>
              <input type="hidden"name="frm_From" value="<?php echo $frm_From?>"></div>
            </td>  
          </tr>  
       <?php }else{?> 
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="" align="left" height=20><div class=maintext>
              &nbsp;&nbsp;<b>From:</b>&nbsp;</div>
            </td>
            <td width="" align="left" height=20><div class=maintext>
              <input type="text" size="115" name="frm_From" value="<?php echo $frm_From?>"></div>
            </td> 
          </tr>    
      <?php }
        if($theaction == "confirm" || $theaction == "summary"){
      ?> 
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="10%" align="left" height=20><div class=maintext>
              &nbsp;&nbsp;<b>Subject:</b>&nbsp; </div>
            </td>
            <td width="" align="left" height=20><div class=maintext>
              <?php echo $frm_Subject?>
              <input type="hidden"name="frm_Subject" value="<?php echo $frm_Subject?>"></div>
            </td>  
          </tr>  
       <?php }else{?> 
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="" align="left" height=20><div class=maintext>
              &nbsp;&nbsp;<b>Subject:&nbsp;</div>
            </td>
            <td width="" align="left" height=20><div class=maintext>
              <input type="text" size="115" name="frm_Subject" value="<?php echo $frm_Subject?>"></div>
            </td> 
          </tr>
       <?php }
         if($theaction == "confirm"){
       ?>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="10%" align="left" height=20><div class=maintext>
              &nbsp;&nbsp;<b>To:</b></div>
            </td>
            <td width="" align="left" height=20><div class=maintext>
              <?php echo $frm_UserStr?>
              <input type="hidden" name="frm_UserStr" value='<?php echo $frm_UserStr?>'></div>
            </td>
          </tr>
        <?php }else if($theaction == 'fillform'){?>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="" align="left" valign=top height=20><div class=maintext>
              &nbsp;&nbsp;<b>To:</b></div>
            </td>
            <td width="" align="left" valign=top height=20><div class=maintext>
              <textarea cols="88" rows="15" name="frm_UserStr"><?php echo $frm_UserStr?></textarea></div>
            </td>
          </tr>
        <?php }
          if($theaction == 'fillform'){
        ?>       
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="" align="left" colspan="2" height=20><div class=maintext>
              &nbsp;&nbsp;<b>All Users <input type="radio" name="frm_UserSelect" value="allUsers" <?php echo ($frm_UserSelect=='allUsers')?'checked':''?>
              onClick="javascript: select_users();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Select Users 
              <input type="radio" name="frm_UserSelect" value="selectedUsers" <?php echo ($frm_UserSelect=='selectedUsers')?'checked':''?> 
              onClick="javascript: select_users();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Default
              <input type="radio" name="frm_UserSelect" value="default" <?php echo ($frm_UserSelect=='default')?'checked':''?>
              onClick="javascript: select_users();"></div>
            </td>
          </tr>
        <?php }else if($theaction == 'confirm'){?> 
              <input type="hidden" name="frm_UserSelect" value='<?php echo $frm_UserSelect?>'>
        <?php }?>       
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>"><div class=maintext>            
            <td width="" align="left" colspan="2"><span></span>
              <textarea cols="97" rows="12" name="frm_Content" <?php echo ($theaction != "fillform")?"readonly":"";?>><?php echo $frm_Content?></textarea></span></div>
            </td>
          </tr>
        <?php 
          if($theaction == "summary"){  
            if($successMessage){
        ?>  
            <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
              <td width="" align="left" height=20 colspan="2"><div class=maintext>
                &nbsp;&nbsp;<font color='red' face='helvetica,arial,futura' size='2'>The above message has been sent to:</b><br></div> 
              </td>
            </tr>
            <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
              <td width="" align="center" height=20 colspan="2"><div class=maintext> 
                <table border="0" cellpadding="1" cellspacing="1" width="95%">
                  <tr>
                    <td>
                      <div class=maintext><?php echo $successMessage;?></div>
                    </td>
                  </tr>
                </table>  
              </td>
            </tr>
        <?php 
            }
            if($failedMessage){
        ?>   
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="" align="left" height=20 colspan="2"><div class=maintext>
              &nbsp;&nbsp;<font color='red' face='helvetica,arial,futura' size='2'>The above message hse been failed sent to:</b><br></div> 
            </td>
          </tr>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">            
            <td width="" align="center" height=20 colspan="2"><div class=maintext> 
              <table border="0" cellpadding="1" cellspacing="1" width="95%">
                <tr>
                  <td>
                    <div class=maintext><?php echo $failedMessage;?></div>
                  </td>
                </tr>
              </table>  
            </td>
          </tr>
        <?php 
            }
          }
          if($theaction == "confirm"){
        ?> 
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>"><div class=maintext>            
            <td width="" align="center" colspan="2">
              <input type='button' name='sentForm' value=' confirm ' onClick="javascript: sentform();">
              <input type='button' name='sentForm' value=' Back ' onClick="javascript: backfill();"></div>
            </td>
          </tr>  
        <?php }else if($theaction == 'fillform'){?>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>"><div class=maintext>            
            <td width="" align="center" colspan="2">
              <input type='button' name='frm_Conferm' value=' Sent Email ' onClick="javascript: confirm();">
              <input type='button' name='frm_Close' value=' Close ' onClick="javascript: window.close();">
              </div>
            </td>
          </tr>
        <?php }else if($theaction == "summary"){?>
          <tr bgcolor="<?php echo $TB_CELL_COLOR;?>"><div class=maintext>            
            <td width="" align="center" colspan="2">
              <input type='button' name='frm_ToFillForm' value=' To Edit Form ' onClick="javascript: to_fillform();">
              <input type='button' name='frm_Close' value=' Close ' onClick="javascript: window.close();">
              </div>
            </td>
          </tr>
        <?php }?>               
        </table><br>       
      </td>
  </tr>
</table>
<?php 
//******************************************************************
function send_mail($to, $msg,  $subject='', $from='', $replayTo=''){
//******************************************************************
  if(!$to or !$msg){
    echo 'need $to or $msg to send a email!';
    exit;
  }
  return mail($to, $subject, $msg, "From: $from\r\n"."Reply-To: $replayTo\r\n");
}
//********************************************************************
//include("./admin_footer.php");
?>
