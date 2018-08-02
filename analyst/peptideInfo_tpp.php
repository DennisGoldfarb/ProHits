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
if(!$TppProtein_ID){ 
  header ("Location: noaccess.html");
}
 
//-------------------------------------------------------------------------------------------------
$tpp_cgi = "http://" . $gpm_ip . TPP_CGI_DIR;
//-------------------------------------------------------------------------------------------------

$SQL = "SELECT `ID`,
        `ProteinAcc`,
        `PROBABILITY`,
        `PERCENT_COVERAGE`,
        `UNIQUE_NUMBER_PEPTIDES`,
        `TOTAL_NUMBER_PEPTIDES`,
        `PCT_SPECTRUM_IDS`,
        `SearchDatabase`,
        `SearchEngine`,
        `XmlFile` 
        FROM `TppProtein` WHERE `ID`='$TppProtein_ID'";
$TppProteinArr = $HITSDB->fetch($SQL);

$SQL = "SELECT `ID`,
        `Sequence`,
        `INITIAL_PROBABILITY`,
        `NSP_ADJUSTED_PROBABILITY`,
        `WEIGHT`,
        `N_ENZYMATIC_TERMINI`,
        `N_SIBLING_PEPTIDES`,
        `N_INSTANCES`,
        `IS_CONTRIBUTING_EVIDENCE`,
        CALC_MASS,
        CHARGE
        FROM `TppPeptideGroup` 
        WHERE ProteinID='$TppProtein_ID' ORDER BY `NSP_ADJUSTED_PROBABILITY` DESC";
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
  //$urlString = get_URL_str($TppProteinArr['ProteinAcc']);
  if(is_numeric($TppProteinArr['ProteinAcc'])){
    $Acc_V_arr = replease_gi_with_Acc_Version($TppProteinArr['ProteinAcc']);
    $Acc_V = $Acc_V_arr['Acc_Version'];
  }else{
    $Acc_V = $TppProteinArr['ProteinAcc'];
  }
  $urlString = get_URL_str($Acc_V);
  ?>
		&nbsp;<font color="#ffffff" face="helvetica,arial,futura" size="3"><b>Tpp Protein Information (<?php  echo $Acc_V.$urlString;?>) </b> 
	</span></td>
</tr>
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Coverage</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $TppProteinArr['PERCENT_COVERAGE'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Probability</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $TppProteinArr['PROBABILITY'];?></span></td>
</tr> 
<tr>
   <td  bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Peptide&nbsp;#</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $TppProteinArr['TOTAL_NUMBER_PEPTIDES'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Unique Peptide&nbsp;#</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $TppProteinArr['UNIQUE_NUMBER_PEPTIDES'];?></span></td>
</tr> 
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Pct spectrum IDs</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $TppProteinArr['PCT_SPECTRUM_IDS'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Results File</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>
<?php 
if($_SERVER['HTTP_HOST'] == 'prohitsms.com'){
  echo "<a href='../msManager/demo_search_results.php' target=_new>Click to view <b>TPP</b> search results</a>\n";
}else{
  if(strstr($TppProteinArr['SearchEngine'], 'Uploaded')){
    $tmp_file_name = $TppProteinArr['XmlFile'];
    $theFile = "./ProhitsTPP_protHTML.php?userID=$AccessUserID&File=$tmp_file_name&SearchEngine=".$TppProteinArr['SearchEngine'];
?>
    <a href="javascript:popwin('<?php echo $theFile?>',800,800)">
<?php }else{?>   
   <a href="<?php echo $tpp_cgi?>/protxml2html.pl?xmlfile=<?php echo $TppProteinArr['XmlFile'];?>" target=new>
<?php }
?>   
   Click to view <b>TPP</b> search results</a></span>
<?php 
}
?>    
   </td>
</tr>
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Database</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $TppProteinArr['SearchDatabase'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>&nbsp;&nbsp;</span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>&nbsp;&nbsp;</span></td>
</tr>  
</table><br>
<table border="0" cellpadding="1" cellspacing="1" width="100%">  
	<tr bgcolor="">
	  <td width="5" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>ID</div>
	  </td>
    <td width="70" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>weight</div>
	  </td>
    <td width="70" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>is contributing evidence</div>
	  </td>
    <td width="70" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>nsp adj prob</div>
	  </td>
	  <td width="25" height="25" bgcolor="<?php echo $bgcolordark;?>" align=center>
    <div class=tableheader>init prob</div> 
	  </td>
	  <td width="100" bgcolor="<?php echo $bgcolordark;?>" align=center> 
   <div class=tableheader>net</div>
	  </td>
	  <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>nsp</div>
	  </td>
    <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>total</div>
	  </td>
    
    <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>CALC_MASS</div>
	  </td>
    <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>CHARGE</div>
	  </td>
    
    <td width="600" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center>
	   <div class=tableheader>sequence</div>
	  </td>
	</tr>
<?php 
foreach($PeptidesArr as $PeptidesValue) {
  $nspColor = "<font color=black>";
  $bgcolor = "#dbdbdb";
  if($PeptidesValue['IS_CONTRIBUTING_EVIDENCE'] == 'Y'){
    $nspColor = "<font color=red>";
    $bgcolor = '#e2e083';
  }
?>
	<tr bgcolor="<?php echo $bgcolor;?>">
	  <td width="" align="center"><font face="arial" size="1">
	      <?php echo '<b>'.$PeptidesValue['ID']."</b>";?>&nbsp;
	    </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $PeptidesValue['WEIGHT'] ;?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $PeptidesValue['IS_CONTRIBUTING_EVIDENCE'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext><?php echo $nspColor?>
	      <?php echo $PeptidesValue['NSP_ADJUSTED_PROBABILITY'] ;?></font>&nbsp;
	      </div>
	  </td>
	  <td width="" align="left"><div class=maintext>
	      <?php echo $PeptidesValue['INITIAL_PROBABILITY'];?>&nbsp;
	      </div>
	  </td>
	  <td width="" align="center"><div class=maintext>
	      <?php echo  $PeptidesValue['N_ENZYMATIC_TERMINI'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo  $PeptidesValue['N_SIBLING_PEPTIDES'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo  $PeptidesValue['N_INSTANCES'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo  $PeptidesValue['CALC_MASS'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo  $PeptidesValue['CHARGE'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="left"><div class=maintext>
	      &nbsp;<?php echo $PeptidesValue['Sequence'];?>&nbsp;
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