'use strict';

(function (window, $) {

    class ProfileMedia {
        constructor(options) {
            this.opt = options;
            this.saveBtn = this.opt.saveBtn;
            this.form = this.opt.form;
            this.fileInput = this.opt.fileInput;
            this.deletePreviewBtn = this.opt.deletePreviewBtn;
            this.deleteImgBtn = this.opt.deleteImgBtn;

            this.init();
        }

        init() {
            let _this = this;

            $(_this.saveBtn).on(
                'click', function (e) {
                    _this.saveProfileMedia(e)
                }
            );

            _this.changeFormListen();

            $('body').on(
                'click',
                _this.deletePreviewBtn, function () {
                _this.deletePreview();

            }).on(
                'click',
                _this.deleteImgBtn, function (e) {
                _this.deleteImg(e, $(this))
            });

            $('[data-toggle=offcanvas]').click(function() {
                $('.row-offcanvas').toggleClass('active');
            });
        }

        saveProfileMedia(e) {
            e.preventDefault();

            let _this = this;

            _this.sendFormAjax($(_this.form));
        }

        sendFormAjax(_form) {
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
                        $('body').removeClass('loader');
                        window.scrollTo(0, 0);
                    } else {
                        let timeout = setTimeout(function(){
                            clearTimeout(timeout);
                            location.reload();
                        }, 2000)
                    }
                }
            });
        }

        changeFormListen() {
            let _this = this;

            $(_this.fileInput).on('change', function () {
                $('div[data-number]').remove();
                let imageCount = $('.gallery-image').length;

                $.each(this.files, function (k, file) {
                    if(_this.validateImg(file, imageCount)) {
                        _this.addPreview(imageCount, k, file);
                        imageCount = imageCount + 1;
                    }
                });
            });
        }

        validateImg(file, imageCount) {
            $('.validate-message').css('display', 'none');

            if (file.type != "image/jpeg" && file.type != "image/png") {
                $('.validate-message-ext').css('display', 'block');
                return false;
            } else if (file.size > 10485760) {
                $('.validate-message-size').css('display', 'block');
                return false;
            }
            if (imageCount > 7) {
                $('.validate-message-count').css('display', 'block');
                return false;
            }

            return true;
        }

        addPreview(imageCount, k, file) {
            let _this = this;
            let html = _this.addPreviewImg(imageCount, k);

            $('.text-lg-left').append(html);
            _this.loadFile(file, '.image-' +imageCount);
        }

        addPreviewImg(imageCount, k) {
            return '<div class="col-lg-3 col-md-4 col-xs-6 gallery-image" data-number="' + k + '">' +
                '<div class="d-block mb-4 position-relative">' +
                '<a href="" class="image-' + imageCount + '" data-toggle="lightbox" data-gallery="example-gallery"></a>' +
                '<div class="delete-preview"><i class="icon icon-delete"></i></div>' +
                '</div>' +
                '</div>';
        }

        loadFile(file, elem) {
            let reader = new FileReader();

            reader.onload = function (e) {
                $('body').find(elem).attr("href", e.target.result).css('background-image', 'url("' + e.target.result + '")');
            };

            reader.readAsDataURL(file);
        }

        deletePreview() {
            let _this = this;
            let elem = $(this).parents('.gallery-image');
            let number = $(elem).attr('data-number');
            let input = $(_this.fileInput)[0];
            let files = input.files;
            let newFileList = Array.from(files);

            const dt = new DataTransfer();

            for (let file of files) {
                if (file !== input.files[0])
                    dt.items.add(file);
            }

            newFileList.splice(number,1);
            input.onchange = null;
            $('div[data-number]').remove();
            input.files = dt.files;
        }

        deleteImg(e, $this) {
            e.preventDefault();
            let imageId = $this.attr('data-image-id');

            $.ajax({
                type: "POST",
                url: '/media/' + imageId + '/delete',
                contentType: false,
                async:false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data.success) {
                        $this.parents('.gallery-image').remove()
                    }
                }
            });
        }
    }
    window.ProfileMedia = ProfileMedia;
})(window, jQuery);