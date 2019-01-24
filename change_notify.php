<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/postvalidator.class.php');

require_auth();

$notify_on = (int)(isset($_POST['notify_on']) && $_POST['notify_on'] == 'on');

try {
    $DATABASE->update_user(
        $_SESSION['id'],
        $_SESSION['username'],
        $notify_on
    );
    $_SESSION['email_notifications'] = $notify_on ? '1' : '0';
} catch (RuntimeException $e) {
    die_with_alert('danger', 'Error', $e->getMessage());
}
die_with_alert('success', 'Your preferences have been updated', '', 200);
