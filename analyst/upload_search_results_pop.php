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

set_time_limit(3600*2);
$tr_bgcolor = '#e3e3e3';
$tr_title_bgcolor = 'white';
$Bait_ID = 0;
$Exp_ID = 0;
$divSize = "95%";
$msg = '';
$err_msg = '';
$frm_mascot_file = '';

$frm_GPM_file = '';

$frm_file_type = '';
$theAction = '';
$passed_Band_ID = '';
$exportingParameterStr = '';
$PROHITS_ROOT = '';
$error_msg = '';
$searchedDB = '';
$searchEngine = '';

$isUploaded_Mascot = 0;
$isUploaded_GPM = 0;
$isUploaded_Sequest = 0;
$isUploaded_MSGF = 0;
$isUploaded_TPP = 0;
$isUploaded_OpenMS = 0;

$pepex = 0;
$pepex_dot = 0; 
$proex = 0;
$proex_dot = 0;

$isOwner_Mascot = 0;
$isOwner_GPM = 0;
$isOwner_Sequest = 0;
$isOwner_TPP = 0;
$isOwner_MSGF = 0;
$isOwner_OpenMS = 0;

$isWrongFormat_Mascot = 0;
$isWrongFormat_GPM = 0;
$isWrongFormat_Sequest = 0;
$isWrongFormat_TPP = 0;
$isWrongFormat_MSGF = 0;
$isWrongFormat_OpenMS = 0;

$isRemoved_Mascot = 0;
$isRemoved_GPM = 0;
$isRemoved_Sequest = 0;
$isRemoved_TPP = 0;
$isRemoved_MSGF = 0;
$isRemoved_OpenMS = 0;

$hitsDB = '';
$proteinDB = '';
$requireboldred = '1';
$removeType = '';

$uploaded_dat_mascot = '';
$uploaded_dat_GPM = '';
$uploaded_dat_Sequest = '';
$uploaded_dat_MSGF = '';
$uploaded_dat_OpenMS = '';

$uploaded_log_mascot = '';
$uploaded_log_GPM = '';
$uploaded_log_Sequest = '';
$uploaded_log_MSGF = '';
$uploaded_log_OpenMS = '';

$uploaded_tmp_mascot = '';
$uploaded_tmp_GPM = '';
$uploaded_tmp_Sequest = '';
$uploaded_tmp_MSGF = '';
$uploaded_tmp_OpenMS = '';


$file_Mascot = '';
$file_GPM = '';
$file_tppPep = '';
$file_tppProt = '';
$file_OpenMS_idXML = '';
$file_OpenMS_protQuant = '';
$file_OpenMS_pepQuant = '';

$userID_Mascot = '';
$userID_GPM = '';
$userID_tppPep = '';
$userID_tppProt = '';
$userID_OpenMS = '';

$pepTPPfileName = '';

$_mudpit = 1;

$sequest_rank = 2;
$BP_mode = 'fldTIC';
$mass_unit = 'ppm';

$POST_MAX_SIZE = ini_get('post_max_size');
$UPLOAD_MAX_FILESIZE = ini_get('upload_max_filesize');
$DECOY_prefix = '';

//include("../msManager/ms_permission.inc.php");
require("../common/site_permission.inc.php");
//require("../msManager/classes/Storage_class.php");
//require("../msManager/classes/saveConf_class.php");
include("msManager/classes/xmlParser_class.php");
require_once("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require("analyst/status_fun_inc.php");
include("msManager/autoSave/auto_save_tpp_shell_fun.inc.php");
include("msManager/autoSave/auto_save_mascot_shell_fun.inc.php");
include("msManager/autoSave/auto_save_gpm_shell_fun.inc.php");
include("msManager/autoSave/auto_save_MSGF_shell_fun.inc.php");
include("msManager/autoSave/auto_save_sequest_shell_fun.inc.php");
//include("msManager/autoSave/auto_save_OpenMS_shell_fun.inc.php"); // TODO
require_once("msManager/is_dir_file.inc.php");

ini_set('memory_limit','-1');

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//perl mascot parser folder
$path_to = "../MascotParser/";
$upload_to = get_uploaded_search_results_dir();

$SCRIPT_REFERER_DIR = dirname($_SERVER['SERVER_ADDR'].'/'.$_SERVER['PHP_SELF']);
$logfile = "../logs/Mascot/upload_search_results.log";

$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);
$hitsDB = $HITSDB;

$PROHITS_ROOT = str_replace('/analyst','',dirname($_SERVER['SCRIPT_FILENAME']));

if(!$passed_Band_ID){
  $err_msg = "The file you uploaded has exceeded the server limit post_max_size:$POST_MAX_SIZE or upload_max_filesize $UPLOAD_MAX_FILESIZE.Please contact Prohits administrator to change the setting.";
  echo $err_msg;
  exit;
}

$SQL = "SELECT BaitID, ExpID FROM Band Where ID='$passed_Band_ID'";
$Band_arr = $HITSDB->fetch($SQL);
if($Band_arr){
  $Bait_ID = $Band_arr['BaitID'];
  $Exp_ID = $Band_arr['ExpID'];
}else{
  $err_msg = "No sample in Prohits database, please check band id.";
  exit;
}

if($theAction == 'download' and $download_file){
  $filePath = $upload_to . $download_file;
  if(_is_file($filePath)){
    //header("Content-Type: multipart/mixed");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"".basename($filePath)."\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: "._filesize($filePath));
    readfile("$filePath");
  }
  exit;
}

?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>

<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

<script src="../common/site_ajax.js"></script>
<script language="javascript">
function toggle_upload(thisValue){
  var ttp_obj = document.getElementById('upload_ttp_div');
  var mascot_obj = document.getElementById('upload_mascot_div');
  var GPM_obj = document.getElementById('upload_GPM_div');
  var Sequest_obj = document.getElementById('upload_Sequest_div');
  var MSGF_obj = document.getElementById('upload_MSGF_div');
  var OpenMS_obj = document.getElementById('upload_OpenMS_div');
  var err_msg_obj = document.getElementById('err_msg');
  var msg_obj = document.getElementById('msg');
  err_msg_obj.innerHTML = '';
  msg_obj.innerHTML = '';
  
  if(thisValue=='TPP'){
    if(ttp_obj.style.display == "none"){
      ttp_obj.style.display = "block";
      mascot_obj.style.display= "none";
      GPM_obj.style.display= "none";
      Sequest_obj.style.display = "none";
      MSGF_obj.style.display = "none";
      OpenMS_obj.style.display = "none";
    }
  }
  if(thisValue=='Mascot'){
    if(mascot_obj.style.display == "none"){
      mascot_obj.style.display = "block";
      ttp_obj.style.display = "none";
      GPM_obj.style.display= "none";
      Sequest_obj.style.display = "none";
      MSGF_obj.style.display = "none";
      OpenMS_obj.style.display = "none";
    }
  }
  if(thisValue=='GPM'){
    if(GPM_obj.style.display == "none"){
      GPM_obj.style.display = "block";
      ttp_obj.style.display = "none";
      mascot_obj.style.display= "none";
      Sequest_obj.style.display = "none";
      MSGF_obj.style.display = "none";
      OpenMS_obj.style.display = "none";
    }  
  }
  if(thisValue=='Sequest'){
    if(Sequest_obj.style.display == "none"){
      Sequest_obj.style.display = "block";
      ttp_obj.style.display = "none";
      mascot_obj.style.display= "none";
      GPM_obj.style.display = "none";
      MSGF_obj.style.display = "none";
      OpenMS_obj.style.display = "none";
    }  
  }
  if(thisValue=='MSGF'){
    if(MSGF_obj.style.display == "none"){
      MSGF_obj.style.display = "block";
      ttp_obj.style.display = "none";
      mascot_obj.style.display= "none";
      GPM_obj.style.display= "none";
      Sequest_obj.style.display = "none";
      OpenMS_obj.style.display = "none";
    }  
  }
  if(thisValue=='OpenMS'){
    if(OpenMS_obj.style.display == "none"){
        OpenMS_obj.style.display = "block";
        MSGF_obj.style.display = "none";
        ttp_obj.style.display = "none";
        mascot_obj.style.display= "none";
        GPM_obj.style.display= "none";
        Sequest_obj.style.display = "none";
    }
  }
}
function submitform(searchType){
  var theForm = document.getElementById('uploadForm');
  theForm.theAction.value = 'uploaded';
  theForm.uploadType.value = searchType;
  if(searchType=='Mascot' && isEmptyStr(theForm.frm_mascot_file.value)){
    alert("Please add Mascot file!");
    return false;
  }
  if(searchType=='GPM' && isEmptyStr(theForm.frm_GPM_file.value)){
    alert("Please add GPM file!");
    return false;
  }
  if(searchType=='Sequest' && isEmptyStr(theForm.frm_Sequest_file.value)){
    alert("Please add GPM file!");
    return false;
  }
  if(searchType=='MSGF' && isEmptyStr(theForm.frm_MSGF_file.value)){
    alert("Please add MSGF file!");
    return false;
  }

    if(searchType=='OpenMS' && isEmptyStr(theForm.frm_OpenMS_file.value)){
        alert("Please add OpenMS file!");
        return false;
    }
  
  if(searchType=='TPP' && isEmptyStr(theForm.frm_tppProt_xml.value)){
    alert("Please add protein prophet file!");
    return false;
  }else if(searchType=='TPP' && isEmptyStr(theForm.frm_tppPep_xml.value)){
    if(!confirm("Are you sure that you don't want to add a peptide prophet file?")){
      return false;
    }
  }
  theForm.submit();
}
function removehits(searchType){
  var theForm = document.getElementById('uploadForm');
  if(confirm("Are you sure that you want to delete the uploaded file?")){
  theForm.theAction.value = 'remove';
  theForm.removeType.value = searchType;
  theForm.submit();
  }
}
function download_uploaded_file(theFile){
  var theForm = document.getElementById('uploadForm');
  theForm.theAction.value = 'download';
  theForm.download_file.value = theFile;
  theForm.submit();
}
function processAjaxReturn(rp){
  var ret_html_arr = rp.split("@@**@@");
  if(ret_html_arr.length == 2){
    var div_id = trimString(ret_html_arr[0]);
    document.getElementById(div_id).innerHTML = ret_html_arr[1];
    return;
  }
}
</script>
</head>
<basefont face="arial">
<body >
<center>
<table width=<?php echo $divSize;?>" border=0 cellspacing="0" cellpadding="0">
  <tr>
    <td>
    <span class=pop_header_text>Upload Search Results</span><br>
    <hr width=100% size="1" noshade>
    </td>
  </tr>
</table>

<DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
<?php 
  bait_info($Bait_ID,$tr_bgcolor,$tr_title_bgcolor,'item_report');
  flush();
?>
</DIV>
<br>
<DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
<?php 
  Exp_info($Exp_ID,'Experiment',$tr_bgcolor,$tr_title_bgcolor,'item_report');
  flush();
?>
</DIV>
<br>
<DIV STYLE="display: block; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
<?php 
  band_info($passed_Band_ID,'Band',$tr_bgcolor,$tr_title_bgcolor,'item_report');
?>
</DIV>
<div style='display:block' id='process'><img src='./images/process.gif' border=0></div> 
<?php 
ob_flush();
flush();

if($_SESSION['AUTH']->Insert and $theAction == 'uploaded' and !$err_msg){
  
  $frm_project_ID = $AccessProjectID;
  if($uploadType == "TPP"){
    if(_is_file($_FILES['frm_tppPep_xml']['tmp_name'])){
      $tmp_pepfilename = $_FILES['frm_tppPep_xml']['name'];
      $err_msg = check_file_type($_FILES['frm_tppPep_xml'], 'tppPep');
       
      if($err_msg){
        $isWrongFormat_TPP = 1;
        //echo $err_msg;
      }else{
        $err_msg = save_search_result_file($_FILES['frm_tppPep_xml'], $upload_to.'TPP/', $passed_Band_ID, 'tppPep');
        if(!$err_msg){
          $isUploaded_TPP = 1;
          $isOwner_TPP = 1;
        }
      }
    }
     
    if(!$err_msg and _is_file($_FILES['frm_tppProt_xml']['tmp_name'])){
      $err_msg = check_file_type($_FILES['frm_tppProt_xml'], 'tppProt');
      if($err_msg){
         $isWrongFormat_TPP = 1;
         echo $err_msg;
      }else{
        $err_msg = save_search_result_file($_FILES['frm_tppProt_xml'], $upload_to.'TPP/', $passed_Band_ID, 'tppProt');
        if(!$err_msg){
          $isUploaded_TPP = 1;
          $isOwner_TPP = 1;
          hits_searchEngines('update', $AccessProjectID, $HITSDB, 'TPP_Uploaded');
        }
      }
    }
  }
  if($uploadType == "Mascot"){
    if(_is_file($_FILES['frm_mascot_file']['tmp_name'])){
      // check file formate
      if(substr($_FILES['frm_mascot_file']['name'],-3,3)=='DAT'){
        $_FILES['frm_mascot_file']['name'] = basename($_FILES['frm_mascot_file']['name'], ".DAT").".dat"; 
      }       
      $err_msg = check_file_type($_FILES['frm_mascot_file'],$frm_file_type);
      if($err_msg){
        $isWrongFormat_Mascot = 1;
      }else {
        // create a exporting parameter string
        $exportingParameterStr = create_parameter($sigthreshold,$maxHits,$proteinScore,$_mudpit,$ignoreionsscorebelow,$requireboldred);
        // move uploaded file
        $err_msg = save_search_result_file($_FILES['frm_mascot_file'], $upload_to.'Mascot/', $passed_Band_ID,$frm_file_type, $exportingParameterStr);
       
        if(!$err_msg){
          $isUploaded_Mascot = 1;
          // read tmp file and save into database
          save_mascot_results($upload_to.'Mascot/'.$passed_Band_ID."_".preg_replace('/dat/','tmp',$_FILES['frm_mascot_file']['name']),'', $passed_Band_ID, '', ';;', $isUploaded_Mascot);
          $isOwner_Mascot = 1;
          hits_searchEngines('update', $AccessProjectID, $HITSDB, 'Mascot');
        }
      }
    }
    
  }
  
  if($uploadType == "Sequest"){
    if(_is_file($_FILES['frm_Sequest_file']['tmp_name'])){
      $err_msg = check_file_type($_FILES['frm_Sequest_file'],$frm_file_type);
      if($err_msg){
        $isWrongFormat_Mascot = 1;
      }else{
        $exportingParameterStr = " '$MAX_RANK' '$BP_mode' '$mass_unit'";
        $err_msg = save_search_result_file($_FILES['frm_Sequest_file'], $upload_to.'Sequest/', $passed_Band_ID,$frm_file_type, $exportingParameterStr);
        if(!$err_msg){
          $isUploaded_Sequest = 1;
          $isOwner_Sequest = 1;
          hits_searchEngines('update', $AccessProjectID, $HITSDB, 'Sequest');
        }
      }
    }
  }
  
  if($uploadType == "GPM"){
    if(_is_file($_FILES['frm_GPM_file']['tmp_name'])){
      $err_msg = check_file_type($_FILES['frm_GPM_file'],$frm_file_type);
      if($err_msg){
        $isWrongFormat_GPM = 1;
      }else {
        $exportingParameterStr = " '$proex' '$proex_dot' '$pepex' '$pepex_dot'";
        $err_msg = save_search_result_file($_FILES['frm_GPM_file'], $upload_to.'GPM/', $passed_Band_ID,$frm_file_type, $exportingParameterStr);
        if(!$err_msg){
          $isUploaded_GPM = 1;
          $isOwner_GPM = 1;
          hits_searchEngines('update', $AccessProjectID, $HITSDB, 'GPM');
        }
      }
    }
  }
  
  if($uploadType == "MSGF"){
     
    if(_is_file($_FILES['frm_MSGF_file']['tmp_name'])){
      $err_msg = check_file_type($_FILES['frm_MSGF_file'],$frm_file_type);
      if($err_msg){
        $isWrongFormat_MSGF = 1;
      }else {
         
        $exportingParameterStr = '';
        $err_msg = save_search_result_file($_FILES['frm_MSGF_file'], $upload_to.'MSGF/', $passed_Band_ID,$frm_file_type, $exportingParameterStr);
        if(!$err_msg){
          $isUploaded_MSGF = 1;
          $isOwner_MSGF = 1;
          hits_searchEngines('update', $AccessProjectID, $HITSDB, 'MSGF');
        }
      }
       
    }
  }

    /* OPENMS START */
	if($uploadType == "OpenMS"){
		if(_is_file($_FILES['frm_OpenMS_idXML']['tmp_name'])){
			$tmp_idXML_filename = $_FILES['frm_OpenMS_idXML']['name'];
			$err_msg = check_file_type($_FILES['frm_OpenMS_idXML'], 'OpenMS_idXML');

			if($err_msg){
				$isWrongFormat_OpenMS = 1;
				//echo $err_msg;
			}else{
				$err_msg = save_search_result_file($_FILES['frm_OpenMS_idXML'], $upload_to.'OpenMS/', $passed_Band_ID, 'OpenMS_idXML');
				if(!$err_msg){
					$isUploaded_OpenMS = 1;
					$isOwner_OpenMS = 1;
                    //hits_searchEngines('update', $AccessProjectID, $HITSDB, 'OpenMS_Uploaded');
				}
			}
		}

		if(!$err_msg and _is_file($_FILES['frm_OpenMS_protQuant']['tmp_name'])){
			$err_msg = check_file_type($_FILES['frm_OpenMS_protQuant'], 'OpenMS_protQuant');
			if($err_msg){
							$isWrongFormat_OpenMS = 1;
				echo $err_msg;
			}else{
				$err_msg = save_search_result_file($_FILES['frm_OpenMS_protQuant'], $upload_to.'OpenMS/', $passed_Band_ID, 'OpenMS_protQuant');
				if(!$err_msg){
				    $isUploaded_OpenMS = 1;
				    $isOwner_OpenMS = 1;
				}
			}
		}

        if(!$err_msg and _is_file($_FILES['frm_OpenMS_pepQuant']['tmp_name'])){
            $err_msg = check_file_type($_FILES['frm_OpenMS_pepQuant'], 'OpenMS_pepQuant');
            if($err_msg){
                $isWrongFormat_OpenMS = 1;
                echo $err_msg;
            }else{
                $err_msg = save_search_result_file($_FILES['frm_OpenMS_pepQuant'], $upload_to.'OpenMS/', $passed_Band_ID, 'OpenMS_pepQuant');
                if(!$err_msg){
                    $isUploaded_OpenMS = 1;
                    $isOwner_OpenMS = 1;
                }
            }
        }
	}
	/* OPENMS END */
}

$SQL = "SELECT SearchEngine, UploadedBy,File,Date FROM UploadSearchResults Where BandID='$passed_Band_ID' order by Date";
$uploaded_arr = $hitsDB->fetchAll($SQL);
foreach($uploaded_arr as $uploaded_record){
  if($uploaded_record){
    $searchEngine = $uploaded_record['SearchEngine'];
    if($searchEngine == 'Mascot'){
      $isUploaded_Mascot = 1;
      $file_Mascot = $uploaded_record['File'];
      $date_Mascot = $uploaded_record['Date'];
      $userID_Mascot = $uploaded_record['UploadedBy'];
      if($AccessUserID == $uploaded_record['UploadedBy']){
        $isOwner_Mascot = 1;
      }
    }else if($searchEngine == 'tppPep'){
      $isUploaded_TPP = 1;
      $file_tppPep = $uploaded_record['File'];
      $date_tppPep = $uploaded_record['Date'];
      $userID_tppPep = $uploaded_record['UploadedBy'];
      if($AccessUserID == $uploaded_record['UploadedBy']){
        $isOwner_TPP = 1;
      }
    }else if($searchEngine == 'tppProt'){
      $isUploaded_TPP = 1;
      $file_tppProt = $uploaded_record['File'];
      $date_tppProt = $uploaded_record['Date'];
      $userID_tppProt = $uploaded_record['UploadedBy'];
      if($AccessUserID == $uploaded_record['UploadedBy']){
        $isOwner_TPP = 1;
      }
    }else if($searchEngine == 'GPM'){
      $isUploaded_GPM = 1;
      $file_GPM = $uploaded_record['File'];
      $date_GPM = $uploaded_record['Date'];
      $userID_GPM = $uploaded_record['UploadedBy'];
      if($AccessUserID == $uploaded_record['UploadedBy']){
        $isOwner_GPM = 1;
      }
    }else if($searchEngine == 'SEQUEST'){
      $isUploaded_Sequest = 1;
      $file_SEQUEST = $uploaded_record['File'];
      $date_SEQUEST = $uploaded_record['Date'];
      $userID_SEQUEST = $uploaded_record['UploadedBy'];
      if($AccessUserID == $uploaded_record['UploadedBy']){
        $isOwner_Sequest = 1;
      }
    }else if($searchEngine == 'MSGF'){
      $isUploaded_MSGF = 1;
      $file_MSGF = $uploaded_record['File'];
      $date_MSGF = $uploaded_record['Date'];
      $userID_MSGF = $uploaded_record['UploadedBy'];
      if($AccessUserID == $uploaded_record['UploadedBy']){
        $isOwner_MSGF = 1;
      }
    }else if($searchEngine == 'OpenMS_idXML'){ /* OPENMS START */
	    $isUploaded_OpenMS = 1;
	    $file_OpenMS_idXML = $uploaded_record['File'];
	    $date_OpenMS_idXML = $uploaded_record['Date'];
	    $userID_OpenMS = $uploaded_record['UploadedBy'];
	    if($AccessUserID == $uploaded_record['UploadedBy']){
			    $isOwner_OpenMS = 1;
	    }
    } else if($searchEngine == 'OpenMS_protQuant'){
	    $isUploaded_OpenMS = 1;
	    $file_OpenMS_protQuant = $uploaded_record['File'];
	    $date_OpenMS_protQuant = $uploaded_record['Date'];
	    $userID_OpenMS = $uploaded_record['UploadedBy'];
	    if($AccessUserID == $uploaded_record['UploadedBy']){
		    $isOwner_OpenMS = 1;
	    }
    } else if($searchEngine == 'OpenMS_pepQuant'){
	    $isUploaded_OpenMS = 1;
	    $file_OpenMS_pepQuant = $uploaded_record['File'];
	    $date_OpenMS_pepQuant = $uploaded_record['Date'];
	    $userID_OpenMS = $uploaded_record['UploadedBy'];
	    if($AccessUserID == $uploaded_record['UploadedBy']){
			    $isOwner_OpenMS = 1;
	    }
    } /* OPENMS END */
  }
}

if($theAction == 'remove' ){  
  if($removeType == "Mascot" and $isOwner_Mascot){
    //remove data from hits, peptide and UploadSearchResults table
    $uploaded_log_name = preg_replace('/dat/','log',$file_Mascot);
    $uploaded_tmp_name = preg_replace('/dat/','tmp',$file_Mascot);
    if(_is_file($upload_to.'Mascot/'.$file_Mascot)) unlink($upload_to.'Mascot/'.$file_Mascot);
    if(_is_file($upload_to.'Mascot/'.$uploaded_log_name))unlink($upload_to.'Mascot/'.$uploaded_log_name);
    if(_is_file($upload_to.'Mascot/'.$uploaded_tmp_name))unlink($upload_to.'Mascot/'.$uploaded_tmp_name);
    remove_hits($passed_Band_ID,$removeType,$file_Mascot);
    $msg = "Mascot file has been removed";
    $isUploaded_Mascot = 0;
    $isRemoved_Mascot = 1;
  }
  if($removeType == "GPM" and $isOwner_GPM){
    //remove data from hits, peptide and UploadSearchResults table
    $uploaded_txt_name = preg_replace('/xml/','txt',$file_GPM);
    if(_is_file($upload_to.'GPM/'.$file_GPM)) unlink($upload_to.'GPM/'.$file_GPM);
    if(_is_file($upload_to.'GPM/'.$uploaded_txt_name))unlink($upload_to.'GPM/'.$uploaded_txt_name);
    remove_hits($passed_Band_ID,$removeType,$file_GPM);
    $msg = "GPM file has been removed";
    $isUploaded_GPM = 0;
    $isRemoved_GPM = 1;
  }
  if($removeType == "SEQUEST" and $isOwner_Sequest){  
    //remove data from hits, peptide and UploadSearchResults table
    $uploaded_txt_name = preg_replace('/xml/','txt',$file_SEQUEST);
    if(_is_file($upload_to.'SEQUEST/'.$file_SEQUEST)) unlink($upload_to.'SEQUEST/'.$file_SEQUEST);
    if(_is_file($upload_to.'SEQUEST/'.$uploaded_txt_name))unlink($upload_to.'SEQUEST/'.$uploaded_txt_name);
    remove_hits($passed_Band_ID,$removeType,$file_SEQUEST);
    $msg = "SEQUEST file has been removed";
    $isUploaded_Sequest = 0;
    $isRemoved_Sequest = 1;
  }
  if($removeType == "TPP" and $isOwner_TPP){
    if(_is_file($upload_to.'TPP/'.$file_tppPep)) unlink($upload_to.'TPP/'.$file_tppPep);
    if(_is_file($upload_to.'TPP/'.$file_tppProt)) unlink($upload_to.'TPP/'.$file_tppProt);
    remove_hits($passed_Band_ID,'tppPep',$file_tppPep);
    remove_hits($passed_Band_ID,'tppProt',$file_tppProt);
    $msg = "TPP file has been removed";
    $isUploaded_TPP = 0;
    $isRemoved_TPP = 1;
  }
  if($removeType == "MSGF" and $isOwner_MSGF){
    //remove data from hits, peptide and UploadSearchResults table
    $uploaded_txt_name = preg_replace('/xml/','txt',$file_MSGF);
    if(_is_file($upload_to.'MSGF/'.$file_MSGF)) unlink($upload_to.'MSGF/'.$file_MSGF);
    if(_is_file($upload_to.'MSGF/'.$uploaded_txt_name))unlink($upload_to.'MSGF/'.$uploaded_txt_name);
    remove_hits($passed_Band_ID,$removeType,$file_MSGF);
    $msg = "MSGF file has been removed";
    $isUploaded_MSGF = 0;
    $isRemoved_MSGF = 1;
  }
  // OPENMS START
  if($removeType == "OpenMS" and $isOwner_OpenMS){
		if(_is_file($upload_to.'OpenMS/'.$file_OpenMS_idXML)) unlink($upload_to.'OpenMS/'.$file_OpenMS_idXML);
		if(_is_file($upload_to.'OpenMS/'.$file_OpenMS_protQuant))unlink($upload_to.'OpenMS/'.$file_OpenMS_protQuant);
	    if(_is_file($upload_to.'OpenMS/'.$file_OpenMS_pepQuant))unlink($upload_to.'OpenMS/'.$file_OpenMS_pepQuant);
		// TODO?
	    remove_hits($passed_Band_ID,$removeType,$file_OpenMS_idXML);
		$msg = "OpenMS file has been removed";
		$isUploaded_OpenMS = 0;
		$isRemoved_OpenMS = 1;
  }
  // OPENMS END
}
?>
<script language='javascript'>
document.getElementById('process').style.display = 'none';
</script>
<div class=maintext>
<?php 
if($_SESSION['AUTH']->Insert){
   
?>
  <form id=uploadForm name=uploadForm method=post action=<?php echo $PHP_SELF;?> enctype="multipart/form-data">
  <input type=hidden name=theAction value=''>
  <input type=hidden name=removeType value=''>
  <input type=hidden name=uploadType value=''>
  <input type=hidden name='passed_Band_ID' value='<?php echo $passed_Band_ID;?>'>
  <input type=hidden name='requireboldred' value=1>
  <input type=hidden name='download_file' value=''>
  <br>
  <font face="Arial"  size=3 color="#000000"><b>Upload Search Results File Type:</b></font>
  <input type=radio name='frm_file_type' value='TPP' <?php echo ($isUploaded_TPP)?'disabled':''?> <?php echo ($isWrongFormat_TPP=='1')?"checked":""?> onclick="toggle_upload(this.value)">TPP
  &nbsp;&nbsp; &nbsp;&nbsp;
  <input type=radio name='frm_file_type' value='Mascot' <?php echo ($isUploaded_Mascot)?'disabled':''?> <?php echo ($isWrongFormat_Mascot=='1')?"checked":""?> onclick="toggle_upload(this.value)" >Mascot
  &nbsp;&nbsp; &nbsp;&nbsp;
  <input type=radio name='frm_file_type' value='GPM' <?php echo ($isUploaded_GPM)?'disabled':''?> <?php echo ($isWrongFormat_GPM=='1')?"checked":""?> onclick="toggle_upload(this.value)" >GPM
<?php if(defined("SEQUEST_IP")){?>  
  &nbsp;&nbsp; &nbsp;&nbsp;
  <input type=radio name='frm_file_type' value='Sequest' <?php echo ($isUploaded_Sequest)?'disabled':''?> <?php echo ($isWrongFormat_Sequest=='1')?"checked":""?> onclick="toggle_upload(this.value)" >Sequest
<?php }?>  
 
  &nbsp;&nbsp; &nbsp;&nbsp;
  <input type=radio name='frm_file_type' value='MSGF' <?php echo ($isUploaded_MSGF)?'disabled':''?> <?php echo ($isWrongFormat_MSGF=='1')?"checked":""?> onclick="toggle_upload(this.value)" >MSGF

  <input type=radio name='frm_file_type' value='OpenMS' <?php echo ($isUploaded_OpenMS)?'disabled':''?> <?php echo ($isWrongFormat_OpenMS=='1')?"checked":""?> onclick="toggle_upload(this.value)" >OpenMS

  <table border=0 cellspacing="2" cellpadding="0" width=93%> 
    <tr>
     <td bgcolor="white" colspan="5"><b>Remove proteins witch identifier (tag) starts with</b> 
     <input type=text name=DECOY_prefix value=DECOY>(separate by "|" if there are more than one, e.g "rm|99999").
     </td>
   </tr>
  </table>
  <br><br>
  <font id='msg'color="#008000"><?php echo $msg;?></font>
  <font id='err_msg' color="#FF0000"><?php echo $err_msg;?></font>
  <DIV ID='upload_ttp_div' STYLE="Display:<?php echo ($isWrongFormat_TPP=='1')?"block":"none"?>; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
    <table border=1 cellspacing="2" cellpadding="0" width=100%>
      <tr>
      <td colspan=3 bgcolor="#ffffff"><div class=middle><b>&nbsp;&nbsp;Browse TPP Files</b></div></td>
      </tr>
      <tr>
        <td bgcolor = #e3e3e3 nowrap><b><font face="Arial" size=2pt> TPP ProteinProphet :  </font></b></td>
        <td ><input type=file size=45 name=frm_tppProt_xml></td>
        <td nowrap><font face="Arial" size=2pt color="#0000ff" > select .xml file</font></td>
      </tr>
      <tr>
        <td bgcolor = #e3e3e3 nowrap><b><font face="Arial" size=2pt> TPP PeptideProphet : </font></b></td>
        <td ><input type=file size=45 name=frm_tppPep_xml></td>
        <td align="left" ><font face="Arial" size=2pt color="#0000ff"> select .xml file</font></td>
      </tr>
    <tr>
        <td colspan=3 ><center><div class=maintext>Upload max file size:&nbsp;<font color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;Post max size:&nbsp;<font color='red'><?php echo $POST_MAX_SIZE?></font></div></center></td>
      </tr>
    </table>
    <br>
    <input type=button value='Submit' onClick="submitform('TPP')">
    <input type="button" value='Close' onClick="window.close()";>
    <br>
  </DIV>
  <DIV ID='upload_mascot_div' STYLE="Display:<?php echo ($isWrongFormat_Mascot=='1')?"block":"none"?>; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
    <table border=0 cellspacing="2" cellpadding="0" width=100%>
      <tr>
      <td colspan=3 bgcolor="#ffffff"><div class=middle><b>&nbsp;&nbsp;Browse Mascot Files</b></div></td>
      </tr>
      <tr>
        <td bgcolor= #e3e3e3 nowrap><div class=maintext><b> &nbsp;Mascot File :  </b></div></td>
        <td ><input type=file size=50 name=frm_mascot_file></td>
		<td align="left"><font face="Arial" size=2pt color="#0000ff"> select .dat file</font></td>
      </tr>
    </table>
    <hr width=100% size="1" noshade>
    <table cellspacing=1 cellpadding=1 width=100%>
      <tr>
        <td bgcolor=#ffffff colspan=2 nowrap><div class=middle><b>&nbsp;&nbsp;Filter</b></div></td>
      </tr>
      <tr>
        <td bgcolor=#e3e3e3 nowrap><div class=maintext><b>&nbsp;Ions score cut-off  &lt;: </b><input name="ignoreionsscorebelow" type=text size=16 value='<?php echo ($isWrongFormat_Mascot)?"$ignoreionsscorebelow":'27'?>'></div></td>
        <td bgcolor=#e3e3e3 nowrap><div class=maintext><b>&nbsp;Require bold red peptide :</b><input type="checkbox" name="rbrchkbox" onClick="if (form.rbrchkbox.checked) { form.requireboldred.value = 1; } else { form.requireboldred.value = 0; } return true;" <?php echo ($requireboldred)?"checked":""?>></div></td>
      </tr>
      <tr>
        <td bgcolor=#e3e3e3 nowrap><div class=maintext><b>&nbsp;Save Protein score &gt; </b><input name="proteinScore" type=text size=15 value='<?php echo ($isWrongFormat_Mascot)?"$proteinScore":'save all hits'?>'></div></td>
        <td bgcolor=#e3e3e3 nowrap><div class=maintext><b>&nbsp;Max. number of hits : </b><input name="maxHits" type=text size=5 value='<?php echo ($isWrongFormat_Mascot)?"$maxHits":'AUTO'?>'></div></td>
      </tr>
      <tr>
        <td bgcolor=#e3e3e3 nowrap><div class=maintext>
          &nbsp;<b>Significance threshold p&lt;:</b> <input name="sigthreshold" type=text size=10 value='<?php echo ($isWrongFormat_Mascot)?"$sigthreshold":"0.05"?>'></div>
        </td>        
        <td bgcolor=#e3e3e3 nowrap><div class=maintext>
          <b>Standard scoring</b>&nbsp;<INPUT TYPE="radio" VALUE=1 NAME="_mudpit" <?php echo ($_mudpit)?"checked":""?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>MudPIT scoring<b>&nbsp;<INPUT TYPE="radio" VALUE=0 NAME="_mudpit" <?php echo (!$_mudpit)?"checked":""?>>
        </td>
      </tr>
      <tr>
        <td colspan=2 ><center><div class=maintext>Upload max file size:&nbsp;<font color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;Post max size:&nbsp;<font color='red'><?php echo $POST_MAX_SIZE?></font></div></center></td>
      </tr>
    </table>
    <br>
    <input type=button value='Submit' onClick="submitform('Mascot')">
    <input type=button value='Close' onClick="window.close()";>
    <br>
  </DIV>
  <DIV ID='upload_GPM_div' STYLE="Display:<?php echo ($isWrongFormat_GPM=='1')?"block":"none"?>; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
    <table border=0 cellspacing="2" cellpadding="0" width=100%>
      <tr>
      <td colspan=3 bgcolor="#ffffff"><div class=middle><b>&nbsp;&nbsp;Browse GPM Files</b></div></td>
      </tr>
      <tr>
        <td bgcolor= #e3e3e3 nowrap><div class=maintext><b> &nbsp;GPM File :  </b></div></td>
        <td><input type=file size=50 name=frm_GPM_file></td>
		    <td align="left"><font face="Arial" size=2pt color="#0000ff"> select .xml file</font></td>
      </tr>
    </table>
    <hr width=100% size="1" noshade>
    <table cellspacing=1 cellpadding=1 width=100% border=0>
      <tr>
        <td bgcolor=#ffffff colspan=3 nowrap><div class=middle><b>&nbsp;&nbsp;Filter</b></div></td>
      </tr>
   
      
     <tr>
     <td rowspan="2" bgcolor="#e0e0e0" width=20%><div class=middle><b>GPM</b></div></td>
     <td bgcolor="white"><div class=middle><b>Ions expect log{e) cut-off > </b></div></td>
     <td bgcolor="white">
     <select name="pepex" size="1">
        <option value='100'<?php echo ($pepex=='1')?' selected':'';?>>save all peptide
        <?php 
         $st = -1; $end = -50; $increment = 10;
         while($st >= $end){
           $selected = '';
           if($pepex == $st){
             $selected = ' selected';
           }
           echo "      <option value='$st'$selected>$st\n";
           $st -= 1;
         }
        ?>
     </select> 
     <select name="pepex_dot">
      <?php 
         $st = 0.0; $end = 0.9;
         while($st < $end){
           $selected = '';
           if($pepex_dot == "$st"){
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
     <td bgcolor="white"><div class=middle><b>Save Protein expect log{e) < </b></div></td>
     <td bgcolor="white">
     <select name="proex" size="1">
        <option value='0'<?php echo ($proex=='1')?' selected':'';?>>save all hits
        <?php 
         $st = -1; $end = -500; $increment = 10;
         while($st >= $end){
           $selected = '';
           if($proex == $st){
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
     <select name="proex_dot">
      <?php 
         $st = 0; $end = 0.9;
         
         while($st < $end){
           $selected = '';
           if($proex_dot == "$st"){
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
        <td colspan=3 ><center><div class=maintext>Upload max file size:&nbsp;<font color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;Post max size:&nbsp;<font color='red'><?php echo $POST_MAX_SIZE?></font></div></center></td>
      </tr>
    </table>
    <br>
    <input type=button value='Submit' onClick="submitform('GPM')">
    <input type=button value='Close' onClick="window.close()";>
    <br>
  </DIV>

  <DIV ID='upload_Sequest_div' STYLE="Display:<?php echo ($isWrongFormat_Sequest=='1')?"block":"none"?>; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
  <?php $BP_mode_arr = array("fldMaxBP" => "Apex",  "fldFBP" => "Full", "fldZBP" => "Zoom", "fldTIC" => "MS2");?>
    <table border=0 cellspacing="2" cellpadding="0" width=100%>
      <tr>
      <td colspan=3 bgcolor="#ffffff"><div class=middle><b>&nbsp;&nbsp;Browse Sequest Zipped Files</b></div></td>
      </tr>
      <tr>
        <td bgcolor= #e3e3e3 nowrap><div class=maintext><b> &nbsp;Sequest Zipped File :  </b></div></td>
        <td><input type=file size=50 name=frm_Sequest_file></td>
		    <td align="left"><font face="Arial" size=2pt color="#0000ff"> select .tar.gz file</font></td>
      </tr>
    </table>
    <hr width=100% size="1" noshade>  
    
    <table cellspacing=1 cellpadding=1 width=100% border=0>
      <tr>
        <td bgcolor=#ffffff colspan=3 nowrap><div class=middle><b>&nbsp;Filter</b></div></td>
      </tr>
      <tr>
        <td bgcolor="#e0e0e0" width=20%><div class=middle><b>Sequest</b></div></td>
        <td align="left">
          <div class=maintext>&nbsp;&nbsp;&nbsp;<b>Depth:</b>&nbsp;
          <select name="MAX_RANK"> '$MAX_RANK' '$BP_mode' '$mass_unit'
          <?php for($i=1; $i<=12; $i++){?>
          <option value="<?php echo $i?>" <?php echo ($sequest_rank==$i)?"selected":""?> ><?php echo $i?> 
          <?php }?>
          </select>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan=3 ><center><div class=maintext>Upload max file size:&nbsp;<font color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;Post max size:&nbsp;<font color='red'><?php echo $POST_MAX_SIZE?></font></div></center></td>
      </tr>
    </table>    
    <br>
    <input type=button value='Submit' onClick="submitform('Sequest')">
    <input type="button" value='Close' onClick="window.close()";>
    <br>
  </DIV>  
  <DIV ID='upload_MSGF_div' STYLE="Display:<?php echo ($isWrongFormat_MSGF=='1')?"block":"none"?>; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
   
    <table border=0 cellspacing="2" cellpadding="0" width=100%>
      <tr>
      <td colspan=3 bgcolor="#ffffff"><div class=middle><b>&nbsp;&nbsp;Browse  MS-GF+ Files</b></div></td>
      </tr>
      <tr>
        <td bgcolor= #e3e3e3 nowrap><div class=maintext><b> &nbsp;Protein level CSV/TSV file :  </b></div></td>
        <td colspan=2><input type=file size=50 name=frm_MSGF_file>
		    <br><font face="Arial" size=2pt color="#0000ff">format: Protein,TotalPeptides ,ProteinID,Comment,UniquePeptides,Modified,</font>
        </td>
      </tr>
    </table>
     
    <br>
    <input type=button value='Submit' onClick="submitform('MSGF')">
    <input type="button" value='Close' onClick="window.close()";>
    <br>
  </DIV>


  // OPENMS START
  <DIV ID='upload_openms_div' STYLE="Display:<?php echo ($isWrongFormat_TPP=='1')?"block":"none"?>; border: #a4a4a4 solid 1px; width: <?php echo $divSize;?>">
      <table border=1 cellspacing="2" cellpadding="0" width=100%>
      <tr>
          <td colspan=3 bgcolor="#ffffff"><div class=middle><b>&nbsp;&nbsp;Browse OpenMS Files</b></div></td>
      </tr>
      <tr>
          <td bgcolor = #e3e3e3 nowrap><b><font face="Arial" size=2pt> OpenMS idXML :  </font></b></td>
          <td ><input type=file size=45 name=frm_OpenMS_idXML></td>
          <td nowrap><font face="Arial" size=2pt color="#0000ff" > select .idXML file</font></td>
      </tr>
      <tr>
          <td bgcolor = #e3e3e3 nowrap><b><font face="Arial" size=2pt> OpenMS protein quant : </font></b></td>
          <td ><input type=file size=45 name=frm_OpenMS_protQuant></td>
          <td align="left" ><font face="Arial" size=2pt color="#0000ff"> select .csv file</font></td>
      </tr>
      <tr>
          <td bgcolor = #e3e3e3 nowrap><b><font face="Arial" size=2pt> OpenMS peptide quant : </font></b></td>
          <td ><input type=file size=45 name=frm_OpenMS_pepQuant></td>
          <td align="left" ><font face="Arial" size=2pt color="#0000ff"> select .csv file</font></td>
      </tr>
      <tr>
          <td colspan=3 ><center><div class=maintext>Upload max file size:&nbsp;<font color='red'><?php echo $UPLOAD_MAX_FILESIZE?></font>&nbsp;&nbsp;Post max size:&nbsp;<font color='red'><?php echo $POST_MAX_SIZE?></font></div></center></td>
      </tr>
  </table>
  <br>
  <input type=button value='Submit' onClick="submitform('OpenMS')">
  <input type="button" value='Close' onClick="window.close()";>
  <br>
  </DIV>
  // OPENMS END

  </form>
<?php 
}else{
  echo "<br><font color=#FF0000>You have no permission to upload search results</font><br>";
}

if($isUploaded_TPP or $isUploaded_Mascot or $isUploaded_GPM or $isUploaded_Sequest or $isUploaded_MSGF or $isUploaded_OpenMS){
  echo "
  <table cellspacing=1 cellpadding=3 width=\"$divSize\">
    <tr>
      <th bgcolor=#e3e3e3 nowrap><div class=maintext>Uploaded</div></th>
      <th bgcolor=#e3e3e3 nowrap><div class=maintext>User</div></th>
      <th bgcolor=#e3e3e3 nowrap><div class=maintext>Date</div></th>
      <th bgcolor=#e3e3e3 nowrap><div class=maintext>File</div></th>
    </tr>
    ";
    if($isUploaded_Mascot){
      $theUser = $PROHITSDB->fetch("select Fname, Lname from User where ID='".$userID_Mascot."'");
?>
    <tr>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext>Mascot</div>
      <?php if($isOwner_Mascot){?>
        <div class=button><a  title='Remove uploaded file and hits' href="javascript: removehits('Mascot')" class=button>[delete]</a></div>
      <?php }?>
      </td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $theUser['Fname'] ." " . $theUser['Lname'];?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $date_Mascot;?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $file_Mascot;?>
        <a  title='Download uploaded file' href="javascript: download_uploaded_file('Mascot/<?php echo $file_Mascot;?>')"> <img src=./images/icon_download.gif border=0></a>
      </div>
      </td>
    </tr>
<?php   }
    if($isUploaded_GPM){
      $theUser = $PROHITSDB->fetch("select Fname, Lname from User where ID='".$userID_GPM."'");
?>
    <tr>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext>GPM</div>
      <?php if($isOwner_GPM){?>
        <div class=button><a  title='Remove uploaded file and hits' href="javascript: removehits('GPM')" class=button>[delete]</a></div>
      <?php }?>
      </td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $theUser['Fname'] ." " . $theUser['Lname'];?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $date_GPM;?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $file_GPM;?>
        <a  title='Download uploaded file' href="javascript: download_uploaded_file('GPM/<?php echo $file_GPM;?>')"> <img src=./images/icon_download.gif border=0></a>
      </div>
      </td>
    </tr>
<?php   }
    if($isUploaded_Sequest){
      $theUser = $PROHITSDB->fetch("select Fname, Lname from User where ID='".$userID_SEQUEST."'");
?>
    <tr>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext>SEQUEST</div>
      <?php if($isOwner_Sequest){?>
        <div class=button><a  title='Remove uploaded file and hits' href="javascript: removehits('SEQUEST')" class=button>[delete]</a></div>
      <?php }?>
      </td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $theUser['Fname'] ." " . $theUser['Lname'];?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $date_SEQUEST;?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $file_SEQUEST;?>
        <a  title='Download uploaded file' href="javascript: download_uploaded_file('Sequest/<?php echo $file_SEQUEST;?>')"> <img src=./images/icon_download.gif border=0></a>
      </div>
      </td>
    </tr>
<?php   }
    if($isUploaded_TPP){
      $theUser = $PROHITSDB->fetch("select Fname, Lname from User where ID='".$userID_tppProt."'");
?>
    <tr>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext>TPP</div>
      <?php if($isOwner_TPP){?>
        <div class=button><a  title='Remove uploaded file and hits' href="javascript: removehits('TPP')" class=button>[delete]</a></div>
      <?php }?>
      </td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $theUser['Fname'] ." " . $theUser['Lname'];?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $date_tppProt;?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext>
        <?php echo $file_tppProt;?><a  title='Download uploaded file' href="javascript: download_uploaded_file('TPP/<?php echo $file_tppProt;?>')"> <img src=./images/icon_download.gif border=0></a>
        <br>
        <?php echo $file_tppPep;?><a  title='Download uploaded file' href="javascript: download_uploaded_file('TPP/<?php echo $file_tppPep;?>')"> <img src=./images/icon_download.gif border=0></a>

      </div>
      </td>
    </tr>
  <?php }
   if($isUploaded_MSGF){
      $theUser = $PROHITSDB->fetch("select Fname, Lname from User where ID='".$userID_MSGF."'");
?>
    <tr>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext>MSGF</div>
      <?php if($isOwner_MSGF){?>
        <div class=button><a  title='Remove uploaded file and hits' href="javascript: removehits('MSGF')" class=button>[delete]</a></div>
      <?php }?>
      </td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $theUser['Fname'] ." " . $theUser['Lname'];?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $date_MSGF;?></div></td>
      <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $file_MSGF;?>
        <a  title='Download uploaded file' href="javascript: download_uploaded_file('MSGF/<?php echo $file_MSGF;?>')"> <img src=./images/icon_download.gif border=0></a>
      </div>
      </td>
    </tr>
<?php   }
    // OPENMS START
	if($isUploaded_OpenMS){
		$theUser = $PROHITSDB->fetch("select Fname, Lname from User where ID='".$userID_OpenMS."'");
		?>
      <tr>
          <td bgcolor=#e3e3e3 nowrap><div class=maintext>OpenMS</div>
						<?php if($isOwner_OpenMS){?>
                <div class=button><a  title='Remove uploaded file and hits' href="javascript: removehits('OpenMS')" class=button>[delete]</a></div>
						<?php }?>
          </td>
          <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $theUser['Fname'] ." " . $theUser['Lname'];?></div></td>
          <td bgcolor=#e3e3e3 nowrap><div class=maintext><?php echo $date_OpenMS;?></div></td>
          <td bgcolor=#e3e3e3 nowrap>
              <div class=maintext>
                <?php echo $file_tppProt;?><a  title='Download uploaded file' href="javascript: download_uploaded_file('OpenMS/<?php echo $file_OpenMS_idXML;?>')"> <img src=./images/icon_download.gif border=0></a>
                <br>
                <?php echo $file_tppPep;?><a  title='Download uploaded file' href="javascript: download_uploaded_file('OpenMS/<?php echo $file_OpenMS_protQuant;?>')"> <img src=./images/icon_download.gif border=0></a>
                <br>
                <?php echo $file_tppPep;?><a  title='Download uploaded file' href="javascript: download_uploaded_file('OpenMS/<?php echo $file_OpenMS_pepQuant;?>')"> <img src=./images/icon_download.gif border=0></a>
              </div>
          </td>
      </tr>
	<?php   }
	// OPENMS END
}?>
 </table>
</center>
</body>
</html>
<?php 
function check_file_type($file,$file_type){  
  if(!_is_file($file['tmp_name'])) return "Cannot open file: ".$file['name'];
  $rt = '';
  if($file_type == 'Mascot'){
    if(substr($file['name'],-3,3)=='dat' or substr($file['name'],-3,3)=='DAT'){
      if ($fp = @fopen($file['tmp_name'], "r")) {
        $i=0;
        while ($data = fgets($fp, 4096)) {
          if(preg_match('/Mascot/i', $data, $matches)){
            break;
          }
        }
        $i++;
        if($i>20){
          $rt = "The uploaded $file_type file is not correct format.";
        }
      }
    }else{
      $rt = "The uploaded $file_type file is not correct format.";
    }
  }else if($file_type == 'tppPep' or $file_type == 'tppProt'){
    if ($fp = @fopen($file['tmp_name'], "r")){
      $i=0;
      while ($data = fgets($fp, 4096)) {
        if($file_type == 'tppProt'){
          if(strpos($data, '<program_details analysis="proteinprophet"') === 0){
            break;
          }
        }
        if($file_type == 'tppPep'){
          if(strpos($data, '<peptideprophet_summary version="PeptideProphet') === 0){
            break;
          }else if(strpos($data, '<analysis_summary analysis="interprophet"') === 0){
            break;
          }
        }
        $i++;
        if($i>20){
          $rt = "The uploaded $file_type file is not correct format.";
          break;
        }
      }
    }
  }else if($file_type == 'GPM'){
    if(!preg_match("/.xml$/i", $file['name'])){
      $rt = "The uploaded $file_type file is not correct format.";
    }  
  }else if($file_type == 'Sequest'){
    if(!preg_match("/\.tar\.gz$/i", $file['name']) && !stristr($file['name'], '.zip')){
      $rt = "The uploaded $file_type file is not correct format.";
    }
  }else if($file_type == 'MSGF'){
    if(!preg_match("/.csv|.tsv$/i", $file['name'])){
      $rt = "The uploaded $file_type file is not correct format. The file extension should be csv.";
    }  
  }
  // OPENMS START

  else if($file_type == 'OpenMS_protQuant'  or $file_type == 'OpenMS_pepQuant'){
	  if(!preg_match("/.csv|.tsv$/i", $file['name'])){
		  $rt = "The uploaded $file_type file is not correct format. The file extension should be csv.";
	  }
  }

  else if($file_type == 'OpenMS_idXML'){
	  if ($fp = @fopen($file['tmp_name'], "r")){
		  $i=0;
		  while ($data = fgets($fp, 4096)) {
              if(strpos($data, '<?xml-stylesheet type="text/xsl" href="https://www.openms.de/xml-stylesheet/IdXML.xsl" ?>') === 0){
                  break;
              }
			  $i++;
			  if($i>20){
				  $rt = "The uploaded $file_type file is not correct format.";
				  break;
			  }
		  }
	  }
  }
  // OPENMS END
  return $rt;
}

function save_search_result_file($file_arr, $upload_to, $frm_sample_ID, $Type, $exportingParameterStr=''){
  global $hitsDB;
  global $AccessUserID;
  global $AccessProjectID;
  global $pepTPPfileName;
  
  $error_msg = '';
  $err_msg = '';
  $ok = true;
  if(!get_writable_dir_path($upload_to)){
    "error: cannot write folder $upload_to";exit;
  }
  
  $uploaded_file_name = $file_arr['name'];  
  if($Type == 'Sequest'){
    $uploaded_file_name = preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
  }else{
    $uploaded_file_name = $frm_sample_ID."_".preg_replace ( '/[^-+\w+\.]/', '', $uploaded_file_name );
  }
  $tmpFileFullName = $upload_to . $uploaded_file_name;
  if(move_uploaded_file($file_arr['tmp_name'], $tmpFileFullName)){
    //$error_msg = parse_TPP_file();
    if($Type == 'tppPep'){
      //the function is in auto_save_tpp_shell_fun.inc.php
       
      $ok = parse_peptideProphet($frm_sample_ID, $tmpFileFullName, $uploaded_file_name, 'uploaded');
      
      $pepTPPfileName = $uploaded_file_name;
    }elseif($Type == 'tppProt'){
      //the function is in auto_save_tpp_shell_fun.inc.php
      $ok = parse_proteinProphet($frm_sample_ID, $tmpFileFullName, $pepTPPfileName, $uploaded_file_name, 'uploaded');
    }elseif($Type == 'Mascot'){
      $err_msg = parse_Mascot($tmpFileFullName, $exportingParameterStr);
    }elseif($Type == 'GPM'){
      $tmpFileFullName .= $exportingParameterStr;      
      $ok = save_gpm_results($tmpFileFullName,'', $frm_sample_ID,'', ';;', 1);
    }elseif($Type == 'Sequest'){
      $ok = save_sequest_results($tmpFileFullName,'', $frm_sample_ID,'', ';;', $exportingParameterStr, 1);
       
    }elseif($Type == 'MSGF'){
      $exportingParameterStr = '';
      $ok = save_MSGF_results($tmpFileFullName, $frm_sample_ID, 1);
    }
    // OPENMS START
    // TODO
    elseif($Type == 'OpenMS_idXML'){
      // the functions are in auto_save_openms_shell_fun.inc.php
      $ok = parse_OpenMS_idXML($frm_sample_ID, $tmpFileFullName, $uploaded_file_name, 'uploaded');
      //$pepTPPfileName = $uploaded_file_name;
    }elseif($Type == 'OpenMS_protQuant'){
      $ok = parse_OpenMS_protQuant($frm_sample_ID, $tmpFileFullName, $uploaded_file_name, 'uploaded');
    }elseif($Type == 'OpenMS_pepQuant'){
      $ok = parse_OpenMS_pepQuant($frm_sample_ID, $tmpFileFullName, $uploaded_file_name, 'uploaded');
    }
    // OPENMS END
  }
  if(!$ok){
    $error_msg = "There is error when parsing file (sample ID:$frm_sample_ID) " .$uploaded_file_name. ". read log file for detail.";
    write_Log($error_msg);
  }else if($err_msg){
    $error_msg = $err_msg;
    write_Log($error_msg);
  }else{
    $exportingParameterStr = addslashes($exportingParameterStr);
    $SQL = "insert into UploadSearchResults set
            BandID='$frm_sample_ID',
            File='$uploaded_file_name',
            UploadedBy='$AccessUserID',
            ParameterStr='$exportingParameterStr',
            Date=now(),
            SearchEngine='".(($Type=='Sequest')?'SEQUEST':$Type)."'";
    $hitsDB->insert($SQL);
    $Log = new Log($hitsDB->link);
    $Desc = "uploaded file:$uploaded_file_name";
    $Log->insert($AccessUserID,'UploadSearchResults',$frm_sample_ID,'insert',$Desc,$AccessProjectID);
  }
  return $error_msg;
}

function parse_Mascot($tmpFileFullName, $exportingParameterStr){
  global $PROHITS_ROOT;
  global $SCRIPT_REFERER_DIR;
  
  if (!defined('PERL_58')) {
    define("PERL_58", "perl");
  }
  $err_msg = '';
  $uploaded_log_mascot = "";
  $uploaded_tmp_mascot = "";
  if(!check_mascot_parser()){
    $err_msg = "Mascot parser doesn't work. Please follow the instruction to install Mascot parser from /install/Mascot/.";
  }else{
    $uploaded_log_mascot = preg_replace('/dat$/i','log',$tmpFileFullName);
    $uploaded_tmp_mascot = preg_replace('/dat$/i','tmp',$tmpFileFullName);
    $com = "cd $PROHITS_ROOT/MascotParser/scripts; ".PERL_58." ProhitsMascotParser.pl $tmpFileFullName $exportingParameterStr $uploaded_tmp_mascot $SCRIPT_REFERER_DIR > $uploaded_log_mascot 2>&1";
    
    system($com);
    $error_log = file_get_contents($uploaded_log_mascot);
    if($error_log){
      $err_msg .= $error_log;
    }
  }
   
  return $err_msg;
}

function create_parameter($sigthreshold,$maxHits,$proteinScore,$_mudpit,$ignoreionsscorebelow,$requireboldred){
  if($proteinScore == 'save all hits'){
    $proteinScore = '0';
  }
  if($maxHits == 'AUTO'){
    //$maxHits = '99999999';
    $maxHits = '0';
  }
  $ParameterStr = "$sigthreshold:$maxHits:$proteinScore:$_mudpit:$ignoreionsscorebelow:0:$requireboldred";
  return $ParameterStr;
}

function remove_hits($passed_Band_ID,$SearchEngine,$file){
  global $hitsDB;
  global $USER;
  global $AccessUserID;
  global $AccessProjectID;
  $msg = '';
  $SQL = "DELETE FROM UploadSearchResults where BandID='".$passed_Band_ID."' and SearchEngine='$SearchEngine'";
   
  $hitsDB->update($SQL);

  if($SearchEngine == 'Mascot'){
    $SQL = "select ID from Hits where BandID='$passed_Band_ID' and SearchEngine='MascotUploaded'";
    $new_hits = $hitsDB->fetchAll($SQL);
    for($i = 0; $i < count($new_hits); $i++){
      $hit_ID = $new_hits[$i]['ID'];
      //echo "delete from Hits where ID='$hit_ID'";
      $hitsDB->execute("delete from Hits where ID='$hit_ID'");
      $hitsDB->execute("delete from Peptide where HitID='$hit_ID'");
      $hitsDB->execute("delete from HitNote where HitID='$hit_ID'");
    }
  }elseif($SearchEngine == 'GPM'){
    $SQL = "select ID from Hits where BandID='$passed_Band_ID' and SearchEngine='GPMUploaded'";
    $new_hits = $hitsDB->fetchAll($SQL);
    for($i = 0; $i < count($new_hits); $i++){
      $hit_ID = $new_hits[$i]['ID'];
      //echo "delete from Hits where ID='$hit_ID'";
      $hitsDB->execute("delete from Hits where ID='$hit_ID'");
      $hitsDB->execute("delete from Peptide where HitID='$hit_ID'");
      $hitsDB->execute("delete from HitNote where HitID='$hit_ID'");
    }
  }elseif($SearchEngine == 'MSGF'){
    $SQL = "select ID from Hits where BandID='$passed_Band_ID' and SearchEngine='MSGFUploaded'";
    $new_hits = $hitsDB->fetchAll($SQL);
    for($i = 0; $i < count($new_hits); $i++){
      $hit_ID = $new_hits[$i]['ID'];
      $hitsDB->execute("delete from Hits where ID='$hit_ID'");
      $hitsDB->execute("delete from Peptide where HitID='$hit_ID'");
      $hitsDB->execute("delete from HitNote where HitID='$hit_ID'");
    }
  }elseif($SearchEngine == 'SEQUEST'){
    $SQL = "select ID from Hits where BandID='$passed_Band_ID' and SearchEngine='SEQUESTUploaded'";
    $new_hits = $hitsDB->fetchAll($SQL);
    for($i = 0; $i < count($new_hits); $i++){
      $hit_ID = $new_hits[$i]['ID'];
      //echo "delete from Hits where ID='$hit_ID'";
      $hitsDB->execute("delete from Hits where ID='$hit_ID'");
      $hitsDB->execute("delete from SequestPeptide where HitID='$hit_ID'");
      $hitsDB->execute("delete from HitNote where HitID='$hit_ID'");
    }  
  }elseif($SearchEngine == 'tppPep'){
      $SQL = "DELETE FROM TppPeptide where BandID='". $passed_Band_ID ."' and XmlFile='".$file."'";
      $hitsDB->execute($SQL);
  }else if($SearchEngine == 'tppProt'){
      $SQL = "SELECT ID FROM TppProtein where BandID='". $passed_Band_ID ."' and XmlFile='".$file."'";
      $ID_Arr = $hitsDB->fetchAll($SQL);
      foreach($ID_Arr as $tmpRd){
        $SQL = "DELETE FROM TppPeptideGroup where ProteinID='".$tmpRd['ID']."'";
        $hitsDB->execute($SQL);
      }
      $SQL = "DELETE FROM TppProtein where BandID='". $passed_Band_ID ."' and XmlFile='".$file."'";
      $hitsDB->execute($SQL);
  }
  // OPENMS START
  elseif($SearchEngine == 'OpenMS_idXML') {
	  $SQL = "select ID from Hits where BandID='$passed_Band_ID' and SearchEngine='OpenMSUploaded'";
	  $new_hits = $hitsDB->fetchAll($SQL);
	  for($i = 0; $i < count($new_hits); $i++){
		  $hit_ID = $new_hits[$i]['ID'];
		  $hitsDB->execute("delete from Hits where ID='$hit_ID'");
		  $hitsDB->execute("delete from Peptide where HitID='$hit_ID'");
		  $hitsDB->execute("delete from HitNote where HitID='$hit_ID'");
	  }
  } elseif($SearchEngine == 'OpenMS_protQuant') {
	  $SQL = "update Hits set Intensity_log = NULL where BandID='$passed_Band_ID' and SearchEngine='OpenMSUploaded'";
	  $hitsDB->execute($SQL);
  } elseif($SearchEngine == 'OpenMS_pepQuant') {
	  $SQL = "select ID from Hits where BandID='$passed_Band_ID' and SearchEngine='OpenMSUploaded'";
	  $new_hits = $hitsDB->fetchAll($SQL);
	  for($i = 0; $i < count($new_hits); $i++){
		  $hit_ID = $new_hits[$i]['ID'];
		  $hitsDB->execute("update Peptide set Intensity_log = NULL where ID='$hit_ID'");
	  }
  }
  // OPENMS END

  $Log = new Log($hitsDB->link);
  $Desc = "BandID: $passed_Band_ID, file $file";
  $Log->insert($AccessUserID,'UploadSearchResults',$passed_Band_ID,'delete',$Desc,$AccessProjectID);
}


?>
