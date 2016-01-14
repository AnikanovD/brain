<?php

namespace AssocField;

/**
 * Image
 * It a mixed graph.
 * It a stable pattern of activated entities.
 */
class Image
{
    public $entity;
    public $chains = [];

    public $depth = 3;
    public $relevance = 3;

    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    public function flash()
    {
        $this->chains[$this->entity->id] = $this->buildChain($this->entity);

        return $this;
    }

    public function depth(int $value)
    {
        $this->depth = $value;

        return $this;
    }

    public function relevance(int $value)
    {
        $this->relevance = $value;

        return $this;
    }

    public function interfere(Entity $entity)
    {

    }

    public function buildChain(Entity $entity, $depth = 0, $relevance = 0)
    {
        if ($depth > $this->depth) {
            return Chain::DISCONTINUE;
        }

        if (($depth != 0) && ($entity->id == $this->entity->id)) {
            // loop
            return Chain::LOOP;
        }

        $chains = [];

        foreach ($entity->links as $entityId => $linkRelevance) {
            $entityRelated = AssocField::$field->findEntityById($entityId);

            $chains[$entityRelated->id] = $this->buildChain($entityRelated, $depth + 1, $relevance + $linkRelevance);
        }

        if (!empty($chains)) {
            return $chains;
        } else {
            return Chain::END;
        }
    }

}