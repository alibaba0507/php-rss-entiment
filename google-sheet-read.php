<?php
error_reporting(E_ALL ^ E_NOTICE);  
require_once("ml_model.php");
header("Content-Type:application/json");
//echo "-----------------------1 ---------------\n";
$arr_err = array("errCode" => 400,"errMsg" =>"Invalid Request");
$tbl_no = (!isset($_GET['tbl_cnt']))?0:(int)trim($_GET["tbl_cnt"],"\"'");//(int)$_GET['tbl_cnt'];
$col_no = (!isset($_GET['col_no']))?"0":trim($_GET["col_no"],"\"'");//$_GET['col_no'];
// patterns parameters , startIndex,len , accuracy (0 to 1
if (!isset($_GET['s']) )
{
    //echo "-----------------------2 ---------------\n";
	$arr_err["errMsg"] = "Missing Spreadsheet url";
	//echo json_encode($arr_err);
	//echo "\n".$_GET['t'];
	//echo "\n".$JWT_SECRET_KEY;
	echo json_encode($arr_err);
    return;
}

$url = trim($_GET["s"],"\"'");
$table = google_sheet_to_csv($url);
//echo json_encode($table);
$data = $table[$tbl_no];
$cols = explode(",",$col_no);
//echo "---------------------------------------\n";
//echo json_encode($data);
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
            // echo "--------------[".$c[(int)$cols[$i]]."][".$i."]-------------\n";
        }
    //echo "------------ col[".((int)$cols[$i])."] ---------------------------\n".$c[(int)$cols[$i]]."\n";

}
$startIndex = 2;
$len = 5;
$accuracy = 0.5;
$gridRows = 3;
$patterns = [];
for ($i = 0;$i < count($arr);$i++)
{
   $a = explode(",",$arr[$i]);
   // remove empty values
   $a = array_filter($a, function($v){return !empty($v) || $v === 0 || is_numeric($v);});
   $found = checkPatterns($a,$startIndex,$len,$gridRows,$accuracy); // array of indexes of patterns
   $patterns[] = $found;
   
}
$disp = $patterns[0];
if (count($patterns) > 1)
{
    for ($i = 1;$i < count($patterns);$i++)
        $disp = array_intersect($disp,$patterns[$i]);
}
print_r($patterns);
print_r($disp);
//echo "---------------------- END -----------------\n";
//echo json_encode($arr);
//print_r($arr);

?>