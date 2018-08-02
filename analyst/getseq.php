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
require("../config/conf.inc.php");
require("../common/common_fun.inc.php");
$hit     = '';
$description = '';
$seq = '';
//get parameters

$PARAM = array_merge($_GET, $_POST);
$hit   = $PARAM['hit'];
$pro_arr = get_protein_from_url($hit);
 
if(isset($pro_arr['sequence']) and $pro_arr['sequence']){
  $seq = strtoupper($pro_arr['sequence']);
}else{
  echo "didn't get protein $hit sequence";exit;
}  
?>
<HTML>
<BODY BGCOLOR="#ffffff" ALINK="#0000ff" VLINK="#0000ff">
<pre>
<?php 
print_r($pro_arr);
?>
</pre>
</BODY>
</HTML>

