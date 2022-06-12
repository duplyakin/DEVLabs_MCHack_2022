let mongooseConnect = require('./connect.js');
let mongoose = mongooseConnect.mongoose;
let Schema = mongoose.Schema;


let accountsSchema = new Schema({
    task_id: {
        type: mongoose.ObjectId,
        default: null,
    },

    status: {
        type: Number,
        default: 0,
    },

    login: {
        type: String,
        default: null,
    },

    password: {
        type: String,
        default: null,
    },

    expires: Number,

    cookies: {
        type: Array,
        default: null,
    },

    blocking_type: {
        type: String,
        default: null,
    },

    blocking_data: {
        type: Object,
        default: null,
    },

});


let cronLockSchema = new Schema({
    lock: {
        type: String,
        unique: true, // it's needed to prevent creating new documents. always 1 document with lock = cron_lock
    },

    ack: {
        type: Number,
        default: 0,
    },

});

module.exports = {
    Accounts: mongoose.model('Accounts', accountsSchema),
    CronLock: mongoose.model('CronLock', cronLockSchema),
}
