const puppeteer = require("puppeteer");
const selectors = require("../linkedin/selectors");
const MyExceptions = require('../exceptions/exceptions.js');

// test auth puppeteer

(async () => {
    console.log("..... test_auth started: .....", __filename);
    try{
        var credentials = {
            username: 'grinnbob@rambler.ru',
            password: 'linked123',
        };
        
        var browser = await puppeteer.launch({ 
            headless: false, 
         });
        var context = await browser.createIncognitoBrowserContext();
        var page = await context.newPage();
        //console.log( '..........browserContext..........',  context)
        

        await page.goto('https://www.linkedin.com/uas/login');
        
        await page.click(selectors.USERNAME_SELECTOR);
        await page.keyboard.type(credentials.username);
        await page.click(selectors.PASSWORD_SELECTOR);
        await page.keyboard.type(credentials.password);
        await page.click(selectors.CTA_SELECTOR);


        await page.waitFor(5000);
        let screenshot_str = await page.screenshot();
        let current_url = page.url();

        let context_obj = {
            endpoint: browser.wsEndpoint(),
            context_id: context._id,
            url: current_url,
            //screenshot: screenshot_str,
          }
        

        //browser.disconnect();
        console.log( '..........context_obj..........',  context_obj)

        if (current_url === 'https://www.linkedin.com/feed/') { // add domain here
            console.log( '..........success..........',  )
        } else {
            console.log( '..........unsuccess.............',  )
        }

  } catch(err) {
      console.log( '..........err.............', err.stack )
  }

})();