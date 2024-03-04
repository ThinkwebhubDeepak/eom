<?php

    include "../config/config.php";
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    date_default_timezone_set('Asia/Kolkata');
    session_start();

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'AccessPage')) {
        if($_POST['role_id'] != '' && $_POST['page_id'] != ''){
            $access = $conn->prepare('SELECT * FROM `access` WHERE `role_id` = ?');
            $access->execute([$_POST['role_id']]);
            $result = $access->fetch(PDO::FETCH_ASSOC);
            if($result){
                $accessPage = json_decode($result['access_page']);
                array_push($accessPage, $_POST['page_id']);
                $update = $conn->prepare('UPDATE `access` SET `access_page` = ? WHERE `role_id` = ?');
                $result = $update->execute([ json_encode($accessPage) , $_POST['role_id']]);
                if($result){
                    http_response_code(200);
                    echo json_encode(['message'=> 'Access successfull']);
                    exit;
                }else{
                    http_response_code(500);
                    echo json_encode(['message'=> 'Something Went Wrong']);
                    exit;
                }
            }else{
                $accessPage = [$_POST['page_id']];
                $update = $conn->prepare('INSERT INTO `access` (`role_id`, `access_page`) VALUES (? , ?)');
                $result = $update->execute([ $_POST['role_id'] ,json_encode($accessPage)]);
                if($result){
                    http_response_code(200);
                    echo json_encode(['message'=> 'Access successfull']);
                    exit;
                }else{
                    http_response_code(500);
                    echo json_encode(['message'=> 'Something Went Wrong']);
                    exit;
                }
            }
        }else{
            http_response_code(400);
            echo json_encode(['message'=> 'Fill all required fields.']);
        }
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'RemoveAccessPage')) {
        if ($_POST['role_id'] != '' && $_POST['page_id'] != '') {
            $access = $conn->prepare('SELECT * FROM `access` WHERE `role_id` = ?');
            $access->execute([$_POST['role_id']]);
            $result = $access->fetch(PDO::FETCH_ASSOC);
    
            if ($result) {
                $accessPage = json_decode($result['access_page'], true); 
                $key = array_search($_POST['page_id'], $accessPage);
    
                if ($key !== false) {
                    unset($accessPage[$key]);
                    $accessPage = array_values($accessPage);
                }
    
                $update = $conn->prepare('UPDATE `access` SET `access_page` = ? WHERE `role_id` = ?');
                $result = $update->execute([json_encode($accessPage), $_POST['role_id']]);
    
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['message' => 'Access successfully removed']);
                    exit;
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Something Went Wrong']);
                    exit;
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Fill all required fields.']);
        }
    }

?>