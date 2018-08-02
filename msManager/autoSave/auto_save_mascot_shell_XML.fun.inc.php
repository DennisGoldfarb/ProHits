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
   2. It well process a mascot result file ( mascot_parser.pl) to save hit into a target DB.
   3. some information should be passed to the function.
      a. Mascot results file = '/data/20040128/F003210.dat'
      b. A Conf object which contains :
        var $table;
        var $link;
        
        var $ID;
        var $TaskID;
        var $Mascot_SaveScore;
        var $Mascot_SaveValidation;
        var $Status;
        var $SaveBy;
        var $SetDate;
        var $Mascot_SaveWell_str;
        var $GPM_SaveWell_str;
        var $Mascot_Other_Value;
        var $GPM_Value;
        
        var $count;
  
      c. target well array $target_band_id = array(well_ID, band_ID)
      d. MsWell ID - source_well_id
   4. default protein accession type is GI. It will be changed based on the searched
      result file. It will be used in Proteins: Proetein_Accession.
      cgi/mascot_parser.pl file should be modified and passed back the value.
\**************************************************************************/


function save_mascot_results($MascotResults,$source_well_id, $target_band_id, $Conf, $field_spliter=';;'){
  global $table;
  global $USER;
  global $proteinDB;
  global $managerDB;
  global $hitsDB;
  
  $resultTableName = $table . "SearchResults";
  
  if(!$MascotResults or !$target_band_id) return 0;
  $well_id = '';
  $band_id = $target_band_id;
  if($Conf->Mascot_SaveScore == 'save all hits'){
    $limited_score = 1;
  }else if($Conf->Mascot_SaveScore == 'validate all hits'){
    $limited_score = 1000000;
  }else{
    $limited_score = $Conf->Mascot_SaveScore;
  }
  $Mascot_Other_Value = $Conf->Mascot_Other_Value;
  $peptide_min_score = 0;
  $requireBoldRed =1;
  if(preg_match('/.*peptide_min_score:(\d*).*requireBoldRed:(\d*)/i', $Mascot_Other_Value, $matches)){
    $peptide_min_score = $matches[1];
    $requireBoldRed = $matches[2];
  }
  $file = $MascotResults;
  
  $timeout = 300000000;
  $old = ini_set('default_socket_timeout', $timeout);
  
  
  $url_para = "&do_export=1&prot_hit_num=1&prot_acc=1&pep_query=1&pep_exp_mz=1&export_format=XML&_sigthreshold=0.05&REPORT=AUTO&_mudpit=99999999&_requireboldred=$requireBoldRed&_ignoreionsscorebelow=$peptide_min_score&show_same_sets=1&show_header=1&show_params=1&show_format=1&show_masses=1&show_queries=1&prot_score=1&prot_desc=1&prot_mass=1&prot_matches=1&prot_cover=1&prot_len=1&prot_pi=1&prot_tax_str=1&prot_tax_id=1&pep_exp_mr=1&pep_exp_z=1&pep_calc_mr=1&pep_delta=1&pep_start=1&pep_end=1&pep_miss=1&pep_score=1&pep_homol=1&pep_ident=1&pep_expect=1&pep_rank=1&pep_seq=1&pep_frame=1&pep_var_mod=1";//&prohitsExport=Y";
  
  $url = "http://".MASCOT_IP.MASCOT_CGI_DIR."/export_dat.pl?file=".$file.$url_para;
  $fd = @fopen($url,"r");
  
  ini_set('default_socket_timeout', $old);
  stream_set_timeout($fd, $timeout);
  $i =0;
  if(!$fd){
    $msg = "The file (http://".MASCOT_IP.MASCOT_CGI_DIR."/export_dat.pl) dose not exsist.\n<br>save_mascot_results function stopped in file auot_save_shell_fun.inc.php. the prohits_parser.pl should be placed in Mascot/cgi/";
    echo $msg;
    mail(ADMIN_EMAIL, "Mascot Parser error", $msg, "From: prohits_server\r\n"."Reply-To: \r\n");
    exit;
  }
  $treePath = array();
  $searchedDB = '';
  $ResultFile = '';
  $queryPrinted = array();
  $querieArr = array();
  $sampleArr = array();
  
  while (!feof($fd)){
    $buffer = fgets($fd, 40960);
    if(preg_match ('/^<header>/i', $buffer)){
      $treePath[0] = 'header';
    }elseif(preg_match('/^<\/header>/i', $buffer)){
      array_pop($treePath);
    }elseif(isset($treePath[0]) && $treePath[0] == 'header' && count($treePath) == 1){
      if(preg_match ('/^<DB>(.*)<\/DB>/i', $buffer, $matches)){
        $searchedDB = $matches[1];
      }elseif(preg_match ('/^<FastaVer>(.*)<\/FastaVer>/i', $buffer, $matches)){
        $searchedDB .= '('.$matches[1].')';
      }elseif(preg_match ('/^<URI>.*?=(.*)<\/URI>/i', $buffer, $matches)){
        $ResultFile = $matches[1];
      }
    }elseif(preg_match ('/^<search_parameters>/i', $buffer)){
      $treePath[0] = 'search_parameters';
    }elseif(preg_match('/^<\/search_parameters>/i', $buffer)){
      array_pop($treePath);
    }elseif(isset($treePath[0]) && $treePath[0] == 'search_parameters' && count($treePath) == 1){
      if(preg_match ('/^<INSTRUMENT>(.*)<\/INSTRUMENT>/i', $buffer, $matches)){
        $instrument = $matches[1];
      } 
    }elseif(preg_match('/^<hits>/i', $buffer)){
      $treePath[0] = 'hits';
    }elseif(preg_match('/^<\/hits>/i', $buffer)){
      array_pop($treePath);
    }elseif(preg_match('/^<hit/i', $buffer)){
      $treePath[1] = 'hit';
      $proteinCounter = 0;
      $hitArr = array('HitGI'=>'','HitName'=>'','Expect'=>'','MW'=>'','Coverage'=>'','Pep_num_uniqe'=>'','Pep_num'=>'','RedundantGI'=>'');
      $peptidesArr = array();
      $RedundantGI = '';
    }elseif(preg_match('/^<\/hit>/i', $buffer)){
      $tmpSeqArr = array();
      foreach($peptidesArr as $pepValue){
        if(!in_array($pepValue['Sequence'], $tmpSeqArr) && $pepValue['Status'] == "RB"){
          array_push($tmpSeqArr, $pepValue['Sequence']);
        }
      }
      $hitArr['Pep_num_uniqe'] = count($tmpSeqArr);
      $hitArr['Pep_num'] = count($peptidesArr);
      $hitArr['RedundantGI'] = $RedundantGI;
      $hit_peptides_Arr['hit'] = $hitArr;
      $hit_peptides_Arr['peptides'] = $peptidesArr;
      array_push($sampleArr, $hit_peptides_Arr);
      //Clean up variables
      unset($tmpSeqArr);
      unset($peptidesArr);
      unset($hitArr);
      unset($hit_peptides_Arr);
      array_pop($treePath);
    }elseif(preg_match('/^<protein.*?=\"(.*)\">/i', $buffer, $matches)){
      $treePath[2] = 'protein';
      if($proteinCounter){
        if($RedundantGI) $RedundantGI .= '; ';
        $RedundantGI .= $matches[1];
      }else{
        $hitArr['HitGI'] = trim(preg_replace("/sp\||gi\||\|/", "",$matches[1]));
      }
      $proteinCounter++;
    }elseif(preg_match('/^<\/protein>/i', $buffer, $matches)){
      array_pop($treePath);
    }elseif(isset($treePath[2]) && $treePath[2] == 'protein' && $proteinCounter == 1 && count($treePath) == 3){
      if(preg_match('/<prot_desc>(.*)<\/prot_desc>/i', $buffer, $matches)){
        $hitArr['HitName'] = mysqli_escape_string($hitsDB->link, $matches[1]);
      }elseif(preg_match('/<prot_score>(.*)<\/prot_score>/i', $buffer, $matches)){
        $hitArr['Expect'] = $matches[1];
      }elseif(preg_match('/<prot_mass>(.*)<\/prot_mass>/i', $buffer, $matches)){
        $hitArr['MW'] = $matches[1];
      }elseif(preg_match('/<prot_cover>(.*)<\/prot_cover>/i', $buffer, $matches)){
        $hitArr['Coverage'] = $matches[1];
      }elseif(preg_match('/^<peptide query=\"(.*)\">/i', $buffer, $matches) && $proteinCounter == 1){//start peptide
        $queryNum = $matches[1];
				$querieArr[$queryNum] = '';
        $peptideArr = array();
        $peptideArr['queryNum'] = $queryNum;
      }elseif(preg_match('/^<pep_exp_mz>(.*)<\/pep_exp_mz>/i', $buffer, $matches)){
        $peptideArr['MZ'] = $matches[1];
      }elseif(preg_match('/^<pep_exp_mr>(.*)<\/pep_exp_mr>/i', $buffer, $matches)){
        $peptideArr['MASS'] = round($matches[1]/1000, 3);
        $peptideArr['Charge'] = ($peptideArr['MZ'])?round($matches[1]/$peptideArr['MZ']):'';	
      }elseif(preg_match('/^<pep_miss>(.*)<\/pep_miss>/i', $buffer, $matches)){
       $peptideArr['Miss'] = trim($matches[1]);
      }elseif(preg_match('/^<pep_score>(.*)<\/pep_score>/i', $buffer, $matches)){
        $peptideArr['Expect'] = trim($matches[1]);
      }elseif(preg_match('/^<pep_expect>(.*)<\/pep_expect>/i', $buffer, $matches)){
        $peptideArr['Expect2'] = trim($matches[1]);
      }elseif(preg_match('/^<pep_start>(.*)<\/pep_start>/i', $buffer, $matches)){
        $start = $matches[1]; 
      }elseif(preg_match('/^<pep_end>(.*)<\/pep_end>/i', $buffer, $matches)){
        $end = $matches[1];
        $peptideArr['Location'] = $start."--".$end;
      }elseif(preg_match('/^<pep_seq>(.*)<\/pep_seq>/i', $buffer, $matches)){
        $peptideArr['Sequence'] = trim($matches[1]);
      }elseif(preg_match('/^<pep_var_mod>(.*)<\/pep_var_mod>/i', $buffer, $matches)){
        $peptideArr['Modifications'] = mysqli_escape_string($hitsDB->link, $matches[1]);
      }elseif(preg_match('/^<pep_var_mod\s?\/>/i', $buffer, $matches)){
        $peptideArr['Modifications'] = '';
      }elseif(preg_match('/^<pep_rank>(.*)<\/pep_rank>/i', $buffer, $matches)){
        $rank = $matches[1];
        $peptideArr['Status'] = get_peptide_status($queryNum, $rank);
      }elseif(preg_match('/^<\/peptide>/i', $buffer) && $proteinCounter == 1){
        array_push($peptidesArr, $peptideArr);
        unset($peptideArr);//---clean up peptides array---------
      }  
    }elseif(preg_match('/^<queries>/i', $buffer)){
      $treePath[0] = 'queries';
    }elseif(preg_match('/^<\/queries>/i', $buffer)){
      array_pop($treePath);
    }elseif(preg_match('/^<query number=\"(.*)\">/i', $buffer, $matches)){
      $treePath[1] = 'querie';
      $temNum = $matches[1];
    }elseif(preg_match('/^<\/query>/i', $buffer, $matches)){
      array_pop($treePath);
      unset($temNum);
    }elseif(isset($treePath[1]) && $treePath[1] == 'querie' && count($treePath) == 2){
      if(preg_match('/^<StringTitle>(.*)<\/StringTitle>/i', $buffer, $matches)){
				if(array_key_exists($temNum, $querieArr)) $querieArr[$temNum] = $matches[1];
      }
    }
  }
  /*echo "<br>";
  print_r($sampleArr);
  echo "</br>";*/
  if($sampleArr){
    //get bait id of the well and bait species.
    $sql = "SELECT E.TaxID, E.BaitID, E.ProjectID FROM Experiment E, Band B WHERE E.ID = B.ExpID and B.ID='$band_id'";
    
    $tmp_arr = $hitsDB->fetch($sql);
    //$pTaxID = $tmp_arr['TaxID'];
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
    
    for($i=0; $i<count($sampleArr); $i++){
      $tmphitArr = $sampleArr[$i]['hit'];
      if(is_numeric($limited_score)){
        if(is_numeric($tmphitArr['Expect']) and $tmphitArr['Expect'] < $limited_score) continue;
      }
      $tmppeptidesArr = $sampleArr[$i]['peptides'];
      
      $tmphitArr['HitGI'] = trim(preg_replace("/sp\||gi\||\|/", "",$tmphitArr['HitGI']));
      $AccType = get_protein_ID_type($tmphitArr['HitGI']);
      $GeneID = get_GeneID($tmphitArr,$AccType);
      if(!is_exsist_hit($well_id, $tmphitArr['HitGI'], $file)){
        $LocusTag = '';
        if($GeneID){
          $SQL = "select LocusTag from Protein_Class where EntrezGeneID='".$GeneID."'";
          $row = $proteinDB->fetch($SQL);
          if(count($row) && $row['LocusTag'] != '-'){
            $LocusTag = $row['LocusTag'];
          }
        } 
        $MW = round($tmphitArr['MW']/1000,2);
        if(!$MW and $AccType == "GI") {
          //if it is not GI there is a different way to get sequence.
          //it can get sequence from Protein:Protein_Accession table.
          $tmp_seq_des = get_seqence_from_NCBI($tmphitArr['HitGI']);
          $pSequence = $tmp_seq_des['sequence'];
          $MW = calcProteinMass($pSequence);
        }
        
        if($GeneID and $bait_GeneID){
           $SQL = "SELECT BaitGeneID from BaitToHits WHERE BaitGeneID='$bait_GeneID' and HitGeneID='$GeneID' and ProjectID='$project_id'";
           if(!$hitsDB->exist($SQL)){
              $SQL = "INSERT INTO BaitToHits SET BaitGeneID='$bait_GeneID', HitGeneID='$GeneID', ProjectID='$project_id'";
              $hitsDB->insert($SQL);
           }
        } 
        $SQL ="INSERT INTO Hits SET 
          WellID='$well_id', 
          BaitID='$bait_id', 
          BandID='$band_id', 
          Instrument='$instrument', 
          GeneID='$GeneID', 
          LocusTag='$LocusTag', 
          HitGI='" .$tmphitArr['HitGI']."', 
          AccType='$AccType', 
          HitName='".addslashes($tmphitArr['HitName'])."', 
          Coverage='".$tmphitArr['Coverage']."',
          Pep_num='".$tmphitArr['Pep_num']."',
          Pep_num_uniqe='".$tmphitArr['Pep_num_uniqe']."', 
          Expect='".$tmphitArr['Expect']."',
          MW='$MW',
          RedundantGI='".$tmphitArr['RedundantGI']."',
          ResultFile='$file', 
          SearchDatabase='$searchedDB', 
          DateTime=now(),
          SearchEngine='Mascot', 
          OwnerID='".$USER['ID']."'";
        $hitsDB->check_connection();
        $hit_id = $hitsDB->insert($SQL);
        
        foreach($tmppeptidesArr as $value){
           $tmp_pep_ID = 0;
           $IonFile = (isset($querieArr[$value['queryNum']]))?$querieArr[$value['queryNum']]:'';
           if($hit_id){
              $SQL ="INSERT INTO Peptide SET 
                  HitID='$hit_id', 
                  Charge='".$value['Charge']."', 
                  MZ='".$value['MZ']."', 
                  MASS='".$value['MASS']."', 
                  Location='".$value['Location']."', 
                  Expect='".$value['Expect']."',
                  Expect2='".$value['Expect2']."',
                  Sequence='".$value['Sequence']."',
                  IonFile='".$IonFile."',
                  Status='".$value['Status']."',
                  Modifications='".$value['Modifications']."',
                  Miss='".$value['Miss']."'";
              $tmp_pep_ID = $hitsDB->insert($SQL);
           }
           //look for same mz peptide set smaller one "RemvedBy=-1"
           if(!isset($pep_ionFile_scores[$IonFile])){
              $pep_ionFile_scores[$IonFile] = array($tmp_pep_ID, $value['Expect']);
           }else if($tmp_pep_ID){
              $small_score_pep_ID = $tmp_pep_ID;
              if($pep_ionFile_scores[$IonFile][1] < $value['Expect']){
                 $small_score_pep_ID = $pep_ionFile_scores[$IonFile][0];
                 $pep_ionFile_scores[$IonFile] = array($tmp_pep_ID, $value['Expect']);
              }
              $SQL = "update Peptide set RemovedBy='-1' where ID='$small_score_pep_ID'";
              $hitsDB->update($SQL);
           }
        }//end foreach loop
      }
    }
  }
  if($sampleArr){
    return true;
  }else{
    return false;
  }
}

//end of main function ********************************************************************************************

//------------------------
//if the specie is human it will 
// use protein gi to compare and make redundant gis
// get the GeneID for the group hits
// this script should be modified to use one query to get all GeneID. e.g. GI in (112,234,22344) ordery by EntreGeneID desc
//------------------------
function get_GeneID(&$tmphitArr,$AccType){
  global $proteinDB;
  $rt = '';
  
  $geneID = find_gene_id($AccType, $tmphitArr['HitGI']);
  if($geneID){
    return $geneID;
  }elseif($tmphitArr['RedundantGI']){
    $tmp_arr = explode(';', $tmphitArr['RedundantGI']);
    for($i = 0; $i < count($tmp_arr); $i++){
      $tmpAccType = get_protein_ID_type($tmp_arr[$i]);
      $tmpProteinID = trim(preg_replace("/sp\||gi\||\|/", "",$tmp_arr[$i]));
      $geneID = find_gene_id($tmpAccType, $tmpProteinID);
      if($geneID){
        $SQL = "select EntrezGeneID, Description, SequenceID, GI from Protein_Accession where EntrezGeneID='".$geneID."'";
        $tmpAccAtrArr = $proteinDB->fetch($SQL);
        if(count($tmpAccAtrArr)){
          $rt = $tmpAccAtrArr['EntrezGeneID'];
          $tmphitArr['HitName'] = $tmpAccAtrArr['Description'];
          $mProteinID = $tmphitArr['HitGI'];
          $tmphitArr['HitGI'] = $tmpProteinID;
          $SQL = "select Sequence from Protein_Sequence where ID='" . $tmpAccAtrArr['SequenceID'] . "'";
          $row = $proteinDB->fetch($SQL);
          if(count($row) && $row['Sequence']){
            $tmphitArr['MW'] = calcMass($row['Sequence']);
          }else{
            $tmphitArr['MW'] = '';
          }
          $tmp_arr[$i] =$mProteinID;
          break;
        }
      }  
    }//end for
    $tmphitArr['RedundantGI'] = implode("; ", $tmp_arr);
  }
  return $rt;
}

function find_gene_id($AccType, $ProteinID){
  global $proteinDB;
  $AccType = strtoupper($AccType);
  $SQL = '';
  if($AccType == 'GI'){
    $SQL = "select EntrezGeneID from Protein_Accession where GI='".$ProteinID."' and EntrezGeneID is not null";
  }elseif($AccType == 'ORF'){
    $SQL = "SELECT `EntrezGeneID` FROM `Protein_Class` WHERE `LocusTag`='".$ProteinID."'";
  }elseif($AccType == 'NCBIACC'){ 
    $giSplitArr = explode('.',$ProteinID);
    $SQL = "select EntrezGeneID from Protein_Accession where Acc='".$giSplitArr[0]."' and EntrezGeneID is not null";
  }elseif($AccType == 'UNIPROT'){
    $SQL = "select EntrezGeneID from Protein_Accession where UniProtID='".$ProteinID."' and EntrezGeneID is not null";
  }
  if($SQL){
    $row = $proteinDB->fetch($SQL); 
    if(count($row) && $row['EntrezGeneID']){
      return $row['EntrezGeneID'];
    }else{
      return '';
    }
  }else{
    return '';
  }
}

//-----------------------
//check if the hit is in 
//database
//-----------------------
function is_exsist_hit($well_id,$hit_gi, $dataFile){
  global $hitsDB;
  $rt = 0;
  $SQL = "select ID from Hits where WellID='$well_id' and HitGI='$hit_gi' and ResultFile='$dataFile' and SearchEngine='Mascot'";
  //echo $SQL;
  $hitsDB->fetch($SQL);
  if( count($hitsDB->fetch($SQL)) ){
    $rt = 1;
  }
  return $rt;
}//end function

// ---- -----------------
//calExpect(string str)
//retrun float
//-----------------------

function calExpect($str){
  $str = trim($str);
  $expect = str_replace('?0','e',$str);
  return $expect; //retrun 2.3e-25 string
}

function get_peptide_status($queryNum, $rank){
  global $queryPrinted;
  $startHiLite = '';
  if(isset($queryPrinted[$queryNum]) && $rank > 1) {
        $startHiLite = "";
  }elseif(isset($queryPrinted[$queryNum]) && $rank <= 1){
    $startHiLite = "R";
    #$endHiLite = "</FONT>";
  }elseif(!isset($queryPrinted[$queryNum]) && $rank <= 1){
    $startHiLite = "RB";
    #$endHiLite = "</B></FONT>";
    $queryPrinted[$queryNum] = 1;
  }else{
  # !$queryPrinted{$queryNum} && $rank{$key} > 1
    $startHiLite = "B";
    #$endHiLite = "</B>";
    $queryPrinted[$queryNum] = 1;
  }
  return $startHiLite;
}

?>
