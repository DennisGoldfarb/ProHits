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

require("../common/site_permission.inc.php");
require_once("msManager/is_dir_file.inc.php");

if(!$infileName || !$spliter){
  echo "no input file or no delimit.";
  exit;
}
$filename_in = $infileName;

$reportDir = "../TMP/comparison/";
if(!_is_dir($reportDir)) _mkdir_path($reportDir);

$filename_out = $reportDir.$_SESSION['USER']->Username."_osprey.txt";

$handle_write = fopen($filename_out, "w");
$handle_read = fopen($filename_in, "r");

fwrite( $handle_write, "GENE_A\tGENE_B\tSCREEN_A\tSCREEN_B\n" );

if($handle_read){
  while (!feof($handle_read)){
    $buffer = fgets($handle_read, 4096);
    $buffer = trim($buffer);
	
	if(preg_match ('/^Total samples:(\d+)/', $buffer, $matches)){
		$totalSamples = $matches[1];
	} elseif(preg_match ('/^Sample Names:(.+)/', $buffer, $matches)){
		$sampleNames = $matches[1];
		$sampleSet = explode(",,", $sampleNames );
		$sampleList = array( );
		for( $i = 0; $i < $totalSamples; $i++  ) {
			$sampleSplit = explode( "_", $sampleSet[$i] );
									
			$SQL = "SELECT `ID`,`GeneID`,`GeneName`, `BaitAcc`,`TaxID`,`Clone` FROM `Bait` 
          						WHERE `ID`='" . $sampleSplit[0] . "'";
  		$SelectedBaits = $HITSDB->fetchAll($SQL);
			
			$sampleList[$i]["ID"] = $sampleSplit[0];
			$sampleList[$i]["NAME"] = (isset($sampleSplit[1]))?$sampleSplit[1]:"";
			$sampleList[$i]["GENE_ID"] = ($SelectedBaits)?$SelectedBaits[0]['GeneID']:"";
			$sampleList[$i]["GENE_NAME"] = ($SelectedBaits)?$SelectedBaits[0]['GeneName']:"";
		}
		
	}elseif(preg_match('/^\w+,,(.+),,\d+$/', $buffer, $matches)){
		$interactors = explode( ",,", $matches[1] );
		
		for( $i = 0; $i < $totalSamples; $i++ ) {
			if( strlen( trim( $interactors[$i] ) ) > 0 ) {
				
				$geneName = $interactors[$totalSamples];
				if( strlen( $geneName ) <= 0 ) {
					$geneName = $interactors[$totalSamples+1];
				}
			
				fwrite( $handle_write, $sampleList[$i]['GENE_ID'] . "\t" . $interactors[$totalSamples+1] . "\t" . $sampleList[$i]['GENE_NAME'] . "\t" . $geneName . "\n" );
				
			}
		}
    }
	
  }
  fclose($handle_read);
  fclose($handle_write);
}

if(_is_file($filename_out)){
  header("Cache-Control: public, must-revalidate");
  //header("Pragma: hack");
  header("Content-Type: application/octet-stream");  //download-to-disk dialog
  header("Content-Disposition: attachment; filename=".basename($filename_out).";" );
  header("Content-Transfer-Encoding: binary");
  header("Content-Length: "._filesize($filename_out));
  readfile("$filename_out");
  exit();
}
?>