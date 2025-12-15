<?php
include 'select_database.php';

$adminEmail = 'admin@example.com';
$adminEmailEscaped = mysqli_real_escape_string($conn, $adminEmail);
$checkAdmin = "SELECT id FROM users WHERE email = '$adminEmailEscaped'";
$result = mysqli_query($conn, $checkAdmin);

if (mysqli_num_rows($result) == 0) {
    $adminPassword = password_hash('123456', PASSWORD_DEFAULT);
    $adminPasswordEscaped = mysqli_real_escape_string($conn, $adminPassword);
    $sql = "INSERT INTO users (first_name, last_name, email, password, role) 
            VALUES ('Admin', 'User', '$adminEmailEscaped', '$adminPasswordEscaped', 'admin')";
    if (mysqli_query($conn, $sql)) {
        echo "Admin user created successfully!<br>";
        echo "Email: $adminEmail<br>";
        echo "Password: 123456<br>";
    } else {
        echo "Error inserting admin user: " . mysqli_error($conn) . "<br>";
    }
} else {
    $adminPassword = password_hash('123456', PASSWORD_DEFAULT);
    $adminPasswordEscaped = mysqli_real_escape_string($conn, $adminPassword);
    $updateSql = "UPDATE users SET role = 'admin', password = '$adminPasswordEscaped' WHERE email = '$adminEmailEscaped'";
    if (mysqli_query($conn, $updateSql)) {
        echo "Admin user already exists. Password and role updated.<br>";
        echo "Email: $adminEmail<br>";
        echo "Password: 123456<br>";
    } else {
        echo "Error updating admin user: " . mysqli_error($conn) . "<br>";
    }
}

$users = [
    ['Koko', 'Kokov', 'koko@example.com', '123456'],
    ['Kiki', 'Kikov', 'kiki@example.com', '123456']
];

foreach ($users as $u) {
    $email = mysqli_real_escape_string($conn, $u[2]);
    $checkUser = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkUser);
    
    if (mysqli_num_rows($result) == 0) {
        $passwordHash = password_hash($u[3], PASSWORD_DEFAULT);
        $firstName = mysqli_real_escape_string($conn, $u[0]);
        $lastName = mysqli_real_escape_string($conn, $u[1]);
        $passwordHashEscaped = mysqli_real_escape_string($conn, $passwordHash);
        
        $sql = "INSERT INTO users (first_name, last_name, email, password, role) 
                VALUES ('$firstName', '$lastName', '$email', '$passwordHashEscaped', 'user')";
        if (mysqli_query($conn, $sql)) {
            echo "User $email created successfully!<br>";
        } else {
            echo "Error inserting user $email: " . mysqli_error($conn) . "<br>";
        }
    } else {
        $passwordHash = password_hash($u[3], PASSWORD_DEFAULT);
        $passwordHashEscaped = mysqli_real_escape_string($conn, $passwordHash);
        $updateSql = "UPDATE users SET password = '$passwordHashEscaped' WHERE email = '$email'";
        if (mysqli_query($conn, $updateSql)) {
            echo "Password updated for existing user $email<br>";
        } else {
            echo "User $email already exists.<br>";
        }
    }
}

$projects = [
    ['Website Redesign', 1],
    ['Mobile App', 2]
];

foreach ($projects as $p) {
    $checkProject = "SELECT id FROM projects WHERE name = '$p[0]' AND owner_id = $p[1]";
    $result = mysqli_query($conn, $checkProject);
    
    if (mysqli_num_rows($result) == 0) {
        $sql = "INSERT INTO projects (name, owner_id) VALUES ('$p[0]', $p[1])";
        if (mysqli_query($conn, $sql)) {
            echo "Project '$p[0]' created successfully!<br>";
        } else {
            echo "Error inserting project '$p[0]': " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Project '$p[0]' already exists.<br>";
    }
}

$statuses = [
    ['To Do', 1],
    ['In Progress', 2],
    ['Done', 3]
];

foreach ($statuses as $s) {
    $checkStatus = "SELECT id FROM statuses WHERE name = '$s[0]'";
    $result = mysqli_query($conn, $checkStatus);
    
    if (mysqli_num_rows($result) == 0) {
        $sql = "INSERT INTO statuses (name, order_index) VALUES ('$s[0]', $s[1])";
        if (mysqli_query($conn, $sql)) {
            echo "Status '$s[0]' created successfully!<br>";
        } else {
            echo "Error inserting status '$s[0]': " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Status '$s[0]' already exists.<br>";
    }
}

echo "<br>Seed data process completed!";
mysqli_close($conn);
