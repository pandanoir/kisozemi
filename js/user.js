class User {
    constructor(screenName, name, followList) {
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
    getName() {
        return this.name;
    }
    getScreenName() {
        return this.screenName;
    }
    getFollowList() {
        return this.followList;
    }
    getHiddenGroup() {
        return this.hiddenGroup;
    }
    follow(groupID) {
        if (binarySearch(this.followList, groupID) === -1) {
            request.post('user/follow')
                .type('form')
                .send({groupID: '' + groupID, 'screen_name': this.screenName})
                .end(function(err, res) {console.log(res.text)});
            this.followList.push(groupID);
            this.followList.sort(function(a, b) {return a - b});
        }
    }
    unfollow(groupID) {
        this.followList = this.followList.filter(function(_) {return _ !== groupID});
        request.post('user/unfollow')
            .type('form')
            .send({groupID: groupID, screen_name: this.screenName})
            .end(function(err, res) {console.log(res.text)});
    }
    hide(groupID) {
        if (binarySearch(this.hiddenGroup, groupID) === -1) {
            this.hiddenGroup.push(groupID);
            this.hiddenGroup.sort(function(a, b) {return a - b});
        }
    }
    show(groupID) {
        this.hiddenGroup = this.hiddenGroup.filter(function(_) {return _ !== groupID});
    }
}