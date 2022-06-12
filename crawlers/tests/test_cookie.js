
const models = require("../models/models.js");
const models_shared = require("../models/shared.js");



(async () => {

    let task = await models_shared.TaskQueue.findOneAndUpdate({ action_key: 'linkedin-send-message'}, { new: true });
    let cookies = await models.Cookies.findOne({ credentials_id: task.credentials_id }, function (err, res) {
        if (err) throw MyExceptions.MongoDBError('MongoDB find COOKIE err: ' + err);
      });

      console.log('.......typeof..credentials_id........ ', typeof task.credentials_id)
      console.log('.........credentials_id........ ', task.credentials_id)

      console.log(task)
      console.log(cookies)

  })();