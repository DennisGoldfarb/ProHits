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

$theaction = '';
$frm_name = '';
$msg = '';
require("../common/site_permission.inc.php");
$prohitsDB = new mysqlDB(PROHITS_DB);
if($theaction == 'add'){
  if(!trim($frm_name)){
    $msg = "Please type a lab name";
  }else{
    $tmp_name = mysqli_escape_string($prohitsDB->link, $frm_name);
    $SQL = "select ID from Lab where Name='$tmp_name'";
    if(!$prohitsDB->fetch($SQL)){
      $SQL = "insert into Lab set Name='$tmp_name'";
      $prohitsDB->insert($SQL);
    }else{
      $msg = "'$frm_name' has been added already.";
    }
  }
} 

$SQL = "select ID, Name from Lab";
$labs = $prohitsDB->fetchAll($SQL);


?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
</head>

<body>
<br>
<center>
<?php echo "<font color=red><b>$msg</b></font>";?>
<form name="Backup_Setup" method=post action=<?php echo $_SERVER['PHP_SELF'];?> enctype="multipart/form-data">
<input type=hidden name=theaction value="add">
<table border="0" cellpadding="0" cellspacing="1" width="90%">
  <tr bgcolor=#4169e1 height=30>
    <td align="center"><div class=tableheader><b>Lab ID</b></div></td>
    <td align="center"><div class=tableheader><b>Lab Name</b></div></td>    
  </tr>
  <?php 
  foreach($labs as $lab){?>
  <tr bgcolor="#b0c4de">
    <td align="center"><div class=maintext><?php echo $lab['ID'];?></div></td>
    <td align="center"><div class=maintext><?php echo $lab['Name'];?></div></td>    
  </tr>
  <?php }?>
  <tr bgcolor="#b0c4de">
    <td align="center">&nbsp;</td>
    <td align="center"><input name=frm_name size=30>&nbsp;
    <input type='submit' value='Add' onClick="addnew(this.form)">
    </td>    
  </tr>
  
  </td>
</tr>        
</table>
</form>
</body></html> 