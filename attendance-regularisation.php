<?php
  $page_name = 'attendance-regularisation';
  $title = 'Attendance Employee || EOM ';
  include "includes/header.php";
  if($roleId != 1 && !(in_array($page_name, $pageAccessList))){
    echo '<script>window.location.href = "index.php"</script>';
  }   
  $currentDate = date('Y-m-d');
  $attendances = $conn->prepare("SELECT * FROM `attendance` WHERE `regularisation` = 1 ORDER BY `created_at` DESC");
  $attendances->execute();
  $attendances = $attendances->fetchAll(PDO::FETCH_ASSOC);

?>


<main style="margin-top: 100px;">
  <div class="container-flude pt-5">
    <div class="container-flude px-5">
      <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
        <p class="fw-bold page_heading">Attendance Regularisation</p>
       
      </div>
      <table id="dataTable" class="display">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">Role</th>
            <th scope="col">Date</th>
            <th scope="col">Time</th>
            <th scope="col">Remarks</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          foreach ($attendances as $attendance) {
            $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
            $user->execute([$attendance['user_id']]);
            $user = $user->fetch(PDO::FETCH_ASSOC);

            $role = $conn->prepare("SELECT * FROM `role` WHERE `id` = ?");
            $role->execute([$user['role_id']]);
            $role = $role->fetch(PDO::FETCH_ASSOC);

            echo '
                    <tr id="row_' . $attendance['id'] . '">
                      <th scope="row">' . $i . '</th>
                      <td>' . $user['first_name'] . '</td>
                      <td>' . $user['last_name'] . '</</td>
                      <td>' . strtoupper($role['role']). '</</td>
                      <td>' . date("d M Y",strtotime($attendance['date']))  . '</td>
                      <td>' . date("h:i A", strtotime($attendance['clock_in_time'])) . '<br><span style="color:red">' . date("h:i A", strtotime($attendance['clock_out_time'])) . '</span></td>
                      <td>' . $attendance['remark'] . '</td>
                      <td> <a class="btn btn-primary" style="margin:0 10px" onclick="approveAttendance(' . $attendance['id'] . ')">Approve</a></td>
                    </tr>
                  ';
            $i++;
          }

          ?>
        </tbody>
      </table>
    </div>
</main>


<?php include 'includes/footer.php' ?>
<script>

  function approveAttendance(id) {
    $.ajax({
      type: 'POST',
      url: 'includes/settings/api/attendanceApi.php',
      data: {
        type: 'approveAttendance',
        id: id
      },
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        $('#row_' + id).remove();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  }

  
</script>

</body>

</html>