<?php

function matches_all(...$patterns) {
    return function ($str) use ($patterns) {
        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $str))
                return false;
        }
        return $str;
    };
}

function make_alert($type, $text) {
    return "<div class=\"alert alert-$type\">$text</div>";
}

function die_with_alert($type, $pre, $text, $response=400) {
    http_response_code($response);
    die(make_alert($type, "<strong>$pre</strong> $text"));
}
