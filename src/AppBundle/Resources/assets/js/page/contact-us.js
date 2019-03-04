'use strict';

(function (window, $) {
    class ContactUs {
        constructor(options) {
            this.opt = options;
            this.sendFeedbackBtn = this.opt.sendFeedbackBtn;
            this.form = this.opt.form;

            this.init();
        }

        init() {
            let _this = this;
            $(_this.sendFeedbackBtn).on(
                'click', function (e) {
                    _this.sendFeedbackValidation(e)
                }
            );
        }
        sendFeedbackValidation(e) {
            e.preventDefault();

            let _this = this;

            let validation = new FormValidation({
                form: $(_this.form)
            });

            if (grecaptcha && grecaptcha.getResponse() == "" && $('#contact-us-captcha-box').hasClass('contact-us-captcha-show')) {
                $('#contact-us-recaptcha-error').html('The reCAPTCHA wasn\'t entered correctly');
            }

            if ((validation.requireFormValidation() && grecaptcha && grecaptcha.getResponse().length > 0 && $('#contact-us-captcha-box').hasClass('contact-us-captcha-show'))
            || (validation.requireFormValidation() && $('#contact-us-captcha-box').hasClass('contact-us-captcha-show') == false)
            ) {
                $('#contact-us-recaptcha-error').html('');
                _this.sendFeedback($(_this.form));
            }
        }

        sendFeedback(_form) {
            let _this = this;
            $('body').addClass('loader');

            $.ajax({
                type: "POST",
                url: _form.attr('action'),
                contentType: false,
                async: false,
                cache: false,
                processData: false,
                data: new FormData(_form[0]),
                success: function (data) {
                    if (data[0].success) {
                        $('#contact-us-name').val('');
                        $('#contact-us-email').val('');
                        $('#contact-us-feedback').val('');
                        $('body').removeClass('loader');
                        $('body').append(data[0].modal);
                        $('body').find('#feedback-sent').modal('show');
                        if ((typeof data.contact_us_need_captcha !== "undefined") && data.contact_us_need_captcha) {
                            grecaptcha.reset();
                            $('.contact-us-captcha-box').addClass('contact-us-captcha-show');
                        }
                        $('#feedback-sent').on('hidden.bs.modal', function(e){
                            window.location.reload(true);
                        });
                    } else {
                        let validation = new FormValidation({
                            form: $(_this.form)
                        });
                        validation.processErrors('contact_us', data.errors);
                        window.scrollTo(0, 0);
                        $('body').removeClass('loader');
                    }
                }
            });
        }
    }

    window.ContactUs = ContactUs;
})(window, jQuery);