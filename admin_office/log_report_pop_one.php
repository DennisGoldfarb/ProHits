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

$order_by = '';

require("../common/site_permission.inc.php");
include("admin_log_class.php");
include("common_functions.inc.php");

$SQL = "SELECT DBname
        FROM Projects 
        WHERE ID=$Project";
$DBnameArr = $mainDB->fetch($SQL);
$oldDBName = change_DB($mainDB, $HITS_DB[$DBnameArr['DBname']]);  

$SQL = "SELECT
         UserID, 
         MyTable, 
         RecordID,
         MyAction,
         Description,
         TS FROM Log
         WHERE MyTable='$MyTable' AND RecordID=$RecordID AND UserID IN($UserIDstr)";  
  if($order_by){
    $SQL .= " ORDER BY $order_by ";
  }   
  //echo $SQL;exit;
  $result = mysqli_query($mainDB->link, $SQL);
  
  $UserNameArr = get_users_ID_Name($mainDB);
?> 
<html>
<head>
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
<SCRIPT language=JavaScript>
<!--
function sortList(order_by){
  var theForm = document.log_report;
  theForm.order_by.value = order_by;  
  theForm.submit();
}
-->
</SCRIPT>
</head>
<body bgcolor=#ffffff>
  <form name="log_report" method=post action=<?php echo $_SERVER['PHP_SELF'];?>>
  <input type='hidden' name='order_by' value=''>
  <input type='hidden' name='MyTable' value='<?php echo $MyTable?>'>
  <input type='hidden' name='RecordID' value='<?php echo $RecordID?>'>
  <input type='hidden' name='UserIDstr' value="<?php echo $UserIDstr?>">
  <table border="0" cellpadding="0" cellspacing="1" width="900">
  <tr bgcolor="">
    <td width="15%" height="25" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
    Owner</div>
    </td>    
    <td width="10%" height="25" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
    Table
    </div>
    </td>
    <td width="6%" height="25" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
    Record ID
    </div>
    </td>
    <td width="6%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center> <div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "MyAction")? 'MyAction%20desc':'MyAction';?>');">
      Action</a>
    <?php if($order_by == "MyAction") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "MyAction desc") echo "<img src='images/icon_order_down.gif'>";
    ?></div>
    </td>
    <td width="" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center> <div class=tableheader>
      Description
    </div>
    </td>
    <td width="13%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
      <a href="javascript: sortList('<?php echo ($order_by == "TS")? 'TS%20desc':'TS';?>');">
    Time </a>
    <?php if($order_by == "TS") echo "<img src='images/icon_order_up.gif'>";
      if($order_by == "TS desc") echo "<img src='images/icon_order_down.gif'>";
    ?></div>
    </td>   
  </tr>
<?php 
  while($row = mysqli_fetch_assoc($result)){
    
?>    
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>">
    <td width="" align="left" height="20"><div class=maintext>&nbsp; 
        <?php echo (isset($UserNameArr[$row['UserID']]))?$UserNameArr[$row['UserID']]:"";?>&nbsp;
      </div>
    </td>
    <td width="" align="left" height="20"><div class=maintext>&nbsp; 
        <?php echo $row['MyTable'];?>&nbsp;
      </div>
    </td>
    <td width="" align="left" ><div class=maintext>&nbsp; 
        <?php echo $row['RecordID'];?>&nbsp;
      </div>
    </td> 
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $row['MyAction'];?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $row['Description'];?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
        <?php echo $row['TS'];?>&nbsp;
      </div>
    </td>    
  </tr>
  <?php 
  } //end foreach
  ?>
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>" align="center">
	  <td colspan="6">		
		<input type="button" value=" Close " onclick="javascript: window.close();" class=black_but></td>
	</tr>
  </table> 
  </form>
</body>
</html>