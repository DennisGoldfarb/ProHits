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

$need_login = 0;

$gd_support = '';
$session_support = '';
$session_path = '';
$Apache_verion = '';
$apache_user = ''; 
$apache_server_root = '';
$theaction =  '';
$storage_folder = '';
$mysql_support = '';
$gd_support = '';
$ftp_support = '';
$php_ini_path = '';

$localhost_link = 0;
$remote_link = 0;
$manager_db_ok = false;

if($need_login){
  require_once("../common/site_permission.inc.php");
}
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}

$PHP_SELF = $_SERVER['PHP_SELF'];
$this_web_dir = pathinfo($PHP_SELF,PATHINFO_DIRNAME);
$prohits_web_root = dirname($this_web_dir);
if($prohits_web_root == "/") $prohits_web_root = '';

$prohits_root = str_replace("admin_office","",dirname(__FILE__));
$conf_file_path = $prohits_root . "config/conf.inc.php";
$prohits_conf_file_isreadable = is_readable($conf_file_path);

if($prohits_conf_file_isreadable){
  include_once("../config/conf.inc.php");
  include_once("../msManager/autoSearch/auto_search_mascot.inc.php");
  include_once("../msManager/autoBackup/shell_functions.inc.php");
  include_once("../msManager/is_dir_file.inc.php");
  include_once("../msManager/common_functions.inc.php");
}else{
  echo  "Error: cannot read conf.ini.php file";exit;
}


$prohits_conf_file_writable = _is_writable($conf_file_path);

ob_start();
phpinfo(-1);
$s = ob_get_contents();
ob_end_clean();
$info_arr = explode("\n", $s);
$gd_support = '';
foreach($info_arr as $buffer){
 if(strstr($buffer, "Apache Version")){
    $Apache_verion =  get_value($buffer, "Apache Version");
 }else if(strstr($buffer, "Configuration File (php.ini) Path")){
    $php_ini_path =  get_value($buffer, "Configuration File (php.ini) Path");
 }else if(strstr($buffer, "User/Group")){
    $apache_user =  preg_replace("/\(.+$/","", get_value($buffer, "User/Group"));
 }else if(strstr($buffer, "FTP support")){
    $ftp_support =  get_value($buffer, "FTP support");
 }else if(strstr($buffer, "GD Support")){
    $gd_support =  get_value($buffer, "GD Support");
 }else if(strstr($buffer, "Session Support")){
    $session_support =  get_value($buffer, "Session Support");
 }else if(strstr($buffer, "MySQL Support")){
    $mysql_support =  get_value($buffer, "MySQL Support");
 }else if(strstr($buffer, "Server Root")){
      $apache_server_root =  get_value($buffer, "Server Root");
 }
}
  
function get_value($line, $key){
  global $VERIABLES;
  return trim(str_replace($key,'',strip_tags($line)));
}


?>
<br>
<table border="0" cellpadding="0" cellspacing="0" width="95%" align=center>
  <tr>
    <td align="left">
		&nbsp; <font color="<?php echo $TB_HD_COLOR;?>" face="helvetica,arial,futura" size="+2"><b>Installation check list</b></font> 
	  </td>
    <td align="right">
      &nbsp;
    </td>    
  </tr>
  <tr>
  	<td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
  </tr>
</table>
<br> 
<table cellspacing="0" cellpadding="" border="0" align=center width=95%>
<tr>
<td bgcolor=black valign=top>
<table cellspacing="1" cellpadding="1" border="0" width=100%>
  <tr bgcolor=white>
      
      <td bgcolor=#6495ed align=center><font face="Arial"><b>Check Point</b></font></td>
      <td bgcolor=#6495ed align=center><font face="Arial"><b>Message</b></font></td>
      <td bgcolor=#6495ed align=center><font face="Arial"><b>Result</b></font></td>
  </tr>
  <tr bgcolor=white>
      <?php
      
      $apacheversion = apache_get_version();
      $error= '';
      $msg = '';
      if(!preg_match("/Apache\/2/", $apacheversion)){
         $error .= "<li>Please update Apache to version 2.\n";
      }
      $ps = commandCheck("/usr/sbin/getenforce");
      
      $error = ($ps and ($ps != 'Disabled' and $ps != 'Permissive') )?'Please disable or Permissive SELinux to install ProHits.':'';
      ?>
      <td valign=top align=center>
      <b>Apache Setup</b><br>
      <img src=./images/apache_logo.gif border=0></td>
      <td><li>SELinux: <?php echo $ps;?><br>
          <li>Apache version: <?php echo $apacheversion;?><br>
          <li>Apache User: <font color="#008000"><b><?php echo $apache_user;?></b></font><br>
          <li>Web Document Root: <?php echo $_SERVER["DOCUMENT_ROOT"];?><br>
          <li>Prohits Root : <b><font color="green"><?php echo $prohits_root;?></font></b><br>
          <li>Web Server Address: <?php echo $_SERVER["SERVER_ADDR"];?><br>
      <font color=red><?php echo $error;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  <tr bgcolor=white>
      <?php
       $phpversion = phpversion();
       $error = '';
       $msg = '';
       if(!preg_match("/^5/", $phpversion)){
         $error .= "<li>Please update PHP to version 5.\n";
       }
       if($ftp_support != 'enabled'){
          $error .= "<li>Please set FTP enabled in php.ini file.<br>";
       }
       if($gd_support != 'enabled'){
          $error .= "<li>Please make sure php-gd is installed and GD enabled in php.ini file. Prohits needs it to generate graphic.<br>";
       }
       $file_uploads = ini_get("file_uploads");
       $post_max_size = ini_get('post_max_size');
       $upload_max_filesize = ini_get("upload_max_filesize");
      
        
       if(!$upload_max_filesize) $upload_max_filesize = $post_max_size;
       if(!$file_uploads){
          $file_uploads = 'Off';
          $error .= "<li>Please set file_uploads On in php.ini file.<br>";
       }else{
          $file_uploads = 'On';
       }
       if(strtolower($session_support) != 'enabled'){
          $error .= "<li>Please set session enabled, session.auto_start Off and session.use_cookies On in php.ini file.<br>";
       }else{
          
          if(ini_get("session.use_cookies")){
             $session_path =  ini_get("session.save_path");
             if(!_is_writable($session_path)){
               $error .= "<li>Please make $session_path is writable for apache user $apache_user";
             }
             $session_auto_start = ini_get("session.auto_start");
             if($session_auto_start){
                //$session_auto_start = 'On';
                $error .= "<li>Please set session.auto_start Off in php.ini file<br>";
             }else{
                //$session_auto_start = 'Off';
             }
          }else{
            $error .= "<li>Please set session.use_cookies on in php.ini file.<br>";
          }
       }
      ?>
      <td align=center>
      <b>PHP Setup</b><br>
      <img src=./images/php_logo.gif boder=0></td>
      <td><li>PHP version: <?php echo $phpversion;?><br>
          <li>PHP ini Path: <b><?php echo $php_ini_path;?></b><br>
          <li>FTP support: <?php echo $ftp_support;?><br>
          <?php
          if($ftp_support = 'enabled'){
          	$ncbi_test = test_ftp_site();
          	if($ncbi_test === true){
	      		  echo "NCBI ftp connection is ok.\n";   
      	  	}else{
	      		  $error .= $ncbi_test; 
      	  	}
	        }
           
          ?>
         
          <li>GD support: <?php echo $gd_support;?><br>
          <li>File uploads: <b><?php echo $file_uploads;?></b><br>
          <li>Post max size: <b><?php echo $post_max_size;?></b><br>
          <li>Upload max filesize: <b><?php echo $upload_max_filesize;?></b><br>
          <li>Session support: <?php echo $session_support;?><br>
          <li>Session auto start: <?php echo $session_auto_start;?><br>
          <li>Session Path: <?php echo $session_path;?><br>
          <li>Session folder is writable:
       <?php
          if(_is_writable($session_path)){
            echo " <font color='green'>Yes</font><br>";
          }else{
            echo " <font color='red'>No</font><br>";
          }
          $register_globals = ini_get("register_globals");
          if($register_globals){
            $error .= "<li>Please set register_globals Off in php.ini file.<br>";
          }
          $display_errors = ini_get("display_errors");
          if(!$display_errors){
            $error .= "<li>Please set display_errors On in php.ini file.<br>";
          }
           
      ?>
          <li>Register globals : <?php echo ($register_globals)?'On':'Off';?><br>
          <li>Display error : <?php echo ($display_errors)?'On':'Off';?><br>
           
          <li>wget: 
          <?php 
          if(!commandCheck("wget")){
            echo "not installed";
            $error .= "<li>Please make sure that wget is installed<br>";
          }else{
            echo "installed";
          }
          ?>
          
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  
  <tr bgcolor=white>
      <?php
       $error = '';
       $msg = '';
       $mysql_installed = false;
       if(strtolower($mysql_support) == 'enabled'){
         $mysql_installed = true;
       }else{
        $error .= "The web server doesn't support MYSQL. Prohits needs PHP with MYSQL";
        fatal_Error($error);
       }
      ?>
      <td valign=top align=center><b>Mysql Setup</b><br>
      <img src=./images/mysql_logo.gif border=0></td>
      <td>Mysql support: <?php echo $mysql_support;?><br>
      <?php
      if($mysql_installed){
        $version_num = mysqli_get_client_version();
        $version = floor($version_num/10000) .".". ($version_num%10000)/100;
        echo "Mysql client version:  $version<br>";
      }
      
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
      ?>
      </td>
  </tr>
  <tr bgcolor=white align=center>
  <td colspan=3><br>
  <img src=./images/computer4.gif border=0> <b><font size="+1">Configure Prohits</font></b>
  <br>
  <br>
  </td>
  </tr>
   
  
  <tr bgcolor=white>
      
      <td align=center><font face="Arial">Folder Permission</font><br><img src=./images/icon_folder.gif border=0></td>
      <td>
      <?php
      $error = '';
			$theFolder = $prohits_root."admin_office/update_protein_db/";
      echo "<li>$theFolder is writable: ";
      if(_is_writable($theFolder)){
        echo " <font color='green'>Yes</font><br>";
      }else{
        echo " <font color='red'>No</font><br>";
        $error .= "<li>The folder '$theFolder' should be writable for user '$apache_user' to support COIP image uploading.<br>";
      }
      $theFolder = $prohits_root."analyst/coip_images/";
      echo "<li>$theFolder is writable: ";
      if(_is_writable($theFolder)){
        echo " <font color='green'>Yes</font><br>";
      }else{
        echo " <font color='red'>No</font><br>";
        $error .= "<li>The folder '$theFolder' should be writable for user '$apache_user' to support COIP image uploading.<br>";
      }
      $theFolder = $prohits_root."analyst/gel_images/";
      echo "<li>$theFolder is writable: ";
      if(_is_writable($theFolder)){
        echo "<font color='green'>Yes</font><br>";
      }else{
        echo "<font color='red'>No</font><br>";
        $error .= "<li>The folder '$theFolder' should be writable for user '$apache_user' to support gel image uploading.<br>";
      }
      $theFolder = $prohits_root."TMP/";
      echo "<li>$theFolder is writable: ";
      if(_is_writable($theFolder)){
        echo " <font color='green'>Yes</font><br>";
      }else{
        echo " <font color='red'>No</font><br>";
        $error .= "<li>The folder '$theFolder' should be writable for user '$apache_user'. Prohits needs the folder to create temp file.<br>";
      }
       
      $theFolder = $prohits_root."/msManager/images/msLogo/";
      echo "<li>$theFolder is writable: ";
      if(_is_writable($theFolder)){
        echo " <font color='green'>Yes</font><br>";
      }else{
        echo " <font color='red'>No</font><br>";
        $error .= "<li>The folder '$theFolder' should be writable for user '$apache_user'. Prohits needs the folder to upload logo.<br>";
      }
			//if($error) $error .= ". Please follow the installation instruction step 2 to fix the error";
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
       
      ?>
      </td>
  </tr>

  <tr bgcolor=white>
      
      <td align=center><font face="Arial">Prohits Conf File</font><br><img src=./images/icon_file.gif border=0></td>
      <td>
      <li>Conf file path: <b><font color="green"><?php echo $conf_file_path;?></font></b><br>
      <li>Conf file is readable: <?php echo ($prohits_conf_file_isreadable)?"<font color='green'>Yes":"<font color='red'>No";?></font><br>
      <li>Conf file is writable: <?php echo ($prohits_conf_file_writable)?"<font color='green'>Yes":"<font color='red'>No";?></font><br>
      <li>PROHITS_SERVER_IP: <?php echo PROHITS_SERVER_IP;?><br>
      <?php
      $error = '';
      $msg = '';
      
      if(is_file($conf_file_path)){
        if(!$prohits_conf_file_isreadable){
          $error .= "<li>Please make Prohits conf file is readable and writable for Apache user '$apache_user' then run this script again!<br>";
          fatal_Error($error);
        }        
      }else{
        $error .= "<li>The Prohits conf file is missing. Please place the file in $conf_file_path<br>";
        fatal_Error($error);
      }
       
       
      if($_SERVER["SERVER_ADDR"] != PROHITS_SERVER_IP and $_SERVER["SERVER_NAME"] != PROHITS_SERVER_IP or 1){
        $IP = gethostbyname(PROHITS_SERVER_IP);
        
        if($IP != $_SERVER["SERVER_ADDR"]){
          //$error .= "<li>Your Prohits server IP address is ".$_SERVER["SERVER_ADDR"].".
          //Your should change PROHITS_SERVER_IP to match the Prohits server IP address in conf file.<br>"; 
        }
      }
      
      $lsst_line = exec("which php 2>&1", $output);
      if($lsst_line != PHP_PATH){
            $error .= "Please modify PHP_PATH in conf file to match your computer ($php_path).";
            fatal_Error($error);
      }
      ?>
      <li>PHP_PATH: <?php echo PHP_PATH;?><br>
      <?php
      if($mysql_support){
          $localhost_link= @mysqli_connect(HOSTNAME, USERNAME, DBPASSWORD);
          if(!$localhost_link){
            $error .= "<li>Unable to connect to Mysql database server '".HOSTNAME. "'. <br>
            Make suer HOSTNAME, USERNAME and DBPASSWORD are correct in Prohits conf file.<br>".mysqli_error();
            fatal_Error($error);
          }else{
            echo "<li>Mysql ". HOSTNAME." is accessible from account(USERNAME, DBPASSWORD).<br>".mysqli_get_host_info($localhost_link).") <br>";
          }
          //$remote_link= @mysqli_connect(PROHITS_SERVER_IP, USERNAME, DBPASSWORD);
          //if(!$remote_link){
          //  $error .= "<li>Unable to connect to Mysql database server ".PROHITS_SERVER_IP. " from remote computer. <br>
          //  Make suer the account (USERNAME and DBPASSWORD) has remote access permission to //PROHITS_SERVER_IP.<br>".mysqli_error($remote_link);
          //  fatal_Error($error);
          //}else{
          //  echo "<li>Mysql ". PROHITS_SERVER_IP." is accessible from account(USERNAME, DBPASSWORD).<br> ". //mysqli_get_host_info($remote_link).") <br>";
          //}
      }
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/".$img." border=0>";
       
      ?>
      </td>
  </tr>
  <?php
  $timeout = 5;
  $old = ini_set('default_socket_timeout', $timeout);
  
  check_database(PROHITS_DB, "Prohits Database", true);
  check_database(PROHITS_PROTEINS_DB, "Protein class Database");
  if(!defined('DISABLE_RAW_DATA_MANAGEMENT') or !DISABLE_RAW_DATA_MANAGEMENT){
    check_database(MANAGER_DB, "msManager Database", true);
  
  ?>
  <tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      $storage_folder_arr = array();
      $storage_folder = STORAGE_FOLDER;
      $storage_folder_iswritable = '<font color=red>No</font>';
      if(!isset($BACKUP_SOURCE_FOLDERS) or !count($BACKUP_SOURCE_FOLDERS)){
        $error .= "<li>No raw file backup has been set<br>";
      }else{
        $storage_folder = add_folder_backslash($storage_folder);
        $action = "&action=isWritable";
        $path = $storage_folder;
        $url = "http://".STORAGE_IP.$prohits_web_root."/msManager/process_storage_folder.php?path=";
        $storage_folder_arr = file($url.$path.$action);
				 
        if(!$storage_folder_arr[0]){
          $error .= "The Storage folder is not writable for apache user '$apache_user'. Please change it to be writable.<br>";
        }else if($storage_folder_arr[0] == '2'){
          $error .= "The Storage folder doesn't exist. Please create the folder and make it writable for apache user '$apache_user'.<br>";
        }else{
          $storage_folder_iswritable = "<font color='green'>Yes</font>";
          $storage_folder_arr = file($url);
          
        }
      }
      ?>
      <td align=center><font face="Arial">Prohits Storage</font><br><img src=./images/computer2.gif border=0></td>
      <td>
      <li>Storage computer: <?php echo STORAGE_IP;?><br>
      <li>Storage Directory: <b><font color="green"><?php echo STORAGE_FOLDER;?></font></b><br>
      <li>Storage Database: <?php echo MANAGER_DB;?><br>
      <li>Storage Directory is writable: <?php echo $storage_folder_iswritable;?><br>
      <?php
      if(!@mysqli_select_db($localhost_link, MANAGER_DB)){
          $error .= "<li>The database ".MANAGER_DB. ". is not accessible for user USERNAME in conf file<br>".mysqli_error($localhost_link)."<br>";
          fatal_Error($error);
      }else{
        $manager_db_ok = true;
      }
      ?>
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
       
      ?>
      </td>
  </tr>
      <?php
      $ms_tables_arr = get_ms_tables();
      
      if(is_array($BACKUP_SOURCE_FOLDERS) and count($BACKUP_SOURCE_FOLDERS)){
        foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
          if($baseTable == 'LTQ_DEMO') continue;
          _check_backup($baseTable, $var_arr);
        }
      } 
 
      include('check_search_engines.php');
 
 }else if(!ENABLE_UPLOAD_SEARCH_RESULTS){?>
  <tr bgcolor=white>
      <td>Enable upload search results</td>
      <td><font color="#FF0000">Please set ENABLE_UPLOAD_SEARCH_RESULTS at 1 in conf file since the data management is turned off.</font></td>
      <td align=center><img src='./images/check_no.gif'></td>
  </tr>
 <?php
 }else{
 ?>
  <tr bgcolor=white>
    <td colspan=3 align=center><b><font color="#008000">DISABLE_RAW_DATA_MANAGEMENT = 1<br>
      Prohits connection with mass spec machine, raw data handling and auto-search are disabled.</font>
    </b></td>
  </tr> 
 <?php
 }//end of PROHITS_PROTEINS_DB
?>
  <tr bgcolor=white>
    <td colspan=3 align=center><b>End of the check list</b></td>
  </tr>
  </table>
</td>
</tr> 
</table> 
<br><br>
<?php
ini_set('default_socket_timeout', $old);

function _add_folder_backslash($folder){
  if(!preg_match("/^\//",$folder)){
    $folder = "/" . $folder;
  }
  if(!preg_match("/\/$/",$folder)){
    $folder .= "/";
  }
  return $folder;
}
function _check_backup($baseTable, $var_arr){
 global $url;
 global $storage_folder;
 global $apache_user; 
 //global $localhost_link;
 global $manager_db_ok;
 global $prohits_root;
 global $prohits_web_root;
 global $ms_tables_arr;
 $logo_dir_web_path = $prohits_web_root."/msManager/images/msLogo/";
 $logo_dir_path = $prohits_root."/msManager/images/msLogo/";
 $no_backup_set = '';
?>
<tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      if(is_file($logo_dir_path . strtoupper($baseTable)."_logo.gif")){
        $logo_image = $logo_dir_web_path . strtoupper($baseTable)."_logo.gif";
      }else{
        $logo_image = $logo_dir_web_path . "default_logo.gif";
      }
      ?>
      <td align=center>
        <font face="Arial"><?php echo $baseTable;?> Backup Setting</font><br>
        <table cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align=right><img src=./images/computer_ms.gif border=0></td>
            <td><img src=<?php echo $logo_image;?> border=0 width=80></td>
        </tr>
        </table>
      </td>
      <td>
      <li>Backup source dir: 
      <?php
      if(!$var_arr['SOURCE']){
        echo '<font color=green><b>NO BACKUP TO BE SET</b></font><br>';
        $no_backup_set = 'Y';
      }else{
        echo $var_arr['SOURCE'].'<br>';
        echo "<li>Backup source dir is readable: ";
        $var_arr['SOURCE'] = add_folder_backslash($var_arr['SOURCE']);
        $action = '&action=isDir';
        $path = $var_arr['SOURCE'];
        $tmp_arr = file($url.$path.$action);
        if(!$tmp_arr[0]){
          $error .= "Please make sure that the source directory exists and readable for apache user '$apache_user'.<br>";
          echo "<font color='red'>No</font><br>";
        }else{
          echo "<font color='green'>Yes</font><br>"; 
        }
        echo "<li>Backup source dir is mounted: ";
        $action = '&action=isEmpty_or_unexist';
        //echo $url.$path.$action."<br>";exit;
        $tmp_arr = file($url.$path.$action);
        if($tmp_arr[0] == '2'){
          $error .= "The source directory is empty. 
          Please followe the instruction of 'Mount Mass spec Machine Computers to Prohits' from install_readme to mounted the mass spec computer raw file folder. <br>";
          echo "<font color='red'>No</font><br>";
        }else if($tmp_arr[0] == '1'){
          $error .= "The source directory doesn't exist. Please make the folder and mount it to the mass spec computer folder<br>";
          echo "<font color='red'>No</font><br>";
        }else{
          echo "<font color='green'>Yes</font><br>"; 
        }
      }
      $path = $storage_folder.$baseTable."/";
      echo "<li>Destination dir: " . $path."<br>";
      echo "<li>Destination dir is writable: ";
      $action = '&action=isWritable';
      //echo $url.$path.$action;exit;
      $tmp_arr = file($url.$path.$action);
      if(!$tmp_arr[0] or $tmp_arr[0] == '2'){
        //if(!$no_backup_set){
          $error .= "Please make sure that the destination directory exists and writable for apache user '$apache_user'.<br>";
        //}
        echo "<font color='red'>No</font><br>";
      }else{
          echo "<font color='green'>Yes</font><br>"; 
      }
      if($manager_db_ok){
        if(!in_array($baseTable, $ms_tables_arr)){ 
          $error .= "Database error: ".$baseTable. " doesn't exist.<br>";
        }else{
           if(in_array($baseTable."SearchTasks", $ms_tables_arr)){ 
                if(!in_array($baseTable."SaveConf", $ms_tables_arr)){
                   $error .= "Database error: ".$baseTable."SaveConf" . " doesn't exist.<br>";
                }
                if(!in_array($baseTable."SearchResults", $ms_tables_arr)){
                   $error .= "Database error: ".$baseTable."SearchResults" . " doesn't exist.<br>";
                }
                if(!in_array($baseTable."tppTasks", $ms_tables_arr)){
                   $error .= "Database error: ".$baseTable."tppTasks" . " doesn't exist.<br>";
                }
                if(!in_array($baseTable."tppResults", $ms_tables_arr)){
                   $error .= "Database error: ".$baseTable."tppResults" . " doesn't exist.<br>";
                }
                if(!$error){
                  echo "<li>'Auto-search' is set for the machine. <br>All tables exist.(".$baseTable.", ".$baseTable."SearchTasks, ".$baseTable."SearchResults, ".$baseTable."SaveConf, ".$baseTable."tppTasks, ".$baseTable."tppResults)<br>";
                }
           }else{
             echo "<li>'Auto-search' is not set for the machine. <br>If you want to set it to auto-search please use 'Backup Setup' tool in Admin Office.<br>";
           }
        }
      }else{
        $error .= "Please correct previous error then run this script again.<br>";
      }
      ?>
      
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
       
      ?>
      </td>
  </tr>
<?php
}
function check_database($db, $db_lable, $checkremote=false){
  global $theaction;
  global $PHP_SELF;
  global $localhost_link;
  global $remote_link;
  global $mysql_support;
?>
<tr bgcolor=white>
      <?php
      $error = '';
      $msg = '';
      ?>
      <td align=center><font face="Arial"><?php echo $db_lable;?> Permission</font><br><img src=./images/computer_db.gif border=0></td>
      <td>
      <li>Database: <b><font color="green"><?php echo $db;?></font></b><br>
      <?php
  if($mysql_support){
      if($localhost_link){
        if(!@mysqli_select_db($localhost_link, $db)){
          $error .= "<li>The database ".$db. ". is not accessible for user USERNAME in conf file<br>".mysqli_error($localhost_link);
          fatal_Error($error);
        }else{
          echo "<li>The database is accessible as ".mysqli_get_host_info($localhost_link)."<br>";
          //if($theaction==$db){
             
             @mysqli_query($localhost_link, "DROP TABLE IF EXISTS `test`");
             $SQL = "CREATE TABLE `test` (`fname` VARCHAR( 20 ))";
             @mysqli_query($localhost_link, $SQL);
             if(!mysqli_error($localhost_link)){
                @mysqli_query($localhost_link, "DROP TABLE IF EXISTS `test`");
                echo "<li>Database ". $db." permission: OK (for ". mysqli_get_host_info($localhost_link).") <br>";
             }else{
                $error .= "<li>The database ".$db. ". is not writable for user USERNAME<br>".mysqli_error($localhost_link)."<br>";
             }
          //}
          //echo "[<a href=$PHP_SELF?theaction=$db>check creating table permission</a>]<br>";
        }
      }
      if($checkremote and $remote_link){
         if(!@mysqli_select_db($remote_link, $db)){
            $error .= "<li>The database ".$db. ". is not accessible from remote for user USERNAME in conf file<br>".mysqli_error($remote_link);
            fatal_Error($error);
         }else{
            echo "<li>The database is accessible as ".mysqli_get_host_info($remote_link)."<br>";
         }
      }
      if($error){
        $error .= "<br>Please make suer that the database <b>".$db."</b> has been created in the server and set full permission for user <b>".USERNAME."</b>";
        fatal_Error($error);
      }
   }else{
     $error .= "Please make the web server to support Mysql.";
     fatal_Error($error);
   }
      ?>
      
      <font color=red><?php echo $error;?></font>
      <font color=green><?php echo $msg;?></font>
      </td>
      <td align=center>
      <?php
      $img = ($error)?"check_no.gif":"check_yes.gif";
      echo "<img src=./images/$img border=0>";
       
      ?>
      </td>
  </tr>
<?php
}
//-------------------------------------------------------------------
function test_ftp_site(){
//------------------------------------------------------------------- 
	$NCBI_FTP="ftp.ncbi.nlm.nih.gov";
	$NCBI_gene_path="/gene/DATA/";
	$NCBI_ftp_username="anonymous";
	$NCBI_ftp_password="nobody@nobody.com";
    $conn_id = ftp_connect($NCBI_FTP, 21, 10);
     
    if(!$conn_id) {
        return "It cannot connect: $NCBI_FTP. If the ftp site address is correct, please check the DNS address in this computer and firewall for ftp port both this computer and your institute.";
    }
    $login_result = ftp_login($conn_id, $NCBI_ftp_username, $NCBI_ftp_password);
    if(!$login_result) return "$NCBI_FTP connected, but login incorrect";
    ftp_pasv($conn_id, true);
    $contents = ftp_nlist($conn_id, $NCBI_gene_path);
    
    if($contents === false){
	 	   return  "$NCBI_FTP connection test fail. If your Prohits server firewall is on and DNS and FTP should be trusted.";   
    }
    return true;
}
function fatal_Error($msg){
  echo "<font color=red size=+2>$msg <br> Please run the script again after fix this error.</font>";
  exit;
}
function get_ms_tables(){
  global $manager_db_ok;
  global $localhost_link;
  $ms_tables_arr = array();
  if(!$manager_db_ok) return $ms_tables_arr;
  $SQL = "SHOW TABLES FROM ".MANAGER_DB;
  $result = mysqli_query($localhost_link, $SQL);
  if(!$result){
     echo 'MySQL Error: ' . mysqli_error();
     exit;
  }
  while($row = mysqli_fetch_row($result)){
    array_push( $ms_tables_arr, $row[0]);
  }
  return $ms_tables_arr;
}
//--------------------------------------------
function commandCheck($cmd, $returnAll=false){
//--------------------------------------------
  $output = array();
  exec("$cmd 2>&1", $output);
  if(isset($output[0])){
    if(preg_match("/command not found|No such file or directory/", $output[0], $matches)){
      return false;
      
    }else{
      if($returnAll){ 
        return $output;
      }else{
        return $output[0];
      }
    }
  }else{
    return 'OK';
  }
}
//--></body></html>
?>

