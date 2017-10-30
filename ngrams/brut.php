<?php

$words = explode("\n", file_get_contents('words.txt'));
$excep = [];
$ccc = 0;

foreach ($words as $word) {
    $word = strtoupper(trim($word));

    if (strlen($word) != 5) continue;

    $excep[] = $word;
}

//print_r($excep);


//$alphas = array_merge(range('A', 'Z'), range('0', '9'));
$alphas = array_merge(range('A', 'Z'));
$vowel = ['A', 'E', 'I', 'O', 'U', 'Y'];

foreach ($alphas as $a1) {
    foreach ($alphas as $a2) {
        foreach ($alphas as $a3) {
            foreach ($alphas as $a4) {
                foreach ($alphas as $a5) {
                    //$isRep = (($a1 == $a2) && ($a2 == $a3) && ($a3 == $a4) && ($a4 == $a5)) ;
                    //
                    $isTwo = (in_array($a1, $vowel) && in_array($a2, $vowel))
                        || (in_array($a2, $vowel) && in_array($a3, $vowel) && ($a2 != 'O') && ($a2 != 'E'))
                        || (in_array($a3, $vowel) && in_array($a4, $vowel) && ($a3 != 'O') && ($a3 != 'E'))
                        || (in_array($a4, $vowel) && in_array($a5, $vowel)  && ($a4 != 'O') && ($a4 != 'E'));

                    $isThree = (!in_array($a1, $vowel) && !in_array($a2, $vowel) && !in_array($a3, $vowel))
                        || (!in_array($a2, $vowel) && !in_array($a3, $vowel) && !in_array($a4, $vowel))
                        || (!in_array($a3, $vowel) && !in_array($a4, $vowel) && !in_array($a5, $vowel));

                    $_w = $a1 . $a2 . $a3 . $a4 . $a5;
                    $isExcep = (in_array($_w, $excep));

                    if ($isTwo || $isThree || $isExcep) continue;

                    echo $a1 . $a2 . $a3 . $a4 . $a5 . '.ru' . "\n";
                }
            }
        }
    }
}