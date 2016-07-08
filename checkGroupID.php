<?php

require_once __DIR__ . '/../../password.php';

ini_set("display_errors", On);
error_reporting(E_ALL);

$screen_name = filter_input(INPUT_POST, 'screen_name');

$mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
if ($mysqli -> connect_errno) {
    print($mysqli->error);
    exit;
}
$db_selected = $mysqli->select_db('rabbitplot');
if (!$db_selected){
    die('データベース選択失敗です。' . $mysqli->error);
}

if (preg_match('/^[0-9a-z_]+$/i', $screen_name) === 1) {
    $result = $mysqli->query("SELECT COUNT(*) AS count FROM `group` WHERE screen_name='${screen_name}'");
    if (!$result) {
        die('クエリーが失敗しました。' . $mysqli->error);
    }
    $row = $result->fetch_assoc();
    if ($row['count'] === '0') {
        print('available');
    } else {
        print('unavailable');
    }
    $result->free();
    $mysqli->close();
} else {
    print('unavailable');
}