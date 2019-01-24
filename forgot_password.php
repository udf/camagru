<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/postvalidator.class.php');

if (isset($_SESSION['username']))
    header('Location: index.php');

$validator = new PostValidator(
    [
        'username' => [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['regexp' => '/^.+$/'],
            'error' => 'No username/email provided'
        ]
    ]
);

if ($validator->verify()) {
    try {
        $user_data = $DATABASE->get_user($validator->data['username']);
        $pw_change_id = $DATABASE->init_pw_reset($user_data['id']);
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    mail(
        $user_data['email'],
        'Camagru Account Password Reset',
        'Click this link to reset your password: http://' . $_SERVER['HTTP_HOST'] . '/reset_password.php?id=' . $pw_change_id,
        'From: webmaster@camagru.com'
    );
    die_with_alert('success', 'Password Reset Initialized!', 'Please check your email for further instructions!', 200);
}

$_PAGE_BUILDER = new Pagebuilder('Forgot Password');
?>

<form class="form-center">
    <h1 class="mt-5 font-weight-normal">Forgot Password</h1>
    <input class="form-control" type="text" name="username" placeholder="Email or Username" required="" autofocus="">
    <button class="btn btn-lg btn-primary btn-block" type="submit">Reset Password</button>
</form>
