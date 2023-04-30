<?php
/*
 *
 * File by Encry at 16.04.2022, 18:53
 * Copyright (c) 2022.
 *
 */

require_once('database.php');

// Проверяем, что запрос был POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

// Проверяем, что запрос от CryptoneBOT
if ($_SERVER['HTTP_USER_AGENT'] !== "CryptoneBOT") {
    http_response_code(401);
    exit();
}

$user_id = $_POST['user_id'];
$app_id = $_POST['app_id'];
$owner_id = $_POST['owner_id'];

// Проверяем, что все необходимые поля заполнены
if (empty($user_id) || empty($app_id) || empty($owner_id)) {
    echo json_encode(['status' => 'failed', 'method' => $_SERVER['REQUEST_METHOD'], 'message' => "One of the fields is empty"]);
    exit();
}

// Проверяем, что пользователь еще не добавлен в базу данных
$check_user = mysqli_query($mysqli, "SELECT * FROM `apps` WHERE `user_id` = '$user_id'");
if (mysqli_num_rows($check_user) > 0) {
    echo json_encode(['status' => 'failed', 'method' => $_SERVER['REQUEST_METHOD'], 'message' => "This user already exists in the database"]);
    exit();
}

// Добавляем пользователя в базу данных
mysqli_query($mysqli, "INSERT INTO `apps` (`user_id`, `app_id`, `owner_id`) VALUES ('$user_id', '$app_id', '$owner_id');");
echo json_encode(['status' => 'success', 'method' => $_SERVER['REQUEST_METHOD'], 'user_id' => $user_id]);

// Устанавливаем правильный заголовок Content-Type
header('Content-Type: application/json; charset=utf-8');
?> 




?>
