var events = [];
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