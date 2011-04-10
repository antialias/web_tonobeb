<?

// NOTES / TO FIX: 
// if a piece is highlighted for moving, but
// the user clicks on a nother piece that has
// already been moved, the user is allowed to
// re-move that piece as if it had not been moved.


session_start();

require_once("config.php");
require_once("include.php");
require_once("game.php");
require_once("board_class.php");



function curplayer_session_id() {
	global $theboard;
	global $game;
	global $boardnum;
	if($boardnum == -1)
		return -999;
	if($theboard->cur_player == 'w')
		return $game->w;
	else
		return $game->r;
}
class gameroom {
	function gameroom() {
		$this->waiting_status = "posting";
		$this->waiting_session = -1;
		$this->board_serial=-1;
		$this->waiting_session_arrival = time();
	}
	
	function new_session($sid) {
		$this->waiting_session = $sid;
		$this->waiting_session_arrival = time();
		$this->waiting_status = "posting";
	}

	var $waiting_status;
	var $waiting_session;
	var $waiting_session_arrival;
	var $board_serial;
}





if(!file_exists($gamedir."/"."gameroom.tonobebgameroom") || !($gameroomstr = file_get_contents($gamedir."/"."gameroom.tonobebgameroom")))
	$thegameroom = new gameroom();
else
	$thegameroom = unserialize($gameroomstr);
$oldgameroomstr = serialize($thegameroom);
	

if($boardnum == -1)
	$boardnum = $thegameroom->board_serial;
else
	$boardnum = $_REQUEST['boardnum'];

if(!file_exists($gamedir."/"."game.".$boardnum.".tonobebgame") || !($gamestr = file_get_contents($gamedir."/"."game.".$boardnum.".tonobebgame"))) {
	$game = new game($boardnum);
} else {
	$game = unserialize($gamestr);
}
$oldgamestr = serialize($game);



if(!file_exists($gamedir."/"."board.".$boardnum.".tonobebboard")) {
    // error_log("creating new board");
		$theboard = new board($num_pieces);
		$newboard = true;
} else {
    // error_log("reading board from file");
	$newboard = false;
	$theboard=unserialize(file_get_contents($gamedir."/"."board.".$boardnum.".tonobebboard"));
}
$oldboardstr = serialize($theboard); // use this board string to detect if anything has changed by the time we are expected to write the new board file to disk. any time there is a change, we are expected to write the board do disk and store an undo back-up.
    

    $fromcol = $_REQUEST['fromcol'];
    $fromrow = $_REQUEST['fromrow'];
    $frompiece = $theboard->getpiece($fromcol, $fromrow);

	$elapsed = (time()-$thegameroom->waiting_session_arrival);
	if($_REQUEST['action'] == 'new_session') {
		if($thegameroom->waiting_session != session_id()) {
			if($elapsed > $challenge_timeout) { // if the challenger's request is too old, we ignore it and make ourselves a new challenge
				$thegameroom->new_session(session_id());
			}
		}

		$thegameroom->waiting_session_arrival = time();
		$ret = "|";
	} else if($_REQUEST['action'] == 'new_email_game') {

		++$thegameroom->board_serial;
		$boardnum=$thegameroom->board_serial;
		$ret="gotoboard|".$thegameroom->board_serial;
    $game->w=session_id();
    $game->r=-2;
		$game->w_email=$_REQUEST['player_email'];
		$game->r_email=$_REQUEST['opponent_email'];
		$ret = "gotoboard|".$thegameroom->board_serial;
	    // error_log("creating a new e-mail based game");
    // error_log("game->w_email  = ".$game->w_email."<br />");
    // error_log("game->r_email  = ".$game->r_email."<br />");

	} else if($_REQUEST['action'] == 'findplayer') {
		if($thegameroom->waiting_session == session_id()) { // if we initiated the challenge

			switch($thegameroom->waiting_status) { // manage the state-machine of accepting a post for challenge, waiting for a challenger, accepting a challenge, and mack to waiting to post a challenge
			case "accepted":
				$thegameroom->waiting_status = "posting";
                // error_log("someone has accepted my challenge");
				$thegameroom->waiting_session = -1; // reset the waiting mechanism
				$thegameroom->waiting_session_arrival = time();
				$boardnum = $thegameroom->board_serial;
				$ret = "gotoboard|".$thegameroom->board_serial;
			break;
			case "waiting":
			default:

				$elapsed_min = (int)($elapsed / 60);
				$elapsed_sec = (int)($elapsed % 60);
				if($elapsed > $challenge_timeout) { // if our challenge grows old, then cancel the challenge until the page is re-loaded.
					$ret = "nobodyhome|".$elapsed;
				} else {
					$ret = "keepwaiting|".$elapsed;
				}
			break;
			}
		} else { // if we did not initiate the challenge, it is our job to accept it

			switch($thegameroom->waiting_status) { // manage the state-machine of accepting a post for challenge, waiting for a challenger, accepting a challenge, and mack to waiting to post a challenge
			case "waiting":
				$thegameroom->waiting_status = "accepted";
				// probably not a good idea to modify a var that is supposed to represent a request value, but this should work at least.
				$game->r = session_id();
				$game->w = $thegameroom->waiting_session;
				$boardnum = $thegameroom->board_serial;
				$ret = "gotoboard|".$thegameroom->board_serial;
			break;
			case "posting":
			default:

				$thegameroom->waiting_status = "waiting";
				++$thegameroom->board_serial;	// increment the board serial here, so the next time the cchallenger or the challenged refreshed, it will be the same.
				$boardnum = $thegameroom->board_serial;
				$thegameroom->waiting_session = session_id();
				$thegameroom->waiting_session_arrival = time();
				$ret = "keepwaiting|".$elapsed;
			break;
			}
		}
	} else if($_REQUEST['action'] == 'updateemail') {
		switch(session_id()) {
			case $game->r;
				$game->r_email = $_REQUEST['email'];
			    // error_log("updated red's email address");
			break;
			case $game->w;
				$game->w_email = $_REQUEST['email'];
			    // error_log("updated white's email address");
			break;
			default:
			    // error_log("requested email address is not for red or white");
			break;
		}
		$ret = "message|set your email address to ".$_REQUEST['email'];
	} else if(curplayer_session_id() != session_id()) { // if it is not our turn
	    // error_log("it is not your turn");
  // error_log("request action = ".strcmp ('becomecurrentplayer',$_REQUEST['action']));

		switch($_REQUEST['action']) {
		case 'becomecurrentplayer':
    case 'becomeplayer':
      if(isset($_REQUEST['tobecome']))
        $tobecome = $_REQUEST['tobecome'];
      else 
        $tobecome = $theboard->cur_player;
			$elapsed = time() - $game->lastmovetime;
			if(false && $elapsed < 60*5) {
				$ret = "message|it has been less than five minutes since the last move. Please wait another ".(int)($elapsed/60)." minutes and ".($elapsed % 60)." seconds and try again.";
				break;
			}
			if($tobecome == 'w') { 
//				if($game->r == session_id()) // I would like to support the same player at the same machine, though I still need to make some provilsions for this, e.g., detrmining what state to be in when showing the board.
//					$game->r = 0;
				$game->w = session_id();
			} else {
//				if($game->w == session_id())
//					$game->w = 0;
				$game->r = session_id();
			}
    $ret="gotoboard|".$boardnum;
		break;
		case 'board':
		break;
		default:
			$ret=("message|You can't do that unless you are the current player");
		}
	} else { // if it is our turn
		switch($_REQUEST['action']) {
		case 'endturn':
			// Four-walling is an implicit move that kills a piece if it cannot be moved. We check for them here, when the current player has confirmed the move.
			// check for four-walled pieces:
			
			$fw_col = Array(); // store rows and cols of four-walled pieces so we can delete
			$fw_row = Array(); // them after making sure all other pieces have been moved
			
			$opponent = $theboard->get_opponent();
		    // error_log("opponent = $opponent");
		    // error_log("you are = $theboard->cur_player");
			for($r=0;$r<$theboard->num_rows;++$r) {
				for($c=0;$c<$theboard->num_cols;++$c) {
					$p = $theboard->getpiece($c,$r);
					if($p->getowner() == $theboard->cur_player && $p->status == ' ' && $p->value > 1) {
						$wall = 0;
						$l = $c - abs($p->value)+1;
					    // error_log("l == $l");
						for($cp = $c-1; $cp >= $l; --$cp) { // check spots to the left of the piece
						    // error_log("cp == $cp");
	
							if($cp >= 0) {
							    // error_log("if(".$theboard->getpiece($cp,$r)->value." != 0 && ((".$cp." == ".$l.") && (".$theboard->getpiece($cp,$r)->getowner()." != ".$opponent.")))");
								if(!($cp == $l && $theboard->getpiece($cp,$r)->getowner() == $opponent) &&
								 ($theboard->getpiece($cp,$r)->value !=0)) {
									++$wall; error_log("hit left wall @ $cp,$r");
									break;
								}
							} else {
								++$wall;
							    // error_log("hit left bounds");
								break;
							}
						}
						$l = $c + abs($p->value)-1;
						for($cp = $c+1; $cp <= $l; ++$cp) { // check spots to the right of the piece
						    // error_log("cp == $cp");

							if($cp < $theboard->num_cols) {
								if(!($cp == $l && $theboard->getpiece($cp,$r)->getowner() == $opponent) &&
								 ($theboard->getpiece($cp,$r)->value !=0)) {
									++$wall; error_log("hit right wall @ $cp,$r");
									break;
								}
							} else {
								++$wall; error_log("hit right bounds with piece $c, $r, $p->value");
								break;
							}
						}
						$l = $r - abs($p->value)+1;
						for($rp = $r-1; $rp >= $l; --$rp) { // check spots above the piece
							if($rp >= 0) {
								if(!($rp == $l && $theboard->getpiece($c,$rp)->getowner() == opponent) &&
								 ($theboard->getpiece($c,$rp)->value != 0)) {
									++$wall; error_log("hit above wall @ $c,$rp");
									break;
								}
							} else {
								++$wall; error_log("hit above bounds");
								break;
							}
						}
						$l = $r + abs($p->value)-1;
						for($rp = $r+1; $rp <= $l; ++$rp) { // check spots below the piece
							if($rp < $theboard->num_rows) {
								if(!($rp == $l && $theboard->getpiece($c,$rp)->getowner() == opponent) &&
								 ($theboard->getpiece($c,$rp)->value != 0)) {
									++$wall; error_log("hit below wall @$c,$rp");
									break;
								}
							} else {
								++$wall; error_log("hit below bounds");
								break;
							}
						}
						if($wall == 4) {
							$theboard->pieces[$c+$theboard->num_cols*$r]->status = 'F';
						    // error_log("FOUR WALLED! at $c, $r");
						} else {
						    // error_log("no four-walls: walls == $wall");
						}
					}
				}
			}

			for($c=0;$c<$theboard->num_cols;++$c) { // check for un-moved pieces and exit if there are any.
				for($r=0;$r<$theboard->num_rows; ++$r) {
					$curpiece = $theboard->getpiece($c, $r);
					if($curpiece->status == ' ' && $curpiece->getowner() == $theboard->cur_player) { // we can still move some pieces so the turn isn't finished
						foreach($theboard->pieces as $p) // undo the pieces marked as four-walled because they shouldn't be if they can be legitametely moved.
							if($p->status == 'F')
								$p->status = ' ';
						$ret=("message|you must move all your pieces before ending your turn");
						break 3;
					}
				}
			}


			// it is okay for the player to end the turn if we got here, so go ahead and set the statuses of the pieces to normal.

			for($c=0;$c<$theboard->num_cols;++$c) {
				for($r=0;$r<$theboard->num_rows; ++$r) {
					$curpiece = $theboard->getpiece($c, $r);
					switch($curpiece->status) {
						case 'c':
							$curpiece->value = -$curpiece->value;
						break;
						case 'F';
						case 'x':
							$curpiece->value = 0;
						break;
					}
					$theboard->pieces[$c+$theboard->num_cols*$r]->status = " ";
					$theboard->pieces[$c+$theboard->num_cols*$r]->undo = -1;
				}
			}
			$theboard->cur_player = $theboard->cur_player == 'r' ? 'w' : 'r';
			
			// It is now the opponent's turn, so e-mail them to let them know.
			
			if(curplayer_session_id() == $game->r) {
				$to   = $game->r_email;
				$from = $game->w_email;
        $beplayer = 'r';
			} else {
				$to   = $game->w_email;
				$from = $game->r_email;
        $beplayer = 'w';
			}
			$subject = "It is your turn on Tonobeb board ".$boardnum;
			
      $server_directory  = ($_SERVER['HTTPS'] ? "https" : "http")."://";
      $server_directory .= $_SERVER['SERVER_NAME'];
      $server_directory .= substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],'/')+1);
      
      $link_to_board  = $server_directory."index.php";
      $link_to_board .= "?page=board";
      $link_to_board .= "&creation_state=coming_from_email";
      $link_to_board .= "&boardnum=".$boardnum;
      $link_to_board .= "&beplayer=".$beplayer;

      $message  = "Hi! This message was automatically generated when your\n";
      $message .= "tonobeb opponent, with e-mail address ".$from."\n";
      $message .= "ended his turn.\n";
      $message .= "Click the link below to go to the board and make your move:\n\n";
      $message .= $link_to_board."\n\n";
      $message .= "Your opponent will be notified by e-mail when you click the\n";
      $message .= " \"end turn\" button after you have finished making your move.\n";
      $message .= "\n\nIf you believe you got this message in error, please send\n";
      $message .= "a message to wrongemail@tonobeb.com and we'll take your\n";
      $message .= "address out of the system.";

			$headers  = "From: $from\r\n";
			$headers .= "X-Sender: \n";
			$headers .= "X-Mailer: PHP5\n";
			$headers .= "X-Priority: 3\n";
			$headers .= "Return-Path: $from\n";

			$success = mail($to, $subject, $message, $headers, "-f ".$from);
			if ($success) {
			    // error_log("The email to $to from $from was successfully sent");
			} else {
			    // error_log("An error occurred when sending the email to $to from $from"); 
		    }
        
			
		break;
		case "handleboardclick":
		    // error_log("frompiece->value == ".$frompiece->value."");
			$fpo = $frompiece->getowner();
		    // error_log("frompiece->owner == ".$fpo."");
			switch($frompiece->getowner()) {
				case $theboard->cur_player:
					if($frompiece->status == '*') {// if you click on one of your pieces that you have already moved, rewind moves until that piece has not moved yet.
						if($frompiece->undo >0)
							$theboard = unserialize(file_get_contents($gamedir."/"."undomove.".$frompiece->undo."."."board.".$boardnum.".tonobebboard"));
 					}
					else
						$ret=("movepiece|".$fromcol."|".$fromrow."|"); // the piece was selected to be moved. tell the client to tell the user that we are waiting to know where it is going to be moved.
				break;
				case 'n':
					$ret=("message|that space is empty");
				break;
				default: // toggle between killing, capturing, or leaving an opponent's piece alone.
					togglepiece($fromcol, $fromrow);
				break;
			}
		    // error_log("isset(ret) == ".isset($ret));
			
		break;
		case 'movepiece':
		    // error_log("moving piece");
			$tocol = $_REQUEST['tocol'];
			$torow = $_REQUEST['torow'];

			$topiece = $theboard->getpiece($tocol, $torow);
			$tpo = $topiece->getowner();
			$fpo = $frompiece->getowner();


			if($frompiece->getowner() == 'n') {
				$ret=("message|that is not a piece");
				break;
			}
			$coldist = abs($tocol - $fromcol);
			$rowdist = abs($torow - $fromrow);
			$movedist = max($coldist, $rowdist);
						
			if($movedist !=0 && $tpo == $fpo) { // make sure we don't move on top of one of our own pieces; unless we are moving zero units.
				// another of ourthe current player's pieces has been clicked while attempting to move a different piece.
				$ret=("movepiece|$tocol|$torow");
			}
			else if($coldist > 0 && $rowdist > 0) {
				$ret=("illegalmovespace|pieces may only be moved across rows or columns");
			}
			else if(abs($movedist - abs($frompiece->value))>1) {
				$ret=("illegalmovespace|cannot move your ($frompiece->value,$fromcol,$fromrow) ($coldist, $rowdist); you may only move one unit greater than or less than the value of your piece.");
			}
			else if($movedist > 6) {
				$ret = "illegalmovespace|you may not move any piece more than ".$theboard->numpieces." spaces";
			}
			
			else if($coldist > 0) { // piece was moved along a column; make sure there are no pieces in the moved path
				$scanbeg = min($fromcol, $tocol);
				$scanend = max($fromcol, $tocol);
				for($c = $scanbeg+1;$c < $scanend; ++$c) {
					if($theboard->getpiece($c, $torow)->value != 0) {
						$ret=("illegalmovespace|you cannot move over pieces in a row");
						break;
					}
				}
			} else { // piece was moved along a row; make sure there are no pieces in the moved path
				$scanbeg = min($fromrow, $torow);
				$scanend = max($fromrow, $torow);
				for($r = $scanbeg+1;$r < $scanend; ++$r) {
					if($theboard->getpiece($tocol, $r)->value != 0) {
						$ret=("illegalmovespace|you cannot move over pieces in a column");
						break;
					}
				}
			}
			if(isset($ret)) {
				// the attempted move was illegal
			    // error_log("the attempted move was illegal");
				if($tpo != 'n' && $tpo != $fpo) {
					// an opponents piece was clicked.
				    // error_log("an opponent's piece was clicked");
					togglepiece($tocol, $torow);
				} else {
					 error_log("an opponent's piece was not clicked");
				}
			} else {
	
				// if we have gotten to this point, it is a valid move and will be recorded on the board.
	
				$theboard->pieces[$fromcol + $num_cols*$fromrow]->clear(); // reset the location we are coming from
				$theboard->pieces[$tocol   + $num_cols*$torow]->status = "*"; // set the status of the place we are going to as "in turn".
				$theboard->pieces[$tocol   + $num_cols*$torow]->value = max(1,$movedist); // set the value to the number of spaces moved.
				$theboard->pieces[$tocol   + $num_cols*$torow]->setowner($fpo);
				
	//			if($frompiece->value < 0)
	//				$theboard->pieces[$tocol   + $num_cols*$torow]->value *= -1;
				
				$theboard->pieces[$tocol   + $num_cols*$torow]->undo = $game->moveundostack; // store the undo number of the board when this piece was moved, just in case the user decides to take back this move.
			}
		break;
		case "undomove":
		    // error_log("trying to undo");
		    // error_log("if(".curplayer_session_id()." != ".session_id().")");
			if(curplayer_session_id() != session_id()) {
				$ret=("message|can't take back a move when it's not your turn");
				break;
			}
			if($game->moveundostack <= 0) {
				$ret=("message|no move history beyond this point");
				break;
			}
			--$game->moveundostack;
			$theboard = unserialize(file_get_contents($gamedir."/"."undomove.".$game->moveundostack."."."board.".$boardnum.".tonobebboard"));

		    // error_log("undid! - ".$game->moveundostack);
		break;
		}
	}


	$boardchanged=false;
    if($newboard == true || strcmp(serialize($theboard), $oldboardstr) != 0) { // if the board has changed, write it back to disk and save an undo file
		$boardchanged=true;
        // error_log("I am writing a new board to disk");
		$game->lastmovetime = time();
        if($_REQUEST['action'] != 'undomove') { // we want to support multiple-undos, so we can't store the undo as a move, otherwise, we would end up undoing our undo if we recorded it.
  
            if(!file_put_contents($gamedir."/"."undomove.".$game->moveundostack."."."board.".$boardnum.".tonobebboard", $oldboardstr))
                die("message|cannot write move undo file");
            ++$game->moveundostack;
        }
		$boardstr = serialize($theboard);
        if(!file_put_contents($gamedir."/"."board.".$boardnum.".tonobebboard",$boardstr)) // make sure we are able to write to the board file
            die("message|cannot write to board file");

    } else {
	    // error_log("the board was not changed, so I don't need to write anything to disk");
    }
	
	$gamestr = serialize($game);
	if(strcmp($oldgamestr, $gamestr) != 0) {
	    // error_log("serializing the game:<br />".$gamestr);
		if(!file_put_contents($gamedir."/"."game.".$boardnum.".tonobebgame",$gamestr))
		    die("message|could not write game details to disk");
	} else {
	    // error_log("the game state was not changed, so I'm not writing anything to disk");
    }
		
    // error_log("game['w'] == ".$game->w."");
    // error_log("game['r'] == ".$game->r."");
    // error_log("session id == ".session_id()."");

	if(session_id() == $game->r) {
		$reqcolor = "red";
    $reqemail = $game->r_email;
	} else if(session_id() == $game->w) {
		$reqcolor = "white";
    $reqemail = $game->w_email;
	} else
		$reqcolor = "watching";

	$thegameroomstr = serialize($thegameroom);
	if(strcmp($oldgameroomstr, $thegameroomstr) != 0) {
		if(!file_put_contents($gamedir."/"."gameroom.tonobebgameroom", serialize($thegameroom)))
			die("message|could not write to gameroom file");
	} else {
        // error_log("the gameroom file was not changed, so I don't need to write it back to disk");
	}

	if(isset($ret) && !$boardchanged)
		echo $ret;
	else {
		echo "board|".$reqcolor."|".$reqemail."|".$theboard->tostring();
	}
?>
