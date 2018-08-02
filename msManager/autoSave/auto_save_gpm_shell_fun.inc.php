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

/**************************************************************************\
Author: Frank Liu
Date:   2004-02-29
Description: 
   1. this a included file in a shell php script auto_save_shell.php.
   2. It well process a GPM result file ( GPM_parser.pl) to save hit into a target DB.
   3. some information should be passed to the function.
      a. GPM results file = 'F:/gpm/archive/GPM00300000104.xml'
      b. A Conf object which contains :
        var $table;
        var $link;
        
        var $ID;
        var $TaskID;
        var $GPM_SaveScore;
        var $GPM_SaveValidation;
        var $Status;
        var $SaveBy;
        var $SetDate;
        var $GPM_SaveWell_str;
        var $GPM_SaveWell_str;
        var $GPM_Other_Value;
        var $GPM_Value;
        
        var $count;
  
      c. target well array $target_band_id = array(well_ID, band_ID)
      d. MsWell ID - source_well_id
   4. default protein accession type is GI. It will be changed based on the searched
      result file. It will be used in Proteins: Proetein_Accession.
      cgi/GPM_parser.pl file should be modified and passed back the value.
\**************************************************************************/
//error_reporting(E_ALL ^ E_NOTICE);


$pGIs = array();
$pRedundantGIs= array();
$pNames= array();
$pMasses= array();
$AccessionType = "GI";

function save_gpm_results($GPMResults,$source_well_id, $target_band_id, $Conf, $field_spliter=';;', $isUploaded=0){
  global $USER;
  global $hitsDB;
  global $proteinDB;
  global $pRedundantGIs;
  global $AccessionType;
  global $prohits_server_name;
  global $gpm_ip;
  
  
  if($isUploaded){
    global $DECOY_prefix;
  }else{
    $DECOY_prefix = $Conf->DECOY_prefix;
  }
  
  if(!$GPMResults or !$target_band_id) return 0;
  $well_id = '';
  $band_id = $target_band_id;
  
  
  
  //$Conf->GPM_Value
  //echo $frm_targetDB;exit;
  //echo $frm_score = $Conf->GPM_SaveScore;
  //echo $frm_peptide_validation = $Conf->SaveValidation;
  
  //make it saved.
  //peptide_min_size:0;matched_ion_percentage:0;peptide_min_charge:2;matched_ions_group_size:6;matched_ions_num:4;is_modified_peptide:0;peptide_min_score:27;requreBoldRed:1
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
  $pGeneIDs = array();
  
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
  
  if($isUploaded){
    $subject = $GPMResults;
    $tmp_arr = explode(" ", $subject);
    $file = $tmp_arr[0];
    if(basename($_SERVER['SCRIPT_FILENAME']) == 'auto_save_shell.php'){
      $PROHITS_ROOT = str_replace('/msManager/autoSave','',dirname($_SERVER['SCRIPT_FILENAME']));
      $GPMResults = dirname(GPM_CGI_PATH).$GPMResults;
    }else{
      $PROHITS_ROOT = str_replace('/analyst','',dirname($_SERVER['SCRIPT_FILENAME']));
    }    
    $com2 = "php prohits_GPM_parser.php $GPMResults";
    $com = "cd $PROHITS_ROOT/GPMParser; $com2;";
     
    $parsed_file_name = system($com, $retval);
    if($retval){
      echo "Parsing file $GPMResults is failed!";
      exit;
    }
    $tmp_dir_name = dirname($file); 
    $tmp_txt_name = basename($file, ".xml").".txt";
    $gpm_parser_page = $tmp_dir_name."/".$tmp_txt_name;
    $SearchEngine = "GPMUploaded";
	  $fd = fopen($gpm_parser_page,"r");
  }else{
    $timeout = 300000000;
    $old = ini_set('default_socket_timeout', $timeout);
    $host = $gpm_ip;
    if($host == 'localhost'){
      $host = $prohits_server_name;
    }
    
    $file = "$GPMResults"; 
    
    $queryString = "&field_spliter=$field_spliter&" . str_replace(',', '&', $Conf->GPM_Value);
    $gpm_parser_page = "http://$host".GPM_CGI_DIR."/prohits_parser.pl?path=$file$queryString";
    
     
    $fd = fopen($gpm_parser_page,"r");
    ini_set('default_socket_timeout', $old);
    stream_set_timeout($fd, $timeout);
    $SearchEngine = "GPM";
  }
  
  $i =0;
  if(!$fd){
    $msg =  "The file (http://$host".GPM_CGI_DIR."/prohits_parser.pl) dose not exist.
    \n<br>save_GPM_results function stopped in file auto_save_shell_fun.inc.php. the prohits_parser.pl should be placed in GPM/cgi/.
    If the prohits_parser.pl is in /GPM/cgi/ folder, you still get the error message. 
    The Prohits server default_socket_timeout should be increased in php.ini file.";
    fatal_Error($msg);
    mail(ADMIN_EMAIL, "GPM Parser error", $msg, "From: prohits_server\r\n"."Reply-To: \r\n");
    exit;
  } 
  //get all hits into arrays
  $pNum = 0; //temp checkbox counter
  $hitStart = false; 
  $peptide_start = false;
  $peptide_num = 0;
  $searchedDB = '';
  //while (!feof ($fd) and !$endFile) {
  while (!feof ($fd)) {
      $buffer = trim(fgets($fd, 40960));
      if(!$buffer) continue; 
      if(strstr($buffer,"could not be found in the archive")){
        $msg = "GPM dat file ('$file') is missing from GPM archive folder.";
        write_Log($msg);
      }
      //get searched database
      if(strstr($buffer,"<B>protein, taxon:")){
        $searchedDB = parse_gpm_parameter("protein, taxon:", $buffer);
      }
      //find hit start position
      //<b>HitNumber;; Protein: Identifier;;log(I)(sum of spectrum intensity);;rI(num peptide);;num uniqe peptide;;Coverage%;;log(e)(expect);;pI;;Mr(kDa);;Description
      if(preg_match("/^(Hit_[0-9]*)/", $buffer) > 0){
        //echo $buffer."\n";
        $tmp_array = explode($field_spliter, $buffer, 10);
        
        //---jp add 2016/07/26-------------------------------------------------------------
        $protein_tring = trim($tmp_array[1]);
        if(remove_DECOY_frefix($DECOY_prefix, $protein_tring)){
          continue;
        }
        //----------------------------------------------------------------------------------
        $pNum++;  //tmp checkbox counter start from 1
        $peptide_start = true; 
        $peptide_num = 0; //peptids num of the hit
        
        $pGIs[$pNum]    = $tmp_array[1];
        if(preg_match("/^gi\|([0-9]+)/", $pGIs[$pNum] , $matches)){
          $pGIs[$pNum]    = $matches[1];
        }else{
          $id_arr = explode("|", $tmp_array[1]);
          if(count($id_arr) and strlen($id_arr[0])<4){
            $pGIs[$pNum] = $id_arr[1];
          }else{
            $pGIs[$pNum] = $id_arr[0];
          }
        }
      
//---jp add 2015/03/02---------------------------------------------------------------------
        if(preg_match('/gn\|(.*)?:(.+)?\|$/',trim($tmp_array[1]),$matches)){
          $pGeneIDs[$pNum] = $matches[2];
        } 
//----------------------------------------------------------------------------------------- 
        //print_r($pGIs);
        //print_r($pGeneIDs);
        //exit;
        
        $pIntensitry[$pNum] = $tmp_array[2];  //sum of spectrum intensity
        $pPeptideUniqiqeNum[$pNum] = $tmp_array[3];  //num uniqe peptide
        $pPeptideNum[$pNum] = $tmp_array[4];  //num peptide
        $pCoverage[$pNum] = $tmp_array[5];
        $pExpects[$pNum] = $tmp_array[6];
        //$tmp_array[7];  //pritein pI
        $pMasses[$pNum] = $tmp_array[8];
        $pNames[$pNum] = $tmp_array[9];
        
        $buffer = trim(fgets($fd, 140960));//get next line
     
      }
      if(strtoupper($buffer) == '<HR>') {
        $peptide_start = false;
      }
      
      if($peptide_start and $buffer){
        // spectrum;;log(e)(expect);;log(I)(sum of intensity);;mh(pepetide mass);;delta;;z;;start;;sequence;;end;;modifications;;ionFile
        
        $tmp_array = explode($field_spliter, $buffer, 11);
         
        $tmp_array[0];  //spectrum 
        $pepExpects[$pNum][$peptide_num]  = $tmp_array[1]; 
        
        $pepIntensities[$pNum][$peptide_num] = $tmp_array[2]; //log(I)
        $pepMass[$pNum][$peptide_num]     = $tmp_array[3]/1000;
        //$tmp_array[4]; //delta
        $pepCharges[$pNum][$peptide_num]  = $tmp_array[5];
        $pepLocatoins[$pNum][$peptide_num]= preg_replace("/[a-z]+|-/i", "", $tmp_array[6])."--".preg_replace("/[a-z]+|-/i", "", $tmp_array[8]);
        $pepSequences[$pNum][$peptide_num]= $tmp_array[7];
        $pepModifications[$pNum][$peptide_num]= $tmp_array[9];
        $pepIonFiles[$pNum][$peptide_num]= $tmp_array[10];
        $peptide_num++;
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
    //print_r($pGIs);exit;
    //print_r($pRedundantGIs);exit;
    
    for($num=1; $num<=count($pGIs); $num++){
      
      $pGeneID = '';
      $MW = 0;
      $hit_id = 0;
      $thisAccessionType = get_protein_ID_type($pGIs[$num]);

      if($pGIs[$num] and isset($pepSequences[$num][0])){
        //this hit has been selected to save to ProHits
        $pLocusTag = '';
        
//--------------------------------------------------------------------------------------------------        
        if(isset($pGeneIDs[$num])){
          $pGeneID = $pGeneIDs[$num];
        }else{
          $pGeneID = get_protein_GeneID($pGIs[$num], $thisAccessionType, $proteinDB);
        }
//-------------------------------------------------------------------------------------------------        
         
        //$pGeneID = get_protein_GeneID($pGIs[$num], $thisAccessionType, $proteinDB);        
        
        if( !is_exist_hit($band_id,$pGIs[$num], $file, "GPM")){
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
           
          if(!$MW and $thisAccessionType == "GI") {
            //if it is not GI there is a different way to get sequence.
            //it can get sequence from Protein:Protein_Accession table.
            $tmp_seq_des = get_seqence_from_NCBI($pGIs[$num]);
            $pSequence[$num] = $tmp_seq_des['sequence'];
            $MW = calcProteinMass($pSequence[$num]);
          }//end of MW check
          $tmp_file = mysqli_real_escape_string($hitsDB->link, $file);
          $SQL ="INSERT INTO Hits SET 
            WellID='$well_id', 
            BaitID='$bait_id', 
            BandID='$band_id',
            GeneID='$pGeneID', 
            LocusTag='$pLocusTag', 
            HitGI='" .$pGIs[$num]."', 
            AccType='" .$thisAccessionType."', 
            HitName='".mysqli_real_escape_string($hitsDB->link, $pNames[$num])."', 
            Expect2='".$pExpects[$num]."',
            MW='$MW',
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
                //echo "<br>";
                //echo $SQL;
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
        } //end if not saved.
      }//end if -- checkbox has been checked, only checked hits will be saved
    }//end for -- all hits
    //display information  let user check saved records or back to sarch enchine
    //pass objects to function.
    //**************************************
  }//=====================end of inserting ===================================
  if(count($pGIs)>0){
   $msg = "File parsed: $GPMResults";
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
function is_exsist_gpm_hit($band_id,$proteinKey, $dataFile){
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
function parse_gpm_parameter($prt_name, $html_str){
  //<B>protein, taxon:  <font color=red>yeast</font></B>
  if(!$prt_name or !$html_str) return '';
  $html_str = preg_replace("/($prt_name)|(<\/?)(\w+)([^>]*>)/i",  "" ,$html_str);
  $html_str = trim($html_str);
  return $html_str;
}

function save_gpm_results_($GPMResults,$source_well_id, $target_band_id, $Conf, $field_spliter=';;', $isUploaded=0){
  global $USER;
  global $hitsDB;
  global $proteinDB;
  global $pRedundantGIs;
  global $AccessionType;
  global $gpm_ip;
  
  //echo "$GPMResults=source well===$source_well_id";
  //print_r($target_band_id);
  //echo "<br>";return;
  if(!$GPMResults or !$target_band_id) return 0;
  $well_id = '';
  $band_id = $target_band_id;
  
  //$Conf->GPM_Value
  //echo $frm_targetDB;exit;
  //echo $frm_score = $Conf->GPM_SaveScore;
  //echo $frm_peptide_validation = $Conf->SaveValidation;
  
  //make it saved.
  //peptide_min_size:0;matched_ion_percentage:0;peptide_min_charge:2;matched_ions_group_size:6;matched_ions_num:4;is_modified_peptide:0;peptide_min_score:27;requreBoldRed:1
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
  
  if($isUploaded){
    $subject = $GPMResults;
    $tmp_arr = explode(" ", $subject);
    $file = $tmp_arr[0];
    if(basename($_SERVER['SCRIPT_FILENAME']) == 'auto_save_shell.php'){
      $PROHITS_ROOT = str_replace('/msManager/autoSave','',dirname($_SERVER['SCRIPT_FILENAME']));
      $GPMResults = dirname(GPM_CGI_PATH).$GPMResults;
    }else{
      $PROHITS_ROOT = str_replace('/analyst','',dirname($_SERVER['SCRIPT_FILENAME']));
    }
    $com2 = "php prohits_GPM_parser.php $GPMResults";
    $com = "cd $PROHITS_ROOT/GPMParser; $com2;";
     
    $parsed_file_name = system($com, $retval);
    if($retval){
      echo "Parsing file $GPMResults is failed!";
      exit;
    }
    $tmp_dir_name = dirname($file); 
    $tmp_txt_name = basename($file, ".xml").".txt";
    $gpm_parser_page = $tmp_dir_name."/".$tmp_txt_name;
    $SearchEngine = "GPMUploaded";
	  $fd = fopen($gpm_parser_page,"r");
  }else{
    $timeout = 300000000;
    $old = ini_set('default_socket_timeout', $timeout);
    $host = $gpm_ip;
    $file = "$GPMResults"; 
    
    $queryString = "&field_spliter=$field_spliter&" . str_replace(',', '&', $Conf->GPM_Value);
    //echo "http://$host/thegpm-cgi/prohits_parser.pl?path=$file$queryString";exit;
    $gpm_parser_page = "http://$host".GPM_CGI_DIR."/prohits_parser.pl?path=$file$queryString";
    $fd = fopen($gpm_parser_page,"r");
    ini_set('default_socket_timeout', $old);
    stream_set_timeout($fd, $timeout);
    $SearchEngine = "GPM";
  }
  
  $i =0;
  if(!$fd){
    $msg =  "The file (http://$host".GPM_CGI_DIR."/prohits_parser.pl) dose not exist.
    \n<br>save_GPM_results function stopped in file auto_save_shell_fun.inc.php. the prohits_parser.pl should be placed in GPM/cgi/.
    If the prohits_parser.pl is in /GPM/cgi/ folder, you still get the error message. 
    The Prohits server default_socket_timeout should be increased in php.ini file.";
    fatal_Error($msg);
    mail(ADMIN_EMAIL, "GPM Parser error", $msg, "From: prohits_server\r\n"."Reply-To: \r\n");
    exit;
  } 
  //get all hits into arrays
  $pNum = 0; //temp checkbox counter
  $hitStart = false; 
  $peptide_start = false;
  $peptide_num = 0;
  $searchedDB = '';
  //while (!feof ($fd) and !$endFile) {
  while (!feof ($fd)) {
      $buffer = trim(fgets($fd, 40960));
      if(!$buffer) continue; 
      if(strstr($buffer,"could not be found in the archive")){
        $msg = "GPM dat file ('$file') is missing from GPM archive folder.";
        write_Log($msg);
      }
      //get searched database
      if(strstr($buffer,"<B>protein, taxon:")){
        $searchedDB = parse_gpm_parameter("protein, taxon:", $buffer);
      }
      //find hit start position
      //<b>HitNumber;; Protein: Identifier;;log(I)(sum of spectrum intensity);;rI(num peptide);;num uniqe peptide;;Coverage%;;log(e)(expect);;pI;;Mr(kDa);;Description
      if(preg_match("/^(Hit_[0-9]*)/", $buffer) > 0){
        //echo $buffer."\n";
        $pNum++;  //tmp checkbox counter start from 1
        $peptide_start = true; 
         
        $peptide_num = 0; //peptids num of the hit
        $tmp_array      = explode($field_spliter, $buffer, 10);
        $pGIs[$pNum]    = $tmp_array[1];
        if(strstr($pGIs[$pNum] , 'gi')){
          $pGIs[$pNum]    = preg_replace('/[^0-9]/','', $pGIs[$pNum]);
        }
        $pIntensitry[$pNum] = $tmp_array[2];  //sum of spectrum intensity
        $pPeptideUniqiqeNum[$pNum] = $tmp_array[3];  //num uniqe peptide
        $pPeptideNum[$pNum] = $tmp_array[4];  //num peptide
        $pCoverage[$pNum] = $tmp_array[5];
        $pExpects[$pNum] = $tmp_array[6];
        //$tmp_array[7];  //pritein pI
        $pMasses[$pNum] = $tmp_array[8];
        $pNames[$pNum] = $tmp_array[9];
        
        $buffer = trim(fgets($fd, 140960));//get next line
     
      }
      if(strtoupper($buffer) == '<HR>') {
        $peptide_start = false;
      }
      
      if($peptide_start and $buffer){
        // spectrum;;log(e)(expect);;log(I)(sum of intensity);;mh(pepetide mass);;delta;;z;;start;;sequence;;end;;modifications;;ionFile
        
        $tmp_array = explode($field_spliter, $buffer, 11);
         
        $tmp_array[0];  //spectrum 
        $pepExpects[$pNum][$peptide_num]  = $tmp_array[1]; 
        
        $pepIntensities[$pNum][$peptide_num] = $tmp_array[2]; //log(I)
        $pepMass[$pNum][$peptide_num]     = $tmp_array[3]/1000;
        //$tmp_array[4]; //delta
        $pepCharges[$pNum][$peptide_num]  = $tmp_array[5];
        $pepLocatoins[$pNum][$peptide_num]= preg_replace("/[a-z]+|-/i", "", $tmp_array[6])."--".preg_replace("/[a-z]+|-/i", "", $tmp_array[8]);
        $pepSequences[$pNum][$peptide_num]= $tmp_array[7];
        $pepModifications[$pNum][$peptide_num]= $tmp_array[9];
        $pepIonFiles[$pNum][$peptide_num]= $tmp_array[10];
        $peptide_num++;
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
    //print_r($pGIs);exit;
    //print_r($pRedundantGIs);exit;
    
    for($num=1; $num<=count($pGIs); $num++){
      
      $pGeneID = '';
      $MW = 0;
      $hit_id = 0;
      $thisAccessionType = get_protein_ID_type($pGIs[$num]);

      if($pGIs[$num] and isset($pepSequences[$num][0])){
        //this hit has been selected to save to ProHits
        $pLocusTag = ''; 
        $pGeneID = get_protein_GeneID($pGIs[$num], $thisAccessionType, $proteinDB);
        
        if( !is_exist_hit($band_id,$pGIs[$num], $file, "GPM")){
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
           
          if(!$MW and $thisAccessionType == "GI") {
            //if it is not GI there is a different way to get sequence.
            //it can get sequence from Protein:Protein_Accession table.
            $tmp_seq_des = get_seqence_from_NCBI($pGIs[$num]);
            $pSequence[$num] = $tmp_seq_des['sequence'];
            $MW = calcProteinMass($pSequence[$num]);
          }//end of MW check
          $tmp_file = mysqli_real_escape_string($hitsDB->link, $file);
          $SQL ="INSERT INTO Hits SET 
            WellID='$well_id', 
            BaitID='$bait_id', 
            BandID='$band_id',
            GeneID='$pGeneID', 
            LocusTag='$pLocusTag', 
            HitGI='" .$pGIs[$num]."', 
            AccType='" .$thisAccessionType."', 
            HitName='".mysqli_real_escape_string($hitsDB->link, $pNames[$num])."', 
            Expect2='".$pExpects[$num]."',
            MW='$MW',
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
                //echo "<br>";
                //echo $SQL;
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
        } //end if not saved.
      }//end if -- checkbox has been checked, only checked hits will be saved
    }//end for -- all hits
    //display information  let user check saved records or back to sarch enchine
    //pass objects to function.
    //**************************************
  }//=====================end of inserting ===================================
  if(count($pGIs)>0){
   $msg = "File parsed: $GPMResults";
    write_Log($msg);
    return true;
  }
  return false;
}
//end of main function ********************************************************************************************

?>