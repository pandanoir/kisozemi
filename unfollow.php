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
$result = $mysqli->query("SELECT userID FROM user WHERE screen_name = '${screen_name}'");
if (!$result) {
    die('クエリーが失敗しました。' . $mysqli->error);
}
$userinfo = $result->fetch_assoc();
$result->free();
$userID = $userinfo['userID'];

$result = $mysqli->query("DELETE FROM follow_relation WHERE userID = ${userID} AND groupID = ${groupID}");
if (!$result) {
    die('クエリーが失敗しました。' . $mysqli->error);
}
$result->free();

$mysqli->close();

print('success');