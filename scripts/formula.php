<?php
// Performs simulation and output results.

// **********Setup
require 'BatterFormula.php';
require 'PitcherFormula.php';
require 'data.php';
include 'maths.php';

$randomMethodList = ['discrete','continuous','single'];
// Command line usage
if(// Input errors
    count($argv) < 5 ||
    (isset($argv[5]) && !in_array($argv[5],$randomMethodList)) ||
    (isset($argv[6]) && (string)(int)($argv[6]) != $argv[6])
    ){
    print "\nArgument usage: u p db Rpath [ dist [ num_opp ] ]\n
        u = MySQL username\n
        p = MySQL password\n
        db = Database name\n
        Rpath = path to R executable\n
        dist = Method to generate random opponents. Default is 'discrete'. Options:'discrete','continuous','single'\n
        num_opp = Number of opponents of each kind to generate. Default is 200. If 'single' is chosen num_opp is always 1.\n";
    exit();
}
//
// **********Get real data
// Setup
$batters = array();
$pitchers = array();
$mysqli = mysqli_connect("127.0.0.1", $argv[1], $argv[2], $argv[3]);
if (!$mysqli) {
    die("Failed to connect to MySQL: (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
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
$rscript_count = '200';
if(isset($argv[5])){
    $type = $argv[5];
}
if(isset($argv[6])){
    $rscript_count = $argv[6];
}
if($type != "single"){
    $RScriptCmdArgs = $argv[1].' '.$argv[2].' '.$argv[3].' '.$type.' '.$rscript_count;
    $cmd = $argv[4].' '.dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'r'.DIRECTORY_SEPARATOR.'script.R'.' '.$RScriptCmdArgs;
    echo "\n***\nPassing R script the args: ".$RScriptCmdArgs."\n***\n";
    exec($cmd, $json);
    $json = $json[0];
    try {
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
    catch (Exception $e){
        echo "\n R script returned NULL.\nCan't proceed.\n";
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
//print_r($bCards);
//print_r(getCardAverages($bCards));

$pCards = playersToCards($pitchers, 'p', $avgBattingOpponent);
//print_r($pCards);
//print_r(getCardAverages($pCards));

// Print results file
$filename = "formula_output" . time() . ".txt";
//file_put_contents("formula_output.txt",print_r($batters, true),FILE_APPEND);
file_put_contents($filename,print_r($bCards, true));//,FILE_APPEND);
//file_put_contents("formula_output.txt",print_r($pitchers, true),FILE_APPEND);
file_put_contents($filename,print_r($pCards, true),FILE_APPEND);

//print_r(averageMetaOnbase(getCardAverages($bCards),getCardAverages($pCards)));

$mysqli->close();

// Transform player data into card charts
function playersToCards($players, $type, $avgOpp){
    switch ($type) {
        case 'b':
            $minVal = 4; // Cards will be calculated for these values.
            $maxVal = 12;
            $ob_ctrl = 'OB';
            break;
        case 'p':
            $minVal = 0;
            $maxVal = 6;
            $ob_ctrl = 'C';
            break;
        default:
            echo 'You did not enter a valid player type.';
            break;
    }
    $cards = array();
    // Get card of all batters
    $inc = 0;
    foreach ($players as $num => $player) {
        print "Processing player ".++$inc.": ".$player->real['nameFirst']." ".$player->real['nameLast']."\n";
        $diffs = array();
        $difsums = array();
        $result = array();
        // Loop through number of outs on the card to find ideal amount
        for ($index = $minVal; $index <= $maxVal; $index++) {
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
