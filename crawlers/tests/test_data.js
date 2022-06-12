

(async () => {
    console.log("..... task test started: .....", __filename);
    let task = 0;
    try {
        console.log("..... try: .....");
        let current_url = 'https://www.linkedin.com/in/grigoriy-polyanitsin/';
        
         console.log("..... try: .....", '.'+(new URL(current_url)).hostname);
         return;
    } catch (err) {
        console.log( err.stack )
    } finally {
        task;
        console.log("..... finally: .....", current_url);
    }


})();
