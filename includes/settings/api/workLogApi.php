<?php

include '../config/config.php';
session_start();
date_default_timezone_set('Asia/Kolkata'); 
header("content-Type: application/json");
$user_id = $_SESSION['userId'];
$current_time = date("H:i:s");
$currentDate = date('Y-m-d');
$currentDateTime = date('Y-m-d H:i:s');

function pauseWork($conn , $user_id , $task_id , $project_id ,$status){
    $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks`) VALUES (? , ? , ? , ? , ? , ?)');
    $result = $sql->execute([$user_id, $task_id ,$project_id , $status , 'Pause Work', 'Stop Work']);
    return $result;
}

function useBreak($conn, $user_id , $task_id){
    $updateBreak = $conn->prepare("UPDATE `break` SET `logged` = 1 WHERE `user_id` = ? AND `task_id` = ? ");
    $updateBreak->execute(array($user_id,$task_id));
}

function efficiencyAdd($conn ,$user_id , $task_id , $project_id, $role){

    $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?");
    $task->execute([$task_id , $project_id]);
    $task = $task->fetch(PDO::FETCH_ASSOC);

    switch ($role) {
        case 'pro':
            $part = 0.75;
            break;
        case 'qc':
            $part = 0.20;
            break;
    }

    if($task['is_reassigned'] == 1 && $role == 'pro'){
        $part = $part / 2;
    }
    
    if($task['is_qc_failed'] == 1 && $role == 'qc'){
        $part = $part / 2;
    }

    $totalTime = $task['estimated_hour'] * 60 * $part;

    $workHoursforTime = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `user_id` = ? AND `prev_status` = ? ORDER BY `id` DESC;");
    $workHoursforTime->execute([$task_id, $project_id , $user_id , 'assign_'.$role]);
    $workHoursforTime = $workHoursforTime->fetch(PDO::FETCH_ASSOC);

    $sql_query = $conn->prepare("SELECT SUM(taken_time) AS total_taken_time FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `id` > ? ");
    $sql_query->execute([$user_id ,$task_id, $project_id , $workHoursforTime['id']]);
    $result = $sql_query->fetch(PDO::FETCH_ASSOC);
    $taken_time = $result['total_taken_time'] == '' ? 0.000001 : $result['total_taken_time'];

    $efficiency = ($totalTime / $taken_time ) * 100;

    $addEfficiency = $conn->prepare('INSERT INTO `efficiency`(`user_id`, `task_id`, `project_id`, `profile`, `efficiency` , `total_time` , `taken_time`) VALUES (? , ? , ? , ? , ? , ? , ?)');
    $addEfficiency = $addEfficiency->execute([$user_id , $task_id , $project_id,  $role , $efficiency ,$totalTime  , $taken_time ]);

}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addPauseLogWork')) {

    if($_POST['task_id'] != '' && $_POST['project_id'] != '' && $_POST['minute'] != '' && $_POST['status'] != '' && $_POST['work_percentage'] != ''){
        $taken_time = $_POST['minute'];
        $workPercentage = intval($_POST['work_percentage']);

        if($_POST['status'] == 'pro_in_progress'){
            $role = 'pro';
            $next_status = 'ready_for_qc';
        }else if($_POST['status'] == 'qc_in_progress'){
            $role = 'qc';
            $next_status = 'ready_for_qa';
        }else{
            $role = 'qa';
            $next_status = 'complete';
        }

         // current working percentage
        $workHoursforTime = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `user_id` = ? AND `prev_status` = ? ORDER BY `id` DESC;");
        $workHoursforTime->execute([$_POST['task_id'], $_POST['project_id'] , $user_id , 'assign_'.$role]);
        $workHoursforTime = $workHoursforTime->fetch(PDO::FETCH_ASSOC);

        $currentWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_work_percentage FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `id` > ? AND `prev_status` = ?");
        $currentWorkPercentage->execute([$user_id ,$_POST['task_id'] , $_POST['project_id']  , $workHoursforTime['id'] , $role."_in_progress"]);
        $result = $currentWorkPercentage->fetch(PDO::FETCH_ASSOC);
        $currentWorkPercentage = $result['total_work_percentage'] == '' ? 0 : intval($result['total_work_percentage']);

        if($workPercentage < 100){
            if ($currentWorkPercentage < $workPercentage){
                $sql = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`,`prev_status`, `next_status`, `remarks` , `work_percentage`,`taken_time`) VALUES (? , ? , ? , ?, ? , ? ,? , ?)');
                $result = $sql->execute([$user_id, $_POST['task_id'],$_POST['project_id'], $_POST['status'], $_POST['status'] , $_POST['remarks'] , $workPercentage - $currentWorkPercentage ,$taken_time]);
                if($result){
                    http_response_code(200);
                    echo json_encode(["message" => "Add Log Successfull"]);
                    pauseWork($conn , $user_id , $_POST['task_id'] , $_POST['project_id'] ,$_POST['status']);
                    useBreak($conn,$user_id,$_POST['task_id']);
                    exit();
                }
            }else{
                http_response_code(400);
                echo json_encode(["message" => "Previous Records not Found."]);
                exit();
            }
        }else{
            if($workPercentage == 100){

                // add work log
                $workLog = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`,`prev_status`, `next_status`, `remarks` , `work_percentage`,`taken_time`) VALUES (? , ? , ? , ?, ? , ? ,? , ?)');
                $workLog = $workLog->execute([$user_id, $_POST['task_id'],$_POST['project_id'], $_POST['status'], $next_status , $_POST['remarks'] , $workPercentage - $currentWorkPercentage ,$taken_time]);
             
                // update task data
                $updateTask = $conn->prepare('UPDATE `tasks` SET `status` = ? , `updated_at` = ? WHERE `task_id` = ? AND `project_id` = ?');
                $updateTask = $updateTask->execute([ $next_status , $currentDateTim , $_POST['task_id'] , $_POST['project_id']]);
                
                // update assign data
                $updateAssign = $conn->prepare('UPDATE `assign` SET `status` = ? , `updated_at` = ? WHERE `role` = ? AND `task_id` = ? AND `project_id` = ?');
                $updateAssign = $updateAssign->execute(['complete' , $currentDateTim , $role ,  $_POST['task_id'] , $_POST['project_id']]);

                // calculate efficiency

                efficiencyAdd($conn , $user_id , $_POST['task_id'] , $_POST['project_id'] , $role);
                
                http_response_code(200);
                echo json_encode(["message" => "Add Log Successfull"]);

                useBreak($conn,$user_id,$_POST['task_id']);
                exit();
                
            }else{
                http_response_code(400);
                echo json_encode(["message" => "Previous Records not Found."]);
                exit();
            }

        }
    }else{
        http_response_code(400);
        echo json_encode(["message" => "Fill All Required Fields"]);
        exit();
    }
}


// incomplete
if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'reAssignTask')) {

    if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['user_id'] != '')){
        
        $assign = $conn->prepare("SELECT * FROM `assign` WHERE `task_id` = ? AND `project_id` = ? AND `role` = 'pro' ORDER BY `created_at` DESC");
        $assign->execute([$_POST['task_id'],$_POST['project_id'] ]);
        $assign = $assign->fetch(PDO::FETCH_ASSOC);
        
        $checkTask = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?');
        $checkTask->execute([$_POST['task_id'], $_POST['project_id']]);
        $checkTask = $checkTask->fetch(PDO::FETCH_ASSOC);
        
        $checkEfficiency = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `profile` = 'pro'");
        $checkEfficiency->execute([$assign['user_id'], $_POST['task_id'], $_POST['project_id']]);
        $checkEfficiency = $checkEfficiency->fetch(PDO::FETCH_ASSOC);
        
        // if($assign['user_id'] == $_POST['user_id']){
        //     http_response_code(400);
        //     echo json_encode(array("message" => "This User already working on this Task", "status" => 400));
        //     exit;
        // }
        
        if($assign){
            $past_assigned_user = $assign['user_id'];

           
            $check = $conn->prepare('INSERT INTO `assign`(`user_id`, `project_id`, `task_id`, `role`, `status` , `assigned_by`) VALUES (? , ? , ? , ? , ? , ?)');
            $result = $check->execute([$_POST['user_id'], $_POST['project_id'] ,$_POST['task_id'], $assign['role'], "assign",$user_id]);
            if($result){
              
                $task = $conn->prepare("UPDATE `tasks` SET `is_reassigned`= 1 , `status` = ? WHERE `task_id` = ? AND `project_id` = ?");
                $task->execute(["assign_pro",$_POST['task_id'],$_POST['project_id']]);

                $efficiency = $conn->prepare("UPDATE `efficiency` SET `efficiency` = ? , `remarks` = ? WHERE `id` = ?");
                $efficiency->execute([$checkEfficiency['efficiency'] / 2, "fail" ,$checkEfficiency['id']]);
                
                $worklog = $conn->prepare("INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks` ,`change_type`) VALUES (? , ? , ? , 'in_progress' , 'in_progress' , 'assign'  ,'ressigned')");
                $worklog->execute([$_POST['user_id'],$_POST['task_id'],$_POST['project_id']]);
                
                    
        
                http_response_code(200);
                echo json_encode(array("message" => 'Re-Assign Task successfull.', "status" => 200));
            }else{
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }
            

        }
    }else{
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}
    
?>