var log = require('loglevel').getLogger("o24_logger");

var arr = [
    {
        linkedin: '//http:linkedin.com/olol',
        name: 'Lex',
        tags: ['like']
    },
    {
        linkedin: '//http:linkedin.com/olol',
        name: 'Lex',
        tags: ['comment']
    },
    {
        linkedin: '//http:linkedin.com/kek',
        name: 'Kyk',
        tags: ['like']
    },
    {
        linkedin: '//http:linkedin.com/pots-l',
        name: 'Pots',
        tags: ['comment']
    },
];

(async () => {
    try {
        log.setLevel("DEBUG");
        log.debug("..... test uniq started .....")

        var unique_linkedin = {}
        var not_unique_linkedin = {}
        
        arr = arr.filter((item) => {
            var linkedin = item.linkedin
            var res = unique_linkedin.hasOwnProperty(linkedin) ? false : true
            if(res) {
                unique_linkedin[linkedin] = true
                return true
            } else {
                not_unique_linkedin[linkedin] = true
            }
        }).map((item) => {
            if(not_unique_linkedin.hasOwnProperty(item.linkedin)) {
                if(item.tags[0] == 'like') {
                    item.tags.push('comment')
                } else {
                    item.tags.push('like')
                }
            }
            return item
        })

        log.debug("..... arr: .....", arr)
        log.debug("..... unique_linkedin: .....", unique_linkedin)
        log.debug("..... not_unique_linkedin: .....", not_unique_linkedin)

    } catch (err) {
        log.error("..... error: .....", err)
    }

})();
