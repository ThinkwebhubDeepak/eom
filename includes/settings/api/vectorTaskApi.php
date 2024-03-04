<?php

    session_start();
    include '../config/config.php';
    date_default_timezone_set('Asia/Kolkata');
    header("content-Type: application/json");
    $user_id = $_SESSION['userId'];
    $currentDateTime = date("Y-m-d H:i:s");

    function useBreak($conn, $user_id , $task_id , $project_id){
        $updateBreak = $conn->prepare("UPDATE `break` SET `logged` = 1 WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? ");
        $updateBreak->execute(array($user_id,$task_id , $project_id));
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'startTaskVector')) {
        if ($_POST['task_id'] != '' && $_POST['project_id'] != '') {
            try {
                $conn->beginTransaction();
    
                $task_id = $_POST['task_id'];
                $project_id = $_POST['project_id'];
    
                foreach ($task_id as $key => $value) {
                    $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `vector_status` = ?");
                    $check->execute([$task_id[$key], $project_id[$key], 'assign_vector']);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
    
                    if ($check) {
                        $taskupdate = $conn->prepare("UPDATE `tasks` SET `vector_status`= ? WHERE `task_id` = ? AND `project_id` = ?");
                        $taskupdate->execute(['vector_in_progress', $task_id[$key], $project_id[$key]]);
    
                        $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks`) VALUES ( ? , ? , ? , ?, ? , ?)');
                        $sql->execute([$user_id, $task_id[$key], $project_id[$key], 'assign_vector', 'vector_in_progress', 'In Progress By Vector',]);
                    } else {
                        throw new Exception("Vector is already in progress");
                    }
                }
    
                $conn->commit();
                http_response_code(200);
                echo json_encode(["message" => "Vector in progress"]);
            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        }
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'startTaskQa')) {
        if ($_POST['task_id'] != '' && $_POST['project_id'] != '') {
            try {
                $conn->beginTransaction();
    
                $task_id = $_POST['task_id'];
                $project_id = $_POST['project_id'];
    
                foreach ($task_id as $key => $value) {
                    $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                    $check->execute([$task_id[$key], $project_id[$key], 'assign_qa']);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
    
                    if ($check) {
                        $taskupdate = $conn->prepare("UPDATE `tasks` SET `status`= ? WHERE `task_id` = ? AND `project_id` = ?");
                        $taskupdate->execute(['qa_in_progress', $task_id[$key], $project_id[$key]]);
    
                        $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks`) VALUES ( ? , ? , ? , ?, ? , ?)');
                        $sql->execute([$user_id, $task_id[$key], $project_id[$key], 'assign_qa', 'qa_in_progress', 'In Progress By Qa',]);
                    } else {
                        throw new Exception("QA is already in progress");
                    }
                }
    
                $conn->commit();
                http_response_code(200);
                echo json_encode(["message" => "QA in progress"]);
            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        }
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeTaskVector')) {
        if($_POST['task_id'] !='' && $_POST['project_id'] != ''){
            $task_id = $_POST['task_id'];
            $project_id = $_POST['project_id'];
            $number = count($task_id);
            try {
                $conn->beginTransaction();
                foreach ($task_id as $key => $value) {
    
                    $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `vector_status` = ?");
                    $check->execute([$task_id[$key], $project_id[$key],'vector_in_progress']);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
                    if($check){
                        
                        $task_estimated_hour = (($check['estimated_hour']) * (0.03) * 60);

                        $checkWork = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `next_status` = ? ORDER BY `id` DESC");
                        $checkWork->execute([$user_id, $task_id[$key], $project_id[$key],'vector_in_progress']);
                        $checkWork = $checkWork->fetch(PDO::FETCH_ASSOC);
                        if($checkWork){
                            $givenTimestamp = strtotime($checkWork['created_at']);
                            $currentTimestamp = time();
                            $timeDifferenceInSeconds = $currentTimestamp - $givenTimestamp;
                            $timeDifferenceInMinutes = round($timeDifferenceInSeconds / 60 , 2);

                            // total time divide by total files
                            $timeDifferenceInMinutes = $timeDifferenceInMinutes/$number;

                            // check break is available or not
                            $checkBreak = $conn->prepare("SELECT SUM(`time`) AS total_time FROM `break` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `logged` = 0");
                            $checkBreak->execute([$user_id , $task_id[$key] , $project_id[$key]]);
                            $checkBreak = $checkBreak->fetch(PDO::FETCH_ASSOC);
                            if($checkBreak['total_time']){
                                $workingMinutes = floatval($timeDifferenceInMinutes) - floatval($checkBreak['total_time']);
                            }else{
                                $workingMinutes = floatval($timeDifferenceInMinutes);
                            }

                            $prFileMinutes = ($workingMinutes);
                            
                            $effciency = ($task_estimated_hour/$prFileMinutes)*100;

                            // set task status is complete
                            $taskupdate = $conn->prepare("UPDATE `tasks` SET `vector_status` = 'complete' , `updated_at` = ? WHERE `task_id` = ? AND `project_id` = ?");
                            $taskupdate = $taskupdate->execute([ $currentDateTime ,$task_id[$key], $project_id[$key]]);

                            useBreak($conn, $user_id , $task_id[$key] , $project_id[$key]);

                            $workLog = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` , `work_percentage` , `taken_time`) VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)');
                            $workLog = $workLog->execute([$user_id , $task_id[$key], $project_id[$key],'vector_in_progress' , 'complete' , 'Complete By Vector' , 100 , $prFileMinutes]);

                            // add efficincy data                            
                            $effciencyAdd = $conn->prepare('INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency` , `total_time` , `taken_time`) VALUES (? , ? , ? , ? , ? , ? , ?)');
                            $effciencyAdd = $effciencyAdd->execute([$user_id , $task_id[$key], $project_id[$key], 'vector' , $effciency , $task_estimated_hour , $prFileMinutes]);

                             // set assign status is complete
                            $assignSql = $conn->prepare("UPDATE `assign` SET `status` = ? WHERE `task_id` = ? AND `project_id` = ? AND `role` = ? AND `user_id` = ?");
                            $assignSql = $assignSql->execute(['complete', $task_id[$key], $project_id[$key] , 'vector' , $user_id ]);


                        }else{
                            http_response_code(400);
                            echo json_encode(["message" => "Vector Work Log not found."]);
                            exit;
                        }
                    }else{
                        http_response_code(400);
                        echo json_encode(["message" => "Vector is not in progress"]);
                        exit;
                    }
                }
                $conn->commit();
                http_response_code(200);
                echo json_encode(["message" => "Vector in progress"]);

            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        }
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeTaskQa')) {
        if($_POST['task_id'] !='' && $_POST['project_id'] != ''){
            $task_id = $_POST['task_id'];
            $project_id = $_POST['project_id'];
            $number = count($task_id);
            try {
                $conn->beginTransaction();
                foreach ($task_id as $key => $value) {
    
                    $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                    $check->execute([$task_id[$key], $project_id[$key],'qa_in_progress']);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
                    if($check){
                        
                        if($check['vector_status'] != ''){
                            $task_estimated_hour = (($check['estimated_hour']) * (0.02) * 60);
                        }else{
                            $task_estimated_hour = (($check['estimated_hour']) * (0.05) * 60);
                        }

                        $checkWork = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `next_status` = ? ORDER BY `id` DESC");
                        $checkWork->execute([$user_id , $task_id[$key], $project_id[$key],'qa_in_progress']);
                        $checkWork = $checkWork->fetch(PDO::FETCH_ASSOC);
                        if($checkWork){
                            $givenTimestamp = strtotime($checkWork['created_at']);
                            $currentTimestamp = time();
                            $timeDifferenceInSeconds = $currentTimestamp - $givenTimestamp;
                            $timeDifferenceInMinutes = round($timeDifferenceInSeconds / 60 , 2);

                            // total time divide by total files
                            $timeDifferenceInMinutes = $timeDifferenceInMinutes/$number;

                            // check break is available or not
                            $checkBreak = $conn->prepare("SELECT SUM(`time`) AS total_time FROM `break` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `logged` = 0");
                            $checkBreak->execute([$user_id , $task_id[$key] , $project_id[$key]]);
                            $checkBreak = $checkBreak->fetch(PDO::FETCH_ASSOC);
                            if($checkBreak['total_time']){
                                $workingMinutes = floatval($timeDifferenceInMinutes) - floatval($checkBreak['total_time']);
                            }else{
                                $workingMinutes = floatval($timeDifferenceInMinutes);
                            }

                            $prFileMinutes = ($workingMinutes);
                            
                            $effciency = ($task_estimated_hour/$prFileMinutes)*100;

                            // set task status is complete
                            $taskupdate = $conn->prepare("UPDATE `tasks` SET `status` = 'complete' , `updated_at` = ? WHERE `task_id` = ? AND `project_id` = ?");
                            $taskupdate = $taskupdate->execute([ $currentDateTime ,$task_id[$key], $project_id[$key]]);

                            useBreak($conn, $user_id , $task_id[$key] , $project_id[$key]);

                            $workLog = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` , `work_percentage` , `taken_time`) VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)');
                            $workLog = $workLog->execute([$user_id , $task_id[$key], $project_id[$key],'qa_in_progress' , 'complete' , 'Complete By Qa' , 100 , $prFileMinutes]);

                            // add efficincy data                            
                            $effciencyAdd = $conn->prepare('INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency` , `total_time` , `taken_time`) VALUES (? , ? , ? , ? , ? , ? , ?)');
                            $effciencyAdd = $effciencyAdd->execute([$user_id , $task_id[$key], $project_id[$key], 'qa' , $effciency , $task_estimated_hour , $prFileMinutes]);

                             // set assign status is complete
                            $assignSql = $conn->prepare("UPDATE `assign` SET `status` = ? WHERE `task_id` = ? AND `project_id` = ? AND `role` = ? AND `user_id` = ?");
                            $assignSql = $assignSql->execute(['complete', $task_id[$key], $project_id[$key] , 'qa' , $user_id ]);


                        }else{
                            http_response_code(400);
                            echo json_encode(["message" => "Qa Work Log not found."]);
                            exit;
                        }
                    }else{
                        http_response_code(400);
                        echo json_encode(["message" => "Qa is not in progress"]);
                        exit;
                    }
                }
                $conn->commit();
                http_response_code(200);
                echo json_encode(["message" => "Qa in progress"]);

            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'startTaskQc')) {
        if ($_POST['task_id'] != '' && $_POST['project_id'] != '') {
            try {
                $conn->beginTransaction();
    
                $task_id = $_POST['task_id'];
                $project_id = $_POST['project_id'];
    
                foreach ($task_id as $key => $value) {
                    $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                    $check->execute([$task_id[$key], $project_id[$key], 'assign_qc']);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
    
                    if ($check) {
                        $taskupdate = $conn->prepare("UPDATE `tasks` SET `status`= ? WHERE `task_id` = ? AND `project_id` = ?");
                        $taskupdate->execute(['qc_in_progress', $task_id[$key], $project_id[$key]]);
    
                        $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks`) VALUES ( ? , ? , ? , ?, ? , ?)');
                        $sql->execute([$user_id, $task_id[$key], $project_id[$key], 'assign_qc', 'qc_in_progress', 'In Progress By Qc',]);
                    } else {
                        throw new Exception("Qc is already in progress");
                    }
                }
    
                $conn->commit();
                http_response_code(200);
                echo json_encode(["message" => "Qc in progress"]);
            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeTaskQc')) {
        if($_POST['task_id'] !='' && $_POST['project_id'] != ''){
            $task_id = $_POST['task_id'];
            $project_id = $_POST['project_id'];
            $number = count($task_id);
            try {
                $conn->beginTransaction();
                foreach ($task_id as $key => $value) {
    
                    $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                    $check->execute([$task_id[$key], $project_id[$key],'qc_in_progress']);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
                    if($check){
                        
                        $task_estimated_hour = (($check['estimated_hour']) * (0.20) * 60);

                        $checkWork = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `next_status` = ? ORDER BY `id` DESC");
                        $checkWork->execute([$user_id , $task_id[$key], $project_id[$key],'qc_in_progress']);
                        $checkWork = $checkWork->fetch(PDO::FETCH_ASSOC);
                        if($checkWork){
                            $givenTimestamp = strtotime($checkWork['created_at']);
                            $currentTimestamp = time();
                            $timeDifferenceInSeconds = $currentTimestamp - $givenTimestamp;
                            $timeDifferenceInMinutes = round($timeDifferenceInSeconds / 60 , 2);


                            // total time divide by total files
                            $timeDifferenceInMinutes = $timeDifferenceInMinutes/$number;

                            // check break is available or not
                            $checkBreak = $conn->prepare("SELECT SUM(time) AS total_time FROM `break` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `logged` = 0");
                            $checkBreak->execute([$user_id , $task_id[$key] , $project_id[$key]]);
                            $checkBreak = $checkBreak->fetch(PDO::FETCH_ASSOC);
                            if($checkBreak['total_time']){
                                $workingMinutes = floatval($timeDifferenceInMinutes) - floatval($checkBreak['total_time']);
                            }else{
                                $workingMinutes = floatval($timeDifferenceInMinutes);
                            }

                            $prFileMinutes = $workingMinutes;
                            
                            $effciency = ($task_estimated_hour/$prFileMinutes)*100;

                            // set task status is complete
                            $taskupdate = $conn->prepare("UPDATE `tasks` SET `status` = 'ready_for_qa' , `updated_at` = ? WHERE `task_id` = ? AND `project_id` = ?");
                            $taskupdate = $taskupdate->execute([ $currentDateTime ,$task_id[$key], $project_id[$key]]);

                            useBreak($conn, $user_id , $task_id[$key] , $project_id[$key]);

                            $workLog = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` , `work_percentage` , `taken_time`) VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)');
                            $workLog = $workLog->execute([$user_id , $task_id[$key], $project_id[$key],'qc_in_progress' , 'ready_for_qa' , 'Complete By Qc' , 100 , $prFileMinutes]);

                            // add efficincy data                            
                            $effciencyAdd = $conn->prepare('INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency` , `total_time` , `taken_time`) VALUES (? , ? , ? , ? , ? , ? , ?)');
                            $effciencyAdd = $effciencyAdd->execute([$user_id , $task_id[$key], $project_id[$key], 'qc' , $effciency , $task_estimated_hour , $prFileMinutes]);

                             // set assign status is complete
                            $assignSql = $conn->prepare("UPDATE `assign` SET `status` = ? WHERE `task_id` = ? AND `project_id` = ? AND `role` = ? AND `user_id` = ?");
                            $assignSql = $assignSql->execute(['complete', $task_id[$key], $project_id[$key] , 'qc' , $user_id ]);


                        }else{
                            http_response_code(400);
                            echo json_encode(["message" => "Qc Work Log not found."]);
                            exit;
                        }
                    }else{
                        http_response_code(400);
                        echo json_encode(["message" => "Qc is not in progress"]);
                        exit;
                    }
                }
                $conn->commit();
                http_response_code(200);
                echo json_encode(["message" => "Qc in progress"]);

            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'startTaskPro')) {
        if ($_POST['task_id'] != '' && $_POST['project_id'] != '') {
            try {
                $conn->beginTransaction();
    
                $task_id = $_POST['task_id'];
                $project_id = $_POST['project_id'];
    
                foreach ($task_id as $key => $value) {
                    $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                    $check->execute([$task_id[$key], $project_id[$key], 'assign_pro']);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
    
                    if ($check) {
                        $taskupdate = $conn->prepare("UPDATE `tasks` SET `status`= ? WHERE `task_id` = ? AND `project_id` = ?");
                        $taskupdate->execute(['pro_in_progress', $task_id[$key], $project_id[$key]]);
    
                        $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks`) VALUES ( ? , ? , ? , ?, ? , ?)');
                        $sql->execute([$user_id, $task_id[$key], $project_id[$key], 'assign_pro', 'pro_in_progress', 'In Progress By Pro',]);
                    } else {
                        throw new Exception("Pro is already in progress");
                    }
                }
    
                $conn->commit();
                http_response_code(200);
                echo json_encode(["message" => "Pro in progress"]);
            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeTaskPro')) {
        if($_POST['task_id'] !='' && $_POST['project_id'] != ''){
            $task_id = $_POST['task_id'];
            $project_id = $_POST['project_id'];
            $number = count($task_id);
            try {
                $conn->beginTransaction();
                foreach ($task_id as $key => $value) {
    
                    $check = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ? AND `status` = ?");
                    $check->execute([$task_id[$key], $project_id[$key],'pro_in_progress']);
                    $check = $check->fetch(PDO::FETCH_ASSOC);
                    if($check){
                        
                        $task_estimated_hour = (($check['estimated_hour']) * (0.75) * 60);

                        $checkWork = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `next_status` = ? ORDER BY `id` DESC");
                        $checkWork->execute([$user_id , $task_id[$key], $project_id[$key],'pro_in_progress']);
                        $checkWork = $checkWork->fetch(PDO::FETCH_ASSOC);
                        if($checkWork){
                            $givenTimestamp = strtotime($checkWork['created_at']);
                            $currentTimestamp = time();
                            $timeDifferenceInSeconds = $currentTimestamp - $givenTimestamp;
                            $timeDifferenceInMinutes = round($timeDifferenceInSeconds / 60 , 2);

                            // total time divide by total files
                            $timeDifferenceInMinutes = $timeDifferenceInMinutes/$number;

                            // check break is available or not
                            $checkBreak = $conn->prepare("SELECT SUM(time) AS total_time FROM `break` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `logged` = 0");
                            $checkBreak->execute([$user_id , $task_id[$key] , $project_id[$key]]);
                            $checkBreak = $checkBreak->fetch(PDO::FETCH_ASSOC);
                            if($checkBreak['total_time']){
                                $workingMinutes = floatval($timeDifferenceInMinutes) - floatval($checkBreak['total_time']);
                            }else{
                                $workingMinutes = floatval($timeDifferenceInMinutes);
                            }

                            $prFileMinutes = ($workingMinutes);
                            
                            $effciency = ($task_estimated_hour/$prFileMinutes)*100;

                            // set task status is complete
                            $taskupdate = $conn->prepare("UPDATE `tasks` SET `status` = 'ready_for_qc' , `updated_at` = ? WHERE `task_id` = ? AND `project_id` = ?");
                            $taskupdate = $taskupdate->execute([ $currentDateTime ,$task_id[$key], $project_id[$key]]);

                            useBreak($conn, $user_id , $task_id[$key] , $project_id[$key]);

                            $workLog = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` , `work_percentage` , `taken_time`) VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)');
                            $workLog = $workLog->execute([$user_id , $task_id[$key], $project_id[$key],'pro_in_progress' , 'ready_for_qc' , 'Complete By Pro' , 100 , $prFileMinutes]);

                            // add efficincy data                            
                            $effciencyAdd = $conn->prepare('INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency` , `total_time` , `taken_time`) VALUES (? , ? , ? , ? , ? , ? , ?)');
                            $effciencyAdd = $effciencyAdd->execute([$user_id , $task_id[$key], $project_id[$key], 'pro' , $effciency , $task_estimated_hour , $prFileMinutes]);

                             // set assign status is complete
                            $assignSql = $conn->prepare("UPDATE `assign` SET `status` = ? WHERE `task_id` = ? AND `project_id` = ? AND `role` = ? AND `user_id` = ?");
                            $assignSql = $assignSql->execute(['complete', $task_id[$key], $project_id[$key] , 'pro' , $user_id ]);


                        }else{
                            http_response_code(400);
                            echo json_encode(["message" => "Pro Work Log not found."]);
                            exit;
                        }
                    }else{
                        http_response_code(400);
                        echo json_encode(["message" => "Pro is not in progress"]);
                        exit;
                    }
                }
                $conn->commit();
                http_response_code(200);
                echo json_encode(["message" => "Pro in progress"]);

            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["message" => $e->getMessage()]);
            }
        }
    }
?>