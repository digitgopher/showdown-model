<?php
// Performs simulation and output results.

// **********Setup
$pathToRExecutable = 'C:\dev\R-3.1.2\bin\Rscript.exe';
require 'BatterFormula.php';
require 'PitcherFormula.php';
require 'data.php';
include 'maths.php';

// Command line usage
if(count($argv) < 3){
    print "\nArgument usage: u,p[,dist[,num_opp]]\n
        u = MySQL username\n
        p = MySQL password\n
        dist = Method to generate random opponents. Default is 'discrete'. Options:'discrete','continuous','single'\n
        num_opp = Number of opponents of each kind to generate. Default is 2000. If 'single' is chosen num_opp is always 1.";
    exit();
}
// 
// **********Get real data
// Setup
$batters = array();
$pitchers = array();
$mysqli = new mysqli("127.0.0.1", $argv[1], $argv[2], "mlb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
// Get batters
$b_stmt = $mysqli->prepare($b_query);
$b_stmt->execute();
$b_result = $b_stmt->get_result();
$b_stmt->close();
echo 'Returned '.$b_result->num_rows.' batters.';
while($row = $b_result->fetch_array(MYSQLI_ASSOC)){
    $batters[] = new BatterFormula($row);
}

// Get pitchers
$p_stmt = $mysqli->prepare($p_query);
$p_stmt->execute();
$p_result = $p_stmt->get_result();
$p_stmt->close();
echo 'Returned '.$p_result->num_rows.' pitchers.';
while($row = $p_result->fetch_array(MYSQLI_ASSOC)){
    $pitchers[] = new PitcherFormula($row);
}

// **********Get game historical data to calculate against
// Initialize
$avgPitchingOpponent = array('C' => 3.1, 'PU' => 2, 'SO' => 4.5, 'GB' => 5.5, 'FB' => 4, 'BB' => 1.5, '1B' => 1.8, '2B' => .65, 'HR' => .05);
$avgBattingOpponent = array('OB' => 7.5, 'SO' => 1.15, 'GB' => 1.77, 'FB' => 1.09, 'BB' => 4.7, '1B' => 6.6, '1B+' => .41, '2B' => 1.96, '3B' => .34, 'HR' => 1.98);

// Customize arguments for R script
$type = "discrete";
$rscript_count = '2000';
if(isset($argv[3])){
    $type = $argv[3];
}
if(isset($argv[4])){
    $rscript_count = $argv[4];
}
if($type != "single"){
    $RScriptCmdArgs = $argv[1].' '.$argv[2].' '.$type.' '.$rscript_count;
    $cmd = $pathToRExecutable.' '.dirname(__FILE__).'\..\r\script.R'.' '.$RScriptCmdArgs;
    echo "\n***\nPassing R script the args: ".$RScriptCmdArgs."\n***\n";
    exec($cmd, $json);
    if(count($json) > 1){ // The first result is a log of defining MYSQL_HOME
        $json = $json[1]; // The first result is a log of defining MYSQL_HOME, so this is the actual returned result
        // Transform returned json into a formatted set of batters
        $json = substr($json, 4);
        $json = stripslashes($json);
        $json = trim($json, '"');
        $json = explode("},{", $json); // need to add in the braces again after this...
        $bat_json = $json[0]."}";
        $pit_json = "{".$json[1];
        $bat_json = json_decode($bat_json, true);
        $pit_json = json_decode($pit_json, true);
    //    print_r($bat_json);print_r($pit_json);exit;
    }
    else{
        echo "\n R script returned NULL.\nCan't proceed.";
        exit();
    }
    echo "\n***\nPassing distribution of players from R script to calculate against.\n***\n";
    $avgPitchingOpponent = $pit_json;
    $avgBattingOpponent = $bat_json;
}
else{
    echo "\n***\nPassing a single average player.\n***\n";
}

// Somewhere, this will need to be done:
// Narrow down the player population from the comprehensive arrays read in from db.
// Right now it is all done in query (> 50 G and > 100 AB respectively)

//print_r($batters);
//print_r($avgPitchingOpponent);exit;
$bCards = playersToCards($batters, 'b', $avgPitchingOpponent);
print_r($bCards);
//print_r(getCardAverages($bCards));

$pCards = playersToCards($pitchers, 'p', $avgBattingOpponent);
print_r($pCards);
//print_r(getCardAverages($pCards));

//print_r(averageMetaOnbase(getCardAverages($bCards),getCardAverages($pCards)));

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
        $difsums = array();
        $result = array();
        // Loop through number of outs on the card to find ideal amount
        for ($index = $minNumOuts; $index <= $maxNumOuts; $index++) {
            $result[$index] = $player->getRawCard($avgOpp, $index);
            $diffs[$index] = $player->computePercentDifferent(processChart($result[$index]), $avgOpp);
        }
        $index--; // get back to a usable value!
        $difsums = array();
        foreach ($diffs as $key => $value) {
            // TODO: make this more accurate
            $difsums[$key] = array_sum($value);
        }
        // Get card with least error and add it to be returned
        $cards[$num] = processChart($result[array_search(min($difsums),$difsums)]);
    }
    return $cards;
}