<?php
require_once(__DIR__ . '/../config/database.php');

class DB {
    protected $conn;

    function __construct($use_database = true) {
        global $DB_NAME, $DB_DSN, $DB_USER, $DB_PASSWORD;

        try {
            $this->conn = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die('Database connection error: ' . $e->getMessage());
        }

        if ($use_database) {
            try {
                $this->conn->query("USE camagru;");
            } catch(PDOException $error) {
                die('Failed to find database, please run config/setup.php first!');
            }
        }
    }

    function setup() {
        $this->conn->query('DROP DATABASE IF EXISTS camagru');
        $this->conn->query('CREATE DATABASE camagru; USE camagru;');

        $this->conn->query('CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(256) UNIQUE NOT NULL,
            email VARCHAR(256) UNIQUE NOT NULL,
            pw_hash VARCHAR(60) NOT NULL,
            verify_id VARCHAR(64) DEFAULT NULL,
            pw_change_id VARCHAR(64) DEFAULT NULL,
            email_notifications BOOLEAN DEFAULT 1
        );');
        echo "Successfully (re)created database!";
    }

    function add_user($username, $email, $password) {
        $sql = $this->conn->prepare('
            INSERT INTO users
                (`username`, `email`, `pw_hash`, `verify_id`)
            VALUES
                (?, ?, ?, ?);
        ');
        $pw_hash = password_hash($password, PASSWORD_BCRYPT);
        $verify_id = bin2hex(random_bytes(32));

        try {
            $sql->execute([$username, $email, $pw_hash, $verify_id]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000')
                throw new RuntimeException('A user with those details already exists');
        }
        return $verify_id;
    }

    function get_user($username_or_email, $password) {
        $sql = $this->conn->prepare('
            SELECT
                `username`, `email`, `pw_hash`, `verify_id`
            FROM `users` WHERE
                (email = ? OR username = ?);
        ');
        try {
            $sql->execute([$username_or_email, $username_or_email]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result === false)
            throw new RuntimeException('A user with those details was not found!');
        if (password_verify($password, $result['pw_hash']) == false)
            throw new RuntimeException('Incorrect password!');
        if ($result['verify_id'] !== NULL)
            throw new RuntimeException(
                "Your email \"" . htmlspecialchars($result['email']) . "\" is not verified! "
                . "<a href=reverify.php?email=" . urlencode($result['email']) . ">Click here to resend your verification email.</a>"
            );
        return $result['username'];
    }

    function get_verify_id($email) {
        $sql = $this->conn->prepare('
            SELECT
                `verify_id`
            FROM `users` WHERE
                email = ?;
        ');
        try {
            $sql->execute([$email]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result === false)
            throw new RuntimeException('There is no account associated with the provided email address');
        return $result['verify_id'];
    }

    function verify_user($verify_id) {
        $sql = $this->conn->prepare('
            UPDATE users SET
                verify_id = NULL
            WHERE
                verify_id = ?;
        ');
        try {
            $sql->execute([$verify_id]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
    }
}
?>
