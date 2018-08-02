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

require("../common/site_permission.inc.php");
require_once("msManager/is_dir_file.inc.php");

if( getenv("HTTP_CLIENT_IP")){
 $ip = getenv('HTTP_CLIENT_IP');
}else if ( getenv("HTTP_X_FORWARDED_FOR")) {
 $ip = getenv('HTTP_X_FORWARDED_FOR');
}else{
 $ip = getenv('REMOTE_ADDR');
}
if(is_file("../TMP/comparison/$ip.txt")){
?>
<html>
<head>
<title>print report</title>
</head>
<body>
<center>
<img src="./comparison_gif_pop.php?ip=<?php echo $ip;?>" border=0>
<br>
<a href='javascript: window.close();'>[Close Window]</a>
<!--a href="./comparison_gif_pop.php?ip=<?php echo $ip;?>">[aaaaaaa]</a-->
</center>
</body>
</html>

<?php 
}
?>