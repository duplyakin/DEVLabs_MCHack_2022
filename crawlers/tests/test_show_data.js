const models_shared = require("../models/shared.js");
const models = require("../models/models.js");

const SEARCH_URL = "https://www.linkedin.com/search/results/all/?keywords=acronis&origin=GLOBAL_SEARCH_HEADER&page=97";
const CONNECT_URL_1 = "https://www.linkedin.com/in/kirill-shilov-25aa8630/";
const CONNECT_URL_2 = "https://www.linkedin.com/in/vlad-duplyakin-923475116/";
const CONNECT_URL_3 = "https://www.linkedin.com/in/alexander-savinkin-3ba99614/";
const CONNECT_URL_4 = "https://www.linkedin.com/in/bersheva/";
const CONNECT_URL_5 = "https://www.linkedin.com/in/alexyerokhin/";

// test task

(async () => {
  console.log("..... test_create_data started: .....", __filename);
  try {

    let account_id = "111113a80a2de70af2b11111"; // test id
    let task_id = "000003a80a2de70af2b00000"; // test id

    let account = await models.Accounts.findOne({ _id: account_id });
    let task = await models_shared.TaskQueue.findOne({ _id: task_id });

    console.log('..........account.............', account)
    console.log('..........task.............', task)

  } catch (err) {
    console.log('..........err.............', err.stack)
  }

})();