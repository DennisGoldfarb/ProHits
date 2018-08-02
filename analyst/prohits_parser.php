<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/
require "./common.php";

require "./ssrcalc.php";

$u133_all = array();
$u95a_all = array();
$peptides = array();
$bIsHuman = 0;
$pep_locations = array();
$file_version = "ptable.pl, v. 2005.09.07";

// Calculate the dissociation constant, K, from the appropriate pK values
// B. Bjellqvist, et al. Electrophoresis (1994) 15:529-539.
$K_Asp = exp(-2.303 * 4.05);
$K_Glu = exp(-2.303 * 4.45);
$K_His = exp(-2.303 * 5.98);
$K_Cys = exp(-2.303 * 9.0);
$K_Tyr = exp(-2.303 * 10.0);
$K_Lys = exp(-2.303 * 10.0);
$K_Arg = exp(-2.303 * 12.0);
$K_CTerminal = exp(-2.303 * 3.55);
$K_NTerminal = exp(-2.303 * 7.00);

$sort = '';
$path = '';
$proex = 0;
$pepex = 100;
$npep = '';
$spliter = '';
$spliter = ";;";

$proex_dot = 0;
$pepex_dot = 0;
$corA = '';


require("../common/site_permission.inc.php");
//$path="./38597_D05_12204_gpm.xml&field_spliter=;;&proex=0&pepex=100&proex_dot=0&pepex_dot=0";
$path="./38597_D05_12204_gpm.xml";
$url = $path;
$gpm = $path;
$corABok = 0;

$proex_dot = $proex_dot + 0;
$proex = $proex - $proex_dot;

$pepex_dot = $pepex_dot + 0;
$pepex = $pepex - $pepex_dot;


$pattern = '/^-?(\d+\.?\d*)|(\.\d+)$/';
preg_match($pattern, $corA); 



if(isset($corA) && isset($corB) && preg_match($pattern, $corA) && preg_match($pattern, $corB)) $corABok = 1;
if($corABok == 0){
  $corA = 0;
  $corB = 1;
}


$pattern_1 = '/GPM[A-Z]*[0-9]{11}\./i';
$pattern_2 = '/.*(GPM[A-Z]*[0-9]{11})\..*/i';
$replacement = '$1';

if(preg_match($pattern_1, $gpm)){
  $gpm = preg_replace($pattern_2, $replacement, $gpm);
}else{
  $gpm = "";
}

//$m_pfAaMass = '';
$m_resCount = array();

load_masses($path);
$m_pfAaMass=set_aa($path);

// rc - added 20050222 - used in get_mass()192.197.250192.197.250.100
$m_fWater = $m_pfAaMass['H2O'];


$label = '';
$INPUT = fopen("$path","r");
while(!feof($INPUT)) {
  $buffer = fgets($INPUT, 4096);
  if(preg_match("/\<bioml/", $buffer)){
    if(preg_match("/label=\"/", $buffer)){
      $pattern = '/.*label=\"(.*?)\".*/';
      $replacement = '$1';
      $label = preg_replace($pattern, $replacement, $buffer);
      echo $label."<br>";
    }
    break;
  }
}  
fclose($INPUT);

$patterns = array();
$patterns[0] = '/\\\/';
$patterns[1] = '/:/';
$patterns[2] = '/\//';
$patterns[3] = '/·\-/';
$replacements = array();
$replacements[3] = '-';
$replacements[2] = '.';
$replacements[1] = '-';

$label = preg_replace($patterns, $replacements, $label);
echo $label."<br>";

$xlpath=$path;
$xlpath = preg_replace('/\.xml/i', '.xls', $xlpath);
echo $xlpath."<br>"; 

print "Content-type: text/html\n\n";
print "<pre>";

$INPUT = fopen("$path","r") or die("$path not found");

$line = '';
$id = '';
$temp = '';
$string = '';
$uid = 0;
$uid = array();
$proteins = array();
$mass = array();
$pi = array();
$sequences = array();
$I = array();
$intensity = "_";
$ri = '';
$sequence = '';
$expect = 0;
$expect = array();
$counts = array();
$proteinids = array();
$descriptions = array();
$tmpDescription = '';
$coverage = array();
$length = '';
$length = array();
$parameters = '';
$temp = ''; 

while(!feof($INPUT)) {
  $buffer = fgets($INPUT, 4096);
  if(preg_match("/\<group\slabel=\"input\sparameters\"\stype=\"parameters\"/", $buffer)){
    $buffer = fgets($INPUT, 4096);
    if(!preg_match("/\<\/group/", $buffer)){
      $string = $buffer;
      $temp = get_feature($string,"label");
echo "\$temp=$temp<br>";exit;
    }
  }
}    




function get_root(){
	return "..";
}
?>