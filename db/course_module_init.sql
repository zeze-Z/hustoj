-- HUSTOJ 课件商城模块 - 数据库初始化脚本
-- 创建日期: 2026-03-31

-- 1. 创建课程学科表
CREATE TABLE IF NOT EXISTS `course_subject` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `sort_order` INT DEFAULT 0,
  `status` TINYINT DEFAULT 1,
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程学科表';

-- 2. 创建课程表
CREATE TABLE IF NOT EXISTS `course` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `subject_id` INT NOT NULL,
  `tags` VARCHAR(255),
  `lesson_count` INT DEFAULT 0,
  `description` TEXT,
  `price` DECIMAL(10,2) DEFAULT 0,
  `status` TINYINT DEFAULT 1,
  `courseware_preview_url` VARCHAR(500),
  `lesson_plan_preview_url` VARCHAR(500),
  `courseware_link` VARCHAR(500),
  `courseware_code` VARCHAR(50),
  `lesson_plan_link` VARCHAR(500),
  `lesson_plan_code` VARCHAR(50),
  `link_expire_date` DATE,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`subject_id`) REFERENCES `course_subject`(`id`),
  KEY `idx_subject_status` (`subject_id`, `status`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程表';

-- 3. 创建订单表
CREATE TABLE IF NOT EXISTS `course_order` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` VARCHAR(100) NOT NULL,
  `course_id` INT NOT NULL,
  `amount` DECIMAL(10,2) DEFAULT 0,
  `email` VARCHAR(255),
  `pay_status` TINYINT DEFAULT 0,
  `pay_time` DATETIME,
  `pay_channel` VARCHAR(50),
  `trade_no` VARCHAR(100) DEFAULT NULL COMMENT '第三方支付流水号',
  `mail_status` TINYINT DEFAULT 0,
  `mail_sent_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `course`(`id`),
  UNIQUE KEY `uk_user_course` (`user_id`, `course_id`),
  KEY `idx_user_status` (`user_id`, `pay_status`),
  KEY `idx_course_status` (`course_id`, `pay_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程订单表';

-- 4. 初始化学科数据
INSERT IGNORE INTO `course_subject` (`name`, `sort_order`, `status`) VALUES
('人工智能通识', 1, 1),
('信息技术', 2, 1),
('美术', 3, 1),
('音乐', 4, 1);
