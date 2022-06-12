const selectors = require("../selectors");
const action = require('./action.js');

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

class MessageCheckAction extends action.Action {
  constructor(cookies, credentials_id, url) {
    super(cookies, credentials_id);

    // CONNECT URL
    this.url = url;
  }

  async messageCheck() {
    await super.gotoChecker(this.url);

    await super.close_msg_box(this.page);

    if (await this.page.$(selectors.WRITE_MSG_BTN_SELECTOR) === null) {
      log.debug('MessageCheckAction: You can\'t write messages to ' + this.url);
      return { code: 0, if_true: false }; // TODO: send (code = ...) here in result_data
    }

    await this.page.waitForSelector(selectors.WRITE_MSG_BTN_SELECTOR, { timeout: 5000 });

    await this.page.click(selectors.WRITE_MSG_BTN_SELECTOR);
    await this.page.waitFor(10000) // wait linkedin loading process

    // wait selector here
    await super.check_success_selector(selectors.LAST_MSG_LINK_SELECTOR);


    let short_url = super.get_pathname_url(this.url)

    if(short_url == '') {
      log.error("MessageCheckAction: empty short_url:", short_url + " prospect full url: " + this.url)
      throw MyExceptions.MessageCheckActionError('Empty short_url: ' + short_url + " prospect full url: " + this.url)
    }

    let mySelectors = {
      selector1: selectors.LAST_MSG_LINK_SELECTOR,
      selector2: selectors.LAST_MSG_SELECTOR,
      short_url: short_url,
    };
    /*
    let lastSender = await this.page.evaluate((mySelectors) => {
      let sender_link = Array.from(document.querySelectorAll(mySelectors.selector1)).map(el => (el.href));
      let text = Array.from(document.querySelectorAll(mySelectors.selector2)).map(el => (el.innerText));
      return { sender_link: sender_link.pop(), text: text.pop() };
    }, mySelectors);
    
    let short_url = super.get_pathname_url(this.url)

    if(lastSender.sender_link == null) {
      log.debug("MessageCheckAction: empty message story:", lastSender);
      return { message: '' };
    }
    
    if (lastSender.sender_link.includes(short_url) && short_url != '') {
      log.debug("MessageCheckAction: new message:", lastSender);
      if(lastSender.text == null || lastSender.text == '') {
        log.debug("MessageCheckAction: STRANGER THINGS - empty reply:", lastSender);
        return { message: '' };
      }
      return { message: lastSender.text };
    }*/

    let result = { code: 0, if_true: false }

    result = await this.page.evaluate((mySelectors) => {
      let links = Array.from(new Set(document.querySelectorAll(mySelectors.selector1))).map(el => (el.href)) // 'Set' gets only unique values
      let messages = Array.from(document.querySelectorAll(mySelectors.selector2)).map(el => (el.innerText))

      if(links.length == 0 || links == null) {
        return { code: 0, if_true: false }
      }

      if(messages.length == 0 || messages == null) {
        return { code: 0, if_true: false }
      }

      for(let link of links) {
        if(link.includes(mySelectors.short_url)) {
          return { code: 2000, if_true: true, data: JSON.stringify(messages.pop()) }
        }
      }

      return { code: 0, if_true: false }
      
    }, mySelectors)

    //log.debug("MessageCheckAction result: ", result)

    return result
  }

}

module.exports = {
  MessageCheckAction: MessageCheckAction
}
