<?php
    include_once('Dao.php');
    include_once('Game.php');

    session_start();

    if(isset($_SESSION['user']) && $_SERVER['REQUEST_METHOD'] == "GET") {
        $json_game_data = Dao::get_game_data($_SESSION['user']);
        if($json_game_data) {
            echo $json_game_data;
        }
        else {
            $game_id = Dao::get_wait_player();
            if($game_id != '') {
                Dao::set_wait_player('');
                Dao::add_player_to_game($_SESSION['user'], $game_id);
                $json_game_data = Dao::get_game_data($_SESSION['user']);
                $additionnal_data = '';
                $game_data = json_decode($json_game_data);
                if(count($game_data->moves) == 0) {
                    if($game_data->player_team == TEAM::WHITE)
                        $additionnal_data = '+your_team+'.TEAM::BLACK.'+';
                    else
                        $additionnal_data = '+your_team+'.TEAM::WHITE.'+';
                }
                echo $json_game_data.$additionnal_data;
            }
            else if(isset($_GET['new'])) {
                $game_id = Dao::insertGame($_SESSION['user']);
                Dao::set_wait_player($game_id);
                $json_game_data = Dao::get_game_data($_SESSION['user']);
                echo $json_game_data;
            }
            else {
                $json_game_data = Dao::get_last_game_data($_SESSION['user']);
                echo $json_game_data;
            }
        }
    }

?>
