<?php

$page_name = 'my-efficiency';
include 'includes/header.php';
$data = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ? ORDER BY `created_at` DESC");
$data->execute([$_SESSION['userId']]);
$data = $data->fetchAll(PDO::FETCH_ASSOC);


$projectlist = $conn->prepare("SELECT * FROM `projects`");
$projectlist->execute();
$projectlist = $projectlist->fetchAll(PDO::FETCH_ASSOC);

$tasklist = $conn->prepare("SELECT * FROM `tasks`");
$tasklist->execute();
$tasklist = $tasklist->fetchAll(PDO::FETCH_ASSOC);

function convertMinutesToHoursAndMinutes($minutes)
{
    $hours = floor($minutes / 60);
    $remainingMinutes = round($minutes % 60);
    if ($hours != 0) {
        return sprintf('%d h %d m', $hours, $remainingMinutes);
    } else {
        return sprintf('%d m', $remainingMinutes);
    }
}

?>
<style>
    .img-border {
        border-radius: 50%;
        object-fit: cover;
        height: 40px;
        width: 40px;
        margin: 0 10px;
    }
</style>
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
</style>
<main style="margin-top: 100px;">
    <div class="container-flude px-5" >
        <div class="col-xl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h4 class="page_heading">Total Efficiency</h4>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-lg-3 p-2">
                            <select name="method" id="method" class="form-control">
                                <option value="all">All</option>
                                <option value="today">Today</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="date">Date</option>
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
                <div class="card-body" id="lowercont">
                    <table id="dataTable" class="display">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Taken Time</th>
                                <th>Total Time</th>
                                <th>Role</th>
                                <th>Efficiency</th>
                                <th>Date</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                            <?php
                            $i = 0;
                            foreach ($data as $value) {
                                $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?");
                                $task->execute([$value['task_id'], $value['project_id']]);
                                $task = $task->fetch(PDO::FETCH_ASSOC);

                                $project = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
                                $project->execute([$value['project_id']]);
                                $project = $project->fetch(PDO::FETCH_ASSOC);

                                $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                $user->execute([$value['user_id']]);
                                $user = $user->fetch(PDO::FETCH_ASSOC);

                                if ($value['efficiency'] > 50) {
                                    $progress = '<div class="progress" role="progressbar" aria-label="Success  striped example" aria-valuenow="' . $value['efficiency'] . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success" style="width: ' . $value['efficiency'] . '%">' . $value['efficiency'] . '%</div></div>';
                                } else {
                                    $progress = '<div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="' . $value['efficiency'] . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-danger " style="width: ' . $value['efficiency'] . '%">' . $value['efficiency'] . '%</div></div>';
                                }

                                echo '
                                        <tr>
                                            <td>' . ++$i . '</td>
                                            <td><img class="img-border" src="images/users/' . ($user['profile'] == '' ? 'default.jpg' : $user['profile']) . '" width="25px" heigth="25px">' . $user['first_name'] . ' ' . $user['last_name'] . '</td>
                                            <td>' . $task['task_id'] . '</td>
                                            <td>' . $project['project_name'] . '</td>
                                            <td>' . convertMinutesToHoursAndMinutes($value['taken_time']) . '</td>
                                            <td>' . convertMinutesToHoursAndMinutes($value['total_time']) . '</td>
                                            <td>' . $value['profile'] . '</td>
                                            <td>' . $progress . '</td>
                                            <td>' . date("j M Y, g:i A", strtotime($value['created_at'])) . '</td>
                                            <td><a target="_blank" href="view-efficiency.php?task_id=' . $task['task_id'] . '">view</a></td>
                                        </tr>
                                   ';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <div class="container-flude px-5" id="uppercont" style="display:none">
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
                    <div class="tex">
                        <h4 id="tasK_efficiency">Efficiency : </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
include 'includes/footer.php';
?>
<script>
    $('#method').change(() => {
        var method = $('#method').val()
        if(method == 'all'){
            $('#lowercont').css('display','block');
            $('#uppercont').css('display','none');
        }else{
            $('#lowercont').css('display','none');
            $('#uppercont').css('display','block');
        }
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

    function clearOldData() {
        $('.totalprodata .total_time').text('');
        $('.totalprodata .taken_time').text('');
        $('.totalprodata .area_sqkm').text('');
        $('.totalprodata .area_lkm').text('');
        $('.totalprodata .efficiency').text('');

        $('.totalqcdata .total_time').text('');
        $('.totalqcdata .taken_time').text('');
        $('.totalqcdata .area_sqkm').text('');
        $('.totalqcdata .area_lkm').text('');
        $('.totalqcdata .efficiency').text('');

        $('.totalqadata .total_time').text('');
        $('.totalqadata .taken_time').text('');
        $('.totalqadata .area_sqkm').text('');
        $('.totalqadata .area_lkm').text('');
        $('.totalqadata .efficiency').text('');

        $('.totalvectordata .total_time').text('');
        $('.totalvectordata .taken_time').text('');
        $('.totalvectordata .area_sqkm').text('');
        $('.totalvectordata .area_lkm').text('');
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

    $("#search-btn").click(() => {
        // $('#project_full_eff').css('display', 'none');
        var method = $('#method').val()
        var user_id = $("#user_id").val();
        var task_id = $("#task_id").val();
        var product_id = $("#product_id").val();
        var start_date = $('#search_start_date').val();
        var end_date = $('#search_end_date').val();

        clearOldData();

        if (method == 'today' || method == 'monthly' || method == 'yearly' || method == 'date') {
            if (user_id != '') {
                $.ajax({
                    url: 'includes/settings/api/efficiencyAPi.php',
                    data: {
                        type: 'getEfficiency',
                        user_id: <?php echo $_SESSION['userId'] ?>,
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
                        $('#total_remaning_time').text(convertMinutesToHoursAndMinutes(response.active_time - response.task - response.break));
                        $('#total_working_time').text(convertMinutesToHoursAndMinutes(qatime + qctime + protime + vectortime));
                        $('#tasK_efficiency').text('Efficiency : ' + (((qatime + qctime + protime + vectortime) / response.task ) * 100).toFixed(2)+'%');
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                        notyf.error(errorMessage);
                    }
                });
            } else {
                notyf.error('Select User First.');
            }
        }
    });
</script>