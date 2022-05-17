<?php

// Запускается каждый час

require_once("mysql.php");
require_once("settings.php");

$checkTime = time();

$lastLaunchTimestamp = @file_get_contents("last_launch_timestamp.txt"); // Вместо файла можно использовать Redis или БД.

//Если скрипт долго не работал, то отрабатываем скрипт за последние 2 дня
if (!$lastLaunchTimestamp) $lastLaunchTimestamp = time() - 3600*24*2; 
$lastLaunchTimestamp = max($lastLaunchTimestamp, time() - 3600*24*2);

$periodStart = $lastLaunchTimestamp + 3600 * 24 * $settings["remind_days"];
$periodEnd = $checkTime + 3600 * 24 * $settings["remind_days"];

echo "Запуск формирования списков рассылки для юзеров с окончанием оплаты с " . date("d.m.Y H:i:s", $periodStart) . " до " . date("d.m.Y H:i:s", $periodEnd) . PHP_EOL;

$pack = 0;
while (true) { //Юзеров получаем сетами чтобы не пегеружать память

    $users = mysqli_query($conn, "SELECT users.username, users.email, users.confirmed, emails.checked, emails.valid
        FROM users
        LEFT JOIN emails ON emails.email = users.email 
        WHERE 
        validts >= '".mysqli_real_escape_string($conn, date("Y-m-d H:i:s", $periodStart))."'
        AND validts <= '".mysqli_real_escape_string($conn, date("Y-m-d H:i:s", $periodEnd))."'
        AND (users.confirmed=1 OR emails.checked=0 OR emails.valid=1 OR emails.checked IS NULL) LIMIT " . intval($pack * $settings['users_pack_size']) ."," . intval($settings['users_pack_size']));

    $usersPlaced = 0;
    foreach ($users as $user)
    {
        $needCheck = 1;
        if ($user['confirmed'] == 1 || (isset($user['valid']) && $user['valid'] == 1)) {
            $needCheck = 0;
        }

        $sql = "INSERT IGNORE INTO emails_queue (email, username, need_check) VALUES (?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssd', $user['email'], $user['username'], $needCheck);
        $stmt->execute();

        $usersPlaced++;
    }

    $pack++;
    echo "Пачка " . $pack . "; В очередь помещено " . $usersPlaced . " юзеров" . PHP_EOL;

    if ($usersPlaced < $settings['users_pack_size']) 
        break;
}

//Ставим дату последней обработки
file_put_contents("last_launch_timestamp.txt", $checkTime);