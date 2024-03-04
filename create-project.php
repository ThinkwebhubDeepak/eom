<?php
$page_name = 'create-project';
$title = 'Create Project || EOM ';
include "includes/header.php";
if(!(in_array($page_name, $pageAccessList)) && $roleId != 1){
  echo '<script>window.location.href = "index.php"</script>';
}  
?>
<style>
  .datepicker-container {
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    padding: 5px;

  }
</style>

<main style="margin-top: 58px;">
  <div class="container-flude pt-4">
    <div class="project_issue">
      <div class="projec-task">
        <?php

        if (isset($_GET['edit'])) {
          $project_id = base64_decode($_GET['edit']);
          $sqll = $conn->prepare('SELECT * FROM `projects` WHERE `id` = ?');
          $sqll->execute([$project_id]);
          $project = $sqll->fetch(PDO::FETCH_ASSOC);

        ?>
          <form id="productForm">
            <div class="container-flude px-4">
              <div class="d-flex justify-content-between">
                <div>
                  <h2 class="fw-bold page_heading">Create Project</h2>
                </div>
              </div>
              <hr class="bg-dark" />
              <div class="project_form">
                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required </p>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Project <span style="color:red">*</span></label>
                  <input type="text" name="project_name" class="form-control" value="<?php echo $project['project_name'] ?>" required>
                  <input type="hidden" name="type" value="updateProject">
                  <input type="hidden" name="product_id" value="<?php echo $project_id ?>">
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Summary <span style="color:red">*</span></label>
                  <input type="text" name="summary" class="form-control " value="<?php echo $project['summary'] ?>" required>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Description <span style="color:red">*</span></label>
                  <input type="text" name="description" value="<?php echo $project['description'] ?>" class="form-control " required>
                </div>
                <div class="d-flex input-box">
                  <label for="firstName" class=" me-2 control-label" id="area">Area <span style="color:red">*</span></label>

                  <select class="form-control" name="area" required>
                    <option value="sqkm" <?php if ($project['area'] == 'sqkm') {
                                              echo 'selected';
                                            } ?>>Square Kilometer</option>
                    <option value="lkm" <?php if ($project['area'] == 'lkm') {
                                              echo 'selected';
                                            } ?>>Line Kilometer</option>
                  </select>
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="firstName" class=" me-2 control-label" id="Complexity">Complexity <span style="color:red">*</span></label>

                  <select class="form-control" name="complexity" required>
                    <option value="" disabled>Choose Complexity </option>
                    <option value="high" <?php if ($project['complexity'] == 'high') {
                                              echo 'selected';
                                            } ?>>High</option>
                    <option value="medium" <?php if ($project['complexity'] == 'medium') {
                                              echo 'selected';
                                            } ?>>Medium</option>
                    <option value="low" <?php if ($project['complexity'] == 'low') {
                                            echo 'selected';
                                          } ?>>Low</option>
                  </select>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Vector Project <span style="color:red">*</span></label>
                  <div class="input-group">
                    <label for="html" style="width:80px">Yes <input type="radio" id="html" name="vector" value="1" <?php if ($project['vector'] == 1) {
                                                                                                                      echo 'checked';
                                                                                                                    } ?>> </label> <br>
                    <label for="css">No
                      <input type="radio" id="css" name="vector" value="0" <?php if ($project['vector'] == 0) {
                                                                              echo 'checked';
                                                                            } ?>></label><br>
                  </div>
                </div>
                <div class="d-flex input-box no-margin ">
                  <div class="col-6">
                    <div class="input-box">
                      <label for="" class="me-3">Start date <span style="color:red">*</span></label>
                      <input type="date" class="form-control Attachment-input" id="datepicker" placeholder="Select a date" name="start_date" value="<?php echo $project['start_date'] ?>" required>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="input-box">
                      <label for="" class="me-3">End date <span style="color:red">*</span></label>
                      <input type="date" class="form-control Attachment-input" id="datepicker1" placeholder="Select a date" name="end_date" value="<?php echo $project['end_date'] ?>" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="d-flex input-box">
                <label for="Summary" class=" me-2 control-label">Estimated Hour </label>
                <div class="input-group">
                  <input type="number" class="form-control overflow" value="<?php echo $project['estimated_hour'] ?>" name="estimated_hour" id="estimated_hour" required>
                </div>
              </div>

              <div class="creat-btn d-flex justify-content-center mt-4 mb-5 " style="gap:10px">
                <button type="submit" class="btn btn-primary create-btn ">Update</button>
                <a class="btn btn-danger" onclick="deleteProject(<?php echo $project_id ?>)">Delete</a>
              </div>
            </div>
          </form>
        <?php
        } else {
        ?>
          <form id="productForm">
            <div class="container-flude px-5">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="fw-bold page_heading">Create Project</p>
                </div>
              </div>
              <hr class="bg-dark" />
              <div class="project_form">
                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required</p>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Project <span style="color:red">*</span></label>
                  <input type="text" name="project_name" class="form-control" required>
                  <input type="hidden" name="type" value="addProject">
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Summary <span style="color:red">*</span></label>
                  <input type="text" name="summary" class="form-control " required>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Description <span style="color:red">*</span></label>
                  <input type="text" name="description" class="form-control " required>
                </div>
                <div class="d-flex input-box">
                  <label for="firstName" class=" me-2 control-label" id="area">Area <span style="color:red">*</span></label>

                  <select class="form-control" name="area" required>
                    <option value="sqkm" selected>Square Kilometer</option>
                    <option value="lkm">Line Kilometer</option>
                  </select>
                </div>
                <hr class="bg-dark" />
                <div class="d-flex input-box">
                  <label for="firstName" class=" me-2 control-label" id="Complexity">Complexity <span style="color:red">*</span></label>

                  <select class="form-control" name="complexity" required>
                    <option value="" disabled>Choose Complexity</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                <div class="d-flex input-box">
                  <label for="Summary" class=" me-2 control-label">Vector Project <span style="color:red">*</span></label>
                  <div class="input-group">
                    <label for="html" style="width:80px">Yes <input type="radio" id="html" name="vector" value="1"> </label> <br>
                    <label for="css">No
                      <input type="radio" id="css" name="vector" value="0"></label><br>
                  </div>
                </div>
                <div class="d-flex input-box no-margin ">
                  <div class="col-6">
                    <div class="input-box">
                      <label for="" class="me-3">Start date <span style="color:red">*</span></label>
                      <input type="text" class="form-control Attachment-input" id="start_date" placeholder="Select a date" name="start_date" required>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="input-box">
                      <label for="" class="me-3">End date <span style="color:red">*</span></label>
                      <input type="text" class="form-control Attachment-input" id="end_date" placeholder="Select a date" name="end_date" required>
                    </div>
                  </div>
                </div>
              </div>
              <div class="d-flex input-box">
                <label for="Summary" class=" me-2 control-label">Estimated Hour </label>
                <div class="input-group">
                  <input type="number" class="form-control overflow" name="estimated_hour" id="estimated_hour" required>
                </div>
              </div>

              <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
                <button type="submit" class="btn btn-primary create-btn ">Create</button>
              </div>
            </div>
          </form>
        <?php } ?>
      </div>
    </div>
  </div>
</main>

<?php include 'includes/footer.php' ?>
<script>

  $('#start_date').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
  
  $('#end_date').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });

  function diffDate(start_date, end_date) {
    const date1 = new Date(start_date);
    const date2 = new Date(end_date);
    const differenceInMilliseconds = date2 - date1;
    const day = Math.floor(differenceInMilliseconds / (1000 * 60 * 60 * 24));
    return day;
  }

  $('#start_date, #end_date').on('change', function() {
    var startDate = new Date($('#start_date').val());
    var endDate = new Date($('#end_date').val());

    if (startDate && endDate) {
      if (startDate > endDate) {
        notyf.error('Start date cannot be greater than End date.');
        $('#end_date').val('');
      } else {
        const startDateStr = $('#start_date').val();
        const endDateStr = $('#end_date').val();
        $('#estimated_hour').val(diffDate(startDateStr, endDateStr) * 8);
      }
    }
  });



  $('#productForm').submit(function(event) {
    event.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'includes/settings/api/projectApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(response) {
        notyf.success(response.message);
        setTimeout(() => {
          window.location = 'project-list.php';
        }, 1000);

      },
      error: function(xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });
  
  
 function deleteProject(project_id){
    $.ajax({
      url: 'includes/settings/api/projectApi.php',
      type: 'POST',
      data: {
        type : 'deleteProject',
        project_id : project_id
      },
      dataType: 'json',
      success: function(response) {
        notyf.success(response.message);
        setTimeout(() => {
          window.location = 'project-list.php';
        }, 1000);

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