
(async () => {
/*
    var arr = [55, 44, 65, 55, 14, 11, 12, 44, 44, 59];
    var set = new Set(arr);
    console.log(set.size === arr.length);
    console.log(set);

    var myset = new Set(["https://www.linkedin.com/in/grigoriy-polyanitsin/", "https://www.linkedin.com/in/bersheva/"])

    myset.forEach( (link) => {
        if(link.includes('bersheva')) {
            console.log(link)
        }
    })
*/

    var arr = [4,2,3,4,5,6,4]
    for(let a of arr) {
        if(arr.indexOf(a) === -1) {
            arr.push(a);
        }
    }

    console.log(arr);

})();