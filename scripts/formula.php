

<?php
// *************************
// Setup
// *************************
require 'BatterFormula.php';
require 'PitcherFormula.php';
require 'data.php';
include 'maths.php';
// Change 1
// Change 2
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
echo 'Returned '.$b_result->num_rows.' batters.';

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
echo 'Returned '.$p_result->num_rows.' pitchers.';

while($row = $p_result->fetch_array(MYSQLI_ASSOC)){
    $pitchers[] = new PitcherFormula($row);
}
    


// Narrow down the player population from the comprehensive arrays read in from db.
// Right now it is all done in query (> 50 G and > 100 AB respectively)

// while(canGetMoreRealistic?):
//      1. Get card of each batter
//      2. Calculate average batter
//      3. Get card of each pitcher
//      4. Calculate average pitcher

$bCards = playersToCards($batters, 'b');
//print_r($batCards);
print_r(getCardAverages($bCards));

$pCards = playersToCards($pitchers, 'p');
//print_r($batCards);
print_r(getCardAverages($pCards));

print_r(averageMetaOnbase(getCardAverages($bCards),getCardAverages($pCards)));

$mysqli->close();

// Transform player data into card charts
function playersToCards($players, $type){
    switch ($type) {
        case 'b':
            $avgOpp = array('C' => 3.1, 'PU' => 2, 'SO' => 4.5, 'GB' => 5.5, 'FB' => 4, 'BB' => 1.5, '1B' => 1.8, '2B' => .65, 'HR' => .05);
            $minNumOuts = 1; // These values will be used to pick the
            $maxNumOuts = 7; // most appropriate OB vs. num outs.
            $ob_ctrl = 'OB';
            break;
        case 'p':
            $avgOpp = array('OB' => 7.5, 'SO' => 1.15, 'GB' => 1.77, 'FB' => 1.09, 'BB' => 4.7, '1B' => 6.6, '1B+' => .41, '2B' => 1.96, '3B' => .34, 'HR' => 1.98);
            $minNumOuts = 13;
            $maxNumOuts = 19;
            $ob_ctrl = 'C';
            break;
        default:
            echo 'You did not enter a valid player type.';
            break;
    }
    $cards = array();
    // Get card of all batters
    foreach ($players as $num => $player) {
        $diffs = array();
        $result = array();
        // See which number of outs on the card is the best fit
        for ($index = $minNumOuts; $index <= $maxNumOuts; $index++) {
            $result[$index] = $player->getRawCard($avgOpp, $index); // index normally 1 - 7
            //$result = $pitchers[0]->getRawCard($avgbatter, $index); // index normally 13 - 19 ?
            $d = $result[$index];
            foreach ($result[$index] as $key => $value) {
                $r = round($value);
                // Give OB/C increased weight (why?)
                $key == $ob_ctrl ? $d[$key] = $value < $r ? ($r - $value)*2 : ($value - $r)*2 : $d[$key] = $value < $r ? $r - $value : $value - $r;
            }
            $diffs[$index] = array_sum($d);
            //print_r($batResult[$index]);

        }
        //print_r($diffs);
        //$batCards[0] = (BatterFormula::processChart($batResult[array_search(min($diffs),$diffs)]));
        $cards[$num] = ($result[array_search(min($diffs),$diffs)]);
        //print_r($batCards[$num]);
    }
    return $cards;
}