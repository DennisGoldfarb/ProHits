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

$bgcolor = "#e9e1c9";
$bgcolordark = "#a58a5a";
$imageLocation = "./western_images/";

if(!$WesternGel) exit;
$pattern = '/^([a-zA-Z0-9]+)exp(\d+)_(.+)/';
preg_match($pattern, $WesternGel, $matches);
$peID = $matches[2];
$name = $matches[3];

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
  		&nbsp; <font  face="helvetica,arial,futura" size="3"><b>Image Information</b> 
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
  		    <font face="helvetica,arial,futura" size="2"><b>Exp ID:</b></font> 
  		  </td>
  		  <td width="" bgcolor="<?php echo $bgcolor;?>">&nbsp;
  		    <font  face="helvetica,arial,futura" size="2"><?php echo $peID;?></font>
  		  </td>
      </tr>  
  		<tr bgcolor="">  
  		  <td width="" bgcolor="<?php echo $bgcolor;?>" nowrap>&nbsp;
  		    <font  face="helvetica,arial,futura" size="2"><b>Batch Code:</b></font> 
  		  </td>
  		  <td width="" bgcolor="<?php echo $bgcolor;?>">&nbsp;
  		    <font  face="helvetica,arial,futura" size="2"><?php echo $BatchCode;?></font> 
  		  </td>
  	  </tr>	
      <tr bgcolor="">  
        <td width="" bgcolor="<?php echo $bgcolor;?>" nowrap>&nbsp;
          <font  face="helvetica,arial,futura" size="2"><b>Image Name:</b></font> 
        </td>
        <td width="" bgcolor="<?php echo $bgcolor;?>">&nbsp;
          <font  face="helvetica,arial,futura" size="2"><?php echo $name;?></font> 
        </td>
      </tr>		    	  
    	<tr>
    	 <td align=center bgcolor="<?php echo $bgcolor;?>" colspan=2>
    	    <img src="<?php echo $imageLocation . $WesternGel;?>" border=0>      
    	 </td>
    	</tr>
    </table>
    </td>
</table>
</center>
</body>
</html>

