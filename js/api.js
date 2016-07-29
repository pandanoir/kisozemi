var API = {};
API.getGroups = function(groupIDs) {
    return new Promise(function(resolve, reject) {
        request.post('get/group')
            .type('form')
            .send({groupIDs: groupIDs.join(',')})
            .end(function(err, res) {
                res = JSON.parse(res.text).map(function(_) {
                    _.screenName = _.screen_name;
                    return _;
                })
                resolve(res);
            });
    });
};
    return new Promise(function(resolve, reject) {
API.getEvents = function(groupIDs, user_screen_name) {
        request.post('get/event')
            .type('form')
            .send({groupIDs: groupIDs.join(','), user_screen_name: user_screen_name})
            .end(function(err, res) {
                res = JSON.parse(res.text).map(function(_) {
                    _.transformed = JSON.parse(_.transformed);
                    return _;
                });
                resolve(res);
            });
    });
};
API.search = function(keyword) {
    return new Promise(function(resolve, reject) {
        request.post('search')
            .type('form')
            .send({keyword: keyword})
            .end(function(err, res) {
                res = JSON.parse(res.text);
                resolve(res);
            });
    });
};