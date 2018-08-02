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

/****************************************************************************\
author: Frank Liu
date:   2004-01-21
description: 
        1. this is a included file in ../../ms_autosearch_deca_results_wells.php
        2. the script will handle user input of auto save a plate.
        3. only users who have permitions can submit the form.
        4. after 24 hours if status still is 'no process'. user can Click Save 
           to execute auto_save.php -- shell script again.
        5. set variabels $midnight and $earlymorning in auto_save.php
        6. If variable $TEST_ONLY = 1, the target database is 'prohitsDev'
           do not need to modidy other file.
        7. get the target (hits) dbs the user can save to.
        8. if $TEST_ONLY it will save hits to prohitsDev database.
        9. http://10.197.104.21/mascot/cgi/prohits_parser.pl?file=F:/Mascot_data/20040827/F038251.dat&hit_min_score=100&peptide_min_size=0&matched_ion_percentage=1&peptide_min_charge=2&matched_ions_group_size=6&matched_ions_num=4&is_modified_peptide=0&peptide_min_score=27&requireBoldRed=1&field_spliter=;;
           
\*****************************************************************************/
$debug = 0;     //a command line will be showed on the window and run it on shell.
$error_msg = '';
$the_plate_in_dbs = array();
$the_plate_in_dbs_str = '';
$tmp_new_wells_str = '';
if(!isset($frm_parser_setID)) $frm_parser_setID = 0;
if(!isset($frm_gpmexpect))  	$frm_gpmexpect = 1;
if(!isset($frm_gpmeionxpect)) $frm_gpmeionxpect=1;
$error_msg = '';
$p_msg = '';
//-------------------------------------------------------------

if($myaction == "changeParserType" && $frm_parser_type != 'search'){
  $geneLevelNotParsed_arr = array();
  $SQL = "SELECT `ID`, 
                 `Machine`, 
                 `TaskID`, 
                 `TppID`, 
                 `pepXML_original`, 
                 `pepXML`, 
                 `ProhitsID`, 
                 `SearchEngine`, 
                 `ProjectID`, 
                 `isUploaded`, 
                 `FastaFile`, 
                 `Parsed` 
         FROM `GeneLevelParse` 
         WHERE `Machine`='$table'
         AND `TaskID`='$iniTaskID' 
         AND `TppID`='$tppTaskID'
         AND `Parsed`!='1'";
         
  $geneLevelNotParsed_arr = $managerDB->fetchAll($SQL);
  if($geneLevelNotParsed_arr) $parseOldGeneLevel = 1;
}
//-------------------------------------------------------------

//defined value
//$task_arr = $task_records[0];
//$tableSearchResults = $table . "SearchResults";
//$tableSearchTasks = $table . "SearchTasks";
//$prohitsDB = new mysqlDB(PROHITS_DB);
//$managerDB = new mysqlDB(MANAGER_DB);
//***********************************************

$CHECK_TARGETDB_BAND_ID = 1; 
                 //=0, A01_2345.RAW only check A01 in the plate but the target plate name and id-++
                 // will be checked.
                 //=1, A01.RAW only check A01, A01_2345.RAW wil be checked band_id 2345. 
//***********************************************
require_once("./common_functions.inc.php"); 
require("classes/saveConf_class.php");

$default_score = 100; //for defult form interface 

$phpself_dir = dirname($_SERVER['SCRIPT_FILENAME']) . "/autoSave"; 
$analyst_dir = str_replace(strstr($phpself_dir, "msManager"), '', $phpself_dir) . "analyst";

//$hits_plate_ID = $task_arr['ProhitsID'];
$hits_project_ID = $task_arr['ProjectID'];
$task_ID = $task_arr['ID'];

//add_sequest_field_on_conf($table,$managerDB);

$SID = session_id();
$Conf = new SaveConf($table, $managerDB->link);
//processing action
if(($myaction == 'auto-save' or $myaction == 'run_parser_again') and $perm_insert  and $task_ID){
  //update MsPlate information
  $run_parser = 0;
  if($myaction == 'run_parser_again' and isset($SaveConf_record_ID)){
    $run_parser = 1;
  }else{
    addIn_other_value("peptide_min_score", $frm_peptide_min_score);
    addIn_other_value("requireBoldRed", $frm_requireBoldRed);
    addIn_other_value("sigthreshold", $frm_sigthreshold);
    addIn_other_value("report", $frm_report);
    addIn_other_value("_mudpit", $_mudpit);
    addIn_other_value("sequest_rank", $sequest_rank);
    
    $tmp_gpm_other_value = "proex=$frm_gpmexpect,pepex=$frm_gpmeionxpect";
    if($frm_gpmexpect=='0')$frm_gpmexpect_dot = 0;
    if($frm_gpmeionxpect == '100') $frm_gpmeionxpect_dot = 0;
    $tmp_gpm_other_value .= ",proex_dot=$frm_gpmexpect_dot,pepex_dot=$frm_gpmeionxpect_dot";
    $tmp_sequest_other_value = "sequest_rank=$sequest_rank";
    
//echo "$frm_well_id_str_Mascot";   
//exit; 
    
    if($frm_well_id_str_Mascot or $frm_well_id_str_GPM or $frm_well_id_str_SEQUEST or $frm_well_id_str_MSPLIT or $frm_well_id_str_MSPLIT_DDA or $frm_well_id_str_TPP or $parseOldGeneLevel){
      if($frm_well_id_str_SEQUEST) $frm_well_id_str_SEQUEST = 'SEQUEST:'.$frm_well_id_str_SEQUEST;
      if($frm_well_id_str_MSPLIT) $frm_well_id_str_MSPLIT = 'MSPLIT:'.$frm_well_id_str_MSPLIT;
      if($frm_well_id_str_MSPLIT_DDA) $frm_well_id_str_MSPLIT_DDA = 'MSPLIT_DDA:'.$frm_well_id_str_MSPLIT_DDA;
      $frm_well_id_str_SEQUEST .= $frm_well_id_str_MSPLIT;
      $frm_well_id_str_SEQUEST .= $frm_well_id_str_MSPLIT_DDA;
      if($SWATH_app == 'MSPLIT'){
        $frm_geneLevelHits_str = "";
      }else{
        $frm_geneLevelHits_str = "frm_geneLevelHits=$frm_geneLevelHits;";
      }    
      $TMP_PROBABILITY = "frm_TPP_PARSE_MIN_PROBABILITY=$frm_TPP_PARSE_MIN_PROBABILITY;frm_geneLevelFDR=$frm_geneLevelFDR;".$frm_geneLevelHits_str."frm_pepPROBABILITY=$frm_pepPROBABILITY";      
      $SQL ="INSERT INTO $tableSaveConf SET 
            TaskID='$task_ID', 
            Mascot_SaveScore='$frm_score', 
            Status='processing',
            SaveBy='".$USER->Fname.' '.$USER->Lname."', 
            SetDate=now(), 
            Mascot_SaveWell_str='$frm_well_id_str_Mascot', 
            GPM_SaveWell_str='$frm_well_id_str_GPM',
            SEQUEST_SaveWell_str='$frm_well_id_str_SEQUEST', 
            Mascot_Other_Value='$frm_other_value', 
            GPM_Value='$tmp_gpm_other_value',
            SEQUEST_Value='$tmp_sequest_other_value',
            Tpp_Value='$TMP_PROBABILITY',
            DECOY_prefix='$DECOY_prefix',
            TppTaskID='$tppTaskID'";                        
      if($frm_well_id_str_TPP){
         $SQL .= ",Tpp_SaveWell_str='$frm_well_id_str_TPP'";
      }
      $SaveConf_record_ID = $managerDB->insert($SQL);
      $run_parser = 1;
    }
  }
  if($run_parser){
    if(defined("DEBUG_SAVE_HITS") and DEBUG_SAVE_HITS){
      $debug = 1;
    } 
    $user_id = $USER->ID;
//===============================================================================================================================    
//echo "<script language=javascript>document.location.href='./autoSave/auto_save_shell.php?SID=$SID&table=$table&task_ID=$task_ID&debug=$debug&CHECK_TARGETDB_BAND_ID=$CHECK_TARGETDB_BAND_ID&user_id=$user_id&prohits_server_name=".$_SERVER['SERVER_NAME']."';</script>";
//exit;
//===============================================================================================================================     
   
    $comm = "cd $phpself_dir; ".PHP_PATH." $phpself_dir/auto_save_shell.php $SID $table $task_ID $debug $CHECK_TARGETDB_BAND_ID $USER->ID ". $_SERVER['SERVER_NAME'];
    if(!$debug){
      $tmp_PID =  system($comm." > /dev/null & echo \$!");
      $SQL = "update ". $tableSaveConf ." set Status='processing:$tmp_PID' where ID='$SaveConf_record_ID'";
       
      $managerDB->execute($SQL);
      ?>
        <script language=javascript>
        document.location = "<?php echo $PHP_SELF;?>?frm_PlateID=<?php echo $frm_PlateID;?>&taskIndex=<?php echo $taskIndex;?>&table=<?php echo $table;?>&iniTaskID=<?php echo $iniTaskID;?>";
        </script>
      <?php 
      exit;
    }else{
      echo "<b><font color=red>For Debug please copy following line into shell:</font> <br></b>$comm";
    }
  }else{
    $error_msg = "You didn't select any wells to save.";
  }  
}else if(($myaction == 'new_filter_set' or $myaction == 'modify_filter_set') and $perm_insert){
	addIn_other_value("peptide_min_score", $frm_peptide_min_score);
  addIn_other_value("requireBoldRed", $frm_requireBoldRed);
  addIn_other_value("sigthreshold", $frm_sigthreshold);
  addIn_other_value("report", $frm_report);
  addIn_other_value("_mudpit", $_mudpit);
  addIn_other_value("sequest_rank", $sequest_rank);
  $tmp_gpm_other_value = "proex=$frm_gpmexpect,pepex=$frm_gpmeionxpect";
  
  if($SWATH_app == 'MSPLIT'){
    $frm_geneLevelHits_str = "";
  }else{
    $frm_geneLevelHits_str = "frm_geneLevelHits=$frm_geneLevelHits;";
  }   
  $TMP_PROBABILITY = "frm_TPP_PARSE_MIN_PROBABILITY=$frm_TPP_PARSE_MIN_PROBABILITY;frm_geneLevelFDR=$frm_geneLevelFDR;".$frm_geneLevelHits_str."frm_pepPROBABILITY=$frm_pepPROBABILITY";      
    
  if($frm_gpmexpect=='0')$frm_gpmexpect_dot = 0;
  if($frm_gpmeionxpect == '100') $frm_gpmeionxpect_dot = 0;
  $tmp_gpm_other_value .= ",proex_dot=$frm_gpmexpect_dot,pepex_dot=$frm_gpmeionxpect_dot";
	$frm_para_str  =  "Parser_type=$frm_parser_type\n";
	$frm_para_str .= "Mascot_SaveScore=$frm_score\n";
  $frm_para_str .= "DECOY_prefix=$DECOY_prefix\n"; 
  $frm_para_str .= "Mascot_Other_Value=$frm_other_value\n";
  $frm_para_str .= "GPM_Value=$tmp_gpm_other_value\n";
  $frm_para_str .= "TPP_Value=$TMP_PROBABILITY\n";
	$tmp_parser_set = 0;
    
	if($myaction == 'modify_filter_set'){
		$tmp_parser_set = $frm_parser_setID;
	}
	$frm_parser_setID = search_para_add_modify('PARSER', $tmp_parser_set, $frm_new_parser_filter_name, $USER->ID, $task_arr['ProjectID'], $frm_para_str);
  if(!$frm_parser_setID){
    $error_msg = "<br>The name '$frm_new_parser_filter_name' has been used. Please use other name!";
  }
	$myaction = '';
}else if($myaction != 'changeParserType'){
  $frm_parser_type = '';
}

$paser_set_arr = get_search_parameters('PARSER', 0, $task_arr['ProjectID']);


//JP 2017/05/30--------------------------------------------------------------------------------------------------------
if($myaction == 'delete_hits' and $frm_delete_well_id and $frm_delete_searchEngine and $perm_delete){
  $project_ID_DBname = get_projectID_DBname_pair($prohitsDB);
  remove_hits_and_peptide($table, $frm_delete_well_id, $task_ID, $frm_delete_searchEngine);
}
//---------------------------------------------------------------------------------------------------------------------
$SQL = "SELECT 
         ID, 
         TaskID, 
         Mascot_SaveScore, 
         Mascot_SaveValidation, 
         Status, 
         SaveBy, 
         SetDate, 
         Mascot_SaveWell_str, 
         GPM_SaveWell_str, 
         Mascot_Other_Value, 
         GPM_Value, 
         Tpp_SaveWell_str,
         Tpp_Value,
         DECOY_prefix  
         FROM $tableSaveConf where TaskID='$task_ID' order by ID desc limit 1";
$the_SaveConf_record = $managerDB->fetch($SQL);
//---------------------------------------------
if($the_SaveConf_record){
	$form_value = $the_SaveConf_record;
	get_parser_form($form_value);
}
//----------------------------------------------
if(!$the_SaveConf_record){
	$frm_status = 'not saved';
	$frm_savedy = '';
}

$color_parse = '#357b73';
$on_process = 0;
 
 if(strpos($frm_status, 'processing') === 0){
   //check if the processing is more then 24 hours, it means the processing has been interrupted.
   $pro_hours = 12;
   if(onProcess($frm_setdate, $pro_hours, $frm_status)){
     $on_process = 1;
   }else{
     $frm_status .= ": The process is not running. Maybe it was interrupted.";
     if($perm_insert) $frm_status .= "You can Click '<a href=\"javascript:run_parser_again(".$the_SaveConf_record['ID'].")\")>HERE</a>' to try again";
   }
 }
 $insert_only = 1;
 $Pro_ID_names = get_user_permited_project_id_name($prohitsDB, $USER->ID, $insert_only);
 if(!$on_process and $perm_insert){
   $has_parser_permit = true;
 } 
 ?>
 <script language="javascript">
 function get_id_str(fieldObj){
   var str = '';
   if(typeof fieldObj === 'undefined') return str; 
   if(typeof fieldObj.length === 'undefined'){
     if(fieldObj.checked){
       str = fieldObj.value;
     }else{
       return str;
     } 
   }else{
     if(fieldObj){
       for (var i = 0; i < fieldObj.length; i++){   
         if(fieldObj[i].checked){
           if(str != ''){
             str += ';';
           }
           str += fieldObj[i].value
         }
       }
     }
   }
   return str;
 }
 
 function have_checked_any(theForm){
   var rt = false; 
   theForm.frm_well_id_str_TPP.value = '';
   var gpm_id_str = get_id_str(theForm.frm_wells_GPM);
   if(gpm_id_str){
      theForm.frm_well_id_str_GPM.value = gpm_id_str;
      rt = true;
   }
   var mascot_id_str = get_id_str(theForm.frm_wells_Mascot);
   if(mascot_id_str){
     theForm.frm_well_id_str_Mascot.value = mascot_id_str;
     rt = true;
   }
   var SEQUEST_id_str = get_id_str(theForm.frm_wells_SEQUEST);
   if(SEQUEST_id_str){
     theForm.frm_well_id_str_SEQUEST.value = SEQUEST_id_str;
     rt = true;
   }
   
   var MSPLIT_id_str = get_id_str(theForm.frm_wells_MSPLIT);
   if(MSPLIT_id_str){
     theForm.frm_well_id_str_MSPLIT.value = MSPLIT_id_str;
     rt = true;
   }
   
   var MSPLIT_DDA_id_str = get_id_str(theForm.frm_wells_MSPLIT_DDA);
   if(MSPLIT_DDA_id_str){
     theForm.frm_well_id_str_MSPLIT_DDA.value = MSPLIT_DDA_id_str;
     rt = true;
   }
   
    
   var tpp_gpm_id_str = get_id_str(theForm.frm_tpp_GPM);
   if(tpp_gpm_id_str){
     theForm.frm_well_id_str_TPP.value += "GPM:"+tpp_gpm_id_str; 
     rt = true;
   }   
   var tpp_mascot_id_str = get_id_str(theForm.frm_tpp_Mascot);
   if(tpp_mascot_id_str){
     theForm.frm_well_id_str_TPP.value += "Mascot:" + tpp_mascot_id_str;
     rt = true;
   }   
   var tpp_SEQUEST_id_str = get_id_str(theForm.frm_tpp_SEQUEST);
   if(tpp_SEQUEST_id_str){
     theForm.frm_well_id_str_TPP.value += "SEQUEST:" + tpp_SEQUEST_id_str;
     rt = true;
   }
   var tpp_COMET_id_str = get_id_str(theForm.frm_tpp_COMET);
   if(tpp_COMET_id_str){
     theForm.frm_well_id_str_TPP.value += "COMET:" + tpp_COMET_id_str;
     rt = true;
   }
   var tpp_MSFragger_id_str = get_id_str(theForm.frm_tpp_MSFragger);
   if(tpp_MSFragger_id_str){
     theForm.frm_well_id_str_TPP.value += "MSFragger:" + tpp_MSFragger_id_str;
     rt = true;
   }
//------------------------------------------------------------------------------------------------   
   var tpp_MSGFPL_id_str = get_id_str(theForm.frm_tpp_MSGFPL);
   if(tpp_MSGFPL_id_str){
     theForm.frm_well_id_str_TPP.value += "MSGFPL:" + tpp_MSGFPL_id_str;
     rt = true;
   }   
//------------------------------------------------------------------------------------------------   
   var tpp_iProphet_id_str = get_id_str(theForm.frm_tpp_iProphet);
   if(tpp_iProphet_id_str){
     theForm.frm_well_id_str_TPP.value += "iProphet:" + tpp_iProphet_id_str;
     rt = true;
   } 
   return rt;
 }
 
 function check_frm_other_value(theForm){
   if(theForm.frm_other_value.value.length == 0){
     //set default value parameters
     theForm.frm_other_value.value = '';
   }
   if(theForm.frm_BoldRed.checked){
    theForm.frm_requireBoldRed.value = "1";
  }else{
    theForm.frm_requireBoldRed.value = "0";
  } 
  if(parseInt(theForm.frm_report.value) > 0){
    if(confirm('When you set a Max number of hits the Significance threshold will be 0!\nDo you want to continue?')){
     theForm.frm_sigthreshold.value = '0';
    }else{
      theForm.frm_report.value = 'AUTO';
      if(parseInt(theForm.frm_sigthreshold.value) >= 0){
         theForm.frm_sigthreshold.value = '0.05';
      }
      return false;
    }
  }
  return true;
 }
 function run_parser_again(conf_save_ID){
   var theForm = document.forms[0];
   theForm.SaveConf_record_ID.value=conf_save_ID;
   theForm.myaction.value = "run_parser_again";
   theForm.submit();
 }
 function auto_save(theForm){
   //check form well chekcbox array 'frm_wells'
   if(!checked_type(theForm.frm_parser_type)){
     alert("Please select a parser type!");
     return false;
   }
   if(!have_checked_any(theForm)){
//alert(theForm.parseOldGeneLevel.value);
      if(theForm.parseOldGeneLevel.value == 1 && theForm.frm_geneLevelHits.checked){
        if(confirm("Do you want to parse gene level hits? Click OK, it will parse gene level hits their TPP hits have been saved.")){
          theForm.myaction.value = "auto-save";
          theForm.submit();
        }else{
          return false;
        }
      }else{
        alert("Please check results you want to save.");
        return false;
      }
   }else if(confirm('If all fields you seleced are correct, click OK.\n You only can remove hits after the process has been finished.')){
     if(!check_frm_other_value(theForm)){
       return false;
     }
     theForm.myaction.value = "auto-save";
     theForm.submit();
   }else{
     return false;
   }
 }
 
 function checked_type(parseTypeObj){
   var isChecked = false;
   for(var j = 0; j< parseTypeObj.length; j++){
     if(parseTypeObj[j].checked){
       isChecked = true;
     }
   }
	 return isChecked;
 }
 
 var removed_ids = [];
 
 function removehits(well_id, searchEngine){
   if(well_id == 'All'){
     msg = "all search engines";
     if(confirm("Are you sure that you want to remove hits from " + msg + "?")){
       var theForm = document.forms[0];
       theForm.frm_delete_well_id.value = well_id;
       theForm.myaction.value = "delete_hits";
       theForm.frm_delete_searchEngine.value = searchEngine;
       theForm.submit();
     }
   }else{
     var div_id = well_id+'@@'+searchEngine;
     chang_icon(div_id);
   }
   return;
 }
 
 function removeTPPhits(tpp_id, well_id, searchEngine){
   if(well_id == 'All'){
     var msg = "ID " + well_id;
     if(confirm("Are you sure that you want to remove " + searchEngine + " hits from " + msg + "?")){
       var theForm = document.forms[0];
       theForm.frm_delete_well_id.value = well_id;
       theForm.myaction.value = "delete_tpphits";
       theForm.frm_delete_searchEngine.value = searchEngine;
       theForm.tppTaskID.value = tpp_id;
       theForm.submit();
     }
   }else{
     var div_id = well_id+'@@'+searchEngine+'@@'+tpp_id;
     chang_icon(div_id); 
   }
   return;
 }
 
 function remove_listed_hits(){
    var removed_str = '';
    var display_str = '';
    for(var i=0; i<removed_ids.length; i++){
      if(removed_ids[i] == undefined) continue;
      if(removed_str) removed_str += ',';
      removed_str += removed_ids[i];
      var tem_arr = removed_ids[i].split("@@");
      if(tem_arr[1] == "GPM") tem_arr[1] = "XTandem";
      if(tem_arr.length == 3 && tem_arr[1] != "iProphet") tem_arr[1] = "TPP " + tem_arr[1];
      var tmp_str = "file ID: "+tem_arr[0]+"\t\t"+tem_arr[1];
      if(tem_arr.length == 3){
        //var tmp_str = tmp_str+"\ttpp task ID: "+tem_arr[2];
      }
      display_str = display_str + tmp_str + "\n";
    }
    if(!display_str){
      alert('Please select parsed results to delete.');
      return;
    }
    if(confirm("Are you sure that you want to delete hits from the list:\n" + display_str + "?")){
       var theForm = document.forms[0];
       theForm.frm_delete_well_id.value = removed_str;
       theForm.myaction.value = "delete_selsected_hits";
       theForm.submit();
    }
    return;
 } 
 
 function chang_icon(div_id){
    var find_id = 0;
    for(var i=0; i<removed_ids.length; i++){
      if(removed_ids[i] == div_id){
        delete removed_ids[i];
        document.getElementById(div_id).title = "to-be-deleted";
        document.getElementById(div_id).innerHTML = "<img src=./images/icon_delete.gif border=0>";
        find_id = 1;
        break;
      }
    }
    if(!find_id){
      document.getElementById(div_id).title = "do not delete";      
      document.getElementById(div_id).innerHTML = "<img src=./images/gray_yellow6.gif border=0 width=13 height=13>";
      removed_ids.push(div_id); 
    }  
 } 
 
function modify_filter_set(){
	var theForm = document.forms[0];
	theForm.myaction.value = "modify_filter_set";
	if(!check_frm_other_value(theForm)){
       return false;
  }
	theForm.submit();
}

function new_filter_set(){
	var theForm = document.forms[0];
	if(isEmptyStr(theForm.frm_new_parser_filter_name.value)){
		alert('Please type filter set name');
		return false;
	}
	if(!checked_type(theForm.frm_parser_type)){
     alert("Please select a parser type!");
     return false;
  }
  if(!check_frm_other_value(theForm)){
       return false;
  }
	theForm.myaction.value = "new_filter_set";
	theForm.submit();
}

function changePaserSet(){
	var theForm = document.forms[0];
	theForm.myaction.value = "change_paser_set";
	theForm.submit();
}
</script>
 <input type=hidden name=SaveConf_record_ID value=''>
 <input type=hidden name=frm_other_value value=''>
 <input type=hidden name=frm_well_id_str value=''>
 <input type=hidden name=frm_well_id_str_Mascot value=''>
 <input type=hidden name=frm_well_id_str_GPM value=''>
 <input type=hidden name=frm_well_id_str_SEQUEST value=''>
 <input type=hidden name=frm_well_id_str_MSPLIT value=''>
 <input type=hidden name=frm_well_id_str_MSPLIT_DDA value=''>
 <input type=hidden name=frm_well_id_str_TPP value=''>
 <input type=hidden name=frm_delete_well_id value=''>
 <input type=hidden name=frm_delete_searchEngine value=''>
 <input type=hidden name=frm_requireBoldRed value=<?php echo $frm_requireBoldRed;?>>
 <input type=hidden name=parseOldGeneLevel value=<?php echo $parseOldGeneLevel;?>>
 <br>
 <table border="0" bgcolor='<?php echo $color_parse;?>' width=100% cellpadding="0" cellspacing="0" height=25>
   <tr>
     <td colspan="3" nowrap>
       <b><font size="2" color="white"> Parse Hits to Prohits Analyst database </font></b>
			
     </td>
     <td align=right width=90% bgcolor="white">
     <a href="javascript: popwin('../doc/management_help.html#Parsing',782,600,'help');"><img src=./images/icon_help.gif border=0></a>
     <a href="javascript:showhide('parsehits', 'parsehits_a')" id=parsehits_a class='button' title='click to select parser filters'>[&nbsp;Detail&nbsp;]</a>
     </td>
   </tr>
</table>
<DIV style="border: 1px solid <?php echo $color_parse;?>;" >
 
 <table cellpadding="1" cellspacing="1" bgcolor='white' width=100% border=0>
   <tr>
     <td bgcolor="#e0e0e0" width=30%><b>Parsed Hits Status:</b></td>
     <td bgcolor="#e0e0e0"> <font color="#ff0000"><?php echo $frm_status;?></font> <?php echo $frm_setdate;?></td>
   </tr>
   <tr>
     <td bgcolor="#e0e0e0"><b>Parsed By:</b></td>
     <td bgcolor="#e0e0e0">
<?php 
$selected_parser_set_arr = array();
if($the_SaveConf_record){
	echo $frm_saveby;
	echo "&nbsp; &nbsp; &nbsp; &nbsp;[<a href=\"javascript: popwin('./ms_search_parser_detail.php?table=$table&task_ID=".$task_ID."',650,400)\" class=button title='click to display filter detail'>Detail</a>]";
}
?>
		 </td>
   </tr>
 </table>
 
 <DIV ID="parsehits" style="display:<?php echo ($frm_parser_type or $frm_parser_setID or $error_msg)?'block':'none';?>">
<table cellpadding="1" cellspacing="1" bgcolor='white' width=100% border=0>
 <?php if($has_parser_permit){?>
	<tr>
     <td colspan=3 bgcolor="#e0e0e0"><b>Pre-defined Filter Set:</b>
		&nbsp;
		<select name="frm_parser_setID" size="1" onChange="changePaserSet()">
   		<option value='0'>default
<?php 
		  
			$the_parser_set_user = '';
      foreach($paser_set_arr as $tmpSet){
        $selected = "";
				if($tmpSet['ID'] == $frm_parser_setID){
					$selected = " selected";
					$selected_parser_set_arr = $tmpSet;
					$SQL = "select Fname, Lname from User where ID='".$tmpSet['User']."'";
    			$user_rd = $PROHITSDB->fetch($SQL);
    			if($user_rd)$the_parser_set_user = $user_rd['Fname'] . " " . $user_rd['Lname']; 
				}
        echo "<option value='" . $tmpSet['ID'] . "'$selected>".$tmpSet['Name']."\n";
				
      }
?>
		</select>	&nbsp;
<?php 	

      $form_value = '';
			if($selected_parser_set_arr){
				$form_value = $selected_parser_set_arr['Parameters'];
				get_parser_form($form_value);
			}
      
		  if($the_parser_set_user) {
        echo "Set by:". $the_parser_set_user."&nbsp; &nbsp;"; 
		    if($USER->Type == 'Admin' or 
			    (isset($selected_parser_set_arr['User']) and 
			    $selected_parser_set_arr['User'] == $USER->ID)){
			    echo "[<a href=\"javascript: modify_filter_set()\" class=button title='modify filter set'>Save the Change</a>]&nbsp; &nbsp;\n";
		    }
      }
?>
		[<a href="javascript:href_show_hand();" onclick="showTip(event,'new_filer_name_div')" class='button' title='create a new filter set'>Save As</a>]
    	<DIV ID='new_filer_name_div' STYLE="position: absolute; 
                          display: none;
                          border: black solid 1px;
                          width: 180px";>
      <table align="center" cellspacing="0" cellpadding="1" border="0" width=100% bgcolor="#e6e6cc">
        <tr bgcolor="#c1c184" height=25><td align=center><b>Type filter set name</b></td></tr>
        <tr><td align=center><input type=text NAME="frm_new_parser_filter_name" size="15" maxlength="15"></td></tr>
        <tr height=35><td align="center">
				<input type=button name='save_parser_filter' VALUE=" Confirm " onclick="javascript: new_filter_set();">&nbsp;&nbsp;
        <input type=button name='hide_div' VALUE=" Cancel " onclick="javascript: hideTip('new_filer_name_div');">
        </td></tr>
      </table>   
    	</DIV> 
			<font color="#FF0000"><?php echo $error_msg;?></font> 
		</td>
   </tr>
	
   <tr>
     <td bgcolor="white" colspan=3 height=30 align=center>
     Parse Results from <b>Search Engines<?php echo (defined("SEQUEST_IP") and SEQUEST_IP)?"/Sequest":""?></b></b>
     <input type=radio name=frm_parser_type value='search'<?php echo ($frm_parser_type=='search')?' checked':'';?> onClick="submitForm(this.form, 'changeParserType', '')"> &nbsp;
     <b>TPP</b>
     <input type=radio name=frm_parser_type value='TPP'<?php echo ($frm_parser_type=='TPP')?' checked':'';?> onClick="submitForm(this.form, 'changeParserType', '')"> &nbsp;
     <b>Search Engines<?php echo (defined("SEQUEST_IP") and SEQUEST_IP)?"/Sequest":""?> and TPP</b>
     <input type=radio name=frm_parser_type value='both'<?php echo ($frm_parser_type=='both')?' checked':'';?> onClick="submitForm(this.form, 'changeParserType', '')">
     </td>
   </tr>
   <tr>
     <td colspan=3 bgcolor=white><font size=-5>&nbsp;</font></td>
   </tr>
 <?php }?>
   <tr>
     <td bgcolor="white" colspan="5"><b>Remove proteins which identifier (tag) starts with</b> 
     <input type=text name=DECOY_prefix value='<?php echo ($selected_parser_set_arr)?$DECOY_prefix:''?>'>(separate by "|" if there are more than one, e.g "rev|DECOY").
     </td>
   </tr>
 
   <tr>
     <td rowspan="6" bgcolor="#e0e0e0" width=10%><b>Mascot</b></td>
     <td bgcolor="white" width=35%><b>Standard scoring&nbsp;<INPUT TYPE="radio" VALUE=1 NAME="_mudpit" <?php echo ($_mudpit==1)?'checked':'';?>>&nbsp;</b></td>
     <td width=55% bgcolor="white"><b>MudPIT scoring&nbsp;<INPUT TYPE="radio" VALUE=0 NAME="_mudpit" <?php echo ($_mudpit==0)?'checked':'';?>></b></td>
     <!--td bgcolor="white" width=35%><b>Standard scoring&nbsp;<INPUT TYPE="radio" VALUE=99999999 NAME="_mudpit" CHECKED>&nbsp;</b></td-->
     <!--td width=55% bgcolor="white"><b>MudPIT scoring&nbsp;<INPUT TYPE="radio" VALUE=0.000000001 NAME="_mudpit"></b></td-->
   </tr> 
   <tr>
     <td bgcolor="white" width=35%><b>Ions score cut-off <:</b></td>
     <td width=55% bgcolor="white"> <input type=text size=3 name=frm_peptide_min_score value='<?php echo ($frm_peptide_min_score == '-1')?'27': $frm_peptide_min_score;?>'></td>
   </tr>
   <tr>
     <td bgcolor="white"><b>Require bold red peptide :</b></td>
     <td bgcolor="white"> <input type=checkbox value=1 name=frm_BoldRed <?php echo ($frm_requireBoldRed)?'checked':'';?>></td>
   </tr>
   <tr>
     <td nowrap bgcolor="white"><b>Save Protein Score &gt; </b></td>
     <td nowrap bgcolor="white">
        <select name="frm_score" size="1">
         <option value='1'<?php echo ($frm_score=='1')?' selected':'';?>>save all hits
        <?php 
         $st = 2; $end = 1000; $increment = 2;
         while($st <= $end){
           $selected = '';
           if($frm_score == $st){
             $selected = ' selected';
           }
           echo "      <option value='$st'$selected>$st\n";
           if($st < 40) {
             $st += $increment;
           }else if($st < 100) {
             $st += $increment;
           }else{
             $st += 50;
           }
         }
        ?>
       </select></td>
   </tr>
   <tr> 
     <td bgcolor="white"><b>Max. number of hits :</b></td>
     <td width=55% bgcolor="white"> <input type=text size=3 name=frm_report value='<?php echo (!$frm_report)?'AUTO': $frm_report;?>'></td>
   </tr>
   <tr> 
     <td bgcolor="white"><b>Significance threshold p <:</b></td>
     <td width=55% bgcolor="white"> <input type=text size=3 name=frm_sigthreshold value='<?php echo $frm_sigthreshold;?>'></td>
   </tr>
   <tr>
     <td colspan=3 bgcolor=white><font size=-5>&nbsp;</font></td>
   </tr>
   <!-- gpm start -->
   <tr>
     <td rowspan="2" bgcolor="#e0e0e0"><b>XTandem</b></td>
     <td bgcolor="white"><b>Ions expect log{e) cut-off > </b></td>
     <td bgcolor="white">
     <select name="frm_gpmeionxpect" size="1">
        <option value='100'<?php echo ($frm_gpmeionxpect=='1')?' selected':'';?>>save all peptide
        <?php 
         $st = -1; $end = -50; $increment = 10;
         while($st >= $end){
           $selected = '';
           if($frm_gpmeionxpect == $st){
             $selected = ' selected';
           }
           echo "      <option value='$st'$selected>$st\n";
           $st -= 1;
         }
        ?>
     </select> 
     <select name="frm_gpmeionxpect_dot">
      <?php 
         $st = 0.0; $end = 0.9;
         while($st < $end){
           $selected = '';
           if($frm_gpmeionxpect_dot == "$st"){
             $selected = ' selected';
           }
           echo "      <option value='$st'$selected>$st\n";
           $st += 0.1;
         }
        ?>
     </select>
     </td>
   </tr>
   <tr>
     <td bgcolor="white"><b>Save Protein expect log(e) < </b></td>
     <td bgcolor="white">
     <select name="frm_gpmexpect" size="1">
        <option value='0'<?php echo ($frm_gpmexpect=='1')?' selected':'';?>>save all hits
        <?php 
         $st = -1; $end = -500; $increment = 10;
         while($st >= $end){
           $selected = '';
           if($frm_gpmexpect == $st){
             $selected = ' selected';
           }
           echo "      <option value='$st'$selected>$st\n";
           if($st > -20) {
             $st -= 1;
           }else if($st > -100) {
             $st -= 10;
           }else{
             $st -= 50;
           }
         }
        ?>
     </select> 
     <select name="frm_gpmexpect_dot">
      <?php 
         $st = 0; $end = 0.9;
         
         while($st < $end){
           $selected = '';
           if($frm_gpmexpect_dot == "$st"){
             $selected = ' selected';
           }
           echo "      <option value='$st'$selected>$st\n";
           $st += 0.1;
         } 
        ?>
     </select>
     </td>
  </tr>
  <!-- gpm end -->
<?php if(defined("SEQUEST_IP") and SEQUEST_IP){?>
  <tr>
    <td colspan=3 bgcolor=white><font size=-5>&nbsp;</font></td>
  </tr>
   <!-- sequest star -->
  <tr>
    <td rowspan="2" bgcolor="#e0e0e0"><b>Sequest</b></td>
    <td align="left">
    <div class=maintext><b>Depth:</b>&nbsp;
    <select name="sequest_rank">
    <?php for($i=1; $i<=12; $i++){?>
    <option value="<?php echo $i?>" <?php echo ($sequest_rank==$i)?"selected":""?> ><?php echo $i?> 
    <?php }?>
    </select>
    </div>
    </td>
    <tr>
     <td colspan=3 bgcolor=white><font size=-5>&nbsp;</font></td>
    </tr>
  </tr>
  <!-- sequest end -->
<?php }?>  
	<tr>
     <td colspan=3 bgcolor=white><font size=-5>&nbsp;</font></td>
  </tr>
	<tr>
     <td bgcolor="#e0e0e0"><b>TPP</b></td>
     <td bgcolor="white"><b>TPP_PARSE_MIN_PROBABILITY = </b></td>
     <td width=55% bgcolor="white"> <input type=text size=3 name=frm_TPP_PARSE_MIN_PROBABILITY value='<?php echo $frm_TPP_PARSE_MIN_PROBABILITY;?>'></td>
   <tr>
<?php if($SWATH_app != 'MSPLIT'){?> 
   <tr> 
     <td bgcolor="#e0e0e0" rowspan=3><b>GeneLeve</b></td>
     <td bgcolor="white"><b>Save gene level hits</b></td>
     <td width=55% bgcolor="white"><input type='checkbox' name='frm_geneLevelHits' value='Y' <?php echo ($frm_geneLevelHits)?'checked':'';?>></td>
   </tr>
   <tr>
     <td bgcolor="white"><b>Peptide PROBABILITY cut-off<</b></td>
     <td width=55% bgcolor="white"> <input type=text size=3 name=frm_pepPROBABILITY value='<?php echo $frm_pepPROBABILITY;?>'>
   </td>
   </tr>
<?php }else{?>
   <tr>
     <td bgcolor="#e0e0e0" rowspan=2><b>GeneLeve</b></td>
     <td bgcolor="white"><b>Peptide PROBABILITY cut-off<</b></td>
     <td width=55% bgcolor="white"> <input type=text size=3 name=frm_pepPROBABILITY value='<?php echo $frm_pepPROBABILITY;?>'>
   </td>
   </tr>
    
<?php }?>   <tr> 
     <td bgcolor="white"><b>FDR cut-off></b></td>
     <td width=55% bgcolor="white"> <input type=text size=3 name=frm_geneLevelFDR value='<?php echo $frm_geneLevelFDR;?>'>
   </td>
   </tr>
     
   </tr>
    <?php if($frm_parser_type and  $has_parser_permit){?>
   <tr>
     <td colspan=3 bgcolor=white align=center>
     <input type="button" value=" Run " onClick='auto_save(this.form);'>
     <input type="reset"></td>
   </tr>
   <?php }?>
  </tr>
</table>
 </DIV>
 </DIV>
<?php 
//--------------------------------------
//different of time between set to 
//process and current time
//if is more than 24 hours
//--------------------------------------
function onProcess($beforeTime, $diff_hr, $status=''){
  if($status and preg_match("/processing:(\d+)$/", $status, $matches)){
    
    $ps_ID = $matches[1];
    $last_line = exec("ps $ps_ID | grep auto_save_shell.php", $output);
    if(!$last_line){
      return 0;
    }else{
      return 1;
    }
  }
  //$beforeTime="2004-01-25 12:10:19";
  $d2 = @Date("Y-m-d H:i:s",@time() - ($diff_hr *3600));
  //echo "$d2=$beforeTime";
  if($d2 < $beforeTime){
    return 1;
  }else{
    return 0;
  }
}
//-------------------------------------------------
function parsFrom_other_value($value){
//return the $value from string $frm_other_value it from 
// the string 
// 'peptide_min_size:10;matched_ion_percentage:0;peptide_min_charge:2;matched_ions_group_size:6;matched_ions_num:4;is_modified_peptide:0;peptide_min_score:32;requireBoldRed:1'
//-------------------------------------------------
  global $frm_other_value;
  $rt = -1;
  if(preg_match("/$value:([0-9.]+)/", $frm_other_value, $matches)){
    //$frm_other_value = preg_replace("/^$value:[0-9]+;|$value:[0-9]+;/", '', $frm_other_value);
    $rt = $matches[1];
  }
  return $rt;
}
//-------------------------------------------------
function addIn_other_value($key, $value){ 
// 'peptide_min_size:10;matched_ion_percentage:0;peptide_min_charge:2;matched_ions_group_size:6;matched_ions_num:4;is_modified_peptide:0;peptide_min_score:32;requireBoldRed:1'
//-------------------------------------------------
  global $frm_other_value;
  if(strstr($frm_other_value, $key) ){
    $frm_other_value = preg_replace("/$key:[0-9]+/", "$key:$value", $frm_other_value);
  }else{
    $frm_other_value .= ";$key:$value";
  }
}
function get_parser_form($value){
	 global 
	  $myaction,
	 	$frm_score,
	  $frm_status,
	  $frm_saveby,
	  $frm_setdate,
	  $frm_other_value,
	  $frm_requireBoldRed,
	  $frm_peptide_min_score,
	  $frm_sigthreshold,
	  $frm_report,
	  $tmp_GPM_Value,
	  $frm_gpmexpect,
	  $frm_gpmexpect_dot,
	  $frm_gpmeionxpect,
	  $frm_gpmeionxpect_dot,
    
    $frm_TPP_PARSE_MIN_PROBABILITY,
    $frm_geneLevelFDR,
    $frm_geneLevelHits,
    $frm_pepPROBABILITY,
    $DECOY_prefix;
    
    
	global $frm_parser_type;
	if(is_array($value)){
		$the_SaveConf_record = $value;
	  $frm_score = $the_SaveConf_record['Mascot_SaveScore'];
	  $frm_status = $the_SaveConf_record['Status'];
	  $frm_saveby = $the_SaveConf_record['SaveBy'];
	  $frm_setdate = $the_SaveConf_record['SetDate'];
	  $frm_other_value = $the_SaveConf_record['Mascot_Other_Value'];
	  $GPM_Value = $the_SaveConf_record['GPM_Value'];
    $TPP_Value = $the_SaveConf_record['Tpp_Value'];
    $DECOY_prefix = $the_SaveConf_record['DECOY_prefix'];
	}else{
		$tmp_arr = explode("\n", $value);
		foreach($tmp_arr as $tmp_str){
		  $tmp_str = trim($tmp_str);
			if(preg_match("/^Parser_type=(.*)/", $tmp_str, $matches)){
				if($myaction == "change_paser_set") $frm_parser_type = $matches[1];
			}else if(preg_match("/^^Mascot_SaveScore=(.*)/", $tmp_str, $matches)){
				$frm_score = $matches[1];
			}else if(preg_match("/^Mascot_Other_Value=(.*)/", $tmp_str, $matches)){
				$frm_other_value = $matches[1];
			}else if(preg_match("/^GPM_Value=(.*)/", $tmp_str, $matches)){
				$GPM_Value = $matches[1];
			}else if(preg_match("/^TPP_Value=(.*)/", trim($tmp_str), $matches)){
				$TPP_Value = $matches[1];
			}else if(preg_match("/^DECOY_prefix=(.*)/", $tmp_str, $matches)){
				$DECOY_prefix = $matches[1];      
			}
		}
	}
  
  $TPP_Value_arr = explode(";", $TPP_Value);
  foreach($TPP_Value_arr as $TPP_Value_val){
    $tmp_arr = explode("=", $TPP_Value_val);
    if(count($tmp_arr) == 2){
      $$tmp_arr[0] = $tmp_arr[1];
    }else{
      $$tmp_arr[0] = '';
    }
  }  
  
  $frm_requireBoldRed = parsFrom_other_value('requireBoldRed');
  $frm_peptide_min_score = parsFrom_other_value('peptide_min_score');
  $frm_sigthreshold = parsFrom_other_value('sigthreshold');
  if($frm_sigthreshold < 0) $frm_sigthreshold = '0.05';
  $frm_report= parsFrom_other_value('report');
  if($frm_report <= 0) $frm_report = 'AUTO';
  
  
  $tmp_arr = preg_split("/,|=/", $GPM_Value);
  $frm_gpmexpect = '';
  $frm_gpmexpect_dot = 0;
  $frm_gpmeionxpect = '';
  $frm_gpmeionxpect_dot = 0;
  if(count($tmp_arr) > 3){
    $frm_gpmexpect = $tmp_arr[1];
    $frm_gpmeionxpect = $tmp_arr[3];
  }
  if(count($tmp_arr) > 7){
    $frm_gpmexpect_dot = $tmp_arr[5];
    $frm_gpmeionxpect_dot = $tmp_arr[7];
  }
}




















/*function add_sequest_field_on_conf($tableName,$managerDB){
  $confTableName = $tableName."SaveConf";
  $result = mysqli_query($managerDB->link, "SHOW COLUMNS FROM $confTableName");
  if(!$result){
    echo 'Could not run query: ' . mysqli_error();
    exit;
  }
  if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
      if($row['Field'] == 'SEQUEST_SaveWell_str') return;
    }
  }
  $SQL = "ALTER TABLE $confTableName ADD SEQUEST_SaveWell_str text";
  if(!$managerDB->execute($SQL)){
    echo "cannot add field SEQUEST_SaveWell_str on DB table $confTableName";
    exit;
  }
  $SQL = "ALTER TABLE $confTableName ADD SEQUEST_Value VARCHAR(200)";
  if(!$managerDB->execute($SQL)){
    echo "cannot add field SEQUEST_Value on DB table $confTableName";
    exit;
  }
}*/
?>
