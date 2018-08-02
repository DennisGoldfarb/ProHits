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
require("../common/site_permission.inc.php");
require("analyst/common_functions.inc.php");

$URL = getURL();
 
$urlGI = $GI;  
foreach($URL as $Value){
  if($Value['ProteinTag'] == "urlGI"){
    $urlString = $Value['URL'].$$Value['ProteinTag'];
    break;
  }   
}

header("location: " .$urlString);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Untitled</title>
</head>

<body>
<img src="../msTools/DNAanalyzer/images/underconstruction.gif" alt="" width="216" height="144" border="0">

http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?cmd=Retrieve&db=protein&list_uids=10835838&dopt=GenPept&term=10835838&qty=1
</body>
</html>
