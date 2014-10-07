
<?php
// Returns all the batter charts 4-11 onbase, 1-7 out in order of their average OBP against a set of 56 pitchers (one each of 0-6 control, 12-19 out).

$b_charts = array();
for($ob = 4; $ob <= 11; $ob++){
    for($outs = 1; $outs <= 7; $outs++){
        $b_charts[$ob.','.$outs] = array();//[$c, $outs];
    }
}
// After this loop runs, p_charts each pitcher chart saying how it performs against each batter strength, ordered 
$i = 0;
for($c = 0; $c <= 6; $c++){
    for($p_outs = 12; $p_outs <= 19; $p_outs++){
        // Find how each chart performs agains this one batter
        foreach($b_charts as $key => &$b_chart){
            if(strlen($key) == 3){
                $b_ob = substr($key,0,1);
                $b_outs = substr($key,2,1);
            }
            else{
                $b_ob = substr($key,0,2);
                $b_outs = substr($key,3,1);
            }
            //echo $b_ob.','.$b_outs.' ';
            if($c < $b_ob){
                $b_chart[$c.','.$p_outs] = ($b_ob-$c)/20*(20-$b_outs)/20 + (20-($b_ob-$c))/20*(20-$p_outs)/20;
            }
            else{
                $b_chart[$c.','.$p_outs]  = /* No chance of getting batter's chart + */ (20-$p_outs)/20;
            }
            arsort($b_chart);
            
        }
    }//print_r($b_charts);exit;
}
//print_r($b_charts);exit;
// 7*8 = 56 pitcher charts
// 8*6 = 48 batter charts
//$y = array();
// $y is the reverse of $p_charts so we can operate on them
//foreach ($p_charts['4,15'] as $key => $value) {
//    $y[$key] = null;
//}
//print_r($y);
//build $y
foreach ($b_charts as $key1 => $value1) { // loops 56 times
    foreach ($value1 as $key2 => $value2) { // loops 48 times
//        if($key2 == '8,4'){
            $y[$key2][$key1] = $value2;
//        }
    }
}
foreach ($y as $key => &$value) {
    arsort($value);
}
//arsort($y['8,4']);
//print_r($y);exit;
//echo array_search("0,12",array_keys($y['8,4']));



$sum = 0;
$z = array_fill_keys(array_keys($y['3,15']), 0);
$zz = array_fill_keys(array_keys($y['3,15']), 0);

foreach ($y as $key => $value) {
    foreach ($value as $k => $v) {
        $z[$k] += array_search($k,array_keys($value));
        $zz[$k] += $v;
    }
    
}

foreach ($zz as $key => &$value) {
    $value /= 56;
}

asort($z);
arsort($zz);
print_r($z);
print_r($zz);
////echo $z['10,4'];
//echo array_search("0,12",array_keys($y['10,4']));


