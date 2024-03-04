<?php
    $page_name = 'leave-bonus';
    $title = 'Leaves Bonus || EOM ';
    include 'includes/header.php' ;
    if(!(in_array($page_name, $pageAccessList)) && $roleId != 1){
        echo '<script>window.location.href = "index.php"</script>';
    }  
?>
  <main style="margin-top: 100px;">
  <div class="container pt-5">
        <div class="container">
        <div class="d-flex justify-content-between" style="padding: 0 0 40px 0; font-size: 25px;">
          <div>
            <p class="fw-bold">Leave Bonus</p>
          </div>
        </div>
        <table id="dataTable" class="display">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Approve By</th>
              <th scope="col">Date</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
            $leaves = $conn->prepare("SELECT * FROM `leave_bonus` WHERE `aprrove` = 0");
            $leaves->execute();
            $leaves = $leaves->fetchAll(PDO::FETCH_ASSOC);
              foreach($leaves as $leave){
                $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                $user->execute([$leave['user_id']]);
                $user = $user->fetch(PDO::FETCH_ASSOC);

                $auser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                $auser->execute([$leave['approve_by']]);
                $auser = $auser->fetch(PDO::FETCH_ASSOC);

                  echo '
                    <tr id="row_'.$leave['id'].'">
                      <th scope="row">'.$i.'</th>
                      <td>'.$user['first_name'].' '.$user['last_name'].'</td>
                      <td>'.$auser['first_name'].' '.$auser['last_name'].'</td>
                      <td> '.date('d M, Y', strtotime($leave['created_at'])).' </td>
                      <td><button class="btn btn-primary" onclick="approveLeave('.$leave['id'].')">Approve</button></td>
                    </tr>';
                  $i++;
              }
            
            ?>
          </tbody>
        </table>
        </div>
  </div>
  </main>
<?php include 'includes/footer.php' ?>
<script>
    function approveLeave(id){
        $.ajax({
        url: 'includes/settings/api/leaveApi.php',
        type : 'POST',
        data: {
          type : 'approveLeaveBonus',
          leave_id : id
        },
        dataType: 'json',
        success: function(response) {
          notyf.success(response.message);
          $('#row_'+id).remove();
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }
</script>