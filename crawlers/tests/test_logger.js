var log = require('loglevel');

// test running

(async () => {
    try {
        log.setLevel(0)
        
        log.trace('trace')
        log.debug('debug')
        log.info('info')
        log.warn('warn', 'kjhgfbdcxz')
        log.error('error')

        console.log(log.getLevel())
        console.log(log.levels)
        console.log(w)

    } catch(err) {
        console.log('------------------------------------')
        log.trace('trace', err)
        log.debug('debug')
        log.info('info', err.stack)
        log.warn('warn')
        log.error('error')
    }

  })();