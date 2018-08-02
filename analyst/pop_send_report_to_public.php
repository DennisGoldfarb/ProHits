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

$uploaded_location = '';

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");
require_once ('common/PHPMailer-master/PHPMailerAutoload.php');
@require_once("common/HTTP/Request_Prohits.php");
ini_set("memory_limit","2000M");

$bgcolor = '#f1f1f8';
$Use_name = $_SESSION['USER']->Username;
$Project_ID = $_SESSION['AUTH']->ProjPageID;
$session_id = session_id();
$random_chars = random_chars($chars = 2);

$timestamp = time();

if($html_report_url){
  $tmp_url_name_arr = explode('.',$html_report_url);
  $ext = end($tmp_url_name_arr);
}else{
  exit;
}
$err_msg = '';


$real_file_name = $Use_name."_".$Project_ID."_".$session_id."_".$random_chars."_".$timestamp.".".$ext;

if(defined("PROHITS_PUBLIC_REPORT_CGI") and !$uploaded_location){
  //exit;
  $public_url = PROHITS_PUBLIC_REPORT_CGI;
  $req = new HTTP_Request($public_url, array('timeout' => 18000,'readTimeout' => array(18000,0)));
  $req->setMethod(HTTP_REQUEST_METHOD_POST);
  $req->addHeader('Content-Type', 'multipart/form-data');
  
  $req->addPostData('upalode_from_prohits', "1");
  $req->addPostData('real_file_name', $real_file_name);
  $req->addFile("uploaded_file", $html_report_url, $contentType = 'application/octet-stream');
  
  $result = $req->sendRequest();
  if(!PEAR::isError($result)) {
    $response1 = $req->getResponseBody();
    if($response1 !== false) {
      if(preg_match('/UPLOADED_LOCATION:(.+\.html)/', $response1, $matchs)){
        $uploaded_location = $matchs[1];
      }
    }
  }
}
if($theaction == 'sent_report'){
  $error = check_gmail();
  if($error){
    echo "<font color=red>Error: $error</font>";exit;
  }
  $from = $_SESSION['USER']->Email;
  $to      = $frm_sent_to;
  $subject = $frm_subject;
  $message = ''; 
  $err = '';
  if(defined("PROHITS_PUBLIC_REPORT_CGI") and $uploaded_location){
    $message = $uploaded_location."\r\n\r\n";
    $message .= $frm_detail."\r\n";
    $err = prohits_gmail($to, $from, $subject, $message);
    //$sta = prohits_mail($to, $subject, $message, $from); 
  }else{
    $message = "Please open the attached file from web browser.\r\n\r\n";
    $message .= $frm_detail;
    $err = prohits_gmail($to, $from, $subject, $message, 0, array($html_report_url));
    //$sta = prohits_mail($to, $subject, $message, $from, array($html_report_url));
    //echo "html file attachment has been sent.";
  }
 
  if(!$err){
    write_to_log($to, $from, $subject, $uploaded_location, "sent mail");
    echo '<script language="Javascript">
      alert("email has been sent.");
      window.close();
    </script>';
    exit;
  }else{
    $err_msg = "An error was detected. Email didn't send out. \n$err.";
    write_to_log($to, $from, $subject, $uploaded_location, $err_msg);
    echo "<font color=red><pre>$err_msg</pre></font>";
  }
  exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Prohits</title>
  <link rel="stylesheet" type="text/css" href="./site_style.css">
  <STYLE type="text/css">
    TD{font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
  </STYLE>
  <!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
  <script language="Javascript" src="../common/javascript/site_javascript.js"></script>
  <script language="JavaScript" type="text/javascript">
  function sent_report(theForm){
    if(isEmptyStr(theForm.frm_sent_to.value)){
      alert("Please enter email address");
      return false;
    }
    theForm.submit();
    //window.close();
  }
  </script>
</head>
<body>
<FORM NAME='sent_report_frm' ACTION='<?php echo $_SERVER['PHP_SELF'];?>' METHOD='POST'>
<input type='hidden' name='theaction' value="sent_report">
<input type='hidden' name='html_report_url' value="<?php echo $html_report_url?>">
<input type='hidden' name='uploaded_location' value="<?php echo $uploaded_location?>">
<?php 
echo $err_msg;
?><br>
<table cellspacing='1' cellpadding='1' border='0' align=center width='99%'>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" width='20%' nowrap>
	    <div class=maintext>Sent link:&nbsp;&nbsp;</div>
	  </td>
    <td align="left">
      <div class=maintext><?php echo $real_file_name?></div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" width='20%' nowrap>
	    <div class=maintext>To:&nbsp;&nbsp;</div>
	  </td>
    <td align="left">
      <div class=maintext><input type="text" name="frm_sent_to" size="72" value=""></div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td align="right" width='20%' nowrap>
	    <div class=maintext>Subject:&nbsp;&nbsp;</div>
	  </td>
    <td align="left">
      <div class=maintext><input type="text" name="frm_subject" size="72" value=""></div>
    </td>
  </tr>
  <tr bgcolor="<?php echo $bgcolor;?>">
	  <td align="right" width='20%' nowrap>
	    <div class=maintext>&nbsp;&nbsp;Contents:&nbsp;&nbsp;</div>
	  </td>
	  <td align="left" bgcolor="<?php echo $bgcolor;?>">
        <div class=maintext><textarea name=frm_detail cols=70 rows=6></textarea></div>
    </td>
	</tr>
  <tr bgcolor="<?php echo $bgcolor;?>">	  
	  <td valign=top colspan=2 align=center> 
        <input type="button" value="Send" onClick="javascript: sent_report(this.form);">&nbsp;
        <input type="reset" value="Reset">&nbsp;
        <input type="button" value="Close" onClick="javascript: window.close();">
	  </td>
	</tr>
  <tr bgcolor="#ffffff">	
    <td colspan=2><br>
    [<b>Send from email account of your choice</b>]<br><UL>
    <?php 
    
    echo "<Li>Option 1: Save the <a href='$html_report_url'>FILE</a> to your local computer by select \"SAVE LINK/TARGET AS\"(shown upon right click). Then attach the saved file to you email.";
    if($uploaded_location){
      echo "<Li>Option 2: Include the following URL link  in  your email:<br><font color=#008080>$uploaded_location</font>";
    }
    
    ?>
    </UL>
    </td>
  </tr>
</table>
</FORM>  
</body>
</html>
<?php 
function random_chars($chars = 2) {
  $letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
  return substr(str_shuffle($letters), 0, $chars);
}

function write_to_log($to, $from, $subject, $uploaded_location, $status){
  $log_file = "../logs/html_report.log";
  $log_handle = fopen($log_file, "a");
  fwrite($log_handle, "=====================================\r\n");
  fwrite($log_handle, @date("F j, Y, g:i a")."\r\n");
  fwrite($log_handle, "Sent to: $to\r\n");
  fwrite($log_handle, "Sent by: $from\r\n");
  fwrite($log_handle, "Subject: $subject\r\n");
  fwrite($log_handle, "URL: $uploaded_location\r\n");
  fwrite($log_handle, "Status: $status\r\n\r\n");
  fclose($log_handle);
}   
?>
