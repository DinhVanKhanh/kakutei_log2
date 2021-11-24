<?php
    require_once __DIR__ . ('/../../../../common_files/config/variable.php');
    global $WEBSERVER_FLG;
    global $DATABASE;
    $WEBSERVER_FLG    = $common_files['webserver'];
    $DATABASE         = $connect['manual']['mysql'];
    require_once $WEBSERVER_FLG;
    require_once $DATABASE;
?>