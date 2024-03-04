<?php

    session_start();
    include '../config/config.php';
    header("content-Type: application/json");
    $user_id = $_SESSION['userId'];

    
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'AssignProject')) {

        if(($_POST['project_id'] != '') && ($_POST['user_id'] != '')){

            $userSql = $conn->prepare('SELECT * FROM `assignproject` WHERE `project_id` = ? AND access = 1 AND `user_id` = ?');
            $userSql->execute([$_POST['project_id'],$_POST['user_id']]);
            $userSql = $userSql->fetch(PDO::FETCH_ASSOC);
            if(!$userSql){

                $sql = $conn->prepare('INSERT INTO `assignproject`(`user_id`, `assign_by`, `project_id`) VALUES (? , ? , ?)');
                $result = $sql->execute([$_POST['user_id'], $user_id , $_POST['project_id'] ]);
                if($result){
                    http_response_code(200);
                    echo json_encode(array("message" => 'Successfull Assign Project', "status" => 200));
                }else{
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something Went Wrong', "status" => 400));
                }        
            }else{
                http_response_code(400);
                echo json_encode(array("message" => 'All Ready Assigned', "status" => 400));
            }
        }else{
            http_response_code(404);
            echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
        }
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'reAssignProject')) {

        if(($_POST['project_id'] != '') && ($_POST['user_id'] != '')){

            $userSql = $conn->prepare('SELECT * FROM `assignproject` WHERE `project_id` = ? AND access = 1');
            $userSql->execute([$_POST['project_id']]);
            $userSql = $userSql->fetch(PDO::FETCH_ASSOC);
            if($userSql){

                // $updateSql = $conn->prepare('UPDATE `assignproject` SET  access = 0 WHERE `id` = ?');
                // $updateSql->execute([$userSql['id']]);

                $sql = $conn->prepare('INSERT INTO `assignproject`(`user_id`, `assign_by`, `project_id`) VALUES (? , ? , ?)');
                $result = $sql->execute([$_POST['user_id'], $user_id , $_POST['project_id'] ]);
                if($result){
                    http_response_code(200);
                    echo json_encode(array("message" => 'Successfull Assign Project', "status" => 200));
                }else{
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something Went Wrong', "status" => 400));
                }        
            }else{
                http_response_code(400);
                echo json_encode(array("message" => 'All Ready Assigned', "status" => 400));
            }
        }else{
            http_response_code(404);
            echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
        }
    }
