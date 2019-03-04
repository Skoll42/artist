'use strict';

(function (window, $) {

    class App {
        constructor(options) {
            this.opt = options;
            this.io = require('socket.io-client');
            this.socket = {};
            this.room = {};
            this.client = {};

            this.selfId = this.opt.selfId;
            this.senderId = this.opt.senderId;
            this.senderName = this.opt.senderName;
            this.senderImage = this.opt.senderImage;
            this.targetId = this.opt.targetId;
            this.targetName = this.opt.targetName;
            this.targetImage = this.opt.targetImage;

            this.connection();
        }

        connection() {
            let _this = this;

            _this.socket = _this.io.connect('/', {path: '/server1/'});

            _this.init();

            this.room = new Room(_this);
            this.client = new Client({
                app: _this
            });
            this.client.init();

        }

        init() {
            let _this = this;

            _this.socket.on('connect', function() {
                // register user in socket and get unread message count
                _this.socket.emit('register', _this.selfId, _this.socket.id);
            });

            _this.socket.on('message.unread.count', function (data) {
                let client = new Client({});
                client.unreadMessageCount(data);
            });

            _this.socket.on('message.history', function (data) {
                let client = new Client({
                    app: _this
                });
                client.history(data);
            });

            _this.socket.on('message.typing.start', function(userName){
                let client = new Client({
                    app: _this
                });
                client.typingStart(userName);
            });

            _this.socket.on('message.typing.stop', function(userName){
                let client = new Client({
                    app: _this
                });
                client.typingStop(userName);
            });

            _this.socket.on('chat.typing.start', function(room){
                let client = new Client({
                    app: _this
                });
                client.chatTypingStart(room);
            });

            _this.socket.on('chat.typing.stop', function(room){
                let client = new Client({
                    app: _this
                });
                client.chatTypingStop(room);
            });

            _this.socket.on('message.output', function(data) {
                let message = new Message({
                    messagesWrapper: Client.prototype.getNode('.chat'),
                    app: _this
                });
                message.output(data[0]);
            });

            _this.socket.on('status.clear', function (clear, message) {
                let client = new Client({
                    app: _this
                });
                client.clear(clear, message);
            });

            _this.socket.on('message.read', function (data) {
                let client = new Client({
                    app: _this
                });
                client.read(data);
            });

            _this.socket.on('message.readed', function (room, message) {
                let client = new Client({
                    app: _this
                });
                client.readed(room, message);
            });

            _this.socket.on('message.last', function(data) {
                let message = new Message({
                    messagesWrapper: Client.prototype.getNode('.chat'),
                    app: _this
                });
                message.last(data);
            });
        }
    }

    window.App = App;
})(window, jQuery);