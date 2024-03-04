<?php

    include "../config/config.php";
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    session_start();
    $user_id = $_SESSION['userId'];
    $role_id = $_SESSION['roleId'];

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'sendNotification')) {
        if($_POST['message'] != '' && $_POST['send_type'] != ''){
            if($_POST['send_type'] == 'user'){
                $id = $_POST['user_id'];
            }else{
                $id = $_POST['role_id'];
            }
            $sql = $conn->prepare('INSERT INTO `notification`(`user_id` ,`message`, `type`, `type_id`) VALUES (? , ? , ? , ?)');
            $result = $sql->execute([$user_id , $_POST['message'] , $_POST['send_type'] , $id]);
            if($result){
                http_response_code(200);
                echo json_encode(["message" => "Send Notification Successfull." , "status" => 200]);
            }else{
                http_response_code(500);
                echo json_encode(["message" => "somenthing went wrong." , "status" => 500]);
            }
        }else{
            http_response_code(400);
            echo json_encode(["message" => "Fill All Required Fields." , "status" => 400]);
        }
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'deleteNotification')) {
        if($_POST['id'] != ''){
            $sql = $conn->prepare('DELETE from `notification` WHERE id = ?');
            $result = $sql->execute([$_POST['id']]);
            if($result){
                http_response_code(200);
                echo json_encode(["message" => "Delete Notification Successfull." , "status" => 200]);
            }else{
                http_response_code(500);
                echo json_encode(["message" => "somenthing went wrong." , "status" => 500]);
            }
        }
    }

    if(($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'seenNotification')){
        $notifications = $conn->prepare("SELECT * FROM `notification` WHERE `type` = 'user' AND `type_id` = ?");
        $notifications->execute([$user_id]);
        $notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
        foreach ($notifications as $notification) {
            $view = json_decode($notification['view'],true);
            if ($view && is_array($view) && in_array($user_id, $view)) {
                continue;
            } else {
                $view[] = $user_id;
                $update = $conn->prepare("UPDATE `notification` SET `view`= ? WHERE `id` =  ?");
                $update->execute([json_encode($view) , $notification['id']]);
            }
        }
        
        $notifications = $conn->prepare("SELECT * FROM `notification` WHERE `type` = 'role' AND `type_id` = ?");
        $notifications->execute([$role_id]);
        $notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
        foreach ($notifications as $notification) {
            $view = json_decode($notification['view'],true);
            if ($view && is_array($view) && in_array($user_id, $view)) {
                continue;
            } else {
                $view[] = $user_id;
                $update = $conn->prepare("UPDATE `notification` SET `view`= ? WHERE `id` =  ?");
                $update->execute([json_encode($view),$notification['id']]);
            }
        }
    }

?>