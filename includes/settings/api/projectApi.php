<?php

include '../config/config.php';
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addProject')) {

    if (($_POST['project_name'] != '') && ($_POST['summary'] != '') && ($_POST['description'] != '') && ($_POST['complexity'] != '')  && ($_POST['area'] != '')  && ($_POST['vector'] != '') && ($_POST['start_date'] != '') && ($_POST['end_date'] != '')) {

        if($_POST['estimated_hour']){
            $estimated_hour = $_POST['estimated_hour'];
        }else{
            $date1 = new DateTime($_POST['start_date']);
            $date2 = new DateTime($_POST['end_date']);
            $interval = $date1->diff($date2);
            $daysDifference = $interval->days;
            $estimated_hour = $daysDifference * 8;
        }

        $project = array($_POST['project_name'] , $_POST['summary'] , $_POST['description'] , $_POST['area'] , $_POST['complexity'] ,$_POST['vector'] , $_POST['start_date'], $_POST['end_date'] , $estimated_hour);
        
        $check = $conn->prepare('INSERT INTO `projects`(`project_name`, `summary`, `description`,`area` , `complexity`,`vector`,`start_date`, `end_date` , `estimated_hour` ) VALUES (? , ? , ? , ? , ?, ? , ? , ?,? )');
        $result = $check->execute($project);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull Project Added.', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getAllProduct')) {
    $sql = $conn->prepare('SELECT * FROM `projects`');
    $sql->execute();
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'No project found', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'getProduct') {
    $sql = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
    $sql->execute([$_GET['id']]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
    http_response_code(200);
    echo json_encode($result[0]);
    } else {
    http_response_code(404);
    echo json_encode(array("message" => 'No project found', "status" => 404));
    }
}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'updateProject')) {

    if (($_POST['project_name'] != '') && ($_POST['summary'] != '') && ($_POST['description'] != '')  && ($_POST['area'] != '') && ($_POST['complexity'] != '') && ($_POST['vector'] != '') && ($_POST['start_date'] != '') && ($_POST['end_date'] != '') && ($_POST['product_id'] != '')) {

        if($_POST['estimated_hour']){
            $estimated_hour = $_POST['estimated_hour'];
        }else{
            $date1 = new DateTime($_POST['start_date']);
            $date2 = new DateTime($_POST['end_date']);
            $interval = $date1->diff($date2);
            $daysDifference = $interval->days;
            $estimated_hour = $daysDifference * 8;
        }

        $project = array($_POST['project_name'] , $_POST['summary'] , $_POST['description'] , $_POST['area'] , $_POST['complexity'] ,$_POST['vector'] , $_POST['start_date'], $_POST['end_date'] , $estimated_hour , $_POST['product_id']);
        
        $sql = $conn->prepare("UPDATE `projects` SET `project_name` = ?, `summary` = ?, `description`= ?, `area` = ? , `complexity`= ?, `vector` = ?, `start_date`= ?, `end_date` = ? , `estimated_hour` = ? WHERE `id` = ?");
        $result = $sql->execute($project);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull Project Update.', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'deleteProject')) {

    if ($_POST['project_id'] != ''){

               
        $deleteTask = $conn->prepare("DELETE FROM `tasks` WHERE `project_id` = ?");
        $result = $deleteTask->execute([$_POST['project_id']]);

        $deletePro = $conn->prepare("DELETE FROM `projects` WHERE `id` = ?");
        $result = $deletePro->execute([$_POST['project_id']]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull Project Delete.', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}

if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'completeProject')) {

    if ($_POST['id'] != ''){

        $deletePro = $conn->prepare("UPDATE `projects` SET `is_complete` = 1 WHERE `id` = ?");
        $result = $deletePro->execute([$_POST['id']]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull Project Complete.', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'inCompleteProject')) {

    if ($_POST['id'] != ''){

        $deletePro = $conn->prepare("UPDATE `projects` SET `is_complete` = 0 WHERE `id` = ?");
        $result = $deletePro->execute([$_POST['id']]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull Project inComplete.', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }

}

?>