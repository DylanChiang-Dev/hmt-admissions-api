ALTER TABLE `questions` 
ADD COLUMN `answer_key_json` JSON DEFAULT NULL AFTER `options_json`,
ADD COLUMN `explanation` TEXT DEFAULT NULL AFTER `answer_key_json`,
ADD COLUMN `media_json` JSON DEFAULT NULL AFTER `knowledge_point_ids_json`,
ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
MODIFY COLUMN `difficulty` TINYINT NOT NULL DEFAULT 1;
