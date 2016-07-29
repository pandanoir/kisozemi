<?php

require_once __DIR__ . '/../../../password.php';

ini_set("display_errors", On);
error_reporting(E_ALL);

$name = filter_input(INPUT_POST, 'name');
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
    $available = false;
    if ($stmt = $mysqli->prepare('SELECT COUNT(*) FROM `group` WHERE screen_name=?')) {
        // IDかぶりがあるか確認
        $stmt->bind_param('s', $screen_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();

        $available = $count === 0;
        $stmt->close();
    }

    if ($available){
        $result = $mysqli->query("SELECT MAX(groupID) AS max FROM `group`"); // 一番番号の大きいgroupIDを探す
        if (!$result) {
            die('クエリーが失敗しました。' . $mysqli->error);
        }
        $row = $result->fetch_assoc();
        $groupID = intval($row['max']) + 1;
        $result->free();
    
        if ($stmt = $mysqli->prepare('INSERT INTO `group` VALUES(?,?,?)')) {
            $stmt->bind_param('iss', $groupID, $name, $screen_name);
            $stmt->execute();
            if ($stmt->affected_rows === 1) {
                print('succeeded');
            } else {
                print('failed');
            }
            $stmt->close();
        }
    } else {
        print('failed');
    }
} else {
    print('failed');
}
$mysqli->close();