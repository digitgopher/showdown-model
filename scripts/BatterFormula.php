<?php
class BatterFormula
{
    // Real-life statistical values, pulled in from database
    protected $real;

    // Constructor for a batter
    function __construct($rawData){
        // Set real-life statistics data structure
        $this->real = $rawData;
    }
    
    // Process a raw chart:
    //  - Round each value
    //  - If less than 20 possible results, try giving a single plus first. If it
    //    absolutely can't be done, give whatever else is closest.
    //  - If more than 20 possible results, 
    //  - Return formatted chart
    //
    // If second parameter set to true, a rendered version of the chart is
    // returned. By default, the number of slots is returned.
    public function processChart($chart, $prettify=false){
        $chart = self::roundChart($chart);
        if($prettify){
            $chart = self::prettifyChart($chart);
        }
        return $chart;
    }
    
    private function roundChart($chart){
        // Round
        $temp = array();
        foreach ($chart as $key => $value) {
            $temp[$key] = round($value);
        }
        
        $count = array_sum($temp) - $temp['OB'];
        
        // Deal with the rounding not adding up to 20
        if($count == 19){
            // Give 1B+ if single and double values allow for it, else find the most appropriate value to add to
            $singlesOff = $chart['1B'] < $temp['1B'] ? $temp['1B'] - $chart['1B'] : $chart['1B'] - $temp['1B'];
            $doublesOff = $chart['2B'] < $temp['2B'] ? $temp['2B'] - $chart['2B'] : $chart['2B'] - $temp['2B'];
            if($singlesOff + $doublesOff > .3){
                $temp['1B+']++;
            }
            else{
                $curVal = 0;
                $curKey = '';
                foreach ($chart as $key => $value) {
                    if($key == 'OB'){
                        continue;
                    }
                    if($value > round($value)){ // Don't care if we are rounding up already...
                        if($value - round($value) > $curVal){
                            $curVal = $value - round($value);
                            $curKey = $key;
                        }
                    }
                }
                $temp[$curKey]++;
            }
            
        }
        elseif($count == 21){
            //$d[$key] = $value < $r ? $r - $value : $value - $r;
            $curVal = 0;
            $curKey = '';
            foreach ($chart as $key => $value) {
                if($key == 'OB'){
                    continue;
                }
                if($value < round($value)){ // Don't care if we are rounding down already...
                    if(round($value) - $value > $curVal){
                        $curVal = round($value) - $value;
                        $curKey = $key;
                    }
                }
            }
            $temp[$curKey]--;
            
        }
        elseif($count == 20){
            // Rounded to 20 as expected. Nice.
        }
        else{
            echo 'The rounded chart is outside the bounds of 19-21 values!';
        }
        
        return $temp;        
    }
    
    private function prettifyChart($chart){
        // Convert to printable format for output
        $formatedChart = array();
        $old_val = "0";
        foreach ($chart as $key => $value) {
            if($key == 'OB'){
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
    
    // Get chart from data
    public function getRawCard($avgPitcher, $bouts){
        
        $batted_outs = $this->real['AB'] - $this->real['SO'] - $this->real['H'];
        $GBouts_tot = $this->real['G/F']*$batted_outs/(1+$this->real['G/F']);
        $FBouts_tot = $batted_outs - $GBouts_tot; // = (1/$this->real['G/F'])*$batted_outs/(1+(1/$this->real['G/F'])); 
        
        // Get onbase, using either an average pitcher or a set of pitchers (generated from a distribution),
        // whatever whas passed in. Parse the different data structures.
        if(!is_array($avgPitcher['C'])){ // only one average pitcher given
            $pC = $avgPitcher['C'];
            $pouts = $avgPitcher['PU'] + $avgPitcher['SO'] + $avgPitcher['GB'] + $avgPitcher['FB'];
            $OB = $this->computeOB($bouts,$this->real['OBP'],$pC,$pouts);
            return $this->getRawCardi($avgPitcher, $pC, $OB, $GBouts_tot, $FBouts_tot);
        }
        else{// multiple batters passed in
            $batters = array();
            for ($i = 0; $i < count($avgPitcher['C']); $i++) {
                $pC = $avgPitcher['C'][$i];
                //echo $pC." ";
                $pouts = $avgPitcher['PU'][$i] + $avgPitcher['SO'][$i] + $avgPitcher['GB'][$i] + $avgPitcher['FB'][$i];
                //echo $pouts." ";
                $OB = $this->computeOB($bouts,$this->real['OBP'],$pC,$pouts);
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
        //print_r($negs);//exit;
        
        // Normalize, because we can't make the pitcher worse, as the pitcher is the input
        foreach ($negs as $key => $value) {
            switch ($key) {
                // Outs
                case 'SO':
                    $sum = $chart['GB']+$chart['FB'];
                    if($sum == 0){
                        break; //everybody is 0! must be realllllly unproportionate somehow...
                    }
                    $chart['GB'] += $chart['GB']/$sum*$value;
                    $chart['FB'] += $chart['FB']/$sum*$value;
                    break;
                case 'GB':
                    $sum = $chart['SO']+$chart['FB'];
                    if($sum == 0){
                        break; //everybody is 0!
                    }
                    $chart['SO'] += $chart['SO']/$sum*$value;
                    $chart['FB'] += $chart['FB']/$sum*$value;
                    break;
                case 'FB':
                    $sum = $chart['SO']+$chart['GB'];
                    if($sum == 0){
                        break; //everybody is 0!
                    }
                    $chart['GB'] += $chart['GB']/$sum*$value;
                    $chart['SO'] += $chart['SO']/$sum*$value;
                    break;
                
                // Non-outs
                case 'BB':
                    $sum = $chart['1B']+$chart['2B']+$chart['3B']+$chart['HR'];
                    $chart['1B'] += $chart['1B']/$sum*$value;
                    $chart['2B'] += $chart['2B']/$sum*$value;
                    $chart['3B'] += $chart['3B']/$sum*$value;
                    $chart['HR'] += $chart['HR']/$sum*$value;
                    break;
                case '1B':
                    $sum = $chart['BB']+$chart['2B']+$chart['3B']+$chart['HR'];
                    $chart['BB'] += $chart['BB']/$sum*$value;
                    $chart['2B'] += $chart['2B']/$sum*$value;
                    $chart['3B'] += $chart['3B']/$sum*$value;
                    $chart['HR'] += $chart['HR']/$sum*$value;
                    break;
                case '2B':
                    $sum = $chart['BB']+$chart['1B']+$chart['3B']+$chart['HR'];
                    $chart['BB'] += $chart['BB']/$sum*$value;
                    $chart['1B'] += $chart['1B']/$sum*$value;
                    $chart['3B'] += $chart['3B']/$sum*$value;
                    $chart['HR'] += $chart['HR']/$sum*$value;
                    break;
                case '3B':
                    $sum = $chart['BB']+$chart['1B']+$chart['2B']+$chart['HR'];
                    $chart['BB'] += $chart['BB']/$sum*$value;
                    $chart['1B'] += $chart['1B']/$sum*$value;
                    $chart['2B'] += $chart['2B']/$sum*$value;
                    $chart['HR'] += $chart['HR']/$sum*$value;
                    break;
                case 'HR':
                    $sum = $chart['BB']+$chart['1B']+$chart['2B']+$chart['3B'];
                    $chart['BB'] += $chart['BB']/$sum*$value;
                    $chart['1B'] += $chart['1B']/$sum*$value;
                    $chart['2B'] += $chart['2B']/$sum*$value;
                    $chart['3B'] += $chart['3B']/$sum*$value;
                    break;

                default:
                    // Debugging...
                    echo $this->real['OBP']." ".$key." ".$value." Shouldn't get here ever.";
                    break;
            }
        }
        //echo $sumOuts."\n".$sumNonOuts."\n";
        

        return $chart;
    }
    
    // Will not return a negative onbase!
    function computeOB($b_outs, $obp, $pC, $p_outs){
        // General form:
        // obp = chance of batters chart * chance of getting onbase on batters chart + chance of pitchers chart * chance of getting onbase on pitchers chart
        // $obp = ($OB-$pC)/20*(20-$b_outs)/20 + (20-($OB-$pC))/20*(20-$p_outs)/20
        //
        // Equation solved by WolphramAlpha as follows:
        // a = ((b - c)/20*(20-d)/20) + ((20 - (b - c))/20*(20-e)/20) solve for b
        // 
        // where:
        //      a = obp
        //      b = OB
        //      c = C
        //      d = b_outs
        //      e = p_outs
        //
        // Only Restriction: d != e
        //echo $b_outs." ".$obp." ".$pC." ".$p_outs;
        $OB = (-400*$obp + $pC*$b_outs - $pC*$p_outs - 20*$p_outs + 400)/($b_outs - $p_outs);
        if($OB < 0){
            return 0;
        }
        return $OB; 
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

    // Returns the exact number of slots on charts that should be given if the batter
    // is to have the same performance as real life, per pitcher passed in.
    function computeBatterNum_G($OB, $metric, $PA, $pC, $p_result){
        // General form:
        // realLifeCount / plateApperances = chance of batters chart * chance of getting that result on batters chart + chance of pitchers chart * chance of getting that result on pitchers chart
        // realLifeCount = GBtoFBratio * number of batted outs /(1 + GBtoFBratio)
        // Equation:
        // ($bGtoF*$batted_outs/(1+$bGtoF))/$bPA = (($OB-$C)/20*numGBonBattersChart/20) + ((20-($OB-$C))/20*numGBonPitchersChart/20)
        //
        // Equation solved by WolphramAlpha as follows:
        // (a*b/(1+a))/$bPA = (($OB-$C)/20*numGBonBattersChart/20) + ((20-($OB-$C))/20*numGBonPitchersChart/20) solve for f
        // 
        // where:
        //      a = GB/FB ratio
        //      b = number of batted outs
        //      c = C
        //      d = OB
        //      e = p_result (number of result values on pitcher's chart)
        //      f = b_result (number of result values on batter's chart)
        //
        // Only Restriction: b*c != b*d
        //      essentially meaning OB can't equal Control, and PA can't be 0

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
            $calcs['1B'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['1B']/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*$pitchers['1B'][$index]/20;  
            $calcs['1B+'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['1B+']/20;  
            $calcs['2B'] += ($batter['OB']-$pitchers['C'][$index])/20 * $batter['2B']/20 + (20-($batter['OB']-$pitchers['C'][$index]))/20*$pitchers['2B'][$index]/20;  
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
