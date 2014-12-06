<?php
class PitcherFormula
{
    // Real-life statistical values, pulled in from database
    protected $real;

    // Constructor for a pitcher
    function __construct($rawData){
        // Set real-life statistics data structure
        $this->real = $rawData;
    }
    
    // Get chart from data
    public function getRawCard($avgBatter, $pouts){
        
        // truth statements:
        //      $batted_outs = $GBouts_tot + $Fly_outs
        //      $Fly_outs = $PUouts_tot + $FBouts_tot
        $batted_outs = $this->real['AB'] - $this->real['SO'] - $this->real['H'];
        $GBouts_tot = $this->real['G/F']*$batted_outs/(1+$this->real['G/F']);
        $Fly_outs = $batted_outs - $GBouts_tot; // = (1/$this->real['G/F'])*$batted_outs/(1+(1/$this->real['G/F']));
        // Split fly outs into FB and PU
        $PUouts_tot = $Fly_outs*$this->real['PUpct']; // The original query pads the number for Flys into the outfield that aren't deep enough to tag, since the official statistic only counts along the lines of 'balls in the air in the infield'. PUpct should never be over 60% so we don't need value checking.
        $FBouts_tot = $Fly_outs - $PUouts_tot;
        //echo $C." ".$batted_outs." ".$Fly_outs." ".$PUouts_tot." ".$FBouts_tot." ".$GBouts_tot;
        
        
        // Get control, using either an average batter or a set of batters (generated from a distribution),
        // whatever whas passed in. Parse the different data structures.
        if(!is_array($avgBatter['OB'])){ // only one average batter given
            $OB = $avgBatter['OB'];
            $bouts = $avgBatter['SO'] + $avgBatter['GB'] + $avgBatter['FB'];
            $C = $this->computeControl($pouts, $this->real['OBP'], $OB, $bouts);
            return $this->getRawCardi($avgBatter, $OB, $C, $PUouts_tot, $GBouts_tot, $FBouts_tot);
        }
        else{// multiple batters passed in
            $pitchers = array();
            for ($i = 0; $i < count($avgBatter['OB']); $i++) {
                $OB = $avgBatter['OB'][$i];
                //echo $OB." ";
                $bouts = $avgBatter['SO'][$i] + $avgBatter['GB'][$i] + $avgBatter['FB'][$i];
                //echo $bouts." ";
                $C = $this->computeControl($pouts, $this->real['OBP'], $OB, $bouts);
                $curBatter = array();
                foreach ($avgBatter as $key => $dontcareaboutthis) {
                    $curBatter[$key] = $avgBatter[$key][$i];
                }
                //print_r($curBatter);exit;
                $pitchers[] = $this->getRawCardi($curBatter, $OB, $C, $PUouts_tot, $GBouts_tot, $FBouts_tot);
            }
//            print_r($pitchers);
            // Average them now, after the fact rather than before!
            // Set as 0 so we can ++
            $sums = array_fill_keys(array_keys($pitchers[0]), 0);
            // Add all categories
            foreach ($pitchers as $key => $pitcher) {
                foreach ($pitcher as $k => $v) {
                    //echo $k; exit;
                    $sums[$k] += $v;
                }
            }
            // Divide to get average
//            print_r($pitchers);
//            print_r($sums);
            foreach ($sums as $key => &$value) {
                $value /= count($pitchers);
            }
//            print_r($sums);exit;
            return $sums;
        }
        echo "Should never get here. Exiting!";
        exit;
    }
    
    // Internal function
    private function getRawCardi($avgBatter, $OB, $C, $PUouts_tot, $GBouts_tot, $FBouts_tot){
        // The sum of all the values should add up to PA if it is going to work.
        // Note we have to divide by number of outs so that the calculated negatives don't throw off the balance
        $chart = array('C' => $C,// computePitcherSlots($pC, $metric, $PA, $OB, $b_result)
            'PU' => $this->computePitcherSlots($C, $PUouts_tot, $this->real['PA'], $OB, 0),
            'SO' => $this->computePitcherSlots($C, $this->real['SO'], $this->real['PA'], $OB, $avgBatter['SO']),
            'GB' => $this->computePitcherSlots($C, $GBouts_tot, $this->real['PA'], $OB, $avgBatter['GB']) ,
            'FB' => $this->computePitcherSlots($C, $FBouts_tot, $this->real['PA'], $OB, $avgBatter['FB']) ,
            //'Ou' => $this->computePitcherSlots($C, $bAB-$bH, $this->real['PA'], $OB, $pouts),
            'BB' => $this->computePitcherSlots($C, $this->real['BB'], $this->real['PA'], $OB, $avgBatter['BB']), 
            '1B' => $this->computePitcherSlots($C, $this->real['1B'], $this->real['PA'], $OB, $avgBatter['1B'] + $avgBatter['1B+']/2),
            '2B' => $this->computePitcherSlots($C, $this->real['2B'] + $this->real['3B'], $this->real['PA'], $OB, $avgBatter['1B+']/2 + $avgBatter['2B'] + $avgBatter['3B']), 
            'HR' => $this->computePitcherSlots($C, $this->real['HR'], $this->real['PA'], $OB, $avgBatter['HR']));

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
                if($key == 'PU' || $key == 'SO' || $key == 'GB' || $key == 'FB'){
                    $sumOuts += $value;
                }
                elseif($key == 'BB' || $key == '1B' || $key == '2B' || $key == 'HR'){
                    $sumNonOuts += $value;
                }
                elseif($key == 'C'){
                    // Do nothing
                }
                else{
                    echo "Should never get here!";
                }
            }
        }
        //print_r($negs);
        
        // Normalize, because we can't make the pitcher worse, as the pitcher is the input
        foreach ($negs as $key => $value) {
            switch ($key) {
                // Outs
                case 'PU':
                    $sum = $chart['SO']+$chart['GB']+$chart['FB'];
                    $chart['SO'] += $chart['SO']/$sum*$value;
                    $chart['GB'] += $chart['GB']/$sum*$value;
                    $chart['FB'] += $chart['FB']/$sum*$value;
                    break;
                case 'SO':
                    $sum = $chart['PU']+$chart['GB']+$chart['FB'];
                    $chart['PU'] += $chart['PU']/$sum*$value;
                    $chart['GB'] += $chart['GB']/$sum*$value;
                    $chart['FB'] += $chart['FB']/$sum*$value;
                    break;
                case 'GB':
                    $sum = $chart['PU']+$chart['SO']+$chart['FB'];
                    $chart['PU'] += $chart['PU']/$sum*$value;
                    $chart['SO'] += $chart['SO']/$sum*$value;
                    $chart['FB'] += $chart['FB']/$sum*$value;
                    break;
                case 'FB':
                    $sum = $chart['PU']+$chart['SO']+$chart['GB'];
                    $chart['PU'] += $chart['PU']/$sum*$value;
                    $chart['SO'] += $chart['SO']/$sum*$value;
                    $chart['GB'] += $chart['GB']/$sum*$value;
                    break;
                
                // Non-outs
                case 'BB':
                    $sum = $chart['1B']+$chart['2B']+$chart['HR'];
                    $chart['1B'] += $chart['1B']/$sum*$value;
                    $chart['2B'] += $chart['2B']/$sum*$value;
                    $chart['HR'] += $chart['HR']/$sum*$value;
                    break;
                case '1B':
                    $sum = $chart['BB']+$chart['2B']+$chart['HR'];
                    $chart['BB'] += $chart['BB']/$sum*$value;
                    $chart['2B'] += $chart['2B']/$sum*$value;
                    $chart['HR'] += $chart['HR']/$sum*$value;
                    break;
                case '2B':
                    $sum = $chart['BB']+$chart['1B']+$chart['HR'];
                    $chart['BB'] += $chart['BB']/$sum*$value;
                    $chart['1B'] += $chart['1B']/$sum*$value;
                    $chart['HR'] += $chart['HR']/$sum*$value;
                    break;
                case 'HR':
                    $sum = $chart['BB']+$chart['1B']+$chart['2B'];
                    $chart['BB'] += $chart['BB']/$sum*$value;
                    $chart['1B'] += $chart['1B']/$sum*$value;
                    $chart['2B'] += $chart['2B']/$sum*$value;
                    break;

                default:
                    echo "Shouldn't get here ever.";
                    break;
            }
        }
        //echo $sumOuts."\n".$sumNonOuts."\n";
        
        return $chart;
    }
    
    // Given how many outs on pitcher's chart and the average batter, what should the control be for real-life OOB?
    // Will not return a negative control!
    function computeControl($p_outs, $obp, $OB, $b_outs){
        // General form:
        // obp = chance of batters chart * chance of getting onbase on batters chart + chance of pitchers chart * chance of getting onbase on pitchers chart
        // $obp = ($OB-$pC)/20*(20-$b_outs)/20 + (20-($OB-$pC))/20*(20-$p_outs)/20
        //
        // Equation solved by WolphramAlpha as follows:
        // a = ((b - c)/20*(20-d)/20) + ((20 - (b - c))/20*(20-e)/20) solve for c
        // 
        // where:
        //      a = obp
        //      b = OB
        //      c = C
        //      d = b_outs
        //      e = p_outs
        //
        // Only Restriction: d != e

        $pC = (400*$obp + $OB*($b_outs - $p_outs) + 20*($p_outs - 20))/($b_outs - $p_outs);
        if($pC < 0){
            return 0;
        }
        return $pC; 
    }

    // Returns the exact number of slots on charts that should be given if the pitcher
    // is to have the same performance as real life, per batter that is passed in.
    function computePitcherSlots($pC, $metric, $PA, $OB, $b_result){
        // General form:
        // realLifeCount / plateApperances = chance of batters chart * chance of getting that result on batters chart + chance of pitchers chart * chance of getting that result on pitchers chart
        // Ex for doubles: 
        // $b2B/$bPA = ($OB-$pC)/20*$numDoublesOnBattersChart/20 + (20-($OB-$pC))/20*$numDoublesOnPitchersChart/20
        //
        // Equation solved by WolphramAlpha as follows:
        // a/b = (d-c)/20*f/20 + (20-(d-c))/20*e/20 solve for e
        // 
        // where:
        //      a = metric (real-life statistic; Ex. number of doubles in a season)
        //      b = PA (Plate Appearances)
        //      c = C
        //      d = OB
        //      e = p_result (number of result values on pitcher's chart)
        //      f = b_result (number of result values on batter's chart)
        //
        // Only Restriction: b*(c-d+20) != 0
        //      essentially meaning 20 minus the difference of OB and Control can't be 0,
        //      and also PA can't be 0

        $p_result = (400*$metric + $PA*$b_result*($pC-$OB))/($PA*($pC-$OB + 20));
        // Negative means the average batter needs to be worse!
        return $p_result;
    }
    
    // Built to take a representative sample of batters, the game calculations all depend on the selection of batters!
    function computePercentDifferent($pitcher, $batters){
        // Copied from above
        $batted_outs = $this->real['AB'] - $this->real['SO'] - $this->real['H'];
        $GBouts_tot = $this->real['G/F']*$batted_outs/(1+$this->real['G/F']);
        $f = $batted_outs - $GBouts_tot;// Split fly outs into FB and PU
        $PUouts_tot = $f*$this->real['PUpct'];
        $FBouts_tot = $f - $PUouts_tot;
        // Again, using the formula:
        // realLifeCount / plateApperances = chance of batters chart * chance of getting that result on batters chart + chance of pitchers chart * chance of getting that result on pitchers chart
//        print_r($pitcher);
//        print_r($batters);
//        print_r($this->real);
        // Define all 3 at once
        $diffs = $reals = $calcs = array_fill_keys(array_keys($pitcher), 0);
        //print_r($calcs);
        // Get $reals = real life statistics
        $reals['C'] = $this->real['OBP'];
        $reals['PU'] = $PUouts_tot / $this->real['PA'];
        $reals['SO'] = $this->real['SO'] / $this->real['PA'];
        $reals['GB'] = $GBouts_tot / $this->real['PA'];
        $reals['FB'] = $FBouts_tot / $this->real['PA'];
        $reals['BB'] = $this->real['BB'] / $this->real['PA'];
        $reals['1B'] = $this->real['1B'] / $this->real['PA'];
        $reals['2B'] = ($this->real['2B'] + $this->real['3B']) / $this->real['PA'];// Triples counted as doubles
        $reals['HR'] = $this->real['HR'] / $this->real['PA'];
        // Get $calcs = game statistics, average performance over all the pitchers
        for ($index = 0; $index < count($batters['OB']); $index++) {
            $calcs['C'] += ($batters['OB'][$index]-$pitcher['C'])/20 * ($batters['BB'][$index]+$batters['1B'][$index]+$batters['1B+'][$index]+$batters['2B'][$index]+$batters['3B'][$index]+$batters['HR'][$index])/20 + (20-($batters['OB'][$index]-$pitcher['C']))/20*($pitcher['BB']+$pitcher['1B']+$pitcher['2B']+$pitcher['HR'])/20;
            $calcs['PU'] += (20-($batters['OB'][$index]-$pitcher['C']))/20*$pitcher['PU']/20;  
            $calcs['SO'] += ($batters['OB'][$index]-$pitcher['C'])/20 * $batters['SO'][$index]/20 + (20-($batters['OB'][$index]-$pitcher['C']))/20*$pitcher['SO']/20;  
            $calcs['GB']  += ($batters['OB'][$index]-$pitcher['C'])/20 * $batters['GB'][$index]/20 + (20-($batters['OB'][$index]-$pitcher['C']))/20*$pitcher['GB']/20; 
            $calcs['FB'] += ($batters['OB'][$index]-$pitcher['C'])/20 * $batters['FB'][$index]/20 + (20-($batters['OB'][$index]-$pitcher['C']))/20*$pitcher['FB']/20; 
            $calcs['BB'] += ($batters['OB'][$index]-$pitcher['C'])/20 * $batters['BB'][$index]/20 + (20-($batters['OB'][$index]-$pitcher['C']))/20*$pitcher['BB']/20;  
            $calcs['1B'] += ($batters['OB'][$index]-$pitcher['C'])/20 * ($batters['1B'][$index] + $batters['1B+'][$index]/2)/20 + (20-($batters['OB'][$index]-$pitcher['C']))/20*$pitcher['1B']/20;
            $calcs['2B'] += ($batters['OB'][$index]-$pitcher['C'])/20 * ($batters['2B'][$index] + $batters['1B+'][$index]/2 + $batters['3B'][$index])/20 + (20-($batters['OB'][$index]-$pitcher['C']))/20*$pitcher['2B']/20;
            $calcs['HR'] += ($batters['OB'][$index]-$pitcher['C'])/20 * $batters['HR'][$index]/20 + (20-($batters['OB'][$index]-$pitcher['C']))/20*$pitcher['HR']/20;  
        }
        foreach ($calcs as $key => &$value) {
            $value /= count($batters['OB']);// Represents number of batters
        }
        // Get $diffs = difference between reals and calcs
        foreach ($diffs as $key => &$value) {
            $value = abs(($reals[$key] - $calcs[$key]) / ($reals[$key] > 0 ? $reals[$key] : INF));
        }
        return $diffs;
    }

}
