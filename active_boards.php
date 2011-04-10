
<?
require_once "game.php";

	$dir = $gamedir."/";

$old_board_time_limit_hours = 24*10; // show boards from the past ten days

echo "boards that are less than ".$old_board_time_limit_hours." hours old:</p>";

	if (!is_dir($dir))
		die("games directory does not exist");
	
	if (!$dh = opendir($dir))
		die("cannot open games directory");
	
class board_info {
	var $date;
	var $id;
}

$board_infos = array();

while (($file = readdir($dh)) !== false) {
	if(filetype($dir . $file) == "file") {
		if(substr($file,0,strlen("board.")) == "board.") {
			for($i=strlen("board.");$file[$i] != '.';++$i)
				;
			if($i > strlen("board.")) {
				$boardnum = substr($file,strlen("board."),$i-strlen("board."));
				if(!$my_gamestr = file_get_contents($gamedir."/game.".$boardnum.".tonobebgame"))
					die("could not open file: ".$gamedir."/game.".$boardnum.".tonobebgame");
				$cur_game = unserialize($my_gamestr);
				$cur_board_info = new board_info();
				$cur_board_info->date = $cur_game->lastmovetime;
				$cur_board_info->id = $boardnum;
				
				$board_infos[$cur_board_info->date.".".$boardnum] = $cur_board_info;
			}
		}
	}
}

krsort($board_infos);

if(count($board_infos) > 0) {
	echo "<ul>\n";
	foreach($board_infos as $cur_info) {
		$age = time() - $cur_info->date;
		if($age < 60*60*$old_board_time_limit_hours) { // don't show boards that are older than the cut-off in hours
			echo "<li><a href = 'javascript:opengamewindow_board(".$cur_info->id.")'>Board ".$cur_info->id."</a> - ";
			echo "Last move was ";
			if($age < 60)
				echo (int)($age)." seconds";
			else if($age < 60*60)
				echo (int)($age/60)." minutes";
			else if($age < 60 * 60 * 24)
				echo (int)($age/(60*60))." hours";
			else
				echo (int)($age / (60 * 60 * 24))." days";
			echo " ago.";
			
			if($game->r == session_id())
				echo "- <b>you are red</b> ";
			if($game->w == session_id())
				echo "- <b>you are white</b> ";
			echo "</li>";
		}
	}
	echo "</ul>\n";
} else
	echo "no activity";

closedir($dh);
	
?>
