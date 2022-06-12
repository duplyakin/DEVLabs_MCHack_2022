const puppeteer = require("puppeteer");

(async () => {
    console.log("..... test_auth started: .....", __filename);


        var endpoint = 'ws://127.0.0.1:64596/devtools/browser/076b71d9-2a4e-4abe-9fd9-12140797d66b';
        
        
        var browser = await puppeteer.connect({
            browserWSEndpoint: endpoint
        });

        let contexts = await browser.browserContexts();
        console.log(contexts);
        let c = contexts[1];
        console.log(Object.keys(c));

        let pages = await c.pages();
        console.log(pages);
        
        let p = pages[0];
        
        let url = await p.url();
        console.log(url);
        //var page = await browser.newPage();
       // console.log( '..........here 1..........',  current_context)
        

       // let current_url = await page.url();

})();