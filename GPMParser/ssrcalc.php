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


$file_version = "ssrcalc.php, v1. 2010.01.15";

$cnt02 = '';
$cnt03 = '';
$K = '';
$KSCALE = '';
$LPLim = '';
$LPSFac = '';
$NOCLUSTER = '';
$NODIGEST = '';
$NOELECTRIC = '';
$score = '';
$SPLim = '';
$SPSFac = '';
$step1 = '';
$SUMSCALE1 = '';
$SUMSCALE2 = '';
$SUMSCALE3 = '';
$SUMSCALE4 = '';
$UDF21 = '';
$UDF22 = '';
$UDF31 = '';
$UDF32 = '';
$v1 = '';
$Z01 = '';
$Z02 = '';
$Z03 = '';
$Z04 = '';
//--hash array---
$AMASS = array();
$CT = array();
$NT = array();
$PK = array();
$RC = array();
$RC1 = array();
$RC2 = array();
$RCN = array();
$RCN2 = array();
$UndKRH = array();
//--index array---
$eK = array();
$emax = array();
$emin = array();
$pick = array();
$val1 = array();

$badAA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

initializeGlobals();
ReadParmFile();

function initializeGlobals(){
  global $cnt02,$cnt03,$K,$KSCALE,$LPLim,$LPSFac,$NOCLUSTER,$NODIGEST,$NOELECTRIC,$score,$SPLim,$SPSFac,$step1;
	global $SUMSCALE1,$SUMSCALE2,$SUMSCALE3,$SUMSCALE4,$UDF21,$UDF22,$UDF31,$UDF32,$v1,$Z01,$Z02,$Z03,$Z04;
  global $AMASS,$CT,$NT,$PK,$RC,$RC1,$RC2,$RCN,$RCN2,$UndKRH;//--hash
  global $eK,$emax,$emin,$pick,$val1;
// control variables, 0 means leaving them ON, 1 means turning them OFF
	$NOELECTRIC=0;
	$NOCLUSTER=0;
	$NODIGEST=1;

// Length Scaling lenght limits and scaling factors
	$LPLim = 20;      // long peptide lower length limit
	$SPLim = 8;       // short peptide upper length limit
	$LPSFac = 0.0245; // long peptide scaling factor
	$SPSFac = -0.05;  // short peptide scaling factor

// UnDigested (missed cuts) scaling Factors
	$UDF21=.13; $UDF22=.08;   // rightmost
	$UDF31=.05; $UDF32=.05;   // inside string

// total correction values, 20..30 / 30..40 / 40..50 /50..500
	$SUMSCALE1=.22; $SUMSCALE2=.29; $SUMSCALE3=.33; $SUMSCALE4=.39;

// clusterness scaling: i.e. weight to give cluster correction.
	$KSCALE=0.15;

// isoelectric scaling factors
	$Z01=-.030;    $Z02=0.6;   // negative delta values
	$Z03=0.00;     $Z04=0.0;   // positive delta values
}

function ReadParmFile(){
  global $AMASS,$CT,$NT,$PK,$RC,$RC1,$RC2,$RCN,$RCN2,$UndKRH;//--hash
  global $eK,$emax,$emin,$pick,$val1,$badAA;
  
  $pSet = "";
  $cnt01 = 0;
  $cnt02 = 0;
  $cnt03 = 0;
  
  $F1 = @fopen("./SSRCalc2.par", "r") or die("Cannae open parameter file, Capn");
  
  while (!feof($F1)){
    $buffer = fgets($F1, 4096);
    $buffer = trim($buffer);
    $pattern = '/#.*/i';
    $replacement = '';
    $buffer = preg_replace($pattern, $replacement, $buffer); //delete comments
    $pattern = '/\s/';
    $replacement = '';
    $buffer = preg_replace($pattern, $replacement, $buffer); //delete white
    if(!$buffer) continue;
//echo $buffer."<br>";        
    //Which parms?
    if(preg_match('/\<(AAPARAMS)\>/i', $buffer)){
      $pSet="AAPARAMS";  
      continue;
    }else if(preg_match('/\<ISOPARAMS\>/i', $buffer)){
      $pSet="ISOPARAMS"; 
      continue;
    }else if(preg_match('/\<CLUSTCOMB\>/i', $buffer)){
      $pSet="CLUSTCOMB"; 
      continue;
    }else if(preg_match('/\<\/.+\>/i', $buffer)){
      $pSet=""; 
      continue;
    }

    if(preg_match('/AAPARAMS/', $pSet)){
      list($aa1,$rc,$rc1,$rc2,$rcn,$rcn2,$krh,$amass,$ct_,$nt_,$pk_) = explode('|', $buffer);
      $RC[$aa1] = $rc; 
      $RC1[$aa1] = $rc1; 
      $RC2[$aa1] = $rc2; //  Retention Factors
      $RCN[$aa1] = $rcn; 
      $RCN2[$aa1] = $rcn2;  
      $UndKRH[$aa1] = $krh;    // Factors for aa's near undigested KRH.
      $AMASS[$aa1] = $amass;   // aa masses in Daltons
      $CT[$aa1] = $ct_; 
      $NT[$aa1] = $nt_; 
      $PK[$aa1] = $pk_;  // Iso-electric factors
      
      $badAA =  preg_replace('/$aa1/i', '', $badAA);
      $cnt01++;
    }elseif(preg_match('/ISOPARAMS/', $pSet)){
      list($e1,$e2,$e3) = explode('|', $buffer);
      $emin[$cnt02] = $e1;
      $emax[$cnt02] = $e2;
      $eK[$cnt02] = $e3;
      $cnt02++;
    }elseif(preg_match('/CLUSTCOMB/', $pSet)){
      list($s1,$s2) = explode('|', $buffer);
      $search  = array('l', 'v');
      $replace = array('5', '1');
      $s1 = str_replace($search, $replace, $s1);
      $val1[$cnt03]=$s2; 
      $pick[$cnt03]=$s1;
      $cnt03++;
    }
  }
/*echo "</pre>";
echo "\$RC";
print_r($RC);
echo "\$RC1";
print_r($RC1);
echo "\$RC2";
print_r($RC2);
echo "\$RCN";
print_r($RCN);
echo "\$RCN2";
print_r($RCN2);
echo "\$UndKRH";
print_r($UndKRH);
echo "\$AMASS";
print_r($AMASS);
echo "\$CT";
print_r($CT);
echo "\$NT";
print_r($NT);
echo "\$PK";
print_r($PK);
echo "</pre>";*/
}

// if residue masses exists in output.xml file, use those instead of defaults from SSRCalc2.par
function load_masses($path){
  global $AMASS;
	$residue = array();
	$mass = array();
	$H2O = 0;
	$NH3 = 0;
	$a = 0;
	$found = 0;
  
  if(!$INPUT = fopen($path,"r")){
    if($INPUT = fopen("$path.gz","r")){ 
      fclose($INPUT);
      system("gzip -d $path.gz");
      $INPUT = fopen("$path","r");
    }else{
			echo "Content-type: text/html\n\n";
			echo "<pre>
				<html>
					<head>
					<title>GPM</title>
					<link rel=\"stylesheet\" href=\"/tandem/tandem-style.css\" />
					<link rel=\"stylesheet\" href=\"/tandem/tandem-style-print.css\" media=\"print\"/>
					</head>
				<body>
				<table><tr><td>
				<a href=\"/tandem/thegpm_tandem.html\"><img src=\"/pics/gpm.png\" border=\"0\"></a>
				</td><td>&nbsp;&nbsp;</td><td valign=\"middle\" width=\"400\">
				The model file <b>$path</b> <BR>
				could not be found in the archive.</td></tr>
				</table></body></html>
        </pre>";
			exit;
		}
	}
  while(!feof($INPUT)) {
    $buffer = fgets($INPUT, 4096);
    if(preg_match("/group label=\"residue mass parameters\" type=\"parameters\"/", $buffer)){
      $found=1;
      $tmp_str = trim($buffer);
      while(!feof($INPUT)){
        $buffer = fgets($INPUT, 4096);
        if(preg_match("/\<\/group\>/", $buffer)){
          preg_match_all("/\<aa type=\"(\w)\" mass=\"\d+\.\d+\" \/>/",$tmp_str,$matches);
          if($matches[1]) $residue = $matches[1];
          preg_match_all("/\<aa type=\"\w\" mass=\"(\d+\.\d+)\" \/>/",$tmp_str,$matches_2);
          if($matches_2[1]) $mass = $matches_2[1];
          preg_match("/\<molecule type=\"NH3\" mass=\"(\d+\.\d+)\" \/>/",$tmp_str,$matches_3);
          if($matches_3[1]) $NH3 = $matches_3[1];
          preg_match("/\<molecule type=\"H2O\" mass=\"(\d+\.\d+)\" \/>/",$tmp_str,$matches_4);
          if($matches_4[1]) $H2O = $matches_4[1];
          $AMASS = array_combine($residue, $mass);
    			$AMASS['H2O'] = $H2O;
    			$AMASS['NH3'] = $NH3;
    			fclose($INPUT);
          $tmp_str = '';
          break;
        }else{
          $tmp_str .= trim($buffer);
        }
      }
      if($found) break;
    }
  }
}

function TSUM($sq2){
  global $RC1,$RC2,$RCN,$RCN2,$RC,$SUMSCALE1,$SUMSCALE2,$SUMSCALE3,$SUMSCALE4;
  $tsum2=0;
  //my (
  $i = ''; 
  $pick = ''; 
  $sze = ''; 
  $edge = '';
  //);
  // core summation
  $sze = strlen($sq2); 
  $edge=0;
  if($sze < 4) return $tsum2;
   
  $tsum2 = $RC1[substr($sq2,0,1)]        // Sum weights for 1st,
         + $RC2[substr($sq2,1,1)]        //                      second,
         + $RCN[substr($sq2,$sze-1,1)]   //                              last, 
         + $RCN2[substr($sq2,$sze-2,1)]; //                                    and second last a.a.
         
  for($i=2;$i<$sze-2;$i++){
    $tsum2 += $RC[substr($sq2,$i,1)];       // sum weights for a.a.s in the middle.
  }
  // 2- clusterness # NB:weighting of v1 is now done in subrtn.
  $v1 = clusterness($sq2); 
  $tsum2 -= $v1 ;
  
  // 2.5- proline fix
  $v1 = &proline($sq2); 
  $tsum2 -= $v1;
  
  
  // 3- length scaling correction
  $v1=&length_scale($sze); 
  $tsum2 *= $v1;
  
  // 4- total sum correction
  if($tsum2 >= 20 && $tsum2 < 30) $tsum2 = $tsum2 - ($tsum2 - 18) * $SUMSCALE1;
  if($tsum2 >= 30 && $tsum2 < 40) $tsum2 = $tsum2 - ($tsum2 - 18) * $SUMSCALE2;
  if($tsum2 >= 40 && $tsum2 < 50) $tsum2 = $tsum2 - ($tsum2 - 18) * $SUMSCALE3;
  if($tsum2 >= 50)                $tsum2 = $tsum2 - ($tsum2 - 18) * $SUMSCALE4;
  
  // 4.5- isoelectric change
  $v1 = newiso($sq2,$tsum2);
  
//echo $tsum2."<br>";  
  
  $tsum2 += $v1;
  $K=1;
  return $tsum2 * $K;
}

function clusterness($a){
  global $NOCLUSTER,$pick,$val1,$KSCALE;
  //my(
  $score = '';
  $i = '';
  $x1 = '';
  $occur0 = '';
  $pt = '';
  $sk = '';
  $addit = '';
  //);
  if($NOCLUSTER==1) return 0;
  
  $a = strtoupper($a);  // uppercase
  $a = preg_replace('/[WFLI]/', '5', $a);
  $a = preg_replace('/[MYV]/', '1', $a);
  $a = preg_replace('/[A-Z]/', '0', $a);
  $a = "0" . $a . "0"; // pad it out
  
//echo $a."<br>";  
  
    
	$score=0;
	for ($i=0;$i<count($pick);$i++){
		$pt = $pick[$i]; 
    $sk = $val1[$i];
		$occur0 = 0; 
		$x1= "0" . $pt . "0"; // pad it out
//echo $x1."-----<br>";    
    preg_match_all("/($x1)/",$a,$matches);
    $tmp_occur0 = count($matches[1]);
    $occur0 += $tmp_occur0;
    
    //if(preg_match_all("/$x1/",$a,$matches)){
      //$occur0++;
    //}
		//while(preg_match_all("/$x1/",$a)) $occur0++;  # count occurrences
		if($occur0>0){ 
//echo $occur0."-----<br>";     
			$addit = $sk * $occur0;
			$score = $score + $addit; 
		}
	}
//echo $score."<br>";
	return $score * $KSCALE;
}

// process based on proline - v 2 algorithm
function proline($seq1){
	$score=0;
  if(preg_match("/PP/",$seq1)) $score = 2.0;
  if(preg_match("/PPP/",$seq1)) $score = 4.0;
  if(preg_match("/PPPP/",$seq1)) $score = 6.0;
	return $score;
}

// scaling based on sequence length 
function length_scale($sqlen){
  global $SPLim,$SPSFac,$LPLim,$LPSFac;
	$LS = 1;
	if($sqlen < $SPLim){ 
    $LS = 1 + $SPSFac * ($SPLim - $sqlen);
  }elseif($sqlen > $LPLim){
    $LS = 1 / (1 + $LPSFac * ($sqlen - $LPLim)); 
  }
	return $LS;
}

// process based on new isoelectric stuff 
function newiso($seq1,$tsum){
  global $NOELECTRIC,$AMASS,$Z01,$Z02,$Z03,$Z04;
  $i = '';
  $mass = '';
  $cf1 = '';
  $delta1 = '';
  $corr01 = '';
  $pi1 = '';
  $lmass = '';
	
	if($NOELECTRIC == 1) return 0;
	// compute mass
	$mass=0;  
	for($i=0;$i<strlen($seq1);$i++){
		$cf1 = substr($seq1,$i,1);
		$mass = $mass + $AMASS[$cf1];
	}
//echo $mass."<br>";	exit;
	// compute isoelectric value
	$pi1 = electric($seq1);  
	$lmass = 1.8014 * log($mass);
		
	// make mass correction
	$delta1 = $pi1 - 19.107 + $lmass;
	if($delta1 < 0){
		// apply corrected value as scaling factor
		$corr01 = ($tsum * $Z01 + $Z02) * $delta1;
	}
	
	if($delta1 > 0)	{
		$corr01 = ($tsum * $Z03 + $Z04) * $delta1;
	}
	return $corr01;
}

function electric($proc){
  global $CT,$NT;
  $ss = '';
  $s1 = '';
  $s2 = '';
  $i = '';
  $z = '';
  $best = '';
  $min = '';
  $check = '';
  $e = '';
  $pk0 = '';
  $pk1 = '';
	
	$aaCNT = array('K'=>0, 'R'=>0, 'H'=>0, 'D'=>0, 'E'=>0, 'C'=>0, 'Y'=>0);
  
	// get c and n terminus acids
	$ss = strlen($proc); 

	$s1 = substr($proc,0,1); 
	$s2 = substr($proc,$ss-1,1);

	$pk0 = $CT[$s1];
	$pk1 = $NT[$s2];

	// count them up
	for($i=0;$i<strlen($proc);$i++){
		$e = substr($proc,$i,1);
    if(preg_match("/[KRHDECY]/",$e)) $aaCNT[$e]++;
	}
	// cycle through pH values looking for closest to zero

	// coarse pass
	$best = 0;
  $min = 100000; 
  $step1 = .3;
	for($z=.01; $z<=14; $z=$z+$step1){
		$check = CalcR($z, $pk0, $pk1, $aaCNT);
    if($check < 0) $check = 0 - $check;
		if($check < $min){ 
      $min = $check; 
      $best = $z; 
    }
	}
	$best1 = $best;
	
	// fine pass
	$min = 100000;
	for($z=$best1-$step1; $z<=$best1+$step1; $z=$z+.01){
		$check = CalcR($z, $pk0, $pk1, $aaCNT); 
    if($check < 0) $check = 0 - $check;
		if($check < $min){ 
      $min = $check; 
      $best = $z; 
    }
	}
	return $best;
}

function CalcR($pH,$PK0,$PK1,$CNTref){	
  global $PK;
	$cr0 = _partial_charge($PK0, $pH)  // n terminus
       + $CNTref['K'] * _partial_charge( $PK['K'], $pH)  // lys
       + $CNTref['R'] * _partial_charge( $PK['R'], $pH)  // arg
       + $CNTref['H'] * _partial_charge( $PK['H'], $pH)  // his
       - $CNTref['D'] * _partial_charge( $pH, $PK['D'])  // asp
       - $CNTref['E'] * _partial_charge( $pH, $PK['E'])  // glu
       - $CNTref['Y'] * _partial_charge( $pH, $PK['Y'])  // try
       - _partial_charge( $pH, $PK1); // c terminus

// The following was taken out of the formula for R
//  - $CNTref->{C} * _partial_charge( $pH,      $PK{C} )    # cys
	return $cr0;
}

function _partial_charge($p0,$p1){
   $cr = pow(10, ($p0 - $p1));
   return $cr / ( $cr + 1 );
}
?>
