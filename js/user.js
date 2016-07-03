function User() {
    // getByDataBase();
    this.followList = [0, 1, 12, 15]; // temp
    this.hiddenGroup = [];
    this.id = 10000; // temp
    this.screenName = 'test_user'
    this.name = 'テストユーザー'; // temp
}
User.prototype.follow = function(groupID) {
    if (binarySearch(this.followList, groupID) === -1) {
        this.followList.push(groupID);
        this.followList.sort(function(a, b) {return a - b});
    }
}
User.prototype.unfollow = function(groupID) {
    this.followList = this.followList.filter(function(_) {return _ !== groupID});
}
User.prototype.hide = function(groupID) {
    if (binarySearch(this.hiddenGroup, groupID) === -1) {
        this.hiddenGroup.push(groupID);
        this.hiddenGroup.sort(function(a, b) {return a - b});
    }
}
User.prototype.show = function(groupID) {
    this.hiddenGroup = this.hiddenGroup.filter(function(_) {return _ !== groupID});
}
var user = new User();