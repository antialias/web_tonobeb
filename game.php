<?

class game {
	function game($boardnum) {
		$this->r = "away";
		$this->w = "away";
//		$this->r_email = "thomas@hallock.net";
//		$this->w_email = "thomas@hallock.net";
		$this->moveundostack = 0;
		$this->turnundostack = 0;
	}
	var $lastmovetime;
	var $moveundostack;
	var $turnundostack;
	var $r;
	var $r_email;
	
	var $w;
	var $w_email;
}

?>