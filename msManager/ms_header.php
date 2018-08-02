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
//$enable_comet = true;
//$enable_msgfpl = true;
//$enable_MSFragger = true;

 
$storage_ip = STORAGE_IP;
if(STORAGE_IP=='localhost') $storage_ip = $PROHITS_IP;
 
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<LINK REL="SHORTCUT ICON" HREF="../images/prohits.ico">
<link rel="stylesheet" type="text/css" href="./ms_style.css">
<title>ms data management</title>
<script type="text/javascript" src="./ms.js"></script>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script> 
 
<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script> 
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>

<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css" /> 
 
</head>
<body  bgcolor=#d3d3d3 <?php echo ((isset($php_file) && $php_file=="ms_storage_raw_info")?'onload="initial()"':'')?>><center>
<table width=1100 cellpadding=0 cellspacing=0 border=0>
<tr>
<td class=tb colspan=3><img src=./images/dot.gif width=1 height=1></td>
</tr>
<tr>
<td class=tb width=1><img src=./images/dot.gif width=1 height=0></td>
<td width=1098 valign=bottom nowrap><IMG SRC="images/site_head_011.gif" WIDTH=500 HEIGHT=75><IMG SRC="images/site_head_02.gif" WIDTH=598 HEIGHT=75></td>
<td class=tb width=1><img src=./images/dot.gif width=1 height=1></td>
</tr>
</table>
<!-- menue --------------------------------------->
<table width=1100 cellpadding=0 cellspacing=0 border=0>
  <tr>
  <td class=tb rowspan=2><img src=./images/dot.gif width=1 height=1></td>
  <td class=ttabs width=1098 valign=bottom><img src=images/dot.gif width=1 height=1><?php 
 //is no $thePage variable passed
 
 if(isset($thePage)){
   if(strstr($SCRIPT_NAME,"storage")){
    $thePage = "storage";
   }else if(strstr($SCRIPT_NAME,"search")){
    $thePage = "search";
   }else{
    $thePage = "home";
   }
 }
 //$tmp_str = str_replace(".php","",$tmp_str);
 echo "<a href='./'><img src=images/home";
 echo ($thePage=="home")?"_black":"";
 echo ".gif width=120 height=26 border=0 ></a>";
 echo "<a href='./ms_storage.php'><img src=images/storage";
 echo ($thePage=="storage")?"_black":"";
 echo ".gif width=120 height=26 border=0 ></a>";
 echo "<a href='./ms_search.php'><img src=images/search";
 echo ($thePage=="search")?"_black":"";
 echo ".gif width=120 height=26 border=0 ></a>";
 $anchor = '';
 if($SCRIPT_NAME == 'ms_storage.php' or $SCRIPT_NAME == 'ms_storage_raw_data.php'){
  $anchor = 'Storage';
 }else if($SCRIPT_NAME == 'ms_storage_fetch_raw.php'){
  $anchor = 'Searching_files';
 }else if($SCRIPT_NAME == 'ms_search.php'){
  $anchor = 'Using_Auto_Search';
 }else if($SCRIPT_NAME == 'ms_search_task_list.php' 
       or $SCRIPT_NAME == 'ms_search_task.php'
       or $SCRIPT_NAME == 'ms_search_task_view.php'
 ){
  $anchor = 'Manually_initiate';
 }else if($SCRIPT_NAME == 'ms_search_results_detail.php'){
  $anchor = 'View_Results';
 }
 
 
?></td>
  <td class=tb rowspan=2><img src=./images/dot.gif width=1 height=1></td>
  </tr>
  <tr>
  <td><?php 
       $tmp_img = "etln_". $thePage . ".gif";
       echo "<img src=./images/$tmp_img width=790 height=1 border=0>";
?></td>
  </tr>
 </table>

<table width=1100 cellpadding=0 cellspacing=0 border=0><tr>
<td class=tb width=1><img src=./images/dot.gif width=1 height=1></td>
<td bgcolor=black align=right><img src=./images/dot.gif width=1 height=18 align=right>
 <font color="white" face="helvetica,arial,futura" size="2">
<?php 
if(defined("VERSION")){
echo "(version ". VERSION .")&nbsp; &nbsp; &nbsp;";
}
?>
</font>
<a href="javascript: popwin('../doc/management_help.html#<?php echo $anchor;?>',782,750, 'help');" class=logout>Help</a>
&nbsp; &nbsp; &nbsp;
<a href=../analyst/index.php class=logout>Analyst</a>
&nbsp;&nbsp; &nbsp;
<a href=../common/logout.php class=logout>Logout</a>
</td>
<td class=tb  width=1><img src=./images/dot.gif width=1 height=1></td>
</tr>
</table>
<table bgcolor=#ffffff width=1100 cellpadding=0 cellspacing=0 border=0><tr>
<td class=tb width=1><img src=./images/dot.gif width=1 height=1></td>
<td align=right width=1098>
<center>
<!----- containt ------------------------------------------------->