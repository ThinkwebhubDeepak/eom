<?php
session_start();
include 'includes/settings/config/config.php';
$sql = $conn->prepare('SELECT * FROM `work_log` WHERE user_id = ? ORDER BY `work_log`.`id` DESC;');
$sql->execute([$_SESSION['userId']]);
$sql = $sql->fetch(PDO::FETCH_ASSOC);
if($sql){

    if($sql['next_status'] == 'pro_in_progress' || $sql['next_status'] == 'qc_in_progress' || $sql['next_status'] == 'qa_in_progress' || $sql['next_status'] == 'vector_in_progress'){
        echo "<script> alert('Pls Pause or Complete Task Brefore LogOut..') </script>";
    }else{
        session_unset();
        session_destroy();
        
        setcookie('userId',$_SESSION['userId'],time() - 3600*720);
        unset($_COOKIE['userId']);
        
        header("location: login.php");
        die();
    }

}else{
    session_unset();
    session_destroy();
    
    setcookie('userId',$_SESSION['userId'],time() - 3600*720);
    unset($_COOKIE['userId']);
    
    header("location: login.php");
    die();
}

?>