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
set_time_limit(3600*2);
$file    = '';
$query   = '';
$hit     = '';
$index   = '';
$indexAccession = '';
$tmp_parsed_file = '';
$tmp_parsed_file_log = '';
$field_spliter = ';;';
$double = "++";
$debug = '';
$gifParams = array();
$massHeader_array  = array();
$masses_array  = array();
$vmMass_array  = array();
$neutralLoss_array  = array();
$pepNeutralLoss_array  = array();
$reqPepNeutralLoss_array  = array();
$labels  = array();
$labelList  = array();
$seriesUsedStr = array();
$include = array();
$neutralLoss = array();
$neutralLossList = array();
$net_nl_list = array();
$left_label_list = array();
$right_label_list = array();
$var_mod_string_list = array();
$calc_masses = array();
$massList = array();
$intensityList = array();
$std_matches = array();
$std_fields = array();
$tmp = array();
$tmp_a = array();
$tmp1 = array();
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
$zhIons = array();
$zh2Ions = array();
$zhhIons = array();
$zhh2Ions = array();
$dIons = array();
$dpIons = array();
$vIons = array();
$wIons = array();
$wpIons = array();
$Ions = array();
$include['immIons'] = 0;
$include['aIons'] = 0;
$include['a2Ions'] = 0;
$include['asIons'] = 0;
$include['as2Ions'] = 0;
$include['a0Ions'] = 0;
$include['a02Ions'] = 0;
$include['bIons'] = 0;
$include['b2Ions'] = 0;
$include['bsIons'] = 0;
$include['bs2Ions'] = 0;
$include['b0Ions'] = 0;
$include['b02Ions'] = 0;
$include['cIons'] = 0;
$include['c2Ions'] = 0;
$include['xIons'] = 0;
$include['x2Ions'] = 0;
$include['yIons'] = 0;
$include['y2Ions'] = 0;
$include['ysIons'] = 0;
$include['ys2Ions'] = 0;
$include['y0Ions'] = 0;
$include['y02Ions'] = 0;
$include['zIons'] = 0;
$include['z2Ions'] = 0;
$include['intybIons'] = 0;
$include['intyaIons'] = 0;
$include['zhIons'] = 0;
$include['zh2Ions'] = 0;
$include['dIons'] = 0;
$include['dpIons'] = 0;
$include['vIons'] = 0;
$include['wIons'] = 0;
$include['wpIons'] = 0;
$include['zhhIons'] = 0;
$include['zhh2Ions'] = 0;
$gifParams['bottom']   = 10;
$gifParams['range']    = 10;
$gifParams['tick1']    = 1;
$gifParams['left']     = 20;
$gifParams['to']       = 1e99;
$gifParams['from']     = -1;
$gifParams['height']   = 300;
$gifParams['width']    = 550;
$gifParams['right']    = 20;
$gifParams['scoop']    = 2;
$gifParams['tick_int'] = 1;
$gifParams['top']      = 100;
$gifParams['gif.x']    = -1;

require("../config/conf.inc.php");
require("../common/common_fun.inc.php");
require("../msManager/is_dir_file.inc.php");

$PARAM = array_merge($_GET, $_POST);
$file    = $PARAM['file'];
$query   = $PARAM['query'];
$hit     = $PARAM['hit'];
$index   = $PARAM['index'];
$exportingParameterStr = $PARAM['expPara'];
if (sizeof($PARAM)>5){
  if (isset($PARAM['bottom']))$gifParams['bottom'] = $PARAM['bottom'];
  if (isset($PARAM['range']))$gifParams['range']   = $PARAM['range'];
  if (isset($PARAM['tick1']))$gifParams['tick1']   = $PARAM['tick1'];
  if (isset($PARAM['left']))$gifParams['left']     = $PARAM['left'];
  if (isset($PARAM['to']))$gifParams['to']         = $PARAM['to'];
  if (isset($PARAM['from']))$gifParams['from']     = $PARAM['from'];
  if (isset($PARAM['height']))$gifParams['height'] = $PARAM['height'];
  if (isset($PARAM['width']))$gifParams['width']   = $PARAM['width'];
  if (isset($PARAM['right']))$gifParams['right']   = $PARAM['right'];
  if (isset($PARAM['scoop']))$gifParams['scoop']   = $PARAM['scoop'];
  if (isset($PARAM['tick_int']))$gifParams['tick_int'] = $PARAM['tick_int'];
  if (isset($PARAM['top']))$gifParams['top']       = $PARAM['top'];
  if (isset($PARAM['gif_x']))$gifParams['gif.x']   = $PARAM['gif_x'];
}
if($file){
  if(!_is_file($file)){
    echo "the file ($file) doesn't exist";
    exit;
  }
}else{
  echo "no Mascot search results file passed";
  exit;
}

//$indexAccession = preg_replace('/gi\|/','',$index);
$indexAccession = $index;

//$tmp_parsed_dir = get_uploaded_search_results_dir('Mascot');
$tmp_parsed_dir = dirname($file);

$tmp_parsed_file = $tmp_parsed_dir."/". baseName($file)."_h".$hit."_q".$query.".tmp";
$tmp_parsed_file_log = $tmp_parsed_file . ".log";
$PROHITS_ROOT = str_replace('/analyst','',dirname($_SERVER['SCRIPT_FILENAME']));



if(!file_exists($tmp_parsed_file)){
  parse_Mascot_perl_pep($PROHITS_ROOT,$file);
  read_parsed_tmpfile($tmp_parsed_file);
   
}else {
  read_parsed_tmpfile($tmp_parsed_file);
}

// get polarity and charge
if (preg_match("/^\-+/", $observedCharge, $match)){
  $polarity = $match[1];
  $charge = $match[2];
  }else{
  $polarity = '+';
  $charge = $observedCharge;
}
if ($polarity != "+") {
  $double = "--";
}
// get Series Ions
getSeriesIons();
// get the peptide sequence string
$peptide = strtolower($pepSequences);
//get peaks
$peaks = 0;
if ($peaksIons1){
  $peaks += $peaksIons1;
}
if ($peaksIons2){
  $peaks += $peaksIons2;
}
if ($peaksIons3){
  $peaks += $peaksIons3;
}
// get masses,vmMass,vmString,$neutralLoss,$pepNeutralLoss,$pepNeutralLoss,$reqPepNeutralLoss
if($massesTmp){
  $massesTmp = substr($massesTmp, 0,-2);
  $masses_array = explode('##',$massesTmp);
  foreach($masses_array as $i){
    $tmp = explode(';;',$i);
    $masses[$tmp[0]]=$tmp[1];
  }
}
if(!isset($masses['electron']))$masses['electron'] = 0.000549;
if($vmMassTmp){
  $vmMassTmp = substr($vmMassTmp, 0,-2);
  $vmMass_array = explode('##',$vmMassTmp);
  foreach($vmMass_array as $i){
    $tmp = explode(';;',$i);
    $vmMass[$tmp[0]]=$tmp[1];
  }
}
if($varModifications){
    $vmString = explode(',',$varModifications);
}
if($neutralLossTmp){
  $neutralLossTmp = substr($neutralLossTmp, 0,-2);
  $neutralLoss_array = explode('ff',$neutralLossTmp);
  foreach($neutralLoss_array as $i){
    $tmp = explode(';;',$i);
    $t = substr($tmp[1], 0,-2);
    $tmp_a = explode('##',$t);
    foreach($tmp_a as $j){
      $tmp1 = explode('=',$j);
      $neutralLoss[$tmp[0]][$tmp1[0]]=$tmp1[1];
    }
  }
}
if($pepNeutralLossTmp){
  $pepNeutralLossTmp = substr($pepNeutralLossTmp, 0,-2);
  $pepNeutralLoss_array = explode('##',$pepNeutralLossTmp);
  foreach($pepNeutralLoss_array as $i){
    $tmp = explode(';;',$i);
    $pepNeutralLoss[$tmp[0]]=$tmp[1];
  }
}
if($reqPepNeutralLossTmp){
  $reqPepNeutralLossTmp = substr($reqPepNeutralLossTmp, 0,-2);
  $reqPepNeutralLoss_array = explode('##',$reqPepNeutralLossTmp);
  foreach($reqPepNeutralLoss_array as $i){
    $tmp = explode(';;',$i);
    $reqPepNeutralLoss[$tmp[0]]=$tmp[1];
  }
}

// for both matched and unmatched queries
if ($massMin and $massMax and $massMax > $massMin){
  // work out zoom state for gif
  if ($gifParams['gif.x'] == -1){
    // first time through or user clicked on submit button
    if ($gifParams['from'] < $massMin){
      $gifParams['from'] = $massMin;
    }
    if ($gifParams['to'] > $massMax){
      $gifParams['to'] = $massMax;
    }
    if ($gifParams['to'] <= $gifParams{'from'}){
      $gifParams['from'] = $massMin;
      $gifParams['to'] = $massMax;
    }
    list($gifParams['tick_int'], $gifParams['tick1'], $gifParams['range']) =
        calcRange($gifParams['from'], $gifParams['to']);
    if ($gifParams['range'] < 10){
      $gifParams['range'] = 10;
      $gifParams['tick_int'] = 1;
    }
  } else {
    // user clicked on gif to zoom in factor of 2
    // work out the mass value of the mouse click
    $xScale = ($gifParams['width'] - $gifParams['left'] - $gifParams['right']) / $gifParams['range'];
    $newMass = (($gifParams['gif.x'] - $gifParams['left']) / $xScale) + $gifParams{'tick1'};
    // want to zoom in about this mass, while staying within the mass range of the data
    if (($newMass-$gifParams['range'] / 4) < $massMin){
      $gifParams['from'] = $massMin;
    } else {
      $gifParams['from'] = $newMass - $gifParams['range'] / 4;
    }
    if (($gifParams['from'] + $gifParams['range'] / 2) > $massMax){
      $gifParams['to'] = $massMax;
    } else {
      $gifParams['to'] = $gifParams['from'] + $gifParams['range'] / 2;
    }
    if (($gifParams['to'] - $gifParams['range'] / 2) < $gifParams['from']){
      if (($gifParams['to'] - $gifParams['range'] / 2) >= $massMin){
        $gifParams['from'] = ($gifParams['to'] - $gifParams['range'] / 2);
      } else {
        $gifParams['from'] = $massMin;
      }
    }
    list($gifParams['tick_int'], $gifParams['tick1'], $gifParams['range']) = calcRange($gifParams['from'], $gifParams['to']);
    if ($gifParams['range'] < 10){
      $gifParams['range'] = 10;
      $gifParams['tick_int'] = 1;
    }
  }
  $gifParams['lasttick'] = $gifParams['tick1'] + $gifParams['range'];
}
$gifParams['from'] = (int)($gifParams['from'] * 100) / 100;
$gifParams['to'] = (int)($gifParams['to'] * 100) / 100;

// calculate fragment ion masses
// if there are variable mods, they will be included in the running sum
calcIons();

// sort calc vals by mass
sort($calc_masses);
//print_r($calc_masses);
// find matches to experimental data. Either ions
findMatches();

function parse_Mascot_perl_pep($PROHITS_ROOT, $file){
  global $tmp_parsed_file;
  global $tmp_parsed_file_log;
  global $query;
  global $hit;
  global $index;
  global $indexAccession;
  global $exportingParameterStr;
  if (!defined('PERL_58')) {
    define("PERL_58", "perl");
  }
  if(!check_mascot_parser()){
    echo "Mascot parser doesn't work. Please setup mascot parser.";
    exit;
  }else{
    $com = "cd $PROHITS_ROOT/MascotParser/scripts; ".PERL_58." ProhitsMascotParserExt.pl $file $exportingParameterStr '$indexAccession' '$tmp_parsed_file' $hit $query > '$tmp_parsed_file_log' 2>&1";
    //echo $com;exit;
    
    
    

    system($com);
    $tmp_arr = file("$tmp_parsed_file_log");
    if($tmp_arr != Array ()){
      echo "Parsing not success. For detail, see file: $tmp_parsed_file_log";
      exit;
    }
  }
}
Function read_parsed_tmpfile($tmp_parsed_file){
  global $pepSequences;
  global $proDesc;
  global $pepExpects;
  global $pepMZ;
  global $title;
  global $msDataFile;
  global $typeMass;
  global $pepMass;
  global $fixedModifications;
  global $varModifications;
  global $pepScores;
  global $pepExpect;
  global $matchesRB;
  global $massHeader_array;
  global $masses_array;
  global $massMin;
  global $massMax;
  global $field_spliter;
  global $index;
  global $query;
  global $typeList;
  global $varModsString;
  global $observedCharge;
  global $rules;
  global $internals;
  global $seriesUsedStr;
  global $peaks;
  global $massesTmp;
  global $vmMassTmp;
  global $vmStringTmp;
  global $neutralLossTmp;
  global $pepNeutralLossTmp;
  global $reqPepNeutralLossTmp;
  global $num_used1;
  global $num_used2;
  global $num_used3;
  global $Ions;
  global $peaksIons1;
  global $peaksIons2;
  global $peaksIons3;
  global $massList;
  global $intensityList;
  global $tol;
  global $tolU;
  global $iTol;
  global $iTolU;
  global $ignoreMass;
  global $std_matches;
  $massList_tmp = "";
  $intensityList_tmp = "";
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
        if(preg_match('/^Title\s*:(.*)$/i', $buffer, $matches)) $title = trim($matches[1]);
        if(preg_match('/^MS\s?data\s?file\s*:(.*)$/i', $buffer, $matches)) $msDataFile = trim($matches[1]);
        if(preg_match('/^Mass\s?values\s*:(.*)$/i', $buffer, $matches)) $typeMass = trim($matches[1]);
        if(preg_match('/^Fixed\s?Modifications\s*:(.*)$/i', $buffer, $matches)) $fixedModifications = trim($matches[1]);
        if(preg_match('/^Variable\s?Modifications\s*:(.*)$/i', $buffer, $matches)) $varModifications = trim($matches[1]);
        if(preg_match('/^Peptide\s?Mass\s?Tolerance\s*:(.*)$/i', $buffer, $matches)){
          $tolerance= trim($matches[1]);
          $tmp = preg_split("/[\s,]+/",$tolerance);
          $tol = $tmp[0];
          $tolU = $tmp[1];
        }
        if(preg_match('/^Fragment\s?Mass\s?Tolerance\s*:(.*)$/i', $buffer, $matches)){
          $itolerance = trim($matches[1]);
          $tmp = preg_split("/[\s,]+/",$itolerance);
          $iTol = $tmp[0];
          $iTolU = $tmp[1];
        }
        if(preg_match('/^MassMin\s*:(.*)$/i', $buffer, $matches)) $massMin = trim($matches[1]);
        if(preg_match('/^MassMax\s*:(.*)$/i', $buffer, $matches)) $massMax = trim($matches[1]);
        if(preg_match('/^MassList:(.*)$/i', $buffer, $matches)) {
          $massList_tmp = trim($matches[1]);
          $massList = preg_split("/[\s,]+/",$massList_tmp);
        }
        if(preg_match('/^IntensityList:(.*)$/i', $buffer, $matches)){
          $intensityList_tmp = trim($matches[1]);
          $intensityList = preg_split("/[\s,]+/",$intensityList_tmp);
        }
        if(preg_match('/^TypeList\s*:(.*)$/i', $buffer, $matches)) $typeList = trim($matches[1]);
        if(preg_match('/^IgnoreMass:(.*)$/i', $buffer, $matches)){
          $tmp = trim($matches[1]);
          $ignoreMass = preg_split("/[\s,]+/",$tmp);
        }
        if(preg_match('/^StdMatches:(.*)$/i', $buffer, $matches)){
          $tmp = trim($matches[1]);
          $std_matches = preg_split("/\s/",$tmp);
        }
        if(preg_match('/^VarModsString\s*:(.*)$/i', $buffer, $matches)) $varModsString = trim($matches[1]);
        if(preg_match('/^ObservedCharge\s*:(.*)$/i', $buffer, $matches)) $observedCharge = trim($matches[1]);
        if(preg_match('/^Rules\s*:(.*)$/i', $buffer, $matches)) $rules = trim($matches[1]);
        if(preg_match('/^Internals\s*:(.*)$/i', $buffer, $matches)) $internals = trim($matches[1]);
        if(preg_match('/^SeriesUsedStr\s*:(.*)$/i', $buffer, $matches)) $seriesUsedStr = trim($matches[1]);
        if(preg_match('/^Num_used1\s*:(.*)$/i', $buffer, $matches)) $num_used1 = trim($matches[1]);
        if(preg_match('/^Num_used2\s*:(.*)$/i', $buffer, $matches)) $num_used2 = trim($matches[1]);
        if(preg_match('/^Num_used3\s*:(.*)$/i', $buffer, $matches)) $num_used3 = trim($matches[1]);
        if(preg_match('/^Ions1\s*:(.*)$/i', $buffer, $matches)) $Ions[1] = trim($matches[1]);
        if(preg_match('/^Ions2\s*:(.*)$/i', $buffer, $matches)) $Ions[2] = trim($matches[1]);
        if(preg_match('/^Ions3\s*:(.*)$/i', $buffer, $matches)) $Ions[3] = trim($matches[1]);
        if(preg_match('/^PeaksIons1\s*:(.*)$/i', $buffer, $matches)) $peaksIons1 = trim($matches[1]);
        if(preg_match('/^PeaksIons2\s*:(.*)$/i', $buffer, $matches)) $peaksIons2 = trim($matches[1]);
        if(preg_match('/^PeaksIons3\s*:(.*)$/i', $buffer, $matches)) $peaksIons3 = trim($matches[1]);
        if(preg_match('/^Masses\s*:(.*)$/i', $buffer, $matches)) $massesTmp = trim($matches[1]);
        if(preg_match('/^VmMass\s*:(.*)$/i', $buffer, $matches)) $vmMassTmp = trim($matches[1]);
        if(preg_match('/^VmString\s*:(.*)$/i', $buffer, $matches)) $vmStringTmp = trim($matches[1]);
        if(preg_match('/^NeutralLoss\s*:(.*)$/i', $buffer, $matches)) $neutralLossTmp = trim($matches[1]);
        if(preg_match('/^PepNeutralLoss\s*:(.*)$/i', $buffer, $matches)) $pepNeutralLossTmp = trim($matches[1]);
        if(preg_match('/^ReqPepNeutralLoss\s*:(.*)$/i', $buffer, $matches)) $reqPepNeutralLossTmp = trim($matches[1]);
        //get protein info
        //HitNumber;;ProteinID;;ProteinMass;;ProteinScore;;PeptidesMatched;;ProteinDesc;;Coverage;;Expect;;Threshold
        if(preg_match("/^Hit_;;/i", $buffer)){
          $tmp_array = explode($field_spliter, $buffer);
          $proMasses = $tmp_array[2];
          $proScores = $tmp_array[3];
          $proMatched = $tmp_array[4];
          $proDesc = $tmp_array[5];
          $proCoverage = $tmp_array[6];
          $proExpect = $tmp_array[7];
        }
        //QueryNumber;;Observed(MZ);;Mr(expt);;Mr(calc);;Delta;;Miss;;Score;;Start;;End;;Rank;;Peptide;;Modification;;Status;;IonFile;;PepExpect
        if(preg_match("/^$query/", $buffer)){
          $tmp_array = explode($field_spliter, $buffer);
          $pepMZ       = $tmp_array[1];
          $pepExpects  = $tmp_array[2];
          $pepMass     = $tmp_array[3];
          $pepDelta    = $tmp_array[4];
          $missed_clg  = $tmp_array[5];
          $pepScores   = $tmp_array[6];
          $pepStart    = $tmp_array[7];
          $pepEnd      = $tmp_array[8];
          $pepRank     = $tmp_array[9];
          $pepSequences= $tmp_array[10];
          $pepModifications= $tmp_array[11];
          $tmpPep_array = explode(",",$pepModifications);
          if(preg_match('/^1\s/i', $tmpPep_array[0], $matches)){
	          $pepModifications = preg_replace('/1/','',$tmpPep_array[0]);
	        }else{
            $pepModifications = $tmpPep_array[0];
          }
          $pepExpect= $tmp_array[14];
        }
      }
    }
  }
}


function calcRange($minValue,$maxValue){
  $valueRange = 0;
  $valueRange = $maxValue - $minValue;
  $i = 0;
  while (pow(10, $i) < $valueRange){
    $i++;
  }
  // drop a power of 10 & find ceiling
  $j = 10;
  $i = pow(10,($i - 1));
  while ($j * $i > $valueRange){
    $j--;
  }
  // drop another power of 10 & find ceiling
  $j = ($j + 1) * 10;
  $i = $i / 10;
  while ($j * $i > $valueRange){
    $j--;
  }
  // increase $tickInterval to get between 10 and 25 ticks
  $tickInterval = $i;
  $numTicks = $j + 2;
  if ($numTicks > 50) {
    $tickInterval *= 5;
  }elseif ($numTicks > 20) {
    $tickInterval *= 2;
  }
  $firstTick = (int)($minValue / $tickInterval) * $tickInterval;
  while(($firstTick) > $minValue){
    $firstTick -= $tickInterval;
  }
  $displayRange = (int)(($maxValue - $firstTick) / $tickInterval) * $tickInterval;
  while(($firstTick + $displayRange) < $maxValue){
    $displayRange += $tickInterval;
  }
  return array($tickInterval,$firstTick,$displayRange);
}
function getSeriesIons(){
  global $seriesUsedStr;
  global $seriesStr;
  if ($seriesUsedStr) {
    $seriesStr['iatol'] = $seriesUsedStr[0];
    $seriesStr['iastol'] = $seriesUsedStr[1];
    $seriesStr['ia2tol'] = $seriesUsedStr[2];
    $seriesStr['ibtol'] = $seriesUsedStr[3];
    $seriesStr['ibstol'] = $seriesUsedStr[4];
    $seriesStr['ib2tol'] = $seriesUsedStr[5];
    $seriesStr['iytol'] = $seriesUsedStr[6];
    $seriesStr['iystol'] = $seriesUsedStr[7];
    $seriesStr['iy2tol'] = $seriesUsedStr[8];
    $seriesStr['ictol'] = $seriesUsedStr[9];
    $seriesStr['ic2tol'] = $seriesUsedStr[10];
    $seriesStr['ixtol'] = $seriesUsedStr[11];
    $seriesStr['ix2tol'] = $seriesUsedStr[12];
    $seriesStr['iztol'] = $seriesUsedStr[13];
    $seriesStr['iz2tol'] = $seriesUsedStr[14];
    if (strlen($seriesUsedStr) > 15) {
      $seriesStr['izhtol'] = $seriesUsedStr[15];
      $seriesStr['izh2tol'] = $seriesUsedStr[16];
    }
    if (strlen($seriesUsedStr) > 17) {
      $seriesStr['izhhtol'] = $seriesUsedStr[17];
      $seriesStr['izhh2tol'] = $seriesUsedStr[18];
    }
    getRules();
  }
}
function getRules(){
  global $rules;
  global $internals;
  global $MinInternalMass;
  global $MaxInternalMass;
  global $charge;
  global $include;
  $MinInternalMass = 0.0;
  $MaxInternalMass = 700.0;
  $tmp = array();
  $specified = array();
  $chosen = array();
  if ($internals) {
    $tmp = preg_split('/,/', $internals);
    $MinInternalMass = $tmp[0];
    $MaxInternalMass = $tmp[1];
  }
  if (!$rules) {$rules = "1,2,5,6,8,9,13,14";}
  $specified = preg_split('/,/', $rules);
  for ($i = 1; $i <=25; $i++) {
    $chosen[$i] = 0;
  }
  for ($i = 0; $i < sizeof($specified); $i++) {
    $chosen[$specified[$i]] = 1;
  }
  if ($chosen[4]) {$include['immIons'] = 1;}
  if ($chosen[5]) {$include['aIons'] = 1;}
  if ($chosen[5] && $chosen[2] && $charge > 1) {$include['a2Ions'] = 1;}
  if ($chosen[5] && $chosen[3] && $charge > 2) {$include['a2Ions'] = 1;}
  if ($chosen[6]) {$include['asIons'] = 1;}
  if ($chosen[6] && $chosen[2] && $charge > 1) {$include['as2Ions'] = 1;}
  if ($chosen[6] && $chosen[3] && $charge > 2) {$include['as2Ions'] = 1;}
  if ($chosen[7]) {$include['a0Ions'] = 1;}
  if ($chosen[7] && $chosen[2] && $charge > 1) {$include['a02Ions'] = 1;}
  if ($chosen[7] && $chosen[3] && $charge > 2) {$include['a02Ions'] = 1;}
  if ($chosen[8]) {$include['bIons'] = 1;}
  if ($chosen[8] && $chosen[2] && $charge > 1) {$include['b2Ions'] = 1;}
  if ($chosen[8] && $chosen[3] && $charge > 2) {$include['b2Ions'] = 1;}
  if ($chosen[9]) {$include['bsIons'] = 1;}
  if ($chosen[9] && $chosen[2] && $charge > 1) {$include['bs2Ions'] = 1;}
  if ($chosen[9] && $chosen[3] && $charge > 2) {$include['bs2Ions'] = 1;}
  if ($chosen[10]) {$include['b0Ions'] = 1;}
  if ($chosen[10] && $chosen[2] && $charge > 1) {$include['b02Ions'] = 1;}
  if ($chosen[10] && $chosen[3] && $charge > 2) {$include['b02Ions'] = 1;}
  if ($chosen[11]) {$include['cIons'] = 1;}
  if ($chosen[11] && $chosen[2] && $charge > 1) {$include['c2Ions'] = 1;}
  if ($chosen[11] && $chosen[3] && $charge > 2) {$include['c2Ions'] = 1;}
  if ($chosen[12]) {$include['xIons'] = 1;}
  if ($chosen[12] && $chosen[2] && $charge > 1) {$include['x2Ions'] = 1;}
  if ($chosen[12] && $chosen[3] && $charge > 2) {$include['x2Ions'] = 1;}
  if ($chosen[13]) {$include['yIons'] = 1;}
  if ($chosen[13] && $chosen[2] && $charge > 1) {$include['y2Ions'] = 1;}
  if ($chosen[13] && $chosen[3] && $charge > 2) {$include['y2Ions'] = 1;}
  if ($chosen[14] && !$chosen[16]) {$include['ysIons'] = 1;}
  if ($chosen[14] && !$chosen[16] && $chosen['2'] && $charge > 1) {$include['ys2Ions'] = 1;}
  if ($chosen[14] && !$chosen[16] && $chosen['3'] && $charge > 2) {$include['ys2Ions'] = 1;}
  if ($chosen[15]) {$include['y0Ions'] = 1;}
  if ($chosen[15] && $chosen[2] && $charge > 1) {$include['y02Ions'] = 1;}
  if ($chosen[15] && $chosen[3] && $charge > 2) {$include['y02Ions'] = 1;}
  if ($chosen[16]) {$include['zIons'] = 1;}
  if ($chosen[16] && $chosen[2] && $charge > 1) {$include['z2Ions'] = 1;}
  if ($chosen[16] && $chosen[3] && $charge > 2) {$include['z2Ions'] = 1;}
  if ($chosen[17]) {$include['intybIons'] = 1;}
  if ($chosen[18]) {$include['intyaIons'] = 1;}
  if ($chosen[21]) {$include['zhIons'] = 1;}
  if ($chosen[21] && $chosen[2] && $charge > 1) {$include['zh2Ions'] = 1;}
  if ($chosen[21] && $chosen[3] && $charge > 2) {$include['zh2Ions'] = 1;}
  if ($chosen[22]) {$include['dIons'] = 1; $include['dpIons'] = 1;}
  if ($chosen[23]) {$include['vIons'] = 1;}
  if ($chosen[24]) {$include['wIons'] = 1; $include['wpIons'] = 1;}
  if ($chosen[25]) {$include['zhhIons'] = 1;}
  if ($chosen[25] && $chosen[2] && $charge > 1) {$include['zhh2Ions'] = 1;}
  if ($chosen[25] && $chosen[3] && $charge > 2) {$include['zhh2Ions'] = 1;}
}
Function calcIons(){
  global $neutralLossList;
  global $neutralLoss_C_Term;
  global $neutralLoss_N_Term;
  global $net_nl_list;
  global $left_label_list;
  global $right_label_list;
  global $var_mod_string_list;
  global $runningSum;
  global $masses;
  global $vmMass;
  global $neutralLoss;
  global $calc_masses;
  global $varModsString;
  global $peptide;
  global $polarity;
  global $include;
  global $double;
  global $numCalcVals;
  global $labelList;
  global $aIons;
  global $a2Ions;
  global $asIons;
  global $as2Ions;
  global $a0Ions;
  global $a02Ions;
  global $bIons;
  global $b2Ions;
  global $bsIons;
  global $bs2Ions;
  global $b0Ions;
  global $b02Ions;
  global $cIons;
  global $c2Ions;
  global $xIons;
  global $x2Ions;
  global $yIons;
  global $y2Ions;
  global $ysIons;
  global $ys2Ions;
  global $y0Ions;
  global $y02Ions;
  global $zIons;
  global $z2Ions;
  global $zhIons;
  global $zh2Ions;
  global $zhhIons;
  global $zhh2Ions;
  global $dIons;
  global $dpIons;
  global $vIons;
  global $wIons;
  global $wpIons;
  global $immIons;
  global $intyaIons;
  global $intybIons;
  // calculate the running sum of the residue masses, including any variable mods
  $runningSum[0] = $masses[$peptide[0]];
  $temp = substr($varModsString, 1, 1);
  if (preg_match("/[1-9A-X]/",$temp,$match)){
    $runningSum[0] += $vmMass[$temp];
    $neutralLossList[0] = $neutralLoss[$temp][0];
  } else if (isset($neutralLoss[$peptide[0]])) {
    $neutralLossList[0] = $neutralLoss[$peptide[0]][0];
  }else{  
    $neutralLossList[0] = 0;
  }
  for ($i = 1; $i < strlen($peptide); $i++){
    $runningSum[$i] = $runningSum[$i-1] + $masses[$peptide[$i]];
    $temp = substr($varModsString, $i+1, 1);
    if (preg_match("/[1-9A-X]/",$temp,$match)){
      $runningSum[$i] += $vmMass[$temp];
      $neutralLossList[$i] = $neutralLossList[$i-1]+ $neutralLoss[$temp][0];
    } else if (isset($neutralLoss[$peptide[$i]])){
      $neutralLossList[$i] = $neutralLossList[$i-1] + $neutralLoss[$peptide[$i]][0];
    }else{    
      $neutralLossList[$i] = $neutralLossList[$i-1];
    }  
  }
  
  // If there is a variable mod at either terminus, add it to masses{}
  $temp = substr($varModsString, 0, 1);
  if (preg_match("/[1-9A-X]/",$temp,$match)){
    $masses['n_term'] += $vmMass[$temp];
    $neutralLoss_N_Term = $neutralLoss[$temp][0];
  } else if (isset($neutralLoss['n_term'])) {
      $neutralLoss_N_Term = $neutralLoss['n_term'][0];
  } else {
    $neutralLoss_N_Term = 0;
  }
  $temp = substr($varModsString, -1, 1);
  if (preg_match("/[1-9A-X]/",$temp,$match)){
    $masses['c_term'] += $vmMass[$temp];
    $neutralLoss_C_Term = $neutralLoss[$temp][0];
  } else if(isset($neutralLoss['c_term'])){
    $neutralLoss_C_Term = $neutralLoss['c_term'][0];
  } else {
    $neutralLoss_C_Term = 0;
  }
   
  // quiet sanity check
  $calcMr = $runningSum[strlen($peptide) - 1] + $masses['n_term'] + $masses['c_term'];
  // calculate fragment ion masses for each series
  $CO = $masses['carbon'] + $masses['oxygen'];
  $NH3 = $masses['nitrogen'] + 3*$masses['hydrogen'];
  $H2O = 2*$masses['hydrogen'] + $masses['oxygen'];
  if ($polarity == "+") {
    $masses['charge'] = $masses['hydrogen'] - $masses['electron'];
  } else {
    $masses['charge'] = - $masses['hydrogen'] + $masses['electron'];
  }
  // variables to handle multiple neutral losses
  // $nl_label is the net neutral as a text string for a peak label
  $nl_label = "";
  // $frag_seq is the sequence of a fragment
  $frag_seq = "";
  // $var_mod_string is the substring of $varModsString corresponding to the fragment
  // Used for figuring out where neutral losses need to be permuted
  // For a high energy fragment, string excludes new terminal residue
  $var_mod_string = "";
  // $net_nl is the total neutral loss for a fragment
  $net_nl = 0;
  $calc_masses[0] = 0;
  $net_nl_list[0] = 0;
  $left_label_list[0] = "";
  $right_label_list[0] = "";
  $var_mod_string_list[0] = "";
  // first n-term
  for ($i = 0; $i < (strlen($peptide) - 1); $i++){
    $j = $i + 1;
    $frag_seq = substr($peptide, 0, $i+1);
    $var_mod_string = substr($varModsString, 0, $i+2);
    $net_nl = $neutralLossList[$i] + $neutralLoss_N_Term;   
     if ($nl_label != sprintf("%.0f", - $net_nl) + 0)$nl_label = "";
    $aIons[$i] = $runningSum[$i] + $masses['n_term'] - $CO - $net_nl - $masses['hydrogen'] + $masses['charge'];
    if ($include['aIons'] > 0){
      array_push($calc_masses, $aIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "a($j)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$aIons[$i])] = "a($j)$nl_label";
    }
    if ($include['a2Ions'] > 0){
      $a2Ions[$i] = ($aIons[$i] + $masses['charge']) / 2;
      array_push($calc_masses, $a2Ions[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "a($j)");
      array_push($right_label_list, $double);
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$a2Ions[$i])] = "a($j)$nl_label$double";
    }
    if (preg_match("/[rknq]/",$frag_seq,$match)) {
      $asIons[$i] = $aIons[$i] - $NH3;
      if ($include['asIons'] > 0){
        array_push($calc_masses, $asIons[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "a*($j)");
        array_push($right_label_list, "");
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$asIons[$i])] = "a*($j)$nl_label";
      }
      if ($include['as2Ions'] > 0){
        $as2Ions[$i] = ($asIons[$i] + $masses['charge']) / 2;
        array_push($calc_masses, $as2Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "a*($j)");
        array_push($right_label_list, $double);
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$as2Ions[$i])] = "a*($j)$nl_label$double";
      }
    }
    if (preg_match("/[sted]/",$frag_seq,$match )) {
      $a0Ions[$i] = $aIons[$i] - $H2O;
      if ($include['a0Ions'] > 0){
        array_push($calc_masses, $a0Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "a0($j)");
        array_push($right_label_list, "");
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$a0Ions[$i])] = "a0($j)$nl_label";
      }
      if ($include['a02Ions'] > 0){
        $a02Ions[$i]=($a0Ions[$i] + $masses['charge']) / 2;
        array_push($calc_masses, $a02Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "a0($j)");
        array_push($right_label_list, $double);
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$a02Ions[$i])] = "a0($j)$nl_label$double";
      }
    }
    $bIons[$i]=$runningSum[$i] + $masses['n_term'] - $net_nl - $masses['hydrogen'] + $masses['charge'];
    if ($include['bIons'] > 0){
      array_push($calc_masses, $bIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "b($j)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$bIons[$i])] = "b($j)$nl_label";
    }
    if ($include['b2Ions'] > 0){
      $b2Ions[$i] = ($bIons[$i] + $masses['charge']) / 2;
      array_push($calc_masses, $b2Ions[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "b($j)");
      array_push($right_label_list, $double);
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$b2Ions[$i])] = "b($j)$nl_label$double";
    }
    if (preg_match("/[rknq]/",$frag_seq,$match)) {
      $bsIons[$i] = $bIons[$i] - $NH3;
      if ($include['bsIons'] > 0){
        array_push($calc_masses, $bsIons[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "b*($j)");
        array_push($right_label_list, "");
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$bsIons[$i])] = "b*($j)$nl_label";
      }
      if ($include['bs2Ions'] > 0){
        $bs2Ions[$i] = ($bsIons[$i] + $masses['charge']) / 2;
        array_push($calc_masses, $bs2Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "b*($j)");
        array_push($right_label_list, $double);
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$bs2Ions[$i])] = "b*($j)$nl_label$double";
      }
    }
    if (preg_match("/[sted]/",$frag_seq,$match)) {
      $b0Ions[$i] = $bIons[$i] - $H2O;
      if ($include['b0Ions'] > 0){
        array_push($calc_masses, $b0Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "b0($j)");
        array_push($right_label_list, "");
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$b0Ions[$i])] = "b0($j)$nl_label";
      }
      if ($include['b02Ions'] > 0){
        $b02Ions[$i] = ($b0Ions[$i] + $masses['charge']) / 2;
        array_push($calc_masses, $b02Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "b0($j)");
        array_push($right_label_list, $double);
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$b02Ions[$i])] = "b0($j)$nl_label$double";
      }
    }
    $cIons[$i]=$bIons[$i] + $NH3;
    if ($include['cIons'] > 0){
      array_push($calc_masses, $cIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "c($j)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$cIons[$i])] = "c($j)$nl_label";
    }
    if ($include['c2Ions'] > 0){
      $c2Ions[$i]=($cIons[$i] + $masses['charge'])/2;
      array_push($calc_masses, $c2Ions[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "c($j)");
      array_push($right_label_list, $double);
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$c2Ions[$i])] = "c($j)$nl_label$double";
    }
  }
  // then c-term
  for ($i = 0; $i < strlen($peptide) - 1; $i++){
    $j = $i + 1;
    $frag_seq = substr(($peptide), -$i-1);
    $var_mod_string = substr($varModsString, -$i-2);
    $net_nl = $neutralLossList[strlen($peptide)-1] - $neutralLossList[strlen($peptide)-2-$i] + $neutralLoss_C_Term;
    if ($nl_label != sprintf("%.0f", - $net_nl) + 0)$nl_label = "";
    $yIons[$i] = $runningSum[strlen($peptide)-1] - $runningSum[strlen($peptide)-2-$i] + $masses['c_term'] + 2 * $masses['hydrogen'] - $net_nl - $masses['hydrogen'] + $masses['charge'];
    if ($include['yIons'] > 0){
      array_push($calc_masses, $yIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "y($j)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$yIons[$i])] = "y($j)$nl_label";
    }
    if ($include['y2Ions'] > 0){
      $y2Ions[$i] = ($yIons[$i] + $masses['charge']) / 2;
      array_push($calc_masses, $y2Ions[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "y($j)");
      array_push($right_label_list, $double);
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$y2Ions[$i])] = "y($j)$nl_label$double";
    }
    if (preg_match("/[rknq]/",$frag_seq,$match)) {
      $ysIons[$i] = $yIons[$i] - $NH3;
      if ($include['ysIons'] > 0){
        array_push($calc_masses, $ysIons[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "y*($j)");
        array_push($right_label_list, "");
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$ysIons[$i])] = "y*($j)$nl_label";
      }
      if ($include['ys2Ions'] > 0){
        $ys2Ions[$i] = ($ysIons[$i] + $masses['charge'])/2;
        array_push($calc_masses, $ys2Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "y*($j)");
        array_push($right_label_list, $double);
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$ys2Ions[$i])] = "y*($j)$nl_label$double";
      }
    }
    if (preg_match("/[sted]/",$frag_seq,$match)) {
      $y0Ions[$i] = $yIons[$i] - $H2O;
      if ($include['y0Ions'] > 0){
        array_push($calc_masses, $y0Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "y0($j)");
        array_push($right_label_list, "");
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$y0Ions[$i])] = "y0($j)$nl_label";
      }
      if ($include['y02Ions'] > 0){
        $y02Ions[$i] = ($y0Ions[$i] + $masses['charge']) / 2;
        array_push($calc_masses, $y02Ions[$i]);
        array_push($net_nl_list, $net_nl);
        array_push($left_label_list, "y0($j)");
        array_push($right_label_list, $double);
        array_push($var_mod_string_list, $var_mod_string);
        $labelList[sprintf("%.4f",$y02Ions[$i])] = "y0($j)$nl_label$double";
      }
    }
    $xIons[$i] = $yIons[$i] - 2 * $masses['hydrogen'] + $CO;
    if ($include['xIons'] > 0){
      array_push($calc_masses, $xIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "x($j)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$xIons[$i])] = "x($j)$nl_label";
    }
    if ($include['x2Ions'] > 0){
      $x2Ions[$i]=($xIons[$i] + $masses['charge'])/2;
      array_push($calc_masses, $x2Ions[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "x($j)");
      array_push($right_label_list, $double);
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$x2Ions[$i])] = "x($j)$nl_label$double";
    }
    $zIons[$i] = $yIons[$i] - $NH3;
    if ($include['zIons'] > 0){
      array_push($calc_masses, $zIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "z($j)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$zIons[$i])] = "z($j)$nl_label";
    }
    if ($include['z2Ions'] > 0){
      $z2Ions[$i]=($zIons[$i] + $masses['charge']) / 2;
      array_push($calc_masses, $z2Ions[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "z($j)");
      array_push($right_label_list, $double);
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$z2Ions[$i])] = "z($j)$nl_label$double";
    }
    $zhIons[$i] = $zIons[$i] + $masses['hydrogen'];
    if ($include['zhIons'] > 0){
      array_push($calc_masses, $zhIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "z+1($j)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$zhIons[$i])] = "z+1($j)$nl_label";
    }
    if ($include['zh2Ions'] > 0){
      $zh2Ions[$i]=($zhIons[$i] + $masses['charge'])/2;
      array_push($calc_masses, $zh2Ions[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "z+1($j)");
      array_push($right_label_list, $double);
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$zh2Ions[$i])] = "z+1($j)$nl_label$double";
    }
    $zhhIons[$i] = $zhIons[$i] + $masses['hydrogen'];
    if ($include['zhhIons'] > 0){
      array_push($calc_masses, $zhhIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "z+2($j)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$zhhIons[$i])] = "z+2($j)$nl_label";
    }
    if ($include['zhh2Ions'] > 0){
      $zhh2Ions[$i]=($zhhIons[$i] + $masses['charge'])/2;
      array_push($calc_masses, $zhh2Ions[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, "z+2($j)");
      array_push($right_label_list, $double);
      array_push($var_mod_string_list, $var_mod_string);
      $labelList[sprintf("%.4f",$zhh2Ions[$i])] = "z+2($j)$nl_label$double";
    }
  }
  // immonium ions
  if ($include['immIons'] > 0){
    for ($i = 0; $i < count($peptide); $i++) {
      $immIons[$i] = $masses[$peptide[$i]] - $CO + $masses['charge'];
      $temp = substr($varModsString, $i+1, 1);
      $net_nl = 0;
      if (preg_match("/[1-9A-X]/",$temp,$match)){
        $immIons[$i] += $vmMass[$temp];
        $net_nl =$neutralLoss[$temp][0];
      } elseif (defined($neutralLoss[$peptide[$i]][0])) {
        $net_nl =$neutralLoss[$peptide[$i]][0];
      }
      $immIons[$i] -= $net_nl;
      if($nl_label != sprintf("%.0f", - $net_nl) + 0)$nl_label = "";
      array_push($calc_masses, $immIons[$i]);
      array_push($net_nl_list, $net_nl);
      array_push($left_label_list, strtoupper($peptide[$i]));
      array_push($right_label_list, "");
      array_push($var_mod_string_list, substr($varModsString, $i+1, 1));
      $labelList[sprintf("%.4f",$immIons[$i])] = strtoupper($peptide[$i]) . $nl_label;
    }
  }
  // internals(unlike other series, we may encounter duplicate mass values)
  if ($include['intyaIons'] > 0 || $include['intybIons'] > 0) {
    for ($i = 0; $i < count($peptide) - 3; $i++) {
      for ($j = $i + 2; $j < count($peptide) - 1; $j++) {
        $frag_seq = substr($peptide, $i+1, $j-$i);
        $var_mod_string = substr($varModsString, $i+2, $j-$i);
        $net_nl = $neutralLossList[$j] - $neutralLossList[$i];
        if ($nl_label != sprintf("%.0f", - $net_nl) + 0)$nl_label = "";
        array_push($intybIons, $runningSum[$j] - $runningSum[$i]  + $masses['hydrogen'] - $net_nl - $masses['hydrogen'] + $masses['charge']);
        array_push($intLabels, $frag_seq);
        if ($include['intybIons'] > 0) {
          if ($intybIons[-1] < $MinInternalMass || $intybIons[-1] > $MaxInternalMass) {
            array_push($calc_masses, $intybIons[-1]);
            array_push($net_nl_list, $net_nl);
            array_push($left_label_list, $frag_seq);
            array_push($right_label_list, "");
            array_push($var_mod_string_list, $var_mod_string);
            $labelList[sprintf("%.4f",$intybIons[-1])] = $frag_seq . $nl_label;
          }
        }
        array_push($intyaIons, $intybIons[-1] - $CO);
        if ($include['intyaIons'] > 0) {
          if ($intyaIons[-1] < $MinInternalMass || $intyaIons[-1] > $MaxInternalMass) {
            array_push($calc_masses, $intyaIons[-1]);
            array_push($net_nl_list, $net_nl);
            array_push($left_label_list, $frag_seq . "-CO");
            array_push($right_label_list, "");
            array_push($var_mod_string_list, $var_mod_string);
            $labelList[sprintf("%.4f",$intyaIons[-1])] = $frag_seq . "-CO" . $nl_label;
          }
        }
      }
    }
  }
  // for high energy side-chain cleavage fragments, (d, d', v, w, w'),
  if ($include['dIons'] > 0){
    if (substr($peptide, 0, 1) == "R") {
      $dIons[0] = $masses['n_term'] - $CO
        - $neutralLoss_N_Term - $masses['hydrogen'] + $masses['charge']
        + $masses['carbon'] * 3 + $masses['hydrogen'] * 5
        + $masses['nitrogen'] + $masses['oxygen'];
      array_push($calc_masses, $dIons[0]);
      array_push($net_nl_list, $neutralLoss_N_Term);
      array_push($left_label_list, "d(1)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, substr($varModsString, 0, 1));
       if ($nl_label != sprintf("%.0f", - $neutralLoss_N_Term) + 0)$nl_label = "";
      $labelList[sprintf("%.4f",$dIons[0])] = "d(1)$nl_label";
    }
    for ($i = 1; $i < (strlen($peptide) - 1); $i++){
      $j = $i + 1;
      if (preg_match("/R/",substr($peptide, 0, $i+1),$match)) {
        $var_mod_string = substr($varModsString, 0, $i+1);
        $net_nl = $neutralLossList[$i - 1] + $neutralLoss_N_Term;
        if ($nl_label != sprintf("%.0f", - $net_nl) + 0)$nl_label = "";
        if (substr($peptide, $i, 1) == "I") {
          $dIons[$i] = $aIons[$i - 1]
            + $masses['carbon'] * 4 + $masses['hydrogen'] * 7
            + $masses['nitrogen'] + $masses['oxygen'];
          array_push($calc_masses, $dIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "d($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$dIons[$i])] = "d($j)$nl_label";
          $dpIons[$i] = $aIons[$i - 1]
            + $masses['carbon'] * 5 + $masses['hydrogen'] * 9
            + $masses['nitrogen'] + $masses['oxygen'];
          array_push($calc_masses, $dpIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "d'($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$dpIons[$i])] = "d'($j)$nl_label";
        } elseif (substr($peptide, $i, 1) == "T") {
          $dIons[$i] = $aIons[$i - 1]
            + $masses['carbon'] * 4 + $masses['hydrogen'] * 7
            + $masses['nitrogen'] + $masses['oxygen'];
          array_push($calc_masses, $dIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "d($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$dIons[$i])] = "d($j)$nl_label";
          $dpIons[$i] = $aIons[$i - 1]
            + $masses['carbon'] * 3 + $masses['hydrogen'] * 5
            + $masses['nitrogen'] + $masses['oxygen'] * 2;
          array_push($calc_masses, $dpIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "d'($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$dpIons[$i])] = "d'($j)$nl_label";
        } elseif (substr($peptide, $i, 1) == "V") {
          $dIons[$i] = $aIons[$i - 1]
            + $masses['carbon'] * 4 + $masses['hydrogen'] * 7
            + $masses['nitrogen'] + $masses['oxygen'];
          array_push($calc_masses, $dIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "d($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$dIons[$i])] = "d($j)$nl_label";
        } elseif (preg_match("/[RNDCEQLKMPS]/",substr($peptide, $i, 1),$match)) {
          $dIons[$i] = $aIons[$i - 1]
            + $masses['carbon'] * 3 + $masses['hydrogen'] * 5
            + $masses['nitrogen'] + $masses['oxygen'];
          array_push($calc_masses, $dIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "d($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$dIons[$i])] = "d($j)$nl_label";
        }
      }
    }
  }
  if ($include['vIons'] > 0){
    if (substr($peptide, -1, 1) == "R") {
      $vIons[0] = $masses['c_term'] + $masses['hydrogen'] * 3
        - $neutralLoss_C_Term - $masses['hydrogen'] + $masses['charge']
        + $masses['carbon'] * 2
        + $masses['nitrogen'] + $masses['oxygen'];
      array_push($calc_masses, $vIons[0]);
      array_push($net_nl_list, $neutralLoss_C_Term);
      array_push($left_label_list, "v(1)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, substr($varModsString, -1));
       if ($nl_label != sprintf("%.0f", - $neutralLoss_C_Term) + 0)$nl_label = "";
      $labelList[sprintf("%.4f",$vIons[0])] = "v(1)$nl_label";
    }
    for ($i = 1; $i < (strlen($peptide) - 1); $i++){
      $j = $i + 1;
      $var_mod_string = substr($varModsString, -$i-1);
      if (preg_match("/R/",substr($peptide, -$i-1),$match)) {
        if (substr($peptide, -$i-1, 1) != "G") {
          $net_nl = $neutralLossList[length($peptide)-1] - $neutralLossList[strlen($peptide)-1-$i] + $neutralLoss_C_Term;
          $vIons[$i] = $yIons[$i - 1]
            + $masses['carbon'] * 2 + $masses['hydrogen']
            + $masses['nitrogen'] + $masses['oxygen'];
          array_push($calc_masses, $vIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "v($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
           if ($nl_label != sprintf("%.0f", - $net_nl) + 0)$nl_label = "";
          $labelList[sprintf("%.4f",$vIons[$i])] = "v($j)$nl_label";
        }
      }
    }
  }
  if ($include['wIons'] > 0){
    if (substr($peptide, -1, 1) == "R") {
      $wIons[0] = $masses['c_term'] + $masses['hydrogen'] * 4
        - $neutralLoss_C_Term - $masses['hydrogen'] + $masses['charge']
        + $masses['carbon'] * 3 + $masses['oxygen'];
      array_push($calc_masses, $wIons[0]);
      array_push($net_nl_list, $neutralLoss_C_Term);
      array_push($left_label_list, "w(1)");
      array_push($right_label_list, "");
      array_push($var_mod_string_list, substr($varModsString, -1));
       if ($nl_label != sprintf("%.0f", - $neutralLoss_C_Term) + 0)$nl_label = "";
      $labelList[sprintf("%.4f",$wIons[0])] = "w(1)";
    }
    for ($i = 1; $i < (strlen($peptide) - 1); $i++){
      $j = $i + 1;
      if (preg_match("/R/",substr($peptide, -$i-1),$match)) {
        $var_mod_string = substr($varModsString, -$i-1);
        $net_nl = $neutralLossList[length($peptide)-1]
          - $neutralLossList[length($peptide)-1-$i] + $neutralLoss_C_Term;
        if ($nl_label = sprintf("%.0f", - $net_nl) + 0)$nl_label = "" ;
        if (substr($peptide, -$i-1, 1) == "I") {
          $wIons[$i] = $yIons[$i - 1]
            + $masses['carbon'] * 4 + $masses['hydrogen'] * 4
            + $masses['oxygen'];
          array_push($calc_masses, $wIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "w($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$wIons[$i])] = "w($j)$nl_label";
          $wpIons[$i] = $yIons[$i - 1]
            + $masses['carbon'] * 5 + $masses['hydrogen'] * 6
            + $masses['oxygen'];
          array_push($calc_masses, $wpIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "w'($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$wpIons[$i])] = "w'($j)$nl_label";
        } elseif (substr($peptide, -$i-1, 1) == "T") {
          $wIons[$i] = $yIons[$i - 1]
            + $masses['carbon'] * 4 + $masses['hydrogen'] * 4
            + $masses['oxygen'];
          array_push($calc_masses, $wIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "w($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$wIons[$i])] = "w($j)$nl_label";
          $wpIons[$i] = $yIons[$i - 1]
            + $masses['carbon'] * 3 + $masses['hydrogen'] * 2
            + $masses['oxygen'] * 2;
          array_push($calc_masses, $wpIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "w'($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$wpIons[$i])] = "w'($j)$nl_label";
        } elseif (substr($peptide, -$i-1, 1) == "V") {
          $wIons[$i] = $yIons[$i - 1]
            + $masses['carbon'] * 4 + $masses['hydrogen'] * 4
            + $masses['oxygen'];
          array_push($calc_masses, $wIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "w($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$wIons[$i])] = "w($j)$nl_label";
        } elseif (preg_match("/[RNDCEQLKMPS]/",substr($peptide, $i, 1),$match)) {
          $wIons[$i] = $yIons[$i - 1]
            + $masses['carbon'] * 3 + $masses['hydrogen'] * 2
            + $masses['oxygen'];
          array_push($calc_masses, $wIons[$i]);
          array_push($net_nl_list, $net_nl);
          array_push($left_label_list, "w($j)");
          array_push($right_label_list, "");
          array_push($var_mod_string_list, $var_mod_string);
          $labelList[sprintf("%.4f",$wIons[$i])] = "w($j)$nl_label";
        }
      }
    }
  }
  // If any variable mods have multiple neutral losses, we now need to
  // permute out additional calculated values
  $local_mod = array(1,2,3,4,5,6,7,8,9,"X");
  foreach ($local_mod as $mod) {
    if (isset($neutralLoss[$mod]) and count($neutralLoss[$mod])> 0) {
      $last_index = count($calc_masses);
      for ( $i = 1; $i < $last_index; $i++) {
        if (preg_match("/$mod/",$var_mod_string_list[$i],$match)) {
           preg_match_all("/$mod/",$var_mod_string_list[$i],$match);
           $counts = $match[0];
           $count = count($counts);
          for ($j = 0; $j < count($neutralLoss[$mod]); $j++) {
            $delta = $count * ($neutralLoss[$mod][$j] - $neutralLoss[$mod][0]);
            
            if($delta){
              array_push($net_nl_list, $net_nl_list[$i] + $delta);
              array_push($left_label_list, $left_label_list[$i]);
              array_push($right_label_list, $right_label_list[$i]);
              array_push($var_mod_string_list, $var_mod_string_list[$i]);
              if($nl_label == ""){
                $nl_label = sprintf("%.0f", - $net_nl_list[count($net_nl_list)-1]) + 0;
              }
              if ($right_label_list[$i]) {
                # allow for charge
                array_push($calc_masses, $calc_masses[$i] - $delta / 2);
              } else {
                array_push($calc_masses, $calc_masses[$i] - $delta);
              }
              $labelList[sprintf("%.4f",$calc_masses[count($calc_masses)-1])] = $left_label_list[$i]. $nl_label . $right_label_list[$i];
            }
          }
        }
      }
    }
  }
  
  // precursor neutral loss(es)
  if (isset($pepNeutralLoss)){
    foreach ($pepNeutralLoss as $ref){
      foreach($ref as $nl) {
        array_push($calc_masses, ($calcMr - $nl + $charge * $masses['charge']) / $charge);
        $labelList[sprintf("%.4f",$calc_masses[-1])] = "M".sprintf("%.0f", -$nl).$polarity*$charge;
      }
    }
  }
  if(isset($reqPepNeutralLoss)){
    foreach  ($reqPepNeutralLoss as $ref) {
      foreach ($ref as $nl) {
        array_push($calc_masses, ($calcMr - $nl + $charge * $masses['charge']) / $charge);
        $labelList[sprintf("%.4f",$calc_masses[-1])] = "M" . sprintf("%.0f", - $nl) . $polarity * $charge;
      }
    }
  }
  $numCalcVals = count($calc_masses)-1;
//DEBUG
//for ($i=0; $i <= $numCalcVals; $i++) {
//print $calc_masses[$i];
//print "\t";
//print $net_nl_list[$i];
//print "\t";
//print $left_label_list[$i];
//print "\t";
//print $right_label_list[$i];
//print "\t";
//print $var_mod_string_list[$i];
//print "<br>";
//}
//DEBUG
  // if there are no values in a column, drop the column
  if (count($asIons) < 0) {
    $include['asIons'] = 0;
  }
  if (count($as2Ions) < 0) {
    $include['as2Ions'] = 0;
  }
  if (count($a0Ions) < 0) {
    $include['a0Ions'] = 0;
  }
  if (count($a02Ions) < 0) {
    $include['a02Ions'] = 0;
  }
  if (count($bsIons) < 0) {
    $include['bsIons'] = 0;
  }
  if (count($bs2Ions) < 0) {
    $include['bs2Ions'] = 0;
  }
  if (count($b0Ions) < 0) {
    $include['b0Ions'] = 0;
  }
  if (count($b02Ions) < 0) {
    $include['b02Ions'] = 0;
  }
  if (count($ysIons) < 0) {
    $include['ysIons'] = 0;
  }
  if (count($ys2Ions) < 0) {
    $include['ys2Ions'] = 0;
  }
  if (count($y0Ions) < 0) {
    $include['y0Ions'] = 0;
  }
  if (count($y02Ions) < 0) {
    $include['y02Ions'] = 0;
  }
  if (count($dIons) < 0) {
    $include['dIons'] = 0;
  }
  if (count($dpIons) < 0) {
    $include['dpIons'] = 0;
  }
  if (count($vIons) < 0) {
    $include['vIons'] = 0;
  }
  if (count($wIons) < 0) {
    $include['wIons'] = 0;
  }
  if (count($wpIons) < 0) {
    $include['wpIons'] = 0;
  }
  return 1;
}
function printMasses($index, $ionMassesRef, $seriesStr){
  global $debug;
  global $labels;
  if (isset($ionMassesRef[$index])and isset($labels[sprintf("%.4f", $ionMassesRef[$index])])){
    if ($debug){
      if ($seriesStr == 2) {
        $printMass = sprintf("<B><I><FONT COLOR=#FF0000>%7.4f</FONT></I></B>", $ionMassesRef[$index]);
      } else {
        if ($seriesStr == 1) {
          $printMass = sprintf("<B><FONT COLOR=#FF0000>%7.4f</FONT></B>", $ionMassesRef[$index]);
        } else {
        $printMass = sprintf("<FONT COLOR=#FF0000>%7.4f</FONT>", $ionMassesRef[$index]);
        }
      }
    } else {
      $printMass = sprintf("<B><FONT COLOR=#FF0000>%7.4f</FONT></B>", $ionMassesRef[$index]);
    }
  } else {
    if (isset($ionMassesRef[$index]) and $ionMassesRef[$index] > 0) {
      $printMass = sprintf("%7.4f", $ionMassesRef[$index]);
    }else{
      $printMass ="&nbsp";
    }
  }
  echo "<TD>$printMass</TD>\n";
}
function findMatches(){
  global $massList;
  global $massList_gif;
  global $matchList;
  global $intensityList;
  global $typeList;
  global $type;
  global $errorList;
  global $num_used1;
  global $num_used2;
  global $num_used3;
  global $Ions;
  global $calc_masses;
  global $debug;
  global $peaks;
  global $exp_masses;
  global $labels;
  global $labelList;
  global $tol;
  global $tolU;
  global $iTol;
  global $iTolU;
  global $ignoreMass;
  global $peaksIons1;
  global $peaksIons2;
  global $peaksIons3;
  $int_mass = array();
  $fullMassList = array();
  $matched_exp = array();
  $matched_int = array();
  $matched_calc = array();
  
  if (!$debug) {
  // make a copy of the full experimental mass list, sorted by mass
  // and put the corresponding intensities in a hash
    for ($i = 0; $i < count($massList); $i++){
      $int_mass[$massList[$i]] = $intensityList[$i];
    }
  }
  sort($massList);
  $fullMassList = $massList;
  if ($num_used1 == -1) {
    $num_used[1] = $peaksIons1;
    $num_used[2] = $peaksIons2;
    $num_used[3] = $peaksIons3;
  } else {
    $num_used[1] = $num_used1;
    $num_used[2] = $num_used2;
    $num_used[3] = $num_used3;
  }
  $massList = array();
  $intensityList = array();
  $typeList = array();
  for ($j = 1; $j <= 3; $j++) {
    if (isset($Ions[$j])) {
      $tempString = $Ions[$j];
      $tempString = preg_replace("/^([by])-/","",$tempString);
      if($type == ""){
        $tempArray = preg_split("/\,/", $tempString);
        for ($i = 0; $i < $num_used[$j]; $i++) {
          $tmp = preg_split("/\:/", $tempArray[$i]);
          array_push($massList,$tmp[0]) ;
          if ($tmp[1]) {
            array_push($intensityList, $tmp[1]);
          } else {
            array_push($intensityList, 0);
          }
          array_push($typeList, $type);
        }
      }
    }
  }
  if (!$debug and $massList) {
  // add experimental peaks to the list of potential matches if they are of greater
  // intensity than the smaller adjacent matched peak and not on the ignore list
    $mass = array();
    sort($massList);
    $mass = $massList;
    array_unshift($mass, 0);
    array_push($mass, 999999);
    $c = count($mass);
    $int_mass[$mass[0]] = $int_mass[$mass[1]];
    $int_mass[$mass[$c-1]] = $int_mass[$mass[$c-2]];
    $j = 0;
    for ($i = 1; $i < count($mass); $i++) {
      $intThresh = $int_mass{$mass[$i]};
      if ($int_mass{$mass[$i-1]} < $int_mass[$mass[$i]]) {
        $intThresh = $int_mass[$mass[$i-1]];
      }
      while (isset($fullMassList[$j]) and $fullMassList[$j] < $mass[$i]) {
        if ($int_mass{$fullMassList[$j]} >= $intThresh) {
          $ignoreMe = 0;
          if ($iTolU == "Da"){
            $this_tol = $iTol;
          } else {
            if ($iTolU == "mmu"){
              $this_tol = $iTol/ 1000;
            }
          }
          for ($k = 0; $k <count($ignoreMass); $k++) {
            if (abs($fullMassList[$j]-$ignoreMass[$k]) <=  $this_tol){
              $ignoreMe = 1;
              break;
            }
          }
          if (!$ignoreMe) {
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
  
  for ($i = 1; $i < count($calc_masses); $i++){
    $matchCount[$i] = -1;
  }
  for ($j = 0; $j < count($massList); $j++){
    if ($iTolU == "Da"){
      $this_tol = $iTol;
    } else {
      if ($iTolU == "mmu"){
        $this_tol = $iTol/ 1000;
      }
    }
    //echo "[$j]=$this_tol<br>";
    for ($i = 1; $i <count($calc_masses); $i++){
      if (abs($massList[$j] - $calc_masses[$i]) <=$this_tol){
        if ($matchCount[$i] > -1){
          if ($matched_int[$matchCount[$i]] < $intensityList[$j]) {
            $matched_exp[$matchCount[$i]] = $massList[$j];
            $matched_int[$matchCount[$i]] = $intensityList[$j];
          }
          continue;
        }
        array_push($matched_calc, $calc_masses[$i]);
        array_push($matched_exp, $massList[$j]);
        array_push($matched_int, $intensityList[$j]);
        $matchCount[$i] = count($matched_int)-1;
      }
    }
  }
  
  sort ($matched_exp);
  $exp_masses = $matched_exp;
  sort ($matched_calc);
  $calc_masses = $matched_calc;
  $calc_masses = array_unique($calc_masses);  
  //print_r($exp_masses);
  //print_r($calc_masses);
  for ($i = 0; $i < count($exp_masses); $i++){
    if(!isset($calc_masses[$i])){
      unset($exp_masses[$i]);
    }
  } 
  
  $calc_masses = array_values($calc_masses);
  $exp_masses = array_values($exp_masses);

  // create error list to be passed to mass_error.pl
  for ( $i = 0; $i < count($calc_masses); $i++) {
    $delta_masses[$i] = $exp_masses[$i] - $calc_masses[$i];
  }
  if (count($exp_masses) > 0) {
    $massList_gif = sprintf("%.2f", $exp_masses[0]);
    for ($i = 1; $i < count($exp_masses); $i++) {
      $massList_gif .= ";;" . sprintf("%.2f", $exp_masses[$i]);
    }
  }
  if(count($delta_masses) > 0) {
    $errorList = sprintf("%.6f", $delta_masses[0]);
    for ($i = 1; $i < count($delta_masses); $i++) {
      $errorList .= ";;" . sprintf("%.6f", $delta_masses[$i]);
    }
  }
  // concatenate string  of "$label, $exp_mass, ..." to be passed to msms_gif.pl
  // set $labels[$calc_mass] for highlighting the printed table
 
  for ($i = 0; $i <count($calc_masses); $i++){
    if(isset($labelList[sprintf("%.4f",$calc_masses[$i])])){  
      $matchList .= $labelList[sprintf("%.4f",$calc_masses[$i])] . "," . sprintf("%.4f", $exp_masses[$i]) . ",";
      $labels{sprintf("%.4f", $calc_masses[$i])} = 1;
    }
  }
  $matchList = substr($matchList, 0, -1);
  $matchList = preg_replace("/\+/",';',$matchList);
}
//echo $matchList."</br>";
//echo $errorList;
//echo "\n<pre>";
//print_r($neutralLoss);
//echo "</pre>\n";
//echo "/msms_gif.php?tick1=".$gifParams['tick1']."&tick_int=".$gifParams['tick_int']."&range=".$gifParams['range']."&tmpFile=".$tmp_parsed_file."&matchList=".$matchList;
function printNLInfo($nl_idx) {
  global $neutralLoss;
  if ($neutralLoss[$nl_idx] 
    and count($neutralLoss[$nl_idx]) == 1
    and $neutralLoss[$nl_idx][0] != 0.0) {
    print ", with neutral loss "
      . sprintf("%.4f", $neutralLoss[$nl_idx][0]);
  } elseif ($neutralLoss[$nl_idx] and count($neutralLoss[$nl_idx]) > 1) {
    print ", with neutral losses "
      . sprintf("%.4f", $neutralLoss[$nl_idx][0])
      . "(shown in table)";
    for ($i = 1; $i <count($neutralLoss[$nl_idx]); $i++){
      print ", " . sprintf("%.4f", $neutralLoss[$nl_idx][$i]);
      
    }
  }
}
?>

<HTML>
<HEAD>
<TITLE>Mascot Search Results: Peptide View</TITLE>
</HEAD>
<script language='javascript'>
function popTest(){
  file = "./msms_gif.php?tick1=<?php echo $gifParams['tick1'];?>&tick_int=<?php echo $gifParams['tick_int'];?>&range=<?php echo $gifParams['range'];?>&tmpFile=<?php echo $tmp_parsed_file;?>&matchList=<?php echo $matchList;?>";
  nWin = window.open(file,"image",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=800');
  nWin.moveTo(4,0);
}
function popTest_1(){
  file = "./mass_error.php?units=<?php echo $iTolU;?>&massList=<?php echo $massList_gif;?>&errorList=<?php echo $errorList;?>";
  nWin = window.open(file,"image",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=800');
  nWin.moveTo(4,0);
}
function popTest_2(){
  file = "./mass_error.php?units=ppm&massList=<?php echo $massList_gif;?>&errorList=<?php echo $errorList;?>"
  nWin = window.open(file,"image",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=800,height=800');
  nWin.moveTo(4,0);
}


</script>


<BODY BGCOLOR="#ffffff" ALINK="#0000ff" VLINK="#0000ff">
<H1><IMG SRC="./images/88x31_logo_white_Mascot.gif" WIDTH=88 HEIGHT=31
ALIGN="TOP" BORDER=0 NATURALSIZEFLAG=3> Mascot Search Results</H1>
<H3>Peptide View</H3>
MS/MS Fragmentation of <B><FONT COLOR=#FF0000><?php echo $pepSequences;?></FONT></B><BR>
Found in <B><FONT COLOR=#FF0000><?php echo $index;?></FONT></B>, <?php echo $proDesc;?><BR>
<P>Match to Query <?php echo $query;?>: <?php echo $pepExpects;?> from(<?php echo $pepMZ;?>,<?php echo $charge.$polarity;?>)<BR>
Title: <?php echo $title;?><BR>
Data file <?php echo $msDataFile;?><BR>
<FORM METHOD="GET" ENCTYPE="application/x-www-form-urlencoded" ACTION="<?php echo $_SERVER['PHP_SELF'];?>">
Click mouse within plot area to zoom in by factor of two about that point<BR>
Or,&nbsp;<INPUT TYPE="submit" NAME="zoomOut" VALUE="Plot from" >&nbsp;
<INPUT TYPE="text" SIZE=8 NAME="from" VALUE="<?php echo $gifParams['tick1'];?>">&nbsp;to&nbsp;
<INPUT TYPE="text" SIZE=8 NAME="to" VALUE="<?php echo $gifParams['lasttick'];?>">&nbsp;Da
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<INPUT TYPE="submit" NAME="Reset" VALUE="Full range" onClick="this.form.from.value=0;
this.form.to.value=0; return 0">
<P><INPUT TYPE="image" NAME="gif" HEIGHT="<?php echo $gifParams['height'];?>" WIDTH="<?php echo $gifParams['width'];?>"
 ALT="MS/MS spectrum of <?php echo $pepSequences;?>" BORDER="1"
SRC="./msms_gif.php?tick1=<?php echo $gifParams['tick1'];?>&tick_int=<?php echo $gifParams['tick_int'];?>&range=<?php echo $gifParams['range'];?>&tmpFile=<?php echo $tmp_parsed_file;?>&matchList=<?php echo $matchList;?>">
<INPUT TYPE="hidden" NAME="file" VALUE="<?php echo $file;?>">
<INPUT TYPE="hidden" NAME="query" VALUE="<?php echo $query;?>">
<INPUT TYPE="hidden" NAME="hit" VALUE="<?php echo $hit;?>">
<INPUT TYPE="hidden" NAME="expPara" VALUE="<?php echo $exportingParameterStr;?>">
<INPUT TYPE="hidden" NAME="tick1" VALUE="<?php echo $gifParams['tick1'];?>">
<INPUT TYPE="hidden" NAME="tick_int" VALUE="<?php echo $gifParams['tick_int'];?>">
<INPUT TYPE="hidden" NAME="range" VALUE="<?php echo $gifParams['range'];?>">
<INPUT TYPE="hidden" NAME="expPara" VALUE="<?php echo $exportingParameterStr;?>">
<INPUT TYPE="hidden" NAME="index" VALUE="<?php echo $index;?>">
<INPUT TYPE="hidden" NAME="bottom" VALUE="<?php echo $gifParams['bottom'];?>">
<INPUT TYPE="hidden" NAME="left" VALUE="<?php echo $gifParams['left'];?>">
<INPUT TYPE="hidden" NAME="height" VALUE="<?php echo $gifParams['height'];?>">
<INPUT TYPE="hidden" NAME="width" VALUE="<?php echo $gifParams['width'];?>">
<INPUT TYPE="hidden" NAME="right" VALUE="<?php echo $gifParams['right'];?>">
<INPUT TYPE="hidden" NAME="scoop" VALUE="<?php echo $gifParams['scoop'];?>">
<INPUT TYPE="hidden" NAME="top" VALUE="<?php echo $gifParams['top'];?>">
</FORM>
<!--table><tr><td>
  <a href='javascript: popTest();'>[Test Image]</a>
  </td></tr>
</table-->


<FONT FACE='Courier New,Courier,monospace'>
<PRE>
<B><?php echo $typeMass;?> mass of neutral peptide Mr(calc):</B> <?php echo $pepMass;?>

<?php 
if($fixedModifications) echo "<B>Fixed modifications:</B> $fixedModifications\n";
if (preg_match("/[1-9A-X]/",$varModsString,$match)) {
  print "<B>Variable modifications: </B>\n";
  $tempString = substr($varModsString, 0, 1);
  if (preg_match("/[1-9A-X]/",$tempString,$match)){
    print "<B>N-term : </B>" . $vmString{$tempString};
    printNLInfo($tempString);
    print "\n";
  }
  for ($i = 1; $i < strlen($varModsString)-1; $i++){
    $tempString = substr($varModsString, $i, 1);
    if (preg_match("/[1-9A-X]/",$tempString,$match)){
      //if ($pepMatch{'frame'} 
      //  and $objResFile->getSectionValueStr($msparser::ms_mascotresfile::SEC_PARAMETERS, 'ERRORTOLERANT') 
      //  and preg_match("/^NA_/i",$vmString[$tempString],$match)) {
      //  print "<B>       : </B>" . $vmString{$tempString} . "\n";
      //} else {
        print "<B>" . strtoupper($peptide[$i-1]) . sprintf('%-2d', $i) . "    : </B>" . $vmString[0];
        printNLInfo($tempString);
        print "\n";
      //}
    }
  }
  $tempString = substr($varModsString, -1, 1);
  if (preg_match("/[1-9A-X]/",$tempString,$match)){
    print "<B>C-term : </B>" . $vmString{$tempString};
    printNLInfo($tempString);
    print "\n";
  }
}
?>
<B>Ions Score:</B> <?php echo $pepScores;?>  <B>Expect:</B> <?php echo $pepExpect;?>

<B>Matches (<FONT COLOR=#FF0000>Bold Red</FONT>):</B> <?php echo count($exp_masses);?>/<?php echo $numCalcVals;?> fragment ions using <?php echo $peaks;?> most intense peaks
</PRE>
</FONT>
<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>
  <TR BGCOLOR=#cccccc>
    <TH>#</TH>
<?php 
if ($include['immIons']>0){echo "    <TH>Immon.</TH>\n";}
if ($include['aIons']>0)  {echo "    <TH>a</TH>\n";}
if ($include['a2Ions']>0) {echo "    <TH>a<SUP>$double</SUP></TH>\n";}
if ($include['asIons']>0) {echo "    <TH>a*</TH>\n";}
if ($include['as2Ions']>0){echo "    <TH>a*<SUP>$double</SUP></TH>\n";}
if ($include['a0Ions']>0) {echo "    <TH>a<SUP>0</SUP></TH>\n";}
if ($include['a02Ions']>0){echo "    <TH>a<SUP>0$double</SUP></TH>\n";}
if ($include['bIons']>0)  {echo "    <TH>b</TH>\n";}
if ($include['b2Ions']>0) {echo "    <TH>b<SUP>$double</SUP></TH>\n";}
if ($include['bsIons']>0) {echo "    <TH>b*</TH>\n";}
if ($include['bs2Ions']>0){echo "    <TH>b*<SUP>$double</SUP></TH>\n";}
if ($include['b0Ions']>0) {echo "    <TH>b<SUP>0</SUP></TH>\n";}
if ($include['b02Ions']>0){echo "    <TH>b<SUP>0$double</SUP></TH>\n";}
if ($include['cIons']>0)  {echo "    <TH>c</TH>\n";}
if ($include['c2Ions']>0) {echo "    <TH>c<SUP>$double</SUP></TH>\n";}
if ($include['dIons']>0)  {echo "    <TH>d</TH>\n";}
if ($include['dpIons']>0) {echo "    <TH>d'</TH>\n";}
echo "    <TH>Seq.</TH>\n";
if ($include['vIons']>0)  {echo "    <TH>v</TH>\n";}
if ($include['wIons']>0)  {echo "    <TH>w</TH>\n";}
if ($include['wpIons']>0)  {echo "    <TH>w'</TH>\n";}
if ($include['xIons']>0)  {echo "    <TH>x</TH>\n";}
if ($include['x2Ions']>0) {echo "    <TH>x<SUP>$double</SUP></TH>\n";}
if ($include['yIons']>0)  {echo "    <TH>y</TH>\n";}
if ($include['y2Ions']>0) {echo "    <TH>y<SUP>$double</SUP></TH>\n";}
if ($include['ysIons']>0) {echo "    <TH>y*</TH>\n";}
if ($include['ys2Ions']>0){echo "    <TH>y*<SUP>$double</SUP></TH>\n";}
if ($include['y0Ions']>0) {echo "    <TH>y<SUP>0</SUP></TH>\n";}
if ($include['y02Ions']>0){echo "    <TH>y<SUP>0$double</SUP></TH>\n";}
if ($include['zIons']>0)  {echo "    <TH>z</TH>\n";}
if ($include['z2Ions']>0) {echo "    <TH>z<SUP>$double</SUP></TH>\n";}
if ($include['zhIons']>0)  {echo "    <TH>z+1</TH>\n";}
if ($include['zh2Ions']>0) {echo "    <TH>z+1<SUP>$double</SUP></TH>\n";}
if ($include['zhhIons']>0)  {echo "    <TH>z+2</TH>\n";}
if ($include['zhh2Ions']>0) {echo "    <TH>z+2<SUP>$double</SUP></TH>\n";}
?>
    <TH>#</TH>
  </TR>
<?php 
for ($i = 1; $i <= strlen($peptide); $i++) {
  $j = strlen($peptide) - $i + 1;
  echo "  <TR ALIGN=\"RIGHT\">\n";
  echo "    <TD><B><FONT COLOR=#0000FF>$i</FONT></B></TD>\n";
  if ($include['immIons']>0) printMasses($i-1, $immIons, 0);
  if ($include['aIons']>0)   printMasses($i-1, $aIons, $seriesStr['iatol']);
  if ($include['a2Ions']>0)  printMasses($i-1, $a2Ions, $seriesStr['ia2tol']);
  if ($include['asIons']>0)  printMasses($i-1, $asIons, $seriesStr['iastol']);
  if ($include['as2Ions']>0) printMasses($i-1, $as2Ions, 0);
  if ($include['a0Ions']>0)  printMasses($i-1, $a0Ions, 0);
  if ($include['a02Ions']>0) printMasses($i-1, $a02Ions, 0);
  if ($include['bIons']>0)   printMasses($i-1, $bIons, $seriesStr['ibtol']);
  if ($include['b2Ions']>0)  printMasses($i-1, $b2Ions, $seriesStr['ib2tol']);
  if ($include['bsIons']>0)  printMasses($i-1, $bsIons, $seriesStr['ibstol']);
  if ($include['bs2Ions']>0) printMasses($i-1, $bs2Ions, 0);
  if ($include['b0Ions']>0)  printMasses($i-1, $b0Ions, 0);
  if ($include['b02Ions']>0) printMasses($i-1, $b02Ions, 0);
  if ($include['cIons']>0)   printMasses($i-1, $cIons, $seriesStr['ictol']);
  if ($include['c2Ions']>0)  printMasses($i-1, $c2Ions, $seriesStr['ic2tol']);
  if ($include['dIons']>0)   printMasses($i-1, $dIons, 0);
  if ($include['dpIons']>0)  printMasses($i-1, $dpIons, 0);
  echo "    <TD ALIGN=\"CENTER\"><B><FONT COLOR=#0000FF>".strtoupper($peptide[$i-1])."</FONT></B></TD>\n";
  if ($include['vIons']>0)   printMasses($j-1, $vIons, 0);
  if ($include['wIons']>0)   printMasses($j-1, $wIons, 0);
  if ($include['wpIons']>0)  printMasses($j-1, $wpIons, 0);
  if ($include['xIons']>0)   printMasses($j-1, $xIons, $seriesStr['ixtol']);
  if ($include['x2Ions']>0)  printMasses($j-1, $x2Ions, $seriesStr['ix2tol']);
  if ($include['yIons']>0)   printMasses($j-1, $yIons, $seriesStr['iytol']);
  if ($include['y2Ions']>0)  printMasses($j-1, $y2Ions, $seriesStr['iy2tol']);
  if ($include['ysIons']>0)  printMasses($j-1, $ysIons, $seriesStr['iystol']);
  if ($include['ys2Ions']>0) printMasses($j-1, $ys2Ions, 0);
  if ($include['y0Ions']>0)  printMasses($j-1, $y0Ions, 0);
  if ($include['y02Ions']>0) printMasses($j-1, $y02Ions, 0);
  if ($include['zIons']>0)   printMasses($j-1, $zIons, $seriesStr['iztol']);
  if ($include['z2Ions']>0)  printMasses($j-1, $z2Ions, $seriesStr['iz2tol']);
  if ($include['zhIons']>0)  printMasses($j-1, $zhIons, $seriesStr['izhtol']);
  if ($include['zh2Ions']>0) printMasses($j-1, $zh2Ions, $seriesStr['izh2tol']);
  if ($include['zhhIons']>0) printMasses($j-1, $zhhIons, $seriesStr['izhhtol']);
  if ($include['zhh2Ions']>0)printMasses($j-1, $zhh2Ions, $seriesStr['izhh2tol']);
  echo "    <TD><B><FONT COLOR=#0000FF>$j</FONT></B></TD>\n";
  echo "  </TR>\n";
}
?>
</TABLE>

<P><IMG SRC="mass_error.php?units=<?php echo $iTolU;?>&massList=<?php echo $massList_gif;?>&errorList=<?php echo $errorList;?>" WIDTH=450 HEIGHT=150 ALT="Error Distribution">
<IMG SRC="mass_error.php?units=ppm&massList=<?php echo $massList_gif;?>&errorList=<?php echo $errorList;?>" WIDTH=450 HEIGHT=150 ALT="Error Distribution (ppm)">

<!--table>
  <tr>
  <td><a href='javascript: popTest_1();'>[Test Image_1]</a></td>
  <td><a href='javascript: popTest_2();'>[Test Image_2]</a></td>
  </tr>
</table-->
<P>NCBI <B>BLAST</B> search of <A HREF="
http://www.ncbi.nlm.nih.gov/blast/Blast.cgi?ALIGNMENTS=50&ALIGNMENT_VIEW=Pairwise
&AUTO_FORMAT=Semiauto&CLIENT=web&DATABASE=nr&DESCRIPTIONS=100&ENTREZ_QUERY=(none)
&EXPECT=20000&FORMAT_BLOCK_ON_RESPAGE=None&FORMAT_OBJECT=Alignment&FORMAT_TYPE=HTML
&GAPCOSTS=9+1&I_THRESH=0.001&LAYOUT=TwoWindows&MATRIX_NAME=PAM30&NCBI_GI=on
&PAGE=Proteins&PROGRAM=blastp&QUERY=<?php echo $pepSequences;?>
&SERVICE=plain&SET_DEFAULTS.x=32&SET_DEFAULTS.y=7&SHOW_OVERVIEW=on&WORD_SIZE=2
&END_OF_HTTPGET=Yes" TARGET="_blank"><?php echo $pepSequences;?></A><BR>
(Parameters: blastp, nr protein database, expect=20000, no filter, PAM30)<BR>
<p><b>All matches to this query</b><br>
<p><TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>
  <TR BGCOLOR=#cccccc>
    <TH>Score</TH>
    <TH>Mr(calc):</TH>
    <TH>Delta</TH>
    <TH>Sequence</TH>
  </TR>
<?php 
for ($i = 0; $i < count($std_matches); $i++) {
  $tmp = preg_split("/\;/", $std_matches[$i]);
  $std_fields = preg_split("/\,/", $tmp[0]);
  $tmp1 = preg_split("/\,/", $tmp[1]);
  $right = array();
  $right = preg_split("/\:/", $tmp1[0]);
  $matchIndex = preg_replace("/\"/","",$right[0]);
  echo "<tr><td>". sprintf("%.1f", $std_fields[9]). "</td><td>"
      . sprintf("%7.2f", $std_fields[3])
      . "</td><td>"
      . sprintf("%7.2f", $std_fields[4])
      . "</td><td>"
      /*
      . "<a href=\"ProhitsMascot_pepHTML.php?file="
      . $file
      . "&query="
      . $std_fields[0]
      . "&hit="
      . $std_fields[1]
      . "&index="
      . $matchIndex
      . "&expPara=". $exportingParameterStr
      . "\">"
      */
      . $std_fields[6]
      . "</td></tr>";
}
?>
</table>
<p>
</body>
</html>
