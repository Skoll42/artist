'use strict';
const message = require('./message');

(function (window, $) {

    class Client {
        constructor(options) {
            this.opt = options;
            this.messagesWrapper = this.getNode('.chat');
            this.textArea = this.getNode('#message');
            this.typingWrapper = this.getNode('.typing-wrapper');
            this.chatStatus = this.getNode('.chat-status');
            this.sendBtn = this.getNode('.send-message');
            this.chatStatusDefault = $(this.chatStatus).length > 0 ? this.chatStatus.textContent : '';
        }

        init() {
            let _this = this;

            if(_this.textArea) {
                _this.textArea.addEventListener('keyup', function(event){
                    _this.keyUpListener(event);
                });
            }

            $(_this.sendBtn).on('click', function () {
                _this.messageNew()
            });
        }

        getNode(selector) {
            return document.querySelector(selector) || null;
        }

        unreadMessageCount(data) {
            $('a.communication span i').text('(' + parseInt(data) + ')');
        }

        history(data) {
            let _this = this,
                message = new Message({
                    messagesWrapper: this.messagesWrapper
                });

            if(data.length) {
                for(let x = 0; x < data.length; x = x + 1) {
                    message.item(data[x], _this.opt.app.senderId);
                }
            }

            _this.scrolling();
        }

        readMessage(userId) {
            let _this = this;
            setTimeout(function(){
                $(_this.messagesWrapper).find('.unread').each(function() {
                    // if ($(this).isInViewport() && $(this).hasClass('item-from')) {
                        let messageId = $(this).attr('data-message-id');
                        let room = $(this).parents('.chat').attr('data-room-id');
                        _this.opt.app.socket.emit('message.read', {
                            'userId': userId,
                            'messageId': messageId,
                            'room' : room
                        });
                    // }
                });
            }, 2000);
        }

        scrolling() {
            let _this = this;

            if($(_this.messagesWrapper).length > 0) {
                if($(_this.messagesWrapper).attr('data-scrolled') === 'true') {
                    $(_this.messagesWrapper).scrollTop($(_this.messagesWrapper)[0].scrollHeight);
                    _this.readMessage(_this.opt.app.selfId);
                }

                _this.messagesWrapper.addEventListener('scroll', function(event) {
                    _this.scrollListener(event);
                });
            }
        }

        scrollListener(event) {
            let _this = this,
                element = event.target;

            if(_this.roundUsing(Math.ceil, (element.scrollHeight - element.scrollTop), 0) == element.clientHeight) {
                $(_this.messagesWrapper).attr('data-scrolled', "true");
            } else {
                $(_this.messagesWrapper).attr('data-scrolled', 'false');
            }

            _this.readMessage(_this.opt.app.selfId);
        }

        keyUpListener(event) {
            let _this = this;

            _this.opt.app.socket.emit('message.typing.start', {
                'userId': _this.opt.app.targetId,
                'userName': _this.opt.app.senderName,
                'room': _this.opt.app.room.name()
            });

            if($(event.target).val() === '') {
                _this.opt.app.socket.emit('message.typing.stop', {
                    'userId': _this.opt.app.targetId,
                    'userName': _this.opt.app.senderName,
                    'room': _this.opt.app.room.name()
                });
            }

            // When the client hits ENTER on their keyboard
            if (event.which === 13 && event.shiftKey === false) {
                _this.messageNew();
            }
        }

        typingStart(userName) {
            let _this = this;

            if($(_this.typingWrapper).find('.typing').length > 0) {
                return;
            }

            let message = $("<div class='message typing' />").append($('<em />')).text(userName + ' is typing message ...');
            $(_this.typingWrapper).append($(message));
            $(_this.typingWrapper).find('.typing').fadeIn(function () {
                $(this).show();
            });
        }

        typingStop(userName) {
            let _this = this;

            if(!$(_this.typingWrapper).find('.typing').length > 0) {
                return;
            }

            $(_this.typingWrapper).find('.typing').fadeOut(function () {
                $(this).remove();
            });
        }

        chatTypingStart(room) {
            let message = $("<div class='typing' />").append($('<em />')).text(' is typing message ...');
            let chat = $('#main').find('[data-chat-name="' + room + '"]');

            if($(chat).length > 0 && $(chat).find('.typing').length === 0) {
                $(chat).find('.message').append($(message));
                $(chat).find('.typing').fadeIn(function () {
                    $(this).show();
                });
            }
        }

        chatTypingStop(room) {
            let chat = $('#main').find('[data-chat-name="' + room + '"]');

            if(!$(chat).length > 0) {
                return
            }

            $(chat).find('.typing').fadeOut(function () {
                $(this).remove();
            });
        }

        messageNew() {
            let _this = this;

            let whitespacePattern = /^\s*$/;

            if (whitespacePattern.test(_this.textArea.value)) {
                _this.setStatus('Message text is required.');
                return false;
            }

            let message = new Message({
                messagesWrapper: this.messagesWrapper,
                app: _this.opt.app,
            });
            message.new({
                chatName: _this.opt.app.room.name(),
                senderId: _this.opt.app.senderId,
                senderName: _this.opt.app.senderName,
                senderImage: _this.opt.app.senderImage,
                targetId: _this.opt.app.targetId,
                targetName: _this.opt.app.targetName,
                targetImage: _this.opt.app.targetImage,
                body: _this.textArea.value,
                created_at: new Date(),
                updated_at: new Date(),
                status: 'unread'
            });

            _this.opt.app.socket.emit('message.typing.stop', {
                'userId': _this.opt.app.targetId,
                'userName': _this.opt.app.senderName,
                'room': _this.opt.app.room.name()
            });

            _this.statusClear();
        }

        setStatus(s, clear = true) {
            let _this = this;

            _this.chatStatus.textContent = s;

            if (s !== _this.chatStatusDefault && clear) {
                let delay = setTimeout(function () {
                    _this.setStatus(_this.chatStatusDefault);
                    clearInterval(delay);
                }, 3000);
            }
        }

        statusClear() {
            let _this = this;
            _this.opt.app.socket.emit('status.clear', {
                'userId': _this.opt.app.selfId,
                'message': '',
                'room': _this.opt.app.room.name()
            });
        }

        clear(clear, message) {
            let _this = this;
            if(clear === true && $(_this.textArea).length > 0) {
                (_this.textArea).value = '';
            }
        }

        read(data) {
            let _this = this;

            let item = $(_this.messagesWrapper).find('[data-message-id="' + data.messageId + '"]');

            if(item.length > 0) {
                $(item).removeClass('unread').addClass('read');
            }

            _this.opt.app.socket.emit('message.unread.count', {'userId': _this.opt.app.selfId});
        }

        readed(room, message) {
            let _this = this;

            let item = $('.chats').find('[data-chat-name="' + room + '"]');

            if(item.length > 0) {
                $(item).removeClass('unread').addClass('read');
            }
        }

        roundUsing(func, number, prec) {
            var tempnumber = number * Math.pow(10, prec);
            tempnumber = func(tempnumber);
            return tempnumber / Math.pow(10, prec);
        }

    }

    window.Client = Client;
})(window, jQuery);