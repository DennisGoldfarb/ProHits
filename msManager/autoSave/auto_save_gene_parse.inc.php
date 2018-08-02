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

function redundancy_score($array1, $array2) {

	$uniq = $check = $shared = 0;	

	for($i = 0; $i < count($array1); $i++) {
		$check=0;
		for($j = 0; $j < count($array2); $j++) {
			if($array1[$i] == $array2[$j]){
				$check = 1;
				break;
			}
		}
		if($check == 0){
			$uniq++;
		}
		else{
			$shared ++;
		}
	}

	$uniq2 = count($array2) - count($array1);

	if($uniq == 0 && $uniq2 == 0){
		$uniq = -1;
	}

	return array($uniq, $shared);

}

function gene_parse($pepXML, $fasta_db, $gene_map_file, $searchEngine='TPP', $fdr=0.01, $pep_cutoff=0.85){
	/*$pepXML = '4745_BirAFLAG_27Sept2012_combined.pepxml';
	$fasta_db = 'HEK293_RefV57_cRAPgene_20130129.fasta';
	$gene_map_file = 'gi_map_V53.csv';
	$searchEngine = 'TPP';
	$fdr = 0.01;
	$pep_cutoff = 0.85;*/
  echo "pepXML=$pepXML\nfasta_db=$fasta_db\ngene_map_file=$gene_map_file\n";
   
  
  $fasta_dir = dirname($gene_map_file);
  $pepXML_basename = basename($pepXML);
  $outfile_name = $fasta_dir."/".$pepXML_basename."_geneResults.txt"; 

	$pep_list = array();	#initialize peptide array
	$pep_file = fopen($pepXML, "r");
	if($pep_file){  
		$count_hits = -1;
    
    $Peptide_index = '';
    $SpecFile_index = '';
    $SpecProb_index = '';
    $counter = 0;    
    
   	while (($line = fgets($pep_file)) !== false) {
      $line = trim($line);
      if(!$line) continue;
      
			if($searchEngine == "TPP"){
        if(strpos($line, '<search_hit ')===0){
  				if(preg_match("/ peptide=\"([^\"]+)/", $line, $match)){
  					$count_hits ++;
  					$curr_pep = $match[1];
  					$init_peps[$count_hits] = $curr_pep;
  					$init_peps_mod[$count_hits] = $curr_pep;
  					$init_peps_decoy[$count_hits] = 0;
  					if(preg_match("/protein=\"DECOY/", $line)){
  						$init_peps_decoy[$count_hits] = 1;
  					}
  				}
        }else if(preg_match("/^<modification_info modified_peptide=\"([^\"]+)\"/", $line, $match)){
					$init_peps_mod[$count_hits] = $match[1];
				}else if(preg_match("/^<peptideprophet_result probability=\"([0-9\.]+)/", $line, $match)){
					$init_peps_prob[$count_hits] = $match[1];
				}
			}elseif($searchEngine == "MSPLIT_DDA"){
				if(preg_match("/[^\w]Peptide[^\w]/", $line, $match)){
          $title_arr = explode("\t", trim($line));
          foreach($title_arr as $key => $val){
            if($val == 'Peptide'){
              $Peptide_index = $key;
            }elseif($val == '#SpecFile'){
              $SpecFile_index = $key;
            }elseif($val == 'PepFDR'){
              $PepFDR_index = $key;
            }
          }
          if($Peptide_index === '' || $SpecFile_index === '' || $PepFDR_index === ''){
            echo "File format error__________";
            return false;
          }
          continue;   
				}      
        
        $val_arr = explode("\t", trim($line));

        $pep_length = strlen($val_arr[$Peptide_index]);
        $mod_pep = substr($val_arr[$Peptide_index], 1, $pep_length-2);
        $obs_fdr = $val_arr[$PepFDR_index];
        
				$mod_pep = substr($mod_pep, 1, $pep_length-4);
        $curr_pep = preg_replace("/[\+\-\.0-9]/", "", $mod_pep);

        if($obs_fdr <= $fdr){
//-------------------------------------------------------------------------------------------------------
//if($obs_fdr == '3.4831068E-4' || $obs_fdr == '6.5210304E-4' || $obs_fdr == '9.759271E-4') continue;
//-------------------------------------------------------------------------------------------------------
//echo "$obs_fdr<br>";
          if(array_key_exists($curr_pep, $pep_list)){
          	$pep_list[$curr_pep] += 1;
          	if(array_key_exists($mod_pep, $pep_list_mods[$curr_pep])){
          		$pep_list_mods[$curr_pep][$mod_pep] += 1;
          	}else{
          		$pep_list_mods[$curr_pep][$mod_pep] = 1;
          	}
          }else{
          	$pep_list[$curr_pep] = 1;
          	$pep_list_mods[$curr_pep][$mod_pep] = 1;
          }
        }      
			}else{     
        if(preg_match("/[^\w]Peptide[^\w]/", $line, $match)){
          $title_arr = explode("\t", trim($line));
          $Peptide_index = '';
          foreach($title_arr as $key => $val){
            if($val == 'Peptide'){
              $Peptide_index = $key;
              break;
            }
          }
          if(!$Peptide_index){
            echo "File format error__________";
            return false;
          }
          continue;   
				}
        
        $val_arr = explode("\t", trim($line));
        $mod_pep = $val_arr[$Peptide_index];
        $curr_pep = preg_replace("/\[.*?\]/", "", $mod_pep);
  			if(array_key_exists($curr_pep, $pep_list)){
  				$pep_list[$curr_pep] += 1;
  				if(array_key_exists($mod_pep, $pep_list_mods[$curr_pep])){
  					$pep_list_mods[$curr_pep][$mod_pep] += 1;
  				}else{
  					$pep_list_mods[$curr_pep][$mod_pep] = 1;
  				}
  			}else{
  				$pep_list[$curr_pep] = 1;
  				$pep_list_mods[$curr_pep][$mod_pep] = 1;
  			}
			}
		}
		fclose($pep_file);
	} else {
   	die("Unable to open .pepXML file.");
	}

	#Prob filter TPP results
	if($searchEngine == "TPP"){
		//$decoys = 0;
		//$total = 0;
		arsort($init_peps_prob, SORT_NUMERIC);
		foreach($init_peps_prob as $key => $value) {
			if($value < $pep_cutoff) {
				unset($init_peps[$key]);
				unset($init_peps_decoy[$key]);
				unset($init_peps_mod[$key]);
			}
		}

		#reset indices
 
		$init_peps = array_values($init_peps);
		$init_peps_mod = array_values($init_peps_mod);

		#get peptides passing desired FDR

		for($i = 0; $i < count($init_peps); $i++){
			$curr_pep = $init_peps[$i];
			$mod_pep = $init_peps_mod[$i];
			if(array_key_exists($curr_pep, $pep_list)){
				$pep_list[$curr_pep] += 1;
				if(array_key_exists($mod_pep, $pep_list_mods[$curr_pep])){
					$pep_list_mods[$curr_pep][$mod_pep] += 1;
				}
				else{
					$pep_list_mods[$curr_pep][$mod_pep] = 1;
				}
			}
			else{
				$pep_list[$curr_pep] = 1;
				$pep_list_mods[$curr_pep] = array();
				$pep_list_mods[$curr_pep][$mod_pep] = 1;
			}
		}
	}
  
	ksort($pep_list);
  
	#Read in database

	$prot_list = array();
	$db_file = fopen($fasta_db, "r");
	if ($db_file) {
   	while (($line = fgets($db_file)) !== false) {
//-----------------------------------------------------------------
      if(preg_match('/^>(.+)/', $line, $match)){
        $tmp_arr = explode(" ",  $match[1]);
        $protein_info_arr = explode("|", $tmp_arr[0]);
        if(count($protein_info_arr)>1){
          if(strlen($protein_info_arr[0]) > 4){
            $protein_id = $protein_info_arr[0];
          }else if(strlen($protein_info_arr[1]) > 4){
            $protein_id = $protein_info_arr[1];
          }
        }else{
          $protein_id = $protein_info_arr[0];
        }
        $prot_list[$protein_id] = '';
				$current_key = $protein_id;
      }
//------------------------------------------------------------------
			else{
				$prot_list[$current_key] .= rtrim($line);
			}
		}
		fclose($db_file);
	} else {
   	die("Unable to open fasta databse.");
	}
//echo "prot_list=".count($prot_list)."<br>";

	#Read in protein to gene map

	$gene_map = array();
	$geneid_gene = array();
	$map_file = fopen($gene_map_file, "r");
	if ($map_file) {
   	while (($line = fgets($map_file)) !== false) {
      $line = trim($line);
      $tmp_map_arr = explode(",", $line);
      $gene_map[$tmp_map_arr[2]] = $tmp_map_arr[0];
			$geneid_gene[$tmp_map_arr[0]] = $tmp_map_arr[1];
			/*if(preg_match("/^([A-Za-z_\-0-9]+),([^\,]+),([a-zA-Z0-9\]+)/", $line, $match)){
				$gene_map[$match[3]] = $match[1];
				$geneid_gene[$match[1]] = $match[2];
			}*/
		}
		fclose($map_file);
	} else {
   	die("Unable to open protein to gene map.");
	}

	#Match peptides to proteins and genes

	$pep_list_length = $pep_list_match = array();
	$pep_gene_length = $pep_gene_match = array();
	$prot_peps = $prot_pep_counts = $prot_no_peps = array();
	$prot_unique_peps = $prot_total_unique_peps = $prot_total_peps = array();
	$red_peps = array();
	$red_peps_length = 0;
	$gene_peps = $gene_pep_counts = $gene_no_peps = array();
	$gene_unique_peps = $gene_total_unique_peps = $gene_total_peps = array();
	$red_gene_peps = array();
	$red_gene_peps_length = 0;

	foreach($pep_list as $pep_key => $value) {
		# protein matching

		$pep_list_length[$pep_key] = 0;
		foreach($prot_list as $prot_key => $value2) {
			if(strpos($prot_list[$prot_key], $pep_key)) {
				$pep_list_match[$pep_key][$pep_list_length[$pep_key]] = $prot_key;
				$pep_list_length[$pep_key]++;
			}
		}
		if($pep_list_length[$pep_key] == 1){
			$prot = $pep_list_match[$pep_key][0];
			if(array_key_exists($prot, $prot_no_peps)) {
				$prot_peps[$prot][$prot_no_peps[$prot]] = $pep_key;
			}
			else{
				$prot_no_peps[$prot] = 0;
				$prot_unique_peps[$prot] = 0;
				$prot_total_unique_peps[$prot] = 0;
				$prot_total_peps[$prot] = 0;
				$prot_peps[$prot][$prot_no_peps[$prot]] = $pep_key;
			}
			$prot_pep_counts[$prot][$pep_key] = $pep_list[$pep_key];
			$prot_no_peps[$prot] += 1;
			$prot_unique_peps[$prot] += 1;
			$prot_total_unique_peps[$prot] += $pep_list[$pep_key];
			$prot_total_peps[$prot] += $pep_list[$pep_key];
		}
		else{
			$red_peps[$red_peps_length] = $pep_key;
      
			$red_peps_length++;
		}

		#gene matching

		$pep_gene_length[$pep_key] = 0;
		for($i = 0; $i < $pep_list_length[$pep_key]; $i++) {
			if(array_key_exists($pep_list_match[$pep_key][$i], $gene_map)) {
				$pep_gene_match[$pep_key][$pep_gene_length[$pep_key]] = $gene_map[$pep_list_match[$pep_key][$i]];
				$pep_gene_length[$pep_key]++;
			}
		}
     
		if($pep_gene_length[$pep_key] > 0){
			$pep_gene_match[$pep_key] = array_values(array_unique($pep_gene_match[$pep_key]));
			$pep_gene_length[$pep_key] = count($pep_gene_match[$pep_key]);
			if($pep_gene_length[$pep_key] == 1) {
				$gene = $pep_gene_match[$pep_key][0];
				if(array_key_exists($gene, $gene_no_peps)) {
					$gene_peps[$gene][$gene_no_peps[$gene]] = $pep_key;
				}
				else {
					$gene_no_peps[$gene] = 0;
					$gene_unique_peps[$gene] = 0;
					$gene_total_unique_peps[$gene] = 0;
					$gene_total_peps[$gene] = 0;
					$gene_peps[$gene][$gene_no_peps[$gene]] = $pep_key;
				}
				$gene_pep_counts[$gene][$pep_key] = $pep_list[$pep_key];
				$gene_no_peps[$gene] += 1;
				$gene_unique_peps[$gene] += 1;
				$gene_total_unique_peps[$gene] += $pep_list[$pep_key];
				$gene_total_peps[$gene] += $pep_list[$pep_key];
			}
			else {
				$red_gene_peps[$red_gene_peps_length] = $pep_key;
				$red_gene_peps_length++;
			}
		}
	}

	#Match degenerate peptides to proteins

	for($i=0; $i < $red_peps_length; $i++) {
		$un_pep_sum = $check_in_unique_list = $weight = 0;
		for($j=0; $j < $pep_list_length[$red_peps[$i]]; $j++) {
			$prot = $pep_list_match[$red_peps[$i]][$j];
			if(array_key_exists($prot, $prot_unique_peps)) {
				$un_pep_sum += $prot_total_unique_peps[$prot];
				$check_in_unique_list = 1;
			}
		}
		if($check_in_unique_list == 1) {
			for($j=0; $j < $pep_list_length[$red_peps[$i]]; $j++) {
				$prot = $pep_list_match[$red_peps[$i]][$j];
				if(array_key_exists($prot, $prot_unique_peps)) {
					$weight = $prot_total_unique_peps[$prot] / $un_pep_sum;
					$prot_peps[$prot][$prot_no_peps[$prot]] = $red_peps[$i];
					$prot_pep_counts[$prot][$red_peps[$i]] = $weight*$pep_list[$red_peps[$i]];
					$prot_no_peps[$prot]++;
					$prot_total_peps[$prot] += $weight*$pep_list[$red_peps[$i]];
				}
			}
		}
		else{
			for($j=0; $j < $pep_list_length[$red_peps[$i]]; $j++) {
				$prot = $pep_list_match[$red_peps[$i]][$j];
				$weight = 1 / $pep_list_length[$red_peps[$i]];
				if(array_key_exists($prot, $prot_no_peps)) {
					$prot_peps[$prot][$prot_no_peps[$prot]] = $red_peps[$i];
				}
				else{
					$prot_no_peps[$prot] = 0;
					$prot_total_peps[$prot] = 0;
					$prot_peps[$prot][$prot_no_peps[$prot]] = $red_peps[$i];
				}
				$prot_pep_counts[$prot][$red_peps[$i]] = $weight*$pep_list[$red_peps[$i]];
				$prot_no_peps[$prot]++;
				$prot_total_peps[$prot] += $weight*$pep_list[$red_peps[$i]];
			}
		}
	}

	#Match degenerate peptides to genes

	for($i=0; $i < $red_gene_peps_length; $i++) {
		$un_pep_sum = $check_in_unique_list = $weight = 0;
		for($j=0; $j < $pep_gene_length[$red_gene_peps[$i]]; $j++) {
			$gene = $pep_gene_match[$red_gene_peps[$i]][$j];
			if(array_key_exists($gene, $gene_unique_peps)) {
				$un_pep_sum += $gene_total_unique_peps[$gene];
				$check_in_unique_list = 1;
			}
		}
		if($check_in_unique_list == 1) {
			for($j=0; $j < $pep_gene_length[$red_gene_peps[$i]]; $j++) {
				$gene = $pep_gene_match[$red_gene_peps[$i]][$j];
				if(array_key_exists($gene, $gene_unique_peps)) {
					$weight = $gene_total_unique_peps[$gene] / $un_pep_sum;
					$gene_peps[$gene][$gene_no_peps[$gene]] = $red_gene_peps[$i];
					$gene_pep_counts[$gene][$red_gene_peps[$i]] = $weight*$pep_list[$red_gene_peps[$i]];
					$gene_no_peps[$gene]++;
					$gene_total_peps[$gene] += $weight*$pep_list[$red_gene_peps[$i]];
				}
			}
		}
		else{
			for($j=0; $j < $pep_gene_length[$red_gene_peps[$i]]; $j++){
				$gene = $pep_gene_match[$red_gene_peps[$i]][$j];
				$weight = 1 / $pep_gene_length[$red_gene_peps[$i]];
				if(array_key_exists($gene, $gene_no_peps)) {
					$gene_peps[$gene][$gene_no_peps[$gene]] = $red_gene_peps[$i];
				}
				else{
					$gene_no_peps[$gene] = 0;
					$gene_total_peps[$gene] = 0;
					$gene_peps[$gene][$gene_no_peps[$gene]] = $red_gene_peps[$i];
				}
				$gene_pep_counts[$gene][$red_gene_peps[$i]] = $weight*$pep_list[$red_gene_peps[$i]];
				$gene_no_peps[$gene]++;
				$gene_total_peps[$gene] += $weight*$pep_list[$red_gene_peps[$i]];
			}
		}
	}

	#Merge redundant genes

	$red_genes = array();	# list of genes with no unique peptides	
	$red_gene_no = 0;	# number of such genes
	foreach($gene_no_peps as $key => $value) {
		if(!array_key_exists($key, $gene_unique_peps)) {
			$red_genes[$red_gene_no] = $key;
			$red_gene_no++;
		}
	}

	$red_shared = array();				#stores information on which non-unique genes/proteins perfectly share all peptides
	$red_partial = array();			#stores which non-unique genes/proteins share some peptides
	$red_subsumed = array();			#if a gene/protein is contained within another, its entry in this hash will be 1
	$red_subsumed_list = array();	#for genes/protein that have subsumed others, the other will be listed in this hash array
	for($i = 0; $i < $red_gene_no; $i++) {
		$red_array = array();
		$red_array_l = 0;
		$gene1 = $red_genes[$i];
		for($j = 0; $j < $red_gene_no; $j++) {
			$gene2 = $red_genes[$j];
			list($red_score, $shared) = redundancy_score($gene_peps[$gene1], $gene_peps[$gene2]);
			if($red_score == 0){
				$red_array[$red_array_l] = $gene2;
				$red_array_l++;
			}
			elseif($red_score == -1 && ($gene1 != $gene2)){
				$red_shared[$gene1][] = $gene2;
			}
			elseif($shared > 0 && $gene1 != $gene2){
				$red_partial[$gene1][$gene2] = $shared;
			}
		}
		if($red_array_l > 0){
			$red_subsumed[$gene1] = 1;
			for($j = 0; $j < $red_array_l; $j++){
				$gene2 = $red_array[$j];
				$red_subsumed_list[$gene2][] = $gene1;
				if(array_key_exists($gene1, $red_subsumed_list) && count($red_subsumed_list[$gene1]) > 0) {
					$red_subsumed_list[$gene2] = array_merge($red_subsumed_list[$gene2], $red_subsumed_list[$gene1]);
				}
				for($k = 0; $k < count($gene_peps[$gene1]); $k++){
					$gene_pep_counts[$gene2][$gene_peps[$gene1][$k]] += $gene_pep_counts[$gene1][$gene_peps[$gene1][$k]]/$red_array_l;
				}
				$gene_total_peps[$gene2] += $gene_total_peps[$gene1]/$red_array_l;
			}
		}
	}

	#Remove redundant entries in subsumed list

	for($i = 0; $i < $red_gene_no; $i++) {
		$gene = $red_genes[$i];
		if(array_key_exists($gene, $red_subsumed_list)) {
			$red_subsumed_list[$gene] = array_values(array_unique($red_subsumed_list[$gene]));
		}
	}

	#Remove subsumed entries from other partial matches

	$subsumed_list = array();	#contains a list of all genes that are subsumed within others
	foreach($red_subsumed as $gene_key => $value) {
		$subsumed_list[] = $gene_key;
	}
	$subsumed_list = array_values(array_unique($subsumed_list));

	#Output summary

	$omit = array();					#array containing redundant genes to omit printing full entries for
	$curr_gene_list = array();	#array containing a list of redundant genes
	$hit_count = 0;			# counts the number of gene hits

	ksort($gene_peps);

	$outfile = fopen($outfile_name, "w");

	fwrite($outfile, "HitNumber;;Gene;;GeneID;;SpectralCount;;Unique;;Subsumed\n");
	fwrite($outfile, "Peptide;;SpectralCount;;IsUnique\n\n");
	ksort($gene_peps);
  
	foreach($gene_peps as $gene_key => $value) {
		if(!array_key_exists($gene_key, $red_subsumed) && !array_key_exists($gene_key, $omit)) {
			$hit_count++;
			$write_string = "Hit_" . $hit_count . ";;";
			$gene_string = $gene_key;
			$curr_gene_list = array();
			$curr_gene_list = array();
			$curr_gene_list[0] = $gene_key;

			#check for and print out redundant gene entries

			if(array_key_exists($gene_key, $red_shared)) {
				$gene_string .= ", ";
				sort($red_shared[$gene_key]);
				$gene_string .= implode(", ", $red_shared[$gene_key]);
				$curr_gene_list = array_merge($curr_gene_list, $red_shared[$gene_key]);
				$curr_omit = array_fill_keys($red_shared[$gene_key], 1);
				$omit = $omit + $curr_omit;
			}
			$gene_string .= ";;";

			#print Genes for GeneIDs

			for($i=0; $i < count($curr_gene_list); $i++){
				if($i > 0) {
					$write_string .= ", ";
				}
				$print_var = $geneid_gene[$curr_gene_list[$i]];
				$write_string .= $print_var;
			}
			$write_string .= ";;";
			$write_string .= $gene_string;
			$print_var = round($gene_total_peps[$gene_key]); // Print total spectral count, rounded to nearest int
			if($print_var == 0) {		// if less than one spectral count, omit hit
				$hit_count --;
				continue;
			}
			$write_string .= $print_var . ";;";

			#count unique peptides (i.e. peptides that belong to redundant or subsumed genes are also unique)

			if(array_key_exists($gene_key, $red_subsumed_list)) {
				$curr_gene_list = array_merge($curr_gene_list, $red_subsumed_list[$gene_key]);
			}

			$curr_uniq = 0;
			$curr_pep_list = $gene_peps[$gene_key];
			sort($curr_gene_list);

			for($i = 0; $i < count($curr_pep_list); $i++){
				sort($pep_gene_match[$curr_pep_list[$i]]);
				if($pep_gene_match[$curr_pep_list[$i]] == $curr_gene_list){
					$curr_uniq++;
				} 
			}
		
			$write_string .= $curr_uniq . ";;";

			#print subsumed genes

			if(array_key_exists($gene_key, $red_subsumed_list)) {
				for($i = 0; $i < count($red_subsumed_list[$gene_key]); $i++) {
					$print_var = $red_subsumed_list[$gene_key][$i];
					$write_string .= $print_var;
					if($i != count($red_subsumed_list[$gene_key]) - 1) {
						$write_string .= ", ";
					}
				}
			}
			else{
				$write_string .= "-";
			}

			$write_string .= "\n";
			fwrite($outfile, $write_string);
		
			#Print Peptides

			for($i = 0; $i < $gene_no_peps[$gene_key]; $i++){
				sort($pep_gene_match[$gene_peps[$gene_key][$i]]);
				if($pep_gene_length[$gene_peps[$gene_key][$i]] == 1 || $pep_gene_match[$gene_peps[$gene_key][$i]] == $curr_gene_list){
					$yes_no = 'yes';
				}
				else{
					$yes_no = 'no';			
				}

				foreach($pep_list_mods[$gene_peps[$gene_key][$i]] as $mod_keys => $value2) {
					$print_var = round($pep_list_mods[$gene_peps[$gene_key][$i]][$mod_keys]);	// round peptide spectral counts
					if($print_var > 0) {
						$write_string = $mod_keys . ";;" . $print_var . ";;" . $yes_no . "\n";
						fwrite($outfile, $write_string);
						$write_string = "";
					}
				}
			}
			fwrite($outfile, "\n");
		}
	}
	fclose($outfile);
	$outfile_name = $outfile_name;
	return $outfile_name;
}
?>

