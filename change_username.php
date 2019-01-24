<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/postvalidator.class.php');

require_auth();

$validator = new PostValidator(
    [
        'current_password' => $VALIDATOR_PASSWORD_CURRENT,
        'username' => $VALIDATOR_USERNAME,
    ]
);

if ($validator->verify()) {
    try {
        $DATABASE->update_user(
            $_SESSION['id'],
            $validator->data['username'],
            $_SESSION['email_notifications']
        );
        $_SESSION['username'] = $validator->data['username'];
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    die_with_alert('success', 'Your username has been updated', 'Please refresh the page to load the latest details', 200);
}
