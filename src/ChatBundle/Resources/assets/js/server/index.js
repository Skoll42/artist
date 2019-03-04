#!/usr/bin/env nodejs
/**
 * Importing Node modules
 */
const mongo = require('mongodb'),
    MongoClient = mongo.MongoClient,
    http = require('http'),
    socketEvents = require('./io/events'),
    config = require('./config/main'),
    ObjectId = mongo.ObjectId;


/**
 * Use connect method to connect to the server
 */
MongoClient.connect(config.dbUrl, function(err, client) {
    if (err) throw err;

    /**
     * Select database
     */
    const db = client.db(config.database);

    /**
     * Start the server
     */
    const server = http.createServer().listen(config.port);

    /**
     * Socket IO initialize
     */
    const io = require('socket.io').listen(server);

    socketEvents(io, db, ObjectId);
});
