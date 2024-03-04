<?php
    $page_name = 'my-attendance';
    include "includes/header.php";
    $currentDate = date('Y-m-d');
    $attendances = $conn->prepare("SELECT * FROM `attendance` WHERE `user_id` = ? ORDER BY `created_at` DESC");
    $attendances->execute([$_SESSION['userId']]);
    $attendances = $attendances->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
  .late_login {
    background-color: red;
    padding: 3px 8px;
    border-radius: 5px;
  }

  .morning {
    background-color: black;
  }

  .evening {
    background-color: #ffcc00;
  }

  .general {
    background-color: #339900;
  }
</style>
<div class="modal" id="myModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add Regularisation</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
      </div>
      <form id="addRegularisation" name="upload">
        <div class="modal-body">
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Clock Out </label>
              <div class="input-group">
                <input type="hidden" value="addRegularisation" name="type">
                <input type="time" class="form-control" id="clockout_time" name="clockout_time" required>
                <input type="hidden" name="attendance_id" id="attendance_id" value="" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Remarks </label>
              <div class="input-group">
                <input type="text" class="form-control" id="remark" name="remark">
              </div>
            </div>
            <div class="input-group">
              <button class="btn btn-primary" style="margin: auto;">Add Regularisation</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<main style="margin-top: 58px;">
  <div class="container-flude pt-5">
    <div class="container-flude px-4">
      <div class="d-flex justify-content-between" style="padding: 0 0 20px 0; ">
        <p class="fw-bold page_heading text-center">Attandance</p>
      </div>

      <table id="dataTable" class="display">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Name</th>
            <th scope="col">Role</th>
            <th scope="col">Date </th>
            <th scope="col">Clock-in-time</th>
            <th scope="col">Clock-out-time</th>
            <th scope="col">Efficiency</th>
            <th scope="col">Total Time / Taken Time</th>
            <th>Ideal Time / Active Time</th>
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

             // for calculate efficiency
             $efficincy = $conn->prepare("SELECT AVG(efficiency) as totalefficiency , SUM(total_time) as totaltime , SUM(taken_time) as takentime FROM `efficiency` WHERE `user_id` = ? AND DATE(`created_at`) = ?");
             $efficincy->execute([$user['id'] , $attendance['date']]);
             $efficincy = $efficincy->fetch(PDO::FETCH_ASSOC);

            $clock_in_time = strtotime($attendance['clock_in_time']);

            if ($clock_in_time >= strtotime('5:00 AM') && $clock_in_time <= strtotime('8:00 AM')) {
              $late_login = '<br><span class="badge badge-danger late_login morning">Morning</span>';
              if ($clock_in_time >= strtotime('6:45 AM')) {
                $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
              } else {
                $late_login_status = '';
              }
            } else if ($clock_in_time >= strtotime('12:00 PM') && $clock_in_time <= strtotime('3:00 PM')) {
              $late_login = '<br><span class="badge badge-danger late_login evening">Evening</span>';
              if ($clock_in_time > strtotime('2:45 PM')) {
                $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
              } else {
                $late_login_status = '';
              }
            } else {
              $late_login = '<br><span class="badge badge-danger late_login general">General</span>';
              if ($clock_in_time >= strtotime('9:15 AM')) {
                $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
              } else {
                $late_login_status = '';
              }
            }

            if ($attendance['clock_out_time'] != '') {
              $attendance_clock_out = date('h:i A', strtotime($attendance['clock_out_time']));
              $TclockInTime = strtotime($attendance['clock_in_time']);
              $TclockOutTime = strtotime($attendance['clock_out_time']);
              $timeDifferenceSeconds = $TclockOutTime - $TclockInTime;
              $timeDifferenceHours = $timeDifferenceSeconds / 3600;
              $ideal_time = round($timeDifferenceHours - ($efficincy['takentime']/60) - ($break['break_time']/60),2);
              $ideal_hour = ' <span class="text-success">'.$ideal_time.'H </span> / <span class="text-danger"> '.round($timeDifferenceHours , 2).'H  </span> ';
              if($timeDifferenceHours > 5 && $timeDifferenceHours < 6.5){
                $half_status = '<span class="badge badge-danger late_login" style="background-color: #bd00ff;">Half Day</span>';
              }else if($timeDifferenceHours < 5 ){
                $half_status = '<span class="badge badge-danger late_login" style="background-color: black;">Absent</span>';
              }else{
                $half_status = '';
              }

            } else {
              $attendance_clock_out = '';
              $half_status = '';
              $ideal_hour = '';
            }

            if($attendance['clock_out_time'] == '' && $attendance['date'] != $currentDate){
                $regulazation = '<a onclick="addRegularisation('.$attendance['id'] .')" class="btn btn-primary">Add Regularisation</a>';
                $status = '';
            }else{
                if($attendance['regularisation'] == 1){
                    $status = '<span class="text-danger">Regularization Pending </span>';
                }else{
                    $status = '';
                }
                $regulazation = '';
            }

            echo '
                    <tr id="row_' . $attendance['id'] . '" style="'.($ideal_time < 0.5 ? '' : 'background: #e98d8d;').'">
                      <th scope="row">' . $i . '</th>
                      <td>' . $user['first_name'] . ' ' . $user['last_name'] . ' ' . $late_login . ' ' . $late_login_status . ' '.$half_status.'</td>
                      <td>' .  strtoupper($role['role'])  . '</</td>
                      <td>' .  date("d M Y",strtotime($attendance['date']))  . '</</td>
                      <td>' . date('h:i A', strtotime($attendance['clock_in_time'])) . '</td>
                      <td class="text-'.($attendance['regularisation'] == 1 ? 'danger' : 'success').'">' . $attendance_clock_out . '</td>
                      <td>'.(round($efficincy['totalefficiency'], 2) ?? 0).'%</td>
                      <td> <span class="text-success">'.round($efficincy['totaltime']/60 , 2).'H </span> / <span class="text-danger">'.round($efficincy['takentime']/60 , 2).'H </span> </td>
                      <td>'.$ideal_hour.'</td>
                      <td>'.$regulazation.' '.$status.'</td>
                    </tr>
                  ';
            $i++;
          }

          ?>
        </tbody>
      </table>
    </div>
</main>
<?php include "includes/footer.php" ?>
<script>

  $('#addRegularisation').submit(function(){
      event.preventDefault();
      var formData = new FormData(this);
      $.ajax({
        url: 'includes/settings/api/attendanceApi.php',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          location.reload();
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    });

  $('#getAttendance').submit(function(e) {
    e.preventDefault();
    var startDate = $('#startDate').val();
    var endDate = $('#endDate').val();
    $.ajax({
      url: 'includes/settings/api/attendanceApi.php',
      type: 'GET',
      data: {
        startDate: startDate,
        endDate: endDate,
        type: 'getMonth'
      },
      dataType: 'json',
      success: function(response) {
        const extractedDataArray = [];
        var date = response.date
        var daysTotal = date.length - 1;
        date.push('Total Days (' + (date.length - 1) + ')');
        const extractedData = date;
        extractedDataArray.push(extractedData);
        var attendance = response.attendance;
        attendance.forEach(element => {
          const countOfAbsent = element.filter(item => item === '0').length;
          const countOfWeekOff = element.filter(item => item === 'week off').length;
          const countOfLeave = element.filter(item => item === 'leave').length;
          console.log(countOfAbsent, countOfWeekOff, countOfLeave);
          element.push(daysTotal - (countOfLeave + countOfWeekOff + countOfAbsent));
          extractedDataArray.push(element);
        });
        console.log(extractedDataArray);
        downloadExcel(extractedDataArray);
      },
      error: function(xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });


  function downloadExcel(data) {
    $.ajax({
      url: "includes/settings/downloadExcel.php",
      type: 'POST',
      data: {
        data: data
      },
      xhrFields: {
        responseType: 'blob'
      },
      success: function(result) {
        var a = document.createElement('a');
        var url = window.URL.createObjectURL(result);
        a.href = url;
        a.download = "example.xlsx"; // Set the desired file name
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        notyf.success("Excel File Download SuccessFull");
      }
    });
  }

  function addRegularisation(id){
    $('#attendance_id').val(id);
    $('#myModal').modal('show');
  }
</script>