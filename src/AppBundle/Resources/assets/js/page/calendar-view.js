$(document).ready(function() {
    let calendarWrapper = $(document).find('#artist-calendar-wrapper');

    window.busyDatesArr = [];

    for (var i in busy_dates) {
        busyDatesArr.push(busy_dates[i]);
    }

    //generate array of date range from today to + 90 days to get dates and add them to calendar

    Date.prototype.addDays = function(days) {
        let dat = new Date(this.valueOf())
        dat.setDate(dat.getDate() + days);
        return dat;
    }

    function getDates(startDate, stopDate) {
        let dateArray = new Array();
        let currentDate = startDate;
        while (currentDate <= stopDate) {
            let dayIndex = currentDate.getDate();
            dayIndex = (`0${dayIndex}`).slice(-2);
            let monthIndex = currentDate.getMonth() + 1;
            monthIndex = (`0${monthIndex}`).slice(-2);
            let year = currentDate.getFullYear();
            dateArray.push(year + "-" + monthIndex + "-" + dayIndex);
            currentDate = currentDate.addDays(1);
        }
        return dateArray;
    }

    let datesRangeArr = getDates(new Date(), (new Date()).addDays(90));

    $(document).on('viewRender', function () {
        datesRangeArr.forEach(function (element) {
            let cell = $(calendarWrapper).find('[data-date="' + element +'"]');
            $(cell).find('.fc-day-number').attr("data-outofrange", "false");
        });

        busyDatesArr.forEach(function (element) {
            $(calendarWrapper).find('[data-date="' + element +'"]').attr("data-busy", "true");
        });

    });

    let calendar =  $('#artist-calendar-wrapper').fullCalendar({
        header: {
            left: 'title',
            center: '',
            right: 'today prev,next'
        },
        editable: false,
        firstDay: 1, //  1(Monday) this can be changed to 0(Sunday) for the USA system
        selectable: true,
        defaultView: 'month',

        axisFormat: 'h:mm',
        columnFormat: {
            month: 'ddd',    // Mon
            week: 'ddd d', // Mon 7
            day: 'dddd M/d',  // Monday 9/7
            agendaDay: 'dddd d'
        },
        titleFormat: {
            month: 'MMMM yyyy', // September 2009
            week: "MMMM yyyy", // September 2009
            day: 'MMMM yyyy'                  // Tuesday, Sep 8, 2009
        },
        timeFormat: {
            '': 'H:mm{-H:mm}'
        },
        allDaySlot: true,
        selectHelper: true,
        events: events
    });

    //ugly fixes to calendar markup

    let month = $(calendarWrapper).find('.fc-header-left > span');

    let prevSpan = $(calendarWrapper).find('.fc-header-right > span:nth-child(3)');

    month.insertAfter(prevSpan);

    $(calendarWrapper).find('.fc-header-left').addClass('hide-on-mobile');

    $(calendarWrapper).find('.fc-header-space').addClass('hide-on-mobile');

    $(calendarWrapper).find('.fc-header-left').html(calendarHeaderText);

    $(calendarWrapper).find('.fc-button-today').html('Go to today');
});