/*************************************************
usage: 
function ajax_submit(){
  queryString = getquerystring();
	ajaxPost('<?=$PHP_SELF;?>', queryString);
}
function processAjaxReturn(data){
	document.getElementById("results").innerHTML = data;
}
function getquerystring() {
    var form     = document.forms['f1'];
    var word = form.word.value;
    qstr = 'w=' + escape(word);  // NOTE: no '?' before querystring
    return qstr;
}
**************************************************/

/*function ajaxPost(remote_cgi, queryString){
	var xmlHttp;
  if(window.ActiveXObject){
      xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
  }else if(window.XMLHttpRequest){
      xmlHttp = new XMLHttpRequest();
  } 
	xmlHttp.open("POST", remote_cgi, true);
  xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded;");
  xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4) {
            var rp = xmlHttp.responseText;
						processAjaxReturn(rp);
        }
  }
  xmlHttp.send(queryString);
}*/

function ajaxPost(remote_cgi, queryString){
  $.ajax({
    type: "POST",
    url: remote_cgi,
    data: queryString,
    success: function(data){
      if(data){
        processAjaxReturn(data);
      }
    }
  });
}  
