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

$total = 0;
$spliter = ",,";

if( getenv("HTTP_CLIENT_IP")){
 $ip = getenv('HTTP_CLIENT_IP');
}else if ( getenv("HTTP_X_FORWARDED_FOR")) {
 $ip = getenv('HTTP_X_FORWARDED_FOR');
}else{
 $ip = getenv('REMOTE_ADDR');
}
$handle = fopen("../TMP/comparison/$ip.txt", "r");

////////////////////////////////////////
// layout parameters 
////////////////////////////////////////
//set cell size
$cellWidth   = 25;
$cellHeight  = 14;
$geneGiWidth = 86;
$hitWidth    = 10;
$startY      = 70;

$line_num = 0;
$gi_started = 0;
$cellsHarr = array();
$gi_lines = array();

$totalCellH = 0;
if($handle){
  while(!feof($handle)){
    $buffer = fgets($handle, 4096);
    $buffer = trim($buffer);
    if(preg_match('/^Total samples:(\d*)/', $buffer, $matches)){
      $total_samples = $matches[1];
    }elseif(preg_match('/^Total GIs:(\d*)/', $buffer, $matches)){
      $total_GIs = $matches[1];
    }elseif(preg_match('/^Sample Names:(.*)/', $buffer, $matches)){
      $sample_names = explode($spliter, $matches[1]);
    }elseif(preg_match('/^Group Names:(.*)/', $buffer, $matches)){
      $group_names = explode($spliter, $matches[1]);  
    }elseif(preg_match('/^Background:(.*)/', $buffer, $matches)){
      $backgrounds = explode($spliter, $matches[1]);
    }elseif(preg_match('/^titals:(.*)/', $buffer, $matches)){
      $baitArr = explode('##', $matches[1]);
    }elseif(preg_match('/^GI list:(.*)/', $buffer, $matches)){
      $gi_started = 1;
    }elseif($gi_started == 1 && $buffer){
      preg_match('/^(.+),,(\d+)/', $buffer, $matches);
      $gi_lines[$line_num] = $matches[1];
      array_push($cellsHarr, $matches[2] * ($cellHeight) + 2);
      $totalCellH += $matches[2] * ($cellHeight) + 2;
      $line_num++;
    }
  }
  //$totalCellH = $totalCellH + ($line_num -1) * 2;
  fclose($handle);
}else{
  exit;
}

$font3Width = imagefontwidth(3);
$font2Width = imagefontwidth(2);
$font2Heighth = imagefontheight(2);
$maxLen_C = 0;
foreach($sample_names as $value){
  if(strlen($value) > $maxLen_C){
    $maxLen_C = strlen($value);
  }
}
$maxLen = $maxLen_C * $font3Width + 4;
$wellLabelHeight = $cellHeight + 5;
$wellLabelFlag = 0;
if($maxLen > $cellWidth){
  $wellLabelHeight = $maxLen;
  $wellLabelFlag = 1;
}
//-----!!!!!!change labelsHeight!!!!!!-----------------------------------------------------------------------------------
if(isset($group_names)){
  $labelsHeight = (3 * $cellHeight + 11) + $wellLabelHeight + 5 + $cellHeight;
}else{
  $labelsHeight = (3 * $cellHeight + 11) + $wellLabelHeight;
}  

$overallWidth = 0;
//$overallWidth = ($cellWidth + 2) * $total_samples + $geneGiWidth * 2;        // width in pixels
$overallHeight = $totalCellH + $startY + $labelsHeight + 10;       // height in pixels


//create $baitsInfoArr, $expsInfoArr and $cellsWarr.
$baitsInfoArr = array();
$expsInfoArr = array();
$cellsWarr = array();
for($i=0; $i<count($baitArr); $i++){
  $bait_expArr = explode('%%', $baitArr[$i]);
  if(preg_match('/^(.+\))(\d+)$/', $bait_expArr[0], $matches)){
    if(!$matches[2]) continue;
    $tmpArr1 = explode('(' , $matches[1]);
    $tmpArr1[0] = str_replace(" ", "", $tmpArr1[0]);
    $tmpArr1[1] = str_replace(")", "", $tmpArr1[1]);
    if(strlen($tmpArr1[0]) > strlen($tmpArr1[1])){
      $baitLabelLen = strlen($tmpArr1[0]) * $font3Width;
    }else{
      $baitLabelLen = strlen($tmpArr1[1]) * $font3Width;
    }
    $tmpArr2[0] = strlen($tmpArr1[0]) * $font3Width;
    $tmpArr2[1] = strlen($tmpArr1[1]) * $font3Width;
    $expsArr = explode(";;", $bait_expArr[1]);
    $currentbaitInfoArr = array();
    $baitRowLen = 0;
    foreach($expsArr as $value){
      $expLabelLen = 0;
      $tmpArr = explode(",,", $value); //tmpArr hold exp name and # bands--
      if($tmpArr[1] == 0) continue;
      $expLabelLen = strlen($tmpArr[0]) * $font3Width;
      $expRowLen = ($cellWidth + 2) * $tmpArr[1] - 2;
      if($expLabelLen > $expRowLen){
        $currentCellW = round((($expLabelLen - $expRowLen) / $tmpArr[1]), 0) + $cellWidth;
        $currentExpW = ($currentCellW + 2) * $tmpArr[1] - 2;
      }else{
        $currentCellW = $cellWidth;
        $currentExpW = ($cellWidth + 2) * $tmpArr[1] - 2;
      }
      $currentExpInfoArr = array("explableLen" => strlen($tmpArr[0])*$font3Width, "expLable" => $tmpArr[0], "cellCounter" => $tmpArr[1], "cellW" => $currentCellW);
      array_push($currentbaitInfoArr, $currentExpInfoArr);
      if($baitRowLen) $baitRowLen += 2;
      $baitRowLen += $currentExpW;
    }
    //echo $baitRowLen."<br>";
    if($baitRowLen < $baitLabelLen){
      $cellextraW = round((($baitLabelLen - $baitRowLen) / $matches[2]), 0);
      for($x=0; $x < count($currentbaitInfoArr); $x++){
        $currentbaitInfoArr[$x]['cellW'] += $cellextraW;
      }
    }
    $baitW = 0;
    foreach($currentbaitInfoArr as $tmpValue){
      for($y=0; $y<$tmpValue['cellCounter']; $y++){
        array_push($cellsWarr, $tmpValue['cellW']);
        if($baitW) $baitW += 2;
        $baitW += $tmpValue['cellW'];
      }
      array_push($expsInfoArr, $tmpValue);
    }
    //$baitW = ($tmpValue['cellW'] + 2) * $matches[2] - 2;
    array_push($baitsInfoArr, array("baitW" => $baitW, "baitLabelLen" => $tmpArr2, "baitLabel" => $tmpArr1));
    if($overallWidth) $overallWidth += 2;
    $overallWidth += $baitW;
  }
}
$overallWidth += ($geneGiWidth + 2) * 2;
array_push($cellsWarr, $geneGiWidth, $geneGiWidth);

header("Content-type: image/png");   
$im = Imagecreate($overallWidth,$overallHeight) or die("Cannot Initialize new GD image stream");
// allocate some browser safe colours
$white = ImageColorAllocate($im,255,255,255);
$black = ImageColorAllocate($im,0,0,0);       
$red = ImageColorAllocate($im,255,85,75);      
$green = ImageColorAllocate($im,57,255,35);
$blue = ImageColorAllocate($im,72,141,255);
$bglight = ImageColorAllocate($im,219,221,221);
$bgdark = ImageColorAllocate($im,196,200,200);
$bgyellow = ImageColorAllocate($im,248,234,184);

// make the background transparent and interlaced
imagecolortransparent($im, $white);
imageinterlace ($im,1);

$hitsH = 0;

$thisX1 = 0;
$thisX2 = 0;
$thisY1 = $startY;
$thisY2 = ($thisY1 + 1 + $cellHeight);
$hitsH += $thisY2;
for($i=0; $i<count($baitsInfoArr); $i++){
  if($i % 2){
    $bgColor = $bgdark;
  }else{
    $bgColor = $bglight;
  }
  if($i == 0){
    $thisX1 = 0;
  }else{
    $thisX1 = ($thisX2 + 2);
  }  
  $thisX2 = ($thisX1 + $baitsInfoArr[$i]['baitW']);
  imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
  imagestring($im, 3, round($thisX1 + ($baitsInfoArr[$i]['baitW'] - $baitsInfoArr[$i]['baitLabelLen'][0])/2), $thisY1+2, $baitsInfoArr[$i]['baitLabel'][0], $black);
}

$thisX1 = 0;
$thisX2 = 0;
$thisY1 = $thisY2;
$thisY2 = ($thisY1 + 5 + $cellHeight);
$hitsH += $thisY2; 
for($i=0; $i<count($baitsInfoArr); $i++){
  if($i % 2){
    $bgColor = $bgdark;
  }else{
    $bgColor = $bglight;
  }
  if($i == 0){
    $thisX1 = 0;
  }else{
    $thisX1 = ($thisX2 + 2);
  }  
  $thisX2 = ($thisX1 + $baitsInfoArr[$i]['baitW']);
  imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
  imagestring($im, 3, round($thisX1 + ($baitsInfoArr[$i]['baitW'] - $baitsInfoArr[$i]['baitLabelLen'][1])/2), $thisY1+2, $baitsInfoArr[$i]['baitLabel'][1], $black);
}

$thisX1 = 0;
$thisX2 = 0;
$thisY1 = $thisY2 + 2;
$thisY2 = ($thisY1 + 5 + $cellHeight);
$hitsH += $thisY2;
for($i=0; $i<count($expsInfoArr); $i++){
  if($i == 0){
    $thisX1 = 0;
  }else{
    $thisX1 = ($thisX2 + 2);
  }
  $expW = ($expsInfoArr[$i]['cellW'] + 2) * $expsInfoArr[$i]['cellCounter'] - 2;  
  $thisX2 = ($thisX1 + $expW);
  if($i % 2){
    $bgColor = $bgdark;
  }else{
    $bgColor = $bglight;
  }
  imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
  imagestring($im, 3, round($thisX1 + ($expW -$expsInfoArr[$i]['explableLen'])/2), $thisY1+2, $expsInfoArr[$i]['expLable'], $black);
}


$thisX1 = ($thisX2 + 2);
$thisX2 = $thisX1 + $geneGiWidth * 2 + 2;
$thisY1 = $startY;
imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
imagestring($im, 3, round($thisX1 + 65) , $thisY1+16, "Hits", $black);

//$maxLen ---band label max W.
$strLen = $font3Width * $maxLen;
$thisX1 = 0;
$thisX2 = 0;
$thisY1 = $thisY2 + 2;
$thisY2 = ($thisY1 + $wellLabelHeight);

for($i = 0; $i<count($sample_names); $i++){
  if($sample_names[$i]){
    if($line_num){
      if(strstr($backgrounds[$i], 'light')){
        $bgColor = $bglight;
      }elseif(strstr($backgrounds[$i], 'dark')){
        $bgColor = $bgdark;
      }
    }else{
      if($i % 2){
        $bgColor = $bgdark;
      }else{
        $bgColor = $bglight;
      }
    }  
    if($i == 0){
      $thisX1 = 0;
    }else{
      $thisX1 = ($thisX2 + 2);
    }
    $thisX2 = ($thisX1+ $cellsWarr[$i]);
    
    $tmpStrLen = strlen($sample_names[$i]) * $font3Width;
    imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
    if(!$wellLabelFlag){  
      imagestring($im, 3, round($thisX1+($cellsWarr[$i]-$tmpStrLen)/2) , $thisY1+2, $sample_names[$i], $black);
    }else{
      imagestringup($im, 3, round($thisX1+(($cellsWarr[$i]-imagefontheight(3))/2)), $thisY2-2, $sample_names[$i], $black);
    }  
  }  
}

$cabelArr = array('Gene Name','Protein ID');
foreach($cabelArr as $value){
  $thisX1 = ($thisX2 + 2);
  $thisX2 = ($thisX1+ $geneGiWidth);
  $tmpStrLen = strlen($value) * $font3Width;
  imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
  if(!$wellLabelFlag){
    imagestring($im, 3, round($thisX1+($geneGiWidth-$tmpStrLen)/2) , $thisY1+2, $value, $black);
  }else{
    imagestring($im, 3, round($thisX1+($geneGiWidth-$tmpStrLen)/2) , $thisY1+($wellLabelHeight-imagefontheight(3))/2, $value, $black);
  }  
}
//------------------------------------------------------------
//$maxLen ---band label max W.
if(isset($group_names)){
  $strLen = $font3Width * $maxLen;
  //---------------------------------------
  $wellLabelFlag = 0;
  //------------------------------------------
  $thisX1 = 0;
  $thisX2 = 0;
  $thisY1 = $thisY2 + 2;
  $thisY2 = ($thisY1 + 5 + $cellHeight);
  
  for($i = 0; $i<count($group_names); $i++){
    if($group_names[$i]){
      if($line_num){
        if(strstr($backgrounds[$i], 'light')){
          $bgColor = $bglight;
        }elseif(strstr($backgrounds[$i], 'dark')){
          $bgColor = $bgdark;
        }
      }else{
        if($i % 2){
          $bgColor = $bgdark;
        }else{
          $bgColor = $bglight;
        }
      }  
      if($i == 0){
        $thisX1 = 0;
      }else{
        $thisX1 = ($thisX2 + 2);
      }
      $thisX2 = ($thisX1+ $cellsWarr[$i]);
      
      $tmpStrLen = strlen($group_names[$i]) * $font3Width;
      imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
      if(!$wellLabelFlag){  
        imagestring($im, 3, round($thisX1+($cellsWarr[$i]-$tmpStrLen)/2) , $thisY1+2, $group_names[$i], $black);
      }else{
        imagestringup($im, 3, round($thisX1+(($cellsWarr[$i]-imagefontheight(3))/3)), $thisY2-2, $group_names[$i], $black);
      }  
    }  
  }
}  

$cabelArr = array('','');
foreach($cabelArr as $value){
  $thisX1 = ($thisX2 + 2);
  $thisX2 = ($thisX1+ $geneGiWidth);
  $tmpStrLen = strlen($value) * $font3Width;
  imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
  if(!$wellLabelFlag){
    imagestring($im, 3, round($thisX1+($geneGiWidth-$tmpStrLen)/2) , $thisY1+2, $value, $black);
  }else{
    imagestring($im, 3, round($thisX1+($geneGiWidth-$tmpStrLen)/2) , $thisY1+($wellLabelHeight-imagefontheight(3))/2, $value, $black);
  }  
}

//-----------------------------------------------------------------
//echo count($gi_lines);

for($i=0; $i<count($gi_lines); $i++){
   drawRow($gi_lines[$i], $i);
}

imagepng($im);
imagedestroy($im);
exit;
//---------------------------------------------
// draw a record row
// &drawRow()
// $_[0] record string: blue,0,0,0,0,1,LOC285554,27477962
// record color, hit1, hit2, hit3, hit4, hit5, geneName, GI number
//---------------------------------------------

function drawRow($theLine, $theLine_num){
  global $cellsHarr, $cellsWarr;
  global $red,$green,$blue,$bglight,$bgdark,$giColor;
  global $total_samples,$total_GIs,$sample_names,$backgrounds,$gi_lines;
  global $im,$bgyellow;
  global $thisX1,$thisY1,$thisX2,$thisY2,$tmp_x,$tmp_y,$startY,$cellWidth,$cellHeight,$geneGiWidth,$hitWidth;
  //-----------------------------------------------------------------------------------------------
  $thisY1 = ($thisY2 + 2);
  $thisY2 = ($thisY2 + $cellsHarr[$theLine_num]);
  preg_match('/^(\w+),,(.+)$/', $theLine, $matches);
  
  if($matches[1] == 'red'){
    $giColor = $red;
  }elseif($matches[1] == 'green'){
    $giColor = $green;
  }elseif($matches[1] == 'blue'){
    $giColor = $blue;
  }
  $rowCells = explode(',,', $matches[2]);
  for($k=0; $k<count($rowCells); $k++){     
    if($k == 0){
      $thisX1 = 0;
    }else{
      $thisX1 = ($thisX2 + 2);
    }
    $thisX2 = ($thisX1+ $cellsWarr[$k]);
    if($k == count($rowCells) - 1){
      $bgColor = $giColor;
    }elseif($k == count($rowCells) - 2){
      $bgColor = $bglight;
    }else{
      if($backgrounds[$k] == 'light'){
        $bgColor = $bglight;
      }elseif($backgrounds[$k] == 'dark'){
        $bgColor = $bgdark;
      //}elseif($backgrounds[$k] == 'yellow'){
      }elseif(strstr($backgrounds[$k], 'yellow')){
        $bgColor = $bgyellow;
      }
    }
    drawCell($bgColor,$rowCells[$k],$cellsWarr[$k]);
  }
}

//------------------------------------------------
// draw a table cell
// &drawCell()
// $_[0] bgcolor for the cell
// $_[1] 0 or 1 indecate the cell has hit
//------------------------------------------------

function drawCell($bgColor, $isHit, $cellWidth){
  global $white,$black,$red,$green,$blue,$bglight,$bgdark,$giColor;
  global $im;
  global $thisX1,$thisY1,$thisX2,$thisY2,$cellHeight,$hitWidth;
  global $font2Width,$font2Heighth ;
  imagefilledrectangle($im,$thisX1,$thisY1,$thisX2,$thisY2,$bgColor);
  
  if(preg_match('/^h#(.+)$/', $isHit, $matches)){
    //echo $matches[1]."\n";
    $typesArr = explode("@", $matches[1]); 
    $cellContentArr = array();
    foreach($typesArr as $typevalue){
      $tempArr = explode(':', $typevalue);
      $numPepArr = explode(';', $tempArr[1]);
      foreach($numPepArr as $PepValue){
        array_push($cellContentArr, array($tempArr[0],$PepValue));
      }
    }
    $pepCounter = count($cellContentArr);
    $hitX = $thisX1 + $cellWidth/2 - $hitWidth/2;
    $m_hitX = $thisX1 + $cellWidth/2;
    for($i=0; $i<$pepCounter; $i++){
      $tmpH = $i * $cellHeight;
      $hitY = $thisY1 + $tmpH + $cellHeight/2 - $hitWidth/2;
      $m_hitY = $thisY1 + $tmpH + $cellHeight/2;
      if($cellContentArr[$i][0] == 'g'){
        imagefilledrectangle($im,$hitX, $hitY,$hitX+$hitWidth,$hitY+$hitWidth,$giColor);
      }else{
        $rWidth = $hitWidth + 1;
        imagefilledellipse($im,$m_hitX, $m_hitY, $rWidth,$rWidth,$giColor);
      }
      $lettersW = strlen($cellContentArr[$i][1]) * $font2Width;
      $hW = ($hitWidth - $lettersW)/2;
      $vW = ($hitWidth - $font2Heighth)/2;
      /*if($cellContentArr[$i][1] !== '0'){
        imagestring($im,2,$hitX+$hW,$hitY+$vW,$cellContentArr[$i][1],$black);
      }*/  
    }  
  }elseif($isHit){
    imagestring($im,2,$thisX1+2,$thisY1,$isHit,$black);
  }
}
?>