// Импортировать модуль mongoose
var mongoose = require('mongoose');
// Установим подключение по умолчанию

var log = require('loglevel').getLogger("o24_logger");

var APP_ENV = process.env.APP_ENV;

var mongoDB = '';

if (APP_ENV == 'Production') {
    mongoDB = 'mongodb://127.0.0.1:27017/O24Mc-prod'; 
} else {
    mongoDB = 'mongodb://127.0.0.1:27017/O24Mc-test'; 
}

mongoose.connect(mongoDB, function (err) {
    if (err) throw err;
    log.debug('... Successfully connected to mongoDB: ...', mongoDB);
});

// Позволим Mongoose использовать глобальную библиотеку промисов
mongoose.Promise = global.Promise;
// Получение подключения по умолчанию
var db = mongoose.connection;

// Привязать подключение к событию ошибки (получать сообщения об ошибках подключения)
db.on('error', console.error.bind(console, '... MongoDB connection error: ...'));

module.exports = {
    mongoose: mongoose,
}
