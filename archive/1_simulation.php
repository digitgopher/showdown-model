

<?php
    require 'BatterStatistics.php';
    require 'PitcherStatistics.php';
    $batterStatistics = 'BatterStatistics';
    //$batterStatistics::$calc_PU;

    // Connect to db
    $mysqli = new mysqli("127.0.0.1", $argv[1], $argv[2], "mlb");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }

    // Initialize players
    $batters = array();
    $pitchers = array();
    
    // Get batters
    $query = "SELECT
                m.nameFirst,
                m.nameLast,
                (b.SO / b.H) as SO,
                (b.AB - b.H - b.SO) / b.AB * (b.SH / (b.SH + b.SF)) as GB,
                (b.AB - b.H - b.SO) / b.AB * (b.SF / (b.SH + b.SF)) as FB,
                (b.BB / b.H) as BB,
                ((b.H - b.2B - b.3B - b.HR) / b.H) as 1B,
                (b.2B / b.H) as 2B,
                (b.3B / b.H) as 3B,
                (b.HR / b.H) as HR,
                b.H / b.AB as Average,
                (b.H + b.BB + b.HBP) / (b.AB + b.BB + b.HBP + b.SF) as OBP,
                ((b.H - b.2B - b.3B - b.HR) + 2*b.2B + 3*b.3B + 4*b.HR) / b.AB as SLG,
                b.G,
                b.R,
                b.RBI,
                b.SB,
                b.CS
        FROM master m
        INNER JOIN batting b ON m.playerID = b.playerID
        WHERE b.yearID = '2000' AND b.teamID = 'BAL'
        ORDER BY b.AB DESC
        Limit 0,15
        ;";
    // Run query
    $result = $mysqli->query($query);

    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $hello = $batters[] = new BatterStatistics($row,1,5);
        //echo $row['SO'];
        //$hello->printeverything();
        //print_r($row);
    }
    
    // Get pitchers
    $query = "SELECT
                m.nameFirst,
                m.nameLast,
                (p.BFP - p.H - p.BB - p.SO) * (p.HR / 100) / p.H as PU,
                p.SO / p.IPouts as SO,
                (p.H - p.HR) / p.H as GB,
                p.HR / p.H as FB,
                p.BB / p.IPouts as BB,
                (p.H - p.HR) / p.IPouts as 1B,
                (p.H - p.HR - (p.H / 2.5)) / p.IPouts as 2B,
                p.HR * 5 / p.IPouts as HR,
                (p.SO / p.BB) / 10 as Control,
                p.W,
                p.L,
                p.G,
                p.GS,
                p.SV,
                p.IPouts,
                p.ERA
        FROM master m
        INNER JOIN pitching p ON m.playerID = p.playerID
        WHERE p.yearID = '2000' AND p.teamID = 'SEA'
        ORDER BY p.G DESC 
        Limit 0,10
        ;";//AND p.teamID = 'SEA'
    
        // Run query
    $result = $mysqli->query($query);

    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $hello = $pitchers[] = new PitcherStatistics($row,1,0);
        //echo $row['SO'];
        //$hello->printeverything();
        //print_r($row);
    }
    
    //$averageBatter = new BatterStatistics('Brett',1.0,10);
    //$averageBatter->printeverything();
    //$averagePitcher = new PitcherStatistics();



    //While (not within tolerances)
    // Num times to run the Monte Carlo
    $chartOfDists = array('PU' => '', 'SO' => '', 'GB' => '', 'FB' => '', 'BB' => '', '1B' => '', '2B' => '', '3B' => '', 'HR' => '');
    // numIterations has to be at least 10,000 for an increment value of .001, based off eyeballing different data
//    $numSims = 10;
    $numIterations = 1000;
//    $pitchersAverages = array();
//    $battersAverages = array();
    for($i = 0; $i < $numIterations; $i++){
        foreach ($pitchers as $p_index => $pitcher) {
            foreach ($batters as $b_index => $batter) {
                // Roll for control, then get result
                if(mt_rand(1, 20) /*+ $pitcher->control*/ > 5/*$batter->onbase*/){
                    // Roll for result on pitchers chart
                    $result = $pitcher->getChartResult(100);
                }
                else{
//                  // Roll for result on batters chart
                    $result = $batter->getChartResult(100);
                }
                // Deal with result
                $batterDists = $batter->incrementTotalAndRecalculate($result, .001);
                $pitcherDists = $pitcher->incrementTotalAndRecalculate($result, .001);
//                foreach ($batterDists as $key => $value) {
//                    $chartOfDists[$key][] = $value;
//                }
//                $batter->printeverything();
//                $pitcher->printeverything();
                //$pitcher->incrementTotal($result);
            }
        }
//        // battersAverage[0] holds the average of the 10 batters[0] results
//        $count = 0;
//        foreach ($batters as $key => $batterObject) {
//            $dummyArray = array();
//            foreach ($batterObject->getFinalChart(20) as $key => $value) {
//                $dummyArray[$key] = $value;
//            };
//            foreach ($value as $key => $value) {
//                
//            }
//            $battersAverages[$count] = $dummyArray
//        }
//        foreach ($battersAverages as $key => $value) {
//            $value /= $numSims;
//        }
    }
    
//    $batters[1]->printeverything();
//    $batters[4]->printeverything();
//    $batters[0]->printeverything();
//    $batters[12]->printeverything();
//    $batters[10]->printeverything();
    foreach ($batters as $value) {
        print_r($value->getFinalChartFormatted(20));
    }
    
//    $pitchers[0]->printeverything();
//    $pitchers[1]->printeverything();
//    $pitchers[2]->printeverything();
    //print_r($batters[0]->getFinalChart(100));
//    print_r($batters[1]->getFinalChartFormatted(20));
//    print_r($batters[4]->getFinalChartFormatted(20));
//    print_r($batters[0]->getFinalChartFormatted(20));
//    print_r($batters[12]->getFinalChartFormatted(20));
//    print_r($batters[11]->getFinalChartFormatted(20));
    
//    print_r($battersAverage[1]->getFinalChartFormatted(20));
//    print_r($battersAverage[4]->getFinalChartFormatted(20));
//    print_r($battersAverage[0]->getFinalChartFormatted(20));
//    print_r($battersAverage[12]->getFinalChartFormatted(20));
//    print_r($battersAverage[11]->getFinalChartFormatted(20));
//    $batters[13]->printeverything();
//    print_r($pitchers[0]->getFinalChartFormatted(20));
//    print_r($pitchers[8]->getFinalChartFormatted(20));
    foreach ($pitchers as $key => $value) {
        print_r($value->getFinalChartFormatted(20));
    }
    //file_put_contents('aa_simulation_results.txt',print_r($chartOfDists /*print_r($batters[0]->getStateOfBatter()*/, true), FILE_APPEND);
//    }
    $mysqli->close();
    //session_destroy();

