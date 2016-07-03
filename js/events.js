var events = [
    {selector: '2016/5/6...2016/5/7', transformed: [["range","2016/5/6...2016/5/7"]], title: '皐月合宿', groupID: 12},
    {selector: '2016/5/19...2016/5/21', transformed: [["range","2016/5/19...2016/5/21"]], title: '新歓合宿', groupID: 12},
    {selector: '2016/6/6...2016/6/11', transformed: [["range","2016/6/6...2016/6/11"]], title: '朝稽古週間', groupID: 12},
    {selector: 'year: 2016 month: 6 date: 25', transformed: [["year","2016"],["month","6"],"&&",["date","25"],"&&"], title: '一二年交流会', groupID: 12},
    {selector: 'year: 2016 month: 7 date: 2', transformed: [["year","2016"],["month","7"],"&&",["date","2"],"&&"], title: '東連', groupID: 12},
    {selector: 'year: 2016 month: 7 date: 16', transformed: [["year","2016"],["month","7"],"&&",["date","16"],"&&"], title: '前期演武会', groupID: 12},
    {selector: 'range: 2016/8/1...2016/8/6', transformed: [["range","2016/8/1...2016/8/6"]], title: '暑中稽古', groupID: 12},
    {selector: '2016/8/22...2016/8/27', transformed: [["range","2016/8/22...2016/8/27"]], title: '夏合宿', groupID: 12},
    {selector: 'range:2016/8/6...2016/8/8', transformed: [["range","2016/8/6...2016/8/8"]], title: '七夕祭り', groupID: 13},
    {selector: 'month:1 date:1 range:1948/7/20..', transformed: [["month","1"],["date","1"],"&&",["range","1948/7/20..."],"&&"], title: '元旦', groupID: 0},
    {selector: 'month:1 date:15 range:1948/7/20..1999/12/31', transformed: [["month","1"],["date","15"],"&&",["range","1948/7/20...1999/12/31"],"&&"], title: '成人の日', groupID: 0},
    {selector: 'month:1 day:2nd-mon range:2000/1/1..', transformed: [["month","1"],["day","2nd-mon"],"&&",["range","2000/1/1..."],"&&"], title: '成人の日', groupID: 0},
    {selector: 'month:2 date:11 range:1967/1/1..', transformed: [["month","2"],["date","11"],"&&",["range","1967/1/1..."],"&&"], title: '建国記念の日', groupID: 0},
    {selector: 'month:4 date:29 range:1948/7/20..1988/12/31', transformed: [["month","4"],["date","29"],"&&",["range","1948/7/20...1988/12/31"],"&&"], title: '天皇誕生日', groupID: 0},
    {selector: 'month:4 date:29 range:1989/1/1..2006/12/31', transformed: [["month","4"],["date","29"],"&&",["range","1989/1/1...2006/12/31"],"&&"], title: 'みどりの日', groupID: 0},
    {selector: 'month:4 date:29 range:2007/1/1..', transformed: [["month","4"],["date","29"],"&&",["range","2007/1/1..."],"&&"], title: '昭和の日', groupID: 0},
    {selector: 'month:5 date:3 range:1948/7/20..', transformed: [["month","5"],["date","3"],"&&",["range","1948/7/20..."],"&&"], title: '憲法記念日', groupID: 0},
    {selector: 'month:5 date:4 range:2007/1/1..', transformed: [["month","5"],["date","4"],"&&",["range","2007/1/1..."],"&&"], title: 'みどりの日', groupID: 0},
    {selector: 'month:5 date:5 range:1948/7/20..', transformed: [["month","5"],["date","5"],"&&",["range","1948/7/20..."],"&&"], title: 'こどもの日', groupID: 0},
    {selector: 'month:7 date:20 range:1996/1/1..2002/12/31', transformed: [["month","7"],["date","20"],"&&",["range","1996/1/1...2002/12/31"],"&&"], title: '海の日', groupID: 0},
    {selector: 'month:7 day:3rd-mon range:2003/1/1..', transformed: [["month","7"],["day","3rd-mon"],"&&",["range","2003/1/1..."],"&&"], title: '海の日', groupID: 0},
    {selector: 'month:8 date:11 range:2016/1/1..', transformed: [["month","8"],["date","11"],"&&",["range","2016/1/1..."],"&&"], title: '山の日', groupID: 0},
    {selector: 'month:9 date:15 range:1966/1/1..2002/12/31', transformed: [["month","9"],["date","15"],"&&",["range","1966/1/1...2002/12/31"],"&&"], title: '敬老の日', groupID: 0},
    {selector: 'month:9 day:3rd-mon range:2003/1/1..', transformed: [["month","9"],["day","3rd-mon"],"&&",["range","2003/1/1..."],"&&"], title: '敬老の日', groupID: 0},
    {selector: 'month:10 date:10 range:1966/1/1..1999/12/31', transformed: [["month","10"],["date","10"],"&&",["range","1966/1/1...1999/12/31"],"&&"], title: '体育の日', groupID: 0},
    {selector: 'month:10 day:2nd-mon range:2000/1/1..', transformed: [["month","10"],["day","2nd-mon"],"&&",["range","2000/1/1..."],"&&"], title: '体育の日', groupID: 0},
    {selector: 'month:11 date:3 range:1948/7/20..', transformed: [["month","11"],["date","3"],"&&",["range","1948/7/20..."],"&&"], title: '文化の日', groupID: 0},
    {selector: 'month:11 date:23 range:1948/7/20..', transformed: [["month","11"],["date","23"],"&&",["range","1948/7/20..."],"&&"], title: '勤労感謝の日', groupID: 0},
    {selector: 'month:12 date:23 range:1989/1/1..', transformed: [["month","12"],["date","23"],"&&",["range","1989/1/1..."],"&&"], title: '勤労感謝の日', groupID: 0},
    {selector: 'date:vernal-equinox-day range:1949/1/1..', transformed: [["date","vernal-equinox-day"],["range","1949/1/1..."],"&&"], title: '春分の日', groupID: 0},
    {selector: 'date:autumnal-equinox-day range:1948/1/1..', transformed: [["date","autumnal-equinox-day"],["range","1948/1/1..."],"&&"], title: '秋分の日', groupID: 0},
    {selector: 'date:full-moon-night range:1901/1/1..', transformed: [["date","full-moon-night"],["range","1901/1/1..."],"&&"], title: '十五夜', groupID: 0},
    {selector: 'day:monday', transformed: [["day","monday"]], title: 'ラーメン二郎 定休日', groupID: 15}
];
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