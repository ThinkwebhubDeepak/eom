<?php
$page_name = 'feedback-task';
include "includes/header.php";
if ($roleId != 1 && !(in_array($page_name, $pageAccessList))) {
    echo '<script>window.location.href = "index.php"</script>';
}

$sql = $conn->prepare('SELECT * FROM `projects` WHERE `is_complete` = 1 ORDER BY `created_at` DESC');
$sql->execute();
$projects = $sql->fetchAll(PDO::FETCH_ASSOC);

$feedback = $conn->prepare("SELECT * FROM `projectefficiency` WHERE `user_id` = ? AND `type` = 'feedback' AND `status` = 'start'");
$feedback->execute([$_SESSION['userId']]);
$feedback = $feedback->fetch(PDO::FETCH_ASSOC);
?>
<style>
    .hidden {
        display: none !important;
    }

    .complete-btn {
        position: fixed;
        z-index: 99999;
        left: 90%;
        top: 10%;
    }

    .pre-loader {
        position: fixed;
        width: 100%;
        height: 100%;
        z-index: 9999;
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
</style>
<div class="pre-loader <?php echo !($feedback) ? 'hidden' : '' ?>">
    <button class="btn btn-warning complete-btn" onclick="completeFeedback()">Complete Feedback</button>
    <div id="cloud-intro"></div>
    <div class="drone">
        <p class="loadingText">Feedback . . .</p>
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

<main style="margin-top: 100px;" class="<?php echo ($feedback) ? 'hidden' : '' ?>">
    <div class="container-flude pt-5" div class="container-flude pt-5">
        <div class="container-flude px-5">
            <div class="d-flex justify-content-between " style="padding: 0 0 40px 0;">
                <p class="fw-bold page_heading">Feedback Task</p>
                <div style="display: flex; height:40px;">
                    <select name="project_id" id="project_id" class="form-control" style="margin :0 15px;">
                        <option value="">Search Project</option>
                        <?php
                        foreach ($projects as $value) {
                            echo '<option value="' . $value['id'] . '">' . $value['project_name'] . '</option>';
                        }
                        ?>
                    </select>
                    <button style="margin:0 20px;width:300px" onclick="projectFeedback()" class="btn btn-warning">Start Feedback</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include "includes/footer.php" ?>
<script>
    function projectFeedback() {
        var project_id = $('#project_id').val();
        if (project_id == '') {
            notyf.error('Select Project');
        } else {
            $.ajax({
                url: 'includes/settings/api/projectPreparationApi.php',
                type: 'POST',
                data: {
                    type: 'projectFeedback',
                    project_id: project_id
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

    function completeFeedback() {
        $.ajax({
            url: 'includes/settings/api/projectPreparationApi.php',
            type: 'POST',
            data: {
                type: 'completeFeedback',
                project_id: <?php echo $feedback['project_id'] ?? 0 ?>
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