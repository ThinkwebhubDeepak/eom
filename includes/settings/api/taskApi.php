<?php

include "../config/config.php";
// header("Access-Control-Allow-Origin: *");
// header('Content-Type: application/json');
session_start();
$user_id = $_SESSION['userId'];
$current_time = date("H:i:s");
$current_date = date("Y-m-d");
$currentDateTime = date("Y-m-d H:i:s");


if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addTask')) {

    if (($_POST['task_id'] != '') && ($_POST['project_id'] != '') && ($_POST['area_sqkm'] != '') && ($_POST['estimated_hour'] != '') && ($_POST['complexity'] != '')) {

        $checkProject = $conn->prepare("SELECT * FROM `projects` WHERE id = ?");
        $checkProject->execute([$_POST['project_id']]);
        $checkProject = $checkProject->fetch(PDO::FETCH_ASSOC);
        if (!$checkProject) {
            http_response_code(400);
            echo json_encode(["message" => "Project not found. Please Check Project."]);
            exit();
        } else {
            if ($checkProject['vector'] == 1) {
                $vector = 'pending';
            } else {
                $vector = null;
            }
        }


        if (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] == UPLOAD_ERR_OK) {
            $attachment = basename($_FILES['attachment']['name']);
            $uploadPath = '../../upload/attachment/' . $attachment;
            move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath);
        } else {
            $attachment = null;
        }

        $project = array($_POST['task_id'], $_POST['project_id'], $_POST['estimated_hour'], $_POST['area_sqkm'], $_POST['complexity'], $attachment, $_POST['start_date'], $_POST['end_date'], $vector);

        $check = $conn->prepare('INSERT INTO `tasks` (`task_id`, `project_id` ,`estimated_hour`, `area_sqkm`, `complexity`, `attachment`,`start_date`, `end_date` , `vector_status`) VALUES (? ,? , ? , ? , ? , ? , ? , ? , ?)');
        $result = $check->execute($project);

        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull task Added.', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Fill all required fields", "status" => 400));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'deleteTask') {
    if ($_POST['task_id'] != '' && $_POST['project_id'] != '') {

        $conn->beginTransaction();
        try {
            $sql = $conn->prepare("DELETE FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?");
            $result = $sql->execute([$_POST['task_id'], $_POST['project_id']]);
            if (!$result) {
                http_response_code(500);
                echo json_encode(array("message" => 'Task File Failed.', "status" => 500));
            }

            $sql = $conn->prepare("DELETE FROM `assign` WHERE `task_id` = ? AND `project_id` = ?");
            $result = $sql->execute([$_POST['task_id'], $_POST['project_id']]);
            if (!$result) {
                http_response_code(500);
                echo json_encode(array("message" => 'Assign Failed.', "status" => 500));
            }

            $sql = $conn->prepare("DELETE FROM `comments` WHERE `task_id` = ? AND `project_id` = ?");
            $result = $sql->execute([$_POST['task_id'], $_POST['project_id']]);
            if (!$result) {
                http_response_code(500);
                echo json_encode(array("message" => 'Comments Failed.', "status" => 500));
            }

            $sql = $conn->prepare("DELETE FROM `work_log` WHERE `task_id` = ? AND `project_id` = ?");
            $result = $sql->execute([$_POST['task_id'], $_POST['project_id']]);
            if (!$result) {
                http_response_code(500);
                echo json_encode(array("message" => 'Work Log Failed.', "status" => 500));
            }

            $conn->commit();

            http_response_code(200);
            echo json_encode(array("message" => 'Delete task successfull.', "status" => 404));
        } catch (Exception $e) {
            $conn->rollBack();

            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong.', "status" => 500));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => 'Fill All Required Field.', "status" => 404));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'updateTask') {

    $complexity = $_POST['complexity'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $task_id = $_POST['task_id'];
    $project_id = $_POST['project_id'];

    foreach ($task_id as $key => $value) {
        if ($start_date) {
            $startDate = date('Y-m-d', strtotime($start_date[$key]));
        }
        if ($endDate) {
            $endDate = date('Y-m-d', strtotime($end_date[$key]));
        }

        $sql = $conn->prepare("UPDATE `tasks` SET `complexity` = ?, `start_date` = ?, `end_date` = ? WHERE `task_id` = ?");
        $result = $sql->execute([$complexity[$key], $startDate, $endDate, $task_id[$key]]);
    }

    if ($result) {
        http_response_code(200);
        echo json_encode(array("message" => 'Update task successful...', "status" => 200));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => 'Something went wrong...', "status" => 500));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'UpdateTask') {

    if ($_POST['task_id'] != '' && $_POST['project_id'] != '' && $_POST['area_sqkm'] != '' && $_POST['estimated_hour'] != '' && $_POST['complexity'] != '') {

        $complexity = $_POST['complexity'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $task_id = $_POST['task_id'];
        $area_sqkm = $_POST['area_sqkm'];
        $project_id = $_POST['project_id'];
        $estimated_hour = $_POST['estimated_hour'];

        $sql = $conn->prepare("UPDATE `tasks` SET  `project_id` = ?,`area_sqkm` = ? , `complexity` = ?, `start_date` = ? , `end_date` = ? ,`estimated_hour` = ? WHERE `task_id` = ?");
        $result = $sql->execute([$project_id, $area_sqkm, $complexity, $start_date, $end_date, $estimated_hour, $task_id]);

        if (isset($_FILES["attachment"]) && $_FILES["attachment"]["error"] == UPLOAD_ERR_OK) {
            $attachment = basename($_FILES['attachment']['name']);
            $uploadPath = '../../upload/attachment/' . $attachment;
            move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath);
            $sql = $conn->prepare("UPDATE `tasks` SET  `attachment` = ?  WHERE `task_id` = ?");
            $result = $sql->execute([$attachment, $task_id]);
        }


        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'Update task successful...', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong...', "status" => 500));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => 'Fill All Required Field', "status" => 500));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['type'] === 'inProgress') {
    if ($_POST['task_id'] != '' && $_POST['project_id'] != '') {
        $assign = $conn->prepare('SELECT * FROM `assign` WHERE `task_id` = ? AND `project_id` = ? AND `user_id` = ?');
        $assign->execute([$_POST['task_id'], $_POST['project_id'], $user_id]);
        $assign = $assign->fetch(PDO::FETCH_ASSOC);
        if ($assign) {
            $task = $conn->prepare('SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?');
            $task->execute([$_POST['task_id'], $_POST['project_id']]);
            $task = $task->fetch(PDO::FETCH_ASSOC);
            switch ($task['status']) {
                case 'assign_pro':
                    $status = 'pro_in_progress';
                    $remarks = "In Progress By Employee";
                    break;
                case 'assign_qa':
                    $status = 'qa_in_progress';
                    $remarks = "In Progress By QA";
                    break;
                case 'assign_qc':
                    $status = 'qc_in_progress';
                    $remarks = "In Progress By QC";
                    break;
                case 'assign_vector':
                    $status = 'vector_in_progress';
                    $remarks = "In Progress By Vector";
                    break;
            }

            try {
                $conn->beginTransaction();

                $sql = $conn->prepare('UPDATE `tasks` SET `status` = ? , `updated_at` = ? WHERE `task_id` = ? AND `project_id` = ?');
                $result2 = $sql->execute([$status, $currentDateTime, $_POST['task_id'], $_POST['project_id']]);

                $sql3 = $conn->prepare('INSERT INTO `work_log`(`user_id`, `task_id`, `project_id`, `prev_status`, `next_status`, `remarks`) VALUES (? , ? , ? , ? , ? , ?)');
                $result3 = $sql3->execute([$user_id, $_POST['task_id'], $_POST['project_id'], $task['status'], $status, $remarks]);

                if ($result2 && $result3) {
                    $conn->commit();
                    http_response_code(200);
                    echo json_encode(array("message" => 'Successfull in Progress', "status" => 200, "next_status" => $status));
                } else {
                    $conn->rollBack();
                    http_response_code(404);
                    echo json_encode(array("message" => 'No task found', "status" => 404));
                }
            } catch (PDOException $e) {
                $conn->rollBack();
                http_response_code(500);
                echo json_encode(array("message" => 'Something Went Wrong', "status" => 500));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("message" => 'Assign Details not Found', "status" => 404));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Task Id & Project Id not Found', "status" => 404));
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'getDataTable') {
    if (!empty($_GET['project_id'])) {
        $i = 0;
        
        if ($_GET['vector'] == 1) {
            $sql2 = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id` = ? AND `status` <> 'complete' AND `vector_status` <> 'complete' ORDER BY `updated_at` DESC");
        } else {
            $sql2 = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id` = ? AND `status` <> 'complete' ORDER BY `updated_at` DESC");
        }

        $sql2->execute([$_GET['project_id']]);
        $tasks = $sql2->fetchAll(PDO::FETCH_ASSOC);
        // echo json_decode($task);
        // exit;

        foreach ($tasks as $task) {
            $assign = $conn->prepare("SELECT `user_id` FROM `assign` WHERE `isActive` = 1 AND `project_id` = ? AND `role` != 'vector' AND `task_id` = ? AND `status` = 'assign'");
            $assign->execute([$task['project_id'], $task['task_id']]);
            $assign = $assign->fetch(PDO::FETCH_ASSOC);

            $assignVector = $conn->prepare("SELECT `user_id` FROM `assign` WHERE `isActive` = 1 AND `project_id` = ? AND `role` = 'vector' AND `task_id` = ? AND `status` = 'assign'");
            $assignVector->execute([$task['project_id'], $task['task_id']]);
            $assignVector = $assignVector->fetch(PDO::FETCH_ASSOC);

            $user = $conn->prepare('SELECT first_name , last_name FROM `users` WHERE `id` = ?');
            $user->execute([$assign['user_id']]);
            $user = $user->fetch(PDO::FETCH_ASSOC);

            if ($_GET['vector'] == 1) {
                $Vectoruser = $conn->prepare('SELECT first_name , last_name FROM `users` WHERE `id` = ?');
                $Vectoruser->execute([$assignVector['user_id']]);
                $Vectoruser = $Vectoruser->fetch(PDO::FETCH_ASSOC);
            }

            $t_id = base64_encode($task['task_id']);
            $p_id = base64_encode($task['project_id']);

            http_response_code(200);
            echo '
            <tr>
                <td>'.++$i.'</td>
                <th class="' . $task['task_id'] . '">' . $task['task_id'] . '</th>
                <td>' . $task['area_sqkm'] . ' </td>
                <td>
                    <span class="close ' . $task['status'] . '" style="background-color :' . ($task['status'] == 'pending' ? 'rgb(248 170 170)' : 'rgb(233 248 170)') . '">
                        ' . str_replace('_', ' ', $task['status']) . '
                    </span>
                    <span class="close" style="background-color :rgb(235 170 248)">
                        ' . $user['first_name'] . ' ' . $user['last_name'] . '
                    </span>
                </td>';

            if ($_GET['vector'] == 1) {
                echo '
                <td>
                    <span class="close ' . $task['status'] . '" style="background-color : ' . ($task['vector_status'] == 'pending' ? 'rgb(248 170 170)' : 'rgb(233 248 170)') . '">
                        ' . str_replace('_', ' ', $task['vector_status']) . '
                    </span>
                    <span class="close" style="background-color :rgb(235 170 248)">
                        ' . $Vectoruser['first_name'] . ' ' . $Vectoruser['last_name'] . '
                    </span>
                </td>';
            }

            echo '
                <td style="display: flex;justify-content: space-between;">
                    <a href="task-details.php?task_id=' . $t_id . '&project_id=' . $p_id . '"><i style="color: black;" class="fas fa-info-circle"></i></a> ';

            if ($_GET['access'] == 0) {
                echo '
                    <a href="create-task.php?edit=' . $t_id . '"><i style="color: #0001fa;" class="fas fa-edit"></i></a>
                    <a onclick="deleteTask(\'' . $task['task_id'] . '\',' . $task['project_id'] . ')"><i style="color: #dd0000;" class="fas fa-trash"></i></a>';
            }
            echo '
                </td>
            </tr>';
        }
    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Task Id & Project Id not Found', "status" => 404));
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['type'] === 'getDataTableComplete') {
    if (!empty($_GET['project_id'])) {
        $i = 0;

        if ($_GET['vector'] == 1) {
            $sql2 = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id` = ? AND `status` = 'complete' AND `vector_status` = 'complete' ORDER BY `updated_at` DESC");
        } else {
            $sql2 = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id` = ? AND `status` = 'complete' ORDER BY `updated_at` DESC");
        }

        $sql2->execute([$_GET['project_id']]);
        $tasks = $sql2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tasks as $task) {
            $assign = $conn->prepare("SELECT `user_id` FROM `assign` WHERE `isActive` = 1 AND `project_id` = ? AND `role` != 'vector' AND `task_id` = ? AND `status` = 'assign'");
            $assign->execute([$task['project_id'], $task['task_id']]);
            $assign = $assign->fetch(PDO::FETCH_ASSOC);

            $assignVector = $conn->prepare("SELECT `user_id` FROM `assign` WHERE `isActive` = 1 AND `project_id` = ? AND `role` = 'vector' AND `task_id` = ? AND `status` = 'assign'");
            $assignVector->execute([$task['project_id'], $task['task_id']]);
            $assignVector = $assignVector->fetch(PDO::FETCH_ASSOC);

            $user = $conn->prepare('SELECT first_name , last_name FROM `users` WHERE `id` = ?');
            $user->execute([$assign['user_id']]);
            $user = $user->fetch(PDO::FETCH_ASSOC);

            $Vectoruser = $conn->prepare('SELECT first_name , last_name FROM `users` WHERE `id` = ?');
            $Vectoruser->execute([$assignVector['user_id']]);
            $Vectoruser = $Vectoruser->fetch(PDO::FETCH_ASSOC);

            $t_id = base64_encode($task['task_id']);
            $p_id = base64_encode($task['project_id']);

            http_response_code(200);
            echo '
            <tr>
                <td>'.++$i.'</td>
                <th class="' . $task['task_id'] . '">' . $task['task_id'] . '</th>
                <td>' . $task['area_sqkm'] . ' </td>
                <td>
                    <span class="close ' . $task['status'] . '" style="background-color :' . ($task['status'] == 'pending' ? 'rgb(248 170 170)' : 'rgb(233 248 170)') . '">
                        ' . str_replace('_', ' ', $task['status']) . '
                    </span>
                    <span class="close" style="background-color :rgb(235 170 248)">
                        ' . $user['first_name'] . ' ' . $user['last_name'] . '
                    </span>
                </td>';

            if ($_GET['vector'] == 1) {
                echo '
                <td>
                    <span class="close ' . $task['status'] . '" style="background-color : ' . ($task['vector_status'] == 'pending' ? 'rgb(248 170 170)' : 'rgb(233 248 170)') . '">
                        ' . str_replace('_', ' ', $task['vector_status']) . '
                    </span>
                    <span class="close" style="background-color :rgb(235 170 248)">
                        ' . $Vectoruser['first_name'] . ' ' . $Vectoruser['last_name'] . '
                    </span>
                </td>';
            }

            echo '
                <td style="display: flex;justify-content: space-between;">
                    <a href="task-details.php?task_id=' . $t_id . '&project_id=' . $p_id . '"><i style="color: black;" class="fas fa-info-circle"></i></a> ';

            if ($_GET['access'] == 0) {
                echo '
                    <a href="create-task.php?edit=' . $t_id . '"><i style="color: #0001fa;" class="fas fa-edit"></i></a>
                    <a onclick="deleteTask(\'' . $task['task_id'] . '\',' . $task['project_id'] . ')"><i style="color: #dd0000;" class="fas fa-trash"></i></a>';
            }
            echo '
                </td>
            </tr>';
        }
    } else {
        http_response_code(404);
        echo json_encode(array("message" => 'Task Id & Project Id not Found', "status" => 404));
    }
}
