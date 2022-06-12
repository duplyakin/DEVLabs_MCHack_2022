const modules = require('../modules.js');
const models_shared = require("../../models/shared.js");
const models = require("../../models/models.js");

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

const status_codes = require('../status_codes');


async function get_cookies(credentials_id) {

  let account = await models.Accounts.findOne({ _id: credentials_id }, function (err, res) {
    if (err) throw MyExceptions.MongoDBError('MongoDB find account err: ' + err)
  })

  if (account == null) {
    throw new Error("get_cookies: Account not found with credentials_id:", credentials_id)
  }

  const is_expired = check_expired(account) // true if we have to update cookies

  if (account.cookies == null || !Array.isArray(account.cookies) || account.cookies.length <= 0 || is_expired) {
    let loginAction = new modules.loginAction.LoginAction(credentials_id)
    await loginAction.startBrowser()
    await loginAction.login()
    await loginAction.closeBrowser()

    account = await models.Accounts.findOne({ _id: credentials_id }, function (err, res) {
      if (err) throw MyExceptions.MongoDBError('MongoDB find account err: ' + err)
    })

    return account.cookies
  }

  return account.cookies
}


function check_expired(account) {
  if (account.expires == null) {
    log.debug("check_expired: expires is null, credentials_id:", account._id)
    return true
  }

  if (Date.now() / 1000 > account.expires) {
    log.debug("check_expired: expires is OLD, credentials_id:", account._id)
    log.debug("check_expired: account.expires:", account.expires)
    log.debug("check_expired: expires now:", Date.now() / 1000)
  }

  return (Date.now() / 1000 > account.expires)
}


function serialize_data(input_data) {
  if (input_data == null) {
    throw new Error('SERIALIZATION error: input_data can’t be empty')
  }

  let task_data = {}

  task_data['campaign_data'] = input_data.campaign_data
  task_data['template_data'] = input_data.template_data
  task_data['prospect_data'] = input_data.prospect_data

  return task_data
}


async function searchWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    searchAction = new modules.searchAction.SearchAction(cookies, credentials_id, task_data.campaign_data.search_url, task_data.campaign_data.interval_pages);
    browser = await searchAction.startBrowser();
    result_data = await searchAction.search();
    browser = await searchAction.closeBrowser();

    status = result_data.code >= 0 ? 5 : -1;  // if we got some exception (BAN?), we have to save results before catch Error and send task status -1

  } catch (err) {

    log.error("searchWorker error:", err.stack)

    status = status_codes.FAILED;

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.SearchWorkerError().code,
        raw: MyExceptions.SearchWorkerError("searchWorker error: " + err).error
      };
    }

  } finally {
    log.debug("SearchWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function sn_searchWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    sn_searchAction = new modules.sn_searchAction.SN_SearchAction(cookies, credentials_id, task_data.campaign_data.search_url, task_data.campaign_data.interval_pages);
    browser = await sn_searchAction.startBrowser();
    result_data = await sn_searchAction.search();
    browser = await sn_searchAction.closeBrowser();

    status = result_data.code >= 0 ? 5 : -1;  // if we got some exception (BAN?), we have to save results before catch Error and send task status -1

  } catch (err) {

    log.error("sn_searchWorker error:", err.stack)

    status = status_codes.FAILED;

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.SN_SearchWorkerError().code,
        raw: MyExceptions.SN_SearchWorkerError("sn_searchWorker error: " + err).error
      };
    }

  } finally {
    log.debug("sn_searchWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function connectWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    let message = '';
    if (task_data.template_data != null) {
      if (task_data.template_data.message != null)
        message = task_data.template_data.message;
    }

    let connectAction = new modules.connectAction.ConnectAction(cookies, credentials_id, task_data.prospect_data.linkedin, message, task_data.prospect_data);
    browser = await connectAction.startBrowser();
    res = await connectAction.connect();
    browser = await connectAction.closeBrowser();

    result_data = {
      code: 0,
      if_true: res,
    };
    status = status_codes.CARRYOUT;

  } catch (err) {

    log.error("connectWorker error:", err.stack)

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.ConnectWorkerError().code,
        raw: MyExceptions.ConnectWorkerError("connectWorker error: " + err).error
      };
    }
    status = status_codes.FAILED;

  } finally {
    log.debug("ConnectWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function messageWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    // check reply
    let messageCheckAction = new modules.messageCheckAction.MessageCheckAction(cookies, credentials_id, task_data.prospect_data.linkedin);
    browser = await messageCheckAction.startBrowser();
    let resCheckMsg = await messageCheckAction.messageCheck();
    browser = await messageCheckAction.closeBrowser();

    if (!resCheckMsg.if_true) {
      // if no reply - send msg
      let messageAction = new modules.messageAction.MessageAction(cookies, credentials_id, task_data.prospect_data.linkedin, task_data.prospect_data, task_data.template_data.message);
      browser = await messageAction.startBrowser();
      let res = await messageAction.message();
      browser = await messageAction.closeBrowser();

      result_data = {
        code: 0,
        if_true: res,
      };
    } else {
      // else - task finished
      result_data = resCheckMsg
    }
    status = status_codes.CARRYOUT;

  } catch (err) {

    log.error("messageWorker error:", err.stack)

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.MessageWorkerError().code,
        raw: MyExceptions.MessageWorkerError("messageWorker error: " + err).error
      };
    }
    status = status_codes.FAILED;

  } finally {
    log.debug("MessageWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function scribeWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    let scribeAction = new modules.scribeAction.ScribeAction(cookies, credentials_id, task_data.prospect_data.linkedin);
    browser = await scribeAction.startBrowser();
    let res = await scribeAction.scribe();
    browser = await scribeAction.closeBrowser();

    result_data = {
      code: 0,
      if_true: true,
      data: JSON.stringify(res),
    };
    status = status_codes.CARRYOUT;

  } catch (err) {

    log.error("scribeWorker error:", err.stack)

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.ScribeWorkerError().code,
        raw: MyExceptions.ScribeWorkerError("scribeWorker error: " + err).error
      };
    }
    status = status_codes.FAILED;

  } finally {
    log.debug("ScribeWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function sn_scribeWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    let sn_scribeAction = new modules.sn_scribeAction.SN_ScribeAction(cookies, credentials_id, task_data.prospect_data.linkedin_sn);
    browser = await sn_scribeAction.startBrowser();
    let res = await sn_scribeAction.scribe();
    browser = await sn_scribeAction.closeBrowser();

    result_data = {
      code: 0,
      if_true: true,
      data: JSON.stringify(res),
    };
    status = status_codes.CARRYOUT;

  } catch (err) {

    log.error("sn_scribeWorker error:", err.stack)

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.SN_ScribeWorkerError().code,
        raw: MyExceptions.SN_ScribeWorkerError("sn_scribeWorker error: " + err).error
      };
    }
    status = status_codes.FAILED;

  } finally {
    log.debug("sn_scribeWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function messageCheckWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    let messageCheckAction = new modules.messageCheckAction.MessageCheckAction(cookies, credentials_id, task_data.prospect_data.linkedin);
    browser = await messageCheckAction.startBrowser();
    result_data = await messageCheckAction.messageCheck();
    browser = await messageCheckAction.closeBrowser();

    status = status_codes.CARRYOUT;

  } catch (err) {

    log.error("messageCheckWorker error:", err.stack)

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.MessageCheckWorkerError().code,
        raw: MyExceptions.MessageCheckWorkerError("messageCheckWorker error: " + err).error
      };
    }
    status = status_codes.FAILED;

  } finally {
    log.debug("MessageCheckWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function connectCheckWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    let connectCheckAction = new modules.connectCheckAction.ConnectCheckAction(cookies, credentials_id, task_data.prospect_data.linkedin);
    browser = await connectCheckAction.startBrowser();
    let res = await connectCheckAction.connectCheck();
    browser = await connectCheckAction.closeBrowser();

    result_data = {
      code: 0,
      if_true: res,
    };
    status = status_codes.CARRYOUT;

  } catch (err) {

    log.error("connectCheckWorker error:", err.stack)

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.ConnectCheckWorkerError().code,
        raw: MyExceptions.ConnectCheckWorkerError("connectCheckWorker error: " + err).error
      };
    }
    status = status_codes.FAILED;

  } finally {
    log.debug("ConnectCheckWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function visitProfileWorker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    let visitProfileAction = new modules.visitProfileAction.VisitProfileAction(cookies, credentials_id, task_data.prospect_data.linkedin);
    browser = await visitProfileAction.startBrowser();
    let res = await visitProfileAction.visit();
    browser = await visitProfileAction.closeBrowser();

    result_data = {
      code: 0,
      if_true: res,
    };
    status = status_codes.CARRYOUT;

  } catch (err) {

    log.error("visitProfileWorker error:", err.stack)

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.VisitProfileWorkerError().code,
        raw: MyExceptions.VisitProfileWorkerError("visitProfileWorker error: " + err).error
      };
    }
    status = status_codes.FAILED;

  } finally {
    log.debug("visitProfileWorker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


async function post_engagement_worker(task_id) {
  let status = status_codes.FAILED;
  let result_data = {};
  let task = null;
  let credentials_id = null;

  let browser = null;
  try {
    task = await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id, ack: 0 }, { ack: 1 }, { new: true, upsert: false });
    if (task == null) {
      log.debug("..... task not found or locked: .....");
      return;
    }

    credentials_id = task.credentials_id;
    if (!credentials_id) {
      throw new Error('there is no task.credentials_id');
    }
    let input_data = task.input_data;
    if (!input_data) {
      throw new Error('there is no task.input_data');
    }
    let task_data = serialize_data(input_data);

    let cookies = await get_cookies(credentials_id);

    // start work
    post_engagement_action = new modules.post_engagement_action.Post_engagement_action(cookies, credentials_id, task_data.campaign_data.post_url);
    browser = await post_engagement_action.startBrowser();
    result_data = await post_engagement_action.engagement();
    browser = await post_engagement_action.closeBrowser();

    status = status_codes.CARRYOUT
  } catch (err) {

    log.error("post_engagement_worker error:", err.stack)

    status = status_codes.FAILED;

    if (err.code != null && err.code != -1) {
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
    } else if (err.code == -1) {
      status = status_codes.BLOCK_HAPPENED;
      // Context error
      result_data = {
        if_true: false,
        code: err.code,
        raw: err.error
      };
      await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { task_id: task_id }, { upsert: false });

    } else {
      result_data = {
        if_true: false,
        code: MyExceptions.PostEngagementWorkerError().code,
        raw: MyExceptions.PostEngagementWorkerError("post_engagement_worker error: " + err).error
      };
    }

  } finally {
    //log.debug("post_engagement_worker RES: ", result_data);

    if (task !== null) {
      await models_shared.TaskQueue.findOneAndUpdate({ _id: task_id }, { ack: 0, status: status, result_data: result_data, is_queued: 0 }, { upsert: false });
    }

    if (browser != null) {
      await browser.close();
      browser.disconnect();
    }
  }
}


module.exports = {
  searchWorker: searchWorker,
  sn_searchWorker: sn_searchWorker,

  connectWorker: connectWorker,
  messageWorker: messageWorker,

  scribeWorker: scribeWorker,
  sn_scribeWorker: sn_scribeWorker,

  messageCheckWorker: messageCheckWorker,
  connectCheckWorker: connectCheckWorker,

  visitProfileWorker: visitProfileWorker,

  post_engagement_worker: post_engagement_worker,
}
