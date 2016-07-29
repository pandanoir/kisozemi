<my-group-form>
    グループ名: <input type="text" name="name" value="東北大学計算機科学研究会"><br>
    ID: <input type="text" name="screen_name" value="tohoku_3k"><button onclick={checkID}>使用可能かチェック</button>
    <span if={unavailable === true} style="color: red">使用できません</span>
    <span if={unavailable === false} style="color: green">使用できます</span><br>
    (注意: IDは後から変更できません)<br>
    IDは英数字、アンダーバーのみ使用できます。<br>
    <button onclick={submit}>作成</button>
    <span if={showsMessage}>{message}</span>
    <script>
    const store = new class Store {
        get unavailable() {return this._unavailable;}
        constructor() {
            riot.observable(this);
            this._unavailable = null;
            this.actionTypes = {
                changed: 'store_changed'
            };
            this.on('check', unavailable => {
                this._unavailable = unavailable;
                RiotControl.trigger(this.actionTypes.changed);
            });
        }
    };
    const action = new class Action {
        constructor() {}
        submit(name, screen_name) {
            if (name !== '' && screen_name !=='') {
                request.post('create/_group_')
                    .type('form')
                    .send({screen_name, name})
                    .end((err, res) => {
                        console.log(res);
                        if (res.text === 'succeeded') {
                            RiotControl.trigger('creating_group_succeeded');
                        } else {
                            RiotControl.trigger('creating_group_failed');
                        }
                    })
            }
            RiotControl.trigger('submit');
        }
        checkID(screen_name) {
            if (screen_name !== '') {
                request.post('check/groupID')
                    .type('form')
                    .send({screen_name})
                    .end((err, res) => {
                        console.log(res);
                        RiotControl.trigger('check', res.text !== 'available');
                    });
            } else {
                RiotControl.trigger('check', true);
            }
        }
    };
    RiotControl.addStore(store);
    this.unavailable = store.unavailable;
    this.showsMessage = false;
    this.message = '';
    RiotControl.on(store.actionTypes.changed, () => {
        this.unavailable = store.unavailable;
        this.update();
    });
    RiotControl.on('creating_group_succeeded', () => {
        this.showsMessage = true;
        this.message = '成功しました';
        this.update();
        setTimeout(() => {
            this.showsMessage = false;
            this.update();
        }, 5000);
    });
    RiotControl.on('creating_group_failed', () => {
        this.showsMessage = true;
        this.message = '失敗しました';
        this.update();
        setTimeout(() => {
            this.showsMessage = false;
            this.update();
        }, 5000);
    });
    this.submit = () => {
        action.submit(this.name.value, this.screen_name.value);
    };
    this.checkID = () => {
        action.checkID(this.screen_name.value);
    };
</my-group-form>