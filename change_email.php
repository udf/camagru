<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/postvalidator.class.php');

require_auth();

$validator = new PostValidator(
    [
        'email' => make_callback_validator(
            function ($str) { return $str == $_SESSION['email']; },
            'Incorrect email address.'
        ),
        'new_email' => $VALIDATOR_EMAIL,
    ]
);

if ($validator->verify()) {
    try {
        if ($validator->data['new_email'] == $_SESSION['email'])
            throw new RuntimeException('Your new email address cannot be the same as your current.');
        $DATABASE->update_email(
            $_SESSION['id'],
            $validator->data['new_email']
        );
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    die_with_alert('success', 'Your email has been updated', 'Please refresh the page to load the latest details', 200);
}
