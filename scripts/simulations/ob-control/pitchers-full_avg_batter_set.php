<?php
$p_charts = array();
for($c = 0; $c <= 6; $c++){
    for($outs = 12; $outs <= 19; $outs++){
        $p_charts[$c.','.$outs] = array();//[$c, $outs];
    }
}
// After this loop runs, p_charts each pitcher chart saying how it performs against each batter strength, ordered 
for($ob = 4; $ob <= 11; $ob++){
    for($b_outs = 1; $b_outs <= 6; $b_outs++){
        // Find how each chart performs agains this one batter
        foreach($p_charts as $key => &$p_chart){
            $p_control = substr($key,0,1);
            $p_outs = substr($key,2,2);
            if($p_control < $ob){
                $p_chart[$ob.','.$b_outs] = ($ob-$p_control)/20*(20-$b_outs)/20 + (20-($ob-$p_control))/20*(20-$p_outs)/20;
            }
            else{
                $p_chart[$ob.','.$b_outs]  = /* No chance of getting batter's chart + */ (20-$p_outs)/20;
            }
            asort($p_chart);
        }
        print_r($p_chart);exit;
    }
}
//print_r($p_charts);
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
    foreach ($value1 as $key2 => $value2) { // loops 48 times
//        if($key2 == '8,4'){
            $y[$key2][$key1] = $value2;
//        }
    }
}
foreach ($y as $key => &$value) {
    asort($value);
}
//asort($y['8,4']);
//print_r($y['4,1']);
//echo array_search("0,12",array_keys($y['8,4']));



$sum = 0;
$z = array_fill_keys(array_keys($y['8,4']), 0);
$zz = array_fill_keys(array_keys($y['8,4']), 0);
foreach ($y as $key => $value) {
    foreach ($value as $k => $v) {
        $z[$k] += array_search($k,array_keys($value));
        $zz[$k] += $v;
    }
    
}

foreach ($zz as $key => &$value) {
    $value /= 48;
}

asort($z);
asort($zz);
print_r($z);
print_r($zz);
////echo $z['10,4'];
//echo array_search("0,12",array_keys($y['10,4']));


