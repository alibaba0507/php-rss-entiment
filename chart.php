<?php
function drawChart($data,$img_width = 850,$img_height = 650,$margins = 20,$printImg = false){
  if (!$data || count($data) <= 0)
   return json_encode(["error" => "no data"]);
  $max_value = 0.0;
  $min_value = 0.0;
  $total_bars = 0.0;
  $hasArray = false;
  for ($i = 0;$i < count($data);$i++)
  {
    if (is_array($data[$i])){
      $hasArray = true;
      $tmp_max = max($data[$i]);
      $tmp_min = min($data[$i]);
      if ($total_bars == 0 || $total_bars < count($data[$i]))
        $total_bars = count($data[$i]);
    }else
    {
      $tmp_max = ($data[$i]);
      $tmp_min = ($data[$i]);
      if ($total_bars == 0 || $total_bars < ($i+1))
        $total_bars = ($i+1);
    }

    if ($max_value == 0 || $max_value < $tmp_max)
        $max_value = $tmp_max;
    if ($min_value == 0 || $min_value > $tmp_min)
        $min_value = $tmp_min;
  }
   
  
    # ---- Find the size of graph by substracting the size of borders
    $graph_width=$img_width - $margins * 2;
    $graph_height=$img_height - $margins * 2; 
    $img=imagecreate($img_width,$img_height);
    
   // $total_bars=count($pattern_data);

    # -------  Define Colors ----------------
    $bar_color=imagecolorallocate($img,0,64,128);
    $pattern_color=imagecolorallocate($img,240,64,0);
    $background_color=imagecolorallocate($img,240,240,255);
    $border_color=imagecolorallocate($img,200,200,200);
    $line_color=imagecolorallocate($img,220,220,220);
    
    # ------ Create the border around the graph ------

    imagefilledrectangle($img,1,1,$img_width-2,$img_height-2,$border_color);
    imagefilledrectangle($img,$margins,$margins,$img_width-1-$margins,$img_height-1-$margins,$background_color);
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
    

    $hl = $graph_height / $total_bars;
    $wl = $graph_width / $total_bars;
    $d = [];
    $d1 = [];
    $print_color = [];
    if ($hasArray)
    {
      for ($i = 0;$i < count($data);$i++)
      {
        $tmp = $data[$i];
        for ($j = 0;$j < count($tmp) - 1;$j++)
        {
            $value = $tmp[$j];
            $value1 = $tmp[$j + 1];
            $key = $j+1;
            $d[] = [intval(($wl * $key)),intval(($wl+((($max_value - $value)*$ratio))))];
            $d1[] = [intval(($wl * ($key+1))),intval(($wl+((($max_value - $value1)*$ratio))))];
            $print_color[] = ($i < count($data)-1) ? $bar_color : $pattern_color;
       }
       //print_r($d);
     }
    }else
    {
        for ($i = 0;$i < count($data)-1;$i++)
        {
            $value = $data[$i];
            $value1 = $data[$i + 1];
            $key = $i+1;
            $d[] = [intval(($wl * $key)),intval(($wl+((($max_value - $value)*$ratio))))];
            $d1[] = [intval(($wl * ($key+1))),intval(($wl+((($max_value - $value1)*$ratio))))];
            $print_color[] = $pattern_color;
        } 
    }
    for($i=0;$i< count($d); $i++){ 
        # ------ Extract key and value pair from the current pointer position
        //list($key,$value)=each($pattern_data); 
    imageline($img,$d[$i][0],$d[$i][1],$d1[$i][0],$d1[$i][1],/*$bar_color*/$print_color[$i]);
      
    }
    
    if ($printImg)
    {
        header("Content-type:image/png");
        imagepng($img);
        $_REQUEST['asdfad']=234234;
        return;

    }else
    {
        ob_start();
        imagepng( $img );
        $imageData = ob_get_contents();
        ob_clean(); 
        return (base64_encode($imageData));
    }
    
}
?>
