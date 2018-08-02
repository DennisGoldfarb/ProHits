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
$sample_as_name = '';
$run_saint = 'SAINT';
$parentID = '';

$Quant_InternalLibSearch = '';
$saint_bait_name_str = '';

require("../common/site_permission.inc.php");

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/


if(!$parentID) exit;
$SQL = "SELECT `ID`, 
               `Name`, 
               `UserID`, 
               `Date`, 
               `Description`, 
               `Machine`, 
               `SearchEngine`, 
               `TaskIDandFileIDs`, 
               `Status`, 
               `ProjectID`, 
               `UserOptions`, 
               `ParentQuantID`, 
               `ProcessID` 
       FROM `DIAUmpireQuant_log` 
       WHERE ID = '$parentID'";
$Quant_arr = $PROHITSDB->fetch($SQL);


$UserOptions_arr = explode(';',$Quant_arr['UserOptions']);

$frm_selected_list_str = $Quant_arr['TaskIDandFileIDs'];
$frm_machine = $Quant_arr['Machine'];
$frm_SearchEngine =  $Quant_arr['SearchEngine'];
$DIAUmpireQuantName = $Quant_arr['Name'];
$DIAUmpireQuantDescription = $Quant_arr['Description'];
$frm_selected_sample_str = '';
$raw_arr = explode(',',$frm_selected_list_str);

$rawkID_sampleID_arr = array();
foreach($raw_arr as $raw_val){
  $tmp_tmp_arr = explode('|',$raw_val);
  $rawkID_sampleID_arr[$tmp_tmp_arr[1]] = $tmp_tmp_arr[2];
  if($frm_selected_sample_str) $frm_selected_sample_str .= ',';
  $frm_selected_sample_str .= $tmp_tmp_arr[2];
}

$control_id_arr = array();
$SAINT_pa = '';
$mapDIA_pa = '';
foreach($UserOptions_arr as $UserOptions_val){
  if(strstr($UserOptions_val,"SAINT=Version:") or strstr($UserOptions_val,"mapDIA=")){
    $SAINT_Info_arr = explode("\n",$UserOptions_val);    
    foreach($SAINT_Info_arr as $SAINT_Info_val){
      if(preg_match('/^SAINT_control_id_str=(.+)/', $SAINT_Info_val, $matches)){
        $control_id_str = $matches[1];  //45
        $control_id_arr = explode(',',$control_id_str);
      }elseif(preg_match('/^SAINT_bait_name_str=(.+)/', $SAINT_Info_val, $matches)){
        $saint_bait_name_str = $matches[1];
      }elseif(preg_match('/^SAINT_or_mapDIA=(.+)/', $SAINT_Info_val, $matches)){
        $run_saint = $matches[1];
      }elseif(preg_match('/^SAINT=(.+)/', $SAINT_Info_val, $matches)){
        $SAINT_pa = $matches[1];
      }elseif(preg_match('/^mapDIA=(.+)/', $SAINT_Info_val, $matches)){
        $mapDIA_pa = $matches[1];
      }
    } 
  }else{
    $tmp_arr = explode(':',$UserOptions_val);
    if($tmp_arr[0] == 'InternalLibSearch'){
      $Quant_InternalLibSearch = $tmp_arr[1];
    }elseif($tmp_arr[0] == 'PeptideFDR'){
      $Quant_PeptideFDR = $tmp_arr[1];
    }elseif($tmp_arr[0] == 'ProteinFDR'){
      $Quant_ProteinFDR = $tmp_arr[1];
    }elseif($tmp_arr[0] == 'ProbThreshold'){
      $Quant_ProbThreshold = $tmp_arr[1];
    }elseif($tmp_arr[0] == 'FilterWeight'){
      $Quant_FilterWeight = $tmp_arr[1];
    }elseif($tmp_arr[0] == 'MinWeight'){
      $Quant_MinWeight = $tmp_arr[1];
    }elseif($tmp_arr[0] == 'TopNFrag'){
      $Quant_TopNFrag = $tmp_arr[1];
    }elseif($tmp_arr[0] == 'TopNPep'){
      $Quant_TopNPep = $tmp_arr[1];
    }elseif($tmp_arr[0] == 'Freq'){
      $Quant_Freq = $tmp_arr[1];
    }
  }
}

$item_propty_arr = array();
if($frm_selected_sample_str){
  $item_id_str = $frm_selected_sample_str;
   
  $item_name = "Sample";
  $SQL = "SELECT D.ID,
          D.Location AS Name,
          B.GeneName AS Bait_name 
          FROM Band D
          LEFT JOIN Bait B ON (D.BaitID=B.ID) 
          WHERE D.ID IN ($item_id_str) ORDER BY D.Location";
  $item_propty_arr = $HITSDB->fetchAll($SQL);
}
$sample_propty_arr = array();
foreach($item_propty_arr as $item_propty_val){
  $sample_propty_arr[$item_propty_val['ID']] = $item_propty_val;
}

$total_files = count($raw_arr);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Export hits DIAUmpireQuant</title>
<link rel="stylesheet" type="text/css" href="./site_style.css">
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
function checkempty(theform){
  /*var theType = getRadioCheckedValue(theform.run_saint);
  if(!confirm("Are you sure you want to re-run " + theType + "?")){
    return;
  }*/
  theform.action = "DIAUmpire_Quant_run.php";
  theform.theaction.value = '';
  theform.target = 'subWin';
  theform.submit();
}

function toggle_item_type_name(theform){
  theform.action = "DIAUmpire_Quant_reRun_prepare.php";
  theform.target = 'subWin';
  theform.theaction.value = 'changeLabel';
  theform.submit();
}
function moveItem(theIndex, goDown){
  var theform = document.run_DIAUmpireQuant_prepare_form;
  theform.action = "DIAUmpire_Quant_run_prepare.php";
  theform.target = 'subWin';
  
  if(goDown != 'down'){
    theIndex = theIndex - 1;
  }else if(theIndex + 1 >= <?php echo $total_files;?>){
    return;
  }
  
  if(theIndex > -1){
    theform.fileIndex.value = theIndex;
    theform.theaction.value = 'moveDown';
    theform.submit();
  }
}

//-->
</script>
</head>
<body>
<FORM ACTION="DIAUmpire_Quant_run_prepare.php" ID="" NAME="run_DIAUmpireQuant_prepare_form" METHOD="POST">
<input type="hidden" name="ParentQuantID" value="<?php echo $parentID?>">
<input type="hidden" name="frm_selected_list_str" value="<?php echo $frm_selected_list_str;?>">
<input type="hidden" name="frm_machine" value="<?php echo $frm_machine;?>">
<input type="hidden" name="frm_SearchEngine" value="<?php echo $frm_SearchEngine;?>">
<input type="hidden" name="frm_selected_sample_str" value="<?php echo $frm_selected_sample_str;?>">
<input type="hidden" name="item_name" value="<?php echo $item_name;?>">
<input type="hidden" name="saint_bait_name_str" value="<?php echo trim($saint_bait_name_str);?>">  
<input type="hidden" name="control_id_str" value="<?php echo trim($control_id_str);?>"> 
<input type="hidden" name="theaction" value=""> 
<input type="hidden" name="fileIndex" value="">
<input type="hidden" name=DIAUmpireQuantName value='<?php echo $DIAUmpireQuantName;?>'>
<input type="hidden" name="DIAUmpireQuantDescription" value='<?php echo $DIAUmpireQuantDescription;?>'>
<input type="hidden" name="Quant_InternalLibSearch" value='<?php echo $Quant_InternalLibSearch;?>'>
<input type="hidden" name="Quant_PeptideFDR" value="<?php echo $Quant_PeptideFDR;?>">              
<input type="hidden" name="Quant_ProteinFDR" value="<?php echo $Quant_ProteinFDR;?>">
<input type="hidden" name="Quant_ProbThreshold" value="<?php echo $Quant_ProbThreshold;?>">              
<input type="hidden" name="Quant_FilterWeight" value=<?php echo $Quant_FilterWeight?>>
<input type="hidden" name="Quant_MinWeight" value="<?php echo $Quant_MinWeight;?>">     
<input type="hidden" name="Quant_TopNFrag" value="<?php echo $Quant_TopNFrag;?>">
<input type="hidden" name="Quant_TopNPep" value="<?php echo $Quant_TopNPep;?>">
<input type="hidden" name="Quant_Freq" value="<?php echo $Quant_Freq;?>">
<input type="hidden" name="SAINT_pa" value="<?php echo trim($SAINT_pa);?>">
<input type="hidden" name="mapDIA_pa" value="<?php echo trim($mapDIA_pa);?>">

<table border=0 width=95% cellspacing="1" cellpadding=1 align=center>
  <tr>
    <td align=left nowrap><span class=pop_header_text>Re-Run</span></td>
  </tr>  
  <tr>
    <td height='1'><hr size=1>
    <table border=0 width=100% cellspacing="1" cellpadding=3 align=center bgcolor=#5c8ca3>
      <tr bgcolor=white>
        <td colspan="4" align=left nowrap> 
          Run SAINT:<input type=radio name='run_saint' value='SAINT' <?php echo ($run_saint=='SAINT'?'checked':'')?> onClick="view_hide_control('SAINT')">
          &nbsp; &nbsp; Run mapDIA:<input type=radio name='run_saint' value='mapDIA' <?php echo ($run_saint=='mapDIA'?'checked':'')?> onClick="view_hide_control('mapDIA')">

        </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td colspan="4">
    <div id='running_saint' style="display: block">
      <table border=0 width=100% cellspacing="1" cellpadding=0 align=center bgcolor=#5c8ca3>
        <tr  bgcolor=#6699cc height="25">
          <td align=left nowrap><font color="#FFFFFF"><b>Raw File ID</b></font></td>
          <td align=left><font color="#FFFFFF"><b><?php echo $item_name?> Name</b></font></td>
          <td align=left>
            <font color="#FFFFFF"><b>Bait Name/Label</b></font>
      <?php if($item_name != 'Bait' && 0){?>
            <br><input id="sample_as_name" type="checkbox" name="sample_as_name" value='y' <?php echo ($sample_as_name?'checked':'')?> onClick="toggle_item_type_name(this.form)">
            Use sample name
      <?php }?>    
          </td>
          <td align=center nowrap><font color="#FFFFFF"><b>Is control</font></td>
        </tr>      
      <?php 
        $index = 0;
        foreach($rawkID_sampleID_arr as $tmp_key => $tmp_val){
          $val = $sample_propty_arr[$tmp_val];
          $tr_bgcolor = "#f8f8fc";
          $tmp_name = 'CHECK_'.$tmp_key;
          $checked = '';
          $target = array(",", "|", " ");
          $val['Name'] = str_replace($target, "", $val['Name']);
          $val['Bait_name'] = str_replace($target, "",$val['Bait_name']);
          if(in_array($tmp_key, $control_id_arr)){ 
            $checked = 'checked'; 
          }
          $textName = "TEXT_".$tmp_key;
          
          if(isset($$textName) and $theaction !='changeLabel'){
            $tmp_label = $$textName;
          }else{
            $tmp_label = ($sample_as_name)?$val['Name']:$val['Bait_name'];
          }
      ?>  
        <tr id='t_<?php echo $tmp_key?>' bgcolor='<?php echo $tr_bgcolor?>'>
          <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $tmp_key?>&nbsp;&nbsp;</div></td>
          <td align=left nowrap><div class=maintext>&nbsp;&nbsp;<?php echo $val['Name']?>&nbsp;&nbsp;</div></td>
          <td align=><input ID="<?php echo $textName?>" type="text" name="<?php echo $textName?>" size="30"  value='<?php echo $tmp_label?>' maxlength="300" readonly>
          </td>
          <td align=center>
            <input id="d_<?php echo $tmp_key?>" type="checkbox" name="CHECK_<?php echo $tmp_key?>" value="<?php echo $tmp_key?>" <?php echo $checked?>>
          </td>
        </tr>
      <?php  
          $index++;
        }
      ?>
      <tr>
        <td align=center bgcolor='white' colspan="4">
          <input type="button" value=" Next " onClick="checkempty(this.form)" ></td>
        </td>
      </tr>
      </table>
    </div>
  </tr>
</table> 
</FORM>
</body>
<script language="JavaScript" type="text/javascript">
var rowID_arr = [];
<?php foreach($rawkID_sampleID_arr as $tmp_key => $tmp_val){?>
    var d_id = "d_"+<?php echo $tmp_key?>;
    rowID_arr.push(d_id);       
<?php }?>
function view_hide_control(type){
  for(var i=0; i<rowID_arr.length; i++){
    if(type == 'SAINT'){
      document.getElementById(rowID_arr[i]).disabled = false;
    }else{
      document.getElementById(rowID_arr[i]).disabled = true;
    }
  }
}
</script>
</html>
 