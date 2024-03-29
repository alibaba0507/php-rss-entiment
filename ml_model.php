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

function realRenage($a,$startIndex,$len){
    if (!is_array($a) || count($a) == 0)
        return ["NA","NA"];
    $top = 0.0;
    $bottom = 0.0;
   $arr = array_slice($a,$startIndex - ($len-1),$len);
   $max = max($arr);
    $min = min($arr);
    $top += (trim($max," \"'") - trim($a[$startIndex]," \"'"));
    $bottom += (trim($a[$startIndex]," \"'") - trim($min," \"'") );
    $ret = [round($top,5),round($bottom,5)];
    return $ret;
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
        $arr = array_slice($a,($startIndex[$i] - ($len))+1,$len); // backwards
        $max = max($arr);
        $min = min($arr);
        $top += (trim($max," \"'") - trim($a[$startIndex[$i]]," \"'"));
        $bottom += (trim($a[$startIndex[$i]]," \"'") - trim($min," \"'") );
    }
    $top /= count($startIndex);
    $bottom /= count($startIndex);
    $ret = [round($top,5),round($bottom,5)];
    return $ret;
}
function printGrid($grid,$gridRows,$grdName = "")
{
    $s = "";
    echo "----------------[".$grdName ."]----------------------\n";
    for ($i = 0;$i<count($grid);$i++)
    {
        $s .= $grid[$i]."  ,  ";
        if ( (($i+1) % $gridRows) == 0)
        {
            $s.= "<br\>\n";
            echo $s;         
            $s = "";
        }
    }

    echo "--------------------------------------\n";
}

function checkGridPatterns($a,$patternStart,$len,$gridRows,$minMatch = 0.6,$predicted_len = 0)
{
    $model_grid = createModelGrid($a,$patternStart,$len,$gridRows);
    $foundAt = [];
    $grdCnt = 0;
    $predicted_len = ($patternStart + $len < $predicted_len)?$predicted_len - ($patternStart + $len):0;
    for ($j = $patternStart + $len + $predicted_len;$j < count($a)-$len;$j++)
   {
        $arr = array_slice($a,$j,$len);
        $max = max($arr);
        $min = min($arr);
        $d_col = ($max-$min)/(int)$gridRows;
        
        $d_row = count($arr)/(int)$gridRows;
        $accuracy = 0.0;
        for ($i = 0;$i < count($arr);$i++)
        {
            $col = ($max - $arr[$i])/(float)$d_col;
            $col = ($col == 0.0)?0.1:$col;
            $r = ceil(($i+1)/(float)$d_row);
            $cell = (((ceil($col) - 1)*$gridRows)+$r);
            $accuracy += $model_grid[$cell];
        }
        $accuracy /= count($arr);
        if ($accuracy >= $minMatch)
        {
           /* if ($grdCnt < 1)
            {
                $grd = createModelGrid($a,$j,$len,$gridRows);
                printGrid($grd,$gridRows,"Compare[".$accuracy."]accuracy Grid");
                $grdCnt++;
            }
            */
            $foundAt[] = $j;
            $j += $len;
        }
   }// end for
   return $foundAt;
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
      $min_row = $comparePattern['min'] % $gridRows;
      $max_row = $comparePattern['max'] % $gridRows;
      $min_check = ((($min_row > 1) && ($min_row < 0)) ?
                 (($model['min'] == $comparePattern['min']-1)|| ($model['min'] == $comparePattern['min'] + 1))
                 :(($min_row == 1)?  ($model['min'] == $comparePattern['min'] + 1)
                 : (($min_row == 0)?  ($model['min'] == $comparePattern['min'] - 1):false)));
       $max_check = ((($max_row > 1) && ($max_row < 0)) ?
                 (($model['max'] == $comparePattern['max']-1)|| ($model['max'] == $comparePattern['max'] + 1))
                 :(($max_row == 1)?  ($model['max'] == $comparePattern['max'] + 1)
                 : (($max_row == 0)?  ($model['max'] == $comparePattern['max'] - 1):false)));           
      //echo "------ Min[".((string)$comparePattern['min'])."] max[".((string)$comparePattern['max'])."]----\n";
      if (count($diff) <= 0) {
         // 100% match
         $foundAt[] = $i;
         $i += $len;
      }else if (count($diff) > 0 && (1 - ((float)count($diff)/(float)count($model['grid']))) >= $minMatch
            && $model['grid'][0] == $comparePattern['grid'][0] 
            && $model['grid'][count($model['grid'])-1] == $comparePattern['grid'][count($comparePattern['grid'])-1]
            && ($model['min'] == $comparePattern['min'] || $min_check)
            && ($model['max'] == $comparePattern['max'] || $max_check))
      {
         $foundAt[] = $i;
         $i += $len;
      }
   }// end for
   return $foundAt;
}

function createModelGrid($a,$start,$len,$rows)
{
    $grid = array_fill(0,((int)$rows*(int)$rows),0);
    if (!is_array($a)|| count($a)==0)
     return $grid;
    
    $arr = array_slice($a,$start,$len);
    $max = max($arr);
    $min = min($arr);
    $d_col = ($max-$min)/(int)$rows;
    $d_row = count($arr)/(int)$rows;
    for ($i = 0;$i < count($arr);$i++)
    {
      $col = ($max - $arr[$i])/(float)$d_col;
      $col = ($col == 0.0)?0.1:$col;
      $r = ceil(($i+1)/(float)$d_row);
      $cell = (((ceil($col) - 1)*$rows)+$r);
      $column = $cell % $rows;
      $row = floor($cell / $rows);
      $grid[$cell] = 1;
      if ($column > 1 && $grid[($cell - 1)] != 1)
      {

        $grid[($cell - 1)] = 0.25;
       // echo "--- DWON COL[".$column."] [".$cell."][".($cell - 1)."]-----\n";
      }
      if ($column < ($rows - 1) && $grid[($cell + 1)] != 1)
      {

        $grid[($cell + 1)] = 0.25;
      //  echo "--- UP COL[".$column."] [".$cell."][".($cell + 1)."]-----\n";
      }
      if ($row > 0 &&  $grid[($cell - $rows)] != 1)
      {  
        $grid[($cell - $rows)] = 0.5;
        //echo "--- TOP COL[".$row."] [".$cell."][".($cell - $rows)."]-----\n";
      }
      if ($row < ($rows - 1) && $grid[($cell + $rows)] != 1)
      {
        $grid[($cell + $rows)] = 0.5;
        //echo "--- BOTTOM COL[".$row."] [".$cell."][".($cell + $rows)."]-----\n";
      }
      /*if ($column > 1 && $row > 0 && ($grid[($cell - $rows) - 1] != 1 && $grid[($cell - $rows) - 1] != 0.5))
      {
        $grid[($cell - $rows) - 1] = 0.25;
       // echo "--- TOP LEFT[".$row."] [".$cell."][".(($cell - $rows) - 1)."]-----\n";
      }
      if ($column < ($rows - 1) && $row > 0 && ($grid[($cell - $rows) + 1] != 1 && $grid[($cell - $rows) + 1] != 0.5 ))
      {
        $grid[($cell - $rows) + 1] = 0.25;
       // echo "--- TOP RIGHT[".$row."] [".$cell."][".(($cell - $rows) + 1)."]-----\n";
      }
      //---------------
      if ($column > 1 && $row < ($rows - 1) && ($grid[($cell + $rows) - 1] != 1 && $grid[($cell + $rows) - 1] != 0.5))
      {
        $grid[($cell + $rows) - 1] = 0.25;
       // echo "--- BOTTOM LEFT[".$row."] [".$cell."][".(($cell + $rows) - 1)."]-----\n";
      }
      if ($column < ($rows - 1) && $row < ($rows - 1) && ($grid[($cell + $rows) + 1] != 1 && $grid[($cell + $rows) + 1] != 0.5))
      {
        $grid[($cell + $rows) + 1] = 0.25;
      //  echo "--- BOTTOM RIGHT[".$row."] [".$cell."][".(($cell + $rows) + 1)."]-----\n";
      }
      */
    }
    return $grid;
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
    $grid = array_fill(0,((int)$rows*(int)$rows),0);
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
      
      $grid[(((ceil($col) - 1)*$rows)+$r)] = 1;

      if ($arr[$i] == $max)
       $maxIndx = $out[$i];
      if ($arr[$i] == $min)
       $minIndx = $out[$i];
    }
    $ret = [];
    $ret['min'] = $minIndx;
    $ret['max'] = $maxIndx;
    $ret['grid'] = $out;
    $ret['pattern'] = $grid;
    return $ret;
 }
  
?>