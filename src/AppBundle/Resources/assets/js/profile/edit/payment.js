$('.payment-profile').on('click', function (e) {

    let _form = $(this).parents('form');

    let validation = new FormValidation({
        form: $(_form)
    });

    if (validation.requireFormValidation()) {
        $(_form)[0].submit();
    }
});