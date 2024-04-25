<?php
$page_name = 'training-task';
include "includes/header.php";
if ($roleId != 1 && !(in_array($page_name, $pageAccessList))) {
    echo '<script>window.location.href = "index.php"</script>';
}

$sql = $conn->prepare('SELECT * FROM `projects` WHERE `is_complete` = 0 ORDER BY `created_at` DESC');
$sql->execute();
$projects = $sql->fetchAll(PDO::FETCH_ASSOC);

$training = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `type` = 'training' AND `status` = 'start'");
$training->execute([$_SESSION['userId']]);
$training = $training->fetch(PDO::FETCH_ASSOC);
?>
<style>
    .hidden {
        display: none !important;
    }

    .complete-btn {
        position: fixed;
        z-index: 3000;
        left: 90%;
        top: 10%;
    }

    .pre-loader {
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: 3000;
        display: block;
        background-color: #007ced;
        background: linear-gradient(to bottom, #007ced 1%, #cce7ff 100%);
    }

    #cloud-intro {
        position: relative;
        height: 100%;
        background: url(https://i.imgur.com/VwO4ylN.png);
        background: url(https://i.imgur.com/VwO4ylN.png) 0 200px,
            url(https://i.imgur.com/sWifZt4.png) 0 300px,
            url(https://i.imgur.com/hleo0UW.png) 100px 250px;
        animation: wind 20s linear infinite;
    }

    .drone {
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
        width: 250px;
        height: 80px;
        background-color: #212121;
        border-radius: 10px;
    }

    .arm {
        position: absolute;
        width: 40.0160064px;
        height: 150.06002401px;
        background-color: #212121;
    }

    .arm:before {
        content: "";
        width: 50.020008px;
        height: 50.020008px;
        background: #717171;
        position: absolute;
        top: 0;
        left: 50%;
        margin-top: -25.010004px;
        margin-left: -25.010004px;
        border-radius: 50%;
        z-index: 1;
    }

    .arm:after {
        content: "";
        position: absolute;
        top: -30.0120048px;
        left: -10.0040016px;
        width: 60.0240096px;
        height: 60.0240096px;
        border-radius: 30%;
        background-color: #212121;
    }

    .arm.top-left-arm,
    .arm.top-right-arm {
        top: -100px;
    }

    .arm.bottom-left-arm,
    .arm.bottom-right-arm {
        bottom: -100px;
    }

    .arm.top-right-arm,
    .arm.bottom-right-arm {
        right: -50.020008px;
    }

    .arm.top-left-arm,
    .arm.bottom-left-arm {
        left: -50.020008px;
    }

    .arm.top-left-arm {
        -webkit-transform: rotate(-45deg);
        transform: rotate(-45deg);
    }

    .arm.top-right-arm {
        -webkit-transform: rotate(45deg);
        transform: rotate(45deg);
    }

    .arm.bottom-left-arm {
        -webkit-transform: rotate(-135deg);
        transform: rotate(-135deg);
    }

    .arm.bottom-right-arm {
        -webkit-transform: rotate(135deg);
        transform: rotate(135deg);
    }

    .prop {
        position: absolute;
        top: -70px;
        left: 50%;
        width: 18px;
        height: 140px;
        margin-left: -9px;
        background-color: coral;
        z-index: 1;
        border-top-right-radius: 5px;
        border-top-left-radius: 15px;
        border-bottom-right-radius: 15px;
        border-bottom-left-radius: 5px;
        -webkit-transform: rotate(30deg);
        transform: rotate(30deg);
        -webkit-animation: rotate 300ms linear infinite;
        animation: rotate 400ms linear infinite;
    }

    .loadingText {
        text-align: center;
        height: 25px;
        padding-top: 25px;
        font-family: sans-serif;
        font-size: 20px;
        color: #717171;
    }

    @media only screen
    /* iphone 2G, 3G, 4, 4S */

    and (min-device-width: 320px) and (max-device-width: 480px) {
        .drone {
            width: calc(250px / 2);
            height: calc(80px / 2);
        }

        .arm {
            width: calc(40.0160064px / 2);
            height: calc(150.06002401px / 2);
        }

        .arm:before {
            content: "";
            width: calc(50.020008px / 2);
            height: calc(50.020008px / 2);
            margin-top: calc(-25.010004px / 2);
            margin-left: calc(-25.010004px / 2);
        }

        .arm:after {
            top: calc(-30.0120048px / 2);
            left: calc(-10.0040016px / 2);
            width: calc(60.0240096px / 2);
            height: calc(60.0240096px / 2);
        }

        .arm.top-left-arm,
        .arm.top-right-arm {
            top: calc(-100px / 2);
        }

        .arm.bottom-left-arm,
        .arm.bottom-right-arm {
            bottom: calc(-100px / 2);
        }

        .arm.top-right-arm,
        .arm.bottom-right-arm {
            right: calc(-50.020008px / 2);
        }

        .arm.top-left-arm,
        .arm.bottom-left-arm {
            left: calc(-50.020008px / 2);
        }

        .prop {
            top: calc(-70px / 2);
            left: calc(50% / 2);
            width: calc(18px / 2);
            height: calc(140px / 2);
            margin-left: calc(-9px / 8);
        }

        .loadingText {
            height: calc(25px / 2);
            padding-top: calc(25px / 2);
            font-size: calc(25px / 2);
        }
    }

    @media only screen
    /* iphone 5 & 5s */

    and (min-device-width: 320px) and (max-device-width: 568px) {
        .drone {
            width: calc(250px / 2);
            height: calc(80px / 2);
        }

        .arm {
            width: calc(40.0160064px / 2);
            height: calc(150.06002401px / 2);
        }

        .arm:before {
            content: "";
            width: calc(50.020008px / 2);
            height: calc(50.020008px / 2);
            margin-top: calc(-25.010004px / 2);
            margin-left: calc(-25.010004px / 2);
        }

        .arm:after {
            top: calc(-30.0120048px / 2);
            left: calc(-10.0040016px / 2);
            width: calc(60.0240096px / 2);
            height: calc(60.0240096px / 2);
        }

        .arm.top-left-arm,
        .arm.top-right-arm {
            top: calc(-100px / 2);
        }

        .arm.bottom-left-arm,
        .arm.bottom-right-arm {
            bottom: calc(-100px / 2);
        }

        .arm.top-right-arm,
        .arm.bottom-right-arm {
            right: calc(-50.020008px / 2);
        }

        .arm.top-left-arm,
        .arm.bottom-left-arm {
            left: calc(-50.020008px / 2);
        }

        .prop {
            top: calc(-70px / 2);
            left: calc(50% / 2);
            width: calc(18px / 2);
            height: calc(140px / 2);
            margin-left: calc(-9px / 8);
        }

        .loadingText {
            height: calc(25px / 2);
            padding-top: calc(25px / 2);
            font-size: calc(25px / 2);
        }
    }

    @media only screen
    /* iphone 6 Plus */

    and (min-device-width: 414px) and (max-device-width: 736px) {
        .drone {
            width: calc(250px / 2);
            height: calc(80px / 2);
        }

        .arm {
            width: calc(40.0160064px / 2);
            height: calc(150.06002401px / 2);
        }

        .arm:before {
            content: "";
            width: calc(50.020008px / 2);
            height: calc(50.020008px / 2);
            margin-top: calc(-25.010004px / 2);
            margin-left: calc(-25.010004px / 2);
        }

        .arm:after {
            top: calc(-30.0120048px / 2);
            left: calc(-10.0040016px / 2);
            width: calc(60.0240096px / 2);
            height: calc(60.0240096px / 2);
        }

        .arm.top-left-arm,
        .arm.top-right-arm {
            top: calc(-100px / 2);
        }

        .arm.bottom-left-arm,
        .arm.bottom-right-arm {
            bottom: calc(-100px / 2);
        }

        .arm.top-right-arm,
        .arm.bottom-right-arm {
            right: calc(-50.020008px / 2);
        }

        .arm.top-left-arm,
        .arm.bottom-left-arm {
            left: calc(-50.020008px / 2);
        }

        .prop {
            top: calc(-70px / 2);
            left: calc(50% / 2);
            width: calc(18px / 2);
            height: calc(140px / 2);
            margin-left: calc(-9px / 8);
        }

        .loadingText {
            height: calc(25px / 2);
            padding-top: calc(25px / 2);
            font-size: calc(25px / 2);
        }
    }

    @media only screen
    /* iphone 6 */

    and (min-device-width: 375px) and (max-device-width: 667px) {
        .drone {
            width: calc(250px / 2);
            height: calc(80px / 2);
        }

        .arm {
            width: calc(40.0160064px / 2);
            height: calc(150.06002401px / 2);
        }

        .arm:before {
            content: "";
            width: calc(50.020008px / 2);
            height: calc(50.020008px / 2);
            margin-top: calc(-25.010004px / 2);
            margin-left: calc(-25.010004px / 2);
        }

        .arm:after {
            top: calc(-30.0120048px / 2);
            left: calc(-10.0040016px / 2);
            width: calc(60.0240096px / 2);
            height: calc(60.0240096px / 2);
        }

        .arm.top-left-arm,
        .arm.top-right-arm {
            top: calc(-100px / 2);
        }

        .arm.bottom-left-arm,
        .arm.bottom-right-arm {
            bottom: calc(-100px / 2);
        }

        .arm.top-right-arm,
        .arm.bottom-right-arm {
            right: calc(-50.020008px / 2);
        }

        .arm.top-left-arm,
        .arm.bottom-left-arm {
            left: calc(-50.020008px / 2);
        }

        .prop {
            top: calc(-70px / 2);
            left: calc(50% / 2);
            width: calc(18px / 2);
            height: calc(140px / 2);
            margin-left: calc(-9px / 8);
        }

        .loadingText {
            height: calc(25px / 2);
            padding-top: calc(25px / 2);
            font-size: calc(25px / 2);
        }
    }

    @keyframes wind {
        0% {
            background-position: 0 -200px, 0 -300px, -100px -250px;
        }

        100% {
            background-position: -1000px -200px, -1200px -300px, -1100px -250px;
        }

    }

    @-webkit-keyframes rotate {
        to {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    @keyframes rotate {
        to {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    .modal {
        z-index: 3000;
    }
</style>
<div class="pre-loader <?php echo !($training) ? 'hidden' : '' ?>">
    <button class="btn btn-warning complete-btn" onclick="completetraining()">Complete training</button>
    <a class="btn btn-danger complete-btn" style="left:85%" data-bs-toggle="modal" data-bs-target="#breakModal" onclick="getBreak()" style="margin :0 5px">Break</a>
    <div id="cloud-intro"></div>
    <div class="drone">
        <p class="loadingText">Training . . .</p>
        <div class="arm top-left-arm">
            <div class="prop"></div>
        </div>
        <div class="arm top-right-arm">
            <div class="prop"></div>
        </div>
        <div class="arm bottom-left-arm">
            <div class="prop"></div>
        </div>
        <div class="arm bottom-right-arm">
            <div class="prop"></div>
        </div>
    </div>
</div>

<main style="margin-top: 100px;" class="<?php echo ($training) ? 'hidden' : '' ?>">
    <div class="container-flude pt-5" div class="container-flude pt-5">
        <div class="container-flude px-5">
            <div class="d-flex justify-content-between " style="padding: 0 0 40px 0;">
                <p class="fw-bold page_heading">Training</p>
                <div style="display: flex; height:40px;">
                    <select name="project_id" id="project_id" class="form-control" style="margin :0 15px;">
                        <option value="">Search Project</option>
                        <option value="0">R&D</option>
                        <?php
                        foreach ($projects as $value) {
                            echo '<option value="' . $value['id'] . '">' . $value['project_name'] . '</option>';
                        }
                        ?>
                    </select>
                    <select name="task_type" id="task_type" class="form-control" style="margin :0 15px;">
                        <option value="">Select Task</option>
                        <option value="pro">Production</option>
                        <option value="qc">Qc</option>
                        <option value="qa">Qa</option>
                        <option value="vector">Vector</option>
                        <option value="R&D">R&D</option>
                        <option value="feedback">Feedback</option>
                    </select>
                    <button style="margin:0 20px;width:300px" onclick="projecttraining()" class="btn btn-warning">Start</button>
                </div>
            </div>
        </div>
    </div>
</main>

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
                                    <select name="break_type" id="break_type" class="form-control" required>
                                        <option value="" Default>Select Break Type</option>
                                        <option value="break_fast">Break Fast</option>
                                        <option value="snacks">Snacks</option>
                                        <option value="lunch">Lunch</option>
                                        <option value="team_meeting">Team Meeting</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="hidden" name="type" value="addOtherTaskBreak" required>
                                    <input type="hidden" id="project_id" name="project_id" value="<?php echo $training['project_id'] ?>" required>
                                    <input type="hidden" id="project_id" name="task_id" value="<?php echo $training['type'] ?>" required>
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

<?php include "includes/footer.php" ?>
<script>
    $('#break_type').change(function() {
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

    function projecttraining() {
        var project_id = $('#project_id').val();
        var task_type = $('#task_type').val();
        if (project_id == '') {
            notyf.error('Select Project');
        } else {
            if (task_type == '') {
                notyf.error('Select Task');
            } else {
                $.ajax({
                    url: 'includes/settings/api/projectPreparationApi.php',
                    type: 'POST',
                    data: {
                        type: 'projectTraining',
                        project_id: project_id,
                        task: task_type
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
        }
    }

    function completetraining() {
        $.ajax({
            url: 'includes/settings/api/projectPreparationApi.php',
            type: 'POST',
            data: {
                type: 'completeTraining',
                project_id: <?php echo $training['project_id'] ?? 0 ?>
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


    $('#addBreak').submit(function(event) {
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
            success: function(response) {
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
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        });
    });

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
  if (countDownDate) {
    setInterval(function() {
      var now = new Date().getTime();
      var distance = countDownDate - now;
      const minutes = Math.floor(distance / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);
      document.getElementById("timebreaktext").innerHTML = minutes + " min " + seconds + " sec";
    }, 1000);
  }

  
  Notiflix.Loading.custom({
    customSvgCode: `<svg xmlns="http://www.w3.org/2000/svg" id="NXLoadingHourglass" fill="#32c682" width="500px" height="500px" viewBox="0 0 200 200"><style>@-webkit-keyframes NXhourglass5-animation{0%{-webkit-transform:scale(1,1);transform:scale(1,1)}16.67%{-webkit-transform:scale(1,.8);transform:scale(1,.8)}33.33%{-webkit-transform:scale(.88,.6);transform:scale(.88,.6)}37.5%{-webkit-transform:scale(.85,.55);transform:scale(.85,.55)}41.67%{-webkit-transform:scale(.8,.5);transform:scale(.8,.5)}45.83%{-webkit-transform:scale(.75,.45);transform:scale(.75,.45)}50%{-webkit-transform:scale(.7,.4);transform:scale(.7,.4)}54.17%{-webkit-transform:scale(.6,.35);transform:scale(.6,.35)}58.33%{-webkit-transform:scale(.5,.3);transform:scale(.5,.3)}83.33%,to{-webkit-transform:scale(.2,0);transform:scale(.2,0)}}@keyframes NXhourglass5-animation{0%{-webkit-transform:scale(1,1);transform:scale(1,1)}16.67%{-webkit-transform:scale(1,.8);transform:scale(1,.8)}33.33%{-webkit-transform:scale(.88,.6);transform:scale(.88,.6)}37.5%{-webkit-transform:scale(.85,.55);transform:scale(.85,.55)}41.67%{-webkit-transform:scale(.8,.5);transform:scale(.8,.5)}45.83%{-webkit-transform:scale(.75,.45);transform:scale(.75,.45)}50%{-webkit-transform:scale(.7,.4);transform:scale(.7,.4)}54.17%{-webkit-transform:scale(.6,.35);transform:scale(.6,.35)}58.33%{-webkit-transform:scale(.5,.3);transform:scale(.5,.3)}83.33%,to{-webkit-transform:scale(.2,0);transform:scale(.2,0)}}@-webkit-keyframes NXhourglass3-animation{0%{-webkit-transform:scale(1,.02);transform:scale(1,.02)}79.17%,to{-webkit-transform:scale(1,1);transform:scale(1,1)}}@keyframes NXhourglass3-animation{0%{-webkit-transform:scale(1,.02);transform:scale(1,.02)}79.17%,to{-webkit-transform:scale(1,1);transform:scale(1,1)}}@-webkit-keyframes NXhourglass1-animation{0%,83.33%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(180deg);transform:rotate(180deg)}}@keyframes NXhourglass1-animation{0%,83.33%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}to{-webkit-transform:rotate(180deg);transform:rotate(180deg)}}#NXLoadingHourglass *{-webkit-animation-duration:1.2s;animation-duration:1.2s;-webkit-animation-iteration-count:infinite;animation-iteration-count:infinite;-webkit-animation-timing-function:cubic-bezier(0,0,1,1);animation-timing-function:cubic-bezier(0,0,1,1)}</style><g data-animator-group="true" data-animator-type="1" style="-webkit-animation-name:NXhourglass1-animation;animation-name:NXhourglass1-animation;-webkit-transform-origin:50% 50%;transform-origin:50% 50%; scale: 0.5;transform-box:fill-box"><g id="NXhourglass2" fill="inherit"><g data-animator-group="true" data-animator-type="2" style="-webkit-animation-name:NXhourglass3-animation;animation-name:NXhourglass3-animation;-webkit-animation-timing-function:cubic-bezier(.42,0,.58,1);animation-timing-function:cubic-bezier(.42,0,.58,1);-webkit-transform-origin:50% 100%;transform-origin:50% 100%;transform-box:fill-box" opacity=".4"><path id="NXhourglass4" d="M100 100l-34.38 32.08v31.14h68.76v-31.14z"></path></g><g data-animator-group="true" data-animator-type="2" style="-webkit-animation-name:NXhourglass5-animation;animation-name:NXhourglass5-animation;-webkit-transform-origin:50% 100%;transform-origin:50% 100%;transform-box:fill-box" opacity=".4"><path id="NXhourglass6" d="M100 100L65.62 67.92V36.78h68.76v31.14z"></path></g><path d="M51.14 38.89h8.33v14.93c0 15.1 8.29 28.99 23.34 39.1 1.88 1.25 3.04 3.97 3.04 7.08s-1.16 5.83-3.04 7.09c-15.05 10.1-23.34 23.99-23.34 39.09v14.93h-8.33a4.859 4.859 0 1 0 0 9.72h97.72a4.859 4.859 0 1 0 0-9.72h-8.33v-14.93c0-15.1-8.29-28.99-23.34-39.09-1.88-1.26-3.04-3.98-3.04-7.09s1.16-5.83 3.04-7.08c15.05-10.11 23.34-24 23.34-39.1V38.89h8.33a4.859 4.859 0 1 0 0-9.72H51.14a4.859 4.859 0 1 0 0 9.72zm79.67 14.93c0 15.87-11.93 26.25-19.04 31.03-4.6 3.08-7.34 8.75-7.34 15.15 0 6.41 2.74 12.07 7.34 15.15 7.11 4.78 19.04 15.16 19.04 31.03v14.93H69.19v-14.93c0-15.87 11.93-26.25 19.04-31.02 4.6-3.09 7.34-8.75 7.34-15.16 0-6.4-2.74-12.07-7.34-15.15-7.11-4.78-19.04-15.16-19.04-31.03V38.89h61.62v14.93z"></path></g></g>
        <text id="timebreaktext" transform="matrix(1 0 0 1 20 200)" fill="#49BA81" font-family="'MyriadPro-Regular'" font-size="30px"></text>
        </svg>`,
  });
  setTimeout(setTimeAndRemoveLoader, differenceInMilliseconds);
</script>