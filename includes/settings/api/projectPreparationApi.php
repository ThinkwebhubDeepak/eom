<?php

include '../config/config.php';
date_default_timezone_set('Asia/Kolkata');
header("content-Type: application/json");
session_start();
$user_id = $_SESSION['userId'];
$currentDateTime = date('Y-m-d H:i:s');

function getBreakTime($conn, $user_id , $project_id , $type){
    $sql = $conn->prepare("SELECT `time` FROM `break` WHERE `project_id` = ? AND `user_id` = ? AND `task_id` = ? ORDER BY `id` DESC");
    $sql->execute([$project_id , $user_id, $type]);
    $sql = $sql->fetch(PDO::FETCH_ASSOC);

    $update = $conn->prepare("UPDATE `break` SET `logged` = '1' WHERE `break`.`id` = ?");
    $update->execute([$sql['id']]);
    return intval($sql['time']);
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'projectPreparation')) {
    if ($_POST['project_id'] != '') {
        $sql = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'preparation' AND `status` = 'start'");
        $sql->execute([$user_id, $_POST['project_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if (!$sql) {
            $insert = $conn->prepare("INSERT INTO `projectefficiency`(`project_id`, `user_id`, `status`, `type`) VALUES ( ? , ? , ? , ? )");
            $result = $insert->execute([$_POST['project_id'], $user_id, 'start', 'preparation']);
            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => 'Success Project Start in Preparation', "status" => 200]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => 'Something went wrong ', "status" => 500]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => 'Already Project in Preparation.', "status" => 400]);
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'projectFinalization')) {
    if ($_POST['project_id'] != '') {
        $sql = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'finalization' AND `status` = 'start'");
        $sql->execute([$user_id, $_POST['project_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if (!$sql) {
            $insert = $conn->prepare("INSERT INTO `projectefficiency`(`project_id`, `user_id`, `status`, `type`) VALUES ( ? , ? , ? , ? )");
            $result = $insert->execute([$_POST['project_id'], $user_id, 'start', 'finalization']);
            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => 'Success Project Start in Finalization', "status" => 200]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => 'Something went wrong ', "status" => 500]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => 'Already Project in Finalization.', "status" => 400]);
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'projectFeedback')) {
    if ($_POST['project_id'] != '') {
        $sql = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'feedback' AND `status` = 'start'");
        $sql->execute([$user_id, $_POST['project_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if (!$sql) {
            $insert = $conn->prepare("INSERT INTO `projectefficiency`(`project_id`, `user_id`, `status`, `type`) VALUES ( ? , ? , ? , ? )");
            $result = $insert->execute([$_POST['project_id'], $user_id, 'start', 'feedback']);
            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => 'Success Project Start in Feedback', "status" => 200]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => 'Something went wrong ', "status" => 500]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => 'Already Project in Feedback.', "status" => 400]);
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'projectTraining')) {
    if ($_POST['project_id'] != '') {
        $sql = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'training' AND `status` = 'start'");
        $sql->execute([$user_id, $_POST['project_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if (!$sql) {
            $insert = $conn->prepare("INSERT INTO `projectefficiency`(`project_id`, `user_id`, `status`, `type` , `task`) VALUES ( ? , ? , ? , ? , ?)");
            $result = $insert->execute([$_POST['project_id'], $user_id, 'start', 'training',$_POST['task']]);
            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => 'Success Project Start in Training', "status" => 200]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => 'Something went wrong ', "status" => 500]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => 'Already Project in Training.', "status" => 400]);
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completePreparation')) {
    if ($_POST['project_id'] != '') {
        $sql = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'preparation' AND `status` = 'start'");
        $sql->execute([$user_id, $_POST['project_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if ($sql) {
            $date1 = strtotime($sql['created_at']);
            $date2 = strtotime($currentDateTime);
            $timeDiffInSeconds = abs($date2 - $date1);
            $timeDiffInMinutes = ($timeDiffInSeconds / 60) - getBreakTime($conn , $user_id, $_POST['project_id'] , 'preparation' );

            $insert = $conn->prepare("UPDATE `projectefficiency` SET `status` = 'complete' ,`taken_time` = ? , `updated_at` = ? ,`created_at` = ? WHERE `id` = ?");
            $result = $insert->execute([$timeDiffInMinutes , $currentDateTime, $sql['created_at'], $sql['id']]);
            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => 'Success Project Complete Preparation', "status" => 200]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => 'Something went wrong ', "status" => 500]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => 'Project is not in Preparation.', "status" => 400]);
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeFinalization')) {
    if ($_POST['project_id'] != '') {
        $sql = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'finalization' AND `status` = 'start'");
        $sql->execute([$user_id, $_POST['project_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if ($sql) {

            $date1 = strtotime($sql['created_at']);
            $date2 = strtotime($currentDateTime);
            $timeDiffInSeconds = abs($date2 - $date1);
            $timeDiffInMinutes = ($timeDiffInSeconds / 60) - getBreakTime($conn , $user_id, $_POST['project_id'] , 'finalization' );

            $insert = $conn->prepare("UPDATE `projectefficiency` SET `status` = 'complete' ,`taken_time` = ? , `updated_at` = ? ,`created_at` = ? WHERE `id` = ?");
            $result = $insert->execute([$timeDiffInMinutes , $currentDateTime, $sql['created_at'], $sql['id']]);
            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => 'Success Project Complete Finalization', "status" => 200]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => 'Something went wrong ', "status" => 500]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => 'Project is not in Finalization.', "status" => 400]);
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeFeedback')) {
    if ($_POST['project_id'] != '') {
        $sql = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'feedback' AND `status` = 'start'");
        $sql->execute([$user_id, $_POST['project_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if ($sql) {
            
            $date1 = strtotime($sql['created_at']);
            $date2 = strtotime($currentDateTime);
            $timeDiffInSeconds = abs($date2 - $date1);
            $timeDiffInMinutes = ($timeDiffInSeconds / 60) - getBreakTime($conn , $user_id, $_POST['project_id'] , 'feedback' );

            $insert = $conn->prepare("UPDATE `projectefficiency` SET `status` = 'complete' ,`taken_time` = ? , `updated_at` = ? ,`created_at` = ? WHERE `id` = ?");
            $result = $insert->execute([$timeDiffInMinutes , $currentDateTime, $sql['created_at'], $sql['id']]);
            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => 'Success Project Complete Feedback', "status" => 200]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => 'Something went wrong ', "status" => 500]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => 'Project is not in Feedback.', "status" => 400]);
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeTraining')) {
    if ($_POST['project_id'] != '') {
        $sql = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'training' AND `status` = 'start'");
        $sql->execute([$user_id, $_POST['project_id']]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if ($sql) {
            
            $date1 = strtotime($sql['created_at']);
            $date2 = strtotime($currentDateTime);
            $timeDiffInSeconds = abs($date2 - $date1);
            $timeDiffInMinutes = ($timeDiffInSeconds / 60) - getBreakTime($conn , $user_id, $_POST['project_id'] , 'training' ) ;

            $insert = $conn->prepare("UPDATE `projectefficiency` SET `status` = 'complete' ,`taken_time` = ? , `updated_at` = ? ,`created_at` = ? WHERE `id` = ?");
            $result = $insert->execute([$timeDiffInMinutes , $currentDateTime, $sql['created_at'], $sql['id']]);
            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => 'Success Project Complete Training', "status" => 200]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => 'Something went wrong ', "status" => 500]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => 'Project is not in Training.', "status" => 400]);
        }
    }
}
