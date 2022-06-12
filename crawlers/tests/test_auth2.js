const puppeteer = require("puppeteer");

(async () => {
    console.log("..... test_auth started: .....", __filename);

        var endpoint = 'ws://127.0.0.1:50871/devtools/browser/6b80ff89-74b6-4be6-be86-7fdc1ebb663d';
        var context_id = 'D787F6AA3D1F9077EE6811B84DAAEA1C';
            
        var browser = await puppeteer.connect({
            browserWSEndpoint: endpoint
        });

        let contexts = await browser.browserContexts();
        //console.log(contexts);

        let found_context = null;

        for (var cx in contexts){
            console.log(contexts[cx]._id);
            if (contexts[cx]._id == context_id){
                found_context = contexts[cx];
                break;
            }
        }

        //let c = contexts[1];
        
        let pages = await found_context.pages();
        let p = pages[0];
        
        let url = await p.url();
        console.log(url);
        //var page = await browser.newPage();
       // console.log( '..........here 1..........',  current_context)
       await p.goto('https://www.linkedin.com/uas/login');

       // let current_url = await page.url();
       await browser.close();

})();