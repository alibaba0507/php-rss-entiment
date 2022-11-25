<?php
error_reporting(E_ALL ^ E_NOTICE);  
require_once("ml_model.php");
header("Content-Type:application/json");
//echo "-----------------------1 ---------------\n";
$arr_err = array("errCode" => 400,"errMsg" =>"Invalid Request");
$tbl_no = (!isset($_GET['tbl_cnt']))?0:(int)trim($_GET["tbl_cnt"],"\"'");//(int)$_GET['tbl_cnt'];
$col_no = (!isset($_GET['col_no']))?"0":trim($_GET["col_no"],"\"'");//$_GET['col_no'];
$len =  (!isset($_GET['l']))?"5":trim($_GET["l"],"\"'");//$_GET['col_no'];
if (!isset($_GET['p']))
{
    $arr_err["errMsg"] = "Missing Pattern Indexes";
	echo json_encode($arr_err);
}    
$patterns = json_decode( trim($_GET["p"],"\"'"));//$_GET['col_no']; 
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

$res  =[];
for ($i = 0;$i < count($arr);$i++)
{
   $a = explode(",",$arr[$i]);
   // remove empty values
   $a = array_filter($a, function($v){return is_numeric(trim($v," ")) === true;});

   $res = patternRange($a,$patterns,$len);
   break;  
   
}

echo json_encode($res);
//print_r($arr);

?>