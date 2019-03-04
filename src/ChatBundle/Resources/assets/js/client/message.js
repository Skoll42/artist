'use strict';

$.fn.isInViewport = function() {
    let elementTop = $(this).offset().top;
    let elementBottom = elementTop + $(this).outerHeight();

    let viewportTop = $(window).scrollTop();
    let viewportBottom = viewportTop + $(window).height();

    return elementBottom > viewportTop && elementTop < viewportBottom;
};

(function (window, $) {

    class Message {
        constructor(options) {
            this.opt = options;
        }

        new(data) {
            let _this = this;

            _this.opt.app.socket.emit('message.insert', data);
            _this.opt.app.socket.emit('message.last', data);
        }

        item(data, senderId) {
            let _this = this,
                time = _this.timeFormat(data.created_at),
                itemClass = senderId === data.senderId ? 'item-self' : 'item-from ' + data.status,
                messagesWrapper = this.opt.messagesWrapper;

            let message = '<div style="display: none;" class="item-message ' + itemClass + '" data-message-id="' + data._id + '" data-date="' + (new Date(data.created_at)).getDay() + '">'+
                '<a href="/public/profile/' + data.senderId + '" class="user-image" style="background-image: url(' + data.senderImage + ')"></a>' +
                '<div class="message-body">' +
                '<a href="/public/profile/' + data.senderId + '" class="user-name">' + data.senderName + '</a>' +
                '<div class="message-time">' + time + '</div>' +
                '<div class="clearfix"></div>' +
                '<div class="message-text">' + data.body + '</div>' +
                '</div>' +
                '<div class="clearfix"></div>' +
                '</div>';

            _this.separate(data.created_at);

            // Append
            $(messagesWrapper).append($(message));
            $(messagesWrapper).find('[data-message-id="' + data._id + '"]').fadeIn(function () {
                $(this).show();
            });
        }

        separate(curDate) {
            let _this = this,
                messagesWrapper = this.opt.messageWrapper,
                lastElem = $('.chat').find('.item-message:last');

            if($(lastElem).attr('data-date') != (new Date(curDate)).getDay()) {
                $(messagesWrapper).append($('<div class="date-separate"><span>' + (new Date(curDate).format('MMMM dd, yyyy')) + '</span></div>'));
            }
        }

        timeFormat(time) {
            return new Date(time).format('HH:mm')
        }

        output(data) {
            let _this = this;

            _this.item(data, _this.opt.app.senderId);
            _this.opt.app.socket.emit('message.unread.count', {'userId': _this.opt.app.targetId});
            let client = new Client({
                messagesWrapper: Client.prototype.getNode('.chat'),
                app: _this.opt.app
            });
            client.scrolling();
        }

        last(data) {
            let _this = this;
            let chatsArea = $('.chats .col-12');
            let chat = $("#main").find('a.item[data-chat-name="' + data.chatName + '"]');

            let message = data.senderId == _this.opt.app.selfId ? '<i>You: </i>' + data.body : data.body;
            let image = data.senderId == _this.opt.app.selfId ? data.targetImage : data.senderImage;
            let name = data.senderId == _this.opt.app.selfId ? data.targetName : data.senderName;

            if($(chat).length > 0) {
                $(chat).find('.user-image').css('background-image', 'url("' + image + '")');
                $(chat).find('.user-name').text(name);
                $(chat).find('.message').html(message);
                $(chat).find('.message-date').text(new Date(data.created_at).format('MMMM dd, yyyy'));

                if(data.senderId != _this.opt.app.selfId) {
                    $(chat).removeClass('unread').removeClass('read');
                    $(chat).addClass(data.status);
                }

                if($(chat).attr('data-position') != 1) {
                    let move = $(chat);
                    $(chat).remove();
                    $(".chats .col-12").find('a[data-position="1"]').before($(move));
                }
            } else {
                let status = data.senderId != _this.opt.app.selfId ? data.status : '';
                let chat = $('<a href="/communication/' + data.targetId + '/to/' + data.senderId + '" class="item ' + status + '" data-chat-name="' + data.chatName + '"></a>');
                $(chat).append($('<div class="user-image" style="background-image: url(' + image + ')"></div>'));
                $(chat).append($('<div class="user-name">' + name + '</div>'));
                $(chat).append($('<div class="message">' + message + '</div>'));
                $(chat).append($('<div class="message-date">' + new Date(data.created_at).format('MMMM dd, yyyy') + '</div>'));
                $(chat).append($('<div class="clearfix"></div>'));

                if($('.chats .col-12 a').length > 0) {
                    $(".chats .col-12").find('a[data-position="1"]').before($(chat));
                } else {
                    $(".chats .col-12").append($(chat));
                }

                let chats = $(chatsArea).find('.item ');
                let pagination = $('.pagination-box').find('.page-item');

                if($(chats).length > 2 && $(pagination).length > 1) {
                    $(".chats .col-12 a:last").remove();
                }
            }

            $(chatsArea).find('.item ').each(function (k, e) {
                $(e).attr('data-position', k+1);
            });
        }
    }

    window.Message = Message;
})(window, jQuery);