<?php
error_reporting(E_ALL ^ E_NOTICE);  
require 'vendor/autoload.php';
require_once("ml_model.php");
require_once("Statistics.php");
require_once("chart.php");
//header("Content-Type:application/json");
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
{    
	$arr_err["errMsg"] = "Missing Spreadsheet url";
	echo json_encode($arr_err);
    return;
}
$url = trim($_GET["s"],"\"'");
$data = google_sheet_read_csv($url,-1,$header,$reverse_read);
if ($data && $data["err"])
{
    echo json_encode($data);
    return;
}
$a = array_column($data,(int)$col_no);
$j_out = [];
$j_out["data_row"] = count($data);
//------------------------------ collecting data and extract column from spreadsheet as array --------------------------
$ma_arr = [];
$stat = new Statistics();
$stat->moving_average($a,$ma,$ma_arr);
//------------------------ Create array of Moving avarages values based on selected column from spreadsheet -----------
$model_grid = createModelGrid($ma_arr,$startIndex,$len,$gridRows);//create our grid model of the pattern
$out = checkGridPatterns($ma_arr,$startIndex,$len,$gridRows,$accuracy); // find all occurance of the grid patterns
$j_out["patterns"] = count($out);
$tm = [];
for ($i = 0;$i < count($out) ;$i++)
{
    $tm[] = ((string)$data[$out[$i]][0])." ".((string)$data[$out[$i]][1]); 
}
$pr = patternRange($a,$out,$future_len);
if ($startIndex - $future_len >= 0)
{
  $real_range = realRenage($a,$startIndex,$future_len);
  $j_out["real_range"] = $real_range;
}
$pr = patternRange($a,$out,$future_len);     
$j_out["predicted_range"] = $pr;
$j_out["occurance"] = $tm;
$pattern_data = array_slice($a,$startIndex,$len);
$chart_data = [];
for ($i = 0;$i < count($out) ;$i++)
{
    $arr = array_slice($a,$out[$i],$len);
    $chart_data[] = $arr;
}
$chart_data[] = $pattern_data;
//echo "--------------------[".count($chart_data)."]------------------------\n";
//echo 
$ret = drawChart($chart_data/*$pattern_data*/,550,850,20);
$j_out["graph"] = $ret;
echo json_encode( $j_out);
//echo "-------------------------------------------\n";
?>