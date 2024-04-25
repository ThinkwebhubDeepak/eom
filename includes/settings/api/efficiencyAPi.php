<?php

    include '../config/config.php';
    date_default_timezone_set('Asia/Kolkata');
    header("content-Type: application/json");
    session_start();
    $user_id = $_SESSION['userId'];

    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getEfficiency') && ($_GET['method'] == 'today')){
        if($_GET['user_id'] != ''){
            $loginTime = $conn->prepare('SELECT * FROM `attendance` WHERE `user_id` = ? AND `date` = CURDATE()');
            $loginTime->execute([$_GET['user_id']]);
            $loginTime = $loginTime->fetch(PDO::FETCH_ASSOC);
            if($loginTime){

                $preparationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'preparation' AND `user_id` = ? AND DATE(`created_at`) = CURDATE()");
                $preparationTime->execute([$_GET['user_id']]);
                $preparationTime =  $preparationTime->fetch(PDO::FETCH_ASSOC);

                $finalizationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'finalization' AND `user_id` = ? AND DATE(`created_at`) = CURDATE()");
                $finalizationTime->execute([$_GET['user_id']]);
                $finalizationTime =  $finalizationTime->fetch(PDO::FETCH_ASSOC);

                $feedbackTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'feedback' AND `user_id` = ? AND DATE(`created_at`) = CURDATE()");
                $feedbackTime->execute([$_GET['user_id']]);
                $feedbackTime =  $feedbackTime->fetch(PDO::FETCH_ASSOC);

                $trainingTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'training' AND `user_id` = ? AND DATE(`created_at`) = CURDATE()");
                $trainingTime->execute([$_GET['user_id']]);
                $trainingTime =  $trainingTime->fetch(PDO::FETCH_ASSOC);

                $project_time = ["preparation" => $preparationTime['taken_time'] , "finalization" => $finalizationTime['taken_time'] , "feedback" => $feedbackTime['taken_time'], "training" => $trainingTime['taken_time']];

                $clock_in_time = $loginTime['clock_in_time'];
                $clock_out_time = $loginTime['clock_out_time'] ?: date("H:i:s");
                $clock_in_datetime = new DateTime($clock_in_time);
                $clock_out_datetime = new DateTime($clock_out_time);
                $interval = $clock_in_datetime->diff($clock_out_datetime);
                $duration = $interval->h * 60 + $interval->i;

                $task_id = $conn->prepare("SELECT DISTINCT task_id FROM `work_log` WHERE `date` = CURDATE() AND `user_id` = ?  ORDER BY `work_log`.`id` DESC");
                $task_id->execute([$_GET['user_id']]);
                $task_id = $task_id->fetchAll(PDO::FETCH_ASSOC);
                $arr = [];
                $taskTime = 0;
                foreach ($task_id as $value) {

                    $taskDetails = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                    $taskDetails->execute([$value['task_id']]);
                    $taskDetails = $taskDetails->fetch(PDO::FETCH_ASSOC);

                    $projectDetails = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
                    $projectDetails->execute([$taskDetails['project_id']]);
                    $projectDetails = $projectDetails->fetch(PDO::FETCH_ASSOC);

                    switch ($projectDetails['area']) {
                        case 'sqkm':
                            $taskDetails['area_lkm'] =  0;
                            break;
                        
                        case 'lkm':
                            $taskDetails['area_lkm'] =  $taskDetails['area_sqkm'];
                            $taskDetails['area_sqkm'] = 0;
                            break;
                    }

                    $workLogs = $conn->prepare("SELECT * FROM `work_log` WHERE `date` = CURDATE() AND `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `prev_status` = ?  ORDER BY `work_log`.`id` DESC");
                    $workLogs->execute([$_GET['user_id'],$value['task_id'] , $projectDetails['id'] , 'pro_in_progress']);
                    $workLogs = $workLogs->fetchAll(PDO::FETCH_ASSOC);
                    $workPercentage = 0;
                    $time = 0;
                    foreach ($workLogs as $workLog) {
                        $workPercentage += intval($workLog['work_percentage']);
                        $time += intval($workLog['taken_time']);
                        $taskTime += intval($workLog['taken_time']);
                    }
                    $part = 0.75;

                    if($workPercentage > 0){
                        $arr['pro'][] = ["task_id" => $value['task_id'] ,"percentage" => $workPercentage > 100 ? 100 : $workPercentage , "time" => $time , "task_time" => $taskDetails['estimated_hour'] * 60 * $part , "area_sqkm" => floatval($taskDetails['area_sqkm']) ,  "area_lkm" => floatval($taskDetails['area_lkm'])];
                    }
                    // qc

                    $workLogs = $conn->prepare("SELECT * FROM `work_log` WHERE `date` = CURDATE() AND `user_id` = ? AND `task_id` = ? AND project_id  = ? AND `prev_status` = ?  ORDER BY `work_log`.`id` DESC");
                    $workLogs->execute([$_GET['user_id'],$value['task_id'] ,  $projectDetails['id'] , 'qc_in_progress']);
                    $workLogs = $workLogs->fetchAll(PDO::FETCH_ASSOC);
                    $workPercentage = 0;
                    $time = 0;
                    foreach ($workLogs as $workLog) {
                        $workPercentage += intval($workLog['work_percentage']);
                        $time += intval($workLog['taken_time']);
                        $taskTime += intval($workLog['taken_time']);
                    }
                    $part = 0.20;
                    if($workPercentage > 0){
                        $arr['qc'][] = ["task_id" => $value['task_id'] ,"percentage" => $workPercentage > 100 ? 100 : $workPercentage , "time" => $time , "task_time" => $taskDetails['estimated_hour'] * 60 * $part , "area_sqkm" => floatval($taskDetails['area_sqkm']),  "area_lkm" => floatval($taskDetails['area_lkm'])];
                    }

                    // qa
                    
                    $workLogs = $conn->prepare("SELECT * FROM `work_log` WHERE `date` = CURDATE() AND `user_id` = ? AND `task_id` = ? AND project_id  = ? AND `prev_status` = ?  ORDER BY `work_log`.`id` DESC");
                    $workLogs->execute([$_GET['user_id'],$value['task_id']  , $projectDetails['id'] , 'qa_in_progress']);
                    $workLogs = $workLogs->fetchAll(PDO::FETCH_ASSOC);
                    $workPercentage = 0;
                    $time = 0;
                    foreach ($workLogs as $workLog) {
                        $workPercentage += intval($workLog['work_percentage']);
                        $time += intval($workLog['taken_time']);
                        $taskTime += intval($workLog['taken_time']);
                    }
                    if($taskDetails['vector_status']){
                        $part = 0.02;
                    }else{
                        $part = 0.05;
                    }

                    if($workPercentage > 0){
                        $arr['qa'][] = ["task_id" => $value['task_id'] ,"percentage" => $workPercentage > 100 ? 100 : $workPercentage , "time" => $time , "task_time" => $taskDetails['estimated_hour'] * 60 * $part , "area_sqkm" => floatval($taskDetails['area_sqkm']),  "area_lkm" => floatval($taskDetails['area_lkm'])];
                    }


                    // vector
                    
                    $workLogs = $conn->prepare("SELECT * FROM `work_log` WHERE `date` = CURDATE() AND `user_id` = ? AND `task_id` = ? AND project_id  = ? AND `prev_status` = ?  ORDER BY `work_log`.`id` DESC");
                    $workLogs->execute([$_GET['user_id'],$value['task_id'] ,  $projectDetails['id'] , 'vector_in_progress']);
                    $workLogs = $workLogs->fetchAll(PDO::FETCH_ASSOC);
                    $workPercentage = 0;
                    $time = 0;
                    foreach ($workLogs as $workLog) {
                        $workPercentage += intval($workLog['work_percentage']);
                        $time += intval($workLog['taken_time']);
                        $taskTime += intval($workLog['taken_time']);
                    }

                    if($workPercentage > 0){
                        $part = 0.03;
                        $arr['vector'][] = ["task_id" => $value['task_id'] ,"percentage" => $workPercentage > 100 ? 100 : $workPercentage , "time" => $time , "task_time" => $taskDetails['estimated_hour'] * 60 * $part , "area_sqkm" => floatval($taskDetails['area_sqkm']) , "area_lkm" => floatval($taskDetails['area_lkm'])];
                    }

                }

                $breaks = $conn->prepare("SELECT * FROM `break` WHERE DATE(`created_at`) = CURDATE() AND `user_id` = ?");
                $breaks->execute([$_GET['user_id']]);
                $breaks = $breaks->fetchAll(PDO::FETCH_ASSOC);
                $breakTime = 0;
                $breakArr = [];
                foreach ($breaks as $break) {
                    $breakTime += $break['time'];
                    $breakArr[] = ["task_id" => $break['task_id'], "time" => $break['time']];
                }


                $data = ["active_time" => $duration, "total_time" => $total_time, "dataTask" => $arr ,"breakData" => $breakArr , "break" => $breakTime , "task" => $taskTime , "project_time" => $project_time];
                echo json_encode($data);
            }else{
                http_response_code(400);
                echo json_encode(["message" => "Today is Absent."]);
            }
        }else{
            http_response_code(400);
            echo json_encode(["message" => "User Id not found."]);
        }
    }

    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getEfficiency') && ($_GET['method'] == 'monthly')){
        if($_GET['user_id'] != ''){
            $loginTimes = $conn->prepare('SELECT * FROM `attendance` WHERE `user_id` = ? AND `date` BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()');
            $loginTimes->execute([$_GET['user_id']]);
            $loginTimes = $loginTimes->fetchAll(PDO::FETCH_ASSOC);
            $durations = 0;
            $taskTime = 0;
            $breakTime = 0;
            $arr = [];
            $breakArr = [];
            foreach ($loginTimes as $loginTime) {

                $preparationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'preparation' AND `user_id` = ? AND DATE(`created_at`) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()");
                $preparationTime->execute([$_GET['user_id']]);
                $preparationTime =  $preparationTime->fetch(PDO::FETCH_ASSOC);

                $finalizationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'finalization' AND `user_id` = ? AND DATE(`created_at`) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()");
                $finalizationTime->execute([$_GET['user_id']]);
                $finalizationTime =  $finalizationTime->fetch(PDO::FETCH_ASSOC);

                $feedbackTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'feedback' AND `user_id` = ? AND DATE(`created_at`) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()");
                $feedbackTime->execute([$_GET['user_id']]);
                $feedbackTime =  $feedbackTime->fetch(PDO::FETCH_ASSOC);

                $trainingTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'training' AND `user_id` = ? AND DATE(`created_at`) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE()");
                $trainingTime->execute([$_GET['user_id']]);
                $trainingTime =  $trainingTime->fetch(PDO::FETCH_ASSOC);

                $project_time = ["preparation" => $preparationTime['taken_time'] , "finalization" => $finalizationTime['taken_time'] , "feedback" => $feedbackTime['taken_time'], "training" => $trainingTime['taken_time']];

                    $clock_in_time = $loginTime['clock_in_time'];
                    $clock_out_time = $loginTime['clock_out_time'] ?: date("H:i:s");
                    $clock_in_datetime = new DateTime($clock_in_time);
                    $clock_out_datetime = new DateTime($clock_out_time);
                    $interval = $clock_in_datetime->diff($clock_out_datetime);
                    $duration = $interval->h * 60 + $interval->i;
                    $durations += $duration;
    
                    $task_id = $conn->prepare("SELECT DISTINCT task_id FROM `work_log` WHERE `date` = ? AND `user_id` = ?");
                    $task_id->execute([$loginTime['date'] , $_GET['user_id']]);
                    $task_id = $task_id->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($task_id as $value) {
                        $workLogs = $conn->prepare("SELECT * FROM `work_log` WHERE `date` = ? AND `user_id` = ? AND `task_id` = ?");
                        $workLogs->execute([$loginTime['date'] , $_GET['user_id'],$value['task_id']]);
                        $workLogs = $workLogs->fetchAll(PDO::FETCH_ASSOC);
                        $workPercentage = 0;
                        $time = 0;
                        foreach ($workLogs as $workLog) {
                            $workPercentage += intval($workLog['work_percentage']);
                            $time += intval($workLog['taken_time']);
                            $taskTime += intval($workLog['taken_time']);

                            $taskDetails = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                            $taskDetails->execute([$value['task_id']]);
                            $taskDetails = $taskDetails->fetch(PDO::FETCH_ASSOC);

                            $projectDetails = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
                            $projectDetails->execute([$taskDetails['project_id']]);
                            $projectDetails = $projectDetails->fetch(PDO::FETCH_ASSOC);

                            switch ($projectDetails['area']) {
                                case 'sqkm':
                                    $taskDetails['area_lkm'] =  0;
                                    break;
                                
                                case 'lkm':
                                    $taskDetails['area_lkm'] =  $taskDetails['area_sqkm'];
                                    $taskDetails['area_sqkm'] = 0;
                                    break;
                            }

                            switch ($workLog['next_status']) {
                                case 'pro_in_progress':
                                    $role = 'pro';
                                    $part = 0.75;
                                    break;
                                case 'qc_in_progress':
                                    $role = 'qc';
                                    $part = 0.20;
                                    break;
                                case 'qa_in_progress':
                                    $role = 'qa';
                                    if($taskDetails['vector_status']){
                                        $part = 0.02;
                                    }else{
                                        $part = 0.05;
                                    }
                                    break;
                                case 'vector_in_progress':
                                    $role = 'vector';
                                    $part = 0.03;
                                    break;
                            }
                        }

                        $arr[$role][] = ["task_id" => $value['task_id'] , "percentage" => $workPercentage > 100 ? 100 : $workPercentage , "time" => $time , "task_time" => $taskDetails['estimated_hour'] * 60 * $part , "area_sqkm" => floatval($taskDetails['area_sqkm']) ,  "area_lkm" => floatval($taskDetails['area_lkm'])];
                    }
    
                    $breaks = $conn->prepare("SELECT * FROM `break` WHERE DATE(`created_at`) = ? AND `user_id` = ?");
                    $breaks->execute([$loginTime['date'],$_GET['user_id']]);
                    $breaks = $breaks->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($breaks as $break) {
                        $breakTime += $break['time'];
                        $breakArr[] = ["task_id" => $break['task_id'], "time" => $break['time']];
                    } 
            }
            $data = ["active_time" => $durations, "total_time" => $total_time, "dataTask" => $arr ,"breakData" => $breakArr , "break" => $breakTime , "task" => $taskTime , "project_time" => $project_time ];
            echo json_encode($data);
        }else{
            http_response_code(400);
            echo json_encode(["message" => "User Id not found."]);
        }
    }
    
    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getEfficiency') && ($_GET['method'] == 'yearly')){
        if($_GET['user_id'] != ''){
            $loginTimes = $conn->prepare('SELECT * FROM `attendance` WHERE `user_id` = ? AND `date` BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND CURDATE()');
            $loginTimes->execute([$_GET['user_id']]);
            $loginTimes = $loginTimes->fetchAll(PDO::FETCH_ASSOC);
            $durations = 0;
            $taskTime = 0;
            $breakTime = 0;
            $arr = [];
            $breakArr = [];
            foreach ($loginTimes as $loginTime) {

                $preparationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'preparation' AND `user_id` = ? AND DATE(`created_at`) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND CURDATE()");
                $preparationTime->execute([$_GET['user_id']]);
                $preparationTime =  $preparationTime->fetch(PDO::FETCH_ASSOC);

                $finalizationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'finalization' AND `user_id` = ? AND DATE(`created_at`) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND CURDATE()");
                $finalizationTime->execute([$_GET['user_id']]);
                $finalizationTime =  $finalizationTime->fetch(PDO::FETCH_ASSOC);

                $feedbackTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'feedback' AND `user_id` = ? AND DATE(`created_at`) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND CURDATE()");
                $feedbackTime->execute([$_GET['user_id']]);
                $feedbackTime =  $feedbackTime->fetch(PDO::FETCH_ASSOC);

                $trainingTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'training' AND `user_id` = ? AND DATE(`created_at`) BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 YEAR) AND CURDATE()");
                $trainingTime->execute([$_GET['user_id']]);
                $trainingTime =  $trainingTime->fetch(PDO::FETCH_ASSOC);

                $project_time = ["preparation" => $preparationTime['taken_time'] , "finalization" => $finalizationTime['taken_time'] , "feedback" => $feedbackTime['taken_time'], "training" => $trainingTime['taken_time']];

                    $clock_in_time = $loginTime['clock_in_time'];
                    $clock_out_time = $loginTime['clock_out_time'] ?: date("H:i:s");
                    $clock_in_datetime = new DateTime($clock_in_time);
                    $clock_out_datetime = new DateTime($clock_out_time);
                    $interval = $clock_in_datetime->diff($clock_out_datetime);
                    $duration = $interval->h * 60 + $interval->i;
                    $durations += $duration;
    
                    $task_id = $conn->prepare("SELECT DISTINCT task_id FROM `work_log` WHERE `date` = ? AND `user_id` = ?");
                    $task_id->execute([$loginTime['date'] , $_GET['user_id']]);
                    $task_id = $task_id->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($task_id as $value) {
                        $workLogs = $conn->prepare("SELECT * FROM `work_log` WHERE `date` = ? AND `user_id` = ? AND `task_id` = ?");
                        $workLogs->execute([$loginTime['date'] , $_GET['user_id'],$value['task_id']]);
                        $workLogs = $workLogs->fetchAll(PDO::FETCH_ASSOC);
                        $workPercentage = 0;
                        $time = 0;
                        foreach ($workLogs as $workLog) {
                            $workPercentage += intval($workLog['work_percentage']);
                            $time += intval($workLog['taken_time']);
                            $taskTime += intval($workLog['taken_time']);

                            $taskDetails = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                            $taskDetails->execute([$value['task_id']]);
                            $taskDetails = $taskDetails->fetch(PDO::FETCH_ASSOC);

                            $projectDetails = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
                            $projectDetails->execute([$taskDetails['project_id']]);
                            $projectDetails = $projectDetails->fetch(PDO::FETCH_ASSOC);

                            switch ($projectDetails['area']) {
                                case 'sqkm':
                                    $taskDetails['area_lkm'] =  0;
                                    break;
                                
                                case 'lkm':
                                    $taskDetails['area_lkm'] =  $taskDetails['area_sqkm'];
                                    $taskDetails['area_sqkm'] = 0;
                                    break;
                            }

                            switch ($workLog['next_status']) {
                                case 'pro_in_progress':
                                    $role = 'pro';
                                    $part = 0.75;
                                    break;
                                case 'qc_in_progress':
                                    $role = 'qc';
                                    $part = 0.20;
                                    break;
                                case 'qa_in_progress':
                                    $role = 'qa';
                                    if($taskDetails['vector_status']){
                                        $part = 0.02;
                                    }else{
                                        $part = 0.05;
                                    }
                                    break;
                                case 'vector_in_progress':
                                    $role = 'vector';
                                    $part = 0.03;
                                    break;
                            }
                        }

                        $arr[$role][] = ["task_id" => $value['task_id'] , "percentage" => $projectDetails['project_name'] , "time" => $time , "task_time" => $taskDetails['estimated_hour'] * 60 * $part , "area_sqkm" => floatval($taskDetails['area_sqkm']) ,  "area_lkm" => floatval($taskDetails['area_lkm'])];
                    }
    
                    $breaks = $conn->prepare("SELECT * FROM `break` WHERE DATE(`created_at`) = ? AND `user_id` = ?");
                    $breaks->execute([$loginTime['date'],$_GET['user_id']]);
                    $breaks = $breaks->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($breaks as $break) {
                        $breakTime += $break['time'];
                        $breakArr[] = ["task_id" => $break['task_id'], "time" => $break['time']];
                    } 
            }
            $data = ["active_time" => $durations, "total_time" => $total_time, "dataTask" => $arr ,"breakData" => $breakArr , "break" => $breakTime , "task" => $taskTime , "project_time" => $project_time];
            echo json_encode($data);
        }else{
            http_response_code(400);
            echo json_encode(["message" => "User Id not found."]);
        }
    }
    
    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getEfficiency') && ($_GET['method'] == 'date')){
        if($_GET['user_id'] != '' & $_GET['start_date'] != '' && $_GET['end_date'] != ''){
            $loginTimes = $conn->prepare('SELECT * FROM `attendance` WHERE `user_id` = ? AND `date` BETWEEN ? AND ?');
            $loginTimes->execute([$_GET['user_id'] , $_GET['start_date'] , $_GET['end_date']]);
            $loginTimes = $loginTimes->fetchAll(PDO::FETCH_ASSOC);
            $durations = 0;
            $taskTime = 0;
            $breakTime = 0;
            $arr = [];
            $breakArr = [];
            foreach ($loginTimes as $loginTime) {


                $preparationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'preparation' AND `user_id` = ? AND DATE(`created_at`) BETWEEN ? AND ?");
                $preparationTime->execute([$_GET['user_id'] , $_GET['start_date'] , $_GET['end_date']]);
                $preparationTime =  $preparationTime->fetch(PDO::FETCH_ASSOC);

                $finalizationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'finalization' AND `user_id` = ? AND DATE(`created_at`) BETWEEN ? AND ?");
                $finalizationTime->execute([$_GET['user_id'] , $_GET['start_date'] , $_GET['end_date']]);
                $finalizationTime =  $finalizationTime->fetch(PDO::FETCH_ASSOC);

                $feedbackTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'feedback' AND `user_id` = ? AND DATE(`created_at`) BETWEEN ? AND ?");
                $feedbackTime->execute([$_GET['user_id'], $_GET['start_date'] , $_GET['end_date']]);
                $feedbackTime =  $feedbackTime->fetch(PDO::FETCH_ASSOC);

                $trainingTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'training' AND `user_id` = ? AND DATE(`created_at`) BETWEEN ? AND ?");
                $trainingTime->execute([$_GET['user_id'], $_GET['start_date'] , $_GET['end_date']]);
                $trainingTime =  $trainingTime->fetch(PDO::FETCH_ASSOC);

                $project_time = ["preparation" => $preparationTime['taken_time'] , "finalization" => $finalizationTime['taken_time'] , "feedback" => $feedbackTime['taken_time'], "training" => $trainingTime['taken_time']];
                
                    $clock_in_time = $loginTime['clock_in_time'];
                    $clock_out_time = $loginTime['clock_out_time'] ?: date("H:i:s");
                    $clock_in_datetime = new DateTime($clock_in_time);
                    $clock_out_datetime = new DateTime($clock_out_time);
                    $interval = $clock_in_datetime->diff($clock_out_datetime);
                    $duration = $interval->h * 60 + $interval->i;
                    $durations += $duration;
    
                    $task_id = $conn->prepare("SELECT DISTINCT task_id FROM `work_log` WHERE `date` = ? AND `user_id` = ?");
                    $task_id->execute([$loginTime['date'] , $_GET['user_id']]);
                    $task_id = $task_id->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($task_id as $value) {
                        $workLogs = $conn->prepare("SELECT * FROM `work_log` WHERE `date` = ? AND `user_id` = ? AND `task_id` = ?");
                        $workLogs->execute([$loginTime['date'] , $_GET['user_id'],$value['task_id']]);
                        $workLogs = $workLogs->fetchAll(PDO::FETCH_ASSOC);
                        $workPercentage = 0;
                        $time = 0;
                        foreach ($workLogs as $workLog) {
                            $workPercentage += intval($workLog['work_percentage']);
                            $time += intval($workLog['taken_time']);
                            $taskTime += intval($workLog['taken_time']);

                            $taskDetails = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
                            $taskDetails->execute([$value['task_id']]);
                            $taskDetails = $taskDetails->fetch(PDO::FETCH_ASSOC);

                            $projectDetails = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
                            $projectDetails->execute([$taskDetails['project_id']]);
                            $projectDetails = $projectDetails->fetch(PDO::FETCH_ASSOC);

                            switch ($projectDetails['area']) {
                                case 'sqkm':
                                    $taskDetails['area_lkm'] =  0;
                                    break;
                                
                                case 'lkm':
                                    $taskDetails['area_lkm'] =  $taskDetails['area_sqkm'];
                                    $taskDetails['area_sqkm'] = 0;
                                    break;
                            }

                            switch ($workLog['next_status']) {
                                case 'pro_in_progress':
                                    $role = 'pro';
                                    $part = 0.75;
                                    break;
                                case 'qc_in_progress':
                                    $role = 'qc';
                                    $part = 0.20;
                                    break;
                                case 'qa_in_progress':
                                    $role = 'qa';
                                    if($taskDetails['vector_status']){
                                        $part = 0.02;
                                    }else{
                                        $part = 0.05;
                                    }
                                    break;
                                case 'vector_in_progress':
                                    $role = 'vector';
                                    $part = 0.03;
                                    break;
                            }
                        }

                        $arr[$role][] = ["task_id" => $value['task_id'] , "percentage" => $workPercentage > 100 ? 100 : $workPercentage , "time" => $time , "task_time" => $taskDetails['estimated_hour'] * 60 * $part , "area_sqkm" => floatval($taskDetails['area_sqkm']),  "area_lkm" => floatval($taskDetails['area_lkm'])];
                    }
    
                    $breaks = $conn->prepare("SELECT * FROM `break` WHERE DATE(`created_at`) = ? AND `user_id` = ?");
                    $breaks->execute([$loginTime['date'],$_GET['user_id']]);
                    $breaks = $breaks->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($breaks as $break) {
                        $breakTime += $break['time'];
                        $breakArr[] = ["task_id" => $break['task_id'], "time" => $break['time']];
                    } 
            }
            $data = ["active_time" => $durations, "total_time" => $total_time, "dataTask" => $arr ,"breakData" => $breakArr , "break" => $breakTime , "task" => $taskTime , "project_time" => $project_time];
            echo json_encode($data);
        }else{
            http_response_code(400);
            echo json_encode(["message" => "User Id not found."]);
        }
    }

    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getEfficiency') && ($_GET['method'] == 'project')){
        if($_GET['user_id'] != '' && $_GET['project_id'] != ''){
            $project = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
            $project->execute([$_GET['project_id']]);
            $project = $project->fetch(PDO::FETCH_ASSOC);
            $arr = [];
            $data = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ? AND `project_id` = ?");
            $data->execute([$_GET['user_id'] , $_GET['project_id']]);
            $data = $data->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $value) {
                $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?");
                $task->execute([$value['task_id'] , $_GET['project_id']]);
                $task = $task->fetch(PDO::FETCH_ASSOC);
                $value['area_sqkm'] = $task['area_sqkm'];
                $value['project_name'] = $project['project_name'];
                $arr[] = $value;
            }
            echo json_encode($arr);
        }else{
            http_response_code(400);
            echo json_encode(["message" => "User Id not found."]);
        }
    }
    
    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getEfficiency') && ($_GET['method'] == 'task')){
        if($_GET['user_id'] != '' && $_GET['task_id'] != ''){
            $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
            $task->execute([$_GET['task_id']]);
            $task = $task->fetch(PDO::FETCH_ASSOC);

            $project = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
            $project->execute([$task['project_id']]);
            $project = $project->fetch(PDO::FETCH_ASSOC);
            
            $data = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ?");
            $data->execute([$_GET['user_id'] , $_GET['task_id'] , $task['project_id']]);
            $data = $data->fetchAll(PDO::FETCH_ASSOC);

            $arr = [];
            foreach ($data as $value) {
                $value['area_sqkm'] = $task['area_sqkm'];
                $value['project_name'] = $project['project_name'];
                $arr[] = $value;
            }
            echo json_encode($arr);
        }else{
            http_response_code(400);
            echo json_encode(["message" => "User Id not found."]);
        }
    }

    if(($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getProjectEfficiency')){
        $method = $_GET['method'];
        if($method == 'all'){
            if($_GET['user_id'] != ''){
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ?");
                $efficiency->execute([$_GET['user_id']]);
            }else{
                $efficiency = $conn->prepare("SELECT * FROM `efficiency`");
                $efficiency->execute();
            }
        }else if($method == 'project'){
            if($_GET['user_id'] != ''){
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `project_id` = ? AND `user_id` = ?");
                $efficiency->execute([$_GET['product_id'],$_GET['user_id']]);
            }else{
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `project_id` = ?");
                $efficiency->execute([$_GET['product_id']]);
            }
        }else if($method == 'task'){
            if($_GET['user_id'] != ''){
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ? AND `user_id` = ?");
                $efficiency->execute([$_GET['task_id'],$_GET['user_id']]);
            }else{
                $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `task_id` = ?");
                $efficiency->execute([$_GET['task_id']]);
            }
        }else if($method == 'date'){
            if($_GET['start_date'] != '' && $_GET['end_date'] != ''){
                if($_GET['user_id'] != ''){
                    $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `created_at` BETWEEN ? AND ? AND `user_id` = ?");
                    $efficiency->execute([$_GET['start_date'],$_GET['end_date'],$_GET['user_id']]);
                }else{
                    $efficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `created_at` BETWEEN ? AND ?");
                    $efficiency->execute([$_GET['start_date'],$_GET['end_date']]);
                }
            }else{
                http_response_code(400);
                echo json_encode(["message" => "Start & End is required"]);
                exit;
            }
        }
    
        $efficiency = $efficiency->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($efficiency as $value) {
            $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
            $task->execute([$value['task_id']]);
            $task = $task->fetch(PDO::FETCH_ASSOC);
    
            $userslist = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
            $userslist->execute([$value['user_id']]);
            $userslist = $userslist->fetch(PDO::FETCH_ASSOC);
            $value['first_name'] = $userslist['first_name'];
            $value['last_name'] = $userslist['last_name'];
    
            $project = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
            $project->execute([$value['project_id']]);
            $project = $project->fetch(PDO::FETCH_ASSOC);
            $value['task_name'] = $task['area_sqkm'];
            $value['project_name'] = $project['project_name'];
            $data[] = $value;
        }
    
        http_response_code(200);
        echo json_encode($data);
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'fetchEfficiency') && ($_GET['date'] != '')) {
        $sql = $conn->prepare("SELECT `project_id`, `user_id`, `efficiency`, `task_id`, `profile`, `taken_time`, `total_time`, `created_at` FROM `efficiency` WHERE DATE(`created_at`) =  ? ORDER BY `created_at` DESC");
        $sql->execute([$_GET['date']]);
        $sql = $sql->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($sql as $value) {
            $project = $conn->prepare("SELECT `id`, `project_name` FROM `projects` WHERE `id` = ?");
            $project->execute([$value['project_id']]);
            $project = $project->fetch(PDO::FETCH_ASSOC);

            $user = $conn->prepare("SELECT `profile` , `first_name` , `id` ,`last_name` FROM `users` WHERE `id` = ?");
            $user->execute([$value['user_id']]);
            $user = $user->fetch(PDO::FETCH_ASSOC);

            if ($value['efficiency'] > 50) {
                $progress = '<div class="progress" role="progressbar" aria-label="Success  striped example" aria-valuenow="' . $value['efficiency'] . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success" style="width: ' . $value['efficiency'] . '%">' . $value['efficiency'] . '%</div></div>';
            } else {
                $progress = '<div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="' . $value['efficiency'] . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-danger " style="width: ' . $value['efficiency'] . '%">' . $value['efficiency'] . '%</div></div>';
            }

            $work = $conn->prepare("SELECT `created_at` FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = ? AND `user_id` = ?");
            $work->execute([$value['task_id'], $value['project_id'], 'assign_' . $value['profile'], $user['id']]);
            $work = $work->fetch(PDO::FETCH_ASSOC);
            $arr = ['efficiency' => $value['efficiency'] ,'image' => 'images/users/' . ($user['profile'] == '' ? 'default.jpg' : $user['profile']) , 'name' => $user['first_name'] . ' ' . $user['last_name'] , 'task' => $value['task_id'] , 'project' => $project['project_name'] , 'taken_time' => $value['taken_time'] , 'total_time' => $value['total_time'] , 'profile' => $value['profile'] , 'progress' => $progress , 'start' => date("j M Y, g:i A", strtotime($work['created_at'])) , 'end' => date("j M Y, g:i A", strtotime($value['created_at']))];
            $data[] = $arr;
        }
        echo json_encode($data);
    }

?>