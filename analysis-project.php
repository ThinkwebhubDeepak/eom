<?php 
    $page_name = 'analysis';
    include "includes/header.php"; 
    function returnArea($conn , $task_id , $project_id){
        $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?");
        $task->execute([$task_id,$project_id]);
        $task = $task->fetch(PDO::FETCH_ASSOC);
        return $task['area_sqkm'] ?? 0;
    }
?>

<!-- jquery.vectormap css -->
<link href="analytics/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />



<!-- Bootstrap Css -->
<link href="analytics/assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="analytics/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="analytics/assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
<script src="analytics/assets/libs/jquery/jquery.min.js"></script>
<script src="analytics/assets/libs/raphael/raphael.min.js"></script>
<script src="analytics/assets/libs/morris.js/morris.min.js"></script>
<style>
    .navbar {
        padding-top: 0rem;
        padding-bottom: 0rem;
    }

    .is_complete {
        background: #cfffcf;
    }

    .databoxx {
        cursor: pointer;
    }

    .modal-dialog {
        min-width: fit-content;
    }
</style>
<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Analysis</h4>

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-sm-6 col-lg-3">
                    <div class="card text-center">
                        <div class="card-body databoxx" onclick="viewData('today')">
                            <h2 class="mt-3 mb-2"><i class="mdi mdi-arrow-down text-success me-2"></i><b>Today</b>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card text-center">
                        <div class="card-body p-t-10 databoxx" onclick="viewData('yesterday')">
                            <h2 class="mt-3 mb-2"><i class="mdi mdi-arrow-up text-success me-2"></i><b>Yesterday</b></h2>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card text-center">
                        <div class="card-body p-t-10 databoxx" onclick="viewData('week')">
                            <h2 class="mt-3 mb-2"><i class="mdi mdi-arrow-up text-success me-2"></i><b>This Week</b></h2>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card text-center">
                        <div class="card-body p-t-10 databoxx" onclick="viewData('month')">
                            <h2 class="mt-3 mb-2"><i class="mdi mdi-arrow-down text-success me-2"></i><b>This Month</b>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->

            <div class="row">

                <?php
                    $project_id = $_GET['project_id'];
                    $projects = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
                    $projects->execute([$project_id]);
                    $project = $projects->fetch(PDO::FETCH_ASSOC);
                    $tasks = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id` = ?");
                    $tasks->execute([$project['id']]);
                    $tasks = $tasks->fetchAll(PDO::FETCH_ASSOC);
                    if ($project['vector'] == 1) {
                        $completetasks = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ? AND `status` = 'complete' AND `vector_status` = 'complete'");
                        $completetasks->execute([$project['id']]);
                        $completetasks = $completetasks->fetchAll(PDO::FETCH_ASSOC);

                        $completeVector = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ? AND `vector_status` = 'complete'");
                        $completeVector->execute([$project['id']]);
                        $completeVector = $completeVector->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $completetasks = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ? AND `status` = 'complete'");
                        $completetasks->execute([$project['id']]);
                        $completetasks = $completetasks->fetchAll(PDO::FETCH_ASSOC);
                    }

                    $completePro = $conn->prepare("SELECT * FROM `tasks` WHERE status > 'pro_in_progress' AND `project_id` = ?");
                    $completePro->execute([$project['id']]);
                    $completePro = $completePro->fetchAll(PDO::FETCH_ASSOC);

                    $completeQc = $conn->prepare("SELECT * FROM `tasks` WHERE status > 'qc_in_progress' AND `project_id` = ?");
                    $completeQc->execute([$project['id']]);
                    $completeQc = $completeQc->fetchAll(PDO::FETCH_ASSOC);

                    $completeQa = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ? AND `status` = 'complete'");
                    $completeQa->execute([$project['id']]);
                    $completeQa = $completeQa->fetchAll(PDO::FETCH_ASSOC);


                    $proCount = count($completePro);
                    $qcCount = count($completeQc);
                    $qaCount = count($completeQa);

                    if ($qcCount < $qaCount) {
                        $qcCount = $qaCount;
                    }

                    if ($proCount < $qcCount) {
                        $proCount = $qcCount;
                    }
                ?>
                    <div class="col-lg-4 ">
                        <div class="card <?php echo $project['is_complete'] ? 'is_complete' : '' ?>">
                            <div class="card-body">
                                <h4 class="mt-0 card-title"><?php echo $project['project_name'] ?></h4>

                                <ul class="list-inline d-flex justify-content-around mt-3">
                                    <li class="list-inline-item">
                                        <h5 class="text-center"><b><?php echo count($tasks); ?></b></h5>
                                        <p class="text-muted mb-0">Total Task</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="text-center"><b><?php echo count($completetasks); ?></b></h5>
                                        <p class="text-muted mb-0">Complete</p>
                                    </li>
                                    <li class="list-inline-item">
                                        <h5 class="text-center"><b><?php echo count($tasks) - count($completetasks); ?></b></h5>
                                        <p class="text-muted mb-0">Pending</p>
                                    </li>
                                </ul>

                                <div class="morris-charts" id="project_graph_<?php echo $project['id'] ?>" style="height: 300px"></div>
                            </div>
                        </div>
                    </div>
                    <script>
                        Morris.Donut({
                            element: 'project_graph_<?php echo $project['id'] ?>',
                            data: [{
                                    label: "PRO",
                                    value: <?php echo $proCount ?>
                                },
                                {
                                    label: "QC",
                                    value: <?php echo $qcCount ?>
                                },
                                {
                                    label: "QA",
                                    value: <?php echo $qaCount ?>
                                }
                                <?php if (isset($completeVector)) {
                                    echo ', {label: "Vector", value:' . count($completeVector) . '}';
                                } ?>
                            ]
                        });
                    </script>
            </div>
            <!-- end row -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <h4 class="mb-4 mt-0 card-title">Project profile</h4>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Pro</th>
                                                    <th>QC</th>
                                                    <th>QA</th>
                                                    <?php
                                                    if($project['vector'] == 1){
                                                        echo '<th>Vector</th>';
                                                    }
                                                    
                                                    ?>
                                                    <th>Taken Time / Total Time</th>
                                                    <th>Efficiency</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                <?php
                                                $users = $conn->prepare('SELECT * FROM `users` ORDER BY `first_name` ASC');
                                                $users->execute();
                                                $users = $users->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($users as $user) {

                                                    // pro
                                                    $proArea = 0;
                                                    $qcArea = 0;
                                                    $qaArea = 0;
                                                    $vectorArea = 0;

                                                    $area = $conn->prepare("SELECT * FROM `efficiency` WHERE `project_id` = ? AND `user_id` = ? ORDER BY `profile` ASC");
                                                    $area->execute([$project_id , $user['id']]);
                                                    $areas = $area->fetchAll(PDO::FETCH_ASSOC);
                                                    foreach($areas as $area){
                                                        switch ($area['profile']) {
                                                            case 'pro':
                                                                $proArea += returnArea($conn , $area['task_id'],$project_id);
                                                                break;
                                                            case 'qc':
                                                                $qcArea += returnArea($conn , $area['task_id'],$project_id);
                                                                break;
                                                            case 'qa':
                                                                $qaArea += returnArea($conn , $area['task_id'],$project_id);
                                                                break;
                                                            case 'vector':
                                                                $vectorArea += returnArea($conn , $area['task_id'],$project_id);
                                                                break;
                                                        }
                                                    }

                                                    $time = $conn->prepare("SELECT SUM(`taken_time`) as takentime , SUM(`total_time`) as totaltime FROM `efficiency` WHERE `project_id` = ? AND `user_id` = ?");
                                                    $time->execute([$project_id , $user['id']]);
                                                    $time = $time->fetch(PDO::FETCH_ASSOC);


                                                    if($time['takentime'] != 0){
                                                        $efficiency = round($time['totaltime']/$time['takentime'] * 100 , 2);
                                                    }else{
                                                        $efficiency = 0;
                                                        continue;
                                                    }
                                                    


                                                    echo
                                                    ' <tr>
                                                    <td>' . $user['first_name'] . ' '.$user['last_name']. '</td>
                                                    <td> '.$proArea.''.$project['area'].'</td>
                                                    <td> '.$qcArea.''.$project['area'].'</td>
                                                    <td> ' . $qaArea . ''.$project['area'].'</td>';
                                                    if($project['vector'] == 1){
                                                        echo '<td> ' . $vectorArea . ''.$project['area'].'</td>';
                                                    }

                                                    echo '<td> <span class="text-danger"> ' . round($time['takentime']/60 , 2) . 'hr. </span> / <span class="text-success"> ' . round($time['totaltime']/60 , 2) . ' hr.</span> </td>
                                                    <td> ' . $efficiency.'%</td>
                                                </tr>';
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
            <!-- end row -->
        </div>

    </div>
</div>
<!-- end main content-->

</div>
<!-- END layout-wrapper -->

<!-- Right Sidebar -->
<div class="right-bar">
    <div data-simplebar class="h-100">
        <div class="rightbar-title d-flex align-items-center px-3 py-4">

            <h5 class="m-0 me-2">Settings</h5>

            <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                <i class="mdi mdi-close noti-icon"></i>
            </a>
        </div>

        <!-- Settings -->
        <hr class="mt-0" />
        <h6 class="text-center mb-0">Choose Layouts</h6>

        <div class="p-4">
            <div class="mb-2">
                <img src="assets/images/layouts/layout-1.jpg" class="img-fluid img-thumbnail" alt="layout-1">
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input theme-choice" type="checkbox" id="light-mode-switch" checked>
                <label class="form-check-label" for="light-mode-switch">Light Mode</label>
            </div>

            <div class="mb-2">
                <img src="assets/images/layouts/layout-2.jpg" class="img-fluid img-thumbnail" alt="layout-2">
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input theme-choice" type="checkbox" id="dark-mode-switch" data-bsStyle="assets/css/bootstrap-dark.min.css" data-appStyle="assets/css/app-dark.min.css">
                <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
            </div>
        </div>

    </div> <!-- end slimscroll-menu-->
</div>
<!-- /Right-bar -->

<div class="modal fade" id="history_modal" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="model_head"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover table-center mb-0" id="dataTable">
                    <thead>
                        <tr>
                            <th>Sno</th>
                            <th>Name</th>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Role</th>
                            <th>Efficiency</th>
                            <th>Data Time</th>
                        </tr>
                    </thead>
                    <tbody id="task_data_view">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>
<?php include "includes/footer.php"; ?>
<!-- JAVASCRIPT -->

<script src="analytics/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="analytics/assets/libs/metismenu/metisMenu.min.js"></script>
<script src="analytics/assets/libs/simplebar/simplebar.min.js"></script>


<script src="analytics/assets/js/pages/dashboard.init.js"></script>
<!-- <script src="assets/js/app.js"></script> -->
<script>
    function viewData(duration) {
        Notiflix.Loading.standard();
        dataTable.clear().draw();
        $('#model_head').html(duration);
        $.ajax({
            url: 'includes/settings/api/analysisApi.php',
            type: 'GET',
            data: {
                type: duration
            },
            success: function(result) {
                var i = 1;
                result.forEach(element => {
                    var newRowData = [i++, element.name, element.task_id, element.project_name, element.profile, element.efficiency+'%', element.date];
                    dataTable.row.add(newRowData).draw();
                });
                $('#history_modal').modal('show');
                Notiflix.Loading.remove();
            }
        });
    }
</script>
</body>

</html>