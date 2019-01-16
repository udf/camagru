<?php
class _HTMLTag {
    protected $name;
    public $attrs;
    public $children;

    function __construct(string $name, array $attrs=[]) {
        $this->name = $name;
        $this->attrs = $attrs;
        $this->children = [];
    }

    function append($obj) {
        $this->children[] = $obj;
        return $this;
    }

    function prepend($obj) {
        array_unshift($this->children, $obj);
        return $this;
    }

    function __toString() {
        $str = "<{$this->name}";
        foreach ($this->attrs as $attr => $value) {
            $str .= ' ';
            if (is_int($attr))
                $str .= $value;
            else
                $str .= sprintf('%s="%s"', $attr, htmlspecialchars($value));
        }
        $str .= '>';
        foreach ($this->children as $child) {
            $str .= (string)$child;
        }
        $str .= "</{$this->name}>";
        return $str;
    }
}

function HTMLTag(...$args) {
    return new _HTMLTag(...$args);
}
