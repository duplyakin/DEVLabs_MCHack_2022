const models = require("../models/models.js");

(async () => {
    console.log("..... acc test started: .....", __filename);
    let credentials_id = '5ed523dbb30946e61acaa412';
    let new_account = {
        login: 'grinnbob@rambler.ru',
        password: 'linked123',
    }

    let account = await models.Accounts.findOneAndUpdate({
        _id: credentials_id,
        //'status' : 1
    }, new_account, {upsert: false, new: true}, function (err, res) {
        if (err) console.log("..... error: .....", err);
    });
    console.log("..... account: .....", account);

})();
