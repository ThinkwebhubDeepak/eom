<?php 
    $page_name = 'add-employee';
    include "includes/header.php";
    if($roleId != 1 && !(in_array($page_name, $pageAccessList))){
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
                    $id = $_GET['edit'];
                    $id = base64_decode($id);
                    $sql = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                    $sql->execute([$id]);
                    $result = $sql->fetch(PDO::FETCH_ASSOC);
                    if (!$result) {
                        echo '<script>alert("user not found")</script>';
                    }
                ?>
                    <form id="employeeForm">
                        <div class="container-flude px-5">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-bold">Create Employee</p>
                                </div>
                            </div>
                            <hr class="bg-dark" />
                            <div class="employee_form">
                                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required</p>
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">First Name <span style="color:red">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="<?php echo $result['first_name'] ?>" required>
                                    <input type="hidden" name="type" value="updateUserData">
                                    <input type="hidden" name="id" value="<?php echo $result['id'] ?>">
                                </div>
                                <hr class="bg-dark" />
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Last Name <span style="color:red">*</span></label>
                                    <input type="text" name="last_name" value="<?php echo $result['last_name'] ?>" class="form-control " required>
                                </div>
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Address <span style="color:red">*</span></label>
                                    <input type="text" name="address" value="<?php echo $result['address'] ?>" class="form-control " required>
                                </div>
                                <hr class="bg-dark" />
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Date of Birth <span style="color:red">*</span></label>
                                    <input type="text" value="<?php echo $result['dob'] ?>" name="dob" class="form-control " id="dob1" placeholder="Enter DOB" required>
                                </div>

                                <hr class="bg-dark" />
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Employee Id <span style="color:red">*</span></label>
                                    <input type="text" value="<?php echo $result['employee_id'] ?>" name="employee_id" class="form-control " required>
                                </div>

                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Phone <span style="color:red">*</span></label>
                                    <input type="number" name="phone" id="phone" value="<?php echo $result['phone'] ?>" class="form-control " required>
                                </div>

                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Type <span style="color:red">*</span></label>
                                    <select name="role_id" class="form-control">
                                        <?php
                                        $sql = $conn->prepare("SELECT * FROM `role`");
                                        $sql->execute();
                                        $role = $sql->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($role as $value) {
                                            $selected = $result['role_id'] == $value['id'] ? 'selected' : '';
                                            echo '<option value = ' . $value['id'] . ' ' . $selected . '>' . strtoupper($value['role']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
                                    <a onclick="resetPassword(<?php echo $result['id'] ?>)" class="btn btn-danger create-btn m-2">Reset Password</a>
                                    <?php
                                        if($result['is_terminated'] == 1){
                                            echo '<a onclick="reJoinUser('.$result['id'].')" class="btn btn-success create-btn m-2">Rejoin User</a>';
                                        }else{
                                            echo '<a onclick="terminateUser('.$result['id'].')" class="btn btn-danger create-btn m-2">Terminate User</a>';
                                        }
                                    ?>
                                    <button type="submit" class="btn btn-primary create-btn m-2">Update</button>
                                </div>
                            </div>
                    </form>
                <?php

                } else {
                ?>
                    <form id="employeeForm" class="submitForm">
                        <div class="container-flude px-5">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-bold page_heading">Create Employee</p>
                                </div>
                            </div>
                            <hr class="bg-dark" />
                            <div class="employee_form">
                                <p>All field marked with an asterisk (<span style="color:red">*</span>) are required</p>
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">First Name <span style="color:red">*</span></label>
                                    <input type="text" name="first_name" class="form-control" required>
                                    <input type="hidden" name="type" value="saveUserData">
                                </div>
                                <hr class="bg-dark" />
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Last Name <span style="color:red">*</span></label>
                                    <input type="text" name="last_name" class="form-control " required>
                                </div>
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Address <span style="color:red">*</span></label>
                                    <input type="text" name="address" class="form-control " required>
                                </div>
                                <hr class="bg-dark" />
                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Employee Id <span style="color:red">*</span></label>
                                    <input type="text" name="employee_id" class="form-control " required>
                                </div>

                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Date of Birth <span style="color:red">*</span></label>
                                    <input type="text" name="dob" class="form-control " id="dob2" placeholder="Enter DOB" required>
                                </div>

                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Phone <span style="color:red">*</span></label>
                                    <input type="number" name="phone" class="form-control" id="phone" required>
                                </div>

                                <div class="d-flex input-box">
                                    <label for="Summary" class=" me-2 control-label">Type <span style="color:red">*</span></label>
                                    <select name="role_id" class="form-control">
                                        <?php
                                        $sql = $conn->prepare("SELECT * FROM `role`");
                                        $sql->execute();
                                        $role = $sql->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($role as $value) {
                                            echo '<option value = ' . $value['id'] . '>' . strtoupper($value['role']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>


                                <div class="creat-btn d-flex justify-content-center mt-4 mb-5 ">
                                    <button type="submit" class="btn btn-primary create-btn ">Create</button>
                                </div>
                            </div>
                    </form>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</main>
<?php include "includes/footer.php" ?>
<script>
      $('#dob1').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
  $('#dob2').dateDropper({
    format: 'Y/m/d',
    large: true,
    largeDefault: true,
    largeOnly: true,
    theme: 'datetheme'
  });
    $(document).ready(function() {
        $('#employeeForm').submit(function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            var phone  = $('#phone').val();
            if(phone.length == 10){
                $.ajax({
                    url: 'includes/settings/api/userApi.php',
                    type: 'POST',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        notyf.success(response.message);
                        setTimeout(() => {
                            window.location.href = 'employee-list.php';
                        }, 500);
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                        notyf.error(errorMessage);
                    }
                });
            }else{
                notyf.error('Phone no must be 10 digits');
            }
        });
    });

    function resetPassword(id) {
        $.ajax({
            url: 'includes/settings/api/userApi.php',
            type: 'POST',
            data: {
                user_id: id,
                type: 'resetPassword'
            },
            dataType: 'json',
            success: function(response) {
                notyf.success(response.message);
                setTimeout(() => {
                    window.location.href = 'employee-list.php';
                }, 500);
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        })
    }


    function terminateUser(id) {
        $.ajax({
            url: 'includes/settings/api/userApi.php',
            type: 'POST',
            data: {
                user_id: id,
                type: 'terminateUser'
            },
            dataType: 'json',
            success: function(response) {
                notyf.success(response.message);
                setTimeout(() => {
                    window.location.href = 'terminated-employee.php';
                }, 500);
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        })
    }

    function reJoinUser(id){
        $.ajax({
            url: 'includes/settings/api/userApi.php',
            type: 'POST',
            data: {
                user_id: id,
                type: 'reJoinUser'
            },
            dataType: 'json',
            success: function(response) {
                notyf.success(response.message);
                setTimeout(() => {
                    window.location.href = 'terminated-employee.php';
                }, 500);
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        })
    }

</script>