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
$result = $mysqli->query("SELECT groupID FROM `group` WHERE name LIKE '${keyword}%'");
if (!$result) {
    die('クエリーが失敗しました。' . $mysqli->error);
}
$res = array();
while ($row = $result->fetch_assoc()) {
    $res[] = $row['groupID'];
}
$result->free();
$mysqli->close();

print('[' . join(',', $res) . ']');