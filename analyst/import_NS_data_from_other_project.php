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

$theaction = '';
$order_by = '';
$GeneID = '';
$msg = '';
$frm_BaitGeneID = '';
$frm_BaitORF = '';
$frm_BaitGene = '';
$frm_TaxID = '';
$frm_NS_group_id = '';
$option = 0;

//-------------------------
$frm_save_type = '';
$frm_group_name_s = '';
$frm_group_name_t = '';
$frm_other_project = '';
$frm_other_NS = '';
$DB_index = '';
$script = "import_NS_data_from_other_project.php";
// --------------------------
require("../common/site_permission.inc.php");
require("common/common_fun.inc.php");
require("analyst/common_functions.inc.php");
require_once("msManager/is_dir_file.inc.php");

$NS_Dir = STORAGE_FOLDER."Prohits_Data/Non_Specific/";
$NS_data_dir = $NS_Dir."NS_data/";

if(!_is_dir($NS_data_dir)) _mkdir_path($NS_data_dir);

$projectID_DBname_arr = get_projectID_DBname_pair($PROHITSDB);
 
$HITS_DB_obj_arr = array();              
foreach($HITS_DB as $key=>$DB_name_val){
  $HITS_DB_obj_arr[$key] = new mysqlDB($DB_name_val);
}
 
$NSarr_others = array();
$other_NS_id_fname_array = array();
if($frm_other_project){
  $DB_index = $projectID_DBname_arr[$frm_other_project];
   
  //print_r($projectID_DBname_arr);
  $HITS_DB_obj = new mysqlDB($HITS_DB[$DB_index]);
  $SQL = "SELECT `ID`,`Name`,`FileName`,`UserID`,`Date` FROM `ExpBackGroundSet` WHERE `ProjectID`='$frm_other_project'";
  $NSarr_others = $HITS_DB_obj->fetchAll($SQL);
  foreach($NSarr_others as $others_val){
    $other_NS_id_fname_array[$others_val['ID']] = $others_val['FileName'];
  }
}

if($theaction == "insert"){
  if($frm_save_type == "new"){
    $SQL = "INSERT INTO `ExpBackGroundSet` SET 
            `Name`='$frm_group_name_t',
            `ProjectID`='$AccessProjectID',
            `UserID`='$AccessUserID',
            `Date`='".@date("Y-m-d")."'";
    if(!$frm_group_name_s = $HITSDB->insert($SQL)){
      echo "db insert problem";
      exit;
    }
    $fileName = "P".$AccessProjectID."_G".$frm_group_name_s."_".$frm_group_name_t.".txt";
    $SQL = "UPDATE `ExpBackGroundSet` SET
           `FileName`='$fileName'
           WHERE ID= '$frm_group_name_s'";
    if(!$ret = $HITSDB->execute($SQL)){
      echo "db update problem";
      exit;
    }
  }else{
    $SQL = "SELECT `ID`,`FileName` FROM `ExpBackGroundSet` WHERE `ID`='$frm_group_name_s'";
    if(!$tmpArr = $HITSDB->fetch($SQL)){
      echo "db fetch problem";
      exit;
    }
    $fileName = $tmpArr['FileName'];
    $frm_group_name_s = $tmpArr['ID'];
  }  
  $dataFileFullName = $NS_data_dir.$fileName;
  $Append_file_name = $other_NS_id_fname_array[$frm_NS_group_id];
  $Append_file_Full_name = $NS_data_dir.$Append_file_name;

  if($frm_save_type != "new"){
    $tmpStr = file_get_contents($dataFileFullName);
    $tmpStr = trim($tmpStr);
    $tmpStr_2 = file_get_contents($Append_file_Full_name);
    $tmpStr_2 = trim($tmpStr_2);
    if($tmpStr && $tmpStr_2){
      $tmpArr = explode(',',$tmpStr);
      $tmpArr_2 = explode(',',$tmpStr_2);
      $merged_arr = array_merge($tmpArr, $tmpArr_2);
      $new_arr = array_unique($merged_arr);
      $new_str = implode(",",$new_arr);
      if(!$NS_data_handle = fopen($dataFileFullName, "w")){
        echo "Cannot open file $new_full_file_name";
        exit;
      }
      fwrite($NS_data_handle, $new_str);
    }elseif($tmpStr_2){
      if(!copy($Append_file_Full_name, $dataFileFullName)){
        echo "failed to copy $file...\n";
        exit;
      }
    }
  }else{
    if(!copy($Append_file_Full_name, $dataFileFullName)){
      echo "failed to copy $file...\n";
      exit;
    }
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Untitled</title>
</head>
<script language=javascript>
function passvalue(){
  theFile = "./mng_set_non_specific.php?order_by=GeneName&filterID=12&frm_TaxID=9606&frm_NS_group_id=<?php echo $frm_group_name_s?>";
  opener.window.open(theFile,"_top");
  window.close();
}    
</script>  
<body onload="passvalue();">
</body>
</html>
<?php 
  exit;
}

if(!$order_by) $order_by = "GeneName";

$bgcolor = "#f3eee2";
$bgcolordark = "#9d9d9d";

$user_id_name_arr = get_users_ID_Name($PROHITSDB);
$project_id_name_arr = get_project_id_name_arr();

$exist_user_accessed_projects_arr = array();

$SQL = "SELECT `ProjectID` FROM `ProPermission` WHERE `UserID`='$AccessUserID'";
$tmp_ProPermission_arr = $PROHITSDB->fetchAll($SQL);
if($tmp_ProPermission_arr){
  $user_accessed_projects_arr = array();
  foreach($tmp_ProPermission_arr as $tmp_ProPermission_val){
    if($tmp_ProPermission_val['ProjectID'] == $AccessProjectID) continue;
    array_push($user_accessed_projects_arr, $tmp_ProPermission_val['ProjectID']);
  }
  $user_accessed_projects_str = implode(",", $user_accessed_projects_arr);
  $SQL = "SELECT `ProjectID`
          FROM `ExpBackGroundSet` 
          WHERE `ProjectID` IN ($user_accessed_projects_str)
          GROUP BY `ProjectID`";
  foreach($HITS_DB_obj_arr as $DB_link){
    $tmp_arr = $DB_link->fetchAll($SQL);
    foreach($tmp_arr as $tmp_val){
      array_push($exist_user_accessed_projects_arr, $tmp_val['ProjectID']);
    }
  }
}
$SQL = "SELECT `ID`,`Name`,`FileName`,`UserID`,`Date` FROM `ExpBackGroundSet` WHERE `ProjectID`='$AccessProjectID'";
$NSarr_this = $HITSDB->fetchAll($SQL); 
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
  <link rel="stylesheet" type="text/css" href="./site_style.css">
  <!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
  <script language="Javascript" src="../common/javascript/site_javascript.js"></script>
  
<script src="../common/javascript/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="../common/javascript/prohits.tooltip.js" type="text/javascript"></script>
<script src="../common/javascript/jquery-ui.js" type="text/javascript"></script>
<link rel="stylesheet" href="../common/javascript/jquery-ui.css" type="text/css">
<link rel="stylesheet" href="../common/javascript/prohits.tooltip.css" type="text/css">

</head>
<BODY onload='dis_item();'>
<script language=javascript>

var groupNameArr = new Array();
<?php 
  $j = 0;
  foreach($NSarr_this as $value){
?>
    groupNameArr[<?php echo $j++?>] = '<?php echo $value['Name']?>';
<?php }?>
function dis_item(){
  var theForm = document.NS_form;
  var radio_obj = theForm.frm_save_type;
  if(radio_obj[0].checked == true){
    theForm.frm_group_name_t.disabled = false;
    theForm.frm_group_name_s.disabled=true;
  }else if(radio_obj[1].checked == true){
    theForm.frm_group_name_t.disabled = true;
    theForm.frm_group_name_s.disabled = false;
  }
}
function change_object(){
  var theForm = document.NS_form;
  theForm.theaction.value = 'change_object';
  theForm.submit();
}

function change_group(){
  var theForm = document.NS_form;
  theForm.theaction.value = 'change_group';
  theForm.submit();
}

function process_file(){
  var theForm = document.NS_form;
  var radio_obj = theForm.frm_save_type;
  if(radio_obj[0].checked == false && radio_obj[1].checked == false){
    alert('Please select [Add as new] or [Append to existing].');
    return false;
  }else if(radio_obj[0].checked == true){
    if(!onlyAlphaNumerics(theForm.frm_group_name_t.value, 7)){
      alert("Only characters \"%+-_A-Za-z0-9\(\)\.:\" and spaces are allowed.");
      return false;
    }
    for(var i=0; i<groupNameArr.length; i++){
      if(groupNameArr[i] == theForm.frm_group_name_t.value){
        alert("The set name is already exist. Please give another name.");
        return false;
      }
    }
  }else if(radio_obj[1].checked == true){
    if(theForm.frm_group_name_s.value == ''){
      alert('Please select a existing group.');
      return false;
    }
  }
  theForm.theaction.value = 'insert';
  theForm.submit();
}
</script>
<center>
<form id="NS_form" name="NS_form" method=post action="<?php echo $PHP_SELF;?>">
<input type=hidden name=theaction value=''>
<input type=hidden name=DB_index value='<?php echo $DB_index?>'>
<table border=0 width=100% cellspacing="1" cellpadding=0 bgcolor='#a0a7c5' width=100%>
  <tr>
    <td valign=top align=center bgcolor="white" width=100%>
    <table border="0" cellpadding="0" cellspacing="0" width="90%">
      <tr> 
      <td><br><span class=pop_header_text>Non-specific (background)</span>&nbsp;&nbsp;import from other project<br><hr size=1></td>
      </tr>
      <tr>
	      <td align="center" valign=top nowrap width="100%">
        
        <table border="0" cellpadding="1" cellspacing="1" width="100%" bgcolor="white">
  	      <tr bgcolor="<?php echo $bgcolor;?>">
            <td>
              <div class=middle_bold>Projects:&nbsp;</div>
            </td>
            <td>                  
              <select name="frm_other_project" onchange="javascript: change_object();">
                <option value="">----Select a project----<br>
                <?php 
                  foreach($exist_user_accessed_projects_arr as $value){
                    echo"<option value='".$value."' ".(($frm_other_project==$value)?'selected':'').">".$project_id_name_arr[$value];
                  }
                ?>  
              </select>
            </td>
            <td>
              <div class=middle_bold>Non-specific Set:</div>
            </td>
            <td>               
              <select name="frm_NS_group_id" onchange="javascript: change_group();">
                <option value="">----Select a group----<br>
                <?php 
                  foreach($NSarr_others as $value){
                    if(!_is_file($NS_data_dir."/".$value['FileName'])) continue;
                    echo"<option value='".$value['ID']."' ".(($frm_NS_group_id==$value['ID'])?'selected':'').">".$value['Name'];
                  }
                ?>  
              </select>
            </td>
          </tr>
          <tr bgcolor="<?php echo $bgcolor;?>">
            <td valign=top nowrap rowspan=2><div class=middle_bold>Set Name: </b></div></td>
            <td colspan=3>
              <div class=middle>
              <input type='radio' name='frm_save_type' value="new" <?php echo ($frm_save_type=="new")?'checked':''?> onClick="javascript: dis_item();">Add as new<?php echo  str_repeat("&nbsp;",15);?>
              <input type='text' name='frm_group_name_t' size="39" maxlength="50" value="<?php echo $frm_group_name_t?>" >
              </div>
            </td>
          </tr>
          <tr bgcolor="<?php echo $bgcolor;?>">
            <td colspan=3>
              <div class=middle>
              <input type='radio' name='frm_save_type' value="appand" <?php echo ($frm_save_type=="appand")?'checked':''?> onClick="javascript: dis_item();">Append to existing</font>&nbsp;&nbsp;&nbsp;  
              <select name="frm_group_name_s">
                <option value="">----Select a group----<br>
                <?php 
                  foreach($NSarr_this as $value){
                    if(!_is_file($NS_data_dir."/".$value['FileName'])) continue;
                    echo"<option value='".$value['ID']."' ".(($frm_group_name_s==$value['ID'])?'selected':'').">".$value['Name'];
                  }
                ?>  
              </select>
              </div>
            </td>
          </tr>
 <?php if($theaction == 'change_group' && $frm_NS_group_id){;?>         
          <tr bgcolor="<?php echo $bgcolor;?>">
            <td colspan=5 align=right>
              <input type="button" value=" Import " onClick="javascript: process_file()">
            </td>
          </tr>
          
          <tr><td>&nbsp;</td></tr>
          
  <tr>
    <td align="center" valign=top colspan=6>
    <DIV STYLE="display: block;border: #a0a7c5 solid 1px">
      <table border="0" cellpadding="0" cellspacing="1" width="100%">
      <tr bgcolor="">
    	  <td width="" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
        <div class=tableheader>GeneID</div>
    	  </td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center onwrap>
        <div class=tableheader>GeneName</div>
    	  </td>
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center nowrap>
        <div class=tableheader>Gene Alias</div>
    	  </td>
    	  <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center>
    	    <div class=tableheader>Links</div> 
    	  </td>
    <?php if($USER->Type == "Admin" && $theaction != 'addnew' && $script != "import_NS_data_from_other_project.php"){
        $option = 1;
    ?>    
        <td width="" bgcolor="<?php echo $bgcolordark;?>" align=center>
    	    <div class=tableheader>Option</div> 
    	  </td>
    <?php }?>    
    	</tr>
<?php 
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
get_NS_geneID_other_project($NSfilteIDarr,$frm_NS_group_id,$DB_index);
$NSlistAarr  = array_unique($NSfilteIDarr);
$genePropertyArr = array();
$ENSgenePropertyArr = array();
get_gene_property($genePropertyArr,$ENSgenePropertyArr,$NSlistAarr,'value');
$geneArr = array();
$ENSgeneArr = array();
$indexArr = array();
sort_print_filter_table($genePropertyArr,$ENSgenePropertyArr,$order_by,$option);
}
?> 
   
    </table>
    </DIV>
    </form><br>
 </td>
 </tr>
</table> 
</td></tr>
</table>
<a href="javascript: window.close();" class=button>[Close Window]</a>   
</body>
</html>
<?php 
function get_NS_geneID_other_project(&$NSfilteIDarr,$groupID,$DB_index){
  if(!$groupID) return;
  global $HITS_DB;
  global $HITS_DB_obj_arr;
  global $NS_data_dir;
  $tmpGroupArr = array();
  $SQL = "SELECT `ID`, `FileName` FROM `ExpBackGroundSet` WHERE `ID`='$groupID'";
  $NSarr = $HITS_DB_obj_arr[$DB_index]->fetch($SQL); 
  
  if($NSarr['FileName']){
    $NSfileFullName = $NS_data_dir.$NSarr['FileName'];
    $NSgeneIDstr = @trim(file_get_contents($NSfileFullName));
    $tmpArr = explode(",",$NSgeneIDstr);
    $NSfilteIDarr = $tmpArr;
  }
}
?>