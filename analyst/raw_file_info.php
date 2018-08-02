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
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");

if(!$Band_ID){ 
  header ("Location: noaccess.html");
}
$bgcolordark = '#999900';
$bgcolor = '#e2e083';
$bgHitcolor="#e2e083";

$msManagerDB = new mysqlDB(MANAGER_DB);
?>
<html>
<head>
 <title>Prohits</title>
 <link rel="stylesheet" type="text/css" href="./site_style.css"> 
 <script language="Javascript" src="site_javascript.js"></script>
 <script language="Javascript" src="site_no_right_click.inc.js"></script>
 </head>
 <body>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
<tr bgcolor="">
	  <td width="30%" height="25" colspan=5>
     <font color='<?php echo $bgcolordark;?>' face='helvetica,arial,futura' size='3'><b>Raw File Information</b></font>
	  </td>
	</tr>  
	<tr bgcolor="">
	  <td width="30%" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
      <div class=tableheader>File Name</div>
	  </td>
    <td width="10%" bgcolor="<?php echo $bgcolordark;?>" align=center> 
	  <div class=tableheader>File Type</div>
	  </td>
    <td width="15%" bgcolor="<?php echo $bgcolordark;?>"align=center> 
	  <div class=tableheader>Instrument</div>
	  </td>
	  <td width="25%" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>Date</div> 
	  </td>
	  <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center> 
   <div class=tableheader>Size</div>
	  </td>
	</tr>
<?php 
$SQL = "SELECT
       `ID`,
       `RawFile` 
       FROM `Band` 
       WHERE `ID`='".$Band_ID."'";
if($BandArr = $HITSDB->fetch($SQL)){
  $rawFileArr = explode(";",$BandArr['RawFile']);
  foreach($rawFileArr as $value){
    $tmpArr = explode(":",$value);
    if(count($tmpArr) == 2){
      $SQL = "SELECT 
              `FileName` ,
              `FileType`,
              `Date`,
              `Size`
              FROM ".$tmpArr[0]." WHERE ID='".$tmpArr[1]."'";
        if($fileArr = $msManagerDB->fetch($SQL)){
          $dateTime = (preg_match('/^(\d{4}-\d{2}-\d{2})/', $fileArr['Date'], $matches))?$matches[1]:''; 
?>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td width="" ><div class=maintext>&nbsp;
	      <?php echo $fileArr['FileName'];?>&nbsp;
	    </div>
	  </td>
    <td width="" ><div class=maintext>&nbsp;
	      <?php echo $fileArr['FileType'];?>&nbsp;
	      </div>
	  </td>
    <td width=""><div class=maintext>&nbsp;
	      <?php echo $tmpArr[0] ;?>&nbsp;
	      </div>
	  </td>
	  <td width=""><div class=maintext>&nbsp;
	      <?php echo $dateTime;?>&nbsp;
	      </div>
	  </td>
	  <td width=""><div class=maintext>&nbsp;
	      <?php echo  $fileArr['Size'];?>&nbsp;
	      </div>
	  </td>
  </tr>
<?php 
      }
    }  
  }
}  
?>
</table><br>
<form>
<center>
<input type=button value=' Close ' onClick='javascript: window.close();' class=black_but>
</center>
</form>
 </body>
 </html>
