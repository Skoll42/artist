exports = module.exports = function (io, db, ObjectId) {

    const pushService = require('./../service/push');
    const mongoService = require('./../service/mongo');

    /**
     * Handle connection to socket.io.
     */
    io.on('connection', (socket) => {

        /**
         * On registered socket from client.
         */
        socket.on('register', (userId, connectionId) => {
            pushService.registerUser(userId, connectionId);
            pushService.registerSocket(userId, connectionId, socket);
            mongoService.getUnreadMessage(db, userId, function (result) {
                pushService.unreadMessage(userId, result);
            });
        });

        /**
         * On join to room check if exists or create new
         */
        socket.on('room.join', (userId, connectionId, room) => {

            // Get all user room
            mongoService.findUserRoom(db, room, function(result) {
                if(result.length === 0) {
                    // Create new room
                    mongoService.createRoom(db, room, function() {});
                }
            });

            mongoService.getMessagesHistory(db, room, function (result) {
                pushService.messagesHistory(userId, room, result);
            });

            pushService.registerRoom(userId, connectionId, room);
        });

        /**
         * On typing start.
         */
        socket.on('message.typing.start', (data) => {
            pushService.userTyping(data.userId, data.userName, 'start', data.room);
        });

        /**
         * On typing stop.
         */
        socket.on('message.typing.stop', (data) => {
            pushService.userTyping(data.userId, data.userName, 'stop', data.room);
        });

        /**
         * Clear status after send
         */
        socket.on('status.clear', (data) => {
            pushService.sendStatus(data.userId, 'clear', data.room, data.message)
        });

        /**
         * Insert News message into database
         */
        socket.on('message.insert', (data) => {
            // Insert message into MongoDB
            mongoService.insertMessage(db, data, function(result) {
                if(result) {
                    // Push message to client
                    pushService.pushMessage(data.targetId, result, data.chatName);
                    // Push message self
                    pushService.pushMessage(data.senderId, result, data.chatName);
                }
            });
        });

        /**
         * Last message
         */
        socket.on('message.last', (data) => {
            mongoService.getLastMessage(db, data.chatName, function (result) {
                if(result.length > 0) {
                    pushService.getLastMessage(data.targetId, result[0], data.chatName);
                    pushService.getLastMessage(data.senderId, result[0], data.chatName);
                }
            });
        });

        /**
         * Unread messages count
         */
        socket.on('message.unread.count', (data) => {
            mongoService.getUnreadMessage(db, data.userId, function (result) {
                pushService.unreadMessage(data.userId, result);
            });
        });

        /**
         * Mark message as read
         */
        socket.on('message.read', function(data) {
            mongoService.readMessage(db, ObjectId, data, function (result) {
                socket.emit('message.read', data);
            });

            mongoService.findMessage(db, ObjectId, data, function (result) {
                if(result.status === 'read') {
                    pushService.readedMessage(result.senderId, result.chatName, result.id);
                }
            });
        });

        /**
         * On disconnected socket.
         */
        socket.on('disconnect', function() {
            pushService.removeConnection(socket);
        });
    });
};