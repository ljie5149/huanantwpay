function checkDateValidate7(input)
{
	const selectedValue = input;
	let last_day  = jsgetDateByNdays(selectedValue, 0, 'YYYY-MM-DD', true);
	const offsetDays = -7;
	let limit_day  = jsgetDateByNdays(last_day, offsetDays, 'YYYY-MM-DD');
	console.log('limit_day :', limit_day);
	const input_day = new Date(selectedValue + ' 23:59:59');
	const limit = new Date(limit_day + ' 00:00:00');
	if (input_day > limit) {
		console.log('input_day > limit');
		return false;
	} else if (limit > input_day) {
		console.log('limit > input_day');
	} else {
		console.log('兩個時間相同');
	}
	return true;
}
function jsgetDateByNdays(inputDate, val = 0, formatStr = 'YYYY-MM-DD', lastflag = false) {
    let date = new Date(inputDate);
    date.setDate(date.getDate() + val);
    if (lastflag) {
        const year = date.getFullYear();
        const month = date.getMonth();
        date = new Date(year, month + 1, 0); // 設定為該月最後一天
    }

    console.log("date :", date);
	if (formatStr == 'YYYY-MM-DD')
    	return jsformatDate(date, formatStr);
	
    return jsformatDateTime(date, formatStr);
}
function jsgetDateByNmonth(inputDate, val = 0, formatStr = 'YYYY-MM-DD') {
    const date = new Date(inputDate);
    date.setMonth(date.getMonth() + val);

    return jsformatDate(date, formatStr);
}
function jsgetDateByNyears(inputDate, val = 0, formatStr = 'YYYY-MM-DD') {
    const date = new Date(inputDate);
    date.setFullYear(date.getFullYear() + val);

    return jsformatDate(date, formatStr);
}

function jsformatDate(date, formatStr) {
    const year 	= date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const day 	= String(date.getDate()).padStart(2, '0');

    return formatStr.replace('YYYY', year).replace('MM', month).replace('DD', day);
}
function jsformatDateTime(date, formatStr) {
    const year 		= date.getFullYear();
    const month 	= String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const day 		= String(date.getDate()).padStart(2, '0');
    const hours 	= String(date.getHours()).padStart(2, '0');
    const minutes 	= String(date.getMinutes()).padStart(2, '0');
    const seconds 	= String(date.getSeconds()).padStart(2, '0');

    return formatStr.replace('YYYY', year).replace('MM', month).replace('DD', day).replace('HH', hours).replace('mm', minutes).replace('ss', seconds);
}
function jsformatTime(date, formatStr) {
    const hours 	= String(date.getHours()).padStart(2, '0');
    const minutes 	= String(date.getMinutes()).padStart(2, '0');
    const seconds 	= String(date.getSeconds()).padStart(2, '0');

    return formatStr.replace('HH', hours).replace('mm', minutes).replace('ss', seconds);;
}
function getLastDayOfMonth(year, month) {
    // month 從 0 開始（0 表示 1 月，11 表示 12 月）
    return new Date(year, month + 1, 0).getDate();
}