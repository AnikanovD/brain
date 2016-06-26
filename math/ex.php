<?php

$associations = [
    'signs' => [
        'equals',
        ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'plus', 'minus', 'equal', 'good', 'bad'],
        ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '+', '-', '=', '@', '!'],
    ],
    'digit' => [
        'includes',
        ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve']
    ],
    'operator' => [
        'includes',
        ['plus', 'minus', 'equal']
    ],
    'score' => [
        'includes',
        ['good', 'bad']
    ],
    'expression' => [
        'requires',
        ['digit', 'operator', 'equal']
    ],
];

class Field
{
    public $entities;
    public $relations;

    public function __construct($assocs)
    {
        foreach ($assocs as $key => $value) {
            $relation = Relation::load($value)
            $this->entities = array_merge($this->relations, $relation->entities);
            $this->relations[$key] = $relation;
        }
    }

    public function read($sign)
    {

    }
}

class Relation
{
    public $entities;

    public static function load($rel)
    {
        switch ($rel[0]) {
            case 'equals':
                return new EqualsRelation($rel);
                break;
            case 'includes':
                return new IncludesRelation($rel);
                break;
            case 'requires':
                return new RequiresRelation($rel);
                break;

            default:
                throw new Exception('Unknown relation type');
                break;
        }
    }

}

class EqualsRelation extends Relation
{
    public function __construct($rel)
    {
        foreach ($rel[1] as $value) {
            $this->entities[$value] = $value;
        }
    }
}

class IncludesRelation extends Relation
{

}

class RequiresRelation extends Relation
{

}
