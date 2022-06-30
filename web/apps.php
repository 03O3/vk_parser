<?php
/*
 *
 * File by Encry at 16.04.2022, 18:53
 * Copyright (c) 2022.
 *
 */

require_once('database.php');

$user_id = $_POST['user_id'];
$app_id = $_POST['app_id'];
$owner_id = $_POST['owner_id'];


header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
} else {
    if($_SERVER['HTTP_USER_AGENT'] == "CryptoneBOT"){
        $check_user = mysqli_query($mysqli, "SELECT * FROM `apps` WHERE `user_id` = '$user_id'");
        if (mysqli_num_rows($check_user) < 1){
            if(!empty($user_id) && !empty($app_id) && !empty($owner_id)){
                mysqli_query($mysqli, "INSERT INTO `apps` (`user_id`, `app_id`, `owner_id`) VALUES ('$user_id', '$app_id', '$owner_id');");
                echo json_encode([ 'status' => 'success', 'method' =>  $_SERVER['REQUEST_METHOD'], 'user_id' =>  $user_id]);
            } else {
                echo json_encode([ 'status' => 'failed', 'method' =>  $_SERVER['REQUEST_METHOD'], 'message' =>  "One of the fields is empty"]);
            }
        } else {
            echo json_encode([ 'status' => 'failed', 'method' =>  $_SERVER['REQUEST_METHOD'], 'message' =>  "This user already exists in the database"]);
        }
    } else {
        http_response_code(401);
    }
}




?>