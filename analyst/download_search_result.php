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
define ("RESULTS_PER_PAGE", 100);
define ("MAX_PAGES", 12);

$searched_bait_str = '';
$searched_sample_str = ''; 
$searched_id_str = '';
$searched_id_vl_str = '';
$item_id_value_arr = array();

$bait_format_default_arr = array('Tag', 'BaitAcc', 'OwnerID');
$sample_format_default_arr = array('S.BaitID','SB.GeneName', 'SB.Tag', 'S.OwnerID', 'S.DateTime');

$bait_lable_arr = array(
  'GeneID'=>"GeneID",
  'LocusTag'=>"LocusTag",
  'BaitAcc'=>"ProteinID",
  'AccType'=>"ProteinType",
  'Tag'=>"Tag",
  'Mutation'=>"Mutation",
  'Clone'=>"Clone",
  'Vector'=>"Vector",
  'OwnerID'=>"User"
);
$sample_lable_arr = array(
	'S.BaitID'=>"BaitID",
	'S.LocusTag'=>"LocusTag",
	'S.DateTime'=>"Date",
	'S.OwnerID'=>"User",
	'S.BaitID'=>"BaitID",
	'SB.GeneID'=>"BaitGeneID",
	'SB.GeneName'=>"BaitGene"
);

require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
require("analyst/status_fun_inc.php");
require_once("msManager/is_dir_file.inc.php");

$users_ID_NameArr = get_users_ID_Name($HITSDB);

if($item_type == 'Bait'){
  if(stristr($title_lable, 'Search hits')){
    $searched_id_vl_str = $hit_Band_ids_v;
  }elseif(stristr($title_lable, 'Search TPP hits')){
    $searched_id_vl_str = $tpp_band_ids_v;
  }  
}

if($searched_id_vl_str){
  $searchE_arr = array();
  $searchE_type_counter = 0;
  $tmp_arr = explode(":", $searched_id_vl_str);
  foreach($tmp_arr as $value){
    $tmp_arr2 = explode(",", $value);
    if(count($tmp_arr2)==2){
      $tmp_arr3 = explode(" ", $tmp_arr2[1]);
      if(!array_key_exists($tmp_arr3[0], $searchE_arr)){
        $searchE_arr[$tmp_arr3[0]] = ++$searchE_type_counter;
      }
      $tmp_str = str_replace(" / ", " ", $tmp_arr2[1]);
      $tmp_str = str_replace(" ", ",", $tmp_str);
      $item_id_value_arr[$tmp_arr2[0]] = $tmp_str;
    }
  }
}

$Items = array();
if($item_type == 'Bait' && $searched_bait_str){
  $item_format_default_arr = $bait_format_default_arr;
  $item_lable_arr = $bait_lable_arr;
  $SQL = "SELECT 
         ID, 
         GeneID,
         LocusTag, 
         GeneName,
         BaitAcc,
         AccType, 
         TaxID, 
         BaitMW, 
         Family, 
         Tag,
         Mutation, 
         Clone, 
         Vector, 
         OwnerID, 
         ProjectID,
         GelFree, 
         DateTime
         FROM Bait
         WHERE ProjectID='$AccessProjectID'
         AND ID IN($searched_bait_str)";
}elseif($item_type == 'Band' && $searched_sample_str){
  $item_format_default_arr = $sample_format_default_arr;
  $item_lable_arr = $sample_lable_arr;
  $SQL = "SELECT 
        S.ID,
        S.ExpID, 
        S.LaneID,
        S.BaitID,
        S.Location,
        S.OwnerID,
        S.DateTime,
        S.InPlate,
        SB.ID AS BaitID,
			  SB.GeneID,
        SB.Tag,
        SB.GelFree,
        SB.GeneName,
			  SB.Tag,
			  SB.Mutation,
        SB.Clone,
			  SB.Vector 
        FROM Band S left join Bait SB on (SB.ID = S.BaitID) 
        WHERE S.ProjectID='$AccessProjectID'
        And S.ID IN($searched_sample_str)";
}

if($SQL){
  $SQL .= " ORDER BY ID DESC";
  $Items = $HITSDB->fetchAll($SQL);
}

//get bait and exp column names----------------------------
$SQL = "SELECT `ID`,ParentID, `Name` FROM `ExpDetailName`";
$exp_optionID_name_array = array();
$all_exp_details_arr = $PROHITSDB->fetchAll($SQL);
foreach($all_exp_details_arr as $tmp_arr){
	$exp_optionID_name_array[$tmp_arr['ID']] = $tmp_arr;
}

$item_format_arr = array();
$colums_number = 0;

$displayFormat['Format'] = $displayFormat_str;
if($displayFormat['Format']){
  $tmp_arr = explode(",", $displayFormat['Format']);
  foreach($tmp_arr as $value){
    if($item_type == 'Bait'){
      if(preg_match("/^([BE])\.(.+)/", $value, $matches)){
        if(count($matches) == 3){
          if($matches[1] == 'B'){
            array_push($item_format_arr, $matches[2]);
            $colums_number++;
          }else{            
            if(isset($exp_optionID_name_array) and isset($exp_optionID_name_array[$matches[2]])){
              array_push($item_format_arr, $exp_optionID_name_array[$matches[2]]);
              $colums_number++;
            }
          }
        }
      }
    }elseif($item_type == 'Band'){
    	if(preg_match("/^([SE]|SB)\.(.+)/", $value, $matches)){
    		if(count($matches) == 3){
    			if($matches[1] == 'E'){
    				if(isset($exp_optionID_name_array) and isset($exp_optionID_name_array[$matches[2]])){
    					array_push($item_format_arr, $exp_optionID_name_array[$matches[2]]);
              $colums_number++;
    				}
    			}else{
    				array_push($item_format_arr, $value);
    				$colums_number++;
    			}
    		}
    	}
    }
  }
}

if(!$colums_number){
  $item_format_arr = $item_format_default_arr;
}

if(!_is_dir("../TMP/adv_search_report/")) _mkdir_path("../TMP/adv_search_report/");
$tmp_file = "../TMP/adv_search_report/". $USER->ID .".csv";

if(!$handle = fopen($tmp_file, 'w')){
  echo "Cannot open file ($tmp_file)";
}

$line = urldecode($title_lable)."\r\n";
fwrite($handle,"$line");

if($item_type == 'Bait'){
  $line = "Bait ID,Bait Name";
}elseif($item_type == 'Band'){
  $line = "Sample ID,Sample Name";
}

foreach($item_format_arr as $value){
  if($line) $line .= ',';
  if(!is_array($value)){
		$line .= (isset($item_lable_arr[$value]))?$item_lable_arr[$value]:$value;
	}else{
		$line .= $value['Name'];
	}
}

if($item_id_value_arr){
  if($line) $line .= ',';
  $line .= "SearchEngine,Score or Probability,# Total peptide";
}
fwrite($handle,"$line\r\n");

$tem_bandID_arr = array();
$tem_baitID_arr = array();

foreach($Items as $ItemValue){
  $line = '';
  $ownerName = '';
	if(isset($users_ID_NameArr[$ItemValue['OwnerID']])){
		$ownerName = $users_ID_NameArr[$ItemValue['OwnerID']];
	}
  if($item_type == 'Band'){
    $line = $ItemValue['ID'].','.$ItemValue['Location'];
    foreach($item_format_arr as $value){
      $value = preg_replace("/^(S|SB)\./", "", $value);//---------------------------------
	    $tmp_display = '';
      if(!is_array($value)){
  			if($value == 'OwnerID'){
  				$tmp_display = $ownerName;
  			}else if($value=='DateTime'){
  			  $tmp_display = substr($ItemValue['DateTime'],0,10);
  			}else{
  				$tmp_display = $ItemValue[$value];
  			}
      }else{
        $SQL = "SELECT OptionID FROM ExpDetail WHERE ExpID='".$ItemValue['ExpID']."' and SelectionID='".$value['ID']."'";
    		$tmpOption = $HITSDB->fetch($SQL);        
    		if($tmpOption){
    			if(isset($exp_optionID_name_array[$tmpOption['OptionID']])){
    			  $tmp_display = $exp_optionID_name_array[$tmpOption['OptionID']]['Name'];
    			}
    		}
      }
      if($line) $line .= ',';
      $line .= $tmp_display;
    }
    //$statusArr = get_status($ItemValue['ID'],"Band",$toggle_arr);
    if($item_id_value_arr){
        if($line) $line .= ',';
        $line .= $item_id_value_arr[$ItemValue['ID']];
    }
    fwrite($handle,"$line\r\n");
  }elseif($item_type == 'Bait'){
  
    if(stristr($title_lable,'hits')){    
      $SQL ="SELECT `ID`,`ExpID`,`BaitID` FROM `Band` WHERE `BaitID`='".$ItemValue['ID']."' order by ID";
      $BandArr = $HITSDB->fetchAll($SQL);
      $ExpIdArr = array();
      foreach($BandArr as $BandVal){
        if(array_key_exists($BandVal['ID'], $item_id_value_arr)){
          $line = $ItemValue['ID'].','.$ItemValue['GeneName'];
          foreach($item_format_arr as $value){
            $tmp_display = '';
            if(!is_array($value)){
              if($value == 'OwnerID'){
        				$tmp_display = $ownerName;
        			}else if($value=='DateTime'){
        			  $tmp_display = substr($ItemValue['DateTime'],0,10);
        			}else{
        				$tmp_display = $ItemValue[$value];
        			}
            }else{
              $SQL = "SELECT OptionID FROM ExpDetail WHERE ExpID='".$BandVal['ExpID']."' and SelectionID='".$value['ID']."'";
          		$tmpOption = $HITSDB->fetch($SQL);        
          		if($tmpOption){
          			if(isset($exp_optionID_name_array[$tmpOption['OptionID']])){
          			  $tmp_display = $exp_optionID_name_array[$tmpOption['OptionID']]['Name'];
          			}
          		}
            }
            if($line) $line .= ',';
            $line .= $tmp_display;
          }
          //$statusArr = get_status($ItemValue['ID'],"Band",$toggle_arr);
          if($item_id_value_arr){
              if($line) $line .= ',';
              $line .= $item_id_value_arr[$BandVal['ID']];
          }
          fwrite($handle,"$line\r\n");
        }
      }
    }else{
      $line = $ItemValue['ID'].','.$ItemValue['GeneName'];
      foreach($item_format_arr as $value){
        $tmp_display = '';
        if(!is_array($value)){
          if($value == 'OwnerID'){
    				$tmp_display = $ownerName;
    			}else if($value=='DateTime'){
    			  $tmp_display = substr($ItemValue['DateTime'],0,10);
    			}else{
    				$tmp_display = $ItemValue[$value];
    			}
        }else{
          $SQL ="SELECT `ID` FROM `Experiment` WHERE `BaitID`='".$ItemValue['ID']."' order by ID"; 
          $ExpArr = $HITSDB->fetchAll($SQL);
          foreach($ExpArr as $ExpValue){            
            $SQL = "SELECT OptionID FROM ExpDetail WHERE ExpID='".$ExpValue['ID']."' and SelectionID='".$value['ID']."'";
          	$tmpOption = $HITSDB->fetch($SQL);        
          	if($tmpOption){
        			if(isset($exp_optionID_name_array[$tmpOption['OptionID']])){
        			  $tmp_display = $exp_optionID_name_array[$tmpOption['OptionID']]['Name'];
        			}
        		}
          }
        }
        if($line) $line .= ',';
        $line .= $tmp_display;
        //$statusArr = get_status($ItemValue['ID'],"Band",$toggle_arr);
      }
      fwrite($handle,"$line\r\n");
    }
  }
}
export_file($tmp_file);
?>
