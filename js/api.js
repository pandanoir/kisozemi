var API = {};
API.getGroups = groupIDs => new Promise((resolve, reject) => {
    request.post('get/group')
        .type('form')
        .send({groupIDs: groupIDs.join(',')})
        .end((err, res) => {
            res = JSON.parse(res.text).map(_ => {
                _.screenName = _.screen_name;
                return _;
            })
            resolve(res);
        });
});
API.getEvents = (groupIDs, user_screen_name) => new Promise((resolve, reject) => {
    request.post('get/event')
        .type('form')
        .send({groupIDs: groupIDs.join(','), user_screen_name: user_screen_name})
        .end((err, res) => {
            res = JSON.parse(res.text).map(_ => {
                _.transformed = JSON.parse(_.transformed);
                return _;
            });
            resolve(res);
        });
});
API.search = keyword => new Promise((resolve, reject) => {
    request.post('search')
        .type('form')
        .send({keyword: keyword})
        .end((err, res) => {
            res = JSON.parse(res.text);
            resolve(res);
        });
});