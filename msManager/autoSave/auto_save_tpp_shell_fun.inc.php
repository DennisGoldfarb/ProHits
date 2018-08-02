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

//error_reporting(E_ALL ^ E_NOTICE);

$pGIs = array();
$pRedundantGIs= array();
$pNames= array();
$pMasses= array();
$AccessionType = "GI";

function save_tpp_results($table, $source_well_ids, $Engine, $Conf, $field_spliter=";;"){
  global $USER;
  global $hitsDB;
  global $managerDB;
  global $proteinDB;
  global $task_arr;
  global $searchedDB;
  global $searchEngine;
  global $save_gene_parse_arr;
  global $gpm_ip;
  global $frm_geneLevelHits;
	
	global $debug;
  
  $searchEngine = $Engine;  
  $tableTppResults = $table . "tppResults";
  
  $theProhitsID = 0;
  $theProjectID = 0;
  $theProtXML_file = '';
  $thePepXML_file = '';
  
  $peptide_saved = false;
  $protein_saved = false;
  
  $SQL = "Select WellID, TppTaskID, SearchEngine, pepXML, protXML, ProhitsID, ProjectID, SavedBy from " . $tableTppResults . " Where 
  WellID='".$source_well_ids."' and 
  TppTaskID='".$Conf->TppTaskID."' and 
  SearchEngine='".$searchEngine."'";
   
  $theTPP = $managerDB->fetch($SQL);
  if($theTPP['SavedBy']) {
    return false;
  }
   
  if(!$theTPP) {
    $msg = "No tpp task results found for : WellID=".$source_well_ids." TppTaskID=".$Conf->TppTaskID." SearchEngine=".$searchEngine;
    write_Log($msg);
    return false;
  }
 
  if(strpos($source_well_ids, ",")){
    $theProhitsID = $theTPP['ProhitsID'];
    $theProjectID = $theTPP['ProjectID'];
  }else{
    $SQL = "Select ProhitsID, ProjectID from ". $table . " where ID='" . $theTPP['WellID'] . "'";
    $theRaw = $managerDB->fetch($SQL);
    if($theRaw) {
      $theProhitsID = $theRaw['ProhitsID'];
      $theProjectID = $theRaw['ProjectID'];
    }
  }
  if(!$theProhitsID or !$theProjectID or !$theTPP['pepXML']){
    $msg = "No sample ID or tpp task results found for : WellID=".$source_well_ids." TppTaskID=".$Conf->TppTaskID." SearchEngine=".$searchEngine;
    write_Log($msg);
    return false;
  }else{
    if(!check_hitsDB($theProjectID)) {
      return false;
    }
  }
  if($debug) {
		print "in save_tpp_results()\n";
		print_r($theTPP);
  }
  
  
  if(!tppxml_parsed('TppPeptide', $theTPP['pepXML'])){
    $thePepXML_file = getTppXML($theTPP['pepXML'], $table);
  }else{
    //return false;
  }
  //***************************************************************************
  //$rt = parse_peptideProphet($theProhitsID, $thePepXML_file, $theTPP['pepXML']);
  //*************************************************************************** 
  
  
  //if(!tppxml_parsed('TppProtein',$theTPP['protXML'])){
    $noCopyInLocal = 1;
    $theProtXML_file = getTppXML($theTPP['protXML'], $table, $noCopyInLocal);
  //}
	print "protXML file: $theProtXML_file\n";
  
   
  
  //*****************************************
  //$genelevel_Parsed = 0. going to parse.
  //$genelevel_Parsed = 1. parsed.
  //$genelevel_Parsed = 2. waiting, don't parse now.
  $genelevel_Parsed = '2';
  if($frm_geneLevelHits){
    $genelevel_Parsed = '0';
  }
  //*****************************************
 
  $fasta_geneMap_arr = create_fasta_map_file($thePepXML_file);
  if(!isset($isUploaded)){
    $isUploaded = 0;
  }
  if(is_array($fasta_geneMap_arr)){
    $SQL = "SELECT `ID`,`Parsed`
            FROM `GeneLevelParse`
            WHERE `Machine`='$table' 
                   AND `TaskID`='".$Conf->TaskID."' 
                   AND `TppID`='".$Conf->TppTaskID."' 
                   AND `pepXML_original`='".$theTPP['pepXML']."'
                   AND `ProhitsID`='$theProhitsID' 
                   AND `SearchEngine`='$searchEngine' 
                   AND `ProjectID`='$theProjectID' 
                   AND `isUploaded`='$isUploaded' 
                   AND `FastaFile`='".$fasta_geneMap_arr['local_fasta_path']."'";
    $tmp_sql_arr = $managerDB->fetch($SQL);
 

    $tmp_name = basename($thePepXML_file);
    $tmp_dir = dirname($thePepXML_file);
    $tmp_thePepXML_file = $tmp_dir."/".$theTPP['WellID']."_task".$Conf->TaskID."_tpp".$Conf->TppTaskID."_".$tmp_name;
    $cmd = "mv '$thePepXML_file' '$tmp_thePepXML_file'";
    system($cmd);
    if(!$tmp_sql_arr){
      $SQL = "INSERT INTO `GeneLevelParse` SET
             `Machine`='$table', 
             `TaskID`='".$Conf->TaskID."', 
             `TppID`='".$Conf->TppTaskID."', 
             `pepXML_original`='".$theTPP['pepXML']."', 
             `pepXML`='".$tmp_thePepXML_file."', 
             `ProhitsID`='$theProhitsID', 
             `SearchEngine`='$searchEngine', 
             `ProjectID`='$theProjectID', 
             `isUploaded`='$isUploaded', 
             `FastaFile`='".$fasta_geneMap_arr['local_fasta_path']."', 
             `Parsed`=$genelevel_Parsed";
      $insertID = $managerDB->insert($SQL);
     
    }
  }else{
    $msg = "protein gene map file is failed to created. Gene level hits cannot be parsed.";
    echo "$msg<br>";
    write_Log($msg);
  }
  
  
  
  
  
  
   
  //************************************************************************************************
  $rt = parse_proteinProphet($theProhitsID, $theProtXML_file, $theTPP['pepXML'], $theTPP['protXML']);
  if($rt){
    $SQL = "UPDATE $tableTppResults SET SavedBy='".$USER['ID']."' WHERE pepXML='".$theTPP['pepXML']."'";
    $managerDB->execute($SQL);
    $msg = "File parsed: " . $theTPP['protXML'];
    if($theProjectID && $searchEngine){
      $searchEngine_TPP = 'TPP_'.$searchEngine;
      hits_searchEngines('update', $theProjectID, $hitsDB, $searchEngine_TPP);
    }
    write_Log($msg);
  }
  //if(is_file($theProtXML_file)) unlink($theProtXML_file);
  //if(is_file($thePepXML_file)) unlink($thePepXML_file);
  return true;
}

//-------------------------------------
function getTppXML($xmlFilePath='', $table, $noCopyInLocal = 0){
  global $gpm_ip;
  if(!$xmlFilePath) return '';
  $tmp_folder = "../../TMP/parser";
  $tmp_file_path =  $tmp_folder."/".$table;
  if(!is_dir($tmp_folder)) mkdir($tmp_folder, 0755);
  if(!is_dir($tmp_file_path))mkdir($tmp_file_path, 0755);
  $tmp_file_path .= "/".basename($xmlFilePath);
  if(_is_file($xmlFilePath)){
    $from = $xmlFilePath;
    if($noCopyInLocal){
      return $from;
    }
  }else{
    $http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
    $tpp_formaction = $http_gpm_cgi_dir . "/Prohits_TPP.pl";
    $postData = "tpp_myaction=downloadTppXML&fileName=" . $xmlFilePath;
    $from = $tpp_formaction."?".$postData;
  }
  if(copy($from, $tmp_file_path)){
    return $tmp_file_path;
  }else{
    $msg= "cannot get xml file from: ".$tpp_formaction."?".$postData." to the local folder $tmp_folder\n";
    write_Log($msg);
    return '';
  }
}

//----------------------------------------
function save_tpp_peptide($theProhitsID, $thePepXML_file, $tmp_pep_arr){
  global $hitsDB;
  $pep_id = 0;

  $tmp_pep_arr['PROTEIN'] = parse_protein_Acc($tmp_pep_arr['PROTEIN']);
  if(!isset($tmp_pep_arr['NUM_MATCHED_IONS']) || !isset($tmp_pep_arr['TOT_NUM_IONS']) || $tmp_pep_arr['TOT_NUM_IONS'] == 0){
    $Ions_field = 0;
  }else{
    $Ions_field = $tmp_pep_arr['NUM_MATCHED_IONS']."/". $tmp_pep_arr['TOT_NUM_IONS'];
  }
  $SQL ="INSERT INTO TppPeptide SET ";
  $SQL .= "BandID='".$theProhitsID."',
           Probability='".$tmp_pep_arr['PROBABILITY']."',
           Spectrum='".$tmp_pep_arr['SPECTRUM']."',
           Score1='".$tmp_pep_arr['Score1']."', 
           Score2='".$tmp_pep_arr['Score2']."', 
           Score3='".$tmp_pep_arr['Score3']."', 
           Score4='".$tmp_pep_arr['Score4']."', 
           Score5='".$tmp_pep_arr['Score5']."', 
           Ions='".$Ions_field."', 
           Sequence='".$tmp_pep_arr['PEPTIDE']."', 
           Peptide_prev='".$tmp_pep_arr['PEPTIDE_PREV_AA']."', 
           Peptide_next='".$tmp_pep_arr['PEPTIDE_NEXT_AA']."', 
           Protein='".$tmp_pep_arr['PROTEIN']."', 
           Xpress='".$tmp_pep_arr['Xpress']."', 
           Libra1='".$tmp_pep_arr['Libra1']."', 
           Libra2='".$tmp_pep_arr['Libra2']."', 
           Libra3='".$tmp_pep_arr['Libra3']."', 
           Libra4='".$tmp_pep_arr['Libra4']."', 
           Charge='".$tmp_pep_arr['ASSUMED_CHARGE']."', 
           Precursor_mass='".$tmp_pep_arr['PRECURSOR_NEUTRAL_MASS']."', 
           Calc_mass='".$tmp_pep_arr['CALC_NEUTRAL_PEP_MASS']."', 
           MZratio='".$tmp_pep_arr['PRECURSOR_NEUTRAL_MASS']/$tmp_pep_arr['ASSUMED_CHARGE']."', 
           Retention_time_sec='".$tmp_pep_arr['RETENTION_TIME_SEC']."', 
           Num_tol_term='".$tmp_pep_arr['NUM_TOL_TERM']."', 
           Mised_cleavages='".$tmp_pep_arr['NUM_MISSED_CLEAVAGES']."', 
           Massdiff='".$tmp_pep_arr['MASSDIFF']."', 
           Fval='".$tmp_pep_arr['Fval']."', 
           XmlFile='".$thePepXML_file."'";
           //echo $SQL;exit;
  $hitsDB->check_connection();
  $pep_id = $hitsDB->insert($SQL); 
  
  if($pep_id) return true;
}

//---------------------------------------
function save_tpp_protein($theProhitsID, $thePepXML_file, $theProtXML_file, $theProtein_arr, $theProtein_pep_arr, $isUploaded=''){
  global $hitsDB;
  global $proteinDB;
  global $searchedDB;
  global $searchEngine;
	global $debug;
  
  if($isUploaded){
    global $DECOY_prefix;
  }else{
    global $Conf;
    $DECOY_prefix = $Conf->DECOY_prefix;
  }
  
  $theWellID = 0;
  $theBaitID = 0;
  $theBandID = $theProhitsID;
  $theBaitGeneID = 0;
  $theGeneID = 0;
  $theLocusTag = '';
  $theProteinAcc = '';
  $tmp_searchEngine = '';
  
  //---jp add 2016/07/26-------------------------------------------------------------------
  $protein_tring = $theProtein_arr['PROTEIN_NAME'];
  if(remove_DECOY_frefix($DECOY_prefix, $protein_tring)){
    return false;
  }
  //----------------------------------------------------------------------------------------
  
  if(preg_match('/gn\|(.*)?:(.+)?\|$/',$theProtein_arr['PROTEIN_NAME'], $matches)){
    $theGeneID = $matches[2];
  }
   
  $theProtein_arr['PROTEIN_NAME'] = parse_protein_Acc($theProtein_arr['PROTEIN_NAME']);
  
  $theAccType = get_protein_ID_type($theProtein_arr['PROTEIN_NAME']);
  if(!$theGeneID){
    $theGeneID = get_protein_GeneID($theProtein_arr['PROTEIN_NAME'], $theAccType,  $proteinDB);
  }
  if($theGeneID){
    $SQL = "select LocusTag from Protein_Class where EntrezGeneID='".$theGeneID."'";
    $row = $proteinDB->fetch($SQL);
    if($row){
      $theLocusTag = $row['LocusTag'];
    }
  }
  $theBand_arr = get_band_arr($hitsDB, $theProhitsID);
	 
  if($theBand_arr){
    $theBaitID = $theBand_arr['BaitID'];
    $theBaitGeneID = $theBand_arr['GeneID'];
    $theWellID = $theBand_arr['WellID'];
  }else{
    return false;
  }
  
  $uniq_pep_arr = explode('+', $theProtein_arr['UNIQUE_STRIPPED_PEPTIDES']);

  if($searchEngine == 'MASCOT'){
    $tmp_searchEngine = 'Mascot';
  }else if(strstr($searchEngine,'X! Tandem')){
    $tmp_searchEngine = 'GPM';
  }else{
    $tmp_searchEngine = $searchEngine;
  }
  if($isUploaded){
    $tmp_searchEngine = $tmp_searchEngine ."Uploaded";
  }
  
  $SQL = "INSERT INTO TppProtein SET ";
  //$SQL .= "GROUP_ID='".$theProtein_arr['GROUP_ID']."',";
  
  $SQL .= "WellID='".$theWellID."', 
          BaitID='".$theBaitID."', 
          BandID='".$theBandID."', 
          GeneID='".$theGeneID."', 
          LocusTag='".$theLocusTag."', 
          ProteinAcc='".$theProtein_arr['PROTEIN_NAME']."', 
          AccType='".$theAccType."', 
          ProteinDec='". mysqli_escape_string($hitsDB->link, $theProtein_arr['PROTEIN_DESCRIPTION'])."', 
          INDISTINGUISHABLE_PROTEIN='". $theProtein_arr['INDISTINGUISHABLE_PROTEIN']."', 
          PROBABILITY='".$theProtein_arr['PROBABILITY']."', 
          PERCENT_COVERAGE='".$theProtein_arr['PERCENT_COVERAGE']."', 
          XPRESSRATIO_MEAN='".$theProtein_arr['RATIO_MEAN']."', 
          XPRESSRATIO_STANDARD_DEV='".$theProtein_arr['RATIO_STANDARD_DEV']."', 
          XPRESSRATIO_NUM_PEPTIDES='".$theProtein_arr['RATIO_NUMBER_PEPTIDES']."',
          GROUP_NUMBER_PEPTIDES='".$theProtein_arr['GROUP_NUMBER_PEPTIDES']."',
          UNIQUE_NUMBER_PEPTIDES='".$theProtein_arr['UNIQUE_NUMBER_PEPTIDES']."',
          TOTAL_NUMBER_PEPTIDES='".$theProtein_arr['TOTAL_NUMBER_PEPTIDES']."', 
          PCT_SPECTRUM_IDS='".$theProtein_arr['PCT_SPECTRUM_IDS']."', 
          XmlFile='".$theProtXML_file."',
          SearchEngine='".$tmp_searchEngine."',
          SearchDatabase='".$searchedDB."'";
  
  $prot_id = $hitsDB->insert($SQL); 
  if(!$prot_id) return false; 
  
  $new_total_pep = 0;
  foreach($theProtein_pep_arr as $thePep_arr){ 
    $SQL =  "INSERT INTO TppPeptideGroup SET ";
    $SQL .= "ProteinID='".$prot_id."', 
            Sequence='".$thePep_arr['PEPTIDE_SEQUENCE']."', 
            INITIAL_PROBABILITY='".$thePep_arr['INITIAL_PROBABILITY']."', 
            NSP_ADJUSTED_PROBABILITY='".$thePep_arr['NSP_ADJUSTED_PROBABILITY']."', 
            WEIGHT='".$thePep_arr['WEIGHT']."', 
            IS_NONDEGENERATE='".$thePep_arr['IS_NONDEGENERATE_EVIDENCE']."', 
            N_ENZYMATIC_TERMINI='".$thePep_arr['N_ENZYMATIC_TERMINI']."', 
            N_SIBLING_PEPTIDES='".$thePep_arr['N_SIBLING_PEPTIDES']."', 
            N_SIBLING_PEPTIDES_BIN='".$thePep_arr['N_SIBLING_PEPTIDES_BIN']."', 
            N_INSTANCES='".$thePep_arr['N_INSTANCES']."', 
            EXP_TOT_INSTANCES='".$thePep_arr['EXP_TOT_INSTANCES']."', 
            IS_CONTRIBUTING_EVIDENCE='".$thePep_arr['IS_CONTRIBUTING_EVIDENCE']."',
            CALC_MASS='".(isset($thePep_arr['CALC_NEUTRAL_PEP_MASS'])?$thePep_arr['CALC_NEUTRAL_PEP_MASS']:'')."'";
    if(isset($thePep_arr['CHARGE']) and $thePep_arr['CHARGE']){
      $SQL .= ", CHARGE='".$thePep_arr['CHARGE']."'"; 
    }
    $pep_id = $hitsDB->insert($SQL);
    if($pep_id and $thePepXML_file){ 
      update_TppPeptide_groupid($pep_id, $theProtein_arr, $thePep_arr['PEPTIDE_SEQUENCE'],  $thePepXML_file);
    }
    $new_total_pep += $thePep_arr['N_INSTANCES'];
  }
  $SQL = "update TppProtein SET TOTAL_NUMBER_PEPTIDES='".$new_total_pep."' where ID='".$prot_id."'";
  $hitsDB->update($SQL);
   
  return true;
}

//---------------------------------------
// remove all non-contributing_deidence peptids
function get_hit_unique_pep_num($theProtein_pep_arr){
  $N_count = 0;
  $total_num = count($theProtein_pep_arr);
  for($i = 0; $i< $total_num; $i++){
    if($theProtein_pep_arr[$i]['IS_CONTRIBUTING_EVIDENCE'] == 'N'){
      $N_count++;
    }
  }
  return $total_num - $N_count;
}
//---------------------------------------
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
//----------------------------------------
function empty_prot_arr(){
  $theProtein_arr = array(); 
  $theProtein_arr['INDISTINGUISHABLE_PROTEIN'] = '';
  $theProtein_arr['RATIO_MEAN'] =0;
  $theProtein_arr['RATIO_STANDARD_DEV'] =0;
  $theProtein_arr['RATIO_NUMBER_PEPTIDES'] = 0;
  $theProtein_arr['PROTEIN_DESCRIPTION'] ='';
  return $theProtein_arr;
}
function update_TppPeptide_groupid($thePeptide_groupID, $theProtein_arr, $thePeptide,  $thePepXML){
  global $hitsDB;
  $SQL = "UPDATE `TppPeptide` SET GroupID='$thePeptide_groupID' WHERE 
          Sequence='$thePeptide' and XmlFile='$thePepXML'";;
  if(!$hitsDB->update($SQL . " and Protein='".$theProtein_arr['PROTEIN_NAME']."'")){
    if($theProtein_arr['INDISTINGUISHABLE_PROTEIN']){
      $tmp_protein_arr = explode(';', $theProtein_arr['INDISTINGUISHABLE_PROTEIN']);
      foreach($tmp_protein_arr as $tmp_prot){
        if(trim($tmp_prot)){
          if($hitsDB->update($SQL . " and Protein='".$tmp_prot."'")){
            break;
          }
        }
      }
    }
  }
}

//**********************************************************************************
function parse_peptideProphet($theProhitsID, $tppPepLocalPath, $tppPepRemotePath, $isUploaed=''){
//**********************************************************************************
  global $frm_TPP_PARSE_MIN_PROBABILITY;
  global $searchEngine;
    
  $pepxml_P = new xmlParser(); 
  if(!$pepxml_P->parse($tppPepLocalPath) ){
    write_Log($pepxml_P->error_msg);
    print $pepxml_P->error_msg;
    return false;
  }
  $i = 0;
  $rt = false;
  foreach($pepxml_P->output[0]['child'] as $tmp_arr){
    if(isset($tmp_arr['name'])){
      if($tmp_arr['name'] == 'MSMS_RUN_SUMMARY'){
        $xmlPep_arr = $tmp_arr['child'];
        foreach($xmlPep_arr as $tmp_arr){
          $tmp_pep_arr = empty_pep_arr();
          if($tmp_arr['name'] == 'SEARCH_SUMMARY'){
            if(isset($tmp_arr['attrs']['SEARCH_ENGINE'])){
              if(!$searchEngine){
                $searchEngine = $tmp_arr['attrs']['SEARCH_ENGINE'];
                return 1;
              }  
            }
          }
          if($tmp_arr['name'] == 'SPECTRUM_QUERY' and isset($tmp_arr['child'])){
            
             //$i++;
             //if($i < 6) continue;
             
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
            }
            if($tmp_pep_arr['PROBABILITY'] >= $frm_TPP_PARSE_MIN_PROBABILITY and isset($tmp_pep_arr['PROTEIN'])){
              $rt = save_tpp_peptide($theProhitsID, $tppPepRemotePath,  $tmp_pep_arr);
			  //only save one peptide
              return $rt;
            }
          }
        }
      }
    }
  }//end of pepXML
  return $rt;
}
//*****************************************************************************************************
function parse_proteinProphet($theProhitsID, $tppProtLocalPath, $tppPepRemotePath, $tppProtRemotePath, $isUploaded=''){
//*****************************************************************************************************
  global $searchedDB;
  global $searchEngine;
  global $frm_TPP_PARSE_MIN_PROBABILITY;
    
  if(!$tppProtLocalPath) {
     $msg = "the XML cannot be opened:".$tppProtRemotePath;
     write_Log($msg);
     return false;
  }
  $protxml_P =new xmlParser();
  if(!$protxml_P->parse($tppProtLocalPath) ){
    write_Log($protxml_P->error_msg);
    return false;
  }
  $group_id = 0;
  $searchedDB = '';
  $rt = false;
  foreach($protxml_P->output[0]['child'] as $tmp_arr){
    if(isset($tmp_arr['name'])){
      if($tmp_arr['name'] == 'PROTEIN_SUMMARY_HEADER'){
        $searchedDB = basename($tmp_arr['attrs']['REFERENCE_DATABASE']);
      }
      
      if($tmp_arr['name'] == 'PROTEIN_GROUP'){
        $group_id++;
        foreach($tmp_arr['child'] as $tmp_prot_arr){
          $theProtein_arr = empty_prot_arr();
          $theProtein_pep_arr = array(); 
          $theProtein_arr['GROUP_ID'] = $group_id;
          
          if(isset($tmp_prot_arr['name']) and $tmp_prot_arr['name'] == 'PROTEIN'){
            $theProtein_arr = array_merge($tmp_prot_arr['attrs'], $theProtein_arr);
            if(!isset($theProtein_arr['PERCENT_COVERAGE'])) $theProtein_arr['PERCENT_COVERAGE'] = 0;
            if(!isset($theProtein_arr['PCT_SPECTRUM_IDS'])) $theProtein_arr['PCT_SPECTRUM_IDS'] = 0;
            
            $group_pep_num_counter = 0;
            $uniq_pep_num_counter = 0;
            //$uniq_pep_num_counter = count(explode("+",$tmp_prot_arr['attrs']['UNIQUE_STRIPPED_PEPTIDES']));
            foreach($tmp_prot_arr['child'] as $tmp_prot_pep_arr){
              if($tmp_prot_pep_arr['name'] == 'ANNOTATION'){
                $theProtein_arr['PROTEIN_DESCRIPTION'] = $tmp_prot_pep_arr['attrs']['PROTEIN_DESCRIPTION'];
              }else if($tmp_prot_pep_arr['name'] == 'INDISTINGUISHABLE_PROTEIN'){
                $theProtein_arr['INDISTINGUISHABLE_PROTEIN'] .= parse_protein_Acc($tmp_prot_pep_arr['attrs']['PROTEIN_NAME'])."; ";
              }else if($tmp_prot_pep_arr['name'] == 'ANALYSIS_RESULT' and $tmp_prot_pep_arr['attrs']['ANALYSIS']=='xpress'){
                $theProtein_arr['RATIO_MEAN'] = $tmp_prot_pep_arr['child'][0]['attrs']['RATIO_MEAN'];
                $theProtein_arr['RATIO_STANDARD_DEV'] = $tmp_prot_pep_arr['child'][0]['attrs']['RATIO_STANDARD_DEV'];
                $theProtein_arr['RATIO_NUMBER_PEPTIDES'] = $tmp_prot_pep_arr['child'][0]['attrs']['RATIO_NUMBER_PEPTIDES'];
              }else if($tmp_prot_pep_arr['name'] == 'PEPTIDE'){
                              
                if(isset($tmp_prot_pep_arr['attrs']['IS_CONTRIBUTING_EVIDENCE']) and $tmp_prot_pep_arr['attrs']['IS_CONTRIBUTING_EVIDENCE'] == 'Y') $group_pep_num_counter++;
                if(isset($tmp_prot_pep_arr['child'][0]['name'])){
                  if($tmp_prot_pep_arr['child'][0]['name'] == 'INDISTINGUISHABLE_PEPTIDE'){
                    $count = 0;
                    $attrs_arr = $tmp_prot_pep_arr['attrs'];
                    $attrs_arr['CHARGE'] = '';
                    $attrs_arr['CALC_NEUTRAL_PEP_MASS'] = '';
                    foreach($tmp_prot_pep_arr['child'] as $sub_prot_pep_arr){
                      
                      if($sub_prot_pep_arr['name'] == 'INDISTINGUISHABLE_PEPTIDE'){
                        if(isset($sub_prot_pep_arr['attrs']['CHARGE'])){
                          //$attrs_arr['PEPTIDE_SEQUENCE'] = $sub_prot_pep_arr['attrs']['PEPTIDE_SEQUENCE'];
                          if(!$count) {
                            $attrs_arr['CHARGE'] = '';
                            $attrs_arr['CALC_NEUTRAL_PEP_MASS'] = '';
                          }else{
                            $attrs_arr['CHARGE'] .= '/';
                            $attrs_arr['CALC_NEUTRAL_PEP_MASS'] .= '/';
                          }
                          $attrs_arr['CHARGE'] .= $sub_prot_pep_arr['attrs']['CHARGE'];
                           
                          $attrs_arr['CALC_NEUTRAL_PEP_MASS'] .= (isset($sub_prot_pep_arr['attrs']['CALC_NEUTRAL_PEP_MASS'])?$sub_prot_pep_arr['attrs']['CALC_NEUTRAL_PEP_MASS']:'');
                        }
                        //if(isset($sub_prot_pep_arr['child'][0]['name']) and $sub_prot_pep_arr['child'][0]['name'] == 'MODIFICATION_INFO' and isset($sub_prot_pep_arr['child'][0]['attrs']['MODIFIED_PEPTIDE'])){
                        //  $attrs_arr['PEPTIDE_SEQUENCE'] = $sub_prot_pep_arr['child'][0]['attrs']['MODIFIED_PEPTIDE'];
                        //}
                      }
                      //if(isset($tmp_prot_pep_arr['attrs']['IS_CONTRIBUTING_EVIDENCE']) and $tmp_prot_pep_arr['attrs']['IS_CONTRIBUTING_EVIDENCE'] != 'N') $uniq_pep_num_counter++;
                      //array_push($theProtein_pep_arr, $attrs_arr);
                      $count++;
                    }
                    array_push($theProtein_pep_arr, $attrs_arr);
                  }else{
                    if($tmp_prot_pep_arr['child'][0]['name'] == 'MODIFICATION_INFO'){
                      $tmp_prot_pep_arr['attrs']['PEPTIDE_SEQUENCE'] = $tmp_prot_pep_arr['child'][0]['attrs']['MODIFIED_PEPTIDE'];
                    }
                    //if(isset($tmp_prot_pep_arr['attrs']['IS_CONTRIBUTING_EVIDENCE']) and $tmp_prot_pep_arr['attrs']['IS_CONTRIBUTING_EVIDENCE'] != 'N') $uniq_pep_num_counter++;  
                    array_push($theProtein_pep_arr, $tmp_prot_pep_arr['attrs']);
                  }
                }else{
                  //if(isset($tmp_prot_pep_arr['attrs']['IS_CONTRIBUTING_EVIDENCE']) and $tmp_prot_pep_arr['attrs']['IS_CONTRIBUTING_EVIDENCE'] != 'N') $uniq_pep_num_counter++;                  
                  array_push($theProtein_pep_arr, $tmp_prot_pep_arr['attrs']);
                }
              }
            }
            $theProtein_arr['GROUP_NUMBER_PEPTIDES'] = $group_pep_num_counter;
            $theProtein_arr['UNIQUE_NUMBER_PEPTIDES'] = $group_pep_num_counter;
          }
          if($theProtein_arr and $theProtein_arr['PROBABILITY'] >= $frm_TPP_PARSE_MIN_PROBABILITY){
            if(!is_exist_TPP_hit($theProhitsID, $theProtein_arr['PROTEIN_NAME'], $tppProtRemotePath)){
              $rt = save_tpp_protein($theProhitsID, $tppPepRemotePath, $tppProtRemotePath, $theProtein_arr, $theProtein_pep_arr, $isUploaded);
            }
          }
        }
        //end of the protein group
      }
    } 
  }//end of protXML
  return true;
}
function is_exist_TPP_hit($band_id, $hit_gi, $dataFile, $SearchEngine=''){
  global $hitsDB;
//echo $hitsDB->selected_db_name."<br>";
  $hit_gi = parse_protein_Acc($hit_gi);
  $tmp_file = mysqli_escape_string($hitsDB->link, $dataFile);
  $rt = 0;
  $SQL = "select ID from TppProtein where BandID='$band_id' and ProteinAcc='$hit_gi' and XmlFile='$tmp_file'";
  if($SearchEngine){
    $SQL .= " and SearchEngine='$SearchEngine'";
  }
  //echo "$SQL";exit;
  $hitsDB->fetch($SQL);
  if( count($hitsDB->fetch($SQL)) ){
    $rt = 1;
  }
  return $rt;
}
?>