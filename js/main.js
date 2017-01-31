var TILE_SIZE = 60;

//var SERVER_URL = "http://localhost/";
var SERVER_URL = "https://div.vaneesbeeck.me/";

var game = new Phaser.Game(16*TILE_SIZE, 9*TILE_SIZE, Phaser.AUTO, 'blockade',
			   { preload: preload,
			     create: create,
			     update: update ,
			     render: render });

function preload() {
    game.stage.disableVisibilityChange = true;
    
    game.load.image('back_ground', 'img/back_ground.png');
    game.load.image('path', 'img/path.png');
    game.load.image('black', 'img/black.png');
    game.load.image('white', 'img/white.png');

    game.load.audio('wooden_hover', ['assets/wooden_hover.mp3', 'assets/wooden_hover.ogg']);

    game.load.spritesheet('btn', 'img/button_sprite.png', 260, 100);
}

var images = [
    new Array(16), new Array(16), new Array(16), new Array(16),
    new Array(16), new Array(16), new Array(16), new Array(16),
    new Array(16)
];
var GAME_STATE = {
    MENU: 0,
    FIRST_POLL: 1,
    POLL: 2,
    PLAY: 3
};
var current_state = GAME_STATE.MENU;
var game_data = new GameData();
var team;

var button;

var play_url = SERVER_URL+"play.php";
var polling_url = SERVER_URL+"poll.php";
var polling_frequency = 500;
var next_polling = 0;

function create() {
    snd_hover = game.add.audio('wooden_hover', 0.5, false, true);
    game.stage.backgroundColor = '#000';
    draw_board(game_data);
    button = game.add.button(250, 120, 'btn', btnPlayClick, this, 2, 1, 0);
    button.events.onInputOver.add(overSound, this);
}

function overSound() {
    snd_hover.play();
}

function btnPlayClick() {
    button.visible = false;
    startGame();
}

function startGame() {
    current_state = GAME_STATE.FIRST_POLL;
}

function first_polling_callback(polled_game_data) {
    array_polled_data = polled_game_data.split("+");
    console.log('+++++++++++++++++=');
    console.log(array_polled_data[0]);
    console.log('+++++++++++++++++=');
    console.log('Your team: '+array_polled_data[2]);
    console.log('+++++++++++++++++=');
    game_data = JSON.parse(array_polled_data[0]);
    if(array_polled_data.length > 2 && array_polled_data[1] == 'your_team') {
	team = array_polled_data[2];
	current_state = GAME_STATE.POLL;
    }
    else {
	team = game_data.player_team;
	current_state = GAME_STATE.PLAY;
	console.log("current_state = GAME_STATE.PLAY");
    }
    update_board(game_data);
}

function render() {

}

function update() {
    if(current_state == GAME_STATE.FIRST_POLL && next_polling < game.time.now) {
	console.log('FIRST POLL');
	poll(polling_url+'?new=true', first_polling_callback);
	next_polling = game.time.now + polling_frequency;
    }
    else if(current_state == GAME_STATE.POLL && next_polling < game.time.now) {
	if(game_data === "")
	    poll(polling_url+'?new=true', polling_callback);
	else
	    poll(polling_url, polling_callback);
	next_polling = game.time.now + polling_frequency;
    }
    else if(game.input.activePointer.isDown) {
	handle_click(game.input.activePointer.x, game.input.activePointer.y);
    }
}

function handle_click(x, y) {
    if(current_state == GAME_STATE.PLAY) {
	console.log('try to play');
	if(select_pawn(x, y)) {
	    current_state = GAME_STATE.POLL;
	    console.log('play success: current_state = GAME_STATE.POLL');
	}
    }
}

function select_pawn(x, y) {
    for(var i = 0; i < 8; ++i)
	if(game_data.board.pawns[i].team == team) {
	    var pawn = game_data.board.pawns[i];
	    if(click_tile(pawn_x(pawn), pawn_y(pawn), x, y)) {
		play(play_url, pawn.pos);
		return true;
	    }
	}
    return false;
}

function click_tile(tile_x, tile_y, click_x, click_y) {
    return ((click_x > (tile_x * TILE_SIZE)) && (click_x < ((tile_x + 1) * TILE_SIZE))) && ((click_y > (tile_y * TILE_SIZE)) && (click_y < ((tile_y + 1) * TILE_SIZE)));
}

function polling_callback(polled_game_data) {
    polled_game_data = JSON.parse(polled_game_data);
    if(!(JSON.stringify(game_data) === JSON.stringify(polled_game_data))) {
	game_data = polled_game_data;
	update_board(polled_game_data);
	if(polled_game_data.winner_team != null) {
	    game_over(polled_game_data.winner_team);
	}
	else if(polled_game_data.player_team == team) {
	    current_state = GAME_STATE.PLAY;
	}
    }
}

function update_board(game_data) {
    draw_board(game_data);
    display_text(game_data);
}

function display_text(game_data) {
    var style = { font: "bold 20px Arial", fill: "#fff", boundsAlignH: "center", boundsAlignV: "middle" };
    if(game_data.player_team == TEAM.WHITE)
	turn_text = game.add.text(20, 60, 'It\'s white\'s turn', style);
    else
	turn_text = game.add.text(20, 60, 'It\'s black\'s turn', style);
    if(team == TEAM.WHITE)
	team_text = game.add.text(20, 79, 'Your team is white', style);
    else
	team_text = game.add.text(20, 79, 'Your team is black', style);
    sortition_text = game.add.text(20, 98, 'Player can move forward '+game_data.sortition+' spaces', style);
}

function game_over(winner) {
    if(winner == team) {
	console.log("+++ GAME OVER, YOU WIN");
	win();
    }
    else {
	console.log("+++ GAME OVER, YOU LOOSE");
	loose();
    }
}

function draw_board(game_data) {
    for(var y = 0; y < 9; y+=2)
	for(var x = 0; x < 16; ++x)
	    draw_tile(x, y, 'path');
    for(var y = 1; y < 8; y+=2)
	for(var x = 0; x < 16; ++x)
	    draw_tile(x, y, 'back_ground');
    draw_tile(15, 1, 'path');
    draw_tile(15, 5, 'path');
    draw_tile(0, 3, 'path');
    draw_tile(0, 7, 'path');
    
    for(var i = 0; i < 8; ++i)
	draw_pawn(game_data.board.pawns[i]);
}

function draw_pawn(pawn) {
    draw_tile(pawn_x(pawn), pawn_y(pawn), pawn_tile(pawn));
}

function pawn_x(pawn) {
    if(pawn.pos < 16)
	return pawn.pos;
    if(pawn.pos > 16 && pawn.pos < 33)
	return 16 - (pawn.pos - 16);
    if(pawn.pos > 33 && pawn.pos < 50)
	return pawn.pos - 34;
    if(pawn.pos > 50 && pawn.pos < 67)
	return 16 - (pawn.pos - 50);
    if(pawn.pos > 67)
	return pawn.pos - 68;
    if(pawn.pos == 16 || pawn.pos == 50)
	return 15;
    if(pawn.pos == 33 || pawn.pos == 67)
	return 0;
}

function pawn_y(pawn) {
    if(pawn.pos < 16)
	return 0;
    if(pawn.pos > 16 && pawn.pos < 33)
	return 2;
    if(pawn.pos > 33 && pawn.pos < 50)
	return 4;
    if(pawn.pos > 50 && pawn.pos < 67)
	return 6;
    if(pawn.pos > 67)
	return 8;
    if(pawn.pos == 16)
        return 1;
    if(pawn.pos == 33)
	return 3;
    if(pawn.pos == 50)
        return 5;
    if(pawn.pos == 67)
	return 7;
}

function pawn_tile(pawn) {
    if(pawn.team == TEAM.WHITE)
	return 'white';
    if(pawn.team == TEAM.BLACK)
	return 'black';
    return null;
}

function draw_tile(x, y, type) {
    if(images[y][x] != null)
	images[y][x].destroy();
    images[y][x] = game.add.sprite(x*TILE_SIZE, y*TILE_SIZE, type);
}

function poll(url, callback) {
    var xmlhttp = new XMLHttpRequest();
    
    xmlhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200) {
	    console.log('poll response txt-|'+this.responseText+'|-');
	    if(this.responseText != '') {
	        var game_data = this.responseText;
		callback(game_data);
	    }
	}
    };
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}

function play(url, pos) {
    var xmlhttp = new XMLHttpRequest();
    /*xmlhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200) {
	    console.log('play response txt-|'+this.responseText+'|-');
	}
    };*/
    xmlhttp.open("GET", url+'?pos='+pos, true);
    xmlhttp.send(null);
    console.log('+++++++++++++');
    console.log('SEND MOVE: '+pos);
    console.log('+++++++++++++');
}

function win() {
    reset_game();
    popup_text('You win');
}

function loose() {
    reset_game();
    popup_text('You loose');
}

function reset_game() {
    var current_state = GAME_STATE.MENU;
    var game_data = new GameData();
    var team;
    create();
}

function popup_text(text) {
    var style = { font: "bold 20px Arial", fill: "#fff", boundsAlignH: "center", boundsAlignV: "middle" };
    popup_text = game.add.text(300, 60, text, style);
    
    game.time.events.add(5000, function() {
	game.add.tween(popup_text).to(
	    {y: 0}, 1500, Phaser.Easing.Linear.None, true);
	game.add.tween(popup_text).to(
	    {alpha: 0}, 1500, Phaser.Easing.Linear.None, true);
    }, this);
}
