function DropDown(theDiv){
	var wWidth = $(document).width();
	var dWidth = theDiv.width();
	var oLeft = (wWidth-dWidth)/2;
	theDiv.css("left", oLeft);
 
	theDiv.slideDown(700);
}