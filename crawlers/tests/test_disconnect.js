const puppeteer = require('puppeteer');
const models = require("../models/shared.js");

// test running

(async () => {
  console.log("..... disconnecct-close test started: .....", __filename);
  browser = await puppeteer.launch({ headless: false }); // test mode
  //browser = await puppeteer.launch();
  context = await browser.createIncognitoBrowserContext();
  page = await context.newPage();

  
  browser.disconnect();
  await browser.close();
/*
  await browser.close();
  browser.disconnect();

  let task = await models.TaskQueue.findOneAndUpdate({
    action_key: 'linkedin-connect',
  }, {
    action_key: 'olololo'
  }, {
    new: true,
    upsert: false
  });

  console.log('task: ', task);
  */

})();
