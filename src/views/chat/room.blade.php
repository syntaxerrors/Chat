<div class="row">
	<div class="col-md-8">
		<div class="panel panel-default" style="height: 400px;">
			<div class="panel-heading">
				<strong class="text-info">Chat Room:</strong>&nbsp;<strong class="text-error">{{ $chatRoom->name }}</strong>
				<div class="panel-btn">
					{{ HTML::link('chat/fullChat/'. $chatRoom->uniqueId, 'Full Transcript', array('target' => '_blank')) }}
				</div>
			</div>
			<div id="chatBox" style="padding: 5px 0px 0px 5px;"></div>
		</div>
	</div>
	<div class="col-md-2">
		<div class="well" style="height: 400px;">
			<strong class="text-info">Users Online</strong>
			<div id="usersOnline">
			</div>
			<br />
			<strong class="text-info">Users Away</strong>
			<div id="usersAway">
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-10">
		{{ Form::textarea('message', null, array('id' => 'message', 'placeholder' => 'Type a message', 'class' => 'well col-md-12', 'style' => 'height: auto;', 'rows' => 4)) }}
		<div class="row">
			<div class="col-md-4">
				<span class="help-inline">
					Use Shift+Enter to make a new line.<br />
					Use Enter to submit your message.
				</span>
			</div>
		</div>
	</div>
</div>
@section('js')
{{ HTML::script('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.js') }}
{{ HTML::script('vendor/jQuery-slimScroll/jquery.slimscroll.min.js') }}
{{ HTML::script('vendor/jquery-titlealert/jquery.titlealert.js') }}
{{ HTML::script('vendor/jwerty/jwerty.js') }}
{{ HTML::script('vendor/jquery-idletimer/dist/idle-timer.min.js') }}


{{ HTML::script('js/socket.io.js') }}

<script type="text/javascript">
	$('#message').attr("disabled", "disabled");

	$( document ).idleTimer( 600000 );

	var socket = io.connect("{{ Config::get('app.url') }}:1337");

	var reconnect = false;

    socket.on('connecting', function () {
    	if (reconnect == true) {
    		location.reload();
    		throw new Error('This is not an error. This is just to abort javascript');
    	}

        Messenger().post({message: 'Connecting to chat...', hideAfter: 3});
    });

    socket.on('error', function () {
        Messenger().post({message: 'Chat server offline :(',type: 'error'});
    });

    socket.on('reconnecting', function () {
        Messenger().post({message: 'Connection to chat lost. Reconnecting...',type: 'error'});
        $('#message').attr("disabled", "disabled");

        reconnect = true;

        socket.disconnect();
        socket = io.connect("{{ Config::get('app.url') }}:1337");
    });

    socket.on('connect', function () {

    	$('#message').attr("disabled", null);
    	Messenger().hideAll();
    	Messenger().post({message: 'You\'re connected to chat!', hideAfter: 3});

        // Subscribe to a chat room
        socket.emit('subscribe', 
    	{
    		'room': '{{ $chatRoom->uniqueId }}',
    		'userId': '{{ $activeUser->uniqueId }}',
    		'username': '{{ $activeUser->username }}'
    	});

        socket.on('backFillChatLog', function (chatLog) {
        	$('#chatBox').html(chatLog.join(''));

			chatScroll();
        });

        // Update the userlist when a user connects or disconnects.
        socket.on('userListUpdate', function (userList) {
            $('#usersOnline').html(userList.join('<br />'));
        });

        socket.on('message', function (message) {
            $('#chatBox').append(message);

			chatScroll();

			$.titleAlert("New message!", {
				requireBlur:true,
				stopOnFocus:true,
				duration:0,
				interval:700
			});
        });

        socket.on('connectionMessage', function (connectionMessageData) {
    		$('#chatBox').append(connectionMessageData);

    		chatScroll();
        });

        socket.on('awayListUpdate', function (userList) {
            $('#usersAway').html(userList.join('<br />'));
        });        

		$( document ).on( "idle.idleTimer", function(){
	        socket.emit('statusUpdate', 
	    	{
	    		'status': 'Away'
	    	});
		});

		$( document ).on( "active.idleTimer", function(){
	        socket.emit('statusUpdate', 
	    	{
	    		'status': ''
	    	});
		});

    });
	jwerty.key('enter', false);
	jwerty.key('enter', true, '#message');
	jwerty.key('enter', function () {
		var message = $('#message').val();
		$.post('/chat/addmessage', { chat_room_id: '{{ $chatRoom->id }}', message: message });
		$('#message').val('');
	});

	function chatScroll() {
		$('#chatBox').slimScroll({
			height: '350px',
			railVisible: true,
			alwaysVisible: true,
			color: '#81aab0',
			scrollTo: $('#chatBox')[0].scrollHeight
		});
	};

	function sortChats() {
		// Get an array of all ticket rows in the table
		$($('#chatBox table').toArray().sort(function(a, b) {
			var date1 = Date.parse($(a).attr('data-date'));
			var date2 = Date.parse($(b).attr('data-date'));

			if (date1 > date2) {
				return 1;
			} else if(date1 == date2) {
				return 0;
			} else {
				return -1;
			}
		})).appendTo('#chatBox');
	}
</script>
@endsection