<?php

//Запускается раз в час

require_once("mysql.php");
require_once("settings.php");

//Удаляем обработанные записи старше суток
mysqli_query($conn, "DELETE FROM emails_queue WHERE processed=1 AND DATE_ADD(processingts, INTERVAL " . intval($settings['clean_hours']) . " HOUR) < NOW()");

//Ставим в повторную обработку записи, которые не завершились (скорей всего что то не сработало)
//Сюда можно добавить проверку что если такие записи есть, то послать письмо админу дабы разобрался из за чего обработка обрывается раньше времени.
mysqli_query($conn, "UPDATE emails_queue SET processing=0 WHERE processing=1 AND processed=0 AND DATE_ADD(processingts, INTERVAL " . intval($settings['relaunch_hours']) . " HOUR) < NOW()");

