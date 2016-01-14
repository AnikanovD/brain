<?php

namespace AssocField;

/**
 * Consciousness
 * It is the beginning of a structured chaos that we call mind.
 */
class Consciousness
{
    public $images;

    public function addImage($image)
    {
        $this->images[] = $image;

        return $this;
    }

    public function resolve()
    {

    }
}
