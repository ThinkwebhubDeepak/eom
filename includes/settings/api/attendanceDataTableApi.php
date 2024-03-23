<?php

include '../config/config.php';
$query = "SELECT `attendance`.* , `users`.`first_name`, `users`.`last_name` , `role`.`role` FROM `attendance` JOIN `users` ON `attendance`.`user_id` = `users`.`id` JOIN `role` ON `role`.`id` = `users`.`role_id`";

$column = array("id", "first_name", "role", "date", "id", "id", "id", "id", "id", "id", "id");

if (isset($_POST["search"]["value"])) {
    $query .= '
	WHERE `users`.`first_name` LIKE "%' . $_POST["search"]["value"] . '%" 
	OR `users`.`last_name` LIKE "%' . $_POST["search"]["value"] . '%"
    OR `role`.`role` LIKE "%' . $_POST["search"]["value"] . '%"
    OR DATE_FORMAT(`attendance`.`date`, \'%d %b, %Y\') LIKE "%' . $_POST["search"]["value"] . '%"';
}

if (isset($_POST["order"])) {
    $query .= 'ORDER BY ' . $column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
} else {
    $query .= 'ORDER BY `attendance`.`created_at` DESC ';
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
    $efficincy = $conn->prepare("SELECT COUNT(task_id) as countefficiency , SUM(total_time) as totaltime , SUM(taken_time) as takentime FROM `efficiency` WHERE `user_id` = ? AND DATE(`created_at`) = ?");
    $efficincy->execute([$row['user_id'], $row['date']]);
    $efficincy = $efficincy->fetch(PDO::FETCH_ASSOC);

    $break = $conn->prepare("SELECT SUM(`time`) as break_time FROM `break` WHERE DATE(`created_at`) = ? AND `user_id` = ?");
    $break->execute([$row['date'], $row['user_id']]);
    $break = $break->fetch(PDO::FETCH_ASSOC);


    $project_time = $conn->prepare("SELECT COUNT(`taken_time`) as `totaltime` FROM `projectefficiency` WHERE DATE(`created_at`) = ? AND `user_id` = ?");
    $project_time->execute([$row['date'], $row['user_id']]);
    $project_time = $project_time->fetch(PDO::FETCH_ASSOC);

    $clock_in_time = strtotime($row['clock_in_time']);
    if ($clock_in_time >= strtotime('5:00 AM') && $clock_in_time <= strtotime('8:00 AM')) {
        $late_login = '<br><span class="badge badge-danger late_login morning">Morning</span>';
        if ($clock_in_time >= strtotime('6:45 AM')) {
            $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
        } else {
            $late_login_status = '';
        }
    } else if ($clock_in_time >= strtotime('12:00 PM') && $clock_in_time <= strtotime('3:00 PM')) {
        $late_login = '<br><span class="badge badge-danger late_login evening">Evening</span>';
        if ($clock_in_time > strtotime('2:45 PM')) {
            $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
        } else {
            $late_login_status = '';
        }
    } else {
        $late_login = '<br><span class="badge badge-danger late_login general">General</span>';
        if ($clock_in_time >= strtotime('9:15 AM')) {
            $late_login_status = '<span class="badge badge-danger late_login">Late</span>';
        } else {
            $late_login_status = '';
        }
    }

    if ($row['clock_out_time'] != '') {
        $attendance_clock_out = date('h:i A', strtotime($row['clock_out_time']));
        $TclockInTime = strtotime($row['clock_in_time']);
        $TclockOutTime = strtotime($row['clock_out_time']);
        $timeDifferenceSeconds = $TclockOutTime - $TclockInTime;
        $timeDifferenceHours = $timeDifferenceSeconds / 3600;
        $ideal_time = round($timeDifferenceHours - ($efficincy['takentime'] / 60) - ($break['break_time'] / 60) - ($project_time['totaltime']/60), 2);
        $ideal_hour = ' <span class="text-success">' . $ideal_time . 'H </span> / <span class="text-danger"> ' . round($timeDifferenceHours, 2) . 'H  </span> ';
        if ($timeDifferenceHours > 5 && $timeDifferenceHours < 6.5) {
            $half_status = '<span class="badge badge-danger late_login" style="background-color: #bd00ff;">Half Day</span>';
        } else if ($timeDifferenceHours < 5) {
            $half_status = '<span class="badge badge-danger late_login" style="background-color: black;">Absent</span>';
        } else {
            $half_status = '';
        }
    } else {
        $TclockOutTime = date('Y-m-d H:i:s');
        $attendance_clock_out = '';
        $ideal_hour = '';
    }

    if ($attendance['regularisation'] == 1) {
        $is_regularisation = true;
    } else {
        $is_regularisation = false;
    }

    if($efficincy['takentime'] > 0){
        $efficincy_user = ($efficincy['totaltime']/$efficincy['takentime'] * 100);
    }else{
        $efficincy_user = 0;
    }

    if($row['clock_out_time'] != ''){
        $clock_out = date('h:i A', strtotime($row['clock_out_time']));
    }else{
        $clock_out = '';
    }

    $sub_array = array();
    $sub_array[] = $row['id'];
    $sub_array[] = $row['first_name'] . $row['last_name'] . $late_login . ' ' . $late_login_status . ' ' . $half_status ;
    $sub_array[] = ucfirst($row['role']);
    $sub_array[] = date('d M, Y', strtotime($row['date']));
    $sub_array[] = date('h:i A', strtotime($row['clock_in_time']));
    $sub_array[] = $clock_out;
    $sub_array[] = $efficincy['countefficiency'];
    $sub_array[] = (round($efficincy_user, 2) ?? 0).'%';
    $sub_array[] = '<span class="text-success">'.round($efficincy['totaltime']/60 , 2).'H </span> / <span class="text-danger">'.round($efficincy['takentime']/60 , 2).'H </span>';
    $sub_array[] = '<span class="text-success">'.round($project_time['totaltime']/60 , 2).'H </span>';
    $sub_array[] = $ideal_hour;
    $data[] = $sub_array;
}

function count_all_data($connect)
{
    $query = "SELECT count(*) as total FROM `attendance`";
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
