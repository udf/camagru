<?php
require_once('../includes/init.php');
require_auth();
require_once('../includes/postvalidator.class.php');

$validator = new PostValidator(
    [
        'current_password' => $VALIDATOR_PASSWORD_CURRENT,
        'password' => $VALIDATOR_PASSWORD,
        'password_verify' => $VALIDATOR_PASSWORD_VERIFY
    ]
);

if ($validator->verify()) {
    try {
        if (password_verify($validator->data['password'], $_SESSION['pw_hash']))
            throw new RuntimeException('Your new password cannot be the same as your old password');
        $_SESSION['pw_hash'] = $DATABASE->update_password($_SESSION['id'], $validator->data['password']);
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    die_with_alert('success', 'Your password has been updated', '', 200);
}
