<?php
class BatterFormula
{
    // Real-life statistical values, pulled in from database
    public $real;

    // Constructor for a batter
    function __construct($rawData){
        // Set real-life statistics data structure
        $this->real = $rawData;
    }
    
    // Get chart from data
    public function getRawCard($avgPitcher, $OB){
        
        $batted_outs = $this->real['AB'] - $this->real['SO'] - $this->real['H'];
        $GBouts_tot = $this->real['G/F']*$batted_outs/(1+$this->real['G/F']);
        $FBouts_tot = $batted_outs - $GBouts_tot; // = (1/$this->real['G/F'])*$batted_outs/(1+(1/$this->real['G/F'])); 
        
        // Get onbase, using either an average pitcher or a set of pitchers (generated from a distribution),
        // whatever whas passed in. Parse the different data structures.
        if(!is_array($avgPitcher['C'])){ // only one average pitcher given
            $pC = $avgPitcher['C'];
            $pouts = $avgPitcher['PU'] + $avgPitcher['SO'] + $avgPitcher['GB'] + $avgPitcher['FB'];
            return $this->getRawCardi($avgPitcher, $pC, $OB, $GBouts_tot, $FBouts_tot);
        }
        else{// multiple batters passed in
            $batters = array();
            for ($i = 0; $i < count($avgPitcher['C']); $i++) {
                $pC = $avgPitcher['C'][$i];
                //echo $pC." ";
                $pouts = $avgPitcher['PU'][$i] + $avgPitcher['SO'][$i] + $avgPitcher['GB'][$i] + $avgPitcher['FB'][$i];
                //echo $pouts." ";
                $curPitcher = array();
                foreach ($avgPitcher as $key => $dontcareaboutthis) {
                    $curPitcher[$key] = $avgPitcher[$key][$i];
                }
                //print_r($curBatter);exit;
                $batters[] = $this->getRawCardi($curPitcher, $pC, $OB, $GBouts_tot, $FBouts_tot);;
            }
//            print_r($pitchers);
            // Average them now, after the fact rather than before!
            // Set as 0 so we can ++
            $sums = array_fill_keys(array_keys($batters[0]), 0);
            // Add all categories
            foreach ($batters as $key => $batter) {
                foreach ($batter as $k => $v) {
                    //echo $k; exit;
                    $sums[$k] += $v;
                }
            }
            // Divide to get average
//            print_r($batters);
//            print_r($sums);
            foreach ($sums as $key => &$value) {
                $value /= count($batters);
            }
            //print_r($sums);//exit;
            return $sums;
        }
        echo "Should never get here. Exiting!";
        exit;
    }
    
    // Internal function
    private function getRawCardi($avgPitcher, $pC, $OB, $GBouts_tot, $FBouts_tot){
        // The sum of all the values should add up to PA if it is going to work.
        // Note we have to divide by number of outs so that the calculated negatives don't throw off the balance
        $chart = array('OB' => $OB,
            'SO' => $this->computeBatterNum_B123H($OB, $this->real['SO'], $this->real['PA'], $pC, $avgPitcher['SO']),
            'GB' => $this->computeBatterNum_B123H($OB, $GBouts_tot, $this->real['PA'], $pC, $avgPitcher['GB']) ,
            'FB' => $this->computeBatterNum_B123H($OB, $FBouts_tot, $this->real['PA'], $pC, $avgPitcher['FB'] + $avgPitcher['PU']) ,
            //'Ou' => $this->computeBatterNum_B123H($OB, $bAB-$bH, $this->real['PA'], $pC, $pouts),
            'BB' => $this->computeBatterNum_B123H($OB, $this->real['BB'], $this->real['PA'], $pC, $avgPitcher['BB']), 
            '1B' => $this->computeBatterNum_B123H($OB, $this->real['1B'], $this->real['PA'], $pC, $avgPitcher['1B']),
            '1B+' => 0,
            '2B' => $this->computeBatterNum_B123H($OB, $this->real['2B'], $this->real['PA'], $pC, $avgPitcher['2B']), 
            '3B' => $this->computeBatterNum_B123H($OB, $this->real['3B'], $this->real['PA'], $pC, 0),
            'HR' => $this->computeBatterNum_B123H($OB, $this->real['HR'], $this->real['PA'], $pC, $avgPitcher['HR']));
        
        $negs = array();
        $sumOuts = 0;
        $sumNonOuts = 0;
        // Handle negative chart values: replace with 0 but keep the value
        foreach ($chart as $key => $value) {
            if($value < 0){
                $chart[$key] = 0;
                $negs[$key] = $value;
            }
            else{
                if($key == 'SO' || $key == 'GB' || $key == 'FB'){
                    $sumOuts += $value;
                }
                elseif($key == 'BB' || $key == '1B' || $key == '2B' || $key == '3B' || $key == 'HR'){
                    $sumNonOuts += $value;
                }
                elseif($key == 'OB' || $key == '1B+'){
                    // Do nothing
                }
                else{
                    echo "Should never get here!";
                }
            }
        }
        //print_r(array_sum($chart));print_r(array_sum($negs));echo "\n";
        // Normalize EQUALLY, because we can't make the pitcher worse, as the pitcher is the input
        foreach ($negs as $key => $value) {
            $sum = array_sum($chart) - $chart['OB'] - $chart['1B+'] - $chart[$key];
            foreach ($chart as $k => &$v) {
                if($k != 'OB' && $k != '1B+'){
                    $v += $v / $sum * $value;
                }
            }
        }
        
        // Throw on dummy values, doesn't matter but needs something to keep going
        if(array_sum($chart) - $chart['OB'] == 0){
            $chart = array_map(function($val) { return $val + (20 / 8); },$chart);
        }
        if(round(array_sum($chart) - $chart['OB']) < 20){
            print_r($chart);print_r($negs);printf("%.40f\n", $rrr);exit;
        }
        return $chart;
    }
    

    //function computeBatterOuts($OB, $obp, $pC, $p_outs){
    //    // General form:
    //    // obp = chance of batters chart * chance of getting onbase on batters chart + chance of pitchers chart * chance of getting onbase on pitchers chart
    //    // $obp = ($OB-$pC)/20*(20-$b_outs)/20 + (20-($OB-$pC))/20*(20-$p_outs)/20
    //    //
    //    // Equation solved by WolphramAlpha as follows:
    //    // a = ((b - c)/20*(20-d)/20) + ((20 - (b - c))/20*(20-e)/20) solve for d
    //    // 
    //    // where:
    //    //      a = obp
    //    //      b = OB
    //    //      c = C
    //    //      d = b_outs
    //    //      e = p_outs
    //    //
    //    // Only Restriction: b != c
    //    
    //    $b_outs = (-400*$obp + $p_outs*($OB - $pC - 20) + 400)/($OB - $pC);
    //    // Negative means the average pitcher needs to be worse!
    //    return $b_outs; 
    //}

    // Returns the exact number of slots on charts that should be given if the batter
    // is to have the same performance as real life, per pitcher passed in.
    function computeBatterNum_B123H($OB, $metric, $PA, $pC, $p_result){
        // General form:
        // realLifeCount / plateApperances = chance of batters chart * chance of getting that result on batters chart + chance of pitchers chart * chance of getting that result on pitchers chart
        // Ex for doubles: 
        // $b2B/$bPA = ($OB-$pC)/20*$numDoublesOnBattersChart/20 + (20-($OB-$pC))/20*$numDoublesOnPitchersChart/20
        //
        // Equation solved by WolphramAlpha as follows:
        // a/b = (d-c)/20*f/20 + (20-(d-c))/20*e/20 solve for f
        // 
        // where:
        //      a = metric (real-life statistic; Ex. number of doubles in a season)
        //      b = PA (Plate Appearances)
        //      c = C
        //      d = OB
        //      e = p_result (number of result values on pitcher's chart)
        //      f = b_result (number of result values on batter's chart)
        //
        // Only Restriction: b*c != b*d
        //      essentially meaning OB can't equal Control, and PA can't be 0
        if($PA == 0 || $pC == $OB){
            return 0; // Should this be handled differently??
        }
        $b_result = ($PA*$p_result*($pC - $OB + 20) - 400*$metric)/($PA*($pC - $OB));
        // Negative means the average pitcher needs to be worse!
        return $b_result; 
    }

    // Built to take a representative sample of pitchers, the game calculations all depend on the selection of pitchers!
    function computePercentDifferent($batter, $pitchers){
        $batted_outs = $this->real['AB'] - $this->real['SO'] - $this->real['H'];
        $GBouts_tot = $this->real['G/F']*$batted_outs/(1+$this->real['G/F']);
        $FBouts_tot = $batted_outs - $GBouts_tot; // = (1/$this->real['G/F'])*$batted_outs/(1+(1/$this->real['G/F'])); 
        
        // Again, using the formula:
        // realLifeCount / plateApperances = chance of batters chart * chance of getting that result on batters chart + chance of pitchers chart * chance of getting that result on pitchers chart
//        print_r($batter);
//        print_r($pitchers);
//        print_r($this->real);
        // Define all 3 at once
        $diffs = $reals = $calcs = array_fill_keys(array_keys($batter), 0);
        //print_r($calcs);
        // Get $reals = real life statistics
        $reals['OB'] = $this->real['OBP'];
        $reals['SO'] = $this->real['SO'] / $this->real['PA'];
        $reals['GB'] = $GBouts_tot / $this->real['PA'];
        $reals['FB'] = $FBouts_tot / $this->real['PA'];
        $reals['BB'] = $this->real['BB'] / $this->real['PA'];
        $reals['1B'] = $this->real['1B'] / $this->real['PA'];
        $reals['1B+'] = 0 / $this->real['PA'];
        $reals['2B'] = $this->real['2B'] / $this->real['PA'];
        $reals['3B'] = $this->real['3B'] / $this->real['PA'];
        $reals['HR'] = $this->real['HR'] / $this->real['PA'];
        // Get $calcs = game statistics, average performance over all the pitchers
        for ($index = 0; $index < count($pitchers['C']); $index++) {
            $calcs['OB'] += ($batter['OB']-$pitchers['C'][$index])/20 * ($batter['BB']+$batter['1B']+$batter['1B+']+$batter['2B']+$batter['3B']+$batter['HR'])/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*($pitchers['BB'][$index]+$pitchers['1B'][$index]+$pitchers['2B'][$index]+$pitchers['HR'][$index])/20;
            $calcs['SO'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['SO']/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*$pitchers['SO'][$index]/20;  
            $calcs['GB'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['GB']/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*$pitchers['GB'][$index]/20;  
            $calcs['FB'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['FB']/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*($pitchers['FB'][$index] + $pitchers['PU'][$index])/20;  
            $calcs['BB'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['BB']/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*$pitchers['BB'][$index]/20;  
            $calcs['1B'] += ($batter['OB']-$pitchers['C'][$index])/20 * ($batter['1B'] + $batter['1B+']/2)/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*$pitchers['1B'][$index]/20;  
            $calcs['1B+'] += 0; 
            $calcs['2B'] += ($batter['OB']-$pitchers['C'][$index])/20 * ($batter['2B'] + $batter['1B+']/2)/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*$pitchers['2B'][$index]/20;  
            $calcs['3B'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['3B']/20;
            $calcs['HR'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['HR']/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*$pitchers['HR'][$index]/20;  
        }
        foreach ($calcs as $key => &$value) {
            $value /= count($pitchers['C']);// Represents number of pitchers
        }
        // Get $diffs = difference between reals and calcs
        foreach ($diffs as $key => &$value) {
            $value = abs(($reals[$key] - $calcs[$key]) / ($reals[$key] > 0 ? $reals[$key] : INF));
        }
        return $diffs;
    }

}
