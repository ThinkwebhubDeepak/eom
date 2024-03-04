<?php

    include '../config/config.php';
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    $getLeave = $conn->prepare("SELECT * FROM `leaves` WHERE CURDATE() BETWEEN `form_date` AND `end_date` AND `status` = 'cancel'");
    $getLeave->execute();
    $getLeave =$getLeave->fetchAll(PDO::FETCH_ASSOC);
    foreach ($getLeave as $value) {
        $attandences = $conn->prepare("SELECT * FROM `attendance` WHERE `user_id` = ? ORDER BY `id` DESC LIMIT 2");
        $attandences->execute([$value['user_id']]);
        $attandences = $attandences->fetchAll(PDO::FETCH_ASSOC);
        foreach ($attandences as $attandence) {
            $updateAttandences = $conn->prepare("UPDATE `attendance` SET `punishment` = 1 WHERE `id` = ?");
            $updateAttandences->execute([$attandence['id']]);
        }
    }

?>