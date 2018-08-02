<?php 
/***********************************************************
Author: frnak liu
    Date: 2005-04-12
    Desc: display log file. no account checking.
    apache user should has full permission for all log fies;
    
************************************************************/

$display= '';
if(array_key_exists('REQUEST_METHOD', $_SERVER)){
  if( $_SERVER['REQUEST_METHOD'] == "POST"){
    $request_arr = $_POST;
  }else{
    $request_arr = $_GET;
  }
  foreach ($request_arr as $key => $value) {
    $$key=$value;
  }
}
if(!isset($log_file) or !is_file($log_file)){
  echo "the file $log_file doesn't exisit!";exit;
}
if(!isset($display_last) or  !intval($display_last)){
  $display_last = 100;
}else{
  $display_last = intval($display_last);
}

$lines = file($log_file);
$total_lines = count($lines);
?>
<HTML>
<HEAD><SCRIPT LANGUAGE="JavaScript">
<!-- Begin hiding Javascript from old browsers.
var theWait = 10000;
 function reloadMe(){
window.location.reload(true);
}
//-- End hiding Javascript from old browsers. -->
</SCRIPT><TITLE>Process status</TITLE></HEAD>
<BODY>
  
<pre>
<form name=frm_view method=post action=<?php echo $_SERVER['PHP_SELF'];?>>
<input type=hidden name=log_file value=<?php echo $log_file;?>>
<h2>Log File '<?php echo $log_file;?>'</h2>
<?php if($display != 'all'){?>
<b>Display last :<input type=text size=5 name=display_last value=<?php echo ($display_last)?$display_last:'100';?>>lines <input type=submit value=submit> <input value='Refresh' type=button onClick='reloadMe()'>
<?php }?>
</from>

<?php 
if($display == 'all'){
  foreach($lines as $line){
    echo $line;
  }
}else{
  for($i=$total_lines-1; $i > $total_lines - $display_last -1 ; $i--) {
    if($i > -1){
      
      echo ($i+1) . "\t" . $lines[$i];
      if($i == $total_lines-1) echo "\n";
    }
  }
}
?>
</pre>
<br><br>
<center>[<a href='javascript: window.close();'>Close Window</a>]</center>
</body>
</html>