<?php

function matches_all(...$patterns) {
    return function ($str) use ($patterns) {
        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $str))
                return;
        }
        return $str;
    };
}

$matcher = matches_all('/^.{5}$/', '/\d/');

echo $matcher('hello');
echo $matcher('hello1');
echo $matcher('hell1');
