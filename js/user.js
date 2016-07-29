class User {
    get name() {return this._name}
    get screenName() {return this._screenName}
    get followList() {return this._followList}
    get hiddenGroup() {return this._hiddenGroup}
    constructor(screenName, name, followList) {
        riot.observable(this);
        this._followList = [0, 1];
        if (followList && typeof followList.length === 'number') {
            this._followList = this._followList.concat(followList);
        }
        this._hiddenGroup = [];
        this._screenName = screenName;
        this._name = name;
        this.on('follow', this.follow.bind(this));
        this.on('unfollow', this.unfollow.bind(this));
        this.on('show', this.show.bind(this));
        this.on('hide', this.hide.bind(this));
    }
    follow(groupID) {
        if (binarySearch(this.followList, groupID) === -1) {
            request.post('user/follow')
                .type('form')
                .send({groupID: '' + groupID, 'screen_name': this.screenName})
                .end(function(err, res) {console.log(res.text)});
            this._followList.push(groupID);
            this._followList.sort((a, b) => a - b);
        }
    }
    unfollow(groupID) {
        this._followList = this.followList.filter(_ => _ !== groupID);
        request.post('user/unfollow')
            .type('form')
            .send({groupID: groupID, screen_name: this.screenName})
            .end(function(err, res) {console.log(res.text)});
    }
    hide(groupID) {
        if (binarySearch(this.hiddenGroup, groupID) === -1) {
            this._hiddenGroup.push(groupID);
            this._hiddenGroup.sort((a, b) => a - b);
        }
    }
    show(groupID) {
        this._hiddenGroup = this.hiddenGroup.filter(_ => _ !== groupID);
    }
}