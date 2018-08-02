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

$this_User = '';
$Set_Date = '';
$frm_setName = '';
$frm_setID = 0;
$set_UserID = '';

$msg = '';

$set_arr = array();
$perm_modify = '';
$pars_file = '';
$default = 0;
$tbcolor = '#dedede';

$frm_Machine = '';
$frm_Description = '';
$SearchEngine = '';
 
$is_default = 0;
$is_SWATH = 0;

$para_FDR = 0.02;
$para_decoy_fragment_mass_tolerane = 0.03;
$para_parent_mass_tolerance = 25;
$para_fragment_mass_tolerance = 30;
$para_number_scans = 0;
$para_maxRT = 0.1;
$para_minRT = 0.01;
$para_rt = 0;
$dia_win_ms1_start = '';
$dia_win_ms1_start_default = 0;
$dia_win_ms1_end = '';
$dia_win_ms1_end_default = 1250;
$dia_SWATH_window_setting = '';
$dia_SWATH_window_setting_default='
399.5 408.2
407.2 415.8
414.8 422.7
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
 

include("./ms_permission.inc.php");
require("./common_functions.inc.php");
include ( "./is_dir_file.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

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
    //$frm_setID = search_para_add_modify('MSPLIT', $frm_setID, $frm_setName, $USER->ID, $frm_ProjectID, $to_file);
    $frm_setID = search_para_add_modify('MSPLIT', $frm_setID, $frm_setName, $USER->ID, $to_file, $is_SWATH, $is_default, $frm_Machine, $frm_Description, $SearchEngine);
    
    if(!$frm_setID){
      $prohits_err_msg = "The name '$frm_setName' has been used. Please use other name";
      $frm_myaction = 'newSet';
    } 
  }
}

if($frm_myaction == 'yes' or !$frm_myaction){
  $frm_myaction = 'modifySet';
}

$set_arr = get_search_parameters('MSPLIT', 0);

$set_arr = put_default_first($set_arr);

if(!$frm_setID){
  if($set_arr){
    $frm_setID = $set_arr[0]['ID'];
  }
}
 
if($frm_myaction == 'newSet'){
  $default = 1;
  $dia_win_ms1_start = $dia_win_ms1_start_default;
  $dia_win_ms1_end = $dia_win_ms1_end_default;
  $dia_SWATH_window_setting = $dia_SWATH_window_setting_default;
}else if($frm_setID){
  $theSet_arr = get_search_parameters('MSPLIT', $frm_setID);
  $frm_setName = $theSet_arr['Name'];
  $frm_ProjectID = $theSet_arr['ProjectID'];
  $frm_Description = $theSet_arr['Description'];
  $is_default = $theSet_arr['Default'];
  
  if($theSet_arr){
    $thePara_arr = explode(";",$theSet_arr['Parameters']);
     
    $this_User = get_userName($theSet_arr['User']);
     
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
<form name=listform method=post action=<?php echo $PHP_SELF;?>>
<input type=hidden name=frm_myaction value='yes'> 
<table border="0" cellpadding="0" cellspacing="0" width=100% bgcolor=#6699cc>
<tr>
<td align=center>
<div class='divBoxPop'>

<table border="0" cellpadding="0" cellspacing="0">
 <tr>
   <td width=><img src='./images/msplit.gif' border=0>&nbsp;</td>
   <td><span class="pop_header_text">MSPLIT-DIA Parameters</span><br>
     MSPLIT-DIA  is a spectral library search tool that identify  multiplexed MS/MS spectra in DIA data (e.g. SWATH)
   </td>
 </tr>
 <tr>
    <td colspan=2 height=1> <font color="red"><?php echo $prohits_err_msg;?></font></td>
 </tr>
 
 <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
 </tr>
 <tr>
    <td colspan=2 align=center> 
      <br>
      <table bgcolor="" width=98% cellspacing="2" cellpadding="2" border="0">
        
       <tr>   
        <TD align=right bgcolor="<?php echo $tbcolor;?>" width=20% valign=top><font color="#613030"><b>Creating library from MSGFDB search results</b></font></TD>
        <TD align=left bgcolor="<?php echo $tbcolor;?>" >
        &nbsp;<b>FDR </b><INPUT NAME="para_FDR" VALUE="<?php echo $para_FDR;?>" SIZE=3 maxlength="4">
        <br>Creating library from DDA runs:<br>
        picks a representative PSM (currently the one with lowest MSGF probability)  for each unique peptide precursor (i.e. peptide sequence and charge state combination) and ensure that the overall peptide-level FDR for the combined search results is low
        </TD>
      </tr>
      <tr>   
        <TD align=right bgcolor="<?php echo $tbcolor;?>" width=10% valign=top><font color="#613030"><b>Creating decoy spectral library</b></font></TD>
        <TD align=left bgcolor="<?php echo $tbcolor;?>" >
        &nbsp;<b>fragment mass tolerance</b><INPUT NAME="para_decoy_fragment_mass_tolerane" VALUE="<?php echo $para_decoy_fragment_mass_tolerane;?>" SIZE=3 maxlength="4">Da
         <br>
        appends a decoy version of the spectral library to the original library and also performs some noise filtering of the target spectrum based on the fragment ion annotation from the identified peptides.
        </TD>
      </tr>
      <tr>   
        <TD align=right bgcolor="<?php echo $tbcolor;?>" width=10% valign=top rowspan="2"><font color="#613030"><b>Performing spectral library search</b></font></TD>
        <TD align=left bgcolor="<?php echo $tbcolor;?>" >
        &nbsp;<b>parent mass tolerance </b><INPUT NAME="para_parent_mass_tolerance" VALUE="<?php echo $para_parent_mass_tolerance;?>" SIZE=3 maxlength="4">Da
        
        
        </TD>
      </tr> 
      <tr>   
        <TD align=left bgcolor="<?php echo $tbcolor;?>" >
        &nbsp;<b>fragment mass tolerance  </b><INPUT NAME="para_fragment_mass_tolerance" VALUE="<?php echo $para_fragment_mass_tolerance;?>" SIZE=3 maxlength="4">PPM
         
        </TD>
      </tr>
      <INPUT type=hidden NAME="para_number_scans" VALUE="0">
      <tr>   
        <TD align=right bgcolor="<?php echo $tbcolor;?>" width=10% valign=top rowspan="3"><font color="#613030"><b>Filtering search results</b></font></TD>
        <TD align=left bgcolor="<?php echo $tbcolor;?>" >
        &nbsp;<b>max RT used to build RT correlation </b><INPUT NAME="para_maxRT" VALUE="<?php echo $para_maxRT;?>" SIZE=3 maxlength="4">
        
        
        </TD>
      </tr> 
      <tr>   
        <TD align=left bgcolor="<?php echo $tbcolor;?>" >
        &nbsp;<b>min RT used to build RT correlation  </b><INPUT NAME="para_minRT" VALUE="<?php echo $para_minRT;?>" SIZE=3 maxlength="4">
         
        </TD>
      </tr>
      <tr>   
        <TD align=left bgcolor="<?php echo $tbcolor;?>" >
        &nbsp;<b>use retention time to filter result </b><INPUT type="checkbox" NAME="para_rt" VALUE="1"<?php echo ($para_rt)?" checked":"";?>>
         
        </TD>
      </tr>
      <tr>
        <td valign="top" align=right bgcolor=<?php echo $tbcolor;?>><font color="#613030"><b> SWATH window setting </b></font>
        <br>(Start m/z, end m/z<br>separated by space)<br>
         
        (variable SWATH window)       
        </td>
        <td colspan=3 bgcolor=<?php echo $tbcolor;?>>
         <table order=0>
          <tr>
          	<td>#Scan</td>
          	<td>windowBegin&nbsp; &nbsp;
                windowEnd
            </td>
          </tr>
          <tr>
          	<td>MS1</td>
          	<td><INPUT NAME="dia_win_ms1_start" VALUE="<?php echo $dia_win_ms1_start;?>" SIZE=3 maxlength="3">&nbsp; &nbsp;
                <INPUT NAME="dia_win_ms1_end" VALUE="<?php echo $dia_win_ms1_end;?>" SIZE=4 maxlength="5">
            </td>
          </tr>
          <tr>
          	<td valign=top>MS2</td>
          	<td><textarea cols="20" rows="6" name="dia_SWATH_window_setting" valign=top><?php echo $dia_SWATH_window_setting;?></textarea></td>
          </tr>
         </table>
        </td>
      </tr>
  </table>
  
  </td>
 </tr>
 </table>
 </div>
 <div class='divBoxPop'> 
<table border="0" cellpadding="0" cellspacing="0" width=98%>
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
  <td colspan=2>
   <br><center>
   <?php 
	 if($perm_modify and $frm_myaction != 'show'){
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