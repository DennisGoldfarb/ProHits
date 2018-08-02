
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

error_reporting(E_ALL);

ini_set("memory_limit","858M");

//$searchThis = '';
//$ListType = '';
$sub = '';
$theaction = '';
$Gel_ID = '';
$Bait_ID = '';
$Exp_ID = '';
$type = '';

//need set path
//all page needs the correct path to include this page at the top of page.

//$searchThis = trim($searchThis); 
//$PHP_SELF = $_SERVER['PHP_SELF'];
$Prohits_path = dirname(dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR . $Prohits_path . PATH_SEPARATOR . $Prohits_path . '/common/phpseclib0.2.2');

 
require("config/conf.inc.php");
include("common/mysqlDB_class.php");
require("common/user_class.php");
 
require("common/auth_class.php");
require("common/log_class.php");
require_once("common/project_class.php"); 
if(!isset($expireTime)){
  $expireTime = 3600*SESSION_TIMEOUT;
}
 
//ini_set('session.gc_maxlifetime',$expireTime); 
session_set_cookie_params($expireTime);
session_start();
if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}
 
if(isset($_SESSION["workingDBname"])){
  $HITSDB = new mysqlDB($_SESSION["workingDBname"]);
}else{
  $HITSDB = new mysqlDB(PROHITS_DB);
}
$PROHITSDB = new mysqlDB(PROHITS_DB);
$mainDB = new mysqlDB(PROHITS_DB);

$currentDBname = $PROHITSDB->selected_db_name;
$logincheck = true;

$SCRIPT_NAME = basename($_SERVER["PHP_SELF"]);
$DIR_NAME = dirname($_SERVER["PHP_SELF"]); 
$DIR_NAME = substr(strrchr($DIR_NAME, "/"), 1);
$PHP_SELF = $_SERVER["PHP_SELF"];
$PROHITS_IP = $_SERVER['SERVER_NAME'];
$PROHITS_NAME = $_SERVER['SERVER_NAME'];

$gpm_ip = $PROHITS_IP;
$tpp_ip = $PROHITS_IP;


if(!isset($_SESSION['USER']->ID) ){
   $logincheck = false;
}
/*
if(SESSION_TIMEOUT and $logincheck){
  $expires =@time() - 3600 * SESSION_TIMEOUT;
  if($_SESSION['logintime'] < $expires ){
	   $logincheck = false;    
	}
}
*/
if(!$logincheck){
  $_SESSION = array();
  if (isset($_COOKIE[session_name()])) {
     setcookie(session_name(), '',@time()-42000, '/');
  }
  if (isset($_COOKIE['auth'])) {
    setcookie('auth', "",@time());
  }
  session_destroy();
  header ("Location: ../");
  exit;
}

if($DIR_NAME == "analyst" && $SCRIPT_NAME == "index.php"){
  $_SESSION['MODEL'] = "project";
}else if($DIR_NAME == "admin_office" && $SCRIPT_NAME == "index.php"){
  $_SESSION['MODEL'] = "admin";
}else if($DIR_NAME == "msManager" && $SCRIPT_NAME == "index.php"){
  $_SESSION['MODEL'] = "rawData";
}
$AccessUsers = '';
$AccessProjectID = 0;
$AccessProjectTaxID = 0;
$AccessProjectName = '';
$AccessProjectSpecies = '';
$setID = '';

if(isset($change_project) && $change_project){
  $current_Project = new Projects($change_project);
  $_SESSION["workingProjectID"] = $current_Project->ID;
  $_SESSION["workingProjectName"] = $current_Project->Name;
  $_SESSION["workingProjectTaxID"] = $current_Project->TaxID;
  $_SESSION["workingFilterSetID"] = $current_Project->FilterSetID;
  $_SESSION["workingDBname"] = $HITS_DB[strtolower($current_Project->DBname)];
  if($current_Project->Frequency){
    $_SESSION["workingProjectFrequency"] = $current_Project->Frequency;
  }else{
    $_SESSION["workingProjectFrequency"] = 0;
  }
  $AUTH = new Auth($_SESSION['USER']->ID, "project", $_SESSION["workingProjectID"]); 
  $_SESSION['AUTH'] = $AUTH;
}

if($_SESSION['MODEL'] == "admin"){
  if($SCRIPT_NAME == "create_bio_filter.php"){
    $SCRIPT_NAME_tmp = "filter.php";
  }else{
    $SCRIPT_NAME_tmp = $SCRIPT_NAME;
  }   
  if(in_array($SCRIPT_NAME_tmp, $_SESSION['USER']->AccessPages_arr)){
    $AUTH = new Auth($_SESSION['USER']->ID, "page", "", $SCRIPT_NAME_tmp);
    if(!$AUTH->Access){
      header ("Location: noaccess.html");
      exit;
    }
  }else{
    $SQL = "SELECT ScriptName FROM Page WHERE ScriptName='$SCRIPT_NAME_tmp'";
    $ScriptNameArr = $PROHITSDB->fetch($SQL);    
    if($ScriptNameArr){
      header("location: /");
      exit;
    }
  }  
}else if($_SESSION['MODEL'] == "project"){
  
   
  $Projects = new Projects($PROHITSDB->link); 
  $Projects->getAccessProjects($_SESSION["USER"]->ID);
    
   
  if($Projects->count == 0){
    header("location: /");
    exit;
  }
  if(isset($_SESSION["workingProjectID"]) ){
    if($SCRIPT_NAME == "index.php"){      
      $AUTH = new Auth($_SESSION['USER']->ID, "project", $_SESSION["workingProjectID"]); 
      if(!$AUTH->Access){      
        unset($_SESSION["workingProjectID"]); 
        unset($_SESSION["workingProjectName"]);
        unset($_SESSION["workingProjectTaxID"]);
        unset($_SESSION["workingFilterSetID"]);
        unset($_SESSION["workingDBname"]);
        unset($_SESSION["workingProjectFrequency"]);
        unset($_SESSION["superUsers"]);
        header ("Location: ./index.php");    
        exit;
      }
      $_SESSION['AUTH'] = $AUTH;
    }else if($_SESSION["workingDBname"]){           
      $AUTH = $_SESSION['AUTH'];
      if($_SESSION["workingDBname"] != $currentDBname){
        $currentDBname = $_SESSION["workingDBname"];
        $mainDB->change_db($currentDBname);
      }  
    }
  }else{
    if($SCRIPT_NAME != "index.php"){
      header ("Location: ./index.php");    
      exit;
    }
  }
}


 
$analyst_this_page_permission_arr = array('Insert' => 0,'Modify' => 0,'Delete' => 0);
$SQL = "SELECT ID FROM Page WHERE ScriptName='$SCRIPT_NAME'";
if($tmp_arr = $PROHITSDB->fetch($SQL)){
  $SQL = "SELECT `Insert`, 
                 `Modify`, 
                 `Delete` 
                  FROM PagePermission
                  WHERE UserID='".$_SESSION['USER']->ID."'
                  AND PageID='".$tmp_arr['ID']."'";
  if($tmp_permission_arr = $PROHITSDB->fetch($SQL)) $analyst_this_page_permission_arr = $tmp_permission_arr;
}
if(isset($_SESSION["workingProjectID"])){

  
  $AccessProjectID = $_SESSION["workingProjectID"];
  $AccessProjectName = $_SESSION["workingProjectName"];
  $AccessProjectTaxID = $_SESSION["workingProjectTaxID"]; 
  $AccessProjectSetID = $_SESSION["workingFilterSetID"];
  $AccessProjectFrequency = $_SESSION["workingProjectFrequency"];
  $AccessDBname = $_SESSION["workingDBname"];
  $SuperUsers = 0;
}
$AccessUserID = $_SESSION['USER']->ID;
$AccessUserName = $_SESSION['USER']->Fname." ".$_SESSION['USER']->Lname;
$AccessUserType = $_SESSION['USER']->Type;
$USER = $_SESSION['USER'];
if(isset($_SESSION['AUTH'])){
  $AUTH = $_SESSION['AUTH'];
}
if(!$AccessProjectID and $DIR_NAME == "analyst" and $SCRIPT_NAME != "index.php"){
   header ("Location: ./index.php");    
   exit;
}
/*echo "<pre>";
print_r($_SESSION);
echo "</pre>";*/
?>
