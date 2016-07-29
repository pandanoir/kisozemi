<?php

require_once __DIR__ . '/../../../password.php';

ini_set("display_errors", On);
error_reporting(E_ALL);

$groupIDs = filter_input(INPUT_POST, 'groupIDs'); // 'X,Y,Z,...'
$user_screen_name = filter_input(INPUT_POST, 'user_screen_name'); // 'X,Y,Z,...'
$typeDic = array(
    3=>'int',
    252=>'string',
    253=>'string',
    254=>'string'
);


$mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
if ($mysqli -> connect_errno) {
    print($mysqli->error);
    exit;
}
$db_selected = $mysqli->select_db('rabbitplot');
if (!$db_selected){
    die('データベース選択失敗です。' . $mysqli->error);
}

$result = $mysqli->query("SELECT * FROM event WHERE groupID IN(${groupIDs})");
if (!$result) {
    die('クエリーが失敗しました。' . $mysqli->error);
}
$finfo = $result->fetch_fields();
foreach($finfo as $value) {
    $type[$value->name] = $typeDic[$value->type];
}

$res = array();
while ($row = $result->fetch_assoc()) {
    foreach($row as $key => $value) {
        settype($row[$key], $type[$key]);
    }
    $res[] = json_encode($row);
}
$result->free();

if ($stmt = $mysqli->prepare('SELECT userID FROM user WHERE screen_name = ?')) {
    $stmt->bind_param('s', $user_screen_name);
    $stmt->execute();
    $stmt->bind_result($userID);
    $stmt->fetch();
    $stmt->close();
    if ($stmt = $mysqli->prepare('SELECT userID, title, selector, transformed FROM user_event WHERE userID = ?')) {
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $stmt->bind_result($userID, $title, $selector, $transformed);
        while ($stmt->fetch()) {
            $res[] = '{"title":"' . $title . '", "selector":"' . $selector . '","groupID":1,"transformed":' . $transformed . '}';
        }
        $stmt->close();
    }
}
$mysqli->close();

print('[' . join(',', $res) . ']');