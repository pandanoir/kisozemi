var _groups = [
    {groupID: 0, name: '祝日'},
    {groupID: 1, name: 'プライベート'},
    {groupID: 12, name: '東北大学合気道部'},
    {groupID: 13, name: '仙台イベント'},
    {groupID: 14, name: '東北大学'},
    {groupID: 15, name: 'ラーメン二郎 仙台店'}
];
var groups = [];

for (var i = 0, _i = _groups.length; i < _i; i++) {
    groups[_groups[i].groupID] = _groups[i];
}