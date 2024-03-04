<?php 
  $page_name = 'terminated-employee';
  include "includes/header.php";

  if($roleId != 1 && !(in_array($page_name, $pageAccessList))){
    echo '<script>window.location.href = "index.php"</script>';
  }
  
  $sql = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 1");
  $sql->execute();
  $users = $sql->fetchAll(PDO::FETCH_ASSOC);

?>
<main style="margin-top: 58px;">
  <div class="container-flude pt-5">
    <div class="container-flude px-5">
      <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
          <p class="fw-bold page_heading">Terminated Employees</p>
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
            <th scope="col">Date of Birth</th>
            <th scope="col">Phone</th>
            <th scope="col">Address</th>
            <th scope="col">Role</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          foreach ($users as $user) {
            $id = base64_encode($user['id']);
            $sql = $conn->prepare("SELECT * FROM `role` WHERE `id` = ?");
            $sql->execute([$user['role_id']]);
            $role = $sql->fetch(PDO::FETCH_ASSOC);
            echo '
                    <tr id="row_' . $user['id'] . '">
                      <th scope="row">' . $i . '</th>
                      <td>' . $user['employee_id'] . '</td>
                      <td>' . $user['first_name'] . '</td>
                      <td>' . $user['last_name'] . '</</td>
                      <td>' . date('d M, Y',strtotime($user['dob'])) . '</</td>
                      <td>' . $user['phone'] . '</td>
                      <td>' . $user['address'] . '</td>
                      <td>' . strtoupper($role['role']) . '</td>
                      <td style="display: flex;"> <a class="btn btn-primary" href="add-employee.php?edit=' . $id . '" style="margin:0 10px">Edit</a></td>
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
    dataTable.column(7).search(userType).draw();
  });
</script>