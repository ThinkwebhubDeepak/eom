<?php

$current_page = 'login';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $employee_id = $_POST['employee_id'];
  $userpassword = $_POST['password'];

  include 'includes/settings/config/config.php';
  $check = $conn->prepare('SELECT * FROM `users` WHERE `employee_id` = ? AND `is_terminated` = 0');
  $check->execute([$employee_id]);
  $result = $check->fetch(PDO::FETCH_ASSOC);
  if ($result) {
    if (password_verify($userpassword, $result['password'])) {
      session_start();
      $_SESSION['loggedin'] = true;
      $_SESSION['userDetails'] = $result;
      $_SESSION['userId'] = $result['id'];
      $_SESSION['roleId'] = $result['role_id'];
      setcookie('userId', $result['id'], time() + 3600 * 720);


      $check = $conn->prepare("SELECT * FROM `attendance` WHERE `date` = ?  AND `user_id` = ?");
      $check->execute([date("Y-m-d"), $result['id']]);
      $check = $check->fetch(PDO::FETCH_ASSOC);
      if (!$check) {
        $attendance = $conn->prepare("INSERT INTO `attendance`(`user_id`) VALUES ( ? )");
        $attendance->execute([$result['id']]);
      }

      header("location:index.php");
    } else {
      echo "<script>alert('Password is wrong.')</script>";
    }
  } else {
    echo "<script>alert('User not found Check the Employee Id.')</script>";
  }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login page</title>

  <link rel="shortcut icon" href="images/favic.jpg">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" integrity="sha384-b6lVK+yci+bfDmaY1u0zE8YYJt0TZxLEAFyYSLHId4xoVvsrQu3INevFKo+Xir8e" crossorigin="anonymous">
  <link rel="stylesheet" href="includes/assets/plugin/font-awesome-all.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="includes/assets/plugin/bootstrap.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .container1,
        .container2 {
            width: 50%;
        }

        .container2 {
            background: #011f5163;
            height: 100vh;
            border-radius: 40px 0 0 40px;
            display: flex;
            align-items: center;
        }

        .container2 img {
            width: 900px;
            margin-left: -90px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: -webkit-fill-available;
            padding: 10px;
            font-size: 16px;
            background: #cedeeb;
            border-radius: 7px;
            border: 0;
        }

        .form-group button {
            border-radius: 7px;
            background-color: #005396;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 20px;
        }

        .mainbox {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form {
            width: 500px;
            margin: auto;
        }
        .text{
            padding: 25px 0;
        }
        .text .main-text{
            font-size: 50px;
        }
        .text .subhead{
            font-size: 30px;
            font-weight: 400;
            margin-bottom: 20px;
        }
        .text img{
            margin-bottom: 70px;
            margin-left: -30px;
        }
    </style>
</head>

<body>

  <div class="mainbox">
    <div class="container1">
      <div class="form">
        <div class="text">
          <img src="images/logo2.png" alt="" width="500px">
          <p class="main-text m-0">Let's you sign in</p>
          <p class="subhead">Welcome to DWR</p>
        </div>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>"  method="POST">
          <div class="form-group">
            <label for="username">Employee ID</label>
            <input type="text" id="username" name="employee_id" required>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
          </div>
          <div class="form-group">
            <button type="submit">Sign In</button>
          </div>
        </form>
      </div>
    </div>
    <div class="container2">
      <img src="images/123.png" alt="">
    </div>
  </div>

  <?php include 'includes/footer.php' ?>
  <script>
    function togglePasswordVisibility() {
      var passwordInput = document.getElementById("pass");
      var eyeIcon = document.getElementById("eye-icon");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.addClass = "fa-eye";
      } else {
        passwordInput.type = "password";
        eyeIcon.addClass = " fa-eye-slash";
        eyeIcon.removeClass = "fa-eye"
      }
    }
  </script>
</body>

</html>