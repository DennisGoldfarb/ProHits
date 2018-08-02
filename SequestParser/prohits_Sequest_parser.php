<?php 
$selected_dir_str = '';
$tmp_file_name = '';
$selected_peptide_str = '';
$no_group_rank = 0;
$HitNumber = 0;

$selected_dir_str = '';

$is_break = 0;
$max_list = 0;
$BP_mode = 'fldTIC';
$mass_unit = 'ppm';
$filterdupepep = '';
$ppmrecal = "0.0";
$groupsortby = 'TIC';

$notnew = '';
$auto_select = 1;
$min_error = -8;
$max_error = 8;
$peptide_rank = 11;
$MAX_RANK= 2;
$prefie = array();
$frm_dir_selected = '';

$n = array(); //hash
$selectedentry = array();

$filter = array(
  "filterdescription" => '',
	"Group_Peptides" => 2,
	"filterIons2" => '',
	"filterIons1" => '',
	"filterRSp4" =>'', 
	"filters" => array('Cull','Default'),
	"filterSp2" => '',
	"filterandor" => 'or',
	"filterSf5" => 0.89,
	"auto_select" => 'on',
	"filterdCn4" => '',
	"filterRSp2" => '',
	"filter_type" => 'Default',
	"filterP5" => 90,
	"filter_action" => 'SELECT',
	"cleavageorand" => 'and',
	"filterSf2" => 0.89,
	"filterScoresgtlt" => 'gt',
	"name" => 'Default',
	"filterXCorr4" => '',
	"filterP4" => 90,
	"filterIons4" => '',
	"filterz4" => '',
	"filterP3" => 90,
	"filterIons5" => '',
	"filterdCn3" => '',
	"filterz5" => '',
	"filterdCn1" => '',
	"filterSf4" => 0.89,
	"filterz2" => '',
	"filterSp4" => '',
	"filtersequence" => '',
	"filterdCn5" => '',
	"filterz1" => '',
	"filterP1" => 80,
	"filterXCorr1" => '',
	"filterXCorr3" => '',
	"filterSp1" => '',
	"filterdCn2" => '',
	"filterRSp1" => '',
	"filterXCorr2" => '',
	"NoGroup_Peptides" => 2,
	"use_filter" => 1,
	"filterRSp3" => '',
	"filterSp3" => '',
	"microchem" => '',
	"filterSf3" => 0.89,
	"Group_Sf" => 1.1,
	"filterfile" => 'C:/InetPub/etc/config/summaryfilter.xml',
	"NoGroup_Sf" => 0.75,
	"filterP2" => 90,
	"filterSp5" => '',
	"filterRSp5" => '',
	"filterz3" => '',
	"filterXCorr5" => '',
	"filterIons3" => '',
	"filterSf1" => 0.84,
);

$DEFS_RUNSUMMARY = array(
  'Show/Sort by' => 'Consensus',
	'Top TIC percentage' => 25,
	'MH+ Precision' => 4,
	'Export to' => 'XML',
	'Max rank' => 2,
	'Delta Mass Unit' => 'ppm',
	'Auto Refresh Period' => 180,
	'Sort field within a consensus group' => 'Sf',
	'Max No Group list' => 100,
	'Consensus group score threshold' => 0.75,
	'Number of Descriptions' => 100,
	'DeltaCN threshold' => 0.1,
	'Consensus group sort by' => 'TIC',
	'Consensus SF bold threshold' => 1.0,
	'Consensus group view' => 'collapsed',
	'Number of sequences bold threshold' => 2,
	'Max list' => 1500,
	'Max display rank' => 4,
	'XCorr threshold' => 2.5,
	'Consensus groups have at least one top ranked hit' => 1,
	'Sp threshold' => 600,
	'P threshold' => 13,
	'Nucleotide contaminant database' => 'contaminants_nuc.fasta',
	'RSp threshold' => 2,
	'Sf threshold' => 0.80,
	'Pull to Top' => 'yes',
	'Top TIC list' => 500,
	'Protein contaminant database' => 'contaminant');
  
$Mono_mass = array(
  'S' => 87.03202840,
	'T' => 101.04767846,
	'N' => 114.04292744,
	'K' => 128.09496300,
	'Oxygen' => 15.994914622,
	'Y' => 163.06332852,
	'E' => 129.04259308,
	'Z' => 128.55058529,
	'OH' => 17.0027396541,
	'J' => 118.806,
	'W' => 186.07931294,
	'Ammonia' => 17.02655,
	'B' => 114.53493523,
	'H' => 137.05891186,
	'D' => 115.02694302,
	'I' => 113.08406396,
	'G' => 57.02146372,
	'U' => 118.806,
	'F' => 147.06841390,
	'Moxidized' => 147.035399212,
	'V' => 99.06841390,
	'Q' => 128.05857750,
	'AA' => 'Mono',
	'Proton' => 1.0072764522,
	'M' => 131.04048459,
	'C' => 103.00918447,
	'L' => 113.08406396,
	'A' => 71.03711378,
	'O' => 114.07931,
	'X' => 113.08406,
	'Electron' => 0.0005485799,
	'P' => 97.05276384,
	'Water' => 18.0105646862,
	'R' => 156.10111102,
  'Hydrogen' => 1.0078250321
);

$num_char_arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
require "../config/conf.inc.php";
require "../common/mysqlDB_class2.php";
require "../common/common_fun.inc.php";
require "../msManager/is_dir_file.inc.php";
ini_set("memory_limit","-1");


if(!isset($_SERVER['argv'][1]) || !$_SERVER['argv'][1]){
  echo "not data folder";
}

/*echo "<pre>";
print_r($_SERVER['argv']);
echo "</pre>";*/

$data_folder = $_SERVER['argv'][1];
$MAX_RANK = (isset($_SERVER['argv'][2]))?$_SERVER['argv'][2]:2;
$BP_mode = (isset($_SERVER['argv'][3]))?$_SERVER['argv'][3]:'fldTIC';
$mass_unit = (isset($_SERVER['argv'][4]))?$_SERVER['argv'][4]:'ppm'; 

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
  
$full_data_file_name = $data_folder.".dat";
if(!$data_handle = @fopen($full_data_file_name, "w")){
  echo "Cannot open data file $full_data_file_name.\r\n";
  exit(1);
}
//if($MAX_RANK <= 4){
  //$peptide_rank = 4;
//}else{
$peptide_rank = $MAX_RANK;
//} 
$dMlabel = ($mass_unit == "ppm") ? "ppm" : "dM";
//global------
$plain_display = 0;
$consensus_groupings = array(); //array
$consensus_group = array();     //hash
$FORM_arr = array();              //hash

//***************************************

$options = array(
  'groupsortby' => 'TIC',
	'filter' => '',
	'differentdescrip' => '',
	'sort' => 'consensus',
	'ingroupsort' => 'Sf',
	'group_score_threshold' => 1.1,
	'toprankhit' => 1,
	'group_sequences_threshold' => 2,
	'sequences_threshold' => 2,
	'score_threshold' => 0.75);

//$file_root_dir = "../TMP/sequest_data2/";

$dir_arr[0] = $data_folder;
$dir_arr_detail = array();

$files_arr = array();
$location_arr = array();
$ref_arr = array();

for($a=0; $a<count($dir_arr); $a++){
  $out_dir = $dir_arr[$a];
  $retention_file = $out_dir."/retention.txt";
  $lcq_profile = $out_dir."/lcq_profile.txt";
  $lcq_chro = $out_dir."/lcq_chro.txt";

  $retention_arr = array();
  $zBP_arr = array();
  $fBP_arr = array();
  $TIC_arr = array();
  $maxBP_arr = array();
  
  $tmp_dir_arr['directory'] = $out_dir;
  $tmp_dir_arr['dir_num'] = $a;
  if(isset($options)){
    $tmp_dir_arr['directory_info'] = '';
    $tmp_dir_arr['LogInfo'] = '';
    $tmp_dir_arr['filter'] = $options['filter'];
    $tmp_dir_arr['dtanumber'] = 0; 
    $tmp_dir_arr['BP'] = (isset($options['BPMode']))?"fld".$options['BPMode']:'fldTIC';
  }
  $dir_arr_detail[$a] = $tmp_dir_arr;
  
  /*$handle = @fopen($retention_file, "r");
  if($handle){
    while(($buffer = fgets($handle, 4096)) !== false){
      trim($buffer);
      $pieces = explode("\t", $buffer);
      $retention_arr[trim($pieces[0])] = trim($pieces[1]);
    }
    fclose($handle);
  }else{
    echo "Cannot open file $retention_file\r\n";
    exit(1);
  }
  
  $handle = @fopen($lcq_profile, "r");
  if($handle){
    while(($buffer = fgets($handle, 4096)) !== false){
      trim($buffer);
      $pieces = explode(" ", $buffer);
      $file_index = trim(basename($pieces[0], ".dta"));
      $fBP_arr[$file_index] = $pieces[2];
      $zBP_arr[$file_index] = $pieces[4];
      $TIC_arr[$file_index] = $pieces[5];
    }
    fclose($handle);
  }else{
    echo "Cannot open file $lcq_profile\r\n";
    exit(1);
  }
  
  $handle = @fopen($lcq_chro, "r");
  if($handle){
    while(($buffer = fgets($handle, 4096)) !== false){
      trim($buffer);
      $pieces = explode(" ", $buffer);
      $file_index = trim(basename($pieces[0], ".cta"));
      $maxBP_arr[$file_index] = trim($pieces[3]);
    }
    fclose($handle);
  }else{
    echo "Cannot open file $lcq_chro\r\n";
    exit(1);
  }*/
  
  $dir_handle = opendir($out_dir);
  if(!$dir_handle){
    echo "Cannot open directory $out_dir\r\n";
    exit(1);
  }
  
  $sample_fname_arr = array();
  $database_arr = array();
  $rundate_arr = array();
  $diffmod_arr = array();
  $Directory = '';
  $Enzyme_arr = array();
  $Mass_arr = array();
  $Mass_type_arr = array();
  $fileNum = 0;
  while (false !== ($file = readdir($dir_handle))){
  
    /*if(preg_match("/\.dta$/i", $file)){
      $dir_arr_detail[$a]['dtanumber']++;
		}*/ 
    if(preg_match("/\.out$/i", $file)){
      $dir_arr_detail[$a]['dtanumber']++;
		} 
        
    if(preg_match("/(.+?\.(\d+)\.(\d+)\.(\d+))\.out$/i", $file, $matches)){
      $file_index = trim($matches[1]);
      $tmp_file_info_arr = array();
      $tmp_file_info_arr['fileName'] = $file_index;
      $tmp_file_info_arr['fileNum'] = ++$fileNum;
      
      $tmp_file_info_arr['fldTIC'] = (isset($TIC_arr[$file_index]) && $TIC_arr[$file_index])?$TIC_arr[$file_index]:0;
      $tmp_file_info_arr['fldMaxBP'] = (isset($maxBP_arr[$file_index]) && $maxBP_arr[$file_index])?$maxBP_arr[$file_index]:0;
      $tmp_file_info_arr['fldFBP'] = (isset($fBP_arr[$file_index]) && $fBP_arr[$file_index])?$fBP_arr[$file_index]:0;
      $tmp_file_info_arr['fldZBP'] = (isset($zBP_arr[$file_index]) && $zBP_arr[$file_index])?$zBP_arr[$file_index]:0;        
      
      $tmp_file_info_arr['Scan'] = ($matches[2]==$matches[3])?$matches[2]:$matches[2]."-".$matches[3];
      $tmp_file_info_arr['RT'] = (isset($retention_arr[$file_index]))?$retention_arr[$file_index]:0;
      $tmp_file_info_arr['z'] = $matches[4];
      $info_array = array();
      //$options = array();
      $outfile =  $out_dir."/".$file;
      $has_proteins = read_seqoutfile($info_array, $outfile, $options);
      if(!$has_proteins) continue;      
      if(!in_array($info_array['samplefilename'], $sample_fname_arr)) array_push($sample_fname_arr, $info_array['samplefilename']);
      if(!in_array($info_array['database'], $database_arr)) array_push($database_arr, $info_array['database']);
      if(!in_array($info_array['runinfo']['rundate'], $rundate_arr)) array_push($rundate_arr, $info_array['runinfo']['rundate']);
      if(!in_array($info_array['modifications']['diffmod'], $diffmod_arr)) array_push($diffmod_arr, $info_array['modifications']['diffmod']);
      if(!$Directory) $Directory = $info_array['one_level_path'];
      $tmp_Enzyme = $info_array['modifications']['enzyme'];
      $tmp_Enzyme_arr = explode("(", $tmp_Enzyme);
      $tmp_Enzyme = trim($tmp_Enzyme_arr[0]);
      if(!in_array($tmp_Enzyme, $Enzyme_arr)) array_push($Enzyme_arr, $tmp_Enzyme);
      if(!in_array($info_array['srchtolerance'], $Mass_arr)) array_push($Mass_arr, trim($info_array['srchtolerance'])); 
      if(!in_array($info_array['mass_type_fragment'], $Mass_type_arr)) array_push($Mass_type_arr, $info_array['mass_type_fragment']);


      $tmp_file_info_arr['massIon'] = $info_array['mhplus'];
      $tmp_arr = array();
      for($b=1; $b<=$peptide_rank; $b++){
        if($b > count($info_array['proteins'])) break;
        $tmp_arr[$b] = $info_array['proteins'][$b-1];
        $ref = trim($tmp_arr[$b]['reference']);
				$index = $fileNum . ":" . $b;
        if(!array_key_exists($ref, $location_arr)){
				  $location_arr[$ref] = "$a:" . $index;
        }else{
          $location_arr[$ref] .= " $a:" . $index;
          if(!in_array($ref, $ref_arr)) array_push($ref_arr, $ref);
        }
        if(isset($tmp_arr[$b]['attachedproteins'])){
          foreach($tmp_arr[$b]['attachedproteins'] as $val){
            $ref = trim($val['reference']);
            if(!array_key_exists($ref, $location_arr)){
    				  $location_arr[$ref] = "$a:" . $index;
            }else{
              $location_arr[$ref] .= " $a:" . $index;
              if(!in_array($ref, $ref_arr)) array_push($ref_arr, $ref);
            }
          }
        }
      }
      
      $tmp_file_info_arr['proteins'] = $tmp_arr;
      $files_arr[$a][$fileNum] = $tmp_file_info_arr;
    }
  }      
  $dir_arr_detail[$a]['Directory'] = $Directory;
  $dir_arr_detail[$a]['DataFiles'] = $sample_fname_arr;
  $dir_arr_detail[$a]['Database'] = $database_arr;
  $dir_arr_detail[$a]['rundate'] = $rundate_arr;
  $dir_arr_detail[$a]['diffmod'] = $diffmod_arr;
  $dir_arr_detail[$a]['Enzyme'] = implode(",", $Enzyme_arr);
  $dir_arr_detail[$a]['Mass'] = $Mass_arr;
  $dir_arr_detail[$a]['Mass_type'] = $Mass_type_arr;
  closedir($dir_handle);
}
sort($ref_arr);
$tmp = group_protein_acc($MAX_RANK, $options);
$BP_arr = array();
$BPTot_arr = get_BP($BP_arr);

$dm = array();
$abs_dm = array();
if($selected_peptide_str){
  $selected_peptide_arr_tmp = explode(",", $selected_peptide_str);
  foreach($selected_peptide_arr_tmp as $tmp_val){
    $selected_peptide_arr[$tmp_val] = 1;
  }
}else{
  $selected_peptide_arr = array();
}

$consensus_counter = 0;
foreach($consensus_groupings as $ref){
  if($ref == 'nogroup') continue;
  $consensus_counter +=  count($consensus_group[$ref]['outfiles']);
}
if(!$selected_peptide_str){
  $max_list = $consensus_counter + $no_group_rank;
}   

$tmp_dir_arr = $dir_arr_detail;
krsort($tmp_dir_arr);

$dir_display_arr = array('Samples'=>'','OutFiles'=>'','DataFiles'=>'','Enzyme'=>'','Database'=>'','Mass'=>'','Directory'=>'','Diff Mods'=>'');

$file_info = '';
$Samples_str = '';
$OutFiles_str = '';
$DataFiles_arr = array();
$Enzyme_arr = array();
$Database_arr = array();
$Mass_arr = array();
$Mass_type_arr = array();
$Directory_str = '';
$Diff_Mods_arr = array(); 

foreach($tmp_dir_arr as $tmp_val){
  if(!$dir_display_arr['Samples']) $dir_display_arr['Samples'] = $tmp_val['Directory'];
  if($Samples_str) $Samples_str .= ",";
  $Samples_str .= $tmp_val['Directory'];
  if(!$dir_display_arr['OutFiles']) $dir_display_arr['OutFiles'] =  $BPTot_arr[1].'|'.$BPTot_arr[2];
  $OutFiles_str = $dir_display_arr['OutFiles'];
  if(!$dir_display_arr['DataFiles']) $dir_display_arr['DataFiles'] = $tmp_val['DataFiles'][0]." (".$tmp_val['rundate'][0].")";
  $DataFiles_arr = array_merge($DataFiles_arr, $tmp_val['DataFiles']);
  if(!$dir_display_arr['Enzyme']) $dir_display_arr['Enzyme'] = $tmp_val['Enzyme'];
  if(!in_array($tmp_val['Enzyme'], $Enzyme_arr)) array_push($Enzyme_arr, $tmp_val['Enzyme']);
  if(!$dir_display_arr['Database']) $dir_display_arr['Database'] = $tmp_val['Database'][0];
  $Database_arr = array_merge($Database_arr, $tmp_val['Database']);
  if(!$dir_display_arr['Mass']) $dir_display_arr['Mass'] = "(+/-)".$tmp_val['Mass'][0]." (".trim($tmp_val['Mass_type'][0]).")";
  $Mass_arr = array_merge($Mass_arr, $tmp_val['Mass']);
  $Mass_type_arr = array_merge($Mass_type_arr, $tmp_val['Mass_type']);
  if(!$dir_display_arr['Directory']) $dir_display_arr['Directory'] = $tmp_val['Directory'];
  if($Directory_str) $Directory_str .= ",";
  $Directory_str .= $tmp_val['Directory'];
  
  $arr1 = array("(", ")");
  $arr2 = array("", "");
  
  $tmp_difmod = array();
  foreach($tmp_val['diffmod'] as $difmod){
    $tmp_difmod_arr = preg_split("/\)\s*\(/", trim($difmod));
    for($k=0; $k<count($tmp_difmod_arr);$k++){
      $tmp_difmod_arr[$k] = str_replace($arr1, $arr2, $tmp_difmod_arr[$k]);
    }
    $Diff_Mods_arr = array_merge($Diff_Mods_arr, $tmp_difmod_arr);
  }
  //$Diff_Mods_arr = array_unique($Diff_Mods_arr);
  if(!$dir_display_arr['Diff Mods']) $dir_display_arr['Diff Mods'] = str_replace($arr1, $arr2, $tmp_val['diffmod'][0]);
//print_r($tmp_val['diffmod']);
}

$file_info = "Samples\t\t: $Samples_str\r\n";
$file_info .= "OutFiles\t: $OutFiles_str\r\n";

$DataFiles_arr = array_unique($DataFiles_arr);
$DataFiles_str = implode(",", $DataFiles_arr);
$file_info .= "DataFiles\t: $DataFiles_str\r\n";

$Enzyme_arr = array_unique($Enzyme_arr);
$Enzyme_str = implode(",", $Enzyme_arr);
$file_info .= "Enzyme\t\t: $Enzyme_str\r\n";

$Database_arr = array_unique($Database_arr);
$Database_str = implode(",", $Database_arr);
$file_info .= "Database\t: $Database_str\r\n";

$Mass_arr = array_unique($Mass_arr);
$Mass_str = "(+-)".implode(",(+-)", $Mass_arr);
$file_info .= "Mass\t\t: $Mass_str\r\n";

$Mass_type_arr = array_unique($Mass_type_arr);
$Mass_type_str = implode(",", $Mass_type_arr);
$file_info .= "Mass_type\t: $Mass_type_str\r\n";
$file_info .= "Directory\t: $Directory_str\r\n";

$Diff_Mods_arr = array_unique($Diff_Mods_arr);
sort($Diff_Mods_arr);  

for($i=0;$i<count($Diff_Mods_arr);$i++){
  if(!$Diff_Mods_arr[$i]) continue;
  $Diff_Mods_arr[$i] = str_replace($arr1, $arr2, $Diff_Mods_arr[$i]);
}

$Mods_key_val_arr = array();
foreach($Diff_Mods_arr as $Diff_Mods_val){
  if(!trim($Diff_Mods_val)) continue;
  $Mods_arr_tmp = explode(" ", $Diff_Mods_val);
  if(count($Mods_arr_tmp) == 2){
    //if(preg_match("/([A-Za-z]+)([^A-Za-z])$/", $Mods_arr_tmp[0], $matches)){
    if(preg_match("/([A-Z]+)([^A-Z])$/", $Mods_arr_tmp[0], $matches)){
      $char_arr = str_split($matches[1]);
      foreach($char_arr as $char_val){
        $Mods_arr_index = $char_val; //.$matches[2];
        $Mods_key_val_arr[$Mods_arr_index] = $Mods_arr_tmp[1];
      }
    }
  }
}

$Diff_Mods_str = implode(",", $Diff_Mods_arr);
$file_info .= "Diff_Mods\t: $Diff_Mods_str\r\n\r\n";  
fwrite($data_handle, $file_info);

if($mass_unit == 'ppm'){
  $ppm_amu_lable = 'ppm';
}else{
  $ppm_amu_lable = 'amu';
}
$BP_mode_lable = str_replace('fld', '', $BP_mode);

$protein_lable_line = "HitNumber;;ProteinID;;Coverage;;Uniq_peptide;;Peptide;;Score;;Sfavg;;BPsum;;BPavg;;ProteinMass;;Description;;Unified_score\r\n";
fwrite($data_handle, $protein_lable_line);
$peptide_lable_line = "TIC;;MaxBP;;FBP;;ZBP;;Scan;;RT(retention);;z(charge);;ppm;;amu;;MH+(mass_ion);;xC(xCorr);;dCn(deltaCn);;Sp;;RSp(rankSp);;Ions(ionRatio);;Sf(scoreFinal);;P(probability);;Sequence;;IonFile;;Start;;End;;Modification;;Peptide_mass(calc);;Unified_score\r\n\r\n";
fwrite($data_handle, $peptide_lable_line);

$Database_arr =  array_unique($Database_arr);
$Database_str = implode(",", $Database_arr);

$line_counter = 0;
$count = 0;

foreach($consensus_groupings as $ref){
  $peptides_location_arr = array();
  $Protein_checked = print_one_protein($ref, $plain_display);
  $outfiles = $consensus_group[$ref]['outfiles'];  
  $dupepep = array();
  $all_select = 1;
  foreach($outfiles as $val){
    $Dir = $val['SeqDir'];
    $file = $val['fileNum'];
    $Directory = $Dir['dir_num'];
    $not_apply_filter = '';    
    if(!isset($consensus_group[$ref]['checked']) || $ref == "nogroup"){
      if(!$selected_peptide_str){
			  if(!$notnew and $auto_select) $selected_peptide_arr["$Directory/$file"] = 0;
      }  
		}elseif($auto_select || !$notnew || ($BPTot_arr[1] < $BPTot_arr[3])){
      if(!$selected_peptide_str){
			$selected_peptide_arr["$Directory/$file"] = 1;
      }  
		  $not_apply_filter = 1; 
		}
    $selected_v = print_one_peptide($val, $ref, $not_apply_filter,$Protein_checked,$dupepep);
    if(!$selected_v) $all_select = 0;
    if($line_counter >= $max_list){
      $is_break = 1;
      break;
    }
  }
  fwrite($data_handle, "<HR>\r\n");
  $count++;
  if($is_break) break;
}

list($dMavg, $stddev) = get_mean_stddev($abs_dm);
$offset = get_mean($dm);

if($mass_unit == "ppm"){
	$dMavg = round($dMavg, 2);
	$stddev = round($stddev, 2);
	$offset = round($offset, 2);
}else{
	$dMavg = round($dMavg, 4);
	$stddev = round($stddev, 4);
	$offset = round($offset, 4);
}
//echo $full_data_file_name;
exit(0);

function print_one_peptide($val, $ref, $not_apply_filter,$Protein_checked,$dupepep){
  global $line_counter,$min_error,$max_error,$filterdupepep;
  global $Mono_mass,$mass_unit,$dm,$abs_dm,$BP_mode;
  global $not_apply_filter;
  global $tmp_file_name;
  global $filter,$ppmrecal;
  global $selected_peptide_str;
  global $selected_peptide_arr;
  global $num_char_arr;
  global $data_handle;
  global $peptides_location_arr;
  global $Mods_key_val_arr;
  
  $deltaCN_limit = 0.00001;
  if(!$val['fileName']) return 0;
  
  $Dir = $val['SeqDir'];
  $file = $val['fileNum'];
  $fileName = $val['fileName'];
  $Directory = $Dir['dir_num']; 
  
  $airectory_char = $num_char_arr[$Directory]; 
  $temp = preg_replace("/\.\d$/", "", $fileName);
  
  if(!$selected_peptide_str){
    $selected_v = ($selected_peptide_arr["$Directory/$file"])?$selected_peptide_arr["$Directory/$file"]:0;
  }
  $data_arr = $val['preferred_refPeptide'];

  
  $peptide_key = $data_arr['peptide'];
  $massIon = $val['massIon'];
  $chargeState = $val['z'];
  $delta_amu = '';
  $delta_ppm = '';
  $mhplus = $data_arr['MplusH_plus'];
  
  if($mhplus && $massIon){
		$delta_amu = $mhplus - $massIon;    		
		$delta_ppm = $delta_amu / ($massIon + ($chargeState - 1) * $Mono_mass["Proton"]) * 1000000;
    if($ppmrecal){
      $delta_ppm += $ppmrecal;
			$delta_amu = $delta_ppm *  ($massIon + ($chargeState - 1) * $Mono_mass["Proton"]) / 1000000;
			$massIon = $mhplus - $delta_amu;
		}
	}
  if($mass_unit == 'ppm'){
    $deltaM = $delta_ppm;
  }elseif($mass_unit == 'amu'){
    $deltaM = $delta_amu;
  }
  $data_arr['fileName'] = $val['fileName'];
  $data_arr['fileNum'] = $val['fileNum'];
  $data_arr['fldTIC'] = $val['fldTIC'];
  $data_arr['Scan'] = $val['Scan'];
  $data_arr['RT'] = $val['RT'];
  $data_arr['z'] = $val['z'];
  $data_arr['massIon'] = $val['massIon'];
  
  if($filter['use_filter']){
    if(!$not_apply_filter){
  	  if($filter['filter_action'] == "SELECT"){
        if(!$selected_peptide_str){
				  if(pass_filter($filter, $data_arr)) {
					  $selected_v = 1;	# check this scan if it passes filter
				  }else{
					  $selected_v = 0;
				  }
        }  
  	  }
		} 
 	}
  $selected_peptide_val = "$Directory/$file";   
  
  if($selected_peptide_str){
    if(array_key_exists($selected_peptide_val, $selected_peptide_arr)){
      $selected_v =1;
    }else{
      $selected_v =0;
    }  
  }
        
  if($selected_v){
    if($deltaM >= $min_error && $deltaM <= $max_error){
      array_push($dm, $deltaM);
      array_push($abs_dm, abs($deltaM));
    }
  }
    
  $CHECKED = ($selected_v)?"checked":"";
  
  if($ref != "nogroup"){
    if(trim($ref) == trim($data_arr['reference'])){
      $reference_display = $data_arr['reference'];
    }else{
      $reference_display = $data_arr['reference']."<br>".$ref;
    }
  }else{
    $reference_display = $data_arr['reference'];
  }
  $rankIndex = ($data_arr['number'])?$data_arr['number']:1;
  
  $deltaCn = '';
  if($rankIndex != 1){
    $deltaCn = "----";
  }else{
    if($data_arr['deltcn_display'] >= $deltaCN_limit) $deltaCn = $data_arr['deltcn_display'];
  }
  $delta_ppm = round($delta_ppm,1);
  $delta_amu = round($delta_amu,4);
  $delta_ppm_amu = ($mass_unit == 'ppm')?$delta_ppm:$delta_amu;  
  $Mods_location = '';

  if($peptides_location_arr){
    list($start,$end) =  explode("-",$peptides_location_arr[$data_arr['peptide']]['start_end']);
    $Peptide_mass = $peptides_location_arr[$data_arr['peptide']]['Peptide_mass'];
    if($peptides_location_arr[$data_arr['peptide']]['Mods_loc']){
      $Mods_location_arr = $peptides_location_arr[$data_arr['peptide']]['Mods_loc'];
      $Mods_location = '';
      foreach($Mods_location_arr as $Mods_location_key => $Mods_location_val){
        if($Mods_location) $Mods_location .= ",";
        $tmp_loc_arr = explode(",", $Mods_location_val);
        $tmp_loc_str = '';
        foreach($tmp_loc_arr as $tmp_loc_val){
          if($tmp_loc_str) $tmp_loc_str .= ',';
          $tmp_loc_str .= $start + $tmp_loc_val - 1;
        }
        
        if(array_key_exists($Mods_location_key, $Mods_key_val_arr)){
        //if(in_array($Mods_location_key, $Mods_key_val_arr)){
          $Mods_location .= $Mods_location_key." [".($tmp_loc_str)."] ".$Mods_key_val_arr[$Mods_location_key];
        }  
      }
    }
  }
  
  $BP_mode_arr = array("fldMaxBP" => "Apex",  "fldFBP" => "Full", "fldZBP" => "Zoom", "fldTIC" => "MS2");   
  if($Protein_checked && $CHECKED){
    $peptide_lable_line = $val["fldTIC"].";;".$val["fldMaxBP"].";;".$val["fldFBP"].";;".$val["fldZBP"].";;".$val['Scan'].";;".$val['RT'].";;".$val['z'].";;".$delta_ppm.";;".$delta_amu.";;".$massIon.";;".$data_arr['xcorr'].";;".(($deltaCn=="----")?"":$deltaCn).";;".$data_arr['sp2'].";;".$data_arr['sp'].";;".$data_arr['ions'].";;".((isset($data_arr['sf']))?$data_arr['sf']:'0').";;".$data_arr['prob'].";;".$data_arr['peptide'].";;$fileName.dta;;$start;;$end;;$Mods_location;;$Peptide_mass;;".$data_arr['unified_score']."\r\n";
    fwrite($data_handle, $peptide_lable_line);
  }
  
  $line_counter++;
	//$prinedoutfile{"$outfileName"} = 1;
	return $selected_v;
} 

function get_sequence_description($protein_acc){
  global $proteinDB,$consensus_group; 
  $tmp_arr =  explode("|", $protein_acc);
  //gi|6323435|ref|NP_013507.1|
  $GI = $tmp_arr[1];
  $AccessionType = '';
  $tmp_info_arr = get_protin_info($GI, $AccessionType, $proteinDB);
  if(isset($tmp_info_arr['Sequence']) && $tmp_info_arr['Sequence']){
    $gene_name = get_Gene_Name($tmp_info_arr['EntrezGeneID'], $proteinDB);      
  }else{
    $tmp_info_arr = get_protein_from_url($GI);
  }    
  if(isset($tmp_info_arr['Sequence']) && $tmp_info_arr['Sequence']) $sequence = $tmp_info_arr['Sequence'];
  $description = '';
  if(isset($tmp_info_arr['Description']) && $tmp_info_arr['Description']){
    if(isset($gene_name)) $description = "( $gene_name )";
    $description .= $tmp_info_arr['Description'];
  }elseif(isset($tmp_info_arr['description']) && $tmp_info_arr['description']){
    if(isset($gene_name)) $description = "( $gene_name )";
    $description = $tmp_info_arr['description'];
  }else{
    $description = $consensus_group[$ref]['outfiles'][0]['proteins'][1]['description'];
  }
  return array($sequence,$description);
}    

function print_one_protein($ref, $with_others){
  global $consensus_group,$consensus_groupings,$HITSDB,$proteinDB,$BPTot_arr,$HitNumber;
  global $data_handle;
	$isnogroup = ($ref == "nogroup")?1:0;
	$SFscores = isset($consensus_group[$ref]["SFscores"])?$consensus_group[$ref]["SFscores"]:0;
  $Uscores = isset($consensus_group[$ref]['unified_score'])?$consensus_group[$ref]['unified_score']:0;
	$numfiles = count($consensus_group[$ref]['outfiles']); //-------- Sequences  
	//$count = ($isnogroup)?count($consensus_groupings):$consensus_group[$ref]['rank'];
	$outfile = $consensus_group[$ref]['outfiles'][0];
	$SeqDir = $outfile['SeqDir'];
	$filenum = $outfile['fileNum'];
  $sequence = '';
  $coverage = '';
  $BPperc = 0;
  
  if(isset($SFscores)){
		if($SFscores < 10){
		   $SFdisplay = precision($SFscores, 2);    //------------------ Score
		}elseif($SFscores < 100){
		   $SFdisplay = precision($SFscores, 1);
		}else{
		   $SFdisplay = precision($SFscores, 0);
		}
	}
  
  $sequence = '';  
  if($ref != "nogroup"){
    list($sequence,$description) = get_sequence_description($ref);    
    $peptides_arr = $consensus_group[$ref]['Peptides'];
    list($coverage,$ProteinMass) = get_coverage($sequence,$peptides_arr);//--------------Cov
    $coverage = precision($coverage, 1);
    $uniq = get_uniq_peptide_num($sequence,$peptides_arr); //--------------Uniq
  }  
  $BPsum = ($consensus_group[$ref]['BPsum']) ? $consensus_group[$ref]['BPsum'] : 0;
  $BPsum = sci_format($BPsum);         
  if($numfiles) $sfavg = precision($SFdisplay / $numfiles, 2); //----------------Avg
  if($numfiles) $BPavg = sci_format($BPsum / $numfiles);     //---------------Avg
  $BPsum_sci = sci_format($BPsum);
	$BPperc = ($BPTot_arr[0] != 0)?(100 * $BPsum_sci/$BPTot_arr[0]):0;
	$BPperc = intval($BPperc + .5);

  if($ref != "nogroup"){
    $Protein_checked = (isset($consensus_group[$ref]['checked']) && $consensus_group[$ref]['checked'])?$consensus_group[$ref]['checked']:'';
  }else{
    $Protein_checked = '';
  }
  if($Protein_checked){
    $HitNumber++;
    $protein_line = "Hit_$HitNumber;;$ref;;$coverage;;$uniq;;$numfiles;;$SFdisplay;;$sfavg;;$BPsum_sci;;$BPavg;;$ProteinMass;;$description;;$Uscores\r\n";
    fwrite($data_handle, $protein_line);
  }
  return $Protein_checked;
} 

function get_BP($BPs){
  global $files_arr,$dir_arr_detail;
  global $BP_mode,$selected_peptide_arr,$prefie;
  $BPTot = 0;
  $spectra = 0;
  $num_d = 0;
  
	foreach($files_arr as $SeqDir_key => $SeqDir){
    //$BP = $dir_arr_detail[$SeqDir_key]['BP'];
    $BP = $BP_mode;    
    $num_d += $dir_arr_detail[$SeqDir_key]['dtanumber'];
    $Directory = $dir_arr_detail[$SeqDir_key]['dir_num'];
		foreach($SeqDir as $file_val){
      $file = $file_val['fileNum'];
      $fileName = $file_val['fileName'];      
      $temp = preg_replace("/\.\d$/", "", $fileName);
      $spectra++;      
			if(isset($prefie[$temp])){
				$prefie[$temp]['count']++;
				if(!$prefie[$temp]['checked'] && $selected_peptide_arr["$Directory/$file"]) $prefie[$temp]['checked'] = 1 ;
				continue;  # don't count 2+/3+ pairs twice
			}
			$BPTot += $file_val[$BP];
      array_push($BPs, $file_val[$BP]);
      $prefie[$temp]['count'] = 1;
      $prefie[$temp]['checked'] = $selected_peptide_arr["$Directory/$file"];
		}
	}
	$BPTot = sci_format($BPTot); 
	rsort($BPs); 
  $important = count($BPs);   
  return array($BPTot,$spectra,$important,$num_d);
}

function sci_format($num){
  $sci = sprintf("%.2e", $num);
  $sci = preg_replace("/e([\+\-])?0*(\d+)/", "e$1$2", $sci);
  $sci = preg_replace("/\+/", "", $sci);
  return $sci;
}

function get_coverage($sequence,&$peptidesArr){

  global $peptides_location_arr;
  global $Mods_key_val_arr;
  $sequenceLen = '';
  $ProteinMass = '';
  if($sequence){
    $sequenceLen = strlen($sequence);
    $sequence = strtolower($sequence);
    for($i=0; $i<count($peptidesArr); $i++){
      $index = $peptidesArr[$i];
      if(strstr($peptidesArr[$i], '+')){
        $temArr = explode('+', $peptidesArr[$i]);
        $real_peptide = $temArr[0];
      }elseif(strstr($peptidesArr[$i], '.')){
        $temArr = explode('.', $peptidesArr[$i]);
        $real_peptide = $temArr[1];
      }
      $Mods_location_arr = array(); 
      $temArr_2 = preg_split("/[^A-Z]/", $real_peptide);
      $tmp_len_total = 0;
      for($y=0;$y<count($temArr_2)-1; $y++){
        $tmp_len = strlen($temArr_2[$y]);
        $tmp_len_total += $tmp_len;
        $mod_char = substr($temArr_2[$y], -1);     
        if(!array_key_exists($mod_char, $Mods_location_arr)){
          $Mods_location_arr[$mod_char] = $tmp_len_total;
        }else{
          $Mods_location_arr[$mod_char] .= ','.$tmp_len_total;
        }
      }
      $peptidesArr[$i] = trim(preg_replace("/[^A-Z]/e", "",$real_peptide));      
      $peptideUp = strtoupper($peptidesArr[$i]);
      $sequence = str_ireplace($peptidesArr[$i], $peptideUp, $sequence);
      $tmp_arr = explode($peptideUp, $sequence);
      if(count($tmp_arr) != 2){
        echo "match 2<br>";
      }else{
        $start = strlen($tmp_arr[0]) + 1;
        $end = strlen($peptideUp) + $start - 1;
      }
      $peptides_location_arr[$index]['start_end'] = $start."-".$end;
      $peptides_location_arr[$index]['Mods_loc'] = $Mods_location_arr;
      $peptides_location_arr[$index]['Peptide_mass'] = calcMass($peptidesArr[$i]);
    }
    $ProteinMass = calcMass($sequence);
  }
  $upCase = 0;
  foreach(count_chars($sequence, 1) as $i => $val) {
    if($i >= 65 && $i <= 90){
      $upCase += $val;
    }
  }
  if(!$sequenceLen){
    $coverage = 0;
  }else{
    $coverage = round($upCase/$sequenceLen, 4) * 100;
  }
  return array($coverage,$ProteinMass);
}

function get_uniq_peptide_num($sequence, $peptides){
	$matches = array(); //arr, preg_match_all
  $unique = array(); //hash
	$count = 0;
	$seq_length = strlen($sequence);  
	foreach($peptides as $pep){
    if(in_array($pep, $unique)) continue;
    array_push($unique, $pep);
    preg_match_all("/$pep/", $sequence, $matches, PREG_PATTERN_ORDER);
    $count += count($matches);
  }  
	return $count;
}

function precision($num, $precision, $leftprecision='', $fill=''){
  $power = '';
  $temp = '';
  $pos = '';
  $a = '';
  $neg = '';
  $positive = '';
  if($num > 0) $positive = 1;
  $num = abs($num);

  if(!$precision) return $num;
  
  $precision = intval($precision);
  if($precision < 0) return ($num);
  if(!$fill) $fill = "0" ;
  $power = ($precision)?pow(10, $precision):1;
  $temp = intval($power * $num + 0.5)/$power;

  if($precision !== 0){
    $pos = strpos($temp, ".");
    if($pos === false){
      $temp .= ".";
      $temp .= str_repeat("0", $precision);
    }else{
      $a = strlen($temp) - $pos - 1;
      if($a < $precision){
        $temp .= str_repeat("0",($precision - $a));
      }
    }
  }
  
  if($leftprecision){
    $leftprecision = intval($leftprecision);
    # $a is the number of digits to the left of the decimal point
    $a = strlen($temp);
    if($precision) $a -= 1 + $precision ;
    if($a < $leftprecision){
      if($positive || $temp === 0){
        $temp = str_repeat($fill,($leftprecision - $a)) . $temp;
      }else{
        if($fill === "0") {
          $temp = "-" . str_repeat($fill,($leftprecision - $a - 1)) . $temp;
        }else{
          $temp = str_repeat($fill,($leftprecision - $a - 1)) . "-" . $temp;
        }
      }
    }
  }else{
    if(!$positive && ($temp !== 0)) $temp = "-" . $temp; 
  }
  return ($temp);
}

function group_protein_acc($MAX_RANK, $options){
  global $files_arr;
  global $dir_arr_detail;
  global $location_arr;
  global $ref_arr;
  global $BP_mode; 
  global $consensus_groupings; //array
  global $consensus_group;     //hash
  global $FORM_arr;              //hash
  global $selectedentry;
  
  $sortBy = $options['sort'];
  $groupsortby = $options['groupsortby'];
  $differentdescrip = $options['differentdescrip'];
  $toprankhit = $options['toprankhit'];   //'1';
  $sequences_threshold = $options['sequences_threshold'];
  $score_threshold = $options['score_threshold'];
  $group_sequences_threshold = $options['group_sequences_threshold'];
  $group_score_threshold = $options['group_score_threshold'];
  $ingroupsort = ''; 
  $filter = $options{'filter'};

  $ordered_refs = array(); // array
  $top_score = array(); // hash
  $backlocation_arr = array(); // hash
  $summedtic = array(); // hash

  $selectedentry = array();
  $differentdescrip_arr = preg_split("/\s+/", $differentdescrip);

  foreach($ref_arr as $ref_val){
    if(!$ref_val) continue;
    $loc = $location_arr[$ref_val];
    if(!array_key_exists($loc, $backlocation_arr)){
      $backlocation_arr[$loc] = $ref_val;
    }else{
      $first_ref_value = $backlocation_arr[$loc];
      if(in_array($ref_val, $differentdescrip_arr)){
        $selectedentry[$ref_val] = 1;
        $backlocation_arr[$loc] = $ref_val;
        if(isset($consensus_group[$first_ref_value]['consensus_refs']) && $consensus_group[$first_ref_value]['consensus_refs']){
          $consensus_group[$ref_val]['consensus_refs'] = $consensus_group[$first_ref_value]['consensus_refs'];
          unset($consensus_group[$first_ref_value]['consensus_refs']);
        }
        if(!isset($consensus_group[$ref_val]['consensus_refs'])){
          $consensus_group[$ref_val]['consensus_refs'] = $first_ref_value; 
        }else{
          $consensus_group[$ref_val]['consensus_refs'] .= " " . $first_ref_value; 
        }
      }else{
        if(!isset($consensus_group[$first_ref_value]['consensus_refs'])){
          $consensus_group[$first_ref_value]['consensus_refs'] = $ref_val;
        }else{
          $consensus_group[$first_ref_value]['consensus_refs'] .= " " . $ref_val;
        }
    	}
    }
  }
  
  foreach($backlocation_arr as $ref){
  	$score_filelist_arr = protein_score($files_arr, $location_arr, $ref, $MAX_RANK, $toprankhit);
    if(count($score_filelist_arr) == 2){
      $consensus_group[$ref]['score'] = $score_filelist_arr[0];
      $consensus_group[$ref]['filelist'] = $score_filelist_arr[1];
      array_push($ordered_refs, $ref);
    }
  }
  usort($ordered_refs, "cmp_val");
  $processed = array();
  $group_file_map_arr = array();
 
  foreach($ordered_refs as $ref){
    $count = 0;
    $sfScore = 0;
    $unified_score =0;
    $location_tmp_str = $location_arr[$ref];
    $location_tmp_arr = explode(" ", $location_tmp_str);
    $tmp_location_arr = array();
    
    foreach($location_tmp_arr as $index){
      list($dir, $i, $num) = explode(":", $index);
      if(isset($processed["$dir:$i"]) || $num > $MAX_RANK) continue;
  	  $processed["$dir:$i"] = 1;      
      
  	  $SeqDirObj = $dir_arr_detail[$dir];      
  	  $outfile = $files_arr[$dir][$i]; 
  	  $outfile['SeqDir'] = $SeqDirObj;
   	  $outfile['preferred_refPeptide'] = $outfile['proteins']["$num"];
  	  if(!array_key_exists('outfiles', $consensus_group[$ref])){
        $consensus_group[$ref]['outfiles'] = array();
      }
      array_push($consensus_group[$ref]['outfiles'], $outfile);
      array_push($tmp_location_arr, "$dir:$i");
      if(isset($outfile['proteins']["$num"]['unified_score'])){
  	    $unified_score += $outfile['proteins']["$num"]['unified_score'] ;
      }   
      if(isset($outfile['proteins']["$num"]['sf'])){
  	    $sfScore += $outfile['proteins']["$num"]['sf'] ;
      }  
  	  $count++;
  	} 
  	//if(!isset($count) || $count < $sequences_threshold || (isset($sfScore) && $sfScore < $score_threshold)){
    if(!isset($count) || $count < $sequences_threshold){
      unset($consensus_group[$ref]);
    }else{
      array_push($consensus_groupings, $ref);
  		$consensus_group[$ref]['SFscores'] = $sfScore;
      $consensus_group[$ref]['unified_score'] = $unified_score;
  		if($count >= $group_sequences_threshold){
  			if(!$sfScore || $sfScore >= $group_score_threshold) $consensus_group[$ref]['checked'] = "CHECKED";
  		}
      $group_file_map_arr[$ref] = $tmp_location_arr;
  	}
  }

  for($i=0; $i<count($consensus_groupings); $i++){
  	$tic = '';
  	$ref = $consensus_groupings[$i];
    $count = 0;
  	$peptides = array();
    $tmp_location_arr = $group_file_map_arr[$ref];
    
    for($j=0; $j<count($consensus_group[$ref]['outfiles']); $j++){
      $SeqDirObj = $consensus_group[$ref]['outfiles'][$j]['SeqDir'];
      $filenum = $consensus_group[$ref]['outfiles'][$j]['fileNum'];
      $num = $consensus_group[$ref]['outfiles'][$j]['preferred_refPeptide']['number'];
      if($sortBy == "consensus") $consensus_group[$ref]['outfiles'][$j]['preferred_ref'] = $ref;
      $file = $consensus_group[$ref]['outfiles'][$j]['fileName'];
      $pep = $consensus_group[$ref]['outfiles'][$j]['preferred_refPeptide']['peptide'];
      array_push($peptides, $pep);
      $file_trunc = preg_replace('/\.\d+$/', '', $file);
      if(!isset($summedtic[$file_trunc])){
        if(isset($consensus_group[$ref]['outfiles'][$j][$BP_mode])){
          $tic += $consensus_group[$ref]['outfiles'][$j][$BP_mode];
        }
  			$summedtic[$file_trunc] = 1;
  		}
      $consensus_group[$ref]['outfiles'][$j]['ConsensusGroup'] = $ref;
      $tmp_location = explode(":", $tmp_location_arr[$j]);
      $files_arr[$tmp_location[0]][$tmp_location[1]]['ConsensusGroup'] = $ref;
      $count++;
    }
        
  	$consensus_group[$ref]['BPsum'] = $tic;
  	$consensus_group[$ref]['Peptides'] = $peptides;
  }
  usort($consensus_groupings, "group_sort_by");  
  for($i=0; $i<count($consensus_groupings); $i++){
  	$consensus_group[$consensus_groupings[$i]]['rank'] = $i;
  }  
  if($sortBy == "consensus"){
    $no_group_count = sort_by_protein($files_arr, $summedtic, $ingroupsort);
    return $no_group_count;
  }
}

function get_outfiles($SeqDirs, $sort){
  global $dir_arr_detail;
	$outs = array();
	foreach($SeqDirs as $dir_key => $dir){
		foreach($dir as $filenum => $fileval) {
			$outfile = $dir[$filenum];
			$outfile['SeqDir'] = $dir_arr_detail[$dir_key];
      if($sort != "consensus" || !isset($outfile['preferred_refPeptide'])){
			  $outfile['preferred_refPeptide'] = $outfile['proteins']["1"]; 	
      }
      array_push($outs, $outfile);
		}
	}
	return $outs;
}

function sort_by_protein($files_arr, &$summedtic, $ingroupsort){
  global $dir_arr_detail,$consensus_group,$consensus_groupings;
  $consensus_group['nogroup'] = array();
  $consensus_group['nogroup']['BPsum'] = 0;
  $consensus_group['nogroup']['SFscores'] = 0; 
  $consensus_group['nogroup']['outfiles'] = array();
	$count = 0;
	foreach($files_arr as $SeqDirkey => $SeqDirObj){
    $tmp_BP = $dir_arr_detail[$SeqDirkey]['BP'];
		foreach($SeqDirObj as $filenum => $fileval){
			$outfile = $SeqDirObj[$filenum];

      if(isset($outfile['ConsensusGroup'])){
        $count++;
      }else{ 
				$outfile['preferred_refPeptide'] = $outfile['proteins'][1];
				$outfile['SeqDir'] = $dir_arr_detail[$SeqDirkey];
				$out = $outfile['fileName'];
				$file_trunc = preg_replace('/\.\d+$/', '', $out);
        if(!isset($summedtic[$file_trunc])){
				  $consensus_group['nogroup']['BPsum'] += $outfile[$tmp_BP];    //===$SeqDirObj->{'BP'}???????
        }
        if(isset($outfile['preferred_refPeptide']['sf'])){
				  $consensus_group['nogroup']['SFscores'] += $outfile['preferred_refPeptide']['sf'];
        }  
				$summedtic[$file_trunc] = 1;
        array_push($consensus_group['nogroup']['outfiles'], $outfile);
			} 
		}
	}
  if(count($consensus_group['nogroup']['outfiles'])) array_push($consensus_groupings, "nogroup");
	foreach($consensus_groupings as $ref){
		$sortedoutfiles = do_sort_outs($files_arr, $ingroupsort, $consensus_group{$ref}{'outfiles'});
		$consensus_group[$ref]['outfiles'] = $sortedoutfiles;
	}
	return $count;
}

function do_sort_outs($SeqDirs, $sort, $outs=array(), $selected='' ){
  if(!count($outs)) $outs = get_outfiles($SeqDirs, $sort);
  if($sort == "ppm") $outs =  prepare_for_DeltaPPM($outs);
  usort($outs, "sort_out_files");
  return $outs;
}

function sort_out_files($a, $b){
  global $sort;
  if($sort == "number" || $sort == "#"){
  	if($a['fileNum'] > $b['fileNum']){
      return 1;
    }elseif($a['fileNum'] == $b['fileNum'] && $a['SeqDir']['Directory'] > $b['SeqDir']['Directory']){
      return 1;
    }
    return -1;
  }elseif($sort == "scan"){
    $tmp_arrA = explode(".", $a['fileName']);
    $tmp_arrB = explode(".", $b['fileName']);
    if($tmp_arrA[1] > $tmp_arrB[1]){
      return 1;
    }elseif($tmp_arrA[1] == $tmp_arrB[1] && $tmp_arrA[3] > $tmp_arrB[3]){
       return 1;
    }
    return -1;
  }elseif($sort == "rt"){
    if(isset($a['fldRetention']) && isset($b['fldRetention']) && $a['fldRetention'] > $b['fldRetention']){
      return 1;
    }elseif(isset($a['fldRetention']) && isset($b['fldRetention']) && $a['fldRetention'] == $b['fldRetention'] || !isset($a['fldRetention']) || !isset($b['fldRetention'])){
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "bp" || $sort == "fbp" || $sort == "tic" || $sort == "maxbp"){
  	if($b[$b['SeqDir']['BP']] > $a[$a['SeqDir']['BP']]){
      return 1;
    }elseif($b[$b['SeqDir']['BP']] == $a[$a['SeqDir']['BP']]){
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "p"){
    if($b['preferred_refPeptide']['prob'] > $a['preferred_refPeptide']['prob']){
      return 1;
    }elseif($b['preferred_refPeptide']['prob'] == $a['preferred_refPeptide']['prob']){
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "ions"){
    if(eval($b['preferred_refPeptide']['ions']) > eval($a['preferred_refPeptide']['ions'])){
      return 1;
    }elseif(eval($b['preferred_refPeptide']['ions']) == eval($a['preferred_refPeptide']['ions'])){
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "ref"){
    if($a['preferred_refPeptide']['reference'] > $b['preferred_refPeptide']['reference']){
      return 1;
    }elseif($a['preferred_refPeptide']['reference'] == $b['preferred_refPeptide']['reference']){
  	  if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "parens"){
    if($a['preferred_refPeptide']['fldPrevAA'] > $b['preferred_refPeptide']['fldPrevAA']){
      return 1;
    }elseif($a['preferred_refPeptide']['fldPrevAA'] == $b['preferred_refPeptide']['fldPrevAA']){  
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "sp"){
    if($b['preferred_refPeptide']['sp2'] > $a['preferred_refPeptide']['sp2']){
      return 1;
    }elseif($b['preferred_refPeptide']['sp2'] == $a['preferred_refPeptide']['sp2']){
  	  if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "rsp"){
    if($a['preferred_refPeptide']['sp'] > $b['preferred_refPeptide']['sp']){
      return 1;
    }elseif($a['preferred_refPeptide']['sp'] == $b['preferred_refPeptide']['sp']){
  	  if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "xc"){
    if($b['preferred_refPeptide']['xcorr'] > $a['preferred_refPeptide']['xcorr']){
      return 1;
    }elseif($b['preferred_refPeptide']['xcorr'] > $a['preferred_refPeptide']['xcorr']){
  	  if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "ckbox"){
    $tmp_indexA = $a['SeqDir']['Directory']."/".$a['fileName'];
    $tmp_indexB = $b['SeqDir']['Directory']."/".$b['fileName'];
    if($selected[$tmp_indexB] > $selected[$tmp_indexA]){
      return 1;
    }else if($selected[$tmp_indexB] == $selected[$tmp_indexB]){
  	  if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif(($sort == "mh+") || ($sort == "mhplus")){
    if($a['fldMassIon'] > $b['fldMassIon']){
      return 1;
    }elseif($a['fldMassIon'] == $b['fldMassIon']){
  	  if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "z"){
    $tmp_arrA = explode(".", $a['fileName']);
    $tmp_arrB = explode(".", $b['fileName']);
    if($tmp_arrA[3] > $tmp_arrB[3]){
      return 1;
    }elseif($tmp_arrA[3] == $tmp_arrB[3]){
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "dm"){
    if(($b['fldMassIon'] - $b['preferred_refPeptide']['fldMHPlus']) > ($a['fldMassIon'] - $a['preferred_refPeptide']['fldMHPlus'])){
      return 1;
    }elseif(($b['fldMassIon'] - $b['preferred_refPeptide']['fldMHPlus']) == ($a['fldMassIon'] - $a['preferred_refPeptide']['fldMHPlus'])){
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "ppm"){
    if($b['fldDeltaPPM'] > $a['fldDeltaPPM']){
      return 1;
    }elseif($b['fldDeltaPPM'] = $a['fldDeltaPPM']){  
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "dcn"){
    if($b['preferred_refPeptide']['deltcn'] > $a['preferred_refPeptide']['deltcn']){
      return 1;
    }elseif($b['preferred_refPeptide']['deltcn'] == $a['preferred_refPeptide']['deltcn']){  
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }elseif($sort == "sequence"){
    if($a['preferred_refPeptide']['peptide'] > $b['preferred_refPeptide']['peptide']){
      return 1;
    }elseif($a['preferred_refPeptide']['peptide'] == $b['preferred_refPeptide']['peptide']){
      if($b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
        return 1;
      }
    }
    return -1;
  }else{
  	if(isset($b['preferred_refPeptide']['sf']) && isset($a['preferred_refPeptide']['sf']) && $b['preferred_refPeptide']['sf'] > $a['preferred_refPeptide']['sf']){
      return 1;
    }
    return -1;
  }
} 

function prepare_for_DeltaPPM($outs){
  for($i=0; $i<$outs; $i++){
    preg_match("/(\d+)$/", $outs[$i]['fileName'], $matches);
    $z = $matches[1];
  }
  return $outs;
}  

function group_sort_by($a, $b){
  global $groupsortby,$consensus_group;
  if($groupsortby == 'TIC'){ 
    if(isset($consensus_group[$b]['BPsum']) && isset($consensus_group[$a]['BPsum']) && $consensus_group[$b]['BPsum'] > $consensus_group[$a]['BPsum']){
      return 1;
    }elseif(isset($consensus_group[$b]['BPsum']) && isset($consensus_group[$a]['BPsum']) && $consensus_group[$b]['BPsum'] == $consensus_group[$a]['BPsum'] 
    || !isset($consensus_group[$b]['BPsum']) || !isset($consensus_group[$a]['BPsum'])){
      if($a > $b) return 1;
    }
    return -1;
  }elseif($groupsortby == 'Sf'){    
    if(isset($consensus_group[$b]['SFscores']) && isset($consensus_group[$a]['SFscores']) && $consensus_group[$b]['SFscores'] > $consensus_group[$a]['SFscores']){
      return 1;
    }
    return -1;
  }elseif($groupsortby == 'Sequences'){
    if(isset($consensus_group[$b]['outfiles']) && isset($consensus_group[$a]['outfiles']) && count($consensus_group[$b]['outfiles']) > count($consensus_group[$a]['outfiles'])){
      return 1;
    }elseif((isset($consensus_group[$b]['outfiles']) && isset($consensus_group[$a]['outfiles']) && count($consensus_group[$b]['outfiles']) == count($consensus_group[$a]['outfiles'])) 
    || !isset($consensus_group[$b]['outfiles']) || !isset($consensus_group[$a]['outfiles'])){    
      if($a > $b) return 1;
    }
    return -1;
  }
}

function cmp_val_2($a, $b){
  global $n;
  if(isset($n[$a]) && isset($n[$b]) && $n[$a] > $n[$b]){
    return 1;
  }
  return -1;
}

function cmp_val($a, $b){
  global $consensus_group,$selectedentry;
  if(isset($consensus_group[$b]['filelist'][1]) && isset($consensus_group[$a]['filelist'][1]) && count($consensus_group[$b]['filelist'][1]) > count($consensus_group[$a]['filelist'][1])){
    return 1;
  }elseif((isset($consensus_group[$b]['filelist'][1]) && isset($consensus_group[$a]['filelist'][1]) && count($consensus_group[$b]['filelist'][1]) == count($consensus_group[$a]['filelist'][1])) 
  || !isset($consensus_group[$b]['filelist'][1]) || !isset($consensus_group[$a]['filelist'][1])){
    if($consensus_group[$b]['score'] > $consensus_group[$a]['score']){
      return 1;
    }elseif($consensus_group[$b]['score'] == $consensus_group[$a]['score']){
      if(isset($selectedentry[$b]) && isset($selectedentry[$a]) && $selectedentry[$b] > $selectedentry[$a]){
        return 1;
      }elseif((isset($selectedentry[$b]) && isset($selectedentry[$a]) && $selectedentry[$b] == $selectedentry[$a]) || !isset($selectedentry[$b]) || !isset($selectedentry[$a])){
        if($a > $b){
          return 1;
        }elseif($a == $b){
          return 0;
        }
      }
    }
  }
  return -1;
}

function protein_score($SeqDirObjs, &$location, $ref, $MAX_RANK, $toprankhit){
  global $n;
  $scorearr = array(10, 8, 6, 4, 2, 1);
  $numscores = count($scorearr);
	$score = '';
  $num = '';
  $filelist = array(); //hash
  
  $file_pep_seen = array(); //hash
	$pep_seen = array(); //hash
	$file_used = array(); //hash 
   
  
  $temparr = explode(" ", trim($location[$ref]));
  $n = array(); //hash
  
  foreach($temparr as $index){
    if(preg_match('/:(\d+)$/', $index, $matches)){
      $n[$index] = $matches[1];
    }
	}
  usort($temparr, "cmp_val_2");
  foreach($temparr as $index){
    if(preg_match('/(\d+):(\d+):(\d+)/', $index, $matches)){
      $dir = $matches[1];
      $i = $matches[2];
      $num = $matches[3];
    }
    $outfile = $SeqDirObjs[$dir][$i];
    $pep = $outfile['proteins'][$num]['peptide'];
    $pep = preg_replace('/[^A-Z]/', '', $pep);

    //$pep = str_replace("#*@\$\^\~\[\]", "", $pep);
    if(isset($file_pep_seen["$dir:$i:$pep"])) continue;
		$file_pep_seen["$dir:$i:$pep"] = 1;
    $realnum = ($num<=$numscores)?$num:$numscores;
    if(!array_key_exists($realnum, $filelist)){
      $filelist[$realnum] = array();
    }
    array_push($filelist[$realnum], $outfile);
    if(!isset($pep_seen[$pep]) and $num <= $MAX_RANK){
			$score += $scorearr[$realnum-1];
			$pep_seen[$pep] = 1;
			$file_used["$dir:$i"] = 1;
		}
  }
  if(count($file_used) < 2) return array();
  if(count($pep_seen) < 2) return array();
  if($toprankhit and !isset($filelist[1])) return array();
	return array($score, $filelist);
}

function read_seqoutfile(&$info_array,$outfile, $options){
  if(!$outfile){
    echo "$outfile is not exists";
    exit;
  }  
  if(preg_match("/\.xml/i", $outfile)){
		read_xmloutfile($info_array,$outfile);
	}else{
		return read_outfile($info_array,$outfile, $options);
	}	
}

function read_outfile(&$info_array, $outfile, $options){
	// it is highly unlikely that number of proteins is more than 50000
	$rank = (isset($options['rank']) &&  $options['rank']) ?  $options['rank'] : 50000; 
  $readheader = (isset($options['readheader']) &&  $options['readheader']) ?  $options['readheader'] : 1;
  $readdescript = (isset($options['readdescript']) &&  $options['readdescript']) ?  $options['readdescript'] : 1;  
	$proteinnum = -1;
	$attachedproteinnum = 0;
	$descriptionnum = -1;
	$part = "";
  
  $handle = fopen($outfile, "r");
  if(!$handle){
    echo "$outfile, It may not exist";
    exit;
  }  
  $info_array['filename'] = $outfile;
  $info_array['origfilepath'] = dirname($outfile);
  $info_array['one_level_path'] = basename($info_array['origfilepath']);   
  $deltcn_display_arr = array();    
  while(($line = fgets($handle)) !== false){
  	trim($line);
    if(!preg_match("/\S/", $line)){ 
			if($part == ""){
        $part = "HEADER";
        continue;
      }elseif($part == "HEADER"){
				$part = "PROTEIN";
				while(($line = fgets($handle, 4096)) !== false){
          if(preg_match("/^\s*\#\s*Rank\s*\/\s*Sp/", $line)){
            if(preg_match("/\sP\s/", $line)){
							$info_array['include_probability'] = 1;
						}else{
              $info_array['include_probability'] = 0;
            }
            if(preg_match("/\sSf\s/", $line)){
							$info_array['include_scorefinal'] = 1;
						}else{
              $info_array['include_scorefinal'] = 0;
            }
						//next line is of the sort "-------- --------- --------" so skip it 
						$line = fgets($handle, 4096);
						break;
					}
				}
				continue;
			}elseif($part == "PROTEIN"){
        $line = fgets($handle, 4096);
        if(preg_match("/^\s*(\d+)\.\s+(\d+)\s*\/\s*(\d+)/", $line)){
					read_protein($info_array, $line, $rank, $proteinnum, $attachedproteinnum,$deltcn_display_arr);
					continue;
				}else{ 
					$part = "DESCRIPT";    
          read_description($info_array, $line, $rank, $descriptionnum);
          continue;
				}
			}
		}
          
		if($part == "HEADER" && $readheader) {
			read_header($info_array, $line);
		}elseif($proteinnum < $rank && $part == "PROTEIN"){
			read_protein($info_array, $line, $rank, $proteinnum, $attachedproteinnum,$deltcn_display_arr);
		}elseif($descriptionnum < $rank && $readdescript && $part == "DESCRIPT"){
			read_description($info_array, $line, $rank, $descriptionnum);
		}
		if($proteinnum >= $rank && !$readdescript) break;
		if($descriptionnum >= $rank && $readdescript) break;
	}
  if(isset($info_array['proteins'][-1])){
    fclose($handle);
    return false;
  }  
  foreach($deltcn_display_arr as  $deltcn_display_key => $deltcn_display_arr_val){
    if($deltcn_display_arr_val >= 0.003){
      break;
    }
  }
  foreach($info_array['proteins'] as $tmp_key => $tmp_val){
    if($tmp_key < $deltcn_display_key){
      $info_array['proteins'][$tmp_key]['deltcn_display'] = $deltcn_display_arr_val;
    }else{
      $info_array['proteins'][$tmp_key]['deltcn_display'] = $info_array['proteins'][$tmp_key]['deltcn'];
    }  
  }  
	fclose($handle);
  return true;
}

function read_header(&$info_array, $line){
  if(preg_match("/(\S+\.out)/", $line, $matches)){
		$info_array['origfilename'] = $matches[1];
    $tmp_fname_arr = explode(".", $line);
    $info_array['samplefilename'] = $tmp_fname_arr[0];
  }elseif(preg_match("/SEQUEST.*v\.*(\d+).*\(rev\.\s(\d+)\)/", $line, $matches)){
		$info_array['sequestinfo'] = trim($line);
		$info_array['sequestversion'] = $matches[1];
		$info_array['sequestrevision'] = $matches[2];
	}elseif(preg_match("/J.Eng\/(.*\/)?J.Yates/", $line, $matches)){
		$info_array['creator'] = $matches[0];
	}elseif(preg_match("/Licensed/", $line, $matches)){
		$info_array['license'] = $matches[0];
	}elseif(preg_match("/(.*),\s+?(.*)\s+sec\.\s+.*\s(.*)/", $line, $matches)){
    $tmp_date_arr = explode(",", $matches[1]);
		$info_array['runinfo']['rundate'] = trim($tmp_date_arr[0]);
		$info_array['runinfo']['runlengthsecs'] = $matches[2];
		$info_array['runinfo']['runmachine'] = $matches[3];
	}elseif(preg_match("/\(M\+H\)\+ mass =\s*(\d+\.\d+)\s*~\s*(\d+\.\d+)\s+\((.*)\),\s+.*=\s*(.*),\s+(.*)\/(.*)/", $line, $matches)){
		$info_array['mhplus'] = $matches[1];
		$info_array['srchtolerance'] = $matches[2];
		$info_array['srchchgstate'] = $matches[3];
		$info_array['fragtolerance'] = $matches[4];
		$info_array['mass_type_parent'] = $matches[5];
		$info_array['mass_type_fragment'] = $matches[6];
	}elseif(preg_match("/total inten =\s*(.*),.*=\s*(.*),.*=\s*(.*)/", $line, $matches)){
		$info_array['totalinten'] = $matches[1];
		$info_array['lowestsp'] = $matches[2];
		$info_array['nummatchedpeptides'] = $matches[3];
	}elseif(preg_match("/\#\s*amino acids =/", $line, $matches)){
    $line = trim($line);
    $data_tmp_arr = explode(",", $line);
    $data_tmp_arr_0 = explode("=", $data_tmp_arr[0]);
		$info_array['numaminoacids'] = trim($data_tmp_arr_0[1]);
    $data_tmp_arr_1 = explode("=", $data_tmp_arr[1]);
		$info_array['numproteins'] = trim($data_tmp_arr_1[1]);
		$info_array['database'] = basename($data_tmp_arr[2], ".fasta");
		$info_array['databasehdr'] = dirname($data_tmp_arr[2]);
  //}elseif(preg_match("/\#\s*bases =\s*(\d+).*?,\s+.*=\s*(\d+),\s*(.*?\.\w+)(,\s+(\S+))?/", $line, $matches)){
  }elseif(preg_match("/\#\s*bases =\s*(\d+).*?,\s+.*=\s*(\d+),\s*(.*?\.\w+)(,\s+(\S+))?/", $line, $matches)){
    $line = trim($line);
    $data_tmp_arr = explode(",", $line);
    $data_tmp_arr_0 = explode("=", $data_tmp_arr[0]);
		$info_array['numbases'] = trim($data_tmp_arr_0[1]);
    $data_tmp_arr_1 = explode("=", $data_tmp_arr[1]);
		$info_array['numproteins'] = trim($data_tmp_arr_1[1]);
		$info_array['database'] = basename($data_tmp_arr[2], ".fasta");
		$info_array['databasehdr'] = $data_tmp_arr[3];
  }elseif(preg_match("/ion series.*:(.+)$/", $line, $matches)){
    $ions =  explode(" ", trim($matches[1]));
		$info_array['ion_series']['nlA'] = $ions[0];
		$info_array['ion_series']['nlB'] = $ions[1];
		$info_array['ion_series']['nlY'] = $ions[2];
		$info_array['ion_series']['ionA'] = $ions[3];
		$info_array['ion_series']['ionB'] = $ions[4];
		$info_array['ion_series']['ionC'] = $ions[5];
		$info_array['ion_series']['ionD'] = $ions[6];
		$info_array['ion_series']['ionV'] = $ions[7];
		$info_array['ion_series']['ionW'] = $ions[8];
		$info_array['ion_series']['ionX'] = $ions[9];
		$info_array['ion_series']['ionY'] = $ions[10];
		$info_array['ion_series']['ionZ'] = $ions[11];
	}elseif(preg_match("/display\s(.*), ion % =\s*(.*), CODE =\s*(\d+)(,.*=\s*(.*)-(.*))?/", $line, $matches)){
		$info_array['display'] = $matches[1];
		$info_array['ionpercent'] = $matches[2];
		$info_array['code'] = $matches[3];
		$info_array['minproteinmass'] = (isset($matches[5]))?$matches[5]:'';
		$info_array['maxproteinmass'] = (isset($matches[6]))?$matches[6]:'';
	}elseif(preg_match("/sequence header = (.*)/", $line, $matches)){
		$info_array['sequenceheaders'] = $matches[1];
	}elseif(preg_match("/match peak mass = (.*?)\s*~\s*(.*),.*=(.*)/", $line, $matches)){
    $peakmasses = explode(" ", trim($matches[1]));
		$info_array['matchpeak']['matchpeakmasses'] = $peakmasses;
		$info_array['matchpeak']['matchpeaktol'] = $matches[2];
		$info_array['matchpeak']['allowederrors'] = $matches[3];
	}elseif(preg_match("/((\(.+?\)\s+)+)/", $line, $matches)){
		$info_array['modifications']['modline'] = $line;
    $diffmods = explode(")", trim($matches[1]));
		$info_array['modifications']['diffmod'] = $matches[1];
		for($i=0; $i<count($diffmods); $i++){
      if(!$diffmods[$i]) continue;
			$diffmod = $diffmods[$i];
      $diffmod = preg_replace("/\(/", '', $diffmod);
      $tpm = explode(" ", trim($diffmod));
			list($diffname, $diffvalue) = explode(" ", trim($diffmod));
      if(preg_match("/^(\w+)(.*)/", $diffname, $matches)){
  			$diffname = $matches[1];
  			$diffsymbol = $matches[2];
      }
			$info_array['modifications']['diffmods'][$i]['diffmodsymbol'] = $diffsymbol;
			$info_array['modifications']['diffmods'][$i]['diffmodname'] = $diffname;
			$info_array['modifications']['diffmods'][$i]['diffmodvalue'] = $diffvalue;
		}
    $info_array['modifications']['addedmasses']['staticmods'] = '';
    if(preg_match("/.*?((\S+=\d+\.\d+\s+)+).*/", $line, $matches)){
      $info_array['modifications']['addedmasses']['staticmods'] = $matches[1];
		}
    $info_array['modifications']['enzyme'] = '';
    if(preg_match("/Enzyme:(.+)?/", $line, $matches)){
		  $info_array['modifications']['enzyme'] = $matches[1];
    }  
	}
}

function read_protein(&$info_array,$line, $rank, &$pnum, &$anum, &$deltcn_display_arr){
	$proteinnum = $pnum;
	$attachedproteinnum = $anum;
	$regex = "^\s*(\d+)\.\s+(\d+)\s*\/\s*(\d+)\s*(\d*)?\s+(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)\s+";
	$regex .= ($info_array['include_scorefinal'] == 1) ? "(\d+\.\d+)\s+" : "";
	$regex .= ($info_array['include_probability'] == 1) ? "(\S+)\s+" : "";
	$regex .= "(\d+\/\s*\d+)\s+([^\+]+)\s+((\+\d+)\s+)?(\S+)\s*$";

	if(preg_match("/$regex/", $line, $matches)){
		$proteinnum++;
		$pnum = $proteinnum;
		$anum = 0;
		if($proteinnum < $rank){
			$script = 1;
      $tmp_protein_arr = array();
      $tmp_protein_arr['number'] = $matches[$script++];
			$tmp_protein_arr['rank'] = $matches[$script++];
			$tmp_protein_arr['sp'] = $matches[$script++];
			$tmp_protein_arr['id'] = $matches[$script++];
			$tmp_protein_arr['MplusH_plus'] = $matches[$script++];
			$tmp_protein_arr['deltcn'] = $matches[$script++];
      $deltcn_display_arr[$proteinnum] = $tmp_protein_arr['deltcn'];
			$tmp_protein_arr['xcorr'] = $matches[$script++];
			$tmp_protein_arr['sp2'] = $matches[$script++];
      if($info_array['include_scorefinal'] == 1) $tmp_protein_arr['sf'] = $matches[$script++];
      if($info_array['include_probability'] == 1) $tmp_protein_arr['prob'] = $matches[$script++];
			$tmp_protein_arr['ions'] = $matches[$script++];
			$tmp_protein_arr['ions'] = preg_replace('/\s/', '', $tmp_protein_arr['ions']); 
			$tmp_protein_arr['reference'] = $matches[$script++];
			$script++;
			$tmp_protein_arr['moreref'] = $matches[$script++];
			$tmp_protein_arr['peptide'] = $matches[$script++];
      $tmp_protein_arr['unified_score'] = 10000*($tmp_protein_arr['deltcn']*$tmp_protein_arr['deltcn']+$tmp_protein_arr['sp2'])*$tmp_protein_arr['xcorr'];
      $info_array['proteins'][$proteinnum] = $tmp_protein_arr;
		}
  }elseif(preg_match("/^\s{1,}((\d+)\s+)(\w+\|?\S+)(\s(.*))?$/", $line, $matches)){
    $tmp_attachedprotein_arr['id'] = $matches[1];
    //$tmp_attachedprotein_arr['id'] = $matches[2];
    $tmp_attachedprotein_arr['reference'] = $matches[3];
    $tmp_attachedprotein_arr['description'] = $matches[5];
    $info_array['proteins'][$proteinnum]['attachedproteins'][$attachedproteinnum] = $tmp_attachedprotein_arr;
		$attachedproteinnum++;
		$anum = $attachedproteinnum;
	}elseif(preg_match("/^\s+(\d+)\s+$/", $line, $matches)){
    $tmp_attachedprotein_arr['id'] = $matches[1];
    //$tmp_attachedprotein_arr['id'] = $matches[2];
    $tmp_attachedprotein_arr['reference'] = '';
    $tmp_attachedprotein_arr['description'] = '';
    $info_array['proteins'][$proteinnum]['attachedproteins'][$attachedproteinnum] = $tmp_attachedprotein_arr;
		$attachedproteinnum++;
		$anum = $attachedproteinnum;
	}
}

function read_description(&$info_array, $line, $rank, &$dnum){
	$descriptionnum = $dnum;  
  if(preg_match("/^\s{0,2}(\d+)\.\s*(\d+)?\s*(\S+)\s*(.*)$/", $line, $matches)){
		$descriptionnum++;
		$dnum = $descriptionnum;
    if($descriptionnum < $rank){
		  $info_array['proteins'][$descriptionnum]['description'] = $matches[4];
    }   
	}elseif(preg_match("/^\s{1,}((\d+)\s+)?(\w+\|\S+)\s(.*)$/", $line, $matches)){
    $info_array['proteins'][$descriptionnum]['description'] = $matches[1];
	}elseif(preg_match("/^\s{6}(.+)\s*$/", $line, $matches)){
    $info_array['proteins'][$descriptionnum]['description'] = $matches[1];
	}else{
    $info_array['proteins'][$descriptionnum]['description'] = '';
  }  
}

function get_mean($dm){
	$count = count($dm);
	if(!$count) return 0.0 ;
	$total = array_sum($dm);
	return $total / $count;
}

function get_mean_stddev($abs_dm){
	$count = count($abs_dm) - 1;
	if($count < 0) return array(0.0, 0.0);
	$sum_squared_data = 0;
	$sum_data_squared = 0;
	foreach($abs_dm as $abs_val) {
		$sum_squared_data += ($abs_val * $abs_val);
		$sum_data_squared += $abs_val;
	} 
	$mean = $sum_data_squared / ($count + 1);
	$sum_data_squared *= $sum_data_squared;
	$s = (!$count) ? 0 : (1 / $count) * sqrt(((($count+1) * $sum_squared_data ) - $sum_data_squared));
	return array($mean,$s);
}


function pass_filter($filter, $data_arr){
  global $BP_mode;
	$return = '';
  $endreturn = '';
  $not = '';

	if(isset($filter['filterandor']) && $filter['filterandor'] == "and"){
		$return = 0;
		$endreturn = 1;
		$not = "myreverse";
	} else {
		$return = 1;
		$endreturn = 0;
		$not = "keepsame";
	}
	$allowed_z = array();
	$charge = ($data_arr["z"])?$data_arr["z"]:1;
  for($i=1; $i<=5; $i++){
		if($filter["filterz$i"]) $allowed_z[$i] = 1 ;
	}
	if($allowed_z) {
		if(not_f($allowed_z[$charge],$not) || ($allowed_z[5] && $charge > 4)) {
			return $return;
		}
	}
	$greater_than = ($filter['filterScoresgtlt'] != "lt");
  preg_match('/(\d+)\/(\d+)/', $data_arr['ions'], $matches);	
	$contents_Ions = ($matches[2])?($matches[1] / $matches[2])*100:0;
  $filterscores = array();
  if(isset($filter["filterXCorr$charge"])){
    array_push($filterscores, $filter["filterXCorr$charge"]);
  }else{
    array_push($filterscores, '');
  }  
  if(isset($filter["filterdCn$charge"])){
    array_push($filterscores, $filter["filterdCn$charge"]);
  }else{
    array_push($filterscores, '');
  } 
  if(isset($filter["filterSp$charge"])){
    array_push($filterscores, $filter["filterSp$charge"]);
  }else{
    array_push($filterscores, '');
  }     
  if(isset($filter["filterRSp$charge"])){
    array_push($filterscores, $filter["filterRSp$charge"]);
  }else{
    array_push($filterscores, '');
  }     
  if(isset($filter["filterBP$charge"])){
    array_push($filterscores, $filter["filterBP$charge"]);
  }else{
    array_push($filterscores, '');
  }     
  if(isset($filter["filterIons$charge"])){
    array_push($filterscores, $filter["filterIons$charge"]);
  }else{
    array_push($filterscores, '');
  }     
  if(isset($filter["filterSf$charge"])){
    array_push($filterscores, $filter["filterSf$charge"]);
  }else{
    array_push($filterscores, '');
  }     
  if(isset($filter["filterP$charge"])){
    array_push($filterscores, $filter["filterP$charge"]);
  }else{
    array_push($filterscores, '');
  }
	
	$actualscores = array($data_arr["xcorr"],
                        $data_arr["deltcn"],
                        $data_arr["sp2"],
						            $data_arr["rank"],
                        (isset($data_arr[$BP_mode])?$data_arr[$BP_mode]:0),
                        $contents_Ions,
						            (isset($data_arr["sf"])?$data_arr["sf"]:0),
                        (isset($data_arr["prob"])?$data_arr["prob"]:0)
                        );
            
  for($i=0; $i<count($filterscores); $i++){
    if(!$filterscores[$i]) continue;
    $actualscores[$i] = preg_replace('/[^\d\.e]/', '', $actualscores[$i]);
		if($filter['filterScoresgtlt'] != "lt"){
			if(not_f($actualscores[$i] > $filterscores[$i],$not)){
				return $return;
			}
		}else{
			if (not_f($actualscores[$i] <= $filterscores[$i],$not)) {	
				return $return;
			}
		}
	}
	return $endreturn;
}

function not_f($in_v,$not){
  if($not == "myreverse"){
    return !$in_v;
	}elseif($not == "keepsame"){
    return $in_v;
  }  
}
?>










