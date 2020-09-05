<?php
require_once('includes/pagebuilder.class.php');;
require_once('includes/htmltag.class.php');

require_auth();

$_PAGE_BUILDER = new Pagebuilder('Settings');
?>
<h1 class="mt-5">Your Settings</h1>
<?php
    HTMLTag('h4')
        ->setContent("You're logged in as {$_SESSION['username']}")
        ->print();
?>
<hr />

<form class="form-center" action="change_username.php">
    <h1 class="h3 mb-3 font-weight-normal">Change Username</h1>
    <input class="form-control" type="password" name="current_password" placeholder="Current Password" required="">
    <input class="form-control" type="text" name="username" placeholder="New Username" required="">
    <button class="btn btn-lg btn-secondary btn-block" type="submit">Change Username</button>
</form>
<hr />

<form class="form-center" action="change_password.php">
    <h1 class="h3 mb-3 font-weight-normal">Change Password</h1>
    <input class="form-control" type="password" name="current_password" placeholder="Current Password" required="">
    <input class="form-control" type="password" name="password" placeholder="New Password" required="">
    <input class="form-control" type="password" name="password_verify" placeholder="Confirm Password" required="">
    <button class="btn btn-lg btn-secondary btn-block" type="submit">Change Password</button>
</form>
<hr />

<form class="form-center" action="change_email.php" redirect="login.php">
    <h1 class="h3 mb-3 font-weight-normal">Change Email</h1>
    <input class="form-control" type="text" name="email" placeholder="Current Email Address" required="">
    <input class="form-control" type="text" name="new_email" placeholder="New Email Address" required="">
    <button class="btn btn-lg btn-secondary btn-block" type="submit">Change Email</button>
</form>
<hr />

<form class="form-center" action="change_notify.php">
    <h1 class="h3 mb-3 font-weight-normal">Email Notifications</h1>
    <label>Notifications Enabled:
        <?php
            HTMLTag('input')
                ->setAttr('class', 'checkbox pull-right')
                ->setAttr('type', 'checkbox')
                ->setAttr('name', 'notify_on')
                ->setAttr('checked', $_SESSION['email_notifications'] ? true : false)
                ->print();
        ?>
    </label>
    <button class="btn btn-lg btn-secondary btn-block" type="submit">Save</button>
</form>
<hr />
