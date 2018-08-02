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
require("common/common_fun.inc.php");

if(!$Hit_ID){ 
  header ("Location: noaccess.html");
}
//-------------------------------------------------------------------------------------------------
$SEQUEST_cgi = "http://" . SEQUEST_IP . SEQUEST_CGI_DIR;
//------------------------------------------------------------------------------------------------

$SQL = "SELECT  `GeneID`,
                `BandID`, 
                `Subsumed`,
                `Instrument`,
                `SpectralCount`,
                `Unique`,
                `ResultFile`,
                `SearchEngine`,
                `SearchDatabase`,
                `DateTime`
                FROM `Hits_GeneLevel` WHERE `ID`='$Hit_ID'";
$geneLevelHitsArr = $HITSDB->fetch($SQL);
$SQL = "SELECT `ID`,
        `Location`,
        `Sequence`,
        `SpectralCount`,
        `IsUnique`,
        `Miss`,
        `Modifications` 
        FROM `Peptide_GeneLevel`
        WHERE HitID='$Hit_ID' ORDER BY `SpectralCount` DESC";
$PeptidesArr = $HITSDB->fetchAll($SQL);
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
  //$urlString = get_URL_str($geneLevelHitsArr['HitGI']);
  $urlString = get_URL_str('', $geneLevelHitsArr['GeneID']);
  ?>
		&nbsp;<font color="#ffffff" face="helvetica,arial,futura" size="3"><b>Hit Information Gene ID: </b>(<?php  echo $geneLevelHitsArr['GeneID']."&nbsp;&nbsp;".$urlString;?>) 
	</span></td>
</tr>
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Spectral Count</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $geneLevelHitsArr['SpectralCount'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Unique Peptide</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $geneLevelHitsArr['Unique'];?></span></td>
</tr> 
<tr>
   <td  bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Subsumed&nbsp;</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $geneLevelHitsArr['Subsumed'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Engine&nbsp;</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $geneLevelHitsArr['SearchEngine'];?></span></td>
</tr> 
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Database</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $geneLevelHitsArr['SearchDatabase'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Results File</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>
<?php if(strstr($geneLevelHitsArr['SearchEngine'], 'Uploaded')){
    $tmp_file_name = $geneLevelHitsArr['ResultFile'];
    //$theFile = "./ProhitsTPP_protHTML.php?userID=$AccessUserID&File=$tmp_file_name&SearchEngine=".$geneLevelHitsArr['SearchEngine'];
  }else{ 
    $theFile = "../msManager/ms_search_MSPLIT_results_view.php?path=".$geneLevelHitsArr['ResultFile']."&BandID=".$geneLevelHitsArr['BandID']."&SearchEngine=".$geneLevelHitsArr['SearchEngine']."&table=".$geneLevelHitsArr['Instrument'];
  }
?>
    <a href="javascript:popwin('<?php echo str_replace("\\","/",$theFile)?>',800,800,'new')">Click to view <b>GeneLevel <?php echo $geneLevelHitsArr['SearchEngine']?></b> search results</a>
  </td>
</tr>
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Date Time</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $geneLevelHitsArr['DateTime'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>&nbsp;&nbsp;</span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>&nbsp;&nbsp;</span></td>
</tr>  
</table><br>
<table border="0" cellpadding="1" cellspacing="1" width="100%">  
	<tr bgcolor="">
	  <td width="5%" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>ID</div>
	  </td>
    <td width="5%" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Spectral Count</div>
	  </td>
    <td width="5%" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Is Unique</div>
	  </td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Miss</div>
	  </td>
	  <td width="15%" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>Location</div> 
	  </td>
	  <td bgcolor="<?php echo $bgcolordark;?>" align=center width="15%"> 
   <div class=tableheader>sequence</div>
	  </td>
	  <td bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>Modifications</div>
	  </td>
	</tr>
<?php 
foreach($PeptidesArr as $PeptidesValue){
?>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td width="" align="center"><font face="arial" size="1">
	      <?php echo '<b>'.$PeptidesValue['ID']."</b>";?>&nbsp;
	    </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $PeptidesValue['SpectralCount'] ;?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $PeptidesValue['IsUnique'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $PeptidesValue['Miss'] ;?></font>&nbsp;
	      </div>
	  </td>
	  <td width="" align="left"><div class=maintext>
	      <?php echo $PeptidesValue['Location'];?>&nbsp;
	      </div>
	  </td>
	  <td width="" align="left"><div class=maintext>
	      <?php echo  $PeptidesValue['Sequence'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="left"><div class=maintext>
	      <?php echo  $PeptidesValue['Modifications'];?>&nbsp;
	      </div>
	  </td>
	</tr>
<?php 
} //end for
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
