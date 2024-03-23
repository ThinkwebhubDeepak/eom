<?php
$page_name = 'attendance';
include "includes/header.php";
if($roleId != 1 && !(in_array($page_name, $pageAccessList))){
  echo '<script>window.location.href = "index.php"</script>';
} 

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

      <table id="dataTablee" class="display">
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
            <th scope="col">Other Task</th>
            <th>Ideal Time / Active Time</th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>
    </div>
</main>
<?php include "includes/footer.php" ?>
<script>

  var dataTablee = $('#dataTablee').DataTable({
      "processing": true,
      "serverSide": true,
      "order": [],
      "ajax": {
          url: "includes/settings/api/attendanceDataTableApi.php",
          type: "POST"
      }
  });

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