</head>
<style>
    .sidebar {
        max-width: 100vh !important;
        overflow: auto;
    }
</style>

<body>
    <header>
        <!-- Sidebar -->
        <nav id="sidebarMenu" class=" d-lg-block sidebar   d-flex">
            <div>
                <div class="position-sticky">
                    <div class="list-group list-group-flush mx-3 " style="margin-top: 30px;">
                        <a href="index.php" class=" list-group-item-action py-2  ripple <?php echo $page_name == 'index' ? 'active' : '' ?>" aria-current="true">
                            <i class="fas fa-tachometer-alt fa-fw me-1 ms-3"></i><span>Main Dashboard</span>
                        </a>
                        <?php if ($roleId != 1) { ?>
                            <a class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'pro-task-list' || $page_name == 'qc-task-list' || $page_name == 'qa-task-list' || $page_name == 'vector-task-list') ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseWorkList" aria-expanded="false" aria-controls="collapseWorkList">
                                <i class="fas fa-chart-area fa-fw me-1"></i><span>Work List</span>
                            </a>
                            <div class="collapse  <?php echo ($page_name == 'pro-task-list' || $page_name == 'qc-task-list' || $page_name == 'qa-task-list' || $page_name == 'vector-task-list') ? 'show' : '' ?>" id="collapseWorkList">
                                <div class="d-flex flex-column w-100 btn-group list-group collapse-btn">
                                    <a href="pro-task-list.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'pro-task-list' ? 'active' : '' ?>"><i class="fa-solid fa-list-check ms-3 me-1"></i><span>Pro Task</span></a>
                                    <a href="qc-task-list.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'qc-task-list' ? 'active' : '' ?>"><i class="fa-solid fa-thumbtack me-1 ms-3"></i><span>QC Task</span></a>
                                    <a href="qa-task-list.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'qa-task-list' ? 'active' : '' ?>"><i class="fa-solid fa-bangladeshi-taka-sign me-1 ms-3"></i><span>QA Task</span></a>
                                    <a href="vector-task-list.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'vector-task-list' ? 'active' : '' ?>"><i class="fa-solid fa-staff-snake me-1 ms-3"></i><span>Vector Task</span></a>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($roleId != 1) { ?>
                            <a class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'finalization-task' || $page_name == 'feedback-task' || $page_name == 'preparation-task') ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseWorkList" aria-expanded="false" aria-controls="collapseWorkList">
                                <i class="fas fa-chart-area fa-fw me-1"></i><span>Other Task</span>
                            </a>
                            <div class="collapse  <?php echo ($page_name == 'finalization-task' || $page_name == 'feedback-task' || $page_name == 'preparation-task')  ? 'show' : '' ?>" id="collapseWorkList">
                                <div class="d-flex flex-column w-100 btn-group list-group collapse-btn">

                                <?php if ($roleId == 1 || in_array('preparation-task', $pageAccessList)) { ?>

                                    <a href="preparation-task.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'preparation-task' ? 'active' : '' ?>"><i class="fa-solid fa-thumbtack me-1 ms-3"></i><span>Preparation</span></a>

                                <?php } ?>
                                <?php if ($roleId == 1 || in_array('finalization-task', $pageAccessList)) { ?>

                                    <a href="finalization-task.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'finalization-taskt' ? 'active' : '' ?>"><i class="fa-solid fa-list-check ms-3 me-1"></i><span>Finalization</span></a>

                                <?php } ?>   
                                <?php if ($roleId == 1 || in_array('feedback-task', $pageAccessList)) { ?>

                                    <a href="feedback-task.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'feedback-task' ? 'active' : '' ?>"><i class="fa-solid fa-bangladeshi-taka-sign me-1 ms-3"></i><span>Feedback</span></a>
                                <?php } ?>      
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($roleId == 1 || in_array('create-project', $pageAccessList) || in_array('create-task', $pageAccessList) || in_array('project-list', $pageAccessList) || in_array('complete-project-list', $pageAccessList)) { ?>
                            <a class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'create-project' || $page_name == 'project-list' || $page_name == 'create-task' || $page_name == 'complete-project-list') ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseProject" aria-expanded="false" aria-controls="collapseProject">
                                <i class="fas fa-building fa-fw me-1"></i><span>Project</span>
                            </a>
                            <div class="collapse  <?php echo ($page_name == 'create-project' || $page_name == 'complete-project-list' || $page_name == 'create-task' || $page_name == 'project-list') ? 'show' : '' ?>" id="collapseProject">
                                <div class="d-flex flex-column w-100 btn-group list-group collapse-btn">
                                    <?php if ($roleId == 1 || in_array('create-project', $pageAccessList)) { ?>
                                        <a href="create-project.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'create-project' ? 'active' : '' ?>"><i class="fa-solid fa-user-shield me-1 ms-3"></i><span>Create Project</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('create-task', $pageAccessList)) { ?>
                                        <a href="create-task.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'create-task' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line me-1 ms-3"></i><span>Create Task</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('project-list', $pageAccessList)) { ?>
                                        <a href="project-list.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'project-list' ? 'active' : '' ?>"><i class="fa-solid fa-list-check me-1 ms-3"></i><span>Project List</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('complete-project-list', $pageAccessList)) { ?>
                                        <a href="complete-project-list.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'complete-project-list' ? 'active' : '' ?>"><i class="fa-solid fa-shield me-1 ms-3"></i><span>Complete List</span></a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>


                        <?php if ($roleId == 1 || in_array('assign-pro', $pageAccessList) || in_array('assign-qc', $pageAccessList) || in_array('assign-qa', $pageAccessList) || in_array('assign-vector', $pageAccessList)) { ?>
                            <a class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'assign-pro' || $page_name == 'assign-qc' || $page_name == 'assign-qa') ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseAssign" aria-expanded="false" aria-controls="collapseAssign">
                                <i class="fas fa-list-check fa-fw me-1"></i><span>Assign Task</span>
                            </a>
                            <div class="collapse  <?php echo ($page_name == 'assign-pro' || $page_name == 'assign-qc' || $page_name == 'assign-vector' || $page_name == 'assign-qa') ? 'show' : '' ?>" id="collapseAssign">
                                <div class="d-flex flex-column w-100 btn-group list-group collapse-btn">
                                    <?php if ($roleId == 1 || in_array('assign-pro', $pageAccessList)) { ?>
                                        <a href="assign-pro.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'assign-pro' ? 'active' : '' ?>"><i class="fa-solid fa-people-arrows me-1 ms-3"></i><span>Assign Pro</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('assign-qc', $pageAccessList)) { ?>
                                        <a href="assign-qc.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'assign-qc' ? 'active' : '' ?>"><i class="fa-solid fa-code-compare me-1 ms-3"></i><span>Assign QC</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('assign-qa', $pageAccessList)) { ?>
                                        <a href="assign-qa.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'assign-qa' ? 'active' : '' ?>"><i class="fa-solid fa-user-check me-1 ms-3"></i><span>Assign QA</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('assign-vector', $pageAccessList)) { ?>
                                        <a href="assign-vector.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'assign-vector' ? 'active' : '' ?>"><i class="fa-solid fa-chart-simple me-1 ms-3"></i><span>Assign Vector</span></a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>


                        <?php if (in_array('assigned-project', $pageAccessList)) { ?>
                            <a href="assign-project.php" class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'assigned-project') ? 'active' : '' ?>">
                                <i class="fa-solid fa-shield me-1"></i><span>Assigned Project</span>
                            </a>
                        <?php } ?>

                        <?php if ($roleId == 1 || in_array('resign-task', $pageAccessList)) { ?>
                            <a href="reassign-pro-task.php" class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'resign-task') ? 'active' : '' ?>">
                                <i class="fa-solid fa-shield me-1"></i><span>Reassign Task</span>
                            </a>
                        <?php } ?>

                        <?php if ($roleId == 1) { ?>
                            <a href="authentication.php" class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'authentication') ? 'active' : '' ?>">
                                <i class="fas fa-chart-pie fa-fw me-1"></i><span>Authentication</span>
                            </a>
                        <?php } ?>
                        <a class="list-group-item list-group-item-action ripple <?php echo ($page_name == 'apply-leave' || $page_name == 'leave-bonus' || $page_name == 'approve-leave'  || $page_name == 'history' || $page_name == 'pending') ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseLeave" aria-expanded="false" aria-controls="collapseLeave">
                            <i class="fas fa-chart-bar fa-fw me-1"></i><span>Leave Application</span></a>
                        <div class="collapse  <?php echo ($page_name == 'apply-leave' || $page_name == 'approve-leave' || $page_name == 'leave-bonus' || $page_name == 'history' || $page_name == 'pending') ? 'show' : '' ?>" id="collapseLeave">
                            <div class="d-flex flex-column w-100 btn-group list-group collapse-btn">
                                <a href="leave.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'apply-leave' ? 'active' : '' ?>"><i class="fa-solid fa-power-off me-1 ms-3"></i><span>Leave Apply</span></a>
                                <a href="history.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'history' ? 'active' : '' ?>"><i class="fa-solid fa-landmark me-1 ms-3"></i><span>History</span></a>
                                <a href="pending.php" href="" class="btn list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'pending' ? 'active' : '' ?>"><i class="fa-solid fa-hourglass-end me-1 ms-3"></i><span>Pending</span></a>
                                <!-- middle        -->
                                <?php if ($roleId == 1 || in_array('approve-leave', $pageAccessList)) { ?>
                                    <a href="approve-leave.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'approve-leave' ? 'active' : '' ?>"><i class="fa-solid fa-person-circle-check me-1 ms-3"></i><span>Approve Leave</span></a>
                                <?php } ?>
                                <?php if ($roleId == 1 || in_array('leave-bonus', $pageAccessList)) { ?>
                                    <a href="leave-bonus.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'leave-bonus' ? 'active' : '' ?>"><i class="fa-solid fa-person-circle-check me-1 ms-3"></i><span>Leave Bonus</span></a>
                                <?php } ?>
                                <!-- middle        -->
                            </div>
                        </div>
                        <?php if ($roleId == 1 || in_array('add-employee', $pageAccessList) || in_array('terminated-employee', $pageAccessList) || in_array('employee-list', $pageAccessList) || in_array('employee-salary', $pageAccessList) || in_array('employee-status', $pageAccessList)) { ?>
                            <a class="list-group-item list-group-item-action ripple  <?php echo ($page_name == 'employee-list' || $page_name == 'add-employee' || $page_name == 'terminated-employee' || $page_name == 'employee-salary' || $page_name == 'employee-status') ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseEmployee" aria-expanded="false" aria-controls="collapseEmployee">
                                <i class="fas fa-users fa-fw me-1"></i><span>Employee</span></a>
                            <div class="collapse <?php echo ($page_name == 'employee-list' || $page_name == 'add-employee' || $page_name == 'terminated-employee' || $page_name == 'employee-status' || $page_name == 'employee-salary') ? 'show' : '' ?>" id="collapseEmployee">
                                <div class="d-flex flex-column w-100 btn-group list-group collapse-btn">

                                    <?php if ($roleId == 1 || in_array('add-employee', $pageAccessList)) { ?>
                                        <a href="add-employee.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'add-employee' ? 'active' : '' ?>"><i class="fa-solid fa-user-plus me-1 ms-3"></i><span>Add Employee</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('employee-list', $pageAccessList)) { ?>
                                        <a href="employee-list.php" class="btn  list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'employee-list' ? 'active' : '' ?>"><i class="fa-solid fa-users me-1 ms-3"></i><span>Employee List</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('terminated-employee', $pageAccessList)) { ?>
                                        <a href="terminated-employee.php" class="btn  list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'terminated-employee' ? 'active' : '' ?>"><i class="fa-solid fa-user-slash me-1 ms-2"></i><span>Resign Employee</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('employee-status', $pageAccessList)) { ?>
                                        <a href="employee-status.php" class="btn  list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'employee-status' ? 'active' : '' ?>"><i class="fa-solid fa-chart-area ms-3 me-1"></i><span>Employee Status</span></a>
                                    <?php } ?>
                                    <?php if ($roleId == 1 || in_array('employee-salary', $pageAccessList)) { ?>
                                        <a href="employee-salary.php" class="btn  list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'employee-salary' ? 'active' : '' ?>"><i class="fa-solid fa-chart-area ms-3 me-1"></i><span>Employee Salary</span></a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <a href="attendance.php" class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'attendance' || $page_name == 'ideal-attendance' || $page_name == 'my-attendance' || $page_name == 'attendance-regularisation') ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseAttandence" aria-expanded="false" aria-controls="collapseAttandence"><i class="fas fa-globe fa-fw me-1"></i><span>Attendance</span></a>
                        <div class="collapse  <?php echo ($page_name == 'attendance' || $page_name == 'ideal-attendance' || $page_name == 'my-attendance' || $page_name == 'attendance-regularisation') ? 'show' : '' ?>" id="collapseAttandence">
                            <div class="d-flex flex-column w-100 btn-group list-group collapse-btn">
                                <a href="my-attendance.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'my-attendance' ? 'active' : '' ?>"><i class="fa-solid fa-street-view me-1 ms-3"></i><span>My Attendance</span></a>
                                <?php if ($roleId == 1 || in_array('attendance', $pageAccessList)) { ?>
                                    <a href="attendance.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'attendance' ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-user me-1 ms-3"></i><span>View Attendance</span></a>
                                <?php } ?>
                                <?php if ($roleId == 1 || in_array('attendance-regularisation', $pageAccessList)) { ?>
                                    <a href="attendance-regularisation.php" href="" class="btn  list-group-item list-group-item-action  ripple menu-option-open <?php echo $page_name == 'attendance-regularisation' ? 'active' : '' ?>"><i class="fa-solid fa-star me-1 ms-3"></i><span>Regularization</span></a>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if ($roleId == 1 || in_array('holiday', $pageAccessList)) { ?>
                            <a href="holiday.php" class="list-group-item list-group-item-action py-2 ripple <?php echo $page_name == 'holiday' ? 'active' : '' ?>"><i class="fas fa-calendar fa-fw me-1"></i><span>Holiday</span></a>
                        <?php } ?>
                        <a href="attendance.php" class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'my-efficiency' || $page_name == 'total-efficiency' || $page_name == 'project-time' || $page_name == 'today-efficiency') ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseEfficiency" aria-expanded="false" aria-controls="collapseEfficiency"><i class="fas fa-clock fa-fw me-1"></i><span>Efficiency</span></a>
                        <div class="collapse  <?php echo ($page_name == 'my-efficiency' || $page_name == 'project-time' || $page_name == 'today-efficiency' || $page_name == 'total-efficiency') ? 'show' : '' ?>" id="collapseEfficiency">
                            <div class="d-flex flex-column w-100 btn-group list-group collapse-btn">
                                <a href="my-efficiency.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'my-efficiency' ? 'active' : '' ?>"><i class="fa-solid fa-gear me-1 ms-3"></i><span>My Efficiency</span></a>
                                <?php if ($roleId == 1 ||  in_array('today-efficiency', $pageAccessList)) { ?>
                                    <a href="today-efficiency.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'today-efficiency' ? 'active' : '' ?>"><i class="fa-solid fa-gears me-1 ms-3"></i><span>User Efficiency</span></a>
                                <?php } ?>
                                <?php if ($roleId == 1 ||  in_array('total-efficiency', $pageAccessList)) { ?>
                                    <a href="total-efficiency.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'total-efficiency' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high me-1 ms-3"></i><span>Total Efficiency</span></a>
                                <?php } ?>

                                <a href="project-time.php" class="list-group-item list-group-item-action ripple menu-option-open <?php echo $page_name == 'project-time' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high me-1 ms-3"></i><span>Other Task</span></a>
                            </div>
                        </div>
                        <?php if ($roleId == 1 || in_array('analysis', $pageAccessList)) { ?>
                            <a href="analysis.php" class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'analysis') ? 'active' : '' ?>"><i class="fa-solid fa-chart-simple me-1"></i><span>Analysis</span></a>
                        <?php } ?>
                        <?php if ($roleId == 1 || in_array('notification', $pageAccessList)) { ?>
                            <a href="notification.php" class="list-group-item list-group-item-action py-2 ripple <?php echo ($page_name == 'notification') ? 'active' : '' ?>"><i class="fas fa-message fa-fw me-1"></i><span>Notification</span></a>
                        <?php } ?>
                    </div>
                </div>

            </div>


        </nav>

        <!-- Navbar -->
        <nav id="main-navbar" class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
            <!-- Container wrapper -->
            <div class="container-fluid">
                <!-- Toggle button -->
                <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Brand -->
                <a class="navbar-brand" href="index.php">
                    <img src="images/EOM-Beand.png" height="25" alt="MDB Logo" loading="lazy" />
                </a>



                <ul class="navbar-nav ms-auto d-flex flex-row gap-4 align-items-center">
                    <?php
                    $count = 0;
                    $notifications = $conn->prepare("SELECT * FROM `notification` WHERE `type` = 'user' AND `type_id` = ?");
                    $notifications->execute([$_SESSION['userId']]);
                    $notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($notifications as $notification) {
                        $view = json_decode($notification['view'], true);
                        if ($view && is_array($view) && in_array($_SESSION['userId'], $view)) {
                            continue;
                        } else {
                            $count++;
                        }
                    }

                    $notifications = $conn->prepare("SELECT * FROM `notification` WHERE `type` = 'role' AND `type_id` = ?");
                    $notifications->execute([$_SESSION['roleId']]);
                    $notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($notifications as $notification) {
                        $view = json_decode($notification['view'], true);
                        if ($view && is_array($view) && in_array($_SESSION['userId'], $view)) {
                            continue;
                        } else {
                            $count++;
                        }
                    }

                    ?>


                    <p> <?php
                        if ($count > 0) {
                            echo 'You have <span style="color: blue;">' . $count . '</span> Notification. ';
                        }
                        ?> <span class="ms-2"><i class="fa-solid fa-bell btn" style="font-size: 26px;" onclick="seenNotifcation()" data-bs-toggle="modal" data-bs-target="#notificationModal"><sup>3</sup></i></span></p>




                    <!-- Avatar -->
                    <div class="dropdown float-end">
                        <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php
                            if (($_SESSION['userDetails']['profile'] == '') || !($_SESSION['userDetails']['profile'])) {
                                echo '
              
                <img src="images/users/default.jpg" alt="" class="birthday-img">';
                            } else {
                                echo ' 
              <img src="images/users/' . $_SESSION['userDetails']['profile'] . '" width="25px" class="birthday-img">';
                            } ?><span class="ms-1" style="font-size: 12px;"><?php echo $_SESSION['userDetails']['first_name'] . ' ' . $_SESSION['userDetails']['last_name'] ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-user me-2"></i>My profile</a></li>
                            <!-- <li><a class="dropdown-item" href="#">Settings</a></li> -->
                            <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                </ul>
            </div>
            </div>
            <!-- Container wrapper -->
        </nav>
        <!-- Navbar -->

        <!-- notification modal -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class=" d-flex justify-content-between">
                            <div> <span><i class="fa-regular fa-comment-dots fa-fade"></i><span class="ms-3">Massage</span></span></div>
                        </div>
                        <div class="notification-massage">
                            <?php
                            $notifications = $conn->prepare("SELECT * FROM `notification` ORDER BY `created_at` DESC");
                            $notifications->execute();
                            $notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($notifications as $value) {

                                $user = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
                                $user->execute([$value['user_id']]);
                                $user = $user->fetch(PDO::FETCH_ASSOC);

                                if ($value['type'] == 'role' && $value['type_id'] == $_SESSION['roleId']) {
                                    echo '<div class="message d-flex justify-content-between align-items-center">
                                        <div class="d-flex  align-items-center">
                                            <img class="birthday-img m-1" src="images/users/' . ($user['profile'] == '' ? 'default.jpg' : $user['profile']) . '" alt="" width="30px" height="30px">
                                            <p>' . $user['first_name'] . ' ' . $user['last_name'] . '</p>
                                        </div>
                                        <span>' . $value['message'] . '</span>
                                    </div>';
                                } else {
                                    if ($value['type_id'] == $_SESSION['userId']) {
                                        echo '<div class="message d-flex justify-content-between align-items-center">
                                            <div class="d-flex  align-items-center">
                                                <img class="birthday-img m-1" src="images/users/' . ($user['profile'] == '' ? 'default.jpg' : $user['profile']) . '" alt="" width="30px" height="30px">
                                                <p>' . $user['first_name'] . ' ' . $user['last_name'] . '</p>
                                            </div>
                                            <span>' . $value['message'] . '</span>
                                        </div>';
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>