<?php
include("./ms_permission.inc.php");
include("msManager/common_functions.inc.php");
echo "<html>
<head> 
<link rel='stylesheet' type='text/css' href='./ms_style.css'>
<title></title>
 
</head>
<body>
<table border=1 cellpadding=0 cellspacing=0 width=100% bgcolor=#6699cc>
<tr>
<td align=center>
<div class='divBoxPop'>
<table border='0' cellpadding='0' cellspacing='0' width=98%>
 <tr>
   <td width=120><img src='./images/msplit.gif' border=0>&nbsp;</td>
   <td align=left><span class='pop_header_text'>MSPLIT-DIA Library</span><br>
   </td>
 </tr>
 <tr>
    <td colspan=2 height=1 bgcolor=black><img src=./images/pixel.gif></td>
 </tr>
 <tr>
  <td colspan=2><br>
<pre>";

$file =  get_msplit_lib_file_path();
$lines = file($file);
echo $file."\n";
foreach($lines as $line){
    echo $line;
   
}
?>
</pre>
 </td>
 </tr>
</table>
</div>
 </td>
 </tr>
</table
</body>
</html>