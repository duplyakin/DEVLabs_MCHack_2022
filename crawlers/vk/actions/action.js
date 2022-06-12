const puppeteer = require("puppeteer");
const LoginAction = require('./loginAction.js');
const links = require("../links");
const selectors = require("../selectors");
const models = require("../../models/models.js");

var log = require('loglevel').getLogger("o24_logger");

const MyExceptions = require('../../exceptions/exceptions.js');
const error_codes = require('../../exceptions/error_codes.js');
const utils = require("./utils.js");

class Action {
  constructor(cookies, credentials_id) {
    this.cookies = cookies
    this.credentials_id = credentials_id
  }


  async startBrowser() {
    //this.browser = await puppeteer.launch({ headless: false }) // test mode
    this.browser = await puppeteer.launch()
    this.context = await this.browser.createIncognitoBrowserContext()
    this.page = await this.context.newPage()
    
    //log.debug('cooooookiieeeess: ', this.cookies)
    if(this.cookies != null && Array.isArray(this.cookies) && this.cookies.length > 0) {
      await this.page.setCookie(...this.cookies)
    } else {
      log.error("action: Empty or invalid cookies for credentials_id:", this.credentials_id)
    }

    return this.browser
  }


  async closeBrowser() {
    await utils.update_cookie(this.page, this.credentials_id)

    await this.browser.close()
    this.browser.disconnect()

    return null
  }


  check_block(url) {
    if(!url) {
      throw new Error('Empty url in check_block.')
    }

    if(url.includes(links.BAN_LINK) || url.includes(links.CHALLENGE_LINK)) {
      // not target page here
      return true
    } else {
      // all ok
      return false
    }
  }


async check_success_selector(selector, page = this.page) {
  if(!selector) {
    throw new Error ('check_success_selector: Empty selector.')
  }

  try {
    await page.waitForSelector(selector, { timeout: 5000 })
    return true

  } catch(err) {
    
    if (this.check_block(page.url())) {
      throw MyExceptions.ContextError("Block happend: " + page.url())
    }

    return false
  }
}


  async check_success_page(required_url, page = this.page) {
    if(!required_url) {
      throw new Error ('check_success_page: Empty required_url.')
    }

    let current_url = page.url()

    if(current_url.includes(this.get_pathname(required_url))) {
      return true
    }

    if(this.check_block(page.url())) {
      throw MyExceptions.ContextError("Block happend.")
    }

    // uncknown page here
    throw new Error('Uncknowm page here: ', current_url)
    //return false
  }


  async close_msg_box(page = this.page) {
    if(page == null) {
      throw new Error ('Page not found.')
    }
    try {
      // close messages box !!! (XZ ETOT LINKED)
      await page.waitFor(10000) // wait linkedIn loading process
      await page.waitForSelector(selectors.CLOSE_MSG_BOX_SELECTOR, { timeout: 5000 })
      await page.click(selectors.CLOSE_MSG_BOX_SELECTOR)
      await page.waitFor(2000) // wait linkedIn loading process
    } catch (err) {
      log.debug("action.close_msg_box: CLOSE_MSG_BOX_SELECTOR not found.")
    }
  }


  // format message with data templates
  formatMessage(message, data) {
    if(message == null || message == '') {
      log.debug("action.formatMessage: Empty message.")
      return ''
    }

    if(data == null) {
      return message
    }

    let str = message
    for (var obj in data) {
      str = str.replace(new RegExp('{' + obj + '}', 'g'), data[obj])
    }

    str = str.replace(new RegExp('\{(.*?)\}', 'g'), '')
    return str
  }


  // cut user unique pathname in url
  get_pathname_url(url) {
    if(!url || !url.includes('linkedin') || !url.includes('/in/')) {
      log.error("action.get_pathname_url incorrect url format:", url)
      return url
    }

    var pathname = new URL(url).pathname
    pathname = pathname.split( '/' )[2]

    log.debug("action.get_pathname_url:", pathname)
    return pathname
  }


  // cut pathname in url
  get_pathname(url) {
    if(!url || !url.includes('linkedin')) {
      log.error("action.get_pathname incorrect url format:", url)
      return url
    }

    var pathname = new URL(url).pathname

    log.debug("action.get_pathname:", pathname)
    return pathname
  }


  // do 1 trie to goto URL or goto login
  async gotoChecker(url, page = this.page) {
    //log.debug('gotoChecker - url: ', url)
    if(!url) {
      throw new Error('Empty url.')
    }
    try {
      let current_url = page.url()
      const short_url = this.get_pathname(url)

      // save cookie if it was not new page
      if(current_url && !current_url.includes('about:blank')) {
        await utils.update_cookie(this.page, this.credentials_id)
      }

      await page.goto(url, {
        waitUntil: 'load',
        //waitUntil: 'domcontentloaded',
        timeout: 180000 // it may load too long! critical here
      })

      await page.waitFor(10000) // puppeteer wait loading...
      current_url = page.url()

      if (!current_url.includes(short_url)) {
        // Sales Navigator access
        if (current_url.includes('guest_login_sales_nav')) {
          log.debug('gotoChecker - Sales Navigator unreachable: ', current_url)
          throw MyExceptions.SN_access_error("gotoChecker - Sales Navigator unreachable: " + current_url)

        // Login
        } else if (current_url.includes('login') || current_url.includes('signup') || current_url.includes("authwall")) {
          let loginAction = new LoginAction.LoginAction(this.credentials_id)
          await loginAction.setContext(this.context)

          let result = await loginAction.login()
          if (result) {
            await page.goto(url, {
              waitUntil: 'load',
              timeout: 180000 // it may load too long! critical here
            })      
          }

        // Unexpectable page
        } else {
          log.error('gotoChecker - current url: ', current_url)
          log.error('gotoChecker - required url: ', url)
          throw new Error("gotoChecker - We cann't go to page, we got: " + current_url)
        }
      }
    } catch (err) {
      log.error('gotoChecker - current page: ', page.url())
      log.error('gotoChecker - error: ', err.stack)

      if(this.check_block(page.url())) {
        throw MyExceptions.ContextError("Block happend.")
      }

      if(err.code != null && err.code == error_codes.SN_ACCESS_ERROR) {
        throw MyExceptions.SN_access_error("gotoChecker - Sales Navigator unreachable")
      }

      if(err.toString().includes('ERR_TOO_MANY_REDIRECTS')) {
        /*// TODO
        log.error('--------------------------------------')
        let account = await models.Accounts.findOne({ _id: this.credentials_id }, function (err_db, res) {
          if (err_db) throw MyExceptions.MongoDBError('MongoDB find account err: ' + err_db)
        })
      
        if (account == null) {
          throw new Error("gotoChecker: Account not found with credentials_id:", this.credentials_id)
        }

        if(account.login != null && account.password != null) {
          let loginAction = new LoginAction.LoginAction(this.credentials_id)
          await loginAction.setContext(this.context)
  
          let result = await loginAction.login()
          if (result) {
            await page.goto(url)
          }
        } else {
          throw MyExceptions.TooManyRedirectsError("Relogin required.")
        }*/
        throw MyExceptions.TooManyRedirectsError("Relogin required.")
      }

      throw new Error('gotoChecker error: ', err)
    }
  }


  async autoScroll(page) {
    await page.evaluate(async () => {
      await new Promise((resolve, reject) => {
        var totalHeight = 0;
        var distance = 100;
        var timer = setInterval(() => {
          var scrollHeight = document.body.scrollHeight;
          window.scrollBy(0, distance);
          totalHeight += distance;

          if (totalHeight >= scrollHeight) {
            clearInterval(timer);
            resolve();
          }
        }, 100);
      });
    });
  }

}

module.exports = {
  Action: Action
}
