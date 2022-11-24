<?php
require_once("ml_model.php");
header("Content-Type:application/json");
$arr_err = array("errCode" => 400,"errMsg" =>"Invalid Request");
$tbl_no = (!isset($_GET['tbl_cnt']))?0:(int)$_GET['tbl_cnt'];
$col_no = (!isset($_GET['col_no']))?"0":$_GET['col_no'];
if (!isset($_GET['s']) )
{
	$arr_err["errMsg"] = "Missing Spreadsheet url";
	//echo json_encode($arr_err);
	//echo "\n".$_GET['t'];
	//echo "\n".$JWT_SECRET_KEY;
	return;
}
$url = trim($_GET['s'],'\'"');
$table = google_sheet_to_csv($url);
//echo json_encode($table);
$data = $table[$tbl_no];
$cols = explode(",",$col_no);
echo "---------------------------------------\n";
echo json_encode($data);
$rows = explode("\n",$data);
$arr = [];

    //echo "------------row---------------------------\n";
    //echo json_encode($c);
    for ($i = 0;$i < count($cols);$i++)
    {
        if (strlen($cols[$i]) <= 0) continue;
        if (count($arr) <= $i || !is_array($arr[$i]));
            $arr[$i] = "";
            for ($j = 0;$j < count($rows);$j++)
            {
                $c = explode(",",$rows[$j]);
                $arr[$i] .= $c[(int)$cols[$i]].",";
            }
        //echo "------------ col[".((int)$cols[$i])."] ---------------------------\n".$c[(int)$cols[$i]]."\n";

    }

echo "---------------------- END -----------------\n";
echo json_encode($arr);
/*echo "<table>";

foreach ($table as $row):
    echo "<tr>";
    
    foreach ($row as $cell):
        echo "<td>" . $cell . "</td>";
    endforeach;
    
    echo "</tr>";
endforeach;

echo "</table>";
*/
?>