<?php

$xgmml_file = "/install/Cytoscape/cyto_test.xgmml";
$prohits_web_path = preg_replace("/\/analyst\/.+/", "", $_SERVER['PHP_SELF']); 

if( $_SERVER['REQUEST_METHOD'] == "POST"){
  $request_arr = $_POST;
}else{
  $request_arr = $_GET;
}
foreach ($request_arr as $key => $value) {
  $$key=$value;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>  
<title>Cytoscape Web</title>

<link rel="stylesheet" type="text/css" href="../common/javascript/cytoscape_web/css/layout.css" />
<link rel="stylesheet" type="text/css" href="../common/javascript/jquery/jquery-ui/css/custom-theme/jquery-ui-1.7.2.custom.css" />
<link rel="stylesheet" type="text/css" href="../common/javascript/cytoscape_web/css/demo.css" />
<script type="text/javascript">
var PROHITS_WEB_PATH = "/Prohits";
var DEFAULT_FILE = PROHITS_WEB_PATH + "<?php echo $xgmml_file;?>";
</script> 
<script type="text/javascript" src="../common/javascript/jquery/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/plugins/jquery.qtip-1.0.0-rc3.min.js"></script>
<script type="text/javascript" src="../common/javascript/layout/layout.js"></script>
<script type="text/javascript" src="../common/javascript/string/levenshtein.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/jquery-ui/js/jquery-ui-1.8.12.custom.min.js"></script>
<script type="text/javascript" src="../common/javascript/flash/flash_detect_min.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/plugins/jquery.layout.min.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/plugins/jquery.menu.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/plugins/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/plugins/jquery.validate.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/plugins/jquery.thread.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/plugins/jquery.farbtastic.js"></script>
<script type="text/javascript" src="../common/javascript/jquery/plugins/jquery.cytoscapeweb.js"></script>
<script type="text/javascript" src="../common/javascript/cytoscape_web/json2.min.js"></script>
<script type="text/javascript" src="../common/javascript/cytoscape_web/AC_OETags.min.js"></script>
<script type="text/javascript" src="../common/javascript/cytoscape_web/cytoscapeweb.min.js"></script>
<script type="text/javascript" src="../common/javascript/cytoscape_web/cytoscapeweb-styles-demo.js"></script>
<script type="text/javascript" src="../common/javascript/cytoscape_web/cytoscapeweb-file.js"></script>
<script type="text/javascript" src="../common/javascript/cytoscape_web/demo.js"></script>
<script type="text/javascript" src="../common/javascript/util/ga.js"></script>
</head>
<body>
</body>
</html> 