<?php
error_reporting(E_ALL ^ E_NOTICE);  
require_once("ml_model.php");
require_once("Statistics.php");
header("Content-Type:application/json");
// for reading google spreadsheet as csv
// 1. Publich spreadsheet to the web , and use this linl
// https://docs.google.com/spreadsheets/d/{spread_seet_id}/pub?output=csv
$arr_err = array("errCode" => 400,"errMsg" =>"Invalid Request");
$col_no = (!isset($_GET['col_no']))?"0":trim($_GET["col_no"],"\"'");//$_GET['col_no'];
$startIndex = (!isset($_GET['strt_indx']))?"1":trim($_GET["strt_indx"],"\"'");
$len = (!isset($_GET['l']))?"5":trim($_GET["l"],"\"'");
$reverse_read = (isset($_GET['reverse_read'])? true:false);
$header = (isset($_GET['header'])? true : false);
$startIndex = (!isset($_GET['strt_indx']))?"1":trim($_GET["strt_indx"],"\"'");
$len = (!isset($_GET['l']))?"5":trim($_GET["l"],"\"'");
$future_len = (!isset($_GET['predict']))?5:trim($_GET["predict"],"\"'");
//$return_prediction = (!isset($_GET['retunr_predict']) && strlen($_GET['retunr_predict'])> 0)?false:trim($_GET["retunr_predict"],"\"'");
$ma = (!isset($_GET['ma']))?21:trim($_GET["ma"],"\"'");
// if there is no patterns produce one can reduce min_accuracy or min_efficiency 
// or l (length) of the pattern
$accuracy = (!isset($_GET['min_accuracy']))?"0.5":trim($_GET["min_accuracy"],"\"'");
$gridRows = (!isset($_GET['min_efficiency']))?"5":trim($_GET["min_efficiency"],"\"'");
if (!isset($_GET['s']) )
{    //echo "-----------------------2 ---------------\n";
	$arr_err["errMsg"] = "Missing Spreadsheet url";
	//echo json_encode($arr_err);
	//echo "\n".$_GET['t'];
	//echo "\n".$JWT_SECRET_KEY;
	echo json_encode($arr_err);
    return;
}

$url = trim($_GET["s"],"\"'");
$data = google_sheet_read_csv($url,-1,$header,$reverse_read);
echo "---------------------- ROWS[".count($data)."] ---------------------\n";
//print_r($data);
$a = array_column($data,(int)$col_no);
$ma_arr = [];
$stat = new Statistics();
$stat->moving_average($a,$ma,$ma_arr);
echo "---------------------- COLS [".count($a)."] ---------------------\n";
$out = checkPatterns($ma_arr,$startIndex,$len,$gridRows,$accuracy);
echo "---------------------- Finish [".count($out)."]---------------------------\n";
$tm = [];
for ($i = 0;$i < count($out);$i++)
{
    $tm[] = ((string)$data[$out[$i]][0]).":".((string)$data[$out[$i]][1]); 
}

$pr = patternRange($a,$out,$future_len);
print_r($pr);
print_r($tm);


?>