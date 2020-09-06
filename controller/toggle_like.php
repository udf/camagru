<?php
require_once('../includes/init.php');

require_auth();

if (array_key_exists('id', $_POST) === false)
    die_with_alert('danger', 'Error', 'Invalid post data');

try {
    $ret = $DATABASE->toggle_like(
        $_POST['id'],
        $_SESSION['id']
    );
    die($ret['isLiked']);
} catch (RuntimeException $e) {
    die_with_alert('danger', 'Error', $e->getMessage());
}