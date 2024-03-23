<?php

$page_name = 'total-efficiency';
include 'includes/header.php';

if ($roleId != 1 && !(in_array($page_name, $pageAccessList))) {
    echo '<script>window.location.href = "index.php"</script>';
}

// $data = $conn->prepare("SELECT `project_id`, `user_id`, `efficiency`, `task_id`, `profile`, `taken_time`, `total_time`, `created_at` FROM `efficiency` ORDER BY `created_at` DESC");
// $data->execute();
// $data = $data->fetchAll(PDO::FETCH_ASSOC);

// function convertMinutesToHoursAndMinutes($minutes)
// {
//     $hours = floor($minutes / 60);
//     $remainingMinutes = round($minutes % 60, 2);
//     if ($hours != 0) {
//         return sprintf('%d h %d m', $hours, $remainingMinutes);
//     } else {
//         return round($minutes, 2) . 'm';
//     }
// }

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
                <div class="card-header d-flex" style="display:none !important">
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
                    <input type="date" class="form-control" id="dateSearch" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="card-body">
                    <table id="dataDTable" class="display">
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
                            </tr>
                        </thead>
                        <tbody id="tbody">

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
    var dataTablee = $('#dataDTable').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: "includes/settings/api/efficiencyDataTableApi.php",
            type: "POST"
        }
    });

    // function convertMinutesToHoursAndMinutes(minutes) {
    //     var hours = Math.floor(minutes / 60);
    //     var remainingMinutes = Math.round(minutes % 60);
    //     if (hours !== 0) {
    //         return hours + 'h ' + remainingMinutes + 'm';
    //     } else {
    //         return minutes + 'm';
    //     }
    // }

    // $('#projectSelect').change(function() {
    //     var projectInput, projectValue;
    //     projectInput = document.getElementById("projectSelect");
    //     projectValue = projectInput.value;
    //     dataTablee.column(3).search(projectValue).draw();
    // });

    // $('#dateSearch').change(function() {
    //     var date = $('#dateSearch').val();
    //     dataTablee.clear();
    //     $.ajax({
    //         url: 'includes/settings/api/efficiencyApi.php',
    //         type: 'GET',
    //         data: {
    //             type: 'fetchEfficiency',
    //             date: date
    //         },
    //         dataType: 'json',
    //         success: function(response) {
    //             var i = 0;
    //             response.forEach(element => {
    //                 var rowData = [
    //                     ++i,
    //                     `<img loading="lazy" class="img-border" src="${element.image}" width="25px" heigth="25px">${element.name} `,
    //                     element.task,
    //                     element.project,
    //                     convertMinutesToHoursAndMinutes(element.taken_time),
    //                     convertMinutesToHoursAndMinutes(element.total_time),
    //                     element.profile,
    //                     element.progress,
    //                     element.start,
    //                     element.end
    //                 ];
    //                 dataTablee.row.add(rowData).draw();
    //             });
    //             console.log(response);
    //         },
    //         error: function(xhr, status, error) {
    //             var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
    //             notyf.error(errorMessage);
    //         }
    //     });
    // })
</script>