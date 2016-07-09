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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/riot/2.4.1/riot+compiler.min.js"></script>
    <script src="https://npmcdn.com/riotcontrol@0.0.3"></script>
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
                    <tr><td colspan="7"><button onclick={previousMonth}>&lt;-</button>{year}年{month + 1}月<button onclick={nextMonth}>-&gt;</button></td></tr>
                    <tr><th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></tr>
                </thead>
                <tbody>
                    <tr each={week in calendar.weeks}>
                        <td each={date in week} onclick={select} class={booked: isBooked[date === '' ? 0 : date]}>{date}</td>
                    </tr>
                </tbody>
            </table>
            {month + 1}月{selected.date}日
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
            var calendarStore = new function() {
                var self = this;
                var eventList = filterByID(filterByID(eventsStore.getEventList(), userStore.getFollowList()), userStore.getHiddenGroup(), true);
                
                riot.observable(this);
                this.year = new Date().getFullYear();
                this.month = new Date().getMonth();
                this.selected = {date: new Date().getDate()};
                this.calendar = new Calendar(this.year, this.month);
                this.events = getEvents(new Date(this.year, this.month, this.selected.date));
                this.bookedList = getBookedList(this.calendar.weeks);
                this.fields = ['year', 'month', 'selected', 'calendar', 'bookedList', 'events'];
                this.previousMonth = function() {
                    this.month--;
                    if (this.month < 0) {
                        this.year--;
                        this.month += 12;
                    }
                    this.selected = {date: 1};
                    this.calendar = new Calendar(this.year, this.month);
                    this.events = getEvents(new Date(this.year, this.month, this.selected.date));
                    RiotControl.trigger(this.actionTypes.changed);
                };
                this.nextMonth = function() {
                    this.month++;
                    if (this.month > 11) {
                        this.year++;
                        this.month -= 12;
                    }
                    this.selected = {date: 1};
                    this.calendar = new Calendar(this.year, this.month);
                    this.events = getEvents(new Date(this.year, this.month, this.selected.date));
                    RiotControl.trigger(this.actionTypes.changed);
                };
                this.select = function(date) {
                    if (date !== ''){
                        this.selected.date = date;
                        this.events = getEvents(new Date(this.year, this.month, this.selected.date));
                        RiotControl.trigger(this.actionTypes.changed);
                    }
                };
                this.fields.forEach(function(item) {
                    // self['getYear'] = function() {return self['year']};
                    self['get' + item.charAt(0).toUpperCase() + item.slice(1)] = function() {return self[item]};
                });
                this.actionTypes = {
                    changed: 'calendar_store_changed'
                };
                this.on('previousMonth', this.previousMonth.bind(this));
                this.on('nextMonth', this.nextMonth.bind(this));
                this.on('select', this.select.bind(this));
                this.on('follow unfollow show hide ' + eventsStore.actionTypes.changed, function() {
                    eventList = filterByID(filterByID(eventsStore.getEventList(), userStore.getFollowList()), userStore.getHiddenGroup(), true);
                    self.events = getEvents(new Date(self.year, self.month, self.selected.date));
                    self.bookedList = getBookedList(self.calendar.weeks);
                    RiotControl.trigger(self.actionTypes.changed);
                });
                function getEvents(date) {
                    return filter(eventList, date);
                }
                function getBookedList(weeks) {
                    var res = [];
                    res[0] = false;
                    for (var i = 0, _i = weeks.length; i < _i; i++) {
                        for (var j = 0, _j = weeks[i].length; j < _j; j++) {
                            if (weeks[i][j] !== '') res[weeks[i][j]] = getEvents(new Date(self.year, self.month, weeks[i][j])).length > 0;
                        }
                    }
                    return res;
                }
            };
            RiotControl.addStore(calendarStore);
            var action = new function() {
                this.previousMonth = function() {
                    RiotControl.trigger('previousMonth');
                };
                this.nextMonth = function() {
                    RiotControl.trigger('nextMonth');
                };
                this.select = function(date) {
                    RiotControl.trigger('select', date);
                };
            };
            var self = this;
            calendarStore.fields.forEach(function(item) {
                // self['year'] = calendarStore['getYear']();
                self[item] = calendarStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
            });
            this.isBooked = calendarStore.getBookedList();

            previousMonth() {
                action.previousMonth();
            }
            nextMonth() {
                action.nextMonth();
            }
            select(e) {
                action.select(e.item.date);
            }
            RiotControl.on(calendarStore.actionTypes.changed, function() {
                calendarStore.fields.forEach(function(item) {
                    self[item] = calendarStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
                });
                self.isBooked = calendarStore.getBookedList();
                self.update();
            });
        </my-calendar>
    </script>
    <script type="riot/tag">
        <my-search>
            <h2>グループを探す</h2>
            <input name="keyword" type="text" value="東北大学"><button onclick={search}>検索</button>
            <ul if={searching == false}>
                <li each={id in result}>{groups[id].name}<span if={id >= 10}>(@{groups[id].screenName})</span><button onclick={follow} if={id >= 10}>フォロー</button></li>
            </ul>
            <span if={searching == true}>検索中...</span>
            <style scoped>
            </style>
            <script>
            var self = this;
            var searchStore = new function() {
                var self = this;
                riot.observable(this);
                this.searching = false;
                this.result = [];
                this.fields = ['searching', 'result'];
                this.actionTypes = {
                    changed: 'search_store_changed'
                };
                this.fields.forEach(function(item) {
                    // self['getYear'] = function() {return self['year']};
                    self['get' + item.charAt(0).toUpperCase() + item.slice(1)] = function() {return self[item]};
                });
                this.on('search', function(keyword) {
                    this.searching = true;
                    this.result = [];
                    RiotControl.trigger(self.actionTypes.changed);
                    API.search(keyword).then(function(groupIDs) {
                        self.result = groupIDs;
                        groupsStore.fetchGroups(self.result).then(function() {
                            self.searching = false;
                            RiotControl.trigger(self.actionTypes.changed);
                        });
                    });
                })
            };
            RiotControl.addStore(searchStore);
            this.groups = groupsStore.getGroupList();
            searchStore.fields.forEach(function(item) {
                // self['year'] = searchStore['getYear']();
                self[item] = searchStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
            });

            var action = new function() {
                this.follow = function(id) {
                    RiotControl.trigger('follow', id);
                };
                this.search = function(keyword) {
                    RiotControl.trigger('search', keyword);
                };
            };
            RiotControl.on(searchStore.actionTypes.changed, function() {
                searchStore.fields.forEach(function(item) {
                    // self['year'] = searchStore['getYear']();
                    self[item] = searchStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
                });
                self.update();
            });
            RiotControl.on(groupsStore.actionTypes.changed, function() {
                self.groups = groupsStore.getGroupList();
                self.update();
            });

            search() {
                var keyword = this.keyword.value;
                action.search(keyword);
            }
            follow(e) {
                action.follow(e.item.id);
            }
        </my-search>
    </script>
    <script type="riot/tag">
        <my-user-info>
            {name}(@{screenName})<a href="./logout.php?token=<?=h(generate_token())?>">ログアウト</a><br>
            フォローしているグループ
            <ul>
                <li each={id in followList}>
                    {groups[id].name}<span if={id >= 10}>(@{groups[id].screenName})</span>
                    <button onclick={unfollow} if={id >= 10}>フォロー解除</button>
                    <button onclick={hiddenGroup.indexOf(id) === -1 ? hide : show}>{hiddenGroup.indexOf(id) === -1 ? '非' : ''}表示にする</button>
                </li>
            </ul>
            <style scoped>
            </style>
            <script>
            var self = this;
            this.name = userStore.getName();
            this.screenName = userStore.getScreenName();
            this.followList = userStore.getFollowList();
            this.hiddenGroup = userStore.getHiddenGroup();
            this.groups = groupsStore.getGroupList();
            RiotControl.on('follow unfollow hide show ' + groupsStore.actionTypes.changed, function() {
                self.followList = userStore.getFollowList();
                self.hiddenGroup = userStore.getHiddenGroup();
                self.update();
            });
            RiotControl.on(groupsStore.actionTypes.changed, function() {
                self.name = userStore.getName();
                self.screenName = userStore.getScreenName();
                self.followList = userStore.getFollowList();
                self.hiddenGroup = userStore.getHiddenGroup();
                self.groups = groupsStore.getGroupList();
                self.update();
            });
            var action = new function() {
                this.unfollow = function(id) {
                    RiotControl.trigger('unfollow', id);
                };
                this.hide = function(id) {
                    RiotControl.trigger('hide', id);
                };
                this.show = function(id) {
                    RiotControl.trigger('show', id);
                };
            };
            unfollow(e) {
                action.unfollow(e.item.id);
            }
            hide(e) {
                action.hide(e.item.id);
            }
            show(e) {
                action.show(e.item.id);
            }
        </my-user-info>
    </script>
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