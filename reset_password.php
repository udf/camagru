<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/postvalidator.class.php');

if (isset($_SESSION['username']))
    header('Location: index.php');

if (array_key_exists('id', $_GET) === false)
    die('No.');

try {
    $user_data = $DATABASE->get_user_by_pw_change_id($_GET['id']);
} catch (RuntimeException $e) {
    die('No.');
}


$validator = new PostValidator(
    [
        'password' => $VALIDATOR_PASSWORD,
        'password_verify' => $VALIDATOR_PASSWORD_VERIFY
    ]
);

if ($validator->verify()) {
    try {
        $DATABASE->update_password($user_data['id'], $validator->data['password']);
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }
    die_with_alert('success', 'Your password has been changed',
        'You may now <a href="login.php">log in</a> with your new password.', 200);
}

$_PAGE_BUILDER = new Pagebuilder('Reset Password');
?>

<form class="form-center">
    <h1 class="mt-5 font-weight-normal">Reset Password</h1>
    <input class="form-control" type="password" name="password" placeholder="Password" minlength="6" required>
    <input class="form-control" type="password" name="password_verify" placeholder="Confirm Password" minlength="6" required>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Reset Password</button>
</form>
