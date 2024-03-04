<?php
$page_name = 'attendance';
include "includes/header.php";
if($roleId != 1 && !(in_array($page_name, $pageAccessList))){
  echo '<script>window.location.href = "index.php"</script>';
} 
$attendances = $conn->prepare("SELECT * FROM `attendance` ORDER BY `created_at` DESC");
$attendances->execute();
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
        <h4 class="modal-title">Attendance</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
      </div>
      <form id="getAttendance" name="upload">
        <div class="modal-body">
          <div class="mb-3">
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Start Date </label>
              <div class="input-group">
                <input type="hidden" value="getMonth" name="type">
                <input type="date" class="form-control" id="startDate" name="startDate" required>
              </div>
            </div>
            <label for="project" class=" me-2 control-label p-2 ">End Date </label>
            <div class="input-group">
              <input type="date" class="form-control" id="endDate" name="endDate" required>
            </div>
            <label for="fileInput" class="form-label file" style="opacity:0">Select a Post</label>
            <div class="input-group">
              <button class="btn btn-primary" style="margin: auto;">Download</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<main style="margin-top: 58px;">
  <div class="container-flude pt-5">
    <div class="container-flude px-5">
      <div class="d-flex justify-content-between " style="padding: 0 0 40px 0;">
        <p class="fw-bold page_heading">View Attandance</p>
        <div style="display: flex; height:40px;">
          <select name="user_name" id="user_name" class="form-control" style="margin :0 15px;">
            <option value="">Search User</option>
            <?php
            $sql = $conn->prepare("SELECT * FROM `users`");
            $sql->execute();
            $role = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($role as $value) {
              echo '  <option value="' . $value['first_name'] . ' ' . $value['last_name'] . '">' . $value['first_name'] . ' ' . $value['last_name'] . '</option>';
            }
            ?>
          </select>
          <a data-bs-toggle="modal" style="margin:0 20px" href="#myModal" class="btn btn-primary">Download</a>
        </div>
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
            <th>Files</th>
            <th scope="col">Efficiency</th>
            <th scope="col">Total Time / Taken Time</th>
            <th>Ideal Time / Active Time</th>
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
            $efficincy = $conn->prepare("SELECT COUNT(task_id) as countefficiency , SUM(total_time) as totaltime , SUM(taken_time) as takentime FROM `efficiency` WHERE `user_id` = ? AND DATE(`created_at`) = ?");
            $efficincy->execute([$user['id'] , $attendance['date']]);
            $efficincy = $efficincy->fetch(PDO::FETCH_ASSOC);

            $break = $conn->prepare("SELECT SUM(`time`) as break_time FROM `break` WHERE DATE(`created_at`) = ? AND `user_id` = ?");
            $break->execute([ $attendance['date'] , $attendance['user_id']]);
            $break = $break->fetch(PDO::FETCH_ASSOC);



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
              $TclockOutTime = date('Y-m-d H:i:s');
              $attendance_clock_out = '';
              $ideal_hour = '';
            }
            
            
            if ($attendance['regularisation'] == 1) {
              $is_regularisation = true;
            } else {
              $is_regularisation = false;
            }

            if($efficincy['takentime'] > 0){
              $efficincy_user = ($efficincy['totaltime']/$efficincy['takentime'] * 100);
            }else{
              $efficincy_user = 0;
            }

            echo '
                    <tr id="row_' . $attendance['id'] . '" style="'.($ideal_time < 0.5 ? '' : 'background: #e98d8d;').'">
                      <th scope="row">' . $i . '</th>
                      <td>' . $user['first_name'] . ' ' . $user['last_name'] . ' ' . $late_login . ' ' . $late_login_status . ' ' . $half_status . '</td>
                      <td>' . strtoupper($role['role']). '</</td>
                      <td>' . date("d M Y",strtotime($attendance['date']))  . '</</td>
                      <td>' . date('h:i A', strtotime($attendance['clock_in_time'])) . '</td>
                      <td class="text-'.($is_regularisation ? 'danger' : 'success').'">' . $attendance_clock_out . '</td>
                      <td>'.$efficincy['countefficiency'].'</td>
                      <td>'.(round($efficincy_user, 2) ?? 0).'%</td>
                      <td> <span class="text-success">'.round($efficincy['totaltime']/60 , 2).'H </span> / <span class="text-danger">'.round($efficincy['takentime']/60 , 2).'H </span> </td>
                      <td>'.$ideal_hour.'</td>
                      </tr>
                  ';
            $i++;
            $half_status = '';
          }

          ?>
        </tbody>
      </table>
    </div>
</main>
<?php include "includes/footer.php" ?>
<script>
  document.getElementById('startDate').max = new Date().toISOString().split('T')[0];
  document.getElementById('endDate').max = new Date().toISOString().split('T')[0];
  $('#user_name').change(function() {
    var user_name = document.getElementById("user_name").value;
    dataTable.column(1).search(user_name).draw();
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
</script>