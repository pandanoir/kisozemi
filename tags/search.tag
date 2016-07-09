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
            if (keyword !== '') {
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
            } else {
                this.searching = false;
                this.result = [];
                RiotControl.trigger(self.actionTypes.changed);
            }
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
