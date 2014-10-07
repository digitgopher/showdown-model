<?php
$p_charts = array();
for($c = 0; $c <= 6; $c++){
    for($outs = 12; $outs <= 19; $outs++){
        $p_charts[$c.','.$outs] = array();//[$c, $outs];
    }
}

$numBatters = 1000;
// Create an array of distributed batters
for ($index = 0; $index < $numBatters; $index++) {
    $batters[] = purebell(7.47,1.29).",".purebell(4.08,1.06);
}
//print_r($batters);

// After this loop runs, p_charts each pitcher chart saying how it performs against each batter strength, ordered 
foreach ($batters as $dontNeedThisVariable => $value) {
    if(strlen($value) == 3){
        $ob = substr($value,0,1);
        $b_outs = substr($value,2,1);
    }
    elseif(strlen($value) == 4){
        $ob = substr($value,0,2);
        $b_outs = substr($value,3,1);
    }
    else{
        echo 'This shouldnt happen ever';
    }
//    for($ob = 4; $ob <= 11; $ob++){
//    for($b_outs = 1; $b_outs <= 6; $b_outs++){
    // Find how each chart performs agains this one batter
    foreach($p_charts as $key => &$p_chart){
        $p_control = substr($key,0,1);
        $p_outs = substr($key,2,2);
        if($p_control < $ob){
            $p_chart[/*$ob.','.$b_outs*/] = [$ob.','.$b_outs,($ob-$p_control)/20*(20-$b_outs)/20 + (20-($ob-$p_control))/20*(20-$p_outs)/20];
        }
        else{
            $p_chart[/*$ob.','.$b_outs*/]  = [$ob.','.$b_outs,/* No chance of getting batter's chart + */ (20-$p_outs)/20];
        }
        asort($p_chart);
    }
//    }
//}
}

//print_r($p_charts);exit;
// 7*8 = 56 pitcher charts
// 8*6 = 48 batter charts
//$y = array();
// $y is the reverse of $p_charts so we can operate on them
//foreach ($p_charts['4,15'] as $key => $value) {
//    $y[$key] = null;
//}
//print_r($y);
//build $y
foreach ($p_charts as $key1 => $value1) { // loops 56 times
    foreach ($value1 as $key2 => $value2) { // loops $numBatters times
        // Each $y[] has an array of len 3: batterChart,pitcherChart,OBP
        $y[] = [$value2[0],$key1,$value2[1]];
    }
}

usort($y, function($a, $b) {
    return round($a[2]*10000) - round($b[2]*10000);
});
//print_r($y);exit;

$u = array_fill_keys(array_keys($p_charts), 0);
$uu = array_fill_keys(array_keys($p_charts), 0);
//print_r($u);exit;
foreach ($y as $k => $value) {
//    foreach ($value as $k => $v) {
        $u[$value[1]] += $k;//array_search($k,array_keys($value));
        $uu[$value[1]] += $value[2];//$v;
//    }
}

foreach ($uu as $key => &$value) {
    $value /= $numBatters;
}

asort($u);
asort($uu);
print_r($u);
print_r($uu);

////echo $z['10,4'];
//echo array_search("0,12",array_keys($y['10,4']));

// function modified from this site
// http://www.eboodevelopment.com/php-random-number-generator-with-normal-distribution-bell-curve/
function purebell($mean,$std_deviation,$min=0,$max=20,$step=1) {
  $rand1 = (float)mt_rand()/(float)mt_getrandmax();
  $rand2 = (float)mt_rand()/(float)mt_getrandmax();
  $gaussian_number = sqrt(-2 * log($rand1)) * cos(2 * M_PI * $rand2);
  //$mean = ($max + $min) / 2;
  $random_number = ($gaussian_number * $std_deviation) + $mean;
  $random_number = round($random_number / $step) * $step;
  if($random_number < $min || $random_number > $max) {
    $random_number = purebell($min, $max,$std_deviation);
  }
  return $random_number;
}

//for ($index = 0; $index < 10000; $index++) {
//    echo purebell(7.47,1.29).",";
//}

