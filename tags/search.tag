<my-search>
    <h2>グループを探す</h2>
    <input name="keyword" type="text" value="東北大学"><button onclick={search}>検索</button>
    <ul if={searching == false}>
        <li each={id in result}>{groups[id].name}(@{groups[id].screenName})<button onclick={follow} if={id >= 10}>フォロー</button></li>
    </ul>
    <span if={searching == true}>検索中...</span>
    <h2>グループを作成する</h2>
    <my-group-form></my-group-form>
    <style scoped>
    </style>
    <script>
    const searchStore = new class SearchStore {
        get searching() {return this._searching;}
        get result() {return this._result;}
        constructor() {
            riot.observable(this);
            this._searching = false;
            this._result = [];
            this.fields = ['searching', 'result'];
            this.actionTypes = {
                changed: 'search_store_changed'
            };
            this.on('search', keyword => {
                if (keyword !== '') {
                    this._searching = true;
                    this._result = [];
                    RiotControl.trigger(this.actionTypes.changed);
                    API.search(keyword).then(groupIDs => {
                        this._result = groupIDs;
                        groupsStore.fetchGroups(this.result).then(() => {
                            this._searching = false;
                            RiotControl.trigger(this.actionTypes.changed);
                        });
                    });
                } else {
                    this._searching = false;
                    this._result = [];
                    RiotControl.trigger(this.actionTypes.changed);
                }
            });
        }
    };
    RiotControl.addStore(searchStore);
    this.groups = groupsStore.groupList;
    searchStore.fields.forEach(item => {
        // this['year'] = searchStore['year']();
        this[item] = searchStore[item];
    });

    const action = new class Action {
        constructor() {}
        follow(id) {
            RiotControl.trigger('follow', id);
        }
        search(keyword) {
            RiotControl.trigger('search', keyword);
        }
    };
    RiotControl.on(searchStore.actionTypes.changed, () => {
        searchStore.fields.forEach(item => {
            // this['year'] = searchStore['year']();
            this[item] = searchStore[item];
        });
        this.update();
    });
    RiotControl.on(groupsStore.actionTypes.changed, () => {
        this.groups = groupsStore.groupList;
        this.update();
    });

    this.search = () => {
        action.search(this.keyword.value);
    };
    this.follow = (e) => {
        action.follow(e.item.id);
    };
</my-search>