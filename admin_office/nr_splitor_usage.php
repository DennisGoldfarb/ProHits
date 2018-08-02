<?php 
/***********************************************************************
    Prohits version 1.00
    Copyright (C) 2001, Mike Tyers, All Rights Reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
*************************************************************************/
require_once("../common/site_permission.inc.php");
include("./admin_header.php");
$bgcolor = "#d0e4f8";
$bgcolormid = "#89baf5";
$bgcolordark = "#637eef";
$proteinDB = new mysqlDB(PROHITS_PROTEINS_DB);

$SQL = "SELECT `ID` FROM `Fasta_file_tree`";  
$IDarr = $proteinDB->fetchAll($SQL);
$numFiles = count($IDarr);
$warnning = '';
if(!$numFiles){
  $warnning = "<font color=red><B>WARNNING</B>: Database table 'Fasta_file_tree' is empty and you must run command 'createTwotables' on script 'split_nr_file.php' first</font>";
}
$SQL = "SELECT `GI` FROM `Fasta_file_gi` LIMIT 1";  
$GIarr = $proteinDB->fetchAll($SQL);
$numFiles = count($GIarr);
if(!$numFiles && !$warnning){
  $warnning = "<font color=red><B>WARNNING</B>: Database table 'Fasta_file_gi' is empty and you must run command 'createTwotables' or command 'createGiFiletableOnly'
  <br>on script 'split_nr_file.php' first</font>";
}
?>
<table border="0" cellpadding="0" cellspacing="0" width="90%">
  <tr>
    <td align="left" >
      &nbsp; <font color="<?php echo $bgcolordark;?>" face="helvetica,arial,futura" size="3"><b>Spliting Nr File Usage</b></font>   
    </td>    
  </tr>
  <tr>
    <td height=1 bgcolor="<?php echo $bgcolormid?>"><img src="./images/pixel.gif"></td>
  </tr>  
  <tr>    
  <td align="center" ><br>
      
<TABLE border="1" cellpadding="1" cellspacing="1" width="60%">
  <TR><TD colspan="2"><div class=maintext><?php echo $warnning?></div></TD></TR> 
  <TR>
  <TD width="60%" valign="top">   
    <TABLE border="0" cellpadding="0" cellspacing="0">
    <TR><TD nowrap><div class=tableheader_blue height=18><B>Usage:</B><br>&nbsp;</div></TD></TR>
    <TR><TD nowrap><div class=maintext>
<pre>
Shell:
1.Create or update both database tables 'Fasta_file_tree'
  and 'Fasta_file_gi': 
  Usage: php Path/split_nr_file.php createTwotables
2.Create or update database table 'Fasta_file_gi' only:
  Usage: php Path/split_nr_file.php createGiFiletableOnly
3.Create or update all fasta files:
  Usage: php Path/split_nr_file.php splitNRfile
4.Create or update individual fasta file:
  Usage: php Path/split_nr_file.php createOneFile <font color=red>'theFastaFileName'</font>
  
Web Browser:
1.Create or update both database tables 'Fasta_file_tree'
  and 'Fasta_file_gi': 
  URL: Path/split_nr_file.php?theaction=createTwotables
2.Create or update database table 'Fasta_file_gi' only:  
  URL: Path/split_nr_file.php?theaction=createGiFiletableOnly
3.Create or update all fasta files:
  URL: Path/split_nr_file.php?theaction=splitNRfile
4.Create or update individual fasta file:  
  URL: Path/split_nr_file.php?theaction=createOneFile
       &file=theFastaFileName

Add file name to Current tax name tree:
Open php file 'split_nr_file.php' and insert centence
$firstLevelNodes .= "'file name',"; to string variable
$firstLevelNodes. Then run php file 'split_nr_file.php'.    
</pre>
  </div></TD></TR>
      <TR><TD nowrap><div class=tableheader_blue height=18><B>Current tax name tree</B><br>&nbsp;</div></TD></TR>
      <?php echo file_list();?>
    </TABLE>
  </TD> 
   
  <TD align="right" valign="top">  
    <TABLE border="0" cellpadding="0" cellspacing="0">
      <TR><TD nowrap><div class=tableheader_blue height=18><B>Standard tax name tree</B><br>&nbsp;</div></TD></TR>
      <TR><TD nowrap><div class=maintext>All entries</div class=maintext></TD></TR>
      <TR><TD nowrap><div class=maintext>. . Archaea (Archaeobacteria)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . Eukaryota (eucaryotes)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Alveolata (alveolates)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Plasmodium falciparum (malaria parasite)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Other Alveolata</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Metazoa (Animals)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Caenorhabditis elegans</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Drosophila (fruit flies)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Chordata (vertebrates and relatives)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . bony vertebrates</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . lobe-finned fish and tetrapod clade</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . Mammalia (mammals)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . Primates</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . . . Homo sapiens (human)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . . . Other primates</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . Rodentia (Rodents)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . . . Mus.</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . . . . . Mus musculus (house mouse)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . . . Rattus</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . . . Other rodentia</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . . . Other mammalia</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . Xenopus laevis (African clawed frog)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . Other lobe-finned fish and tetrapod clade</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . Actinopterygii (ray-finned fishes)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . Takifugu rubripes (Japanese Pufferfish)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . Danio rerio (zebra fish)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . . . . . Other Actinopterygii</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . . . Other Chordata</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Other Metazoa</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Dictyostelium discoideum</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Fungi</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Saccharomyces Cerevisiae (baker's yeast)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Schizosaccharomyces pombe (fission yeast)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Pneumocystis carinii</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Other Fungi</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Viridiplantae (Green Plants)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Arabidopsis thaliana (thale cress)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Oryza sativa (rice)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Other green plants</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Other Eukaryota</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . Bacteria (Eubacteria)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Actinobacteria (class)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Mycobacterium tuberculosis complex</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Other Actinobacteria (class)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Firmicutes (gram-positive bacteria)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Bacillus subtilis</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Mycoplasma</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Streptococcus Pneumoniae</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Streptomyces coelicolor</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Other Firmicutes</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Proteobacteria (purple bacteria)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Agrobacterium tumefaciens</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Campylobacter jejuni</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Escherichia coli</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Salmonella</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . . . Other Proteobacteria</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Other Bacteria</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . Viruses</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Hepatitis C virus</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . . . Other viruses</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . Other (includes plasmids and artificial sequences)</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . unclassified</div></TD></TR>
      <TR><TD nowrap><div class=maintext>. . Species information unavailable</div></TD></TR>
    </TABLE>
  </TD>
  </TR>
</TABLE>
</td>
</tr>
</table><br>&nbsp;
<?php 
include("./admin_footer.php");

function file_list(){
  global $proteinDB; 
  $mainArr = array();  
  array_push($mainArr, '1');
  $itemCount = 0;
  $dot = ' . ';
  while(count($mainArr)&& $itemCount != 100){
    $itemCount++;
    $popItem = array_pop($mainArr);
    $SQL = "SELECT `ID`, `File_name`,`Level` FROM `Fasta_file_tree` WHERE `ID`='$popItem'";
    $fileIDarr = $proteinDB->fetch($SQL);
    if($fileIDarr['ID'] && $fileIDarr['ID'] !=1 ){
      ?>
        <TR><TD nowrap><div class=maintext><?php echo str_repeat($dot, ($fileIDarr['Level']-1)*2);?><?php echo $fileIDarr['File_name'];?></div></TD></TR>
      <?php 
    }
    $SQL = "SELECT `ID` FROM `Fasta_file_tree` WHERE `Parent_id`='$popItem' ORDER BY File_name DESC";  
    $childrinArr = $proteinDB->fetchAll($SQL);
    foreach($childrinArr as $value){
      array_push($mainArr, $value['ID']);
    }
  }  
}
?>
      