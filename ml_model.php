<?php
 /*
 * Convert array values into grid values
 * by creating the grid based on min , max array values
 * and rows parameter and place array elements 
 * inside the grid based on elemenet possition and value
 */
 function createPattern($arr,$rows)
 {
    if (!is_array($arr))
     return -1;
    $max = max($arr);
    $min = min($arr);
    $d_col = ($max-$min)/(int)$rows;
    $d_row = count($arr)/(int)$rows;
    $out = [];
    for ($i = 0;$i < count($arr);$i++)
    {
      $col = ($max - $arr[$i])/(float)$d_col;
      $col = ($col == 0.0)?0.1:$col;
      $r = ceil(($i+1)/(float)$d_row);
      $out[$i] = ((ceil($col) - 1)*$rows)+$r;
    }
    return $out;
 }
  
?>