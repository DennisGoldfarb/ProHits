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
include("../config/conf.inc.php");
require("../common/common_fun.inc.php");
include("../msManager/classes/xmlParser_class.php");
include("../msManager/is_dir_file.inc.php");

set_time_limit(3600*2);
$uniqueStrippedPeptides = 0;
$totalPeptides = 0;
$Protein_arr = array();
$Protein_pep_arr = array();
$uppload_tpp_dir_path = get_uploaded_search_results_dir('TPP');

$PARAM = array_merge($_GET, $_POST);
$userID = $PARAM['userID'];
$File = $uppload_tpp_dir_path . $PARAM['File'];
$group_id_arr = array();


parse_proteinProphet($File);

/*echo "<pre>";
print_r($Protein_arr);
print_r($Protein_pep_arr);
echo "</pre>";*/

//*************************************************
function parse_proteinProphet($tppProtLocalPath){
//*************************************************
  global $Protein_arr;
  global $Protein_pep_arr;
  global $group_id_arr;
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
  
  
  $group_id = 0;
  foreach($protxml_P->output[0]['child'] as $tmp_arr){
    if(isset($tmp_arr['name'])){
      if($tmp_arr['name'] == 'PROTEIN_GROUP'){
        $group_id++;
        $group_NUMBER_PEPTIDES = 0;
        foreach($tmp_arr['child'] as $tmp_prot_arr){
          $theProtein_arr = empty_prot_arr();
          $theProtein_pep_arr = array();
          $theProtein_arr['GROUP_ID'] = $group_id;
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
              $group_NUMBER_PEPTIDES += $theProtein_arr['TOTAL_NUMBER_PEPTIDES'];
            }
            array_push($Protein_arr, $theProtein_arr);
            array_push($Protein_pep_arr, $theProtein_pep_arr);
          }//end of protein group
        }
        if($group_NUMBER_PEPTIDES) array_push($group_id_arr, $group_id);
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
<HTML>
<HEAD>
<TITLE>ProteinProphet protXML View</TITLE>
<script type="text/javascript" src="../common/javascript/site_javascript.js"></script>
</HEAD>
<BODY BGCOLOR="white">
<H1><IMG SRC="../msManager/images/tpp.gif"
ALIGN="TOP" BORDER="0" NATURALSIZEFLAG="3">&nbsp;&nbsp;TPP Search Results</H1>
<H3>ProteinProphet protXML View</H3>

<form>

<font color="red"><?php echo $File?></font>
<PRE>
<FONT COLOR="990000">* corresponds to peptide is_nondegenerate_evidence flag</FONT>
<table cellpadding="0" bgcolor="white" class="results"><!--start-->
<table cellpadding="0" bgcolor="white" class="results">
<?php 
$old_group_ID = '';
for ($counter=0; $counter < count($Protein_arr); $counter++) {

  $group_ID = $Protein_arr[$counter]['GROUP_ID'];
  //if($Protein_arr[$counter]['TOTAL_NUMBER_PEPTIDES']!= 0){
  if(in_array($group_ID, $group_id_arr)){
    $tmp = array();
    $tmp = preg_split('/\+/',$Protein_arr[$counter]['UNIQUE_STRIPPED_PEPTIDES']);
    $uniqueStrippedPeptides += count($tmp);
    $totalPeptides += $Protein_arr[$counter]['TOTAL_NUMBER_PEPTIDES'];
    $TMP_P_arr =  explode(";", $Protein_arr[$counter]['INDISTINGUISHABLE_PROTEIN']);
    $TMP_P_val_str = '';
    foreach($TMP_P_arr as $TMP_P_val){
      $TMP_P_val_t = trim($TMP_P_val);
      if(!$TMP_P_val_t) continue;
      if(is_numeric($TMP_P_val_t)){
        $TMP_P_val_str .= " gi|$TMP_P_val_t";
      }else{
        $TMP_P_val_str .= " $TMP_P_val_t";
      }   
    }
?>
  <tr>
    <td height="8" colspan="10">
    </td>
  </tr>
  <tr>
    <td>
<?php if($group_ID != $old_group_ID){?>    
      <nobr>
        <input type="checkbox" name="excl1" style="height: 15px; width: 15px;" value="yes"><?php echo $group_ID?>
      </nobr>
<?php }?> 
    </td>
    <td colspan="10">
    <!--A HREF="javascript:popwin('CometSeqViewForTPPproHTML.php?xmlfile=<?php echo $File?>&prot=<?php echo $Protein_arr[$counter]['PROTEIN_NAME']?>',800,800,'new')"-->
    <?php echo $Protein_arr[$counter]['PROTEIN_NAME']?><!--/A-->&nbsp;<?php echo $TMP_P_val_str?>&nbsp;<font color="red"><b><?php echo $Protein_arr[$counter]['PROBABILITY']?></b></font>
    </td>
  </tr>
  <tr>
    <td>
    </td>
    <td colspan="11">
      <table cellpadding="0" bgcolor="white" class="results">
        <tr>
		  <?php 
		  if(isset($Protein_arr[$counter]['CONFIDENCE'])){
            echo "<td width=\"150\">confidence: $Protein_arr[$counter]['CONFIDENCE']";
		  }else{
		    echo "<td width=\"150\">";
		  }
		  ?>
          </td>
          <td width="150">coverage: <?php echo $Protein_arr[$counter]['PERCENT_COVERAGE']?>%
          </td>
          <td width="225">num unique peps: <?php echo count($Protein_pep_arr[$counter]);?>
          </td>
          <td width="225">tot indep spectra: <?php echo $Protein_arr[$counter]['TOTAL_NUMBER_PEPTIDES']?>
          </td>
          <td width="225">share of spectrum id's: <?php echo $Protein_arr[$counter]['PCT_SPECTRUM_IDS']?>%
          </td>
          <td>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
    </td>
    <td bgcolor="E0E0E0" width="675" colspan="8">
      <font color="green">&gt;</font>
      <font color="green"><?php echo $Protein_arr[$counter]['PROTEIN_DESCRIPTION']?></font>
    </td>
    <?php 
    if(isset($Protein_arr[$counter]['PROTEIN_LENGTH'])){
      echo "<td width=\"150\">Length: $Protein_arr[$counter]['PROTEIN_LENGTH']aa";
  	}else{
  	  echo "<td width=\"150\">";
  	}
	  ?>
	</td>
  </tr>
  <tr height="50">
    <td>
    </td>
    <td>
    </td>
    <td><font color="brown"><i>weight</i></font>
    </td>
    <td><font color="brown"><i>peptide sequence</i></font>
    </td>
    <td><font color="brown"><i>nsp adj prob</i></font>
    </td>
    <td><font color="brown"><i>init prob</i></font>
    </td>
    <td><font color="brown"><i>ntt</i></font>
    </td>
    <td><font color="brown"><i>nsp</i></font>
    </td>
    <td><font color="brown"><i>total</i></font>
    </td>
    <td><font color="brown"><i>pep grp ind</i></font>
    </td>
    <td>
    </td>
  </tr>
  <?php 
  for ($pcounter = 0; $pcounter < count($Protein_pep_arr[$counter]); $pcounter += 1) {
    if($Protein_pep_arr[$counter][$pcounter]['IS_CONTRIBUTING_EVIDENCE'] == 'N'){
      $font_color = "black";
    }else{
      $font_color = "#FF9933";
    }  
  ?>
  <tr>
    <td></td>
    <td><font color="#990000"><?php echo ($Protein_pep_arr[$counter][$pcounter]['IS_NONDEGENERATE_EVIDENCE']=='Y')?'*':''?></font></td>
    <td><font color="#0000ff"><nobr>wt-<?php echo $Protein_pep_arr[$counter][$pcounter]['WEIGHT']?></nobr></font></td>
    <td><font color="#0000ff" size= "2"><?php echo $Protein_pep_arr[$counter][$pcounter]['CHARGE']?>_<?php echo $Protein_pep_arr[$counter][$pcounter]['PEPTIDE_SEQUENCE']?></font>
    </td>
    <td><font COLOR="<?php echo $font_color?>"><?php echo $Protein_pep_arr[$counter][$pcounter]['NSP_ADJUSTED_PROBABILITY']?></font>
    </td>
    <td><?php echo $Protein_pep_arr[$counter][$pcounter]['INITIAL_PROBABILITY']?></td>
    <td><?php echo $Protein_pep_arr[$counter][$pcounter]['N_ENZYMATIC_TERMINI']?></td>
    <td><!-A HREF="javascript:popwin('show_nspbin_for_ProhitsTPP_proHTML.php?xmlfile=<?php echo $File?>&nsp_bin=2&nsp_val=<?php echo $Protein_pep_arr[$counter][$pcounter]['N_SIBLING_PEPTIDES']?>&charge=<?php echo $Protein_pep_arr[$counter][$pcounter]['CHARGE']?>&pep=<?php echo $Protein_pep_arr[$counter][$pcounter]['PEPTIDE_SEQUENCE']?>&prot=<?php echo $Protein_arr[$counter]['PROTEIN_NAME']?>',900,600,'new')"--><?php echo $Protein_pep_arr[$counter][$pcounter]['N_SIBLING_PEPTIDES']?></A>
    </td>
    <td><?php echo $Protein_pep_arr[$counter][$pcounter]['N_INSTANCES']?>
    </td>
    <?php 
    if(isset($Protein_pep_arr[$counter][$pcounter]['PEPTIDE_GROUP_DESIGNATOR'])){
    ?>
    <td><font color="#DD00DD"><?php echo $Protein_pep_arr[$counter][$pcounter]['PEPTIDE_GROUP_DESIGNATOR']?>-<?php echo $Protein_pep_arr[$counter][$pcounter]['CHARGE']?></font>
    <?php 
    }
    ?>
    </td>
  </tr>
  <?php 
  }
  ?>
  <tr>
    <td>
    </td>
  </tr>
<?php 
  $old_group_ID = $group_ID;
  }
}
?>
</table>
</table>
</form>
</PRE>
</BODY>
</HTML>
<HR/>
<center>
<font color="red">
<b><?php echo round($totalPeptides/2)?> total peptides, <?php echo $uniqueStrippedPeptides?> unique stripped peptides</b>
</font>
</center>
