const models = require("../models/shared.js");
const actionKeys = require('../linkedin/handlers/actionKeys.js');

(async () => {
    console.log("..... task test started: .....", __filename);

    let tasks = await models.TaskQueue.find({
        action_key: { $in: actionKeys.action_keys },
        //'status' : 1
    }, function (err, res) {
        if (err) console.log("..... error: .....", err);
    });
    console.log("..... task: .....", tasks);

    let task = tasks[0];
    console.log("..... typeof task: .....", typeof task);
    console.log("..... task: .....", task);
    console.log("..... Object.keys(task): .....", Object.keys(task));
    console.log("..... task.id: .....", task.id);
    console.log("..... action: .....", task.action_key);
    console.log("..... status: .....", task.status);
    console.log("..... input_data: .....", task.input_data);
    console.log("..... result_data: .....", task.result_data);
    console.log("..... credentials_id: .....", task.credentials_id);
    console.log("..... ack: .....", task.ack);

    /*
    await task.updateOne({
      'status' : 5, result_data : { code: 0, raw: 'yhhty'}
    }, function (err, res) {
      if (err) console.log("..... error: .....", err);
    });
     */
    //task.status = -1;
    //task.save(); // not atomic
    //task.ack = 12;

    //let new_task = await models.TaskQueue.findOneAndUpdate({ _id: task.id, ack: 0 }, { ack: 1 }, { new: true });

    try {
        if (!new_task) {
            //return;
            console.log("..... not found: .....");
        } 

         console.log("..... new_task: .....", new_task);

        // status 3
    } catch (err) {
        // status -1
    } finally {
       // await models.TaskQueue.findOneAndUpdate({ _id: task.id }, { ack: 0 }, { new: true });
    }


})();
