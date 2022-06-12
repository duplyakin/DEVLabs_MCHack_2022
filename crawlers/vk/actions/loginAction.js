const selectors = require("../selectors");
const links = require("../links");
const models = require("../../models/models.js");
const puppeteer = require("puppeteer");
const utils = require("./utils");

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

class LoginAction {
    constructor(credentials_id) {
        this.credentials_id = credentials_id
    }

    async startBrowser() {
        //this.browser = await puppeteer.launch({ headless: false }) // test mode
        this.browser = await puppeteer.launch()
        this.context = await this.browser.createIncognitoBrowserContext()
        this.page = await this.context.newPage()

        // we don't set cookie in loginAction!
    }

    async closeBrowser() {
        await this.browser.close()
        this.browser.disconnect()
      }

    async setContext(context) {
        this.context = context
        this.page = await this.context.newPage()
    }

    _get_domain() {
        let current_url = this.page.url()

        // Exctract domain here in format: “www.linkedin.com”

        return (new URL(current_url)).hostname
    }

    async get_account() {
        let account = await models.Accounts.findOne({ _id: this.credentials_id });
        if(account == null) {
            throw MyExceptions.LoginActionError("Account with credentials_id: " + this.credentials_id + " not exists.");
        }
        return account;
    }

    async login_with_email() {

        /*
        await this.page.goto(links.SIGNIN_LINK, {
            waitUntil: 'load',
            timeout: 60000 // it may load too long! critical here
        })
        
        try {
            await this.page.waitForSelector(selectors.USERNAME_SELECTOR, { timeout: 5000 });
        } catch (err) {
            throw MyExceptions.LoginPageError('Login page is not available.');
        }*/

        let account = await this.get_account();
        if(account == null) {
            throw MyExceptions.LoginActionError("Account with credentials_id: " + this.credentials_id + " not exists.");
        }

        if(!account.login || !account.password) {
            throw MyExceptions.LoginActionError("Empty login or password in account.");
        }

        await this.page.click(selectors.USERNAME_SELECTOR);
        await this.page.keyboard.type(account.login);
        await this.page.click(selectors.PASSWORD_SELECTOR);
        await this.page.keyboard.type(account.password);
        await this.page.click(selectors.CTA_SELECTOR);
        await this.page.waitFor(1000);
    
        let is_phone = this.check_phone_page(this.page.url());
        if (is_phone) {
            await this.skip_phone(this.page);
        }
    }

    async is_logged() {
        /*
        await this.page.waitFor(2000)
        await this.page.goto(links.SIGNIN_LINK, {
            waitUntil: 'load',
            timeout: 60000 // it may load too long! critical here
        })

        await this.page.waitFor(7000) // puppeteer wait loading..
        */

        let current_url = this.page.url()
        if (current_url.includes(links.START_PAGE_SHORTLINK)) {
            // login success
            log.debug("LoginAction: Login success.")
            return true
        } else if (utils.check_block(current_url)) {
            // BAN here
            log.debug("LoginAction: Not logged - BAN here. current url: ", current_url)
            throw MyExceptions.ContextError("Can't goto url: " + current_url)
        }

        log.debug("LoginAction: Not logged. current url: ", current_url)
        // login failed
        return false;
    }

    async login() {

        // check - if we logged
        let logged = await this.is_logged()
        if (logged) {
            await utils.update_cookie(this.page, this.credentials_id)
            await this.page.close()
            return logged
        }

        // if not - try to login with login/password
        await this.login_with_email()
        logged = await this.is_logged()

        if (!logged) {
            throw MyExceptions.LoginActionError("Can't login")
        }

        await utils.update_cookie(this.page, this.credentials_id)
        await this.page.close()

        return logged
    }


    async skip_phone(page) {
        await page.waitForSelector(selectors.SKIP_PHONE_BTN_SELECTOR, { timeout: 5000 });
        await page.click(selectors.SKIP_PHONE_BTN_SELECTOR);
    }

    check_phone_page(url) {
        if (url.includes('phone')) {
            return true;
        }

        return false;
    }
}

module.exports = {
    LoginAction: LoginAction
}
