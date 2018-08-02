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

if(!isset($searchThis)){
  $searchThis = (isset($_SESSION["searchThis"]))?$_SESSION["searchThis"]:"";;
  $ListType = (isset($_SESSION["ListType"]))?$_SESSION["ListType"]:"";
}
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
<LINK REL="SHORTCUT ICON" HREF="../images/prohits.ico">
<link rel="stylesheet" type="text/css" href="./site_style.css">
<title>Prohits</title>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script type="text/javascript" src="../common/site_ajax.js"></script>
<!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
</head><basefont face="arial">
<BODY text=#000000 vLink=#000000 aLink=#000000 link=#000000 bgColor=#ffffff leftMargin=5
topMargin=5 rightMargin=5 marginheight="5" marginwidth="5">
  <script language='javascript'>
  function view_master_results(ResultFile,SearchEngine){
    if(ResultFile == ''){
      alert("No results file exists.");
      return false;
    }
    if(SearchEngine == "Mascot"){  
		  var mascot_IP = '<?php echo MASCOT_IP?>';
      <?php if(defined('MASCOT_IP_OLD')){?>
        	var mascot_IP_old = '<?php echo MASCOT_IP_OLD?>';
       	 if(ResultFile.search(/^\w/) != -1){
          		mascot_IP = mascot_IP_old;
       	 }
      <?php }?>  
        <?php if(MASCOT_USER){?>
	        var tmp_url = "http://"+mascot_IP+"<?php echo MASCOT_CGI_DIR;?>/login.pl";
	        tmp_url += "?action=login&username=<?php echo MASCOT_USER;?>&password=<?php echo MASCOT_PASSWD;?>";
	        tmp_url += "&display=nothing&savecookie=1&referer=master_results_2.pl?file=" + ResultFile;
      <?php }else{?>
          	var tmp_url = "http://"+mascot_IP+"<?php echo MASCOT_CGI_DIR;?>/master_results_2.pl?file=" + ResultFile;
       <?php }?>
        window.open(tmp_url,"mascot_win", 	"toolbar=1,menubar=1,scrollbars=1,resizable=1,width=800,height=800");
    }else if(SearchEngine == "GPM"){  
      var file = "http://<?php echo $gpm_ip;?>/thegpm-cgi/plist.pl?path=" + ResultFile;
			window.open(file,"_blank", "toolbar=1,menubar=1,scrollbars=1,resizable=1,width=800,height=800"); 
    }else{
      return;
    } 
  }
  PreloadImages('arrow_small.gif,icon_empty.gif,icon_carryover_color.gif,icon_carryover_color.gif,icon_peptide.gif,icon_notes.gif,icon_first.gif,icon_view.gif,icon_report.gif,icon_plate.gif,icon_picture.gif');
  </script>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr bgcolor=#054378>
      <td>
       <IMG SRC="images/site_head3_01.gif" HEIGHT=60 border=0></td>
      <td align="right" background=images/site_head3_02.gif width=2000>

      <IMG SRC="images/site_head3_02.gif" WIDTH=71 HEIGHT=60 border=0></td>
      <td align="right">
        <IMG SRC="images/site_head3_04.gif" WIDTH=486 HEIGHT=60></td>
    </tr>
    <tr height="1">
      <td bgcolor="white" colspan="3" height="1">
         <img src="./images/pixel.gif" width="1" height="1" border="0"></td>
    </tr>
    <tr>
      <td colspan="3">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td bgcolor="black">
            <img src="./images/shim.gif" border="0">
            <font color="white" face="helvetica,arial,futura" size="2">
            <?php 
            if(defined("VERSION")){
            echo "(version ". VERSION .")";
            }
            ?>
            <b>&nbsp;Current user:&nbsp;<?php echo $USER->Fname." ".$USER->Lname;?></b></font>
            </td>
            <td bgcolor="black">
            &nbsp;
            </td>
             <form method="post" action="./search.php" onSubmit="return search_check(this)">
             <input type=hidden name=sub value='<?php echo $sub;?>'>
             <input type=hidden name=Bait_ID value='<?php echo $Bait_ID;?>'>
             <input type=hidden name=Gel_ID value='<?php echo $Gel_ID;?>'>
             <input type=hidden name=Exp_ID value='<?php echo $Exp_ID;?>'>
            <td align="right" bgcolor="black">
<?php 
      			if(isset($AUTH) && $AUTH->Access){    
?>            
              <small><font face='verdana,aria' color=#FFFFFF>search for</font></small>
              <input class="small" type="text" name="searchThis" value='<?php echo ((isset($searchThis))?$searchThis:'')?>' size="20">              
              <input type=image src="./images/search.gif" border="0" width="11" height="11" ALT="search"  align="bottom">&nbsp;
              <a href="javascript: popwin('../doc/Analyst_help.php#faq35', 800, 600);" class="left_menu"><img src="./images/searchQ.gif" border="0" width="11" height="11" ALT="search help"  align="bottom" border=0></a>&nbsp;
	            </font>
              <img src="./images/shim.gif" width="25" height="1" border="0">
<?php 
      			}else{
              echo "&nbsp;";
            }    
?>               
            </td>
             </form>
          </tr>
        </table>
      </td>
    </tr>
    <tr height="1">
      <td bgcolor="black" colspan="3" >
      <img src="./images/pixel.gif" width="1" heitht=1 border="0"></td>
    </tr>
  </table>
  <?php if(isset($add_process_img)){?>
  <div style='display:block;text-align:center;}' id='process'><img src='./images/processing_wait.gif' border=0></div>
  <?php 
    @ob_flush();
    flush();
  }?>
  <table border="0" cellpadding="0" cellspacing="0" width="100%" height="421">
    <tr height="1">
      <td colspan="3" height=1>
          <img src="./images/pixel.gif" width="1" height="1" border="0"></td>
    </tr>
    <tr height="470">
      <td background="" bgcolor="#CCD9FE" valign="top" align="left" height="470" width=190>
        <img src="images/manue.gif" width="190" border="0">
        <?php  require("site_left_menu.inc.php"); ?>
      </td>
      <td width="1" bgcolor="black" valign="top" height="470">
      <img src="images/pixel.gif" width="1" height="1" border="0"></td>
      <td width="10000" valign="top" align="center" height="470">
<?php 
function get_selected($selected){
  $arr = array("Bait" => "Bait List", "Plate" => "Plate List", "Gel" => "Gel List", "Band" => "Sample List", "CO-IP" => "CO-IP", "Hit" => "Hit List");
  foreach ($arr as $key => $value){
    if($key == $selected){
      echo "<option value='$key' selected><font size=-2>$value</font>";
    }else{
      echo "<option value='$key'>$value";
    }
  }
}
$GelFreeColor = "#737373";
$unGelFreeColor = "#000000";
$GrowColor = "#d2691e";
$IpColor = "#ffa500";
$DigestColor = "#ffc0cb";
$LC_MSColor = "#63b1b1";
$RawFileColor = "#2080df";
$HasHitsColor = "#5b52ad";
$EmptyColor = "#d9e8f0";
$emptySign = 'O';
$NoBaitFoundColor = "#c0c0c0";
?>
  <!-- end of header -->
  

