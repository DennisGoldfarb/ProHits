<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/

require("../config/conf.inc.php");
require("./mysqlDB_class.php");
require("./user_class.php");

$mainDB = new mysqlDB(DEFAULT_DB);
session_start();
$mainDB->execute("delete from Session where SID='". session_id() . "'");
if(isset($_SESSION['demosid']) and $_SESSION['demosid']){
  $demosid = $_SESSION['demosid'];
  $_SESSION = array();
  $_SESSION['demosid'] = $demosid;
  header ("Location: ../index.php?demosid=$demosid");
  exit;
}
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
?>
