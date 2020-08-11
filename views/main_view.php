<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>MpokErot : Yet Another Poker Hand Evaluator</title>
<script type="text/javascript" src="mpokerot/views/scripts/jquery-1.4.min.js">
</script>
<script type="text/javascript" >
	$(document).ready(function () {
		//i'm a little frustrated here
		$('#result img').hide();

		var cardPosition = 1;
		var lastClickedCard =0;
		var selectedCards = new Array(7);
		for (var j=0; j<7; j++){
			selectedCards[j] = 0;
		}

		$('.card-image').click(function () {
			if ($(this).attr('alt') == "unclicked_card"){
				if (cardPosition <= 7){
					lastClickedCard = parseInt($(this).attr('id').substring(11,13));

					selectedCards[(cardPosition -1)] = lastClickedCard;

					//set preflop or community card to lastClickedDeck and set clicked deck to unseen
					$('#c' + cardPosition).attr({
						src : "mpokerot/images/" + lastClickedCard + ".png"
					});
					cardPosition++;
					$(this).attr({
						src: "mpokerot/images/unseen.gif",
						alt: "clicked_card"
					})
				}

			}else if($(this).attr('alt') == "clicked_card") {
				if (cardPosition > 1 && lastClickedCard == parseInt($(this).attr('id').substring(11,13))){
					$(this).attr({
						src: "mpokerot/images/"+  lastClickedCard + ".png",
						alt: "unclicked_card"
					})

					cardPosition --;

					selectedCards[(cardPosition - 1)] = 0;

					if (cardPosition == 1){
						lastClickedCard = 0;
					}else {
						//lastClickedCard = parseInt($('#c' + (cardPosition -1)).attr('src').substring(16,18));
						lastClickedCard = selectedCards[(cardPosition -2)];
					}

					//set the last preflop or community cards to be unseen
					$('#c' + cardPosition).attr({
						src : "mpokerot/images/unseen.gif"
					});
				}
				
			}
		})//end of (.card-image).onclick()

		$('#clearButton').click(function () {
			if (cardPosition > 1){//why bother if there is no clicked card?
				lastClickedCard = 0;
				cardPosition = 1;
				$('#preflop img').attr('src', 'mpokerot/images/unseen.gif');
				$('#community-card img').attr('src', 'mpokerot/images/unseen.gif');

				for (var i=0; i< 7; i++){

					//set all deck to its original images
					if (selectedCards[i]){
						$('#card-image-' + selectedCards[i]).attr({
							src: 'mpokerot/images/' + selectedCards[i] + '.png',
							alt: 'unclicked_card'
						})
						selectedCards[i] = 0;
					}else {
						break;//stop until the last selectedCards
					}

				}
			}
		})//end of ('#clearButton').click()

		$('#undoButton').click( function () {
			if (cardPosition > 1){
				cardPosition --;

				var scIndex = cardPosition -1;
				$('#c' + cardPosition).attr({
					src : "mpokerot/images/unseen.gif"
				});

				$('#card-image-' + selectedCards[scIndex]).attr({
					src: 'mpokerot/images/' + selectedCards[scIndex] + '.png',
					alt: 'unclicked_card'
				})

				selectedCards[scIndex] = 0;
				lastClickedCard = selectedCards[(scIndex-1)];
			}
			
		})//end of ('#undoButton').click()

		$('#loadingAnimation')
				.bind('ajaxStart', function () {
					$('#response').hide();
					$(this).show();
				})
				.bind('ajaxStop', function () {
					$(this).hide();
					$('#response').show();
				})


		$('#goButton').click(function () {
			if ( cardPosition <=5 ){
				alert('You must provide at least 3 community cards');
			}else {
				$.ajax({
					type: 'GET',
					url: 'mpokerot.php/main/analyze/' +
						selectedCards[0] + "/" +
						selectedCards[1] + "/" +
						selectedCards[2] + "/" +
						selectedCards[3] + "/" +
						selectedCards[4] + "/" +
						selectedCards[5] + "/" +
						selectedCards[6] + "/",
					dataType: 'json',
					success: function (json) {
						$('#winning-percentage').html(json.winning_percentage);
						$('#hand-rank').html(json.rank);
						$('#hand-type').html(json.hand_type),
						$('#rank-above').html(json.rank_above);
						$('#rank-equal').html(json.rank_equal);
						$('#rank-below').html(json.rank_below);
						$('#time-elapsed').html(json.time_elapsed);
					},
					error: function (xhr, status, errorThrown) {
						alert ('Oops, an error occured: ' + xhr + "|"+ status+ "|" + errorThrown)
					},
					timeout: 20000 // 20 seconds
				})//end of .ajax()
			}
			
		})//end of ('#goButton').click()


		//creating popup for About and Help menu
		var popupShown = false;
		var windowWidth = $('body').width();
		var windowHeight = $('body').height();
		$('#popup').css({
			"left" : (windowWidth/2 - $('#popup').width()/2),
			"top" : (windowHeight/2 - $('#popup').height()/2)
		})

		function loadPopup() {
			if (!popupShown){
				$('#popup-background').fadeIn();
				$('#popup').fadeIn();
				popupShown = true;
			}
		}

		function unloadPopup() {
			if (popupShown){
				$('#popup-background').fadeOut();
				$('#popup').fadeOut();
				popupShown = false;
			}
		}


		$('#help').click(function () {
			loadPopup();
		})

		$('#popup-close').click(function () {
			unloadPopup();
		});

		$('#popup-background').click(function () {
			unloadPopup();
		});

		$(document).keypress(function (e){
			if (e.keyCode == 27 && popupShown){
				unloadPopup();
			}
		})

	})//end of (document).ready()
</script>
<?=$this->load->view('styles/styles-general')?>
</head>
<body>

<h1>Mpok Erot</h1>
<div class="ads" >
Ads goes here......
<a style="float:right;padding-right: 8px" href="#" id="help">Help</a>
</div>
<div id="wrapper">
	<div id="content">
		<div id="preflop">
			<img src="mpokerot/images/unseen.gif" alt="preflop" id="c1" width="54px"/>
			<img src="mpokerot/images/unseen.gif" alt="preflop" id="c2" width="54px"/>
		</div>
		<div id="community-card">
			<img src="mpokerot/images/unseen.gif" alt="community cards" id="c3" width="54px" />
			<img src="mpokerot/images/unseen.gif" alt="community cards" id="c4" width="54px" />
			<img src="mpokerot/images/unseen.gif" alt="community cards" id="c5" width="54px" />
			<img src="mpokerot/images/unseen.gif" alt="community cards" id="c6" width="54px" />
			<img src="mpokerot/images/unseen.gif" alt="community cards" id="c7" width="54px" />
		</div>
		<div id="result">
			<div class="buttons" >
				<input type="button" name="undo" id="undoButton" value="undo" />
				<input type="button" name="clear" id="clearButton" value="clear" />
				<input type="button" name="go" id="goButton" value="analyze" />
			</div>
			<img src="mpokerot/images/loadingAnimation.gif" id="loadingAnimation" alt="animation" />
			<div id="response">
				Winning percentage: <span id="winning-percentage">NA</span> %<br />
				Rank : <span id="hand-rank">NA</span> / <span id="hand-type">NA</span><br />
				W/D/L : <span id="rank-above">NA</span>/<span id="rank-equal">NA</span>/<span id="rank-below">NA</span><br />
				Time : <span id="time-elapsed">NA</span>
			</div>
		</div>
	</div>
	<div id="deck" >
<?php
	for ($i = 0; $i<= 3;$i++):
		echo "<div>";
		for ($j = 1; $j <= 52; $j+=4):
			$index = $j+$i;
		?>
		<img src="mpokerot/images/<?=$index?>.png" alt="unclicked_card" class="card-image" id="card-image-<?=$index?>"/>
		<?php endfor;
		echo "</div>";
		?>
	<?php endfor;?><br />
	</div><!-- end of deck -->
	<div class="footer" >
		<?=anchor("https://www.prabowomurti.com", "Prabowo Murti")?> thanks to Allah SWT,
		<?=anchor("http://codingthewheel.com", "Ctw")?>,
		<?=anchor("http://www.suffecool.net/", "KS")?>,
		<?=anchor("http://senzee.blogspot.com/", "PS")?>,
		<?=anchor("http://herlangga.web.id", "ATH")?>,
		<?=anchor("http://ajaxload.info", "AL")?>,
		<?=anchor("http://codeigniter.com", "CI")?>,
		<?=anchor("http://jquery.com", "jQ")?>,
		<?=anchor("http://google.com", "Google")?>
	</div>
</div>
<div id="popup">
	<a id="popup-close" href="#" title="close popup">X</a>
	<div>
		<h4>How to Use</h4>
		Input preflop cards and at least 3 community cards, then click "analyze" button. Click "clear" button to start from the beginning.
		Click "undo" button or the last selected card to cancel the card you've already chosen.<br />

		<h4>Given Informations</h4>
		Winning percentage, rank (and hand type), other card rank (Win/Draw/Lose) against your hand, and elapsed time.<br />

		<h4>Example</h4>
		Your preflop cards are KH and 6H, community cards: QS, KD, QD, 8C, 3H. Your rank 2604/ Two Pair. It loses
		against 132 hands, draws against 50 hands, and wins against 808 hands. Your winning percentage = 100% * 808/990 = 81.62%.<br />

		<h4>About</h4>
		Mpok Erot is yet another poker hand evaluator. All credit goes to its original owner. Feel free to contact me
		by email : prabowo.murti AT gmail DOT com. I promise I will reply it soon.<br />
		Thank you, and enjoy.. :)
	</div>
</div>
<div id="popup-background"></div>

</body>
</html>
