<?php
error_reporting(E_ALL ^ E_NOTICE);  
require 'vendor/autoload.php';
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
if ($data && $data["err"])
{
    echo json_encode($data);
    return;
}
$j_out = [];
$j_out["data"] = count($data);
//echo "---------------------- ROWS[".count($data)."] ---------------------\n";
//print_r($data);
$a = array_column($data,(int)$col_no);
$ma_arr = [];
$stat = new Statistics();
$stat->moving_average($a,$ma,$ma_arr);
//echo "---------------------- COLS [".count($a)."] ---------------------\n";
//$model_grid = createPattern($ma_arr,$startIndex,$len,$gridRows);
//print_r($model_grid);
$out = checkPatterns($ma_arr,$startIndex,$len,$gridRows,$accuracy);
//echo "---------------------- Finish [".count($out)."]---------------------------\n";
$j_out["patterns"] = count($out);
$lbl_org = "labels:[";
$dt_org = "data: [";
for ($i = $startIndex;$i < $startIndex + $len;$i++)
{
    $lbl_org .= "'".$i."',";
    $dt_org .= $a[$i].",";
}
$dt_org .= "]";
$ch_dt_org = "data:{".$lbl_org."],datasets:[";
$ch_dt_org .= "{". $dt_org ."}]}";   
$chart_org = new QuickChart(array(
    'width' => 700,
    'height' => 600
  ));
$chart_org->setConfig('{
    type: "line",'.$ch_dt_org.',options: {
        legend: {
           display: false
        },scales: {
            ticks: {
                 stepSize: 0.0001
             }
         }}}');
$j_out["graph_org"] = $chart_org->getUrl();
$tm = [];
$lbl = "labels:[";
$chart_data = [];
for ($i = 0;$i < count($out);$i++)
{
    $tm[] = ((string)$data[$out[$i]][0])." ".((string)$data[$out[$i]][1]); 
    
    $dt = "data: [";
    for ($j = 0;$j < (int)$len;$j++)
    {
      if ($i == 0)
      $lbl .= "'".$j."',";
      $dt .= $a[$out[$i]+$j].",";
    }
    $dt .= "]";
   // echo $dt ."\n";
    $chart_data[]= $dt;
} 
//echo $lbl ."]\n";
$ch_dt = "data:{".$lbl."],datasets:[";
for ($i = 0 ;$i < count($chart_data);$i++)
{
  $ch_dt .= "{".$chart_data[$i]."},";
}
$ch_dt .= "]}";
$chart = new QuickChart(array(
    'width' => 700,
    'height' => 600
  ));
 // echo "---------- [".$ch_dt."]-------\n";
  /*$chart->setConfig('{
    type: "bar",
    data: {
      labels: ["Hello world", "Test"],
      datasets: [{
        label: "Foo",
        data: [1, 2]
      }]
    }
  }');
  */
  $chart->setConfig('{
    type: "line",'.$ch_dt.',options: {
        legend: {
           display: false
        },scales: {
            ticks: {
                 stepSize: 0.0001
             }
         }}}');
  //echo '{type: "line",'.$ch_dt.'}\n';
  //echo $chart->getUrl();
  $j_out["graph"] = $chart->getUrl();
$pr = patternRange($a,$out,$future_len);
if ($startIndex - $future_len >= 0)
{
  $real_range = realRenage($a,$startIndex,$future_len);
  $j_out["real_range"] = $real_range;
}
$pr = patternRange($a,$out,$future_len);     
$j_out["predicted_range"] = $pr;
$j_out["occurance"] = $tm;
echo json_encode($j_out);
//print_r($pr);
//print_r($tm);


?>