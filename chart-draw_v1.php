<?php
error_reporting(E_ALL ^ E_NOTICE);  
require 'vendor/autoload.php';
require_once("ml_model.php");
require_once(PROJECT_ROOT_PATH."Utils/Statistics.php");
require_once("chart.php");
require_once("linear_regression.class.php");
require_once("pattern_encrypt_decrypt.php");
use Patterns\Statistics;
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
// ------ Encripted pattern -----------------------------------------------------------------//
$encrpt_pattern = [];
//------------------------ Create array of Moving avarages values based on selected column from spreadsheet -----------
$model_grid = createModelGrid($ma_arr,$startIndex,$len,$gridRows);//create our grid model of the pattern
$encrpt_pattern["model"] = $model_grid;
$encrpt_pattern["grid"] = $gridRows;
$encrpt_pattern["length"] = $len;
//echo '------------------Encryption ------------------------------\n';
$enc = encrpt(json_encode($encrpt_pattern));
$j_out["pattern_template"] = $enc;
//echo $enc;
//echo '\n-----------------Decrypt ------------------------------------------\n';
//$dec = decrpt($enc);
echo $dec;
$out = checkGridPatterns($ma_arr,$startIndex,$len,$gridRows,$accuracy,$future_len); // find all occurance of the grid patterns
$j_out["patterns"] = count($out);
$tm = [];
for ($i = 0;$i < count($out) ;$i++)
{
    $tm[] = ((string)$data[$out[$i]][0])." ".((string)$data[$out[$i]][1]); 
}
//$pr = patternRange($a,$out,$future_len);
if ($startIndex - $future_len >= 0)
{
  $real_range = realRenage($a,$startIndex,$future_len);
  $j_out["real_range"] = $real_range;
}
$pr = patternRange($a,$out,$future_len);     
$j_out["predicted_range"] = $pr;
//$j_out["occurance"] = $tm;
$pattern_data = array_slice($a,$startIndex,$len);
$chart_data = [];
$chart_predict_data = [];
$j_out["linear_reg"] = [];
$slope = 0.0;
$reg_slope = 0.0;
for ($i = 0;$i < count($out) ;$i++)
{
    
    $arr = array_slice($a,$out[$i],$len);
    $lr = new linear_regression( array_reverse($arr) );
    $reg_slope += $lr->getGradient();
    //$j_out["linear_reg"][] = ["m" => $lr->getGradient(),"c" => $lr->getIntercept()];
    $chart_data[] = array_reverse($arr);
    $predict = array_slice($a,$out[$i]-$future_len,$future_len);
    $lr = new linear_regression( array_reverse($predict) );
    $slope += $lr->getGradient();
    //$j_out["linear_reg_predict"][] = ["m" => $lr->getGradient(),"c" => $lr->getIntercept()];
    $chart_predict_data[] = /*array_reverse*/($predict);
}
$j_out["linear_reg"] =  (count($out) > 0) ? round($reg_slope/count($out),4): "NA";
$j_out["linear_slope"] = (count($out) > 0) ? round($slope/count($out),4): "NA";
$chart_data[] = array_reverse($pattern_data);
if ($startIndex > $future_len)
{
  $chart_predict_data[] = array_reverse(array_slice($a,$startIndex-$future_len,$future_len));
}
//echo "--------------------[".count($chart_data)."]------------------------\n";
//echo 
$ret = drawChart($chart_data/*$pattern_data*/,550,850,20,$gridRows);
$ret_predict = drawChart($chart_predict_data/*$pattern_data*/,550,850,20,$gridRows);
$j_out["graph"] = $ret;
$j_out["graph_predict"] = $ret_predict;
echo json_encode( $j_out);
// Format the image SRC:  data:{mime};base64,{data};
$src = 'data:image/png;base64,'.$ret;
// Echo out a sample image
echo '<img src="'.$src.'">'; 
$src = 'data:image/png;base64,'.$ret_predict;
echo '<img src="'.$src.'">'; 
//echo "-------------------------------------------\n";
?>