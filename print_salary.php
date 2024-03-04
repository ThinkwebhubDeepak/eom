<?php
$page_name = 'employee-salary';
include 'includes/header.php';

$sql = $conn->prepare("SELECT * FROM `users` WHERE `is_terminated` = 0 AND `id` = ?");
$sql->execute([$_GET['user_id']]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

$sql = $conn->prepare("SELECT * FROM `role` WHERE `id` = ?");
$sql->execute([$user['role_id']]);
$role = $sql->fetch(PDO::FETCH_ASSOC);

$viewDetails = $conn->prepare("SELECT * FROM `userdetails` WHERE `user_id` = ?");
$viewDetails->execute([$user['id']]);
$viewDetails = $viewDetails->fetch(PDO::FETCH_ASSOC);

$current_month = date('m');
if (isset($_GET['month'])) {
    
    $current_month = $_GET['month'];
    if ($_GET['month'] == '01') {
        $start_date = date('Y', strtotime('-1 year')) . '-12-16';
    } else {
        if ($_GET['month'] <= 10) {
            $start_date = date('Y') . '-0' . ($_GET['month'] - 1) . '-16';
        } else {
            $start_date = date('Y') . '-' . ($_GET['month'] - 1) . '-16';
        }
    }
    $end_date = date('Y') . '-' . ($_GET['month']) . '-15';
} else {
    if (date('m') == 1) {
        $start_date = date('Y', strtotime('-1 year')) . '-12-16';
    } else {
        $start_date = date('Y') . '-' . date('m', strtotime('-1 month')) . '-16';
    }
    $end_date = date('Y') . '-' . date('m') . '-15';
}

$incentive_amount = 0;
$advance_salary = 0;
$incentive = $conn->prepare("SELECT * FROM `salary` WHERE MONTH(month) = ? AND `user_id` = ?");
$incentive->execute([$current_month , $user['id']]);
$incentive = $incentive->fetch(PDO::FETCH_ASSOC);
if($incentive){
  $temp = json_decode($incentive['incentive'] , true);
  foreach ($temp as $vvalue) {
    $incentive_amount += $vvalue['incentive'];
  }
  $temp = json_decode($incentive['advance_salary'] , true);
    foreach ($temp as $vvalue) {
        $advance_salary += $vvalue['advance'];
    }
}

function isSunday($dateString) {
    $timestamp = strtotime($dateString);
    $dayOfWeek = date('w', $timestamp);
    return $dayOfWeek == 0;
}

function isHoliday($dateString,$conn) {
    $holiday = $conn->prepare("SELECT * FROM `holiday` WHERE `date` = ?");
    $holiday->execute([$dateString]);
    $holiday = $holiday->fetch(PDO::FETCH_ASSOC);
    if($holiday){
        return 1;
    }
    return 0;
}


$start_datee = new DateTime($start_date);
$end_datee = new DateTime($end_date);
$interval = $start_datee->diff($end_datee);
$daysDifference = $interval->days;
$interval = new DateInterval('P1D');
$current_date = clone $start_datee;
$att = 0;
$holi = 0;
$sun = 0;
$leave = 0;
while ($current_date <= $end_datee) {

    $date = $current_date->format('Y-m-d');
    $current_date->format('Y-m-d') . PHP_EOL;
    $checkAtten = $conn->prepare("SELECT * FROM `attendance` WHERE `user_id` = ? AND `date` = ?");
    $checkAtten->execute([$user['id'] , $date]);
    $checkAtten = $checkAtten->fetch(PDO::FETCH_ASSOC);
    if($checkAtten){
        $att++;
    }else{
        if(isSunday($date)){
            $sun++;
        }else{
            if(isHoliday($date,$conn)){
                $holi++;
            }else{
                $levae = $conn->prepare("SELECT * FROM `leaves` WHERE `user_id` = ? AND form_date <= ? AND end_date >= ? AND `status` = 'approve'");
                $levae->execute([$user['id'], $date , $date ]);
                $levae = $levae->fetch(PDO::FETCH_ASSOC);
                if($levae){
                    if($levae['form_date'] == $date &&  $levae['formdate_session'] == 'Second Half'){
                        $leave += 0.5;
                    }else  if($levae['end_date'] == $date &&  $levae['enddate_session'] == 'First Half'){
                        $leave += 0.5;
                    }else{
                        $leave++;
                    }
                }
            }
        }
    }
    $current_date->add($interval);
}

$workingDay = $daysDifference - $leave - $sun - $holi;
$prDaySalary = $viewDetails['salary'] / $workingDay;
$salary = $prDaySalary * $att;
$food = 2400 / $workingDay;
$food = round($food * $att , 2);

$currentDate = new DateTime();
// if ($end_datee >= $currentDate) {
//     echo "<script>alert('This Month Slip Generate After 15th of month')</script>";
//     echo "<script>window.location.href = 'employee-salary.php'</script>";
//     exit();
// } 

?>

<style>
    .row {
        background: #ffffff;
    }

    .extraa {
        justify-content: space-around;
        padding: 12px;
    }

    .mainArea {
        width: 80%;
        font-size: 20px;
    }

    .dtt .lif,
    .dtt .rig {
        width: 50%;
        border: 1px solid;
    }

    .pymonth {
        padding: 10px;
    }

    @media print {
        #sidebarMenu,
        nav{
            display: none !important;
        }
        main{
            margin: 0 !important;
            padding: 0 !important;
        }
        .mainArea{
            width: 100%;
        }
        .container-flude{
            padding: 0 !important;
        }
    }

</style>

<main style="margin-top: 78px;">
    <div class="container-flude">

        <div class="container-flude p-5">
            <div class="row mt-1  justify-content-center py-5">

                <div class="section">
                    <div class="headBox d-flex extraa">
                        <div class="left">
                            <img src="images/EOM-Beand.png" width="100%" alt="">
                        </div>
                        <div class="righ">
                            <h1>Earth On Mapping Consulting</h1>
                            <p>B1/40, First Floor, Sewak Park <br> Dwarka Mod, New Delhi, India-110059 <br>
                                GST/UIN-07BKGPM2189F1Z1</p>
                        </div>
                    </div>
                </div>
                <h3 class="text-center">Salary Slip</h3>
                <div class="mainArea">
                    <div class="dtt d-flex">
                        <div class="pymonth lif">
                            Pay Slip For The Month :  </div>
                        <div class="pymonth rig"> <?php echo date('M, Y', strtotime($end_date)) ?> </div>
                    </div>
                    <h3 class="text-center">Employee Details</h3>
                    <div class="dtt d-flex">
                        <div class="lif">
                            <div class="pymonth">
                                Name : <?php echo $user['first_name'] . ' ' . $user['last_name'] ?>
                            </div>
                            <div class="pymonth">
                                Employee Id : <?php echo $user['employee_id'] ?>
                            </div>
                            <div class="pymonth">
                                Department : <?php echo $viewDetails['department'] ?>
                            </div>
                            <div class="pymonth">
                                Designation : <?php echo $viewDetails['designation'] ?>
                            </div>
                        </div>
                        <div class="rig">
                            <div class="pymonth">
                                Bank Name : <?php echo $viewDetails['bank_name'] ?>
                            </div>
                            <div class="pymonth">
                                Account No : <?php echo $viewDetails['account_name'] ?>
                            </div>
                            <div class="pymonth">
                                Ifsc No : <?php echo $viewDetails['ifsc_code'] ?>
                            </div>
                            <div class="pymonth">
                                Day Month : <?php echo $daysDifference; ?> Days
                            </div>
                        </div>
                    </div>
                    <h3 class="text-center">Leave Details</h3>
                    <div class="dtt d-flex">
                        <div class="pymonth lif">
                            Leave Balance : <?php echo $viewDetails['leave_balance'] ?> Days</div>
                        <div class="pymonth rig"> Leave :  <?php echo $leave ?> Days </div>
                    </div>
                    <h3 class="text-center">Salary Details</h3>
                    <div class="dtt d-flex">
                        <div class="lif">
                            <div class="pymonth">
                                Basic : Rs. <?php echo round($salary * 0.3 , 2) ?>
                            </div>
                            <div class="pymonth">
                                HRA : Rs. <?php echo round($salary * 0.15 , 2) ?>
                            </div>
                            <div class="pymonth">
                                Conveyance Allowance : Rs. <?php echo round($salary * 0.1 , 2) ?>
                            </div>
                            <div class="pymonth">
                                Food Allowance : Rs. <?php echo $food ?>
                            </div>
                        </div>
                        <div class="rig">
                            <div class="pymonth">
                                Other Allowance : Rs. <?php echo round(($salary * 0.45)  - $food , 2)?>
                            </div>
                            <div class="pymonth">
                                Incentive : Rs. <?php echo $incentive_amount ?>
                            </div>
                            <div class="pymonth">
                                Advance Salary : Rs. <?php echo $advance_salary ?>
                            </div>
                            <div class="pymonth">
                                Gross : Rs. <?php echo round($salary) ?>
                            </div>
                            <div class="pymonth">
                                Net Payable : RS. <?php echo round($salary) + $incentive_amount + $advance_salary ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p>This is System, Generate Payment Slip.</p>
            <button class="btn btn-primary" onclick="window.print()" style="margin: 0 40%">Print</button>
        </div>
    </div>
    </div>
</main>


<?php include 'includes/footer.php' ?>