var events = [];
function Events() {
    var self = this;
    riot.observable(this);
    this.eventList = [];
    this.fetchedGroupList = userStore.getFollowList().concat();
    API.getEvents(userStore.followList).then(function(value) {
        self.eventList.push.apply(self.eventList, value);
        RiotControl.trigger(self.actionTypes.changed);
    });
    this.on('follow', this.fetchEventList.bind(this));
}
Events.prototype.fetchEventList = function(groupID) {
    var self = this;
    if (binarySearch(this.fetchedGroupList, groupID) === -1) {
        this.fetchedGroupList.push(groupID);
        this.fetchedGroupList.sort(function(a, b) {return a - b});
        return API.getEvents([groupID]).then(function(value) {
            self.eventList.push.apply(self.eventList, value);
            RiotControl.trigger(self.actionTypes.changed);
        });
    }
    return Promise.resolve();
};
Events.prototype.getEventList = function() {
    return this.eventList;
};
Events.prototype.actionTypes = {
    changed: 'events_store_changed'
};

//events.forEach(_ => (_.transformed = parseExpression(_.selector).map(__ => __.value)));
/*console.log(
    '[\n' + events.map(_ =>
        "    {selector: '" + _.selector + "', transformed: " + JSON.stringify(_.transformed) + ", title: '" + _.title + "', groupID: " + _.groupID+ "}"
    ).join(',\n') + '\n];'
);*/
function filterByID(eventList, idList, without) {
    var res = [];
    for (var i = 0, _i = eventList.length; i < _i; i++) {
        var searchRes = binarySearch(idList, eventList[i].groupID);
        if (!without && searchRes !== -1 || without && searchRes === -1) {
            res[res.length] = eventList[i];
        }
    }
    return res;
}
function filter(eventList, date) {
    var res = [];
    for (var i = 0, _i = eventList.length; i < _i; i++) {
        if (evaluateExpression(eventList[i].transformed, date)) {
            res[res.length] = eventList[i];
        }
    }
    return res;
}