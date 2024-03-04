<?php $page_name = 'analysis';
include "includes/header.php"; ?>

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
                $projects = $conn->prepare("SELECT * FROM `projects` ORDER BY `id` DESC");
                $projects->execute();
                $projects = $projects->fetchAll(PDO::FETCH_ASSOC);
                foreach ($projects as $project) {
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
                            <a href="analysis-project.php?project_id=<?php echo $project['id'] ?>">
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
                                </a>
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
                <?php
                }
                ?>
            </div>
            <!-- end row -->

            <div class="row">
                <div class="col-lg-7">
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
                                                    <th>Vector</th>
                                                    <th>Status</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($projects as $project) {
                                                    $total_time = $conn->prepare("SELECT SUM(total_time) AS total_time FROM `efficiency` WHERE `project_id` = ?");
                                                    $total_time->execute([$project['id']]);
                                                    $total_time = $total_time->fetch(PDO::FETCH_ASSOC);

                                                    $taken_time = $conn->prepare("SELECT SUM(taken_time) AS taken_time FROM `efficiency` WHERE `project_id` = ?");
                                                    $taken_time->execute([$project['id']]);
                                                    $taken_time = $taken_time->fetch(PDO::FETCH_ASSOC);


                                                    // pro

                                                    $pro_taken_time = $conn->prepare("SELECT SUM(taken_time) AS taken_time FROM `efficiency` WHERE `project_id` = ? AND `profile` = 'pro'");
                                                    $pro_taken_time->execute([$project['id']]);
                                                    $pro_taken_time = $pro_taken_time->fetch(PDO::FETCH_ASSOC);

                                                    $pro_total_time = $conn->prepare("SELECT SUM(total_time) AS total_time FROM `efficiency` WHERE `project_id` = ? AND `profile` = 'pro'");
                                                    $pro_total_time->execute([$project['id']]);
                                                    $pro_total_time = $pro_total_time->fetch(PDO::FETCH_ASSOC);

                                                    // qc
                                                    $qc_taken_time = $conn->prepare("SELECT SUM(taken_time) AS taken_time FROM `efficiency` WHERE `project_id` = ? AND `profile` = 'qc'");
                                                    $qc_taken_time->execute([$project['id']]);
                                                    $qc_taken_time = $qc_taken_time->fetch(PDO::FETCH_ASSOC);

                                                    $qc_total_time = $conn->prepare("SELECT SUM(total_time) AS total_time FROM `efficiency` WHERE `project_id` = ? AND `profile` = 'qc'");
                                                    $qc_total_time->execute([$project['id']]);
                                                    $qc_total_time = $qc_total_time->fetch(PDO::FETCH_ASSOC);


                                                    // qa
                                                    $qa_taken_time = $conn->prepare("SELECT SUM(taken_time) AS taken_time FROM `efficiency` WHERE `project_id` = ? AND `profile` = 'qa'");
                                                    $qa_taken_time->execute([$project['id']]);
                                                    $qa_taken_time = $qa_taken_time->fetch(PDO::FETCH_ASSOC);

                                                    $qa_total_time = $conn->prepare("SELECT SUM(total_time) AS total_time FROM `efficiency` WHERE `project_id` = ? AND `profile` = 'qa'");
                                                    $qa_total_time->execute([$project['id']]);
                                                    $qa_total_time = $qa_total_time->fetch(PDO::FETCH_ASSOC);


                                                    // vector
                                                    $vector_taken_time = $conn->prepare("SELECT SUM(taken_time) AS taken_time FROM `efficiency` WHERE `project_id` = ? AND `profile` = 'vector'");
                                                    $vector_taken_time->execute([$project['id']]);
                                                    $vector_taken_time = $vector_taken_time->fetch(PDO::FETCH_ASSOC);

                                                    $vector_total_time = $conn->prepare("SELECT SUM(total_time) AS total_time FROM `efficiency` WHERE `project_id` = ? AND `profile` = 'vector'");
                                                    $vector_total_time->execute([$project['id']]);
                                                    $vector_total_time = $vector_total_time->fetch(PDO::FETCH_ASSOC);


                                                    if($taken_time['taken_time'] != 0){
                                                        $effciency = round(($total_time['total_time'] / $taken_time['taken_time']) * 100, 2);
                                                    }else{
                                                        $effciency = 0;
                                                    }

                                                    if ($pro_taken_time['taken_time']) {
                                                        $pro_efficieny = round(($pro_total_time['total_time'] / $pro_taken_time['taken_time']) * 100, 2);
                                                    } else {
                                                        $pro_efficieny = 0;
                                                    }

                                                    if ($qc_taken_time['taken_time']) {
                                                        $qc_efficieny = round(($qc_total_time['total_time'] / $qc_taken_time['taken_time']) * 100, 2);
                                                    } else {
                                                        $qc_efficieny = 0;
                                                    }

                                                    if ($qa_taken_time['taken_time']) {
                                                        $qa_efficieny = round(($qa_total_time['total_time'] / $qa_taken_time['taken_time']) * 100, 2);
                                                    } else {
                                                        $qa_efficieny = 0;
                                                    }

                                                    if ($project['vector'] == 1) {
                                                        if ($vector_taken_time['taken_time']) {
                                                            $vector_efficieny = round(($vector_total_time['total_time'] / $vector_taken_time['taken_time']) * 100, 2);
                                                        } else {
                                                            $vector_efficieny = 0;
                                                        }
                                                        $progress = $qa_efficieny . '% <div class="progress  mb-3">
                                                        <div class="progress-bar progress-bar-primary " profile="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: ' . $qa_efficieny . '%;">
                                                        </div><!-- /.progress-bar .progress-bar-danger -->
                                                        </div>';
                                                    } else {
                                                        $qa_efficieny = 0;
                                                        $progress = 'N/A';
                                                    }


                                                    echo
                                                    ' <tr>
                                                    <td>' . $project['project_name'] . '</td>
                                                    <td> ' . $pro_efficieny . '% <div class="progress  mb-3">
                                                        <div class="progress-bar progress-bar-primary " profile="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: ' . $pro_efficieny . '%;">
                                                        </div><!-- /.progress-bar .progress-bar-danger -->
                                                        </div>
                                                    </td>
                                                    <td> ' . $qc_efficieny . '% <div class="progress  mb-3">
                                                        <div class="progress-bar progress-bar-primary " profile="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: ' . $qc_efficieny . '%;">
                                                        </div><!-- /.progress-bar .progress-bar-danger -->
                                                        </div>
                                                    </td>
                                                    <td> ' . $qa_efficieny . '% <div class="progress  mb-3">
                                                        <div class="progress-bar progress-bar-primary " profile="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: ' . $qa_efficieny . '%;">
                                                        </div><!-- /.progress-bar .progress-bar-danger -->
                                                        </div>
                                                    </td>
                                                    <td> ' . $progress . '
                                                    </td>
                                                    <td> ' . ($project['is_complete'] ? 'Complete' : 'Progress') . '
                                                    </td>
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

                <div class="col-lg-5">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <h4 class="mb-4 mt-0 card-title">Project Effciency</h4>
                            <?php
                            foreach ($projects as $project) {
                                $total_time = $conn->prepare("SELECT SUM(total_time) AS total_time FROM `efficiency` WHERE `project_id` = ?");
                                $total_time->execute([$project['id']]);
                                $total_time = $total_time->fetch(PDO::FETCH_ASSOC);

                                $taken_time = $conn->prepare("SELECT SUM(taken_time) AS taken_time FROM `efficiency` WHERE `project_id` = ?");
                                $taken_time->execute([$project['id']]);
                                $taken_time = $taken_time->fetch(PDO::FETCH_ASSOC);

                                if($taken_time['taken_time'] != 0){
                                    $effciency = round(($total_time['total_time'] / $taken_time['taken_time']) * 100, 2);
                                }else{
                                    $effciency = 0;
                                }

                            ?>
                                <p class="font-600 mb-1"><?php echo $project['project_name'] ?><span class="text-primary float-end"><b><?php echo round($effciency, 2) ?>%</b></span></p>
                                <div class="progress  mb-3">
                                    <div class="progress-bar progress-bar-primary " profile="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round($effciency, 2) ?>%;">
                                    </div><!-- /.progress-bar .progress-bar-danger -->
                                </div><!-- /.progress .no-rounded -->
                            <?php

                            }

                            ?>

                        </div>
                    </div>
                </div> <!-- end col -->
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <h4 class="mb-4 mt-0 card-title">Best Performance OverAll in Total</h4>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Name</th>
                                                    <th>Efficency</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                <?php
                                                $toplist = $conn->prepare("SELECT user_id, SUM(efficiency) AS total_efficiency FROM efficiency GROUP BY user_id ORDER BY total_efficiency DESC");
                                                $toplist->execute();
                                                $toplist = $toplist->fetchAll(PDO::FETCH_ASSOC);
                                                $i = 0;
                                                
                                                $data = [];
                                                foreach ($toplist as $value) {
                                                    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                                    $user->execute([$value['user_id']]);
                                                    $user = $user->fetch(PDO::FETCH_ASSOC);
                                                
                                                    $countList = $conn->prepare("SELECT * FROM efficiency WHERE `user_id` = ?");
                                                    $countList->execute([$value['user_id']]);
                                                    $countList = $countList->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                    $efficiency = round($value['total_efficiency'] / count($countList), 2);
                                                    $data[] = ["name" => $user['first_name'] . ' ' . $user['last_name'] , "efficiency" => $efficiency ];

                                                }

                                                usort($data, function ($a, $b) {
                                                    return $b['efficiency'] <=> $a['efficiency'];
                                                });

                                                foreach ($data as $item){
                                                    echo
                                                    ' <tr>
                                                        <td>' . ++$i . '</td>
                                                        <td>' . $item['name'] .'</td>
                                                        <td>' . $item['efficiency'] . '%</td>
                                                    </tr>';
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
                <!-- end col -->

                <div class="col-lg-4">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <h4 class="mb-4 mt-0 card-title">Best Performance OverAll in This Month</h4>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Name</th>
                                                    <th>Efficency</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                <?php
                                                $current_month = date('m');
                                                if (date('d') >= 15) {
                                                    if ($current_month == '01') {
                                                        $start_date = date('Y', strtotime('-1 year')) . '-12-16';
                                                    } else {
                                                        $start_date = date('Y') . $current_month . '-16';
                                                    }
                                                    $end_date = date('Y') . '-' . $current_month . '-15';
                                                } else {
                                                    if (date('m') == 1) {
                                                        $start_date = date('Y', strtotime('-1 year')) . '-12-16';
                                                    } else {
                                                        $start_date = date('Y') . '-' . date('m', strtotime('-1 month')) . '-16';
                                                    }
                                                    $end_date = date('Y') . '-' . date('m') . '-15';
                                                }

                                                $toplist = $conn->prepare("SELECT user_id, SUM(efficiency) AS total_efficiency FROM efficiency WHERE DATE(`created_at`) BETWEEN ? AND ?  GROUP BY user_id ORDER BY total_efficiency DESC");
                                                $toplist->execute([$start_date,$end_date]);
                                                $toplist = $toplist->fetchAll(PDO::FETCH_ASSOC);
                                                $i = 0;
                                                
                                                $data = [];
                                                foreach ($toplist as $value) {
                                                    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                                    $user->execute([$value['user_id']]);
                                                    $user = $user->fetch(PDO::FETCH_ASSOC);
                                                
                                                    $countList = $conn->prepare("SELECT * FROM efficiency WHERE DATE(`created_at`) BETWEEN ? AND ? AND `user_id` = ?");
                                                    $countList->execute([$start_date , $end_date , $value['user_id']]);
                                                    $countList = $countList->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                    $efficiency = round($value['total_efficiency'] / count($countList), 2);
                                                    $data[] = ["name" => $user['first_name'] . ' ' . $user['last_name'] , "efficiency" => $efficiency ];

                                                }

                                                usort($data, function ($a, $b) {
                                                    return $b['efficiency'] <=> $a['efficiency'];
                                                });

                                                foreach ($data as $item){
                                                    echo
                                                    ' <tr>
                                                        <td>' . ++$i . '</td>
                                                        <td>' . $item['name'] .'</td>
                                                        <td>' . $item['efficiency'] . '%</td>
                                                    </tr>';
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

                <div class="col-lg-4">
                    <div class="card card-h-100">
                        <div class="card-body">
                            <h4 class="mb-4 mt-0 card-title">Best Performance OverAll in QA</h4>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Name</th>
                                                    <th>Efficency</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                <?php
                                                $toplist = $conn->prepare("SELECT user_id, SUM(efficiency) AS total_efficiency FROM efficiency WHERE `profile` = 'qa' GROUP BY user_id ORDER BY total_efficiency DESC");
                                                $toplist->execute();
                                                $toplist = $toplist->fetchAll(PDO::FETCH_ASSOC);
                                                $i = 0;
                                                
                                                $data = [];
                                                foreach ($toplist as $value) {
                                                    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                                    $user->execute([$value['user_id']]);
                                                    $user = $user->fetch(PDO::FETCH_ASSOC);
                                                
                                                    $countList = $conn->prepare("SELECT * FROM efficiency WHERE `user_id` = ?");
                                                    $countList->execute([$value['user_id']]);
                                                    $countList = $countList->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                    $efficiency = round($value['total_efficiency'] / count($countList), 2);
                                                    $data[] = ["name" => $user['first_name'] . ' ' . $user['last_name'] , "efficiency" => $efficiency ];

                                                }

                                                usort($data, function ($a, $b) {
                                                    return $b['efficiency'] <=> $a['efficiency'];
                                                });

                                                foreach ($data as $item){
                                                    echo
                                                    ' <tr>
                                                        <td>' . ++$i . '</td>
                                                        <td>' . $item['name'] .'</td>
                                                        <td>' . $item['efficiency'] . '%</td>
                                                    </tr>';
                                                }
                                                
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h4 class="mb-4 mt-0 card-title">Best Performance OverAll in Vector</h4>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Name</th>
                                                    <th>Efficency</th>
                                                </tr>

                                            </thead>
                                            <tbody>
                                                <?php
                                                $toplist = $conn->prepare("SELECT user_id, SUM(efficiency) AS total_efficiency FROM efficiency WHERE `profile` = 'vector' GROUP BY user_id ORDER BY total_efficiency DESC");
                                                $toplist->execute();
                                                $toplist = $toplist->fetchAll(PDO::FETCH_ASSOC);
                                                $i = 0;
                                                
                                                $data = [];
                                                foreach ($toplist as $value) {
                                                    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                                    $user->execute([$value['user_id']]);
                                                    $user = $user->fetch(PDO::FETCH_ASSOC);
                                                
                                                    $countList = $conn->prepare("SELECT * FROM efficiency WHERE `user_id` = ?");
                                                    $countList->execute([$value['user_id']]);
                                                    $countList = $countList->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                    $efficiency = round($value['total_efficiency'] / count($countList), 2);
                                                    $data[] = ["name" => $user['first_name'] . ' ' . $user['last_name'] , "efficiency" => $efficiency ];

                                                }

                                                usort($data, function ($a, $b) {
                                                    return $b['efficiency'] <=> $a['efficiency'];
                                                });

                                                foreach ($data as $item){
                                                    echo
                                                    ' <tr>
                                                        <td>' . ++$i . '</td>
                                                        <td>' . $item['name'] .'</td>
                                                        <td>' . $item['efficiency'] . '%</td>
                                                    </tr>';
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