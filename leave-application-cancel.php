<?php


$page_name = 'approve-leave';
$title = 'Leaves Application || EOM ';
include 'includes/header.php' ;
if(!(in_array($page_name, $pageAccessList)) && $roleId != 1){
  echo '<script>window.location.href = "index.php"</script>';
}  

$leaves = $conn->prepare("SELECT * FROM `leaves` WHERE `status` = 'cancel' ORDER BY `leaves`.`form_date` DESC");
$leaves->execute();
$leaves = $leaves->fetchAll(PDO::FETCH_ASSOC);
$countCancel = count($leaves);

$leavesApprove = $conn->prepare("SELECT * FROM `leaves` WHERE `status` = 'approve' ORDER BY `leaves`.`created_at` DESC");
$leavesApprove->execute();
$leavesApprove = $leavesApprove->fetchAll(PDO::FETCH_ASSOC);
$countAprrove = count($leavesApprove);

$leavespending = $conn->prepare("SELECT * FROM `leaves` WHERE `status` = 'pending' ORDER BY `leaves`.`created_at` DESC");
$leavespending->execute();
$leavespending = $leavespending->fetchAll(PDO::FETCH_ASSOC);
$countPending = count($leavespending);

?>

  <main style="margin-top: 100px;">
  <div class="btn-group   justify-content-center d-flex  mt-3 " role="group">
      <a href="approve-leave.php" style="display: flex;align-items: center;margin: 0 10px">
        <button type="button" class="btn btn-primary position-relative">
        Pending
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countPending ?>
          <span class="visually-hidden">Pending</span>
        </span>
      </button></a>
      <a href="leave-application-approve.php" style="display: flex;align-items: center;margin: 0 10px">
      <button type="button" class="btn btn-primary position-relative">
        Approve
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countAprrove ?>
          <span class="visually-hidden">Approve</span>
        </span>
        </button>
      </a>
      <a href="#" style="display: flex;align-items: center;margin: 0 10px">
        <button type="button" class="btn btn-primary position-relative btn_active" style="background: #fff2f2;
    color: black;">
        Cancel
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?php echo $countCancel ?>
          <span class="visually-hidden">Cancel</span>
        </span>
        </button>
      </a>
    </div>
    <div class="container pt-5">
      <div class="container">
        <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
          <div>
            <p class="fw-bold">Leave Application</p>
          </div>
        </div>
        <table id="dataTable" class="display">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Approve By</th>
              <th scope="col">Reason</th>
              <th scope="col">From Date</th>
              <th scope="col">End Date</th>
              <th scope="col">Days</th>
              <th scope="col">Status</th>
              <?php

                if($roleId == 1){
                  echo '<th scope="col">Action</th>';
                }

              ?>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
              foreach($leaves as $leave){
                $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                $user->execute([$leave['user_id']]);
                $user = $user->fetch(PDO::FETCH_ASSOC);

                $auser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                $auser->execute([$leave['approved_by']]);
                $auser = $auser->fetch(PDO::FETCH_ASSOC);

                $assignCheck = $conn->prepare("SELECT DISTINCT assigned_by FROM `assign` WHERE `user_id` = ?");
                $assignCheck->execute([$leave['user_id']]);
                $assignCheck = $assignCheck->fetchAll(PDO::FETCH_ASSOC);

                $users = [];
                foreach($assignCheck as $assign){
                  $users[] = $assign['assigned_by'];
                }

                $date1 = new DateTime($leave['form_date']);
                $date2 = new DateTime($leave['end_date']);

                $interval = $date1->diff($date2);
                $daysDifference = $interval->days;

                if($leave['formdate_session'] == 'First Half' && $leave['enddate_session'] == 'Second Half'){
                  $daysDifference += 1;
                }
                
                if($leave['formdate_session'] == 'Second Half' && $leave['enddate_session'] == 'First Half'){
                  // $daysDifference += 1;
                }
                
                if($leave['formdate_session'] ==  $leave['enddate_session']){
                  $daysDifference += 0.5;
                }

                  echo '
                    <tr id="row_'.$leave['id'].'">
                      <th scope="row">'.$i.'</th>
                      <td>'.$user['first_name'].' '.$user['last_name'].'</td>
                      <td>'.$auser['first_name'].' '.$auser['last_name'].'</td>
                      <td>'.$leave['region'].'</td>
                      <td>'.date('d-m-Y', strtotime($leave['form_date'])).'<br><span style="color:red">'.$leave['formdate_session'].'</span></td>
                      <td>'.date('d-m-Y', strtotime($leave['end_date'])).'<br><span style="color:red">'.$leave['enddate_session'].'</span></</td>
                      <td> '.$daysDifference.' </td>
                      <td id="row_status_'.$leave['id'].'">'.strtoupper($leave['status']).'</td>';


                      if($roleId == 1){
                      echo '<td> <a class="btn btn-primary" style="margin:0 10px" onclick="approveLeave('.$leave['id'].')">Approve</a></td>';
                      }

                  echo '</tr>';
                  $i++;
              }
            
            ?>
          </tbody>
        </table>
      </div>
  </main>


  <?php include 'includes/footer.php' ?>
  <script>

    function approveLeave(leave_id){
      $.ajax({
        url: 'includes/settings/api/leaveApi.php',
        data: {
          type : 'approveLeave',
          leave_id : leave_id
        },
        dataType: 'json',
        success: function(response) {
          notyf.success(response.message);
          $('#row_'+leave_id).remove();
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }

  </script>

</body>

</html>