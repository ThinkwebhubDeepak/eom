<?php
$page_name = 'employee-salary';
include "includes/header.php";

if ($roleId != 1 && !(in_array($page_name, $pageAccessList))) {
  echo '<script>window.location.href = "index.php"</script>';
}

$sql = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0");
$sql->execute();
$users = $sql->fetchAll(PDO::FETCH_ASSOC);


function isSunday($dateString) {
    $timestamp = strtotime($dateString);
    $dayOfWeek = date('w', $timestamp);
    return $dayOfWeek == 0;
}

function isHoliday($dateString,$conn) {
    $holiday = $conn->prepare("SELECT * FROM `holiday` WHERE `date` = ?");
    $holiday->execute([$dateString]);
    $holiday = $holiday->fetch(PDO::FETCH_ASSOC);
    if($holiday){
        return 1;
    }
    return 0;
}

?>

<style>
  .form-group {
    padding: 10px;
}
</style>

<main style="margin-top: 58px;">
  <div class="container-flude pt-5">
    <div class="container-flude px-5">
      <div class="d-flex justify-content-between mb-3">
        <div>
          <h1 class="fw-bold page_heading">Employees Salary</h1>
        </div>
        <div class="d-flex gap-2">
        <select id="month" class="form-control" style="width:250px" name="month">
            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
        </select>
        <button class="btn btn-primary" onclick="download()">Download</button>
        </div>
      </div>
      <table id="dataTable" class="display">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Employee Id</th>
            <th scope="col">Name</th>
            <?php
            if ($roleId == 1 || (in_array('show-salary', $pageAccessList))) {
              echo '<th scope="col">Salary</th><th scope="col">Incentive</th><th scope="col">Advance</th>';
            }
            ?>
            
            <th scope="col">Total Leave</th>
            <th scope="col">Use Leave</th>
            <th scope="col">Deduct Days</th>
            <th scope="col">Attendance</th>
            <th scope="col">Holiday</th>
            
            <th scope="col">Role</th>
            <?php
            if ($roleId == 1 || (in_array('salary-action', $pageAccessList))) {
              echo '<th scope="col">Action</th>';
            }
            ?>
            
          </tr>
        </thead>
        <tbody>
          <?php
          
          $excel = [];
          $excel[] = ['Sno', 'Employee Id','Name','Leave', 'Use Leave','Punishment' , 'Attendance','Holiday']; 
          $i = 1;
          foreach ($users as $user) {
            $exceldata = [];
            
            $current_month = date('m');
            if(isset($_GET['month'])){
              $current_month = $_GET['month'];
                if($_GET['month'] == '01'){
                    $start_date = date('Y', strtotime('-1 year')).'-12-16';
                }else{
                    if($_GET['month'] <= 10){
                        $start_date = date('Y').'-0'.($_GET['month']-1).'-16';
                    }else{
                        $start_date = date('Y').'-'.($_GET['month']-1).'-16';
                    }
                }
                $end_date = date('Y').'-'.($_GET['month']).'-15';
            }else{
                if(date('m') == 1){
                    $start_date = date('Y', strtotime('-1 year')).'-12-16';
                }else{
                    $start_date = date('Y').'-'.date('m',strtotime('-1 month')).'-16';
                }
                $end_date = date('Y').'-'.date('m').'-15';
            }

            $use = 0;
            $use_leave = $conn->prepare("SELECT `use`, form_date, end_date, `user_id` FROM `leaves` WHERE (`form_date` BETWEEN ? AND ? OR `end_date` BETWEEN ? AND ?) AND `user_id` = ? AND `use` IS NOT NULL");
            $use_leave->execute([$start_date, $end_date, $start_date, $end_date, $user['id']]);
            $use_leave = $use_leave->fetchAll(PDO::FETCH_ASSOC);

            foreach ($use_leave as $value) {
              $startDateTime = new DateTime($start_date);
              $endDateTime = new DateTime($end_date);
              $leaveStartDate = new DateTime($value['form_date']);
              $leaveEndDate = new DateTime($value['end_date']);

              if (($start_date <= $value['form_date']) && ($end_date >= $value['end_date'])) {
                $use += $value['use'];
              } else if ($start_date > $value['form_date'] && $end_date > $value['end_date']) {
                $difference = $startDateTime->diff($leaveStartDate)->days;
                if ($difference == 0) {
                  $use += 1;
                } else {
                  $use += (($value['use'] < $difference ? 0 : $value['use'] - $difference));
                }
              } else if ($start_date < $value['form_date'] && $end_date < $value['end_date']) {
                $difference = $endDateTime->diff($leaveStartDate)->days;
                if ($difference == 0) {
                  $use += 1;
                } else {
                  $use += (($value['use'] < $difference ? $value['use'] : $value['use'] - $difference));
                }
              }
            }

            $incentive_amount = 0;
            $advance_salary = 0;
            $incentive = $conn->prepare("SELECT * FROM `salary` WHERE MONTH(month) = ? AND `user_id` = ?");
            $incentive->execute([$current_month , $user['id']]);
            $incentive = $incentive->fetch(PDO::FETCH_ASSOC);
            if($incentive){
              $temp = json_decode($incentive['incentive'] , true);
              foreach ($temp as $vvalue) {
                $incentive_amount += $vvalue['incentive'];
              }
              $temp = json_decode($incentive['advance_salary'] , true);
              foreach ($temp as $vvalue) {
                $advance_salary += $vvalue['advance'];
              }
            }


            $id = base64_encode($user['id']);
            $sql = $conn->prepare("SELECT * FROM `role` WHERE `id` = ?");
            $sql->execute([$user['role_id']]);
            $role = $sql->fetch(PDO::FETCH_ASSOC);
            
            $viewDetails = $conn->prepare("SELECT * FROM `userdetails` WHERE `user_id` = ?");
            $viewDetails->execute([$user['id']]);
            $viewDetails = $viewDetails->fetch(PDO::FETCH_ASSOC);

            $start_date = new DateTime($start_date);
            $end_date = new DateTime($end_date);
            $interval = new DateInterval('P1D');
            $current_date = clone $start_date;
            $att = 0;
            $holi = 0;
            $sun = 0;
            $leave = 0;
            $punishment = 0;
            while ($current_date <= $end_date) {

                $date = $current_date->format('Y-m-d');
                $current_date->format('Y-m-d') . PHP_EOL;

                if(!isSunday($date)){
                $checkAtten = $conn->prepare("SELECT * FROM `attendance` WHERE `user_id` = ? AND `date` = ? ");
                $checkAtten->execute([$user['id'] , $date]);
                $checkAtten = $checkAtten->fetch(PDO::FETCH_ASSOC);
                if($checkAtten){

                  if($checkAtten['punishment'] == 1){
                    $punishment += 1;
                  }

                  if ($checkAtten['clock_out_time'] != '') {
                    $attendance_clock_out = date('h:i A', strtotime($checkAtten['clock_out_time']));
                    $TclockInTime = strtotime($checkAtten['clock_in_time']);
                    $TclockOutTime = strtotime($checkAtten['clock_out_time']);
                    $timeDifferenceSeconds = $TclockOutTime - $TclockInTime;
                    $timeDifferenceHours = $timeDifferenceSeconds / 3600;
                    if($timeDifferenceHours > 5 && $timeDifferenceHours < 6.5){
                      $leave += 0.5;
                      $att -= 0.5;
                    }if($timeDifferenceHours < 5 ){
                      $leave += 1;
                      $att -= 1;
                    }
                  }
                    $att++;
                }else{
                      $levae = $conn->prepare("SELECT * FROM `leaves` WHERE `user_id` = ? AND form_date <= ? AND end_date >= ? AND `status` = 'approve'");
                      $levae->execute([$user['id'], $date , $date ]);
                      $levae = $levae->fetch(PDO::FETCH_ASSOC);
                      if($levae){
                        $leave++;
                      }else{
                          if(isHoliday($date,$conn)){
                              $holi++;
                          }else{
                            $leave += 1;
                          }
                    }
                }
              }else{
                $sun++;
              }
                $current_date->add($interval);
            }


            echo '
                    <tr id="row_' . $user['id'] . '">
                      <th scope="row">' . ($exceldata[] = $i) . '</th>
                      <td>' . ($exceldata[] = $user['employee_id']) . '</td>
                      <td>' . ($exceldata[] = $user['first_name'] . ' ' . $user['last_name']) . '</</td>';
                  
                  if ($roleId == 1 || (in_array('show-salary', $pageAccessList))) {
                    echo '<td> Rs. ' .$viewDetails['salary'] . '</</td>
                    <td>Rs. ' . $incentive_amount.'</td><td>Rs. ' . $advance_salary.'</td>';
                  }

                      
                      echo '<td>'.($exceldata[] = $leave).'</td>
                      <td class="text-success">'.($exceldata[] = $use ?? 0).'</td>
                      <td class="text-danger">'.($leave - intval($use)).' + '.($exceldata[] = $punishment).'</td>
                      <td>' . ($exceldata[] = $att) . ' - '.$punishment.'</td>
                      <td>' . ($exceldata[] = $sun + $holi).'</td>
                      <td>' . strtoupper($role['role']) . '</td>';


                      if ($roleId == 1 || (in_array('salary-action', $pageAccessList))) {
                        echo '<td style="display: flex;"> 
                        <a class="btn btn-primary" data-bs-toggle="modal" href="#add_incentive" onclick="addIncentive(\''. $user['first_name'] .' '.$user['last_name'].'\','.$user['id'].','.$att.',' . $sun + $holi.','.$viewDetails['salary'].')">Add Incentive</a>
                        <a class="btn btn-primary" style="margin:0 10px" data-bs-toggle="modal" href="#add_incentive" onclick="addAdvance(\''. $user['first_name'] .' '.$user['last_name'].'\','.$user['id'].','.$att.',' . $sun + $holi.','.$viewDetails['salary'].')">Advance</a>
                        <a class="btn btn-primary" target="_blank" href="print_salary.php?user_id='.$user['id'].'&month='.($_GET['month'] ?? date('m')).'" >Print</a>
                        
                        </td>';
                      }

                      

                     echo '
                    </tr>
                  ';
            $i++;
            $excel[] = $exceldata;
          }
          ?>
        </tbody>
      </table>
    </div>
</main>

<div class="modal fade" id="add_incentive" aria-hidden="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><span id="full_name"></span> </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateUserForm">
          <div class="row form-row">
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label id="level_field">Incentive</label>
                <input type="number" class="form-control" name="incentive" id="incentive" required>
                <input type="hidden" class="form-control" name="user_id" id="user_id" required>
                <input type="hidden" class="form-control" name="attendance" id="attendace" required>
                <input type="hidden" class="form-control" name="salary" id="salary" required>
                <input type="hidden" class="form-control" name="leave" id="leave" required>
                <input type="hidden" class="form-control" name="type" id="type_status" value="addIncentive" required>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Remark</label>
                <input type="text" class="form-control" name="remark" id="remark" required>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>



<?php include "includes/footer.php"; ?>
<script>
  $('#userType').change(function() {
    var userType = document.getElementById("userType").value;
    dataTable.column(7).search(userType).draw();
  });

  function addIncentive(name, user_id,leave,attendace,salary){
    $('#full_name').text(name);
    $('#user_id').val(user_id);
    $('#attendace').val(attendace);
    $('#leave').val(leave);
    $('#salary').val(salary);
    $('#type_status').val('addIncentive');
    $('#level_field').text('Incentive');
  }

  function addAdvance(name, user_id,leave,attendace,salary){
    $('#full_name').text(name);
    $('#user_id').val(user_id);
    $('#attendace').val(attendace);
    $('#leave').val(leave);
    $('#salary').val(salary);
    $('#type_status').val('addAdvance');
    $('#level_field').text('Advance');
  }

  $('#updateUserForm').submit(function(e){
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: 'includes/settings/api/incentiveApi.php',
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

  

 
  $('#month').val('<?php echo $_GET['month'] ?? date('m') ?>');

  $('#month').change(()=>{
    var month = $('#month').val();
    window.location.href = 'employee-salary.php?month='+month;
  });

  function download(){
    var data = <?php echo json_encode($excel) ?>;
    downloadExcel(data);
  }
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