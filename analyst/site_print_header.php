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
//print_r($request_arr); 
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
  <title>ProHits</title>
  <link rel="stylesheet" type="text/css" href="./site_style.css"> 
  <script language="Javascript" src="site_javascript.js"></script>
</head>
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight="0" marginwidth="0">
  <center>
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td bgcolor="#004080"><!--img src="images/logo.gif" width="360" height="60" border="0"-->
       &nbsp; &nbsp; &nbsp; &nbsp;<font face="Arial" size="7" color="#FFFFFF"><b>ProHits</b></font>
      <font face="Arial" size="2" color="#FFFFFF"><b>Protein High-throughput Solution</b></font>
      </td>
    </tr>
  </table>
