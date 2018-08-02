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

require("../common/site_permission.inc.php");

// get parameters
if(!($firstTick = $tick1)) $firstTick = 0;
if(!($tickInterval = $tick_int)) $error_message = "Error: no tick_int value";
if(!($displayRange = $range)) $error_message = "Error: no range value";
//print $matchList;
//$matchList="y(1),175.14,b(2),243.11,b(4);;,243.11,y*(2),321.22,y(2),338.28,b0(3),339.28,b*(3),340.20,y(5);;,340.20,b(3),357.27,y(3),439.36,y(7);;,462.49,b0(4),468.22,b*(4),469.17,b(4),486.28,y0(4),549.34,y*(4),550.38,b(5),599.38,y0(5),662.43,y*(5),663.42,y(5),680.43,b*(6),710.34,b(6),727.38,y(6),809.45,b0(7),810.50,b*(7),811.45,b(7),828.40,y0(7),905.47,y*(7),906.45,y(7),923.47,b*(8),974.40,b(8),991.35,y(8),1052.51";
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
$red = ImageColorAllocate($im,255,0,0);
$green = ImageColorAllocate($im,0,204,0);
$blue = ImageColorAllocate($im,0,0,255);

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

// Read tmp file to get data
$fd = @fopen("$tmpFile","r");
if($fd){
  while (!feof ($fd)) {
    $buffer = trim(fgets($fd, 40960));
    if(preg_match('/^MassList:(.*)$/i', $buffer, $matches)) $massList_tmp = trim($matches[1]);
    if(preg_match('/^IntensityList:(.*)$/i', $buffer, $matches)) $intensityList_tmp = trim($matches[1]);

  }
}
$massList = preg_split("/[\s,]+/",$massList_tmp);
$intensityList = preg_split("/[\s,]+/",$intensityList_tmp);

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
  $matchList = preg_replace("/\;/",'+',$matchList);
  $tmpArr1 = preg_split('/\,/', $matchList);
  $tmpArr2 = array();
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
  //print_r($labels);
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
      //print $labelText;
      imagestringup($im, $gdSmallFont, $thisX-$ySmallFont/2, $labelY, $labelText, $black);
      //print $thisX;
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
  $temp = $thisMass;
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

?>
