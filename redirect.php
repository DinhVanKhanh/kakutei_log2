<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/lib/common_files.php";
date_default_timezone_set("Asia/Tokyo");
if (!isset($_POST["controller"])){
    $controller = 'Main';
} else {
    $controller = $_POST["controller"];
}
// require_once __DIR__ . "/controller/C_" . $_POST["controller"] . ".php";
require_once __DIR__ . "/controller/C_" . $controller . ".php";


?>
