<?php
session_start();
$vcodes = '';
header("Content-type: image/png");
$im = imagecreate(44,18);
$back = imagecolorallocate($im, 200,220,230);
imagefill($im,0,0,$back); 
srand((double)microtime()*1000000);
for($i=0;$i<4;$i++){
  $font = imagecolorallocate($im, rand(100,255),rand(0,100),rand(100,255));
  $authnum=rand(1,9);
  $vcodes.=$authnum;
  imagestring($im, 5, 2+$i*10, 1, $authnum, $font);
}
for($i=0;$i<100;$i++){ 
  $randcolor = imagecolorallocate($im,rand(100,255),rand(0,255),rand(0,255));
  imagesetpixel($im, rand()%70 , rand()%30 , $randcolor);
}
imagepng($im);
imagedestroy($im);
$_SESSION['VCODE'] = $vcodes;
?>