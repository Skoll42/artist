module.exports = (function () {

    const config = require('./../config/main');

    return {
        /**
         * Find room by userID
         * @param db: Database
         * @param name: Room name
         * @param callback
         */
        findUserRoom: function(db, name, callback) {
            // Get the chat collection
            const collection = db.collection(config.collectionChat);

            // Find chat
            collection.find({'name': name}).toArray(function(err, result) {
                if(err) throw err;

                callback(result);
            });
        },

        /**
         * Create new room and insert it into MongoDB
         * @param db: Database
         * @param name: Room name
         * @param callback
         */
        createRoom: function(db, name, callback) {
            // Get the chat collection
            const collection = db.collection(config.collectionChat);

            // Insert new chat
            collection.insert({
                name: name,
            }, function (err, result) {
                if(err) throw err;

                callback(result);
            })
        },

        /**
         * Get Last message by room name
         * @param db: Database
         * @param room: Room name
         * @param callback
         */
        getLastMessage: function (db, room, callback) {
            // Get the documents collection
            const collection = db.collection(config.collectionMessages);

            // Emit all chat rooms
            collection.find({chatName: room}).sort({_id: -1}).limit(1).toArray(function (err, result) {
                if (err) throw err;

                callback(result);
            });
        },

        /**
         * Get Last message by room name
         * @param db: Database
         * @param room: Room name
         * @param callback
         */
        getMessagesHistory: function (db, room, callback) {
            // Get the documents collection
            const collection = db.collection(config.collectionMessages);

            // Emit all chat rooms
            collection.find({chatName: room}).sort({_id: 1}).toArray(function (err, result) {
                if (err) throw err;

                callback(result);
            });
        },

        /**
         * Get unread messages
         * @param db: Database
         * @param userId: User Id
         * @param callback
         */
        getUnreadMessage: function (db, userId, callback) {
            // Get the documents collection
            const collection = db.collection(config.collectionMessages);

            collection.distinct("chatName", {"targetId": userId, "status": "unread"}, function (err, result) {
                if (err) throw err;
                
                callback(result.length);
            });
        },

        /**
         * Insert new message into MongoDB
         * @param db: Database
         * @param data: Message data
         * @param callback
         */
        insertMessage: function(db, data, callback) {
            // Get the documents collection
            const collection = db.collection(config.collectionMessages);

            // Insert some message
            collection.insert({
                chatName: data.chatName,
                senderId: data.senderId,
                senderName: data.senderName,
                senderImage: data.senderImage,
                targetId: data.targetId,
                targetName: data.targetName,
                targetImage: data.targetImage,
                status: data.status,
                body: data.body,
                created_at: data.created_at,
                updated_at: data.updated_at,
            }, function (err, result) {
                if(err) throw err;

                // Emit latest message to all client
                callback(result.ops);
            });
        },

        readMessage: function (db, ObjectId, data, callback) {
            // Get the documents collection
            const collection = db.collection(config.collectionMessages);

            collection.updateOne(
                { _id : ObjectId(data.messageId) },
                {
                    $set: { status : 'read' }
                }, function(err, result) {
                    if(err) throw err;

                    callback(result);
                });
        },

        findMessage: function (db, ObjectId, data, callback) {
            // Get the documents collection
            const collection = db.collection(config.collectionMessages);

            collection.findOne({ _id : ObjectId(data.messageId) }, function(err, result) {
                if(err) throw err;

                callback(result);
            });
        }
    }
}());