<?php

define("DB_HOST", "127.0.0.1");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_DATABASE", "test");
define("DB_PREFIX", "InvDb05_Beta_");

function print_array($array, $tap = 0){
    $tapSpace = $tap * 20;
    $spanText = "<span style='width: {$tapSpace}px; display: inline-block;'></span>";
    
    $tapSpace2 = ($tap+1) * 20;
    $spanText2 = "<span style='width: {$tapSpace2}px; display: inline-block;'></span>";
    
    if($tap == 0){
        echo "{$spanText}start array<br>";
    }
    
    echo "{$spanText}{<br>";
    if(!is_array($array)){
        echo "not an array<br>";
        return;
    }
    
    foreach ($array as $key => $value) {
        if(is_array($value)){
            echo "<br>{$spanText2}[{$key}]<br>";
            //$tap++;
            print_array($value, $tap +1);
        }
        else{
            
            echo "{$spanText2}[$key] => $value<br>";
        }
    }
    
    echo "{$spanText}}<br>";
    echo "<br>";
}

function strshort($string, $short){
    
    if($short > 0)
    {
        $short = $short * -1;
    }
    
    return substr($string, 0, $short);
}