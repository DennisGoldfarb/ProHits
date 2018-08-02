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
require("analyst/classes/coip_wstimages_class.php");
$Coip_WSTimages = new Coip_WSTimages();
$Coip_WSTimages->fetchall();

?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
  <title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
<script language="Javascript" src="site_no_right_click.inc.js"></script>
<script language='javascript'>
function passvalue(selectedID, selectedImage){
	opener.document.forms[1].frm_ImageID.value = selectedID;
	opener.document.forms[1].frm_ImageName.value = selectedImage;
	window.close(); 
}
</script>
 
</head>
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight="0" marginwidth="0">
<form>
<table border="0" cellpadding="0" cellspacing="1" width="400">
	<tr bgcolor="#ffffff">
	  <td colspan="3" align="center" height=40>
	   <b>CO-IP Western Information</b>
	  </td>
	</tr>
	
	 <tr bgcolor="#a48b59">
	  <th  width="20%" align="center" height=30>
	   <div class=tableheader><b>ID</b>&nbsp;</div>
	  </td>
	  <th width="60%" height=30>
	   <div class=tableheader><b>Image</b>&nbsp;</div>
	  </td>
	  <th  width="20%" height=30>
	   <div class=tableheader><b>Option</b>&nbsp;</div>
	  </td>
	</tr>
	<?php for($i = 0; $i < $Coip_WSTimages->count; $i++){ ?>
	<tr bgcolor="#e9e1c9" height=19>
	  <td align="right" >
	   <div class=maintext><?php echo $Coip_WSTimages->ID[$i]?>&nbsp;</div>
	  </td>
	  <td align="right">
	   <div class=maintext><?php echo $Coip_WSTimages->Image[$i]?>&nbsp;</div>
	  </td>
	  <td align="center">
	   <div class=maintext><a class=button href="javascript: passvalue('<?php echo $Coip_WSTimages->ID[$i]."','".$Coip_WSTimages->Image[$i];?>');">Select</a>&nbsp;</div>
	  </td>
	</tr>
	<?php }?>
      
      </table>
</form>
</body>
</html>
