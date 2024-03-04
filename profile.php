<?php
  $current_page = 'profile';
  $title = 'Profile || EOM ';
  include 'includes/header.php';
  
  $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
  $user->execute([$userId]);
  $result = $user->fetch(PDO::FETCH_ASSOC);
  
  $role = $conn->prepare("SELECT * FROM `role` WHERE `id` = ?");
  $role->execute([$roleId ]);
  $role = $role->fetch(PDO::FETCH_ASSOC);
?>



<style>
  .container {
    background-color: #ffffff;
  }
  .togglePasswordVisibility{
    position: relative;
    /* margin: 20px; */
}

     .togglePasswordVisibility .toggle-password {
        width: 100%;
        height: 100%;
          position: absolute;
          left: 89%;
          top: 105%;
          transform: translateY(-50%);
          cursor: pointer;
      }
     #eye-icon {
  
          width: 20px; /* Adjust the size of the eye icon */
        
      }
</style>

<main style="margin-top: 100px;">
  <div class="container-flude ">
    <section style="background-color: #eee;">
      <div class="container-flude py-5 px-5">


        <div class="row">
          <div class="col-lg-4">
            <div class="card mb-4">
              <div class="card-body text-center">

                <label for="profile">
                  <?php
                  if (($result['profile'] == '') || !($result['profile'])) {
                    echo '
                        <img id="profile_image" src="images/users/default.jpg" alt="avatar"
                      class="rounded-circle img-fluid" style="width: 150px;height: 150px;object-fit: cover;">
                      ';
                  } else {
                    echo '
                      <img id="profile_image" src="images/users/' . $result['profile'] . '" alt="avatar"
                      class="rounded-circle img-fluid" style="width: 150px;height: 150px;
                      object-fit: cover;">
                      ';
                  }
                  ?>
                  <i class="fas fa-edit" style="position: absolute;"></i>
                </label>
                <input type="file" name="profile" id="profile" style="display:none" accept="image/*">
                <h5 class="my-3">
                  <?php echo $result['first_name'] . ' ' . $result['last_name'] ?>
                </h5>
              </div>
            </div>
            <div class="card col-md-12">
              <div class="card-body">
                <h3 class="d-flex" style="font-size: 20px">Change Password</h3>
                <hr>
                <form id="changePassword">
                  <div class="row form-row m-2">
                    <div class="col-12 col-sm-12">
                      <div class="form-group togglePasswordVisibility">
                        <label>Old Password</label>
                        <input type="password" class="form-control pass" name="old_password"  required>
                        <input type="hidden" class="form-control" name="type"  value="changePassword" required>
                        <span class="toggle-password" onclick="togglePasswordVisibility()">
                         <i class="fa-solid fa-eye"  id="eye-icon"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="row form-row m-2">
                    <div class="col-12 col-sm-12">
                      <div class="form-group togglePasswordVisibility">
                        <label>New Password</label> 
                        <input type="password" class="form-control pass1" name="password"  required="">
                        <span class="toggle-password" onclick="togglePasswordVisibility1()">
                      <i class="fa-solid fa-eye"  id="eye-icon1"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="row form-row m-2">
                    <div class="col-12 col-sm-12">
                      <div class="form-group togglePasswordVisibility">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control pass2" name="cpassword"  required="">
                        <span class="toggle-password" onclick="togglePasswordVisibility2()">
                      <i class="fa-solid fa-eye"  id="eye-icon"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Change Password</button>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <!-- <div class="d-flex justify-content-around">
                  <p>Designation: </p>
                  <p>Department: </p>
                  <p>EMP ID: </p>
              </div>
              <div class="d-flex justify-content-around">
                  <p>D-Name </p>
                  <p>Dept_Name </p>
                  <p>Id </p>
              </div> -->
            <div class="card mb-4 name-card">
              <div class="card-body">
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Full Name:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">
                      <?php echo $result['first_name'] . ' ' . $result['last_name'] ?>
                    </p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Phone:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">
                      <?php echo $result['phone'] ?>
                    </p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Address:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">
                      <?php echo $result['address'] ?>
                    </p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Role :</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0"><?php echo strtoupper($role['role']) ?></p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">Organization:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">Earth On Mapping</p>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-3">
                    <p class="mb-0">EMP ID:</p>
                  </div>
                  <div class="col-sm-9">
                    <p class="text-muted mb-0">#
                      <?php echo $result['employee_id'] ?>
                    </p>
                  </div>
                </div>

              </div>
            </div>
            <div class="row project">
              <!-- <div class="col-md-6">
                <div class="card mb-4 mb-md-0">
                  <div class="card-body"> -->
              <!-- <h1 class="d-flex justify-content-center">Timeline</h1>
                      <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quidem quis cumque eius provident quasi suscipit debitis sint nostrum nobis, pariatur commodi eaque eveniet numquam rerum autem distinctio ipsam voluptatum maxime.</p> -->
              <!-- <p class="mb-4"><span class="text-primary font-italic me-1">assigment</span> Project Status
                    </p>
                    <p class="mb-1" style="font-size: .77rem;">Web Design</p>
                    <div class="progress rounded" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mt-4 mb-1" style="font-size: .77rem;">Website Markup</p>
                    <div class="progress rounded" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 72%" aria-valuenow="72"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mt-4 mb-1" style="font-size: .77rem;">One Page</p>
                    <div class="progress rounded" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 89%" aria-valuenow="89"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mt-4 mb-1" style="font-size: .77rem;">Mobile Template</p>
                    <div class="progress rounded" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 55%" aria-valuenow="55"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="mt-4 mb-1" style="font-size: .77rem;">Backend API</p>
                    <div class="progress rounded mb-2" style="height: 5px;">
                      <div class="progress-bar" role="progressbar" style="width: 66%" aria-valuenow="66"
                        aria-valuemin="0" aria-valuemax="100"></div>
                    </div> -->
              <!-- </div>
                </div>
              </div> -->
              <div class="col-md-12">
                <div class="card mb-4 mb-md-0">
                  <div class="card-body">
                    <h1 class="d-flex justify-content-center">Efficiency</h1>
                    <div>
                      <canvas id="waveformChart" height="200"></canvas>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>

<?php include 'includes/footer.php' ?>

<script>

  $('#changePassword').submit(function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: "includes/settings/api/userApi.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        setTimeout(() => {
          location.reload();
        }, 1000);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });

  $('#profile').change(() => {
    const fileInput = document.getElementById("profile");
    if (fileInput.files.length > 0) {
      const file = fileInput.files[0];
      const formData = new FormData();
      formData.append("profile", file);
      formData.append("type", 'updatedata');
      formData.append("id", <?php echo $userId ?>);
      $.ajax({
        url: "includes/settings/api/userApi.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          $('#profile_image').attr("src", "images/users/" + file.name)
          location.reload();
        }
      });
    }
  });


  function togglePasswordVisibility() {
    var passwordInput = document.querySelector(".pass");
    
    var eyeIcon = document.getElementById("eye-icon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.className= "fa-solid fa-eye"; // Replace with your open eye icon
    } else {
        passwordInput.type = "password";
        eyeIcon.className = "fa-solid fa-eye-slash"; // Replace with your closed eye icon
    }

}
  function togglePasswordVisibility1() {

    var passwordInput1 = document.querySelector(".pass1");
  
    var eyeIcon = document.getElementById("eye-icon1");

    if (passwordInput1.type === "password") {
        passwordInput1.type = "text";
        eyeIcon.className= "fa-solid fa-eye"; // Replace with your open eye icon
    } else {
        passwordInput1.type = "password";
        eyeIcon.className = "fa-solid fa-eye-slash"; // Replace with your closed eye icon
    }

}
  function togglePasswordVisibility2() {
  
    var passwordInput2 = document.querySelector(".pass2");
    var eyeIcon = document.getElementById("eye-icon");

    if (passwordInput2.type === "password") {
        passwordInput2.type = "text";
        eyeIcon.className= "fa-solid fa-eye"; // Replace with your open eye icon
    } else {
        passwordInput2.type = "password";
        eyeIcon.className = "fa-solid fa-eye-slash"; // Replace with your closed eye icon
    }

}
</script>
<?php

  $sql = $conn->prepare("SELECT * FROM efficiency WHERE Date(`created_at`) >= CURDATE() - INTERVAL 1 MONTH AND `user_id` = ? ORDER BY `efficiency`.`created_at` DESC");
  $sql->execute([$userId]);
  $sql = $sql->fetchAll(PDO::FETCH_ASSOC);
  $temp = $sql[0]['created_at'];
  $i = 0;
  $arr = [];
  $sum = 0;
  // ... (your previous code)

  foreach ($sql as $value) {
    if (date('d-m-y',strtotime($temp)) == date('d-m-y',strtotime($value['created_at']))) {
      $i++;
      $sum += $value['efficiency'];
    } else {
      if ($i != 0) {
        $arr[] = $sum / $i;
      }
      $temp = $value['created_at'];
      $i = 1;
      $sum = $value['efficiency'];
    }
  }

  // Include the last calculation outside the loop
  if ($i != 0) {
    $arr[] = $sum / $i;
  }


?>
<script src="includes/assets/js/chart.js"></script>
<script>
    // Your data
    var data = <?php echo json_encode($arr) ?>;

    // Create a line chart
    var ctx = document.getElementById('waveformChart').getContext('2d');
    var waveformChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: Array.from({ length: data.length }, (_, i) => `Day ${i + 1}`),
        datasets: [{
          label: 'Waveform Data',
          data: data,
          fill: false,
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 2
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>

</body>

</html>