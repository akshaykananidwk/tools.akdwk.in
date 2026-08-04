-- ================================================================
-- KRISHNA TOOLS — database schema.
-- The installer replaces {{PREFIX}} with your chosen table prefix
-- (default kt_) and then seeds categories, tools, plans, templates,
-- blog posts and the admin account. You normally do NOT run this by
-- hand — open /install/ instead. Kept here for reference / manual use.
-- ================================================================
SET NAMES utf8mb4;
SET foreign_key_checks = 0;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `status` ENUM('active','blocked','pending') NOT NULL DEFAULT 'active',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `phone_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `plan_id` INT UNSIGNED DEFAULT NULL,
  `plan_expiry` DATETIME DEFAULT NULL,
  `referral_code` VARCHAR(20) DEFAULT NULL,
  `referred_by` INT UNSIGNED DEFAULT NULL,
  `credits` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `last_ip` VARCHAR(45) DEFAULT NULL,
  INDEX(`plan_id`), INDEX(`referral_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}plans` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name_en` VARCHAR(60) NOT NULL,
  `name_gu` VARCHAR(60) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `duration_days` INT NOT NULL DEFAULT 0,
  `daily_limit` INT NOT NULL DEFAULT 0,
  `max_file_mb` INT NOT NULL DEFAULT 5,
  `features` TEXT,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}subscriptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `order_id` VARCHAR(80) DEFAULT NULL,
  `payment_id` VARCHAR(80) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  INDEX(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `razorpay_order_id` VARCHAR(80) DEFAULT NULL,
  `razorpay_payment_id` VARCHAR(80) DEFAULT NULL,
  `signature` VARCHAR(255) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `status` VARCHAR(20) NOT NULL DEFAULT 'created',
  `raw_response` TEXT,
  `created_at` DATETIME DEFAULT NULL,
  INDEX(`user_id`), INDEX(`razorpay_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name_en` VARCHAR(80) NOT NULL,
  `name_gu` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(80) NOT NULL UNIQUE,
  `icon` VARCHAR(40) DEFAULT NULL,
  `color` VARCHAR(20) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}tools` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name_en` VARCHAR(120) NOT NULL,
  `name_gu` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `icon` VARCHAR(40) DEFAULT NULL,
  `description_gu` TEXT,
  `description_en` TEXT,
  `is_premium` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  INDEX(`category_id`), INDEX(`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}tool_usage` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `tool_id` INT UNSIGNED NOT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  INDEX(`tool_id`), INDEX(`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}guest_limits` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip` VARCHAR(45) NOT NULL,
  `tool_id` INT UNSIGNED NOT NULL,
  `uses` INT NOT NULL DEFAULT 0,
  `day` DATE NOT NULL,
  UNIQUE KEY `ip_tool_day` (`ip`,`tool_id`,`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}favourites` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `tool_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT NULL,
  UNIQUE KEY `user_tool` (`user_id`,`tool_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}settings` (
  `k` VARCHAR(80) PRIMARY KEY,
  `v` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}contact_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `message` TEXT,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}whatsapp_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `number` VARCHAR(20) DEFAULT NULL,
  `type` VARCHAR(40) DEFAULT NULL,
  `message` TEXT,
  `media_url` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(20) DEFAULT NULL,
  `api_response` TEXT,
  `created_at` DATETIME DEFAULT NULL,
  INDEX(`number`), INDEX(`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}whatsapp_templates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(60) NOT NULL UNIQUE,
  `name_gu` VARCHAR(120) DEFAULT NULL,
  `body_gu` TEXT,
  `body_en` TEXT,
  `variables` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}whatsapp_sessions` (
  `number` VARCHAR(20) PRIMARY KEY,
  `state` VARCHAR(40) DEFAULT NULL,
  `data` TEXT,
  `updated_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}saved_files` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `tool_id` INT UNSIGNED DEFAULT NULL,
  `filename` VARCHAR(190) DEFAULT NULL,
  `path` VARCHAR(255) DEFAULT NULL,
  `size` INT UNSIGNED DEFAULT 0,
  `expires_at` DATETIME DEFAULT NULL,
  INDEX(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}blog_posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title_gu` VARCHAR(200) NOT NULL,
  `title_en` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `content_gu` MEDIUMTEXT,
  `content_en` MEDIUMTEXT,
  `image` VARCHAR(255) DEFAULT NULL,
  `meta_desc` VARCHAR(255) DEFAULT NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(60) DEFAULT NULL,
  `details` TEXT,
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  INDEX(`user_id`), INDEX(`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}webhook_bins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `token` VARCHAR(40) NOT NULL UNIQUE,
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}webhook_requests` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `bin_token` VARCHAR(40) NOT NULL,
  `method` VARCHAR(10) DEFAULT NULL,
  `headers` TEXT,
  `query_string` TEXT,
  `body` MEDIUMTEXT,
  `ip` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  INDEX(`bin_token`), INDEX(`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Centralized cron / scheduler ──
CREATE TABLE IF NOT EXISTS `{{PREFIX}}cron_jobs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `job_key` VARCHAR(60) NOT NULL UNIQUE,
  `name` VARCHAR(150) DEFAULT NULL,
  `schedule` VARCHAR(60) DEFAULT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `last_run` DATETIME DEFAULT NULL,
  `next_run` DATETIME DEFAULT NULL,
  `last_status` VARCHAR(20) DEFAULT NULL,
  `last_message` TEXT,
  `last_duration_ms` INT UNSIGNED DEFAULT 0,
  `run_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `fail_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `locked_at` DATETIME DEFAULT NULL,
  `lock_token` VARCHAR(40) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}cron_runs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `job_key` VARCHAR(60) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'running',
  `trigger_by` VARCHAR(20) DEFAULT 'cron',
  `message` TEXT,
  `output` MEDIUMTEXT,
  `started_at` DATETIME DEFAULT NULL,
  `finished_at` DATETIME DEFAULT NULL,
  `duration_ms` INT UNSIGNED DEFAULT 0,
  INDEX(`job_key`), INDEX(`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}job_queue` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `channel` VARCHAR(20) NOT NULL DEFAULT 'whatsapp',
  `recipient` VARCHAR(190) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `body` MEDIUMTEXT,
  `media_url` VARCHAR(255) DEFAULT NULL,
  `payload` TEXT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `run_after` DATETIME DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` INT UNSIGNED NOT NULL DEFAULT 3,
  `claimed_by` VARCHAR(40) DEFAULT NULL,
  `last_error` TEXT,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  INDEX(`status`), INDEX(`channel`), INDEX(`run_after`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET foreign_key_checks = 1;
-- Seed data (categories, tools, plans, templates, blog, admin, settings)
-- is inserted programmatically by the installer from the PHP registries so
-- it always stays in sync with /includes/tools_registry.php.
