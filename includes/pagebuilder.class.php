<?php
require_once(__DIR__ . '/init.php');

// Outputs header when constructed
// Deconstruction outputs the footer
class Pagebuilder
{
    function __construct($title) {
?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo $title ?> | Camagru</title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/default.css">
        <script src="js/main.js"></script>
    </head>
    <body>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <a class="navbar-brand" href="/">Camagru</a>
        <ul class="navbar-nav mr-auto">
            <?php if (isset($_SESSION['username'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="ft_snapchat.php">Upload</a>
            </li>
            <?php endif; ?>
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
    <main role="main" class="container">
        <div id="server_messages"></div>
<?php
    }

    function __destruct() {
?>
    </main>
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
