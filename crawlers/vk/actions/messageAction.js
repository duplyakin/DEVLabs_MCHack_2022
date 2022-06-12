const selectors = require("../selectors");
const action = require('./action.js');
const utils = require("./utils");

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

class MessageAction extends action.Action {
  constructor(cookies, credentials_id, url, data, template) {
    super(cookies, credentials_id)

    this.url = url
    this.data = data
    this.template = template
  }

  async message() {
    await super.gotoChecker(this.url)
    let selector_res

    const page = await this.context.newPage() // feature (critical)
    await super.gotoChecker(this.url, page)

    //TODO: add logic for 'closed' for message accounts

    await super.close_msg_box(page)

    if (await this.page.$(selectors.WRITE_MSG_BTN_SELECTOR) == null) {
      log.debug("MessageAction: You can't write messages to", this.url)
      return false 
    }

    // wait selectors here
    selector_res = await utils.check_success_selector(selectors.WRITE_MSG_BTN_SELECTOR, page)
    if(!selector_res) {
      log.error("MessageAction: WRITE_MSG_BTN_SELECTOR not found for:", this.url)
      return false
    }

    await page.click(selectors.WRITE_MSG_BTN_SELECTOR)

    selector_res = await utils.check_success_selector(selectors.MSG_BOX_SELECTOR, page)
    if(!selector_res) {
      log.error("MessageAction: MSG_BOX_SELECTOR not found for:", this.url)
      return false
    }

     selector_res = await utils.check_success_selector(selectors.SEND_MSG_BTN_SELECTOR, page)
     if(!selector_res) {
      log.error("MessageAction: SEND_MSG_BTN_SELECTOR not found for:", this.url)
      return false
    }

    const text = utils.formatMessage(this.template, this.data)

    await page.waitFor(2000)
    await page.click(selectors.MSG_BOX_SELECTOR)
    await page.waitFor(2000)
    await page.keyboard.type(text)
    await page.waitFor(2000) // wait untill SEND button become active
    await page.waitForSelector(selectors.SEND_MSG_BTN_SELECTOR, { timeout: 5000 })
    await page.waitFor(2000) // wait untill SEND button become active
    await page.click(selectors.SEND_MSG_BTN_SELECTOR)
    //await page.waitFor(100000) // to see result

    // wait page here
    await this.page.waitFor(2000)
    await super.check_success_page(this.url, page)

    return true
  }
}

module.exports = {
  MessageAction: MessageAction
}
