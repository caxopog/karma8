<?php

require_once("mysql.php");
require_once("settings.php");

$users = $settings['seeder_seed_amount'];

mysqli_query($conn, "DELETE FROM users");
mysqli_query($conn, "DELETE FROM emails");
mysqli_query($conn, "DELETE FROM emails_queue");

for ($i = 1; $i <= $users; $i++)
{
    $name = uniqid();
    $email = $name . "@gmail.com";
    $validTs = date("Y-m-d H:i:s", time() + rand(3600, 3600*24*10));
    $confirmed = rand(0,1);

    $sql = "INSERT INTO users (username, email, validts, confirmed) VALUES ('".mysqli_real_escape_string($conn, $name)."', '".mysqli_real_escape_string($conn, $email)."', '".mysqli_real_escape_string($conn, $validTs)."', ".intval($confirmed).");";
    mysqli_query($conn, $sql);

    if ($i % 1000 == 0) echo "Создано:". $i ." ";
}

echo "ПОСЕЯНО";