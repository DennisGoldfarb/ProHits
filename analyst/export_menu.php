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

$frm_selected_bait_str = '';
$frm_order_by = '';

require("../common/site_permission.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/experiment_class.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("analyst/site_header.php"); 
?>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
   <td><br><br>
    <b><a href="export_bait_to_hits.php?firstDisplay=y" class=button>Export Bait-Hits Report (CSV)</a></b>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
   <td> <div class=maintext>
         Click to create a filtered or unfiltered bait-hits report (in csv or tsv format).
        </div>
   </td>
  </tr>
  
  <tr>
   <td><br><br>
    <b><a href="export_bait_to_hits.php?firstDisplay=y&public=SAINT" class=button>Export interaction data to run SAINT</b>
    &nbsp; &nbsp; <img src="./images/saint_logo.gif" alt="" border="0"></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
   <td> <div class=maintext>
         SAINT (Significance Analysis of INTeractome) is a generalized computational method that utilizes label-free quantitative data, such
as spectral counts, to assign a confidence value to individual protein-protein interactions. SAINT constructs separate distributions for
true and false interactions to derive the probability of a bona fide protein-protein interaction. The modeling incorporates internal data
normalization procedures, and utilizes data from control purifications when available. ProHits helps you generate the files required to
run SAINT.
        </div>
   </td>
  </tr>
  
  
  <tr>
    <td align="left"><br>
    <a href="export_bait_to_hits.php?firstDisplay=y&public=IntAct" class=button><b>Export interaction data in PSI-MI XML v2.5 format</b>
    <img src="./images/imex_logo.jpg" alt="" border="0">&nbsp; &nbsp; <img src="./images/intact-logo.png" alt="" border="0"></a>
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
     <td>
     <div class=maintext>
    The HUPO Proteomics Standards Initiative (PSI) defines community standards for data representation in proteomics to facilitate data comparison, exchange and verification.   One of its workgroups deals with Molecular Interactions (PSI-MI), with PSI-MI XML v2.5 being the preferred format for submitting data to the IMEx consortium interaction databases. ProHits helps you prepare your submission to IMEx databases by mapping the controlled vocabulary necessary for data submission and preparing the XML file for you. 
    <br><br>
    The IntAct database encourages and welcomes direct user submission of molecular interaction data. Datasets may be deposited prior to publication to a peer-reviewed journal. The IntAct team will be happy to assist you with final data preparation, and will make your submission publicly available as soon as your article is published.
    <br><a class=button target=blank href='http://www.ebi.ac.uk/intact/pages/documentation/data_submission.xhtml'>
    [contact IntAct]</a>
    </div>
     </td>
  </tr>
  <tr>
    <td align="left"><br>
    <a href="export_bait_to_hits.php?firstDisplay=y&public=BioGRID_Tab" class=button><b>Export interaction data in MITAB format  </b>
    <img src="./images/gridsmall.jpg" border=0>
    </a> 
    </td>
  </tr>
  <tr>
    <td height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
    <td>  
      <div class=maintext>The HUPO Proteomics Standards Initiative (PSI) defines community standards for data representation in proteomics to facilitate data comparison, exchange and verification.   One of its workgroups deals with Molecular Interactions (PSI-MI), with MITAB being their tab delimited data exchange format.  MITAB is the preferred format for submitting data to the BioGRID interaction database. ProHits helps you prepare your submission to BioGRID by mapping the controlled vocabulary necessary for data submission and preparing the tab delimited file for you.
      <a class=button target=blank href='http://www.thebiogrid.org/viewdocument.php?documentid=7'><br>
    [contact BioGRID]</a>
      </div> 
    </td>
</table>
</form>
<?php 
require("site_footer.php");
?>

