

<?php
// *************************
// Setup
// *************************
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
    
    // Double entries for Angels,Marlins,Montreal,philly?
    $teams = ['ANA','ARI','ATL','BAL','BOS','CHA','CHN','CIN','CLE','COL',
              'DET','FLO','HOU','KCA','LAA','LAN','MIA','MIL','MIN','MON',
              'NYA','NYN','OAK','PHI','PHN','PIT','SDN','SEA','SFN','SLN',
              'TBA','TEX','TOR','WAS'];
    
//*****************************
//  Step 1: Get average batter chart distribution from existing cards, and find different
//  pitcher charts' OOB against that distribution.
//  Completed with script: pitchers-norm_dist_batter_set.php along with excel manipulation, and imported below.
//*****************************
    // Pitcher chart to OOB(*10000) mapping
    $chart_oob_map = [
        [1072,[6,19]],
        [1421,[5,19]],
        [1534,[6,18]],
        [1790,[4,19]],
        [1859,[5,18]],
        [1995,[6,17]],
        [2162,[3,19]],
        [2203,[4,18]],
        [2298,[5,17]],
        [2457,[6,16]],
        [2534,[2,19]],
        [2550,[3,18]],
        [2616,[4,17]],
        [2736,[5,16]],
        [2897,[2,18]],
        [2919,[6,15]],
        [2938,[3,17]],
        [3030,[4,16]],
        [3174,[5,15]],
        [3244,[1,18]],
        [3260,[2,17]],
        [3327,[3,16]],
        [3380,[6,14]],
        [3443,[4,15]],
        [3582,[1,17]],
        [3591,[0,18]],
        [3612,[5,14]],
        [3624,[2,16]],
        [3715,[3,15]],
        [3842,[6,13]],
        [3857,[4,14]],
        [3904,[0,17]],
        [3921,[1,16]],
        [3987,[2,15]],
        [4050,[5,13]],
        [4104,[3,14]],
        [4218,[0,16]],
        [4259,[1,15]],
        [4270,[4,13]],
        [4351,[2,14]],
        [4492,[3,13]],
        [4531,[0,15]],
        [4598,[1,14]],
        [4684,[4,12]],
        [4714,[2,13]],
        [4845,[0,14]],
        [4881,[3,12]],
        [4936,[1,13]],
        [5078,[2,12]],
        [5158,[0,13]],
        [5275,[1,12]],
        [5472,[0,12]]];
    
//*****************************
//  Step 2: 
//*****************************
    //Pitchers
    // Initiallize for the UNION ALL
    $pitchersQuery = "(
                        select                 
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
                              (H + BB + HBP)/(BFP - IBB) as OOB,
                              p.W,
                              p.L,
                              p.G,
                              p.GS,
                              p.SV,
                              p.IPouts,
                              p.ERA
                        FROM master m
                        INNER JOIN pitching p ON m.playerID = p.playerID
                        where p.yearID = '2000' AND p.teamID = 'garbageshouldreturnnone'
                        order by p.GS desc
                        LIMIT 5
                      )";
    // Build query with all teams, 2 sections for starters and relievers
    foreach ($teams as $ident) {
        $pitchersQuery .= "UNION ALL(
                            select                 
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
                                  (H + BB + HBP)/(BFP - IBB) as OOB,
                                  p.W,
                                  p.L,
                                  p.G,
                                  p.GS,
                                  p.SV,
                                  p.IPouts,
                                  p.ERA
                            FROM master m
                            INNER JOIN pitching p ON m.playerID = p.playerID
                            where p.yearID = '2000' AND p.teamID = '".$ident."'
                            order by p.GS desc
                            LIMIT 5
                          )"."UNION ALL(
                            select                 
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
                                  (H + BB + HBP)/(BFP - IBB) as OOB,
                                  p.W,
                                  p.L,
                                  p.G,
                                  p.GS,
                                  p.SV,
                                  p.IPouts,
                                  p.ERA
                            FROM master m
                            INNER JOIN pitching p ON m.playerID = p.playerID
                            where p.yearID = '2000' AND p.teamID = '".$ident."'
                            order by p.G desc
                            LIMIT 6
                          )";
    }
//    echo $pitchersQuery;exit;
    
    //prepare
    $stmt = $mysqli->prepare($pitchersQuery);
    // Run query
    $stmt->execute();
    $pitchersResult = $stmt->get_result();
    $stmt->close();
    
    // Get batters
    $query = "SELECT
                m.nameFirst,
                m.nameLast,
                (b.SO / b.H) as SO,
                ((b.SH + b.SB) / (b.HR + 3)) / (((b.SH + b.SB) / (b.HR + 3)) + ((b.HR + b.2B + b.BB) / b.AB)) as GB,
                ((b.HR + b.2B + b.BB) / b.AB) / (((b.SH + b.SB) / (b.HR + 3)) + ((b.HR + b.2B + b.BB) / b.AB)) as FB,
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
        WHERE b.yearID = '2000'
        ORDER BY b.AB DESC
        Limit 0,3
        ;";
    
    //prepare
    $stmt = $mysqli->prepare($query);
    // Run query
    //$result = $mysqli->query($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    while($row = $result->fetch_array(MYSQLI_ASSOC)){
        $hello = $batters[] = new BatterStatistics($row,1);
        //echo $row['SO'];
        //$hello->printeverything();
        //print_r($row);
    }
    
//    // Get pitchers
//    $query = "SELECT
//                m.nameFirst,
//                m.nameLast,
//                (p.BFP - p.H - p.BB - p.SO) * (p.HR / 100) / p.H as PU,
//                p.SO / p.IPouts as SO,
//                (p.H - p.HR) / p.H as GB,
//                p.HR / p.H as FB,
//                p.BB / p.IPouts as BB,
//                (p.H - p.HR) / p.IPouts as 1B,
//                (p.H - p.HR - (p.H / 2.5)) / p.IPouts as 2B,
//                p.HR * 5 / p.IPouts as HR,
//                (H + BB + HBP)/(BFP - IBB) as OOB,
//                p.W,
//                p.L,
//                p.G,
//                p.GS,
//                p.SV,
//                p.IPouts,
//                p.ERA
//        FROM master m
//        INNER JOIN pitching p ON m.playerID = p.playerID
//        WHERE p.yearID = '2000' AND p.teamID = 'SEA'
//        ORDER BY p.G DESC 
//        Limit 0,3
//        ;";//AND p.teamID = 'SEA'
    
        // Run query
    //$result = $mysqli->query($query);

    


    while($row = $pitchersResult->fetch_array(MYSQLI_ASSOC)){
        $hello = $pitchers[] = new PitcherStatistics($row,1,$chart_oob_map);
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
    $numIterations = 10000;
//    $pitchersAverages = array();
//    $battersAverages = array();
    for($i = 0; $i < $numIterations; $i++){
        foreach ($pitchers as $p_index => $pitcher) {
            foreach ($batters as $b_index => $batter) {
                // Roll for control, then get result
                if(mt_rand(1, 20) + $pitcher->control > $batter->onbase){
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
    
    
    
//    foreach ($batters as $value) {
//        print_r($value->getFinalChartFormatted(20));
//    }
//    
    
    
    
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
        echo 'Control: '.$value->control;
        echo 'Outs: '.$value->num_outs;
        print_r($value->getFinalChartFormatted(20));
    }
//    $batters[1]->printeverything();
    //file_put_contents('aa_simulation_results.txt',print_r($chartOfDists /*print_r($batters[0]->getStateOfBatter()*/, true), FILE_APPEND);
//    }
    $batters[1]->printeverything();
    $pitchers[1]->printeverything();
    $mysqli->close();
    //session_destroy();

