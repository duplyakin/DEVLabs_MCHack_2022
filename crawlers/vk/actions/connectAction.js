const selectors = require("../selectors");
const action = require('./action.js');
const utils = require("./utils");

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

class ConnectAction extends action.Action {
  constructor(cookies, credentials_id, url, template, data) {
    super(cookies, credentials_id)

    this.url = url
    this.template = template
    this.data = data
  }

  async connect() {
    await super.gotoChecker(this.url)

    const check = await this.connectCheck()
    if(check) {
      log.debug("ConnectAction: You are already connected with " + this.url)
      return true
    }

    await utils.close_msg_box(this.page)

    if(await utils.check_success_selector(selectors.MORE_BTN_SELECTOR, this.page)) {
      await this.page.click(selectors.MORE_BTN_SELECTOR)
    } else {
      log.debug("ConnectAction: MORE_BTN_SELECTOR not found for", this.url)
    }

    //await this.page.waitForSelector(selectors.CONNECT_SELECTOR)
    if (await this.page.$(selectors.CONNECT_SELECTOR) === null) {
      log.debug("ConnectAction: You can't connect ", this.url)

      // TODO: add logic for FOLLOW-UP (for famous contacts) and MESSAGE (for premium acc's)
      return false
    }
    await this.page.click(selectors.CONNECT_SELECTOR)

    // check - if CONNECT btm exists, but muted, then you have already sent request
    //await this.page.waitForSelector(selectors.ADD_MSG_BTN_SELECTOR)
    if (await this.page.$(selectors.ADD_MSG_BTN_SELECTOR) === null) {
      log.debug("ConnectAction: You have already sent request (or you can't) " + this.url)
      return true
    }
    await this.page.click(selectors.ADD_MSG_BTN_SELECTOR)

    // wait selector here
    await utils.check_success_selector(selectors.MSG_SELECTOR, this.page)
    await this.page.click(selectors.MSG_SELECTOR)

    let text = utils.formatMessage(this.template, this.data)

    await this.page.keyboard.type(text)
    await this.page.waitFor(2000)

    if (await this.page.$(selectors.SEND_BTN_DISABLED_SELECTOR) != null) {
      log.debug("ConnectAction: You can't connect (BTN DISABLED):", this.url)

      return false
    }

    await this.page.click(selectors.SEND_INVITE_TEXT_BTN_SELECTOR)

    // wait page here
    await this.page.waitFor(2000)
    await utils.check_success_page(this.url, this.page)

    return true
  }

  async connectCheck() {
    // wait selector here
    let check_selector = await utils.check_success_selector(selectors.CONNECT_DEGREE_SELECTOR, this.page)
    if(!check_selector) {
      log.debug("ConnectAction: connection NOT found (selector not foumd):", this.url)
      return false
    }

    await this.page.waitFor(1000)  // wait linkedIn loading process

    let selector = selectors.CONNECT_DEGREE_SELECTOR
    let connect = await this.page.evaluate((selector) => {
      return document.querySelector(selector) == null ? null : document.querySelector(selector).innerText
    }, selector)

    if (connect == null || connect == '') {
      log.debug("ConnectAction: connection NOT found (selector result is NULL or empty):", this.url)
      return false

    } else if (connect.includes("1")) {
      log.debug("ConnectAction: connection found - success:", connect)
      return true
    }

    log.debug("ConnectAction: connection NOT found (not 1st degree):", connect + " for " + this.url)
    return false
  }
}

module.exports = {
  ConnectAction: ConnectAction
}
