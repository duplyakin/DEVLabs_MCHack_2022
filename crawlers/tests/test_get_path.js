var log = require('loglevel').getLogger("o24_logger");


(async () => {
    try {
        // info: https://learn.javascript.ru/url
        var str = ' https://www.linkedin.com/search/results/all/?keywords=acronis&origin=GLOBAL_SEARCH_HEADER&page=97 '
        const SEARCH_URL_2 = "https://www.linkedin.com/sales/search/people?doFetchHeroCard=false&geoIncluded=103644278&industryIncluded=80&logHistory=false&page=98&preserveScrollPosition=false&rsLogId=343536385&searchSessionId=DY4JJTjhRH6qJ2ZZIKMtXw%3D%3D";

        var res = new URL(str).search

        //str = "Текущая должность: Product Marketing Manager – Morningstar"
        //res = "Architectural Technologist at Reddy Architecture + Urbanism"

        let res1 = res.substr(0, res.indexOf(' at '))
        let res2 = res.substr(res.indexOf(' at ') + 4)

        str = str.split(res)[0]

        log.error("..... res: .....", str)
    } catch (err) {
        log.error("..... error: .....", err)
    } finally {
        log.error("..... finally: .....")
    }

})();