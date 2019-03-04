$(document).ready(function() {
    let calendarWrapper = $(document).find('#artist-calendar-wrapper');

    let busyDatesArr = [];

    for (var i in busy_dates) {
        busyDatesArr.push(busy_dates[i]);
    }

    $(document).on('viewRender', function () {
        busyDatesArr.forEach(function (element) {
            $(calendarWrapper).find('[data-date="' + element +'"]').attr("data-busy", "true");
        });
    });


    $(document).on('editCalendar', function(e, dateFromCalendar){
        let dateBusyEl = $(dateFromCalendar);
        let dateBusy = dateBusyEl.attr("data-date");
        let dateBusyStatus = dateBusyEl.attr("data-busy");

        if(dateBusyStatus == "true") {
            $(dateBusyEl).attr('data-busy', 'false');
            var index = busyDatesArr.indexOf(dateBusy);
            if (index > -1) {
                busyDatesArr.splice(index, 1);
            }
        }
        else {
            $(dateBusyEl).attr('data-busy', 'true');
            dateBusyStatus = 'true';
            if(!busyDatesArr.includes(dateBusy)) {
                busyDatesArr.push(dateBusy);
            }
        }

    });

    $(document).find('#calendar-save-busy-dates-artist').on('click', function(e){
        e.preventDefault();

        $('body').addClass('loader');

        let form = $(document).find('calendar-form');
        let busyDates = JSON.stringify(busyDatesArr);

        $.ajax({
            type: "POST",
            url: form.attr('action'),
            data: {'busydates': busyDates},
            success: function (data) {
                window.scrollTo(0, 0);
                $('body').removeClass('loader');

                if (!data.success) {
                    alert('Error');
                }
            }
        });
    });



    var calendar =  $('#artist-calendar-wrapper').fullCalendar({
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

    $(calendarWrapper).find('.fc-header-left').html('Mark your busy days by click');

    $(calendarWrapper).find('.fc-button-today').html('Go to today');
});