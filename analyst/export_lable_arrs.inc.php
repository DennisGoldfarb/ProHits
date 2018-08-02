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

//echo "\$currentType=$currentType<br>";

//echo "\$hitType=$hitType<br>";

if(!isset($type) || !$type){
  if($currentType == 'Band'){
    $type = 'Sample';
  }elseif($currentType == 'Exp'){
    $type = 'Experiment';
  }else{
    $type = $currentType;
  }  
}


if(isset($Is_geneLevel)){
  if($Is_geneLevel){
    $hitType = 'geneLevel';
  }
}elseif(isset($hitType)){
  if($hitType == 'geneLevel'){
    $Is_geneLevel = 1;
  }
}

$level2_SQL_str_arr['Bait'] = "BA.ID as BaitID,BA.GeneID,BA.LocusTag,BA.GeneName,BA.BaitAcc,BA.AccType,BA.TaxID,BA.BaitMW,BA.Tag,BA.Mutation,BA.Clone,BA.Vector,BA.Description,BA.GelFree";
$level2_SQL_str_arr['Plate'] = "P.ID as `PlateID`,P.Name as `PlateName`,P.PlateNotes,P.DigestedBy,P.Buffer,P.MSDate";
$level2_SQL_str_arr['Gel'] = "G.ID as `GelID`,G.Name as `GelName`,G.Image,G.Stain,G.GelType,G.OwnerID,G.ProjectID,G.DateTime AS GelDateTime";
$level2_SQL_str_arr['Band'] = "B.ID,B.BaitID,B.BandMW,B.Intensity,B.Location,B.Modification,B.BandPI,B.ResultsFile,B.InPlate,B.OwnerID,B.RawFile,B.DateTime";
$level2_SQL_str_arr['Experiment'] = "E.ID as ExpID,E.Name as ExpName,E.GrowProtocol,E.IpProtocol,E.DigestProtocol,E.PeptideFrag";
$level2_SQL_str_arr['Lane'] = "L.ID as `LaneID`,L.LaneNum,L.LaneCode";
$level2_SQL_str_arr['PlateWell'] = "PW.WellCode";
$RawFile_SQL_str = "ID AS FileID, FileName, Date , Size";

$header_SQL_str_arr['Bait'] = "SELECT ID as BaitID,GeneID,GeneName,GelFree";
$header_SQL_str_arr['Plate'] = "SELECT ID as `PlateID`,Name as `PlateName`";
$header_SQL_str_arr['Gel'] = "SELECT ID as `GelID`,Name as `GelName`,Image,GelType";
$header_SQL_str_arr['Band'] = "SELECT ID,BaitID,BandMW,Intensity,Location";
$header_SQL_str_arr['Experiment'] = "SELECT ID as ExpID,Name as ExpName";

$RawFileLableArr = array('FileName' => 'Raw File Name',
                    'Date' => 'Raw File Date',
                    'Size' => 'Raw File Size');

$LableArr['Bait'] = array('BaitID' => 'Bait ID',
                    'TaxID' => 'Bait Tax ID',
                    'GeneID' => 'Bait Gene ID',
                    'BaitAcc' => 'Bait Acc',
                    'GeneName' => 'Bait Gene Name',
                    'AccType' => 'Bait Acc Type',
                    'LocusTag' => 'Bait Locus Tag',
                    'BaitMW' => 'Bait MW',
                    'Clone' => 'Bait Clone',
                    'Vector' => 'Bait Vector',
                    'Description' => 'Bait Description',
                    'GelFree' => 'Is Gel Free');
$LableArr['Experiment'] = array('ExpID' => 'Experiment ID',
                    'ExpName' => 'Experiment Name',
                    'Protocol' => 'Protocol Numbers');
$LableArr['Band'] = array('ID' => 'Sample ID',
                    'BaitID' => 'Bait ID',
                    'BandMW' => 'Sample MW',
                    'Intensity' => 'Sample Intensity',
                    'Location' => 'Sample Name',
                    //'ResultsFile' => 'Sample ResultsFile',
                    'RawFile' => 'Sample Raw File');  
$LableArr['Sample'] = array('ID' => 'Sample ID',
                    'BandMW' => 'Sample MW',
                    'Intensity' => 'Sample Intensity',
                    'Location' => 'Sample Name',
                    //'ResultsFile' => 'Sample Results File',
                    'RawFile' => 'Instrument');
$LableArr['Gel'] = array('GelID' => 'Gel ID',
                    'GelName' => 'Gel Name',
                    'Image' => 'Gel Image',
                    'Stain' => 'Gel Stain',
                    'GelType' => 'Gel Type');
$LableArr['Lane'] = array('LaneID' => 'Lane ID',
                    'LaneNum' => 'Lane Number',
                    'LaneCode' => 'Lane Code');
$LableArr['Plate'] = array('PlateID' => 'Plate ID',
                    'PlateName' => 'Plate Name');
$LableArr['PlateWell'] = array('WellCode' => 'Plate Well Code');


$OpenFreezer = array('VectorID' => 'Vector ID',
                      'Cell_lineID' => 'Cell line ID',
                      'InsertID' => 'Insert ID',
                      'InsertAcc' => 'Insert Acc',
                      'InsertProteinFasta' => 'Insert Protein Fasta',
                      'EntrezGeneID' => 'Entrez Gene ID',
                      'GeneName' => 'Gene Name',
                      'Species' => 'Species');


$RawFileLableArr = array('FileName' => 'Raw File Name',
                    'Date' => 'Raw File Date',
                    'Size' => 'Raw File Size',
                    'RawFilePath' => 'Raw file path',
                    'TaskID' => 'Task ID',
                    'TppTaskID' => 'TPP task ID',
                    'SearchPar' => 'Search parameters');
foreach($RawFileLableArr as $key => $value){
  $LableArr['Sample'][$key] = $value;
} 

                      
$hitsLableArr['ID'] = 'Hit ID';
$hitsLableArr['GeneID'] = 'Hit GeneID';
$hitsLableArr['LocusTag'] = 'Hit Locus Tag';
$hitsLableArr['GeneName'] = 'Hit Gene Name';
$hitsLableArr['HitGI'] = 'Hit Protein ID';
$hitsLableArr['Acc'] = 'Protein Acc';
$hitsLableArr['Expect'] = (isset($SearchEngine) && $SearchEngine == 'SEQUEST')?"Hit Sequest Score":"Hit Score";
$hitsLableArr['RedundantGI'] = 'Redundant GI';
if((isset($SearchEngine) && $SearchEngine != 'SEQUEST') || ( isset($searchEngineField) && $searchEngineField != 'SEQUEST'))$hitsLableArr['Expect2'] = 'Hit XTandem Expect';
$hitsLableArr['MW'] = 'Hit MW';                     
$hitsLableArr['Pep_num'] = 'Total Peptide Number';
$hitsLableArr['ResultFile'] = 'Result File';
$hitsLableArr['Pep_num_uniqe'] = 'Unique Peptide Number';
$hitsLableArr['SearchEngine'] = 'Search Engine';
$hitsLableArr['Coverage'] = 'Hit Coverage';
$hitsLableArr['SearchDatabase'] = 'Search Database';
$hitsLableArr['HitName'] = 'Hit Description';
$hitsLableArr['Filters'] = 'Filters';
$hitsLableArr['Frequency'] = 'Project Frequency';


$geneLevel_hitsLableArr['ID'] = 'Hit ID';
//$geneLevel_hitsLableArr['WellID'] = ''; 
//$geneLevel_hitsLableArr['BaitID'] = ''; 
//$geneLevel_hitsLableArr['BandID'] = ''; 
//$geneLevel_hitsLableArr['Instrument'] = ''; 
$geneLevel_hitsLableArr['GeneID'] = 'Hit GeneID';
$geneLevel_hitsLableArr['GeneName'] = 'Hit Gene Name';
$geneLevel_hitsLableArr['Description'] = 'Description'; 
$geneLevel_hitsLableArr['SpectralCount'] = 'Spectral Count'; 
$geneLevel_hitsLableArr['Unique'] = 'Unique Peptide Number';
$geneLevel_hitsLableArr['Subsumed'] = 'Sub sumed'; 
//$geneLevel_hitsLableArr['Redundant'] = ''; 
$geneLevel_hitsLableArr['ResultFile'] = 'Filters';
$geneLevel_hitsLableArr['SearchDatabase'] = 'Search Database';
$geneLevel_hitsLableArr['SearchEngine'] = 'SearchEngine';


$geneLevel_PeptideLableArr['ID'] = 'Peptide ID';
$geneLevel_PeptideLableArr['Location'] = 'Peptide Location'; 
$geneLevel_PeptideLableArr['Sequence'] = 'Peptide Sequence';
$geneLevel_PeptideLableArr['SpectralCount'] = 'Spectral Count';
$geneLevel_PeptideLableArr['IsUnique'] = 'Is Unique';
$geneLevel_PeptideLableArr['Miss'] = 'Peptide Miss';
$geneLevel_PeptideLableArr['Modifications'] = 'Peptide Modifications';

                      
$TPPLableArr = array('ID' => 'TppID',
                      'GeneName'=> 'Protein Gene Name',
                      'AccType' => 'Protein Acc Type',
                      'GeneID' => 'Protein Gene ID',
                      'ProteinAcc' => 'Protein ID',
                      'Acc' => 'Protein Acc',
                      'LocusTag' => 'Protein Locus Tag',
                      'PROBABILITY' => 'Protein Probability',
                      'PCT_SPECTRUM_IDS' => 'PCT Spectrum IDs',
                      'INDISTINGUISHABLE_PROTEIN' => 'Indistinguishable Protein',
                      'ProteinDec' => 'Protein Dec.',
                      'TOTAL_NUMBER_PEPTIDES' => 'Total Number Peptide',
                      'UNIQUE_NUMBER_PEPTIDES' => 'Unique Number Peptide',
                      'PERCENT_COVERAGE' => 'Coverage Percentage',
                      'XmlFile' => 'Xml File',
                      'SearchEngine' => 'Search Engine',
                      'SearchDatabase' => 'Searched Database',
                      'XPRESSRATIO_MEAN' => 'Xpressratio Mean',
                      'XPRESSRATIO_STANDARD_DEV' => 'Xpressratio STandard Dev.',
                      'XPRESSRATIO_NUM_PEPTIDES' => 'Xpressratio Number Peptide',
                      'Frequency' => 'Project Frequency',
                      'Filters' => 'Filters');
                                           
                      
$PeptideLableArr = array('ID' => 'Peptide ID',
                      'Charge' => 'Peptide Charge',
                      'Expect' => 'Peptide Mascot Score',
                      'Expect2' => 'Peptide XTandem Expect',
                      'MASS' => 'Peptide Mass',
                      'Location' => 'Peptide Location',
                      'Sequence' => 'Peptide Sequence',
                      'Modifications' => 'Peptide Modifications',
                      'Status' => 'Peptide Status');  
                      
$TppPeptideGlableArr = array('ID' => 'Peptide Group ID',
                      'Sequence' => 'Sequence',
                      'N_ENZYMATIC_TERMINI' => 'N Enzymatic Termini',
                      'WEIGHT' => 'Peptide Weight',
                      'IS_CONTRIBUTING_EVIDENCE' => 'Is Contributing Evidence',
                      'NSP_ADJUSTED_PROBABILITY' => 'Nsp Adjusted Probability',
                      'INITIAL_PROBABILITY' => 'Initial Probability',
                      'N_SIBLING_PEPTIDES' => 'N Sibling Peptide',
                      'EXP_TOT_INSTANCES' => 'Exp Total Instances',
                      'Modifications' => 'Peptide Modifications',
                      'CALC_MASS' => 'Mass');
                      
$TppPeptideLableArr = array('ID' => 'Peptide ID',
                      'Protein' => 'Protein',
                      'Probability' => 'Peptide Probability',
                      'Score1' => 'Hyper Score',
                      'Score2' => 'Next Score',
                      'Score3' => 'B-Score',
                      'Score4' => 'Y-Score',
                      'Score5' => 'Expect',
                      'Ions' => 'Ions',
                      'Sequence' => 'Sequence',
                      'Charge' => 'Charge',
                      'Calc_mass' => 'Calc mass',
                      'Massdiff' => 'Delta Mass',
                      'Mised_cleavages' => 'Mised Cleavages');
                      
$SequestPeptideLableArr = array('ID' =>  'Peptide ID',
                      'Charge' => 'Peptide Charge',
                      'Expect' => 'Peptide Sequest Score',
                      'MZ' => 'Peptide MZ',
                      'MASS' => 'Peptide Mass',
                      'Location' => 'Peptide Location',
                      'Sequence' => 'Peptide Sequence',
                      //'Ions' => 'Peptide Ions',
                      'Modifications' => 'Peptide Modifications');
  
$Experiment_lable1_arr = array('ExpID' => 'Experiment ID',
                    'ExpName' => 'Experiment Name',
                    'GrowProtocol' => 'Growing Conditions/Protocol',
                    'IpProtocol' => 'Ip Conditions/Protocol',
                    'DigestProtocol' => 'Digest Conditions/Protocol',
                    'PeptideFrag' => 'Peptide Fractionation');
$ProtocolLableArr = array('GrowProtocol' => 'Growing Conditions/Protocol',
                    'IpProtocol' => 'Ip Conditions/Protocol',
                    'DigestProtocol' => 'Digest Conditions/Protocol',
                    'PeptideFrag' => 'Peptide Fractionation');

$level1_lable_array = array();
$level2_lable_array = array();
$level3_lable_array = array();
$level4_lable_array = array();

if(!isset($hitType)){
  if(strstr($SearchEngine, 'TPP_')){
    $hitType = 'TPP';
  }elseif(isset($Is_geneLevel) and $Is_geneLevel){
    $hitType = 'geneLevel';
  }else{
    $hitType = 'normal';
  }
}   

if($type == "Bait" || $type == "Sample" || $type == "Experiment"){
  $level1_lable_array = $LableArr['Bait'];
  $level1_header = 'Bait';
}elseif($type == "Plate"){
  $level1_lable_array = $LableArr['Plate'];
  $level1_header = 'Plate';
}elseif($type == "Gel"){
  $level1_lable_array = $LableArr['Gel'];
  $level1_header = 'Gel';
}

$level2_lable_array = $LableArr;
$level2_lable_array['Experiment'] = $Experiment_lable1_arr;

if($hitType == 'normal'){
  $level3_lable_array = $hitsLableArr;
}elseif($hitType == 'TPP'){
  $level3_lable_array = $TPPLableArr;
}elseif($hitType == 'TPPpep'){
  $level3_lable_array = $TppPeptideLableArr;
}elseif($hitType == 'geneLevel'){
  $level3_lable_array = $geneLevel_hitsLableArr;
}


$gelFrrItemsArr = array('Bait','Sample','Experiment','OpenFreezer');
$gelFrrItemsArr_not = array('Plate','Gel','Lane','PlateWell');


$formart_type_arr['Bait'] = "Bait___BaitID@Bait___GeneID@Bait___GeneName";
$formart_type_arr['Plate'] = "Bait___BaitID@Bait___GeneID@Bait___GeneName@Plate___PlateID@Plate___PlateName@PlateWell___WellCode";
$formart_type_arr['Gel'] = "Bait___BaitID@Bait___GeneID@Bait___GeneName@Gel___GelID@Gel___Image";
$formart_type_arr['Sample'] = "Bait___BaitID@Bait___GeneID@Bait___GeneName@Sample___ID@Sample___Location";
$formart_type_arr['Experiment'] = "Bait___BaitID@Bait___GeneID@Bait___GeneName@Experiment___ID@Experiment___Name";

$formart_hitType_arr['normal'] = "level3___GeneID@level3___GeneName@level3___HitGI@level3___Expect@level3___Expect2@level3___Pep_num_uniqe";
//-------------------------
$formart_hitType_arr['geneLevel'] = "level3___GeneID@level3___GeneName@level3___Subsumed@level3___SpectralCount@level3___Unique";
//-------------------------
$formart_hitType_arr['TPP'] = "level3___GeneName@level3___ProteinAcc@level3___GeneID@level3___PROBABILITY@level3___TOTAL_NUMBER_PEPTIDES";
$formart_hitType_arr['TPPpep'] = "level3___Protein@level3___Probability@level3___Sequence";

$formart_type_arr[$type] = trim($formart_type_arr[$type]);
$formart_hitType_arr[$hitType] = trim($formart_hitType_arr[$hitType]);
$joiner = '';
if($formart_type_arr[$type] && $formart_hitType_arr[$hitType]){
  $joiner = "@";
}
$formart_str = $formart_type_arr[$type] . $joiner . $formart_hitType_arr[$hitType];
$default_pre_defined_formar_arr = array('ID'=>'0',
                                        'Name'=>'default',
                                        'User'=>'0',
                                        'Format'=> $formart_str);
$msManagerDB = new mysqlDB(MANAGER_DB, HOSTNAME, USERNAME, DBPASSWORD);      
?>
