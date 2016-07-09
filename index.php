<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../password.php';
require_logined_session();

header('Content-Type: text/html; charset=UTF-8');

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
            $res[] = intval($groupID);
        }
        $stmt->close();
    }
    $mysqli->close();
    return $res;
}

?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <style>
    my-user-info, my-search, my-calendar {
        border: 2px solid #888;
        border-radius: 3px;
        padding: 10px;
        margin: 10px 0;
        display: block;
    }
    </style>
  </head>
  <body>
    <my-calendar></my-calendar>
    <my-search></my-search>
    <my-user-info></my-user-info>
    <script src="node_modules/parsimmon/build/parsimmon.browser.min.js"></script>
    <script src="https://wzrd.in/standalone/superagent@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/riot/2.4.1/riot.min.js"></script>
    <script src="https://npmcdn.com/riotcontrol@0.0.3"></script>
    <script src="js/general.js"></script>
    <script src="js/calendar.js"></script>
    <script src="js/shuntingyard.js"></script>
    <script src="js/parser.js"></script>
    <script src="js/group.js"></script>
    <script src="js/user.js"></script>
    <script src="js/events.js"></script>
    <script src="js/api.js"></script>
    <script src="tags/calendar.js"></script>
    <script src="tags/search.js"></script>
    <script src="tags/userinfo.js"></script>
    <script>
        var userStore = new User(<?php
$screen_name = $_SESSION['screen_name'];
$userInfo = getUserInfoBy($screen_name);
$userID = $userInfo['userID'];
$name = $userInfo['name'];
$follow_relation = getFollowRelation($userID);

print("'${screen_name}', '${name}', [" . join(', ', $follow_relation) . "]");
?>);
        var eventsStore = new Events();
        var groupsStore = new Groups();
        RiotControl.addStore(userStore);
        RiotControl.addStore(eventsStore);
        RiotControl.addStore(groupsStore);
        riot.mount('*');
    </script>
  </body>
</html>