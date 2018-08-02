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

$orderBy = "GeneName";
$show_found_hits = '';
require("../common/site_permission.inc.php");
require("analyst/common_functions.inc.php");
include("common/common_fun.inc.php");
require_once("msManager/is_dir_file.inc.php");

$bgcolordark_header = '#808040';
$bgcolorsubdark_header = '#d2d2a6';
$bgcolorlight = '#f5f5f5';
$bgcolordark = '#cecece';
$bgHitcolor = "#e2e083";


$tmp_file = $level1_matched_file;
if(!_is_file($tmp_file)){
  echo "Cannot find file $tmp_file";
}

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);

if(!_is_file($tmp_file)){
  echo "file $tmp_file is not exists";
  exit;
}
if(!$tmp_handle = fopen($tmp_file, 'r')){
  echo "Cannot open file ($tmp_file)";
}

$rootArr = array();
$baitInfoArr = array();
$flag = '';
while(!feof($tmp_handle)){
  $buffer = fgets($tmp_handle, 4096);
  $buffer = trim($buffer);
  if(!$buffer) continue;
  if($buffer == "edge_info"){
    $flag = 1;
    continue;
  }elseif($buffer == "bait_info"){
    $flag = 2;
    continue;
  }
  
  if($flag == "1"){
    $tmpArr = explode(",",$buffer);
    if(!$show_found_hits && $tmpArr[0]) continue;
    $tmpArr2 = explode(" ",$tmpArr[1]);
    if(!array_key_exists($tmpArr2[0], $rootArr)){
      $rootArr[$tmpArr2[0]] = array();
    }  
    array_push($rootArr[$tmpArr2[0]], $buffer);
  }elseif($flag == "2"){
    $tmpArr = explode(",",$buffer);
    $tmpArr2 = explode(" ",$tmpArr[0]);
    $baitInfoArr[$tmpArr2[0]] = $tmpArr2[0].",".$tmpArr[1].",".$tmpArr2[1];
  }
}  
fclose($tmp_handle);

if($show_found_hits){
  $title = "BioGRID interactions".str_repeat(' ', 10);
}else{
  $title = "BioGRID interactions not found";
}
?>
<html>
<head>
  <title>Prohits</title>
  <link rel="stylesheet" type="text/css" href="./site_style.css"> 
  <script language="Javascript" src="site_javascript.js"></script>
  <script language="Javascript">
    function sortList(order_by){
      var theForm = document.grid_form;
      theForm.orderBy.value = order_by;
      theForm.submit();
    }
    function show_found(){
      var theForm = document.grid_form;
      theForm.submit(); 
    }
  </script>
 
</head>
<body>
<form name=grid_form action=<?php echo $PHP_SELF;?> method=post> 
<input type=hidden name=orderBy value=''>
<input type=hidden name=level1_matched_file value='<?php echo $level1_matched_file?>'>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
  <tr>
    <td colspan=4 nowrap>
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
        <td>
          <font color="navy" face="helvetica,arial,futura" size="3">
          <b><?php echo $title?></b>
          </font>
        </td>
        <td align="right">
          <input type=checkbox name=show_found_hits value='y' onclick="show_found();" <?php echo ($show_found_hits)?'checked':''?>>
          <font color="navy" face="helvetica,arial,futura" size="3">
          Show found hits
          </font>
        </td>
        </tr>
      </table>
    </td>
  </tr>  
	<tr bgcolor="<?php echo $bgcolordark_header;?>">
	  <td width="5" height="25" align=center>
    <div class=tableheader>Gene ID</div>
	  </td>
    <td width="70" align="center" > 
	  <div class=tableheader>Gene Name</div>
	  </td>
    <td width="70" align="center"> 
	  <div class=tableheader>Links</div>
	  </td>
	  <td width="25" align=center>
    <div class=tableheader>BioGRID Type</div> 
	  </td>
	</tr>
<?php 
foreach($baitInfoArr as $baitID => $baitInfoVal){
  if(!array_key_exists($baitID, $rootArr)) continue;
  $bait_hits_arr = $rootArr[$baitID];
  if(!$bait_hits_arr) continue;
  $tmpBaitArr = explode(",",$baitInfoVal);
  $baitUrlStr = get_URL_str('', $tmpBaitArr[1], '');
  $baitUrlStr = str_replace("<br>", "", $baitUrlStr);
  
?>
  <tr bgcolor="<?php echo $bgcolorsubdark_header;?>">
  <td colspan=4><div class=maintext>
    <b>Bait ID</b>:&nbsp;<?php echo $tmpBaitArr[0];?>&nbsp;&nbsp;
    <b>Bait Gene ID</b>:&nbsp;<?php echo $tmpBaitArr[1];?>&nbsp;&nbsp;<br>
    <b>Bait Gene Name</b>:&nbsp;<?php echo $tmpBaitArr[2];?>&nbsp;&nbsp;&nbsp;<?php echo $baitUrlStr?>
  </div></td>
  </tr>
<?php 
  foreach($bait_hits_arr as $hit_val){
    $tmpHitArr = explode(",",$hit_val);
    $tmpArr = explode('??',$tmpHitArr[1]);
    $tmpTypeArr = explode(":",$tmpHitArr[2]);
    $UrlStr = get_URL_str('', $tmpHitArr[3], '');
    $UrlStr = str_replace("<br>", "", $UrlStr);
    if($tmpHitArr[0]){
      $bgcolor = $bgcolordark;
    }else{  
      $bgcolor = $bgcolorlight;
    }  
?>  
  <tr  bgcolor='<?php echo $bgcolor;?>' onmousedown="highlightTR(this, 'click', '#CCFFCC', '<?php echo $bgcolor;?>');">
	  <td width="" align="center"><div class=maintext>
	      <?php echo $tmpHitArr[3]?>&nbsp;
	    </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $tmpArr[1]?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $UrlStr;?>&nbsp;
	      </div>
	  </td>
	      <?php echo get_bioGrid_icon($tmpTypeArr,$tmpType,'u');?>
	</tr>
<?php 
  }
} 
?>
</table>
</form> 
</body>
</html>
