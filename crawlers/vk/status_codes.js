module.exports = {
    // codes for task.status
    NEW: 0,
    IN_PROGRESS: 1,
    PAUSED: 2, // not used
    FINISHED: 3,
    READY: 4,
    CARRYOUT: 5,
    BLOCK_HAPPENED: 6,
    NEED_USER_ACTION: 7,
    NEED_USER_ACTION_PROGRESS: 8,
    NEED_USER_ACTION_RESOLVED: 9,
    FAILED: -1,

    ACTIVE: 1,

    // codes for account.status
    AVAILABLE: 0,
    //IN_PROGRESS: 1, // the same as task.status
    BLOCKED: 2,
    SOLVING_CAPTCHA: 3,
    BROKEN_CREDENTIALS: 4,
    //FAILED: -1, // the same as task.status

    // task.result_data.code
    ///
}