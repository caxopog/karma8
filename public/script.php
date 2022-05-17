<?php

//Здесь нужна проверка доступа чтобы абы кто не запускал скрипт (Basic auth или GET параметр с токеном)

$minute = intval(date("m"));

if ($minute == 0) {
    exec("php ../checker.php > /dev/null 2>/dev/null &");
    echo "Скрипт создания очереди запущен<br/>";
}

if ($minute == 30) {
    exec("php ../cleaner.php > /dev/null 2>/dev/null &");
    echo "Скрипт очистки запущен<br/>";
}

exec("php ../sender.php > /dev/null 2>/dev/null &");
echo "Скрипт отправки почты запущен<br/>";