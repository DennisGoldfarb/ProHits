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

$frm_myaction = '';

$TPP_User = '';
$Set_Date = '';
$frm_setName = '';
$frm_setID = 0;
$set_UserID = '';
$frm_ProjectID = 0;
$msg = '';

$set_arr = array();
$perm_modify = '';
$pars_file = '';
$default = 0;
$tbcolor = '#bebebe';



$para_MS1PPM = 30;
$para_MS2PPM = 40;
$para_SN = 3;
$para_MS2SN = 2;
$para_MinMSIntensity = 10;
$para_MinMSMSIntensity = 0.5;
$para_MinMSMSPeakCount = 10;
$para_MinRTRange = 0.1;
$para_MaxNoPeakCluster =4;
$para_MinNoPeakCluster = 2;
$para_MaxMS2NoPeakCluster = 4;
$para_MinMS2NoPeakCluster = 2;
$para_MaxCurveRTRange = 1;
$para_Resolution = 17000;
$para_RTtol = 0.1;
$para_NoPeakPerMin = 150;
$para_StartCharge = 2;
$para_EndCharge = 4;
$para_MS2StartCharge = 1;
$para_MS2EndCharge = 3;
$para_NoMissedScan = 1;
$para_Denoise = 'true';
$para_EstimateBG = 'false';
$para_RemoveGroupedPeaks = 'true';
$para_DetermineBGByID = 'true';
  
      
      
      
      
      








$check_options['general'] = "/^[RGV]$|^PREC$|^n[IPR]$|p\d|^x\d+$|^I\d+$|^d.+|c.+|^PPM$|^E.+|^l\d+$|^TAA$|^TNA$|^mw$|^MONO$|^AVE$|^e[TSCRAGBMD3EKLPN]$/";
$check_options['iProphet'] = "/[PpRIMSE]/";
$check_options['peptideProphet'] = "/[ifgHmIRFAwxlnPNMGEdptus]/";
$check_options['xpress'] = "/^[bLHMNOPil]$|^m\d+([.]\d+)?$|^n[A-Z]+[,]\d([.]\d+)?$|^F\d+$|^c\d+$|^p\d+$/";
$check_options['asap'] = "/^l[A-Znc]+$|^[SFCBZ]$|^f\d+([.]\d+)?$|^r\d+([.]\d+)?$|^m([A-Z]\d+([.]\d+)?)+$/";
$check_options['refreshParser'] = "/^PREV_AA_LEN=\d+$|^NEXT_AA_LEN=\d+$|^RESTORE_NONEXISTENT_IF_PREFIX=\w+/";


 
$prohits_err_msg = '';
$perm_modify = false;
$perm_delete = false;
$perm_insert = false;

$parameter_file_folder = "../TMP/search_parameters";

include("./ms_permission.inc.php");
require("./common_functions.inc.php");
include ( "./is_dir_file.inc.php");


//------------jp 20170710----------------------------
//$gpm_ip = GPM_IP;
//if(GPM_IP=='localhost') $gpm_ip = $PROHITS_IP;
if($gpm_ip=='localhost') $gpm_ip = $PROHITS_IP;
//--------------------------------------------------
//echo "<pre>";print_r($request_arr);echo "</pre>";


$USER = $_SESSION['USER'];
if($USER->Type != 'Admin'){
  $perm_modify = false;
  $perm_delete = false;
  $perm_insert = false;
}

if($frm_setName){
  $frm_setName = preg_replace("/ /", "_", trim($frm_setName));
  $frm_setName = preg_replace("/[^A-Za-z0-9_]/", "", $frm_setName);
}
if(isset($frm_setName) and trim($frm_setName) and $frm_myaction == 'yes' and $perm_modify){
  $to_file = '';
  foreach($request_arr as $key=>$value){
    if(strpos($key, "para_") === 0){
      $to_file .= "$key:$value\n";
    }
  }
  
  
  
  if(!$prohits_err_msg){
    $frm_setID = search_para_add_modify('MSUmpire', $frm_setID, $frm_setName, $USER->ID, $frm_ProjectID, $to_file);
    if(!$frm_setID){
      $prohits_err_msg = "The name '$frm_setName' has been used. Please use other name";
      $frm_myaction = 'newSet';
    } 
  }
}

if($frm_myaction == 'yes' or !$frm_myaction){
  $frm_myaction = 'modifySet';
}

$set_arr = get_search_parameters('MSUmpire', 0, $pro_access_ID_str);

if(!$frm_setID){
  if($set_arr){
    $frm_setID = $set_arr[0]['ID'];
  }
}
 
if($frm_myaction == 'newSet'){
  $default = 1;
}else if($frm_setID){ 
  $theSet_arr = get_search_parameters('MSUmpire', $frm_setID);
   
  $frm_setName = $theSet_arr['Name'];
  $frm_ProjectID = $theSet_arr['ProjectID'];
  if($theSet_arr){
    $thePara_arr = explode("\n",$theSet_arr['Parameters']);
    
    $TTP_User = get_userName($theSet_arr['User']);
    $Set_Date = $theSet_arr['Date'];
    $set_UserID =  $theSet_arr['User'];
     
    foreach($thePara_arr as $str_tmp){
      $str_tmp = trim($str_tmp);
      if($str_tmp){
        $tmp_arr = explode(':',$str_tmp,2);
        if(count($tmp_arr) == 2) $$tmp_arr[0] = $tmp_arr[1];
         
      }
    }
  } 
}




include("./ms_header_simple.php");
?>
<SCRIPT LANGUAGE=JavaScript>
var pre_condition = '';
function isNewSet(theForm, newSet){
  if(newSet){
    theForm.frm_myaction.value = 'newSet';
  }else{
  theForm.frm_myaction.value = 'modifySet';
  }
  theForm.submit();
}
function checkForm(theForm){
   
  if(theForm.frm_setName.value == ''){
    alert('Please enter the new set name.');
    return false;
  }
  theForm.frm_myaction.value = 'yes';
  theForm.submit();
}
 
function IsNumeric(sText){
  var ValidChars = "0123456789.";
  var IsNumber=true;
  var Char;
  if (sText.length == 0) return false;

  for (i = 0; i < sText.length && IsNumber == true; i++) { 
    Char = sText.charAt(i); 
    if (ValidChars.indexOf(Char) == -1) {
      IsNumber = false;
    }
 }
 return IsNumber;
}
 
</SCRIPT>
<br>
<form name=listform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'> 
<table border="0" cellpadding="1" cellspacing="4" width=98%>
  
 
 
 <tr>
   <td width=><img src='./images/msumpire.gif' border=0>&nbsp;</td>
   <td><b><font color='red' face='helvetica,arial,futura' size='3'>MS-Umpire Parameters</font></b><br>
     MS1 quantitation tool for mass spectrometry-based proteomics data  
   </td>
 </tr>
 <tr>
    <td colspan=2 height=1> <font color="red"><?php echo $prohits_err_msg;?></font></td>
 </tr>
 
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
    <td colspan=2>
       
      <table bgcolor="" width=100% cellspacing="1" cellpadding="1" border="0">
        
       <tr>   
        <TD align=right bgcolor="<?php echo $tbcolor;?>"><font color="#FFFFFF"><b>MS1PPM </b></font><INPUT NAME="para_MS1PPM" VALUE="<?php echo $para_MS1PPM;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MS2PPM </b></font><INPUT NAME="para_MS2PPM" VALUE="<?php echo $para_MS2PPM;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>SN </b></font><INPUT NAME="para_SN" VALUE="<?php echo $para_SN;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MS2SN </b></font><INPUT NAME="para_MS2SN" VALUE="<?php echo $para_MS2SN;?>" SIZE=3 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinMSIntensity </b></font><INPUT NAME="para_MinMSIntensity" VALUE="<?php echo $para_MinMSIntensity;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinMSMSIntensity </b></font><INPUT NAME="para_MinMSMSIntensity" VALUE="<?php echo $para_MinMSMSIntensity;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinMSMSPeakCount </b></font><INPUT NAME="para_MinMSMSPeakCount" VALUE="<?php echo $para_MinMSMSPeakCount;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinRTRange </b></font><INPUT NAME="para_MinRTRange" VALUE="<?php echo $para_MinRTRange;?>" SIZE=3 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MaxNoPeakCluster </b></font><INPUT NAME="para_MaxNoPeakCluster" VALUE="<?php echo $para_MaxNoPeakCluster;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinNoPeakCluster </b></font><INPUT NAME="para_MinNoPeakCluster" VALUE="<?php echo $para_MinNoPeakCluster;?>" SIZE=3 maxlength="4"><b></b>
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MaxMS2NoPeakCluster </b></font><INPUT NAME="para_MaxMS2NoPeakCluster" VALUE="<?php echo $para_MaxMS2NoPeakCluster;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinMS2NoPeakCluster </b></font><INPUT NAME="para_MinMS2NoPeakCluster" VALUE="<?php echo $para_MinMS2NoPeakCluster;?>" SIZE=3 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MaxCurveRTRange </b></font><INPUT NAME="para_MaxCurveRTRange" VALUE="<?php echo $para_MaxCurveRTRange;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>Resolution </b></font><INPUT NAME="para_Resolution" VALUE="<?php echo $para_Resolution;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>RTtol </b></font><INPUT NAME="para_RTtol" VALUE="<?php echo $para_RTtol;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>NoPeakPerMin </b></font><INPUT NAME="para_NoPeakPerMin" VALUE="<?php echo $para_NoPeakPerMin;?>" SIZE=3 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>StartCharge </b></font><INPUT NAME="para_StartCharge" VALUE="<?php echo $para_StartCharge;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>EndCharge </b></font><INPUT NAME="para_EndCharge" VALUE="<?php echo $para_EndCharge;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MS2StartCharge </b></font><INPUT NAME="para_MS2StartCharge" VALUE="<?php echo $para_MS2StartCharge;?>" SIZE=3 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MS2EndCharge </b></font><INPUT NAME="para_MS2EndCharge" VALUE="<?php echo $para_MS2EndCharge;?>" SIZE=3 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>NoMissedScan </b></font><INPUT NAME="para_NoMissedScan" VALUE="<?php echo $para_NoMissedScan;?>" SIZE=3 maxlength="4">
        </td>
        <td align=right bgcolor=<?php echo $tbcolor;?>></td>
         <td align=right bgcolor=<?php echo $tbcolor;?>></td>
        <td align=right bgcolor=<?php echo $tbcolor;?>></td>
      </tr>
      <tr>
        <TD bgcolor=<?php echo $tbcolor;?>>&nbsp;<font color="#FFFFFF"><b>Denoise </b><br>
        <INPUT type=radio NAME="para_Denoise" VALUE="true" <?php echo ($para_Denoise == 'true')?"checked":"";?>>true
        <INPUT type=radio NAME="para_Denoise" VALUE="false" <?php echo ($para_Denoise == 'false')?"checked":"";?>>false</font>
        </td>
        <TD bgcolor=<?php echo $tbcolor;?>>&nbsp;<font color="#FFFFFF"><b>EstimateBG </b> <br>
        <INPUT type=radio NAME="para_EstimateBG" VALUE="true" <?php echo ($para_EstimateBG == 'true')?"checked":"";?>>true
        <INPUT type=radio NAME="para_EstimateBG" VALUE="false" <?php echo ($para_EstimateBG == 'false')?"checked":"";?>>false</font>
        </td>
        <TD bgcolor=<?php echo $tbcolor;?>>&nbsp;<font color="#FFFFFF"><b>RemoveGroupedPeaks </b><br>
        <INPUT type=radio NAME="para_RemoveGroupedPeaks" VALUE="true" <?php echo ($para_RemoveGroupedPeaks == 'true')?"checked":"";?>>true
        <INPUT type=radio NAME="para_RemoveGroupedPeaks" VALUE="false" <?php echo ($para_RemoveGroupedPeaks == 'false')?"checked":"";?>>false</font>
        </td>
        <TD bgcolor=<?php echo $tbcolor;?>>&nbsp;<font color="#FFFFFF"><b>DetermineBGByID </b><br>
        <INPUT type=radio NAME="para_DetermineBGByID" VALUE="true" <?php echo ($para_DetermineBGByID == 'true')?"checked":"";?>>true
        <INPUT type=radio NAME="para_DetermineBGByID" VALUE="false" <?php echo ($para_DetermineBGByID == 'false')?"checked":"";?>>false</font>
        </td>
      </tr>
      
  </table>
  </td>
 </tr>
 
 
 
 
 
 
 
 
 
<TR>
   <td colspan=2><br><b><font color='red' face='helvetica,arial,futura' size='3'>Parameter Set</font></b>
   <?php if($perm_modify){?>&nbsp; &nbsp;
   New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(this.form, true)" <?php echo ($frm_myaction == 'newSet')?'checked':'';?>>
   Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(this.form, false)" <?php echo ($frm_myaction == 'modifySet')?'checked':'';?>>
   <?php 
   }else{
     echo " &nbsp; You have no permission to change the setting.";
   }
   if($TPP_User and $frm_myaction != 'newSet'){
       echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
       Set by: <b>".$TPP_User."</b>&nbsp; &nbsp; Set date:<b>".$Set_Date."</b>\n"; 
   }
   if($msg){
    echo "<br><font color='red'>$msg</font>";
   }
   ?> 
  </td>
</tr>
<tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
<tr>
   <td colspan=2 align=center>
    <?php 
   if($frm_myaction == 'newSet'){
       echo '<b>New set name</b>: <input type=text size=10 name=frm_setName>';
   }else{
     if($set_arr){
       echo "<input type=hidden name=frm_setName value='$frm_setName'>\n";
       echo "<b>Set name</b>: <select name=frm_setID onChange=\"isNewSet(this.form, false)\">\n";
       foreach($set_arr as $tmpSet){
         $selected = ($tmpSet['ID'] == $frm_setID)?" selected":"";
         echo "<option value='" . $tmpSet['ID'] . "'$selected>".$tmpSet['Name']."\n";
       }
       echo "</select>\n";
       
     }else if($perm_insert){
       echo "<font color=\"#FF0000\">Please create new default parameter set.</font>";
     }
   }
   echo " &nbsp; &nbsp; <b>for Project</b>: <select name=frm_ProjectID>\n";
   $pro_access_ID_Names[0] = 'none';
   foreach($pro_access_ID_Names as $key => $value){
    $selected = ($key == $frm_ProjectID)?" selected":"";
    echo "<option value='" . $key . "'$selected>".$value."\n";
   }
   echo "</select>\n"; 
    ?>
   </td>
 </tr>
 
<tr>
  <td colspan=2>
   <br><center>
   <?php 
	 if($perm_modify and $frm_myaction != 'show' and (($USER->Type == 'Admin' or $USER->ID == $set_UserID) or $frm_myaction == 'newSet'	)){
    echo "&nbsp; &nbsp; &nbsp;  <input type=button value='Save' onClick=\"checkForm(this.form)\">\n";
   }
   ?>
   <input type=reset value='Reset' name=reset>
   <input type="button" value="Close" onClick="window.close()">
   </center>
  </td>
</tr>

</table>
</form>
<?php 
include("./ms_footer_simple.php");

?>