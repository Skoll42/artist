'use strict';

(function (window, $) {

    class ProfileSettings {
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
                    _this.saveProfileSettings(e)
                }
            );

            $('[data-toggle=offcanvas]').click(function() {
                $('.row-offcanvas').toggleClass('active');
            });
        }

        saveProfileSettings(e) {
            e.preventDefault();

            let _this = this;

            let validation = new FormValidation({
                form: $(_this.form)
            });

            if (!validation.isEmail($(_this.form).find('#artist-email').val())) {
                validation.processErrors('appbundle_user', {
                    'email': 'Email is not valid'
                });
                return false;
            }

            if (validation.requireFormValidation()) {
                _this.sendFormAjax($(_this.form));
            }
        }

        sendFormAjax(_form) {
            let _this = this;
            $('body').addClass('loader');

            $.ajax({
                type: "POST",
                url: _form.attr('action'),
                contentType: false,
                async:false,
                cache: false,
                processData: false,
                data: new FormData(_form[0]),
                success: function (data) {
                    if (data.success) {
                        $('body').removeClass('loader');
                        $('.profile-success').text(data.message);
                        setInterval(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        let validation = new FormValidation({
                            form: $(_this.form)
                        });
                        validation.processErrors('appbundle_user', data.errors);
                        window.scrollTo(0, 0);
                        $('body').removeClass('loader');
                    }
                }
            });
        }
    }
    window.ProfileSettings = ProfileSettings;
})(window, jQuery);


