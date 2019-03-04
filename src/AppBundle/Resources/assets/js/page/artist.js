'use strict';

(function (window, $) {

    class Artist {
        constructor(options) {
            this.opt = options;
            this.bookBtn = this.opt.bookBtn;
            this.bookModalElem = this.opt.bookModalElem;
            this.loginModalElem = this.opt.loginModalElem;
            this.cardElem = this.opt.cardElem;
            this.logged = this.opt.logged;
            this.stripePublic = this.opt.stripePublic;

            this.init();
        }

        init() {
            let _this = this;
            $(_this.bookBtn).on(
                'click', function () {
                    _this.bookNow(this)
                }
            );

            $(document).on('chooseDate', function (e, dateFromCalendar) {
                _this.bookNow(this, dateFromCalendar);
            });

            if(artistLogged) {
                $('body').find('#communication').on('click', function (e) {
                    e.preventDefault();
                    _this.preregisterModal();
                });
            }

            $('body').find('#terms').on('change', function () {
                $(this).parent().find('label').addClass('error');
            });
        }

        bookNow($this, dateFromCalendar = null) {
            let _this = this;

            if(!_this.logged) {
                $('body').find(_this.loginModalElem).modal('show');
                return false;
            }

            if(artistLogged) {
                _this.preregisterModal();
                return false;
            }

            //remove condition after removing book now button
            if(dateFromCalendar != null) {
                let busyStatus = $(dateFromCalendar).attr("data-busy");
                let currentTimestamp = Math.floor((new Date().getTime())/1000);
                let selectedDate = (dateFromCalendar) ? $(dateFromCalendar).attr("data-date") : null;
                let convertedDate = selectedDate.split("-");
                let convertedDateString = convertedDate[1] + "/" + convertedDate[2] + "/" + convertedDate[0];
                let convertedDateToTimestamp = Math.floor((new Date(convertedDateString).getTime())/1000);
                let daysTillBookingfromNow = Math.floor((convertedDateToTimestamp - currentTimestamp)/(3600*24));

                //comparing with -1 day for booking on next day and current day depending on time
                if(!currentArtistLogged)
                {
                    if(daysTillBookingfromNow < 90 && daysTillBookingfromNow >= -1 && busyStatus == "false") {
                        _this.bookModal($this, dateFromCalendar);
                    }
                    else {
                        _this.cantBookModal();
                    }
                }
            }
            else {
                _this.bookModal($this);
            }
        }

        bookModal($this, dateFromCalendar = null) {
            let _this = this;
            let bookDate = (dateFromCalendar) ? $(dateFromCalendar).attr('data-date') : $($this).attr('data-date');
            let $body = $('body');
            $.post('/book/' + artistId + '/artist')
                .done(function (data) {
                    $(_this.bookModalElem).remove();
                    $body.append(data.html);
                    $body.find(_this.bookModalElem).modal('show');
                    $body.find(_this.bookModalElem).find('.artist-book-date').val(bookDate);
                    $body.find(_this.bookModalElem).find('#artistBookDate').keypress(function(e){
                        var keyCode = e.which;

                        if ((keyCode >= 33 && keyCode <= 253)
                            || (keyCode == 8)
                            || (keyCode == 32)) {
                            e.preventDefault();
                        }
                    });
                    _this.submitPayment();
                });
        }

        cantBookModal() {
            $.post('/cant-book/' + artistId + '/artist')
                .done(function (data) {
                    if($('body').find('#cant-book').length > 0) {
                        $('#cant-book').remove();
                    }
                    $('body').append(data.html);
                    $('body').find('#cant-book').modal('show');
                });
        }

        preregisterModal() {
            $.post('/preregister/' + artistId + '/artist')
                .done(function (data) {
                    if($('body').find('#preregister').length > 0) {
                        $('#preregister').remove();
                    }
                    $('body').append(data.html);
                    let preregisterModal = $('body').find('#preregister');
                    preregisterModal.modal('show');
                    preregisterModal.on('shown.bs.modal', function(){
                        $('#preregister-link').on('click', function(e) {
                            e.preventDefault();
                            preregisterModal.modal('hide');
                            $('body').find('#signup-form').modal('show');
                        });
                    });
                    $('body').find('#signup-form').on('shown.bs.modal', function(){
                        $('body').find('#signup-form').find('input#customer-inp').prop("checked", true);
                    });
                });
        }

        submitPayment() {
            let _this = this;
            let stripe = Stripe(_this.opt.stripePublic);
            let elements = stripe.elements();
            let card = elements.create('card', {hidePostalCode: true});

            card.mount(_this.cardElem);
            card.addEventListener('change', function(event) {
                let displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            let form = document.getElementById('book-form');
            $('body').on('click', '.book-artist', function () {

                let $this = $(this);
                $.validator.setDefaults({
                    debug: true,
                    success: "valid"
                });

                $(form).validate();

                if (!$(form).valid()) {
                    $($this).removeAttr('disabled');
                    $('body').removeClass('loader');
                    return false;
                } else {
                    $('body').addClass('loader');
                }

                if(!$("#terms").is(':checked')) {
                    $("#terms").parent().find('label').addClass('error');
                    $('body').removeClass('loader');
                    $('.book-artist').removeAttr('disabled');
                    return false;
                } else {
                    $("#terms").parent().find('label').removeClass('error');
                }

                stripe.createToken(card).then(function(result) {
                    let timeValidation = _this.cardValidation(new Date(result.token.card.exp_year + "-" + result.token.card.exp_month));

                    if(!timeValidation.success) {
                        result.error = timeValidation.error;
                    }

                    if (result.error) {
                        $($this).removeAttr('disabled');
                        $('body').removeClass('loader');
                        let errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                    } else {
                        let hiddenInput = document.createElement('input');
                        hiddenInput.setAttribute('type', 'hidden');
                        hiddenInput.setAttribute('name', 'stripeToken');
                        hiddenInput.setAttribute('value', result.token.id);
                        form.appendChild(hiddenInput);

                        _this.sendPaymentAjax($(form))
                    }
                });
            });
        }

        cardValidation(cardDate) {
            let eventDate = new Date($("body").find('#artistBookDate').val());
            let days = (cardDate - eventDate) / (30 * 1000 * 60 * 60 * 24);

            if((Math.round(days)) < 0) {
                return {
                    success: false,
                    error: {
                        type: "card_error",
                            code: "invalid_expiry_year",
                            message: "Your card's expiration date will be invalid on event date.",
                            param: "exp_year"
                    }
                }
            }

            return true;
        }

        sendPaymentAjax($form) {
            let _this = this;

            $('body').addClass('loader');

            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method'),
                contentType: false,
                async:false,
                cache: false,
                processData: false,
                data: new FormData($form[0]),
                success: function (data) {
                    $('body').removeClass('loader');
                    if(!data.success) {
                        $('body').find('#card-errors').text(data.message);
                        $('body').find('.errors .message').css("display", 'block').text(data.message);
                        $('body').find('.errors').css("display", 'block');
                        $('body').find('.book-artist').removeAttr('disabled');
                    }

                    if(data.hidden) {
                        $('body').find('.success-message').css("display", 'block');
                        $('body').find(_this.bookModalElem).modal('hide');
                    }
                }
            });
        }

        removeModal() {
            let _this = this;
            $("body").find(_this.bookModalElem).remove();
            $('[name="__privateStripeController0"]').remove();
            $('[name="__privateStripeController1"]').remove();
        }
    }

    window.Artist = Artist;
})(window, jQuery);