'use strict';

(function (window, $) {

    class Room {
        constructor(app) {
            this.app = app;
            this.socket = this.app.socket;

            this.init();
        }

        init() {
            let _this = this;

            _this.socket.on('connect', function() {
                _this.join();
            });
        }

        name() {
            let _this = this;

            return $(Client.prototype.getNode('.chat')).attr('data-room-id') || null;
        }

        join() {
            let _this = this;

            if(_this.name()) {
                _this.socket.emit('room.join', _this.app.selfId, _this.socket.id, _this.name());
            }
        }
    }

    window.Room = Room;
})(window, jQuery);