<?php
   require "layout.inc.php";

   if (!isset($month) || $month == "" || $month > 12 || $month < 1) {
      $month = @date("m");
   }
   if (!isset($year) || $year == "" || $year < 1972 || $year > 2036) {
      $year = @date("Y");
   }
  
   $timestamp = @mktime(0, 0, 0, $month, 1, $year);
   $current = @date("F Y", $timestamp);
   if ($month < 2){
      $prevmonth = 12;
      $prevyear = $year - 1;
   }else{
      $prevmonth = $month - 1;
      $prevyear = $year;
   }
   
   if ($month > 11) {
      $nextmonth = 1;
      $nextyear = $year + 1;
   }else{
      $nextmonth = $month + 1;
      $nextyear = $year;
   }
   $backward = @date("F Y", @mktime(0, 0, 0, $prevmonth, 1, $prevyear));
   $forward = @date("F Y", @mktime(0, 0, 0, $nextmonth, 1, $nextyear));
   $first = @date("w", @mktime(0, 0, 0, $month, 1, $year));
   $lastday = 28;
   for ($i=$lastday;$i<32;$i++){
      if (checkdate($month, $i, $year)){
         $lastday = $i;
      }
   }
   
   function AddDay($fday, $fmonth, $fyear, $fvar)
   {  global $calendar_bg_color;
      global $current_day_color;
      global $crtPro;
      global $tableName;
      global $PHP_SELF;
      global $open_dir_ID;
      
      if (!isset($fday) || $fday == "")
      {
         echo '	<TD BGCOLOR=#'.$calendar_bg_color.' width= height=70>
		&nbsp;
';
      }
      else
      {
         $schurl = $PHP_SELF.'?day='.$fday.'&month='.$fmonth.'&year='.$fyear.'&open_dir_ID='.$open_dir_ID.'&tableName='.$tableName.'&crtPro='.$crtPro;
         if (@date("m") == $fmonth && @date("Y") == $fyear && @date("j") == $fday)
         {
            echo '	<TD BGCOLOR=#'.$current_day_color.' ALIGN=CENTER ID="day'.$fday.'" class="curday" align="left" valign="top" width= height=70 
		onMouseOver="tdmouseover(\'day'.$fday.'\')"; onMouseOut="tdcurmouseout(\'day'.$fday.'\')"; 
		onClick="javascript:document.location=\''.$schurl.'\'">
';
         }
         else
         {
            echo '	<TD BGCOLOR=#'.$calendar_bg_color.' ALIGN=CENTER ID="day'.$fday.'" class="calendar"  align="left" valign="top" width= height=70 
		onMouseOver="tdmouseover(\'day'.$fday.'\')"; onMouseOut="tdmouseout(\'day'.$fday.'\')"; 
		onClick="document.location=\''.$schurl.'\'">
';
         }
         echo '		'.$fday.'<br>
';
         if (isset($fvar) && $fvar != "")
         {
            echo '		<A class=\'calendar\' style="cursor: hand" onClick="javascript:window.open(\''.$schurl.'\', 
		\'schedule\', \'width=534,height=400,scrollbars=yes,resizable=yes\')">
';
            echo '		'.$fvar.'
		</A>';
         }
      }
      echo '	</TD>
';
   }//end function


	echo '
	<SCRIPT LANGUAGE="JavaScript">
	<!-- 
		var isIE;
		isIE = (document.all) ? true : false;

		function tdmouseover(itemID)
		{
		   if(isIE)
		   {
		      var theObj = eval("document.all." + itemID);
			
		      theObj.style.backgroundColor = \'#'.$mouse_over_color.'\';
		   }
		}

		function tdmouseout(itemID)
		{
		   if(isIE)
		   {
		      var theObj = eval("document.all." + itemID);

		      theObj.style.backgroundColor = \'#'.$calendar_bg_color.'\';
		   }
		}
		
		function tdcurmouseout(itemID)
		{
		   if(isIE)
		   {
		      var theObj = eval("document.all." + itemID);

		      theObj.style.backgroundColor = \'#'.$current_day_color.'\';
		   }
		}
	//-->
	</SCRIPT>
';
	echo '
	<CENTER>
  
	<TABLE cellspacing=0 cellpadding=0 width=95% border=0>
  <FORM METHOD="post" ACTION="'.$PHP_SELF.'" name=calendar>
   
  <INPUT TYPE=HIDDEN NAME=crtPro VALUE='.$crtPro.'>
  <INPUT TYPE=HIDDEN NAME=tableName value='.$tableName.'>
  <INPUT TYPE=HIDDEN NAME=open_dir_ID value='.$open_dir_ID.'>
	<TR>
	<TD class="form" align="center" valign="bottom" width="100%" COLSPAN=7>
		
		<TABLE class="form" cellspacing=0 cellpadding=0 width="100%" border=0>
    <TR>
		<TD align="left" valign="bottom">
		<select name="month">
';
			for ($j=1;$j<=12;$j++)
			{
			   echo'<option value='.$j;
			   if ($month == $j)
			   {
			      echo ' selected';
			   }
			   echo '>'.@date("F", @mktime(0, 0, 0, $j, 1, 0)).'
			   ';
			}
			echo '</select>			
		         <select name="year">
';
			for ($j=2005;$j<=2010;$j++)
			{
			   echo'<option value='.$j;
			   if ($year == $j)
			   {
			      echo ' selected';
			   }
			   echo '>'.$j.'
			   ';
			}
			echo '			</select>
		</TD>
    </TR>
    <TD align=center><center><br>
    <input type="submit" value="Go">
    <input type="button" value="Today" onClick="javascript: document.location=\''.$PHP_SELF.'?open_dir_ID='.$open_dir_ID.'&tableName='.$tableName.'&crtPro='.$crtPro.'&year='.@date("Y").'&month='.@date("m").'&day='.@date("d").'\'">
		</TD>
		</TR>
		</TABLE><br>
	</TD>
	</TR>
	<TR>
	<TD COLSPAN=7 align=center>
		<TABLE width=100% height=20 cellspacing=0 cellpadding=0 border=0 BGCOLOR="#004080">
		<TR>
		<TD class="ends" nowrap align="center" valign="bottom" width=10%>
			&nbsp;&nbsp;<A HREF="'.$PHP_SELF.'?month='.$prevmonth.'&year='.$prevyear.'&open_dir_ID='.$open_dir_ID.'&tableName='.$tableName.'&crtPro='.$crtPro.'"><img src="./images/icon_month_pre.gif" border=0></a>
		</TD>
		<TD align="center" width=80%><font color=#ffffff><b>
';
   echo  $current.'
		</font></b></TD>
		<TD class="ends" nowrap align="center" valign="bottom" width=10%>
			&nbsp; <A HREF="'.$PHP_SELF.'?month='.$nextmonth.'&year='.$nextyear.'&open_dir_ID='.$open_dir_ID.'&tableName='.$tableName.'&crtPro='.$crtPro.'"><img src="./images/icon_month_next.gif" border=0></a>&nbsp;&nbsp;
		</TD>
		</TR>
		</TABLE>
	</TD>
	</TR>
  </FORM>
  </TABLE>
  <TABLE cellspacing=0 cellpadding=0 width=95% border=0><tr><td bgcolor=black>
  <TABLE cellspacing=1 cellpadding=0 width=100% border=0>
	<TR>';
   if (isset($start_day) && $start_day <= 6 && $start_day >= 0){
      $n = $start_day;
   } else {
      $n = 0;
   } 
   for ($i=0;$i<7;$i++) {
      if ($n > 6) {
         $n = 0;
      }
      if ($n == 0){
         echo '	<TD BGCOLOR="#8a8a8a" class="days" nowrap align="center" valign="middle" width=20 height=20>
		S
	</TD>';
      }
      if ($n == 1){
         echo '	<TD BGCOLOR="#8a8a8a" class="days" nowrap align="center" valign="middle" width= height=20>
		M
	</TD>';
      }
      if ($n == 2){
         echo '	<TD BGCOLOR="#8a8a8a" class="days" nowrap align="center" valign="middle" width= height=20>
		T
	</TD>';
      }
      if ($n == 3){
         echo '	<TD BGCOLOR="#8a8a8a" class="days" nowrap align="center" valign="middle" width= height=20>
		W
	</TD>';
      }
      if ($n == 4){
         echo '	<TD BGCOLOR="#8a8a8a" class="days" nowrap align="center" valign="middle" width= height=20>
		TH
	</TD>';
      }
      if ($n == 5)
      {
         echo '	<TD BGCOLOR="#8a8a8a" class="days" nowrap align="center" valign="middle" width= height=20>
		F
	</TD>';
      }
      if ($n == 6)
      {
         echo '	<TD BGCOLOR="#8a8a8a" class="days" nowrap align="center" valign="middle" width= height=20>
		S
	</TD>';
      }
      $n++;
   }
   echo'	</TR>
';
   $calday = 1;
   while ($calday <= $lastday)
   {
/* Alternate beginning day of the week for calendar view was created by Marion Heider of clixworx.net. */
      echo '<TR>';
      for ($j=0;$j<7;$j++)
      {
         if ($j == 0)
         {
            $n = $start_day;
         }
         else
         {
            if ($n < 6)
            {
               $n = $n + 1;
            }
            else
            {
               $n = 0;
            }
         }
         if ($calday == 1)
         {
            if ($first == $n)
            { 
               AddDay($calday, $month, $year, $info);
               $calday++;
            }
            else
            {
               AddDay('', '', '', '');
            }
         }
         else
         {
            if ($calday > $lastday)
            {
               AddDay('', '', '', '');
            }
            else
            {
               //$info = FillDay($db, $n, $calday, $month, $year);
               AddDay($calday, $month, $year, $info);
               $calday++;
            }
         }
      } 
      echo '</TR>';
   }
   echo '	</TABLE>
   </TABLE>
	</CENTER>';
?>
