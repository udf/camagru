<?php

class PostValidator
{
    public $filter_def;
    public $err_msgs;
    public $data;

    function __construct($filter_def) {
        $this->filter_def = $filter_def;
    }

    function verify() {
        $post_data = filter_input_array(INPUT_POST, $this->filter_def);
        if (is_null($post_data))
            return False;
        foreach ($post_data as $key => $value) {
            if ($value === false)
                die_with_alert('danger', 'Error', $this->filter_def[$key]['error']);
        }
        $this->data = $post_data;
        return True;
    }
}

function make_callback_validator($func, $error_msg) {
    return [
        'filter' => FILTER_CALLBACK,
        'options' => $func,
        'error' => $error_msg
    ];
}

$VALIDATOR_USERNAME = [
    'filter' => FILTER_VALIDATE_REGEXP,
    'options' => ['regexp' => '/^[a-zA-Z\d_]{1,32}$/'],
    'error' => 'Username can only contain letters, numbers, and underscores '
                . 'and it must be shorter or equal to 32 characters'
];
$VALIDATOR_EMAIL = [
    'filter' => FILTER_VALIDATE_EMAIL,
    'error' => 'Invalid email address'
];
$VALIDATOR_PASSWORD = [
    'filter' => FILTER_CALLBACK,
    'options' => matches_all('/^.{6,}$/', '/[a-z]/', '/[A-Z]/', '/\d/'),
    'error' => 'Password has to be at least 6 characters long and contain '
                . 'one of the following: An uppercase letter, a lowercase letter, and a digit'
];
$VALIDATOR_PASSWORD_VERIFY = make_callback_validator(
    function ($str) { return $str === $_POST['password']; },
    'Passwords do not match'
);
$VALIDATOR_PASSWORD_CURRENT = make_callback_validator(
    function ($str) {
        return password_verify($str, $_SESSION['pw_hash']);
    },
    'The password you entered was incorrect'
);
