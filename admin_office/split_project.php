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

//only work for 'Admin' with page permission

$theaction = '';
$frm_project_ID = 0;
$hits_db_name = '';
$bait_results = '';
$request_arr = array();
$frm_selected_bait_str = '';
$frm_order_by = 'ID DESC';
$baitIDarr_have_hits = array();
$baitNotesArr = array(); 
$bait_group_icon_arr = array();
$Selectedbaits = array();
$frm_selected_bait_str = '';
$selected_bait_results = array();

$msg = '';
$error_msg = '';
$frm_type = 'Bait';
$frm_is_all_baits = 0;
$frm_select_group = '';

$menu_color = '#669999';
$msg = '';
$can_submitted = false;
$hitDB = '';
$permitted = '';  

set_time_limit ( 2400 ) ; 

require("../common/site_permission.inc.php");
/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$host = HOSTNAME;
$user = USERNAME;
$pswd = DBPASSWORD;
$prohits_link  = mysqli_connect("$host", $user, $pswd, PROHITS_DB) or die("Unable to connect to mysql..." . mysqli_error($prohits_link));

$msManager_link  = mysqli_connect("$host", $user, $pswd, MANAGER_DB) or die("Unable to connect to mysql..." . mysqli_error($msManager_link));
 
if($USER->Type != 'Admin'){
  echo "Only Prohits admin has permission to access this page";exit;
}

$SQL = "SELECT ID, Name, DBname FROM Projects order by ID";
$results = mysqli_query($prohits_link, $SQL);
while($row = mysqli_fetch_assoc($results)){
  $pro_access_ID_Names[$row['ID']] = $row['Name'];
  $pro_access_ID_database_Names[$row['ID']] = $row['DBname'];
  if($frm_project_ID and $frm_project_ID == $row['ID']){
    $hits_db_name = $row['DBname'];
  }
}

if($theaction == 'process' and $frm_project_ID){
  mysqli_select_db($prohits_link, $HITS_DB[$hits_db_name]);
  $HITSDB->change_db($HITS_DB[$hits_db_name]);

  if(!$frm_to_project_ID){
     $error_msg = "Where do you want to move baits?";
  }else if(!$frm_selected_bait_str){
    $error_msg = "Please select baits?";
  }else{
    $to_file =  "Move Baits from Project: <b>($frm_project_ID)" . $pro_access_ID_Names[$frm_project_ID] . "</b>
     to Project: <b>($frm_to_project_ID)". $pro_access_ID_Names[$frm_to_project_ID]."</b>
     Selected Bait IDs: <b>$frm_selected_bait_str</b>";
    $log_path = '../logs/split_project_'. $USER->Username. @date("Y-m-d").".txt";
    
//=========Process Experimental Editor==================================================================================================
    $to_file .= "\n\nProcess Experimental Editor-------------------";
    $SQL = "SELECT N.ID,
                   N.ParentID, 
                   N.Name,
                   N.UserID,
                   P.ProjectID 
            FROM ExpDetailName N
            LEFT JOIN ExpDetailProject P
            ON (P.SelectionID=N.ID)
            WHERE `ProjectID`='$frm_project_ID'";
    $tmp_Parent_arr = $HITSDB->fetchAll($SQL);

    $from_arr = array();
    foreach($tmp_Parent_arr as $tmp_Parent_val){
      $from_arr[$tmp_Parent_val['Name']] = $tmp_Parent_val;
    }
    $SQL = "SELECT N.ID,
                   N.ParentID, 
                   N.Name,
                   N.UserID,
                   P.ProjectID 
            FROM ExpDetailName N
            LEFT JOIN ExpDetailProject P
            ON (P.SelectionID=N.ID)
            WHERE `ProjectID`='$frm_to_project_ID'";
    $tmp_Parent_to_arr = $HITSDB->fetchAll($SQL);

    $to_arr = array();
    foreach($tmp_Parent_to_arr as $tmp_Parent_to_val){
      $to_arr[$tmp_Parent_to_val['Name']] = $tmp_Parent_to_val;
    }
    $old_new_selectionID_arr = array();
    foreach($from_arr as $key => $val){
      if(array_key_exists($key, $to_arr)){
        $old_new_selectionID_arr[$val['ID']] = $to_arr[$key]['ID'];
      }else{
        $SQL = "INSERT INTO `ExpDetailName` 
                SET ParentID=0, 
                Name='".$val['Name']."', 
                UserID='".$val['UserID']."', 
                DT='".@date('Y-m-j')."'";
        $ParID = $HITSDB->insert($SQL);
        $to_file .= "\n$SQL";
        if($ParID){
          $old_new_selectionID_arr[$val['ID']] = $ParID;
          $SQL = "INSERT INTO `ExpDetailProject`
                  SET SelectionID=$ParID,
                  ProjectID='$frm_to_project_ID', 
                  UserID='".$val['UserID']."', 
                  DT='".@date('Y-m-j')."'";
          $HITSDB->insert($SQL);
          $to_file .= "\n$SQL";
        }
      }
    }
    
    $selectionID_str = '';
    foreach($old_new_selectionID_arr as $key => $val){
      if($selectionID_str) $selectionID_str .= ',';
      $selectionID_str .= $key;
    }

    $old_new_optionID_arr = array();
    if($selectionID_str){
      $SQL = "SELECT `ID`, `ParentID`, `Name`, `UserID`, `DT` FROM `ExpDetailName` WHERE `ParentID` IN ($selectionID_str)";
      $tmp_ExpDetailName_arr = $HITSDB->fetchAll($SQL);
      foreach($tmp_ExpDetailName_arr as $val){
        $new_parent_id = $old_new_selectionID_arr[$val['ParentID']];
        $new_Name = $val['Name'];
        $SQL = "SELECT `ID` 
                FROM `ExpDetailName` 
                WHERE `ParentID`= '$new_parent_id'
                AND `Name`='$new_Name'";
        $tmp_arr = $HITSDB->fetch($SQL);
        if($tmp_arr){
          $old_new_optionID_arr[$val['ID']] = $tmp_arr['ID'];
        }else{
          $SQL = "INSERT INTO `ExpDetailName` 
                  SET ParentID=$new_parent_id, 
                  Name='".$val['Name']."', 
                  UserID='".$val['UserID']."', 
                  DT='".@date('Y-m-j')."'";
          $new_option_id = $HITSDB->insert($SQL);
          $to_file .= "\n$SQL";
          if($new_option_id){
            $old_new_optionID_arr[$val['ID']] = $new_option_id;
          }
        }
      }
    }

    $SQL = "select ID from Experiment where BaitID in ($frm_selected_bait_str)";
    $tmp_Exp_arr = $HITSDB->fetchAll($SQL);
    $Exp_str = '';
    foreach($tmp_Exp_arr as $tmp_Exp_val){
      if($Exp_str) $Exp_str .= ',';
      $Exp_str .= $tmp_Exp_val['ID'];
    }
    if($Exp_str){
      $SQL = "SELECT `ExpID`, `SelectionID`, `OptionID`, `IndexNum`, `UserID`, `DT` FROM `ExpDetail` WHERE `ExpID` IN ($Exp_str)";
      $tmp_ExpDetail_arr = $HITSDB->fetchAll($SQL);
      foreach($tmp_ExpDetail_arr as $tmp_ExpDetail_val){
        if(array_key_exists($tmp_ExpDetail_val['SelectionID'], $old_new_selectionID_arr) && array_key_exists($tmp_ExpDetail_val['OptionID'], $old_new_optionID_arr)){
          $new_SelectionID = $old_new_selectionID_arr[$tmp_ExpDetail_val['SelectionID']];
          $new_OptionID = $old_new_optionID_arr[$tmp_ExpDetail_val['OptionID']];
          $SQL = "UPDATE `ExpDetail` SET `SelectionID`='$new_SelectionID',
                                         `OptionID`='$new_OptionID',
                                         `DT`='".@date('Y-m-j')."'
                  WHERE `ExpID`='".$tmp_ExpDetail_val['ExpID']."'";
          $HITSDB->execute($SQL);
          $to_file .= "\n$SQL";        
        } 
      }
    } 
       
//=========process NoteType and itemGroups =============================================================================================
    $to_file .= "\n\nprocess NoteType and itemGroups------------------------";
    $SQL = "SELECT `ID`, `Name`, `Type`, `Description`, `Icon`, `ProjectID`, `UserID`, `Initial` FROM `NoteType` WHERE `ProjectID`='$frm_project_ID'";
    $NoteType_rs = mysqli_query($prohits_link, $SQL);
    $NoteType_map_arr = array();
    while($NoteType_row = mysqli_fetch_assoc($NoteType_rs)){              
      $SQL = "select ID from `NoteType` 
              where ProjectID='$frm_to_project_ID' 
              and Type='".$NoteType_row['Type']."' 
              and Name='".mysqli_real_escape_string($prohits_link, $NoteType_row['Name'])."'
              and Initial='".$NoteType_row['Initial']."'";             
              
      $tmp_rs = mysqli_query($prohits_link, $SQL);

      if($new_row = mysqli_fetch_assoc($tmp_rs)){
        $NoteType_map_arr[$NoteType_row['ID']] = $new_row['ID'];
      }else{
        $NoteType_map_arr[$NoteType_row['ID']] = $NoteType_row;
      }
    }

    //---BaitGroup------------
    $SQL = "SELECT `ID`,`NoteTypeID`,`RecordID` FROM `BaitGroup` WHERE RecordID in($frm_selected_bait_str)";
    update_itemGroup_to_new_project($NoteType_map_arr,'BaitGroup',$SQL);
    
    //---ExperimentGroup------
    $SQL = "select ID from Experiment where BaitID in ($frm_selected_bait_str)";
    $exp_rs = mysqli_query($prohits_link, $SQL);
    $Exp_str = '';
    while($exp_row = mysqli_fetch_assoc($exp_rs)){
      if($Exp_str) $Exp_str .= ',';
      $Exp_str .= $exp_row['ID'];
    }
    $SQL = "SELECT `ID`,`NoteTypeID` FROM `ExperimentGroup` WHERE RecordID in($Exp_str)";
    update_itemGroup_to_new_project($NoteType_map_arr,'ExperimentGroup',$SQL);
    
    //---BandGroup------------
    $SQL = "select ID from Band where BaitID in ($frm_selected_bait_str)";
    $band_rs = mysqli_query($prohits_link, $SQL);
    $Band_str = '';
    while($band_row = mysqli_fetch_assoc($band_rs)){
      if($Band_str) $Band_str .= ',';
      $Band_str .= $band_row['ID'];
    }
    $SQL = "SELECT `ID`,`NoteTypeID` FROM BandGroup WHERE Note NOT LIKE 'SAM%' AND RecordID in($Band_str)";
    update_itemGroup_to_new_project($NoteType_map_arr,'BandGroup',$SQL);
    
//======Process Exp and Sample Protocol===============================================================================
//------process Protocol table and update NoteTypeID for sample protocol in BandGroup table---------------------------
    $to_file .= "\n\nProcess Exp and Sample Protocol-----------------------";
    //copy sampel protocals
    $SQL = "select ID,Name,Type,Detail,UserID from Protocol where ProjectID='$frm_project_ID'";
    $protocol_rs = mysqli_query($prohits_link, $SQL);
    
    $protocol_old2new = array();
    while($protocol_row = mysqli_fetch_assoc($protocol_rs)){
      $old_protocol_ID = $protocol_row['ID']; 
      
      $SQL = "select ID from Protocol 
              where ProjectID='$frm_to_project_ID' 
              and Type='".$protocol_row['Type']."' 
              and Name='".mysqli_real_escape_string($prohits_link, $protocol_row['Name'])."'";
      
      $tmp_rs = mysqli_query($prohits_link, $SQL);
      if($new_row = mysqli_fetch_assoc($tmp_rs)){
        $new_protocol_ID = $new_row['ID'];
      }else{
        $SQL = "insert into Protocol set 
            Name='".mysqli_real_escape_string($prohits_link, $protocol_row['Name'])."',
            Type='".mysqli_real_escape_string($prohits_link, $protocol_row['Type'])."',
            ProjectID='".$frm_to_project_ID."',
            Detail='".mysqli_real_escape_string($prohits_link, $protocol_row['Detail'])."',
            Date=now(),
            UserID='".$protocol_row['UserID']."'";
        mysqli_query($prohits_link, $SQL);
        $to_file .= "\n$SQL";
        $new_protocol_ID = mysqli_insert_id($prohits_link);
      }
      
      if(preg_match("/^SAM/", $protocol_row['Type'], $matches)){
        $SQL = "select ID from Band where BaitID in ($frm_selected_bait_str)";
        $band_rs = mysqli_query($prohits_link, $SQL);
        while($band_row = mysqli_fetch_assoc($band_rs)){
          $SQL = "update BandGroup set NoteTypeID='".$new_protocol_ID."' where NoteTypeID='".$old_protocol_ID."' AND RecordID='".$band_row['ID']."' AND Note LIKE 'SAM%'";
          mysqli_query($prohits_link, $SQL);
          $to_file .= "\n$SQL";
        }
      }else{
        $protocol_old2new[$old_protocol_ID] = $new_protocol_ID;
      }
    }
//==============Chang old ProtocolID to new ProtocolID ==================================================================================    
    $SQL = "select ID, GrowProtocol, IpProtocol, DigestProtocol, PeptideFrag from Experiment where BaitID in ($frm_selected_bait_str)";
    $exp_rs = mysqli_query($prohits_link, $SQL);
    while( $exp_row = mysqli_fetch_array($exp_rs) ){
      $g_str = '';
      $i_str = '';
      $d_str = '';
      $p_str = '';
      
      $SQL = "update Experiment set ";
      if($exp_row['GrowProtocol']){
        $tmp_arr = explode(",", $exp_row['GrowProtocol']);
        if(count($tmp_arr)==2){
          if(isset($protocol_old2new[$tmp_arr[0]])){
            $g_str = $protocol_old2new[$tmp_arr[0]].",".$tmp_arr[1];
          }  
        }
      }
      if($exp_row['IpProtocol']){
        $tmp_arr = explode(",", $exp_row['IpProtocol']);
        if(count($tmp_arr)==2){
          if(isset($protocol_old2new[$tmp_arr[0]])){
            $i_str = $protocol_old2new[$tmp_arr[0]].",".$tmp_arr[1];
          }  
        }
      }
      if($exp_row['DigestProtocol']){
        $tmp_arr = explode(",", $exp_row['DigestProtocol']);
        if(count($tmp_arr)==2){
          if(isset($protocol_old2new[$tmp_arr[0]])){
            $d_str = $protocol_old2new[$tmp_arr[0]].",".$tmp_arr[1];
          }  
        }
      }
      if($exp_row['PeptideFrag']){
        $tmp_arr = explode(",", $exp_row['PeptideFrag']);
        if(count($tmp_arr)==2){
          if(isset($protocol_old2new[$tmp_arr[0]])){
            $p_str = $protocol_old2new[$tmp_arr[0]].",".$tmp_arr[1];
          }  
        }
      }
      $SQL .= "GrowProtocol='$g_str', IpProtocol='$i_str', DigestProtocol='$d_str', PeptideFrag='$p_str' where ID='".$exp_row['ID']."'";      
      mysqli_query($prohits_link, $SQL);
      $to_file .= "\n$SQL";
    }
    //change linked file-----------------------------------------------------------
    $to_file .= "\n\nProcess Raw files link-------------------------------";
    $SQL = "select ID, RawFile from Band where BaitID in ($frm_selected_bait_str)";
    $band_rs = mysqli_query($prohits_link, $SQL);
    $managerDB = new mysqlDB(MANAGER_DB);
    
    while($band_row = mysqli_fetch_array($band_rs)){
      if($band_row['RawFile']){
        $raw_arr = explode(";", $band_row['RawFile']);
        foreach($raw_arr as $raw_file){
          $tmp_arr = explode(":", $raw_file);
          if(count($tmp_arr) == 2){
            $raw_file_ID = trim($tmp_arr[1]);
            $machine = trim($tmp_arr[0]);
            if(!$raw_file_ID or !$machine) continue;
            $SQL = "show tables like '".$machine."'";
            if(!mysqli_fetch_row(mysqli_query($msManager_link, $SQL))) continue;
            $SQL = "update $machine set ProjectID='$frm_to_project_ID' where ID='$raw_file_ID'";
            mysqli_query($msManager_link, $SQL);
            $to_file .= "\n$SQL";
            $SQL = "update $machine set ProjectID='$frm_to_project_ID' where RAW_ID='$raw_file_ID'";
            mysqli_query($msManager_link, $SQL);
            $to_file .= "\n$SQL";
          }
        }
      }
    }
    $to_file .= "\n\nUpdate Bait, Experiment and Band-------------------------------";
    $SQL = "update Bait set ProjectID='$frm_to_project_ID' where ID in ($frm_selected_bait_str)";
    $to_file .= "\n$SQL";
    mysqli_query($prohits_link, $SQL);
    $SQL = "update Experiment set ProjectID='$frm_to_project_ID' where BaitID in ($frm_selected_bait_str)";
    $to_file .= "\n$SQL";
    mysqli_query($prohits_link, $SQL);
    $SQL = "update Band set ProjectID='$frm_to_project_ID' where BaitID in ($frm_selected_bait_str)";
    $to_file .= "\n$SQL";
    mysqli_query($prohits_link, $SQL);
    
    //$searchEngines_file = "../../TMP/searchEngines/P_".$frm_to_project_ID.".txt";
    
    $fd = fopen($log_path, 'a+');
    fwrite($fd, "$to_file");
    fclose($fd);
    
    $to_project_searchEngines_file = "../TMP/searchEngines/P_".$frm_to_project_ID.".txt";
    if(is_file($to_project_searchEngines_file)){
      unlink($to_project_searchEngines_file);
    }       
    echo "<H2>Bait(s) have been moved.";
    echo "<br>Log file path: $log_path";
    exit;
  }
}

$BaitGroup = 'BaitGroup';
if($frm_project_ID > 0){
  mysqli_select_db($prohits_link, $HITS_DB[$hits_db_name]);
  $HITSDB->change_db($HITS_DB[$hits_db_name]);

  if($frm_select_group){
    $frm_selected_bait_str = '';
    $SQL = "SELECT `RecordID` FROM BaitGroup WHERE NoteTypeID='$frm_select_group'";
    $selected_rs = mysqli_query($prohits_link, $SQL);
    while($row = mysqli_fetch_array($selected_rs)){
      if($frm_selected_bait_str) $frm_selected_bait_str .=',';
      $frm_selected_bait_str .= $row['RecordID'];
    }
  }
  
  $select_ID_array = array();
  if($frm_selected_bait_str){
    $select_ID_array = explode(",", $frm_selected_bait_str);
  }
  $SQL = "SELECT ID, GeneName, BaitAcc,Clone FROM Bait WHERE ProjectID='$frm_project_ID'";
  if($frm_order_by){
    $SQL .= " ORDER BY $frm_order_by";
  }else{
    $SQL .= " ORDER BY ID DESC";
  }   
  $bait_results = mysqli_query($prohits_link, $SQL);
  $SQL = "SELECT `ID`, `Name` , `Description` , `Icon` , `UserID` , `Initial` 
        FROM `NoteType` 
        WHERE Type='Bait' and `ProjectID`='$frm_project_ID'";
  $NoteType_rd = mysqli_query($prohits_link, $SQL);
 
  $empty_spaces = 0;
  while($rd = mysqli_fetch_array($NoteType_rd)){
    $bait_group_icon_arr[$rd['ID']] = array('Icon'=>$rd['Icon'],'Name'=>$rd['Name'],'Initial'=>$rd['Initial'],'Index'=>$empty_spaces++);
  }
  if($frm_selected_bait_str){
     
    $SQL = "SELECT BA.ID, BA.GeneName, BA.BaitAcc,BA.Clone FROM Bait BA  
              WHERE BA.ProjectID='$frm_project_ID' 
              AND BA.ID IN($frm_selected_bait_str)
              ORDER BY BA.ID";
    $selected_bait_results = mysqli_query($prohits_link, $SQL);
  }
  
  $SQL = "SELECT `RecordID`, `NoteTypeID` FROM BaitGroup WHERE `NoteTypeID`<>'0' order by RecordID, NoteTypeID";
  $baitDis_rs = mysqli_query($prohits_link, $SQL);
  while($row = mysqli_fetch_array($baitDis_rs)){ 
    if(!isset($baitNotesArr[$row['RecordID']])){
      $baitNotesArr[$row['RecordID']] = array();
    }
    array_push($baitNotesArr[$row['RecordID']], $row['NoteTypeID']);
  }
}
    
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
  theForm.frm_selected_bait_str.value = '';
  theForm.submit();
}
function process(theForm){
  theForm.theaction.value = 'process';
  theForm.submit();
} 
function submitIT(theForm){
  theForm.submit();
}
 
 
function addbait(theForm){
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
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
  theForm.theaction.value = 'addbait';
  theForm.submit();
}

function removebait(theForm){
  if(typeof(newWin) == 'object'){
    newWin.close();
    theForm.target='_parent';
  }  
  theForm.action = '<?php echo $_SERVER['PHP_SELF'];?>';
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
  theForm.theaction.value = 'removebait';
  theForm.submit();
}

function createSelectedbaitStr(theForm){
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
 
 
</script>
<h2>Split Project</h2>
<form name=editform method=post action='<?php echo $_SERVER['PHP_SELF'];?>'>  
<INPUT TYPE="hidden" NAME="frm_selected_bait_str" VALUE="<?php echo $frm_selected_bait_str?>">
<input type=hidden name=SID value='<?php echo $SID;?>'>
<input type=hidden name=theaction value=''>
 
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
      <td bgcolor=#cbcbcb><b>From Project: </b><br>
      <select name="frm_project_ID"  onChange="changeProject(this.form);">
      <option value='-1'>-- select project --
      <?php 
      foreach($pro_access_ID_Names as $tmp_pro_ID=>$tmp_pro_name){
        $selected = ($tmp_pro_ID == $frm_project_ID)? " selected": "";
        echo "  <option value='$tmp_pro_ID' $selected>($tmp_pro_ID) $tmp_pro_name\n"; 
      }
      ?>
      </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     
    </td>
    <td bgcolor=#cbcbcb><b>To Project: </b><br>
      <select name="frm_to_project_ID">
      <option value=''>-- select project --
      <?php 
      if($frm_project_ID > 0){
        foreach($pro_access_ID_Names as $tmp_pro_ID=>$tmp_pro_name){
          if($pro_access_ID_database_Names[$frm_project_ID] == $pro_access_ID_database_Names[$tmp_pro_ID]){
            if($frm_project_ID !=$tmp_pro_ID){
              $selected = ($tmp_pro_ID == $frm_to_project_ID)? " selected": "";
              echo "  <option value='$tmp_pro_ID'$selected>($tmp_pro_ID) $tmp_pro_name\n"; 
            }
          }
        }
      }
      ?>
      </select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     
    </td>
   </tr>
</table>   
<table border="0" width="700" height="50" cellspacing="2" cellpadding=3 bgcolor=>   
   <tr>
<?php  
  if($bait_results){
?>
   <tr>
      <td bgcolor=#cbcbcb align=center><b>Bait List</b><br>
      <select name="frm_baitList" size=20 multiple>
         <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
      <?php 
      while($row = mysqli_fetch_array($bait_results)){
        $tmpbaitID = $row['ID'];
        if(in_array($tmpbaitID, $select_ID_array)) continue;
        $tmp2top = false;
        $initial_str = '';
        if(array_key_exists($tmpbaitID, $baitNotesArr)){
          foreach($baitNotesArr[$tmpbaitID] as $tmpTypeID){
            if(array_key_exists($tmpTypeID, $bait_group_icon_arr)){
              $v = '';
              if(is_numeric($bait_group_icon_arr[$tmpTypeID]['Initial'])) $v = 'VS';
              $initial_str .= "[".$v.$bait_group_icon_arr[$tmpTypeID]['Initial']."]";
            }
          }
        }
        echo "<option value='".$row['ID']."'>".$row['ID']."&nbsp; &nbsp;".$row['GeneName']."&nbsp; &nbsp;".$row['BaitAcc']."&nbsp; &nbsp;".$initial_str."\n";
      }
      ?>
      </select> 
      <br><br><br>
       
      <b>Sort by:</b>
      <select name="frm_order_by" onChange="submitIT(this.form)">
        <option value="ID" <?php echo ($frm_order_by=='ID DESC' || $frm_order_by=='ID DESC')?'selected':'';?>>ID</option>
        <option value="GeneName" <?php echo ($frm_order_by=='GeneName')?'selected':'';?>>Gene Name</option> 
      </select><br><br>
       
       
      <b>Select All Baits in this Group:</b>
      <select name="frm_select_group" onChange="submitIT(this.form)">
        <option value="">---
        <?php 
        foreach($bait_group_icon_arr as $key =>$rd){
          $selected = ($frm_select_group == $key)?" selected":"";
          $v = '';
          if(is_numeric($bait_group_icon_arr[$key]['Initial'])) $v = 'VS';          
          echo "<option value='".$key."'$selected>".$rd['Name']." (".$v.$rd['Initial'].")</option>\n";
        }
        ?> 
      </select><br><br>
     </td>
     <td valign=center  bgcolor=#cbcbcb>
      <font size="2" face="Arial">
      <center>
      
      <input type=button value='&nbsp;&nbsp;   > >  &nbsp;&nbsp;' onClick="addbait(this.form)">
      <br><br>
      <input type=button value='&nbsp;&nbsp;   < <  &nbsp;&nbsp;' onClick="removebait(this.form)">
      </center>
      </font> 
      </td>
      <td valign=top align=center  bgcolor=#cbcbcb>
     <b>Selected Baits List</b><br>
          <select name="frm_selected_bait" size=20 multiple>
            <option value=''>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
            <?php 
             $typeArr = array(); 
             //$selected_bait_results
             if($selected_bait_results){
             while($Selectedbaits = mysqli_fetch_array($selected_bait_results)){
               $tmpbaitID = $Selectedbaits['ID'];
               $initial_str = '';
               if(isset($baitNotesArr[$tmpbaitID])){
                 foreach($baitNotesArr[$tmpbaitID] as $tmpTypeID){
                  if(isset($bait_group_icon_arr[$tmpTypeID])){
                    $v = '';
                    if(is_numeric($bait_group_icon_arr[$tmpTypeID]['Initial'])) $v = 'VS';
                    $initial_str .= "[".$v.$bait_group_icon_arr[$tmpTypeID]['Initial']."]";
                    //$initial_str .= "[".$bait_group_icon_arr[$tmpTypeID]['Initial']."]";
                  }  
                 }
               }
               echo "<option value='".$Selectedbaits['ID']."'>".$Selectedbaits['ID']."&nbsp; &nbsp;".$Selectedbaits['GeneName']."&nbsp; &nbsp;".$Selectedbaits['BaitAcc']."&nbsp;&nbsp;$initial_str\n";
               array_push($typeArr, $Selectedbaits['ID'].";;".$initial_str);
             }
             $typeStr = implode(",,", $typeArr);
             }
            ?>
            </select>
    </td>
 <tr>
 <td colspan=3 align=center>
    <input type=button value='Close Window' onClick="window.close()" >
    <input type=button value='Submit' onClick="process(this.form)" >
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
function insert_to_NoteType_table($NoteType_row,$frm_to_project_ID){
  global $prohits_link; 
  global $to_file;       
  $SQL = "insert into NoteType set 
          Name='".mysqli_real_escape_string($prohits_link, $NoteType_row['Name'])."',
          Type='".mysqli_real_escape_string($prohits_link, $NoteType_row['Type'])."',
          Description='".mysqli_real_escape_string($prohits_link, $NoteType_row['Description'])."',
          Icon='". $NoteType_row['Icon']."',
          ProjectID='".$frm_to_project_ID."',
          UserID='".$NoteType_row['UserID']."',
          Initial='".$NoteType_row['Initial']."'";
  mysqli_query($prohits_link, $SQL);
  $to_file .= "\n$SQL";
  return $new_NoteType_ID = mysqli_insert_id($prohits_link);
}
  
function update_itemGroup_to_new_project($NoteType_map_arr,$group_name,$SQL){    
  global $prohits_link;
  global $frm_to_project_ID;
  global $to_file;
  $BaitGroup_rs = mysqli_query($prohits_link, $SQL); 
  while($BaitGroup_row = mysqli_fetch_assoc($BaitGroup_rs)){
    $old_NoteType_ID = $BaitGroup_row['NoteTypeID'];
    $index_ID = $BaitGroup_row['ID'];
    if(array_key_exists($old_NoteType_ID, $NoteType_map_arr)){
      if(is_array($NoteType_map_arr[$old_NoteType_ID])){
        $new_NoteType_ID = insert_to_NoteType_table($NoteType_map_arr[$old_NoteType_ID],$frm_to_project_ID);
        $NoteType_map_arr[$old_NoteType_ID] = $new_NoteType_ID;
      }else{
        $new_NoteType_ID = $NoteType_map_arr[$old_NoteType_ID];
      }
      $SQL = "UPDATE $group_name SET NoteTypeID='".$new_NoteType_ID."' WHERE ID='".$index_ID."'";
      mysqli_query($prohits_link, $SQL);
      $to_file .= "\n$SQL";
    }
  } 
}  
?>