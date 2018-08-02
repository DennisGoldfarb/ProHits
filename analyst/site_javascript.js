// ***************************************************************************
// Cookie name is 'ProhitsLogin'. 
// It is not used to check login username nor password.
// It is only used to protect that user forgot to logout or other user using 
// history to view records.
// The cookie timeout is half day
// The cookie will be set in index.php after user login
// ***************************************************************************
var newPop = '';
var secondPop = '';
function popwin(theFile,w,h,w_name){
  if(typeof(w_name) == 'undefined'){ 
    if (!newPop.closed && newPop.location) {
      newPop.close();
    }
    newPop = window.open(theFile,"parawind",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + w + ',height=' + h);  
    newPop.focus();
  }else{
    if (!secondPop.closed && secondPop.location) {
      secondPop.close();
    }
    secondPop = window.open(theFile,"_blank",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + w + ',height=' + h);  
    secondPop.focus();
  }  
}
/*function popwin(theFile,w,h){
  if (!newPop.closed && newPop.location) {
    newPop.close();
  }
  newPop = window.open(theFile,"parawind",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + w + ',height=' + h);  
  newPop.focus();
}*/
var isNav, isIE;
if(parseInt(navigator.appVersion) >= 4){
  if(navigator.appName == "Netscape"){
    isNav = true;
  }else{
    isIE = true;
  }
}
function href_show_hand(){
 return;
}
function showTip(evt,tipDiv){
	var obj = document.getElementById(tipDiv);
  //alert(tipW+'---'+tipH);
  var xl = 10;
  var yl = 20;
  if(isNav){
    obj.style.left = evt.pageX + xl + "px";
  	obj.style.top = evt.pageY + yl + "px";
  }else{
    obj.style.left = window.event.clientX  + document.body.scrollLeft + xl + "px";
  	obj.style.top = window.event.clientY + document.body.scrollTop+ yl + "px";
  }
  obj.style.display="block";
  obj.style.position="absolute";
  obj.style.border="black solid 1px";
  obj.style.backgroundColor  = "white";
}

function hideTip(tipDiv){
	var obj = document.getElementById(tipDiv);
  obj.style.display="none";
  obj.style.position="";
  obj.style.border="";
  obj.style.backgroundColor="";
}
function trimString(str) {
    var str = this != window? this : str;
    return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function search_check(theForm){
  var str = theForm.searchThis.value;
  str = trimString(str);
  if(str == '-' || str == ''){
    alert('What do you want to search for?'); 
    return false;
  }
  if(theForm.ListType.value == 'Band' && /^[A-H]?0?\d{1,2}$/i.test(str)){
    alert("enter Sample name(except A01-H16), Gene Name, Lane Code or Gel Name for search");
    return false;
  }
  for(var position=0; position<str.length; position++){
    var chr = str.charAt(position);
    if (chr != "." && chr !=" "){
      return true;
    }
  }
}
function popSearchHelp(){
 file = 'pop_search_help.html';
 window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=300');
}
function add_notes(Hit_ID){
  file = 'pop_hit_note.php?Hit_ID=' + Hit_ID;
  popwin(file,800,600);
}
function view_peptides(Hit_ID,second_pop){  
  file = 'peptideInfo.php?Hit_ID=' + Hit_ID;
  if(typeof(second_pop) == 'undefined'){
    popwin(file,580,300);
  }else{
    popwin(file,580,300,second_pop);
  }  
}
function view_peptides_tpp(TppProtein_ID,second_pop){  
  file = 'peptideInfo_tpp.php?TppProtein_ID=' + TppProtein_ID;
  if(typeof(second_pop) == 'undefined'){
    popwin(file,580,300);
  }else{
    popwin(file,580,300,second_pop);
  }  
}
function view_gel(Band_ID){  
   file = 'gel_view.php?Band_ID=' + Band_ID;
   popwin(file,580,500,'view');
}
function view_image(Gel_ID) {  
  file = 'gel_view.php?Gel_ID=' + Gel_ID;
  popwin(file,580,500);
}
if(GetCookie('ProhitsLogin') == null) {
//	location.href="index.php";
}
function getCookieVal (offset) {
	var endstr = document.cookie.indexOf (";", offset);
	if (endstr == -1)
	endstr = document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}
function GetCookie (name)  {
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen)  {
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg)	return getCookieVal (j);i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0) break; 
	}
	return null;
}
function PreloadImages(ImagesStr){
  var ImageNames = ImagesStr.split(",");
  var Images = new Array(ImageNames.length); 
  for (var i = 0; i < ImageNames.length; i++){
    Images[i] = new Image();
    Images[i].src = "./images/" + ImageNames[i];
  }
}
function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
function highlightTR(theRow,  theAction,  highlightColor, defaultColor, extra)
{
    if ((highlightColor == '' && defaultColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    if (typeof(window.opera) == 'undefined' ) {
        currentColor = theRow.getAttribute('bgcolor');
        domDetect    = true;
    }else {
        currentColor = theRow.style.backgroundColor;
        domDetect    = false;
    }
    if(currentColor.indexOf("rgb") >= 0)  {
        var rgbStr = currentColor.slice(currentColor.indexOf('(') + 1,currentColor.indexOf(')'));
        var rgbValues = rgbStr.split(",");
        currentColor = "#";
        var hexChars = "0123456789ABCDEF";
        for (var i = 0; i < 3; i++)
        {
            var v = rgbValues[i].valueOf();
            currentColor += hexChars.charAt(v/16) + hexChars.charAt(v%16);
        }
    }
    if (currentColor.toLowerCase() == highlightColor.toLowerCase() && theAction == 'click' ) {
       newColor = defaultColor;
    } else {
       newColor = highlightColor;
    }
    if (newColor) {
        if (domDetect) {
                theRow.setAttribute('bgcolor', newColor, 0);
        }
        else {
                theRow.style.backgroundColor = newColor;
        }
    }
    
    if(typeof(extra) != 'undefined'){
      var parentItem = document.getElementById(theRow.id);
      var childID_gene = 'gene'+parentItem.id;
      var childItem_gene = document.getElementById(childID_gene);    
      var childID_protein = 'protein'+parentItem.id;
      var childItem_protein = document.getElementById(childID_protein);
      var childID_link = 'link'+parentItem.id;
      var childItem_link = document.getElementById(childID_link);        
      var childID_shared = 'shared'+parentItem.id;
      var childItem_shared = document.getElementById(childID_shared);
      if(newColor == highlightColor){
        childItem_gene.className = childItem_link.className = "s15_1"; 
        if(extra != 'shared'){
          childItem_protein.className = childItem_shared.className = "s15_1";
        }
      }else{
        childItem_gene.className = "s16";
        childItem_protein.className = "s17";
        childItem_link.className = "s22";
        childItem_shared.className = "s17";
      }
    }    
    return true;
}
function is_numeric(input){
  if(!(/^\d*?.?\d*$/i.test(trim(input)))){
    return false;
  }else{
    return true;
  }
}
function pop_filter_set(filter_ID){   
  file = 'mng_set.php?filterID=' + filter_ID;
  window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=500,height=620');
}
 // end of the 'highlightTR()' function
/////////////////////////////////////////////////////////////////////////////
