<?php



function readCSV($csvFile){
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle) ) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);
    return $line_of_text;
}


$ngrams1 = readCSV('ngrams1.csv');
$ngrams2 = readCSV('ngrams2.csv');
$ngrams3 = readCSV('ngrams3.csv');

print_r($ngrams1[0]);
print_r($ngrams2[0]);
print_r($ngrams3[0]);

echo "\n\n\n\n\n\n\n\n\n";

//////////////////

$bigramBegin = 46;
$trigramBegin = 37;

//////////////////

function build_sorter($key) {
    return function ($a, $b) use ($key) {
        return strnatcmp($a[$key], $b[$key]);
    };
}

//////////////////////////////////////

function matchTrigram($str, $minCount = 0, $byCount = 1)
{
    global $freqs, $ngrams1, $ngrams2, $ngrams3;

    $results = [];
    $last = substr($str, -2);

    foreach ($ngrams3 as $k => $bg) {
        if ($bg[$byCount] < $minCount) continue;
        $_f = substr($bg[0], 0, 2);
        if ($last != $_f) continue;

        $results[] = substr($str, 0, -2) . $bg[0];
    }

    return $results;
}

// @todo add `n`
function matchNgram($str, $minFreq = 1)
{
    global $freqs, $ngrams2, $ngrams3;

    $results = [];
    $lastLetter = substr($str, -1, 1);

    foreach ($freqs as $k => $bg) {
        if ($lastLetter != $bg['bigram'][0]) continue;
        $sharedLetter = $bg['bigram'][1];

        if ($bg['freq'] < $minFreq) continue;

        $results[] = substr($str, 0, -1) . $bg['bigram'];
    }

    return $results;
}


function testNgram($str)
{
    global $freqs, $ngrams1, $ngrams2, $ngrams3;

    $results = [
        'uni' => [],
        'bi' => [],
        'tri' => [],
    ];

    foreach ($ngrams1 as $ngram) {
        if (empty($ngram)) continue;

        if (preg_match_all('/' . $ngram[0] . '/', $str, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                //$match['_'] = $ngram;
                $match['_'] = $ngram[46 + $match[1]];
                $results['uni'][] = $match;
            }
        }
    }

    foreach ($ngrams2 as $ngram) {
        if (empty($ngram)) continue;

        if (preg_match_all('/' . $ngram[0] . '/', $str, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                //$match['_'] = $ngram;
                $match['_'] = $ngram[46 + $match[1]];
                $results['bi'][] = $match;
            }
        }
    }

    foreach ($ngrams3 as $ngram) {
        if (empty($ngram)) continue;

        if (preg_match_all('/' . $ngram[0] . '/', $str, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                //$results['tri'][] = $match;
                $match['_'] = $ngram[37 + $match[1]];
                $results['tri'][] = $match;
            }
        }
    }

    echo ' === ' . $str . ' === ' . "\n";

    foreach ($results as $type => $_ngrams) {
        // show ngram type
        echo ' ' . str_pad($type . ' ', strlen($str) + 18, '-', STR_PAD_RIGHT) . '---' . "\n";

        $_total = 1;
        foreach ($_ngrams as $entry) {
            $_total += $entry['_'];
        }

        usort($_ngrams, build_sorter('1'));

        foreach ($_ngrams as $entry) {
            if ($entry[1] >= 0) {
                echo '     ';
                echo str_pad(str_repeat(' ', $entry[1]) . $entry[0], strlen($str), ' ', STR_PAD_RIGHT);
                echo ' - ' . str_pad($entry['_'], 14, ' ', STR_PAD_LEFT) . ' ';
                echo '(' . round($entry['_'] / $_total * 100) . '%)';
            }

            echo "\n";
        }
    }
    echo "\n";

    return $results;
}

/*
testNgram('ITCHY');
testNgram('MOMHT');
testNgram('MONTH');
testNgram('SIYGR');
die();
*/

array_shift($ngrams3);
foreach ($ngrams3 as $_ngram3) {
    if ($_ngram3[12] < 10000000) continue;
    $_s0 = $_ngram3[0];
    //echo $_s0 . "\n";
    foreach (matchTrigram($_s0, 10000000, 13) as $_s1) {
        //echo $_s1 . "\n";

        foreach (matchTrigram($_s1, 10000000, 14) as $_s2) {
            echo $_s2 . "\n";
        }
    }
}
die();

/*
foreach ($ngrams3 as $_ngram3) {
    foreach ($ngrams2 as $_ngram2) {
        if (strpos($_ngram3[0], $_ngram2[0]) !== false) {
            $rel = round($_ngram3[1] / $_ngram2[1] * 100);
            if ($rel < 0) continue;
            echo $_ngram3[0] . ' < ' . $_ngram2[0] . '  ';
            echo $rel . '  ';
            echo "\n";
        }
    }
    //testNgram($_ngram[0]);
}
die();
*/

//////////////////////////////////////

$bigrams = require_once('fbg.php');

$freqs = $bigrams;

usort($freqs, function ($a, $b) {
    return ($a['freq'] > $b['freq']) ? -1 : 1;
});

$wordLength = 5;
$_freq = 1;

$gathers = [];

//foreach ($freqs as $_k => $_bg) {
foreach ($ngrams3 as $_k => $_bg) {
    // if ($_bg['freq'] < $_freq) continue;
    // $_s1 = $_bg['bigram'];
    if ($_bg[1] < 7369505315) continue;
    $_s1 = $_bg[0];

    //echo '    ' . $_s1 . "\n";
    //$gathers[] = $_s1;

    foreach (matchNgram($_s1, $_freq) as $_s2) {
        //echo '    ' . $_s2 . "\n";
        //$gathers[] = $_s2;

        foreach (matchNgram($_s2, $_freq) as $_s3) {
            //echo '    ' . $_s3 . "\n";
            $gathers[] = $_s3;

            /*
            foreach (matchNgram($_s3, $_freq) as $_s4) {
                //echo '    ' . $_s4 . "\n";
                //$gathers[] = $_s4;

                foreach (matchNgram($_s4, $_freq) as $_s5) {
                    //echo '    ' . $_s5 . "\n";
                    //$gathers[] = $_s5;

                    foreach (matchNgram($_s5, $_freq) as $_s6) {
                        //echo '    ' . $_s6 . "\n";
                        //$gathers[] = $_s6;

                        foreach (matchNgram($_s6, $_freq) as $_s7) {
                            //echo '    ' . $_s7 . "\n";
                            //$gathers[] = $_s7;
                        }
                    }
                }
            }
            */
        }
    }

    //echo "\n";
}


print_r($gathers);
die();
foreach ($gathers as $word) {
    testNgram($word);
}