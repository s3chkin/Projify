<?php
include 'select_database.php';

$users = [
    ['admin@example.com', '123456', 'admin'],
    ['koko@example.com', '123456', 'user'],
    ['kiki@example.com', '123456', 'user']
];

foreach ($users as $userData) {
    $email = mysqli_real_escape_string($conn, $userData[0]);
    $password = $userData[1];
    $role = $userData[2];
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $passwordHashEscaped = mysqli_real_escape_string($conn, $passwordHash);
    
    $checkUser = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkUser);
    
    if (mysqli_num_rows($result) > 0) {
        $updateSql = "UPDATE users SET password = '$passwordHashEscaped', role = '$role' WHERE email = '$email'";
        if (mysqli_query($conn, $updateSql)) {
            echo "Password updated for $email<br>";
        } else {
            echo "Error updating password for $email: " . mysqli_error($conn) . "<br>";
        }
    } else {
        $firstName = $role === 'admin' ? 'Admin' : ucfirst(explode('@', $email)[0]);
        $lastName = $role === 'admin' ? 'User' : 'User';
        $insertSql = "INSERT INTO users (first_name, last_name, email, password, role) 
                      VALUES ('$firstName', '$lastName', '$email', '$passwordHashEscaped', '$role')";
        if (mysqli_query($conn, $insertSql)) {
            echo "User $email created successfully!<br>";
        } else {
            echo "Error creating user $email: " . mysqli_error($conn) . "<br>";
        }
    }
}

echo "<br>Password fix completed!<br>";
echo "You can now login with:<br>";
echo "- admin@example.com / 123456<br>";
echo "- koko@example.com / 123456<br>";
echo "- kiki@example.com / 123456<br>";

mysqli_close($conn);

