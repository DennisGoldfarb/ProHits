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

$tableName = '';
$raw_file_ID = '';
$Band_ID = '';
$GelFree = '';
$menu_color = '#669999';

require("../common/site_permission.inc.php");
include("analyst/common_functions.inc.php");
require("msManager/classes/Storage_class.php");

if(!$tableName || !$raw_file_ID || !$Band_ID) exit;

$managerDB = new mysqlDB(MANAGER_DB, HOSTNAME, USERNAME, DBPASSWORD);
$SQL = "SELECT `ID`,`FileName`,`FolderID`,`ProjectID`,`Size` FROM $tableName WHERE `ID`='$raw_file_ID'";
$rawFileInfo_arr = $managerDB->fetch($SQL);


$SQL = "SELECT BN.ID AS BandID,
                BN.Location,                
                E.ID AS ExpID,
                E.Name AS ExpName,
                B.ID AS BaitID,
                B.GeneName
                FROM Band BN
                LEFT JOIN Experiment E ON BN.ExpID=E.ID 
                LEFT JOIN Bait B ON BN.BaitID=B.ID
                WHERE BN.ID=$Band_ID";
$BandInfo_arr = $HITSDB->fetch($SQL);
$GelInfo_arr = array();
$gel_info = "Gel Free";
if(!$GelFree){
  $SQL = "SELECT  L.LaneNum,
                  L.LaneCode,
                  G.ID AS GelID,
                  G.Name AS GelName
                  FROM (Band B LEFT JOIN Lane L ON (B.LaneID=L.ID))
                  LEFT JOIN Gel G ON(L.GelID=G.ID)
                  WHERE B.ID=$Band_ID";
  $GelInfo_arr = $HITSDB->fetch($SQL);
  $gel_info = "(".$GelInfo_arr['GelID'].")".$GelInfo_arr['GelName']."&nbsp;&nbsp;<br><b>Lane:</b> ".$GelInfo_arr['LaneCode']."&nbsp;&nbsp;<b>Lane No.:</b> ".$GelInfo_arr['LaneNum'];
}

$folder_tree = create_dir_tree($rawFileInfo_arr['FolderID'],$tableName, false);
$project_id_name_arr = get_project_id_name_arr();
$file_project_name = '';
if($rawFileInfo_arr['ProjectID'] && isset($project_id_name_arr[$rawFileInfo_arr['ProjectID']])){
  $file_project_name = $project_id_name_arr[$rawFileInfo_arr['ProjectID']];
}

$file = "http://".STORAGE_IP."/Prohits/msManager/autoBackup/download_raw_file.php?SID=".session_id()."&tableName=$tableName&ID=".$rawFileInfo_arr['ID'];  

?>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="./site_style.css">
</head>
<style type="text/css">
.c { background-color:yellow; }
</style>
<body>
<script language="Javascript" src="../common/javascript/site_javascript.js"></script>
<script language="javascript"> 
function download(FileID){
  var file ='<?php echo "http://".STORAGE_IP."/Prohits/msManager/autoBackup/download_raw_file.php?SID=".session_id()."&tableName=$tableName";?>' + '&ID=' + FileID;
  popwin(file,500,380,'new_w');
}
</script>
<table border=0 width=90% cellspacing="0" align=center>
  <tr>
    <td align=center colspan=2>
    <font face="Arial" size="+2" color="#660000"><b>Raw file detail and Download</b></font><br>
    <hr width="100%" size="1" noshade>
    </td>
  </tr>
  <tr>
    <td bgcolor="<?php echo $menu_color;?>" colspan=2>
      <table border=0 width=100% cellpadding="0" cellspacing="0">
        <tr>
          <td bgcolor="<?php echo $menu_color;?>">
            <font face="Arial" size="3" color="#ffffff"><b>Raw file information</b></font>
          </td>
          <td bgcolor="<?php echo $menu_color;?>" align=right>
            <a href="javascript: download('<?php echo $rawFileInfo_arr['ID']?>');"><img src='../msManager/images/icon_download.gif' border=0 alt=download></a>
            <font face="Arial" size="2" color="#ffffff">&nbsp;Download Raw file&nbsp;&nbsp;</font>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width=30%><font face=Arial size=2 color=#008000><b>Machine Name:</b></font></td>
    <td><font face=Arial size=2 color=black><?php echo $tableName?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Raw File ID:</b></font></td>
    <td><font face=Arial size=2 color=black><?php echo $rawFileInfo_arr['ID']?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Raw File Name:</b></font></td>
    <td><font face=Arial size=2 color=black><?php echo $rawFileInfo_arr['FileName']?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Raw File Project:</b></font></td>
    <td><font face=Arial size=2 color=black><?php echo $file_project_name?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Raw File Folder:</b></font></td>  
    <td><?php echo $folder_tree?></td>
  </tr>
  <tr>
    <td bgcolor="<?php echo $menu_color;?>" colspan=2>
    <font face="Arial" size="3" color="#ffffff"><b>Link to Experiment Sample</b></font>
    </td>
  </tr>
  <tr>
    <td><font face=Arial size=2 color=#008000><b>Project Name:</b></font></td>
    <td><font color=black><?php echo $file_project_name?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Bait:</b></font></td>
    <td><font color=black>(<?php echo $BandInfo_arr['BaitID']?>)&nbsp;<?php echo $BandInfo_arr['GeneName']?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Experiment:</b></font></td>
    <td><font color=black>(<?php echo $BandInfo_arr['ExpID']?>)&nbsp;<?php echo $BandInfo_arr['ExpName']?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Gel:</b></font></td>
    <td><font color=black><?php echo $gel_info?></font></td>
  </tr>
  <tr>  
    <td><font face=Arial size=2 color=#008000><b>Sample:</b></font></td>
    <td><font color=black>(<?php echo $BandInfo_arr['BandID']?>)&nbsp;<?php echo $BandInfo_arr['Location']?></font></td>
  </tr>
</table>
</body>
</html>
<?php 
function get_dir_tree_line($dirID,&$dirTreelineArr,$tableName){
  global $managerDB;
  $currentDirObj = new Storage($managerDB->link,$tableName);
  $currentDirObj->fetch($dirID);
  array_push($dirTreelineArr, $currentDirObj);
  if($currentDirObj->FolderID){
    get_dir_tree_line($currentDirObj->FolderID,$dirTreelineArr,$tableName);
  }
}
function create_dir_tree($dirID,$tableName, $clickable=true){
  $rt = '';
  $dirTreelineArr = array();
  get_dir_tree_line($dirID,$dirTreelineArr,$tableName);  
  $levelCount = 0;
  $rt .= "<div id='dir_tree'>";
  $rt .= "<ul id='dir_tree_topNodes'>\r\n";
  for($i=count($dirTreelineArr)-1;$i>=0;$i--){   
    if($dirTreelineArr[$i]->ID == $dirID){
      $imageFile1 = "minus.gif";
      $folderImage = "folder_open.gif";
      $folderName = "<span class=dir_tree_text_lite>".$dirTreelineArr[$i]->FileName."</span>";
    }else{
      $imageFile1 = "plus.gif";
      $folderImage = "folder_close.gif";
      $folderName = $dirTreelineArr[$i]->FileName;
    }
    $dateArr = explode(' ',$dirTreelineArr[$i]->Date);
    $date  = $dateArr[0];
    $rt .= (($levelCount)?"<ul>\r\n":'')."<li>";
		if($clickable){
			$rt .= "<a href=\"javascript: open_dir('".$dirTreelineArr[$i]->ID."');\"><img src='../msManager/images/$imageFile1' border=0></a><img src='../msManager/images/$folderImage'>&nbsp;<a href=\"javascript: open_dir('".$dirTreelineArr[$i]->ID."');\">".$folderName."&nbsp;<strong class=dir_tree_text>".ceil($dirTreelineArr[$i]->Size/1024)."(MB)"."&nbsp;&nbsp;".$date."</strong></a></li>\r\n";
    }else{
			$rt .= "<img src='../msManager/images/$imageFile1' border=0><img src='../msManager/images/$folderImage'>&nbsp;".$folderName."&nbsp;<strong class=dir_tree_text>".ceil($dirTreelineArr[$i]->Size/1024)."(MB)"."</strong></li>\r\n";
		}
		$levelCount++;
  }
  while($levelCount){
    $rt .= "</ul>\r\n";
    $levelCount--;
  }
	return $rt."</div>";
}
?>  