var buildU = 'http://localhost/~user16/booker/server/api/'
var URL = 'http://192.168.0.15/~user16/booker/server/api/'
//var URL = '../../api/'



var axConf = {
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
    }
}

//Use instad off writting URL
function getUrl() {
    // return buildU
    return URL
   
}

//Time to create event
var timeStart = 8
var timeEnd = 20
var timeNoon = 12
var min00 = '00'
var min15 = '15'
var min30 = '30'
var min45 = '45'


//Get weeks day
function getWeekDays(str){
    if (str == 'sun')
    {
        return ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    }
    else if (str == 'mon')
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    }
    else if (str == 'ru')
    {
        return ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье']
    }
}

//Get month
function getNameMonth(str){
    if (str == 'en')
    {
        return ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
         'October', 'November', 'December']
    }
    else if (str == 'ru')
    {
        return ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь',
        'Октябрь', 'Ноябрь', 'Декабрь']
    }
}
