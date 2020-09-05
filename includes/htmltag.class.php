<?php
class _HTMLTag {
    protected $name;
    public $attrs;
    public $children;

    function __construct(string $name, array $attrs=[], string $content='') {
        $this->name = $name;
        $this->attrs = $attrs;
        $this->children = [];
        $this->content = $content;
    }

    function setContent($content) {
        $this->content = $content;
        return $this;
    }

    function setAttr($key, $value) {
        $this->attrs[$key] = $value;
        return $this;
    }

    function addChildren($children) {
        $this->children = array_merge($this->children, $children);
        return $this;
    }

    function append($obj) {
        $this->children[] = $obj;
        return $this;
    }

    function prepend($obj) {
        array_unshift($this->children, $obj);
        return $this;
    }

    function print() {
        echo $this;
    }

    function __toString() {
        $str = "<{$this->name}";
        foreach ($this->attrs as $attr => $value) {
            $str .= ' ';
            if (is_int($attr)) {
                $str .= $value;
                continue;
            }
            if (is_bool($value)) {
                if ($value === true)
                    $str .= $attr;
                continue;
            }
            $str .= sprintf('%s="%s"', $attr, htmlspecialchars($value));
        }
        $str .= '>';
        if ($this->content !== '') {
            $str .= htmlspecialchars($this->content);
        }
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
