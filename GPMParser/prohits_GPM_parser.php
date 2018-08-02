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

error_reporting(0);
ini_set("memory_limit","800M");
set_time_limit(3600*24);  // it will execute for 24 hours
require "./common.php";
require "./ssrcalc.php";
require "../config/conf.inc.php";
require "../common/mysqlDB_class.php";
require "../common/common_fun.inc.php";

if(!isset($_SERVER['argv'][1]) || !$_SERVER['argv'][1]){
  echo "not data file";
}

$protein_DB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);

$path = $_SERVER['argv'][1];
$proex = (isset($_SERVER['argv'][2]))?$_SERVER['argv'][2]:0;
$proex_dot = (isset($_SERVER['argv'][3]))?$_SERVER['argv'][3]:0;
$pepex = (isset($_SERVER['argv'][4]))?$_SERVER['argv'][4]:0;
$pepex_dot = (isset($_SERVER['argv'][5]))?$_SERVER['argv'][5]:0;

$tmp_dir_name = dirname($path); 
$tmp_txt_name = basename($path, ".xml").".txt";
$full_file_name = $tmp_dir_name."/".$tmp_txt_name;
//echo $full_file_name."<br>";exit;

$u133_all = array();
$u95a_all = array();
$peptides = array();
$bIsHuman = 0;
$pep_locations = array();
$file_version = "prohits_GPM_parser.php, v1. 2010.01.15";

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
$npep = '';
$spliter = '';
$spliter = ";;";
$corA = '';

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

$xlpath=$path;
$xlpath = preg_replace('/\.xml/i', '.xls', $xlpath);

$INPUT = fopen("$path","r") or die("$path not found");

$line = '';
$id = '';
$temp = '';
$string = '';
$uid = 0;
$uid_arr = array();
$proteins = array();
$mass = array();
$pi = array();
$sequences = array();
$I = array();
$intensity = "_";
$ri = '';
$sequence = '';
$expect = 0;
$expect_arr = array();
$counts = array();
$proteinids = array();
$descriptions = array();
$tmpDescription = '';
$coverage = array();
$length_single = '';
$length = array();
$parameters = '';
$temp = ''; 

while(!feof($INPUT)) {
  $buffer = fgets($INPUT, 4096);
  if(preg_match("/\<group\slabel=\"input\sparameters\"\stype=\"parameters\"/", $buffer)){
    $buffer = fgets($INPUT, 4096); 
    
    while(!preg_match("/\<\/group/", $buffer)){
      $string = $buffer;   
      $temp = get_feature($string,"label");
      $parameters .= "<B>$temp";
      $buffer = preg_replace('/\<.*?\>/', '', $buffer);
      $buffer = preg_replace('/\t|\n/', '', $buffer);
      $parameters .= ":  <font color=red>".$buffer."</font></B>\n";
      $buffer = fgets($INPUT, 4096);
    }
  }
 
  if(preg_match('/group/is', $buffer) && preg_match('/type=\"model\"/is', $buffer)){
    $buffer = fgets($INPUT, 4096);
    $string = $buffer;   
    $id = get_feature($string,"label");
    $real_id = get_protein_typeid($id);
    
    if(preg_match('/ENSP0/', $id)){
      $bIsHuman = 1;
    }
    $uid = get_feature($string,"uid");
    $expect = get_feature($string,"expect");
    if($expect >= $proex){
      continue;
    }
    
    $tmpDescription_tmp = trim(fgets($INPUT, 4096));
    $tmpDescription = '';
    if(preg_match('/>.+?\s(.+?)</', $tmpDescription_tmp,$matches)){
      $tmpDescription = $matches[1];
    }elseif($real_id){
      $tmp_protein_type = get_protein_ID_type($real_id);
      if($tmp_protein_type == "ENS"){
        $SQL = "SELECT `Description` FROM `Protein_AccessionENS` WHERE `ENSP`='$real_id'";
      }elseif($tmp_protein_type == "IPI"){
        $SQL = "SELECT `Description` FROM `Protein_AccessionIPI` WHERE `IPI`='$real_id'";
      }elseif($tmp_protein_type == "UniProt"){
        $SQL = "SELECT `Description` FROM `Protein_Accession` WHERE `UniProtID`='$real_id'";
      }elseif($tmp_protein_type == "GI"){
        $SQL = "SELECT `Description` FROM `Protein_Accession` WHERE `GI`='$real_id'";
      }else{
        $SQL = "SELECT `Description` FROM `Protein_Accession` WHERE `Acc`='$real_id'";
      }
      if($protein_arr = $protein_DB->fetch($SQL)){
        $tmpDescription = $protein_arr['Description'];
      }
    }  
    
    $temp = 0;
    foreach($uid_arr as $line){ 
      if($line == $uid)  { 
        $temp = 1;
      }
    }
    if($temp == 0){
      array_push($proteins,$id);
      if(isset($proteinids[$id])){
        $proteinids[$id] += 1;
      }else{
        $proteinids[$id] = 1;
      }  
      array_push($uid_arr,$uid);
      array_push($expect_arr,$expect);
      array_push($descriptions, $tmpDescription);
      $sequence = "";
      if(array_key_exists($uid, $counts)){
        $counts[$uid] += 1;
      }else{
        $counts[$uid] = 1;
      }  
      while(!feof($INPUT) && $buffer){
        if(preg_match("/\<protein/s", $buffer) && preg_match("/uid=\"$uid\"/s", $buffer)){
          $intensity = get_feature($buffer,"sumI");
          if($intensity != "_"){
            array_push($I,$intensity);
          }
          while(!preg_match("/\<peptide/is", $buffer)){
            $buffer = fgets($INPUT, 4096);
          }
          if(preg_match("/\<peptide/", $buffer)){
            $string = $buffer;
            $length_single = get_feature($string,"end");
          }
          $buffer = fgets($INPUT, 4096);
          while(!preg_match("/\<\/peptide/is", $buffer) && !preg_match("/\<domain/", $buffer)){
            $buffer = preg_replace('/\s+/i', '', $buffer);
            $sequence .= $buffer;
            $buffer = fgets($INPUT, 4096);
          }
          break;
        }
        $buffer = fgets($INPUT, 4096);
      }
      while(!preg_match("/\<\/group/s", $buffer)){
        if(preg_match("/\<protein/s", $buffer)){
          $uid = get_feature($buffer,"uid");
          if(array_key_exists($uid, $counts)){
            $counts[$uid] += 1;
          }else{
            $counts[$uid] = 1;
          }
        }
        $buffer = fgets($INPUT, 4096);
      }
      $sequence = preg_replace('/\b/', '', $sequence);
      array_push($length, $length_single);
      array_push($sequences,$sequence);
      array_push($pi,get_pi($sequence));
      array_push($mass,get_mass($sequence));
    }else{
      $counts[$uid] += 1;
      $buffer = fgets($INPUT, 4096);
      while(!preg_match("/\<\/group/s", $buffer)){
        if(preg_match("/\<protein/s", $buffer)){
          $uid = get_feature($buffer,"uid");
          if(array_key_exists($uid, $counts)){
            $counts[$uid] += 1;
          }else{
            $counts[$uid] = 1;
          }
        }
        $buffer = fgets($INPUT, 4096);
      }
    }
  }
}

fclose($INPUT);

$pcount = array();
foreach($uid_arr as $line){
  array_push($pcount,$counts[$line]);
}
get_peptide();
draw_table($xlpath);

function get_pi($s){
  global $m_resCount;
  $m_resCount = array();
  $a = 0;
  $length = strlen($s);
  $seq = str_split($s);
  
  while($a < $length)  {
    if(isset($m_resCount[$seq[$a]])){
      $m_resCount[$seq[$a]] += 1;
    }else{
      $m_resCount[$seq[$a]] = 1;
    }  
    $a++;
  }
  
  $pH = 3.0;
  $charge = calc_charge($pH);
  
  if($charge < 0.0)  {
    return 3.0;
  }elseif($charge == 0.0)  {
    return 3.0;
  }
  $step = 1.0;
  $precision = 0.01;
  $a = 0;
  while(1){
    if($pH > 12.0)  {
      $pH = 11.9;
      break;
    }
    if(abs($charge) < $precision)  {
      break;
    }
    if($charge > 0.0)  {
      $pH += $step;
    }else{
    
      $pH -= $step;
      $step /= 2.0;
      $pH += $step;
    }
    $charge = calc_charge($pH);
    $a++;
  }
  return $pH;
}    

function calc_charge($pH){
  global $K_NTerminal,$K_CTerminal,$m_resCount,$K_Asp,$K_Glu,$K_Tyr,$K_Cys,$K_His,$K_Lys,$K_Arg;
  $H = exp(-2.303 * $pH);
  $TotalCharge = 0.0;
  $TotalCharge += 1.0/(1.0+($K_NTerminal/$H));
  $TotalCharge -= 1.0/(1.0+($H/$K_CTerminal));
  if(isset($m_resCount['D'])){
    $TotalCharge -= $m_resCount['D']*(1.0/(1.0+($H/$K_Asp)));
  }
  if(isset($m_resCount['E'])){  
    $TotalCharge -= $m_resCount['E']*(1.0/(1.0+($H/$K_Glu)));
  }
  if(isset($m_resCount['Y'])){
    $TotalCharge -= $m_resCount['Y']*(1.0/(1.0+($H/$K_Tyr)));
  }
  if(isset($m_resCount['C'])){  
    $TotalCharge -= $m_resCount['C']*(1.0/(1.0+($H/$K_Cys)));
  }  
  if(isset($m_resCount['H'])){
    $TotalCharge += $m_resCount['H']*(1.0/(1.0+($K_His/$H)));
  }
  if(isset($m_resCount['K'])){  
    $TotalCharge += $m_resCount['K']*(1.0/(1.0+($K_Lys/$H)));
  }
  if(isset($m_resCount['R'])){
    $TotalCharge += $m_resCount['R']*(1.0/(1.0+($K_Arg/$H)));
  }
//echo $TotalCharge."<br>";    
  return $TotalCharge;
}


function get_mass($s){
  global $m_fWater,$m_pfAaMass;
  $seq = str_split($s);
  $a = 0;
  $length = count($seq);
  $mass = $m_fWater;
  while($a < $length)  {
    $mass += $m_pfAaMass[$seq[$a]];
    $a++;
  }
  return $mass; 
}

function get_peptide(){
  global $path,$proex,$corA,$corB,$pepex,$spliter;
  global $peptides,$pep_locations,$coverage;
  
  $INPUT = fopen("$path","r") or die("$path (2) not found");
  
  $string = '';
  $pex = '';
  $feature = '';
  
  $s_cur = '';
  $s_pos = '';
  $space = '';
  $c = '';
  $num = '';
  $line = '';
  $feature = '';
  $e = '';
  $excel = '';
  $mod_list = '';
  
  $s_aa = array();
  $res_pos = array();
  $res = array();
  $res_mod = array();
  $res_mut = array();
  $end = array();
  $domain = array();
  $start = array();
  $seq_mut = array();
 
  $id = ''; 
  $intensity = ''; 
  $protein = ''; 
  $did = '';    
  $seq = '';
  $rtime = ''; 
  $uid = '';       
  $mh = '';    
  $charge = ''; 
  $expect = '';
  $pre = '';   
  $post = '';      
  $start_single = ''; 
  $end_single = '';    
  $delta = '';
  $cid = '';   
  $domain_single = '';    
  $ionFile = ''; 
  $old_uid = '';

  $rtimeLT0=0;
  $wegotsumI=0;

  $intensities = array(); 
  $rtimes = array(); 
  $proteins = array(); 
  $sequences = array(); 
  $ids = array();
  $uids = array();        
  $mhs = array();    
  $zeds = array();   
  $expects = array();   
  $starts = array();
  $ends = array();        
  $deltas = array(); 
  $pres = array();   
  $posts = array();     
  $mods = array();
  $ionFiles = array();    
  $tmp_locations = array();
  
  $old_uid = 0;
  
  $buffer = fgets($INPUT, 4096);
  
  while($buffer){
    if(preg_match("/group/is", $buffer) && preg_match("/type=\"model\"/is", $buffer)){
      $string = $buffer;
      $id = get_feature($string,"id");
      $cid = $id . ".1.1";

      $protein = get_feature($string,"label");
      $charge = get_feature($string,"z");
      $_sumI = get_feature($string,"sumI");
      if($_sumI == "_") { 
        $intensity = 1.0; 
      }else{
        $intensity = $_sumI ; 
        $wegotsumI = 1;
      }
      $expect = get_feature($string,"expect");      
      $string = fgets($INPUT, 4096);
      $uid = get_feature($string,"uid");
      $pex = get_feature($string,"expect");
      
      
      if($pex >= $proex){
        $buffer = fgets($INPUT, 4096);
        $cid = "";
        continue;
      }
    }    
    if(preg_match("/group/is", $buffer) && preg_match("/label=\"fragment ion mass spectrum\"/is", $buffer)){
       $buffer = fgets($INPUT, 4096);
       $string = $buffer;
       $string = preg_replace('/<note label=\"description\">||<\/note>/i', '', $string);
       $string = preg_replace('/\s+$/', '', $string);
       $ionFile = $string;
       array_push($ionFiles, $ionFile);
    }
    
    if(preg_match("/\<domain/is", $buffer)){
      $string = $buffer;  
      
      $did = get_feature($string,"id");
      
      if($did == $cid) {
        $start_single = get_feature($string,"start");
        $end_single = get_feature($string,"end");
        $pre = get_feature($string,"pre");
        $pre = strtolower($pre);
        $post = get_feature($string,"post");
        $post = strtolower($post);
        $mh = get_feature($string,"mh");
        $delta = get_feature($string,"delta");
        $domain_single = get_feature($string,"seq");        
        $seq = get_feature($string,"seq");
        $seq = strtoupper($seq); // all uppercase
        $seq = preg_replace('/[^A-Z]/', '', $seq);
        $newseq = 1;
         
        $line = $string;
        $res_pos = array();
        $res = array();
        $res_mod = array();
        $res_mut = array();
        
        while(!preg_match("/\<\/domain/", $buffer)){
          if(preg_match("/<aa/", $buffer)){
            $line = $buffer;
            $feature = "type";
            $e = get_feature($line,$feature);
            array_push($res,$e);
            $feature = "at";
            $e = get_feature($line,$feature);
            array_push($res_pos,$e);
            $feature = "modified";
            $e = get_feature($line,$feature);
            array_push($res_mod,$e);            
            $feature = "pm";
            $e = get_feature($line,$feature);
            array_push($res_mut,$e);
          }
          $buffer = fgets($INPUT, 4096);
        } 
        $s_cur = $start_single;
        $s_pos = 0;
        $space = 0;
        $s_aa = str_split($domain_single);
        $mod_list = "";        
         
        while($s_cur <= $end_single){
          $c = 0;
          $num = 0;
          $line = "";
           
          while($c < count($res)){
            if($s_cur == $res_pos[$c]){
            
              if(!preg_match("/_/", $res_mut[$c])){
                if($res_mod[$c] < 0.0)  {
                  $line .= $res_mut[$c]."(".$res_mod[$c].") ";
                }else{
                  $line .= $res_mut[$c]."(+".$res_mod[$c].") ";
                }
                if(!preg_match("/".$res_mut[$c]."/", $seq_mut[$s_cur-1])){
                  $seq_mut[$s_cur-1] .= $res_mut[$c];
                }
                $mod_list .= $s_aa[$s_pos]." [".$res_pos[$c]."] ".$res_mod[$c];
                $num = 2;
              }else{
                if($res_mod[$c] < 0.0){
                  $line .= $res_mod[$c] . " ";
                }else{
                  $line .= "+" . $res_mod[$c] . " ";
                }              
                $mod_list .= $s_aa[$s_pos]." [".$res_pos[$c]."] ".$res_mod[$c].",";
                $num = 1;
              }
            }
            $c++;  
          }
          $s_cur++;
          $s_pos++;
        }
                
        if($newseq){
          $rtime = TSUM($seq);
          if($rtime < $rtimeLT0) $rtimeLT0 = $rtime;
          
          array_push($sequences,$seq);
          array_push($proteins,$protein);
          array_push($intensities,$intensity);
          array_push($rtimes,$rtime);
          array_push($ids,"$id.1.1");
          array_push($uids,$uid);
          array_push($expects,$expect);
          array_push($starts,$start_single);
          array_push($ends,$end_single);
          array_push($pres,$pre);
          array_push($posts,$post);
          array_push($mhs,$mh);
          array_push($deltas,$delta);
          array_push($zeds,$charge);
          array_push($mods, $mod_list);  
        }
      }
    }
    $buffer = fgets($INPUT, 4096);
  }
  fclose($INPUT);


  if(($corB * $rtimeLT0 + $corA) < 0.0) $corA = 1 - round($corB * $rtimeLT0);
  for($a = 0; $a < count($rtimes); $a++){
    $rtimes[$a] = $corB * $rtimes[$a] + $corA;
  }
  $rank = array();
  $length = count($intensities);
  $tmp_arr = $starts;
  asort($tmp_arr);
  foreach($tmp_arr as $key => $val){
    array_push($rank, $key);
  }
  
  $pepTmpStr = '';
  foreach($rank as $a){
    $rtime = sprintf ("%.2f",$rtimes[$a]);
    $expect = log($expects[$a])/log(10.0);
    $expect = sprintf("%.1f",$expect);
    if($expect >= $pepex){
        continue;
    }
    $pepTmpStr = $ids[$a].$spliter;
    $pepTmpStr .= $expect.$spliter;
    $pepTmpStr .= $intensities[$a].$spliter;
    $pepTmpStr .= $mhs[$a].$spliter;
    $pepTmpStr .= $deltas[$a].$spliter;
    $pepTmpStr .= $zeds[$a].$spliter;
    #$pepTmpStr .= $rtime.$spliter;
    $pepTmpStr .= $pres[$a]. '-'. $starts[$a].$spliter;
    $pepTmpStr .= $sequences[$a].$spliter;
    $pepTmpStr .= $ends[$a]. '-'. $posts[$a].$spliter;
    $pepTmpStr .= $mods[$a].$spliter;
    $pepTmpStr .= $ionFiles[$a];
    $pepTmpStr .= "\n";
    if(isset($uids[$a])){
      if(array_key_exists($uids[$a],$peptides)){
        $peptides[$uids[$a]] .=  $pepTmpStr;
      }else{
        $peptides[$uids[$a]] =  $pepTmpStr;
      }  
    }
    if(isset($uids[$a])){
      if(array_key_exists($uids[$a],$pep_locations)){
        $pep_locations[$uids[$a]] .=  $starts[$a]."-".$ends[$a].";";
      }else{
        $pep_locations[$uids[$a]] =  $starts[$a]."-".$ends[$a].";";
      }  
    }
    if(isset($uids[$a])){
      if(array_key_exists($uids[$a],$coverage)){
        $coverage[$uids[$a]] .= $starts[$a]." ".$ends[$a]. " ";
      }else{
        $coverage[$uids[$a]] = $starts[$a]." ".$ends[$a]. " ";
      }  
    }  
  }
}

function draw_table($xlpath){
  global $spliter,$length,$peptides,$path,$full_file_name;
  global $uid_arr,$proteins,$mass,$pi,$sequences,$expect_arr,$descriptions;
  global $I,$pcount,$parameters;
  
  $fp = fopen($full_file_name, 'w');
  $line = "Protein: Identifier".$spliter."log(I)(sum of spectrum intensity)".$spliter."num uniqe peptide".$spliter."rI(num peptide)".$spliter."Coverage%".$spliter."log(e)(expect)".$spliter."pI".$spliter."Mr(kDa)".$spliter."Description\n";
  fwrite($fp, $line);
  $line = "Peptide: spectrum".$spliter."log(e)(expect)".$spliter."log(I)(sum of intensity)".$spliter."mh(pepetide mass)".$spliter."delta".$spliter."z".$spliter."start".$spliter."sequence".$spliter."end".$spliter."modifications".$spliter."ionFile\n\n";
  fwrite($fp, $line);
    
  $rank = array();
  $length_single = count($proteins);
  //@rank = sort { $expect_arr[$a] <=> $expect_arr[$b] }  0 .. $#expect;
  $tmp_arr = $expect_arr;
  asort($tmp_arr);
  foreach($tmp_arr as $key => $val){
    array_push($rank, $key);
  }
  $a = 0;
  $uid_local = '';
  $id = '';
  $pi_local = '';
  $mass_local = '';
  $line = '';
  $ADs = '';
  $ads = array();
  $ad = '';
  $adn = '';
  $u133 = '';
  $u95a = '';
  $psequence = '';
  $c = '';
  $u = '';
  $t = '';
  $pcont = 1;
  
  while($a < $length_single)  {
    $uid_local = $uid_arr[$rank[$a]];
    $id = $proteins[$rank[$a]];
    $mass_local = $mass[$rank[$a]];
    $pi_local = $pi[$rank[$a]];
    $psequence = $sequences[$rank[$a]];
    $expect = $expect_arr[$rank[$a]];     
    $tmpDescription = $descriptions[$rank[$a]];
    $id = preg_replace('/\s+$/', '', $id);
    
    $id = get_protein_typeid($id);
    if($I){
      $intensity = $I[$rank[$a]];
    }
    $ri = $pcount[$rank[$a]];
    list($c, $u, $t) = get_coverage($uid_local,$length[$rank[$a]]);
    $line = sprintf("Hit_$pcont".$spliter."$id".$spliter."$intensity".$spliter."$c".$spliter."$t".$spliter."$u".$spliter."$expect".$spliter."%.4f".$spliter."%.4f".$spliter."$tmpDescription",$pi_local,$mass_local/1000.0)."\n";
    fwrite($fp, $line);
    if(isset($peptides[$uid_local])){
      fwrite($fp, $peptides[$uid_local]);
    }  
    fwrite($fp, "<hr>\n");
    $pcont++;
    $a++;
  }
  fwrite($fp, "$parameters\n");
}

function get_coverage($_u,$_l){
  global $coverage;     
  $value = 1.0;
  $se = array();
  if(array_key_exists($_u, $coverage)){
    $se = explode(" ", trim($coverage[$_u]));
  }  
  $total = count($se);
  $p = array();
  $peps = 0;
  $a = 0;
  $seq = array();
  while($a < $_l+1){
    array_push($seq,0);
    $a++;
  }
  $a = 0;
  while($a < $total){
    $b = $se[$a];
    $p_key = $se[$a].' '.$se[$a+1];
    
    if(!array_key_exists($p_key, $p)){
      $p[$p_key] = 1;
      $peps++;
      while($b < $se[$a+1])   {
        $seq[$b]++;
        $b++;
      }
    }
      $a += 2;
  }   
  $a = 0;
  $value = 0;
  while($a < $_l) {
      if($seq[$a] > 0)    {
          $value++;
      }
      $a++;
  }
  $value *= 100.0/$_l;
  
  $ret_arr = array(); 
  array_push($ret_arr, round($peps));
  array_push($ret_arr, round($value,2));
  array_push($ret_arr, round($total/2));
  
  return $ret_arr;
}

function get_protein_typeid($id){
  if(preg_match("/^[a-zA-Z]+[\|:](.+?)[\s\|]/", $id, $matches) || preg_match("/^(\w+)\s*/", $id, $matches)){
    return $matches[1];
  }else{
    return '';
  }
}    
?> 
