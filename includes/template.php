<?php
function gen_page($title, $body) {
echo <<<HTML
	<!DOCTYPE html>
	<html>
	<head>
		<title>$title</title>
	</head>
	<body>
		$body
	</body>
	</html>
HTML;
}
