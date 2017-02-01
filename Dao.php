<?php

include_once('Game.php');

class Dao {
    private static $sqlite;

    private static function connectDB() {
        self::$sqlite = new SQLite3("db/blockade.db");
        self::$sqlite->busyTimeout(60000);
    }

    private static function closeDB() {
        self::$sqlite->close();
    }
    
    public static function getPassHash($login) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "SELECT pass_hash
             FROM users
	         WHERE login = :login");
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $result = $stmt->execute();
        $data = $result->fetchArray();
        self::closeDB();
        if($data) {
            return $data[0];
        }
        return false;
    }

    public static function getId($login) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "SELECT id
             FROM users
	         WHERE login = :login");
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $result = $stmt->execute();
        $data = $result->fetchArray();
        self::closeDB();
        if($data) {
            return $data[0];
        }
        return false;
    }

    public static function updatePassHash($login, $pass_hash) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "UPDATE users 
	         SET pass_hash = :pass_hash
             WHERE login = :login");
        $stmt->bindValue(":pass_hash", $pass_hash, SQLITE3_TEXT);
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $stmt->execute();
        self::closeDB();
    }

    public static function insertUser($login, $pass_hash) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "INSERT INTO users (login, pass_hash)
	         VALUES (:login, :pass_hash)");
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $stmt->bindValue(":pass_hash", $pass_hash, SQLITE3_TEXT);
        $stmt->execute();
        self::closeDB();
    }
    
    public static function insertGame($login) {
        $user_id = self::getId($login);
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            'INSERT INTO games (json, white)
	         VALUES (\'{"board":{"pawns":[{"team":0,"pos":0},{"team":1,"pos":1},{"team":0,"pos":2},{"team":1,"pos":3},{"team":0,"pos":4},{"team":1,"pos":5},{"team":0,"pos":6},{"team":1,"pos":7}]},"moves":[],"sortition":'.rand(1, 4).',"player_team":0,"winner_team":null,"begin":'.time().',"end":null}\', :user_id)');
        $stmt->bindValue(":user_id", $user_id, SQLITE3_INTEGER);
        $stmt->execute();
        $id = self::$sqlite->lastInsertRowID();
        self::closeDB();
        return $id;
    }

    public static function update_game_data($game_id, $json) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            'UPDATE games SET json = :json 
             WHERE id = '.$game_id.';');
        $stmt->bindValue(":json", $json, SQLITE3_TEXT);
        $stmt->execute();
        self::closeDB();
    }

    public static function game_end($game_id) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            'UPDATE games SET end = '.time().' 
             WHERE id = '.$game_id.';');
        $stmt->execute();
        self::closeDB();
    }

    public static function add_player_to_game($login, $game_id) {
        $user_id = self::getId($login);
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            'UPDATE games SET black = '.$user_id.' 
             WHERE id = '.$game_id.';');
        $stmt->execute();
        self::closeDB();
    }
    
    public static function userExists($login) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "SELECT * FROM users WHERE login = :login");
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $result = $stmt->execute();
        $data = $result->fetchArray();
        self::closeDB();
        if($data && count($data) == 1)
            return true;
        return false;
    }

    public static function get_game_data($login) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "SELECT json 
             FROM games 
             JOIN users ON users.id = games.white 
                        OR users.id = games.black 
	         WHERE users.login = :login 
             AND games.end IS NULL");
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $result = $stmt->execute();
        $data = $result->fetchArray();
        self::closeDB();
        if($data) {
            return $data[0];
        }
        return false;
    }

    public static function get_last_game_data($login) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "SELECT json 
             FROM games 
             JOIN users ON users.id = games.white 
                        OR users.id = games.black 
	         WHERE users.login = :login 
             ORDER BY games.end DESC");
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $result = $stmt->execute();
        $data = $result->fetchArray();
        self::closeDB();
        if($data) {
            return $data[0];
        }
        return false;
    }

    public static function get_game_id($login) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "SELECT *
             FROM games 
             JOIN users ON users.id = games.white
                        OR users.id = games.black
	         WHERE users.login = :login 
             AND games.end IS NULL");
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $result = $stmt->execute();
        $data = $result->fetchArray();
        self::closeDB();
        if($data) {
            return $data[0];
        }
        return false;
    }

    public static function get_team($login) {
        self::connectDB();
        $stmt = self::$sqlite->prepare(
            "SELECT *
             FROM users
             JOIN games ON users.id = games.white
	         WHERE users.login = :login
             AND games.end IS NULL");
        $stmt->bindValue(":login", $login, SQLITE3_TEXT);
        $result = $stmt->execute();
        $data = $result->fetchArray();
        self::closeDB();
        if($data)
            return TEAM::WHITE;
        return TEAM::BLACK;
    }

    public static function set_wait_player($game_id) {
        file_put_contents('db/wait_player.id', $game_id);
    }

    public static function get_wait_player() {
        return file_get_contents('db/wait_player.id');
    }

}
?>
