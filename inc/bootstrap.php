<?php
error_reporting(E_ALL ^ E_NOTICE);  

define("PROJECT_ROOT_PATH", __DIR__ . "/../");
 
// include main configuration file
//require_once PROJECT_ROOT_PATH . "/inc/config.php";
require_once PROJECT_ROOT_PATH ."/vendor/autoload.php"; 
// include the base controller file
require_once PROJECT_ROOT_PATH . "/Controller/Api/BaseController.php";
 
// include the use model file
//require_once PROJECT_ROOT_PATH . "/Model/UserModel.php";
?>