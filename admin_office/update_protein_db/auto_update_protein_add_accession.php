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
$process_uniprot_sprot = 0;
$process_uniprot_mapping = 0;
$process_gene_info = 0;
$process_gene2accession = 0;
$process_nr = 0;
$process_ipi = 0;

$Organism_tax['name'] = array(
'yeast', 
'pombe', 
'house mouse',
'rat',
'human',

'Human adenovirus C',
'Human adenovirus 1',
'Human adenovirus 5',
'Human adenovirus 2',

'African clawed frog',
'thale cress',
'zebra fish',
'fruit flies',
'Para rubber tree',

'hydrozoans',
'cattle',
'enterobacteria',
'chicken',
'rabbit',


'horse',
'virus L-A',
'pig',
'Lysobacter enzymogenes',
'sheep',

'Chinese hamster',
'ascomycetes',
'Sumatran orangutan',
'crab-eating macaque',
'dog',

'Drosophila virilis',
'chimpanzee',
'Caenorhabditis elegans',
'Candida albicans',

'Dictyostelium discoideum'
);


$Organism_tax['id'] = array(
'559292', 
'4896', '284812',
'10090', '10091', '10092', '57486', '39442',
'10114', '10116', 
'9606', '63221', '741158',

'129951',
'10533',
'28285',
'10515',


'8355', 
'3702',
'7955',
'7215','7227', '46245',
'3981',

'6100',
'9913',
'562','83333', '511145',
'9031',
'9986',

'9796',
'11008',
'9823',
'69',
'9940',

'10029',
'237561',
'9601',
'9541',
'9615',

'7244',
'9598',
'6239',
'5476',

'44689',
);



ini_set('display_errors', 1);
set_time_limit(0);
ini_set("memory_limit","-1");

include_once("../../config/conf.inc.php");
include_once("../../common/mysqlDB_class.php");
include_once("../../common/user_class.php");
include_once("functions.ini.php");
include_once("auto_update_protein_add_accession.inc.php");
include_once("../../common/common_fun.inc.php");

session_start();
if(!$_SESSION['USER']){
  echo "you didnot login";exit;
} 

ob_end_flush();
echo "<pre>
/***************************************************************************
description: 
   1. Add new records into Protein database from following files:
        gene2accession 
        gene_info 
        gene2refseq.
   2. This script can parse uniprot_sprot dat, uniprot id mapping files to tables;
      Protein_Accession
   3. Don't close this Window while the program is running.
      
****************************************************************************\
";
echo "<b>Only following species will be added in database, when process NCBI <b>gene info</b> and <b>gene2accession</b>. \n
if you want to add more species please add species Taxonomy ID to '\$Organism_tax' in the header of this script file.\n";
print_r( $Organism_tax['name']);
echo "<b>Taxonomy ID</b>:" . implode(", ", $Organism_tax['id']); 
flush();


$progressing_flag = './lock_flag.txt';
$UniProt_disabled = 'No';
$fp_log = 0;

$enable_lock = 0;
$update_protein_desc = true;
$update_gene_history = false;  //if true, it needs $NCBI_gene_file5="gene_history.gz" be download in protein_db_upldate.php file.

 

if(is_file($progressing_flag) and $enable_lock){
  echo "<font color=red>You cannot run downloading files or updating database at this time.<br>"; 
  echo "Maybe another user is doing it now or some exception occurs. Please wait<br>";
  echo "or ask your administrator to remove lock file 'lock_flag.txt' in directory</font><br>";
  echo dirname($_SERVER["PHP_SELF"]);
  exit;
}else{
  if(!$flag_handle = fopen($progressing_flag, "w")){
    $errorMess = "cannot create lock flag";
    _fatal_error($error);
  }else{
    $progressing_flag."<br>";
    fclose($flag_handle);
  }
}

$download_conf = './download.conf';
$download_log = './download.log';

$NCBI_gene_info = "gene_info";            //20 min
$NCBI_gene_history = "gene_history";
$NCBI_gene2accession = "gene2accession";  // needs 2hrs
$NCBI_nr_file = "nr";                     // needs 3hrs

//permission check
if(!is_writable($download_log)) _fatal_error("$download_log is not writable");
$prohitsDB = new mysqlDB(PROHITS_DB);

//===============================================================
$Prohits_proteins = PROHITS_PROTEINS_DB;
$Prohits_proteins = 'Prohits_proteins';
$mainDB = new mysqlDB($Prohits_proteins);
//===============================================================
$proteinDB = $mainDB;
$db_link = $mainDB->link;
$SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName like 'Protein DB Configuration%' and UserID='".$_SESSION['USER']->ID."'";
$record = $prohitsDB->fetch($SQL);
$perm_modify = $record['Modify'];
$perm_delete = $record['Delete'];
$perm_insert = $record['Insert'];
if(!$record or !$perm_insert) {
  echo "The user has no permission to save file to Protien database.";
  exit;
}

//read conf file 
if(!file_exists($download_conf)){
   _fatal_error("file doesn't exist 'download.conf: $download.conf"); 
}else{
   $fd = fopen($download_conf, "r");
   while (!feof ($fd)) {
        $buffer = fgets($fd, 20000);
        $buffer = trim($buffer);
        if(!$buffer or preg_match("/^#/", $buffer )){
            continue;
        }
        list($key,$value) = explode("=", $buffer);
        $$key = $value;
        
    }
} 
 

$statusFile = './download_status.txt';
$statusArr = array();
if(is_file($statusFile)){
  $status_fd = fopen($statusFile, "r");
  while(!feof($status_fd)){
    $buffer = fgets($status_fd, 20000);
    $buffer = trim($buffer);
    if(!$buffer) continue;
    list($key,$value) = explode("=", $buffer);
    $statusArr[$key] = $value;
  }
  fclose($status_fd);
}

$stop = 0;

$selected_to_process = false;

if($process_gene_info == "Yes"){
    $selected_to_process = true;
    //-------------- processing file gene_info---------------------
    //it will update old record and add new record.
    $myfile = $download_to . $NCBI_gene_info;
    $field_delimiter = "\t";
    $tableName = "Protein_Class";
    
    $msg = "\nprocessing file: $myfile \n";
    echo $msg;
    _write_log($msg);
    if(!is_file($myfile)) _fatal_error("file $myfile is missing");
    $fd = popen ("cat $myfile", "r");
    $start_time = @date("Y-m-d H:i:s");
    $line_num = 0;
    $new = 0;
    while (!feof ($fd) and $stop!=100) {
        
        $buffer = fgets($fd, 20000);
        
        if(strpos($buffer,"#") === 0 ){
            continue;
        }
        
        if($buffer){        
            $arr = explode($field_delimiter,$buffer);
 
            $arr[0] = trim($arr[0]);
            if(!in_array($arr[0], $Organism_tax['id'])){
              continue;
            }
            //$stop++;
            if($stop) echo "$buffer\n";
            $field_value_str = "EntrezGeneID='" . $arr[1] . "'";
            $field_value_str .= ",LocusTag='" . mysqli_real_escape_string ($db_link, $arr[3]) . "'";
            $field_value_str .= ",GeneName='" . mysqli_real_escape_string ($db_link, $arr[2]) . "'";
            $field_value_str .= ",GeneAliase='" . mysqli_real_escape_string ($db_link, $arr[4]) . "'";
            $field_value_str .= ",TaxID='" . $arr[0] . "'";
            $field_value_str .= ",Description='" . mysqli_real_escape_string ($db_link, $arr[8]) . "'";
            if(mysqli_num_rows(mysqli_query($db_link, "select EntrezGeneID from $tableName where EntrezGeneID='".$arr[1]."'"))){
                $SQL = "update $tableName set $field_value_str where EntrezGeneID='". $arr[1]. "'";
            }else{
                $SQL = "insert into $tableName set $field_value_str";
            }
            if($stop) echo "$SQL\n";
            $result = mysqli_query($db_link, $SQL);
                   
            if (!$result) {
                _fatal_error($SQL . mysqli_error($db_link)); 
            }else if(mysqli_affected_rows($db_link) > 0){
                $new++;
            }
            print_dot($line_num++);
       }
    }//end while
    pclose($fd);
    
    $end_time = @date("Y-m-d H:i:s");
    echo "\n<h2>end of '$myfile' total new/updated record = $new \n";
    echo "start time: $start_time     end time: $end_time</h2>";
    _write_log("$tableName has been updated: total new/updated record = $new: $end_time");
    $statusArr['processed_date_gene_info'] = @date('Y-m-d H:i:s');
    $updatedItem = str_replace('$', "", "\$process_gene_info");
    update_conf_file($updatedItem);
    
    if($update_gene_history){
      $myfile = $download_to . $NCBI_gene_history;
      $msg = "processing file: $myfile \n";
      echo $msg;
      _write_log($msg);
      if(!is_file($myfile)) _fatal_error("file $myfile is missing");
      $fd = popen ("cat $myfile", "r");
      $start_time = @date("Y-m-d H:i:s");
      $line_num = 0;
      $new = 0;
      while (!feof ($fd)) {
          $stop++;
          //if$stop > 500 ) exit;
          $buffer = fgets($fd);
          if(strpos($buffer,"#") === 0 or !$buffer){
              continue;
          }
          $arr = explode($field_delimiter,$buffer);
          
          $arr[0] = trim($arr[0]);
          if(!in_array($arr[0], $Organism_tax['id'])){
            continue;
          }
           
          $newGeneID = $arr[1];
          $oldGeneID = $arr[2];
          if($newGeneID != '-'){
            $status = "Replaced:$newGeneID";
          }else{
            $status = "Discontinued";
          }
          $SQL = "update $tableName set Status='$status' where EntrezGeneID='$oldGeneID'";
          //echo $SQL."\n";flush();
          mysqli_query($db_link, $SQL);
          print_dot($line_num++);
      }
      pclose($fd);
      $end_time = @date("Y-m-d H:i:s");
      
      echo "\n<h2>end of '$myfile' \n";
      echo "start time: $start_time     end time: $end_time</h2>";
      _write_log("$tableName history updated. $end_time");
   }
}

if($process_gene2accession == "Yes"){
    //------------ start to procession file gene2accession -----------
    // it will add new and update existing records.
    $selected_to_process = true;
    $myfile = $download_to . $NCBI_gene2accession;
    $field_delimiter = "\t";
    $tableName = "Protein_Accession";
    
    $msg = "\nprocessing file: $myfile \n";
    echo $msg;
    _write_log($msg);
    if(!is_file_for_large_file($myfile)) _fatal_error("file $myfile is missing");
    $fd = popen ("cat $myfile", "r");
    $start_time = @date("Y-m-d H:i:s");
    $line_num = 0;
    $new = 0;
    $stop = 0;
     
    $pre_GI = '';
    while (!feof ($fd)) {
        //$stop++; if($stop > 200) exit;
        
        $buffer = fgets($fd, 20000);
        //echo $buffer;
        //$buffer = trim($buffer);
        if(strpos($buffer,"#") === 0){
            echo "$buffer\n";
            continue;
        }
        if($buffer){
           
            print_dot($line_num++);
            $arr = explode($field_delimiter,$buffer);
            $tmp_TaxID = trim($arr[0]);
             
            if(!in_array($tmp_TaxID, $Organism_tax['id'])){
              continue;
            }
             
            
            $tmp_GeneID = $arr[1];
            $tmp_GI = $arr[6];
            $tmp_Acc_version = $arr[5];
            $tmp_Acc = preg_replace("/[.]\w*$/", '', $arr[5]);
            
            if(!is_numeric($tmp_GI) or $pre_GI == $tmp_GI) continue; 
            $pre_GI = $tmp_GI;
             
            $field_value_str = "EntrezGeneID='" . $tmp_GeneID . "'";
            $field_value_str .= ",GI='" . $tmp_GI . "'";
            $field_value_str .= ",Acc='" . mysqli_real_escape_string($db_link, $tmp_Acc) . "'";
            $field_value_str .= ",Acc_Version='" . mysqli_real_escape_string($db_link, trim($tmp_Acc_version)) . "'"; 
            $field_value_str .= ",Status='" . mysqli_real_escape_string($db_link, $arr[2]) . "'";
            $field_value_str .= ",TaxID='" . $tmp_TaxID . "'"; 
            
            // refseq protein accession: NP_; XP_; ZP_; AP_; YP_;
            if(preg_match("/^(NP_|XP_|ZP_|AP_|YP_)/", strtoupper($tmp_Acc) ) ){
               $field_value_str .= ",Source='ref'"; 
            }
             
            $SQL = "select ID, EntrezGeneID from $tableName where GI='".$tmp_GI."'";
            $result = mysqli_query($db_link, $SQL);
            if($row = mysqli_fetch_row($result)){
              if(!$row[1]){
                $SQL = "update $tableName set EntrezGeneID='" . $tmp_GeneID . "' where ID='".$row[0]."'";
                mysqli_query($db_link, $SQL);
              }
            }else{
              $SQL = "insert into $tableName set $field_value_str";              
              $result = mysqli_query($db_link, $SQL);
              //echo "$SQL\n";exit;
              if (!$result) {
                _fatal_error($SQL . mysqli_error($db_link)); 
              }else if(mysqli_affected_rows($db_link) > 0){
                $new++;
              }
            }
        }
    }//end while
    fclose($fd);
    $end_time = @date("Y-m-d H:i:s");
    
    echo "\n<h2>end of '$myfile' total new/updated record = $new \n";
    
    echo "start time: $start_time     end time: $end_time</h2>";
    _write_log("$tableName has been updated: total new/updated record = $new: $end_time");
    $statusArr['processed_date_gene2accession'] = @date('Y-m-d H:i:s');
    $updatedItem = str_replace('$', "", "\$process_gene2accession");
    update_conf_file($updatedItem);
}



if($process_uniprot_sprot and $UniProt_disabled != 'Yes'){
    
    //------------------ processing file uniprot_sprot.dat ----------------------------
    $myfile = $download_to . $process_uniprot_sprot;
    $uniRecord['ID'] = '';
    $uniRecord['ACC_str'] = '';
    $uniRecord['GeneName'] = ''; 
    $uniRecord['TaxID']= ''; 
    $uniRecord['Description']= ''; 
    $uniRecord['RefSeq_arr']= array();
    $uniRecord['GeneID']= '';
    $uniRecord['Source'] = '';
    $selected_to_process = true;
    if(strstr($process_uniprot_sprot, 'uniprot_sprot')){
      $uniRecord['Source'] = 'sp';
    }
    
    $msg = "processing file: $myfile \n";
    echo $msg;
    _write_log($msg); 
    $start_time = @date("Y-m-d H:i:s"); 
    //read a hodge file
    if (!$fp = popen("cat $myfile", "r")) {
       _fatal_error("file $myfile is missing");
    }
    /*
    ID   LR16C_HUMAN             Reviewed;        1435 AA.
    AC   Q6F5E8;  //AC   Q9P2M1; A6NJR7; A7E219; Q9NSN6;
    GN   Name=LRP2BP; Synonyms=KIAA1325;
    OX   NCBI_TaxID=9606;
    DR   RefSeq; NP_001013860.1; -.
    DR   RefSeq; NP_004944.3; NM_004953.4. [Q04637-7]/////////////////////////////
    DR   GeneID; 146206; -.
    DE   RecName: Full=Forkhead box protein D4-like 6;
    DE            Short=FOXD4-like 6;
    */
    
     
    $line_num = 0;
    while ($data = fgets($fp)) {
       //if($line_num > 1000 ) exit;
       $line_num++;
       print_dot($line_num);
       if(preg_match("/^ID   ([^ ]+)/", $data, $matchs)){
          
         ////////////////
         add_uniprot();
         ///////////////
         $uniRecord['ID'] = $matchs[1];
       }else if(preg_match("/^AC   (.+)/", $data, $matchs)){
         $uniRecord['ACC_str'] .= $matchs[1];
       }else if(preg_match("/^GN   Name=([^;]+)/", $data, $matchs)){
         $uniRecord['GeneName']= $matchs[1];
       }else if(preg_match("/^OX   NCBI_TaxID=([0-9]+)/", $data, $matchs)){
         $uniRecord['TaxID']= $matchs[1];
       }else if(preg_match("/^DR   RefSeq; ([^;]+)/", $data, $matchs)){
         $Acc_refSeq = preg_replace("/[.]\w+$/", "", $matchs[1]);
         array_push($uniRecord['RefSeq_arr'], $Acc_refSeq);
       }else if(preg_match("/^DR   GeneID; ([0-9]+)/", $data, $matchs)){
         $uniRecord['GeneID'] = $matchs[1];
       }else if(preg_match("/^DE   (.+)/", $data, $matchs)){
         $uniRecord['Description'] .= trim($matchs[1]);
       }
    }
    if(feof ($fp) and $uniRecord['ID']){
      ///////////////
      add_uniprot();
      ///////////////
    }
    pclose($fp);
     
    $end_time = @date("Y-m-d H:i:s"); 
    $msg =  "\r\nend of '$myfile' total new/updated records = $record_num.\nstart time: $start_time     end time: $end_time";
    echo "<h2>".$msg."</h2>";
    _write_log($msg);
    $statusArr['processed_date_'.$process_uniprot_sprot] = @date('Y-m-d H:i:s');
    update_conf_file('process_uniprot_sprot','');
    
}else if($process_uniprot_mapping and $UniProt_disabled != 'Yes'){
    $selected_to_process = true;
    /*
the following mappings delimited by tab:

1. UniProtKB-AC  ------------
2. UniProtKB-ID  ------------
3. GeneID (EntrezGene) ------
4. RefSeq
5. GI --------
6. PDB
7. GO
8. UniRef100
9. UniRef90
10. UniRef50
11. UniParc
12. PIR
13. NCBI-taxon
14. MIM
15. UniGene
16. PubMed
17. EMBL
18. EMBL-CDS
19. Ensembl ----
20. Ensembl_TRS
21. Ensembl_PRO ----
22. Additional PubMed

    */
    $myfile = $download_to . $process_uniprot_mapping;
     
    $msg = "\nprocessing file: $myfile \n";
    echo $msg;
    _write_log($msg); 
    $start_time = @date("Y-m-d H:i:s"); 
    //read a hodge file
    if (!$fp = popen("cat $myfile", "r")) {
       _fatal_error("file $myfile is missing");
    }
    $line_num = 0;
    $mapping = array();
    while ($data = fgets($fp)) {
       //echo "$data\n";
       //if($line_num > 100 ) exit;
       $line_num++;
       print_dot($line_num);
       $col_arr = explode("\t", $data);
       if(strpos($col_arr[0], ";")) continue;
       
       $mapping['Acc'] = $col_arr[0];
       $mapping['UniProtID'] = $col_arr[1];
       $mapping['EntrezGeneID'] = $col_arr[2];
       $mapping['RefSeq'] = $col_arr[3];
       $mapping['GI'] = $col_arr[4];
       $mapping['TaxID'] = $col_arr[12];
       $mapping['EMBL-CDS'] = $col_arr[17];
       $mapping['ENSG'] = $col_arr[18];
       $mapping['ENSP'] = $col_arr[20];
       add_mapping();
    }    
    pclose($fp);
    $end_time = @date("Y-m-d H:i:s"); 
    $msg =  "\r\nend of '$myfile' and updating updating DB table 'Protein_Accession' total new/updated records = $record_num.\nstart time: $start_time     end time: $end_time";
    echo "<h2>".$msg."</h2>";
    _write_log($msg);
    
//------------------------------------------------    
    Acc_UniProtID_mapping($myfile);
//-----------------------------------------------
    
    $statusArr['processed_date_'.$process_uniprot_mapping] = @date('Y-m-d H:i:s');
    update_conf_file('process_uniprot_mapping','');
}

if($process_ipi and $IPI_disabled != 'Yes'){
   $selected_to_process = true;
   $myfile = $download_to . $process_ipi;
   upload_fasta_IPI_file($myfile);
   $statusArr['processed_date_'.$process_ipi] = @date('Y-m-d H:i:s');
   update_conf_file('process_ipi','');
}

sleep(1);
flush();

if($statusArr){
  if($status_fd = fopen($statusFile, "w")){
    foreach($statusArr as $key => $value){
      fwrite($status_fd, $key."=".$value."\r\n");
    }
    fclose($status_fd);
  }else{
    echo "could not open file $statusFile";
  }
}
if($enable_lock) unlink($progressing_flag);
if($fp_log) fclose($fp_log);
if(!$selected_to_process){
  echo "<h2>Please follow the Protein DB Update instruction on the opener page.</h2>";
}
echo "\n\n<h2>END</h2>";
 
//end of all
//*****************************************************************************************************************************
?>
