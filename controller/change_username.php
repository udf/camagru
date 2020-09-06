<?php
require_once('../includes/init.php');
require_auth();
require_once('../includes/postvalidator.class.php');

$validator = new PostValidator(
    [
        'current_password' => $VALIDATOR_PASSWORD_CURRENT,
        'username' => $VALIDATOR_USERNAME
    ]
);

if ($validator->verify()) {
    try {
        if ($validator->data['username'] === $_SESSION['username'])
            throw new RuntimeException('Your new username cannot be the same ask your old username');
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
