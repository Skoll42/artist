require('select2');

'use strict';

(function (window, $) {

    class Customer {
        constructor(options) {
            this.opt = options;
            this.saveBtn = this.opt.saveBtn;
            this.form = this.opt.form;

            this.init();
        }

        init() {
            let _this = this;

            $(_this.saveBtn).on(
                'click', function (e) {
                    _this.saveProfile(e)
                }
            );

            $('.btn-confirmation-delete').on('click', function (e) {
                _this.deleteProfile(this)
            });

            $('[data-toggle=offcanvas]').click(function() {
                $('.row-offcanvas').toggleClass('active');
            });

            new UploadPhoto({});
        }

        saveProfile(e) {
            e.preventDefault();

            let _this = this;

            let validation = new FormValidation({
                form: $(_this.form)
            });

            if (validation.requireFormValidation()) {
                _this.sendFormAjax($(_this.form));
            }
        }

        sendFormAjax(_form) {
            let _this = this;
            $('body').addClass('loader');

            let data = new FormData(_form[0]);

            try {
                for (let pair of data.entries()) {
                    if (pair[1] instanceof File && pair[1].name == '' && pair[1].size == 0)
                        data.delete(pair[0]);
                }
            } catch(e) {}

            $.ajax({
                type: "POST",
                url: _form.attr('action'),
                data: data,
                contentType: false,
                processData: false,
                context : this,
                success: function (data) {
                    if (!data.success) {
                        let validation = new FormValidation({
                            form: $(_this.form)
                        });

                        validation.processErrors('appbundle_customer', data.errors);
                        $('body').removeClass('loader');
                        window.scrollTo(0, 0);
                    } else {
                        let timeout = setTimeout(function(){
                            clearTimeout(timeout);
                            $('body').removeClass('loader');
                        }, 2000)
                    }
                }
            });
        }

        deleteProfile(btn) {
            let userId = $(btn).attr('data-user-id');

            $.ajax({
                type: "POST",
                url: '/profile/customer/' + userId + '/delete',
                success: function (data) {
                    if (data.success) {
                        $('#confirmation-delete-modal').modal('hide');
                        let successModal = $("body").find('#user-delete');
                        if($(successModal).length > 0) {
                            $(successModal).remove();
                        }
                        $("body").append(data.html);
                        $("body").find('#user-delete').modal('show')
                    }

                    setTimeout(function () {
                        window.location = '/logout';
                    }, 2000)
                }
            });
        }
    }

    window.Customer = Customer;
})(window, jQuery);