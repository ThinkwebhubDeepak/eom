<?php
    $page_name = 'holiday';
    $title = 'Holiday Lists || EOM ';
    include "includes/header.php";
    if($roleId != 1 && !(in_array($page_name, $pageAccessList))){
      echo '<script>window.location.href = "index.php"</script>';
    }
    $currentDate = date('Y-m-d');
    $attendances = $conn->prepare("SELECT * FROM `attendance` WHERE `user_id` = ? ORDER BY `created_at` DESC");
    $attendances->execute([$_SESSION['userId']]);
    $attendances = $attendances->fetchAll(PDO::FETCH_ASSOC);

?>
<?php
if (isset($_POST['upload'])) {
  $tempFile = $_FILES["csvFile"]["tmp_name"];
  $targetFile = "upload/holiday/" . basename($_FILES["csvFile"]["name"]);
  if (move_uploaded_file($tempFile, $targetFile)) {
    $csvFile = fopen($targetFile, "r");
    if (!$csvFile) {
      echo "unable to read the file";
    }

    $sl = 1;
    while ($data = fgetcsv($csvFile)) {

      if ($sl != 1) {
        $rawDate = $data[0];
        $date = date("Y-m-d", strtotime($rawDate));

        $holiday_name = $data[1];
        $sql = $conn->prepare("INSERT INTO `holiday`(`date`, `holiday`) VALUES (? , ?)");
        $result = $sql->execute([$date, $holiday_name]);

      }
      $sl++;

      if ($sl >= 200) {
        break;
      }
    }


  } else {
    echo "failed";
  }
}


?>

  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Edit Holiday</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">  
            <form id="updateHoliday">
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Date</label>
                    <input type="text" class="form-control" name="date" id="date" required>
                    <input type="hidden" class="form-control" name="type" value="updateHoliday" required>
                    <input type="hidden" id="holiday_id" name="holiday_id" value="<?php echo $task_id ?>" required>
                  </div>
                </div>              
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Summary</label>
                    <input type="text" id="summary" class="form-control" name="summary" required>
                  </div>
                </div>
              </div>
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-6 p-2">
                  <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" id="image"class="form-control">
                  </div>
                </div>
                <div class="col-12 col-sm-6 p-2">
                  <img src="" id="imgae" width="200px" alt="">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
              </div>
            </form>
          </div>
      </div>
    </div>
    </div>
  </div>

  <main style="margin-top:100px">
    <div class="login-form-bg ">
      <div class="container-flude my-2 px-5">
        <div class="row justify-content-center mb-5">
          <div class="col-xl-6">
            <div class="form-input-content">
              <div class="card login-form mb-0">
                <div class="card-body pt-5 shadow">
                  <h4 class="text-center">Add Holidays</h4>
                  <form enctype="multipart/form-data" method="post">
                    <div class="form-group">
                      <input type="file" class="form-control" name="csvFile" id="">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block me-2 my-2" name="upload">Upload <svg class="svg-inline--fa fa-arrow-up-from-bracket me-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="arrow-up-from-bracket" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 53 43 96 96 96H352c53 0 96-43 96-96V352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352z"></path></svg></button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row justify-content-center ">
          <div class="col-xl-12">
            <table class="table table-bordered mt-4" id="dataTable">
              <thead>
                <tr>
                  <th>Sno</th>
                  <th>Date</th>
                  <th>Holiday</th>
                  <th>Image</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php

                $sql = $conn->prepare("SELECT * FROM `holiday` ORDER BY `date` ASC");
                $sql->execute();
                $holidays = $sql->fetchAll(PDO::FETCH_ASSOC);
                $i = 0;

                foreach ($holidays as $holiday) {
                  echo '<tr id="row_'.$holiday['id'].'">';
                  echo '<td>'.++$i.'</td>';
                  echo "<td>" . date('M j, D',strtotime($holiday['date'])) . "</td>";
                  echo "<td>" . $holiday['holiday'] . "</td>";
                  echo '<td><img src="images/holiday/'.$holiday['image'].'" width="80px"></td>';
                  echo '<td>
                  <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="getHoilday('.$holiday['id'].')">Edit</button>
                  <button class="btn btn-danger" onclick="deleteHoilday('.$holiday['id'].')">Delete</button>
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
  </main>
  <?php include 'includes/footer.php' ?>
</body>

</html>
<script>

      function getHoilday(id){
        $.ajax({
            url: "includes/settings/api/holidayApi.php?type=getHoliday",
            data: { id: id },
            dataType: "json",
            success: function(response) {
              console.log(response);
              $('#date').val(response.date);
              $("#holiday_id").val(response.id);
              $("#summary").val(response.holiday);
              $("#imgae").attr("src","images/holiday/"+response.image);
            }
          });
      }

      function deleteHoilday(id) {
          if (confirm("Want to delete this holiday?")) {
              $.ajax({
                  type: "POST",
                  url: "includes/settings/api/holidayApi.php?type=deleteHoliday",
                  data: { id: id },
                  dataType: "json",
                  success: function(response) {
                      if (response.success) {
                        notyf.success(response.message);
                        $('#row_'+id).remove();
                      } else {
                        notyf.success("Failed to delete holiday.");
                      }
                  },
                  error: function() {
                      alert("An error occurred while processing the request.");
                  }
              });
          }
      }

      $('#updateHoliday').submit(function(){
        event.preventDefault();
				var formData = new FormData(this);
				$.ajax({
					url: 'includes/settings/api/holidayApi.php',
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

</script>