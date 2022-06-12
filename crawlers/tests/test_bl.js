
(async () => {
    var str = "1asdfsd 2asdfaew. 3rthbrnnntersws, 4ntbrrrnrerf\nwed, 5trvwe 6yhnbergw\n7reveqw 8nhbfgdf\r 9nf 10svcf 11gf\nbnfdgfdgd\rn 12gfnfgsrg\n 13imjtujymym \r hntrhbrt"

    str = str.replace(new RegExp('\rn', 'g'), ' ')
    str = str.replace(new RegExp('\r', 'g'), ' ')
    str = str.replace(new RegExp('\n', 'g'), ' ')
    str = str.replace(new RegExp(',', 'g'), ' ')

    var arr = str.split(" ")
        .filter(function (item) {
            if (item != '') {
                return item
            }
        })
    console.log(arr)

})();

