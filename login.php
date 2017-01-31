<?php
    include('Dao.php');

    session_start();

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        if(auth($_POST['user'], $_POST['password'])) {
            header('Location: game.php');
    	}
    }
    include("login.html");

    function auth($user, $password) {
    	$hash = Dao::getPassHash($user);
        if($hash && password_verify($password, $hash)) {
            if(password_needs_rehash($hash, PASSWORD_BCRYPT)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                Dao::updatePassHash($user, $passHash);
            }
            $_SESSION['user'] = $user;
            return true;
        }
        else {
            return false;
        }
    }


?>