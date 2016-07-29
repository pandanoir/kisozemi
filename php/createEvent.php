<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';
require_logined_session();
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title></title>
    <base href="../">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <style type="text/css">
    my-event-form, my-selector-guide {
        display: block;
    }
    </style>
  </head>
  <body>
    <h1>イベントを作成</h1>
    <my-event-form></my-event-form>
    <my-selector-guide></my-selector-guide>
    <script src="node_modules/parsimmon/build/parsimmon.browser.min.js"></script>
    <script src="https://wzrd.in/standalone/superagent@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/riot/2.4.1/riot+compiler.min.js"></script>
    <script src="https://npmcdn.com/riotcontrol@0.0.3"></script>
    <script src="js/general.js"></script>
    <script src="js/api.js"></script>
    <script src="js/shuntingyard.js"></script>
    <script src="js/parser.js"></script>
    <script src="js/user.js"></script>
    <script src="js/group.js"></script>
    <script type="riot/tag">
        <my-event-form>
            イベント名: <input type="text" name="title" placeholder="例: 花火大会" value=""><br>
            セレクタ: <input type="text" name="selector" value=""><!--button onclick={useGuide}>入力ガイドを使う</button--><br>
            グループID: <input type="text" name="screen_name" value=""><br>
            <input type="hidden" name="userID" value="<?= h(getUserInfoBy($_SESSION['screen_name'])['screen_name']); ?>">
            フォローしているグループ
            <ul>
                <li each={group in groups} if={group.groupID != 0} onclick={selectGroup}>{group.name}<span if={group.screenName}>(@{group.screen_name})</span></li>
            </ul>
            <button onclick={submit}>作成</button><span>{message}</span>
            <script>
            var self = this;
            this.unavailable = null;
            this.groups = groupsStore.groupList;
            this.message = '';
            var action = new function() {
                this.onGuide = function() {
                    RiotControl.trigger('on_guide');
                };
                this.selectGroup = function(screen_name) {
                    RiotControl.trigger('select_group', screen_name);
                };
            };
            RiotControl.on('select_group', function(screen_name) {
                self.screen_name.value = screen_name;
                self.update();
            });
            RiotControl.on(groupsStore.actionTypes.changed, function() {
                self.groups = groupsStore.groupList;
                self.update();
            });
            useGuide() {
                action.onGuide();
            }
            selectGroup(e) {
                action.selectGroup(e.item.group.screenName);
            }
            submit() {
                var title = this.title.value;
                var selector = this.selector.value;
                var userID = this.userID.value;
                var screen_name = this.screen_name.value;
                if (userID !== '' && screen_name !=='' && selector !== '' && title !== '') {
                    var transformed = parseExpression(selector);
                    if (transformed !== null) {
                        console.log(title, selector, transformed, screen_name, userID);
                        request.post('create/_event_')
                            .type('form')
                            .send({title: title, selector: selector, transformed: JSON.stringify(transformed), group_name: screen_name, user_name: userID})
                            .end(function(err, res) {
                                message(res.text === 'succeeded' ? '成功しました' : '失敗しました:' + res.text.replace(/failed: /, ''));
                            });
                    } else {
                        message('不正なセレクタです');
                    }
                }
            }
            function message(mes) {
                self.message = mes;
                self.update();
                setTimeout(function() {
                    self.message = '';
                    self.update();
                }, 5000);
            }
        </my-event-form>
    </script>
    <script type="riot/tag">
        <my-selector-guide if={shows}>
            設定したい日は
            <my-selector each={selector in selectors} selector={selector} onclick={clicked} if={selected == '' || selected == selector}></my-selector>
            <my-values selector={selected} if={selected != ''}></my-values>
            <button>+</button>
            <script>
                var self = this;
                this.selectors = ['year', 'month', 'date', 'day', 'range', 'not'];
                this.selected = '';
                this.shows = false;
                clicked(e) {
                    this.selected = e.item.selector;
                }
                RiotControl.on('on_guide', function() {
                    self.shows = true;
                    self.update();
                })
        </my-selector-guide>
    </script>
    <script type="riot/tag">
        <my-selector>
        {text}
        <script>
        var dic = {
            'year': '何年',
            'month': '何月',
            'date': '何日',
            'day': '何曜日',
            'range': '何日から',
            'not': '除く'
        };
        this.text = dic[opts.selector];
        </my-selector>
    </script>
    <script type="riot/tag">
        <my-values>
        <button each={value in values}>{value}</button>
        <script>
        var values = {
            year: [],
            month: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            date: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
            day: ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
        }
        </my-values>
    </script>
    <script type="riot/tag">
        <my-value onclick={clicked}>
        </my-value>
    </script>
    <script>
        var userStore = new User(<?= getUserClassArguments($_SESSION['screen_name']); ?>);
        var groupsStore = new Groups();
        RiotControl.addStore(groupsStore);
        riot.mount('*');
    </script>
  </body>
</html>