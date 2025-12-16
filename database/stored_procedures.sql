-- Съхранени процедури за системата Projify

-- Процедура за създаване на задача с валидации
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS sp_create_task(
    IN p_project_id INT,
    IN p_title VARCHAR(150),
    IN p_description TEXT,
    IN p_status_id INT,
    IN p_assignee_id INT,
    IN p_created_by INT,
    IN p_start_date DATE,
    IN p_due_date DATE,
    IN p_priority INT,
    IN p_sprint_id INT,
    OUT p_task_id INT,
    OUT p_error_message VARCHAR(255)
)
BEGIN
    DECLARE v_project_exists INT DEFAULT 0;
    DECLARE v_status_exists INT DEFAULT 0;
    DECLARE v_assignee_exists INT DEFAULT 0;
    DECLARE v_creator_exists INT DEFAULT 0;
    DECLARE v_date_valid BOOLEAN DEFAULT TRUE;
    DECLARE v_priority_valid BOOLEAN DEFAULT TRUE;
    
    -- Инициализация на изходни параметри
    SET p_task_id = NULL;
    SET p_error_message = NULL;
    
    -- Валидация: Проверка дали проектът съществува
    SELECT COUNT(*) INTO v_project_exists
    FROM projects
    WHERE id = p_project_id;
    
    IF v_project_exists = 0 THEN
        SET p_error_message = 'Проектът не съществува';
        LEAVE sp;
    END IF;
    
    -- Валидация: Проверка дали статусът съществува
    SELECT COUNT(*) INTO v_status_exists
    FROM statuses
    WHERE id = p_status_id;
    
    IF v_status_exists = 0 THEN
        SET p_error_message = 'Статусът не съществува';
        LEAVE sp;
    END IF;
    
    -- Валидация: Проверка дали назначеният потребител съществува (ако е зададен)
    IF p_assignee_id IS NOT NULL THEN
        SELECT COUNT(*) INTO v_assignee_exists
        FROM users
        WHERE id = p_assignee_id;
        
        IF v_assignee_exists = 0 THEN
            SET p_error_message = 'Назначеният потребител не съществува';
            LEAVE sp;
        END IF;
    END IF;
    
    -- Валидация: Проверка дали създателят съществува (ако е зададен)
    IF p_created_by IS NOT NULL THEN
        SELECT COUNT(*) INTO v_creator_exists
        FROM users
        WHERE id = p_created_by;
        
        IF v_creator_exists = 0 THEN
            SET p_error_message = 'Създателят не съществува';
            LEAVE sp;
        END IF;
    END IF;
    
    -- Валидация: Проверка на датите
    IF p_start_date IS NOT NULL AND p_due_date IS NOT NULL THEN
        IF p_due_date < p_start_date THEN
            SET p_error_message = 'Крайната дата не може да е преди началната дата';
            LEAVE sp;
        END IF;
    END IF;
    
    -- Валидация: Проверка на приоритета
    IF p_priority IS NOT NULL THEN
        IF p_priority < 1 OR p_priority > 4 THEN
            SET p_error_message = 'Приоритетът трябва да е между 1 и 4';
            LEAVE sp;
        END IF;
    END IF;
    
    -- Валидация: Проверка дали спринтът съществува (ако е зададен)
    IF p_sprint_id IS NOT NULL THEN
        DECLARE v_sprint_exists INT DEFAULT 0;
        SELECT COUNT(*) INTO v_sprint_exists
        FROM sprints
        WHERE id = p_sprint_id AND project_id = p_project_id;
        
        IF v_sprint_exists = 0 THEN
            SET p_error_message = 'Спринтът не съществува или не принадлежи на проекта';
            LEAVE sp;
        END IF;
    END IF;
    
    -- Създаване на задачата
    INSERT INTO tasks (
        project_id, sprint_id, title, description, status_id, 
        assignee_id, created_by, start_date, due_date, priority
    ) VALUES (
        p_project_id, p_sprint_id, p_title, p_description, p_status_id,
        p_assignee_id, p_created_by, p_start_date, p_due_date, p_priority
    );
    
    SET p_task_id = LAST_INSERT_ID();
    
    -- Записване в audit log
    IF p_created_by IS NOT NULL THEN
        INSERT INTO audit_logs (user_id, action, entity, entity_id)
        VALUES (p_created_by, 'create', 'task', p_task_id);
    END IF;
    
END$$

DELIMITER ;

-- Процедура за обновяване на задача с валидации
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS sp_update_task(
    IN p_task_id INT,
    IN p_title VARCHAR(150),
    IN p_description TEXT,
    IN p_status_id INT,
    IN p_assignee_id INT,
    IN p_start_date DATE,
    IN p_due_date DATE,
    IN p_priority INT,
    OUT p_success BOOLEAN,
    OUT p_error_message VARCHAR(255)
)
BEGIN
    DECLARE v_task_exists INT DEFAULT 0;
    DECLARE v_status_exists INT DEFAULT 0;
    DECLARE v_assignee_exists INT DEFAULT 0;
    
    SET p_success = FALSE;
    SET p_error_message = NULL;
    
    -- Валидация: Проверка дали задачата съществува
    SELECT COUNT(*) INTO v_task_exists
    FROM tasks
    WHERE id = p_task_id;
    
    IF v_task_exists = 0 THEN
        SET p_error_message = 'Задачата не съществува';
        LEAVE sp;
    END IF;
    
    -- Валидация: Проверка дали статусът съществува
    SELECT COUNT(*) INTO v_status_exists
    FROM statuses
    WHERE id = p_status_id;
    
    IF v_status_exists = 0 THEN
        SET p_error_message = 'Статусът не съществува';
        LEAVE sp;
    END IF;
    
    -- Валидация: Проверка дали назначеният потребител съществува (ако е зададен)
    IF p_assignee_id IS NOT NULL THEN
        SELECT COUNT(*) INTO v_assignee_exists
        FROM users
        WHERE id = p_assignee_id;
        
        IF v_assignee_exists = 0 THEN
            SET p_error_message = 'Назначеният потребител не съществува';
            LEAVE sp;
        END IF;
    END IF;
    
    -- Валидация: Проверка на датите
    IF p_start_date IS NOT NULL AND p_due_date IS NOT NULL THEN
        IF p_due_date < p_start_date THEN
            SET p_error_message = 'Крайната дата не може да е преди началната дата';
            LEAVE sp;
        END IF;
    END IF;
    
    -- Валидация: Проверка на приоритета
    IF p_priority IS NOT NULL THEN
        IF p_priority < 1 OR p_priority > 4 THEN
            SET p_error_message = 'Приоритетът трябва да е между 1 и 4';
            LEAVE sp;
        END IF;
    END IF;
    
    -- Обновяване на задачата
    UPDATE tasks
    SET 
        title = p_title,
        description = p_description,
        status_id = p_status_id,
        assignee_id = p_assignee_id,
        start_date = p_start_date,
        due_date = p_due_date,
        priority = p_priority
    WHERE id = p_task_id;
    
    SET p_success = TRUE;
    
END$$

DELIMITER ;

-- Процедура за завършване на проект с валидация
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS sp_complete_project(
    IN p_project_id INT,
    IN p_user_id INT,
    OUT p_success BOOLEAN,
    OUT p_error_message VARCHAR(255)
)
BEGIN
    DECLARE v_project_exists INT DEFAULT 0;
    DECLARE v_unfinished_tasks INT DEFAULT 0;
    DECLARE v_done_status_id INT DEFAULT 0;
    
    SET p_success = FALSE;
    SET p_error_message = NULL;
    
    -- Валидация: Проверка дали проектът съществува
    SELECT COUNT(*) INTO v_project_exists
    FROM projects
    WHERE id = p_project_id;
    
    IF v_project_exists = 0 THEN
        SET p_error_message = 'Проектът не съществува';
        LEAVE sp;
    END IF;
    
    -- Намиране на статуса 'Done'
    SELECT id INTO v_done_status_id
    FROM statuses
    WHERE name = 'Done'
    LIMIT 1;
    
    -- Валидация: Проверка дали всички задачи са завършени
    SELECT COUNT(*) INTO v_unfinished_tasks
    FROM tasks
    WHERE project_id = p_project_id
    AND status_id != v_done_status_id;
    
    IF v_unfinished_tasks > 0 THEN
        SET p_error_message = CONCAT('Проектът не може да се завърши. Има ', v_unfinished_tasks, ' незавършени задачи.');
        LEAVE sp;
    END IF;
    
    -- Завършване на проекта
    UPDATE projects
    SET status = 'completed'
    WHERE id = p_project_id;
    
    -- Записване в audit log
    IF p_user_id IS NOT NULL THEN
        INSERT INTO audit_logs (user_id, action, entity, entity_id)
        VALUES (p_user_id, 'complete', 'project', p_project_id);
    END IF;
    
    SET p_success = TRUE;
    
END$$

DELIMITER ;

-- Процедура за получаване на статистика за проект
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS sp_get_project_stats(
    IN p_project_id INT,
    OUT p_total_tasks INT,
    OUT p_completed_tasks INT,
    OUT p_overdue_tasks INT,
    OUT p_total_members INT
)
BEGIN
    DECLARE v_done_status_id INT DEFAULT 0;
    
    -- Намиране на статуса 'Done'
    SELECT id INTO v_done_status_id
    FROM statuses
    WHERE name = 'Done'
    LIMIT 1;
    
    -- Общ брой задачи
    SELECT COUNT(*) INTO p_total_tasks
    FROM tasks
    WHERE project_id = p_project_id;
    
    -- Завършени задачи
    SELECT COUNT(*) INTO p_completed_tasks
    FROM tasks
    WHERE project_id = p_project_id
    AND status_id = v_done_status_id;
    
    -- Просрочени задачи
    SELECT COUNT(*) INTO p_overdue_tasks
    FROM tasks
    WHERE project_id = p_project_id
    AND due_date < CURDATE()
    AND status_id != v_done_status_id;
    
    -- Общ брой членове (включително собственика)
    SELECT COUNT(*) + 1 INTO p_total_members
    FROM project_members
    WHERE project_id = p_project_id;
    
END$$

DELIMITER ;

-- Процедура за валидация на потребителски имейл
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS sp_validate_user_email(
    IN p_email VARCHAR(150),
    OUT p_is_valid BOOLEAN,
    OUT p_exists BOOLEAN,
    OUT p_error_message VARCHAR(255)
)
BEGIN
    DECLARE v_email_count INT DEFAULT 0;
    
    SET p_is_valid = FALSE;
    SET p_exists = FALSE;
    SET p_error_message = NULL;
    
    -- Валидация: Проверка за празен имейл
    IF p_email IS NULL OR p_email = '' THEN
        SET p_error_message = 'Имейлът не може да бъде празен';
        LEAVE sp;
    END IF;
    
    -- Валидация: Проверка за формат на имейл (основна проверка)
    IF p_email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        SET p_error_message = 'Невалиден формат на имейл';
        LEAVE sp;
    END IF;
    
    -- Проверка дали имейлът вече съществува
    SELECT COUNT(*) INTO v_email_count
    FROM users
    WHERE email = p_email;
    
    IF v_email_count > 0 THEN
        SET p_exists = TRUE;
        SET p_error_message = 'Имейлът вече съществува';
    ELSE
        SET p_exists = FALSE;
    END IF;
    
    SET p_is_valid = TRUE;
    
END$$

DELIMITER ;

