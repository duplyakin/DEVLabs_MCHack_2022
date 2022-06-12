const models = require("../models/models.js");


/*
(async () => {
    let handler_lock = null

    try {
        handler_lock = await models.CronLock.findOneAndUpdate({ lock: "cron_lock", ack: 0 }, { ack: 1 }, { upsert: true, new: true })
    } catch (err) {

    } finally {
        console.log(handler_lock)

    }

})();*/



(async () => {

    let handler_lock = await models.CronLock.findOneAndUpdate({ lock: "cron_lock" }, { ack: 0 }, { upsert: true, new: true })


    console.log(handler_lock)

})();