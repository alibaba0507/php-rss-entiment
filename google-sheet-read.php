<?php
require_once("ml_model.php");
header("Content-Type:application/json");
$arr_err = array("errCode" => 400,"errMsg" =>"Invalid Request");
if (!isset($_GET['s']) )
{
	$arr_err["errMsg"] = "Missing Spreadsheet url";
	echo json_encode($arr_err);
	//echo "\n".$_GET['t'];
	//echo "\n".$JWT_SECRET_KEY;
	return;
}
$url = trim($_GET['s'],'\'"');
$table = google_sheet_to_csv($url);
echo json_encode($table);
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