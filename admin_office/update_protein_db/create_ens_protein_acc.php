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

/****************************************************
get Ensembl file as follows:
STEP 1: http://www.ensembl.org/index.html => 
STEP 2: BIOMAP => 
STEP 3: Choose database => 
SETP 4: Attributes => 
        Ensembl Gene ID, 
        Ensembl Protein ID, 
        Description, 
        Associated Gene Name, 
        EntrezGeneID,  
STEP 5: Results => File (TSV) =>
        Unique results only   => 
STEP 6: save to (\Prohits\TMP\Protein_Download\ENS)
        file name like line 23-26
STEP 7: modify line 23-26 than run the scrpt in browser
*******************************************************/

set_time_limit(3600 * 1);
ob_end_flush();
require("../../config/conf.inc.php");
echo "<pre>\n";
$filename_tax = "mart_export_homo.txt,  9606";
//$filename_tax = "mart_export_mus.txt, 10090";
//$filename_tax = "mart_export_rattus.txt,  10116";
//$filename_tax = "mart_export_fly.txt, 7227";
$filename_tax = "mart_export_celegan_6239.txt, 6239";



update_protein_accession($filename_tax);
//remove_duplicat_gi();




function remove_duplicat_gi(){

	
	$link = mysqli_connect(HOSTNAME, USERNAME, DBPASSWORD, PROHITS_PROTEINS_DB);
  if(!$link) {
    die('Could not connect: ' . mysqli_error());
  }
   
	
	////////////////////////////////////////////////////////////////////
	$SQL = "select count(GI), GI from Protein_Accession group by GI";
	$results = mysqli_query($link, $SQL);
	$fp_log = fopen("duplicat.gi", "w");
	while($row = mysqli_fetch_row($link)){
		if($row[0] > 1){
			fwrite($fp_log, "\r\n".$row[1]);
		}
	}
	
	fclose($fp_log);
	echo "END  of getting duplicated gi\n";
	flush();
	//exit; 
	///////////////////////////////////////////////////////////////////
	
	
	$handle = fopen("duplicat.gi", "r");
	 
	$i = 0;
	while (!feof($handle)) {
    $tmp_gi = fgets($handle);
		
	  $i++; 
		//if($i>1000) break;
		$tmp_gi = trim($tmp_gi);
		if(!$tmp_gi) continue;
		$SQL = "select ID from Protein_Accession where GI='$tmp_gi' order by ID";
		 
		$results = mysqli_query($link, $SQL);
		$first_gi = 0;
		$delete_gi_str = '';
		while($row = mysqli_fetch_row($results)){
			if(!$first_gi){
				$first_gi = 1;
			}else{
				if($delete_gi_str) $delete_gi_str .= ",";
				$delete_gi_str .= $row[0];
			}
		}
		$SQL = "delete from Protein_Accession where ID in($delete_gi_str)";
		//echo $SQL."=$tmp_gi\n";
		mysqli_query($link, $SQL);
		if($i%200===0){ 
			echo ".";
			flush();
		}
	}
	echo "end all";
}

function update_protein_accession($filename_tax){
  $ENSG_geneID_arr = array();
	$ENSP_no_GeneID_arr = array();
  list($filename,$TaxID) = explode(',',$filename_tax);
  $TaxID = trim($TaxID);
  $tmpNameArr = explode('.',$filename);
  $log = "../../TMP/Protein_Download/ENS/".$tmpNameArr[0].".log";
  $link = mysqli_connect(HOSTNAME, USERNAME, DBPASSWORD, PROHITS_PROTEINS_DB);
  if(!$link) {
    die('Could not connect: ' . mysqli_error());
  }
   
  $dir = "../../TMP/Protein_Download/ENS/";
  
  $fileFullname = $dir.$filename;
   
  if(!$handle = fopen($fileFullname, 'r')){
    echo "Cannot open file ($filename)";
    exit;
  }
  if(!$log_handle = fopen($log, 'w')){
    echo "Cannot open file ($log)";
    exit;
  }
  
  $line_num = 1;
  $falseCounter = 0;
  $okCounter = 0;
  echo "<pre>";
  $buffer = fgets($handle);
  //Ensembl Gene ID	Ensembl Protein ID	Description	Associated Gene Name	EntrezGene ID
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
    echo "<b>ERROR:</b> the file '$filename_tax' format is not corrent. 
    Please save the file as 'TSV' with following fields<br>
    Ensembl Gene ID, 
    Ensembl Protein ID, 
    Description, 
    Associated Gene Name, 
    EntrezGeneID";
    return;
  }
  print str_replace("\t", "; ",$buffer);
  echo "\nProcessing file (<b>$filename</b>) and adding to Protein_ClassENS, Protien_AccessonENS tables.
        start time: ".@date("F j, Y, g:i a")."\n";
  flush();
  
  $no_ENS = 0;
  $no_Peptide_ID = 0;
  $no_Description = 0;
  $no_GeneName = 0;
  $Protein_ClassENS_C = 0;
  $Protein_AccENS_C = 0;
  while(!feof($handle)){
    
    $buffer = fgets($handle);
    $line_num++;
    if(!$buffer) continue;
    print_dot($line_num);
    
    $recordArr = explode("\t",$buffer);
    
    $ENS = $recordArr[0];
    $Peptide_ID = trim($recordArr[1]);
    $Description = trim($recordArr[2]);
    $GeneName = trim($recordArr[3]);
    $GeneID = trim($recordArr[4]);
    
		
		 
    if(count($recordArr) < 5 or !$ENS or !$Peptide_ID){
      continue;
    }
    $AccKey = ''; 
    if(preg_match('/Acc:(.+)]/',$Description, $matches)){
      $AccKey = $matches[1];
      if(strpos($AccKey, '-') && preg_match('/(.+)?-\d+$/',$AccKey, $inner)){
        $AccKey = $inner[1];
      }
      
      if($AccKey){
        $SQL = "SELECT `EntrezGeneID` FROM `Protein_Accession` WHERE `Acc`='$AccKey' AND `EntrezGeneID` IS NOT NULL";
         
        if($result = mysqli_query($link, $SQL)){
          $row = mysqli_fetch_row($result);
					
          if($row[0]){ 
            $GeneID = $row[0];
						$ENSG_geneID_arr[$ENS] = $GeneID;
            $SQL = "SELECT GeneName FROM `Protein_Class` WHERE `EntrezGeneID`='$GeneID'";
            if($result = mysqli_query($link, $SQL)){
              $row = mysqli_fetch_row($result);
              if($row[0]) $GeneName = $row[0];
            }
          }else if($GeneName){
            $SQL = "SELECT EntrezGeneID FROM `Protein_Class` WHERE GeneName='$GeneName' and TaxID='$TaxID'";
            
						if($result = mysqli_query($link, $SQL)){
              $row = mysqli_fetch_row($result);
              if($row[0]) $GeneID = $row[0];
            }
          }
        }
      }
    }else{
      if(!$GeneID and $GeneName){
        $SQL = "SELECT EntrezGeneID FROM `Protein_Class` WHERE GeneName='$GeneName' and TaxID='$TaxID'";
        if($result = mysqli_query($link, $SQL)){
          $row = mysqli_fetch_row($result);
          $GeneID = $row[0];
        }
      }
      fwrite($log_handle, $line_num.": no Acc---".$buffer."\r\n");
    }
    $SQL = "INSERT INTO Protein_ClassENS SET
          ENSG='$ENS',
          GeneName='".mysqli_escape_string($link, $GeneName)."',
          TaxID='$TaxID'";
		 
		 
    if(mysqli_query($link, $SQL)) $Protein_ClassENS_C++;
		if(!$GeneID) array_push($ENSP_no_GeneID_arr, array("ENSG"=>$ENS,"ENSP"=>$Peptide_ID));
    $SQL = "INSERT INTO Protein_AccessionENS SET
          ENSP='$Peptide_ID',
          ENSG='$ENS',
          EntrezGeneID='".$GeneID."',
          Description='".mysqli_escape_string($link, $Description)."',
          GeneName='".mysqli_escape_string($link, $GeneName)."',
          Acc='$AccKey'";
		 
    if(mysqli_query($link, $SQL)) $Protein_AccENS_C++;
  }          
  fclose($handle);
	 
	foreach($ENSP_no_GeneID_arr as $tmp_protein){
	  if(isset($ENSG_geneID_arr[$tmp_protein['ENSG']])){
		  $GeneID = $ENSG_geneID_arr[$tmp_protein['ENSG']];
			$SQL = "update Protein_AccessionENS set EntrezGeneID='".$GeneID." 
			where ENSP='".$ENSG_geneID_arr[$tmp_protein['ENSP']]."'";
			echo $SQL;
			mysqli_query($link, $SQL);
			
		}
	}
  echo "<br>end  time: ".@date("F j, Y, g:i a")."\n";
  echo "new records in Protein_ClassENS=$Protein_ClassENS_C\n";
  echo "new records in Protein_AccENS_C=$Protein_AccENS_C\n";
} 
//----------------------------------
function print_dot($line_num){
//----------------------------------
  if($line_num%200 === 0){
      echo '.';
      if($line_num%20000 === 0){
        echo "$line_num\n";
      }
      flush();
  }
} 

