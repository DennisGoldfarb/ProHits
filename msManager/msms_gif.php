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

/*****************************************************************************
 Create an MS/MS spectrum graphic                                           
*****************************************************************************
 COPYRIGHT NOTICE                                                           
 Copyright 1998-2003 Matrix Science Limited  All Rights Reserved.           
                                                                            
 This program may be used and modified within the licensee's organisation   
 provided that this copyright notice remains intact. Distribution of this   
 program or parts thereof outside the licensee's organisation without the   
 prior written consent of Matrix Science Limited is expressly forbidden.    
******************************************************************************
    $Archive:: /www/cgi/msms_gif.pl                                       $ 
     $Author: johnc $ 
       $Date: 2007/03/10 18:06:56 $ 
   $Revision: 1.30 $ 
 $NoKeywords::                                                            $ 
******************************************************************************

 Preferred calling procedure is to reference a Mascot session file containing
 the input arguments. For debug, the following (case insensitive) URL 
 arguments are also recognised:

 file                   - relative path to result file
 query                  - query number
 width                  - width of gif in pixels
 height                 - height of gif in pixels
 left                   - width of left margin in pixels
 right                  - width of right margin in pixels
 top                    - height of top margin in pixels
 bottom                 - height of bottom margin in pixels
 tick1                  - start value on mass axis
 tick_int               - interval between mass axis tick marks
 range                  - width of mass axis
 matches                - comma separated list of labels for the matched peaks
********************************************************************************/


$count = '';                // index into array of values to be plotted
$displayRange = '';         // width of mass axis
$firstTick = '';            // start value on mass axis
$im = '';   
$matchList = '';            // comma separated list of labels for the matched peaks
$thisScript = '';           // CGI object
$tickInterval = '';         // interval between mass axis tick marks
$error_message = '';        // an error message to be displayed as a picture
$width = 0;     
$height = 0; 
$left = 0;           
$right = 0;     
$top = 0;         
$bottom = 0;
//----array---------
$intensityList = array();        // array of intensity values
$massList = array();             // array of m/z values
//----a
$urlParams = array();            // keys are URL parameter names in lower case; values are names in original case  

require("../common/site_permission.inc.php"); 

// get parameters
if(!($firstTick = $tick1)) $firstTick = 0;
if(!($tickInterval = $tick_int)) $error_message = "Error: no tick_int value";   
if(!($displayRange = $range)) $error_message = "Error: no range value";   
if(!($matchList = $matches)) $matchList = "";
$overallWidth = 550;     
$overallHeight = 300;  
$leftMargin = 20;           
$rightMargin = 20;      
$topMargin = 100;          
$bottomMargin = 10;

$lastTick = $firstTick + $displayRange;

// create a new image
$im = Imagecreate($overallWidth,$overallHeight) or die("Cannot Initialize new image stream");

// allocate some browser safe colours
$white = ImageColorAllocate($im,255,255,255);
$black = ImageColorAllocate($im,0,0,0);       
$red = ImageColorAllocate($im,255,85,75);      
$green = ImageColorAllocate($im,57,255,35);
$blue = ImageColorAllocate($im,72,141,255);

// fonts are gdSmallFont gdLargeFont gdTinyFont gdMediumBoldFont
$gdSmallFont = 2;
$gdMediumBoldFont = 4;

$xSmallFont = imagefontwidth($gdSmallFont);
$ySmallFont = imagefontheight($gdSmallFont);
$xMediumFont = imagefontwidth($gdMediumBoldFont);
$yMediumFont = imagefontheight($gdMediumBoldFont);
// make the background transparent and interlaced
imagecolortransparent($im, $white);
imageinterlace ($im,1);

// Draw a blue frame
imagerectangle($im,0,0,$overallWidth-1,$overallHeight-1,$blue);
$massList = $_SESSION["massList"];
$intensityList = $_SESSION["intensityList"];
// find max intensity in mass range to be plotted
$intMin = 0; // peaks always drawn from x axis  
$intMax = -1;
$count = count($intensityList);
$i = 0;
while($i < $count){
  if($intensityList[$i] && ($intensityList[$i] > $intMax)){
    if($massList[$i] >= $firstTick && $massList[$i] <= $lastTick){
      $intMax = $intensityList[$i];
    }
  }
  $i++; 
}
if($intMax == -1){
// no intensity data
  $intMax = 1;
  for($i=0; $i<count($massList); $i++){
    $intensityList[$i] = 1;
  }
}
  
// calculate scale and offset factors
$xScale = ($overallWidth-$leftMargin-$rightMargin)/$displayRange;
$yScale = ($overallHeight-$topMargin-$bottomMargin-$ySmallFont)/($intMax-$intMin);
$yOffset = $overallHeight-$bottomMargin-$ySmallFont;

// draw peaks
for($i=0; $i<$overallWidth; $i++){
  $pixelMap[$i] = $yOffset;
}
 
$count = count($massList);
$i = 0;
$thisX = 0;
$thisY = 0;
while($i < $count){
  if($massList[$i] >= $firstTick && $massList[$i] <= $lastTick){
    $thisX = ($massList[$i]-$firstTick)*$xScale+$leftMargin;
    $thisY = -$intensityList[$i]*$yScale+$yOffset;
    imageline($im,$thisX,$thisY,$thisX,$yOffset,$red);
    if($pixelMap[$thisX] > $thisY){
      $pixelMap[$thisX] = $thisY;
    }
  }
  $i++;
}

// draw labels (if any)
if($matchList){
  $tmpArr1 = explode(',', $matchList);
  $tmpArr2 = array();
//echo "<br>".count($tmpArr1)."####";exit;
  
  for($i=0; $i<count($tmpArr1); $i+=2){
    $tmpArr2[$tmpArr1[$i]] = $tmpArr1[$i+1];
  }
  $labels = array();
  foreach($tmpArr2 as $key => $value){
    if(array_key_exists($value, $labels)){
      $labels[$value] .= "," . $key; 
    }else{
      $labels[$value] = $key;
    }
  }
// sort by mass
  
  ksort($labels);
  $lastLabelX = -100;
  //$labelMass, $labelText, $labelY, $lastLabelText, $lastLabelY);
  foreach($labels as $labelMass => $labelText){
    if($labelMass >= $firstTick && $labelMass <= $lastTick){
      $thisX = ($labelMass-$firstTick)*$xScale+$leftMargin;
      $thisY = $pixelMap[$thisX];
      for($j=floor($thisX-$ySmallFont/2-0.5); $j<=floor($thisX+$ySmallFont/2+0.5); $j++){
        if($thisY > $pixelMap[$j]){
          $thisY = $pixelMap[$j];
        }
      }
      $labelY = $thisY-10;
      if(($thisX-$lastLabelX) <= ($ySmallFont+1)){
        if(($lastLabelY - (strlen($lastLabelText)*$xSmallFont) - 10) < $labelY &&
          $lastLabelY-(strlen($lastLabelText)+strlen($labelText))*$xSmallFont-10 > 0){
          $labelY = $lastLabelY-strlen($lastLabelText)*$xSmallFont-10;
        }
      }      
      imagestringup($im, $gdSmallFont, $thisX-$ySmallFont/2, $labelY, $labelText, $black);
      imagedashedline($im, $thisX, $pixelMap[$thisX]-4, $thisX, $labelY+4, $green);
      $lastLabelX = $thisX;
      $lastLabelY = $labelY;
      $lastLabelText = $labelText;
    }
  }
}

// draw x axis
// figure length of longest label
$i = 6 * $xSmallFont;
$modVal = 10;
if (($tickInterval*$xScale) > (1.5*$i)){
  $modVal = 1;
}elseif(($tickInterval*$xScale) > (1.5*$i/2)){
  $modVal = 2;    
}elseif(($tickInterval*$xScale) > (1.5*$i/5)){
  $modVal = 5;    
}
imageline($im, $leftMargin, $yOffset, $displayRange*$xScale+$leftMargin, $yOffset, $blue);
$thisY = $overallHeight - $bottomMargin - 0.5 * $ySmallFont;
$thisX = $leftMargin;
$thisMass = $firstTick;
while($thisX <= ($overallWidth-$rightMargin)){
  imageline($im, $thisX, $yOffset, $thisX, $yOffset+0.4*$ySmallFont, $blue);
  //echo "<br>####".$thisMass;
  $temp = $thisMass;
  //$temp = sprintf("%-6.5", $thisMass);
  //echo "<br>####".$temp;
  $temp = preg_replace('/\s+/', '', $temp);
  $i = strlen($temp) * $xSmallFont;
  if(!((($thisMass+1e-6)/$tickInterval)%$modVal)){
    imagestring($im, $gdSmallFont, $thisX-$i/2, $thisY, $temp, $black);
  }     
  $thisMass += $tickInterval;
  $thisX = ($thisMass-$firstTick) * $xScale+$leftMargin;
}
header("Content-type: image/png"); 
imagepng($im);
// Convert the image to GIF or PNG and print it on standard output
 /* $| = 1;   // for GD, as per Roth
  binmode STDOUT;
  local $SIG{__WARN__} = sub {return 1};
  my $imageOutput;
  eval { $imageOutput = $im->gif };
  if ($imageOutput){
    print "Content-type: image/gif\n\n";        
    print $imageOutput; 
  } else {
    print "Content-type: image/png\n\n";
    print $im->png; 
  }
*/
//  exit 0;
  
?>  
