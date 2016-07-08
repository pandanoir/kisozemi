var API = {};
API.getGroups = function(groupIDs) {
    return new Promise(function(resolve, reject) {
        request.post('group.php')
            .type('form')
            .send({groupIDs: groupIDs.join(',')})
            .end(function(err, res) {
                res = JSON.parse(res.text);
                resolve(res);
            });
    });
};
API.getEvents = function(groupIDs) {
    return new Promise(function(resolve, reject) {
        request.post('event.php')
            .type('form')
            .send({groupIDs: groupIDs.join(',')})
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
        request.post('search.php')
            .type('form')
            .send({keyword: keyword})
            .end(function(err, res) {
                res = JSON.parse(res.text);
                resolve(res);
            });
    });
};