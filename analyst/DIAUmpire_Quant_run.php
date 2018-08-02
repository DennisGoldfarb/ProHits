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

set_time_limit(2400);
@require_once("../common/HTTP/Request_Prohits.php");

$msg_err = '';
$has_control = 0;
$run_saint = '';

$nControl = 0;
$nCompressBaits = '2';
$nburn = 2000;
$niter = 5000;
$lowMode=0;
$minFold=1; 
$fthres = '0';
$fgroup = '0';
$var = '0';
$normalize = '1';
$umpireQuant_ID = '';
$disabled_sample_ids = '';
$control_arr = array();
$merge_proteinID = '';
$frm_is_collapse = '';
$saint_type = 'saint_control';

$DIAUmpireQuantName = '';
$DIAUmpireQuantDescription = '';

$frm_selected_sample_str = '';
$saint_bait_name_str = '';
$contrl_id_str = '';
$tableName = '';


require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
include("analyst/common_functions.inc.php");
include("analyst/comparison_common_functions.php");
require_once("msManager/is_dir_file.inc.php");
require_once("msManager/autoSearch/auto_search_umpire.inc.php");
require_once("admin_office/update_protein_db/auto_update_protein_add_accession.inc.php");
require("msManager/common_functions.inc.php");
include('msManager/autoBackup/shell_functions.inc.php');
include("msManager/tppTask/tpp_task_shell_fun.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/
 
ini_set("memory_limit","-1");

$DIAUmpire_info = check_Umpire();

if($umpireQuant_ID){
  $umpireQuantResults_folder = STORAGE_FOLDER."Prohits_Data/DIAUmpireQuant_results/task_$umpireQuant_ID/";
}
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);

//echo "\$run_saint=$run_saint<br>";
//echo "\$theaction=$theaction<br>";

if(!$theaction or $theaction == 'runQuant'){
  if($run_saint){
    $id_label_arr = array();
    $group_arr = array();
    if($saint_bait_name_str){
      $id_label_arr = explode(",", $saint_bait_name_str);
      foreach($id_label_arr as $ID_label){
        list($rawID, $label) = explode("|", $ID_label);
        $group_arr[$label][] = $rawID;
      }
    }
    $control_arr = array();
    foreach($request_arr as $key => $value){
      if(strstr($key, 'CHECK_')){
        $tmp_check_id_arr = explode("_", $key);
        array_push($control_arr, $tmp_check_id_arr[1]);
      }
    }
    if(!$has_control) {
      $has_control = count($control_arr);
      if($saint_type != 'saint_no_control' and $nControl){
       $has_control = $nControl;
      }
    }
  }else{
    //don't run SAINT or mapDIA after Umpire-Quant.
  }
   
  // re-order three id strings based on group
  if($run_saint == 'mapDIA'){
    $list_old_arr = explode(",",$frm_selected_list_str);
    $list_new_arr = array();
    $sample_old_arr = explode(",",$frm_selected_sample_str);
    $sample_new_arr = array();
    $name_old_arr = explode(",",$saint_bait_name_str);
    $name_new_arr = array();
    
    $name_old_rawID_arr = array();
    foreach($name_old_arr as $value){
      $value = preg_replace("/\|.+$/", "", $value);
      $name_old_rawID_arr[] = $value;
    }
     
    $group_id_str = '';
    foreach($group_arr as $tmp_arr){
      if($group_id_str) $group_id_str .="|";
      $group_id_str .= implode("|", $tmp_arr);
    }
    $new_sorted_rawID_arr = explode("|", $group_id_str);
    for($m = 0; $m<count($new_sorted_rawID_arr); $m++){
      $new_list_id = $new_sorted_rawID_arr[$m];
      for($n = 0; $n < count($name_old_rawID_arr); $n++){
        if($name_old_rawID_arr[$n] == $new_list_id){
          $list_new_arr[] = $list_old_arr[$n];
          $sample_new_arr[] = $sample_old_arr[$n];
          $name_new_arr[] = $name_old_arr[$n];
          continue;
        }
      } 
    }
    $request_arr['frm_selected_list_str'] = implode(",", $list_new_arr);
    $request_arr['frm_selected_sample_str'] = implode(",", $sample_new_arr);
    $request_arr['saint_bait_name_str'] = implode(",", $name_new_arr);
  }
}
 
if($theaction == 'taskSubmitted'){
  echo "<h3><font face=\"Arial\">$msg</font></h3>";
  echo "<br><center><input type=button onclick=\"opener.window.location.assign('./DIAUmpire_Quant_report.php');window.close();\" value='Close Window'>";
  exit;
}else if($theaction == 'checkStatus'){
  //it is in /msManager/autoSearch/auto_search_umpire.inc.php
  
  DIAUmpire_Quant_status($umpireQuant_ID, $ProcessID, $machine);
  exit;
}else if($theaction == 'runQuant'){
  echo "<pre>";
  echo "1. Save DIA-Umpire Quant task to DB.\n";
  $mapDIA_option = '';
  $saint_option ='';  
  if($run_saint == 'SAINT'){
    $SAINT_info = check_SAINT();
    if($saint_type != 'express'){
      if(isset($SAINT_info['version']) and $SAINT_info['version']){
        $saint_option =  "Version:".$SAINT_info['version'].",";
      }
    }else if(isset($SAINT_info['version_exp']) and $SAINT_info['version_exp']){
      $saint_option =  "Version:".$SAINT_info['version_exp'].",";
    }
    $saint_option =  "Version:".$SAINT_info['version']."|".$SAINT_info['version_exp'].",";    
    //$saint_option .= "saint_type:$saint_type,";
    $saint_option .= "nControl:$nControl,";
    $saint_option .= "fthres:$fthres,";
    $saint_option .= "fgroup:$fgroup,";
    $saint_option .= "var:$var,";
    $saint_option .= "nburn:$nburn,";
    $saint_option .= "niter:$niter,";
    $saint_option .= "lowMode:$lowMode,";
    $saint_option .= "minFold:$minFold,";
    $saint_option .= "normalize:$normalize,";
    $saint_option .= "nCompressBaits:$nCompressBaits,";
    
  }else if($run_saint == 'mapDIA'){
    $mapDIA_info = check_mapDIA();
     
    if($mapDIA_info['error']){
      echo "<font color=red>Error</font>: please check mapDIA. The program doesn't work.";exit;
    }
    $MIN_OBS = '';
    $SIZE = '';
    $CONTRAST = '';
    $LABELS = '';
    
    $i = 1;
    foreach($group_arr as $label=>$rawID_arr){
      $min_box_name = "MIN_OBS$i";
      if($$min_box_name > count($rawID_arr)){
        echo '<font color=red>Error</font>: MIN_OBS cannot be larger than the number of samples in the group';exit;
      }
      
      
      if($EXPERIMENTAL_DESIGN == 'IndependentDesign'){
        if($SIZE) $SIZE .= " ";
        $SIZE .= count($rawID_arr);
      }else{
        if(!$SIZE){
          $SIZE = count($rawID_arr);
        }
      }
      if($MIN_OBS) $MIN_OBS .= " "; 
      $MIN_OBS .= $$min_box_name;
      
      if($LABELS) $LABELS .= " ";
      $LABELS .= $label;
      for($j = 1; $j <= count($group_arr); $j++){
        $boxName = "box".$i."_".$j;
        if($CONTRAST != ''){
          if($j == 1){
            $CONTRAST .= ",";
          }else{
            $CONTRAST .= " ";
          }
        }
        if(isset($$boxName)){
          $CONTRAST .= "1";
        }else{
          $CONTRAST .= "0";
        }
      }
      $i++;
    }
    $mapDIA_option = "### ".$mapDIA_info['version'].","; 
    $mapDIA_option .= "### input file,";
     
    $mapDIA_option .= "LEVEL=$LEVEL,";
    $mapDIA_option .= "LOG2_TRANSFORMATION = $LOG2_TRANSFORMATION,";
    if($FUDGE){
      $mapDIA_option .= "FUDGE=$FUDGE,";
    }
    $mapDIA_option .= "REMOVE_SHARED_PEPTIDE=$REMOVE_SHARED_PEPTIDE,";
    //$mapDIA_option .= "REMOVE_SHARED_PEPTIDE_GENE=$REMOVE_SHARED_PEPTIDE_GENE,";
    if($IMPUTE){ 
      $mapDIA_option .= "IMPUTE=$IMPUTE,";
    }
    $mapDIA_option .= ",### MODULE data through MRF model,";
    $mapDIA_option .= "MODULE =no MODULE,";
    
    $mapDIA_option .= ",### Experimental design,";
    $mapDIA_option .= "EXPERIMENTAL_DESIGN= $EXPERIMENTAL_DESIGN,";
    
    $mapDIA_option .= ",### Normalization,";
    $mapDIA_option .= "NORMALIZATION= $NORMALIZATION,";
    
    $mapDIA_option .= ",### Filter,";
    $mapDIA_option .= "SDF= $SDF,";
    $mapDIA_option .= "MIN_CORREL= $MIN_CORREL,";
    $mapDIA_option .= "MIN_OBS = $MIN_OBS,";
    $mapDIA_option .= "MIN_FRAG_PER_PEP = $MIN_FRAG_PER_PEP,";
    $mapDIA_option .= "MAX_FRAG_PER_PEP = $MAX_FRAG_PER_PEP,";
    $mapDIA_option .= "MIN_PEP_PER_PROT = $MIN_PEP_PER_PROT,";
    
    $mapDIA_option .= ",### Sample information,";
    $mapDIA_option .= "LABELS=$LABELS,";
    $mapDIA_option .= "SIZE=$SIZE,";
    
    $mapDIA_option .= ",### min. max. DE,";
    $mapDIA_option .= "MIN_DE=$MIN_DE,";
    $mapDIA_option .= "MAX_DE=$MAX_DE,";
    
    $mapDIA_option .= ",### Contrast matrix for group comparison,";
    $mapDIA_option .= "CONTRAST=,$CONTRAST,";
    
    $mapDIA_option .= ",### protein_level.txt,";
    $mapDIA_option .= "MAX_PEP_PER_PROT = $MAX_PEP_PER_PROT";
  }
  
  $AccessUserID = $_SESSION['USER']->ID;
  $UmpireQuant_option = '';
  if(isset($DIAUmpire_info['version']) and $DIAUmpire_info['version']){
    $UmpireQuant_option = "Version:". $DIAUmpire_info['version'].";";
  }
  $UmpireQuant_option .= "InternalLibSearch:$Quant_InternalLibSearch;";
  $UmpireQuant_option .= "ExternalLibSearch:false;";
  $UmpireQuant_option .= "PeptideFDR:$Quant_PeptideFDR;";
  $UmpireQuant_option .= "ProteinFDR:$Quant_ProteinFDR;";
  $UmpireQuant_option .= "ProbThreshold:$Quant_ProbThreshold;";
  $UmpireQuant_option .= "FilterWeight:$Quant_FilterWeight;";
  $UmpireQuant_option .= "MinWeight:$Quant_MinWeight;";
  $UmpireQuant_option .= "TopNFrag:$Quant_TopNFrag;";
  $UmpireQuant_option .= "TopNPep:$Quant_TopNPep;";
  $UmpireQuant_option .= "Freq:$Quant_Freq;";
  
  $DIAUmpireQuantName = mysqli_escape_string($PROHITSDB->link, $DIAUmpireQuantName);
  $DIAUmpireQuantDescription = mysqli_escape_string($PROHITSDB->link, $DIAUmpireQuantDescription);
  if(!$run_saint){
    $saint_bait_name_str = '';
  }
  $the_sait_bait_name_str = mysqli_escape_string($PROHITSDB->link, $saint_bait_name_str);
  
  $umpireQuant_ID = '';
  $SQL = "insert into DIAUmpireQuant_log set 
      Name='$DIAUmpireQuantName', 
      Description='$DIAUmpireQuantDescription',
      Machine='$frm_machine',
      SearchEngine='$frm_SearchEngine',
      TaskIDandFileIDs='$frm_selected_list_str',
      UserID='$AccessUserID',
      ProjectID='$AccessProjectID',
      Status='Running',
      Date=now(),";
  if(isset($ParentQuantID) && $ParentQuantID){
    $SQL .= "ParentQuantID=$ParentQuantID,";
  }
  $SQL .= " UserOptions='QUANT=$UmpireQuant_option\nSAINT=$saint_option\nmapDIA=$mapDIA_option\nSAINT_control_id_str=$control_id_str\nSAINT_bait_name_str=$the_sait_bait_name_str\nREMOVE_SHARED_PEPTIDE_GENE=$REMOVE_SHARED_PEPTIDE_GENE\nSAINT_or_mapDIA=$run_saint'";
  $umpireQuant_ID = $PROHITSDB->insert($SQL); ///////////////////////////////////////////////////
  //$umpireQuant_ID = 5;
  if(!$run_saint){
    $saint_bait_name_str = '';
  }
  //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
  
  //$run_saint = "SAINT/mapDIA//"
  //send to background to run
  $storage_ip = STORAGE_IP;
  
  if(STORAGE_IP=='localhost') $storage_ip = $PROHITS_NAME;
  $file = "http://" .$storage_ip . dirname(dirname($_SERVER['PHP_SELF'])) . "/msManager/autoSearch/auto_DIAUmpireQuant_shell.php?umpireQuant_ID=$umpireQuant_ID"."&SID=".session_id();
  $msg = '';
  $handle = fopen($file, "r");
  while (!feof($handle)) {
    $msg .= fgets($handle, 4096);
  }
  fclose($handle);
  echo $msg;
  //----------old code----------
  //it is in /msManager/autoSearch/auto_search_umpire.inc.php
  //$process_info=array('ProcessID'=>'','Status'=>,'msg'=>'');
  //$frm_selected_list_str= TaskID|RawFileID|sampleID, 
  /*
  $tableName = $frm_machine;
  $managerDB = new mysqlDB(MANAGER_DB);
  $msManager_link = $managerDB->link;
  $process_info = runDIAUmpire_Quant($umpireQuant_ID, $frm_machine, $frm_SearchEngine, $frm_selected_list_str, $saint_option, $UmpireQuant_option, $control_id_str, $saint_bait_name_str, $mapDIA_option, $run_saint);
  //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
  
  $SQL = "update DIAUmpireQuant_log set Status = '".$process_info['Status']."', ProcessID = '".$process_info['ProcessID']."' where ID = '$umpireQuant_ID'";
  $PROHITSDB->update($SQL); 
  */
  ///---------------------------
  if(DEBUG_DIAUmpireQuant) exit;
  
  ?>
  <script language="JavaScript" type="text/javascript">
  alert('DIA-Umpire Quant task ('+<?php echo $umpireQuant_ID?>+') has been submitted. Please check the task status from DIA-Umpire Quant report.'); 
  opener.window.location.assign('./DIAUmpire_Quant_report.php');
  window.close();
  </script>
  <?php 
  exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Export hits DIAUmpireQuant</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
function run_DIAUmpireQuant(theform){
  if(isEmptyStr(theform.DIAUmpireQuantName.value) || isEmptyStr(theform.DIAUmpireQuantDescription.value)){
    alert("DIAUmpireQuant Name and Description are required!");
    return false;
  }
  if(parseFloat(theform.Quant_PeptideFDR.value)>=1 || 
     parseFloat(theform.Quant_ProteinFDR.value)>=1 || 
     parseFloat(theform.Quant_ProbThreshold.value)>=1 || 
     parseFloat(theform.Quant_MinWeight.value)>=1 || 
     parseFloat(theform.Quant_Freq.value)>=1 
    ){
    alert("Please check DIA-Umpire Quant parameter instruction.  PeptideFDR, ProteinFDR, ProbThreshold, MinWeight and  Freq should be < 1.");
    return false;
  }
  <?php if($run_saint == 'mapDIA'){?>
  if(theform.EXPERIMENTAL_DESIGN.options[theform.EXPERIMENTAL_DESIGN.selectedIndex].value == 'replicatedesign'){
    if(theform.canbeREP.value == ''){
      alert("The groups you selected is not a replicatedesign!");
      //return false;
    }
  }
  <?php }else if($run_saint == 'SAINT'){?>
  var num = parseInt(theform.has_control.value);
  if(num > 0){
     var num2 = parseInt(theform.nControl.value);
     if(num < num2){ 
       alert("The compressed control number cannot be greater than the control simples you have selected!");
       return false;
     }else if(num2.value < 1){
       alert("Please input the compressed control number!");
       return false;
     }
  }
  <?php }?> 
  
  theform.submit();
}
function open_intro(theType){
  var theFile = '';
  if(theType == 'SAINT'){
    theFile = "../doc/saint-vignette.pdf";
  }else if(theType == 'mapDIA'){
    theFile = "../doc/mapDIA-manual.pdf";
  }
  if(theFile){
    popwin(theFile,900,500);
  }
}
 
//-->
</script>
</head>
<body>
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'];?>" ID="" NAME="run_DIAUmpireQuant_form" METHOD="POST">
<input type="hidden" name="has_control" value="<?php echo $has_control;?>">
<input type="hidden" name="theaction" value="runQuant">
<?php foreach($request_arr as $key => $value){
    if($key ==  'theaction') continue;
?>
<input type="hidden" name="<?php echo $key?>" value="<?php echo $value?>">
<?php }?>

<table border=0 width=95% cellspacing="1" cellpadding=1 align=center>
  <tr>
    <td align=left nowrap><span class=pop_header_text><?php echo $run_saint;?> parameters</span>    <a class="button" onclick="open_intro('<?php echo $run_saint;?>')"><img src=../msManager/images/help2.gif></a>
    </td>
  </tr>  
  <tr>
    <td height='1' align=left><hr size=1>
    <table border=0 width=100% cellspacing="1" cellpadding=3 align=center bgcolor=#5c8ca3>
   <?php 
    
if($run_saint == 'SAINT'){
    $SAINT_info = check_SAINT();
    ?>    
    <table border=0 width=100% cellspacing="1" cellpadding=1 align=center bgcolor=#345ecb>
      <tr>
          <td colspan='4'> 
          <font color="#FFFFFF">
          <b><font size=+1>SAINT express</font></b>(<?php echo $SAINT_info['version_exp'];?>)<!--input type=radio value='express' name=saint_type<?php echo ($saint_type=='express')?" checked":"";?> -->&nbsp; &nbsp; &nbsp; 
          <?php if($has_control){?>
          <b><font size=+1>SAINT</font></b>(<?php echo $SAINT_info['version'];?>)<!--input type=radio value='saint_control' name=saint_type<?php echo ($saint_type=='saint_control')?" checked":"";?> --> &nbsp; &nbsp; &nbsp;
          <?php }else{?>
          <b><font size=+1>SAINT without control</font></b>(<?php echo $SAINT_info['version'];?>)<!--input type=radio value='saint_no_control' name=saint_type<?php echo ($saint_type=='saint_no_control')?" checked":"";?> --><br>
          <?php }
          echo $msg_err;
          ?>
          </font>
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top width=30%><b>Use SAINT with controls</b></td>
          <td colspan=3 width=80%>
          
          <?php 
          
          if($has_control){
            if($has_control > 4) {
              $nControl = 4;
            }else{
              $nControl = $has_control;
            }
          ?>
           
            <font color="#008000">You have selected <?php echo $has_control;?> control sample(s) in previous step.</font><br>
            How many compressed controls:
            <input type=text size="2" maxlength="2" value='<?php echo $nControl;?>' name=nControl>
          <?php 
          }else{
          ?>
          <font color="#008000">You are going to run SAINT without controls</font>.<br>
           
          frequency threshold for preys above which probability is set to 0 in all IPs:
          <input type=text size="3" maxlength="3" value='<?php echo $fthres;?>' name=fthres><br>
          frequency boundary dividing high and low frequency groups:
          <input type=text size="3" maxlength="3" value='<?php echo $fgroup;?>' name=fgroup><br>
          binary [0/1] indicating whether variance of count data distributions should be modeled or not:
          <input type=text size="3" maxlength="3" value='<?php echo $var;?>' name=var>
          
          <?php }?>
          <input type="hidden" name="has_control" value="<?php echo $has_control;?>"> 
          </td>
      </tr>
      <tr bgcolor=white>
          <td align=right valign=top width=30%><b>Compress baits</b></td>
          <td colspan=3> <input type=text size=5 value='<?php echo $nCompressBaits;?>' name=nCompressBaits>
           replicates in each interaction with the highest counts is involved in the computation of the scores
          </td>
      </tr> 
      <tr >
          <td align=right ><b><font color="#FFFFFF">SAINT (<?php echo $SAINT_info['version'];?>) parameters</font></b></td>
          <td colspan=3 bgcolor=white>&nbsp;</td>
           
      </tr>
      <tr bgcolor=white>
          <td align=right ><b>Burn-in period</b></td>
          <td>nburn:<input type=text size=5 value='<?php echo $nburn;?>' name=nburn> </td>
          <td align=right ><b>Iterations</b></td>
          <td>niter:<input type=text size=5 value='<?php echo $niter;?>' name=niter></td>
      </tr>
      <tr bgcolor=white>
          <td align=right ><b>exclude extremely high counts</b></td>
          <td>lowMode:<input type=text size=5 value='<?php echo $lowMode;?>' name=lowMode> </td><br>
          <td align=right ><b>forcing separation  </b></td>
          <td>minFold:<input type=text size=5 value='<?php echo $minFold;?>' name=minFold></td>
      </tr>
      <tr bgcolor=white>
          <td align=right colspan=3><b>divide spectral counts by the total spectral counts of each IP</b></td>
          <td>normalize:<input type=text size=5 value='<?php echo $normalize;?>' name=normalize></td>
      </tr>
    </table>
<?php 
}else if($run_saint == 'mapDIA'){
  $mapDIA_info = check_mapDIA();
//==========================================================
  $LEVEL = "3";
  $LOG2_TRANSFORMATION = "true";
  $REMOVE_SHARED_PEPTIDE = "false";
  $EXPERIMENTAL_DESIGN = 'replicatedesign';
  $NORMALIZATION = "none";
  $SDF = "2";
  $MIN_CORREL ="0.2";
  $MIN_FRAG_PER_PEP = "3";
  $MAX_FRAG_PER_PEP = "5";
  $MIN_PEP_PER_PROT = "1";
  $FUDGE = "";
  $REMOVE_SHARED_PEPTIDE_GENE = "true";
  $IMPUTE = "";
  $MIN_DE = "0.01";
  $MAX_DE = "0.99";
  $MAX_PEP_PER_PROT = "inf";
  
  if(isset($ParentQuantID) && $ParentQuantID){
    $tmp_arr = explode(",",$mapDIA_pa);
    $mapDIA_option_arr = array();
    for($i=0; $i<count($tmp_arr);$i++){
      if($tmp_arr[$i] == 'CONTRAST='){
        //$mapDIA_option_arr['CONTRAST'] = $tmp_arr[$i+1].','.$tmp_arr[$i+2];
        continue;
      }else{
        $tmp_arr2 = explode("=",$tmp_arr[$i]);
        if(count($tmp_arr2) == 2){
          $mapDIA_option_arr[trim($tmp_arr2[0])] = trim($tmp_arr2[1]);
        }
      }
    }
    foreach($mapDIA_option_arr as $key => $value){
      $$key = $value;
    }
  }
//===================================================================================== 
?>  
    <table border=0 width=100% cellspacing="1" cellpadding=3 align=center bgcolor=#345ecb>
      <tr>
          <td colspan='2'> 
          <font color="#FFFFFF">
          <b><font size=+1>mapDIA</font></b> (<?php echo $mapDIA_info['version'];?>)&nbsp; &nbsp; &nbsp; 
          <?php  
          echo $msg_err;
          ?>
          </font>
          </td>
      </tr>
      <tr bgcolor=white>
         <td valign=top align=right  width=20%><b>Input file</b> </td>
         <td  width=80%>LEVEL
            <select name="LEVEL">
              <option value = '1' <?php echo ($LEVEL==1)?'selected':''?>>protein-level
              <option value = '2' <?php echo ($LEVEL==2)?'selected':''?>>peptide-level
              <option value = '3' <?php echo ($LEVEL==3)?'selected':''?>>fragment-level
            </select><br>
            LOG2_TRANSFORMATION 
            <input type="radio" name="LOG2_TRANSFORMATION" value="false" <?php echo ($LOG2_TRANSFORMATION=='false')?'checked':''?>>false
            <input type="radio" name="LOG2_TRANSFORMATION" value="true" <?php echo ($LOG2_TRANSFORMATION=='true')?'checked':''?>>true <br>
            FUDGE <input type="text" name="FUDGE" value="<?php echo $FUDGE?>" size="2"> (should be between 0 and 1) <br>
            REMOVE_SHARED_PEPTIDE(protein) 
            <input type="radio" name="REMOVE_SHARED_PEPTIDE" value="false" <?php echo ($REMOVE_SHARED_PEPTIDE=='false')?'checked':''?>>false
            <input type="radio" name="REMOVE_SHARED_PEPTIDE" value="true" <?php echo ($REMOVE_SHARED_PEPTIDE=='true')?'checked':''?>>true (if peptides
belonging to more than one protein. This option is valid only for peptide-level and fragment-level data)<br> 
            REMOVE_SHARED_PEPTIDE(gene) 
            <input type="radio" name="REMOVE_SHARED_PEPTIDE_GENE" value="false" <?php echo ($REMOVE_SHARED_PEPTIDE_GENE==false)?'checked':''?>>false
            <input type="radio" name="REMOVE_SHARED_PEPTIDE_GENE" value="true" <?php echo ($REMOVE_SHARED_PEPTIDE_GENE==true)?'checked':''?>>true (if peptides belonging to more than one gene)<br> 
            IMPUTE <input type="text" name="IMPUTE" value="<?php echo $IMPUTE?>" size="10"> (group n or row n, where n > 0) <br>
         </td>
       </tr>
      <tr bgcolor=white>
         <td valign=top align=right  width=20%><b>Experimental design</b> </td>
         <td  width=80%> 
            <select name="EXPERIMENTAL_DESIGN">
              <option value = 'replicatedesign' <?php echo ($EXPERIMENTAL_DESIGN=='replicatedesign')?'selected':''?>>replicatedesign
              <option value = 'IndependentDesign' <?php echo ($EXPERIMENTAL_DESIGN=='IndependentDesign')?'selected':''?>>IndependentDesign
            </select><br>
            REP design: For example, if the 
 design is a time course experiment with 3 time points (t1,t2,t3) across 2
biological replicates (A,B), then the conditions are time points and thus the
samples should be organized in the following order (t1-A, t1-B) (t2-A, t2-B)
(t3-A, t3-B)
         </td>
       </tr>
       <tr bgcolor=white>
         <td valign=top align=right><b>Normalization</b> </td>
         <td><input type="text" name="NORMALIZATION" value="<?php echo $NORMALIZATION?>" size="10"> none/TIS/rt 10 2</td>
       </tr>
       <tr bgcolor=white>
         <td valign=top align=right><b>Filter</b> </td>
         <td valign=top>
             SDF <input type="text" name="SDF" value="<?php echo $SDF?>" size="10"> <br>
             MIN_CORREL <input type="text" name="MIN_CORREL" value="<?php echo $MIN_CORREL?>" size="5"> <br>
             MIN_FRAG_PER_PEP <input type="text" name="MIN_FRAG_PER_PEP" value="<?php echo $MIN_FRAG_PER_PEP?>" size="5"> <br>
             MAX_FRAG_PER_PEP <input type="text" name="MAX_FRAG_PER_PEP" value="<?php echo $MAX_FRAG_PER_PEP?>" size="5"> <br>
             MIN_PEP_PER_PROT <input type="text" name="MIN_PEP_PER_PROT" value="<?php echo $MIN_PEP_PER_PROT?>" size="5"> <br>
         </td>
       </tr>
       <tr bgcolor=white>
        <td valign=top align=right><b>Sample information</b> </td>
        <td bgcolor="#808080">
           <table border=0 width=100% cellspacing="1" cellpadding=1 bgcolor="#808080">
            <tr>
              <td width=50><font color="#FFFFFF">Group #</font></td>
            	<td width=150><font color="#FFFFFF">Raw file ID</font></td>
            	<td ><font color="#FFFFFF">LABEL</font></td>
            	<td  width=30><font color="#FFFFFF">MIN_OBS</font></td>
            </tr>
           <?php 
            $groupNum = 0;
            $g_id_num = 0;
            $is_REP = 'Y';
            $mapDIA_group_label_str = '';
            foreach($group_arr as $label=>$rawID_arr){
              $groupNum++;
              $theMIN_OBS = 2;
              if(count($rawID_arr) == 1) $theMIN_OBS = 1;
              $rawID_str = implode("|", $rawID_arr);
              if($g_id_num and ($g_id_num != count($rawID_arr) or $g_id_num < 2)){
                $is_REP = '';
              }else{
                $g_id_num = count($rawID_arr);
              }
               
           ?>
            <tr>
              <td bgcolor="#ffffff"><?php echo "$groupNum";?></td>
            	<td bgcolor="#ffffff"><?php echo $rawID_str;?></td>
              <td bgcolor="#ffffff"><?php echo $label;?></td>
            	<td bgcolor="#ffffff"><input type="text" name="MIN_OBS<?php echo $groupNum;?>" value="<?php echo $theMIN_OBS;?>" size="2"></td>
            </tr>
           <?php 
              
            }?>
          </table>
          <?php 
          
          ?>
          <input type="hidden" name="canbeREP" value="<?php echo $is_REP;?>">
        </td>
       </tr>
       <tr bgcolor=white>
        <td valign=top align=right><b>DEPs</b> </td>
        <td>
           MIN_DE <input type="text" name="MIN_DE" value="<?php echo $MIN_DE;?>" size="5">  
           MAX_DE <input type="text" name="MAX_DE" value="<?php echo $MAX_DE;?>" size="5"> 
        </td>
       </tr>
       <tr bgcolor=white>
        <td valign=top align=right><b>Protein_level</b> </td>
        <td>
           MAX_PEP_PER_PROT <input type="text" name="MAX_PEP_PER_PROT" value="<?php echo $MAX_PEP_PER_PROT;?>" size="5"> Set to 'inf' to switch off this filter. 
        </td>
       </tr>
       <tr bgcolor=white>
        <td valign=top align=right><b>Contrast matrix for <br>group comparison</td>  
        <td>
          <table border=0 cellspacing="0" cellpadding=0>
            
            <?php 
             
            $checked = '';
            for($i = 0; $i <= $groupNum; $i++){
               
              echo "<tr>\n";
              for($j = 0; $j <= $groupNum; $j++){
                if($j < $i){
                  $checked = ' checked';
                }else{
                  $checked = '';
                } 
                if(!$i){
                  if(!$j){
                    echo "<td>&nbsp;</td>\n";
                  }else{
                    echo "<td align=center>$j</td>\n";
                  }
                }else if(!$j){
                  echo "<td align=center>$i</td>\n";
                }else{
                  echo "<td><input type='checkbox' name='box$i".'_'."$j' value=''$checked></td>\n";
                }
              }
              echo "</tr>\n";
            }
            ?>
             
          </table>
        </td>
      </tr>
   </table>

<?php 
}
?>    
    <br><center>
    <input type=button name='RunDIAUmpireQuant' value='Run DIA-Umpire Quant & <?php echo $run_saint;?>' onClick="run_DIAUmpireQuant(this.form)" >
    </center>
    </td>
  </td>
  </tr>
</table>
</FORM>
</body>
</html>
