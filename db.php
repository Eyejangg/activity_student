<?php
$host = 'localhost';
$dbname = 'student_activity';
$username = 'root';
$password = '';

// เพิ่ม option array เพื่อบังคับ UTF-8
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, $options);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
