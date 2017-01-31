<?php

abstract class TEAM {
    const WHITE = 0;
    const BLACK = 1;
}

class Board {
    public $pawns;

    public function __construct() {
        $pawns = array(new Pawn(TEAM::WHITE, 0), new Pawn(TEAM::BLACK, 1),
                       new Pawn(TEAM::WHITE, 2), new Pawn(TEAM::BLACK, 3),
                       new Pawn(TEAM::WHITE, 4), new Pawn(TEAM::BLACK, 5),
                       new Pawn(TEAM::WHITE, 6), new Pawn(TEAM::BLACK, 7));
    }
}

class Move {
    public $pos;
    public $move;
    public function __construct($pos, $move) {
        $this->pos = $pos;
        $this->move = $move;
    }
}

class Pawn {
    public $team;
    public $pos;
    public function __construct($team, $pos) {
        $this->team = $team;
        $this->pos = $pos;
    }
}

class GameData {
    public $board;
	public $moves = array();
	public $sortition = null;
    public $player_team = TEAM::WHITE;
	public $winner_team = null;
	public $begin;
	public $end = null;

    public function __construct() {
        $begin = time();
        $board = new Board;
    }
}

class Game {

    public $game_data;

    public function play($pos) {
        file_put_contents('debug.log', 'enter play');
        array_push($this->game_data->moves, new Move($pos, $this->game_data->sortition));
	    $target_pos = $pos + $this->game_data->sortition;
        if(!$this->blockade($target_pos)) {
            file_put_contents('debug.log', 'no blockade', FILE_APPEND);
	        $moving_pawn = $this->get_pawn($pos);
	        $target_pawn = $this->get_pawn($target_pos);
	        if($target_pawn != null && $this->player_enemy($target_pawn)) {
                file_put_contents('debug.log', 'target is enemy', FILE_APPEND);
                $msg = 'SERVER PLAY: $target_pawn->pos: '.$target_pawn->pos;
                file_put_contents('debug.log', $msg, FILE_APPEND);
                $msg = 'SERVER PLAY: $moving_pawn->pos: '.$moving_pawn->pos;
                file_put_contents('debug.log', $msg, FILE_APPEND);
		        $target_pawn->pos = $pos;
		        $moving_pawn->pos = $target_pos;
                $msg = 'SERVER PLAY: $target_pawn->pos: '.$target_pawn->pos;
                file_put_contents('debug.log', $msg, FILE_APPEND);
                $msg = 'SERVER PLAY: $moving_pawn->pos: '.$moving_pawn->pos;
                file_put_contents('debug.log', $msg, FILE_APPEND);
                $this->check_game_over($this->game_data->board);
		        return true;
            } else if($target_pawn == null) {
                file_put_contents('debug.log', 'target is void', FILE_APPEND);
                if($target_pos > 83)
                    $moving_pawn->pos = 100;
                else
                    $moving_pawn->pos = $target_pos;
                $this->check_game_over($this->game_data->board);
		        return true;
	        }
	    }
	    return false;
    }

    public function check_game_over($board) {
        $first_white = $this->first_pawns_pos($board, TEAM::WHITE);
        $first_black = $this->first_pawns_pos($board, TEAM::BLACK);
        if($first_white > 83) {
            $this->game_data->winner_team = TEAM::WHITE;
            $this->game_data->end = time();
        }
        else if($first_black > 83) {
            $this->game_data->winner_team = TEAM::BLACK;
            $this->game_data->end = time();
        }
    }

    public function first_pawns_pos($board, $team) {
        $first_pos = 1000;
        for($i = 0; $i < 8; ++$i)
            if($board->pawns[$i]->team == $team)
                if($board->pawns[$i]->pos < $first_pos)
                    $first_pos = $board->pawns[$i]->pos;
        return $first_pos;
    }

    public function blockade($target_pos) {
        $target = $this->get_pawn($target_pos);
	    if($target != null && $target->team != $this->game_data->player_team) {
	        $target_plus1 = $this->get_pawn($target_pos + 1);
	        $target_min1  = $this->get_pawn($target_pos - 1);
	        if( ($target_plus1 != null && $this->player_enemy($target_plus1))
		    || ($target_min1 != null && $this->player_enemy($target_min1)) )
		        return true;
        }
	    return false;
    }

    public function get_pawn($pos) {
        for($i = 0; $i < 8; ++$i)
	        if($this->game_data->board->pawns[$i]->pos == $pos)
		        return $this->game_data->board->pawns[$i];
	    return null;
    }

    public function player_enemy($pawn) {
	    return $pawn->team != $this->game_data->player_team;
    }

    public function switch_team() {
        if($this->game_data->player_team == TEAM::WHITE)
            $this->game_data->player_team = TEAM::BLACK;
        else
            $this->game_data->player_team = TEAM::WHITE;
    }

    public function new_sortition() {
        $this->game_data->sortition = rand(1, 4);
    }
}
?>