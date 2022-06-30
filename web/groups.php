<?php
/*
 *
 * File by Encry at 16.04.2022, 18:56
 * Copyright (c) 2022.
 *
 */

require_once('database.php');

$group_id = $_POST['group_id'];
$owner_id = $_POST['owner_id'];


header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    if (empty($_GET['id'])) {
        // Получение последнего group_id из бд
        $check_last_group = mysqli_query($mysqli, "SELECT `id` FROM `groups` ORDER BY `id` DESC LIMIT 1;");
        $last_group = mysqli_fetch_assoc($check_last_group);
        // Получение информации об последнем айди
        $check_id = mysqli_query($mysqli, "SELECT * FROM `groups` WHERE `id` = " . $last_group['id'] . "");
        $info_last_group_id = mysqli_fetch_assoc($check_id);
        // Создаем json
        $info = ['status' => 'success', 'method' => $_SERVER['REQUEST_METHOD'], 'response' => ['group_id' => (int)$info_last_group_id['group_id'], 'owner_id' => (int)$info_last_group_id['owner_id']]];
        // Выводим
        echo json_encode($info, JSON_UNESCAPED_UNICODE);
    }
} else {
    if ($_SERVER['HTTP_USER_AGENT'] == "CryptoneBOT") {
        if (!empty($group_id) && !empty($owner_id)) {
            $check_group = mysqli_query($mysqli, "SELECT group_id, owner_id FROM `groups` WHERE `group_id` = '$group_id' AND `owner_id` = '$owner_id'");
            if (mysqli_num_rows($check_group) < 1) {
                mysqli_query($mysqli, "INSERT INTO `groups` (`group_id`, `owner_id`) VALUES ('$group_id', '$owner_id');");
                echo json_encode(['status' => 'success', 'method' => $_SERVER['REQUEST_METHOD'], 'group_id' => $group_id]);
            } else {
                echo json_encode(['status' => 'failed', 'method' => $_SERVER['REQUEST_METHOD'], 'message' => "This group and owner already exists in the database"]);
            }
        } else {
            echo json_encode(['status' => 'failed', 'method' => $_SERVER['REQUEST_METHOD'], 'message' => "One of the fields is empty"]);
        }
    } else {
        http_response_code(401);
    }
}

?>