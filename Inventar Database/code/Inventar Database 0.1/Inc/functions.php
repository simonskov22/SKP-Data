<?php


function print_html($string){
    $withTag = str_replace("<","&lt;",$string);

    echo $withTag;
    echo "<br>";
}

function print_array($array, $tap = 0, $header = ""){
    $tapSpace = $tap * 20;
    $spanText = "<span style='width: {$tapSpace}px; display: inline-block;'></span>";
    
    $tapSpace2 = ($tap+1) * 20;
    $spanText2 = "<span style='width: {$tapSpace2}px; display: inline-block;'></span>";
    
    if($tap == 0){
        if($header != ""){ echo $header." --> ";}
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
            
            $valToString = "";
            if(is_string($value)) $valToString = "\"$value\"";
            else if(is_int($value) || is_bool($value)) $valToString = (string)$value;
            else if(is_object($value)) $valToString = "(class) ". get_class($value);
                
                
            echo "{$spanText2}[$key] => $valToString<br>";
        }
    }
    
    echo "{$spanText}}<br>";
    echo "<br>";
}
function array_removeVal($array, $value){
    
    $newArray = array();
    
    foreach ($array as $key => $item) {
        if($value != $item){
            $newArray[$key] = $item;
        }
    }
    
    return $newArray;
}


function strshort($string, $short){
    
    if($short > 0)
    {
        $short = $short * -1;
    }
    
    return substr($string, 0, $short);
}


// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $max_size = parse_size(ini_get('post_max_size'));

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}

function parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}

function ByteToHighest($val){

    $TB = pow(1024,4);
    $GB = pow(1024,3);
    $MB = pow(1024,2);

    if($val >= $TB){
        return ($val / $TB) ." TB";
    }
    else if($val >= $GB){
        return ($val / $GB) ." GB";                        
    }
    else if($val >= $MB){
        return ($val / $MB) ." MB";                        
    }
    else {
        return $val ." BYTE";
    }                    
}