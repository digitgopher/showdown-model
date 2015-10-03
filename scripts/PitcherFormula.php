<?php
class PitcherFormula
{
    // Real-life statistical values, pulled in from database
    public $real;

    function __construct($rawData){
        $this->real = $rawData;
    }

    // Get chart from data
    public function getRawCard($avgBatter, $C){

        // truth statements:
        //      $batted_outs = $GBouts_tot + $Fly_outs
        //      $Fly_outs = $PUouts_tot + $FBouts_tot
        $batted_outs = $this->real['PA'] - $this->real['BB'] - $this->real['SO'] - $this->real['H'];
        $GBouts_tot = $this->real['G/F']*$batted_outs/(1+$this->real['G/F']);
        $Fly_outs = $batted_outs - $GBouts_tot; // = (1/$this->real['G/F'])*$batted_outs/(1+(1/$this->real['G/F']));
        // Split fly outs into FB and PU
        $PUouts_tot = $Fly_outs*$this->real['PUpct']; // The original query pads the number for Flys into the outfield that aren't deep enough to tag, since the official statistic only counts along the lines of 'balls in the air in the infield'. PUpct should never be over 60% so we don't need value checking.
        $FBouts_tot = $Fly_outs - $PUouts_tot;

        // only one average batter given
        if(!is_array($avgBatter['OB'])){
            $OB = $avgBatter['OB'];
            $bouts = $avgBatter['SO'] + $avgBatter['GB'] + $avgBatter['FB'];
            return $this->getRawCardi($avgBatter, $OB, $C, $PUouts_tot, $GBouts_tot, $FBouts_tot);
        }
        // multiple batters passed in
        else{
            $pitchers = array();
            for ($i = 0; $i < count($avgBatter['OB']); $i++) {
                $OB = $avgBatter['OB'][$i];
                $bouts = $avgBatter['SO'][$i] + $avgBatter['GB'][$i] + $avgBatter['FB'][$i];
                $curBatter = array();
                foreach ($avgBatter as $key => $dontcareaboutthis) {
                    $curBatter[$key] = $avgBatter[$key][$i];
                }
                $pitchers[] = $this->getRawCardi($curBatter, $OB, $C, $PUouts_tot, $GBouts_tot, $FBouts_tot);
            }
            // Average them now, after the fact rather than before!
            // Set as 0 so we can ++
            $sums = array_fill_keys(array_keys($pitchers[0]), 0);
            // Add all categories
            foreach ($pitchers as $key => $pitcher) {
                foreach ($pitcher as $k => $v) {
                    $sums[$k] += $v;
                }
            }
            // Divide to get average
            foreach ($sums as $key => &$value) {
                $value /= count($pitchers);
            }
            return $sums;
        }
        echo "Should never get here. Exiting!";
        exit;
    }

    // Internal function
    private function getRawCardi($avgBatter, $OB, $C, $PUouts_tot, $GBouts_tot, $FBouts_tot){
        // The sum of all the values should add up to PA if it is going to work.
        // Note we have to divide by number of outs so that the calculated negatives don't throw off the balance
        $chart = array('C' => $C,
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
        // Handle negative chart values: replace with 0 but keep the value so it can be dealt with
        foreach ($chart as $key => $value) {
            if($value < 0){
                $chart[$key] = 0;
                $negs[$key] = $value;
            }
        }

        // Deal with neg values. Normalize EQUALLY, because we can't make the pitcher worse, as the pitcher is the input.
        foreach ($negs as $key => $value) {
            $sum = array_sum($chart) - $chart['C'] - $chart[$key];
            foreach ($chart as $k => &$v) {
                if($k != 'C'){
                    $v += $v / $sum * $value;
                }
            }
        }

        // Throw on dummy values, doesn't matter but needs something to keep going
        if(array_sum($chart) - $chart['C'] == 0){
            $chart = array_map(function($val) { return $val + (20 / 8); },$chart);
        }
        if(round(array_sum($chart) - $chart['C']) < 20){
            print_r($chart);print_r($negs);exit;
        }
        return $chart;
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
    function computeSquaredDifference($pitcher, $batters){
        // Copied from above
        $batted_outs = $this->real['AB'] - $this->real['SO'] - $this->real['H'];
        $GBouts_tot = $this->real['G/F']*$batted_outs/(1+$this->real['G/F']);
        $f = $batted_outs - $GBouts_tot;// Split fly outs into FB and PU
        $PUouts_tot = $f*$this->real['PUpct'];
        $FBouts_tot = $f - $PUouts_tot;
        // Again, using the formula:
        // realLifeCount / plateApperances = chance of batters chart * chance of getting that result on batters chart + chance of pitchers chart * chance of getting that result on pitchers chart
        // Define all 3 at once
        $diffs = $reals = $calcs = array_fill_keys(array_keys($pitcher), 0);
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
        // Get $diffs = squared difference between reals and calcs
        foreach ($diffs as $key => &$value) {
            $diff = abs(($reals[$key] - $calcs[$key]) / ($reals[$key] > 0 ? $reals[$key] : INF));
            $value = $diff * $diff;
        }
        return $diffs;
    }

}
