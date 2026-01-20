<?php
require_once __DIR__ . '/includes/auth.php';
logout();
header('Location: /saint-paul/login.php');
exit;

