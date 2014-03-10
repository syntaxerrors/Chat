{{ bForm::open() }}
	<div class="panel panel-default">
		<div class="panel-heading">Modify chat configuration</div>
		<div class="panel-body">
			{{ bForm::select('debug', array('Debug Off', 'Debug On'), $chatConfig->debug, array(), 'Debug') }}
			{{ bForm::text('port', $chatConfig->port, array(), 'Port') }}
			{{ bForm::text('backLog', $chatConfig->backLog, array(), 'Back Log') }}
			{{ bForm::text('backFill', $chatConfig->backFill, array(), 'Back Fill') }}
			{{ bForm::text('apiEndPoint', $chatConfig->apiEndPoint, array(), 'API End Point') }}
			{{ bForm::select('connectionMessage', array('Connection Message Off', 'Connection Message On'), $chatConfig->connectionMessage, array(), 'Connection Message') }}
			{{ bForm::submit('Update Config') }}
		</div>
	</div>
{{ bForm::close() }}