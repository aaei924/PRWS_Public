<?php
function getTargetExp(int $lv){
    return $lv * $lv + 5;
}

function getArrExp(int $lv, int $currentExp=0){
    return 1 / 6 * $lv * (2 * $lv * $lv -(3 * $lv) + 31) + $currentExp;
}

function getLevel(int $exp){
    $f = pow(sqrt(3) * sqrt(3888 * $exp * $exp -(19440 * $exp) + 229679) - (108 * $exp) + 270, 1/3);
    return floor(-$f / (2 * pow(9,1/3)) + 59 / (2 * pow(3, 1/3) * $f) + 0.5);
}

if(isset($_GET['tl'])) echo getTargetExp($_GET['tl']);
if (isset($_GET['lv'])) echo getArrExp($_GET['lv'], $_GET['ce']);
if (isset($_GET['exp'])) echo getLevel($_GET['exp']);
