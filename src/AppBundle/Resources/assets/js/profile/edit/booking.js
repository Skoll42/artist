$(".nav-tabs a").click(function(){
    $(this).tab('show');
});

$('.booking-controls').on('click', 'button.accept', function (e) {
    let id = $(this).attr('data-booking-id');
    showBookingModal('#confirmation-booking', '/profile/artist/' + id + '/accept/booking/modal');

}).on('click', 'button.reject', function (e) {
    let id = $(this).attr('data-booking-id');
    showBookingModal('#reject-booking', '/profile/artist/' + id + '/reject/booking/modal');

}).on('click', 'button.canceled', function (e) {
    let id = $(this).attr('data-booking-id');
    showBookingModal('#cancel-booking', '/profile/artist/' + id + '/cancel/booking/modal');

}).on('click', 'button.comment', function (e) {
    let id = $(this).attr('data-booking-id');
    showBookingModal('#comment-booking', '/profile/artist/' + id + '/comment/booking/modal');
}).on('click', 'button.location', function (e) {
    let id = $(this).attr('data-booking-id');
    showBookingModal('#location-booking', '/profile/artist/' + id + '/location/booking/modal');
}).on('click', 'button.customer-canceled', function (e) {
    let id = $(this).attr('data-booking-id');
    showBookingModal('#cancel-booking', '/profile/customer/' + id + '/cancel/booking/modal');
});

function showBookingModal(modalId, url) {
    $.post(url)
        .done(function (data) {

            if($('body').find(modalId).length > 0) {
                $(modalId).remove();
            }

            $('body').append(data.html);
            $('body').find(modalId).modal('show');
        });
}

$('body').on('click','.confirmation-booking', function (e) {
    let id = $(this).attr('data-booking-id');
    confirm('/profile/artist/' + id + '/accept/booking');

}).on('click','.rejecting-booking', function (e) {
    let id = $(this).attr('data-booking-id');
    confirm('/profile/artist/' + id + '/reject/booking');

}).on('click','.cancelling-booking', function (e) {
    let id = $(this).attr('data-booking-id');
    confirm('/profile/artist/' + id + '/cancel/booking');
}).on('click','.customer-cancelling-booking', function (e) {
    let id = $(this).attr('data-booking-id');
    confirm('/profile/customer/' + id + '/cancel/booking');
});

function confirm(url) {
    $.post(url)
        .done(function (data) {
            if(data.success) {
                window.location.reload();
            }
        });
}