const selectors = require("../selectors");
const action = require('./action.js');
const utils = require('./utils.js');

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

class SN_SearchAction extends action.Action {
  constructor(cookies, credentials_id, searchUrl, interval_pages) {
    super(cookies, credentials_id);

    this.searchUrl = searchUrl;
    this.interval_pages = interval_pages;
  }

  async search() {
    if (!this.searchUrl) {
      throw new Error('Empty search url.')
    }

    if (!this.searchUrl) {
      throw new Error('Empty search url.')
    }

    if (this.interval_pages == null || this.interval_pages < 1) {
      throw new Error('Incorrect interval_pages:', this.interval_pages)
    }

    await super.gotoChecker(this.searchUrl)

    if (await this.page.$(selectors.SN_CLOSE_HELP_SELECTOR) != null) {
      await this.page.click(selectors.SN_CLOSE_HELP_SELECTOR) // critical
    }
    
    let currentPage = 1;
    let result_data = {
      code: 0,
      if_true: true,
      data: {
        arr: [],
        link: this.searchUrl
      }
    };

    try {

      let mySelectors = {
        selector1: selectors.SN_SEARCH_ELEMENT_SELECTOR,
        selector2: selectors.SN_LINK_SELECTOR,
        selector3: selectors.SN_FULL_NAME_SELECTOR,
        selector4: selectors.SN_DEGREE_SELECTOR,

        selector5: selectors.SN_JOB_SELECTOR,
        selector6: selectors.SN_JOB_LINK_SELECTOR,
        selector7: selectors.SN_JOB_NAME_SELECTOR,
      };

      while (currentPage <= this.interval_pages) {
        await utils.autoScroll(this.page)

        // wait selector here
        //await super.check_success_selector(selectors.SEARCH_ELEMENT_SELECTOR, this.page)

        if (await this.page.$(selectors.SN_SEARCH_ELEMENT_SELECTOR) == null) {
          // TODO: add check-selector for BAN page
          // perhaps it was BAN
          result_data.code = MyExceptions.SearchActionError().code;
          result_data.raw = MyExceptions.SearchActionError('something went wrong - NEXT_PAGE_SELECTOR not found! page.url: ' + this.page.url()).error;
          log.error('Search_SN_action: something went wrong - NEXT_PAGE_SELECTOR not found! page.url: ', this.page.url());
          break;
        }

        let newData = await this.page.evaluate((mySelectors) => {

          let results = []
          let items = document.querySelectorAll(mySelectors.selector1)

          for (let item of items) {
            // don't add: noName LinkedIn members and 1st degree connections
            if (item.querySelector(mySelectors.selector2) != null && !item.querySelector(mySelectors.selector3).innerText.toLowerCase().includes('linkedin') && (item.querySelector(mySelectors.selector4) == null || !item.querySelector(mySelectors.selector4).innerText.includes('1'))) {
              let full_name = item.querySelector(mySelectors.selector3)
              let job_title = item.querySelector(mySelectors.selector5)
              let company_linkedin_page = item.querySelector(mySelectors.selector6)
              let company_name = item.querySelector(mySelectors.selector7)

              let result = {}

              result.linkedin = item.querySelector(mySelectors.selector2).href

              if (full_name != null) {
                full_name = full_name.innerText
                if (full_name.includes(' ')) {
                  result.first_name = full_name.substr(0, full_name.indexOf(' '))
                  result.last_name = full_name.substr(full_name.indexOf(' ') + 1)
                } else {
                  result.first_name = full_name
                }
              }

              if (job_title != null) {
                result.job_title = job_title.innerText
              }

              if (company_linkedin_page != null) {
                result.company_linkedin_page = company_linkedin_page.href
              }

              if (company_name != null) {
                result.company_name = company_name.innerText
              }

              results.push(result)
            }
          }
          return results
        }, mySelectors)

        result_data.data.arr = result_data.data.arr.concat(newData);
        result_data.data.link = this.page.url();

        if (await this.page.$(selectors.SN_NEXT_PAGE_SELECTOR) == null) {
          // TODO: add check-selector for BAN page
          // perhaps it was BAN
          result_data.code = MyExceptions.SearchActionError().code;
          result_data.raw = MyExceptions.SearchActionError('something went wrong - SN_NEXT_PAGE_SELECTOR not found! page.url: ' + this.page.url()).error;
          log.error('Search_SN_action: something went wrong - SN_NEXT_PAGE_SELECTOR not found! page.url: ', this.page.url());
          break;
        }

        // wait selector here
        //await super.check_success_selector(selectors.NEXT_PAGE_SELECTOR, this.page, result_data);

        await this.page.click(selectors.SN_NEXT_PAGE_SELECTOR)
        await utils.update_cookie(this.page, this.credentials_id)
        await this.page.waitFor(2000)

        // check current page
        let current_page_search_url = utils.get_search_url(this.page.url())
        let previous_page_search_url = utils.get_search_url(result_data.data.link)
        if(result_data.data.link != null && current_page_search_url.includes(previous_page_search_url)) {
          // all awailable pages has been scribed
          result_data.code = 1000
          //log.debug('Search_SN_action: this.page.url():', this.page.url())
          //log.debug('Search_SN_action: result_data.data.link:', result_data.data.link)
          log.debug('Search_SN_action: All awailable pages has been scribed!')
          break
        }
        // here we have to check BAN page
        result_data.data.link = this.page.url() // we have to send NEXT page link in task

        currentPage++
      }
    } catch (err) {
      //await this.page.waitFor(200000)
      log.error("Search_SN_action: we catch something strange:", err)
      result_data.code = -1000
      result_data.data = JSON.stringify(result_data.data)
      return result_data
    }

    //log.debug("Search_SN_action: Reult Data:", result_data)
    //log.debug("Search_SN_action: Users Data:", result_data.data.arr)
    log.debug("Search_SN_action: contacts scribed:", result_data.data.arr.length)
    result_data.data = JSON.stringify(result_data.data)
    return result_data
  }
}

module.exports = {
  SN_SearchAction: SN_SearchAction
}
