<?php

    include '../config/config.php';
    date_default_timezone_set('Asia/Kolkata');
    header("content-Type: application/json");
    session_start();
    $assign_by = $_SESSION['userId'];
    $currentDateTime = date('Y-m-d H:i:s');

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addAssign')) {

        if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['role'] != '') && ($_POST['user_id'] != '')){

            $conn->beginTransaction();
            try {
                $project = $_POST['project_id'];
                $task = $_POST['task_id'];
                foreach ($task as $key => $value) {
                    $check = $conn->prepare('INSERT INTO `assign` ( `user_id`, `project_id`, `task_id`, `role`, `status` , `assigned_by`) VALUES (? , ? , ? , ? , ? , ?)');
                    $result = $check->execute([$_POST['user_id'], $project[$key] , $value ,$_POST['role'], "assign" , $assign_by]);
                    if($result){
                        
                        if($_POST['role'] == 'qa'){
                            $status = 'assign_qa';
                        }else if($_POST['role'] == 'qc'){
                            $status = 'assign_qc';
                        }else if($_POST['role'] == 'pro'){
                            $status = 'assign_pro';
                        }else if($_POST['role'] == 'vector'){
                            $status = 'assign_vector';
                        }

                        if($_POST['role'] == 'vector'){
                            $sql2 = $conn->prepare('UPDATE `tasks` SET `vector_status` = ? , `updated_at` = ? WHERE `task_id` = ?');
                            $result2 = $sql2->execute([$status ,$currentDateTime , $value]);
                        }else{
                            $sql2 = $conn->prepare('UPDATE `tasks` SET `status` = ? , `updated_at` = ? WHERE `task_id` = ?');
                            $result2 = $sql2->execute([$status ,$currentDateTime , $value]);
                        }
        

                    }else{
                        $conn->rollback();
                        http_response_code(500);
                        echo json_encode(array("message" => 'Something went wrong', "status" => 500));
                    }
                }

                if($result2){
                    $conn->commit();
                    http_response_code(200);
                    echo json_encode(array("message" => 'Assign Task successfull.', "status" => $status));
                }else{
                    $conn->rollback();
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went wrong.', "status" => 500));
                }


            } catch (PDOException $e) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(array("message" => "Something went wrong", "status" => 500));
            }


        }else{
            http_response_code(400);
            echo json_encode(array("message" => "Fill all required fields", "status" => 400));
        }

    }

?>