require('select2');

'use strict';

(function (window, $) {

    class Profile {
        constructor(options) {
            this.opt = options;
            this.saveBtn = this.opt.saveBtn;
            this.form = this.opt.form;
            this.otherReqElem = this.opt.otherReqElem;
            this.reqElem = this.opt.reqElem;
            this.themesElem = this.opt.themesElem;
            this.tagsElem = this.opt.tagsElem;
            this.locationElem = this.opt.locationElem;
            this.timeVal = this.opt.timeVal;
            this.platformFee = this.opt.platformFee;
            this.priceElem = this.opt.priceElem;
            window.redirectURL = this.opt.redirectURL;

            this.init();
        }

        init() {
            let _this = this;

            $(_this.saveBtn).on(
                'click', function (e) {
                    _this.saveProfile(e)
                }
            );

            if($(_this.otherReqElem).val() == '') {
                $(_this.otherReqElem).attr('disabled', 'disabled')
            }

            $('.btn-confirmation-delete').on('click', function (e) {
                _this.deleteProfile(this)
            });

            _this.changeReq();
            _this.themesInit();
            _this.tagsInit();
            _this.countryCodeInit();
            _this.locationInit();
            _this.timeInit();
            _this.platformFeeInit();

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
            $('body').addClass('loader');
            let _this = this;

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

                        validation.processErrors('appbundle_artist', data.errors);
                        $('body').removeClass('loader');
                        window.scrollTo(0, 0);
                    } else {
                        let timeout = setTimeout(function(){
                            clearTimeout(timeout);
                            window.location.href = window.redirectURL;
                        }, 2000)
                    }
                }
            });
        }

        deleteProfile(btn) {
            let userId = $(btn).attr('data-user-id');

            $.ajax({
                type: "POST",
                url: '/profile/artist/' + userId + '/delete',
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

        changeReq() {
            let _this = this;
            $('#appbundle_artist_requirements_7').on('change', function () {
                if($(this).is(':checked')) {
                    $(_this.reqElem).removeAttr('disabled');
                } else {
                    $(_this.reqElem).attr('disabled', 'disabled').val('');
                }
            });
        }

        themesInit() {
            let _this = this;
            $(_this.themesElem).select2({
                theme: "bootstrap",
                placeholder: 'Events …',
            }).on("select2:select", function (e) {
                $('body').find('.select2-search__field').val('')
            });
        }

        tagsInit() {
            let _this = this;
            let select2Message = {
                inputTooShort: function(args) {
                    return "Tag name is too short";
                },
                inputTooLong: function(args) {
                    return "Tag name is too long";
                },
                errorLoading: function() {
                    return "Error loading results";
                },
                loadingMore: function() {
                    return "Loading more results";
                },
                noResults: function() {
                    return "No results found";
                },
                searching: function() {
                    return "Searching...";
                },
                maximumSelected: function(args) {
                    return "Too many selected items";
                }
            };

            $(_this.tagsElem).select2({
                theme: "bootstrap",
                placeholder: 'Tags …',
                tags: true,
                maximumSelectionLength: 10,
                maximumInputLength: 35,
                language: select2Message,
                insertTag: function(data, tag){
                    tag.text = "Create Tag";
                    data.push(tag);
                },
                createTag: function (tag) {
                    return {
                        id: tag.term,
                        text: tag.term,
                        isNew : true
                    };
                }
            }).on("select2:select", function (e) {
                if(e.params.data.isNew){
                    _this.addNewTag(e.params.data.id)
                }
            }).on('select2:unselecting', function() {
                $(this).data('unselecting', true);
            }).on('select2:opening', function(e) {
                if ($(this).data('unselecting')) {
                    $(this).removeData('unselecting');
                    e.preventDefault();
                }
            });
        }

        addNewTag(tagName) {
            let _this = this;

            $.ajax({
                type: "POST",
                url: '/tag/new/' + tagName,
                success: function (data) {
                    if (data.success) {
                        $(_this.tagsElem).find('[value="'+tagName+'"]').replaceWith('<option selected value="'+data.tag.id+'">'+data.tag.name+'</option>');
                    }
                }
            });
        }

        countryCodeInit() {
            //set default NO country code if value is empty
            if($('#artist-phone-code').val() == 0) {
                $('#artist-phone-code').val('+47');
            }
            $('#country-codes li').on('click', function() {
                $('#country-codes button span.value').text($(this).attr('value'));
                if($('#artist-phone-code').length != 0) {
                    $('#artist-phone-code').val($(this).attr('value')).change();
                    return;
                }

                $('#customer-phone-code').val($(this).attr('value')).change();
            });
        }

        locationInit() {
            let _this = this;
            let options = {
                language: 'en-GB',
                types: ['(cities)'],
                componentRestrictions: {country: "no"}
            };
            let autocomplete = new google.maps.places.Autocomplete($("#artist-location-input")[0], options);

            window.locationItem = null;

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                if(typeof autocomplete.getPlace().address_components == 'undefined') {
                    let elem = $('body').find('.pac-container').find('.pac-matched:contains("' + _this.ucwords(autocomplete.getPlace().name) + '")');
                    let text = _this.ucwords($(elem).parents('.pac-item-query').text());
                    let val = _this.ucwords(autocomplete.getPlace().name);
                    if($(elem).length > 0 && text === val) {
                        $('#artist-location-input').val(val);
                        window.locationItem = {
                            'long_name': val,
                            'short_name': val
                        }
                    } else {
                        window.locationItem = null;
                        _this.showError();
                        return false;
                    }
                } else {
                    window.locationItem = autocomplete.getPlace().address_components[0];
                    _this.hideError();
                }
            });

            $('#artist-location-input').change(function() {
                // we set a timeout to prevent conflicts between blur and place_changed events
                let timeout = setTimeout(function() {
                    let address = _this.ucwords($('#artist-location-input').val());
                    clearTimeout(timeout);
                    if (window.locationItem !== null) {
                        if (address !== window.locationItem.long_name) {
                            _this.showError()
                        } else {
                            _this.hideError();
                            $.post('/location/new', {
                                'longName': window.locationItem.long_name,
                                'shortName': window.locationItem.short_name
                            }).done(function (data) {
                                $(_this.locationElem)
                                    .append('<option value="' + data.location.id + '">' + data.location.name + '</option>');

                                $(_this.locationElem).val(data.location.id).change();
                            });
                        }
                    } else {
                        $('#artist-location-input').val('');
                        _this.showError();
                    }
                }, 500);
            });
        }

        showError() {
            if($('#artist-location-error').length == 0) {
                $('#artist-location-input').parent().append("<label id=\"artist-location-error\" class=\"error\" for=\"artist-location\">Please select location from a dropdown.</label>");
                $('#artist-location-input').addClass('error');
            }
        }

        hideError() {
            if($('#artist-location-error').length > 0) {
                $('#artist-location-error').remove();
                $('#artist-location-input').removeClass('error');
            }
        }

        ucwords(str) {
            return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
                return $1.toUpperCase();
            });
        }

        timeInit() {
            let _this = this;
            let timeArr = [1, 0];
            if(_this.timeVal !== '') {
                $('#artist-time').val(_this.timeVal);
                timeArr = _this.timeVal.split(':');
            }

            $('#artist-time').timepicki({
                show_meridian: false,
                step_size_hours: 1,
                step_size_minutes: 5,
                start_time: [timeArr[0], timeArr[1]],
                min_hour_value: 0,
                max_hour_value: 23,
                disable_keyboard_mobile: true,
            });
        }

        platformFeeInit() {
            let _this = this;

            $(_this.priceElem).on('keyup', function () {
                let val = $(this).val();
                let fee = val * parseFloat(_this.platformFee);
                let total = val - fee;

                $('.platform-fee').html('Fee: <span>' + fee.toFixed(2) + '</span> kr');
                $('.artist-get').html('You get: <span>' + total.toFixed(2) + '</span> kr');
            });
        }
    }

    window.Profile = Profile;
})(window, jQuery);