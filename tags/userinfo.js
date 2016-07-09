riot.tag2('my-user-info', '{name}(@{screenName})<a href="./logout.php?token=<?=h(generate_token())?>">ログアウト</a><br> フォローしているグループ <ul> <li each="{id in followList}"> {groups[id].name}<span if="{id >= 10}">(@{groups[id].screenName})</span> <button onclick="{unfollow}" if="{id >= 10}">フォロー解除</button> <button onclick="{hiddenGroup.indexOf(id) === -1 ? hide : show}">{hiddenGroup.indexOf(id) === -1 ? \'非\' : \'\'}表示にする</button> </li> </ul> <script>', '', '', function(opts) {
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
    this.unfollow = function(e) {
        action.unfollow(e.item.id);
    }.bind(this)
    this.hide = function(e) {
        action.hide(e.item.id);
    }.bind(this)
    this.show = function(e) {
        action.show(e.item.id);
    }.bind(this)
});
