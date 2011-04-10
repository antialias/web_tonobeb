

var session='<? echo session_id(); ?>';
var num_cols=<? echo $num_cols; ?>;
var num_rows=<? echo $num_rows; ?>;
var request_page = "<? echo (isset($_REQUEST["page"]) ? $_REQUEST["page"] : ""); ?>";
var debug = "<? echo (isset($_REQUEST["debug"]) ? $_REQUEST["debug"] : ""); ?>";
var boardnum = <? if(isset($_REQUEST['boardnum'])) echo $_REQUEST['boardnum']; else echo "-1"; ?>;
var creation_state =<? if(isset($_REQUEST['creation_state'])) echo '"'.$_REQUEST['creation_state'].'"'; else echo "-1";?>;
var movepiece={"row":-1,"col":-1};
var movestate;
var beplayer = "<? echo (isset($_REQUEST["beplayer"]) ? $_REQUEST["beplayer"] : ""); ?>";

var choosingpiece = 1;
var movingpiece = 2;

function trimString (str) {
  str = this != window? this : str;
  return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}

function createRequestObject() {
	if(debug)
		if(document.getElementById("makero"))
      document.getElementById("makero").innerHTML="made httprequest";
	var ro;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer"){
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    }else{
        ro = new XMLHttpRequest();
    }
	return ro;
}

var http;
var isBusy = false;
var colisions = 0;
function sndReq(params) {
	if(isBusy) { // this is to handle requests colliding; mozilla doesn't take this very well. See 
		http.onreadystatechange = function () {}; // IE doesn't like this to be set to a non-function.
		http.abort();
		++colisions;
		if(debug)
			document.getElementById("debug").innerHTML="XMLRequests have collided "+colisions+" times.";
	}
    isBusy = true;
	
	params['boardnum'] = boardnum;
	
	var params_length = 0;
    for(var i in params)
        ++params_length;
    // if(params.length > 0) { // this doesn't work, why can't I get the length of an associative array with Firefox?
    if(params_length > 0) {
        var urlparams = "?";
        for(var key in params)
            urlparams += key + "=" + encodeURIComponent(params[key]) + "&";
        urlparams = urlparams.substr(0,urlparams.length-1);
    }


    http.open('get', 'rpc.php'+urlparams);

    http.onreadystatechange = handleResponse;
    http.send(null);

}

var updateboard = true;

function nullfunc() {return 0; }

var boardreq = createRequestObject();
var updateboard_busy = false;

// creates an XMLHTTPRequest to ask the server to send the latest copy of the board
function updateboard_func() {
	if(updateboard_busy) { // this is to handle requests colliding; mozilla doesn't take this very well. See 
		boardreq.onreadystatechange = function () {};  // IE doesn't like this to be set to a non-function.
		boardreq.abort();
		++colisions;
		if(debug)
			document.getElementById("debug").innerHTML="XMLRequests have collided "+colisions+" times.";
	}
    updateboard_busy = true;
    
    boardreq.open("get", "rpc.php?action=board&boardnum="+boardnum);
	boardreq.onreadystatechange=updateboard_resp;
	boardreq.send(null);
}


// global variable that updateboard_resp stores teh latest board copy into.
var lastboardupdate;

// uses the global variable 'lastboardupdate' that updateboard_resp st,ores the board into to update the page with.
function redrawboard() {
	parseboard(lastboardupdate);
}

// handler fonction for the request created on updateboard_func
function updateboard_resp() {
	if(boardreq.readyState != 4)
		return;
	updateboard_busy=false;
    var response = boardreq.responseText;
     lastboardupdate = new Array();

    if(response.indexOf('|') != -1) {
        lastboardupdate = response.split('|');
		redrawboard();
    }
}


// does the actual work of updating teh contents of the board page
function parseboard(update) {
	if(boardnum >= 0) {
		document.getElementById("boardnum").innerHTML = boardnum;
	} else {
		document.getElementById("boardnum").innerHTML = "unspecified";
	}
		
		document.getElementById("mycolor").innerHTML=update[1];
	
	if(update[3] == 'r') {
		document.getElementById("curplayer").innerHTML="red";
		if(update[1] != 'red') updateboard = true;
		else updateboard = false;
	} else {
		document.getElementById("curplayer").innerHTML="white";
		if(update[1] != 'white') updateboard = true;
		else updateboard = false;
	}
	document.email_form.myemail.value = update[2];
	for(row=0;row<num_rows;++row) {
		for(col=0;col<num_cols;++col) {
			var owner;
			var status;
			var value;
			pieceval = trimString(update[4+col+num_cols*row]);
			switch (pieceval.substring(0,1)) {
				case '*':
					status = "movedpiece";
				break;
				case 'c':
					status = "capturedpiece";
				break;
				case 'x':
					status = "killedpiece";
				break;
				default:
					if(movepiece.col == col && movepiece.row == row) {
						status = "selectedpiece";
					} else {
						status = 'normal';
					}
				break;
			}
			switch(pieceval.substring(0,1)) {
				case '*':
				case 'c':
				case 'x':
					pieceval = pieceval.substr(1,pieceval.length-1);
				break;
			}

			switch (pieceval.substring(0,1)) {
				case '-':
					owner = 'redpiece';
					value = String(pieceval.substr(1,pieceval.length-1));
				break;
				case '0':
					owner = 'neutralpiece';
					value = "&nbsp;";
				break;
				default:
					owner = 'whitepiece';
					value = String(pieceval);
			}

			piece_cell = document.getElementById(String(col)+"|"+String(row)).childNodes[0].childNodes[0];
			piece_cell.childNodes[0].className = owner;
			piece_cell.childNodes[0].childNodes[0].className = status;
			piece_cell.childNodes[0].childNodes[0].childNodes[0].innerHTML = value;
		}
	}
}


function handleResponse() {
	if(http.readyState != 4)
		return;
	isBusy = false;

    var response = http.responseText;
    var update = new Array();

    if(response.indexOf('|') != -1) {
        update = response.split('|');
        switch(update[0]) {
            case "beginupdates":
                updateboard=true;
                document.getElementById("message").innerHTML="beginning regular board updates, as recommended by server";
            break;
            case "board":
				if(debug)
					document.getElementById("debug").innerHTML="received board update from server";

				updateboard_func()
				movepiece.row=-1;
                movepiece.col=-1;
            break;
			case "keepwaiting":
				document.getElementById("message").innerHTML="Challenge posted "+update[1]+" seconds ago<br />"+
					"Get a buddy to go to <a href = 'http://tonobeb.com/?page=findplayer'>tonobeb.com</a> to start this game.";
				setTimeout('findPlayer()',1000);
			break;
      case "movepiece":
        movepiece.row=update[2];
        movepiece.col=update[1];
        movestate=movingpiece;

				redrawboard();
        document.getElementById("message").innerHTML="moving piece from col:"+update[1]+" row:"+movepiece.row+".";
      break;
			case "gotoboard":
				request_page="board";
				clearInterval(findPlayerInterval);
				boardnum=update[1];
				changingboards = true;
				document.getElementById("message").innerHTML = "Challenge Accepted. Going to board number "+update[1];
        window.location='?page=board&boardnum='+boardnum;
//				updateboard_func();
			break;
			case "nobodyhome":
				document.getElementById("message").innerHTML="No-one accepted your challenge within a reasonable amount of time. Click \'new game\' to re-issue your challenge.";
			case "illegalmovespace": // if the user tries to move to an empty, but illegal space on the board.
				movestate = movingpiece;
				document.getElementById("message").innerHTML = update[1];
			break;
        case "null":
      break;
      default:
        document.getElementById("message").innerHTML = update[0]+"|"+update[1];
      }
	}
}



movestate=choosingpiece;

function clickBoardAt(col, row) {
    switch (movestate) {
        case choosingpiece:
            sndReq({"action":"handleboardclick", "fromrow":row, "fromcol":col});
        break;
        case movingpiece:
            sndReq({"action":"movepiece", "fromrow":movepiece["row"], "fromcol": movepiece["col"], "torow": row, "tocol": col});
            movestate=choosingpiece;
        break;
    }
}

function endTurn() {
    sndReq({"action":"endturn"});
}

function becomecurrentplayer() {
	sndReq({"action":"becomecurrentplayer"});
}


function opengamewindow() {
	opengamewindow_board(-1);
}

function opengamewindow_board(boardnum) {
	if(boardnum == -1) {
		window.open("?page=board&creation_state=begin",'','resizable=no,scrollbars=no,menubar=no,height=415,width=404,toolbar=no,location=no,status=no');
	} else {
		window.open("?page=board&boardnum="+boardnum,'','resizable=no,scrollbars=no,menubar=no,height=415,width=404,toolbar=no,location=no,status=no');
	}
}

function openinstructions() {
	window.open("?page=howtoplayonline","","");
}

function openrules() {
	window.open("?page=howtoplay","","");
}

var update_email_reqobj;
function update_email_resp() {
	resp = update_email_reqobj.responseText.split('|');
	document.getElementById("message").innerHTML=resp[1];
}


function update_email() {

	update_email_reqobj = createRequestObject();
	document.getElementById("message").innerHTML="updating email address";
	var req_url = 'rpc.php?boardnum='+boardnum+'&action=updateemail&email='+document.getElementById("myemail").value;
	document.getElementById("message").innerHTML=req_url;
	update_email_reqobj.open('get', req_url);
	update_email_reqobj.onreadystatechange = update_email_resp;
	update_email_reqobj.send(null);
}

function runloop() {
	if(updateboard == true)
		updateboard_func();
	return 0;
}

function findPlayer() {
	sndReq({'action':'findplayer'});
}

function beginfindplayer() {
	var new_session_reqobj;
	new_session_reqobj = createRequestObject();
	new_session_reqobj.open('get', 'rpc.php?action=new_session');
	new_session_reqobj.onreadystatechange = function() { };
	new_session_reqobj.send(null);
	findPlayer();
}

var myrunloopinterval = -1;
var findPlayerInterval = -1;
http = createRequestObject();

function main() {
	switch(creation_state) {
  case "findplayer":
		boardnum=-1;
		beginfindplayer();
		document.getElementById("message").innerHTML="trying to find a player";
  break;
  case "new_email_game":
    var player_email = '<? if(isset($_REQUEST["player_email"])) echo $_REQUEST["player_email"]; else echo "-1"; ?>';
    var opponent_email = '<? if(isset($_REQUEST["opponent_email"])) echo $_REQUEST["opponent_email"]; else echo "-1"; ?>';
    sndReq({'action':'new_email_game', 'player_email':player_email, 'opponent_email':opponent_email});
  break;
  case "coming_from_email":
    sndReq({'action':'becomeplayer','beplayer':beplayer,'boardnum':boardnum});
  break;
  }
}
