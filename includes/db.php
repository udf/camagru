<?php
require_once('config/database.php');

class DB {
    protected static $instance;

    protected function __construct() {}

    public static function get() {
        global $DB_NAME, $DB_DSN, $DB_USER, $DB_PASSWORD;

        if(!empty(self::$instance))
            return self::$instance;
        try {
            self::$instance = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD);
            self::$instance->setAttribute(PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $error) {
            echo $error->getMessage();
        }
        return self::$instance;
    }
}
?>
