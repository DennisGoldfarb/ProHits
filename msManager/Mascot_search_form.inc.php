<?php
  if(!isset($CHARGE)) $CHARGE = '2+, 3+ and 4+';
  if(!isset($MASS)) $MASS = 'Monoisotopic';
?>
<INPUT TYPE="hidden" NAME="INTERMEDIATE" VALUE="">
<INPUT TYPE="hidden" NAME="FORMVER" VALUE="1.01">
<INPUT TYPE="hidden" NAME="SEARCH" VALUE="MIS">
<INPUT TYPE="hidden" NAME="PEAK" VALUE="AUTO">
<INPUT TYPE="hidden" NAME="REPTYPE" VALUE="peptide">
<INPUT TYPE="hidden" NAME="ErrTolRepeat" VALUE="0">
<INPUT TYPE="hidden" NAME="SHOWALLMODS" VALUE="">

<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=3>
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
      <B>Your name</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
    <input id="USERNAME" type="text" style="width: 200px;" value="<?php echo (isset($USERNAME))?$USERNAME:'';?>" size="15" name="USERNAME"></TD>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
      <B>Email</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
    <input id="USEREMAIL" type="text" style="width: 200px;" value="<?php echo (isset($USEREMAIL))?$USEREMAIL:'';?>" size="15" name="USEREMAIL"></TD>
  </TR>
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Search title</B></TD> 
    <TD BGCOLOR=#EEEEFF COLSPAN=3 NOWRAP>
    <INPUT NAME="COM" TYPE="text" SIZE=40 VALUE="<?php echo (isset($COM))?$COM:'';?>" style="width: 450px;"></TD>
  </TR>
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Enzyme</B></TD> 
    <TD BGCOLOR=#EEEEFF NOWRAP>
    <SELECT NAME="CLE"">
      <OPTION<?php echo (isset($CLE) and $CLE == 'Trypsin')?' selected':'';?>>Trypsin</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Trypsin/P')?' selected':'';?>>Trypsin/P</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Arg-C')?' selected':'';?>>Arg-C</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Asp-N')?' selected':'';?>>Asp-N</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Asp-N_ambic')?' selected':'';?>>Asp-N_ambic</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Chymotrypsin')?' selected':'';?>>Chymotrypsin</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'CNBr')?' selected':'';?>>CNBr</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'CNBr+Trypsin')?' selected':'';?>>CNBr+Trypsin</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Formic_acid')?' selected':'';?>>Formic_acid</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Lys-C')?' selected':'';?>>Lys-C</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Lys-C/P')?' selected':'';?>>Lys-C/P</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'LysC+AspN')?' selected':'';?>>LysC+AspN</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'Lys-N')?' selected':'';?>>Lys-N</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'PepsinA')?' selected':'';?>>PepsinA</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'semiTrypsin')?' selected':'';?>>semiTrypsin</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'TrypChymo')?' selected':'';?>>TrypChymo</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'TrypsinMSIPI')?' selected':'';?>>TrypsinMSIPI</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'TrypsinMSIPI/P')?' selected':'';?>>TrypsinMSIPI/P</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'V8-DE')?' selected':'';?>>V8-DE</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'V8-E')?' selected':'';?>>V8-E</OPTION>
      <OPTION<?php echo (isset($CLE) and $CLE == 'None')?' selected':'';?>>None</OPTION>
    </SELECT></TD>
   
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT">
    <B>Allow up to</B></TD>
    <TD BGCOLOR=#EEEEFF ALIGN="LEFT" NOWRAP>
    <SELECT NAME="PFA">
    <?php for($i = 0; $i < 9; $i++){
       $selected = '';
       if(isset($PFA) and $PFA == $i){
        $selected = ' selected';
       }
       echo "\t<OPTION". $selected.">".$i."</OPTION>\n";
      }
    ?>
    </SELECT> missed cleavages</TD>
  </TR>
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT"><B>Quantitation</B></TD>
    <TD BGCOLOR=#EEEEFF COLSPAN=3 NOWRAP>
    <SELECT NAME="QUANTITATION">
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'None')?' selected':'';?>>None</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'iTRAQ 4plex')?' selected':'';?>>iTRAQ 4plex</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'iTRAQ 8plex')?' selected':'';?>>iTRAQ 8plex</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'TMT 6plex')?' selected':'';?>>TMT 6plex</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'TMT 2plex')?' selected':'';?>>TMT 2plex</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == '18O multiplex')?' selected':'';?>>18O multiplex</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'SILAC K+6 R+6 multiplex')?' selected':'';?>>SILAC K+6 R+6 multiplex</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'IPTL (Succinyl and IMID) multiplex')?' selected':'';?>>IPTL (Succinyl and IMID) multiplex</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'ICPL duplex pre-digest [MD]')?' selected':'';?>>ICPL duplex pre-digest [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'ICPL duplex post-digest [MD]')?' selected':'';?>>ICPL duplex post-digest [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'ICPL triplex pre-digest [MD]')?' selected':'';?>>ICPL triplex pre-digest [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == '18O corrected [MD]')?' selected':'';?>>18O corrected [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == '15N Metabolic [MD]')?' selected':'';?>>15N Metabolic [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == '15N + 13C Metabolic [MD]')?' selected':'';?>>15N + 13C Metabolic [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'SILAC K+6 R+10 [MD]')?' selected':'';?>>SILAC K+6 R+10 [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'SILAC K+6 R+10 Arg-Pro [MD]')?' selected':'';?>>SILAC K+6 R+10 Arg-Pro [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'SILAC K+6 R+6 [MD]')?' selected':'';?>>SILAC K+6 R+6 [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'SILAC R+6 R+10 [MD]')?' selected':'';?>>SILAC R+6 R+10 [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'SILAC K+8 R+10 [MD]')?' selected':'';?>>SILAC K+8 R+10 [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'SILAC K+4 K+8 R+6 R+10 [MD]')?' selected':'';?>>SILAC K+4 K+8 R+6 R+10 [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'ICAT ABI Cleavable [MD]')?' selected':'';?>>ICAT ABI Cleavable [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'ICAT D8 [MD]')?' selected':'';?>>ICAT D8 [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'Dimethylation [MD]')?' selected':'';?>>Dimethylation [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'NBS Shimadzu [MD]')?' selected':'';?>>NBS Shimadzu [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'Label-free [MD]')?' selected':'';?>>Label-free [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'Average [MD]')?' selected':'';?>>Average [MD]</OPTION>
      <OPTION<?php echo (isset($QUANTITATION) and $QUANTITATION == 'SILAC K+4 K+8 R+4 R+10 [MD]')?' selected':'';?>>SILAC K+4 K+8 R+4 R+10 [MD]</OPTION>
    </SELECT></TD>  
  </TR>
  
   
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Taxonomy</B></TD> 
    <TD BGCOLOR=#EEEEFF COLSPAN=3 NOWRAP>
      <SELECT NAME="TAXONOMY">
      <?php 
      foreach($SELECT_TAXONOMY_arr as $theLine){
        if(isset($TAXONOMY)){
          $theValue = preg_replace('/<OPTION>|<\/OPTION>/i', '',trim($theLine));
          if($theValue == $TAXONOMY){
            $theLine = preg_replace('/<OPTION>/i', '<OPTION selected>', $theLine);
          }
        }
        echo $theLine;
      }
      ?>
      </SELECT></TD>
  </TR>
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Peptide&nbsp;tol.&nbsp;&#177;</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
    <INPUT NAME="TOL" VALUE="<?php echo (isset($TOL))? $TOL:"1.2";?>" SIZE=7 TYPE="text">
    <SELECT NAME="TOLU">
      <OPTION<?php echo (isset($TOLU) and $TOLU == 'Da')?' selected': '';?>>Da</OPTION>
      <OPTION<?php echo (isset($TOLU) and $TOLU == 'mmu')?' selected': '';?>>mmu</OPTION>
      <OPTION<?php echo (isset($TOLU) and $TOLU == '%')?' selected': '';?>>%</OPTION>
      <OPTION<?php echo (isset($TOLU) and $TOLU == 'ppm')?' selected': '';?>>ppm</OPTION>
    </SELECT>&nbsp;&nbsp;&nbsp;<B>#&nbsp;<sup>13</sup>C</B>&nbsp;
    <SELECT NAME="PEP_ISOTOPE_ERROR">
      <?php 
      for($i = 0; $i < 3; $i++){
        $selected = '';
        if(isset($PEP_ISOTOPE_ERROR) and $PEP_ISOTOPE_ERROR == $i){
          $selected = ' selected';
        }
        echo "\t<OPTION".$selected.">$i</OPTION>\n";
      }
      ?>
    </SELECT>    </TD>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>MS/MS tol.&nbsp;&#177;</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
    <INPUT NAME="ITOL" VALUE="<?php echo (isset($ITOL))? $ITOL:"0.6";?>" SIZE=7 TYPE="text">
    <SELECT NAME="ITOLU">
      <OPTION<?php echo (isset($ITOLU) and $ITOLU == 'Da')?' selected': '';?>>Da</OPTION>
      <OPTION<?php echo (isset($ITOLU) and $ITOLU == 'mmu')?' selected': '';?>>mmu</OPTION>
    <?php if(defined('MASCOT_VERSION_GT23') and MASCOT_VERSION_GT23){?>
      <OPTION<?php echo (isset($ITOLU) and $ITOLU == 'ppm')?' selected': '';?>>ppm</OPTION>
    <?php }?>
      
    </SELECT></TD>
  </TR>
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Peptide&nbsp;charge</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
    
    <SELECT NAME="CHARGE">
      <OPTION<?php echo (isset($CHARGE) and $CHARGE == '2-, 3- and 4-')?" selected":"";?>>2-, 3- and 4-</OPTION>
      <OPTION<?php echo (isset($CHARGE) and $CHARGE == '2- and 3-')?" selected":"";?>>2- and 3-</OPTION>
      <OPTION<?php echo (isset($CHARGE) and $CHARGE == '1-, 2- and 3-')?" selected":"";?>>1-, 2- and 3-</OPTION>
      <OPTION<?php echo (isset($CHARGE) and $CHARGE == '1+, 2+ and 3+')?" selected":"";?>>1+, 2+ and 3+</OPTION>
      <OPTION<?php echo (isset($CHARGE) and $CHARGE == '2+ and 3+')?" selected":"";?>>2+ and 3+</OPTION>
      <OPTION<?php echo (isset($CHARGE) and $CHARGE == '2+, 3+ and 4+')?" selected":"";?>>2+, 3+ and 4+</OPTION>
    <?php 
      for($i = -8; $i< 9; $i++){
        $selected = '';
        if(!$i){
          $value = 'Mr';
        }else if($i<0){
          $value = abs($i) . "-";
        }else{
          $value = $i . "+";
        }
        if(isset($CHARGE) and $CHARGE == '$value'){
          $selected = ' selected';
        }
        echo "<OPTION".$selected.">".$value."</OPTION>\n";
      }
    ?>
       
      
       
    </SELECT></TD>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Monoisotopic</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
    <INPUT TYPE="radio" VALUE="Monoisotopic" NAME="MASS"<?php echo (isset($MASS) and $MASS == 'Monoisotopic')?" checked":"";?>>&nbsp;&nbsp;<B></B>&nbsp;
    <INPUT TYPE="radio" VALUE="Average" NAME="MASS"<?php echo (isset($MASS) and $MASS == 'Average')?" checked":"";?>></TD> 
  </TR>
  
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Instrument</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
      <SELECT NAME="INSTRUMENT">
       <?php 
      foreach($SELECT_INSTRUMENT_arr as $theLine){
        if(isset($INSTRUMENT)){
          $theValue = preg_replace('/<OPTION>|<\/OPTION>/i', '',trim($theLine));
          if($theValue == $INSTRUMENT){
            $theLine = preg_replace('/<OPTION>/i', '<OPTION selected>', $theLine);
          }
        }
        echo $theLine;
      }
      ?>
      </SELECT></TD>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Error&nbsp;tolerant</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
    <INPUT TYPE="checkbox" NAME="ERRORTOLERANT" VALUE="1"<?php echo (isset($ERRORTOLERANT) and $ERRORTOLERANT)?" checked":"";?>></TD>
  </TR>
  
  <TR>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP>
    <B>Decoy</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP><INPUT TYPE="checkbox" NAME="DECOY" VALUE="1"<?php echo (isset($DECOY) and $DECOY)?" checked":"";?>></TD>
    <TD BGCOLOR=#EEEEFF ALIGN="RIGHT" NOWRAP><B>Report top</B></TD>
    <TD BGCOLOR=#EEEEFF NOWRAP>
      <SELECT NAME="REPORT">
      <OPTION<?php echo (isset($REPORT) and $REPORT == 'AUTO')?" selected":"";?>>AUTO</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '5')?" selected":"";?>>5</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '10')?" selected":"";?>>10</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '20')?" selected":"";?>>20</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '30')?" selected":"";?>>30</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '50')?" selected":"";?>>50</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '100')?" selected":"";?>>100</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '200')?" selected":"";?>>200</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '300')?" selected":"";?>>300</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '400')?" selected":"";?>>400</OPTION>
      <OPTION<?php echo (isset($REPORT) and $REPORT == '500')?" selected":"";?>>500</OPTION>
    </SELECT>&nbsp;hits</TD>
  </TR>
</TABLE>