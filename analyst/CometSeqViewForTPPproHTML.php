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
$pepMass = 0;
$totalMass = 0;
$totalModMass = 0;
$pepLen = 0;
$Protein_arr = array();
$Protein_pep_arr = array();
$observedPeptide = array();
$position = array();
$mass = array();
$peptide = array();
$pos_array = array();
$start_arr = array();
$end_arr = array();
$pepMass_arr = array();
$tmp_array = array();
include("../msManager/ms_permission.inc.php");
include("msManager/classes/xmlParser_class.php");
require("analyst/common_functions.inc.php");

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
$sequence = GetSequence($proteinDB,$prot);

parse_proteinProphet($xmlfile);

$totalMass = calcMass($sequence);
$totalMass = sprintf("%.0f",$totalMass);

for ($counter = 1; $counter <= count($Protein_arr); $counter += 1) {
  if($Protein_arr[$counter]['PROTEIN_NAME'] == $prot){
    $proteinDesc = $Protein_arr[$counter]['PROTEIN_DESCRIPTION'];
    $proteinCove = $Protein_arr[$counter]['PERCENT_COVERAGE'];
    if(isset($Protein_arr[$counter]['PROTEIN_LENGTH'])){
	  $proteinLeng = $Protein_arr[$counter]['PROTEIN_LENGTH'];
	}else{
	  $proteinLeng = strlen($sequence);
	}
    for ($pcounter = 0; $pcounter < count($Protein_pep_arr[$counter]); $pcounter += 1) {
      if(preg_match("/\[/", $Protein_pep_arr[$counter][$pcounter]['PEPTIDE_SEQUENCE'])){
        $tmp_array = preg_split("/\[/",$Protein_pep_arr[$counter][$pcounter]['PEPTIDE_SEQUENCE']);
        $tmp_array = preg_split("/\]/",$tmp_array[1]);
        
        $peptideSeq = preg_replace("/\[[0-9]+\]/","",$Protein_pep_arr[$counter][$pcounter]['PEPTIDE_SEQUENCE']);
      }else{
        $peptideSeq = $Protein_pep_arr[$counter][$pcounter]['PEPTIDE_SEQUENCE'];
      }
      $startPos = strpos($sequence,$peptideSeq)+1;
      $endPos = $startPos+strlen($peptideSeq)-1;
      $startend = $startPos.'-'.$endPos;
      array_push($position, "$startend");
      $tmpMass = $Protein_pep_arr[$counter][$pcounter]['CALC_NEUTRAL_PEP_MASS'];
      array_push($mass, "$tmpMass");
      array_push($peptide, "$peptideSeq");
    }
  }
}

asort($position, SORT_NUMERIC);
/*echo "<pre>";
print_r($position);
print_r($mass);
echo "</pre>";*/
$position =  array_unique($position);
for ($counter = 0; $counter < count($peptide); $counter += 1) {
  if (isset($position[$counter])){
    $tmp = $position[$counter].';;'.$mass[$counter].';;'.$peptide[$counter];
    array_push($observedPeptide, "$tmp");
  }
}
sort($observedPeptide, SORT_NUMERIC);

//*************************************************
function parse_proteinProphet($tppProtLocalPath){
//*************************************************
  global $Protein_arr;
  global $Protein_pep_arr;
  if(!$tppProtLocalPath) {
     $msg = "the XML cannot be opened:".$tppProtLocalPath;
     write_Log($msg);
     return false;
  }
  $protxml_P =& new xmlParser();
  if(!$protxml_P->parse($tppProtLocalPath) ){
    write_Log($protxml_P->error_msg);
    return false;
  }
  $i = 1;
  foreach($protxml_P->output[0]['child'] as $tmp_arr){
    if(isset($tmp_arr['name'])){
      if($tmp_arr['name'] == 'PROTEIN_GROUP'){
        $theProtein_arr = empty_prot_arr();
        $theProtein_pep_arr = array();
        $tmp_prot_arr = $tmp_arr['child'][0];
        if( isset($tmp_prot_arr['name']) and $tmp_prot_arr['name'] == 'PROTEIN'){
          $theProtein_arr = array_merge($tmp_prot_arr['attrs'], $theProtein_arr);
          if(!isset($theProtein_arr['PERCENT_COVERAGE'])) $theProtein_arr['PERCENT_COVERAGE'] = 0;
          if(!isset($theProtein_arr['PCT_SPECTRUM_IDS'])) $theProtein_arr['PCT_SPECTRUM_IDS'] = 0;
          foreach($tmp_prot_arr['child'] as $tmp_prot_pep_arr){
            if($tmp_prot_pep_arr['name'] == 'ANNOTATION'){
              $theProtein_arr['PROTEIN_DESCRIPTION'] = $tmp_prot_pep_arr['attrs']['PROTEIN_DESCRIPTION'];
            }else if($tmp_prot_pep_arr['name'] == 'PARAMETER'){
              if($tmp_prot_pep_arr['attrs']['NAME'] == 'prot_length'){
                $theProtein_arr['PROTEIN_LENGTH'] = $tmp_prot_pep_arr['attrs']['VALUE'];
              }
            }else if($tmp_prot_pep_arr['name'] == 'INDISTINGUISHABLE_PROTEIN'){
              $theProtein_arr['INDISTINGUISHABLE_PROTEIN'] .= parse_protein_Acc($tmp_prot_pep_arr['attrs']['PROTEIN_NAME'])."; ";
            }else if($tmp_prot_pep_arr['name'] == 'ANALYSIS_RESULT' and $tmp_prot_pep_arr['attrs']['ANALYSIS']=='xpress'){
              $theProtein_arr['RATIO_MEAN'] = $tmp_prot_pep_arr['child'][0]['attrs']['RATIO_MEAN'];
              $theProtein_arr['RATIO_STANDARD_DEV'] = $tmp_prot_pep_arr['child'][0]['attrs']['RATIO_STANDARD_DEV'];
              $theProtein_arr['RATIO_NUMBER_PEPTIDES'] = $tmp_prot_pep_arr['child'][0]['attrs']['RATIO_NUMBER_PEPTIDES'];
            }else if($tmp_prot_pep_arr['name'] == 'PEPTIDE'){
              if(isset($tmp_prot_pep_arr['child']) and $tmp_prot_pep_arr['child'][0]['name'] == 'MODIFICATION_INFO'){
                $tmp_prot_pep_arr['attrs']['PEPTIDE_SEQUENCE'] = $tmp_prot_pep_arr['child'][0]['attrs']['MODIFIED_PEPTIDE'];
              }
              array_push($theProtein_pep_arr, $tmp_prot_pep_arr['attrs']);
            }
          }
          $Protein_arr[$i] = $theProtein_arr;
          $Protein_pep_arr[$i] = $theProtein_pep_arr;
          $i++;
        }
      }
    }
  }//end of protXML
}
function empty_prot_arr(){
  $theProtein_arr = array();
  $theProtein_arr['INDISTINGUISHABLE_PROTEIN'] = '';
  $theProtein_arr['RATIO_MEAN'] =0;
  $theProtein_arr['RATIO_STANDARD_DEV'] =0;
  $theProtein_arr['RATIO_NUMBER_PEPTIDES'] = 0;
  $theProtein_arr['PROTEIN_DESCRIPTION'] ='';
  return $theProtein_arr;
}
?>
<html>
<head>
<title>COMET Sequence View</title>
<style type="text/css">
.hideit {display:none}
.showit {display:table-row}
.accepted {background: #87ff87; font-weight:bold;}
.rejected {background: #ff8700;}
body{font-family: Helvetica, sans-serif; }
h1  {font-family: Helvetica, Arial, Verdana, sans-serif; font-size: 24pt; font-weight:bold; color:#0E207F}
h2  {font-family: Helvetica, Arial, sans-serif; font-size: 20pt; font-weight: bold; color:#0E207F}
h3  {font-family: Helvetica, Arial, sans-serif; font-size: 16pt; color:#FF8700}
h4  {font-family: Helvetica, Arial, sans-serif; font-size: 14pt; color:#0E207F}
h5  {font-family: Helvetica, Arial, sans-serif; font-size: 10pt; color:#AA2222}
h6  {font-family: Helvetica, Arial, sans-serif; font-size:  8pt; color:#333333}
table   {border-collapse: collapse; border-color: #000000;}
.banner_cid   {
                 background: #0e207f;
                 border: 2px solid #0e207f;
                 color: #eeeeee;
                 font-weight:bold;
              }
.markSeq      {
                 color: #0000FF;
                 font-weight:bold;
              }
.markAA       {
                 color: #AA2222;
                 font-weight:bold;
              }
.glyco        {
                 background: #d0d0ff;
                 border: 1px solid black;
              }
.messages     {
                 background: #ffffff;
                 border: 2px solid #FF8700;
                 color: black;
                 padding: 1em;
              }
.formentry    {
               background: #eeeeee;
                 border: 2px solid #0e207f;
                 color: black;
                 padding: 1em;
              }
.nav          {
                 border-bottom: 1px solid black;
                 font-weight:bold;
              }
.graybox      {
                 background: #dddddd;
                 border: 1px solid black;
                 font-weight:bold;
              }
.seq          {
                 background: #ffaa33;
                 border: 1px solid black;
                 font-weight:bold;
              }
.info	      {
                 border-top: 1px solid black;
                 color: #333333;
		 font-size: 10pt;
              }
</style>
<script type="text/javascript" src="../common/javascript/site_javascript.js">
</script>
</head>

<body bgcolor="#c0c0c0" onload="self.focus();" link=#0000FF vlink=#0000FF>
<table cellspacing="0">
  <tr>
    <td class="banner_cid">&nbsp;&nbsp;Protein: <font color="ff8700"><?php echo $prot?></font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
  </tr>
</table>
<div class="formentry">
  <tt>
    <div class="graybox">
      <tt>&gt;<?php echo $prot?> <?php echo $proteinDesc?>
      </tt>
    </div>
    <?php 
    $pcounter = 0;
    $tmp_array = array();
    $tmp_array = explode(';;',$observedPeptide[$pcounter]);
    $pos = $tmp_array[0];
    $tmp_array = preg_split('/-/',$pos);
    $start = $tmp_array[0];
    $end = $tmp_array[1];
    for($counter =0; $counter< strlen($sequence);$counter += 1) {
      //print "st==".$start."==";
      //print "co==".$counter."==";
      if($start < $counter-1){
        $pcounter++;
        if($pcounter < count($observedPeptide)){
          $tmp_array = explode(';;',$observedPeptide[$pcounter]);
          //print $tmp_array[0];
          $posi = $tmp_array[0];
          $tmp_array = preg_split('/-/',$posi);
          $start = $tmp_array[0];
          $end = $tmp_array[1];
        }
      }
      if($counter == $start-1){
        echo "<font class=\"seq\">";
        for($pepcounter = $start-1; $pepcounter < $end; $pepcounter += 1){
          if (($pepcounter)%10 == 0){
            echo " $sequence[$pepcounter]";
          }else{
            echo "$sequence[$pepcounter]";
          }
        }
        echo "</font>";
        $counter = $end-1;
        //print "co1==".$counter."==";
        $pcounter++;
        if($pcounter < count($observedPeptide)){
          $tmp_array = explode(';;',$observedPeptide[$pcounter]);
          //print $tmp_array[0];
          $posi = $tmp_array[0];
          $tmp_array = preg_split('/-/',$posi);
          $start = $tmp_array[0];
          $end = $tmp_array[1];
        }
      }else{
        if ($counter%10 == 0){
          echo " $sequence[$counter]";
        }else{
          echo "$sequence[$counter]";
        }
      }
    }
    ?>
  </tt>
  <br/>
  <div align="right" class="info">
MONO MW: <b><?php echo $totalMass?></b><br/>
<!--
Links: <A HREF="javascript:popwin('http://www.google.com/search?q=<?php echo $prot?>',800,800,'new')">Google</A> <A HREF="javascript:popwin('http://scholar.google.com/scholar?q=<?php echo $prot?>',800,800,'new')">Google Scholar</A> <A HREF="javascript:popwin('http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=protein&cmd=search&term=<?php echo $prot?>',800,800,'new')">NCBI</A> <A HREF="javascript:popwin('http://www.expasy.ch/cgi-bin/sprot-search-ful?<?php echo $prot?>',800,800,'new')">ExPASy</A> <A HREF="javascript:popwin('http://genome-www4.stanford.edu/cgi-bin/SGD/locus.pl?locus=<?php echo $prot?>',800,800,'new')">SGD</A>
-->
  </div>
</div>
<br/>
<table cellspacing="0">
  <tr>
    <td class="banner_cid">&nbsp;&nbsp;Observed Peptides&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
  </tr>
</table>
<div class="formentry">
  <table cellpadding="2">
    <tr>
      <th class="nav">Position</th>
      <th class="nav">Mass</th>
      <th class="nav">Peptide</th>
    </tr>
    <?php 
    echo count($observedPeptide);
    for($counter = 0; $counter < count($observedPeptide); $counter += 1){
      $tmp_array = explode(';;',$observedPeptide[$counter]);
      $pos = $tmp_array[0];
      $pos_array = preg_split('/-/',$pos);
      $start_arr[$counter] = $pos_array[0];
      $end_arr[$counter] = $pos_array[1];
      $pepMass_arr[$counter] = $tmp_array[1];
      if($counter == 0){
        $pepMass = $tmp_array[1];
        $pepLen =  strlen($tmp_array[2]);
      }else{
        if($start_arr[$counter]>=$end_arr[$counter-1]){
          $pepMass = $pepMass + $tmp_array[1];
          $pepLen =  $pepLen + strlen($tmp_array[2]);
        }else{
        echo "$counter=$counter<br>";
          if($end_arr[$counter] > $end_arr[$counter-1] and $end_arr[$counter] > $end_arr[$counter-2]){
            $pepLen =  $pepLen +$end_arr[$counter]-$end_arr[$counter-1];
            $pepMass =  $pepMass +$pepMass_arr[$counter]-$pepMass_arr[$counter-1];
          }
        }
      }
      $obsPepMass =sprintf("%.2f",$tmp_array[1]);
      echo "<tr><td align=\"center\"><tt>$tmp_array[0]</tt></td>";
      echo "<td align=\"right\"><tt>$obsPepMass</tt></td>";
      echo "<td><tt><A HREF=\"javascript:popwin('http://www.ncbi.nlm.nih.gov/blast/Blast.cgi?CMD=Web&amp;LAYOUT=TwoWindows&amp;AUTO_FORMAT=Semiauto&amp;ALIGNMENTS=50&amp;ALIGNMENT_VIEW=Pairwise&amp;CDD_SEARCH=on&amp;CLIENT=web&amp;COMPOSITION_BASED_STATISTICS=on&amp;DATABASE=nr&amp;DESCRIPTIONS=100&amp;ENTREZ_QUERY=(none)&amp;EXPECT=1000&amp;FILTER=L&amp;FORMAT_OBJECT=Alignment&amp;FORMAT_TYPE=HTML&amp;I_THRESH=0.005&amp;MATRIX_NAME=BLOSUM62&amp;NCBI_GI=on&amp;PAGE=Proteins&amp;PROGRAM=blastp&amp;SERVICE=plain&amp;SET_DEFAULTS.x=41&amp;SET_DEFAULTS.y=5&amp;SHOW_OVERVIEW=on&amp;END_OF_HTTPGET=Yes&amp;SHOW_LINKOUT=yes&amp;QUERY=
$tmp_array[2]',800,800,'new')\">$tmp_array[2]</a></tt></td></tr>";
    }
    ?>
  </table>
  <div align="right" class="info"><b>Coverage:  AA <?php echo $proteinCove?>%</b> (<?php echo $pepLen?> / <?php echo $proteinLeng?> residues)   <b>Mass <?php echo sprintf("%.1f",$pepMass/$totalMass*100);?>%</b> (<?php echo sprintf("%.0f",$pepMass);?> / <?php echo $totalMass?> Da)
  </div>
</div>
<br/>
<hr noshade/>
</body>
</html>


