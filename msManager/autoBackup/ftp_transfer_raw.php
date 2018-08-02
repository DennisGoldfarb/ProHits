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
$debug = 1;
$theaction = '';
$frm_project_ID = 0;
$hits_db_name = '';
$item_results = '';
$request_arr = array();
$frm_selected_item_str = '';
$frm_order_by = 'BA.ID DESC';
$ItemIDarr_have_hits = array();
$ItemNotesArr = array(); 
$item_group_icon_arr = array();
$SelectedItems = array();
$frm_selected_item_str = '';
$selected_item_results = array();
$frm_remote_username = '';
$frm_remote_ip = '';
$frm_remote_password = '';
$frm_remote_type = '';
$frm_remote_folder = '';
$frm_remote_folder_new = '';
$msg = '';
$error_msg = '';
$frm_type = 'Bait';
$frm_is_all_itmes = 0;

$menu_color = '#669999';
$msg = '';
$can_submitted = false;
$hitDB = '';
$permitted = ''; 
$tmp_batch_file = 'admin_ftp.bat';
 
$tmp_dir = dirname(dirname(dirname(__FILE__)))."/TMP/admin_ftp/";

set_time_limit ( 2400 ) ;
include ( "../../config/conf.inc.php");
include ( "../is_dir_file.inc.php");
include ("shell_functions.inc.php");
set_include_path(get_include_path() . PATH_SEPARATOR . '../../common/phpseclib0.2.2');
include('Net/SFTP.php');

session_start();
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}

/*echo "<pre>";
print_r($request_arr);
echo "</pre>";
$temc=0;*/

$tmpfileNameArr = explode('.',$tmp_batch_file);
$csv_file_name = $tmpfileNameArr[0].'.csv';
$csv_log_file = $tmpfileNameArr[0].'_info.log';
$tmp_log_file = $tmpfileNameArr[0].'.log';
$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$prohits_link  = mysqli_connect("$host", $user, $pswd, PROHITS_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
$msManager_link  = mysqli_connect("$host", $user, $pswd, MANAGER_DB ) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
 
$protein_link  = mysqli_connect("$host", $user, $pswd, PROHITS_PROTEINS_DB) or die("Unable to connect to mysql..." . mysqli_error($protein_link));
 
if(!is_admin($SID)){
  echo "Only user admin has permission to access this page";exit;
}


$SQL = "SELECT ID, Name, DBname FROM Projects order by ID";
$results = mysqli_query($prohits_link, $SQL);
while($row = mysqli_fetch_array($results)){
  mysqli_select_db($prohits_link, $HITS_DB[$row['DBname']]);
  $SQL = "SELECT `ID` FROM `Bait` WHERE `ProjectID`= '".$row['ID']."'  LIMIT 1";
  $tmp_rs = mysqli_query($prohits_link, $SQL);
  if(!mysqli_num_rows($tmp_rs)) continue;
  $pro_access_ID_Names[$row['ID']] = $row['Name'];
  if($frm_project_ID and $frm_project_ID == $row['ID']){
    $hits_db_name = $row['DBname'];
  }
}

$Group_table = $frm_type.'Group';
 
if($frm_type == 'Bait'){
  $ItemID = 'BaitID';
  $type_lable = 'Bait';
}elseif($frm_type == 'Band'){
  $ItemID = 'BandID';
  $type_lable = 'Sample';
} 
 
if($frm_project_ID > 0){
  mysqli_select_db($prohits_link, $HITS_DB[$hits_db_name]);
  
  
//================================================================================
  if(!$frm_is_all_itmes){  
    $SQL = "SELECT $ItemID AS ItemID FROM `Hits` GROUP BY $ItemID ORDER BY $ItemID DESC";
    $tmp_rs = mysqli_query($prohits_link, $SQL);
    while($row = mysqli_fetch_array($tmp_rs)){
      array_push($ItemIDarr_have_hits, $row['ItemID']);
    }
  }  
//============================================================================================  
  $SQL = "SELECT ID FROM $frm_type WHERE ProjectID='$frm_project_ID' ";
  if($frm_selected_item_str){
    $SQL .= "AND ID NOT IN($frm_selected_item_str)";
  }
  $tmp_item_results = mysqli_query($prohits_link, $SQL);
  $this_project_ItemID_arr = array();          
  while($row = mysqli_fetch_array($tmp_item_results)){
    array_push($this_project_ItemID_arr, $row['ID']);
  }
  
//=====================================================================================
  if(!$frm_is_all_itmes){  
    $tmp_arr1 = array_diff($this_project_ItemID_arr, $ItemIDarr_have_hits);
    $tmp_arr2 = array_diff($this_project_ItemID_arr, $tmp_arr1);
  }else{
    $tmp_arr2 = $this_project_ItemID_arr;
  }  
//====================================================================================  
  
  
  $tmp_itme_id_has_hits_str = implode(",", $tmp_arr2);
  if(!$tmp_itme_id_has_hits_str) $tmp_itme_id_has_hits_str = 0;

  if($frm_type == 'Bait'){
    $SQL = "SELECT BA.ID, BA.GeneName, BA.BaitAcc,BA.Clone FROM Bait BA  
            WHERE BA.ID IN($tmp_itme_id_has_hits_str) ";
  }elseif($frm_type == 'Band'){
    $SQL = "SELECT B.ID, BA.ID AS BaitID, BA.GeneName, BA.BaitAcc, BA.Clone FROM Band B
            LEFT JOIN Bait BA ON (B.BaitID=BA.ID)
            WHERE B.ID IN($tmp_itme_id_has_hits_str)";
  }
  if($frm_order_by == 'BA.GeneName' or $frm_order_by == 'BA.BaitAcc'){
    $SQL .= "ORDER BY $frm_order_by";
  }else{
    if($frm_type == 'Bait'){
      $SQL .= "ORDER BY BA.ID DESC";
    }elseif($frm_type == 'Band'){
      $SQL .= "ORDER BY B.ID DESC";
    }  
  }   
  $item_results = mysqli_query($prohits_link, $SQL);
  
  $SQL = "SELECT `NoteTypeID` FROM $Group_table GROUP BY `NoteTypeID`";
  $tmp_results = mysqli_query($prohits_link, $SQL);
  $tmp_id_str = '';
  while($tmp_rd = mysqli_fetch_array($tmp_results)){
    if($tmp_id_str) $tmp_id_str .= ",";
    $tmp_id_str .= $tmp_rd['NoteTypeID'];
  }
  $id_in = '';
  if($tmp_id_str){
    $id_in = " and ID IN ($tmp_id_str)";
  } 
  if($frm_type == 'Band'){
    $tmp_type = " Type = 'Band' OR Type='Export' ";
  }else{
    $tmp_type = " Type = '$frm_type' ";
  }   
  $SQL = "SELECT `ID`, `Name` , `Description` , `Icon` , `UserID` , `Initial` 
        FROM `NoteType` 
        WHERE $tmp_type and `ProjectID`='$frm_project_ID' $id_in order by Type, ID";
  $icon_rd = mysqli_query($prohits_link, $SQL);
 
  $empty_spaces = 0;
  while($rd = mysqli_fetch_array($icon_rd)){
    $item_group_icon_arr[$rd['ID']] = array('Icon'=>$rd['Icon'],'Name'=>$rd['Name'],'Initial'=>$rd['Initial'],'Index'=>$empty_spaces++);
  }
/*echo "<pre>"; 
print_r($item_group_icon_arr); 
echo "<pre>";*/  
}

if($frm_selected_item_str){
  if($frm_type == 'Bait'){
    $SQL = "SELECT BA.ID, BA.GeneName, BA.BaitAcc,BA.Clone FROM Bait BA  
            WHERE BA.ProjectID='$frm_project_ID' 
            AND BA.ID IN($frm_selected_item_str)
            ORDER BY BA.ID";
  }elseif($frm_type == 'Band'){
    $SQL = "SELECT B.ID, BA.ID AS BaitID, BA.GeneName, BA.BaitAcc, BA.Clone FROM Band B
            LEFT JOIN Bait BA ON (B.BaitID=BA.ID)
            WHERE BA.ProjectID='$frm_project_ID' 
            AND B.ID IN($frm_selected_item_str) 
            ORDER BY B.ID";
  }
  $selected_item_results = mysqli_query($prohits_link, $SQL);
}

$SQL = "SELECT `RecordID`, `NoteTypeID` FROM $Group_table WHERE `NoteTypeID`<>'0' order by RecordID, NoteTypeID";
$ItemDis_rs = mysqli_query($prohits_link, $SQL);
while($row = mysqli_fetch_array($ItemDis_rs)){ 
  if(!isset($ItemNotesArr[$row['RecordID']])){
    $ItemNotesArr[$row['RecordID']] = array();
  }
  array_push($ItemNotesArr[$row['RecordID']], $row['NoteTypeID']);
}

//---process actions
$tmp_2_file = '';
if($theaction == 'test_ftp'){
  if(check_connection($frm_remote_username, $frm_remote_password, $frm_remote_ip, $frm_remote_type)){
    if($frm_remote_type =='sftp'){
      $msg = "Prohits doesn't support sftp connection. Please login shell to test it.<br>
              You can use the tool to create a batch file to run it on shell.<br>
              >sftp $frm_remote_username@$frm_remote_ip";
    }else{
      $msg =  "Remote connection is ok";
    }
  }else{ 
      $error_msg = "Couldn't connect the remote site";
  }
}else if($theaction == 'upload_raw_files' and $frm_selected_item_str){
   
  $old_dir = '';
  if(isset($frm_is_all_itmes)){
    if($ItemID=='BandID') $ItemID = 'ID';
    $SQL = "SELECT ID FROM Band WHERE $ItemID IN($frm_selected_item_str) GROUP BY ID";
  }else{
    $SQL = "SELECT BandID FROM Hits WHERE $ItemID IN($frm_selected_item_str) GROUP BY BandID";
  }
  
  $item_results_2 = mysqli_query($prohits_link, $SQL);
  $item_str = '';
  while($row = mysqli_fetch_array($item_results_2)){
    if($item_str) $item_str .= ",";
    $item_str .= $row['0'];
  }
 
  $SQL = "Select B.ID,
          B.BaitID, 
          B.RawFile, 
          BA.GeneID 
          from Band B 
          LEFT JOIN Bait BA ON(B.BaitID=BA.ID)
          where B.ID in($item_str)";
   
  $band_results = mysqli_query($prohits_link, $SQL);
  if($band_results){
    if(!_is_dir($tmp_dir)){
      mkdir ($tmp_dir, 0755, true);
    }
    $csv_handle = fopen($tmp_dir.$csv_file_name, 'w');
    $csv_log_handle = fopen($tmp_dir.$csv_log_file, 'w');
    $csv_line = "Row File,$frm_type ID,Gene Name, Gene ID\r\n";
    fwrite($csv_handle, $csv_line);
    $item_id_array = array();
    $du_array = array();
		$put_file_array = array();
    while($row = mysqli_fetch_array($band_results)){
      if($row['RawFile']){
         
        //$SQL = "SELECT `ID` FROM `Hits` WHERE `BandID`='".$row['ID']."' LIMIT 1";
        //$tmp_results = mysqli_query($prohits_link, $SQL);
        //if(!mysqli_num_rows($tmp_results)) continue;
        $temArr1 = explode(";", $row['RawFile']);
        $tmp_filename_str = '';
        $tmp_counter = 0;
        foreach($temArr1 as $tmpValue1){
          $temArr2 = explode(":", $tmpValue1);
          if($temArr2[0] && $temArr2[1]){
            $tmp_path = getFilePath($temArr2[0], $temArr2[1]);
						
            if(!$tmp_path) continue;
						if(preg_match('/(.+)\.(mgf|mzXML.gz)$/', $tmp_path, $matches)){
     					$theTpe = $matches[2];
							if($theTpe != "RAW"){
								if(_is_file($matches[1].".RAW")){
									$tmp_path = $matches[1].".RAW";
								}
							}
						}
            if(in_array($tmp_path, $put_file_array)) continue;
            array_push($put_file_array, $tmp_path);
            $row_file_name = basename($tmp_path);
            if($tmp_filename_str) $tmp_filename_str .= "|";
            $tmp_filename_str .= $row_file_name;
            $tmp_counter++;                   
            $file_path = $tmp_path;
            if($frm_remote_type == 'sftp'){
              $tmp_2_file .= "put ".$file_path."\n";
            }else if($frm_remote_type == 'ftp'){
              if(preg_match("/(.+)\/(.+)$/", $file_path, $matchs)){
                if($old_dir !=$matchs[1]){
                  $tmp_2_file .= "lcd ".$matchs[1]."\n";
                  $old_dir = $matchs[1];
                }
								
                $tmp_2_file .= "put ".$matchs[2]."\n";
              }
            }
          }
        }
				
        if(!$tmp_filename_str) continue;
        $SQL = "SELECT `GeneName` FROM `Protein_Class` WHERE `EntrezGeneID`='".$row['GeneID']."'";
        $protein_results = mysqli_query($protein_link, $SQL);
        $gene_name = '';
        if($protein_row = mysqli_fetch_array($protein_results)){
          $gene_name = $protein_row['GeneName'];
        }
        if($frm_type == 'Bait'){
          $id_index = 'BaitID';
        }else{
          $id_index = 'ID';
        }  
        
        $tmp_name_arr = explode('|',$tmp_filename_str);
        foreach($tmp_name_arr as $value){
          $csv_line = $value.",".$row[$id_index].",".$gene_name.",".$row['GeneID']."\r\n";
          fwrite($csv_handle, $csv_line);
        }  
        if(!array_key_exists($row[$id_index], $item_id_array)){
          $item_id_array[$row[$id_index]] = $tmp_filename_str;
          if($tmp_counter > 1){
            $du_array[$row[$id_index]] = $tmp_filename_str;
          }
        }else{
          if(!array_key_exists($row[$id_index], $du_array)){
            $du_array[$row[$id_index]] = $item_id_array[$row['BaitID']]."|".$tmp_filename_str;
          }else{
            $du_array[$row[$id_index]] .= "|".$tmp_filename_str;
          }
        }
      }
    } 
    foreach($du_array as $key => $value){
      $csv_log_line = $key.": ".$value."\r\n";
      fwrite($csv_log_handle, $csv_log_line);
    }
    if($tmp_2_file){
      $tmp_2_file .= "put ". $tmp_dir.$csv_file_name."\n";
      $handle = fopen ($tmp_dir.$tmp_batch_file, 'w');
      fwrite ($handle, "#cd $tmp_dir\n");
      if($frm_remote_type == 'sftp'){ 
        $tmp_str = " sftp -o \"BatchMode no\" -b $tmp_batch_file $frm_remote_username@$frm_remote_ip 1>>$tmp_log_file 2>&1\n";
        fwrite ($handle, "#". $tmp_str); 
      }else if($frm_remote_type == 'ftp'){
        $tmp_str = " ftp -n $frm_remote_ip < $tmp_batch_file 1>>$tmp_log_file 2>&1\n";
        fwrite ($handle, "#". $tmp_str); 
        fwrite ($handle, "user ".$frm_remote_username." ".$frm_remote_password."\nprompt\nbinary\n");
      }
      
      if($frm_remote_folder){
         if($frm_remote_folder_new) fwrite ($handle, "mkdir $frm_remote_folder\n");
         fwrite ($handle, "cd $frm_remote_folder\n");
      }
      fwrite ($handle, $tmp_2_file);
      fwrite ($handle, "quit\n"); 
      fclose ($handle); 
      $msg = "A shell batch file has been created (<font color=#FF0000>$tmp_dir$tmp_batch_file</font>)<br>
			        1. Log in Prohits storage shell:<br>
							> <font color=red>ssh userName@".STORAGE_IP."</font><br>
							2. Change working dir:<br>
							> <font color=red>cd $tmp_dir</font><br>
							3. Run command:<br>
              > <font color=#FF0000>$tmp_str</font><br>
              Please click the 'View Log File' button if there is any error, after runing the command.<br>";
      if($frm_remote_folder_new){
        $msg .= "If the folder cannot be created please manually  create it than <br>
        uncheck the 'is new folder' to create the batch file";
      }
    }else{
      $error_msg = "No raw file found";
    }
  }
}
//echo @date('h-i-s'); 
////////////////////////////////////////////////////////////////////
//exit;
    
?>
<html>
<body>
<script language="javascript"> 

function changeProject(theForm){
  obj = theForm.frm_project_ID;
  if(obj.options[obj.selectedIndex].value == '-1'){
    alert('Please select a Project!');
    return false;
  }
  theForm.frm_selected_item_str.value = '';
  theForm.submit();
}
function uploadFiles(theForm){
  if(theForm.frm_selected_item_str.value == ''){
    alert("Please select items.");
    return false;
  }else if(check_form(theForm)){
    theForm.theaction.value = 'upload_raw_files';
    theForm.submit();
  }
}
function submitIT(theForm){
  theForm.submit();
}
function testFTP(theForm){
  if(check_form(theForm)){
    theForm.theaction.value = 'test_ftp';
    submitIT(theForm);
  }
}
function check_form(theForm){
  var sel = theForm.frm_remote_type;
  if(theForm.frm_remote_ip.vale == '' || theForm.frm_remote_username.value == '' 
  || theForm.frm_remote_password.value == '' || sel.selectedIndex == 0){
    alert('Please type ftp address, user name, password and conenction type!');
    return false;
  }else{
    return true;
  }
}
function addItem(theForm){
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  selObj = theForm.frm_itemList;
  var tmpSel_str = '';
  for(var i=1; i<selObj.length; i++){
    if(selObj[i].selected == true){
      if(tmpSel_str != '') tmpSel_str += ',';
      tmpSel_str += selObj[i].value;
    }
  }
  if(tmpSel_str == ''){
    alert('Please select a item to add from item list box!');
    return 0;
  }else{
    if(theForm.frm_selected_item_str.value != '') theForm.frm_selected_item_str.value += ','
    theForm.frm_selected_item_str.value += tmpSel_str;
  }
  theForm.theaction.value = 'addItem';
  theForm.submit();
}

function removeItem(theForm){
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
  selObj = theForm.frm_selected_item;
  var tmpSel_arr = theForm.frm_selected_item_str.value.split(",");
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
    theForm.frm_selected_item_str.value = tmpSel_arr.join(",");
  }else{
    alert('Please select a item to remove from the selected item box!');
    return 0;
  }
  theForm.theaction.value = 'removeItem';
  theForm.submit();
}

function createSelectedItemStr(theForm){
  var str = '';
  var selObj;
  selObj = theForm.frm_selected_item;
  for (i=1; i < selObj.options.length; i++) {
    if(selObj.selectedIndex != i){
      if(str.length > 0){
        str = str + ',';
      }
      str = str + selObj.options[i].value;
    }
  }
  theForm.frm_selected_item_str.value = str;
}
function change_type(theForm){
  theForm.frm_selected_item_str.value = '';
  theForm.submit();
}
function is_all_item(theForm){
  theForm.frm_selected_item_str.value = '';
  theForm.submit();
}
</script>
<h2>Upload raw files to remote ftp site</h2>
<form name=editform method=post action='<?php echo $_SERVER['PHP_SELF'];?>'>  
<INPUT TYPE="hidden" NAME="frm_selected_item_str" VALUE="<?php echo $frm_selected_item_str?>">
<input type=hidden name=SID value='<?php echo $SID;?>'>
<input type=hidden name=theaction value=''>
<table border="0" width="700" height="50" cellspacing="2" cellpadding=3 bgcolor=>
<tr>
	<td colspan=4  bgcolor=#99ccff><b>Remote FTP Address</b> (IP or domain name):
	<input type=text name=frm_remote_ip value='<?php echo $frm_remote_ip;?>' size=35>
  <input type=button value='test connection' onClick='testFTP(this.form)'>
  </td>
</tr>
<tr>
	<td bgcolor=#99ccff><b>User Name</b>:</td>
	<td bgcolor=#99ccff><input type=text name=frm_remote_username value=<?php echo $frm_remote_username;?>></td>
	<td bgcolor=#99ccff><b>Password</b>:</td>
  <td bgcolor=#99ccff><input type=password name=frm_remote_password value=<?php echo $frm_remote_password;?>></td>
</tr>
<tr>
  <td bgcolor=#99ccff><b>Connection Protocol</b></td>
  <td bgcolor=#99ccff>
  <select name=frm_remote_type>
    <option value=''>
    <option value=ftp<?php echo ($frm_remote_type=='ftp')?' selected':'';?>>ftp
    <option value=sftp<?php echo ($frm_remote_type=='sftp')?' selected':'';?>>sftp
  </select>
  </td>
  <td bgcolor=#99ccff><b>Upload to Folder</b></td>
  <td bgcolor=#99ccff><input type=text name=frm_remote_folder size=10 value='<?php echo $frm_remote_folder;?>'>
   is new<input type=checkbox name=frm_remote_folder_new <?php echo ($frm_remote_folder_new)?'checked':'';?> value='1'>
  </td>
</tr>
<tr>
  <td bgcolor=#99ccff><b>Batch File Name:</b></td>
  <td bgcolor=#99ccff colspan=3>
   <input type=text name=tmp_batch_file value='<?php echo $tmp_batch_file;?>'>
  </td>
 
</tr>
</table> 
<?php 

if($msg){
  echo "<font color='#008000'><b>$msg</b></font>\n";
}else if($error_msg){
  echo "<font color='#FF0000'><b>$error_msg</b></font>\n";;
}else{
  echo "<br>";
}

?>
<table border="0" width="700" cellspacing=2 cellpadding=3 bgcolor=>
    <tr>
      <td bgcolor=#cbcbcb><b>Project: </b>
      <select name="frm_project_ID"  onChange="changeProject(this.form);">
      <option value='-1'>-- select project --
      <?php 
      foreach($pro_access_ID_Names as $tmp_pro_ID=>$tmp_pro_name){
        $selected = ($tmp_pro_ID == $frm_project_ID)? " selected": "";
        echo "  <option value='$tmp_pro_ID' $selected>($tmp_pro_ID) $tmp_pro_name\n"; 
      }
      ?>
      </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <?php if($frm_project_ID){?>
    <b>Type: </b>
      &nbsp;&nbsp;Bait<input type=radio name='frm_type' value='Bait' <?php echo (($frm_type == 'Bait')?'checked':'')?> onclick="change_type(this.form);">&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;Sample<input type=radio name='frm_type' value='Band' <?php echo (($frm_type == 'Band')?'checked':'')?> onclick="change_type(this.form);">
    <?php }?>
    </td>
   </tr>
</table>   
<table border="0" width="700" height="50" cellspacing="2" cellpadding=3 bgcolor=>   
   <tr>
<?php  
  if($item_results){
?>
   <tr>
      <td bgcolor=#cbcbcb align=center><b><?php echo $type_lable?> List</b><br>
      <select name="frm_itemList" size=20 multiple>
         <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
      <?php 
      $option_str1 = "";
      $option_str2 = "";
     
      while($row = mysqli_fetch_array($item_results)){
        $tmpItemID = $row['ID'];
        $tmp2top = false;
        $initial_str = '';
        if(array_key_exists($tmpItemID, $ItemNotesArr)){
          foreach($ItemNotesArr[$tmpItemID] as $tmpTypeID){
            if(array_key_exists($tmpTypeID, $item_group_icon_arr)){
              $v = '';
              if(is_numeric($item_group_icon_arr[$tmpTypeID]['Initial'])) $v = 'VS';
              $initial_str .= "[".$v.$item_group_icon_arr[$tmpTypeID]['Initial']."]";
            }
          }
        }
        //$temc++; 
        if(intval($frm_order_by)>0 and $initial_str){
          if(in_array($frm_order_by, $ItemNotesArr[$tmpItemID])){
            $tmp2top = true;
          }
        }
        if($tmp2top){
          $option_str1 .= "<option value='".$row['ID']."'>".$row['ID']."&nbsp; &nbsp;".$row['GeneName']."&nbsp; &nbsp;".$row['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
        }else{
          $option_str2 .= "<option value='".$row['ID']."'>".$row['ID']."&nbsp; &nbsp;".$row['GeneName']."&nbsp; &nbsp;".$row['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
        }
      }
      
      echo $option_str1 . $option_str2;
      ?>
      </select> 
      <br><br>
      <b>Include no hit bait:</b>
      <input type=checkbox name=frm_is_all_itmes <?php echo ($frm_is_all_itmes)?'checked':'';?> value='1' onclick="javascript: is_all_item(this.form);">
      <br>
      <b>Sort by:</b>
      <select name="frm_order_by" onChange="submitIT(this.form)">
        <option value="ID" <?php echo ($frm_order_by=='BA.ID DESC' || $frm_order_by=='B.ID DESC')?'selected':'';?>>ID</option>
        <option value="BA.GeneName" <?php echo ($frm_order_by=='BA.GeneName')?'selected':'';?>>Gene Name</option>
        <?php 
        foreach($item_group_icon_arr as $key =>$rd){
          $selected = ($frm_order_by == $key)?" selected":"";
          $v = '';
          if(is_numeric($item_group_icon_arr[$key]['Initial'])) $v = 'VS';          
          echo "<option value='".$key."'$selected>".$rd['Name']." (".$v.$rd['Initial'].")</option>\n";
        }
        ?> 
      </select><br><br>
     </td>
     <td valign=center  bgcolor=#cbcbcb>
      <font size="2" face="Arial">
      <center>
      
      <input type=button value='&nbsp;&nbsp;   > >  &nbsp;&nbsp;' onClick="addItem(this.form)">
      <br><br>
      <input type=button value='&nbsp;&nbsp;   < <  &nbsp;&nbsp;' onClick="removeItem(this.form)">
      </center>
      </font> 
      </td>
      <td valign=top align=center  bgcolor=#cbcbcb>
     <b>Selected <?php echo $type_lable?> List</b><br>
          <select name="frm_selected_item" size=20 multiple>
            <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
            <?php 
             $typeArr = array(); 
             //$selected_item_results
             if($selected_item_results){
             while($SelectedItems = mysqli_fetch_array($selected_item_results)){
               $tmpItemID = $SelectedItems['ID'];
               $initial_str = '';
               if(isset($ItemNotesArr[$tmpItemID])){
                 foreach($ItemNotesArr[$tmpItemID] as $tmpTypeID){
                  if(isset($item_group_icon_arr[$tmpTypeID])){
                    $v = '';
                    if(is_numeric($item_group_icon_arr[$tmpTypeID]['Initial'])) $v = 'VS';
                    $initial_str .= "[".$v.$item_group_icon_arr[$tmpTypeID]['Initial']."]";
                    //$initial_str .= "[".$item_group_icon_arr[$tmpTypeID]['Initial']."]";
                  }  
                 }
               }
               echo "<option value='".$SelectedItems['ID']."'>".$SelectedItems['ID']."&nbsp; &nbsp;".$SelectedItems['GeneName']."&nbsp; &nbsp;".$SelectedItems['BaitAcc']."&nbsp;&nbsp;$initial_str\n";
               array_push($typeArr, $SelectedItems['ID'].";;".$initial_str);
             }
             $typeStr = implode(",,", $typeArr);
             }
            ?>
            </select>
    </td>
 <tr>
 <td colspan=3 align=center>
 <?php if($theaction == "upload_raw_files" && $tmp_2_file){?>
    <input type=button value='View Log File' onClick="window.open('<?php echo $tmp_dir.$csv_log_file;?>')">
 <?php }?>
    <input type=button value='Close Window' onClick="window.close()" >
    <input type=button value='Create Batch File' onClick="uploadFiles(this.form)" >
 </td>
</tr>
<?php }
//echo "\temc=$temc"
?>
</table>
</form>
</body>
</html>
<?php 
function _check_connection($username = '', $password ='', $ip ='', $type ='ftp'){
  $rt = '';
  if($type == 'ftp'){
    if(!$conn_id = ftp_connect($ip)){
      return $rt;
    }
    if (@ftp_login($conn_id, $username, $password)) {
      $rt = true;
    }
    ftp_close($conn_id);
  }else if($type == 'sftp'){
     $rt = true;
  }
  return $rt;
}
//===============================================
function is_admin($SID){
//=============================================== 
  global $prohits_link;  
  $SQL = "SELECT U.ID, U.Type FROM Session S, User U WHERE U.ID=S.UserID and S.SID = '$SID'";
  $result = mysqli_query($prohits_link, $SQL);
  if($row = mysqli_fetch_row($result) ){
    if($row[1] == 'Admin')  return true;
  }
  return false;
}

?>