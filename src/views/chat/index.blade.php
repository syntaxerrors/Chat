<?php $create = false; ?>
@if ($activeUser->checkPermission('CHAT_CREATE'))
	<?php $create = true; ?>
@endif
<div class="row">
	<div class="col-md-offset-2 col-md-8">
		<div class="panel panel-default">
			<div class="panel-heading">
				Chat Rooms
				@if ($create)
					<div class="panel-btn">
						{{ HTML::addButton('/chat/add') }}
					</div>
				@endif
			</div>
			<table class="table table-condensed table-hover table-striped">
				<thead>
					<tr>
						<th class="text-left">Room</th>
						<th class="text-left">User Online</th>
						<th class="text-left">Creator</th>
						@if ($create)
							<th class="text-right">Actions</th>
						@endif
					</tr>
				</thead>
				<tbody>
					@if (count($chatRooms) > 0)
						@foreach ($chatRooms as $chatRoom)
							<tr>
								<td>{{ HTML::link('/chat/room/'. $chatRoom->id, $chatRoom->name) }}</td>
								<td>{{ $chatRoom->usersOnline }}</td>
								<td>{{ HTML::link('/user/view/'. $chatRoom->user_id, $chatRoom->user->username) }}</td>
								@if ($create)
									<td class="text-right">
										<div class="btn-group">
											{{ HTML::link('/chat/clear/'. $chatRoom->id, 'Clear', array('class' => 'btn btn-xs btn-primary')) }}
											{{ HTML::link('/chat/full-chat/'. $chatRoom->id, 'Transcript', array('class' => 'btn btn-xs btn-primary')) }}
											{{ HTML::link('/chat/update/'. $chatRoom->id .'/activeFlag/0', 'Make Inactive', array('class' => 'btn btn-xs btn-primary')) }}
											{{ HTML::linkIcon('/chat/edit/'. $chatRoom->id, 'fa fa-edit', null, array('class' => 'btn btn-xs btn-primary', 'title' => 'Edit')) }}
											{{ HTML::linkIcon('/chat/delete/'. $chatRoom->id, 'fa fa-trash-o', null, array('class' => 'confirm-remove btn btn-xs btn-danger', 'title' => 'Delete')) }}
										</div>
									</td>
								@endif
							</tr>
						@endforeach
					@endif
				</tbody>
			</table>
		</div>
		@if ($create && count($inactiveChatRooms) > 0)
		<div class="panel panel-default">
			<div class="panel-heading">Inactive Chat Rooms</div>
				<table class="table table-condensed table-hover table-striped">
					<thead>
						<tr>
							<th class="text-left">Room</th>
							<th class="text-left">User Online</th>
							<th class="text-left">Creator</th>
							@if ($create)
								<th class="text-right">Actions</th>
							@endif
						</tr>
					</thead>
					<tbody>
						@if (count($inactiveChatRooms) > 0)
							@foreach ($inactiveChatRooms as $chatRoom)
								<tr>
									<td>{{ HTML::link('/chat/room/'. $chatRoom->id, $chatRoom->name) }}</td>
									<td>{{ $chatRoom->usersOnline }}</td>
									<td>{{ HTML::link('/user/view/'. $chatRoom->user_id, $chatRoom->user->username) }}</td>
									@if ($create)
										<td class="text-right">
											<div class="btn-group">
												{{ HTML::link('/chat/clear/'. $chatRoom->id, 'Clear', array('class' => 'btn btn-xs btn-primary')) }}
												{{ HTML::link('/chat/full-chat/'. $chatRoom->id, 'Transcript', array('class' => 'btn btn-xs btn-primary')) }}
												{{ HTML::link('/chat/update/'. $chatRoom->id .'/activeFlag/1', 'Make Active', array('class' => 'btn btn-xs btn-primary')) }}
												{{ HTML::linkIcon('/chat/edit/'. $chatRoom->id, 'fa fa-edit', null, array('class' => 'btn btn-xs btn-primary', 'title' => 'Edit')) }}
												{{ HTML::linkIcon('/chat/delete/'. $chatRoom->id, 'fa fa-trash-o', null, array('class' => 'confirm-remove btn btn-xs btn-danger', 'title' => 'Delete')) }}
											</div>
										</td>
									@endif
								</tr>
							@endforeach
						@endif
					</tbody>
				</table>
			</div>
		@endif
	</div>
</div>