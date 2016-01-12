<?php

namespace AssocField;

/**
 * Entity
 * It smallest unit in the theory associative field, which is related to other entities.
 * It can be anything. It can take any meanings.
 * It is similar to the morpheme at lexicography.
 *
 * For example:
 *   "Notebook" as object
 *   "Stylus" as thing
 *   "Escape" as action
 *   "Painful" as feel
 */
class Entity
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $meaning;

    /**
     * @var array
     */
    public $links = [];

    /**
     * Set meaning and id
     */
    public function __construct($meaning)
    {
        $this->meaning = Utils::normalizeMeaning($meaning);

        $this->id = sha1($this->meaning);
    }

    /**
     * Unidirectional linking
     */
    public function linkUni($entity, $relevance)
    {
        $this->linkWith($entity, $relevance);
    }

    /**
     * Bi-directional linking
     */
    public function linkBi($entity, $relevance)
    {
        $this->linkWith($entity, $relevance);
        $entity->linkWith($this, $relevance);
    }

    /**
     * Link with entity
     */
    protected function linkWith($entity, $relevance)
    {
        if (!isset($this->links[$entity->id])) {
            $this->links[$entity->id] = $relevance;
        }
    }

}