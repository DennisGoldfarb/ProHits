<!doctype html public "-//w3c//dtd html 4.0 transitional//en">
<html>
<head>
</head>
<body text="#000000" bgcolor="#FFFFFF" link="#0000FF" vlink="#551A8B" alink="#FF0000">
<?php 
$session_path =  ini_get("session.save_path");
if(!is_writable($session_path)){
  echo "<H2>Please let Prohits administrator know that the directory '$session_path' should be set writable for Apache.";
  exit;
}
?>
<b><font color="#FF0000"><font size=+3>Notice</font></font></b><b><font color="#FF0000"><font size=+0></font></font></b>
<p><b><font color="#FF0000"><font size=+0>Your browser is having difficulties
establishing a <a href="#Cookies">cookie</a>.</font></font></b>
<p><font size=+0>This may be due to one of the following reasons:</font>

<blockquote>
<font size=+0>1.&nbsp;&nbsp;&nbsp; <font color="#FF0000"><a href="#Cookies">Cookies</a>
have been disabled within your browser. </font>Please enable cookies then
click <a href='../'><b>Here</b></a> to login again.</font>
</blockquote>

<font size=+0>To enable your cookie settings, please do the following:</font>
<br>&nbsp;
<table BORDER NOSAVE >
<tr NOSAVE>
<td ALIGN=LEFT NOSAVE><b><font size=+0>For Internet Explorer 5.x Users</font></b></td>

<td ALIGN=LEFT NOSAVE><b>For Internet Explorer 6.x Users</b></td>
</tr>

<tr NOSAVE>
<td   NOSAVE>
<ol>
<li>
On the "Menu Bar", select "Tools" then click "Internet Options"</li>

<li>
From the popup window, select the "Security" tab then click the "Custom
Level" button</li>

<li>
Scroll until you get to the section that says "Allow per-session [not stored]"&nbsp;</li>

<li>
Select "enable" and press "OK"&nbsp;</li>

<li>
When prompted, "Are you sure you want to change the security settings for
this zone?" Click "Yes"</li>
</ol>
</td>

<td ALIGN=LEFT  NOSAVE>
<ol>
<li>
On the "Menu Bar", select "Tools" then click "Internet Options"

<li>
From the popup window, select the "Privacy" tab.</li>

<li>
Within the "Setting" section of the tab, move the slider to <b> "Medium"</b> to select the privacy setting for the Internet zone.
</li>

<br>
<br>
<br>
<br>
<br>


</ol>
</td>
</tr>


<tr NOSAVE>
<td ALIGN=LEFT NOSAVE><b><font size=+0>For Netscape 4.x Users</font></b></td>

<td ALIGN=LEFT NOSAVE><b>For Netscape 6.x Users</b></td>
</tr>

<tr NOSAVE>
<td NOSAVE>
<ol>
<li>
<font size=+0>On the "Menu Bar", select "Edit" then click "Preferences"</font></li>

<li>
<font size=+0>From the popup window, in the left menu selection, click
"Advanced"</font></li>

<li>
<font size=+0>To the bottom right, select the option box that says "Accept
all cookies"</font></li>

<li>
<font size=+0>Click "Ok"</font></li>
</ol>
</td>

<td NOSAVE>
<ol>
<li>
<font size=+0>On the "Menu Bar", select "Edit" then click "Preferences"</font></li>

<li>
<font size=+0>From the popup window, in the left menu selection, click
"Advanced"</font></li>

<li>
<font size=+0>Click "Privacy and Security"
</font></li>

<li>
<font size=+0>Click on "Cookies"</font></li>

<li>
<font size=+0>Click on <b>"Enable cookies for the originating website only"</b></font></li>

<br>
</ol>
</td>
</tr>


</table>

<p><b><font size=+0>Why should <a href="#Cookies">cookies</a> be "enabled"
on your system?</font></b>
<p><a NAME="Cookies"></a>Cookies are small pieces of data that a web server
creates when you visit a web site. This data is typically encoded information
about how and when you use a site. It is placed on your PC in the form
of a small text file. Prohits creates a Session cookie which is used only for the length of time you stay on the web
site. When you leave the site, it expire and is no longer active.</li>
 
</body>
</html>
