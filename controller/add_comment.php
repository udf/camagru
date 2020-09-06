<?php
require_once('../includes/init.php');
require_auth();
require_once('../includes/postvalidator.class.php');

$validator = new PostValidator(
    [
        'id' => [
            'filter' => FILTER_VALIDATE_INT,
            'error' => 'Invalid post data'
        ],
        'text' => $VALIDATOR_COMMENT
    ]
);

if ($validator->verify()) {
    try {
        $DATABASE->add_comment(
            $validator->data['id'],
            $_SESSION['id'],
            $validator->data['text']
        );
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    die_with_alert('success', 'Your comment as been posted', 'Please refresh the page to load the latest comments', 200);
}
