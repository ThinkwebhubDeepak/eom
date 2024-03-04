<?php
$page_name = 'employee-list';
include "includes/header.php";

if ($roleId != 1 && !(in_array($page_name, $pageAccessList))) {
  echo '<script>window.location.href = "index.php"</script>';
}

$sql = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0");
$sql->execute();
$users = $sql->fetchAll(PDO::FETCH_ASSOC);

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
          <h1 class="fw-bold page_heading">Employees</h1>
        </div>
        <div style=" " class="">
          <select name="user_type" id="userType" class="form-control" style="margin :0 15px;">
            <option value="">Select User</option>
            <?php
            $sql = $conn->prepare("SELECT * FROM `role`");
            $sql->execute();
            $role = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($role as $value) {
              echo '  <option value=' . $value['role'] . '>' . $value['role'] . '</option>';
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
            <th scope="col">Date of Joining</th>
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
                      <td>' . date('d M, Y', strtotime($user['dob'])) . '</</td>
                      <td>' . $user['phone'] . '</td>
                      <td>' . $user['address'] . '</td>
                      <td>' . strtoupper($role['role']) . '</td>
                      <td>' . date('d M, Y', strtotime($user['created_at'])) . '</</td>
                      <td style="display: flex;"> 
                      <a class="btn btn-primary" href="add-employee.php?edit=' . $id . '" style="margin:0 10px">Edit</a>
                      <a class="btn btn-primary" onclick="viewDetails('.$user['id'].' ,\'' . $user['first_name'] . ' ' . $user['last_name'] . '\')" href="#details_user" data-bs-toggle="modal"  style="margin:0 5px">Other Details</a>
                      </td>
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
        <form id="updateUserForm">
          <div class="row form-row">
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Designation</label>
                <input type="text" class="form-control" name="designation" id="designation" required>
                <input type="hidden" class="form-control" name="user_id" id="user_id" required>
                <input type="hidden" class="form-control" name="type" value="addDetails" required>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Department</label>
                <input type="text" class="form-control" name="department" id="department" required>
              </div>
            </div>

          </div>
          <div class="row form-row">

            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Salary</label>
                <input type="number" class="form-control" name="salary" id="salary" required>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="form-group">
                  <label>Account No</label>
                  <input type="number" class="form-control" name="account_no" id="account_no" required>
                </div>
            </div>

          </div>
          <div class="row form-row">
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Bank Name</label>
                <input type="text" class="form-control" name="bank_name" id="bank_name" required>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Ifsc Code</label>
                <input type="text" name="ifsc" id="ifsc" class="form-control">
              </div>
            </div>

          </div>
          <div class="row form-row">
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Leave Balance</label>
                <input type="number" class="form-control" name="leave_balanace" id="leave_balanace" required>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Aadhar No,</label>
                <input type="number" class="form-control" name="aadhar" id="aadhar" required>
              </div>
            </div>
          </div>
          <div class="row form-row">
            <div class="col-12 col-sm-6">
              <div class="form-group">
                <label>Pen card</label>
                <input type="text" class="form-control" name="pen_card" id="pen_card" required>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include "includes/footer.php" ?>
<script>
  $('#userType').change(function() {
    var userType = document.getElementById("userType").value;
    dataTable.column(7).search(userType).draw();
  });

  function viewDetails(user_id , name){
      $('#full_name').text(name);
      $('#user_id').val(user_id);
      $('#bank_name').val('');
      $('#account_no').val('');
      $('#ifsc').val('');
      $('#designation').val('');
      $('#department').val('');
      $('#leave_balanace').val('');
      $('#salary').val('');
      $('#aadhar').val('');
      $('#pen_card').val('');
      $.ajax({
        url : 'includes/settings/api/viewDetailsApi.php',
        type : 'get',
        data : {
          user_id :user_id,
          type : 'viewDetails'
        },
        dataType : 'json',
        success :function(result){
          $('#bank_name').val(result.bank_name);
          $('#account_no').val(result.account_name);
          $('#ifsc').val(result.ifsc_code);
          $('#designation').val(result.designation);
          $('#department').val(result.department);
          $('#leave_balanace').val(result.leave_balance);
          $('#salary').val(result.salary);
          $('#aadhar').val(result.aadhar);
          $('#pen_card').val(result.pen_card);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
  }

  $('#updateUserForm').submit(function(e){
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: 'includes/settings/api/viewDetailsApi.php',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (response) {
          notyf.success(response.message);
          $('#details_user').modal('hide');
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
  });

</script>