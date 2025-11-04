<?php
include 'select_database.php';

// Таблица users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE
)";
mysqli_query($conn, $sql) or die("Error creating users table: " . mysqli_error($conn));

// Таблица projects
$sql = "CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    owner_id INT NOT NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql) or die("Error creating projects table: " . mysqli_error($conn));

// Таблица project_members
$sql = "CREATE TABLE IF NOT EXISTS project_members (
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(50) NOT NULL,
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql) or die("Error creating project_members table: " . mysqli_error($conn));

// Таблица statuses
$sql = "CREATE TABLE IF NOT EXISTS statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    order_index INT NOT NULL
)";
mysqli_query($conn, $sql) or die("Error creating statuses table: " . mysqli_error($conn));

// Таблица tasks
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    status_id INT NOT NULL,
    assignee_id INT,
    start_date DATE,
    due_date DATE,
    priority INT,
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (status_id) REFERENCES statuses(id),
    FOREIGN KEY (assignee_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql) or die("Error creating tasks table: " . mysqli_error($conn));

// Таблица labels
$sql = "CREATE TABLE IF NOT EXISTS labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
)";
mysqli_query($conn, $sql) or die("Error creating labels table: " . mysqli_error($conn));

// Таблица task_labels
$sql = "CREATE TABLE IF NOT EXISTS task_labels (
    task_id INT NOT NULL,
    label_id INT NOT NULL,
    PRIMARY KEY (task_id, label_id),
    FOREIGN KEY (task_id) REFERENCES tasks(id),
    FOREIGN KEY (label_id) REFERENCES labels(id)
)";
mysqli_query($conn, $sql) or die("Error creating task_labels table: " . mysqli_error($conn));

// Таблица comments
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

// Таблица audit_logs
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

echo "All tables created successfully!";
mysqli_close($conn);
