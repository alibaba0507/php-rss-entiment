<?php

function google_sheet_to_csv($html_link = NULL){
    $local_html = "sheets.html";
    $file_contents = file_get_contents($html_link);
    /*$curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $html_link);
       curl_setopt($curl, CURLOPT_HEADER, 0);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
 
       // get the spreadsheet data using curl
       $file_contents = curl_exec($curl);
       curl_close($curl);
    */
    file_put_contents($local_html,$file_contents);
    
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
    echo "-------------- tables[".$tables->length."]-----------------";   
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
function google_sheet($url = NULL) {
 
    $array = array();
 
    if ($url):
       // initialize curl
       $curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $url);
       curl_setopt($curl, CURLOPT_HEADER, 0);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
 
       // get the spreadsheet data using curl
       $sheet = curl_exec($curl);
       curl_close($curl);
       echo json_encode( $sheet);
       // find the table pattern and return the mark-up
       preg_match('/(<table[^>]+>)(.+)(<\/table>)/', $sheet, $matches);
       $data = $matches['0'];
       //echo json_encode( $data);
       // convert the HTML (XML) mark-up to JSON
       $cells_xml = new SimpleXMLElement($data);
       $cells_json = json_encode($cells_xml);
       $cells = json_decode($cells_json, TRUE);
       echo json_encode( $cells);
    endif;
 
    // Convert the JSON array to an array of just the table data
    // This will strip out any Google Sheets formatting and identifiers if they exist
    if ( is_array($cells) ):
       foreach ($cells['tbody']['tr'] as $row => $row_data):
          $column = 'A';
          foreach ($row_data['td'] as $column_index => $cell):
            echo json_encode( $cell);
             // Check that the cell is populated and get the value.
             if (!is_array($cell)):
                $array[($row + 1)][$column++] = $cell;
             elseif (isset($cell['div'])):
                $array[($row + 1)][$column++] = $cell['div'];
             endif;
          endforeach;
       endforeach;
    endif;
   
    return $array;
 }

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