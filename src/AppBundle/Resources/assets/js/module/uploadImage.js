'use strict';

(function (window, $) {

    class UploadPhoto {
        constructor(options) {
            this.opt = options;
            this.wrapper = '.photo-file-widget';
            this.imgPreviewIdName = "preview-img";
            this.preview = true;
            this.previewImgWidth = '100%';
            this.previewImgHeight = 'auto';

            this.init();
        }

        init() {
            let _this = this;
            return $(".uploadPhoto").each(function () {

                let $this = this;

                let previewMode = _this.optionCheck($($this), 'data-preview');

                if (previewMode) {
                    let imgCheck = _this._imgCheck($($this), 'data-img-url');
                    if (imgCheck) {
                        _this.addPreview();
                        $("#" + _this.imgPreviewIdName).attr("src", $($this).attr('data-img-url'));
                    }
                }

                $('input[type="file"]').change(function (e) {
                    let isValid = _this.validateImg($($this), this.files[0]);

                    if (previewMode && isValid) {
                        _this.addPreview();
                        _this.loadFile(this.files[0]);
                    }
                });
            });
        }

        optionCheck($this, attribute) {
            let _this = this;
            let previewMode;
            if (_this.preview || $this.attr(attribute)) {
                previewMode = true;
            } else {
                previewMode = false;
            }
            return previewMode;
        }

        _imgCheck($this, attribute) {
            if ($this.attr(attribute) != '' && $this.attr(attribute) != undefined) {
                return true;
            } else {
                return false;
            }
        }

        addPreview() {
            let _this = this;
            let html = _this.addPreviewImg();

            $('.uploadPhoto').html(html);
        }

        addPreviewImg() {
            let _this = this;
            return $('<img />', {
                'src': '',
                'width': _this.previewImgWidth,
                'height': _this.previewImgHeight,
                'id': _this.imgPreviewIdName,
                'class': 'img-rounded img-bordered img-bordered-primary'
            });
        }

        validateImg($this, file) {
            if (file.type != "image/jpeg" && file.type != "image/png") {
                $('.validate-message-format').css('display', 'block');
                return false;
            } else if (file.size > 10485760) {
                $('.validate-message-size').css('display', 'block');
                return false;
            }
            $('.validate-message').css('display', 'none');
            return true;
        }

        loadFile(file) {
            let _this = this;
            let reader = new FileReader();

            reader.onload = function (e) {
                $("#" + _this.imgPreviewIdName).attr("src", e.target.result);
            };

            reader.readAsDataURL(file);
        }

    }

    window.UploadPhoto = UploadPhoto;
})(window, jQuery);