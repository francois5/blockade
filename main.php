<?php
    session_start();

    if(isset($_SESSION['user']))
	header('Location: game.php');
    else
        header('Location: login.php');
?>
