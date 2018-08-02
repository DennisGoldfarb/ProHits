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

$bgcolordark = '#858585';
$bodycolor = '#ffffff';
$bgHitcolor="#e1e1e1";
$bgDatecolor = "#cecece";
$bigest_size = 0;
$bigest_num_file = 0;

include("./ms_permission.inc.php");
/*echo "<pre>";
print_r($request_arr);
echo "</pre>";*/

$start_date = $start_date_static = '3000-01-00';
$end_date = $end_date_static = '1900-01-00';

$time_arr = array();

$table_arr = $managerDB->list_tables();
$tableName_arr = array();
$colorArr = get_color_arr();
$matrix_arr = array();



if($tableName){
  $bar_color = array_pop($colorArr);
  $tableName_arr[$tableName] = $bar_color;
}else{
  foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
    if(array_key_exists($baseTable, $table_arr)) continue;
    $bar_color = array_pop($colorArr);
    $tableName_arr[$baseTable] = $bar_color;
  }
}
$subSQL = get_subSql_for_file_types();

foreach($tableName_arr as $tableName_key => $tableName_val){
  $matrix_arr[$tableName_key] = get_sub_total($tableName_key, $frm_date1, $frm_date2,$subSQL);
}

get_time_interval_array($start_date,$end_date);
/*
echo "<pre>";
print_r($time_arr);
print_r($tableName_arr);
print_r($matrix_arr);
echo "</pre>";
*/
if(!$time_arr){
  $tmp_date1_arr = explode("-",$frm_date1);
  $tmp_date2_arr = explode("-",$frm_date2);
  if($interval == 'yearly'){
    $frm_date1_tmp = $tmp_date1_arr[0];
    $frm_date2_tmp = $tmp_date2_arr[0];
  }else{
    $frm_date1_tmp = $tmp_date1_arr[0]."-".$tmp_date1_arr[1];
    $frm_date2_tmp = $tmp_date2_arr[0]."-".$tmp_date2_arr[1];
  }
  if($frm_date1_tmp == $frm_date2_tmp){
    $on_data_str = "There is no any data in $frm_date1_tmp";
  }else{
    $on_data_str = "There is no any data between $frm_date1_tmp and $frm_date2_tmp";
  }  
?>
<center>
<font face="Arial" size="5" color="#804040">&nbsp;<b>Raw Files Statistics</font><br><br>
  <?php echo $on_data_str?>
</center>  
<?php 
exit;
}
$_SESSION["time_arr"] = $time_arr;
$_SESSION["tableName_arr"] = $tableName_arr;
$_SESSION["matrix_arr"] = $matrix_arr;
?>
<html>
<head>
<title>Prohits</title>
<link rel="stylesheet" type="text/css" href="../analyst/site_style.css"> 
<script language="Javascript" src="ms.js"></script>
<script language="javascript">

</script>
</script>
</head>
<body>
<font face="Arial" size="5" color="#804040">&nbsp;<b>Raw Files Statistics</font>
<?php 
if(!$tableName && $show_single && $show_all){
  $show_single = '';
  if($file_size) display_info($file_size);
  if($num_file) display_info($num_file);
  $show_single = 'single';
  $show_all = '';
  if($file_size) display_info($file_size);
  if($num_file) display_info($num_file);
}else{
  if($file_size) display_info($file_size);
  if($num_file) display_info($num_file);
} 
?>  
</body>
</html>  
<?php 
exit;
function display_info($content){
  global $tableName,$file_type,$theaction,$frm_date1,$frm_date2,$interval,$show_all,$show_single;
  global $table_style,$bar_style,$line_style,$pare_style,$size_unit,$bigest_size,$bigest_num_file;
  global $tableName_arr,$time_arr,$matrix_arr,$tableWidth,$bgDatecolor;
  $bgcolordark = '#858585';
  $bodycolor = '#ffffff';
  $bgHitcolor="#e8e8e8";
  $sub_title = '';
  if($content == 'size'){
    $main_title = 'Files size';
    $sub_title = '('.$size_unit.')';
    if($show_all) $sub_title .= " [All Machines]";
  }else{
    $main_title = 'Number of Files';
    if($show_all) $main_title .= " </b><font face='Arial' size='2' color='#FFFFFF'>[All Machines]</font>";
  }
  $imageWidth = 400;
  $tital = "tmp titles";
  $border = '1';
  $orientation = 'H';
    
?>
<table border="0" cellpadding="1" cellspacing="1" width="700">
  <tr><td><hr></td></tr>
  <tr bgcolor="">
	  <td colspan="3" bgcolor="#0080c0" >
		<font face="Arial" size="3" color="#FFFFFF">&nbsp;<b><?php echo $main_title?></b></font>&nbsp;<font face="Arial" size="2" color="#FFFFFF"><?php echo $sub_title?></font>
    </td>
  </tr>
<?php if($table_style){?>  
  <tr>
  <td>
    <table border="0" cellpadding="1" cellspacing="1" >
  <?php if(!$tableName && $show_all == "all"){?>
      <tr><td bgcolor="<?php echo $bgDatecolor;?>" width="10">&nbsp;</td>
      <?php foreach($time_arr as $time_val){?>
        <td bgcolor="<?php echo  $bgDatecolor;?>" align=center><span class=maintext><b><?php echo $time_val?></b></span></td>
      <?php }?>
      </tr>
      <tr>
        <td bgcolor="<?php echo $bgHitcolor;?>" nowrap><span class=maintext>&nbsp;<b>All Machines</b>&nbsp;</span></td>
        
      <?php foreach($time_arr as $time_val){
          $sub_total_val = 0;
          foreach($tableName_arr as $tmp_table_key => $tmp_table_name){
            if(isset($matrix_arr[$tmp_table_key][ $time_val][$content])){
              $sub_total_val += $matrix_arr[$tmp_table_key][ $time_val][$content];
            }
          }
      ?>
        <td bgcolor="<?php echo  $bgHitcolor;?>" align=right nora><span class=maintext><?php echo $sub_total_val?></span></td>
      <?php     
        }
      ?>
        </tr>
  <?php }else{?>   
      <tr><td bgcolor="<?php echo $bgDatecolor;?>" width="10">&nbsp;</td>
      <?php foreach($time_arr as $time_val){?>
        <td bgcolor="<?php echo  $bgDatecolor;?>" align=center><span class=maintext><b><?php echo $time_val?></b></span></td>
      <?php }?>
      </tr>
      <?php foreach($tableName_arr as $tableName_key => $tableName_val){?>
      <tr>
        <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext>&nbsp;<b><?php echo $tableName_key?></b>&nbsp;</span></td>
      <?php foreach($time_arr as $time_val){
          $file_size = 0;
          if(isset($matrix_arr[$tableName_key][$time_val])) $file_size = $matrix_arr[$tableName_key][$time_val][$content];
      ?>
        <td bgcolor="<?php echo  $bgHitcolor;?>" align=right><span class=maintext><?php echo $file_size?></span></td>
        <?php }?>
      </tr>
      <?php }
    }
  }  
  ?>      
    </table>    
  </td>
  </tr>
<?php  
  if($line_style){
    $tmp_file = "./line_status_gif.php?tital=$tital&imageWidth=$tableWidth&border=$border&size_unit=$size_unit&tableName=$tableName&file_type=$file_type&bigest_size=$bigest_size&bigest_num_file=$bigest_num_file&show_single=$show_single&show_all=$show_all&content=$content&interval=$interval";
?>
  <tr><td>
  <table border="0" cellpadding="1" cellspacing="1" width="100%">
    <tr>
      <td width="100">
      <img src="./line_status_gif.php?tital=<?php echo $tital?>&imageWidth=<?php echo $tableWidth?>&border=<?php echo $border?>&size_unit=<?php echo $size_unit?>&tableName=<?php echo $tableName?>&file_type=<?php echo $file_type?>&bigest_size=<?php echo $bigest_size?>&bigest_num_file=<?php echo $bigest_num_file?>&show_single=<?php echo $show_single?>&show_all=<?php echo $show_all?>&content=<?php echo $content?>&interval=<?php echo $interval?>">
      <!--a href="javascript: newpopwin('<?php echo $tmp_file?>',850,1000);">table</a-->
      </td>
    </tr> 
  </table>
  </td></tr>
<?php }
  if($bar_style){
?>
  <tr><td>
  <table border="0" cellpadding="1" cellspacing="1" width="100%">
<?php 
    if(!$tableName && $show_single == "single"){
      $time_counter = 0;
      foreach($time_arr as $time_key => $time_val){
        if(!($time_key%2)) echo "<tr>";
    ?>
      <td width="100">
      <img src="./bar_status_gif.php?tital=<?php echo $tital?>&imageWidth=<?php echo $imageWidth?>&border=<?php echo $border?>&orientation=<?php echo $orientation?>&size_unit=<?php echo $size_unit?>&tableName=<?php echo $tableName?>&file_type=<?php echo $file_type?>&date_time=<?php echo $time_val?>&bigest_size=<?php echo $bigest_size?>&bigest_num_file=<?php echo $bigest_num_file?>&show_single=<?php echo $show_single?>&content=<?php echo $content?>">
      <!--a href="./bar_status_gif.php?tital=<?php echo $tital?>&imageWidth=<?php echo $imageWidth?>&border=<?php echo $border?>&orientation=<?php echo $orientation?>&size_unit=<?php echo $size_unit?>&tableName=<?php echo $tableName?>&file_type=<?php echo $file_type?>&date_time=<?php echo $time_val?>&bigest_size=<?php echo $bigest_size?>&bigest_num_file=<?php echo $bigest_num_file?>&show_single=<?php echo $show_single?>&content=<?php echo $content?>" class=button>[test]</a-->
      </td>
    <?php 
        if($time_key>=2 && ($time_key%2)) echo "</tr>";
        $time_counter++;
      }
    }else{
    ?>
      <td width="100">
      <img src="./bar_status_gif.php?tital=<?php echo $tital?>&imageWidth=<?php echo $imageWidth?>&border=<?php echo $border?>&orientation=<?php echo $orientation?>&size_unit=<?php echo $size_unit?>&tableName=<?php echo $tableName?>&file_type=<?php echo $file_type?>&date_time=<?php echo $time_val?>&bigest_size=<?php echo $bigest_size?>&bigest_num_file=<?php echo $bigest_num_file?>&show_single=<?php echo $show_single?>&show_all=<?php echo $show_all?>&content=<?php echo $content?>">
      <!--a href="./bar_status_gif.php?tital=<?php echo $tital?>&imageWidth=<?php echo $imageWidth?>&border=<?php echo $border?>&orientation=<?php echo $orientation?>&size_unit=<?php echo $size_unit?>&tableName=<?php echo $tableName?>&file_type=<?php echo $file_type?>&date_time=<?php echo $time_val?>&bigest_size=<?php echo $bigest_size?>&bigest_num_file=<?php echo $bigest_num_file?>&show_single=<?php echo $show_single?>&show_all=<?php echo $show_all?>&content=<?php echo $content?>" class=button>[test]</a-->
      </td>
    <?php 
    }    
    ?>
  </table>
  </td>
  </tr>
<?php }?>    
</table>
</td>
</tr>
<?php 
}
function get_subSql_for_file_types(){
  global $file_type,$RAW_FILES;  
  if($file_type == 'all'){
    $formal_type_arr = explode(',',$RAW_FILES);
    for($i=0; $i<count($formal_type_arr); $i++){
      $formal_type_arr[$i] = trim($formal_type_arr[$i]);
    }
    $formal_type_str = implode("','", $formal_type_arr);
    $formal_type_str = "'".$formal_type_str."'";
    $subSQL = " AND FileType IN ($formal_type_str)";
  }else{
    $subSQL = " AND FileType='$file_type'";
  }
  return $subSQL;
}    

function get_time_interval_array($frm_date1,$frm_date2){
  global $interval,$time_arr,$start_date_static,$end_date_static;
  if($frm_date1 == $start_date_static && $frm_date2 == $end_date_static) return $time_arr = array();  
  $tmpArr1 = explode('-',$frm_date1);
  $tmpArr2 = explode('-',$frm_date2);
  if($interval == 'yearly'){
    for($i=$tmpArr1[0]; $i<=$tmpArr2[0]; $i++){
      array_push($time_arr, $i);
    }
  }elseif($interval == 'monthly'){
    if($tmpArr1[0] == $tmpArr2[0]){
      $start_month = $tmpArr1[1];
      $end_month = $tmpArr2[1];
      $year = $tmpArr1[0];
      time_for_month($start_month,$end_month,$year);
    }else{
      $start_month = $tmpArr1[1];
      $end_month = 12;
      $year = $tmpArr1[0];
      time_for_month($start_month,$end_month,$year);
      for($i=$tmpArr1[0]; $i<$tmpArr2[0]-1; $i++){
        $start_month = 1;
        $end_month = 12;
        $year = $i + 1;
        time_for_month($start_month,$end_month,$year);
      }
      $start_month = 1;
      $end_month = $tmpArr2[1];
      $year = $tmpArr2[0];
      time_for_month($start_month,$end_month,$year);
    }  
  }
}

function time_for_month($start_month,$end_month,$year){
  global $time_arr;
  for($j=$start_month; $j<=$end_month; $j++){
    $month = $j;
    if(strlen($j) == 1) $month = '0'.$j;    
    $month_key = $year.'-'.$month;
    array_push($time_arr,$month_key);
  }
}

function get_sub_total($tableName, $time1, $time2,$subSQL){
  global $managerDB,$start_date,$end_date,$bigest_size,$bigest_num_file,$interval,$size_unit;
  $sub_total = array();
  if(!$tableName || !$time1 || !$time2) return $sub_total;
  $SQL = "SELECT `ID`,`Size`,`FileType`,`Date` FROM $tableName WHERE FileType<>'dir' and `Date`>='$time1' AND `Date`<='$time2'".$subSQL."ORDER BY `Date`";
  
  $tmpArr = $managerDB->fetchAll($SQL);
  if(!$tmpArr) return $sub_total;
  foreach($tmpArr as $tmpVal){
    $tmp_size = ($tmpVal['Size'])?$tmpVal['Size']:0;
    $tmp_time = explode('-',$tmpVal['Date']);
    if($interval == 'monthly'){
      $date_key = $tmp_time[0].'-'.$tmp_time[1];
    }else{
      $date_key = $tmp_time[0];
    }
    if(!array_key_exists($date_key, $sub_total)){
      $sub_total[$date_key]['size'] = $tmp_size;
      $sub_total[$date_key]['num_files'] = 1;
    }else{
      $sub_total[$date_key]['size'] += $tmp_size;
      $sub_total[$date_key]['num_files']++;
    }
    if($tmpVal['Date'] < $start_date) $start_date = $tmpVal['Date'];
    if($tmpVal['Date'] > $end_date) $end_date = $tmpVal['Date'];
  }
  foreach($sub_total as $key => $sub_val){
    if($size_unit == 'GB'){
      $sub_total[$key]['size'] = round(($sub_total[$key]['size']/1024)/1024/1024,2);
    }else{  
      $sub_total[$key]['size'] = round(($sub_total[$key]['size']/1024)/1024,1);
    }  
    if($sub_total[$key]['size'] > $bigest_size) $bigest_size = $sub_total[$key]['size'];
    if($sub_total[$key]['num_files'] > $bigest_num_file) $bigest_num_file = $sub_total[$key]['num_files'];
  }
  return $sub_total;
}

/*function get_files_detail($tableName, $time1, $time2,$subSQL,$filter){
  global $managerDB,$start_date,$end_date,$bigest_size,$bigest_num_file,$interval,$size_unit;
  $sub_total = array();
  if(!$tableName || !$time1 || !$time2) return $sub_total;
  $SQL = "SELECT `ID`,`Size`,`FileType`,`Date` FROM $tableName WHERE `Date`>='$time1' AND `Date`<='$time2'".$subSQL."ORDER BY `Date`";
  
  $SQL = "SELECT `ID`, 
                 `FileName`, 
                 `FileType`, 
                 `Date`,
                 `ProjectID`, 
                 `Size`,
                 `RAW_ID` 
          FROM $tableName
          WHERE `Date`>='$time1' 
          AND `Date`<='$time2'".$subSQL."
          ORDER BY `Date`";
  $tmpArr = $managerDB->fetchAll($SQL);
  
  if(!$tmpArr) return $sub_total;
  foreach($tmpArr as $tmpVal){
    $tmp_size = ($tmpVal['Size'])?$tmpVal['Size']:0;
    $tmp_time = explode('-',$tmpVal['Date']);
    if($interval == 'monthly'){
      $date_key = $tmp_time[0].'-'.$tmp_time[1];
    }else{
      $date_key = $tmp_time[0];
    }
    if(!array_key_exists($date_key, $sub_total)){
      $sub_total[$date_key]['size'] = $tmp_size;
      $sub_total[$date_key]['num_files'] = 1;
    }else{
      $sub_total[$date_key]['size'] += $tmp_size;
      $sub_total[$date_key]['num_files']++;
    }
    if($tmpVal['Date'] < $start_date) $start_date = $tmpVal['Date'];
    if($tmpVal['Date'] > $end_date) $end_date = $tmpVal['Date'];
  }
  foreach($sub_total as $key => $sub_val){
    if($size_unit == 'GB'){
      $sub_total[$key]['size'] = round(($sub_total[$key]['size']/1024)/1024/1024,2);
    }else{  
      $sub_total[$key]['size'] = round(($sub_total[$key]['size']/1024)/1024,1);
    }  
    if($sub_total[$key]['size'] > $bigest_size) $bigest_size = $sub_total[$key]['size'];
    if($sub_total[$key]['num_files'] > $bigest_num_file) $bigest_num_file = $sub_total[$key]['num_files'];
  }
  return $sub_total;
}*/

function get_color_arr(){
  $colorArr = array('C5CBF7','A9A850','99ffcc','AC9A72','F6B2A9','DF9DF7','884D9E','798AF9','687CFA','AE15E7',
                  'D5CCCD','586EFA','8ED0F5','69B0D8','4C90B7','54F4F6','82ACAD','909595','A0F4B8','7BBC8D',
                  '43CB69','E9E86F','A7B2F6','ffff99','99ffff','99cc00','999900','ffccff','006600','6666ff',
                  '663399','0000ff','cc3300','0099ff','9999ff','99ccff','996600','cc99ff','ff3300','ff66ff',
                  'ff00ff','99ccff','996600','00ff00','990000','993333','99cc33','9999ff','ccccff','9933cc',
                  'ffffcc','ccffff','ccff99','ccff33','E6B751','99ff00','ff00ff','6633ff','6633ff','6600ff',
                  'ffffff','66ffcc','ffcccc','66cccc','ff99cc','6699cc','ff66cc','6666cc','ff33cc','6633cc',
                  'ffff66','66ff66','ffcc66','66cc66','ff9966','669966','ff6666','666666','ff3366','663366',
                  '99ff33','00ff33','99cc33','00cc33','999933','009933','996633','006633','993333','003333',
                  '99ffcc','00ffcc','99cccc','00cccc','9999cc','0099cc','9966cc','0066cc','9933cc','0033cc');
  return $colorArr;                  
}
 ?>
