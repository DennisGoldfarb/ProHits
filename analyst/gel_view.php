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


require("../common/site_permission.inc.php");
require("analyst/classes/gel_class.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");

$Gel_ID = '';

$Band_ID = 0;
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;  
} 

$SCRIPT_NAME = str_replace("/","",substr($SCRIPT_NAME,strrpos($SCRIPT_NAME, "/") ,strlen($SCRIPT_NAME)));
$Gel = new Gel($Gel_ID);
if($Band_ID){
  $Gel->get_gel_id($Band_ID);
  $Gel->fetch($Gel->ID);
}

$Gel_Owner = get_userName($mainDB, $Gel->OwnerID);  
$bgcolor = "#e9e1c9";
$bgcolordark = "#a58a5a";
$imageLocation = "./gel_images/";
?>
<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<title>Gel Image</title>
	<link rel="stylesheet" type="text/css" href="./site_style.css">
 <script language="Javascript" src="site_javascript.js"></script>
</head>
<BODY>
<center>
<script language="javascript">
function printit(){  
	if (window.print) {
	    window.print() ;  
	} else {
	    var WebBrowser = '<OBJECT ID="WebBrowser1" WIDTH=0 HEIGHT=0 CLASSID="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></OBJECT>';
	    document.body.insertAdjacentHTML('beforeEnd', WebBrowser);
	    WebBrowser1.ExecWB(6, 2);//Use a 1 vs. a 2 for a prompting dialog box    WebBrowser1.outerHTML = "";  
	}
}
</script>
<table border="0" cellpadding="0" cellspacing="0" width="97%">
  <tr>
    <td align="left" bgcolor=<?php echo $bgcolordark;?>>
		&nbsp; <font  face="helvetica,arial,futura" size="3"><b>Gel Information</b> 
		</font> 
	</td>
	<td align=right bgcolor=<?php echo $bgcolordark;?>> 
	  <a href="javascript: printit();" class=button><font color=white>[ Print ]</font></a>
	 <a href="javascript: window.close();" class=button><font color=white>[ Close the Window ]</font></a>&nbsp; &nbsp; 
	</td>
  </tr>
  <tr>
  	<td   colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align="center" colspan=2>
    <table border="0" cellpadding="0" cellspacing="1" width="100%">
	  <tr bgcolor="">
		  <td width="15%" bgcolor="<?php echo $bgcolor;?>">&nbsp;
		    <font face="helvetica,arial,futura" size="2"><b>Name:</b></font> 
		  </td>
		  <td width="15%" bgcolor="<?php echo $bgcolor;?>">&nbsp;
		    <font  face="helvetica,arial,futura" size="2"><?php echo $Gel->Name;?></font>
		  </td>
		  <td rowspan=3 width="70%" bgcolor="<?php echo $bgcolor;?>" valign=top>&nbsp;
		    <font   face="helvetica,arial,futura" size="2"><b>Notes:</b> <br>&nbsp;&nbsp;
			<?php 
			$tmpStr = nl2br(htmlspecialchars($Gel->Notes));
			echo $tmpStr;
      if($Band_ID){ //refer from bait_report.php
        echo "<b><font color=red>Band Location: ". $Gel->Band_Location . "</font><b>";
      }
      ?></font>
      
		  </td>
	  </tr>
	  <tr>
		  <td width="" bgcolor="<?php echo $bgcolor;?>" nowrap>&nbsp;
		    <font  face="helvetica,arial,futura" size="2"><b>Method of Staining:</b></font> 
		  </td>
		  <td width="" bgcolor="<?php echo $bgcolor;?>">&nbsp;
		    <font  face="helvetica,arial,futura" size="2"><?php echo $Gel->Stain;?></font> 
		  </td>
	  </tr>
		  <td width="" bgcolor="<?php echo $bgcolor;?>" >&nbsp;
		    <font  face="helvetica,arial,futura" size="2"><b>Submitted by:</b>:</font>
		  </td>
		  <td width="" bgcolor="<?php echo $bgcolor;?>">&nbsp;
		    <font  face="helvetica,arial,futura" size="2"><?php echo $Gel_Owner;?></font>
		  </td>
	   </tr>
     </table>
    </td>
	<tr>
	 <td align=center bgcolor="<?php echo $bgcolor;?>" colspan=2>
	    <img src="<?php echo $imageLocation . $Gel->Image;?>" border=0>      
	 </td>
	</tr>
  </tr>
</table>
</center>
</body>
</html>
