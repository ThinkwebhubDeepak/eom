<?php
// $page_name = 'project-list';
$title = 'Project Details || EOM ';
include 'includes/header.php';

// if(!(in_array($page_name, $pageAccessList)) && $roleId != 1){
//   echo '<script>window.location.href = "index.php"</script>';
// }  
$project_id = base64_decode($_GET['project_id']);
$project = $conn->prepare('SELECT * FROM `projects` WHERE `id` = ?');
$project->execute([$project_id]);
$project = $project->fetch(PDO::FETCH_ASSOC);

function getTimeAgo($givenTimestamp)
{

  $givenUnixTimestamp = strtotime($givenTimestamp);
  $currentUnixTimestamp = time();
  $timeDifference = $currentUnixTimestamp - $givenUnixTimestamp;
  $days = floor($timeDifference / (60 * 60 * 24));
  $hours = floor(($timeDifference - ($days * 60 * 60 * 24)) / (60 * 60));
  $minutes = floor(($timeDifference - ($days * 60 * 60 * 24) - ($hours * 60 * 60)) / 60);
  $seconds = $timeDifference - ($days * 60 * 60 * 24) - ($hours * 60 * 60) - ($minutes * 60);
  $timeAgo = "";
  if ($days > 0) {
    $timeAgo .= $days . " days ";
  }
  if ($hours > 0) {
    $timeAgo .= $hours . " hr ";
  }
  if ($minutes > 0) {
    $timeAgo .= $minutes . " min ";
  }
  if ($seconds > 0) {
    $timeAgo .= $seconds . " sec";
  }
  if ($timeAgo == "") {
    $timeAgo = "just now";
  }

  return $timeAgo;
}

function diffDateInDay($firstDate, $secondDate)
{
  $firstDate = new DateTime($firstDate);
  $secondDate = new DateTime($secondDate);
  if ($firstDate == $secondDate) {
    return 1;
  }
  $interval = $firstDate->diff($secondDate);
  return $interval->days;
}


function convertMinutesToHoursAndMinutes($minutes)
{
  $hours = floor($minutes / 60);
  $remainingMinutes = round($minutes % 60);
  return sprintf('%d h %d m', $hours, $remainingMinutes);
}

function totalAreaSquare($conn, $project_id, $role)
{
  if ($role != '') {
    $sql = $conn->prepare("SELECT DISTINCT `task_id` FROM efficiency WHERE `profile` = ? AND `project_id` = ?");
    $sql->execute([$role, $project_id]);
  } else {
    $sql = $conn->prepare("SELECT DISTINCT `task_id` FROM efficiency WHERE `project_id` = ?");
    $sql->execute([$project_id]);
  }
  $sql  = $sql->fetchAll(PDO::FETCH_ASSOC);
  $area = 0;
  foreach ($sql as $value) {
    $taskd = $conn->prepare("SELECT * FROM tasks WHERE `task_id` = ? AND `project_id` = ?");
    $taskd->execute([$value['task_id'], $project_id]);
    $taskd  = $taskd->fetch(PDO::FETCH_ASSOC);
    $area += $taskd['area_sqkm'];
  }
  return $area;
}

$users = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0");
$users->execute();
$users = $users->fetchAll(PDO::FETCH_ASSOC);

$assign_pros = $conn->prepare("SELECT * FROM `assignproject` WHERE `project_id` = ? AND access = 1");
$assign_pros->execute([$project_id]);
$assign_pros = $assign_pros->fetchAll(PDO::FETCH_ASSOC);
$assign_pro = $assign_pros[0];
if ($assign_pro) {
  $flag = 1;
} else {
  $flag = 0;
}

$tasks = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ?");
$tasks->execute([$project_id]);
$tasks = $tasks->fetchAll(PDO::FETCH_ASSOC);


if ($project['vector'] == 1) {
  $completetasks = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ? AND `status` = 'complete' AND `vector_status` = 'complete'");
  $completetasks->execute([$project_id]);
  $completetasks = $completetasks->fetchAll(PDO::FETCH_ASSOC);
} else {
  $completetasks = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ? AND `status` = 'complete'");
  $completetasks->execute([$project_id]);
  $completetasks = $completetasks->fetchAll(PDO::FETCH_ASSOC);
}
if (count($tasks) > 0) {
  $projectPercentage = count($completetasks) / count($tasks) * 100;
} else {
  $projectPercentage =  0;
}

$completePro = $conn->prepare("SELECT * FROM `tasks` WHERE status > 'pro_in_progress' AND `project_id` = ?");
$completePro->execute([$project_id]);
$completePro = $completePro->fetchAll(PDO::FETCH_ASSOC);

$completeQc = $conn->prepare("SELECT * FROM `tasks` WHERE status > 'qc_in_progress' AND `project_id` = ?");
$completeQc->execute([$project_id]);
$completeQc = $completeQc->fetchAll(PDO::FETCH_ASSOC);

$completeQa = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ? AND `status` = 'complete'");
$completeQa->execute([$project_id]);
$completeQa = $completeQa->fetchAll(PDO::FETCH_ASSOC);

$completeVector = $conn->prepare("SELECT * FROM `tasks` WHERE `project_id`= ? AND `vector_status` = 'complete'");
$completeVector->execute([$project_id]);
$completeVector = $completeVector->fetchAll(PDO::FETCH_ASSOC);

$proCount = count($completePro);
$qcCount = count($completeQc);
$qaCount = count($completeQa);

if ($qcCount < $qaCount) {
  $qcCount = $qaCount;
}

if ($proCount  < $qcCount) {
  $proCount = $qcCount;
}

$preparation = $conn->prepare("SELECT `user_id` FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'preparation' AND `status` = 'start'");
$preparation->execute([$_SESSION['userId'], $project_id]);
$preparation = $preparation->fetch(PDO::FETCH_ASSOC);

$finalization = $conn->prepare("SELECT `user_id` FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'finalization' AND `status` = 'start'");
$finalization->execute([$_SESSION['userId'], $project_id]);
$finalization = $finalization->fetch(PDO::FETCH_ASSOC);

$feedback = $conn->prepare("SELECT `user_id` FROM `projectefficiency` WHERE `user_id` = ? AND `project_id` = ? AND `type` = 'feedback' AND `status` = 'start'");
$feedback->execute([$_SESSION['userId'], $project_id]);
$feedback = $feedback->fetch(PDO::FETCH_ASSOC);
?>

<style>
  .modal-date {
    margin-left: 35px;
    width: 235px;

  }

  .span {
    font-weight: 600;
  }

  .Assign-name {
    width: 170px;
    border: none;
    padding: 5px;
  }

  .option {
    border: none;
    background-color: #F4F6F6;
  }

  .activity-name {
    background-color: #0052cc;
    color: white;
    padding: 5px;
    border-radius: 50%;

    letter-spacing: 1px;
    /* margin: 30px 10px; */
    margin-top: 20px;
    text-transform: uppercase;

  }

  .Activity-short-name {
    background-color: #0052cc;
    color: white;
    padding: 5px;
    border-radius: 15px;
    letter-spacing: 1px;

  }

  .accordion-button {
    padding: 10px;
  }

  .scroll-bar {
    max-height: 150px;
    overflow-y: scroll;
  }

  .home {
    height: auto;
  }


  .item {
    background: white;
    margin: 10px;
    border: 1px solid #0069ba;
  }

  .item-card-box-task {
    padding: 15px;
    transition: 0.5s;
    cursor: pointer;
  }

  .item-card-box-task-title {
    font-size: 15px;
    transition: 1s;
    text-align: right;
    cursor: pointer;
  }

  .item-card-box-task-title i {
    font-size: 15px;
    transition: 1s;
    cursor: pointer;
    color: #008bf6
  }

  .card-box-task-title i:hover {
    transform: scale(1.25) rotate(100deg);
    color: #18d4ca;

  }

  .card-box-task:hover {
    transform: scale(1.05);
    box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.3);
  }

  .card-box-task-text {
    height: 80px;
  }

  .card-box-task::before,
  .card-box-task::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    transform: scale3d(0, 0, 1);
    transition: transform .3s ease-out 0s;
    background: rgba(255, 255, 255, 0.1);
    content: '';
    pointer-events: none;
  }

  .card-box-task::before {
    transform-origin: left top;
  }

  .card-box-task::after {
    transform-origin: right bottom;
  }

  .card-box-task:hover::before,
  .card-box-task:hover::after,
  .card-box-task:focus::before,
  .card-box-task:focus::after {
    transform: scale3d(1, 1, 1);
  }

  .just-center {
    justify-content: center;
  }

  li {
    display: list-item;
    list-style: revert;
  }

  #project_full_eff .box {
    padding: 10px;
    width: 19%;
    border: 1px solid black;
    border-radius: 10px;
    /* box-shadow: 2px 2px 20px 0px; */
  }

  .paddingbtn {
    padding: 0 10px;
  }

  .high {
    background-color: red;
  }

  .container {
    background-color: #f4f5f9;
    max-width: 100%;
  }

  #completeFeedback,
  #completeFinalization,
  #completePreparation {
    position: fixed;
    z-index: 9999;
    left: 80%;
  }
</style>


<!-- start modal -->
<div class="modal fade" id="reAssignModal" tabindex="-1" aria-labelledby="reAssignModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Assign Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="card-body p-0">
          <form id="reAssignTaskForm">
            <div class="row form-row mb-3">
              <div class="col-12 col-sm-12 p-2">
                <div class="form-group">
                  <label>Select Employee</label>
                  <select class="form-control select2" name="user_id" required>
                    <option value="">Choose Employee</option>
                    <?php
                    foreach ($users as $user) {
                      echo '<option value="' . $user['id'] . '" ' . $select . '>' . $user['first_name'] . ' ' . $user['last_name'] . '</option>';
                    }
                    ?>
                  </select>
                  <input type="hidden" class="form-control" id="type_id" name="type" value="AssignProject" required>
                  <input type="hidden" id="project_id" name="project_id" value="<?php echo $project_id ?>" required>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">ReAssign</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- end  modal -->


<!--Main Navigation-->

<!--Main layout-->
<main style="margin-top: 100px;">

  <div class="container">
    <div class="sec">
      <div class="home">
        <div class="nav d-flex">
          <div class="logo">
            <img src="images/favic.jpg" alt="jksjds">
          </div>
          <div class="head">
            <div class="ms-2">
              <li class=" pb-1 text-primary"></li>
              <div class="d-flex">
                <div id="editableText" contenteditable="false">
                  <?php echo $project['project_name'] . ' (Project Id : ' . $project['id'] . ')' ?>
                </div>
              </div>
            </div>
          </div>
          <?php if ($roleId == 1 || in_array('create-project', $pageAccessList)) {
            echo '
                    <div class="right">
                      <a href="create-project.php?edit=' . $_GET['project_id'] . '" class="btn btn-primary"  style="margin: 15px;">Edit</a>
                    </div>
                  ';
          }
          if ($roleId == 1 || in_array('assign-project', $pageAccessList)) {
            if (true && $flag == 0) {
              echo '
                      <div class="right">
                        <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reAssignModal"  style="margin: 15px;">Assign</a>
                      </div>
                    ';
            } else {
              echo '
                      <div class="right">
                        <a class="btn btn-primary" onclick="reAssign()" data-bs-toggle="modal" data-bs-target="#reAssignModal"  style="margin: 15px;">ReAssign</a>
                      </div>
                    ';
            }
          }

          if ($project['is_complete'] == 0) {
            if (!$preparation) {
              echo '<div class="right">
                      <a class="btn btn-warning" onclick="projectPreparation()" style="margin: 15px;">Preparation</a>
                    </div>';
            } else {
              echo '<div class="right">
                      <a id="completePreparation" class="btn btn-warning" onclick="completePreparation()" style="margin: 15px;">Complete Preparation</a>
                    </div>';
            }

            if (!$finalization) {
              echo '<div class="right">
                      <a class="btn btn-warning" onclick="projectFinalization()" style="margin: 15px;">Finalization</a>
                    </div>';
            } else {
              echo '<div class="right">
                      <a id="completeFinalization" class="btn btn-warning" onclick="completeFinalization()" style="margin: 15px;">Complete Finalization</a>
                    </div>';
            }
          } else {
            if (!$feedback) {
              echo '<div class="right">
                      <a class="btn btn-warning" onclick="projectFeedback()" style="margin: 15px;">Feedback</a>
                    </div>';
            } else {
              echo '<div class="right">
                      <a id="completeFeedback" class="btn btn-warning" onclick="completeFeedback()" style="margin: 15px;">Complete Feedback</a>
                    </div>';
            }
          }



          ?>
          <input type="text" id="search-task" placeholder="Search Task" class="form-control" style="width: 300px;height: 50px;margin-left: auto;">
        </div>
        <div>
        </div>
        <br><br>
        <div class=" d-flex px-3 pt-2">
          <div class="col col-7">
            <div class="Details">
              <p class="fw-bold">Details</p>
              <div class="row ms-3">
                <div class="col col-3 text-muted">Name</div>
                <div class="col col-3"><?php echo $project['project_name'] ?></div>
                <div class="col col-3 text-muted">Priority</div>
                <div class="col col-3 indipended"><i class="fas fa-signal" style="font-size: 20px;margin: 0 10px;color:black"></i><span class="text-uppercase px-1 development-text fw-bold <?php echo $project['complexity'] ?>" id="current_status"><?php echo str_replace('_', ' ', $project['complexity']) ?></span>
                </div>
              </div>
            </div>
            <div class="Descrption mt-3">
              <div class="row ms-3">
                <div class="col col-3 text-muted">Estimated Time </div>
                <div class="col col-3"><?php echo $project['estimated_hour'] ?> Hours</div>
                <div class="col col-3 text-muted">Descrption</div>
                <div class="col col-3"><?php echo $project['description'] ?>H</div>
              </div>
            </div>
          </div>


          <div class="col p-3 ms-5 right">
            <div class="people">
              <p class="fw-bold">People</p>
              <div class="row ms-3">
                <div class="col-4 text-muted">Assigness</div>
                <div class="col-8">
                  <select name="" id="" class=" form-control Assign-name">
                    <?php
                    $tempdata = 0;
                    foreach ($assign_pros as $proUser) {
                      if ($tempdata != $proUser['user_id']) {
                        $tempdata = $proUser['user_id'];
                        $sqluser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                        $sqluser->execute([$proUser['user_id']]);
                        $usersdata = $sqluser->fetch(PDO::FETCH_ASSOC);
                        echo '<option value="">' . $usersdata['first_name'] . ' ' . $usersdata['last_name'] . '</option>';
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="report row ms-3 mt-1">

                <div class="col-4 text-muted">
                  <p>Assign By</p>
                </div>
                <div class="col-8">
                  <select name="" id="" class=" form-control  Assign-name">
                    <?php
                    $tempdata = 0;
                    foreach ($assign_pros as $proUser) {
                      if ($tempdata != $proUser['assign_by']) {
                        $tempdata = $proUser['assign_by'];
                        $sqluser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                        $sqluser->execute([$proUser['assign_by']]);
                        $usersdata = $sqluser->fetch(PDO::FETCH_ASSOC);
                        echo '<option value="">' . $usersdata['first_name'] . ' ' . $usersdata['last_name'] . '</option>';
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container" id="project_full_eff">
    <div class="card flex-fill">
      <div class="card-header">
        Project Status
        <div class="progress">
          <div class="progress-bar progress-bar-striped bg-warning" role="progressbar" style="width: <?php echo $projectPercentage ?>%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><?php echo round($projectPercentage, 2) ?>%</div>
        </div>
      </div>
      <?php
      $pro = $conn->prepare("SELECT SUM(taken_time) AS total_taken_time, SUM(total_time) AS total_total_time FROM efficiency WHERE project_id = ? AND `profile` = 'pro'");
      $pro->execute([$project_id]);
      $pro = $pro->fetch(PDO::FETCH_ASSOC);

      $qc = $conn->prepare("SELECT SUM(taken_time) AS total_taken_time, SUM(total_time) AS total_total_time FROM efficiency WHERE project_id = ? AND `profile` = 'qc'");
      $qc->execute([$project_id]);
      $qc = $qc->fetch(PDO::FETCH_ASSOC);

      $qa = $conn->prepare("SELECT SUM(taken_time) AS total_taken_time, SUM(total_time) AS total_total_time FROM efficiency WHERE project_id = ? AND `profile` = 'qa'");
      $qa->execute([$project_id]);
      $qa = $qa->fetch(PDO::FETCH_ASSOC);

      $vector = $conn->prepare("SELECT SUM(taken_time) AS total_taken_time, SUM(total_time) AS total_total_time FROM efficiency WHERE project_id = ? AND `profile` = 'vector'");
      $vector->execute([$project_id]);
      $vector = $vector->fetch(PDO::FETCH_ASSOC);
      ?>
      <?php

      $preparationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'preparation' AND `project_id` = $project_id");
      $preparationTime->execute();
      $preparationTime =  $preparationTime->fetch(PDO::FETCH_ASSOC);

      $finalizationTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'finalization' AND `project_id` = $project_id");
      $finalizationTime->execute();
      $finalizationTime =  $finalizationTime->fetch(PDO::FETCH_ASSOC);

      $feedbackTime = $conn->prepare("SELECT SUM(`taken_time`) as `taken_time` FROM `projectefficiency` WHERE `type` = 'feedback' AND `project_id` = $project_id");
      $feedbackTime->execute();
      $feedbackTime =  $feedbackTime->fetch(PDO::FETCH_ASSOC);

      ?>
      <h6 class="p-2">Task Time Details : </h6>
      <div class="card-body d-flex justify-content-sm-between">
        <div class="box">
          <p>PRO : <?php echo  $proCount  ?> / <?php echo count($tasks) ?></p>
          <p>PRO Area: <span class="pro_area"></span><?php echo totalAreaSquare($conn, $project_id, 'pro') ?> <?php echo $project['area'] ?>.</p>
          <p>PRO Taken: <span class="pro_eff"><?php echo $pro['total_taken_time'] ? convertMinutesToHoursAndMinutes($pro['total_taken_time']) : 0 ?></span></p>
          <p>PRO Total: <span class="pro_eff_total"><?php echo $pro['total_total_time'] ? convertMinutesToHoursAndMinutes($pro['total_total_time']) : 0 ?></span></span></p>
          <p>PRO Effi.: <span class="pro_eff_cal"><?php echo $pro['total_taken_time'] <= 0 ? 0 : number_format(($pro['total_total_time'] / $pro['total_taken_time']) * 100, 2) ?></span>%</p>
        </div>
        <div class="box">
          <p>Qc : <?php echo  $qcCount  ?> / <?php echo count($tasks) ?></p>
          <p>QC Area: <span class="qc_area"></span><?php echo totalAreaSquare($conn, $project_id, 'qc') ?> <?php echo $project['area'] ?>.</p>
          <p>QC Taken: <span class="qc_eff"><?php echo $qc['total_taken_time'] ?  convertMinutesToHoursAndMinutes($qc['total_taken_time']) : 0 ?></span> </p>
          <p>QC Total: <span class="qc_eff_total"><?php echo $qc['total_total_time'] ? convertMinutesToHoursAndMinutes($qc['total_total_time']) : 0 ?></span> </p>
          <p>QC Effi.: <span class="qc_eff_cal"><?php echo $qc['total_taken_time'] <= 0 ? 0 :  number_format(($qc['total_total_time'] / $qc['total_taken_time']) * 100, 2) ?></span>%</p>
        </div>
        <div class="box">
          <p>QA : <?php echo  $qaCount  ?> / <?php echo count($tasks) ?></p>
          <p>QA Area: <span class="qa_area"></span><?php echo totalAreaSquare($conn, $project_id, 'qa') ?> <?php echo $project['area'] ?>.</p>
          <p>QA Taken: <span class="qa_eff"><?php echo $qa['total_taken_time'] ?  convertMinutesToHoursAndMinutes($qa['total_taken_time']) : 0 ?></span> .</p>
          <p>QA Total: <span class="qa_eff_total"><?php echo $qa['total_total_time'] ?  convertMinutesToHoursAndMinutes($qa['total_total_time']) : 0 ?></span> .</p>
          <p>QA Effi.: <span class="qa_eff_cal"><?php echo $qa['total_taken_time'] <= 0 ? 0 :  number_format(($qa['total_total_time'] / $qa['total_taken_time']) * 100, 2) ?></span>%</p>
        </div>
        <?php if ($project['vector'] == 1) { ?>
          <div class="box">
            <p>Vector : <?php echo  count($completeVector)  ?> / <?php echo count($tasks) ?></p>
            <p>Vector Area: <span class="vector_area"></span><?php echo totalAreaSquare($conn, $project_id, 'vector') ?> <?php echo $project['area'] ?>.</p>
            <p>Vector Taken: <span class="vector_eff"><?php echo $vector['total_taken_time'] ?  convertMinutesToHoursAndMinutes($vector['total_taken_time']) : 0 ?></span> .</p>
            <p>Vector Total: <span class="vector_eff_total"><?php echo $vector['total_total_time'] ?  convertMinutesToHoursAndMinutes($vector['total_total_time']) : 0 ?></span> .</p>
            <p>Vector Effi.: <span class="vector_eff_cal"><?php echo $vector['total_taken_time'] <= 0 ? 0 :  number_format(($vector['total_total_time'] / $vector['total_taken_time']) * 100, 2) ?></span>%</p>
          </div>
        <?php } ?>
        <div class="box">
          <p>Total : <?php echo  count($completetasks)  ?> / <?php echo count($tasks) ?></p>
          <p>Total Area: <span class="vector_area"></span><?php echo totalAreaSquare($conn, $project_id, '') ?> <?php echo $project['area'] ?>.</p>
          <p>Project Time: <span class="vector_eff"><?php echo $task_taken_time =  convertMinutesToHoursAndMinutes($feedbackTime['taken_time'] + $finalizationTime['taken_time'] + $preparationTime['taken_time'] + $utt = ($pro['total_taken_time'] + $qc['total_taken_time'] + $qa['total_taken_time'] + $vector['total_taken_time'])) ?></span> . <a href="#exampleModalCenter" data-bs-toggle="modal"> <i class="fa-solid fa-circle-info"></i> </a></p>
          <p>Total Time: <span class="vector_eff_total"><?php echo convertMinutesToHoursAndMinutes($uot = ($pro['total_total_time'] + $qc['total_total_time'] + $qa['total_total_time'] + $vector['total_total_time'])) ?></span> .</p>
          <p>Total Effi.: <span class="vector_eff_cal"><?php echo $utt > 0 ? number_format(($uot / $utt) * 100, 2) : 0 ?></span>%</p>
        </div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="task-list">
      <h2>Task List</h2>
      <div class="container mt-2">
        <div class="row just-center">
          <?php

          foreach ($tasks as $task) {
            $task_id = base64_encode($task['task_id']);
            if (($task['status'] == 'pending') || ($task['status'] == 'ready_for_qa') || ($task['status'] == 'ready_for_qc')) {
              $color = 'red';
            } else {
              $color = 'green';
            };

            if (($task['vector_status'] == 'pending') || ($task['vector_status'] == 'ready_for_qa') || ($task['vector_status'] == 'ready_for_qc')) {
              $vcolor = 'red';
            } else {
              $vcolor = 'green';
            };

            $data = '';
            if ($task['status'] != 'pending') {
              $efficiny = $conn->prepare("SELECT * FROM `efficiency` WHERE `project_id`= ? AND `task_id`= ?");
              $efficiny->execute([$project_id, $task['task_id']]);
              $efficiny = $efficiny->fetchAll(PDO::FETCH_ASSOC);
              foreach ($efficiny as $value) {
                $user = $conn->prepare("SELECT `first_name` , `last_name` FROM users WHERE id = ?");
                $user->execute([$value['user_id']]);
                $user = $user->fetch(PDO::FETCH_ASSOC);
                $data .= '<li>' . $value['profile'] . ' ' . $value['efficiency'] . '% (' . $value['total_time'] . ' M / ' . $value['taken_time'] . ' M) <br><span class="text-danger"> ' . $user['first_name'] . ' ' . $user['last_name'] . '</span>  <br><span class="text-success"> ' . date('d M, Y h:i A', strtotime($value['created_at'])) . ' </span> </li>';
              }
            }

            echo '
              <div class="col-md-3 col-sm-6 item clickable-row" data-href="task-details.php?task_id=' . $task_id . '&project_id=' . base64_encode($project_id) . '">
                <div class="card-box-task item-card-box-task card-box-task-block">
                <h4 class="item-card-box-task-title text-right"><a style="margin: 0px 5px;" href="create-task.php?edit=' . $task_id . '"><i class="fa-solid fa-pen-to-square"></i></a></h4>
                  <h6 class="card-box-task-title  mt-3 mb-3"># ' . $task['task_id'] . '</h6>
                  <h5 class="card-box-task  mt-3 mb-3">Area : ' . $task['area_sqkm'] . ' ' . $project['area'] . '</h5>
                  <ol>' . $data . '</ol>
                  <span class="text-uppercase px-1 development-text fw-bold" id="current_status" style="color:white;font-size:12px;background:' . $color . '">' . str_replace('_', ' ', $task['status']) . '</span>';

            if ($project['vector'] == 1) {

              echo '<span class="text-uppercase px-1 development-text fw-bold" id="current_status" style="color:white;font-size:12px;background:' . $vcolor . '">' . str_replace('_', ' ', $task['vector_status']) . '</span>';
            }

            echo '
                </div>
              </div>  
              ';
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</main>
<!--Main layout-->


<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Total Efficiency</h5>
      </div>
      <div class="modal-body">
        <div class="card-body d-flex justify-content-sm-between">
          <div class="box">
            <p>PRO Taken: <span class="pro_eff"><?php echo $pro['total_taken_time'] ? convertMinutesToHoursAndMinutes($pro['total_taken_time']) : 0 ?></span></p>
            <p>QC Taken: <span class="qc_eff"><?php echo $qc['total_taken_time'] ?  convertMinutesToHoursAndMinutes($qc['total_taken_time']) : 0 ?></span> </p>
            <p>QA Taken: <span class="qa_eff"><?php echo $qa['total_taken_time'] ?  convertMinutesToHoursAndMinutes($qa['total_taken_time']) : 0 ?></span> .</p>
            <p>Task Taken: <span class="vector_eff"><?php echo convertMinutesToHoursAndMinutes($utt) ?></span> .</p>
            <p>Preparation Taken: <span class="vector_eff"><?php echo convertMinutesToHoursAndMinutes($preparationTime['taken_time']) ?></span> .</p>
            <p>Finalization Taken: <span class="vector_eff"><?php echo convertMinutesToHoursAndMinutes($finalizationTime['taken_time']) ?></span> .</p>
            <p>Feedback Taken: <span class="vector_eff"><?php echo convertMinutesToHoursAndMinutes($feedbackTime['taken_time']) ?></span> .</p>
            <p>Total Project Taken: <span class="vector_eff"><?php echo convertMinutesToHoursAndMinutes($feedbackTime['taken_time'] + $finalizationTime['taken_time'] + $preparationTime['taken_time'] + $utt) ?></span> .</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

  <?php include 'includes/footer.php' ?>
  <script>
    <?php
    if ($preparation || $finalization || $feedback) {
      echo 'Notiflix.Loading.dots();';
    }
    ?>

    var currentDate = new Date();
    var formattedDate = currentDate.getFullYear() + "-" + (currentDate.getMonth() + 1) + "-" + currentDate.getDate();
    $("#datepicker").val(formattedDate);


    const editableText = document.getElementById('editableText');
    const editButton = document.getElementById('editButton');

    editButton.addEventListener('click', () => {
      if (editableText.getAttribute('contenteditable') === 'false') {
        editableText.setAttribute('contenteditable', 'true');
        editButton.textContent = 'Save';
      } else {
        editableText.setAttribute('contenteditable', 'false');
        editButton.textContent = 'Edit';
      }
    });



    const editableText1 = document.getElementById('editableText1');
    const editButton1 = document.getElementById('editButton1');

    editButton1.addEventListener('click', () => {
      if (editableText1.getAttribute('contenteditable') === 'false') {
        editableText1.setAttribute('contenteditable', 'true');
        editButton1.textContent = 'Save';
      } else {
        editableText1.setAttribute('contenteditable', 'false');
        editButton1.textContent = 'Edit';
      }
    });

    let date = new Date();
    var dd = String(date.getDate()).padStart(2, '0');
    var mm = String(date.getMonth() + 1).padStart(2, '0');
    var yyyy = date.getFullYear();
    let today = mm + '/' + dd + '/' + yyyy;
    console.log('today is', today);
    $('#datepicker').dateDropper({
      format: 'Y/m/d',
      large: true,
      largeDefault: true,
      largeOnly: true,
      theme: 'datetheme'
    });
    let date1 = new Date();
    var dd = String(date.getDate()).padStart(2, '0');
    var mm = String(date.getMonth() + 1).padStart(2, '0');
    var yyyy = date.getFullYear();
    let today1 = mm + '/' + dd + '/' + yyyy;
    console.log('today is', today);
    $('#datepicker1').dateDropper({
      format: 'Y/m/d',
      large: true,
      largeDefault: true,
      largeOnly: true,
      theme: 'datetheme'
    });
    let date2 = new Date();
    var dd = String(date.getDate()).padStart(2, '0');
    var mm = String(date.getMonth() + 1).padStart(2, '0');
    var yyyy = date.getFullYear();
    let today2 = mm + '/' + dd + '/' + yyyy;
    console.log('today is', today);
    $('#datepicker2').dateDropper({
      format: 'Y/m/d',
      large: true,
      largeDefault: true,
      largeOnly: true,
      theme: 'datetheme'
    });
    let date3 = new Date();
    var dd = String(date.getDate()).padStart(2, '0');
    var mm = String(date.getMonth() + 1).padStart(2, '0');
    var yyyy = date.getFullYear();
    let today3 = mm + '/' + dd + '/' + yyyy;
    console.log('today is', today);
    $('#datepicker3').dateDropper({
      format: 'Y/m/d',
      large: true,
      largeDefault: true,
      largeOnly: true,
      theme: 'datetheme'
    });
    let date4 = new Date();
    var dd = String(date.getDate()).padStart(2, '0');
    var mm = String(date.getMonth() + 1).padStart(2, '0');
    var yyyy = date.getFullYear();
    let today4 = mm + '/' + dd + '/' + yyyy;
    console.log('today is', today);
    $('#datepicker4').dateDropper({
      format: 'Y/m/d',
      large: true,
      largeDefault: true,
      largeOnly: true,
      theme: 'datetheme'
    });
  </script>
  <script>
    $('#search-task').keyup(function() {
      var searchValue = $(this).val().toLowerCase();
      $('.clickable-row').each(function() {
        var taskID = $(this).find('h6').text().toLowerCase();
        if (taskID.includes(searchValue)) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    });

    // Graph
    var ctx = document.getElementById("myChart");

    var myChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: [
          "Sunday",
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
        ],
        datasets: [{
          data: [15339, 21345, 18483, 24003, 23489, 24092, 12034],
          lineTension: 0,
          backgroundColor: "transparent",
          borderColor: "#007bff",
          borderWidth: 4,
          pointBackgroundColor: "#007bff",
        }, ],
      },
      options: {
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: false,
            },
          }, ],
        },
        legend: {
          display: false,
        },
      },
    });
  </script>

  <script>
    function reAssign() {
      $('#type_id').val('reAssignProject');
    }

    $('#reAssignTaskForm').submit(function(event) {
      event.preventDefault();
      var formData = new FormData(this);
      $.ajax({
        url: 'includes/settings/api/projectAssignApi.php',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
          console.log(response);
          notyf.success(response.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    });


    function projectPreparation() {
      $.ajax({
        url: 'includes/settings/api/projectPreparationApi.php',
        type: 'POST',
        data: {
          type: 'projectPreparation',
          project_id: <?php echo $project_id ?>
        },
        dataType: 'json',
        success: function(response) {
          console.log(response);
          notyf.success(response.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }

    function completePreparation() {
      $.ajax({
        url: 'includes/settings/api/projectPreparationApi.php',
        type: 'POST',
        data: {
          type: 'completePreparation',
          project_id: <?php echo $project_id ?>
        },
        dataType: 'json',
        success: function(response) {
          console.log(response);
          notyf.success(response.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }


    function projectFinalization() {
      $.ajax({
        url: 'includes/settings/api/projectPreparationApi.php',
        type: 'POST',
        data: {
          type: 'projectFinalization',
          project_id: <?php echo $project_id ?>
        },
        dataType: 'json',
        success: function(response) {
          console.log(response);
          notyf.success(response.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }

    function completeFinalization() {
      $.ajax({
        url: 'includes/settings/api/projectPreparationApi.php',
        type: 'POST',
        data: {
          type: 'completeFinalization',
          project_id: <?php echo $project_id ?>
        },
        dataType: 'json',
        success: function(response) {
          console.log(response);
          notyf.success(response.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }

    function projectFeedback() {
      $.ajax({
        url: 'includes/settings/api/projectPreparationApi.php',
        type: 'POST',
        data: {
          type: 'projectFeedback',
          project_id: <?php echo $project_id ?>
        },
        dataType: 'json',
        success: function(response) {
          console.log(response);
          notyf.success(response.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }

    function completeFeedback() {
      $.ajax({
        url: 'includes/settings/api/projectPreparationApi.php',
        type: 'POST',
        data: {
          type: 'completeFeedback',
          project_id: <?php echo $project_id ?>
        },
        dataType: 'json',
        success: function(response) {
          console.log(response);
          notyf.success(response.message);
          setTimeout(() => {
            location.reload();
          }, 1500);
        },
        error: function(xhr, status, error) {
          var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
          notyf.error(errorMessage);
        }
      });
    }
  </script>


  </body>

  </html>