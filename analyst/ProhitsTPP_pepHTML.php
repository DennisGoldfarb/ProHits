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
include("../config/conf.inc.php");
require("../common/common_fun.inc.php");
include("../msManager/classes/xmlParser_class.php");
include("../msManager/is_dir_file.inc.php");

$uniqueStrippedPeptides = 0;
$totalPeptides = 0;
$peptide_arr = array();
$uppload_tpp_dir_path = get_uploaded_search_results_dir('TPP');
$PARAM = array_merge($_GET, $_POST);
$userID = $PARAM['userID'];
$File = $uppload_tpp_dir_path . $PARAM['File'];

parse_peptideProphet($File);
//echo "<pre>";
//print_r($peptide_arr);
//echo "</pre>";

//************************************************
function parse_peptideProphet($tppPepLocalPath){
//************************************************
  global $searchEngine;
  global $peptide_arr;
  $pepxml_P = new xmlParser();
  if(!$pepxml_P->parse($tppPepLocalPath) ){
    write_Log($pepxml_P->error_msg);
    print $pepxml_P->error_msg;
    return false;
  }
  $i = 1;
  foreach($pepxml_P->output[0]['child'] as $tmp_arr){
    if(isset($tmp_arr['name'])){
      if($tmp_arr['name'] == 'MSMS_RUN_SUMMARY'){
        $xmlPep_arr = $tmp_arr['child'];
        foreach($xmlPep_arr as $tmp_arr){
          $tmp_pep_arr = empty_pep_arr();
          if($tmp_arr['name'] == 'SEARCH_SUMMARY'){
            if(isset($tmp_arr['attrs']['SEARCH_ENGINE'])){
              $searchEngine = $tmp_arr['attrs']['SEARCH_ENGINE'];
            }
          }
          if($tmp_arr['name'] == 'SPECTRUM_QUERY'){
            $tmp_pep_arr =  array_merge($tmp_pep_arr, $tmp_arr['attrs']);
            if($tmp_arr['child'][0]['name'] == 'SEARCH_RESULT'){
              $tmp_pep_arr = array_merge($tmp_pep_arr, $tmp_arr['child'][0]['child'][0]['attrs']);
              $tmp_score_arr = $tmp_arr['child'][0]['child'][0]['child'];
              $score_i = 1;
              foreach($tmp_score_arr as $score_arr){
                if($score_arr['name'] == 'MODIFICATION_INFO'){
                  $tmp_pep_arr["PEPTIDE"] = $score_arr['attrs']['MODIFIED_PEPTIDE'];
                }else if($score_arr['name'] == 'SEARCH_SCORE'){
                  $tmp_pep_arr["Score$score_i"] = $score_arr['attrs']['VALUE'];
                  $score_i++;
                }else if($score_arr['name'] == 'ANALYSIS_RESULT'){
                  if($score_arr['attrs']['ANALYSIS'] == 'peptideprophet'){
                    $tmp_pep_arr["PROBABILITY"] = $score_arr['child'][0]['attrs']['PROBABILITY'];
                    if($score_arr['child'][0]['child'][0]['child'][0]['attrs']['NAME'] == 'fval'){
                      $tmp_pep_arr["Fval"] = $score_arr['child'][0]['child'][0]['child'][0]['attrs']['VALUE'];
                    }
                  }else if($score_arr['attrs']['ANALYSIS'] == 'libra'){
                     $tmp_libra_arr = $score_arr['child'][0]['child'];
                     $libra_i = 1;
                     foreach($tmp_libra_arr as $tmp_libra){
                        $tmp_pep_arr["Libra$libra_i"] = $tmp_libra['attrs']['ABSOLUTE'];
                        $libra_i++;
                     }
                  }else if($score_arr['attrs']['ANALYSIS'] == 'xpress'){
                     $tmp_pep_arr["Xpress"] =  $score_arr['child'][0]['attrs']['RATIO'];
                  }
                }
              }
              $peptide_arr[$i] = $tmp_pep_arr;
              $i++;
            }
          }
        }
      }
    }
  }//end of pepXML
}
function empty_pep_arr(){
  $tmp_pep_arr = array();
  $tmp_pep_arr["PROBABILITY"] = 0;
  $tmp_pep_arr["PEPTIDE"] = '';
  $tmp_pep_arr["Score1"] = 0;
  $tmp_pep_arr["Score2"] = 0;
  $tmp_pep_arr["Score3"] = 0;
  $tmp_pep_arr["Score4"] = 0;
  $tmp_pep_arr["Score5"] = 0;
  $tmp_pep_arr["Fval"] = '';
  $tmp_pep_arr["Libra1"] = 0;
  $tmp_pep_arr["Libra2"] = 0;
  $tmp_pep_arr["Libra3"] = 0;
  $tmp_pep_arr["Libra4"] = 0;
  $tmp_pep_arr["Xpress"] = '';
  $tmp_pep_arr["RETENTION_TIME_SEC"] = '';
  return $tmp_pep_arr;
}
?>
<HTML>
<HEAD>
<TITLE>PeptideProphet pepXML View</TITLE>
<script type="text/javascript" src="../common/javascript/site_javascript.js"></script>
</HEAD>
<BODY BGCOLOR="white">
<H1><IMG SRC="../msManager/images/tpp.gif"
ALIGN="TOP" BORDER="0" NATURALSIZEFLAG="3">&nbsp;&nbsp;TPP Search Results</H1>
<H3>PeptideProphet pepXML View</H3>
<PRE>
<form name="PepXMLViewForm">
<!-- data table -->
<table border='1' BORDERCOLOR = '#000000'>
  <tr bgcolor='#aaaaaa'>
    <td valign="top" style="font-family: monospace; font-variant: small-caps;">prob</td>
    <td valign="top" style="font-family: monospace; font-variant: small-caps;">spectrum</td>
    <td valign="top" style="font-family: monospace; font-variant: small-caps;">expect</td>
    <td valign="top" style="font-family: monospace; font-variant: small-caps;">ions</td>
    <td valign="top" style="font-family: monospace; font-variant: small-caps;">peptide</td>
    <td valign="top" style="font-family: monospace; font-variant: small-caps;">protein</td>
    <td valign="top" style="font-family: monospace; font-variant: small-caps;">calc_mass</td>
  </tr>
  <?php 
  for ($counter = 1; $counter <= count($peptide_arr); $counter += 1) {
    if($counter%2 ==0){
      echo "<tr bgcolor='#FFFFFF'>";
    }else{
      echo "<tr bgcolor='#DDE0FF'>";
    }
  ?>
    <td style="text-align: center;" ><?php echo $peptide_arr[$counter]['PROBABILITY']?></A></td>
    <td style="text-align: left;" ><?php echo $peptide_arr[$counter]['SPECTRUM']?><img border="0" src="./images/spectrast_tiny.png" alt="SpectraST" /></td>
    <td style="text-align: center;" ><?php echo $peptide_arr[$counter]['Score5']?></td>
    <td style="text-align: center;" ><?php echo $peptide_arr[$counter]['NUM_MATCHED_IONS']?>/<?php echo $peptide_arr[$counter]['TOT_NUM_IONS']?></td>
    <td style="text-align: left;" ><?php echo $peptide_arr[$counter]['PEPTIDE_PREV_AA']?>.<a href="javascript:popwin('http://www.ncbi.nlm.nih.gov/blast/Blast.cgi?CMD=Web&amp;LAYOUT=TwoWindows&amp;AUTO_FORMAT=Semiauto&amp;ALIGNMENTS=50&amp;ALIGNMENT_VIEW=Pairwise&amp;CDD_SEARCH=on&amp;CLIENT=web&amp;COMPOSITION_BASED_STATISTICS=on&amp;DATABASE=nr&amp;DESCRIPTIONS=100&amp;ENTREZ_QUERY=(none)&amp;EXPECT=1000&amp;FILTER=L&amp;FORMAT_OBJECT=Alignment&amp;FORMAT_TYPE=HTML&amp;I_THRESH=0.005&amp;MATRIX_NAME=BLOSUM62&amp;NCBI_GI=on&amp;PAGE=Proteins&amp;PROGRAM=blastp&amp;SERVICE=plain&amp;SET_DEFAULTS.x=41&amp;SET_DEFAULTS.y=5&amp;SHOW_OVERVIEW=on&amp;END_OF_HTTPGET=Yes&amp;SHOW_LINKOUT=yes&amp;QUERY=<?php echo $peptide_arr[$counter]['PEPTIDE']?>',800,800,'new')"><?php echo $peptide_arr[$counter]['PEPTIDE']?></a>.<?php echo $peptide_arr[$counter]['PEPTIDE_NEXT_AA']?><a href="javascript:popwin('https://db.systemsbiology.net/sbeams/cgi/PeptideAtlas/Search?organism_name=Any;search_key=%25<?php echo $peptide_arr[$counter]['PEPTIDE']?>%25;action=GO',800,800,'new')"><img border="0" src="./images/pa_tiny.png" alt="PeptideAtlas" /></a></td>
    <td style="text-align: left;" ><?php echo $peptide_arr[$counter]['PROTEIN']?></td>
    <td style="text-align: center;" ><?php echo $peptide_arr[$counter]['CALC_NEUTRAL_PEP_MASS']?></td>
  </tr>
  <?php 
  }
  ?>
</table>
</form>
</PRE>
</BODY>
</HTML>

