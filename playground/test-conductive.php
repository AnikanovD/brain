<?php

require_once('classes.php');

/*
 Add code to tick mecthod at Conductive class, for verbose output

    $timeline = []; //!

    $timeline[$impulse->time] = true; //!

    echo '[';
    for ($i = $this->distance; $i > 0; $i--) {
        echo (isset($timeline[$i])) ? '>' : '-';
    }
    echo ']';
*/

/*
 o conductor
 0 last conductor
 > induce impulse
 - one distance
 | one distance


 Scheme contains five condutors and distance lenght is 9.
 At 0 iteration, induce initial impulse.
 At 9 iteration of moments of time, last conductor will be trying to induce impulse.
 >o--o---o
         |
         --o
           |
           0

*/

/**
 * Develop conductors
 */
class Conductor extends Conductive
{
    public function modulate($impulse)
    {
        // nothing!
        // just superconductor ;)
        return;
    }
}


class LastConductor extends Conductor
{

    public function induce()
    {
        echo "!!! YOBOM TOKNYLO !!! taschi izolentu";
    }

}

/**
 * Creates topology of conductors
 */
$lastConductor = new LastConductor();
$lastConductor->distance = 1;

$fourhConductor = new Conductor($lastConductor);
$fourhConductor->distance = 4;

$thirtConductor = new Conductor($fourhConductor);
$thirtConductor->distance = 3;

$secondConductor = new Conductor($thirtConductor);
$secondConductor->distance = 2;

$firstConductor = new Conductor($secondConductor); // create unnamed class as stub
$firstConductor->distance = 1;

$conductors = [
    '1' => $firstConductor,
    '2' => $secondConductor,
    '3' => $thirtConductor,
    '4' => $fourhConductor,
    '5' => $lastConductor
];

/**
 * Let's try !
 */
$worldTime = 11;
$timeCounter = $worldTime;
$firstConductor->induce();

while ($timeCounter) {
    echo "\n\n @ " . ($worldTime - $timeCounter--) . " @ \n ";

    foreach ($conductors as $key => $conductor) {
        echo " conductor $key \n ";
        $conductor->tick();
        echo "\n";
    }
}