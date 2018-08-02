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
$show = '';
$theaction = '';
$fileLocation = '';
$delimit = "";
$frm_start_line = 1;
$frm_comma = '';
$frm_tab = '';
$frm_space = '';
$frm_field_name = '';
$frm_save_type = '';
$frm_index = 'ok';
$frm_group_type = '';
$frm_group_name_t = '';
$frm_group_name_s = '';
$message = '';

require("../common/site_permission.inc.php");
require_once("msManager/is_dir_file.inc.php");


$bgcolor = "#f3eee2";
$lableSize = '2';
$lineSelectLimit = 200;

$delimit_str = $frm_space.$frm_comma.$frm_tab;
if($frm_space) $delimit = " ";
if($frm_tab) $delimit .= "\t";
if(!$delimit or $frm_comma){
  $delimit .= ",";
  $frm_comma = "c";
}

$NS_Dir = STORAGE_FOLDER."Prohits_Data/Non_Specific/";
//$NS_Dir = "../TMP/Non_Specific/";
if(!_is_dir($NS_Dir)) _mkdir_path($NS_Dir);
$NS_upload_dir = $NS_Dir."NS_upload/";
if(!_is_dir($NS_upload_dir)) _mkdir_path($NS_upload_dir);
$NS_data_dir = $NS_Dir."NS_data/";
if(!_is_dir($NS_data_dir)) _mkdir_path($NS_data_dir);
$NS_old_data_dir = $NS_Dir."NS_old_data/";
if(!_is_dir($NS_old_data_dir)) _mkdir_path($NS_old_data_dir);

$new_file_name = "P".$AccessProjectID. "_NS.txt";
$new_full_file_name = $NS_upload_dir . $new_file_name;

if($theaction == "process_file"){
  $existIDarr = array();
  $oldCounter = 0;
  if($frm_group_type == 'text'){
    $frm_group_name_t = trim($frm_group_name_t);
    $SQL = "INSERT INTO `ExpBackGroundSet` SET 
            `Name`='$frm_group_name_t',
            `ProjectID`='$AccessProjectID',
            `UserID`='$AccessUserID',
            `Date`='".@date("Y-m-d")."'";
    if(!$frm_group_name_s = $HITSDB->insert($SQL)){
      echo "db insert problem";
      exit;
    }
    $fileName = "P".$AccessProjectID."_G".$frm_group_name_s."_".$frm_group_name_t.".txt";
    $SQL = "UPDATE `ExpBackGroundSet` SET
           `FileName`='$fileName'
           WHERE ID= '$frm_group_name_s'";
    if(!$ret = $HITSDB->execute($SQL)){
      echo "db update problem";
      exit;
    }       
    $frm_save_type = "new";
  }elseif($frm_group_type == 'select'){
    $SQL = "SELECT `FileName` FROM `ExpBackGroundSet` WHERE `ID`='$frm_group_name_s'";
    if(!$tmpArr = $HITSDB->fetch($SQL)){
      echo "db fetch problem";
      exit;
    }
    $fileName = $tmpArr['FileName'];
  }
  $dataFileFullName = $NS_data_dir.$fileName;
  if(_is_file($dataFileFullName)){
    $backupFileName = "U_".$AccessUserID."_".@date("Y-m-d")."_".$fileName;
    $backupFileFullName = $NS_old_data_dir.$backupFileName;
    if(!copy($dataFileFullName, $backupFileFullName)){
      echo "Cannot backup file: $dataFileFullName";
      exit;
    }
  }
  if($frm_save_type == 'appand'){
    if(_is_file($dataFileFullName)){
      $tmpStr = file_get_contents($dataFileFullName);
      $tmpStr = trim($tmpStr);
      $existIDarr = explode(",",$tmpStr);
      $oldCounter = count($existIDarr);
    } 
    if(!$NS_data_handle = fopen($dataFileFullName, "w")){
      echo "Cannot open file $dataFileFullName";
      exit;
    }
  }else{
    if(!$NS_data_handle = fopen($dataFileFullName, "w")){
      echo "Cannot open file $dataFileFullName";
      exit;
    }
  }
  if(!$NS_handle = fopen($new_full_file_name, "r")){
    echo "Cannot open file $new_full_file_name";
    exit;
  }  
  
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
  $frm_index = $frm_index - 1;
  $lineCounter = 0;
  $geneIDcounter = 0;
//------------------------------------------------------------------------------
  $buffer = fgets($NS_handle);
  $buffer = trim($buffer);
  $start_line = $frm_start_line-1;//-2;
  
  
  $buffer_arr = explode("\r",$buffer);    
  if(count($buffer_arr) > 1){
    for($i=1; $i<count($buffer_arr); $i++){
      if($frm_start_line - $i > 0) continue;
      if(add_to_arr($buffer_arr[$i])) continue;
    }
  }else{
    while($start_line>0){
      $buffer = fgets($NS_handle);
      $buffer = trim($buffer);
      $start_line--;
      $lineCounter++;
    }
		while(!feof($NS_handle)){
      //if(++$lineCounter < $frm_start_line) continue;
    	$buffer = fgets($NS_handle);     
      if(add_to_arr($buffer)) continue;
		}
  }
//-----------------------------------------------------------------------------
  $geneIDstr = implode(",", $existIDarr);
  fwrite($NS_data_handle, $geneIDstr);
  
  fclose($NS_handle);
  fclose($NS_data_handle);
  $message = "The uploaded file has been successfully processed (added $geneIDcounter EntrezGeneID).";
  
  $theaction = '';
  $frm_group_type = '';
  $frm_comma = 'c';
  $frm_space = '';
  $frm_tab = '';
  $delimit = ",";
  $frm_group_name_s = '';
  $frm_group_name_t = '';
  $frm_save_type = '';
  $frm_field_name = '';
  $frm_start_line = 1;
  $frm_index = '';
  
}elseif($theaction == 'upload_file'){
  if(!$_FILES['frm_NS_file']['name']){
    echo "file name is empty";exit;
  }
	$uploaded_file_name = $_FILES['frm_NS_file']['name'];
  $uploaded_file_type = $_FILES['frm_NS_file']['type'];
  if(move_uploaded_file($_FILES['frm_NS_file']['tmp_name'], $new_full_file_name)){
    $msg = "image was successfully uploaded----$new_file_name";
		if(!$NS_handle = fopen($new_full_file_name, "r")) exit;
	}else{
	  $msg = "<font color=#FF0000>Possible file upload attack! Please try again</font>";
    $theaction = '';
  }
  
}elseif($theaction == "chang_lable"){
  if(!$NS_handle = fopen($new_full_file_name, "r")) exit;
}
$SQL = "SELECT `ID`,`Name` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
$NSgroupArr = $HITSDB->fetchAll($SQL);
if(!$theaction || $theaction == 'upload_file'){
  //if($NSgroupArr){
  if(!$frm_group_type || $frm_group_type == "select"){
    $groupA = 'block';
    $groupB = 'none';
    $groupLable = 'New';
    $frm_group_type = "select";
  }else{
    $groupA = 'none';
    $groupB = 'block';
    $groupLable = 'List';
    $frm_group_type = "text";
  }
}else{
  if($frm_group_type == "select"){
    $groupA = 'block';
    $groupB = 'none';
    $groupLable = 'New';
  }else{
    $groupA = 'none';
    $groupB = 'block';
    $groupLable = 'List';
  }
}
$onClick = '';
$onClick2 = '';

if($theaction){
  $onClick = "onclick=\"change_lables();\"";
  $onClick2 = "onchange=\"change_lables();\"";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<title>Prohits</title>
<STYLE type=text/css>
	#gdir {
	width:580px;
	height:170px;
	overflow:auto;
	border: black solid 1px;
	font-family: tahoma;
	font-size:10px;
	position:relative;
  white-space: nowrap;
	background-color : #ffffff;
	}
  TD {
  font-family : Arial, Helvetica, sans-serif;
  FONT-SIZE: 8pt;
  }
</STYLE>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="javascript">
var groupNameArr = new Array();
<?php 
  $j = 0;
  foreach($NSgroupArr as $value){
?>
    groupNameArr[<?php echo $j++?>] = '<?php echo $value['Name']?>';
<?php }?>

function process_file(){
  var theForm = document.update_NS_form;
  var radio_obj = theForm.frm_save_type;
  if(radio_obj[0].checked == false && radio_obj[1].checked == false){
    alert('Please select [Add as new] or [Append to existing].');
    return false;
  }else if(radio_obj[0].checked == true){
    if(!onlyAlphaNumerics(theForm.frm_group_name_t.value, 7)){
      alert("Only characters \"%+-_A-Za-z0-9\(\)\.:\" and spaces are allowed.");
      return false;
    }
    for(var i=0; i<groupNameArr.length; i++){
      if(groupNameArr[i] == theForm.frm_group_name_t.value){
        alert("The set name is already exist. Please give another name.");
        return false;
      }
    }
    theForm.frm_group_type.value = "text";
  }else if(radio_obj[1].checked == true){
    if(theForm.frm_group_name_s.value == ''){
      alert('Please select a existing group.');
      return false;
    }
    theForm.frm_group_type.value = "select";
  }
  var flag = 0;
  for(var i=0; i<theForm.frm_index.length; i++){
    if(theForm.frm_index[i].checked == true){
      flag = 1;
      break;
    }
  }
  if(flag == 0){
    alert("Please select a field as EntrezGeneID");
    return false;
  }
  theForm.theaction.value = "process_file";
  theForm.submit();
}

function up_load_file(){
	theForm = document.update_NS_form;
  if(theForm.frm_NS_file.value == ''){
    alert('Please browse files');
    return false;
  }
	theForm.theaction.value = "upload_file";
  theForm.submit();
}
function change_lables(){
  theForm = document.update_NS_form;
  theForm.theaction.value = "chang_lable";
  theForm.submit();
}

function dis_item(){
 theForm = document.update_NS_form;
  var radio_obj = theForm.frm_save_type;
  if(radio_obj[0].checked == true){
    theForm.frm_group_name_t.disabled = false;
    theForm.frm_group_name_s.disabled=true;
  }else if(radio_obj[1].checked == true){
    theForm.frm_group_name_t.disabled = true;
    theForm.frm_group_name_s.disabled = false;
  }
}
</script>
</head>
<BODY onload='dis_item();'>
<table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%>
<tr><td valign=top align=center bgcolor="white" width=100%>
	<form name="update_NS_form" method=post action=<?php echo $PHP_SELF;?>  enctype="multipart/form-data">
	<input type='hidden' name='theaction' value="<?php echo $theaction?>">
  <input type='hidden' name='frm_group_type' value="<?php echo $frm_group_type?>">
	<table border="0" cellpadding="2" cellspacing="0" width="95%" bgcolor="<?php echo $bgcolor;?>">
  <tr>
    <td bgcolor=white>
    <span class=pop_header_text>Non-specific (background)</span> Add / Modify Set
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    <a href="javascript: popwin('../doc/Analyst_help.php#faq40', 800, 600, 'help');"><img src='./images/icon_HELP.gif' border=0 ></a>
    <br><hr size=1>
    <font size="2" face="Arial" color="green"><?php echo $message?></font>
    </td>
  </tr>
  <tr>
	<td align="center" valign=top nowrap width="100%">
    <table border="0" cellpadding="1" cellspacing="1" width="580" bgcolor="white">
  	<tr bgcolor="<?php echo $bgcolor;?>">
  		<td valign=top nowrap width="10%"><font size="<?php echo $lableSize?>" face="Arial"><b>Upload File: </b></font></td>
  	  <td align="left" valign=top nowrap>&nbsp;&nbsp;
  	    <input type='file' name='frm_NS_file' size='50'>&nbsp;
        <input type='button' name='frm_sent' size='30' value="Upload File" onclick="up_load_file();">
  	  </td>
  	</tr>
  	<tr bgcolor="<?php echo $bgcolor;?>">
  		<td valign=top nowrap><font size="<?php echo $lableSize?>" face="Arial"><b>Field Delimiter: </b></font></td>
  	  <td align="left" valign=top nowrap>&nbsp;
  	    <input type='checkbox' name='frm_comma' value="c" <?php echo ($frm_comma=="c")?'checked':''?> <?php echo $onClick;?>><font size="<?php echo $lableSize?>" face="Arial">&nbsp;Comma</font>
  			<input type='checkbox' name='frm_tab' value="t" <?php echo ($frm_tab=="t")?'checked':''?> <?php echo $onClick;?>><font size="<?php echo $lableSize?>" face="Arial">&nbsp;Tab</font>
  			<input type='checkbox' name='frm_space' value="s" <?php echo ($frm_space=="s")?'checked':''?> <?php echo $onClick;?>><font size="<?php echo $lableSize?>" face="Arial">&nbsp;Space</font>
  	  </td>
  	</tr>
    
    
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td valign=top nowrap rowspan=2><div class=middle_bold>Set Name: </b></div></td>
      <td colspan=3>
        <div class=middle>
        <input type='radio' name='frm_save_type' value="new" <?php echo ($frm_save_type=="new")?'checked':''?> onClick="javascript: dis_item();">Add as new<?php echo  str_repeat("&nbsp;",15);?>
        <input type='text' name='frm_group_name_t' size="39" maxlength="50" value="<?php echo $frm_group_name_t?>" >
        </div>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan=3>
        <div class=middle>
        <input type='radio' name='frm_save_type' value="appand" <?php echo ($frm_save_type=="appand")?'checked':''?> onClick="javascript: dis_item();">Append to existing</font>&nbsp;&nbsp;&nbsp;  
        <select name="frm_group_name_s">
          <option value="">----Select a group----<br>
          <?php 
            foreach($NSgroupArr as $valeu){
              echo"<option value='".$valeu['ID']."' ".(($frm_group_name_s==$valeu['ID'])?'selected':'').">".$valeu['Name'];
            }
          ?>  
        </select>
        </div>
      </td>
    </tr>
    
    <tr bgcolor="<?php echo $bgcolor;?>" >
    <td colspan=2>
    <input type='checkbox' name='frm_field_name' value="y" <?php echo ($frm_field_name=="y")?'checked':''?> <?php echo $onClick;?>>
    <font size="<?php echo $lableSize?>" face="Arial">Transfer first line as attribute name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Start Import Row</font>
    <select name="frm_start_line" <?php echo $onClick2;?>;>
      <?php  
  			for($i=1; $i<$lineSelectLimit; $i++){
      ?>
        <option value="<?php echo $i?>"<?php echo ($frm_start_line==$i)?'selected':''?>><?php echo $i;?><br>
      <?php   
        }
      ?>
  	</select>
    </td>	
	  </tr>
    <tr bgcolor="<?php echo $bgcolor;?>" >
    <td colspan=2>
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
  	  <tr bgcolor="<?php echo $bgcolor;?>">
      <td width="50%" nowrap valign="bottom">
      <br><font size="<?php echo $lableSize?>" face="Arial"><b>Select EntrezGeneID Field</b></font>
      </td>
      <td width="50%" nowrap align="right" valign="bottom">
<?php if($theaction){?>
      <input type='button' name='frm_process' size='30' value="Process File" onclick="process_file();">
<?php }?>      
      </td>
      </tr>
      </table>
    </td>	
	  </tr>
    </table>
  </td>
  </tr>  
  <tr>
	<td align="center" bgcolor="<?php echo $bgcolor;?>">	
	<DIV id=gdir>
	<table border="1" cellpadding="1" cellspacing="1" width="50%">
<?php 
	$lineCounter = 0;
	$columLen1 = $columLen2 = 0;  
	if($theaction == 'upload_file' || $theaction == 'chang_lable'){
    $start_line = $frm_start_line-1;
    $buffer = fgets($NS_handle);
    $buffer = trim($buffer);
    
    
     
    
    
    $buffer_arr = explode("\r",$buffer);    
    if(count($buffer_arr) > 1){
      $columArr = preg_split("/[$delimit]/",$buffer_arr[0]);
    }else{
      while($start_line>0){
        $buffer = fgets($NS_handle);
        $buffer = trim($buffer);
        $start_line--;
        $lineCounter++;
      } 
      
      $columArr = preg_split("/[$delimit]/",$buffer);
    }
    $columLen1 = count($columArr);
//echo $columLen1;exit;    
    $lineCounter++;
    
    echo "<tr>";        
		for($i=0;$i<$columLen1;$i++){
        $bgcolor = ($frm_index==$i+1)?"bgcolor='#d6e2dc'":'';
  ?>
  		<td nowrap <?php echo $bgcolor;?>>
  			<input type='radio' name='frm_index' value="<?php echo $i+1?>" <?php echo (($frm_index==$i+1)?'checked':'')?> <?php echo $onClick;?>>&nbsp;
  <?php 
        if($frm_field_name == 'y'){
          echo $columArr[$i];
        }else{
          echo "Field ".($i+1);
        }  
  ?>
  		</td>
  <?php 			
		}
    echo "</tr>";
    if($lineCounter >= $frm_start_line){
//-------------------------------------------------------------------        
    echo "<tr>";        
    for($i=0;$i<$columLen1;$i++){
      $bgcolor = ($frm_index==$i+1)?"bgcolor='#d6e2dc'":'';
  ?>
		<td nowrap <?php echo $bgcolor;?>><?php echo ($columArr[$i]?$columArr[$i]:"&nbsp;")?></td>
  <?php 			
		}
    echo "</tr>";
    }
//------------------------------------------------------------------   
    if(count($buffer_arr) > 1){
      for($i=1; $i<count($buffer_arr); $i++){
        if($start_line - $i > 1) continue;
        print_line($buffer_arr[$i]);
        if($lineCounter	> $frm_start_line + 20) break;
      }
    }else{
  		while(!feof($NS_handle)){
      	$buffer = fgets($NS_handle);     
        print_line($buffer);
        if($lineCounter	> $frm_start_line + 20) break;
  		}
    }
    fclose($NS_handle);	
	}
?>
	</table>
	</DIV>
	</td>
	</tr>
	</table>
	</form>
</td></tr></table>
</body>
</html>
<?php 
function print_line($buffer){
  global $lineCounter,$frm_start_line,$columLen1,$columLen2,$bgcolor,$frm_index,$delimit;     
      $buffer = trim($buffer);
			if(!$buffer) return;
      $lineCounter++;
      if($lineCounter < $frm_start_line) return;

      $columArr = preg_split("/[$delimit]/",$buffer);
      $columLen2 = count($columArr);
//-----------------------------------------------------------------        
echo "<tr>";        
        for($i=0;$i<$columLen2;$i++){
          $bgcolor = ($frm_index==$i+1)?"bgcolor='#d6e2dc'":'';
  ?>
  		<td nowrap <?php echo $bgcolor;?>><?php echo ($columArr[$i]?$columArr[$i]:"&nbsp;")?></td>
  <?php 			
  				}
          for($i=0;$i<$columLen1-$columLen2;$i++){
  ?>
  		<td nowrap>&nbsp;</td>
  <?php 			
	  }
echo "</tr>";
//----------------------------------------------------------------------
}

function add_to_arr($buffer){
  global $frm_index,$existIDarr,$geneIDcounter,$delimit,$proteinDB;
	$buffer = trim($buffer);
	if(!$buffer) return 1;
  $columArr = preg_split("/[$delimit]/",$buffer);
  $geneID = $columArr[$frm_index];
  $SQL = "SELECT `EntrezGeneID` FROM `Protein_Class` WHERE `EntrezGeneID`='$geneID'";
  if(!$tmpArr = $proteinDB->fetch($SQL)) return 1;
  if(in_array($geneID, $existIDarr)) return 1;
  array_push($existIDarr, $geneID);
  $geneIDcounter++;
  return 0;
}          
?>