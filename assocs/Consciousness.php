<?php

/**
 * Consciousness
 * It is the beginning of a structured chaos that we call mind.
 *
 * Implement focus with relevance
 */
class Consciousness
{
    public $field;

    public function __construct($field)
    {
        $this->field = $field;
    }

    public function showProjection()
    {
        foreach ($this->field->entities as $entity) {
            $this->expandChain($entity);
        }
    }

    public function expandChain($entity, $level = 0, $relevance = 0)
    {
        Utils::log(
            str_repeat(' ', $level * 4) . $entity->meaning . '(' . $relevance . ')'
        );

        // Preventing circular links
        if ($level > 10) return;

        // Traverse
        foreach ($entity->links as $entityId => $relevance) {
            $entityRelated = $this->field->findEntityById($entityId);
            $this->expandChain($entityRelated, $level + 1, $relevance);
        }
    }

}
