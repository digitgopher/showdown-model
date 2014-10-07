<?php
class BatterFormula
{
    // Real-life statistical values, pulled in from database
    private $real;

    // Constructor for a batter
    function __construct($rawData){
        // Set real data structure
        $this->real = array(
            'nameFirst' => '', 
            'nameLast' => '',
            'AB' => '',
            'H' => '',
            '2B' => '',
            '3B' => '',
            'HR' => '',
            '1B' => '',
            'BB' => '',
            'SO' => '',
            'G/F' => '', 
            'PA' => '', 
            'Average' => '',
            'OBP' => '',
            'SLG' => '',
            'G' => '',
            'R' => '',
            'RBI' => '',
            'SB' => '',
            'CS' => '');
                    
        // Error handling
        if(count($rawData) != count($this->real)){
            echo 'ERROR: The raw data passed to the batter object is wrong: input('.count($rawData).') expected('.count($this->real).')\n';
        }
        // Initiallize the array
//        foreach ($this->real as $key => $value) {
//            $this->real[$key] = $rawData[$key];
//        }

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
        
        // Convert to printable format for output
        $formatedChart = array();
        $old_val = "0";
        foreach ($temp as $key => $value) {
            if($key == 'OB'){
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
        // Batter inputs
        $this->real['AB'] = 438;
        $this->real['H'] = 87;
        $this->real['2B'] = 20;
        $this->real['3B'] = 2;
        $this->real['HR'] = 22;
        $this->real['1B'] = $this->real['H'] - $this->real['2B'] - $this->real['3B'] - $this->real['HR'];
        $this->real['BB'] = 17;
        $this->real['SO'] = 158;
//        $GBtot = 177;
//        $FBtot = 278;
        $this->real['G/F'] = .4;//$GBtot/$FBtot; //.64
        
        
        $batted_outs = $this->real['AB'] - $this->real['SO'] - $this->real['H'];
        $GBouts_tot = $this->real['G/F']*$batted_outs/(1+$this->real['G/F']);
        $FBouts_tot = (1/$this->real['G/F'])*$batted_outs/(1+(1/$this->real['G/F']));
        
        
        $this->real['PA'] = $this->real['AB'] + $this->real['BB']; // Actual statistic is 631, for our purposes here though it is 568 + 51 = 619
        //$ba = $bH / $bAB;
        $this->real['OBP'] = ($this->real['H'] + $this->real['BB'])/($this->real['AB'] + $this->real['BB']); // .3328 Actual statistic is .340
//        
        
        $pC = $avgPitcher['C'];
        $pouts = $avgPitcher['PU'] + $avgPitcher['SO'] + $avgPitcher['GB'] + $avgPitcher['FB'];
        //$bouts = 2;
        //$b_notouts = 20 - $bouts;
        
        $OB = $this->computeOB($bouts,$this->real['OBP'],$pC,$pouts);

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
        //print_r($negs);
        
        // Normalize, because we can't make the pitcher worse, as the pitcher is the input
        foreach ($negs as $key => $value) {
            switch ($key) {
                // Outs
                case 'SO':
                    $sum = $chart['GB']+$chart['FB'];
                    $chart['GB'] += $chart['GB']/$sum*$value;
                    $chart['FB'] += $chart['FB']/$sum*$value;
                    break;
                case 'GB':
                    $sum = $chart['SO']+$chart['FB'];
                    $chart['SO'] += $chart['SO']/$sum*$value;
                    $chart['FB'] += $chart['FB']/$sum*$value;
                    break;
                case 'FB':
                    $sum = $chart['SO']+$chart['GB'];
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
                    echo "Shouldn't get here ever.";
                    break;
            }
        }
        //echo $sumOuts."\n".$sumNonOuts."\n";
        


        
        return $chart;
        //for ($i = 4; $i < 12; $i++) {
        //    echo $i.",".computeBatterOuts($i,$obp,3,16)."\n";
        //}
//        for ($index = 180; $index < 480; $index++) {
//            $bunchofOBPs[] = $index/1000;
//        }

//        $r = array();
//        foreach ($bunchofOBPs as $key => $value) {
//            for ($j = 1; $j <= 7; $j++) {
//                $raw = computeOB($j,$value,3,16);
//                $rounded = round($raw);
//                if($raw < $rounded){
//                    $diff = $rounded - $raw;
//                }
//                else{
//                    $diff = $raw - $rounded;
//                }
//                //echo $diff.", ".$rounded.", ".$j.", ".$raw."\n";
//
//            }
//
//            }
//        //}
//        //print_r($r);
    }
    
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
    
    $OB = (-400*$obp + $pC*$b_outs - $pC*$p_outs - 20*$p_outs + 400)/($b_outs - $p_outs);
    // Negative means the average pitcher needs to be worse!
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
//    // Useful method declarations
//    public function outs() {
//        return $this->totals['PU'] + $this->totals['SO'] + $this->totals['GB'] + $this->totals['FB'];
//    }
//    public function hits() {
//        return $this->totals['1B'] + $this->totals['2B'] + $this->totals['3B'] + $this->totals['HR'];
//    }
//    public function hitspluswalks() {
//        return $this->totals['1B'] + $this->totals['2B'] + $this->totals['3B'] + $this->totals['HR'] + $this->totals['BB'];
//    }
//    public function plateappearances() {
//        return $this->totals['PU'] 
//                + $this->totals['SO']
//                + $this->totals['GB'] 
//                + $this->totals['FB'] 
//                + $this->totals['BB'] 
//                + $this->totals['1B'] 
//                + $this->totals['2B'] 
//                + $this->totals['3B'] 
//                + $this->totals['HR'];
//    }
//    public function atbats() {
//        return ($this->totals['PU'] 
//                + $this->totals['SO'] 
//                + $this->totals['GB'] 
//                + $this->totals['FB'] 
//                + $this->totals['1B'] 
//                + $this->totals['2B'] 
//                + $this->totals['3B'] 
//                + $this->totals['HR']);
//    }
//    public function normalize(){
//        $sum = array_sum($this->dists);
//        if($sum == 0){
//            echo "Can't normalize, sum is zero.";
//            return;
//        }
//        foreach ($this->dists as $key => $value) {
//            $this->dists[$key] = $value/$sum;
//            //echo $key." => ".$value."\n";
//        }
//        //echo "Array sum after: ".array_sum($this->dists);
//    }
//    public function getFinalChart($chartMaxValue){
//        $val = 0;
//        $convDists = array();
//        foreach ($this->dists as $key => $value) {
//            $value_normalized = $value*$chartMaxValue;
//            $val += $value_normalized;
//            $convDists[$key] = $val;
//        }
//        return $convDists;
//    }
//    public function getFinalChartFormatted($chartMaxValue){
//        $val = 0;
//        $convDists = array();
//        // Convert probability distribution to value between 0 and max chart value (0-20)
//        foreach ($this->dists as $key => $value) {
//            $value_normalized = $value*$chartMaxValue;
//            $val += $value_normalized;
//            $convDists[$key] = $val;
//        }
//        $formatedChart = array();
//        $old_val = "0";
//        foreach ($convDists as $key => $value) {
//            $new_val = round($value);
//            if($new_val > $old_val + 1){
//                $formatedChart[$key] = ($old_val + 1)." - ".$new_val;
//            }
//            elseif($new_val == $old_val + 1){
//                $formatedChart[$key] = $new_val;
//            }
//            else{
//                $formatedChart[$key] = "-";
//            }
//            $old_val = $new_val;
//        }
////        echo 'hello';
////        print_r($convDists);
////        print_r($formatedChart);exit;
//        return $formatedChart;
//    }
//    // chartMaxValue should be large (>=100)for better precision during 
//    // the simulation, and only brought down to 20 at the end.
//    public function getChartResult($chartMaxValue){
//        $roll = mt_rand(1, $chartMaxValue);
//        $convDists = array();
//        $val = 0;
//        foreach ($this->dists as $key => $value) {
//            $value_normalized = $value*$chartMaxValue;
//            $val += $value_normalized;
//            $convDists[$key] = $val;
//        }
////        echo 'Raw distributions: ';
////        print_r($this->dists);
////        echo 'Converted distrib: ';
////        print_r($convDists);
//        foreach ($convDists as $key => $value) {
//            if($roll <= $value){
//                //echo $key;
//                return $key;
//            }
//        }
//        // Something funky about php...don't know why the less than equal to above doesn't register 100...
//        return $key;
////        echo 'I should never get here.';
////        echo $roll;echo $key; echo $value;
////        print_r($this->dists);
////        print_r($convDists);
//    }
//    // Contains all the recalculating logic!
//    public function incrementTotalAndRecalculate($result,$increment){
//        //print_r($this->dists);
//        if($result == null || !isset($this->totals[$result])){
//            // Dont' need to do anything.
//            return;
//        }
//        $this->totals[$result]++;
//        $this->calculateAnalogs();
//        // calculate new statistics: for each statistic, if it is greater that real life then decrement the distribution
//        // Distribution values
//        foreach ($this->analogs as $key => $value) {
//            if(isset($this->real[$key]) && $value > $this->real[$key] && isset($this->dists[$key])){
//                if($this->dists[$key] > $increment){ // don't go below zero
//                    $this->dists[$key] -= $increment;
//                }
//            }
//            elseif(isset($this->real[$key]) && $value < $this->real[$key] && isset($this->dists[$key])){
//                if($this->dists[$key] < (1-$increment)){ // don't go above one
//                    $this->dists[$key] += $increment;
//                }
//            }
//            elseif(isset($this->real[$key]) && $value == $this->real[$key] && isset($this->dists[$key])){
//                // Do nothing. This actually happens, rarely.
//            }
//            else{ // statistic does not have a dist (Average, OBP, SLG)
//                // OBP correlates with onbase
////                if($key == 'OBP'){
////                    if($value > $this->real[$key]){
////                        if($this->onbase > 0){
////                            $this->onbase--;
////                        }
////                    }
////                    elseif($value < $this->real[$key]){
//////                       if($this->onbase < 15){
////                            $this->onbase++;
//////                        }
////                    }
////                    else{
////                        echo 'Onbase exactly right!';
////                    }
////                }
//                // Average correlates with?
//                // SLG correlates with?
//            }
//        }
//        $this->normalize();
//        return $this->dists;
//    }
//    public function calculateAnalogs(){
//        $this->analogs['PU'] = '';
//        $this->analogs['SO'] = $this->totals['SO'] / $this->hits();
//        $this->analogs['GB'] = ($this->totals['GB'] / ($this->totals['FB'] + 1)) / (($this->totals['GB'] / ($this->totals['FB'] + 1)) + (1 / ($this->totals['GB'] / ($this->totals['FB'] + 1))));
//        $this->analogs['FB'] = (1 / ($this->totals['GB'] / ($this->totals['FB'] + 1))) / (($this->totals['GB'] / ($this->totals['FB'] + 1)) + (1 / ($this->totals['GB'] / ($this->totals['FB'] + 1))));
//        $this->analogs['BB'] = $this->totals['BB'] / $this->hits();
//        $this->analogs['1B'] = ($this->hits() - $this->totals['2B'] - $this->totals['3B'] - $this->totals['HR']) / $this->hits();
//        $this->analogs['2B'] = $this->totals['2B'] / $this->hits();
//        $this->analogs['3B'] = $this->totals['3B'] / $this->hits();
//        $this->analogs['HR'] = $this->totals['HR'] / $this->hits();
//        $this->analogs['Average'] = $this->hits() / $this->atbats();
//        $this->analogs['OBP'] = $this->hitspluswalks() / $this->plateappearances();
//        $this->analogs['SLG'] = ( $this->totals['1B'] + 2*$this->totals['2B'] + 3*$this->totals['3B'] + 4*$this->totals['HR'] ) / $this->atbats();
//    }
//    public function printeverything() {
//        echo "************** Dumping batter instance *******************\n";
//        foreach($this as $key => $value) {
//            if(!is_array($key) && !is_array($value)){
//                echo "$key => $value\n";
//            }
//            else{
//                print_r($key);
//                print_r($value);
//            }
//           //printf($key." => %f\n",$value);
//        }
//        echo "Outs: ".$this->outs()."\n";
//        echo "Hits: ".$this->hits()."\n";
//        echo "Hits + Walks: ".$this->hitspluswalks()."\n";
//        echo "Plate Appearances: ".$this->plateappearances()."\n";
//        echo "Atbats: ".$this->atbats()."\n";
//        print_r($this->getFinalChart(20));
//        print_r($this->getFinalChartFormatted(20));
//        echo "*********************************************************\n";
//    }
//    public function getState(){
//        return array($this->real, 
//            $this->analogs,
//            $this->totals, 
//            $this->dists, 
//            $this->onbase, 
//            "Outs: ".$this->outs(),
//            "Hits: ".$this->hits(),
//            "Hits + Walks: ".$this->hitspluswalks(),
//            "Plate Appearances: ".$this->plateappearances(),
//            "Atbats: ".$this->atbats());
//    }
}
