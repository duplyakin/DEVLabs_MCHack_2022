const selectors = require("../selectors");
const action = require('./action.js');
const utils = require("./utils.js");

const MyExceptions = require('../../exceptions/exceptions.js');
var log = require('loglevel').getLogger("o24_logger");

class SearchAction extends action.Action {
  constructor(cookies, credentials_id, searchUrl, interval_pages) {
    super(cookies, credentials_id);

    this.searchUrl = searchUrl;
    this.interval_pages = interval_pages;
  }

  async search() {
    if (!this.searchUrl) {
      throw new Error('Empty search url.')
    }

    if (this.interval_pages == null || this.interval_pages < 1) {
      throw new Error('Incorrect interval_pages:', this.interval_pages)
    }
    
    await super.gotoChecker(this.searchUrl);

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
        selector1: selectors.SEARCH_ELEMENT_SELECTOR,
        selector2: selectors.LINK_SELECTOR,
        selector3: selectors.FULL_NAME_SELECTOR,
        selector4: selectors.DEGREE_SELECTOR,
        selector5: selectors.SEARCH_JOB_SELECTOR,
      };

      while (currentPage <= this.interval_pages) {
        await utils.autoScroll(this.page)

        // wait selector here
        //await super.check_success_selector(selectors.SEARCH_ELEMENT_SELECTOR, this.page);

        if (await this.page.$(selectors.SEARCH_ELEMENT_SELECTOR) == null) {
          // TODO: add check-selector for BAN page
          // perhaps it was BAN
          result_data.code = MyExceptions.SearchActionError().code;
          result_data.raw = MyExceptions.SearchActionError('something went wrong - NEXT_PAGE_SELECTOR not found! page.url: ' + this.page.url()).error;
          log.error('SearchAction: something went wrong - NEXT_PAGE_SELECTOR not found! page.url: ', this.page.url());
          break;
        }

        let newData = await this.page.evaluate((mySelectors) => {

          let results = [];
          let items = document.querySelectorAll(mySelectors.selector1);

          for(let item of items) {
            // don't add: noName LinkedIn members and 1st degree connections
            if (item.querySelector(mySelectors.selector2) != null && !item.querySelector(mySelectors.selector3).innerText.toLowerCase().includes('linkedin') && (item.querySelector(mySelectors.selector4) == null || !item.querySelector(mySelectors.selector4).innerText.includes('1'))) {
              let full_name = item.querySelector(mySelectors.selector3)
              let full_job = document.querySelector(mySelectors.selector5)
              
              let result = {}

              result.linkedin = item.href

              if(full_name != null) {
                full_name = full_name.innerText
                if(full_name.includes(' ')) {
                  result.first_name = full_name.substr(0, full_name.indexOf(' '))
                  result.last_name = full_name.substr(full_name.indexOf(' ') + 1)
                } else {
                  result.first_name = full_name
                }
              }

              if(full_job != null) {
                full_job = full_job.innerText // -> "Текущая должность: Product Marketing Manager – Morningstar"
                full_job = full_job.split(': ')[1]  // -> "Product Marketing Manager – Morningstar"
                if(full_job.includes('–')) {
                  result.job_title = full_job.substr(0, full_job.indexOf('–')) // -> "Product Marketing Manager "
                  result.company_name = full_job.substr(full_job.indexOf('–') + 2) // -> "Morningstar"
                } else {
                  result.job_title = full_job // ? or new param
                }
              }

              results.push(result)
            }
          }
          return results;
        }, mySelectors);
        result_data.data.arr = result_data.data.arr.concat(newData);
        result_data.data.link = this.page.url();

        if (await this.page.$(selectors.NEXT_PAGE_SELECTOR) == null) {
          // TODO: add check-selector for BAN page
          // perhaps it was BAN
          result_data.code = MyExceptions.SearchActionError().code;
          result_data.raw = MyExceptions.SearchActionError('something went wrong - NEXT_PAGE_SELECTOR not found! page.url: ' + this.page.url()).error;
          log.error('SearchAction: something went wrong - NEXT_PAGE_SELECTOR not found! page.url: ', this.page.url());
          break;
        }

        // wait selector here
        //await super.check_success_selector(selectors.NEXT_PAGE_SELECTOR, this.page, result_data);

        if (await this.page.$(selectors.NEXT_PAGE_MUTED_SELECTOR) != null) {
          // all awailable pages has been scribed
          result_data.code = 1000
          log.debug('SearchAction: All awailable pages has been scribed!')
          break
        }

        await this.page.click(selectors.NEXT_PAGE_SELECTOR)
        await utils.update_cookie(this.page, this.credentials_id)
        await this.page.waitFor(2000) // critical here!?
        // here we have to check BAN page
        result_data.data.link = this.page.url() // we have to send NEXT page link in task

        currentPage++
      }
    } catch (err) {
      log.error("SearchAction: we catch something strange:", err)
      result_data.code = -1000
      result_data.data = JSON.stringify(result_data.data)
      return result_data
    }

    //log.debug("SearchAction: Reult Data: ", result_data)
    //log.debug("SearchAction: Users Data: ", result_data.data.arr)
    log.debug("SearchAction: contacts scribed:", result_data.data.arr.length)
    result_data.data = JSON.stringify(result_data.data);
    return result_data;
  }
}

module.exports = {
  SearchAction: SearchAction
}
