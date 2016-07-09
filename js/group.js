function Groups() {
    var self = this;
    riot.observable(this);
    this.groupList = [];
    API.getGroups(userStore.followList).then(function(value) {
        for (var i = 0, _i = value.length; i < _i; i++) {
            self.groupList[value[i].groupID] = value[i];
        }
        RiotControl.trigger(self.actionTypes.changed);
    });
    this.on('follow', this.fetchGroup.bind(this));
}
Groups.prototype.fetchGroup = function(groupID) {
    return this.fetchGroups([groupID]);
};
Groups.prototype.fetchGroups = function(groupIDs) {
    var self = this;
    var missing = groupIDs.filter(function(id) {
        return !self.groupList[id];
    });
    if (missing.length > 0) {
        return API.getGroups(missing).then(function(value) {
            for (var i = 0, _i = value.length; i < _i; i++) {
                self.groupList[value[i].groupID] = value[i];
            }
            RiotControl.trigger(self.actionTypes.changed);
        });
    } else {
        return Promise.resolve();
    }
};
Groups.prototype.getGroupList = function() {
    return this.groupList;
};
Groups.prototype.actionTypes = {
    changed: 'groups_store_changed'
};