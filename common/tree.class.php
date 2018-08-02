<?php 
//(c) Alf Magne Kalleland, http://www.dhtmlgoodies.com - 2005
class dhtmlgoodies_tree{
	var $elementArray = array();
	var $initExpandedNodes = "";
  var $is_radio = '';
	function dhtmlgoodies_tree($ini='', $out_flag='')
	{
		$this->setExpandedNodes($ini);
    $this->is_radio = $out_flag;
	}
	function setExpandedNodes($ini)
	{
		$this->initExpandedNodes = $ini;
	}
	function addToArray($id,$name,$parentID,$imageIcon="", $checked = ""){
		if(empty($parentID))$parentID=0;	
		$this->elementArray[$parentID][] = array($id,$name,$imageIcon, $checked);
	}
	
	function drawSubNode($parentID){
		if(isset($this->elementArray[$parentID])){			
			echo "\n<ul>";
			for($no=0;$no<count($this->elementArray[$parentID]);$no++){
			  $this->drawLi($this->elementArray[$parentID][$no][0], $this->elementArray[$parentID][$no][1], $this->elementArray[$parentID][$no][2], $this->elementArray[$parentID][$no][3]);
				$this->drawSubNode($this->elementArray[$parentID][$no][0]);
				echo "</li>\n";
			}			
			echo "</ul>\n";			
		}		
	}
	function drawTree(){
		echo "\n<div id=\"dhtmlgoodies_tree\">";
		echo "\n<ul id=\"dhtmlgoodies_topNodes\">";
		for($no=0;$no<count($this->elementArray[0]);$no++){
		  $this->drawLi($this->elementArray[0][$no][0], $this->elementArray[0][$no][1], $this->elementArray[0][$no][2], $this->elementArray[0][$no][3]);
			$this->drawSubNode($this->elementArray[0][$no][0]);
			echo "</li>\n";	
		}	
		echo "</ul>\n";	
		echo "</div>\n";	
	}
	function drawLi($id,$name,$imageIcon="", $checked = ""){
		if(!$imageIcon){
			$imageIcon = "<img class=\"folder_openclose\" id=\"openclose".$id."\" src=\"images/folder_close.gif\" alt='$id'>";;
			$tmp_lable = "<a class=\"tree_link\">".$name."</a>";
		}else{
		  $imageIcon = "<img src=\"$imageIcon\" alt=$id>";
      if($this->is_radio){
        $radio_check = 'radio';
      }else{
        $radio_check = 'checkbox';
      }      
			$tmp_lable = "<input name=rawbox type='$radio_check' value=". $id . $checked.">".$name;
		}
		echo "\n<li class=\"tree_node\"><img class=\"tree_plusminus\" id=\"plusMinus".$id."\" src=\"images/plus.gif\">" . $imageIcon . $tmp_lable;	
	}
	function printJavascript(){
?>
<style type="text/css">
a{
text-decoration:none;
font-family:arial;
font-size:13px;
}
</style>
<style type="text/css">
#dhtmlgoodies_tree li{
	list-style-type:none;	
	font-family: arial;
	font-size:13px;
}
#dhtmlgoodies_topNodes{
	margin-left:0px;
	padding-left:0px;
}
#dhtmlgoodies_topNodes ul{
	margin-left:20px;
	padding-left:0px;
	display:none;
}
#dhtmlgoodies_tree .tree_link{
	line-height:13px;
	padding-left:2px;

}
#dhtmlgoodies_tree img{
	padding-top:2px;
}
#dhtmlgoodies_tree a{
	color: #000000;
	text-decoration:none;
}
.activeNodeLink{
	background-color: #316AC5;
	color: #FFFFFF;
	font-weight:bold;
}
</style>	
<script type="text/javascript">
 
var plusNode = 'images/plus.gif';
var minusNode = 'images/minus.gif';
var folderOpen = 'images/folder_open.gif';
var folderClose = 'images/folder_close.gif';
//var nameOfCookie = 'dhtmlgoodies_expanded';
<?php 
echo "var initExpandedNodes =\"".$this->initExpandedNodes."\";\n";
?>	
function expandAll()
{
	var treeObj = document.getElementById('dhtmlgoodies_tree');
	var images = treeObj.getElementsByTagName('IMG');
	for(var no=0;no<images.length;no++){
		if(images[no].className=='tree_plusminus' && images[no].src.indexOf(plusNode)>=0)expandNode(false,images[no]);
	}
}
function collapseAll()
{
	var treeObj = document.getElementById('dhtmlgoodies_tree');
	var images = treeObj.getElementsByTagName('IMG');
	for(var no=0;no<images.length;no++){
		if(images[no].className=='tree_plusminus' && images[no].src.indexOf(minusNode)>=0)expandNode(false,images[no]);
	}
}
function expandNode(e,inputNode)
{
	if(initExpandedNodes.length==0)initExpandedNodes=",";
	if(!inputNode)inputNode = this; 
	if(inputNode.tagName.toLowerCase()!='img')inputNode = inputNode.parentNode.getElementsByTagName('IMG')[0];	
	var inputNodeFolder = inputNode.parentNode.getElementsByTagName('IMG')[1];	
	var inputId = inputNode.id.replace(/[^\d]/g,'');			
	
	var parentUl = inputNode.parentNode;
	var subUl = parentUl.getElementsByTagName('UL');

	if(subUl.length==0)return;
	if(subUl[0].style.display=='' || subUl[0].style.display=='none'){
		subUl[0].style.display = 'block';
		inputNode.src = minusNode;
		if(inputNodeFolder.className=='folder_openclose')inputNodeFolder.src = folderOpen;
		//initExpandedNodes = initExpandedNodes.replace(',' + inputId+',',',');
		//initExpandedNodes = initExpandedNodes + inputId + ',';
		
	}else{
		subUl[0].style.display = '';
		inputNode.src = plusNode;	
		if(inputNodeFolder.className=='folder_openclose')inputNodeFolder.src = folderClose;
		//initExpandedNodes = initExpandedNodes.replace(','+inputId+',',',');			
	}
	//Set_Cookie(nameOfCookie,initExpandedNodes,60);
}
function initTree()
{
	// Assigning mouse events
	var parentNode = document.getElementById('dhtmlgoodies_tree');
	var lis = parentNode.getElementsByTagName('LI'); // Get reference to all the images in the tree
	for(var no=0;no<lis.length;no++){
		var subNodes = lis[no].getElementsByTagName('UL');
		if(subNodes.length>0){
			lis[no].childNodes[0].style.visibility='visible';	
		}else{
      if(typeof lis[no].childNodes[0].style != 'undefined'){
			  lis[no].childNodes[0].style.visibility='hidden';
      }      
			//lis[no].childNodes[0].style.visibility='hidden';
		}
	}	
	
	var images = parentNode.getElementsByTagName('IMG');
	for(var no=0;no<images.length;no++){
		if(images[no].className=='tree_plusminus')images[no].onclick = expandNode;				
	}	

	var aTags = parentNode.getElementsByTagName('A');
	var cursor = 'pointer';
	if(document.all)cursor = 'hand';
	for(var no=0;no<aTags.length;no++){
		aTags[no].onclick = expandNode;		
		aTags[no].style.cursor = cursor;		
	}
	var initExpandedArray = initExpandedNodes.split(',');

	for(var no=0;no<initExpandedArray.length;no++){
		if(document.getElementById('plusMinus' + initExpandedArray[no])){
			var obj = document.getElementById('plusMinus' + initExpandedArray[no]);	
			expandNode(false,obj);
		}
	}				
}

window.onload = initTree;
</script>		
	<?php 
	}
}


?>