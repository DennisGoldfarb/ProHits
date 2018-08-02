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

ini_set("memory_limit","-1");
set_time_limit(3600*2);
$tr_bgcolor = '#e3e3e3';
$tr_title_bgcolor = 'white';
$divSize = "95%";
$frm_mascot_file = '';
$tmp_parsed_file_log = '';
$field_spliter = ';;';
$theAction = '';
$exportingParameterStr = '';
$requireboldred = 1;
$removeType = '';
$sigthreshold = '0.05';
$maxHits = 'AUTO';
$proteinScore = '0';
$ignoreionsscorebelow = '27';
$stdScoring = 1;
$showsubsets ='1';
$indivIonsScores = '';
$pNum = 0; //temp checkbox counter
$hitStart = false;
$redundant_gi_start = false;
$redundant_sub_gi_start = false;
$peptide_start = false;
$peptide_num = 0;
$pRedundant_num = 0;
$pRedundant_sub_num = 0;
$searchedDB = '';
$instrument = '';
$tmpPID = '';
$pGIs = array();
$pMasses = array();
$pScores = array();
$pMatched = array();
$pNames = array();
$pExp = array();
$pepQueryNums = array();
$pepMZ = array();
$pepExpects = array();
$pepMass = array();
$pepDelta = array();
$missed_clg = array();
$pepScores = array();
$pepRank = array();
$pepSequences = array();
$pepModifications = array();
$pepExpect = array();
$redundant_num = array();
$pRedundantGIs = array();
$pRedundantMasses = array();
$pRedundantScores = array();
$pRedundantMatched = array();
$pRedundantNames = array();
$redundant_sub_num = array();
$pRedundantSubGIs = array();
$pRedundantSubMasses = array();
$pRedundantSubScores = array();
$pRedundantSubMatched = array();
$pRedundantSubNames = array();

require("../config/conf.inc.php");
require("../common/common_fun.inc.php");
require("../msManager/is_dir_file.inc.php");

$PARAM = array_merge($_GET, $_POST);
$userID = $PARAM['userID'];
$File = $PARAM['File'];
if(sizeof($PARAM)>2){
  $theAction = $PARAM['theAction'];
  $sigthreshold = $PARAM['sigthreshold'];
  $maxHits = $PARAM['maxHits'];
  $stdScoring = $PARAM['stdScoring'];
  $ignoreionsscorebelow = $PARAM['ignoreionsscorebelow'];
  $showsubsets = $PARAM['showsubsets'];
  $requireboldred = $PARAM['requireboldred'];
}
$tmp_parsed_dir = get_uploaded_search_results_dir('Mascot');
$tmp_parsed_file = $tmp_parsed_dir.'/'.$userID."_for_mascot_html.tmp";
$tmp_parsed_file_log = $tmp_parsed_dir.'/'.$userID."_for_mascot_html.log";
$PROHITS_ROOT = str_replace('/analyst','',dirname($_SERVER['SCRIPT_FILENAME']));

if($File){
  $frm_mascot_file = $File;
  if(!_is_file($File)){
    echo "the file ($File) doesn't exist";
    exit;
  }
}else{
  echo "no Mascot search results file passed";
  exit;
}
if($theAction == 'download' and $File){
   
  if(_is_file($File)){
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"".basename($File)."\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: "._filesize($File));
    readfile("$File");
    exit();
  }
  exit;
}
if($theAction == 'formatAs' ){

}

$exportingParameterStr = create_parameter($sigthreshold,$maxHits,$proteinScore,$stdScoring,$ignoreionsscorebelow,$showsubsets,$requireboldred);
 
parse_Mascot_perl($PROHITS_ROOT,$frm_mascot_file, $exportingParameterStr);

$fd = @fopen("$tmp_parsed_file","r");
if(!$fd){
  $msg = "The $tmp_parsed_file file can not open.";
  fatal_Error($msg);
  exit;
}else{
  //get all hits into arrays
  $pNum = 0; //temp checkbox counter
  $hitStart = false;
  $redundant_gi_start = false;
  $redundant_sub_gi_start = false;
  $peptide_start = false;
  $peptide_num = 0;
  $pRedundant_num = 0;
  $pRdundant_sub_num = 0;
  $redundant_num[$pNum] = 0;
  $redundant_sub_num[$pNum] = 0;
  $searchedDB = '';
  $instrument = '';
  $tmpPID = '';
  $database = '';
  while (!feof ($fd)) {
    $buffer = trim(fgets($fd, 40960));
    if(!$buffer)continue;
    if(preg_match('/^User\s*:(.*)$/i', $buffer, $matches)) $user = trim($matches[1]);
    if(preg_match('/^Email\s*:(.*)$/i', $buffer, $matches)) $email = trim($matches[1]);
    if(preg_match('/^Search\s?title\s*:(.*)$/i', $buffer, $matches)) $searchTitle = trim($matches[1]);
    if(preg_match('/^MS\s?data\s?file\s*:(.*)$/i', $buffer, $matches)) $msDataFile = trim($matches[1]);
    if(preg_match('/^Timestamp\s*:(.*)$/i', $buffer, $matches)) $timestamp = trim($matches[1]);
	if(preg_match('/^Search\s?type\s*:(.*)$/i', $buffer, $matches)) $searchType = trim($matches[1]);
    if(preg_match('/^Instrument\s?type\s*:(.*)$/i', $buffer, $matches)) $instrument = trim($matches[1]);
    if(preg_match('/^Database\s*:(.*)$/i', $buffer, $matches)){
      $searchedDB .= trim($matches[1]);
      $database .= trim($matches[1])." ";
    }else if(preg_match('/^Taxonomy\s*:(.*)$/i', $buffer, $matches)){
      $searchedDB .= "-".trim($matches[1]);
      $taxonomy = trim($matches[1]);
    }
    if(preg_match('/^Number\s?of\s?queries\s*:(.*)$/i', $buffer, $matches)) $numOfQueries = trim($matches[1]);
    if(preg_match('/^Enzyme\s*:(.*)$/i', $buffer, $matches)) $enzyme = trim($matches[1]);
    if(preg_match('/^Max\s?Missed\s?Cleavages\s*:(.*)$/i', $buffer, $matches)) $maxMissedCleavages = trim($matches[1]);
    if(preg_match('/^Fixed\s?modifications\s*:(.*)$/i', $buffer, $matches)) $fixedModifications = trim($matches[1]);
    if(preg_match('/^Variable\s?modifications\s*:(.*)$/i', $buffer, $matches)) $variabledModifications = trim($matches[1]);
    if(preg_match('/^Peptide\s?Mass\s?Tolerance\s*:(.*)$/i', $buffer, $matches)) $peptideMassTolerance = trim($matches[1]);
    if(preg_match('/^Fragment\s?Mass\s?Tolerance\s*:(.*)$/i', $buffer, $matches)) $fragmentMassTolerance = trim($matches[1]);
    if(preg_match('/^Mass\s?values\s*:(.*)$/i', $buffer, $matches)) $massValues = trim($matches[1]);
    if(preg_match('/^Individual\s?ions\s*/i', $buffer, $matches)) $indivIonsScores = $buffer;

    //find hit start position
    //<b>HitNumber;; GInumber;; ProteinMass;; ProteinScore;; PeptidesMached;; ProteinDesc
    if(preg_match("/^(Hit_[0-9]*)/", $buffer) > 0 and !$redundant_gi_start and !$redundant_sub_gi_start){
      $pNum++;  //tmp checkbox counter start from 1
      $peptide_start = true;
      $redundant_gi_start = false;
      $redundant_sub_gi_start = false;
      $pRedundantGIs[$pNum] = '';
      $pRedundantSubGIs[$pNum] = '';
      $peptide_num = 0;
      $pRedundant_num = 0;
      $pRedundant_sub_num = 0;
      //peptids num of the hit
      $tmp_array = explode($field_spliter, $buffer);
      $pGIs[$pNum] = $tmp_array[1];
      $pMasses[$pNum] = $tmp_array[2];
      $pScores[$pNum] = $tmp_array[3];
      $pMatched[$pNum] = $tmp_array[4];
      $pNames[$pNum]  = $tmp_array[5];
      $pExp[$pNum] = $tmp_array[7];
      $buffer = trim(fgets($fd, 40960));//get next line
    }else if( $buffer == '<B>Proteins matching the same set of peptides:</B>' ){
      $redundant_gi_start = true;
      $peptide_start = false;
      $buffer = trim(fgets($fd, 40960));//get next line
    }else if( $buffer == '<B>Proteins matching a subset of these peptides:</B>' ){
      $redundant_sub_gi_start = true;
      $redundant_gi_start = false;
      $peptide_start = false;
      $buffer = trim(fgets($fd, 40960));//get next line
    }else if($buffer == '<HR>') {
      $redundant_gi_start = false;
      $redundant_sub_gi_start = false;
      $peptide_start = false;
      $redundant_num[$pNum] = $pRedundant_num;
      $redundant_sub_num[$pNum] = $pRedundant_sub_num;
    }

    if($peptide_start and $buffer){
    //QueryNumber;;Observed(MZ);;Mr(expt);;Mr(calc);;Delta;;Miss;;Score;;Start;;End;;Rank;;Peptide;;Modification;;Status;;IonFile;;PepExpect
      $tmpMod = '';
      $peptide_num++;
      $tmp_array = explode($field_spliter, $buffer);
      $pepQueryNums[$pNum][$peptide_num]= $tmp_array[0];
      $pepMZ[$pNum][$peptide_num]       = $tmp_array[1];
      $pepExpects[$pNum][$peptide_num]  = $tmp_array[2];
      $pepMass[$pNum][$peptide_num]     = $tmp_array[3];
      $pepDelta[$pNum][$peptide_num]    = $tmp_array[4];
      $missed_clg[$pNum][$peptide_num]  = $tmp_array[5];
      $pepScores[$pNum][$peptide_num]   = $tmp_array[6];
      $pepRank[$pNum][$peptide_num]     = $tmp_array[9];
      $pepSequences[$pNum][$peptide_num]= $tmp_array[10];
      $pepModifications[$pNum][$peptide_num]= $tmp_array[11];
      if($pepModifications[$pNum][$peptide_num]){
        $tmpPep_array = explode(",",$pepModifications[$pNum][$peptide_num]);
	if(preg_match('/^1\s/i', $tmpPep_array[0], $matches)){
	  $tmpMod= preg_replace('/1/','',$tmpPep_array[0]);
	}else{
          $tmpMod= $tmpPep_array[0];
        }
	$peptideSeq[$pNum][$peptide_num]= $pepSequences[$pNum][$peptide_num].' +'.$tmpMod;
      }else{
        $peptideSeq[$pNum][$peptide_num]= $pepSequences[$pNum][$peptide_num];
      }
      $pepStatus[$pNum][$peptide_num]= $tmp_array[12];
      $pepExpect[$pNum][$peptide_num]= $tmp_array[14];
    }
    //redendant proteins
    if($redundant_gi_start and $buffer){
      $pRedundant_num++;
      $tmp_array   = explode($field_spliter, $buffer);
      $pRedundantGIs[$pNum][$pRedundant_num]     = $tmp_array[1];
      $pRedundantMasses[$pNum][$pRedundant_num]  = $tmp_array[2];
      $pRedundantScores[$pNum][$pRedundant_num]  = $tmp_array[3];
      $pRedundantMatched[$pNum][$pRedundant_num] = $tmp_array[4];
      $pRedundantNames[$pNum][$pRedundant_num]   = $tmp_array[5];
    }
    if($redundant_sub_gi_start and $buffer){
      $pRedundant_sub_num++;
      $tmp_array   = explode($field_spliter, $buffer);
      $pRedundantSubGIs[$pNum][$pRedundant_sub_num] = $tmp_array[1];
      $pRedundantSubMasses[$pNum][$pRedundant_sub_num]= $tmp_array[2];
      $pRedundantSubScores[$pNum][$pRedundant_sub_num]= $tmp_array[3];
      $pRedundantSubMatched[$pNum][$pRedundant_sub_num]= $tmp_array[4];
      $pRedundantSubNames[$pNum][$pRedundant_sub_num]= $tmp_array[5];
    }

  }//======================end of file reading================================
  fclose($fd);
}

function parse_Mascot_perl($PROHITS_ROOT, $frm_mascot_file, $exportingParameterStr){
  global $tmp_parsed_file;
  global $tmp_parsed_file_log;
  if (!defined('PERL_58')) {
    define("PERL_58", "perl");
  }
  if(!check_mascot_parser()){
    echo "Mascot parser doesn't work. Please setup mascot parser.";
    exit;
  }else{
    $com = "cd $PROHITS_ROOT/MascotParser/scripts; ".PERL_58." ProhitsMascotParser.pl $frm_mascot_file $exportingParameterStr $tmp_parsed_file> $tmp_parsed_file_log 2>&1";
    
    system($com);
    $error_log = file_get_contents($tmp_parsed_file_log);
    if($error_log and !strstr($error_log, "Warning")){
      echo "There is error while process following command<br>$com<br><br>";
      echo $error_log;
      exit;
    }
  }
}
function create_parameter($sigthreshold,$maxHits,$proteinScore,$stdScoring,$ignoreionsscorebelow,$showsubsets,$requireboldred){
  if($maxHits == 'AUTO'){
    //$maxHits = '99999999';
    $maxHits = '0';
  }
  $ParameterStr = "$sigthreshold:$maxHits:$proteinScore:$stdScoring:$ignoreionsscorebelow:$showsubsets:$requireboldred";
  return $ParameterStr;
}
function is_perl58(){
  $rt = false;
  $perlversion = '../TMP/perlversion.txt';
  if(_is_file(PERL_58)){
    system(PERL_58 . " -v > $perlversion 2>&1" );
    $tmp_arr = file($perlversion);
    $con = 0;
    foreach($tmp_arr as $line){
      if(preg_match('/^This is perl, (v5.8)/i', $line, $matches)){
        $rt = true;
      }
    }
  }
  if(_is_file($perlversion)) unlink($perlversion);
  return $rt;
}
?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<script language="javascript">
function download_uploaded_file(theFile){
  var theForm = document.getElementById('mascotForm');
  theForm.theAction.value = 'download';
  theForm.submit();
}
function submitform(theForm){
  theForm.theAction.value = 'formatAs';
  theForm.submit();
}
</script>
</head>
<basefont face="arial">
<BODY BGCOLOR="#ffffff" ALINK="#0000ff" VLINK="#0000ff">
<form id=mascotForm name=mascotForm method=post action=<?php echo $_SERVER['PHP_SELF'];?> enctype="multipart/form-data">
<input type=hidden name=theAction value=''>
<input type=hidden name='userID' value='<?php echo $userID;?>'>
<input type=hidden name='File' value='<?php echo $File;?>'>
<INPUT TYPE="hidden" NAME="requireboldred" VALUE='<?php echo $requireboldred;?>'>
<INPUT TYPE="hidden" NAME="sigthreshold" VALUE=<?php echo $sigthreshold;?>'>
[<a href="javascript: download_uploaded_file('<?php echo $File;?>')">Download DAT</a>]
<H1><IMG SRC="./images/88x31_logo_white_Mascot.gif" WIDTH=88 HEIGHT=31 ALIGN="TOP" BORDER=0 NATURALSIZEFLAG=3 Name="fred"> Mascot Search Results</H1>
<FONT FACE='Courier New,Courier,monospace'>
<PRE>
<B>User            : <?php echo $user;?></B>
<B>Email           : <?php echo $email;?></B>
<B>Search title    : <?php echo $searchTitle;?></B>
<B>MS data file    : <?php echo $msDataFile;?></B>
<B>Database        : <?php echo $database;?></B>
<B>Taxonomy        : <?php echo $taxonomy;?></B>
<B>Timestamp       : <?php echo $timestamp;?></B>
<TABLE BGCOLOR=#FFFF99 BORDER=0 CELLPADDING=0 CELLSPACING=0>
<?php 
for ($counter = 1; $counter <= $pNum; $counter += 1) {
  if($counter == 1){
    echo "<TR><TD BGCOLOR=#FFFFFF NOWRAP><TT><B>Protein hits&nbsp;&nbsp;&nbsp;&nbsp;:</B></TT></TD>";
  }else{
    echo "<TR><TD BGCOLOR=#FFFFFF NOWRAP>&nbsp;</TD>";
  }
  echo "<TD BGCOLOR=#FFFFFF NOWRAP><TT><B>&nbsp;<A HREF='#Hit$counter'>$pGIs[$counter]</A></B></TT></TD>";
  echo "<TD BGCOLOR=#FFFFFF NOWRAP><TT>&nbsp;$pNames[$counter]</TT></TD></TR>";
}
?>
</TABLE>
</PRE>
</FONT>
<H3>Probability Based Mowse Score</H3>
Ions score is -10*Log(P), where P is the probability that the observed match is a random event.
<BR>
<?php 
echo "$indivIonsScores";
?>
<BR>
Protein scores are derived from ions scores as a non-probabilistic basis for ranking protein hits.
<BR><BR>
<H3>Peptide Summary Report</H3>
<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=3>
  <TR>
    <TD BGCOLOR=#EEEEFF NOWRAP>
      <input type=button value='Format As' onClick="submitform(this.form)">
    </TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
      <SELECT NAME="REPTYPE">
        <OPTION VALUE="peptide" SELECTED>Peptide Summary</OPTION>
      </SELECT>
    </TD>
    <TD BGCOLOR=#EEEEFF>&nbsp;</TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
    </TD>
  </TR>
  <TR>
    <TD BGCOLOR=#EEEEFF>&nbsp;</TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
Significance threshold p&lt; <INPUT NAME="sigthreshold" TYPE=text SIZE=8 VALUE='<?php echo ($sigthreshold);?>'>
    </TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
Max. number of hits <INPUT NAME="maxHits" TYPE=text SIZE=5 VALUE='<?php echo ($maxHits)?>'>
    </TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
Require bold red <INPUT TYPE="checkbox" NAME="rbrchkbox" onClick="if (form.rbrchkbox.checked) { form.requireboldred.value = 1; } else { form.requireboldred.value = 0; } return true;" <?php echo ($requireboldred)?"checked":""?>>
    </TD>
  </TR>
  <TR>
    <TD BGCOLOR=#EEEEFF>&nbsp;</TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
Standard scoring&nbsp;<INPUT TYPE="radio" VALUE=1 NAME="stdScoring" <?php echo ($stdScoring)?"checked":""?>>&nbsp;MudPIT scoring&nbsp;<INPUT TYPE="radio" VALUE=0 NAME="stdScoring" <?php echo (!$stdScoring)?"checked":""?>>
    </TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
Ions score cut-off <INPUT NAME="ignoreionsscorebelow" TYPE=text SIZE=5 VALUE='<?php echo $ignoreionsscorebelow;?>'>
    </TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
Show sub-sets <INPUT NAME="showsubsets" TYPE=text SIZE=2 VALUE='<?php echo $showsubsets;?>'>
    </TD>
  </TR>
</TABLE>
<BR>
<?php 
for ($hitCont = 1; $hitCont <= $pNum; $hitCont += 1) {
?>
<TABLE BORDER=0 CELLSPACING=0>
  <TR><TD><TT><B><A NAME='Hit<?php echo $hitCont?>'><?php echo $hitCont?></A>&nbsp;&nbsp;&nbsp;&nbsp;</B></TT></TD><TD><TT><A HREF="ProhitsMascot_proHTML.php?file=<?php echo $File?>&hit=<?php echo $pGIs[$hitCont]?>&tmpFile=<?php echo $tmp_parsed_file?>&expPara=<?php echo $exportingParameterStr?>" TARGET="_blank" ><?php echo $pGIs[$hitCont]?></A>&nbsp;&nbsp;&nbsp;&nbsp;<B>Mass:</B>&nbsp;<?php echo $pMasses[$hitCont]?>&nbsp;&nbsp;&nbsp;&nbsp;<B>Score:</B>&nbsp;<?php echo $pScores[$hitCont]?>&nbsp;&nbsp;&nbsp;<B>Queries matched:</B>&nbsp;<?php echo $pMatched[$hitCont]?>&nbsp;&nbsp;&nbsp;</TT></TD></TR>
  <TR><TD>&nbsp;</TD><TD NOWRAP><TT><?php echo $pNames[$hitCont]?></TT></TD></TR>
</TABLE>
<TABLE BORDER=0 CELLSPACING=0>
<TR><TD ALIGN=RIGHT><INPUT TYPE="checkbox" NAME="INCLUDE" VALUE="1" onClick="SaveClicks(this,this.form)"></TD><TD NOWRAP><TT>Check to include this hit in error tolerant search or archive report</TT></TD></TR>
<TR><TD><TT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TT></TD><TD>&nbsp;</TD></TR>
</TABLE>
<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>
<TR><TD><TT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TT></TD><TD ALIGN=RIGHT><TT><B>Query&nbsp;&nbsp;</B></TT></TD><TD ALIGN=RIGHT><TT><B>Observed&nbsp;&nbsp;</B></TT></TD><TD ALIGN=RIGHT NOWRAP><TT><B>Mr(expt)&nbsp;&nbsp;</B></TT></TD><TD ALIGN=RIGHT NOWRAP><TT><B>Mr(calc)&nbsp;&nbsp;</B></TT></TD><TD ALIGN=RIGHT><TT><B>&nbsp;Delta&nbsp;</B></TT></TD><TD ALIGN=RIGHT><TT><B>Miss&nbsp;</B></TT></TD><TD ALIGN=RIGHT><TT><B>Score&nbsp;</B></TT></TD><TD ALIGN=RIGHT><TT><B>Expect&nbsp;</B></TT></TD><TD ALIGN=RIGHT><TT><B>Rank&nbsp;</B></TT></TD><TD><TT><B>&nbsp;Peptide</B></TT></TD></TR>
<?php 
for ($pepCont = 1; $pepCont <= $pMatched[$hitCont]; $pepCont += 1) {
?>
<TR>
<?php 
if($pepStatus[$hitCont][$pepCont]=='RB'){
?>
  <TD ALIGN=RIGHT><INPUT TYPE="checkbox" NAME="QUE" VALUE="" CHECKED></TD>
<?php 
}else{
  echo "<TD></TD>";
}
?>
  <TD ALIGN=RIGHT><TT><A HREF="ProhitsMascot_pepHTML.php?file=<?php echo $File?>&query=<?php echo $pepQueryNums[$hitCont][$pepCont]?>&hit=<?php echo $pepRank[$hitCont][$pepCont]?>&index=<?php echo $pGIs[$hitCont]?>&expPara=<?php echo $exportingParameterStr?>" TARGET="_blank"><?php echo $pepQueryNums[$hitCont][$pepCont]?></A>&nbsp;&nbsp;</TT></TD>
  <TD ALIGN=RIGHT><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?><?php echo $pepMZ[$hitCont][$pepCont]?>&nbsp;&nbsp;<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
  <TD ALIGN=RIGHT><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?><?php echo $pepExpects[$hitCont][$pepCont]?>&nbsp;&nbsp;<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
  <TD ALIGN=RIGHT><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?><?php echo $pepMass[$hitCont][$pepCont]?>&nbsp;&nbsp;<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
  <TD ALIGN=RIGHT NOWRAP><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?><?php echo $pepDelta[$hitCont][$pepCont]?>&nbsp;<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
  <TD ALIGN=RIGHT><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?><?php echo $missed_clg[$hitCont][$pepCont]?>&nbsp;&nbsp;<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
  <TD ALIGN=RIGHT NOWRAP><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?><?php echo $pepScores[$hitCont][$pepCont]?>&nbsp;<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
  <TD ALIGN=RIGHT NOWRAP><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?><?php echo $pepExpect[$hitCont][$pepCont]?>&nbsp;<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
  <TD ALIGN=RIGHT><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?><?php echo $pepRank[$hitCont][$pepCont]?>&nbsp;&nbsp;<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
  <TD NOWRAP><TT><FONT COLOR=<?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='R' )?"#FF0000":"#000000"?>><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"<B>":""?>&nbsp;<?php echo $peptideSeq[$hitCont][$pepCont]?><?php echo ($pepStatus[$hitCont][$pepCont]=='RB'or $pepStatus[$hitCont][$pepCont]=='B' )?"</B>":""?></FONT></TT></TD>
</TR>
<?php 
}
?>
</TABLE>
<BR>
<?php 
if($redundant_num[$hitCont]>=1){
?>
<TABLE BORDER=0 CELLSPACING=0>
<TR><TD COLSPAN=2><TT>&nbsp;</TT></TD></TR>
<TR><TD><TT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TT></TD><TD><TT><B>Proteins matching the same set of peptides:</B></TT></TD></TR>
</TABLE>
<?php 
for ($redundantCont = 1; $redundantCont <= $redundant_num[$hitCont]; $redundantCont += 1) {
?>
<TABLE BORDER=0 CELLSPACING=0>
<TR><TD><TT><B>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</B></TT></TD><TD NOWRAP><TT><?php echo $pRedundantGIs[$hitCont][$redundantCont]?>&nbsp;&nbsp;&nbsp;&nbsp;<B>Mass:</B>&nbsp;<?php echo $pRedundantMasses[$hitCont][$redundantCont]?>&nbsp;&nbsp;&nbsp;&nbsp;<B>Score:</B>&nbsp;<?php echo $pRedundantScores[$hitCont][$redundantCont]?>&nbsp;&nbsp;&nbsp;<B>Queries matched:</B>&nbsp;<?php echo $pRedundantMatched[$hitCont][$redundantCont]?></TT></TD></TR>
<TR><TD>&nbsp;</TD><TD NOWRAP><TT><?php echo $pRedundantNames[$hitCont][$redundantCont]?></TT></TD></TR>
</TABLE>
<?php 
 }
}
if($redundant_sub_num[$hitCont]>=1){
?>
<TABLE BORDER=0 CELLSPACING=0>
<TR><TD COLSPAN=2><TT>&nbsp;</TT></TD></TR>
<TR><TD><TT>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TT></TD><TD><TT><B>Proteins matching a subset of these peptides:</B></TT></TD></TR>
</TABLE>
<?php 
for ($redundantSubCont = 1; $redundantSubCont <= $redundant_sub_num[$hitCont]; $redundantSubCont += 1) {
?>
<TABLE BORDER=0 CELLSPACING=0>
<TR><TD><TT><B>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</B></TT></TD><TD NOWRAP><TT><?php echo $pRedundantSubGIs[$hitCont][$redundantSubCont]?>&nbsp;&nbsp;&nbsp;&nbsp;<B>Mass:</B>&nbsp;<?php echo $pRedundantSubMasses[$hitCont][$redundantSubCont]?>&nbsp;&nbsp;&nbsp;&nbsp;<B>Score:</B>&nbsp;<?php echo $pRedundantSubScores[$hitCont][$redundantSubCont]?>&nbsp;&nbsp;&nbsp;<B>Queries matched:</B>&nbsp;<?php echo $pRedundantSubMatched[$hitCont][$redundantSubCont]?></TT></TD></TR>
<TR><TD>&nbsp;</TD><TD NOWRAP><TT><?php echo $pRedundantSubNames[$hitCont][$redundantSubCont]?></TT></TD></TR>
</TABLE>
<?php 
}
}
?>
<HR>
<?php 
}
?>
<H3>Search Parameters</H3>
<FONT FACE='Courier New,Courier,monospace'>
<PRE>
<B>Type of search         : <?php echo $searchType?></B>
<B>Enzyme                 : <?php echo $enzyme?></B>
<B>Fixed modifications    : <?php echo $fixedModifications?></B>
<B>Variable modifications : <?php echo $variabledModifications?></B>
<B>Mass values            : <?php echo $massValues?></B>
<B>Protein Mass           : Unrestricted</B>
<B>Peptide Mass Tolerance : &#177; <?php echo $peptideMassTolerance?></B>
<B>Fragment Mass Tolerance: &#177; <?php echo $fragmentMassTolerance?></B>
<B>Max Missed Cleavages   : <?php echo $maxMissedCleavages?></B>
<B>Instrument type        : <?php echo $instrument?></B>
<B>Number of queries      : <?php echo $numOfQueries?></B>
</PRE>
</FONT>
</form>
</body>
</html>
