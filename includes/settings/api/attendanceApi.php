<?php

    include "../config/config.php";
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    session_start();
    $user_id = $_SESSION['userId'];


    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'clockOut')) {
        $currentTime = new DateTime();
        if ($currentTime->format('H:i:s') < '06:00:00') {
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $sql = $conn->prepare('SELECT * FROM `attendance` WHERE date = ? AND `user_id` = ?');
            $sql->execute([$yesterday, $user_id]);
            $result = $sql->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                if ($result['clock_out_time']) {
                    http_response_code(404);
                    echo json_encode(array("message" => 'already clockout', "status" => 404));
                    exit;
                } else {
                    $sql = $conn->prepare('SELECT * FROM `work_log` WHERE user_id = ? ORDER BY `work_log`.`id` DESC;');
                    $sql->execute([$user_id]);
                    $sql = $sql->fetch(PDO::FETCH_ASSOC);
                    if($sql){
    
                        if($sql['next_status'] == 'pro_in_progress' || $sql['next_status'] == 'qc_in_progress' || $sql['next_status'] == 'qa_in_progress' || $sql['next_status'] == 'vector_in_progress'){
                            http_response_code(500);
                            echo json_encode(array("message" => 'Pls Pause or Complete Task Brefore LogOut.', "status" => 404));
                            exit;
                        }
    
                    }

                    $TclockInTime = new DateTime($result['clock_in_time']);
                    $currentTime = new DateTime();
                    if ($currentTime->format('H:i:s') < '06:00:00') {
                        $currentTime->modify('-1 day');
                    }

                    $timeDifference = $currentTime->diff($TclockInTime);

                    $timeDifferenceHours = $timeDifference->h + ($timeDifference->days * 24);
                    if ($timeDifferenceHours < 5) {
                        $not_allowed = 1;
                    } else {
                        $not_allowed = 0;
                    }

                    $sql = $conn->prepare('UPDATE attendance SET clock_out_time = CURRENT_TIMESTAMP , `not_allowed` = ? WHERE `date` = ? AND `user_id` = ?');
                    $sql->execute([$not_allowed ,$yesterday , $user_id]);
                    http_response_code(200);
                    echo json_encode(array("message" => 'clockOut successful', "status" => 404));
                }
    
            } else {
                http_response_code(404);
                echo json_encode(array("message" => 'clockin first', "status" => 404));
            }

        }else{
            $sql = $conn->prepare('SELECT * FROM `attendance` WHERE date= CURDATE() AND `user_id`= ?');
            $sql->execute([$user_id]);
            $result = $sql->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                if ($result['clock_out_time']) {
                    http_response_code(404);
                    echo json_encode(array("message" => 'already clockout', "status" => 404));
                    exit;
                } else {
                    $sql = $conn->prepare('SELECT * FROM `work_log` WHERE user_id = ? ORDER BY `work_log`.`id` DESC;');
                    $sql->execute([$user_id]);
                    $sql = $sql->fetch(PDO::FETCH_ASSOC);
                    if($sql){
    
                        if($sql['next_status'] == 'pro_in_progress' || $sql['next_status'] == 'qc_in_progress' || $sql['next_status'] == 'qa_in_progress' || $sql['next_status'] == 'vector_in_progress'){
                            http_response_code(500);
                            echo json_encode(array("message" => 'Pls Pause or Complete Task Brefore LogOut.', "status" => 404));
                            exit;
                        }
    
                    }
                    
                    $TclockInTime = strtotime($result['clock_in_time']);
                    $currentTime = new DateTime();
                    $TclockOutTime = $currentTime->format('Y-m-d H:i:s');
                    $timeDifferenceSeconds = $TclockOutTime - $TclockInTime;
                    $timeDifferenceHours = $timeDifferenceSeconds / 3600;
                    if($timeDifferenceHours < 5){
                        $not_allowed = 1;
                    }else{
                        $not_allowed = 0;
                    }
                    $sql = $conn->prepare('UPDATE attendance SET clock_out_time = CURRENT_TIMESTAMP , `not_allowed` = ? WHERE `date` = CURDATE() AND `user_id` = ?');
                    $sql->execute([$not_allowed , $user_id]);
                    http_response_code(200);
                    echo json_encode(array("message" => 'clockOut successful', "status" => 404));
                }
    
            } else {
                http_response_code(404);
                echo json_encode(array("message" => 'clockin first', "status" => 404));
            }
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'clockIn')) {
        $sql = $conn->prepare("SELECT * FROM `attendance` WHERE `date` = ? AND `user_id` = ?");
        $sql->execute([$user_id]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result) {

            http_response_code(404);
            echo json_encode(array("message" => 'already clockin', "status" => 404));
            exit;

        } else {
            $sql = $conn->prepare("INSERT INTO `attendance`(`user_id`) VALUES ( ? )");
            $sql->execute([$user_id]);
            http_response_code(200);
            echo json_encode(array("message" => 'clockIn successful', "status" => 404));

        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addRegularisation')) {
        if($_POST['clockout_time'] != '' && $_POST['attendance_id'] != ''){
            $sql = $conn->prepare("UPDATE `attendance` SET `clock_out_time` = ? , `remark` = ?, `regularisation` = 1 WHERE `id` = ? AND `user_id` = ?");
            $result = $sql->execute([$_POST['clockout_time'], $_POST['remark'], $_POST['attendance_id'], $user_id]);
            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'Add Regularisation successful', "status" => 200));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }
        }else{
            http_response_code(400);
            echo json_encode(array("message" => 'Add ClockOut Time.', "status" => 500));
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'approveAttendance')) {
        $sql = $conn->prepare("UPDATE `attendance` SET `regularisation` = 0 WHERE `id` = ?");
        $result = $sql->execute([$_POST['id']]);
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'Approve Regularisation successful', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getMonth')) {
        if ($_GET['startDate'] != '' && $_GET['endDate'] != '') {
            $startdate = $_GET['startDate'];
            $enddate = $_GET['endDate'];

            if ($startdate > $enddate) {
                http_response_code(400);
                echo json_encode(["message" => "First Date is always Greater then Second Date.", "status" => 400]);
                exit;
            }

            $startDateObj = new DateTime($startdate);
            $endDateObj = new DateTime($enddate);
            $currentDateObj = $startDateObj;
            $attendanceArray = [];

            $users = $conn->prepare('SELECT * FROM `users` ORDER BY `id` DESC');
            $users->execute();
            $users = $users->fetchAll(PDO::FETCH_ASSOC);
            foreach ($users as $user) {
                $data = [];
                $currentDateObj = new DateTime($startdate);
                
                $date = [];
                $date[] = 'Date';
                $data[] = $user['first_name'].' '.$user['last_name'];
                while ($currentDateObj <= $endDateObj) {
                    $currentDate = $currentDateObj->format('Y-m-d');

                    $date[] = $currentDate;

                    $attendances = $conn->prepare("SELECT * FROM `attendance` WHERE `user_id` = ? AND `date` = ?");
                    $attendances->execute([$user['id'], $currentDate]);
                    $attendance = $attendances->fetch(PDO::FETCH_ASSOC);
            
                    if ($attendance) {
                        // $data[] = '1';
                        if($attendance['clock_in_time'] != '' && $attendance['clock_out_time'] != ''){
                            $data[] = date('h:i A', strtotime($attendance['clock_in_time'])).' - '.date('h:i A', strtotime($attendance['clock_out_time']));
                        }else{
                            $data[] = date('h:i A', strtotime($attendance['clock_in_time'])).' - ';
                        }
                    } else {
                        $holiday = $conn->prepare("SELECT * FROM `holiday` WHERE `date` = ?");
                        $holiday->execute([$currentDate]);
                        $holiday = $holiday->fetch(PDO::FETCH_ASSOC);
            
                        if ($holiday) {
                            $data[] = 'holiday';
                        } else {
                            $leave = $conn->prepare("SELECT * FROM `leaves` WHERE `form_date` <= ? AND `end_date` >= ? AND `user_id` = ? AND `status` = 'approve'");
                            $leave->execute([$currentDate, $currentDate, $user['id']]);
                            $leave = $leave->fetch(PDO::FETCH_ASSOC);
            
                            if ($leave) {
                                $data[] = 'leave';
                            } else {
                                if (date("w", strtotime($currentDate)) == 0) {
                                    $data[] = 'week off';
                                }else{
                                    $data[] = '0';
                                }
                            }
                        }
                    }
            
                    $currentDateObj->modify('+1 day');
                }
                $attendanceArray['attendance'][] = $data;
            }
            $attendanceArray['date'] = $date;
            echo json_encode($attendanceArray);

        } else {
            http_response_code(400);
            echo json_encode(["message" => "Start Date and End Date is required.", "status" => 400]);
        }
    }

?>