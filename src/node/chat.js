/**
 * Required Modules
 */
var fs         = require("fs");
var io         = require("socket.io");
var httpsync   = require("httpsync");
var path       = require('path');
var configDir  = '../../../../../app/config/packages/syntax/chat/';
// var log        = fs.createWriteStream('../storage/logs/node.txt', {'flags': 'a'});

/**
 * Configure the application
 */
try {
	// console.log(path.resolve(__dirname, configDir, 'chatConfig.json'));
	if (!fs.existsSync(path.resolve(__dirname, configDir, 'chatConfig.json'))) {
		throw "Configuration file does not exist.";
	}

	var config = JSON.parse( fs.readFileSync(path.resolve(__dirname, configDir, 'chatConfig.json')) );

	if (!('port' in config) || !('apiEndPoint' in config)) {
		throw "Your configuration must have atleast a port and apiEndPoint";
	}
} catch ( error ) {
	console.error("Configuration file error: " + error);
	process.exit(1);
}

/**
 * Main Application
 */
var chat = io.listen(config.port);

// log.write('['+ date +'] Chat server started.\n');

// chat.set('transports', ['xhr-polling', 'jsonp-polling']);

function removeItem(array, item) {
    for (var i in array) {
        if (array[i] == item) {
            array.splice(i,1);
            break;
        }
    }
}

var room = new Array();

chat.sockets.on('connection', function(client) {

	function sendMessage(type, message) {
		client.get('clientInfo', function (error, clientInformation) {

			if (type == 'connectionMessage' || type == 'message') {
				// Only log connection and chat messages.

				if (room[clientInformation.room]['messages'].length >= config.backLog) {
					room[clientInformation.room]['messages'].shift();
				}

				var logData = {
					"date": date,
					"roomId": clientInformation.room,
					"type": 'Connection Message',
					"message": message
				};

				// log.write(JSON.stringify(logData) +'\n');
				room[clientInformation.room]['messages'].push(message);
			}

			if (type == 'backFillChatLog') {
				// Use client emit to just send to the requester.
				client.emit(type, message);
			} else {
				// Use chat socket emit to send to all user in a room.
				chat.sockets.in(clientInformation.room).emit(type, message);
			}
		});
	}


	client.on('subscribe', function(clientInformation) {
		// If the room does not exist in memory fill create the data tables for it.
		if ( typeof chat.sockets.manager.rooms['/' + clientInformation.room] ==  'undefined') {
			room[clientInformation.room] = new Array();
			room[clientInformation.room]['userList'] = new Array();
			room[clientInformation.room]['awayList'] = new Array();

			// Backfill chat logs for site specified number of lines
			var req = httpsync.request({ url : config.apiEndPoint + "/" + clientInformation.room + "/" + config.backFill });
			var res = req.end();

			res = res.data.toString('utf-8');

			res = JSON.parse(res);

			room[clientInformation.room]['messages'] = new Array();

			for (i in res) {
				room[clientInformation.room]['messages'].push(res[i].text);
			}

		}

		// Join the chat room
		client.join(clientInformation.room);

		// Save client data
		client.set('clientInfo', clientInformation);

		// Add client to user list
		room[clientInformation.room]['userList'].push(clientInformation.username);

		// Broadcast new client list to room
		sendMessage('userListUpdate', room[clientInformation.room]['userList']);
		sendMessage('awayListUpdate', room[clientInformation.room]['awayList']);

		// Back fill the chat log for the newly connected user
		sendMessage('backFillChatLog', room[clientInformation.room]['messages']);

		if (config.connectionMessage) {
			sendMessage('connectionMessage', '<small class="muted">' + clientInformation.username + ' has joined the chatroom.</small> <br />');
		}

	});

	client.on('message', function (message) {

		if (room[message.room]['messages'].length >= config.backLog) {
			room[message.room]['messages'].shift();
		}

		var logData = {
			"date": date,
			"roomId": message.room,
			"type": 'Message',
			"message": message.text
		};

		// log.write(JSON.stringify(logData) +'\n');

		// Add the chat message to the in memory chat log
		room[message.room]['messages'].push(message.text);

		// Broadcast the message to the room
		chat.sockets.in(message.room).emit('message', message.text);
	});

	client.on('getUserCount', function (chatRoomId) {
		client.emit('userCount', room[chatRoomId]['userList'].length);
	});

	client.on('statusUpdate', function (status) {
		client.get('clientInfo', function (error, clientInformation) {

			if (status.status == 'Away') {
				var logData = {
					"date": date,
					"roomId": clientInformation.room,
					"type": 'Status Update',
					"message": clientInformation.username +' is away'
				};

				// log.write(JSON.stringify(logData) +'\n');
				removeItem(room[clientInformation.room]['userList'], clientInformation.username);
				room[clientInformation.room]['awayList'].push(clientInformation.username);
			} else {
				var logData = {
					"date": date,
					"roomId": clientInformation.room,
					"type": 'Status Update',
					"message": clientInformation.username +' is back'
				};

				// log.write(JSON.stringify(logData) +'\n');
				room[clientInformation.room]['userList'].push(clientInformation.username);
				removeItem(room[clientInformation.room]['awayList'], clientInformation.username);
			}

			sendMessage('userListUpdate', room[clientInformation.room]['userList']);
			sendMessage('awayListUpdate', room[clientInformation.room]['awayList']);
		});
	});
	

	client.on('disconnect', function() {
		client.get('clientInfo', function (error, clientInformation) {
			// Check for client information before showing disconnect messages
			if (clientInformation != null) {
				// Remove client from user list
				removeItem(room[clientInformation.room]['userList'], clientInformation.username);
				removeItem(room[clientInformation.room]['awayList'], clientInformation.username);

				// Broadcast new client list to room
				sendMessage('userListUpdate', room[clientInformation.room]['userList']);
				sendMessage('awayListUpdate', room[clientInformation.room]['awayList']);


				if (config.connectionMessage) {
					sendMessage('connectionMessage', '<small class="muted">' + clientInformation.username + ' has left the chatroom.</small> <br />');
				}

				if ( typeof chat.sockets.manager.rooms['/' + clientInformation.room] ==  'undefined') {
					// Clear room from memory when no one is left in it.
					room[clientInformation.room] = new Array();
				}
			}
		});
	});
});