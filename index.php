<?

require_once("config.php");
require_once("include.php");

session_start();


if(isset($_REQUEST['ref'])) {
	if(file_exists("data/referrals")) {
		$ref_file = file_get_contents("data/referrals");
		$referrals = unserialize($ref_file);
	} else
		$referrals = array();
	if(isset($referrals[$_REQUEST['ref']]))
		++$referrals[$_REQUEST['ref']];
	else
		$referrals[$_REQUEST['ref']] = 1;
		
	file_put_contents("data/referrals",serialize($referrals));
}


?>
<html>
<head>
<title>
<?
	switch($_REQUEST['page']) {
	case board:
	case findplayer:
		echo "A game of Tonobeb";
	break;
	default:
		echo "Tonobeb.com";
	}
?>
</title>
<LINK REL=StyleSheet HREF="css/style.css" TYPE="text/css" MEDIA=screen>

<style>
.drag{position:relative;}
</style>
<script>

<?

include("js/scripts.php");

?>

</script>




<script language="JavaScript1.2">
<!--
/*Credit JavaScript Kit www.javascriptkit.com*/
var dragapproved=false
var z,x,y
function move(){
if (event.button==1&&dragapproved){
z.style.pixelLeft=temp1+event.clientX-x
z.style.pixelTop=temp2+event.clientY-y
return false
}
}
function drags(){
if (!document.all)
return
if (event.srcElement.className=="drag"){
dragapproved=true
z=event.srcElement
temp1=z.style.pixelLeft
temp2=z.style.pixelTop
x=event.clientX
y=event.clientY
document.onmousemove=move
}
}
document.onmousedown=drags
document.onmouseup=new Function("dragapproved=false")
</script>




</head>
<body onload='javascript:main()'>


<?

switch($_REQUEST["page"]) {
case "board":
break;
default:
echo "<div style = 'margin:10px;'>";

echo "<ul>\n";
echo "<li><a href = '/'>Tonobeb.com home</a></li>\n";
echo "<li><a href = '?page=howtoplay'>How to play Tonobeb</a></li>\n";
echo "<li><a href = '?page=howtoplayonline'>How to move on the online board</a></li>\n";
echo "<li><a href = '?page=active_boards'>Active boards</a></li>\n";
echo "<li><a href = 'javascript:opengamewindow();'>Play Tonobeb!</a></li>\n";
echo "</ul>";

break;
}

// error_log("your session id is: ".session_id()); // don't reference this by $PHPSESSID because it won't be set for the first time the page loads for a new session.

switch($_REQUEST["page"]) {
case "board":
	include("board.php");
break;
case "howtoplayonline":
	include("howtoplayonline.php");
break;
case "howtoplay":
	include("howtoplay.php");
break;
case "active_boards":
	include("active_boards.php");
break;
default:
	include("enter.php");
break;
}



switch($_REQUEST["page"]) {
case "board":
break;
default:
echo "<div>";

break;
}




?>

</body>
</html>
