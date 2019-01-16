<?php
require_once('includes/pagebuilder.class.php');
unset($_SESSION['username']);
header('Location: index.php');
