<?php
$title = 'Leaves || EOM ';
$page_name = 'notification';
include "includes/header.php";
if($roleId != 1 && !(in_array($page_name, $pageAccessList))){
  echo '<script>window.location.href = "index.php"</script>';
}
?>

<style>
  .hidden {
    display: none !important;
  }
  label{
    margin-bottom: 15px !important;
  }
</style>

<main style="margin-top: 75px;">

  <div class="container-flude ">
    <section class="gradient-custom">
      <div class="container-flude px-5 py-2">
        <div class="row justify-content-center align-items-center">
          <div class="col-12 col-lg-12 col-xl-12 mb-3">
            <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
              <div class="card-body p-4 p-md-3">
                <h3 class="mb-4 pb-2 pb-md-0 mb-md-3 page_heading">Notification</h3>
                <form id="sendNotification">
                  <div class="row input-box">
                    <div class="col-md-12 col-sm-12 mb-2 me-3 ">
                      <label class="form-label select-label">Message</label>
                      <input type="txt" class="width-200 form-control" name="message" required>
                      <input type="hidden" name="type" value="sendNotification">
                    </div>
                    <div class="col-md-5 m-2">
                      <label class="form-label select-label">Type</label>
                      <select class="select form-control width-200" id="send_type" name="send_type" required>
                        <option value="" disabled>Choose Type</option>
                        <option value="user">User</option>
                        <option value="role">Role </option>
                      </select>
                    </div>
                    <div class="col-md-5 m-2" id="user_id">
                      <label class="form-label select-label">Select User</label>
                      <select class="select form-control width-200" name="user_id" required>
                        <option value="" disabled>Choose</option>
                        <?php
                        $users = $conn->prepare('SELECT *  FROM `users` WHERE `is_terminated` = 0');
                        $users->execute();
                        $users = $users->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($users as $value) {
                          echo '<option value="' . $value['id'] . '">' . $value['first_name'] . ' ' . $value['last_name'] . ' </option>';
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-5 m-2 hidden" id="role_id">
                      <label class="form-label select-label">Select Role</label>
                      <select class="select form-control width-200" name="role_id" required>
                        <option value="" disabled>Choose</option>
                        <?php
                        $roles = $conn->prepare('SELECT *  FROM `role`');
                        $roles->execute();
                        $roles = $roles->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($roles as $value) {
                          echo '<option value="' . $value['id'] . '">' . strtoupper($value['role']) . '</option>';
                        }
                        ?>
                      </select>
                    </div>
                  </div>


                  <div class="mt-4 pt-2 me-4">
                    <input class="btn btn-primary float-end " type="submit" value="Send Notification" />
                  </div>
                </form>
              </div>

            </div>
          </div>
          <div class="row justify-content-center ">
           <div class="col-xl-12">
            <table class="table table-bordered mt-4 border fw-bold" id="dataTable">
              <thead class="mt-2 mb-2">
                <tr>
                  <th>#</th>
                  <th>Message</th>
                  <th>Type</th>
                  <th>Name</th>
                  <th>Send by</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php

                $notification = $conn->prepare("SELECT * FROM `notification` ORDER BY `created_at` DESC");
                $notification->execute();
                $notification = $notification->fetchAll(PDO::FETCH_ASSOC);
                $i = 1;
                foreach ($notification as $value) {
                  $sender = $conn->prepare("SELECT * FROM `users` WHERE id = ? AND `is_terminated` = 0");
                  $sender->execute([$value['user_id']]);
                  $sender = $sender->fetch(PDO::FETCH_ASSOC);
                  $senderName = $sender['first_name'].' '.$sender['last_name'];
              
                  if($value['type'] == 'user'){
                    $user = $conn->prepare("SELECT * FROM `users` WHERE id = ? AND `is_terminated` = 0");
                    $user->execute([$value['type_id']]);
                    $user = $user->fetch(PDO::FETCH_ASSOC);
                    $name = $user['first_name'].' '.$user['last_name'];
                  }else{
                    $role = $conn->prepare("SELECT * FROM `role` WHERE id = ?");
                    $role->execute([$value['type_id']]);
                    $role = $role->fetch(PDO::FETCH_ASSOC);
                    $name = strtoupper($role['role']);
                  }

                  echo '<tr id="row_'.$value['id'].'">';
                  echo "<td>" . ++$i . "</td>";
                  echo "<td>" . $value['message'] . "</td>";
                  echo "<td>" . strtoupper($value['type']) . "</td>";
                  echo '<td>'.$name.'</td>';
                  echo '<td>'.$senderName.'</td>';
                  echo '<td>
                  <button class="btn btn-danger" onclick="deleteNotification('.$value['id'].')">Delete</button>
                  </td>';
                  echo "</tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
        </div>
      </div>
  </div>
  </section>
  </div>
</main>


<?php include 'includes/footer.php' ?>
<script>
  $('#send_type').change(function() {
    var type = $('#send_type').val();
    if (type == 'user') {
      $('#user_id').removeClass('hidden');
      $('#role_id').addClass('hidden');
    } else {
      $('#user_id').addClass('hidden');
      $('#role_id').removeClass('hidden');
    }
  });

  $('#sendNotification').submit(function(event) {
    event.preventDefault();
    var formData = new FormData(this);

    $.ajax({
      url: 'includes/settings/api/notificationApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(response) {
        notyf.success(response.message);
        location.reload();
      },
      error: function(xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);

      }
    });
  })

  function deleteNotification(id){
    $.ajax({
      url: 'includes/settings/api/notificationApi.php',
      type: 'POST',
      data: {
        id : id,
        type : 'deleteNotification'
      },
      dataType: 'json',
      success: function(response) {
        notyf.success(response.message);
        location.reload();
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