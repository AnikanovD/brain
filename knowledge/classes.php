<?php

abstract class Link
{
    public $uid;

    public $one;
    public $two;

    public function __construct(Concept $one, Concept $two)
    {
        $this->uid = uniqid('l_');
        $this->one = $one;
        $this->two = $two;
    }

    public function has(Concept $concept)
    {
        return ($this->one->uid === $concept->uid)
            || ($this->two->uid === $concept->uid);
    }
}

abstract class Concept
{
    public $uid;
    public $desc;

    public function __construct($desc)
    {
        $this->uid = uniqid('c_');
        $this->desc = $desc;
    }
}

abstract class Image
{
    public $uid;
    public $desc;
    public $links;

    public function __construct($desc)
    {
        $this->uid = uniqid('i_');
        $this->desc = $desc;
    }

    public function involve(Link $link)
    {
        $this->links[] = $link;
    }
}

abstract class Attention
{
    public $images;
}