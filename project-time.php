<?php
    $page_name = 'project-time';

    include "includes/header.php";

    if ($roleId != 1 && !(in_array($page_name, $pageAccessList))) {
        $projectefficiencys = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? ORDER BY `created_at` DESC");
        $projectefficiencys->execute([$_SESSION['userId']]);
        $projectefficiencys = $projectefficiencys->fetchAll(PDO::FETCH_ASSOC);
    }else{
        $projectefficiencys = $conn->prepare("SELECT * FROM `projectefficiency` ORDER BY `created_at` DESC");
        $projectefficiencys->execute();
        $projectefficiencys = $projectefficiencys->fetchAll(PDO::FETCH_ASSOC);
    }


?>

<style>
    .form-group {
        padding: 10px;
    }
</style>

<main style="margin-top: 58px;">
    <div class="container-flude pt-5">
        <div class="container-flude px-5">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <h1 class="fw-bold page_heading">Other Task</h1>
                </div>
                <div class="">
                    <select name="project_name" id="project_name" class="form-control" style="margin :0 15px;">
                        <option value="">Select Projects</option>
                        <?php
                        $projects = $conn->prepare("SELECT * FROM `projects`");
                        $projects->execute();
                        $projects = $projects->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($projects as $value) {
                            echo '  <option value=' . $value['project_name'] . '>' . $value['project_name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <table id="dataTable" class="display">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Employee Id</th>
                        <th scope="col">Name</th>
                        <th scope="col">Project Name</th>
                        <th scope="col">Work</th>
                        <th scope="col">Taken Time</th>
                        <th scope="col">Start Time</th>
                        <th scope="col">End Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($projectefficiencys as $value) {
                        $user = $conn->prepare('SELECT * FROM `users` WHERE `id` = ?');
                        $user->execute([$value['user_id']]);
                        $user = $user->fetch(PDO::FETCH_ASSOC);

                        $project = $conn->prepare('SELECT `project_name` FROM `projects` WHERE `id` = ?');
                        $project->execute([$value['project_id']]);
                        $project = $project->fetch(PDO::FETCH_ASSOC);

                        echo '
                                <tr id="row_' . $value['id'] . '">
                                <th scope="row">' . $i . '</th>
                                <td>' . $user['employee_id'] . '</td>
                                <td>' . $user['first_name'] . ' ' . $user['last_name'] . '</td>
                                <td>' . $project['project_name'] . '</</td>
                                <td>' . ucfirst($value['type']) . '</td>
                                <td>' .  $value['taken_time'].'m</td>
                                <td class="text-danger">' . date('d M, Y h:i A', strtotime($value['created_at'])) . '</</td>
                                <td class="text-success">' . date('d M, Y h:i A', strtotime($value['updated_at'])) . '</</td>
                                </tr>
                            ';
                        $i++;
                    }

                    ?>
                </tbody>
            </table>
        </div>
</main>

<?php include "includes/footer.php" ?>
<script>
    $('#project_name').change(function() {
        var userType = document.getElementById("project_name").value;
        dataTable.column(3).search(userType).draw();
    });

</script>