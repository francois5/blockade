<?php

    session_start();

    if(isset($_SESSION['user'])) {
        include('game.html');
    }
    else
	header('Location: login.php');
?>
