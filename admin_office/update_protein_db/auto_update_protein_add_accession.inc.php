
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
function add_mapping(){
   /* 
  1. if no gene ID it will get it from GI list.
  2. If it is mutiple Acc it will not do nothing.
  3. if gene ID is mutiple it will not update gene ID unless.
  4. Inserting new record will use first gene ID if it is mutiple gene ID.
  5. ENSP only insert new. 
  7. If ENSG is mutiple. no ENSG inserted.
  
  $mapping['Acc']
  $mapping['UniProtID']
  $mapping['EntrezGeneID']
  $mapping['RefSeq']
  $mapping['GI']
  $mapping['TaxID']
  $mapping['EMBL-CDS']
  $mapping['ENSG'] 
  $mapping['ENSP']
 */
  global $mapping;
  global $mainDB;
  global $record_num;
  
  $mapping['RefSeq'] = str_replace(" ","", $mapping['RefSeq']);
  
  $uniprotID_OK = 1;
  if(strpos($mapping['UniProtID'], $mapping['Acc']) === 0){
    $uniprotID_OK = 0;
  }
   
  if(!$mapping['EntrezGeneID'] and $mapping['RefSeq']){
    $RefSeq_str = str_replace(";","','", $mapping['RefSeq']);
    $SQL = "select EntrezGeneID from Protein_Accession Where Acc_Version in ('".$RefSeq_str."')";
    //echo "$SQL\n";
    $results = mysqli_query($mainDB->link, $SQL);
    while($row = mysqli_fetch_row($results)){
      if($row[0]){
        $mapping['EntrezGeneID'] = $row[0];
        break;
      }
    }
  }
  
  $GeneID = '';
  
  $SQL_SET = "UniProtID='".$mapping['UniProtID']."'";
  if($mapping['EntrezGeneID']) {
    if(strpos($mapping['EntrezGeneID'], ";")){
      $GeneID_arr = explode(";", $mapping['EntrezGeneID']);
      $GeneID = $GeneID_arr[0];
      $SQL_SET .= ",EntrezGeneID='".$GeneID."'";
    }else{
      $GeneID = $mapping['EntrezGeneID'];
      $SQL_SET .= ",EntrezGeneID='".$GeneID."'";
    }
  }
  
  if($mapping['RefSeq']){
    $ACC_v_arr = explode(";", $mapping['RefSeq']);
     
  }
  if($mapping['Acc']){
    $ACC_v_arr[] = $mapping['Acc'];
  }
  $mapping['EMBL-CDS'] = str_replace(" ","", $mapping['EMBL-CDS']);
  $EMBL_arr = explode(";", $mapping['EMBL-CDS']);
  $ACC_v_arr = array_merge($ACC_v_arr, $EMBL_arr);
   
  foreach($ACC_v_arr as $acc){
    if($acc == '-') continue;
    if(preg_match('/(.+?)\.\d+$/', $acc, $matches)){
      $acc_insert_part = $acc_part = " Acc_Version='$acc' ";
      if($matches[1]){
        $acc_insert_part = " Acc='".$matches[1]."', Acc_Version='$acc'";
      }     
    }else{
      $acc_insert_part = $acc_part = " Acc='$acc' ";
    }
    $SQL = "update Protein_Accession set ". $SQL_SET . " Where $acc_part and TaxID='".$mapping['TaxID']."'";
    if(!$uniprotID_OK){
      $SQL .= " and (UniProtID is NULL or UniProtID='')";
       
    }
    
    $mainDB->execute($SQL);
    preg_match_all ('/(\S[^:]+): (\d+)/', mysqli_info ($mainDB->link), $matches);
    $info_SQL = array_combine ($matches[1], $matches[2]);
    $matched_rows = $info_SQL['Rows matched'];
    $record_num += $matched_rows;
    if(!$matched_rows){
      $SQL = "select ID from Protein_Accession Where $acc_part and TaxID='".$mapping['TaxID']."'";
      if(!$mainDB->fetch($SQL)){
        $SQL = "insert into Protein_Accession set ". $SQL_SET ." ,$acc_insert_part, TaxID='".$mapping['TaxID']."'";
        //echo "$SQL\n";
        $mainDB->insert($SQL);
        $record_num +=1;
        _write_log($SQL);
     }
    }
  }
  
  if($GeneID and $mapping['ENSG'] and $mapping['ENSP']){
    $ENSP_arr = explode(";", $mapping['ENSP']);
    foreach($ENSP_arr as $theENSP){
      $theENSP = trim($theENSP);
      
      $SQL = "UPDATE Protein_AccessionENS 
              SET EntrezGeneID='".$GeneID."' 
              WHERE ENSP='".$theENSP."'
              AND ENSG='".$mapping['ENSG']."'";
      //echo "$SQL\n";
      $mainDB->execute($SQL);
      preg_match_all ('/(\S[^:]+): (\d+)/', mysqli_info ($mainDB->link), $matches);
      $info_SQL = array_combine ($matches[1], $matches[2]);
      $matched_rows = $info_SQL['Rows matched'];
      $record_num += $matched_rows;
      if(!$matched_rows){
        $SQL = "INSERT INTO Protein_AccessionENS SET 
                EntrezGeneID='".$GeneID."',  
                ENSP='".$theENSP."',
                ENSG='".$mapping['ENSG']."'";
        $mainDB->insert($SQL);
        $record_num +=1;
        _write_log($SQL);
      }
    }
  }   
  $mapping['Acc'] = '';
  $mapping['UniProtID'] = '';
  $mapping['EntrezGeneID'] = '';
  $mapping['RefSeq'] = '';
  $mapping['GI'] = '';
  $mapping['TaxID'] = '';
  $mapping['ENSG'] = '';
  $mapping['ENSP'] = ''; 
   
}

function add_uniprot(){
  /*
  $uniRecord['ID'] ;
  $uniRecord['ACC_str'] 
  $uniRecord['GeneName']  
  $uniRecord['TaxID']= $matches[1]; 
  $uniRecord['RefSeq_arr']= $matches[1]; 
  $uniRecord['GeneID']= $matches[1];
 */
  global $uniRecord;
  global $mainDB;
  global $record_num;
  
  if($uniRecord['ID']){    
    $SQL_SET = "UniProtID='".$uniRecord['ID']."'";
    $uniRecord['ACC_str'] = preg_replace("/;$/", '', $uniRecord['ACC_str']);
    $uniRecord['ACC_str'] = str_replace(" ", "", $uniRecord['ACC_str']);
    $Acc_arr = explode(";", $uniRecord['ACC_str']);
     
    $SQL = "select EntrezGeneID, UniProtID, Description from Protein_Accession Where Acc='". $Acc_arr[0]."'";    
     
    $results = mysqli_query($mainDB->link, $SQL);
    
    if($row = mysqli_fetch_row($results)){
       
      if($row[0] and $row[1] and $row[2]){
        $uniRecord['ID'] = '';
        $uniRecord['ACC_str'] = '';
        $uniRecord['GeneName'] = ''; 
        $uniRecord['TaxID']= ''; 
        $uniRecord['RefSeq_arr']= array();
        $uniRecord['GeneID']= '';
        $uniRecord['Description'] = '';
        return;
      }
    
      if(!$row[0]){
         if($uniRecord['GeneID']) $SQL_SET .= ",EntrezGeneID='".$uniRecord['GeneID']."'";
      }
      if(!$row[2]){
         if($uniRecord['Description']) $SQL_SET .= ",Description='".mysqli_real_escape_string($mainDB->link, $uniRecord['Description'])."'";
      }
      $Acc_arr = array_merge($Acc_arr, $uniRecord['RefSeq_arr']);
      foreach($Acc_arr as $value){
        $SQL = "update Protein_Accession set ". $SQL_SET . " Where Acc='". $value."'";

        if($ok = $mainDB->update($SQL)){
         $record_num +=$ok;
        } 
      }
    }else{
      if(!$uniRecord['GeneID'] and $uniRecord['GeneName'] and $uniRecord['TaxID']){
        $sql_gene = "select EntrezGeneID from Protein_Class where GeneName='".$uniRecord['GeneName']."' and TaxID='".$uniRecord['TaxID']."'";
        $results = mysqli_query($mainDB->link, $sql_gene);
        if($row = mysqli_fetch_row($results)){
          $uniRecord['GeneID'] = $row[0];
        }
      }
      if($uniRecord['GeneID']){
        $SQL_SET .= ",EntrezGeneID='".$uniRecord['GeneID']."'";
        if($uniRecord['Description']) $SQL_SET .= ",Description='".mysqli_real_escape_string($mainDB->link, $uniRecord['Description'])."'";
        if($uniRecord['TaxID']) $SQL_SET .= ", TaxID='".$uniRecord['TaxID']."'";
        
        $SQL = "insert into Protein_Accession set Acc='".$Acc_arr[0]."', Acc_version='".trim($Acc_arr[0])."', Source='".$uniRecord['Source']."', ". $SQL_SET;
         
        _write_log($SQL);
        $mainDB->insert($SQL);
        $record_num +=1;
      }//don't add no gene ID proteins
    }
  }
  
  $uniRecord['ID'] = '';
  $uniRecord['ACC_str'] = '';
  $uniRecord['GeneName'] = ''; 
  $uniRecord['TaxID']= ''; 
  $uniRecord['RefSeq_arr']= array();
  $uniRecord['GeneID']= '';
  $uniRecord['Description'] = '';
}
//------------------------------------
function _fatal_error($error){
//------------------------------------
    global $admin_email;
    global $progressing_flag;
    $error = "database updating stopped: " .$error;
    _write_log($error);
    $server_info = "";
    foreach($_SERVER as $key=>$value){
        $server_info .="\r\n$key=$value";
    }
    echo "<font size=+2><b>ERROR:</b></font> $error";
    $error .= "\r\nProhits server information::::::::::::\r\n" . $server_info;
    @mail($admin_email, "protein update error", $error, "From: ProhitsAdmin\r\n"."Reply-To: $ProhitsAdmin\r\n");
    echo $error;
    unlink($progressing_flag);
    exit;
}

//------------------------------------- 
function _write_log($msg){
//-------------------------------------
    global $download_log;
    global $fp_log;
    if(!$fp_log)  $fp_log = fopen($download_log, "a+");
    fwrite($fp_log, "\r\n$msg");
}
//----------------------------------
function print_dot($line_num){
//----------------------------------
    /*if($line_num%900 === 0){
        echo '.';
        if($line_num%4000 === 0)  echo "$line_num\n";
        flush();
    }*/
    if($line_num%100 === 0){
        echo '.';
        if($line_num%6000 === 0)  echo "$line_num\n";
        flush();
    }
    
}
//-----------------------
function add_sequence(){
//-----------------------
   global $tableName;
   global $tableName2;
   global $db_link;
   global $update_protein_desc;
   
   global $GI_arr, $Sequence;
   $sequence_ID = "";
   $GI_no_sq_arr = array();
   $Tax_to_Gene_arr = array();
   $SQL = "select ID, EntrezGeneID, SequenceID from $tableName where GI=";
    
      //echo $SQL."\n";
  $results = mysqli_query($db_link, $SQL . $GI_arr[0][1]);
  if($row = mysqli_fetch_row($results)){
     if($row[2]) {
        //old GI protien -- this group has sequence id already
        return;
     }
  }else{
    return;
  }
  $SQL = "insert into $tableName2 set Sequence='$Sequence'";
  if(!mysqli_query($db_link, $SQL) ){
      _fatal_error(mysqli_error($db_link)); 
  }else{
      $sequence_ID = mysqli_insert_id($db_link);
  }
   $SQL = "update $tableName set SequenceID='". $sequence_ID. "', Source=";
  
  for($i = 0; $i<count($GI_arr); $i++){
    $tmp_gi = $GI_arr[$i][1];
    if($update_protein_desc){
      $tmp_sql = $SQL . "'". $GI_arr[$i][2]. "', Description='".mysqli_real_escape_string($db_link, $GI_arr[$i][4])."'  where GI=" . $tmp_gi;
    }else{
      $tmp_sql = $SQL . "'". $GI_arr[$i][2]. "' where GI=" . $tmp_gi;
    }
    if(!mysqli_query($db_link, $tmp_sql)){
         _fatal_error($tmp_sql.":". mysqli_error($db_link)); 
    }
     //echo $tmp_sql."\n";
  }
}
//------------------------------------------------
function update_sequence($Sequence, $all_acc_arr, $Description = ''){
//====================================================================
global $fp_log_tmp;
//==================================================================== 
//------------------------------------------------
  //$all_acc_arr = array(
  //  'IPI'=>array('IPI00187591'),
  //  'REFSEQ'=>Array('XP_001078829','XP_574547')
  //);
  global $proteinDB;
  $sequenceID = ''; 
  $acc2gi= array();
  $count = 0;
  //get sequenceID
  //if(count($all_acc_arr) > 1){
  if(count($all_acc_arr)){
    $break_flag = 0;
    foreach($all_acc_arr as $key => $tmp_arr){
      if(!is_array($tmp_arr)) continue;
      $order = '';
      $table_field = _get_acc_table_fields($key, $tmp_arr[0]);

      $SQL = "select ".$table_field['id_field'].", SequenceID from ".$table_field['acc_tableName']." where ".$table_field['match_field']."=";
      foreach($tmp_arr as $acc){
        $SQLs = $SQL . "'$acc' ". $order_by_str;
        $results = mysqli_query($proteinDB->link, $SQLs);
        if($row = mysqli_fetch_array($results)){
          if($row[1]){
            $sequenceID = $row[1]; 
            if($table_field['id_field'] == 'GI'){
              $acc2gi[$acc] = $row[0];
              $break_flag = 1;
              break;
            }        
          }
        }else{
          continue;
        }
      }
      if($break_flag) break;
    }
  }  
  //add seqence
  if(!$sequenceID && $Sequence){
    $SQL = "insert into Protein_Sequence set Sequence='$Sequence'";
    if(!mysqli_query($proteinDB->link, $SQL) ){
      _fatal_error(mysqli_error($db_link)); 
    }else{
      $sequenceID = mysqli_insert_id($proteinDB->link);
    }
  }
  //update seqenceID
  if($sequenceID){
    foreach($all_acc_arr as $key => $tmp_arr){
      if(!is_array($tmp_arr)) continue;
      $table_field = _get_acc_table_fields($key, $tmp_arr[0]);
      foreach($tmp_arr as $acc){
        if($table_field['id_field'] == 'GI' and isset($acc2gi[$acc])){
          $acc = $acc2gi[$acc];
          $table_field['match_field'] = 'GI';
        }
        if($table_field['match_field'] != 'Acc'){
          $order_by_str = $table_field['order'];
        }else{
          $order_by_str = " order by Acc_Version desc limit 1";
        }
        $SQL = "update ".$table_field['acc_tableName']. " set SequenceID='$sequenceID' ";
        if($Description){
          $SQL .= ", Description='".mysqli_real_escape_string ($proteinDB->link, $Description)."' ";
        }
        $SQL .="where ".$table_field['match_field']."='$acc' and (SequenceID IS NULL or SequenceID=0)";
        if(mysqli_query($proteinDB->link, $SQL)){
//===========================================================================
if(isset($fp_log_tmp) && $fp_log_tmp){
  fwrite($fp_log_tmp, "update proteinID=".$acc."\r\n");
}  
//===========================================================================
          $tmp_coun = mysqli_affected_rows($proteinDB->link);
          $count += $tmp_coun;
        }
      }
    }
  }
  return $count;
}

function _get_acc_table_fields($key, $acc=''){
  $order = '';
  if($key == 'IPI'){
    $acc_tableName = 'Protein_AccessionIPI';
    $id_field = 'IPI_Version';
    $match_field = $id_field;
    $geneID_field = 'EntrezGeneID';
  }else if($key == 'ENSEMBL'){
    $acc_tableName = 'Protein_AccessionENS';
    $id_field = 'ENSP';
    $match_field = $id_field;
    $geneID_field = 'ENSG';
  }else if($key == 'REFSEQ'){
    $acc_tableName = 'Protein_Accession';
    $id_field = 'GI';
    if(strpos($acc,".")){
      $id_field = 'GI';
      $match_field = 'Acc_version';
    }else{
      $id_field = 'Acc_version';
      $match_field = 'Acc';
      $order = " order by Acc_version desc limit 1";
    }
    $geneID_field = 'EntrezGeneID';
  }else if($key == 'Acc_Version'){
    $acc_tableName = 'Protein_Accession';
    $id_field = 'GI';
    $match_field = 'Acc_Version';
    $geneID_field = 'EntrezGeneID';
  }else if($key == 'GI' ){
    $acc_tableName = 'Protein_Accession';
    $id_field = 'GI';
    $match_field = 'GI';
    $geneID_field = 'EntrezGeneID';
  }else if($key == 'UniProt' ){
    $acc_tableName = 'Protein_Accession';
    $id_field = 'Acc_Version';
    $match_field = 'UniProtID';
    $geneID_field = 'EntrezGeneID';
  }else{// if($key == 'TREMBL' or $key == 'SWISS-PROT')
    if(strpos($acc,".")){
      $id_field = 'Acc_Version';
      $match_field = 'Acc_Version';
    }else{
      $id_field = 'Acc';
      $match_field = 'Acc';
    }
    $acc_tableName = 'Protein_Accession';
    $geneID_field = 'EntrezGeneID';
  }
  return array('acc_tableName'=>$acc_tableName, 'id_field'=>$id_field, 'match_field'=>$match_field, 'order'=>$order, 'geneID_field'=>$geneID_field);
}

//---------------------------------------------
function exist_no_seq(&$all_acc_arr){
// check if Procession table has sequence
//---------------------------------------------
  //return: 
  // doesn't exist = 0
  // existing but no sequenceID = 1
  // existing has sequenceID = 2
  global $proteinDB;
  $rt = 0;
  foreach($all_acc_arr as $key => $tmp_arr){
    if(!isset($tmp_arr[0])) continue;
    $order = '';
    $table_field = _get_acc_table_fields($key, $tmp_arr[0]);
    if($table_field['match_field'] != 'Acc'){
      $order_by_str = " order by SequenceID desc limit 1";
    }else{
      $order_by_str = " order by Acc_Version desc limit 1";
    }
    
    
    $SQL = "select ".$table_field['id_field'].", SequenceID, ".$table_field['geneID_field']." from ".$table_field['acc_tableName']." 
          where ".$table_field['match_field']."='".$tmp_arr[0]."' $order_by_str";
     
    
    $results = mysqli_query($proteinDB->link, $SQL);
    if($row = mysqli_fetch_array($results)){
      if($table_field['match_field'] == 'Acc'){
        if(array_key_exists('Acc_version', $row)){
          $all_acc_arr = array();
          $all_acc_arr['Acc_version'][0] = $row['Acc_version'];
        }
      }
      if($row[1]){
        $rt =  2;
      }elseif(!$row[1]){
        $rt = 1;
      }
      if($rt and !$row[2] and defined("GET_GENE_FROM_RUL") and GET_GENE_FROM_RUL){
        $table_field['SequenceID'] = $row[1];
        $table_field['geneID'] = $row[2];
        $table_field['match_id'] = $tmp_arr[0];
        $table_field['protein_key'] = $key;
        if(isset($all_acc_arr['Source'])){
          $table_field['Source'] = $all_acc_arr['Source'];
        }else{
          $table_field['Source'] = '';
        }
        update_protein_geneID($table_field);
      }
    }
  }
  return $rt;
}

//---------------------------------------------------------------------------
function add_ipi($tmp_acc_arr, $tmp_GeneName, $tmp_tax,$tmp_desc, $Sequence){
//---------------------------------------------------------------------------
  global $proteinDB;
  $IPI = '';
  $GeneID = '';
  $Acc = '';
  $acc2seq_arr = array();
  if(preg_match("/^IPI:(.*)/", $tmp_acc_arr[0], $matches)){
    $IPI_Version = $matches[1];
    $IPI = preg_replace("/[.].*/", '', $IPI_Version);
    $acc2seq_arr['IPI'] = array($IPI_Version);
  }else{
    return;
  }
  //check existing
  $SQL = "Select IPI from Protein_AccessionIPI 
         where IPI_Version='$IPI_Version' and EntrezGeneID > 0";
  
  if(mysqli_num_rows(mysqli_query($proteinDB->link, $SQL))){
    return;
  }  
  //add or update IPI  
  //try to get Gene ID from Protein_Accession
  for($i = 1; $i < count($tmp_acc_arr); $i++){
    if(preg_match("/(.*):([^-| .]*)/", $tmp_acc_arr[$i], $matches)){
      if($matches[1] != 'SWISS-PROT' and $matches[1] != 'TREMBL' and $matches[1] != 'ENSEMBL' and $matches[1] != 'REFSEQ') continue;
      $tmp_arr = explode(";", $matches[2]);
      $acc2seq_arr[$matches[1]] = $tmp_arr;
      
      foreach($tmp_arr as $value){
        if(!$GeneID){
          $SQL = "select EntrezGeneID, TaxID from Protein_Accession where Acc='$value' and EntrezGeneID is not null order by GI desc limit 1";
          //echo $SQL. "<br>";
          $results = mysqli_query($proteinDB->link, $SQL);
          if($row = mysqli_fetch_row($results)){
            if($tmp_tax and $row[1]){
              if($tmp_tax == $row[1]){
                $GeneID = $row[0];
                $Acc = $value;
              }
            }else{
              $GeneID = $row[0];
              $Acc = $value;
            }          
          }
        }
      }
    }
  }
  
  //try to get Gene ID from Protein_Class
  if(!$GeneID and $tmp_GeneName and $tmp_GeneName !='-' and $tmp_tax){ 
    $SQL = "select EntrezGeneID from Protein_Class where GeneName='$tmp_GeneName' and TaxID='$tmp_tax' order by EntrezGeneID desc limit 1";
    $results = mysqli_query($proteinDB->link, $SQL);
    if($row = mysqli_fetch_row($results)){
      $GeneID = $row[0];
      if(preg_match("/(.*):([^-| .]*)/", $tmp_acc_arr[1], $matches)){
        $tmp_arr = explode(";", $matches[2]);
        $Acc = $tmp_arr[0];
      }
    }
  }elseif(!$GeneID){ //try to get Gene ID from url
    $tmpp_arr = get_protein_detail_from_url($IPI);
    if($tmpp_arr['GeneID']){
      $GeneID = trim($tmpp_arr['GeneID']);
      //$IPI_Version = $tmpp_arr['IPI_Version'];
      //$IPI = $tmpp_arr['IPI'];
      $tmp_desc = $tmpp_arr['description'];
      $Acc = $tmpp_arr['Acc'];
      $tmp_GeneName = trim($tmpp_arr['GeneName']);
      $tmp_tax = $tmpp_arr['TaxID'];
      $Sequence = $tmpp_arr['Sequence'];
    }
  }
  //if($GeneID){
    $tmp_desc = mysqli_escape_string($proteinDB->link, $tmp_desc);
    $SQL_set = "IPI='$IPI', IPI_Version='$IPI_Version',  GeneName='$tmp_GeneName', Description='$tmp_desc', Acc='$Acc', TaxID='$tmp_tax'";
    if($GeneID) $SQL_set .= ", EntrezGeneID='$GeneID'";
    if(mysqli_num_rows(mysqli_query($proteinDB->link, "select IPI from Protein_AccessionIPI where IPI_Version='$IPI_Version'"))){
      $SQL = "update Protein_AccessionIPI set " . $SQL_set . " where IPI_Version='$IPI_Version'";
    }else if(mysqli_num_rows(mysqli_query($proteinDB->link, "select IPI from Protein_AccessionIPI where IPI='$IPI'"))){
      $SQL = "update Protein_AccessionIPI set " . $SQL_set . " where IPI='$IPI'";
    }else{
      $SQL = "insert into Protein_AccessionIPI set " . $SQL_set;
    }
    if(!mysqli_query($proteinDB->link, $SQL)){
      _fatal_error($SQL.":". mysqli_error($proteinDB->link)); 
    }else{
      return @update_sequence($Sequence,$acc2seq_arr);
      //return 1;
    }
  //}
  return 0;
}

//----------------------------------------------
function upload_fasta_IPI_file($myfile, $myfileName=''){
//----------------------------------------------
    $tmp_acc_arr = array();
    $tmp_GeneName = '';
    $tmp_tax = '';
    $tmp_desc = '';
    $Sequence = "";
    $ipi_num=0;
    $record_num = 0;
    $start_time = @date("Y-m-d H:i:s");
    
    if(!$myfileName){
      $msg = "processing file: $myfile \n";
      echo $msg;
      _write_log($msg . $start_time); 
    }
    
    if (!$fp = popen("cat $myfile", "r")) {
       _fatal_error("file $myfile is missing");
    }
    $line_num = 0;
    $stop =0;
    while ($data = fgets($fp)){
      //if($line_num > 60) exit;
      $data = trim($data);
      $line_num++; 
      print_dot($line_num);
      if(strpos($data,'>IPI') === 0){
        $ipi_num++;
        if(count($tmp_acc_arr) > 0){
          //------------------------------
          $i = add_ipi($tmp_acc_arr, $tmp_GeneName, $tmp_tax,$tmp_desc, $Sequence);
          //------------------------------
          $record_num += $i;
        }
        unset($tmp_acc_arr);
        $tmp_acc_arr = array();
        $tmp_GeneName = '';
        $tmp_tax = '';
        $tmp_desc = '';
        $Sequence = "";      
        if(preg_match("/\>(IPI:[^ ]*)(.*)/", $data, $matches)){        
          if(count($matches) == 3){
            $tmp_acc_arr = explode('|', $matches[1]);
            $tmp_desc = $matches[2];
            if(preg_match("/Tax_Id=([0-9]*)/", $matches[2], $sub_matches)){
              $tmp_tax = $sub_matches[1];
            }
            if(preg_match("/Gene_Symbol=([^ ]*)/", $matches[2], $sub_matches)){
              $tmp_GeneName = $sub_matches[1];
              if($tmp_GeneName == '-') $tmp_GeneName = '';
            }
          }
        }
      }else{
        if($line_num > 50 and !$ipi_num){
          echo "<br>The file '$myfile' is not IPI fasta file. <br>
           please download from ftp://ftp.ebi.ac.uk/pub/databases/IPI/current/ and unzip it before uploadding.";
          exit;
        }
        if(!preg_match("/[^A-Z]/i", $data)){
            $Sequence .= $data;
        }
      }
    }
    if(feof ($fp) and $tmp_acc_arr){
      ////////////////////////////
      $i = add_ipi($tmp_acc_arr, $tmp_GeneName, $tmp_tax,$tmp_desc, $Sequence);
      ////////////////////////////
      $record_num += $i;
    }
    fclose ($fp);
    $end_time = @date("Y-m-d H:i:s"); 
    $msg =  "\r\nend: total new/updated records = $record_num.\nstart time: $start_time     end time: $end_time";
    echo "<h2>".$msg."</h2>";
    _write_log($msg);
}

//-----------------------------------------
function upload_fasta_file($uploadedfile){
//-----------------------------------------
  //$tmp_acc_arr = array(
  //  'IPI'=>array('IPI00187591'),
  //  'GI'=>array('34580273'),
  //  'REFSEQ'=>Array('XP_001078829','XP_574547')
  //);
//====================================================================
global $fp_log_tmp;  
//====================================================================
//---------------jp 20170724------------------------------------------
  $has_DECOY = '';
//--------------------------------------------------------------------
 
  global $proteinDB;
  global $frm_removed_id;
  $frm_removed_id = trim($frm_removed_id);
  
  $tmp_acc_arr = array();
  $tmp_GeneName = '';
  $tmp_tax = '';
  $tmp_desc = '';
  $Sequence = "";
  $record_num = 0;
  $Sequence = '';
  $Description = '';
  $seq_num = 0;
  $isIPI_file = false;
  $isOtherFasta = false;
  $no_acc_in_db_arr = array();
  
  $myfile=$uploadedfile['tmp_name'];
  $myfileName=$uploadedfile['name'];
  $start_time = @date("Y-m-d H:i:s"); 
  
  echo "<pre>";
  $msg = "processing uploaded file: $myfileName";
  echo $msg."\n";
  _write_log($msg . $start_time); 
  
  //===========================================================================
  if(isset($fp_log_tmp) && $fp_log_tmp){
    fwrite($fp_log_tmp, "\r\n\r===============================================================================================\r\n");
    fwrite($fp_log_tmp, "processing uploaded file: $myfileName");
  }
  //=========================================================================== 
   
  if(!$fp = popen("cat $myfile", "r")) {
     _fatal_error("file $myfileRealName is missing");
  }
  
  $line_num = 0;
  $stop =0;
  
  $removed_id_arr = explode(",", $frm_removed_id);
  while ($data = fgets($fp)) {
    //$stop++; echo $data;if($stop > 260) exit;;
    $data = trim($data);
    $line_num++; 
    print_dot($line_num);
    if(!$isOtherFasta and strpos($data,'>IPI:') === 0){
      fclose($fp);
      $isIPI_file = true;
      /////////////////////////////////////////////////
      @upload_fasta_IPI_file($myfile, $myfileName);
      /////////////////////////////////////////////////
      break;
    }
    if(strpos($data,'>') === 0){
      $seq_num++;
      if(count($tmp_acc_arr) > 0){ 
       
        $acc_no_seq = exist_no_seq($tmp_acc_arr);
        
        
        
        
        $isOtherFasta = true;
        if(isset($tmp_acc_arr['UniProt'][0]) and isset($tmp_acc_arr['Acc'][0])){
          $SQL = "Update Protein_Accession set UniProtID='".$tmp_acc_arr['UniProt'][0]."' where Acc='".$tmp_acc_arr['Acc'][0]."'";
          mysqli_query($proteinDB->link, $SQL);
          if(mysqli_affected_rows($proteinDB->link)>0) $acc_no_seq = 1;
        }
        //if($acc_no_seq == 2) the SequenceID exists and do nothing.
        //if($acc_no_seq == 1) the SequenceID is not exists and add Sequence then link to this protein.
        //if($acc_no_seq == 0) this protein is not exist. insert this protein and add Sequence then link to this protein.
        
//echo "\$acc_no_seq=$acc_no_seq<br>";
        if($acc_no_seq == 1){
          $i = @update_sequence($Sequence, $tmp_acc_arr, $Description); 
          $record_num += $i;
        }else if(!$acc_no_seq){           
          //insert new protein here.
          if(isset($tmp_acc_arr['ENSEMBL']) and isset($tmp_acc_arr['Acc']) and isset($tmp_acc_arr['Acc_Version'])){
            //wormbase
            if(add_new_wormbase($tmp_acc_arr)){
              $i = @update_sequence($Sequence, $tmp_acc_arr);
              $record_num += $i;
            }
          }else{
            if(defined("GET_GENE_FROM_RUL") and GET_GENE_FROM_RUL and add_protein_url($tmp_acc_arr)){
              //add to gene to Protein_Class and Protien_Accession
              $i = @update_sequence($Sequence, $tmp_acc_arr);
              $record_num += $i;
              $gene_found = 1;
              //echo "ADD protein\n";
            }else{
              if(defined("INSERT_NO_GENE_PROTEIN") and INSERT_NO_GENE_PROTEIN ){
                $record_num += insert_no_gene_protein($Sequence, $tmp_acc_arr);
              }else{
                foreach($tmp_acc_arr as $key=>$tmp_arr){
                  if(is_array($tmp_arr)){
                    array_push($no_acc_in_db_arr, $tmp_arr[0]);
                    break;
                  }  
                }
              }
            }
          }
        }
      }//end adding one seq

      unset($tmp_acc_arr);
      $tmp_acc_arr = array();
      $tmp_GeneName = '';
      $tmp_tax = '';
      $tmp_desc = '';
      $Sequence = "";
      $Description = '';
      //$data='>gi|087066|seq;sp|frank  (P02769) Serum albumin precursor (Allergen Bos d 6) (BSA)';
      
      if(preg_match("/^\>DECOY/", $data)){
        $has_DECOY = 'has_DECOY';
        $Sequence = "skip_this_line";
      }elseif($removed_id_arr){
        foreach($removed_id_arr as $removed_id_val){
          $removed_id_val = trim($removed_id_val);
          if($removed_id_val && preg_match('/^\>'.$removed_id_val.'/', $data)){
            $has_DECOY = 'has_DECOY';
            $Sequence = "skip_this_line";
            break;
          }
        }
      }
      if($Sequence == "skip_this_line"){
        if(isset($fp_log_tmp) && $fp_log_tmp){
          fwrite($fp_log_tmp, "skip the line -- ".$data."\r\n");
        }        
        continue;
      }
/*      
$data = ">AAC37145.1 protein FAM49B isoform 4 [Homo sapiens]";      
$data = ">NP_001340245 protein FAM49B isoform 4 [Homo sapiens]";
$data = ">ENSP00000300161 protein FAM49B isoform 4 [Homo sapiens]";
$data = ">NP_001340245.1 protein FAM49B isoform 4 [Homo sapiens]";
$data = ">ref|NP_001340245.1 protein FAM49B isoform 4 [Homo sapiens]";
$data = ">gi|984390119|ref|NP_001306002.1| phosphatidylinositide phosphatase SAC1 isoform 3 [Homo sapiens]";
$data = ">NP_001263218.1|gn|PRKAR1A:5573| phosphatidylinositide phosphatase SAC1 isoform 3 [Homo sapiens]";      
$data = ">sp|Q8N3X1|FNBP4_HUMAN phosphatidylinositide phosphatase SAC1 isoform 3 [Homo sapiens]";      
$data = ">ref|NP_444384|gn|Klra19:93971| phosphatidylinositide phosphatase SAC1 isoform 3 [Homo sapiens]";      
$data = ">ref|YP_220550.1| phosphatidylinositide phosphatase SAC1 isoform 3 [Homo sapiens]";
*/ 
//$data = ">ENSEMBL:ENSBTAP00000038329 (Bos taurus) 9 kDa protein";      
//$data = ">REFSEQ:XP_001252647 (Bos taurus) similar to endopin 2B";     
      
      if(preg_match("/\>gi\|([0-9]*)[^ ]*(.*)/", $data, $matches)){
        $tmp_acc_arr['GI']=array($matches[1]);
        $Description = trim($matches[2]);
      }else if(preg_match("/\>(ENS[^ ]*)/", $data, $matches)){
        $tmp_acc_arr['ENSEMBL']=array($matches[1]);
        if(preg_match("/\[Source:.*Acc:([^ ]*)\]/", $data, $matches)){
          $tmp_acc_arr['Acc']=array($matches[1]);
        }
      }else{
        if(preg_match("/\>([^\s]*)/", $data, $matches)){
          $matches[1] = preg_replace("/(^[a-z]{2,3}[\||:])/", "",$matches[1]);
          $tmp_arr = explode('|', $matches[1]);
          $first_ID = $tmp_arr[0];
          $second_ID = '';
          if(isset($tmp_arr[1])){
            $second_ID = $tmp_arr[1];
          }
//---------------------jp 20170724-----------------------------------------          
          $protein_ID_type = get_protein_ID_type($first_ID);
          if($protein_ID_type == 'ENS'){
            $protein_ID_type = 'ENSEMBL';
          }
          $tmp_acc_arr[$protein_ID_type] = array($first_ID);
//--------------------------------------------------------------------------          
          /*
          if(strpos($first_ID,'_')){
            $tmp_acc_arr['UniProt']=array($first_ID);
            if(preg_match("/\>[^ ]*[ ]\(([^ ]*)\)/", $data, $matches_a)){
              $tmp_acc_arr['Acc']=array($matches_a[1]); 
            }
          }else if(preg_match("/[ ]gene:([^ ]*)/", $data, $matches_b)){
            //C.elegans for ens
            if(preg_match("/^".$matches_b[1]."[^ ]*?$/", $first_ID, $tmp)){
              $tmp_acc_arr['ENSEMBL']=array($first_ID);
            }
          }else if(preg_match("/[\t| ]WBGene[0-9]+[\t| ]/", $data, $matches_b)){
            //C.elegans for wormbase
            if(preg_match("/UniProt:([^\t| ]*)[\t| ]protein_id:([^\t| ]*)/",$data, $tmp)){
              $tmp_acc_arr['ENSEMBL']=array($first_ID);
              $tmp_acc_arr['Acc']=array($tmp[1]);
              $tmp_acc_arr['Acc_Version']=array($tmp[2]);
            }else{
              $tmp_acc_arr['Acc']=array($first_ID);
            }
          }else if(strpos($data, ">sp|")===0){
            $tmp_acc_arr['Acc']=array($first_ID);
            $tmp_acc_arr['Source']='sp';
            if($second_ID and strpos($second_ID,'_')>0) $tmp_acc_arr['UniProt'] = array($second_ID);
            if(preg_match("/[ ]OS=(.+)[ ]GN=([^ ]+)/",$data, $matches_sp)){
              $tmp_acc_arr['ORGANISM'] = $matches_sp[1];
              $tmp_acc_arr['geneName'] = $matches_sp[2];
            }
          }else if(strpos($data, ">tr|")===0){
            $tmp_acc_arr['Acc']=array($first_ID);
            $tmp_acc_arr['Source']='tr';
            if($second_ID and strpos($second_ID,'_')>0) $tmp_acc_arr['UniProt'] = array($second_ID);
            if(preg_match("/[ ]OS=(.+)[ ]GN=([^ ]+)/",$data, $matches_sp)){
              $tmp_acc_arr['ORGANISM'] = $matches_sp[1];
              $tmp_acc_arr['geneName'] = $matches_sp[2];
            }            
          }else{
            $tmp_acc_arr['Acc']=array($first_ID);
          }
          */
        }
      }      
    }else{
      if($line_num > 50 and !$seq_num){
        return "<br>The file '$myfile' is not fasta file.";
      }
      if($Sequence == "skip_this_line") continue;  
      if(!preg_match("/[^A-Z]/i", $data)){
         $Sequence .= $data;
      }
    }
  }
  
  if(!$isIPI_file){
    if(feof($fp) and $tmp_acc_arr){
      $acc_no_seq = exist_no_seq($tmp_acc_arr);
      if(!$acc_no_seq and isset($tmp_acc_arr['UniProt']) and isset($tmp_acc_arr['Acc'])){
        $SQL = "Update Prohits_Accession set UniProtID='".$tmp_acc_arr['UniProt'][0]."' where Acc='".$tmp_acc_arr['Acc'][0]."'";
        mysqli_query($proteinDB->link, $SQL);
        if(mysqli_affected_rows($proteinDB->link)>0) $acc_no_seq = 1;
      }
      
      if($acc_no_seq == 1){
        $i = @update_sequence($Sequence, $tmp_acc_arr);
        $record_num += $i;
      }else if(!$acc_no_seq){
        if(isset($tmp_acc_arr['ENSEMBL']) and isset($tmp_acc_arr['Acc']) and isset($tmp_acc_arr['Acc_Version'])){
          if(add_new_wormbase($tmp_acc_arr)){
            $i = @update_sequence($Sequence, $tmp_acc_arr);
            $record_num += $i;
          }
        }else{
          if(defined("GET_GENE_FROM_RUL") and GET_GENE_FROM_RUL and add_protein_url($tmp_acc_arr)){
            //add to gene to Protein_Class and Protien_Accession
            $i = @update_sequence($Sequence, $tmp_acc_arr);
            $record_num += $i;
            $gene_found = 1;
          }else{
            if(defined("INSERT_NO_GENE_PROTEIN") and INSERT_NO_GENE_PROTEIN ){
              $record_num += insert_no_gene_protein($Sequence,$tmp_acc_arr);
            }else{
              foreach($tmp_acc_arr as $key=>$tmp_arr){
                if(is_array($tmp_arr)){
                  array_push($no_acc_in_db_arr, $tmp_arr[0]);
                  break;
                }
              }
            }
          }
        }
      }
      fclose($fp);
    }
    echo "</pre>";
    $msg= "End of file processing. updated sequence number=$record_num.";
    echo $msg;
    _write_log($msg);
    if(count($no_acc_in_db_arr)){
       _write_log("following proteins not in Prohits Protein database");
    }
    foreach($no_acc_in_db_arr as $value){ 
       _write_log($value);
    }
  }
  return $has_DECOY;
}

function add_protein_url($tmp_acc_arr){
  global $proteinDB;
  global $URLS;
  global $fp_log_tmp;  

  $rt = 0;
  foreach($tmp_acc_arr as $key=>$tmp_arr){
//print_r($tmp_acc_arr);
    if(!is_array($tmp_arr)) continue;
    $Protein_arr = array();
    $GeneTable = '';
    if($key == 'GI'){
      $GeneTable = 'Protein_Class';
      $ProteinTable = 'Protein_Accession';
      $URL = str_replace("rettype=fasta", "rettype=gp", $URLS["NCBI_PROTEIN_FASTA"]);
      $URL .= $tmp_arr[0];      
      
      $Protein_arr = parse_NCBI_GenePept($URL);
//if(!isset($Protein_arr['GeneID']) || !$Protein_arr['GeneID']) break;
    }else if($key == 'Acc' and isset($tmp_acc_arr['Source']) and ($tmp_acc_arr['Source'] == 'sp' or $tmp_acc_arr['Source'] == 'tr')){
      $GeneTable = 'Protein_Class';
      $ProteinTable = 'Protein_Accession';
      $Protein_arr = get_protein_from_url_UniProt($tmp_arr[0], "uniprotkb");
//if(!isset($Protein_arr['GeneID']) || !$Protein_arr['GeneID']) break;
      $Protein_arr['Source'] = $tmp_acc_arr['Source'];
      if(isset($tmp_acc_arr['UniProt'][0])){
        $Protein_arr['UniProt'] = $tmp_acc_arr['UniProt'][0];
      }
      if(isset($Protein_arr['GeneID']) && isset($Protein_arr['Accession'])){
       $Protein_arr['Accession_Version'] = $Protein_arr['Accession'];
      }
    }else if($key == 'Acc'){
      $GeneTable = 'Protein_Class';
      $ProteinTable = 'Protein_Accession';
      $Protein_arr = get_protein_from_url($tmp_arr[0]);
//if(!isset($Protein_arr['GeneID']) || !$Protein_arr['GeneID']) break;
      if((!isset($Protein_arr['Accession_Version']) || !$Protein_arr['Accession_Version']) && isset($Protein_arr['Accession'])){
        $Protein_arr['Accession_Version'] = $Protein_arr['Accession'];
      } 
    }else if($key == 'UniProt'){
      $GeneTable = 'Protein_Class';
      $ProteinTable = 'Protein_Accession';
      $Protein_arr = get_protein_from_url_UniProt($tmp_arr[0]);
      if((!isset($Protein_arr['Accession_Version']) || !$Protein_arr['Accession_Version']) && isset($Protein_arr['Accession'])){
        $Protein_arr['Accession_Version'] = $Protein_arr['Accession'];
      }   
    }    
    if(isset($Protein_arr['Accession_Version'])){   
      if(isset($Protein_arr['GeneID']) && $Protein_arr['GeneID']){
        $Protein_arr = Insert_or_Update_GeneTable($Protein_arr,$GeneTable,$proteinDB); 
      }
      $SQL = "insert into $ProteinTable set ";
      if($key == 'UniProt'){
        $tmp_key = 'UniProtID';
      }else{
        $tmp_key = $key;
      } 
      $SQL .= "$tmp_key='" . $tmp_arr[0] . "'";
      if($key == 'GI' || $key == 'UniProt'){
        $SQL .= ",Acc='" . mysqli_real_escape_string($proteinDB->link, $Protein_arr['Accession']) . "'";
      }
      $SQL .= ",Acc_Version='" . mysqli_real_escape_string($proteinDB->link, trim($Protein_arr['Accession_Version'])) . "'"; 
      if(isset($Protein_arr['GeneID'])){
        $SQL .= ", EntrezGeneID='" . $Protein_arr['GeneID'] . "'";
      }
      if(isset($Protein_arr['TaxID']) and $Protein_arr['TaxID']){
        $SQL .= ",TaxID='".$Protein_arr['TaxID']."'";
      }
      if(isset($Protein_arr['UniProt']) && $key != 'UniProt'){
        $SQL .= ",UniProtID='".$Protein_arr['UniProt']."'";
      }
      if(isset($Protein_arr['Source'])){
        $SQL .= ",Source='".$Protein_arr['Source']."'";
      }
      if(isset($Protein_arr['description'])){
        $SQL .= ",Description='".mysqli_real_escape_string($proteinDB->link, $Protein_arr['description'])."'";
      }
      //echo "$SQL\n";
      $result = mysqli_query($proteinDB->link, $SQL);
      if($result){
        if(isset($fp_log_tmp) && $fp_log_tmp){
          fwrite($fp_log_tmp, "inserted proteinID=".$tmp_arr[0]."\r\n");
        }
        $rt = 1;
      }
    }
    //only process first protein
    break;
  }
  return $rt;
}

function update_protein_geneID($protein_info){
  global $proteinDB;
  global $URLS;
  global $fp_log_tmp;  

  $Protein_arr = array();
  $GeneTable = '';
  if($protein_info['protein_key'] == 'GI' or 'REFSEQ'){
    $GeneTable = 'Protein_Class';
    //$URL = str_replace("rettype=fasta", "rettype=gp", $URLS["NCBI_PROTEIN_FASTA"]);
    $URL = $URLS["NCBI_PROTEIN_GENPEPT"];
    $URL .= $protein_info['match_id'];
    $Protein_arr = parse_NCBI_GenePept($URL);
  }else if($protein_info['protein_key'] == 'Acc' and isset($protein_info['Source']) and ($protein_info['Source'] == 'sp' or $protein_info['Source'] == 'tr')){
    $GeneTable = 'Protein_Class';
    $Protein_arr = get_protein_from_url_UniProt($protein_info['match_id']);
  }else if($protein_info['protein_key'] == 'Acc' ){
    $GeneTable = 'Protein_Class';
    $Protein_arr = get_protein_from_url($protein_info['match_id']);
  }else if($protein_info['protein_key'] == 'UniProt'){
    $GeneTable = 'Protein_Class';
    $Protein_arr = get_protein_from_url_UniProt($protein_info['match_id']);
  }
   
  if(isset($Protein_arr['GeneID']) && $Protein_arr['GeneID']){
//-----------------------------------------------------------------------------------------------
    $Protein_arr = Insert_or_Update_GeneTable($Protein_arr,$GeneTable,$proteinDB); 
//-----------------------------------------------------------------------------------------------
    $SQL = "update ".$protein_info['acc_tableName']." set ".$protein_info['geneID_field']."='".$Protein_arr['GeneID']."' 
            where ".$protein_info['match_field']."='".$protein_info['match_id']."'";
    $result = mysqli_query($proteinDB->link, $SQL);
  }
  if($protein_info['SequenceID']){
    return 2;
  }else{
    return 1;
  }
}

function Insert_or_Update_GeneTable($Protein_arr,$GeneTable,$proteinDB){
  global $fp_log_tmp;
  $sub_SQL = " EntrezGeneID='" . $Protein_arr['GeneID'] . "'";
  if(isset($Protein_arr['GeneName'])){
    $sub_SQL .= ",GeneName='" . mysqli_real_escape_string ($proteinDB->link, $Protein_arr['GeneName']) . "'";
  }
  if(isset($Protein_arr['TaxID']) and $Protein_arr['TaxID']){
    $sub_SQL .= ",TaxID='".$Protein_arr['TaxID']."'";
  }else if(isset($Protein_arr['ORGANISM']) and $Protein_arr['ORGANISM']){
    $tmp_SQL = "select TaxID from NCBI_tax_names where name_txt='".$Protein_arr['ORGANISM']."'";
    $results_tmp = mysqli_query($proteinDB->link, $tmp_SQL);
    if($row_tmp = mysqli_fetch_row($results_tmp)){
      if($row_tmp[0]){
        $Protein_arr['TaxID'] = $row_tmp[0];
        $sub_SQL .= ",TaxID='".$row_tmp[0]."'";
      }
    }
  }
  $SQL = "select EntrezGeneID, TaxID from $GeneTable where EntrezGeneID='".$Protein_arr['GeneID']."'";
  $results = mysqli_query($proteinDB->link, $SQL);
  $action = '';
  if($row = mysqli_fetch_row($results)){
    $SQL = "UPDATE $GeneTable SET ".$sub_SQL." WHERE EntrezGeneID='".$Protein_arr['GeneID']."'";
    if(!isset($Protein_arr['TaxID']) or !$Protein_arr['TaxID']){
      $Protein_arr['TaxID'] = $row[1];
    } 
    $action = "update";
  }else{
    $SQL = "insert into $GeneTable set ".$sub_SQL;
    $action = "insert";
  } 
//echo "\$action=$action<br>";  
  mysqli_query($proteinDB->link, $SQL);
  if(isset($fp_log_tmp) && $fp_log_tmp && $action == "insert"){
    fwrite($fp_log_tmp, "__________inserted geneID=".$Protein_arr['GeneID']."\r\n");
  }
  return $Protein_arr;
}

function add_new_wormbase($tmp_acc_arr){
  global $proteinDB;
  $GeneID = '';
  $GeneName = '';
  if(isset($tmp_acc_arr['ENSEMBL']) and isset($tmp_acc_arr['Acc']) and isset($tmp_acc_arr['Acc_Version'])){
    $SQL = "SELECT `EntrezGeneID` FROM `Protein_Accession` WHERE  `Acc_Version`='".$tmp_acc_arr['Acc_Version'][0]."' and `EntrezGeneID` IS NOT NULL";
    $result =  mysqli_query($proteinDB->link, $SQL);
    if($row = mysqli_fetch_row($result)){
     $GeneID = $row[0];
    }else{
     $SQL = "SELECT `EntrezGeneID` FROM `Protein_Accession` WHERE `Acc`='".$tmp_acc_arr['Acc'][0]."' and `EntrezGeneID` IS NOT NULL";
     $result =  mysqli_query($proteinDB->link, $SQL);
     if($row = mysqli_fetch_row($result)) $GeneID = $row[0];
   }
   if($GeneID){
      $SQL = "SELECT GeneName FROM `Protein_Class` WHERE `EntrezGeneID`='$GeneID'";
      if($result = mysqli_query($proteinDB->link, $SQL)){
        if($row = mysqli_fetch_row($result)){
          $GeneName = $row[0];
        }
      }
    }
    $tmp_acc = preg_replace("/\..*$/", '', $tmp_acc_arr['Acc_Version'][0]);
    $SQL = "INSERT INTO Protein_AccessionENS SET
    ENSP='".$tmp_acc_arr['ENSEMBL'][0]."',
    ENSG='".$GeneName."',
    EntrezGeneID='".$GeneID."',
    GeneName='".mysqli_escape_string($proteinDB->link, $GeneName)."',
    Acc='".$tmp_acc."'";
    if(mysqli_query($proteinDB->link, $SQL)){
      return true;
    }
  }
  return false;
}

function upload_ens_map_file($uploadedfile){
  global $proteinDB;
  $ENSG_geneID_arr = array();
  $ENSP_no_GeneID_arr = array();
   
  $myfile=$uploadedfile['tmp_name'];
  $myfileName=$uploadedfile['name'];
  $start_time = @date("Y-m-d H:i:s"); 
  
  echo "<pre>";
  $msg = "processing uploaded file: $myfileName";
  echo $msg."\n";
  _write_log($msg . $start_time); 
  
  if (!$fp = popen("cat $myfile", "r")) {
     _fatal_error("file $myfileRealName is missing");
  }
  $line_num = 1;
  $buffer = fgets($fp);
  //Ensembl Gene ID  Ensembl Protein ID  Description  Associated Gene Name  EntrezGene ID
  $tmp_arr = explode("\t",trim($buffer));
  $file_format_ok = false;
  if(count($tmp_arr) >= 5){
    if($tmp_arr[0] == 'Ensembl Gene ID' and 
       $tmp_arr[1] == 'Ensembl Protein ID' and
       $tmp_arr[2] == 'Description' and
       $tmp_arr[3] == 'Associated Gene Name' and 
       $tmp_arr[4] == 'EntrezGene ID'
       ){
        $file_format_ok = true;
     }
  }
  if(!$file_format_ok){
    return "
    The format of the uploaded file '$myfileName' is not correct
    and cannot be mapped to Ensembl. 
    Please save the file as 'TSV' with the following fields:<br>
    Ensembl Gene ID, 
    Ensembl Protein ID, 
    Description, 
    Associated Gene Name, 
    EntrezGeneID";
  }
  $record_num = 0;
   
  while($buffer = fgets($fp)){
    $need_geneID = false;
    $need_add_new = false;
    $tmp_acc_arr = array();
    
    $line_num++;
    if(!$buffer) continue;
    print_dot($line_num);
    //echo $buffer;if($line_num > 60) break;
    
    $recordArr = explode("\t",$buffer);
    
    $ENS = $recordArr[0];
    $Peptide_ID = trim($recordArr[1]);
    $Description = trim($recordArr[2]);
    $GeneName = trim($recordArr[3]);
    $GeneID = trim($recordArr[4]);
     
    if($GeneID) $ENSG_geneID_arr[$ENS] = $GeneID;
    if(count($recordArr) < 5 or !$ENS or !$Peptide_ID){
      continue;
    }
    $AccKey = ''; 
    if(preg_match('/Acc:(.+)]/',$Description, $matches)){
      $AccKey = $matches[1];
      if(strpos($AccKey, '-') && preg_match('/(.+)?-\d+$/',$AccKey, $inner)){
        $AccKey = $inner[1];
      }
    }
    
    $SQL = "SELECT `EntrezGeneID` FROM `Protein_AccessionENS` WHERE ENSP='$Peptide_ID'";
     
    $results =  mysqli_query($proteinDB->link, $SQL);
    if($row = mysqli_fetch_row($results)){ 
      if($row[0]){
        continue;
      }else{
        $need_geneID = true;
      }
    }else{
      $need_add_new = true;
    }
    if($AccKey){
      $SQL = "SELECT `EntrezGeneID` FROM `Protein_Accession` WHERE `Acc`='$AccKey' AND `EntrezGeneID` IS NOT NULL";
      $result =  mysqli_query($proteinDB->link, $SQL);
      if($row = mysqli_fetch_row($result)){
        $GeneID = $row[0];
      }else{
        $SQL = "SELECT `EntrezGeneID` FROM `Protein_AccessionIPI` WHERE `Acc`='$AccKey' AND `EntrezGeneID` IS NOT NULL";
        $result =  mysqli_query($proteinDB->link, $SQL);
        if($row = mysqli_fetch_row($result)){
          $GeneID = $row[0];
        }
      }
    }
    if($GeneID){
      $SQL = "SELECT GeneName FROM `Protein_Class` WHERE `EntrezGeneID`='$GeneID'";
       
      if($result = mysqli_query($proteinDB->link, $SQL)){
        $row = mysqli_fetch_row($result);
        if($row[0]) $GeneName = $row[0];
      }
    }
    //add to ClassENS anyway
    $SQL = "INSERT INTO Protein_ClassENS SET
          ENSG='$ENS',
          GeneName='".mysqli_escape_string($proteinDB->link, $GeneName)."'";
    mysqli_query($proteinDB->link, $SQL);
    if(!$GeneID) {
      array_push($ENSP_no_GeneID_arr, array("ENSG"=>$ENS,"ENSP"=>$Peptide_ID));
    }else{
      if($need_geneID){
        $SQL = "update Protein_AccessionENS SET
          ENSG='$ENS',
          EntrezGeneID='".$GeneID."'
          where ENSP='$Peptide_ID'";
        
        if(mysqli_query($proteinDB->link, $SQL)) $record_num++;
      }
    }
    if($need_add_new){
      $SQL = "INSERT INTO Protein_AccessionENS SET
          ENSP='$Peptide_ID',
          ENSG='$ENS',
          EntrezGeneID='".$GeneID."',
          Description='".mysqli_escape_string($proteinDB->link, $Description)."',
          GeneName='".mysqli_escape_string($proteinDB->link, $GeneName)."',
          Acc='$AccKey'";
        
      if(mysqli_query($proteinDB->link, $SQL)) $record_num++;
    } 
     
  }
  fclose($fp);
  foreach($ENSP_no_GeneID_arr as $tmp_protein){
    if(isset($ENSG_geneID_arr[$tmp_protein['ENSG']])){
      $GeneID = $ENSG_geneID_arr[$tmp_protein['ENSG']];
      $SQL = "update Protein_AccessionENS set EntrezGeneID='".$GeneID."' where ENSP='".$tmp_protein['ENSP']."'";
      mysqli_query($proteinDB->link, $SQL);
    }
  }
  echo "</pre>";
  $msg= "End of file processing. added/updated Ens acc number=$record_num.";
  echo $msg;
  _write_log($msg);
} 
//-------------------------------------------
//open url and return containts
function _open_url($url){
  $timeout = 10; 
  $rt = '';
  
  $fp = @fopen("$url", 'rb'); 
  if (!$fp) {
    echo "Unable to open $url\n";
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

function parse_NCBI_GenePept($URL){
  $protein_arr = array();
  $protein_arr['CDS'] = '';
  $protein_arr['Sequence'] = '';
  $protein_arr['TaxID'] = '';
  $protein_arr['GeneName'] = '';
  $protein_arr['GeneID'] = '';
  
  if(preg_match('/(\d+)$/',$URL, $matches)){
    $pp_id = $matches[1];
  }else{
    $pp_id = 0;
  }
    

$time_start = time();
$response = _open_url($URL);
$time_end = time();
$time_v = $time_end - $time_start;
if($time_v >= 1){
  //echo "|$URL----------$time_v<br>";
}

  if($response){
    $lines = explode("\n", $response);
    $num = 0;
    foreach($lines as $line){
      $num++;
      if(!trim($line)) continue;
      if(preg_match('/^DEFINITION\s*(.+)/', $line, $matches)){
        $protein_arr['description'] = $matches[1];
      }else if(!isset($protein_arr['Accession']) and strpos($line, "VERSION")===0){
        if(preg_match("/^VERSION\s+(\S+)/", $line, $matches)){
          $protein_arr['Accession_Version'] = $matches[1];
          $protein_arr['Accession'] = preg_replace("/[.].+$/",'', $matches[1]);
        }
      }else if(strpos($line, "SOURCE")===0){
        $line = $lines[$num];
        if(preg_match("/ORGANISM\s+(.+)$/", $line, $matches)){
          $protein_arr['ORGANISM'] = $matches[1];
        }
      }else if(!$protein_arr['CDS'] and strpos($line, "  CDS  ")){
        $protein_arr['CDS'] = 1;
      }else if($protein_arr['CDS']){
        if(preg_match("/gene=\"(.+)\"/", $line, $matches)){
          $protein_arr['GeneName'] = $matches[1];
        }else if(preg_match("/locus_tag=\"(.+)\"/", $line, $matches)){
          $protein_arr['locus_tag'] = $matches[1];
        }else if(preg_match("/db_xref=\"GeneID:(.+)\"/", $line, $matches)){
          $protein_arr['GeneID'] = $matches[1];
        }else if(strpos($line, "ORIGIN") === 0){
          $protein_arr['Sequence'] = '';
          continue;
        }
      }else if($protein_arr['CDS'] != 1 and preg_match("/db_xref=\"taxon:([0-9]+)\"/", $line, $matches)){
          $protein_arr['TaxID'] = $matches[1];
      }
      if(isset($protein_arr['Sequence'])){
        $protein_arr['Sequence'] .= $line;
      }
    }
    if(isset($protein_arr['Sequence']) and  $protein_arr['Sequence']){
       $protein_arr['Sequence'] =  preg_replace("/[^A-Z]/", "", strtoupper($protein_arr['Sequence']));
    }
    if(!isset($protein_arr['GeneName']) and isset($protein_arr['locus_tag'])){
      $protein_arr['GeneName'] = $protein_arr['locus_tag'];
    }
  }
  
  return $protein_arr;
}

function insert_no_gene_protein($Sequence,$tmp_acc_arr){
  global $proteinDB;
  global $fp_log_tmp;
  $record_num = 0;
  foreach($tmp_acc_arr as $tmp_key => $tmp_arr){
    if(!is_array($tmp_arr)) continue;
    $table_field = _get_acc_table_fields($tmp_key, $tmp_arr[0]);
    $SQL = "insert into ".$table_field['acc_tableName']." set ".$table_field['match_field']."='".trim($tmp_arr[0])."'";
    $result = mysqli_query($proteinDB->link, $SQL);
    if($result){
      if($table_field['acc_tableName'] == 'Protein_Accession' && isset($tmp_acc_arr['UniProt'][0]) and isset($tmp_acc_arr['Acc'][0])){
        $SQL = "Update Protein_Accession set UniProtID='".$tmp_acc_arr['UniProt'][0]."' where Acc='".$tmp_acc_arr['Acc'][0]."'";
        mysqli_query($proteinDB->link, $SQL);
        if(mysqli_affected_rows($proteinDB->link)>0) $acc_no_seq = 1;
      }
      $i = @update_sequence($Sequence, $tmp_acc_arr);
      $record_num += $i;
      //===========================================================================
      if(isset($fp_log_tmp) && $fp_log_tmp){
        fwrite($fp_log_tmp, "inserted proteinID=".$tmp_arr[0]."\r\n");
      }
      //=========================================================================== 
      break;
      //only process first protein  
    }
  }
  return $record_num;
}

function Acc_UniProtID_mapping($mapping_file){
  global $proteinDB;
  if(!$fp = popen("cat $mapping_file", "r")) {
     _fatal_error("file $mapping_file is missing");
  }
  
  $line_num = 0;
  $record_num = 0;
  
  $start_time = @date("Y-m-d H:i:s");
  $deleted_tax = 0;
  while($data = fgets($fp)){
    //echo "$data\n";
    //if($line_num > 1000) exit;
    $line_num++;
    print_dot($line_num);
    $col_arr = explode("\t", $data);
    if(strpos($col_arr[0], ";")) continue;
    
    $UniProtKB = $col_arr[0];
    $UniProtID = $col_arr[1];
    $GeneID = $col_arr[2];
    $RefSeq = $col_arr[3];
    $GI = $col_arr[4];
    $TaxID = $col_arr[12];
    
    if(!$deleted_tax and trim($TaxID)){
      $deleted_tax = 1;
      echo "<br>";
      $msg = "\nUpdate Protein_mapping table. delete all records belong to Tax ID $TaxID and add new again\n";
      echo $msg;
      echo "<br>";
      $SQL = "DELETE FROM `Protein_mapping` WHERE `TaxID`='$TaxID'";
      $proteinDB->execute($SQL);
    }
    
    $GI_str = str_replace(" ", "", $GI);
    $GI_str = str_replace(";", ",", $GI_str);
    
    $ACC_GI_arr = array();
    if($GI_str){
//--get Acc_Versions by GIs.
      $SQL = "SELECT `Acc_Version` FROM `Protein_Accession` WHERE `GI` IN ($GI_str)";
      $GI_arr_tmp = $proteinDB->fetchAll($SQL);
      foreach($GI_arr_tmp as $tmp_ACC){
        if($tmp_ACC['Acc_Version']){
          if(!in_array($tmp_ACC['Acc_Version'], $ACC_GI_arr)){
            $ACC_GI_arr[] = $tmp_ACC['Acc_Version'];
          } 
        }
      }
    }
//--get Acc_Versions from RefSeq
    $RefSeq_arr = array();
    if(trim($RefSeq)){   
      $RefSeq_str = str_replace(" ", "", $RefSeq);
      $RefSeq_arr = explode(';',$RefSeq_str);
    }
    
//--if no geneID in the file get geneID from Protein_Accession.
    if(!$GeneID){
      $tmp_arr = array_diff($ACC_GI_arr, $RefSeq_arr);
      $ACC_arr = array_merge($RefSeq_arr,  $tmp_arr);
      $ACC_str = implode("','",$ACC_arr);
      $ACC_str = "'".$ACC_str."'";
      $SQL = "SELECT `EntrezGeneID` FROM `Protein_Accession` WHERE `Acc_Version` IN ($ACC_str)";
      $EntrezGeneID_arr = $proteinDB->fetchAll($SQL);
      foreach($EntrezGeneID_arr as $EntrezGeneID_val){
        if($EntrezGeneID_val['EntrezGeneID']){
          $GeneID = $EntrezGeneID_val['EntrezGeneID'];
          break;
        }
      }
    }
//--insert records which Acc_Version in the refseq colon of the file into Protein_mapping             
    foreach($RefSeq_arr as $RefSeq_ID){
      if(!trim($RefSeq_ID)) continue;
      $SQL = "INSERT INTO `Protein_mapping` SET
              `UniProtKB`='$UniProtKB', 
              `UniProtID`='$UniProtID', 
              `GeneID`='$GeneID', 
              `Acc_Version`='$RefSeq_ID', 
              `TaxID`='$TaxID'";
      $proteinDB->insert($SQL);
      $record_num++;
    }
//--insert records which Acc_Version in the [GI(colon)-Refseq9colon)] of the file into Protein_mapping     
    $diff_arr = array_diff($ACC_GI_arr, $RefSeq_arr);
    foreach($diff_arr as $Acc_Version){
      $tmp_ACC_arr = explode('.',$Acc_Version);
      $tmp_ACC = $tmp_ACC_arr[0];
      if($tmp_ACC == $UniProtKB) continue;
      $SQL = "INSERT INTO `Protein_mapping` SET
              `UniProtKB`='$UniProtKB', 
              `UniProtID`='$UniProtID', 
              `GeneID`='$GeneID', 
              `Acc_Version`='$Acc_Version', 
              `TaxID`='$TaxID'";
      $proteinDB->insert($SQL);
      $record_num++;
    }
  }    
  pclose($fp);
  
  $end_time = @date("Y-m-d H:i:s"); 
  $msg =  "\r\nend of '$mapping_file' and updating updating DB table 'Protein_mapping' total new/updated records = $record_num.\nstart time: $start_time     end time: $end_time";
  echo "<h2>".$msg."</h2>";
  _write_log($msg);
}
?>
