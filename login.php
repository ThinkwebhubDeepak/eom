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
  <link rel="stylesheet" href="css/test.css">
  <style>
    *{
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    main {
      padding: 0;
      background: #ffffff;
      color: white;
      height: 100vh !important;
      /* background: #000; */
      color: #fff;
    }

    .container-fluid {
      background-color: #ffffff;
      height: 100vh;
      margin: 0;
      padding: 0;
    
    }

    .row {
      align-items: center;
    }
   .togglePasswordVisibility{
    position: relative;
    /* margin: 20px; */
}

     .togglePasswordVisibility .toggle-password {
        width: 100%;
        height: 100%;
          position: absolute;
          left: 91%;
          top: 105%;
          transform: translateY(-50%);
          cursor: pointer;
      }
     #eye-icon {
  
          width: 20px; /* Adjust the size of the eye icon */
        
      }
      .Eom_text{
        width: 100%;
        margin-top: 40px;
        font-weight: 400;
        font-size: 50px;
      }
      .log-text{
        font-weight: 500;
        font-size: 35px;
        margin-bottom: 20px;
        color: #fff;
      }
   
  </style>
</head>

<body>

  <main>
    <div class="container-flude">
      <div class="Login_page ">
        
          <div class="container-fluid">
            
            
            <div class="row " style="height: 100vh;">
            
                            
                           <div class="col-sm-6 text-black p-3">
                                <div class="d-flex align-items-center justify-content-center;" style="justify-content: center;">
                                            
                                  <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>" style="width: 80%; ">
                                  <!-- <img src="images/favic.jpg" alt="" class="m-auto" style="height:100px; width:100px;"> -->
                                  <p class=" fw-bold">Welcome to</p>
                                  <h5 class=" text-black  log-text  fw-bold" > Earth On Mapping (DWR)</h5>
                                    <!-- <h3 class="log-text mnterb-2 pb-1" style="letter-spacing: 1px;">Log In</h3> -->

                                    <div class="form-outline mb-2 form-group">
                                      <label class="form-label " for="employee_id">Employee Id</label>
                                      <input type="text" id="employee_id" name="employee_id" class="employee_id form-control form-control-md p-2" required />

                                    </div>

                                    <div class="form-outline mb-4 form-group togglePasswordVisibility">
                                      <label class="form-label" for="pass">Password</label>
                                      <input type="password" id="pass" name="password" class="pass form-control form-control-md p-2" required />
                                      <span class="toggle-password" onclick="togglePasswordVisibility()">
                                      <i class="fa-solid fa-eye"  id="eye-icon"></i>
                                      </span>
                                    </div>

                                    <div class="pt-1 mb-2 form-group">
                                      <button class="btn  w-100 p-2" style="color:white;font-weight:700; background-color: #11b3e8;" type="submit"><i class="fa-solid fa-right-to-bracket me-2"></i>Login</button>
                                    </div>
                                    <p id="wm" style="display: none; color: red;"></p>
                                    <!-- <p class="small mb-3 pb-lg-2"><a class="text-muted" href="#!">Forgot password?</a></p> -->
                                    <!-- <p>Don't have an account? <a href="register.php" class="link-info">Register here</a></p> -->

                                  </form>

                                </div>

                           </div>
                            <div class="col-sm-6 align-items-center justify-content-center;" style="justify-content: center;">
                              <img src="images/login_img.jpg" alt="Login image" style="object-fit: cover; object-position: left; height:100vh;  width: 100%; ">
                            </div>
            </div>
          </div>
      
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php' ?>
  <script>
   function togglePasswordVisibility() {
    var passwordInput = document.getElementById("pass");
    var eyeIcon = document.getElementById("eye-icon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.addClass= "fa-eye";
    } else {
        passwordInput.type = "password";
        eyeIcon.addClass = " fa-eye-slash";
        eyeIcon.removeClass= "fa-eye" 
    }
}
</script>
</body>

</html>