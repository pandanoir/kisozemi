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
            セレクタ: <input type="text" name="selector" value=""><button onclick={useGuide}>入力ガイドを使う</button><br>
            グループID: <input type="text" name="screen_name" value=""><br>
            <input type="hidden" name="userID" value="<?= h(getUserInfoBy($_SESSION['screen_name'])['screen_name']); ?>">
            フォローしているグループ
            <ul>
                <li each={group in groups} if={group.groupID != 0} onclick={selectGroup}>{group.name}<span if={group.screenName}>(@{group.screen_name})</span></li>
            </ul>
            <button onclick={submit}>作成</button><span>{message}</span>
            <script>
            this.unavailable = null;
            this.groups = groupsStore.groupList;
            this.message = '';
            const message = mes => {
                this.message = mes;
                this.update();
                setTimeout(function() {
                    this.message = '';
                    this.update();
                }, 5000);
            }
            const action = new function() {
                this.onGuide = function() {
                    RiotControl.trigger('on-guide');
                };
                this.selectGroup = function(screen_name) {
                    RiotControl.trigger('select-group', screen_name);
                };
            };
            RiotControl.on('select-group', screen_name => {
                this.screen_name.value = screen_name;
                this.update();
            });
            RiotControl.on(groupsStore.actionTypes.changed, _ => {
                this.groups = groupsStore.groupList;
                this.update();
            });
            useGuide() {
                action.onGuide();
            }
            selectGroup(e) {
                action.selectGroup(e.item.group.screenName);
            }
            submit() {
                const title = this.title.value;
                const selector = this.selector.value;
                const userID = this.userID.value;
                const screen_name = this.screen_name.value;
                if (userID !== '' && screen_name !=='' && selector !== '' && title !== '') {
                    const transformed = parseExpression(selector);
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
        </my-event-form>
    </script>
    <script type="riot/tag">
        <my-selector-guide if={shows}>
            設定したい日は<br>
            <div class="my-editor">
                <my-selector each={selector in selectors} selector={selector} onclick={clicked} if={selected == ''}></my-selector>
                <my-values selector={selected} if={selected != ''}></my-values><br>
            </div>
            RESULT: <input type="text" value="{result}" name="input-result"><br>
            {readableResult}
            <style scoped>
                .my-editor {
                    height: 30px;
                    padding: 12px 0;
                }
            </style>
            <script>
                this.selectors = ['year', 'month', 'date', 'day'];
                this.clicked = e => {
                    action.clicked(e.item.selector);
                };
                const inputResult = this['input-result'];
                const action = new function() {
                    this.clicked = selector => {
                        RiotControl.trigger('selector-clicked', selector)
                    };
                };
                const selectorGuideStore = new function() {
                    riot.observable(this);
                    this.selected = '';
                    this.shows = false;
                    this.results = [];
                    this.fields = ['selected', 'shows', 'results'];
                    Object.defineProperty(this, 'result', {
                        enumerable: true,
                        configurable: false,
                        get: _ => this.results.map(item => item[0] + ': ' + item[1]).join(' ')
                    });
                    this.actionTypes = {
                        changed: 'selector-guide-store-changed'
                    };
                    this.onGuide = () => {
                        this.shows = true;
                        RiotControl.trigger(this.actionTypes.changed);
                    };
                    this.valueBack = () => {
                        this.selected = '';
                        RiotControl.trigger(this.actionTypes.changed);
                    };
                    this.selectorClicked = selector => {
                        this.selected = selector;
                        RiotControl.trigger(this.actionTypes.changed);
                    };
                    this.finish = value => {
                        console.log(this.result, inputResult.value)
                        if (this.result === inputResult.value || confirm('RESULTが編集されています。変更を破棄し上書きしますか?')) {
                            this.results.push([this.selected, value]);
                            this.selected = '';
                            RiotControl.trigger(this.actionTypes.changed);
                        }
                    }
                    this.on('on-guide', this.onGuide.bind(this));
                    this.on('value-back', this.valueBack.bind(this));
                    this.on('selector-clicked', this.selectorClicked.bind(this));
                    this.on('finish', this.finish.bind(this));
                };
                selectorGuideStore.fields.forEach(el => this[el] = selectorGuideStore[el]);
                Object.defineProperty(this, 'result', {
                    enumerable: true,
                    configurable: false,
                    get: _ => selectorGuideStore.result
                });
                Object.defineProperty(this, 'readableResult', {
                    enumerable: true,
                    configurable: false,
                    get: _ => selectorGuideStore.results.map(item => {
                            let res = '';
                            if (/^\d+$/.test(item[1])) res += item[1];
                            else res += {sunday: '日', monday: '月', tuesday: '火', wednesday: '水', thursday: '木', friday: '金', saturday: '土', 'leap-year': 'うるう'}[item[1]];
                            res += {year: '年', month: '月', date: '日', day: '曜日'}[item[0]];
                            return res;
                        }).join(' かつ ')
                });

                RiotControl.addStore(selectorGuideStore);
                RiotControl.on(selectorGuideStore.actionTypes.changed, () => {
                    selectorGuideStore.fields.forEach(el => this[el] = selectorGuideStore[el]);
                    this.update();
                });
        </my-selector-guide>
    </script>
    <script type="riot/tag">
        <my-selector>
            {text}
            <style scoped>
            :scope {
                border: 1px solid #444;
                border-radius: 2px;
                margin: 0 3px;
                padding: 1px 5px;
                color: #444;
                cursor: pointer;
            }
            :scope:hover {
                background: #f9c;
            }
            </style>
            <script>
                const dic = {
                    'year': 'XX年',
                    'month': 'XX月',
                    'date': 'XX日',
                    'day': 'X曜日'
                };
                this.text = dic[opts.selector];
        </my-selector>
    </script>
    <script type="riot/tag">
        <my-values>
            <my-value></my-value><button onclick={finishClicked}>決定</button><button onclick={back}>戻る</button>
            <script>
                RiotControl.on('selector-clicked', () => riot.mount('my-value', 'my-' + opts.selector + '-value'));
                this.value = '';
                const valueStore = new ValueStore;
                const action = new function() {
                    this.back = () => {
                        RiotControl.trigger('value-back');
                    };
                    this.finishClicked = () => {
                        RiotControl.trigger('edit-finish');
                    };
                    this.finish = text => {
                        RiotControl.trigger('finish', text);
                    };
                };
                this.back = _ => action.back();
                this.finishClicked = _ => {
                    if (this.value !== '') action.finishClicked();
                }
                RiotControl.addStore(valueStore);
                valueStore.on('edit-finish', () => action.finish(this.value));
                valueStore.on(valueStore.actionTypes.changed, () => this.value = valueStore.value);
        </my-values>
    </script>
    <script type="riot/tag">
        <my-year-value>
            <input type="number" onkeyup={edit}>年 <button onclick={uru}>うるう年</button>
            <script>
                const action = new Action;
                action.uru = _ => {
                    RiotControl.trigger('edit-finish');
                };
                this.edit = e => {
                    action.edit(e.target.value);
                };
                this.uru = _ => {
                    action.edit('leap-year');
                    action.uru();
                }
                action.edit('');
        </my-year-value>
    </script>
    <script type="riot/tag">
        <my-month-value>
            <select onchange={edit}><option each={value in values}>{value}月</option></select>
            <script>
                this.values = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
                const action = new Action;
                this.edit = e => {
                    action.edit(e.target.value.slice(0, -1)); // XX月となっているので、"月"を削る
                };
                action.edit('1');
        </my-month-value>
    </script>
    <script type="riot/tag">
        <my-date-value>
            <select onchange={edit}><option each={value in values}>{value}日</option></select>
            <script>
                this.values = [
                    '1', '2', '3', '4', '5', '6', '7', '8', '9', '10',
                    '11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
                    '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'
                ];
                const action = new Action;
                this.edit = e => {
                    action.edit(e.target.value.slice(0, -1)); // XX日となっているから、"日"を削る
                };
                action.edit('1');
        </my-date-value>
    </script>
    <script type="riot/tag">
        <my-day-value>
            <select onchange={edit}><option each={value in values}>{value}曜日</option></select>
            <script>
                this.values = ['日', '月', '火', '水', '木', '金', '土'];
                const action = new Action;
                this.edit = e => {
                    const dic = {
                        '日': 'sunday',
                        '月': 'monday',
                        '火': 'tueday',
                        '水': 'wednesday',
                        '木': 'thursday',
                        '金': 'friday',
                        '土': 'saturday'
                    };
                    action.edit(dic[e.target.value.slice(0, 1)]);
                };
                action.edit('sunday');
        </my-day-value>
    </script>
    <script>
        const userStore = new User(<?= getUserClassArguments($_SESSION['screen_name']); ?>);
        const groupsStore = new Groups();
        class ValueStore {
            constructor(){
                riot.observable(this);
                this.value = '';
                this.actionTypes = {changed: 'value-store-changed'};
                this.on('input-edit', text => {
                    this.value = text;
                    RiotControl.trigger(this.actionTypes.changed);
                });
            }
        }
        class Action {
            edit(text) {
                RiotControl.trigger('input-edit', text);
            }
            finish(text) {
                RiotControl.trigger('finish', text)
            }
        }
        RiotControl.addStore(groupsStore);
        riot.mount('*');
    </script>
  </body>
</html>