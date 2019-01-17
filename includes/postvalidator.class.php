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
