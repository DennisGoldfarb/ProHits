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

if(!function_exists('fatalError')) { 
  function fatalError($msg='', $line=0){
    echo "<h2>Fatal Error</h2><font size=4 color=red>$msg</font>";
    echo "<br>Script Name: " . $_SERVER['PHP_SELF'];
    if($line){
      echo "<br>Line number: $line";
    }
    exit;
  }
}
function is_file_for_large_file($path){
    exec('[ -f "'.$path.'" ]', $tmp, $ret);
    return $ret == 0;
}
function calcMass($seq, $frm_MASS=''){
  $Residue_mono = array();
  $Residue_mono["A"] = 71.03711;  $Residue_ave["A"] = 71.0788;
  $Residue_mono['B'] = 114.53493; $Residue_ave['B'] = 114.59625;
  $Residue_mono['C'] = 103.00919; $Residue_ave['C'] = 103.1388;
  $Residue_mono['D'] = 115.02694; $Residue_ave['D'] = 115.0886;
  $Residue_mono['E'] = 129.04259; $Residue_ave['E'] = 129.1155;
  $Residue_mono['F'] = 147.06841; $Residue_ave['F'] = 147.1766;
  $Residue_mono['G'] =  57.02146; $Residue_ave['G'] =  57.0520;
  $Residue_mono['H'] = 137.05891; $Residue_ave['H'] = 137.1412;
  $Residue_mono['I'] = 113.08406; $Residue_ave['I'] = 113.1595;
  $Residue_mono['J'] =   0.0;     $Residue_ave['J'] =    0.0;
  $Residue_mono['K'] = 128.09496; $Residue_ave['K'] = 128.1742;
  $Residue_mono['L'] = 113.08406; $Residue_ave['L'] = 113.1595;
  $Residue_mono['M'] = 131.04049; $Residue_ave['M'] = 131.1925;
  $Residue_mono['N'] = 114.04293; $Residue_ave['N'] = 114.1039;
  $Residue_mono['O'] =   0.0    ; $Residue_ave['O'] = 0.0;
  $Residue_mono['P'] =  97.05276; $Residue_ave['P'] =  97.1167;
  $Residue_mono['Q'] = 128.05858; $Residue_ave['Q'] = 128.1308;
  $Residue_mono['R'] = 156.10111; $Residue_ave['R'] = 156.1876;
  $Residue_mono['S'] =  87.03203; $Residue_ave['S'] =  87.0782;
  $Residue_mono['T'] = 101.04768; $Residue_ave['T'] = 101.1051;
  $Residue_mono['U'] =   0.0    ; $Residue_ave['U'] = 0.0;
  $Residue_mono['V'] =  99.06841; $Residue_ave['V'] =  99.1326;
  $Residue_mono['W'] = 186.07931; $Residue_ave['W'] = 186.2133;
  $Residue_mono['X'] = 111.0    ; $Residue_ave['X'] = 111.0;
  $Residue_mono['Y'] = 163.06333; $Residue_ave['Y'] = 163.1760;
  $Residue_mono['Z'] = 128.55059; $Residue_ave['Z'] = 128.62315;

  // Atomic masses used for terminus values
  $HYDROGEN['mono'] = 1.007825;  $HYDROGEN['ave'] = 1.00794;
  $OXYGEN['mono'] = 15.99491;    $OXYGEN['ave'] = 15.9994;
  
  $seq = strtoupper($seq);
  $seq = trim(preg_replace("/[^A-Z]/e", "",$seq));
  if($frm_MASS == "Monoisotopic" or !$frm_MASS){
    $mino_masses = $Residue_mono;
    $mono_ave = 'mono';
  }else{
    $mino_masses = $Residue_ave;
    $mono_ave = 'ave';
  }
  $tmp_array = preg_split('//', $seq, -1, PREG_SPLIT_NO_EMPTY);
  $pepMasses = 0;
  for($k = 0; $k < count($tmp_array); $k++){ 
    $pepMasses += $mino_masses[$tmp_array[$k]];
   //$pepMasses[$i] = $runningSum[$end-1]-$runningSum[$startNum[$i]-2];
   //print "$i $peptides[$i] $pepMasses[$i]<br>";
  }
  $pepMasses += 2*$HYDROGEN[$mono_ave] + $OXYGEN[$mono_ave];
  return round($pepMasses,2);
}

function GetSequence($mainDB, $proteinKey, $peptidesArr="", $returnType="", $isOnLine=1){
  $oldDBName = to_proteinDB($mainDB);
  $sequence = '';
  
  $AccessionType = get_protein_ID_type($proteinKey);
  $AccessionType = strtoupper($AccessionType);
  
  if($AccessionType == 'GI'){
    $SQL = "select SequenceID from Protein_Accession where GI='".$proteinKey."'";
  }else if($AccessionType == 'ENS'){
    $SQL = "select SequenceID from Protein_AccessionENS where ENSP='".$proteinKey."'";
  }elseif($AccessionType == 'UNIPROT'){
    $SQL = "select SequenceID from Protein_Accession where UniProtID='".$proteinKey."'";
  }else{
    if(strpos($proteinKey,'.')){
      $SQL = "select SequenceID  from Protein_Accession where Acc_Version='".$proteinKey."'";
    }else{
      $SQL = "select SequenceID  from Protein_Accession where Acc='".$proteinKey."'";
    }
  }
  if($SQL){
    $SQL .= " AND SequenceID IS NOT NULL";
  }  
  $Protein_AccessionArr = $mainDB->fetch($SQL);
  if($Protein_AccessionArr && $Protein_AccessionArr['SequenceID']){
    $SQL = "SELECT Sequence FROM Protein_Sequence WHERE ID='".$Protein_AccessionArr['SequenceID']."'";
    
    $Protein_SequenceArr = $mainDB->fetch($SQL);
    if($Protein_SequenceArr && $Protein_SequenceArr['Sequence']){
      $sequence = $Protein_SequenceArr['Sequence'];
    }
  }
  if(!$sequence && $isOnLine){
  	$pro_arr = get_protein_from_url($proteinKey);
	  if($pro_arr) $sequence = $pro_arr['sequence'];
  }  
  
  back_to_oldDB($mainDB, $oldDBName);
  if(is_array($peptidesArr) && $returnType){
    $sequenceLen = '';
    if($sequence){
      $sequenceLen = strlen($sequence);
      $sequence = strtolower($sequence);
      for($i=0; $i<count($peptidesArr); $i++){
        if(strstr($peptidesArr[$i], '+')){
          $temArr = explode('+', $peptidesArr[$i]);
          $peptidesArr[$i] = trim(preg_replace("/[^A-Z]/e", "",$temArr[0]));
        }elseif(strstr($peptidesArr[$i], '.')){
          $temArr = explode('.', $peptidesArr[$i]);
          $peptidesArr[$i] = trim(preg_replace("/[^A-Z]/e", "",$temArr[1]));
        }
        $peptideUp = strtoupper($peptidesArr[$i]);
        $sequence = str_ireplace($peptidesArr[$i], $peptideUp, $sequence);
      }
    }
    if($returnType == 'bold red'){
      $seqTmpArr =str_split($sequence, 10);
      echo "<br><br>";
      $sequence = "";
      $tmpounter = count($seqTmpArr);
      for($i=0; $i<$tmpounter; $i++){
        if($i && !($i%5)){
          $sequence .="</br>";
        }else if($i) $sequence .= "&nbsp;&nbsp;&nbsp;";
        $sequence .= $seqTmpArr[$i];
      }
      $sequence = preg_replace('/([A-Z]+\s?[A-Z]*)/', '<FONT COLOR=#FF0000><B>\1</B></FONT>', $sequence);
      $sequence = strtoupper($sequence);
      $sequence = str_ireplace("&NBSP;","&nbsp;",$sequence);
      return $sequence;
    }else if($returnType == 'coverage'){    
      $upCase = 0;
      foreach(count_chars($sequence, 1) as $i => $val) {
        if($i >= 65 && $i <= 90){
          $upCase += $val;
        }
      }
      if(!$sequenceLen){
        $coverage = 0;
      }else{
        $coverage = round($upCase/$sequenceLen, 4) * 100;
      }
      return $coverage; 
    }
    
  }else{   
    $sequence = strtoupper($sequence);
    return $sequence;
  }
}
 
function get_seqence_from_NCBI($GI){ 
  if(!$GI) return 0;
  $seqStart = false;
  $seq = '';
  global $URLS;
  //$url = "http://www.ncbi.nih.gov/entrez/eutils/efetch.fcgi?rettype=fasta&retmode=text&db=protein&id=$GI";
  $url = $URLS["NCBI_PROTEIN_FASTA"] .$GI;
  $fhand = @fopen ($url, "r");
  if(!$fhand) {
    echo "NCBI can't be accessed now. Click back button try again later.<br>$url";
    return array();
  }
  $description = fgets($fhand, 40960);
  $descriptionArr = explode('|', $description);
  $description = trim($descriptionArr[count($descriptionArr)-1]);
  //>gi|3550240|gb|AAC36099.1| SH3 domain-containing adapter protein; CD2AP [Mus musculus]
  if(strstr($description,"is not found")) return array();
  
  while (!feof ($fhand)) { 
    $ncbiFile = trim(fgets($fhand, 40960));
    $seq .= $ncbiFile;
  }
  fclose($fhand); 
  return array('sequence'=>$seq, 'description'=>$description);
}

function to_proteinDB($mainDB){
  $oldDBName = '';
  if($mainDB->selected_db_name != PROHITS_PROTEINS_DB){
    $oldDBName =$mainDB->selected_db_name;
    $mainDB->change_db(PROHITS_PROTEINS_DB);  
  }  
  return $oldDBName;
}

function change_DB($mainDB, $toDBname){
  $oldDBName = '';
  if($mainDB->selected_db_name != $toDBname){
    $oldDBName = $mainDB->selected_db_name;
    $mainDB->change_db($toDBname);    
  }  
  return $oldDBName;
}

function to_defaultDB($mainDB){
  $oldDBName = '';
  if($mainDB->selected_db_name != PROHITS_DB){
    $oldDBName =$mainDB->selected_db_name;
    $mainDB->change_db(PROHITS_DB);    
  }  
  return $oldDBName;
}

function back_to_oldDB($mainDB, $oldDBName){ 
  if($oldDBName){
    $mainDB->change_db($oldDBName);
  }
}

function get_user_permited_project_id_name($mainDB, $AccessUserID, $intert_only=0){
  $oldDBName = to_defaultDB($mainDB);
  $SQL = "SELECT P.ID, P.Name 
          FROM Projects P, ProPermission M
          WHERE M.UserID = $AccessUserID 
          AND M.ProjectID=P.ID";
  if($intert_only){
    $SQL .= " AND M.Insert=1";
  }
  $SQL .= " ORDER BY P.Name";
  $ProjectArr2 = $mainDB->fetchAll($SQL);
  if(!$ProjectArr2){
    return  "You don't have adding date permissions for any projects.";
  }
  $ProjectNameIDarr=array();
  for($i=0; $i<count($ProjectArr2); $i++){
    $ProjectNameIDarr[$ProjectArr2[$i]['ID']] = $ProjectArr2[$i]['Name'];
  }
  return $ProjectNameIDarr;
}

function get_projectID_DBname_pair($mainDB, $projectID=0){
  $oldDBName = to_defaultDB($mainDB);
  $SQL = "SELECT ID, DBname FROM Projects";
  if($projectID) $SQL .= " WHERE ID='$projectID'";
  $SQL .= " ORDER BY ID";
  $tmpArr = $mainDB->fetchAll($SQL);
  $pairArr = array();
  foreach($tmpArr as $value){
    $pairArr[$value['ID']] = $value['DBname'];
  }
  back_to_oldDB($mainDB, $oldDBName);
  return $pairArr;
}
//***********************************************
// query protein gene id from Proteins database. used by parser
//***********************************************
function get_protein_GeneID($proteinKey, $AccessionType = '', $proteinDB){
  global $protein_id_sequence_arr;
  $SQL = '';
  $rt = '';
  if(!$proteinKey) return "";
  if(strpos($proteinKey, "DECOY") === 0) return "";
  
  if(!$AccessionType) $AccessionType = get_protein_ID_type($proteinKey);
   
  $AccessionType = strtoupper($AccessionType);
  if($AccessionType == 'ORF'){
    $SQL = "select EntrezGeneID from Protein_Class where LocusTag='$proteinKey'";
  }else if($AccessionType == 'GI'){
    $SQL = "select EntrezGeneID, GI, SequenceID from Protein_Accession where GI='".$proteinKey."'";
  }else if($AccessionType == 'ENS'){
    $SQL = "select ENSG, EntrezGeneID, SequenceID from Protein_AccessionENS where ENSP='".$proteinKey."'";
  }elseif($AccessionType == 'REFSEQ' or $AccessionType == 'NCBIACC' or $AccessionType == 'UNIPROTKB'){ 
    $giSplitArr = explode('.',$proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$giSplitArr[0]."'";
  }elseif($AccessionType == 'UNIPROT'){
    $SQL = "select EntrezGeneID, UniProtID, SequenceID from Protein_Accession where UniProtID='".$proteinKey."'";
  }elseif($AccessionType == 'IPI'){
		$tmp_key = preg_replace("/\..*$/", '', $proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_AccessionIPI where IPI='".$tmp_key."'";
  }else{
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$proteinKey."' order by ID desc";
  }
  
  if($SQL){
    $row = $proteinDB->fetch($SQL);
    if($row && $row['EntrezGeneID']){
      if($AccessionType == 'ENS'){
        $rt = ($row['EntrezGeneID'])? $row['EntrezGeneID']:$row['ENSG'];
      }else{
        $rt = $row['EntrezGeneID'];
      }
      
    }elseif(!$row || !$row['SequenceID']){    
      $protein_from_url_arr = get_protein_detail_from_url($proteinKey);
       
      if(isset($protein_id_sequence_arr) && ($AccessionType == 'GI' || $AccessionType == 'ENS')){
        if(isset($protein_from_url_arr['Sequence']) && $protein_from_url_arr['Sequence']){
          $protein_id_sequence_arr[$proteinKey] = $protein_from_url_arr['Sequence'];
        }
      }      
      if(isset($protein_from_url_arr['sequence']) && $protein_from_url_arr['sequence']){
        
        $protein_from_url_arr['SequenceID'] = insertNewSequence_or_getSequenceID($protein_from_url_arr,$proteinDB);
      }
      if(!$row){
        $GeneID = insert_into_Protein_Accession($protein_from_url_arr,$proteinDB);
      }else{
        if(isset($protein_from_url_arr['UniProt']) && $protein_from_url_arr['UniProt'] == $proteinKey){
          $SQL = "UPDATE Protein_Accession SET  
                `EntrezGeneID` = '".(isset($protein_from_url_arr['GeneID'])?$protein_from_url_arr['GeneID']:'')."',
                `SequenceID` = '".(isset($protein_from_url_arr['SequenceID'])?$protein_from_url_arr['SequenceID']:'')."'
                 WHERE `UniProtID` = '".$protein_from_url_arr['UniProt']."'";
        }elseif(isset($protein_from_url_arr['GI']) && $protein_from_url_arr['GI'] == $proteinKey){
          $SQL = "UPDATE Protein_Accession SET  
                `EntrezGeneID` = '".(isset($protein_from_url_arr['GeneID'])?$protein_from_url_arr['GeneID']:'')."',
                `SequenceID` = '".(isset($protein_from_url_arr['SequenceID'])?$protein_from_url_arr['SequenceID']:'')."'
                 WHERE `GI` = '".$protein_from_url_arr['GI']."'";
        }elseif(isset($protein_from_url_arr['Accession']) && $protein_from_url_arr['Accession'] == $proteinKey){
          $SQL = "UPDATE Protein_Accession SET  
                `EntrezGeneID` = '".(isset($protein_from_url_arr['GeneID'])?$protein_from_url_arr['GeneID']:'')."',
                `SequenceID` = '".(isset($protein_from_url_arr['SequenceID'])?$protein_from_url_arr['SequenceID']:'')."'
                 WHERE `Acc` = '".$protein_from_url_arr['Accession']."'";
        }else{
          $SQL = '';
        } 
        if($SQL){
          $affected_rows = $proteinDB->update($SQL);
        }  
      }
      if(isset($protein_from_url_arr['GeneID']) && $protein_from_url_arr['GeneID']){
        $insert_rt = insert_into_Protein_Class($protein_from_url_arr,$proteinDB);
        $rt = $protein_from_url_arr['GeneID'];
      }
    }
  }
  
  return $rt;
}//end function

//***********************************************
// query protein gene id from Proteins database. used by parser
//***********************************************
function get_protein_info($proteinKey, $AccessionType = '', $proteinDB){
  global $protein_id_sequence_arr;
  $SQL = '';
  $rt = '';
  if(!$proteinKey) return "";
   if(strpos($proteinKey, "DECOY") === 0) return "";
   
  if(!$AccessionType) $AccessionType = get_protein_ID_type($proteinKey);
  $AccessionType = strtoupper($AccessionType);
  if($AccessionType == 'ORF'){
    $SQL = "select EntrezGeneID from Protein_Class where LocusTag='$proteinKey'";
  }else if($AccessionType == 'GI'){
    $SQL = "select EntrezGeneID, GI, SequenceID from Protein_Accession where GI='".$proteinKey."'";
  }else if($AccessionType == 'ENS'){
    $SQL = "select ENSG, EntrezGeneID, SequenceID from Protein_AccessionENS where ENSP='".$proteinKey."'";
  }elseif($AccessionType == 'NCBIACC' or $AccessionType == 'UNIPROTKB'){ 
    $giSplitArr = explode('.',$proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$giSplitArr[0]."'";
  }elseif($AccessionType == 'UNIPROT'){
    $SQL = "select EntrezGeneID, UniProtID, SequenceID from Protein_Accession where UniProtID='".$proteinKey."'";
  }elseif($AccessionType == 'IPI'){
		$tmp_key = preg_replace("/\..*$/", '', $proteinKey);
    $SQL = "select EntrezGeneID, SequenceID  from Protein_AccessionIPI where IPI='".$tmp_key."'";
  }else{
    $SQL = "select EntrezGeneID, SequenceID  from Protein_Accession where Acc='".$proteinKey."' order by ID desc";
  }
  
  if($SQL){
    $row = $proteinDB->fetch($SQL);
    if($row && $row['SequenceID']){
      if($row['SequenceID']){
        $SQL = "SELECT `Sequence` FROM `Protein_Sequence` WHERE `ID`='".$row['SequenceID']."'";      
        $tmp_info_arr = $proteinDB->fetch($SQL);
        if($tmp_info_arr){
          $protein_from_url_arr[$AccessionType] = $proteinKey;
          $protein_from_url_arr['sequence'] = $protein_from_url_arr['sequence'] = $tmp_info_arr['Sequence'];
        }
      }
    }elseif(!$row || !$row['SequenceID']){    
      $protein_from_url_arr = get_protein_detail_from_url($proteinKey);    
      if(isset($protein_id_sequence_arr) && isset($protein_from_url_arr['sequence']) && ($AccessionType == 'GI' || $AccessionType == 'ENS')){
        if($protein_from_url_arr['Sequence']){
          $protein_id_sequence_arr[$proteinKey] = $protein_from_url_arr['Sequence'];
        }
      }      
      if(isset($protein_from_url_arr['sequence']) && $protein_from_url_arr['sequence']){

        $protein_from_url_arr['SequenceID'] = insertNewSequence_or_getSequenceID($protein_from_url_arr,$proteinDB);
      }
      
      if(!$row){
        $GeneID = insert_into_Protein_Accession($protein_from_url_arr,$proteinDB);          
      }elseif(isset($protein_from_url_arr['SequenceID']) && $protein_from_url_arr['SequenceID']){      
      
        if(isset($protein_from_url_arr['UniProt']) && $protein_from_url_arr['UniProt'] == $proteinKey){
          $SQL = "UPDATE Protein_Accession SET ";
          if(!$row['EntrezGeneID'] && isset($protein_from_url_arr['GeneID']) && $protein_from_url_arr['GeneID']){
            $SQL .= " EntrezGeneID = '".$protein_from_url_arr['GeneID']."',";
          }       
          $SQL .= " SequenceID = '".$protein_from_url_arr['SequenceID']."'
                    WHERE `UniProtID` = '".$protein_from_url_arr['UniProt']."'";
        }elseif(isset($protein_from_url_arr['GI']) && $protein_from_url_arr['GI'] == $proteinKey){
          $SQL = "UPDATE Protein_Accession SET ";
          if(!$row['EntrezGeneID'] && isset($protein_from_url_arr['GeneID']) && $protein_from_url_arr['GeneID']){
            $SQL .= " EntrezGeneID = '".$protein_from_url_arr['GeneID']."',";
          }       
          $SQL .= " SequenceID = '".$protein_from_url_arr['SequenceID']."'
                    WHERE `GI` = '".$protein_from_url_arr['GI']."'";
        }elseif(isset($protein_from_url_arr['Accession']) && $protein_from_url_arr['Accession'] == $proteinKey){
         $SQL = "UPDATE Protein_Accession SET ";
          if(!$row['EntrezGeneID'] && isset($protein_from_url_arr['GeneID']) && $protein_from_url_arr['GeneID']){
            $SQL .= " EntrezGeneID = '".$protein_from_url_arr['GeneID']."',";
          }       
          $SQL .= " SequenceID = '".$protein_from_url_arr['SequenceID']."'
                    WHERE `Acc` = '".$protein_from_url_arr['Accession']."'";
        }else{
          $SQL = '';
        }        
        if($SQL){  
          $affected_rows = $proteinDB->update($SQL);
        }  
      }
      if(isset($protein_from_url_arr['GeneID']) && $protein_from_url_arr['GeneID']){
        $insert_rt = insert_into_Protein_Class($protein_from_url_arr,$proteinDB);
      }  
    }
  }
  return $protein_from_url_arr;
}//end function


function insertNewSequence_or_getSequenceID($arr,$proteinDB){   
     if(
  (!isset($arr['Accession']) or !$arr['Accession']) and 
  (!isset($arr['GI']) or !$arr['GI']) and
  (!isset($arr['UniProt']) or !$arr['UniProt'])
  ) return '';
  $sequence = $arr['sequence'];
  if(!$sequence) return '';
  
  $SQL = "SELECT `ID`, `Sequence` FROM `Protein_Sequence` WHERE `Sequence`='$sequence'";
  $Protein_Sequence_arr = $proteinDB->fetch($SQL);
  if(!$Protein_Sequence_arr){
    $SQL ="INSERT INTO Protein_Sequence SET
          `Sequence`='$sequence'";
    $SequenceID = $proteinDB->insert($SQL);
    if($SequenceID){
      return $SequenceID;
    }else{
      return '';
    }
  }else{  
    return $Protein_Sequence_arr['ID'];
  }
}

function insert_into_Protein_Accession($arr,$proteinDB){
  if(
  (!isset($arr['Accession']) or !$arr['Accession']) and 
  (!isset($arr['GI']) or !$arr['GI']) and
  (!isset($arr['UniProt']) or !$arr['UniProt'])
  ) return '';
  
  $SQL ="INSERT INTO Protein_Accession SET  
        `EntrezGeneID` = '".((isset($arr['GeneID']))?trim($arr['GeneID']):'')."',
        `GI` = '".((isset($arr['GI']))?$arr['GI']:'')."',
        `Acc` = '".((isset($arr['Accession']))?trim($arr['Accession']):'')."',
        `Acc_Version` = '".((isset($arr['Acc_Version']))?trim($arr['Acc_Version']):'')."',
        `UniProtID` = '".((isset($arr['UniProt']))?trim($arr['UniProt']):'')."',
        `Description` = '".((isset($arr['description']))?mysqli_real_escape_string($proteinDB->link, $arr['description']):'')."',
        `Source` = '".((isset($arr['Source']))?$arr['Source']:'')."',
        `SequenceID` = '".((isset($arr['SequenceID']))?$arr['SequenceID']:'')."',
        `Status` = '".((isset($arr['Status']))?$arr['Status']:'')."',
        `TaxID` = '".((isset($arr['TaxID']))?trim($arr['TaxID']):'')."'";
  $ID = $proteinDB->insert($SQL);
  if($ID && isset($arr['GeneID']) && $arr['GeneID']){
    return $arr['GeneID'];
  }else{
    return '';
  }     
}

function insert_into_Protein_Class($arr,$proteinDB){
  if(!$arr['GeneID']) return '';       
  $SQL = "INSERT INTO `Protein_Class` SET
          `EntrezGeneID` = '".trim($arr['GeneID'])."',
          `LocusTag` = '".((isset($arr['LocusTag']))?trim($arr['LocusTag']):'')."',
          `GeneName` = '".((isset($arr['GeneName']))?trim($arr['GeneName']):'')."',
          `GeneAliase` = '".((isset($arr['GeneAliase']))?mysqli_real_escape_string($proteinDB->link, $arr['GeneAliase']):'')."',
          `TaxID` = '".((isset($arr['TaxID']))?trim($arr['TaxID']):'')."',
          `Description` = '".((isset($arr['Description']))?mysqli_real_escape_string($proteinDB->link, $arr['Description']):'')."',
          `Status` = '".((isset($arr['Status']))?$arr['Status']:'')."'";
  return $rt = $proteinDB->execute($SQL);
}              

//***********************************************
// by pass a protein ID, return hit record used by parser
//***********************************************
function get_protin_info($proteinKey, &$AccessionType, $proteinDB, $hasGeneID = ''){
  //$rt = array('EntrezGeneID'=>'', 'Description'=>'', 'SequenceID'=>'', 'Sequence'=>'');
  $SQL = '';
  $rt = '';
  $row = array();
    
  $geneIDnotNull = '';
  if(!$proteinKey) return "";
  if(!$AccessionType) $AccessionType = get_protein_ID_type($proteinKey);
  $AccessionType = strtoupper($AccessionType);
  if($hasGeneID){
    $geneIDnotNull = ' and EntrezGeneID>0';
  }  
  if($AccessionType == 'ORF'){
    $SQL = "SELECT `EntrezGeneID` FROM `Protein_Class` WHERE `LocusTag`='$proteinKey'";
    $row = $proteinDB->fetch($SQL);
    if($row && $row['EntrezGeneID']){
      $gene_id = $row['EntrezGeneID'];
      $SQL = "SELECT EntrezGeneID,
              Description,
              SequenceID 
              FROM `Protein_Accession` 
              WHERE EntrezGeneID='$gene_id'
              AND Acc_Version LIKE 'NP_%'
              ORDER BY Acc_Version DESC
              LIMIT 1";
      $row = $proteinDB->fetchAll($SQL);
      if($row){
         $row =  $row[0];
      }
    }  
  }elseif($AccessionType == 'IPI'){
    $SQL = "SELECT EntrezGeneID,
            Description,
            SequenceID 
            FROM Protein_AccessionIPI 
            WHERE IPI='$proteinKey'";
     $row = $proteinDB->fetch($SQL);
  }elseif($AccessionType == 'ENS'){
    $SQL = "SELECT EntrezGeneID,
            Description,
            SequenceID 
            FROM Protein_AccessionENS 
            WHERE ENSP='$proteinKey'";   
     $row = $proteinDB->fetch($SQL);
  }else{
    $acc_field = '';
    if($AccessionType == 'GI'){
      $acc_field = 'GI';
    }elseif($AccessionType == 'UNIPROT'){
      $acc_field = 'UniProtID';
    }elseif($AccessionType == 'REFSEQ'){
      $acc_field = 'Acc_Version';
    }else{    //'uniprotkb', 'NCBIAcc', 'Worm', 
      $acc_field = 'Acc';
    }
    if($acc_field){
      $SQL = "SELECT EntrezGeneID,
              Description,
              SequenceID 
              FROM `Protein_Accession` 
              WHERE $acc_field='$proteinKey' ORDER BY SequenceID DESC LIMIT 1";
       $row = $proteinDB->fetch($SQL);
    }  
  }     
  if(!isset($row) || !$row){
    return array('EntrezGeneID'=>'', 'Description'=>'', 'SequenceID'=>'', 'Sequence'=>'');
  }else{  
    $row['Sequence'] = '';
    if($row['SequenceID']){
      $SequenceID = $row['SequenceID'];
      $SQL = "SELECT `Sequence` FROM `Protein_Sequence` WHERE ID=$SequenceID";
      $tmp_row = $proteinDB->fetch($SQL);
      if($tmp_row){
        $row['Sequence'] = $tmp_row['Sequence'];
      }
    }
    return $row;
  }
}//end function

//***********************************************
// by pass a protein key, return protein key type
//***********************************************
function get_protein_ID_type($proteinKey=''){
  $proteinKey = trim($proteinKey);
  $rt = '';
  if(!$proteinKey){
    return $rt;
  }else{
    $proteinKey = strtoupper($proteinKey);
  }
 //AGAP1_HUMAN
  if(preg_match("/^GI\|[0-9]+?/i", $proteinKey) or is_numeric($proteinKey))	{
		$rt =  "GI";
  }elseif(preg_match("/^sp\||^tr\|/", $proteinKey))	{
		$rt = "uniprotkb";
  }elseif(preg_match("/^[NXZAY]P_\d+/", $proteinKey))	{
		$rt =  "REFSEQ";
  }elseif(preg_match("/^HIP\d/", $proteinKey))	{
    $rt = "Hinvdb";
  }elseif(preg_match("/^osa1[0-9]+/", $proteinKey))	{
    $rt = "TigrOsa";
  }elseif(preg_match("/_[A-Z]+/", $proteinKey))	{
    $rt = "UniProt";
  }elseif(preg_match("/^At[1-9]g[0-9]+|^SPU_[0-9]+|^Bra[0-9]+\.\d/i", $proteinKey))	{
    $rt = "ENS";
    echo "1<br>";   
  }elseif(preg_match("/^AC\d+\.\d\_FGP\d+|^GRMZM|^AGAP|^ACYPI|^LOC_Os|^AT[1-9C]G\d+|^PPA\d+|^FBpp|^DappuP|^BRADI|^SP[A-C][A-Z].*\-\d|^CADAFUAP\d|^Traes/", $proteinKey))	{
		$rt = "ENS";
    echo "2<br>";    
  }elseif(preg_match("/^ENS|^FBpp|^CG[0-9]+?-P[A-Z]|^GSTENP|^NEWSINFRUP/", $proteinKey))	{
		$rt = "ENS";
    echo "3<br>";
  
	}elseif(preg_match("/^Y[A-Z]+?[0-9]+?[A-Z]/", $proteinKey))	{
		$rt =  "ORF"; //yeast ORF
	}elseif(preg_match("/^IPI[0-9]+/", $proteinKey))	{
		$rt =  "IPI";
	}elseif(preg_match("/^HIT[0-9]+/", $proteinKey))	{
		$rt =  "Jbirc";
	}elseif(preg_match("/^OSA1[0-9]+/", $proteinKey))	{
		$rt =  "TigrOsa";
	}elseif(preg_match("/^DDB[0-9]+|^TA[0-9][0-9][0-9][0-9][0-9]|^SP[A-C][A-Z]/", $proteinKey))	{
		$rt =  "DDB";
	}elseif(preg_match("/^AT[1-9]G[0-9]+/", $proteinKey))	{
		$rt =  "TigrAt";
	}elseif(preg_match("/^TB[0-9]+\./", $proteinKey))	{
		$rt =  "TigrTb";
	}elseif(preg_match("/^WP:/", $proteinKey))	{
		$rt =  "Worm";
  }elseif(preg_match("/[OPQ][0-9][A-Z0-9]{3}[0-9]|[A-NR-Z][0-9]([A-Z][A-Z0-9]{2}[0-9]){1,2}/", $proteinKey))	{
    $rt = "uniprotkb";
  
	//}else if(preg_match("/^[A-NR-Z][0-9][A-Z][A-Z0-9][A-Z0-9][0-9](\-[0-9]+)?$/i", $proteinKey)
  //  or preg_match("/^[OPQ][0-9][A-Z0-9][A-Z0-9][A-Z0-9][0-9](\-[0-9]+)?$/i", $proteinKey)
  //  or preg_match("/^[A-Z][0-9|A-Z]+?\.[0-9]/", $proteinKey)
  //  )	{
  //  $rt = "uniprotkb";
 
	}else{
    $rt = "NCBIAcc";
  }
  return $rt;
}
//***********************************************
// by pass a gene ID, return gene type
//***********************************************
function get_Gene_ID_type($geneKey='')	{
  $rt = '';
  if(is_numeric($geneKey)){
    $rt = 'NCBI';
  }else{
    $rt = 'ENS';
  }
  return $rt;
}
//***********************************************
// by pass a gene ID, return gene name
//***********************************************
function get_Gene_Name($geneKey='', $proteinDB)	{
  $SQL = '';
  $rt = '';
  $genetype = get_Gene_ID_type($geneKey);
  if($genetype == 'NCBI'){
    $SQL = "select GeneName from Protein_Class where EntrezGeneID='$geneKey'";
  }else if($genetype == 'ENS'){
    $SQL = "select GeneName from Protein_ClassENS where ENSG='$geneKey'";
  }
  if($SQL){
    $row = $proteinDB->fetch($SQL);
    if($row) $rt = $row['GeneName'];
  }
  return $rt;
}
//***********************************************
// from a tring to protien Key
// gi|6319314|ref|NP_009396.1|
//***********************************************
function parse_protein_Acc($str)	{
  $protein_key = trim($str);
  
  $protein_key =preg_replace("/tr\||sp\||gi\||IPI:/", "",$protein_key);
  $protein_key = trim(preg_replace("/\|.*+| .*/", '', $protein_key));
  return $protein_key;
}


//***********************************************
// pass a protein key, geneID and LocusTag
// retrun URLs string;
//***********************************************
function get_URL_str($proteinKey='', $geneID='', $locusTag='', $geneName='', $callBy=''){
  global $URLS;
  $rt = '';
  $type = '';
  $species = '';
  $type = get_protein_ID_type($proteinKey);
  if($type){
    $url = '';
    if($type == 'GI' or $type == 'NCBIAcc' or $type == 'REFSEQ'){
      $url = $URLS["NCBI_PROTEIN"].$proteinKey;
      
    }else if($type == 'UniProt' or $type == 'uniprotkb'){
      $proteinKey = preg_replace("/SP\||\|/",'', strtoupper($proteinKey));
      $url = $URLS["UNIPROT_PROTEIN"] .$proteinKey ;
    }else if($type == 'ORF'){
      $locusTag = $proteinKey;
    }else if($type == 'ENS'){
      $species = get_ENS_species($proteinKey); 
      $url = $URLS["ENS_PROT"]['domain']. $species. $URLS["ENS_PROT"]['cgi']. $proteinKey;
    }else if($type == 'Worm'){
      $url = $URLS["WORM_PROTEIN"] . $proteinKey;
    }else if($type == 'IPI'){
      $url = $URLS["IPI_PROTEIN"] . $proteinKey;
    }else if($type == 'Jbirc'){
      $url = $URLS["JBIRC_PROTEIN"] . $proteinKey;
    }else if($type == 'TigrAt'){
      $url = $URLS["TIGR_PROTEIN"] . $proteinKey;
    }else if($type == 'TigrTb'){
      $url = $URLS["TIGRTB_PROTEIN"] . $proteinKey;
      if(!$callBy) $rt .= "[<a href=$url target=_blank class=button>Tigr</a>]";
    }
    if($callBy == 'comparison'){
      if($url){
        $rt = "<a href=$url target=_blank>$proteinKey</a>";
      }else{
        $rt = "$proteinKey"; 
        $locusTag = '';
      }  
    }else if($url){
      $rt = "[<a href=$url target=_blank class=button>$type</a>]";
    }
  }
  if($geneID){
    $geneID = trim($geneID);
    $biogrid_url = '';
    $url = '';
    if(get_Gene_ID_type($geneID)=='NCBI'){
      $url = $URLS["ENTREZ_GENE"] . $geneID;
      $biogrid_url = $URLS["BIOGRID"]. $geneID . "&identifierType=entrez_gene";
    }else{
      $species = get_ENS_species($geneID);
      if($species){
        $url = $URLS["ENS_GENE"]["domain"] . $species . $URLS["ENS_GENE"]["cgi"].$geneID;
        $biogrid_url = $URLS["BIOGRID"].$geneID . "&identifierType=Ensembl";
      }
    }
    if(!$callBy || $callBy == 'bait_info'){
      $rt .= "[<a href=$url target=_blank class=button>Gene</a>]";
      
    }else{
      $rt = "<a href=$url target=_blank>$geneName</a>";
    }
    if($biogrid_url){
      if($callBy == 'comparison'){
        $rt .= "<br>[<a href=$biogrid_url target=_blank>BioGRID</a>]";
      }elseif($callBy == 'bait_info'){
        $rt .= "[<a href=$biogrid_url target=_blank class=button>BioGRID</a>]";
      }else{
        $rt .= "&nbsp;&nbsp;[<a href=$biogrid_url target=_blank class=button>BioGRID</a>]";
      }  
    }
  }
  if(get_protein_ID_type($locusTag) == 'ORF'){
    $url = $URLS["SGD"] . $locusTag;
    if(!$callBy){
      $rt .= "[<a href=$url target=_blank class=button>SGD</a>]";
    }else{
      $rt .= "[<a href=$url target=_blank>SGD</a>]";
    }
  }
  return $rt;
}
//***********************************************
// pass a protein key, geneID and LocusTag
// retrun URLs string;
//***********************************************
function get_ENS_species($ENS_ID=''){
  $species = '';
  $ENS_specs = array(
    "ENS" => "Homo_sapiens",
		"ENSMMU" => "Macaca_mulatta",
		"ENSCAF" => "Canis_familiaris",
		"ENSMUS" => "Mus_musculus",
		"ENSXET" => "Xenopus_tropicalis",
		"ENSGAL" => "Gallus_gallus",
		"ENSRNO" => "Rattus_norvegicus",
		"ENSANG" => "Anopheles_gambiae",
		"ENSDAR" => "Danio_rerio",
		"ENSBTA" => "Bos_taurus",
		"ENSAPM" => "Apis_mellifera",
		"NEWSINFRU" => "Fugu_rubripes",
		"GSTEN"  => "Tetraodon_nigroviridis",
    "FB"     => "Drosophila_melanogaster"
    );
  if(preg_match("/(\w*)[P|G]/i", strtoupper($ENS_ID), $matchs)){
    if(isset($ENS_specs[$matchs[1]])) $species = $ENS_specs[$matchs[1]];
  }else if(preg_match("/^[A-Z].+\.[0-9]/", $ENS_ID))	{
    $species = "Caenorhabditis_elegans";
  }
  return $species;
}
//************************************************
// passed mysqlDB object and plate id to add 
//carryover and spill over to gel based hits
//Any mass spec identifications of a protein as a 
//bait that was analyzed 'up-stream' (12 wells) 
//on the same plate.
//************************************************
function add_plate_carry_over($hitsDB, $Plate_ID=0){
    define ("BEFOREWELLS", 12);
  //check up-stream 12 wells
  
  //genareate an array which contains Bait GeneID of the Well sample
  // $BaitGeneIDs[$WellCode]: $BaitGeneIDs['A1'] - $BaitGeneIDs['A12]
  $BaitGeneIDs = array();
  $SQL = "select W.WellCode, H.BaitID, H.GeneID, H.ID
              from PlateWell W, Hits H 
              where W.ID=H.WellID and W.PlateID='$Plate_ID' 
              ORDER BY W.WellCode";
  $HitsArr = $hitsDB->fetchAll($SQL); 
  
  for($i=0; $i<count($HitsArr); $i++){          
    $SQL = "SELECT GeneID FROM Bait where ID='".$HitsArr[$i]['BaitID']."'";  
    $myBait = $hitsDB->fetch($SQL);
    if(!substr($HitsArr[$i]['WellCode'], 1, 1)){
      $HitsArr[$i]['WellCode'] = substr_replace($HitsArr[$i]['WellCode'], '', 1, 1);    
    }      
    if($myBait['GeneID'])  $BaitGeneIDs[$HitsArr[$i]['WellCode']] = $myBait['GeneID'];
  }
  
  // then let each hit gene Name walks through the array
  $A2H_array = array("A","B","C","D","E","F","G","H");
  $wellCode_array = array(); // $wellCode_array[0] = "A1", $wellCode_array[96] = "H12", 
  $wellNum_array = array();  //  $wellNum_array["A1"] = 0, $wellNum_array["H12"] = 96,
  $well_counter = 0;         // well from 0 -> 95
  for($row=0; $row < count($A2H_array); $row++){
    for($col=1; $col <= 12; $col++){
      $wellCode_array[$well_counter] = $A2H_array[$row].$col;
      $wellNum_array[$A2H_array[$row].$col] = $well_counter;
      $well_counter++;
    }
  }
  
  for($i=0; $i<count($HitsArr); $i++){
    $stop = 0;
    $row = 0;  
    if(!$HitsArr[$i]['GeneID']) $stop = 1;
    //start to check carry over
    $current_wellNum = $wellNum_array[$HitsArr[$i]['WellCode']];
    
    $loop = 0;
    while($current_wellNum > 0 and $loop < BEFOREWELLS and !$stop){
      $current_wellNum--;  //go backward 12 times    
      if(isset($BaitGeneIDs[$wellCode_array[$current_wellNum]]) and $BaitGeneIDs[$wellCode_array[$current_wellNum]] == $HitsArr[$i]['GeneID']){
       //this hit is a carry over
       //CO is "Carry Over". Set the record UserID = 0     
        $SQL = "INSERT INTO HitNote SET
                 HitID='".$HitsArr[$i]['ID']."', 
                 FilterAlias='CO',
                 Note='auto detected', 
                 UserID=0, 
                 Date=now()";
        $hitsDB->insert($SQL);
        break;
      }
      $loop++; //only loop backward 12 times from the start well.
    }//end while
  }//end while
}
//************************************************
// return band info array for parser hits
// return array('ID', 'TaxID', 'BaitID', 'ProjectID', 
// 'GeneID', 'WellID')
//************************************************
function get_band_arr($hitsDB, $band_id){
    $band_arr = array();
    if(!$hitsDB or !$band_id) return $band_arr;
    $sql = "SELECT B.ID, E.TaxID, E.BaitID, E.ProjectID FROM Experiment E, Band B WHERE E.ID = B.ExpID and B.ID='$band_id'";
    $band_arr = $hitsDB->fetch($sql);
    if($band_arr){
      $band_arr['GeneID'] = '';
      $band_arr['WellID'] = '';
    }
    $sql = "SELECT GeneID from Bait where ID='".$band_arr['BaitID']."'";
    $tmp_arr = $hitsDB->fetch($sql);
    
    if($band_arr and $tmp_arr){
      $band_arr['GeneID'] = $tmp_arr['GeneID'];
    }
    $SQL = "select ID from PlateWell where BandID='$band_id'"; 
    $well_arr = $hitsDB->fetch($SQL);
    if($band_arr and $well_arr) {
       $band_arr['WellID'] =  $well_arr['ID'];
    }
    return $band_arr;
}
function TaxID_list_($mainDB, $focus_value){
  $oldDBname = to_defaultDB($mainDB);
  $SQL = "SELECT TaxID, Name, Display FROM ProteinSpecies WHERE Species = 'Y' order by ID";  
  $SpeciesArr2 = $mainDB->fetchAll($SQL);
  back_to_oldDB($mainDB, $oldDBname);
  for($i=0; $i<count($SpeciesArr2); $i++){
    $Name = $SpeciesArr2[$i]['Name'];
?>
  <option value="<?php echo $SpeciesArr2[$i]['TaxID'];?>"<?php echo ($SpeciesArr2[$i]['TaxID']==$focus_value)?" selected":"";?>><?php echo $Name;?><br>
<?php
  }
}
function LogSize($logfile, $maxKB=3000){
  if(!is_file($logfile)) return;
  $fsize = filesize($logfile)/1024;
  if($fsize > $maxKB){
    rename($logfile, $logfile."_".@date("Ymj"));
    if($fd = fopen($logfile, 'w')){
       fclose($fd);
       chmod($logfile, 0757);
     }
  }
}
//----------------------------------------------
function fatal_Error($msg='', $line=0, $log_file=''){
//----------------------------------------------
  global $start_time;
  if(!$start_time)$start_time=@date("D M j G:i:s T Y"); 
  $msg  = "Fatal Error--<font color=\"#FF0000\">$msg</font>;<br>\n";
  $msg .= "Script Name: " . $_SERVER['PHP_SELF']. ";<br>\n";
  $msg .= "Start time: ". $start_time . ";";
  if($line){
    $msg .= " Line number: $line;";
  }
  if($log_file){
    write_Log($msg, $log_file);
  }else{
    write_Log($msg);
  }
  echo $msg."\n";
  exit;
}
//---------------------------------------------
function write_Log($msg, $log_file=''){
//---------------------------------------------
  global $logfile; 
  global $debug;
  if(!$log_file and $logfile){
    $log_file = $logfile;
  }
  $fileDir = dirname($log_file);
  if(!is_dir($fileDir)){
    mkdir($fileDir, 0777);
  }
  if($debug) echo $msg."\n";
  $log = fopen($log_file, 'a+');
  if(!$log){
    echo "can not open the log file to write: $log_file"; exit;
  }
  fwrite($log, "\r\n" . $msg);
  fclose($log);
}
//---------------------------------------------
//check if the prophot hits in TppProtein table 
//---------------------------------------------
function tppxml_parsed($tppTable, $xmlFile){
  global $hitsDB;
  $rt = 0;  
  $SQL = "select ID, BandID from $tppTable where XmlFile='$xmlFile' limit 1";
  $tpp_arr = $hitsDB->fetch($SQL);
  if($tpp_arr){
    $msg= "the xml file $xmlFile has been parsed with band ID ". $tpp_arr['BandID'];
    write_Log($msg);
    $rt = 1;
  }
  return $rt;
}

//-----------------------------------------
// get sequence, description from internet
//-----------------------------------------
function get_protein_from_url($proteinKey){
  //$rt = array('MW'=>'', 'Size'=>'', 'description'=>'', 'sequence'=>'', 'protein_ID_type'=>'');
  $rt = array();
  if(preg_match("/\|(.+)/", $proteinKey, $matches)){
    $proteinKey = $matches[1];
    $proteinKey = str_replace("|", "", $proteinKey);
  }
  $type = get_protein_ID_type($proteinKey);
  if($type){
    if($type == 'GI' or $type == 'REFSEQ' or $type=='NCBIAcc'){
      $rt = get_protein_from_url_NCBI($proteinKey);
    }else if($type == 'ENS'){
      $rt = get_protein_from_url_ENS($proteinKey);
    }else if($type == 'IPI'){
      
      $rt = get_protein_from_url_IPI($proteinKey);
    }else if($type == 'Worm'){
      $rt = get_protein_from_url_Wormbase($proteinKey);
    }else if($type == "UniProt" or $type == "uniprotkb"){
      $rt = get_protein_from_url_UniProt($proteinKey, $type);
    }
    $rt['protein_ID_type'] = $type;
  }
  //if not return
  if(!$rt and $type != 'GI' and $type != 'NCBIAcc'){
    $rt = get_protein_from_url_NCBI($proteinKey);
    $rt['protein_ID_type'] = $type;
  }  
  return $rt; 
}

//-----------------------------------------
// get more info.(sequence, description, GeneID, GeneName, TaxID  from internet)
//-----------------------------------------
function get_protein_detail_from_url($proteinKey){
  $rt = array();
  if(preg_match("/\|(.+)/", $proteinKey, $matches)){
    $proteinKey = $matches[1];
    $proteinKey = str_replace("|", "", $proteinKey);
  }
  $type = get_protein_ID_type($proteinKey);
  if($type){
    if($type == 'GI' or $type == 'REFSEQ' or $type=='NCBIAcc'){
      $rt = get_protein_detail_from_url_NCBI($proteinKey);
    }else if($type == 'ENS'){
      //$rt = get_protein_from_url_ENS($proteinKey);
    }else if($type == 'IPI'){
      $rt = get_protein_from_url_IPI($proteinKey);
    }else if($type == 'Worm'){
      //$rt = get_protein_from_url_Wormbase($proteinKey);
    }else if($type == "UniProt" or $type == "uniprotkb"){
      $rt = get_protein_from_url_UniProt($proteinKey, $type);
    }    
    if($rt) $rt['protein_ID_type'] = $type;
  }
  return $rt; 
}

//------------------------------------
//get protein sequence from internet.
//www.ensembl.org
function get_protein_from_url_IPI($proteinKey){
  global $URLS;
  
  return array();
  
  $tmp_key = "IPI-acc:".$proteinKey;
  if(isset($URLS["IPI_PROTEIN_dat"])){
    $URL = str_replace("TYPE:PROTEINKEY", $tmp_key, $URLS["IPI_PROTEIN_dat"]);
  }else{
    $URL= "http://srs.ebi.ac.uk/srsbin/cgi-bin/wgetz?-e+[".$tmp_key."]+-vn+2";
  }
  $response = open_url($URL);
  return parse_IPI_protein_txt($response);
}
//----------------------------------------
// get wissprot protein sequence from internet
function get_protein_from_url_UniProt($proteinKey, $type=''){ 
  global $URLS;
  if(!$proteinKey) return 0;
  $the_type = 'uniprot-AccNumber'; 
  if(isset($URLS["UNIPROT_PROTEIN"])){
    $URL = $URLS["UNIPROT_PROTEIN"]."$proteinKey.txt";
  }else{
    $URL = "http://www.uniprot.org/uniprot/$proteinKey.txt";
  }
 
  //echo "$URL\n";
  $response = open_url($URL);
  $rt = parse_uniport_protein_txt($response);
  return $rt;
}
//----------------------------------------
// get protein detail info from NCBI internet 
function get_protein_detail_from_url_NCBI($proteinKey){ 
  global $URLS;
  if(!$proteinKey) return 0;
  if(isset($URLS["NCBI_PROTEIN_GENPEPT"])){
    $URL = $URLS["NCBI_PROTEIN_GENPEPT"].$proteinKey;
  }else{
    $URL = "http://www.ncbi.nlm.nih.gov/sviewer/viewer.fcgi?sendto=on&dopt=genpept&val=$proteinKey";
  }
  //echo "$URL\n";
  $response = open_url($URL);
  $rt = parse_ncbi_genpept_txt($response);
  
  echo " ";
  flush();
  return $rt;
}

//----------------------------------------
// get worm protein sequence from internet
function get_protein_from_url_Wormbase($proteinKey){
  global $URLS;
  if(!$proteinKey) return 0;
  $rt = array();
  if(isset($URLS["WORM_PROTEIN"])){
    $URL = $URLS["WORM_PROTEIN"] . urlencode($proteinKey);
  }else{
    $URL = "http://www.wormbase.org/db/seq/protein?name=".  urlencode($proteinKey);
  }
  $response = open_url($URL);
  if(preg_match("/Amino Acid Sequence<[\w\W\s]+<pre>([\w\W\s]+)<\/pre>/",$response, $matches)){
    if(isset($matches[1])){
      $rt['sequence'] = preg_replace("/\s+/", '',$matches[1]);
      $rt['Sequence'] = $rt['sequence'];
    }
  }
  return $rt;
}





//----------------------------------------
// get NCBI protein sequence from internet
function get_protein_from_url_NCBI($Acc){
  global $URLS;
  if(!$Acc) return 0;
  $rt = array();
  if(isset($URLS["NCBI_PROTEIN_FASTA"])){
    $URL = $URLS["NCBI_PROTEIN_FASTA"] . $Acc;
  }else{
    $URL = "http://www.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?rettype=fasta&retmode=text&db=protein&id=$Acc";
  }
  $response = open_url($URL);
  //if(preg_match("/^>gi\|(.+)\n([A-Za-z\s]+)/",$response, $matches)){
  if(preg_match("/^>g?i?\|?(.+)\n([A-Za-z\s]+)/",$response, $matches)){
    $tmp_arr = preg_split("/ /", $matches[1], 2);
    if(isset($tmp_arr[1])){
      $rt['description'] = $tmp_arr[1];
    }
    if(isset($matches[2])){
      $rt['sequence'] = preg_replace("/\s+/", '',$matches[2]);
      $rt['Sequence'] = $rt['sequence'];
    }
  }
  return $rt;
}





function get_protein_from_url_NCBI_($GI){
  global $URLS;
  if(!$GI) return 0;
  $rt = array();
  if(isset($URLS["NCBI_PROTEIN_FASTA"])){
    $URL = $URLS["NCBI_PROTEIN_FASTA"] . $GI;
  }else{
    $URL = "http://www.ncbi.nih.gov/entrez/eutils/efetch.fcgi?rettype=fasta&retmode=text&db=protein&id=$GI";
  }
  $response = open_url($URL);
  if(preg_match("/^>gi\|(.+)\n([A-Za-z\s]+)/",$response, $matches)){
    $tmp_arr = preg_split("/ /", $matches[1], 2);
    if(isset($tmp_arr[1])){
      $rt['description'] = $tmp_arr[1];
    }
    if(isset($matches[2])){
      $rt['sequence'] = preg_replace("/\s+/", '',$matches[2]);
      $rt['Sequence'] = $rt['sequence'];
    }
  } 
  return $rt;
}
//------------------------------------
//get protein sequence from internet.
//www.ensembl.org
function get_protein_from_url_ENS($proteinKey){
  global $URLS;
  //$rt = array('MW'=>'', 'description'=>'', 'sequence'=>'');
  $rt = array();
  $species = get_ENS_species($proteinKey);
  if($species){
    if(isset($URLS["ENS_PROT_SEQUENCE"])){
      $URL = $URLS["ENS_PROT_SEQUENCE"]["domain"] . $species . $URLS["ENS_PROT_SEQUENCE"]["cgi"] . $proteinKey;
    }else{
      $URL = "http://www.ensembl.org/". $species ."/Component/Transcript/Web/ProteinSeq?p=". $proteinKey;
    }  
    $response = open_url($URL);    
    if(preg_match("/^<div class/", $response)){
      if(preg_match("/name=\"_query_sequence\" value=\"(\w+)\"/", $response, $matches)){
        $rt['sequence'] = $matches[1];
        $rt['Sequence'] = $rt['sequence'];
      }
    }
  }
  return $rt;
}

//-------------------------------------------
//open url and return containts
function open_url($url){
  $timeout = 10; 
  $rt = '';
  $fp = @fopen("$url", 'rb'); 
  if (!$fp) {
    //echo "Unable to open $url\n";
  } else {
    stream_set_blocking($fp, TRUE); 
    stream_set_timeout($fp, $timeout);
    $res = stream_get_contents($fp);
    
    $info = stream_get_meta_data($fp);
    fclose($fp);
    if ($info['timed_out']) {
        echo 'Connection timed out!';
    } else {
        $rt = $res;
    }
  }
  return $rt;
}
//--------------------------------------------
//remvoe html tags
function html2text($html){
  $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript 
  '@<style[^>]*?>.*?</style>@siU',                   // Strip style tags properly 
  '@<[\/\!]*?[^<>]*?>@si',                           // Strip out HTML tags 
  '@<![\s\S]*?–[ \t\n\r]*>@'                        // Strip multi-line comments including CDATA 
  ); 
  $text = preg_replace($search, "", html_entity_decode($html));
  $pat[0] = "/^\s+/"; 
  $pat[2] = "/\s+\$/"; 
  $rep[0] = ""; 
  $rep[2] = ""; 
  $text = preg_replace($pat, $rep, trim($text)); 
  return $text;
}

function parse_ncbi_genpept_txt($text){
  $rt = array();
  $rt['description'] = '';
  $rt['Accession'] = '';
  $rt['Acc_Version'] = '';
  $rt['GI'] = '';
  $rt['sequence'] = '';
  $rt['TaxID'] = '';
  $rt['GeneName'] = '';
  $rt['GeneAliase'] = '';
  $rt['LocusTag'] = '';
  $rt['GeneID'] = '';
  $rt['size'] = '';
  $is_cds = 0;
  $point = '';
  $text = html2text($text);
  $lines = preg_split("/\n/", $text);
  foreach($lines as $buffer){
    if(preg_match('/^DEFINITION\s*(.+)/', $buffer, $matches)){
      $point = 'DEFINITION';
      $rt['description'] = $matches[1];
    }elseif($point == 'DEFINITION'){  
      if(preg_match('/^ACCESSION\s*(.+)/', $buffer, $matches)){
        $rt['Accession'] = $matches[1];
        $point = 'ACCESSION';
        continue;
      }else{
        $rt['description'] .= trim($buffer);
      }
    }elseif(preg_match("/^VERSION\s*(.+)?\s*GI:(\d+)/", $buffer, $matches)){
      $rt['Acc_Version'] = $matches[1];
      $rt['GI'] = $matches[2];
    }elseif(preg_match("/^FEATURES/", $buffer, $matches)){
      $point = 'FEATURES';
    }elseif($point == 'FEATURES'){
      if(preg_match("/^ORIGIN/", $buffer, $matches)){
        $point = 'ORIGIN';
        continue;
      }
      if(preg_match('/CDS\s+1\.+\d+/', $buffer, $matches)){
        $is_cds = 1;
      }elseif(preg_match('/\/db_xref="taxon:(\d+)"/', $buffer, $matches)){
        $rt['TaxID'] = $matches[1];
      }elseif(preg_match('/\/gene="(.+)?"/', $buffer, $matches) && $is_cds){
        $rt['GeneName'] = $matches[1];
      }elseif(preg_match('/\/gene_synonym="(.+)?"/', $buffer, $matches) && $is_cds){
        $rt['GeneAliase'] = $matches[1];   
      }elseif(preg_match('/\/locus_tag="(.+)?"/', $buffer, $matches) && $is_cds){
        $rt['LocusTag'] = $matches[1];
      }elseif(preg_match('/\/db_xref="GeneID:(\d+)"/', $buffer, $matches) && $is_cds){
        $rt['GeneID'] = $matches[1];
      }     
    }elseif($point == 'ORIGIN'){
      if(preg_match("/^\/\//", $buffer, $matches)){
        break;
      }else{
        $buffer = preg_replace('/[\d+\s+]/', '', trim($buffer));
        $rt['sequence'] .= $buffer;
      }
    }
  }
  $rt['sequence'] = strtoupper($rt['sequence']);
  $rt['Sequence'] = $rt['sequence'];
  $tmp_arr = explode(" ", $rt['Accession']);
  
  if(count($tmp_arr) > 1){
    $match_flag = 0;
    foreach($tmp_arr as $tmp_val){
      $tmp_arr2 = explode(".", $rt['Acc_Version']);
      if(count($tmp_arr2) == 2 && $tmp_val == $tmp_arr2[1]){
        $rt['Accession'] = $tmp_val;
        $match_flag = 1;
        break;
      }
    }
    if(!$match_flag){
      $rt['Accession'] = $tmp_arr[0];
    }
  }
  $rt['size'] =strlen($rt['sequence']);
  return $rt;
}

//----------------------------------------------
function parse_IPI_protein_txt($text){
  //$get_info=array('description', 'sequence',  'size', 'MW');
  $rt = array();
  $rt['IPI_Version'] = '';
  $rt['IPI'] = '';
  $rt['description'] = '';
  $rt['Acc'] = '';
  $rt['GeneName'] = '';
  $rt['TaxID'] = '';
  $rt['GeneID'] = '';
  $rt['sequence'] = '';
  $rt['size'] = '';
  $rt['MW'] = '';
  
  $des = '';
  $seq_started = 0;
  $text = html2text($text);
  $lines = preg_split("/\n/", $text);
  foreach($lines as $line){
    if(preg_match("/^ID   (\S*) +/", $line, $matches)){
      $rt['IPI_Version'] = $matches[1];
    }else if(preg_match("/^AC   (\w*)/", $line, $matches)){
      $rt['IPI'] = $matches[1];
    }else if(preg_match("/^DE   (.+)/", $line, $matches)){
      $rt['description'] .= $matches[1];
    }else if(preg_match("/^DR   Entrez Gene; (.+)?;/", $line, $matches)){
      $tmp_arr = explode(";", $matches[1]);
      if($tmp_arr[0]){
        if(is_numeric($tmp_arr[0])){
          $rt['GeneID'] = $tmp_arr[0];
        }  
      }
      if(isset($tmp_arr[1]) && $tmp_arr[1]){
        $rt['GeneName'] = $tmp_arr[1];
      }
    }else if(preg_match("/^OX   NCBI_TaxID=(\d+);/", $line, $matches)){
      $rt['TaxID'] = $matches[1];    
    }else if(preg_match("/^DR   UniProtKB\/Swiss-Prot; (.+);/", $line, $matches)){
      $tmp_arr = explode("-", $matches[1]);
      $rt['Acc'] = $tmp_arr[0];
    }else if(preg_match("/^SQ   SEQUENCE(.+)/", $line, $matches)){
      $tmp_arr = preg_split("/;/", $matches[1]);
      foreach($tmp_arr as $value){
        if(preg_match("/(.+) AA$/", $value, $matches)){
          $rt['size'] = trim($matches[1]);
        }else if(preg_match("/(.+) MW$/", $value, $matches)){
          $rt['MW'] = trim($matches[1]);
        }
      }
      $seq_started = 1;
    }else if($seq_started == 1){
      if($line =="//") break;
      $rt['sequence'] .= $line;
    }
  }
  $rt['sequence'] = preg_replace("/\s+/", '',$rt['sequence']);
  $rt['Sequence'] = $rt['sequence'];
  return $rt;
}

//----------------------------------------------
function parse_uniport_protein_txt($text){
  //$get_info=array('description', 'sequence',  'size', 'MW');
  $rt = array();
  $rt['description'] = '';
  $rt['sequence'] = '';
  $rt['UniProt'] = '';
  $rt['Accession'] = '';
  $rt['GeneName'] = '';
  $rt['TaxID'] = '';
  $rt['GeneID'] = '';
  $rt['RefSeq'] = '';
  $rt['size'] = '';
  $rt['MW'] = '';
  $rt['Sequence'] = '';
  
  $des = '';
  $seq_started = 0;
  $text = html2text($text);
  $lines = preg_split("/\n/", $text);
  foreach($lines as $line){
    if(preg_match("/^ID   (\S*) +/", $line, $matches)){
      $rt['UniProt'] = $matches[1];
    }else if(preg_match("/^AC   (\w*)/", $line, $matches)){
      $rt['Accession'] = $matches[1];
    }else if(!$rt['description'] and preg_match("/^DE   RecName: Full=(.+)/", $line, $matches)){
      $rt['description'] = $matches[1];
    }else if(preg_match("/^GN   Name=(.+);/", $line, $matches)){
      $rt['GeneName'] = $matches[1];
    }else if(preg_match("/^OX   NCBI_TaxID=(\d+);/", $line, $matches)){
      $rt['TaxID'] = $matches[1];
    }else if(preg_match("/^DR   GeneID; (\d+)/", $line, $matches)){
      $rt['GeneID'] = $matches[1];
    }else if(preg_match("/^DR   Entrez Gene; (\d+);/", $line, $matches)){
      $rt['GeneID'] = $matches[1];
    }else if(preg_match("/^DR   RefSeq; (.*);/", $line, $matches)){
      $rt['RefSeq'] = $matches[1];
    }else if(preg_match("/^SQ   SEQUENCE(.+)/", $line, $matches)){
      $tmp_arr = preg_split("/;/", $matches[1]);
      foreach($tmp_arr as $value){
        if(preg_match("/(.+) AA$/", $value, $matches)){
          $rt['size'] = trim($matches[1]);
        }else if(preg_match("/(.+) MW$/", $value, $matches)){
          $rt['MW'] = trim($matches[1]);
        }
      }
      $seq_started = 1;
    }else if($seq_started == 1){
      if($line =="//") break;
      $rt['sequence'] .= $line;
    }
  }
  $rt['sequence'] = preg_replace("/\s+/", '',$rt['sequence']);
  $rt['Sequence'] = $rt['sequence'];
  
  
  
  return $rt;
}

function get_writable_dir_path($dir_path){
  if(!_is_dir($dir_path)){
    if(_mkdir_path($dir_path)){
      return $dir_path;
    }
  }else if(is_writable($dir_path)){
    return $dir_path;
  }
  return '';
}

function get_uploaded_search_results_dir($SearchEngine=''){
  $path = STORAGE_FOLDER."Prohits_Data/uploaded_search_results";
  if($SearchEngine){
    $path = $path ."/$SearchEngine";
  }
  if(!get_writable_dir_path($path)){
    echo "Error: Permission denied to creade directory '$apth'. Please contact Prohits administrator";
    exit;
  }else{
    return $path."/";
  }
}

function get_psi_proteinDB_arr(){
  $file_path = "../common/javascript/psi_proteinDatabase.js";
  if(!$handle = fopen($file_path, "r")){
    echo "cannot open file $file_path";
    exit;
  }
  $psi_proteinDB_arr = array();
  $count = 0;
  while (!feof($handle)){
    $buffer = fgets($handle);
    $buffer = trim($buffer);
    if(!$buffer) continue;
    if(preg_match("/(MI:\d+)?\s+([\w \/-]+)?\((\w+)?\).+?\>(.+)?\</i", $buffer, $matches)){
      $tmp_arr['MI'] = $matches[1];
      $tmp_arr['short'] = $matches[2];
      $tmp_arr['long'] = $matches[4];
      $psi_proteinDB_arr[$matches[3]] = $tmp_arr;
      $count++;
    }
  }
  return $psi_proteinDB_arr;
}

function check_mascot_parser(){
  $cmd = "cd ../MascotParser/scripts; ". PERL_58 . " ProhitsMascotParser.pl";
  exec("$cmd 2>&1", $output);
  if(isset($output[0])){
    if(preg_match("/command not found|No such file or directory/", $output[0], $matches)){
      return false;
    }else if(strpos($output[0], "USAGE:") === 0){
      return true;
    }
  }
}

function dir_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( _is_dir($src . '/' . $file) ) {
                dir_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
} 
function dir_empty($src) {
  $dir = opendir($src);
  while(false !== ( $file = readdir($dir)) ) {
    if (( $file != '.' ) && ( $file != '..' )) {
      if ( _is_dir($src . '/' . $file) ) {
          dir_empty($src . '/' . $file);
      }
      else {
          unlink($src . '/' . $file);
      }
    }
  }
  closedir($dir);
} 
function get_Sell_PHP_Process_arr(){
  $phpProcess_arr = array();
  //get all php processes
  exec("ps axu | grep .php", $output);
  //USER PID %CPU %MEM VSZ RSS TTY STAT START TIME COMMAND
  foreach($output as $tmp_str){
    $tmp_arr = preg_split('/[ ]+/', trim($tmp_str));
    if(count($tmp_arr) > 11){
      $tmp_command = $tmp_arr[10];
      if( preg_match('/php$/', $tmp_command)){
        $phpProcess_arr[$tmp_arr[1]] = array('user' => $tmp_arr[0],
                                              '%cpu' => $tmp_arr[2],
                                              '%mem' => $tmp_arr[3],
                                              'vsz' => $tmp_arr[4],  //virtual size in kilobytes
                                              'stat'=> $tmp_arr[7], 
                                              'start'=>$tmp_arr[8], 
                                              'time' => $tmp_arr[9],
                                             'script' => $tmp_arr[11]);
        if(count($tmp_arr) > 12){
          $i = 12;
          $arg_num = 1;
          while(isset($tmp_arr[$i])){             
            $phpProcess_arr[$tmp_arr[1]]['arg'.$arg_num] = $tmp_arr[$i];
            $arg_num++;
            $i++;
          }
        }
      }
    }
  }
  return $phpProcess_arr;
}//end of function
function get_running_saint(){
  $rt_arr = array();
  $php_running_arr = get_Sell_PHP_Process_arr();
  foreach($php_running_arr as $PID => $value){
    if(strpos($value['script'], 'export_SAINT_shell.php')){
      $rt_arr[$value['arg1']] = $PID;
    }
  }
  return $rt_arr;
}
function check_Umpire(){
  $error = 1;
  $msg = '';
  $version = '';
  $output = array();
  
  if(defined("DIAUMPIRE_BIN_PATH") and is_dir(DIAUMPIRE_BIN_PATH)){
    $output = array();
    $cmd = "java -jar ". preg_replace("/\/$/", "", DIAUMPIRE_BIN_PATH . '/DIA_Umpire_SE.jar');
    @exec("$cmd 2>&1", $output);
    $output = implode("\n", $output);
  }else{ 
    $msg = "error: please check if 'DIAUMPIRE_BIN_PATH' is correct in Prohits conf file.";
    
  }
  if($output){
    if(preg_match("/DIA-Umpire.+\((version:.+)\)/",$output, $matches)){
      if(count($matches)>1){
        $version = $matches[1];
        $error = 0;
      }
    }else{
      $msg = $output;
    }
  }
  return array('msg'=>$msg, 'version'=>$version, 'error'=>$error);

}
function check_mapDIA(){
  $error = 1;
  $msg = '';
  $version = '';
  $output = '';
  if(defined("MAP_DIA_BIN_PATH") and is_dir(MAP_DIA_BIN_PATH)){
    echo "check mapDIA in local server\n";
    exec(MAP_DIA_BIN_PATH."/mapDIA 2>&1", $output_arr);
    $out_str = implode("\n", $output_arr);
    if(!strstr($out_str, 'Usage')){
  	  $msg = "ERROR: $out_str. Please check Prohits conf file.";
    }else{
      if(preg_match("/mapDIA_v(.+)\/bin[\/]?$/", MAP_DIA_BIN_PATH, $matches)){
        $version = $matches[1];
      }
      $error = 0;
    }
  }else{
    $msg = "error: please check if 'MAP_DIA_BIN_PATH' is correct in Prohits conf file.";
  }
  return array('msg'=>$msg, 'version'=>$version, 'error'=>$error);
}


function check_SAINT(){
  global $old_version;
  $error = 0;
  $msg = '';
  $version = '';
  $version_exp = '';
  if(defined("SAINT_SERVER_WEB_PATH") and strpos(SAINT_SERVER_WEB_PATH, 'http://') === 0){
  	$url = SAINT_SERVER_WEB_PATH."?theaction=check";
    $fd = @fopen($url, 'r');
    if($fd){
    	while($line = fgets($fd, 4096)){
        if(!$version_exp and preg_match("/SAINT express version (.+)/", $line, $matches)){
          $version_exp = $matches[1];
    		}
        if(!$version and preg_match("/SAINT version (.+)/", $line, $matches)){
          $version = $matches[1];
    			$msg = 'SAINT is connected to '.SAINT_SERVER_WEB_PATH;
          $version = $version;
          $version_exp = $version_exp;
    		}
        if(preg_match("/Error(.+)/", $line, $matches)){
          $version = '';
        }
    	}
    }
    if(!$version){
  	  $error = 1;
  	  $msg = "ERROR: SAINT lost connection. Run the URL ' $url ' for detail.";
    }
  }else if(defined("SAINT_SERVER_PATH") and trim(SAINT_SERVER_PATH)){
    exec(SAINT_SERVER_PATH."/saint-spc-ctrl 2>&1", $output);
    $out_str = implode("\n", $output);
    if(!strstr($out_str, 'usage')){
      $error = 1;
  	  $msg = "ERROR: $out_str. Please check Prohits conf file.";
    }else{
      $output = '';
      exec(SAINT_SERVER_PATH."/saint-reformat -v 2>&1", $output);
      $out_str = implode("\n", $output);
      if(preg_match("/SAINT version (.+)/", $out_str, $matches)){
         $version = $matches[1];
         $msg = "SAINT is connected to ". SAINT_SERVER_PATH;
      }
      $output = '';
      if(defined("SAINT_SERVER_EXPRESS_PATH") and trim(SAINT_SERVER_EXPRESS_PATH)){
         
        exec(SAINT_SERVER_EXPRESS_PATH."/SAINTexpress-spc -v 2>&1", $output);
        
        $out_str = implode("\n", $output);
        if(preg_match("/SAINT express version (.+)/", $out_str, $matches)){
          $version_exp = 'exp'.$matches[1];
        }else{
          $msg = $msg = "ERROR: ". $out_str;
          $error = 1;
        }
      }
    }
  }else{
    $msg = 'Warning: SAINT is not set in Prohits';
    $error = 1;
  }
  return array('msg'=>$msg, 'version'=>$version, 'version_exp'=>$version_exp, 'error'=>$error);
}
function check_saint_status($saint_ID=0, $PID=0){
  global $PROHITSDB;
  $saint_folder = STORAGE_FOLDER."Prohits_Data/SAINT_results/saint_$saint_ID/";
  $saintStatus = '';
  if(!$PID and $saint_ID){
    $SQL = "select ID, Name, Description, Status, ProcessID, UserID from SAINT_log where ID='".$saint_ID."'";
    $theSAINT = $PROHITSDB->fetch($SQL);
    $PID = $theSAINT['ProcessID'];
    $saintStatus = $theSAINT['Status'];
    $saintName = $theSAINT['Name'];
    $saintDescription = $theSAINT['Description'];
    $saintUserID = $theSAINT['UserID'];
  }
  
  echo "
  PID=$PID<br>
  SAINT ID=$saint_ID<br>
  <DIV ID=process_saint>
  <img src='./images/processing.gif' border=0 align=middle><br>
   SAINT is running in the background. This page can be closed. Click 'SAINT Report' at the Analyst left menu to view results after this page is closed. If this page is open, it will let you know when the processing is finished, but ProHits will be locked.
  </DIV>\n";
  while(is_process_running($PID)){
    echo(" . ");
    ob_flush(); flush();
    sleep(3);
  }
  echo "\n<script language='javascript'>
  document.getElementById('process_saint').style.display = 'none';
  </script>\n";
  if(_is_file($saint_folder. "RESULT/list.txt") or _is_file($saint_folder. "RESULT/unique_interactions")){
    //add uniProt ID to result file------------------
    
    
    //-----------------------------------------------
    echo "<br>SAINT task $saint_ID has been successfully processed. Please click SAINT report for detail.";
  }else{
    echo "<br>SAINT task $saint_ID has no results. Please click SAINT log file for detail.";
  }
  echo "\n<input type=button value='[Close]' onClick=\"window.close(); window.opener.location.reload(true);\">\n";
}
if(!function_exists('is_process_running')) { 
  function is_process_running($PID){
    exec("ps $PID", $ProcessState);
    return(count($ProcessState) >= 2);
  }
}
function get_machine_icon($name){
// return imgage path
  $icon_dir = '../msManager/images/msLogo/';
  $rt = '';
  $icon_arr = scandir($icon_dir);
  //print_r($icon_arr);
  $name = strtoupper(preg_replace('/[^A-Za-z0-9]/','', $name));
  
  $lable_arr = array();
  foreach($icon_arr as $icon_img){
    $icon_parts = explode("_", $icon_img);
    if(count($icon_parts) == 3){
      if($icon_parts[0] == 'icon' and $icon_parts[1] == $name){
        return $icon_dir.$icon_img;
      }
      array_push($lable_arr, $icon_parts[2]);
    }
  }
  //no img fond, make icon img name
  if(strlen($name) > 3){
    $icon_first_line = substr($name, 0, 3);
  }else{
    $icon_first_line = $name;
  }
  $icon_second_line = '';
  $second_letters = -1;
  $tmp_num = 1;
  while(in_array($icon_first_line.$icon_second_line.".png", $lable_arr)){
    if(3 - $second_letters > strlen($name)){
      $icon_second_line = $tmp_num;
      $tmp_num++;
    }else{
      $icon_second_line = substr($name, $second_letters);
      $second_letters--;
    }
  }
  $img_name = 'icon_'.$name."_".$icon_first_line.$icon_second_line.".png";
  return create_icon($img_name, $icon_first_line, $icon_second_line, $icon_dir);
}
function create_icon($img_name, $icon_first_line, $icon_second_line, $icon_dir){
  $RGB_COLOR = array();
  array_push($RGB_COLOR, array(183,250,248));
  array_push($RGB_COLOR, array(164,234,232));
  array_push($RGB_COLOR, array(154,217,215));
  array_push($RGB_COLOR, array(144,202,200));
  array_push($RGB_COLOR, array(188,248,192));
  array_push($RGB_COLOR, array(179,235,183));
  
  array_push($RGB_COLOR, array(206,249,209));
  array_push($RGB_COLOR, array(147,251,154));
  array_push($RGB_COLOR, array(192,235,183));
  array_push($RGB_COLOR, array(179,221,194));
  array_push($RGB_COLOR, array(249,245,187));
  array_push($RGB_COLOR, array(246,241,169));
  array_push($RGB_COLOR, array(218,214,148));
  array_push($RGB_COLOR, array(250,240,78));
  $index = rand(0, count($RGB_COLOR)-1);
  $im = @imagecreate(17, 17) or die("Cannot Initialize new GD image stream");
  $background_color = imagecolorallocate($im, $RGB_COLOR[$index][0], $RGB_COLOR[$index][1], $RGB_COLOR[$index][2]);
  $fond_color = imagecolorallocate($im, 0, 0, 0); 
  $x = 1;
  $y = 1;
  imagestring($im, 1, $x, $y, $icon_first_line, $fond_color);
  if($icon_second_line){
    $y = 9;
    if(strlen($icon_second_line)<3) $x = 5;
    imagestring($im, 1, $x, $y, $icon_second_line, $fond_color);
  }
  imagepng($im,$icon_dir.$img_name);
  imagedestroy($im);
  return  $icon_dir.$img_name;
}
//--------------------------------------------------------
// encrypt_pwd($pwd); create encrypted pwd
// encrypt_pwd($pwd, $en_pwd); passwd and encrypted passwd
//--------------------------------------------------------
function encrypt_pwd($pwd, $en_pwd = null){
  $salt_len = 5;
  if(defined("PWD_SALT_LENGTH")){
    $salt_len = PWD_SALT_LENGTH;
  }
  
  if ($en_pwd === null){
    $salt = substr(md5(uniqid(rand(), true)), 0, $salt_len);
  }else{
    $salt = substr($en_pwd, 0, $salt_len);
  }
  $encrpyted_pss =  $salt . sha1($pwd . $salt);
  return $encrpyted_pss;
}
function check_gmail(){
  $err = '';
  if(!PROHITS_GMAIL_USER or preg_match("/xxx/", PROHITS_GMAIL_USER, $matches) or !PROHITS_GMAIL_PWD or preg_match("/xxx/", PROHITS_GMAIL_PWD, $matches)){
    $err .= 'Please add GMAIL account in Prohits conf file.';
  }
  $cmd = 'curl --connect-timeout 4  smtp.gmail.com:465 2>&1';
  exec($cmd, $outputs);
  if(preg_match("/Network is unreachable/", implode("\n", $outputs), $matches)){
    $err .= "\nPlease make sure that outgoing port 465 is open.";
  }
  if(!$err){
    
  }
  return $err;
}
function prohits_gmail($to, $from, $subject, $msg, $isHTML=0, $files=array(), $gmail_user='', $gmail_pwd='', $gmail_port=465){
  //use this function needs to add following line in the top of script.
  //require ('path/to/common/PHPMailer-master/PHPMailerAutoload.php');
  if(!$gmail_user or !$gmail_pwd){
    if(defined("PROHITS_GMAIL_PWD") and defined("PROHITS_GMAIL_USER")){
      $gmail_user = PROHITS_GMAIL_USER;
      $gmail_pwd = PROHITS_GMAIL_PWD;
    }
  }
  if(!$gmail_user or !$gmail_pwd){
    return "gmail Error: no gmail account in Prohits conf file.";
  }
  if(!$from) $from = ADMIN_EMAIL;
  $mail = new PHPMailer;
  $mail->IsSMTP();
  $mail->SMTPDebug = 0;
  $mail->Debugoutput = 'html';
  $mail->Host = 'smtp.gmail.com';
  $mail->Port = $gmail_port;
  $mail->SMTPSecure = 'ssl';
  $mail->SMTPAuth = true;
  $mail->Username = $gmail_user;
  $mail->Password = $gmail_pwd;
  $mail->setFrom($from);
  $mail->addReplyTo($from);
  $mail->addAddress($to);
  $mail->Subject = $subject;
  if($isHTML){
    $mail->msgHTML($msg);
  }else{
    $mail->Body = $msg;
  }
  for($i=0;$i<count($files);$i++){
    if(is_file($files[$i])){
      $mail->AddAttachment($files[$i]);
    }
  }
  if (!$mail->send()) {
     return "Gmail Error: check list: \n1. Add gmail account in Prohits conf file. This account has been set 'Access for less secure apps' from gmail Security Chekc-up \n2. The outgoing port 465 is open in Prohits server. \n" . $mail->ErrorInfo;
  } else {
    return;
  }
}
  
function prohits_mail($to,$subject, $message, $sendermail, $files=array()){
  
  $headers = "From: ProHits". "\r\n";
  $headers .= "Reply-To: $sendermail";
   
  // boundary
  if($files){
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
    // headers for attachment
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
    // multipart boundary
    $message = "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=utf-8\"\n" .
    "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
    // preparing attachments
    for($i=0;$i<count($files);$i++){
      if(is_file($files[$i])){
        $message .= "--{$mime_boundary}\n";
        $fp =    @fopen($files[$i],"rb");
        $data =  @fread($fp,filesize($files[$i]));
                @fclose($fp);
        $data = chunk_split(base64_encode($data));
        $message .= "Content-Type: application/octet-stream; name=\"".basename($files[$i])."\"\n" .
        "Content-Description: ".basename($files[$i])."\n" .
        "Content-Disposition: attachment;\n" . " filename=\"".basename($files[$i])."\"; size=".filesize($files[$i]).";\n" .
        "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
        }
    }
    $message .= "--{$mime_boundary}--";
  }
  //echo "=$to, $subject, $message, ==$headers=";exit;    
  $ok = @mail($to, $subject, $message, $headers);
  if($ok){ return 1; } else { return 0; }
}
//------------------------------------ 
function writeLog_a($msg, $log_file=''){
//----------------------------------- 
  global $logfile; 
  global $debug;
  if(!$log_file and $logfile){
    $log_file = $logfile;
  } 
  
  $log = fopen($log_file, 'a+');
  if(!$log){
    echo "can not open the log file to write: $log_file"; exit;
  }
  fwrite($log, "\r\n" . $msg);
  fclose($log);
}

function get_protein_GeneID_in_local_($proteinKey, $AccessionType = '', $proteinDB){
  global $protein_id_sequence_arr;
  $SQL = '';
  $rt = '';
  if(!$proteinKey) return;
  if(strpos($proteinKey, "DECOY") === 0) return "";
  if(!$AccessionType) $AccessionType = get_protein_ID_type($proteinKey);
  $AccessionType = strtoupper($AccessionType);
  if($AccessionType == 'ORF'){
    $SQL = "select EntrezGeneID from Protein_Class where LocusTag='$proteinKey'";
  }else if($AccessionType == 'GI'){
    $SQL = "select EntrezGeneID, GI, SequenceID from Protein_Accession where GI='".$proteinKey."'";
  }else if($AccessionType == 'ENS'){
    $SQL = "select ENSG, EntrezGeneID, SequenceID from Protein_AccessionENS where ENSP='".$proteinKey."'";
  }elseif($AccessionType == 'NCBIACC' or $AccessionType == 'UNIPROTKB'){ 
    $giSplitArr = explode('.',$proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$giSplitArr[0]."'";
  }elseif($AccessionType == 'UNIPROT'){
    $SQL = "select EntrezGeneID, UniProtID, SequenceID from Protein_Accession where UniProtID='".$proteinKey."'";
  }elseif($AccessionType == 'IPI'){
		$tmp_key = preg_replace("/\..*$/", '', $proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_AccessionIPI where IPI='".$tmp_key."'";
  }else{
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$proteinKey."' order by ID desc";
  }
  if($SQL){
    $row = $proteinDB->fetch($SQL);
    if($row && $row['EntrezGeneID']){
      if($AccessionType == 'ENS'){
        $rt = ($row['EntrezGeneID'])? $row['EntrezGeneID']:$row['ENSG'];
      }else{
        $rt = $row['EntrezGeneID'];
      }
    }
  }
  return $rt;
}

function get_protein_GeneID_in_local($proteinKey, $AccessionType = '', $proteinDB){
  global $protein_id_sequence_arr;
  $SQL = '';
  $rt = array();
  if(!$proteinKey) return;
  if(strpos($proteinKey, "DECOY") === 0) return "";
  
  if(!$AccessionType) $AccessionType = get_protein_ID_type($proteinKey);
  
echo "\$AccessionType=$AccessionType";  
  
  $AccessionType = strtoupper($AccessionType);
  if($AccessionType == 'ORF'){
    $SQL = "select EntrezGeneID from Protein_Class where LocusTag='$proteinKey'";
  }else if($AccessionType == 'GI'){
    $SQL = "select EntrezGeneID, GI, SequenceID from Protein_Accession where GI='".$proteinKey."'";
  }else if($AccessionType == 'ENS'){
    $SQL = "select ENSG, EntrezGeneID, SequenceID from Protein_AccessionENS where ENSP='".$proteinKey."'";
  }elseif($AccessionType == 'NCBIACC' or $AccessionType == 'UNIPROTKB' or $AccessionType == 'REFSEQ'){ 
    $giSplitArr = explode('.',$proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$giSplitArr[0]."'";
  }elseif($AccessionType == 'UNIPROT'){
    $SQL = "select EntrezGeneID, UniProtID, SequenceID from Protein_Accession where UniProtID='".$proteinKey."'";
  }elseif($AccessionType == 'IPI'){
		$tmp_key = preg_replace("/\..*$/", '', $proteinKey);
    $SQL = "select EntrezGeneID, SequenceID from Protein_AccessionIPI where IPI='".$tmp_key."'";
  }else{
    $SQL = "select EntrezGeneID, SequenceID from Protein_Accession where Acc='".$proteinKey."' order by ID desc";
  }
  if($SQL){
   
    $row = $proteinDB->fetch($SQL);
    if($row && $row['EntrezGeneID']){
      if($AccessionType == 'ENS'){
        $rt['GeneID'] = ($row['EntrezGeneID'])? $row['EntrezGeneID']:$row['ENSG'];
      }else{
        $rt['GeneID'] = $row['EntrezGeneID'];
      }
    }else{
      $rt['GeneID'] = '';
    }
    $rt['ProteinID'] = $proteinKey;
    $rt['ProteinType'] = $AccessionType;
  }
  return $rt;
}

function creating_iRefIndex_file($uniqe_geneID_prey_arr,$iRefIndex_inter_handle,$proteinDB){
  $gi_str = '';
  foreach($uniqe_geneID_prey_arr as $prey_val){
    if(!is_numeric($prey_val)) continue;
    if($gi_str) $gi_str .= ',';
    $gi_str .= $prey_val;
  }
  $GI_Acc_Version_arr = array();
  if($gi_str){
    $tmp_arr = replease_gi_with_Acc_Version($gi_str);
    foreach($tmp_arr as $gi_key => $Acc_arr){
      $GI_Acc_Version_arr[$gi_key] = $Acc_arr['Acc_Version'];
    }
  }
  
  $SQL = "SHOW TABLES FROM ".PROHITS_PROTEINS_DB;
  $result = $proteinDB->execute($SQL);
  $DB_tables_name_arr = array();
  if($result){
    while($row = mysqli_fetch_row($result)){
      $DB_tables_name_arr[] = $row[0];
    }
  }
  if(in_array("iRefIndex", $DB_tables_name_arr)){
    $to_file = "iRefIndexID\tpreyProteinA preyProteinB\tpreyGeneA preyGeneB\tMethod|Source\n";
    fwrite($iRefIndex_inter_handle, $to_file);
    $iRefIndex_AB_keys = array();
    
    $line_num = 0; 
    foreach($uniqe_geneID_prey_arr as $tmp_geneID=>$tmp_Acc){
      $line_num++; 
      if($line_num%90 === 0){
        echo '.';
        if($line_num%4800 === 0)  echo "\n<br>";
        flush();
        ob_flush();
      }
    
      $SQL = "select ID, geneIDA, geneIDB, method, sourcedb from iRefIndex 
              where geneIDA='$tmp_geneID' and geneIDB is not null";
      $results = mysqli_query($proteinDB->link, $SQL);
      while($row = mysqli_fetch_row($results)){
        if($row[4] != 'MI:0463(biogrid)'
          and $row[4] != 'MI:0465(dip)'
          and $row[4] != 'MI:0469(intact)'
          and $row[4] != 'MI:0471(mint)'
        ) continue;
        $key1 = $row[1]." ". $row[2];
        $key2 = $row[2]." ". $row[1];
        if(!array_key_exists($key1, $iRefIndex_AB_keys) and !array_key_exists($key2, $iRefIndex_AB_keys)){
          $iRefIndex_AB_keys[$key1] = 1;
          if(isset($uniqe_geneID_prey_arr[$row[1]]) && isset($uniqe_geneID_prey_arr[$row[2]])){
            //--------------------------------------------------------------------------------------
            $prey_1 = '';
            $prey_2 = '';
            $prey_1_gi = $uniqe_geneID_prey_arr[$row[1]];
            $prey_2_gi = $uniqe_geneID_prey_arr[$row[2]];
            if(array_key_exists($prey_1_gi, $GI_Acc_Version_arr)){
              $prey_1 = $GI_Acc_Version_arr[$prey_1_gi];
            }else{
              $prey_1 = $prey_1_gi;
            }
            if(array_key_exists($prey_2_gi, $GI_Acc_Version_arr)){
              $prey_2 = $GI_Acc_Version_arr[$prey_2_gi];
            }else{
              $prey_2 = $prey_2_gi;
            }
            if($prey_1 == $prey_2) continue;
            //--------------------------------------------------------------------------------------          
            $to_file = $row[0]."\t". $prey_1." ". $prey_2."\t" . 
            $row[1]." ". $row[2]."\t".$row[3]."|".$row[4]."\n";
            fwrite($iRefIndex_inter_handle, $to_file);
          }
        }
      }
      $SQL = "select ID, geneIDA, geneIDB, method, sourcedb from iRefIndex 
              where geneIDB='$tmp_geneID' and geneIDA is not null";
      $results = mysqli_query($proteinDB->link, $SQL);
      while($row = mysqli_fetch_row($results)){
        if($row[4] != 'MI:0463(biogrid)'
          and $row[4] != 'MI:0465(dip)'
          and $row[4] != 'MI:0469(intact)'
          and $row[4] != 'MI:0471(mint)'
        ) continue; 
        $key1 = $row[1]." ". $row[2];
        $key2 = $row[2]." ". $row[1];
        if(!array_key_exists($key1, $iRefIndex_AB_keys) and !array_key_exists($key2, $iRefIndex_AB_keys)){
          $iRefIndex_AB_keys[$key1] = 1;
          if(isset($uniqe_geneID_prey_arr[$row[1]]) && isset($uniqe_geneID_prey_arr[$row[2]])){
            //-------------------------------------------------------------------------------------
            $prey_1 = '';
            $prey_2 = '';
            $prey_1_gi = $uniqe_geneID_prey_arr[$row[1]];
            $prey_2_gi = $uniqe_geneID_prey_arr[$row[2]];
            if(array_key_exists($prey_1_gi, $GI_Acc_Version_arr)){
              $prey_1 = $GI_Acc_Version_arr[$prey_1_gi];
            }else{
              $prey_1 = $prey_1_gi;
            }
            if(array_key_exists($prey_2_gi, $GI_Acc_Version_arr)){
              $prey_2 = $GI_Acc_Version_arr[$prey_2_gi];
            }else{
              $prey_2 = $prey_2_gi;
            }
            if($prey_1 == $prey_2) continue;
            //--------------------------------------------------------------------------------------
            $to_file = $row[0]."\t". $prey_1." ". $prey_2."\t" . 
            $row[1]." ". $row[2]."\t".$row[3]."|".$row[4]."\n";
            fwrite($iRefIndex_inter_handle, $to_file);
          }
        }
      }
    }
    fclose($iRefIndex_inter_handle);
  }
}

function replease_gi_with_Acc_Version($gi_str){
  global $proteinDB;
  if(!isset($proteinDB)){
    $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
  }
  if(strstr($gi_str, ',')){
    $SQL = "SELECT `EntrezGeneID`,`GI`,`Acc_Version` FROM `Protein_Accession` WHERE `GI`IN($gi_str)";
    $tmp_arr_mul = $proteinDB->fetchAll($SQL);
    $tmp_arr = array();
    foreach($tmp_arr_mul as $tmp_val_mul){
      if(!array_key_exists($tmp_val_mul['GI'], $tmp_arr)){
        if($tmp_val_mul['Acc_Version']){
          $gene_gi_arr['Acc_Version'] = $tmp_val_mul['Acc_Version'];
        }else{
          $gene_gi_arr['Acc_Version'] = $tmp_val_mul['GI'];
        }
        $gene_gi_arr['EntrezGeneID'] = $tmp_val_mul['EntrezGeneID'];
        $tmp_arr[$tmp_val_mul['GI']] = $gene_gi_arr;
      }
    }
    return $tmp_arr;
  }else{
    $SQL = "SELECT `EntrezGeneID`,`GI`,`Acc_Version` FROM `Protein_Accession` WHERE `GI`='$gi_str'";
    $tmp_arr = $proteinDB->fetch($SQL);
    if($tmp_arr){
      $tmp_arr['Acc_Version'] = trim($tmp_arr['Acc_Version']);
      return $tmp_arr;
    }else{
      $rt_arr = array('EntrezGeneID'=>'','Acc_Version'=>'');
      $rt_arr['Acc_Version'] = $gi_str;
      return $rt_arr;
    }
  }
} 

function remove_DECOY_frefix($DECOY_prefix,$protein_tring){
  $DECOY_prefix_arr = explode("|",$DECOY_prefix);
  foreach($DECOY_prefix_arr as $DECOY_prefix){
    $pos = stripos(trim($protein_tring), $DECOY_prefix);
    if($pos !== false){
      return true;
    }
  }
  return false;
}

function get_uniProt_ID($protein_ID,$geneName,$SearchEngine){
  global $gene_UniProt_arr;
  global $NEW_gene_UniProt_arr;
  
  $protein_ID = trim($protein_ID);
  
  $proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD); 
  $uniprotID = '';
  if($SearchEngine == 'GeneLevel'){
    $accType = 'GeneID';
  }else{
    $accType = get_protein_ID_type($protein_ID);
  } 
  
  if(array_key_exists($protein_ID, $gene_UniProt_arr)){
    $uniprotID = $gene_UniProt_arr[$protein_ID];
  }else{
    if($accType == "UniProt"){
      $SQL = "SELECT `UniProtID` FROM `Protein_mapping` WHERE `UniProtID`='$protein_ID'";
      $tmp_arr = $proteinDB->fetch($SQL);
      if($tmp_arr && $tmp_arr['UniProtID']){
        $uniprotID = $tmp_arr['UniProtID'];
        $gene_UniProt_arr[$protein_ID] = $uniprotID;
      }
    }elseif($accType == "GeneID"){
      if(is_numeric($protein_ID)){
        $SQL = "SELECT `EntrezGeneID`,`UniProtID`,`Acc_Version`,`Source` FROM `Protein_Accession` WHERE `EntrezGeneID`='$protein_ID' ORDER BY Source DESC";
        $uniprotID = select_UniProtID($SQL,$geneName,$protein_ID,$accType);
      }
    }else{
      $pos = strpos($protein_ID, '.');
      if($pos){
        $Acc_str = " Acc_Version='$protein_ID'";
      }else{
        $protein_ID_tmp = $protein_ID.'.';
        $Acc_str = " Acc_Version LIKE '$protein_ID_tmp%'";
      }
      $SQL = "SELECT `EntrezGeneID`,`UniProtID`,`Acc_Version`,`Source` FROM `Protein_Accession` WHERE $Acc_str ORDER BY Source DESC";
      $uniprotID = select_UniProtID($SQL,$geneName,$protein_ID,$accType);
    }
    $NEW_gene_UniProt_arr[$protein_ID] = $uniprotID;
  }
  return $uniprotID;
}
     
function select_UniProtID($SQL,$geneName,$protein_ID,$protein_type){
  global $gene_UniProt_arr;
  global $proteinDB;
  $uniprotID = '';
  $REFSEQ_arr = array(); 
  $not_REFSEQ_arr = array();
  
  if($protein_type != 'GeneID' && $protein_type != 'REFSEQ'){
    $SQL = "SELECT `UniProtID` FROM `Protein_mapping` WHERE UniProtKB='$protein_ID'";
    $tmp_arr = $proteinDB->fetch($SQL);
    if($tmp_arr && $tmp_arr['UniProtID']){
      $uniprotID = $protein_ID;
    }
    $gene_UniProt_arr[$protein_ID] = $uniprotID;
    return $uniprotID;
  }
    
  $UniProt_arr = $proteinDB->fetchAll($SQL);
  $ret_arr = filte_UniProt_id($UniProt_arr,$geneName);
  $uniprotID = $ret_arr['uniprotID'];
  $GeneID = $ret_arr['GeneID'];
  
  if($protein_type == 'GeneID'){
    $gene_UniProt_arr[$protein_ID] = $uniprotID;
    return $uniprotID;
  }  
  
  if(!$uniprotID && $GeneID){
    $SQL = "SELECT `EntrezGeneID`,`UniProtID`,`Acc_Version`,`Source` FROM `Protein_Accession` WHERE `EntrezGeneID`='$GeneID' ORDER BY Source DESC";
    $UniProt_arr = $proteinDB->fetchAll($SQL);   
    $ret_arr = filte_UniProt_id($UniProt_arr,$geneName);
    $uniprotID = $ret_arr['uniprotID'];
    $GeneID = $ret_arr['GeneID'];
  }
  $gene_UniProt_arr[$protein_ID] = $uniprotID;
  return $uniprotID;
} 

function filte_UniProt_id($UniProt_arr,$geneName){
  $GeneID = '';
  $uniprotID = '';
  $uniprotID_ref = '';
  $uniprotID_name = '';
  $ret_arr = array('uniprotID'=>'','GeneID'=>'');
  foreach($UniProt_arr as $UniProt_val){
    if(!$GeneID && $UniProt_val['EntrezGeneID']){
      $GeneID = $UniProt_val['EntrezGeneID'];
    }
    $UniProtID_tmp = trim($UniProt_val['UniProtID']);
    if(!$UniProtID_tmp) continue;
    
    if(!$uniprotID){
      $uniprotID = $UniProtID_tmp;
    }
    if(!$uniprotID_ref && $UniProt_val['Source'] == 'ref'){
      $uniprotID_ref = $UniProtID_tmp;
    }
    if($geneName && !$uniprotID_name && !$geneName){
      $pos = stripos($UniProtID_tmp, $geneName);
      if($pos !== false){
        $uniprotID_name = $UniProtID_tmp;
      }
    }
  }
  if($uniprotID_name){
    $ret_arr['uniprotID'] = $uniprotID_name;
  }elseif($uniprotID_ref){
    $ret_arr['uniprotID'] = $uniprotID_ref;
  }elseif($uniprotID){
    $ret_arr['uniprotID'] = $uniprotID;
  }
  $ret_arr['GeneID'] = $GeneID;
  return $ret_arr;
}  





function add_uniprotID_for_SAINT_result_file($result){  
  $result_add_uniprot = $result.".add_uniprot";
  if(_is_file($result)){
    if(!$result_handle = fopen($result, 'r')){
      echo "Cannot open file ($result)";
      exit;
    }
    if(!$result_add_uniprot_handle = fopen($result_add_uniprot, 'w')){
      echo "Cannot open file ($result_add_uniprot_handle)";
      exit;
    }
    
    $counter = 0;
    while(($buffer = fgets($result_handle)) !== false){
      $uniprotID = '';
      $buffer = trim($buffer);
      if(!$counter){
        $title_arr = explode("\t", $buffer);
        $title_len = count($title_arr);
        $buffer .= "\tUniProtID\r\n";
        fwrite($result_add_uniprot_handle, $buffer);
        $counter++;
        continue;
      }
      $tmp_arr = explode("\t", $buffer);
      $data_len = count($tmp_arr);
      $len_diff = $title_len - $data_len;
      for($i=0; $i<$len_diff; $i++){
        $buffer .= "\t";
      }
      $accID = $tmp_arr[1];
      $uniprotID = get_uniProt_ID($accID);      
      $buffer .= "\t$uniprotID\r\n";
      fwrite($result_add_uniprot_handle, $buffer);
      $counter++;
      //if($counter>100) break;
    }
    fclose($result_handle);
    fclose($result_add_uniprot_handle);
  }
}
?>