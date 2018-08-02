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

if(!isset($storageDB)){
  $storageDB = new mysqlDB(MANAGER_DB);
}
$table_arr = $storageDB->list_tables();
//print_r($table_arr);
//echo $SCRIPT_NAME;
$menu_on = '#c0c8d3';
$menu_off = '#a4b0b7';

?>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <?php 
      foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
        if(!in_array($baseTable, $table_arr)) continue; 
      ?>
      <tr>
      <td bgcolor="<?php echo ($tableName == $baseTable)?$menu_on:$menu_off;?>"><br>
      &nbsp; <a href="./ms_storage_raw_data.php?tableName=<?php echo $baseTable;?>" class=left_menu><?php echo $baseTable;?></a></td>
      </tr>
      <tr height="1">
        <td bgcolor="white" height="1"><img src="./images/dot.gif" width="1" height="1" border="0"></td>
      </tr>
      <?php }?>
      <tr>
      <td><br><br>
      &nbsp; <a href="ms_storage_fetch_raw.php?tableName=<?php echo $tableName;?>" class=left_menu>Fetch Raw File</a></td>
      </tr>
      <tr height="1">
        <td bgcolor="white" height="1"><img src="./images/dot.gif" width="1" height="1" border="0"></td>
      </tr>
      <tr height="1">
      <td><br>
      &nbsp; <a href="ms_storage_raw_info.php?tableName=<?php echo $tableName;?>" class=left_menu>Raw File Status.</a></td>
      </tr>
      <tr height="1">
        <td bgcolor="white" height="1"><img src="./images/dot.gif" width="1" height="1" border="0"></td>
      </tr>
      <tr>
      <td><br>
      &nbsp; <a href="javascript: popwin('http://<?php echo $storage_ip . str_replace('msManager','',dirname($_SERVER['PHP_SELF']));?>logs/log_view.php?log_file=raw_back.log',600,400)" class=left_menu>Backup Log </a></td>
      </tr>
      <tr height="1">
        <td bgcolor="white" height="1"><img src="./images/dot.gif" width="1" height="1" border="0"></td>
      </tr>
      <td><br>
      &nbsp; <a href="javascript: popwin('http://<?php echo $storage_ip.dirname($_SERVER['PHP_SELF'])."/autoBackup/export_raw_files.php?SID=". session_id()?>',950,600);" class=left_menu>Export Raw Files</a>
      <a href="javascript: popwin('../doc/ftp_transfers.html',1000,700);"><img src=./images/icon_help.gif border=0></a>
      </td>
      </tr>
      <tr height="1">
        <td bgcolor="white" height="1"><img src="./images/dot.gif" width="1" height="1" border="0"></td>
      </tr>
    </table><br><br>