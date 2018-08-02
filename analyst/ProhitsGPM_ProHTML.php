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

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("msManager/is_dir_file.inc.php");
ini_set("memory_limit","500M");

$field_spliter = ';;';
$hit = array();
$proLogI = array();
$proNum = array();
$proTotal = array();
$proCoverage = array();
$proExpect = array();
$proPI = array();
$proMr = array();
$proDesc = array();
$pepSpectrum = array();
$pepLogE = array();
$pepLogI = array();
$pepMass = array();
$pepDelta = array();
$pepZ = array();
$pepStart = array();
$pepSequence = array();
$pepEnd = array();
$pepModifications = array();
$pepIonfile = array();
$rank = array();
$pro_arr = array();
$pepStart_a = array();
$pepEnd_a = array();
$pepMod_a = array();
$nohead = "no";

//get parameters
$PARAM = array_merge($_GET, $_POST);
$userID = $PARAM['userID'];
$File = $PARAM['File'];
$pro = $PARAM['pro'];
if (isset($PARAM['proex'])){
  $proex = $PARAM['proex'];
}else{
  $proex = -1;
}
if (isset($PARAM['npep'])){
  $npep = $PARAM['npep'];
}else{
  $npep = 0;
}
if (isset($PARAM['nohead'])){
  $nohead = $PARAM['nohead'];
}else{
  $nohead = "No";
}  
if (isset($PARAM['show'])){  
  $show = $PARAM['show'];
}else{
  $show = 0;
}

$form = "/Prohits/analyst/ProhitsGPM_ProHTML.php?";
$form .= "userID=$userID&";
$form .= "File=$File&";
$form .= "pro=$pro";

$pro_arr = get_protein_from_url($pro);///common/common_fun.inc.php

if(isset($pro_arr['sequence']) and $pro_arr['sequence']){
  $proSequence = strtolower($pro_arr['sequence']);
  //echo $proSequence;
}else{
  echo "didn't get protein $pro sequence";exit;
}

//read temp file
read_parsed_tmpfile($File);

foreach ($hit as $key => $val) {
  if ($val==$pro){
    $index=$key;
  }
}

//get peptide matched seqence
$proPepMatchSequence = $proSequence;
for ($i = 0; $i < strlen($proPepMatchSequence); $i += 1){
  for ($j = 1; $j <= $proTotal[$index]; $j += 1){
    $s=preg_replace("/[^0-9]/","",$pepStart[$index][$j]);
    $e=preg_replace("/[^0-9]/","",$pepEnd[$index][$j]);
    for ($k = $s-1; $k < $e;$k += 1){
      $proPepMatchSequence[$k] = strtoupper($proPepMatchSequence[$k]);
      
    }
  }
}

function read_parsed_tmpfile($tmp_parsed_file){
  global $hit;
  global $proLogI;
  global $proNum;
  global $proTotal;
  global $proCoverage;
  global $proExpect;
  global $proPI;
  global $proMr;
  global $proDesc;
  global $pepSpectrum;
  global $pepLogE;
  global $pepLogI;
  global $pepMass;
  global $pepDelta;
  global $pepZ;
  global $pepStart;
  global $pepSequence;
  global $pepEnd;
  global $pepModifications;
  global $pepIonfile;
  global $title;
  global $field_spliter;
  
  $pNum = 0;
  if(!_is_file($tmp_parsed_file)){
    echo "the file ($tmp_parsed_file) doesn't exist";
    exit;
  }else{
    $fd = @fopen("$tmp_parsed_file","r");
    if(!$fd){
      $msg = "The $tmp_parsed_file file can not open.";
      fatal_Error($msg);
      exit;
    }else{
      while (!feof ($fd)) {
        $buffer = trim(fgets($fd, 40960));
        if(!$buffer)continue;
        //get protein info
        //HitNumber;;Identifier;;log(I)(sum of spectrum intensity);;num uniqe peptide;;rI(num peptide);;Coverage%;;log(e)(expect);;pI;;Mr(kDa);;Description
        if(preg_match("/^Hit_[0-9]+/", $buffer)){
          $pNum++;
          $tmp_array = explode($field_spliter, $buffer);
          $hit[$pNum] = $tmp_array[1];
          $proLogI[$pNum] = $tmp_array[2];
          $proNum[$pNum] = $tmp_array[3];
          $proTotal[$pNum] = $tmp_array[4];
          $proCoverage[$pNum] = $tmp_array[5];
          $proExpect[$pNum] = $tmp_array[6];
          $proPI[$pNum] = $tmp_array[7];
          $proMr[$pNum] = $tmp_array[8];
          $proDesc[$pNum] = $tmp_array[9];
          $buffer = trim(fgets($fd, 40960));
          for ($i = 1; $i <= $proTotal[$pNum]; $i += 1){
            //spectrum;;log(e)(expect);;log(I)(sum of intensity);;mh(pepetide mass);;delta;;z;;start;;sequence;;end;;modifications;;ionFile
            $tmp_array = explode($field_spliter, $buffer);
            $pepSpectrum[$pNum][$i] = $tmp_array[0];
            $pepLogE[$pNum][$i]     = $tmp_array[1];
            $pepLogI[$pNum][$i]     = $tmp_array[2];
            $pepMass[$pNum][$i]     = $tmp_array[3];
            $pepDelta[$pNum][$i]    = $tmp_array[4];
            $pepZ[$pNum][$i]        = $tmp_array[5];
            $pepStart[$pNum][$i]    = $tmp_array[6];
            $pepSequence[$pNum][$i] = $tmp_array[7];
            $pepEnd[$pNum][$i]      = $tmp_array[8];
            $pepModifications[$pNum][$i] = $tmp_array[9];
            $pepIonfile[$pNum][$i]  = $tmp_array[10];
            $buffer = trim(fgets($fd, 40960));
          }
        }
        //if(preg_match('/^output\,\stitle:\s*:(.*)/i', $buffer, $matches)) $title = trim($matches[1]);
      }
    }
  }
}

?>
<HTML>
<HEAD>
<TITLE>GPM - protein model: gi|<?php echo $pro?>|
</TITLE>
<link rel="stylesheet" href="/tandem/tandem-style.css" />
<SCRIPT LANGUAGE="JavaScript"> 

</SCRIPT>
</HEAD>
<BODY>
<?php 
if($nohead != "yes")	{
?>
<table>
  <tr>
    <td>
      <img src="./images/gpm.png" border="0">
    </td>
    <td>&nbsp;&nbsp;
    </td>
    <td valign="middle" width="600">
      <i>gi|<?php echo $pro?>|</i><BR>
      protein model: gi|<?php echo $pro?>|<BR><BR>
      <a href="/Prohits/analyst/ProhitsGPM_ParserHTML.php?userID=<?php echo $userID?>&File=<?php echo $File?>" class="small_link" title="Summary navigation page for this model">model</a> | 
      <BR>
    </td>
  </tr>
</table>
<?php 
}
?>
<table border="0" cellpadding="3" cellspacing="3">
  <tr>
    <td align="right">
      <i>Peptide clustering:</i>
    </td>
    <td align="left">
    <?php 
    if($show == 0)	{
    ?>
      <b>unique</b>&nbsp;|&nbsp;
      <a href="<?php echo $form?>&show=2&nohead=<?php echo $nohead?>" title="show all modified peptides">modified</a>&nbsp;|&nbsp;
      <a href="<?php echo $form?>&show=1&nohead=<?php echo $nohead?>" title="show all peptides">none</a>
    <?php 
    }else if($show == 2)	{
	  ?>
      <a href="<?php echo $form?>&show=0&nohead=<?php echo $nohead?>" title="show unique peptides only">unique</a>&nbsp;|&nbsp;
      <b>modified</b>&nbsp;|&nbsp;
      <a href="<?php echo $form?>&show=1&nohead=<?php echo $nohead?>" title="show all peptides">none</a>
    <?php 
    }else if($show == 1)	{
    ?>
	    <a href="<?php echo $form?>&show=0&nohead=<?php echo $nohead?>" title="show all peptides only">unique</a>&nbsp;|&nbsp;
      <a href="<?php echo $form?>&show=2&nohead=<?php echo $nohead?>" title="show all modified peptides">modified</a>&nbsp;|&nbsp;
      <b>none</b>
    <?php 
    }
    ?>
    </td>
    <td align="right">
      <i>Display:</i>
    </td>
    <td align="left">&nbsp;
    <?php 
    if($nohead == "yes")	{
    ?>
	    <b>table</b>&nbsp;|&nbsp;
      <a href="<?php echo $form?>&show=$show&nohead=no" title="HTML">html</a>&nbsp;|&nbsp;
    <?php 
    } else {
    ?>
	    <a href="<?php echo $form?>&show=$show&nohead=yes" title="spreadsheet compatible">table</a>&nbsp;|&nbsp;
      <b>html</b>&nbsp;|&nbsp;
    <?php 
    }
    ?>
    </td>
  </tr>
</table>
<?php 
if($nohead != "yes")	{
?>
<table width="600" cellpadding="3" cellspacing="0">
  <tr>
    <td width="150" valign="top" align="right" bgcolor="#FFFFAA">
      <b>gi|<?php echo $pro?>|:</b><br>
      <b>log(e) = <?php echo $proExpect[$index]?>&nbsp;</b>
    </td>
    <td valign="top" align="left" bgcolor="#FFFFAA">
	  </td>
  </tr>
</table>
<br>
<table cellpadding="0" cellspacing="0">
<?php 
  $a = 0;
	$f = 1;
  $bold = 0;
  while($a < strlen($proPepMatchSequence))	{
    ?>
	<tr>
		<td valign="top" align="right" width="50pt" style="font-family:Courier New,Courier,monospace;font-size:10pt;" >
      &nbsp;<?php echo $f?>&nbsp;
    </td>
    <td style="font-family:Courier New,Courier,monospace;font-size:11pt;" >
<?php 
    if (!($a%60)){
      if($bold){
        echo "<B><FONT COLOR=#FF0000>";
      }
      for ($i = $a; $i < $a+60; $i += 1){
        if($i<strlen($proPepMatchSequence)){
          if((preg_match("/[A-Z]/", $proPepMatchSequence[$i])) and !$bold){
            echo "<B><FONT COLOR=#FF0000>";
            $bold = 1;
          } elseif ((preg_match("/[a-z]/",$proPepMatchSequence[$i])) and $bold){
            echo "</FONT></B>";
            $bold = 0;
          }
          echo strtoupper($proPepMatchSequence[$i]);
        }
      }
    } 
?>
    </td>
<?php     
		$f += 59;
		if($f < strlen($proPepMatchSequence))	{
    ?>
		<td valign="top" align="left" width="50pt" style="font-family:Courier New,Courier,monospace;font-size:10pt;" >
      &nbsp;<?php echo $f?>
    </td>
    <?php 
		}	else	{
    ?>
		<td valign="top" align="left" width="50pt" style="font-family:Courier New,Courier,monospace;font-size:10pt;" >
    &nbsp;<?php echo strlen($proPepMatchSequence)?>
    </td>
    <?php 
		}
    ?>
		</td>
  </tr>
    <?php 
    
		$f++;
		$a+=60;
	}
  ?>
</table>
<br>
<?php 
}
?>
<table cellspacing="3" cellpadding="2">
  <tr>
    <td align="center"><b>spectrum</b></td>
    <td align="center"><b>log(e)</b></td>
    <td align="center"><b>log(I)</b></td>
    <td align="center"><b>m+h</b></td>
    <td align="center"><b>delta</b></td>
    <td align="center"><b>z</b></td>
<?php 
if($nohead != "yes")	{
?>
	  <td colspan="2" align="left"><b>&nbsp;sequence</b></td>
<?php 
} else	{
?>
	  <td colspan="3" align="center"><b>&nbsp;sequence</b></td>
	  <td align="left"><b>&nbsp;modifications</b></td>
<?php 
}
?>
  </tr>
<?php 
$last="";
if($show==2){
  for ($i = 1; $i <= $proTotal[$index]; $i += 1){
    if ($pepModifications[$index][$i]!=""){
?>
  <tr>
		<td align="center"><?php echo $pepSpectrum[$index][$i]?></td>
    <td align="center"><?php echo $pepLogE[$index][$i]?></td>
    <td align="center"><?php echo $pepLogI[$index][$i]?></td>
    <td align="center"><?php echo $pepMass[$index][$i]?></td>
    <td align="center"><?php echo $pepDelta[$index][$i]?></td>
    <td align="center"><?php echo $pepZ[$index][$i]?></td>
<?php     
	    $pepStart_a=explode('-', $pepStart[$index][$i]);
      $pepEnd_a=explode('-', $pepEnd[$index][$i]);
      if ($pepModifications[$index][$i]!=""){
        $pepMod_a=explode(' ', $pepModifications[$index][$i]);
        $pos=preg_replace("/[^0-9]/","",$pepMod_a[1]);
        $diff=$pos-$pepStart_a[1];
        $seq=substr($pepSequence[$index][$i], 0, $diff)."<span title='".substr($pepMod_a[2],0,-1)."' style='color: white;background-color: blue' >".$pepMod_a[0]."</span>".substr($pepSequence[$index][$i],$diff+1);
      }else{
        $seq=$pepSequence[$index][$i];
      }  
      if($nohead != "yes")	{
?>
		<td align="right" valign="top"><?php echo $pepStart_a[0]?><sup><?php echo $pepStart_a[1]?></sup></td>
		<td align="left"><?php echo $seq?>&nbsp;&nbsp;<sup><?php echo $pepEnd_a[0]?></sup><?php echo $pepEnd_a[1]?></td>
<?php 		
      }	else {
?>
		<td align="right"><?php echo $pepStart[$index][$i]?></td>
		<td align="left"><?php echo $seq?></td>
		<td align="left"><?php echo $pepEnd[$index][$i]?></td>
		<td align="left"><?php echo substr($pepModifications[$index][$i],0,-1)?></td>
<?php 
	    }
    }
?>
	</tr>
<?php 
  }
}else{
  for ($i = 1; $i <= $proTotal[$index]; $i += 1){
    if($pepSequence[$index][$i] != $last or $show == 1) { 
?>
  <tr>
		<td align="center"><?php echo $pepSpectrum[$index][$i]?></td>
    <td align="center"><?php echo $pepLogE[$index][$i]?></td>
    <td align="center"><?php echo $pepLogI[$index][$i]?></td>
    <td align="center"><?php echo $pepMass[$index][$i]?></td>
    <td align="center"><?php echo $pepDelta[$index][$i]?></td>
    <td align="center"><?php echo $pepZ[$index][$i]?></td>
<?php     
	    $pepStart_a=explode('-', $pepStart[$index][$i]);
      $pepEnd_a=explode('-', $pepEnd[$index][$i]);
      if ($pepModifications[$index][$i]!=""){
        $pepMod_a=explode(' ', $pepModifications[$index][$i]);
        $pos=preg_replace("/[^0-9]/","",$pepMod_a[1]);
        $diff=$pos-$pepStart_a[1];
        $seq=substr($pepSequence[$index][$i], 0, $diff)."<span title='".substr($pepMod_a[2],0,-1)."' style='color: white;background-color: blue' >".$pepMod_a[0]."</span>".substr($pepSequence[$index][$i],$diff+1);
      }else{
        $seq=$pepSequence[$index][$i];
      }  
      if($nohead != "yes")	{
?>
		<td align="right" valign="top"><?php echo $pepStart_a[0]?><sup><?php echo $pepStart_a[1]?></sup></td>
		<td align="left"><?php echo $seq?>&nbsp;&nbsp;<sup><?php echo $pepEnd_a[0]?></sup><?php echo $pepEnd_a[1]?></td>
<?php 		
      }	else {
?>
		<td align="right"><?php echo $pepStart[$index][$i]?></td>
		<td align="left"><?php echo $seq?></td>
		<td align="left"><?php echo $pepEnd[$index][$i]?></td>
		<td align="left"><?php echo substr($pepModifications[$index][$i],0,-1)?></td>
<?php 
	    }
    }
?>
	</tr>
<?php 
    $last = $pepSequence[$index][$i];
  }
}
?>	
</table>
<BR><BR>
</BODY>
</HTML>
