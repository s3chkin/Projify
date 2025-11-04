<?php
include 'config.php';

$dbname = "Projify";

if (mysqli_select_db($conn, $dbname)) {
    echo "Database '$dbname' selected successfully.<br>";
} else {
    die("Error selecting database: " . mysqli_error($conn));
}
