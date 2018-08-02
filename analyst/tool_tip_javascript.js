// adjust horizontal and vertical offsets here
// (distance from mouseover event which activates tooltip)
Tooltip.offX = 4;  
Tooltip.offY = 4;
Tooltip.followMouse = false;  // must be turned off for hover-tip

function doTooltippic(e, ar) {
  if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
  Tooltip.clearTimer();
  var cntnt = wrapTipContent(ar);
  var tip = document.getElementById( Tooltip.tipID );
  if ( tip && tip.onmouseout == null ) {
      tip.onmouseout = Tooltip.tipOutCheck;
      tip.onmouseover = Tooltip.clearTimer;
  }
  Tooltip.show(e, cntnt);
}
function doTooltipmsg(e, msg){
  if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
  Tooltip.clearTimer();
  var tip = document.getElementById? document.getElementById(Tooltip.tipID): null;
  if ( tip && tip.onmouseout == null ) {
      tip.onmouseout = Tooltip.tipOutCheck;
      tip.onmouseover = Tooltip.clearTimer;
  }
  Tooltip.show(e, msg);
}
function doTooltippic_classic(e, ar) {
    if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
//    Tooltip.clearTimer();
    var cntnt = wrapTipContent(ar);
    var tip = document.getElementById( Tooltip.tipID );
    if ( ar[2] ) tip.style.width = ar[2] + "px";
    else tip.style.width = Tooltip.defaultWidth + "px";
    if ( tip && tip.onmouseout == null ) {
      tip.onmouseout = Tooltip.tipOutCheck;
      tip.onmouseover = Tooltip.clearTimer;
    }
    Tooltip.show(e, cntnt);
}
function hideTip() {
  if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
  Tooltip.timerId = setTimeout("Tooltip.hide()", 300);
}
//function hideTip() {
//    if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
//    Tooltip.hide();
//}
function wrapTipContent(ar) {
    var cntnt = "";
    if ( ar[0] ) cntnt += '<div align="center" class="img"><img src="' + ar[0] + '" /></div>';
    if ( ar[1] ) cntnt += '<div align="center" class="txt">' + ar[1] + '</div>';
    return cntnt;
}
Tooltip.tipOutCheck = function(e) {
  e = dw_event.DOMit(e);
  // is element moused into contained by tooltip?
  var toEl = e.relatedTarget? e.relatedTarget: e.toElement;
  if ( this != toEl && !contained(toEl, this) ) Tooltip.hide();
}
// returns true of oNode is contained by oCont (container)
function contained(oNode, oCont) {
  if (!oNode) return; // in case alt-tab away while hovering (prevent error)
  while ( oNode = oNode.parentNode ) if ( oNode == oCont ) return true;
  return false;
}
Tooltip.timerId = 0;
Tooltip.clearTimer = function() {
  if (Tooltip.timerId) { clearTimeout(Tooltip.timerId); Tooltip.timerId = 0; }
}
Tooltip.unHookHover = function () {
    var tip = document.getElementById? document.getElementById(Tooltip.tipID): null;
    if (tip) {
        tip.onmouseover = null; 
        tip.onmouseout = null;
        tip = null;
    }
}
dw_event.add(window, "unload", Tooltip.unHookHover, true);
var confirmMsg  = '';
function confirmLink(theLink, theaction)
{


    var is_confirmed = confirm(confirmMsg + ' ' + theaction);
    if (is_confirmed) {
        theLink.href += '&is_js_confirmed=1';
    }

    return is_confirmed;
} // end of the 'confirmLink()' function

