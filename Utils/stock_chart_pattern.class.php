<?php
namespace Patterns;

class StockChartPatterns {
    /*
     * @param $filter - minimum row * cols filter to scan the grid by
    */
    public function __construct( array $dataset,$filters = 3,$wdow_size = 2,$step = 2){
        $this->dataset = $dataset;
        $this->filters = $filters;
    }

    public function createGrid($startIndex,$len,$rows){
        $grid = array_fill(0,((int)$rows*(int)$rows),-1);
        if (!is_array($this->dataset)|| count($this->dataset)==0)
         return $grid;
        // slice array to find max and min values , that will be top and bottom rows
        $arr = array_slice($this->dataset,$startIndex,$len);
        $max = max($arr);
        $min = min($arr);
        $d_col = ($max-$min)/(int)$rows; // calc column unit
        $d_row = count($arr)/(int)$rows; // calc row unit
        
        for ($i = 0;$i < count($arr);$i++)
        { // fill the grid with 1 and 0
        $col = ($max - $arr[$i])/(float)$d_col;
        $col = ($col == 0.0)?0.1:$col;
        $r = ceil(($i+1)/(float)$d_row);
        $cell = (((ceil($col) - 1)*$rows)+$r);
        $column = $cell % $rows;
        $row = floor($cell / $rows);
        $grid[$cell] = 1;
        }// end for
        return $grid;
    }
    
    /**
     * Depricated: replaced by 
     * @subGridToArray(array $grid,$rows,$cols,$startIndex,$sub_rows,$sub_cols,$step = 1,$print = false)
     * as more generic
     */
    function calcFilter(array $grid,$startIndex,$rows)
    {
        $sum = 0.0;
        $cols = 0;
        for ($i = 0;$i < $this->filters**2;$i++)
        {
            $incr = $i + $cols;
            $incr -= (((($i+$startIndex)%$rows) == 0) && (($i % $this->filters) != 0))?1:0;
            $sum += $grid[$startIndex + $incr];
            $cols += ((($i % $this->filters) == 0) || ((($i+$startIndex)%$rows) == 0))?$rows-$this->filters:0;            
        }
        return ($sum / ($this->filters**2));
    }
    function arraySumEven(array $grid)
    {
      $counter = 1;
      $sum = 0.0;
      foreach ($grid as &$data) {
         if (($counter % 2))
           $sum += $data;
          else
            $sum -= $data;
          $counter++;
      }// end for
      return $sum;
    } 
    function applyFilters(array $grid,$rows,$step = 1)
    {
      $reduce_grid = [];
      //$tmp = [-1, 1];
      //$tmp_1 = [1, -1];
      //echo "------[".($this->arraySumEven($tmp))."] - [" .($this->arraySumEven($tmp_1))."]------\n";
      for ($i= 0;$i < count($grid);$i+= $step)
      {
        $r = ($rows - ceil(($i+1)/$rows));
        //$reduce_grid[] = $this->calcFilter($grid,$i,$rows);
        $grd = $this->subGridToArray($grid,$rows,$rows,$i,$this->filters,$this->filters,1/*,($i < 4)*/);
        $sum_even = $this->arraySumEven($grd);
        //$sum_norm = array_sum($grd);
       // echo "------------------ Sum Even[".$sum_even."] Norm[".$sum_norm."]-----------\n";
        //print_r($grd);
        $reduce_grid[] = /*array_sum*/$sum_even/(/*$this->filters**2*/count($grd));
        if (($rows - ($i % $rows)) < $this->filters)
        {    
          //  echo "----------------[".$i."]-------\n";
            $i += (($rows - ($i % $rows))*$step);
            if ($r < $this->filters)
             break;
        }
        
      }
      return $reduce_grid;
    }
    /*
     * Select subgrid inside the grid and
     * present the 1d array
     */
    function subGridToArray(array $grid,$rows,$cols,$startIndex,$sub_rows,$sub_cols,$step = 1,$print = false)
    {
       $sub = [];
       $max_r = $startIndex + ($rows*($sub_rows-1));
       for ($i = $startIndex;$i < $max_r || $sub_rows > 0;$i+=($rows*$step),$sub_rows-= $step)
       {
          $l = (($rows - ($i % $rows)) < $sub_cols)?($rows - ($i % $rows)):$sub_cols;
          $c =  array_slice($grid,$i,$l); 
          $sub = array_merge($sub,$c);
          if ($print)
            echo "------------ start at[".$i."]-[".($i+$l)."]---------\n";
          //print_r($sub);
          /*if ((($rows - ($i % $rows)) < $sub_cols))
          {
            $r = ($rows - ceil(($i+1)/$rows));
            if ($r < $sub_rows)
             break;
          }*/
       }
       if ($print)
        print_r($sub);
       return $sub;
    }
    function applyPooling(array $grid,$rows,$cols,$w_size = 2,$step = 1)
    {
        $reduce_grid = [];
        for ($i= 0;$i < count($grid);$i+=$step)
        {
          $r = ($rows - ceil(($i+1)/$rows));
          $p = $this->subGridToArray($grid,$rows,$rows,$i,$w_size,$w_size/*,1,($i < 1)*/);
          //if ($i < 1)
          //  print_r($p);
          $reduce_grid[] = max( $p);
          if (($rows - ($i % $rows)) < $this->filters)
          {    
              //echo "----------------[".$i."]-------\n";
              $i += (($rows - ($i % $rows))*$step);
              if ($r < $w_size)
               break;
          }
          
        }
        return $reduce_grid;
    }
}
?>