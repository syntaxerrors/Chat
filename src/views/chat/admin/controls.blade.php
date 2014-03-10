<div class="row">
	<div class="col-md-7">
		<div class="panel panel-default">
			<div class="panel-heading">Chat Configuration</div>
			<div class="panel-body">
				<dl class="dl-horizontal">
					<dt>Status</dt>
					@if ($status == 'Running')
						<dd class="text-success">{{ $status }}</dd>
					@elseif ($status == 'Restarting...')
						<dd class="text-warning">{{ $status }}</dd>
					@elseif ($status == 'Halted')
						<dd class="text-danger">{{ $status }}</dd>
					@else
						<dd class="text-disabled">{{ $status }}</dd>
					@endif
					<dt>Debug</dt>
					<dd>{{ $chatConfig->debug == 1 ? 'true' : 'false' }}</dd>
					<dt>Port</dt>
					<dd>{{ $chatConfig->port }}</dd>
					<dt>Back Log</dt>
					<dd>{{ $chatConfig->backLog }}</dd>
					<dt>No of chats to back fill</dt>
					<dd>{{ $chatConfig->backFill }}</dd>
					<dt>API End Point</dt>
					<dd>{{ $chatConfig->apiEndPoint }}</dd>
					<dt>Connection Message</dt>
					<dd>{{ $chatConfig->connectionMessage == 1 ? 'true' : 'false' }}</dd>
				</dl>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">Controls</div>
			<div class="panel-body">
				<div class="btn-group text-center">
					@if ($status)
						<button class="btn btn-primary" disabled="disabled">Start</button>
						{{ HTML::link('/chat/admin/stop-server', 'Stop', array('class' => 'btn btn-danger')) }}
					@else
						{{ HTML::link('/chat/admin/start-server', 'Start', array('class' => 'btn btn-primary')) }}
						<button class="btn btn-danger" disabled="disabled">Stop</button>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>