<?php
error_reporting(E_ALL ^ E_NOTICE);  
require 'vendor/autoload.php';
require_once("ml_model.php");
require_once("Statistics.php");
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
//------------------------------ collecting data and extract column from spreadsheet as array --------------------------
$ma_arr = [];
$stat = new Statistics();
$stat->moving_average($a,$ma,$ma_arr);
//------------------------ Create array of Moving avarages values based on selected column from spreadsheet -----------
$model_grid = createModelGrid($ma_arr,$startIndex,$len,$gridRows);//create our grid model of the pattern
$out = checkGridPatterns($ma_arr,$startIndex,$len,$gridRows,$accuracy); // find all occurance of the grid patterns
$pattern_data = array_slice($a,$startIndex,$len);
$chart_data = [];
for ($i = 0;$i < count($out) ;$i++)
{
    $arr = array_slice($a,$out[$i],$len);
    $chart_data[] = $arr;
}
$img_width=850;
$img_height=650; 
$margins=20;
# ---- Find the size of graph by substracting the size of borders
$graph_width=$img_width - $margins * 2;
$graph_height=$img_height - $margins * 2; 
$img=imagecreate($img_width,$img_height);

$bar_width=20;
$total_bars=count($pattern_data);
$gap= ($graph_width- $total_bars * $bar_width ) / ($total_bars +1);


# -------  Define Colors ----------------
$bar_color=imagecolorallocate($img,0,64,128);
$background_color=imagecolorallocate($img,240,240,255);
$border_color=imagecolorallocate($img,200,200,200);
$line_color=imagecolorallocate($img,220,220,220);
//echo "------------- W[" . $bar_color ."] Min[". $background_color ."]GW[".$border_color."]GH[".$line_color."]--------------\n";
# ------ Create the border around the graph ------

imagefilledrectangle($img,1,1,$img_width-2,$img_height-2,$border_color);
imagefilledrectangle($img,$margins,$margins,$img_width-1-$margins,$img_height-1-$margins,$background_color);

# ------- Max value is required to adjust the scale -------
$max_value=max($pattern_data);
$min_value=min($pattern_data);
$ratio= (float)($graph_height/($max_value - $min_value));
# -------- Create scale and draw horizontal lines  --------
$horizontal_lines=20;
$horizontal_gap=$graph_height/$horizontal_lines;
$vertical_gap=$graph_width/$horizontal_lines;
for($i=1;$i<=$horizontal_lines;$i++){
    $y=$img_height - $margins - $horizontal_gap * $i ;
    $x = $img_width - $margins - $vertical_gap * $i;
    imageline($img,$margins,$y,$img_width-$margins,$y,$line_color);
  //imageline($img,int $x1,int $y1,int $x2,int $y2,int $color)
    imageline($img,$x,$margins,$x,$img_height-$margins,$line_color);
   // $v=intval((float)($horizontal_gap * $i /$ratio));
   $v=floatval(((float)($horizontal_gap * $i /$ratio)+$min_value));
    imagestring($img,0,5,$y-5,$v,$bar_color);

}
/*
# ----------- Draw the bars here ------
//echo "----- data[".count($pattern_data)."]------------------\n";
$hl = $graph_height / $total_bars;
$wl = $graph_width / $total_bars;
for($i=0;$i< $total_bars - 1; $i++){ 
    # ------ Extract key and value pair from the current pointer position
   
    imageline($img,intval(($wl * $key)),intval(($wl+((($max_value - $value)*$ratio))))
                ,intval(($wl * ($key+1))),intval(($wl+((($max_value - $value1)*$ratio)))),$bar_color);
   
}
*/
header("Content-type:image/png");
//header("Content-Type:application/json");
//ob_start();
imagepng($img);
/*$bin = ob_get_clean();
$b64 = base64_encode($bin);
$results = array(
    'price' => "NA",
    'image' => base64_encode($b64)
  );
  
$json = json_encode($results);
echo $json;
*/
$_REQUEST['asdfad']=234234;
?>