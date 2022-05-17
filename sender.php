<?php

//Запускается каждую минуту
//Этот модуль можно прокачать до в целом работы с очередями писем. Для этого поле username поменять на email_text и добавить email_subject и email_type

require_once("mysql.php");
require_once("settings.php");
require_once("functions.php");

echo "Запуск рассылки" . PHP_EOL;

//Получаем пачку писем для отправки (По умолчанию 20 штук, 1200 писем в час, 288000 в сутки).
//Число выбрано таким образом, чтобы в целом отправка этой пачки со всеми проверками заняла не более нескольких минут.
//При "штатной" работе сервиса когда  большинство емейлов уже проверены заранее пачка в 20 писем вряд ли будет обрабатываться дольше 2-3 минут (но надо смотреть по факту)
//Если что, всегда можно увеличить число параллельных запусков
$emailsToSend = mysqli_query($conn, "SELECT emails_queue.*, emails.checked, emails.valid 
    FROM emails_queue LEFT JOIN emails ON emails.email=emails_queue.email 
    WHERE processing = 0 AND processed = 0 AND (retries_count=0 OR DATE_ADD(last_sendts, INTERVAL " . $settings['retry_after_hours'] . " HOUR) < NOW()) 
    LIMIT ".intval($settings['mails_per_launch']));

//Ставим пометку что письма в обработке (отправляются) дабы другой процесс не стал пытаться отправить их же повторно
$emailsString = "";
foreach ($emailsToSend as $emailToSend)
    $emailsString .= ($emailsString=="" ? "" : ",") . "'".mysqli_real_escape_string($conn, $emailToSend['email'])."'";

if ($emailsString == "") 
    die();

mysqli_query($conn, "UPDATE emails_queue SET processing=1, processingts='".mysqli_real_escape_string($conn, date("Y-m-d H:i:s"))."' WHERE email IN (".$emailsString.");");

foreach ($emailsToSend as $emailToSend)
{
    $canSendLetter = true;

    //Если нужна проверка на валидность емейла, запускаем её
    if ($emailToSend['need_check'] == 1) {
        
        if (!isset($emailToSend['valid'])) {
            $checkResult = check_email( $emailToSend['email'] );

            //В запись emails ставим что он проверен и проверять его в дальнейшем не нужно
            $sql = "INSERT INTO emails SET checked=1, valid=?, email=? ON DUPLICATE KEY UPDATE checked=1, valid=?";
            $stmt= $conn->prepare($sql);   
            $stmt->bind_param('dsd', $checkResult, $emailToSend['email'], $checkResult);
            $stmt->execute();

            $canSendLetter = $checkResult;

            echo "Проверили email ".$emailToSend['email']." с результатом " . $checkResult . PHP_EOL;
        } else 
            $canSendLetter = $emailToSend['valid'];
    }

    //Если емейл валидный, отправляем письмо
    if ($canSendLetter) {

        $text = $emailToSend['username'] . ", your subscription is expiring soon";
        $sendResult = send_email($emailToSend['email'], $settings['email_from'], $emailToSend['email'], $settings['email_subj'], $text);
        echo "Отправили письмо на ".$emailToSend['email']." с результатом " . $sendResult . PHP_EOL;
        
        if ($sendResult) {

            $sql = "UPDATE emails_queue SET processing=0, processed=1  WHERE email=?";
            $stmt= $conn->prepare($sql);   
            $stmt->bind_param('s', $emailToSend['email']);
            $stmt->execute();

        } else {

            //Если результат отправки неудачный, планируем ретрай отправки
            $retriesCount = intval($emailToSend['retries_count']) + 1;
            $lastSendTs = date("Y-m-d H:i:s");
            $processed = $retriesCount > $settings['retries_count'] ? 1 : 0;

            $sql = "UPDATE emails_queue SET processing=0, processed=?, retries_count=?, last_sendts=? WHERE email=?";
            $stmt= $conn->prepare($sql);   
            $stmt->bind_param('ddss', $processed, $retriesCount, $lastSendTs, $emailToSend['email']);
            $stmt->execute();

            //Если лимит попыток превышен можно отправить админу сообщение что на валидный емейл не доставить письмо
        }

    } else {

        // Емейл неверный - помечаем отправку как завершённую без отправки письма
        $sql = "UPDATE emails_queue SET processing=0, processed=1  WHERE email=?";
        $stmt= $conn->prepare($sql);   
        $stmt->bind_param('s', $emailToSend['email']);
        $stmt->execute();

    }
}