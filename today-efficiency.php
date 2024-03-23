<?php

$page_name = 'today-efficiency';
include 'includes/header.php';

if ($roleId != 1 && !(in_array($page_name, $pageAccessList))) {
    echo '<script>window.location.href = "index.php"</script>';
}

$userslist = $conn->prepare('SELECT * FROM users WHERE `is_terminated` = 0 ORDER BY `first_name` ASC');
$userslist->execute();
$userslist = $userslist->fetchAll(PDO::FETCH_ASSOC);

$projectlist = $conn->prepare("SELECT * FROM `projects`");
$projectlist->execute();
$projectlist = $projectlist->fetchAll(PDO::FETCH_ASSOC);

$tasklist = $conn->prepare("SELECT * FROM `tasks`");
$tasklist->execute();
$tasklist = $tasklist->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
    a {
        text-decoration: none;
    }

    .col-12.col-sm-3 {
        border: 0.1px solid black;
        display: grid;
    }

    .block {
        padding: 10px;
    }

    hr {
        margin: 0;
    }

    li {
        list-style: decimal;
        margin-bottom: 5px;
    }

    .block.taskdata {
        height: 400px;
        overflow: auto;
    }

    #project_full_eff .box {
        padding: 10px;
        width: 20%;
        box-shadow: 2px 2px 20px 0px;
    }

    .paddingbtn {
        padding: 0 10px;
    }

    #uppercont,
    #lowercont {
        display: none;
    }
</style>
<main style="margin-top: 100px;">
    <div class="container-flude px-5">
        <div class="col-xl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h4 class="card-title">User Efficiency</h4>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-lg-3 p-2">
                            <select name="user_id" class="form-control select2" id="user_id">
                                <option value="" default>Select User</option>
                                <?php
                                foreach ($userslist as $user) {
                                    echo '<option value="' . $user['id'] . '">' . $user['first_name'] . ' ' . $user['last_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-lg-3 p-2">
                            <select name="method" id="method" class="form-control">
                                <option value="today">Today</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="date">Date</option>
                                <option value="project">Project</option>
                                <option value="task">Task</option>
                            </select>
                        </div>
                        <div class="col-lg-3 p-2">
                            <select name="product_id" class="form-control" id="product_id" style="display:none">
                                <option value="" default>Select Project</option>
                                <?php
                                foreach ($projectlist as $value) {
                                    echo '<option value="' . $value['id'] . '">' . $value['project_name'] . '</option>';
                                }
                                ?>
                            </select>
                            <div id="msg_task_id" style="display:none;">
                                <select name="task_id" id="task_id" class="form-control select2" style="width: 100%;">
                                    <option value="" default>Select Task</option>
                                    <?php
                                    foreach ($tasklist as $value) {
                                        echo '<option value="' . $value['task_id'] . '">' . $value['task_id'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div id="search_by_date" style="display:none;">
                                <input type="date" class="form-control" name="start_date" id="search_start_date">
                                <input type="date" class="form-control" name="end_date" id="search_end_date">
                            </div>
                        </div>
                        <div class="col-lg-3" style="display: flex; align-items: center;justify-content: space-evenly;">
                            <button class="btn btn-primary" id="search-btn" style=" width: 40%;">Search <i class="fa-solid fa-magnifying-glass ms-2"></i></button>
                            <button class="btn btn-primary" id="download-btn" style=" width: 50%;">Download<i class="fa-solid fa-cloud-arrow-down ms-2"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="container-flude px-5" id="project_full_eff" style="display:none">
        <div class="card flex-fill">
            <div class="card-header">
            </div>
            <div class="card-body d-flex justify-content-sm-between">
                <div class="box">
                    <p>PRO Area: <span class="pro_area"></span> Sqkm.</p>
                    <p>PRO Taken: <span class="pro_eff"></span> hr.</p>
                    <p>PRO Total: <span class="pro_eff_total"></span> hr.</p>
                    <p>PRO Effi.: <span class="pro_eff_cal"></span>%</p>
                </div>
                <div class="box">
                    <p>QC Area: <span class="qc_area"></span> Sqkm.</p>
                    <p>QC Taken: <span class="qc_eff"></span> hr.</p>
                    <p>QC Total: <span class="qc_eff_total"></span> hr.</p>
                    <p>QC Effi.: <span class="qc_eff_cal"></span>%</p>
                </div>
                <div class="box">
                    <p>QA Area: <span class="qa_area"></span> Sqkm.</p>
                    <p>QA Taken: <span class="qa_eff"></span> hr.</p>
                    <p>QA Total: <span class="qa_eff_total"></span> hr.</p>
                    <p>QA Effi.: <span class="qa_eff_cal"></span>%</p>
                </div>
                <div class="box">
                    <p>Vector Area: <span class="vector_area"></span> Sqkm.</p>
                    <p>Vector Taken: <span class="vector_eff"></span> hr.</p>
                    <p>Vector Total: <span class="vector_eff_total"></span> hr.</p>
                    <p>Vector Effi.: <span class="vector_eff_cal"></span>%</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container-flude px-5" id="uppercont">
        <div class="dataView">
            <h4 id="name"></h4>
            <div class="row">
                <div class="col-12 col-sm-3">
                    <div class="block">
                        <h6>Pro</h6>
                    </div>
                    <hr>
                    <div class="block taskdata">
                        <ul id="prodata">

                        </ul>
                    </div>
                    <hr>
                    <div class="block totalprodata">
                        <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                        <p>Total Area Lkm : <span class="area_lkm"></span></p>
                        <p>Total Time : <span class="total_time"></span></p>
                        <p>Taken Time : <span class="taken_time"></span></p>
                        <p>Total Efficiency : <span class="efficiency"></span></p>
                    </div>
                </div>
                <div class="col-12 col-sm-3">
                    <div class="block">
                        <h6>Qc</h6>
                    </div>
                    <hr>
                    <div class="block taskdata">
                        <ul id="qcdata">

                        </ul>
                    </div>
                    <hr>
                    <div class="block totalqcdata">
                        <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                        <p>Total Area Lkm : <span class="area_lkm"></span></p>
                        <p>Total Time : <span class="total_time"></span></p>
                        <p>Taken Time : <span class="taken_time"></span></p>
                        <p>Total Efficiency : <span class="efficiency"></span></p>
                    </div>
                </div>
                <div class="col-12 col-sm-3">
                    <div class="block">
                        <h6>Qa</h6>
                    </div>
                    <hr>
                    <div class="block taskdata">
                        <ul id="qadata">

                        </ul>
                    </div>
                    <hr>
                    <div class="block totalqadata">
                        <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                        <p>Total Area Lkm : <span class="area_lkm"></span></p>
                        <p>Total Time : <span class="total_time"></span></p>
                        <p>Taken Time : <span class="taken_time"></span></p>
                        <p>Total Efficiency : <span class="efficiency"></span></p>
                    </div>
                </div>
                <div class="col-12 col-sm-3">
                    <div class="block">
                        <h6>Vector</h6>
                    </div>
                    <hr>
                    <div class="block taskdata">
                        <ul id="vectordata">

                        </ul>
                    </div>
                    <hr>
                    <div class="block totalvectordata">
                        <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                        <p>Total Area Lkm : <span class="area_lkm"></span></p>
                        <p>Total Time : <span class="total_time"></span></p>
                        <p>Taken Time : <span class="taken_time"></span></p>
                        <p>Total Efficiency : <span class="efficiency"></span></p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6">
                    <div class="tex">
                        <h4>Taken Active Time : <span id="total_active_time"></span></h4>
                    </div>
                    <div class="tex">
                        <h4>Total Task Time : <span id="total_working_time"></span></h4>
                    </div>
                    <div class="tex">
                        <h4>Total Taken Time : <span id="total_taken_time"></span></h4>
                    </div>
                    <div class="tex">
                        <h4>Total Break Time : <span id="total_break_time"></span></h4>
                    </div>
                    <div class="tex">
                        <h4>Total Ideal Time : <span id="total_remaning_time"></span></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="tex">
                        <h4 id="preparation_time">Preparation Time : </h4>
                    </div>
                    <div class="tex">
                        <h4 id="finalization_time">Finalization Time : </h4>
                    </div>
                    <div class="tex">
                        <h4 id="feedback_time">Feedback Time : </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-flude px-5" id="lowercont">
        <div class="col-xl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <!-- <h4 class="card-title">Total Efficiency</h4> -->
                </div>
                <div class="card-body">
                    <table id="myTable" class="display">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Taken Time</th>
                                <th>Total Time</th>
                                <th>Role</th>
                                <th>Efficiency</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                        </tbody>
                    </table>
                    <div class="dataView" style="display : none">
                        <h4 id="name"></h4>
                        <div class="row">
                            <div class="col-12 col-sm-3">
                                <div class="block">
                                    <h6>Pro</h6>
                                </div>
                                <hr>
                                <div class="block taskdata">
                                    <ul id="prodata">

                                    </ul>
                                </div>
                                <hr>
                                <div class="block totalprodata">
                                    <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                                    <p>Total Area Lkm : <span class="area_lkm"></span></p>
                                    <p>Total Time : <span class="total_time"></span></p>
                                    <p>Taken Time : <span class="taken_time"></span></p>
                                    <p>Total Efficiency : <span class="efficiency"></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div class="block">
                                    <h6>Qc</h6>
                                </div>
                                <hr>
                                <div class="block taskdata">
                                    <ul id="qcdata">

                                    </ul>
                                </div>
                                <hr>
                                <div class="block totalqcdata">
                                    <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                                    <p>Total Area Lkm : <span class="area_lkm"></span></p>
                                    <p>Total Time : <span class="total_time"></span></p>
                                    <p>Taken Time : <span class="taken_time"></span></p>
                                    <p>Total Efficiency : <span class="efficiency"></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div class="block">
                                    <h6>Qa</h6>
                                </div>
                                <hr>
                                <div class="block taskdata">
                                    <ul id="qadata">

                                    </ul>
                                </div>
                                <hr>
                                <div class="block totalqadata">
                                    <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                                    <p>Total Area Lkm : <span class="area_lkm"></span></p>
                                    <p>Total Time : <span class="total_time"></span></p>
                                    <p>Taken Time : <span class="taken_time"></span></p>
                                    <p>Total Efficiency : <span class="efficiency"></span></p>
                                </div>
                            </div>
                            <div class="col-12 col-sm-3">
                                <div class="block">
                                    <h6>Vector</h6>
                                </div>
                                <hr>
                                <div class="block taskdata">
                                    <ul id="vectordata">

                                    </ul>
                                </div>
                                <hr>
                                <div class="block totalvectordata">
                                    <p>Total Area Sqkm : <span class="area_sqkm"></span></p>
                                    <p>Total Area Lkm : <span class="area_lkm"></span></p>
                                    <p>Total Time : <span class="total_time"></span></p>
                                    <p>Taken Time : <span class="taken_time"></span></p>
                                    <p>Total Efficiency : <span class="efficiency"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="tex">
                                    <h4 id="total_active_time">Taken Active Time : </h4>
                                </div>
                                <div class="tex">
                                    <h4 id="total_working_time">Total Task Time : </h4>
                                </div>
                                <div class="tex">
                                    <h4 id="total_worked_time">Task Taken Time : </h4>
                                </div>
                                <div class="tex">
                                    <h4 id="total_remaning_time">Total Ideal Time : </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php' ?>
<script>
    $(".select2").select2();
    $(".select2-selection__rendered").addClass("form-control");
    $(".select2-selection--single").css("border", "0");

    $('#method').change(() => {
        var method = $('#method').val()
        if (method == 'today' || method == 'monthly') {
            $('#product_id').css('display', 'none');
            $('#msg_task_id').css('display', 'none');
            $('#search_by_date').css('display', 'none');
        }

        if (method == 'project') {
            $('#product_id').css('display', 'block');
            $('#msg_task_id').css('display', 'none');
            $('#search_by_date').css('display', 'none');
        }

        if (method == 'task') {
            $('#msg_task_id').css('display', 'block');
            $('#product_id').css('display', 'none');
            $('#search_by_date').css('display', 'none');
        }

        if (method == 'date') {
            $('#msg_task_id').css('display', 'none');
            $('#product_id').css('display', 'none');
            $('#search_by_date').css('display', 'flex');
        }
    });

    // function getFullProjectEfficiency(user_id , project_id){
    //     $.ajax({
    //         url: 'includes/settings/api/projectEfficiencyApi.php',
    //         data: {
    //             type: 'getFullProjectEfficiency',
    //             project_id: project_id,
    //             user_id : user_id
    //         },
    //         dataType: 'json',
    //         success: function (response) {
    //         }
    //     });
    // }

    function convertMinutesToHoursAndMinutes(minutes) {
        if (isNaN(minutes) || minutes < 0) {
            return "0 m";
        }

        const hours = Math.floor(minutes / 60);
        const remainingMinutes = Math.round(minutes % 60);

        if (hours === 0) {
            return `${remainingMinutes}m`;
        } else if (remainingMinutes === 0) {
            return `${hours}h`;
        } else {
            return `${hours}h ${remainingMinutes}m`;
        }
    }

    function clearOldData() {
        $('.totalprodata .total_time').text('');
        $('.totalprodata .taken_time').text('');
        $('.totalprodata .area_sqkm').text('');
        $('.totalprodata .efficiency').text('');

        $('.totalqcdata .total_time').text('');
        $('.totalqcdata .taken_time').text('');
        $('.totalqcdata .area_sqkm').text('');
        $('.totalqcdata .efficiency').text('');

        $('.totalqadata .total_time').text('');
        $('.totalqadata .taken_time').text('');
        $('.totalqadata .area_sqkm').text('');
        $('.totalqadata .efficiency').text('');

        $('.totalvectordata .total_time').text('');
        $('.totalvectordata .taken_time').text('');
        $('.totalvectordata .area_sqkm').text('');
        $('.totalvectordata .efficiency').text('');

        $('#total_active_time').text('');
        $('#total_break_time').text('');
        $('#total_taken_time').text('');
        $('#total_remaning_time').text('');
        $('#prodata').html('');
        $('#qcdata').html('');
        $('#qadata').html('');
        $('#vectordata').html('');
    }

    $("#search-btn").click(() => {
        $('#project_full_eff').css('display', 'none');
        var method = $('#method').val()
        var user_id = $("#user_id").val();
        var task_id = $("#task_id").val();
        var product_id = $("#product_id").val();
        var start_date = $('#search_start_date').val();
        var end_date = $('#search_end_date').val();

        clearOldData();

        if (method == 'today' || method == 'monthly' || method == 'yearly' || method == 'date') {
            $("#uppercont").css('display', 'block');
            $("#lowercont").css('display', 'none');
            if (user_id != '') {
                $.ajax({
                    url: 'includes/settings/api/efficiencyAPi.php',
                    data: {
                        type: 'getEfficiency',
                        user_id: user_id,
                        method: method,
                        start_date: start_date,
                        end_date: end_date,
                        task_id: task_id,
                        project_id: product_id
                    },
                    dataType: 'json',
                    success: function(response) {
                        var pro = response.dataTask.pro;
                        var qc = response.dataTask.qc;
                        var qa = response.dataTask.qa;
                        var vector = response.dataTask.vector;
                        var feedback_time = response.project_time.feedback;
                        var finalization_time = response.project_time.finalization;
                        var preparation_time = response.project_time.preparation;
                        $('#preparation_time').text('Preparation Time : ' + convertMinutesToHoursAndMinutes(preparation_time));
                        $('#finalization_time').text('Finalization Time : ' + convertMinutesToHoursAndMinutes(finalization_time));
                        $('#feedback_time').text('Feedback Time : ' + convertMinutesToHoursAndMinutes(feedback_time));

                        var protime = 0;
                        var qctime = 0;
                        var qatime = 0;
                        var vectortime = 0;

                        var prototaltime = 0;
                        var qctotaltime = 0;
                        var qatotaltime = 0;
                        var vectortotaltime = 0;

                        var prototalsqkm = 0;
                        var qctotalsqkm = 0;
                        var qatotalsqkm = 0;
                        var vectortotalsqkm = 0;

                        var prototallkm = 0;
                        var qctotallkm = 0;
                        var qatotallkm = 0;
                        var vectortotallkm = 0;
                        // for pro data
                        if (pro !== undefined) {
                            pro.forEach(element => {
                                $('#prodata').append(`<li> ${element.task_id} <button class="btn btn-success paddingbtn">${element.percentage}</button></li>`);
                                protime += element.task_time;
                                prototaltime += element.time;
                                prototalsqkm += element.area_sqkm;
                                prototallkm += element.area_lkm;
                            });
                            $('.totalprodata .total_time').text(convertMinutesToHoursAndMinutes(protime));
                            $('.totalprodata .taken_time').text(convertMinutesToHoursAndMinutes(prototaltime));
                            $('.totalprodata .area_sqkm').text(prototalsqkm + ' Sqkm');
                            $('.totalprodata .area_lkm').text(prototallkm + ' Lkm');
                            $('.totalprodata .efficiency').text(((protime / prototaltime) * 100).toFixed(2) + '%');
                        }

                        // for qc data
                        if (qc !== undefined) {
                            qc.forEach(element => {
                                $('#qcdata').append(`<li> ${element.task_id} <button class="btn btn-success paddingbtn">${element.percentage}</button></li>`);
                                qctime += element.task_time;
                                qctotaltime += element.time;
                                qctotalsqkm += element.area_sqkm;
                                qctotallkm += element.area_lkm;
                            });
                            $('.totalqcdata .total_time').text(convertMinutesToHoursAndMinutes(qctime));
                            $('.totalqcdata .taken_time').text(convertMinutesToHoursAndMinutes(qctotaltime));
                            $('.totalqcdata .area_sqkm').text(qctotalsqkm + ' Sqkm');
                            $('.totalqcdata .area_lkm').text(qctotallkm + ' Lkm');
                            $('.totalqcdata .efficiency').text(((qctime / qctotaltime) * 100).toFixed(2) + '%');
                        }

                        // for qa data
                        if (qa !== undefined) {
                            qa.forEach(element => {
                                $('#qadata').append(`<li> ${element.task_id} <button class="btn btn-success paddingbtn">${element.percentage}</button></li>`);
                                qatime += element.task_time;
                                qatotaltime += element.time;
                                qatotalsqkm += element.area_sqkm;
                                qatotallkm += element.area_lkm;
                            });
                            $('.totalqadata .total_time').text(convertMinutesToHoursAndMinutes(qatime));
                            $('.totalqadata .taken_time').text(convertMinutesToHoursAndMinutes(qatotaltime));
                            $('.totalqadata .area_sqkm').text(qatotalsqkm + ' Sqkm');
                            $('.totalqadata .area_lkm').text(qatotallkm + ' Lkm');
                            $('.totalqadata .efficiency').text(((qatime / qatotaltime) * 100).toFixed(2) + '%');
                        }

                        // for vector data
                        if (vector !== undefined) {
                            vector.forEach(element => {
                                $('#vectordata').append(`<li> ${element.task_id} <button class="btn btn-success paddingbtn">${element.percentage}</button></li>`);
                                vectortime += element.task_time;
                                vectortotaltime += element.time;
                                vectortotalsqkm += element.area_sqkm;
                                vectortotallkm += element.area_lkm;
                            });
                            $('.totalvectordata .total_time').text(convertMinutesToHoursAndMinutes(vectortime));
                            $('.totalvectordata .taken_time').text(convertMinutesToHoursAndMinutes(vectortotaltime));
                            $('.totalvectordata .area_sqkm').text(vectortotalsqkm + ' Sqkm');
                            $('.totalvectordata .area_lkm').text(vectortotallkm + ' Lkm');
                            $('.totalvectordata .efficiency').text(((vectortime / vectortotaltime) * 100).toFixed(2) + '%');
                        }

                        $('#total_active_time').text(convertMinutesToHoursAndMinutes(response.active_time));
                        $('#total_break_time').text(convertMinutesToHoursAndMinutes(response.break));
                        $('#total_taken_time').text(convertMinutesToHoursAndMinutes(response.task));
                        $('#total_remaning_time').text(convertMinutesToHoursAndMinutes(response.active_time - response.task - response.break - preparation_time - finalization_time - feedback_time));
                        $('#total_working_time').text(convertMinutesToHoursAndMinutes(qatime + qctime + protime + vectortime));
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                        notyf.error(errorMessage);
                    }
                });
            } else {
                notyf.error('Select User First.');
            }
        } else {
            $("#uppercont").css('display', 'none');
            $("#lowercont").css('display', 'block');
            $.ajax({
                url: 'includes/settings/api/efficiencyAPi.php',
                data: {
                    type: 'getEfficiency',
                    user_id: user_id,
                    method: method,
                    start_date: start_date,
                    end_date: end_date,
                    task_id: task_id,
                    project_id: product_id
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    var table = $('#myTable').DataTable();
                    table.clear().draw();
                    response.forEach(element => {
                        var efficiency = 0;
                        if (element.efficiency > 100) {
                            efficiency = 100;
                        } else {
                            efficiency = element.efficiency;
                        }

                        if (efficiency > 50) {
                            var progress = `<div class="progress" role="progressbar" aria-label="Success  striped example" aria-valuenow="${element.efficiency}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success" style="width: ${element.efficiency}%">${element.efficiency}%</div></div>`;
                        } else {
                            var progress = `<div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="${element.efficiency}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-danger " style="width: ${element.efficiency}%">${element.efficiency}%</div></div>`;
                        }
                        var rowData = [
                            element.area_sqkm + 'sqkm' + ' (#' + element.task_id + ')',
                            element.project_name + ' (#' + element.project_id + ')',
                            element.taken_time + ' M',
                            element.total_time + ' M',
                            element.profile,
                            progress,
                            '<a target="_blank" href="view-efficiency.php?task_id=' + element.task_id + '"><i class="fas fa-eye"></i> view</a>'
                        ];
                        table.row.add(rowData).draw();
                    });
                    // if(method == 'project'){
                    //     getFullProjectEfficiency(user_id,product_id);
                    // }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                    notyf.error(errorMessage);
                }
            });
        }
    });

    $('#download-btn').click(() => {
        var user_id = $("#user_id").val();
        var method = $("#method").val();
        var start_date = $('#search_start_date').val();
        var end_date = $("#search_end_date").val();
        var product_id = $("#product_id").val();
        var task_id = $("#task_id").val();
        $.ajax({
            url: 'includes/settings/api/efficiencyAPi.php',
            data: {
                type: 'getProjectEfficiency',
                user_id: user_id,
                method: method,
                product_id: product_id,
                task_id: task_id,
                end_date: end_date,
                start_date: start_date
            },
            dataType: 'json',
            success: function(response) {
                const extractedDataArray = [];
                const extractedData = {
                    "first_name": "First Name",
                    "last_name": "Last Name",
                    "task_id": "Task Id",
                    "area_sqkm": "Area Sqkm",
                    "project_id": "Project Id",
                    "project_name": "Project Name",
                    "total_efficiency": "Total Efficiency"
                };
                extractedDataArray.push(extractedData);
                console.log(response);
                for (const data of response) {
                    const extractedData = {
                        "first_name": data.first_name,
                        "last_name": data.last_name,
                        "task_id": data.task_id,
                        "area_sqkm": data.task_name,
                        "project_id": data.project_id,
                        "project_name": data.project_name,
                        "total_efficiency": data.efficiency
                    };
                    extractedDataArray.push(extractedData);
                }
                // console.log(extractedDataArray);
                downloadExcel(extractedDataArray);
            }
        });
    });

    function downloadExcel(data) {
        $.ajax({
            url: "includes/settings/downloadExcel.php",
            type: 'POST',
            data: {
                data: data
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(result) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(result);
                a.href = url;
                a.download = "example.xlsx"; // Set the desired file name
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                notyf.success("Excel File Download SuccessFull");
            }
        });
    }
</script>