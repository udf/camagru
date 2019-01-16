<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/postvalidator.class.php');

$validator = new PostValidator(
    [
        'username' => [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['regexp' => '/^[a-zA-Z\d_]{1,32}$/']
        ],
        'email' => [
            'filter' => FILTER_VALIDATE_EMAIL
        ],
        'password' => [
            'filter' => FILTER_CALLBACK,
            'options' => matches_all('/^.{6,}$/', '/[a-z]/', '/[A-Z]/', '/\d/')
        ],
        'password_verify' => [
            'filter' => FILTER_CALLBACK,
            'options' => function ($str) { return $str === $_POST['password']; }
        ]
    ],
    [
        'username' => 'Username can only contain letters, numbers, and underscores and it must be shorter or equal to 32 characters',
        'email' => 'Invalid email address',
        'password' => 'Password has to be at least 6 characters long and contain one of the following: An uppercase letter, a lowercase letter, and a digit',
        'password_verify' => 'Passwords do not match',
    ]
);

if ($validator->verify()) {
    try {
        $verify_id = $DATABASE->add_user(
            $validator->data['username'],
            $validator->data['email'],
            $validator->data['password']
        );
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    mail(
        $validator->data['email'],
        'Camagru Account Verification',
        'Click this link to verify your account: http://' . $_SERVER['HTTP_HOST'] . '/verify.php?id=' . $verify_id,
        'From: webmaster@camagru.com'
    );
    die_with_alert('success', 'Registration successful!', 'Please check your email to verify your account', 200);
}

$_PAGE_BUILDER = new Pagebuilder('Register', false);
?>

<main role="main" class="container">
<form class="form-center">
    <h1 class="h3 mb-3 font-weight-normal">Register</h1>
    <input class="form-control" type="text" name="username" placeholder="Username" required autofocus>
    <input class="form-control" type="email" name="email" placeholder="Email" required>
    <input class="form-control" type="password" name="password" placeholder="Password" minlength="6" required>
    <input class="form-control" type="password" name="password_verify" placeholder="Confirm Password" minlength="6" required>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Create account</button>
</form>
</main>
