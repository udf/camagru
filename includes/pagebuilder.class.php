<?php
require_once(__DIR__ . '/util.php');
require_once(__DIR__ . '/db.php');
session_start();

$DATABASE = new DB();

// Outputs header when constructed
// Deconstruction outputs the footer
class Pagebuilder
{
    function __construct($title, $navbar=True) {
?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo $title ?> | Camagru</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="css/default.css">
        <script src="js/main.js"></script>
    </head>
    <body>
<?php
    if ($navbar):
?>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="#">Camagru</a>
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Gallery</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
        </ul>
        <ul class="navbar-nav">
            <?php if (isset($_SESSION['username'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">Your Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Sign Out</a>
            </li>
            <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="login.php">Sign In</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="register.php">Create Account</a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
    <div id="server_messages"></div>
<?php
    }

    function __destruct() {
?>
    <footer class="footer">
        <div class="container">
            <span>© Sam 2018</span>
        </div>
    </footer>
    </body>
    </html>
<?php
    }
}
?>
