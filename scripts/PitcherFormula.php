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
    
    // Process a raw chart:
    //  - Round each value
    //  - If less than 20 possible results, try giving a single plus first. If it
    //    absolutely can't be done, give whatever else is closest.
    //  - If more than 20 possible results, 
    //  - Return formatted chart
    //
    //
    public function processChart($chart){
        $chart = self::roundChart($chart);
        $chart = self::prettifyChart($chart);
        return $chart;
    }
    
    private function roundChart($chart){
        // Round
        $temp = array();
        foreach ($chart as $key => $value) {
            $temp[$key] = round($value);
        }
        
        $count = array_sum($temp) - $temp['C'];
        
        // Deal with the rounding not adding up to 20
        if($count == 19){
            // Find the most appropriate value to add to
            $curVal = 0;
            $curKey = '';
            foreach ($chart as $key => $value) {
                if($key == 'C'){
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
        elseif($count == 21){
            //$d[$key] = $value < $r ? $r - $value : $value - $r;
            $curVal = 0;
            $curKey = '';
            foreach ($chart as $key => $value) {
                if($key == 'C'){
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
            if($key == 'C'){
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
        
        
        $OB = $avgBatter['OB'];
        $bouts = $avgBatter['SO'] + $avgBatter['GB'] + $avgBatter['FB'];
        
        $C = $this->computeControl($pouts, $this->real['OBP'], $OB, $bouts);
        echo $C." ".$batted_outs." ".$Fly_outs." ".$PUouts_tot." ".$FBouts_tot." ".$GBouts_tot;

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

}
