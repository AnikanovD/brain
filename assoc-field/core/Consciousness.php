<?php

namespace AssocField;

/**
 * Consciousness
 * It is the beginning of a structured chaos that we call mind.
 *
 * @todo Implement focus with relevance
 */
class Consciousness
{
    public $field;

    public function __construct($field)
    {
        $this->field = $field;
    }

    public function makeAllChains()
    {
        foreach ($this->field->entities as $entity) {
            $this->makeChain($entity);
        }
    }

    public function makeChain($entity, $level = 0, $relevance = 0)
    {
        AssocField::log(
            str_repeat(' ', $level * 4) . $entity->meaning . '(' . $relevance . ')'
        );

        // Preventing circular links
        if ($level > 3) return;

        // Traverse
        foreach ($entity->links as $entityId => $relevance) {
            $entityRelated = $this->field->findEntityById($entityId);

            $this->makeChain($entityRelated, $level + 1, $relevance);
        }
    }

}
