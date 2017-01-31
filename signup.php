<?php
    include_once('Dao.php');

    function clog( $data ){
    	echo '<script>';
    	echo 'console.log("'. $data .'")';
      	echo '</script>';
    }
    
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        clog("signup POST1");
        if(validPassword($_POST['password1'], $_POST['password2'])
	    && validUser($_POST['user'])) {
	    clog("signup POST2");
	    $pass_hash = password_hash($_POST['password1'], PASSWORD_BCRYPT);
	    Dao::insertUser($_POST['user'], $pass_hash);
	    clog("signup POST3");
	    header('Location: login.php');
        }
    }
    include("signup.html");
    
    function validUser($user) {
        if(Dao::userExists($user) || $user == '')
            return false;
	return true;
    }
    
    function validPassword($pass1, $pass2) {
        return (($pass1 == $pass2) && strong($pass1));
    }

    function strong($pass) {
        return true;
    }
?>
