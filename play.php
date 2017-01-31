<?php
    include_once('Dao.php');
    include_once('Game.php');

    session_start();

    if(isset($_SESSION['user']) && $_SERVER['REQUEST_METHOD'] == "GET") {
        $json_game_data = Dao::get_game_data($_SESSION['user']);
        if($json_game_data) {
            $game_data = json_decode($json_game_data);
            $game = new Game;
            $game->game_data = $game_data;
            $team = Dao::get_team($_SESSION['user']);
            if($game->game_data->player_team == $team) {
                $game->play($_GET['pos']);
                $game_id = Dao::get_game_id($_SESSION['user']);
                $game->new_sortition();
                $game->switch_team();
                Dao::update_game_data($game_id, json_encode($game->game_data));
                if($game->game_data->end != null) {
                    Dao::game_end($game_id);
                }
            }
        }
    }
?>
