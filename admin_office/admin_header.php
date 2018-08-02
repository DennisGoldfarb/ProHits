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

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;  
} 
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
  <link rel="stylesheet" type="text/css" href="./site_style.css"> 
  <title>Prohits</title>
  <script language="Javascript" src="../analyst/site_javascript.js"></script>
</head>
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr bgcolor=#054378>
    <td>
     <IMG SRC="./images/site_head3_01.gif" HEIGHT=60></td>
    <td align="right" background=./images/site_head3_02.gif width=1000>
    <IMG SRC="./images/site_head3_02.gif" WIDTH=71 HEIGHT=60></td>
    <td align="right">
      <IMG SRC="./images/site_head3_04.gif" WIDTH=284 HEIGHT=60></td>
  </tr>
  <tr height="1">
    <td bgcolor="pink" colspan="3" height="1">
       <img src="./images/pixel.gif" width="1" height="1" border="0"></td>
  </tr>
  <tr>
    <td colspan="3">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td bgcolor="black"><img src="./images/shim.gif" width="2" height="2" border="0"></td>
          <td align="right" bgcolor="black">
            <b>&nbsp;&nbsp; </font></b>
            <img src="./images/shim.gif" width="25" height="1" border="0"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr height="1">
    <td bgcolor="white" colspan="3" ><img src="./images/pixel.gif" width="1" height="1" border="0"></td>
  </tr>
</table>
 <?php if(isset($add_process_img)){?>
  <div style='display:block;text-align:center;}' id='process'><b>check ftp connections</b>: <img src='../analyst/images/processing.gif' border=0 valign=middle></div>
  <?php 
    @ob_flush();
    flush();
  }?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="421">
	<tr height="470">
	  <td bgcolor="#d0e4f8" valign="top" align="left" height="470" width=165>
	    <img src="./images/manue.gif" width="165" border="0">
			<br><br>
	    <?php  require("admin_left_menu.inc.php"); ?>
	  </td>
	  <td width="1" bgcolor="black" valign="top" height="470">
	  <img src="./images/pixel.gif" width="1" height="1" border="0"></td>
	  <td width="10000" valign="top" align="center" height="470"><center>