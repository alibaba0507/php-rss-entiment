<?php

class linear_regression {
    public function __construct( array $dataset ){
        $this->dataset = $dataset;

        //Start the execution timer
        $start = microtime( true );

        //Make the calculations
        $this->calculate();
    
        //Calculate the number of seconds elapsed since the start of calculation execution
        $this->elapsed = microtime( true ) - $start;
    }

    protected function calculate(){
        //Count the number of points in the dataset
        $this->number_of_datapoints = count( $this->dataset );

        //Start a total for the X-values
        $this->x_values_sum = 0;

        //Start a total for the Y-values
        $this->y_values_sum = 0;

        //Calculate the X and Y value sums
        $cnt = 0;
        foreach( $this->dataset as $index => $point ){
            $this->x_values_sum += is_array($point)? $point[ 0 ]:+$cnt;
            $this->y_values_sum += is_array($point) ? $point[ 1 ]:$point;

        }

        //Calculate the mean average X value (x bar)
        $this->x_mean = $this->x_values_sum / $this->number_of_datapoints;
        
        //Calculate the mean average Y value (y bar)
        $this->y_mean = $this->y_values_sum / $this->number_of_datapoints;

        //Start the total for the "first moment" deviation score
        $xy_first_moment_sum = 0;

        //Start the total sum of squares for the x and y values
        $this->x_sum_of_squares = 0;
        $this->y_sum_of_squares = 0;
        $cnt = 0;
        foreach( $this->dataset as $index => $point ){
            //Calculate the X and Y value first moment deviation scores
            $x_first_moment = (is_array($point)?$point[ 0 ]:++$cnt) - $this->x_mean;
            $y_first_moment = (is_array($point)?$point[ 1 ]:$point) - $this->y_mean;

            //Increase the xy first moment score; sum (x first moment) * (y first moment)
            //This is the value on the top of the Rxy formula fraction
            $xy_first_moment_sum += $x_first_moment * $y_first_moment;

            //Increase the x and y sums of squares
            $this->x_sum_of_squares += pow( $x_first_moment, 2 );
            $this->y_sum_of_squares += pow( $y_first_moment, 2 );
        }

        //Find the square root of the sums of squares for further use
        $root_x_sum_of_squares = sqrt( $this->x_sum_of_squares );
        $root_y_sum_of_squares = sqrt( $this->y_sum_of_squares );

        //Calculate the r value (Rxy)
        $this->r = $root_x_sum_of_squares != 0 && $root_y_sum_of_squares != 0 ? $xy_first_moment_sum / ( $root_x_sum_of_squares * $root_y_sum_of_squares ) : 0;

        //Calculate the x and y variance from the appliccable sums of squares
        $this->x_variance = $this->x_sum_of_squares / ( $this->number_of_datapoints - 1 );
        $this->y_variance = $this->y_sum_of_squares / ( $this->number_of_datapoints - 1 );

        //Calculate the x and y standard errors
        $this->x_standard_error = $root_x_sum_of_squares / $this->number_of_datapoints;
        $this->y_standard_error = $root_y_sum_of_squares / $this->number_of_datapoints;

        //Calculate the standard deviations from the x and y variances
        $this->x_standard_deviation = sqrt( $this->x_variance );
        $this->y_standard_deviation = sqrt( $this->y_variance );

        //Calculate the regression gradient
        $this->m = $this->x_standard_deviation != 0 && $this->y_standard_deviation != 0 ? $this->r * ( $this->x_standard_deviation / $this->y_standard_deviation ) : 0;

        //Calculate the regression intercept
        $this->c = $this->y_mean - ( $this->m * $this->x_mean );
    }

    /**
     * Represent an equation for the linear regression line as a y=mx+c equation string.
     *
     * @return string|null
     */
    public function getEquationString(): ?string {
        return "y = {$this->m}x + {$this->c}";
    }

    /**
     * Retrieve the number of datapoints were used to calculate the regression data.
     *
     * @return int
     */
    public function getNumber(): int {
        return $this->number_of_datapoints;

    }

    /**
     * Retrieve the sum of the provided X values
     *
     * @return float
     */
    public function getXSum(): float {
        return $this->x_values_sum;

    }

    /**
     * Retrieve the sum of the provided Y values
     *
     * @return float
     */
    public function getYSum(): float {
        return $this->y_values_sum;

    }

    /**
     * Retrieve the X mean average
     *
     * @return float
     */
    public function getXMean(): float {
        return $this->x_mean;

    }

    /**
     * Retrieve the Y mean average
     *
     * @return float
     */
    public function getYMean(): float {
        return $this->y_mean;

    }

    /**
     * Retrieve the Sum of squares for the X values
     *
     * @return float
     */
    public function getXSumSquares(): float {
        return $this->x_sum_of_squares;

    }

    /**
     * Retrieve the Sum of squares for the Y values
     *
     * @return float
     */
    public function getYSumSquares(): float {
        return $this->y_sum_of_squares;

    }

    /**
     * Retrieve the R value (Rxy, pearson correlation coefficient)
     *
     * @return float
     */
    public function getR(): float {
        return $this->r;

    }

    /**
     * Retrieve the variance of the X values
     *
     * @return float
     */
    public function getXVariance(): float {
        return $this->x_variance;

    }

    /**
     * Retrieve the variance of the Y values
     *
     * @return float
     */
    public function getYVariance(): float {
        return $this->y_variance;

    }

    /**
     * Retrieve the standard error of the X values
     *
     * @return float
     */
    public function getXStandardError(): float {
        return $this->x_standard_error;

    }

    /**
     * Retrieve the standard error of the Y values
     *
     * @return float
     */
    public function getYStandardError(): float {
        return $this->y_standard_error;

    }

    /**
     * Retrieve the standard deviation of the X values
     *
     * @return float
     */
    public function getXStandardDeviation(): float {
        return $this->x_standard_deviation;

    }

    /**
     * Retrieve the standard deviation of the Y values
     *
     * @return float
     */
    public function getYStandardDeviation(): float {
        return $this->y_standard_deviation;

    }

    /**
     * Retrieve gradient for the linear regression line. This is the "m" value in y=mx+c
     *
     * @return float
     */
    public function getGradient(): float {
        return $this->m;

    }

    /**
     * Retrieve the y-intercept value for the linear regression line. This is the "c" value in y=mx+c
     *
     * @return float
     */
    public function getIntercept(): float {
        return $this->c;

    }

    /**
     * Retrieve and calculate the upper boundary for the confidence interval for the X values
     *
     * @param float $confidence_level
     * @return float
     */
    public function getXConfidenceIntervalUpper( float $confidence_level ): float {
        return $this->x_mean + ( $confidence_level * $this->x_standard_error );

    }

    /**
     * Retrieve and calculate the upper boundary for the confidence interval for the Y values
     *
     * @param float $confidence_level
     * @return float
     */
    public function getXConfidenceIntervalLower( float $confidence_level ): float {
        return $this->x_mean + ( $confidence_level * $this->x_standard_error );
        
    }

    /**
     * Retrieve and calculate the lower boundary for the confidence interval for the X values
     *
     * @param float $confidence_level
     * @return float
     */
    public function getYConfidenceIntervalUpper( float $confidence_level ): float {
        return $this->y_mean + ( $confidence_level * $this->y_standard_error );

    }

    /**
     * Retrieve and calculate the lower boundary for the confidence interval for the Y values
     *
     * @param float $confidence_level
     * @return float
     */
    public function getYConfidenceIntervalLower( float $confidence_level ): float {
        return $this->y_mean + ( $confidence_level * $this->y_standard_error );

    }

    /**
     * Alias for the class' getEquationString method
     *
     * @return string
     */
    public function __toString(): string {
        return $this->getEquationString();

    }
}

/*$dataset = [
    [1,67],
    [2,66],
    [3,58],
    [4,76],
    [5,79],
    [6,83]
];*/
/*
for( $i = 1; $i <= 5; $i++ ){
    $dataset[] = [
        $i,
        rand( 0, 100 )
    ];

    ;
    echo $i . ": " . end( $dataset )[ 1 ] . ", ";
}
echo "<br/>";

$lr = new linear_regression( $dataset );

?>

<?= $lr ?><br/><br/>
Elapsed: <?= $lr->elapsed ?>s<br/><hr/>

<?php var_dump( $lr )?>
*/
?>