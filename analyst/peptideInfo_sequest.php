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

$SQL = "SELECT `Expect`,
                `HitGI`,
                `Coverage`,
                `Pep_num`,
                `Pep_num_uniqe`,
                `ResultFile`,
                `SearchEngine`,
                `SearchDatabase`,
                `DateTime`
                FROM `Hits` WHERE `ID`='$Hit_ID'";
$SEQUESTProteinArr = $HITSDB->fetch($SQL);
$SQL = "SELECT `ID`,
        `Expect`,
        `Charge`,
        `MASS`,
        `Location`,
        `Sequence`,
        `Modifications` 
        FROM `SequestPeptide`
        WHERE HitID='$Hit_ID' ORDER BY `Expect` DESC";
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
  $urlString = get_URL_str($SEQUESTProteinArr['HitGI']);
  if(is_numeric($TppProteinArr['ProteinAcc'])){
    $Acc_V_arr = replease_gi_with_Acc_Version($TppProteinArr['ProteinAcc']);
    $Acc_V = $Acc_V_arr['Acc_Version'];
  }else{
    $Acc_V = $TppProteinArr['ProteinAcc'];
  }
  $urlString = get_URL_str($Acc_V);
  ?>
		&nbsp;<font color="#ffffff" face="helvetica,arial,futura" size="3"><b>Hit Information (<?php echo $Acc_V.$urlString;?>) </b> 
	</span></td>
</tr>
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Score</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $SEQUESTProteinArr['Expect'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Coverage</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><?php echo $SEQUESTProteinArr['Coverage'];?></span></td>
</tr> 
<tr>
   <td  bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Pep_num&nbsp;#</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $SEQUESTProteinArr['Pep_num'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Unique Peptide&nbsp;#</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $SEQUESTProteinArr['Pep_num_uniqe'];?></span></td>
</tr> 
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Database</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $SEQUESTProteinArr['SearchDatabase'];?></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Results File</b></span></td>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext>
<?php if(strstr($SEQUESTProteinArr['SearchEngine'], 'Uploaded')){
    $tmp_file_name = $SEQUESTProteinArr['ResultFile'];
    //$theFile = "./ProhitsTPP_protHTML.php?userID=$AccessUserID&File=$tmp_file_name&SearchEngine=".$SEQUESTProteinArr['SearchEngine'];
?>
<?php }else{?> 
    <a href="<?php echo $SEQUEST_cgi?>/Prohits_SEQUEST_parser.pl?dir=<?php echo $SEQUESTProteinArr['ResultFile'];?>" target=new>
<?php }?>   
   Click to view <b>Sequest</b> search results</a></span>
   </td>
</tr>
<tr>
   <td bgcolor="<?php echo $bgHitcolor;?>"><span class=maintext><b>Search Date Time</b></span></td>
   <td bgcolor="<?php echo  $bgHitcolor;?>"><span class=maintext><?php echo $SEQUESTProteinArr['DateTime'];?></span></td>
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
	  <div class=tableheader>Score</div>
	  </td>
    <td width="5%" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Charge</div>
	  </td>
    <td width="8%" bgcolor="<?php echo $bgcolordark;?>" align="center" align=center> 
	  <div class=tableheader>Mass</div>
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
	      <?php echo $PeptidesValue['Expect'] ;?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $PeptidesValue['Charge'];?>&nbsp;
	      </div>
	  </td>
    <td width="" align="center"><div class=maintext>
	      <?php echo $PeptidesValue['MASS'] ;?></font>&nbsp;
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
