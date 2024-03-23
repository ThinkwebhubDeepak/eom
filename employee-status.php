<?php 
  $page_name = 'employee-status';
  include "includes/header.php";

  if($roleId != 1 && !(in_array($page_name, $pageAccessList))){
    echo '<script>window.location.href = "index.php"</script>';
  }
  
  $sql = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0");
  $sql->execute();
  $users = $sql->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
    .UnAssigned{
      background-color: red !important;
    }
    .Assigned{
      background-color: yellow !important;
    }
    .Preparation,
    .Feedback,
    .Finalization,
    .Working{
      background-color: rgb(19, 137, 104)  !important;
    }
</style>
<main style="margin-top: 58px;">
  <div class="container-flude pt-5">
    <div class="container-flude px-5">
      <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
          <p class="fw-bold page_heading">Employees Status</p>
          <div style="">
            <select name="user_type" id="userType" class="form-control" style="margin :0 15px;">
            <option value="">Select User</option>
            <?php 
             $sql = $conn->prepare("SELECT * FROM `role`");
             $sql->execute();
             $role = $sql->fetchAll(PDO::FETCH_ASSOC);
             foreach($role as $value){
             echo'  <option value='.$value['role'].'>'.$value['role'].'</option>';
             }
            ?>
             
            
              
            </select>
        </div>
      </div>
      <table id="dataTable" class="display">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Employee Id</th>
            <th scope="col">First Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">Status</th>
            <th scope="col">Current Working</th>
            <th scope="col">Role</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          foreach ($users as $user) {
            $sql = $conn->prepare("SELECT * FROM `role` WHERE `id` = ?");
            $sql->execute([$user['role_id']]);
            $role = $sql->fetch(PDO::FETCH_ASSOC);
            
            $work = $conn->prepare("SELECT * FROM `work_log` WHERE `user_id` = ? ORDER BY `created_at` DESC");
            $work->execute([$user['id']]);
            $work = $work->fetch(PDO::FETCH_ASSOC);
            if($work['next_status'] == 'pro_in_progress' || $work['next_status'] == 'qc_in_progress' || $work['next_status'] == 'qa_in_progress' || $work['next_status'] == 'vector_in_progress'){
                $status = 'Working';
                $cwork = $work['task_id'];
            }else{
              $projectefficiency = $conn->prepare("SELECT `projectefficiency`.* , `projects`.`project_name` FROM `projectefficiency` JOIN `projects` ON `projects`.`id` = `projectefficiency`.`project_id` WHERE `projectefficiency`.`user_id` = ? AND `projectefficiency`.`status` = 'start' ORDER BY `projectefficiency`.`id` DESC");
              $projectefficiency->execute([$user['id']]);
              $projectefficiency = $projectefficiency->fetch(PDO::FETCH_ASSOC);
              if($projectefficiency){
                $status = ucfirst($projectefficiency['type']);
                $cwork = $projectefficiency['project_name'];
              }else{
                $assign = $conn->prepare("SELECT * FROM `assign` WHERE `user_id` = ? AND `status` = 'assign'");
                $assign->execute([$user['id']]);
                $assign = $assign->fetch(PDO::FETCH_ASSOC);
                if($assign){
                    $status = 'Assigned';
                }else{
                  $status = 'Un Assigned';
                  $cwork = '';
                }
              }
            }

            echo '
                    <tr id="row_' . $user['id'] . '" class="'.str_replace(' ','',$status).'">
                      <th scope="row">' . $i . '</th>
                      <td>' . $user['employee_id'] . '</td>
                      <td>' . $user['first_name'] . '</td>
                      <td>' . $user['last_name'] . '</</td>
                      <td>' . $status . '</</td>
                      <td>' . $cwork . '</td>
                      <td>' . strtoupper($role['role']) . '</td>
                      <td style="display: flex;"> <a class="btn btn-primary" href="assign-pro.php" style="margin:0 10px">Assign</a></td>
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
  $('#userType').change(function () {
    var userType = document.getElementById("userType").value;
    dataTable.column(6).search(userType).draw();
  });
</script>