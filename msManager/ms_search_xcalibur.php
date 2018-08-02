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
  
$LCQ_FIRST='';
$LCQ_LAST='';
$LCQ_MIN='300';
$LCQ_MAX='5000';
$LCQ_GROUP='1';
$LCQ_INTER='1';
$LCQ_SCAN='1';
$LCQ_CHARGE='';
$LCQ_PEAKS='';
$LCQ_CALC='';
$LCQ_SEQ='';
$LCQ_OPTIONS='';
$LCQ_User='';
$LCQ_TEMPLATE='';
$LCQ_THRESHOLD= '';
$LCQ_MSN= '';

$parameter_file_folder = "../TMP/search_parameters";
define ("PARS_FILE", "$parameter_file_folder/LCQ_par.txt");


require("../common/site_permission.inc.php"); 
require("../common/common_fun.inc.php");
include ( "./is_dir_file.inc.php");


$USER = $_SESSION['USER'];
$mainDB = new mysqlDB(PROHITS_DB);
$SQL  = "select P.Insert, P.Modify, P.Delete from PagePermission P, Page G where P.PageID=G.ID and G.PageName='Auto Search' and UserID=$USER->ID";
$record = $mainDB->fetch($SQL);
if(count($record)){
  $perm_modify = $record['Modify'];
  $perm_delete = $record['Delete'];
  $perm_insert = $record['Insert'];
} 
if($myaction == 'yes' and $perm_modify){
  $to_file = '';
  if(!is_dir($parameter_file_folder)){
	  mkdir($parameter_file_folder);
  }
  if(!$fd_prt = @fopen(PARS_FILE, "w")){
    fatalError("Apache has no write permission for file ". PARS_FILE . ". Please change the setting of Prohits server computer.", __LINE__);
  }
	$LCQ_User = $USER->Fname. " ". $USER->Lname;
  $LCQ_Date = @date("F j, Y");
  $to_file .= "LCQ_PARS=".$frm_lcq_par_str;
  $to_file .= "\nLCQ_User=".$USER->Fname. " ". $USER->Lname;
  $to_file .= "\nLCQ_Date=". @date("F j, Y");
  fwrite($fd_prt, $to_file);
  fclose($fd_prt); 
}else {
  if($myaction){
    $msg = "You have no permission to change the setting.";
  }
}



if(!$frm_lcq_par_str and is_file(PARS_FILE)){
	$lines = file(PARS_FILE);
	if(count($lines) > 2){
		$LCQ_PARS = '';
		foreach($lines as $value){
			$tmp_arr = explode('=',trim($value));
    	if(count($tmp_arr)==2) $$tmp_arr[0] = $tmp_arr[1];
    }
		$frm_lcq_par_str = $LCQ_PARS;
  }
}
if($frm_lcq_par_str){
  $tmp_arr = explode("-", $frm_lcq_par_str);
  //print_r($tmp_arr);
  foreach($tmp_arr as $val){
    //echo "'$val'";
    if(preg_match('/^([A-Z])([0-9].*)/', $val, $matchs)){
      if(count($matchs) == 3){
        $matchs[2] = trim($matchs[2]);
        switch($matchs[1]){
				case "F":
          $LCQ_FIRST = $matchs[2]; break;
				case "L":
          $LCQ_LAST = $matchs[2]; break;
        case "B":
          $LCQ_MIN = $matchs[2]; break;
        case "T":
          $LCQ_MAX = $matchs[2]; break;
        case "M":
          $LCQ_GROUP = $matchs[2]; break;
        case "S":
          $LCQ_INTER = $matchs[2]; break;
        case "G":
          $LCQ_SCAN = $matchs[2]; break;
        case "I":
          $LCQ_PEAKS = $matchs[2]; break;
        case "C":
          $LCQ_CHARGE = $matchs[2]; break;
        }
      }
    }
  }
}

include("./ms_header_simple.php");
?>

<script language="javascript">
 function closePop(theForm){
    if(opener.document.forms.length > 0){
		  var str = getParString(theForm);
			opener.document.forms[0].frm_lcq_par_str.value=str;
			if(opener.document.forms[0].name == 'form_task'){
      	opener.refreshWin();
			}
		} 
		window.close();
 }
 function getParString(theForm){
		var str = '';
		if(theForm.LCQ_FIRST.value){
		str += ' -F'+ trim(theForm.LCQ_FIRST.value);
		}
		if(theForm.LCQ_LAST.value){
		str += ' -L'+ trim(theForm.LCQ_LAST.value);
		}
		if(theForm.LCQ_MIN.value){
		str += ' -B'+ trim(theForm.LCQ_MIN.value);
		}
		if(theForm.LCQ_MAX.value){
		str += ' -T'+ trim(theForm.LCQ_MAX.value);
		}
		if(theForm.LCQ_PEAKS.value){
      var tmp = trim(theForm.LCQ_PEAKS.value);
      if(tmp > 0){
		    str += ' -I'+tmp;
      }
		}
		if(theForm.LCQ_INTER.value){
		str += ' -S'+trim(theForm.LCQ_INTER.value);
		}
		if(theForm.LCQ_SCAN.value){
		str += ' -G'+trim(theForm.LCQ_SCAN.value);
		}
		if(trim(theForm.LCQ_GROUP.value)){
		str += ' -M'+trim(theForm.LCQ_GROUP.value);
		}
		if(trim(theForm.LCQ_CHARGE.value)){
		str += ' -C'+trim(theForm.LCQ_CHARGE.value);
		}
		return trim(str);
 }
 function saveForm(theForm){
    theForm.frm_lcq_par_str.value = getParString(theForm);
    theForm.submit();
 }
</script>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" name="lcq_form" id="lcq_form">
<input type=hidden name=myaction value='yes'>
<input type=hidden name='frm_lcq_par_str' value=''>
<table border="1" cellpadding="0" cellspacing="0" width="100%">
  <tr><td align=center><br>
    <table border="0" cellpadding="0" cellspacing="2">
      <tr>
        <td height=60><img src='./images/xcalibur.gif' border=0></td>
	      <td><b><font color='red' face='helvetica,arial,futura' size='3'>Xcalibur Parameters</font></b><br>
	        Convert Finnigan Xcalibur RAW file into peak lists using lcq_dta.exe or extract_msn.exe
		    </td>
	    </tr>
      <tr>
        <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
      </tr>
      
      <tr>
        <td colspan=2>
        <?php 
        if($LCQ_User){
          echo "Set by: <b>".$LCQ_User ."</b> &nbsp;&nbsp;&nbsp;  Set Date: <b>" .$LCQ_Date ."</b>";
        }
        ?>
        </td>
      </tr>
      <tr>
        <td colspan=2><br>
        <TABLE BORDER="0" CELLSPACING="1" CELLPADDING="0" width=100% bgcolor=#d4d4d4>
          <TR>
            <TD>
            &nbsp;</TD>
            <TD COLSPAN=3><h2>LCQ_DTA Shell</h2></TD>
          </TR>
          <TR>
            <TD>
            &nbsp;</TD>
            <TD COLSPAN=3>Leave scan range limits empty to process entire data file. </TD>
          </TR>
          <TR>
            <TD COLSPAN=4 NOWRAP>&nbsp;</TD>
          </TR> 
          <TR>
            <TD ALIGN="RIGHT" NOWRAP>
           First Scan</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_FIRST" SIZE="6" TYPE="text" value='<?php echo $LCQ_FIRST;?>'></TD>     
            <TD ALIGN="RIGHT" NOWRAP>
          Last Scan</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_LAST" SIZE="6" TYPE="text" value='<?php echo $LCQ_LAST;?>'></TD>     
          </TR>
          <TR>
            <TD ALIGN="RIGHT" NOWRAP>
          Min. Mass</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_MIN"  SIZE="6" TYPE="text" value='<?php echo ($LCQ_MIN)?$LCQ_MIN:'300';?>'><B>&nbsp;Da</B></TD>     
            <TD ALIGN="RIGHT" NOWRAP>
          Max. Mass</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_MAX" SIZE="6" TYPE="text" value='<?php echo ($LCQ_MAX)?$LCQ_MAX:'4000';?>'><B>&nbsp;Da</B></TD>     
          </TR>
          <TR>
            <TD ALIGN="RIGHT" NOWRAP>
          Grouping Tolerance</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_GROUP" SIZE="6" TYPE="text" value='<?php echo ($LCQ_GROUP)?$LCQ_GROUP:'1.4';?>'><B>&nbsp;Da</B></TD>     
            <TD ALIGN="RIGHT" NOWRAP>
          Intermediate Scans</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_INTER" SIZE="6" TYPE="text" value='<?php echo ($LCQ_INTER)?$LCQ_INTER:'25';?>'></TD>     
          </TR>
          <TR>
            <TD ALIGN="RIGHT" NOWRAP>
          Min. Scans / Group</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_SCAN" SIZE="6" TYPE="text" value='<?php echo ($LCQ_SCAN)?$LCQ_SCAN:'1';?>'></TD>     
            <TD ALIGN="RIGHT" NOWRAP>
          Precursor Charge</TD>
            <TD NOWRAP>
            <SELECT NAME="LCQ_CHARGE">
            <OPTION<?php echo ($LCQ_CHARGE == 'AUTO')?' SELECTED':'';?> value=''>AUTO
            <OPTION<?php echo ($LCQ_CHARGE == '1')?' SELECTED':'';?> value='1'>1
            <OPTION<?php echo ($LCQ_CHARGE == '2')?' SELECTED':'';?> value='2'>2
            <OPTION<?php echo ($LCQ_CHARGE == '3')?' SELECTED':'';?> value='3'>3
            <OPTION<?php echo ($LCQ_CHARGE == '4')?' SELECTED':'';?> value='4'>4
            </SELECT></TD> 
          </TR>
          <TR>
            <TD colspan=4 bgcolor=white>&nbsp;</TD>
          </TR>
          <TR>
            <TD ALIGN="RIGHT" NOWRAP>
        	Calculate Charge</TD>
        	<TD NOWRAP>
        	<INPUT NAME="LCQ_CALC" TYPE="CHECKBOX" <?php echo ($LCQ_CALC)?'checked':'';?>></TD>
        
            <TD ALIGN="RIGHT" NOWRAP>
        	Min. Peaks in .DTA</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_PEAKS" VALUE="<?php echo ($LCQ_PEAKS)?$LCQ_PEAKS:"0";?>" SIZE="6" TYPE="text"></TD>
          </TR>
           
          <TR>
            <TD ALIGN="RIGHT" NOWRAP>
        	AA Sequence</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_SEQ" VALUE="<?php echo ($LCQ_SEQ)?$LCQ_SEQ:"";?>" SIZE="10" TYPE="text"></TD>
        
            <TD ALIGN="RIGHT" NOWRAP>
           Option String</TD>
            <TD NOWRAP>
            <INPUT NAME="LCQ_OPTIONS" VALUE="<?php echo ($LCQ_OPTIONS)?$LCQ_OPTIONS:"";?>" SIZE="10" TYPE="text">
            &nbsp;
            </TD>
          </TR>
          <TR>
            <TD colspan=4>&nbsp;</TD>
          </TR>
          </TABLE>
        </td>
      </tr>
      <tr>
        <td colspan=2 align=center><br>
        <?php if($perm_modify){?>
        <input type="button" value="Save" onClick="saveForm(this.form)">
        <?php 
        }else{
          echo "You have no permission to change the setting.";
        }
        
        ?>
        <input type="reset" value='Rest'>
        <input type="button" value='Close' onClick="closePop(this.form)">
        </td>
      </tr>
   </table>
  </td></tr>
</table>
</form>
<?php 
include("./ms_footer_simple.php");
?>
