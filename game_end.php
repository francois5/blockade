<?php
    include_once('Dao.php');

    session_start();

    if(isset($_SESSION['user']) && $_SERVER['REQUEST_METHOD'] == "GET") {
        $game_id = Dao::get_game_id($_SESSION['user']);
        if($game_id)
            Dao::game_end($game_id);
        include("game.php");
    }
?>
