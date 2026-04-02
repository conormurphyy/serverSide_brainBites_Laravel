-- BrainBites MySQL schema
-- Generated from Laravel migrations
-- Compatible with MySQL 8+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `likes`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `role` VARCHAR(255) NOT NULL DEFAULT 'reader',
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `posts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `summary` VARCHAR(280) NOT NULL,
  `body` LONGTEXT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 1,
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_is_public_published_at_index` (`is_public`, `published_at`),
  KEY `posts_user_id_foreign` (`user_id`),
  KEY `posts_category_id_foreign` (`category_id`),
  CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `likes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `post_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `likes_user_id_post_id_unique` (`user_id`, `post_id`),
  KEY `likes_user_id_foreign` (`user_id`),
  KEY `likes_post_id_foreign` (`post_id`),
  CONSTRAINT `likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `likes_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT,
  `cancelled_at` INT DEFAULT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo data
INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
  (1, 'Admin User', 'admin@brainbites.local', 'admin', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NOW(), NOW()),
  (2, 'Conor Reader', 'reader@brainbites.local', 'reader', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NOW(), NOW()),
  (3, 'Maya Writer', 'writer@brainbites.local', 'reader', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NOW(), NOW());

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
  (1, 'Technology', 'technology', 'AI, apps, tools, and coding questions.', NOW(), NOW()),
  (2, 'Science', 'science', 'Physics, biology, and space explained simply.', NOW(), NOW()),
  (3, 'Health', 'health', 'Fitness, nutrition, and mental wellness.', NOW(), NOW()),
  (4, 'Finance', 'finance', 'Saving, budgeting, investing, and money basics.', NOW(), NOW()),
  (5, 'Education', 'education', 'Learning methods and study strategies.', NOW(), NOW());

INSERT INTO `posts` (`id`, `user_id`, `category_id`, `title`, `slug`, `summary`, `body`, `image_path`, `is_public`, `published_at`, `created_at`, `updated_at`) VALUES
  (1, 3, 1, 'What is an API in simple words?', 'what-is-an-api-in-simple-words', 'A beginner-friendly explanation of APIs with everyday analogies.', 'An API is like a waiter between two systems. Your app asks for data, the API brings it from the server, and returns it in a format the app can use. You do not need to know the server internals to consume an API.', 'posts/api-explainer.jpg', 1, NOW(), NOW(), NOW()),
  (2, 3, 2, 'Why is the sky blue?', 'why-is-the-sky-blue', 'A quick breakdown of Rayleigh scattering and sunlight.', 'Sunlight contains many colors. Earth\'s atmosphere scatters shorter wavelengths like blue more than red, so blue light is spread across the sky and reaches your eyes from many directions.', 'posts/sky-blue.jpg', 1, NOW(), NOW(), NOW()),
  (3, 2, 3, 'How much water should you drink daily?', 'how-much-water-should-you-drink-daily', 'Practical hydration guidance without hype.', 'Hydration needs vary by body size, weather, activity, and diet. A simple rule is to drink consistently through the day and monitor thirst and urine color instead of chasing a fixed one-size-fits-all number.', 'posts/hydration.jpg', 1, NOW(), NOW(), NOW()),
  (4, 1, 4, 'Beginner budget plan: 50/30/20 explained', 'beginner-budget-plan-50-30-20-explained', 'A practical starter framework to control monthly spending.', 'The 50/30/20 method splits income into needs, wants, and savings or debt repayment. Start with realistic percentages, track for 30 days, then adjust categories based on your actual lifestyle.', 'posts/budget-50-30-20.jpg', 1, NOW(), NOW(), NOW()),
  (5, 3, 5, 'How to study faster with active recall', 'how-to-study-faster-with-active-recall', 'Use retrieval practice and spaced repetition to improve retention.', 'Active recall means testing yourself instead of rereading notes. Pair it with spaced repetition by revisiting content over increasing intervals to move knowledge into long-term memory.', 'posts/active-recall.jpg', 1, NOW(), NOW(), NOW()),
  (6, 2, 1, 'JavaScript closures made easy', 'javascript-closures-made-easy', 'Understand closures with one practical coding pattern.', 'A closure happens when a function remembers variables from its outer scope even after that outer function finishes running. This is useful for private state and factory-style helpers.', 'posts/js-closures.jpg', 1, NOW(), NOW(), NOW());

INSERT INTO `likes` (`id`, `user_id`, `post_id`, `created_at`, `updated_at`) VALUES
  (1, 1, 1, NOW(), NOW()),
  (2, 2, 1, NOW(), NOW()),
  (3, 1, 2, NOW(), NOW()),
  (4, 3, 2, NOW(), NOW()),
  (5, 2, 4, NOW(), NOW()),
  (6, 3, 5, NOW(), NOW()),
  (7, 1, 6, NOW(), NOW());

ALTER TABLE `users` AUTO_INCREMENT = 4;
ALTER TABLE `categories` AUTO_INCREMENT = 6;
ALTER TABLE `posts` AUTO_INCREMENT = 7;
ALTER TABLE `likes` AUTO_INCREMENT = 8;

SET FOREIGN_KEY_CHECKS = 1;
