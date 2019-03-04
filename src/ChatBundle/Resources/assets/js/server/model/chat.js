const mongo = require('mongo'),
    mongoClient = mongo.MongoClient,
    Schema = mongoClient.Schema;

// Schema defines how chat messages will be stored in MongoDB
const ChatSchema = new Schema({
    _id: Schema.Types.ObjectId,
    name: String,
});

module.exports = mongoClient.model('Chat', ChatSchema);