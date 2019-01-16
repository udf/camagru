<?php
require_once('includes/pagebuilder.class.php');
$_PAGE_BUILDER = new Pagebuilder('Gallery');
?>
<main role="main" class="container">
    <h1 class="mt-5">hello, world!</h1>
    <?php echo "you are logged in as " . htmlspecialchars($_SESSION['username']); ?>
</main>
