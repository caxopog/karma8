<?php

require_once("settings.php");
require_once("functions.php");

$conn = new mysqli($settings['mysql_host'], $settings['mysql_user'], $settings['mysql_password']);
$conn->select_db($settings['mysql_db']);

if ($conn->connect_error) {
  //Отправить админу сообщение об ошибке
  die("Connection failed: " . $conn->connect_error);
}

return $conn;