const workers = require('../linkedin/workers/workers.js');
var log = require('loglevel').getLogger("o24_logger");

// test

(async () => {
  log.setLevel("DEBUG")
  log.debug("..... test started: .....", __filename)

  const task_id = "000003a80a2de70af2b00000" // test task_id

  //await workers.loginWorker(task_id)
  //await workers.searchWorker(task_id)
  //await workers.sn_searchWorker(task_id)
  //await workers.connectWorker(task_id)
  //await workers.messageWorker(task_id)
  await workers.scribeWorker(task_id)
  //await workers.sn_scribeWorker(task_id)
  //await workers.messageCheckWorker(task_id)
  //await workers.connectCheckWorker(task_id)
  //await workers.visitProfileWorker(task_id)
  //await workers.post_engagement_worker(task_id)

})();