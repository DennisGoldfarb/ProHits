/**********************************

***********************************/
$(document).ready(function() {
	 $( document ).tooltip({
      content:function(){
        var my_str =  $(this).prop('title');
        var rt = '';
        if(my_str){
          if(my_str.indexOf(';;') !== -1){
            var tipParts = my_str.split(';;');
            rt = '<div id="tooltip-title">'+tipParts[0]+'</div><div id="tooltip-body">'+tipParts[1]+'</div>';
          }else{
            rt = '<div id="tooltip-body">'+my_str+'</div>';
          }
        }
        return rt;
      }
    });
});