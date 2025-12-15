<?php
include 'select_database.php';

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user' CHECK (role IN ('admin', 'user'))
)";
mysqli_query($conn, $sql) or die("Error creating users table: " . mysqli_error($conn));

// Добавяне на password колона ако таблицата вече съществува без нея
$check = "SHOW COLUMNS FROM users LIKE 'password'";
$result = mysqli_query($conn, $check);
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL AFTER email";
    mysqli_query($conn, $sql) or die("Error adding password column: " . mysqli_error($conn));
}

// Добавяне на role колона ако таблицата вече съществува без нея
$check = "SHOW COLUMNS FROM users LIKE 'role'";
$result = mysqli_query($conn, $check);
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user'";
    mysqli_query($conn, $sql) or die("Error adding role column: " . mysqli_error($conn));
}

// Добавяне на CHECK constraint за role ако не съществува
$checkConstraint = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = 'projify' AND TABLE_NAME = 'users' 
                    AND CONSTRAINT_TYPE = 'CHECK'";
$result = mysqli_query($conn, $checkConstraint);
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE users ADD CONSTRAINT chk_user_role CHECK (role IN ('admin', 'user'))";
    @mysqli_query($conn, $sql);
}


$sql = "CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    owner_id INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'completed')),
    FOREIGN KEY (owner_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql) or die("Error creating projects table: " . mysqli_error($conn));

// Добавяне на status колона ако таблицата вече съществува без нея
$check = "SHOW COLUMNS FROM projects LIKE 'status'";
$result = mysqli_query($conn, $check);
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE projects ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'completed'))";
    @mysqli_query($conn, $sql);
}

// Добавяне на UNIQUE constraint за (owner_id, name), ако не съществува
$checkUnique = "SELECT INDEX_NAME 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'projects' 
                  AND INDEX_NAME = 'uniq_owner_project_name'";
$result = mysqli_query($conn, $checkUnique);
if ($result && mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE projects ADD CONSTRAINT uniq_owner_project_name UNIQUE (owner_id, name)";
    @mysqli_query($conn, $sql);
}


$sql = "CREATE TABLE IF NOT EXISTS project_members (
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(50) NOT NULL CHECK (role IN ('owner', 'member', 'viewer')),
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql) or die("Error creating project_members table: " . mysqli_error($conn));

// Добавяне на CHECK constraint за role ако таблицата вече съществува
$checkConstraint = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = 'projify' AND TABLE_NAME = 'project_members' 
                    AND CONSTRAINT_TYPE = 'CHECK'";
$result = mysqli_query($conn, $checkConstraint);
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE project_members ADD CONSTRAINT chk_role CHECK (role IN ('owner', 'member', 'viewer'))";
    @mysqli_query($conn, $sql);
}


$sql = "CREATE TABLE IF NOT EXISTS statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    order_index INT NOT NULL
)";
mysqli_query($conn, $sql) or die("Error creating statuses table: " . mysqli_error($conn));


$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    status_id INT NOT NULL,
    assignee_id INT,
    start_date DATE,
    due_date DATE,
    priority INT CHECK (priority >= 1 AND priority <= 4),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES statuses(id),
    FOREIGN KEY (assignee_id) REFERENCES users(id),
    CHECK (due_date IS NULL OR start_date IS NULL OR due_date >= start_date)
)";
mysqli_query($conn, $sql) or die("Error creating tasks table: " . mysqli_error($conn));

// Добавяне на CHECK constraints ако таблицата вече съществува
$checkConstraint = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = 'projify' AND TABLE_NAME = 'tasks' 
                    AND CONSTRAINT_TYPE = 'CHECK'";
$result = mysqli_query($conn, $checkConstraint);
if (mysqli_num_rows($result) == 0) {
    // Добавяне на CHECK за priority
    $sql = "ALTER TABLE tasks ADD CONSTRAINT chk_priority CHECK (priority >= 1 AND priority <= 4)";
    @mysqli_query($conn, $sql);
    
    // Добавяне на CHECK за дати
    $sql = "ALTER TABLE tasks ADD CONSTRAINT chk_dates CHECK (due_date IS NULL OR start_date IS NULL OR due_date >= start_date)";
    @mysqli_query($conn, $sql);
}


$sql = "CREATE TABLE IF NOT EXISTS labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
)";
mysqli_query($conn, $sql) or die("Error creating labels table: " . mysqli_error($conn));


$sql = "CREATE TABLE IF NOT EXISTS task_labels (
    task_id INT NOT NULL,
    label_id INT NOT NULL,
    PRIMARY KEY (task_id, label_id),
    FOREIGN KEY (task_id) REFERENCES tasks(id),
    FOREIGN KEY (label_id) REFERENCES labels(id)
)";
mysqli_query($conn, $sql) or die("Error creating task_labels table: " . mysqli_error($conn));


$sql = "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    author_id INT NOT NULL,
    text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id),
    FOREIGN KEY (author_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql) or die("Error creating comments table: " . mysqli_error($conn));


$sql = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(50) NOT NULL,
    entity_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql) or die("Error creating audit_logs table: " . mysqli_error($conn));


$sql = "CREATE TABLE IF NOT EXISTS sprints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CHECK (end_date >= start_date)
)";
mysqli_query($conn, $sql) or die("Error creating sprints table: " . mysqli_error($conn));

// Добавяне на sprint_id колона в tasks ако не съществува
$check = "SHOW COLUMNS FROM tasks LIKE 'sprint_id'";
$result = mysqli_query($conn, $check);
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE tasks ADD COLUMN sprint_id INT NULL AFTER project_id";
    mysqli_query($conn, $sql) or die("Error adding sprint_id column: " . mysqli_error($conn));
    
    $sql = "ALTER TABLE tasks ADD FOREIGN KEY (sprint_id) REFERENCES sprints(id) ON DELETE SET NULL";
    @mysqli_query($conn, $sql);
}

echo "All tables created successfully!";
mysqli_close($conn);
