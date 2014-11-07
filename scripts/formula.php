

<?php
// *************************
// Setup
// *************************
$pathToRExecutable = 'C:\dev\R-3.1.2\bin\Rscript.exe';
require 'BatterFormula.php';
require 'PitcherFormula.php';
require 'data.php';
include 'maths.php';

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

// Run R script
$cmd = $pathToRExecutable.' '.dirname(__FILE__).'\..\r\script.R';
echo $cmd;
exec($cmd, $json);
// Transform returned json into a formatted set of batters
$json = substr($json[0], 4);
$json = stripslashes($json);
$json = trim($json, '"');
$json = explode("},{", $json); // need to add in the braces again after this...
$bat_json = $json[0]."}";
$pit_json = "{".$json[1];
$bat_json = json_decode($bat_json, true);
$pit_json = json_decode($pit_json, true);
//print_r($bat_json);print_r($pit_json);exit;


// Narrow down the player population from the comprehensive arrays read in from db.
// Right now it is all done in query (> 50 G and > 100 AB respectively)

// while(canGetMoreRealistic?):
//      1. Get card of each batter
//      2. Calculate average batter
//      3. Get card of each pitcher
//      4. Calculate average pitcher

$avgPitchingOpponent = array('C' => 3.1, 'PU' => 2, 'SO' => 4.5, 'GB' => 5.5, 'FB' => 4, 'BB' => 1.5, '1B' => 1.8, '2B' => .65, 'HR' => .05);
$avgBattingOpponent = array('OB' => 7.5, 'SO' => 1.15, 'GB' => 1.77, 'FB' => 1.09, 'BB' => 4.7, '1B' => 6.6, '1B+' => .41, '2B' => 1.96, '3B' => .34, 'HR' => 1.98);

if($argv[3] == true){
    echo "\n*************\nPassing distribution to calculate against rather than simply an average player.\n*************\n";
    $avgPitchingOpponent = $pit_json;
    $avgBattingOpponent = $bat_json;
}
$bCards = playersToCards($batters, 'b', $avgPitchingOpponent);
//print_r($batCards);
print_r(getCardAverages($bCards));

$pCards = playersToCards($pitchers, 'p', $avgBattingOpponent);
//print_r($batCards);
print_r(getCardAverages($pCards));

print_r(averageMetaOnbase(getCardAverages($bCards),getCardAverages($pCards)));

$mysqli->close();

// Transform player data into card charts
function playersToCards($players, $type, $avgOpp){
    switch ($type) {
        case 'b':
            $minNumOuts = 1; // These values will be used to pick the
            $maxNumOuts = 7; // most appropriate OB vs. num outs.
            $ob_ctrl = 'OB';
            break;
        case 'p':
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
            $result[$index] = $player->getRawCard($avgOpp, $index);
            $d = $result[$index];
            foreach ($result[$index] as $key => $value) {
                $r = round($value);
                // Give OB/C increased weight (why?)
                $key == $ob_ctrl ? $d[$key] = $value < $r ? ($r - $value)*2 : ($value - $r)*2 : $d[$key] = $value < $r ? $r - $value : $value - $r;
            }
            $diffs[$index] = array_sum($d);
        }
        //print_r($diffs);
        //$batCards[0] = (BatterFormula::processChart($batResult[array_search(min($diffs),$diffs)]));
        $cards[$num] = ($result[array_search(min($diffs),$diffs)]);
        //print_r($batCards[$num]);
    }
    return $cards;
}