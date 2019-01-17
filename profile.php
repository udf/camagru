<?php
require_once('includes/pagebuilder.class.php');

require_auth();

$_PAGE_BUILDER = new Pagebuilder('Settings');
?>
<main role="main" class="container">
    <h1 class="mt-5">Your Settings</h1>
    <h4>You are logged in as <?php echo htmlspecialchars($_SESSION['username']); ?></h4>
    <hr />

    <form class="form-center" action="change_username.php">
        <h1 class="h3 mb-3 font-weight-normal">Change Username</h1>
        <input class="form-control" type="text" name="username" placeholder="New Username" required="">
        <button class="btn btn-lg btn-secondary btn-block" type="submit">Change Username</button>
    </form>
    <hr />

    <form class="form-center" action="change_password.php">
        <h1 class="h3 mb-3 font-weight-normal">Change Password</h1>
        <input class="form-control" type="text" name="password" placeholder="Current Password" required="">
        <input class="form-control" type="text" name="new_password" placeholder="New Password" required="">
        <input class="form-control" type="text" name="new_password_verify" placeholder="New Password" required="">
        <button class="btn btn-lg btn-secondary btn-block" type="submit">Change Password</button>
    </form>
    <hr />

    <form class="form-center" action="change_email.php">
        <h1 class="h3 mb-3 font-weight-normal">Change Email</h1>
        <input class="form-control" type="text" name="email" placeholder="Current Email Address" required="">
        <input class="form-control" type="text" name="new_email" placeholder="New Email Address" required="">
        <button class="btn btn-lg btn-secondary btn-block" type="submit">Change Email</button>
    </form>
    <hr />

    <form class="form-center" action="change_notify.php">
        <h1 class="h3 mb-3 font-weight-normal">Email Nofications</h1>
        <label>Notifications Enabled:
            <input class="checkbox pull-right" type="checkbox" name="notify_on" checked>
        </label>
        <button class="btn btn-lg btn-secondary btn-block" type="submit">Save</button>
    </form>
    <hr />
</main>
