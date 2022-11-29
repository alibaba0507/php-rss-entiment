<?php
function google_sheet_read_csv($html_link = NULL,$col_no = 0,$hasNames = false,$reverseArray = false){
    //$spreadsheet_url = @file_get_contents($html_link);
    //$csv = file_get_contents($spreadsheet_url);
    $data = array();
    if (($csv = @file_get_contents($html_link)) === false) {
        $error = error_get_last();
        return $data["err"] = "HTTP request failed. Error was: " . $error['message'];
    } /*else {
            echo "Everything went better than expected";
    }*/
    $rows = explode("\n",$csv);
    
    $names = array();
    for($i=0; $i<count($rows); $i++) {
        if($i==0 && $hasNames == true){
        $names = str_getcsv($rows[$i]);
        }else if ($col_no != -1){
            $tmp = str_getcsv($rows[$i]);
            $data[] = $tmp[(int)$col_no];
            //echo "--------------------- [".$rows[$i][$col_no]."]----------------\n";
        }else{
         $data[] = str_getcsv($rows[$i]);
        }
    }
    //print_r($data);
    if ($reverseArray == true)
    {    
        $data = array_reverse($data);
        
    }
    return $data;
}
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
function patternRange($a,$startIndex,$len)
{
    if (!is_array($a) || count($a) == 0)
     return ["NA","NA"];
    if (!is_array($startIndex) || count($startIndex) == 0)
        return ["NA","NA"];
    $top = 0.0;
    $bottom = 0.0;
    
    for ($i = 0;$i < count($startIndex);$i++)
    {
        $arr = array_slice($a,$startIndex[$i] - ($len-1),$len); // backwards
        $max = max($arr);
        $min = min($arr);
        $top += (trim($max," \"'") - trim($a[$startIndex[$i]]," \"'"));
        $bottom += (trim($a[$startIndex[$i]]," \"'") - trim($min," \"'") );
    }
    $top /= count($startIndex);
    $bottom /= count($startIndex);
    $ret = [$top,$bottom];
    return $ret;
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
      $diff = array_diff_assoc($model['grid'],$comparePattern['grid']);
      //echo "------ Min[".((string)$comparePattern['min'])."] max[".((string)$comparePattern['max'])."]----\n";
      if (count($diff) <= 0) {
         // 100% match
         $foundAt[] = $i;
         $i += $len;
      }else if (count($diff) > 0 && (1 - ((float)count($diff)/(float)count($model['grid']))) >= $minMatch
            && $model['grid'][0] == $comparePattern['grid'][0] 
            && $model['grid'][count($model['grid'])-1] == $comparePattern['grid'][count($comparePattern['grid'])-1]
            && $model['min'] == $comparePattern['min']
            && $model['max'] == $comparePattern['max'])
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
    $out = [];
    if (!is_array($a)|| count($a)==0)
     return $out;
    
   $arr = array_slice($a,$start,$len);
   //echo "-------------------- Slice stsrt[" .$start . "] len[" . $len ."][".count($arr)."]\n<br/>";
   //print_r($a);
    $max = max($arr);
    $min = min($arr);
    $d_col = ($max-$min)/(int)$rows;
    
    $d_row = count($arr)/(int)$rows;
    //echo "-------------- max[".$max."]min[".$min."] rows[".$rows."] d_row[".$d_row."] d_col[".$d_col."]-------\n<br/>";
    $maxIndx = -1;
    $minIndx = -1;
    for ($i = 0;$i < count($arr);$i++)
    {
      $col = ($max - $arr[$i])/(float)$d_col;
      $col = ($col == 0.0)?0.1:$col;
      $r = ceil(($i+1)/(float)$d_row);
      $out[$i] = ((ceil($col) - 1)*$rows)+$r;
      if ($arr[$i] == $max)
       $maxIndx = $out[$i];
      if ($arr[$i] == $min)
       $minIndx = $out[$i];
    }
    $ret = [];
    $ret['min'] = $minIndx;
    $ret['max'] = $maxIndx;
    $ret['grid'] = $out;
    return $ret;
 }
  
?>