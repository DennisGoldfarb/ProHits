#!/usr/bin/perl -wT
use CGI qw(:standard);
  
# print HTTP header (so we can see error messages)
  $thisScript = new CGI;
  print $thisScript->header;
  
  @params = $thisScript->param();
  $i = 0;
  while($params[$i]){
    $name = $params[$i];
    print $name;
    print " = ";
    print $thisScript->param($name);
    print "\n";
    $i++;
  }
  exit;