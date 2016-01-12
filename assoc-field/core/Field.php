<?php

namespace AssocField;

/**
 * Associative field
 * Storage of entities.
 */
class Field
{
    /**
     * @var \Entity[]
     */
    public $entities = [];

    /**
     * @var integer
     */
    public static $lastId = 0;

    /**
     * Instance entity object and add to the field
     *
     * @return \Entity
     */
    public function createEntity($meaning)
    {
        $entity = new Entity($meaning);

        $this->entities[$entity->id] = $entity;

        return $entity;
    }

    /**
     * Find for entities in the field
     */
    public function findEntityById($id)
    {
        if (isset($this->entities[$id])) {
            return $this->entities[$id];
        }
    }

    /**
     * Find for entities in the field
     */
    public function findEntityByMeaning($meaning)
    {
        $meaning = Utils::normalizeMeaning($meaning);

        foreach ($this->entities as $entityId => $entity) {
            if ($entity->meaning == $meaning) {
                return $entity;
            }
        }
    }

    /**
     * Generate ID
     *
     * @return string
     */
    public static function generateId()
    {
        self::$lastId++;

        $id = str_pad(self::$lastId, 16, '0', STR_PAD_LEFT);

        return $id;
    }
}