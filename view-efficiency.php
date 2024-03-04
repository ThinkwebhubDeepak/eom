<?php

$current_page = 'total-efficiency';
$title = 'View Efficiency || EOM ';
include 'includes/header.php';

$task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ?");
$task->execute([$_GET['task_id']]);
$task = $task->fetch(PDO::FETCH_ASSOC);


$percentage=[];
$hour=[];

// $work_logs = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? ORDER BY `id` ASC");
// $work_logs->execute([$_GET['task_id']]);
// $work_logs = $work_logs->fetchAll(PDO::FETCH_ASSOC);

?>

    <style>
    body {
        margin-top: 20px;
    }

    .timeline_area {
        position: relative;
        z-index: 1;
    }

    .single-timeline-area {
        position: relative;
        z-index: 1;
        padding-left: 180px;
    }

    @media only screen and (max-width: 575px) {
        .single-timeline-area {
            padding-left: 100px;
        }
    }

    .single-timeline-area .timeline-date {
        position: absolute;
        width: 180px;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 1;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        -ms-grid-row-align: center;
        align-items: center;
        -webkit-box-pack: end;
        -ms-flex-pack: end;
        justify-content: flex-end;
        padding-right: 60px;
    }

    @media only screen and (max-width: 575px) {
        .single-timeline-area .timeline-date {
            width: 100px;
        }
    }

    .single-timeline-area .timeline-date::after {
        position: absolute;
        width: 3px;
        height: 100%;
        content: "";
        background-color: #ebebeb;
        top: 0;
        right: 30px;
        z-index: 1;
    }

    .single-timeline-area .timeline-date::before {
        position: absolute;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background-color: #0d6efd;
        content: "";
        top: 50%;
        right: 26px;
        z-index: 5;
        margin-top: -5.5px;
    }

    .single-timeline-area .timeline-date p {
        margin-bottom: 0;
        color: #0d6efd;
        font-size: 13px;
        text-transform: uppercase;
        font-weight: 500;
    }

    .single-timeline-area .single-timeline-content {
        position: relative;
        z-index: 1;
        padding: 30px 30px 25px;
        border-radius: 6px;
        margin-bottom: 15px;
        margin-top: 15px;
        -webkit-box-shadow: 0 0.25rem 1rem 0 rgba(47, 91, 234, 0.125);
        box-shadow: 0 0.25rem 1rem 0 rgba(47, 91, 234, 0.125);
        border: 1px solid #ebebeb;
        transition:  0.5s;
    }
    .single-timeline-area .single-timeline-content:hover{
        transform:scale(1.1);
    }

    @media only screen and (max-width: 575px) {
        .single-timeline-area .single-timeline-content {
            padding: 20px;
        }
    }

    .single-timeline-area .single-timeline-content .timeline-icon {
        -webkit-transition-duration: 500ms;
        transition-duration: 500ms;
        width: 30px;
        height: 30px;
        /* background-color: #0d6efd; */
        line-height: 10px;
        -webkit-box-flex: 0;
        -ms-flex: 0 0 30px;
        flex: 0 0 30px;
        text-align: center;
        max-width: 30px;
        border-radius: 50%;
        margin-right: 15px;
        line-height: 28px !important;
    }

    .single-timeline-area .single-timeline-content .timeline-icon i {
        color: #ffffff;
       
    }

    .single-timeline-area .single-timeline-content .timeline-text h6 {
        -webkit-transition-duration: 500ms;
        transition-duration: 500ms;
    }

    .single-timeline-area .single-timeline-content .timeline-text p {
        font-size: 13px;
        margin-bottom: 0;
    }

    .single-timeline-area .single-timeline-content:hover .timeline-icon,
    .single-timeline-area .single-timeline-content:focus .timeline-icon {
        background-color: #020710;
        color: #fff;
    }

    .single-timeline-area .single-timeline-content:hover .timeline-text h6,
    .single-timeline-area .single-timeline-content:focus .timeline-text h6 {
        color: #3f43fd;
    }
    </style>
    <main style="margin-top: 100px;">
        <div class="container ">

            <section class="timeline_area section_padding_130">
                <div class="container-flude">
                    <div class="row justify-content-center px-1">
                        <div class="col-12 col-sm-8 col-lg-6">
                            <!-- Section Heading-->
                            <div class="section_heading text-center">
                                <h2 class="page_heading my-3">View Efficiency</h2>
                                <h3>Task #<?php echo $_GET['task_id']?></h3>
                                <div class="line"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <!-- Timeline Area-->
                            <div class="apland-timeline-area">
                                <!-- Single Timeline Content-->
                                <?php
                                $i = 1;
                                $temp_id = 0;
                                while (true) {
                                $work_logs = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `id` > ? AND `prev_status` != 'vector_in_progress' AND `prev_status` != 'assign_vector' ORDER BY `id` ASC");
                                $work_logs->execute([$_GET['task_id'], $temp_id]);
                                $work_logs = $work_logs->fetchAll(PDO::FETCH_ASSOC);

                                // print_r($work_logs);
                                
                                if (empty($work_logs)) {
                                    break; // Exit the loop if there are no more work logs
                                }
                                
                                // echo '<div class="single-timeline-area">';
                                foreach ($work_logs as $work_log) {

                                    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ? ");
                                    $user->execute([$work_log['user_id']]);
                                    $user = $user->fetch(PDO::FETCH_ASSOC);
                                    $temp_id = $work_log['id'];

                                    if ($work_log['change_type'] != '') {
                                        echo '</div></div>';
                                        if ($work_log['prev_status'] == 'in_progress' && $work_log['change_type'] == 'ressigned') {
                                            echo '<div class="single-timeline-area"><div class="timeline-date wow fadeInLeft" data-wow-delay="0.1s"
                                                style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInLeft;">
                                                <p>Resign Task (' . $user['first_name'] . ' ' . $user['last_name'] . ')</p>
                                            </div>
                                            <div class="row">';
                                        }else if($work_log['prev_status'] == 'in_progress' && $work_log['change_type'] == 'qc_failure_ressignment'){
                                            echo '<div class="single-timeline-area"><div class="timeline-date wow fadeInLeft" data-wow-delay="0.1s"
                                                style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInLeft;">
                                                <p>QC Failure ReAssign (' . $user['first_name'] . ' ' . $user['last_name'] . ')</p>
                                            </div>
                                            <div class="row">';
                                        }else if($work_log['prev_status'] == 'in_progress' && $work_log['change_type'] == 'qa_failure_ressignment'){
                                            echo '<div class="single-timeline-area"><div class="timeline-date wow fadeInLeft" data-wow-delay="0.1s"
                                                style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInLeft;">
                                                <p>QA Failure ReAssign (' . $user['first_name'] . ' ' . $user['last_name'] . ')</p>
                                            </div>
                                            <div class="row">';
                                        }
                                        break; // Skip this entry if change_type is not empty
                                    }

                                    if($work_log['prev_status'] == 'assign_qc'){
                                        echo '</div></div>';
                                        echo '<div class="single-timeline-area"><div class="timeline-date wow fadeInLeft" data-wow-delay="0.1s"
                                                style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInLeft;">
                                                <p>QC Assign (' . $user['first_name'] . ' ' . $user['last_name'] . ')</p>
                                            </div>
                                            <div class="row">';
                                    }
                                    
                                    if($work_log['prev_status'] == 'assign_qa'){
                                        echo '</div></div>';
                                        echo '<div class="single-timeline-area"><div class="timeline-date wow fadeInLeft" data-wow-delay="0.1s"
                                                style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInLeft;">
                                                <p>QA Assign (' . $user['first_name'] . ' ' . $user['last_name'] . ')</p>
                                            </div>
                                            <div class="row">';
                                    }
                                    

                                    if ($work_log['prev_status'] == 'assign_pro') {
                                        echo '<div class="single-timeline-area"><div class="timeline-date wow fadeInLeft" data-wow-delay="0.1s"
                                            style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInLeft;">
                                            <p>Start Task (' . $user['first_name'] . ' ' . $user['last_name'] . ')</p>
                                        </div><div class="row gap-2">';
                                    } 
                                    
                                    
                                    if($work_log['taken_time']){
                                    // Display other timeline entries
                                    echo '<div class="col-12 col-md-6 col-lg-4">
                                        <div class="single-timeline-content d-flex wow fadeInLeft" data-wow-delay="0.3s"
                                            style="visibility: visible; animation-delay: 0.3s; animation-name: fadeInLeft;">
                                            <div class="timeline-icon"><i class="fa-solid fa-bars-progress"></i></div>
                                            <div class="timeline-text">
                                                <h6>' . str_replace('_',' ',(strtoupper($work_log['prev_status']))) . '</h6>
                                                <p>Percentage : ' . $work_log['work_percentage'] . '%</p>
                                                <p>Time : ' . $work_log['taken_time'] . ' M</p>
                                                <p>Remark : ' . $work_log['remarks'] . '.</p>
                                            </div>
                                        </div>
                                    </div>';
                                    }
                                    $i++;
                                    }
                                }
                                // echo json_encode($percentage);
                                // echo json_encode($hour);
                                ?>

                                

                                <?php
                                    $work_logs = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND (`prev_status` = 'vector_in_progress' OR `prev_status` = 'assign_vector') ORDER BY `id` ASC");
                                    $work_logs->execute([$_GET['task_id']]);
                                    $work_logs = $work_logs->fetchAll(PDO::FETCH_ASSOC);

                                    foreach($work_logs as $work_log){

                                        $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ? ");
                                        $user->execute([$work_log['user_id']]);
                                        $user = $user->fetch(PDO::FETCH_ASSOC);
                                        $temp_id = $work_log['id'];

                                        if($work_log['prev_status'] == 'assign_vector'){
                                            echo '</div></div>';
                                            echo '<div class="single-timeline-area"><div class="timeline-date wow fadeInLeft" data-wow-delay="0.1s"
                                                    style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInLeft;">
                                                    <p>Vector Assign (' . $user['first_name'] . ' ' . $user['last_name'] . ')</p>
                                                </div>
                                                <div class="row">';
                                        }else{
                                                echo '<div class="col-12 col-md-6 col-lg-4">
                                                <div class="single-timeline-content d-flex wow fadeInLeft" data-wow-delay="0.3s"
                                                    style="visibility: visible; animation-delay: 0.3s; animation-name: fadeInLeft;">
                                                    <div class="timeline-icon"><i class="fa-solid fa-bars-progress"></i></div>
                                                    <div class="timeline-text">
                                                        <h6>' . str_replace('_',' ',(strtoupper($work_log['prev_status']))) . '</h6>
                                                        <p>Percentage : ' . $work_log['work_percentage'] . '%</p>
                                                        <p>Time : ' . $work_log['taken_time'] . ' M</p>
                                                        <p>Remark : ' . $work_log['remarks'] . '.</p>
                                                    </div>
                                                </div>
                                            </div>';
                                        }
                                    }

                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <?php include 'includes/footer.php' ?>
    <script>
        var percentages = <?php echo json_encode($percentage); ?>;
        var hour = <?php echo json_encode($hour); ?>;
        var total_orignal_time = <?php echo $task['estimated_hour'] ?>;
        const totalPercentage = percentages.reduce((sum, percentage) => sum + percentage, 0);
        var time = percentages.map(percentage => (percentage / 100) * total_orignal_time);
        
        const efficiencyArray = [];

        for (let i = 0; i < time.length; i++) {
            const efficiency = parseFloat(((time[i] / hour[i]) * 100).toFixed(2));
            efficiencyArray.push(efficiency);
        }

        // console.log(percentages);
        console.log(hour);
        console.log(time);
        console.log(efficiencyArray);
    </script>
</body>

</html>