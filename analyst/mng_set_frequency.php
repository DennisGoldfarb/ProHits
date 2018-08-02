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
$order_by = '';
$GeneID = '';
$msg = '';
$frm_frequency_name = '';
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

$bgcolor = "#e1e1e1";
$bgcolordark = "#8a8a8a";
if(!$order_by) $order_by = 'Value Desc';
//------------------------------------------------------------
$FrequencyLimit = $_SESSION["workingProjectFrequency"];
$ProjectName = $_SESSION["workingProjectName"];
?>
<div style='display:block' id='process' align="center"><img src='./images/process.gif' border=0></div> 
<?php 
ob_flush();
flush();

if($theaction == 'update_only' || $theaction == 'updateFrequency'){
  updata_frequency();
?>
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>
<?php 
  if($theaction == 'update_only'){ 
    frequency_updated();
    exit;
  }  
}

$Prohits_Data_dir = STORAGE_FOLDER . "Prohits_Data/";
$frequency_dir = $Prohits_Data_dir . "frequency";
$sub_frequency_dir = $Prohits_Data_dir . "subFrequency";
$user_frequency_dir = $Prohits_Data_dir . "user_d_frequency/P_$AccessProjectID";

$frequency_dir_arr['P'] = $frequency_dir;
$frequency_dir_arr['G'] = $sub_frequency_dir;
$frequency_dir_arr['U'] = $user_frequency_dir;

$all_frequency_name_lable_arr = array();
get_all_frequency_info();

$all_frequency_name_lable_arr_1d = array();
foreach($all_frequency_name_lable_arr as $typ_key => $frequency_name_lable_arr){
  foreach($frequency_name_lable_arr as $key => $val){
    $all_frequency_name_lable_arr_1d[$key] = $val;
  }
}

if($theaction == "delete_frequency"){
  $tmp_f_name_arr = explode(":", $frm_frequency_name);
  $deleted_frequency_file_name = $frequency_dir_arr[$tmp_f_name_arr[0]]."/".$tmp_f_name_arr[1];
  if(unlink($deleted_frequency_file_name)){
    $frm_frequency_name = '';
  }else{
    echo "Cannot delete frequency file<br>";
  }
}

if(!array_key_exists($frm_frequency_name, $all_frequency_name_lable_arr_1d)){
  $frm_frequency_name = '';
}

if(!$frm_frequency_name){
  $frm_frequency_name = "P:P".$AccessProjectID."_frequency.csv";
}

?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
<title>Prohits</title>
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>
<script language="Javascript" src="site_javascript.js"></script>
<script language='javascript'>
function updateFrequency(){
  theForm = document.del_form;
  theForm.theaction.value = "updateFrequency";
  theForm.submit();
}
function frequency_detail(){
  theForm = document.del_form
  theForm.submit();
}
function go_to_opener(){
  window.opener.location.href = "user_defined_frequency.php?firstDisplay=y";
  window.close();
}
function deleteFrequency(){
  theForm = document.del_form;
  theForm.theaction.value = "delete_frequency";
  theForm.submit();
}
</script>
</head><basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff>
<form name="del_form" method=post action="<?php echo $PHP_SELF;?>">
  <input type=hidden name=theaction value=''> 
  <input type=hidden name=order_by value='<?php echo $order_by;?>'>
  <input type=hidden name=frm_frequency_name value='<?php echo $frm_frequency_name;?>'>
  
<table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%>
  <tr>
    <td valign=top align=center bgcolor="white" width=100%>
    <table border="0" cellpadding="0" cellspacing="0" width="95%">
      <tr> 
        <td colspan=2><br><span class=pop_header_text>Frequency Set</span> (<?php echo $ProjectName;?>)</td>
      </tr>
      <tr>
        <td nowrap align=center height='1' colspan=2><hr size=1></td>
      </tr>
      <!--tr>
       <td colspan=2>Any protein found in association with <?php echo $FrequencyLimit;?>% or more of the baits assayed. 
       The frequency table is to be updated when you click "Update Frequency" button. 
       Frequency is calculated with in the project you selected. 
       After the frequency has been updated it will be automatically updated in hits report.
           <br>Any protein with frequency <?php echo $FrequencyLimit;?>% or more will be grayed out in report.<br>&nbsp;&nbsp; 
      </td>
      </tr--> 
      <tr> 
        <td align="" nowrap>Frequency List: 
        <select id="frm_frequency_name" name="frm_frequency_name" onchange="frequency_detail();">
          <option value="">-------------
      <?php 
        $deleted_frequency_lable = '';
        foreach($all_frequency_name_lable_arr_1d as $key => $val){
          if($frm_frequency_name == $key ){
            $selected = 'selected';
            $deleted_frequency_lable = $val;
          }else{
            $selected = '';
          }
      ?>
          <option value="<?php echo $key?>" <?php echo $selected?>><?php echo $val?><br>
      <?php }?>
        </select>
      <?php 
        if(strstr($frm_frequency_name,"U:") && 0){
          $tmp_frequency_name_arr = explode("-", $frm_frequency_name);
          $frequeny_owner_id = $tmp_frequency_name_arr[1];
          if($_SESSION['USER']->Type == 'Admin' || $frequeny_owner_id == $AccessUserID){
      ?>
          <input type=button value='Delete <?php echo $deleted_frequency_lable?> Frequency' onClick='javascript: deleteFrequency();'>
      <?php   }
        }
      ?>
        </td>    
      <?php if($AUTH->Modify){?>
        <td align="right" nowrap>&nbsp;
          <!--input type=button value='Update Frequency' onClick='javascript: updateFrequency();'-->
        </td>
      <?php }?>
      </tr>
 <tr>   
    <td align="right" nowrap colspan=2>&nbsp;<br>
      <?php 
        $theFile = "./export_frequency.php?frm_frequency_name=$frm_frequency_name";
        if(isset($all_frequency_name_lable_arr_1d[$frm_frequency_name])){
          $frequency_name_lable = (stristr($all_frequency_name_lable_arr_1d[$frm_frequency_name],"Frequency"))?$all_frequency_name_lable_arr_1d[$frm_frequency_name]:$all_frequency_name_lable_arr_1d[$frm_frequency_name]." Frequency";
      ?>
        <a href="<?php echo $theFile;?>" class=button>[Export <?php echo $frequency_name_lable?>]</a>
      <?php }?>
      <a href="javascript: window.close();" class=button>[Close Window]</a>
    </td>
 </tr>
 <tr>
    <td align="center" valign=top colspan=2>
    <font color=red><?php echo $msg;?></font>
      <table border="0" cellpadding="0" cellspacing="1" width="100%">   
      <tr bgcolor="">
        <td width="" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader><a href="<?php echo $PHP_SELF;?>?order_by=<?php echo ($order_by == "GeneID")? 'GeneID%20desc':'GeneID';?>&frm_frequency_name=<?php echo $frm_frequency_name?>">
        Gene ID</a>
        <?php if($order_by == "GeneID") echo "<img src='images/icon_order_up.gif'>";
          if($order_by == "GeneID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
        ?></div>
        </td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>
    	    <a href="<?php echo $PHP_SELF;?>?order_by=<?php echo ($order_by == "GeneName")? 'GeneName%20desc':'GeneName';?>&frm_frequency_name=<?php echo $frm_frequency_name?>">
    		 GeneName</a>
    		<?php if($order_by == "GeneName") echo "<img src='images/icon_order_up.gif'>";
    			if($order_by == "GeneName desc") echo "<img src='images/icon_order_down.gif'>";
    		?> </div>
    	  </td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center nowrap>
        <div class=tableheader>Gene Aliase</div>
    	  </td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap><div class=tableheader>
          <div class=tableheader><a href="<?php echo $PHP_SELF;?>?order_by=<?php echo ($order_by == "Value")? 'Value%20desc':'Value';?>&frm_frequency_name=<?php echo $frm_frequency_name?>">
         Frequency</a>
        <?php if($order_by == "Value") echo "<img src='images/icon_order_up.gif'>";
          if($order_by == "Value desc") echo "<img src='images/icon_order_down.gif'>";
        ?> </div>
        </td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center>
          <div class=tableheader>Links</div> 
        </td>
      </tr> 
<?php 
$tmp_frequency_name = explode(":", $frm_frequency_name);
$frequency_Dir = $frequency_dir_arr[$tmp_frequency_name[0]];
$FileName = $frequency_Dir."/".$tmp_frequency_name[1];

echo $tmp_frequency_name[1]."<br>";

if(strIstr($FileName, 'GeneLevel')){
  $GeneName_arr = array();
  $frequencyArr = generate_frequency_arr_for_geneLevel($FileName,$GeneName_arr);
}else{
  $frequencyArr = generate_frequency_arr($FileName);
} 

if(!$order_by){
  arsort($frequencyArr);
}

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);

if(strstr($order_by, 'GeneName')){
	//=====================================================================
	$genePropertyArr = array();
	$ENSgenePropertyArr = array();
	get_gene_property($genePropertyArr,$ENSgenePropertyArr,$frequencyArr,'key');
	$geneArr = array();
	$ENSgeneArr = array();
	$indexArr = array();
	sort_print_filter_table($genePropertyArr,$ENSgenePropertyArr,$order_by,'','y');
	//=====================================================================
}else{
	if($order_by == "GeneID"){
	  ksort($frequencyArr);
	}elseif($order_by == "GeneID desc"){
	  krsort($frequencyArr);
	}elseif($order_by == "Value"){
	  asort($frequencyArr);
	}elseif($order_by == "Value desc"){
	  arsort($frequencyArr);
	}
	foreach($frequencyArr as $key => $value){
	  $HitFrequency = $value;
	  $LocusTag = '';
	  $GeneName = '';
	  if(is_numeric($key)){
	    $SQL = "SELECT 
							EntrezGeneID, 
		          LocusTag, 
		          GeneName, 
		          GeneAliase, 
		          TaxID 
	            FROM Protein_Class 
	            WHERE EntrezGeneID = '$key'";
	  }else{
	    $SQL = "SELECT 
							ENSG as EntrezGeneID, 
	            GeneName, 
	            TaxID 
	            FROM Protein_ClassENS   
	            WHERE ENSG  = '$key'";
	  }  
	  $recoder = $proteinDB->fetch($SQL);
    $GeneAliase = '';
    if(count($recoder) == 5){
      $GeneName = $recoder['GeneName'];
      $LocusTag = $recoder['LocusTag'];
      if($LocusTag == "-") $LocusTag = '';
      $GeneAliase = $recoder['GeneAliase'];
      if($GeneAliase == "-" || $GeneAliase == "|" || !$GeneAliase){
        $GeneAliase = $LocusTag;
      }else{
        $GeneAliase = str_replace("|", "<br>&nbsp;&nbsp;", $GeneAliase);
        if($LocusTag && stristr($GeneAliase, $LocusTag) === FALSE){
          $GeneAliase .= "<br>&nbsp;&nbsp;".$LocusTag;
        }
      }      
    }
    if(stristr($FileName, 'GeneLevel_')){
      $tmp_arr = explode('|',$key);
      $GeneID = $tmp_arr[0];
      $GeneName = $tmp_arr[1];
    }else{
      $GeneID = $key;
    }
?>
      <tr bgcolor="<?php echo $bgcolor;?>">
        <td width="" align="left"><div class=maintext>&nbsp;
            <?php echo $GeneID;?>&nbsp;
          </div>
        </td> 
	      <td width="" align="left"><div class=maintext>&nbsp;
            <?php echo $GeneName?>&nbsp;
          </div>
        </td>
        <td width="" align="left"><div class=maintext>&nbsp;
            <?php echo $GeneAliase;?>&nbsp;
          </div>
        </td>
        <td width="" align="left"><div class=maintext>&nbsp;
            <?php echo $HitFrequency;?>%&nbsp;
          </div>
        </td>
        <td width="" align="left"><div class=maintext>&nbsp;
        <?php           
          $urlStr=get_URL_str('', $key);
          $urlStr = str_replace("<br>", "", $urlStr);
          echo $urlStr;
        ?>
          </div>
        </td>
      </tr>
    <?php 
	} //end for
}
    ?>    
   </table>
   </form>
 </td>
 </tr>
</table>
<center>  
<a href="javascript: window.close();" class=button>[Close Window]</a>
</center>
</body>
</html>
<?php 
function frequency_updated(){
  global $PHP_SELF,$AccessProjectName;
  $message = "The frequency has been updated";
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="./site_style.css"> 
<title>Prohits</title>
<script language='javascript'>
function frequence_detail(){
  theForm = document.updated_info_form;
  theForm.submit();
}
</script>
</head><basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff>
<form name="updated_info_form" method=post action="<?php echo $PHP_SELF;?>">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
 <tr> 
  <td align="left" onwrap>&nbsp; 
    <font color="navy" face="helvetica,arial,futura" size="3"><b>Frequency</font>
    <font color='red' face='helvetica,arial,futura' size='2'>(Project: <?php echo $AccessProjectName;?>)</font>
  </td>
 </tr>
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
</table>
<?php 
  flush();
?> 
<table border="0" cellpadding="0" cellspacing="0" width="100%"> 
 <tr>
    <td colspan=2" align=center onwrap><br><font color='red' face='helvetica,arial,futura' size='3'>
      <?php echo $message;?></font>
    </td>
 </tr>
 <tr>
    <td align=center><br><a href="javascript: frequence_detail('normal')">Show Frequency</a></td>
 </tr>
 <tr>
    <td align=center><br><input type=button value='Close Window' onClick='javascript: window.close();'></td>
 </tr>
</table> 
</td></tr></table>
</form>
</body>
</html>
<?php 
}
?>