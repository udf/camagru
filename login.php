<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/postvalidator.class.php');

if (isset($_SESSION['username']))
    header('Location: index.php');

$validator = new PostValidator(
    [
        'username' => [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['regexp' => '/^.+$/']
        ],
        'password' => [
            'filter' => FILTER_VALIDATE_REGEXP,
            'options' => ['regexp' => '/^.+$/']
        ]
    ],
    [
        'username' => 'No username/email provided',
        'password' => 'No password provided'
    ]
);

if ($validator->verify()) {
    try {
        $username = $DATABASE->get_user(
            $validator->data['username'],
            $validator->data['password']
        );
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    $_SESSION['username'] = $username;
    die_with_alert('success', 'Login successful!', 'You will be redirected to the home page', 200);
}

$_PAGE_BUILDER = new Pagebuilder('Login', false);
?>

<main role="main" class="container">
<form class="form-center" redirect="index.php">
    <h1 class="h3 mb-3 font-weight-normal">Sign in</h1>
    <input class="form-control" type="text" name="username" placeholder="Email or Username" required="" autofocus="">
    <input class="form-control" type="password" name="password" placeholder="Password" minlength="6" required="">
    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    <a class="btn btn-lg btn-secondary btn-block" style="color: white;" href="register.php">Create account</a>
</form>
</main>
