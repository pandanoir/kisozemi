function User(screenName, name, followList) {
    riot.observable(this);
    this.followList = [0, 1];
    if (followList && typeof followList.length === 'number') {
        this.followList = this.followList.concat(followList);
    }
    this.hiddenGroup = [];
    this.id = 10000; // temp
    this.screenName = screenName;
    this.name = name;
    this.on('follow', this.follow.bind(this));
    this.on('unfollow', this.unfollow.bind(this));
    this.on('show', this.show.bind(this));
    this.on('hide', this.hide.bind(this));
}
User.prototype.getName = function() {
    return this.name;
};
User.prototype.getScreenName = function() {
    return this.screenName;
};
User.prototype.getFollowList = function() {
    return this.followList;
};
User.prototype.getHiddenGroup = function() {
    return this.hiddenGroup;
};
User.prototype.follow = function(groupID) {
    if (binarySearch(this.followList, groupID) === -1) {
        request.post('follow.php')
            .type('form')
            .send({groupID: '' + groupID, 'screen_name': this.screenName})
            .end(function(err, res) {console.log(res.text)});
        this.followList.push(groupID);
        this.followList.sort(function(a, b) {return a - b});
    }
}
User.prototype.unfollow = function(groupID) {
    this.followList = this.followList.filter(function(_) {return _ !== groupID});
    request.post('unfollow.php')
        .type('form')
        .send({groupID: groupID, screen_name: this.screenName})
        .end(function(err, res) {console.log(res.text)});
}
User.prototype.hide = function(groupID) {
    if (binarySearch(this.hiddenGroup, groupID) === -1) {
        this.hiddenGroup.push(groupID);
        this.hiddenGroup.sort(function(a, b) {return a - b});
    }
}
User.prototype.show = function(groupID) {
    this.hiddenGroup = this.hiddenGroup.filter(function(_) {return _ !== groupID});
}