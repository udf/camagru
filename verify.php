<?php
require_once('includes/pagebuilder.class.php');

if (array_key_exists('id', $_GET) === false)
    die('No.');

try {
    $DATABASE->verify_user($_GET['id']);
} catch (RuntimeException $e) {
    die($e->getMessage());
}
header('Location: index.php');
