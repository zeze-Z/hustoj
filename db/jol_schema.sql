/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: jol
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-5ubuntu0.1 from Ubuntu

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `balloon`
--

DROP TABLE IF EXISTS `balloon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `balloon` (
  `balloon_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` char(48) NOT NULL,
  `sid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`balloon_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `compileinfo`
--

DROP TABLE IF EXISTS `compileinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `compileinfo` (
  `solution_id` int(11) NOT NULL DEFAULT 0,
  `error` text DEFAULT NULL,
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contest`
--

DROP TABLE IF EXISTS `contest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contest` (
  `contest_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `defunct` char(1) NOT NULL DEFAULT 'N',
  `description` text DEFAULT NULL,
  `private` tinyint(4) NOT NULL DEFAULT 0,
  `langmask` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'bits for LANG to mask',
  `password` char(16) NOT NULL DEFAULT '',
  `contest_type` smallint(5) unsigned DEFAULT 0,
  `subnet` varchar(255) NOT NULL DEFAULT '',
  `user_id` varchar(48) NOT NULL DEFAULT 'admin',
  `school_id` int(11) DEFAULT NULL COMMENT '创建学校ID',
  `is_public` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否公开:0否,1是',
  PRIMARY KEY (`contest_id`),
  KEY `idx_school_id` (`school_id`),
  KEY `key_c_def` (`defunct`),
  KEY `key_c_end` (`end_time`),
  KEY `key_c_dend` (`defunct`,`end_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1011 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contest_problem`
--

DROP TABLE IF EXISTS `contest_problem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contest_problem` (
  `problem_id` int(11) NOT NULL DEFAULT 0,
  `contest_id` int(11) DEFAULT NULL,
  `title` char(200) NOT NULL DEFAULT '',
  `num` int(11) NOT NULL DEFAULT 0,
  `c_accepted` int(11) NOT NULL DEFAULT 0,
  `c_submit` int(11) NOT NULL DEFAULT 0,
  `score` int(11) DEFAULT 0 COMMENT '竞赛中该题分值',
  KEY `Index_contest_id` (`contest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `lesson_count` int(11) DEFAULT 0,
  `download_count` int(11) NOT NULL DEFAULT 0 COMMENT '课程下载次数',
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `preview_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '预览版价格',
  `source_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '原文件价格',
  `status` tinyint(4) DEFAULT 1,
  `courseware_preview_url` varchar(500) DEFAULT NULL,
  `courseware_full_preview_url` varchar(500) DEFAULT '' COMMENT '课件完整预览URL',
  `lesson_plan_preview_url` varchar(500) DEFAULT NULL,
  `lesson_plan_full_preview_url` varchar(500) DEFAULT '' COMMENT '教案完整预览URL',
  `courseware_link` varchar(500) DEFAULT NULL,
  `lesson_plan_link` varchar(500) DEFAULT NULL,
  `link_expire_date` date DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_subject_status` (`subject_id`,`status`),
  KEY `idx_sort` (`sort_order`),
  CONSTRAINT `course_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `course_subject` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='课程表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_order`
--

DROP TABLE IF EXISTS `course_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_no` varchar(50) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL,
  `license_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '权限类型：1=仅预览版 2=仅原文件 3=完整版(预览+原文件)',
  `amount` decimal(10,2) DEFAULT 0.00,
  `email` varchar(255) DEFAULT NULL,
  `pay_status` tinyint(4) DEFAULT 0,
  `pay_time` datetime DEFAULT NULL,
  `pay_channel` varchar(50) DEFAULT NULL,
  `trade_no` varchar(100) DEFAULT NULL COMMENT '第三方支付流水号',
  `mail_status` tinyint(4) DEFAULT 0,
  `counted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已计入下载次数：0=未计数 1=已计数',
  `referrer_id` varchar(48) DEFAULT NULL COMMENT '分享归因：分享者 user_id（7天cookie归因）',
  `share_reward` int(11) DEFAULT NULL COMMENT '分享返佣积分（NULL=未发放，>=0=已发放）',
  `mail_sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  UNIQUE KEY `uk_user_course_license` (`user_id`,`course_id`,`license_type`),
  KEY `idx_user_status` (`user_id`,`pay_status`),
  KEY `idx_course_status` (`course_id`,`pay_status`),
  KEY `idx_referrer` (`referrer_id`),
  CONSTRAINT `course_order_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='课程订单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_subject`
--

DROP TABLE IF EXISTS `course_subject`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_subject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='课程学科表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custominput`
--

DROP TABLE IF EXISTS `custominput`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custominput` (
  `solution_id` int(11) NOT NULL DEFAULT 0,
  `input_text` text DEFAULT NULL,
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `db_version`
--

DROP TABLE IF EXISTS `db_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `db_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `script_name` varchar(255) NOT NULL COMMENT '执行的SQL文件名',
  `execute_time` datetime NOT NULL COMMENT '执行时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `script_name` (`script_name`),
  UNIQUE KEY `uk_script_name` (`script_name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='数据库版本记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam`
--

DROP TABLE IF EXISTS `exam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '试卷标题',
  `description` text DEFAULT NULL COMMENT '试卷说明',
  `creator_id` char(48) NOT NULL DEFAULT '' COMMENT '创建者ID',
  `total_score` int(11) NOT NULL DEFAULT 100 COMMENT '总分',
  `duration_min` int(11) NOT NULL DEFAULT 60 COMMENT '时长（分钟）',
  `start_time` datetime NOT NULL DEFAULT '2099-01-01 00:00:00' COMMENT '开始时间',
  `end_time` datetime NOT NULL DEFAULT '2099-01-01 00:00:00' COMMENT '结束时间',
  `defunct` char(1) NOT NULL DEFAULT 'N' COMMENT 'Y删除/N正常',
  `school_id` int(11) DEFAULT NULL COMMENT '所属学校',
  `is_public` char(1) NOT NULL DEFAULT 'Y' COMMENT 'Y公开/N私有',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`exam_id`),
  KEY `idx_creator` (`creator_id`),
  KEY `idx_school` (`school_id`),
  KEY `idx_defunct` (`defunct`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_attend`
--

DROP TABLE IF EXISTS `exam_attend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_attend` (
  `ea_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL COMMENT '试卷ID',
  `user_id` char(48) NOT NULL COMMENT '用户ID',
  `start_time` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '开始时间',
  `submitted` char(1) NOT NULL DEFAULT 'N' COMMENT 'Y已交卷/N未交',
  `total_score` int(11) DEFAULT 0 COMMENT '最终总分',
  `score_calculated` tinyint(4) DEFAULT 0 COMMENT '0未计算/1完成/2计算中',
  PRIMARY KEY (`ea_id`),
  UNIQUE KEY `uk_exam_user` (`exam_id`,`user_id`),
  KEY `idx_exam` (`exam_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_problem`
--

DROP TABLE IF EXISTS `exam_problem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_problem` (
  `ep_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL COMMENT '试卷ID',
  `problem_id` int(11) NOT NULL COMMENT '题目ID',
  `score` int(11) NOT NULL DEFAULT 10 COMMENT '本题分值',
  `num` int(11) NOT NULL DEFAULT 0 COMMENT '题目序号',
  PRIMARY KEY (`ep_id`),
  KEY `idx_exam` (`exam_id`),
  KEY `idx_problem` (`problem_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_result`
--

DROP TABLE IF EXISTS `exam_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_result` (
  `er_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL COMMENT '试卷ID',
  `user_id` char(48) NOT NULL COMMENT '用户ID',
  `problem_id` int(11) NOT NULL COMMENT '题目ID',
  `user_answer` char(200) NOT NULL DEFAULT '' COMMENT '用户答案',
  `is_correct` char(1) NOT NULL DEFAULT 'N' COMMENT 'Y正确/N错误',
  `score` int(11) NOT NULL DEFAULT 0 COMMENT '本题得分',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`er_id`),
  UNIQUE KEY `uk_exam_user_problem` (`exam_id`,`user_id`,`problem_id`),
  KEY `idx_exam_user` (`exam_id`,`user_id`),
  KEY `idx_exam` (`exam_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loginlog`
--

DROP TABLE IF EXISTS `loginlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loginlog` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(48) NOT NULL DEFAULT '',
  `password` varchar(40) DEFAULT NULL,
  `ip` varchar(46) DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_log_index` (`user_id`,`time`),
  KEY `user_time_index` (`user_id`,`time`)
) ENGINE=MyISAM AUTO_INCREMENT=21743 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail`
--

DROP TABLE IF EXISTS `mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT,
  `to_user` varchar(48) NOT NULL DEFAULT '' COMMENT 'user_id',
  `from_user` varchar(48) NOT NULL DEFAULT '' COMMENT 'user_id',
  `title` varchar(200) NOT NULL DEFAULT '',
  `content` text DEFAULT NULL,
  `new_mail` tinyint(1) NOT NULL DEFAULT 1,
  `reply` tinyint(4) DEFAULT 0,
  `in_date` datetime DEFAULT NULL,
  `defunct` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`mail_id`),
  KEY `uid` (`to_user`)
) ENGINE=MyISAM AUTO_INCREMENT=1013 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(48) NOT NULL DEFAULT '' COMMENT 'user_id',
  `title` varchar(200) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `time` datetime NOT NULL DEFAULT '2016-05-13 19:24:00',
  `importance` tinyint(4) NOT NULL DEFAULT 0,
  `menu` int(11) NOT NULL DEFAULT 0,
  `defunct` char(1) NOT NULL DEFAULT 'N',
  `school_id` int(11) DEFAULT NULL COMMENT '创建学校ID',
  `is_public` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否公开:0否,1是',
  PRIMARY KEY (`news_id`),
  KEY `idx_school_id` (`school_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1014 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `online`
--

DROP TABLE IF EXISTS `online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `online` (
  `hash` varchar(32) NOT NULL,
  `ip` varchar(46) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `ua` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `refer` varchar(4096) DEFAULT NULL,
  `lastmove` int(11) NOT NULL,
  `firsttime` int(11) DEFAULT NULL,
  `uri` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`hash`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `openai_task_queue`
--

DROP TABLE IF EXISTS `openai_task_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `openai_task_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `user_id` varchar(40) NOT NULL DEFAULT '',
  `task_type` varchar(24) NOT NULL DEFAULT '',
  `solution_id` bigint(20) DEFAULT 0,
  `problem_id` bigint(20) NOT NULL DEFAULT 0,
  `request_body` mediumtext NOT NULL COMMENT '请求参数(JSON格式字符串)',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态：0:待处理, 1:处理中, 2:已完成, 3:失败',
  `response_body` mediumtext DEFAULT NULL COMMENT '返回结果',
  `error_message` text DEFAULT NULL COMMENT '错误信息',
  `create_date` datetime NOT NULL COMMENT '创建时间',
  `update_date` datetime NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_status_create` (`status`,`create_date`),
  KEY `idx_user_status` (`user_id`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='异步任务队列-MyISAM版';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_card`
--

DROP TABLE IF EXISTS `point_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `point_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(64) NOT NULL COMMENT '生成批次号',
  `card_no` varchar(64) NOT NULL COMMENT '卡号',
  `card_secret` varchar(64) NOT NULL COMMENT '卡密',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态：0未兑换 1已兑换 2已禁用',
  `redeem_user_id` varchar(48) DEFAULT NULL COMMENT '兑换用户ID',
  `redeem_time` datetime DEFAULT NULL COMMENT '兑换时间',
  `redeem_ip` varchar(45) DEFAULT NULL COMMENT '兑换IP',
  `create_time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_card_no` (`card_no`),
  KEY `idx_batch_no` (`batch_no`),
  KEY `idx_status` (`status`),
  KEY `idx_redeem_user_id` (`redeem_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='积分充值卡';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_goods`
--

DROP TABLE IF EXISTS `point_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `point_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `product_key` varchar(32) NOT NULL COMMENT '商品唯一标识（履约路由键，如 offline_game）',
  `title` varchar(100) NOT NULL COMMENT '商品名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '商品描述（前端横幅/弹窗展示）',
  `icon` varchar(16) NOT NULL DEFAULT '' COMMENT '图标（emoji，可为空）',
  `price` int(11) NOT NULL DEFAULT 0 COMMENT '兑换价格（积分）',
  `original_price` int(11) DEFAULT NULL COMMENT '划线价（积分，NULL=不展示划线价）',
  `download_url` varchar(500) NOT NULL DEFAULT '' COMMENT '下载链接',
  `validity_days` int(11) NOT NULL DEFAULT 365 COMMENT '有效期（天）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态：1=上架 0=下架',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序权重（小的在前）',
  `create_time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_key` (`product_key`),
  KEY `idx_status_sort` (`status`,`sort`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='积分商品表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_goods_order`
--

DROP TABLE IF EXISTS `point_goods_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `point_goods_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(48) NOT NULL COMMENT '兑换用户',
  `product_key` varchar(32) NOT NULL DEFAULT 'offline_game' COMMENT '商品标识（关联 point_goods.product_key）',
  `school_name` varchar(100) NOT NULL COMMENT '学校名称',
  `room_name` varchar(100) NOT NULL COMMENT '机房名称',
  `license_code` text NOT NULL COMMENT 'JSON授权码（含签名）',
  `expire_date` date NOT NULL COMMENT '授权有效期',
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `point_amount` int(11) NOT NULL DEFAULT 50 COMMENT '消耗积分',
  `create_time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_order` (`user_id`,`order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expire_date` (`expire_date`),
  KEY `idx_product_key` (`product_key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='离线游戏兑换订单';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `point_log`
--

DROP TABLE IF EXISTS `point_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `point_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(48) NOT NULL COMMENT '用户ID',
  `change_point` int(11) NOT NULL COMMENT '积分变化，正数收入，负数支出',
  `balance` int(11) NOT NULL COMMENT '交易后余额',
  `type` tinyint(4) NOT NULL COMMENT '类型：1充值卡兑换 2课件购买 3管理员调整 4系统操作',
  `relation_id` varchar(64) DEFAULT NULL COMMENT '关联业务ID，如卡号、课件订单号',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_user_time` (`user_id`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='积分流水';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `printer`
--

DROP TABLE IF EXISTS `printer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `printer` (
  `printer_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` char(48) NOT NULL,
  `in_date` datetime NOT NULL DEFAULT '2018-03-13 19:38:00',
  `status` smallint(6) NOT NULL DEFAULT 0,
  `worktime` timestamp NULL DEFAULT current_timestamp(),
  `printer` char(16) NOT NULL DEFAULT 'LOCAL',
  `content` text NOT NULL,
  PRIMARY KEY (`printer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `privilege`
--

DROP TABLE IF EXISTS `privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `privilege` (
  `user_id` char(48) NOT NULL DEFAULT '',
  `rightstr` char(30) NOT NULL DEFAULT '',
  `valuestr` char(11) NOT NULL DEFAULT 'true',
  `defunct` char(1) NOT NULL DEFAULT 'N',
  KEY `user_id_index` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `problem`
--

DROP TABLE IF EXISTS `problem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `problem` (
  `problem_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `problem_type` enum('programming','choice_single','choice_multi','judge') NOT NULL DEFAULT 'programming' COMMENT '题目类型',
  `description` mediumtext NOT NULL,
  `input` mediumtext NOT NULL,
  `output` mediumtext NOT NULL,
  `sample_input` text DEFAULT NULL,
  `sample_output` text DEFAULT NULL,
  `spj` char(1) NOT NULL DEFAULT '0',
  `hint` mediumtext NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `contest_source` varchar(100) DEFAULT NULL COMMENT '竞赛来源（蓝桥杯/CSP等）',
  `answer` varchar(500) DEFAULT NULL COMMENT '正确答案（选择题）/ OJ配置（编程题）',
  `analysis` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '答案解析',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '选择题/判断题选项，JSON格式',
  `score` int(11) NOT NULL DEFAULT 0 COMMENT '题目分值',
  `school_id` int(11) DEFAULT NULL COMMENT '创建学校ID',
  `is_public` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否公开:0否,1是',
  `in_date` datetime DEFAULT NULL,
  `time_limit` decimal(10,3) NOT NULL DEFAULT 0.000,
  `memory_limit` int(11) NOT NULL DEFAULT 0,
  `defunct` char(1) NOT NULL DEFAULT 'N',
  `level` int(11) NOT NULL DEFAULT 1 COMMENT '难度等级(1-5)',
  `accepted` int(11) DEFAULT 0,
  `submit` int(11) DEFAULT 0,
  `solved` int(11) DEFAULT 0,
  `remote_oj` varchar(16) DEFAULT NULL,
  `remote_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`problem_id`),
  KEY `idx_school_id` (`school_id`),
  KEY `key_p_def` (`defunct`)
) ENGINE=MyISAM AUTO_INCREMENT=1799 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reply`
--

DROP TABLE IF EXISTS `reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reply` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` varchar(48) NOT NULL DEFAULT '' COMMENT 'user_id',
  `time` datetime NOT NULL DEFAULT '2016-05-13 19:24:00',
  `content` text NOT NULL,
  `topic_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `ip` varchar(46) NOT NULL,
  PRIMARY KEY (`rid`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `runtimeinfo`
--

DROP TABLE IF EXISTS `runtimeinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `runtimeinfo` (
  `solution_id` int(11) NOT NULL DEFAULT 0,
  `error` text DEFAULT NULL,
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `school`
--

DROP TABLE IF EXISTS `school`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `school` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '学校名称',
  `code` varchar(50) NOT NULL COMMENT '学校代码',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1启用',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_code`
--

DROP TABLE IF EXISTS `share_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `share_code` (
  `share_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_time` datetime DEFAULT NULL,
  PRIMARY KEY (`share_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sim`
--

DROP TABLE IF EXISTS `sim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sim` (
  `s_id` int(11) NOT NULL,
  `sim_s_id` int(11) DEFAULT NULL,
  `sim` int(11) DEFAULT NULL,
  PRIMARY KEY (`s_id`),
  KEY `Index_sim_id` (`sim_s_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `simfilter` BEFORE INSERT ON `sim` FOR EACH ROW BEGIN
 declare new_user_id varchar(64);
 declare old_user_id varchar(64);
 select user_id from solution where solution_id=new.s_id into new_user_id;
 select user_id from solution where solution_id=new.sim_s_id into old_user_id;
 if old_user_id=new_user_id then
set new.s_id=0;
 end if;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `solution`
--

DROP TABLE IF EXISTS `solution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `solution` (
  `solution_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL DEFAULT 0,
  `user_id` char(48) NOT NULL,
  `nick` char(20) NOT NULL DEFAULT '',
  `time` int(11) NOT NULL DEFAULT 0,
  `memory` int(11) NOT NULL DEFAULT 0,
  `in_date` datetime NOT NULL DEFAULT '2009-06-13 19:00:00',
  `result` smallint(6) NOT NULL DEFAULT 0,
  `language` int(10) unsigned NOT NULL DEFAULT 0,
  `ip` char(46) NOT NULL DEFAULT '',
  `contest_id` int(11) DEFAULT 0,
  `valid` tinyint(4) NOT NULL DEFAULT 1,
  `num` tinyint(4) NOT NULL DEFAULT -1,
  `code_length` int(11) NOT NULL DEFAULT 0,
  `judgetime` timestamp NULL DEFAULT current_timestamp(),
  `pass_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '通过率百分比',
  `first_time` tinyint(1) DEFAULT 0,
  `lint_error` int(10) unsigned NOT NULL DEFAULT 0,
  `judger` char(16) NOT NULL DEFAULT 'LOCAL',
  `remote_oj` char(16) NOT NULL DEFAULT '',
  `remote_id` char(16) NOT NULL DEFAULT '',
  `exam_id` int(11) DEFAULT 0 COMMENT '所属考试ID，0为非考试提交',
  PRIMARY KEY (`solution_id`),
  KEY `uid` (`user_id`),
  KEY `pid` (`problem_id`),
  KEY `res` (`result`),
  KEY `cid` (`contest_id`),
  KEY `fst` (`first_time`),
  KEY `idx_solution_in_date` (`in_date`)
) ENGINE=MyISAM AUTO_INCREMENT=19049 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci ROW_FORMAT=FIXED;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`debian-sys-maint`@`localhost`*/ /*!50003 TRIGGER `firstAC` BEFORE UPDATE ON `solution` FOR EACH ROW begin
 declare acTimes int;
 if new.result=4 then
    select count(1) from solution where problem_id=new.problem_id and result=4 and user_id=new.user_id and first_time=1 into acTimes;
    if acTimes=0 then
        set new.first_time=1;
    end if;
end if;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `solution_ai_answer`
--

DROP TABLE IF EXISTS `solution_ai_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `solution_ai_answer` (
  `solution_id` int(11) NOT NULL DEFAULT 0,
  `answer` mediumtext DEFAULT NULL,
  PRIMARY KEY (`solution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `source_code`
--

DROP TABLE IF EXISTS `source_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `source_code` (
  `solution_id` int(11) NOT NULL,
  `source` text NOT NULL,
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `source_code_user`
--

DROP TABLE IF EXISTS `source_code_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `source_code_user` (
  `solution_id` int(11) NOT NULL,
  `source` text NOT NULL,
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teacher_promo_stat`
--

DROP TABLE IF EXISTS `teacher_promo_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_promo_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` varchar(48) NOT NULL COMMENT '教师user_id',
  `week_start` date NOT NULL COMMENT '结算周起始日(周一日期)',
  `week_no` tinyint(4) NOT NULL COMMENT '周期内周次 1-4',
  `bound_count` int(11) NOT NULL DEFAULT 0 COMMENT '当周关联学生总数',
  `active_count` int(11) NOT NULL DEFAULT 0 COMMENT '当周登录过的学生数（accesstime落在时间窗内）',
  `threshold` int(11) NOT NULL DEFAULT 0 COMMENT '达标阈值=floor(bound_count*0.6)',
  `is_qualified` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否达标 0/1',
  `is_granted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已发放 0/1',
  `grant_point` int(11) NOT NULL DEFAULT 0 COMMENT '发放积分数',
  `settle_time` datetime DEFAULT NULL COMMENT '发分结算时间',
  `gen_time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '快照生成时间',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '周期状态：0进行中，1已完成(满4周)，2已中断(某周未达标)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_teacher_week` (`teacher_id`,`week_start`),
  KEY `idx_week_start` (`week_start`),
  KEY `idx_teacher_status` (`teacher_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci COMMENT='教师推广周结算状态记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `topic`
--

DROP TABLE IF EXISTS `topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `topic` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `title` varbinary(60) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `top_level` int(11) NOT NULL DEFAULT 0,
  `cid` int(11) DEFAULT NULL,
  `pid` int(11) NOT NULL,
  `author_id` varchar(48) NOT NULL DEFAULT '' COMMENT 'user_id',
  PRIMARY KEY (`tid`),
  KEY `cid` (`cid`,`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` varchar(48) NOT NULL DEFAULT '' COMMENT 'user_id',
  `email` varchar(100) DEFAULT NULL,
  `submit` int(11) DEFAULT 0,
  `solved` int(11) DEFAULT 0,
  `defunct` char(1) NOT NULL DEFAULT 'N',
  `ip` varchar(46) NOT NULL DEFAULT '',
  `register_fingerprint` varchar(64) DEFAULT NULL COMMENT '注册设备指纹（UA+分辨率+语言+时区+Canvas哈希）',
  `reg_ip` varchar(64) DEFAULT NULL COMMENT '注册时真实IP（与登录IP字段`ip`解耦）',
  `accesstime` datetime DEFAULT NULL,
  `volume` int(11) NOT NULL DEFAULT 1,
  `language` int(11) NOT NULL DEFAULT 1,
  `password` varchar(32) DEFAULT NULL,
  `reg_time` datetime DEFAULT NULL,
  `expiry_date` date NOT NULL DEFAULT '2099-01-01',
  `nick` varchar(20) NOT NULL DEFAULT '',
  `school` varchar(100) NOT NULL DEFAULT '' COMMENT '学校名称(冗余)',
  `school_id` int(11) DEFAULT NULL COMMENT '所属学校ID',
  `role` varchar(20) NOT NULL DEFAULT 'student' COMMENT '用户角色：teacher/student',
  `bind_teacher_id` varchar(48) DEFAULT NULL COMMENT '学生归属教师user_id（教师推广关联）',
  `group_name` varchar(16) NOT NULL DEFAULT '',
  `activecode` varchar(32) DEFAULT NULL,
  `starred` int(11) NOT NULL DEFAULT 0,
  `point` int(11) NOT NULL DEFAULT 0 COMMENT '平台积分余额',
  `point_recharged` int(11) NOT NULL DEFAULT 0 COMMENT '充值积分余额（存量余额视为赠送，充值后累计；不参与分享返佣基数的赠送部分）',
  `new_user_reward_claimed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '新用户奖励是否已领取：0-未领取，1-已领取',
  `login_streak` int(11) NOT NULL DEFAULT 0 COMMENT '连续登录天数（0=尚未开始签到）',
  `last_login_reward_date` date DEFAULT NULL COMMENT '最后领取登录奖励日期（领6分当天即播种为当天，兼做每日幂等键）',
  `login_reward_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '签到状态：0-进行中/未开始，1-已完成(满7天)，2-已断签结束',
  PRIMARY KEY (`user_id`),
  KEY `idx_school_id` (`school_id`),
  KEY `idx_register_fingerprint_reg_time` (`register_fingerprint`,`reg_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-10-23 22:11:15
