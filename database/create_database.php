<?php
include 'config.php';

$dbname = "Projify";

$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (mysqli_query($conn, $sql)) {
    echo "Database '$dbname' created successfully.<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

mysqli_close($conn);
