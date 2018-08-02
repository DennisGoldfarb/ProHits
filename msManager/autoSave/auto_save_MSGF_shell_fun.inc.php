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
$pMasses= array();
$AccessionType = "GI";

function save_MSGF_results($MSGFResults,$target_band_id, $isUploaded=0){
  global $USER;
  global $hitsDB;
  global $proteinDB;
  global $pRedundantGIs;
  global $AccessionType;
  
  if(!$MSGFResults or !$target_band_id) return 0;
  $well_id = '';
  $band_id = $target_band_id;
  
  //================ start to read file =========================================
  //all array counter start from [1]
  $HitsIDS = array(); //to store all new inserted hits IDs
  $pNames = array(); //protein names
  $pMasses = array(); //protein masses
  $pGIs = array();   //protein GI number
  $pExpects = array(); //protein Expects
  $pCoverage = array();
  $pIntensitry = array();
  $pPeptideNum = array();
  $pPeptideUniqiqeNum = array();
  
  $pepQueryNums = array();
  $pepExpects = array();  // $pepExpects[0][0], $pepExpects[0][1], $pepExpects[0][2] for protein 1 peptid 1,2,3 expects
                          // $pepExpects[1][0], $pepExpects[1][1], $pepExpects[1][2] for protein 2 peptid 1,2,3 expects
  $pepCharges = array();  // $pepCharges[0][0], $pepCharges[0][1], $pepCharges[0][2] for protein 1 peptid charges
  $pepMass = array();      // $pepMass[0][0],$pepMass[0][1], $pepMass[0][2] for protein 1 peptid 1,2,3 masses
  $pepSequences = array(); // $pepSequence[0][0],$pepSequence[0][1],$pepSequence[0][2], for protein 1 peptid 1,2,3 sequences
  $pepIonFiles = array();
  $pepLocatoins = array();
  $pepIntensities = array();
  $pepModifications = array();
  $pepIonFiles = array();
  $pep_ionFile_scores = array();
  
   
  
  if(is_object($USER)){
    $user_ID = $USER->ID;
  }else if(is_array($USER)){
    $user_ID = $USER['ID'];
  }
  $file = $MSGFResults;
  if($isUploaded){
    $SearchEngine = "MSGFUploaded";
	  $fd = fopen($file,"r");
  }else{
    //$timeout = 300000000;
    //$fd = fopen($gpm_parser_page,"r");
    //ini_set('default_socket_timeout', $old);
    //stream_set_timeout($fd, $timeout);
    $SearchEngine = "MSGF";
  }
  
  $i =0;
  if(!$fd){
    $msg =  "Cannot open MSGF file $MSGFResults";
    fatal_Error($msg);
    exit;
  } 
   
  //get all hits into arrays
  $pNum = 0; //temp checkbox counter
  $hitStart = false; 
  $file_start = false;
  $peptide_num = 0;
  $searchedDB = '';
  //while (!feof ($fd) and !$endFile) {
  while (!feof ($fd)) {
      $buffer = trim(fgets($fd, 40960));
      if(!$buffer) continue; 
       
      //Protein,Hits,ProteinID,Comment,Peptides,Modified,
      //gi|106049528|ref|NP_001035806.1|,1959,gi|106049528,gi|106049528|ref|NP_001035806.1|,176,71,if(preg_match("/^(Hit_[0-9]*)/", $buffer) > 0){
      //echo $buffer."\n";
      $tmp_array      = preg_split("/,|\t/", $buffer, 6);
       
      if(!$file_start){
        if($tmp_array[0] != 'Protein' or
           $tmp_array[1] != 'Hits'    or
           $tmp_array[2] != 'ProteinID' or
           $tmp_array[3] != 'Comment'  or
           $tmp_array[4] != 'Peptides' 
           ){
          $msg =  "It is not MS-GF group_by_protein file. The file format should be 'Protein,	Hits,	ProteinID,	Comment,	Peptides,	Modified'";
          fatal_Error($msg);
          exit;
        }else{
          $file_start = true;
          continue; 
        }
      }
      
      $pNum++;  //tmp checkbox counter start from 1
      $pGIs[$pNum]    = $tmp_array[2];
      if(strstr($pGIs[$pNum] , 'gi')){
        $pGIs[$pNum]    = preg_replace('/[^0-9]/','', $pGIs[$pNum]);
      }
      $pPeptideUniqiqeNum[$pNum] = $tmp_array[4];  //num uniqe peptide
      $pPeptideNum[$pNum] = $tmp_array[1];  //num peptide
      $pNames[$pNum] = $tmp_array[3];
       
       
  }//======================end of file reading================================
  fclose($fd);
   
  //====================== insert into database ==============================
  if(count($pGIs)){
    //get bait id of the well and bait species.
    $sql = "SELECT E.TaxID, E.BaitID, E.ProjectID FROM Experiment E, Band B WHERE E.ID = B.ExpID and B.ID='$band_id'";
    
    $tmp_arr = $hitsDB->fetch($sql);
    $pTaxID = $tmp_arr['TaxID'];
    $bait_id = $tmp_arr['BaitID'];
    $project_id = $tmp_arr['ProjectID'];
    $bait_GeneID = '';
    
     
    $sql = "SELECT GeneID from Bait where ID='$bait_id'";
    $tmp_arr = $hitsDB->fetch($sql);
    if(count($tmp_arr)){
      $bait_GeneID = $tmp_arr['GeneID'];
    }
    for($num=1; $num<=count($pGIs); $num++){
      $pGeneID = '';
      $MW = 0;
      $Hits->ID = 0;
      
       
      $thisAccessionType = get_protein_ID_type($pGIs[$num]);
      
       
      if($pGIs[$num]){
        //this hit has been selected to save to ProHits
        $pLocusTag = ''; 
        $pGeneID = get_protein_GeneID($pGIs[$num], $thisAccessionType, $proteinDB);
         
        if( !is_exist_hit($band_id,$pGIs[$num], $file, $SearchEngine)){
          //get the Yeast protein ORFName or NCBI gene LocusTag from Protein database $pGIs[$num]);
          if($pGeneID){
            $SQL = "select LocusTag from Protein_Class where EntrezGeneID='".$pGeneID."'";
            //echo $SQL;
            $row = $proteinDB->fetch($SQL);
            if(count($row)){
              $pLocusTag = $row['LocusTag'];
            }
          }
           
          $tmp_file = mysqli_real_escape_string($hitsDB->link, $file);
          $SQL ="INSERT INTO Hits SET
            BaitID='$bait_id', 
            BandID='$band_id',
            GeneID='$pGeneID', 
            LocusTag='$pLocusTag', 
            HitGI='" .$pGIs[$num]."', 
            AccType='" .$thisAccessionType."', 
            HitName='".mysqli_real_escape_string($hitsDB->link, $pNames[$num])."', 
             
            Pep_num='".$pPeptideNum[$num]."',
            Pep_num_uniqe='".$pPeptideUniqiqeNum[$num]."',
            ResultFile='$tmp_file', 
            DateTime=now(),
            SearchEngine='$SearchEngine', 
            OwnerID='".$user_ID."'";
            
          $hitsDB->check_connection();
          $hit_id = $hitsDB->insert($SQL); 
          /*
          //print_r ($hit_id);
          for($pepNum=0; $pepNum < count($pepSequences[$num]); $pepNum++){
             $tmp_pep_ID = 0;
             if($hit_id){
                $SQL ="INSERT INTO Peptide SET 
                    HitID='$hit_id', 
                    Charge='".$pepCharges[$num][$pepNum]."',
                    MASS='".$pepMass[$num][$pepNum]."', 
                    Location='".$pepLocatoins[$num][$pepNum]."', 
                    Expect2='".$pepExpects[$num][$pepNum]."', 
                    Intensity_log='".$pepIntensities[$num][$pepNum]."', 
                    Sequence='".trim($pepSequences[$num][$pepNum])."',
                    Modifications='".trim($pepModifications[$num][$pepNum])."',
                    IonFile='".$pepIonFiles[$num][$pepNum]."'";
                $tmp_pep_ID = $hitsDB->insert($SQL);
             }
             //look for same peptide set biger one "RemvedBy=-1"
             if(!isset($pep_ionFile_expects[$pepIonFiles[$num][$pepNum]])){
                $pep_ionFile_expects[$pepIonFiles[$num][$pepNum]] = array($tmp_pep_ID, $pepExpects[$num][$pepNum]);
             }else if($tmp_pep_ID){
                $big_expect_pep_ID = $tmp_pep_ID;
                if($pep_ionFile_expects[$pepIonFiles[$num][$pepNum]][1] > $pepExpects[$num][$pepNum]){
                   $big_expect_pep_ID = $pep_ionFile_expects[$pepIonFiles[$num][$pepNum]][0];
                   $pep_ionFile_expects[$pepIonFiles[$num][$pepNum]] = array($tmp_pep_ID, $pepExpects[$num][$pepNum]);
                }
                $SQL = "update Peptide set RemovedBy='-1' where ID='$big_expect_pep_ID'";
                //echo $SQL;
                $hitsDB->update($SQL);
             }
          }//end for loop
          */
        } //end if not saved.
      }//end if -- checkbox has been checked, only checked hits will be saved
    }//end for -- all hits
    //display information  let user check saved records or back to sarch enchine
    //pass objects to function.
    //**************************************
  }//=====================end of inserting ===================================
  if(count($pGIs)>0){
   $msg = "File parsed: $MSGFResults";
    write_Log($msg);
    return true;
  }
  return false;
}
//end of main function ********************************************************************************************


//-----------------------
//check if the hit is in 
//database
//-----------------------
function is_exsist_MSGF_hit($band_id,$proteinKey, $dataFile){
  global $hitsDB;
  $rt = 0;
  $tmp_file = mysqli_real_escape_string($hitsDB->link, $dataFile);
  $SQL = "select ID from Hits where BandID='$band_id' and HitGI='$proteinKey' and ResultFile='$tmp_file' and SearchEngine='GPM'";
  //echo $SQL;
  $hitsDB->fetch($SQL);
  if( count($hitsDB->fetch($SQL)) ){
    $rt = 1;
  }
  return $rt;
}//end function
//---------------------------------------------
//return a parameter from a html string
//----------------------------------------------
function parse_MSGF_parameter($prt_name, $html_str){
  //<B>protein, taxon:  <font color=red>yeast</font></B>
  if(!$prt_name or !$html_str) return '';
  $html_str = preg_replace("/($prt_name)|(<\/?)(\w+)([^>]*>)/i",  "" ,$html_str);
  $html_str = trim($html_str);
  return $html_str;
}
?>