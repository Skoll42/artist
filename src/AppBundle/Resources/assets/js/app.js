'use strict';

require('bootstrap-datepicker');
require('moment');
require('bootstrap-daterangepicker');
require('select2');
require('ekko-lightbox');

/**
 * clearing invalid form elements
 */
function clearingInvalidFormElements(form){
    var invalidClassName = 'is-invalid';
    var invalidFeedback = 'invalid-feedback';

    //clearing invalid date
    form.find('.'+invalidClassName).removeClass(invalidClassName);
    form.find('.'+invalidFeedback).remove();
}

/**
 * Handler for confirm popup windows
 *
 */
function confirmBoxHandler(confirmForm, mailBoxId, email) {
    confirmForm.find('#'+mailBoxId).html(email);
    confirmForm.modal('show');
}

window.CaptchaCallback = function() {
    $('.g-recaptcha').each(function(index, el) {
        grecaptcha.render(el, {
            'sitekey' : jQuery(el).attr('data-sitekey')
            ,'theme' : jQuery(el).attr('data-theme')
            ,'size' : jQuery(el).attr('data-size')
            ,'tabindex' : jQuery(el).attr('data-tabindex')
            ,'callback' : jQuery(el).attr('data-callback')
            ,'expired-callback' : jQuery(el).attr('data-expired-callback')
            ,'error-callback' : jQuery(el).attr('data-error-callback')
        });
    });
};

(function (window, $) {
    $(document).ready(function(){
        /**
         * registration
         */

        $('#registration').find('#btn-signup').on('click', function(e){
            e.preventDefault();
            let _form = $('#registration');

            var clientTime = new Date().getTime();
            var timeVal = Math.floor(clientTime/1000);

            $('[name="clientTime"]').val(timeVal);

            var password = _form.find('#password');
            var confirmPassword  = _form.find('#confirm-password');

            if(password.val() == confirmPassword.val()) {
                $('#show_hide_password_first').removeClass('is-invalid');
                $('#show_hide_password_first').find('.invalid-feedback').remove();
                $(document).trigger('sendRegistrationForm');
            }
            else {
                var msg = $( '<div class="invalid-feedback"></div>' );

                if($('#show_hide_password_first').hasClass('is-invalid')) {
                    $('#show_hide_password_first').removeClass('is-invalid');
                }

                $('#show_hide_password_first').find('.invalid-feedback').remove();
                $('#show_hide_password_first').addClass('is-invalid');

                msg.html('The entered passwords don\'t match.').insertAfter( password.next() );
            }
        });

        $(document).on('sendRegistrationForm', function (e) {
            var _form = $('#registration');

            function submitRegForm(form){
                var url = form.attr("action");
                var formData = $(form).serializeArray();

                    $('body').addClass('loader');
                    $.post(url, formData).done(function (data) {

                        $('body').removeClass('loader');

                        $('.is-invalid').each(function () {
                            $(this).removeClass("is-invalid");
                        });

                        $('.invalid-feedback').each(function () {
                            $(this).remove();
                        });

                        if ((typeof data.success !== "undefined") && data.success) {
                            let location = ((data.redirect !== undefined) && data.redirect) ? data.redirect : '/';

                            // success modal handler
                            confirmRegistrHandler(form);
                        }

                        if ((typeof data.need_captcha !== "undefined") && data.need_captcha) {
                            grecaptcha.reset();
                            $('.captcha-box').addClass('captcha-show');
                        }

                        if ((typeof data.errors !== "undefined") && data.errors) {

                            for (var key in data.errors) {
                                switch (key) {
                                    case 'email':

                                        errorHandler(key, data.errors[key]);
                                        break;
                                    case 'plainPassword':

                                        errorHandler('password', data.errors[key]['first']);
                                        break;

                                    case 'captcha':

                                        errorHandler('captcha', data.errors[key]);
                                        break;
                                }
                            }
                        }
                    });
            }

            // confirm registration handler
            function confirmRegistrHandler(form) {

                var email = form.find('#email').val();
                // load confirm forgot form
                if(! $('#confirm-signup').length) {

                    $.get( "/register/confirmrg", function( data ) {
                        $( "#registr-confirm-modal-box" ).html( data );
                        form.parents('#signup-form').modal('hide');

                        confirmBoxHandler($('#confirm-signup'), 'confirmed-mail', email);
                        $('#confirm-signup').on('hidden.bs.modal', function(e){
                            window.location.reload(true);
                        });
                    });

                } else {
                    form.parents('#signup-form').modal('hide');
                    confirmBoxHandler($('#confirm-signup'), 'confirmed-mail', email);
                    $('#confirm-signup').on('hidden.bs.modal', function(e){
                        window.location.reload(true);
                    });
                }
            }

            /**
             * registration error handler
             */
            function errorHandler(id, dataErrors) {

                if(id !== undefined){

                    var input = $('#'+id).addClass('is-invalid');

                    if(dataErrors[0] !== undefined){

                        var msg = $( '<div class="invalid-feedback"></div>' );

                        if(id === 'password') {
                            input.parents('.input-group').find('.show-pwd').addClass('is-invalid');
                            msg.html(dataErrors[0]).insertAfter( input.next() );
                        } else if(id === 'captcha'){
                            msg.html(dataErrors).insertAfter($('#artistcap'));
                        } else {
                            msg.html(dataErrors[0]).insertAfter( input );
                        }
                    }
                }
            }

            submitRegForm(_form);

            e.preventDefault();
        });

        /**
         * login
         */
        $( "#login" ).on('submit', function (e) {

            var _form = $('#login');
            function submitLogForm(form){
                var url = form.attr("action");
                var formData = $(form).serializeArray();

                $('body').addClass('loader');
                $.post(url, formData).done(function (data) {

                    $('body').removeClass('loader');

                    if ((typeof data.error !== "undefined")  && data.error) {

                        clearingInvalidFormElements(form);

                        var input = $('#username');
                        $(input).parents('.form-group').addClass('has-error');
                        $('#reg-password').parents('.form-group').addClass('has-error');
                        $( '<div class="invalid-feedback">' + data.error + '</div>' ).insertAfter( $(input).parents('.input-parent') ).show();

                    }else{
                        window.location.reload(false);
                    }
                });
            }

            submitLogForm(_form);

            e.preventDefault();
        });

        /**
         * become-customer
         */
        $('a[data-toggle="modal"]').on('click', function(e){

            let _this = $(this);

            let userTypeGroup = $('input[type="radio"][name="user_type"]');

            $(userTypeGroup).each(function () {
                $(this).removeAttr("checked");
            });

            if($(_this).hasClass('become-customer')) {
                $('#customer-inp').attr('checked', "checked");
            } else if($(_this).hasClass('become-artist')) {
                $('#artist-inp').attr('checked', "checked");
            }
        });

        /*
         * Remove scrollbar on modal
         */
        $('body').on('show.bs.modal', '.modal', function () {
            $('body').css('overflow','hidden');
            $('body').addClass('custom-modal-open');

            // clearing input data
            $('#login-form form')[0].reset();
            $('#signup-form form')[0].reset();

            //recaptcha reset
            grecaptcha.reset();

            // reset forgot password form
            var forgotForm = $('#forgot-form form')[0];

            if ( forgotForm !== undefined ){
                forgotForm.reset();
            }

            // clearing invalid messages
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();

            // clearing showing eye
            $(this).find('.show-pwd').removeClass('visible-pwd');

            // disabling resend link
            let resendLinks = $('#registr-resend-email, #resend-email');
            var backLink = $(this).find(resendLinks).addClass('disable');
            setTimeout(function (backLink) {
                backLink.removeClass('disable')
            }, 60000, backLink);

        }).on('hide.bs.modal', '.modal', function () {
            $('body').css('overflow','auto');
            $('body').removeClass('custom-modal-open');
        });

        /**
         * show password on mousedown
         */
        $( ".show-pwd" ).mouseup(function() {
            $( this ).parents('.form-group').find('input').attr('type','password');
        }).mousedown(function() {
            $( this ).parents('.form-group').find('input').attr('type','text');
        });

        //forgot password modal
        $('body').on('click', '#back-to-login', function(e) {
            $('#forgot-form').modal('hide');
            $('#login-form').modal('show');
        });

        /**
         * forgot password submit
         */
        $('body').on('submit', '#forgot-pass', function(el) {

            var form = $(this);
            var url = $(this).attr("action");
            var formData = $(this).serializeArray();
            var invalidClassName = 'is-invalid';
            var invalidFeedback = 'invalid-feedback';

            $('body').addClass('loader');
            $.post(url, formData).done(function (data) {

                $('body').removeClass('loader');

                if((typeof data.success !== "undefined")  && data.success) {

                    let email = form.find('#username').val();

                    confirmForgotPassHandler(form, email);
                }else {
                    clearingInvalidFormElements(form);

                    var input = form.find('#username').addClass(invalidClassName);
                    if ((typeof data.error !== "undefined")  && data.error) {
                        $( '<div class="'+invalidFeedback+'">' + data.error + '</div>' ).insertAfter( input );
                    }
                }
            });
            el.preventDefault();
        });

        // confirm forgot password handler
        function confirmForgotPassHandler(form, email) {

            // load confirm forgot form
            if(! $('#confirm-forgot-form').length) {

                $.get( "/resetting/confirmfp", function( data ) {
                    $( "#confirm-forgot-modal-box" ).html( data );
                    form.parents('.modal').modal('hide');

                    confirmBoxHandler($('#confirm-forgot-form'), 'confirmed-fog-mail', email);
                });

            } else {
                form.parents('.modal').modal('hide');

                confirmBoxHandler($('#confirm-forgot-form'), 'confirmed-fog-mail', email);
            }
        }

        //resend email for forgot password
        $('body').on('click', '#resend-email', function (el) {

            var disableClassName = 'disable';
            var delayTime = 60000; //timeout for disable button
            var resendLink = $(this);
            var userEmail = $('#confirmed-fog-mail').html();

            if(! resendLink.hasClass(disableClassName)){
                let forgotForm = $('body').find('#forgot-pass');
                forgotForm.find('#username').val(userEmail);
                forgotForm.submit();

                resendLink.addClass(disableClassName);
                setTimeout(function () {
                    resendLink.removeClass(disableClassName);
                }, delayTime);
            }

            el.preventDefault()
        });

        /*
         * resend verified email to user
         */
        function resendVerifiedEmail(email, disableClassName, resendLink, delayTime) {
            let emailData ={email: email};
            let data = JSON.stringify(emailData);

            $.post( "/register/resend", data, function( data ) {

                resendLink.addClass(disableClassName);
                setTimeout(function () {
                    resendLink.removeClass(disableClassName);
                }, delayTime);
            });
        }

        //resend email for success registration
        $('body').on('click', '#registr-resend-email', function (el) {

            var disableClassName = 'disable';
            var delayTime = 60000; //timeout for disable button
            var resendLink = $(this);
            var userEmail = $('#confirmed-mail').html();

            if(! resendLink.hasClass(disableClassName)){

                resendVerifiedEmail(userEmail, disableClassName, resendLink, delayTime);
            }

            el.preventDefault()
        });

        // reset password
        $('#form-reset-pass').on('submit', function(e) {
            var form = $(this);
            var url = $(this).attr("action");
            var formData = $(this).serializeArray();

            let pass = $(this).find('#password');
            let cPass = $(this).find('#confirm-password');

            if(pass.val() === cPass.val()) {
                $.post(url, formData).done(function (data) {
                    window.location.href = '/';
                });
            } else {
                clearingInvalidFormElements(form);

                pass.addClass('is-invalid');
                pass.parents('.input-group').find('.show-pwd').addClass('is-invalid');
                $( '<div class="invalid-feedback">passwords is not the same</div>' ).insertAfter( pass.next() );
            }

            e.preventDefault();
        });

        /**
         * show-hide forgot password modal
         */
        $('body').on('click', '#forgot-modal-lnk', function (e) {

            if(! $('#forgot-form').length) {
                $.get( "/resetting/request", function( data ) {
                    $( "#forgot-pass-modal-box" ).html( data );
                    $('#login-form').modal('hide');
                    $('#forgot-form').modal('show');
                });
            }else{
                $('#login-form').modal('hide');
                $('#forgot-form').modal('show');
            }

            e.preventDefault();
        });

        $('#login-form').on('hidden.bs.modal', function () {
            $(this).find('.has-error').removeClass('has-error');
        });

        /**
         * show eye when password input
         */
        $('[type="password"]').on('keyup', function (e) {

            var visibleClassName = 'visible-pwd';
            var showPwd = $(this).parent().find('.show-pwd');

            if($(this).val()) {
                showPwd.addClass(visibleClassName);
            } else {
                showPwd.removeClass(visibleClassName);
            }
        });

        /**
         * resend verification email on login form
         */
        $('body').on('click', '#resend-verification', function (e) {
            let email = $(this).attr("data-email");
            let disableClassName = 'disable';
            let delayTime = 60000; //timeout for disable button

            resendVerifiedEmail(email, disableClassName, $(this), delayTime);

            e.preventDefault();
        });

        $(document).on('click', '[data-toggle="lightbox"]:not([data-gallery="navigateTo"]):not([data-gallery="example-gallery-11"])', function(event) {
            event.preventDefault();
            return $(this).ekkoLightbox({
                onShown: function() {
                    if (window.console) {
                        return console.log('Checking our the events huh?');
                    }
                },
                onNavigate: function(direction, itemIndex) {
                    if (window.console) {
                        return console.log('Navigating '+direction+'. Current item: '+itemIndex);
                    }
                }
            });
        });

        $("a[href='#top']").click(function() {
            $("html, body").animate({ scrollTop: 0 }, "slow");
            return false;
        });
    });
})(window, jQuery);

let body = document.body;
$(".modal").on("shown.bs.modal", function () {
    body.setAttribute("data-reg-document-modal", "active");
    toggleViewportScrolling(true);
}).on("hidden.bs.modal", function () {
    body.removeAttribute("data-reg-document-modal");
    toggleViewportScrolling(false);
});

let freezeVp = function(e) {
    e.preventDefault();
};

function toggleViewportScrolling (bool) {
    if (bool === true) {
        body.addEventListener("touchmove", freezeVp, false);
    } else {
        body.removeEventListener("touchmove", freezeVp, false);
    }
}



// TODO: remove this modal after implements non-functional elements
// AR-512
/*
$('body').on('click',
    '#policy, #artist-list, #inst, #fb, #tw, #google, #search-date, #comments, #rating, .delete-profile, .icon-big-star',
    function (e) {
        e.preventDefault();
        $.post('/coming-soon')
            .done(function (data) {

                if($('body').find('#coming-soon').length > 0) {
                    $('#coming-soon').remove();
                }

                $('body').append(data.html);
                $('body').find('#coming-soon').modal('show');
            });
    });*/
