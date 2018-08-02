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

//get parameters
$PARAM = array_merge($_GET, $_POST);
$userID = $PARAM['userID'];
$File = $PARAM['File'];
if(sizeof($PARAM)>2){
  $proex = $PARAM['proex'];
  $npep = $PARAM['npep'];
  $nohead = $PARAM['nohead'];
  $order = $PARAM['order'];
}else{
  $proex = -1;
  $npep = 0;
  $nohead = "No";
  $order = "e";
}

//read temp file
read_parsed_tmpfile($File);

$clink = "/Prohits/analyst/ProhitsGPM_ParserHTML.php?userID=".$userID."&File=".$File."&amp;proex=".$proex."&amp;npep=".$npep."&amp;nohead=".$nohead;
$rlink = "/Prohits/analyst/ProhitsGPM_ParserHTML.php?userID=".$userID."&File=".$File."&amp;proex=".$proex."&amp;npep=".$npep."&amp;nohead=no&amp;order=".$order;

if($nohead != "yes")	{
	$rlink = "";
}

if($order == "e")	{
	asort($proExpect);
  $rank=$proExpect;
}else if($order == "m")	{
	arsort($proMr);
  $rank=$proMr;
}else if($order == "i")	{
	arsort($proLogI);
  $rank=$proLogI;
}else if($order == "c")	{
	arsort($proCoverage);
  $rank=$proCoverage;
}else if($order == "t")	{
	arsort($proTotal);
  $rank=$proTotal;
}else if($order == "u")	{
	arsort($proNum);
  $rank=$proNum;
}else if($order == "E")	{
	arsort($proExpect);
  $rank=$proExpect;
}else if($order == "M")	{
	asort($proMr);
  $rank=$proMr;
}else if($order == "I")	{
	asort($proLogI);
  $rank=$proLogI;
}else if($order == "C")	{
	asort($proCoverage);
  $rank=$proCoverage;
}else if($order == "T")	{
	asort($proTotal);
  $rank=$proTotal;
}else if($order == "U")	{
	asort($proNum);
  $rank=$proNum;
}else	{
	asort($proExpect);
  $rank=$proExpect;
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
<TITLE>GPM - Models from '-data-www-thegpm-gpm-archive-LCQ-36417_D05_12204.mzXML'
</TITLE>
<link rel="stylesheet" href="/tandem/tandem-style.css" />
<link rel="stylesheet" href="/tandem/tandem-style-print.css" media="print"/>
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
      <i>Models from '<?php echo $File?>'</i><BR>
      Main model display<BR><BR>
      <a href="/Prohits/analyst/ProhitsGPM_ParserHTML.php?userID=<?php echo $userID?>&File=<?php echo $File?>" class="small_link" title="Summary navigation page for this model">model</a> | 
      <BR>
    </td>
  </tr>
</table>

<div  style="display:none">
  <form name="display_form" action=<?php echo $_SERVER['PHP_SELF'];?> method="POST">
    log(e) &lt; <input class="small_value" size="2" name="proex" value="<?php echo $proex;?>"/>
    and # &gt; <input class="small_value" size="2" name="npep" value="<?php echo $npep;?>"/>
    and no headers <input class="small_value" value="yes" name="nohead">
    <input type="hidden" value="<?php echo $order;?>" name="order">
    <input type=hidden name='userID' value='<?php echo $userID;?>'>
    <input type=hidden name='File' value='<?php echo $File;?>'>
  </form>
</div>
<BR>
<table cellspacing="2">
  <tr>
    <td valign="middle">
      <i>Display:</i>
    </td>
    <td valign="middle">
      log(e) &lt; 
    </td>
    <td valign="middle">
      <input class="small_value" size="2" onChange="display_form.proex.value=this.value;" value="<?php echo $proex;?>"/>
    </td>
    <td valign="middle">
      and # &gt; 
    </td>
    <td valign="middle">
      <input class="small_value" size="2" onChange="display_form.npep.value=this.value;" value="<?php echo $npep;?>"/>
    </td>
    <td valign="middle">
      , show as | 
      <a onClick="display_form.nohead.value='yes';display_form.submit();">table</a> |
      <a onClick="display_form.nohead.value='no';display_form.submit();">html</a> |
    </td>
  </tr>
</table>
<?php 
}
?>
<table cellpadding="1" cellspacing="2" width="678">
  <tr>
    <td valign="bottom" align="right" width="40" title="Ordinal rank of the protein, by expectation value">
      <b>rank</b>
    </td>
    <?php 
    if($order == "e" or strlen($order)== 0)	{
    ?>
    <td valign="bottom" align="center" width="50" title="Base-10 log of the expectation that this assignment is stochastic">
      <a href="<?php echo $clink;?>&order=E"><b>log(e)<sup>+</sup></b></a>
    </td>
    <?php 
    }elseif($order == "E")	{
    ?>
    <td valign="bottom" align="center" width="50" title="Base-10 log of the expectation that this assignment is stochastic">
      <a href="<?php echo $clink;?>&order=e"><b>log(e)<sup>-</sup></b></a>
    </td>
    <?php 
    }	else	{
    ?>
    <td valign="bottom" align="center" width="50" title="Base-10 log of the expectation that this assignment is stochastic">
      <a href="<?php echo $clink;?>&order=e"><b>log(e)</b></a>
    </td>
    <?php 
    }
    if($order == "i")	{
    ?>
    <td valign="bottom" align="center" width="50" title="Base-10 log of the sum of the intensities of the fragment ion spectra">
      <a href="<?php echo $clink;?>&order=I"><b>log(I)<sup>+</sup></b></a>
    </td>
    <?php 
    }else if($order == "I")	{
    ?>
    <td valign="bottom" align="center" width="50" title="Base-10 log of the sum of the intensities of the fragment ion spectra">
      <a href="<?php echo $clink;?>&order=i"><b>log(I)<sup>-</sup></b></a>
    </td>
    <?php 
    }else	{
    ?>
    <td valign="bottom" align="center" width="50" title="Base-10 log of the sum of the intensities of the fragment ion spectra">
      <a href="<?php echo $clink;?>&order=i"><b>log(I)</b></a>
    </td>
    <?php 
    }
    if($order == "c")	{
    ?>
    <td valign="bottom" align="center" width="35" title="Protein coverage: percent fraction of protein residues in identified peptides">
      <a href="<?php echo $clink;?>&order=C"><b>%<sup>+</sup></b></a>
    </td>
    <?php 
    }else if($order == "C")	{
    ?>
    <td valign="bottom" align="center" width="35" title="Protein coverage: percent fraction of protein residues in identified peptides">
      <a href="<?php echo $clink;?>&order=c"><b>%<sup>-</sup></b></a>
    </td>
    <?php 
    }else	{
    ?>
    <td valign="bottom" align="center" width="35" title="Protein coverage: percent fraction of protein residues in identified peptides">
      <a href="<?php echo $clink;?>&order=c"><b>%</b></a>
    </td>
    <?php 
    }
    if($order == "u")	{
    ?>
    <td valign="bottom" align="center" width="35" title="Number of unique peptides found">
      <a href="<?php echo $clink;?>&order=U"><b>#<sup>+</sup></b></a>
    </td>
    <?php 
    }else if($order == "U")	{
    ?>
    <td valign="bottom" align="center" width="35" title="Number of unique peptides found">
      <a href="<?php echo $clink;?>&order=u"><b>#<sup>-</sup></b></a>
    </td>
    <?php 
    }else	{
    ?>
    <td valign="bottom" align="center" width="35" title="Number of unique peptides found">
      <a href="<?php echo $clink;?>&order=u"><b>#</b></a>
    </td>
    <?php 
    }
    if($order == "t")	{
    ?>
    <td valign="bottom" align="center" width="35" title="Total number of peptides found from this sequence only">
      <a href="<?php echo $clink;?>&order=T"><b>total<sup>+</sup></b></a>
    </td>
    <?php 
    }else if($order == "T")	{
    ?>
    <td valign="bottom" align="center" width="35" title="Total number of peptides found from this sequence only">
      <a href="<?php echo $clink;?>&order=t"><b>total<sup>-</sup></b></a>
    </td>
    <?php 
    }else	{
    ?>
    <td valign="bottom" align="center" width="35" title="Total number of peptides found from this sequence only">
      <a href="<?php echo $clink;?>&order=t"><b>total</b></a>
    </td>
    <?php 
    }
    if($order == "m")	{
    ?>
    <td valign="bottom" align="center" width="35" title="Protein molecular mass, in kilodaltons">
      <a href="<?php echo $clink;?>&order=M"><b>Mr<sup>+</sup></a>
    </td>
    <?php 
    }else if($order == "M")	{
    ?>
    <td valign="bottom" align="center" width="35" title="Protein molecular mass, in kilodaltons">
      <a href="<?php echo $clink;?>&order=m"><b>Mr<sup>-</sup></a>
    </td>
    <?php 
    }else	{
    ?>
    <td valign="bottom" align="center" width="35" title="Protein molecular mass, in kilodaltons">
      <a href="<?php echo $clink;?>&order=m"><b>Mr</a>
    </td>
    <?php 
    }
    if(strlen($rlink) == 0)	{
    ?>
    <td valign="bottom" align="left" width="366" title="Identifying accession number for the protein: (H) indicates a protein is a homolog">
      <b>accession</b>
    </td>
    <?php 
    }else	{
    ?>
    <td valign="bottom" align="left" width="366">
      <a href="<?php echo $rlink?>" title="show full page with headers"><b>accession</b></a>
    </td>
    <?php 
    }
    ?>
  </tr>
  <?php 
  $r=1;
  foreach ($rank as $key => $val) {
    if($proExpect[$key]<$proex and $proNum[$key]>$npep){
  ?>
  <tr>
    <td align="right" valign="top" width="40">
      <?php echo $r?>&nbsp;
    </td>
    <td align="center" valign="top" width="50">
      <?php echo $proExpect[$key]?>
    </td>
    <td align="center" valign="top" width="50">
      <?php echo $proLogI[$key]?>
    </td>
    <td align="center" valign="top" width="35">
      <?php echo $proCoverage[$key]?>
    </td>
    <td align="center" valign="top" width="35">
      <?php echo $proNum[$key]?>
    </td>
    <td align="center" valign="top" width="35">
      <?php echo $proTotal[$key]?>
    </td>
    <td align="center" valign="top" width="35">
      <?php echo $proMr[$key]?>
    </td>
    <td align="left" valign="top" width="366">
      <?php echo $hit[$key]?>|&nbsp;&nbsp;
      <?php 
      if($nohead != "yes")	{
      ?>
      <a href="/Prohits/analyst/ProhitsGPM_ProHTML.php?userID=<?php echo $userID?>&File=<?php echo $File;?>&pro=<?php echo $hit[$key];?>" title="View the protein sequence, identified peptides and additional information">protein</a>
      <?php 
      }
      ?>
    </td>
  </tr>
  <?php 
    $r=$r+1;
    }
    
  }
  ?>
</table>
<BR><BR>
</BODY>
</HTML>
