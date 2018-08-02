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
set_time_limit(3600*2);


$frm_username = '';
$PHP_SELF = '';
$frm_password = '';
$frm_table = '';
$theaction = '';
$frm_host = '';
$mysql_link = '';
$frm_db = '';
$frm_delimiter = '';

require_once("../msManager/is_dir_file.inc.php");

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}


$username = $frm_username;
$dbpassword = $frm_password;
$host = $frm_host;
$table_name = $frm_table;

if($username and $host and $dbpassword) {
  $mysql_link = mysqli_connect($host, $username, $dbpassword, $frm_db);
}
 
if($frm_table and $mysql_link){
  //get table structure info
  $table_name = $frm_table;
  $SQL = "select * from $table_name limit 1 ";
	$result = mysqli_query ($mysql_link, $SQL);
	$fields = mysqli_field_count($mysql_link);
	$rows   = mysqli_num_rows ($result);
	$i = 0;
	$table = $table_name;

	//echo "<b>".$table."</b> table has ".$fields." fields and ".$rows." records <BR>";
  //echo "The table has the following fields <BR>";
	while ($i < $fields) {
      $finfo = mysqli_fetch_field_direct($result, $i);
	    $type[$i]  = $finfo->type;
	    $name[$i]  = $finfo->name;
	    $len[$i]   = $finfo->max_length;
	    $flags[$i] = $finfo->flags;
	    //echo $type[$i]." ".$name[$i]." ".$len[$i]." ".$flags[$i]."<BR>";
	    $i++;
	}
}
//preview
if($theaction == 'preview' and $frm_import_file_name){
    
    $tmp_import_file = "./tmp/tmp_import_file_$REMOTE_ADDR.txt";
    $myshellcmd = "mv " . $frm_import_file . " $tmp_import_file";
    $uploaded_file_name = $frm_import_file_name;
    exec($myshellcmd);
		if($frm_other_delimiter){
			$delimiter = $frm_other_delimiter;
		}else{
			$delimiter = $frm_delimiter;
		}
    //process fields_enclose charactor
		if($frm_fields_enclosed_by){
      $frm_fields_enclosed_by = stripslashes($frm_fields_enclosed_by);
      $fd = fopen ($tmp_import_file, "r");
      $fd_tmp = fopen("./tmp/tmp_import_file_$REMOTE_ADDR.modified.txt", "w");
      //go through the oraginal file and remove new line char between fields enclosed chars
      while (!feof ($fd) ) {
			  $buffer = fgets($fd, 4096);
			  $buffer_tmp = trim($buffer);
        if($buffer_tmp){
          $buffer = str_replace("\n","",$buffer);
          //this line has fields enclosed char
          //if find 2 '"' in same line delete '"'
          if(strchr($buffer, $frm_fields_enclosed_by)){
           
            if(strrpos($buffer,'"') !=strpos($buffer,'"')){ 
              $buffer = str_replace('"','',$buffer); 
            }
          }
          if(strchr($buffer, $frm_fields_enclosed_by)) {
             if(!$enclosed_start) {
                $enclosed_start = 1;
                $enclosed_sotp = 0;
             }else {
                $enclosed_start = 0;
                $enclosed_sotp = 1;
             }
          }
          if($enclosed_start) {
            fwrite($fd_tmp, $buffer . "AAAFRANKAAA");
          }else{
             fwrite($fd_tmp, $buffer . "\n");
          }
        }
      }
       fclose ($fd);
       fclose($fd_tmp);
       $tmp_import_file = "./tmp/tmp_import_file_$REMOTE_ADDR.modified.txt";
       //echo $tmp_import_file;
    }
    
    //*******************************************
   if($table_name == "UploadMDS" or $table_name == "UploadMDS2" ){
     require("../classes/yeastDB_class.php");
     require("../db/dbstart.php");
     $YeastDB = new YeastDB();
   }
   $fd = fopen ($tmp_import_file, "r");
   $out_preview =  "<table border=1>";
    
   //only display 20 records in preview
   while (!feof ($fd) and $stop!=20) {
     $buffer = fgets($fd, 4096);
     $row = explode($delimiter, $buffer);
     if($buffer){
	$out_preview .= "<tr>";
	for($i=0; $i < count($row); $i++){
           //============ for UploadMDS only ================= 
           if($table_name == "UploadMDS"  or $table_name == "UploadMDS2"){
               if(strstr($row[$i], "ID #:")){
                 $new_Bait = trim(strtoupper($row[$i+1]));
                 $new_Bait = str_replace("-E","",$new_Bait);
                 $new_Bait = str_replace("-PRE","",$new_Bait);
                 $YeastDB->fetchORForGene($new_Bait);
                 if(!$YeastDB->ORFName){
                    $err_msg .= display_update_yeastDB($new_Bait,'');
                 }
               }else if(preg_match ("/gi\|[0-9]*/", $row[$i], $tmp)){
                  $tmp_arr = explode("AAAFRANKAAA",$row[$i]);
                  for($j=0;$j<count($tmp_arr);$j++){
                    if(preg_match ("/gi\|[0-9]*/", $tmp_arr[$j], $tmp)){
                      $new_GI = str_replace("gi|","",$tmp[0]);
                      $tmp_YeastDB = new YeastDB();
                      $tmp_YeastDB->fetch( '','',$new_GI);
                      if(!$tmp_YeastDB->ORFName){
                         $err_msg .= display_update_yeastDB('',$new_GI);
                      }
                    }
                  }//end for
               }
           }
           //==================================================
 
           $out_preview .= "<td>$row[$i]&nbsp;</td>";
	}
	$out_preview .= "</tr>";
	//$stop++;
     }//end if buffer
  }//end while
  $out_preview .= "</table>";
  if($table_name == "UploadMDS" or $table_name == "UploadMDS2"){
     $out_preview .= "Please click <a href='add_new_yeastDB.php' target=_blank>Here</a> to update the local yeast database.<br>"; 
     $out_preview .= $err_msg;
  }
  $out_preview .= "<br><font color=red><b>This is the end of uploaded file</b></font>";
  fclose ($fd);
}
if($theaction == 'insert' and _is_file($tmp_import_file)){
	  if($frm_other_delimiter){
			$delimiter = $frm_other_delimiter;
		}else{
			$delimiter = $frm_delimiter;
		}
		$fd = fopen ($tmp_import_file, "r");
	  //empty the table
  //**********************************
  if($table_name == "UploadMDS" or $table_name == "UploadMDS2"){
  //**********************************
    mysqli_query($mysql_link, "delete from $table_name");
		while (!feof ($fd) ) {
			$buffer = fgets($fd, 4096);
			//$buffer = trim($buffer);
			$row = explode($delimiter, $buffer);
			//echo $buffer;
			$SQL = "insert into $table_name set";
			for($i=0; $i < count($name); $i++){
				if($i){
					$SQL .= ",";
				}
				$tmp_value = addslashes(trim($row[$i]));
				$SQL .= " $name[$i]='$tmp_value'";
			}
			
			if($buffer){
			  mysqli_query($mysql_link, $SQL);
		   	  //echo $SQL."<br>";
			}
	  }//end while
  //******************
  }//end if UploadMDS
  //******************
	  fclose ($fd); 
		$successful_msg = "Data has been imported successully";
}
?>
<html>
<head><title>Import data</title>
<script language='javascript'>

function db_selected(){
  document.forms[0].submit();
}
function table_selected(){  
  var sel = document.forms[0].frm_table;
  if(sel.options[sel.selectedIndex].value == ''){
		alert("Pleast select a table!");
	} else {
		document.forms[0].submit();
	}
}
function preview(){
	document.forms[0].theaction.value = 'preview';
	if(document.forms[0].frm_import_file.value == ''){
		alert("pleas upload a file!");
		return false;
	}
	if(!checkDelimiter() && document.forms[0].frm_other_delimiter.value == ''){
		alert("Please select a delimiter!");
		return false;
	}
	document.forms[0].submit();
}
function checkDelimiter() {
  for (var i=0; i < document.forms[0].frm_delimiter.length; i++) {
    if (document.forms[0].frm_delimiter[i].checked){
      return true;
		}
  }
  return false;
}
function clearRadio(){
	for (var i=0; i < document.forms[0].frm_delimiter.length; i++) {
     document.forms[0].frm_delimiter[i].checked = false;
      
  }
}
function saveToMysql(){
	document.forms[0].theaction.value = 'insert';
	document.forms[0].submit();
}
</script>
</head>
<body>
<h1><font face="Arial" color=red>Import Data Into MYSQL database</font></h1>
<pre>
This is a tool which can import data from text file. Before you run 
this script you should create table in your mysql database. and make
sure thate field type match the text file.
</pre>
 <br><strong><font size="+1">Step 1: Select database and table</font></strong>
<form method=post name=db_form action='' enctype="multipart/form-data">
<input type=hidden name=frm_host value='<?php  echo $frm_host; ?>'>
<input type=hidden name=frm_username value='<?php  echo $frm_username; ?>'>
<input type=hidden name=frm_password value='<?php  echo $frm_password; ?>'>
<input type=hidden name=theaction value=''>
<input type=hidden name=uploaded_file value='<?php echo $uploaded_file_name;?>'>
<input type=hidden name=tmp_import_file value='<?php echo $tmp_import_file;?>'>
<table border=3>
<?php  if(!$mysql_link) { ?>
  <tr>
    <td>
      Host Name:
	</td>
	<td>
	  <input name=frm_host value='<?php echo  ($frm_host)?$frm_host:"localhost"; ?>'>
	</td>
  </tr>
  <tr>
	<td>
	User Name:
	</td>
	<td>
	  <input name=frm_username value=<?php  echo $frm_username; ?>>
	</td>
  </tr>
  <tr>
    <td>
      Password:
	</td>
	<td>
	  <input type=password name=frm_password value=<?php  echo $frm_password; ?>>
	</td>
  </tr>

<?php  }else{ ?>
  <tr>
	<td>
	  Select Database:
    </td>
	<td>
     <select name="frm_db" onchange="javascriopt: db_selected()">
	  <option value=''>--select database--
<?php 
if($mysql_link){
  $db_list = mysqli_query($mysql_link, "SHOW DATABASES");
   while ($row = mysqli_fetch_array($db_list)) {
     if($frm_db == $row[0]){
       echo "<option value='".$row[0]."' selected>".$row[0]."\n";
	 }else{
	   echo "<option value='".$row[0]."' >".$row[0]."\n";
	 }
   }
}
?>
    </select>
	</td>
  </tr>
  <tr>
	<td>
      Select Table:
	</td>
    <td>
	  <select name="frm_table">
     <option value=''>--select table--
<?php 
if($mysql_link){
  if($frm_db){
	  $tables=@mysqli_query( $mysql_link, "SHOW TABLES FROM $frm_db" );
	  while ($bla=@mysqli_fetch_array($tables)) {
	     //echo $bla." sind die tabellennamen";
		 if($frm_table == $bla[0]){
	       echo "<option value='".$bla[0]."' selected>".$bla[0]."\n";
		 }else{
		   echo "<option value='".$bla[0]."' >".$bla[0]."\n";
		 }

	   }//end while
   }
}
?>
   </select>
   </td>
   </tr>
<?php  } ?>
   <tr>
     <td colspan=2 align=center>
<?php  if($mysql_link){ ?>
     <input type=button value="Select Table" onClick="javascript: table_selected();">
<?php  }else{ ?>
	 <input type=button value="Connect to Mysql" onClick="document.forms[0].submit();">
<?php  } ?>
     </td>
   </tr>
  </table>
<?php if($frm_table){?>
   <br><strong><font size="+1">Step 2: Upload text file</font></strong>
  <table border = 1>
	<tr>
		<td>upload data file: </td>
		<td><input type='file' name='frm_import_file' size='30'></td>
	</tr>
	<tr>
		<td>Field delimiter:</td>
		<td><input type=radio name=frm_delimiter value='	' <?php echo ($frm_delimiter=='	' or !$frm_delimiter)?'checked':''; if(!$frm_delimiter) ?>> Tab &nbsp;
				<input type=radio name=frm_delimiter value=';' <?php echo ($frm_delimiter==';')?'checked':'';?>>Semicolon &nbsp;
 				<input type=radio name=frm_delimiter value=',' <?php echo ($frm_delimiter==',')?'checked':'';?>> Comma &nbsp;
				<input type=radio name=frm_delimiter value=' ' <?php echo ($frm_delimiter==' ')?'checked':'';?>>Space &nbsp;
				<input type=text name=frm_other_delimiter value='<?php echo stripslashes($frm_other_delimiter);?>' size=1 onFocus="javascript:clearRadio();">Other &nbsp;
		</td>
	</tr>
  <tr>
    <td>Filed encloased by</td>
    <td>
      <select name=frm_fields_enclosed_by>
          <option value="">none
          <option value='"' <?php echo ($frm_fields_enclosed_by=='\"' or !$frm_fields_enclosed_by)?'selected':'';?>>"
          <option value="'" <?php echo ($frm_fields_enclosed_by=="\'")?'selected':'';?>>'
          <option value="|" <?php echo ($frm_fields_enclosed_by=='|')?'selected':'';?>>|
      </select>
     </td>     
  </tr>
	<tr>
		<td colspan=2 align=center><input type=button value='Preview' onClick="javascript: preview()">
		</td>
	</tr>
</table>
<?php }?>
<?php if($theaction == 'preview' and is_file($tmp_import_file)){ ?>
	<br><strong><font size="+1">Step 3: Insert into MYSQL databbase</font></strong>
	<table border=0>
		<tr>
			<td align=center>  <input type=button value='empty the table then save the new data to mysql database' onClick="javascript: saveToMysql()">
		</tr>
	</table>
<?php }?>
</form>
<?php 
if(!$frm_table or !$frm_db) die;

//echo $frm_buffer;
//--------------------work with each table--------------------------------------
//output table structure
$output .= '<pre>/***************************************************************************';
$output .= "\n";
$output .=  "<b> $table_name </b>";
$output .= "\n";
$output .= ' +----------------+---------------+------+-----+---------+----------------+';
$output .= "\n";
$output .= ' | Field          | Type          | Null | Key | Default | Extra          |';
$output .= "\n";
$output .= ' +----------------+---------------+------+-----+---------+----------------+';
$output .= "\n";
for($i = 0; $i < $fields; $i++){
 $name_tmp = $name[$i];
 for($k = strlen($name[$i]); $k < 15; $k++){
   $name_tmp .= ' ';
 }
 $type_tmp = $type[$i].'('.$len[$i]. ')';
 for($k = strlen($type[$i].'('.$len[$i]. ')'); $k < 15; $k++){
   $type_tmp .= ' ';
 }
 $output .= ' | '. $name_tmp .'|'. $type_tmp.'|'. $flags[$i];
 $output .= "\n";

}
$output .= ' +----------------+---------------+------+-----+---------+----------------+';
$output .= "\n";
$output .=  '****************************************************************************/</pre>';
echo $output;
if($successful_msg){
	echo "<font size=+2 color=red>$successful_msg</font>";
  if(strstr($table_name, "UploadMDS") and strstr($username, "uploadmds") ){
     echo "<br>";
     echo "<a href='./UploadMDS.php?user=$frm_username&database=$frm_db&table=$table_name&uploaded_file=$uploaded_file' target=new><h2>Save Data to $frm_db</h2></a>";
  }
}else{
	echo $out_preview;
}
//end of file
//-------------------------
function display_update_yeastDB($ORFName='',$GI=''){
  $output = '<br>';
  if($GI){
    $output .=  "Update <b>GI|$GI</b><a href='http://www3.ncbi.nlm.nih.gov/htbin-post/Entrez/query?form=6&db=p&Dopt=g&uid=$GI' target=ncbi>NCBI</a> ";
  }else if($ORFName){
    $output .=" Update <b>$ORFName</b> <a href='http://genome-www4.stanford.edu/cgi-bin/SGD/locus.pl?locus=$ORFName' target=sgd>SGD</a>";
 }
  return $output;
}
//end of file
?>

