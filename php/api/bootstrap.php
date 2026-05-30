<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../class/database.php';
require_once __DIR__ . '/../class/ApiResponse.php';
require_once __DIR__ . '/../class/JwtService.php';
require_once __DIR__ . '/../class/Student.php';

$database = new Database();
$conn = $database->getConnection();

$jwtService = new JwtService();
$studentService = new Student($conn);