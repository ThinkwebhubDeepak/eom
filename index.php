<?php
$page_name = 'index';
include "includes/header.php";
$currentDate = date('Y-m-d');
$user_id = $_SESSION['userId'];


$todayholiday = $conn->prepare("SELECT * FROM `holiday` WHERE `date` = CURRENT_DATE()");
$todayholiday->execute();
$todayholiday = $todayholiday->fetch(PDO::FETCH_ASSOC);
if($todayholiday){
  $iStodayHoliday = 1;
}else{
  $iStodayHoliday = 0;
}


// for get birthday details
$bday_users = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0 AND MONTH(`dob`) = MONTH(CURTIME()) AND DAY(`dob`) > DAY(CURTIME())  ORDER BY DAY(`dob`) ASC");
$bday_users->execute();
$bday_users = $bday_users->fetchAll(PDO::FETCH_ASSOC);

// for get birthday details
$check_enivs = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0 AND MONTH(`created_at`) = MONTH(CURTIME()) AND DAY(`created_at`) > DAY(CURTIME())  ORDER BY DAY(`created_at`) ASC");
$check_enivs->execute();
$check_enivs = $check_enivs->fetchAll(PDO::FETCH_ASSOC);

// for get self Attendance details
$myAttandence = $conn->prepare("SELECT * FROM `attendance` WHERE `date` = '$currentDate' AND `user_id` = ?");
$myAttandence->execute([$user_id]);
$myAttandence = $myAttandence->fetch(PDO::FETCH_ASSOC);

// for get posts details
$posts = $conn->prepare("SELECT * FROM `posts` ORDER BY `created_at` DESC");
$posts->execute();
$posts = $posts->fetchAll(PDO::FETCH_ASSOC);
// for get holidays details
$holidays = $conn->prepare("SELECT * FROM `holiday` WHERE `date` >= '$currentDate'");
$holidays->execute();
$holidays = $holidays->fetch(PDO::FETCH_ASSOC);
// for get project details
$projects = $conn->prepare("SELECT * FROM `projects` WHERE `is_complete` = 0");
$projects->execute();
$projects = $projects->fetchAll(PDO::FETCH_ASSOC);

// get time age
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

$sql = $conn->prepare("SELECT * FROM efficiency WHERE Date(`created_at`) >= CURDATE() - INTERVAL 1 MONTH AND `user_id` = ? ORDER BY `efficiency`.`created_at` DESC");
  $sql->execute([$userId]);
  $sql = $sql->fetchAll(PDO::FETCH_ASSOC);
  $temp = $sql[0]['created_at'];
  $i = 0;
  $arr = [];
  $sum = 0;
  // ... (your previous code)

  foreach ($sql as $value) {
    if (date('d-m-y',strtotime($temp)) == date('d-m-y',strtotime($value['created_at']))) {
      $i++;
      $sum += $value['efficiency'];
    } else {
      if ($i != 0) {
        $arr[] = $sum / $i;
      }
      $temp = $value['created_at'];
      $i = 1;
      $sum = $value['efficiency'];
    }
  }

  // Include the last calculation outside the loop
  if ($i != 0) {
    $arr[] = $sum / $i;
  }

?>
<style>
  .company_update {
    height: -webkit-fill-available;
    overflow: auto;
  }

  .clickable-row {
    cursor: pointer;
  }

  .medium {
    background-color: yellow !important;
  }

  .higher {
    background-color: #ffacac !important;
  }

  .lower {
    background-color: #c6f6c1 !important;
  }

  .birthday-list {
    background: white;
    padding: 10px;
    border-radius: 15px;
    border: 1px dotted black;
  }

  .overflorbox {
    height: 88vh;
    overflow: auto;
  }

  .read_more_text {
    display: none;
  }

  .read_more_btn {
    font-weight: 700;
  }

  .late_login {
    background-color: red;
    padding: 3px 8px;
    border-radius: 5px;
  }

  .morning {
    background-color: black;
  }

  .evening {
    background-color: #7a7a7a;
  }

  .general {
    background-color: #739700;
  }

  .list-group a {
    background-color: #0069BA;
    border-radius: 6px;
    margin: 2px auto;
    color: #fff;
  }

  .birthday-img {
    width: 31px;
    height: 31px;
    flex-shrink: 0;
    border-radius: 50%;
    text-align: center;
  }

  .bor-rad {
    border-radius: 10px;
  }

  .modal-body label {
    font-size: 18px;
    font-weight: 500;

  }

  .collapse-btn a:active:link {
    background: #fff;
    color: #000;
  }
  .boxi{
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .status-text{
    background: #7ac8d0;
    font-size: 20px;
  }
</style>

<div class="modal" id="myModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Upload Post</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
      </div>
      <form id="uploadPost" name="upload">
        <div class="modal-body">
          <div class="mb-3">
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Caption </label>
              <span id="captionerror" style="color:red"></span>
              <div class="input-group">
                <textarea class="form-control" name="caption" id="caption" required></textarea>
                <input type="hidden" class="form-control" name="type" value="postUpload" required>
              </div>
            </div>
            <label for="fileInput" class="form-label file">Select a Post</label>
            <div class="input-group">
              <input type="file" class="form-control Attachment-input" id="inputGroupFile04"
                aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="post" required>
            </div>
            <label for="fileInput" class="form-label file" style="opacity:0">Select a Post</label>
            <div class="input-group">
              <button class="btn btn-primary" style="margin: auto;">Post</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal" id="editModal">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Post</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
      </div>
      <form id="editPostForm" name="upload">
        <div class="modal-body">
          <div class="mb-3">
            <div class="mb-3">
              <label for="project" class=" me-2 control-label p-2 ">Caption </label>
              <span id="captionerror" style="color:red"></span>
              <div class="input-group">
                <textarea class="form-control" name="caption" id="edit_caption" required></textarea>
                <input type="hidden" class="form-control" name="type" value="editPost" required>
                <input type="hidden" class="form-control" name="id" id="post_id" value="" required>
              </div>
            </div>
            <label for="fileInput" class="form-label file">Select a Post</label>
            <div class="input-group">
              <input type="file" class="form-control Attachment-input" id="inputGroupFile04"
                aria-describedby="inputGroupFileAddon04" aria-label="Upload" name="post">
            </div>
            <label for="fileInput" class="form-label file" style="opacity:0">Select a Post</label>
            <div class="input-group">
              <button class="btn btn-primary" style="margin: auto;">Update</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<main style="margin-top: 78px;">
  <div class="container-flude">

    <div class="container-flude px-5">
      <div class="row mt-1  justify-content-center ">



        <div class="col  col-6 p-10  box2 overflorbox">
          <div class="border d-block px-4 py-3 w-100 mb-3 white-back" style=" border: 1px solid black;">
            <h2 class="heading mt-2 mb-2 heading">Attendance</h2>
            <div class="d-flex mb-2">
              <?php
                if($_SESSION['userDetails']['profile'] != ''){
                  $image = $_SESSION['userDetails']['profile'];
                  echo '<img src="images/users/'.$image.'" alt="cake" class="birthday-cake">';
                }else{
                  echo '<img src="images/users/default.jpg" alt="cake" class="birthday-cake">';
                }
              ?>
              
              <div class="ms-2">
                <p class="fw-bold " style="font-size: 22px;">
                  <?php echo $_SESSION['userDetails']['first_name'] . ' ' . $_SESSION['userDetails']['last_name'] ?>
                </p>
                <p>Clocked In : <span style="color:grey"> <?php echo  date('h:i A', strtotime($myAttandence['clock_in_time'])) ?> </span></p>
                    <?php
                      if ($myAttandence['clock_out_time'] != '') {
                        echo '<p>Clocked Out : <span id="clock_out" style="color:grey">';
                        echo date('h:i A', strtotime($myAttandence['clock_out_time']));
                        $flag = 1;
                      } else {
                        echo '<p>Clock Out : <span id="clock_out" style="color:grey">';
                        $flag = 0;
                      }
                    ?>
                  </span></p>
              </div>
            </div> 
            <div class="d-flex gap-3 birthday text-center" style="justify-content: flex-end; ">
              <button class="btn btn-primary" style="font-size: 20px;" disabled style="padding: -10px;" >
                
                Clocked In
                <!-- <i class="fa-solid fa-clock"></i> -->
                <img src="images/clockin.svg" alt="" style="width: 21px; height:21px; margin-top: -5px;">
              </button>
              <button style="font-size: 20px;" class="btn btn-primary" id="clockout_btn" onclick="clockOut()" <?php if ($flag) echo "disabled" ?> >
              <?php if ($flag) echo "Clocked Out" ; else  echo "Clock Out" ;?>
              <img src="images/clockout.svg" alt="" style="width: 21px; height:21px; color:#fff;margin-top: -5px;">
              <!-- <i class="fa-solid fa-clock"></i> -->
              </button>
            </div>
          </div>

          <div class="border d-block px-4 py-3 w-100 mb-3 white-back" style=" border: 1px solid black;">
          <h5>Upcomming </h5>
            <div class="d-flex gap-3 birthday text-center justify-content-between issue-scrollerX">
              <div class="month">
                <div class="d-flex gap-2 justify-content-center" style="flex-wrap: wrap;">
          <?php
            $check_birthday = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0 AND DAYOFMONTH(`dob`) = DAYOFMONTH(CURDATE()) AND MONTH(`dob`) = MONTH(CURDATE()) ORDER BY `dob` DESC");
            $check_birthday->execute();
            $check_birthday = $check_birthday->fetchAll(PDO::FETCH_ASSOC);
            $check_eniv = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0 AND  DAYOFMONTH(`created_at`) = DAYOFMONTH(CURDATE()) AND MONTH(`created_at`) = MONTH(CURDATE())  ORDER BY `created_at` DESC");
            $check_eniv->execute();
            $check_eniv = $check_eniv->fetchAll(PDO::FETCH_ASSOC);
            if($check_birthday){
              echo '<h2 class="heading">Today Birthday</h2>';
              foreach ($check_birthday as $value) {
                if ($value['profile'] != '') {
                  $image = $value['profile'];
                } else {
                  $image = 'default.jpg';
                }
                echo ' <div class="d-flex mb-2 align-items-center">
                <img src="images/users/' . $image . '" alt="cake" class="birthday-cake">
                <div class="ms-2">
                  <p class="fw-bold">Happy Birthday '.$value['first_name'].' '.$value['last_name'].', On this wonderful day,
                    We wish you the best that life has to offer!</p>
                </div>
              </div><hr>';
              }
            }

            if($check_eniv){
              echo '<h2 class="heading">Today Anniversary</h2>';
              foreach ($check_eniv as $value) {
                if ($value['profile'] != '') {
                  $image = $value['profile'];
                } else {
                  $image = 'default.jpg';
                }
                echo ' <div class="d-flex mb-2 align-items-center">
                <img src="images/users/' . $image . '" alt="cake" class="birthday-cake">
                <div class="ms-2">
                  <p class="fw-bold">Happy Anniversary '.$value['first_name'].' '.$value['last_name'].', On this wonderful day,
                    We wish you the best that life has to offer!</p>
                </div>
              </div><hr>';
              }
            }
          ?>
            


                <?php

                if($bday_users){
                  foreach ($bday_users as $bday_user) {
                    if ($bday_user['profile'] != '') {
                      $image = $bday_user['profile'];
                    } else {
                      $image = 'default.jpg';
                    }
                    echo '<div>
                            <img src="images/users/' . $image . '" alt="" class="birthday-img" style="mix-blend-mode: multiply;">
                            <p class="birth-name">' . $bday_user['first_name'] . ' ' . $bday_user['last_name'] . '</p>
                            <p class="birth-name">' . date("d M",strtotime($bday_user['dob'])) . '</p>
                            <p>Birthday</p>
                        </div>';
                  }
                }else{
                  echo 'No Upcomming Birthday <br>';
                }

                echo '</div><div class="d-flex gap-2 justify-content-center" style="flex-wrap: wrap;">';

                if($check_enivs){

                  foreach ($check_enivs as $bday_user) {
                    if ($bday_user['profile'] != '') {
                      $image = $bday_user['profile'];
                    } else {
                      $image = 'default.jpg';
                    }
                    echo '<div>
                            <img src="images/users/' . $image . '" alt="" class="birthday-img" style="mix-blend-mode: multiply;">
                            <p class="birth-name">' . $bday_user['first_name'] . ' ' . $bday_user['last_name'] . '</p>
                            <p class="birth-name">' . date("d M",strtotime($bday_user['created_at'])) . '</p>
                            <p>Work Anniversary</p>
                        </div>';
                  }
                }else{
                  echo 'No Upcomming Anniversary';
                }
// echo '</div>';
                  ?>
                </div>
              </div>
              <div class="imges-right">
                <img src="images/dashboard-img1.png" alt="">
              </div>
            </div>


          </div>
    
          <div class="border d-block px-4 py-3 w-100 UP-Leave shadow text-light">
            <!-- <h2 class="heading">Upcomming Leave</h2> -->
            <div class="d-flex justify-content-between">
              <div>
                <h2 class="fw-bold heading">Upcomming Holiday</h2>
              
                <div class="mt-2">
                  <h1 class="fw-bold Holiday-text"><?php echo $holidays['holiday'] ;?></h1>
                  <p><?php echo date('D',strtotime($holidays['date'])) ;?> <span><?php echo $holidays['date'] ;?></span></p>
                </div>
                
              </div>
              <div class="hollyday-img">
                <img src="images/holiday/<?php echo $holidays['image'] ;?>" alt="">
              </div>
            </div>
          </div>
          <!-- <h2 class="heading">Issue</h2> -->
          <div class="border d-block px-4 py-3  shadow mt-3">
            <div class="issue-scroller" style="height:100%">
                  <?php
if($roleId == 2){

                  ?>
            <h3 class="d-flex justify-content-center">Efficiency</h3>
              <div>
                <canvas id="waveformChart" height="200"></canvas>
              </div>
<?php
}else{


?>
              <div class="scroll-bar" style="height: 250px;">

                <h2 class="fw-bold heading">Project</h2>
                <table class="table table-striped ">
                  <tbody class="" style="font-weight: 600;">
                    <?php foreach($projects as $project) {
                      $assignPr = $conn->prepare("SELECT * FROM `assignproject` WHERE `access` = 1 AND `project_id` = ?");
                      $assignPr->execute([$project['id']]);
                      $assignPr = $assignPr->fetch(PDO::FETCH_ASSOC);

                      // for get project details
                        $pUser = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                        $pUser->execute([$assignPr['user_id']]);
                        $pUser = $pUser->fetch(PDO::FETCH_ASSOC);
                        if($pUser){
                          $name = $pUser['first_name'].' '.$pUser['last_name'];
                        }else{
                          $name = '.';
                        }

                    ?>

                    <tr>
                      <th class><?php echo $project['id']; ?></th>
                      <td><?php echo $project['project_name']; ?></td>
                      <td><span class="close text-center"><?php echo $name ?></span></td>
                    </tr>
                    <?php } ?>
                   
                  </tbody>
                </table>

              </div>
              <?php } ?>
            </div>
          </div>

        </div>
        <div class="col-sm-6  p-10 ">
          <div class=" border px-5 py-3 mb-3 w-100  shadow white-back" >
            <div class="align-items-center">
              <div class="d-flex justify-content-between">
              <div>  <h2 class="heading"><?php echo $_SESSION['userDetails']['first_name'] . ' ' . $_SESSION['userDetails']['last_name'] ?>!👋</h2></div>
                <?php if ($roleId == 1 || in_array('post', $pageAccessList)) { ?>
                <a data-bs-toggle="modal" style=" width:150px;" href="#myModal" class="btn btn-primary text-center  m-0 py-2"><svg class="svg-inline--fa fa-arrow-up-from-bracket me-2" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="arrow-up-from-bracket" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 53 43 96 96 96H352c53 0 96-43 96-96V352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352z"></path></svg> PostUpload</a>
                <?php } ?>
              </div>
              <p class="mb-2">Welcome to Earth On Mapping.</p>
            </div>
            <div class="status d-flex">
              <?php

              if($roleId == 1 || $roleId == 3 || $roleId == 4 || $roleId == 6){
                  $regular = $conn->prepare("SELECT count(`status`) as `status` FROM `leaves` WHERE `status` = 'pending';");
                  $regular->execute();
                  $regular = $regular->fetch(PDO::FETCH_ASSOC);
                  if($regular){
                    echo '
                      <div class="boxi mx-4">
                        <div class="birthday-img status-text">
                          '.$regular['status'].'
                        </div>
                        Leave Approval
                      </div>
                    ';
                  }
              ?>
              <?php
                  $regular = $conn->prepare("SELECT count(`regularisation`) AS `regularisation_count` FROM `attendance` WHERE `regularisation` = 1;");
                  $regular->execute();
                  $regular = $regular->fetch(PDO::FETCH_ASSOC);
                  if($regular){
                    echo '
                      <div class="boxi mx-4">
                        <div class="birthday-img status-text">
                          '.$regular['regularisation_count'].'
                        </div>
                        Attendance Regularisation
                      </div>
                    ';
                  }
                }
              ?>
            </div>
          </div>
          <div class="overflorbox" style="height:65vh">
          
            <?php foreach($posts as $post){  

              if ($roleId == 1 || in_array('post', $pageAccessList)) {
                $action = '<i data-bs-toggle="modal" href="#editModal" class="fas fa-edit" style="margin:0 10px;color:green;cursor: pointer;" onclick="editPost(' . $post['id'] . ')"></i><i class="fas fa-trash" style="color:#cc3300;cursor: pointer;" onclick="deletePost(' . $post['id'] . ')"></i>';
              } else {
                $action = '';
              }

              $users = $conn->prepare("SELECT * FROM `users` WHERE`id` = ?");
              $users->execute([ $post['user_id']]);
              $users = $users->fetch(PDO::FETCH_ASSOC);

            ?>
            <div class=" border px-4 py-4 mb-3 w-100 shadow white-back" id="post_<?php echo $post['id'] ?>">

              <div class="d-flex  border-bottom justify-content-between">
                <div class="company d-flex">
                  <img src="images/users/<?php echo $users['profile'] != '' ? $users['profile'] : 'default.jpg' ?>" alt="" class="birthday-img">
                  <div class="ms-2 mb-2">
                    <p><?php echo $users['first_name'].' ' .$users['last_name'] ;?></p>
                    <p style="font-size: 14px;"><?php echo getTimeAgo($post['created_at']) ?> ago.</p>
                  </div>
                </div>
                <div style="text-align:right" class=""><?php echo $action ?></div>
              </div>

              <div>
                <p class=" mb-2"><?php echo $post['caption'] ;?> </p>
                <div class="company-post">
                  <img src="images/posts/<?php echo $post['image'] ;?>" alt="" width="100%">
                </div>
              </div>

            </div>
            <?php } ?>
          </div>



        </div>
      </div>
    </div>
  </div>
</main>
<?php include "includes/footer.php" ?>
<script src="includes/assets/js/chart.js"></script>
<script>
  function addLeaveBonus(){
    var currentDate = new Date();
    if (currentDate.getDay() === 0 || <?php echo $iStodayHoliday; ?>) {
      Notiflix.Confirm.show(
          'Confirm',
          'What you want to Leave Bonus?',
          'Yes',
          'no',
          () => {
            $.ajax({
              url: 'includes/settings/api/leaveApi.php',
              type: 'POST',
              data: { type: 'addLeaveBonus' },
              dataType: 'json',
              success: function (response) {
                notyf.success(response.message);
              },
              error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
              }
            });
          },
      );
    }
  }

  function clockOut() {
    $.ajax({
      url: 'includes/settings/api/attendanceApi.php',
      type: 'POST',
      data: { type: 'clockOut' },
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        $("#clock_out").text(response.clockOut);
        $("#clockout_btn").prop("disabled", true);
        addLeaveBonus();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
      }
    });
  }

  $('#uploadPost').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    Notiflix.Loading.standard();
    var caption = $('#caption').val();
    $.ajax({
      url: 'includes/settings/api/postApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        location.reload();
        Notiflix.Loading.remove();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
        Notiflix.Loading.remove();
      }
    });
  })

  $('#editPostForm').submit(function (event) {
    event.preventDefault();
    var formData = new FormData(this);
    Notiflix.Loading.standard();
    $.ajax({
      url: 'includes/settings/api/postApi.php',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (response) {
        notyf.success(response.message);
        location.reload();
      },
      error: function (xhr, status, error) {
        var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
        notyf.error(errorMessage);
        location.reload();
      }
    });
  })

  function deletePost(id) {
    Notiflix.Confirm.show(
      'Confirmation',
      'Do you want to delete this post?',
      'Yes',
      'No',
      function () {
        $.ajax({
          url: 'includes/settings/api/postApi.php',
          type: 'POST',
          data: {
            type: 'deleteUpload',
            id: id
          },
          dataType: 'json',
          success: function (response) {
            notyf.success(response.message);
            $('#post_' + id).remove();
          },
        });
      });
  }

  function getPushiment() {
    $.ajax({
      url: 'includes/settings/api/leaveApi.php',
      type: 'GET',
      data: {
        type: 'getPushiment',
      },
      dataType: 'json',
      success: function (response) {
        Notiflix.Confirm.show(
          'Without Leave Absent',
          'Please Apply Leave '+response,
          'Yes',
          'No',
          function () {
          }
        );
      },
    });
  }

  getPushiment();


  function editPost(id) {
    $('#post_id').val('');
    $("#edit_caption").val('');
    $.ajax({
      url: 'includes/settings/api/postApi.php',
      type: 'GET',
      data: {
        type: 'getPost',
        id: id
      },
      dataType: 'json',
      success: function (response) {
        $('#post_id').val(id);
        $("#edit_caption").val(response.caption);
      },
    });
  }

  var data = <?php echo json_encode($arr) ?>;

// Create a line chart
var ctx = document.getElementById('waveformChart').getContext('2d');
var waveformChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: Array.from({ length: data.length }, (_, i) => `Day ${i + 1}`),
    datasets: [{
      label: 'Waveform Data',
      data: data,
      fill: false,
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 2
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

</script>