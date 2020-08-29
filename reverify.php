<?php
require_once('includes/pagebuilder.class.php');

if (array_key_exists('email', $_GET) === false)
    die('No.');

try {
    $user = $DATABASE->get_user($_GET['email']);
} catch (RuntimeException $e) {
    die($e->getMessage());
}
$verify_id = $user['verify_id'];
if ($verify_id !== NULL) {
    mail(
        $_GET['email'],
        'Camagru Account Verification',
        'Click this link to verify your account: http://' . $_SERVER['HTTP_HOST'] . '/verify.php?id=' . $verify_id,
        'From: webmaster@camagru.com'
    );
}
echo "Please check your email";
