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
ini_set('display_errors', 1);



/*$time_start = time();
// Sleep for a while
usleep(2000000);
$time_end = time();
$time = $time_end - $time_start;
echo "Did nothing in $time seconds\n";

exit;*/


//default paramters
$disable_ftp="No";
$admin_email="you@youremail.com";
$NCBI_ftp_username="anonymous";
$NCBI_ftp_password="nobody@nobody.com";
$download_to="../../TMP/Protein_Download/";
$download_new_ncbi_files="No";
$download_new_uniprot_files="No";
$download_new_uniprot_mapping_files="No";
$download_new_IPI_files="No";
$refresh_NCBI_tables="No";
$NCBI_FTP="ftp.ncbi.nlm.nih.gov";
$NCBI_gene_path="/gene/DATA/";
$NCBI_tax_path="/pub/taxonomy/";
$NCBI_taxdump="taxdump.tar.gz";
$NCBI_gi_taxid="gi_taxid_prot.dmp.gz";
$NCBI_gene_file1="gene2accession.gz";
$NCBI_gene_file2="gene_info.gz";
$NCBI_gene_file3="gene2go.gz";
$NCBI_gene_file4="gene2refseq.gz";
$NCBI_gene_file5="gene_history.gz";
$UniProt_disabled="";
$UniProt_FTP="ftp.ebi.ac.uk";
$UniProt_taxonomic_divisions_path="/pub/databases/uniprot/current_release/knowledgebase/taxonomic_divisions/";
$UniProt_mapping_taxonomic_divisions_path="/pub/databases/uniprot/current_release/knowledgebase/idmapping/by_organism/";
$UniProt_dat_file_str="";
$UniProt_dat_mapping_file_str="";
 
$process_gene_info="No";
$process_gene2accession="No";
$process_nr="No";
$process_uniprot_sprot="No";
$process_uniprot_sprot_arr = array();
$process_uniprot_mapping="No";
$process_uniprot_mapping_arr = array();
$process_ipi="No";
$process_ipi_arr=array();
$Modified_By = '';
$Modified_Date = '';



$theaction = "";
$UniProt_dat_file = array();
$UniProt_dat_file_arr = array();

$UniProt_dat_mapping_file = array();
$UniProt_dat_mapping_file_arr = array();

$add_process_img = 'Yes';

require_once("../common/site_permission.inc.php");
include("./admin_header.php");
$bgcolor = "#d0e4f8";
$bgcolormid = "#89baf5";
$bgcolordark = "#466cc6";

$userFullname = $_SESSION["USER"]->Fname . " " .$_SESSION["USER"]->Lname;
$count=0;
$conf_file = "./update_protein_db/download.conf";
$statusFile = './update_protein_db/download_status.txt';

if($theaction == "runOnBackground"){
  exec("php ./update_protein_db/auto_update_protein_download.php > /dev/null &", $output);
  $theaction = "";
}

$conn_id = false;

if($theaction == "update"){
  if (!$fdout = fopen($conf_file, "w")){
     echo "Cannot open file ($filename)";
     exit;
  }  
  fwrite($fdout, "######################################################\r\n");
  fwrite($fdout, "# Prohits: Protein database update configuration file\r\n");
  fwrite($fdout, "# Modified Date: ".@date('Y-m-d')."\r\n");
  fwrite($fdout, "# Modified By: ".$userFullname."\r\n");
  fwrite($fdout, "######################################################\r\n\r\n");
  if(strtolower($disable_ftp)=='Yes'){
    fwrite($fdout, "disable_ftp=Yes\r\n\r\n");
  }else{
    fwrite($fdout, "disable_ftp=No\r\n\r\n");
  }
  fwrite($fdout, "admin_email=$admin_email\r\n\r\n");
  fwrite($fdout, "NCBI_ftp_username=anonymous\r\n");
  fwrite($fdout, "NCBI_ftp_password=nobody@nobody.com\r\n\r\n");
  fwrite($fdout, "## DESTINATION FOLDER ##\r\n"); 
  fwrite($fdout, "download_to=../../TMP/Protein_Download/\r\n\r\n");
  fwrite($fdout, "## DOWNLOAD ##\r\n"); 
  if($download_new_ncbi_files == 'Yes' and strtolower($disable_ftp)!='Yes'){
    fwrite($fdout, "download_new_ncbi_files=Yes\r\n");
  }else{
    fwrite($fdout, "download_new_ncbi_files=No\r\n");
  }
  if($download_new_uniprot_files == 'Yes' and strtolower($disable_ftp)!='Yes'){
    fwrite($fdout, "download_new_uniprot_files=Yes\r\n");
  }else{
    fwrite($fdout, "download_new_uniprot_files=No\r\n");
  }
  if($download_new_uniprot_mapping_files == 'Yes' and strtolower($disable_ftp)!='Yes'){
    fwrite($fdout, "download_new_uniprot_mapping_files=Yes\r\n");
  }else{
    fwrite($fdout, "download_new_uniprot_mapping_files=No\r\n");
  }
  if($download_new_IPI_files == 'Yes' and strtolower($disable_ftp)!='Yes'){
    fwrite($fdout, "download_new_IPI_files=Yes\r\n");
  }else{
    fwrite($fdout, "download_new_IPI_files=No\r\n");
  }
  if($refresh_NCBI_tables == 'Yes'){
    fwrite($fdout, "refresh_NCBI_tables=Yes\r\n\r\n");
  }else{
    fwrite($fdout, "refresh_NCBI_tables=No\r\n\r\n");
  }
  fwrite($fdout, "##NCBI##\r\n"); 
  fwrite($fdout, "NCBI_FTP=".trim($NCBI_FTP)."\r\n");
  fwrite($fdout, "NCBI_gene_path=".check_dir_format($NCBI_gene_path)."\r\n");
  //fwrite($fdout, "NCBI_nr_path=".check_dir_format($NCBI_nr_path)."\r\n");
  fwrite($fdout, "NCBI_tax_path=".check_dir_format($NCBI_tax_path)."\r\n\r\n");
  //fwrite($fdout, "NCBI_nr=$NCBI_nr\r\n");
  fwrite($fdout, "NCBI_taxdump=$NCBI_taxdump\r\n");
  //fwrite($fdout, "NCBI_gi_taxid=$NCBI_gi_taxid\r\n\r\n");
  fwrite($fdout, "NCBI_gene_file1=$NCBI_gene_file1\r\n");
  fwrite($fdout, "NCBI_gene_file2=$NCBI_gene_file2\r\n");
  fwrite($fdout, "NCBI_gene_file3=$NCBI_gene_file3\r\n");
  //fwrite($fdout, "NCBI_gene_file4=$NCBI_gene_file4\r\n");
	//fwrite($fdout, "NCBI_gene_file5=$NCBI_gene_file5\r\n\r\n");
  
  
  fwrite($fdout, "\r\n##UniProt ID mamping##\r\n");
  fwrite($fdout, "UniProt_disabled=$UniProt_disabled\r\n");
  fwrite($fdout, "UniProt_FTP=$UniProt_FTP\r\n");
  fwrite($fdout, "UniProt_mapping_taxonomic_divisions_path=".check_dir_format($UniProt_mapping_taxonomic_divisions_path)."\r\n");
  $uniFile_str = '';
  foreach($UniProt_dat_mapping_file as $value){
    $uniFile_str .= $value." ";
  }
  fwrite($fdout, "UniProt_dat_mapping_file_str=".$uniFile_str."\r\n\r\n");
  
  
  fwrite($fdout, "##UniProt (Swiss pro)##\r\n");
  fwrite($fdout, "UniProt_disabled=$UniProt_disabled\r\n");
  fwrite($fdout, "UniProt_FTP=$UniProt_FTP\r\n");
  fwrite($fdout, "UniProt_taxonomic_divisions_path=".check_dir_format($UniProt_taxonomic_divisions_path)."\r\n");
  $uniFile_str = '';
  foreach($UniProt_dat_file as $value){
    $uniFile_str .= $value." ";
  }
  fwrite($fdout, "UniProt_dat_file_str=".$uniFile_str."\r\n\r\n");  
  
  fwrite($fdout, "## update Protein_Class and Protein_Accession ##\r\n");
  if($process_gene_info == 'Yes'){
    fwrite($fdout, "process_gene_info=Yes\r\n");
  }else{
    fwrite($fdout, "process_gene_info=No\r\n");
  }
  if($process_gene2accession == 'Yes'){
    fwrite($fdout, "process_gene2accession=Yes\r\n");
  }else{
    fwrite($fdout, "process_gene2accession=No\r\n");
  }
  if($process_nr == 'Yes'){
    fwrite($fdout, "process_nr=Yes\r\n");
  }else{
    fwrite($fdout, "process_nr=No\r\n");
  }
  
  $uni_file_name = '';
  if($process_uniprot_sprot_arr){
    foreach($process_uniprot_sprot_arr as $value){
      if($value){
        $uni_file_name = $value;
        fwrite($fdout, "process_uniprot_sprot=$uni_file_name\r\n");
        break;
      }
    }
  }
  
  if($process_uniprot_mapping_arr){
    foreach($process_uniprot_mapping_arr as $value){
      if($value){
        $uni_file_name = $value;
        fwrite($fdout, "process_uniprot_mapping=$uni_file_name\r\n");
        break;
      }
    }
  }
  
  $ipi_file_name = '';
  if($process_ipi_arr){
    foreach($process_ipi_arr as $value){
      if($value){
        $ipi_file_name = $value;
        fwrite($fdout, "process_ipi=$ipi_file_name\r\n");
        break;
      }
    }
  }
  
  fwrite($fdout, "##end of file");
  $theaction = "";
}


if(is_file($conf_file)){
  if(!$fdin = fopen($conf_file, "r")){
    echo "Cannot open file ($conf_file)";
    exit;
  }
}

$NCBI_gene_files = array();

while (isset($fdin) and !feof($fdin)){
  $buffer = fgets($fdin, 4096);
  $buffer = trim($buffer);
  substr($buffer, 0, 1);  
  if(strstr($buffer, 'Modified By')){
    $tmpArr = explode(':', $buffer);
    $tmpArr[1] = trim($tmpArr[1]);    
    $Modified_By = $tmpArr[1];    
  }elseif(strstr($buffer, 'Modified Date')){
    $tmpArr = explode(':', $buffer);
    $tmpArr[1] = trim($tmpArr[1]);
    $Modified_Date = $tmpArr[1];       
  }else if(!$buffer || preg_match("/^#/", $buffer ) ){
    continue;
  }else{
    list($key, $value) = explode('=', $buffer);
    if(preg_match("/^NCBI_gene_file[0-9]/", $key )){
      array_push($NCBI_gene_files,$value);
    }
    $key = trim($key);
    $value = trim($value);
    $$key = $value; 
  }
  if($UniProt_dat_file_str){
    $UniProt_dat_file_arr = explode(" ", trim($UniProt_dat_file_str));
  }else{
    $UniProt_dat_file_arr = array();
  }
  if($UniProt_dat_mapping_file_str){
    $UniProt_dat_mapping_file_arr = explode(" ", trim($UniProt_dat_mapping_file_str));
  }else{
    $UniProt_dat_mapping_file_arr = array();
  }
   
}

$errorMes = '';
$login_status_arr = array();
$curr_ftp_domain = $NCBI_FTP;
$curr_ftp_username = $NCBI_ftp_username;
$curr_ftp_password = $NCBI_ftp_password;

$download_file_folder = "../TMP/Protein_Download/";
$statusArr = array();
if(is_file($statusFile)){
  if($status_fd = fopen($statusFile, "r")){
    $z = 0;
    while(!feof($status_fd)){
      $buffer = fgets($status_fd);
      $buffer = trim($buffer);
      if(!$buffer) continue;
      list($key,$value) = explode("=", $buffer);
      $statusArr[$key] = $value;
    }
  }  
  fclose($status_fd);
}
?>
<STYLE type="text/css">  
td { font-family : Arial, Helvetica, sans-serif; FONT-SIZE: 10pt;}
</STYLE>
<script language="javascript">
function ValidatePath(path){
  path.value = trimString(path.value);   
  var v = new RegExp(); 
  v.compile("^[A-Za-z0-9-_%&\?\/.=]+$"); 
  if (!v.test(path.value)) { 
      alert("You must supply a valid path.");      
      path.select(); 
      return false; 
  } 
  return true;
} 
function checkform(theForm){
  var email_str = trimString(theForm.admin_email.value);
  if(!email_str){   
    theForm.admin_email.value = "<?php echo $_SESSION["USER"]->Email;?>";
  }
  email_str = theForm.admin_email.value;
  if((email_str.indexOf(".") > 2) && (email_str.indexOf("@") > 0)){ 
    theForm.theaction.value = "update";
    theForm.submit();
  }else{
    alert("Please enter correct email address.");
  }
}
function modifyInfo(theForm){
  theForm.theaction.value = "modify";
  theForm.submit();
}
function trimString (str) {
  var str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}

function resetForm(theForm){
  theForm.reset();
}
function run_on_pop(whichScript){
  var theForm = document.getElementById("modify_form");
  if(whichScript == 'download'){ 
    file = './update_protein_db/auto_update_protein_download.php?front=Yes';
  }else{
    file = './update_protein_db/auto_update_protein_add_accession.php?front=Yes';
  } 
  thePop = window.open(file,"thewin",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=600,height=600');
  thePop.focus();
}
function run_on_background(theForm){
  return;
  theForm.theaction.value = "runOnBackground";
  theForm.submit();
}

function getToday(Field){
  <?php  $today =@time()+5*60;?>
  var Today = '<?php echo @date("Y-m-d",$today);?>';
  Field.value = Today;
}
function check_one(checkedItem){
  var theForm = document.modify_form;
  for(var i=0; i<theForm.length; i++){
    if(theForm.elements[i].type == "checkbox"){
      if(theForm.elements[i].name != checkedItem.name){
        if((checkedItem.name == 'download_new_ncbi_files' || checkedItem.name == 'download_new_uniprot_files')
            && (theForm.elements[i].name == 'download_new_uniprot_files' || theForm.elements[i].name == 'download_new_ncbi_files' )){
            //do nothing
        }else{
          theForm.elements[i].checked = false;
        }
      }
    }
  }
}
function upload_fasta(){
  <?php
  if(!processed_all_downloaded_files() and 0){
    echo "alert(\"Please process all downloaded files before uploading protein fasta files.\");";
    echo "\nreturn;\n";
  }
  ?>
  var file = './update_protein_db/upload_fasta_file.php';
  
  thePop = window.open(file,"thewin",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=600,height=600');
  thePop.focus();
}
</script>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left">
      &nbsp; <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>Protein DB Update</b></font>   
    </td>    
  </tr>
  <br>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr> 
  <tr>
    <td colspan=2><br><br>
ProHits maps all protein identification data to the NCBI Gene list, even if other databases were used to search the data.  If MS database searching was performed using non-NCBI databases, additional database information must be downloaded to ensure proper mapping.  ProHits can automatically handle data searched in UniProt/SwissProt, but manual mapping is required for other data sources at this time.
<br><br>
The protein databases for ProHits should be updated every time the search engine databases are.  When a hit is parsed to the Analyst module, ProHits retrieves gene ID (and associated information) from the specified protein database.  This page allows the ProHits administrator to download gene and protein information from database ftp sites, and save them in the ProHits protein DB. 
<br><br>
The general procedure to modify the Protein DB is to download relevant files from specified sources, then choose which tables to process in the ProHits DB.  Due to processing time, and to assist in troubleshooting, each step is performed individually (i.e. first download information, then process each of the files, one at a time).  Each time a selection is made (in the Configuration Parameters [Modify] page), select the Download and Update option at the bottom of the page ("Processing in web browser")

		<ul>
		<b>Download File</b>
	    <ol>
	    <li> Make sure that all NCBI, UniProt ftp addresses, and file names are correct.
	    <li> From this page click the 'Modify' button above the table.  Select 'Download NCBI files' and 'Download UniProt files' checkboxes.  For UniProt, select the desired taxon. You only need to download UniProt files if you are using UniProt or SwissProt for MS database searching.
	    <li> [Save] selected parameters and press [Go back] to return to the main page and download files by selecting the processing option.
      </ol>
		</ul>
		<ul>
		<b>Save File in Database</b>
			<ol>
			<li> From this page, click the 'Modify' button, then select the top active checkbox in the 'Save file in Database' section. [Save] the conf file and [Go Back] to main page.
	    <li> Select the desired processing option to parse this file to the database.  Always save files starting from the top of the list.
	    <li> Repeat steps 1 and 2 to save all files. 
	    </ol>
		</UL>
    </td>
  </tr>  
  <tr>    
  <td align="center" valign=top><br>
  <img src=./images/proteins_relation.gif border=0>
  <table border="0" cellpadding="0" cellspacing="1" width="800">
    <tr bgcolor="white"><br>
      <td align="left" colspan="2" height=20>
        <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>Configuration Parameters</b></font>   
      </td>
      <td colspan="2" align="right" height=22>
      <?php 
     $cnn_arr = creat_ftp_connect($NCBI_FTP);
     $connectMes = $cnn_arr[1];
     $error_msg = '';
     
  		if($AUTH->Modify && !$theaction){
      ?>
		     <a href="javascript: modifyInfo(document.modify_form);" class=button>[Modify]</a>    
             <!--input type="button" value="Back"  onClick="javascript: go_back();" class=green_but-->
      <?php }else if($AUTH->Modify && $theaction=="modify"){?>
        <a href="javascript: checkform(document.modify_form);" class=button>[Save]</a>
        <a href="javascript: resetForm(document.modify_form);" class=button>[Reset]</a>           
        <a href="<?php echo $PHP_SELF;?>" class=button>[Go Back]</a> 
         
      <?php }
      if(!$cnn_arr[0]){
        $error_msg =  "<b><font color='yellow'>Protein database cannot be updated, since $connectMes</font></b>";
      }
      ?>       
      </td>   
    </tr>
    <form name=modify_form method=post action='<?php echo $PHP_SELF;?>' enctype="multipart/form-data">
    <input type="hidden" name="theaction" size="55" value="">
    <input type="hidden" name="disable_ftp" value="<?php echo $disable_ftp;?>">
    <input type="hidden" name="UniProt_disabled" value="<?php echo $UniProt_disabled;?>">
    <input type="hidden" name="IPI_disabled" value="<?php echo $IPI_disabled;?>">
    <tr bgcolor="<?php echo $bgcolordark;?>">
      <td colspan="4" height=22>
        &nbsp;<b><font color="#FFFFFF">Download</font></b>
        <?php echo $error_msg;?>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22 width="150">
	      <div class=maintext><b>Modified Date</b>:&nbsp;</div>
	    </td>
	    <td width="150">
      <?php if($theaction == "modify"){?>
        <div class=maintext>&nbsp;&nbsp;<?php echo @date('Y-m-d');?></div>
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo $Modified_Date;?></div> 
      <?php }?>  
      </td>
	    <td align="right" nowrap height=22 width="150">
	      <div class=maintext><b>Modified By</b>:&nbsp;</div>
	    </td>
	    <td>
      <?php if($theaction == "modify"){?>
        <div class=maintext>&nbsp;&nbsp;<?php echo $userFullname;?></div>
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo $Modified_By;?></div> 
      <?php }?>  
      </td>
	  </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>Admin Email</b>:&nbsp;</div>
	    </td>
	    <td colspan=3>
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="text" name="admin_email" value="<?php echo $_SESSION["USER"]->Email;?>">
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo $admin_email;?></div> 
      <?php }?>  
      </td>
	     
	  </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=22>
        &nbsp;<font color="#800000"><b>NCBI:</b></font>
      </td>
    </tr>  
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>Download NCBI files</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="checkbox" name="download_new_ncbi_files" value="Yes" <?php echo ($download_new_ncbi_files=='Yes')?" checked":"";?> onclick="check_one(this)">
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo (strtolower($disable_ftp) == 'Yes')?'ftp disabled':$download_new_ncbi_files;?></div> 
      <?php }?>
      </td>
	  </tr> 
	  <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>NCBI FTP</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="text" name="NCBI_FTP" size="55" value="<?php echo $NCBI_FTP;?>" onblur="return ValidatePath(this);">
      <?php }else{?>    
        <div class=maintext>&nbsp;&nbsp;<?php echo $NCBI_FTP;?>&nbsp;&nbsp;<?php echo ($connectMes)?'(<font color=red>'.$connectMes.'</font>)':''?></div> 
      <?php }?>  
      </td>
	  </tr> 
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>NCBI Gene Path</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="text" name="NCBI_gene_path" size="55" value="<?php echo $NCBI_gene_path;?>" onblur="ValidatePath(this);">
      <?php }else{
          $NCBI_gene_path_arr = check_ftp_dir($NCBI_gene_path);
        }?>  
      </td>
	  </tr> 
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>NCBI Tax Path</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="text" name="NCBI_tax_path" size="55" value="<?php echo $NCBI_tax_path;?>" onblur="ValidatePath(this);">
      <?php }else{        
          $NCBI_tax_path_arr = check_ftp_dir($NCBI_tax_path); 
        }?>  
      </td>
	  </tr>
     
    <tr bgcolor="<?php echo $bgcolor;?>">  	  
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>NCBI taxdump</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        <input type="hidden" name="NCBI_taxdump" size="55" value="<?php echo $NCBI_taxdump;?>">         
      <?php }?> 
        <div class=maintext>&nbsp;&nbsp;<?php echo $NCBI_taxdump;?>&nbsp;&nbsp;&nbsp;
          <?php display_timestamp($NCBI_taxdump,'download')?>
        </div>
      </td>
	  </tr>
     
    <?php 
    for($i=0; $i<count($NCBI_gene_files); $i++){
    ?>
    <tr bgcolor="<?php echo $bgcolor;?>">     
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>NCBI Gene File<?php echo $i+1?></b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        <input type="hidden" name="NCBI_gene_file<?php echo $i+1?>" size="55" value="<?php echo $NCBI_gene_files[$i];?>">         
      <?php }?> 
        <div class=maintext>&nbsp;&nbsp;<?php echo $NCBI_gene_files[$i];?>&nbsp;&nbsp;&nbsp;
          <?php display_timestamp($NCBI_gene_files[$i],'download')?>
        </div> 
      </td>
	  </tr>
    <?php }?>
    
    
    
    
    
    
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=22>
        &nbsp;<font color="#800000"><b>UniProt (ID mapping):</b></font> <font color=red><?php echo ($UniProt_disabled == 'Yes')?'is disabled':'';?></font>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>Download UniProt mapping files</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="checkbox" name="download_new_uniprot_mapping_files" value="Yes" <?php echo ($download_new_uniprot_mapping_files=='Yes')?" checked":"";?> onclick="check_one(this)">
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo (strtolower($disable_ftp) == 'Yes')?'ftp disabled':$download_new_uniprot_mapping_files;?></div> 
      <?php }?>
      </td>
	  </tr> 
    <?php     
    if($conn_id !== false){
      ftp_close($conn_id);
    }
    $conn_id = false;
    $cnn_arr = creat_ftp_connect($UniProt_FTP);
    $connectMes = $cnn_arr[1];
    
    ?>
    
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>UniProt FTP</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php 
      
      if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="text" name="UniProt_FTP" size="55" value="<?php echo $UniProt_FTP;?>" onblur="ValidatePath(this);">
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo $UniProt_FTP;?>&nbsp;&nbsp;<?php echo ($connectMes)?'(<font color=red>'.$connectMes.'</font>)':''?></div> 
      <?php }?>  
      </td>
	  </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>Taxonomic Divisions Path</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="text" name="UniProt_mapping_taxonomic_divisions_path" size="90" value="<?php echo $UniProt_mapping_taxonomic_divisions_path;?>" onblur="ValidatePath(this);">
      <?php }else{
         
         $UniProt_mapping_taxonomic_divisions_path_arr = check_ftp_dir($UniProt_mapping_taxonomic_divisions_path); 
          
           
        }?> 
      </td>
	  </tr> 
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>dat_file</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify" and $conn_id){
          $tax_mapping_file_list = $contents = ftp_nlist($conn_id, $UniProt_mapping_taxonomic_divisions_path);
           
          echo "&nbsp; <select name='UniProt_dat_mapping_file[]' size='4' multiple>\n<option value=''>--select file--\n";
          if($tax_mapping_file_list){
            foreach($tax_mapping_file_list as $value){
             
              if(preg_match('/[^\/]+selected\.tab\.gz$/', $value, $matchs)){
                $selected = (in_array($matchs[0], $UniProt_dat_mapping_file_arr))? ' selected':'';
                echo "<option value='".$matchs[0]."'$selected>".$matchs[0]."\n";
              }
            }
          }
          echo "</select>\n";
        }else{
          echo "<div class=maintext>&nbsp;&nbsp;";
          foreach($UniProt_dat_mapping_file_arr as $value){
            echo "$value &nbsp; &nbsp;";
            display_timestamp($value,'download');
            echo "<br>";
          }
          echo "</div>\n";
         }
         ?><br>
      </td>
	  </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td colspan="4" height=22>
        &nbsp;<font color="#800000"><b>UniProt (Swiss-prot):</b></font> <font color=red><?php echo ($UniProt_disabled == 'Yes')?'is disabled':'';?></font>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>Download UniProt files</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="checkbox" name="download_new_uniprot_files" value="Yes" <?php echo ($download_new_uniprot_files=='Yes')?" checked":"";?> onclick="check_one(this)">
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo (strtolower($disable_ftp) == 'Yes')?'ftp disabled':$download_new_uniprot_files;?></div> 
      <?php }?>
      </td>
	  </tr> 
    
    
     
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>Taxonomic Divisions Path</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        &nbsp;&nbsp;<input type="text" name="UniProt_taxonomic_divisions_path" size="90" value="<?php echo $UniProt_taxonomic_divisions_path;?>" onblur="ValidatePath(this);">
      <?php }else{        
          $UniProt_taxonomic_divisions_path_arr = check_ftp_dir($UniProt_taxonomic_divisions_path); 
           
        }?> 
      </td>
	  </tr> 
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" nowrap height=22>
	      <div class=maintext><b>dat_file</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify" and $conn_id){
          $tax_file_list = $contents = ftp_nlist($conn_id, $UniProt_taxonomic_divisions_path);
          
          echo "&nbsp; <select name='UniProt_dat_file[]' size='4' multiple>\n<option value=''>--select file--\n";
          if($tax_file_list){
            foreach($tax_file_list as $value){
              
              if(preg_match('/uniprot_sprot_.+$/', $value, $matchs)){
                $selected = (in_array($matchs[0], $UniProt_dat_file_arr))? ' selected':'';
                echo "<option value='".$matchs[0]."'$selected>".$matchs[0]."\n";
              }
            }
          }
          echo "</select>\n";
        }else{
          echo "<div class=maintext>&nbsp;&nbsp;";
          foreach($UniProt_dat_file_arr as $value){
            echo "$value &nbsp; &nbsp;";
            display_timestamp($value,'download');
            echo "<br>";
          }
          echo "</div>\n";
         }
         ?><br>
      </td>
	  </tr>
    <tr bgcolor="<?php echo $bgcolordark;?>">
      <td colspan="4" height=22>
        &nbsp;<b><font color="#FFFFFF">Save File in Database</font></b>
        <?php  if($theaction == "modify"){?>
        <font color=yellow>select top active checkbox. If the checkbox is disabled, it 
        means the file has been saved.</font>
        <?php }?>
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">  
      <td align="right" height=22>
	      <div class=maintext><b>Refresh NCBI tables</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){
       
      ?>
        <div class=maintext>&nbsp;&nbsp;
          <input type="checkbox" name="refresh_NCBI_tables" value="Yes" <?php is_checked($refresh_NCBI_tables);?> onclick="check_one(this)" <?php echo is_disabled('gene2go.gz','processed_date_NCBI_tables')?>>&nbsp;&nbsp;
           
       
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo $refresh_NCBI_tables;?>&nbsp;&nbsp;
        <?php 
        }
        display_timestamp('processed_date_NCBI_tables','update')?><br>&nbsp;&nbsp;(empty before add new)
        </div> 
      </td>
	  </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
	    <td align="right" height=22>
	      <div class=maintext><b>Save gene Info file</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        <div class=maintext>&nbsp;&nbsp;
          <input type="checkbox" name="process_gene_info" value="Yes" <?php is_checked($process_gene_info);?> onclick="check_one(this)" <?php echo is_disabled('gene_info.gz','processed_date_gene_info')?>>&nbsp;&nbsp;
        
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo $process_gene_info;?>&nbsp;&nbsp;
      <?php }
        display_timestamp('processed_date_gene_info','update')?>
        </div> 
       
      </td>
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">
      <td align="right" height=22>
	      <div class=maintext><b>Save gene2accession file</b>:&nbsp;</div>
	    </td>
	    <td colspan="3">
      <?php if($theaction == "modify"){?>
        <div class=maintext>&nbsp;&nbsp;
        <input type="checkbox" name="process_gene2accession" value="Yes" <?php is_checked($process_gene2accession);?> onclick="check_one(this)" <?php echo is_disabled('gene2accession.gz','processed_date_gene2accession')?>>&nbsp;&nbsp;
      
      <?php }else{?>        
        <div class=maintext>&nbsp;&nbsp;<?php echo $process_gene2accession;?>&nbsp;&nbsp;
      <?php }
        display_timestamp('processed_date_gene2accession','update')?>
        </div> 
      
      </td>
	  </tr>
     
    <?php 
    $uniprot_i = -1;
    $ipi_i = -1;
    $uniprot_mapping_i = -1;
    $matches = array();
    
    foreach($statusArr as  $value_file_gz=>$date){
      $process_this = false;
      if(!preg_match("/^uniprot_sprot|^ipi|_selected\.tab\.gz$/", $value_file_gz, $matches)) continue;
      $value_file = str_replace('.gz', '', $value_file_gz);
      if($matches[0] == 'uniprot_sprot'){
        if($process_uniprot_sprot== $value_file) $process_this = true;
        $uniprot_i++;
        $i = $uniprot_i;
      }else if($matches[0] == '_selected.tab.gz'){
         
        if($process_uniprot_mapping == $value_file) $process_this = true;
        $uniprot_mapping_i++;
        $i = $uniprot_mapping_i;
        $matches[0] = "uniprot_mapping";
      
      }else{
        if($process_ipi== $value_file) $process_this = true;
        $ipi_i++;
        $i = $ipi_i;
      }
    ?>
      <tr bgcolor="<?php echo $bgcolor;?>">  
        <td align="right" height=22>
  	      <div class=maintext><?php echo $value_file;?>:&nbsp;</div>
  	    </td>
  	    <td colspan="3">
        <?php if($theaction == "modify"){?>
          <div class=maintext>&nbsp;&nbsp;
            <input type="checkbox" name="process_<?php echo $matches[0];?>_arr[<?php echo $i;?>]" value="<?php echo $value_file;?>" <?php echo ($process_this)?' checked':'';?> onclick="check_one(this)" <?php echo is_disabled($value_file_gz, "processed_date_".$value_file)?>>&nbsp;&nbsp;
             
        <?php }else{?>        
          <div class=maintext>&nbsp;&nbsp;<?php echo ($process_this)?"Yes":"";?>&nbsp;&nbsp;
        <?php }
          display_timestamp("processed_date_".$value_file,'update')?>
          </div>
        </td>
  	  </tr>
    <?php 
    }?>
       
  </form>
   
  </table>
  </td>
  </tr>
<?php if($theaction != "modify" and !$error_msg){?>
  <tr>    
  <td align="center" valign=top> 
  <br>
  <table border="0" cellpadding="0" cellspacing="1" width="800">
    <tr bgcolor="white">
      <td align="left" height=20>
        <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>Process downloading & parsing files</b></font>   
      </td>      
    </tr>
    <tr bgcolor="<?php echo $bgcolor?>">
      <td>        
        <center>
        <table border="0" cellpadding="0" cellspacing="2">
          
          <?php 
          if($refresh_NCBI_tables == "Yes" || $download_new_ncbi_files == "Yes" || $download_new_uniprot_files == "Yes" || $download_new_uniprot_mapping_files == "Yes"){
            $whichScript = 'download';
          }else{
            $whichScript = 'add_accession';
          }
          ?>
          <tr>                         
            <td><br><br>
            <input type="button" name="Processing_in_web_browser" value=" Processing in web browser " onclick="javascript: run_on_pop('<?php echo $whichScript?>');"></td>
            
          </tr>
          <tr>
            <td>This is a long processing. It will run on the web browser pop-up<br>
                window. Please keep the pop-up window open until the processing is finished.<br>
          	    <br><br><br>
          	</td>
          </tr> 
        </table>       
      </td>
    </tr>        
  </table><br>
  <table border="0" cellpadding="0" cellspacing="1" width="800">
    <tr bgcolor="white">
      <td align="left" height=20>
        <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>Upload protein fasta file to Prohits_protein database</b></font>   
      </td>      
    </tr>
    <tr bgcolor="<?php echo $bgcolor;?>">  
      <td>
    You can upload protein fasta files to add protein sequences in ProHits_protein database, after above file have been processed.<br>
    &nbsp;&nbsp; <a href="javascript: upload_fasta()">[Upload fasta file]</a>
   </td>
   </tr>
   </table>   
  </td>
  </tr>
<?php 
  }
  if($conn_id !== false){
    ftp_close($conn_id);
  }
?>  
</table>
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>
<br>
<?php 
include("./admin_footer.php");

function check_ftp_dir($path){
  global $conn_id,$errorMes;
  //$path = preg_replace('/\/$/', '', $path);
  $contents = array();
  if($conn_id){
    $contents = ftp_nlist($conn_id, $path);
    //print_r($conn_id);
    //var_dump($contents);
    if($contents === false){
      $path_Str = $path . "&nbsp;&nbsp;(<font color=red>not exist</font>)"; 
    }else{      
      if($contents){
        $path_Str = $path; 
      }else{
        $path_Str = $path . "&nbsp;&nbsp;(<font color=red>does not contain any file</font>)";
      }
    }  
  }else{
    $path_Str = $path."&nbsp;&nbsp;(<font color=red>no connection</font>)";
  }
  echo "<div class=maintext>&nbsp;&nbsp;$path_Str</div>";
  return $contents;
}      
function creat_ftp_connect($ftp_domain){
  global $curr_ftp_username;
  global $curr_ftp_password;
  global $conn_id;
  global $disable_ftp;
  global $conf_file;
  
  $message = "";
  $cnn = true;
  if(strtolower($disable_ftp) == "Yes"){
    //echo $_SERVER['SCRIPT_FILENAME'];
    
    $download_path = realpath('../TMP/Protein_Download/');
    $message = "ftp is disabled in conf file: '$conf_file'<br>. 
     Please download files to <b>$download_path</b> folder";
    if($ftp_domain != "ftp.uniprot.org"){  
      $message .= " except taxdump.tar.gz. Download file taxdump.tar.gz to <b>$download_path/Taxonomy/</b> folder";
    }  
    $message .= ". Before downloading, copy all old files to <b>$download_path/Download_old/</b> folder. <br>Please unzip files in the same folder.";
    return $message;
  }
    
  if(!$conn_id = @ftp_connect($ftp_domain, 21, 10)){
    $ftp_domain_info = dns_get_record($ftp_domain);
     
    if(!$ftp_domain_info){
      $message = " The address '$ftp_domain' cannot be found.";
    }else{
      $message = " '$ftp_domain' cannot be connected, it has been blocked by network firewall.";
    }
    $cnn = false;
  }elseif(!$login_result = @ftp_login($conn_id, $curr_ftp_username, $curr_ftp_password)){
    $message = "it can be connected, but login incorrect.";
    $cnn = false;
  }else{
    ftp_pasv ($conn_id, true) ;
  }
  
   
  return array($cnn, $message);
}
 
function last_download_time($fileName){
  if(file_exists($fileName)) {
    return "Last download time: " . @date("F d Y", filectime($fileName));
  }else{
    return '';//"Not exist in local.";
  } 
}
function display_timestamp($fileName,$type){
  global $statusArr;
  //print_r($statusArr);exit;
  if(isset($statusArr[$fileName])){
    if($type == 'download'){
      $str = "(Last download ";
    }else{
      $str = "(Last update ";
    }  
    echo $str . $statusArr[$fileName] . ')';
  }  
}
function is_disabled($download,$update){
  
  global $statusArr;
   
  
  if(!isset($statusArr[$download])){
    return "disabled";
  }else if(isset($statusArr[$update])){
    if(strcasecmp($statusArr[$update], $statusArr[$download])> 0){
      return "disabled";
    }
  }
  return '';
}
function processed_all_downloaded_files(){
  global $statusArr;
  if(!is_disabled('gi_taxid_prot.dmp.gz','processed_date_NCBI_tables')
    or !is_disabled('gene_info.gz','processed_date_gene_info')
    or !is_disabled('gene2accession.gz','processed_date_gene2accession')
  ){
    return false;
  }
  foreach($statusArr as $key=>$value){
    if(strpos($key, 'uniprot') === 0 or strpos($key, 'ipi') === 0){
      $processed_name = preg_replace("/\.gz$/",'', $key);
      $processed_name = 'processed_date_'. $processed_name;
      if(!is_disabled($key,$processed_name)){
        return false;
      }
    }
  }
  return true;
}
function is_checked($tableName){
  global $statusArr;
  if($statusArr && $tableName == 'Yes'){
    echo " checked";
  }
}
function check_dir_format($path){
  if(!preg_match("/\/$/",$path)){
    $path = $path . "/";
  }
  return $path;
}
function fatal_error($error){
//-------------------------------------------------------------------
     global $admin_email;
     global $curr_ftp_domain;
     global $curr_ftp_username;
     global $curr_ftp_password;
     global $conn_id;
     global $progressing_flag;
     
     
     if(!$conn_id){
        exec( "nslookup $curr_ftp_domain", $output);
        $IP = $output[count($output)-2];
     }
     $msg = "\r\n--------prohits error report------------";
     $msg .= "\r\n$error. protein database updating stoped.";
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
     send_mail($admin_email, $msg, "Prohis - Protein download problem", "prohitsAdmin", "prohitsAdmin");
     
     echo $msg;
     unlink($progressing_flag);
     exit;
}
?>
