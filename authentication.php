<?php
$page_name = 'authentication';
include 'includes/header.php';
?>
<link rel="stylesheet" href="includes/assets/plugin/bootstrap-toggle.min.css">
<main style="margin-top: 58px;">
    <div class="container-flude pt-5">
        <div class="container-flude px-5">
            <div class="row" id="filter_box">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="modal-header">
                            <h4 class="modal-title">Filter</h4>
                        </div>
                        <div class="card-body">
                            <form id="searchTaskForm">
                                <div class="row form-row" style="align-items: center;">
                                    <div class="col-12 col-sm-6 m-2">
                                        <div class="form-group">
                                            <select class="form-control" name="role_id" id="role_id" required>
                                                <option value="" default>Select User</option>
                                                <?php
                                                $sql = $conn->prepare("SELECT * FROM `role` WHERE id != 1");
                                                $sql->execute();
                                                $role = $sql->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($role as $value) {
                                                    $selected = $_GET['role_id'] == $value['id'] ? 'selected' : '';
                                                    echo '<option value = ' . $value['id'] . ' ' . $selected . '>' . strtoupper($value['role']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-5 m-2">
                                        <button type="submit" class="btn btn-primary w-100 h-50">Search<i class="fa-solid fa-magnifying-glass ms-2"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Page</th>
                                            <th>Active</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table_boby">
                                      <?php
                                      if (isset($_GET["role_id"]) && $_GET['role_id'] != '') {

                                          $access = $conn->prepare("SELECT * FROM `access` WHERE `role_id` = ?");
                                          $access->execute([$_GET['role_id']]);
                                          $access = $access->fetch(PDO::FETCH_ASSOC);
                                          $pageAccessList = json_decode($access['access_page']) ?? [];

                                          $pages = $conn->prepare("SELECT * FROM `pages`");
                                          $pages->execute();
                                          $pages = $pages->fetchAll(PDO::FETCH_ASSOC);
                                          foreach ($pages as $value) {

                                              if (in_array($value['id'], $pageAccessList)) {
                                                  $check = 'checked';
                                              } else {
                                                  $check = '';
                                              }

                                              echo '<tr id="tr' . $value['id'] . '">
                                                    <td>' . ++$i . '</td>
                                                    <td>' . $value['page'] . '</td>
                                                    <td><input data-id="' . $value['id'] . '" class="toggle_btn_page" ' . $check . ' type="checkbox" data-on="Allow" data-off="Not Allow" data-toggle="toggle" data-onstyle="success" data-offstyle="danger"></td>
                                                </tr>
                                            ';
                                          }
                                      }
                                      ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
?>
<script src="includes/assets/plugin/bootstrap-toggle.min.js"></script>
<script>
     $('.toggle_btn_page').on('change', function() {
        var role_id = $('#role_id').val();
        var dataId = $(this).data('id');
        if ($(this).prop('checked')) {
           $.ajax({
                url: 'includes/settings/api/pageAccessApi.php',
                type: 'POST',
                data: {
                    role_id : role_id,
                    page_id : dataId,
                    type : 'AccessPage'
                },
                dataType: 'json',
                success: function (response) {
                    $('#toggle-trigger').prop('checked', false).change()
                    notyf.success(response.message);
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                    notyf.error(errorMessage);
                }
           });
        } else {
            $.ajax({
                url: 'includes/settings/api/pageAccessApi.php',
                type: 'POST',
                data: {
                    role_id : role_id,
                    page_id : dataId,
                    type : 'RemoveAccessPage'
                },
                dataType: 'json',
                success: function (response) {
                    $('#toggle-trigger').prop('checked', true).change()
                    notyf.success(response.message);
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                    notyf.error(errorMessage);
                }
           });
        }
    });

</script>