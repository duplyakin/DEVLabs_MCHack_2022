const selectors = require("../selectors");
const action = require('./action.js');

const MyExceptions = require('../../exceptions/exceptions.js');
const utils = require("./utils");
var log = require('loglevel').getLogger("o24_logger");

class Post_engagement_action extends action.Action {
    constructor(cookies, credentials_id, url) {
        super(cookies, credentials_id)

        this.url = url
    }

    async engagement() {
        if (!this.url) {
            throw new Error('Empty post url.')
        }

        if (!utils.get_pathname_url(this.url).includes('posts')) {
            throw new Error('Incorrect post url:' + this.url) // add custom error here
        }

        await super.gotoChecker(this.url)

        var result_data = {
            code: 0,
            if_true: true,
            data: []
        }

        try {
            await utils.close_msg_box(this.page)

            // get post general info
            let general_info = await this._get_general_info()

            // get likers
            let likes_data = await this._get_likers(general_info)
            result_data.data = likes_data

            // get comenters
            let comments_data = await this._get_commenters(general_info)

            // remove not unique users from comments
            var unique_linkedin = {}
            var not_unique_linkedin = {}
            
            comments_data = comments_data.filter((item) => {
                // remove
                var linkedin = item.linkedin
                var res = unique_linkedin.hasOwnProperty(linkedin) ? false : true

                // remove author
                if(res && !item.linkedin.includes(utils.get_pathname_url(general_info.post_author_link))) {
                    unique_linkedin[linkedin] = true
                    return true
                } else {
                    not_unique_linkedin[linkedin] = true
                }
            })

            result_data.data = result_data.data.concat(comments_data)

            log.debug("Post_engagement_action: commenters unique_linkedin: ", Object.keys(unique_linkedin).length)
            log.debug("Post_engagement_action: commenters not_unique_linkedin: ", Object.keys(not_unique_linkedin).length)
            //log.debug("Post_engagement_action: commenters not_unique_linkedin: ", not_unique_linkedin)

            //log.debug("Post_engagement_action: total contacts scribed:", result_data.data)
            log.debug("Post_engagement_action: total contacts scribed:", result_data.data.length)

            // remove not unique users and set for them double tag ['like', 'comment']
            unique_linkedin = {}
            not_unique_linkedin = {}
            
            result_data.data = result_data.data.filter((item) => {
                // remove
                var linkedin = item.linkedin
                var res = unique_linkedin.hasOwnProperty(linkedin) ? false : true

                // remove author
                if(res && !item.linkedin.includes(utils.get_pathname_url(general_info.post_author_link))) {
                    unique_linkedin[linkedin] = item.tags[0]
                    return true
                } else {
                    not_unique_linkedin[linkedin] = true
                }
            }).map((item) => {
                // add tags
                if(not_unique_linkedin.hasOwnProperty(item.linkedin)) {
                    if(item.tags[0] == 'like') {
                        item.tags.push('comment')
                    } else {
                        item.tags.push('like')
                    }
                }
                return item
            })

            log.debug("Post_engagement_action: total unique_linkedin: ", Object.keys(unique_linkedin).length)
            log.debug("Post_engagement_action: total not_unique_linkedin: ", Object.keys(not_unique_linkedin).length)

            //log.debug("Post_engagement_action: total not_unique_linkedin: ", not_unique_linkedin)

        } catch (err) {
            log.error("Post_engagement_action: error: ", err.stack)
            result_data.code = -1000

        } finally {
            //log.debug("Post_engagement_action: Users Data: ", result_data.data)
            log.debug("Post_engagement_action: contacts scribed:", result_data.data.length)
            result_data.data = JSON.stringify(result_data.data)
            return result_data
        }
    }


    async _get_general_info() {
        let general_info = {
            post_url: this.url
        }

        let evaluate_data = {
            selector1: selectors.POST_AUTHOR_NAME_SELECTOR,
            selector2: selectors.POST_AUTHOR_LINK_SELECTOR,
        }

        let post = await this.page.evaluate((evaluate_data) => {
            let result = {
                author_name: document.querySelector(evaluate_data.selector1).innerText,
                author_link: document.querySelector(evaluate_data.selector2).href,
            }
            return result
        }, evaluate_data)

        general_info.post_author_name = post.author_name
        general_info.post_author_link = post.author_link

        log.debug("Post_engagement_action: general_info:", general_info)
        return general_info
    }


    async _get_likers(general_info) {
        let likes_data = []

        let check_selector = await this.page.$(selectors.POST_LIKERS_MODAL_BTN_SELECTOR)
        if (check_selector != null) {
            // a lot of likes - open modal
            log.debug("Post_engagement_action: _get_likers modal started")
            // open modal
            await this.page.click(selectors.POST_LIKERS_MODAL_BTN_SELECTOR)
            await this.page.waitFor(2000)

            await utils.autoScroll_modal(this.page, selectors.POST_LIKERS_MODAL_SELECTOR)

            let evaluate_data = {
                general_info: general_info,
                selector1: selectors.POST_LIKERS_MODAL_ELEMENT_SELECTOR,
                selector2: selectors.POST_LIKERS_MODAL_NAME_SELECTOR,
                selector3: selectors.POST_LIKERS_MODAL_LINK_SELECTOR,
                selector4: selectors.POST_LIKERS_MODAL_DEGREE_SELECTOR,
                selector5: selectors.POST_LIKERS_MODAL_JOB_SELECTOR,
            }

            likes_data = await this.page.evaluate((evaluate_data) => {
                let results = new Set()
                let items = document.querySelectorAll(evaluate_data.selector1)

                for (let item of items) {
                    // don't scribe 1st degree
                    if (item.querySelector(evaluate_data.selector4) != null ? !item.querySelector(evaluate_data.selector4).innerText.includes('1') : true) {
                        let result = {}
                        let full_name = item.querySelector(evaluate_data.selector2)
                        let full_job = item.querySelector(evaluate_data.selector5)
                        let linkedin = item.querySelector(evaluate_data.selector3)

                        if (full_name != null) {
                            full_name = full_name.innerText
                            if (full_name.includes(' ')) {
                                result.first_name = full_name.substr(0, full_name.indexOf(' '))
                                result.last_name = full_name.substr(full_name.indexOf(' ') + 1)
                            } else {
                                result.first_name = full_name
                            }
                        }

                        if (full_job != null) {
                            full_job = full_job.innerText // -> "Founder & CDO at StreetPod International & Director RUA Architects"
                            if (full_job.includes(' at ')) {
                                result.job_title = full_job.substr(0, full_job.indexOf(' at ')) // -> "Founder & CDO "
                                result.company_name = full_job.substr(full_job.indexOf(' at ') + 4) // -> "StreetPod International & Director RUA Architects"
                            } else {
                                result.job_title = full_job // ? or new param
                            }
                        }

                        if (linkedin != null) {
                            result.linkedin = linkedin.href

                            result.tags = ["like"]
                            result.post_action = "like"
                            result.post_url = evaluate_data.general_info.post_url
                            result.post_author_name = evaluate_data.general_info.post_author_name

                            results.add(result)
                        }
                    }
                }

                return [...results]
            }, evaluate_data)

            // close modal
            if (await this.page.$(selectors.POST_LIKERS_MODAL_CLOSE_SELECTOR) != null) {
                await this.page.click(selectors.POST_LIKERS_MODAL_CLOSE_SELECTOR)
                await this.page.waitFor(2000)
            } else {
                log.debug("Post_engagement_action: Can't close modal (close btn selector not found).")
                await super.gotoChecker(this.url)
                await utils.close_msg_box(this.page)
            }

        } else {
            // a little number of likes
            log.debug("Post_engagement_action: _get_likers started")

            let evaluate_data = {
                general_info: general_info,
                selector1: selectors.POST_LIKERS_ELEMENT_SELECTOR,
            }

            likes_data = await this.page.evaluate((evaluate_data) => {
                let results = new Set()
                let items = document.querySelectorAll(evaluate_data.selector1)

                if (items != null && items.length > 0) {
                    for (let item of items) {
                        let result = {}
                        let linkedin = item

                        result.tags = ["like"]
                        result.post_action = "like"
                        result.post_url = evaluate_data.general_info.post_url
                        result.post_author_name = evaluate_data.general_info.post_author_name

                        result.linkedin = linkedin.href

                        results.add(result)
                    }
                }

                return [...results]
            }, evaluate_data)
        }

        //log.debug("Post_engagement_action: likes_data: ", likes_data)
        log.debug("Post_engagement_action: likers scribed:", likes_data.length)
        return likes_data
    }


    async _get_commenters(general_info) {
        // load all comments
        let check_selector = ''
        while (check_selector != null) {
            await utils.autoScroll(this.page)
            check_selector = await this.page.$(selectors.POST_MORE_COMMENTERS_BTN_SELECTOR)
            if (check_selector != null) {
                await this.page.click(selectors.POST_MORE_COMMENTERS_BTN_SELECTOR)
            }
        }

        // load all sub-comments
        check_selector = ''
        while (check_selector != null) {
            check_selector = await this.page.$(selectors.POST_PREVIOUS_REPLIES_BTN_SELECTOR)
            if (check_selector != null) {
                await this.page.click(selectors.POST_PREVIOUS_REPLIES_BTN_SELECTOR)
            }
        }

        let evaluate_data = {
            general_info: general_info,
            selector1: selectors.POST_ELEMENT_SELECTOR,
            selector2: selectors.POST_COMMENTER_LINK_SELECTOR,
            selector3: selectors.POST_AUTHOR_TAG_SELECTOR,
            selector4: selectors.POST_COMMENTER_NAME_SELECTOR,
            selector5: selectors.POST_COMMENTER_JOB_SELECTOR,
            selector6: selectors.POST_COMMENTER_DEGREE_SELECTOR,
        }

        let comments_data = await this.page.evaluate((evaluate_data) => {
            let results = new Set()
            let items = document.querySelectorAll(evaluate_data.selector1)

            if (items != null && items.length > 0) {
                for (let item of items) {
                    // don't scribe author of post and 1st degree
                    if (item.querySelector(evaluate_data.selector6) == null ? true : !item.querySelector(evaluate_data.selector6).innerText.includes('1')) {
                        let result = {}
                        let full_name = item.querySelector(evaluate_data.selector4)
                        let linkedin = item.querySelector(evaluate_data.selector2)
                        let full_job = item.querySelector(evaluate_data.selector5)

                        if (full_name != null) {
                            full_name = full_name.innerText
                            if (full_name.includes(' ')) {
                                result.first_name = full_name.substr(0, full_name.indexOf(' '))
                                result.last_name = full_name.substr(full_name.indexOf(' ') + 1)
                            } else {
                                result.first_name = full_name
                            }
                        }

                        if (full_job != null) {
                            full_job = full_job.innerText // -> "Founder & CDO at StreetPod International & Director RUA Architects"
                            if (full_job.includes(' at ')) {
                                result.job_title = full_job.substr(0, full_job.indexOf(' at ')) // -> "Founder & CDO "
                                result.company_name = full_job.substr(full_job.indexOf(' at ') + 4) // -> "StreetPod International & Director RUA Architects"
                            } else {
                                result.job_title = full_job // ? or new param
                            }
                        }

                        if (linkedin != null) {
                            result.linkedin = linkedin.href

                            result.tags = ["comment"]
                            result.post_action = "comment"
                            result.post_url = evaluate_data.general_info.post_url
                            result.post_author_name = evaluate_data.general_info.post_author_name

                            results.add(result)
                        }
                    }
                }
            }

            return [...results]
        }, evaluate_data)

        //log.debug("Post_engagement_action: comments_data: ", comments_data)
        log.debug("Post_engagement_action: commenters scribed:", comments_data.length)
        return comments_data
    }
}

module.exports = {
    Post_engagement_action: Post_engagement_action
}
