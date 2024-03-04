<?php 
  include "settings/config/config.php"; 
  session_start();
  if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    echo '<script>window.location.href = "login.php";</script>';
    exit;
  } else {
    $userId = $_SESSION['userId'];
    $roleId = $_SESSION['roleId'];
    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
    $user->execute([$userId]);
    $result = $user->fetch(PDO::FETCH_ASSOC);
    if($result){
        if($result['is_terminated'] == 1){
          header("location: login.php");
          echo '<script>window.location.href = "login.php";</script>';
          exit;
        }else{
          $_SESSION['userDetails'] = $result;
          $_SESSION['userId'] = $result['id'];
          $_SESSION['roleId'] = $result['role_id'];
        }
    }else{
      header("location: login.php");
      echo '<script>window.location.href = "login.php";</script>';
      exit;
    }
  }

  $pageAccessList = [];
  $role = $conn->prepare("SELECT * FROM `access` WHERE role_id = ?");
  $role->execute([$roleId]);
  $role = $role->fetch(PDO::FETCH_ASSOC);
  foreach (json_decode($role['access_page'],true) as $value) {
    $page = $conn->prepare("SELECT * FROM `pages` WHERE id = ?");
    $page->execute([$value]);
    $page = $page->fetch(PDO::FETCH_ASSOC);
    $pageAccessList[] = $page['slug'];
  }

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo ($page_name == 'index') ? 'DashBoard' : ucfirst($page_name) ?> || EOM</title>
  <link rel="shortcut icon" href="images/favic.jpg">
  <link rel="stylesheet" href="includes/assets/plugin/font-awesome-all.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="includes/assets/plugin/bootstrap.min.css"  crossorigin="anonymous">
  <link rel="stylesheet" href="includes/assets/plugin/bootstrap-icons.css" crossorigin="anonymous">
    <link rel="stylesheet" href="includes/assets/plugin/new_bootstrap.css" crossorigin="anonymous"
        referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="includes/assets/plugin/select2.css" rel="stylesheet" />
    <link rel="stylesheet" href="includes/assets/plugin/notify.css" rel="stylesheet" />
    <link rel="stylesheet" href="includes/assets/plugin/datatables.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="includes/assets/plugin/notifix.css" rel="stylesheet" />
    <!-- font family link -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@600&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/test.css">

  <?php include 'sidebar.php' ?>