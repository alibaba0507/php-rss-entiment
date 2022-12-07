<?php
include(PROJECT_ROOT_PATH."Utils/CsvUtils.php");
include(PROJECT_ROOT_PATH."Utils/stock_chart_pattern.class.php");
use Patterns\StockChartPatterns;
use Utils\CsvUtils;
class PatternsController extends BaseController
{
    public function findAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        if (!isset($arrQueryStringParams['s']) || trim($arrQueryStringParams['s']) == "") {
            $strErrorDesc = 'Invalid or missing scv file';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
        $col_no = (!isset($arrQueryStringParams['col_no']))?"0":trim($arrQueryStringParams["col_no"],"\"'");//$_GET['col_no'];
        $startIndex = (!isset($arrQueryStringParams['strt_indx']))?"1":trim($arrQueryStringParams["strt_indx"],"\"'");
        $len = (!isset($arrQueryStringParams['l']))?"5":trim($arrQueryStringParams["l"],"\"'");
        $reverse_read = (isset($arrQueryStringParams['reverse_read'])? true:false);
        $gridRows = (!isset($arrQueryStringParams['min_efficiency']))?"5":trim($arrQueryStringParams["min_efficiency"],"\"'");
        $header = (isset($arrQueryStringParams['header'])? true : false);
        $scv= new CsvUtils();
        $url = trim($arrQueryStringParams["s"],"\"'");
        $data = $scv->google_sheet_read_csv($url,-1,$header,$reverse_read);
        if ($data && $data["err"])
        {
           $strErrorDesc = $data;
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
        $a = array_column($data,(int)$col_no);
        $ptrns = new StockChartPatterns($a);
        $grid = $ptrns->createGrid($startIndex,$len,$gridRows);
        print_r($grid);
        $reduce_grid = $ptrns->applyFilters($grid,$gridRows,1);
        $rows = (int)sqrt(count($reduce_grid));
        //echo "=-========= rows[".$rows."]------------------\n";
        print_r($reduce_grid);
        $pool_grid = $ptrns->applyPooling($reduce_grid,$rows,$rows,2,2);
        print_r($pool_grid);
    }
}
?>