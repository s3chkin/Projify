<?php
$servername = "localhost";
$port = 3307; // XAMPP използва порт 3307
$username = "root";
$password = "";

$conn = mysqli_connect($servername, $username, $password, null, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
