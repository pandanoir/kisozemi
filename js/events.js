var events = [];
class Events {
    constructor() {
        const self = this;
        riot.observable(this);
        this.eventList = [];
        this.fetchedGroupList = userStore.getFollowList().concat();
        this.actionTypes = {
            changed: 'events_store_changed'
        };

        API.getEvents(userStore.followList, userStore.screenName).then(value => {
            this._eventList.push(...value);
            RiotControl.trigger(this.actionTypes.changed);
        });
        this.on('follow', this.fetchEventList.bind(this));
    }
    fetchEventList(groupID) {
        const self = this;
        if (binarySearch(this.fetchedGroupList, groupID) === -1) {
            this.fetchedGroupList.push(groupID);
            this.fetchedGroupList.sort(function(a, b) {return a - b});
            return API.getEvents([groupID], userStore.screenName).then(value => {
                this._eventList.push(...value);
                RiotControl.trigger(this.actionTypes.changed);
            });
        }
        return Promise.resolve();
    }
    getEventList() {
        return this.eventList;
    }
}

//events.forEach(_ => (_.transformed = parseExpression(_.selector).map(__ => __.value)));
/*console.log(
    '[\n' + events.map(_ =>
        "    {selector: '" + _.selector + "', transformed: " + JSON.stringify(_.transformed) + ", title: '" + _.title + "', groupID: " + _.groupID+ "}"
    ).join(',\n') + '\n];'
);*/
function filterByID(eventList, idList, without) {
    const res = [];
    for (let i = 0, _i = eventList.length; i < _i; i++) {
        var searchRes = binarySearch(idList, eventList[i].groupID);
        if (!without && searchRes !== -1 || without && searchRes === -1) {
            res[res.length] = eventList[i];
        }
    }
    return res;
}
function filter(eventList, date) {
    const res = [];
    for (let i = 0, _i = eventList.length; i < _i; i++) {
        if (evaluateExpression(eventList[i].transformed, date)) {
            res[res.length] = eventList[i];
        }
    }
    return res;
}