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
                      <td> <a class="btn btn-primary" style="margin:0 10px" href="#details_user" data-bs-toggle="modal" onclick="approveAttendance(' . $attendance['id'] . ',\''.$attendance['clock_out_time'].'\')">Approve</a></td>
                    </tr>
                  ';
            $i++;
          }

          ?>
        </tbody>
      </table>
    </div>
</main>

<div class="modal fade" id="details_user" aria-hidden="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><span id="full_name"></span> Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="approveAttendance">
          <div class="row form-row">
            <div class="col-12 col-sm-6 p-2">
              <div class="form-group">
                <label>Time</label>
                <input type="time" class="form-control" name="time" id="time" required>
                <input type="hidden" class="form-control" name="id" id="id" required>
                <input type="hidden" name="type" value="approveAttendance">
              </div>
            </div>
            <div class="col-12 col-sm-6 p-2">
            <div class="form-group">
              <label>.</label>
              <button type="submit" class="btn btn-primary w-100">Save</button>
            </div></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<?php include 'includes/footer.php' ?>
<script>

  function approveAttendance(id,time) {
    $('#id').val(id);
    $('#time').val(time);
    // $.ajax({
    //   type: 'POST',
    //   url: 'includes/settings/api/attendanceApi.php',
    //   data: {
    //     type: 'approveAttendance',
    //     id: id
    //   },
    //   dataType: 'json',
    //   success: function (response) {
    //     notyf.success(response.message);
    //     $('#row_' + id).remove();
    //   },
    //   error: function (xhr, status, error) {
    //     var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
    //     notyf.error(errorMessage);
    //   }
    // });
  }

  $('#approveAttendance').submit(function(e){
    var id = $('#id').val();
    e.preventDefault();
    var formdata = new FormData(this);
    $.ajax({
      type: 'POST',
      url: 'includes/settings/api/attendanceApi.php',
      data: formdata,
      processData: false,
      cache: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        $('#row_' + id).remove();
        location.reload();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });

  
</script>

</body>

</html>