<?php

    include "../config/config.php";
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    session_start();
    $user_id = $_SESSION['userId'];

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addLeave')) {

        if (($_POST['form_date'] != '') && ($_POST['end_date'] != '') && ($_POST['formdate_session'] != '') && ($_POST['enddate_session'] != '') && ($_POST['contact_detail'] != '') && ($_POST['region'] != '')) {

            if (isset($_FILES["upload"]) && $_FILES["upload"]["error"] == UPLOAD_ERR_OK) {

                $upload = basename($_FILES['upload']['name']);
                $uploadPath = '../../../upload/attachment/' . $upload;
                move_uploaded_file($_FILES['upload']['tmp_name'], $uploadPath);
            }else{
                $upload = null;
            }

            $leave = array($user_id ,$_POST['form_date'] , $_POST['end_date'] , $_POST['formdate_session'] , $_POST['enddate_session'] , $upload , $_POST['contact_detail'], $_POST['region'] );
            
            $check = $conn->prepare('INSERT INTO `leaves`( `user_id`, `form_date`, `end_date`, `formdate_session`, `enddate_session`, `upload`, `contact_detail`, `region` ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)');
            $result = $check->execute($leave);

            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'successfull Leave Added...', "status" => 200));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }
        }else {
            http_response_code(400);
            echo json_encode(array("message" => "Fill all required fields", "status" => 400));
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'approveLeave')) {

        $ggetLeave = $conn->prepare("SELECT * FROM `leaves` WHERE id = ?");
        $ggetLeave->execute([$_GET['leave_id']]);
        $ggetLeave =$ggetLeave->fetch(PDO::FETCH_ASSOC);
        if($ggetLeave){
            $date1 = new DateTime($ggetLeave['form_date']);
            $date2 = new DateTime($ggetLeave['end_date']);

            $interval = $date1->diff($date2);
            $daysDifference = $interval->days + 1;

            $balance = $conn->prepare("SELECT * FROM `userdetails` WHERE user_id = ?");
            $balance->execute([$ggetLeave['user_id']]);
            $balance =$balance->fetch(PDO::FETCH_ASSOC);
            $use = 0;
            if(intval($balance['leave_balance']) <= $daysDifference){
                $use = $balance['leave_balance'];
                $ucheck = $conn->prepare("UPDATE `userdetails` SET `leave_balance` = 0 WHERE user_id = ? ");
                $uresult = $ucheck->execute([$ggetLeave['user_id']]);
            }else{
                $use = $daysDifference;
                $ucheck = $conn->prepare("UPDATE `userdetails` SET `leave_balance` = ? WHERE user_id = ? ");
                $uresult = $ucheck->execute([intval($balance['leave_balance']) - $daysDifference ,$ggetLeave['user_id']]);
            }



            $check = $conn->prepare("UPDATE `leaves` SET `status` = 'approve' , `approved_by` = ? , `use` = ? WHERE id = ? ");
            $result = $check->execute([$user_id , $use ,$_GET['leave_id']]);
        
            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'successfull Leave Status Change.', "status" => 200));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }
        }else{
            http_response_code(500);
            echo json_encode(array("message" => 'No leave found', "status" => 500));
        }
    
        
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'cancelLaves')) {
        
         
        $check = $conn->prepare("UPDATE `leaves` SET `status` = 'cancel' , `approved_by` = ? WHERE id = ? ");
        $result = $check->execute([$user_id ,$_GET['leave_id']]);
    
        if ($result) {
            http_response_code(200);
            echo json_encode(array("message" => 'successfull Leave Status Change...', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
        
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addLeaveBonus')) {   
        $holiday = $conn->prepare("SELECT * FROM `holiday` WHERE `date` = CURRENT_DATE()");
        $holiday->execute();
        $holiday = $holiday->fetch(PDO::FETCH_ASSOC);
        if(date('N') == 7 || $holiday)  {
            $check = $conn->prepare('INSERT INTO `leave_bonus`(`user_id`) VALUES ( ? )');
            $result = $check->execute([$user_id ]);

            if ($result) {
                http_response_code(200);
                echo json_encode(array("message" => 'successfull Leave Bonus Added.', "status" => 200));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => 'Something went wrong', "status" => 500));
            }
        }else{
            http_response_code(500);
            echo json_encode(array("message" => 'Today is not Holiday', "status" => 500));
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'approveLeaveBonus')) {   

        $check = $conn->prepare('SELECT * FROM `leave_bonus` WHERE `id` = ? AND `aprrove` = 0');
        $check->execute([$_POST['leave_id'] ]);
        $check = $check->fetch(PDO::FETCH_ASSOC);
        if ($check) {
            $updatecheck = $conn->prepare('UPDATE `leave_bonus` SET `aprrove` = 1 , `approve_by` = ? WHERE `id` = ?');
            $result = $updatecheck->execute([$user_id , $_POST['leave_id'] ]);

            $updateLeave = $conn->prepare('UPDATE `userdetails` SET `leave_balance` = leave_balance + 1 WHERE `user_id` = ?');
            $updateresult = $updateLeave->execute([$check['user_id'] ]);

            http_response_code(200);
            echo json_encode(array("message" => 'successfull Leave Bonus Approve.', "status" => 200));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => 'Something went wrong', "status" => 500));
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'GET') && ($_GET['type'] == 'getPushiment')) {
        $sql = $conn->prepare("SELECT * FROM `leave-pushiment` WHERE `user_id` = ? AND `is_pushiment` = 0");
        $sql->execute([$user_id]);
        $sql = $sql->fetch(PDO::FETCH_ASSOC);
        if($sql){
            http_response_code(200);
            echo $sql['leave_date'];
        }
    }

?>