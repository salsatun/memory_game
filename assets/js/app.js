/* ==========================================================================
 14.heartbeat
 ========================================================================== */ 
var heartId = 0;
 var autoCountDown = true;
 var autoCountUp = true;
 
function initHeartBeating()	
{
	heartId = setInterval( "heartbeat()", 1000 );
} 
function stopHeartBeating()
{
	clearInterval( heartId );
}
function heartbeat()	
{
	if ( autoCountDown == true ) {
		counter(".auto-countdown", -1);
	}
	if ( autoCountDown == true ) {
		counter(".auto-countUp", 1);
	}	
} 

/* ==========================================================================
 15.counter
 ========================================================================== */ 
function counter(counterIndex, step) {
 $(counterIndex).each(
	function(index, element) {
		var now = Math.floor(Date.now() / 1000);
		var current = $(this).attr('current');
		var total = $(this).attr('total');
		if(current!=parseFloat(current)) {
			current=0;
			return;
		} 
		if(total!=parseFloat(total)) {
			total=0;
			return;
		} 
		
		var change_time = $(this).attr('change-time');
		var change_function = $(this).attr('change-function');
		if(change_function)
		{
			if(change_time == current)
			eval(change_function);
		}
		
		current=parseFloat(current);
		total=parseFloat(total);
		step=parseFloat(step);
		current = current+step;
		
		if(total-current>=0 && current>=0) {
			var percent=(current*100)/total;
			var days = Math.floor(current / (3600 * 24));
			var hours = Math.floor((current - (days * (3600 * 24)))/3600);
			var minutes = Math.floor((current - (days * (3600 * 24)) - (hours * 3600)) / 60);
			var seconds = Math.floor(current - (days * (3600 * 24)) - (hours * 3600) - (minutes * 60));
			if (days < 10) {
				days = "00"+days;
			}
			else
			if (days < 100) {
				days = "0"+days;
			}
			if (hours < 10) {
				hours = "0"+hours;
			}
			if (minutes < 10) {
				minutes = "0"+minutes;
			}
			if (seconds < 10) {
				seconds = "0"+seconds;
			} 
			$(this).find( ".mask" ).css("width",percent+"%");
			$(this).find( ".days" ).text(days);
			$(this).find( ".hours" ).text(hours);
			$(this).find( ".minutes" ).text(minutes);
			$(this).find( ".seconds" ).text(seconds);
			$(this).attr('current',current);
		}	
	});
}
function shuffle(array) {
    let counter = array.length;

    // While there are elements in the array
    while (counter > 0) {
        // Pick a random index
        let index = Math.floor(Math.random() * counter);

        // Decrease counter by 1
        counter--;

        // And swap the last element with it
        let temp = array[counter];
        array[counter] = array[index];
        array[index] = temp;
    }

    return array;
}

function _start_game_timer()	
{
	let cards_full_array=[];
	let cards_length=28;
	for(let i=0;i<18;i++)
	{
		cards_full_array[i]=i+1;
	}
	//14 *2=28
	cards_full_array=shuffle(cards_full_array);
	
	let cards_game=[];
	for(let i=0,j=0;i<cards_length/2;i++)
	{
		cards_game[j++]=cards_full_array[i];
		cards_game[j++]=cards_full_array[i];
	}
	cards_game=shuffle(cards_game);
	let game_board_cards="";
	for(let i=0;i<cards_length;i++)
	{
		game_board_cards+='<article class="" card-number="'+(cards_game[i])+'"><div id="card'+(i+1)+'" class="card"><div class="card_back"></div><div class="card_front" style="background-image:url(assets/images/cards/'+(cards_game[i])+'.jpg);"></div></div></article>';
	}
	$('.game-content #game-board').html(game_board_cards);
	
	$( ".game-content #game-board > article > .card" ).click(function(event) {
		if (number_open > 1) {
			return
		  }
		  if (number_open == 0) {
			current_card_1=$(this).attr("id");
		  }
		  if (number_open == 1) {
			current_card_2=$(this).attr("id");
			chk_cards_timeout = setTimeout(checkCards, delay);
		  }
		  if (!$(this).parent().hasClass('active')) {
			number_open++;
			$(this).parent().addClass('active');
		  }
	});

	$('body .game-content').addClass('loaded');
	$('.game-header .top-menu .timer').addClass('auto-countUp');
	$('.game-footer .game-progressBar .timer').addClass('auto-countUp');
	initHeartBeating();
} 

function _stop_game_timer()	
{
	
	$(".game-content #game-board").html("");
	

	$('body .game-content').removeClass('loaded');
	$('.game-header .top-menu .timer').removeClass('auto-countUp').attr("current","0");
	$('.game-footer .game-progressBar .timer').removeClass('auto-countUp').attr("current","0");
	$('.game-header .top-menu .timer .days,'+
	'.game-header .top-menu .timer .hours,'+
	'.game-header .top-menu .timer .minutes,'+
	'.game-header .top-menu .timer .seconds,'+
	'.game-footer .game-progressBar .timer .days,'+
	'.game-footer .game-progressBar .timer  .hours,'+
	'.game-footer .game-progressBar .timer .minutes,'+
	'.game-footer .game-progressBar .timer .seconds').html('--');
	$('.game-header .top-menu .timer .fill .mask,'+
	'.game-footer .game-progressBar .timer .fill .mask').css("width","0%");
	stopHeartBeating();
}
function _end_game_timer()	
{
	$('.game-header .top-menu a.play-stop-button').addClass('disabled');
	stopHeartBeating();
	///ajax send score
		var formData = new FormData();
		formData.append("new-score-duration", $(".game-header .top-menu > li .timer").attr("current"));
		$.ajax({
				url:"",
				type: "POST",
				dataType: 'json',
				async:true,
				crossDomain: false, 
				//xhrFields: {'withCredentials': true},
				data: formData,
				processData: false,  // tell jQuery not to process the data
				contentType: false,  // tell jQuery not to set contentType
				//contentType: 'application/json',
				success: function (result) {
					console.log(result);
					$('.game-header .top-menu a.play-stop-button').removeClass('disabled');
					$('.game-footer .bottom-menu > li.score-1 .badge').html(result.formatted_duration_1);
					$('.game-footer .bottom-menu > li.score-2 .badge').html(result.formatted_duration_2);
					$('.game-footer .bottom-menu > li.score-3 .badge').html(result.formatted_duration_3);
					
					$('.game-header .top-menu > li .timer').attr('total',result.duration_3);
					$('.game-footer .game-progressBar .timer').attr('total',result.formatted_duration_3);
					$('.game-footer .game-progressBar .timer').attr('total',result.formatted_duration_3);
					alert(result.message);
				},
				error: function (result) {
					console.log(result);
					$('.game-header .top-menu a.play-stop-button').removeClass('disabled');
				}
			});
} 

function _block_game_timer()	
{
	$('.game-header .top-menu a.play-stop-button').removeClass('disabled');
	stopHeartBeating();
	$('.game-content #game-board > article > .card').addClass('disabled');
	alert("Vous avez perdu!");
} 

function checkCards()	
{
	let current_parent_1 = $('.game-content #game-board #'+current_card_1).parent();
	let current_parent_2 = $('.game-content #game-board #'+current_card_2).parent();
	
	if(current_parent_1.attr('card-number') != current_parent_2.attr('card-number'))
	{
		current_parent_1.removeClass('active');
		current_parent_2.removeClass('active');
	}
	else
	{
		if(!$(".game-content.loaded #game-board > article:not(.active)").length)
		_end_game_timer();
	}
	
	number_open = 0;
	current_card_1 = null;
	current_card_2 = null;

	chk_cards_timeout = null;
} 
/* ==========================================================================
   >>.ready
   ========================================================================== */
   
var delay = 1000;
var number_open = 0;
var current_card_1 = null;
var current_card_2 = null;

var chk_cards_timeout = null;
     $().ready(function() {
		
		$('body').addClass('loaded');
	 });
	