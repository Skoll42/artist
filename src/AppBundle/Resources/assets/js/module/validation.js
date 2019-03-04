'use strict';

(function (window, $) {

    class FormValidation {
        constructor(options) {
            this.opt = options;
            this.form = this.opt.form;
        }

        requireFormValidation() {
            let _this = this;
            let validationSettings;

            jQuery.validator.setDefaults({
                debug: true,
                success: "valid"
            });

            $.validator.addMethod( "alphanumeric_space", function( value, element ) {
                return this.optional( element ) || /^[\w\-\s]+$/i.test( value );
            }, "Letters, numbers, underscores, dashes, whitespaces is allowed" );

            $.validator.addMethod( "alphanumeric", function( value, element ) {
                return this.optional( element ) || /^\w+$/i.test( value );
                i.test( value );
            }, "Letters, numbers only please" );

            let validationSettingsArtistProfile = {
                rules: {
                    "appbundle_artist[name]": {
                        maxlength: 255
                    },
                    "appbundle_artist[description]": {
                        maxlength: 1000
                    },
                    "appbundle_artist[price]": {
                        number: true,
                        maxlength: 12
                    },
                    "appbundle_artist[first_name]": {
                        alphanumeric_space: true,
                        maxlength: 255,
                    },
                    "appbundle_artist[last_name]": {
                        alphanumeric_space: true,
                        maxlength: 255,
                    },
                    "appbundle_artist[age]": {
                        digits: true,
                        maxlength: 3
                    },
                    "appbundle_artist[phone]": {
                        digits: true,
                        maxlength: 10,
                        minlength: 4
                    },
                    "appbundle_artist[postal_code]": {
                        digits: true,
                        maxlength: 4 // maximum for Norway
                    },
                    "appbundle_artist[address]": {
                        maxlength: 255
                    },
                    "appbundle_artist[iban]": {
                        alphanumeric: true,
                        maxlength: 34
                    }
                },
                messages: {
                    "appbundle_artist[name]": {
                        maxlength: "Artist Name should be less than 256 characters"
                    },
                    "appbundle_artist[description]": {
                        maxlength: "Artist Description should be less than 1000 characters"
                    },
                    "appbundle_artist[price]": {
                        number: 'Only digits, ",", "." symbols are allowed',
                        maxlength: "Price should be less than billions"
                    },
                    "appbundle_artist[first_name]": {
                        alphanumeric: "Letters, numbers, and underscores only please",
                        maxlength: "First Name is Too Long",
                    },
                    "appbundle_artist[last_name]": {
                        alphanumeric: "Letters, numbers, and underscores only please",
                        maxlength: "Last Name is Too Long"
                    },
                    "appbundle_artist[age]": {
                        digits: "Only digits are allowed",
                        maxlength: "Age value is not valid"
                    },
                    "appbundle_artist[phone]": {
                        digits: "Only digits are allowed",
                        maxlength: "Please enter a valid phone number",
                        minlength: "Please enter a valid phone number"
                    },
                    "appbundle_artist[postal_code]": {
                        digits: "Postal Code should contain digits only",
                        maxlength: "Please enter a valid postal code"
                    },
                    "appbundle_artist[address]": {
                        maxlength: "Too long Address"
                    },
                    "appbundle_artist[iban]": {
                        alphanumeric: "IBAN should contain only letters and numbers",
                        maxlength: "IBAN should not be longer that 34 characters"
                    }
                }
            };

            let validationSettingsCustomerProfile = {
                rules: {
                },
                messages: {
                }
            };

            let validationSettingsContactUs = {
                rules: {
                    "appbundle_artist[name]": {
                        maxlength: 255,
                        required: true,
                    },
                    "appbundle_artist[description]": {
                        maxlength: 1000,
                        required: true,
                    },
                },
                messages: {
                    "appbundle_artist[name]": {
                        maxlength: "Name should be less than 256 characters"
                    },
                    "appbundle_artist[feedback]": {
                        maxlength: "Message should be less than 1000 characters"
                    },
                }
            };

            switch(_this.form[0].name) {
                case 'appbundle_artist' :
                    validationSettings = validationSettingsArtistProfile;
                    break;
                case 'appbundle_customer' :
                    validationSettings = validationSettingsCustomerProfile;
                    break;
                case 'contact_us' :
                    validationSettings = validationSettingsContactUs;
                    break;
            }

            _this.form.validate(validationSettings);
            return _this.form.valid();
        }

        processErrors(bundle, errors) {
            $('form').find('.has-error').removeClass('has-error');
            $('form').find('.is-invalid').remove();
            $.each(errors, function(index, value) {
                let input = $("[name='" + bundle + '[' + index + ']' + "']"),
                    formGroup = input.parents('.form-group');

                formGroup.addClass('has-error');
                formGroup.after('<div class="form-group row is-invalid">\n' +
                    '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 col-xl-2 profile-label"></div>\n' +
                    '<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 col-xl-10 profile-input">' + value + '</div>\n' +
                    '</div>');
            });
        }

        isEmail(email) {
            let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }
    }

    window.FormValidation = FormValidation;
})(window, jQuery);