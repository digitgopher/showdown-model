<?php

// Of the given player cards, returns averages and standard deviations as ['avg'] and ['sd'] respectively
function getCardAverages($cards){
    // Initialize all averages as 0
    $avgs = array_fill_keys(array_keys($cards[0]), 0);
    // Get sums (Add up all the OB values, etc)
    foreach ($cards as $key => $value) {
        foreach ($value as $k => $v) {
            $avgs[$k] += $v;
        }
    }
    // Get averages
    $avgs = array_combine(
                array_keys($avgs), // keep the keys
                array_map( // get the values
                    function($val,$c) { return $val / $c; },
                    $avgs, // numerator
                    array_fill(0, count($avgs),count($cards)) // denominator
                )
            );
    // Create array for stddev
    $stddevs = array_fill_keys(array_keys($cards[0]), array());
    // Fill the array with all values, e.g. $stddevs['OB'] = [val1,val2,...,valLast]
    foreach ($cards as $key => $value) {
        foreach ($value as $k => $v) {
            // push next chart val onto appropriate array
            $stddevs[$k][] = $v;
        }
    }
    // Get standard deviations
    foreach ($stddevs as $key => &$value) {
        // Transform a list of values into the stddev of those values
        $value = sd($value);
    }
    return ['avg' => $avgs,'sd' => $stddevs];
}

// Function to calculate standard deviation (uses sd_square)    
function sd($array){   
    // square root of sum of squares devided by N
    return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)/*Subtract 1 if sample rather than population*/) );
}

// Function to calculate square of value - mean
function sd_square($x, $mean){
    return pow($x - $mean,2);
}

// To aggregate individual averages into number of outs and OB/C
function averageMetaOnbase($b_avgs,$p_avgs){// $setOfAvgs = [getCardAverages($pCards),getCardAverages($bCards)]
    return array(
        'OB avg'        => $b_avgs['avg']['OB'],
        'OB sd'         => $b_avgs['sd']['OB'],
        'b_outs avg'    => $b_avgs['avg']['SO'] + $b_avgs['avg']['GB'] + $b_avgs['avg']['FB'],
        'b_outs sd'     => sd_sum([$b_avgs['sd']['SO'],$b_avgs['sd']['GB'],$b_avgs['sd']['FB']]),
        'C avg'         => $p_avgs['avg']['C'],
        'C sd'          => $p_avgs['sd']['C'],
        'p_outs avg'    => $p_avgs['avg']['PU'] + $p_avgs['avg']['SO'] + $p_avgs['avg']['GB'] + $p_avgs['avg']['FB'],
        'p_outs sd'     => sd_sum([$p_avgs['sd']['PU'],$p_avgs['sd']['SO'],$p_avgs['sd']['GB'],$p_avgs['sd']['FB']]),
        );
}

// Function to "add up" standard deviations
// Conceptually, sum the variances and then take the square root to get the standard deviation
function sd_sum($values){
    return sqrt(array_sum(array_map("sd_square", $values, array_fill(0,count($values),0))));
}


// Process a raw chart:
//  - Round each value
//  - If less than 20 possible results, find the most off value(s) to add. For
//      batters, give preference to adding in 1B+ results.
//  - If more than 20 possible results, find the most off value(s) to reduce.
//  - Return chart in the chosen format
//
// If second parameter set to true, a rendered version of the chart is
// returned. By default, the number of slots is returned.
function processChart($chart, $renderAsCard=false){
    // Calculating $chart type here is just easier than in each method
    $firstKey = array_key_exists("C", $chart) ? "C" : "OB";
    $chart = roundChart($chart, $firstKey);
    if($renderAsCard){
        $chart = prettifyChart($chart, $firstKey);
    }
    return $chart;
}

function roundChart($chart, $firstKey){
    // Round
    $temp = array();
    foreach ($chart as $key => $value) {
        $temp[$key] = round($value);
    }
    
    $count = array_sum($temp) - $temp[$firstKey];

    // Deal with the rounding not adding up to 20
    $num_over_20 = $count - 20;
    switch($num_over_20){
        case 0:
            // Rounded to 20 as expected. Nice.
            break;
        case -1:
            // Give 1B+ if single and double values allow for it, else find the most appropriate value to add to
            $singlesOff = $doublesOff = 0;
            if($firstKey == 'OB'){
                $singlesOff = abs($temp['1B'] - $chart['1B']);
                $doublesOff = abs($temp['2B'] - $chart['2B']);
            }
            if($singlesOff + $doublesOff > .3){
                $temp['1B+']++;
            }
            else{
                $curVal = 0;
                $curKey = '';
                foreach ($chart as $key => $value) {
                    if($key == $firstKey){
                        continue;
                    }
                    if($value > round($value)){ // Only care if we are rounding down by default...
                        if($value - round($value) > $curVal){
                            $curVal = $value - round($value);
                            $curKey = $key;
                        }
                    }
                }
                $temp[$curKey]++;
            }
            break;
        case -2:
            // Give 1B+ if single and double values allow for it, else find the most appropriate value to add to
            $singlesOff = $doublesOff = 0;
            if($firstKey == 'OB'){
                $singlesOff = abs($temp['1B'] - $chart['1B']);
                $doublesOff = abs($temp['2B'] - $chart['2B']);
            }
            if($singlesOff + $doublesOff > .6){
                $temp['1B+'] += 2;
            }
            elseif($singlesOff + $doublesOff > .3){
                $temp['1B+']++;
                $curVal = 0;
                $curKey = '';
                foreach ($chart as $key => $value) {
                    if($key == $firstKey){
                        continue;
                    }
                    if($value > round($value)){ // Only care if we are rounding down by default...
                        if($value - round($value) > $curVal){
                            $curVal = $value - round($value);
                            $curKey = $key;
                        }
                    }
                }
                $temp[$curKey]++;
            }
            else{
                // Add 1 to the two 'most off' values
                $curVal = 0;
                $curKey = '';
                // Find first value that is most off
                foreach ($chart as $key => $value) {
                    if($key == $firstKey){
                        continue;
                    }
                    if($value > round($value)){ // Only care if we are rounding down by default...
                        if($value - round($value) > $curVal){
                            $curVal = $value - round($value);
                            $curKey = $key;
                        }
                    }
                }
                $temp[$curKey]++;
                $curVal = 0;
                $markedKey = $curKey;
                $curKey = '';
                // Find the second most off value
                foreach ($chart as $key => $value) {
                    if($key == $firstKey || $key == $markedKey){
                        continue;
                    }
                    if($value > round($value)){ // Only care if we are rounding down by default...
                        if($value - round($value) > $curVal){
                            $curVal = $value - round($value);
                            $curKey = $key;
                        }
                    }
                }
                $temp[$curKey]++;
            }
            break;
        case 1:
            $curVal = 0;
            $curKey = '';
            foreach ($chart as $key => $value) {
                if($key == $firstKey){
                    continue;
                }
                if($value < round($value)){ // Only care if we are rounding up by default...
                    if(round($value) - $value > $curVal){
                        $curVal = round($value) - $value;
                        $curKey = $key;
                    }
                }
            }
            $temp[$curKey]--;
            break;
        case 2:
            // Subtract 1 from the two 'most off' values
            $curVal = 0;
            $curKey = '';
            // Find the first value that is 'most off'
            foreach ($chart as $key => $value) {
                if($key == $firstKey){
                    continue;
                }
                if($value < round($value)){ // Only care if we are rounding up by default...
                    if(round($value) - $value > $curVal){
                        $curVal = round($value) - $value;
                        $curKey = $key;
                    }
                }
            }
            $temp[$curKey]--;
            $curVal = 0;
            $markedKey = $curKey;
            $curKey = '';
            // Find the second value that is 'most off'
            foreach ($chart as $key => $value) {
                if($key == $firstKey || $key == $markedKey){
                    continue;
                }
                if($value < round($value)){ // Only care if we are rounding up by default...
                    if(round($value) - $value > $curVal){
                        $curVal = round($value) - $value;
                        $curKey = $key;
                    }
                }
            }
            $temp[$curKey]--;
            break;
        default:
            echo "Shouln't get here EVERRRRRR!! Count value = ".$count."\n";
    }
    return $temp;
}

function prettifyChart($chart, $firstKey){
    // Convert to printable format for output
    $formatedChart = array();
    $old_val = "0";
    foreach ($chart as $key => $value) {
        if($key == $firstKey){
            $formatedChart[$key] = $value;
            continue;
        }

        if(($value + $old_val) > $old_val + 1){
            $formatedChart[$key] = ($old_val + 1)." - ".($value + $old_val);
            $old_val += $value;
        }
        elseif(($value + $old_val) == $old_val + 1){
            $formatedChart[$key] = ($value + $old_val);
            $old_val += $value;
        }
        else{
            $formatedChart[$key] = "-";
        }

    }

    return $formatedChart;
}