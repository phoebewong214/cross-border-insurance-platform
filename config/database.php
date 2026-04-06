<?php
// 数据库配置
define('DB_DSN', 'mysql:host=localhost;dbname=insurance_system;charset=utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', '');

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 设置时区
date_default_timezone_set('Asia/Macau');
