
<? require_once("include.php");
?>

<?
if(strstr($HTTP_USER_AGENT,"MSIE")) {
?>


<a href="http://www.spreadfirefox.com/?q=affiliates&id=24941&t=84"><img border="0" alt="Get Firefox!" title="Get Firefox!" src="http://sfx-images.mozilla.org/affiliates/Buttons/80x15/blue_2.gif"/></a>
<?
}
?>



<?

switch($_REQUEST['creation_state']) {
  case 'begin':
    echo "<ul>";
    echo "<li><a href = '?page=board&creation_state=findplayer' style='font-size:18pt; font-weight:bold;'>Play Tonobeb</a></li>";
    echo "<li>";
    echo "	Play a game over e-mail:";
    echo "	<form action='' name = 'new_email_game' style='margin-bottom:0px; padding-bottom:0px;text-align:right; width:350px;'>";
    echo "		Your e-mail address: <input type = 'text' name='player_email' /><br />";
    echo "		Opponent's e-mail address: <input type = 'text' name = 'opponent_email' /><br />";
    echo "		<input type = 'submit' value='Start New Game by e-mail' />";
    echo "    <input type = 'hidden' value = 'board' name = 'page' />";
    echo "    <input type = 'hidden' value = 'new_email_game' name = 'creation_state' />";
    echo "	</form>";
    echo "</li>";
    echo "<ul>";
  break;
  case 'playgame':
  case 'new_email_game':
  // request may contain e-mail addresses for
  // the player and the opponent (opponents_email, your_email),
  // so remember to set them in the game record.

  
  case 'findplayer':
  default:
    echo "<div class = 'board_object'>";
    echo "<div style='float:right;'>Concept by Bruce Hallock<br />Implementation by Thomas Hallock</div>";
    echo "<h1>Tonobeb</h1>";
    
    echo "<div class='info'>";
     debug("<div id = 'debug'></div>"); 
     debug("<div id = 'response'></div>"); 
     debug("<div id = 'makero'>did we make a request object?</div>"); 
     debug("</div>"); 
    
    echo "<div id = 'boardnum_msg'>Board number: <span id = \"boardnum\">unknown</span>. <a style=\"float:right;\" href = \"javascript:window.location='?page=board&boardnum='+boardnum\">Reload</a></div>";
    echo "<div id = 'curplayermsg'>Now Playing: <span id = \"curplayer\">undefined</span>. <span style=\"float:right;\"><a href = \"javascript:becomecurrentplayer()\">Become current player</a> <a href = \"javascript:beginfindplayer()\">new game</a></style></div>";
    echo "<div id = 'you'>You are: <span id = 'mycolor'>??</span></div>";
    echo "</div>";
    
    
    $new_game=false;
    
    
    
    echo "<div class = 'board'><table>\n";
    for($row=0;$row<$num_rows;++$row) {
        echo "\t<tr>\n";
    	for($col=0;$col<$num_cols;++$col) {
        
    
    
            echo "\t\t<td id='".$col."|".$row."'><div class='drag'><div class = 'piece'><div style='' class = 'neutralpiece'><div style='' class = 'normal'><a class='drag' style='' href = '";
    
            echo "javascript:clickBoardAt(".$col.", ".$row.")";
            echo "'>?</a></div></div></div></div></td>\n";
        }
        echo "\t</tr>\n";
    }
    echo "</table></div>";
    
    
    echo '<div class = "command_buttons">';
    echo '	<ul>';
    echo "		<li><a href = 'javascript:endTurn()'>finish move</a></li>";
    echo "		<li><a href = 'javascript:sndReq({\"action\":\"undomove\"})'>undo</a></li>";
    echo "	</ul>";
    echo "</div>";
    echo "	<div style='display:none;' class = \"email_settings\"><div class = \"email_controls\"><form style='padding:0px; margin:0px; display:inline;' name = \"email_form\"><input type = 'text' name = 'myemail' /></form>";
    echo "	[ <a href = \"javascript:update_email();\">set</a> ]</div>When I need to move, send an e-mail to:</div>";
    echo "<div>";

	echo 'Instructions: ';
	echo '<a href = "javascript:openinstructions();">how to move</a> - ';
	echo '<a href = "javascript:openrules();">How to play Tonobeb</a>';
    echo '	<div class = "info">';
    echo '		<div class = "message" id = "message">This is where your messages are shown.</div>';
    echo "	</div>";
    echo "</div>";
    echo "<script>";
    echo "		myrunloopinterval = setInterval('runloop()',1000);";
    echo "</script>";
    echo "</div>";

  break;
}
?>
