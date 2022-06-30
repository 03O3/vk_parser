<?php
/*
 *
 * File by Encry at 16.04.2022, 18:56
 * Copyright (c) 2022.
 *
 */

require_once('database.php'); // Подключаем бд

$user_id = $_POST['user_id']; // Получаем юзер id
$first_name = $_POST['first_name']; // Получаем Имя
$last_name = $_POST['last_name']; // Получаем Фамилию


header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    if (empty($_GET['id'])) {
        // Получение последнего id из бд
        $check_lastid = mysqli_query($mysqli, "SELECT `id` FROM `users` ORDER BY `id` DESC LIMIT 1;");
        $last_id = mysqli_fetch_assoc($check_lastid);
        // Получение информации об последнем айди
        $check_id = mysqli_query($mysqli, "SELECT * FROM `users` WHERE `user_id` = " . $last_id['user_id'] . "");
        $info_id = mysqli_fetch_assoc($check_id);
        // Подготавливаем проверку на владением группы
        $check_group = mysqli_query($mysqli, "SELECT `group_id` FROM `groups` WHERE `owner_id` = " . $last_id['user_id'] . "");
        if (mysqli_num_rows($check_group) > 0) {
            $group_arr = mysqli_fetch_all($check_group);
            // Конструкция foreach предоставляет простой способ перебора массивов
            $group_ids = [];
            foreach ($group_arr as $group_info) {
                $group_ids[] = (int)$group_info['0'];
            }
            // Создаем json ответ с группами
            $info = ['status' => 'success', 'method' => $_SERVER['REQUEST_METHOD'], 'response' => ['id' => (int)$last_id['user_id'], 'first_name' => $info_id['first_name'], 'last_name' => $info_id['last_name'], 'reg_date' => $info_id['reg_date'], 'group_ids' => $group_ids]];
        } else {
            // Создаем json ответ без групп
            $info = ['status' => 'success', 'method' => $_SERVER['REQUEST_METHOD'], 'response' => ['id' => (int)$last_id['user_id'], 'first_name' => $info_id['first_name'], 'last_name' => $info_id['last_name'], 'reg_date' => $info_id['reg_date']]];
        }
        // Вывод response
        echo json_encode($info, JSON_UNESCAPED_UNICODE);
    } else {
        // Подготавливаем проверку существование id
        $id = $mysqli->real_escape_string($_GET['id']);
        $check_id = mysqli_query($mysqli, "SELECT * FROM `users` WHERE `user_id` = '$id'");
        // Проверяем существование id
        if (mysqli_num_rows($check_id) > 0) {
            // Получение инфо о id
            $info_id = mysqli_fetch_assoc($check_id);
            // Подготавливаем проверку на владением группы
            $check_group = mysqli_query($mysqli, "SELECT `group_id` FROM `groups` WHERE `owner_id` = " . $_GET['id'] . "");
            if (mysqli_num_rows($check_group) > 0) {
                $group_arr = mysqli_fetch_all($check_group);
                // Конструкция foreach предоставляет простой способ перебора массивов
                $group_ids = [];
                foreach ($group_arr as $group_info) {
                    $group_ids[] = (int)$group_info['0'];
                }
                // Создаем json ответ с группами
                $info = ['status' => 'success', 'method' => $_SERVER['REQUEST_METHOD'], 'response' => ['id' => (int)$id, 'first_name' => $info_id['first_name'], 'last_name' => $info_id['last_name'], 'reg_date' => $info_id['reg_date'], 'group_ids' => $group_ids]];
            } else {
                // Создаем json ответ без групп
                $info = ['status' => 'success', 'method' => $_SERVER['REQUEST_METHOD'], 'response' => ['id' => (int)$id, 'first_name' => $info_id['first_name'], 'last_name' => $info_id['last_name'], 'reg_date' => $info_id['reg_date']]];
            }
            // Вывод response
            echo json_encode($info, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['status' => 'fail', 'method' => $_SERVER['REQUEST_METHOD'], 'message' => "There is no such user in the database"]);
        }
    }
} else {
    if ($_SERVER['HTTP_USER_AGENT'] == "CryptoneBOT") { // Проверка на юзер агента
        // Проверка существует ли юзер
        $check_user = mysqli_query($mysqli, "SELECT * FROM `users` WHERE `user_id` = '$user_id'");
        // Если юзера нет
        if (mysqli_num_rows($check_user) < 1) {
            // Проверка на пустые поля
            if ($user_id_field = !empty($user_id) && $first_name_field = !empty($first_name) && $last_name_field = !empty($last_name)) {
                // Получение даты регистрации
                $text = file_get_contents("https://vk.com/foaf.php?id=" . $user_id . "");
                preg_match('|ya:created dc:date="(.*?)"|si', $text, $arr);
                $time_create = date("d-m-Y H:i:s", strtotime($arr[1]));
                // Заносим юзера в бд
                mysqli_query($mysqli, "INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `reg_date`) VALUES ('$user_id', '$first_name', '$last_name', '$time_create');");
                // Выводим о успехе
                echo json_encode(['status' => 'success', 'method' => $_SERVER['REQUEST_METHOD'], 'user_id' => $user_id]);
            } else {
                // Говорим о том что одно из полей POST пустое
                echo json_encode(['status' => 'failed', 'method' => $_SERVER['REQUEST_METHOD'], 'user_id' => $user_id_field, '$first_name' => $first_name_field, '$last_name' => $last_name_field, 'message' => "One of the fields is empty"]);
            }
        } else {
            // Иначе говорим что он уже есть
            echo json_encode(['status' => 'failed', 'method' => $_SERVER['REQUEST_METHOD'], 'message' => "This user already exists in the database"]);
        }
    } else {
        // Если юзер агент не тот
        http_response_code(401);
    }
}
?>