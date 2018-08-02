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
error_reporting(E_ALL);
ini_set('display_errors',1);

$frm_myaction = '';

$this_User = '';
$Set_Date = '';
$frm_setName = '';
$frm_setID = 0;
$set_UserID = '';
$frm_ProjectID = 0;
$msg = '';

$frm_Machine = '';
$frm_Description = '';
$SearchEngine = '';
 
$is_default = 0;
$is_SWATH = 0;

$set_arr = array();
$perm_modify = '';
$pars_file = '';
$default = 0;
$tbcolor = '#bebebe';


#Fragment grouping parameter
$dia_RPmax = 25;
$dia_RFmax = 300;
$dia_CorrThreshold=0.2;
$dia_DeltaApex = 0.6;
$dia_RTOverlap=0.3; //v2.0
$dia_AdjustFragIntensity = 'true';
$dia_BoostComplementaryIon = 'true';

$dia_ExportPrecursorPeak = 'false';
$dia_ExportFragmentPeak = 'false';


//#Signal extraction parameters
$para_MS1PPM = 30;
$para_MS2PPM = 40;
$para_SN = 2;
$para_MS2SN = 2;
$para_MinMSIntensity = 5;
$para_MinMSMSIntensity = 1;
$para_MaxCurveRTRange = 1;
$para_Resolution = 17000;
 
$para_StartCharge = 2;
$para_EndCharge = 4;
$para_MS2StartCharge = 2;
$para_MS2EndCharge = 4;
$para_NoMissedScan = 1;

$para_MinFrag = 10;
$para_EstimateBG = 'true';
$para_MinNoPeakCluster = 2;
$para_MaxNoPeakCluster = 4;

//version 2.0
$para_StartRT=0;
$para_EndRT=9999;
$para_MinMZ=200;
$para_MinPrecursorMass=600;
$para_MaxPrecursorMass=5000;
$para_IsoPattern=0.3;
$para_MassDefectFilter='true';
$para_MassDefectOffset=0.1;


$dia_WindowType = 'SWATH';
$dia_WindowSize = 25;


$dia_SWATH_window_setting = '';

$dia_SWATH_window_setting_default='
399.5 408.2
407.2 415.8
414.8.422.7
421.7 429.7
428.7 437.3
436.3 444.8
443.8 451.7
450.7 458.7
457.7 466.7
465.7 473.4
472.4 478.3
477.3 485.4
484.4 491.2
490.2 497.7
496.7 504.3
503.3 511.2
510.2 518.2
517.2 525.3
524.3 533.3
532.3 540.3
539.3 546.8
545.8 554.5
553.5 561.8
560.8 568.3
567.3 575.7
574.7 582.3
581.3 588.8
587.8 595.8
594.8 601.8
600.8 608.9
607.9 616.9
615.9 624.8
623.8 632.2
631.2 640.8
639.8 647.9
646.9 654.8
653.8 661.5
660.5 670.3
669.3 678.8
677.8 687.8
686.8 696.9
695.9 706.9
705.9 715.9
714.9 726.2
725.2 737.4
736.4 746.6
745.6 757.5
756.5 767.9
766.9 779.5
778.5 792.9
791.9 807
806 820
819 834.2
833.2 849.4
848.4 866
865 884.4
883.4 899.9
898.9 919
918 942.1
941.1 971.6
970.6 1006
1005 1053
1052 1110.6
1109.6 1200.5';

$prohits_err_msg = '';
$perm_modify = false;
$perm_delete = false;
$perm_insert = false;

$parameter_file_folder = "../TMP/search_parameters";
 
include("./ms_permission.inc.php");
require("./common_functions.inc.php");
include ( "./is_dir_file.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

//echo "<pre>";print_r($request_arr);echo "</pre>";


$USER = $_SESSION['USER'];
if($USER->Type != 'Admin'){
  $perm_modify = false;
  $perm_delete = false;
  $perm_insert = false;
}else{
  $perm_modify = true;
  $perm_delete = true;
  $perm_insert = true;
}


if($frm_setName){
  $frm_setName = preg_replace("/ /", "_", trim($frm_setName));
  $frm_setName = preg_replace("/[^A-Za-z0-9_]/", "", $frm_setName);
}
if(isset($frm_setName) and trim($frm_setName) and $frm_myaction == 'yes' and $perm_modify){
  $to_file = '';
  foreach($request_arr as $key=>$value){
    if(strpos($key, "para_") === 0 or strpos($key, "dia_") === 0){
      $to_file .= "$key:$value;";
    }
  }  
  if(!$prohits_err_msg){
    //$frm_setID = search_para_add_modify('DIAUmpire', $frm_setID, $frm_setName, $USER->ID, $frm_ProjectID, $to_file);
    $frm_setID = search_para_add_modify('DIAUmpire', $frm_setID, $frm_setName, $USER->ID, $to_file, $is_SWATH, $is_default, $frm_Machine, $frm_Description, $SearchEngine);
      
    if(!$frm_setID){
      $prohits_err_msg = "The name '$frm_setName' has been used. Please use other name";
      $frm_myaction = 'newSet';
    } 
  }
}

if($frm_myaction == 'yes' or !$frm_myaction){
  $frm_myaction = 'modifySet';
}

$set_arr = get_search_parameters('DIAUmpire', 0);
$set_arr = put_default_first($set_arr);


if(!$frm_setID){
  if($set_arr){
    $frm_setID = $set_arr[0]['ID'];
  }
}
 
if($frm_myaction == 'newSet'){
  $default = 1;
  $dia_SWATH_window_setting = $dia_SWATH_window_setting_default;
}else if($frm_setID){ 
  $theSet_arr = get_search_parameters('DIAUmpire', $frm_setID);
   
  $frm_setName = $theSet_arr['Name'];
   
  if($theSet_arr){
    $thePara_arr = explode(";",$theSet_arr['Parameters']);
    
    $this_User = get_userName($theSet_arr['User']);
    $Set_Date = $theSet_arr['Date'];
    $set_UserID =  $theSet_arr['User'];
    $frm_Description = $theSet_arr['Description']; 
    $is_default = $theSet_arr['Default'];

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
<form name=listform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'> 
<table border="0" cellpadding="0" cellspacing="0" width=100% bgcolor=#669999>
<tr>
<td align=center>
 
 <div class=divBoxPop>
 <table border="0" cellpadding="0" cellspacing="2" width=98% bgcolor=white>
 <tr>
   <td width=><img src='./images/diaumpire.gif' border=0>&nbsp;</td>
   <td><span class="pop_header_text">DIA-Umpire Parameters</span>
   <a onClick="newpopwin('../doc/DIA_Umpire_Manual.pdf',900,500)" class=button><img src='./images/help2.gif' border=0></a>
   <br>
     Untargeted peptide and protein identification and quantitation using DIA data, that also incorporates a targeted extraction approach to reduce missing quantitation. 
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
        <TD bgcolor="<?php echo $tbcolor;?>" colspan=2><font color="green">Fragment grouping parameter</td>
      </tr>
      <tr>   
        <TD align=right bgcolor="<?php echo $tbcolor;?>"><font color="#FFFFFF"><b>RPmax</b></font><INPUT NAME="dia_RPmax" VALUE="<?php echo $dia_RPmax;?>" SIZE=4 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>RFmax</b></font><INPUT NAME="dia_RFmax" VALUE="<?php echo $dia_RFmax;?>" SIZE=4 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>CorrThreshold </b></font><INPUT NAME="dia_CorrThreshold" VALUE="<?php echo $dia_CorrThreshold;?>" SIZE=4 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>DeltaApex </b></font><INPUT NAME="dia_DeltaApex" VALUE="<?php echo $dia_DeltaApex;?>" SIZE=4 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>RTOverlap </b></font><INPUT NAME="dia_RTOverlap" VALUE="<?php echo $dia_RTOverlap;?>" SIZE=4 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>>&nbsp;</td>
      </tr>
      <tr>   
        <TD bgcolor="<?php echo $tbcolor;?>" colspan=2><font color="green">Signal extraction parameters</td>
      </tr>
      
      <tr>
        <TD align=right bgcolor="<?php echo $tbcolor;?>"><font color="#FFFFFF"><b>MS1PPM </b></font><INPUT NAME="para_MS1PPM" VALUE="<?php echo $para_MS1PPM;?>" SIZE=4 maxlength="4">
        </td>
      
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MS2PPM </b></font><INPUT NAME="para_MS2PPM" VALUE="<?php echo $para_MS2PPM;?>" SIZE=4 maxlength="4">
        </td>
      </tr>
      <tr>
      
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>SN </b></font><INPUT NAME="para_SN" VALUE="<?php echo $para_SN;?>" SIZE=4 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MS2SN </b></font><INPUT NAME="para_MS2SN" VALUE="<?php echo $para_MS2SN;?>" SIZE=4 maxlength="4">
        </td>
      </tr>
      <tr>
      <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinMSIntensity </b></font><INPUT NAME="para_MinMSIntensity" VALUE="<?php echo $para_MinMSIntensity;?>" SIZE=4 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinMSMSIntensity </b></font><INPUT NAME="para_MinMSMSIntensity" VALUE="<?php echo $para_MinMSMSIntensity;?>" SIZE=4 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MaxCurveRTRange </b></font><INPUT NAME="para_MaxCurveRTRange" VALUE="<?php echo $para_MaxCurveRTRange;?>" SIZE=4 maxlength="5">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>Resolution </b></font><INPUT NAME="para_Resolution" VALUE="<?php echo $para_Resolution;?>" SIZE=4 maxlength="6">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>StartCharge </b></font><INPUT NAME="para_StartCharge" VALUE="<?php echo $para_StartCharge;?>" SIZE=4 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>EndCharge </b></font><INPUT NAME="para_EndCharge" VALUE="<?php echo $para_EndCharge;?>" SIZE=4 maxlength="4">
        </td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MS2StartCharge </b></font><INPUT NAME="para_MS2StartCharge" VALUE="<?php echo $para_MS2StartCharge;?>" SIZE=4 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MS2EndCharge </b></font><INPUT NAME="para_MS2EndCharge" VALUE="<?php echo $para_MS2EndCharge;?>" SIZE=4 maxlength="5">
        </td>
      </tr>
      <tr>
      <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>NoMissedScan </b></font><INPUT NAME="para_NoMissedScan" VALUE="<?php echo $para_NoMissedScan;?>" SIZE=4 maxlength="5">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinFrag </b></font><INPUT NAME="para_MinFrag" VALUE="<?php echo $para_MinFrag;?>" SIZE=4 maxlength="4">
        </td>
      </tr>
      <tr>
       <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>EstimateBG </b></font>
        Yes<INPUT type=radio NAME="para_EstimateBG" VALUE="true"<?php echo ($para_EstimateBG == 'true')?" checked":"";?>> 
        No<INPUT type=radio NAME="para_EstimateBG" VALUE="false"<?php echo ($para_EstimateBG == 'false')?" checked":"";?>>
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>>&nbsp;</td>
      </tr>
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinNoPeakCluster</b></font><INPUT NAME="para_MinNoPeakCluster" VALUE="<?php echo $para_MinNoPeakCluster;?>" SIZE=4 maxlength="4">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MaxNoPeakCluster</b></font><INPUT NAME="para_MaxNoPeakCluster" VALUE="<?php echo $para_MaxNoPeakCluster;?>" SIZE=4 maxlength="2">
        </td>
      </tr> 
      <tr> 
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>StartRT </b></font><INPUT NAME="para_StartRT" VALUE="<?php echo $para_StartRT;?>" SIZE=4 maxlength="5">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>EndRT </b></font><INPUT NAME="para_EndRT" VALUE="<?php echo $para_EndRT;?>" SIZE=4 maxlength="5">
        </td>
      </tr>
      
      
      <tr>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinMZ </b></font><INPUT NAME="para_MinMZ" VALUE="<?php echo $para_MinMZ;?>" SIZE=3 maxlength="4">
        </td>
         
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MinPrecursorMass</b></font><INPUT NAME="para_MinPrecursorMass" VALUE="<?php echo $para_MinPrecursorMass;?>" SIZE=4 maxlength="5">
        </td>
      </tr>
      <tr>  
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MaxPrecursorMass</b></font><INPUT NAME="para_MaxPrecursorMass" VALUE="<?php echo $para_MaxPrecursorMass;?>" SIZE=4 maxlength="5">
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>IsoPattern</b></font><INPUT NAME="para_IsoPattern" VALUE="<?php echo $para_IsoPattern;?>" SIZE=4 maxlength="5">
        </td>
      </tr>  
        
      <tr>
	      <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MassDefectFilter</b></font>
        Yes<INPUT type=radio NAME="para_MassDefectFilter" VALUE="true"<?php echo ($para_MassDefectFilter == 'true')?" checked":"";?>> 
        No<INPUT type=radio NAME="para_MassDefectFilter" VALUE="false"<?php echo ($para_MassDefectFilter == 'false')?" checked":"";?>>
        </td>
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>MassDefectOffset</b></font><INPUT NAME="para_MassDefectOffset" VALUE="<?php echo $para_MassDefectOffset;?>" SIZE=4 maxlength="5">
        </td>
      </tr>
      <tr>   
        <TD bgcolor="<?php echo $tbcolor;?>" colspan=2><font color="green">Isolation window setting</td>
      </tr>
      <tr>
        <td align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>WindowType </b></font>
        <select name="dia_WindowType">
        <option value='SWATH'<?php echo ($dia_WindowType=='SWATH')?" selected":"";?>>SWATH(fixed)
        <option value='V_SWATH'<?php echo ($dia_WindowType=='V_SWATH')?" selected":"";?>>V_SWATH
        <option value='MSX'<?php echo ($dia_WindowType=='MSX')?" selected":"";?>>MSX
        <option value='MSE'<?php echo ($dia_WindowType=='MSE')?" selected":"";?>>MSE
        <option value='MSE'<?php echo ($dia_WindowType=='pSMART')?" selected":"";?>>pSMART
        </select>
        </td>   
        <TD align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b>Fixed window size </b></font><INPUT NAME="dia_WindowSize" VALUE="<?php echo $dia_WindowSize;?>" SIZE=4 maxlength="5">
        </td>
      </tr>
      <tr>
        <td valign="top" align=right bgcolor=<?php echo $tbcolor;?>><font color="#FFFFFF"><b> SWATH window setting </b></font>
        <br>(Start m/z, end m/z<br>separated by space)<br>
        V_SWATH <br>
        (variable SWATH window)       
        </td>
        <td bgcolor=<?php echo $tbcolor;?>>
         ==window setting begin<br>
         <textarea cols="20" rows="6" name="dia_SWATH_window_setting"><?php echo $dia_SWATH_window_setting;?></textarea><br>
        ==window setting end
        </td>
      </tr>
      
  </table>
  </td>
 </tr>
 </table>
 </div>
 <div class=divBoxPop>
 <table border="0" cellpadding="0" cellspacing="2" width=98% bgcolor=white>
</td>
<TR>
   <td colspan=2><b><font color='red' face='helvetica,arial,futura' size='3'>Parameter Set</font></b>
   <?php if($perm_modify){?>&nbsp; &nbsp;
   New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(this.form, true)" <?php echo ($frm_myaction == 'newSet')?'checked':'';?>>
   Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(this.form, false)" <?php echo ($frm_myaction == 'modifySet')?'checked':'';?>>
   <?php 
   }else{
     
   }
   
   if($this_User and $frm_myaction != 'newSet'){
       echo "<br> 
       Set by: <b>".$this_User."</b>&nbsp; &nbsp; Set date:<b>".$Set_Date."</b>\n"; 
   }
   echo "<br><font color='#008000'>Only Prohits administrator can change the setting</font>.";
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
   <td width=20%><b>Set Name</b>: </td>
   <td>
    <?php 
   if($frm_myaction == 'newSet'){
        echo '<input type=text size=20 name=frm_setName>';
   }else{
     if($set_arr){
       echo "<input type=hidden name=frm_setName value='$frm_setName'>\n";
       echo "<select id=frm_setID name=frm_setID onChange=\"isNewSet(this.form, false)\">\n";
       echo "<option value=''>\n";
       foreach($set_arr as $tmpSet){
          $style_str = '';
         if($tmpSet['Default']){
          $style_str = " style='background-color: yellow;' ";
          }
         $selected = ($tmpSet['ID'] == $frm_setID)?" selected":"";
         echo "<option value='" . $tmpSet['ID'] . "'$selected$style_str>".$tmpSet['Name']."\n";
       }
       echo "</select>\n";
       
     }else if($perm_insert){
       echo "<font color=\"#FF0000\">Please create new parameter set.</font>";
     }
   }
?>
  
   </td>
</tr>
<tr>
   <td valign=top><b>Description: </b></td> 
   <td>
   <textarea cols='50' rows='2' name='frm_Description'><?php echo $frm_Description;?></textarea>
   </td>
</tr>
<tr>
   <td>
    &nbsp;
   </td>
   <td>
    Is default <input type="checkbox" name="is_default" value="1"<?php echo ($is_default)?" checked":"";?>> 
   </td>
</tr>
 
<tr>
  <td colspan=2 align=center><br>
   <?php 
  if($perm_modify){
    echo "&nbsp; &nbsp; &nbsp;  <input type=button value='Save' onClick=\"checkForm(this.form)\">\n";
   }
   ?>
   
   <input type=reset value='Reset' name=reset>
   <input type="button" value="Close" onClick="window.close()">
   </center>
  </td>
</tr>

</table>
</div>
</td>
</tr>
</table>
</form>
<?php 
include("./ms_footer_simple.php");

?>