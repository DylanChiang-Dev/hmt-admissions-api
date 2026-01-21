CREATE TABLE IF NOT EXISTS `users` (
    `id` VARCHAR(36) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `questions` (
    `id` VARCHAR(36) NOT NULL,
    `exam_path` VARCHAR(255) NOT NULL,
    `track` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `question_type` VARCHAR(50) NOT NULL,
    `stem` TEXT NOT NULL,
    `options_json` JSON DEFAULT NULL,
    `difficulty` INT NOT NULL DEFAULT 1,
    `tags_json` JSON DEFAULT NULL,
    `knowledge_point_ids_json` JSON DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attempts` (
    `id` VARCHAR(36) NOT NULL,
    `user_id` VARCHAR(36) NOT NULL,
    `question_id` VARCHAR(36) NOT NULL,
    `answer_json` JSON NOT NULL,
    `correct` TINYINT(1) NOT NULL,
    `elapsed_ms` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_question` (`user_id`, `question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_progress` (
    `user_id` VARCHAR(36) NOT NULL,
    `streak_current` INT DEFAULT 0,
    `streak_best` INT DEFAULT 0,
    `daily_goal` INT DEFAULT 10,
    `daily_done_count` INT DEFAULT 0,
    `subject_mastery_json` JSON DEFAULT NULL,
    `last_activity_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `review_queue` (
    `user_id` VARCHAR(36) NOT NULL,
    `question_id` VARCHAR(36) NOT NULL,
    `due_at` DATETIME NOT NULL,
    `interval_days` FLOAT NOT NULL,
    `ease_factor` FLOAT NOT NULL,
    PRIMARY KEY (`user_id`, `question_id`),
    KEY `idx_due_at` (`due_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Panel 用户表
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` VARCHAR(36) NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `last_login_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
