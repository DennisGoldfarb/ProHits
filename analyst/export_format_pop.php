<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>
<script language="Javascript" src="site_no_right_click.inc.js"></script>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>
<script language="JavaScript">

</script>
</head>
<body>

<table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%>    
  <tr>
    <td valign=top align=center bgcolor="white" width=100%>
    <table border=0 width=95% cellspacing="0" cellpadding=0>
      <tr>
        <td colspan='2' nowrap >&nbsp;&nbsp;</td>
      </tr>
      <tr>
        <td nowrap align="<?php echo $tmp_align?>" height='25'>
          <span class=pop_header_text>Pre-defined export format set</span>  <font size='3'>(<?php echo $AccessProjectName;?>)</font>
        </td>
      </tr>
      <tr>
        <td nowrap align=center height='1'><hr size=1></td>
      </tr>
      <tr>
        <td align=center>
        <?php if($public == 'IntAct'){
        echo "<b><font size='+2'>$public</font></b> &nbsp; &nbsp; <img src='./images/intact-logo.png' alt='' border='0' align=middle>";
        }
        ?>
        </td>
      </tr>
    </table>
    </td> 
  </tr> 
</table>
</body>
</html>
