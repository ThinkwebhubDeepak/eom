<?php

session_start();
include '../config/config.php';
header("content-Type: application/json");
$user_id = $_SESSION['userId'];


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addBreak')) {

    if (($_POST['break_type'] != '') && ($_POST['project_id'] != '') && ($_POST['task_id'] != '') && ($_POST['time'] != '')) {

        $sql = $conn->prepare('INSERT INTO `break`(`user_id`,`task_id`, `project_id`, `break_type`, `other`, `who`, `why`, `time`, `remarks`) VALUES ( ? , ? ,? , ? , ? , ? ,? , ? , ? )');
        $result = $sql->execute([$user_id, $_POST['task_id'], $_POST['project_id'], $_POST['break_type'], $_POST['other'], $_POST['who'], $_POST['why'], $_POST['time'], $_POST['remarks']]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'Add Break Successfull', "status" => 200, "time" => $_POST['time']));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went worrg', "status" => 500));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addVectorBreak')) {

    if (($_POST['break_type'] != '') && ($_POST['time'] != '')) {

        $chech_assign = $conn->prepare("SELECT * FROM `assign` WHERE `status` = 'assign' AND `role` = 'vector' AND `isActive` = 1 AND `user_id` = ?");
        $chech_assign->execute([$user_id]);
        $chech_assign = $chech_assign->fetchAll(PDO::FETCH_ASSOC);
        $time = $_POST['time'];
        if ($chech_assign) {
            $count = 0;
            $tasks = [];
            $projects = [];
            foreach ($chech_assign as $data) {
                $chech_working_task = $conn->prepare("SELECT * FROM `tasks` WHERE `vector_status` = 'vector_in_progress' AND  `task_id` = ? AND `project_id` = ?");
                $chech_working_task->execute([$data['task_id'], $data['project_id']]);
                $chech_working_task = $chech_working_task->fetchAll(PDO::FETCH_ASSOC);
                if($chech_working_task){
                    $tasks[] = $data['task_id'];
                    $projects[] = $data['project_id'];
                    $count++;
                }
            }

            $avgTime = intval($_POST['time']) / $count;
            
            foreach ($tasks as $key => $task) {     
                $sql = $conn->prepare('INSERT INTO `break`(`user_id`,`task_id`, `project_id`, `break_type`, `other`, `who`, `why`, `time`, `remarks`) VALUES ( ? , ? ,? , ? , ? , ? ,? , ? , ? )');
                $result = $sql->execute([$user_id, $task , $projects[$key], $_POST['break_type'], $_POST['other'], $_POST['who'], $_POST['why'], $avgTime , $_POST['remarks']]);
            }
            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'Add Break Successfull', "status" => 200, "time" => $time));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'No Active Work Found', "status" => 500));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No Task Found", "status" => 400));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addQaBreak')) {

    if (($_POST['break_type'] != '') && ($_POST['time'] != '')) {

        $chech_assign = $conn->prepare("SELECT * FROM `assign` WHERE `status` = 'assign' AND `role` = 'qa' AND `isActive` = 1 AND `user_id` = ?");
        $chech_assign->execute([$user_id]);
        $chech_assign = $chech_assign->fetchAll(PDO::FETCH_ASSOC);
        $time = $_POST['time'];
        if ($chech_assign) {
            $count = 0;
            $tasks = [];
            $projects = [];

            foreach ($chech_assign as $data) {
                $chech_working_task = $conn->prepare("SELECT * FROM `tasks` WHERE `status` = 'qa_in_progress' AND  `task_id` = ? AND `project_id` = ?");
                $chech_working_task->execute([$data['task_id'], $data['project_id']]);
                $chech_working_task = $chech_working_task->fetchAll(PDO::FETCH_ASSOC);
                if($chech_working_task){
                    $tasks[] = $data['task_id'];
                    $projects[] = $data['project_id'];
                    $count++;
                }
            }

            $avgTime = intval($_POST['time']) / $count;

            foreach ($tasks as $key => $task) {     
                $sql = $conn->prepare('INSERT INTO `break`(`user_id`,`task_id`, `project_id`, `break_type`, `other`, `who`, `why`, `time`, `remarks`) VALUES ( ? , ? ,? , ? , ? , ? ,? , ? , ? )');
                $result = $sql->execute([$user_id, $task , $projects[$key], $_POST['break_type'], $_POST['other'], $_POST['who'], $_POST['why'], $avgTime , $_POST['remarks']]);
            }
            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'Add Break Successfull', "status" => 200, "time" => $time));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'No Active Work Found', "status" => 500));
            }
           
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No Task Found", "status" => 400));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addQcBreak')) {

    if (($_POST['break_type'] != '') && ($_POST['time'] != '')) {

        $chech_assign = $conn->prepare("SELECT * FROM `assign` WHERE `status` = 'assign' AND `role` = 'qc' AND `isActive` = 1 AND `user_id` = ?");
        $chech_assign->execute([$user_id]);
        $chech_assign = $chech_assign->fetchAll(PDO::FETCH_ASSOC);
        $time = $_POST['time'];
        if ($chech_assign) {
            $count = 0;
            $tasks = [];
            $projects = [];

            foreach ($chech_assign as $data) {
                $chech_working_task = $conn->prepare("SELECT * FROM `tasks` WHERE `status` = 'qc_in_progress' AND  `task_id` = ? AND `project_id` = ?");
                $chech_working_task->execute([$data['task_id'], $data['project_id']]);
                $chech_working_task = $chech_working_task->fetchAll(PDO::FETCH_ASSOC);
                if($chech_working_task){
                    $tasks[] = $data['task_id'];
                    $projects[] = $data['project_id'];
                    $count++;
                }
            }

            $avgTime = intval($_POST['time']) / $count;

            foreach ($tasks as $key => $task) {     
                $sql = $conn->prepare('INSERT INTO `break`(`user_id`,`task_id`, `project_id`, `break_type`, `other`, `who`, `why`, `time`, `remarks`) VALUES ( ? , ? ,? , ? , ? , ? ,? , ? , ? )');
                $result = $sql->execute([$user_id, $task , $projects[$key], $_POST['break_type'], $_POST['other'], $_POST['who'], $_POST['why'], $avgTime , $_POST['remarks']]);
            }
            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'Add Break Successfull', "status" => 200, "time" => $time));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'No Active Work Found', "status" => 500));
            }
           
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No Task Found", "status" => 400));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addProBreak')) {

    if (($_POST['break_type'] != '') && ($_POST['time'] != '')) {

        $chech_assign = $conn->prepare("SELECT * FROM `assign` WHERE `status` = 'assign' AND `role` = 'pro' AND `isActive` = 1 AND `user_id` = ?");
        $chech_assign->execute([$user_id]);
        $chech_assign = $chech_assign->fetchAll(PDO::FETCH_ASSOC);
        $time = $_POST['time'];
        if ($chech_assign) {
            $count = 0;
            $tasks = [];
            $projects = [];

            foreach ($chech_assign as $data) {
                $chech_working_task = $conn->prepare("SELECT * FROM `tasks` WHERE `status` = 'pro_in_progress' AND  `task_id` = ? AND `project_id` = ?");
                $chech_working_task->execute([$data['task_id'], $data['project_id']]);
                $chech_working_task = $chech_working_task->fetchAll(PDO::FETCH_ASSOC);
                if($chech_working_task){
                    $tasks[] = $data['task_id'];
                    $projects[] = $data['project_id'];
                    $count++;
                }
            }

            $avgTime = intval($_POST['time']) / $count;

            foreach ($tasks as $key => $task) {     
                $sql = $conn->prepare('INSERT INTO `break`(`user_id`,`task_id`, `project_id`, `break_type`, `other`, `who`, `why`, `time`, `remarks`) VALUES ( ? , ? ,? , ? , ? , ? ,? , ? , ? )');
                $result = $sql->execute([$user_id, $task , $projects[$key], $_POST['break_type'], $_POST['other'], $_POST['who'], $_POST['why'], $avgTime , $_POST['remarks']]);
            }
            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'Add Break Successfull', "status" => 200, "time" => $time));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'No Active Work Found', "status" => 500));
            }
           
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "No Task Found", "status" => 400));
        }

    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
    }
}

?>