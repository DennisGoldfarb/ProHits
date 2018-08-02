// ** **********************************************************************
// Cookie name is 'ProhitsLogin'.
// It is not used to check login username nor password.
// It is only used to protect that user forgot to logout or other user using 
// history to view records.
// The cookie timeout is half day
// The cookie will be set in index.php after user login
// *************************************************************************
var newPop = '';
var secondPop = '';
function popwin(theFile,w,h,w_name){
  theFile = theFile.replace(/\+/g, '%2B');
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
//alert(tipDiv);
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
  obj.style.border="solid 1px black";
  obj.style.backgroundColor  = "white";
}

function showTip_right(evt,tipDiv){
  var obj = document.getElementById(tipDiv);
  if(isNav){
    var yl = 20;
    var xl = -438;
    obj.style.left = evt.pageX + xl + "px";
    obj.style.top = evt.pageY + yl + "px";
  }else{
    var yl = 10;
    var xl = -420;
    obj.style.left = window.event.clientX  + document.body.scrollLeft + xl + "px";
    obj.style.top = window.event.clientY + document.body.scrollTop+ yl + "px";
  }
  obj.style.display="block";
  obj.style.position="absolute";
  obj.style.border="black solid 1px";
  obj.style.backgroundColor  = "white";
}

function hideTips(tipDiv_head){
  for(var i=0; i<Sample_id_arr.length; i++){
    var Div_id = tipDiv_head + Sample_id_arr[i];
    hideTip(Div_id);
  }
}

function hideTip(tipDiv){
  var obj = document.getElementById(tipDiv);
  obj.style.display="none";
  obj.style.position="";
  obj.style.border="";
  obj.style.backgroundColor="";
}
 
function trimString(str) { 
    return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}
function search_check(theForm){
  if(isEmptyStr(theForm.searchThis.value)){
    alert('Please enter value in Word(s).');
    return false;
  }
  if(!onlyAlphaNumerics(theForm.searchThis.value,5) && !isEmptyStr(theForm.searchThis.value)){
    alert("Only characters A-Z, a-z, 0-9, +, -, _,  and space are valid.");
    return false;
  }
  return true;
}
function popSearchHelp(){
 file = 'pop_search_help.html';
 window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=400,height=300');
}

function add_notes(Hit_ID){
  file = 'pop_hit_note.php?Hit_ID=' + Hit_ID;
  popwin(file,800,600);
}

function add_notes_geneLevel(Hit_ID){
  file = 'pop_hit_note_geneLevel.php?Hit_ID=' + Hit_ID;
  popwin(file,800,600);
}

function add_notes_dev(item_ID,item_type){
  file = 'pop_note.php?item_ID=' + item_ID + '&item_type=' + item_type;
  popwin(file,600,600);
}

function view_peptides(Hit_ID,second_pop){  
  file = 'peptideInfo.php?Hit_ID=' + Hit_ID;
  if(typeof(second_pop) == 'undefined'){
    popwin(file,580,300);
  }else{
    popwin(file,580,300,second_pop);
  }  
}

function view_peptides_geneLevel(Hit_ID,second_pop){  
  file = 'peptideInfo_geneLevel.php?Hit_ID=' + Hit_ID;
  if(typeof(second_pop) == 'undefined'){
    popwin(file,580,300);
  }else{
    popwin(file,580,300,second_pop);
  }  
}

function view_peptides_SEQUEST(Hit_ID,second_pop){  
  file = 'peptideInfo_sequest.php?Hit_ID=' + Hit_ID;
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
//  location.href="index.php";
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
    if (document.cookie.substring(i, j) == arg)  return getCookieVal (j);i = document.cookie.indexOf(" ", i) + 1;
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
function isEmptyStr(str){
  var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
  if(temstr == 0 || temstr == ''){
     return true;
  } else {
    return false;
  }
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
        childItem_gene.className = "s15_1";
        if(childItem_link != null) childItem_link.className = "s15_1"; 
        if(extra != 'shared'){
          childItem_protein.className = childItem_shared.className = "s15_1";
        }
      }else{
        childItem_gene.className = "s16";
        childItem_protein.className = "s17";
        if(childItem_link != null) childItem_link.className = "s22";
        childItem_shared.className = "s17";
      }
    }    
    return true;
}
function is_numeric(mixed_var){
  return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}
function pop_filter_set(filter_ID){   
  file = 'mng_set.php?filterID=' + filter_ID;
  window.open(file,"",'toolbar=1,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=570,height=620');
}


// move options from one box to another
function addToEnd(sel, text, value) {
   if (!hasOptions(sel)) { 
    var index = 0; 
  }else { 
    var index=sel.options.length; 
  }
  sel.options[index] = new Option(text, value, false, false);
}
function removeItem(sel) {
  sel.options[sel.selectedIndex] = null;
}
function moveOption(sel_from, sel_to) {
  for (var i=0; i<sel_from.options.length; i++) {
    var o = sel_from.options[i];
    if (o.selected) {
      addToEnd(sel_to,o.text, o.value);
      sel_from.remove(i);
    }
  }
}
function getOption(sel, theValue){
  for (var i=0; i<sel.options.length; i++) {
    var o = sel.options[i];
    if (o.value == theValue) {
      return o;
    }
  }
  return false;
}
function removeAll(sel_from, sel_to){

  while(sel_from.options.length > 0) {
    if(sel_to){
      var o = sel_from.options[0];
      addToEnd(sel_to,o.text, o.value);
    }
    sel_from.remove(0);
  }
}
function hasOptions(sel) {
    return (sel!=null && typeof(sel.options)!="undefined" && sel.options!=null);
}
function toggle_2_divs(Div1, Div2){
  var obj1 = document.getElementById(Div1);
  if(obj1.style.display == "block"){ 
    showhideDiv(Div2, Div1);
  }else{
    showhideDiv(Div1, Div2);
  }
}

// show and hide a Div
function showhideDiv(ShowDivID, HideDivID) {
  var obj = document.getElementById(ShowDivID);
  if(HideDivID){
    var obj_a = document.getElementById(HideDivID);
    obj_a.style.display = "none";
    obj.style.display = "block";
  }else{
    if(obj.style.display == "none"){
      obj.style.display = "block";
    }else{
      obj.style.display = "none";
    }  
  }
}
function showhide(DivID, lableDivID){
  var obj = document.getElementById(DivID);
  if(lableDivID){
    var obj_a = document.getElementById(lableDivID);
  }
  if(obj.style.display == "none"){
    obj.style.display = "block";
    if(lableDivID){
      obj_a.innerHTML = "[&nbsp;Hide&nbsp;]";
    }
  }else{
    obj.style.display = "none";
    if(lableDivID){
      obj_a.innerHTML = "[&nbsp;Detail&nbsp;]";
    }
  }  
}
function showhideClass(className){
  var obj = document.getElementsByClassName(className);
  for(var i = 0; i < obj.length; i++) {
    if(obj[i].style.display == "none"){
      obj[i].style.display = "block";
    }else{
      obj[i].style.display = "none";
    }
  }
}
// only characters are allowed.

function onlyAlphaNumerics(checkString, num){
  var regExp1 = /^[A-Za-z0-9]$/;
  var regExp2 = /^[_A-Za-z0-9]$/;
  var regExp3 = /^[% +\-_A-Za-z0-9]$/;
  var regExp4 = /^[%+\-_A-Za-z0-9]$/;
  var regExp5 = /^[ +\-_A-Za-z0-9]$/;
  var regExp6 = /^[\(\)_A-Za-z0-9]$/;
  var regExp7 = /^[% +\-_A-Za-z0-9\(\)\.:]$/;
  var regExp8 = /^[\-_A-Za-z0-9,:\|\#]$/;
  var regExp9 = /^[\d]$/;
  var regExp10 = /^[\d\.]$/;
  var regExp11 = /^[% _A-Za-z0-9\-]$/;
  var regExp12 = /^[ ,+\-_A-Za-z0-9]$/;
  var regExp;
  if(num == 1){
    regExp = regExp1;
  }else if(num == 2){
    regExp = regExp2;
  }else if(num == 3){
    regExp = regExp3;
  }else if(num == 4){
    regExp = regExp4;
  }else if(num == 5){
    regExp = regExp5;
  }else if(num == 6){
    regExp = regExp6;
  }else if(num == 7){
    regExp = regExp7;
  }else if(num == 8){
    regExp = regExp8;
  }else if(num == 9){
    regExp = regExp9;
  }else if(num == 10){
    regExp = regExp10;
  }else if(num == 11){
    regExp = regExp11;
  }else if(num == 12){
    regExp = regExp12;
  }    
          
  if(checkString!= null && checkString!= ""){
    for(var i = 0; i < checkString.length; i++){
      if(!checkString.charAt(i).match(regExp)){
        return false;
      }
    }
  } else {
    return false;
  }
  return true;
}

function add_compare(obj, theID, type){
  var sessionAction;
  if(obj.checked){
    sessionAction = 'AddComparision';
  }else{
    sessionAction = 'RemoveComparision';
  }
  ajaxPost('display_format.inc.php', "formatAction=" + sessionAction + "&Type=" + type + "&IDs=" + theID);
}
function status_detail(item_ID,itemType){
  var queryString = "item_ID="+item_ID+"&itemType="+itemType+"&status_detail_show=y";
  var tmp_head = '';
  if(itemType == "Bait"){
    tmp_head = 'B';
  }else if(itemType == "Experiment"){
    tmp_head = 'E';
  }else if(itemType == "Band"){
    tmp_head = 'S';
  }
  item_ID = tmp_head+item_ID;
  var detail_ID = item_ID + "_a";
  var detail_status = document.getElementById(detail_ID);
  if(detail_status.style.display == "block"){
    detail_status.style.display = "none";
  }else{
    if(detail_status.innerHTML == ''){
      ajaxPost("status_fun_inc.php", queryString);
    }  
    detail_status.style.display = "block"; 
  }  
}

function show_hit_detail(event,detail_div_id){
  var obj_1 = document.getElementById(detail_div_id);
  var obj_2 = document.getElementById('hit_detail_td');
  obj_2.innerHTML = obj_1.innerHTML;
  showTip(event,'hit_detail_div');
}

// moveOptionsUp
// move the selected options up one location in the select list
// second argument 0 for comperison.php and 1 for export_bait_to_hits.php
function moveOptionsUp(selectId,flag){
  var selectList = document.getElementById(selectId); 
  var selectOptions = selectList.getElementsByTagName('option');
  if(!is_same_color(selectOptions)){
    alert("please select items with same color");
    return;
  }
  for(var i = 1; i < selectOptions.length; i++){  
    var opt = selectOptions[i];
    if(opt.selected){
//alert(selectOptions[i].text)
    //alert(i);
      if(i <= 1) break;
      if(flag != 1 && opt.value != selectOptions[i-1].value) break; 
      selectList.removeChild(opt);   
      selectList.insertBefore(opt, selectOptions[i - 1]);
    }    
  }
  selectList.focus();
}
// moveOptionsDown
// move the selected options down one location in the select list
// second argument 0 for comperison.php and 1 for export_bait_to_hits.php 
function moveOptionsDown(selectId,flag){ 
  var selectList = document.getElementById(selectId); 
  var selectOptions = selectList.getElementsByTagName('option');
  if(!is_same_color(selectOptions)){
    alert("please select items with same color");
    return;
  }
  if(selectOptions[selectOptions.length - 1].selected) return;
  for(var i = selectOptions.length - 2; i >= 0; i--){
    var opt = selectOptions[i];  
    if(opt.selected){
      if(opt.value == '') break;
      if(flag != 1 && opt.value != selectOptions[i+1].value) break; 
        
      var nextOpt = selectOptions[i + 1];
      opt = selectList.removeChild(opt);   
      nextOpt = selectList.replaceChild(opt, nextOpt);   
      selectList.insertBefore(nextOpt, opt);     
    }    
  }
  selectList.focus();
}

function sort_selected_list(currentType){
  var orderBy = document.getElementById('frm_sel_order_by');
  for(var k=0; k<orderBy.length; k++){
    if(orderBy[k].selected){
      var index = orderBy[k].value;
    }
  }
  var selectList = document.getElementById('frm_selected_list'); 
  var selectOptions = selectList.getElementsByTagName('option');
  var optionNew = document.createElement('option');
  var length = selectOptions.length;
  for(var i=1; i<length; i++){    
    for(var j=i+1; j<length; j++){
      var opt_aa = selectOptions[i];
      var tmp_arr_aa = opt_aa.text.split(" ");
      var aa = tmp_arr_aa[index];
      var opt_bb = selectOptions[j];
      var tmp_arr_bb = opt_bb.text.split(" ");
      var bb = tmp_arr_bb[index];
      if((isNaN(aa) && isNaN(bb) && aa > bb) || parseInt(aa) > parseInt(bb)){
       var yy = selectList.replaceChild(optionNew,selectOptions[i]);
       var xx = selectList.replaceChild(yy,selectOptions[j]);
       var zz = selectList.replaceChild(xx,selectOptions[i]);
      }
    }
  }
}

function is_same_color(selectOptions){  
  var first_val = '';
  var flag = 1;
  for(var i = 1; i < selectOptions.length; i++){
    var opt = selectOptions[i];
    if(opt.selected){
      if(flag == 1){
        first_val = opt.value;
        flag = 0;
      }  
      if(first_val != opt.value) return false;
    } 
  }
  return true;
}
function urlencode(str){
  str = escape(str);
  str = str.replace('+', '%2B');
  str = str.replace('%20', '+');
  str = str.replace('*', '%2A');
  str = str.replace('/', '%2F');
  str = str.replace('@', '%40');
  return str;
}

function urldecode(str) {
  str = str.replace('+', ' ');
  str = unescape(str);
  return str;
}

function toggle_group(the_obj){
  for(var j=0; j<group_item_id_arr.length; j++){
    var tmp_id = the_obj.value + "_" + group_item_id_arr[j];
//alert(tmp_id);return;    
    
    var group_obj = document.getElementById(tmp_id);
    if(group_obj != null){
      if(the_obj.checked == true){
        group_obj.style.display = "block";
      }else{
        group_obj.style.display = "none";
      }
    }
  }  
}

function toggle_group_description(base_id,sign_id){
  var group_obj = document.getElementById(base_id);
  var group_a_id = base_id + '_a';
  var group_a_obj = document.getElementById(group_a_id);
  if(sign_id != undefined){
    var sign_obj = document.getElementById(sign_id);
  }  
  if(group_obj.style.display == "none"){
    group_obj.style.display = "block";
    group_a_obj.innerHTML = '[-]';
    if(sign_id != undefined){
      sign_obj.value = 0;
    }  
  }else{
    group_obj.style.display = "none";
    group_a_obj.innerHTML = '[+]';
    if(sign_id != undefined){
      sign_obj.value = 1;
    }  
  }
}

function change_user(theForm){
  theForm.start_point.value = 0;
  theForm.theaction.value = 'viewall';
  theForm.title_lable.value = '';
  theForm.submit();
}

function get_group_id_list(group_ids){
  if(typeof(group_ids) == 'undefined') return '';
  var group_id_list = '';
  var group_name_arr = [];
   
  if(group_ids[0]){
    for(var i=0; i<group_ids.length; i++){
      if(group_ids[i].checked){
        if(group_id_list != '') group_id_list += ',';
        group_id_list += group_ids[i].value;
        var tmp_arr = group_ids[i].value.split('_');
        if(tmp_arr.length == 3) tmp_arr[0] = tmp_arr[0] + "_" + tmp_arr[1];
        if(!in_array(tmp_arr[0],group_name_arr)){
          group_name_arr.push(tmp_arr[0]);
        }
      }
    }
 }else{
    if(group_ids.checked){
      group_id_list = group_ids.value;   
    }
  }
  return group_id_list;
}

function in_array(item,array){
  var flag = false;
  for(var i=0; i<=array.length-1; i++){
    if(item == array[i]){
      flag = true;
      break;
    }
  }
  return flag;
}

function set_group_id_list(theForm){
  group_ids = theForm.frm_group_id;
  var group_id_list = get_group_id_list(group_ids);
  theForm.frm_group_id_list.value = group_id_list;
  //theForm.theaction.value = "viewall";
}

function pop_Frequency_set(){
  var Fequency_obj = document.getElementById('frm_filter_Fequency');
  var file_id = '';
  for(var i=0; i<Fequency_obj.length; i++){
    if(Fequency_obj[i].selected){
      file_id = Fequency_obj.value;
      break;
    }
  }
  if(!file_id) return;
  var frequency_file_type = document.getElementById('frequency_file_type').value;
  var project_id = document.getElementById('related_project_id').value;
  var f_file_name = '';
  if(file_id == 'Fequency'){
    f_file_name = 'P:P'+project_id+'_'+frequency_file_type+'_frequency.csv';
  }else if(file_id.match(/U:/)){  
    f_file_name = file_id;
  }else{
    f_file_name = 'G:Pro'+project_id+'_Type'+file_id+'.csv';
  }
  popwin('mng_set_frequency.php?frm_frequency_name='+f_file_name, 800, 800);
}

function getRadioCheckedValue(oRadio){
   for(var i = 0; i < oRadio.length; i++)
   {
      if(oRadio[i].checked)
      {
         return oRadio[i].value;
      }
   }
   return '';
}

function all_option_to_str(sel){
  var str = '';
  for (var i=0; i < sel.length; i++) {
   if(str){
    str +=";;";
   }
   if(sel.options[i].value){
    str += sel.options[i].value;
   }
  }
  return str;   
}

function inset_new_option(parent, newOption,nextOption){
	try {
    parent.add(newOption, nextOption); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    parent.add(newOption, nextOption.index); // IE only
  }
}

function append_new_option(parent, newOption){
	try {
    parent.add(newOption, null); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    parent.add(newOption); // IE only
  }
}

function add_option_to_selected(sourceL, selectedL){
  var sourceList = document.getElementById(sourceL);
  var selectedList = document.getElementById(selectedL);
   
	var selectedCounter = 0;
	var currentIndex = 0;
  var is_alerted = false;
  for(var i=sourceList.length-1; i>-1; i--){
    if(sourceList.options[i].selected){
      if(sourceList.options[i].value == '') continue;
			selectedCounter++;
      var optionNew = document.createElement('option');
    	optionNew.id = sourceList.options[i].id;
      optionNew.text = sourceList.options[i].text;
      optionNew.value = sourceList.options[i].value;
			sourceList.remove(i);
  		if(selectedCounter == 1){
  			append_new_option(selectedList, optionNew);
  			currentIndex = selectedList.length-1;
  		}else{
  			inset_new_option(selectedList, optionNew, selectedList.options[currentIndex]);
  		}
		}	
  }
}
/////////////////////////////////////////////////////////////////////////////
