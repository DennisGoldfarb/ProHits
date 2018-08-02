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

//require("../common/site_permission.inc.php");
require("../config/conf.inc.php");
include("../common/mysqlDB_class.php");

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;  
}

$db = new mysqlDB();
$SQL = "SELECT URL, ProteinTag FROM WebLink WHERE ID=$linkID";
$SetUrlLinksArr=$db->fetch($SQL);
$linkURL = $SetUrlLinksArr['URL'];
$arguType = $SetUrlLinksArr['ProteinTag'];
$arguType = substr($arguType, 3);

?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
<script language="javascript">
function pop_linkWindow(){
  var val = document.linkURL_form.frm_queryString.value;
  var link_url = '<?php echo $linkURL?>' + val;
  window.open(link_url,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=yes, copyhistory=yes, width=1000, height=800")
}
</script>
<script language="Javascript" src="site_javascript.js"></script>
</head>
<body bgcolor=#ffffff>

<form name=linkURL_form method=post action="/prohits_new/pop_bait_note.php"> 
<table border="0" cellpadding="1" cellspacing="1" width="100%">  
  <tr bgcolor="">
	  <td align="center" colspan="2" bgcolor="#0080c0"><span class=maintext>
		<font face="Arial" size="3" color="#FFFFFF"><span class=maintext>&nbsp;<b>URL Test</b></font>
    </td>
  </tr>
  <tr>
    <td bgcolor="#e1e1e1" width="20%"><span class=maintext><b>URL:</b></span></td>
    <td bgcolor="#e1e1e1"><span class=maintext><?php echo $linkURL?></span></td>   
  </tr>
  <tr>
    <td bgcolor="#e1e1e1"><span class=maintext><b>Type <?php echo $arguType?>:</b></span></td>
    <td bgcolor="#e1e1e1">
      <input type="text" name="frm_queryString" size="60" value="">
    </td>   
  </tr>  
  <tr>
    <td bgcolor="#e1e1e1" colspan=4 align='center'>
      <input type=button value='Submit' onClick='return pop_linkWindow()'; class=black_but>
      <input type=button value=' Close ' onClick='javascript: window.close();' class=black_but>
    </td> 
  </tr>
</table>
<br> 
</form>
</body>
</html>

