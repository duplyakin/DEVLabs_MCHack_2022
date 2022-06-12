const selectors = require("../selectors");
const links = require("../links");
const action = require('./action.js');

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

class ConnectCheckAction extends action.Action {
  constructor(cookies, credentials_id, url) {
    super(cookies, credentials_id)

    this.url = url
  }

  async connectCheck() {
    await super.gotoChecker(this.url)

    // wait selector here
    let check_selector = await super.check_success_selector(selectors.CONNECT_DEGREE_SELECTOR)
    if(!check_selector) {
      log.debug("ConnectCheckAction: connection NOT found (selector not foumd): ", this.url)
      return false
    }

    await this.page.waitFor(1000)  // wait linkedIn loading process

    let selector = selectors.CONNECT_DEGREE_SELECTOR
    let connect = await this.page.evaluate((selector) => {
      return document.querySelector(selector) == null ? null : document.querySelector(selector).innerText
    }, selector)

    if (connect == null || connect == '') {
      log.debug("ConnectCheckAction: connection NOT found (selector result is NULL or empty): ", this.url)
      return false

    } else if (connect.includes("1")) {
      log.debug("ConnectCheckAction: connection found - success: ", connect)
      return true
    }

    log.debug("ConnectCheckAction: connection NOT found (not 1st degree): ", connect + " for " + this.url)
    return false
  }
}

module.exports = {
  ConnectCheckAction: ConnectCheckAction
}
