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


$aIons = array();
$a2Ions = array();
$asIons = array();
$as2Ions = array();
$a0Ions = array();
$a02Ions = array();
$bIons = array();
$b2Ions = array();
$bsIons = array();
$bs2Ions = array();
$b0Ions = array();
$b02Ions = array();
$cIons = array();
$c2Ions = array();
$immIons = array();
$intyaIons = array();
$intybIons = array();
$xIons = array();
$x2Ions = array();
$yIons = array();
$y2Ions = array();
$ysIons = array();
$ys2Ions = array();
$y0Ions = array();
$y02Ions = array();
$zIons = array();
$z2Ions = array();
$configFile = array();    // holds mascot.dat
$delta_masses = array();
$exp_masses = array();
$fields = array();        // holds entire result file
$ignoreMass = array();
$intensityList = array();
$intyaIonsByColumn = array();
$intybIonsByColumn = array();
$intybLabels = array();
$labelsByColumn = array();
$massList = array();
$pepFields = array();
$pepMatches = array();
$protTemp = array();
$residues = array();
$runningSum = array();
$summer = array();
$temp_masses = array();
$typeList = array();
//------------------------------------------------------------------
$include = array();     //******ok
$indexArr = array();    //******ok     // keys and values from index block
$labelList = array();   //******ok
$labels = array();
$masses = array();      //******ok  // keys and values from masses block
$neutralLoss = array(); //******ok
$parameters = array();  //******ok # keys and values from parameters block
$peptides = array();    //******ok
$queryArr = array();    //******ok
$seriesSig = array();   //******ok
$summary = array();     //******ok  // keys and values from summary block
$vmMass = array();      //******ok
$vmString = array();    //******ok  // mass deltas for variable mods 
//-----------------------------------------------------------------
$debug = '';
$displayRange = '';
$encoded = '';
$fieldCode = '';
$fileIn = '';        // result file path passed as URL argument 'file'
$firstTick = ''; 
$frameNum = '';
$hitNum = '';        // hit number
$i = '';             // general purpose loop variable 
$indexNum = '';
$indexSave = '';
$ionsData = '';      // flag set if ions data present
$ionsDP = '';
$j = '';
$lastTick = '';
$leftMargin = '';
$massDP = '';        // number of decimal places in displayed mass values
$massMax = '';
$massMin = '';
$matchList = '';
$newMass = '';
$numCalcVals = '';
$numRes = '';
$overallHeight = '';
$overallWidth = '';
$parseRule = '';
$parseString = '';
$peaks = '';
$peptide = '';
$px = '';
$queryNum = '';      // query number
$realMatch = '';
$rightMargin = '';
$scoop = '';
$seqReport = '';
$shipper = '';
$temp = '';
$tempString = '';
$thisScript = '';    // CGI object
$tickInterval = '';   
$title = '';
$tmpLeft = '';
$tmpMatch = '';
$tmpRight = '';
$topMargin = ''; 
$xClick = '';
$xScale = '';
$accession = '';
$argString = '';
$blockName = '';     // name of next block to be unpacked from result file
$bottomMargin = '';
$boundary = '';      // MIME boundary string
$charge = '';
//------------------------------------------------
$tick1 = '';  
$tick_int = '';   
$range = '';   
$from = '';
$to = '';
//$gif.x = ''; //########################
$width = '';  
$height = '';
$left = '';          
$right = '';      
$top = '';          
$bottom = '';
//------------------------------------------------

require("../common/site_permission.inc.php");
/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/


$start_time = @date("F j, Y, g:i a");                 
$file?$fileIn=$file:fatalError("no file name", __LINE__);
$query?$queryNum=$query:fatalError("no query number", __LINE__);
$hit?$hitNum=$hit:fatalError("no hit number", __LINE__);
/*
echo "<br>";
echo '$queryNum='.$queryNum;
echo "<br>";
echo '$hitNum='.$hitNum;
echo "<br>";
*/

if(!($firstTick = $tick1)) $firstTick = 1;  
if(!($tickInterval = $tick_int)) $tickInterval = 1;    
if(!($displayRange = $range)) $displayRange = 10 ;    
if(!($massMin = $from)) $massMin = -1 ;
if(!($massMax = $to)) $massMax = 1e99 ;
//if(!($xClick = $gif.x)) $xClick = -1;
$xClick = -1;
if(!$scoop) $scoop = 2;                
if(!($overallWidth = $width)) $overallWidth = 550;    
if(!($overallHeight = $height)) $overallHeight = 300;
if(!($leftMargin = $left)) $leftMargin = 20;          
if(!($rightMargin = $right)) $rightMargin = 20;      
if(!($topMargin = $top)) $topMargin = 100;          
if(!($bottomMargin = $bottom)) $bottomMargin = 10;     
if(!$debug) $debug = 'FALSE';

// get mass precision parameters from mascot.dat
// default to 2 decimal places for peptides, 1 for fragment ions
$configFileArr = file("mascot.dat");
if(!$configFileArr) fatalError("cannot close mascot.dat", __LINE__);
/*
echo "<pre>";
print_r($configFileArr);
echo "</pre>";
*/
$i=0;
foreach($configFileArr as $value){
  if(preg_match('/^MassDecimalPlaces\s*(\d+)/', $value, $matches)){
    $massDP = $matches[1];
//echo "<br>MassDecimalPlaces ";
//echo $massDP;    
    $i++;
  }else if(preg_match('/^IonsDecimalPlaces\s*(\d+)/', $value, $matches)){
    $ionsDP = $matches[1];
//echo "<br>IonsDecimalPlaces ";
//echo $ionsDP;
    $i++;
  }
  if($i ==2) break;
}
if($massDP < 1 || $massDP > 5) {
  $massDP = 2;
}
if($ionsDP < 1 || $ionsDP > 5) {
  $ionsDP = 1;
}

// dump entire data file into array
$fields = file($fileIn);
if(!$fields) fatalError("cannot open $fileIn", __LINE__);
/*echo "<pre>";
print_r($fields);
echo "</pre>";*/

$boundary = '';
getIndex() || fatalError("could not get index from $fileIn", __LINE__);

/*echo "<pre>";
print_r($indexArr);
echo "</pre>";*/
//echo "\$indexArr = ". count($indexArr);
$blockName = "parameters";
unBlock($blockName,$parameters) ||  fatalError("could not unpack $blockName from $fileIn", __LINE__);
/*
echo "<pre>";
print_r($parameters);
echo "</pre>";
*/
//echo "<br>\$parameters = ". count($parameters);

$blockName="masses";
unBlock($blockName,$masses) ||  fatalError("could not unpack $blockName from $fileIn", __LINE__);

/*echo "<pre>";
print_r($masses);
echo "</pre>";*/
//echo "<br>\$masses = ". count($masses);

$blockName= "query" . $queryNum;
unBlock($blockName, $queryArr, $massList, $intensityList, $typeList) || fatalError("could not unpack $blockName from $fileIn", __LINE__);

//echo "<pre>";
//print_r($queryArr);
//echo "<br>\$queryArr = ". count($queryArr);
//print_r($massList);
//echo "<br>\$massList = ". count($massList);
//print_r($intensityList);
//echo "<br>\$intensityList = ". count($intensityList);
//print_r($typeList);
//echo "<br>\$intensityList = ". count($intensityList);
//echo "</pre>";

$blockName="summary";
unBlock($blockName, $summary) || fatalError("could not unpack $blockName from $fileIn", __LINE__);
/*
echo "<pre>";
print_r($summary);
echo "</pre>";
*/
//echo "<br>\$summary = ". count($summary);
//echo "<br>";
//echo $massList[0]."**********************";
if(isset($massList[0])){
  $ionsData = 1;
} else {
  $ionsData = 0;
}

if($px){  // called from peptide summary&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  $indexSave = $index;
  $indexNum = $indexSave;
  // accession string may contain meta characters
  $indexNum = preg_replace('/(\W)/', '\\\\\1', $indexNum);
  //echo $indexNum;
  $blockName="peptides";
  unBlock($blockName,$peptides) || fatalError("could not unpack $blockName from $fileIn", __LINE__);
  /*
  echo "<pre>";
  print_r($peptides);
  echo "</pre>";
  */
//echo "<br>\$peptides = ". count($peptides);
  if(isset($peptides["q".$queryNum."_p".$hitNum]) && $peptides["q".$queryNum."_p".$hitNum]){
    $realMatch = 1;
    $tmpRight = trim($peptides{"q".$queryNum."_p".$hitNum});
    
//echo "<br>\$peptides[q".$queryNum."_p".$hitNum."]=";
//echo $tmpRight;
  }else{
    $realMatch = 0;
  }
    
  if(!$realMatch || $tmpRight == "-1"){
  // no match, just echo picture
    $realMatch = 0;
    $accession = "zilch";
  }else{    
    if(!$indexNum){
      // if no index supplied, use the first accession string listed
      if(preg_match('/;\"?(.+?)\"?:/', $tmpRight, $matches)){
        $tmpRight = $matches[0];
        $indexSave = $matches[1];
        $indexNum = $indexSave;
        $indexNum = preg_replace('/(\W)/', '\\\\\1', $indexNum);
/*        
echo "<br>";
echo "indexNum=" . $indexNum;
echo "<br>";
echo "tmpRight=" . $tmpRight;
*/
      }  
    }
//echo "<br>";
//echo $tmpRight;    
    if(preg_match('/\"?'.$indexNum.'\"?:(\d+?):(\d+?):(\d+?):(\d+?)/', $tmpRight, $matches)){    
      $frameNum = $matches[1];
      $summer[3] = $matches[2];
      $summer[4] = $matches[3];
      $summer[10] = $matches[4];
/*       
echo "<br>##";
echo $frameNum;        
echo "<pre>"; 
print_r($summer);
echo "</pre>";
*/
      $tmpArr = explode(';', $tmpRight);
      $tmpRight = $tmpArr[0];
//echo "<br>##";
//echo $tmpRight;         
      $pepFields = explode(',',$tmpRight);
/*      
echo  "<pre>"; 
print_r($pepFields);
echo  "</pre>";
*/

      $i = $indexArr['proteins'];   // offset of first line of block
//echo "<br>###";
//echo $i;      
      if($i<4) return 0;         // lowest offset cannot be <4
      $i++;
      $i++;                      // skip blank line
      while(!preg_match("/^".$boundary."/", $fields[$i])){
        if(preg_match("/^\"?".$indexNum."\"?=/", $fields[$i])){
//echo "<br>******";
//echo $fields[$i];
          break;
        }
        $i++;
      }
      if(preg_match("/^".$boundary."/", $fields[$i])){
        $seqReport = "";
        $tmpLeft = $indexSave;
        getItem($tmpLeft, "title");
      }else{
        $fields[$i] = trim($fields[$i]);
        $tmpArr = explode('=', $fields[$i], 2);
/*echo "<br>left= ";
echo $tmpArr[0];
echo "<br>right= ";
echo $tmpArr[1];*/
      }
      $protTemp = (split(',' ,$tmpArr[1],2));
      //$protTemp[1] =~ s/\"(.*)\"/$1/;
      $accession=$tmpArr[0];
      $title=$protTemp[1];
//echo "<br>\$accession=";        
//echo $accession;  
//echo "<br>\$title=";        
//echo $title;              
      $summer[0] = $pepFields[0];
      $summer[1] = $pepFields[1];
      $summer[2] = $pepFields[2];
      $summer[5] = $pepFields[3];
      $summer[6] = $pepFields[4];
      $summer[7] = $pepFields[5];
      $summer[8] = $pepFields[6];
      $summer[9] = $pepFields[7];
      if(isset($pepFields[8]) && strlen($pepFields[8]) > 8){
        $summer[11]=$pepFields[8];
        if(isset($pepFields[9])){
          $summer[12]=$pepFields[9];
        }
        if (isset($pepFields[10])){
          $summer[13]=$pepFields[10];
        }
      }
//echo "<pre>";
//echo '$summer=';
//echo "<br>";
ksort($summer);
//print_r($summer);
//echo "</pre>";
    } else {
      fatalError("cannot find $indexSave in results file", __LINE__);
    }
  }
}else{
  # called from protein summary&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  # assume a real match
  $realMatch = 1;

  # retrieve accession number and title
  if(preg_match('/^(.*),(.*?),(.*?),(.*?)$/', $summary["h"."$hitNum"], $matches)){
    $accession = $matches[1];
  }else{
    fatalError("cannot find accession in array \$summary", __LINE__);
  }
  $title = $summary["h".$hitNum."_text"];
  if(isset($summary["h".$hitNum."_frame"]) && $summary["h".$hitNum."_frame"]){
    $frameNum = $summary["h".$hitNum."_frame"];
  }else{
    $frameNum = 0;
  }
  // split out the hit_query line from summary
  $summer = explode(',', $summary["h".$hitNum."_q".$queryNum]);
  
//echo "<br>\$frameNum=$frameNum";
//echo "<br>\$accession=$accession";
//echo "<br>\$title=$title";
//echo "<pre>";
//echo "<br>";
//print_r($summer);
//echo "</pre>";  
}

//retrieve precursor charge
#echo "<br>";
#echo "qexp".$queryNum;
$tmpArr = explode(',',$summary["qexp".$queryNum]);
#echo "<br>";
#echo $summary{"qexp".$queryNum};
if(preg_match('/Mr/', $tmpArr[1])){
  $charge = 0;
}elseif(preg_match('/(\d)\+/', $tmpArr[1], $matches)){
  $charge = $matches[1];
}
//echo "<br>\$charge=";
//echo $charge;
/**********************************************************************************************
 version X scoring scheme required ion series to be specified explicitly
 version Y scoring scheme iterated 6 ion series: a, a*, b, b*, y, y* 
 plus  a++, b++, y++ if precursor charge was 2 or more
 version Z scoring scheme iterates ion series specified by INSTRUMENT entry from fragmentation_rules file
 both versions Y and Z record scoring by ion series as "bit pattern" in $summer[11]
   0 means matches not significant
   1 means matches not chosen to contribute to score
   2 means matches contribute to score
*********************************************************************************************/
if(isset($summer[11]) && $summer[11]){
# version Z
# debug
#    if (length($summer[11]) == 15) {
# debug
  if(strlen($summer[11]) > 9) {
//echo "<br>";
//echo $summer[11];
    $tmpArr = str_split($summer[11]);

# debug
    list($seriesSig['iatol'],
        $seriesSig['iastol'],
        $seriesSig['ia2tol'],
        $seriesSig['ibtol'],
        $seriesSig['ibstol'],
        $seriesSig['ib2tol'],
        $seriesSig['iytol'],
        $seriesSig['iystol'],
        $seriesSig['iy2tol'],
        $seriesSig['ictol'],
        $seriesSig['ic2tol'],
        $seriesSig['ixtol'],
        $seriesSig['ix2tol'],
        $seriesSig['iztol'],
        $seriesSig['iz2tol']) = $tmpArr;
/*        
echo "<pre>";
print_r($seriesSig);
echo "</pre>";
*/
    /*
    unset($seriesSig['iastol']);
    unset($seriesSig['ibstol']);
    unset($seriesSig['iystol']);
    */
    $seriesSig['iastol'] = '';
    $seriesSig['ibstol'] = '';
    $seriesSig['iystol'] = '';
/*    
echo "<pre>";
print_r($seriesSig);
echo "</pre>";
*/
    getRules();
  }elseif(strlen($summer[11]) == 9) {
  # version Y
    $tmpArr = str_split($summer[11]);
    list( $seriesSig{'iatol'},
      $seriesSig['iastol'],
      $seriesSig['ia2tol'],
      $seriesSig['ibtol'],
      $seriesSig['ibstol'],
      $seriesSig['ib2tol'],
      $seriesSig['iytol'],
      $seriesSig['iystol'],
      $seriesSig['iy2tol'] ) = $tmpArr;
    $include['aIons'] = 1;
    $include['asIons'] = 1;
    $include['bIons'] = 1;
    $include['bsIons'] = 1;
    $include['yIons'] = 1;
    $include['ysIons'] = 1;
    if ($charge > 1){
      $include['a2Ions'] = 1;
      $include['b2Ions'] = 1;
      $include['y2Ions'] = 1;
    }
    $include['immIons'] = 1;
  }else{
  # version X
    if ($parameters['iatol'] > 0) $include['aIons'] = 1;
    if ($parameters['iastol'] > 0) $include['asIons'] = 1;
    if ($parameters['ibtol'] > 0) $include['bIons'] = 1;
    if ($parameters['ibstol'] > 0) $include['bsIons'] = 1;
    if ($parameters['iytol'] > 0) $include['yIons'] = 1;
    if ($parameters['iystol'] > 0) $include['ysIons'] = 1;
    if ($charge > 1) {
      if ($parameters['ia2tol'] > 0) $include['a2Ions'] = 1;
      if ($parameters['ib2tol'] > 0) $include['b2Ions'] = 1;
      if ($parameters['iy2tol'] > 0) $include['y2Ions'] = 1;
    }
  }
}
/*
echo "<pre>\$include ";
print_r($include);
echo "</pre>";
*/
//------------------------------------------

if($realMatch){
  $peaks = $parameters['peak'];
#echo "<br>";
#echo $parameters{peak};       
  if($summer[7]){
    $peaks = $summer[7];
  }
  if($summer[12]){
    $peaks += $summer[12];
  }
  if(defined($summer[13])){
    $peaks += $summer[13];
  }
#echo "<br>";
#$ii = 0;      
#foreach (@summer) {
#  echo "summer[" . $ii . "]=" .$_ . "<br>";
#  $ii++;
#}                 
# retrieve the peptide sequence string
  $peptide = strtolower($summer[6]);
  $numRes = strlen($peptide);
  $residues = str_split($peptide);   # one residue letter per array element

# Create hash for neutral losses, and lookup arrays for variable mods and masses to be ignored
  foreach($masses as $keys => $value){
    if(preg_match('/^delta(\d+)/i', $keys, $matches)){
      $value = trim($value);
      list($vmMass[$matches[1]], $vmString[$matches[1]]) = explode(',', $value);
    }elseif(preg_match('/^NeutralLoss[_]*(.*)/i', $keys, $matches)){
      $value = trim($value);
      $neutralLoss[$matches[1]] = $value;
    }elseif(preg_match('/^Ignore(\d+)/i', $keys, $matches)){
      $value = trim($value);
      $ignoreMass[$matches[1]] = $value;
    }
  }
  
//echo "<br>\$vmMass";
//print_r($vmMass);
//echo "<br>\$vmString";
//print_r($vmString); 
//echo "<br>\$neutralLoss";
//print_r($neutralLoss);
//echo "<br>\$ignoreMass";
//print_r($ignoreMass);

  // Add on any mod found in error tolerant search
  if($px && isset($peptides["q".$queryNum."_p".$hitNum."_et_mods"]) && $peptides["q".$queryNum."_p".$hitNum."_et_mods"]){
    list($vmMass['X'], $neutralLoss['X'], $vmString['X']) = explode(',', $peptides["q".$queryNum."_p".$hitNum."_et_mods"], 3);
  }elseif(isset($summary["h".$hitNum."_q".$queryNum."_et_mods"]) && $summary["h".$hitNum."_q".$queryNum."_et_mods"]){
    list($vmMass['X'], $neutralLoss['X'], $vmString['X']) = explode(',', $summary["h".$hitNum."_q".$queryNum."_et_mods"], 3);
  }

  //calculate fragment ion masses
  // if there are variable mods, they will be included in the running sum
  calcIons();
  
  // and matches to experimental data
  findMatches();
}

if(isset($queryArr['mass_min']) && $queryArr['mass_min'] && isset($queryArr['mass_max']) && $queryArr['mass_max']){
# work out zoom state for gif
  if($xClick == -1){
  # first time through or user clicked on submit button
    if($massMin < $queryArr['mass_min']){
      $massMin = $queryArr['mass_min'];
    }
    if($massMax > $queryArr['mass_max']){
      $massMax = $queryArr['mass_max'];
    }
    if ($massMax <= $massMin){
      $massMin = $queryArr['mass_min'];
      $massMax = $queryArr['mass_max'];
    }
    calcRange();
  }else{
  # user clicked on gif to zoom in factor of 2
  # work out the mass value of the mouse click
    $xScale = ($overallWidth-$leftMargin - $rightMargin) / $displayRange;
    $newMass = (($xClick-$leftMargin) / $xScale) + $firstTick;
  # want to zoom in about this mass, while staying within the mass range of the data
    if(($newMass-$displayRange/4) < $queryArr['mass_min']){
      $massMin = $queryArr['mass_min'];
    }else{
      $massMin = $newMass - $displayRange/4;
    }      
    if(($massMin + $displayRange/2) > $queryArr['mass_max']){
      $massMax=$queryArr['mass_max'];
    }else{
      $massMax = $massMin + $displayRange/2;
    }
    if(($massMax - $displayRange/2) < $massMin){
      if(($massMax - $displayRange/2) >= $queryArr['mass_min']){
        $massMin = ($massMax - $displayRange/2);
      }else{
        $massMin =$queryArr['mass_min'];
      }
    }
    calcRange();
  }
  $lastTick = $firstTick + $displayRange;
}
?>

//start HTML----------------------------------------------------------------------------------------------
<HTML>
<HEAD>
<TITLE>Mascot Search Results: Peptide View</TITLE>
</HEAD>
<BODY BGCOLOR="#ffffff" ALINK="#0000ff" VLINK="#0000ff">
<H1><IMG SRC="../images/88x31_logo_white.gif" WIDTH="88" HEIGHT="31"
ALIGN="TOP" BORDER="0" NATURALSIZEFLAG="3"> Mascot Search Results</H1>
<?php 
$massMin = round($massMin*100)/100;
$massMax = round($massMax*100)/100;
// output form containing MS/MS gif
if($realMatch){
  $peptide = strtoupper($peptide);
  $accession = noDoubleQoute($accession);
?> 
<H3>Peptide View</H3>
MS/MS Fragmentation of <B><FONT COLOR=#FF0000><?php echo $peptide?></FONT></B><br>
Found in <B><FONT COLOR=#FF0000><?php echo noDoubleQoute($accession)?></FONT></B>, <?php echo noDoubleQoute($title)?>
<?php 
  if($frameNum){
    echo "<br>Translated in frame $frameNum ";  
    $encoded = $accession;
    $encoded = preg_replace('/(\W)/', '%20', $accession);
    echo "(<A HREF=\"../cgi/getseq.pl?".$parameters['db']."+$encoded+seq+$frameNum+$summer[3]+$summer[4]\" TARGET=\"_blank\">nucleic acid sequence</A>)<BR>\n";
  }
  echo "<BR>\n";
  $fieldCode = '%.'.$massDP.'f';
  echo "<P>Match to Query ".$queryNum." (" . vsprintf("$fieldCode,%s", split(",",$summary["qexp"."$queryNum"])).")&nbsp;";
  if(isset($queryArr['title']) && $queryArr['title']){
    //------$queryArr['title'] = preg_replace('/%([\dA-Fa-f][\dA-Fa-f])/', pack("C", hexdec(\1)), $queryArr['title']);
    echo noTag($queryArr['title']);
  }
  echo "<BR>";
  if(isset($parameters['file']) &&  $parameters['file']){
    echo "From data file ".noTag($parameters['file'])."<BR>\n";
  }
}else{
  $peptide = "";
  echo "<H3>Peptide View</H3>\n";
  echo "Query $queryNum: No match found<BR>\n";
}
// don't display gif if no mass data (e.g. sequence query)
if($ionsData){
  if($argString = $matchList){
  
    //-----$argString = preg_replace('/([& +])/', sprintf("%%%02x", ord(\1)), $argString);
  } else {
    $argString = "";
  }
  echo "<FORM METHOD=\"GET\" ENCTYPE=\"application/x-www-form-urlencoded\"";
  echo " ACTION=\"peptide_view1.8.pl\">\n";
  echo "Click mouse within plot area to zoom in by factor of two about that point<BR>\n";
  echo "Or,&nbsp;<INPUT TYPE=\"submit\" NAME=\"zoomOut\" VALUE=\"Plot from\" >&nbsp;";
  echo "<INPUT TYPE=\"text\" SIZE=8 NAME=\"from\" VALUE=\"$firstTick\">&nbsp;to&nbsp;";
  echo "<INPUT TYPE=\"text\" SIZE=8 NAME=\"to\" VALUE=\"$lastTick\">&nbsp;Da";
  echo "<P>\n<INPUT TYPE=\"image\" NAME=\"gif\" HEIGHT=$overallHeight WIDTH=$overallWidth";
  echo " ALT=\"MS/MS spectrum of $peptide\" BORDER=2\n";
  echo "SRC=\"./msms_gif.pl?file=$fileIn&query=$queryNum&hit=$hitNum&tick1=$firstTick&tick_int=$tickInterval&range=$displayRange&matches=$argString\">\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"file\" VALUE=\"$fileIn\">\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"query\" VALUE=$queryNum>\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"hit\" VALUE=$hitNum>\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"tick1\" VALUE=$firstTick>\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"tick_int\" VALUE=$tickInterval>\n";
  echo "<INPUT TYPE=\"hidden\" NAME=\"range\" VALUE=$displayRange>\n";
  if($px){
    echo "<INPUT TYPE=\"hidden\" NAME=\"index\" VALUE=\"$accession\">\n";
    echo "<INPUT TYPE=\"hidden\" NAME=\"px\" VALUE=$px>\n";
  }
  echo "</FORM>\n";
}
echo "SRC=\"./msms_gif.pl?file=$fileIn&query=$queryNum&hit=$hitNum&tick1=$firstTick&tick_int=$tickInterval&range=$displayRange&matches=$argString\">\n";

# summary info.

if ($realMatch) {
  echo "<FONT FACE='Courier New,Courier,monospace'>\n";
  echo "<PRE>\n";
  $temp = $runningSum[$numRes-1] + $masses['n_term'] + $masses['c_term'];
  echo "<B>".$parameters['mass']." mass of neutral peptide (Mr):</B>". sprintf(" %.".$massDP."f",$temp)."\n";
  if($parameters['mods']){
    echo "<B>Fixed modifications: </B>".$parameters['mods']."\n";
  }
  if(preg_match('/[1-9A-FX]/', $summer[8])){
    echo "<B>Variable modifications: </B>\n";
    $temp = substr($summer[8],0,1);
    if(preg_match('/[1-9A-FX]/', $temp)){    
      echo "<B>N-term : </B>".$vmString[$temp]."\n";
    }
    for ($i=1; $i<strlen($summer[8])-1; $i++){
      $temp = substr($summer[8],$i,1);
      if(preg_match('/[1-9A-FX]/', $temp)){
        echo "<B>".strtoupper($residues[$i-1]).sprintf('%-2d',$i)."    : </B>".$vmString[$temp]."\n";
      }
    }
    $temp = substr($summer[8],-1,1);
    if(preg_match('/[1-9A-FX]/', $temp)){
      echo "<B>C-term : </B>".$vmString[$temp]."\n";
    }
  }
  printf("<B>Ions Score:</B> %-.f  ",$summer[9]);
  $i = count($exp_masses);
  if($massList[0]){
    echo "<B>Matches (<FONT COLOR=#FF0000>Bold Red</FONT>):</B> $i/$numCalcVals";
    echo " fragment ions using $peaks most intense peaks\n";
  } else {
    echo "\n";
  }
  echo "</PRE>\n";
  echo "</FONT>\n";
# echo table of fragment ion masses
  
  echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>\n";

  echo "  <TR BGCOLOR=#cccccc>\n";
  echo "    <TH>#</TH>\n";
  if (isset($include['immIons']) && $include['immIons']>0)echo "    <TH>Immon.</TH>\n";
  if (isset($include['aIons']) && $include['aIons']>0)  echo "    <TH>a</TH>\n";
  if (isset($include['a2Ions']) && $include['a2Ions']>0) echo "    <TH>a<SUP>++</SUP></TH>\n";
  if (isset($include['asIons']) && $include['asIons']>0) echo "    <TH>a*</TH>\n";
  if (isset($include['as2Ions']) && $include['as2Ions']>0)echo "    <TH>a*<SUP>++</SUP></TH>\n";
  if (isset($include['a0Ions']) && $include['a0Ions']>0) echo "    <TH>a<SUP>0</SUP></TH>\n";
  if (isset($include['a02Ions']) && $include['a02Ions']>0)echo "    <TH>a<SUP>0++</SUP></TH>\n";
  if (isset($include['bIons']) && $include['bIons']>0)  echo "    <TH>b</TH>\n";
  if (isset($include['b2Ions']) && $include['b2Ions']>0) echo "    <TH>b<SUP>++</SUP></TH>\n";
  if (isset($include['bsIons']) && $include['bsIons']>0) echo "    <TH>b*</TH>\n";
  if (isset($include['bs2Ions']) && $include['bs2Ions']>0)echo "    <TH>b*<SUP>++</SUP></TH>\n";
  if (isset($include['b0Ions']) && $include['b0Ions']>0) echo "    <TH>b<SUP>0</SUP></TH>\n";
  if (isset($include['b02Ions']) && $include['b02Ions']>0)echo "    <TH>b<SUP>0++</SUP></TH>\n";
  if (isset($include['cIons']) && $include['cIons']>0)  echo "    <TH>c</TH>\n";
  if (isset($include['c2Ions']) && $include['c2Ions']>0) echo "    <TH>c<SUP>++</SUP></TH>\n";
  echo "    <TH>Seq.</TH>\n";
  if (isset($include['xIons']) && $include['xIons']>0)  echo "    <TH>x</TH>\n";
  if (isset($include['x2Ions']) && $include['x2Ions']>0) echo "    <TH>x<SUP>++</SUP></TH>\n";
  if (isset($include['yIons']) && $include['yIons']>0)  echo "    <TH>y</TH>\n";
  if (isset($include['y2Ions']) && $include['y2Ions']>0) echo "    <TH>y<SUP>++</SUP></TH>\n";
  if (isset($include['ysIons']) && $include['ysIons']>0) echo "    <TH>y*</TH>\n";
  if (isset($include['ys2Ions']) && $include['ys2Ions']>0)echo "    <TH>y*<SUP>++</SUP></TH>\n";
  if (isset($include['y0Ions']) && $include['y0Ions']>0) echo "    <TH>y<SUP>0</SUP></TH>\n";
  if (isset($include['y02Ions']) && $include['y02Ions']>0)echo "    <TH>y<SUP>0++</SUP></TH>\n";
  if (isset($include['zIons']) && $include['zIons']>0)  echo "    <TH>z</TH>\n";
  if (isset($include['z2Ions']) && $include['z2Ions']>0) echo "    <TH>z<SUP>++</SUP></TH>\n";
  echo "    <TH>#</TH>\n";
  echo "  </TR>\n";
  
  $fieldCode = $ionsDP + 5;
  $fieldCode = '%'.$fieldCode.'.'.$ionsDP.'f';
  
/*  
echo "<pre>";  
print_r($labels);
echo "</pre>"; 
exit;
*/
  for ($i = 1; $i <= $numRes; $i++) {
    $j = $numRes - $i + 1;
    echo "  <TR ALIGN=\"RIGHT\">\n";
    echo "    <TD><B><FONT COLOR=#0000FF>$i</FONT></B></TD>\n";
    if (isset($include['immIons']) && $include['immIons']>0)  printMasses($i-1, $fieldCode, $immIons, 0);
    if (isset($include['aIons']) && $include['aIons']>0)   printMasses($i-1, $fieldCode, $aIons, $seriesSig['iatol']);
    if (isset($include['a2Ions']) && $include['a2Ions']>0)  printMasses($i-1, $fieldCode, $a2Ions, $seriesSig['ia2tol']);
    if (isset($include['asIons']) && $include['asIons']>0)  printMasses($i-1, $fieldCode, $asIons, $seriesSig['iastol']);
    if (isset($include['as2Ions']) && $include['as2Ions']>0) printMasses($i-1, $fieldCode, $as2Ions, 0);
    if (isset($include['a0Ions']) && $include['a0Ions']>0)  printMasses($i-1, $fieldCode, $a0Ions, 0);
    if (isset($include['a02Ions']) && $include['a02Ions']>0) printMasses($i-1, $fieldCode, $a02Ions, 0);
    if (isset($include['bIons']) && $include['bIons']>0)   printMasses($i-1, $fieldCode, $bIons, $seriesSig['ibtol']);
    if (isset($include['b2Ions']) && $include['b2Ions']>0)  printMasses($i-1, $fieldCode, $b2Ions, $seriesSig['ib2tol']);
    if (isset($include['bsIons']) && $include['bsIons']>0)  printMasses($i-1, $fieldCode, $bsIons, $seriesSig['ibstol']);
    if (isset($include['bs2Ions']) && $include['bs2Ions']>0) printMasses($i-1, $fieldCode, $bs2Ions, 0);
    if (isset($include['b0Ions']) && $include['b0Ions']>0)  printMasses($i-1, $fieldCode, $b0Ions, 0);
    if (isset($include['b02Ions']) && $include['b02Ions']>0) printMasses($i-1, $fieldCode, $b02Ions, 0);
    if (isset($include['cIons']) && $include['cIons']>0)   printMasses($i-1, $fieldCode, $cIons, $seriesSig['ictol']);
    if (isset($include['c2Ions']) && $include['c2Ions']>0)  printMasses($i-1, $fieldCode, $c2Ions, $seriesSig['ic2tol']);
    echo "    <TD ALIGN=\"CENTER\"><B><FONT COLOR=#0000FF>".strtoupper($residues[$i-1])."</FONT></B></TD>\n";
    if (isset($include['xIons']) && $include['xIons']>0)   printMasses($j-1, $fieldCode, $xIons, $seriesSig['ixtol']);
    if (isset($include['x2Ions']) && $include['x2Ions']>0)  printMasses($j-1, $fieldCode, $x2Ions, $seriesSig['ix2tol']);
    if (isset($include['yIons']) && $include['yIons']>0)   printMasses($j-1, $fieldCode, $yIons, $seriesSig['iytol']);
    if (isset($include['y2Ions']) && $include['y2Ions']>0)  printMasses($j-1, $fieldCode, $y2Ions, $seriesSig['iy2tol']);
    if (isset($include['ysIons']) && $include['ysIons']>0)  printMasses($j-1, $fieldCode, $ysIons, $seriesSig['iystol']);
    if (isset($include['ys2Ions']) && $include['ys2Ions']>0) printMasses($j-1, $fieldCode, $ys2Ions, 0);
    if (isset($include['y0Ions']) && $include['y0Ions']>0)  printMasses($j-1, $fieldCode, $y0Ions, 0);
    if (isset($include['y02Ions']) && $include['y02Ions']>0) printMasses($j-1, $fieldCode, $y02Ions, 0);
    if (isset($include['zIons']) && $include['zIons']>0)   printMasses($j-1, $fieldCode, $zIons, $seriesSig['iztol']);
    if (isset($include['z2Ions']) && $include['z2Ions']>0)  printMasses($j-1, $fieldCode, $z2Ions, $seriesSig['iz2tol']);
    echo "    <TD><B><FONT COLOR=#0000FF>$j</FONT></B></TD>\n";
    echo "  </TR>\n";
  }
  echo "</TABLE>\n";
  
  if((isset($include['intyaIons']) && $include['intyaIons']>0) || (isset($include['intybIons']) && $include['intybIons']>0)){
  # split list into 3 columns
    $j = 0;
    for($m=0; $m<3; $m++){
      $labelsByColumn[$m] = array();
      $intyaIonsByColumn[$m] = array();
      $intybIonsByColumn[$m] = array();
    }
    for($i = 0; $i < count($intyaIons); $i++){
      if($intyaIons[$i] < 700){
        array_push($labelsByColumn[$j % 3], $intybLabels[$i]);
        array_push($intyaIonsByColumn[$j % 3], $intyaIons[$i]);
        array_push($intybIonsByColumn[$j % 3], $intybIons[$i]);
        $j++;
      }
    }
    echo "<P><TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>\n";
    echo "  <TR BGCOLOR=#cccccc>\n";
    echo "    <TH>Seq</TH>\n";
    if($include['intyaIons']>0) echo  "    <TH>ya</TH>\n";
    if($include['intybIons']>0) echo  "    <TH>yb</TH>\n";
    if(count($labelsByColumn[1])){
      echo "    <TH>Seq</TH>\n";
      if($include['intyaIons']>0) echo "    <TH>ya</TH>\n";
      if($include['intybIons']>0) echo "    <TH>yb</TH>\n";
    }
    if(count($labelsByColumn[2])){
      echo "    <TH>Seq</TH>\n";
      if($include['intyaIons']>0) echo "    <TH>ya</TH>\n";
      if($include['intybIons']>0) echo "    <TH>yb</TH>\n";
    }
    echo "  </TR>\n";
    $i = 0;
    while(isset($labelsByColumn[0][$i])){
      echo "  <TR ALIGN=\"LEFT\">\n";
      echo "    <TD><B><FONT COLOR=#0000FF>$" . $labelsByColumn[0][$i] . "</FONT></B></TD>\n";
      if($include['intyaIons']>0) printMasses($i, $fieldCode, $intyaIonsByColumn[0], 0);
      if($include['intybIons']>0) printMasses($i, $fieldCode, $intybIonsByColumn[0], 0);
      if(count($labelsByColumn[1]) && isset($labelsByColumn[1][$i]) && $labelsByColumn[1][$i]){
        echo "    <TD><B><FONT COLOR=#0000FF>" . $labelsByColumn[1][$i] . "</FONT></B></TD>\n";
        if($include['intyaIons']>0) printMasses($i, $fieldCode, $intyaIonsByColumn[1], 0);
        if($include['intybIons']>0) printMasses($i, $fieldCode, $intybIonsByColumn[1], 0);
      }elseif(count($labelsByColumn[1])){
        echo "    <TD>&nbsp;</TD>\n";
        if($include['intyaIons']>0) echo "    <TD>&nbsp;</TD>\n";
        if($include['intybIons']>0) echo "    <TD>&nbsp;</TD>\n";
      }
      if(count($labelsByColumn[2]) && isset($labelsByColumn[2][$i]) && $labelsByColumn[2][$i]){
        echo "    <TD><B><FONT COLOR=#0000FF>" . $labelsByColumn[2][$i] . "</FONT></B></TD>\n";
        if($include['intyaIons']>0) printMasses($i, $fieldCode, $intyaIonsByColumn[2], 0);
        if($include['intybIons']>0) printMasses($i, $fieldCode, $intybIonsByColumn[2], 0);
      }elseif(count($labelsByColumn[2])){
        echo "    <TD>&nbsp;</TD>\n";
        if($include['intyaIons']>0) echo "    <TD>&nbsp;</TD>\n";
        if($include{'intybIons'}>0) echo "    <TD>&nbsp;</TD>\n";
      }
      echo "  </TR>\n";
      $i++;
    }
    echo "</TABLE>\n";
  }
    
  # graph of mass error distribution
  echo "<P><IMG SRC=\"mass_error.pl?units=".$parameters['itolu']."&file=massList:";
  if(count(exp_masses)){
    echo sprintf("%.2f",$exp_masses[0]);
    for($i=1; $i<count($exp_masses); $i++){
      echo "," . sprintf("%.2f",$exp_masses[$i]);
    }
  }
  echo "&hit=errorList:";
  if(count($delta_masses)){
    echo sprintf("%.6f",$delta_masses[0]);
    for($i=1; $i<count($delta_masses); $i++){
      echo ",".sprintf("%.6f",$delta_masses[$i]);
    }
  }
  echo "\" WIDTH=450 HEIGHT=150 ALT=\"Error Distribution\">\n";

  echo "<P>NCBI <B>BLAST</B> search of <A HREF=\"";
  echo "http://www.ncbi.nlm.nih.gov/blast/Blast.cgi?ALIGNMENTS=50&ALIGNMENT_VIEW=Pairwise";
  echo "&AUTO_FORMAT=Semiauto&CLIENT=web&DATABASE=nr&DESCRIPTIONS=100&ENTREZ_QUERY=(none)";
  echo "&EXPECT=20000&FORMAT_BLOCK_ON_RESPAGE=None&FORMAT_OBJECT=Alignment&FORMAT_TYPE=HTML";
  echo "&GAPCOSTS=9+1&I_THRESH=0.001&LAYOUT=TwoWindows&MATRIX_NAME=PAM30&NCBI_GI=on";
  echo "&PAGE=Proteins&PROGRAM=blastp&QUERY=";
  echo $peptide;
  echo "&SERVICE=plain&SET_DEFAULTS.x=32&SET_DEFAULTS.y=7&SHOW_OVERVIEW=on&WORD_SIZE=2";
  echo "&END_OF_HTTPGET=Yes\" TARGET=\"_blank\">";
  echo "$peptide</A><BR>\n";
?>
(Parameters: blastp, nr protein database, expect=20000, no filter, PAM30)<BR>
Other BLAST <A HREF="../help/blast_help.html#web">web gateways</A><BR> 
<P><TABLE WIDTH="100%" BORDER="2" CELLSPACING="2" CELLPADDING="1">
<TR><TD ALIGN="CENTER" NOWRAP><B>Mascot:</B>&nbsp;
<A HREF="http://www.matrixscience.com/index.html">http://www.matrixscience.com/</A>
</TD></TR>
</TABLE>
<?php 
} 


/*****************************************************************************
# printMasses()
# $index index into ions array
# $fieldCode $fieldCode
# $ionMasses \@ionMasses
# $seriesSig $seriesSig{i*tol}
# globals:
# my(%labels, $debug);
# prints cell in ions table
*****************************************************************************/

function printMasses($index, $fieldCode, &$ionMasses, $seriesSig){
  global $labels, $debug;
  if(isset($ionMasses[$index]) && isset($labels["$ionMasses[$index]"]) && $labels["$ionMasses[$index]"]){
    if(preg_match('/^true/i', $debug)){
      if (isset($seriesSig) && $seriesSig == 2) {
        echo sprintf("    <TD><B><I><FONT COLOR=#FF0000>$fieldCode</FONT></I></B></TD>\n", $ionMasses[$index]);
      }elseif(isset($seriesSig) && $seriesSig == 1) {
        echo sprintf("    <TD><B><FONT COLOR=#FF0000>$fieldCode</FONT></B></TD>\n", $ionMasses[$index]);
      }else{
        echo sprintf("    <TD><FONT COLOR=#FF0000>$fieldCode</FONT></TD>\n", $ionMasses[$index]);
      }
    }else{
      echo sprintf("    <TD><B><FONT COLOR=#FF0000>$fieldCode</FONT></B></TD>\n", $ionMasses[$index]);
    }
  }elseif(isset($ionMasses[$index]) && $ionMasses[$index] > 0) {
    echo sprintf("    <TD>$fieldCode</TD>\n", $ionMasses[$index]);
  }else{
    echo "    <TD>&nbsp;</TD>\n";
  }
  return 1;
}


/************************************************************************
 &calcRange()
 no parameters
 globals:
 my($massMax, $massMin, $tickInterval, $firstTick, $displayRange);
 have to duplicate some of the code from msms_gif.pl here because
 there is no mechanism for it to return the actual mass range plotted
 find least power of 10 which is >= total mass range
**************************************************************************/

function calcRange(){
  global $massMax, $massMin, $tickInterval, $firstTick, $displayRange;
echo "<br>".$massMax." - ".$massMin;
  $massRange = $massMax - $massMin;
echo "<br>".$massRange;
  $i = 0;
  while(pow(10, $i) < $massRange) $i++;
// drop a power of 10 & find ceiling
  $j = 10;
  $i = pow(10, ($i-1));
  while ($j*$i > $massRange) $j--;
// drop another power of 10 & find ceiling
  $j = ($j+1)*10;
  $i = $i/10;
  while ($j*$i > $massRange) $j--;
// increase $tickInterval to get between 10 and 25 ticks
  $tickInterval = $i;
  $numTicks = $j + 2;
  if($numTicks > 50){
    $tickInterval *= 5;
  }elseif($numTicks>20){
    $tickInterval *= 2;
  }  
  $firstTick = intval($massMin / $tickInterval) * $tickInterval;
  $displayRange = intval(($massMax-$firstTick)/$tickInterval)*$tickInterval;
  while(($firstTick+$displayRange) < $massMax){
    $displayRange += $tickInterval;    
  }
  if($displayRange < 10){
    $displayRange = 10;
    $tickInterval = 1;
  }  
return 1;
}

/******************************************************************************
 getRules()
 no parameters
 globals:
 $charge, $include, $parameters;
********************************************************************************

 1  # singly charged 
      (required)
 2  # doubly charged if precursor 2+ or higher
      (not internal or immonium) 
 3  # doubly charged if precursor 3+ or higher
      (not internal or immonium) 
 4  # immonium
 5  # a series
 6  # a - NH3 if a significant and fragment includes RKNQ
 7  # a - H2O if a significant and fragment includes STED
 8  # b series
 9  # b - NH3 if b significant and fragment includes RKNQ
 10 # b - H2O if b significant and fragment includes STED
 11 # c series
 12 # x series
 13 # y series
 14 # y - NH3 if y significant and fragment includes RKNQ
 15 # y - H2O if y significant and fragment includes STED
 16 # z series
 17 # internal yb < 700 Da
 18 # internal ya < 700 Da
 19 # y or y++ must be significant
 20 # y or y++ must be highest scoring series
********************************************************************************/
function getRules(){
  global $charge, $include, $parameters;
  $chosen = array();
  $specified = array();
  
  if(!isset($parameters['rules']) || !$parameters['rules']) {
  // ensure reasonable default
  // debug
    $parameters['rules'] = "1,2,5,6,8,9,13,14";
  // debug
  //     $parameters['rules'] = "1,2,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18";
  // debug
  }
  $specified = explode(',', $parameters['rules']);
  foreach($specified as $value){
    $chosen[$value] = 1;
  }
  // always include 1+ series, even though rules imply they can be omitted
  if(isset($chosen['4'])) $include['immIons'] = 1;
  if(isset($chosen['5'])) $include['aIons'] = 1;
  if(isset($chosen['5']) && isset($chosen['2']) && $charge > 1) $include['a2Ions'] = 1;
  if(isset($chosen['5']) && isset($chosen['3']) && $charge > 2) $include['a2Ions'] = 1;
  if(isset($chosen['6'])) $include['asIons'] = 1;
  if(isset($chosen['6']) && isset($chosen['2']) && $charge > 1) $include['as2Ions'] = 1;
  if(isset($chosen['6']) && isset($chosen['3']) && $charge > 2) $include['as2Ions'] = 1;
  if(isset($chosen['7'])) $include['a0Ions'] = 1;
  if(isset($chosen['7']) && isset($chosen['2']) && $charge > 1) $include['a02Ions'] = 1;
  if(isset($chosen['7']) && isset($chosen['3']) && $charge > 2) $include['a02Ions'] = 1;
  if(isset($chosen['8'])) $include['bIons'] = 1;
  if(isset($chosen['8']) && isset($chosen['2']) && $charge > 1) $include['b2Ions'] = 1;
  if(isset($chosen['8']) && isset($chosen['3']) && $charge > 2) $include['b2Ions'] = 1;
  if(isset($chosen['9'])) $include['bsIons'] = 1;
  if(isset($chosen['9']) && isset($chosen['2']) && $charge > 1) $include['bs2Ions'] = 1;
  if(isset($chosen['9']) && isset($chosen['3']) && $charge > 2) $include['bs2Ions'] = 1;
  if(isset($chosen['10'])) $include['b0Ions'] = 1;
  if(isset($chosen['10']) && isset($chosen['2']) && $charge > 1) $include['b02Ions'] = 1;
  if(isset($chosen['10']) && isset($chosen['3']) && $charge > 2) $include['b02Ions'] = 1;
  if(isset($chosen['11'])) $include['cIons'] = 1;
  if(isset($chosen['11']) && isset($chosen['2']) && $charge > 1) $include['c2Ions'] = 1;
  if(isset($chosen['11']) && isset($chosen['3']) && $charge > 2) $include['c2Ions'] = 1;
  if(isset($chosen['12'])) $include['xIons'] = 1;
  if(isset($chosen['12']) && isset($chosen['2']) && $charge > 1) $include['x2Ions'] = 1;
  if(isset($chosen['12']) && isset($chosen['3']) && $charge > 2) $include['x2Ions'] = 1;
  if(isset($chosen['13'])) $include['yIons'] = 1;
  if(isset($chosen['13']) && isset($chosen['2']) && $charge > 1) $include['y2Ions'] = 1;
  if(isset($chosen['13']) && isset($chosen['3']) && $charge > 2) $include['y2Ions'] = 1;
  if(isset($chosen['14']) && !isset($chosen['16'])) $include['ysIons'] = 1;
  if(isset($chosen['14']) && !isset($chosen['16']) && isset($chosen['2']) && $charge > 1) $include['ys2Ions'] = 1;
  if(isset($chosen['14']) && !isset($chosen['16']) && isset($chosen['3']) && $charge > 2) $include['ys2Ions'] = 1;
  if(isset($chosen['15'])) $include['y0Ions'] = 1;
  if(isset($chosen['15']) && isset($chosen['2']) && $charge > 1) $include['y02Ions'] = 1;
  if(isset($chosen['15']) && isset($chosen['3']) && $charge > 2) $include['y02Ions'] = 1;
  if(isset($chosen['16'])) $include['zIons'] = 1;
  if(isset($chosen['16']) && isset($chosen['2']) && $charge > 1) $include['z2Ions'] = 1;
  if(isset($chosen['16']) && isset($chosen['3']) && $charge > 2) $include['z2Ions'] = 1;
  if(isset($chosen['17'])) $include['intybIons'] = 1;
  if(isset($chosen['18'])) $include['intyaIons'] = 1;
}

/*****************************************************************************
 &calcIons()
 no parameters
 globals:
 my(@temp_masses, @runningSum, %masses, @residues, @summer, %vmMass,
   $numRes, @aIons, %parameters, %labelList, @asIons, @a2Ions, @bIons,
   @bsIons, @b2Ions, @yIons, @ysIons, @y2Ions, $numCalcVals, );
******************************************************************************/

function calcIons(){
  global $temp_masses, $runningSum, $masses, $residues, $summer, $vmMass,
         $parameters, $labelList, $queryArr, $numRes, $numCalcVals, $include;
  global $aIons,$a2Ions,$asIons,$as2Ions,$a0Ions,$a02Ions,$bIons,$b2Ions,$bsIons,$bs2Ions,$b0Ions,$b02Ions,
          $cIons,$c2Ions,$immIons,$intyaIons,$intybIons,$xIons,$x2Ions,$yIons,$y2Ions,$ysIons,$ys2Ions,$y0Ions,
          $y02Ions,$zIons,$z2Ions;        
         
/*        
echo "<pre>\$include ";
print_r($include);
echo "</pre>";
*/
// calculated masses for selected series are consolidated in array $temp_masses
  $temp_masses[0] = 0;
// and label text goes into array $labelList using calculated mass as key
// array $neutralLossList contains any neutral loss masses for the corresponding elements of array $runningSum
// calculate the running sum of the residue masses, including any variable mods
  $runningSum[0] = $masses{$residues[0]};
  $temp = substr($summer[8],1,1);
  if(preg_match('/[1-9A-FX]/', $temp)){
    $runningSum[0] += $vmMass[$temp];
    $neutralLossList[0] = $neutralLoss[$temp];
  }elseif(isset($neutralLoss[$residues[0]]) && $neutralLoss[$residues[0]]){
    $neutralLossList[0] = $neutralLoss[$residues[0]];
  }else{
    $neutralLossList[0] = 0;
  }
  for ($i=1; $i<$numRes; $i++){
    $runningSum[$i] = $runningSum[$i-1] + $masses[$residues[$i]];
    $temp = substr($summer[8], $i+1, 1);
    if(preg_match('/[1-9A-FX]/', $temp)){
      $runningSum[$i] += $vmMass[$temp];
      $neutralLossList[$i] = $neutralLossList[$i-1] + $neutralLoss[$temp];
    }elseif(isset($neutralLoss[$residues[$i]]) && $neutralLoss[$residues[$i]]){
      $neutralLossList[$i] = $neutralLossList[$i-1] + $neutralLoss[$residues[$i]];
    }else{
      $neutralLossList[$i] = $neutralLossList[$i-1] + 0;
    }
  }

// If there is a variable mod at either terminus, add it to masses{}
  $temp = substr($summer[8],0,1);
  if(preg_match('/[1-9A-FX]/', $temp)){
    $masses['n_term'] += $vmMass[$temp];
    $neutralLossList_N_Term = $neutralLoss[$temp];
  }elseif(isset($neutralLoss['n_term']) && $neutralLoss['n_term']){
    $neutralLossList_N_Term = $neutralLoss['n_term'];
  }else{
    $neutralLossList_N_Term = 0;
  }  
  $temp = substr($summer[8],-1,1);
  if(preg_match('/[1-9A-FX]/', $temp)){
    $masses['c_term'] += $vmMass[$temp];
    $neutralLossList_C_Term = $neutralLoss[$temp];
  }elseif(isset($neutralLoss['c_term']) && $neutralLoss['c_term']){
    $neutralLossList_C_Term = $neutralLoss['c_term'];
  } else {
    $neutralLossList_C_Term = 0;
  }  

// calculate fragment ion masses for each series
    $CO = $masses['carbon'] + $masses['oxygen'];
    $NH3 = $masses['nitrogen'] + 3*$masses['hydrogen'];
    $H2O = 2*$masses['hydrogen'] + $masses['oxygen'];
# first n-term
  for($i=0; $i<($numRes-1); $i++){
    $j = $i + 1;
    $aIons[$i]=$runningSum[$i] + $masses['n_term'] - $CO - $neutralLossList[$i] - $neutralLossList_N_Term;
    if(isset($include['aIons']) && $include['aIons'] > 0){
      array_push($temp_masses, $aIons[$i]);
      $labelList["$aIons[$i]"] = "a($j)";
    } 
    if(isset($include['a2Ions']) && $include['a2Ions'] >0 ){
      $a2Ions[$i]=($aIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $a2Ions[$i]);
      $labelList["$a2Ions[$i]"] = "a($j)++";
    }
    if(preg_match('/[RKNQ]/', substr($summer[6],0,$i+1))){
      $asIons[$i] = $aIons[$i] - $NH3; 
      if(isset($include['asIons']) && $include['asIons'] > 0){
        array_push($temp_masses, $asIons[$i]);
        $labelList["$asIons[$i]"] = "a*($j)";
      }  
      if(isset($include['as2Ions']) && $include['as2Ions'] > 0 ){
        $as2Ions[$i]=($asIons[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $as2Ions[$i]);
        $labelList["$as2Ions[$i]"] = "a*($j)++";
      }  
    }
    if(preg_match('/[STED]/', substr($summer[6],0,$i+1))){
      $a0Ions[$i] = $aIons[$i] - $H2O; 
      if(isset($include['a0Ions']) && $include['a0Ions'] > 0){
        array_push($temp_masses, $a0Ions[$i]);
        $labelList["$a0Ions[$i]"] = "a0($j)";
      }  
      if(isset($include['a02Ions']) && $include['a02Ions'] > 0){
        $a02Ions[$i]=($a0Ions[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $a02Ions[$i]);
        $labelList["$a02Ions[$i]"] = "a0($j)++";
      }  
    }
    $bIons[$i]=$runningSum[$i] + $masses['n_term'] - $neutralLossList[$i] - $neutralLossList_N_Term; 
    if(isset($include['bIons']) && $include['bIons'] > 0){
      array_push($temp_masses, $bIons[$i]);
      $labelList["$bIons[$i]"] = "b($j)";
    }  
    if(isset($include['b2Ions']) && $include['b2Ions'] > 0){
      $b2Ions[$i]=($bIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $b2Ions[$i]);
      $labelList["$b2Ions[$i]"] = "b($j)++";
    }
    if(preg_match('/[RKNQ]/', substr($summer[6],0,$i+1))){
      $bsIons[$i] = $bIons[$i] - $NH3; 
      if(isset($include['bsIons']) && $include['bsIons'] > 0){
        array_push($temp_masses, $bsIons[$i]);
        $labelList["$bsIons[$i]"] = "b*($j)";
      }  
      if(isset($include['bs2Ions']) && $include['bs2Ions'] > 0){
        $bs2Ions[$i] = ($bsIons[$i] + $masses{hydrogen})/2;
        array_push($temp_masses, $bs2Ions[$i]);
        $labelList["$bs2Ions[$i]"] = "b*($j)++";
      }  
    }
    if(preg_match('/[STED]/', substr($summer[6],0,$i+1))){
      $b0Ions[$i]=$bIons[$i] - $H2O; 
      if(isset($include['b0Ions']) && $include['b0Ions']>0){
        array_push($temp_masses, $b0Ions[$i]);
        $labelList["$b0Ions[$i]"] = "b0($j)";
      }  
      if(isset($include['b02Ions']) && $include['b02Ions'] > 0){
        $b02Ions[$i]=($b0Ions[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $b02Ions[$i]);
        $labelList["$b02Ions[$i]"] = "b0($j)++";
      }  
    }
    $cIons[$i] = $bIons[$i] + $NH3; 
    if(isset($include['cIons']) && $include['cIons'] > 0){
      array_push($temp_masses, $cIons[$i]);
      $labelList["$cIons[$i]"] = "c($j)";
    }  
    if(isset($include['c2Ions']) && $include['c2Ions'] > 0){
      $c2Ions[$i] = ($cIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $c2Ions[$i]);
      $labelList["$c2Ions[$i]"] = "c($j)++";
    }  
  }
# then c-term
  for($i=0; $i<($numRes-1); $i++){
    $j = $i + 1;
    $yIons[$i] = $runningSum[$numRes-1] - $runningSum[$numRes-2-$i] + $masses['c_term'] + 2*$masses['hydrogen']
      - $neutralLossList[$numRes-1] + $neutralLossList[$numRes-2-$i] - $neutralLossList_C_Term; 
    if(isset($include['yIons']) && $include['yIons'] > 0){
      array_push($temp_masses, $yIons[$i]);
      $labelList["$yIons[$i]"] = "y($j)";
    }  
    if(isset($include['y2Ions']) && $include['y2Ions'] > 0){
      $y2Ions[$i] = ($yIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $y2Ions[$i]);
      $labelList["$y2Ions[$i]"] = "y($j)++";
    }
    if(preg_match('/[RKNQ]/', substr($summer[6],-$i-1))){
      $ysIons[$i] = $yIons[$i] - $NH3; 
      if(isset($include['ysIons']) && $include['ysIons'] > 0){
        array_push($temp_masses, $ysIons[$i]);
        $labelList["$ysIons[$i]"] = "y*($j)";
      }  
      if(isset($include['ys2Ions']) && $include['ys2Ions'] > 0){
        $ys2Ions[$i]=($ysIons[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $ys2Ions[$i]);
        $labelList["$ys2Ions[$i]"] = "y*($j)++";
      }  
    }
    if(preg_match('/[STED]/', substr($summer[6],-$i-1))){
      $y0Ions[$i] = $yIons[$i] - $H2O; 
      if(isset($include['y0Ions']) && $include['y0Ions'] > 0){
        array_push($temp_masses, $y0Ions[$i]);
        $labelList["$y0Ions[$i]"] = "y0($j)";
      }  
      if(isset($include['y02Ions']) && $include['y02Ions'] > 0){
        $y02Ions[$i] = ($y0Ions[$i] + $masses['hydrogen'])/2;
        array_push($temp_masses, $y02Ions[$i]);
        $labelList["$y02Ions[$i]"] = "y0($j)++";
      }  
    }
    $xIons[$i] = $yIons[$i] - 2*$masses['hydrogen'] + $CO; 
    if(isset($include['xIons']) && $include['xIons'] > 0){
      array_push($temp_masses, $xIons[$i]);
      $labelList["$xIons[$i]"] = "x($j)";
    }  
    if(isset($include['x2Ions']) && $include['x2Ions'] > 0){
      $x2Ions[$i]=($xIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $x2Ions[$i]);
      $labelList["$x2Ions[$i]"] = "x($j)++";
    }  
    $zIons[$i]=$yIons[$i] - $NH3; 
    if(isset($include['zIons']) && $include['zIons'] > 0){
      array_push($temp_masses, $zIons[$i]);
      $labelList["$zIons[$i]"] = "z($j)";
    }  
    if(isset($include['z2Ions']) && $include['z2Ions'] > 0){
      $z2Ions[$i]=($zIons[$i] + $masses['hydrogen'])/2;
      array_push($temp_masses, $z2Ions[$i]);
      $labelList["$z2Ions[$i]"] = "z($j)++";
    }  
  }
  $numCalcVals = count($temp_masses) - 1;

  // immonium ions
//echo "<br>count(\$residues)=";
//echo count($residues);  
  if(isset($include['immIons']) && $include['immIons'] > 0){
    for($i=0; $i < count($residues); $i++){
      $immIons[$i] = $masses[$residues[$i]] - $CO + $masses['hydrogen'];
      if(isset($neutralLoss[$residues[$i]]) && $neutralLoss[$residues[$i]]) {
        $immIons[$i] -= $neutralLoss[$residues[$i]];
      }
      $temp = substr($summer[8],$i+1,1);
      if(preg_match('/[1-9A-FX]/', $temp)){
        $immIons[$i] += $vmMass[$temp];
        $immIons[$i] -= $neutralLoss[$temp];
      }
      array_push($temp_masses, $immIons[$i]);
      $labelList["$immIons[$i]"] = strtoupper($residues[$i]);
    }
  }

  // internals
  // unlike other series, we may encounter duplicate mass values
  if((isset($include['intyaIons']) && $include['intyaIons'] > 0) || (isset($include['intybIons']) && $include['intybIons'] > 0)){
    for($i=0; $i < count($residues) - 3; $i++){
      for($j=$i+2; $j < count($residues) - 1; $j++){
        array_push($intybIons, $runningSum[$j] - $runningSum[$i]  + $masses['hydrogen'] - $neutralLossList[$j] + $neutralLossList[$i]);
        array_push($intybLabels, substr($summer[6],$i+1,$j-$i));
        $intybIonsLastIndex = count($intybIons) - 1;
        $intybIonsLastValue = $intybIons[$intybIonsLastIndex];
        if($include['intybIons'] > 0 && $intybIonsLastValue < 700){
          array_push($temp_masses, $intybIonsLastValue);
          $labelList["$intybIonsLastValue"] = substr($summer[6],$i+1,$j-$i);
        }
        array_push($intyaIons, $intybIonsLastValue - $CO);
        $intyaIonsLastIndex = count($intyaIons) - 1;
        $intyaIonsLastValue = $intyaIons[$intyaIonsLastIndex];
        if($include['intyaIons'] > 0 && $intyaIonsLastValue < 700){
          array_push($temp_masses, $intyaIonsLastValue);
          $labelList["$intyaIonsLastValue"] = substr($summer[6],$i+1,$j-$i) . "-28";
        }
      }
    }
  }
/*  
echo "<pre>\$include ";
print_r($include);
echo "</pre>";
*/
//echo "<pre>\$temp_masses ";
//print_r($temp_masses);  
  
  // if there are no values for the NH3 and H2O neutral losses, drop the column
  
  if(!isset($asIons) || count($asIons) == 0){
    $include['asIons'] = 0;
  }
  if(!isset($as2Ions) || count($as2Ions) == 0){
    $include['as2Ions'] = 0;
  }
  if(!isset($a0Ions) || count($a0Ions) == 0){
    $include['a0Ions'] = 0;
  }
  if(!isset($a02Ions) || count($a02Ions) == 0){
    $include['a02Ions'] = 0;
  }
  if(!isset($bsIons) || count($bsIons) == 0){
    $include['bsIons'] = 0;
  }
  if(!isset($bs2Ions) || count($bs2Ions) == 0){
    $include['bs2Ions'] = 0;
  }
  if(!isset($b0Ions) || count($b0Ions) == 0){
    $include['b0Ions'] = 0;
  }
  if(!isset($b02Ions) || count($b02Ions) == 0){
    $include['b02Ions'] = 0;
  }
  if(!isset($ysIons) || count($ysIons) == 0){
    $include['ysIons'] = 0;
  }
  if(!isset($ys2Ions) || count($ys2Ions) == 0){
    $include['ys2Ions'] = 0;
  }
  if(!isset($y0Ions) || count($y0Ions) == 0){
    $include['y0Ions'] = 0;
  }
  if(!isset($y02Ions) || count($y02Ions) == 0){
    $include['y02Ions'] = 0;
  }
  /*
  echo "<pre>"; 
  print_r($b0Ions);
  echo "</pre>"; exit;
  */
  return 1;
}

/******************************************************************************
 &tolVal()
 $_[0] tolerance to be converted
 globals:
 my(%parameters);
 returns tolerance in Da
*******************************************************************************/

function tolVal($tolerance){
  global $parameters;
  $scaleMass = $tolerance;
  if($parameters['itolu'] == "%"){
    return $scaleMass * $parameters['itol'] / 100;
  }elseif($parameters['itolu'] == "Da"){
    return $parameters['itol'];
  }elseif($parameters['itolu'] == "ppm"){
    return $scaleMass * $parameters['itol'] / 1000000;
  }elseif($parameters['itolu'] == "mmu"){
    return $parameters['itol'] / 1000;
  } else {
    fatalError("Unrecognised fragment mass tolerance unit", __LINE__);
  }
}

/******************************************************************************
 findMatches()
 no parameters
 globals:
 my (@massList, @temp_masses, @intensityList, %query,
   @summer, @typeList, @exp_masses, %labelList, %labels);
*******************************************************************************/

function cmp($a, $b){
  if($a == $b)  return 0;
  return ($a < $b) ? -1 : 1;
}

function cmp2($a, $b){
  global $int_mass;
  if($int_mass[$a] == $int_mass[$b]){
    if($a == $b)  return 0;
    return ($a < $b) ? -1 : 1;
  }
  return ($int_mass[$b] < $int_mass[$a]) ? -1 : 1;
}

function findMatches(){
  global $matchList, $debug, $peaks,  $numRes, $ignoreMass; 
  global $massList, $temp_masses, $intensityList, $queryArr, $summer, $typeList, $exp_masses, $labelList, $labels;
  
  $matched_calc = array();
  $matched_exp = array();
  $matched_int = array();  
  
# return if no mass data (e.g. from sequence query)
  if(!$massList[0]){
    $matchList = "";
    return 0;
  }

# sort calc vals by mass
  $calc_masses = $temp_masses;
  usort($calc_masses, "cmp");
  

  if($debug == 'FALSE'){
  # make a copy of the full experimental mass list, sorted by mass
  # and put the corresponding intensities in a hash
    for ($i=0; $i<count($massList); $i++){
      $int_mass[$massList[$i]]= $intensityList[$i];
    }
    $fullMassList = $massList;
    usort($fullMassList, "cmp");
  }
  
  if(isset($queryArr['num_used1']) && $queryArr['num_used1']){
    # new scoring scheme; matches are the first num_used values listed
    if($queryArr['num_used1'] == -1){
      $queryArr['num_used1'] = $summer[7];
      $queryArr['num_used2'] = $summer[12];
      $queryArr['num_used3'] = $summer[13];
    }
    $massList = array();
    $intensityList = array();
    $typeList = array();
  
    
    for($j=1; $j<=3; $j++){
      if(isset($queryArr["ions"."$j"]) && $queryArr["ions"."$j"]){
        $tmpString = $queryArr["ions"."$j"];
//echo "<br>\$queryArr[ions".$j."]="; 
//echo $tmpString;       
        if(preg_match('/^([by])-/i', $tmpString, $matches)){
          preg_replace('/^([by])-/i', '', $tmpString);
        }
        if(isset($matches[1]) && $matches[1]){
          $type = $matches[1];
        }else{
          $type = "";
        }
        $mass = explode(',', $tmpString);

        for($i=0; $i < $queryArr["num_used"."$j"]; $i++){
          list($tmpLeft,$tmpRight) = explode(':',$mass[$i]);
          array_push($massList, $tmpLeft);
          if (isset($tmpRight) && $tmpRight){
            array_push($intensityList, $tmpRight);
          } else {
            array_push($intensityList, 0);
          }
          array_push($typeList, $type);
        }
      }
    }
    
    if ($debug == 'FALSE' && count($massList) > 0){
    # add experimental peaks to the list of potential matches if they are of greater
    # intensity than the smaller adjacent matched peak and not on the ignore list
      $mass = $massList;
      usort($mass, "cmp");
      array_unshift($mass, 0);
      array_push($mass, 999999);
/*      
echo "<br>\$mass=";
echo "<per>"; 
print_r($mass); 
echo "</per>";
echo "<br>\$int_mass=";
echo "<per>"; 
print_r($int_mass); 
echo "</per>";
*/


      
      $int_mass[$mass[0]] = $int_mass[$mass[1]];
//echo "<br>\$int_mass[$mass[0]]=".$int_mass[$mass[1]];
      $tmplastIndex = count($mass)-1;      
      $int_mass[$mass[$tmplastIndex]] = $int_mass[$mass[$tmplastIndex-1]];
//echo "<br>\$int_mass[".$mass[$tmplastIndex]."]=".$int_mass[$mass[$tmplastIndex-1]];
      $j = 0;
      for($i=1; $i<count($mass); $i++){
        $intThresh = $int_mass[$mass[$i]];
        if($int_mass[$mass[$i-1]] < $int_mass[$mass[$i]]){
          $intThresh = $int_mass[$mass[$i-1]];
        }
        while(isset($fullMassList[$j]) && $fullMassList[$j] && $fullMassList[$j] < $mass[$i]){
          if($int_mass[$fullMassList[$j]] >= $intThresh){
            $ignoreMe = 0;
            $this_tol = tolVal($fullMassList[$j]);
            
            
            
            for ($k=0; $k<count($ignoreMass); $k++){
              if(abs($fullMassList[$j] - $ignoreMass[$k]) <=  $this_tol){
                $ignoreMe = 1;
                break;
              }
            }
            if(!$ignoreMe) {
              array_push($massList, $fullMassList[$j]);
              array_push($intensityList, $int_mass[$fullMassList[$j]]);
              array_push($typeList, "");
            }
          }
          $j++;
        }
        $j++;
      }
    }
    $peaks = count($massList);

    for ($i=1; $i<count($calc_masses); $i++){
      $matchCount[$i] = -1;
    }
    for ($j=0; $j<count($massList); $j++){
      $this_tol=&tolVal($massList[$j]);
      
      for ($i=1; $i<count($calc_masses); $i++){
        if(abs($massList[$j]-$calc_masses[$i]) <=  $this_tol){
          if($matchCount[$i] > -1){
            if($matched_int[$matchCount[$i]] < $intensityList[$j]){
              $matched_exp[$matchCount[$i]] = $massList[$j];
              $matched_int[$matchCount[$i]] = $intensityList[$j];
            }
            continue;
          }
          array_push($matched_calc, $calc_masses[$i]);
          array_push($matched_exp, $massList[$j]);
          array_push($matched_int, $intensityList[$j]);
          $matchCount[$i] = count($matched_int) - 1;
        }
      }
    }

    $exp_masses = $matched_exp;
    usort($exp_masses, "cmp");    
    $calc_masses = $matched_calc;
    usort($calc_masses, "cmp");

    # create error list to be passed to mass_error.pl
    for ($i=0; $i<count($exp_masses); $i++){
      $delta_masses[$i] = $exp_masses[$i] - $calc_masses[$i];
    }

    # concatenate string  of "$label, exp_mass, ..." to be passed to msms_gif.pl
    # and select %labels{calc_mass} from %labelList for highlighting the printed table
    $matchList="";
    $i = 0;
    while(isset($calc_masses[$i]) && $calc_masses[$i]){
    # commented out lines suppress matching to complete peptide
    #  if ($labelList{$calc_masses[$i]} =~ /\($numRes\)/) {
    #    splice @exp_masses, $i, 1;
    #    splice @calc_masses, $i, 1;
    #  } else {
    
        $matchList .= $labelList["$calc_masses[$i]"].",".sprintf("%.2f",$exp_masses[$i]).",";
        $labels["$calc_masses[$i]"] = $labelList["$calc_masses[$i]"];
        $i++;
    #  }
    }
/*    
echo "<pre>";
print_r($labels);
print_r($labelList);
echo "</pre>";exit;
*/
    if($matchList){
      $len = strlen($matchList);
      $matchList = substr($matchList, 0, $len-1);
    }
    return 1;
  } 
    
# peak matching for old scoring scheme
# sort exp vals by descending intensity (problem if any exact duplicate masses)
  for ($i=0; $i<count($massList); $i++){
    $int_mass[$massList[$i]] = $intensityList[$i];
  }
  //@exp_masses = sort { $int_mass{$b} <=> $int_mass{$a} || $a <=> $b } @massList;
  usort($calc_masses, "cmp2");
    
  $matched_masses[0] = 0;
  for ($i=0; $i<$peaks; $i++){
    $this_tol = tolVal($exp_masses[$i]);
    $j=1;
    $matches_by_exp[$exp_masses[$i]] = array();
    while($calc_masses[$j]){
      if(abs($exp_masses[$i]-$calc_masses[$j]) <=  $this_tol){
        array_push($matches_by_exp[$exp_masses[$i]], $calc_masses[$j]);
        $matches_by_calc[$calc_masses[$j]] = $exp_masses[$i];
        array_push($matched_masses, $calc_masses[$j]);
        array_splice($calc_masses, $j, 1);
      }else{
        $j++;
      }
    }
    if(!$matches_by_exp[$exp_masses[$i]] || !is_array($matches_by_exp[$exp_masses[$i]])){
      for ($k=1; $k<count($matched_masses); $k++){
        if(abs($exp_masses[$i]-$matched_masses[$k]) <=  $this_tol){
          $other_exp_mass = $matches_by_calc[$matched_masses[$k]];
          if(isset($matches_by_exp[$other_exp_mass][1]) && $matches_by_exp[$other_exp_mass][1]){
            array_push($matches_by_exp[$exp_masses[$i]], $matched_masses[$k]);
            for ($m=0; $m<count($matches_by_exp[$other_exp_mass]); $m++){
              if($matches_by_exp[$other_exp_mass][$m] == $matched_masses[$k]){
                array_splice($matches_by_exp[$other_exp_mass], $m, 1);
                break;
              } 
            }
            $matches_by_calc[$matched_masses[$k]] = $exp_masses[$i];
            break;
          }
        }
      }
    }
  }
    
# sort match list to eliminate any crossed matches
  /*
  unset($calc_masses);
  unset($exp_masses);
  unset($temp_masses);
  unset($intensityList);
  */
  $calc_masses = '';
  $exp_masses = '';
  $temp_masses = '';
  $intensityList = '';
  
  foreach($matches_by_exp as $matchKey => $matchValue){
    array_push($temp_masses, $matchKey);
    array_push($intensityList, $matchValue[0]);
  }
  $exp_masses = $temp_masses;
  usort($exp_masses, "cmp");
  $calc_masses = $intensityList;
  usort($calc_masses, "cmp");

  # create error list to be passed to mass_error.pl
  for ($i=0; $i<count($exp_masses); $i++){
    $delta_masses[$i] = $exp_masses[$i] - $calc_masses[$i];
  }

# concatenate string  of "$label, exp_mass, ..." to be passed to msms_gif.pl
# and select %labels{calc_mass} from %labelList for highlighting the printed table
  $matchList = "";
  for ($i=0; $i<count($exp_masses); $i++){
    $matchList .= $labelList["$calc_masses[$i]"].",".sprintf("%.2f",$exp_masses[$i]).",";
    $labels["$calc_masses[$i]"] = $labelList["$calc_masses[$i]"];
  }
  if($matchList){
    $len = strlen($matchList);
    $matchList = substr($matchList, 0, $len-1);
  }
  return 1;
}

/******************************************************************************
 &getItem()
 $_[0] is an accession number
 $_[1] is the argument to ms-getseq.exe
 globals:
 my(%parameters, $seqReport);
*******************************************************************************/
function incode_specials($matches){
  return sprintf("%%%02x", ord($matches[1]));
}

function getItem($accessionNumber,$exeArgu){
  global $parameters, $seqReport;

  $label = $parameters['db']."_SEQ";
  $tempString = getConfigParam("WWW",$label) || fatalError("could not find $label in WWW section of mascot.dat", __LINE__);
  $tempString = preg_replace('/$label /', '', $tempString);
  
  preg_match_all('/\"(.+?)\"/', $tempString, $outArr, PREG_PATTERN_ORDER);
  list($parseRule,$host,$service,$file) = $outArr[1];
  if($host == "localhost"){
    if(preg_match('/^\"/', $accessionNumber)){
      $accession = $accessionNumber;
    }else{ 
      $accession = "\"".$accessionNumber."\"";
    }
  } else {
    $accession = preg_replace('/^\"(.*)\"$/', '\1', $accessionNumber);
    $accession = preg_replace_callback('|(\W)|', "incode_specials", $accession);
  }
  $file = preg_replace('/#ACCESSION#([ +^])seq/i', "#ACCESSION#\\1$tmp", $file);
  
  
  $file = preg_replace('/#ACCESSION#([ +])seq/i', "#ACCESSION#\\1$exeArgu", $file);
  $file = preg_replace('/#ACCESSION#/', "$accession", $file);
  $file = preg_replace('/#FRAME#/', "0", $file);
  
  if(getReport($host,$service,$file)) {
    if ($seqReport) {
      return 1;
    }
  }
  return 0;
}

/*****************************************************************************
# &getConfigParam()
# $mascotDatSection is section in mascot.dat
# $mascotDatLabel is label in mascot.dat
# globals:
# my(@configFile);
# on success, returns line from mascot.dat
******************************************************************************/

function getConfigParam($mascotDatSection, $mascotDatLabel){
  global $configFile;
  
  $inWrongSection = 0;
  $inRightSection = 0;
  $section = $mascotDatSection;
  $label = $mascotDatLabel;
  for($i = 0; $i <count($configFile); $i++){    
    if($inWrongSection == 0 && $inRightSection == 0){
      if(preg_match('/^(databases|parse|www|taxonomy_\d+|cluster|unigene|options|cron)[\s\n#]/i', $configFile[$i])){
        if(preg_match('/^$section[\s\n#]/i', $configFile[$i])){
          $inRightSection = 1;
        } else {
          $inWrongSection = 1;
        }
      }
    }elseif($inRightSection){
      if(preg_match('/^$label[\s\n=#]/i', $configFile[$i])){
        return $configFile[$i];
      }else{
        if(preg_match('/^end[\s\n#]/i', $configFile[$i])){
          return 0;
        }
      }
    }else{
      if(preg_match('/^end[\s\n#]/i', $configFile[$i])){
        $inWrongSection = 0;
      }
    }
  }
  return 0;
}

/******************************************************************************
# &getReport()
# $hostArgu host
# $serviceArgu service
# $fileArgu file
# globals:
# my($seqReport);
******************************************************************************/

/*function getReport($hostArgu,$serviceArgu,$fileArgu){
  global $seqReport;
my(
  $auth,
  $buffer,
  $host,
  $password,
  $req,
  $result,
  $tempString,
  $ua,
  $user
  );

  if ($hostArgu eq "localhost"){
    open SOCK, $fileArgu." |"
      || return 0;
    binmode(SOCK);
    $seqReport = "";
    while(read(SOCK, $buffer, 1048576)){
      $seqReport .= $buffer;
    }
    close (SOCK) 
      || return 0;
  } else {
    $ua = new LWP::UserAgent;
    if ($hostArgu =~ /@/) {
      ($auth,$host) = explode('@',$hostArgu);
      ($user,$password) = explode(':',$auth);
      $req = new HTTP::Request GET => "http://$host:$serviceArgu$fileArgu";
      $req->authorization_basic($user, $password);
    } else {
      $host = $hostArgu;
      $req = new HTTP::Request GET => "http://$host:$serviceArgu$fileArgu";
    }    
    if (($tempString = &getConfigParam("Options","proxy_server")) ||
      ($tempString = &getConfigParam("WWW","proxy_server"))) {
      $tempString =~ s/proxy_server\s+//i;
      if ($tempString) {
        $ua->proxy('http' => $tempString);
        $user = "" unless (($user = &getConfigParam("Options","proxy_username")) ||
          ($user = &getConfigParam("WWW","proxy_username"))) ;
        $user =~ s/proxy_username\s+//i;
        $password = "" unless (($password = &getConfigParam("Options","proxy_password")) ||
          ($password = &getConfigParam("WWW","proxy_password"))) ;
        $password =~ s/proxy_password\s+//i;
        if ($user || $password) {
          $req->proxy_authorization_basic($user, $password);
        }
      } else {
        $ua->env_proxy;   # initialize from environment variables
      }
    } else {
      $ua->env_proxy;   # initialize from environment variables
    }
    $result = $ua->request($req);
    if ($result->is_success) {
      $seqReport = $result->content;
    } else {
      $seqReport = "Failed to retrieve report using http://$host:$serviceArgu$fileArgu\n";
      $seqReport .= "                " . $result->status_line . "\n";
      return 0;
    }
  }
  return 1;
}*/




/******************************************************************************
 function getIndex()
 no parameters
 globales:
 $fields, $boundary, $indexArr);
 parse index block into %index
 labels are all set to lower case
 this routine is very picky about the structure of the data file
 first line must match exactly apart from trailing white space
 final non-blank line must be a boundary
 spurious blank lines in body of file may cause problems 
*******************************************************************************/

function getIndex(){
  global $fields, $boundary, $indexArr;

  $fields[0] = trim($fields[0]);  // delete trailing white space
  if($fields[0] != 'MIME-Version: 1.0 (Generated by Mascot version 1.0)')    return 0;
  
  $fields[1] = trim($fields[1]);
  if(preg_match('/boundary=(.+)$/', $fields[1], $matches)){
    $boundary = "--" . $matches[1];
  }
  end($fields);  // work back from end of file looking for boundary
  $i = key($fields); 
  while(!preg_match('/^'.$boundary.'/', $fields[$i])){
    $i--;
    if ($i<-30) {
      return 0;
    }
  }
  $i--;    // work back to next boundary
  while (!preg_match('/^'.$boundary.'/', $fields[$i])){
    $fields[$i] = trim($fields[$i]);  // delete trailing white space
    if($fields[$i]){
      $tmpArr = explode('=', $fields[$i]);
      $indexArr[strtolower($tmpArr[0])] = $tmpArr[1];
    }
    $i--;
  }
  return 1;
}



/***************************************************************************
 &unBlock()
 $_[0] $blockName
 $_[1] \%query
 $_[2] \@massList
 $_[3] \@intensityList
 $_[4] \@typeList
 globals:
 my(%index, @fields, $boundary);
 can call with first 2 arguments only or all 5
 nb labels are all set to lower case
****************************************************************************/
function unBlock($blockName, &$blockArr, &$massList='', &$intensityList='', &$typeList=''){
  global $indexArr, $fields, $boundary;
  $type = '';
  $tmpString = '';
  $i = $indexArr[strtolower($blockName)];  //offset of first line of block
  if($i<4) return 0;        // lowest offset cannot be <4
  $i++;
  $i++;                      // skip blank line
  while (!preg_match('/^'.$boundary.'/', $fields[$i])){
    $fields[$i] = trim($fields[$i]); // delete trailing white space
    //($label,$value) = explode('=', $fields[$i] ,2);
    $tmpArr = explode('=', $fields[$i] ,2);
    $blockArr[strtolower($tmpArr[0])] = $tmpArr[1];
    $i++; 
  }
/*echo "<pre>$blockName=";
print_r($blockArr); 
echo "</pre>";*/   
  if($typeList !== ''){
    for($j = 1; $j <= 3; $j++){ 
      if(isset($blockArr["ions".$j]) && $blockArr["ions".$j]){
      //if(defined(${$_[1]}{"ions"."$j"}) && ${$_[1]}{"ions"."$j"} gt ""){
        $tmpString = $blockArr["ions".$j];
//echo "<br>\$tmpString"; 
//echo $tmpString;               
      //$tmpString =~ s/^([by])-//i;
        if(preg_match('/^([by])-/i', $tmpString, $matches)){
          preg_replace('/^([by])-/i', '', $tmpString);
        }
      //$type = "" unless ($type = $1);
        if(isset($matches[1]) && $matches[1]){
          $type = $matches[1];
        }else{
          $type = "";
        }
      //@mass = $tmpString =~ /(.*?),/g;
        $mass = explode(',', $tmpString);
/*echo "<pre>";
echo "\$mass***********************";
print_r($mass);
echo "</pre>";*/
      /*$i=0;
        while($mass[$i]){
          ($tmpLeft,$tmpRight) = (split(/:/,$mass[$i]));
          push @{$_[2]}, $tmpLeft;
          if (defined($tmpRight)) {
            push @{$_[3]}, $tmpRight;
          } else {
            push @{$_[3]}, 0;
          }
          push @{$_[4]}, $type;
          $i++;
        }*/  
        foreach($mass as $value){
          if(!$value) continue;
          list($tmpLeft,$tmpRight) = explode(':', $value, 2);
          array_push($massList, $tmpLeft);
          if(isset($tmpRight) && $tmpRight){
            array_push($intensityList, $tmpRight);
          }else{
            array_push($intensityList, '0');
          }
          array_push($typeList, $type);
        }
      }
    }
    /*
    echo "#################################<br>";
        echo "<pre>";
        echo "\$massList";
        print_r($massList);
        echo "\$intensityList";
        print_r($intensityList);
        echo "\$typeList";
        print_r($typeList);
        echo "</pre>";
        echo "<br>#################################";
    */    
  }
  return 1;
}

//----------------------------------------------
function fatalError($msg='', $line=0){
//----------------------------------------------
  global $start_time;
  $msg  = "Fatal Error--$msg;";
  $msg .=  " Script Name: " . $_SERVER['PHP_SELF']. ";";
  $msg .= " Start time: ". $start_time . ";";
  if($line){
    $msg .= " Line number: $line;";
  }
  echo $msg;
  //writeLog($msg);
  exit;
}

/*****************************************************************************
 &noTag()
 $_[0] string which may contain HTML tags
 returns de-tagged string
*****************************************************************************/

function noTag($htmlStr){
  $pattens = array('/</', '/>/');
  $replacement = array('&lt;','&gt;');
  $temp = preg_replace($pattens, $replacement, $htmlStr);
  return $temp;
}

function noDoubleQoute($str){
  $temp = preg_replace('/"/', '', $str);
  return $temp;
}

/******************************************************************************
# &decompress()
# $_[0] filename (without .Z)
# returns filename on success
******************************************************************************/
/*
sub decompress{

  my(
  $expand,
  $inFile,
  $sysCall
  );

  $inFile = $_[0];

  if ($ENV{'WINDIR'}){
    $expand="../bin/gzip.exe";
  } else {
    $expand="compress";
  }

  if (-e "./www.pl") {
    do "./www.pl";
    $inFile = &decrypt($inFile);
    $shipper = 'FALSE';
  } else {
    $shipper = 'TRUE';
  }

  unless (-r $inFile) {
    if (-r "$inFile.Z") {
      $sysCall="$expand -d $inFile.Z";
      if ($ENV{'WINDIR'}){
        $sysCall =~ s#/#\\#g;
      }
      if(system($sysCall)){
        &fatal("Cannot expand compressed result file", __LINE__);
      }
    } else {
      &fatal("Cannot find result file", __LINE__);
    }
  }

  return $inFile;

}

*/

?>
