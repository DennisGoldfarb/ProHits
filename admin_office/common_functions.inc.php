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

function to_proteinDB($mainDB){
  $oldDBName = '';
  if($mainDB->selected_db_name != PROHITS_PROTEINS_DB){
    $oldDBName =$mainDB->selected_db_name;
    $mainDB->change_db(PROHITS_PROTEINS_DB);    
  }  
  return $oldDBName;
}

function change_DB($mainDB, $toDBname){
  $oldDBName = '';
  if($mainDB->selected_db_name != $toDBname){
    $oldDBName = $mainDB->selected_db_name;
    $mainDB->change_db($toDBname);    
  }  
  return $oldDBName;
}

function to_defaultDB($mainDB){
  $oldDBName = '';
  if($mainDB->selected_db_name != PROHITS_DB){
    $oldDBName =$mainDB->selected_db_name;
    $mainDB->change_db(PROHITS_DB);    
  }  
  return $oldDBName;
}

function back_to_oldDB($mainDB, $oldDBName){ 
  if($oldDBName){
    $mainDB->change_db($oldDBName);
  }
}

function get_project_ID_Name($mainDB){
  $oldDBName = '';
  $projectArr = array();
  if($mainDB->selected_db_name != PROHITS_DB){
    $oldDBName =to_defaultDB($mainDB);    
  }
  $SQL = "select ID, Name from Projects order by ID";
  $results = mysqli_query($mainDB->link, $SQL); 
  while($Row = mysqli_fetch_row($results)){      
    $projectArr[$Row[0]] = $Row[1];
  }
  $projectArr['-1'] = "others";
  back_to_oldDB($mainDB, $oldDBName);
  return $projectArr;
}

function TaxID_list($mainDB, $focus_value, $showDisplay=''){
  $SQL = "SELECT TaxID, Name, Display FROM ProteinSpecies WHERE Species = 'Y' order by ID";  
  $SpeciesArr2 = $mainDB->fetchall($SQL);  
  for($i=0; $i<count($SpeciesArr2); $i++){
?>
  <option value="<?php echo  $SpeciesArr2[$i]['TaxID'];?>"<?php echo  ($SpeciesArr2[$i]['TaxID']==$focus_value)?" selected":"";?>><?php echo $SpeciesArr2[$i]['Name'];?><br>
<?php   
  }
}

function get_TaxID_name($mainDB, $TaxID){
  $TaxName = '';
  if($TaxID){
    $SQL = "SELECT Name FROM ProteinSpecies WHERE TaxID=$TaxID";  
    $SpeciesArr = $mainDB->fetch($SQL);  
    $TaxName = '';
    if(count($SpeciesArr) && $SpeciesArr['Name']){
      $TaxName = $SpeciesArr['Name'];
    }
  }  
  return $TaxName;
}

//--link list is broken--------------------------
function TaxID_list_($mainDB, $focus_value, $showDisplay=''){  
  $SpeciesArr['TaxID'] = 0;
  $mainArr = array();  
  array_push($mainArr, $SpeciesArr);
  $itemCount = 0;
  while(count($mainArr)){
    $itemCount++;
    if($itemCount > 500){
      break;
    }
    $popItem = array_pop($mainArr);
    $SQL = "SELECT TaxID, Name, Display FROM ProteinSpecies WHERE ParentTaxID=".$popItem['TaxID']." ORDER BY Name DESC";  
    $SpeciesArr2 = $mainDB->fetchAll($SQL);
    
    if($popItem['TaxID']){
      ?>
        <option value="<?php echo $popItem['TaxID'];?>"<?php echo ($popItem['TaxID']==$focus_value)?" selected":"";?>><?php echo ($showDisplay)?$popItem['Display']:$popItem['Name'];?><br>
      <?php 
    }  
    for($i=0; $i<count($SpeciesArr2); $i++){
      array_push($mainArr, $SpeciesArr2[$i]);
    }
  }  
}

function get_users_ID_Name($mainDB){
  $DBnameChanged = 0;
  $UsersArr = array();
  if($mainDB->selected_db_name != PROHITS_DB){
    $oldDBName =$mainDB->selected_db_name;
    $mainDB->change_db(PROHITS_DB);
    $DBnameChanged = 1;
  }
  $SQL = "select ID, Fname, Lname from User";
  $results = mysqli_query($mainDB->link, $SQL); 
  while($Row = mysqli_fetch_row($results)){      
    $UsersArr[$Row[0]] = $Row[1].' '.$Row[2];
  }
  if($DBnameChanged == 1){
    $mainDB->change_db($oldDBName);
  }
  return $UsersArr;
}

//----------------------------------------------
function fatalError($msg='', $line=0){
//----------------------------------------------
  global $start_time;
  $msg  = "Fatal Error--$msg;";
  $msg .=  " Script Name: " . $_SERVER['PHP_SELF']. ";";
  $msg .= " Start time: ". $start_time . ";";
  if($line){
    $msg .= " Line number: $line;";
  }
  writeLog($msg);
  exit;
}
//---------------------------------------------
function writeLog($msg){
//---------------------------------------------
  global $logfile; 
  $log = fopen($logfile, 'a+');
  fwrite($log, "\r\n" . $msg);
  fclose($log);
  echo $msg."\r\n";
}

function get_current_DB_tables_name_arr($DB_obj){
  $r = mysqli_query($DB_obj->link, "SELECT DATABASE()") or die(mysqli_error($DB_obj->link));
  $row = mysqli_fetch_row($r);
  $db_name = $row[0];
  $tables_name_arr = array();
  $names_result = mysqli_query($DB_obj->link, "SHOW TABLES FROM $db_name");
   
  while($row = mysqli_fetch_row($names_result)){
    array_push($tables_name_arr, $row[0]);
  }
  return $tables_name_arr;
}

function check_conditions_for_creat_tables($DB_obj){
  global $BACKUP_SOURCE_FOLDERS;
  $tables_name_arr = get_current_DB_tables_name_arr($DB_obj);
  if(!$tables_name_arr){
    $meg = "Warning: DataBase $DB_obj->selected_db_name is empty";
  }
  $meg = '';
  if(!isset($BACKUP_SOURCE_FOLDERS) || !$BACKUP_SOURCE_FOLDERS){
    $meg = "Warning: BACKUP_SOURCE_FOLDERS is not exist or empty";
  }else{
    $key_arr = array_keys($BACKUP_SOURCE_FOLDERS);
    $other_tb_arr = array('SearchResults','SearchTasks','SaveConf','tppResults','tppTasks');
    $original_tb_name = ''; 
    foreach($key_arr as $k_header){
      if(!in_array($k_header, $tables_name_arr)) continue;
      $all_exist_flag = 1;
      foreach($other_tb_arr as $k_body){
        $key_name = $k_header.$k_body;
        if(!in_array($key_name, $tables_name_arr)){
          $all_exist_flag = 0;
          break;
        }  
      }
      if($all_exist_flag){
        $original_tb_name = $k_header;
        break;
      }  
    }
    if(!$original_tb_name){
      $meg = "Warning: Table $original_tb_name is not exist.";
    }else{
      $meg = $original_tb_name;
    }
  }
  return $meg;
}


//---------------------------------------------------------------
function clone_db_table_structure($DB_obj,$original_tb_name,$target_tb_name){
//---------------------------------------------------------------
  $SQL = "CREATE TABLE IF NOT EXISTS `$target_tb_name` LIKE `$original_tb_name`";
  $ret = mysqli_query($DB_obj->link, $SQL);
  return $ret;
}
?>
