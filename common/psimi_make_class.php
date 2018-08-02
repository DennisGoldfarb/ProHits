<?php
$xml = new mySimpleXML('frank');
$data1 = $xml->addChild('Liu');
$post = $data1->addChild('Post','', array('id'=>2, 'refTypeAc'=>'frank'));
$id = $post->addChild('id');
$title = $post->addChild('title', 'The Title');
$body = $post->addChild('body', 'This is the post body');
$created = $post->addChild('created', '2008-07-28 12:01:06');

$data1 = $xml->addChild('Liu');
$post = $data1->addChild('Post');
$id = $post->addChild('id');
$id->setData('2');
$title = $post->addChild('title');
$title->setData('A title once again');
$body = $post->addChild('body');
$body->setData('And the post body follows');
$created = $post->addChild('created');
$created->setData('2008-07-28 12:01:07');

header ("content-type: text/xml"); 
echo "<?php xml version=\"1.0\" encoding=\"iso-8859-1\"?>";
print $xml->toXMLString();

class psimi_maker{
  var $_xml_file_Name = '';
  var $_entrySet;
  var $_entry;
  var $_source;
  var $_experimentList = array();
  var $_interactorList = array();
  var $_interactionList = array();
  
  function makeEntrySet(){
  
  }
  function makeSource(){
  
  }function addExperiment(){
    
  }
  function addInteractor(){
  
  }
  function addInteraction(){
  
  }
  function writeXML_file(){
  
  }
}
class mySimpleXML {
  var $_attributes = array();
  var $_name = '';
  var $_data = '';
  var $_children = array();
  var $_level = 0;
  function __construct($name, $data='', $attrs = array(), $level = 0) {
    $this->_attributes = $attrs;
    $this->_name = $name;
    if($data){
      $this->_data = $data;
    }
    $this->_level = $level;
  }
  function setData($data) {
    $this->_data = $data;
  }
  function &addChild($name, $data='', $attrs = array(), $level = null) {
    if(!isset($this->$name)) {
      $this->$name = array();
    }
    if ($level == null)  {
      $level = ($this->_level + 1);
    }
    $classname = get_class( $this );
    $child = new $classname( $name, $data,  $attrs, $level );
    $this->{$name}[] =& $child;
    $this->_children[] =& $child;
    return $child;
  }
  function toXMLString() {
    $out = "\n".str_repeat("\t", $this->_level).'<'.$this->_name;
    foreach($this->_attributes as $attr => $value) {
      $out .= ' '.$attr.'="'.htmlspecialchars($value).'"';
    }
    if (empty($this->_children) && empty($this->_data)) {
      $out .= " />";
    } else {
      if(!empty($this->_children)) {
        $out .= '>';
        foreach($this->_children as $child)
          $out .= $child->toXMLString();
        $out .= "\n".str_repeat("\t", $this->_level);
      } elseif(!empty($this->_data)){
        $out .= '>'.htmlspecialchars($this->_data);
      }
      $out .= '</'.$this->_name.'>';
    }
    return $out;
  }
} 
?>