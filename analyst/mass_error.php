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
* Create an mass error graph                                                 *
******************************************************************************
 file                   - comma separated list of experimental masses with prefix "massList:"
 hit                    - comma separated list of experimental errors with prefix "errorList:"
 units                  - units to be used for error axis, Da, %, ppm, mmu
 width                  - width of gif in pixels
 height                 - height of gif in pixels
 left                   - width of left margin in pixels
 right                  - width of right margin in pixels
 top                    - height of top margin in pixels
 bottom                 - height of bottom margin in pixels
******************************************************************************/
// 'Global' variables
$file = '';
$hit = '';
$width = '';
$height = '';
$left = '';
$right = '';
$top = '';
$bottom = '';
$xOffset = 0;
//--------
$bottomMargin = '';         // bottom margin height in pixels
$count = '';                // index into array of values to be plotted
$errorMax = '';             // maximum value in list of mass errors
$errorMin = '';             // minimum value in list of mass errors
$fileIn = '';               // relative path to result file = ''; passed as required URL argument
$hitNum = '';               // comma separated list of experimental errors
$im = '';                   // GD image object
$leftMargin = '';           // left margin width in pixels
$massMax = '';              // maximum value in list of masses
$massMin = '';              // minimum value in list of masses
$overallHeight = '';        // height of gif in pixels
$overallWidth = '';         // width of gif in pixels
$rightMargin = '';          // right margin width in pixels
$thisScript = '';           // CGI object
$topMargin = '';            // top margin height in pixels
$units = '';                // units to be used for error axis, Da, %, ppm, mmu
$error_message = '';        // an error message to be displayed as a picture
$errorList_arr = array();   // array of experimental errors
$massList_arr = array();    // array of experimental Mr values
$urlParams = array();       // keys are URL parameter names in lower case, values are names in original case(a)

require("../common/site_permission.inc.php");
// get parameters
if(!($units = $units)) $units = "Da";
if(!($overallWidth = $width)) $overallWidth = 450;
if(!($overallHeight = $height)) $overallHeight = 150;
if(!($leftMargin = $left)) $leftMargin = 20;
if(!($rightMargin = $right)) $rightMargin = 20;
if(!($topMargin = $top))$topMargin = 20;
if(!($bottomMargin = $bottom))$bottomMargin = 20;

$massList_arr = explode(';;',$massList);
$errorList_arr = explode(';;',$errorList);

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
//imagerectangle($im,0,0,$overallWidth-1,$overallHeight-1,$blue);
//--------------------------------------------------------------------------

if($error_message){
  // print error picture
  imagestring($im, $gdSmallFont, $xOffset+20,2*$ySmallFont,$error_message,$red);
}else{
// RMS error in ppm
  $RMS_error = 0;
  if(count($errorList_arr)){
    for($i=0; $i<count($errorList_arr); $i++){
       $RMS_error += pow(($errorList_arr[$i] * 1000000 / $massList_arr[$i]), 2);
    }
    $RMS_error = floor(pow(($RMS_error / count($errorList_arr)),0.5));
  }

// unit conversion
  if($units != "Da") {
    for($i=0; $i<count($errorList_arr); $i++){
      if($units == "%") {
        $errorList_arr[$i] = $errorList_arr[$i] * 100 / $massList_arr[$i];
      }elseif($units == "ppm") {
        $errorList_arr[$i] = $errorList_arr[$i] * 1000000 / $massList_arr[$i];
      }elseif($units == "mmu") {
        $errorList_arr[$i] = $errorList_arr[$i] * 1000;
      }else{
        $units = "Da";
      }
    }
  }

// find max and min error values
  if($count = count($errorList_arr)){
    $errorMin = 99999999;
    $errorMax = -99999999;
    $i = 0;
    while($i < $count){
      if($errorList_arr[$i] > $errorMax){
        $errorMax = $errorList_arr[$i];
      }
      if($errorList_arr[$i] < $errorMin){
        $errorMin = $errorList_arr[$i];
      }
      $i++;
    }
    if($errorMin == $errorMax){
      if($errorMin > 0){
        $errorMin = $errorMin * 0.95;
        $errorMax = $errorMax * 1.05;
      }elseif($errorMin == 0){
        $errorMin = -0.1;
        $errorMax = 0.1;
      }else{
        $errorMin = $errorMin * 1.05;
        $errorMax = $errorMax * 0.95;
      }
    }
  } else {
    $errorMin = -0.1;
    $errorMax = 0.1;
  }
  list($yTickInterval, $yFirstTick, $yDisplayRange) = calcRange($errorMin, $errorMax);

// find max and min mass values
  if($count = count($massList_arr)){
    $massMin = 99999999;
    $massMax = -1;
    $i = 0;
    while($i < $count){
      if($massList_arr[$i] > $massMax){
        $massMax = $massList_arr[$i];
      }
      if($massList_arr[$i] < $massMin){
        $massMin = $massList_arr[$i];
      }
      $i++;
    }
    if($massMin == $massMax){
      if ($massMin == 0) {
        $massMin = 100;
        $massMax = 1000;
      }else{
        $massMin = $massMin * 0.95;
        $massMax = $massMax * 1.05;
      }
    }
  }else{
    $massMin = 100;
    $massMax = 1000;
  }
  list($xTickInterval, $xFirstTick, $xDisplayRange) = calcRange($massMin, $massMax);

// calculate scale and offset factors
  $xScale = ($overallWidth-$leftMargin-$rightMargin-6*$xSmallFont)/$xDisplayRange;
  $xOffset = $leftMargin+6*$xSmallFont;
  $yScale = ($overallHeight-$topMargin-$bottomMargin-$ySmallFont)/$yDisplayRange;
  $yOffset = $overallHeight-$bottomMargin-$ySmallFont;

// Draw a frame
//  $im->rectangle(0,0,$overallWidth-1,$overallHeight-1,$black);

// draw x axis
// figure length of longest label
  $i = 6 * $xSmallFont;
  $modVal = 10;
  if(($xTickInterval*$xScale) > (1.5*$i)){
    $modVal = 1;
  }elseif(($xTickInterval*$xScale) > (1.5*$i/2)){
    $modVal = 2;
  }elseif(($xTickInterval*$xScale) > (1.5*$i/5)){
    $modVal = 5;
  }

  imageline($im, $xOffset, $yOffset, $xDisplayRange*$xScale+$xOffset, $yOffset, $blue);
  $thisY = $overallHeight-$bottomMargin-0.5*$ySmallFont;
  $thisX = $xOffset;
  $thisMass = $xFirstTick;
  while($thisX <= ($overallWidth-$rightMargin)){
    imageline($im, $thisX, $yOffset, $thisX, $yOffset+0.4*$ySmallFont, $blue);
    $temp = $thisMass;
    //$temp = sprintf("%-6.5g", $thisMass);
    $temp = preg_replace('/\s+/', '', $temp);
    $i = strlen($temp) * $xSmallFont;

    if(!((($thisMass+1e-6)/$xTickInterval)%$modVal)){
      imagestring($im, $gdSmallFont, $thisX-$i/2,$thisY,$temp,$black);
    }
    $thisMass += $xTickInterval;
    $thisX = ($thisMass-$xFirstTick)*$xScale+$xOffset;
  }
  imagestring($im, $gdSmallFont, $overallWidth-9*$xSmallFont-$rightMargin,$overallHeight-$ySmallFont,"Mass (Da)",$black);
  imagestring($im, $gdSmallFont, 0,$overallHeight-$ySmallFont,"RMS error $RMS_error ppm",$green);

// draw y axis and ticks
  $i = $ySmallFont;
  $modVal = 10;
  if(($yTickInterval*$yScale) > (1.5*$i)){
    $modVal = 1;
  }elseif(($yTickInterval*$yScale) > (1.5*$i/2)){
    $modVal = 2;
  }elseif(($yTickInterval*$yScale) > (1.5*$i/5)){
    $modVal = 5;
  }
  imageline($im, $xOffset,$yOffset,$xOffset,$topMargin,$blue);
  $thisY = $yOffset;
  $thisX = 0;
  $thisError = $yFirstTick;
  
  //echo $thisError."<br>";
  //echo $yTickInterval."<br>";
  
  $m = 0;
  while($thisY >= $topMargin){
//echo $thisError."<br>";  
    imageline($im, $xOffset-0.5*$xSmallFont,$thisY,$xOffset,$thisY,$blue);
    if(!(floor(abs($thisError/$yTickInterval)+0.5)%$modVal)){
      //if(!$m){
        //$style = array($blue);
      //}else{
        $style = array($blue, $blue, $blue, $blue, $white, $white, $white,$white);
      //}
      imagesetstyle($im, $style);
      imageline($im, $xOffset-$xSmallFont, $thisY ,$xDisplayRange*$xScale+$xOffset, $thisY, IMG_COLOR_STYLED);
      $m++;

      $temp = $thisError;
//echo $temp."<br>";      
      $patterns[0] = '/\.$/';
      $replacements[0] = '';
      $temp = preg_replace($patterns, $replacements, $temp);

      if($temp >= 0){
        if($temp < 1E-8 )$temp = 0;
        imagestring($im, $gdSmallFont, $leftMargin+$xSmallFont, $thisY-0.5*$ySmallFont, "$temp", $black);
      }else{
        if($temp > -1E-8 )$temp = 0;
        imagestring($im, $gdSmallFont, $leftMargin, $thisY-0.5*$ySmallFont, "$temp", $black);
      }
    }
    $thisError += $yTickInterval;
    $thisY = -($thisError-$yFirstTick)*$yScale+$yOffset;
  }
  imagestringup($im, $gdSmallFont, 0,$xSmallFont*11+$topMargin,"Error ($units)",$black);

// draw data points
  for ($i = 0; $i<count($errorList_arr); $i++) {
    $thisY = -($errorList_arr[$i]-$yFirstTick)*$yScale+$yOffset;
    $thisX = ($massList_arr[$i]-$xFirstTick)*$xScale+$xOffset;
    imagefilledrectangle($im, $thisX-1,$thisY-1,$thisX+1,$thisY+1,$red);
  }
}
//------------------------------------------------------------------------------------
// Convert the image to GIF or PNG and print it on standard output

header("Content-type: image/png");
imagepng($im);

/******************************************************************************
 &calcRange()
 $_[0] minimum value to plot
 $_[1] maximum value to plot
 returns 3 element array containing
 tick interval, first tick, and plot range
*******************************************************************************/

function calcRange($massMin, $massMax){
  $minValue = $massMin;
  $maxValue = $massMax;

  $valueRange = $maxValue - $minValue;
  $i = 0;
  while(pow(10,$i) < $valueRange) $i++;
  // drop a power of 10 & find ceiling
  $j = 10;
  $i = pow(10, ($i - 1));
  while($j*$i > $valueRange) $j--;
  // drop another power of 10 & find ceiling
  $j = ($j + 1) * 10;
  $i = $i / 10;
  while ($j*$i > $valueRange) $j--;
  // increase $tickInterval to get between 10 and 25 ticks
  $tickInterval = $i;
  $numTicks = $j + 2;
  if($numTicks > 50) {
    $tickInterval *= 5;
  }elseif($numTicks > 20) {
    $tickInterval *= 2;
  }
  $firstTick = floor($minValue / $tickInterval) * $tickInterval;
  while(($firstTick) > $minValue){
    $firstTick -= $tickInterval;
  }
  $displayRange = floor(($maxValue - $firstTick) / $tickInterval) * $tickInterval;
  while(($firstTick + $displayRange) < $maxValue){
    $displayRange += $tickInterval;
  }
  return array($tickInterval, $firstTick, $displayRange);
}
?>
