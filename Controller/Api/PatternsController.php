<?php
include(PROJECT_ROOT_PATH."Utils/CsvUtils.php");
include(PROJECT_ROOT_PATH."Utils/stock_chart_pattern.class.php");
require_once(PROJECT_ROOT_PATH."Statistics.php");
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
        $this->startIndex = (!isset($arrQueryStringParams['strt_indx']))?"1":trim($arrQueryStringParams["strt_indx"],"\"'");
        $this->len = (!isset($arrQueryStringParams['l']))?"5":trim($arrQueryStringParams["l"],"\"'");
        $reverse_read = (isset($arrQueryStringParams['reverse_read'])? true:false);
        $this->gridRows = (!isset($arrQueryStringParams['min_efficiency']))?"5":trim($arrQueryStringParams["min_efficiency"],"\"'");
        $header = (isset($arrQueryStringParams['header'])? true : false);
        $this->$scv= new CsvUtils();
        $url = trim($arrQueryStringParams["s"],"\"'");
        $this->data = $this->$scv->google_sheet_read_csv($url,-1,$header,$reverse_read);
        if ( $this->data &&  $this->data["err"])
        {
           $strErrorDesc =  $this->data;
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
        $this->columnData = array_column($this->data,(int)$col_no);
        $this->$charts = new StockChartPatterns($this->columnData);
        /*$grid = $this->$charts->createGrid($this->startIndex,$this->len,$this->gridRows);
        //print_r($grid);
        $reduce_grid = $this->$charts->applyFilters($grid,$this->gridRows,1);
        $rows = (int)sqrt(count($reduce_grid));
        //echo "=-========= rows[".$rows."]------------------\n";
        print_r($reduce_grid);
        $this->$pool_grid = $this->$charts->applyPooling($reduce_grid,$rows,$rows,2,2);
       /* $this->sendOutput(
            json_encode ($this->$pool_grid),
            array('Content-Type: application/json', 'HTTP/1.1 200 OK')
        );*/
        $this->$pool_grid = $this->genModel($this->startIndex);
        print_r($this->$pool_grid);
    }
    protected function genModel($startIndx)
    {
        $grid = $this->$charts->createGrid($startIndx,$this->len,$this->gridRows);
        $reduce_grid = $this->$charts->applyFilters($grid,$this->gridRows,1);
        $rows = (int)sqrt(count($reduce_grid));
        //$this->$pool_grid = 
        return $this->$charts->applyPooling($reduce_grid,$rows,$rows,2,2);
    }
}
?>