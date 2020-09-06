<?php
require_once('../includes/init.php');
require_auth();
require_once('../includes/postvalidator.class.php');
require_once('../includes/htmltag.class.php');

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

        $email = $DATABASE->get_email_for_image(
            $validator->data['id'],
            $_SESSION['id']
        );
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }

    // Close connection after sending success message
    ob_start();
    echo make_alert('success', 'Your comment has been posted<br>Please refresh the page to load the latest comments');
    $size = ob_get_length();
    header("Content-Encoding: none");
    header("Content-Length: {$size}");
    header("Connection: close");
    ob_end_flush();
    ob_flush();
    flush();
    if(session_id()) session_write_close();

    // Send email in the background
    if ($email !== false) {
        $message = 'Someone commented "' . htmlspecialchars($validator->data['text']) . '"';
        $message .= ' on ' . (
            HTMLTag('a')
            ->setAttr('href', "http://{$_SERVER['HTTP_HOST']}/comments.php?id={$validator->data['id']}")
            ->setContent('your post')
        ) . '!';
        mail(
            $email['email'],
            'Camagru: comment on your post',
            $message,
            'Content-Type: text/html; charset=UTF-8'
        );
    }
}
