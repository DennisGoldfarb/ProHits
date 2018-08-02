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

function save_mascot_results($MascotResults,$source_well_id, $target_band_id, $Conf='', $field_spliter=';;', $isUploaded=0){
  global $table;
  global $USER;
  global $hitsDB;
  global $managerDB;
  global $proteinDB;
  global $task_arr;
  global $pAccType;
  
  global $pGIs, $pRedundantGIs,$pNames,$pMasses, $pCoverage;
  global $AccessionType;
  global $SCRIPT_REFERER_DIR;
  
  if($isUploaded){
    global $DECOY_prefix;
  }else{
    $DECOY_prefix = $Conf->DECOY_prefix;
  }
  
  if(!$MascotResults or !$target_band_id) return 0;
  $well_id = '';
  $band_id = $target_band_id;
  $user_ID = 0;
  $file = "$MascotResults";
   
  $resultTableName = $table . "SearchResults";
  if(is_object($USER)){
    $user_ID = $USER->ID;
  }else if(is_array($USER)){
    $user_ID = $USER['ID'];
  }
  //make it saved.
  //peptide_min_size:0;matched_ion_percentage:0;peptide_min_charge:2;matched_ions_group_size:6;matched_ions_num:4;is_modified_peptide:0;peptide_min_score:27;requreBoldRed:1
  //================ start to read file =========================================
  //all array counter start from [1]
  $HitsIDS = array(); //to store all new inserted hits IDs
  $pNames = array(); //protein names
  $pCoverage = array();
  $pMasses = array();
  $pGIs = array();   //protein GI number
  $pDatabases = array(); //protein database
  $pExpects = array(); //protein Expects
  $pRedundantGIs = array(); //mach the same set of peptides
  $pRedundantSubGIs = array(); //mach the subset of those peptides
  $pGeneIDs = array();
  
  
  $pepQueryNums = array();
  $pepExpects = array();  // $pepExpects[0][0], $pepExpects[0][1], $pepExpects[0][2] for protein 1 peptid 1,2,3 expects
                          // $pepExpects[1][0], $pepExpects[1][1], $pepExpects[1][2] for protein 2 peptid 1,2,3 expects
  $pepCharges = array();  // $pepCharges[0][0], $pepCharges[0][1], $pepCharges[0][2] for protein 1 peptid charges
  $pepMass = array();      // $pepMass[0][0],$pepMass[0][1], $pepMass[0][2] for protein 1 peptid 1,2,3 masses
  $pepSequences = array(); // $pepSequence[0][0],$pepSequence[0][1],$pepSequence[0][2], for protein 1 peptid 1,2,3 sequences
  $pepModifications = array();
  $pepStatus = array();
  $pepIonFiles = array();
  $pep_ionFile_scores = array();
  if(!$isUploaded){
    if($Conf->Mascot_SaveScore == 'save all hits'){
      $limited_score = 1;
    }else{
      $limited_score = $Conf->Mascot_SaveScore;
    }
    $validation = $Conf->Mascot_SaveValidation;
    //$include_redundant_peptide = $Conf->include_redundant_peptide;
    if($Conf->Mascot_Other_Value){
      //peptide_min_size:0;matched_ion_percentage:0;peptide_min_charge:2;matched_ions_group_size:6;matched_ions_num:4;is_modified_peptide:0;peptide_min_score:27;requreBoldRed:1
      $tmp_arr = explode(";", $Conf->Mascot_Other_Value);
  	  $other_value_str = '';
      for($i=0;$i<count($tmp_arr);$i++){
        $name_value = explode(":", $tmp_arr[$i]);
        if(count($name_value) < 2) continue;
        $tmp_name = $name_value[0];
        $$tmp_name = $name_value[1];
  	    $other_value_str .= "&" . $tmp_name ."=" .  $name_value[1];
      }
    }
    $host = MASCOT_IP;
    if(defined('MASCOT_IP_OLD') and preg_match("/^\w/", $file, $matches)){
      $host = MASCOT_IP_OLD;
    }

    $queryString  = "&hit_min_score=$limited_score";
    if($Conf->Mascot_Other_Value){
      $queryString  .= $other_value_str;
    }
    /*
    $queryString .= "&peptide_min_size=$peptide_min_size";
    $queryString .= "&peptide_min_score=$peptide_min_score";
    $queryString .= "&matched_ion_percentage=$matched_ion_percentage";
    $queryString .= "&matched_ions_group_size=$matched_ions_group_size";
    $queryString .= "&matched_ions_num=$matched_ions_num";
    //$queryString .= "&include_redundant_peptide=$include_redundant_peptide";
    $queryString .= "&peptide_min_charge=$peptide_min_charge";
    */    
    $queryString .= "&field_spliter=$field_spliter";
    
    $queryString .= "&script_referer_dir=".$SCRIPT_REFERER_DIR;
    
    if(MASCOT_USER and MASCOT_PASSWD){
      $theuser =  htmlentities(urlencode(MASCOT_USER));
      $thepass =  htmlentities(urlencode(MASCOT_PASSWD));
      $queryString .= "&username=".$theuser."&password=".$thepass;
    }
    $cmd = "http://$host".MASCOT_CGI_DIR."/ProhitsMascotParser.pl?file=$file$queryString";
    $timeout = 300000000;
    $old = ini_set('default_socket_timeout', $timeout);
    $fd = @fopen($cmd,"r");
    ini_set('default_socket_timeout', $old);
    stream_set_timeout($fd, $timeout);
    //stream_set_blocking($fd, 0);
    $i =0;
    if(!$fd){
      $msg = "The file (http://$host".MASCOT_CGI_DIR."/ProhitsMascotParser.pl) dose not exsist.\n<br>save_mascot_results function stopped in file auot_save_shell_fun.inc.php. the prohits_parser.pl should be placed in Mascot/cgi/";
      fatal_Error($msg);
      mail(ADMIN_EMAIL, "Mascot Parser error", $msg, "From: prohits_server\r\n"."Reply-To: \r\n");
      exit;
    }
  }else{
    //open your temp file
    $fd = @fopen("$file","r");
    if(!$fd){
      $msg = "The file can not open.";
  	  fatal_Error($msg);
  	  exit;
    }
  }
  
  //get all hits into arrays
  $pNum = 0; //temp checkbox counter
  $hitStart = false;
  $redundant_gi_start = false;
  $redundant_sub_gi_start = false;
  $peptide_start = false;
  $peptide_num = 0;
  $searchedDB = '';
  $instrument = '';
  $tmpPID = '';
  
  while (!feof ($fd)) {    
    $buffer = trim(fgets($fd, 40960));
    if(!$buffer)continue;
    if(strstr($buffer,"cannot open $file")){
      $msg= "mascot dat file ('$file') is missing from mascot data folder.";
      write_Log($msg);
    }
    if(preg_match('/^Instrument\s?type\s*:(.*)$/i', $buffer, $matches)) $instrument = trim($matches[1]);
    if(preg_match('/^Database\s*:([^\s]*)/', $buffer, $matches)){
      $searchedDB .= trim($matches[1]). " ";
    }elseif(preg_match('/^Taxonomy\s*:(.*)\(.*$/i', $buffer, $matches)){
      //$searchedDB .= "-".trim($matches[1]);
    }
    //find hit start position
    //<b>HitNumber;; GInumber;; ProteinMass;; ProteinScore;; PeptidesMached;; ProteinDesc
    if(preg_match("/^(Hit_[0-9]*)/", $buffer) > 0 and !$redundant_gi_start){
      $tmp_array = explode($field_spliter, $buffer);      
      //---jp add 2016/07/26-------------------------------------------------------------------
      $protein_tring = trim($tmp_array[1]);
      if(remove_DECOY_frefix($DECOY_prefix, $protein_tring)){
        continue;
      }
      //----------------------------------------------------------------------------------------
      $pNum++;  //tmp checkbox counter start from 1
      $peptide_start = true;
      $redundant_gi_start = false;
      $redundant_sub_gi_start = false;
      $pRedundantGIs[$pNum] = ''; 
      $pRedundantSubGIs[$pNum] = '';
      $peptide_num = 0; //peptids num of the hit
  
      //---jp add 2015/03/02---------------------------------------------------------------------
      if(preg_match('/gn\|(.*)?:(.+)?\|$/',trim($tmp_array[1]),$matches)){
        $pGeneIDs[$pNum] = $matches[2];
      }
      //-----------------------------------------------------------------------------------------        
      $tmp_pro_arr = explode('|',$tmp_array[1]);
      if(count($tmp_pro_arr)>1 ){
        if(strlen($tmp_pro_arr[1]) > 4){
          $tmp_array[1] = $tmp_pro_arr[1];
        }else if(strlen($tmp_pro_arr[0]) > 4){
          $tmp_array[1] = $tmp_pro_arr[0];
        }
      }
      
      $pAccType[$pNum] = get_protein_ID_type($tmp_array[1]);
	    $pGIs[$pNum] = trim(preg_replace("/sp\||gi\||\|/", "",$tmp_array[1]));
       
      //$pGIs[$pNum] = str_replace('gi|','', $tmp_array[1]);                  //----------$PGIs
      $pMasses[$pNum] = $tmp_array[2]/1000;                                   //----------$pMasses
      $pScores[$pNum] = $tmp_array[3];                                        //----------$pScores
      $pNames[$pNum]  = $tmp_array[5];                                        //----------$pNames
      $pCoverage[$pNum] = $tmp_array[6];
	    
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
    }
    if($peptide_start and $buffer){
    //QueryNumber;;   Observed;;   Mr(expt);;   Mr(calc);;   Delta;;  Miss;; Score;;  Start;; End;; Rank;;   Peptide;; Modification;; Status;; IonFile</b>
      $tmp_array = explode($field_spliter, $buffer);
      $pepQueryNums[$pNum][$peptide_num]= $tmp_array[0];
      $pepMZ[$pNum][$peptide_num]       = $tmp_array[1];
      $pepExpects[$pNum][$peptide_num]  = $tmp_array[2]; 
      $pepCharges[$pNum][$peptide_num]  = round($tmp_array[2]/$tmp_array[1]);
      $pepMass[$pNum][$peptide_num]     = $tmp_array[3]/1000;
      $missed_clg[$pNum][$peptide_num]  = $tmp_array[5];
      $pepScores[$pNum][$peptide_num]   = $tmp_array[6];
      $pepLocatoins[$pNum][$peptide_num]= $tmp_array[7]."--".$tmp_array[8];
      $pepSequences[$pNum][$peptide_num]= $tmp_array[10];
      $pepModifications[$pNum][$peptide_num]= $tmp_array[11];
      $pepStatus[$pNum][$peptide_num]= $tmp_array[12];
      $pepIonFiles[$pNum][$peptide_num]= $tmp_array[13];
      $peptide_num++;
    }
    //redendant proteins
    if($redundant_gi_start and $buffer){
      $tmp_array   = explode($field_spliter, $buffer);
      
      $tmp_pro_arr = explode('|',$tmp_array[1]);
      if(count($tmp_pro_arr)>1){
        if(strlen($tmp_pro_arr[1]) > 4){
          $tmp_array[1] = $tmp_pro_arr[1];
        }else if(strlen($tmp_pro_arr[0]) > 4){
          $tmp_array[1] = $tmp_pro_arr[0];
        }
      }
      
      $tmpPID = trim(preg_replace("/sp\||gi\||\|/", "",$tmp_array[1]));
      if($tmpPID){
        $pRedundantGIs[$pNum] .= $tmpPID."; ";
      }
    }
    if($redundant_sub_gi_start and $buffer){
      $tmp_array   = explode($field_spliter, $buffer);
      $tmp_pro_arr = explode('|',$tmp_array[1]);
      if(count($tmp_pro_arr)>1 ){
        if(strlen($tmp_pro_arr[1]) > 4){
          $tmp_array[1] = $tmp_pro_arr[1];
        }else if(strlen($tmp_pro_arr[0]) > 4){
          $tmp_array[1] = $tmp_pro_arr[0];
        }
      }
      
      $tmpPID = trim(preg_replace("/sp\||gi\||\|/", "",$tmp_array[1]));
      if($tmpPID){
        $pRedundantSubGIs[$pNum] .= $tmpPID.";";
      }
    }
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
    
    //get well id if it exist.
    $SQL = "select ID from PlateWell where BandID='$band_id'"; 
    $well_arr = $hitsDB->fetch($SQL);
    if($well_arr) $well_id = $well_arr['ID'];
    //number of hits should $pcounter - 1
    
    for($num=1; $num<=count($pGIs); $num++){
      $pGeneID = '';
      $MW = 0;
      $Hits->ID = 0;
      $pSequence[$num] = '';
      if($pGIs[$num]){ 
        $pGeneID = '';
        $pLocusTag = '';
        
//----------------------------------------------------------------------------------------        
        if(isset($pGeneIDs[$num])){
          $pGeneID = $pGeneIDs[$num];
        }else{
          $pGeneID = get_GeneID($num);
        } 
//----------------------------------------------------------------------------------------        
        
        if(!is_exist_hit($band_id,$pGIs[$num], $file, "Mascot")){
        //get the Yeast protein ORFName or NCBI gene LocusTag from Protein database $pGIs[$num]);
          if($pGeneID and $pAccType[$num] == "GI"){
            $SQL = "select LocusTag from Protein_Class where EntrezGeneID='".$pGeneID."'";
            $row = $proteinDB->fetch($SQL);
            if(count($row)){
              $pLocusTag = $row['LocusTag'];
            }
          }
          $MW = $pMasses[$num];
          if($isUploaded){
            $AccessionType = '';
            $prot_arr = get_protin_info($pGIs[$num], $AccessionType, $proteinDB);
            
            $pSequence[$num] = $prot_arr['Sequence'];
            if(!$pSequence[$num]){
              $prot_arr = get_protein_from_url($pGIs[$num]);
              if(isset($prot_arr['sequence'])){
                $pSequence[$num] = $prot_arr['sequence'];
              }
            }
            if(!$MW and $pSequence[$num]){
              $MW = calcMass($pSequence[$num]);
              $MW = $MW/1000;
            }
            if($pSequence[$num]){
              $plength = strlen($pSequence[$num]);
              $pCoverage[$num] = round($pCoverage[$num]/$plength, 1);
            }else if($MW){
              $pCoverage[$num] = '';
              $tmp_mw = get_total_pep_mass($pepLocatoins[$num], $pepSequences[$num]);
              $pCoverage[$num] = 100*$tmp_mw/$MW;
            }else{
                $pCoverage[$num] = 0;
            }
          }
          $uniqSeqArr = array();
          for($pepNum=0; $pepNum < count($pepSequences[$num]); $pepNum++){
            if(!in_array($pepSequences[$num][$pepNum], $uniqSeqArr) && $pepStatus[$num][$pepNum] == "RB"){
              array_push($uniqSeqArr, $pepSequences[$num][$pepNum]);
            }
          }
          $searchEngine = '';
          if($isUploaded){
            $file = preg_replace('/tmp$/i', 'dat', $file);
            $searchEngine = 'MascotUploaded';
          }else{
            $searchEngine = 'Mascot';
          }
          $MW = round($MW,2);
          $tmp_file = mysqli_escape_string($hitsDB->link, $file);
         
          $SQL ="INSERT INTO Hits SET 
            WellID='$well_id', 
            BaitID='$bait_id', 
            BandID='$band_id', 
            Instrument='$instrument', 
            GeneID='$pGeneID', 
            LocusTag='$pLocusTag', 
            HitGI='" .$pGIs[$num]."', 
            AccType='".$pAccType[$num]."', 
            HitName='".mysqli_escape_string($hitsDB->link, $pNames[$num])."', 
            Coverage='".$pCoverage[$num]."',
            Pep_num='".count($pepSequences[$num])."',
            Pep_num_uniqe='".count($uniqSeqArr)."',  
            Expect='".$pScores[$num]."',
            MW='$MW',
            RedundantGI='".$pRedundantGIs[$num]."',
            ResultFile='$tmp_file', 
            SearchDatabase='$searchedDB', 
            DateTime=now(),
            SearchEngine='$searchEngine', 
            OwnerID='".$user_ID."'";
          //echo "$SQL<br>\n";
          $hitsDB->check_connection();
          $hit_id = $hitsDB->insert($SQL);
          for($pepNum=0; $pepNum < count($pepSequences[$num]); $pepNum++){
            $tmp_pep_ID = 0;
            if($hit_id){
              $SQL ="INSERT INTO Peptide SET 
                    HitID='$hit_id', 
                    Charge='".$pepCharges[$num][$pepNum]."', 
                    MZ='".$pepMZ[$num][$pepNum]."', 
                    MASS='".$pepMass[$num][$pepNum]."', 
                    Location='".$pepLocatoins[$num][$pepNum]."', 
                    Expect='".$pepScores[$num][$pepNum]."', 
                    Sequence='".trim($pepSequences[$num][$pepNum])."',
                    IonFile='".$pepIonFiles[$num][$pepNum]."',
                    Status='".$pepStatus[$num][$pepNum]."',
                    Modifications='".$pepModifications[$num][$pepNum]."', 
                    Miss='".$missed_clg[$num][$pepNum]."'";
                
                //echo $SQL."<br>\n";
                $tmp_pep_ID = $hitsDB->insert($SQL);
             }
             
             //look for same mz peptide set smaller one "RemvedBy=-1"
             if(!isset($pep_ionFile_scores[$pepIonFiles[$num][$pepNum]])){
                $pep_ionFile_scores[$pepIonFiles[$num][$pepNum]] = array($tmp_pep_ID, $pepScores[$num][$pepNum]);
             }else if($tmp_pep_ID and 0){
                $small_score_pep_ID = $tmp_pep_ID;
                if($pep_ionFile_scores[$pepIonFiles[$num][$pepNum]][1] < $pepScores[$num][$pepNum]){
                   $small_score_pep_ID = $pep_ionFile_scores[$pepIonFiles[$num][$pepNum]][0];
                   $pep_ionFile_scores[$pepIonFiles[$num][$pepNum]] = array($tmp_pep_ID, $pepScores[$num][$pepNum]);
                }
                $SQL = "update Peptide set RemovedBy='-1' where ID='$small_score_pep_ID'";
                //echo $SQL;
                $hitsDB->update($SQL);
             }
          }//end for loop
        } //end if not saved.
      }//end if -- checkbox has been checked, only checked hits will be saved
    }//end for -- all hits
  }//=====================end of inserting ===================================
  if(count($pGIs)>0){
    $msg = "File parsed: $MascotResults";
    write_Log($msg);
    return true;
  }
  return false;
}
//end of main function ********************************************************************************************

//------------------------
//if the specie is human it will 
// use protein gi to compare and make redundant gis
// get the GeneID for the group hits
// this script should be modified to use one query to get all GeneID. e.g. GI in (112,234,22344) ordery by EntreGeneID desc
//------------------------
function get_GeneID($num){
  global $pGIs, $pRedundantGIs,$pNames,$pMasses,$pAccType;
  global $proteinDB;
  $redundant_str = '';
  $geneID = '';
  //--------------------------------------------------------
  //$geneID = find_gene_id($pAccType[$num], $pGIs[$num]);
  $geneID = get_protein_GeneID($pGIs[$num], $pAccType[$num], $proteinDB);
   
  //-------------------------------------------------------
  if($geneID){
    return $geneID;
  }elseif($pRedundantGIs[$num]){
    $tmp_arr = explode(';', $pRedundantGIs[$num]);
    for($i = 0; $i < count($tmp_arr) - 1; $i++){
      
      $tmpAccType = get_protein_ID_type($tmp_arr[$i]);
      $tmpProteinID = trim(preg_replace("/sp\||gi\||\|/", "",$tmp_arr[$i]));
      $prot_arr = get_protin_info($tmpProteinID, $tmpAccType, $proteinDB, 'geneNotNull');
      if($prot_arr){
        $pNames[$num] = $prot_arr['Description'];
        $mProteinID = $pGIs[$num];
        $pGIs[$num] = $tmpProteinID;
        $tmp_arr[$i] = $mProteinID;
        $pRedundantGIs[$num] = implode("; ", $tmp_arr);
        $geneID = $prot_arr['EntrezGeneID'];
        if($prot_arr['Sequence']){
          $pMasses[$num] = calcMass($prot_arr['Sequence']);
        }else{
          $pMasses[$num] = '';
        }
        break;
      }
    }//end for
  }
  return $geneID;
}//end function

//---------------------------------
//check if the hit is in hits table
//---------------------------------
function is_exist_hit($band_id, $hit_gi, $dataFile, $SearchEngine=''){
  global $hitsDB;
//echo $hitsDB->selected_db_name."<br>";
  $tmp_file = mysqli_escape_string($hitsDB->link, $dataFile);
  $rt = 0;
  $SQL = "select ID from Hits where BandID='$band_id' and HitGI='$hit_gi' and ResultFile='$tmp_file'";
  if($SearchEngine){
    $SQL .= " and SearchEngine='$SearchEngine'";
  }
  //echo "\$SQL=$SQL";exit;
  $hitsDB->fetch($SQL);
  if( count($hitsDB->fetch($SQL)) ){
    $rt = 1;
  }
  return $rt;
}

// ---- -----------------
//calExpect(string str)
//retrun float
//-----------------------

function calExpect($str){
  $str = trim($str);
  $expect = str_replace('?0','e',$str);
  return $expect; //retrun 2.3e-25 string
}
//-------------------------
// get all peptides mass and remove the overlap pars
//-------------------------

function get_total_pep_mass($loc_arr, $seq_arr){
  $AA_arr = array();
  for($pepNum=0; $pepNum < count($loc_arr); $pepNum++){
    $loc = $loc_arr[$pepNum];
    $seq = $seq_arr[$pepNum];
    $tmp_loc = explode("--", $loc);
    $nr = $tmp_loc[0];
    $n = 0;
    while (isset($seq{$n})) {
      $AA_arr[$nr] = $seq{$n};
      $nr++;
      $n++;
    }
  }
  $theStr = implode("", $AA_arr);
  return calcMass($theStr)/1000;
}
?>