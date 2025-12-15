<?php
include 'select_database.php';

mysqli_set_charset($conn, "utf8mb4");

$users = [
    ['admin@example.com', '123456', 'admin', 'Admin', 'User'],
    ['koko@example.com', '123456', 'user', 'Koko', 'Kokov'],
    ['kiki@example.com', '123456', 'user', 'Kiki', 'Kikov']
];

foreach ($users as $userData) {
    $email = $userData[0];
    $password = $userData[1];
    $role = $userData[2];
    $firstName = $userData[3];
    $lastName = $userData[4];
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $checkSql = "SELECT id, password FROM users WHERE email = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "s", $email);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    $existing = mysqli_fetch_assoc($result);
    mysqli_stmt_close($checkStmt);
    
    if ($existing) {
        $updateSql = "UPDATE users SET password = ?, role = ? WHERE email = ?";
        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "sss", $passwordHash, $role, $email);
        
        if (mysqli_stmt_execute($updateStmt)) {
            echo "✓ Updated password for $email (hash length: " . strlen($passwordHash) . ")<br>";
            
            $verifySql = "SELECT password FROM users WHERE email = ?";
            $verifyStmt = mysqli_prepare($conn, $verifySql);
            mysqli_stmt_bind_param($verifyStmt, "s", $email);
            mysqli_stmt_execute($verifyStmt);
            $verifyResult = mysqli_stmt_get_result($verifyStmt);
            $row = mysqli_fetch_assoc($verifyResult);
            
            if (password_verify($password, $row['password'])) {
                echo "  ✓ Password verification PASSED!<br>";
            } else {
                echo "  ✗ Password verification FAILED!<br>";
            }
            mysqli_stmt_close($verifyStmt);
        } else {
            echo "✗ Error updating $email: " . mysqli_error($conn) . "<br>";
        }
        mysqli_stmt_close($updateStmt);
    } else {
        $insertSql = "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertSql);
        mysqli_stmt_bind_param($insertStmt, "sssss", $firstName, $lastName, $email, $passwordHash, $role);
        
        if (mysqli_stmt_execute($insertStmt)) {
            echo "✓ Created user $email (hash length: " . strlen($passwordHash) . ")<br>";
            
            if (password_verify($password, $passwordHash)) {
                echo "  ✓ Password verification PASSED!<br>";
            }
        } else {
            echo "✗ Error creating $email: " . mysqli_error($conn) . "<br>";
        }
        mysqli_stmt_close($insertStmt);
    }
}

echo "<br>=== Password fix completed! ===<br>";
echo "You can now login with:<br>";
echo "- admin@example.com / 123456<br>";
echo "- koko@example.com / 123456<br>";
echo "- kiki@example.com / 123456<br>";

mysqli_close($conn);

