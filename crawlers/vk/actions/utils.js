const puppeteer = require("puppeteer");
const LoginAction = require('./loginAction.js');
const links = require("../links");
const selectors = require("../selectors");
const models = require("../../models/models.js");

var log = require('loglevel').getLogger("o24_logger");

const MyExceptions = require('../../exceptions/exceptions.js');


async function get_current_cookie(page) {
    // Get Session Cookies
    const newCookies = await page.cookies()
    if (!newCookies || !Array.isArray(newCookies) || newCookies.length == 0) {
        log.error("utils: Can't get cookie (or empty).")
        throw new Error("utils: Can't get cookie (or empty).")
    }

    //log.debug("utils: cookie:", newCookies)
    return newCookies
}


async function update_cookie(page, credentials_id) {
    const new_cookies = await get_current_cookie(page)

    let new_expires = 0
    for(let item of new_cookies) {
        if (item.name === 'li_at') {
            new_expires = item.expires
            break
        }
    }

    const account = await models.Accounts.findOneAndUpdate({ _id: credentials_id }, { expires: new_expires, cookies: new_cookies }, { upsert: false }, function (err, res) {
        if (err) throw MyExceptions.MongoDBError('MongoDB find Account err: ' + err)
    })

    if(account == null) {
        throw new Error("utils: Account with credentials_id: " + credentials_id + " not exists.")
    }

    log.debug("utils: Cookies updated.")
}


function check_block(url) {
    if (!url) {
        throw new Error('Empty url in check_block.')
    }

    return ((url.includes(links.BAN_LINK) || url.includes(links.CHALLENGE_LINK)) ? true : false)
}


async function check_success_selector(selector, page) {
    if (!selector) {
        throw new Error('Empty selector.')
    }

    try {
        await page.waitForSelector(selector, { timeout: 5000 })
        return true

    } catch (err) {

        if (this.check_block(page.url())) {
            throw MyExceptions.ContextError("Block happend: " + page.url())
        }

        return false
    }
}


async function check_success_page(required_url, page) {
    if (!required_url) {
        throw new Error('Empty required_url.')
    }

    let current_url = page.url()

    if (current_url.includes(get_pathname_url(required_url))) {
        return true
    }

    if (check_block(page.url())) {
        throw MyExceptions.ContextError("Block happend.")
    }

    // uncknown page here
    throw new Error('Uncknowm page here: ', current_url)
    //return false
}


async function close_msg_box(page) {
    if (page == null) {
        throw new Error('Page not found.')
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
function formatMessage(message, data) {
    if (message == null || message == '') {
        log.debug("action.formatMessage: Empty message.")
        return ''
    }

    if (data == null) {
        return message
    }

    let str = message
    for (var obj in data) {
        str = str.replace(new RegExp('{' + obj + '}', 'g'), data[obj])
    }

    str = str.replace(new RegExp('\{(.*?)\}', 'g'), '')
    return str
}


// cut pathname in url
function get_pathname_url(url) {
    if (!url || !url.includes('linkedin')) {
        log.error("utils get_pathname_url incorrect url format:", url)
        return url
    }

    var pathname = new URL(url).pathname

    //log.debug("utils get_pathname_url:", pathname)
    return pathname
}


// cut search in url
function get_search_url(url) {
    if (!url || !url.includes('linkedin')) {
        log.error("utils get_search_url incorrect url format:", url)
        return url
    }

    var search = new URL(url).search

    //log.debug("utils get_search_url:", search)
    return search
}


// cut hostname in url
function get_hostname_url(url) {
    if (!url || !url.includes('linkedin')) {
        log.error("utils get_hostname_url incorrect url format:", url)
        return url
    }

    var hostname = new URL(url).hostname

    //log.debug("utils get_hostname_url:", hostname)
    return hostname
}


// do 1 trie to goto URL or goto login
async function gotoChecker(context, page, credentials_id, url) {
    //log.debug('gotoChecker - url: ', url)
    if (!context) {
        throw new Error('gotoChecker - Empty context.')
    }
    if (!page) {
        throw new Error('gotoChecker - Empty page.')
    }
    if (!credentials_id) {
        throw new Error('gotoChecker - Empty credentials_id.')
    }
    if (!url) {
        throw new Error('gotoChecker - Empty url.')
    }
    try {
        await page.goto(url, {
            waitUntil: 'load',
            //waitUntil: 'domcontentloaded',
            timeout: 30000 // it may load too long! critical here
        })

        await page.waitFor(7000) // puppeteer wait loading..

        let current_url = page.url()

        let short_url = get_pathname_url(url)

        if (!current_url.includes(short_url)) {
            if (current_url.includes('login') || current_url.includes('signup') || current_url.includes("authwall")) {

                let loginAction = new LoginAction.LoginAction(credentials_id)
                await loginAction.setContext(context)

                let result = await loginAction.login()
                if (result) {
                    await page.goto(url)
                }
            } else {
                log.error('gotoChecker - current url: ', current_url)
                log.error('gotoChecker - required url: ', url)
                throw new Error("gotoChecker - We cann't go to page, we got: " + current_url)
            }
        }
    } catch (err) {
        log.error('gotoChecker - current page: ', page.url())
        log.error('gotoChecker - error: ', err.stack)

        if (this.check_block(page.url())) {
            throw MyExceptions.ContextError("Block happend.");
        }

        throw new Error('gotoChecker error: ', err);
    }
}


// get new page after click link
async function clickAndWaitForTarget (clickSelector, page, browser) {
    const pageTarget = page.target(); //save this to know that this was the opener
    await page.click(clickSelector); //click on a link
    const newTarget = await browser.waitForTarget(target => target.opener() === pageTarget); //check that you opened this page, rather than just checking the url
    const newPage = await newTarget.page(); //get the page object
    // await newPage.once("load",()=>{}); //this doesn't work; wait till page is loaded
    await newPage.waitForSelector("body"); //wait for page to be loaded
  
    return newPage;
  }


async function autoScroll(page) {
    await page.evaluate(async () => {
      await new Promise((resolve, reject) => {
        var totalHeight = 0
        var distance = 100

        var timer = setInterval(() => {
          var scrollHeight = document.body.scrollHeight
          window.scrollBy(0, distance)
          totalHeight += distance

          if (totalHeight >= scrollHeight) {
            clearInterval(timer)
            resolve()
          }
        }, 100)
      })
    })
  }


async function autoScroll_modal(page, element_class) {
    if(element_class == null) {
        return
    }
    await page.evaluate(async (element_class) => {
        await new Promise((resolve, reject) => {
            var totalHeight = 0
            var distance = 100
            let element = document.getElementsByClassName(element_class)[0]

            var timer = setInterval(() => {
                var scrollHeight = element.scrollHeight
                element.scrollBy(0, distance)
                totalHeight += distance

                if (totalHeight >= scrollHeight) {
                    clearInterval(timer)
                    resolve()
                }
            }, 500)
        })
    }, element_class)
}


module.exports = {
    clickAndWaitForTarget: clickAndWaitForTarget,
    autoScroll: autoScroll,
    autoScroll_modal: autoScroll_modal,
    gotoChecker: gotoChecker,
    get_pathname_url: get_pathname_url,
    get_search_url: get_search_url,
    get_hostname_url: get_hostname_url,
    formatMessage: formatMessage,
    close_msg_box: close_msg_box,
    check_success_page: check_success_page,
    check_success_selector: check_success_selector,
    check_block: check_block,
    update_cookie: update_cookie,
    get_current_cookie: get_current_cookie,
}
