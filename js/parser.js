// all properties:
//   year
//   month
//   date
//   day
//   range
//   not

'use strict';
// var Parsimmon = require('parsimmon');
// var shuntingYard = require('./shuntingyard.js');

var join = _ => _.join('');
var flatten = (a, b) => a.concat(b);

var lazy = Parsimmon.lazy;
var seq = Parsimmon.seq;
var alt = Parsimmon.alt;
var regex = Parsimmon.regex;
var string = Parsimmon.string;
var s = string;

var naturalNumber = regex(/[1-9][0-9]*/);
var ws = Parsimmon.optWhitespace;
var year = naturalNumber;
var month = regex(/1[012]|[1-9]/);
var date = regex(/[12][0-9]|3[01]|[1-9]/);
var fullDate = seq(year, s('/'), month, s('/'), date).map(join);

var DAY_REGEXP = /(?:(1st|2nd|3rd|[4-9]th)[\- ])?(sun(?:day)?|mon(?:day)?|tue(?:sday)?|wed(?:nesday)?|thu(?:rsday)?|fri(?:day)?|sat(?:urday)?)/i;


var primaryExpression = lazy(function() {
    var selector = lazy(function() {
        return alt(
            seq(s('year').skip(s(':')).skip(ws), alt(year, s('leap-year'))),
            seq(s('month').skip(s(':')).skip(ws), month),
            seq(s('date').skip(s(':')).skip(ws), alt(date, s('vernal-equinox-day'), s('autumnal-equinox-day'), s('full-moon-night'))),
            seq(s('day').skip(s(':')).skip(ws), regex(DAY_REGEXP)),
            seq(s('range').skip(s(':')).skip(ws), seq(fullDate.times(0, 1), s('.').times(2, 3).result('...'), fullDate.times(0, 1)).map(join)),
            seq(s('not').skip(s(':')).skip(ws), selector.map(_ => _[0].value))
        ).map(x => [{
            value: x,
            type: 'primary'
        }]);
    });
    return alt(
        selector,
        seq(s('(').skip(ws).result({value: '(', type: 'left-parenthesis'}), expression, s(')').result({value: ')', type: 'right-parenthesis'})).map(x => x.reduce(flatten, []))
    );
});
var expression = lazy(function() {
    var andOperator = alt(seq(ws, alt(s('&&'), s('and'))), Parsimmon.whitespace).skip(ws).result([{value: '&&', type: 'operator'}]);
    var orOperator = seq(ws, alt(s('||'), s('or')), ws).result([{value: '||', type: 'operator'}]);
    return seq(
        primaryExpression,
        seq(
            alt(orOperator, andOperator),
            primaryExpression
        ).many()
    ).map(function(x) {
        return x[0].concat(
            x[1].map(function(x) {
                return x[0].concat(x[1]);
            }).reduce(flatten, [])
        );
    });
});

function parseExpression(expr) {
    var res = expression.parse(expr);
    if (res.status) {
        return shuntingYard(res.value).map(_ => _.value);
    }
    return null;
}
function evaluateExpression(transformed, date) {
    var stack = [];
    for (var i = 0, _i = transformed.length; i < _i; i++) {
        if (transformed[i] === '&&' || transformed[i] === '||') {
            var b = stack.pop();
            var a = stack.pop();
            if (!a.isEvaluated) a = {value: evaluateSelector(a.value, date), isEvaluated: true};
            if (transformed[i] === '&&') {
                stack[stack.length] = {value: a.value && (b.isEvaluated ? b.value : evaluateSelector(b.value, date)), isEvaluated: true};
            } else {
                stack[stack.length] = {value: a.value || (b.isEvaluated ? b.value : evaluateSelector(b.value, date)), isEvaluated: true};
            }
        } else {
            stack[stack.length] = {value: transformed[i], isEvaluated: false};
        }
    }
    var res = stack.pop();
    return res.isEvaluated ? res.value : evaluateSelector(res.value, date);
}
function evaluateSelector(primary, date) {
    var property = primary[0];
    var value = primary[1];
    var dayDic = {
        sun: 0, sunday: 0,
        mon: 1, monday: 1,
        tue: 2, tuesday: 2,
        wed: 3, wednesday: 3,
        thu: 4, thursday: 4,
        fri: 5, friday: 5,
        sat: 6, saturday: 6
    };
    if (property === 'year') {
        if (value === 'leap-year') {
            var year = date.getFullYear();
            return year % 400 === 0 || year % 100 !== 0 && year % 4 === 0;
        } else {
            return '' + date.getFullYear() === value;
        }
    } else if (property === 'month') {
        return '' + (date.getMonth() + 1) === value;
    } else if (property === 'date') {
        var year = date.getFullYear();
        if (value === 'vernal-equinox-day') {
            if (year < 1949 || date.getMonth() + 1 !== 3) return false; // 1949年以前は祝日ではなかった
            if (year > 2030) return false; // 得られたデータが2030年までしかないからこうするしかない
            return date.getDate() === 20 + [1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,0,1,1,1,0,0,1,1,0,0,1,1,0,0,1,1,0,0,1,1,0,0,1,1,0,0,1,1,0,0,1,1,0,0,1,1,0,0,0,1,0,0,0][year - 1949];
        }
        if (value === 'autumnal-equinox-day') {
            if (year < 1948 || date.getMonth() + 1 !== 9) return false; // 1948年以前は祝日ではなかった
            if (year > 2030) return false; // 得られたデータが2030年までしかないからこうするしかない
            else if (year % 4 === 3 && 1979 >= year && year >= 1951) return date.getDate() === 24;
            else if (year % 4 === 0 && 2044 >= year && year >= 2012) return date.getDate() === 22;
            else if (date.getDate() === 23) return true;
        }
        if (value === 'full-moon-night') {
            var MEIGETSU = [
                {date: 27}, {date: 16}, {month: 9, date: 5}, {date: 24}, {date: 13}, {month: 9, date: 2}, {date: 22}, {date: 10}, {date: 29},
                {date: 18}, {month: 9, date: 6}, {date: 25}, {date: 15}, {month: 9, date: 4}, {date: 23}, {date: 12}, {date: 30}, {date: 19},
                {month: 9, date: 8}, {date: 26}, {date: 16}, {month: 9, date: 5}, {date: 25}, {date: 13}, {month: 9, date: 2}, {date: 21}, {date: 10},
                {date: 28}, {date: 17}, {month: 9, date: 6}, {date: 26}, {date: 15}, {month: 9, date: 4}, {date: 23}, {date: 12}, {date: 30},
                {date: 19}, {month: 9, date: 8}, {date: 27}, {date: 16}, {month: 9, date: 5}, {date: 25}, {date: 14}, {month: 9, date: 1}, {date: 20},
                {date: 10}, {date: 29}, {date: 17}, {month: 9, date: 6}, {date: 26}, {date: 15}, {month: 9, date: 3}, {date: 22}, {date: 11},
                {date: 30}, {date: 19}, {date: 8}, {date: 27}, {date: 17}, {month: 9, date: 5}, {date: 24}, {date: 13}, {month: 9, date: 2},
                {date: 20}, {date: 10}, {date: 29}, {date: 18}, {month: 9, date: 6}, {date: 26}, {date: 15}, {month: 9, date: 3}, {date: 22},
                {date: 11}, {date: 30}, {date: 20}, {date: 8}, {date: 27}, {date: 17}, {month: 9, date: 5}, {date: 23}, {date: 12},
                {month: 9, date: 1}, {date: 21}, {date: 10}, {date: 29}, {date: 18}, {month: 9, date: 7}, {date: 25}, {date: 14}, {month: 9, date: 3},
                {date: 22}, {date: 11}, {date: 30}, {date: 20}, {date: 9}, {date: 27}, {date: 16}, {month: 9, date: 5}, {date: 24},
                {date: 12}, {month: 9, date: 1}, {date: 21}, {date: 11}, {date: 28}, {date: 18}, {month: 9, date: 6}, {date: 25}, {date: 14},
                {month: 9, date: 3}, {date: 22}, {date: 12}, {date: 30}, {date: 19}, {date: 8}, {date: 27}, {date: 15}, {month: 9, date: 4},
                {date: 24}, {date: 13}, {month: 9, date: 1}, {date: 21}, {date: 10}, {date: 29}, {date: 17}, {month: 9, date: 6}, {date: 25},
                {date: 15}, {month: 9, date: 3}, {date: 22}, {date: 12}
            ];
            var month = date.getMonth();
            if(year < 1901 || year > 2030){
                // データなし
                return false;
            }else{
                return (!MEIGETSU[year - 1901].month && month === 8 || MEIGETSU[year - 1901].month === month) && MEIGETSU[year-1901].date === date;
            }
        }
        return '' + date.getDate() === value;
    } else if (property === 'day') {
        var res = value.match(DAY_REGEXP);
        var ordinal = res[1];
        var day = res[2];
        return dayDic[day.toLowerCase()] === date.getDay() &&
            (typeof ordinal === 'undefined' ||
                (0 | (date.getDate() - 1) / 7) === parseInt(ordinal, 10) - 1);
    } else if (property === 'range') {
        var value = value.split('...');
        var res = true;
        if (value[0] !== '') {
            var start = value[0].split('/').map(_ => parseInt(_, 10));
            start = new Date(start[0], start[1] - 1, start[2]);
            res = res && date - start >= 0;
        }
        if (value[1] !== '') {
            var end = value[1].split('/').map(_ => parseInt(_, 10));
            end = new Date(end[0], end[1] - 1, end[2]);
            res = res && end - date >= 0;
        }
        return res;
    } else if (property === 'not') {
        return !evaluateSelector(value, date);
    }
    return false;
}
