CREATE DATABASE IF NOT EXISTS `karma` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'karma'@'%' IDENTIFIED WITH mysql_native_password BY 'karma';
GRANT ALL ON *.* TO 'karma'@'%';

USE karma;


CREATE TABLE `users` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `validts` timestamp NULL DEFAULT NULL,
  `confirmed` tinyint NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
  ADD PRIMARY KEY (`email`),
  ADD KEY `users_validts_index` (`validts`),
  ADD KEY `users_confirmed_index` (`confirmed`);


CREATE TABLE `emails` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked` tinyint NULL DEFAULT 0,
  `valid` tinyint NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `emails`
  ADD PRIMARY KEY (`email`),
  ADD KEY `emails_checked_index` (`checked`),
  ADD KEY `emails_valid_index` (`valid`);


CREATE TABLE `emails_queue` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `need_check` tinyint NULL,
  `processing` tinyint NULL DEFAULT 0,
  `processed` tinyint NULL DEFAULT 0,
  `processingts` timestamp NULL DEFAULT NULL,
  `retries_count` tinyint NULL DEFAULT 0,
  `last_sendts` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `emails_queue`
  ADD PRIMARY KEY (`email`),
  ADD KEY `emails_queue_processing_index` (`processing`),
  ADD KEY `emails_queue_processed_index` (`processed`);