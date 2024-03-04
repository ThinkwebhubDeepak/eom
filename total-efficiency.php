 <?php

$page_name = 'total-efficiency';
include 'includes/header.php';

if ($roleId != 1 && !(in_array($page_name, $pageAccessList))) {
    echo '<script>window.location.href = "index.php"</script>';
}

$data = $conn->prepare("SELECT `project_id`, `user_id`, `efficiency`, `task_id`, `profile`, `taken_time`, `total_time`, `created_at` FROM `efficiency` ORDER BY `created_at` DESC LIMIT 1000");
$data->execute();
$data = $data->fetchAll(PDO::FETCH_ASSOC);

function convertMinutesToHoursAndMinutes($minutes)
{
    $hours = floor($minutes / 60);
    $remainingMinutes = round($minutes % 60 , 2);
    if ($hours != 0) {
        return sprintf('%d h %d m', $hours, $remainingMinutes);
    } else {
        return round($minutes , 2).'m';
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
<main style="margin-top: 100px;">
    <div class="container-flude px-5" id="lowercont">
        <div class="col-xl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <select name="project" id="projectSelect" class="form-control" style="padding :15px;">
                        <option value="">Select Project</option>
                        <?php
                        $projectList = $conn->prepare("SELECT `project_name` FROM `projects`");
                        $projectList->execute();
                        $projectList = $projectList->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($projectList as $project) {
                            echo '<option value="' . $project['project_name'] . '">' . $project['project_name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="card-body">
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
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                            <?php
                            $i = 0;
                            foreach ($data as $value) {
                                // $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?");
                                // $task->execute([$value['task_id'], $value['project_id']]);
                                // $task = $task->fetch(PDO::FETCH_ASSOC);

                                $project = $conn->prepare("SELECT `id`, `project_name` FROM `projects` WHERE `id` = ?");
                                $project->execute([$value['project_id']]);
                                $project = $project->fetch(PDO::FETCH_ASSOC);

                                $user = $conn->prepare("SELECT `profile` , `first_name` , `id` ,`last_name` FROM `users` WHERE `id` = ?");
                                $user->execute([$value['user_id']]);
                                $user = $user->fetch(PDO::FETCH_ASSOC);

                                if ($value['efficiency'] > 50) {
                                    $progress = '<div class="progress" role="progressbar" aria-label="Success  striped example" aria-valuenow="' . $value['efficiency'] . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success" style="width: ' . $value['efficiency'] . '%">' . $value['efficiency'] . '%</div></div>';
                                } else {
                                    $progress = '<div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="' . $value['efficiency'] . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-danger " style="width: ' . $value['efficiency'] . '%">' . $value['efficiency'] . '%</div></div>';
                                }

                                $work = $conn->prepare("SELECT `created_at` FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = ? AND `user_id` = ?");
                                $work->execute([$value['task_id'] , $value['project_id'] , 'assign_'.$value['profile'] , $user['id']]);
                                $work = $work->fetch(PDO::FETCH_ASSOC);

                                echo '
                                        <tr>
                                            <td>' . ++$i . '</td>
                                            <td><img loading="lazy" class="img-border" src="images/users/' . ($user['profile'] == '' ? 'default.jpg' : $user['profile']) . '" width="25px" heigth="25px">' . $user['first_name'] . ' ' . $user['last_name'] . '</td>
                                            <td>' . $value['task_id'] . '</td>
                                            <td>' . $project['project_name'] . '</td>
                                            <td>' . convertMinutesToHoursAndMinutes($value['taken_time']) . '</td>
                                            <td>' . convertMinutesToHoursAndMinutes($value['total_time']) . '</td>
                                            <td>' . $value['profile'] . '</td>
                                            <td>' . $progress . '</td>
                                            <td>' . date("j M Y, g:i A", strtotime($work['created_at'])) . '</td>
                                            <td>' . date("j M Y, g:i A", strtotime($value['created_at'])) . '</td>
                                            <td><a target="_blank" href="view-efficiency.php?task_id=' . $value['task_id'] . '">view</a></td>
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
</main>
<?php
include 'includes/footer.php';
?>
<script>
    $('#projectSelect').change(function() {
        var projectInput, projectValue;
        projectInput = document.getElementById("projectSelect");
        projectValue = projectInput.value;
        dataTable.column(3).search(projectValue).draw();
    });
</script>