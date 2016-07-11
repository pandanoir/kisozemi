<?php

require_once __DIR__ . '/../../password.php';
function getUserInfoBy($screen_name) {
    $mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
    if ($mysqli -> connect_errno) {
        print($mysqli->error);
        exit;
    }
    $db_selected = $mysqli->select_db('rabbitplot');
    if (!$db_selected){
        die('データベース選択失敗です。' . $mysqli->error);
    }
    if ($stmt = $mysqli->prepare('SELECT userID, screen_name, name FROM user WHERE screen_name = ?')) {
        $userinfo = array();
        $stmt->bind_param('s', $screen_name);
        $stmt->execute();
        $stmt->bind_result($userinfo['userID'], $userinfo['screen_name'], $userinfo['name']);
        $stmt->fetch();
        $stmt->close();
    }
    $mysqli->close();
    return $userinfo;
}
function getFollowRelation($userID) {
    $mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
    if ($mysqli -> connect_errno) {
        print($mysqli->error);
        exit;
    }
    $db_selected = $mysqli->select_db('rabbitplot');
    if (!$db_selected){
        die('データベース選択失敗です。' . $mysqli->error);
    }
    if ($stmt = $mysqli->prepare('SELECT groupID FROM follow_relation WHERE userID = ? ORDER BY groupID')){
        $res = array();
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $stmt->bind_result($groupID);
        while ($stmt->fetch()) {
            $res[] = $groupID;
        }
        $stmt->close();
    }
    $mysqli->close();
    return $res;
}
function getUserClassArguments($screen_name) {
    $userInfo = getUserInfoBy($screen_name);
    $userID = $userInfo['userID'];
    $name = $userInfo['name'];
    $follow_relation = getFollowRelation($userID);

    return "'${screen_name}', '${name}', [" . join(', ', $follow_relation) . "]";
}