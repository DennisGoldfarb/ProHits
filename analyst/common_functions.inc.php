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

function create_filter_status_arrs(&$typeBioArr,&$typeExpArr,&$typeFrequencyArr,$callBy=''){
  global $PROHITSDB,$workingFilterSetID,$theaction,$submitted,$hitType;
  $SQL = "SELECT FilterNameID FROM Filter WHERE FilterSetID='" . $_SESSION['workingFilterSetID'] . "' ORDER BY FilterNameID";
  $filterIDArr=$PROHITSDB->fetchAll($SQL);
  foreach($filterIDArr as $Value) {
    $SQL = "SELECT ID, Name, Alias, Color, Type, Init FROM FilterName WHERE ID=" . $Value['FilterNameID'];
    $filterAttrArr=$PROHITSDB->fetch($SQL);
    if($filterAttrArr['Type'] == 'Fre'){
      $filterAttrArr['Counter'] = 0;
      $typeFrequencyArr = $filterAttrArr;
    }else{
      $filterAttrArr['Counter'] = 0;  
      if($filterAttrArr['Type'] == 'Bio'){
        array_push($typeBioArr, $filterAttrArr);
      }else if($filterAttrArr['Type'] == 'Exp' && ($hitType == 'normal' || $hitType == 'TPP' || $hitType == 'geneLevel' || $callBy)){
        array_push($typeExpArr, $filterAttrArr);
      }  
    }  
  }
  if($hitType == 'TPPpep' && !$callBy){
    get_tpp_pep_typeExpArr($typeExpArr);
  }
}

function hits_table_field_translate_for_tpp($inField){
  $outField = array();
  if($inField == 'ID'){
    $outField[0] = 'ID';
    $outField[1] = 'ID';
  }elseif($inField == 'Expect' || $inField == 'Expect2'){
    $outField[0] = 'PROBABILITY';
    $outField[1] = 'PROBABILITY as Expect';
  }elseif($inField == 'Coverage'){
    $outField[0] = 'PERCENT_COVERAGE';
    $outField[1] = 'PERCENT_COVERAGE as Coverage';
  }elseif($inField == 'Pep_num'){
    $outField[0] = 'TOTAL_NUMBER_PEPTIDES';
    $outField[1] = 'TOTAL_NUMBER_PEPTIDES as Pep_num';
  }elseif($inField == 'Pep_num_uniqe'){
    $outField[0] = 'UNIQUE_NUMBER_PEPTIDES';
    $outField[1] = 'UNIQUE_NUMBER_PEPTIDES as Pep_num_uniqe';
  }
  return $outField;
}

function get_frequency_arr(&$frequencyArr,$FileName=''){
  global $AccessProjectID;
  $biggest = 0;
  if(!$FileName) return false;
  if($FileName == 'iProphet_frequency.csv'){
    $FileName = 'tpp_frequency.csv';
  }
  $frequencyDir = STORAGE_FOLDER."Prohits_Data/frequency/";  
  $frequencyfileName = $frequencyDir.'P'.$AccessProjectID."_$FileName";
  if(!_is_file($frequencyfileName)) return false; 
  $frequencyHandle = fopen($frequencyfileName, "r");
  if($frequencyHandle){
    $buffer = fgets($frequencyHandle, 4096);
    while(!feof($frequencyHandle)) {
      $buffer = fgets($frequencyHandle, 4096);
      $buffer = trim($buffer);
      if(!$buffer) continue;
      $tmpArr = explode(',',$buffer);
      //if(count($tmpArr) == 2){
      $tmp_key_arr = explode('|',$tmpArr[0]);
      $frequencyArr[trim($tmp_key_arr[0])] = $tmpArr[1];
      if($tmpArr[1] > $biggest) $biggest = $tmpArr[1];
      //}  
    }
    fclose($frequencyHandle);
  }else{
  }
  return $biggest;
} 

function get_sub_frequency_arr($FileName,$frequencyLimit){
  global $AccessProjectID;
  if(!$FileName) return false;
  $sub_frequencyDir = STORAGE_FOLDER."Prohits_Data/subFrequency/";
  $frequencyfileName = $sub_frequencyDir.'Pro'.$AccessProjectID."_$FileName";
  $frequencyArr = array();
  if(!_is_file($frequencyfileName)) return $frequencyArr; 
  $frequencyHandle = fopen($frequencyfileName, "r");
  if($frequencyHandle){
    $buffer = fgets($frequencyHandle, 4096);
    while (!feof($frequencyHandle)) {
      $buffer = fgets($frequencyHandle, 4096);
      $buffer = trim($buffer);
      if(!$buffer) continue;
      $tmpArr = explode(',',$buffer);
      $frequencyArr[$tmpArr[0]] = $tmpArr[1];
    }
    fclose($frequencyHandle);
  }
  return $frequencyArr;
}

function get_user_frequency_arr($FileName){
  global $AccessProjectID;
  if(!$FileName) return false;
  $user_frequencyDir = STORAGE_FOLDER."Prohits_Data/user_d_frequency/P_$AccessProjectID/";
  $frequencyfileName = $user_frequencyDir.$FileName;
  $frequencyArr = array();
  if(!_is_file($frequencyfileName)) return $frequencyArr; 
  $frequencyHandle = fopen($frequencyfileName, "r");
  if($frequencyHandle){
    $buffer = fgets($frequencyHandle, 4096);
    $is_start_point = 0;    
    while(!feof($frequencyHandle)){
      $buffer = fgets($frequencyHandle, 4096);
      $buffer = trim($buffer);
      if(!$buffer) continue;
      $tmpArr = explode(',',$buffer);
      if($tmpArr[0] == 'GeneID'){
        $is_start_point = 1;
        continue;
      }elseif(!$is_start_point){
        continue;
      }      
      $frequencyArr[$tmpArr[0]] = $tmpArr[1];
    }
    fclose($frequencyHandle);
  }
  return $frequencyArr;
}

function get_NS_geneID(&$NSfilteIDarr,$groupID){
  if(!$groupID) return;
  global $HITSDB;
  $NS_Dir = STORAGE_FOLDER."Prohits_Data/Non_Specific/";
  $NS_data_dir = $NS_Dir."NS_data/";
  $tmpGroupArr = array();
  $SQL = "SELECT `ID`, `FileName` FROM `ExpBackGroundSet` WHERE `ID`='$groupID'";
  $NSarr = $HITSDB->fetch($SQL);
  if($NSarr['FileName']){
    $NSfileFullName = $NS_data_dir.$NSarr['FileName'];
    $NSgeneIDstr = @trim(file_get_contents($NSfileFullName));
    $tmpArr = explode(",",$NSgeneIDstr);
    $NSfilteIDarr = $tmpArr;
  }
}

function get_allProjectsDB_index($mainDB){
  $oldDBName = to_defaultDB($mainDB);
  $SQL = "SELECT DBname FROM Projects GROUP BY DBname";
  $ProjectsArr2 = $mainDB->fetchAll($SQL);
  $DBindex = array();
  if(count($ProjectsArr2)){
    for($i=0; $i<count($ProjectsArr2); $i++){
      array_push($DBindex, $ProjectsArr2[$i]['DBname']);
    }
  }
  back_to_oldDB($mainDB, $oldDBName);
  return $DBindex;
}

function get_userName($mainDB='', $userID){
  global $PROHITSDB;
  $SQL = "select Fname, Lname from User WHERE ID='$userID'";  
  $nameArr = $PROHITSDB->fetch($SQL);
  $userFullName = ''; 
  if(count($nameArr)){
    $userFullName = $nameArr['Fname']." ".$nameArr['Lname'];
  }
  return $userFullName;
}

function get_users_ID_Name($mainDB){
  $DBnameChanged = 0;
  $UsersArr = array();
  if($mainDB->selected_db_name != PROHITS_DB){
    $oldDBName =$mainDB->selected_db_name;
    $mainDB->change_db(PROHITS_DB);
    $DBnameChanged = 1;
  }
  $SQL = "select ID, Fname, Lname from User ORDER BY Fname";
  $results = mysqli_query($mainDB->link, $SQL); 
  while($Row = mysqli_fetch_row($results)){      
    $UsersArr[$Row[0]] = $Row[1].' '.$Row[2];
  }
  if($DBnameChanged == 1){
    $mainDB->change_db($oldDBName);
  }
  return $UsersArr;
}

function getURL(){
  global $AccessProjectSetID;
  global $mainDB;
  $oldDBName = to_defaultDB($mainDB);
  $SQL = "SELECT URLLinkID FROM SetLink WHERE FilterSetID=" . $AccessProjectSetID . " ORDER BY URLLinkID";
  $urlIDArr=$mainDB->fetchAll($SQL);
  $URL = array();
  foreach($urlIDArr as $Value) {
    $SQL = "SELECT Name, URL, Lable, ProteinTag FROM WebLink WHERE ID=" . $Value['URLLinkID'];
    $urlAttrArr=$mainDB->fetch($SQL);
    array_push($URL, $urlAttrArr);
  } 
  back_to_oldDB($mainDB, $oldDBName);
  return $URL;
}

function get_Species_from_proteinDB($proteinDB, $TaxID){
  $SQL = "SELECT `name_txt` FROM `NCBI_tax_names` WHERE `TaxID`='$TaxID'";  
  $SpeciesArr = $proteinDB->fetch($SQL);  
  $TaxName = '';
  if(count($SpeciesArr)){
    $TaxName = $SpeciesArr['name_txt'];
  }
  return $TaxName;
}

function get_TaxID_name($mainDB, $TaxID){
  $oldDBname = to_defaultDB($mainDB); 
  $SQL = "SELECT Name FROM ProteinSpecies WHERE TaxID='$TaxID'";  
  $SpeciesArr = $mainDB->fetch($SQL);  
  $TaxName = '';
  if(count($SpeciesArr)){
    $TaxName = $SpeciesArr['Name'];
  }
  back_to_oldDB($mainDB, $oldDBname);
  return $TaxName;
}

function get_TaxID_Name_Pair($mainDB){
  $oldDBname = to_defaultDB($mainDB); 
  $SQL = "SELECT TaxID, Name FROM ProteinSpecies ORDER BY TaxID";  
  $SpeciesArr2 = $mainDB->fetchAll($SQL);  
  $TaxIDNameArr = array();
  for($i=0; $i<count($SpeciesArr2); $i++){
    $TaxIDNameArr[$SpeciesArr2[$i]['TaxID']] = $SpeciesArr2[$i]['Name'];
  }
  back_to_oldDB($mainDB, $oldDBname);
  return $TaxIDNameArr;
}

function get_Project_name($mainDB='', $ProjectID=0){
  $ProjectName = '';
  if(!$mainDB || !$ProjectID){
    return $ProjectName;
  }
  $oldDBname = to_defaultDB($mainDB); 
  $SQL = "SELECT Name FROM Projects WHERE ID=$ProjectID";  
  $ProjectArr = $mainDB->fetch($SQL);  
  $ProjectName = '';
  if(count($ProjectArr)){
    $ProjectName = $ProjectArr['Name'];
  }
  back_to_oldDB($mainDB, $oldDBname);
  return $ProjectName;
}

function is_in_species_family($proteinDB, $thisTaxID, $familyTaxID){
  $result = mysqli_query($proteinDB->link,"SHOW TABLES LIKE 'NCBI_tax_nodes'");
  $tableExists = mysqli_num_rows($result);  
  if($tableExists == 0) return "table_not_exist";
  if(!$thisTaxID || !$familyTaxID) return 0;
  if($thisTaxID == $familyTaxID) return 1;
  $tmp_thisTaxID = $thisTaxID;
  $y=0;
  while($tmp_thisTaxID){
    $y++;
    if($y>5000){
      break;
    }
    $SQL = "SELECT `Tax_id`,`Parent_tax_id` FROM `NCBI_tax_nodes` WHERE `Tax_id`='$tmp_thisTaxID'";
    $tmpTaxIDsArr = $proteinDB->fetch($SQL);
    if($tmpTaxIDsArr && isset($tmpTaxIDsArr['Parent_tax_id'])){
      if($tmpTaxIDsArr['Tax_id'] == 1){
        return 0;
      }elseif($tmpTaxIDsArr['Parent_tax_id'] == '$familyTaxID'){
        return 1;
      }else{
        $tmp_thisTaxID = $tmpTaxIDsArr['Parent_tax_id'];
      }
    }else{
      return 0;
    }
  }
  return 0;
}

function get_TaxID_family_from_proteinDB($proteinDB, $TaxID){  
  $TaxIDsArr = array();
  if(!$TaxID) return $TaxIDsArr;
  $queue = array();
  array_push($queue, $TaxID);
  $y=0;
  while(count($queue)){
    $y++;
    if($y>10){
      break;
    }
    $firstItem = array_shift($queue);
    array_push($TaxIDsArr, $firstItem); 
    $SQL = "SELECT `Tax_id` FROM `NCBI_tax_nodes` WHERE `Parent_tax_id`='$firstItem'";
    $tmpTaxIDsArr = $proteinDB->fetchAll($SQL);
    for($i=0; $i<count($tmpTaxIDsArr); $i++){
      array_push($queue, $tmpTaxIDsArr[$i]['Tax_id']);
    }
  }
  return $TaxIDsArr;
}

//----used by---bait.php--bait_report-----
function getSingleGI($mainDB, $GeneID){
  $oldDBName =$mainDB->selected_db_name;
  $mainDB->change_db(PROHITS_PROTEINS_DB);
  $proteinSQL = "SELECT GI, Acc FROM Protein_Accession WHERE EntrezGeneID='".$GeneID."'";
  $GIarr = $mainDB->fetchAll($proteinSQL);
  $retGI = '';
  for($i=0; $i<count($GIarr); $i++){
    if(strstr($GIarr[$i]['Acc'], 'NP')){
      $retGI = $GIarr[$i]['GI'];
      break;
    }else{
      $retGI = $GIarr[$i]['GI'];
    }
  }
  back_to_oldDB($mainDB, $oldDBName);
  return $retGI;
}

function getGI($mainDB, $GeneID){
  $oldDBName =$mainDB->selected_db_name;
  $mainDB->change_db(PROHITS_PROTEINS_DB);
  $proteinSQL = "SELECT GI FROM Protein_Accession WHERE EntrezGeneID='".$GeneID."'";
  $GIarr = $mainDB->fetchAll($proteinSQL);
  back_to_oldDB($mainDB, $oldDBName);
  return $GIarr;
}  

function get_geneName($mainDB, $GeneID){ 
  $oldDBname = to_proteinDB($mainDB);
  $proteinSQL = "SELECT GeneName, GeneAliase FROM Protein_Class WHERE EntrezGeneID ='$GeneID'";  
  $proteinArr = $mainDB->fetch($proteinSQL);    
  $geneName = '';    
  if(count($proteinArr)){
    if($proteinArr['GeneName']){
      $geneName = $proteinArr['GeneName'];
    }else{
      $tmpArr = explode("|", $proteinArr['GeneAliase']);
      $geneName = $tmpArr[0];
    }  
  }
  $mainDB->change_db($oldDBname);
  return $geneName;
}

function get_IDNamePair($mainDB){
  $oldDBName = to_defaultDB($mainDB);
  $SQL = "select ID, Fname, Lname from User";
  $results = mysqli_query($mainDB->link, $SQL); 
  $UsersArr = array();
  while ( $Row =  mysqli_fetch_row($results) ){      
    $UsersArr[$Row[0]] = $Row[1].' '.$Row[2];
  }
  back_to_oldDB($mainDB, $oldDBName);
  return $UsersArr;
} 
function get_coip_color($mainDB, $BaitGeneID, $TargetGeneID){
  $color_and_ID = array('color' => '','ID' => '');
  $SQL = "select ID, Interaction from Coip where (BaitGeneID ='$BaitGeneID' and TargetGeneID='$TargetGeneID') or (BaitGeneID ='$TargetGeneID' and TargetGeneID='$BaitGeneID')";
  if($results = mysqli_query($mainDB->link, $SQL)){
    if($row = mysqli_fetch_row($results)){
      switch ($row[1]){
        case "Yes":
          $color_and_ID['color'] = "green";
          break;
        case "No":
          $color_and_ID['color'] = "red";
          break;
        case "Possible":
          $color_and_ID['color'] = "yellow";
          break;
        case "In Progress":
          $color_and_ID['color'] = "blue";
          break;
      }
    }
    $color_and_ID['ID'] = $row[0];    	
  	return $color_and_ID;
  }else{
    return $results;
  }
}
function Protocol_list($mainDB, $frm_Protocol, $ProtocolType){
  global $AccessProjectID;
  $focus_ID = 0;
  $tmpProtocolArr = explode(",", $frm_Protocol);
  if(count($tmpProtocolArr) == 2){
    $focus_ID = $tmpProtocolArr[0];
  }
  $SQL = "SELECT ID, Name FROM Protocol WHERE Type='$ProtocolType' AND ProjectID='$AccessProjectID' order by Name";
  $ProtocolArr2 = $mainDB->fetchAll($SQL);
  for($i=0; $i<count($ProtocolArr2); $i++){
?>
  <option value="<?php echo $ProtocolArr2[$i]['ID'];?>"<?php echo ($ProtocolArr2[$i]['ID']==$focus_ID)?" selected":"";?>><?php echo $ProtocolArr2[$i]['Name'];?><br>
<?php   
  }
}

function get_Protocol_Name_Pair($mainDB){
  $SQL = "SELECT ID, Name FROM Protocol";  
  $ProtocolArr2 = $mainDB->fetchAll($SQL);  
  $ProtocolIDNameArr = array();
  for($i=0; $i<count($ProtocolArr2); $i++){
    $ProtocolIDNameArr[$ProtocolArr2[$i]['ID']] = $ProtocolArr2[$i]['Name'];
  }
  return $ProtocolIDNameArr;
}
//--$ID can be Experiment ID or Band ID 
function get_Progress_status($ID, $tableName){
  global $HITSDB;
  $retValArr['num_files'] = 0;
  $retValArr['num_hits'] = 0;
  $retValArr['num_hitsTppProt'] = 0;
  $retValArr['num_hitsTppPep'] = 0;
  $retValArr['hitsParsed'] = 0;
  $retValArr['num_Band'] = 0;
  
  $bandsStr = '';
  if($tableName == "Experiment"){
    $SQL = "SELECT
           `ID`,
           `RawFile` 
           FROM `Band` 
           WHERE `ExpID`='".$ID."'";
    if($BandArr = $HITSDB->fetchAll($SQL)){
      $bandsStr = '';
      $retValArr['num_Band'] = count($BandArr);
      foreach($BandArr as $BandValue){
        if($bandsStr) $bandsStr .= "','";
        $bandsStr .= $BandValue['ID'];
        if($BandValue['RawFile']){
          $temArr1 = explode(";", $BandValue['RawFile']);
          $retValArr['num_files'] += count($temArr1);
          if(!$retValArr['hitsParsed']){
            foreach($temArr1 as $tmpValue1){
              $temArr2 = explode(":", $tmpValue1);
              if($temArr2[0] && $temArr2[1]){
                if(preg_match("/(.+),$/", $temArr2[1], $matches)){
                  $temArr2[1] = $matches[1];
                } 
                $retValArr['hitsParsed'] = is_hits_parsed($temArr2[1], $temArr2[0]);  
              }
            }
          }  
        }
      }
    }
  }elseif($tableName == "Band"){
    $SQL = "SELECT
           `ID`,
           `RawFile` 
           FROM `Band` 
           WHERE `ID`='".$ID."'";
    if($BandArr = $HITSDB->fetch($SQL)){
      $retValArr['num_Band'] = 1;
      if($BandArr['RawFile']){
        $temArr1 = explode(";", $BandArr['RawFile']);
        $retValArr['num_files'] = count($temArr1);
        if(!$retValArr['hitsParsed']){
          foreach($temArr1 as $tmpValue1){
            $temArr2 = explode(":", $tmpValue1);
            if($temArr2[0] && $temArr2[1]){
              if(preg_match("/(.+),$/", $temArr2[1], $matches)){
                $temArr2[1] = $matches[1];
              } 
              $retValArr['hitsParsed'] = is_hits_parsed($temArr2[1], $temArr2[0]);  
            }
          }
        }  
      }
    }
    $bandsStr = $ID;
  }
  if($bandsStr){ 
    $num_arr = get_hit_num($bandsStr);
    $retValArr['num_hits'] = $num_arr['hits'];
    $retValArr['num_hitsTppProt'] = $num_arr['hitsTppProt'];
    $retValArr['num_hitsTppPep'] = $num_arr['hitsTppPep'];
  }
  //if(!$retValArr['num_hits'] && !$retValArr['num_hitsTppPep'] && !$retValArr['hitsParsed']) $retValArr['num_hits'] = '';
  return $retValArr;
}
//----------------------------------
// $rt = 'tpp0' means tpp parsed but
// no protein
//----------------------------------
function get_hit_num($bandsStr=''){
  $rt = array('hits'=>0, 'hitsTppProt'=>0, 'hitsTppPep'=>0,'hitsGeneLevel'=>0);
  if(!$bandsStr) return $rt;
//--------------------------------------------------------
  global $HITSDB;
//-----------------------------------------------------------------------
  $exist_Hits_tables_arr = get_exist_Hits_tables_arr($HITSDB);
//-----------------------------------------------------------------------  
  $bandsStr = "'" . $bandsStr . "'";  
  
  $Hits_table_arr = array('Hits','TppProtein','Hits_GeneLevel');
  $table_bridge = array('Hits'=>'hits','TppProtein'=>'hitsTppProt','Hits_GeneLevel'=>'hitsGeneLevel');
  
  foreach($Hits_table_arr as $Hits_table){
    if(array_key_exists($Hits_table, $exist_Hits_tables_arr)){
      $SQL = "SELECT count(ID) as HitNum FROM $Hits_table WHERE BandID IN($bandsStr)";
      if($HitsArr = $HITSDB->fetch($SQL)){
        $rt[$table_bridge[$Hits_table]] = $HitsArr['HitNum'];
      }else{
        $rt[$table_bridge[$Hits_table]] = '';
      }
    }else{
      $rt[$table_bridge[$Hits_table]] = '';
    }
  } 
  return $rt;
}

function get_exist_Hits_tables_arr($HITSDB){
  $DB_name = $HITSDB->selected_db_name;
  $exist_Hits_tables_arr = array();
  $SQL = "SHOW TABLES FROM $DB_name";
  $res = mysqli_query($HITSDB->link, $SQL);
  if($res){
    while($row = $res->fetch_row()){
      if($row[0] == 'Hits' && hasHits_inAccessProject('Hits')){
        $exist_Hits_tables_arr[$row[0]] = '';
      }elseif($row[0] == 'TppProtein' && hasHits_inAccessProject('TppProtein')){
        $exist_Hits_tables_arr[$row[0]] = 'TPP';
      }elseif($row[0] == 'Hits_GeneLevel'){
        if(hasHits_inAccessProject('Hits_GeneLevel')){
          $exist_Hits_tables_arr[$row[0]] = 'geneLevel';
        }
      }
    }
  }
  return $exist_Hits_tables_arr;
}  

function get_uploaded_item_ids($type){
  global $HITSDB;
  $id_arr = array();
  if(!$type) return $id_arr;
  $sample_id_arr = array();
  $bait_id_arr = array();
  $SQL = "SELECT `BandID` FROM `UploadSearchResults` GROUP BY `BandID`";
  $tmp_sample_arr = $HITSDB->fetchAll($SQL);
  foreach($tmp_sample_arr as $tmp_sample_val){
    array_push($sample_id_arr, $tmp_sample_val['BandID']);
  }
  if($type == 'Sample'){
    return $sample_id_arr;
  }elseif($type == 'Bait' && count($sample_id_arr)){
    $sample_id_str = implode(",", $sample_id_arr);
    $SQL = "SELECT `BaitID` FROM `Band` WHERE `ID` IN ($sample_id_str) GROUP BY `BaitID`";
    $tmp_bait_arr = $HITSDB->fetchAll($SQL);
    foreach($tmp_bait_arr as $tmp_bait_val){
      array_push($bait_id_arr, $tmp_bait_val['BaitID']);
    }  
    return $bait_id_arr;
  }else{
    return $id_arr;
  }
}

function is_hits_parsed($rawFileID, $tableName){
  global $msManagerDB;  
  if(!$rawFileID || !$tableName) return 0;
  if(!$msManagerDB){
    $msManagerDB = new mysqlDB(MANAGER_DB, HOSTNAME, USERNAME, DBPASSWORD);
  }
  $HitsParsed = 0;
  if(strstr($tableName, 'tppResults')){
    $resultTableName = $tableName;
  }else{
    $resultTableName = $tableName . "SearchResults";
  }
  $fileID = $rawFileID;  
  $SQL = "SELECT `SavedBy` FROM $resultTableName WHERE `WellID` IN($fileID)";
  if($resultTableArr = $msManagerDB->fetchAll($SQL)){
    foreach($resultTableArr as $value){
      if($value['SavedBy']){
        $HitsParsed = 1;
        break;
      }  
    }  
  }
  return $HitsParsed;
}

function sample_status($BandID, $tableName){
  global $emptySign;
  $RawFileColor = "#2080df";
  $HasHitsColor = "#5b52ad";
  $EmptyColor = "#d9e8f0";
  $statusArr = get_Progress_status($BandID, $tableName);
  $tmp_str = "";
  $file_color = ($statusArr['num_files'])?$RawFileColor:$EmptyColor;
  $fLinkEnd = '';
  if($file_color == $RawFileColor){
      $tmp_str .= "<a href=\"javascript: popwin('raw_file_info.php?Band_ID=$BandID',550,150)\" style='text-decoration:none'>";
      $fLinkEnd = "</a>";
  }
  $tmp_str .= "<font face='Courier' style='background-color:".$file_color."'>&nbsp;</font>$fLinkEnd<img src=images/pixel.gif border=0>";
  $letter = "&nbsp;";
  $hLinkEnd = '';
  if($statusArr['num_hits'] === ''){
    $tmp_color = $EmptyColor;
  }else{
    $tmp_color = $HasHitsColor;
    $tmp_str .= "<a href=\"javascript: popwin('experiment_progress_status.php?Band_ID=$BandID',550,380)\" style='text-decoration:none'>";
    $hLinkEnd = "</a>";
    if($statusArr['num_hits'] === 0){
      $letter = "<font color=white>$emptySign</font>";
    }
  }
  $tmp_str .= '<font face="Courier" style="background-color:'.$tmp_color.'">'.$letter.'</font>'.$hLinkEnd.'<img src=images/pixel.gif border=0>';
  $tmp_str .= '<font face="Courier" size=-1>&nbsp;</font>';
  echo $tmp_str;
  $isEmpty = 0;  if(!$statusArr['num_files'] && $statusArr['num_hits'] === '') $isEmpty = 1;
  return $isEmpty;
}

$bait_group_icon_arr = array();

function get_rawfile_path($ID,$table,$msManagerDB){
  $path = '';
  while($ID){
    $SQL = "SELECT `FileName`,`FolderID` FROM $table WHERE `ID`='$ID'";
    if($tmpArr = $msManagerDB->fetch($SQL)){
      $path = $tmpArr['FileName']."/".$path;
      $ID = $tmpArr['FolderID'];
    }else{
      break;
    }
  }
  return $table.":/".$path;
}	 
//----------------------------------------------------------------------------------------------------
function display_note($note, $note_header='Expt'){
  $note =  substr($note, 0, 250);
  $note =  str_replace("<br>", "", $note);
  $note =  htmlspecialchars($note, ENT_QUOTES);
  return "<font face='Arial' color=green size=2><b>$note_header.Notes</b>:</font><br><font face='Arial' size=1>".nl2br($note)."</font>";
}
function get_Protocol($ProtocolIDdate,$ProtocolType){
  global $HITSDB;
  $tmpProtocolArr = explode(",",$ProtocolIDdate);
  $SQL = "SELECT `Name` FROM `Protocol` WHERE `ID`='".$tmpProtocolArr[0]."'";
  $protocolName = '';
  if($protocolArr = $HITSDB->fetch($SQL)) $protocolName = $protocolArr['Name'];
  $createDate = "";
  if(isset($tmpProtocolArr[1]) && $tmpProtocolArr[1]) $createDate = " (" . $tmpProtocolArr[1] .")";
  return $message = $protocolName.$createDate;
}

function display_step($repeatTime=0,$color,$message=''){
  global $letterFlag;
  $repeatTime = 2*$repeatTime;
  $letter = "&nbsp;";
  if($letterFlag) $letter = "<font color=white>O</font>";
  $spaces = str_repeat("&nbsp;",$repeatTime);
  $tmpStr = $spaces."<font style='background-color: $color' face='Courier' size=1>$letter</font>&nbsp;<font face='Arial' size=1>$message</font>\r\n";          
  $letterFlag = 0;
  return $tmpStr;
}

function display_color_bar($color,$value=''){
  if(!$value) $value = "&nbsp;";
  $tmpStr = "<font style='background-color: $color; color: white' face='Courier' size=+1>$value</font><img src='images/pixel.gif' border=0>";          
  return $tmpStr;
}

function get_link_for_bait_note($Bait_ID){
  global $bait_group_icon_arr,$HITSDB;
  if(!$bait_group_icon_arr)  $bait_group_icon_arr = get_project_noteType_arr($HITSDB);
  $SQL = "SELECT BaitID, NoteType FROM `BaitDiscussion` WHERE `BaitID`='".$Bait_ID."' and `NoteType`>0 ORDER BY NoteType";
  $retStr = '';    
  if($BaitDiscussionArr = $HITSDB->fetchAll($SQL)){
  
    $retStr .= "<table border=0 cellpadding=1 cellspacing=1>\n";
    $retStr .= "<tr>\n";
    $tmpCount = count($BaitDiscussionArr);
    $tmp_group_icon_arr = array();
    for($k=0; $k<$tmpCount; $k++){
      if(is_numeric($bait_group_icon_arr[$BaitDiscussionArr[$k]['NoteType']]['Initial'])){
        $tmp_group_icon_arr[$BaitDiscussionArr[$k]['NoteType']] = $bait_group_icon_arr[$BaitDiscussionArr[$k]['NoteType']];
      }else{
        $tmp_icon = $bait_group_icon_arr[$BaitDiscussionArr[$k]['NoteType']]['Icon'];
        $tmp_alt = $bait_group_icon_arr[$BaitDiscussionArr[$k]['NoteType']]['Name'];     
        $retStr .= "<td><a href=\"javascript: popwin('./pop_bait_note.php?Bait_ID=$Bait_ID', 650,500);\"><img src='./gel_images/$tmp_icon' border='0' alt='$tmp_alt' align='bottom'></a></td>\n";
      }
    }
    $retStr .= "<td>\n";
    $retStr .= "<table border=0 cellpadding=0 cellspacing=2>\n";
    $retStr .= "<tr>\n";
    foreach($tmp_group_icon_arr as $temVal){
      $retStr .= "<td class=tdback_star_image ><a href=\"javascript: popwin('./pop_bait_note.php?Bait_ID=$Bait_ID', 650,500);\" class=small_button>".$temVal['Initial']."</a></td>\n";
    }
    $retStr .= "</tr>\n";
    $retStr .= "</table>\n"; 
    $retStr .= "</td>\n";          
    $retStr .= "</tr>\n";
    $retStr .= "</table>\n";  
  }
  if($retStr){
    return $retStr;
  }else{
    return "&nbsp;";
  }
}

function get_link_for_note($item_ID,$item_type='',$toggle='',$passed_type=''){
  global $HITSDB,$current_type_has_note,$group_icon_arr;
  $ret_arr = array('&nbsp;',0);  
  $type_arr = array();
  $group_icon_arr = get_project_noteType_arr($HITSDB,$item_type);

  if(!$item_type){
    if($toggle){
      $table_name = $passed_type;
      if($table_name == 'Experiment'){
        $SQL = "SELECT BaitID AS Bait, ID AS Experiment FROM $table_name WHERE ID='$item_ID'";
      }elseif($table_name == 'Band'){
        $SQL = "SELECT BaitID AS Bait, ExpID AS Experiment, ID AS Band FROM $table_name WHERE ID='$item_ID'";
      }    
      $type_arr = $HITSDB->fetch($SQL);
      $item_type = $table_name;
      $toggle_arr = $toggle; 
    }
  }else{
    $type_arr[$item_type] = $item_ID;
    $toggle_arr[0] = $item_type;
    if($item_type == 'Band') $toggle_arr[1] = 'Band_z';
  }
  $item_discussion_arr = array();
  $item_discussion_arr_2 = array();
  foreach($type_arr as $table_key => $tmp_id){
    $table_name = $table_key."Group";   
    $SQL = "SELECT RecordID,
                   Note,  
                   NoteTypeID 
                   FROM $table_name 
                   WHERE RecordID='".$tmp_id."' 
                   and NoteTypeID>0 ORDER BY NoteTypeID";
    if($tmp_arr = $HITSDB->fetchAll($SQL)){
      $item_discussion_arr[$table_key] = $tmp_arr;
    }
    $SQL = "SELECT RecordID,
                   Note, 
                   NoteTypeID 
                   FROM $table_name 
                   WHERE RecordID='".$tmp_id."' 
                   and NoteTypeID=0 LIMIT 1";
    if($tmp_arr = $HITSDB->fetchAll($SQL)){
      $item_discussion_arr_2[$table_key] = 1;
    }else{
      $item_discussion_arr_2[$table_key] = 0;
    }                            
  }
  $retStr = '';
  if(count($item_discussion_arr)){
    $retStr .= "<table border=0 cellpadding=1 cellspacing=1>\n";
    $retStr .= "<tr>\n";
    $tmp_group_icon_arr = array();
    foreach($item_discussion_arr as $div_key => $item_discussion_val){
      $item_lable = '';
      $item_arr_tmp = $item_discussion_val;
      if(in_array($div_key, $toggle_arr)){
        $style = "STYLE='display: block;'";
      }else{
        $style = "STYLE='display: none;'";
      }
      $full_div_key = $div_key."_".$item_ID;  
      $retStr .= "<td nowrap valign=top><span id='$full_div_key' $style>";
      foreach($item_arr_tmp as $item_arr){
        if(!isset($group_icon_arr[$item_arr['NoteTypeID']])) continue;
        if($group_icon_arr[$item_arr['NoteTypeID']]['Type'] == 'Export'){
          $tmp_group_icon_arr['Band_z'][$item_arr['NoteTypeID']] = $group_icon_arr[$item_arr['NoteTypeID']];
        }elseif(($pos = strpos($item_arr['Note'], 'SAM_')) === false){
          $tmp_icon = $group_icon_arr[$item_arr['NoteTypeID']]['Icon'];
          $tmp_alt = $group_icon_arr[$item_arr['NoteTypeID']]['Name'];     
          $retStr .= "<a href=\"javascript: popwin('./pop_note.php?item_ID=".$item_arr['RecordID']."&item_type=$div_key', 650,500);\"><img src='./gel_images/$tmp_icon' border='0' alt='$tmp_alt' align='bottom'></a>\n";
        }
      }
      $retStr .= "$item_lable</span></td>";  
    } 
    $retStr .= "<td valign=top>\n";
    foreach($tmp_group_icon_arr as $tmp_key => $temVal){
      //if($tmp_key == 'Band_z') $tmp_key = 'Band';
      if(in_array($tmp_key, $toggle_arr)){
        $style = "STYLE='display: block;'";
      }else{
        $style = "STYLE='display: none;'";
      }
      $full_div_key = $tmp_key."_".$item_ID;
      $retStr .= "<span id='$full_div_key' $style>";
      $retStr .= "<table border=0 cellpadding=0 cellspacing=2>\n";
      $retStr .= "<tr>\n";
      foreach($temVal as $temArr){
        $retStr .= "<td class=tdback_star_image valign=MIDDLE align=MIDDLE><a href=\"javascript: popwin('./pop_note.php?item_ID=".$item_arr['RecordID']."&item_type=$div_key', 650,500);\" class=small_button>".$temArr['Initial']."</a>&nbsp;</td>\n";
      }
      $retStr .= "</tr>\n";
      $retStr .= "</table>\n"; 
      $retStr .= "</span>";  
    }
    $retStr .= "</td>\n";          
    $retStr .= "</tr>\n";
    $retStr .= "</table>";  
  }
  if(isset($item_discussion_arr[$item_type]) || isset($item_discussion_arr_2[$item_type])){
    $current_type_has_note = 'Y';
    if(isset($item_discussion_arr[$item_type]) && $item_discussion_arr[$item_type]){
      $noteTypeID_arr = $item_discussion_arr[$item_type];
      $noteTypeID_str = '';
      foreach($noteTypeID_arr as $noteTypeID_val){
        if($noteTypeID_str) $noteTypeID_str .= ',';
        $noteTypeID_str .= $noteTypeID_val['NoteTypeID'];
      }
      $current_type_has_note = $noteTypeID_str;
    }
  }else{
    $current_type_has_note = 0;
  }
  $ret_arr[1] = $current_type_has_note;
  if($retStr){
    $ret_arr[0] = $retStr;
  }
  return $ret_arr;
}

function is_one_peptide($hitID,$HITSDB,$PepNumUniqe){
  if(!$hitID || !$HITSDB) return 0;
  if($PepNumUniqe == 1) return 1;            
  $SQL = "SELECT ID FROM Peptide WHERE HitID='$hitID' LIMIT 2";
  $PeptideArr = $HITSDB->fetchAll($SQL);
  if(count($PeptideArr) == 1){
    return 1;
  }else{
    return 0;
  }
}
function get_project_noteType_arr($HITSDB,$type=''){
  global $AccessProjectID;
  if($type){
    if($type == 'Band'){
      $type = "(Type='$type' or Type='Export') and ";
    }else{
      $type = " Type='$type' and ";
    }  
  }else{
    $type = "";
  }  
  $item_group_icon_arr = array();
  $SQL = "SELECT `ID`,`Name`,`Type`,`Description`,`Icon`,`UserID`,`Initial` 
          FROM `NoteType` 
          WHERE $type `ProjectID`='$AccessProjectID' order by Initial DESC";
  $GroupArr = $HITSDB->fetchAll($SQL);
  if($GroupArr){
    $empty_spaces = 0;
    foreach($GroupArr as $rd){
      $item_group_icon_arr[$rd['ID']] = array('Icon'=>$rd['Icon'],'Name'=>$rd['Name'],'Type'=>$rd['Type'],'Initial'=>$rd['Initial'],'Index'=>$empty_spaces++);
    }
  }
  return $item_group_icon_arr;
}

function get_noteType_ini_by_type(){
  global $HITSDB,$AccessProjectID;
  $tmp_arr = array();
  $SQL = "SELECT `Type`,`Initial` FROM `NoteType`";
  $GroupArr = $HITSDB->fetchAll($SQL);
  foreach($GroupArr as $GroupVal){
    if(!$GroupVal['Type'] || !$GroupVal['Initial']) continue;
    if(!array_key_exists($GroupVal['Type'], $tmp_arr)){
      $tmp_arr[$GroupVal['Type']] = array();
    }
    array_push($tmp_arr[$GroupVal['Type']], $GroupVal['Initial']);
  }
  return $tmp_arr;
}

//start frequency functions ==============================================
function has_hits_for_pFrequency($ES=''){
  global $AccessProjectID;
  global $HITSDB;
  $SE_str = '';
  if($ES == 'Mascot' || $ES == 'GPM'){
    $hits_table = 'Hits H';
    $SE_str = " AND (H.SearchEngine='".$ES."' OR H.SearchEngine='".$ES."Uploaded') ";
  }elseif($ES == 'All'){
    $hits_table = 'Hits H';
    $SE_str = " AND (H.SearchEngine='Mascot' OR H.SearchEngine='MascotUploaded' OR H.SearchEngine='GPM' OR H.SearchEngine='GPMUploaded') ";
  }elseif($ES == 'TppProtein'){
    $hits_table = 'TppProtein H';
  }elseif(strstr($ES, 'GeneLevel_')){
    $ES = str_replace('GeneLevel_', '', $ES);
    $hits_table = 'Hits_GeneLevel H';
  }else{
    return false;
  }
  $SQL = "SELECT H.ID 
          FROM $hits_table 
          LEFT JOIN Bait B
          ON (H.BaitID=B.ID)
          WHERE B.ProjectID=$AccessProjectID
          $SE_str
          LIMIT 1";
  $tmp_arr = $HITSDB->fetch($SQL);
  if(count($tmp_arr)){
    return true;
  }else{
    return false;
  }
}

function has_hits_for_subFrequency($Band_str, $ES=''){
  global $AccessProjectID;
  global $HITSDB;
  $SE_str = '';    
  if($ES == 'TppProtein'){
    $hits_table = 'TppProtein H';
  }elseif($ES == 'Hits_GeneLevel'){
    $hits_table = 'Hits_GeneLevel H';
  }else{
    $hits_table = 'Hits H';
    $SE_str = " AND (H.SearchEngine='Mascot' OR H.SearchEngine='MascotUploaded' OR H.SearchEngine='GPM' OR H.SearchEngine='GPMUploaded') ";
  }
  $SQL = "SELECT H.ID 
          FROM $hits_table 
          LEFT JOIN Band B
          ON (H.BandID=B.ID)
          WHERE B.ID IN ($Band_str)
          $SE_str
          LIMIT 1";
  $tmp_arr = $HITSDB->fetch($SQL);
  if(count($tmp_arr)){
    return true;
  }else{
    return false;
  }
}

function updata_frequency(){
  updata_project_frequencys();
  updata_group_frequencys();
}

function updata_project_frequencys(){
  global $AccessProjectID;
  global $exist_Hits_tables_arr;
  
  if(!is_array($exist_Hits_tables_arr)) return;

  $Prohits_Data_dir = STORAGE_FOLDER . "Prohits_Data/";
  $frequency_dir = $Prohits_Data_dir . "frequency";
  $frequency_files = scandir($frequency_dir);
  foreach($frequency_files as $f_file){
    if($f_file == '..' || $f_file == '.') continue;
    $pattern = "/^P".$AccessProjectID."_/";
    if(preg_match($pattern, $f_file)){
      //echo "\$f_file=$f_file<br>";
      $f_file_full_name = $frequency_dir."/".$f_file;
      unlink($f_file_full_name);
    }
  }
  
  $to_file_str_arr = array();
  if(array_key_exists('Hits', $exist_Hits_tables_arr)){
    $rt_1 = update_project_frequency_all();
    if($rt_1){
      $to_file_str_arr[] = $rt_1;
    }
  }
  if(array_key_exists('TppProtein', $exist_Hits_tables_arr)){
    $rt_3 = update_project_frequency('tpp_frequency.csv','TppProtein');
    if($rt_3){
      $to_file_str_arr[] = $rt_3;
    }
  }
  if(array_key_exists('Hits', $exist_Hits_tables_arr)){
    $rt_4 = update_project_frequency_SEs();
    if($rt_4){
      $to_file_str_arr_sub = $rt_4;
      foreach($to_file_str_arr_sub as $to_file_str_arr_val){
        $to_file_str_arr[] = $to_file_str_arr_val;
      }
    }
  }
}

function updata_group_frequencys(){
  global $AccessProjectID;
  $Prohits_Data_dir = STORAGE_FOLDER . "Prohits_Data/";
  $frequency_dir = $Prohits_Data_dir . "subFrequency";
  $frequency_files = scandir($frequency_dir);
  foreach($frequency_files as $f_file){
    if($f_file == '..' || $f_file == '.') continue;
    $pattern = "/^Pro".$AccessProjectID."_/";    
    if(preg_match($pattern, $f_file)){
      //echo "\$f_file=$f_file<br>";
      $f_file_full_name = $frequency_dir."/".$f_file;
      unlink($f_file_full_name);
    }
  } 
  $SQL = "SELECT B.ID,
                 D.NoteTypeID 
                 FROM Band B, BaitGroup D 
                 WHERE B.BaitID=D.RecordID 
                 AND B.ProjectID='".$AccessProjectID."' 
                 ORDER BY D.NoteTypeID";
  updata_sub_frequency($SQL);
  
  $SQL = "SELECT B.ID,
                 D.NoteTypeID 
                 FROM Band B, ExperimentGroup D 
                 WHERE B.ExpID=D.RecordID
                 AND B.ProjectID='".$AccessProjectID."' 
                 ORDER BY D.NoteTypeID";
  updata_sub_frequency($SQL);
  
  $SQL = "SELECT B.ID, 
                 D.NoteTypeID 
                 FROM Band B, BandGroup D 
                 WHERE B.ID=D.RecordID
                 AND B.ProjectID='".$AccessProjectID."'
                 AND D.Note NOT LIKE 'SAM%' 
                 ORDER BY D.NoteTypeID";
  updata_sub_frequency($SQL);
}

function update_single_group_frequeny($file_name){
  global $HITSDB,$AccessProjectID;
  if(preg_match('/Pro(\d+)_Type(\d+)_*(\w*).csv/', $file_name,$matches)){
    $ProjectID = $matches[1];
    $typeID = $matches[2];
    $is_TPP = $matches[3];
    $SQL = "SELECT `Type` FROM `NoteType` WHERE `ID`='$typeID'";
    $NoteType_arr = $HITSDB->fetch($SQL);
    $item_type = $NoteType_arr['Type'];
    if($item_type == 'Export') $item_type = 'Band';
    
    if($item_type == 'Bait'){
      $SQL = "SELECT B.ID,
                     D.NoteTypeID 
                     FROM Band B, BaitGroup D 
                     WHERE B.BaitID=D.RecordID 
                     AND B.ProjectID='".$AccessProjectID."'
                     AND D.NoteTypeID='$typeID'";
    }elseif($item_type == 'Experiment'){
      $SQL = "SELECT B.ID,
                     D.NoteTypeID 
                     FROM Band B, ExperimentGroup D 
                     WHERE B.ExpID=D.RecordID
                     AND B.ProjectID='".$AccessProjectID."'
                     AND D.NoteTypeID='$typeID'";
    }elseif($item_type == 'Band'){
      $SQL = "SELECT B.ID,
                     D.NoteTypeID 
                     FROM Band B, BandGroup D 
                     WHERE B.ID=D.RecordID
                     AND B.ProjectID='".$AccessProjectID."'
                     AND D.Note NOT LIKE 'SAM%'
                     AND D.NoteTypeID='$typeID'";
    }
    updata_sub_frequency($SQL,'y',$is_TPP);
  }
}

function updata_sub_frequency($SQL,$is_update_single='',$is_TPP=''){
  global $HITSDB;
  if($typesArr = $HITSDB->fetchAll($SQL)){
    $typeID = '';
    $banIdGroupArr = array();
    foreach($typesArr as $typeValue){
      if(!$typeValue['ID']) continue;
      if($typeValue['NoteTypeID'] != $typeID){ 
        $typeID = $typeValue['NoteTypeID'];
        $banIdGroupArr[$typeID] = array();
      }  
      if(!in_array($typeValue['ID'], $banIdGroupArr[$typeID])){
         array_push($banIdGroupArr[$typeID], $typeValue['ID']);
      }
    }      
    foreach($banIdGroupArr as $typeKey => $typeBands){
      $in_str = implode("','", $typeBands);
      $in_str = "'".$in_str."'";
      $subTotal = count($typeBands);
      if($in_str && $in_str != 'no_hits'){
        if($is_update_single){
          if($is_TPP == 'TPP'){
            updata_sub_frequency_one_type($in_str,$typeKey,$subTotal,'TPP');
          }elseif($is_TPP == 'geneLevel'){
            updata_sub_frequency_one_type($in_str,$typeKey,$subTotal,'geneLevel');
          }else{
            updata_sub_frequency_one_type($in_str,$typeKey,$subTotal);
          }  
        }else{
          updata_sub_frequency_one_type($in_str,$typeKey,$subTotal);
          updata_sub_frequency_one_type($in_str,$typeKey,$subTotal,'TPP');
          updata_sub_frequency_one_type($in_str,$typeKey,$subTotal,'geneLevel');
        }  
      }  
    }
  }
}

function updata_sub_frequency_one_type($in_str,$typeKey,$subTotal,$tpp=''){//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&      
  global $HITSDB;
  global $AccessProjectID;
  global $filter_status_arr;
  global $exist_Hits_tables_arr;
  
  $subDir = STORAGE_FOLDER."Prohits_Data/subFrequency";
  if(!_is_dir($subDir)) _mkdir_path($subDir);
  
  if($tpp == 'TPP'){
    $subFileName = "Pro".$AccessProjectID."_Type".$typeKey."_TPP.csv";
    $Hits = 'TppProtein';
    if(!has_hits_for_subFrequency($in_str, $Hits)){
      $fileFullName = $subDir."/".$subFileName;
      if(is_file($fileFullName)){
        unlink($fileFullName);
      }
    }else{
      if(!is_array($exist_Hits_tables_arr) or !array_key_exists($Hits, $exist_Hits_tables_arr)) return false;
      $sub_sql = get_subSql_for_frequency_filter($filter_status_arr['tpp']);
      calculate_group_frequency($subFileName,$Hits,$sub_sql,$subTotal,$in_str);
    }  
  }elseif($tpp == 'geneLevel'){
    $subFileName = "Pro".$AccessProjectID."_Type".$typeKey."_geneLevel.csv";
    $Hits = 'Hits_GeneLevel';
    if(!is_array($exist_Hits_tables_arr) or !array_key_exists($Hits, $exist_Hits_tables_arr) || !has_hits_for_subFrequency($in_str, $Hits)){
      $fileFullName = $subDir."/".$subFileName;
      if(is_file($fileFullName)){
        unlink($fileFullName);
      }
    }else{
      if(!array_key_exists($Hits, $exist_Hits_tables_arr)) return false;
      //$sub_sql = get_subSql_for_frequency_filter($filter_status_arr['GeneLevel']);
      foreach($filter_status_arr as $key => $val){
        if(stristr($key, 'GeneLevel')){
          $sub_sql[$key] = get_subSql_for_frequency_filter($val);
        }
      }
      calculate_group_frequency_geneLevel($subFileName,$Hits,$sub_sql,$subTotal,$in_str);
    }  
  }else{
    $subFileName = "Pro".$AccessProjectID."_Type".$typeKey.".csv";
    $Hits = 'Hits';
    if(!has_hits_for_subFrequency($in_str, $Hits)){
      $fileFullName = $subDir."/".$subFileName;
      if(is_file($fileFullName)){
        unlink($fileFullName);
      }
    }else{
      if(!is_array($exist_Hits_tables_arr) or !array_key_exists($Hits, $exist_Hits_tables_arr)) return false;
      foreach($filter_status_arr as $key => $val){
        if(stristr($key, 'tpp') || stristr($key, 'GeneLevel')) continue;
        //if($key == 'tpp' || $key == 'geneLevel') continue;
        $sub_sql[$key] = get_subSql_for_frequency_filter($val);
      }
      calculate_group_frequency_normal($subFileName,$Hits,$sub_sql,$subTotal,$in_str);
    }
  }
}

function calculate_group_frequency_normal($subFileName,$Hits,$sub_sql,$subTotal,$in_str){
  global $AccessProjectID;
  global $HITSDB;  
  $subDir = STORAGE_FOLDER."Prohits_Data/subFrequency";
  if(!_is_dir($subDir)) _mkdir_path($subDir);

  $all_E_arr = array();
//echo "<br>normal---------------------------------------------------------------<br>";
  foreach($sub_sql as $searchE => $sub_sql_val){
    if(stristr($searchE, 'tpp') || stristr($searchE, 'GeneLevel')) continue;
    
    $searchE_str = $searchE;
    $searchE_Upload = $searchE_str."Uploaded";
    $searchE_str = " AND (SearchEngine='$searchE_str' OR SearchEngine='$searchE_Upload') ";    
    $sub_sql_val = $sub_sql_val.$searchE_str;          
    $SQL = "SELECT DISTINCT(B.ID) as BandID, 
                H.GeneID 
                FROM Band B, $Hits H
                WHERE B.ID=H.BandID 
                AND B.ProjectID='".$AccessProjectID."'
                AND B.ID IN($in_str)
                $sub_sql_val
                GROUP BY H.GeneID 
                ORDER BY H.GeneID DESC";
//echo "$SQL<br><br>";                
    $single_E_arr = array();                
    if($sub_tmp_arr = $HITSDB->fetchAll($SQL)){
      foreach($sub_tmp_arr as $sub_tmp_val){
        if(!array_key_exists($sub_tmp_val['GeneID'], $single_E_arr)){
          $single_E_arr[$sub_tmp_val['GeneID']] = array();
        }
        $single_E_arr[$sub_tmp_val['GeneID']][] = $sub_tmp_val['BandID'];
      }
      if(!$all_E_arr){
        $all_E_arr = $single_E_arr;
      }else{
        foreach($single_E_arr as $geneID => $BandID_arr){
          if(array_key_exists($geneID, $all_E_arr)){
            $all_E_arr[$geneID] = array_merge($all_E_arr[$geneID], $BandID_arr);
          }else{
            $all_E_arr[$geneID] = $BandID_arr;
          }
        }
      }
    }
  }
  $subFreqArr = array();
  foreach($all_E_arr as $GeneID => $BandID_arr){
    $tmp_arr['GeneID'] = $GeneID;
    $tmp_arr['value'] = count($BandID_arr);
    $subFreqArr[] = $tmp_arr;
  }
  $fileFullName = $subDir."/".$subFileName;
  if($handle_write = fopen($fileFullName, "w")){
    $title = "GeneID,Frequency,CatchedGene,TotalGene\r\n";
    fwrite($handle_write, $title);
    foreach($subFreqArr as $fValue){
      $freqency = round(($fValue['value'] / $subTotal)*100, 2);
      fwrite($handle_write, $fValue['GeneID'].",".$freqency.",".$fValue['value'].",".$subTotal."\r\n");
    }
    fclose($handle_write);
  }
}
//================================================================================================================

function calculate_group_frequency_geneLevel($subFileName,$Hits,$sub_sql,$subTotal,$in_str){
  global $AccessProjectID;
  global $HITSDB;  
  $subDir = STORAGE_FOLDER."Prohits_Data/subFrequency";
  if(!_is_dir($subDir)) _mkdir_path($subDir);
//echo "<br>GeneLevel---------------------------------------------------------------<br>";
  $all_E_arr = array();
  foreach($sub_sql as $searchE => $sub_sql_val){
    if(!stristr($searchE, 'GeneLevel')) continue;
    
    $searchE_str = str_replace("GeneLevel_", "", $searchE);
    $searchE_Upload = $searchE_str."Uploaded";
    $searchE_str = " AND (SearchEngine='$searchE_str' OR SearchEngine='$searchE_Upload') ";

    $sub_sql_val = $sub_sql_val.$searchE_str;
    $SQL = "SELECT B.ID as BandID,
              H.GeneID,
              H.GeneName 
              FROM Band B, $Hits H
              WHERE B.ID=H.BandID 
              AND B.ProjectID='".$AccessProjectID."'
              AND B.ID IN($in_str)
              $sub_sql_val
              GROUP BY H.GeneID";
//echo "$SQL****<br><br>";
    if($frequency_catched_arr_tmp = $HITSDB->fetchAll($SQL)){
      $frequency_catched_arr = array();
      foreach($frequency_catched_arr_tmp as $catched_val){
        if(strstr($catched_val['GeneID'], ',')){
          $tmp_ID_arr = explode(",", $catched_val['GeneID']);
          $tmp_Name_arr = explode(",", $catched_val['GeneName']);
          for($i=0; $i<count($tmp_ID_arr);$i++){
            $tmp_key = $tmp_ID_arr[$i]."|".$tmp_Name_arr[$i];
            $tmp_arr['BandID'] = $catched_val['BandID'];
            $tmp_arr['GeneID'] = $tmp_key;
            $frequency_catched_arr[] = $tmp_arr;
          }
        }else{
          $tmp_key = $catched_val['GeneID']."|".$catched_val['GeneName'];
          $tmp_arr['BandID'] = $catched_val['BandID'];
          $tmp_arr['GeneID'] = $tmp_key;
          $frequency_catched_arr[] = $tmp_arr;
        }
      }
      $single_E_arr = array();
      $sub_tmp_arr = $frequency_catched_arr;    
      foreach($sub_tmp_arr as $sub_tmp_val){
        if(!array_key_exists($sub_tmp_val['GeneID'], $single_E_arr)){
          $single_E_arr[$sub_tmp_val['GeneID']] = array();
        }
        $single_E_arr[$sub_tmp_val['GeneID']][] = $sub_tmp_val['BandID'];
      }
      if(!$all_E_arr){
        $all_E_arr = $single_E_arr;
      }else{
        foreach($single_E_arr as $geneID => $BandID_arr){
          if(array_key_exists($geneID, $all_E_arr)){
            $all_E_arr[$geneID] = array_merge($all_E_arr[$geneID], $BandID_arr);
          }else{
            $all_E_arr[$geneID] = $BandID_arr;
          }
        }
      }
    }
  }
  
  $subFreqArr = array();
  foreach($all_E_arr as $GeneID => $BandID_arr){
    $tmp_arr['GeneID'] = $GeneID;
    $tmp_arr['value'] = count(array_unique($BandID_arr));
    $subFreqArr[] = $tmp_arr;
  }
  $fileFullName = $subDir."/".$subFileName;
  if($handle_write = fopen($fileFullName, "w")){
    $title = "GeneID,Frequency,CatchedGene,TotalGene\r\n";
    fwrite($handle_write, $title);
    foreach($subFreqArr as $fValue){
      $freqency = round(($fValue['value'] / $subTotal)*100, 2);
      fwrite($handle_write, $fValue['GeneID'].",".$freqency.",".$fValue['value'].",".$subTotal."\r\n");
    }
    fclose($handle_write);
  }
}

function calculate_group_frequency($subFileName,$Hits,$sub_sql,$subTotal,$in_str){
  global $AccessProjectID;
  global $HITSDB;
//cho "<br>TPP---------------------------------------------------------------<br>";  
  $subDir = STORAGE_FOLDER."Prohits_Data/subFrequency";
  if(!_is_dir($subDir)) _mkdir_path($subDir);
  $SQL = "SELECT COUNT(DISTINCT(B.ID)) as value, 
              H.GeneID 
              FROM Band B, $Hits H
              WHERE B.ID=H.BandID 
              AND B.ProjectID='".$AccessProjectID."'
              AND B.ID IN($in_str)
              $sub_sql
              GROUP BY H.GeneID 
              ORDER BY value DESC";
//echo "$SQL****<br><br>";              
  if($subFreqArr = $HITSDB->fetchAll($SQL)){
    $fileFullName = $subDir."/".$subFileName;
    if($handle_write = fopen($fileFullName, "w")){
      $title = "GeneID,Frequency,CatchedGene,TotalGene\r\n";
      fwrite($handle_write, $title);
      foreach($subFreqArr as $fValue){
        $freqency = round(($fValue['value'] / $subTotal)*100, 2);
        fwrite($handle_write, $fValue['GeneID'].",".$freqency.",".$fValue['value'].",".$subTotal."\r\n");
      }
      fclose($handle_write);
    }  
  }
}

function get_subSql_for_frequency_filter($updated_filter_tmp){
  $sub_sql = '';
  $tmp_SE_arr = explode('::',$updated_filter_tmp);
  if(isset($tmp_SE_arr[1]) && trim($tmp_SE_arr[1])){
    $tmp_SE_arr[1] = str_replace("Unique", "`Unique`", $tmp_SE_arr[1]);
    $tmp_SE_str = str_replace(",", " AND ", $tmp_SE_arr[1]);
    if($tmp_SE_str){  
      $sub_sql = " AND $tmp_SE_str ";
    }
  }
  return $sub_sql;
}

//==================================
function update_project_frequency_SEs($SE=''){
  global $HITSDB;
  global $AccessProjectID;
  global $is_frequency_base_on_sample;
  global $filter_status_arr;
  global $exist_Hits_tables_arr;
  global $hits_searchEngines;  
  
  if(!array_key_exists('Hits', $exist_Hits_tables_arr)) return false;

  $frequencyDir = STORAGE_FOLDER."Prohits_Data/frequency/";
  if(!_is_dir($frequencyDir)) _mkdir_path($frequencyDir);
  $SearchEngine_arr = $hits_searchEngines;

  $frequency_SEs_base_arr = array();
  $frequency_SEs_catched_arr = array();
  
  $updated_filter = array();
  foreach($SearchEngine_arr as $SearchEngine_val){  
    if(strstr($SearchEngine_val, 'TPP')) continue;
    if(strstr($SearchEngine_val, 'GeneLevel_')){
      $Hits_table = 'Hits_GeneLevel';
    }else{
      $Hits_table = 'Hits';
    }
    
    $SearchEngine_TMP = $SearchEngine_val;
    $formated_SE = str_replace("GeneLevel_", "", $SearchEngine_val);
    if($SE && $SearchEngine_TMP != $SE) continue;
    if($formated_SE == 'Sonar') continue;        
    if(!has_hits_for_pFrequency($SearchEngine_TMP)){
      $frequency_filename = $SearchEngine_TMP."_frequency.csv";
      $filename = $frequencyDir."P".$AccessProjectID."_$frequency_filename";
      if(is_file($filename)){
        unlink($filename);
      }
    }else{
      $updated_filter[] = $updated_filter_tmp = $filter_status_arr[$SearchEngine_TMP];
      $sub_sql = get_subSql_for_frequency_filter($updated_filter_tmp);
      if($SearchEngine_TMP){ 
        if(stristr($SearchEngine_TMP, 'GeneLevel_')){
          $WHERE = " WHERE (H.SearchEngine='".$formated_SE."' OR H.SearchEngine='".$formated_SE."Uploaded') ";
        }else{
          $WHERE = " WHERE (H.SearchEngine='".$formated_SE."' OR H.SearchEngine='".$formated_SE."Uploaded') ";
        } 
      }else{
        $WHERE = " WHERE (H.SearchEngine='' OR H.SearchEngine IS NULL) ";
      }    
      $is_Gel_free = has_gel_free_based_bait($AccessProjectID,$HITSDB);
      
          
      if(!$is_Gel_free){
        if(strstr($SearchEngine_val, 'GeneLevel_')){
          $frequency_SEs_catched_arr[$SearchEngine_TMP] = get_frequency_catched_arr_for_geneLevel_NotGel_free($HITSDB,$AccessProjectID,$sub_sql,'',$SearchEngine_TMP);
        }else{
          $SQL = "SELECT COUNT(DISTINCT(B.GeneID)) as value, 
                  H.GeneID 
                  FROM Bait B, $Hits_table H 
                  $WHERE $sub_sql
                  AND B.GeneID != '0' AND B.GeneID IS NOT NULL AND B.GeneID!=''
                  AND B.ID=H.BaitID 
                  AND B.ProjectID='".$AccessProjectID."'
                  GROUP BY H.GeneID 
                  ORDER BY value DESC";
          if($tmp_BaitID_GeneID_arr = $HITSDB->fetchAll($SQL)){
            if(!$SearchEngine_TMP) $SearchEngine_TMP = 'no_SE';
            $frequency_SEs_catched_arr[$SearchEngine_TMP] = $tmp_BaitID_GeneID_arr;
          }
        }
        $SQL = "SELECT H.BaitID 
                FROM $Hits_table H
                $WHERE
                GROUP BY H.BaitID";
        if($tmp_BaitID_arr = $HITSDB->fetchAll($SQL)){
          $BaitID_str = '';
          foreach($tmp_BaitID_arr as $tmp_BaitID_val){
            if($BaitID_str) $BaitID_str .= ',';
            $BaitID_str .= $tmp_BaitID_val['BaitID'];
          }
          if($BaitID_str !== ''){
            $SQL = "SELECT COUNT(DISTINCT(GeneID)) as value
                    FROM `Bait`
                    WHERE `ProjectID`='".$AccessProjectID."'
                    AND GeneID != '0' AND GeneID IS NOT NULL AND GeneID!='' 
                    AND ID IN($BaitID_str)";
            if($base_num = $HITSDB->fetch($SQL)){
              if($SearchEngine_TMP){
                $frequency_SEs_base_arr[$SearchEngine_TMP] = $base_num['value'];
              }else{
                $frequency_SEs_base_arr['no_SE'] = $base_num['value'];;        
              }
            }  
          }
        }
      }else{
        $tmp_BaitID_GeneID_arr = array();
        if(strstr($SearchEngine_val, 'GeneLevel_')){
          $frequency_SEs_catched_arr[$SearchEngine_TMP] = get_frequency_catched_arr_for_geneLevel_Gel_free($HITSDB,$AccessProjectID,$sub_sql,'',$SearchEngine_TMP);
        }else{
          //echo "--gel free: base is total number of band IDs in a project.---$SearchEngine_TMP<br>";
          $SQL = "SELECT COUNT(DISTINCT(B.ID)) as value,
                  H.GeneID 
                  FROM Band B, $Hits_table H 
                  $WHERE $sub_sql
                  AND B.ID=H.BandID 
                  AND B.ProjectID='".$AccessProjectID."'
                  GROUP BY H.GeneID 
                  ORDER BY value DESC";                  
          if($tmp_BaitID_GeneID_arr = $HITSDB->fetchAll($SQL)){
            if(!$SearchEngine_TMP) $SearchEngine_TMP = 'no_SE';
            $frequency_SEs_catched_arr[$SearchEngine_TMP] = $tmp_BaitID_GeneID_arr;
          }  
        }
        if($tmp_BaitID_GeneID_arr || $frequency_SEs_catched_arr[$SearchEngine_TMP]){ 
          $SQL = "SELECT COUNT(DISTINCT(B.ID)) as value
                  FROM Band B, $Hits_table H 
                  $WHERE
                  AND B.ID=H.BandID 
                  AND B.ProjectID='".$AccessProjectID."'";
          if($tmp_BaitID_arr = $HITSDB->fetch($SQL)){
            $totalSample = $tmp_BaitID_arr['value'];
            if($SearchEngine_TMP){
              $frequency_SEs_base_arr[$SearchEngine_TMP] = $totalSample;
            }else{
              $frequency_SEs_base_arr['no_SE'] = $totalSample;     
            }
          }
        }else{
          $frequency_SEs_catched_arr[$SearchEngine_TMP] = array();
        }
      }
    }
  }   
  foreach($frequency_SEs_catched_arr as $key => $frequency_SEs_catched_val){
      $frequency_filename = $key."_frequency.csv";
      $filename = $frequencyDir."P".$AccessProjectID."_$frequency_filename";
      if(!$feqHandle = fopen($filename, 'w')){
        echo "Cannot open file ($filename)";
        continue;
      } 
      if(!$frequency_SEs_catched_val) continue;
      fwrite($feqHandle, "GeneID,Frequency,CatchedGene,TotalGene\r\n");
      $HitFreqArr = $frequency_SEs_catched_val;
      $totalBaitGene = $frequency_SEs_base_arr[$key];
 
      foreach($HitFreqArr as $value){
        $frequency = round($value['value']*100/$totalBaitGene,2);
        fwrite($feqHandle, $value['GeneID'].",".$frequency.",".$value['value'].",".$totalBaitGene."\r\n");
      }
      fclose($feqHandle);
  }  
  return $updated_filter;
}

function update_project_frequency_all(){
  global $HITSDB;
  global $AccessProjectID;
  global $is_frequency_base_on_sample;
  global $filter_status_arr;
  global $exist_Hits_tables_arr;
  global $filter_arr;
  
  if(!array_key_exists('Hits', $exist_Hits_tables_arr)) return false;

  $frequencyDir = STORAGE_FOLDER."Prohits_Data/frequency/";
  if(!_is_dir($frequencyDir)) _mkdir_path($frequencyDir);
  
  if(!has_hits_for_pFrequency('All')){
    $frequency_filename = "frequency.csv";
    $filename = $frequencyDir."P".$AccessProjectID."_$frequency_filename";
    if(is_file($filename)){
      unlink($filename);
    }
  }else{
    foreach($filter_arr as $filter_key => $filter_val){
      if(stristr($filter_key, 'GeneLevel_')) continue;
      if($filter_key == 'tpp') continue;
      $SearchEngine_arr[] = $filter_key;
    }    
    $frequency_SEs_base_arr = array();
    $frequency_SEs_catched_arr = array();
    
    $updated_filter = array();
    
    $tmp_BaitID_GeneID_arr_3 = array();
    
    foreach($SearchEngine_arr as $SearchEngine_val){
      $SearchEngine_TMP = $SearchEngine_val;
      if(!trim($SearchEngine_TMP)) continue;
      if($SearchEngine_TMP == 'Sonar') continue;
      if($SearchEngine_TMP == 'MSGF') continue;
      
      $updated_filter[] = $updated_filter_tmp = $filter_status_arr[$SearchEngine_TMP];
      
      $sub_sql = get_subSql_for_frequency_filter($updated_filter_tmp);
      if($SearchEngine_TMP){
        $WHERE = " WHERE (H.SearchEngine='".$SearchEngine_TMP."' OR H.SearchEngine='".$SearchEngine_TMP."Uploaded') ";
      }else{
        $WHERE = " WHERE (H.SearchEngine='' OR H.SearchEngine IS NULL) ";
      }
          
      $is_Gel_free = has_gel_free_based_bait($AccessProjectID,$HITSDB);    
      if(!$is_Gel_free){
        //echo "--gel: base is total number of bait gene IDs in a project.---$SearchEngine_TMP<br>";
  //---------------------------------------------------------------------------------------------------------      
        $SQL = "SELECT DISTINCT(B.GeneID) AS ID, 
                H.GeneID 
                FROM Bait B, Hits H 
                $WHERE $sub_sql
                AND B.GeneID != '0' AND B.GeneID IS NOT NULL AND B.GeneID!=''
                AND B.ID=H.BaitID 
                AND B.ProjectID='".$AccessProjectID."'
                GROUP BY H.GeneID 
                ORDER BY B.GeneID DESC";
        if($tmp_BaitID_GeneID_arr_1 = $HITSDB->fetchAll($SQL)){
          $tmp_BaitID_GeneID_arr_2 = array();
          foreach($tmp_BaitID_GeneID_arr_1 as $tmp_BaitID_GeneID_val_1){
            if(!array_key_exists($tmp_BaitID_GeneID_val_1['GeneID'], $tmp_BaitID_GeneID_arr_2)){
              $tmp_BaitID_GeneID_arr_2[$tmp_BaitID_GeneID_val_1['GeneID']] = array();
            }
            $tmp_BaitID_GeneID_arr_2[$tmp_BaitID_GeneID_val_1['GeneID']][] = $tmp_BaitID_GeneID_val_1['ID'];
          }
          if(!$tmp_BaitID_GeneID_arr_3){
            $tmp_BaitID_GeneID_arr_3 = $tmp_BaitID_GeneID_arr_2;
          }else{
            foreach($tmp_BaitID_GeneID_arr_2 as $hitGeneID => $BandID_arr){
              if(array_key_exists($hitGeneID, $tmp_BaitID_GeneID_arr_3)){
                $tmp_BaitID_GeneID_arr_3[$hitGeneID] = array_merge($tmp_BaitID_GeneID_arr_3[$hitGeneID], $BandID_arr);
              }else{
                $tmp_BaitID_GeneID_arr_3[$hitGeneID] = $BandID_arr;
              }
            }
          }
        }      
      }else{ 
        //echo "--gel free: base is total number of band IDs in a project.---$SearchEngine_TMP<br>";
        $SQL = "SELECT DISTINCT(B.ID), 
                H.GeneID 
                FROM Band B, Hits H 
                $WHERE $sub_sql
                AND B.ID=H.BandID 
                AND B.ProjectID='".$AccessProjectID."'
                ORDER BY H.GeneID DESC";
        if($tmp_BaitID_GeneID_arr_1 = $HITSDB->fetchAll($SQL)){
          $tmp_BaitID_GeneID_arr_2 = array();
          foreach($tmp_BaitID_GeneID_arr_1 as $tmp_BaitID_GeneID_val_1){
            if(!array_key_exists($tmp_BaitID_GeneID_val_1['GeneID'], $tmp_BaitID_GeneID_arr_2)){
              $tmp_BaitID_GeneID_arr_2[$tmp_BaitID_GeneID_val_1['GeneID']] = array();
            }
            $tmp_BaitID_GeneID_arr_2[$tmp_BaitID_GeneID_val_1['GeneID']][] = $tmp_BaitID_GeneID_val_1['ID'];
          }
          if(!$tmp_BaitID_GeneID_arr_3){
            $tmp_BaitID_GeneID_arr_3 = $tmp_BaitID_GeneID_arr_2;
          }else{
            foreach($tmp_BaitID_GeneID_arr_2 as $hitGeneID => $BandID_arr){
              if(array_key_exists($hitGeneID, $tmp_BaitID_GeneID_arr_3)){
                $tmp_BaitID_GeneID_arr_3[$hitGeneID] = array_merge($tmp_BaitID_GeneID_arr_3[$hitGeneID], $BandID_arr);
              }else{
                $tmp_BaitID_GeneID_arr_3[$hitGeneID] = $BandID_arr;
              }
            }
          }
        }
      }
    }
    if(!$is_Gel_free){
      $tmp_BaitID_GeneID_arr = array();
      foreach($tmp_BaitID_GeneID_arr_3 as $key => $val){
        $tmp_array['GeneID'] = $key;
        $tmp_array['value'] = count($val);
        $tmp_BaitID_GeneID_arr[] = $tmp_array;
      }
    
      $totalSample = 1;
      $frequency_SEs_catched_arr = $tmp_BaitID_GeneID_arr;
      
      $SQL = "SELECT H.BaitID 
              FROM Hits H
              GROUP BY H.BaitID";
      if($tmp_BaitID_arr = $HITSDB->fetchAll($SQL)){
        $BaitID_str = '';
        foreach($tmp_BaitID_arr as $tmp_BaitID_val){
          if($BaitID_str) $BaitID_str .= ',';
          $BaitID_str .= $tmp_BaitID_val['BaitID'];
        }
        if($BaitID_str !== ''){
          $SQL = "SELECT COUNT(DISTINCT(GeneID)) as value
                  FROM `Bait`
                  WHERE `ProjectID`='".$AccessProjectID."'
                  AND GeneID != '0' AND GeneID IS NOT NULL AND GeneID!='' 
                  AND ID IN($BaitID_str)";
          if($base_num = $HITSDB->fetch($SQL)){
            $totalSample = $base_num['value'];
          }  
        }
      }
    }else{  
      $tmp_BaitID_GeneID_arr = array();
      foreach($tmp_BaitID_GeneID_arr_3 as $key => $val){
        $tmp_array['GeneID'] = $key;
        $tmp_array['value'] = count($val);
        $tmp_BaitID_GeneID_arr[] = $tmp_array;
      }
      $totalSample = 1;
      $frequency_SEs_catched_arr = $tmp_BaitID_GeneID_arr;
      $SQL = "SELECT COUNT(DISTINCT(B.ID)) as value
              FROM Band B, Hits H 
              WHERE B.ID=H.BandID 
              AND B.ProjectID='".$AccessProjectID."'";
      if($tmp_BaitID_arr = $HITSDB->fetch($SQL)){
        $totalSample = $tmp_BaitID_arr['value'];
      }
    }
      
    $frequency_filename = "frequency.csv";
    $filename = $frequencyDir."P".$AccessProjectID."_$frequency_filename";
    if(!$feqHandle = fopen($filename, 'w')){
      echo "Cannot open file ($filename)";
      exit;
    }
    fwrite($feqHandle, "GeneID,Frequency,CatchedGene,TotalGene\r\n");
    $HitFreqArr = $frequency_SEs_catched_arr;
    $totalBaitGene = $totalSample;
    foreach($HitFreqArr as $value){
      $frequency = round($value['value']*100/$totalBaitGene,2);
      fwrite($feqHandle, $value['GeneID'].",".$frequency.",".$value['value'].",".$totalBaitGene."\r\n");
    }
    fclose($feqHandle);
  }
}

function update_project_frequency($frequency_filename,$tableName,$projectID=''){
  global $HITSDB;
  global $AccessProjectID;
  global $HITS_DB;
  global $is_frequency_base_on_sample;
  global $filter_status_arr;
  global $exist_Hits_tables_arr; 

  if(!array_key_exists($tableName, $exist_Hits_tables_arr)) return false;  
  $frequency_catched_arr = array();
  
  $switch_arr = array('Hits_GeneLevel'=>'geneLevel','TppProtein'=>'tpp');
  $updated_filter = $updated_filter_tmp = $filter_status_arr[$switch_arr[$tableName]];
  $sub_sql = get_subSql_for_frequency_filter($updated_filter_tmp);
  if($projectID){
    $DB_name_arr = get_project_id_DBname_arr();
    $DB_name = $DB_name_arr[$projectID];
    $new_HITSDB = new mysqlDB($HITS_DB[$DB_name]);
  }else{
    $projectID = $AccessProjectID;
    $new_HITSDB = $HITSDB;
  }
  
  $frequencyDir = STORAGE_FOLDER."Prohits_Data/frequency/";
  if(!_is_dir($frequencyDir)) _mkdir_path($frequencyDir);
  
  if(!has_hits_for_pFrequency($tableName)){
    $filename = $frequencyDir."P".$projectID."_$frequency_filename";
    if(is_file($filename)){
      unlink($filename);
    }
  }else{
    $is_Gel_free = has_gel_free_based_bait($projectID,$new_HITSDB);  
    if(!$is_Gel_free){
      //echo "--gel: base is total number of bait gene IDs in a project.<br>";
      if($tableName == 'Hits_GeneLevel'){
        $frequency_catched_arr = get_frequency_catched_arr_for_geneLevel_NotGel_free($new_HITSDB,$projectID,$sub_sql);
//echo "Hits_GeneLevel<br>";
      }else{
        $SQL = "SELECT COUNT(DISTINCT(B.GeneID)) as value, 
                H.GeneID
                FROM Bait B, $tableName H 
                WHERE B.ID=H.BaitID 
                AND B.ProjectID='".$projectID."'";
        if($sub_sql){        
          $SQL .=  $sub_sql;      
        }elseif($tableName == 'TppProtein'){
          $SQL .=  " AND H.PROBABILITY >= 0.95";
        }        
        $SQL .=  " AND B.GeneID != '0' AND B.GeneID IS NOT NULL AND B.GeneID!=''
                GROUP BY H.GeneID 
                ORDER BY value DESC";
      }       
              
      if($frequency_catched_arr || $frequency_catched_arr){
        $SQL = "SELECT COUNT(DISTINCT(B.GeneID)) as value
                FROM Bait B, $tableName H 
                WHERE B.ID=H.BaitID 
                AND B.ProjectID='".$projectID."'
                AND B.GeneID != '0' AND B.GeneID IS NOT NULL AND B.GeneID!=''";
        $frequency_total_arr = $new_HITSDB->fetch($SQL);
      }
    }else{
      //echo "--gel free: base is total number of band IDs in a project.<br>";
      if($tableName == 'Hits_GeneLevel'){
        $frequency_catched_arr = get_frequency_catched_arr_for_geneLevel_Gel_free($new_HITSDB,$projectID,$sub_sql);
      }else{
        $SQL = "SELECT COUNT(DISTINCT(B.ID)) as value, 
                H.GeneID
                FROM Band B, $tableName H 
                WHERE B.ID=H.BandID 
                AND B.ProjectID='".$projectID."'";
        if($sub_sql){        
          $SQL .=  $sub_sql;      
        }elseif($tableName == 'TppProtein'){
          $SQL .=  " AND H.PROBABILITY >= 0.95";
        }        
        $SQL .= " GROUP BY H.GeneID 
                ORDER BY value DESC";
        $frequency_catched_arr = $new_HITSDB->fetchAll($SQL);
      }
      if($frequency_catched_arr || $frequency_catched_arr){
        $SQL = "SELECT COUNT(DISTINCT(B.ID)) as value
                FROM Band B,$tableName H 
                WHERE B.ID=H.BandID 
                AND B.ProjectID='".$projectID."'";
        $frequency_total_arr = $new_HITSDB->fetch($SQL);
      }
    }          
    $filename = $frequencyDir."P".$projectID."_$frequency_filename";
    if(!$feqHandle = fopen($filename, 'w')){
      echo "Cannot open file ($filename)";
      exit;
    }
    
    fwrite($feqHandle, "GeneID,Frequency,CatchedGene,TotalGene\r\n");
    if(isset($frequency_total_arr['value'])){
      $no_d_arr = array();
      foreach($frequency_catched_arr as $value){
        if(!array_key_exists(trim($value['GeneID']), $no_d_arr)){
          $no_d_arr[$value['GeneID']] = 1;
        }else{
          continue;
        }
        
        $frequency = round($value['value']*100/$frequency_total_arr['value'],2);
        fwrite($feqHandle, trim($value['GeneID']).",".$frequency.",".$value['value'].",__________".$frequency_total_arr['value']."\r\n");
      }
    }
    fclose($feqHandle);
  }
  return $updated_filter;
}

function get_frequency_catched_arr_for_geneLevel_Gel_free($new_HITSDB,$projectID,$sub_sql,$BandID_str='',$SearchEngine=''){
  $frequency_catched_arr = array();
  $searchE_str = '';
  if($SearchEngine){
    $searchE_str = str_replace("GeneLevel_", "", $SearchEngine);
    $searchE_Upload = $searchE_str."Uploaded";
    $searchE_str = " AND (SearchEngine='$searchE_str' OR SearchEngine='$searchE_Upload') ";
  }
  
  $SQL = "SELECT COUNT(DISTINCT(B.ID)) as value, 
          H.GeneID,
          H.GeneName
          FROM Band B, Hits_GeneLevel H 
          WHERE B.ID=H.BandID 
          AND B.ProjectID='".$projectID."'";
  if($sub_sql){        
    $SQL .=  $sub_sql;      
  }
  if($searchE_str){        
    $SQL .=  $searchE_str;      
  }
  if($BandID_str){
    $SQL .=  " AND H.BandID IN ($BandID_str) ";
  }     
  $SQL .= " GROUP BY H.GeneID 
          ORDER BY value DESC";
//echo "geneLevel-----------------------------------------------------------------------------------<br>";
          
  if($frequency_catched_arr_tmp = $new_HITSDB->fetchAll($SQL)){
    $frequency_catched_arr = array();
    $SS_arr = array();
    foreach($frequency_catched_arr_tmp as $catched_val){
      if(strstr($catched_val['GeneID'], ',')){
        $tmp_ID_arr = explode(",", $catched_val['GeneID']);
        $tmp_Name_arr = explode(",", $catched_val['GeneName']);
        for($i=0; $i<count($tmp_ID_arr);$i++){
          $tmp_key = trim($tmp_ID_arr[$i])."|".trim($tmp_Name_arr[$i]);
          
          if(!array_key_exists($tmp_key, $SS_arr)){
            $SS_arr[$tmp_key] = 1;
          }else{
            continue;
          }
          
          $tmp_arr['value'] = $catched_val['value'];
          $tmp_arr['GeneID'] = $tmp_key;
          $frequency_catched_arr[] = $tmp_arr;
        }
      }else{
        $tmp_key = trim($catched_val['GeneID'])."|".trim($catched_val['GeneName']);
        
        if(!array_key_exists($tmp_key, $SS_arr)){
          $SS_arr[$tmp_key] = 1;
        }else{
          continue;
        }
        
        $tmp_arr['value'] = $catched_val['value'];
        $tmp_arr['GeneID'] = $tmp_key;
        $frequency_catched_arr[] = $tmp_arr;
      }
    }
  }
  return $frequency_catched_arr;        
}

//@@@@@
function get_frequency_catched_arr_for_geneLevel_NotGel_free($new_HITSDB,$projectID,$sub_sql,$BandID_str='',$SearchEngine=''){
  $frequency_catched_arr = array();
  $searchE_str = '';  
  if($SearchEngine){
    $searchE_str = str_replace("GeneLevel_", "", $SearchEngine);
    $searchE_Upload = $searchE_str."Uploaded";
    $searchE_str = " AND (SearchEngine='$searchE_str' OR SearchEngine='$searchE_Upload') ";
  }
  
  $SQL = "SELECT COUNT(DISTINCT(B.GeneID)) as value, 
              H.GeneID,
              H.GeneName
              FROM Bait B, Hits_GeneLevel H 
              WHERE B.ID=H.BaitID 
              AND B.ProjectID='".$projectID."'";
      if($sub_sql){        
        $SQL .=  $sub_sql;      
      }
      if($searchE_str){        
        $SQL .=  $searchE_str;      
      }
      if($BandID_str){
        $SQL .=  " AND H.BandID IN ($BandID_str) ";
      }    
      $SQL .=  " AND B.GeneID != '0' AND B.GeneID IS NOT NULL AND B.GeneID!=''
              GROUP BY H.GeneID 
              ORDER BY value DESC";
          
  if($frequency_catched_arr_tmp = $new_HITSDB->fetchAll($SQL)){
    $frequency_catched_arr = array();
    foreach($frequency_catched_arr_tmp as $catched_val){
      if(strstr($catched_val['GeneID'], ',')){
        $tmp_ID_arr = explode(",", $catched_val['GeneID']);
        $tmp_Name_arr = explode(",", $catched_val['GeneName']);
        for($i=0; $i<count($tmp_ID_arr);$i++){
          $tmp_key = $tmp_ID_arr[$i]."|".$tmp_Name_arr[$i];
          $tmp_arr['value'] = $catched_val['value'];
          $tmp_arr['GeneID'] = $tmp_key;
          $frequency_catched_arr[] = $tmp_arr;
        }
      }else{
        $tmp_key = $catched_val['GeneID']."|".$catched_val['GeneName'];
        $tmp_arr['value'] = $catched_val['value'];
        $tmp_arr['GeneID'] = $tmp_key;
        $frequency_catched_arr[] = $tmp_arr;
      }
    }
  }
  return $frequency_catched_arr;        
}


function has_gel_free_based_bait($projectID,$DB){  
  $SQL = "SELECT `GelFree` 
          FROM `Bait` 
          WHERE `GelFree`='1'
          AND ProjectID='".$projectID."' 
          LIMIT 1";
  if($is_Gel = $DB->fetch($SQL)){
    return 1;
  }else{
    return 0;
  }  
}  
//===end frequency functions==============================
//===functions for reports================================
function print_table_head(){
  global $frequencyLimit, $bgcolordark;
  global $theaction,$hitType,$item_hits_order_by,$frm_selected_band,$subQueryString;
  global $SCRIPT_NAME;
  global $start_point;
  global $typeExpArr;
  global $searchEngineField;
  global $frm_selected_item_str;
  global $passedTypeArr,$noteTypeID_str,$frm_filter_Fequency;
  global $SearchEngineConfig_arr;
  global $hitType;
  
//echo "\$searchEngineField=$searchEngineField<br>";
  
  $item_report_header_arr = array();
  foreach($SearchEngineConfig_arr as $base_en){
    $normal_en = "normal_$base_en";
    $TPP_en = "TPP_$base_en";
    $TPPpep_en = "TPPpep_$base_en";
    
    if($base_en == 'iProphet'){
      $TPP_F = '';
      $TPPpep_F = '';
    }else{
      $TPP_F = 'TPP';
      $TPPpep_F = 'TPPpep';
    }
    if($base_en == 'GPM'){
      $base_en_L = 'XTandem';
    }else{
      $base_en_L = $base_en;
    }     
    if($hitType == "geneLevel"){
      $item_report_header_arr["geneLevel_$base_en"]['lable'] = "Gene level $base_en_L Hits";
      $item_report_header_arr["geneLevel_$base_en"]['SearchEngine'] = "$base_en";
    }
  }
  if($hitType != "geneLevel"){
    $item_report_header_arr = get_project_SearchEngine_for_head();
  }

  $PHP_SELF = $_SERVER['PHP_SELF'];
  if(stristr($PHP_SELF, 'item_report')){
    global $level_2_arr;
    global $item_ID,$submitted,$type,$allBandsStr;
    require("../common/page_counter_class.php");
  }else{
    global $Band_ID,$Plate_ID,$CurrPlate_ID;
    $frm_selected_band = $Band_ID;
  }
  if(!isset($hitType)) $hitType = 'normal';
?>
<script language=javascript>
function switch_hitType(hitType,searchEngineField){
  var theForm = document.forms[0];
  if(document.forms.length > 1){
    theForm = document.forms[1];
  }
  theForm.hitType.value = hitType;
  theForm.searchEngineField.value = searchEngineField;
  theForm.theaction.value = 'showone';
  theForm.submit();
}
function item_hits_sort_by(sort_by){
  var theForm = document.forms[0];
  if(document.forms.length > 1){
    theForm = document.forms[1];
  }
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  theForm.item_hits_order_by.value = sort_by;
  theForm.theaction.value = 'showone';
  theForm.submit();
}
function search_engine_comparison(sample_ID){
  var theUrl = "search_engine_comparison.php?sample_ID="+sample_ID;
  popwin(theUrl,'650','850');
}
<?php if($hitType == 'TPPpep'){?>
function check_band_selected(theForm){
  var obj = theForm.frm_selected_band;
  if(obj.options[obj.selectedIndex].value == ''){
    alert("Please select a sample or all samples");
    return false;
  }else{
    theForm.theaction.value = '';
    theForm.start_point.value = 0;
    theForm.submit();
  }
}
<?php }?>
</script>
        <tr><td colspan=15>
        <div class="bchead">
          <div class="satabs" style="border: #708090 solid 0px;">
        &nbsp;&nbsp;&nbsp;
        <?php 
          $search_engine_count = 0;
          if(stristr($PHP_SELF, 'item_report')){
            foreach($item_report_header_arr as $key => $val){
              $tmp_type_arr = explode('_',$key,2);
              $tmp_hit_type = $tmp_type_arr[0];
              $tmp_searchEngine = $tmp_type_arr[1];
              //if($hitType == "geneLevel"){
              if(!$tmp_has_hit = item_has_hits($frm_selected_item_str,$tmp_hit_type,$tmp_searchEngine)) continue;
              //}
              if($tmp_hit_type == 'normal' || $tmp_hit_type == 'geneLevel'){
                $search_engine_count++;
              } 
              if((!$hitType or $hitType == $tmp_hit_type) and $searchEngineField == $tmp_searchEngine){
                echo "<b>".$val['lable']."</b>&nbsp;";
                echo "<input type='hidden' name='SearchEngine' value='".$val['SearchEngine']."'>";
              }else{            
                if(strstr($val['lable'], 'Peptides')) continue;
                if(isset($noteTypeID_str)){
                  $noteTypeID_str_url = "&noteTypeID_str=$noteTypeID_str";
                }else{
                  $noteTypeID_str_url = '';
                }
                echo "<a href='$PHP_SELF?type=$type&item_ID=$item_ID&hitType=$tmp_hit_type$noteTypeID_str_url&searchEngineField=$tmp_searchEngine&SearchEngine=".$val['SearchEngine']."'>".$val['lable']."</a>&nbsp;";
              }
            }
             
            if($search_engine_count >= 1 && $type == 'Sample'){
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=button value='Search Engine results comparison' onClick='search_engine_comparison($item_ID);'>";
            }
          }else{
            foreach($item_report_header_arr as $key => $val){
              $tmp_type_arr = explode('_',$key);
              $tmp_hit_type = $tmp_type_arr[0];
              $tmp_searchEngine = $tmp_type_arr[1];
              if(!$tmp_has_hit = item_has_hits($frm_selected_item_str,$tmp_hit_type,$tmp_searchEngine)) continue;
              echo ((!$hitType or $hitType == $tmp_hit_type) and $searchEngineField == $tmp_searchEngine)?"<b>".$val['lable']."</b>&nbsp;":"<a href='javascript: switch_hitType(\"".$tmp_hit_type."\",\"".$tmp_searchEngine."\")'>".$val['lable']."</a>&nbsp;";
            }
          }
        ?>
          </div>
        </div>
        </td></tr>
        <?php if($hitType == 'TPPpep'){?>
        <tr height=30>
        <td colspan=14>
        <table width="100%"><tr><td width="30%">
          <?php 
            if(stristr($PHP_SELF, 'item_report')){
          ?>
              <div class=large><b>Select sample:&nbsp;
              <select name="frm_selected_band" onchange="check_band_selected(this.form)">
              <option value="">--select a sample--		            
            <?php foreach($level_2_arr as $arr2_value){
                if(!has_hits_in_band($arr2_value['ID'],$searchEngineField)) continue;
                if($allBandsStr) $allBandsStr .= ',';
                $allBandsStr .= $arr2_value['ID'];
                if(!$frm_selected_band) $frm_selected_band = $arr2_value['ID'];
            ?>              
			        <option value="<?php echo $arr2_value['ID']?>"<?php echo ($frm_selected_band==$arr2_value['ID'])?" selected":"";?>><?php echo $arr2_value['Location']?>			
			 	    <?php }?>
			        <option value="all_bands"<?php echo ($frm_selected_band=='all_bands')?" selected":"";?>>All samples			            
              </select>
              </b></div>
          <?php }else{?>
              &nbsp;&nbsp;
          <?php }?>
        </td>
        <td align="right">
          <?php if($frm_selected_band=='all_bands'){
              echo "&nbsp;";
            }else{
              $total_records = get_total_hits_in_band($frm_selected_band,$searchEngineField);
              if(stristr($PHP_SELF, 'item_report')){
              //if($SCRIPT_NAME == 'item_report.php'){
                $query_string = "item_ID=$item_ID&theaction=$theaction&submitted=$submitted&type=$type&hitType=$hitType&item_hits_order_by=$item_hits_order_by&frm_selected_band=$frm_selected_band";
              }else{
                $query_string = "&Band_ID=$Band_ID&Plate_ID=$Plate_ID&theaction=showone&hitType=$hitType&CurrPlate_ID=$CurrPlate_ID&item_hits_order_by=$item_hits_order_by";
              }
              $query_string .= $subQueryString;
              
              $PAGE_COUNTER = new PageCounter();
              $page_output = $PAGE_COUNTER->page_links($start_point, $total_records, TPP_PEPTIDE_RESULTS_PER_PAGE, TPP_PEPTIDE_MAX_PAGES,str_replace(' ','%20',$query_string)); 
              echo $page_output;
            }
          ?>
        </td></tr></table>
        </td>
        </tr>
        <tr bgcolor="<?php echo $bgcolordark;?>">
          <td width="50" height="25" align=center>
          <div class=tableheader>&nbsp;ID</div></td>
          <td width="80" align="center">
          <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by=="Protein")? 'Protein desc':'Protein';?>')">Protein</a>
          <?php if($item_hits_order_by == "Protein") echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == "Protein desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div></td>
          <td width="80" align="center">
          <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by=="Probability")? 'Probability desc':'Probability';?>')">Probability</a>
          <?php if($item_hits_order_by == "Probability") echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == "Probability desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div></td>
          <td width="80" height="25" align=center>
          <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by=="Score1")? 'Score1 desc':'Score1';?>')">HyperScore</a>
          <?php if($item_hits_order_by == "Score1") echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == "Score1 desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div></td>
          <td width="80" height="25" align=center>
          <div class=tableheader>NextScore</div></td>
          <td width="25" align=center> 
          <div class=tableheader>B-Score</div></td> 
          <td width="10" align="center">
           <div class=tableheader>Y-Score</div></td>
          <td width="10" align="center">
           <div class=tableheader>Expect</div></td>
          <td width="30" align="center">
           <div class=tableheader>Ions</div></td>
          <td width="200" align="center">
           <div class=tableheader>Peptide</div></td>
          <td width="50" align="center">
           <div class=tableheader>Charge</div></td>
          <td width="25" align="center">
          <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by=="Calc_mass")? 'Calc_mass desc':'Calc_mass';?>')">CalcMass</a>
          <?php if($item_hits_order_by == "Calc_mass") echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == "Calc_mass desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div></td>
           <!--div class=tableheader>CalcMass</div--></td>
          <td width="25" align="center">
            <div class=tableheader>DeltaMass</div></td>
          <td width="25" align="center">
            <div class=tableheader>Miss clvg</div></td>
          <td width="20" align="center">
            <div class=tableheader>Option</div></td>
        </tr>
        <?php }elseif($hitType == 'geneLevel'){?>        
        <tr bgcolor="<?php echo $bgcolordark;?>">
          <td width="20" height="25" align=center>
            <div class=tableheader>&nbsp;ID</div>
          </td>
          <td width="80" align="center"> 
            <div class=tableheader>Gene</div>
          </td>
          <td width="200" align="center">
            <div class=tableheader>Redundant</div>
          </td>
          <td width="100" height="25" align=center>
            <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by=='SpectralCount')? 'SpectralCount desc':'SpectralCount';?>')"># SpectralCount</a>
          <?php if($item_hits_order_by == 'SpectralCount') echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == 'SpectralCount desc' or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div>
          </td>
          <td width="170" height="25" align=center>
            <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by=='Unique')? 'Unique desc':'Unique';?>')"># Unique Group<BR> Peptide</a>
          <?php if($item_hits_order_by == 'Unique') echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == 'Unique desc' or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div>
          </td>
          
          <td width="200" align="center">
            <div class=tableheader>Subsumed</div>
          </td>
          <td width="100" height="25" align=center>
          <?php 
            if(strstr($frm_filter_Fequency,'U:')){
              if(preg_match('/(TPP)_(.+)?\./', $frm_filter_Fequency,$matches)){
                $frequency_lable = "(U) ".$matches[2]." ".$matches[1];
              }elseif(preg_match('/-\d+-(.+)?\./', $frm_filter_Fequency,$matches)){
                $frequency_lable = "(U) ".$matches[1];;
              }else{
                continue;
              }
            }elseif(is_numeric($frm_filter_Fequency)){
              if(is_numeric($passedTypeArr[$frm_filter_Fequency])){
                $frequency_lable = 'VS'.$passedTypeArr[$frm_filter_Fequency];
              }else{
                $frequency_lable = $passedTypeArr[$frm_filter_Fequency];
              }  
            }else{
              $frequency_lable = 'Project';
            }
          ?>
            <div class=tableheader><?php echo $frequency_lable?> Frequency</div>
          </td>
          <td width="50" align="center">
            <div class=tableheader>Links</div></td>
          <td width="50" align="center">
            <div class=tableheader>Filter</div></td>
          <td width="100" align="center">
            <div class=tableheader>Option</div></td>
        </tr>
        
       <?php }else{?>
       <tr bgcolor="<?php echo $bgcolordark;?>">
          <td width="5" height="25" align=center>
          <div class=tableheader>&nbsp;ID</div></td>
          <td width="40" height="25" align=center>
          <?php if($hitType == 'TPP'){?>
          <div class=tableheader>Protein</div>
          <?php }else{?> 
            <div class=tableheader>Protein</div>
          <?php }?> 
          </td>
          <td width="50" align="center"> 
          <div class=tableheader>Gene</div></td>
          <td width="70" align="center">
          <?php if($hitType == 'TPP'){?> 
            <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by=="PROBABILITY")? 'PROBABILITY desc':'PROBABILITY';?>')">Probability</a>
            <?php if($item_hits_order_by == "PROBABILITY") echo "<img src='images/icon_order_up.gif'>";
              if($item_hits_order_by == "PROBABILITY desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
            </div>
          <?php }else{?>
            <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by=="Expect")? 'Expect desc':'Expect';?>')">Score</a>
            <?php if($item_hits_order_by == "Expect") echo "<img src='images/icon_order_up.gif'>";
              if($item_hits_order_by == "Expect desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
            </div>
          <?php }?> 
          </td>
          <td width="40" align="center">
           <div class=tableheader><?php echo ($hitType == 'TPP')?"Pct spectrum IDs":"Expect";?></div></td>
          <td width="25" height="25" align=center>
          <?php 
            if(strstr($frm_filter_Fequency,'U:')){
              if(preg_match('/(TPP)_(.+)?\./', $frm_filter_Fequency,$matches)){
                $frequency_lable = "(U) ".$matches[2]." ".$matches[1];
              }elseif(preg_match('/-\d+-(.+)?\./', $frm_filter_Fequency,$matches)){
                $frequency_lable = "(U) ".$matches[1];;
              }else{
                continue;
              }
            }elseif(is_numeric($frm_filter_Fequency)){
              if(is_numeric($passedTypeArr[$frm_filter_Fequency])){
                $frequency_lable = 'VS'.$passedTypeArr[$frm_filter_Fequency];
              }else{
                $frequency_lable = $passedTypeArr[$frm_filter_Fequency];
              }  
            }else{
              $frequency_lable = 'Project';
            }
          ?>
          <div class=tableheader><?php echo $frequency_lable?> Frequency</div></td>
          <td width="100" align="center">
          <div class=tableheader>Redundant</div></td>
          <?php if($hitType !='TPP'){?>
          <td width="50" align=center> 
          <div class=tableheader>MW<BR>kDa</div></td> 
          <?php }?>
          <td width="350" align="center">
           <div class=tableheader>Description</div></td>
          <td width="40" height="25" align=center>
          <?php if($hitType =='TPP'){
              $tmpTotalPep = 'TOTAL_NUMBER_PEPTIDES';
            }elseif($hitType =='normal'){
              $tmpTotalPep = 'Pep_num';
            }
          ?>  
          <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by==$tmpTotalPep)? $tmpTotalPep.' desc':$tmpTotalPep;?>')"># Peptide</a>
          <?php if($item_hits_order_by == $tmpTotalPep) echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == $tmpTotalPep." desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div>
          </td>
          <!--td width="30" align="center">
           <div class=tableheader># Peptide</div></td-->
          <td width="40" height="25" align=center>
          <?php if($hitType =='TPP'){
              $tmpUniPep = 'UNIQUE_NUMBER_PEPTIDES';
            }elseif($hitType =='normal'){
              $tmpUniPep = 'Pep_num_uniqe';
            }
          ?>  
          <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by==$tmpUniPep)? $tmpUniPep.' desc':$tmpUniPep;?>')"># Unique<BR>Peptide</a>
          <?php if($item_hits_order_by == $tmpUniPep) echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == $tmpUniPep." desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div>
          </td> 
          <!--td width="30" align="center">
          <div class=tableheader># Unique<BR>Peptide</div></td-->
          <td width="40" height="25" align=center>
          <?php if($hitType =='TPP'){
              $tmpCovPep = 'PERCENT_COVERAGE';
            }elseif($hitType =='normal'){
              $tmpCovPep = 'Coverage';
            }
          ?>  
          <div class=tableheader><a href="javascript: item_hits_sort_by('<?php echo ($item_hits_order_by==$tmpCovPep)? $tmpCovPep.' desc':$tmpCovPep;?>')">Coverage</a>
          <?php if($item_hits_order_by == $tmpCovPep) echo "<img src='images/icon_order_up.gif'>";
            if($item_hits_order_by == $tmpCovPep." desc" or !$item_hits_order_by) echo "<img src='images/icon_order_down.gif'>";
          ?></div>
          </td> 
          <td width="75" align="center">
            <div class=tableheader>Links</div></td>
          <td width="75" align="center">
            <div class=tableheader>Filter</div></td>
          <td width="75" align="center">
            <div class=tableheader>Option</div></td>
        </tr>
       <?php }?>
<?php 
}

function has_hits_in_band($Band_ID,$searchEngineField){
  global $HITSDB;
  $SQL = "SELECT TP.ID
                FROM TppPeptide TP
                LEFT JOIN (
                TppPeptideGroup P
                ) ON ( TP.GroupID = P.ID ) 
                LEFT JOIN (
                TppProtein T
                ) ON ( P.ProteinID = T.ID ) ";
  $WHERE = " WHERE TP.BandID = '$Band_ID' ";
  
  $S_R_init = 'T';
  $searchEngineWhere = searchEngineWhere($searchEngineField,$S_R_init);
  
  $SQL .= $WHERE . $searchEngineWhere . " LIMIT 1";
  $tempArr = $HITSDB->fetch($SQL);
  if(count($tempArr)){
    return 1;
  }else{
    return 0;
  }  
}

function get_total_hits_in_band($Band_ID,$searchEngineField){//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  global $HITSDB,$subWhere,$theaction;
  $S_R_init = 'T';
  
  $searchEngineWhere = searchEngineWhere($searchEngineField,$S_R_init);
  $SQL = "SELECT TP.ID 
                FROM TppPeptide TP
                LEFT JOIN (
                TppPeptideGroup P
                ) ON ( TP.GroupID = P.ID ) 
                LEFT JOIN (
                TppProtein T
                ) ON ( P.ProteinID = T.ID ) "; 
  $WHERE = " WHERE TP.BandID='$Band_ID'" . $searchEngineWhere . (($theaction=='exclusion')?$subWhere:'');
  $SQL .= $WHERE; 
  $temp_result = mysqli_query($HITSDB->link, $SQL);
  $num_rows = mysqli_num_rows($temp_result);
  return $num_rows;
}

function get_hits_geneID_arr($ID, &$hitsGeneIDarr, $hitType=''){
  global $HITSDB,$type;
  $typeID = "BaitID";
  if($hitType == 'TPP'){
    $SQL = "SELECT GeneID FROM TppProtein WHERE $typeID='$ID' GROUP BY GeneID ORDER BY GeneID";
  }elseif($hitType == 'TPPpep'){
    $SQL = "SELECT T.GeneID FROM TppPeptide TP 
            LEFT JOIN (TppPeptideGroup P) ON (TP.GroupID=P.ID) 
            LEFT JOIN (TppProtein T) ON (P.ProteinID=T.ID)
            WHERE T.$typeID='$ID' GROUP BY T.GeneID ORDER BY T.GeneID";
  }elseif($hitType == 'geneLevel'){
    $SQL = "SELECT GeneID FROM Hits_GeneLevel WHERE $typeID='$ID' GROUP BY GeneID ORDER BY GeneID";
  }else{
    $SQL = "SELECT GeneID FROM Hits WHERE $typeID='$ID' GROUP BY GeneID ORDER BY GeneID";
  }
  $hits_result = $HITSDB->fetchAll($SQL);
  foreach($hits_result as $value){
    array_push($hitsGeneIDarr, $value['GeneID']);
  }
}

//--called by item_report.php and exort_hits.php.
function get_hits_result($ID, &$hits_result, $searchEngineField, $hitType=''){
  global $HITSDB,$type,$AccessProjectID,$item_hits_order_by,$theaction,$subWhere;
  global $start_point,$frm_selected_band;
  global $peptide_report_protein_id;
  global $protein_old_id;
  
  $H_T_TP = '';
  $HitsTable = '';
  $DBtables = '';  
  if($hitType == 'TPP'){
    $SQL = "SELECT 
            T.ID,
            T.WellID, 
            T.BandID,
            T.ProteinAcc,
            T.AccType,
            T.GeneID,
            T.LocusTag,
            T.PROBABILITY,
            T.PCT_SPECTRUM_IDS,
            T.INDISTINGUISHABLE_PROTEIN,
            T.ProteinDec,
            T.TOTAL_NUMBER_PEPTIDES,
            T.UNIQUE_NUMBER_PEPTIDES,
            T.PERCENT_COVERAGE,
            T.XmlFile,
            T.SearchEngine,
            T.SearchDatabase,
            T.BaitID,
            T.XPRESSRATIO_MEAN,
            T.XPRESSRATIO_STANDARD_DEV, 
            T.XPRESSRATIO_NUM_PEPTIDES";
    $S_R_init = 'T';       
    $H_T_TP = 'T';
    $HitsTable = 'TppProtein';
    $DBtables = $HitsTable." ".$H_T_TP;
    $tableOrderBy = " ORDER BY T.$item_hits_order_by";
  }elseif($hitType == 'TPPpep'){
    $SQL = "SELECT
            TP.ID,
            TP.BandID,
            TP.Protein,
            TP.Probability,
            TP.Score1,
            TP.Score2,
            TP.Score3,
            TP.Score4,
            TP.Score5,
            TP.Ions,
            TP.Sequence,
            TP.Charge,
            TP.Calc_mass,
            TP.Massdiff,
            TP.Mised_cleavages,
            TP.XmlFile,
            TP.GroupID,
            TP.Spectrum, 
            TP.Peptide_prev,
            TP.Peptide_next, 
            TP.Xpress,
            TP.Libra1,
            TP.Libra2,
            TP.Libra3,
            TP.Libra4,
            TP.Precursor_mass,
            TP.MZratio,
            TP.PI,
            TP.Retention_time_sec,
            TP.Ppm, 
            TP.Num_tol_term, 
            TP.Fval,
            TP.PI_zscore,
            TP.RT,
            TP.RT_score,
            T.GeneID,
            T.LocusTag,
            T.ProteinAcc,
            T.AccType,
            T.SearchEngine";
    $S_R_init = 'T';        
    $H_T_TP = 'TP';
    $HitsTable = 'TppPeptide';
    $tableOrderBy = " ORDER BY TP.$item_hits_order_by";
    $DBtables = $HitsTable." ".$H_T_TP." LEFT JOIN (TppPeptideGroup P) ON (TP.GroupID=P.ID) LEFT JOIN (TppProtein T) ON (P.ProteinID=T.ID)";
  }elseif($hitType == 'geneLevel'){
    $SQL = "SELECT 
            H.ID, 
            H.WellID, 
            H.BaitID, 
            H.BandID, 
            H.Instrument,
            H.GeneName, 
            H.GeneID,
            H.Description,
            H.SpectralCount,
            H.Unique, 
            H.Redundant,
            H.ResultFile, 
            H.SearchDatabase,
            H.DateTime,
            H.OwnerID,
            H.SearchEngine,
            H.Subsumed";
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits_GeneLevel';
    $tableOrderBy = " ORDER BY H.$item_hits_order_by";
    $DBtables = $HitsTable." ".$H_T_TP;   
  }else{
    $SQL = "SELECT 
            H.ID, 
            H.WellID, 
            H.BaitID, 
            H.BandID, 
            H.Instrument, 
            H.GeneID,
            H.LocusTag,
            H.HitGI,
            H.AccType, 
            H.HitName, 
            H.Expect,
            H.Expect2,
            H.MW,
            H.Coverage,
            H.Pep_num,
            H.Pep_num_uniqe, 
            H.RedundantGI,
            H.ResultFile, 
            H.SearchDatabase,
            H.DateTime,
            H.OwnerID,
            H.SearchEngine,
            H.Intensity_log";
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits';
    $tableOrderBy = " ORDER BY H.$item_hits_order_by";
    $DBtables = $HitsTable." ".$H_T_TP;
    /*$SQL_gi = "SELECT
            H.HitGI,
            H.RedundantGI";*/
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits';
    $tableOrderBy = " ORDER BY H.$item_hits_order_by";
    $DBtables = $HitsTable." ".$H_T_TP;
  }
   
  $searchEngineWhere = searchEngineWhere($searchEngineField,$S_R_init);
  $protein_id_sql = ''; 
  if($hitType == 'TPPpep'){
    $limited = '';
    if($frm_selected_band != 'all_bands'){
      $limited = " LIMIT $start_point, ".TPP_PEPTIDE_RESULTS_PER_PAGE;
    }  
    if($theaction=='exclusion' && $subWhere){
      $SQL .= " FROM ". $DBtables ."  WHERE ".$H_T_TP.".BandID='".$ID."'".$searchEngineWhere.$subWhere.$tableOrderBy.$limited;
    }else{
      $SQL .= " FROM ". $DBtables ."  WHERE ".$H_T_TP.".BandID='".$ID."'".$searchEngineWhere.$tableOrderBy.$limited;
    }  
  }else{
    if(isset($protein_old_id) && $protein_old_id){
      if($hitType == 'TPP'){
        $protein_id_sql = " AND ".$H_T_TP.".ProteinAcc='$protein_old_id' ";
      }else{
        if($hitType != 'geneLevel'){
          $protein_id_sql = " AND ".$H_T_TP.".HitGI='$protein_old_id' ";
        }
      }  
    }
    $SQL .= " FROM ". $DBtables ."  WHERE ".$H_T_TP.".BandID='".$ID."'".$protein_id_sql.$searchEngineWhere.$subWhere.$tableOrderBy;
  }
  $hits_result = mysqli_query($HITSDB->link, $SQL);
}

function get_bait_arr(&$BaitArr,$BaitID){
  global $HITSDB;
  $SQL = "SELECT `ID`,`GeneID`,`GelFree` FROM `Bait` WHERE ID='$BaitID'";
  $BaitArr = $HITSDB->fetch($SQL);
}

function get_bio_filter_arr(&$hitsArr){
  global $tmpHitNotes,$proteinDB,$BaitArr,$HitGeneName,$HitFrequency,$AccessProjectID,$hitType,$LocusTag;
  if($hitsArr['GeneID']){
    if($hitType != 'TPPpep'){
      $geneType = get_Gene_ID_type($hitsArr['GeneID']);
      if($geneType == 'NCBI'){
        $SQL = "SELECT LocusTag, GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID='".$hitsArr['GeneID']."'";
      }else if($geneType == 'ENS'){
        $SQL = "SELECT GeneName, FROM Protein_ClassENS WHERE  ENSG='".$hitsArr['GeneID']."'";
      }
      $ProteinArr = $proteinDB->fetch($SQL);
      if($ProteinArr){
        //---Process Bio Filters--------------------------------------------------    
        if(isset($ProteinArr['BioFilter']) && $ProteinArr['BioFilter']){
          $tmpHitNotes = explode(",", $ProteinArr['BioFilter']);
        }
      }
    }  
    //----Process 'BT'-------------------------------------------
    if($BaitArr['GeneID'] && $hitsArr['GeneID'] && ($BaitArr['GeneID'] == $hitsArr['GeneID'])){
      array_push($tmpHitNotes, 'BT');
    }  
  }
} 
  
function get_expFilter_arr(&$hitsArr){
  global $tmpHitNotes,$HITSDB,$proteinDB,$BaitArr,$totalGenes,$HitGeneName,$HitFrequency,$AccessProjectID,$hitType,$LocusTag;
  global $NSfilteIDarr;
  if($hitsArr['GeneID']){
    if($hitType != 'TPPpep'){
      $geneType = get_Gene_ID_type($hitsArr['GeneID']);
      if($geneType == 'NCBI'){
        $SQL = "SELECT LocusTag, GeneName, BioFilter FROM Protein_Class WHERE  EntrezGeneID='".$hitsArr['GeneID']."'";
      }else if($geneType == 'ENS'){
        $SQL = "SELECT GeneName FROM Protein_ClassENS WHERE  ENSG='".$hitsArr['GeneID']."'";
      }
      $ProteinArr = $proteinDB->fetch($SQL);
      if($ProteinArr){
        if($geneType == 'NCBI' && $ProteinArr['LocusTag'] && $ProteinArr['LocusTag'] != "-"){
          $LocusTag = $ProteinArr['LocusTag'];
        }
        if($ProteinArr['GeneName']){
          $HitGeneName = $ProteinArr['GeneName'];
        }
        //---Process Bio Filters--------------------------------------------------    
        if(isset($ProteinArr['BioFilter']) && $ProteinArr['BioFilter']){
          $tmpHitNotes = explode(",", $ProteinArr['BioFilter']);
        }
      }
    }  
    //----Process 'BT'-------------------------------------------
    if($BaitArr['GeneID'] && $hitsArr['GeneID'] && ($BaitArr['GeneID'] == $hitsArr['GeneID'])){
      array_push($tmpHitNotes, 'BT');
    }  
  }
  if($hitType == 'normal' || $hitType == 'TPP' || $hitType == 'geneLevel'){
    if($hitsArr['GeneID']){
      //---Process NS-------------------------------------------------------------------
      if(in_array($hitsArr['GeneID'], $NSfilteIDarr)){
        array_push($tmpHitNotes, 'NS');
      }
    }
    if($hitsArr['ID'] && ($hitType == 'normal' || $hitType == 'geneLevel')){
    //------Process CO, ME, RI, SO,  ----------------------------------------
      $SQL = "SELECT FilterAlias FROM HitNote WHERE HitID='".$hitsArr['ID']."'";
      $HitNoteArr = $HITSDB->fetchAll($SQL);
      if($HitNoteArr){
        for($n=0; $n<count($HitNoteArr); $n++){
          if($HitNoteArr[$n]['FilterAlias']){
            array_push($tmpHitNotes, $HitNoteArr[$n]['FilterAlias']);
          }
        }
      }
    }
  //-----------Process OP--------------------------------------------------------------------------
    if($hitType == 'normal'){
      $SQL = "SELECT B.BandMW FROM Band B, PlateWell P WHERE B.ID=P.BandID AND P.ID='".$hitsArr['WellID']."'";
      $BandMWArr = $HITSDB->fetch($SQL);
      $tmpNum = 0;
      if($BandMWArr && $BandMWArr['BandMW']){
        if($BandMWArr['BandMW'] > 0){
    		  $tmpNum = abs(($BandMWArr['BandMW'] - $hitsArr['MW'])*100/$BandMWArr['BandMW']);
        }
    		if($BandMWArr['BandMW'] < 25 or $BandMWArr['BandMW'] > 100){
    			if($tmpNum > 50){
    				array_push($tmpHitNotes, "AW");
    			}
        }else{
          if($tmpNum > 30 ){
    				array_push($tmpHitNotes, "AW");
    			}
        } 
      }//from checkCarryOver.inc.php=====================
    }
  }
}

//===========================================================================
function get_exp_filter_arr(&$expAliasArr,&$hitsArr){
	global $HITSDB,$hitType;
  if($hitType == 'normal' || $hitType == 'geneLevel'){
    //------Process CO, ME, RI, SO,  ----------------------------------------
  	$SQL = "SELECT FilterAlias FROM HitNote WHERE HitID='".$hitsArr['ID']."'";
  	$HitNoteArr = $HITSDB->fetchAll($SQL);
  	if($HitNoteArr){
  	  for($n=0; $n<count($HitNoteArr); $n++){
  	    if($HitNoteArr[$n]['FilterAlias']){
  	      array_push($expAliasArr, $HitNoteArr[$n]['FilterAlias']);
  	    }
  	  }
  	}
  }  
	if($hitType != "TPP"){
		$SQL = "SELECT B.BandMW FROM Band B, PlateWell P WHERE B.ID=P.BandID AND P.ID='".$hitsArr['WellID']."'";
		$BandMWArr = $HITSDB->fetch($SQL);
		$tmpNum = 0;
		if($BandMWArr && $BandMWArr['BandMW']){
		  if($BandMWArr['BandMW'] > 0){
		  	$tmpNum = abs(($BandMWArr['BandMW'] - $hitsArr['MW'])*100/$BandMWArr['BandMW']);
			}
			if($BandMWArr['BandMW'] < 25 or $BandMWArr['BandMW'] > 100){
				if($tmpNum > 50){
					array_push($expAliasArr, "AW");
				}
			}else{
		    if($tmpNum > 30 ){
					array_push($expAliasArr, "AW");
				}
		  } 
		}
	}
}

function get_total_genes(){
  global $HITSDB,$AccessProjectID;
  $SQL = "SELECT BaitGeneID FROM BaitToHits 
          WHERE ProjectID ='".$AccessProjectID."'         
          GROUP BY BaitGeneID";     
  return $totalGenes = $HITSDB->get_total($SQL);
}
//===end function for reports=============================
function get_tpp_pep_typeExpArr(&$typeExpArr){
  global $hitType;  
  $tppExpFilterStr = "TPP Probability,PBT,#fbe497,,0,<,Expect,:Coverage,COV,#e7d158,,0,<,Coverage,:Unique Peptide,UPT,#ddb331,,0,<,Pep_num_uniqe,:Total Peptide,TPT,#c88f1a,,0,<,Pep_num,";
  $tppPepExpFilterStr = "TPP Probability,PBT,#fbe497,,0,>=,TP.Probability,<:Hyper score,HSR,#e7d158,,0,>=,TP.Score1,<:Ion,ION,#ddb331,,0,>=,TP.Ions,<:Exclude charges 1+,CH1,#c88f1a,,0,!=,TP.Charge,=:2+,CH2,#c88f1a,,0,!=,TP.Charge,=:3+,CH3,#c88f1a,,0,!=,TP.Charge,=";
    
  if($hitType == 'TPPpep'){
    $tmpTppArr = explode(':',$tppPepExpFilterStr);  
  }
  foreach($tmpTppArr as $tppValue){
    $tmpPare = array();
    $tmpArr = explode(',',$tppValue);
    $tmpPare['Name'] = $tmpArr[0];
    $tmpPare['Alias'] = $tmpArr[1];
    $tmpPare['Color'] = $tmpArr[2];
    $tmpPare['Init'] = $tmpArr[3];
    $tmpPare['Counter'] = $tmpArr[4];
    $tmpPare['Operator'] = $tmpArr[5];
    $tmpPare['DBfieldName'] = $tmpArr[6];
    $tmpPare['Operator2'] = $tmpArr[7];
    array_push($typeExpArr, $tmpPare);
  }
}

function get_color_arr(&$colorArr){
  $colorArr = array('C5CBF7','A9A850','99ffcc','AC9A72','F6B2A9','DF9DF7','884D9E','798AF9','687CFA','AE15E7',
                  'D5CCCD','586EFA','8ED0F5','69B0D8','4C90B7','54F4F6','82ACAD','909595','A0F4B8','7BBC8D',
                  '43CB69','E9E86F','A7B2F6','ffff99','99ffff','99cc00','999900','ffccff','006600','6666ff',
                  '663399','0000ff','cc3300','0099ff','9999ff','99ccff','996600','cc99ff','ff3300','ff66ff',
                  'ff00ff','99ccff','996600','00ff00','990000','993333','99cc33','9999ff','ccccff','9933cc',
                  'ffffcc','ccffff','ccff99','ccff33','E6B751','99ff00','ff00ff','6633ff','6633ff','6600ff',
                  'ffffff','66ffcc','ffcccc','66cccc','ff99cc','6699cc','ff66cc','6666cc','ff33cc','6633cc',
                  'ffff66','66ff66','ffcc66','66cc66','ff9966','669966','ff6666','666666','ff3366','663366',
                  '99ff33','00ff33','99cc33','00cc33','999933','009933','996633','006633','993333','003333',
                  '99ffcc','00ffcc','99cccc','00cccc','9999cc','0099cc','9966cc','0066cc','9933cc','0033cc');
}

function get_item_general_info($type,$item_ID,&$isGelFree){
  global $level_1_arr,$level_2_arr,$BaitArr,$HITSDB,$AccessProjectID,$exp_arr,$hitType;
  global $level2_SQL_str_arr;
  
  if($type == "Sample" && $item_ID){
    $SQL = "SELECT ".$level2_SQL_str_arr["Bait"].
            " FROM Band B 
            LEFT JOIN Bait BA ON(B.BaitID=BA.ID) 
            WHERE B.ID='$item_ID'";
    $level_1_arr = $HITSDB->fetch($SQL);
    $geneNameSub = '';
    if($level_1_arr['Tag'] && $level_1_arr['Mutation']){
      $geneNameSub = " (".$level_1_arr['Tag'].",".$level_1_arr['Mutation'].")";
    }elseif($level_1_arr['Tag']){
      $geneNameSub = " (".$level_1_arr['Tag'].")";
    }elseif($level_1_arr['Mutation']){
      $geneNameSub = " (".$level_1_arr['Mutation'].")";
    }
    $level_1_arr['GeneName'] = $level_1_arr['GeneName'].$geneNameSub;
    
    $BaitArr = $level_1_arr;
    $isGelFree = $level_1_arr['GelFree'];
    
    $tmpStr = '';            
    foreach($level2_SQL_str_arr as $key => $value){
      if($isGelFree){
        if($key == 'Band' || $key == 'Experiment'){
          if($tmpStr) $tmpStr .= ',';
          $tmpStr .= $value;
        }
      }else{
        if($key != 'Bait'){
          if($tmpStr) $tmpStr .= ',';
          $tmpStr .= $value;
        }
      }
    }
    if($isGelFree){
      $SQL = "SELECT ". $tmpStr .
              " FROM Band B
              LEFT JOIN Experiment E ON (B.ExpID=E.ID)
              WHERE B.ID = '$item_ID'";
    }else{
      $SQL = "SELECT ". $tmpStr .
            " FROM (Band B
              LEFT JOIN Lane L ON (B.LaneID=L.ID)
              LEFT JOIN PlateWell PW ON (B.ID=PW.BandID)
              LEFT JOIN Experiment E ON (B.ExpID=E.ID))
              LEFT JOIN Gel G ON (L.GelID = G.ID)
              LEFT JOIN Plate P ON(PW.PlateID = P.ID)
              WHERE B.ID = '$item_ID'";
    }
//echo "$SQL";exit;                 
    $level_2_arr = $HITSDB->fetchAll($SQL);
    add_item_to_level2_arr($level_2_arr);    
  }elseif($type == "Experiment" && $item_ID){  
   $SQL = "SELECT ".$level2_SQL_str_arr["Bait"].
          " FROM Experiment E 
            LEFT JOIN Bait BA ON(E.BaitID=BA.ID) 
            WHERE E.ID='$item_ID'";
    $level_1_arr = $HITSDB->fetch($SQL);
    
    $geneNameSub = '';
    if($level_1_arr['Tag'] && $level_1_arr['Mutation']){
      $geneNameSub = " (".$level_1_arr['Tag'].",".$level_1_arr['Mutation'].")";
    }elseif($level_1_arr['Tag']){
      $geneNameSub = " (".$level_1_arr['Tag'].")";
    }elseif($level_1_arr['Mutation']){
      $geneNameSub = " (".$level_1_arr['Mutation'].")";
    }
    $level_1_arr['GeneName'] = $level_1_arr['GeneName'].$geneNameSub;
    
    $BaitArr = $level_1_arr;
    $isGelFree = $level_1_arr['GelFree'];
    
    $tmpStr = '';        
    foreach($level2_SQL_str_arr as $key => $value){
      if($isGelFree){
        if($key == 'Band' || $key == 'Experiment'){
          if($tmpStr) $tmpStr .= ',';
          $tmpStr .= $value;
        }
      }else{
        if($key != 'Bait'){
          if($tmpStr) $tmpStr .= ',';
          $tmpStr .= $value;
        }
      }
    }
    if($isGelFree){
       $SQL = "SELECT ". $tmpStr .
              " FROM Band B
              LEFT JOIN Experiment E ON (B.ExpID=E.ID)
              WHERE B.ExpID = '$item_ID'";
    }else{
      $SQL = "SELECT ". $tmpStr .
            " FROM (Band B
              LEFT JOIN Lane L ON (B.LaneID=L.ID)
              LEFT JOIN PlateWell PW ON (B.ID=PW.BandID)
              LEFT JOIN Experiment E ON (B.ExpID=E.ID))
              LEFT JOIN Gel G ON (L.GelID = G.ID)
              LEFT JOIN Plate P ON(PW.PlateID = P.ID)
              WHERE B.ExpID = '$item_ID'";
    }              
    $level_2_arr = $HITSDB->fetchAll($SQL);
    add_item_to_level2_arr($level_2_arr);
    $SQL = "SELECT 
      ID, 
      Name, 
      OwnerID,
      PreySource, 
      DateTime 
      FROM Experiment
      WHERE ID = '$item_ID'";
    $exp_arr = $HITSDB->fetchAll($SQL);
    
    $hitsTable = 'Hits';    
    if($hitType == 'TPP'){
      $hitsTable = 'TppProtein';
    }elseif($hitType == 'TPPpep'){
      $hitsTable = 'TppPeptide';
    }elseif($hitType == 'geneLevel'){
      $hitsTable = 'Hits_GeneLevel';
    }
    
    for($i=0; $i<count($exp_arr); $i++){
      $SQL = "SELECT H.ID
              FROM $hitsTable H, Band B, Experiment E
              WHERE E.ID='".$exp_arr[$i]['ID']."' AND E.ID=B.ExpID 
              AND B.ID=H.BandID LIMIT 1";
      if($tmpArr = $HITSDB->fetchAll($SQL)){
        $exp_arr[$i]['Empty'] = 0;
      }else{
        $exp_arr[$i]['Empty'] = 1;
      }      
    }
  }elseif($type == "Bait" && $item_ID){
    $SQL = "SELECT ".$level2_SQL_str_arr["Bait"].
      " FROM Bait BA WHERE BA.ID='$item_ID' AND BA.ProjectID=$AccessProjectID";  
    $level_1_arr = $HITSDB->fetch($SQL);
    $geneNameSub = '';
    
    
    if(!$level_1_arr['GeneID']){
      if(!$level_1_arr['BaitAcc']){
        $level_1_arr['GeneID'] = $level_1_arr['GeneName'];
      }else{
        $level_1_arr['GeneID'] = $level_1_arr['BaitAcc'];
      }
    }
    if($level_1_arr['Tag'] && $level_1_arr['Mutation']){
      $geneNameSub = " (".$level_1_arr['Tag'].",".$level_1_arr['Mutation'].")";
    }elseif($level_1_arr['Tag']){
      $geneNameSub = " (".$level_1_arr['Tag'].")";
    }elseif($level_1_arr['Mutation']){
      $geneNameSub = " (".$level_1_arr['Mutation'].")";
    }
    $level_1_arr['GeneName'] = $level_1_arr['GeneName'].$geneNameSub;
    $BaitArr = $level_1_arr;
    $isGelFree = $level_1_arr['GelFree'];
    $tmpStr = '';
    foreach($level2_SQL_str_arr as $key => $value){
      if($isGelFree){
        if($key == 'Band' || $key == 'Experiment'){
          if($tmpStr) $tmpStr .= ',';
          $tmpStr .= $value;
        }
      }else{
        if($key != 'Bait'){
          if($tmpStr) $tmpStr .= ',';
          $tmpStr .= $value;
        }
      }
    }       
    if($isGelFree){
      $SQL = "SELECT ". $tmpStr .
            " FROM Band B
              LEFT JOIN Experiment E ON (B.ExpID=E.ID)
              WHERE B.BaitID = '$item_ID'
              ORDER BY E.ID, B.ID";
    }else{
      $SQL = "SELECT ". $tmpStr .
            " FROM (Band B
              LEFT JOIN Lane L ON (B.LaneID=L.ID)
              LEFT JOIN PlateWell PW ON (B.ID=PW.BandID)
              LEFT JOIN Experiment E ON (B.ExpID=E.ID))
              LEFT JOIN Gel G ON (L.GelID = G.ID)
              LEFT JOIN Plate P ON(PW.PlateID = P.ID)
              WHERE B.BaitID = '$item_ID'
              ORDER BY E.ID, B.ID";              
              
    }
    $level_2_arr = $HITSDB->fetchAll($SQL);    
    add_item_to_level2_arr($level_2_arr);
    $SQL = "SELECT 
      ID, 
      Name, 
      OwnerID,
      PreySource, 
      DateTime 
      FROM Experiment
      WHERE BaitID = '$item_ID'";
    $exp_arr = $HITSDB->fetchAll($SQL);
    
    $hitsTable = 'Hits';    
    if($hitType == 'TPP'){
      $hitsTable = 'TppProtein';
    }elseif($hitType == 'TPPpep'){
      $hitsTable = 'TppPeptide';
    }elseif($hitType == 'geneLevel'){
      $hitsTable = 'Hits_GeneLevel';
    }
    
    for($i=0; $i<count($exp_arr); $i++){
      $SQL = "SELECT H.ID
              FROM $hitsTable H, Band B, Experiment E
              WHERE E.ID='".$exp_arr[$i]['ID']."' AND E.ID=B.ExpID 
              AND B.ID=H.BandID LIMIT 1";
      if($tmpArr = $HITSDB->fetchAll($SQL)){
        $exp_arr[$i]['Empty'] = 0;
      }else{
        $exp_arr[$i]['Empty'] = 1;
      }      
    }
    $BaitArr = $level_1_arr;
  }elseif($type == "Plate" && $item_ID){
    $SQL = "SELECT ".$level2_SQL_str_arr["Plate"].
           " FROM Plate P WHERE  P.ID='$item_ID' AND P.ProjectID='$AccessProjectID'";    
    $level_1_arr = $HITSDB->fetch($SQL);
    $tmpStr = '';
    foreach($level2_SQL_str_arr as $key => $value){
      if($key != $type){
        if($tmpStr) $tmpStr .= ',';
        $tmpStr .= $value;
      }
    }
    $SQL = "SELECT ". $tmpStr .
            " FROM (Band B
            LEFT JOIN Lane L ON (B.LaneID=L.ID)
            LEFT JOIN PlateWell PW ON (B.ID=PW.BandID)
            LEFT JOIN Experiment E ON (B.ExpID=E.ID)
            LEFT JOIN Bait BA ON (B.BaitID=BA.ID))
            LEFT JOIN Gel G ON (L.GelID = G.ID)
            WHERE PW.PlateID = '$item_ID'
            ORDER By BA.ID, PW.WellCode";
    $level_2_arr = $HITSDB->fetchAll($SQL);
    add_item_to_level2_arr($level_2_arr);
  }elseif($type == "Gel" && $item_ID){
    $SQL = "SELECT ".$level2_SQL_str_arr[$type].
           " FROM $type G WHERE  G.ID='$item_ID' AND G.ProjectID='$AccessProjectID'";  
    $level_1_arr = $HITSDB->fetch($SQL);
    $tmpStr = '';
    foreach($level2_SQL_str_arr as $key => $value){
      if($key != $type){
        if($tmpStr) $tmpStr .= ',';
        $tmpStr .= $value;
      }
    }
    $SQL = "SELECT ". $tmpStr .
            " FROM (Band B
            LEFT JOIN Lane L ON (B.LaneID=L.ID)
            LEFT JOIN PlateWell PW ON (B.ID=PW.BandID)
            LEFT JOIN Experiment E ON (B.ExpID=E.ID)
            LEFT JOIN Bait BA ON (B.BaitID=BA.ID))
            LEFT JOIN Plate P ON (PW.PlateID=P.ID)
            WHERE L.GelID = '$item_ID'
            ORDER BY L.LaneNum, B.ID";
    $level_2_arr = $HITSDB->fetchAll($SQL);
    add_item_to_level2_arr($level_2_arr);
  }else{
    exit;
  }  
}

function add_item_to_main_arr($tmp_name_id,&$mainArr){
  global $RawFile_SQL_str,$ProtocolLableArr,$msManagerDB;
  foreach($mainArr as $key => $value){
    if(array_key_exists($key, $ProtocolLableArr)){
      $single_arr = explode(',',$value);
      $mainArr[$key] = $single_arr[0];
    }
  }  
  //--------------
  $instrumentArr = array();
  if($tmp_name_id){
    $WellID_arr = array();
    if(preg_match("/(.+);$/", $tmp_name_id, $matches)) $tmp_name_id = $matches[1];
    $tmpArr = explode(";",$tmp_name_id);
    $M_fileID_arr = array();
    foreach($tmpArr as $tmpVal){
      $tmp_arr1 = explode(":",$tmpVal);
      if(!array_key_exists($tmp_arr1[0], $M_fileID_arr)){
        $M_fileID_arr[$tmp_arr1[0]] = $tmp_arr1[1];
      }else{
        $M_fileID_arr[$tmp_arr1[0]] .= ",".$tmp_arr1[1];
      }
    }  
    
    foreach($M_fileID_arr as $key => $val){
      if(preg_match("/(\w+)tppResults/i", $key,$matches)){
        $table_name = $matches[1];
        $stable_name = $key;
      }else{
        $table_name = $key;
        $stable_name = $key."SearchResults";
      }
      
      $SQL = "SELECT ID 
              FROM $table_name             
              WHERE ID IN($val)
			  ORDER BY RAW_ID";
      $tmp_Arrs = $msManagerDB->fetch($SQL);
	    if($tmp_Arrs) $WellID_arr[] = $tmp_Arrs['ID'];
      
      /*$SQL = "SELECT S.WellID 
              FROM $table_name T 
              LEFT JOIN $stable_name S
              ON S.WellID = T.ID
              WHERE S.WellID IN($val) 
              AND (S.SavedBy IS NOT NULL OR S.SavedBy !='' OR S.SavedBy !=0)
              AND (T.RAW_ID IS NULL OR T.RAW_ID='' OR T.RAW_ID=0)";
      $tmp_Arrs = $msManagerDB->fetchAll($SQL);
      foreach($tmp_Arrs as $tmp_Vals){
        if(!in_array($tmp_Vals['WellID'], $WellID_arr)){
          $WellID_arr[] = $tmp_Vals['WellID'];
        }
      }*/
    }
    
    foreach($WellID_arr as $WellID_val){
      $SQL = "SELECT ".$RawFile_SQL_str.
            " FROM ".$table_name."                 
            WHERE ID = $WellID_val";
      if($tmp_instrumentArr = $msManagerDB->fetch($SQL)){
        $RawFilePath = get_rawfile_path($WellID_val,$table_name,$msManagerDB);
        $tmp_r_arr = explode("/", $RawFilePath);
        $tmp_item = array_pop($tmp_r_arr);
        if(!$tmp_item){
          $tmp_item = array_pop($tmp_r_arr);
        }
        $RawFilePath = implode("/", $tmp_r_arr);        
        $tmp_instrumentArr['RawFilePath'] = $RawFilePath;
        $parameters = get_parameters($WellID_val,$table_name);
        foreach($parameters as $key => $value){
          $tmpValue = $value;
          if(!array_key_exists($key, $tmp_instrumentArr)) {
            $tmp_instrumentArr[$key] = $tmpValue;
          }
        }
        $instrumentArr[$WellID_val] = $tmp_instrumentArr;
      }
    }
  }
  
  $last_instrumentArr = array();
  foreach($instrumentArr as $tmp_arr){
    foreach($tmp_arr as $tmp_key => $tmp_val){
      if(!array_key_exists($tmp_key, $last_instrumentArr)){
        $last_instrumentArr[$tmp_key] = $tmp_val;
      }else{
        $last_instrumentArr[$tmp_key] .= '|'.$tmp_val;
      }
    }
  }
  
  if(!$last_instrumentArr){
    $empty_instrumentArr = array(
      'FileID' => '',
      'FileName' => '',
      'Date' => '',
      'Size' => '',
      'RawFilePath' => '',
      'WellID' => '',
      'TaskID' => '',
      'Convert_Parameters' => '',
      'SearchEngines' => '',
      'TppTaskID' => ''
    );
    $last_instrumentArr = $empty_instrumentArr;
  }
  foreach($last_instrumentArr as $key => $val){
    if(!array_key_exists($key, $mainArr)){
      $mainArr[$key] = $val;
    }
  }
}

function get_parameters($fileID,$BaseTable_name){
  global $msManagerDB;
  global $task_tpptask_ids_arr;
  $rt_arr = array();       
  $SQL = "SELECT SR.WellID, SR.TaskID, ST.Parameters, ST.LCQfilter AS Convert_Parameters, ST.SearchEngines
          FROM ".$BaseTable_name."SearchResults SR
          LEFT JOIN ".$BaseTable_name."SearchTasks ST
          ON SR.TaskID=ST.ID 
          WHERE SR.WellID='$fileID' AND SR.SavedBy>0 
          GROUP BY SR.WellID";
  $tmp_Arr = $msManagerDB->fetch($SQL);
  if(!$tmp_Arr){
    return $rt_arr;
  }else{
    foreach($tmp_Arr as $tmp_key => $tmp_val){
      $rt_arr[$tmp_key] = $tmp_val;
    }  
    $SQL = "SELECT SR.TppTaskID, ST.Parameters AS TppParameters
            FROM ".$BaseTable_name."tppResults SR
            LEFT JOIN ".$BaseTable_name."tppTasks ST
            ON SR.TppTaskID=ST.ID 
            WHERE SR.WellID='$fileID' AND SR.SavedBy>0
            GROUP BY SR.WellID";
    $tmp_TPP_Arr = $msManagerDB->fetch($SQL);
    if($tmp_TPP_Arr){ 
      foreach($tmp_TPP_Arr as $tmp_key => $tmp_val){
        $rt_arr[$tmp_key] = $tmp_val;
      }
    }
    $tmp_id_key = '';
    if($tmp_Arr['TaskID'] && isset($tmp_TPP_Arr['TppTaskID']) && $tmp_TPP_Arr['TppTaskID']){
      $tmp_id_key = $tmp_Arr['TaskID'].'_'.$tmp_TPP_Arr['TppTaskID'];
    }elseif(isset($tmp_Arr['TaskID'])){
      $tmp_id_key = $tmp_Arr['TaskID'];
    }
    
    if($tmp_id_key && !array_key_exists($tmp_id_key, $task_tpptask_ids_arr)){
      $task_tpptask_ids_arr[$tmp_id_key] = $rt_arr;
      
      $search_SearchEngines = $rt_arr['SearchEngines'];
      $pattern = '/;([^;]+):;/';
      if(preg_match($pattern, $search_SearchEngines, $matches)){
        $task_tpptask_ids_arr[$tmp_id_key]['SearchEngines'] = $matches[1];
      }else{
        $tmp_arr = explode(";", $rt_arr['SearchEngines']);
        $tmp_s = '';
        foreach($tmp_arr as $tmp_val){
          $tmp_arr2 = explode("=", $rt_arr['SearchEngines']);
          if($tmp_s) $tmp_s .= ':';
          $tmp_s .= $tmp_arr2[0];
        }
        $task_tpptask_ids_arr[$tmp_id_key]['SearchEngines'] = $tmp_s;
      }
    }else{
      $task_tpptask_ids_arr[$tmp_id_key]['WellID'] .= "|".$rt_arr['WellID'];
    }    
    $last_rt_arr = array();
    foreach($rt_arr as $rt_key => $rt_val){
      if($rt_key == 'Parameters' || $rt_key == 'TppParameters') continue;
      $last_rt_arr[$rt_key] = $rt_val;
    }
    return $last_rt_arr;
  }
}

function add_item_to_level2_arr(&$level_2_arr){
  for($i=0; $i<count($level_2_arr); $i++){
    $tmp_name_id = $level_2_arr[$i]['RawFile'];
    add_item_to_main_arr($tmp_name_id,$level_2_arr[$i]);
  }
}    
    
function print_table_sub_head(&$arr2_value){
  global $type,$handle,$subHeadFlag,$hitType,$level2_lable_array,$fileDelimit,$level1_header,$ProtocolLableArr;
  
  $bandLable = "Band ID: <b>".$arr2_value['ID']."</b>";   
  if($hitType != 'TPPpep') $bandLable .= "&nbsp;&nbsp; Observed MW: <b>".$arr2_value['BandMW']."</b> kDa";   
  $bandLable .= "&nbsp;&nbsp; Band Code: <b>".$arr2_value['Location']."</b><br>";
  
  if($type == "Bait"){
  ?>
    <tr bgcolor="">
    <td colspan=15><div class=maintext_color><hr width="100%" size="1" noshade>
      <?php echo $bandLable;?>
      Experiment ID: <b><?php echo $arr2_value['ExpID']?></b>
      &nbsp; Experiment Name: <b><?php echo $arr2_value['ExpName']?></b>
      </div>
    </td>
   </tr>
  <?php 
  }elseif($type == "Experiment"){
  ?>
    <tr bgcolor="">
    <td colspan=15><div class=maintext_color><hr width="100%" size="1" noshade>
      <?php echo $bandLable;?>
      </div>
    </td>
   </tr>
  <?php 
  }elseif($type == "Plate"){
  ?>  
  <tr bgcolor="">
    <td colspan=15><div class=maintext_color><hr width="100%" size="1" noshade>
      <?php echo $bandLable;?>           
      Bait ID: <b><?php echo $arr2_value['BaitID']?></b>&nbsp: Bait Gene: <b><?php echo $arr2_value['GeneID']."/".$arr2_value['GeneName']?></b>
      &nbsp; Bait LocusTag: <b><?php echo $arr2_value['LocusTag']?></b>              
      &nbsp; Bait MW: <b><?php echo $arr2_value['BaitMW']?></b>
    </div>
    </td>
   </tr>
  <?php 
    $arr2_value['GeneName'] = str_replace(",", ";", $arr2_value['GeneName']);
    $arr2_value['LocusTag'] = str_replace(",", ";", $arr2_value['LocusTag']);
  }elseif($type == "Gel"){
  ?>
  <tr bgcolor="">
    <td colspan=15><div class=maintext_color><hr width="100%" size="1" noshade>
      <?php echo $bandLable;?> 
      Lane Number: <b><?php echo $arr2_value['LaneNum']?></b>
      &nbsp;&nbsp;Lane Code: <b><?php echo $arr2_value['LaneCode']?></b>
      </div>
    </td>
  </tr> 
  <?php 
  }
  foreach($level2_lable_array as $tmpKey => $tmpLable){
    if($tmpKey == $level1_header) continue;
    $fileLevel_2_str = '';
    foreach($tmpLable as $lableKey => $lableVel){
      if($fileLevel_2_str) $fileLevel_2_str .= $fileDelimit;
      if(array_key_exists($lableKey, $arr2_value)){
        $single_str = str_replace(",", ";", $arr2_value[$lableKey]);
        $single_str = str_replace("\n", "", $single_str);
        $fileLevel_2_str .= $lableVel.'==='.$single_str;
      }else{
        $fileLevel_2_str .= $lableVel.'===';
      }  
    }
    $fileLevel_2_str = $tmpKey.'::'.$fileLevel_2_str."\r\n";
    fwrite($handle, $fileLevel_2_str);  
  }
}
function get_hit_type($itemID, $itemType){
  global $HITSDB;
  $hisType = '';
  if($itemType == 'Band'){
    if(!$itemID){
      return '';
    }else{
      $bandStr = $itemID;
    }  
  }else{
    if($itemType == 'Bait'){
      $SQL = "SELECT B.ID FROM Band B, Bait BA WHERE B.BaitID=BA.ID AND BA.ID=$itemID";
    }elseif($itemType == 'Plate'){
      $SQL = "SELECT B.ID FROM Band B, PlateWell PW, Plate P WHERE B.ID=PW.BandID AND PW.PlateID=P.ID AND P.ID=$itemID";
    }elseif($itemType == 'Gel'){
      $SQL = "SELECT B.ID FROM Band B, Lane L, Gel G WHERE B.LaneID=L.ID AND L.GelID=G.ID AND G.ID='$itemID'";
    }
    if($bandArr = $HITSDB->fetchAll($SQL)){
      $bandStr = '';
      foreach($bandArr as $bandID){
        if($bandStr) $bandStr .= ",";
        $bandStr .= $bandID['ID'];
      }
    }else{
      return '';
    }
  }  
  if(strstr($bandStr, ',')){
    $band_sql = " BandID IN($bandStr) ";
  }else{
    $band_sql = " BandID='$bandStr' ";
  }
  $SQL = "SELECT ID FROM Hits WHERE $band_sql LIMIT 1";
  $tmpArr = $HITSDB->fetch($SQL);
  if(count($tmpArr)){
    $hisType = 'normal';
  }else{
    $SQL = "SELECT ID FROM TppProtein WHERE $band_sql LIMIT 1";
    $tmpArr = $HITSDB->fetch($SQL);
    if(count($tmpArr)){
      $hisType = 'TPP';
    }else{
      $SQL = "SELECT ID FROM Hits_GeneLevel WHERE $band_sql LIMIT 1";
      $tmpArr = $HITSDB->fetch($SQL);
      if(count($tmpArr)){
        $hisType = 'geneLevel';
      }  
    }
  }
  return $hisType;
}

function write_file_line(){
  global $columnsArr,$mapArr,$Delimit,$handle_write,$previewArr,$theaction,$buffer,$level_flag_counter,$current_bait_id;
  global $AccessProjectID,$log_handle_write,$uniqe_hit_gene_arr,$duplicate_log_title,$filename_out;
  global $lowest_level_item,$lowest_level_item_unique_id_arr; 
  if($theaction == 'generate_report'){
    global $handle_write;
  }            
  $valueLine = '';  
  if($theaction == 'generate_report' && isset($mapArr['Bait']) && isset($mapArr['level3'])&& !$level_flag_counter){
    if($current_bait_id != $mapArr['Bait'][0]){
      $current_bait_id = $mapArr['Bait'][0];
      $uniqe_hit_gene_arr = array();
      if($mapArr['level3'][1]){
        array_push($uniqe_hit_gene_arr,$mapArr['level3'][1]);
      }else{
        array_push($uniqe_hit_gene_arr,$mapArr['level3'][4]."_p");
      }
    }else{
      if($mapArr['level3'][1]){
        $hits_gene_id = $mapArr['level3'][1];
      }else{
        $hits_gene_id = $mapArr['level3'][4]."_p";
      }  
      if(!in_array($hits_gene_id, $uniqe_hit_gene_arr)){
        array_push($uniqe_hit_gene_arr,$hits_gene_id);
      }else{
        if(isset($duplicate_log_title) && !$duplicate_log_title){
          $duplicate_log_title = 1;
          $log_line = "Date: ".@date('Y-m-d h:i:s A').",Project ID: ".$AccessProjectID.",File Name: ".$filename_out."\r\n";
          fwrite($log_handle_write, $log_line);
          $log_line = "Bait ID,Hit Gene ID,Hit Protein ID\r\n";
          fwrite($log_handle_write, $log_line);
        }
        $log_line = $current_bait_id.",".$mapArr['level3'][1].",".$mapArr['level3'][4]."\r\n";
        fwrite($log_handle_write, $log_line);
      }
    }
  }
  
  if(isset($lowest_level_item) && $lowest_level_item){
    $unique_id = $mapArr[$lowest_level_item][0];
    if(!in_array($unique_id, $lowest_level_item_unique_id_arr)){
      array_push($lowest_level_item_unique_id_arr, $unique_id);
    }else{
      return;
    }
  }    
  for($i=0; $i<count($columnsArr); $i++){
    list($levelIndex,$childIndex) = explode('___',$columnsArr[$i]);
    if(isset($mapArr[$levelIndex])){
      $valueLine .= $mapArr[$levelIndex][$childIndex];
    }  
    if($i == count($columnsArr)-1){
      $valueLine .= "\r\n";
    }else{
      $valueLine .= $Delimit;
    }
  }
  if($theaction == 'generate_report'){
    if(isset($handle_write)){
      fwrite($handle_write, $valueLine);
    }  
  }else{  
    array_push($previewArr, $valueLine);
  }
}

function format_key_map($format_str){
  global $LableArr,$Experiment_lable1_arr,$ProtocolLableArr;
//---------------------------------------------  
  global $Protein_Length_index;
//---------------------------------------------
  $tmpArr1 = explode("@",$format_str);  
  $new_format_str = '';
  $new_format_arr = array();
  foreach($tmpArr1 as $tmpValue1){
    $tmpArr2 = explode("___", $tmpValue1);
    if($tmpArr2[0] != 'level4'){
      if($tmpArr2[0] == 'Experiment'){
        $itemArr = $Experiment_lable1_arr;
      }else{
        $itemArr = $LableArr[$tmpArr2[0]];
      }  
      $i = 0;
      foreach($itemArr as $itemKey => $itemVal){
        if($itemKey == $tmpArr2[1]){
          $singleItem = $tmpArr2[0]."___". $i;
          if(!in_array($singleItem, $new_format_arr)){
            array_push($new_format_arr, $singleItem);
          }
          if($itemKey == 'Protein_Length') $Protein_Length_index = $i;
          break;
        }elseif($tmpArr2[1] == 'Protocol' && $tmpArr2[0] == 'Experiment'){
          $tmp_itemArr = $LableArr[$tmpArr2[0]];
          $j=0;
          foreach($tmp_itemArr as $tmpKey => $tmpVal){
            if($tmpKey == 'Protocol'){
              break;
            }else{
              $j++;
            }
          }
          $i = $j;
          foreach($ProtocolLableArr as $tmpVal){
            $singleItem = $tmpArr2[0]."___". $i;
            if(!in_array($singleItem, $new_format_arr)){
              array_push($new_format_arr, $singleItem);
            }
            $i++;
          }
          break;  
        }else{
          $i++;
        }
      }
    }else{
      array_push($new_format_arr, $tmpValue1);
    }
    $new_format_str = implode("@", $new_format_arr);
  }
  return $new_format_str;
}

function tpp_table_field_translate_for_hits($inField){
  if($inField == 'ID'){
    $outField = 'ID';
  }elseif(strstr($inField, 'PROBABILITY')){
    $outField = str_replace('PROBABILITY', 'Expect', $inField);
  }elseif(strstr($inField, 'PERCENT_COVERAGE')){
    $outField = str_replace('PERCENT_COVERAGE', 'Coverage', $inField);
  }elseif(strstr($inField, 'TOTAL_NUMBER_PEPTIDES')){
    $outField = str_replace('TOTAL_NUMBER_PEPTIDES', 'Pep_num', $inField);    
  }elseif(strstr($inField, 'UNIQUE_NUMBER_PEPTIDES')){
    $outField = str_replace('UNIQUE_NUMBER_PEPTIDES', 'Pep_num_uniqe', $inField);
  }
  return $outField;
}

function get_mermission($mainDB, $scriptName, $userID){
  $oldDBname = to_defaultDB($mainDB);
  if($scriptName == "mng_set.php") $scriptName = "filter.php";
  $SQL = "SELECT ID FROM Page WHERE ScriptName='$scriptName'";
   
  $pageArr = $mainDB->fetch($SQL);
  $PagePermissionArr = array();  
  if(count($pageArr)){
    $SQL = "SELECT *                   
          FROM PagePermission 
          WHERE PageID=".$pageArr['ID']."
          AND UserID=$userID";
          
    $PagePermissionArr = $mainDB->fetch($SQL);
  }
  back_to_oldDB($mainDB, $oldDBname);
  if(!$PagePermissionArr){
    $PagePermissionArr = array("Insert" => 0, "Modify" => 0, "Delete" => 0);
  }
  return $PagePermissionArr;
}

function sort_filter_list(&$genePropertyArr,&$ENSgenePropertyArr,$order_by){
  global $geneArr,$ENSgeneArr,$indexArr;
  if(strstr($order_by, 'GeneName')){
    foreach($genePropertyArr as $key => $geneVal){
      if($geneVal['GeneName']){
        $index = $geneVal['GeneName']."_".$geneVal['TaxID'];
      }else{
        $index = "00000".$key."_".$geneVal['TaxID'];
      }
      $geneArr[$index] = $geneVal;
      $indexArr[$index] = "Protein_Class";
    }
    foreach($ENSgenePropertyArr as $key => $ENSgeneVal){
      if($ENSgeneVal['GeneName']){
        $index = $ENSgeneVal['GeneName']."_".$ENSgeneVal['TaxID'];
      }else{
        $index = "00000".$key."_".$ENSgeneVal['TaxID'];
      }
      if(!array_key_exists($index, $indexArr)){
        $indexArr[$index] = "Protein_ClassENS";
      }else{
         $indexArr[$index] = "both";
      }
      $ENSgeneArr[$index] = $ENSgeneVal;
    }
    if(stristr($order_by, 'desc')){
      krsort($indexArr);
    }else{
      ksort($indexArr);
    }
  }else{
    if(stristr($order_by, 'desc')){
      $genePropertyArr = array_merge($ENSgenePropertyArr, $genePropertyArr);
    }else{
      $genePropertyArr = array_merge($genePropertyArr,$ENSgenePropertyArr);
    }
  }
}
function print_filter_table(&$recoder,$option='',$frequency=''){
  global $permission,$USER,$bgcolor,$analyst_this_page_permission_arr;
  $GeneID = $recoder['EntrezGeneID'];
  $GeneName = $recoder['GeneName'];
  $GeneAliase = '';
  if(count($recoder) == 5){
    $LocusTag = $recoder['LocusTag'];
    if($LocusTag == "-") $LocusTag = '';
    $GeneAliase = $recoder['GeneAliase'];
    if($GeneAliase == "-" || $GeneAliase == "|" || !$GeneAliase){
      $GeneAliase = $LocusTag;
    }else{
      $GeneAliase = str_replace("|", "<br>&nbsp;&nbsp;", $GeneAliase);
      if($LocusTag && stristr($GeneAliase, $LocusTag) === FALSE){
        $GeneAliase .= "<br>&nbsp;&nbsp;".$LocusTag;
      }
    }      
  }
?>
  <tr bgcolor="<?php echo $bgcolor;?>">
    <td width="" align="left"><div class=maintext>&nbsp;
      <?php echo $GeneID;?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
      <?php echo $GeneName;?>&nbsp;
      </div>
    </td>
    <td width="" align="left"><div class=maintext>&nbsp;
      <?php echo $GeneAliase;?>&nbsp;
      </div>
    </td>
<?php if($frequency){?>
		<td width="" align="left"><div class=maintext>&nbsp;
      <?php echo $frequency;?>&nbsp;
      </div>
    </td>
<?php }?>		
    <td width="" align="left" valign="center"><div class=maintext>&nbsp;
      <?php 
      echo get_URL_str('', $GeneID, '');
      ?>
      </div>
    </td>
<?php if($option){?>    
    <td width="" align="left"><div class=maintext>&nbsp; &nbsp;
      <?php if($analyst_this_page_permission_arr['Delete']){?>
            <a href="javascript:confirm_delete('<?php echo $GeneID;?>');">
           <img border="0" src="images/icon_purge.gif" alt="Delete"></a>&nbsp;
      <?php }else{
	      	echo "<img src=\"images/icon_empty.gif\">";
	      }
      ?>    
      </div>
    </td>
<?php }?>    
  </tr>
<?php 
}
function sort_print_filter_table(&$genePropertyArr,&$ENSgenePropertyArr,$order_by,$option='',$display_frequency=''){
  global $indexArr,$geneArr,$ENSgeneArr,$frequencyArr;
  sort_filter_list($genePropertyArr,$ENSgenePropertyArr,$order_by);
	$frequency = '';
  if(strstr($order_by, 'GeneName')){
    foreach($indexArr as $key => $value){
      if($value == "Protein_Class"){
        if($display_frequency){
  				$frequency = $frequencyArr[$geneArr[$key]['EntrezGeneID']];
  			}
        print_filter_table($geneArr[$key],$option,$frequency);
      }elseif($value == "Protein_ClassENS"){
        if($display_frequency){
  				$frequency = $frequencyArr[$ENSgeneArr[$key]['EntrezGeneID']];
  			}
        print_filter_table($ENSgeneArr[$key],$option,$frequency);
      }else{
        if($display_frequency){
  				$frequency = $frequencyArr[$geneArr[$key]['EntrezGeneID']];
  			}
        print_filter_table($geneArr[$key],$option,$frequency);
        if($display_frequency){
  				$frequency = $frequencyArr[$ENSgeneArr[$key]['EntrezGeneID']];
  			}
        print_filter_table($ENSgeneArr[$key],$option,$frequency);
      }
    }
  }else{
    foreach($genePropertyArr as $key => $value){
      print_filter_table($value,$option,$frequency);
    }
  }
}
function get_gene_property(&$genePropertyArr,&$ENSgenePropertyArr,&$geneIDarr,$asWhat){
	global $proteinDB, $order_by;
	$geneStr = '';
	$ENSgeneStr = '';
	foreach($geneIDarr as $key => $value){
		if($asWhat == 'value'){
			$geneID = $value;
		}else{
			$geneID = $key;
		}
	  if(is_numeric($geneID)){
	    if($geneStr) $geneStr .= ",";
	    $geneStr .= $geneID;
	  }else{
	    if($ENSgeneStr) $ENSgeneStr .= ",";
	    $ENSgeneStr .= "'".$geneID."'";
	  }
	}
	
	$genePropertyArr = array();
	if($geneStr){
	  $SQL = "SELECT 
	          EntrezGeneID, 
	          LocusTag, 
	          GeneName, 
	          GeneAliase, 
	          TaxID 
	          FROM Protein_Class 
	          WHERE EntrezGeneID IN ($geneStr)
	          ORDER BY $order_by";
	  $genePropertyArr = $proteinDB->fetchAll($SQL);        
	}
	$ENSgenePropertyArr = array();
	if($ENSgeneStr){
	  $SQL2 = "SELECT 
	            ENSG as EntrezGeneID, 
	            GeneName, 
	            TaxID 
	            FROM 
	            Protein_ClassENS 
	            WHERE ENSG IN ($ENSgeneStr)";
	  if(strstr($order_by, 'EntrezGeneID')){
	    $ENSorder_by = str_replace('EntrezGeneID', 'ENSG', $order_by);
	    $SQL2 .= " ORDER BY $ENSorder_by";
	  }else{
	    $SQL2 .= " ORDER BY $order_by";
	  }
	  $ENSgenePropertyArr = $proteinDB->fetchAll($SQL2);
	}
}

function get_max_value($valueName){
  global $HITSDB,$currentType,$frm_selected_item_str,$MAX,$SearchEngine,$filter_for;
  global $hitType;
  global $Is_geneLevel;
    
  if(!isset($MAX) || !$MAX) $MAX = 'MAX';
  $frm_selected_item_str_tmp = '';  
  if(isset($filter_for) && $filter_for == 'item_report'){
    global $hitType,$searchEngineField;
    if($hitType == "TPP"){
      $SearchEngine = "TPP_";
    }elseif($hitType == "normal" || $hitType == "geneLevel"){
      $SearchEngine = $searchEngineField;
    }elseif($hitType == "TPPpep"){
      $SearchEngine = "TPPpep";
    }
    $frm_selected_item_str_tmp = $frm_selected_item_str;
    $itemID = 'BandID';
  }else{
    if($currentType == 'Exp'){
      if($frm_selected_item_str){
        $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($frm_selected_item_str)";
        if($tmp_arr = $HITSDB->fetchAll($SQL)){
          foreach($tmp_arr as $tmp_val){
            if($frm_selected_item_str_tmp) $frm_selected_item_str_tmp .= ',';
            $frm_selected_item_str_tmp .= $tmp_val['ID'];
          }
          $itemID = 'BandID';
        }
      }
    }else{
      $frm_selected_item_str_tmp = $frm_selected_item_str;
      $itemID = $currentType.'ID';
    }  
  }   
  if(!$frm_selected_item_str_tmp) return 5000;
	
  $outField = $valueName;
  if(strstr($SearchEngine, 'TPP_')){
    $tmp1 = hits_table_field_translate_for_tpp($valueName);
    $outField = $tmp1[0];
  }
  if($Is_geneLevel){
    $HitsTable = 'Hits_GeneLevel';
  }else{  
    $HitsTable = 'Hits';
  }  
  if($valueName == 'Expect2'){
    $MAX = 'MIN';
  }else{
    $MAX = 'MAX';
  }  
  if(strstr($SearchEngine, 'TPP_')){
    $HitsTable = 'TppProtein';
  }elseif($hitType == 'geneLevel'){
    $HitsTable = 'Hits_GeneLevel';
  }
	$SQL = "SELECT $MAX(`".$outField."`) as biggestNum FROM $HitsTable WHERE $itemID IN($frm_selected_item_str_tmp)";
  
	$hitsArrTmp2 = $HITSDB->fetch($SQL);
	$maxScore = $hitsArrTmp2['biggestNum'];
  if($maxScore < 0) $maxScore = -1 * $maxScore;  
  return $maxScore;
}

function create_filter_list($listName,$frmName){
  global $$frmName,$orderby,$SearchEngine;  
  global $M_SpecSum,$M_maxSpec;
  global $M_INTENSITYSUM,$M_INTENSITY;
  global $frm_selected_item_str;
  
  if($listName == 'Fequency' || $listName == 'Coverage'){
    $biggestNum = 100;
  }elseif($frmName == 'AvgP' || $frmName == 'maxSAINT' || $frmName == 'SaintScore' || $frmName == 'BFDR'){
    $biggestNum = 1;
  }elseif($frmName == 'SpecSum'){
    $biggestNum = $M_SpecSum;
  }elseif($frmName == 'maxSpec'){
    $biggestNum = $M_maxSpec;
  }elseif($frmName == 'IntensitySum'){
    $biggestNum = $M_INTENSITYSUM;
  }elseif($frmName == 'Intensity'){
    $biggestNum = $M_INTENSITY;    
  }elseif($frmName == 'NumReplicates' || $frmName == 'NumRep'){
    $biggestNum = 10; 
  }elseif($frmName == 'MaxP'){
    $biggestNum = 1;
  }elseif((strstr($SearchEngine, 'TPP_')) && $listName == 'Expect'){  
    $biggestNum = 1;
  }else{
    $biggestNum = get_max_value($listName);
    if($biggestNum == 5000 && ($listName == 'Unique' || $listName == 'Pep_num')){
      $biggestNum = 500;
    }elseif($listName == 'SpectralCount'){
      $biggestNum = 1500;
    }
  }     
  $sign = '';
  if($listName == 'Expect2'){
    $sign = '-';
  }
  $numbers = '0';
  if($frmName == 'AvgP' || $frmName == 'maxSAINT' || $frmName == 'SaintScore' || $frmName == 'MaxP'){ 
    $numbers = 0.45;
  }
  $kk = 1;
  if($frmName == 'NumRep'){
    //$kk = 2;
  }
  if($SearchEngine == "SEQUEST"){
    if($biggestNum >=10000000){
      $numLen = 1000000;
    }elseif($biggestNum <10000000 && $biggestNum >=1000000){
      $numLen = 100000;
    }elseif($biggestNum <1000000 && $biggestNum >=100000){
      $numLen = 10000;
    }elseif($biggestNum <100000 && $biggestNum >=10000){
      $numLen = 1000;
    }elseif($biggestNum <10000 && $biggestNum >1000){
      $numLen = 100;
    }elseif($biggestNum <=1000 && $biggestNum >=100){
      $numLen = 10;
    }elseif($biggestNum <=100 && $biggestNum >=10){
      $numLen = 1;
    }
  }else{
    if($frmName == 'SpecSum' || $frmName == 'maxSpec'){
      $numLen = 2;
    }elseif($biggestNum >=1000){
      $numLen = 10;
    }elseif($biggestNum <1000 && $biggestNum >=100){
      $numLen = 5;
    }elseif($biggestNum <100 && $biggestNum >=50){
      $numLen = 2;
    }elseif($biggestNum <50 && $biggestNum >=10){
      $numLen = 1;
    }elseif($biggestNum <10 && $biggestNum >1){
      $numLen = 0.1;
    //}elseif($biggestNum <=1 && $biggestNum >=1){
      //$numLen = 0.05;  
    }elseif($biggestNum <=1 && $biggestNum >=0.1){
      $numLen = 0.01;
    }
  }
  echo "<select name=\"$frmName\" size=1>\r\n";
  if($listName == '0'){
    echo "<option value='2' selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  }elseif($listName == 'Fequency'){
    echo "<option value='101' selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  }else{
    echo "<option value='0' selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  }
  $ss = 1;
  if($frmName == 'NumRep'){
    $ss = '2';
  }
  
  while($numbers < $biggestNum){
    if(($listName == 'Pep_num' || $listName == 'Unique'|| $listName == 'SpectralCount' || $listName == 'Fequency' || $listName == 'Coverage') && $numbers < 10){
      $numbers = $ss++;
      if($numbers >= 10) $kk = round($numbers/$numLen) + 1;
    }elseif($listName == 'Expect' && $biggestNum <50 && $biggestNum >=10 && $numbers < 100){
      $numbers = $ss++;
      if($numbers >= 100) $kk = round($numbers/$numLen) + 1;
    }elseif($listName == 'Expect2'){
      if($numbers < 20){
        $numbers = $ss++;
      }elseif($numbers >= 20 && $numbers < 100){
        $numbers += 5;
      }elseif($numbers >= 100){
        $numbers += 10;
      }    
    }elseif($frmName == 'AvgP' || $frmName == 'maxSAINT' || $frmName == 'SaintScore' || $frmName == 'MaxP'){ 
      if($numbers >= 0.45 && $numbers <= 0.85){
        $numbers += 0.05;
      }elseif($numbers > 0.85){
        $numbers += 0.01;
      }
    }elseif($frmName == 'BFDR'){
      if($numbers < 0.05){
        $numbers += 0.01;
      }elseif($numbers == 0.05){
        $numbers = 0.1;
      }elseif($numbers < 0.9){
        $numbers += 0.1;
      }else{
        break;
      }
    }else{
      $numbers = $numLen * $kk;
      $kk++;
    }
    echo "<option value=\"$sign$numbers\" ".(($$frmName==$sign.$numbers)?'selected':'').">$sign$numbers\r\n";
  } 
  echo '</select>';
} 

function get_bioGrid_icon(&$typeArr,&$typeStr,$s=''){
  $typeImg = '';
  $tdStart = '';
  $tdEnd = '';
  if($s == 's'){
   $s = "_s";
  }elseif($s == 'u'){
    $s = '';
    $typeImg .= "<td>";
  }else{
    $tdStart = "<td>";
    $tdEnd = "</td>";
  } 
  if(!$typeArr){
    $typeImg .= "$tdStart&nbsp;$tdEnd";
  }else{
    $typeStr = '';
    foreach($typeArr as $type){
      if($type == 'PH'){
        $typeImg .= "$tdStart&nbsp;<img src='./images/icon_pHTP$s.gif'>$tdEnd";
        if($typeStr) $typeStr .= ':';
        $typeStr .= $type;
      }
      if($type == 'PN'){
        $typeImg .= "$tdStart&nbsp;<img src='./images/icon_pNONHTP$s.gif'>$tdEnd";
        if($typeStr) $typeStr .= ':';
        $typeStr .= $type;
      }
      if($type == 'GH'){
        $typeImg .= "$tdStart&nbsp;<img src='./images/icon_gHTP$s.gif'>$tdEnd";
        if($typeStr) $typeStr .= ':';
        $typeStr .= $type;
      }
      if($type == 'GN'){
        $typeImg .= "$tdStart&nbsp;<img src='./images/icon_gNONHTP$s.gif'>$tdEnd";
        if($typeStr) $typeStr .= ':';
        $typeStr .= $type;
      }
    }
  }
  if($s == 'u'){
    $typeImg .= "</td>";;
  }  
  return $typeImg;
}
function get_bioGride_response($baitGeneIDstr, &$gride_reponse_arr){
  $formaction = BIOGRID_URL;
  //$gride_reponse_arr = @file($formaction."w?ids=$baitGeneIDstr");
  //return;
  
  @require_once "../common/HTTP/Request_Prohits.php";
  $req = new HTTP_Request($formaction, array('timeout' => 150,'readTimeout' => array(150,0)));
  $req->setMethod(HTTP_REQUEST_METHOD_POST);
  $req->addHeader('Content-Type', 'multipart/form-data');
  
  $req->addPostData('ids', $baitGeneIDstr);
   
  $response = $req->sendRequest();
  
  //echo $formaction."?ids=$baitGeneIDstr<br>";exit;
  if (!PEAR::isError($response)) { 
    $response1 = $req->getResponseBody();
    //echo "\$response1=$response1<br>";exit;
    $gride_reponse_arr = explode("\n", $response1);
  }else{
    echo "BIOGRID connection error";
  }
  return;
  
}


function get_project_id_name_arr(){
  global $PROHITSDB;
  $SQL = "SELECT `ID`,`Name` FROM `Projects` ORDER BY `ID`";
  $tmp_project_arr = $PROHITSDB->fetchAll($SQL);
  $project_arr = array();
  foreach($tmp_project_arr as $tmp_project_val){
   $project_arr[$tmp_project_val['ID']] = $tmp_project_val['Name'];
  }
  return $project_arr;
}
function get_project_id_DBname_arr(){
  global $PROHITSDB;
  $SQL = "SELECT `ID`,`DBname` FROM `Projects` ORDER BY `ID`";
  $tmp_project_arr = $PROHITSDB->fetchAll($SQL);
  $project_arr = array();
  foreach($tmp_project_arr as $tmp_project_val){
   $project_arr[$tmp_project_val['ID']] = $tmp_project_val['DBname'];
  }
  return $project_arr;
}
function get_project_id_TaxID_arr(){
  global $PROHITSDB;
  $SQL = "SELECT `ID`,`TaxID` FROM `Projects` ORDER BY `ID`";
  $tmp_project_arr = $PROHITSDB->fetchAll($SQL);
  $project_arr = array();
  foreach($tmp_project_arr as $tmp_project_val){
   $project_arr[$tmp_project_val['ID']] = $tmp_project_val['TaxID'];
  }
  return $project_arr;
}

function has_hits($ID, $Type='Bait'){
  $rt = false;
  if(!$ID) return $rt;
  global $HITSDB;
	if($Type == 'Bait'){
		$fieldName = "BaitID";
	}else{
		$fieldName = "BandID";
    if($Type == 'Experiment'){
      $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID`='$ID'";
      $tem_arr = $HITSDB->fetchAll($SQL);
      if($tem_arr){
        $ID = '';
        foreach($tem_arr as $val){
          if($ID) $ID .= ",";
          $ID .= $val['ID'];
        }
      }else{
        return $rt;
      }
    }
	}	
	$SQL = "select ID from Hits where ".$fieldName." IN(".$ID.")";
	$rt = $HITSDB->exist($SQL);
	if(!$rt){
		$SQL = "select ID from TppProtein where ".$fieldName." IN(".$ID.")";
		$rt = $HITSDB->exist($SQL);
    if(!$rt){
  		$SQL = "select ID from Hits_GeneLevel where ".$fieldName." IN(".$ID.")";
  		$rt = $HITSDB->exist($SQL);
  	}
	}
	return $rt;
}

function change_item_type_for_session($item_type_A,$item_type_B){
  global $HITSDB;
  $rtString = '';
  $SQL_matrix = array('Band_Bait'=>array('ID'=>'BaitID AS itemID',
                                         'table'=>'Band',
                                         'in_ids_index'=>'com_SampleIDs',
                                         'ID_in' => 'ID',
                                         'group_by'=>'GROUP BY BaitID',
                                         'out_ids_index'=>'com_BaitIDs'),
                      'Exp_Bait' =>array('ID'=>'BaitID AS itemID',
                                         'table'=>'Experiment',
                                         'ID_in' => 'ID',
                                         'in_ids_index'=>'com_ExperimentIDs',
                                         'group_by'=>'GROUP BY BaitID',
                                         'out_ids_index'=>'com_BaitIDs'),
                      'Band_Exp' =>array('ID'=>'ExpID AS itemID',
                                         'table'=>'Band',
                                         'ID_in' => 'ID',
                                         'in_ids_index'=>'com_SampleIDs',
                                         'group_by'=>'GROUP BY ExpID',
                                         'out_ids_index'=>'com_ExperimentIDs'),
                      'Bait_Exp' =>array('ID'=>'ID AS itemID',
                                         'table'=>'Experiment',
                                         'ID_in' => 'BaitID',
                                         'in_ids_index'=>'com_BaitIDs',
                                         'group_by'=>'',
                                         'out_ids_index'=>'com_ExperimentIDs'),
                      'Exp_Band' =>array('ID'=>'ID AS itemID',
                                         'table'=>'Band',
                                         'ID_in' => 'ExpID',
                                         'in_ids_index'=>'com_ExperimentIDs',
                                         'group_by'=>'',
                                         'out_ids_index'=>'com_SampleIDs'),
                      'Bait_Band' =>array('ID'=>'ID AS itemID',
                                         'table'=>'Band',
                                         'ID_in' => 'BaitID',
                                         'in_ids_index'=>'com_BaitIDs',
                                         'group_by'=>'',
                                         'out_ids_index'=>'com_SampleIDs'),                  
                                         );
  $matrix_index = $item_type_A."_".$item_type_B;
  
  $SQL_parts_arr = $SQL_matrix[$matrix_index];
  $SQL = "SELECT ".$SQL_parts_arr['ID']. 
                 " FROM ".$SQL_parts_arr['table'].
                 " WHERE ".$SQL_parts_arr['ID_in'].
                 " IN(".$_SESSION[$SQL_parts_arr['in_ids_index']].")".
                 $SQL_parts_arr['group_by'];
  $itemArr = $HITSDB->fetchAll($SQL);
  foreach($itemArr as $itemVal){
    if($rtString) $rtString .= ",";
    $rtString .= $itemVal['itemID'];
  }
  $_SESSION[$SQL_parts_arr['out_ids_index']] = $rtString;
  return $rtString;
}

function get_comparison_session($Type="Bait", $returnType='string'){
  global $HITSDB;
  $rtArr= array();
	$rtString = '';
	if($Type == "Bait"){
		if(!isset($_SESSION['com_BaitIDs']) or !$_SESSION['com_BaitIDs']){
		  if(isset($_SESSION['com_SampleIDs']) and $_SESSION['com_SampleIDs']){
        $rtString = change_item_type_for_session('Band','Bait');
      }elseif(isset($_SESSION['com_ExperimentIDs']) and $_SESSION['com_ExperimentIDs']){  
        $rtString = change_item_type_for_session('Exp','Bait');
      }
		}else{
			$rtString = $_SESSION['com_BaitIDs'];
		}
		$_SESSION['com_SampleIDs'] = '';
    $_SESSION['com_ExperimentIDs'] = '';
  }elseif($Type == "Exp"){ 
    if(!isset($_SESSION['com_ExperimentIDs']) or !$_SESSION['com_ExperimentIDs']){
		  if(isset($_SESSION['com_SampleIDs']) and $_SESSION['com_SampleIDs']){
        $rtString = change_item_type_for_session('Band','Exp');
      }elseif(isset($_SESSION['com_BaitIDs']) and $_SESSION['com_BaitIDs']){  
        $rtString = change_item_type_for_session('Bait','Exp');
      }
		}else{
			$rtString = $_SESSION['com_ExperimentIDs'];
		}
		$_SESSION['com_SampleIDs'] = '';
    $_SESSION['com_BaitIDs'] = '';
	}elseif($Type == "Sample" || $Type == "Band"){ 
		if(!isset($_SESSION['com_SampleIDs']) or !$_SESSION['com_SampleIDs']){
		  if(isset($_SESSION['com_BaitIDs']) and $_SESSION['com_BaitIDs']){
				$rtString = change_item_type_for_session('Bait','Band');
			}elseif(isset($_SESSION['com_ExperimentIDs']) and $_SESSION['com_ExperimentIDs']){
        $rtString = change_item_type_for_session('Exp','Band');
      }
		}else{
			$rtString = $_SESSION['com_SampleIDs'];
		}
		$_SESSION['com_BaitIDs'] = '';
    $_SESSION['com_ExperimentIDs'] = '';
	}
	if($returnType=='string'){
		return $rtString;
	}else{
	  if($rtString){
			$rtArr = explode(",", $rtString);
		}  
		return $rtArr;
	}
}

function has_itemIDstr_in_session(){
  if((isset($_SESSION['com_BaitIDs']) && $_SESSION['com_BaitIDs']) || (isset($_SESSION['com_ExperimentIDs']) && $_SESSION['com_ExperimentIDs']) || (isset($_SESSION['com_SampleIDs']) && $_SESSION['com_SampleIDs'])){
    return true;
  }else{
    return false;
  }
}

function get_comparison_session_($Type="Bait", $returnType='string'){
  global $HITSDB;
  $rtArr= array();
	$rtString = '';
	if($Type == "Bait"){
		if(!isset($_SESSION['com_BaitIDs']) or !$_SESSION['com_BaitIDs']){
		  if(isset($_SESSION['com_SampleIDs']) and $_SESSION['com_SampleIDs']){
				$SQL = "SELECT `BaitID` FROM `Band` WHERE `ID` IN(".$_SESSION['com_SampleIDs'].") GROUP BY BaitID";
		    $BandArr = $HITSDB->fetchAll($SQL);
		    foreach($BandArr as $BandVal){
				  if($rtString) $rtString .= ",";
		      $rtString .= $BandVal['BaitID'];
		    }
			}
			$_SESSION['com_BaitIDs'] = $rtString;
		}else{
			$rtString = $_SESSION['com_BaitIDs'];
		}
		$_SESSION['com_SampleIDs'] = '';
	}else{
		if(!isset($_SESSION['com_SampleIDs']) or !$_SESSION['com_SampleIDs']){
		  if(isset($_SESSION['com_BaitIDs']) and $_SESSION['com_BaitIDs']){
				$SQL = "SELECT `ID` FROM `Band` WHERE `BaitID` IN(".$_SESSION['com_BaitIDs'].")";
		    $BandArr = $HITSDB->fetchAll($SQL);
		    $tmpSampleArr_2 = array();
		    foreach($BandArr as $BandVal){
				  if($rtString) $rtString .= ",";
				  $rtString .= $BandVal['ID'];
		    }
			}
			$_SESSION['com_SampleIDs'] = $rtString;
		}else{
			$rtString = $_SESSION['com_SampleIDs'];
		}
		$_SESSION['com_BaitIDs'] = '';
	}
	if($returnType=='string'){
		return $rtString;
	}else{
	  if($rtString){
			$rtArr = explode(",", $rtString);
		}
		return $rtArr;
	}
}

function get_comparison_session_Type(){
 	if(isset($_SESSION['com_SampleIDs']) and $_SESSION['com_SampleIDs']){
	  return "Sample";
  }elseif(isset($_SESSION['com_ExperimentIDs']) and $_SESSION['com_ExperimentIDs']){
	  return "Exp"; 
  }else{
    return "Bait";
  }
}

function get_comparison_session_Type_(){
 	if(isset($_SESSION['com_SampleIDs']) and $_SESSION['com_SampleIDs']){
	  return "Sample";
  }else{
    return "Bait";
  }
}

function edit_comparison_session($IDs='', $Type="Bait", $Add='add'){
  if(!$IDs) return;
  $tmpStr = get_comparison_session($Type);
	if($Add =='add'){
	  if($tmpStr) $tmpStr .= ",";
		if($Type=="Bait"){
			$_SESSION['com_BaitIDs'] = $tmpStr . $IDs;
    }elseif($Type=="Exp"){
			$_SESSION['com_ExperimentIDs'] = $tmpStr . $IDs;
		}else{
			$_SESSION['com_SampleIDs'] = $tmpStr . $IDs;
		}
  }elseif($Add =='new'){
    if($Type=="Bait"){
			$_SESSION['com_BaitIDs'] = $IDs;
    }elseif($Type=="Exp"){
			$_SESSION['com_ExperimentIDs'] = $IDs;
		}else{
			$_SESSION['com_SampleIDs'] = $IDs;
		}
	}else{
    if($tmpStr){
    	$tmpSampleArr_1 = explode(",", $tmpStr);
    }else{
      $tmpSampleArr_1 = array();
    } 
    $tmpSampleArr_2 = explode(",",$IDs);
    $tmpSampleArr_3 = array_diff($tmpSampleArr_1, $tmpSampleArr_2);
    $theSt = implode(",", $tmpSampleArr_3);
    if($Type=="Bait"){
			$_SESSION['com_BaitIDs'] = $theSt;
    }elseif($Type=="Exp"){
			$_SESSION['com_ExperimentIDs'] = $theSt;
		}else{
			$_SESSION['com_SampleIDs'] = $theSt;
		}
	}
}

function get_expDetail_id_name_arr(){
  global $PROHITSDB;
  $ExpDetail_id_name_arr = array();
  $SQL = "SELECT `ID`,`Name` FROM `ExpDetailName`";
  $tmpExpDetail_arr = $PROHITSDB->fetchAll($SQL);
  foreach($tmpExpDetail_arr as $tmpExpDetail_val){
    $ExpDetail_id_name_arr[$tmpExpDetail_val['ID']] = $tmpExpDetail_val['Name'];
  }
  return $ExpDetail_id_name_arr;
}
function get_exist_note_hitsID_arr(&$level_2_arr,&$note_exist_arr){
  global $HITSDB;
  $sample_ids_str = '';
  foreach($level_2_arr as $sample_val){
    if($sample_ids_str) $sample_ids_str .= ",";
    $sample_ids_str .= $sample_val['ID'];
  }
  
  if($sample_ids_str){
    $SQL = "SELECT `ID` FROM `Hits` WHERE `BandID` IN ($sample_ids_str)";
    $tmp_hits_id_arr = $HITSDB->fetchAll($SQL);
    $hits_id_str = '';
    foreach($tmp_hits_id_arr as $tmp_hits_val){
      if($hits_id_str) $hits_id_str .= ",";
      $hits_id_str .= $tmp_hits_val['ID'];
    }
    if($hits_id_str){
      $SQL = "SELECT `HitID` FROM `HitNote` WHERE `HitID` IN ($hits_id_str) GROUP BY `HitID`";
      $tmp_hits_id_arr = $HITSDB->fetchAll($SQL);
      foreach($tmp_hits_id_arr as $tmp_hits_val){
        array_push($note_exist_arr, $tmp_hits_val['HitID']);
      }
      $SQL = "SELECT `HitID` FROM `HitDiscussion` WHERE `HitID` IN ($hits_id_str) GROUP BY `HitID`";
      $tmp_hits_id_arr = $HITSDB->fetchAll($SQL);
      foreach($tmp_hits_id_arr as $tmp_hits_val){
        if(in_array($tmp_hits_val['HitID'], $note_exist_arr)) continue;
        array_push($note_exist_arr, $tmp_hits_val['HitID']);
      }
    }
  }
} 

function plate_info($Plate_ID,$bgcolor='',$bgcolordark='',$script=''){
  global $HITSDB;
  if(!$bgcolor) $bgcolor = '#e3e3e3';
  if(!$bgcolordark) $bgcolordark = 'white';
  
  $SQL = "SELECT `ID`,`Name`,`ComplitDate`,`MSDate`,`DigestedBy`,`Buffer` FROM `Plate` WHERE ID='$Plate_ID'";
    $Plate = $HITSDB->fetch($SQL);
    $tableheader = 'tableheader';
    if($script='item_report'){
      $tableheader = 'tableheader_black';
      $cellspacing = "2";
    }else{
      $tableheader = 'tableheader';
      $cellspacing = "1";
    } 
    $PlateOwner = get_userName($HITSDB, $Plate['DigestedBy']);
?> 
  <table border="0" cellpadding="<?php echo $cellspacing?>" cellspacing="1" width="100%">  
  	<tr bgcolor="">
  	  <td align="left" colspan="4" bgcolor="<?php echo $bgcolordark;?>">
  		<span class=<?php echo $tableheader?>>&nbsp;<b>Plate Information)</b></span></td>
    </tr>
    <tr>
     <td bgcolor="<?php echo $bgcolor;?>" width="15%" nowrap><span class=maintext><b>Plate ID</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" width="35%"><span class=maintext><?php echo $Plate['ID']?></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" width="15%" nowrap><span class=maintext><b>Plate Name</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Plate['Name']?></span></td>  
    </tr>
    <tr>
     <td bgcolor="<?php echo $bgcolor;?>" width="15%" nowrap><span class=maintext><b>MS Complit Date:</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Plate['MSDate']?></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>Digested By</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $PlateOwner?></span></td>
    </tr>
    <tr>  
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>Resusp. Buffer</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Plate['Buffer']?></span></td>  
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>&nbsp;&nbsp;</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext>&nbsp;&nbsp;</span></td> 
    </tr>
  </table>
<?php 
}

function gel_info($Gel_ID,$bgcolor='',$bgcolordark='',$script=''){
  global $HITSDB;
  if(!$bgcolor) $bgcolor = '#e3e3e3';
  if(!$bgcolordark) $bgcolordark = 'white';
  
  $SQL = "SELECT `ID`,`Name`,`Image`,`OwnerID`,`GelType`,`DateTime` FROM `Gel` WHERE ID='$Gel_ID'";
    $Gel = $HITSDB->fetch($SQL);
    $tableheader = 'tableheader';
    if($script='item_report'){
      $tableheader = 'tableheader_black';
      $cellspacing = "2";
    }else{
      $tableheader = 'tableheader';
      $cellspacing = "1";
    } 
    $GelOwner = get_userName($HITSDB, $Gel['OwnerID']);
?> 
  <table border="0" cellpadding="<?php echo $cellspacing?>" cellspacing="1" width="100%">  
  	<tr bgcolor="">
  	  <td align="left" colspan="4" bgcolor="<?php echo $bgcolordark;?>">
  		<span class=<?php echo $tableheader?>>&nbsp;<b>Gel Information)</b></span></td>
    </tr>
    <tr>
     <td bgcolor="<?php echo $bgcolor;?>" width="15%" nowrap><span class=maintext><b>Gel ID</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" width="35%"><span class=maintext><?php echo $Gel['ID']?></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" width="15%" nowrap><span class=maintext><b>Gel Name</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Gel['Name']?></span></td>  
    </tr>
    <tr>
     <td bgcolor="<?php echo $bgcolor;?>" width="15%" nowrap><span class=maintext><b>Gel Type</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Gel['GelType']?></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>Uploaded by</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $GelOwner?></span></td>
    </tr>
    <tr>  
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>Created On</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Gel['DateTime']?></span></td>  
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>&nbsp;&nbsp;</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext>&nbsp;&nbsp;</span></td> 
    </tr>
  </table>
<?php 
}

function bait_info($Bait_ID,$bgcolor='',$bgcolordark='',$script=''){
  global $HITSDB;
  if(!$bgcolor) $bgcolor = '#e3e3e3';
  if(!$bgcolordark) $bgcolordark = 'white';
  
  $SQL = "SELECT 
          ID, 
          GeneID,
          LocusTag,
          GeneName,
          BaitAcc,
          AccType, 
          TaxID, 
          BaitMW, 
          Family, 
          Tag,
          Mutation, 
          Clone, 
          Vector, 
          Description, 
          OwnerID, 
          ProjectID,
          GelFree, 
          DateTime
          FROM Bait where ID='$Bait_ID'";
    $Bait = $HITSDB->fetch($SQL);
    $tableheader = 'tableheader';
    if($script='item_report'){
      $tableheader = 'tableheader_black';
      $cellspacing = "2";
    }else{
      $tableheader = 'tableheader';
      $cellspacing = "1";
    }
    $out_lind = get_URL_str($Bait['BaitAcc'], $Bait['GeneID'], $Bait['LocusTag'],'','bait_info');
    $Tag_Mutation = '';
    if($Bait['Tag'] && $Bait['Mutation']){
      $Tag_Mutation = '('.$Bait['Tag'].','.$Bait['Mutation'].')';
    }elseif($Bait['Tag']){
      $Tag_Mutation = '('.$Bait['Tag'].')';
    }elseif($Bait['Mutation']){
      $Tag_Mutation = '('.$Bait['Mutation'].')';
    }  
?> 
  <table border="0" cellpadding="<?php echo $cellspacing?>" cellspacing="1" width="100%">  
  	<tr bgcolor="">
  	  <td align="left" colspan="4" bgcolor="<?php echo $bgcolordark;?>">
  		<span class=<?php echo $tableheader?>>&nbsp;<b>Bait Information (<?php echo  $Bait['ID'];?>)</b>&nbsp;&nbsp; 
  	</span><span class=maintext><?php echo $out_lind?></span></td>
    </tr>
    <tr>
     <td bgcolor="<?php echo $bgcolor;?>" width="15%" nowrap><span class=maintext><b>Bait Gene ID</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" width="20%"><span class=maintext><?php echo $Bait['GeneID']?></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>Bait Gene Name</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Bait['GeneName']?> <?php echo $Tag_Mutation?></span></td>  
    </tr>
    <tr>
     <td bgcolor="<?php echo $bgcolor;?>" width="15%" nowrap><span class=maintext><b>Bait Locus Tag</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Bait['LocusTag']?></span></td>
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>Bait MW (kDa)</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Bait['BaitMW']?></span></td>
    </tr>
    <tr>  
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>Bait Clone</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Bait['Clone']?></span></td>  
     <td bgcolor="<?php echo $bgcolor;?>" nowrap><span class=maintext><b>Bait Description</b></span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Bait['Description']?></span></td> 
    </tr>
  </table>
<?php 
}
function band_info($Band_ID,$type,$bgcolor='',$bgcolordark='',$script=''){
  global $HITSDB,$AccessProjectID;
  if(!$bgcolor) $bgcolor = '#e3e3e3';
  if(!$bgcolordark) $bgcolordark = 'white';
    
  $SQL = "SELECT B.ExpID,B.BaitID, BA.GelFree FROM Band B LEFT JOIN Bait BA ON(BA.ID=B.BaitID) WHERE B.ID='$Band_ID'";
  
  if($tmp_arr = $HITSDB->fetch($SQL)){
    $Bait_ID = $tmp_arr['BaitID'];
    $Exp_ID = $tmp_arr['ExpID'];
    $gel_free = $tmp_arr['GelFree'];
  }else{
    echo "error1";
    exit;
  }
  
  $SQL = "SELECT 
          ID,
          ExpID, 
          LaneID, 
          BaitID, 
          BaitMW, 
          BandMW, 
          Intensity, 
          Location, 
          Modification,
          BandPI, 
          Analysis, 
          ResultsFile, 
          OwnerID, 
          ProjectID, 
          DateTime,
          InPlate 
          FROM Band ";
  if($type == 'Band' && $Band_ID){
    $Where = " WHERE ID='$Band_ID'";
  }elseif($type == 'Exp' && $Exp_ID){
    $Where = " WHERE ExpID='$Exp_ID'";
  }elseif($type == 'Bait' && $Bait_ID){
    $Where = " WHERE BaitID='$Bait_ID'";
  }        
  $Where .= " and ProjectID=$AccessProjectID";
  $Where .= " ORDER BY ExpID DESC, ID DESC";
  $SQL .= $Where;
  if(!$Band_arr = $HITSDB->fetchAll($SQL)){
    echo "error";
    exit;
  }
  
  $tableheader = 'tableheader';
  if($script='item_report'){
    $tableheader = 'tableheader_black';
    $cellspacing = "2";
  }else{
    $tableheader = 'tableheader';
    $cellspacing = "1";
  }
  
?>
<table border="0" cellpadding="<?php echo $cellspacing?>" cellspacing="1" width="100%">  
	<tr bgcolor="">
	  <td align="left" colspan="6" bgcolor="<?php echo $bgcolordark;?>">
		<span class=<?php echo $tableheader?>><b>&nbsp;Sample Information</b> 
	  </span>
    </td>
  </tr>
  <tr>
   <td bgcolor="<?php echo $bgcolor;?>" width="10%" nospan><span class=maintext><b>Sample ID</b></span></td> 
   <td bgcolor="<?php echo $bgcolor;?>" width="20%" ><span class=maintext><b>Sample Code</b></span></td>
   <td bgcolor="<?php echo $bgcolor;?>" width="15%" ><span class=maintext><b>Submitted by</b></span></td>
<?php if(!$gel_free){?>
   <td bgcolor="<?php echo $bgcolor;?>" width="20%" ><span class=maintext><b>Sample Observed MW(kDa)</b></span></td> 
   <td bgcolor="<?php echo $bgcolor;?>" width="15%" ><span class=maintext><b>Sample in Gel</b></span></td>
   <td bgcolor="<?php echo $bgcolor;?>" width="20%" nospan><span class=maintext><b>Lane Num/ Lane Code</b></span></td>
<?php }?>   
  </tr> 
  
  
<?php foreach($Band_arr as $Band_val){
    $BandOwner = get_userName($HITSDB, $Band_val['OwnerID']);
?>
  <tr>
    <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Band_val['ID'];?></span></td>
    <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Band_val['Location'];?></span></td>
    <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $BandOwner;?></span></td>
<?php   if(!$gel_free){
      $SQL = "SELECT L.LaneNum,
                     L.LaneCode,
                     G.Name 
                     FROM Lane L 
                     LEFT JOIN Gel G ON L.GelID=G.ID
                     WHERE L.ID='".$Band_val['LaneID']."'";
      $lane_arr = $HITSDB->fetch($SQL);
      $lane_name = '';
      $lane_numb_code = '';
      if($lane_arr){
        $lane_name = $lane_arr['Name'];
        $lane_numb_code = $lane_arr['LaneNum']." / ".$lane_arr['LaneCode'];
      }
?>
    <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $Band_val['BandMW'];?></span></td>
    <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $lane_name;?></span></td>
    <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext><?php echo $lane_numb_code?></span></td>
<?php   }
  }
?>   
  </tr>
</table>
<?php 
}
function Exp_info($Exp_ID,$type,$bgcolor='',$bgcolordark='',$script=''){
  global $HITSDB,$AccessProjectID;
  if(!$bgcolor) $bgcolor = '#e3e3e3';
  if(!$bgcolordark) $bgcolordark = 'white';
    
  $ExpDetail_id_name_arr = get_expDetail_id_name_arr();
  $SQL = "SELECT BaitID FROM `Experiment` WHERE `ID`='$Exp_ID'";
  if(!$tmp_arr = $HITSDB->fetch($SQL)){
    echo "error1";
    exit;
  }  
  $Bait_ID = $tmp_arr['BaitID'];
  $SQL = "SELECT 
          ID, 
          BaitID, 
          Name, 
          TaxID, 
          OwnerID, 
          ProjectID,
          GrowProtocol,
          IpProtocol,
          DigestProtocol,
          PreySource,
          Notes,
          WesternGel, 
          DateTime
          FROM Experiment ";
  if($type == 'Experiment' && $Exp_ID){
    $Where = " WHERE ID='$Exp_ID'";
  }elseif($type == 'Bait' && $Bait_ID){
    $Where = " WHERE BaitID='$Bait_ID'";
  }        
  $Where .= " and ProjectID=$AccessProjectID";
  $Where .= " ORDER BY ID";
  $SQL .= $Where;
  if(!$exp_arr = $HITSDB->fetchAll($SQL)){
    echo "error";
    exit;
  }
  $tableheader = 'tableheader';
  if($script='item_report'){
    $tableheader = 'tableheader_black';
    $cellspacing = "2";
  }else{
    $tableheader = 'tableheader';
    $cellspacing = "1";
  }  
?>
<table border="0" cellpadding="<?php echo $cellspacing?>" cellspacing="1" width="100%">  
	<tr bgcolor="">
	  <td align="left" colspan="6" bgcolor="<?php echo $bgcolordark;?>">
		<span class='<?php echo $tableheader?>'><b>&nbsp;Experiment Information</b> 
	  </span>
    </td>
  </tr>
  <tr>
   <td bgcolor="<?php echo $bgcolor;?>" width="10%" nowrap><span class=maintext><b>Experiment ID</b></span></td> 
   <td bgcolor="<?php echo $bgcolor;?>" width="15%"><span class=maintext><b>Name/Batch Name</b></span></td>
   <td bgcolor="<?php echo $bgcolor;?>" width="15%"><span class=maintext><b>Exp. Detail</b></span></td>
   <td bgcolor="<?php echo $bgcolor;?>" ><span class=maintext><b>Exp. Status:</b></span></td>
   <td bgcolor="<?php echo $bgcolor;?>" width="15%"><span class=maintext><b>Inputed by</b></span></td>
   <td bgcolor="<?php echo $bgcolor;?>" width="15%"><span class=maintext><b>Date</b></span></td>
  </tr>
<?php    
  for($i=0; $i<count($exp_arr); $i++){
    $theUser = get_userName($HITSDB, $exp_arr[$i]['OwnerID']);
    $SQL = "SELECT 
            `SelectionID`,
            `OptionID` 
            FROM `ExpDetail` 
            WHERE `ExpID`='".$exp_arr[$i]['ID']."'
            ORDER BY `IndexNum`";
    $ExpCDT = $HITSDB->fetchAll($SQL);
    $exConditions = '';
    foreach($ExpCDT as $ExpCDT_val){
      if($exConditions){
        $exConditions .= ";<BR>".$ExpDetail_id_name_arr[$ExpCDT_val['SelectionID']]." : ".$ExpDetail_id_name_arr[$ExpCDT_val['OptionID']];
      }else{
        $exConditions .= $ExpDetail_id_name_arr[$ExpCDT_val['SelectionID']]." : ".$ExpDetail_id_name_arr[$ExpCDT_val['OptionID']];
      } 
    }
?>
  <tr>
    <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $exp_arr[$i]['ID'];?></span></td>
    <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $exp_arr[$i]['Name'];?></span></td>
    <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $exConditions;?></span></td>
    <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext>
    <DIV id="R_<?php echo $exp_arr[$i]['ID']?>"><?php get_status($exp_arr[$i]['ID'],'Experiment')?></DIV>
    </td>
    <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $theUser;?></span></td>
    <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $exp_arr[$i]['DateTime'];?></span></td>

  </tr>
<?php }?>  
</table>
<?php 
}

function item_has_hits($IDstr,$hitType,$searchEngineField){
  global $HITSDB;
  if(!$IDstr){
    echo "error";
    return 0;
  }
  $H_T_TP = '';
  $HitsTable = '';
  $DBtables = '';
  if($hitType == 'TPP'){
    $SQL = "SELECT 
            T.ID";
    $S_R_init = 'T';       
    $H_T_TP = 'T';
    $HitsTable = 'TppProtein';
    $DBtables = $HitsTable." ".$H_T_TP;
  }elseif($hitType == 'TPPpep'){
    $SQL = "SELECT
            TP.ID";
    $S_R_init = 'T';        
    $H_T_TP = 'TP';
    $HitsTable = 'TppPeptide';
    $DBtables = $HitsTable." ".$H_T_TP." LEFT JOIN (TppPeptideGroup P) ON (TP.GroupID=P.ID) LEFT JOIN (TppProtein T) ON (P.ProteinID=T.ID)";
  }elseif($hitType == 'geneLevel'){
    $SQL = "SELECT 
            H.ID";
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits_GeneLevel';
    $DBtables = $HitsTable." ".$H_T_TP;
  }else{
    $SQL = "SELECT 
            H.ID";
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits';
    $DBtables = $HitsTable." ".$H_T_TP;
  }  
  $searchEngineWhere = searchEngineWhere($searchEngineField,$S_R_init);
  $SQL .= " FROM ". $DBtables ."  WHERE ".$H_T_TP.".BandID IN ($IDstr) ".$searchEngineWhere . " LIMIT 1";
  $tempArr = $HITSDB->fetch($SQL);
  if(count($tempArr)){
    return 1;
  }else{
    return 0;
  }  
}

function is_SearchEngine_uploaded($IDstr,$hitType){
  global $HITSDB;
  if(!$IDstr){
    echo "error";
    return 0;
  }
  $ret_status = 0;
  $H_T_TP = '';
  $HitsTable = '';
  $DBtables = '';
  if($hitType == 'TPP'){
    $SQL = "SELECT 
            T.SearchEngine";
    $S_R_init = 'T';       
    $H_T_TP = 'T';
    $HitsTable = 'TppProtein';
    $DBtables = $HitsTable." ".$H_T_TP;
  }elseif($hitType == 'TPPpep'){
    $SQL = "SELECT
            T.SearchEngine";
    $S_R_init = 'T';        
    $H_T_TP = 'TP';
    $HitsTable = 'TppPeptide';
    $DBtables = $HitsTable." ".$H_T_TP." LEFT JOIN (TppPeptideGroup P) ON (TP.GroupID=P.ID) LEFT JOIN (TppProtein T) ON (P.ProteinID=T.ID)";
  }elseif($hitType == 'geneLevel'){
    $SQL = "SELECT 
            H.SearchEngine";
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits_GeneLevel';
    $DBtables = $HitsTable." ".$H_T_TP;
  
  }else{
    $SQL = "SELECT 
            H.SearchEngine";
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits';
    $DBtables = $HitsTable." ".$H_T_TP;
  }
  $SQL .= " FROM ". $DBtables ."  WHERE ".$H_T_TP.".BandID IN ($IDstr)  GROUP BY $S_R_init.SearchEngine ORDER BY $S_R_init.SearchEngine DESC";

  $tempArr = $HITSDB->fetchALL($SQL);
  $SearchEngine_arr = array();
  foreach($tempArr as $value){
    if(!$value) continue;
    if(strstr($value['SearchEngine'], 'Uploaded')) $ret_status = 1;
    break;
  }
  return $ret_status;
}

function get_default_searchEngine($IDstr,$hitType){
  global $HITSDB;
  if(!$IDstr){
    echo "error";
    return 0;
  }
  $H_T_TP = '';
  $HitsTable = '';
  $DBtables = '';
  if($hitType == 'TPP'){
    $SQL = "SELECT 
            T.SearchEngine";
    $S_R_init = 'T';       
    $H_T_TP = 'T';
    $HitsTable = 'TppProtein';
    $DBtables = $HitsTable." ".$H_T_TP;
  }elseif($hitType == 'TPPpep'){
    $SQL = "SELECT
            T.SearchEngine";
    $S_R_init = 'T';        
    $H_T_TP = 'TP';
    $HitsTable = 'TppPeptide';
    $DBtables = $HitsTable." ".$H_T_TP." LEFT JOIN (TppPeptideGroup P) ON (TP.GroupID=P.ID) LEFT JOIN (TppProtein T) ON (P.ProteinID=T.ID)";
  }elseif($hitType == 'geneLevel'){
    $SQL = "SELECT 
            H.SearchEngine";
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits_GeneLevel';
    $DBtables = $HitsTable." ".$H_T_TP;
  }else{
    $SQL = "SELECT 
            H.SearchEngine";
    $S_R_init = 'H';        
    $H_T_TP = 'H';
    $HitsTable = 'Hits';
    $DBtables = $HitsTable." ".$H_T_TP;
  }
  $SQL .= " FROM ". $DBtables ."  WHERE ".$H_T_TP.".BandID IN ($IDstr)  GROUP BY $S_R_init.SearchEngine ORDER BY $S_R_init.SearchEngine DESC";
//echo "\$SQL=$SQL<br>";
  $tempArr = $HITSDB->fetchALL($SQL);
  $SearchEngine_arr = array();
  foreach($tempArr as $value){
    if(!$value) continue;
    array_push($SearchEngine_arr, $value['SearchEngine']);
  } 
  sort($SearchEngine_arr);
  if(!$SearchEngine_arr) return "Mascot";
  if($hitType == 'TPP'){
    return $SearchEngine_arr[0];
  }else{
    return str_replace("Uploaded", "", $SearchEngine_arr[0]);
  }
}

function color_keys_for_experiment(){
  global $GelFreeColor,$unGelFreeColor,$GrowColor,$IpColor,$DigestColor,$LC_MSColor,$RawFileColor,$HasHitsColor;
  global $HasHitsColor,$NoBaitFoundColor,$emptySign,$group_lable_descipt_bgcolor,$toggle_color_status;
?>      
  <div class=middle_bold style="clear:both;width:100%;height:25px;">
    Experiment status color keys&nbsp;<a id='color_key_a' href="javascript:  toggle_group_description('color_key','toggle_color_status')" class=Button><?php echo ($toggle_color_status)?'[+]':'[-]'?></a>
  </div>
  <input type=hidden id=toggle_color_status name=toggle_color_status  value='<?php echo $toggle_color_status?>'>
  <DIV id='color_key' STYLE="display:<?php echo ($toggle_color_status)?'none':'block'?>;width:100%;border: black solid 0px">
  <div class=small>
    <font size=4 face="Courier" style='background-color:<?php echo $GelFreeColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;Gel free experiment &nbsp;&nbsp;&nbsp;&nbsp;
  </div>
  <div class=small>  
    <font size=4 face="Courier" style='background-color:<?php echo $unGelFreeColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;Gel based experiment
  </div>
  <div class=small>
    <font size=4 face="Courier" style='background-color:<?php echo $GrowColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;Biological Material
  </div>
  <div class=small>
    <font size=4 face="Courier" style='background-color:<?php echo $IpColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;Affinity Purification
  </div>
  <div class=small>
    <font size=4 face="Courier" style='background-color:<?php echo $DigestColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;Peptide Preparation
  </div>
  <div class=small>
    <font size=4 face="Courier" style='background-color:<?php echo $LC_MSColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;LC-MS
  </div>
  <div class=small>
    <font size=4 face="Courier" style='background-color:<?php echo $RawFileColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;Raw file created
  </div>
  <div class=small>
    <font size=4 face="Courier" style='background-color:<?php echo $HasHitsColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;Hits parsed &nbsp;&nbsp;&nbsp;&nbsp;
    <font size=4 face="Courier" style='background-color:<?php echo  $HasHitsColor?>'><font color=white><?php echo $emptySign?></font></font>&nbsp;&nbsp;&nbsp;No hits  
  </div>
  <div class=small>
    <font size=4 face="Courier" style='background-color:<?php echo $NoBaitFoundColor?>'>&nbsp;</font>&nbsp;&nbsp;&nbsp;No bait found
  </div>
  </DIV>
<?php 
}

function print_group_bar(){
  global $SCRIPT_NAME,$toggle_group_status;
  if($SCRIPT_NAME == "bait.php"){
    $group_type_arr = array('Bait');
  }elseif($SCRIPT_NAME == "experiment_show.php"){
    $group_type_arr = array('Bait','Experiment');
  }elseif($SCRIPT_NAME == "band_show.php"){
    $group_type_arr = array('Bait','Experiment','Band','Export');
  }
  $d_id = "group_div";
  $d_id_a = "group_div_a";
  $num_group = count($group_type_arr);
?>
  <div style="width:100%;white-space:nowrap;text-align:left;border: black solid 0px">
    <div style="width:100%;white-space:nowrap;text-align:left;border: black solid 0px;clear:both;width:100%;height:25px;">
      <div class=middle_bold>
      Group and exported versions<a id='group_div_a' href="javascript: toggle_group_description('group_div','toggle_group_status')" class=Button><?php echo ($toggle_group_status)?'&nbsp;[+]':'&nbsp;[-]'?></a>
      </div>
    </div>
    <input type=hidden id=toggle_group_status name=toggle_group_status  value='<?php echo $toggle_group_status?>'>
    <DIV id='group_div' STYLE="display: <?php echo ($toggle_group_status)?'none':'block'?>; width:100%;border: black solid 0px">
    <table border="0" width=100% cellpadding="1" cellspacing="1">
	  <tr>
	    <td width="100%" align="left" colspan="<?php echo $num_group?>"> 
        Description:
        <input type="button" value=" Go " onClick="javascript: change_groups(this.form);">
      </td>
    </tr>
    <tr>
      <td>
<?php    
  if($num_group){
    $td_width = round(100/$num_group);
?>
      <table border="0" width=100% cellpadding="1" cellspacing="1">
      <tr>
<?php   foreach($group_type_arr as $val){?>
      <td valign="top" width="<?php echo floor(100/$num_group)?>%">
<?php     group_icon($val);?>
      </td>
<?php   }?>
      </tr>
      </table>
<?php 
  }else{
    echo "&nbsp;";
  }
?> 
      </td>
    </tr>
    </table>   
    </DIV>
  </div>
<?php 
}

function group_icon($group_type){
  global $AccessProjectID,$HITSDB,$AUTH,$USER,$group_lable_descipt_bgcolor,$frm_group_id;
  global $group_id_arr;
  
  $type_color_pair = protocol_type_color_pair();    
  $order_by = "Name";
  if($group_type == 'Export'){
    $type_tital = 'Exported versions';
    $order_by = 'Initial';
  }elseif($group_type == 'Band'){
    $type_tital = 'Sample groups';
  }else{
    $type_tital = $group_type . ' groups';
  }
  $SQL = "SELECT `ID`, `Name`, `Type`, `Description` , `Icon` , `UserID` , `Initial` 
          FROM `NoteType` 
          WHERE `Type` = '$group_type' 
          and `ProjectID`='$AccessProjectID'
          order by $order_by";
  $GroupArr = $HITSDB->fetchAll($SQL);
  $ProtocolArr = array();
  if($group_type == 'Band'){
    $SQL = "SELECT ID, 
                   Name, 
                   Type,
                   Detail AS Description, 
                   UserID 
            FROM Protocol  
            WHERE Type COLLATE latin1_general_cs LIKE 'SAM_%' 
            AND ProjectID='$AccessProjectID'
            AND Name IS NOT NULL
            ORDER BY ID";
    $ProtocolArr = $HITSDB->fetchAll($SQL);
  }
 ?>
  <div style="float:left;white-space:nowrap;border: blue solid 0px;">
  <table border='0' width=100% cellpadding="0" cellspacing="1">
  <tr><td>
    <div>
      <div class=middle_bold>
      <?php echo $type_tital?>&nbsp;
      </div>
    </div><br>
    <div>
  </td></tr>  
 <?php     
  if($GroupArr){
    $empty_spaces = 0;
    $i = 0;
    foreach($GroupArr as $rd){
      $selected_prot_div_id = $AccessProjectID."_".$rd['ID'];
      $file_name = "./group_detail_pop.php?modal=this_project&outsite_script=1&selected_type_div_id=$group_type&selected_prot_div_id=$selected_prot_div_id";
?>
    <tr>
      <td height="25" nowrap>
<?php    
      echo "<div style=\"white-space:nowrap;\">";   
      if($group_type == 'Export'){
        $group_id = "Band_z_".$rd['ID'];
        $locked_label = '';
       if($rd['Icon'] == 'locked'){
        $locked_label = " &nbsp; <font color='#FF0000'>(locked)</font>";
       }
    ?>
        <span style="float:left;border:red solid 0px;">
          <input type=checkbox name='frm_group_id' value='<?php echo $group_id?>' <?php echo (in_array($group_id, $group_id_arr))?'checked':''?>>&nbsp;
        </span>
        <span class=tdback_star_image style="float:left;border:blue solid 0px;padding:0px 0px 0px 0px;height:17px;width:18px">  
          <a href="javascript: popwin('<?php echo $file_name?>','600','500');"><?php echo $rd['Initial'];?></a>
        </span>
        <span style="float:left;border:blue solid 0px;">
          <div class=small><?php echo "(VS".$rd['Initial'].") ".$rd['Name'].$locked_label;?></div>
        </span>
    <?php }else{
        $group_id = $rd['Type']."_".$rd['ID'];
    ?>           
        <span style="float:left;border:blue solid 0px;">
          <div class=small>
          <input type=checkbox name='frm_group_id' value='<?php echo $group_id?>' <?php echo (in_array($group_id, $group_id_arr))?'checked':''?>>&nbsp;
          <a href="javascript: popwin('<?php echo $file_name?>','600','500');"><img src='./gel_images/<?php echo $rd['Icon'];?>' border=0></a>&nbsp;&nbsp;&nbsp;<?php echo "(".$rd['Initial'].") ".$rd['Name'];?>
          </div>
        </span>
    <?php   
      }
      echo "</div><br>\n";
?>

      </td>
    </tr>
<?php           
    }
  }
?>
  </table>
  </div>
<?php 
  if($ProtocolArr){
  ?>
  <div style="float:left;white-space:nowrap;border: blue solid 0px;">
  <table border='0' width=100% cellpadding="1" cellspacing="1">
    <tr>
    <td>
    <div style="width:100%;height:40px;float:left;border: blue solid 0px;"> 
      <div class=middle_bold>&nbsp;<br>
      Sample protocols
      </div>
    </div><br>
    </td>
    </tr>
  <?php 
    foreach($ProtocolArr as $rd){
      if(!$rd['Name']) continue;
      $selected_prot_div_id = $AccessProjectID."_".$rd['ID'];
      $tmp_arr = explode('_', $rd['Type'], 2);
      $protocol_type = $rd['Type'];
      $file_name = "./protocol_detail_pop.php?modal=this_project&outsite_script=1&selected_type_div_id=$protocol_type&selected_prot_div_id=$selected_prot_div_id";
      $group_id = "SAM_".$rd['ID'];
    ?><tr>
        <td nowrap height="25">           
        <div style="width:100%;float:left;height:25px;">
          <div class=small style="word-wrap:break-word;border: blue solid 0px;width:250px;">
          <input type=checkbox name='frm_group_id' value='<?php echo $group_id?>' <?php echo (in_array($group_id, $group_id_arr))?'checked':''?>>&nbsp;
          <a href="javascript: popwin('<?php echo $file_name?>','720','700');" class=button>
            <span style="border: blue solid 0px;;color:white;padding:2px 2px 2px 2px;background-color:<?php echo $type_color_pair[$rd['Type']]?>">SP<?php echo $rd['ID']?></span>
          </a>&nbsp;&nbsp;<?php echo $rd['Name'];?>
          </div>
        </div>
        </td>
      </tr>
    <?php          
    }
    ?>
  </table>
   </div> 
    <?php 
  }
}

function export_file($file_full_name){
  if(_is_file($file_full_name)){
    header("Cache-Control: public, must-revalidate");
    //header("Pragma: hack");
    header("Content-Type: application/octet-stream");  //download-to-disk dialog
    header("Content-Disposition: attachment; filename=".basename($file_full_name).";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: "._filesize($file_full_name));
    $ret = readfile("$file_full_name");
  }else{
    echo "$file_full_name is not a file<br>";
  }
  exit;
}

//call by item_report.php
//call by export_generate_map_file_inc.php
//call by comparison_results_table.php
function get_filter_array_for_export($request_arr){
  global $filter_export_arr;
  global $filter_export_arr_2;
  global $filter_export_arr_3;
  global $passedTypeArr;
  
  $SAINT_filter_arr = array('SpecSum','AvgP','maxSpec','MaxP');
  $SAINT_filter_lable_arr = array('SpecSum'=>'Total Spec','AvgP'=>'Avg SAINT','maxSpec'=>'max Spec','MaxP'=>'max SAINT');
    
  $Peptide_name_arr = array('Pep_num' => 'Total Peptide','Pep_num_uniqe' => 'Uniqe Peptide');
  
  $NS_arr = get_ExpBackGroundSet_id_name_arr();
  $alias_name_arr = get_Filter_Alias_Name_arr();
  $SearchEngine = '';
  foreach($request_arr as $key => $val){
    if($key == 'currentType' || $key == 'type'){
      if($val == 'Exp'){
        $filter_export_arr['Item Type'] = 'Experiment';
      }elseif($val == 'Band'){
        $filter_export_arr['Item Type'] = 'Sample';
      }else{
        $filter_export_arr['Item Type'] = $val;
      }  
    }elseif($key == 'SearchEngine'){
      $filter_export_arr['SearchEngine'] = $val;
    }elseif($key == 'hitType'){
      if($val == 'TPP') $SearchEngine = 'TPP_';
    }elseif($key == 'searchEngineField'){
      $SearchEngine .= $val;
      $filter_export_arr['SearchEngine'] = $SearchEngine;
    }
  }
  $filter_start = 0;
  $aplly_ns = 0;
  $script_action = '';
  $tmp_display = 0;
  
  foreach($request_arr as $key => $val){
    if($key == 'frm_apply_filter' && $val == 'y' || $key == 'applyFilters' && $val == '1' || $key == 'is_show_filter' && $val == 'Y'){
      if($key == 'frm_apply_filter'){
        $script_action = 'export';
      }elseif($key == 'applyFilters'){
        $script_action = 'comparison';
      }elseif($key == 'is_show_filter'){
        $script_action = 'item_report';
      }  
      $filter_start = 1;
      continue;
    } 
    if($filter_start){
      if($key == 'frm_NS'){
        $aplly_ns = 1;
        continue;
      }elseif($key == 'frm_NS_group_id'){
        if($aplly_ns && $val) $filter_export_arr_2['Background Set'] = ' = '.$NS_arr[$val];
      }elseif(preg_match('/check/', $key) || $key == 'frm_Frequency'){
        if($key == 'frm_Expect_check' && $val){
          if($filter_export_arr['SearchEngine'] == 'Mascot' && $request_arr['frm_filter_Expect']){
            $filter_export_arr_2['Mascot Score'] = ' < '.$request_arr['frm_filter_Expect'];
          }elseif($filter_export_arr['SearchEngine'] == 'GPM' && $request_arr['frm_filter_Expect']){
            $filter_export_arr_2['GPM Expect'] = ' < '.$request_arr['frm_filter_Expect'];
          }elseif($filter_export_arr['SearchEngine'] == 'SEQUEST' && $request_arr['frm_filter_Expect']){
            $filter_export_arr_2['SEQUEST Expect'] = ' < '.$request_arr['frm_filter_Expect'];
          }elseif($request_arr['frm_filter_Expect']){
            $filter_export_arr_2['TPP Probability'] = ' < '.$request_arr['frm_filter_Expect'];
          }
        }elseif($key == 'frm_Cov_check' && $val && $request_arr['frm_filter_Coverage']){
          $filter_export_arr_2['Coverage'] = ' < '.$request_arr['frm_filter_Coverage'];
        }elseif($key == 'frm_PT_check' && $val && $request_arr['frm_filter_Peptide_value']){
          if(isset($request_arr['frm_filter_Peptide'])){
            if($request_arr['frm_filter_Peptide'] == 'Pep_num_uniqe'){
              $filter_export_arr_2['Uniqe Peptide'] = ' < '.$request_arr['frm_filter_Peptide_value'];
            }else{
              $filter_export_arr_2['Total Peptide'] = ' < '.$request_arr['frm_filter_Peptide_value'];
            }
          }else{
            $filter_export_arr_2['Uniqe Peptide'] = ' < '.$request_arr['frm_filter_Peptide_value'];
          }
        }elseif($key == 'frm_Frequency' && $val){
          $filter_export_arr_2['Fequency'] = ' > '.$request_arr['frequencyLimit'];
          //$filter_export_arr_2['Fequency_value'] = 
        }
      }elseif(preg_match('/^frm_filter_(.+)/', $key, $matches)){
        if($script_action == 'item_report'){
          continue;
        }else{
          if($key == 'frm_filter_Fequency_value' && $val > 100) continue;
          if($val){
            if($filter_export_arr['SearchEngine'] == 'Mascot' && $matches[1] == 'Expect'){
              $filter_export_arr_2['Mascot Score'] = ' < '.$val;
            }elseif($filter_export_arr['SearchEngine'] == 'GPM' && $matches[1] == 'Expect'){
              $filter_export_arr_2['GPM Expect'] = ' < '.$val;
            }elseif($filter_export_arr['SearchEngine'] == 'SEQUEST' && $matches[1] == 'Expect'){
              $filter_export_arr_2['SEQUEST Expect'] = ' < '.$val;
            }elseif(strstr($filter_export_arr['SearchEngine'], 'TPP') && $matches[1] == 'Expect'){
              $filter_export_arr_2['TPP Probability'] = ' < '.$val;
            }elseif($matches[1] == 'Peptide'){
              $Peptide_index = $Peptide_name_arr[$val];
            }elseif($matches[1] == 'Peptide_value'){
              if(isset($Peptide_index) && $Peptide_index){
                $filter_export_arr_2[$Peptide_index] = ' < '.$val;
              }  
            }elseif($matches[1] == 'Fequency'){
              if(is_numeric($val)){
                if(isset($passedTypeArr[$val])){
                  $Fequency_index = $passedTypeArr[$val]." Fequency";
                }
              }else{        
                $Fequency_index = "Project ".$val;
              }                
            }elseif($matches[1] == 'Fequency_value'){
              if(isset($Fequency_index) && $Fequency_index){  
                $filter_export_arr_2[$Fequency_index] = ' > '.$val;
              }else{
                $filter_export_arr_2['Project Fequency'] = ' > '.$val;
              }
               
            }else{            
              $filter_export_arr_2[$matches[1]] = ' < '.$val;
            }  
          }  
        }
      }elseif($key == 'frm_color_mode'){
        continue;
      }elseif(preg_match('/^frm_(.+)/', $key, $matches)){      
        if(array_key_exists($matches[1], $alias_name_arr)){
          $filter_export_arr_3[$alias_name_arr[$matches[1]]] = $val;
        }else{
          if(strstr($matches[1], 'biogrid_')) continue;
          $filter_export_arr_3[$matches[1]] = $val;
        }
      }elseif(array_key_exists($key, $SAINT_filter_lable_arr)){      
        $filter_export_arr_2[$SAINT_filter_lable_arr[$key]] = ' < '.$val;
      }  
    }
    if($key == 'filterd_prey_cells' && $val){
      $filter_export_arr_2['Manually removed cells'] = ': '. str_replace(",", ";", $val);
    }
    if($key == 'filterd_prey_lines' && $val){
      $filter_export_arr_2['Manually removed prey lines'] = ': '. str_replace(",", ";", $val);
    }
  }
}

//call by item_report.php
//call by export_generate_map_file_inc.php
//call by comparison_results_table.php
////call by export_SAINT_file_inc.php
function write_filter_info_map($handle, $ItemType=''){
  global $filter_export_arr;
  global $filter_export_arr_2;
  global $filter_export_arr_3;
  global $AccessUserName;
  global $AccessProjectID;
  global $AccessProjectName;
  global $SearchEngine_lable_arr;
  global $PROHITSDB;
  
  if(!isset($PROHITSDB)){
    $PROHITSDB = new mysqlDB(PROHITS_DB);
  }
  //$SCRIPT_NAME = basename($_SERVER['PHP_SELF']);
  if($ItemType){
    $general_info = '';
    $report_line = trim($ItemType) . " report\r\n";
  }else{
    $general_info = "general_info@@";
    $report_line = $general_info.((isset($filter_export_arr['Item Type']))?$filter_export_arr['Item Type']:'SAINT')." report\r\n";;
  }
  fwrite($handle, $report_line);
  if(isset($filter_export_arr['SearchEngine']) && isset($SearchEngine_lable_arr[$filter_export_arr['SearchEngine']]) && $SearchEngine_lable_arr[$filter_export_arr['SearchEngine']]){
    $SearchEngine_lable = $SearchEngine_lable_arr[$filter_export_arr['SearchEngine']];
  }else{
    $SearchEngine_lable = isset($filter_export_arr['SearchEngine'])?$filter_export_arr['SearchEngine']:"";
  }
  $report_line = $general_info."SearchEngine: ".$SearchEngine_lable."\r\n";
  fwrite($handle, $report_line);
  $report_line = $general_info."Project name: ".$AccessProjectName."\r\n";
  fwrite($handle, $report_line);
  if($ItemType == 'SAINT' && $AccessProjectID){
    $SQL = "SELECT `ID`, `Name`, `TaxID`, `DBname`, `Description`, `LabID` FROM `Projects` WHERE `ID` = '$AccessProjectID'";
    $tmp_project_arr = $PROHITSDB->fetch($SQL);
    foreach($tmp_project_arr as $tmp_key => $tmp_project_val){
      if($tmp_key == 'ID'){
        $report_line = $general_info."Project ID: ".$tmp_project_val."\r\n";
        fwrite($handle, $report_line);
      }elseif($tmp_key == 'TaxID'){
        $report_line = $general_info."Project TaxID: ".$tmp_project_val."\r\n";
        fwrite($handle, $report_line);
      }elseif($tmp_key == 'DBname'){
        $report_line = $general_info."Project DBname: ".$tmp_project_val."\r\n";
        fwrite($handle, $report_line);
      }elseif($tmp_key == 'LabID'){
        $report_line = $general_info."LabID: ".$tmp_project_val."\r\n";
        fwrite($handle, $report_line);
      }
    }
  }  
  $report_line = $general_info."Created by: ".$AccessUserName."\r\n";
  fwrite($handle, $report_line);
  $report_line = $general_info."Creation date: ".@date("Y-m-d")."\r\n";
  fwrite($handle, $report_line);
  if($ItemType){
    if($ItemType != 'SAINT'){
      $star_line = "**********************************************\r\n";
      fwrite($handle, $star_line);
    }  
    $filter_info = '';
  }else{
    $filter_info = "filter_info@@";
  }  
  if($filter_export_arr_2 || $filter_export_arr_3){
    $report_line = $filter_info."<Filters>:\r\n";
    fwrite($handle, $report_line);
    foreach($filter_export_arr_2 as $key => $val){
      $report_line = $filter_info.$key.$val."\r\n";
      fwrite($handle, $report_line);
    }
    foreach($filter_export_arr_3 as $key => $val){
      if($key == 'min_XPRESS' || $key == 'max_XPRESS'){
        $report_line = $filter_info.$key.'='.$val."\r\n";
      }else{
        $report_line = $filter_info.$key."\r\n";
      }  
      fwrite($handle, $report_line);
    }
    //if($ItemType){
      $desh_line = $filter_info."</Filters>\r\n";
      fwrite($handle, $desh_line);
    //}  
  }
  
  if(!$ItemType){
    $report_line = "end_filter_info\r\n";
    fwrite($handle, $report_line);
  }
}

//call by export_hits_inc.php,
//call by export_hits_public.php
//call by comparison_results_export_inc.php
function export_filter_info($in_handle,$out_handle=''){
  global $theaction;
  while(!feof($in_handle)){
    $buffer = fgets($in_handle);
    $buffer = trim($buffer);
    if($buffer == "end_filter_info"){
      if($out_handle){
        fwrite($out_handle, "\r\n");
      }
      return;
    }
    if($out_handle){
      if(preg_match('/^general_info@@(.*)/', $buffer, $matches)){
        fwrite($out_handle, $matches[1]."\r\n");
      }elseif(preg_match('/^filter_info@@(.*)/', $buffer, $matches)){
        fwrite($out_handle, $matches[1]."\r\n");
      }
    }  
  }
  if($in_handle) rewind($in_handle);
}  
function get_ExpBackGroundSet_id_name_arr(){
  global $AccessProjectID;
  global $HITSDB;
  $id_name_arr = array();
  $SQL = "SELECT `ID`,`Name` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
  $tmp_arr = $HITSDB->fetchAll($SQL);
  foreach($tmp_arr as $tmp_val){
    $id_name_arr[$tmp_val['ID']] = $tmp_val['Name'];
  }
  return $id_name_arr;
}

function get_Filter_Alias_Name_arr(){
  global $PROHITSDB;
  $alias_name_arr = array();
  $SQL = "SELECT `Alias`,`Name` FROM `FilterName` WHERE `Alias` IS NOT NULL AND `Alias`!=''";
  $tmp_arr = $PROHITSDB->fetchAll($SQL);
  foreach($tmp_arr as $tmp_val){
    $alias_name_arr[$tmp_val['Alias']] = $tmp_val['Name'];
  }
  return $alias_name_arr;
}

function add_species($frm_TaxID,$new_species){
  global $PROHITSDB;
  if(!$frm_TaxID) return false;
  $SQL = "SELECT `TaxID` FROM `ProteinSpecies` WHERE `TaxID`='$frm_TaxID'";
  $tmp_arr = $PROHITSDB->fetch($SQL);
  if(!$tmp_arr){
    $SQL = "SELECT MAX(ID) as biggestNum FROM `ProteinSpecies`";
    $tmp_arr = $PROHITSDB->fetch($SQL);
    if($tmp_arr){
      $biggest_id_num = $tmp_arr['biggestNum'] + 0.1;
      if($tmp_arr){
        $SQL = "INSERT INTO ProteinSpecies SET 
                ID='$biggest_id_num',
                TaxID='".$frm_TaxID."',
                Name='".$new_species."',
                Species='y'";
        $PROHITSDB->insert($SQL);
      }
    }  
  }
}

function Description_div_for_sample($Description_value,$Sample_id,$textarea_name="frm_Description",$readonly=''){ 
  $DIV_id = "des_".$Sample_id;
  global $TB_CELL_COLOR,$TB_HD_COLOR;
?>              
  <td align=left><font size=2>
  <a href="javascript: href_show_hand();" onclick="hideTips('des_');showTip(event,'<?php echo $DIV_id;?>'); tmp_flag=1;" >
  <img border="0" src="images/desciption_pop.gif" alt="Description">
  </a>
  <DIV ID='<?php echo $DIV_id;?>' STYLE="position: absolute;display: none;border: black solid 1px;width: 223px;z-index:30">
    <table align="center" cellspacing="0" cellpadding="0" border="0" width=100% bgcolor="#ffffff">
      <tr bgcolor="#e0e0e0" height=20>
        <td valign="bottem" align="left">&nbsp;<font color="#818181" face="helvetica,arial,futura" size="2"><b>Description:</b></font>
        <td valign="bottem" align="right">
          <a href="javascript: hideTip('<?php echo $DIV_id;?>');"><img border="0" src="images/icon_remove.gif" alt="Close"></a>&nbsp;&nbsp;
        </td>
      </tr>
      <tr ><td colspan="2"><div class=maintext>
      <?php if($readonly){?>
        <?php echo nl2br(htmlentities($Description_value));?>&nbsp;&nbsp;
      <?php }else{?>
      <textarea cols="30" rows="5" name="<?php echo $textarea_name?>"><?php echo $Description_value;?></textarea>
      <?php }?>
      </div>
      </td></tr>
    </table>   
  </DIV>
  </td>
<?php               
}

function show_project_users_list(){
  global $PROHITSDB,$AccessProjectID;
  $SQL = "SELECT 
          U.ID, 
          U.Fname,
          U.Lname 
          FROM ProPermission P 
          LEFT JOIN User U ON (P.UserID=U.ID)
          WHERE P.ProjectID='$AccessProjectID'
          ORDER BY U.Fname";
  $tmp_arr = $PROHITSDB->fetchAll($SQL);
  $user_id_name_arr = array();
  foreach($tmp_arr as $tmp_val){
    if(!$tmp_val['ID'] || !$tmp_val['Fname'] && !$tmp_val['Lname']) continue;
    $user_id_name_arr[$tmp_val['ID']] = $tmp_val['Fname']." ".$tmp_val['Lname'];	
  }
  return $user_id_name_arr;
}

function array_to_delimited_str(&$sqlArr, $index){
  $tmpStr = '';
  foreach($sqlArr as $tmpValue){
    if(!trim($tmpValue[$index])) continue;
    if($tmpStr) $tmpStr .= ',';
    $tmpStr .= $tmpValue[$index];
  }  
  return $tmpStr;
}
function array_and_delimited_str(&$sqlArr, $index, &$outArr){
  $tmpStr = '';
  foreach($sqlArr as $tmpValue){
    if(!trim($tmpValue[$index])) continue;
    array_push($outArr, $tmpValue[$index]);
    if($tmpStr) $tmpStr .= ',';
    $tmpStr .= $tmpValue[$index];
  }
  return $tmpStr;
}
function array_to_array(&$sqlArr, $index, &$outArr){
  $tmpStr = '';
  foreach($sqlArr as $tmpValue){
    array_push($outArr, $tmpValue[$index]);
  }
}

/*-----------------------------------------------------------------------------------------------
1. $item_type: Bait, Experiment, Band.
2. $frm_group_id_list: Can be multiple groups list for example "Bait_12,Bait_13,Experiment_2,Experiment_3,Band_22,Band_12,SAM_11,SAM_10".
3. including  user id search.
4. returned IDs match item_type's type. 
-------------------------------------------------------------------------------------------------*/
function get_all_group_recordes_str($frm_group_id_list,$item_type){
  global $used_group_arr,$frm_user_id,$HITSDB;
  //if(!$frm_group_id_list || !$item_type) return '';
  $group_name_ids_arr = array();
  $all_group_recodes_arr = array();
  $all_group_recodes_str = '';
  $OwnerID = '';
  $group_user_ID = '';
  if($frm_user_id){
    $OwnerID = " AND OwnerID = $frm_user_id";
  }
  $tmp_arr = explode(",", $frm_group_id_list);
  foreach($tmp_arr as $tmp_val){
    $tmp_arr2 = explode("_", $tmp_val);
    if(count($tmp_arr2) == 3){
      $tmp_group_name = $tmp_arr2[0]."_".$tmp_arr2[1];
      $tmp_group_id = $tmp_arr2[2];
    }else{
      $tmp_group_name = $tmp_arr2[0];
      $tmp_group_id = $tmp_arr2[1];
    }
    if(!in_array($tmp_group_name, $used_group_arr)){
      array_push($used_group_arr, $tmp_group_name);
    }
    if(!array_key_exists($tmp_arr2[0], $group_name_ids_arr)){
      $group_name_ids_arr[$tmp_arr2[0]] = $tmp_group_id;
    }else{
      $group_name_ids_arr[$tmp_arr2[0]] .= ','.$tmp_group_id;
    }
  }
  foreach($group_name_ids_arr as $key => $val){
    $sub_SQL = '';
    if($key == 'Band'){
      $sub_SQL = " AND Note NOT LIKE 'SAM%'";
    }elseif($key == 'SAM'){
      $key = 'Band';
      $sub_SQL = " AND Note LIKE 'SAM%'";
    }
    $table_name = $key."Group";       
    $SQL = "SELECT RecordID FROM $table_name WHERE NoteTypeID IN ($val)".$sub_SQL;
    if($tmp_sql_arr = $HITSDB->fetchAll($SQL)){
      $record_ids_str = array_to_delimited_str($tmp_sql_arr, 'RecordID');
      if($record_ids_str){
        $ID = 'ID'; 
        $Group_by = "";
        if($key == 'Bait'){
          if($item_type == 'Band' || $item_type == 'Experiment'){
            $tmp_item_id_lable = 'BaitID';
          }elseif($item_type == 'Bait'){
            $tmp_item_id_lable = 'ID';
          }
        }elseif($key == 'Experiment'){
          if($item_type == 'Band'){
            $tmp_item_id_lable = 'ExpID';
          }elseif($item_type == 'Experiment'){
            $tmp_item_id_lable = 'ID';
          }elseif($item_type == 'Bait'){
            $item_type = 'Experiment';
            $tmp_item_id_lable = 'ID';
            $ID = 'BaitID AS ID';
            $Group_by = " GROUP BY BaitID ";
          }  
        }elseif($key == 'Band'){
          if($item_type == 'Band'){
            $tmp_item_id_lable = 'ID';
          }elseif($item_type == 'Experiment'){
            $tmp_item_id_lable = 'ID';
            $item_type = 'Band';
            $ID = 'ExpID AS ID';
            $Group_by = " GROUP BY ExpID ";
          }elseif($item_type == 'Bait'){
            $tmp_item_id_lable = 'ID';
            $item_type = 'Band';
            $ID = 'BaitID AS ID';
            $Group_by = " GROUP BY BaitID ";
          }    
        }else{
          return;
        }
        $SQL = "SELECT $ID FROM $item_type WHERE $tmp_item_id_lable IN ($record_ids_str) $OwnerID $Group_by";
        $tmp_id_arr = $HITSDB->fetchAll($SQL);
        foreach($tmp_id_arr as $tmp_id_val){
          if($tmp_id_val['ID'] && !in_array($tmp_id_val['ID'], $all_group_recodes_arr)){
            array_push($all_group_recodes_arr, $tmp_id_val['ID']);
          }
        }
      }
    }
  }
  return $all_group_recodes_str = implode(",", $all_group_recodes_arr);
} 

function get_itemID_frequencyFileName_arr($item_type,$hits_db_type,$index=''){ 
  global $AccessProjectID;
  if($item_type == "Bait"){
    $item_type_f = "B";
  }elseif($item_type == "Exp" || $item_type == "Experiment"){
    $item_type_f = "E";
  }elseif($item_type == "Band" || $item_type == "Sample"){
    $item_type_f = "S";
  }

  $Prohits_Data_dir = STORAGE_FOLDER . "Prohits_Data/";
  $user_frequency_dir = $Prohits_Data_dir . "user_d_frequency/P_$AccessProjectID";
  
  $id_frequencyFileName_arr = array();
  if(_is_dir($user_frequency_dir)){
    $user_frequency_files = scandir($user_frequency_dir);
    rsort($user_frequency_files);  
    foreach($user_frequency_files as $user_frequency_file){
      if($user_frequency_file == '.' || $user_frequency_file == '..') continue;
      $tmp_arr = explode("-", $user_frequency_file);
      if($tmp_arr[0] != $item_type_f) continue;
      if($hits_db_type == "TPP"){
        if(!preg_match('/^(TPP)_(.+)?\./', $tmp_arr[2])){
          continue;
        }
      }else{
        if(preg_match('/^(TPP)_(.+)?\./', trim($tmp_arr[2]))){
          continue;
        }
      }      
      $file_full_name = $user_frequency_dir."/".$user_frequency_file;    
      $handle = @fopen($file_full_name, "r");
      if(!$handle) continue;
      while(!feof($handle)) {
        $buffer = fgets($handle, 4096);
        $buffer = trim($buffer);
        if(preg_match('/^Item id list:(.+)$/', $buffer, $matchse)){
          $tmp_id_arr = explode(",", trim($matchse[1]));
          if($index == 'itemID'){
            foreach($tmp_id_arr as $tmp_id){
              if(!array_key_exists($tmp_id, $id_frequencyFileName_arr)){
                $id_frequencyFileName_arr[$tmp_id] = $user_frequency_file;
              }else{
                $id_frequencyFileName_arr[$tmp_id] .= ','.$user_frequency_file;
              }
            }
          }else{
            $id_frequencyFileName_arr[$user_frequency_file] = $tmp_id_arr;
          }  
          break;
        }
      }
      fclose($handle);
    }
  }
  return $id_frequencyFileName_arr;
}

function generate_frequency_arr($FileName){
  $frequencyArr = array();
  if(!$FileName) return $frequencyArr;
  if(!_is_file($FileName)) return $frequencyArr;
  $frequencyHandle = fopen($FileName, "r");
  if($frequencyHandle){
    $is_start_point = 0;    
    while(!feof($frequencyHandle)){
      $buffer = fgets($frequencyHandle, 4096);
      $buffer = trim($buffer);
      if(!$buffer) continue;
      $tmpArr = explode(',',$buffer);
      if($tmpArr[0] == 'GeneID'){
        $is_start_point = 1;
        continue;
      }elseif(!$is_start_point){
        continue;
      }
      if(strstr($FileName, 'geneLevel')){
        $gene_arr = explode("|",$tmpArr[0]);
        $frequencyArr[$gene_arr[0]] = $tmpArr[1];
      }else{
        $frequencyArr[$tmpArr[0]] = $tmpArr[1];
      }  
    }
    fclose($frequencyHandle);
  }
  return $frequencyArr;
}

function generate_frequency_arr_for_geneLevel($FileName,&$GeneName_arr){
  $frequencyArr = array();
  if(!$FileName) return $frequencyArr;
  if(!_is_file($FileName)) return $frequencyArr;
  $frequencyHandle = fopen($FileName, "r");
  if($frequencyHandle){
    $is_start_point = 0;    
    while(!feof($frequencyHandle)){
      $buffer = fgets($frequencyHandle, 4096);
      $buffer = trim($buffer);
      if(!$buffer) continue;
      $tmpArr = explode(',',$buffer);
      if($tmpArr[0] == 'GeneID'){
        $is_start_point = 1;
        continue;
      }elseif(!$is_start_point){
        continue;
      }
      if(strstr($FileName, 'geneLevel')){
        $gene_arr = explode("|",$tmpArr[0]);
        $frequencyArr[$gene_arr[0]] = $tmpArr[1];
        $GeneName_arr[$gene_arr[0]] = $gene_arr[1];
      }else{
        $frequencyArr[$tmpArr[0]] = $tmpArr[1];
        
      }  
    }
    fclose($frequencyHandle);
  }
  return $frequencyArr;
}

function generate_user_defined_frequency($frm_selected_list_str, $is_return_arr=''){//&&&&&&&&&&&&&&&&&&&&&&&&&&&
  global $user_frequency_dir;
  global $frm_frequency_name;
  global $frm_frequency_description;
  global $currentType;
  global $HITSDB;
  global $AccessProjectID;
  global $SearchEngine;
  global $AccessUserName;
  global $AccessUserID;
  global $Is_geneLevel;
  global $hits_table;
  global $Frequency_filters_U_file;
  global $filter_status_arr;
  
  $tableName = $hits_table;
  
  $filter_arr_U['Hits'] = array('Expect'=>'','Expect2'=>'','Pep_num'=>'','Pep_num_uniqe'=>'');
  $filter_arr_U['TppProtein'] = array('PROBABILITY'=>'','TOTAL_NUMBER_PEPTIDES'=>'','UNIQUE_NUMBER_PEPTIDES'=>'');
  $filter_arr_U['Hits_GeneLevel'] = array('SpectralCount'=>'','Unique'=>'');
  
  $switch_arr = array('Hits'=>'','TppProtein'=>'tpp','Hits_GeneLevel'=>'geneLevel');  
  $frequency_arr = array();
  if(!$is_return_arr){
    if(!$currentType || !$user_frequency_dir || !$frm_frequency_name) return false;
  }else{
    //return $frequency_arr;
  }  
   
  $type_lable = array('Bait'=>'Bait','Exp'=>'Experiment','Band'=>'Sample');
      
  $file_name = $user_frequency_dir .'/'. $frm_frequency_name . ".csv";
  $tmp_arr1 = explode(";", $frm_selected_list_str);
  $item_id_str = '';
  foreach($tmp_arr1 as $tmp_arr1_val){
    $tmp_arr = explode(":", $tmp_arr1_val);
    if(count($tmp_arr) < 2) continue;
    if($item_id_str) $item_id_str .= ',';
    $item_id_str .= $tmp_arr[1];
  }
  
  $item_id_str_display = $item_id_str;  
  
  if($currentType == 'Bait' || $currentType == 'Exp'){
    $tmp_item_id = $currentType.'ID';
    $SQL_tmp = "SELECT ID
                FROM Band
                WHERE $tmp_item_id IN ($item_id_str)";
    $tmp_arr = $HITSDB->fetchAll($SQL_tmp);
    $item_id_str = '';
    foreach($tmp_arr as $tmp_val){
      if($item_id_str) $item_id_str .= ',';
      $item_id_str .= $tmp_val['ID'];
    }     
  }
  $sub_sql_arr = get_new_filter_status_U();
  if($hits_table == "Hits_GeneLevel"){
    $sub_SQL = $sub_sql_arr[$switch_arr[$hits_table]];
    $sub_sql = get_subSql_for_frequency_filter($sub_SQL);
    $item_frequency_arr = get_frequency_catched_arr_for_geneLevel_Gel_free($HITSDB,$AccessProjectID,$sub_sql,$item_id_str);
    
  }elseif($hits_table == "TppProtein"){
    $sub_SQL = $sub_sql_arr[$switch_arr[$hits_table]];
    $sub_sql = get_subSql_for_frequency_filter($sub_SQL);
    $SQL = "SELECT COUNT(DISTINCT(BandID)) AS value, 
            GeneID 
            FROM $hits_table 
            WHERE BandID IN ($item_id_str)
            $sub_sql
            GROUP BY GeneID 
            ORDER BY value DESC";
    $item_frequency_arr = $HITSDB->fetchAll($SQL);
  }elseif($hits_table == "Hits"){
    $tmp_BaitID_GeneID_arr_3 = array();
    $sub_SQL = '';  
    foreach($sub_sql_arr as $SearchEngine => $filter_str){
      $sub_sql = get_subSql_for_frequency_filter($filter_str);
      if($sub_SQL) $sub_SQL .= "||";
      $sub_SQL .= $filter_str;
      if($SearchEngine){
        $WHERE = " WHERE (SearchEngine='".$SearchEngine."' OR SearchEngine='".$SearchEngine."Uploaded') ";
      }else{
        $WHERE = " WHERE (SearchEngine='' OR SearchEngine IS NULL) ";
      }
      $SQL = "SELECT BandID AS ID, 
              GeneID 
              FROM Hits 
              $WHERE $sub_sql
              AND BandID IN ($item_id_str)
              ORDER BY GeneID DESC";
      if($tmp_BaitID_GeneID_arr_1 = $HITSDB->fetchAll($SQL)){
        $tmp_BaitID_GeneID_arr_2 = array();
        foreach($tmp_BaitID_GeneID_arr_1 as $tmp_BaitID_GeneID_val_1){
          if(!array_key_exists($tmp_BaitID_GeneID_val_1['GeneID'], $tmp_BaitID_GeneID_arr_2)){
            $tmp_BaitID_GeneID_arr_2[$tmp_BaitID_GeneID_val_1['GeneID']] = array();
          }
          $tmp_BaitID_GeneID_arr_2[$tmp_BaitID_GeneID_val_1['GeneID']][] = $tmp_BaitID_GeneID_val_1['ID'];
        }
        if(!$tmp_BaitID_GeneID_arr_3){
          $tmp_BaitID_GeneID_arr_3 = $tmp_BaitID_GeneID_arr_2;
        }else{
          foreach($tmp_BaitID_GeneID_arr_2 as $hitGeneID => $BandID_arr){
            if(array_key_exists($hitGeneID, $tmp_BaitID_GeneID_arr_3)){
              $tmp_BaitID_GeneID_arr_3[$hitGeneID] = array_merge($tmp_BaitID_GeneID_arr_3[$hitGeneID], $BandID_arr);
            }else{
              $tmp_BaitID_GeneID_arr_3[$hitGeneID] = $BandID_arr;
            }
          }
        }
      }
    }
    $tmp_BaitID_GeneID_arr = array();
    foreach($tmp_BaitID_GeneID_arr_3 as $key => $val){
      $tmp_array['GeneID'] = $key;
      $tmp_array['value'] = count($val);
      $tmp_BaitID_GeneID_arr[] = $tmp_array;
    }
    $item_frequency_arr = $tmp_BaitID_GeneID_arr;
  }        
  $SQL_t = "SELECT COUNT(DISTINCT(BandID)) AS value
            FROM $tableName
            WHERE BandID IN ($item_id_str)";
  $tmp_total = $HITSDB->fetch($SQL_t);
  
  $subTotal = $tmp_total['value'];
  
  if(!$is_return_arr){
    if(!$subTotal) return false;    
    if($handle_write = fopen($file_name, "w")){
      $title = "Created by: $AccessUserName--$AccessUserID\r\n";
      fwrite($handle_write, $title);
      $title = "Created time: ".@date('Y-m-d')."\r\n";
      fwrite($handle_write, $title);
      $title = "Type: ".$type_lable[$currentType]."\r\n";
      fwrite($handle_write, $title);
      $title = "Item id list: $item_id_str_display\r\n";
      fwrite($handle_write, $title);
      $title = "Hits type: $SearchEngine\r\n";
      fwrite($handle_write, $title);      
      $title = "Description: $frm_frequency_description\r\n";
      fwrite($handle_write, $title);
      $sub_SQL = str_replace("::", "&&", $sub_SQL);
      $title = "Filter_SQL: $sub_SQL\r\n\r\n";
      fwrite($handle_write, $title);
      $title = "GeneID,Frequency\r\n";
      fwrite($handle_write, $title);
      foreach($item_frequency_arr as $fValue){
        $freqency = round(($fValue['value'] / $subTotal)*100, 2);
        fwrite($handle_write, $fValue['GeneID'].",".$freqency."\r\n");
      }
      fclose($handle_write);
      if($log_handle = fopen($Frequency_filters_U_file,"a")){
        $line = "#####".basename($file_name)."---".@date("Y-m-d H:i:s");                   
        fwrite($log_handle, $line."\r\n");
        fclose($log_handle);
      }
      
      return true;
    }else{
      return false;
    }
  }else{
    if(!$subTotal) return $frequency_arr;
    foreach($item_frequency_arr as $fValue){
      $freqency = round(($fValue['value'] / $subTotal)*100, 2);
      $frequency_arr[$fValue['GeneID']] = $freqency;
    }
    return $frequency_arr;
  }  
}

function get_all_frequency_info(){
  global $all_frequency_name_lable_arr;
  global $all_frequency_info_arr;
  global $frequency_dir_arr;
  global $AccessProjectID;
  global $HITSDB;
  global $Is_geneLevel;  

  $SQL = "SELECT `ID`,`Name`,`Description`,`Type`,`Initial`, `Icon` FROM `NoteType` WHERE `ProjectID`='$AccessProjectID'";
  $note_type_arr = $HITSDB->fetchAll($SQL);
  $note_type_id_arr = array();
  foreach($note_type_arr as $note_type_val){
    $note_type_id_arr[$note_type_val['ID']]['Name'] = $note_type_val['Name'];
    $note_type_id_arr[$note_type_val['ID']]['Description'] = $note_type_val['Description'];
    $note_type_id_arr[$note_type_val['ID']]['Icon'] = $note_type_val['Icon'];
    $note_type_id_arr[$note_type_val['ID']]['Type'] = $note_type_val['Type'];
    $note_type_id_arr[$note_type_val['ID']]['Initial'] = $note_type_val['Initial'];
  }
  
  if(_is_dir($frequency_dir_arr['P'])){
    $frequency_files = scandir($frequency_dir_arr['P']);
    sort($frequency_files);
  }else{
    $frequency_files = array();
  }
  
  foreach($frequency_files as $file_name){
    if(!strstr($file_name, 'P'.$AccessProjectID."_") || strstr($file_name, 'exported')) continue;
    $full_file_name = $frequency_dir_arr['P'].'/'.$file_name;
    if(strstr($file_name, 'tpp')){
      $all_frequency_name_lable_arr['P']['P:'.$file_name] = "(P) TPP ";
      if(isset($all_frequency_info_arr)){
        $all_frequency_info_arr['P']['P:'.$file_name]['description'] = "Project frequency from all search engine TPP hits";
        $all_frequency_info_arr['P']['P:'.$file_name]['m_time'] = (file_exists($full_file_name))?@date("Y-m-d H:i:s", filemtime($full_file_name)):'';
      }
    }else{
      preg_match('/P\d+_(\w+)_frequency.csv/',$file_name, $matches);
      if(isset($matches[1])){
        $SE = ($matches[1]=='no_SE')?'no search engine':$matches[1];
        if($matches[1] == 'GPM') $matches[1] = 'XTandem';
      }else{
        $SE = '';
      } 
      $all_frequency_name_lable_arr['P']['P:'.$file_name] = "(P) ".((isset($matches[1]))?$matches[1]:'Project');
      if(isset($all_frequency_info_arr)){
        $all_frequency_info_arr['P']['P:'.$file_name]['description'] = "Project frequency from ".(($SE)? $SE.' hits':' all search engine hits except TPP hits.');
        $all_frequency_info_arr['P']['P:'.$file_name]['m_time'] = (file_exists($full_file_name))?@date("Y-m-d H:i:s", filemtime($full_file_name)):'';        
      }
    }
  }
  
  if(_is_dir($frequency_dir_arr['U'])){
    $user_frequency_files = scandir($frequency_dir_arr['U']);
    sort($user_frequency_files);
  }else{
    $user_frequency_files = array();
  }
  
  foreach($user_frequency_files as $file_name){
    if($file_name == '.' || $file_name == '..') continue;
    $full_file_name = $frequency_dir_arr['U'].'/'.$file_name;
    if(strstr($file_name, 'TPP')){
      if(preg_match('/(TPP)_(.+)?\./', $file_name, $matches)){
        $all_frequency_name_lable_arr['U']['U:'.$file_name] = "(U) ".$matches[2]." ".$matches[1];
      }
    }elseif(strstr($file_name, 'geneLevel')){
      if(preg_match('/(geneLevel)_(.+)?\./', $file_name, $matches)){
        $all_frequency_name_lable_arr['U']['U:'.$file_name] = "(U) ".$matches[2]." ".$matches[1];
      }
    }else{
      if(preg_match('/-\d+-(.+)?\./', $file_name, $matches)){
        $all_frequency_name_lable_arr['U']['U:'.$file_name] = "(U) ".$matches[1];
      }  
    }
    if(isset($all_frequency_info_arr)){
      $tmp_ret = get_Ufrequency_description($full_file_name);
      $tmp_ret_arr = explode(';;;',$tmp_ret);          
      $all_frequency_info_arr['U']['U:'.$file_name]['description'] = $tmp_ret_arr[1];
      $all_frequency_info_arr['U']['U:'.$file_name]['owner'] = $tmp_ret_arr[0];
      $all_frequency_info_arr['U']['U:'.$file_name]['m_time'] = (file_exists($full_file_name))?@date("Y-m-d H:i:s", filemtime($full_file_name)):'';        
      if(isset($tmp_ret_arr[2])){
        $all_frequency_info_arr['U']['U:'.$file_name]['Filter_SQL'] = $tmp_ret_arr[2];
      }        
    } 
  }
  
  if(_is_dir($frequency_dir_arr['G'])){
    $sub_frequency_files = scandir($frequency_dir_arr['G']);
    sort($sub_frequency_files);
  }else{
    $sub_frequency_files = array();
  }
  
  foreach($sub_frequency_files as $file_name){
    if(!strstr($file_name, 'Pro'.$AccessProjectID.'_')) continue;
    $full_file_name = $frequency_dir_arr['G'].'/'.$file_name;
    if(preg_match('/Type(\d+)/', $file_name, $matches)){
      if(array_key_exists($matches[1], $note_type_id_arr)){
        if(strstr($file_name, 'TPP')){
          $all_frequency_name_lable_arr['G']['G:'.$file_name] = '(G) ' . $note_type_id_arr[$matches[1]]['Name'] . " TPP";
        }elseif(strstr($file_name, 'geneLevel')){
          $all_frequency_name_lable_arr['G']['G:'.$file_name] = '(G) ' . $note_type_id_arr[$matches[1]]['Name'] . " geneLevel";
        }else{
          $all_frequency_name_lable_arr['G']['G:'.$file_name] = '(G) ' . $note_type_id_arr[$matches[1]]['Name'];
        }
        if(isset($all_frequency_info_arr)){
          $all_frequency_info_arr['G']['G:'.$file_name]['description'] = $note_type_id_arr[$matches[1]]['Description'];
          $all_frequency_info_arr['G']['G:'.$file_name]['m_time'] = (file_exists($full_file_name))?@date("Y-m-d H:i:s", filemtime($full_file_name)):'';                
          $all_frequency_info_arr['G']['G:'.$file_name]['Icon'] = $note_type_id_arr[$matches[1]]['Icon'];
          $all_frequency_info_arr['G']['G:'.$file_name]['Initial'] = $note_type_id_arr[$matches[1]]['Initial'];
          $all_frequency_info_arr['G']['G:'.$file_name]['Type'] = $note_type_id_arr[$matches[1]]['Type'];                
        }
      }
    }
  }
  return $note_type_id_arr;
}

function get_Ufrequency_description($FileName){
  $ret = '';        
  $frequencyHandle = fopen($FileName, "r");
  if($frequencyHandle){
    while(!feof($frequencyHandle)){
      $buffer = fgets($frequencyHandle, 4096);
      $buffer = trim($buffer);
      if(strstr($buffer, 'Created by:')){
        $tmpArr = explode(':',$buffer);
        $ret = trim($tmpArr[1]).";;;";
      }
      if(strstr($buffer, 'Description:')){
        $tmpArr = explode(':',$buffer);
        $ret .= trim($tmpArr[1]).";;;";
      }
      if(strstr($buffer, 'Filter_SQL:')){
        $tmpArr = explode(':',$buffer);
        $ret .= trim($tmpArr[1]);
        break;
      }
    }
    fclose($frequencyHandle);
  }
  return $ret;          
} 

function get_optionArr_for_user_d_frequency($frm_selected_list_str,$currentType,$hitType){
  $optionArr_for_user_d_frequency = array();
  $id_frequencyFileName_arr = array();
  $tmp_list_arr = explode(";", $frm_selected_list_str);
  $selected_item_id_arr = array();
  foreach($tmp_list_arr as $tmp_list_val){
    $tmp_list_arr2 = explode(":", $tmp_list_val);
    $tmp_id_str = $tmp_list_arr2[1];
    $id_arr = explode(",", $tmp_id_str);
    foreach($id_arr as $id_val){
      array_push($selected_item_id_arr, $id_val);
    }
  }                  
  if($hitType == 'TPP' || $hitType == 'normal' || $hitType == 'geneLevel'){
    $id_frequencyFileName_arr = get_itemID_frequencyFileName_arr($currentType,$hitType);
  }
  foreach($id_frequencyFileName_arr as $id_frequencyFileName_key => $id_frequencyFileName_val){
    if(!array_diff($selected_item_id_arr, $id_frequencyFileName_val)){
      $tmp_arr = explode('-',$id_frequencyFileName_key);
      if($hitType == 'TPP'){
        if(strpos(trim($tmp_arr[2]), "TPP") !== 0) continue;
        $option_lable = "(U) ".$tmp_arr[2];
      }elseif($hitType == 'geneLevel'){
        if(strpos(trim($tmp_arr[2]), "geneLevel") !== 0) continue;
        $option_lable = "(U) ".$tmp_arr[2];
      }elseif($hitType == 'normal'){
        if(strpos(trim($tmp_arr[2]), "geneLevel") === 0 || strpos(trim($tmp_arr[2]), "TPP") === 0) continue;
        $option_lable = "(U) ".$tmp_arr[2];
      }else{
        continue;
      }
      $option_value = "U:".$id_frequencyFileName_key;
      $optionArr_for_user_d_frequency[$option_value] = $option_lable;
    }
  }                
  return $optionArr_for_user_d_frequency;
}

function format_pro_type_name($pro_type_name){
  $pro_type_name = str_replace("\$", " ", $pro_type_name);
  if(preg_match('/^SAM_(.+)/', $pro_type_name, $matches)){
    $protocol_lable = $matches[1];
  }else{
    $protocol_lable = $pro_type_name;
  }
  return $protocol_lable;
} 

function protocol_type_color_pair(){
  global $AccessProjectID,$HITSDB;
  $type_color_pair = array();
  $color_arr = array('#cc9999','#669933','#cc9933','#33cc00','#7c7c7c',
                     '#ff8000','#ff0000','#00ffff','#800000','#0080c0',
                     '#ff00ff','#800000','#ff80ff','#0080ff','#0000ff',
                     '#00ff00','#8000ff','#808080','#008040','#ffff00');
  $SQL = "SELECT `ID`,
                 `Type` 
         FROM `Protocol` 
         WHERE `ProjectID`='$AccessProjectID' 
         AND `Type`LIKE 'SAM%' 
         GROUP BY `Type` 
         ORDER BY ID";
  $tmp_arr = $HITSDB->fetchAll($SQL);
  for($i=0; $i<count($tmp_arr); $i++){
    $type_color_pair[$tmp_arr[$i]['Type']] = $color_arr[$i];
  }
  return $type_color_pair;
}

function num_protocols_for_this_project($ProjectID){
  global $HITSDB;
  $SQL = "SELECT Type
          FROM Protocol 
          WHERE ProjectID='$ProjectID'
          AND Name IS NOT NULL
          AND Type COLLATE latin1_general_cs LIKE 'SAM_%'
          GROUP BY Type
          ORDER BY Type";
  $tmp_arr = $HITSDB->fetchAll($SQL);
  return count($tmp_arr);
}

function num_protocols_used_by_this_sample($sample_ID){
  global $HITSDB;
  $SQL = "SELECT B.ID 
          FROM BandGroup B
          LEFT JOIN Protocol P
          ON (P.ID=B.NoteTypeID) 
          WHERE B.RecordID='$sample_ID'
          AND B.Note LIKE 'SAM%'";
  $tmp_arr = $HITSDB->fetchAll($SQL);
  return count($tmp_arr);
}

function is_all_sample_protocls_used($sample_ID){
  global $AccessProjectID;
  $num_protocols_for_this_project = num_protocols_for_this_project($AccessProjectID);
  $num_protocols_used_by_this_sample = num_protocols_used_by_this_sample($sample_ID);
  if($num_protocols_for_this_project == $num_protocols_used_by_this_sample){
    return 1;
  }else{
    return 0;
  }  
}

function sample_protocols_select_update_block($Band_ID=''){
  global $HITSDB,$AccessProjectID,$bgcolor;
  global $SCRIPT_NAME;
  global $AUTH,$AccessUserID; 
  $pro_type_counter = 0;
  
  if($SCRIPT_NAME == 'pop_note.php'){
    $border = 0;
    $style = "font-weight:normal;";
    $td_align = "left";
  }else{
    $border = 0;
    $style = "font-weight:normal;";
    $td_align = "right";
  }
  $SQL = "SELECT P.ID,
               P.Name ,
               P.Type,
               P.Detail,
               B.ID AS Protocol_ID,
               B.UserID 
               FROM BandGroup B
               LEFT JOIN Protocol P
               ON (P.ID=B.NoteTypeID) 
               WHERE B.RecordID='$Band_ID'
               AND B.Note LIKE 'SAM%'";
  $tmp_pro_arr = $HITSDB->fetchAll($SQL);
  ?>
  <table border="<?php echo $border;?>" cellpadding="1" cellspacing="1" width="100%">
  <?php 
  $used_pro_arr = array();
  $used_pro_link_arr = array();
  foreach($tmp_pro_arr as $tmp_pro_val){
    $used_pro_arr[$tmp_pro_val['ID']] = $tmp_pro_val['UserID'];
    $used_pro_link_arr[$tmp_pro_val['ID']] = $tmp_pro_val['Protocol_ID'];
  }  
  $SQL = "SELECT 
          `ID`,
          `Name`,
          `Type`
          FROM `Protocol` 
          WHERE `ProjectID`='$AccessProjectID'
          AND Type COLLATE latin1_general_cs LIKE 'SAM_%'
          ORDER BY Type";
  if($protocol_type_sql_arr = $HITSDB->fetchAll($SQL)){
    $protocol_type_arr = array();
    foreach($protocol_type_sql_arr as $protocol_type_sql_val){
      if(!$protocol_type_sql_val['Name']) continue;
      if(!array_key_exists($protocol_type_sql_val['Type'], $protocol_type_arr)){
        $protocol_type_arr[$protocol_type_sql_val['Type']] = array();
      }
      array_push($protocol_type_arr[$protocol_type_sql_val['Type']], $protocol_type_sql_val);
    }
    foreach($protocol_type_arr as $type_key => $type_val){
      $single_type = $type_val;
      $tmp_arr = explode("_", $type_key, 2);
      $selected_flag = 0;
      $selected_name = '';
      foreach($single_type as $single_val){
        if(array_key_exists($single_val['ID'], $used_pro_arr)){
          $selected_flag = $single_val['ID'];
          $selected_name = $single_val['Name'];
          break;
        }
      }
      if($selected_flag){
        if($SCRIPT_NAME != 'pop_note.php'){
          $file  = "./protocol_detail_pop.php?modal=this_project&outsite_script=1&selected_type_div_id=".$type_key."&selected_prot_div_id=SA_$selected_flag";
  ?>      
    <tr>
      <td bgcolor="<?php echo  $bgcolor;?>" align=<?php echo $td_align?> colspan="2" nowrap>
        <span class=maintext style="<?php echo $style?>">
          <?php echo format_pro_type_name($tmp_arr[1])?>
        </span>
      </td>
      <td bgcolor="<?php echo $bgcolor;?>" align=left colspan="2" width="80%">
            <a href="javascript: popwin('<?php echo $file;?>','700','700','new');" class=button>
              <span class=maintext>
                <?php echo $single_val['Name']?>
              </span> 
            </a>
  <?php 
          if($AUTH->Delete && $used_pro_arr[$single_val['ID']] == $AccessUserID){
  ?> 
            <a href="javascript:delete_sample_link('<?php echo $used_pro_link_arr[$single_val['ID']];?>','<?php echo $single_val['Name']?>');">
              <span class=maintext>
                <img border="0" src="images/icon_purge.gif" alt="Delete sample protocol">
              </span> 
            </a>
  <?php 
          }
  ?>  
      </td>
    </tr>  
  <?php       
        }
        continue;
      }  
  ?>
    <tr>
      <td bgcolor="<?php echo  $bgcolor;?>" align=<?php echo $td_align?> colspan="2" nowrap><span class=maintext style="<?php echo $style?>"><?php echo format_pro_type_name($tmp_arr[1])?></span></td>
      <td bgcolor="<?php echo $bgcolor;?>" align=left colspan="2" width="80%">
        <span class=maintext>
          <select name=frm_ProType_<?php echo ++$pro_type_counter;?>>
          <?php //if((!$selected_flag && $SCRIPT_NAME == 'pop_note.php') || $SCRIPT_NAME != 'pop_note.php'){?> 
            <option value='<?php echo "___".$type_key?>' selected>
          <?php //}
          foreach($single_type as $single_val){
          ?>
            <option value='<?php echo $single_val['ID']."___".$type_key?>' <?php echo ((array_key_exists($single_val['ID'], $used_pro_arr))?'selected':'')?>><?php echo $single_val['Name']?>
        <?php }?>
          </select>
          <a href="javascript: show_protocol_detail('<?php echo $pro_type_counter;?>');" class=button>[view]</a>
        </span>  
      </td>
    </tr>
  <?php }?>
  <input type=hidden name=pro_type_counter value='<?php echo $pro_type_counter;?>'>
  <input type=hidden name=linked_pro_id value=''>
 </table>     
<script language="javascript">
function show_protocol_detail(Protocol_type){
  var theForm = document.action_form;
  var Protocol_ID = '';
  var frm_Type = '';
  var theaction = '';
<?php for($i=1; $i<=$pro_type_counter; $i++){?>
    if(Protocol_type == <?php echo $i?>){
      Protocol_ID = theForm.frm_ProType_<?php echo $i?>.options[theForm.frm_ProType_<?php echo $i?>.selectedIndex].value;
    }
<?php }?>
  if(Protocol_ID != ''){
    var Pretocol_arr = Protocol_ID.split("___");
    if(Pretocol_arr[0] == ''){
      alert("Please select a protocol");
      return;
    }
    var type_div_id = Pretocol_arr[1];
    var prot_div_id = '<?php echo $AccessProjectID?>_' + Pretocol_arr[0];
    file  = "./protocol_detail_pop.php?modal=this_project&outsite_script=1&selected_type_div_id="+type_div_id+"&selected_prot_div_id="+prot_div_id;
    popwin(file,700,700,'new');
  }
}
function delete_sample_link(user_protocol_id,p_id){
  theForm = document.action_form;
  if(!confirm("Unlink protocol '"+p_id+"'?"))return;
  theForm.linked_pro_id.value = user_protocol_id;
  theForm.theaction.value = 'unlink_sample_protocol';
  theForm.Band_ID.value = '<?php echo $Band_ID?>';
  theForm.submit();
}  
</script>
    <?php 
    return $pro_type_counter;
  }
}

function note_block($note_action,$item_ID,$item_type,$frm_disID){
  global $group_table_name,$lable_display,$mod_BaitDiscussion;
  global $HITSDB,$AUTH,$AccessUserID,$AccessProjectID,$SCRIPT_NAME;
  global $bgcolordark,$bgcolor,$Log;
  global $frm_Note,$frm_NoteType,$PHP_SELF;
  $bgcolordark = '#858585';
  if($SCRIPT_NAME != 'pop_note.php'){
    $lable_align = 'right';
    $bgcolor = '#d2dcff';
  }else{
    $lable_align = 'left';
  }
  $group_table_name = $item_type.'Group';
  $lable_display = ($item_type == 'Band')?'Sample':$item_type;
  if($note_action == "insert" && trim($frm_Note)){
    $SQL = "INSERT INTO $group_table_name SET
            `RecordID`='$item_ID',
            `Note`='".mysqli_escape_string($HITSDB->link, $frm_Note)."',
            `NoteTypeID`='$frm_NoteType',
            `UserID`='$AccessUserID',
            `DateTime`=now()";
    if($ret_id = $HITSDB->insert($SQL)){
      $Desc = $item_type."ID=$item_ID,NoteType=$frm_NoteType";
      $Log->insert($AccessUserID,$item_type.'Group',$ret_id,'insert',$Desc,$AccessProjectID);
    }
  }
  if($note_action == "delete" and $frm_disID and $AUTH->Delete){
    $SQL = "DELETE FROM $group_table_name WHERE ID='$frm_disID' and UserID='$AccessUserID'";
    if($HITSDB->execute($SQL)){
      $Desc = "ID=$frm_disID";
      $Log->insert($AccessUserID,$item_type.'Group',$frm_disID,'delete',$Desc,$AccessProjectID);
    }  
  }
  if($note_action == "update" and $AUTH->Modify and $frm_disID) {
    $SQL = "UPDATE $group_table_name SET
            `Note`='".mysqli_escape_string($HITSDB->link, $frm_Note)."'
            WHERE ID='$frm_disID'
            AND UserID='$AccessUserID'";
    if($HITSDB->execute($SQL)){        
      $Desc = $item_type."ID=$item_type";
      $Log->insert($AccessUserID,$item_type.'Group',$frm_disID,'update',$Desc,$AccessProjectID);
    }  
  }
  $item_note_arr = get_project_noteType_arr($HITSDB,$item_type);
  $exist_type_arr = array();
  $SQL = "SELECT `ID`,
          `RecordID`,
          `Note`,
          `NoteTypeID`,
          `UserID`,
          `DateTime` 
          FROM $group_table_name 
          WHERE RecordID='$item_ID'";
  if($group_table_name == 'BandGroup'){
    $SQL .= " AND Note COLLATE latin1_general_cs NOT LIKE 'SAM_%'";
  }
  $item_Discussions_tmp = $HITSDB->fetchAll($SQL); 
?>
  <input id='frm_disID' type='hidden' name='frm_disID' value='<?php echo $frm_disID;?>'>
  <input id='note_action' type='hidden' name='note_action' value='<?php echo $note_action;?>'>
  <DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: 100%">
<?php 
  if($item_Discussions_tmp){
    $item_Discussions = array();
    foreach($item_Discussions_tmp as $item_Discussions_tmp_val){
      $item_Discussions[$item_Discussions_tmp_val['ID']] = $item_Discussions_tmp_val;
    }
?>
  <table border="0" cellpadding="1" cellspacing="1" width="100%">
<?php if($SCRIPT_NAME == 'pop_note.php'){?>  
    <tr>
       <td width=100% nowrap bgcolor="" colspan="6"><span class='tableheader_black'><b><?php echo $item_type?> Notes</b></span></td>
    </tr>
<?php }?>
    <tr>
       <td width="17%" nowrap bgcolor="<?php echo $bgcolordark;?>"><span class=maintext_bold_white>Notes Type</span></td>
       <td bgcolor="<?php echo $bgcolordark;?>" align=center><span class=maintext_bold_white>Notes</span></td>
       <td width=15% nowrap bgcolor="<?php echo $bgcolordark;?>"><span class=maintext_bold_white>Added By</span></td>
       <td width=10% nowrap bgcolor="<?php echo $bgcolordark;?>"><span class=maintext_bold_white>&nbsp;Added On</span></td>
       <td width=10% nowrap bgcolor="<?php echo $bgcolordark;?>"><span class=maintext_bold_white>&nbsp;Action</span></td>
    </tr>
  <?php 
    $temp_icon = '';
    $tem_initial = '';
    foreach($item_Discussions as $item_Discussions_val){
      if($item_Discussions_val['Note'] == 'SAM_') continue;
      $tmpUser = get_userName($HITSDB, $item_Discussions_val['UserID']);
      $tmpNote = nl2br(htmlspecialchars($item_Discussions_val['Note']));
      $temp_icon = '';
      
      if(isset($item_note_arr[$item_Discussions_val['NoteTypeID']])){
        $tmpNoteType = $item_note_arr[$item_Discussions_val['NoteTypeID']]['Name'];
        $temp_icon = $item_note_arr[$item_Discussions_val['NoteTypeID']]['Icon'];
        $tem_initial = $item_note_arr[$item_Discussions_val['NoteTypeID']]['Initial'];
        array_push($exist_type_arr, $item_Discussions_val['NoteTypeID']);
      }else{
        $tmpNoteType = "Discussion";
      }
      $DateTime_arr = explode(" ", $item_Discussions_val['DateTime']);
      $DateTime = $DateTime_arr[0];  
    ?>
    <tr>
    <?php if(!is_numeric($tem_initial)){?>
        <td bgcolor="<?php echo $bgcolor;?>" nowrap><div class=maintext>
        <?php 
        if($temp_icon){
         echo "<img src=./gel_images/$temp_icon border=0>\n";
        }else{
        
        }
        ?>
        <?php echo $tmpNoteType;?></div>
        </td>
    <?php }else{?>
        <td bgcolor="<?php echo $bgcolor;?>" nowrap><div class=maintext>
          <table border=0 cellpadding="1" cellspacing="1">
            <tr>
              <td class=tdback_star_image><?php echo $tem_initial;?></td>
            </tr>
          </table><?php echo $tmpNoteType;?></div>
        </td>
    <?php }?> 
       <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $tmpNote;?></span></td>
       <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $tmpUser;?></span></td>
       <td bgcolor="<?php echo  $bgcolor;?>"><span class=maintext><?php echo $DateTime;?></span></td>
       <td bgcolor="<?php echo $bgcolor;?>">
        <?php if($AUTH->Delete and $item_Discussions_val['UserID'] == $AccessUserID and !(is_numeric($tem_initial) and $temp_icon=='locked')) {?>
          <a href="javascript:confirm_delete_note(<?php echo $item_Discussions_val['ID'];?>);">
          <img border="0" src="images/icon_purge.gif" alt="Delete"></a>
        <?php }else{?> 
          <img src="images/icon_empty.gif" width=17>
        <?php }
          if($AUTH->Modify and ($item_Discussions_val['UserID'] == $AccessUserID) and !(is_numeric($tem_initial) and $temp_icon=='locked')){
        ?>     
          <a href="javascript:modify_note(<?php echo $item_Discussions_val['ID'];?>);">
          <img border="0" src="images/icon_view.gif" alt="Modify"></a>&nbsp;
        <?php }else{ ?>
          <img src="images/icon_empty.gif" width=17>  
        <?php }?>
       </td>
    </tr>
    <?php 
    }//end for
  ?>
  </table>
  <?php 
  }
  if($note_action == "modify" and $frm_disID){  
  	$mod_BaitDiscussion = $item_Discussions[$frm_disID];
  }
  ?>
  <table border="0" cellpadding="1" cellspacing="1" width="100%">
    <tr>
      <td colspan=2 align=center bgcolor="<?php echo  $bgcolordark;?>"><b><span id='note_header'class=maintext_bold_white><?php echo  isset($mod_BaitDiscussion['ID'])?"Modify":"New";?> <?php echo $lable_display?> Note</span></b></td>
    </tr>
    <tr>
     <td bgcolor="<?php echo  $bgcolor;?>" width="120" align='<?php echo $lable_align?>'><span class=maintext>Notes Type&nbsp;</span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span id='note_type' class='maintext'>
     <?php 
     if(isset($mod_BaitDiscussion['ID'])){
       if($mod_BaitDiscussion['NoteTypeID'] > 0){
        echo $item_note_arr[$mod_BaitDiscussion['NoteTypeID']]['Name'];
       }else{
        echo "Discussion";
       }
     }else{
     ?>
     <select name=frm_NoteType>
        <?php  
        echo "<option value='0'>Discussion\n";
        if($AUTH->Modify && count($item_note_arr)){
          foreach($item_note_arr as $key =>$rd){
            if(!in_array($key, $exist_type_arr)){
              $tmp_initial = $rd['Initial'];
              if(is_numeric($rd['Initial'])){
                if($item_type == 'Band'){
                  $tmp_initial = "VS".$rd['Initial'];
                }else{
                  continue;
                }
              }
              if($rd['Icon'] !== 'locked'){
                echo "<option value='".$key."'>".$rd['Name']." (".$tmp_initial.")\n";
              }
            }
          }
        }
        ?>
      </select>
      </span>
     </td>
    </tr>
   <?php }?>
    <tr>
     <td bgcolor="<?php echo  $bgcolor;?>" valign='top' align='<?php echo $lable_align?>'><span class=maintext>Notes&nbsp;</span></td>
     <td bgcolor="<?php echo $bgcolor;?>"><span class=maintext>
      <textarea id='frm_Note' cols='50' rows='4' name='frm_Note'><?php  echo isset($mod_BaitDiscussion['ID'])?$mod_BaitDiscussion['Note']:"";?></textarea></td>
    </tr>
  </table>
  </DIV>
<script language="javascript">
function modify_note(disID){ 
  queryString = "theaction=modify_note&frm_disID="+disID+"&item_type=<?php echo $item_type?>";
  ajaxPost("<?php echo $PHP_SELF;?>", queryString);
}

function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 4){
    document.getElementById('frm_disID').value = ret_html_arr[1];
    document.getElementById('note_type').innerHTML = ret_html_arr[2];
    document.getElementById('frm_Note').innerHTML = ret_html_arr[3];
    document.getElementById('note_header').innerHTML = 'Modify <?php echo $item_type?> Note';
    document.getElementById('note_action').value = 'update';
<?php if($SCRIPT_NAME == 'pop_note.php'){?>
    document.getElementById('add_update').value = 'Update';
<?php }?>    
    return;
  }else if(ret_html_arr.length == 2){
    var div_id = trimString(ret_html_arr[0]);
    document.getElementById(div_id).innerHTML = ret_html_arr[1];
    return;
  }
}
</script>
<?php 
}
function modify_note_block($frm_disID,$item_type){
  global $HITSDB;
  $group_table = $item_type."Group";
  $SQL = "SELECT G.ID,
                 G.RecordID,
                 G.Note,
                 G.NoteTypeID,
                 N.Name 
                 FROM $group_table G
                 LEFT JOIN NoteType N
                 ON (G.NoteTypeID=N.ID) 
                 WHERE G.ID='$frm_disID'";
  if($tmp_arr = $HITSDB->fetch($SQL)){
    if($tmp_arr['NoteTypeID']){
      $note_type_name = $tmp_arr['Name'];
    }else{
      $note_type_name = 'Discussion';
    }  
    $note_detail = $tmp_arr['Note'];
    echo "@@**@@$frm_disID@@**@@$note_type_name@@**@@$note_detail";
  }
}

function hits_searchEngines($get_update, $projectID, $HITSDB, $theEngine=''){
  $curret_dir = getcwd();
  if(basename($curret_dir) == 'analyst'){
    $path = "../TMP/searchEngines";
  }else{
    $path = "../../TMP/searchEngines";
  }
  if(!_is_dir($path)){
    _mkdir_path($path);
  }
  $file = $path."/P_".$projectID.".txt";
  if(!_is_file($file) || !trim(file_get_contents($file))){
    $protein_tableName_arr = array('Hits','Hits_GeneLevel','TppProtein');
    $SQL = "SELECT `ID` FROM `Bait` WHERE `ProjectID`='$projectID'";
    $tmp_Bait_arr = $HITSDB->fetchAll($SQL);

    $Bait_str = '';
    foreach($tmp_Bait_arr as $Bait_id){
      if($Bait_str) $Bait_str .= ',';
      $Bait_str .= $Bait_id['ID'];  
    }
    $handle = fopen($file, "w");
    if($Bait_str){
      foreach($protein_tableName_arr as $val){
        $SQL = "SHOW TABLES LIKE '$val'";
        $result = mysqli_query($HITSDB->link, $SQL);
        $tableExists = mysqli_num_rows($result) > 0;
        if(!$tableExists) continue;
        $SQL = "SELECT SearchEngine FROM $val
                WHERE BaitID IN ($Bait_str)
                GROUP BY SearchEngine"; 
        $tmp_Engine_arr = $HITSDB->fetchAll($SQL);
        $SearchEngine_arr = array();
        foreach($tmp_Engine_arr as $tmp_Engine_val){
//-------------------------------------------------------------------------------------------------
          if($val == 'TppProtein'){
            $SearchEngine_TMP = $tmp_Engine_val['SearchEngine'];
          }else{
            $SearchEngine_TMP = str_replace("Uploaded", "", $tmp_Engine_val['SearchEngine']);
          }
//-------------------------------------------------------------------------------------------------
          if(!trim($SearchEngine_TMP)) continue;
          if($val == 'TppProtein'){
            $TMP_key = 'TPP_'.$SearchEngine_TMP;
          }elseif($val == 'Hits_GeneLevel'){
            $TMP_key = 'GeneLevel_'.$SearchEngine_TMP;
          }else{
            $TMP_key = $SearchEngine_TMP;
          }
          if(!in_array($TMP_key, $SearchEngine_arr)){
            $SearchEngine_arr[] = $TMP_key;
            fwrite($handle, $TMP_key."\n");
          }
        }
      }
    }
    fclose($handle);
  }
  $lines = file($file);
  if($get_update == 'update' && $theEngine){
    $theEngine_tmp = $theEngine."\n";
    if(!in_array($theEngine_tmp, $lines)){
      $lines[] = $theEngine_tmp;
    }
  }  
  $normal_arr = array();
  $TPP_arr = array();
  $geneLevel_arr = array();
  $iProphet = ''; 
  foreach($lines as $key => $val){
    $tmp_line = trim($val);
    if(!trim($tmp_line)) continue;
    if(strstr($tmp_line, 'TPP_')){
      $TPP_arr[] = $tmp_line;
    }elseif(strstr($tmp_line, 'GeneLevel_')){
      $geneLevel_arr[] = $tmp_line;
    }else{
      $normal_arr[] = $tmp_line;
    }
  }
  sort($normal_arr);
  sort($TPP_arr);
  if($iProphet) $TPP_arr[] = $iProphet;
  sort($geneLevel_arr);
  $lines = array_merge($normal_arr, $TPP_arr,$geneLevel_arr);
  $lines = array_unique($lines);
  $lines_str = implode("\n",$lines);
  $lines_str = trim($lines_str);
  $lines_str .= "\n";
  file_put_contents($file, $lines_str);
  return $lines;
}


function get_project_SearchEngine($all=''){
  global $AccessProjectID,$HITSDB;
  global $Is_geneLevel,$hitType;
  
  if(isset($Is_geneLevel)){
    if($Is_geneLevel){
      $hitType = 'geneLevel';
    }
  }elseif(isset($hitType)){
    if($hitType == 'geneLevel'){
      $Is_geneLevel = 1;
    }
  }
  $searchEngines = hits_searchEngines('get', $AccessProjectID, $HITSDB);
  $SearchEngineConfig_arr = array();
  if($Is_geneLevel){
    foreach($searchEngines as $val){
      if(strstr($val, "GeneLevel_")){
        $SearchEngine_TMP = str_replace("GeneLevel_", "",$val);
        if(!in_array($SearchEngine_TMP, $SearchEngineConfig_arr)){
          $SearchEngineConfig_arr[] = $SearchEngine_TMP;
        }
      }
    }
  }else{
    foreach($searchEngines as $val){
      if(strstr($val, "GeneLevel_")) continue;
      $SearchEngine_TMP = str_replace("TPP_", "",$val);
      if(!in_array($SearchEngine_TMP, $SearchEngineConfig_arr)){
        $SearchEngineConfig_arr[] = $SearchEngine_TMP;
      }
    }
  }
  return $SearchEngineConfig_arr;
}

function get_project_SearchEngine_($all=''){
  global $AccessProjectID,$HITSDB;
  global $Is_geneLevel,$hitType;
  
  if(isset($Is_geneLevel)){
    if($Is_geneLevel){
      $hitType = 'geneLevel';
    }
  }elseif(isset($hitType)){
    if($hitType == 'geneLevel'){
      $Is_geneLevel = 1;
    }
  }
  $searchEngines = hits_searchEngines('get', $AccessProjectID, $HITSDB);  
  $SearchEngineConfig_arr = array();
  if($Is_geneLevel){
    if(in_array("MSPLIT", $searchEngines)){
      $SearchEngineConfig_arr[] = "MSPLIT";
      return $SearchEngineConfig_arr;
    }
  }else{
    foreach($searchEngines as $val){
      $SearchEngine_TMP = str_replace("TPP_", "",$val);
      if(!$all && $SearchEngine_TMP == "MSPLIT"){
        continue;
      }
      if(!in_array($SearchEngine_TMP, $SearchEngineConfig_arr)){
        $SearchEngineConfig_arr[] = $SearchEngine_TMP;
      }
    }
  }
  return $SearchEngineConfig_arr;
}

function get_has_hits_SearchEngine($hits_searchEngines){
  $name_lable_arr = array();
  foreach($hits_searchEngines as $val){
    $name_lable_arr[$val] = 1;
  }
  return $name_lable_arr;
}

function get_project_SearchEngine_for_head($all=''){
  global $AccessProjectID,$HITSDB;
  global $Is_geneLevel,$hitType;
  
  $item_report_header_arr = array();
  if(isset($Is_geneLevel)){
    if($Is_geneLevel){
      $hitType = 'geneLevel';
    }
  }elseif(isset($hitType)){
    if($hitType == 'geneLevel'){
      $Is_geneLevel = 1;
    }
  }
  $searchEngines = hits_searchEngines('get', $AccessProjectID,$HITSDB);
  foreach($searchEngines as $val){
    if(strstr($val, 'GeneLevel')) continue;
    if(strstr($val, 'TPP_')){
      $tmp_engine = str_replace("_", " ", $val);
      $tmp_engine = str_replace("GPM", "XTandem", $val);
      $sub_arr['lable'] = $tmp_engine." Hits";
      $sub_arr['SearchEngine'] = $tmp_engine;      
      $item_report_header_arr[$val] = $sub_arr;
    }else{
      if($val == 'iProphet'){
        $key = "TPP_".$val;
        $sub_arr['lable'] = $val." Hits";
        $sub_arr['SearchEngine'] = "TPP ".$val; 
      }else{
        $key = "normal_".$val;
        $val = str_replace("GPM", "XTandem", $val);
        $sub_arr['lable'] = $val." Hits";
        $sub_arr['SearchEngine'] = $val; 
      }
      $item_report_header_arr[$key] = $sub_arr;
    }
  }
  return $item_report_header_arr;
}

//----------------------------------------------------------------------------
function get_project_SearchEngine_U($all=''){
  global $AccessProjectID,$HITSDB;
  global $Is_geneLevel,$hitType;
  
  if(isset($Is_geneLevel)){
    if($Is_geneLevel){
      $hitType = 'geneLevel';
    }
  }elseif(isset($hitType)){
    if($hitType == 'geneLevel'){
      $Is_geneLevel = 1;
    }
  }
  
  if($all){
    $protein_tableName_arr = array('Hits','Hits_GeneLevel');
  }elseif(!$Is_geneLevel){
    $protein_tableName_arr = array('Hits','TppProtein');
  }else{
    $protein_tableName_arr = array('Hits_GeneLevel');
  }
  $SearchEngineConfig_arr = array();
  $first_Engine = '';
  foreach($protein_tableName_arr as $val){
    $SQL = "SELECT H.SearchEngine FROM $val H
            LEFT JOIN Bait B 
            ON (H.BaitID=B.ID) 
            WHERE B.ProjectID='$AccessProjectID'
            GROUP BY `SearchEngine`";
    $tmp_Engine_arr = $HITSDB->fetchAll($SQL);    
    foreach($tmp_Engine_arr as $tmp_Engine_val){
      $SearchEngine_TMP = str_replace("Uploaded", "", $tmp_Engine_val['SearchEngine']);
      if(!trim($SearchEngine_TMP)) continue;
      if(!in_array($SearchEngine_TMP, $SearchEngineConfig_arr)){
        if($SearchEngine_TMP == "Mascot"){
          $first_Engine = $SearchEngine_TMP;
        }else{
          array_push($SearchEngineConfig_arr, $SearchEngine_TMP);
        }  
      }
    }
  }
  $SearchEngineConfig_arr[] = 'TPP';
  if($first_Engine) array_unshift($SearchEngineConfig_arr, $first_Engine);
  return $SearchEngineConfig_arr;
}

function get_project_SearchEngine_GL(){
  global $AccessProjectID,$HITSDB;
  $SearchEngineConfig_arr = array();
  $first_Engine = '';
  
  $SQL = "SELECT H.SearchEngine FROM Hits_GeneLevel H
          LEFT JOIN Bait B 
          ON (H.BaitID=B.ID) 
          WHERE B.ProjectID='$AccessProjectID'
          GROUP BY `SearchEngine`";
  $tmp_Engine_arr = $HITSDB->fetchAll($SQL);
  foreach($tmp_Engine_arr as $tmp_Engine_val){
    $SearchEngine_TMP = str_replace("Uploaded", "", $tmp_Engine_val['SearchEngine']);
    if(!trim($SearchEngine_TMP)) continue;
    if(!in_array($SearchEngine_TMP, $SearchEngineConfig_arr)){
      if($SearchEngine_TMP == "UmpireQuant"){
        $first_Engine = $SearchEngine_TMP;
      }else{
        array_push($SearchEngineConfig_arr, $SearchEngine_TMP);
      }  
    }
  }
  if($first_Engine) array_unshift($SearchEngineConfig_arr, $first_Engine);
  return $SearchEngineConfig_arr;
}

function get_SearchEngine_lable_arr($SearchEngineConfig_arr){
  $SearchEngine_lable_arr = array();
  foreach($SearchEngineConfig_arr as $key){
    if($key == 'GPM'){
      $val = 'XTandem';
      $TPP_key = "TPP_$key";
      $TPP_val = "TPP $val";
      $GeneLevel_key = "GeneLevel_$key";
      $GeneLevel_val = "GeneLevel $val";
    }else{
      $val = $key;
      $TPP_key = "TPP_$key";
      $TPP_val = "TPP $key";
      $GeneLevel_key = "GeneLevel_$key";
      $GeneLevel_val = "GeneLevel $key";
    }
    $SearchEngine_lable_arr[$key] = $val;
    $SearchEngine_lable_arr[$TPP_key] = $TPP_val;
    $SearchEngine_lable_arr[$GeneLevel_key] = $GeneLevel_val;
  }
  return $SearchEngine_lable_arr;
}

function get_SearchEngineProperty_arr($SearchEngineConfig_arr){
  $SearchEngineProperty_arr = array();
  foreach($SearchEngineConfig_arr as $key){
    $SearchEngineProperty_arr[$key] = array();
    $selected_id_str_key = 'selected_id_str_';
    $SearchEngineProperty_arr[$key][$selected_id_str_key] = '';
    $selected_id_str_key_TPP = 'selected_id_str_TPP_';
    $SearchEngineProperty_arr[$key][$selected_id_str_key_TPP] = '';
    $selected_id_str_key_GeneLevel = 'selected_id_str_GeneLevel_';
    $SearchEngineProperty_arr[$key][$selected_id_str_key_GeneLevel] = '';
    $SearchEngineProperty_arr[$key]['tmp_arr'] = array();
  }
  return $SearchEngineProperty_arr;
}

function get_SearchEngine_for_js_arr($SearchEngineConfig_arr){
  global $Is_geneLevel;
  $SearchEngine_for_js_arr = array();
  foreach($SearchEngineConfig_arr as $key){
    $ttp_key = 'TPP_'.$key;
    $GeneLevel_key = 'GeneLevel_'.$key;
    $SearchEngine_for_js_arr[$key] = 'selected_id_str_'.$key;
    $SearchEngine_for_js_arr[$ttp_key] = 'selected_id_str_TPP_'.$key;
    $SearchEngine_for_js_arr[$GeneLevel_key] = 'selected_id_str_GeneLevel_'.$key;
  }
  return $SearchEngine_for_js_arr;
}

function fill_SearchEngineProperty_arr($selected_id_str,$SearchEngine,$session_Type){//&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
  global $HITSDB;
  global $SearchEngineConfig_arr;
  global $SearchEngineProperty_arr;
  global $tmp_id;
  global $Is_geneLevel;
  //------------------------------------------------------------------------------------------------------------------------------  
  if($selected_id_str){
    if($session_Type == 'Exp'){
      $SQL = "SELECT 
              B.ExpID AS ID,
              H.SearchEngine 
              FROM Hits H, Band B
              WHERE B.ExpID IN ($selected_id_str) AND B.ID=H.BandID";
    }else{
      $SQL = "SELECT 
              $tmp_id AS ID,
              SearchEngine 
              FROM Hits 
              WHERE $tmp_id IN ($selected_id_str)";
    }          
    $tmpItemArr = $HITSDB->fetchAll($SQL);

    foreach($tmpItemArr as $tmpItemVal){
      foreach($SearchEngineConfig_arr as $key){
        if(strstr($tmpItemVal['SearchEngine'], $key)){
          if(!in_array($tmpItemVal['ID'], $SearchEngineProperty_arr[$key]['tmp_arr'])){
            array_push($SearchEngineProperty_arr[$key]['tmp_arr'], $tmpItemVal['ID']);
            if($SearchEngineProperty_arr[$key]['selected_id_str_']) $SearchEngineProperty_arr[$key]['selected_id_str_'] .= ",";
            $SearchEngineProperty_arr[$key]['selected_id_str_'] .= $tmpItemVal['ID'].":C_FFFFFF:";
          }
        }
      }    
    }
    foreach($SearchEngineConfig_arr as $key){
      if(!$SearchEngine && $SearchEngineProperty_arr[$key]['selected_id_str_']) $SearchEngine = $key;
      $SearchEngineProperty_arr[$key]['selected_id_str_'] = $tmp_id."@@".$SearchEngineProperty_arr[$key]['selected_id_str_'];
    }
    
    if($session_Type == 'Exp'){
      $SQL = "SELECT 
              B.ExpID AS ID,
              H.SearchEngine 
              FROM TppProtein H, Band B
              WHERE B.ExpID IN ($selected_id_str) AND B.ID=H.BandID";
    }else{
      $SQL = "SELECT 
              $tmp_id AS ID,
              SearchEngine
              FROM TppProtein  
              WHERE $tmp_id IN ($selected_id_str)";
    }          
    $tmpItemArr = $HITSDB->fetchAll($SQL);
    foreach($SearchEngineConfig_arr as $key){
      $SearchEngineProperty_arr[$key]['tmp_arr'] = array();
    }
    foreach($tmpItemArr as $tmpItemVal){
      foreach($SearchEngineConfig_arr as $key){
        if(strstr($tmpItemVal['SearchEngine'], $key)){
          if(!in_array($tmpItemVal['ID'], $SearchEngineProperty_arr[$key]['tmp_arr'])){
            array_push($SearchEngineProperty_arr[$key]['tmp_arr'], $tmpItemVal['ID']);
            if($SearchEngineProperty_arr[$key]['selected_id_str_TPP_']) $SearchEngineProperty_arr[$key]['selected_id_str_TPP_'] .= ",";
            $SearchEngineProperty_arr[$key]['selected_id_str_TPP_'] .= $tmpItemVal['ID'].":C_FFFFFF:";
          }
        }
      }    
    }
    foreach($SearchEngineConfig_arr as $key){
      if(!$SearchEngine && $SearchEngineProperty_arr[$key]['selected_id_str_TPP_']) $SearchEngine = 'TPP_'.$key;
      $SearchEngineProperty_arr[$key]['selected_id_str_TPP_'] = $tmp_id."@@".$SearchEngineProperty_arr[$key]['selected_id_str_TPP_'];
    }
  }
  return $SearchEngine;
}

function print_SearchEngine_hedden_tag(){
  global $SearchEngineProperty_arr;
  foreach($SearchEngineProperty_arr as $key => $val){
    $tmp_arr = $val;
    foreach($tmp_arr as $key2 => $val2){
      if($key2 == 'tmp_arr') continue;
      $tag_nam = $key2.$key;
  ?>
  <INPUT TYPE="hidden" NAME="<?php echo $tag_nam?>" VALUE="<?php echo $val2?>">
  <?php 
    }
  }
}

function SearchEngine_WHERE_OR($SearchEngine){
  global $SearchEngineConfig_arr;
  $SearchEngine = str_replace("TPP_", "", $SearchEngine);
  $SearchEngine = str_replace("GeneLevel_", "", $SearchEngine);           
  $WHERE = "";       
  foreach($SearchEngineConfig_arr as $val){
    if(strstr($SearchEngine, $val)){
      $uploaded_engine = $val.'Uploaded';        
      $WHERE = " WHERE (SearchEngine='$val' OR SearchEngine='$uploaded_engine') AND ";
    }      
  }
  if(!$WHERE) $WHERE = " WHERE ";
  return $WHERE;
}

function get_all_elements_for_this_project(&$elementID){
  global $HITSDB,$currentType,$AccessProjectID;  
  if($currentType == 'Bait'){
    $tableName = 'Bait';
    $elementID = 'BaitID';
  }elseif($currentType == 'Exp'){
    $tableName = 'Experiment';
    $elementID = 'ExpID'; 
  }elseif($currentType == 'Band'){
    $tableName = 'Band';
    $elementID = 'BandID';
  }else{
    exit;
  }  
  $SQL = "SELECT `ID` FROM $tableName WHERE `ProjectID`='$AccessProjectID'";
  if($tmpArr = $HITSDB->fetchAll($SQL)){
    return $tmpStr = array_to_delimited_str($tmpArr,'ID');
  }else{
    return '';
  }
}

function get_SearchEngine_type($tmpStr,$elementID){
  global $frm_SearchEngine,$displaySearchEngine,$SearchEngine;
  global $SearchEngineConfig_arr;
  $counter = 0;
  $checked = 0;
  
  $name_lable_arr = array();
  foreach($SearchEngineConfig_arr as $val){
    $name_lable_arr[$val] = '';
  }
  foreach($SearchEngineConfig_arr as $val){
    $TPP_val = 'TPP_'.$val;
    $name_lable_arr[$TPP_val] = '';
  }
  foreach($name_lable_arr as $key => $value){
    if($has_hits = have_hits($key,$elementID,$tmpStr)){
      if(!$checked){
        $frm_SearchEngine = $SearchEngine = $key;
        $checked = 1;
      }
      $counter++;
    }
    $name_lable_arr[$key] = $has_hits;
  }  
  if($counter > 1){
    $displaySearchEngine = 1;
  }else{
    $displaySearchEngine = 0;
  }
  return $name_lable_arr;
}

function get_SearchEngine_type_for_U($tmpStr,$elementID){
  global $frm_SearchEngine,$displaySearchEngine,$SearchEngine;
  global $HITSDB;
  $hits_table_arr = array('Hits','TppProtein','Hits_GeneLevel');
  $match_arr = array('Hits'=>'Normal','TppProtein'=>'TPP','Hits_GeneLevel'=>'Gene Level');
  $name_lable_arr = array();
  if($elementID == 'ExpID'){
    $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($tmpStr)";
    $tmpExpArr2 = $HITSDB->fetchAll($SQL);
    $tmpStr = '';
    foreach($tmpExpArr2 as $tmpExpVal2){
      if($tmpStr) $tmpStr .= ",";
      $tmpStr .= $tmpExpVal2['ID'];
    }
    $elementID = 'BandID';
  }
  foreach($hits_table_arr as $hitsTable){
    $SQL = "SELECT $elementID 
            FROM $hitsTable 
            WHERE $elementID IN($tmpStr) LIMIT 1 ";
    if($tmpArr2 = $HITSDB->fetchAll($SQL)){
      $name_lable_arr[$hitsTable] = $match_arr[$hitsTable];
    }else{
      $name_lable_arr[$hitsTable] = '';
    }
  }
  return $name_lable_arr;
}

function have_hits($SearchEngine,$elementID,$tmpStr){
  global $HITSDB;
  global $Is_geneLevel;
  
  if(!$tmpStr) return 0;
  $hitsTable = 'Hits';
  $pos = strpos($SearchEngine, 'TPP_');
  
  if($pos === 0){
    $hitsTable = 'TppProtein';
  }elseif($Is_geneLevel){
    $hitsTable = 'Hits_GeneLevel';
  }

  if($elementID == 'ExpID'){
    $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID` IN ($tmpStr)";
    $tmpExpArr2 = $HITSDB->fetchAll($SQL);
    $tmpStr = '';
    foreach($tmpExpArr2 as $tmpExpVal2){
      if($tmpStr) $tmpStr .= ",";
      $tmpStr .= $tmpExpVal2['ID'];
    }
    $elementID = 'BandID';
  }
  $SQL = "SELECT $elementID 
          FROM $hitsTable ";
          
  $WHERE = SearchEngine_WHERE_OR($SearchEngine);  
  $WHERE .= " $elementID IN($tmpStr) LIMIT 1 ";
  $SQL .= $WHERE;
  if($tmpArr2 = $HITSDB->fetchAll($SQL)){
    return 1;
  }else{
    return 0;
  }
}

function search_item_name($frm_search_by,$currentType,$tmpElementIdStr){  
  global $HITSDB;
  $tmp_gene_arr = array();
  $tmpElementIdArr = array();   
  if($currentType == 'Bait'){
    $item_name = "GeneName";
    $item_type ='Bait';
  }elseif($currentType == 'Exp'){
    $item_name = "Name";
    $item_type ='Experiment';
    $SQL = "SELECT E.ID,
            B.GeneName 
            FROM Experiment E
            LEFT JOIN Bait B 
            ON(B.ID=E.BaitID)
            WHERE E.ID IN($tmpElementIdStr)";
    $tmp_gene_arr = $HITSDB->fetchAll($SQL);
  }elseif($currentType == 'Band'){
    $item_name = "Location";
    $item_type ='Band';
    $SQL = "SELECT N.ID,
            B.GeneName 
            FROM Band N 
            LEFT JOIN Bait B 
            ON(B.ID=N.BaitID)
            WHERE N.ID IN($tmpElementIdStr)";
    $tmp_gene_arr = $HITSDB->fetchAll($SQL);
  }    
  $SQL = "SELECT ID, $item_name FROM $item_type WHERE `ID` IN($tmpElementIdStr)";
  $tmp_arr = $HITSDB->fetchAll($SQL);
  $tmpElementIdStr = '';
  foreach($tmp_arr as $tmp_val){
    $pos = strpos(strtoupper($tmp_val[$item_name]), strtoupper($frm_search_by));
    if($pos !== false){
      if(!in_array(!$tmp_val['ID'], $tmpElementIdArr)){
        array_push($tmpElementIdArr, $tmp_val['ID']);
      }  
    }
  } 
  foreach($tmp_gene_arr as $tmp_gene_val){
    $pos = strpos(strtoupper($tmp_gene_val['GeneName']), strtoupper($frm_search_by));
    if($pos !== false){ 
      if(!in_array($tmp_gene_val['ID'], $tmpElementIdArr)){
        array_push($tmpElementIdArr, $tmp_gene_val['ID']);
      }  
    }
  } 
  $tmpElementIdStr = implode(",", $tmpElementIdArr);
  return $tmpElementIdStr;
}

function escapeSpace($inStr){
  return str_replace(' ', '&nbsp;', $inStr);
} 

function get_max_min_value(&$frm_max, &$frm_min){
  global $elementsValue, $frm_order_by, $currentType;
  $comparedValue = '';
  if(!is_numeric($frm_order_by)){
    if($currentType == 'Bait'){
      if($frm_order_by == 'ID'){
        $comparedValue = $elementsValue['ID'];
      }elseif($frm_order_by == 'GeneName'){
        $comparedValue = $elementsValue['GeneName'];
      }elseif($frm_order_by == 'BaitAcc'){
        $comparedValue = $elementsValue['BaitAcc'];
      }
    }elseif($currentType == 'Band'){
      if($frm_order_by == 'D.ID'){
          $comparedValue = $elementsValue['ID'];
      }elseif($frm_order_by == 'D.BaitID'){
          $comparedValue = $elementsValue['D.BaitID'];
      }elseif($frm_order_by == 'D.Location'){
          $comparedValue = $elementsValue['Location'];
      }elseif($frm_order_by == 'B.GeneName'){
          $comparedValue = $elementsValue['GeneName'];
      }elseif($frm_order_by == 'L.GelID'){
          $comparedValue = $elementsValue['GelID'];
      }
    }  
  }else{
  
  }
  if($comparedValue > $frm_max){
      $frm_max = $comparedValue;
  }elseif($comparedValue < $$frm_min){
      $frm_min = $comparedValue;
  } 
}

function create_page_lable($totalElements){
  global $elementsPerPage,$currentPage;
  if($totalElements%$elementsPerPage){                     
    $totalPages = intval($totalElements/$elementsPerPage) + 1;
  }else{
    $totalPages = intval($totalElements/$elementsPerPage);
  }
  //$pageLable = "<font FACE='verdana,Arial, Helvetica' size=2>Total Baits : $totalElements (TotalPages: $totalPages)</font>";
  $pageLable = '';
  for($i=1; $i<=$totalPages; $i++){
    $fontColor = '';
    if($currentPage == $i) $fontColor = "color='red'";
    $pageLable .= "<A href=\"javascript: startRequest('changePage','$i');\"><font $fontColor FACE='verdana,Arial, Helvetica' size=2>$i</FONT></A>&nbsp;&nbsp;";
  }
  if($totalPages > 1){
    return $pageLable."<br><br>";
  }else{
    return '<br><br>';
  }  
}

function get_tages(&$has_notes_itemID_arr,&$group_icon_arr,$itemType,$currentType){
  global $HITSDB;
  $tagesStr = '';
  
  $table_name = $itemType."Group";
  foreach($group_icon_arr as $key => $value){
    if($tagesStr) $tagesStr .= ',';
    $tagesStr .= $key;
  }
    
  if($tagesStr){
    $SQL = "SELECT RecordID, NoteTypeID FROM $table_name WHERE NoteTypeID IN($tagesStr) order by RecordID , NoteTypeID";
    if($BaitDiscussion2 = $HITSDB->fetchAll($SQL)){
      $hasTage = 1;
      foreach($BaitDiscussion2 as $theNotes){      
        if(!isset($has_notes_itemID_arr[$theNotes['RecordID']])){
          $has_notes_itemID_arr[$theNotes['RecordID']] = array();
        }
        array_push($has_notes_itemID_arr[$theNotes['RecordID']], $theNotes['NoteTypeID']);
      }
    }
    if(count($has_notes_itemID_arr)){
      if($currentType == 'Band'){
        if($itemType == 'Bait' || $itemType == 'Experiment'){
          $tmp_arr = $has_notes_itemID_arr;
          $has_notes_itemID_arr = array();        
          foreach($tmp_arr as $tmp_key => $tmp_val){
            if($itemType == 'Bait'){
              $SQL = "SELECT `ID` FROM `Band` WHERE `BaitID`='$tmp_key'";
            }elseif($itemType == 'Experiment'){
              $SQL = "SELECT `ID` FROM `Band` WHERE `ExpID`='$tmp_key'";
            }
            if($tmp_band_arr = $HITSDB->fetchAll($SQL)){
              foreach($tmp_band_arr as $tem_band_val){
                if(!array_key_exists($tem_band_val['ID'], $has_notes_itemID_arr)){
                  $has_notes_itemID_arr[$tem_band_val['ID']] = array();
                }
                foreach($tmp_val as $tmp_val2){
                  if(!in_array($tmp_val2, $has_notes_itemID_arr[$tem_band_val['ID']])){
                    array_push($has_notes_itemID_arr[$tem_band_val['ID']], $tmp_val2);
                  }
                }
              }
            }
          }
        }
      }elseif($currentType == 'Exp'){
        if($itemType == 'Bait'){
          $tmp_arr = $has_notes_itemID_arr;
          $has_notes_itemID_arr = array();        
          foreach($tmp_arr as $tmp_key => $tmp_val){
            $SQL = "SELECT `ID` FROM `Experiment` WHERE `BaitID`='$tmp_key'";
            if($tmp_band_arr = $HITSDB->fetchAll($SQL)){
              foreach($tmp_band_arr as $tem_band_val){
                if(!array_key_exists($tem_band_val['ID'], $has_notes_itemID_arr)){
                  $has_notes_itemID_arr[$tem_band_val['ID']] = array();
                }
                foreach($tmp_val as $tmp_val2){
                  if(!in_array($tmp_val2, $has_notes_itemID_arr[$tem_band_val['ID']])){
                    array_push($has_notes_itemID_arr[$tem_band_val['ID']], $tmp_val2);
                  }
                }
              }
            }
          }
        }
      }
    }
    if(count($has_notes_itemID_arr)){
      return 1;
    }  
  }
  return 0;
}

function get_source_elements_arr(&$elementsArr,$tmpStr2,$startPoint,$len){
  global $HITSDB,$currentType,$frm_order_by,$frm_user;
  $max_mim_arr = array();
  if(!$tmpStr2) return $max_mim_arr['max'] = $max_mim_arr['min'] = 0;
  $tmpName = '';  
  if($currentType == 'Bait'){
    $SQL = "SELECT `ID`,`GeneName`, `BaitAcc`, `Tag`, `Mutation`";
    $FROM =" FROM `Bait` 
             WHERE `ID` IN($tmpStr2) ";
    if($frm_order_by == 'GeneName' or $frm_order_by == 'BaitAcc'){
      $ORDER_BY = "ORDER BY $frm_order_by";
      $tmpName = $frm_order_by;
    }else{
      $ORDER_BY = "ORDER BY ID DESC";
      $tmpName = 'ID';
    }
  }elseif($currentType == 'Exp'){
    $SQL = "SELECT E.ID, E.BaitID, E.Name, B.GeneName, B.Tag, B.Mutation";
    $FROM ="  FROM Experiment E
              LEFT JOIN Bait B ON E.BaitID = B.ID
              WHERE E.ID IN($tmpStr2) ";
    if($frm_order_by == 'B.GeneName' or $frm_order_by == 'E.Name'){
      $ORDER_BY = "ORDER BY $frm_order_by";
      preg_match('/\.(\w+)$/', $frm_order_by, $matches);
      $tmpName = $matches[1];
    }elseif($frm_order_by == 'E.BaitID'){
      $ORDER_BY = "ORDER BY $frm_order_by DESC";
      $tmpName = 'BaitID';
    }else{
      $ORDER_BY = "ORDER BY E.ID DESC";
      $tmpName = 'ID';
    }
  }elseif($currentType == 'Band'){ 
    $SQL = "SELECT D.ID, D.BaitID, D.Location, B.GeneName, B.Tag, B.Mutation, L.GelID, L.LaneNum";
    $FROM ="  FROM Band D
              LEFT JOIN Bait B ON D.BaitID = B.ID
              LEFT JOIN Lane L ON D.LaneID = L.ID
              WHERE D.ID IN($tmpStr2) ";
    if($frm_order_by == 'B.GeneName' or $frm_order_by == 'D.Location'){
      $ORDER_BY = "ORDER BY $frm_order_by";
      preg_match('/\.(\w+)$/', $frm_order_by, $matches);
      $tmpName = $matches[1];
    }elseif($frm_order_by == 'D.BaitID'){
      $ORDER_BY = "ORDER BY $frm_order_by DESC";
      $tmpName = 'BaitID';
    }elseif($frm_order_by == 'L.GelID'){
      $ORDER_BY= "ORDER BY $frm_order_by DESC, L.LaneNum ASC";
      $tmpName = 'GelID';
    }else{
      $ORDER_BY = "ORDER BY D.ID DESC";
      $tmpName = 'ID';
    }
  }
  $LIMIT = " LIMIT $startPoint,$len";
  $SQL .= $FROM.$ORDER_BY.$LIMIT;
  $elementsArr = $HITSDB->fetchAll($SQL);
  $max_mim_arr['max'] = $max_mim_arr['min'] = (isset($elementsArr[0][$tmpName]))?trim($elementsArr[0][$tmpName]):"";
  foreach($elementsArr as $value){
		$value[$tmpName] = trim($value[$tmpName]);
    if($value[$tmpName] > $max_mim_arr['max']){
      $max_mim_arr['max'] = $value[$tmpName];
    }elseif($value[$tmpName] < $max_mim_arr['min']){
      $max_mim_arr['min'] = $value[$tmpName];
    }
  }
  return $max_mim_arr;
}

//-return real elements(bait, experiment or band) (GPM, Mascot,orOther) (owner or all owner)for this project.
function get_real_elements_for_this_project(&$tmpElementIdArr, $isTest='', $SearchEngine=''){
  global $HITSDB, $currentType, $AccessProjectID,$frm_user;
  global $Is_geneLevel;
  global $for_frequencyU;
  if($currentType == 'Bait'){
    $tableName = 'Bait';
    $elementID = 'BaitID';
  }elseif($currentType == 'Band' || $currentType == 'Exp'){
    $tableName = 'Band';
    $elementID = 'BandID';
  }else{
    exit;
  }
  $OwnerID_str = ($frm_user)?" AND OwnerID=$frm_user ":"";
  
  $SQL = "SELECT `ID` FROM $tableName WHERE `ProjectID`='$AccessProjectID' $OwnerID_str";  //.$subSQL;
  if($tmpArr = $HITSDB->fetchAll($SQL)){
    $tmpStr = array_to_delimited_str($tmpArr,'ID'); //-----Bait or Band id str.
    unset($tmpArr);
    $hitsTable = 'Hits';
    if(strstr($SearchEngine, 'TPP_')){
      $hitsTable = 'TppProtein';
    }elseif($Is_geneLevel){
      $hitsTable = 'Hits_GeneLevel';
    }    
    $SQL = "SELECT $elementID 
            FROM $hitsTable ";
    if(isset($for_frequencyU) && $for_frequencyU){
      $WHERE = ' WHERE ';
    }else{
      $WHERE = SearchEngine_WHERE_OR($SearchEngine);
    }
    $WHERE .= "$elementID IN($tmpStr)  ";
    $GROUP = "GROUP BY $elementID ORDER BY BaitID";
    $SQL .= $WHERE . $GROUP;    
    if($tmpArr2 = $HITSDB->fetchAll($SQL)){
      if($currentType == 'Exp'){
        $band_id_str = '';
        foreach($tmpArr2 as $tmpVal2){
          if($band_id_str) $band_id_str .= ',';
          $band_id_str .= $tmpVal2['BandID'];
        }
        $SQL = "SELECT `ExpID` FROM `Band` WHERE `ID` IN($band_id_str) GROUP BY `ExpID` ORDER BY `BaitID`";
        $tmpArr2 = $HITSDB->fetchAll($SQL);
        $elementID = 'ExpID';
        if(!$tmpArr2) return '';
      }
      if(!$isTest){
        $tmpElementIdStr = array_and_delimited_str($tmpArr2,$elementID,$tmpElementIdArr);
        return $tmpElementIdStr;
      }else{
        return 'not empty';
      }  
    }else{
      return 'no_hits';
    }  
  }else{
    return 'no_item';
  }  
}
                      
function searchEngineWhere($SearchEngine,$S_R_init){
  global $SearchEngineConfig_arr;
  $SearchEngine = str_replace("TPP_", "", $SearchEngine);
  $WHERE = "";        
  foreach($SearchEngineConfig_arr as $val){
    if(strstr($SearchEngine, $val)){
      $uploaded_engine = $val.'Uploaded';
      $WHERE = " AND ($S_R_init.SearchEngine='$val' OR $S_R_init.SearchEngine='$uploaded_engine') ";
    }      
  }
  return $WHERE;
} 

function export_raw_file($file){
  if(file_exists($file)){
    $out_fileName = str_replace(" ", "_", $file);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($out_fileName));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_end_clean(); 
    while(ob_get_level()){ 
      ob_end_clean(); 
    } 
    flush(); 
    $fp = fopen($file,'r'); 
    while($data = fgets($fp)){ 
      echo $data; 
    }
    return $file;
  }
  return 0;
} 

function exist_hits_table($DB_name){
  global $HITSDB;
  $exist_Hits_tables_arr = array();
  $SQL = "SHOW TABLES FROM $DB_name";
  $res = mysqli_query($HITSDB->link, $SQL);
  if($res){
    while($row = $res->fetch_row()){
      if($row[0] == 'Hits' && hasHits_inAccessProject('Hits')){
        $exist_Hits_tables_arr[$row[0]] = '';
      }elseif($row[0] == 'TppProtein' && hasHits_inAccessProject('TppProtein')){
        $exist_Hits_tables_arr[$row[0]] = 'TPP';
      }elseif($row[0] == 'Hits_GeneLevel'){
        if(hasHits_inAccessProject('Hits_GeneLevel')){
          $exist_Hits_tables_arr[$row[0]] = 'geneLevel';
        }
      }
    }
  }
  return $exist_Hits_tables_arr;
}

function hasHits_inAccessProject($hits_table){
  global $AccessProjectID;
  global $HITSDB;
  $hits_searchEngines = hits_searchEngines('get', $AccessProjectID,$HITSDB);
  $hits_table_arr = array();
  foreach($hits_searchEngines as $hits_searchE){
    if(preg_match('/^(TPP)_/i', $hits_searchE, $matches)){
      if(!array_key_exists($matches[1], $hits_table_arr)){
        $hits_table_arr[$matches[1]] = 'TppProtein';
      }
    }elseif(preg_match('/^(GeneLevel)_/i', $hits_searchE, $matches)){
      if(!array_key_exists($matches[1], $hits_table_arr)){
        $hits_table_arr[$matches[1]] = 'Hits_GeneLevel';
      }
    }else{
      if(!array_key_exists('Normal', $hits_table_arr)){
        $hits_table_arr['Normal'] = 'Hits';
      }
    }
  }
  if(in_array($hits_table, $hits_table_arr)){
    return true;
  }else{
    return false;
  }
}

function get_idedBaitsArr_($tmpBaitsIDstr){  
  global $exist_Hits_tables_arr;
  global $HITSDB;
  $idedBaitsArr = array();
  if(!$tmpBaitsIDstr) return $idedBaitsArr;
  foreach($exist_Hits_tables_arr as $key => $val){
    $SQL = "SELECT B.ID FROM Bait B, $key H 
            WHERE B.ID IN($tmpBaitsIDstr) 
            AND H.BaitID=B.ID AND H.GeneID=B.GeneID 
            GROUP BY B.ID";
    $tmpBaitsArr = $HITSDB->fetchAll($SQL);
    foreach($tmpBaitsArr as $value){
      if(!in_array($value['ID'], $idedBaitsArr)){
        array_push($idedBaitsArr, $value['ID']);
      }  
    }
  }
  return $idedBaitsArr;
}

function get_idedItemsArr($tmpItemIDstr,$itemTable){
  global $exist_Hits_tables_arr;
  global $HITSDB;
  $idedItemsArr = array();
  if(!$tmpItemIDstr || !$itemTable) return $idedItemsArr;
  if($itemTable == "Experiment"){
    foreach($exist_Hits_tables_arr as $key => $val){
      $SQL = "SELECT E.ID 
              FROM Experiment E, Bait B, $key H 
              WHERE E.ID IN($tmpItemIDstr)
              AND B.ID=E.BaitID 
              AND E.BaitID=H.BaitID
              AND H.GeneID=B.GeneID 
              GROUP BY E.ID";
      $tmp_exp_arr = $HITSDB->fetchAll($SQL);
      foreach($tmp_exp_arr as $value){
        if(!in_array($value['ID'], $idedItemsArr)){
          array_push($idedItemsArr, $value['ID']);
        }  
      }
    }
  }elseif($itemTable == "Band"){
    foreach($exist_Hits_tables_arr as $key => $val){
      $SQL = "SELECT S.ID 
              FROM Band S, Bait B, $key H 
              WHERE S.ID IN($tmpItemIDstr) 
              AND H.BandID=S.ID
              AND B.ID=S.BaitID 
              AND H.GeneID=B.GeneID 
              GROUP BY S.ID";
      $tmpBaitsArr = $HITSDB->fetchAll($SQL);
      foreach($tmpBaitsArr as $value){
        if(!in_array($value['ID'], $idedItemsArr)){
          array_push($idedItemsArr, $value['ID']);
        }  
      }
    }
  }else{
    if(!$tmpItemIDstr) return $idedItemsArr;
    foreach($exist_Hits_tables_arr as $key => $val){
      $SQL = "SELECT B.ID 
              FROM Bait B, $key H 
              WHERE B.ID IN($tmpItemIDstr) 
              AND H.BaitID=B.ID 
              AND H.GeneID=B.GeneID 
              GROUP BY B.ID";
      $tmpBaitsArr = $HITSDB->fetchAll($SQL);
      foreach($tmpBaitsArr as $value){
        if(!in_array($value['ID'], $idedItemsArr)){
          array_push($idedItemsArr, $value['ID']);
        }  
      }
    }
  }
  return $idedItemsArr;
}
 
function get_gi_acc_arr($hits_result,$hitType,$proteinDB){
  $gi_acc_arr = array();
  $tmp_gi_arr = array();
  while($hitsValue_tmp = mysqli_fetch_assoc($hits_result)){
    if($hitType == 'TPP'){
      $ACC = $hitsValue_tmp['ProteinAcc'];
      $tmp_RedundantGI = $hitsValue_tmp['INDISTINGUISHABLE_PROTEIN'];
    }else{
      $ACC = $hitsValue_tmp['HitGI'];
      $tmp_RedundantGI = $hitsValue_tmp['RedundantGI'];
    }
    if(is_numeric($ACC)){
      $tmp_gi_arr[] = $ACC;
    }
    $tmp_RedundantGI = str_ireplace("gi|", "", $tmp_RedundantGI);
    $tmp_re_arr = explode(";", $tmp_RedundantGI);
    foreach($tmp_re_arr as $tmp_re_val){
      $tmp_re_val = trim($tmp_re_val);
      if(is_numeric($tmp_re_val)){
        $tmp_gi_arr[] = $tmp_re_val;
      }
    }
  }     
  $tmp_gi_str = implode(',',$tmp_gi_arr);      
  if($tmp_gi_str){
    $SQL = "SELECT `GI`, `Acc`,`Acc_Version` FROM `Protein_Accession` WHERE `GI` IN ($tmp_gi_str)";
    $tmp_Protein_arr = $proteinDB->fetchAll($SQL);
    foreach($tmp_Protein_arr as $tmp_Protein_val){
      if($tmp_Protein_val['Acc_Version']){
        $gi_acc_arr[$tmp_Protein_val['GI']]['Acc_V'] = $tmp_Protein_val['Acc_Version'];
        $gi_acc_arr[$tmp_Protein_val['GI']]['Acc'] = $tmp_Protein_val['Acc'];
      }else{
        $gi_acc_arr[$tmp_Protein_val['GI']]['Acc_V'] = $tmp_Protein_val['GI'];
        $gi_acc_arr[$tmp_Protein_val['GI']]['Acc'] = $tmp_Protein_val['GI'];
      }
    }
  }
  return $gi_acc_arr;
}

function print_Redundant_url($RedundantGI_str,$proteinDB){
  $tmp_RedundantGI = str_ireplace("gi", "", $RedundantGI_str);
  $tmp_RedundantGI = str_ireplace("|", "", $tmp_RedundantGI);
  $GI_array = explode(";", $tmp_RedundantGI);
  for($i=0;$i<count($GI_array);$i++){
    $tmp_gi = trim($GI_array[$i]);
    if($tmp_gi){
      if(is_numeric($tmp_gi)){
        $SQL = "SELECT `Acc_Version` FROM `Protein_Accession` WHERE `GI`='$tmp_gi'";
        $tmp_Protein_arr = $proteinDB->fetch($SQL);
        if($tmp_Protein_arr && $tmp_Protein_arr['Acc_Version']){
          $acc = $tmp_Protein_arr['Acc_Version'];
          echo $acc . get_URL_str($acc) . "<br>";
        }
      }else{
        echo $tmp_gi . get_URL_str($tmp_gi) . "<br>";
      }
    }  
  }
}  

//--This function get OpenFreezer Info and write to OpenFreezer map file---------
function get_Info_from_OpenFreezer_($Vector_str,$Username='',$Password=''){
  global $OF_map_file;
  //global $OpenFreezer;
  //global $mapfileDelimit;
  $Vector_arr = array();
  $Vector_arr_tmp = explode(',',$Vector_str);
  foreach($Vector_arr_tmp as $Vector_arr_val){
	  $Vector_v = trim( $Vector_arr_val);
	  if(!$Vector_v) continue;
    $Vector_arr[strtoupper($Vector_v)] = $Vector_v;
  }
//print_r($Vector_arr);  
  $OF_session_id = '';
  if(isset($_SESSION["OF_session_id"]) && $_SESSION["OF_session_id"]){
    $OF_session_id = $_SESSION["OF_session_id"];
  }
  
  $theaction = 'export';
  //$formaction = 'http://prohits.mshri.on.ca/ProhitsPublished/GIPR/check_post_get.php';
  //$formaction = "http://openfreezer_test.mshri.on.ca/directDB_interface.php";
  $formaction = "http://openfreezer.lunenfeld.ca/directDB_interface.php";
  
  $req = new HTTP_Request($formaction,array('timeout' => 180,'readTimeout' => array(180,0)));
  $req->setMethod(HTTP_REQUEST_METHOD_POST);
  $req->addHeader('Content-Type', 'multipart/form-data');
  $req->addPostData('Username', $Username); 
  $req->addPostData('Password', $Password);  
  $req->addPostData('Vector_str', $Vector_str);
  $req->addPostData('theaction', $theaction);
  
  $result = $req->sendRequest();
  
  $OF_Info = "OF_Info::";
  if(!PEAR::isError($result)){
    $response1 = $req->getResponseBody();
    echo "\n======response from $formaction========\n";
    echo "\$response1=$response1<br>" . "\n";
    echo "\n======end of url open========\n";
  }else{ 
   	//echo $result->getMessage();
    echo $OF_Info.$result->getMessage();
    return false;
  }
//----------------------------------------------------------------------------------------
  $responsed_OF_arr = array();
  if($response1){
    $ret_str = $response1;
    if(preg_match("/@@start@@/", $ret_str, $matches)){
      if(!$handle_OF = fopen($OF_map_file, 'w')){
        echo $OF_Info."Cannot open file ($OF_map_file)";
        return false;
      }
      $request_ID_arr = explode("\n",$ret_str);
      $OF_line = '';
      $start_flag = 0;
      foreach($request_ID_arr as $tmp_val){
        if(trim($tmp_val) == '@@start@@'){
          $start_flag = 1;
          continue;
        }
        if(trim($tmp_val) == '@@end@@'){
          break;
        }
        if($start_flag){
          $vectorID = '';
          $OF_line = trim($tmp_val);
          $tmp_arr2 = explode(",", $OF_line);
          foreach($tmp_arr2 as $tmp_val2){
            $tmp_arr3 = explode("=", $tmp_val2);            
            if((trim($tmp_arr3[0]) == 'VectorID' || $tmp_arr3[0] == 'Cell_lineID') && array_key_exists(strtoupper($tmp_arr3[1]),$Vector_arr)){
              $vectorID = $Vector_arr[strtoupper($tmp_arr3[1])];
              break;
            }
          }
          $OF_line = $vectorID."::".$OF_line;
          fwrite($handle_OF, $OF_line."\r\n");
        }
      }
      echo $OF_Info."OK";      
    }else{
      $ret_str = str_replace("\n", "", $ret_str);
      $ret_str = str_replace("\r", "", $ret_str);
      if(preg_match("/##start##(.+?)##end##/", $ret_str, $matches)){
        echo $OF_Info.$matches[1];
      }else{
        echo "The data returned from OPENFREEZER is not available. The script for the data is being developed on OPENFREEZER site now.";
      }
      if(preg_match('/(Forbidden)/', $ret_str, $matchs)){
        echo $OF_Info."Server access denied";
      }
    }
  }
  return true;
}


//--This function get OpenFreezer Info and write to OpenFreezer map file---------
function get_Info_from_OpenFreezer($Vector_str,$Username='',$Password=''){
  global $OF_map_file;
  //global $OpenFreezer;
  //global $mapfileDelimit;
  $OF_line_key_arr = array('VectorID','Cell_lineID','InsertID','InsertAcc','InsertProteinFasta','EntrezGeneID','GeneName','Species');
  
  $Vector_arr = array();
  $Vector_arr_tmp = explode(',',$Vector_str);
  foreach($Vector_arr_tmp as $Vector_arr_val){
	  $Vector_v = trim( $Vector_arr_val);
	  if(!$Vector_v) continue;
    $Vector_arr[strtoupper($Vector_v)] = $Vector_v;
  }
//print_r($Vector_arr);  
  $OF_session_id = '';
  if(isset($_SESSION["OF_session_id"]) && $_SESSION["OF_session_id"]){
    $OF_session_id = $_SESSION["OF_session_id"];
  }
  
  $theaction = 'export';
  //$formaction = 'http://prohits.mshri.on.ca/ProhitsPublished/GIPR/check_post_get.php';
  //$formaction = "http://openfreezer_test.mshri.on.ca/directDB_interface.php";
  $formaction = "http://openfreezer.lunenfeld.ca/directDB_interface.php";
  
  $req = new HTTP_Request($formaction,array('timeout' => 180,'readTimeout' => array(180,0)));
  $req->setMethod(HTTP_REQUEST_METHOD_POST);
  $req->addHeader('Content-Type', 'multipart/form-data');
  $req->addPostData('Username', $Username); 
  $req->addPostData('Password', $Password);  
  $req->addPostData('Vector_str', $Vector_str);
  $req->addPostData('theaction', $theaction);
  
  $result = $req->sendRequest();
  
  $OF_Info = "OF_Info::";
  if(!PEAR::isError($result)){
    $response1 = $req->getResponseBody();
    //echo "\n======response from $formaction========\n";
    //echo "\$response1=$response1<br>" . "\n";
    //echo "\n======end of url open========\n";
  }else{ 
   	//echo $result->getMessage();
    echo $OF_Info.$result->getMessage();
    return false;
  }
//----------------------------------------------------------------------------------------
  $responsed_OF_arr = array();
  if($response1){
    $ret_str = $response1;
    if(preg_match("/@@start@@/", $ret_str, $matches)){
      if(!$handle_OF = fopen($OF_map_file, 'w')){
        echo $OF_Info."Cannot open file ($OF_map_file)";
        return false;
      }
      $request_ID_arr = explode("\n",$ret_str);
      $OF_line = '';
      $start_flag = 0;
      foreach($request_ID_arr as $tmp_val){
        if(trim($tmp_val) == '@@start@@'){
          $start_flag = 1;
          continue;
        }
        if(trim($tmp_val) == '@@end@@'){
          break;
        }
        if($start_flag){
          $vectorID = '';
          $OF_line = trim($tmp_val);
          $tmp_arr2 = explode(",", $OF_line);
          $tmp_OF_line = '';
          foreach($tmp_arr2 as $tmp_val2){
            $OF_line_unit = trim($tmp_val2);
            if(!trim($OF_line_unit)) continue;
            $tmp_arr3 = explode("=", $OF_line_unit);
            $OF_line_key = trim($tmp_arr3[0]);
            if(!in_array($OF_line_key, $OF_line_key_arr)) continue;
            if($tmp_OF_line) $tmp_OF_line .= ',';
            $tmp_OF_line .= $OF_line_unit;
            if(($OF_line_key == 'VectorID' || $OF_line_key == 'Cell_lineID') && array_key_exists(strtoupper($tmp_arr3[1]),$Vector_arr)){
              $vectorID = $Vector_arr[strtoupper($tmp_arr3[1])];
            }
          }
		      if(!$vectorID) continue;
          $OF_line = $vectorID."::".$tmp_OF_line;
          fwrite($handle_OF, $OF_line."\r\n");
        }
      }
      echo $OF_Info."OK";      
    }else{
      $ret_str = str_replace("\n", "", $ret_str);
      $ret_str = str_replace("\r", "", $ret_str);
      if(preg_match("/##start##(.+?)##end##/", $ret_str, $matches)){
        echo $OF_Info.$matches[1];
      }else{
        echo "The data returned from OPENFREEZER is not available. The script for the data is being developed on OPENFREEZER site now.";
      }
      if(preg_match('/(Forbidden)/', $ret_str, $matchs)){
        echo $OF_Info."Server access denied";
      }
    }
  }
  return true;
}        					                		                                           		                                      		       
?>

 		       