<?php 
$mvName = $_GET['mv'];
if(!$mvName){
	echo "no movie name passed";exit;
}
?>
<center>
<OBJECT CLASSID="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" WIDTH="100%" HEIGHT="100%" CODEBASE="http://www.apple.com/qtactivex/qtplugin.cab">
<PARAM name="SRC" VALUE="<?php echo $mvName;?>">
<PARAM name="AUTOPLAY" VALUE="true">
<PARAM name="CONTROLLER" VALUE="false">
<EMBED SRC="<?php echo $mvName;?>" WIDTH="100%" HEIGHT="100%" AUTOPLAY="true" CONTROLLER="false" PLUGINSPAGE="http://www.apple.com/quicktime/download/">
</EMBED>
</OBJECT>
</center>