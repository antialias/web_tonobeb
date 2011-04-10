<?php


class boardpiece {
	function boardpiece($value) {
		$this->value = $value;
		$this->status = ' ';
		$this->undo = -1;
	}
	function setowner($owner) {
		if($this->value > 0)
			if($owner == 'r')
				$this->value *= -1;
		if($this->value < 0)
			if($owner == 'w')
				$this->value *= -1;
	}
	function getowner() {
		debug("thispiece->value == ".$this->value);
		if($this->value > 0)
			return 'w';
		else if($this->value < 0)
			return 'r';
		else
			return 'n';
	}
	function clear() {$this->value = 0; $this->status = ' '; $this->undo = -1;}
    var $value;
    var $status;
    var $undo; // use this to store the moveundostack position where this piece was last moved so we can de a multi-undo to properly undo this piece's move if the player so chooses.
}




function togglepiece($col, $row) {
	global $ret;
	global $theboard;
	global $num_cols;




	$curpiece = $theboard->getpiece($col, $row);
	debug("curpiece->status == ".$curpiece->status);
	switch($curpiece->status) {
	case 'x': $newstatus = 'c'; break;
	case 'c': $newstatus = ' '; break;
	case ' ': $newstatus = 'x'; break;
	}

	// find the number of your pieces that are surrounding the piece you are trying to kill or capture.
	$numkillers = 0;

	$p = $theboard->getpiece($col-1,$row);
	if($p->getowner() == $theboard->cur_player && $p->status == '*')
		++$numkillers;
	$p = $theboard->getpiece($col+1,$row);
	if($p->getowner() == $theboard->cur_player && $p->status == '*')
		++$numkillers;
	$p = $theboard->getpiece($col,$row-1);
	if($p->getowner() == $theboard->cur_player && $p->status == '*')
		++$numkillers;
	$p = $theboard->getpiece($col,$row+1);
	if($p->getowner() == $theboard->cur_player && $p->status == '*')
		++$numkillers;

	if($numkillers <2)
		$ret=("message|numkillers = $numkillers - opponent's piece must be surrounded by two or more of your pieces to be killed or captured");
	else {
		debug("yes, this piece can be killed or captured");
		debug("status change : ".$theboard->pieces[$col+$num_cols*$row]->status." = ".$newstatus);

		$theboard->pieces[$col+$num_cols*$row]->status = $newstatus;
		$theboard->pieces[$col+$num_cols*$row]->value = $curpiece->value;
		$ret="null|";
	}
	
}



class board {
	function board($num_pieces) {
		$this->num_pieces = $num_pieces;
		$this->num_cols=2*$num_pieces+2;
		$this->num_rows=$num_pieces+1;
		$this->cur_player = 'w';
		for($r=0;$r<$this->num_rows; ++$r)
	        for($c=0;$c<$this->num_cols;++$c)
            {
                if($r == 0 && $c < $this->num_pieces) // if c,r is where a white piece belongs
                    $this->pieces[$c+$this->num_cols*$r] = new boardpiece($this->num_pieces-$c);
                else if($r == $this->num_rows-1 && $c > $this->num_cols-$this->num_pieces-1) // if c,r is where a red piece belongs
                    $this->pieces[$c+$this->num_cols*$r] = new boardpiece(-($c-($this->num_cols-$this->num_pieces-1)));
                else
                    $this->pieces[$c+$this->num_cols*$r] = new boardpiece(0);
            }		
	}
	
	function getpiece($col, $row) {
		if($col < 0 || $col >= $this->num_cols || $row < 0 || $row >= $this->num_rows) {
			debug("the requested piece: $col,$row, was out of bounds");
			return new boardpiece("0");
		}
			
		debug("getting piece: ".$col.$row." == ".$this->pieces[$col+$this->num_cols*$row]);
		debug("I am using this board: ".$this->tostring());

		return $this->pieces[$col + $this->num_cols * $row];
	}
	function get_opponent() {
		return $this->cur_player == 'w' ? 'r' : 'w';
	}
	function tostring() {
		$r = $this->cur_player."|";
		foreach($this->pieces as $p) {
			$r .= $p->status.$p->value."|";
		}
		return $r;
	}
	var $num_pieces;
	var $cur_player;
	var $pieces = Array();
	var $num_rows;
	var $num_cols;
}
?>
