<?php
    ob_start();
    header("Access-Control-Allow-Origin: *");
    date_default_timezone_set("Asia/Kolkata");
    $configPath = __DIR__ . '/../config/config.php';
    require $configPath;

    echo "Beginning TXN...\n";
    $filename = __DIR__ . '/../logs/' . date('Y-m-d_H-i-s') . "_cron_log.txt";
    // $fileHandler = fopen($filename, 'a');

    try {
        $conn->beginTransaction();
        $currentDateTime = date('Y-m-d H:i:s');
        $users = $conn->prepare("SELECT ud.id, ud.leave_balance, us.first_name, us.last_name FROM `userdetails` AS ud LEFT JOIN `users` as us ON ud.user_id = us.id");
        $users->execute();
        $users = $users->fetchAll();
        // print_r($users);
        $userdata = [];
        foreach ($users as $user) {
            echo "Current User is " . $user['first_name'] . ' ' . $user['last_name'] . "...\n";
            $sql = $conn->prepare("UPDATE `userdetails` SET `leave_balance` = ? , `updated_at` = ? WHERE `id` = ?");
            $status = $sql->execute([$user['leave_balance'] + 1 , $currentDateTime, $user['id']]);

            $userd = new stdClass();
            $userd->id = $user['id'];
            $userd->name = $user['first_name'] . ' ' . $user['last_name'];
            $userd->previous_leave_balance = $user['leave_balance'];
            $userd->new_leave_balance = $user['leave_balance'] + 1;

            if (!$status) {
                throw new PDOException("Failed To Insert");
            }

            $userdata[] = $userd;
        }

        // insert log
        echo "Cron Completed. Executing Logs...\n";
        $insertLog = $conn->prepare("INSERT INTO `cron_status` SET `remarks` = ?, `data` = ?");
        $logStatus = $insertLog->execute([ "CRON Executed Successfully", json_encode($userdata) ]);
        if (!$logStatus) {
            throw new PDOException("Log Insertion Failed");
        }
        $conn->commit();
        echo "Logging Completed. Exiting...\n";
    } catch(PDOException $e) {

        echo "PDO Exception Occured... \n";
        echo $e;
        echo "\n";
        $conn->rollBack();
        $insertLog = $conn->prepare("INSERT INTO `cron_status` SET `remarks` = ?, `data` = ?");
        $insertLog->execute([ "CRON Execution Failed", json_encode($e) ]);

    } catch(Exception $e) {
        echo $e;
        echo "\n";
        echo "Unknown Exception Occured...\n";
    } finally {
        $output = ob_get_clean();
        file_put_contents($filename, $output);
        echo "Logs written in file $filename successfully. \n";
        

    }
    

?>