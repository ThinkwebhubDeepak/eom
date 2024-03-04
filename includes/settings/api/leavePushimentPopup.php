<?php

    include "../config/config.php";
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    date_default_timezone_set('Asia/Kolkata');
    $currentDate = date('Y-m-d');
    $currentDateTime = date('Y-m-d H:i:s');


    function isPresent($conn , $user_id){
        $attendance = $conn->prepare("SELECT * FROM `attendance` WHERE DATE(`date`) = CURDATE() AND `user_id` = ?");
        $attendance->execute([$user_id]);
        $attendance = $attendance->fetch(PDO::FETCH_ASSOC);
        if($attendance){
            return true;
        }else{
            return false;
        }
    }
    
    function isHoliday($conn){
        $holiday = $conn->prepare("SELECT * FROM `holiday` WHERE DATE(`date`) = CURDATE()");
        $holiday->execute();
        $holiday = $holiday->fetch(PDO::FETCH_ASSOC);
        if($holiday){
            return true;
        }else{
            return false;
        }
    }

    function inLeave($conn , $user_id){
        $leave = $conn->prepare("SELECT * FROM `leaves` WHERE CURDATE() >= `form_date` AND CURDATE() <= `end_date` AND `user_id` = ? AND `status` = 'approve'");
        $leave->execute([$user_id]);
        $leave = $leave->fetch(PDO::FETCH_ASSOC);
        if($leave){
            return true;
        }else{
            return false;
        }
    }


    // add pushiment ---------------------------------------------------------------------------------

    // get all non terminate user
    $users = $conn->prepare("SELECT * FROM `users` WHERE is_terminated = 0;");
    $users->execute();
    $users = $users->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        // check today is user present or not
        if(!(isPresent($conn , $user['id']))){
            // check today is holiday or not
            if(!(isHoliday($conn))){
                // check user is in leave or not
                if(!(inLeave($conn , $user['id']))){
                    // check Today is Sunday or not
                    if(date('w') != 0){
                        $getPushiment = $conn->prepare("SELECT * FROM `leave-pushiment` WHERE `user_id` = ? AND `is_pushiment` = 0");
                        $getPushiment->execute([$user['id']]);
                        $getPushiment = $getPushiment->fetch(PDO::FETCH_ASSOC);
                        if($getPushiment){
                            $leave = json_decode($getPushiment['leave_date'],true);
                            $leave[] = $currentDate;
                            $updatePushiment = $conn->prepare("UPDATE `leave-pushiment` SET `leave_date` = ? , `updated_at` = ? WHERE `id` = ? ");
                            $updatePushiment->execute([json_encode($leave) , $currentDateTime ,$getPushiment['id']]);
                        }else{
                            $leave = [$currentDate];
                            $addPushiment = $conn->prepare("INSERT INTO `leave-pushiment`(`user_id` , `leave_date`) VALUES (? , ?)");
                            $addPushiment->execute([$user['id'] , json_encode($leave)]);
                        }

                    }
                }
            }

        }
        
    }

    // take action ---------------------------------------------------------------------------------
    foreach ($users as $user) {
        $number = 0;
        $getPushiments = $conn->prepare("SELECT * FROM `leave-pushiment` WHERE DATE(updated_at) <= DATE_SUB(NOW(), INTERVAL 2 DAY) AND `user_id` = ? AND `is_pushiment` = 0 ");
        $getPushiments->execute($user['id']);
        $getPushiments = $getPushiments->fetchAll(PDO::FETCH_ASSOC);
        foreach ($getPushiments as $value) {
            $leaveDate = json_decode($value['leave_date'],true);

            foreach ($leaveDate as $date) {
                $leave = $conn->prepare("SELECT * FROM `leaves` WHERE ? >= `form_date` AND ? <= `end_date` AND `user_id` = ?");
                $leave->execute([$date , $date ,$value['user_id']]);
                $leave = $leave->fetch(PDO::FETCH_ASSOC);
                if(!$leave){
                    $number++;
                }else{
                    $remark = $value['remark'].' - '. $date;
                    $updatePushiment = $conn->prepare("UPDATE `leave-pushiment` SET `remark` = ?  WHERE `id` = ? ");
                    $updatePushiment->execute([$remark , $value['id']]);
                }
            }

            $number = $number*2;
            $getAttandance = $conn->prepare("SELECT * FROM `attendance` WHERE `user_id` = ? ORDER BY `date` DESC LIMIT $number");
            $getAttandance->execute([$user['id']]);
            $getAttandance = $getAttandance->fetchAll(PDO::FETCH_ASSOC);
            $pushiment = [];
            foreach ($getAttandance as $attandance) {
                $pushiment[] = $attandance['date'];
                $updateAttandance = $conn->prepare("UPDATE `attendance` SET `not_allowed` = 1 , `updated_at` = ? WHERE `id` = ? ");
                $updateAttandance->execute([$currentDateTime , $attandance['id']]);
            }
            $updatePushiment = $conn->prepare("UPDATE `leave-pushiment` SET `pushiment` = ? , `is_pushiment` = 1 WHERE `id` = ? ");
            $updatePushiment->execute([json_encode($pushiment) , $value['id']]);
        } 



    }

?>