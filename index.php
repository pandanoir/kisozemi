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
    $result = $mysqli->query("SELECT userID, screen_name, name FROM user WHERE screen_name = '${screen_name}'");
    if (!$result) {
        die('クエリーが失敗しました。' . $mysqli->error);
    }
    $userinfo = $result->fetch_assoc();
    $result->free();
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
    $result = $mysqli->query("SELECT groupID FROM follow_relation WHERE userID = ${userID} ORDER BY groupID");
    if (!$result) {
        die('クエリーが失敗しました。' . $mysqli->error);
    }
    $res = array();
    while ($row = $result->fetch_assoc()) {
        $res[] = intval($row['groupID']);
    }
    $result->free();
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/riot/2.4.1/riot+compiler.min.js"></script>
    <script src="js/general.js"></script>
    <script src="js/calendar.js"></script>
    <script src="js/shuntingyard.js"></script>
    <script src="js/parser.js"></script>
    <script src="js/group.js"></script>
    <script src="js/user.js"></script>
    <script src="js/events.js"></script>
    <script src="js/api.js"></script>
    <script type="riot/tag">
        <my-calendar>
            <table>
                <thead>
                    <tr><td colspan="7"><button onclick={previousMonth}>&lt;-</button>{this.year}年{this.month + 1}月<button onclick={nextMonth}>-&gt;</button></td></tr>
                    <tr><th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></tr>
                </thead>
                <tbody>
                    <tr each={week in calendar.weeks}><td each={date in week} onclick={select} class={booked: isBooked(date)}>{date}</td></tr>
                </tbody>
            </table>
            {this.month + 1}月{this.selected.date}日
            <ul>
                <li each = {event in events}>{event.title}</li>
                <li if = {events.length === 0}>予定なし</li>
            </ul>
            <style scoped>
                .header { text-align: center; }
                table { text-align: center; }
                .booked { background: pink; }
                button {
                    margin: 0 10px;
                    cursor: pointer;
                    border: 1px solid #444;
                    padding: 2px 10px;
                }
                button:first-of-type { border-radius: 3px 0 0 3px; }
                button:last-of-type { border-radius: 0 3px 3px 0; }
                table td, table th{
                    padding: 10px;
                    background: #eee;
                }
            </style>
            <script>
            var self = this;
            var eventList = filterByID(filterByID(opts.events, opts.user.followList), opts.user.hiddenGroup, true);
            this.on('select', function() {
                this.events = getEvents(new Date(this.year, this.month, this.selected.date));
            });
            opts.listener.on('follow update_event_list', function() {
                eventList = filterByID(filterByID(opts.events, opts.user.followList), opts.user.hiddenGroup, true);
                self.update();
            });
            this.year = new Date().getFullYear();
            this.month = new Date().getMonth();
            this.selected = {date: new Date().getDate()};
            this.follow = opts.user.followList;
            this.calendar = new Calendar(this.year, this.month);
            this.trigger('select');
            previousMonth() {
                this.month--;
                if (this.month < 0) {
                    this.year--;
                    this.month += 12;
                }
                this.selected = {date: 1};
                this.calendar = new Calendar(this.year, this.month);
                this.trigger('select');
            }
            nextMonth() {
                this.month++;
                if (this.month > 11) {
                    this.year++;
                    this.month -= 12;
                }
                this.selected = {date: 1};
                this.calendar = new Calendar(this.year, this.month);
                this.trigger('select');
            }
            function getEvents(date) {
                return filter(eventList, date);
            }
            select(e) {
                if (e.item.date !== '') {
                    this.selected.date = e.item.date;
                    this.trigger('select');
                }
            }
            isBooked(date) {
                if (date === '') return false;
                return getEvents(new Date(this.year, this.month, date)).length > 0;
            }
        </my-calendar>
    </script>
    <script type="riot/tag">
        <my-search>
            <h2>グループを探す</h2>
            <input name="keyword" type="text" value="東北大学"><button onclick={search}>検索</button>
            <ul if={searching == false}>
                <li each={id in result}>{groups[id].name}<button onclick={follow}>フォロー</button></li>
            </ul>
            <span if={searching == true}>検索中...</span>
            <style scoped>
            </style>
            <script>
            var self = this;
            this.searching = false;
            this.groups = opts.groups;
            this.result = [];
            search() {
                var keyword = this.keyword.value;
                this.searching = true;
                this.result = [];
                API.search(keyword).then(function(groupIDs) {
                    self.result = groupIDs;
                    var missing = [];
                    for (var i = 0, _i = groupIDs.length; i < _i; i++) {
                        if (!opts.groups[groupIDs[i]]) {
                            missing.push(groupIDs[i]);
                        }
                    }
                    if (missing.length > 0) {
                        API.getGroups(missing).then(function(value) {
                            for (var i = 0, _i = value.length; i < _i; i++) {
                                opts.groups[value[i].groupID] = value[i];
                            }
                            self.searching = false;
                            self.update();
                        });
                    } else {
                        self.searching = false;
                        self.update();
                    }
                });
            }
            follow(e) {
                opts.user.follow(e.item.groupID);
                opts.listener.trigger('follow', e.item.groupID);
            }
        </my-search>
    </script>
    <script type="riot/tag">
        <my-user-info>
            {user.name}(@{user.screenName})<a href="./logout.php?token=<?=h(generate_token())?>">ログアウト</a><br>
            フォローしているグループ
            <ul>
                <li each={id in user.followList}>
                    {groups[id].name}
                    <button onclick={unfollow} if={id >= 10}>フォロー解除</button>
                    <button onclick={user.hiddenGroup.indexOf(id) === -1 ? hidden : show}>{user.hiddenGroup.indexOf(id) === -1 ? '非' : ''}表示にする</button>
                </li>
            </ul>
            <style scoped>
            </style>
            <script>
            var self = this;
            this.user = opts.user;
            this.groups = opts.groups;
            opts.listener.on('follow update_group_list', function() {
                self.update();
            })
            search() {
                var keyword = this.keyword.value;
                this.result = opts.groups.filter(function(item) {return item.name.indexOf(keyword) !== -1;});
            }
            unfollow(e) {
                opts.user.unfollow(e.item.id);
                opts.listener.trigger('follow');
            }
            hidden(e) {
                opts.user.hide(e.item.id);
                opts.listener.trigger('follow');
            }
            show(e) {
                opts.user.show(e.item.id);
                opts.listener.trigger('follow');
            }
        </my-user-info>
    </script>
    <script>
    (function() {
        var option = {};
        option.user = new User(<?php
$screen_name = $_SESSION['screen_name'];
$userInfo = getUserInfoBy($screen_name);
$userID = $userInfo['userID'];
$name = $userInfo['name'];
$follow_relation = getFollowRelation($userID);

print("'${screen_name}', '${name}', [" . join(', ', $follow_relation) . "]");
?>);
        option.listener = riot.observable();
        option.events = [];
        option.groups = [];
        option.listener.on('follow', function(groupID) {
            if (groupID) {
                API.getEvents([groupID]).then(function(value) {
                    option.events.push.apply(option.events, value);
                    option.listener.trigger('update_event_list');
                });
                API.getGroups([groupID]).then(function(value) {
                    for (var i = 0, _i = value.length; i < _i; i++) {
                        option.groups[value[i].groupID] = value[i];
                    }
                    option.listener.trigger('update_group_list');
                });
            }
        });
        API.getGroups(option.user.followList).then(function(value) {
            for (var i = 0, _i = value.length; i < _i; i++) {
                option.groups[value[i].groupID] = value[i];
            }
            option.listener.trigger('update_group_list');
        });
        API.getEvents(option.user.followList).then(function(value) {
            option.events.push.apply(option.events, value);
            option.listener.trigger('update_event_list');
        });
        riot.mount('*', option);

    })();
    </script>
  </body>
</html>