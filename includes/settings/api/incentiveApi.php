<?php

    session_start();
    include '../config/config.php';
    header("content-Type: application/json");
    $user_id = $_SESSION['userId'];

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addAdvance')) {
        if($_POST['user_id'] != '' && $_POST['incentive'] != '' && $_POST['remark'] != ''){
            if(date("Y-m-d") < date("Y-m-15") || date("Y-m-d") > date("Y-m-25") ){
                http_response_code(400);
                echo json_encode(array("message" => 'Add Advance Bettween 15th and 25th', "status" => 400));
                exit();
            }
            
            $checkAdvance = $conn->prepare('SELECT * FROM `userdetails` WHERE `user_id` = ?');
            $checkAdvance->execute([$_POST['user_id']]);
            $checkAdvance = $checkAdvance->fetch(PDO::FETCH_ASSOC);
            if($checkAdvance){
                $updateAdvance = $conn->prepare("UPDATE `userdetails` SET `total_advance` = ? Where `user_id` = ?");
                $result_advance = $updateAdvance->execute([$checkAdvance['total_advance'] + $_POST['incentive'] ,$_POST['user_id']]);
            }

            $checkIncentive = $conn->prepare('SELECT * FROM `salary` WHERE `user_id` = ? AND MONTH(`month`) = ?');
            $checkIncentive->execute([$_POST['user_id'], date('m')]);
            $checkIncentive = $checkIncentive->fetch(PDO::FETCH_ASSOC);
            if(!$checkIncentive){
                $arr = [["advance" => $_POST['incentive'] , "remark" => $_POST['remark'] , "date" => date("Y-m-d")]];
                $sql = $conn->prepare('INSERT INTO `salary`(`user_id`, `salary`, `month`,  `attendance`,`leave`, `advance_salary`) VALUES (? , ? , ? , ? , ? , ? )');
                $result = $sql->execute([$_POST['user_id'], $_POST['salary'] , date("Y-m-d"), $_POST['attendance'] , $_POST['leave'] , json_encode($arr)]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(array("message" => 'Add Advance Successfull', "status" => 200, "time" => $_POST['time']));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went worrg', "status" => 500));
                }
            }else{
                $originalArr = json_decode($checkIncentive['advance_salary'] , true);
                $arr = ["advance" => $_POST['incentive'] , "remark" => $_POST['remark'] , "date" => date("Y-m-d")];
                $originalArr[] = $arr;

                $sql = $conn->prepare('UPDATE `salary` SET `advance_salary` = ? WHERE `user_id` = ? AND MONTH(`month`) = ?');
                $result = $sql->execute([json_encode($originalArr),$_POST['user_id'], date('m')]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(array("message" => 'More Add Advance Successfull', "status" => 200, "time" => $_POST['time']));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went worrg', "status" => 500));
                }
            }

        }else{
            http_response_code(404);
            echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
        }
    }
    
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['type'] == 'addIncentive')) {
        if($_POST['user_id'] != '' && $_POST['incentive'] != '' && $_POST['remark'] != ''){
            if(date("Y-m-d") < date("Y-m-15") || date("Y-m-d") > date("Y-m-25") ){
                http_response_code(400);
                echo json_encode(array("message" => 'Add Incentive Bettween 15th and 25th', "status" => 400));
                exit();
            }
            $checkIncentive = $conn->prepare('SELECT * FROM `salary` WHERE `user_id` = ? AND MONTH(`month`) = ?');
            $checkIncentive->execute([$_POST['user_id'], date('m')]);
            $checkIncentive = $checkIncentive->fetch(PDO::FETCH_ASSOC);
            if(!$checkIncentive){
                $arr = [["incentive" => $_POST['incentive'] , "remark" => $_POST['remark'] , "date" => date("Y-m-d")]];
                $sql = $conn->prepare('INSERT INTO `salary`(`user_id`, `salary`, `month`,  `attendance`,`leave`, `incentive`) VALUES (? , ? , ? , ? , ? , ? )');
                $result = $sql->execute([$_POST['user_id'], $_POST['salary'] , date("Y-m-d"), $_POST['attendance'] , $_POST['leave'] , json_encode($arr)]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(array("message" => 'Add Incentive Successfull', "status" => 200, "time" => $_POST['time']));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went worrg', "status" => 500));
                }
            }else{
                $originalArr = json_decode($checkIncentive['incentive'] , true);
                $arr = ["incentive" => $_POST['incentive'] , "remark" => $_POST['remark'] , "date" => date("Y-m-d")];
                $originalArr[] = $arr;

                $sql = $conn->prepare('UPDATE `salary` SET `incentive` = ? WHERE `user_id` = ? AND MONTH(`month`) = ?');
                $result = $sql->execute([json_encode($originalArr),$_POST['user_id'], date('m')]);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(array("message" => 'More Add Incentive Successfull', "status" => 200, "time" => $_POST['time']));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => 'Something went worrg', "status" => 500));
                }
            }

        }else{
            http_response_code(404);
            echo json_encode(array("message" => 'Fill All Required Fields', "status" => 404));
        }
    }