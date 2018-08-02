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

$frm_selected_bait_str = '';
$frm_order_by = '';
$frm_is_zip = '';
$bait_group_icon_arr = array();
$BaitIDarr_have_hits = array();
$Pid = '';
require("../common/site_permission.inc.php");
require("analyst/common_functions.inc.php");

$bait_group_icon_arr = get_project_noteType_arr($HITSDB);

$SelectedBaits = array();
$tmpBaitArr = array();
if($frm_selected_bait_str){
  $SQL = "SELECT `ID`,`GeneID`,`GeneName`, `BaitAcc`,`TaxID`,`Clone` FROM `Bait` 
          WHERE `ProjectID`='$AccessProjectID' AND ID IN($frm_selected_bait_str) order by ID desc";
  
  $SelectedBaits = $HITSDB->fetchAll($SQL);
  foreach($SelectedBaits as $value) array_push($tmpBaitArr,$value['ID']);
}

$Baits = array();
$SQL = "SELECT `BaitID` FROM `Hits` GROUP BY `BaitID` ORDER BY `BaitID` DESC";
$results = $HITSDB->fetchAll($SQL);
foreach($results as $theID){
  array_push($BaitIDarr_have_hits, $theID['BaitID']);
}
$SQL = "SELECT `BaitID`, `NoteType` FROM `BaitDiscussion` WHERE `NoteType`<>'0' order by BaitID, NoteType";
$BaitDiscussion2 = $HITSDB->fetchAll($SQL);
$BaitNotesArr = array(); 
 
foreach($BaitDiscussion2 as $theNotes){
  if(!isset($BaitNotesArr[$theNotes['BaitID']])){
    $BaitNotesArr[$theNotes['BaitID']] = array();
  }
  array_push($BaitNotesArr[$theNotes['BaitID']], $theNotes['NoteType']);
}
 
if(!$frm_order_by) $frm_order_by = 'ID DESC';
$SQL = "SELECT `ID`,`GeneName`, `BaitAcc`,`Clone` FROM `Bait` 
        WHERE `ProjectID`='$AccessProjectID' ";
if($frm_selected_bait_str){
  $SQL .= "AND ID NOT IN($frm_selected_bait_str) ";
} 
if($frm_order_by == 'ID' or $frm_order_by == 'GeneName' or $frm_order_by == 'BaitAcc'){
  $SQL .= "ORDER BY $frm_order_by";
}else{
  $SQL .= "ORDER BY ID DESC";
}
$Baits = $HITSDB->fetchAll($SQL);

require("site_header.php");
$bg_tb_header = '#7eb48e';
$bg_tb = '#c0c0c0';
?>
<script language="javascript">
var newWin;
function changeOrderBy(){
  theForm = document.form_bait2hits;
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }
  theForm.action = '<?php echo $PHP_SELF;?>';
  theForm.submit();
}

function addBait(){
  theForm = document.form_bait2hits;
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm = document.form_bait2hits;
  theForm.action = '<?php echo $PHP_SELF;?>';
  selObj = theForm.frm_baitList;
  var tmpSel_str = '';
  for(var i=1; i<selObj.length; i++){
    if(selObj[i].selected == true){
      if(tmpSel_str != '') tmpSel_str += ',';
      tmpSel_str += selObj[i].value;
    }
  }
  if(tmpSel_str == ''){
    alert('Please select a bait to add from bait list box!');
    return 0;
  }else{
    if(theForm.frm_selected_bait_str.value != '') theForm.frm_selected_bait_str.value += ','
    theForm.frm_selected_bait_str.value += tmpSel_str;
  }
  theForm.theaction.value = 'addBait';
  theForm.submit();
}

function removeBait(){
  theForm = document.form_bait2hits;
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm.action = '<?php echo $PHP_SELF;?>';
  selObj = theForm.frm_selected_bait;
  var tmpSel_arr = theForm.frm_selected_bait_str.value.split(",");
  var atLeaseOne = 0;
  for(var i=1; i<selObj.length; i++){
    if(selObj[i].selected == true){
      atLeaseOne = 1;
      for(var j=0; j<tmpSel_arr.length; j++){
        if(selObj[i].value == tmpSel_arr[j]){
          tmpSel_arr.splice(j, 1);
          break;
        }
      }
    }
  }
  if(atLeaseOne == 1){
    theForm.frm_selected_bait_str.value = tmpSel_arr.join(",");
  }else{
    alert('Please select a bait to remove from the selected bait box!');
    return 0;
  }
  theForm.theaction.value = 'removeBait';
  theForm.submit();
}

function createSelectedBaitStr(theForm){
  var str = '';
  var selObj;
  selObj = theForm.frm_selected_bait;
  for (i=1; i < selObj.options.length; i++) {
    if(selObj.selectedIndex != i){
      if(str.length > 0){
        str = str + ',';
      }
      str = str + selObj.options[i].value;
    }
  }
  theForm.frm_selected_bait_str.value = str;
}

function generateReport(){
  theForm = document.form_bait2hits;
  var selected_obj = theForm.frm_selected_bait;
  if(selected_obj.length <= 1){
    alert("Please pass a bait.");
    return false;
  }
  theForm.action = 'export_bait2hits_process.php';
  theForm.submit();
}
</script>
<FORM ACTION="<?php echo $PHP_SELF;?>" NAME="form_bait2hits" METHOD="POST">
<INPUT TYPE="hidden" NAME="theaction" VALUE="">
<INPUT TYPE="hidden" NAME="frm_selected_bait_str" VALUE="<?php echo $frm_selected_bait_str?>">
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td colspan=2>&nbsp;
    </td>
  </tr>
  <tr>
    <td align="left" colspan=2>
    &nbsp; <font color="navy" face="helvetica,arial,futura" size="4"><b>Export non-filtered Bait-Hits Report</b></font> 
    </td>
  </tr>
  <tr>
    <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
  <tr>
    <td align=center colspan=2><br>
    <table border="0" width="600" height="50" cellspacing="1" cellpadding=3 >
    <tr>
      <td width="41%" BGCOLOR="<?php echo $bg_tb;?>" align=center>
      <font size="2" face="Arial"><b>Bait List</b><br>BaitID GeneName ProteinID<br>
       <select name="frm_baitList" size=20 multiple>
         <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
        <?php 
         $option_str1 = "";
         $option_str2 = "";
         for($i=0; $i<count($Baits); $i++){
           $tmpBaitID = $Baits[$i]['ID'];
           if(!in_array($tmpBaitID, $BaitIDarr_have_hits)) continue;
           $tmp2top = false;
           $initial_str = '';
           if(isset($BaitNotesArr[$tmpBaitID])){
             foreach($BaitNotesArr[$tmpBaitID] as $tmpTypeID){  
              $initial_str .= "[".$bait_group_icon_arr[$tmpTypeID]['Initial']."]";
             }
           }
           if(intval($frm_order_by)>0 and $initial_str){
             if(in_array($frm_order_by, $BaitNotesArr[$tmpBaitID])){
              $tmp2top = true;
               
             }
           }
           if($tmp2top){
              
              $option_str1 .= "<option value='".$Baits[$i]['ID']."'>".$Baits[$i]['ID']."&nbsp; &nbsp;".$Baits[$i]['GeneName']."&nbsp; &nbsp;".$Baits[$i]['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
           }else{
               
              $option_str2 .= "<option value='".$Baits[$i]['ID']."'>".$Baits[$i]['ID']."&nbsp; &nbsp;".$Baits[$i]['GeneName']."&nbsp; &nbsp;".$Baits[$i]['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
           }
         }
         echo $option_str1 . $option_str2;
        ?>
       </select><br><br>
       <b>Sort by:</b>
       <select name="frm_order_by" onChange="changeOrderBy()">
        <option value="ID DESC" <?php echo ($frm_order_by=='ID DESC')?'selected':'';?>>ID</option>
        <option value="GeneName" <?php echo ($frm_order_by=='GeneName')?'selected':'';?>>Gene Name</option>
        <?php 
        foreach($bait_group_icon_arr as $key =>$rd){
          $selected = ($frm_order_by == $key)?" selected":"";
          echo "<option value='".$key."'$selected>".$rd['Name']." (".$rd['Initial'].")</option>\n";
        }
        ?> 
      </select><br><br>
      </td>
      <td width="18%"  BGCOLOR="<?php echo $bg_tb;?>" valign=center>
      <font size="2" face="Arial">
      <center>
      
      <input type=button value='&nbsp;&nbsp;   > >  &nbsp;&nbsp;' onClick="addBait()">
      <br><br>
      <input type=button value='&nbsp;&nbsp;   < <  &nbsp;&nbsp;' onClick="removeBait()">
      </center>
      </font> 
      </td>
      <td width="41%" BGCOLOR="<?php echo $bg_tb;?>" align=center valign=top>
      <table border="0" height="50" cellspacing="1" cellpadding=3 >
        <tr><td align=center>
          <font size="2" face="Arial"><b>Selected baits</b><br>BaitID GeneName ProteinID<br>
          <select name="frm_selected_bait" size=20 multiple>
            <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
            <?php 
             $typeArr = array(); 
             for($i=0; $i<count($SelectedBaits); $i++){
               $tmpBaitID = $SelectedBaits[$i]['ID'];
               $initial_str = '';
               if(isset($BaitNotesArr[$tmpBaitID])){
                 foreach($BaitNotesArr[$tmpBaitID] as $tmpTypeID){  
                  $initial_str .= "[".$bait_group_icon_arr[$tmpTypeID]['Initial']."]";
                 }
               }
               echo "<option value='".$SelectedBaits[$i]['ID']."'>".$SelectedBaits[$i]['ID']."&nbsp; &nbsp;".$SelectedBaits[$i]['GeneName']."&nbsp; &nbsp;".$SelectedBaits[$i]['BaitAcc']."&nbsp;&nbsp;$initial_str\n";
               array_push($typeArr, $SelectedBaits[$i]['ID'].";;".$initial_str);
             }
             $typeStr = implode(",,", $typeArr);
            ?>
            </select>
        </td></tr>
        <tr><td>
          <input type=checkbox name=Pid value='Y' <?php echo ($Pid=='Y')?'checked':''?>>&nbsp;<font size="2" face="Arial"><b>With hit protein ID</b></font>
          <INPUT TYPE="hidden" NAME="typeStr" VALUE="<?php echo $typeStr?>">
          </td></tr>
      </table>
      </td>
    </tr>
    </table><br>
    <input type=button value=' Download ' onClick="generateReport()" class=green_but>
    </td>
  </tr>
</table>
</form>
<?php 
require("site_footer.php");
?>

