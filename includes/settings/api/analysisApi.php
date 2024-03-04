<?php

include '../config/config.php';
date_default_timezone_set('Asia/Kolkata');
header("content-Type: application/json");
session_start();
$assign_by = $_SESSION['userId'];
$currentDateTime = date('Y-m-d H:i:s');

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'today')) {

    if(isset($_GET['project_id']) && $_GET['project_id'] != ''){
        $sql2 = $conn->prepare('SELECT * FROM efficiency WHERE project_id = ? AND DATE(created_at) = CURDATE() ORDER BY `efficiency`.`id` DESC');
        $sql2->execute([$_GET['project_id']]);
    }else{
        $sql2 = $conn->prepare('SELECT * FROM efficiency WHERE DATE(created_at) = CURDATE() ORDER BY `efficiency`.`id` DESC');
        $sql2->execute();
    }
    $result2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    foreach ($result2 as $value) {
        $project = $conn->prepare('SELECT * FROM projects WHERE id = ?');
        $project->execute([$value['project_id']]);
        $project = $project->fetch(PDO::FETCH_ASSOC);

        $user = $conn->prepare('SELECT * FROM users WHERE id = ?');
        $user->execute([$value['user_id']]);
        $user = $user->fetch(PDO::FETCH_ASSOC);
        $value['name'] = $user['first_name'].' '.$user['last_name'];
        $value['project_name'] = $project['project_name'];
        $value['date'] = date('d M, Y', strtotime($value['created_at']));
        $data[] = $value;
    }
    echo json_encode($data);
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'yesterday')) {

    if(isset($_GET['project_id']) && $_GET['project_id'] != ''){
        $sql2 = $conn->prepare('SELECT * FROM efficiency WHERE project_id = ? AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY ORDER BY `efficiency`.`id` DESC');
        $sql2->execute([$_GET['project_id']]);
    }else{
        $sql2 = $conn->prepare('SELECT * FROM efficiency WHERE DATE(created_at) = CURDATE() - INTERVAL 1 DAY ORDER BY `efficiency`.`id` DESC');
        $sql2->execute();   
    }
    $result2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    foreach ($result2 as $value) {
        $project = $conn->prepare('SELECT * FROM projects WHERE id = ?');
        $project->execute([$value['project_id']]);
        $project = $project->fetch(PDO::FETCH_ASSOC);

        $user = $conn->prepare('SELECT * FROM users WHERE id = ?');
        $user->execute([$value['user_id']]);
        $user = $user->fetch(PDO::FETCH_ASSOC);
        $value['name'] = $user['first_name'].' '.$user['last_name'];
        $value['project_name'] = $project['project_name'];
        $value['date'] = date('d M, Y', strtotime($value['created_at']));
        $data[] = $value;
    }
    echo json_encode($data);
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'month')) {

    if(isset($_GET['project_id']) && $_GET['project_id'] != ''){
        $sql2 = $conn->prepare('SELECT * FROM efficiency WHERE project_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY `efficiency`.`id` DESC');
        $sql2->execute([$_GET['project_id']]);
    }else{
        $sql2 = $conn->prepare('SELECT * FROM efficiency WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY `efficiency`.`id` DESC');
        $sql2->execute();
    }
    $result2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    foreach ($result2 as $value) {
        $project = $conn->prepare('SELECT * FROM projects WHERE id = ?');
        $project->execute([$value['project_id']]);
        $project = $project->fetch(PDO::FETCH_ASSOC);
        
        $user = $conn->prepare('SELECT * FROM users WHERE id = ?');
        $user->execute([$value['user_id']]);
        $user = $user->fetch(PDO::FETCH_ASSOC);
        $value['name'] = $user['first_name'].' '.$user['last_name'];
        $value['project_name'] = $project['project_name'];
        $value['date'] = date('d M, Y', strtotime($value['created_at']));
        $data[] = $value;
    }
    echo json_encode($data);
}

if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'week')) {

    if(isset($_GET['project_id']) && $_GET['project_id'] != ''){
        $sql2 = $conn->prepare('SELECT * FROM efficiency WHERE project_id = ? AND created_at >= CURDATE() - INTERVAL 7 DAY ORDER BY id DESC');
        $sql2->execute([$_GET['project_id']]);
    }else{
        $sql2 = $conn->prepare('SELECT * FROM efficiency WHERE created_at >= CURDATE() - INTERVAL 7 DAY ORDER BY id DESC');
        $sql2->execute();
    }
    $result2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    foreach ($result2 as $value) {
        $project = $conn->prepare('SELECT * FROM projects WHERE id = ?');
        $project->execute([$value['project_id']]);
        $project = $project->fetch(PDO::FETCH_ASSOC);

        $user = $conn->prepare('SELECT * FROM users WHERE id = ?');
        $user->execute([$value['user_id']]);
        $user = $user->fetch(PDO::FETCH_ASSOC);
        $value['name'] = $user['first_name'].' '.$user['last_name'];
        $value['project_name'] = $project['project_name'];
        $value['date'] = date('d M, Y', strtotime($value['created_at']));
        $data[] = $value;
    }
    echo json_encode($data);
}
?>
