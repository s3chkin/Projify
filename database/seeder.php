<?php
include 'select_database.php';

$adminPassword = password_hash('123456', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (first_name, last_name, email, password, role) 
        VALUES ('Admin', 'User', 'admin@example.com', '$adminPassword', 'admin')";
mysqli_query($conn, $sql) or die("Error inserting admin user: " . mysqli_error($conn));

$users = [
    ['Koko', 'Kokov', 'koko@example.com', '123456'],
    ['Kiki', 'Kikov', 'kiki@example.com', '123456']
];

foreach ($users as $u) {
    $passwordHash = password_hash($u[3], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (first_name, last_name, email, password, role) 
            VALUES ('$u[0]', '$u[1]', '$u[2]', '$passwordHash', 'user')";
    mysqli_query($conn, $sql) or die("Error inserting user: " . mysqli_error($conn));
}

$projects = [
    ['Website Redesign', 1],
    ['Mobile App', 2]
];

foreach ($projects as $p) {
    $sql = "INSERT INTO projects (name, owner_id) VALUES ('$p[0]', $p[1])";
    mysqli_query($conn, $sql) or die("Error inserting project: " . mysqli_error($conn));
}

$statuses = [
    ['To Do', 1],
    ['In Progress', 2],
    ['Done', 3]
];

foreach ($statuses as $s) {
    $sql = "INSERT INTO statuses (name, order_index) VALUES ('$s[0]', $s[1])";
    mysqli_query($conn, $sql) or die("Error inserting status: " . mysqli_error($conn));
}

echo "Seed data inserted successfully!";
mysqli_close($conn);
