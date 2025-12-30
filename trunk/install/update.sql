update contest set start_time='2000-01-01 00:00:00' where start_time<'1000-01-01 00:00:00';
update contest set end_time='2099-01-01 00:00:00' where end_time<'1000-01-01 00:00:00';

-- 使用存储过程来安全地执行表结构修改
DELIMITER //

-- 添加字段的通用函数
DROP PROCEDURE IF EXISTS add_column_if_not_exists //
CREATE PROCEDURE add_column_if_not_exists(
    IN table_name VARCHAR(64),
    IN column_name VARCHAR(64),
    IN column_definition VARCHAR(255),
    IN after_column VARCHAR(64)
)
BEGIN
    DECLARE column_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO column_count
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
    AND table_name = table_name 
    AND column_name = column_name;
    
    IF column_count = 0 THEN
        IF after_column != '' THEN
            SET @sql = CONCAT('ALTER TABLE `', table_name, '` ADD COLUMN `', column_name, '` ', column_definition, ' AFTER `', after_column, '`');
        ELSE
            SET @sql = CONCAT('ALTER TABLE `', table_name, '` ADD COLUMN `', column_name, '` ', column_definition);
        END IF;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

-- 修改字段的通用函数
DROP PROCEDURE IF EXISTS modify_column_if_exists //
CREATE PROCEDURE modify_column_if_exists(
    IN table_name VARCHAR(64),
    IN column_name VARCHAR(64),
    IN column_definition VARCHAR(255)
)
BEGIN
    DECLARE column_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO column_count
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
    AND table_name = table_name 
    AND column_name = column_name;
    
    IF column_count > 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', table_name, '` MODIFY COLUMN `', column_name, '` ', column_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

-- 更改字段的通用函数
DROP PROCEDURE IF EXISTS change_column_if_exists //
CREATE PROCEDURE change_column_if_exists(
    IN table_name VARCHAR(64),
    IN old_column_name VARCHAR(64),
    IN new_column_name VARCHAR(64),
    IN column_definition VARCHAR(255)
)
BEGIN
    DECLARE column_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO column_count
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
    AND table_name = table_name 
    AND column_name = old_column_name;
    
    IF column_count > 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', table_name, '` CHANGE COLUMN `', old_column_name, '` `', new_column_name, '` ', column_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

-- 添加索引的通用函数
DROP PROCEDURE IF EXISTS add_index_if_not_exists //
CREATE PROCEDURE add_index_if_not_exists(
    IN table_name VARCHAR(64),
    IN index_name VARCHAR(64),
    IN index_definition VARCHAR(255)
)
BEGIN
    DECLARE index_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO index_count
    FROM information_schema.statistics 
    WHERE table_schema = DATABASE() 
    AND table_name = table_name 
    AND index_name = index_name;
    
    IF index_count = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', table_name, '` ADD INDEX `', index_name, '` (', index_definition, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

-- 添加主键的通用函数
DROP PROCEDURE IF EXISTS add_primary_key_if_not_exists //
CREATE PROCEDURE add_primary_key_if_not_exists(
    IN table_name VARCHAR(64),
    IN primary_key_definition VARCHAR(255)
)
BEGIN
    DECLARE pk_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO pk_count
    FROM information_schema.table_constraints 
    WHERE table_schema = DATABASE() 
    AND table_name = table_name 
    AND constraint_type = 'PRIMARY KEY';
    
    IF pk_count = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', table_name, '` ADD PRIMARY KEY (', primary_key_definition, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

DELIMITER ;

-- 执行安全的表结构修改

-- 添加contest.user_id字段
CALL add_column_if_not_exists('contest', 'user_id', 'CHAR(48) NOT NULL DEFAULT ''admin''', 'password');
update contest c inner JOIN (SELECT * FROM privilege WHERE rightstr LIKE 'm%') p ON concat('m',contest_id)=rightstr set c.user_id=p.user_id;

-- 添加contest_problem.c_accepted和c_submit字段
CALL add_column_if_not_exists('contest_problem', 'c_accepted', 'INT NOT NULL DEFAULT ''0''', 'num');
CALL add_column_if_not_exists('contest_problem', 'c_submit', 'INT NOT NULL DEFAULT ''0''', 'c_accepted');
update contest_problem cp inner join (select count(1) submit,contest_id cid,num from solution where contest_id>0 group by contest_id,num) sb on cp.contest_id=sb.cid and cp.num=sb.num set cp.c_submit=sb.submit;
update contest_problem cp inner join (select count(1) ac,contest_id cid,num from solution where contest_id>0 and result=4 group by contest_id,num) sb on cp.contest_id=sb.cid and cp.num=sb.num set cp.c_accepted =sb.ac;

-- 添加solution.nick字段
CALL add_column_if_not_exists('solution', 'nick', 'char(20) not null default ''''', 'user_id');
update solution s inner join users u on s.user_id=u.user_id set s.nick=u.nick;

-- 添加privilege.user_id_index索引
CALL add_index_if_not_exists('privilege', 'user_id_index', 'user_id');

-- 修改problem.time_limit字段
CALL change_column_if_exists('problem', 'time_limit', 'time_limit', 'DECIMAL(10,3) NOT NULL DEFAULT ''0''');

-- 添加privilege.valuestr字段
CALL add_column_if_not_exists('privilege', 'valuestr', 'char(11) not null default ''true''', 'rightstr');

-- 修改news.time字段
CALL modify_column_if_exists('news', 'time', 'datetime NOT NULL DEFAULT ''2016-05-13 19:24:00''');

-- 添加news.menu字段
CALL add_column_if_not_exists('news', 'menu', 'int(11) NOT NULL DEFAULT 0', 'importance');

-- 修改solution.pass_rate字段
CALL modify_column_if_exists('solution', 'pass_rate', 'decimal(4,3) not null default 0.0');

-- 添加problem.remote_oj和remote_id字段
CALL add_column_if_not_exists('problem', 'remote_oj', 'varchar(16) default NULL', 'solved');
CALL add_column_if_not_exists('problem', 'remote_id', 'varchar(32) default NULL', 'remote_oj');

-- 添加solution.remote_oj和remote_id字段
CALL add_column_if_not_exists('solution', 'remote_oj', 'char(16) not null default ''''', 'judger');
CALL add_column_if_not_exists('solution', 'remote_id', 'char(32) not null default ''''', 'remote_oj');

-- 修改news.content字段
CALL modify_column_if_exists('news', 'content', 'mediumtext not null');

-- 修改problem的多个字段
CALL modify_column_if_exists('problem', 'description', 'mediumtext not null');
CALL modify_column_if_exists('problem', 'input', 'mediumtext not null');
CALL modify_column_if_exists('problem', 'output', 'mediumtext not null');
CALL modify_column_if_exists('problem', 'hint', 'mediumtext not null');

-- 添加users.activecode和group_name字段
CALL add_column_if_not_exists('users', 'activecode', 'varchar(16) not null default ''''', 'school');
CALL add_column_if_not_exists('users', 'group_name', 'varchar(16) not null default ''''', 'school');

-- 添加loginlog.log_id主键
CALL add_primary_key_if_not_exists('loginlog', 'log_id INT NOT NULL AUTO_INCREMENT FIRST');

-- 添加problem.key_p_def索引
CALL add_index_if_not_exists('problem', 'key_p_def', 'defunct');

-- 添加contest的多个索引
CALL add_index_if_not_exists('contest', 'key_c_def', 'defunct');
CALL add_index_if_not_exists('contest', 'key_c_end', 'end_time');
CALL add_index_if_not_exists('contest', 'key_c_dend', 'defunct, end_time');

-- 添加users.starred和expiry_date字段
CALL add_column_if_not_exists('users', 'starred', 'int default 0', 'activecode');
CALL add_column_if_not_exists('users', 'expiry_date', 'date not null default ''2099-01-01''', 'reg_time');

-- 添加contest.contest_type和subnet字段
CALL add_column_if_not_exists('contest', 'contest_type', 'smallint UNSIGNED default 0', 'password');
CALL modify_column_if_exists('contest', 'contest_type', 'smallint UNSIGNED default 0');
CALL add_column_if_not_exists('contest', 'subnet', 'varchar(255) not null default ''''', 'contest_type');

-- 修改online.refer字段
CALL modify_column_if_exists('online', 'refer', 'varchar(4096) DEFAULT NULL');

-- 添加solution.first_time字段和索引
CALL add_column_if_not_exists('solution', 'first_time', 'tinyint(1) default 0', 'pass_rate');
CALL add_index_if_not_exists('solution', 'fst', 'first_time');

-- 创建solution_ai_answer表（已使用IF NOT EXISTS）
CREATE TABLE IF NOT EXISTS solution_ai_answer ( solution_id int not null default 0, answer mediumtext ,primary key (solution_id)) charset utf8mb4;

-- 创建solution.in_date索引
DELIMITER //
DROP PROCEDURE IF EXISTS create_index_if_not_exists //
CREATE PROCEDURE create_index_if_not_exists(
    IN index_name VARCHAR(64),
    IN table_name VARCHAR(64),
    IN index_definition VARCHAR(255)
)
BEGIN
    DECLARE index_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO index_count
    FROM information_schema.statistics 
    WHERE table_schema = DATABASE() 
    AND table_name = table_name 
    AND index_name = index_name;
    
    IF index_count = 0 THEN
        SET @sql = CONCAT('CREATE INDEX `', index_name, '` ON `', table_name, '` (', index_definition, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

CALL create_index_if_not_exists('idx_solution_in_date', 'solution', 'in_date');

-- 处理触发器
-- 删除并重新创建触发器
delimiter //
drop trigger if exists firstAC//
UPDATE solution s JOIN (SELECT user_id,problem_id, MIN(solution_id) AS first_solution_id FROM solution WHERE result = 4 GROUP BY user_id, problem_id ) t ON s.solution_id = t.first_solution_id SET s.first_time = 1 //
create trigger firstAC
before update on solution
for each row
begin
 declare acTimes int;
 if new.result=4 then
    select count(1) from solution where problem_id=new.problem_id and result=4 and first_time=1 and  user_id=new.user_id into acTimes;
    if acTimes=0 then
        set new.first_time=1;
    end if;
end if;
end//
delimiter ;

-- 清理临时存储过程
DELIMITER //
DROP PROCEDURE IF EXISTS add_column_if_not_exists //
DROP PROCEDURE IF EXISTS modify_column_if_exists //
DROP PROCEDURE IF EXISTS change_column_if_exists //
DROP PROCEDURE IF EXISTS add_index_if_not_exists //
DROP PROCEDURE IF EXISTS add_primary_key_if_not_exists //
DROP PROCEDURE IF EXISTS create_index_if_not_exists //
DELIMITER ;

-- #create fulltext index problem_title_source_index on problem(title,source);

                                                                                                         
