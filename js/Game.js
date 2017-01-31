var GameData = function() {

//    var GameData = function() {
	var Board = function() {
	    this.pawns = [new Pawn(TEAM.WHITE, 0), new Pawn(TEAM.BLACK, 1),
			  new Pawn(TEAM.WHITE, 2), new Pawn(TEAM.BLACK, 3),
			  new Pawn(TEAM.WHITE, 4), new Pawn(TEAM.BLACK, 5),
			  new Pawn(TEAM.WHITE, 6), new Pawn(TEAM.BLACK, 7)];
	};
	
	var Move = function(pos, move) {
	    this.pos = pos;
	    this.move = move;
	};
	
	var Pawn = function(team, pos) {
	    this.team = team;
	    this.pos = pos;
	};
	
	this.board = new Board();
	this.moves = new Array();
	this.sortition = null;
	this.player_team = TEAM.WHITE;
	this.winner_team = null;
	this.begin = null;
	this.end = null;	
  //  }

    //this.game_data;// = new GameData();
    /*
    this.play = function(pos) { // return true if pawn moved
	this.game_data.moves.push(new Move(pos, this.game_data.sortition));
	var target_pos = pos + this.game_data.sortition;
	if(!this.blockade(target_pos)) {
	    var moving_pawn = this.get_pawn(pos);
	    var target_pawn = this.get_pawn(target_pos);
	    if(target_pawn != null && this.player_enemy(target_pawn)) { // pawn take enemy
		target_pawn.pos = pos;
		moving_pawn.pos = target_pos;
		return true;
	    } else if(target_pawn == null) { // pawn move to tile
		moving_pawn.pos = target_pos;
		return true;
	    }
	}
	return false;
    }

    this.blockade = function(target_pos) {
	var target = this.get_pawn(target_pos);
	if(target != null && target.team != this.game_data.player_team) { // there is an enemy on target tile
	    var target_plus1 = this.get_pawn(target_pos + 1);
	    var target_min1  = this.get_pawn(target_pos - 1);
	    if( (target_plus1 != null && this.player_enemy(target_plus1))
		|| (target_min1 != null && this.player_enemy(target_min1)) )
		return true;
	}
	return false;
    }

    this.get_pawn = function(pos) {
	for(var i = 0; i < 8; ++i)
	    if(this.game_data.board.pawns[i].pos == pos)
		return this.game_data.board.pawns[i];
	return null;
    }

    this.player_enemy = function(pawn) {
	return pawn.team != this.game_data.player_team;
    }
*/
}
