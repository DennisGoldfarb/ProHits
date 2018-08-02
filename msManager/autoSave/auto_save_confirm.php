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

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}
?>
<html>
<head>
<title>Untitled</title>
</head>
<body>
<form>
<img src='../images/cool_clock.gif' border=0><br>
<b>Auto-Save has been started in the ProHits server.<br>
You can click <input type='button' value='refresh'> button in the Search Results window to see the auto-save status.</b>
</form>
<script language="JavaScript" type="text/javascript">
opener.document.location = "../ms_search_results_detail.php?frm_PlateID=<?php echo $frm_PlateID;?>&taskIndex=<?php echo $taskIndex;?>&table=<?php echo $table;?>";
setTimeout("window.close()", 10000);
</script>
</body>
</html>
