/**********************************

***********************************/
$(document).ready(function() {
	 $( document ).tooltip({
      content:function(){
        var my_rel =  $(this).prop('rel');
        //var tipParts = my_rel.split(';;');
        //var tipTitle = tipParts.shift();
        //var rt = '<b>'+tipTitle+'</b><br>'+tipParts[1];
        return my_rel;
      },
      tooltipClass: "my-tooltip",
      items:'[rel]',
    });
});
   

$(document).ready( function( ) {
	$('a.title_head').cluetip({ splitTitle: ';;', width: '280'});
});
$(document).ready( function( ) {
	$('a.sTitle_long').cluetip({cluetipClass: 'no_head', splitTitle: '|', width: '280'});
});
$(document).ready( function( ) {
	$('a.sTitle').cluetip({cluetipClass: 'no_head', splitTitle: '|', width: '120'});
});
$(document).ready( function( ) {
	$('a.tipButton').cluetip({cluetipClass: 'no_head', splitTitle: '|', width: '100'});
});
