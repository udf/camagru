<?php
require_once('includes/pagebuilder.class.php');
foreach ($_SESSION as $key => $value) {
    unset($_SESSION[$key]);
}
header('Location: index.php');
