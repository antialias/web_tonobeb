<?
require_once("game.php");

?>


<?
if(strstr($HTTP_USER_AGENT,"MSIE")) {
?>

<a href="http://www.spreadfirefox.com/?q=affiliates&id=24941&t=51"><img border="0" alt="Get Firefox!" title="Get Firefox!" src="http://sfx-images.mozilla.org/affiliates/Banners/120x600/rediscover.png"/></a>
<br />
Game play on Tonobeb.com works best with Firefox or Apple's Safari web browser. It sort of works with Internet Explorer, but you'll notice some rendering bugs that will degrade your experience.

</div>

Enter all the IE-destroying HTML that you want here.

<?
}
?>

<h1>Welcome to Tonobeb.com!</h1>

<?
/*
	// show a big board to click on to start the game

<div style = 'text-align:center;' >
<a href = 'javascript:opengamewindow()' >
Click the board to start a new game<br />
<img src = 'imgs/tonoboard.png' />
</a>
</div>

*/

echo "<iframe width = 404 height = 430 src = 'http://".$_SERVER['SERVER_NAME']."/?page=board&creation_state=findplayer'></iframe>";

?>

<h2>What's Tonobeb?</h2>

<p>
Tonobeb is a dice game described in the premier issue of Gameplay Magazine (February 1983). The game is played on a 7 square by 14 square board. The dice are not rolled, but instead moved around the board as pieces. Each player starts with six dice. one of each pip-strength from 1 to 6 in opposite corners of the board.
Pieces move horizontally or vertically the number of spaces shown on the top face of each die, or one space more or one less. If moved more or less the value, of the die is changed to show the actual number of spaces moved.
Units can be eliminated from play in one of three ways called "smashing", "capturing" and "fourwalling".
"Smashing" is landing on an opposing piece by exact count, removing it from play.
Pieces are captured by placing opposing pieces on either side, adjacent, and replacing it with a die of the opposing player's color.
"Fourwalling" is blocking a piece from moving its required number of spaces in every direction. Fourwalled pieces are removed from play.
The object of the game is to eliminate all oposing pieces.</p>

<h2>Things you can do here:</h2>

<ul>
<li><a href = '?page=howtoplay'>Learn to play Tonobeb</a></li>
<li><a href = 'javascript:opengamewindow();' style="font-size:18pt; font-weight:bold;">Begin a new game</a></li>
<li>Read or subscribe to the <a href = 'http://groups.google.com/group/tonobeb-announcements'>Tonobeb.com announcements list</a>.</li>
<?
//echo "<li><a href = 'gameplay_article/'>Read the original article published in the first issue of Gameplay Magazine in 1983</a></li>";
?>
<li>Look at references to Tonobeb around the internet:
<ul>
<li><a href = 'http://www.boardgamegeek.com/game/21609'>BoardGameGeek game information</a></li>
<li><a href = 'http://homepage.ntlworld.com/andy.merritt/OldCatJun04.htm'>MNG-AJM's June 2004 Catalog</a></li>
<li><a href = 'http://groups.google.com/group/rec.games.board.marketplace/browse_thread/thread/724263e2218a07c7/4e96e413d4e314b2?lnk=st&q=tonobeb&rnum=1#4e96e413d4e314b2'>Usenet Posting from November 1997</a></li>
</ul>
</li>
</ul>

Recent gameplay activity:
<?
include "active_boards.php";
?>
</p>

<?
 echo "<a href = 'http://www.gvisit.com/map.php?sid=1207bd69136e5d3e74d0e1e666982824'>map of visitors</a><br />";
 echo '<script language="JavaScript" src="http://www.gvisit.com/record.php?sid=1207bd69136e5d3e74d0e1e666982824" type="text/javascript"></script>';
?>
<div style='margin-top:50px;'>Send feedback to: <a href = 'mailto:tonobeb_website_feedback.20.antialias@spamgourmet.com'>Thomas Hallock</a></div>
