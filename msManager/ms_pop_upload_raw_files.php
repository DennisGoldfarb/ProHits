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

$frm_old_folder_ID = '';
$frm_Project_ID = '';
$projectID = '';
$frm_new_folder_name = '';
$defualtProjectID = '1';
$theaction = '';

require("../common/site_permission.inc.php");
require("classes/Storage_class.php");
//echo $AccessUserID;
//$ftp_server = "192.197.250.94";
//$conn_id = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server"); 
//exit;
echo $USER->Type;exit;
$mainDB = new mysqlDB(PROHITS_DB);
$storageDB = new mysqlDB("Backup");

if($frm_old_folder_ID && $frm_old_folder_ID != "-1"){
  $tmpArr = explode('|', $frm_old_folder_ID);  
  $frm_old_folder_ID = trim($tmpArr[0]);
  if(isset($tmpArr[1]) && trim($tmpArr[1])){
    $projectID = $tmpArr[1];
  }else{
    $projectID = $defualtProjectID;
  }
}else if($frm_Project_ID && $frm_Project_ID != "-1"){
  $projectID = $frm_Project_ID;
}
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
  <link rel="stylesheet" type="text/css" href="./ms_style.css">
  <link rel="stylesheet" type="text/css" href="./site_style.css"> 
  <title>Upload Files</title>
</head>
<script language="javascript">
function select_folder(){
  theForm = document.raw_file_upload;
  if(theForm.whichFolder[0].checked){
    theForm.frm_new_folder_name.value = "";
    theForm.frm_new_folder_name.disabled = true;
    theForm.frm_Project_ID.value = "-1";
    theForm.frm_Project_ID.disabled = true;
    theForm.frm_old_folder_ID.disabled = false;
  }else{ 
    theForm.frm_new_folder_name.disabled = false;
    theForm.frm_Project_ID.disabled = false;
    theForm.frm_old_folder_ID.value = "-1";
    theForm.frm_old_folder_ID.disabled = true;
  }
}
function check_folder_select(){
  theForm = document.raw_file_upload;
  if(!theForm.whichFolder[0].checked && !theForm.whichFolder[1].checked){
    alert("Please select a radio button");
    return false;
  }
  if(theForm.whichFolder[0].checked){
    if(theForm.frm_old_folder_ID.selectedIndex == '-1'){
      alert("Please select a exist Folder");
      return false;
    }
  }else{
    if(!(/\d{8}_[A-Z]{3}\d+_\d+_P\d+/i.test(theForm.frm_new_folder_name.value))){
      alert("Please enter Folder Name like '20051203_YDP00123_234_P23'");
      return false;
    }
    if(theForm.frm_Project_ID.selectedIndex == '-1'){
      alert("Please select a Project");
      return false;
    }  
  }
  theForm.theaction.value = "uploadFiles";
  theForm.submit();
}
</script>
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5 topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">
<?php 
if($theaction == "uploadFiles"){
  /*$projectIDarr = array();
  $SQL = "SELECT ProjectID FROM ProPermission WHERE Modify='1' AND UserID='$AccessUserID'";
  $result = mysql_query($SQL, $mainDB->link);
  while($row = mysql_fetch_assoc($result)){
    array_push($projectIDarr, $row['ProjectID']);
  }  
  if(in_array($projectID, $projectIDarr)){*/
?> 
<form name="raw_file_upload" enctype='multipart/form-data' method=post action=<?php echo $PHP_SELF;?>>
  <input type=hidden name="theaction" value="">
  <input type=hidden name="tableName" value="<?php echo $tableName?>">
  <input type=hidden name="projectID" value="">
    <CENTER>
<table border="0" cellpadding="0" cellspacing="1" width="700">
  <tr bgcolor="#637eef" bgcolor="#d2dcff">
    <td colspan="1" align="center" height=25>
      <font color="white" face="helvetica,arial,futura" size="3"><b>Upload Files</b></font>
    </td>
  </tr> 
  <tr>
	  <td CLASS=sbottom2  HEIGHT='24' BGCOLOR='e9e9e9' colspan='6' Align='center'>
		<table cellpadding=0 cellspacing=0 width=90%>
			<tr>
				<td colspan=6 align='left' BGCOLOR='e9e9e9' ><Font size=2 >To add an attachment, type in a path or hit the Browse button. Then hit the Add Attachment Now button to add the attachment to the list below.</td>
			</tr>
			<tr>
				<td colspan=6 height=10 BGCOLOR='e9e9e9'>&nbsp;</td>
			</tr>
			<tr>
				<td align='center' colspan=6>
					<INPUT TYPE=FILE NAME=ffname SIZE=50>
				</td>
			</tr>
			<tr>
				<td colspan=6 height=10>&nbsp;</td>
			</tr>
			<tr>
				<td colspan=6 align='center'><INPUT TYPE=submit VALUE='Add Attachment Now' onclick='return add_attachment()'></td>
			</tr>
			<tr>
				<td colspan=6 align='center' BGCOLOR='e9e9e9'><br>
					<table bordercolor='e9e9e9' border=1 cellpadding=0 cellspacing=0 width=100%>
						<tr>
				  		 	<td align='left' valign='middle' width=10%>
				   			<a href='javascript:DeleteMarkedFiles();'>
				      			<img src='button_drop.gif' border=0 width=16 height=16 alt='Delete marked files'></a>
				   			</td>
				   			<td align='left' valign='middle'><nobr><Font size=2 >Click this icon to remove checked attached files</B></td>
				   	</tr>
						<tr>
							<td colspan=6 height=10>&nbsp;</td>
						</tr>
				   	<tr bgcolor='#a0a0a0'>
				  		<td align='center' valign='middle' width=10%>
				   				&nbsp;
				   		</td>
				   		<td align='center' valign='middle'><nobr><Font size=2 color='#ffffff'><nobr><B>File Name</B></font></td>
				   	</tr>
            <tr bgcolor='#FFFFFF'>
            	<td align=center CLASS=sbottom3>
            		<input type='checkbox' name='deletedFiles' value='yeast_complete.sql'  >
            	</td>
              	<td align='left' bgcolor='#FFFFFF' CLASS=sbottom3>
              yeast_complete.sql
              	</td>
            </tr>
						<tr>
							<td colspan=6 height=10>&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	  </td>
  </tr>
</table>
   </center>       
</form>
<?php  
}else{
  $SQL = "SELECT P.ID,P.Name 
          FROM Projects P, ProPermission M 
          WHERE P.ID=M.ProjectID 
          AND M.Modify='1' 
          AND M.UserID='$AccessUserID' 
          ORDER BY P.Name";
  $result = mysql_query($SQL, $mainDB->link);
  $num_rows = mysql_num_rows($result);
  if(!$num_rows){
    //===not access permssion===
  }else{
    $projectIDarr = array();
    $projectIDstr = '';
    while($row = mysql_fetch_assoc($result)){
      $projectIDarr[$row['ID']] = $row['Name'];
      if($projectIDstr) $projectIDstr .= ",";
      $projectIDstr .= $row['ID'];
    }
    
?>
<form name="raw_file_upload" method=post action=<?php echo $PHP_SELF;?>>
  <input type=hidden name="theaction" value="">
  <input type=hidden name="tableName" value="<?php echo $tableName?>">
  <input type=hidden name="projectID" value="">
    <center>
    <table border="0" cellpadding="0" cellspacing="1" width="700">
      <tr bgcolor="#637eef" bgcolor="#d2dcff">
		    <td colspan="2" align="center" height=25>
		      <font color="white" face="helvetica,arial,futura" size="3"><b>Upload Files</b></font>
		    </td>
	    </tr>
      <tr bgcolor="#d2dcff" bgcolor="#d2dcff">
		    <td colspan="2" align="center" height=25>
          <input type="radio" name="whichFolder" value="oldFolder" onclick="select_folder();">Exist Folder&nbsp;&nbsp;&nbsp;
          <input type="radio" name="whichFolder" value="newFolder" onclick="select_folder();">New Folder 
		    </td>
	    </tr>
		  <tr bgcolor="#d2dcff">          
	      <td width="50%" align=right>
          <table border="0" cellpadding="0" cellspacing="0" width="85%" >
            <tr>
              <td bgcolor="#d2dcff" width="70%" >
                <font color="#637eef" face="helvetica,arial,futura" size="2">&nbsp;&nbsp;<b>Exist Folers</b></font>                 
              </td>
            </tr>
            <tr>   
              <td bgcolor="#d2dcff" valign=top nowrap>&nbsp;        
              <select name="frm_old_folder_ID" size=20>
              <?php folder_option($storageDB, $tableName, $frm_old_folder_ID, $projectIDstr)?>                 
    				  </select>&nbsp;&nbsp;<br>&nbsp;
              </td>
    	      </tr>        
          </table>                      
	      </td>
        <td width="50%" align=right>
          <table border="0" cellpadding="0" cellspacing="0" width="85%">
            <tr>
              <td bgcolor="#d2dcff" width="70%">
                <font color="#637eef" face="helvetica,arial,futura" size="2">&nbsp;&nbsp;<b>New Folder Name</b></font>                 
              </td>
            </tr>
            <tr>
              <td bgcolor="#d2dcff" width="70%" >&nbsp;
                <input type='text' name='frm_new_folder_name' value='<?php echo $frm_new_folder_name?>' size=30>                 
              </td>
            </tr>
            <tr>
              <td bgcolor="#d2dcff" width="70%" >
                <font color="#637eef" face="helvetica,arial,futura" size="2">&nbsp;&nbsp;<b>Project</b></font>                 
              </td>
            </tr>
            <tr>   
              <td bgcolor="#d2dcff" valign=top nowrap>&nbsp;        
              <select name="frm_Project_ID" size=17>                 
    				 	<?php project_option($mainDB, $frm_Project_ID, $projectIDarr)?>
    				  </select>&nbsp;&nbsp;<br>&nbsp;
              </td>
    	      </tr>        
          </table>                      
	      </td>    
        </tr>	
        <tr bgcolor="#d2dcff">
		      <td colspan="3" align="center" height=25>
		      <input type='button' name='frm_submit' value=' Submit ' onClick="check_folder_select()">
		      </td>
	      </tr>        
      </table> 
   </center>       
</form>
<?php 
  }
}
?>
</body>
</html>
<?php 
function folder_option($DB, $tableName, $frm_ID, $projectIDstr){
  $frm_ID = trim($frm_ID);
  $SQL = "SELECT 
           ID, 
           FileName,
           ProjectID
           FROM $tableName 
           where FolderID=0 AND ProjectID IN ($projectIDstr)
           ORDER BY ID DESC";       
  $idNameArr = $DB->fetchAll($SQL);
  for($i=0; $i<count($idNameArr); $i++){
  ?>
    <option  value='<?php echo $idNameArr[$i]['ID']."|".$idNameArr[$i]['ProjectID']?>' <?php echo  ($frm_ID == $idNameArr[$i]['ID'])?" selected":"";?>><?php echo $idNameArr[$i]['FileName']?><br>
  <?php   
  }         
}
function project_option($DB, $frm_Project_ID, &$projectIDarr){
  foreach ($projectIDarr as $key => $value){
  ?>
    <option  value='<?php echo $key?>' <?php echo  ($frm_Project_ID==$key)?" selected":"";?>><?php echo $value?><br>
  <?php   
  }         
}                  
?>
