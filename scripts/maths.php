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