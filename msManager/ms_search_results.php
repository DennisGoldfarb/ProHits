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
 
$start_point = 0;
$order_by = '';

define ("RESULTS_PER_PAGE", 50);
define ("MAX_PAGES", 10); //this is max page link to display 

include("./ms_search_header.php");

$PAGE_COUNTER = new PageCounter();
$caption = "Plates/Folder";
$query_string = "table=$table";

$SQL = "SELECT `ID`,`PlateID` FROM ".$tableSearchTasks." GROUP BY `PlateID` ";
$plateIDarr = $managerDB->fetchAll($SQL);
$total_records =  count($plateIDarr);
$plateIDstr = '';
$specialTaskArr = array();
foreach($plateIDarr as $tmpValue){
  if(strstr($tmpValue['PlateID'], ',')){
    $tmpArr = explode(',',$tmpValue['PlateID']);
    $specialTaskArr[$tmpValue['ID']] = $tmpArr;
  }
  if($plateIDstr) $plateIDstr .= ',';
  $plateIDstr .= $tmpValue['PlateID'];
}

if(!$plateIDstr) exit;

if($order_by) $query_string .= "&order_by=".$order_by;
$page_output = $PAGE_COUNTER->page_links($start_point, $total_records, RESULTS_PER_PAGE, MAX_PAGES, str_replace(' ','%20',$query_string)); 
if(!$order_by) $order_by = "ID desc";
if(!$start_point) $start_point = 0;

$SQL = "select ID, FileName, ProhitsID, ProjectID, Date 
        from $table T 
        where ID IN($plateIDstr) and " . $where_project . "
        order by $order_by 
        Limit $start_point, ". RESULTS_PER_PAGE;        
$plate_records = $managerDB->fetchAll($SQL);

//get this page project ids
$page_project_IDs = array();
for($i = 0; $i<count($plate_records); $i++){
  array_push($page_project_IDs, $plate_records[$i]['ProjectID']);
}
?>
 <table border=0 width=97% cellspacing=5 cellpadding=0>
    <tr><td align=center><br>
     <font face="Arial" size="+1" color="<?php echo $menu_color;?>"><b><?php echo $table;?> Search Results</b></font>
     <hr width="100%" size="1" noshade>
    </td>
    </tr>
    <tr><td> 
      <table border=0>
       <tr><td><b>Project Name</b></td><td width=50 align=center><b>ID</b></td></tr>
    <?php 
    foreach ($pro_access_ID_Names as $key => $value){
      if(in_array($key, $page_project_IDs))
      echo "<tr><td>$value</td><td align=center>$key</td></tr>\n";
    }
    ?>
      </table>
    </td>
    </tr>
    <tr><td align=right><?php echo $page_output;?></td>
    </tr>
    <tr><td bgcolor=#d2d2d2>
       <table border=0 width=100% cellspacing=1 cellpadding=2>
        <tr>
          <td width=8% bgcolor=<?php echo $menu_color;?> align=center><a href='<?php echo $PHP_SELF;?>?table=<?php echo $table;?>&order_by=<?php echo ($order_by == "ID")? 'ID%20desc':'ID';?>'><b><font color="#000000">Folder ID</font></b></a>
            <?php 
            if($order_by == "ID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td align=center width=5% bgcolor=<?php echo $menu_color;?> align=center><a href='<?php echo $PHP_SELF;?>?table=<?php echo $table;?>&order_by=<?php echo ($order_by == "ProhitsID")? 'ProhitsID%20desc':'ProhitsID';?>'><b><font color="#000000">Analyst<br>PlateID</font></b></a>
            <br>
            <?php 
            if($order_by == "ProhitsID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ProhitsID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=8% bgcolor=<?php echo $menu_color;?> align=center><a href='<?php echo $PHP_SELF;?>?table=<?php echo $table;?>&order_by=<?php echo ($order_by == "ProjectID")? 'ProjectID%20desc':'ProjectID';?>'><b><font color="#000000">Project</font></b></a>
            <?php 
            if($order_by == "ProjectID") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "ProjectID desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=40% bgcolor=<?php echo $menu_color;?> align=center><a href='<?php echo $PHP_SELF;?>?table=<?php echo $table;?>&order_by=<?php echo ($order_by == "FileName")? 'FileName%20desc':'FileName';?>'><b><font color="#000000">Folder Name</font></b></a>
            <?php 
            if($order_by == "FileName") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "FileName desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          
          <td width=10% bgcolor=<?php echo $menu_color;?>  align=center><a href='<?php echo $PHP_SELF;?>?table=<?php echo $table;?>&order_by=<?php echo ($order_by == "Date")? 'Date%20desc':'Date';?>'><b><font color="#000000">Date</font></b></a>
           <?php 
            if($order_by == "Date") echo "<img src='images/icon_order_up.gif'>";
            if($order_by == "Date desc" or !$order_by) echo "<img src='images/icon_order_down.gif'>";
            ?>
          </td>
          <td width=6% bgcolor=<?php echo $menu_color;?> align=center><b><font color="#000000">Task ID</font></b>
          </td>
          <td width=5% bgcolor=<?php echo $menu_color;?> align=center><b>Detail</b></td>
         </tr>
        <?php 
        $tmpTaskIDarr = array();
        for($i=0; $i < count($plate_records); $i++){
          $SQL = "select ID from $tableSearchTasks where PlateID='".$plate_records[$i]['ID']."'";
          $managerDB->check_connection();
          $tmp_rds = $managerDB->fetchAll($SQL);
          if(count($tmp_rds)){
            $tmp_task_str = '';
            foreach ($tmp_rds as $key => $value){
              $tmp_task_str .= ($tmp_task_str)?"<br>".$value['ID']:$value['ID'];
            }
            $bgcolor=(in_array($plate_records[$i]['ID'], $runningTaskPlateIDs))?"yellow":"#ffffff";
            echo  "<tr>";
            echo "<td bgcolor=$bgcolor>".$plate_records[$i]['ID']."</td>
                  <td bgcolor=$bgcolor>".$plate_records[$i]['ProhitsID']."</td>
                  <td bgcolor=$bgcolor>".$plate_records[$i]['ProjectID']."</td>
                  <td bgcolor=$bgcolor>".$plate_records[$i]['FileName']."</td>
                  <td bgcolor=$bgcolor>".$plate_records[$i]['Date']."</td>
                  <td bgcolor=$bgcolor>".$tmp_task_str."</td>
                  <td bgcolor=$bgcolor><a href=./ms_search_results_detail.php?table=$table&frm_PlateID=" . $plate_records[$i]['ID']. "><img src=./images/icon_checked.gif border=0 alt=detail></a></td>
                 ";
            echo "</tr>\n";
          }
          if(count($specialTaskArr)){
            print_multiple_folders($plate_records[$i]);
          }  
        }
        ?>
       </table>
    </td>
    </tr>
   </table> 
<?php 
include("./ms_search_footer.php");
function print_multiple_folders(&$plate_records){
  global $specialTaskArr,$managerDB,$tmpTaskIDarr,$table,$tableSearchTasks,$runningTaskPlateIDs;
  foreach($specialTaskArr as $tmpKey => $value1){
    if(!in_array($tmpKey, $tmpTaskIDarr) && in_array($plate_records['ID'], $value1)){
      array_push($tmpTaskIDarr, $tmpKey);
      $tmpIDstr = implode(",", $value1);
      $SQL = "select ID, FileName, ProhitsID, ProjectID, Date 
              from $table 
              where ID IN($tmpIDstr)";
      $multipleFoldArr = $managerDB->fetchAll($SQL);
      $SQL = "select ID from $tableSearchTasks where PlateID='".$tmpIDstr."'";
      $managerDB->check_connection();
      $tmp_rds = $managerDB->fetchAll($SQL);
      $tmp_task_str = '';
      foreach ($tmp_rds as $key => $value){
        $tmp_task_str .= ($tmp_task_str)?"<br>".$value['ID']:$value['ID'];
      }
      echo  "<tr>";
      $tmpIDStr = '';
      $tmpProhitsIDStr = '';
      $tmpProjectIDStr = '';
      $tmpFileNameStr = '';
      $tmpDateStr = '';
      foreach($multipleFoldArr as $FoldArrvalue){
        if($tmpIDStr) $tmpIDStr .= '<br>';
        if($tmpProhitsIDStr) $tmpProhitsIDStr .= '<br>';
        if($tmpProjectIDStr) $tmpProjectIDStr .= '<br>';
        if($tmpFileNameStr) $tmpFileNameStr .= '<br>';
        if($tmpDateStr) $tmpDateStr .= '<br>';
        $tmpIDStr .= $FoldArrvalue['ID'];
        $tmpProhitsIDStr .= $FoldArrvalue['ProhitsID'];
        $tmpProjectIDStr .= $FoldArrvalue['ProjectID'];
        $tmpFileNameStr .= $FoldArrvalue['FileName'];
        $tmpDateStr .= $FoldArrvalue['Date'];
      }
      $bgcolorTmp=(in_array($tmpIDstr, $runningTaskPlateIDs))?"yellow":"#ffffff";
      echo "<td bgcolor=$bgcolorTmp>".$tmpIDStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmpProhitsIDStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmpProjectIDStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmpFileNameStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmpDateStr."</td>
        <td bgcolor=$bgcolorTmp>".$tmp_task_str."</td>
        <td bgcolor=$bgcolorTmp><a href=./ms_search_results_detail.php?table=$table&frm_PlateID=" . $tmpIDstr. "><img src=./images/icon_checked.gif border=0 alt=detail></a></td>
       ";
      echo "</tr>\n";
      break;
    }
  }
}          
?>
