

<?php
// *************************
// Setup
// *************************
require 'BatterFormula.php';
require 'PitcherFormula.php';
require 'data.php';

// Connect to db
$mysqli = new mysqli("127.0.0.1", $argv[1], $argv[2], "mlb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

// Initialize players arrays
$batters = array();
$pitchers = array();

//prepare
$b_stmt = $mysqli->prepare($b_query);
// Run query
//$result = $mysqli->query($query);
$b_stmt->execute();
$b_result = $b_stmt->get_result();
$b_stmt->close();


while($row = $b_result->fetch_array(MYSQLI_ASSOC)){
    $batters[] = new BatterFormula($row);
}

//prepare
$p_stmt = $mysqli->prepare($p_query);
// Run query
//$result = $mysqli->query($query);
$p_stmt->execute();
$p_result = $p_stmt->get_result();
$p_stmt->close();


while($row = $p_result->fetch_array(MYSQLI_ASSOC)){
    $pitchers[] = new PitcherFormula($row);
}
    
// Initialize average pitcher & batter from historical charts
$avgPitcher = array('C' => 3.1, 'PU' => 2, 'SO' => 4.5, 'GB' => 5.5, 'FB' => 4, 'BB' => 1.5, '1B' => 1.8, '2B' => .65, 'HR' => .05);
$avgbatter = array('OB' => 7.5, 'SO' => 1.15, 'GB' => 1.77, 'FB' => 1.09, 'BB' => 4.7, '1B' => 6.6, '1B+' => .41, '2B' => 1.96, '3B' => .34, 'HR' => 1.98);


// Narrow down the player population from the comprehensive arrays read in from db.
// Right now it is all done in query (> 50 G and > 100 AB respectively)

// while(canGetMoreRealistic?):
//      1. Get card of each batter
//      2. Calculate average batter
//      3. Get card of each pitcher
//      4. Calculate average pitcher
// 
//

// Start 1. Get card of each batter
$batCards = array();
// Get card of all batters
foreach ($batters as $num => $bat) {
    $diffs = array();
    $batResult = array();
    // See which number of outs on the card is the best fit
    for ($index = 1; $index <= 7; $index++) {
        $batResult[$index] = $bat->getRawCard($avgPitcher, $index); // index normally 1 - 7
        //$result = $pitchers[0]->getRawCard($avgbatter, $index); // index normally 13 - 19 ?
        $d = $batResult[$index];
        foreach ($batResult[$index] as $key => $value) {
            $r = round($value);
            // Give OB increased weight
            $key == 'OB' ? $d[$key] = $value < $r ? ($r - $value)*2 : ($value - $r)*2 : $d[$key] = $value < $r ? $r - $value : $value - $r;
        }
        $diffs[$index] = array_sum($d);
        //print_r($batResult[$index]);

    }
    //print_r($diffs);
    //$batCards[0] = (BatterFormula::processChart($batResult[array_search(min($diffs),$diffs)]));
    $batCards[$num] = ($batResult[array_search(min($diffs),$diffs)]);
    //print_r($batCards[$num]);
}
//print_r($batCards);
// End 1. Get card of each batter

// Print averages
$avgs = array_fill_keys(array_keys($batCards[0]), 0);
print_r($avgs);
foreach ($batCards as $key => $value) {
    foreach ($value as $k => $v) {
        $avgs[$k] += $v;
    }
}
print_r($avgs);
// Get actual averages
$avgs = array_map( function($val,$c) { return $val / $c; }, $avgs, array_fill(0, count($avgs), count($batCards)));
print_r($avgs);


//foreach ($batters as $key => $value) {
//    $result = $value->getCard($avgPitcher, 4);
//    print_r($result);
//    echo (array_sum($result) - $result['OB']);
//}
//echo computeBattersChartNum(computeOB(4,$obp,3,16), $b2B, $bPA, $pC, $p2B);



$mysqli->close();




    
        //$num_SO; // q
        //$num_GB; // r
        //$num_FB; // s
        //$num_outs; // p = q+r+s
        //
        //$num_BB; // t
        //$num_1B; // u
        //$num_2B; // v
        //$num_3B; // w
        //$num_HR; // x
        //$OB = 0;     // y 


// System of equations to get chart. Kyle Seager 2014 on Sep 22.
// Wolphram alpha, then php

//$num_2B = ($b2B/$bPA - ((20-(8-4))/20*1/20))*20/(8-4)*20; echo $num_2B;
//    27/619 = ((y-4)/20*v/20) + ((20-(y-4))/20*.95/20) // Doubles equation // y,v
//    .34 = 4/20*(20-(y-4))/20 + (20-q-r-s)/20*(y-4)/20 // OB equation    // y,q,r,s
//    4/619 = (y-4)/20*w/20 // Triples equation                           // y,w
//    25/619 = ((y-4)/20*x/20) + ((20-(y-4))/20*.05/20) // Homers equation// y,x
//    99/619 = ((y-4)/20*u/20) + ((20-(y-4))/20*2/20) // Singles equation // y,u
//    51/619 = ((y-4)/20*t/20) + ((20-(y-4))/20*2/20) // Walks equation   // y,t
//    300/619 = ((y-4)/20*p/20) + ((20-(y-4))/20*2/20) // Outs equation   // y,p

// .3328 = 4/20*(20-(y-4))/20 + (20-(2*(619*y + 45144)/(619*(y-4))))/20*(y-4)/20

//    113/619 = ((y-4)/20*q/20) + ((20-(y-4))/20*8/20) // K's equation    // y,q
//    (.64*(568-113-155)/(1+.64))/619 = ((y-4)/20*r/20) + ((20-(y-4))/20*4/20) // GB outs equation // y,r
//    (568-113-155-(.64*(568-113-155)/(1+.64)))/619 = ((y-4)/20*s/20) + ((20-(y-4))/20*4/20) // FB outs equation // y,s


// Solve for y plugging things into the OB equation!
// From Wolphram Alpha:
//  Boundary: y != 4
//  q = 8*(619*y - 9206)/(619*(y-4))
//  r = 4*(25379*y - 129096)/(25379*(y-4))
//  s = 4*(25379*y + 140904)/(25379*(y-4))

// .34 = 4/20*(20-(y-4))/20 + (20-(8*(619*y - 9206)/(619*(y-4)))-(4*(25379*y - 129096)/(25379*(y-4)))-(4*(25379*y + 140904)/(25379*(y-4))))/20*(y-4)/20
// .34 = 5/20*(20-(y-0))/20 + (20-(8-(53840/(619*y)))-(4-(110320/(25379*y)))-(4+(969680/(25379*y))))/20*(y-0)/20

