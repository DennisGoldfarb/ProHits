<?php 
$introduction   = 'files/Prohits_manual_intro_2015_v2.pdf';
$analyst_manual = 'files/Prohits_Analyst_v20.pdf';
$manager_manual = 'files/DataManagement_v11.pdf';

$analyst_mv     = 'movies/analyst.mp4';
$manager_mv     = 'movies/msManager.mp4';

$introduction_date = @date("F j, Y", filemtime($introduction));
$analyst_manual_date = @date("F j, Y", filemtime($analyst_manual));
$manager_manual_date = @date("F j, Y", filemtime($manager_manual));
$analyst_mv_date = @date("F j, Y", filemtime($analyst_mv));
$manager_mv_date = @date("F j, Y", filemtime($manager_mv));
?>
<html>
  <head>
<style type=text/css>
body { margin: 5; }
a:link,a:visited,a:hover{
        COLOR: black;
        FONT-FAMILY: arial,sans-serif;
        TEXT-DECORATION: none
		    }
body,td{font-family:arial,sans-serif;font-size : 14px; }
</style>
  </head> 
  <body background=../images/site_bg.gif bgcolor="#CACACA">
    <table border="0" cellpadding="0" cellspacing="1" width="100%" height=100% valign="middle">
      <tr>
        <td width="100%" align="center" valign="middle"> 
          <table border="0" cellpadding="1" cellspacing="0" width="750" bgcolor="black">
            <tr>
              <td width="749">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="white" valign="middle">
                  <tr>
                    <td bgcolor="white">
                      <img src="../images/top_white.gif" width="749" height="116" border="0">
                    </td>
                  </tr>
                  <tr height="1">
                    <td height="1" bgcolor="white"><img src="../images/empty_dot.gif" width="1" height="1" border="0"></td>
                  </tr>
                  <tr>
                    <td bgcolor="#3F569B" height="23" align=right>&nbsp;<a href="../"><font color="#FFFFFF"><b>HOME</b></font></a> &nbsp;&nbsp;</td>
                  </tr>
                  <tr height="1">
                    <td height="1" bgcolor="white"><img src="../images/empty_dot.gif" width="1" height="1" border="0"></td>
                  </tr>
                  <tr>
                    <td align="center">
                    
<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 align=center width=80%>
 <TR>
    <TD colspan=2><br><font size="+1" color="#008000"><b>Documents</b></font></TD>
</TR>
<TR><TD bgcolor="#bcbcbc"  colspan=2><img width=1 height=1></TD><TR>
<TR>
    <TD valign=top><br><a href="./<?php echo $introduction;?>" target=_balck><img src="../images/pdf.gif" border=0></a></TD>
    <TD valign="bottom"><b>User Manual Introduction</b><br>
        last updated on: <?php echo $introduction_date;?> 
    </TD>
</TR>
<TR><TD bgcolor="#bcbcbc"  colspan=2><img width=1 height=1></TD><TR>
<TR>
    <TD valign=top><br><a href="./<?php echo $analyst_manual;?>" target=_balck><img src="../images/pdf.gif" border=0></a></TD>
    <TD valign="bottom"><b>Analyst User Manual</b><br>
        last updated on: <?php echo $analyst_manual_date;?> 
    </TD>
</TR>
<TR><TD bgcolor="#bcbcbc"  colspan=2><img width=1 height=1></TD><TR>
<TR>
<TR>
    <TD valign=top><br><a href="./<?php echo $manager_manual;?>" target=_balck><img src="../images/pdf.gif" border=0></a></TD>
    <TD valign="bottom"><b>MS Data Management User Manual</b><br>
        last updated on: <?php echo $manager_manual_date;?>
    </TD>
</TR>
<TR><TD bgcolor="#bcbcbc"  colspan=2><img width=1 height=1></TD><TR>
<TR>
    <TD colspan=2><br><font size="+1" color="#008000"><b>View instructional videos</b></font></TD>
</TR>
<TR><TD bgcolor="#bcbcbc"  colspan=2><img width=1 height=1></TD><TR>
<TR>
    <TD valign=top><br><a href="http://prohitsms.com/Prohits/doc/mv_play.php?mv=movies/analyst.mp4" target=_balck><img src="../images/mv.gif" border=0></a></TD>
    <TD valign="bottom"><b>Exploring search results in the Analyst module</b><br>
       
    </TD>
</TR> 
<TR><TD bgcolor="#bcbcbc"  colspan=2><img width=1 height=1></TD><TR>
<TR>
    <TD valign=top><br><a href="http://prohitsms.com/Prohits/doc/mv_play.php?mv=?mv=movies/msManager.mp4" target=_balck><img src="../images/mv.gif" border=0></a></TD>
    <TD valign="bottom"><b>Overview of the MS Data Management module</b><br>
        
    </TD>
</TR> 
<TR><TD bgcolor="#bcbcbc"  colspan=2><img width=1 height=1></TD><TR>
</TABLE>                
                     </td>
                  </tr>
                  <tr height="23">
                    <td bgcolor="#ffffff" height=12><img src="../images2/empty_dot.gif" width="1" height="1" border="0"></td>
                  </tr>
                   
                </table>
              </td>
            </tr>
          </table>
            <font face="Arial" size=1 color=black>Copyright &copy; 2010 <a href='http://www.lunenfeld.ca/researchers/gingras' class=button target=blank><font size=1>Gingras</font></a> and <a href='http://www.tyerslab.com/' class=button target=blank><font size=1>Tyers</font></a> labs, Samuel Lunenfeld Research Institute, Mount Sinai Hospital.</font>
        </td>
       </tr>
    </table>      
  </body>
</html>