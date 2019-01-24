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
        ],
        'password' => [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['regexp' => '/^.+$/'],
            'error' => 'No password provided'
        ]
    ]
);

if ($validator->verify()) {
    try {
        $user_data = $DATABASE->get_user($validator->data['username']);
        if (password_verify($validator->data['password'], $user_data['pw_hash']) == false)
            throw new RuntimeException('Incorrect password!');
        if ($user_data['verify_id'] !== NULL)
            throw new RuntimeException(
                "Your email \"" . htmlspecialchars($user_data['email']) . "\" is not verified! "
                . "<a href=reverify.php?email=" . urlencode($user_data['email'])
                . ">Click here to resend your verification email.</a>"
            );
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    foreach ($user_data as $key => $value)
        $_SESSION[$key] = $value;
    die_with_alert('success', 'Login successful!', 'You will be redirected to the home page', 200);
}

$_PAGE_BUILDER = new Pagebuilder('Login');
?>

<form class="form-center" redirect="index.php">
    <h1 class="mt-5 font-weight-normal">Sign in</h1>
    <input class="form-control" type="text" name="username" placeholder="Email or Username" required="" autofocus="">
    <input class="form-control" type="password" name="password" placeholder="Password" minlength="6" required="">
    <a href="forgot_password.php">Forgot Your Password?</a>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    <a class="btn btn-lg btn-secondary btn-block" style="color: white;" href="register.php">Create account</a>
</form>
