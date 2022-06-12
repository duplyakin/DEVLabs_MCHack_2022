const selectors = require("../selectors");
const action = require('./action.js');
const utils = require("./utils");

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

class SN_ScribeAction extends action.Action {
  constructor(cookies, credentials_id, url) {
    super(cookies, credentials_id)

    this.url = url
  }

  async scribe() {
    await super.gotoChecker(this.url)

    let result = {}
    let selector
    let selector_res

    result = await this.scribe_contact_info()

    await super.autoScroll(this.page)

    // location
    selector_res = await utils.check_success_selector(selectors.SN_LOCATION_SELECTOR, this.page)
    if(selector_res) {
      selector = selectors.SN_LOCATION_SELECTOR
      result.location = await this.page.evaluate((selector) => {
        return document.querySelector(selector).innerText
      }, selector)

      log.debug("SN_ScribeAction: location added")
    }

    // education
    selector_res = await utils.check_success_selector(selectors.SN_EDUCATION_SELECTOR, this.page)
    if(selector_res) {
      selector = selectors.SN_EDUCATION_SELECTOR
      result.education = await this.page.evaluate((selector) => {
        return document.querySelector(selector).innerText
      }, selector)

      log.debug("SN_ScribeAction: education added")
    }

    // basic linkedin url
    let linkedin = await this.page.evaluate(() => {
      let elems = document.querySelectorAll('code')
      for(let el of elems) {
        try {
          let res = JSON.parse(el.innerText)
          if(res.hasOwnProperty('flagshipProfileUrl')) {
            return res.flagshipProfileUrl
          }
        } catch(err) {}
      }
    })

    if(linkedin) {
      if(utils.get_search_url(linkedin) != '') {
        result.linkedin = linkedin.split(utils.get_search_url(linkedin))[0]
      } else {
        result.linkedin = linkedin
      }

      log.debug("SN_ScribeAction: basic linkedin url added")
    }

    // company informatiom
    // job title
    selector_res = await utils.check_success_selector(selectors.SN_JOB_SELECTOR, this.page)
    if(selector_res) {
      selector = selectors.SN_JOB_SELECTOR
      result.job_title = await this.page.evaluate((selector) => {
        return document.querySelector(selector).innerText
      }, selector)

      log.debug("SN_ScribeAction: job title added")
    }

    // company name
    selector_res = await utils.check_success_selector(selectors.SN_COMPANY_NAME_SELECTOR, this.page)
    if(selector_res) {
      selector = selectors.SN_COMPANY_NAME_SELECTOR
      result.company_name = await this.page.evaluate((selector) => {
        return document.querySelector(selector).innerText
      }, selector)

      log.debug("SN_ScribeAction: company_name:", result.company_name)
      
    } else if (await utils.check_success_selector(selectors.SN_COMPANY_NAME_SELECTOR_2, this.page)) {
      selector = selectors.SN_COMPANY_NAME_SELECTOR_2
      result.company_name = await this.page.evaluate((selector) => {
        return document.querySelectorAll(selector)[1].innerText
      }, selector)

      log.debug("SN_ScribeAction: company_name:", result.company_name)
    }

    // company linkedin page
    selector_res = await utils.check_success_selector(selectors.SN_JOB_LINK_SELECTOR, this.page)
    /*
    if(!selector_res) {
      // feature of SN - sometimes don't load company link and name
      await super.gotoChecker(this.url)
      await this.page.waitFor(5000)
      selector_res = await utils.check_success_selector(selectors.SN_JOB_LINK_SELECTOR, this.page)
    }

    if(!selector_res) {
      // feature of SN - sometimes don't load company link and name
      await super.gotoChecker(this.url)
      await this.page.waitFor(5000)
      selector_res = await utils.check_success_selector(selectors.SN_JOB_LINK_SELECTOR, this.page)
    }

    if(!selector_res) {
      // feature of SN - sometimes don't load company link and name
      await super.gotoChecker(this.url)
      await this.page.waitFor(5000)
      selector_res = await utils.check_success_selector(selectors.SN_JOB_LINK_SELECTOR, this.page)
    }
    */

    if(selector_res) {
      selector = selectors.SN_JOB_LINK_SELECTOR
      result.company_linkedin_page = await this.page.evaluate((selector) => {
        return document.querySelector(selector).href
      }, selector)

      log.debug("SN_ScribeAction: company_linkedin_page:", result.company_linkedin_page)

      await super.gotoChecker(result.company_linkedin_page)

      // company website on company SN linkedin page
      selector_res = await utils.check_success_selector(selectors.SN_JOB_SITE_SELECTOR, this.page)
      if(selector_res) {
        selector = selectors.SN_JOB_SITE_SELECTOR
        result.company_url = await this.page.evaluate((selector) => {
          return document.querySelector(selector).href
        }, selector)
      }
    }

    log.debug("SN_ScribeAction: result:", result)
    return result
  }


  async scribe_contact_info() {
    let result = {}
    let mySelector = ''
    log.debug("SN_ScribeAction: scribe_contact_info started")

    let selector_res = await utils.check_success_selector(selectors.SN_CONTACT_INFO_SELECTOR, this.page)
    if(!selector_res) {
      // can't find contact info selector
      log.debug("SN_ScribeAction: can't find contact info selector")
      return result
    }

    await this.page.$eval(selectors.SN_CONTACT_INFO_SELECTOR, elem => elem.click()) // page.click not working

    await this.page.waitFor(2000)

    // phone
    selector_res = await utils.check_success_selector(selectors.SN_CONTACT_INFO_PHONE_SELECTOR, this.page)
    if(selector_res) {
      mySelector = selectors.SN_CONTACT_INFO_PHONE_SELECTOR

      result.phone = await this.page.evaluate((mySelector) => {
        return document.querySelector(mySelector).innerText
      }, mySelector)

      log.debug("SN_ScribeAction: phone added")
    }

    // address
    selector_res = await utils.check_success_selector(selectors.SN_CONTACT_INFO_ADDRESS_SELECTOR, this.page)
    if(selector_res) {
      mySelector = selectors.SN_CONTACT_INFO_ADDRESS_SELECTOR

      result.address = await this.page.evaluate((mySelector) => {
        return document.querySelector(mySelector).innerText
      }, mySelector)

      log.debug("SN_ScribeAction: address added")
    }

    // email
    selector_res = await utils.check_success_selector(selectors.SN_CONTACT_INFO_EMAIL_SELECTOR, this.page)
    if(selector_res) {
      mySelector = selectors.SN_CONTACT_INFO_EMAIL_SELECTOR

      result.emails = await this.page.evaluate((mySelector) => {
        let emails = []
        let elements = document.querySelectorAll(mySelector)
        for(let elem of elements) {
          emails.push(elem.innerText)
        }

        return emails

      }, mySelector)

      log.debug("SN_ScribeAction: emails added")
    }

    // twitter
    selector_res = await utils.check_success_selector(selectors.SN_CONTACT_INFO_SOCIAL_SELECTOR, this.page)
    if(selector_res) {
      mySelector = selectors.SN_CONTACT_INFO_SOCIAL_SELECTOR

      let social = await this.page.evaluate((mySelector) => {
        let result = {}
        let elements = document.querySelectorAll(mySelector)

        if(elements != null && elements.length > 0) {

          for(let elem of elements) {
            if(elem.querySelector('span') != null && elem.querySelector('a') != null) {
              if(elem.querySelector('span').innerText.toLowerCase().includes('twitter')) {
                result.twitter = elem.querySelector('a').href
              }
              if(elem.querySelector('span').innerText.toLowerCase().includes('skype')) {
                result.skype = elem.querySelector('a').href
              }
              if(elem.querySelector('span').innerText.toLowerCase().includes('wechat')) {
                result.wechat = elem.querySelector('a').href
              }
              if(elem.querySelector('span').innerText.toLowerCase().includes('icq')) {
                result.icq = elem.querySelector('a').href
              }
              if(elem.querySelector('span').innerText.toLowerCase().includes('aim')) {
                result.aim = elem.querySelector('a').href
              }
              if(elem.querySelector('span').innerText.toLowerCase().includes('yahoo')) {
                result.yahoo = elem.querySelector('a').href
              }
              if(elem.querySelector('span').innerText.toLowerCase().includes('qq')) {
                result.qq = elem.querySelector('a').href
              }
              if(elem.querySelector('span').innerText.toLowerCase().includes('hangouts')) {
                result.hangouts = elem.querySelector('a').href
              }
            }
          }
        }

        return result
      }, mySelector)

      if(social != null) {
        if(social.twitter != null) {
          result.twitter = social.twitter
          log.debug("SN_ScribeAction: twitter added")
        }
        if(social.skype != null) {
          result.skype = social.skype
          log.debug("SN_ScribeAction: skype added")
        }
        if(social.wechat != null) {
          result.wechat = social.wechat
          log.debug("SN_ScribeAction: wechat added")
        }
        if(social.icq != null) {
          result.icq = social.icq
          log.debug("SN_ScribeAction: icq added")
        }
        if(social.aim != null) {
          result.aim = social.aim
          log.debug("SN_ScribeAction: aim added")
        }
        if(social.yahoo != null) {
          result.yahoo = social.yahoo
          log.debug("SN_ScribeAction: yahoo added")
        }
        if(social.qq != null) {
          result.qq = social.qq
          log.debug("SN_ScribeAction: qq added")
        }
        if(social.hangouts != null) {
          result.hangouts = social.hangouts
          log.debug("SN_ScribeAction: hangouts added")
        }
      }
    }

    // website
    selector_res = await utils.check_success_selector(selectors.SN_CONTACT_INFO_WEBSITE_SELECTOR, this.page)
    if(selector_res) {
      mySelector = selectors.SN_CONTACT_INFO_WEBSITE_SELECTOR

      let websites = await this.page.evaluate((mySelector) => {
        let websites = []
        let elements = document.querySelectorAll(mySelector)

        if(elements != null && elements.length > 0) {

          for(let elem of elements) {
            if(elem.querySelector('span') != null && elem.querySelector('a') != null) {
              websites.push( elem.querySelector('a').href )
            }
          }
        }

        return websites
      }, mySelector)

      if(websites != null) {
        result.websites = websites
        log.debug("SN_ScribeAction: websites added")
      }
    }


    // close contact info popup
    selector_res = await utils.check_success_selector(selectors.SN_CONTACT_INFO_CLOSE_SELECTOR, this.page)
    if(selector_res) {
      await this.page.click(selectors.SN_CONTACT_INFO_CLOSE_SELECTOR)
    } else {
      await super.gotoChecker(this.url)
    }

    await this.page.waitFor(5000)

    //log.debug("SN_ScribeAction: contact info scribed:", result)
    return result
  }
}

module.exports = {
  SN_ScribeAction: SN_ScribeAction
}
