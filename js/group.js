class Groups {
    constructor() {
        const self = this;
        riot.observable(this);
        this.groupList = [];
        this.actionTypes = {
            changed: 'groups_store_changed'
        };
        API.getGroups(userStore.followList).then(function(value) {
            for (let i = 0, _i = value.length; i < _i; i++) {
                self.groupList[value[i].groupID] = value[i];
            }
            RiotControl.trigger(self.actionTypes.changed);
        });
        this.on('follow', this.fetchGroup.bind(this));
    }
    fetchGroup(groupID) {
        return this.fetchGroups([groupID]);
    }
    fetchGroups(groupIDs) {
        const self = this;
        const missing = groupIDs.filter(function(id) {
            return !self.groupList[id];
        });
        if (missing.length > 0) {
            return API.getGroups(missing).then(function(value) {
                for (let i = 0, _i = value.length; i < _i; i++) {
                    self.groupList[value[i].groupID] = value[i];
                }
                RiotControl.trigger(self.actionTypes.changed);
            });
        } else {
            return Promise.resolve();
        }
    }
    getGroupList() {
        return this.groupList;
    }
}