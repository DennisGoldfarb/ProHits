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
/************************************************
Desc: site common variables and definitions
Prohits needs following database
1. PROHITS_DB: save all user information and sample information.
used by Prohits/Analst and Prohits/Admin Office
2. MANAGER_DB: handle auto-serch and auto-save
used by Prohits/MS Data Management
3. STORAGE_DB: record raw file informaiton. It can be merged with MANAGER_DB
4. PROTEIN_DB: all protein informaiton witch download from NCBI and other
public protein databases.
*************************************************/

/************ PROHITS SERVER *****************************/
define("VERSION", '6.0.1');
define("SESSION_TIMEOUT", 8); //hours
//Prohits server needs fixed IP address.
define("PROHITS_SERVER_IP", "localhost");
define("PHP_PATH", "/usr/bin/php");
define("EMAIL2ADMIN_CONNECTION_ERROR", false);
define("ADMIN_EMAIL", "");
//prohits server will use the gmail account to send outgoing email.
//Prohits server port 465 should be opened for outgoing email.
define("PROHITS_GMAIL_USER", 'xxxxx@gmail.com');
define("PROHITS_GMAIL_PWD", '');

//JAR_COMMANT_MAX_MEMORY will be used to run java jar program. The value shold be set less than the computer total memory.
//MSDB+/MSDB. The heap size will be 5G or lesss than the value.
//java -Xmx5G -jar /path/to/MSGFPlus.jar
//DIA-Umpire and  MSPLIT will use the max value.
//java -Xmx40G -jar /path/to/DIA_Umpire_SE.jar

define("JAR_MAX_MEMORY", '34G');

/************ PROHITS MySQL DATABASES *****************************/
define("HOSTNAME", "localhost"); //mysql db host
define("USERNAME", "www");
define("DBPASSWORD", "www_password");


//don't change the database name unless you have manually changed MYSQL
//database names
define("PROHITS_DB", "Prohits");
define("MANAGER_DB", "Prohits_manager");
define("PROHITS_PROTEINS_DB", "Prohits_proteins");
define("DEFAULT_DB", PROHITS_DB);
$HITS_DB["prohits"] = PROHITS_DB;

/************* File STORGE *****************************************/
//if it allows user to upload search results file and parse to Prohits Analyst,
//set ENABLE_UPLOAD_SEARCH_RESULTS to true otherwise set to false
define("ENABLE_UPLOAD_SEARCH_RESULTS", true);
//If you do not want prohits handle raw data backup, you should set
//DISABLE_RAW_DATA_MANAGEMENT to true
define("DISABLE_RAW_DATA_MANAGEMENT", false);
//If DISABLE_RAW_DATA_MANAGEMENT" is true (Prohits will not connect search
//engines or mass spec computers), you can ignore following setting of the section.
define("STORAGE_IP", PROHITS_SERVER_IP);
//Only the file which (fileModifiedTime) - (currentTime) > FILE_COPY_DELAY_HOURS
//will be copied to storage for automatically backup.
define("FILE_COPY_DELAY_HOURS", 3);
//STORAGE_FOLDER is the main folder of the storage. it is a large folder
//to save all raw files. (end with forward slash)
define("STORAGE_FOLDER", "/var/www/html/Prohits/ProhitsStorage/");
//search results location path. It is search results storage.
//default: parent folder of GPM_CGI_PATH with '/gpm/archive/'.
define("SEARCH_ARCHIVE", STORAGE_FOLDER ."SearchResults/archive/");

//merge converted mgf and dta files to a single file. the merged file maximum size (MG) is allowed.
define("MERGE_SIZE_MAX", 1000);

//--------------- MASS SPEC RAW DATA FOLDERS ---------------
//Each mass spec (machine) data folder sould be mounted to Prohits storege.
//Please see storege mounting in installation instruction.
//A subfolder in STORAGE_FOLDER has been created as the destination folder.
//For example, a LCQ mass spectrometry has a destination folder
//"STORAGE_FOLDER / LCQ" in Prohits.
//Each BACKUP_SOURCE_FOLDERS line represent one mass spec machine.
//The ms machine name should have only A-Z and 0-9.
//Raw files will be copyed to 'STORAGE_FOLDER/msMachineName' folder;
//Raw file source folder is mounted from mass spec machine computer folder
//which contains raw files;
//$BACKUP_SOURCE_FOLDERS can be mantained by 'Backup Setup' tool in Admin Office.
//e.g.
//$BACKUP_SOURCE_FOLDERS[msMacineName]=
//  array(
//    'SOURCE'=>'mounted_raw_file_source_folder_path',
//    'DEFAULT_PROJECT_ID'=>'default_project_id',
//    'SOURCE_COMPUTER'=>
//        array(
//         'ADDRESS'=>'LTQ10269.ad.mshri.on.ca',
//         'RAW_DATA_FOLDER'=>'MSdata',
//         'SHARED_TO_USER'=>'msusers',
//         'SHARED_TO_USER_PASSWD'=>'xxxxx',
//         'WINDOWS_ACTIVE_DIRECTORY'=>'slri_lan1'
//       )
//  );
//SOURCE_COMPUTER is raw data aquresition computer.
//if ['SOURCE'] is empty, it means no backup needed but user can upload raw files.
//login as sudo user to test if raw file folders can be mounted. run following command in "Prohits/msManager/" directory
//# sudo php auto_run_shell.php connect
//
//login Admin Office --> backup Setup --> To add a new instrument.
$BACKUP_SOURCE_FOLDERS['LTQ_DEMO'] = array('SOURCE'=>'', 'DEFAULT_PROJECT_ID'=>'1');


//***************** SEARCH ENGINE SERVERS ****************************************
//---------define the local search engine full path--------------
//It will run search locally,when set "GPM_IP" as  "localhost".
define("TPP_BIN_PATH", "/var/www/html/Prohits/EXT/TPP/bin");
define("PHILOSOPHER_BIN_PATH", "/var/www/html/philosopher/v1.5/");
define("GPM_CGI_PATH", "/var/www/html/Prohits/EXT/thegpm/thegpm-cgi");
define("COMET_BIN_PATH", "/var/www/html/Prohits/EXT/comet/comet_binaries_2014022");
define("MSFRAGGER_BIN_PATH", "/var/www/html/Prohits/EXT/MSFragger/MSFragger_20170103");
define("MSGFPL_BIN_PATH", "/var/www/html/Prohits/EXT/MSGFPlus/MSGFPlus.20140630");
define("MSGFDB_BIN_PATH", "/var/www/html/Prohits/EXT/MSGFDB/MSGFDB.20120607");
define("SAINT_SERVER_PATH", "/var/www/html/Prohits/EXT/Prohits_SAINT/SAINT_v2.5.0/bin");
define("SAINT_SERVER_EXPRESS_PATH", "/var/www/html/Prohits/EXT/Prohits_SAINT/SAINTexpress_v3.3/bin");
define("MAP_DIA_BIN_PATH", "/var/www/html/Prohits/EXT/mapDIA/mapDIA_v2.0.5/bin");
define("DIAUMPIRE_BIN_PATH", "/var/www/html/Prohits/EXT/DIAUmpire/DIA-Umpire_v1_4273");
define("MSPLIT_JAR_PATH", "/var/www/html/Prohits/EXT/MSPLIT-DIA/MSPLIT-DIAv1.0/MSPLIT-DIAv02102015.jar");
define("PROTEOWIZARD_BIN_PATH", "/var/www/html/Prohits/EXT/pwiz-bin");

//----------------Prohits only support SGE cluster--------------------------
define("QSUB_BIN_PATH", ""); //if no OGSG cluster, set it empty
define("QSUB_USER", ""); //SGE user without password from apache user. If SGE user is apache, set it empty.
define("QSUB_TIMEOUT_HRS", 20);
putenv ("SGE_ROOT=/opt/gridengine");
putenv ("SGE_QMASTER_PORT=536");
putenv ("SGE_EXECD_PORT=537");

define("NUM_THREAD", 24); //for search engines: comet, xtandem...
define("PROHITS_TMP", "/tmp/"); //copy mzXML to Prohits_tmp for DiaUmpireSE speed up.


//theGPM and TPP have to be in the same Linux computer (Apache web server). If you use different
//ServerNames (VirtualHost) the "ServerName" should be used for GPM_IP and TPP_IP.
define("MASCOT_IP", ""); //set it empty if no Mascot to be connected.
//if no security setup define("MASCOT_USER", "");
define("MASCOT_USER", "prohits");
//if no security setup define("MASCOT_PASSWD", "");
define("MASCOT_PASSWD", "prohits");
define("MASCOT_CGI_DIR", "/mascot/cgi");

define("GPM_CGI_DIR", "/thegpm-cgi");
define("SEQUEST_IP", "");
define("SEQUEST_CGI_DIR", "/Prohits_SEQUEST");
//TPP IP should be the same as GPM IP

define("TPP_CGI_DIR", "/tpp/cgi-bin");

define("PREFERRED_FILE_TYPE", "mzML"); //mzML/mzXML for search engine. other type will be converted to this type.
define("RAW_CONVERTER_SERVER_PATH", "http://yourRawConverterIP/RawConverter/RawConverter.pl");
define("TPP_PEPTIDE_RESULTS_PER_PAGE", 1000);
define("TPP_PEPTIDE_MAX_PAGES", 20);
define("TPP_PARSE_MIN_PROBABILITY", 0.05);
define("TPP_DISPLAY_MIN_PROBABILITY", 0.05);
define("BIOGRID_URL", "https://thebiogrid.org/idQuery.php");

//**** PLEASE DO NOT CHANGE FOLLOWING SETTING **************************************
//set to 1 in debug mode. 
//go to the web page to get a command line then run the command line in shell.
define("DEBUG_SEARCH", 0);
define("DEBUG_TPP", 0);
define("DEBUG_BACKUP", 0);
define("DEBUG_CONVERTER", 0);
define("DEBUG_SAINT", 0);
define("DEBUG_SAVE_HITS", 0);
define("DEBUG_DIAUmpireQuant", 0);
define("DEBUG_FTP_EXPORT", 0);

// color variables -------------------
$TB_HD_COLOR = "#637eef";
$TB_CELL_COLOR = "#d2dcff";
$TB_CELL_GRAY = "#cfcfcf";
$TB_CELL_DARK_GRAY = "#999999";
//filter-------------------------------------
define("ID_MANUALEXCLUSION", "ME");//--6
define("ID_REINCLUDE", "RI");//--7
define("ID_ONEPEPTIDE", "OP");//--8
define("ID_BAIT", "BT"); //--13
define("ID_SCFAMILY25", 21);
define("DEFAULT_EXPECT_EXCLUSION", 20);
//prohits will working with those raw file formats. 
//raw files have one of following extentions.
//more formats should be added here if needed.
//no case sensitive.
//make sure that Mascot and GPM both support those formats, if you want to user both search engines.
$RAW_FILES = "RAW, dta, mgf, mzData, mzXML, mzXML.gz, PKL, WIFF, mzML, mzML.gz, mgf.gz, SCAN";

//----------------------------------------
//public links
$URLS["MASSIVE_IP"]         = 'massive.ucsd.edu';
$URLS["ENTREZ_GENE"]        = "http://www.ncbi.nlm.nih.gov/gene/";
$URLS["NCBI_PROTEIN"]       = "http://www.ncbi.nlm.nih.gov/protein/";
$URLS["NCBI_PROTEIN_FASTA"] = "http://www.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?rettype=fasta&retmode=text&db=protein&id=";
$URLS["NCBI_PROTEIN_GENPEPT"] = "http://www.ncbi.nlm.nih.gov/sviewer/viewer.fcgi?sendto=on&dopt=genpept&val=";
$URLS["BIOGRID"]            = "http://www.thebiogrid.com/search.php?geneID&keywords=";
$URLS["ENS_PROT_SEQUENCE"]  = array("domain"=> "http://www.ensembl.org/","cgi"=>"/Component/Transcript/Web/ProteinSeq?p=");
$URLS["ENS_PROT"]           = array("domain"=>"http://www.ensembl.org/", "cgi"=>"/Transcript/ProteinSummary?db=core;p=");
$URLS["UNIPROT_PROTEIN"]    = "http://www.uniprot.org/uniprot/";
$URLS["UNIPROT_PROTEIN_dat"] = "http://srs.ebi.ac.uk/srsbin/cgi-bin/wgetz?[TYPE:PROTEINKEY]+-e+-vn+2";
$URLS["UNIPROTkb_PROTEIN_dat"] = "http://www.uniprot.org/uniprot/";
$URLS["IPI_PROTEIN_dat"]    = $URLS["UNIPROT_PROTEIN_dat"];
$URLS["WORM_PROTEIN"]       = "http://www.wormbase.org/db/seq/protein?name=";
$URLS["IPI_PROTEIN"]        = "http://www.ebi.ac.uk/cgi-bin/dbfetch?db=IPI&id=";
$URLS["JBIRC_PROTEIN"]      = "http://www.jbirc.aist.go.jp/hinv/soup/pub_Detail.pl?acc_id=";
$URLS["TIGR_PROTEIN"]       = "http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=ath1&orf=";
$URLS["TIGRTB_PROTEIN"]     = "http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=tba1&orf=";
$URLS["ENS_GENE"]           = array("domain"=>"http://www.ensembl.org/", "cgi"=>"/Gene/Summary?db=core;g");
$URLS["SGD"]                = "http://www.yeastgenome.org/cgi-bin/locus.fpl?locus=";
$URLS["EBI_TAGE"]           = "http://www.ebi.ac.uk/ols/ontologies/mi/terms?iri=http://purl.obolibrary.org/obo/";
?>