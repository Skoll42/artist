module.exports = (function() {

    let connections = {};
    return {
        /**
         * Register user in connections. This method must be executed as first in whole registration process.
         * @param userId id of user.
         * @param connectionId id of connection.
         */
        registerUser: function(userId, connectionId) {
            if (connections[userId] === undefined) {
                connections[userId] = {};
            }

            connections[userId][connectionId] = null;
        },
        /**
         * Register socket to communication. Must be executed after registerUser.
         * Modify socket object and set field userId and connectionId.
         * @param userId id of user.
         * @param connectionId id of connection.
         * @param socket socket.
         * @returns {boolean} if socket was registered or not, if false then you have to do everything again.
         */
        registerSocket: function(userId, connectionId, socket) {
            if (connections[userId] != null && connections[userId][connectionId] == null) {
                socket.userId = userId;
                socket.connectionId = connectionId;
                connections[userId][connectionId] = socket;
                return true;
            } else {
                return false;
            }
        },

        registerRoom: function(userId, connectionId, room) {
            if (connections[userId] != null && connections[userId][connectionId] != null) {
                let socket = connections[userId][connectionId];
                socket.room = room;
                connections[userId][connectionId] = socket;
                return true;
            } else {
                return false;
            }
        },

        /**
         * Remove connection.
         * @param socket socket to remove.
         */
        removeConnection: function(socket) {
            let userId = socket.userId;
            let connectionId = socket.connectionId;
            if (userId && connectionId && connections[userId] && connections[userId][connectionId]) {
                delete connections[socket.connectionId];
            }
        },

        /**
         * User messages history.
         * @param userId id of user.
         * @param messages history messages for user.
         * @param room room.
         */
        messagesHistory: function(userId, room, messages) {
            let userConnections = connections[userId];
            if (userConnections) {
                for (let connectionId in userConnections) {
                    if (userConnections.hasOwnProperty(connectionId)) {
                        let socket = userConnections[connectionId];
                        if (socket != null && socket['room'] === room) {
                            if(messages.length > 0) {
                                socket.emit('message.history', messages);
                            }
                        }
                    }
                }
            }
        },

        /**
         * Socket emit typing status
         * @param userId
         * @param userName
         * @param room
         * @param status
         */
        userTyping: function (userId, userName, status, room) {
            let userConnections = connections[userId];
            if (userConnections) {
                for (let connectionId in  userConnections) {
                    if (userConnections.hasOwnProperty(connectionId)) {
                        let socket = userConnections[connectionId];
                        if (socket != null) {
                            socket.emit('chat.typing.' + status, room);
                        }
                        if (socket != null && socket['room'] === room) {
                            socket.emit('message.typing.' + status, userName);
                        }
                    }
                }
            }
        },

        /**
         * Send message to user.
         * @param userId id of user.
         * @param message message.
         * @param room room.
         */
        pushMessage: function(userId, message, room) {
            let userConnections = connections[userId];
            if (userConnections) {
                for (let connectionId in userConnections) {
                    if (userConnections.hasOwnProperty(connectionId)) {
                        let socket = userConnections[connectionId];
                        if (socket != null && socket['room'] === room) {
                            socket.emit('message.output', message);
                        }
                    }
                }
            }
        },

        /**
         * Send notification to user.
         * @param userId id of user.
         * @param result unreaded message count.
         */
        unreadMessage: function(userId, result) {
            let userConnections = connections[userId];
            if (userConnections) {
                for (let connectionId in userConnections) {
                    if (userConnections.hasOwnProperty(connectionId)) {
                        let socket = userConnections[connectionId];
                        if (socket != null) {
                            socket.emit('message.unread.count', result);
                        }
                    }
                }
            }
        },

        /**
         * Send message status
         * @param userId id of user.
         * @param status
         * @param room
         * @param message message.
         */
        sendStatus: function (userId, status, room, message = '') {
            let userConnections = connections[userId];
            if (userConnections) {
                for (let connectionId in  userConnections) {
                    if (userConnections.hasOwnProperty(connectionId)) {
                        let socket = userConnections[connectionId];
                        if (socket != null && socket['room'] === room) {
                            socket.emit('status.' + status, true, message);
                        }
                    }
                }
            }
        },

        /**
         * @param userId
         * @param room
         * @param message
         */
        readedMessage: function (userId, room, message) {
            let userConnections = connections[userId];
            if (userConnections) {
                for (let connectionId in  userConnections) {
                    if (userConnections.hasOwnProperty(connectionId)) {
                        let socket = userConnections[connectionId];
                        if (socket != null) {
                            socket.emit('message.readed', room, message);
                        }
                    }
                }
            }
        },

        /**
         * Get Last message
         * @param userId
         * @param room
         */
        getLastMessage: function(userId, room) {
            let userConnections = connections[userId];
            if (userConnections) {
                for (let connectionId in userConnections) {
                    if (userConnections.hasOwnProperty(connectionId)) {
                        let socket = userConnections[connectionId];
                        if (socket != null) {
                            socket.emit('message.last', room);
                        }
                    }
                }
            }
        },
    }
}());