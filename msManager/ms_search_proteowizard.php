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

$tableName = '';
$thePage = '';
$myaction = '';
$frm_lcq_par_str = '';
$perm_modify = false;
$perm_delete = false;
$perm_insert = false;

$set_arr = array();
$theSet_arr = array();
$set_arr = array();
$set_name_arr = array();
$frm_setName = '';
$frm_setID = 0;
$set_UserID = '';
$storagePop = 0;

$frm_Machine = '';
$frm_Description = '';
 
$is_default = 0;
$is_SWATH = 0;

$wizard_User= '';
$msg = ''; 
$use_proteowizard  = '';
$FIRST = '';
$LAST = '';
$PEAKPICKING = '2';
$PREFER_VENDOR = 'true';
$MSLEVEL = '2';
$GZIP = '';
$PAR_User = '';
$AutoConverterID = '';

$frm_PROTEOWIZARD_par_str = '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g';
$frm_SCIEX_par_str = ' -proteinpilot /doubleprecision';

include("./ms_permission.inc.php"); 
include ( "./is_dir_file.inc.php");
require("./common_functions.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$taskTables= $managerDB->list_tables();
if(!$frm_Machine){
  foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
    if(in_array($baseTable."SearchTasks", $taskTables)){
      $frm_Machine = $baseTable;
    }
  }
}

$converter_err = check_search_engine_url('converter');
if(strpos($converter_version, "and SCIEX converter")){
  $sciex_converter_on = 1;
}
 
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

if(isset($frm_setName) and trim($frm_setName) and $myaction == 'yes' and $perm_modify){
  $to_file = ''; 
  $com = '';
  if($ENCODE == '32'){
      $com .= "--32 --mz32 --inten32";
  }else{
    $com .= "--64 --mz64 --inten64";
  }
  if($PEAKPICKING){
    $com .= " --filter \"peakPicking $PREFER_VENDOR $PEAKPICKING\"";
  }
  if($MSLEVEL){
    $com .= " --filter \"msLevel $MSLEVEL\"";
  }
  if($FIRST && $LAST){
    $com .= " --filter \"index [$FIRST,$LAST]\"";
  }
  
  if($THRESHOLD_1 and $THRESHOLD_2 and $THRESHOLD_3){
    $THRESHOLD_2 = preg_replace("/^0/", '', $THRESHOLD_2);
    $com .= " --filter \"threshold $THRESHOLD_1 $THRESHOLD_2 $THRESHOLD_3\"";
  }
  if($GZIP){
    $com .= " -g";
  }
  $frm_PROTEOWIZARD_par_str = $com;
  if(isset($sciex_converter_on)){
    $frm_SCIEX_par_str = "$use_proteowizard $OUTPUT_CONTENT_TYPE $PRECISON";
    $pra_str = $frm_PROTEOWIZARD_par_str."|".$frm_SCIEX_par_str;
  }else{
    $pra_str = $frm_PROTEOWIZARD_par_str;
  }
  $frm_setID = search_para_add_modify('Converter', $frm_setID, $frm_setName, $USER->ID, $pra_str, $is_SWATH, $is_default, $frm_Machine, $frm_Description);
  //search_para_add_modify($type, $ID, $Name, $UserID, $parameter, $is_SWATH=0, $is_default=0, $Machine='', $description=''){
  if(!$frm_setID){
    $msg = "The name '$frm_setName' has been used. Please use other name";
    $frm_myaction = 'newSet';
  }
}else if($myaction == 'saveAutoConverter' and $perm_modify){
  $pra_str = '';
   
  foreach ($request_arr as $key=>$value){
    if(strpos($key, 'auto_')=== 0){
      if($pra_str) $pra_str .= ";";
      $pra_str .= $key.":".$value; 
    }
  }
  if($pra_str){
    search_para_add_modify('AutoConverter', $AutoConverterID, 'AutoConverter', $USER->ID, $pra_str);
  }
}
 
if($myaction == 'yes' or !$myaction){
  $frm_myaction = 'modifySet';
}
 
$set_arr = get_search_parameters('Converter', 0, '', $frm_Machine, 'All');

if(!$frm_setID){
  if($set_arr){
    $frm_setID = $set_arr[0]['ID'];
  }else{
    $frm_myaction = 'newSet';
  }
}
 
if($frm_setID and $myaction != 'newSet'){
  
  $myaction = 'modifySet';
  $theSet_arr = get_search_parameters('Converter', $frm_setID);
  $frm_setName =  $theSet_arr['Name'];
	$set_UserID = $theSet_arr['User'];
  $frm_machine = $theSet_arr['Machine'];
  $frm_Description = $theSet_arr['Description'];
  $is_default = $theSet_arr['Default'];
  $is_SWATH = $theSet_arr['SWATH'];

  $para_str = $theSet_arr['Parameters'];

  $tmp_arr = explode("|", $para_str);
  $frm_PROTEOWIZARD_par_str = $tmp_arr[0];
  if(count($tmp_arr) == 2){
    $frm_SCIEX_par_str = $tmp_arr[1];
  }
  $wizard_User = get_userName($set_UserID);
  $Set_Date = $theSet_arr['Date'];
}


$FIRST = '';
$LAST = '';
$PEAKPICKING = '';
$PREFER_VENDOR='';
$MSLEVEL = '';
$GZIP = '';
$THRESHOLD_1 = '';
$THRESHOLD_2 = '';
$THRESHOLD_3 = '';

$PRECISON = '';
$OUTPUT_CONTENT_TYPE = '';
$use_proteowizard = '';
 
if(preg_match('/--64/', $frm_PROTEOWIZARD_par_str, $matchs)){
  $ENCODE = '64';
}else{
  $ENCODE = '32';
}
if(strstr($frm_PROTEOWIZARD_par_str, "-g")){
  $GZIP = '-g';
}

$tmp_arr = explode(" ", $frm_SCIEX_par_str);
if(count($tmp_arr) == 3){
  $PRECISON = $tmp_arr[2];
  $OUTPUT_CONTENT_TYPE = $tmp_arr[1];
  $use_proteowizard = $tmp_arr[0];
}
$tmp_arr = explode("--filter ", $frm_PROTEOWIZARD_par_str);
foreach($tmp_arr as $val){
  if(preg_match('/^"(\S+)\s(.+)"/', $val, $matchs)){
    if(count($matchs) == 3){
      switch($matchs[1]){
      case "index":
        $matchs[2] = preg_replace("/[\[\]]/", '', $matchs[2]);
        $arr = explode(",", $matchs[2]);
        $FIRST = $arr[0];
        if(isset($arr[1])) $LAST = $arr[1];
        break;
      case "peakPicking":
        list($PREFER_VENDOR, $PEAKPICKING) = explode(" ", $matchs[2]);
         
        break;
      case "msLevel":
        $MSLEVEL = $matchs[2];
        break;
      case "threshold":
        $tmp_arr = explode(" ", $matchs[2]);
        if(count($tmp_arr) == 3){
          $THRESHOLD_1 = $tmp_arr[0];
          $THRESHOLD_2 = $tmp_arr[1];
          $THRESHOLD_3 = $tmp_arr[2];
        }
        break;
      }
    }
  }
}


include("./ms_header_simple.php");
?>
<script language="javascript">
 function chang_machine(){
  theForm = document.pwzd_form;
  theForm.frm_setName.value = '';
  theForm.frm_setID.value = '';
  theForm.submit();
}

 function passvalue(theForm){
    if(opener.document.forms.length > 0){
      var str = getParString(theForm);
      opener.document.forms[0].frm_PROTEOWIZARD_par_str.value=str;
      if(opener.document.forms[0].name == 'form_task'){
        opener.refreshWin();
      }
    } 
    window.close();
 }
 function getParString(theForm){
    var str = ''; 
    for (var i=0; i < theForm.ENCODE.length; i++) {
      if (theForm.ENCODE[i].checked){
        ECODE = theForm.ENCODE[i].value; 
      }
    }
    var F = theForm.FIRST.value;
    var L = theForm.LAST.value;
    var PK = theForm.PEAKPICKING.options[theForm.PEAKPICKING.selectedIndex].value;
    var PF_VENDOR = theForm.PREFER_VENDOR.options[theForm.PREFER_VENDOR.selectedIndex].value;
    var MSL =  theForm.MSLEVEL.options[theForm.MSLEVEL.selectedIndex].value;
    var TRDH_1 = theForm.THRESHOLD_1.options[theForm.THRESHOLD_1.selectedIndex].value;
    var TRDH_2 = theForm.THRESHOLD_2.value;
    var TRDH_3 = theForm.THRESHOLD_3.options[theForm.THRESHOLD_3.selectedIndex].value;
    
    if(ECODE == '32'){
       str +="--32 --mz32 --inten32";
    }else{
      str += "--64 --mz64 --inten64";
    }
    
    if(!is_numeric(F)){
      F = '';
    } 
    if(!is_numeric(L)){
      L = '';
    } 
    if(theForm.FIRST.value || theForm.LAST.value){
      str += ' --filter "index ';
      if(!F){
        F = '0';
      }
      str += F + '-';
      if(L){
        str += L + '"';
      }else{
        str += '"';
      }
    }
    if(PK){
      str += ' --filter "peakPicking ' + PF_VENDOR + ' ' + PK + '"';
    }
    if(MSL){
      str += ' --filter "msLevel ' + MSL + '"';
    }
    if(TRDH_1 && TRDH_2 && TRDH_3){ 
      str +=  ' --filter "threshold ' + TRDH_1 + ' ' + TRDH_2 + ' ' + TRDH_3 + '"';
    }
    if(theForm.GZIP.checked){
      str += ' -g';
    }
    <?php if(isset($sciex_converter_on)){?>
    //Sciex paramters
    str += '|';
    if(theForm.use_proteowizard.checked){
      str += '1';
    }
    str += ' ' + theForm.OUTPUT_CONTENT_TYPE.options[theForm.OUTPUT_CONTENT_TYPE.selectedIndex].value; 
    for (var i=0; i < theForm.PRECISON.length; i++) {
      if (theForm.PRECISON[i].checked){
        str += ' ' + theForm.PRECISON[i].value; 
      }
    }
    <?php }?>
    return trim(str);
 }
 
 function isNewSet(newSet){
    theForm = document.pwzd_form;
    if(newSet){
      theForm.frm_Machine.value = '';
      theForm.myaction.value = 'newSet';
    }else{
      theForm.myaction.value = 'modifySet';
    }
    theForm.submit();
 }
 function checkForm(theForm){
    if(isEmptyStr(theForm.frm_setName.value)) {
      alert("Please type the new set name.");
      return false;
    }
    theForm.submit();
 }
 function saveAutoConverter(theForm){
    theForm.myaction.value = 'saveAutoConverter';
    theForm.submit();
 }
 
  
 
</script>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" name="pwzd_form" id="pwzd_form">
<input type=hidden name=myaction value='yes'>
<input type=hidden name=storagePop value='<?php echo $storagePop;?>'>
<input type=hidden name=AutoConverterID value='<?php echo $AutoConverterID;?>'>
<table border="0" cellpadding="0" cellspacing="2" width="100%">
  <tr><td align=center><br>
    <table border="0" cellpadding="2" cellspacing="2" width=90%>
      <tr>
        <td height=60><img src='./images/proteowizard.png' border=0></td>
        <td><b><font color='#0066cc' face='helvetica,arial,futura' size='3'>proteowizard Parameters</font></b><br>
          Convert RAW file into peak lists using msconvert.exe
          <a onClick="newpopwin('../doc/proteowizard_prt.txt',600,700)" class=button><img src='./images/help2.gif' border=0></a>
          <?php 
          if($converter_err){
            echo "<br><font color=red>Error: $converter_err</font>";
          }
          ?>
        </td>
      </tr>
      <tr>
        <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
      </tr>
     <tr>
      <td colspan=2>
      ProteoWizard supports the following vendor proprietary
      formats on Windows, through the use of vendor software libraries:
          <a onClick="newpopwin('http://proteowizard.sourceforge.net/',600,700)" class=button>[proteowizard home]</a>
          &nbsp; &nbsp; &nbsp; <a onClick="newpopwin('<?php echo RAW_CONVERTER_SERVER_PATH;?>?vendor=test',600,700)" class=button>[test setup]</a>
          <ul>
            <li>Agilent* (MassHunter .d)
            <li>Applied Biosystems (WIFF)
            <li>Bruker (Compass .d, FID, YEP, BAF)
            <li>Thermo Fisher* (RAW)
            <li>Waters (MassLynx .raw)
          </ul>
      </td>
     </tr>
      <tr>
        <td colspan=2>
        <?php 
        if($PAR_User){
          echo "Set by: <b>".$PAR_User ."</b> &nbsp;&nbsp;&nbsp;  Set Date: <b>" .$PAR_Date ."</b>";
        }
        ?>
        </td>
      </tr>
      <tr>
        <td colspan=2>
        <TABLE BORDER="0" CELLSPACING="1" CELLPADDING="1" width=100%>
          <TR>
            <TD COLSPAN=4 bgcolor=#9a9a9a><font size="+1" color="#FFFFFF"><b>Parameters</b></font>
            </TD>
          </TR>
          <TR>
            <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4>
           First Scan</TD>
            <TD NOWRAP bgcolor=#d4d4d4>
            <INPUT NAME="FIRST" SIZE="6" TYPE="text" value='<?php echo $FIRST;?>'></TD>     
            <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4>
          Last Scan</TD>
            <TD NOWRAP bgcolor=#d4d4d4>
            <INPUT NAME="LAST" SIZE="6" TYPE="text" value='<?php echo $LAST;?>'></TD>     
          </TR>
           
          <TR>
            <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4>
          apply peak picking</TD>
            <TD NOWRAP bgcolor=#d4d4d4>
            <SELECT NAME="PEAKPICKING">
            <OPTION value=''<?php echo (!$PEAKPICKING)?" selected":"";?>>no
            <OPTION value='1'<?php echo ($PEAKPICKING == '1')?" selected":"";?>>msLevel 1
            <OPTION value='1-'<?php echo ($PEAKPICKING == '1-')?" selected":"";?>>all msLevel
            <OPTION value='2'<?php echo ($PEAKPICKING == '2')?" selected":"";?>>msLevel 2
            <OPTION value='2-'<?php echo ($PEAKPICKING == '2-')?" selected":"";?>>msLevel >=2
            </SELECT>
             prefer vendor:
            <SELECT NAME="PREFER_VENDOR">
            <OPTION value='true'<?php echo ($PREFER_VENDOR=='true')?" selected":"";?>>yes
            <OPTION value='false'<?php echo ($PREFER_VENDOR=='false')?" selected":"";?>>no
            </SELECT>
            </TD>  
            <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4>
          extract ms level</TD>
            <TD NOWRAP bgcolor=#d4d4d4>
            <SELECT NAME="MSLEVEL">
            <OPTION value=''<?php echo (!$MSLEVEL)?" selected":"";?>>all msLevel
            <OPTION value='2'<?php echo ($MSLEVEL == '2')?" selected":"";?>>msLevel 2
            <OPTION value='2-'<?php echo ($MSLEVEL == '2-')?" selected":"";?>>msLevel >=2
            </SELECT> 
           
            </TD>     
          </TR>
          <TR>
            <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4>
          gzip entire output file</TD>
            <TD NOWRAP bgcolor=#d4d4d4>
            <input type=checkbox Name=GZIP ID=GZIP value='-g'<?php echo ($GZIP)?" checked":"";?>> for mzXML file
            </TD>  
            <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4>
             <b>Encoding:</b></TD>
            <TD NOWRAP bgcolor=#d4d4d4>
            <input type=radio Name=ENCODE ID=ENCODE32 value='32'<?php echo ($ENCODE == '32')?" checked":"";?>> 32-bit
            <input type=radio Name=ENCODE ID=ENCODE64 value='64'<?php echo ($ENCODE == '64')?" checked":"";?>>64-bit
            </TD>   
          </TR>
          <TR>
            <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4 valign=top>
            <b>Threshold:</b></TD>
            <TD NOWRAP bgcolor=#d4d4d4 colspan=3>
            <SELECT NAME="THRESHOLD_1">
            <OPTION value=''>
            <OPTION value='count'<?php echo ($THRESHOLD_1 == 'count')?" selected":"";?>>count
            <OPTION value='count-after-ties'<?php echo ($THRESHOLD_1 == 'count-after-ties')?" selected":"";?>>count-after-ties
            <OPTION value='absolute'<?php echo ($THRESHOLD_1 == 'absolute')?" selected":"";?>>absolute
            <OPTION value='bpi-relative'<?php echo ($THRESHOLD_1 == 'bpi-relative')?" selected":"";?>>bpi-relative
            <OPTION value='tic-relative'<?php echo ($THRESHOLD_1 == 'tic-relative')?" selected":"";?>>tic-relative
            <OPTION value='tic-cutoff'<?php echo ($THRESHOLD_1 == 'tic-cutoff')?" selected":"";?>>tic-cutoff
            </SELECT>  
            
            <INPUT NAME="THRESHOLD_2" SIZE="4" TYPE="text" value='<?php echo $THRESHOLD_2;?>'>    
           
            <SELECT NAME="THRESHOLD_3">
            <OPTION value=''>
            <OPTION value='most-intense'<?php echo ($THRESHOLD_3 == 'most-intense')?" selected":"";?>>most-intense
            <OPTION value='least-intense'<?php echo ($THRESHOLD_3 == 'least-intense')?" selected":"";?>>least-intense
            </SELECT>
            </TD>     
          </TR>
           
          </TABLE>
        </td>
      </tr>
     <?php if(isset($sciex_converter_on)){?>
      <tr>
        <td height=60><img src='./images/abSciex.png' border=0></td>
        <td><b><font color="#0066cc" face='helvetica,arial,futura' size='3'>AB SCIEX MS Data Converter</font></b><br>
          Convert WIFF file into peak lists using SCIEX MS Data Converter<br>
        </td>
      </tr>
      <tr>
        <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
      </tr>
     <tr>
      <td colspan=2>
      This converter only converts spectral data (WIFF) from any AB SCIEX instrument. <br>
      <input type="checkbox" name="use_proteowizard" value="1"<?php echo ($use_proteowizard)?" checked":"";?>>
      <font color="#FF0000">I want to use proteowizard to convert WIFF file.</font>
       <tr>
      <td colspan=2>
        <Table width="100%" cellspacing="1" cellpadding="1" border="0">
            <TR>
              <TD COLSPAN=4 bgcolor=#9a9a9a><font size="+1" color="#FFFFFF"><b>Parameters</b></font>
              </TD>
            </TR>
            <TR>
              <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4>
             Output content type:</TD>
              <TD NOWRAP bgcolor=#d4d4d4>
               <SELECT NAME="OUTPUT_CONTENT_TYPE">
                <OPTION value='-proteinpilot'<?php echo ($OUTPUT_CONTENT_TYPE == '-proteinpilot')?" selected":"";?>>-proteinpilot 
                <OPTION value='-centroid'<?php echo ($OUTPUT_CONTENT_TYPE == '-centroid')?" selected":"";?>>-centroid
                <OPTION value='-profile'<?php echo ($OUTPUT_CONTENT_TYPE == '-profile')?" selected":"";?>>-profile 
               </SELECT>
              </TD>     
              <TD ALIGN="RIGHT" NOWRAP bgcolor=#d4d4d4>
            Binary data precision:</TD>
              <TD NOWRAP bgcolor=#d4d4d4>
              <input type=radio Name=PRECISON value='/singleprecision'<?php echo ($PRECISON == '/singleprecision')?" checked":"";?>> 32-bit
              <input type=radio Name=PRECISON value='/doubleprecision'<?php echo ($PRECISON == '/doubleprecision')?" checked":"";?>>64-bit
              </TD>     
            </TR>
        </Table>
        </td>
      </tr>
      <?php }?>
      <tr>
       <td colspan=2><br><b><font color='red' face='helvetica,arial,futura' size='3'>Parameter Set</font></b>
       <?php if($perm_modify){?>&nbsp; &nbsp;
       New Set<input type=radio value=newSet name=frm_set onClick="isNewSet(true)" <?php echo ($myaction == 'newSet')?'checked':'';?>>
       Modify Set<input type=radio value=modifySet name=frm_set onClick="isNewSet(false)" <?php echo ($myaction == 'modifySet')?'checked':'';?>>
       <?php 
       }else{
         
       }
       if($wizard_User and $myaction != 'newSet'){
           echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
           Set by: <b>".$wizard_User."</b>&nbsp; &nbsp; Set date:<b>".$Set_Date."</b>\n"; 
       }
       echo "<br><font color=#008000>Only Prohits administrator can change the setting</font>.<br>";
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
       <td width=30%><b>Set Name</b>: </td>
       <td>
   <?php 
    if($myaction == 'newSet'){
      echo '<input type=text size=20 name=frm_setName>';
    }else{
      if($set_arr){
        echo "<input type=hidden name=frm_setName value='".$frm_setName."'>\n";
        echo "<select id='frm_setID' name='frm_setID' onChange=\"isNewSet(false)\">\n";
        foreach($set_arr as $tmpSet){
          $selected = ($tmpSet['ID'] == $frm_setID)?" selected":"";
          $style_str = '';
          if($tmpSet['SWATH']){
            $style_str = " style='background-color: #CCCCCC;' ";
            if($tmpSet['Default']){
              $style_str = " style='background-color: #D6AD03;' ";
            }
          }else{
            if($tmpSet['Default']){
              $style_str = " style='background-color: yellow;' ";
            }
          }
          echo "<option value='" . $tmpSet['ID'] . "'$selected $style_str>".$tmpSet['Name']."\n";          
        }
        echo "</select>\n";
      }else if($perm_insert){
        echo "<font color=\"#FF0000\">Please create new parameter set.</font>";
      }
    }
?>
      </td>
    <tr>
     <td><b>Mass Spec Machine</b>: </td>
     <td>
   <select name=frm_Machine onChange="chang_machine()">
 <?php foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
		  if(in_array($baseTable."SearchTasks", $taskTables)){
 ?>   
     <option value='<?php echo $baseTable?>' <?php echo (($baseTable == $frm_Machine)?" selected":"")?>><?php echo $baseTable?>
 <?php   }
   }?>
   </select>
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
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    Is for SWATH file  <input type="checkbox" name="is_SWATH" value="1"<?php echo ($is_SWATH)?" checked":"";?>> 
   </td>
</tr>

 
 
 <tr>
  <td colspan=4>
   <br><center>
   <?php 
   if($perm_modify){
    echo "&nbsp; &nbsp; &nbsp;  <input type=button value='Save' onClick=\"checkForm(this.form)\">\n";
   }
   ?>
   <input type=reset value='Reset' name=reset>
   <?php if($storagePop){
    echo "<input type=button value='Pass Value' name='pass' onClick=\"passvalue(this.form);\">";
   }
   ?>
   <input type="button" value="Close" onClick="window.close()">
   </center>
  </td>
 </tr>
 <tr>
  <td colspan=4 align=left>
  <?php 
    $Auto_set_arr = get_search_parameters('AutoConverter', 0);
    if($Auto_set_arr){
      $AutoConverterID = $Auto_set_arr[0]['ID'];
      $auto_tmp_arr = explode(";", $Auto_set_arr[0]['Parameters']);
      foreach($auto_tmp_arr as $theValue){
        list($theName, $v) = explode(":", $theValue);
        $$theName = $v;
      }
    }
  ?>
    <input type=hidden name=AutoConverterID value='<?php echo $AutoConverterID;?>'>
    Autolinked files will be converted using the following settings:
    <a id="auto_convert_a" class="button" title="set automatically convert raw file" href="javascript:showhide('auto_convert', 'auto_convert_a')">[Detail]</a>
    <div id="auto_convert" style="display: none;">
    <table width=100%>
    <tr>
    	<th>Mass Spec</th>
      <th>Auto Convert</th>
    	<th>DDA File Parameter</th>
    	<th>SWATH File Parameter</th>
    </tr>
    <?php 
    $set_arr = get_search_parameters('Converter');
    
    foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
    ?>
    <tr align=right bgcolor="#66ccff">
    	<td><?php echo $baseTable?></td>
      <?php 
      $frmName = "auto_".$baseTable;
      if(isset($$frmName) and $$frmName){
        $isChecked = ' checked';
      }else{
        $isChecked = '';
      }
      ?>
      <td><input type="checkbox" name="auto_<?php echo $baseTable?>" value="1"<?php echo $isChecked;?>></td>
    	<td>
        <select name="auto_<?php echo $baseTable?>_DDA_ID">
        <option value=''>&nbsp; &nbsp; &nbsp;
        <?php 
        $frmName = "auto_".$baseTable."_DDA_ID";
        foreach($set_arr as $tmpSet){
          if($tmpSet['Machine'] == $baseTable and $tmpSet['Default'] and !$tmpSet['SWATH']){
            $selected = (isset($$frmName) and $$frmName == $tmpSet['Name'])?" selected":"";
            echo "<option value='".$tmpSet['Name']."' $selected>".$tmpSet['Name']."\n";
          }
        }
        ?>
        </select>
      </td>
      <td>
        <select name="auto_<?php echo $baseTable?>_SWATH_ID">
        <option value=''>&nbsp; &nbsp; &nbsp;
        <?php 
        $frmName = "auto_".$baseTable."_SWATH_ID";
        foreach($set_arr as $tmpSet){
          if($tmpSet['Machine'] == $baseTable and $tmpSet['Default'] and $tmpSet['SWATH']){
            $selected = (isset($$frmName) and $$frmName == $tmpSet['Name'])?" selected":"";
            echo "<option value='".$tmpSet['Name']."' $selected>".$tmpSet['Name']."\n";
          }
        }
        ?>
        </select>
      </td>
    </tr>
    <tr align=center bgcolor="#66ccff">
    <td colspan=4>
    <?php 
    }
    if(defined("CONVERT_AUTOLINKED_FILE") and CONVERT_AUTOLINKED_FILE){
      if($perm_modify){
    ?>
    <input type="button" onclick="saveAutoConverter(this.form)" value="Save">
    <input type="reset" name="reset" value="Reset">
    <?php 
      }
    }else
      echo "Converting autolinked file has not set in Prohits configure file";
    ?>
    </td> 
    </tr>
    </table>
    </div>
    
  
  </td>
  </tr>
  </table>
  </td>
  </tr>
</table>
</form>
<?php 
include("./ms_footer_simple.php");

 
?>
