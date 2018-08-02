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

error_reporting(E_ALL);
set_time_limit(3600*24);
ini_set("memory_limit","-1");
ini_set("default_socket_timeout", "10000");
//ob_end_flush();
echo "<pre>
**************************************************************************

description: 
       0. read download.conf file to get variables;
       1. download files from 
          NCBI:
          ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene2accession.gz
          ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene_info.gz
          ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene2go.gz
          ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene2refseq.gz
          ftp://ftp.ncbi.nlm.nih.gov/blast/db/FASTA//nr.gz
          ftp://ftp.ncbi.nlm.nih.gov/pub/taxonomy/taxdump.tar.gz (once only)
          ftp://ftp.ncbi.nlm.nih.gov/pub/taxonomy/gi_taxid_prot.dmp.gz
          
          UniProt:
          ftp://ftp.uniprot.org/pub/databases/uniprot/current_release/knowledgebase/taxonomic_divisions/
          
          IPI
          ftp://ftp.ebi.ac.uk/pub/databases/IPI/current/
          
        2. parse data to database.
        
        
****************************************************************************

";
include_once("../../config/conf.inc.php");
include_once("../../common/mysqlDB_class.php");
include_once("functions.ini.php");
$progressing_flag = './lock_flag.txt';
$enable_lock = 0;
$Prohits_proteins = PROHITS_PROTEINS_DB;
$Prohits_proteins = 'Prohits_proteins';

if($enable_lock){
  if(is_file($progressing_flag) ){
    echo "<font color=red>You cannot run downloading files or updating database at this time.<br>"; 
    echo "Maybe another user is doing it now or some exception occurs. Please wait<br>";
    echo "or ask your administrator to remove lock file 'lock_flag.txt' in directory</font><br>";
    echo dirname($_SERVER["PHP_SELF"]);
    exit;
  }else{
    if(!$flag_handle = fopen($progressing_flag, "w")){
      $errorMess = "cannot create lock flag";
      fatal_error($error);
    }else{
      fclose($flag_handle);
    }
  }
}
$download_conf = './download.conf';
$download_log = './download.log'; 
$statusFile = './download_status.txt';
$add_accession_file = "auto_update_protein_add_accession.php";

$conn_id = 0;

//read conf file
$NCBI_gene_files = array();
if(!file_exists($download_conf)){
   fatal_error("file doesn't exist: $download_conf."); 
}else{
   $fd = fopen($download_conf, "r");
   while (!feof ($fd)) {
        $buffer = fgets($fd, 20000);
        $buffer = trim($buffer);
        echo $buffer."\n";
        if(!$buffer or preg_match("/^#/", trim($buffer) )){
            continue;
        }
        list($key,$value) = explode("=", $buffer);
        if(preg_match("/^NCBI_gene_file[0-9]/", $key )){
            array_push($NCBI_gene_files,$value);
        }
        $$key = $value;
    }
    if(isset($UniProt_dat_file_str)){
      $UniProt_dat_file_arr = explode(" ", trim($UniProt_dat_file_str));
    }else{
      $UniProt_dat_file_arr = array();
    }
    if(isset($UniProt_dat_mapping_file_str)){
      $UniProt_dat_mapping_file_arr = explode(" ", trim($UniProt_dat_mapping_file_str));
    }else{
      $UniProt_dat_mapping_file_arr = array();
    }
    if(isset($IPI_fasta_file_str)){
      $IPI_fasta_file_arr = explode(" ", trim($IPI_fasta_file_str));
    }else{
      $IPI_fasta_file_arr = array();
    }
}

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

if(!_is_writable($download_log)) fatal_error($NCBI_FTP, $NCBI_ftp_username, "$download_log is not writable");
if(!_is_writable($download_to)) fatal_error("$download_to is not writable. Please read prohits_install_readme.doc step 2");
if(!file_exists($download_old)){
    //on output if ok
    $cmd = "mkdir -p $download_old 2>&1";
    if(exec($cmd, $output)) fatal_error("cannot create download folder '$download_old'." . $output[0]);
}
if(!file_exists($download_to ."Taxonomy/")){
    $cmd = "mkdir -p " . $download_to ."Taxonomy/ 2>&1";
    if(exec($cmd, $output)) fatal_error("cannot create download folder '$download_to Taxonomy/'." . $output[0]);

}

$curr_ftp_domain = $NCBI_FTP;
$curr_ftp_username = $NCBI_ftp_username;
$curr_ftp_password = $NCBI_ftp_password;
 
//FILE 1: NCBI files
print str_repeat("  \n",4);
echo "<pre>";
// ************* download from internet  **************************************************
//*****************************************************************************************

if($download_new_ncbi_files == "Yes"){
    connect_current_ftp_site();
    echo "\n<h2>Start to download files from $curr_ftp_domain $NCBI_gene_path</h2>";ob_flush();flush();
    
    //-----------------------------------------------------------------------------------
    for($i = 0;$i< count($NCBI_gene_files); $i++){
      $local_file = $download_to.$NCBI_gene_files[$i];
      
      $remote_file = $NCBI_gene_path.$NCBI_gene_files[$i];
      echo "\nDownload file from: $remote_file<br>\n";ob_flush();flush();
      ftp_file_get($conn_id, $local_file, $remote_file);
      unzip_file($local_file);
      sleep(1);
      $statusArr[$NCBI_gene_files[$i]] = @date('Y-m-d H:i:s');
    }
    echo "\n<h2>Start to download file from $curr_ftp_domain $NCBI_tax_path :$NCBI_taxdump</h2>";
    //------------------------------------------------------------------------------------
    $local_file = $download_to."Taxonomy/".$NCBI_taxdump;
    $cmd = "rm -f ".$download_to."Taxonomy/* 2>&1";
    if(exec($cmd, $output)) fatal_error($cmd."--cannot delete files from $local_file. Error:" . $output[0] );
    $remote_file = $NCBI_tax_path.$NCBI_taxdump;
    
    
    ftp_file_get($conn_id, $local_file, $remote_file);
    $curr_tar_folder = "Taxonomy/";
    unzip_file($local_file);
    $statusArr[$NCBI_taxdump] = @date('Y-m-d H:i:s');

    ftp_close($conn_id);
    update_conf_file('download_new_ncbi_files');
    echo "\nEnd of download from NCBI.";
}

if($download_new_uniprot_mapping_files == "Yes"){
    if($UniProt_disabled != 'Yes' and $UniProt_dat_mapping_file_arr){
  		$curr_ftp_domain = $UniProt_FTP; 
      connect_current_ftp_site();
  		echo "\n<h2>Start to download file from $curr_ftp_domain$UniProt_mapping_taxonomic_divisions_path</h2>";ob_flush();flush();
  		//------------------------------------------------------------------------------------
  		foreach($UniProt_dat_mapping_file_arr as $value){
        if($value){
          $local_file = $download_to.$value;
    		  $remote_file = $UniProt_mapping_taxonomic_divisions_path.$value;
          echo "\nDownload file from: $remote_file<br>\n";ob_flush();flush();
    		  ftp_file_get($conn_id, $local_file, $remote_file);
    		  unzip_file($local_file);
    		  $statusArr[$value] = @date('Y-m-d H:i:s');
        }
  		}
      ftp_close($conn_id);
  		update_conf_file('download_new_uniprot_mapping_files');
	  }
    echo "\nEnd of download from Uniport id mapping files";
}else if($download_new_uniprot_files == "Yes"){
    if($UniProt_disabled != 'Yes' and $UniProt_dat_file_arr){
  		$curr_ftp_domain = $UniProt_FTP;
  		connect_current_ftp_site();
  		echo "\n<h2>Start to download file from $curr_ftp_domain $UniProt_taxonomic_divisions_path</h2>";ob_flush();flush();
  		//------------------------------------------------------------------------------------
  		foreach($UniProt_dat_file_arr as $value){
        if($value){
          $local_file = $download_to.$value;
    		  $remote_file = $UniProt_taxonomic_divisions_path.$value;
    		  ftp_file_get($conn_id, $local_file, $remote_file);
    		  unzip_file($local_file);
    		  $statusArr[$value] = @date('Y-m-d H:i:s');
        }
  		}
      ftp_close($conn_id);
  		update_conf_file('download_new_uniprot_files');
	  }
    echo "\nEnd of download from Uniport";
}//end of downloading

if($download_new_IPI_files == "Yes"){
    if($IPI_disabled != 'Yes' and $IPI_fasta_file_arr){
  		$curr_ftp_domain = $IPI_FTP;
  		connect_current_ftp_site();
  		echo "\n<h2>Start to download file from $curr_ftp_domain $IPI_current_dir_path</h2>";ob_flush();flush();
  		//------------------------------------------------------------------------------------
  		foreach($IPI_fasta_file_arr as $value){
        if($value){
          $local_file = $download_to.$value;
    		  $remote_file = $IPI_current_dir_path.$value;
    		  ftp_file_get($conn_id, $local_file, $remote_file);
    		  unzip_file($local_file);
    		  $statusArr[$value] = @date('Y-m-d H:i:s');
        }
  		}
      ftp_close($conn_id);
  		update_conf_file('download_new_IPI_files');
	  }
}//end of downloading


write_to_status_file($statusArr);


// ************* parse files to NCBI tables************************************************
//*****************************************************************************************
$db_link = 0;

if($refresh_NCBI_tables == "Yes"){
    
    echo PROHITS_SERVER_IP;
    $NCBI_tables = array();
    
//==========================================================================
    //$mainDB = new mysqlDB("Prohits_proteins","localhost","std","std");
    $mainDB = new mysqlDB($Prohits_proteins);
//==========================================================================    
    
    $db_link = $mainDB->link;
    
    $results = mysqli_query($mainDB->link, "SHOW TABLES LIKE 'NCBI_%'");
    while($row = mysqli_fetch_row($results)){
      array_push($NCBI_tables, $row[0]);
    }
    print_r($NCBI_tables);
    //===================================================================================================================

    //$downloadfiles = "ftp://ftp.ncbi.nlm.nih.gov/pub/taxonomy/taxdump.tar.gz";
    $onlySaveHasValue = "scientific name\t|";
    $field_delimiter = "\t|\t";
    $myfile = $download_to."Taxonomy/names.dmp";
    //---------------------------
    $tableName = "NCBI_tax_names"; 
    //---------------------------
    $empty_table = "Yes";
    $addPrimaryKey = "";
    echo "\n<br>add record to $tableName<br>\n";
      $creatTableQuerry = "CREATE TABLE IF NOT EXISTS `$tableName` (
                        `TaxID` int(15) NOT NULL default '0',
                        `name_txt` varchar(100) NOT NULL default '',
                        `unique_name` varchar(100) NOT NULL default '',
                        `name_class` varchar(50) NOT NULL default '',
                        PRIMARY KEY  (`TaxID`)
                      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
     
    file_to_table($myfile, $field_delimiter, $tableName,$empty_table,$addPrimaryKey,$onlySaveHasValue,$creatTableQuerry);
    
     
    //===================================================================================================================
    //$downloadfiles = "ftp://ftp.ncbi.nlm.nih.gov/pub/taxonomy/taxdump.tar.gz";
    $onlySaveHasValue = "";
    $field_delimiter = "\t|\t";
    $myfile = $download_to."Taxonomy/nodes.dmp";
    //---------------------------
    $tableName = "NCBI_tax_nodes";
    //---------------------------
    $empty_table = "Yes";
    $addPrimaryKey = "";
    echo "\n<br>add record to $tableName<br>\n";
    $creatTableQuerry = "CREATE TABLE IF NOT EXISTS `$tableName` (
                        `Tax_id` int(11) NOT NULL default '0',
                        `Parent_tax_id` int(11) NOT NULL default '0',
                        `Rank` varchar(64) NOT NULL default '',
                        `Embl_code` varchar(16) NOT NULL default '',
                        `Division_id` int(11) NOT NULL default '0',
                        `Inherited_div_flag` tinyint(1) NOT NULL default '0',
                        `Genetic_code_id` int(11) NOT NULL default '0',
                        `Inherited_GC_flag` tinyint(1) NOT NULL default '0',
                        `Mitochondrial_genetic_code_id` int(11) NOT NULL default '0',
                        `Inherited_MGC_flag` tinyint(1) NOT NULL default '0',
                        `GenBank_hidden_flag` tinyint(1) NOT NULL default '0',
                        `Hidden_subtree_root_flag` tinyint(1) NOT NULL default '0',
                        `Comments` text NOT NULL,
                        KEY `Tax_id` (`Tax_id`),
                        KEY `Parent_tax_id` (`Parent_tax_id`)
                      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
    file_to_table($myfile, $field_delimiter, $tableName,$empty_table,$addPrimaryKey,$onlySaveHasValue,$creatTableQuerry);
    
    //===================================================================================================================
    //$downloadfiles = "ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene2go.gz";
    $onlySaveHasValue = "";
    $field_delimiter = "\t";
    $myfile = $download_to."gene2go";
    $addPrimaryKey = "";
    //----------------------------
    $tableName = "NCBI_gene2go";
    //----------------------------
    $empty_table = "Yes";
    echo "\n<br>add record to $tableName<br>\n";
    $creatTableQuerry = "CREATE TABLE IF NOT EXISTS `$tableName` (
                        `tax_id` varchar(20) NOT NULL default '',
                        `GeneID` varchar(20) NOT NULL default '',
                        `GO_ID` varchar(20) NOT NULL default '',
                        `Evidence` varchar(20) NOT NULL default '',
                        `Qualifier` varchar(20) NOT NULL default '',
                        `GO_term` varchar(254) NOT NULL default '',
                        `PubMed` varchar(20) NOT NULL default '',
                        `Category` varchar(40) default NULL,
                        KEY `GeneID` (`GeneID`)
                      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
    file_to_table($myfile, $field_delimiter, $tableName,$empty_table,$addPrimaryKey,$onlySaveHasValue,$creatTableQuerry);
    
    //===================================================================================================================
    //$downloadfiles = "ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene2refseq.gz";
    $onlySaveHasValue = "";
    $field_delimiter = "\t";
    $myfile = $download_to."gene2refseq";
    $addPrimaryKey = "";
    //----------------------------
    $tableName = "NCBI_gene2refseq";
    //----------------------------
    $empty_table = "Yes";
    //echo "\n<br>add record to $tableName<br>\n";
    $creatTableQuerry = "CREATE TABLE IF NOT EXISTS `NCBI_gene2refseq` (
                        `tax_id` varchar(20) NOT NULL DEFAULT '',
                        `GeneID` varchar(20) NOT NULL DEFAULT '',
                        `status` varchar(20) NOT NULL DEFAULT '',
                        `RNA_nucleotide_acc` varchar(20) NOT NULL DEFAULT '',
                        `RNA_nucleotide_gi` varchar(20) NOT NULL DEFAULT '',
                        `protein_acc` varchar(20) NOT NULL DEFAULT '',
                        `protein_gi` varchar(20) NOT NULL DEFAULT '',
                        `genomic_nucleotide_acc` varchar(20) NOT NULL DEFAULT '',
                        `genomic_nucleotide_gi` varchar(20) NOT NULL DEFAULT '',
                        `start_position` varchar(20) NOT NULL DEFAULT '',
                        `end_positon` varchar(20) NOT NULL DEFAULT '',
                        `orientation` varchar(20) NOT NULL DEFAULT '',
                        `assembly` varchar(20) DEFAULT NULL,
                        `mature_peptide_accession` varchar(30) DEFAULT NULL,
                        `mmature_peptide_gi` varchar(25) DEFAULT NULL,
                        `Symbol` varchar(15) DEFAULT NULL,
                        
                        KEY `protein_gi` (`protein_gi`),
                        KEY `GeneID` (`GeneID`)
                      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
    //file_to_table($myfile, $field_delimiter, $tableName,$empty_table,$addPrimaryKey,$onlySaveHasValue,$creatTableQuerry);
    
    //===================================================================================================================
    //$downloadfiles = "ftp://ftp.ncbi.nlm.nih.gov/pub/taxonomy/gi_taxid_prot.dmp.gz";
    $onlySaveHasValue = "";
    $field_delimiter = "\t";
    $myfile = $download_to."gi_taxid_prot.dmp";
    
    //------------------------------
    $tableName = "NCBI_gi_tax";
    //------------------------------
    $empty_table = "Yes";
    $addPrimaryKey = "";
    //echo "\n<br>add record to $tableName<br>\n";
    $creatTableQuerry = "CREATE TABLE IF NOT EXISTS `$tableName` (
                          `GI` varchar(20) NOT NULL default '',
                          `TaxID` int(11) NOT NULL default '0',
                          PRIMARY KEY  (`GI`)
                        ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
    
    //file_to_table($myfile, $field_delimiter, $tableName,$empty_table,$addPrimaryKey,$onlySaveHasValue,$creatTableQuerry);
   
    //===================================================================================================================
    $statusArr['processed_date_NCBI_tables'] = @date('Y-m-d H:i:s');
    write_to_status_file($statusArr);
    update_conf_file('refresh_NCBI_tables');
}

echo "\n\n<h2>end of downloading</h2>";
if($enable_lock) unlink($progressing_flag);



//-----------------------------------------------------
function write_to_status_file(&$statusArr){
  global $statusFile;
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
}
//-----------------------------------------------------

//-------------------------------------------------------------------
function connect_current_ftp_site(){
//-------------------------------------------------------------------
    global $conn_id;
    global $curr_ftp_domain;
    global $curr_ftp_username;
    global $curr_ftp_password;
     
    $conn_id = ftp_connect($curr_ftp_domain);
    
    if(!$conn_id){
        fatal_error("It cannot connect $curr_ftp_domain");
    }else{
        write_log(@date("Y-m-d H:i:s"));
        write_log("$curr_ftp_domain connected");
    }
    $login_result = @ftp_login($conn_id, $curr_ftp_username, $curr_ftp_password);
    //ftp_set_option($conn_id, FTP_TIMEOUT_SEC, 300);
    ftp_pasv($conn_id, true);
    if(!$login_result) fatal_error("ftp connected, but login incorrect");
}
//-------------------------------------------------------------------
function ftp_file_get($conn_id, $local_file, $remote_file){
//-------------------------------------------------------------------
    global $curr_ftp_domain;
    global $conn_id;
    if($conn_id)   ftp_close($conn_id);
    connect_current_ftp_site();
    
    $remote_file_size = ftp_size($conn_id, $remote_file);
    if (ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY)) {
        if(filesize($local_file) != $remote_file_size) fatal_error("File $remote_file couldn't be completely downloaded");
        $msg = "Successfully downloaded file to $local_file. size: ".$remote_file_size;
        write_log( $msg);
        echo "\n".$msg;
        ob_flush();
        flush();
        usleep(300000);
    } else {
        fatal_error("Successfully connected, but cannot get remote file $remote_file");
    }
}
//-------------------------------------------------------------------
function fatal_error($error){
//-------------------------------------------------------------------
     global $admin_email;
     global $curr_ftp_domain;
     global $curr_ftp_username;
     global $curr_ftp_password;
     global $conn_id;
     global $progressing_flag;
     
     
     if(!$conn_id and $curr_ftp_domain){
        exec( "nslookup $curr_ftp_domain", $output);
        $IP = $output[count($output)-2];
     }
     $msg = "\r\n--------<h2>prohits error report</h2>------------";
     $msg .= "\r\n$error. protein database updating stopped.";
     if(isset($IP)){
        $msg .= "\r\nCheck if the site has been blocked by your network firewall.";
        $msg .= "\r\nFTP IP $IP";
     }
     $msg .= "\r\nlogin information.";
     $msg .= "\r\nFtp domain: $curr_ftp_domain";
     $msg .= "\r\nFtp user name: $curr_ftp_username";
     $msg .= "\r\nFtp password: $curr_ftp_password";
     $msg .= "\r\nProhits Server IP Address: " . $_SERVER['SERVER_ADDR'];
     $msg .= "\r\nScript: ". $_SERVER["PHP_SELF"];
     write_log("$msg");
     send_mail($admin_email, $msg, "Prohits - Protein download problem", "prohitsAdmin", "prohitsAdmin");
     
     echo $msg;
     if(is_file($progressing_flag)) unlink($progressing_flag);
     if($error == "It cannot connect $curr_ftp_domain"){
       Manually_download_files();      
     }
     exit;
}
//-------------------------------------------------------------------
function send_mail($to, $msg,  $subject='', $from='', $replayTo=''){
//-------------------------------------------------------------------
  if(!$to or !$msg){
    echo 'need $to or $msg to send a email!';
    exit;
  }
  @mail($to, $subject, $msg, "From: $from\r\n"."Reply-To: $replayTo\r\n");
}
//-------------------------------------------------------------------
function write_log($msg){
//-------------------------------------------------------------------
    global $download_log;
    $fp_log = fopen($download_log, "a+");
    fwrite($fp_log, "\r\n$msg");
    fclose($fp_log);
}
//-------------------------------------------------------------------
function unzip_file($zipped_file){
//-------------------------------------------------------------------
   global $download_to;
   global $download_old;
   global $curr_tar_folder; //if is tar.gz file
   if(!_file_exist($zipped_file)) fatal_error("cannot unzip file $zipped_file, doesn't exist");
   //echo "$zipped_file";
   $tmp_arr = explode(".", $zipped_file); 
   if(strtolower($tmp_arr[count($tmp_arr)-1]) == "gz"){
        $unzipped_file = substr($zipped_file,0, strlen($zipped_file)-3);        
        if( strtolower($tmp_arr[count($tmp_arr)-2]) == "tar" ){
            if(_file_exist($unzipped_file)){
                if(exec("rm -f $unzipped_file 2>&1", $output)) fatal_error("cannot move file to $unzipped_file. Error:" . $output[0] );
            }
            $cmd = "gunzip $zipped_file 2>&1";
            if(exec($cmd, $output)) fatal_error("cannot unzip gz file $zipped_file, ". $output[0]);
            $cmd = "tar -xf $unzipped_file -C $download_to".$curr_tar_folder. " 2>/dev/null";
            if(exec($cmd, $output)) fatal_error("cannot unzip tar file $zipped_file, ". $output[0]);
        }else{
            if(_file_exist($unzipped_file)){
                //no output if ok
                $mv_cmd = "mv -f $unzipped_file ". str_replace($download_to, $download_old, $unzipped_file). " 2>&1";
                if(exec($mv_cmd, $output)) fatal_error("cannot move file to $unzipped_file. Error:" . $output[0] );
            }
            $cmd = "gunzip $zipped_file 2>&1";
            if(exec($cmd, $output)) fatal_error("cannot unzip gz file $zipped_file, ".$output[0]);
        }
   }
}
//-------------------------------------------------------------------
function file_to_table($myfile, $field_delimiter, $tableName,$empty_table,$addPrimaryKey,$onlySaveHasValue,$creatTableQuerry=''){
//-------------------------------------------------------------------
     
    global $db_link;
    global $NCBI_tables;
    
    if(!_file_exist($myfile)){
        fatal_error("trying to add record to $tableName from file $myfile. but the file is missing."); 
    }
    $fd = fopen ($myfile, "r");
    $start_time = @date("Y-m-d H:i:s");
    
    
    if($empty_table == "Yes" ) mysqli_query($db_link, "DELETE FROM $tableName");
    if(in_array($tableName, $NCBI_tables) and $creatTableQuerry){
      if($empty_table == "Yes"){
        mysqli_query($db_link, "DROP TABLE IF EXISTS `$tableName`"); 
      }
    }
    if($creatTableQuerry){
      mysqli_query($db_link, $creatTableQuerry);
    }else{
      echo "can not create table $tableName\n";
      return;
    }
    //get table file name
    $SQL = "select * from $tableName limit 1";
    $result = mysqli_query($db_link, $SQL);
    
    if(mysqli_error($db_link)){
        echo $SQL;
        fatal_error(mysqli_error($db_link));
    }
    $field_name_str = "";
    
    $field_name_num = mysqli_field_count($db_link);
    if($addPrimaryKey){
        $field_name_num = $field_name_num -1;
    }
    for ($i = 0; $i < $field_name_num; $i++) {
       $finfo = mysqli_fetch_field_direct($result, $i);
       if($i) $field_name_str .=",";
       $field_name_str .= $finfo->name;
    } 
    echo "\n<h2>Add records to $tableName</h2>\n";
    echo $field_name_str . "\n\n";
    $line_num = 0;
    $stop = 0;
    $new = 0;
    
    while (!feof ($fd) and $stop!=20) {
        //$stop++;
        $line_num++;
        $buffer = fgets($fd, 20000);
        $buffer = trim($buffer);
        if(strpos($buffer,"#") === 0){
            continue;
        }
        
        if(isset($onlySaveHasValue) and $onlySaveHasValue){
            if(!stristr($buffer, $onlySaveHasValue)){
                continue;
            }
        }
        if($buffer){
            $arr = explode($field_delimiter,$buffer);
            if(count($arr) != $field_name_num){
                echo "<b>file fields do not match database table</b>";
                echo "\n$buffer\n";
                exit;
            }
            $i = 0;
            if($addPrimaryKey){
                $field_value_str = "''";
            }else{
                $field_value_str = "";
            }
            foreach($arr as $value){
                if($field_value_str) $field_value_str .=",";
                $field_value_str .= "'".addslashes($value)."'";
            }
            if($new < 10) echo $field_value_str . "\n";
            $SQL = "insert into $tableName ($field_name_str) values ($field_value_str)";
            $result = mysqli_query($db_link, $SQL);
            if (!$result) {
                die('Invalid query: ' . mysqli_error($db_link));
            }else if(mysqli_affected_rows($db_link) == 1){
                $new++;
            }
            
            if($line_num%800 === 0){
                echo '.';
                ob_flush();flush();
            }
            if($line_num%80000 === 0){
                echo "\n";
                ob_flush();flush();
            }
       }
    }//end while
    $end_time = @date("Y-m-d H:i:s");
    //pclose($fd);
    echo "\n<h2>end of '$myfile' total new record = $new \n";
    echo "start time: $start_time     end time: $end_time</h2>\n";
    write_log($tableName." has been updated. $end_time");
}

function Manually_download_files(){
echo 
"<pre>
<font color='#800080'>
<B>Prohits server maybe is behind firewall. NCBI and Uniprot FTP severs cannot be accessed. You can manually download files from other computer then paste to Prohits.</b>
-------------------------------------------
1. Download file from NCBI
-------------------------------------------
ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene2accession.gz
ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene_info.gz
ftp://ftp.ncbi.nlm.nih.gov/gene/DATA/gene2go.gz
unzip the three gene* files to /Prohits/TMP/Protein_Download/
    
ftp://ftp.ncbi.nlm.nih.gov/pub/taxonomy/taxdump.tar.gz;
unzip the tax* file to /Prohits/TMP/Protein_Download/Taxonomy/

modify/add file /Prohits/admin_office/update_protein_db/download_status.txt
change or add lines (today date and time)
gene2accession.gz=2016-03-30 11:11:11
gene_info.gz=2016-03-30 11:11:11
gene2go.gz=2016-03-30 11:11:11

-------------------------------------------
2. Download files form UniProt(select organisms to download)
-------------------------------------------
ftp://ftp.uniprot.org/pub/databases/uniprot/current_release/knowledgebase/idmapping/by_organism/
ftp://ftp.uniprot.org/pub/databases/uniprot/current_release/knowledgebase/taxonomic_divisions/
unzip *tab.gz or *dat.gz files to /Prohits/TMP/Protein_Download/

modify file /Prohits/admin_office/update_protein_db/download_status.txt
change or add lines (today date and time)
e.g if you downlaoded \"HUMAN_9606_idmapping_selected.tab.gz\" add or modify following line.
HUMAN_9606_idmapping_selected.tab.gz=2016-03-30 11:11:11

-------------------------------------------
3. Process files (login Prohits)
-------------------------------------------
Prohits-->Admin Office --> Protein DB Update --> Read instruction to process downloaded files.
</font>
</pre>";
}
?> 
