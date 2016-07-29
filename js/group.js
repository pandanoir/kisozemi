class Groups {
    get groupList() {return this._groupList;}
    constructor() {
        riot.observable(this);
        this._groupList = [];
        this.actionTypes = {
            changed: 'groups_store_changed'
        };
        API.getGroups(userStore.followList).then(value => {
            for (let i = 0, _i = value.length; i < _i; i++) {
                this._groupList[value[i].groupID] = value[i];
            }
            RiotControl.trigger(this.actionTypes.changed);
        });
        this.on('follow', this.fetchGroup.bind(this));
    }
    fetchGroup(groupID) {
        return this.fetchGroups([groupID]);
    }
    fetchGroups(groupIDs) {
        const self = this;
        const missing = groupIDs.filter(id => {
            return !this.groupList[id];
        });
        if (missing.length > 0) {
            return API.getGroups(missing).then(value => {
                for (let i = 0, _i = value.length; i < _i; i++) {
                    this._groupList[value[i].groupID] = value[i];
                }
                RiotControl.trigger(this.actionTypes.changed);
            });
        } else {
            return Promise.resolve();
        }
    }
}
