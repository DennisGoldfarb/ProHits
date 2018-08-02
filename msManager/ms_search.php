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

$tableName = '';
$thePage = '';

$mascot_err= '';
$gpm_err = '';;
$tpp_err = '';
$sequest_err = '';
$diaumpire_err = '';



require("../common/site_permission.inc.php"); 
require("./common_functions.inc.php");
require("./is_dir_file.inc.php");
require("../common/PHPMailer-master/PHPMailerAutoload.php");
require("../common/common_fun.inc.php"); 
$mangerDB = new mysqlDB(MANAGER_DB);
$taskTables= $mangerDB->list_tables();



include("./ms_header.php");

//GPM_IP == 'localhost' and ...
$gpm_in_prohits = is_in_local_server('GPM');
$tpp_in_prohits = is_in_local_server('TPP');
$comet_in_prohits = is_in_local_server('COMET');
$msgfpl_in_prohits = is_in_local_server('MSGFPL');
$diaumpire_in_prohits = is_in_local_server('DIAUmpire');
$msplit_in_prohits = is_in_local_server('MSPLIT');
$msfragger_in_prohits = is_in_local_server('MSFragger');
 
 

$storage_err = check_stoage_computer();
$storage_broken = ($storage_err)?"_lost":"";
if(defined("MASCOT_IP") and MASCOT_IP){
  $mascot_err = check_search_engine_url('mascot');
}
if(defined("SEQUEST_IP") and SEQUEST_IP){
  $sequest_err = check_search_engine_url('sequest');
}


if($gpm_in_prohits){
  $gpm_err = check_search_engine_url('gpm', $gpm_in_prohits);
}
if($diaumpire_in_prohits){
  $diaumpire_err = check_search_engine_url('diaumpire', $diaumpire_in_prohits);
}
if($msplit_in_prohits){
  $msplit_err = check_search_engine_url('msplit', $msplit_in_prohits); 
} 
if(defined("PHILOSOPHER_BIN_PATH") and PHILOSOPHER_BIN_PATH){
  $philosopher_err = check_search_engine_url('philosopher');
}else{
  $tpp_err = check_search_engine_url('tpp');
}
if($comet_in_prohits){
  $comet_err = check_search_engine_url('comet', $comet_in_prohits); 
}
if($msgfpl_in_prohits){
  $msgfpl_err = check_search_engine_url('msgfpl', $msgfpl_in_prohits); 
}
if($msfragger_in_prohits){
  $msfragger_err = check_search_engine_url('msfragger', $msfragger_in_prohits); 
}
 
$converter_err = check_search_engine_url('converter');  
 
?>

<br>
    <table border="0" cellpadding="0" cellspacing="2" width=85%>
      
      <tr>
        <td colspan=2> <font color='' face='helvetica,arial,futura' size='+1'><b>Parameter Setup</b></font>
        <a href="javascript: popwin('../doc/management_help.html#Using_Auto_Search',780,600, 'help');"><img src=./images/icon_help.gif border=0></a>
        <br>
        ProHits monitors connections to the search engines.  Broken connections are indicated by a broken red arrow.
        </td>
      </tr>
      <tr>
        <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
      </tr>
      <tr>
      <td colspan=2>
        <table cellspacing="0" cellpadding="0" border="0">
        <?php 
        $search_engine_num = 1;
        $tmp_seach_engine_str = '';
        if(defined("MASCOT_IP") and MASCOT_IP){
          $rul = "http://".MASCOT_IP. MASCOT_CGI_DIR;
          $tmp_seach_engine_str = make_search_engine_str($rul, "Mascot", $mascot_err);
          $search_engine_num++;
        }
        if(defined("SEQUEST_IP") and SEQUEST_IP){
          $url = "http://".SEQUEST_IP."/Prohits_SEQUEST/Prohits_SEQUEST.pl";
          $tmp_seach_engine_str .= make_search_engine_str($url, "Sequest", $sequest_err);
          $search_engine_num++;
        } 
        if($gpm_in_prohits){
          $url = TPP_BIN_PATH."/tandem";
        
          $tmp_seach_engine_str .= make_search_engine_str($url, "GPM", $gpm_err);
          $search_engine_num++;
        } 
        if($comet_in_prohits){
          $url = COMET_BIN_PATH."/comet";
         
          $tmp_seach_engine_str .= make_search_engine_str($url, "Comet", $comet_err);
          $search_engine_num++;
        } 
        if($msfragger_in_prohits){
          $url = "java -jar ".MSFRAGGER_BIN_PATH."/MSFragger.jar";
          $tmp_seach_engine_str .= make_search_engine_str($url, "MSFragger", $msfragger_err);
          $search_engine_num++;
        } 
        if($msgfpl_in_prohits){
          $url = "java -jar ". MSGFPL_BIN_PATH . "/MSGFPlus.jar"; 
          $tmp_seach_engine_str .= make_search_engine_str($url, "MSGFPL", $msgfpl_err);
          $search_engine_num++;
        }
        if(defined("PHILOSOPHER_BIN_PATH") and PHILOSOPHER_BIN_PATH){
          $url = PHILOSOPHER_BIN_PATH."/philosopher";
          $tmp_seach_engine_str .= make_search_engine_str($url, "philosopher", $philosopher_err);
          $search_engine_num++;
        }else if($tpp_in_prohits){
          $url = TPP_BIN_PATH."/xinteract";
          $tmp_seach_engine_str .= make_search_engine_str($url, "TPP", $tpp_err);
          $search_engine_num++;
        }
        if($diaumpire_in_prohits){
            $url2 = "java -jar ".DIAUMPIRE_BIN_PATH."/DIA_Umpire_SE.jar";
        }
        $tmp_seach_engine_str .= make_search_engine_str($url2, "DIAUmpire", $diaumpire_err);
        $search_engine_num++;
        
        $url3 = "java -jar -Xmx5G ". MSPLIT_JAR_PATH;
        $tmp_seach_engine_str .= make_search_engine_str($url3, "MSPLIT", $msplit_err);
        $search_engine_num++;
        
        ?>
          <tr align=center>
              <td rowspan="<?php echo $search_engine_num;?>" align=center bgcolor=#cdcdcd width=150><div class=small>
                  Storage Computer:<br><font color='red'><b><?php echo STORAGE_IP;?></b></font><br>
                  Storage Folder: <br><font color='red'><b><?php echo STORAGE_FOLDER;?></b></font><br>
                  <img src=./images/db_prohits.gif border=0 alt='storage'><br>
                  <img src=./images/db_d<?php echo $storage_broken;?>.gif border=0 alt='storage'><br>
                  <img src=./images/db.gif border=0 alt='storage'>
                </div>
              </td>
              <td rowspan="<?php echo $search_engine_num;?>"><img src=./images/db_center1.png border=0></td>
              <td rowspan="<?php echo $search_engine_num;?>" width="7" background="./images/db_center2.png">&nbsp;</td>
              <td height=75><img src=./images/db_lr<?php echo ($converter_err)?'_lost':'';?>.gif border=0></td>
              <td><a href="javascript: popwin('./ms_search_proteowizard.php', 700, 900)"><img src='./images/proteowizard.png' border=0></a></td>
              <td align=center><b><a href="javascript: popwin('./ms_search_proteowizard.php', 730, 580)" class=large>Proteowizard Parameters</a><br>(RawConverter)</b>
              <div class=small>
              ( <?php echo RAW_CONVERTER_SERVER_PATH;?> ) <br><?php echo $converter_version;?></div>              
              </td>
          </tr>
          <?php echo $tmp_seach_engine_str;?>
        </table>
      </td>
      </tr>
      <tr>
        <td colspan=2><br><font color='' face='helvetica,arial,futura' size='+1'><b>Tasks and Results</b></font></td>
      </tr>
      <tr>
        <td colspan=2 height=1 bgcolor="black"><img src="./images/pixel.gif"></td>
      </tr>
        <td colspan=2> 
          <table border=0 width=100%>
    <?php 
    $num = 1;
    foreach($BACKUP_SOURCE_FOLDERS as $baseTable => $var_arr){
      if(in_array($baseTable."SearchTasks", $taskTables)){
        $table = $baseTable;
        $logo = $table;
        if(!is_file("./images/msLogo/" . $logo . "_logo.gif")) $logo = "default";
         $out_str = "
        <td height=60 width=20%><a href='./ms_search_task_list.php?table=$table'><img src='./images/msLogo/$logo"."_logo.gif' border=0></a></td>
        <td width=30%><b><a href='./ms_search_task_list.php?table=$table' class=large>$table</a></b>         
          <li><a href='./ms_search_task_new.php?table=$table&myaction=new' class=button title='create task'>new task</a>
          <li><a href='./ms_search_task_list.php?table=$table' class=button title='brows task'>task list</a>
          <li><a href='./ms_search_task_list.php?table=$table&frm_list_by=folder' class=button title='brows task by folder'>folder list</a>
          <li>Parse searched results
        </td>
       ";
       if($num%2){
         echo "\n<tr>$out_str";
       }else{
         echo "$out_str</tr>";
       }
       $num++;
      }
    }
    if($num%2 === 0){
      echo "<td>&nbsp;</td><td>&nbsp;</td></tr>";
    }
    ?>   
      </table>
      </td>
    </tr>
    </table>
   <br>
   <br>
<?php 
include("./ms_footer.php");
@ob_flush(); 
flush();
if(($storage_err 
    or $mascot_err
    or $gpm_err
    or $tpp_err
    or $converter_err) and EMAIL2ADMIN_CONNECTION_ERROR){
  $msg = "<H2>Prohits Error Report</H2>:<br>
          <b>send from url</b>: ". $PHP_SELF."<br>
          <b>user</b>: ". $USER->Fname. " ". $USER->Lname."<br>
          <b>user email</b>: ". $USER->Email ."<br>
          <b>userID</b>:".$USER->ID."<br>";
  $msg .= $storage_err . "<br>".
          $mascot_err  . "<br>".
          $gpm_err  . "<br>".
          $tpp_err  . "<br>".
          $converter_err  . "<br>";
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
  $headers .= 'From: '.PROHITS_SERVER_IP . "\r\n";
  if(PROHITS_GMAIL_USER and PROHITS_GMAIL_PWD){
    $err = prohits_gmail(ADMIN_EMAIL, "", 'search engine connection error', $msg, 1);
    echo $err;
  }else{
    mail(ADMIN_EMAIL, "storage connection error", $msg, $headers);
  }
}
//------------------------------------------------------------------------
function make_search_engine_str($url, $search_engine, $err, $log = ''){
//------------------------------------------------------------------------
  global $mascot_version;
  global $gpm_version;
  global $tpp_version;
  global $philosopher_version;
  global $sequest_version;
  global $comet_version;
  global $msfragger_version;
  global $msgfpl_version;
  global $converter_version;
  global $diaumpire_version;
  global $msplit_version;
  global $PROHITS_IP;
  
  $search_eingine_arr = array("MASCOT","GPM","COMET","MSGFPL", "MSFRAGGER");
  
  $url = str_replace("localhost", $PROHITS_IP, $url);
  $version_var = strtolower($search_engine)."_version";
  $upper_search_eingine = strtoupper($search_engine);
  $lower_search_eingine = strtolower($search_engine);
  if(!$log) $log = $lower_search_eingine.".gif";
  $search_lable = $search_engine;
  if($search_lable == 'GPM') $search_lable = 'XTandem';
  if($search_lable == 'TPP') $search_lable = 'TPP';
  if($search_lable == 'DIAUmpire') $search_lable = 'DIA-Umpire';
  if($search_lable == 'MSPLIT') $search_lable = 'MSPLIT-DIA';
  $str = "<tr align=center>
              <td height=75><img src=./images/db_lr";
  $str .= ($err)?'_lost':'';
  if(in_array($upper_search_eingine, $search_eingine_arr)){
    $str .=  ".gif border=0></td>
                <td><a href=\"javascript: popwin('./ms_search_parameter.php?selected_SearchEngine=$upper_search_eingine', 880, 800)\"><img src='./images/".$log."' border=0></a></td>
                <td><a href=\"javascript: popwin('./ms_search_parameter.php?selected_SearchEngine=$upper_search_eingine', 880, 800)\" class=large>".$search_lable." Parameters</a></b>";
                
  }else{
    $str .=  ".gif border=0></td>
                <td><a href=\"javascript: popwin('./ms_search_". $lower_search_eingine. ".php', 880, 900)\"><img src='./images/".$log."' border=0></a></td>
                <td><a href=\"javascript: popwin('./ms_search_". $lower_search_eingine. ".php', 880, 900)\" class=large>".$search_lable." Parameters</a></b>";
                
  }
  $str .=  "<div class=small>";             
  if(!$url){
    $str .= "$search_engine is disabled.";
  }else{
    $str .= $url . "  <br>". $$version_var;
  }
  $str .= "</div>
              </td>
          </tr>";
  return $str;
}
?>
