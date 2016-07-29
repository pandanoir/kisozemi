<?php

require_once __DIR__ . '/../../../password.php';

ini_set("display_errors", On);
error_reporting(E_ALL);

$user_name = filter_input(INPUT_POST, 'user_name');
$group_name = filter_input(INPUT_POST, 'group_name');
$title = filter_input(INPUT_POST, 'title');
$selector = filter_input(INPUT_POST, 'selector');
$transformed = filter_input(INPUT_POST, 'transformed');

$mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
if ($mysqli -> connect_errno) {
    print($mysqli->error);
    exit;
}
$db_selected = $mysqli->select_db('rabbitplot');
if (!$db_selected){
    die('データベース選択失敗です。' . $mysqli->error);
}

if ($group_name === 'private') {
    if ($stmt = $mysqli->prepare('SELECT userID FROM user WHERE screen_name = ?')) {
        $stmt->bind_param('s', $user_name);
        $stmt->execute();
        $stmt->bind_result($userID);
        $stmt->fetch();
        $stmt->close();
        if ($stmt = $mysqli->prepare('INSERT INTO `user_event` VALUES(?,?,?,?)')){
            $stmt->bind_param('isss', $userID, $title, $selector, $transformed);
            $stmt->execute();
            if ($stmt->affected_rows === 1) {
                print('succeeded');
            } else {
                print('failed: failed to insert event into `user_event`');
            }
            $stmt->close();
        }
    } else {
        print('failed: failed to get userID');
    }
} else {
    if ($stmt = $mysqli->prepare('SELECT groupID FROM `group` WHERE screen_name = ?')) {
        $stmt->bind_param('s', $group_name);
        $stmt->execute();
        $stmt->bind_result($groupID);
        $stmt->fetch();
        $stmt->close();
        if ($stmt = $mysqli->prepare('INSERT INTO `event` VALUES(?,?,?,?)')){
            $stmt->bind_param('isss', $groupID, $title, $selector, $transformed);
            $stmt->execute();
            if ($stmt->affected_rows === 1) {
                print('succeeded');
            } else {
                print('failed: failed to insert event into `event`');
            }
            $stmt->close();
        }
    } else {
        print('failed: failed to get groupID');
    }
}
$mysqli->close();