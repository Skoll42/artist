$('body').on('click', '.save-policy', function (e) {
    let val = $('body').find('input[name=cancellation-policy]:checked').val();
    let userId = $(this).attr('data-user-id');

    $.ajax({
        type: "POST",
        url: '/profile/artist/' + userId + '/edit/policy',
        data: {'policy': val},
        success: function (data) {
            if(data.success) {
                location.reload();
            }
        }
    });
});