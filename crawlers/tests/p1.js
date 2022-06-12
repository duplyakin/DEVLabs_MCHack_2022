const puppeteer = require("puppeteer");
// test auth puppeteer

(async () => {
    console.log("..... test_auth started: .....", __filename);
    try{
        
        var browser = await puppeteer.launch({
            headless: false,  // test mode
            //userDataDir: './user_data',
         });
        
        var context = await browser.createIncognitoBrowserContext();
        var page = await context.newPage();
        await page.goto('https://www.ya.ru/');

        
        let current_url = await page.url();

        let current_context = await page.browserContext();
        console.log( '..........browserContext..........',  current_context)

        console.log( '..........wsEndpoint..........',  browser.wsEndpoint())
        

        var page1 = await context.newPage();
        await page1.goto('https://google.com');
        //await browser.close();
       // await browser.disconnect(); 

       //browser.disconnect();
        /*
        var browser1 = await puppeteer.launch({ 
            headless: false,  // test mode
            //userDataDir: './user_data',
         });
         var page1 = await current_context.newPage();
         await page1.goto('https://www.linkedin.com/feed/');
         await page1.waitFor(5000);
         */



  } catch(err) {
      console.log( '..........err.............', err.stack )
  }

})();