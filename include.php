<?

$debug = $_REQUEST['debug'];

function debug($str) {
	global $_REQUEST;
	if($_REQUEST['debug'])
		echo $str."<br />";
}

?>