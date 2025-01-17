<?php
// Hash password menggunakan PHP
$password = 'admin12345'; // Password asli
$hashed_password = password_hash($password, PASSWORD_BCRYPT); // Hash password
echo "Hash Password: " . $hashed_password;
?>
