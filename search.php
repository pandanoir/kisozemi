<?php

require_once __DIR__ . '/../../password.php';

ini_set("display_errors", On);
error_reporting(E_ALL);

$keyword = filter_input(INPUT_POST, 'keyword');

$mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
if ($mysqli -> connect_errno) {
    print($mysqli->error);
    exit;
}
$db_selected = $mysqli->select_db('rabbitplot');
if (!$db_selected){
    die('データベース選択失敗です。' . $mysqli->error);
}

if ($stmt = $mysqli->prepare('SELECT groupID FROM `group` WHERE name LIKE ?')) {
    $res = array();
    $keyword = $keyword . '%';
    $stmt->bind_param('s', $keyword);
    $stmt->execute();
    $stmt->bind_result($groupID);
    while ($stmt->fetch()) {
        $res[] = $groupID;
    }
    $stmt->close();
}
$mysqli->close();

print('[' . join(',', $res) . ']');