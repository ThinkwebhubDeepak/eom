<?php
    $page_name = 'pro-task-list';
    $title = 'Employee || EOM ';
    include "includes/header.php";
    $task_id = base64_decode($_GET['task_id']);
    $project_id = base64_decode($_GET['project_id']);

    // task details fetch
    $taskDetails = $conn->prepare("SELECT * FROM `tasks` WHERE `task_id` = ? AND `project_id` = ?");
    $taskDetails->execute([$task_id , $project_id]);
    $taskDetails = $taskDetails->fetch(PDO::FETCH_ASSOC);

    
    // project details fetch
    $projectDetails = $conn->prepare("SELECT * FROM `projects` WHERE `id` = ?");
    $projectDetails->execute([$project_id]);
    $projectDetails = $projectDetails->fetch(PDO::FETCH_ASSOC);
    
    // assign details fetch
    $assignDetails = $conn->prepare("SELECT * FROM `assign` WHERE `task_id` = ? AND `project_id` = ? AND `role` != 'vector' ORDER BY `id` DESC");
    $assignDetails->execute([$task_id , $project_id]);
    $assignDetails = $assignDetails->fetch(PDO::FETCH_ASSOC);
    
    $role = $assignDetails['role'];
    switch ($role) {
        case 'pro':
            $part = 0.75;
            break;
        case 'qc':
            $part = 0.20;
            break;
    }


    if($taskDetails['is_reassigned'] == 1 && $role == 'pro'){
        $part = $part / 2;
    }
    
    if($taskDetails['is_qc_failed'] == 1 && $role == 'qc'){
        $part = $part / 2;
    }

    // current working percentage
    $workHoursforTime = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `user_id` = ? AND `prev_status` = ? ORDER BY `id` DESC;");
    $workHoursforTime->execute([$task_id, $project_id , $_SESSION['userId'] , 'assign_'.$role]);
    $workHoursforTime = $workHoursforTime->fetch(PDO::FETCH_ASSOC);

    $currentWorkPercentage = $conn->prepare("SELECT SUM(work_percentage) AS total_work_percentage FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `id` > ? AND `prev_status` = ? ");
    $currentWorkPercentage->execute([$_SESSION['userId'] ,$task_id, $project_id , $workHoursforTime['id'] , $role."_in_progress"]);
    $result = $currentWorkPercentage->fetch(PDO::FETCH_ASSOC);
    $currentWorkPercentage = $result['total_work_percentage'] == '' ? 0 : $result['total_work_percentage'];

    // assign details fetch
    $users = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0");
    $users->execute();
    $users = $users->fetchAll(PDO::FETCH_ASSOC);

    // function duration time
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
        return $timeAgo;
    }
    if ($hours > 0) {
        $timeAgo .= $hours . " hr ";
    }
    if ($minutes > 0) {
        $timeAgo .= $minutes . " min ";
    }
    if ($timeAgo == "") {
        $timeAgo = "just now";
    }

    return $timeAgo;
    }

?>
<style>
    .accordion-button,
    button.collapsed{
        padding: 10px;
    }
    .border-img{
        border-radius: 50%;
        object-fit: cover;
    }
</style>


  <!-- start modal -->
  <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Add Comment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="card-body p-0">
            <form id="addComment">
              <div class="row form-row mb-3">
                <div class="col-12 col-sm-12 p-2">
                  <div class="form-group">
                    <label>Comment</label>
                    <input type="text" class="form-control" name="comment" id="comment" required>
                    <input type="hidden" class="form-control" name="type" value="addComment" required>
                    <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                    <input type="hidden" id="project_id" name="project_id" value="<?php echo $project_id ?>" required>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Comment</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- end  modal -->

<!-- start modal -->
    <div class="modal fade" id="logWorkModal" tabindex="-1" aria-labelledby="logWorkModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="logWorkModalLabel">Log Work</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <div class="card-body p-0">
                <form id="addPauseLogWork">
                <div class="row form-row mb-3">
                    <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" class="form-control" name="status" id="status"
                        value="<?php echo $taskDetails['status'] ?>" readonly required>
                        <input type="hidden" class="form-control" name="type" value="addPauseLogWork" required>
                        <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                        <input type="hidden" id="project_id" name="project_id" value="<?php echo $project_id ?>"
                        required>
                    </div>
                    </div>

                    <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                        <label>Work Percentage ( <span id="per_val" style="color:green">
                            <?php echo $currentWorkPercentage; ?>%
                        </span> )</label>
                        <input type="range" id="work_percentage"
                        min="<?php echo $currentWorkPercentage; ?>" max="100" step="10"
                        class="form-range" name="work_percentage" value="0" style="height: 40px;" required>
                    </div>
                    </div>
                </div>
                <div class="row form-row mb-3">
                    <div class="col-12 col-sm-12 p-2">
                    <div class="form-group">
                        <label>Minute</label>
                        <input type="number" class="form-control" value="0" id="log_minute" min="0" max="59" name="minute" readonly required>
                    </div>
                    </div>
                </div>
                <div class="row form-row mb-3">
                    <div class="col-12 col-sm-12 p-2">
                    <div class="form-group">
                        <label>Remarks</label>
                        <input type="text" class="form-control" name="remarks">
                    </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="logWorkBtn" class="btn btn-primary">Log Work</button>
                </div>
                </form>
            </div>
            </div>
        </div>
        </div>
    </div>
<!-- end  modal -->

<!-- start break Modal -->
    <div class="modal fade" id="breakModal" tabindex="-1" aria-labelledby="breakModal" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="logWorkModalLabel">Break</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <div class="card-body p-0">
                <form id="addBreak">
                <div class="row form-row mb-3">
                    <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                        <label>Break Type</label>
                        <select name="break_type" id="break_type"  class="form-control" required>
                            <option value="" Default>Select Break Type</option>
                            <option value="break_fast">Break Fast</option>
                            <option value="snacks" >Snacks</option>
                            <option value="lunch" >Lunch</option>
                            <option value="team_meeting" >Team Meeting</option>
                            <option value="other" >Other</option>
                        </select>
                        <input type="hidden"  name="type" value="addBreak" required>
                        <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
                        <input type="hidden" id="project_id" name="project_id" value="<?php echo $project_id ?>"
                        required>
                    </div>
                    </div>

                    <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                        <label>Time (minute)</label>
                        <input type="number" id="break_time" class="form-control" name="time" min="1" value="30" required readonly>
                    </div>
                    </div>
                </div>
                <div class="row form-row mb-3" id="team_meeting_box">
                
                </div>
                <div class="row form-row mb-3">
                    <div class="col-12 col-sm-12 p-2">
                    <div class="form-group">
                        <label>Remarks</label>
                        <input type="text" class="form-control" name="remarks">
                    </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="logWorkBtn" class="btn btn-danger">Break</button>
                </div>
                </form>
            </div>
            </div>
        </div>
        </div>
    </div>
<!-- end  break Modal -->

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
                    <input type="hidden" class="form-control" name="type" value="reAssignTask" required>
                    <input type="hidden" id="task_id" name="task_id" value="<?php echo $task_id ?>" required>
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


<main style="margin-top: 100px;">
    <input type="hidden" id="role" value="<?php echo $role ?>">
    <div class="container">
        <div class="sec">
            <div class="home">
                <div class="nav d-flex">
                    <div class="logo">
                        <img src="https://jira.atlassian.com/secure/projectavatar?pid=18511&avatarId=105990" alt="jksjds">
                    </div>
                    <div class="head">
                        <div class="ms-2">
                            <li class=" pb-1 text-primary">
                                <?php echo $projectDetails['project_name'] . ' (Project Id : ' . $project_id . ')' ?> /
                                <?php echo ' (Task Id : ' . $task_id . ')' ?>
                            </li>
                        </div>
                    </div>
                </div>
                <div class="main-btn d-flex justify-content-between">
                    <div class="btn-left mt-4 ">
                        <button type="button" class="btn bg-btn" data-bs-toggle="modal" data-bs-target="#commentModal"><i class="fa-sharp fa-regular fa-comment me-2"></i>Comment</button>
                        <!-- <button type="button" class="btn bg-btn">Assign</button>    -->
                        <?php 
                        if (($taskDetails['status'] == 'assign_pro') || ($taskDetails['status'] == 'assign_qc')) {
                            ?>
                            <button type="button" onclick="startProgress('<?php echo $task_id ?>',<?php echo $project_id ?>)" class="btn  btn-success">Start Work</button>
                        <?php
                        } else {
                        ?>
                            <?php
                                $contionues = $conn->prepare("SELECT * FROM `work_log` WHERE `task_id` = ? AND `project_id` = ? AND `prev_status` = ? AND `next_status` = 'Pause Work' ORDER BY `work_log`.`id` DESC");
                                $contionues->execute([$task_id, $project_id, $taskDetails['status']]);
                                $contionues = $contionues->fetch(PDO::FETCH_ASSOC);
                                if ($contionues) {
                                    $logbtnflg = 0;
                                } else {
                                    $logbtnflg = 1;
                                }
                            ?>
                                <?php if ($logbtnflg == 1) { ?>
                                    <button type="button" id="#logWorkModalBtn" class="btn bg-btn" data-bs-toggle="modal" data-bs-target="#logWorkModal" onclick="getLastLog()" style="background: #098a01;color: white;">Log Work</button>
                                    <button type="button" id="#breakModal" class="btn bg-btn" data-bs-toggle="modal" data-bs-target="#breakModal" style="background: #c71b1b;color: white;">Break</button>
                                <?php } else { ?>
                                    <button type="button" class="btn bg-btn" onclick="getContinueWork()" style="background: #098a01;color: white;">Continue Work</button>
                                <?php } ?>
                        <?php
                            }
                        ?>
                        <?php if (($taskDetails['status'] == 'ready' || $taskDetails['status'] == 'in_progress') && ($_SESSION['userType'] == 'admin') && ($tasks['is_qc_failed'] == 0)) { ?>
                            <button type="button" class="btn bg-btn" data-bs-toggle="modal" data-bs-target="#reAssignModal">Re-Assign</button>
                        <?php } ?>
                    </div>
                </div>

                <div class=" d-flex px-3 pt-2">
                    <div class="col col-7">
                        <div class="Details mt-3">
                            <p class="fw-bold">Details</p>
                            <div class="row ms-3 mb-2">
                                <div class="col col-3 text-muted">Type</div>
                                <div class="col col-3">
                                    <span class="text-uppercase px-1 development-text fw-bold bg-warning" id="current_status">
                                            <?php echo strtoupper($assignDetails['role']) ?>
                                    </span>
                                </div>
                                <div class="col col-3 text-muted">Status</div>
                                <div class="col col-3 indipended">
                                    <span class="text-uppercase px-1 development-text fw-bold" id="current_status">
                                        <?php echo str_replace('_', ' ', $taskDetails['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row ms-3">
                                <div class="col col-3 text-muted">Priority</div>
                                <div class="col col-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="12 " fill="currentColor" class="bi bi-chevron-double-up text-danger fw-bold" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M7.646 2.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 3.707 2.354 9.354a.5.5 0 1 1-.708-.708l6-6z" />
                                        <path fill-rule="evenodd" d="M7.646 6.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 7.707l-5.646 5.647a.5.5 0 0 1-.708-.708l6-6z" />
                                    </svg>
                                    <?php echo $taskDetails['complexity'] ?>
                                </div>
                                <div class="col col-3 text-muted">Resolution</div>
                                <div class="col col-3 ">Unresolved</div>
                            </div>
                        </div>
                        <!-- <div class="Descrption mt-3">
                            <p class="fw-bold">Descrption</p>
                            <div class="row ms-3 mt-1">
                                <div class="col-12 ">
                                    <p>
                                        <?php echo $taskDetails['description'] ?>
                                    </p>
                                </div>
                            </div>
                        </div> -->
                        <div class="Descrption mt-3">
                            <p class="fw-bold mb-2 ms-3">Attachment</p>
                            <div class="input-group ms-3 mt-1">
                                <input type="file" class="form-control" id="uploadAttechment" aria-describedby="inputGroupFileAddon04" name="attachment" aria-label="Upload">
                                <button class="btn btn-outline-secondary" type="button" id="uploadAttechment_btn">Button</button>
                            </div>
                        </div>
                        <div class="Descrption mt-3">
                            <p class="fw-bold mb-2 ms-3"> Activity</p>
                            <form action="">
                                <div class="d-flex border mt-1 ms-3 ">
                                    <div class="accordion p-3" id="accordionFlushExample">
                                        <div class="">
                                            <div class="row">
                                                <div class="col  d-flex">
                                                    <div class="   ">
                                                        <h2 class="accordion-header" id="flush-headingOne">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                                                <div class="me-2">
                                                                    Activity
                                                                </div>
                                                            </button>
                                                        </h2>
                                                    </div>
                                                    <div class=" ">
                                                        <h2 class="accordion-header" id="flush-headingTwo">
                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="true" id="btnradio4" aria-controls="flush-collapseTwo">
                                                                <div class="me-2" id="" >
                                                                    <label class=" " for="btnradio4">Comment</label>
                                                                </div>
                                                            </button>
                                                        </h2>
                                                    </div>
                                                    <div class=" ">
                                                        <h2 class="accordion-header" id="flush-headingThree">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                                                <div class="me-2">WorkLog</div>
                                                            </button>
                                                        </h2>
                                                    </div>
                                                </div>
                                                <div class="col col-12">
                                                    <div class=" d-block w-100 ">
                                                        <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                                            <div class="accordion-body mt-2">
                                                                <!-- <div class=" scroll-bar" id="">
                                                                    <div class="d-flex mt-1">
                                                                        <div><span class="activity-name">AS</span></div>
                                                                        <div>
                                                                            <span>Anil Sahoo</span>
                                                                            <p class="d-inline text-muted">added a comment - 4day age at 10:29 PM</p>
                                                                            <p class="text-muted">Code added in the requestof getSdui api</p>
                                                                        </div>
                                                                    </div>
                                                                    <hr>
                                                                    <div class="d-flex">
                                                                        <div><span class="activity-name">SD</span></div>
                                                                        <div>
                                                                            <span>Sumit Dash</span>
                                                                            <p class=" text-muted">Change the Assignee july 1, 2023 at 10:29 PM</p>
                                                                            <p class="text-muted"><span class="">Unssigned <i class="bi bi-arrow-right"></i></span><span class="Activity-short-name mx-2">SD</span>Sumit Dash </p>
                                                                        </div>
                                                                    </div>
                                                                </div> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class=" d-block w-100 ">
                                                        <div id="flush-collapseTwo" class="accordion-collapse collapse show" aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
                                                            <div class="accordion-body mt-2">
                                                                <div class=" scroll-bar" id="commentBox">
                                                                    <?php

                                                                        $comments = $conn->prepare("SELECT * FROM `comments` WHERE `task_id` = ?AND `project_id` = ?  ORDER BY `created_at` DESC");
                                                                        $comments->execute([$task_id, $project_id]);
                                                                        $comments = $comments->fetchAll(PDO::FETCH_ASSOC);
                                                                        foreach ($comments as $comment) {

                                                                            $commentuser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                                                            $commentuser->execute([$comment['user_id']]);
                                                                            $commentuser = $commentuser->fetch(PDO::FETCH_ASSOC);

                                                                            if($commentuser['profile'] != ''){
                                                                                $image = $commentuser['profile'];
                                                                            }else{
                                                                                $image = 'default.jpg';
                                                                            }

                                                                        echo '
                                                                            <div class="d-flex mt-1">
                                                                            <div><span class="activity-name"><img class="border-img" src="images/users/'.$image.'" width="25px" heigth="25px" ></span></div>
                                                                            <div>
                                                                                <span class="m-2">' . $commentuser['first_name'] . ' ' . $commentuser['last_name'] . '</span>
                                                                                <p class="d-inline text-muted">added a comment - ' . getTimeAgo($comment['created_at']) . ' ago.</p>
                                                                                <p class="text-muted">' . $comment['comment'] . '</p>
                                                                            </div>
                                                                            </div>
                                                                            <hr>
                                                                        
                                                                        ';
                                                                    }

                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class=" d-block w-100 ">
                                                        <div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                                            <div class="accordion-body mt-2">
                                                                <div class=" scroll-bar" id="">
                                                                    <?php

                                                                    $sqli = $conn->prepare("SELECT * FROM `work_log` WHERE `project_id` = ?  AND `task_id` = ? ORDER BY `created_at` DESC");
                                                                    $sqli->execute([$project_id,$task_id]);
                                                                    $WorkLogs = $sqli->fetchAll(PDO::FETCH_ASSOC);


                                                                    foreach ($WorkLogs as $WorkLog) {
                                                                        $sqluser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                                                        $sqluser->execute([$WorkLog['user_id']]);
                                                                        $usersdata = $sqluser->fetch(PDO::FETCH_ASSOC);
                                                                        if($usersdata['profile'] != ''){
                                                                            $image = $usersdata['profile'];
                                                                        }else{
                                                                            $image = 'default.jpg';
                                                                        }

                                                                        echo '<div class="d-flex mb-3 mt-2">
                                                                                <div><span class="activity-name" style="text-transform: uppercase;"><img class="border-img" src="images/users/'.$image.'" width="25px" heigth="25px" ></span></div>
                                                                                <div>
                                                                                <span class="m-2"> ' . $usersdata['first_name'] . ' ' . $usersdata['last_name'] . '</span>
                                                                                <p class="d-inline text-muted m-0">Logged </p class="m-0"><span>' . getTimeAgo($WorkLog['created_at']) . '</span> ago<p class="d-flex d-block m-0"></p>
                                                                                <p class="text-muted ms-2">Remark : ' . $WorkLog['remarks'] . '</p>
                                                                                <p class="text-muted ms-2">Time : ' . $WorkLog['taken_time'] . 'M</p>
                                                                                </div>
                                                                            </div>
                                                                            <hr>';
                                                                    }

                                                                    ?>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>

                    <div class="col px-3 ms-5 right">
                        <div class="people">
                            <p class="fw-bold">People</p>
                            <div class="row ms-3">
                                <div class="col-4 text-muted">Assigness</div>
                                <div class="col-8">
                                    <select name="" id="" class=" form-control Assign-name">
                                        <?php
                                            $sqluser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                            $sqluser->execute([$assignDetails['user_id']]);
                                            $usersdata = $sqluser->fetch(PDO::FETCH_ASSOC);
                                            echo '<option value="">' . $usersdata['first_name'] . ' ' . $usersdata['last_name'] . '</option>';
                                        ?>
                                    </select>
                                    <!-- <li class="text-primary">Assign to me</li> -->
                                </div>
                            </div>
                            <div class="report row ms-3 mt-1">

                                <div class="col-4 text-muted">
                                    <p>Assign By</p>
                                </div>
                                <div class="col-8">
                                    <select name="" id="" class=" form-control  Assign-name">
                                        <?php
                                            $sqluser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                            $sqluser->execute([$assignDetails['assigned_by']]);
                                            $usersdata = $sqluser->fetch(PDO::FETCH_ASSOC);
                                            echo '<option value="">' . $usersdata['first_name'] . ' ' . $usersdata['last_name'] . '</option>';
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="Time-traking ">
                            <p class="fw-bold">Time Traking</p>
                            <div class="row ms-3 mb-2">
                                <div class="col-4 ">Estimated</div>
                                <div class="col-6 mt-1">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">100%</div>
                                    </div>

                                </div>
                                <div class="col ms-2">
                                    <?php echo $total_time = ($taskDetails['estimated_hour'] * 60) * $part ?> M
                                </div>
                            </div>

                            <?php

                            $taken_time = 0;
                            if ($workHoursforTime) {
                                $sql_query = $conn->prepare("SELECT SUM(taken_time) AS total_taken_time FROM `work_log` WHERE `user_id` = ? AND `task_id` = ? AND `project_id` = ? AND `id` > ? ");
                                $sql_query->execute([$_SESSION['userId'] ,$task_id, $project_id , $workHoursforTime['id']]);
                                $result = $sql_query->fetch(PDO::FETCH_ASSOC);
                                $taken_time = $result['total_taken_time'] == '' ? 0 : $result['total_taken_time'];
                            }

                            if($total_time < 0){
                                $remaing_time = 0;
                                $remaing_time_per = 0;
                                $log_time_per = 0;
                            }else{
                                if($total_time > 0){
                                    $remaing_time = $total_time - $taken_time;
                                    $remaing_time_per = (($remaing_time) / $total_time) * 100;
                                    $log_time_per = (($taken_time) / $total_time) * 100;
                                }else{
                                    $remaing_time = 0;
                                    $remaing_time_per = 0;
                                    $log_time_per = 0;
                                }
                            }

                            ?>

                            <div class="row ms-3 mb-1">
                                <div class="col-4 ">Remaining</div>
                                <div class="col-6 mt-1">
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $remaing_time_per; ?>%">
                                            <?php echo number_format($remaing_time_per, 2); ?>%
                                        </div>
                                    </div>
                                </div>
                                <div class="col ms-2">
                                    <?php echo $remaing_time;?> M
                                </div>
                            </div>
                            <div class="row ms-3 mb-1">
                                <div class="col-4 ">Logged</div>
                                <div class="col-6 mt-1">
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width:<?php echo $log_time_per ?>%">
                                            <?php echo number_format($log_time_per, 2); ?>%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-1 ms-2">
                                    <?php echo $taken_time; ?> M
                                </div>
                            </div>

                        </div>
                        <div class="Time-traking ">
                            <p class="fw-bold">Attachment <a style="margin-left: 45%;color: #0d6efd;font-weight: 500;text-decoration: none;cursor:pointer" id="downloadButton">Download All</a></p>
                            <?php
                            if ($taskDetails['attachment'] != '') {
                            ?>
                                <div class="mt-3">
                                    <div class="row ms-3 mb-2">
                                        <div class="col-2" style="display: flex;align-items: center;justify-content: center;font-size: 35px">
                                            <i class="fas fa-file-image"></i>
                                        </div>
                                        <div class="col-10">
                                            <div class="">
                                                <a class="attachment_file_download" href="upload/attachment/<?php echo $taskDetails['attachment'] ?>" target="_blank" style="text-decoration:none">
                                                    <?php echo $taskDetails['attachment'] ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col ms-2" style="text-align: right;">
                                        <?php echo getTimeAgo($tasks['create_at']) ?>
                                    </div>
                                </div>
                                <hr>
                            <?php
                            }

                                $attachments = $conn->prepare("SELECT * FROM `attachment` WHERE `task_id` = ?");
                                $attachments->execute([$task_id]);
                                $attachments = $attachments->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($attachments as $attachment) {
                            ?>

                                <div class="mt-3">
                                    <div class="row ms-3 mb-2">
                                        <div class="col-2" style="display: flex;align-items: center;justify-content: center;font-size: 35px">
                                            <i class="fas fa-file-image"></i>
                                        </div>
                                        <div class="col-8">
                                            <div class="">
                                                <a class="attachment_file_download" href="upload/attachment/<?php echo $attachment['attachment'] ?>" target="_blank" style="text-decoration:none">
                                                    <?php echo $attachment['attachment'] ?>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-2" style="display: flex;align-items: center;justify-content: center;font-size: 18px;color:red;cursor: pointer;">
                                            <i class="fas fa-trash" onclick="deleteAttachment(<?php echo $attachment['id'] ?>,'<?php echo $task_id ?>')"></i>
                                        </div>
                                    </div>
                                    <div class="col ms-2" style="text-align: right;">
                                        <?php echo getTimeAgo($attachment['created_at']) ?>
                                    </div>
                                </div>

                            <?php
                                }
                            ?>


                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</main>

<?php include "includes/footer.php"; ?>
<script src="includes/assets/plugin/jsZip.js"></script>
<script>

    $('#work_percentage').on('input', function () {
        var percentage = $(this).val();
        $("#per_val").text(percentage + '%');
    });

    $('#addComment').submit(function(e){
        e.preventDefault();
        var formdata = new FormData(this);
        $.ajax({
            url: 'includes/settings/api/commentApi.php',
            type: 'POST',
            data: formdata,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        });
    })

    $('#addBreak').submit(function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'includes/settings/api/breakApi.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                notyf.success(response.message);
                const currentTime = new Date();
                const currentTimeString = currentTime.toISOString();
                localStorage.setItem('breakTime', currentTimeString);
                localStorage.setItem('breakDuration', response.time);
                $('#addBreak').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        });
    });

    $('#break_type').change(function () {
        var break_type = $('#break_type').val();
        if (break_type == 'team_meeting') {
            $('#team_meeting_box').html(` <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                        <label>Who</label>
                        <input type="text" class="form-control" name="who" required>
                    </div>
                    </div>

                    <div class="col-12 col-sm-6 p-2">
                    <div class="form-group">
                        <label>Why</label>
                        <input type="text" class="form-control" name="why" required>
                    </div>
                    </div>`);
        } else {
            $('#team_meeting_box').html('');
        }
        if (break_type == 'lunch') {
            $('#break_time').val(45);
        } else {
            $('#break_time').val(30);
        }
    });



    function startProgress(task_id, project_id) {
        $.ajax({
            url: 'includes/settings/api/taskApi.php',
            type : 'post',
            data: {
                type: 'inProgress',
                task_id: task_id,
                project_id: project_id
            },
            dataType: 'json',
            success: function (result) {
                notyf.success(result.message);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        });
    }

    function getLastLog(){
        var task_id = $('#task_id').val();
        var project_id = $('#project_id').val();
        var status = $('#status').val();
        $.ajax({
            url: 'includes/settings/api/otherApi.php',
            data: {
                type: 'getLastLog',
                task_id: task_id,
                project_id : project_id,
                status: status
            },
            success: function (response) {
                console.log(response);
                $('#log_minute').val(response.minutes);
            }
        });
    }

    $('#addPauseLogWork').submit(function(e){
        e.preventDefault();
        var percentage = parseInt($("#work_percentage").val());
        var log_minute = parseInt($('#log_minute').val());
        var formData = new FormData(this);
        if (percentage != 0) {
            if(log_minute != 0){
                $.ajax({
                    url: 'includes/settings/api/workLogApi.php',
                    type: 'POST',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (response) {
                        notyf.success(response.message);
                        $('#logWorkModal').modal('hide');
                        if (percentage == 100) {
                            var role = $('#role').val();
                            if(role == 'qc'){
                                Notiflix.Confirm.show(
                                    'Qc Confirm',
                                    'What you want to ?',
                                    'Pass',
                                    'Fail',
                                    () => {
                                        window.location.href = 'qc-task-list.php';
                                    },
                                    () => {
                                        $('#reAssignModal').modal('show');
                                    }
                                );

                            }else{
                                window.location.href = 'pro-task-list.php';
                            }
                        } else {
                            location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                        notyf.error(errorMessage);
                    }
              });
            }else{
                notyf.error("At least 1 min need");
            }
        }else{
            notyf.error("Work Percentage is zero.");
        }
    });

    function getContinueWork(){
        var task_id = $('#task_id').val();
        var project_id = $('#project_id').val();
        var pause_id = '<?php echo $contionues['id'] ?>';
        $.ajax({
        url: 'includes/settings/api/otherApi.php',
        type : 'POST',
        data: {
            type: 'getContinueWork',
            task_id: task_id,
            project_id : project_id,
            pause_id : pause_id
        },
        dataType : 'JSON',
        success: function (response) {
            notyf.success(response.message);
            setTimeout(() => {
                location.reload();
            }, 1000);
        },
        error: function (xhr, status, error) {
            var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
            notyf.error(errorMessage);
        }
        });
    }

    // end break modal code

    const storedBreakTime = new Date(localStorage.getItem('breakTime'));
    const storedBreakDuration = parseInt(localStorage.getItem('breakDuration'));
    const currentTime = new Date();

    const differenceInMilliseconds = storedBreakTime.getTime() + storedBreakDuration * 60000 - currentTime.getTime();
    const durationInMinutes = Math.floor(differenceInMilliseconds / (1000 * 60));
    console.log(differenceInMilliseconds);
    function setTimeAndRemoveLoader() {
        Notiflix.Loading.remove();
    }

    var countDownDate = storedBreakTime.setMinutes(storedBreakTime.getMinutes() + storedBreakDuration);
    if(countDownDate){
        setInterval(function () {
            var now = new Date().getTime();
            var distance = countDownDate - now;
            const minutes = Math.floor(distance / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            document.getElementById("timebreaktext").innerHTML = minutes + " min " + seconds + " sec";
        }, 1000);
    }


Notiflix.Loading.custom({
    customSvgCode: `<svg xmlns="http://www.w3.org/2000/svg" id="NXLoadingHourglass" fill="#32c682" width="500px" height="500px" viewBox="0 0 200 200"><style>@-webkit-keyframes NXhourglass5-animation{0%{-webkit-transform:scale(1,1);transform:scale(1,1)}16.67%{-webkit-transform:scale(1,.8);transform:scale(1,.8)}33.33%{-webkit-transform:scale(.88,.6);transform:scale(.88,.6)}37.5%{-webkit-transform:scale(.85,.55);transform:scale(.85,.55)}41.67%{-webkit-transform:scale(.8,.5);transform:scale(.8,.5)}45.83%{-webkit-transform:scale(.75,.45);transform:scale(.75,.45)}50%{-webkit-transform:scale(.7,.4);transform:scale(.7,.4)}54.17%{-webkit-transform:scale(.6,.35);transform:scale(.6,.35)}58.33%{-webkit-transform:scale(.5,.3);transform:scale(.5,.3)}83.33%,to{-webkit-transform:scale(.2,0);transform:scale(.2,0)}}@keyframes NXhourglass5-animation{0%{-webkit-transform:scale(1,1);transform:scale(1,1)}16.67%{-webkit-transform:scale(1,.8);transform:scale(1,.8)}33.33%{-webkit-transform:scale(.88,.6);transform:scale(.88,.6)}37.5%{-webkit-transform:scale(.85,.55);transform:scale(.85,.55)}41.67%{-webkit-transform:scale(.8,.5);transform:scale(.8,.5)}45.83%{-webkit-transform:scale(.75,.45);transform:scale(.75,.45)}50%{-webkit-transform:scale(.7,.4);transform:scale(.7,.4)}54.17%{-webkit-transform:scale(.6,.35);transform:scale(.6,.35)}58.33%{-webkit-transform:scale(.5,.3);transform:scale(.5,.3)}83.33%,to{-webkit-transform:scale(.2,0);transform:scale(.2,0)}}@-webkit-keyframes NXhourglass3-animation{0%{-webkit-transform:scale(1,.02);transform:scale(1,.02)}79.17%,to{-webkit-transform:scale(1,1);transform:scale(1,1)}}@keyframes NXhourglass3-animation{0%{-webkit-transform:scale(1,.02);transform:scale(1,.02)}79.17%,to{-webkit-transform:scale(1,1);transform:scale(1,1)}}@-webkit-keyframes NXhourglass1-animation{0%,83.33%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(180deg);transform:rotate(180deg)}}@keyframes NXhourglass1-animation{0%,83.33%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(180deg);transform:rotate(180deg)}}#NXLoadingHourglass *{-webkit-animation-duration:1.2s;animation-duration:1.2s;-webkit-animation-iteration-count:infinite;animation-iteration-count:infinite;-webkit-animation-timing-function:cubic-bezier(0,0,1,1);animation-timing-function:cubic-bezier(0,0,1,1)}</style><g data-animator-group="true" data-animator-type="1" style="-webkit-animation-name:NXhourglass1-animation;animation-name:NXhourglass1-animation;-webkit-transform-origin:50% 50%;transform-origin:50% 50%; scale: 0.5;transform-box:fill-box"><g id="NXhourglass2" fill="inherit"><g data-animator-group="true" data-animator-type="2" style="-webkit-animation-name:NXhourglass3-animation;animation-name:NXhourglass3-animation;-webkit-animation-timing-function:cubic-bezier(.42,0,.58,1);animation-timing-function:cubic-bezier(.42,0,.58,1);-webkit-transform-origin:50% 100%;transform-origin:50% 100%;transform-box:fill-box" opacity=".4"><path id="NXhourglass4" d="M100 100l-34.38 32.08v31.14h68.76v-31.14z"></path></g><g data-animator-group="true" data-animator-type="2" style="-webkit-animation-name:NXhourglass5-animation;animation-name:NXhourglass5-animation;-webkit-transform-origin:50% 100%;transform-origin:50% 100%;transform-box:fill-box" opacity=".4"><path id="NXhourglass6" d="M100 100L65.62 67.92V36.78h68.76v31.14z"></path></g><path d="M51.14 38.89h8.33v14.93c0 15.1 8.29 28.99 23.34 39.1 1.88 1.25 3.04 3.97 3.04 7.08s-1.16 5.83-3.04 7.09c-15.05 10.1-23.34 23.99-23.34 39.09v14.93h-8.33a4.859 4.859 0 1 0 0 9.72h97.72a4.859 4.859 0 1 0 0-9.72h-8.33v-14.93c0-15.1-8.29-28.99-23.34-39.09-1.88-1.26-3.04-3.98-3.04-7.09s1.16-5.83 3.04-7.08c15.05-10.11 23.34-24 23.34-39.1V38.89h8.33a4.859 4.859 0 1 0 0-9.72H51.14a4.859 4.859 0 1 0 0 9.72zm79.67 14.93c0 15.87-11.93 26.25-19.04 31.03-4.6 3.08-7.34 8.75-7.34 15.15 0 6.41 2.74 12.07 7.34 15.15 7.11 4.78 19.04 15.16 19.04 31.03v14.93H69.19v-14.93c0-15.87 11.93-26.25 19.04-31.02 4.6-3.09 7.34-8.75 7.34-15.16 0-6.4-2.74-12.07-7.34-15.15-7.11-4.78-19.04-15.16-19.04-31.03V38.89h61.62v14.93z"></path></g></g><text id="timebreaktext" transform="matrix(1 0 0 1 20 200)" fill="#49BA81" font-family="'MyriadPro-Regular'" font-size="30px"></text></svg>`,
});

setTimeout(setTimeAndRemoveLoader, differenceInMilliseconds);



$('#downloadButton').click(function () {
    var zip = new JSZip();

    // Fetch and add attachments to the ZIP archive
    var attachments = document.querySelectorAll('.attachment_file_download');
    var promises = [];
    attachments.forEach(function (attachment) {
        var fileName = attachment.textContent.trim();
        var fileURL = attachment.getAttribute('href');

        promises.push(
            fetch(fileURL)
                .then(function (response) {
                    return response.blob();
                })
                .then(function (blob) {
                    zip.file(fileName, blob);
                })
        );
    });

    // Wait for all promises to resolve
    Promise.all(promises)
        .then(function () {
            // Generate the ZIP file
            return zip.generateAsync({ type: 'blob' });
        })
        .then(function (content) {
            // Create a download link for the ZIP file
            var zipFileName = 'attachments.zip';
            var zipBlob = new Blob([content], { type: 'application/zip' });
            var zipURL = URL.createObjectURL(zipBlob);

            var link = document.createElement('a');
            link.href = zipURL;
            link.download = zipFileName;
            link.style.display = 'none';

            // Trigger the download
            document.body.appendChild(link);
            link.click();

            // Clean up the link
            document.body.removeChild(link);
        })
        .catch(function (error) {
            console.error('Error:', error);
        });
});



$("#uploadAttechment_btn").click(() => {
    var task_id = '<?php echo $task_id ?>';
    var formData = new FormData();
    formData.append('type', 'uploadAttechment');
    formData.append('task_id', task_id);
    formData.append('attachment', $('#uploadAttechment')[0].files[0]);

    $.ajax({
      url: 'includes/settings/api/attachmentApi.php',
      type: 'POST',
      data: formData,
      processData: false,  // Don't process the data
      contentType: false,  // Don't set content type
      success: function (result) {
        location.reload();
      }
    });
  });

  
  function deleteAttachment(id, task_id) {
    $.ajax({
      url: 'includes/settings/api/attachmentApi.php',
      type: 'POST',
      data: {
        type: 'deleteAttechment',
        id: id,
        task_id: task_id
      },
      success: function (result) {
        location.reload();
      }
    });
  }


  $('#reAssignTaskForm').submit(function(e){
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
      url: 'includes/settings/api/workLogApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        setTimeout(() => {
            window.location.href = 'qc-task-list.php';
        }, 1000);
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  });

</script>