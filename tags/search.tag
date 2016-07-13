<my-search>
    <h2>グループを探す</h2>
    <input name="keyword" type="text" value="東北大学"><button onclick={search}>検索</button>
    <ul if={searching == false}>
        <li each={id in result}>{groups[id].name}(@{groups[id].screenName})<button onclick={follow} if={id >= 10}>フォロー</button></li>
    </ul>
    <span if={searching == true}>検索中...</span>
    <a href="./createGroup.html" target="_blank">グループを作成する</a>
    <style scoped>
    </style>
    <script>
    const searchStore = new function() {
        riot.observable(this);
        this.searching = false;
        this.result = [];
        this.fields = ['searching', 'result'];
        this.actionTypes = {
            changed: 'search_store_changed'
        };
        this.fields.forEach(item => {
            // this['getYear'] = function() {return this['year']};
            this['get' + item.charAt(0).toUpperCase() + item.slice(1)] = function() {return this[item]};
        });
        this.on('search', keyword => {
            if (keyword !== '') {
                this.searching = true;
                this.result = [];
                RiotControl.trigger(this.actionTypes.changed);
                API.search(keyword).then(groupIDs => {
                    this.result = groupIDs;
                    groupsStore.fetchGroups(this.result).then(() => {
                        this.searching = false;
                        RiotControl.trigger(this.actionTypes.changed);
                    });
                });
            } else {
                this.searching = false;
                this.result = [];
                RiotControl.trigger(this.actionTypes.changed);
            }
        })
    };
    RiotControl.addStore(searchStore);
    this.groups = groupsStore.getGroupList();
    searchStore.fields.forEach(item => {
        // this['year'] = searchStore['getYear']();
        this[item] = searchStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
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
            // this['year'] = searchStore['getYear']();
            this[item] = searchStore['get' + item.charAt(0).toUpperCase() + item.slice(1)]();
        });
        this.update();
    });
    RiotControl.on(groupsStore.actionTypes.changed, () => {
        this.groups = groupsStore.getGroupList();
        this.update();
    });

    this.search = () => {
        action.search(this.keyword.value);
    };
    this.follow = (e) => {
        action.follow(e.item.id);
    };
</my-search>