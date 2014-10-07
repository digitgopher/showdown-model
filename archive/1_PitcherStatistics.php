<?php
class PitcherStatistics
{
    // Real-life statistical values, pulled in from database
    private $real;
    // Game statistics, calculated from simulation to compare with the real ones
    private $analogs;
    // Contains number each result as the batter accumulates them
    private $totals;
    // Probability distribution - simulating a chart.
    private $dists;
    
    public $control;

    // Constructor for a batter
    function __construct($rawData, $totDistribution, $control_start){
        // Set real data
        $this->real = array(
            'nameFirst' => '', 
            'nameLast' => '',
            'PU' => '',
            'SO' => '',
            'GB' => '',
            'FB' => '',
            'BB' => '',
            '1B' => '',
            '2B' => '',
            'HR' => '',
            'Control' => '', 
            'W' => '', 
            'L' => '',
            'G' => '',
            'GS' => '',
            'SV' => '',
            'IPouts' => '',
            'ERA' => '');
        if(count($rawData) != count($this->real)){
            echo 'ERROR: The raw data passed to the pitcher object is wrong: input('.count($rawData).') expected('.count($this->real).')\n';
        }
        $i = 0;
        foreach ($this->real as $key => $value) {
            $this->real[$key] = $rawData[$key];
            $i++;
        }

        // Initialize totals
        // Some are not zero in order to avoid dividing by zero elsewhere (with many iterations should be negligiable)
        $this->totals = array('PU' => 0, 'SO' => 0, 'GB' => 1, 'FB' => 0, 'BB' => 1, '1B' => 1, '2B' => 0, '3B' => 0, 'HR' => 0);

        // Set distributions
        $this->dists = array('PU' => '', 'SO' => '', 'GB' => '', 'FB' => '', 'BB' => '', '1B' => '', '2B' => '', 'HR' => '');
        foreach ($this->dists as $key => $value) {
            $this->dists[$key] = $totDistribution/count($this->dists);
        }

        $this->analogs = array();
        $this->calculateAnalogs();
        
        $this->control = $control_start;
    }
    
    // Useful method declarations
    public function outs() {
        return $this->totals['PU'] + $this->totals['SO'] + $this->totals['GB'] + $this->totals['FB'];
    }
    public function hits() {
        return $this->totals['1B'] + $this->totals['2B'] + $this->totals['3B'] + $this->totals['HR'];
    }
    public function hitspluswalks() {
        return $this->totals['1B'] + $this->totals['2B'] + $this->totals['3B'] + $this->totals['HR'] + $this->totals['BB'];
    }
    public function battersFaced() {
        return $this->totals['PU'] 
                + $this->totals['SO']
                + $this->totals['GB'] 
                + $this->totals['FB'] 
                + $this->totals['BB'] 
                + $this->totals['1B'] 
                + $this->totals['2B'] 
                + $this->totals['3B'] 
                + $this->totals['HR'];
    }
    public function atbats() {
        return ($this->totals['PU'] 
                + $this->totals['SO'] 
                + $this->totals['GB'] 
                + $this->totals['FB'] 
                + $this->totals['1B'] 
                + $this->totals['2B'] 
                + $this->totals['3B'] 
                + $this->totals['HR']);
    }
    public function normalize(){
        $sum = array_sum($this->dists);
        if($sum == 0){
            echo "Can't normalize, sum is zero.";
            return;
        }
        foreach ($this->dists as $key => $value) {
            $this->dists[$key] = $value/$sum;
            //echo $key." => ".$value."\n";
        }
        //echo "Array sum after: ".array_sum($this->dists);
    }
    public function getFinalChart($chartMaxValue){
        $val = 0;
        $convDists = array();
        foreach ($this->dists as $key => $value) {
            $value_normalized = $value*$chartMaxValue;
            $val += $value_normalized;
            $convDists[$key] = $val;
        }
        return $convDists;
    }
        public function getFinalChartFormatted($chartMaxValue){
        $val = 0;
        $convDists = array();
        foreach ($this->dists as $key => $value) {
            $value_normalized = $value*$chartMaxValue;
            $val += $value_normalized;
            $convDists[$key] = $val;
        }
        $formatedChart = array();
        $old_val = "0";
        foreach ($convDists as $key => $value) {
            $new_val = round($value);
            if($new_val > $old_val + 1){
                $formatedChart[$key] = ($old_val + 1)." - ".$new_val;
            }
            elseif($new_val == $old_val + 1){
                $formatedChart[$key] = $new_val;
            }
            else{
                $formatedChart[$key] = "-";
            }
            $old_val = $new_val;
        }
        return $formatedChart;
    }
    // chartMaxValue should be large (>=100)for better precision during 
    // the simulation, and only brought down to 20 at the end.
    public function getChartResult($chartMaxValue){
        $roll = mt_rand(1, $chartMaxValue);
        $convDists = array();
        $val = 0;
        foreach ($this->dists as $key => $value) {
            $value_normalized = $value*$chartMaxValue;
            $val += $value_normalized;
            $convDists[$key] = $val;
        }
//        echo 'Raw distributions: ';
//        print_r($this->dists);
//        echo 'Converted distrib: ';
//        print_r($convDists);
        foreach ($convDists as $key => $value) {
            if($roll <= $value){
                //echo $key;
                return $key;
            }
        }
        // Something funky about php...don't know why the less than equal to above doesn't register 100...
        return $key;
//        echo 'I should never get here.';
//        echo $roll;echo $key; echo $value;
//        print_r($this->dists);
//        print_r($convDists);
    }
    // Contains all the recalculating logic!
    public function incrementTotalAndRecalculate($result,$increment){
        //print_r($this->dists);
        if($result == null || !isset($this->totals[$result])){
            // Dont' need to do anything.
            return;
        }
        $this->totals[$result]++;
        $this->calculateAnalogs();
        // calculate new statistics: for each statistic, if it is greater that real life then decrement the distribution
        // Distribution values
        foreach ($this->analogs as $key => $value) {
            if(isset($this->real[$key]) && $value > $this->real[$key] && isset($this->dists[$key])){
                if($this->dists[$key] > $increment){ // don't go below zero
                    $this->dists[$key] -= $increment;
                }
            }
            elseif(isset($this->real[$key]) && $value < $this->real[$key] && isset($this->dists[$key])){
                if($this->dists[$key] < (1-$increment)){ // don't go above one
                    $this->dists[$key] += $increment;
                }
            }
            elseif(isset($this->real[$key]) && $value == $this->real[$key] && isset($this->dists[$key])){
                // Do nothing. This actually happens, rarely.
            }
            else{ // statistic does not have a dist
                // Control
//                if($key == 'Control'){
//                    if($value > $this->real[$key]){
//                        if($this->control > 0){
//                            $this->control--;
//                        }
//                    }
//                    elseif($value < $this->real[$key]){
////                        if($this->control < 10){
//                            $this->control++;
////                        }
//                    }
//                    else{
//                        echo 'Control exactly right!';
//                    }
//                }
                // Other statistics
            }
        }
        $this->normalize();
        return $this->dists;
    }
    public function calculateAnalogs(){
        $this->analogs['PU'] = ($this->battersFaced() - $this->hits() - $this->totals['SO'] - $this->totals['BB']) * $this->totals['HR'] / $this->hits() / 100 / 18;
        $this->analogs['SO'] = $this->totals['SO'] / $this->outs();
        $this->analogs['GB'] = ($this->hits() - $this->totals['HR']) / $this->hits();
        $this->analogs['FB'] = $this->totals['HR'] / $this->hits();
        $this->analogs['BB'] = $this->totals['BB'] / $this->outs();
        $this->analogs['1B'] = ($this->hits() - $this->totals['HR']) / $this->outs();
        $this->analogs['2B'] = ($this->hits() - $this->totals['HR'] - ($this->hits() / 2.5)) / $this->outs();
        $this->analogs['3B'] = '';
        $this->analogs['HR'] = $this->totals['HR'] * 5 / $this->outs();
        $this->analogs['Control'] = $this->totals['SO'] / $this->totals['BB'] / 10;
    }
    public function printeverything() {
        echo "************** Dumping Pitcher instance *******************\n";
        foreach($this as $key => $value) {
            if(!is_array($key) && !is_array($value)){
                echo "$key => $value\n";
            }
            else{
                print_r($key);
                print_r($value);
            }
           //printf($key." => %f\n",$value);
        }
        echo "Outs: ".$this->outs()."\n";
        echo "Hits: ".$this->hits()."\n";
        echo "Hits + Walks: ".$this->hitspluswalks()."\n";
        echo "Plate Appearances: ".$this->battersFaced()."\n";
        echo "Atbats: ".$this->atbats()."\n";
        echo "*********************************************************\n";
    }
    public function getState(){
        return array($this->real, 
            $this->analogs,
            $this->totals, 
            $this->dists, 
            $this->control, 
            "Outs: ".$this->outs(),
            "Hits: ".$this->hits(),
            "Hits + Walks: ".$this->hitspluswalks(),
            "Plate Appearances: ".$this->battersFaced(),
            "Atbats: ".$this->atbats());
    }
}
?>