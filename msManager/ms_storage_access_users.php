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

function getAccessUserNames($mainDB, $storageDB, $USER,$tableName, $userField=''){
  $tmp_users_array = array();
  //get all user name the user can access
  $SQL = "select Username, Alias from User ";
   
  if($USER->Type == 'user'){
     $tmp = str_replace(',',"','",$USER->Alias);
     $SQL .= " where username in('$tmp') or username='".$USER->Username."'";
  }else if($USER->Type == 'supervisor' or $USER->Type == 'labTech'){
     $SQL .= " where LabID='".$USER->LabID."' ";
  }
  $SQL .= " group by Username order by Username";
  $records = $mainDB->fetchAll($SQL);
  
  $m = 0;
  for($k=0; $k<count($records); $k++){
    $tmp_users_array[$m++] = strtoupper($records[$k]['Username']);
    $tmp_alias_arr = explode(',', strtoupper($records[$k]['Alias']));
    for($n = 0; $n < count($tmp_alias_arr); $n++){
      if(!in_array($tmp_alias_arr[$n],$tmp_users_array) ){
        $tmp_users_array[$m++] = $tmp_alias_arr[$n];
      }
    }
  }
  //add alias in the array
  $tmp_array = explode(',',$USER->Alias);
  $i = 0;
  for($k=0; $k<count($tmp_array); $k++){
    if(!in_array(strtoupper($tmp_array[$k]),$tmp_users_array)){
      $tmp_users_array[$i++] = strtoupper($tmp_array[$k]);
    }
  }
  //get storage table user names
  if(!$userField) $userField = "User";
  $SQL = "select $userField from $tableName group by $userField order by $userField";
  if($storageDB){
    $records = $storageDB->fetchAll($SQL);
  }else{
    $records = $mainDB->fetchAll($SQL);
  }
  $i = 0;
  $out_users_str = '';
  $out_users_array = array();
  for($k=0; $k<count($records); $k++){
     if($USER->Type == 'MSTech' or in_array(strtoupper($records[$k][$userField]),$tmp_users_array) ){
       if($i) $out_users_str .= ',';
       $out_users_str .= "'".$records[$k][$userField]."'";
       $out_users_array[$i++] = $records[$k][$userField];
     }
  }
  return array($out_users_array,$out_users_str);
}
?>