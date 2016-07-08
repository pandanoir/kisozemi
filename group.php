<?php

require_once __DIR__ . '/../../password.php';

ini_set("display_errors", On);
error_reporting(E_ALL);

$groupIDs = filter_input(INPUT_POST, 'groupIDs'); // 'X,Y,Z,...'

$mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
if ($mysqli -> connect_errno) {
    print($mysqli->error);
    exit;
}
$db_selected = $mysqli->select_db('rabbitplot');
if (!$db_selected){
    die('データベース選択失敗です。' . $mysqli->error);
}
$result = $mysqli->query("SELECT * FROM `group` WHERE groupID IN(${groupIDs})");
if (!$result) {
    die('クエリーが失敗しました。' . $mysqli->error);
}
$typeDic = array(
    3=>'int',
    252=>'string',
    253=>'string',
    254=>'string'
);
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
$mysqli->close();

print('[' . join(',', $res) . ']');