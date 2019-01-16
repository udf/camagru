<?php

class PostValidator
{
    public $filter_def;
    public $err_msgs;
    public $data;

    function __construct($filter_def, $err_msgs) {
        $this->filter_def = $filter_def;
        $this->err_msgs = $err_msgs;
    }

    function verify() {
        $post_data = filter_input_array(INPUT_POST, $this->filter_def);
        if (is_null($post_data))
            return False;
        foreach ($post_data as $key => $value) {
            if ($value === false)
                die_with_alert('danger', 'Error', $this->err_msgs[$key]);
        }
        $this->data = $post_data;
        return True;
    }
}
