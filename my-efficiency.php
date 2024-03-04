<?php

    $page_name = 'my-efficiency';
    include 'includes/header.php';
    $data = $conn->prepare("SELECT * FROM `efficiency` WHERE `user_id` = ? ORDER BY `created_at` DESC");
    $data->execute([$_SESSION['userId']]);
    $data = $data->fetchAll(PDO::FETCH_ASSOC);

    function convertMinutesToHoursAndMinutes($minutes) {
        $hours = floor($minutes / 60);
        $remainingMinutes = round($minutes % 60);
        if($hours != 0){
            return sprintf('%d h %d m', $hours, $remainingMinutes);
        }else{
            return sprintf('%d m', $remainingMinutes);
        }
    }

?>
<style>
    .img-border{
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
                    <h4 class="page_heading">Total Efficiency</h4>
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
                                <th>Date</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                            <?php
                                $i = 0;
                                 foreach ($data as $value) {
                                    $task = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?");
                                    $task->execute([$value['task_id'] , $value['project_id']]);
                                    $task = $task->fetch(PDO::FETCH_ASSOC);
                                    
                                    $project = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
                                    $project->execute([$value['project_id']]);
                                    $project = $project->fetch(PDO::FETCH_ASSOC);
                                    
                                    $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                    $user->execute([$value['user_id']]);
                                    $user = $user->fetch(PDO::FETCH_ASSOC);

                                    if ($value['efficiency'] > 50) {
                                        $progress = '<div class="progress" role="progressbar" aria-label="Success  striped example" aria-valuenow="'.$value['efficiency'].'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-success" style="width: '.$value['efficiency'].'%">'.$value['efficiency'].'%</div></div>';
                                    } else {
                                        $progress = '<div class="progress" role="progressbar" aria-label="Danger   striped example" aria-valuenow="'.$value['efficiency'].'" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped  bg-danger " style="width: '.$value['efficiency'].'%">'.$value['efficiency'].'%</div></div>';
                                    }

                                   echo '
                                        <tr>
                                            <td>'.++$i.'</td>
                                            <td><img class="img-border" src="images/users/'.($user['profile'] == '' ? 'default.jpg' : $user['profile']).'" width="25px" heigth="25px">'.$user['first_name'].' '.$user['last_name'].'</td>
                                            <td>'.$task['task_id'].'</td>
                                            <td>'.$project['project_name'].'</td>
                                            <td>'.convertMinutesToHoursAndMinutes($value['taken_time']).'</td>
                                            <td>'.convertMinutesToHoursAndMinutes($value['total_time']).'</td>
                                            <td>'.$value['profile'].'</td>
                                            <td>'.$progress .'</td>
                                            <td>'.date("j M Y, g:i A",strtotime($value['created_at'])).'</td>
                                            <td><a target="_blank" href="view-efficiency.php?task_id='.$task['task_id'].'">view</a></td>
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