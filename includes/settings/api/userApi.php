<?php

include "../config/config.php";
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
session_start();
$user_id = $_SESSION['user_id'];

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "saveUserData")) {

    if ($_POST['phone'] != '' && $_POST['employee_id'] != '' && $_POST['first_name'] != '' && $_POST['last_name'] != '' && $_POST['role_id'] != '') {

        $phone = $_POST['phone'];
        $employee_id = $_POST['employee_id'];

        $check = $conn->prepare("SELECT * FROM `users` WHERE phone = ? OR employee_id = ?");
        $check->execute([$phone, $employee_id]);
        $check = $check->fetch(PDO::FETCH_ASSOC);
        if (!$check){
            $password = password_hash('EOM@123', PASSWORD_DEFAULT);
            $user = array($_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['employee_id'], $_POST['role_id'], $_POST['dob'] , $_POST['address'], $password);
    
            $sql = $conn->prepare("INSERT INTO `users`(`first_name`, `last_name`, `phone`, `employee_id`, `role_id`, `dob` , `address`, `password`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )");
            $result = $sql->execute($user);
            if ($result) {
                http_response_code(200);
                echo json_encode(array('message' => 'Successfully Add Employee', 'status' => 200));
                exit;
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Something Went Wrong', 'status' => 500));
                exit;
            }
        }else{
            http_response_code(400);
            echo json_encode(['message' => 'User Already exist. Please Check Employee Id & Phone no.' , "status" => 400]);
            exit;
        }

    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Fill All Required Fields.', "status" => 400]);
        exit;
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "resetPassword")) {

    if($_POST['user_id'] != ''){

        $check = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $check->execute([$_POST['user_id']]);
        $user = $check->fetch(PDO::FETCH_ASSOC);
        if($user){
            $hashedPassword = password_hash('EOM@123', PASSWORD_DEFAULT);
            $sql = $conn->prepare("UPDATE `users` SET `password`=  ? WHERE `id` = ?");
            $result = $sql->execute([$hashedPassword,$_POST["user_id"]]);
            if ($result) {
                http_response_code(200);
                echo json_encode(array('message' => 'Successfull Change Password', 'status' => 500));
                exit;
            }
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Something Went Wrong', 'status' => 500));
            exit;
        }
    }else {
        http_response_code(400);
        echo json_encode(['message' => 'User Id is required.' , "status" => 400]);
        exit;
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "terminateUser")) {

    if($_POST['user_id'] != ''){

        $check = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $check->execute([$_POST['user_id']]);
        $user = $check->fetch(PDO::FETCH_ASSOC);
        if($user){
            $sql = $conn->prepare("UPDATE `users` SET `is_terminated`=  1 WHERE `id` = ?");
            $result = $sql->execute([$_POST["user_id"]]);
            if ($result) {
                http_response_code(200);
                echo json_encode(array('message' => 'Successfull Terminate', 'status' => 500));
                exit;
            }
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Something Went Wrong', 'status' => 500));
            exit;
        }
    }else {
        http_response_code(400);
        echo json_encode(['message' => 'User Id is required.' , "status" => 400]);
        exit;
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "reJoinUser")) {

    if($_POST['user_id'] != ''){

        $check = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $check->execute([$_POST['user_id']]);
        $user = $check->fetch(PDO::FETCH_ASSOC);
        if($user){
            $sql = $conn->prepare("UPDATE `users` SET `is_terminated`=  0 WHERE `id` = ?");
            $result = $sql->execute([$_POST["user_id"]]);
            if ($result) {
                http_response_code(200);
                echo json_encode(array('message' => 'Successfull Rejoin', 'status' => 500));
                exit;
            }
        } else {
            http_response_code(500);
            echo json_encode(array('message' => 'Something Went Wrong', 'status' => 500));
            exit;
        }
    }else {
        http_response_code(400);
        echo json_encode(['message' => 'User Id is required.' , "status" => 400]);
        exit;
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "updateUserData")) {

    if ($_POST['phone'] != '' && $_POST['id'] != '' && $_POST['employee_id'] != '' && $_POST['first_name'] != '' && $_POST['last_name'] != '' && $_POST['role_id'] != '') {

        $id = $_POST['id'];
        $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = $id");
        $user->execute();
        $user = $user->fetch(PDO::FETCH_ASSOC);
        if($user){
            if($_FILES['profile']['name'] != ''){
                unlink('../../../images/users/' . $user['profile']);
                $image = basename($_FILES['profile']['name']);
                $sql = $conn->prepare("UPDATE `users` SET `profile`= ? WHERE `id` = ?");
                $result = $sql->execute([$image,$_POST['id']]);
                $uploadPath = '../../../images/users/' . $image;
                move_uploaded_file($_FILES['profile']['tmp_name'], $uploadPath);
            }else{
                $image = '';
            }

            $user = array($_POST['first_name'], $_POST['last_name'] , $_POST['dob'] , $_POST['employee_id'] , $_POST['phone'],$_POST['address'] ,  $_POST['role_id'] ,   $image , $id);

            $sql = $conn->prepare("UPDATE `users` SET `first_name`= ? , `last_name` = ? ,`dob` = ? ,`employee_id` = ? ,`phone` = ? , `address` = ?, `role_id` = ? , `profile` = ? WHERE `id` = ?");
            $result = $sql->execute($user);
            if ($result) {
                http_response_code(200);
                echo json_encode(array('message' => 'Successfull Change Password', 'status' => 500));
                exit;
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Something Went Wrong', 'status' => 500));
                exit;
            }
        }else{
            http_response_code(400);
            echo json_encode(array('message' => 'User not found!', 'status' => 500));
            exit;
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "changePassword")) {

    if($_POST['old_password'] != '' && $_POST['password'] != '' && $_POST['cpassword'] != ''){
        if($_POST['password'] == $_POST['cpassword']){
            $check = $conn->prepare('SELECT * FROM `users` WHERE `id` = ?');
            $check->execute([$_SESSION['userId']]);
            $result = $check->fetch(PDO::FETCH_ASSOC);
            if($result){
                if(password_verify($_POST['old_password'], $result['password'])){
                    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql = $conn->prepare('UPDATE `users` SET `password`= ? WHERE `id` = ?');
                    $result = $sql->execute([$hashedPassword , $_SESSION['userId']]);
                    if ($result) {
                        http_response_code(200);
                        echo json_encode(array('message' => 'Password is Changed.', 'status' => 200));
                        exit;
                    } else {
                        http_response_code(500);
                        echo json_encode(array('message' => 'Something Went Wrong.', 'status' => 500));
                        exit;
                    }
                }else{
                    http_response_code(400);
                    echo json_encode(array('message' => 'Old Password is incorrect!', 'status' => 400));
                    exit;
                }
            }else{
                http_response_code(400);
                echo json_encode(array('message' => 'User is not found!', 'status' => 400));
                exit;
            }
        }else{
            http_response_code(400);
            echo json_encode(array('message' => 'Password And Confirm Password is not matched!', 'status' => 400));
            exit;
        }
    }else{
        http_response_code(400);
        echo json_encode(array('message' => 'Fill All Required Field!', 'status' => 400));
        exit;
    }
}

if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['type'] == "updatedata")) {

    $id = $_POST['id'];
    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = $id");
    $user->execute();
    $user = $user->fetch(PDO::FETCH_ASSOC);

    if($_FILES['profile']['name'] != ''){
        unlink('../../../images/users/' . $user['profile']);
        $image = basename($_FILES['profile']['name']);
        $sql = $conn->prepare("UPDATE `users` SET `profile`= ? WHERE `id` = ?");
        $result = $sql->execute([$image,$_POST['id']]);
        $uploadPath = '../../../images/users/' . $image;
        move_uploaded_file($_FILES['profile']['tmp_name'], $uploadPath);
        $sql = $conn->prepare("UPDATE `users` SET `profile`= ? WHERE `id` = ?");
        $result = $sql->execute([$image, $id]);
        if ($result) {
            http_response_code(200);
            echo json_encode(["message" => "user update successfull" , "image" => $image]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Something Went Wrong."]);
        }
    }

    if($_POST['phone'] != ''){
        $phone = $_POST['phone'];
        $employee_id = $_POST['employee_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $user_type = $_POST['user_type'];
        $dob = $_POST['dob'];
        $address = $_POST['address'];
        $sql = $conn->prepare("UPDATE `users` SET `first_name`= :first_name , `last_name` = :last_name,`dob` = :dob,`employee_id` = :employee_id,`phone` = :phone, `address` = :address , `user_type` = :user_type WHERE `id` = :id");
        $sql->bindParam(':first_name', $first_name);
        $sql->bindParam(':last_name', $last_name);
        $sql->bindParam(':address', $address);
        $sql->bindParam(':employee_id', $employee_id);
        $sql->bindParam(':user_type', $user_type);
        $sql->bindParam(':dob', $dob);
        $sql->bindParam(':phone', $phone);
        $sql->bindParam(':id', $id);
        $result = $sql->execute();
        if ($result) {
            http_response_code(200);
            echo json_encode(["message" => "user update successfull"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Something Went Wrong."]);
        }
    }

}

?>