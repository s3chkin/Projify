<?php
include 'select_database.php';

$email = 'admin@example.com';
$password = '123456';

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
echo "Generated hash length: " . strlen($passwordHash) . "<br>";
echo "Hash preview: " . substr($passwordHash, 0, 30) . "...<br><br>";

$emailEscaped = mysqli_real_escape_string($conn, $email);
$passwordHashEscaped = mysqli_real_escape_string($conn, $passwordHash);

$updateSql = "UPDATE users SET password = '$passwordHashEscaped' WHERE email = '$emailEscaped'";

if (mysqli_query($conn, $updateSql)) {
    echo "✓ Password updated successfully!<br>";
    
    $verifySql = "SELECT password FROM users WHERE email = '$emailEscaped'";
    $result = mysqli_query($conn, $verifySql);
    $row = mysqli_fetch_assoc($result);
    $storedHash = $row['password'];
    
    echo "Stored hash length: " . strlen($storedHash) . "<br>";
    echo "Stored hash preview: " . substr($storedHash, 0, 30) . "...<br><br>";
    
    if (password_verify($password, $storedHash)) {
        echo "✓✓✓ Password verification test PASSED!<br>";
        echo "You can now login with:<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
    } else {
        echo "✗ Password verification test FAILED!<br>";
        echo "This means the hash was not stored correctly.<br>";
    }
} else {
    echo "✗ Error: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);

