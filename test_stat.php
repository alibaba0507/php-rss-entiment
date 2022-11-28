<?php
require_once("Statistics.php");
$y = array(1.2714,
1.2716,
1.2717,
1.2715,
1.2714,
1.2715,
1.2717,
1.2719,
1.272,
1.2717,
1.2706,
1.2701,
1.2685,
1.2678,
1.2667,
1.266,
1.263,
1.2634,
1.2639,
1.2638,
1.264,
1.2644); // original data
$forecast_number = 4; // number of future data in $y you want to predict
$forecasts = array(); // output array, size will be length of $y + $forecast_number
$seasons = 5;
$stat = new Statistics();
//$stat->moving_average($y,$seasons,$forecasts);
$stat->time_series_forecast_multiplicative_model($y, $seasons, $forecast_number, $forecasts);
		
echo json_encode($forecasts);
?>
