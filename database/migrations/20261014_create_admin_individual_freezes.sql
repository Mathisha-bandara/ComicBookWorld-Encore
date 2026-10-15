-- Run only if the existing database does not yet have this table.
-- Do not import database.sql into a populated database as a reset operation.
USE `comic_book_world`;
CREATE TABLE IF NOT EXISTS `admin_individual_freezes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `is_frozen` tinyint(1) NOT NULL DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `frozen_by` int DEFAULT NULL,
  `frozen_at` datetime DEFAULT NULL,
  `unfrozen_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_admin_freeze_user` (`user_id`),
  KEY `idx_admin_freeze_status` (`is_frozen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
