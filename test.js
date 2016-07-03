

var datas = [
//    'year: leap-year',
//    'month: 11',
//    'date: 30',
//    'day: 2nd-Wednesday',
//    'range:1997/5/21...2016/6/28',
//    'date: 13 day: 3rd-wednesday',
//    'month: 5 date: 5',
//    'year: leap-year month: 2 date: 29',
//    'day: saturday || day: sunday date: 20',
//    '(day: saturday || day: sunday) date: 20',
//    '(day: saturday || year: 1999 && (day: sunday || month: 11)) date: 20',
//    'day: saturday || year: 1999 && day: sunday || month: 11 date: 20',
//    'year: leap-year month: 5',
];
for (const data of datas) {
    var res = expression.parse(data);
    res = shuntingYard(res.value);
//    console.log(res);
    console.log(data, JSON.stringify(res.map(function(_) {return _.value}), null, '  '));
    //console.log(data, res.status);
//    console.log(data, Parsimmon.formatError(res));
}