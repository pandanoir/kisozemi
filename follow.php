<?php

require_once __DIR__ . '/../../password.php';

ini_set("display_errors", On);
error_reporting(E_ALL);
// ユーザから受け取ったユーザ名とパスワード
$screen_name = filter_input(INPUT_POST, 'screen_name');
$groupID = filter_input(INPUT_POST, 'groupID');

$groupID = intval($groupID);

$mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
if ($mysqli -> connect_errno) {
    print($mysqli->error);
    exit;
}
$db_selected = $mysqli->select_db('rabbitplot');
if (!$db_selected){
    die('データベース選択失敗です。' . $mysqli->error);
}
if ($stmt = $mysqli->prepare("SELECT userID FROM user WHERE screen_name = ?")) {
    $stmt->bind_param('s', $screen_name);
    $stmt->execute();
    $stmt->bind_result($userID);
    $stmt->fetch();
    $stmt->close();
}

if ($stmt = $mysqli->prepare('INSERT INTO follow_relation VALUES(?,?)')) {
    $stmt->bind_param('ii', $userID, $groupID);
    $stmt->execute();
    $stmt->close();
}
$mysqli->close();

print('success');