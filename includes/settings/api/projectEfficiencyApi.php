<?php

    include "../config/config.php";
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    session_start();
    $user_id = $_SESSION['userId'];

    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getFullProjectEfficiency')){
    }

?>