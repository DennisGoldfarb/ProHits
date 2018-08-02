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
ini_set('display_errors', 1);
set_time_limit(0);
ini_set("memory_limit","-1");

$frm_removed_id = '';
$download_file = '';
$frm_decoy = '';
$num_files = 4;

$UpdateDB = 0;

include_once("../config/conf.inc.php");
include_once("../common/mysqlDB_class.php");
include_once("../common/user_class.php");
include_once("../common/common_fun.inc.php");
include_once("../analyst/common_functions.inc.php");

//-------------------------------jp 20170719------------------------------------
include_once("./update_protein_db/functions.ini.php");
include_once("./update_protein_db/auto_update_protein_add_accession.inc.php");
//------------------------------------------------------------------------------

$remove_old = 1;
$frm_collapse_evidences = 0;

session_start();
if(!isset($_SESSION['USER']) || !$_SESSION['USER']){
	echo "you did not login";exit;
} 
$msg = '';
$err_msg = '';
$theAction = '';
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}

/*echo "<pre>";
print_r($request_arr);
//print_r($_FILES);
echo "</pre>";*/

//-------------------------------jp 20170719------------------------------
$progressing_flag = './update_protein_db/lock_flag.txt';
$download_log = './update_protein_db/download.log';
$statusFile = './update_protein_db/download_status.txt';
if(!defined("GET_GENE_FROM_RUL")){
  define("GET_GENE_FROM_RUL", "0");
}
define("INSERT_NO_GENE_PROTEIN", "1");
 
//=================================================================
$download_log_tmp = './update_protein_db/download_tmp.log';
$fp_log_tmp = fopen($download_log_tmp, "a+");
//=================================================================

$removed_id_arr = explode(",", $frm_removed_id);

//---------jp added 20170719--------------------------------------------------
if($UpdateDB){
  $current_timestamp = time()."<br>";
  if(is_file($statusFile)){
    $status_fd = fopen($statusFile, "r");
    while(!feof($status_fd)){
      $buffer = fgets($status_fd, 20000);
      $buffer = trim($buffer);    
      if(!$buffer) continue;
      list($key,$value) = explode("=", $buffer);
      
      if($key == "processed_date_gene_info"){
        $last_update_date = $value;
        $tmp_arr = explode(" ", $value);
        $tmp_arr2 = explode("-", $tmp_arr[0]);
        $tmp_arr2 = array_reverse($tmp_arr2);
        $ole_date_formated = implode("-",$tmp_arr2);
        $old_time_timestamp = @strtotime($ole_date_formated );
        $diff_time_timestamp = $current_timestamp - $old_time_timestamp;
        $aday = 60*60*24;
        $passed_days = round($diff_time_timestamp/$aday);
        if($passed_days > 60) {
          $message = "The Protein DB haven't been updeted for $passed_days days. Please update Protein DB first.";
        }else{
          $UpdateDB = 0;
        }
        break;
      }
    }
    fclose($status_fd);
  }
  if($UpdateDB){
    if(!$message){
      $message = "Please update Protein DB first.";
    }
  }
}

if(!$UpdateDB){
  $message = '';
  $Prohits_proteins = PROHITS_PROTEINS_DB;
  $Prohits_proteins = 'Prohits_proteins';

  $prohitsDB = new mysqlDB(PROHITS_DB);
  $proteinDB = new mysqlDB($Prohits_proteins);
  $db_link = $proteinDB->link;
  
  $SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName like 'Protein DB Configuration%' and UserID='".$_SESSION['USER']->ID."'";
  $record = $prohitsDB->fetch($SQL);
  $perm_modify = $record['Modify'];
  $perm_delete = $record['Delete'];
  $perm_insert = $record['Insert'];
  if(!$record or !$perm_insert){
    if(!$message){
      $message = "The user has no permission to save file to Protien database.";
    }
    $UpdateDB = 1;
  }
}
//-------------------------------------------------------------------------------------------

$AccessUserID = $_SESSION['USER']->ID;

$POST_MAX_SIZE = ini_get('post_max_size');
$UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');

$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"]; 
$PHP_SELF = $_SERVER['PHP_SELF'];
$tmp_arr = explode("/",$PHP_SELF);
 
if(defined('TPP_BIN_PATH') and is_dir(TPP_BIN_PATH)){
  $decoyFASTA = TPP_BIN_PATH."/decoyFASTA";
}else{
  $decoyFASTA = $DOCUMENT_ROOT."/".$tmp_arr[1]."/EXT/TPP/bin/decoyFASTA";
}

$fasta_dir = "../TMP/modfied_fasta_files/";
$fasta_tmp_dir = "../TMP/modfied_fasta_files/tmp/";
if(!is_dir($fasta_dir)) mkdir($fasta_dir);
if(!is_dir($fasta_tmp_dir)) mkdir($fasta_tmp_dir);

$merged_file = $fasta_tmp_dir.$AccessUserID."merged_step1.fasta";
$merged_file2 = $fasta_tmp_dir.$AccessUserID."merged_step2.fasta";

if($theAction == 'download'){
  export_raw_file($download_file);
  exit;
}elseif($theAction == 'uploaded_file'){

  $protein_id_arr = array();
  $Sequence = "";
  $protein_str = ''; 
  $description = '';          
  $tmp_split_arr = array();
  $tmp_proteins_line_arr = array();

  $has_DECOY_arr = array();
  if($_FILES[0]['tmp_name']){
    $file_name_str = '';
    foreach($_FILES as $file_inf){
      if($file_name_str) $file_name_str .= " ";
      if($file_inf['tmp_name'] && is_file($file_inf['tmp_name'])){
        $file_name_str .= $file_inf['tmp_name'];
        exec("echo \"\" >> ". $file_inf['tmp_name']);
      }
    }
    $fasta_file = '';
     
    $com = "cat $file_name_str >> $merged_file";
    $last_line = system($com, $retval);

    if(!is_file($merged_file)){
      echo ("file $merged_file is missing");
      exit; 
    }    
     
    if($frm_decoy){
      if(is_file($decoyFASTA)){
        $com = "$decoyFASTA -t DECOY $merged_file $merged_file2";
        $last_line = system($com, $retval);
        if(is_file($merged_file2)){
          rename($merged_file2, $merged_file);
        }
      }else{
        echo "$decoyFASTA is not a file";
        exit;
      }
    }
        
    if(!$fp = popen("cat $merged_file", "r")) {
       echo ("file $merged_file is missing");
    }
    $line_num = 0;
    $stop =0;
    
    $tmp_file_namr_arr = explode('.',$_FILES[0]['name']);
    $fasta_file = $fasta_dir.$tmp_file_namr_arr[0].'_'.@date("Ymd").'.fasta'; 
    if(!$fp_w = fopen($fasta_file, "w")) {
       echo("file $fasta_file is missing");
    }
    $seq_num = 0;    
    echo "<DIV id='process_file'>";
    
    fwrite($fp_log_tmp, "\r\n\r\nstart add gene Info to $fasta_file.-----------------------------------\r\n\r\n");
        
    while($data = fgets($fp)){
      $data = trim($data);
      $line_num++; 
      if($line_num%900 === 0){
        echo '.';
        if($line_num%4000 === 0)  echo "$line_num\n";
        flush();
        ob_flush();
      }

      if(strpos($data,'>') === 0){
        $seq_num++;
        if(count($protein_id_arr) > 0){
          process_protein_block($fp_w);
        }    
        
        $protein_id_arr = array();
        $Sequence = "";
        $protein_str = ''; 
        $description = '';          
        $tmp_split_arr = array();
        $tmp_proteins_line_arr = array();
      
        if($removed_id_arr){
          foreach($removed_id_arr as $removed_id_val){
            $removed_id_val = trim($removed_id_val);
            if($removed_id_val && preg_match('/^\>'.$removed_id_val.'/', $data)){
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
        
//$data = ">sp|REF_HEVBR|";
//$data = ">P08311ups|CATG_HUMAN_UPS Cathepsin G heavy chain (Chain 21-255) - Homo sapiens (Human)";
//$data = ">DECOY33426"; ?????????????
//$data = ">#sp|LYSC_CHICK| Reverse sequence, was (P00698) Hen egg lysozyme C precursor";
//$data = ">#ENSP00000353797 Reverse sequence, was pep:novel chromosome:NCBI36:Y:22170624:22171520:1 gene:ENSG00000196770 transcript:ENST00000360590";    

//$data = ">sp|ALDOA_RABIT| (P00883) Rabbit fructose-bisphosphate aldolase A";       
//$data = ">sp|Q9HWK6|LYSC_PSEAE Lysyl endopeptidase OS=Pseudomonas aeruginosa (strain ATCC 15692 / PAO1 / 1C / PRS 101 / LMG 12228) GN=prpL PE=1 SV=1";        
//$data = ">NP_001263215.1|gn|BBX:56987| HMG box transcription factor BBX isoform 3 [Homo sapiens]";        
//$data = ">NP_001263215.1| HMG box transcription factor BBX isoform 3 [Homo sapiens]";
//$data = ">NP_001263215.1 HMG box transcription factor BBX isoform 3 [Homo sapiens]";       
//$data = ">REFSEQ:XP_585019 (Bos taurus) similar to afamin";
//$data = ">tr|A0A0G2JP25|A0A0G2JP25_HUMAN Leukocyte immunoglobulin-like receptor subfamily B member 4 OS=Homo sapiens GN=LILRB4 PE=1 SV=1";
//$data = ">O00762ups|UBE2C_HUMAN_UPS Ubiquitin-conjugating enzyme E2 C (Chain 1-179, N-terminal His tag)- Homo sapiens (Human)";
//$data = ">rev_P09211ups_REVERSED|GSTP1_HUMAN_UPS Glutathione S-transferase P (Chain 2-210) - Homo sapiens (Human) - REVERSED";
//$data = ">Q06830ups|PRDX1_HUMAN_UPS Peroxiredoxin 1 (Chain 2-199) - Homo sapiens (Human)";
//$data = ">Q86W50 Putative_methyltransferase_METT10D_n=2_Tax=Homo_sapiens_RepID=MET10";

//$data = ">gi|160409929|ref|YP_001551773.1|gb|EAW83415.1| control protein E4orf4 [Human adenovirus C]";
//$data = ">gi|160409929 ORF128 ankyrin repeat protein [Orf virus] ref|NP_957905.1"; 
//$data = ">gi|3195499|ref|NP_957905.1 ORF128 ankyrin repeat protein [Orf virus]"; 
//$data = ">gi|62565463 ORF128 ankyrin repeat protein [Orf virus] ref|NP_957905.1"; 
//$data = ">gi|391224047|gn|COX1:13080334| cytochrome c oxidase subunit 1 (mitochondrion) [Candida albicans]";
$data = ">gi|119603821|gb|EAW83415.1| hCG2014067 [Homo sapiens]"; 
//$data = ">gi|160409929|emb|CAI99843.1| immunoglobulin kappa light chain variable region [Homo sapiens]"; ???????
//$data = ">ENSP00000354687 pep:known chromosome:NCBI36:MT:3308:4264:1 gene:ENSG00000198888 transcript:ENST00000361390";
//$data = ">ENSP00000354 pep:known chromosome:NCBI36:MT:3308:4264:1 gene:ENSG00000198888 transcript:ENST00000361390";
//$data = ">sp|Q9Y543-2|HES2_HUMAN Isoform 2 of Transcription factor HES-2 OS=Homo sapiens GN=HES2";
//$data = ">sp|Q9Y5435-2|HES2_HUMANH Isoform 2 of Transcription factor HES-2 OS=Homo sapiens GN=HES2";
//echo "$data<br>";
$data = ">sp|Q68CZ2-2|TENS3_HUMAN Isoform 2 of Tensin-3 OS=Homo sapiens GN=TNS3";
        
        if(preg_match('/^>(DECOY.+)/', $data, $matches)){
          $protein_id_arr[] = $matches[1];
        }elseif(preg_match('/^>(.+)/', $data, $matches)){
          $protein_id_arr = array();
          
          $protein_str = '';
          $tmp_proteins_line_arr = explode(" ", $matches[1], 2); 
                   
          $protein_str = $tmp_proteins_line_arr[0];
          $description = $tmp_proteins_line_arr[1];
          $tmp_split_arr = preg_split("/gn\|.+?:/", $protein_str);
          
          $proteins_info_arr_tmp = explode("|", $tmp_split_arr[0]);
          
          for($i=0; $i<count($proteins_info_arr_tmp); $i++){
            if(strlen($proteins_info_arr_tmp[$i]) > 3){
              $protein_id_arr[] = $proteins_info_arr_tmp[$i];
            }
          }
        }
      }else{
        if($line_num > 50 and !$seq_num){
          return "<br>The file '$myfile' is not fasta file.";
        }
        if($Sequence == "skip_this_line") continue;  
        if(!preg_match("/[^A-Z]/i", $data)){
          $Sequence .= $data."\r\n";
        }
      }      
    }
    
    if(count($protein_id_arr) > 0){
      process_protein_block($fp_w);
    }   
    echo "</DIV>";
    if(isset($fp)){
      fclose($fp);
      unlink($merged_file);
    }  
    if(isset($fp_w))  fclose($fp_w);
    ob_flush(); 
  }
}
?>
<theml>
<head>
<link rel='stylesheet' type='text/css' href='../analyst/site_style.css'>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="javascript">
function submitform(){
  var theForm = document.getElementById('uploadForm');
  var theFile = document.getElementById('0');
  if(isEmptyStr(theFile.value)){
    alert("Please add first file!");
    return false;
  }
  var proecss_icon = document.getElementById('process');
  proecss_icon.style.display = 'block';
  theForm.submit();
}
function download_uploaded_file(theFile){
  var theForm = document.getElementById('uploadForm');
  theForm.theAction.value = 'download';
  theForm.download_file.value = theFile;
  theForm.submit();
}
function hide_file_process(){
  var process_obj = document.getElementById('process_file');
  process_obj.style.display = 'none';
}
</script>
</head>
<basefont face='arial'>
<body onload="hide_file_process()">
<center>
<div style='display:none' id='process'>
  <img src='../analyst/images/process.gif' border=0>
</div>
<?php 
ob_flush();
flush();
?>
<form id=uploadForm name=uploadForm method=post action=<?php echo $PHP_SELF;?> enctype="multipart/form-data">
<input type=hidden name=theAction value='uploaded_file'>
<input type=hidden name=download_file value=''>
<table width=90% border=0 cellspacing=0 cellpadding=0>
<?php if(!$theAction){?>
  <tr height=50>
    <td colspan=2>
    <span class=pop_header_text><b>Add gene and decoy sequence to fasta file </b></span><br>
    <hr width=100% size=1 noshade>
    </td>
  </tr>
<?php if($message){?>  
  <tr height=''>
    <td colspan=2>
      <font color='red'><?php echo $message;?></font>   
    </td>
  </tr> 
<?php }?>  
  <tr height=''>
    <td colspan=2>
      Instructions <a id='instruction_a' href="javascript: toggle_group_description('instruction')" class=Button>[+]</a>
    </td>
  </tr> 
  <tr>
    <td colspan=2>
    <DIV id='instruction'>
    <ul>
    <li> Upload fasta files. 
    <li> The fasta files are uploaded to Prohits protein database first</font>.
    <li> Concatenate files to one file. 
    <li> Add decoy sequences in concatenated file (optional). 
    <li> Add gene ID and gene name in concatenated file.
    <li> Download concatenated file.    
    </ul>
    </DIV>
    </td>
  </tr> 
  <tr bgcolor=white>
  	<td colspan="3">Don't upload protein ID start with >&nbsp;<input type=text size=38 name=frm_removed_id> eg. DECOY or rev
    (separate by "," if there are more than one, e.g "DECOY,rev").
    </td>
  </tr>
<?php for($i=0; $i<$num_files; $i++){?>
  <tr bgcolor=white height=25>
  	<td width='30%' bgcolor='' nowrap>
    <font face="Arial" size=2pt><b>Fasta file <?php echo ($i)?$i.'</b> (option)':''?>:</font>
    </td>
    <td width='' bgcolor=''>
    <input type=file size=45 name='<?php echo $i?>' id='<?php echo $i?>'>
    </td>
  </tr>
<?php }?>
  <tr  height=38>
    <td colspan=2 >
    <font color="#008000">Concatenate files to one file</font><br>
    <input type='checkbox' name='frm_decoy'  value='Y'">
    Add decoy sequences (identifier the prefix starts by DECOY)
    </td>
  </tr>
  <tr height=38>
    <td colspan=2>
<?php if(!$UpdateDB){?>  
    <input type=button value='Submit' onClick="submitform()">
    <input type="reset" value="Reset">
<?php }?>
    <input type="button" value='Close' onClick="window.close()";>
    </td>
  </tr>
<?php }elseif(is_file($fasta_file)){?>
  <tr>
    <td nowrap colspan=2 align=center>
      <a class=sTitle title='Download fasta file' href="javascript: download_uploaded_file('<?php echo $fasta_file;?>')"> <img src=../admin_office/images/Download-lg.png border=0></a>
      <?php echo basename($fasta_file);?>
      <input type="button" value='Close' onClick="window.close()";>
    </td>
  </tr> 
<?php }?>  
</table>
</form>
</center>   
</body>
</html>
<?php 
function process_protein_block($fp_w){          
  global $protein_id_arr;
  global $Sequence;
  global $protein_str; 
  global $description;          
  global $tmp_split_arr;
  global $tmp_proteins_line_arr;          
  global $proteinDB;
  global $fp_log_tmp;
  
  $insert = 0;        
          
  if(strstr($protein_id_arr[0], 'DECOY')){
    $new_line = '>'.$protein_id_arr[0];
  }else{
    $gene_id = '';
    $Acc_V_SET_arr = array();   
    foreach($protein_id_arr as $protein_id_val){
      $proteinType = get_protein_ID_type($protein_id_val);
      $table_field = array();
      $protein_info_arr = array();
      if($proteinType != 'NCBIAcc'){
        $table_field = _get_acc_table_fields($proteinType, $protein_id_val);
echo "<pre>";
print_r($table_field);
echo "**********************</pre>";        
        if($table_field['geneID_field'] == 'ENSG'){
          $table_field['geneID_field'] = 'EntrezGeneID';
        }
        if($table_field['match_field'] != 'Acc' && $table_field['match_field'] != 'UniProtID'){
          $order_by_str = " order by SequenceID desc limit 1";
        }else{
          $order_by_str = " order by Acc_Version desc limit 1";
        }
        $SQL = "SELECT ".$table_field['id_field'].", Description, ".$table_field['geneID_field'].$table_field['other_fields']." from ".$table_field['acc_tableName']." 
                WHERE ".$table_field['match_field']."='".$protein_id_val."' $order_by_str"; //========
        $protein_info_arr = $proteinDB->fetch($SQL);
echo "<pre>";
print_r($protein_info_arr);
echo "**********************</pre>";         
        if(!$gene_id && $protein_info_arr && $protein_info_arr[$table_field['geneID_field']]){
          $gene_id = $protein_info_arr[$table_field['geneID_field']];
        }
        
        if($table_field['match_field'] == 'Acc' && strstr($protein_id_val, '-') && !$protein_info_arr){
          $insert = 1;
          $tmp_Acc_arr = explode('-',$protein_id_val);
          if(count($tmp_Acc_arr) == 2){
            $tmp_protein_id = $tmp_Acc_arr[0];
            $SQL = "SELECT ".$table_field['geneID_field']." 
                    FROM ".$table_field['acc_tableName']." 
                    WHERE ".$table_field['match_field']."='$tmp_protein_id' 
                    AND ".$table_field['geneID_field']."!= 0
                    AND ".$table_field['geneID_field']." IS NOT NULL";
            $protein_geneid_arr_tmp = $proteinDB->fetch($SQL);
            if(isset($protein_geneid_arr_tmp[$table_field['geneID_field']])){
              $gene_id = $protein_geneid_arr_tmp[$table_field['geneID_field']]; 
            }
          }
        }
      }
  
      if(!$table_field){
        //do nothing        
      }elseif(!$protein_info_arr){
        if(!array_key_exists($table_field['match_field'], $Acc_V_SET_arr)){
          $Acc_V_SET_arr[$table_field['match_field']] = $protein_id_val;
        }
      }else{
        if(isset($protein_info_arr['Acc_Version']) && $protein_info_arr['Acc_Version']){
          if(!in_array($protein_info_arr['Acc_Version'], $protein_id_arr)){
            $protein_str = preg_replace('/^gi\|\d+\|?/', $protein_info_arr['Acc_Version'].'|', $protein_str);
          }else{
            $protein_str = preg_replace('/^gi\|\d+\|?/', '', $protein_str);
          }
        }
        if($insert == 1){
          $Acc_V_SET_arr[$table_field['match_field']] = $protein_id_val;
        }else{
          $Acc_V_SET_arr = array();
          break;
        }
      }          
    }
/*echo "<pre>";
print_r($table_field);
echo "</pre>"; */   
    if(strstr($protein_str, 'ref|')){
      $protein_str = preg_replace('/^gi\|\d+\|?/', '', $protein_str);
    }
    
    if($insert && $gene_id){
      $Acc_V_SET_arr['EntrezGeneID'] = $gene_id;
    }
 
    if(isset($Acc_V_SET_arr['Acc_Version'])){
    
      $tmpAcc_arr = explode('.',$Acc_V_SET_arr['Acc_Version']);
      $Acc = $tmpAcc_arr[0];
      $Acc_V_SET_arr['Acc'] = $Acc;
    }elseif(isset($Acc_V_SET_arr['Acc']) && (!isset($Acc_V_SET_arr['Acc_Version']) || !$Acc_V_SET_arr['Acc_Version'])){
      $Acc_V_SET_arr['Acc_Version'] = $Acc_V_SET_arr['Acc'];
    }
    $Acc_V_SET_str = '';
    foreach($Acc_V_SET_arr as $Acc_V_SET_key => $Acc_V_SET_val){
      if(preg_match('/^\W/',$Acc_V_SET_val)){
        $Acc_V_SET_str = '';
        break;
      }
      $Acc_V_SET_str .= "$Acc_V_SET_key='$Acc_V_SET_val', ";
    }
    $db_Sequence = str_replace("\r\n", "", $Sequence);
    if(!$table_field){
        //do nothing
    }elseif((!$protein_info_arr || $insert) && $Acc_V_SET_str){
//echo "insert into Sequence and Protein_Accession<br>";
      $SQL = "INSERT INTO `Protein_Sequence`
              SET `Sequence`='$db_Sequence'";
      $SequenceID = $proteinDB->insert($SQL);
      $SQL = "INSERT INTO ".$table_field['acc_tableName']." 
              SET $Acc_V_SET_str
              Description='".mysqli_escape_string($proteinDB->link, $description)."',
              SequenceID = '$SequenceID'";
      $proteinDB->execute($SQL);
      
              
//echo "$SQL<br>";
      
      if(isset($fp_log_tmp) && $fp_log_tmp){
        fwrite($fp_log_tmp, "INSERT TO db -- ".$SQL."\r\n");
      }
    }elseif($protein_info_arr && !$protein_info_arr['SequenceID']){ //============
      $update_sequence_flag = 1;
      if($table_field['match_field'] == 'Acc' || $table_field['match_field'] == 'UniProtID'){
        if($protein_info_arr['GI']){
          $match_field = 'GI';
          $match_field_val = $protein_info_arr['GI'];
        }elseif($protein_info_arr['Acc_Version']){
          $match_field = 'Acc_Version';
          $match_field_val = $protein_info_arr['Acc_Version'];
        }else{
          $update_sequence_flag = 0;
        }
      }else{
        $match_field = $table_field['match_field'];
        $match_field_val = $protein_id_val;
      }
    
      if($update_sequence_flag){
//echo "insert into Sequence and update Protein_Accession<br>";
        $SQL = "INSERT INTO `Protein_Sequence`
                SET `Sequence`='$db_Sequence'";
        $SequenceID = $proteinDB->insert($SQL);     
        $SQL = "UPDATE ".$table_field['acc_tableName']." 
                SET SequenceID = '$SequenceID'";
        if(!$protein_info_arr['Description']){        
        $SQL .= ", Description='".mysqli_escape_string($proteinDB->link, $description)."'";
        }
        $SQL .= " WHERE ".$match_field."='".$match_field_val."'";
echo "$SQL<br>"; 
       
        $proteinDB->execute($SQL);
exit;
      }
    }        
    if(count($tmp_split_arr) == 1 && $gene_id){
      $gene_name = get_Gene_Name($gene_id, $proteinDB);
      $new_gene_str = "gn|".$gene_name.':'.$gene_id."|";
      $protein_str = trim($protein_str);
      if(!preg_match("/\|$/i", $protein_str)){
        $protein_str .= "|";
      }         
      $protein_str .= $new_gene_str;
    }  
    $tmp_proteins_line_arr[0] = $protein_str;                
    $new_line = '>'.implode(" ",$tmp_proteins_line_arr);
  }         
  fwrite($fp_w,$new_line."\r\n");
  fwrite($fp_w,$Sequence);
  exit;
}            
?>
