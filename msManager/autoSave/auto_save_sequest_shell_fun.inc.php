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

function save_sequest_results($SequestResults,$source_well_id, $target_band_id, $Conf, $field_spliter=';;', $exportingParameterStr, $isUploaded=0){
  global $USER;
  global $hitsDB;
  global $proteinDB;
  global $pRedundantGIs;
  global $AccessionType;
  global $data_folder;
  global $no_DECOY;
  
  if($isUploaded){
    global $DECOY_prefix;
  }else{
    $DECOY_prefix = $Conf->DECOY_prefix;
  }
  
 
  if(!$SequestResults or !$target_band_id) return 0;
  $well_id = '';
  $band_id = $target_band_id;
  
  //================ start to read file =========================================
  //all array counter start from [1]
  $HitsIDS = array(); //to store all new inserted hits IDs
  $pNames = array(); //protein names
  $pUnifiedScore = array();
  $pMasses = array(); //protein masses
  $pGIs = array();   //protein GI number
  $pPtype = array();
  $pExpects = array(); //protein Expects
  $pCoverage = array();
  $pIntensitry = array();
  $pPeptideNum = array();
  $pPeptideUniqiqeNum = array();
  $pGeneIDs = array();
  
  $pepQueryNums = array();
  $pepScoreFinal = array();  // $pepScoreFinal[0][0], $pepScoreFinal[0][1], $pepScoreFinal[0][2] for protein 1 peptid 1,2,3 expects
                          // $pepScoreFinal[1][0], $pepScoreFinal[1][1], $pepScoreFinal[1][2] for protein 2 peptid 1,2,3 expects
  $pepTIC = array();
  $pepMaxBP = array();
  $pepFBP = array();
  $pepZBP = array();
  $pepScan = array();
  $pepRetention = array();
  $pepCharge = array();
  $pepPpm = array();
  $pepAmu = array();
  $pepMass_ion = array();
  $pepMZ = array();
  $pepxCorr = array();
  $pepdeltaCn = array();
  $pepSp = array();
  $peprankSp = array();
  $pepIons = array();
  $pepScoreFinal = array();
  $pepProbability = array();
  $pepSequence = array();
  $pepIonFile = array();
  $pepLocation = array();
  $pepModification = array();
  $pepUnifiedScore = array();
  
  if(is_object($USER)){
    $user_ID = $USER->ID;
  }else if(is_array($USER)){
    $user_ID = $USER['ID'];
  }
  
  if($isUploaded){
    $unzip_folder = unzip_file($SequestResults);
  }else{
    $data_folder = STORAGE_FOLDER."Prohits_Data/sequest_search_results";
    if(!_is_dir($data_folder)) _mkdir_path($data_folder);
    $shFileName = basename($SequestResults);
    $shDirName = dirname($SequestResults);      
    $tmp_arr = explode("/", $shDirName);
    $shDirName = '';
    for($i=0; $i<2; $i++){
      if(!$shDirName){
        $shDirName = array_pop($tmp_arr);
      }else{
        $shDirName = array_pop($tmp_arr)."/".$shDirName;
      }
    } 
    $data_folder .= "/".$shDirName;      
    $rt_folder_path = downloadSEQESTout($SequestResults,$shFileName);
    $unzip_folder = unzip_file($rt_folder_path);
    $tmp_arr = explode("=", $exportingParameterStr);
    $exportingParameterStr = $tmp_arr[1];
  }
  if(preg_match("/(.+)?(\/analyst|\/msManager)/i", $_SERVER['SCRIPT_FILENAME'], $matches)){
    $PROHITS_ROOT = $matches[1];
  }else{
    exit;
  }
  $com2 = "php prohits_Sequest_parser.php $unzip_folder $exportingParameterStr";
  $com = "cd $PROHITS_ROOT/SequestParser; $com2;";
  system($com, $retval);
  $parsed_file_name = $unzip_folder.".dat";
  if($retval){
    echo "Parsing files in directory $unzip_folder is failed!";
    exit;
  }
  $sequest_parser_page = trim($parsed_file_name);
  if($isUploaded){
    $SearchEngine = "SEQUESTUploaded"; 
  }else{
    $SearchEngine = "SEQUEST";
  }
  $fd = fopen($sequest_parser_page,"r");
  $i =0;
  if(!$fd){
    echo "Cannot open file $sequest_parser_page!";
    exit;
  }
  
  if(!$isUploaded){
    $parsed_file_name = $SequestResults;
  }
  
  $pNum = 0; //temp checkbox counter
  $hitStart = false; 
  $peptide_start = false;
  $peptide_num = 0;
  $searchedDB = '';
  while (!feof ($fd)) {
      $buffer = trim(fgets($fd, 40960));
      if(!$buffer) continue;
      $buffer = trim($buffer);
      if(strstr($buffer,"Database")){
        $tmp_arr = explode(":", $buffer);
        if(count($tmp_arr) == 2) $searchedDB = trim(str_replace(",", ";", $tmp_arr[1]));
      }
      //find hit start position
      //HitNumber;;ProteinID;;Coverage;;Uniq_peptide;;Peptide;;Score;;Sfavg;;BPsum;;BPavg;;ProteinMass;;Description
      if(preg_match("/^(Hit_[0-9]+)/", $buffer)){
        
        $tmp_array = explode($field_spliter, $buffer);
        if(strstr($tmp_array[1] , '|')){
          $tmp_arr = explode("|", $buffer);
          $protein_id = $tmp_arr[1];
        }else{
          $protein_id = $tmp_array[1];
        }
        
//---jp add 2016/07/26-------------------------------------------------------------
        $protein_tring = trim($tmp_array[1]);
        if(remove_DECOY_frefix($DECOY_prefix, $protein_tring)){
          continue;
        }
//----------------------------------------------------------------------------------
        $pNum++;  //tmp checkbox counter start from 1
        $peptide_start = true; 
        $peptide_num = 0; //peptids num of the hit
//---jp add 2015/03/02--------------------------------------------------------------------
        if(preg_match('/gn\|(.*)?:(.+)?\|$/',trim($tmp_array[1]),$matches)){
          $pGeneIDs[$pNum] = $matches[2];
        } 
//-----------------------------------------------------------------------------------------
        //$protein_type = get_protein_ID_type($protein_id);
        $pPtype[$pNum] = get_protein_ID_type($protein_id); 
        $pGIs[$pNum] = $protein_id;
        $pIntensitry[$pNum] = '';  //sum of spectrum intensity
        $pCoverage[$pNum] = $tmp_array[2];
        $pPeptideUniqiqeNum[$pNum] = $tmp_array[3];  //num uniqe peptide
        $pPeptideNum[$pNum] = $tmp_array[4];  //num peptide
        $pExpects[$pNum] = $tmp_array[5];
        $pMasses[$pNum] = $tmp_array[9];
        $pNames[$pNum] = $tmp_array[10];
        $pUnifiedScore[$pNum] = $tmp_array[11];
        $buffer = trim(fgets($fd, 140960));//get next line
      }
      if(strtoupper($buffer) == '<HR>') {
        $peptide_start = false;
      }   
      if($peptide_start and $buffer){
        $tmp_array = explode($field_spliter, $buffer);
        $pepTIC[$pNum][$peptide_num] = $tmp_array[0];
        $pepMaxBP[$pNum][$peptide_num] = $tmp_array[1];
        $pepFBP[$pNum][$peptide_num] = $tmp_array[2];
        $pepZBP[$pNum][$peptide_num] = $tmp_array[3];
        $pepScan[$pNum][$peptide_num] = $tmp_array[4];
        $pepRetention[$pNum][$peptide_num] = $tmp_array[5];
        $pepCharge[$pNum][$peptide_num] = $tmp_array[6];
        $pepPpm[$pNum][$peptide_num] = $tmp_array[7];
        $pepAmu[$pNum][$peptide_num] = $tmp_array[8];
        $pepMass_ion[$pNum][$peptide_num] = $tmp_array[9];
        $pepMZ[$pNum][$peptide_num] = $pepMass_ion[$pNum][$peptide_num]/$pepCharge[$pNum][$peptide_num];
        $pepxCorr[$pNum][$peptide_num] = $tmp_array[10];
        $pepdeltaCn[$pNum][$peptide_num] = $tmp_array[11];
        $pepSp[$pNum][$peptide_num] = $tmp_array[12];
        $peprankSp[$pNum][$peptide_num] = $tmp_array[13];
        $pepIons[$pNum][$peptide_num] = $tmp_array[14];
        $pepScoreFinal[$pNum][$peptide_num] = $tmp_array[15];
        $pepProbability[$pNum][$peptide_num] = $tmp_array[16];
        $tmp_Sequence_arr = explode(".", $tmp_array[17]);
        $pepSequence[$pNum][$peptide_num] = preg_replace('/[^A-Z]/', '', $tmp_Sequence_arr[1]);
        //$pepSequence[$pNum][$peptide_num] = $tmp_Sequence_arr[1];
        $pepIonFile[$pNum][$peptide_num] = $tmp_array[18];
        $pepLocation[$pNum][$peptide_num] = $tmp_array[19]."-".$tmp_array[20];
        $pepModification[$pNum][$peptide_num] = $tmp_array[21];
        $pepUnifiedScore[$pNum][$peptide_num] = $tmp_array[23];
        $peptide_num++;
      } 
  }
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
       
    for($num=1; $num<=count($pGIs); $num++){
      $pGeneID = '';
      $MW = 0;
      $Hits->ID = 0;
      if($pGIs[$num] and isset($pepSequence[$num][0])){
        //this hit has been selected to save to ProHits
        $pLocusTag = '';
        
//--------------------------------------------------------------------------------------------------        
        if(isset($pGeneIDs[$num])){
          $pGeneID = $pGeneIDs[$num];
        }else{
          $pGeneID = get_protein_GeneID($pGIs[$num], $pPtype[$pNum], $proteinDB);
        }
//-------------------------------------------------------------------------------------------------        
        
        if(!is_exist_hit($band_id,$pGIs[$num], $parsed_file_name, $SearchEngine)){        
          //get the Yeast protein ORFName or NCBI gene LocusTag from Protein database $pGIs[$num]);
          if($pGeneID){
            $SQL = "select LocusTag from Protein_Class where EntrezGeneID='".$pGeneID."'";
            //echo $SQL;
            $row = $proteinDB->fetch($SQL);
            if(count($row)){
              $pLocusTag = $row['LocusTag'];
            }
          }
          $MW = round($pMasses[$num],3);
          
          $tmp_file = mysqli_escape_string($hitsDB->link, $parsed_file_name);
          $SQL ="INSERT INTO Hits SET 
            WellID='$well_id', 
            BaitID='$bait_id', 
            BandID='$band_id',
            GeneID='$pGeneID', 
            LocusTag='$pLocusTag', 
            HitGI='" .$pGIs[$num]."', 
            AccType='" .$pPtype[$num]."', 
            HitName='".addslashes($pNames[$num])."', 
            Expect='".round($pUnifiedScore[$num], 2)."',
            MW='".$pMasses[$num]."',
            Coverage='".$pCoverage[$num]."', 
            Intensity_log='".$pIntensitry[$num]."',
            Pep_num='".$pPeptideNum[$num]."',
            Pep_num_uniqe='".$pPeptideUniqiqeNum[$num]."',
            ResultFile='$tmp_file', 
            SearchDatabase='$searchedDB', 
            DateTime=now(),
            SearchEngine='$SearchEngine', 
            OwnerID='".$user_ID."'";
          $hitsDB->check_connection();
          $hit_id = $hitsDB->insert($SQL); 
          //print_r ($hit_id);
          for($pepNum=0; $pepNum < count($pepSequence[$num]); $pepNum++){
             $tmp_pep_ID = 0;
             if($hit_id){
                $SQL ="INSERT INTO SequestPeptide SET 
                  `HitID`='$hit_id', 
                  `Charge`='".$pepCharge[$num][$pepNum]."',
                  `MZ`='".$pepMZ[$num][$pepNum]."',
                  `MASS`='".$pepMass_ion[$num][$pepNum]."',
                  `Location`='".$pepLocation[$num][$pepNum]."',
                  `Fscore`='".$pepScoreFinal[$num][$pepNum]."',
                  `Expect`='".round($pepUnifiedScore[$num][$pepNum],2)."',
                  `TIC`='".$pepTIC[$num][$pepNum]."',
                  `MaxBP`='".$pepMaxBP[$num][$pepNum]."',
                  `FBP`='".$pepFBP[$num][$pepNum]."',
                  `ZBP`='".$pepZBP[$num][$pepNum]."',
                  `Scan`='".$pepScan[$num][$pepNum]."',
                  `Ppm`='".$pepPpm[$num][$pepNum]."',
                  `Aum`='".$pepAmu[$num][$pepNum]."',
                  `xCorr`='".$pepxCorr[$num][$pepNum]."',
                  `deltaCn`='".$pepdeltaCn[$num][$pepNum]."',
                  `Sp`='".$pepSp[$num][$pepNum]."',
                  `rankSp`='".$peprankSp[$num][$pepNum]."',
                  `Ions`='".$pepIons[$num][$pepNum]."',
                  `Probability`='".$pepProbability[$num][$pepNum]."',
                  `Sequence`='".$pepSequence[$num][$pepNum]."',
                  `IonFile`='".$pepIonFile[$num][$pepNum]."',
                  `Modifications`='".$pepModification[$num][$pepNum]."'";
                $tmp_pep_ID = $hitsDB->insert($SQL);
             }
             //look for same peptide set biger one "RemvedBy=-1"             
             if(!isset($pep_ionFile_expects[$pepIonFile[$num][$pepNum]])){
                $pep_ionFile_expects[$pepIonFile[$num][$pepNum]] = array($tmp_pep_ID, $pepScoreFinal[$num][$pepNum]);
             }else if($tmp_pep_ID){
                $big_expect_pep_ID = $tmp_pep_ID;
                if($pep_ionFile_expects[$pepIonFile[$num][$pepNum]][1] > $pepScoreFinal[$num][$pepNum]){
                   $big_expect_pep_ID = $pep_ionFile_expects[$pepIonFile[$num][$pepNum]][0];
                   $pep_ionFile_expects[$pepIonFile[$num][$pepNum]] = array($tmp_pep_ID, $pepScoreFinal[$num][$pepNum]);
                }
                $SQL = "update Peptide set RemovedBy='-1' where ID='$big_expect_pep_ID'";
                //echo $SQL;
                $hitsDB->update($SQL);
             }
          }//end for loop
        } //end if not saved.
      }//end if -- checkbox has been checked, only checked hits will be saved
    }//end for -- all hits
    //display information  let user check saved records or back to sarch enchine
    //pass objects to function.
    //**************************************
  }//=====================end of inserting ===================================  
  if(count($pGIs)>0){
    $msg = "File parsed: $parsed_file_name";
    write_Log($msg);
    return true;
  }
  return false;
}
//end of main function ********************************************************************************************
//$data_folder ---------the folder which contain the zipped file 
//$tmp_tar_gz_path -----zip file full name

function unzip_file($tmp_tar_gz_path){
  $data_folder = dirname($tmp_tar_gz_path);
  $base_name = basename($tmp_tar_gz_path);
  $tmp_arr = explode(".", $base_name);
  $unzip_folder = $data_folder."/".$tmp_arr[0];
  if(stristr($base_name, '.zip')){
    $sysCall = "cd $data_folder; unzip $tmp_tar_gz_path >tar.log 2>&1";
  }else{
    $sysCall = "cd $data_folder; tar xzfv $tmp_tar_gz_path >tar.log 2>&1";
  }  
  system($sysCall);
  if(system($sysCall) === false){ 
    echo "\nCannot unzip file $tmp_tar_gz_path. Error: return from: $sysCall\nError $? $!\n";
    exit;
  }
  if(!_is_dir($unzip_folder)){
    echo("\nError: return from: $sysCall\nError $? $!\n");
    exit;
  }
  return $unzip_folder;
}

function downloadSEQESTout($datFile,$shFileName){
  global $data_folder;
  $download_from = SEQUEST_IP . SEQUEST_CGI_DIR . "/Prohits_SEQUEST.pl";
  $tmpOut_folder = $shFileName;
  $sysCall = '';
  if(!_is_dir($data_folder)) _mkdir_path($data_folder);
  $rt_folder_path = $data_folder."/".$tmpOut_folder;
  $tmp_tar_gz_path = "$rt_folder_path.tar.gz";
  if(SEQUEST_IP == PROHITS_SERVER_IP){
    $rt_folder_path = $datFile;
  }else{ 
    if(!_is_dir($rt_folder_path)){
      $postData = "SEQUEST_myaction=download&type=out&dir=" . $datFile;
      wget_download_($download_from, $postData, $tmp_tar_gz_path);
      if(_is_file($tmp_tar_gz_path)){
        $unzip_status = unzip_file($tmp_tar_gz_path);
        if(!$unzip_status) exit;
        if(!_is_dir($rt_folder_path)){
          //echo("\nError: return from: $sysCall\nError $? $!\n");
          exit;
        }
      }else{
        echo("\nError: return from: $sysCall\nError $? $!\n");exit;
      }
    }
  }
  return $rt_folder_path;
}

//$data_folder ---------the folder which contain the zipped file 
//$tmp_tar_gz_path -----zip file full name

function wget_download_($download_from,$postData,$out_file_name,$out_to_dir=''){
  $sysCall = '';
  $sysCall .= "wget --post-data=\"$postData\"";
  if($out_to_dir){
    $sysCall .= " --directory-prefix=\"$out_to_dir\"";
  }
  $sysCall .= " --output-document=\"$out_file_name\"";
  $sysCall .= " ". $download_from;
  if(system($sysCall) === false){  
    print ("TPP Error: return from: $sysCall\nError $? $!\n");exit;
  }
}
?>