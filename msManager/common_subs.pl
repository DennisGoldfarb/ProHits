#!/usr/local/bin/perl
##############################################################################
# Common subroutines for cgi scripts                                         #
##############################################################################
#    $Archive:: /www/cgi/common_subs.pl                                    $ #
#     $Author: johnc $ #
#       $Date: 2007/02/25 12:37:12 $ #
#   $Revision: 1.19 $ #
# $NoKeywords::                                                            $ #
##############################################################################
# COPYRIGHT NOTICE                                                           #
# Copyright 1998-2006 Matrix Science Limited  All Rights Reserved.           #
#                                                                            #
# This program may be used and modified within the licensee's organisation   #
# provided that this copyright notice remains intact. Distribution of this   #
# program or parts thereof outside the licensee's organisation without the   #
# prior written consent of Matrix Science Limited is expressly forbidden.    #
##############################################################################
 use strict;                                                                #
##############################################################################

  my @configFile;
  
# ensure this library returns true
  1;

###############################################################################
# &readConfig()
# slurp mascot.dat into an array
###############################################################################

sub readConfig {

  open(TEMPFILE, "../config/mascot.dat") 
    || &fatal("Cannot open mascot.dat", __LINE__, __FILE__, "");
  @configFile = <TEMPFILE>;
  close(TEMPFILE) 
    || &fatal("Cannot close mascot.dat", __LINE__, __FILE__, "");

}

###############################################################################
# &fatal()
# prints fatal error message to browser then dies
# $_[0] error message
# $_[1] line number
# $_[2] script name
# $_[3] dat file name
###############################################################################

sub fatal {

  my $message = shift;
  my $line_number = shift;
  my $script = shift;
  my $datFile = shift;
  print "\n\n<HTML><HEAD><TITLE>Fatal Error</TITLE></HEAD>\n<BODY>\n";
  print "<H1>Fatal Error</H1>\n";
  print "<P><B>$message</B><P></BODY></HTML>\n\n";
  if($datFile) {
    die "[" . &dateTime() . "] $script stopped at line $line_number when processing $datFile: $message\n";
  } else {
    die "[" . &dateTime() . "] $script stopped at line $line_number: $message\n";
  }
  
}

###############################################################################
# &dateTime()
# returns current date & time in ISO format
###############################################################################

sub dateTime {

  my $year = (localtime(time))[5]+1900;
  my $month = (localtime(time))[4]+1;
  my $day = (localtime(time))[3];    
  my $hour = (localtime(time))[2];
  my $minute = (localtime(time))[1];
  my $second = (localtime(time))[0];    
  return sprintf("%04d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $hour, $minute, $second);

}

###############################################################################
# &printHeader1()
# prints the HTML page <HEAD> block
# $_[0] title text
###############################################################################

sub printHeader1 {
  
  my $titleText = shift;
  
  print <<"end_of_static_HTML_text_block";
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<HTML>
<HEAD>

<TITLE>$titleText</TITLE>

<META NAME="description" content="Matrix Science offers the Mascot search engine for rapid protein identification using mass spectrometry data">
<META NAME="keywords" content="mascot, matrix science, mowse, proteomics, bioinformatics, protein identification, protein characterization, database search, mass spectrometry, spectrum, spectra, electrospray, maldi, ms/ms, ms-ms, lcms, peptide mass fingerprint, amino acid">
<META NAME="author" content="Matrix Science Limited">
<META NAME="robots" content="all">
<META NAME="copyright" content = "2006 Matrix Science Limited">
<link href="../templates/matrixscience.css" rel=stylesheet type="text/css">
<script language="JavaScript" src="../templates/level1.js"></script>
<script language="JavaScript" src="../templates/browser.js"></script>
end_of_static_HTML_text_block
 
}

###############################################################################
# &printHeader2()
# prints the HTML page header (menu and htdig search form)
# $_[0] javascript to go in <BODY> tag
# $_[1] text for this page in navigation bar
# $_[2] flag to include Mascot as lower level in navigation bar
###############################################################################

sub printHeader2 {

  my $javaText = shift;
  my $pageText = shift;
  my $mascotFlag = shift;
  my $htsearch = "htsearch";

  if ($ENV{'WINDIR'}) {
    $htsearch = "htsearch.exe";
  }

  print <<"end_of_static_HTML_text_block";
</HEAD>

<BODY LEFTMARGIN=0 MARGINWIDTH=0 TOPMARGIN=0 MARGINHEIGHT=0 BGCOLOR=#FFFFFF TEXT=#000000 LINK=#0000FF ALINK=#FF0000 VLINK=#800080 $javaText>
<table width=100% height=100% cellspacing=0 cellpadding=0 border=0>
<tr><!-- start header -->
<td valign=top height=100><form name="SiteSearch" method="post" action="../cgi/$htsearch"><table width=100% cellspacing=0 cellpadding=0 border=0><tr><td><table width=100% cellspacing=0 cellpadding=0 border=0>
<tr bgcolor=#EEEEFF><td width=120><a href="http://www.matrixscience.com/"><img src="../images/logo_120x50.gif" height=50 width=120 border=0 alt="Matrix Science"></a></td>
<td align=right valign=bottom><table cellspacing=0 cellpadding=0 border=0>

<tr><!-- start menu -->
<td><a href="../home.html" onMouseOver="actMenuItem('home')" onMouseOut="inactMenuItem('home')"><img src="../images/home_off.gif" height=12 width=37 border=0 name="home" alt="Home"></a></td>
end_of_static_HTML_text_block

  if (-e "../cgi/www.pl") {
    print <<"end_of_static_HTML_text_block";
<td><img src="../images/spacer.gif" height=12 width=3 border=0></td>
<td><a href="../whats_new.html" onMouseOver="actMenuItem('whatsnew')" onMouseOut="inactMenuItem('whatsnew')"><img src="../images/whatsnew_off.gif" height=12 width=72 border=0 name="whatsnew" alt="What's New"></a></td>
end_of_static_HTML_text_block
  }
  
  print <<"end_of_static_HTML_text_block";
<td><img src="../images/spacer.gif" height=12 width=3 border=0></td>
<td><a href="../search_form_select.html" onMouseOver="actMenuItem('mascot')" onMouseOut="inactMenuItem('mascot')"><img src="../images/mascot_off.gif" height=12 width=50 border=0 name="mascot" alt="Mascot"></a></td>
<td><img src="../images/spacer.gif" height=12 width=3 border=0></td>
<td><a href="../help_index.html" onMouseOver="actMenuItem('help')" onMouseOut="inactMenuItem('help')"><img src="../images/help_off.gif" height=12 width=30 border=0 name="help" alt="Help"></a></td>
end_of_static_HTML_text_block

  if (-e "../cgi/www.pl") {
    print <<"end_of_static_HTML_text_block";
<td><img src="../images/spacer.gif" height=12 width=3 border=0></td>
<td><a href="../products.html" onMouseOver="actMenuItem('products')" onMouseOut="inactMenuItem('products')"><img src="../images/products_off.gif" height=12 width=62 border=0 name="products" alt="Products"></a></td>
<td><img src="../images/spacer.gif" height=12 width=3 border=0></td>
<td><a href="../support.html" onMouseOver="actMenuItem('support')" onMouseOut="inactMenuItem('support')"><img src="../images/support_off.gif" height=12 width=54 border=0 name="support" alt="Support"></a></td>
<td><img src="../images/spacer.gif" height=12 width=3 border=0></td>
<td><a href="../training.html" onMouseOver="actMenuItem('training')" onMouseOut="inactMenuItem('training')"><img src="../images/training_off.gif" height=12 width=57 border=0 name="training" alt="Training"></a></td>
<td><img src="../images/spacer.gif" height=12 width=3 border=0></td>
<td><a href="../contact.html" onMouseOver="actMenuItem('contact')" onMouseOut="inactMenuItem('contact')"><img src="../images/contact_off.gif" height=12 width=56 border=0 name="contact" alt="Contact"></a></td>
end_of_static_HTML_text_block
  }

  print <<"end_of_static_HTML_text_block";
<td width=3>&nbsp;</td>
<td align=right><table cellspacing=0 cellpadding=0  border=0 bgcolor=#4C69BF>
<tr><td valign=bottom><img src="../images/reflex_36x18.gif" height=36 width=18 border=0></td>
<td><input type=hidden name="config" value="htdig">
<input type=hidden name="restrict" value="">
<input type=hidden name="exclude" value="">
<input type=hidden name="method" value="and">
<input type=hidden name="format" value="builtin-long">
<input type=hidden name="sort" value="score">
<font size=-1 style="font-size: 10px;"><INPUT type=text name="words" value="Search" size=8 style="width: 100px; height: 20px;"></font></td>
<td width=6>&nbsp;</td>
<td><INPUT type=image src="../images/go_button.gif" name=htdig height=21 width=25 border=0 alt="Go"></td>
<td width=6>&nbsp;</td>
</tr></table></td>
</tr><!-- end menu -->

</table></td></tr></table></td></tr><tr><td width=100% align=left>
<table width=100% cellspacing=0 cellpadding=0 border=0 bgcolor=#4C69BF>

<tr><!-- start navigation -->
<td width=5% height=24>&nbsp;</td><td width=65% nowrap>
end_of_static_HTML_text_block

if ($mascotFlag) {
  print "<A HREF=\"../search_form_select.html\" class=\"menubar\"><FONT COLOR=#ffffff>Mascot</FONT></A>\n";
  print "<FONT COLOR=#ffffff> &gt; </FONT>\n";
}

if (-e "../cgi/www.pl") {

print <<"end_of_static_HTML_text_block";
<FONT COLOR=#ffffff>$pageText</FONT>
</td><td width=25% nowrap align=right>&nbsp;</td><td width=5% height=24>&nbsp;</td>
end_of_static_HTML_text_block

} else {
  
print <<"end_of_static_HTML_text_block";
<FONT COLOR=#ffffff>$pageText</FONT>
</td><td width=25% nowrap align=right>&nbsp;<script language="JavaScript">
<!-- hide from older browser
document.write(login_text); 
//-->
</script></td><td width=5% height=24>&nbsp;</td>
end_of_static_HTML_text_block

}

print <<"end_of_static_HTML_text_block";
</tr><!-- end navigation -->

</table></td></tr></table>&nbsp;</form></td>
</tr><!-- end header -->

<tr><!-- start body - single column -->
<td valign=top><table width=100% cellspacing=0 cellpadding=0 border=0><tr>
<td width=5%>&nbsp;</td><!-- left hand margin -->
<td width=90%><!-- start content column -->

end_of_static_HTML_text_block
  
}

###############################################################################
# &printFooter()
# prints HTML footer (copyright notice)
###############################################################################

sub printFooter {

  print <<"end_of_static_HTML_text_block";


</td><!-- end content column -->
<td width=5%>&nbsp;</td><!-- right hand margin -->
</tr><tr>
<td colspan=3 height=12>&nbsp;</td><!-- bottom margin -->
</tr></table></td>
</tr><!-- end body - single column -->

<tr><!-- begin footer -->
<td valign=bottom><table width=100% height=24 cellspacing=0 cellpadding=0 border=0 bgcolor=#4C69BF><tr>
<td align=center nowrap><FONT COLOR=#ffffff SIZE=-1 style="font-size: 10px;">Copyright &#169; 2006 </FONT><A 
HREF="http://www.matrixscience.com/" class="menubar"><FONT COLOR=#ffffff SIZE=-1 
style="font-size: 10px;">Matrix Science Ltd.</FONT></A><FONT COLOR=#ffffff SIZE=-1
style="font-size: 10px;"> All Rights Reserved.</FONT></td>
</tr></table></td></tr></table>
end_of_static_HTML_text_block

}

###############################################################################
# &decompress()
# decompresses result file if extension is Z or gz or bz2
# returns filename on success
# $_[0] filename (.dat extension)
# globals:
###############################################################################

sub decompress{

  my(
    $expand,
    $extension,
  );

  my $inFile = shift;

# www.pl contains the code to decrypt result file names
# its existence indicates that this script is running on the public web site
  if (-e "../cgi/www.pl") {
    $inFile = &decrypt($inFile);
  }

  if (-r $inFile) {
    $expand = "";
  } elsif (-r "$inFile.Z") {
    if ($ENV{'WINDIR'}){
      $expand = "../bin/gzip.exe";
    } else {
    # prefer gzip over compress for *.Z
      $expand = `which gzip`;
      chomp $expand;
      unless ($expand =~ /^\//) {
        $expand = "compress";
      }
    }
    $extension = "Z";
  } elsif (-r "$inFile.gz") {
    if ($ENV{'WINDIR'}){
      $expand = "../bin/gzip.exe";
    } else {
      $expand = "gzip";
    }
    $extension = "gz";
  } elsif (-r "$inFile.bz2") {
    if ($ENV{'WINDIR'}){
      $expand = "../bin/bzip2.exe";
    } else {
      $expand = "bzip2";
    }
    $extension = "bz2";
  } else {
    return "";
  }
  
  if ($expand) {
    my $sysCall = "$expand -d $inFile.$extension";
    if ($ENV{'WINDIR'}){
      $sysCall =~ s#/#\\#g;
    }
    if(system($sysCall)){
      return "";  # failed to expand the result file
    }
  }

  return $inFile;

}

###############################################################################
# &getConfigParam()
# case insensitive search of mascot.dat for labelled line
# on success, returns complete line (including keyword)
# $_[0] is section in mascot.dat
# $_[1] is label in mascot.dat
###############################################################################

sub getConfigParam{

  my $section = shift;
  my $label = shift;

  my $inWrongSection = 0;
  my $inRightSection = 0;
  for (my $i = 0; $i <= $#configFile; $i++){    
    if ($inWrongSection == 0 && $inRightSection == 0){
      if ($configFile[$i] =~ /^(databases|parse|www|taxonomy_\d+|cluster|unigene|options|cron)[\s\n#]/i){
        if ($configFile[$i] =~ /^$section[\s\n#]/i){
          $inRightSection = 1;
        } else {
          $inWrongSection = 1;
        }
      }
    } elsif ($inRightSection) {
      if ($configFile[$i] =~ /^$label[\s\n=#]/i){
        return $configFile[$i];
      } else {
        if ($configFile[$i] =~ /^end[\s\n#]/i) {
          return 0;
        }
      }
    } else {
      if ($configFile[$i] =~ /^end[\s\n#]/i) {
        $inWrongSection = 0;
      }
    }
  }
  return 0;
}

###############################################################################
# &getReport()
# executes a command, directly for localhost, else via HTTP
# returns raw command output on success
# $_[0] host
# $_[1] service
# $_[2] file
###############################################################################

sub getReport{

  my $host = shift;
  my $service = shift;
  my $file = shift;
  my $output;
  
# untaint arguments, which are all safe because they can only come from mascot.dat
  ($host) = $host =~ /(.*)/;
  ($service) = $service =~ /(.*)/;
  ($file) = $file =~ /(.*)/;

  if ($host eq "localhost"){
    
    open SOCK, $file . " |"
      || return 0;
    binmode(SOCK);
    my $buffer;
    while(read(SOCK, $buffer, 1048576)){
      $output .= $buffer;
    }
    close (SOCK) 
      || return 0;
      
  } else {

    my $ua = new LWP::UserAgent;
    my $req;
    if ($host =~ /@/) {
      my $auth;
      ($auth, $host) = split(/@/, $host);
      my ($user,$password) = split(/:/, $auth);
      $req = new HTTP::Request GET => "http://$host:$service$file";
      $req->authorization_basic($user, $password);
    } else {
      $req = new HTTP::Request GET => "http://$host:$service$file";
    }
    my $tempString;
    if (($tempString = &getConfigParam("Options", "proxy_server")) ||
      ($tempString = &getConfigParam("WWW", "proxy_server"))) {
      chomp $tempString;
      $tempString =~ s/proxy_server\s+//i;
      if ($tempString) {
        my($user, $password);
        $ua->proxy('http' => $tempString);
        $user = "" unless (($user = &getConfigParam("Options", "proxy_username")) ||
        	($user = &getConfigParam("WWW", "proxy_username")));
        chomp $user;
        $user =~ s/proxy_username\s+//i;
        $password = "" unless (($password = &getConfigParam("Options", "proxy_password")) ||
          ($password = &getConfigParam("WWW", "proxy_password")));
        chomp $password;
        $password =~ s/proxy_password\s+//i;
        if ($user || $password) {
          $req->proxy_authorization_basic($user, $password);
        }
      } else {
        $ua->env_proxy;   # initialize from environment variables
      }
    } else {
      $ua->env_proxy;   # initialize from environment variables
    }
    my $result = $ua->request($req);
    if ($result->is_success) {
      $output = $result->content;
    } else {
      return 0;
    }

  }

  return $output;

}

###############################################################################
# &parseTitle()
# parse description from FASTA title line
# returns parsed text on success, empty string on failure
# $_[0] is text to be parsed
# $_[1] is the database name
###############################################################################

sub parseTitle{

  my $text = shift;
  my $db_name = shift;

# Try to find the correct parse rule to extract description from text returned by ms-getseq.exe
  my ($tempString, $parseString);
  if ($tempString = &getConfigParam("Databases", $db_name)) {
    my $parseRule = "RULE_" . (split(/\s+/, $tempString))[11];
    if ($parseString = &getConfigParam("PARSE", $parseRule)) {
      ($parseString) = $parseString =~ /\"(.+)\"/;
      $parseString =~ s/\\([)(}{])/$1/g;
      $parseString =~ s/([|+?])/\\$1/g;
    } 
  }
  unless ($parseString) {
  # if that failed (e.g. database no longer defined) try RULE_13 ">[^ ]* \(.*\)"
    $parseString = ">[^ ]* (.*)"
  }

  my $repString;
  ($repString) = $text =~ /$parseString/s;

# watch out in case parse rule was \(.*\)
  $repString =~ s/^Content-type: text\/plain\s*//s;
  $repString =~ s/\s*$//s;

  return $repString;

}

###############################################################################
# &parseText()
# parse text from souce defined in WWW section of mascot.dat
# returns parsed text on success or error string on failure
# $_[0] is text to be parsed
# $_[1] is the parse rule number
###############################################################################

sub parseText{

  my $inputText = shift;
  my $parseRule = shift; 

  my $parseString;
  if ($parseString = &getConfigParam("PARSE", "RULE_$parseRule")) {
    ($parseString) = $parseString =~ /\"(.+)\"/;
    $parseString =~ s/\\([)(}{])/$1/g;
    $parseString =~ s/([|+?])/\\$1/g;
  } else {
    return "Failed to find parse rule $parseRule in mascot.dat";
  }

  my $repString;
  ($repString) = $inputText =~ /$parseString/s;
  unless ($repString){
    return "No report text found using parse rule $parseRule";  
  }
  
#  $repString =~ s/</&lt;/gs;
#  $repString =~ s/>/&gt;/gs;

  return $repString;

}

###############################################################################
# &parseSequence()
# parse sequence from FASTA entry output of ms-getseq.exe
# returns sequence string on success, 0 on failure
# $_[0] is text to be parsed
# $_[1] is the parse rule number
###############################################################################

sub parseSequence{

  my $inputText = shift;
  my $parseRule = shift; 
  my $seqString;

# drop everything from the second '>' onwards
  if ($inputText =~ />.*>/s) {
    $inputText =~ s/(.*?>.*?)>.*/$1/s;
  }
  my $parseString = &getConfigParam("PARSE", "RULE_" . $parseRule) || return 0;  
  ($parseString) = $parseString =~ /\"(.+)\"/;
  $parseString =~ s/\\([)(}{])/$1/g;
  $parseString =~ s/([|+?])/\\$1/g;
  ($seqString) = $inputText =~ /$parseString/s;
  unless ($seqString){
    return 0;  
  }  
  $seqString =~ s/\*/x/g;         # some NCBI entries have * in place of X
  $seqString =~ s/\@/_/g;         # change gap character to underscore
  $seqString =~ s/[^a-zA-Z_]//g;  # delete non-alpha
  chomp($seqString);
  return $seqString;

}

###############################################################################
# &noTag()
# returns de-tagged string
# $_[0] string which may contain HTML tags
###############################################################################

sub noTag{

  my $temp = shift;

  $temp =~ s/</&lt;/g;
  $temp =~ s/>/&gt;/g;
  $temp =~ s/\"/&quot;/g;

  return $temp;

}

###############################################################################
# &urlEscape()
# returns escaped string, with spaces converted to +, other characters to %xx
# $_[0] string to be escaped
###############################################################################

sub urlEscape{

  my $temp = shift;

  $temp =~ s/(\W)/sprintf("%%%02x", ord($1))/eg;
  $temp =~ s/%20/\+/g;

  return $temp;

}

###############################################################################
# &getExtInfo()
# get information from a source defined in the WWW section of mascot.dat
# returns three element array:
#    1 for success or 0 for failure
#    returned text or an error message
#    parse rule number or 0
# $_[0] is an accession string
# $_[1] is frame number
# $_[2] is the database name
# $_[3] is the item required. One of: report, sequence, title, length, pI
###############################################################################

sub getExtInfo {

  my $accession = shift;
  my $frame = shift;
  my $db = shift;
  my $dataItem = shift;
  
  my $label = $db;
  if ($dataItem eq "report") {
    $label .= "_REP";
  } else {
    $label .= "_SEQ";
  }
  
  my $tempString;
  if ($tempString = &getConfigParam("WWW", $label)) {
  } else {
    return (0, "Unable to find $label in mascot.dat", 0);
  } 
  $tempString =~ s/$label\s+//;  
  my($parseRule, $host, $service, $file) = ($tempString =~ /\"(.+?)\"/g);
  if ($dataItem ne "report") {
    if ($file !~ /ms-getseq\.exe/i || $host !~ /localhost/i) {
      return (0, "Cannot retrieve $dataItem from $file on $host", 0);
    }
  }
  if ($host eq "localhost"){
  # give up if database not active
    unless (&getConfigParam("Databases", $db)) {
      return (0, "Database $db is not active", 0);
    }
    if ($accession !~ /^\"/) {
      $accession = "\"$accession\"";
    }
  } else {
    $accession =~ s/^\"(.*)\"$/$1/;
    $accession =~ s/(\W)/sprintf("%%%02x", ord($1))/eg;
  }
  
  if ($dataItem eq "report") {
  # do nuffink
  } elsif ($dataItem eq "sequence") {
  # do nuffink
  } elsif ($dataItem eq "title") {
    $file =~ s/#ACCESSION#\s+seq/#ACCESSION# title/i;
  } elsif ($dataItem eq "length") {
    $file =~ s/#ACCESSION#\s+seq/#ACCESSION# len/i;
  } elsif ($dataItem eq "pI") {
    $file =~ s/#ACCESSION#\s+seq/#ACCESSION# pI/i;
  } else{
    return (0, "Unrecognised keyword: $dataItem", 0);
  }
  $file =~ s/#ACCESSION#/$accession/;
  $file =~ s/#FRAME#/$frame/;
  
  my $output = &getReport($host,$service,$file);
  if ($output) {
    $output =~ s/\s*$//;    # delete trailing white space
    return (1, $output, $parseRule);
  } else {
    return (0, "Failed to retrieve $dataItem from $file on $host", 0);
  }
 
}

###############################################################################
# &simpleFooter()
# output old style copyright footer
###############################################################################

sub simpleFooter{

  print <<'end_of_static_HTML_text_block';

<P><TABLE WIDTH="100%" BORDER="2" CELLSPACING="2" CELLPADDING="1">
<TR><TD ALIGN="CENTER" NOWRAP><B>Mascot:</B>&nbsp;
<A HREF="http://www.matrixscience.com/">http://www.matrixscience.com/</A>
</TD></TR>
</TABLE>

end_of_static_HTML_text_block
  
}

###############################################################################
# &go2login()
# Mascot security: display login form
# $_[0] referer
# $_[1] error
# $_[2] errorstring
###############################################################################

sub go2login{

  print "<SCRIPT LANGUAGE=\"JavaScript\">\n";
  print "<!-- Begin hiding Javascript from old browsers.\n";
  print "if(window.navigator.userAgent.indexOf(\"MSIE\") != -1){\n";
  print "  window.location.replace(\"../cgi/login.pl?referer=$_[0]&error=$_[1]&errorstring=$_[2]\");\n";
  print "} else if (window.location.replace == null){\n";
  print "  window.location.assign(\"../cgi/login.pl?referer=$_[0]&error=$_[1]&errorstring=$_[2]\");\n";
  print "} else {\n";
  print "  window.location.replace(\"../cgi/login.pl?referer=$_[0]&error=$_[1]&errorstring=$_[2]\");\n";
  print "}\n";
  print "\n";
  print "\n";
  print "// End hiding Javascript from old browsers. -->\n";
  print "</SCRIPT>\n";
  print "<NOSCRIPT>\n";
  print "<A HREF=\"../cgi/login.pl?referer=$_[0]&error=$_[1]&errorstring=$_[2]\">Click here if you are not redirected automatically</A>";
  print "</NOSCRIPT>\n";

}

###############################################################################
# &checkErrorHandler()
# Display Mascot Parser errors
# $_[0] object that failed isValid() test
# returns concatenated string of error messages
###############################################################################

sub checkErrorHandler {
  
  my $obj = shift;

  my $errorList = "";
  my $err = $obj->getErrorHandler();  
  
  for (my $i = 1; $i <= $err->getNumberOfErrors(); $i++) {
    $errorList .= "Error number: "
      . $err->getErrorNumber($i)
      . " ("
      . $err->getErrorRepeats($i)+1 . " times) : "
      . $err->getErrorString($i) . "<BR>\n";
  }
  
  if ($errorList) {
    $errorList = "Mascot Parser error(s):<BR>\n" . $errorList;
  }

  $obj->clearAllErrors();

  return $errorList;
  
}

