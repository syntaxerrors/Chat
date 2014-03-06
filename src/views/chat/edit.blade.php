<div class="row">
	<div class="col-md-offset-5 col-md-3">
		<div class="well">
			<div class="well-title">Update {{ $chatRoom->name }}</div>
			{{ bForm::open() }}
				{{ bForm::text('name', $chatRoom->name, array('id' => 'name', 'placeholder' => 'Name'), 'Name') }}
				{{ bForm::checkbox('activeFlag', 1, $chatRoom->activeFlag, array('id' => 'activeFlag'), 'Active') }}
				{{ bForm::submitReset('Update') }}
			{{ bForm::close(); }}
		</div>
	</div>
</div>