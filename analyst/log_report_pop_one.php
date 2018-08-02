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
include("admin_office/admin_log_class.php");
include("admin_office/common_functions.inc.php");

if($MyTable){
  if(!isset($category_lable_arr[$MyTable])){
    $category_lable_arr[$MyTable] = ret_search_lable($MyTable);
  }
}  

$category_arr['Bait'] = "Bait,BaitGroup";
  
$category_lable_arr['Bait'] = 'Bait';
$category_lable_arr['BaitGroup'] = 'Bait Notes';
//------------------------------------------------------------------------
$category_arr['Experiment'] = "Experiment,ExperimentGroup,Protocol";

$category_lable_arr['Experiment'] = 'Experiment';
$category_lable_arr['ExperimentGroup'] = 'Experiment Notes';
$category_lable_arr['Protocol'] = 'Experiment Protocol';

//------------------------------------------------------------------------
$category_arr['Band'] = "Plate,PlateWell,Gel,Lane,Band,BandGroup";

$category_lable_arr['Band'] = 'Sample';
$category_lable_arr['BandGroup'] = 'Sample Notes';

$category_lable_arr['Plate'] = 'Plate';
$category_lable_arr['PlateWell'] = 'Plate Well';

$category_lable_arr['Gel'] = 'Gel';
$category_lable_arr['Lane'] = 'Lane';
//------------------------------------------------------------------------


$category_arr['Hit'] = "HitDiscussion,HitNote";

$category_lable_arr['HitDiscussion'] = 'Hit Notes';
$category_lable_arr['HitNote'] = 'Hit Experiment Filters'; 
//------------------------------------------------------------------------
$category_arr['Group Lists'] = "NoteType";

$category_lable_arr['NoteType'] = 'Group Lists';
//------------------------------------------------------------------------
$category_arr['Search Results'] = "UploadSearchResults";

$category_lable_arr['UploadSearchResults'] = 'Uploaded Search Results';
//------------------------------------------------------------------------
$category_arr['Coip'] = "Coip";

$category_lable_arr['Coip'] = 'Coip';
  //------------------------------------------------------------------------

$SQL = "SELECT
        UserID, 
        MyTable, 
        RecordID,
        MyAction,
        Description,
        TS FROM Log
        WHERE MyTable='$MyTable' AND RecordID=$RecordID AND UserID='$frm_User'";  
  if($order_by){
    $SQL .= " ORDER BY $order_by ";
  }   
  //echo $SQL;exit;
  $result = mysqli_query($HITSDB->link, $SQL);
  $UserNameArr = get_users_ID_Name($HITSDB);
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
  <input type='hidden' name='frm_User' value="<?php echo $frm_User?>">
  <table border="0" cellpadding="0" cellspacing="1" width="900">
  <tr>
    <td colspan=9 height=25>
      <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="3"><b>Project<?php echo  $AccessProjectID;?>:</b></font>&nbsp;
      <font color="red" face="helvetica,arial,futura" size="3"><b>
        <?php echo $AccessProjectName;?></b></font>&nbsp;&nbsp;&nbsp;&nbsp;      
    </td>
  </tr>
  <tr>
    <td colspan=9 height=25>
      <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="3">Table:</font>&nbsp;
      <font color="red" face="helvetica,arial,futura" size="3">
        <?php echo $category_lable_arr[$MyTable];?></font>&nbsp;&nbsp;&nbsp;&nbsp;      
    </td>
  </tr>
  <tr>
    <td colspan=9 height=25>
      <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="3">Owner:</font>&nbsp;
      <font color="red" face="helvetica,arial,futura" size="3">
        <?php echo (isset($UserNameArr[$frm_User]))?$UserNameArr[$frm_User]:"";?></font>&nbsp;&nbsp;&nbsp;&nbsp;      
    </td>
  </tr>
  
  <tr bgcolor="">
    <td width="10%" height="25" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center><div class=tableheader>
    Record ID
    </div>
    </td>
    <td width="10%" bgcolor="<?php echo $TB_HD_COLOR;?>" align=center> <div class=tableheader>
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
  <tr bgcolor="<?php echo $TB_CELL_COLOR;?>"  height=20>
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
<?php 
function ret_search_lable($MyTable){
  $lable = '';
  $pattern = '/^(\w+)(Search|tpp)(Results)$/';
  if(preg_match($pattern, $MyTable, $matches)){
    $lable = $matches[1].' '.$matches[2].' '.$matches[3];
  }  
  return $lable;
}    
?>