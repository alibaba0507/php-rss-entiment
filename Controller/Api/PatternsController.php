<?php
include(PROJECT_ROOT_PATH."Utils/CsvUtils.class.php");
include(PROJECT_ROOT_PATH."Utils/stock_chart_pattern.class.php");
include(PROJECT_ROOT_PATH."Utils/Chartdata.class.php");
include(PROJECT_ROOT_PATH."Utils/Statistics.php");
use Patterns\StockChartPatterns;
use Patterns\Chart;
use Utils\CsvUtils;
use Patterns\Statistics;
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
        $accuracy = (!isset($_GET['min_accuracy']))?"0.5":trim($_GET["min_accuracy"],"\"'");
        $header = (isset($arrQueryStringParams['header'])? true : false);
        $this->$scv= new CsvUtils();
        $this->chart = new Chart();
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
        $ma = (!isset($_GET['ma']))?21:trim($_GET["ma"],"\"'");
        $this->columnData = array_column($this->data,(int)$col_no);
        $ma_arr = [];
        $stat = new Statistics();
        $stat->moving_average($this->columnData,$ma,$ma_arr);

        $this->$charts = new StockChartPatterns($ma_arr);
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
        //$this->$pool_grid = 
        //$m = $this->genModel($this->startIndex);
        //print_r($m);
        $grid = $this->$charts->createModelGrid($startIndx,$this->len,$this->gridRows);
        //print_r($grid);
        $foundAt = $this->$charts->checkGridPatterns($startIndx,$this->len,$this->gridRows,$accuracy);
        print_r($foundAt);
        if (count($foundAt)> 0)
        {
            $arr = array_slice($this->columnData,$this->startIndex,$this->len);
            $ret = $this->chart->drawChart($arr,350,450,20,$this->gridRows);
            $src = 'data:image/png;base64,'.$ret;
            echo '<img src="'.$src.'">'; 
            $cnt = (count($foundAt) < 3)?count($foundAt):3;
            for ($i = 0;$i < $cnt;$i++)
            {
                $arr = array_slice($this->columnData,$foundAt[$i],$this->len);
                $ret = $this->chart->drawChart($arr,350,450,20,$this->gridRows);
                $src = 'data:image/png;base64,'.$ret;
                echo '<img src="'.$src.'">'; 
            }
        }
       /* $s = $this->startIndex + $this->len;
        $end = count($this->columnData)-$this->len;
        $cnt_arr = count($m);
        $patterns_match = [];
        for ($i = $s;$i < $end;$i++)
        {
            $tmp = $this->genModel($i);
            $diff = array_intersect($m, $tmp);
            if ($accuracy >= (count($diff)/$cnt_arr))
            {    
                $patterns_match[] = $i;
                $i += ($this->len);
            }
            //print_r($diff);
            //print_r($tmp);
        }
        echo "----------------------- Done ----------------\n";
        //print_r($patterns_match);
        $arr = array_slice($this->columnData,$this->startIndex,$this->len);
        $ret = $this->chart->drawChart($arr,350,450,20,$this->gridRows);
        $src = 'data:image/png;base64,'.$ret;
       echo '<img src="'.$src.'">'; 
       
       for ($i = 3;$i < 13;$i++)
       {
        $arr = array_slice($this->columnData,$patterns_match[$i],$this->len);
        $ret = $this->chart->drawChart($arr,350,450,20,$this->gridRows);
        $src = 'data:image/png;base64,'.$ret;
        echo '<img src="'.$src.'">'; 
       }
       */
       // $this->chart->drawChart($arr,$img_width = 850,$img_height = 650,$margins = 20,$horizontal_lines = 20,$printImg = false);
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