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
ini_set('display_errors', 1);
set_time_limit(0);
ini_set("memory_limit","-1");

$frm_removed_id = '';

include_once("../../config/conf.inc.php");
include_once("../../common/mysqlDB_class.php");
include_once("../../common/user_class.php");
include_once("../../common/common_fun.inc.php");
include_once("functions.ini.php");
include_once("auto_update_protein_add_accession.inc.php");

$progressing_flag = './lock_flag.txt';
$download_log = './download.log';
$statusFile = './download_status.txt';
if(!defined("GET_GENE_FROM_RUL")){
  define("GET_GENE_FROM_RUL", "0");
}
define("INSERT_NO_GENE_PROTEIN", "1");
 
//=================================================================
$download_log_tmp = './download_tmp.log';
$fp_log_tmp = fopen($download_log_tmp, "a+");
//=================================================================

session_start();
if(!$_SESSION['USER']){
	echo "you didnot login";exit;
} 
$msg = '';
$err_msg = '';
$theAction = '';
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}

$frm_removed_id = preg_quote($frm_removed_id, '/');

//=======================================================================
//echo "\$frm_removed_id=$frm_removed_id";
//=======================================================================

$statusArr = array();
if(is_file($statusFile)){
  $status_fd = fopen($statusFile, "r");
  while(!feof($status_fd)){
    $buffer = fgets($status_fd, 20000);
    $buffer = trim($buffer);
    if(!$buffer) continue;
    list($key,$value) = explode("=", $buffer);
    $statusArr[$key] = $value;
  }
  fclose($status_fd);
}
ob_end_flush(); 
?>
<theml>
<head>
<link rel='stylesheet' type='text/css' href='../../analyst/site_style.css'>
<script language="Javascript" src="../../common/javascript/site_javascript.js"></script>
<script language="javascript">
function submitform(theFileID){
  var theForm = document.getElementById('uploadForm');
  var theFile = document.getElementById(theFileID);
  if(isEmptyStr(theFile.value)){
    alert("Please add file!");
    return false;
  }
  theForm.submit();
}
function show_div(sDiv, hDiv){
  var selected_obj = document.getElementById(sDiv);
  var selected_a_obj = document.getElementById(hDiv);
  _hide('display_msg');
  selected_obj.style.display = "block";
  selected_a_obj.style.display = "none";
   
}
function show_hide(theDiv){
  var selected_obj = document.getElementById(theDiv);
  _hide('display_msg');
  if(selected_obj.style.display == "block"){
    selected_obj.style.display = "none";
  }else{
    selected_obj.style.display = "block";
  }
}
function _hide(theDiv){
  var selected_obj = document.getElementById(theDiv);
  selected_obj.style.display = "none";
}
</script>
</head>
<basefont face='arial'>
<body >
<center>
<table width=90% border=0 cellspacing=0 cellpadding=0>
  <tr>
    <td>
    <span class=pop_header_text>Upload fasta file</span><br>
    <hr width=100% size=1 noshade>
    </td>
  </tr>
</table>
<DIV ID='upload_desc' STYLE="Display:block; border: #a4a4a4 solid 1px; width: 90%">
<table width=100%>
<tr>
<td><font face='arial' size=2pt>
Description:<br>
  <ul> 
   <li>The function adds new protein sequences in Protein_protein database. 
   The uploaded fasta files should be the same as those used for MS database searching.
   <li>NCBI files are the ProHits standard and are already mapped.
   <li>For UniProt & SwissProt the dat files need to first be downloaded & parsed on the previous opener web page to enable mapping to fasta.
   <li>Ensembl files must first be mapped by clicking <a href="javascript: show_div('upload_ensmap_div','upload_fasta_div')" class=button>[HERE]</a>.
   <li>Once mapping is completed (if needed), Click<a href="javascript: show_div('upload_fasta_div', 'upload_ensmap_div')" class=button>[HERE]</a> to upload Protein fasta files.
     
  </font>
  </ul>
  <a href=./download.log target=new class=button>[Log file]</a>
  &nbsp; &nbsp;&nbsp; &nbsp; 
  <a href="javascript: show_hide('uploaded_list')" class=button>[Uploaded files]</a>
</td></tr>
</table>
</DIV>
<br>
<DIV ID='uploaded_list' STYLE="Display:none; border: #a4a4a4 solid 1px; width: 90%">
<table width=100%>
<tr>
<td><font face='arial' size=2pt>
Uploaded files:<br>
  <ul> 
  <?php
  foreach($statusArr as $key=>$value){
    if(preg_match("/uploaded_date_(.*)/", $key, $matches)){
      echo "<li>".$matches[1]." ($value)";
    }else if(preg_match("/processed_date_(ipi\..*fasta)$/", $key, $matches)){
      echo "<li>".$matches[1]." ($value)";
    }
  }
  ?>
  </ul>
 </font>
</td></tr>
</table>
</DIV><br>
</center>
<div id=display_msg>
<?php 
 
flush();
$POST_MAX_SIZE = ini_get('post_max_size');
$UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');
 
//permission check
if(!is_writable($download_log)) die("$download_log is not writable");
$prohitsDB = new mysqlDB(PROHITS_DB);
//=================================================================
$mainDB = new mysqlDB(PROHITS_PROTEINS_DB);
//=================================================================
$proteinDB = $mainDB;
$db_link = $mainDB->link;
$SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName like 'Protein DB Configuration%' and UserID='".$_SESSION['USER']->ID."'";
$record = $prohitsDB->fetch($SQL);
$perm_modify = $record['Modify'];
$perm_delete = $record['Delete'];
$perm_insert = $record['Insert'];
if(!$record or !$perm_insert) {
  echo "The user has no permission to save file to Protien database.";
  exit;
}

$PHP_SELF = $_SERVER['PHP_SELF'];
if($theAction == 'uploaded_file'){
  if($_FILES['frm_fasta_file']['name']){
    $the_file = $_FILES['frm_fasta_file'];
    if(!preg_match("/.fasta$/", $the_file['name'], $matches)){
      $err_msg = "Please upload fasta file (extension is 'fasta').";
    }else{
echo "start time: ".@date("Y-m-d H:i:s")."<br>";
      $err_msg = upload_fasta_file($the_file);
echo "end time: ".@date("Y-m-d H:i:s")."<br>";
    }
  }else if($_FILES['frm_map_file']['name']){
    $the_file = $_FILES['frm_map_file'];
    $err_msg = upload_ens_map_file($the_file);
  }
  if(!$err_msg){ 
    if($status_fd = fopen($statusFile, "a+")){
      $to_file = 'uploaded_date_'.$the_file['name'].'='.@date('Y-m-d H:i:s');
      fwrite($status_fd, $to_file."\r\n");
      fclose($status_fd);
    }else{
      echo "could not open file $statusFile to write";
    }
    
  }
  if(isset($fp_log) and $fp_log) fclose($fp_log);
}
flush();
 
echo $msg;
echo "<font color=red>$err_msg</font>";
echo "</div><center>";

?>
 
<form id=uploadForm name=uploadForm method=post action=<?php echo $PHP_SELF;?> enctype="multipart/form-data">
<input type=hidden name=theAction value='uploaded_file'>
<center>
<DIV ID='upload_fasta_div' STYLE="Display:none; border: #a4a4a4 solid 1px; width: 90%">
<table width=100%>
  <tr bgcolor=white>
  	<td colspan="3">Don't upload protein ID start with >&nbsp;<input type=text size=38 name=frm_removed_id> eg. DECOY or rev</td>
  </tr>
  <tr bgcolor=white>
  	<td bgcolor=#a4d5c2><b><font face="Arial" size=2pt> &nbsp; Protein fasta file :  </font></b></td>
  	<td><input type=file size=45 name=frm_fasta_file id=fasta_file></td>
  	<td>&nbsp;</td>
  </tr>
 <tr>
   <td colspan=3 ><center><div class=maintext>Upload max file size:&nbsp;<font color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;Post max size:&nbsp;<font color='red'><?php echo $POST_MAX_SIZE?></font></div></center></td>
 </tr>
</table><br>
    <input type=button value='Submit' onClick="submitform('fasta_file')">
    <input type="button" value='Close' onClick="window.close()";>
    <br>
</DIV>
<DIV ID='upload_ensmap_div' STYLE="Display:none; border: #a4a4a4 solid 1px; width: 90%">
<table width=100%>
  <tr bgcolor=white>
  	<td bgcolor=#cccccc colspan=3><font face="Arial" size=2pt>
  Download Ensembl map file
  <OL>
  <li> http://www.ensembl.org
  <li> BioMart (at the top of the page) 
  <li> CHOOSE DATABASE "Ensembl Genes xx" then select DATASET
  <li> Click Attributes at the left menu to select:<br>
        Ensembl Gene ID<br> 
        Ensembl Protein ID<br>
        Description<br>
        Associated Gene Name<br>
        EntrezGeneID<br>
  <li>  Results => File (TSV) => Unique results only
        </font>
    </td>
  </tr>
  <tr bgcolor=white>
  	<td bgcolor=#add8e6><b><font face="Arial" size=2pt> &nbsp; Ensembl map file :  </font></b></td>
  	<td><input type=file size=45 name=frm_map_file id=map_file></td>
  	<td>&nbsp;</td>
  </tr>
 <tr>
   <td colspan=3 ><center><div class=maintext>Upload max file size:&nbsp;<font color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;Post max size:&nbsp;<font color='red'><?php echo $POST_MAX_SIZE?></font></div></center></td>
 </tr>
</table><br>
    <input type=button value='Submit' onClick="submitform('map_file')">
    <input type="button" value='Close' onClick="window.close()";>
    <br>
</DIV>
</form>
</body>
</html>
