<?php

class Main extends Controller {

	function Main()
	{
		parent::Controller();
		$this->load->library('arrays');
		$this->load->helper('html');
		$this->load->helper('url');
	}
	
	function index()
	{
		$this->load->view('main_view');
		
	}

	function analyze () {
		$winning_percentage = 0;
		$rank_above = 0;
		$rank_below = 0;
		$rank_equal = 0;

		$timestart = microtime(true);
		$reserved_cards = $this->arrays->getVar('cards');
		for ($i=3; $i<=9; $i++){
			$l = $this->uri->segment($i);
			if ($l == 0)
				break;
			//get from arrays instead of using table
			$cards[] = $reserved_cards[$l];
		}

		//evaluating 7 cards
		if ( !empty($cards[6]) ) {
			$rank = $this->eval_7hand($cards);
			$available_cards = array_values(array_diff($reserved_cards, $cards));
			$count_cards = count($available_cards)-1;

			for ($i = 1; $i <= $count_cards; $i++){
				$cards[0] = $available_cards[$i];
				for ($j=$i+1; $j<=$count_cards; $j++){
					$cards[1] = $available_cards[$j];
					$other_rank = $this->eval_7hand($cards);
					if ($other_rank > $rank) {
						$rank_below ++;
					}elseif ($other_rank == $rank){
						$rank_equal ++;
					}else {
						$rank_above ++;
					}
				}
			}
		}elseif ( !empty($cards[5]) ){//evaluating 6 cards
			$rank = $this->eval_6hand($cards);
			$available_cards = array_values(array_diff($reserved_cards, $cards));
			$count_cards = count($available_cards)-1;

			for ($i = 1; $i <= $count_cards; $i++){
				$cards[0] = $available_cards[$i];
				for ($j=$i+1; $j<=$count_cards; $j++){
					$cards[1] = $available_cards[$j];
					$other_rank = $this->eval_6hand($cards);
					if ($other_rank > $rank) {
						$rank_below ++;
					}elseif ($other_rank == $rank){
						$rank_equal ++;
					}else {
						$rank_above ++;
					}
				}
			}
		}else {//if only 5 cards selected
			$rank = $this->eval_5hand_fast($cards);
			$available_cards = array_values(array_diff($reserved_cards, $cards));
			$count_cards = count($available_cards)-1;

			for ($i = 1; $i <= $count_cards; $i++){
				$cards[0] = $available_cards[$i];
				for ($j=$i+1; $j<=$count_cards; $j++){
					$cards[1] = $available_cards[$j];
					$other_rank = $this->eval_5hand_fast($cards);
					if ($other_rank > $rank) {
						$rank_below ++;
					}elseif ($other_rank == $rank){
						$rank_equal ++;
					}else {
						$rank_above ++;
					}
				}
			}
		}

		$winning_percentage = round(100 * $rank_below/ ($rank_above + $rank_equal + $rank_below), 2);

		$timeend = microtime(true);

		$hand_type = $this->getHandType($rank);

		$json_format = array(
			'winning_percentage' => $winning_percentage,
			'rank' => $rank,
			'hand_type' => $hand_type,
			'rank_above' => $rank_above,
			'rank_equal' => $rank_equal,
			'rank_below' => $rank_below,
			'time_elapsed' => ($timeend-$timestart)
			);
		
		echo json_encode($json_format);

	}//end of function analyze()

	private function eval_7hand($cards) {
		$rank = 7414;//since it's impossible to get rank 7415-7462 with 7 cards
		for ($a =0; $a<=2; $a++){
			$subcards[0] = $cards[$a];
			for ($b=$a+1;$b<=3; $b++){
				$subcards[1] = $cards[$b];
				for ($c=$b+1;$c<=4; $c++){
					$subcards[2] = $cards[$c];
					for ($d=$c+1;$d<=5; $d++){
						$subcards[3] = $cards[$d];
						for ($e=$d+1;$e<=6; $e++){
							$subcards[4] = $cards[$e];
//							$newrank = $this->eval_5hand_fast($cards[$a], $cards[$b], $cards[$c], $cards[$d], $cards[$e]);
							$newrank = $this->eval_5hand_fast($subcards);
							if ($newrank < $rank) {
								$rank = $newrank;
								if ($rank == 1)
									return $rank;
							}
						}
					}
				}
			}
		}


		return $rank;
	}

	private function eval_6hand($cards) {
		$rank = 7450;//since it's impossible to get rank 7451-7462 with 6 hand
		for ($a =0; $a<=1; $a++){
			$subcards[0] = $cards[$a];
			for ($b=$a+1;$b<=2; $b++){
				$subcards[1] = $cards[$b];
				for ($c=$b+1;$c<=3; $c++){
					$subcards[2] = $cards[$c];
					for ($d=$c+1;$d<=4; $d++){
						$subcards[3] = $cards[$d];
						for ($e=$d+1;$e<=5; $e++){
							$subcards[4] = $cards[$e];
							$newrank = $this->eval_5hand_fast($subcards);
							if ($newrank < $rank) {
								$rank = $newrank;
								if ($rank == 1){
									return $rank;
								}
							}
						}
					}
				}
			}
		}

		return $rank;
	}

	private function eval_5hand_fast($cards) {
//	private function eval_5hand_fast($c1, $c2, $c3, $c4, $c5) {
		$c1 = $cards[0];
		$c2 = $cards[1];
		$c3 = $cards[2];
		$c4 = $cards[3];
		$c5 = $cards[4];
		$q = ($c1 | $c2 | $c3 | $c4 | $c5 ) >> 16;
		
		$isFlush = $c1 & $c2 & $c3 & $c4 & $c5 & 61440;

		if ($isFlush != 0) {
			$rank = $this->arrays->getVar("flushes");
			return $rank[$q];
		}

		//check for straight and high card hands
		$unique5 = $this->arrays->getVar("unique5");
		$s = 0;

		if (($s = $unique5[$q])) return $s;

		//check for the rest
		$u = ($c1 & 255) * ($c2 & 255) * ($c3 & 255) * ($c4 & 255) * ($c5 & 255);

		return $this->binarySearch($u);
		//return $this->findFast($u);

	}

	private function binarySearch ($key){
		$products = $this->arrays->getVar('products');
		$values = $this->arrays->getVar('values');

		$low = 0;
		$high = 4887;//count($products)

		while ($low < $high){
			$mid = ($high + $low) / 2;
			if ($key < $products[$mid]){
				$high = $mid;
			}elseif ($key > $products[$mid]){
				$low = $mid;
			}else {
				return $values[$mid];
			}
		}

		return 0;
	}

	private function findFast ($u) {
		$this->load->helper('biginteger');
		$hash_values = $this->arrays->getVar('hash_values');
		$hash_adjust = $this->arrays->getVar('hash_adjust');

		$u = new Math_BigInteger($u);
		$u = $u->add(new Math_BigInteger('3910838837'));
		
		$u = $u->bitwise_xor($u->bitwise_rightShift(16));
		
		$u = $u->add($u->bitwise_leftShift(8));
		list(, $u) = $u->divide(new Math_BigInteger('4294967296'));
		$u = $u->bitwise_xor($u->bitwise_rightShift(4));
		$b = $u->bitwise_rightShift(8)->bitwise_and(new Math_BigInteger('511'));
		$a = $u->bitwise_leftShift(2)->add($u)->bitwise_rightShift(19);
		$r = $a->bitwise_xor(new Math_BigInteger($hash_adjust[$b->toString()]));

		return ($hash_values[$r->toString()]);
	}


	private function getHandType ($rank = 1){
		if ($rank > 6185) return("High Card");        // 1277 high card
		if ($rank > 3325) return("One Pair");         // 2860 one pair
		if ($rank > 2467) return("Two Pair");         //  858 two pair
		if ($rank > 1609) return("Three of a kind");  //  858 three-kind
		if ($rank > 1599) return("Straight");         //   10 straights
		if ($rank > 322)  return("Flush");            // 1277 flushes
		if ($rank > 166)  return("Full House");       //  156 full house
		if ($rank > 10)   return("Four of a Kind");   //  156 four-kind
		if ($rank > 0)    return("Straight Flush");   //  10 straight flush
		return "Something went wrong";
	}

	private function getHandRank ($rank = 1){
		if ($rank > 6185) return(9);         // 1277 high card
		if ($rank > 3325) return(8);         // 2860 one pair
		if ($rank > 2467) return(7);         //  858 two pair
		if ($rank > 1609) return(6);		 //  858 three-kind
		if ($rank > 1599) return(5);         //   10 straights
		if ($rank > 322)  return(4);         // 1277 flushes
		if ($rank > 166)  return(3);         //  156 full house
		if ($rank > 10)   return(2);         //  156 four-kind
		return 1;
	}


}//end of class

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
