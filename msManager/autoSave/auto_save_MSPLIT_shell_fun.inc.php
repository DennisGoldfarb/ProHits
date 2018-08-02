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

$pGIs = array();
$pRedundantGIs= array();
$pNames= array();
$pCoverage = array();
$pMasses= array();
$pAccType = array();

function save_MSPLIT_results($MSPLITResults, $target_band_id, $field_spliter=';;', $isUploaded=0, $searchEngine='MSPLIT', $the_pepXML=''){
  global $table;
  global $task_ID;
  global $USER;
  global $hitsDB;
  global $managerDB;
  global $proteinDB;
  global $task_arr;
  global $pAccType;
  global $pGIs, $pRedundantGIs,$pNames,$pMasses, $pCoverage;
  global $AccessionType;
  global $SCRIPT_REFERER_DIR;
  global $gpm_ip;
  global $searchedDB;
  
  if(!$MSPLITResults or !$target_band_id){
    $msg = "No results file or No target band ID ";
    write_Log($msg);
    return false;
  }
  $well_id = '';
  $band_id = $target_band_id;
  $user_ID = 0;
  $ResultFile = "$MSPLITResults";
  $sql = "SELECT E.TaxID, E.BaitID, E.ProjectID FROM Experiment E, Band B WHERE E.ID = B.ExpID and B.ID='$band_id'";
  $tmp_arr = $hitsDB->fetch($sql);
  
  if(!$tmp_arr){
    $msg = "$sql return empty array";
    write_Log($msg);
    return false;
  }
  $pTaxID = $tmp_arr['TaxID'];
  $bait_id = $tmp_arr['BaitID'];
  $project_id = $tmp_arr['ProjectID'];
  $instrument = $table;
  
  if($isUploaded){
    $file = preg_replace('/tmp$/i', 'dat', $ResultFile);
    $searchEngine = $searchEngine.'Uploaded';
  }else{
    $file = $ResultFile;
    //$http_gpm_cgi_dir = "http://" . $gpm_ip . GPM_CGI_DIR;
    //$file = $http_gpm_cgi_dir . "/Prohits_TPP2.pl?tpp_myaction=downloadTppXML&fileName=$ResultFile";
  }
  $Taske_table = $table.'SearchTasks';
  if(preg_match("/Database=(.+)?;*/", $task_arr['SearchEngines'],$matches)){
    $searchedDB = $matches[1];
  }
  if(is_object($USER)){
    $user_ID = $USER->ID;
  }else if(is_array($USER)){
    $user_ID = $USER['ID'];
  }
  $peptide_start = false;
  $hits_arr = array();
  $peptides_arr = array();
  
  $fd = fopen($file,"r");
  if(!$fd){
    $msg = "The $file can not open.";
    fatal_Error($msg);
    return false;
  }
  
  if(!$the_pepXML){
    $the_pepXML = $ResultFile;
  }
  
  $hit_id = '';
  $tmp_pep_ID = '';
  
  while(!feof ($fd)){    
    $buffer = trim(fgets($fd, 40960));
    if(preg_match("/^(Hit_[0-9]*)/", $buffer, $hit_matches)){
      $peptide_start = true;
      $tmp_array_hits = explode($field_spliter, $buffer);
      $hits_combine = array_combine($hits_key_arr, $tmp_array_hits);
      
      //--------------------------------------------------------------------------------------
      //add select check function here.
      $SQL ="SELECT `ID` FROM `Hits_GeneLevel` 
            WHERE `BandID`='$band_id' 
            AND `GeneID`='".$hits_combine['GeneID']."' 
            AND `ResultFile`='".mysqli_escape_string($hitsDB->link,$the_pepXML)."'
            AND `SearchEngine`='$searchEngine'";
      $hitsDB->check_connection();
      $exist_hit = $hitsDB->fetch($SQL);
      if($exist_hit){
        continue;
      }
      //--------------------------------------------------------------------------------------
      
      $SQL ="INSERT INTO Hits_GeneLevel SET 
            WellID='$well_id', 
            BaitID='$bait_id', 
            BandID='$band_id', 
            Instrument='$instrument', 
            GeneID='".$hits_combine['GeneID']."',
            GeneName='" .$hits_combine['Gene']."',
            SpectralCount='".$hits_combine['SpectralCount']."',
            `Unique`='".$hits_combine['Unique']."',
            Subsumed='".$hits_combine['Subsumed']."',
            ResultFile='".mysqli_escape_string($hitsDB->link,$the_pepXML)."', 
            SearchDatabase='$searchedDB', 
            DateTime=now(),
            SearchEngine='$searchEngine', 
            OwnerID='".$user_ID."'";
  //echo "$SQL<br>\n";
        $hitsDB->check_connection();
        $hit_id = $hitsDB->insert($SQL);
        
      //-------------------------------------------------------------------------------------
      $buffer = trim(fgets($fd, 40960));//get next line
    }elseif(!trim($buffer)) {
      $peptide_start = false;
      $hit_id = '';
    }elseif(preg_match("/^HitNumber;;Gene;;GeneID;;SpectralCount;;Unique;;Subsumed/", $buffer)){
      $hits_key_arr = explode(";;",$buffer);
    }elseif(preg_match("/^Peptide;;SpectralCount;;IsUnique/", $buffer)){
      $peptide_key_arr = explode(";;",$buffer);
    }
    
    if($peptide_start and $buffer){
      $tmp_array_peptide = explode($field_spliter, $buffer);
      if(count($tmp_array_peptide) == 2){
        $tmp_array_peptide[] = '';
      }
      $peptide_combine = array_combine($peptide_key_arr, $tmp_array_peptide);
      if($hit_id){
         $SQL ="INSERT INTO Peptide_GeneLevel SET 
              HitID='$hit_id',
              Sequence='".$peptide_combine['Peptide']."', 
              SpectralCount='".$peptide_combine['SpectralCount']."',
              IsUnique='".$peptide_combine['IsUnique']."'
              ";
         $tmp_pep_ID = $hitsDB->insert($SQL);
      }
    }    
  }//======================end of file reading================================
  fclose($fd);
  if($tmp_pep_ID){
    $msg = "File parsed: $MSPLITResults";
    write_Log($msg);
    return true;
  }
  return false;
}
?>