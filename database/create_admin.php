<?php
include 'select_database.php';

$adminEmail = 'admin@example.com';
$adminPassword = '123456';

$checkAdmin = "SELECT id, password FROM users WHERE email = '$adminEmail'";
$result = mysqli_query($conn, $checkAdmin);

if (mysqli_num_rows($result) == 0) {
    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $passwordHashEscaped = mysqli_real_escape_string($conn, $passwordHash);
    $emailEscaped = mysqli_real_escape_string($conn, $adminEmail);
    
    $sql = "INSERT INTO users (first_name, last_name, email, password, role) 
            VALUES ('Admin', 'User', '$emailEscaped', '$passwordHashEscaped', 'admin')";
    
    if (mysqli_query($conn, $sql)) {
        echo "✓ Admin user created successfully!<br>";
        echo "Email: $adminEmail<br>";
        echo "Password: $adminPassword<br>";
        echo "Hash length: " . strlen($passwordHash) . "<br>";
        
        if (password_verify($adminPassword, $passwordHash)) {
            echo "✓ Password verification test PASSED!<br>";
        }
    } else {
        echo "✗ Error: " . mysqli_error($conn);
    }
} else {
    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $passwordHashEscaped = mysqli_real_escape_string($conn, $passwordHash);
    $emailEscaped = mysqli_real_escape_string($conn, $adminEmail);
    
    $updateSql = "UPDATE users SET role = 'admin', password = '$passwordHashEscaped' WHERE email = '$emailEscaped'";
    
    if (mysqli_query($conn, $updateSql)) {
        echo "✓ Admin user updated successfully!<br>";
        echo "Email: $adminEmail<br>";
        echo "Password: $adminPassword<br>";
        echo "Hash length: " . strlen($passwordHash) . "<br>";
        
        $verifySql = "SELECT password FROM users WHERE email = '$emailEscaped'";
        $verifyResult = mysqli_query($conn, $verifySql);
        $row = mysqli_fetch_assoc($verifyResult);
        
        if (password_verify($adminPassword, $row['password'])) {
            echo "✓ Password verification test PASSED!<br>";
        } else {
            echo "✗ Password verification test FAILED!<br>";
        }
    } else {
        echo "✗ Error updating: " . mysqli_error($conn) . "<br>";
    }
}

mysqli_close($conn);

