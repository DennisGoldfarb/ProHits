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

$type_bgcolor = '#808040';
$pro_name_bgcolor = '#d1d0be';
$general_title_bgcol = '#b1b09e';
$bgcolor = "#f1f1ed";
$theaction = '';

$new_saint_ID = '';
$new_saint_folder = '';

$saintName = '';
$saintDescription = '';
 


$control_arr = array();
$msg_err = array();
$error_msg = '';

$saint_type = 'express';
$nControl = '4';
$nCompressBaits = '2';
$nburn = 2000;
$niter = 5000;
$lowMode=0;
$minFold=1; 
$fthres = '0';
$fgroup = '0';
$var = '0';
$normalize = '1';
$saint_ID = '';
$has_iRefIndex_file = 0;



$isZipped = '';
$files_div_style = 'Display:block';
$zipped_div_style = 'Display:none';
$form_div_style = 'Display:block';
$process_div_style = 'Display:none';

$control_div_style = 'Display:block';
$nocontrol_div_style = 'Display:none';


require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

$Log = new Log();
$is_error = 0;

$workDir = "../TMP/SAINT_".$AccessUserID."_upload/";
$SAINT_info = check_SAINT();
if($SAINT_info['error']) $error_msg = $SAINT_info['msg'];

if($theaction == "run_saint"){
  if(_is_dir($workDir)){
    @dir_empty($workDir);
  }else{
    if(!_mkdir_path($workDir)){
      $error_msg = "Cannot create $workDir. Please check the folder permision.";
    }
  }
  if($isZipped){
    if($_FILES["zip_file"]["error"] > 0){
      $error_msg = 'zip file not fully uploaded. please check the file size.';
    }
  }else if($_FILES["prey_file"]["error"] > 0){
    $error_msg = 'prey file not fully uploaded. please check the file size.';
  }
  if(!$error_msg){
    if($isZipped){
      move_uploaded_file($_FILES["zip_file"]["tmp_name"], "$workDir/" . $_FILES["zip_file"]["name"]);
      $cmd = "unzip -d $workDir "."$workDir/" . $_FILES["zip_file"]["name"] ." 2>&1";
      exec ($cmd, $output);
      if(isset($output[1]) and strpos($output[1],'End-of-central-directory signature not found')){
        $error_msg = 'The uploaded file is not zipfile.';
      }
    }else{
      move_uploaded_file($_FILES["bait_file"]["tmp_name"], "$workDir/" . $_FILES["bait_file"]["name"]);
      move_uploaded_file($_FILES["inter_file"]["tmp_name"], "$workDir/" . $_FILES["inter_file"]["name"]);
      move_uploaded_file($_FILES["prey_file"]["tmp_name"], "$workDir/" . $_FILES["prey_file"]["name"]);
    }
    if(!_is_file("$workDir/bait.dat") or !_is_file("$workDir/inter.dat") or !_is_file("$workDir/prey.dat")){
      $error_msg = 'bait.dat, inter.dat and prey.dat file names are required.';
    }
  }
  if(!$error_msg){
    $error_msg = check_uploaded_file_names($workDir);
  }
  if(!$error_msg){ 
    $other_option='';
    
    if($saint_type != 'express'){
      if(isset($SAINT_info['version']) and $SAINT_info['version']){
        $other_option =  "Version:".$SAINT_info['version'].",";
      }
    }else if(isset($SAINT_info['version_exp']) and $SAINT_info['version_exp']){
      $other_option =  "Version:".$SAINT_info['version_exp'].",";
    }
    
    $other_option .= "saint_type:$saint_type,";
    $other_option .= "nControl:$nControl,";
     
    $other_option .= "fthres:$fthres,";
    $other_option .= "fgroup:$fgroup,";
    $other_option .= "var:$var,";
    $other_option .= "nCompressBaits:$nCompressBaits,";
    $other_option .= "normalize:$normalize,";
    
    $other_option .= "nburn:$nburn,";
    $other_option .= "niter:$niter,";
    
    $other_option .= "lowMode:$lowMode,";
    $other_option .= "minFold:$minFold,";
     
    if($isZipped){
      $other_option .= "zippedFileName:".$_FILES["zip_file"]["name"].",";
    }
    
    $saintName = mysqli_escape_string($PROHITSDB->link, $saintName);
    $saintDescription = mysqli_escape_string($PROHITSDB->link, $saintDescription);
    //check if the name has been used.
    $SQL = "SELECT ID FROM SAINT_log WHERE  Name='$saintName' and ProjectID='$AccessProjectID'";
     
    if($PROHITSDB->exist($SQL)){
      $error_msg = "The SAINT name '$saintName' has been used by other task.";
    }
  }
   
  if(!$error_msg){
    $SQL = "insert into SAINT_log set 
      Name='$saintName (uploaded)', 
      Description='$saintDescription',
      UserID='$AccessUserID',
      ProjectID='$AccessProjectID',
      Status = '',
      Date=now(),
      UserOptions='$other_option'
      ";
    $new_saint_ID = $PROHITSDB->insert($SQL);
    $new_saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_$new_saint_ID/";
    if (!mkdir($new_saint_folder,0775, true)) {
      $error_msg = "Failed to create folders $new_saint_folder in Prohits storage.";
    }
    
  }
  if(!$error_msg){
    if(isset($new_saint_folder)){
      dir_copy($workDir,$new_saint_folder);
      if($new_saint_ID){
        $shell_log = $new_saint_folder."shell.log";
        $com = PHP_PATH ." ". dirname(__FILE__) ."/export_SAINT_shell.php ".$new_saint_ID. " " . $other_option;
         
        if(defined('DEBUG_SAINT') and DEBUG_SAINT){
          echo "Prohits SAINT stopped by administrator:<br> if you are Prohits admin copy following line and run it on the server shell for debug.<br>\n";
          echo "<font color=green>$com</font>";
          exit;
        }
        $tmp_PID = shell_exec($com . " >> $shell_log 2>&1  & echo $!");
        $tmp_PID = trim($tmp_PID);
        $SQL = "update SAINT_log set ProcessID='".$tmp_PID."', Status='Running' where ID='".$new_saint_ID."'";
        $PROHITSDB->update($SQL);
         
        echo "<script language='javascript'>\n";
        echo "window.location='".$_SERVER['PHP_SELF']."?theaction=status&PID=".$tmp_PID."&saint_ID=".$new_saint_ID."';\n";
        echo "</script>\n";
        exit;
      }
    }
  }
}
if($theaction == "status"){
  check_saint_status($saint_ID, $PID);
  exit;
}

function check_uploaded_file_names($workDir){
  if(is_file($workDir."/bait.dat") and is_file($workDir."/inter.dat") and is_file($workDir."/prey.dat") ){
    if(is_file($workDir."/iRefIndex.dat")){
      $has_iRefIndex_file = 1;
    }
    return "";
  }else{
    return "Please upload files with above names. 
    If you uploaded the zipped file, please zip the three files to one zipped file.";
  }
}
?>

<html>
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<STYLE type="text/css">
TD { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
function checkForm(theForm){
  var x=document.getElementById("isZipped");
  if(x.checked){
    if(theForm.zip_file.value.search(/zip/i)==-1){
      alert('Please upload zip file');return; 
    } 
  }else if(theForm.bait_file.value.search(/bait.dat/i) == -1){
      alert('Please upload bait file');return;  
  }
  else if(theForm.inter_file.value.search(/inter.dat/i) == -1){
      alert('Please upload inter file');return;  
  }else if(theForm.prey_file.value.search(/prey.dat/i) == -1){
      alert('Please upload prey file');return;  
  }
  if(isEmptyStr(theForm.saintName.value) || isEmptyStr(theForm.saintDescription.value)){
    alert("SAINT log Name and Description are required!");
    return false;
  }
  theForm.theaction.value = "run_saint";
  theForm.submit();
} 

function changeControl(obj){
   
  if(obj.value == 'saint_no_control'){
    showhideDiv('nocontrol_div','control_div');
    
  }else{
    showhideDiv('control_div','nocontrol_div');
  }
}
function showhideDiv(ShowDivID, HideDivID) {
  var obj = document.getElementById(ShowDivID);
  if(HideDivID){
    var obj_a = document.getElementById(HideDivID);
    obj_a.style.display = "none";
    obj.style.display = "block";
  }else{
    if(obj.style.display == "none"){
      obj.style.display = "block";
    }else{
      obj.style.display = "none";
    }  
  }
}
function displayZip(theForm){
  var x=document.getElementById("isZipped");
  if(x.checked){
    showhideDiv('zipped_div','files_div');
  }else{
    showhideDiv('files_div','zipped_div');
  }
} 
//-->
</script>
</head>
<body>
  <form name="saint_form" method=post action="<?php echo $PHP_SELF;?>" enctype="multipart/form-data">
  <input type=hidden name=theaction value=""> 
  <font face='helvetica,arial,futura' size='4' class=pop_header_text>Uploading input files to run SAINT</font>
  <hr size=1> 
  <TABLE BORDER=0 cellspacing=4 CELLPADDING=1 BGCOLOR="#ffffff" align=center >
  <TR>
    <td align=left>
      <b>Data must be prepared in three files. All files are tab or space delimited</b>.
      [<a href="javascript: popwin('../doc/files/saint-vignette.pdf',700,800, 'pdf');" class=button>detail</a>]
      <pre>
      File: bait.dat
      ----------------------------------------------
      Column 1:  ProjectID_SampleID: IP ID
      Column 2:  Saint Bait Name/Bait GeneName(geneID). Could be changed by user
      Column 3:  Control/Test
      
      File: inter.dat
      ----------------------------------------------
      Column 1:  ProjectID_SampleID: IP ID
      Column 2:  Saint Bait Name/Bait GeneName(geneID). Could be changed by user
      Column 3:  Prey ProteinID
      Column 4:  Prey Peptide Number
      
      File: prey.dat
      ----------------------------------------------
      Column 1:  Prey ProteinID
      Column 2:  Prey Protein Sequence Length
      Column 3:  Prey Gene Name(geneID)</pre>
      <?php 
      echo "<font color='#FF0000'>$error_msg</font>";
      ?>
     </td>
  </TR>
  <TR>
    <td bgcolor=#708090 align=center><br>
    <font color="#FFFFFF">
    <b><font size=+1>SAINT express</font></b>(<?php echo $SAINT_info['version_exp'];?>)<input type=radio value='express' name=saint_type<?php echo ($saint_type=='express')?" checked":"";?> onClick="changeControl(this)">&nbsp; &nbsp; &nbsp; 
    <b><font size=+1>SAINT</font></b>(<?php echo $SAINT_info['version'];?>)<input type=radio value='saint_control' name=saint_type<?php echo ($saint_type=='saint_control')?" checked":"";?> onClick="changeControl(this)"> &nbsp; &nbsp; &nbsp;
    <b><font size=+1>SAINT without control</font></b>(<?php echo $SAINT_info['version'];?>)<input type=radio value='saint_no_control' name=saint_type<?php echo ($saint_type=='saint_no_control')?" checked":"";?> onClick="changeControl(this)"><br>
    </font>
    <table border=0 width=100% cellspacing=1 CELLPADDING=1 align=center bgcolor=#708090>
      <tr bgcolor=white>
          <td align=right valign=top width=25%><b>Controls</b> </td>
          <td colspan=3 width=80%>
          
          <DIV ID='control_div' STYLE="<?php echo $control_div_style;?>">
            How many compressed controls:
            <input type=text size="2" maxlength="2" value='<?php echo $nControl;?>' name=nControl>
          </DIV>
          <DIV ID='nocontrol_div' STYLE="<?php echo $nocontrol_div_style;?>">
          frequency threshold for preys above which probability is set to 0 in all IPs:
          <input type=text size="3" maxlength="3" value='<?php echo $fthres;?>' name=fthres><br>
          frequency boundary dividing high and low frequency groups:
          <input type=text size="3" maxlength="3" value='<?php echo $fgroup;?>' name=fgroup><br>
          binary [0/1] indicating whether variance of count data distributions should be modeled or not:
          <input type=text size="3" maxlength="3" value='<?php echo $var;?>' name=var>
          </DIV>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top><b>Compress baits</b></td>
          <td colspan=3> <input type=text size=5 value='<?php echo $nCompressBaits;?>' name=nCompressBaits><br>
           replicates in each interaction with the highest counts is involved in the computation of the scores
          </td>
           
      </tr>
      <tr bgcolor=white>
          <td align=right ><b>Burn-in period</b></td>
          <td>nburn:<input type=text size=5 value='<?php echo $nburn;?>' name=nburn> </td>
          <td align=right ><b>Iterations</b></td>
          <td>niter:<input type=text size=5 value='<?php echo $niter;?>' name=niter></td>
      </tr>
      <tr bgcolor=white>
          <td align=right ><b>exclude extremely high counts</b></td>
          <td>lowMode:<input type=text size=5 value='<?php echo $lowMode;?>' name=lowMode> </td><br>
          <td align=right ><b>forcing separation  </b></td>
          <td>minFold:<input type=text size=5 value='<?php echo $minFold;?>' name=minFold></td>
      </tr>
      <tr bgcolor=white>
          <td align=right colspan=3><b>divide spectral counts by the total spectral counts of each IP</b></td>
          <td>normalize:<input type=text size=5 value='<?php echo $normalize;?>' name=normalize></td>
      </tr>
      <tr>
    </table>
    <font color="#FFFFFF"><b>Upload Prohits generated zipped file for SAINT (Three files are zipped in one file)</b>:</font>
    <input type="checkbox" ID='isZipped' name=isZipped value=1 onClick="displayZip(this.form)"<?php echo ($isZipped)?" checked":'';?>>
    <DIV ID='files_div' STYLE="<?php echo $files_div_style;?>">
    <table cellspacing="1" cellpadding="1" border="0" width=100%>
      <tr bgcolor=white>
          <td align=right width=30%><b>Bait table </b></td>
          <td width=70%><input type=file size=50 name=bait_file></td>
      </tr>
      <tr bgcolor=white>
          <td align=right><b>Interaction table</b></td>
          <td><input type=file size=50 name=inter_file></td>
      </tr>
      <tr bgcolor=white>
          <td align=right><b>Prey table</b></td>
          <td><input type=file size=50 name=prey_file></td>
      </tr>
    </table>
    </DIV>
    <DIV ID='zipped_div' STYLE="<?php echo $zipped_div_style;?>">
    <table cellspacing="1" cellpadding="2" border="0" width=100%>
      <tr bgcolor=white>
          <td align=right width=30%><b>Prohits SAINT zipped file</b></td>
          <td width=70%><input type=file size=40 name=zip_file></td>
      </tr>
    </table>
    </DIV>
    <table border=0 width=100% cellspacing=1 CELLPADDING=2 align=center bgcolor=#708090>
      <tr>
          <td colspan='4'> <font color="#FFFFFF"><b>SAINT Log Information</b></font></td>
      </tr>
      <tr bgcolor=white>
          <td align=right width=30%><b>Name</b></td>
          <td colspan=3 width=70%><input type=text size=60 value='<?php echo $saintName;?>' name=saintName> </td>
      </tr>
      <tr bgcolor=white>
          <td align=right ><b>Description</b></td>
          <td colspan=3><textarea cols="60" rows="2" name="saintDescription"><?php echo $saintDescription;?></textarea></td>
      </tr>
      <tr>
        <td colspan=4' align=center bgcolor=#ffffff><input type=button name='RunSAINT' value=' Run SAINT ' onClick="checkForm(this.form)" ></td>
      </tr>
    </table>
   </td>
   </tr> 
   </table>  
  </form>
</body>
</html>
 