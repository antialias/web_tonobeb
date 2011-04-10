
/*
class board {
	function board(num_pieces) {
		this->num_pieces = $num_pieces;
		this->num_cols=2*$num_pieces+2;
		this->num_rows=$num_pieces+1;
		this->cur_player = 'w';
		for($r=0;$r<$this->num_rows; ++$r)
	        for($c=0;$c<$this->num_cols;++$c)
            {
                if($r == 0 && $c < $this->num_pieces) // if c,r is where a white piece belongs
                    $this->pieces[$c+$this->num_cols*$r] = (string)($this->num_pieces-$c);
                else if($r == $this->num_rows-1 && $c > $this->num_cols-$this->num_pieces-1) // if c,r is where a red piece belongs
                    $this->pieces[$c+$this->num_cols*$r] = (string)(-($c-($this->num_cols-$this->num_pieces-1)));
                else
                    $this->pieces[$c+$this->num_cols*$r] = (string)"0";
            }		
	}
	
	function getpiece($col, $row) {
		if($error_log) echo "getting piece: ".$row.$col." == ".$this->pieces[$col+$this->num_cols*$row]."<br />";
		if($error_log) echo "I am using this board: ".$this->tostring()."<br />";
		if($error_log) echo "I am using this piece array: ".serialize($this->pieces).". <br />";
		if($col < 0 || $col > $this->num_cols || $row < 0 || $row > $this->num_rows)
			$p = "0";
		else
			$p = $this->pieces[$col+$this->num_cols*$row];

		switch($p[0]) {
			case '*':
			case 'x':
			case 'c':
			case 'F':
				$s = $p[0];
				$p = substr($p, 1, strlen($p)-1);
			break;
			default:
				$s = ' ';
		}
		$r = new boardpiece;
		$r->owner = $p > 0? 'w':($p < 0 ? 'r' : 'n');
		$r->value = $p;
		$r->status = $s;

		return $r;
	}
	function get_opponent() {
		return $this->cur_player == 'w' ? 'r' : 'w';
	}
	function tostring() {
		$r = $this->cur_player."|";
		foreach($this->pieces as $p)
			$r .= $p."|";
		return $r;
	}
	var $num_pieces;
	var $cur_player;
	var $pieces = Array();
	var $num_rows;
	var $num_cols;
}
*/