<?php
require __DIR__ . "/inc/bootstrap.php";
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
//print_r($uri);
$indx = array_search("index.php", $uri)-1;
//echo '----- indx['.$indx.']-------\n';
if ((!isset($uri[$indx+2]) || !isset($uri[$indx + 3])))
{
    header("HTTP/1.1 404 Not Found");
    exit(); 
}else if ($uri[$indx+2] == 'rss')
{
    if ($uri[$indx + 3] != 'sentiment')
    {
        header("HTTP/1.1 404 Not Found");
        exit();  
    }
    require PROJECT_ROOT_PATH . "/Controller/Api/RssController.php";
    $objFeedController = new RssController();
    $strMethodName = $uri[$indx+3] . 'Action';
    $objFeedController->{$strMethodName}();
}else if ($uri[$indx+2] == 'patterns')
{
    if ($uri[$indx + 3] != 'find')
    {
        header("HTTP/1.1 404 Not Found");
        exit();  
    }
    require PROJECT_ROOT_PATH . "/Controller/Api/PatternsController.php";
    $objFeedController = new PatternsController();
    $strMethodName = $uri[$indx+3] . 'Action';
    $objFeedController->{$strMethodName}();
}
else
if ((isset($uri[$indx+2]) && $uri[$indx+2] != 'user') || !isset($uri[$indx+3])) {
    header("HTTP/1.1 404 Not Found");
    exit();
}else{
    require PROJECT_ROOT_PATH . "/Controller/Api/UserController.php";

    $objFeedController = new UserController();
    $strMethodName = $uri[3] . 'Action';
    $objFeedController->{$strMethodName}();
}
?>
