var newPop = '';
function popwin(file,w,h){
  if (!newPop.closed && newPop.location) {
    newPop.close();
  }
  newPop = window.open(file,"parawind",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + w + ',height=' + h);
  //newPop.moveTo(400,10);
  newPop.focus();
}
function newpopwin(file,w,h){
  thePop = window.open(file,"thewin",'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=' + w + ',height=' + h);
  //newPop.moveTo(400,10);
  thePop.focus();
}
function reloadMe(){
  window.location.reload(true);
}

function submitForm(theForm, theActionName, theActionPage){ 
  theForm.myaction.value = theActionName;
  if(!isEmptyStr(theActionPage)){
    theForm.action = theActionPage;
  }
  theForm.submit();
}
function isEmptyStr(str){
  var temstr =  str.replace(/^\s+/g, '').replace(/\s+$/g, '');
  if(temstr == 0 || temstr == ''){
     return true;
  } else {
    return false;
  }
}