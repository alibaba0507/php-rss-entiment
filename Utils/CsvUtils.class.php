<?php
namespace Utils
{
    class CsvUtils {


        function google_sheet_read_csv($html_link = NULL,$col_no = 0,$hasNames = false,$reverseArray = false,$selected_fields_keys = []){
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
            if (count($selected_fields_keys) > 0)
            {
                $names = array();
                $data = array_map(function ($rows) use ($selected_fields_keys) {
                    $d = [];
                    foreach ($selected_fields_keys as $field) {

                        if (array_key_exists($field, $rows)) {
                            $d[$field] = $rows[$field];
                        }
                    }
                    return $d;
                }, $rows);
                return $data;
            }
            $names = array();
            for($i=0; $i<count($rows); $i++) {
                if($i==0 && $hasNames == true){
                    $names = str_getcsv($rows[$i]);
                }else if ($col_no != -1){
                    $tmp = str_getcsv($rows[$i]);
                    $data[] = $tmp[(int)$col_no];
                    //echo "--------------------- [".$rows[$i][$col_no]."]----------------\n";
                }
                else{
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
        

    }
}
?>