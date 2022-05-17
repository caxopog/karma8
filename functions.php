<?php

function check_email( string $email ): int
{
    echo "Проверяем емейл ".$email.PHP_EOL;
    sleep(rand(1,60));
    return rand(0,1);
}

function send_email( string $email, string $from, string $to, string $subj, string $body ): int
{
    echo "Отправляем письмо на ".$email.PHP_EOL;
    sleep(rand(1,10));
    return rand(0,1); // Имитация неуспешной отправки почты - половина писем отправляется неуспешно
}