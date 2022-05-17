Деплой:
1. Перейти в папку docker
1. docker-compose build для билда образа приложения
2. docker-compose up для создания и запуска контейнера
3. docker exec karma php seeder.php для засеивания базы пользователей и емейлов.
   По умолчанию удалит все текущие данные и создаст 100 тыс новых юзеров и емейлов, число юзеров можно поменять в settings.php

Настройки находятся в settings.php
Для выполнения скриптов достаточно вызывать по http скрипт public/script.php с частотой 1 раз в минуту

Для тестирования можно запускать скрипты отдельно:
docker exec karma php checker.php - для генерации списка писем для отсылки
docker exec karma php sender.php - для рассылки писем
docker exec karma php cleaner.php - для очистки и рестарта "зависших" отправок