<div class="row">
	<div class="col-md-8">
		<div class="panel panel-default">
			<div class="panel-heading">Full Chat Transcript for {{ $chatRoom->name }}</div>
			<div id="chatBox">
				@foreach ($chatRoom->chats as $chat)
					<table class="table-hover">
						<tbody>
							<tr>
								<td style="vertical-align: top;white-space: nowrap;"><small class="muted">({{ $chat->created_at }})</small>&nbsp;</td>
								<td style="vertical-align: top;white-space: nowrap;">
									<strong class="text-disabled">
										{{ $chat->user->username }}
									</strong>
								</td>
								<td style="vertical-align: top;">{{ nl2br($chat->message) }}</td>
							</tr>
						</tbody>
					</table>
				@endforeach
			</div>
		</div>
	</div>
	<div class="col-md-4">
		{{ bForm::setType('basic')->open() }}
			<div class="panel panel-default">
				<div class="panel-heading">Add to existing post</div>
				<div class="panel-body">
					{{ bForm::text('post_id', null, array('placeholder' => 'Post Id'), 'Post ID') }}
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">Make a new post to a board</div>
				<div class="panel-body">
					{{ bForm::select('board_id', $boards, null, array(), 'Board') }}
					{{ bForm::text('title', null, array('placeholder' => 'Post Title'), 'Post Title') }}
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">Extra Options</div>
				<div class="panel-body">
					{{ bForm::text('start', 1, array('placeholder' => 'Start at line'), 'Starting Line Number') }}
					{{ bForm::text('end', $index-1, array('placeholder' => 'End at line'), 'Ending Line Number') }}
					{{ bForm::checkbox('noTimestamps', 1, true, array(), 'Omit timestamps') }}
					{{ bForm::submit('Post') }}
				</div>
			</div>
		{{ bForm::close() }}
	</div>
</div>