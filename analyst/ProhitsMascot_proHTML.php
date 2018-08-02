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
$file    = '';
$hit     = '';
$exportingParameterStr = '';
$tmp_parsed_file = '';
$indexAccession = '';
$massList_gif = '';
$errorList_gif = '';
$field_spliter = ';;';
$massDP = 2;
$delta = "Delta";
$sortOrder = 'startup';
$showAll = 'false';
$reportString = '';
$reportString_arr = array();
$taxonomy_arr = array();
$pepQueryNums = array();
$pepMZ = array();
$pepExpects = array();
$pepMass = array();
$pepDelta = array();
$missed_clg = array();
$pepScores = array();
$pepStart = array();
$pepEnd = array();
$pepRank = array();
$pepSequences = array();
$pepModifications = array();
$pepExpect = array();

require("../config/conf.inc.php");
require("../common/common_fun.inc.php");
require("../msManager/is_dir_file.inc.php");
//get parameters
$PARAM = array_merge($_GET, $_POST);
$file = $PARAM['file'];
$hit = $PARAM['hit'];
if(isset($PARAM['expPara']))$exportingParameterStr = $PARAM['expPara'];
$indexAccession = preg_replace('/gi\|/','',$hit);
$PROHITS_ROOT = str_replace('/analyst','',dirname($_SERVER['SCRIPT_FILENAME']));
if($PARAM['tmpFile']=='extFile'){
  $tmp_parsed_dir = get_uploaded_search_results_dir('Mascot');
  $tmp_parsed_file = $tmp_parsed_dir.'/'.$indexAccession."_for_mascot_prohtml.tmp";
  $tmp_parsed_file_log = $tmp_parsed_dir.'/'.$indexAccession."_for_mascot_prohtml.log";
  parse_Mascot_perl($PROHITS_ROOT,$file, $exportingParameterStr, $indexAccession);
}else{
  $tmp_parsed_file = $PARAM['tmpFile'];
}
if (sizeof($PARAM)>3){
  if (isset($PARAM['sort']))$sortOrder = $PARAM['sort'];
}
$pro_arr = get_protein_from_url($indexAccession);

if(isset($pro_arr['sequence']) and $pro_arr['sequence']){
  $proSequence = strtolower($pro_arr['sequence']);
}else{
  echo "didn't get protein $indexAccession sequence";exit;
}
 
//read temp file
read_parsed_tmpfile($tmp_parsed_file);
//get exactly taxonomy
$taxonomy_arr = explode('(',$taxonomy);
$taxonomy =  trim($taxonomy_arr[0]);
//get protein coverage
$proCoverage = round($proCoverage/strlen($proSequence));
//get peptide matched seqence
$proPepMatchSequence = $proSequence;
for ($i = 0; $i < strlen($proPepMatchSequence); $i += 1){
  for ($j = 1; $j <= $proMatched; $j += 1){
    for ($k = $pepStart[$j]-1; $k < $pepEnd[$j];$k += 1){
      $proPepMatchSequence[$k] = strtoupper($proPepMatchSequence[$k]);
    }
  }
}
//sort peptide
if ($sortOrder == "massup"){
  asort($pepMass);
  $sortBy = $pepMass;
} elseif ($sortOrder == "massdown"){
  arsort($pepMass);
  $sortBy = $pepMass;
} else {
  asort($pepStart);
  $sortBy = $pepStart;
}

//get data for graph
for ($j = 1; $j < $proMatched; $j += 1){
  $massList_gif .= $pepExpects[$j].$field_spliter;
  $errorList_gif .= $pepDelta[$j].$field_spliter;
}
$massList_gif = $massList_gif.$pepExpects[$proMatched];
$errorList_gif = $errorList_gif.$pepDelta[$proMatched];



function parse_Mascot_perl($PROHITS_ROOT, $frm_mascot_file, $exportingParameterStr, $hit){
  global $tmp_parsed_file;
  global $tmp_parsed_file_log;
  if (!defined('PERL_58')) {
    define("PERL_58", "perl");
  }
  if(!check_mascot_parser()){
    echo "Mascot parser doesn't work.";
    exit;
  }else{
    $com = "cd $PROHITS_ROOT/MascotParser/scripts; ".PERL_58." ProhitsMascotParserExt.pl $frm_mascot_file $exportingParameterStr $hit $tmp_parsed_file> $tmp_parsed_file_log 2>&1";
    system($com);
    $tmp_arr = file($tmp_parsed_file_log);
    if($tmp_arr != Array ()){
      echo "Uploading not success.";
      exit;
    }
  }
}

function read_parsed_tmpfile($tmp_parsed_file){
  global $msDataFile;
  global $taxonomy;
  global $fixedModifications;
  global $variabledModifications;
  global $proMasses;
  global $proScores;
  global $proMatched;
  global $proDesc;
  global $proCoverage;
  global $proExpect;
  global $pepQueryNums;
  global $pepMZ;
  global $pepExpects;
  global $pepMass;
  global $pepDelta;
  global $missed_clg;
  global $pepScores;
  global $pepStart;
  global $pepEnd;
  global $pepRank;
  global $pepSequences;
  global $pepModifications;
  global $pepExpect;
  global $hit;
  global $field_spliter;
  global $units;
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
        if(preg_match('/^MS\s?data\s?file\s*:(.*)$/i', $buffer, $matches)) $msDataFile = trim($matches[1]);
        if(preg_match('/^Taxonomy\s*:(.*)$/i', $buffer, $matches)) $taxonomy = trim($matches[1]);
        if(preg_match('/^Fixed\s?modifications\s*:(.*)$/i', $buffer, $matches)) $fixedModifications = trim($matches[1]);
        if(preg_match('/^Variable\s?modifications\s*:(.*)$/i', $buffer, $matches)) $variabledModifications = trim($matches[1]);
        if(preg_match('/^Fragment Mass Tolerance\s*:.+ ([a-z]+)$/i', $buffer, $matches)) $units = trim($matches[1]);
        
        //get protein info
        //HitNumber;;ProteinID;;ProteinMass;;ProteinScore;;PeptidesMatched;;ProteinDesc;;Coverage;;Expect;;Threshold
         
        if(preg_match("/^Hit_[0-9]+/", $buffer)){
          $tmp_array = explode($field_spliter, $buffer);
          if($tmp_array[1] == $hit){
            $proMasses = $tmp_array[2];
            $proScores = $tmp_array[3];
            $proMatched = $tmp_array[4];
            $proDesc = $tmp_array[5];
            $proCoverage = $tmp_array[6];
            $proExpect = $tmp_array[7];
            $buffer = trim(fgets($fd, 40960));
            for ($i = 1; $i <= $proMatched; $i += 1){
              //QueryNumber;;Observed(MZ);;Mr(expt);;Mr(calc);;Delta;;Miss;;Score;;Start;;End;;Rank;;Peptide;;Modification;;Status;;IonFile;;PepExpect
              $tmp_array = explode($field_spliter, $buffer);
              $pepQueryNums[$i]    = $tmp_array[0];
              $pepMZ[$i]       = $tmp_array[1];
              $pepExpects[$i]  = $tmp_array[2];
              $pepMass[$i]     = $tmp_array[3];
              $pepDelta[$i]    = $tmp_array[4];
              $missed_clg[$i]  = $tmp_array[5];
              $pepScores[$i]   = $tmp_array[6];
              $pepStart[$i]    = $tmp_array[7];
              $pepEnd[$i]      = $tmp_array[8];
              $pepRank[$i]     = $tmp_array[9];
              $pepSequences[$i]= $tmp_array[10];
              $pepModifications[$i]= $tmp_array[11];
              $tmpPep_array = explode(",",$pepModifications[$i]);
              if(preg_match('/^1\s/i', $tmpPep_array[0], $matches)){
  	             $pepModifications[$i] = preg_replace('/1/','',$tmpPep_array[0]);
  	          }else{
                $pepModifications[$i] = $tmpPep_array[0];
              }
              $pepExpect[$i]= $tmp_array[14];
              $buffer = trim(fgets($fd, 40960));
            }
          }
        }
      }
    }
  }
}


?>
<HTML>
<HEAD>
<TITLE>Mascot Search Results: Protein View</TITLE>
</HEAD>
<BODY BGCOLOR="#ffffff" ALINK="#0000ff" VLINK="#0000ff">
<H1><IMG SRC="./images/88x31_logo_white_Mascot.gif" WIDTH="88" HEIGHT="31"
ALIGN="TOP" BORDER="0" NATURALSIZEFLAG="3"> Mascot Search Results</H1>
<H3>Protein View</H3>
<FORM METHOD="POST">
<INPUT TYPE="hidden" NAME="file" VALUE="<?php echo $file;?>">
<INPUT TYPE="hidden" NAME="hit" VALUE="<?php echo $hit;?>">
<INPUT TYPE="hidden" NAME="expPara" VALUE="<?php echo $exportingParameterStr;?>">
<FONT FACE='Courier New,Courier,monospace'>
<PRE>
Match to: <B><?php echo $hit;?></B> Score: <B><?php echo $proScores;?></B>
<B><?php echo $proDesc;?></B>
Found in search of <?php echo $msDataFile;?>


Nominal mass (M<SUB>r</SUB>): <B><?php echo $proMasses;?></B>;
NCBI BLAST search of <A HREF="http://www.ncbi.nlm.nih.gov/blast/Blast.cgi?ALIGNMENTS=50&ALIGNMENT_VIEW=Pairwise&AUTO_FORMAT=Semiauto&CDD_SEARCH=on&CLIENT=web&COMPOSITION_BASED_STATISTICS=on&DATABASE=nr&DESCRIPTIONS=100&ENTREZ_QUERY=(none)&EXPECT=10&FILTER=L&FORMAT_BLOCK_ON_RESPAGE=None&FORMAT_OBJECT=Alignment&FORMAT_TYPE=HTML&GAPCOSTS=11+1&I_THRESH=0.001&LAYOUT=TwoWindows&MATRIX_NAME=BLOSUM62&NCBI_GI=on&PAGE=Proteins&PROGRAM=blastp&QUERY=<?php echo $proSequence;?>" TARGET="_blank"><?php echo $hit;?></A> against nr
Unformatted <A HREF="getseq.php?hit=<?php echo $hit;?>" TARGET="_blank">sequence string</A> for pasting into other applications

Taxonomy: <A HREF="http://www.ncbi.nlm.nih.gov/taxonomy/?term=<?php echo $taxonomy;?>" target=_blank><?php echo $taxonomy;?></A>

Fixed modifications: <?php echo $fixedModifications;?>

Variable modifications: <?php echo $variabledModifications;?>

Sequence Coverage: <B><?php echo $proCoverage;?>%</B>

Matched peptides shown in <B><FONT COLOR=#FF0000>Bold Red</FONT></B>

     <B>1</B> <?php 
$bold = 0;
for ($i = 0; $i < strlen($proPepMatchSequence); $i += 1){
  if((preg_match("/[A-Z]/", $proPepMatchSequence[$i])) and !$bold){
    echo "<B><FONT COLOR=#FF0000>";
    $bold = 1;
  } elseif ((preg_match("/[a-z]/",$proPepMatchSequence[$i])) and $bold){
    echo "</FONT></B>";
    $bold = 0;
  }
  echo strtoupper($proPepMatchSequence[$i]);
  if (!(($i+1)%10)){
    echo " ";
  }
  if (!(($i+1)%50)){
    if ($bold){
      echo sprintf("</FONT></B>\n<B>%6d<FONT COLOR=#FF0000> ", $i+2);
    } else {
      echo sprintf("\n<B>%6d</B> ", $i+2);
    }
  }
}
if ($bold){
  echo "</FONT></B>";
}
?>
</PRE></FONT>
<?php 
$button1 = "";
$button2 = "";
$button3 = "";
if ($sortOrder == "massup"){
  $button1 = "";
  $button2 = "checked";
  $button3 = "";
} elseif ($sortOrder == "massdown"){
  $button1 = "";
  $button2 = "";
  $button3 = "checked";
} else {
  $button1 = "checked";
  $button2 = "";
  $button3 = "";
}
?>
<P><INPUT TYPE="submit" VALUE="Sort Peptides By">&nbsp;
<INPUT TYPE="radio" NAME="sort" VALUE="startup" <?php echo $button1;?>>Residue Number&nbsp;
<INPUT TYPE="radio" NAME="sort" VALUE="massup" <?php echo $button2;?>>Increasing Mass&nbsp;
<INPUT TYPE="radio" NAME="sort" VALUE="massdown" <?php echo $button3;?>>Decreasing Mass&nbsp;
<FONT FACE='Courier New,Courier,monospace'><PRE>
<B> Start - End   Observed  Mr(expt) Mr(calc)   <?php echo $delta;?>   Miss Sequence</B>
<?php 
 
foreach ($sortBy as $key => $val) {
  echo sprintf("<B><FONT COLOR=#FF0000> %5d - %-5d %8.2f %8.2f %8.2f %8.2f    %2d  %s </FONT></B>",
                $pepStart[$key],
                $pepEnd[$key],
                $pepMZ[$key],
                $pepExpects[$key],
                $pepMass[$key],
                $pepDelta[$key],
                $missed_clg[$key],
                $pepSequences[$key]);
  echo $pepModifications[$key];
  echo " (<A HREF=\"ProhitsMascot_pepHTML.php?file=$file&query=$pepQueryNums[$key]&hit=$pepRank[$key]&index=$hit&expPara=$exportingParameterStr\" TARGET=\"_blank\">Ions score $pepScores[$key]</A>)\n";
}
?>
<P><IMG SRC="mass_error.php?units=<?php echo $units;?>&massList=<?php echo $massList_gif;?>&errorList=<?php echo $errorList_gif;?>" WIDTH=450 HEIGHT=150 ALT="Error Distribution"><IMG SRC="mass_error.php?units=ppm&massList=<?php echo $massList_gif;?>&errorList=<?php echo $errorList_gif;?>" WIDTH=450 HEIGHT=150 ALT="Error Distribution (ppm)">
</FORM><HR>
<?php echo $reportString;?>

</PRE></FONT>
</TABLE>
</BODY>
</HTML>

