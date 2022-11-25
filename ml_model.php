<?php

function google_sheet_to_csv($html_link = NULL){
    //$local_html = "sheets.html";
    $file_contents = file_get_contents($html_link);
    /*$curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $html_link);
       curl_setopt($curl, CURLOPT_HEADER, 0);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
 
       // get the spreadsheet data using curl
       $file_contents = curl_exec($curl);
       curl_close($curl);
    */
    //file_put_contents($local_html,$file_contents);
    
    $dom        = new DOMDocument();  
    $html       = @$dom->loadHTML('<?xml encoding="utf-8" ?>' .$file_contents);  //Added a @ to hide warnings - you might remove this when testing
    $dom->preserveWhiteSpace = false;   
   /*
    echo '---------------------------------<br/>';
    echo json_encode($dom);
    echo '---------------------------------<br/>';
  */
    $csv = "";
    $tables     = $dom->getElementsByTagName('table');
    //echo "-------------- tables[".$tables->length."]-----------------";   
    //for ($cnt = 0;$cnt < $tables->length - 1;$cnt++)
    $tables_csv = [];
    foreach($tables as $cnt => $tbl) 
    {
        $rows       = /*$tables->item($cnt)*/$tbl->getElementsByTagName('tr'); 
        if (is_null($rows->item(0))) continue;
        $tbl_rows = "";
        for ($i = 0;$i < $rows->length;$i++)
        {
            //echo $rows->item($i)->nodeValue ."\n";
            $cols       = $rows->item($i)->getElementsByTagName('td');
            $row_csv = "";
            for ($j = 0;$j < $cols->length;$j++)
            {
              $row_csv .= $cols->item($j)->textContent." , ";
            }
            $tbl_rows .= $row_csv."\n";
            //echo $row_csv."\n";
        }
        $tables_csv[] = $tbl_rows;
       
    }
    //Save to a file and/or output 
    //file_put_contents("result.csv",$csv);
    return $tables_csv;//$csv;
}

/*
 * Look for a model and search entire array ($a)
 * to match this model and if found will save 
 * array index to array witch will return 
*/
function checkPatterns($a,$patternStart,$len,$gridRows,$minMatch = 0.6)
{
   $model = createPattern($a,$patternStart,$len,$gridRows);
   $foundAt = [];
   for ($i = $patternStart + $len;$i < count($a)-$len;$i++)
   {
      $comparePattern = createPattern($a,$i,$len,$gridRows);
      $diff = array_diff_assoc($model,$comparePattern);
      if (count($diff) <= 0) {
         // 100% match
         $foundAt[] = $i;
         $i += $len;
      }else if (count($diff) > 0 && (1 - ((float)count($diff)/(float)count($model))) >= $minMatch)
      {
         $foundAt[] = $i;
         $i += $len;
      }
   }// end for
   return $foundAt;
}
 /*
 * Convert array values into grid values
 * by creating the grid based on min , max array values
 * and rows parameter and place array elements 
 * inside the grid based on elemenet possition and value
 *  @param $a - array of values
 *  @param $start - start index of range
 *  @parma $len - length for the range
 *  @param $rows - for creating grid for the pattern 
 *  slice the $a from $start with $len and create pattern based
 *  on grid created on $rows  
 */
 function createPattern($a,$start,$len,$rows)
 {
    if (!is_array($a))
     return -1;
   $arr = array_slice($a,$start,$len);
   //echo "-------------------- Slice stsrt[" .$start . "] len[" . $len ."]\n<br/>";
   //print_r($arr);
    $max = max($arr);
    $min = min($arr);
    $d_col = ($max-$min)/(int)$rows;
    //echo "-------------- max[".$max."]min[".$min."] rows[".$rows."][".count($arr)."]-------\n<br/>";
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