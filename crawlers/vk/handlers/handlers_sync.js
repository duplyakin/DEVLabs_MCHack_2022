const models = require("../../models/models.js");
const models_shared = require("../../models/shared.js");

const workers = require('../workers/workers.js');

const cron = require('node-cron');

const actionKeys = require('./actionKeys.js');
const status_codes = require('../status_codes')

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");


async function consumer(data) {
    try {
        switch (data.action_key) {
            case 'linkedin-connect':
                await workers.connectWorker(data.task_id);
                break;
            case 'linkedin-send-message':
                await workers.messageWorker(data.task_id);
                break;
            case 'linkedin-check-accept':
                await workers.connectCheckWorker(data.task_id);
                break;
            case 'linkedin-search':
                await workers.searchWorker(data.task_id);
                break;
            case 'linkedin-search-sn':
                await workers.sn_searchWorker(data.task_id);
                break;
            case 'linkedin-parse-profile':
                await workers.scribeWorker(data.task_id);
                break;
            case 'linkedin-parse-profile-sn':
                await workers.sn_scribeWorker(data.task_id);
                break;
            case 'linkedin-check-reply':
                await workers.messageCheckWorker(data.task_id);
                break;
            case 'linkedin-visit-profile':
                await workers.visitProfileWorker(data.task_id);
                break;
            case 'linkedin-post-parsing':
                await workers.post_engagement_worker(job.data.task_id);
                break;

            default:
                //log.debug('unknown action_key: ', data.action_key);
                break;
        }
    } catch (err) {
        log.error('queue error - something went wrong: ', err.stack);

        let err_result = {
            code: MyExceptions.HandlerError().code,
            raw: MyExceptions.HandlerError("HandlerError error: " + err).error
        };

        await models_shared.TaskQueue.findOneAndUpdate({ _id: data.task_id }, { status: -1, result_data: err_result }, function (err, res) {
            if (err) throw MyExceptions.MongoDBError('MongoDB save err: ' + err);
            // updated!
        });
    }
}

async function taskStatusListener() {
    var handler_lock = null

    // start cron every minute
    cron.schedule("* * * * *", async () => {
        try {
            handler_lock = await models.CronLock.findOneAndUpdate({ lock: "cron_lock", ack: 0 }, { ack: 1 }, { upsert: true, new: true })

            let tasks = await models_shared.TaskQueue.find({ status: status_codes.IN_PROGRESS, is_queued: 0, action_key: { $in: actionKeys.action_keys } }, function (err, res) {
                if (err) throw MyExceptions.MongoDBError('MongoDB find TASKs err: ' + err);
            });

            if (Array.isArray(tasks) && tasks.length !== 0) {

                for (let task of tasks) {
                    let data = {
                        task_id: task.id,
                        action_key: task.action_key,
                    };

                    await models_shared.TaskQueue.findOneAndUpdate({ _id: task.id }, { is_queued: 1 }, function (err, res) {
                        if (err) throw MyExceptions.MongoDBError('MongoDB updateOne TASK err: ' + err);
                    });

                    log.error('taskStatusListener: task executed, status: ' + task.status + ' action_key: ' + task.action_key); // test
                    consumer(data);
                }

                log.debug('taskStatusListener: TASKs executed');
            }

            log.debug('taskStatusListener: ....this message logs every minute - CRON is active....');

        } catch (err) {
            log.debug('taskStatusListener: ....CRON error:....', err);
            log.debug('taskStatusListener: ....CRON error handler_lock:....', handler_lock);

        } finally {
            log.debug('taskStatusListener: ....CRON finally handler_lock:....', handler_lock);
            if (handler_lock != null) {
                await models.CronLock.findOneAndUpdate({ lock: "cron_lock" }, { ack: 0 }, { upsert: false })
            }
        }
    });

}

module.exports = {
    taskStatusListener: taskStatusListener,
}

