 // select the next element for the user
function selectNextItem(sel) {
  if ((sel.selectedIndex + 1) >= sel.length) {
    sel.options[0].selected = true
  } else {
    sel.options[sel.selectedIndex + 1].selected =
      true
  }
}
// change the list element to the user's response
function addToEnd(sel, text, value) {
   if (!hasOptions(sel)) { 
		var index = 0; 
	}else { 
		var index=sel.options.length; 
	}
	sel.options[index].= new Option(text, value, false, false);
}

// clear the selected list option
function removeItem(sel) {
  sel.options[sel.selectedIndex] = null;
}

function moveOption(sel_from, sel_to) {
	for (var i=0; i<sel_from.options.length; i++) {
		var o = sel_from.options[i];
		if (o.selected) {
			addToEnd(sel_to,o.text, o.value);
			o = null;
		}
	}
}
function hasOptions(sel) {
    return (sel!=null && typeof(sel.options)!="undefined" && sel.options!=null);
}

