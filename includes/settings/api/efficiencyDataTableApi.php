<?php

include '../config/config.php';

function convertMinutesToHours($minutes) {
    $minutes = round($minutes);
    if (!is_numeric($minutes) || $minutes < 0) {
        return "Invalid input";
    }

    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;

    $hourString = ($hours == 1) ? "hr" : "hr";
    $minuteString = ($remainingMinutes == 1) ? "m" : "m";

    if ($hours == 0) {
        return "{$remainingMinutes} {$minuteString}";
    } elseif ($remainingMinutes == 0) {
        return "{$hours} {$hourString}";
    } else {
        return "{$hours} {$hourString}, {$remainingMinutes} {$minuteString}";
    }
}


$query = "SELECT efficiency.id, efficiency.project_id, efficiency.user_id, efficiency.task_id, efficiency.updated_at, efficiency.efficiency,efficiency.profile,efficiency.taken_time, efficiency.total_time ,`users`.`first_name`,`users`.`last_name` , `projects`.`project_name` , `work_log`.`created_at`  FROM `efficiency` JOIN `users` ON `efficiency`.`user_id` = `users`.`id`  JOIN `projects` ON `efficiency`.`project_id` = `projects`.`id` JOIN `work_log` ON `efficiency`.`task_id` = `work_log`.`task_id` AND `efficiency`.`project_id` = `work_log`.`project_id` AND `efficiency`.`user_id` = `work_log`.`user_id` AND CONCAT('assign_',`efficiency`.`profile`) = `work_log`.`prev_status`";

$column = array("id", "first_name", "task_id", "project_name", "taken_time", "total_time", "taken_time", "total_time","profile","efficiency");

if (isset($_POST["search"]["value"])) {
    $query .= '
	WHERE `efficiency`.`task_id` LIKE "%' . $_POST["search"]["value"] . '%" 
	OR `projects`.`project_name` LIKE "%' . $_POST["search"]["value"] . '%"
    OR `efficiency`.`project_id` LIKE "%' . $_POST["search"]["value"] . '%"
    OR `users`.`first_name` LIKE "%' . $_POST["search"]["value"] . '%" 
    OR DATE_FORMAT(`efficiency`.`updated_at`, \'%d %b, %Y\') LIKE "%' . $_POST["search"]["value"] . '%"
	OR `efficiency`.`profile` LIKE "%' . $_POST["search"]["value"] . '%"';
}

if (isset($_POST["order"])) {
    $query .= 'ORDER BY ' . $column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
} else {
    $query .= 'ORDER BY id DESC ';
}

$query1 = '';

if ($_POST["length"] != -1) {
    $query1 = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}

$statement = $conn->prepare($query);

$statement->execute();

$number_filter_row = $statement->rowCount();

$result = $conn->query($query . $query1);

$data = array();

foreach ($result as $row) {
    $sub_array = array();
    $sub_array[] = $row['id'];
    $sub_array[] = $row['first_name'] . $row['last_name'];
    $sub_array[] = $row['task_id'];
    $sub_array[] = $row['project_name'];
    $sub_array[] = convertMinutesToHours($row['taken_time']);
    $sub_array[] = convertMinutesToHours($row['total_time']);
    $sub_array[] = ucfirst($row['profile']);
    $sub_array[] = round($row['efficiency'], 2) . '%';
    $sub_array[] = date('d M, Y h:i A', strtotime($row['created_at']));
    $sub_array[] = date('d M, Y h:i A', strtotime($row['updated_at']));
    $data[] = $sub_array;
}

function count_all_data($connect)
{
    $query = "SELECT COUNT(*) as total FROM `efficiency` JOIN `projects` ON `efficiency`.`project_id` = `projects`.`id` JOIN `users` ON `efficiency`.`user_id` = `users`.`id` JOIN `work_log` ON `efficiency`.`task_id` = `work_log`.`task_id` AND `efficiency`.`project_id` = `work_log`.`project_id` AND `efficiency`.`user_id` = `work_log`.`user_id` AND CONCAT('assign_',`efficiency`.`profile`) = `work_log`.`prev_status` ";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

$output = array(
    "draw"        =>    intval($_POST["draw"]),
    "recordsTotal"    =>    count_all_data($conn),
    "recordsFiltered"    =>    $number_filter_row,
    "data"    =>    $data
);

echo json_encode($output);