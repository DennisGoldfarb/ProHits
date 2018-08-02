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

$thePage = '';
$tmp_str = '';
$displayby = '';
//----for calendar.inc.php-----
$open_dir_ID = 0;
$info = '';
$crtPro = '';
$tableName = '';


//connect database msManager and check login -------
require_once("../common/site_permission.inc.php");

require_once("msManager/common_functions.inc.php");
include ("msManager/is_dir_file.inc.php");
require ("common/PHPMailer-master/PHPMailerAutoload.php");
require ("common/common_fun.inc.php");
//--------------------------------------------------
 

$this_web_dir = pathinfo($PHP_SELF,PATHINFO_DIRNAME);
$prohits_web_root = dirname($this_web_dir);
$prohits_root = str_replace("msManager","",dirname(__FILE__));
 
include("./ms_header.php");

$mTablesNameArr = get_mdb_table_names();
 
/*echo "<pre>";
print_r($mdb_table_namesa_arr);
echo "</pre>";exit;*/
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
  <td bgcolor="#a4b0b7" valign="top" align="left" width="175">
   <?php include("./ms_storage_menu.inc.php");?>
   <?php include("./calendar/calendar.inc.php");?>
   <br><br>
  </td>
  <td width="928" align=left valign=top>
   <table border=0 width=97%>
    <tr><td align=center>
      <font face="Arial" size="+2" color="#660000"><b>Raw Data Storage</b></font>
      <hr width="100%" size="1" noshade>
    </td></tr>
   <tr>
     <td> 
   
   This section allows you to monitor the transfer of data from each of the acquisition computers to the ProHits backup system. It also allows you to search, browse and download files, convert raw files to other formats, and manually upload raw data.
 <br><br>
All MS raw data will be saved to the ProHits computer (<b><?php echo STORAGE_IP. " : " . STORAGE_FOLDER;?></b>) and a MySQL database (<b><?php echo PROHITS_SERVER_IP . " : ".MANAGER_DB;?></b>) will store the data information.  ProHits monitors all connections with acquisition computers.  Broken connections are indicated by a broken red arrow.
 

     </td> 
   </tr>
   </table>
   <table align=center border=0>
   <?php 
   $storage_err = check_stoage_computer();
    
   $tb_count = count($BACKUP_SOURCE_FOLDERS);
   $total_rows = round($tb_count/2); 
   $ct = 1;
   $error = '';
   $msg = '';
   $storage_broken = ($storage_err)?"_lost":"";
    foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
      $rt = array('msg'=>'', 'error'=>'');
      if(!$storage_err){
        
        $rt = check_backup($baseTable, $var_arr);
      }
      if($rt['error']) $error .= "<li>".$rt['error'];
      if(!in_array($baseTable, $table_arr)){
        $error .= "<li>No database related with $baseTable";
      }
      $msg = $rt['msg'];
      $logo = strtoupper($baseTable);
      $broken = '';
      $title_detail = 'connected';
      if($rt['error'] AND $var_arr['SOURCE']){
        $broken = "_lost";
        $title_detail = 'back up connection is broken';
      }elseif(!is_auto_search($baseTable)){
        $broken = "_lgreen";
        $title_detail = 'no auto-search setup';
      }elseif(!$var_arr['SOURCE']){
        $broken = "_lblue";
        $title_detail = 'no back up setup';
      }    
      
      if(!is_file("./images/msLogo/" . $logo . "_logo.gif")) $logo = "default";
      if($ct%2){
        echo "<tr>\n";
        
        echo "<td align=center>
          <a href='ms_storage_raw_data.php?tableName=".$baseTable."'><img src='./images/msLogo/".$logo."_logo.gif' border=0 width=100 height=80></a><br><b>".$baseTable."</b><br>$msg</td>
          <td><a  title='$title_detail'><img src='./images/db_lr$broken.gif' border=0></a></td>\n";
          if($ct==1){
            echo "<td rowspan=$total_rows bgcolor=#cdcdcd width=150 align=center>";
            echo "Storage Database Computer:<br><font color='red'><b>".PROHITS_SERVER_IP."</b></font><br>";
            echo "Storage Database Name: <br><font color='red'><b>".MANAGER_DB."</b></font><br><br>";
            echo "<img src=./images/db_prohits.gif border=0 alt='storage'><br><br>";
            echo "<img src=./images/db_d$storage_broken.gif border=0 alt='storage'><br><br>";
            echo "<img src=./images/db.gif border=0 alt='storage'><br><br>";
            echo "Storage Computer:<br><font color='red'><b>". STORAGE_IP ."</b></font><br>";
            echo "Storage Folder: <br><font color='red'><b>".STORAGE_FOLDER."</b></font><br><br>";
            echo "</td>\n";
          }
      }else{
        echo "<td><a  title='$title_detail'><img src='./images/db_rl$broken.gif' border=0></a></td>
          <td align=center>
          <a href='ms_storage_raw_data.php?tableName=".$baseTable."'><img src='./images/msLogo/".$logo."_logo.gif' border=0 width=100 height=80></a><br><b>".$baseTable."</b><br> $msg</td>\n";
          echo "</tr>\n";
      }
      $ct++;
    }
    if($ct%2===0){
      echo "<td>&nbsp;</td></tr>\n";
    }
   ?>
   </table>
   <font color="#FF0000"><?php echo "<ol>".$error.$storage_err."</ol>";?></font>
  </td>
  </tr> 
</table>
<?php 

if(($error or $storage_err) and EMAIL2ADMIN_CONNECTION_ERROR and ADMIN_EMAIL){
  $msg = "<H2>Prohits Error Report</H2><br>
          <b>send from url</b>: ". $PHP_SELF."<br>
          <b>user</b>: ". $USER->Fname. " ". $USER->Lname."<br>
          <b>user email</b>: ". $USER->Email ."<br>
          <b>userID</b>: ".$USER->ID."<br>
          <ol>".$error ."\r\n". $storage_err."</ol>
          Please login Prohits server as root or sudo user, run following command from Prohits/msManager/ for detail.<br>
          # php auto_run_shell.php connect
          ";
  
  if(PROHITS_GMAIL_USER and PROHITS_GMAIL_PWD){
    $err = prohits_gmail(ADMIN_EMAIL, "", 'storage connection error', $msg, 1);
    echo $err;
  }else{
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: '.PROHITS_SERVER_IP . "\r\n";
    mail(ADMIN_EMAIL, "storage connection error", $msg, $headers);
  }
}
include("./ms_footer.php");


function get_mdb_table_names(){//return a array of all tables name in prohits_mamager db.
  $prohitsManagerDB = new mysqlDB(MANAGER_DB);
  $mDBname = MANAGER_DB;
  $SQL = "SHOW TABLES FROM $mDBname";
  //echo $SQL;
  $result = mysqli_query($prohitsManagerDB->link, $SQL);
  if(!$result){
     echo "DB Error, could not list tables\n";
     echo 'MySQL Error: ' . mysqli_error($prohitsManagerDB->link);
     exit;
  }
  $mTablesNameArr = array();
  while($row = mysqli_fetch_row($result)){
    $mTablesNameArr[strtoupper($row[0])] = $row[0];
  }
  return $mTablesNameArr;
}

function is_auto_search($tableName){
  global $mTablesNameArr;
  $match_arr = array("SearchResults","SearchTasks","SaveConf","tppResults","tppTasks");
  $is_auto_search = 0;
  foreach($match_arr as $match_val){
    $full_name = $tableName.$match_val;
    if(in_array($full_name, $mTablesNameArr)){
      $is_auto_search = 1;
      break;
    }
  }
  return $is_auto_search;
}
?>
