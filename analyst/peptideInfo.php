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
require("analyst/common_functions.inc.php");
require("analyst/classes/bait_class.php");
require("analyst/classes/band_class.php");
require("analyst/classes/peptide_class.php");
require("analyst/classes/hits_class.php");
include("common/common_fun.inc.php");

if(!$Hit_ID){ 
  header ("Location: noaccess.html");
}
$Hit = new Hits($Hit_ID);
$Bait = new Bait($Hit->BaitID);
$Band = new Band($Hit->BandID);
$Peptides = new Peptide();
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB, HOSTNAME, USERNAME, DBPASSWORD);
$RedundantGI_str = trim($Hit->RedundantGI);

$Peptides->fetchall($Hit_ID);
if($Hit->SearchEngine == 'Mascot'){
  $MascotResults = 1;
}
$bgcolordark = '#999900';
$bgcolor = '#e2e083';
$bgHitcolor="#e2e083";

?>
<html>
<head>
 <title>Prohits</title>
 <link rel="stylesheet" type="text/css" href="./site_style.css"> 
 <script language="Javascript" src="site_javascript.js"></script>
 <!--script language="Javascript" src="site_no_right_click.inc.js"></script-->
 </head>
 <body>
<table border="0" cellpadding="1" cellspacing="1" width="100%">
<tr>
  <td align="left" colspan="4" bgcolor="<?php echo $bgcolordark;?>">
  <?php 
  if(is_numeric($Hit->HitGI)){
    $Acc_V_arr = replease_gi_with_Acc_Version($Hit->HitGI);
    $Acc_V = $Acc_V_arr['Acc_Version'];
  }else{
    $Acc_V = $Hit->HitGI;
  }
  $urlString = get_URL_str($Acc_V);
  ?>
		&nbsp;<font color="#ffffff" face="helvetica,arial,futura" size="3"><b>Hit Information (<?php  echo $Acc_V. $urlString;?>) </b> 
	</span></td>
</tr>

<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Instrument</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Hit->Instrument;?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Score/Expect</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo (($Hit->Expect)?$Hit->Expect:'')."/".$Hit->Expect2;?></span></td>
</tr> 
<tr>
   <td  bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Redundant</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>
   <?php 
    print_Redundant_url($RedundantGI_str,$proteinDB);
   /*for($i=0;$i<count($GI_array);$i++){
      if($GI_array[$i]){
        echo $GI_array[$i] . get_URL_str($GI_array[$i]) . "<br>";
      }  
   }*/ 
   ?></span></td>
   
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Results File</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php 
if($Hit->ResultFile){
  if(strstr($Hit->SearchEngine, 'Uploaded')){
    $tmp_file_name = $Hit->ResultFile;
    $tmp_SearchEngine = '';
    if(strstr($Hit->SearchEngine, 'Mascot')){
      $theFile = "./ProhitsMascotParserHTML.php?userID=$AccessUserID&File=$tmp_file_name";
      $tmp_SearchEngine = 'Mascot';
    }elseif(strstr($Hit->SearchEngine, 'GPM')){
      $theFile = "./ProhitsGPM_ParserHTML.php?userID=$AccessUserID&File=$tmp_file_name";
      $tmp_SearchEngine = 'GPM';
    }
?>
    <a href="javascript:popwin('<?php echo $theFile?>',800,800)">Click to view <b><?php echo $tmp_SearchEngine;?></b> search results</a>
<?php }else{
    if($Hit->SearchEngine == "Mascot"){
      $mascot_IP = MASCOT_IP;
      if(defined('MASCOT_IP_OLD') and preg_match("/^\w/", $Hit->ResultFile, $matches)){
        $mascot_IP = MASCOT_IP_OLD;
      }
      if(MASCOT_USER){
        $tmp_url = "http://".$mascot_IP. MASCOT_CGI_DIR."/login.pl";
        $tmp_url .= "?action=login&username=".MASCOT_USER."&password=".MASCOT_PASSWD;
        $tmp_url .= "&display=nothing&savecookie=1&referer=master_results_2.pl?file=".$Hit->ResultFile;
      }else{
        $tmp_url = "http://".$mascot_IP. MASCOT_CGI_DIR."/master_results_2.pl?file=".$Hit->ResultFile;
      }
      echo "<a href='$tmp_url' target=mascot_win>Click to view <b>Mascot</b> search results</a>";
    }else if($Hit->SearchEngine == "GPM"){
      echo "<a href='http://".$gpm_ip."/thegpm-cgi/plist.pl?path=".$Hit->ResultFile."' target=mascot_win>Click to view <b>GPM</b> search results</a>";
    }else if($Hit->SearchEngine == "MSPLIT"){
      $theFile = "../msManager/ms_search_MSPLIT_results_view.php?path=".$Hit->ResultFile;
?>
      <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')">Click to view <b>MSPLIT</b> search results</a>
<?php   }else{
      //echo "<a href='".$Hit->ResultFile."'>Click to view <b>Sonar</b> search results</a>";
    }
  }  
}
     ?></span></td>
</tr> 
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Database</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Hit->SearchDatabase;?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Date</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $Hit->DateTime;?></span></td>
</tr>  
</table><br>
<table border="0" cellpadding="1" cellspacing="1" width="100%">  
	<tr bgcolor="">
	  <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>ID</div>
	  </td>
    <td width="70" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Score</div>
	  </td>
    <td width="70" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Expect</div>
	  </td>
	  <td width="25" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>Charge</div> 
	  </td>
	  <td width="100" bgcolor="<?php echo $bgcolordark;?>" align=center> 
   <div class=tableheader>Mass (kDa)</div>
	  </td>
	  <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>Location</div>
	  </td>
    <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>Sequence</div>
	  </td>
	</tr>
<?php 
for($i=0; $i < $Peptides->count; $i++) {
  $add_color = '';
  $end_color = '';
 if($Peptides->Status[$i] == 'RB'){
    $add_color = '<font color=red><b>';
    $end_color = '</font></b>';
 }else if($Peptides->Status[$i] == 'R'){
    $add_color = '<font color=red>';
    $end_color = '</font>';
 }else if($Peptides->Status[$i] == 'B'){
    $add_color = '<font color=black><b>';
    $end_color = '</font></b>';
 }else {
    $add_color = '<font color=black>';
    $end_color = '</font>';
 }
 $Peptides->Sequence[$i] = $add_color . $Peptides->Sequence[$i] . $end_color;
 
?>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td width="" align="center"><font face="arial" size="1">
	      <?php echo '<b>'.$Peptides->ID[$i]."</b>";?>&nbsp;
	    </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $Peptides->Expect[$i] ;?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $Peptides->Expect2[$i] ;?>&nbsp;
	      </div>
	  </td>
	  <td width="" align="left"><div class=maintext>
	      <?php echo $Peptides->Charge[$i];?>&nbsp;
	      </div>
	  </td>
	  <td width="" align="center"><div class=maintext>
	      <?php echo  $Peptides->MASS[$i];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo  $Peptides->Location[$i];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="left"><div class=maintext>
	      &nbsp;<?php echo $Peptides->Sequence[$i];?>&nbsp;
        <?php echo ($Peptides->Modifications[$i])?" + ".$Peptides->Modifications[$i]:"";?>
	    </div>
	  </td>
	</tr>
<?php 
} //end for
//**************************************
// this function will return 10 base power 
// string value for displaying by passing
// a float value.
function expectFormat($Value){
  $rt='';
  if($Value == 0) return "0";
  $decimals = log10($Value);
  $tmp_int = intval( $decimals );
  if($decimals < 0){
    $tmpPow = $decimals + abs($tmp_int) + 1;
    $rt = sprintf("%0.1f", pow(10,$tmpPow));
    $tmp_int = $tmp_int -1;
    $rt .= "?0<sup>$tmp_int</sup>";
   
  }else if($decimals > 1){
    $tmpPow = $decimals - $tmp_int;
    $rt = sprintf("%0.1f", pow(10,$tmpPow));
    $rt .= "?0<sup>$tmp_int</sup>";
  }else{
    $rt = sprintf("%0.1f", $Value);
  }
  return $rt;
}
?>
   </table> 
    </td>
  </tr>
</table><br>
<form>
<center>
<input type=button value=' Close ' onClick='javascript: window.close();' class=black_but>
</center>
</form>
</body>
</html>
