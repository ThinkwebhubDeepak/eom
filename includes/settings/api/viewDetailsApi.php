<?php

include "../config/config.php";
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
session_start();
$user_id = $_SESSION['user_id'];

if (($_SERVER['REQUEST_METHOD'] === 'GET') && ($_GET['type'] == "viewDetails")) {
    if($_GET['user_id'] != ''){
        $sql = $conn->prepare("SELECT * FROM `userdetails` WHERE `user_id` = ?");
        $sql->execute([$_GET['user_id']]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if($result){
            http_response_code(200);
            echo json_encode($result);
        }else{
            http_response_code(400);
            echo json_encode(["message" => " Details Not found"]);
        }
    }else{
        http_response_code(400);
        echo json_encode(["message" => "user id not found"]);
    }
}


if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "addDetails")) {
    if($_POST['user_id'] != '' && $_POST['designation'] != '' && $_POST['department'] != '' && $_POST['account_no'] != ''  && $_POST['pen_card'] != ''  && $_POST['aadhar'] != '' && $_POST['bank_name'] != '' && $_POST['ifsc'] != '' && $_POST['leave_balanace'] != '' && $_POST['e_contact'] != ''){
        $sql = $conn->prepare("SELECT * FROM `userdetails` WHERE `user_id` = ?");
        $sql->execute([$_POST['user_id']]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if($_POST['salary'] == ''){
            $_POST['salary'] =  $result['salary'];
        }
        
        if($result){
            $addDetails = $conn->prepare("UPDATE `userdetails` SET `bank_name` = ? ,`account_name` = ? ,`ifsc_code` = ?,`designation` = ?,`department` = ? ,`leave_balance` = ?,`salary` = ? , `pen_card` = ? , `aadhar` = ? , `e_contact` = ? WHERE `user_id` = ?");
            $addDetails = $addDetails->execute([$_POST['bank_name'] , $_POST['account_no'] ,$_POST['ifsc'] , $_POST['designation'] ,$_POST['department'] ,$_POST['leave_balanace'] ,$_POST['salary'] , $_POST['pen_card'] , $_POST['aadhar'],$_POST['e_contact'] , $_POST['user_id']]);
            if($addDetails){
                http_response_code(200);
                echo json_encode(["message" => "Update Successfully."]);
            }else{
                http_response_code(500);
                echo json_encode(["message" => "Something Went Wrong."]);
            }
        }else{
            $addDetails = $conn->prepare("INSERT INTO `userdetails`(`user_id`, `bank_name`, `account_name`, `ifsc_code`, `designation`, `department`, `leave_balance`, `salary` , `pen_card` , `aadhar`) VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ?)");
            $addDetails = $addDetails->execute([$_POST['user_id'], $_POST['bank_name'] , $_POST['account_no'] ,$_POST['ifsc'] , $_POST['designation'] ,$_POST['department'] ,$_POST['leave_balanace'] ,$_POST['salary'] , $_POST['pen_card'] , $_POST['aadhar']]);
            if($addDetails){
                 http_response_code(200);
                 echo json_encode(["message" => "Add Successfully."]);
            }else{
                 http_response_code(500);
                 echo json_encode(["message" => "Something Went Wrong."]);
            }
        }
    }else{
        http_response_code(400);
        echo json_encode(["message" => "Fill All Required Fields."]);
    }
}